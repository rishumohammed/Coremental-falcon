<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Employee;
use App\Attendance;
use Illuminate\Pagination\LengthAwarePaginator;

class ReportController extends Controller
{
    public function absent(Request $req)
    {
        $req->flash();

        // Default to yesterday if no dates are provided
        $fromDate = $req->from_date ? $req->from_date : date('Y-m-d', strtotime('-1 day'));
        $toDate = $req->to_date ? $req->to_date : date('Y-m-d', strtotime('-1 day'));

        $employeesQuery = Employee::select('id', 'name', 'employee_id', 'weekend_days');

        if ($req->search) {
            $employeesQuery->where(function ($q) use ($req) {
                $q->where('name', 'like', "%{$req->search}%")
                  ->orWhere('employee_id', 'like', "%{$req->search}%");
            });
        }

        $employees = $employeesQuery->get();

        // Get all attendances in the date range
        $attendances = Attendance::select('employee_id', \DB::raw('CAST(created_at AS DATE) as date'))
            ->whereBetween('created_at', [$fromDate . " 00:00:00", $toDate . " 23:59:59"])
            ->groupBy('employee_id', \DB::raw('CAST(created_at AS DATE)'))
            ->get()
            ->groupBy('date');

        // Get all employee leaves in the date range
        $employeeLeaves = \App\EmployeeLeave::whereBetween('date', [$fromDate, $toDate])
            ->get()
            ->groupBy('date');

        // Get all active blocks that overlap with the date range
        $employeeBlocks = \App\EmployeeBlock::where(function($q) use ($fromDate, $toDate) {
            $q->where('start_date', '<=', $toDate)
              ->where('end_date', '>=', $fromDate);
        })->get();

        // Get the system default leave type
        $defaultLeaveType = \App\LeaveType::where('is_default', true)->first();
        $allLeaveTypes = \Cache::rememberForever('leave_types', fn() => \App\LeaveType::all());

        // ── Load weekend & holiday exclusions ───────────────────────────────
        $weekendSetting = \App\Setting::where('key', 'weekend_days')->first();
        $weekendDays = $weekendSetting
            ? array_map('intval', array_filter(explode(',', $weekendSetting->val), 'strlen'))
            : []; // empty = no weekend exclusion

        $holidayDates = \App\Holiday::pluck('date')->map(function($d) {
            return date('Y-m-d', strtotime($d));
        })->toArray();
        // ────────────────────────────────────────────────────────────────────

        $absent_records = [];

        $period = new \DatePeriod(
            new \DateTime($fromDate),
            new \DateInterval('P1D'),
            (new \DateTime($toDate))->modify('+1 day')
        );

        $employeeBlocksGrouped = $employeeBlocks->groupBy('employee_id');

        foreach ($period as $dt) {
            $dateStr = $dt->format("Y-m-d");

            // Skip public holidays
            if (in_array($dateStr, $holidayDates)) {
                continue;
            }
            
            // Pluck employee IDs that have attendance on this specific date
            $attendancesOnDate = isset($attendances[$dateStr]) 
                ? $attendances[$dateStr]->pluck('employee_id')->flip()->toArray() 
                : [];
                
            $leavesOnDate = isset($employeeLeaves[$dateStr]) 
                ? $employeeLeaves[$dateStr]->keyBy('employee_id') 
                : collect();

            foreach ($employees as $emp) {
                // Check weekend for this specific employee
                $empWeekend = $emp->weekend_days ? array_map('intval', array_filter(explode(',', $emp->weekend_days), 'strlen')) : $weekendDays;
                $dayOfWeek = (int) $dt->format('w');
                if (in_array($dayOfWeek, $empWeekend)) {
                    continue;
                }

                if (!isset($attendancesOnDate[$emp->id])) {
                    $leaveTypeId = null;

                    if ($leavesOnDate->has($emp->id)) {
                        // 1. Manual Override takes highest priority
                        $leaveTypeId = $leavesOnDate->get($emp->id)->leave_type_id;
                    } else {
                        // 2. Block Reason takes second priority
                        $empBlocks = $employeeBlocksGrouped->get($emp->id, collect());
                        $block = $empBlocks->first(function($b) use ($dateStr) {
                            return $b->start_date <= $dateStr && $b->end_date >= $dateStr;
                        });
                        
                        if ($block) {
                            $leaveTypeId = $block->leave_type_id;
                        } else if ($defaultLeaveType) {
                            // 3. System Default takes lowest priority
                            $leaveTypeId = $defaultLeaveType->id;
                        }
                    }

                    $leaveTypeName = 'Absent';
                    if ($leaveTypeId) {
                        $matchedType = $allLeaveTypes->firstWhere('id', $leaveTypeId);
                        if ($matchedType) {
                            $leaveTypeName = $matchedType->name;
                        }
                    }
                    
                    $absent_records[] = (object) [
                        'date' => $dateStr,
                        'employee' => $emp,
                        'leave_type_id' => $leaveTypeId,
                        'leave_type_name' => $leaveTypeName
                    ];
                }
            }
        }

        // Sort records by date descending
        usort($absent_records, function($a, $b) {
            return strtotime($b->date) - strtotime($a->date);
        });

        if ($req->export) {
            return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\ViewExport(collect($absent_records), 'admin.reports.absent-export'), 'absent-report-'.date('Y-m-d').'.xlsx');
        }

        // Paginate the array
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 100;
        $currentItems = array_slice($absent_records, $perPage * ($currentPage - 1), $perPage);
        $paginatedRows = new LengthAwarePaginator($currentItems, count($absent_records), $perPage, $currentPage, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
            'query' => $req->query()
        ]);

        $leaveTypes = $allLeaveTypes;
        $allEmployees = Employee::all(['id', 'name', 'employee_id']);

        return view('admin.reports.absent', compact('paginatedRows', 'leaveTypes', 'allEmployees'));
    }

    public function leaves(Request $req)
    {
        $req->flash();

        // Default to current month up to today
        $fromDate = $req->from_date ? $req->from_date : date('Y-m-01');
        $toDate = $req->to_date ? $req->to_date : date('Y-m-d');

        $employeesQuery = Employee::select('id', 'name', 'employee_id', 'department_id', 'weekend_days')->with('department');

        if ($req->search) {
            $employeesQuery->where(function ($q) use ($req) {
                $q->where('name', 'like', "%{$req->search}%")
                  ->orWhere('employee_id', 'like', "%{$req->search}%");
            });
        }
        
        if ($req->has('department_id') && $req->department_id != '') {
            $employeesQuery->where('department_id', $req->department_id);
        }

        $employees = $employeesQuery->get();

        $attendances = Attendance::select('employee_id', \DB::raw('CAST(created_at AS DATE) as date'))
            ->whereBetween('created_at', [$fromDate . " 00:00:00", $toDate . " 23:59:59"])
            ->groupBy('employee_id', \DB::raw('CAST(created_at AS DATE)'))
            ->get()
            ->groupBy('date');

        $employeeLeaves = \App\EmployeeLeave::whereBetween('date', [$fromDate, $toDate])
            ->get()
            ->groupBy('date');

        $employeeBlocks = \App\EmployeeBlock::where(function($q) use ($fromDate, $toDate) {
            $q->where('start_date', '<=', $toDate)
              ->where('end_date', '>=', $fromDate);
        })->get();

        $defaultLeaveType = \App\LeaveType::where('is_default', true)->first();
        $allLeaveTypes = \Cache::rememberForever('leave_types', fn() => \App\LeaveType::all());

        $weekendSetting = \App\Setting::where('key', 'weekend_days')->first();
        $weekendDays = $weekendSetting ? array_map('intval', array_filter(explode(',', $weekendSetting->val), 'strlen')) : [];
        $holidayDates = \App\Holiday::pluck('date')->map(function($d) { return date('Y-m-d', strtotime($d)); })->toArray();

        $reportData = [];
        foreach ($employees as $emp) {
            $reportData[$emp->id] = [
                'employee' => $emp,
                'total_leaves' => 0,
                'breakdown' => [],
                'details' => []
            ];
            foreach ($allLeaveTypes as $lt) {
                $reportData[$emp->id]['breakdown'][$lt->name] = 0;
            }
            $reportData[$emp->id]['breakdown']['Absent'] = 0;
        }

        $period = new \DatePeriod(
            new \DateTime($fromDate),
            new \DateInterval('P1D'),
            (new \DateTime($toDate))->modify('+1 day')
        );

        $employeeBlocksGrouped = $employeeBlocks->groupBy('employee_id');

        foreach ($period as $dt) {
            $dateStr = $dt->format("Y-m-d");

            if (in_array($dateStr, $holidayDates)) continue;
            
            $attendancesOnDate = isset($attendances[$dateStr]) ? $attendances[$dateStr]->pluck('employee_id')->flip()->toArray() : [];
            $leavesOnDate = isset($employeeLeaves[$dateStr]) ? $employeeLeaves[$dateStr]->keyBy('employee_id') : collect();

            foreach ($employees as $emp) {
                $empWeekend = $emp->weekend_days ? array_map('intval', array_filter(explode(',', $emp->weekend_days), 'strlen')) : $weekendDays;
                if (in_array((int)$dt->format('w'), $empWeekend)) continue;

                if (!isset($attendancesOnDate[$emp->id])) {
                    $leaveTypeId = null;

                    if ($leavesOnDate->has($emp->id)) {
                        $leaveTypeId = $leavesOnDate->get($emp->id)->leave_type_id;
                    } else {
                        $empBlocks = $employeeBlocksGrouped->get($emp->id, collect());
                        $block = $empBlocks->first(function($b) use ($dateStr) {
                            return $b->start_date <= $dateStr && $b->end_date >= $dateStr;
                        });
                        
                        if ($block) {
                            $leaveTypeId = $block->leave_type_id;
                        } else if ($defaultLeaveType && $dateStr <= date('Y-m-d')) {
                            $leaveTypeId = $defaultLeaveType->id;
                        }
                    }

                    // If it's a future date and no explicit leave block exists, ignore it
                    if ($dateStr > date('Y-m-d') && !$leaveTypeId) {
                        continue;
                    }

                    $leaveTypeName = 'Absent';
                    if ($leaveTypeId) {
                        $matchedType = $allLeaveTypes->firstWhere('id', $leaveTypeId);
                        if ($matchedType) {
                            $leaveTypeName = $matchedType->name;
                        }
                    }
                    
                    $reportData[$emp->id]['breakdown'][$leaveTypeName]++;
                    $reportData[$emp->id]['total_leaves']++;
                    
                    $reportData[$emp->id]['details'][] = [
                        'date' => $dateStr,
                        'type' => $leaveTypeName
                    ];
                }
            }
        }

        $records = array_values($reportData);
        usort($records, function($a, $b) {
            return $b['total_leaves'] <=> $a['total_leaves'];
        });

        if ($req->export) {
            return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\ViewExport(collect($records), 'admin.reports.leaves_export', ['leaveTypes' => $allLeaveTypes]), 'leave-report-'.date('Y-m-d').'.xlsx');
        }

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 50;
        $currentItems = array_slice($records, $perPage * ($currentPage - 1), $perPage);
        $paginatedRows = new LengthAwarePaginator($currentItems, count($records), $perPage, $currentPage, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
            'query' => $req->query()
        ]);
        $paginatedRows->setPath($req->url());

        $departments = \Cache::rememberForever('departments', fn() => \App\Department::all());
        $allEmployees = Employee::all(['id', 'name', 'employee_id']);

        return view('admin.reports.leaves', compact('paginatedRows', 'allLeaveTypes', 'departments', 'allEmployees'));
    }

    public function missingCheckouts(Request $req)
    {
        $req->flash();

        // Fetch last 30 days of history up to today
        $fromDate = date('Y-m-d', strtotime('-30 days'));
        $toDate = date('Y-m-d');

        $departments = \Cache::rememberForever('departments', fn() => \App\Department::all());

        $employeesQuery = Employee::select('id', 'name', 'employee_id', 'department_id', 'shift_id')->with('shift');

        $hasFilter = false;
        if ($req->search) {
            $hasFilter = true;
            $employeesQuery->where(function ($q) use ($req) {
                $q->where('name', 'like', "%{$req->search}%")
                  ->orWhere('employee_id', 'like', "%{$req->search}%");
            });
        }
        
        if ($req->has('department_id') && $req->department_id != '') {
            $hasFilter = true;
            $employeesQuery->where('department_id', $req->department_id);
        }

        $employeesCollection = $employeesQuery->get();
        $employees = $employeesCollection->keyBy('id');
        $employeeIds = $employees->keys();

        $attQuery = Attendance::select('employee_id', 'type', 'created_at', 'id', 'photo', 'entry_type')
            ->whereBetween('created_at', [$fromDate . " 00:00:00", $toDate . " 23:59:59"])
            ->orderBy('created_at', 'asc');
            
        if ($hasFilter) {
            $attQuery->whereIn('employee_id', $employeeIds);
        }
        
        $attendances = $attQuery->get();

        $tz = timezone();
        $grouped = [];
        foreach ($attendances as $log) {
            $dateKey = $log->created_at->timezone($tz)->format('Y-m-d');
            $empId = $log->employee_id;
            
            if (!isset($grouped[$dateKey])) {
                $grouped[$dateKey] = [
                    'raw_date' => $dateKey,
                    'employees' => []
                ];
            }
            if (!isset($grouped[$dateKey]['employees'][$empId])) {
                $grouped[$dateKey]['employees'][$empId] = [];
            }
            $grouped[$dateKey]['employees'][$empId][] = $log;
        }

        $missingRecords = [];

        foreach ($grouped as $dateKey => $periodData) {
            foreach ($periodData['employees'] as $empId => $logs) {
                if (!isset($employees[$empId])) continue;

                $checkInTime = null;
                $pairs = [];

                foreach ($logs as $log) {
                    $logTime = $log->created_at->timezone($tz)->format('Y-m-d H:i:s');
                    
                    if ($log->type == 0) { // Check In
                        if ($checkInTime !== null) {
                            $pairs[] = ['in' => $checkInTime, 'out' => null];
                        }
                        $checkInTime = $logTime;
                    } else if ($log->type == 1) { // Check Out
                        if ($checkInTime !== null) {
                            $pairs[] = ['in' => $checkInTime, 'out' => $logTime];
                            $checkInTime = null;
                        } else {
                            $pairs[] = ['in' => null, 'out' => $logTime];
                        }
                    }
                }

                if ($checkInTime !== null) {
                    $pairs[] = ['in' => $checkInTime, 'out' => null];
                }

                $employeeObj = $employees[$empId];
                foreach ($pairs as $index => $p) {
                    if ($this->isPairMissingCheckout($dateKey, $p, $employeeObj)) {
                        $missingRecords[] = (object) [
                            'date' => $dateKey,
                            'employee' => $employeeObj,
                            'check_in_time' => $p['in'],
                            'pair_index' => $index
                        ];
                    }
                }
            }
        }

        usort($missingRecords, function($a, $b) {
            return strtotime($b->date) <=> strtotime($a->date);
        });

        if ($req->export) {
            return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\ViewExport(collect($missingRecords), 'admin.reports.missing_checkouts_export', []), 'missing-checkouts-'.date('Y-m-d').'.xlsx');
        }

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 50;
        $currentItems = array_slice($missingRecords, $perPage * ($currentPage - 1), $perPage);
        $paginatedRows = new LengthAwarePaginator($currentItems, count($missingRecords), $perPage, $currentPage, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
            'query' => $req->query()
        ]);
        $paginatedRows->setPath($req->url());

        $allEmployees = Employee::all(['id', 'name', 'employee_id']);

        return view('admin.reports.missing_checkouts', compact('paginatedRows', 'departments', 'allEmployees'));
    }

    public function manualEntry()
    {
        $allEmployees = Employee::orderBy('name', 'ASC')->get(['id', 'name', 'employee_id']);
        return view('admin.reports.manual_entry', compact('allEmployees'));
    }

    public function addManualCheckin(Request $req)
    {
        $req->validate([
            'employee_id' => 'required',
            'date' => 'required|date',
            'time' => 'required'
        ]);

        $dateTime = date('Y-m-d H:i:s', strtotime($req->date . ' ' . $req->time));
        
        $utcTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $dateTime, timezone())->setTimezone('UTC');

        $log = new Attendance();
        $log->employee_id = $req->employee_id;
        $log->type = 0; // Checkin
        $log->entry_type = 1; // Manual
        $log->created_at = $utcTime;
        $log->updated_at = $utcTime;
        $log->user_id = \Auth::user()->id;
        $log->save();

        return redirect()->back()->with('status', 'Manual check-in added successfully.');
    }

    public function addManualCheckout(Request $req)
    {
        $req->validate([
            'employee_id' => 'required',
            'date' => 'required|date',
            'time' => 'required'
        ]);

        $dateTime = date('Y-m-d H:i:s', strtotime($req->date . ' ' . $req->time));
        
        $utcTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $dateTime, timezone())->setTimezone('UTC');

        $log = new Attendance();
        $log->employee_id = $req->employee_id;
        $log->type = 1; // Checkout
        $log->entry_type = 1; // Manual
        $log->created_at = $utcTime;
        $log->updated_at = $utcTime;
        $log->user_id = \Auth::user()->id;
        $log->save();

        return redirect()->back()->with('status', 'Manual checkout added successfully.');
    }
    
    public function assignLeave(Request $req)
    {
        $req->validate([
            'employee_id' => 'required|integer',
            'date' => 'required|date',
            'leave_type_id' => 'nullable|integer'
        ]);

        if ($req->leave_type_id) {
            \App\EmployeeLeave::updateOrCreate(
                ['employee_id' => $req->employee_id, 'date' => $req->date],
                ['leave_type_id' => $req->leave_type_id]
            );
        } else {
            \App\EmployeeLeave::where('employee_id', $req->employee_id)
                ->where('date', $req->date)
                ->delete();
        }

        return response()->json(['status' => 'success']);
    }

    public function workingHours(Request $req)
    {
        $req->flash();

        if ($req->view_type == 'monthly') {
            $month = $req->month_filter ?: date('Y-m');
            $fromDate = date('Y-m-01', strtotime($month));
            $toDate = date('Y-m-t', strtotime($month));
        } else {
            $fromDate = $req->from_date ? $req->from_date : date('Y-m-d');
            $toDate = $req->to_date ? $req->to_date : date('Y-m-d');
        }

        $departments = \Cache::rememberForever('departments', fn() => \App\Department::all());
        $designations = \Cache::rememberForever('designations', fn() => \App\Designation::all());
        $shifts = \Cache::rememberForever('shifts', fn() => \App\Shift::all());
        $locations = \Cache::rememberForever('locations', fn() => \App\Location::all());
        $divisions = \Cache::rememberForever('divisions', fn() => \App\Division::all());
        $adminUsers = \App\User::orderBy('name')->get();

        $employeesQuery = Employee::select('id', 'name', 'employee_id', 'department_id', 'designation_id', 'shift_id', 'location_id', 'division_id')->with(['department', 'designation', 'shift', 'location', 'division', 'users']);

        if ($req->search) {
            $employeesQuery->where(function ($q) use ($req) {
                $q->where('name', 'like', "%{$req->search}%")
                  ->orWhere('employee_id', 'like', "%{$req->search}%");
            });
        }
        
        if ($req->has('department_id') && $req->department_id != '') {
            $employeesQuery->where('department_id', $req->department_id);
        }
        if ($req->has('designation_id') && $req->designation_id != '') {
            $employeesQuery->where('designation_id', $req->designation_id);
        }
        if ($req->has('shift_id') && $req->shift_id != '') {
            $employeesQuery->where('shift_id', $req->shift_id);
        }
        if ($req->has('location_id') && $req->location_id != '') {
            $employeesQuery->where('location_id', $req->location_id);
        }
        if ($req->has('division_id') && $req->division_id != '') {
            $employeesQuery->where('division_id', $req->division_id);
        }
        if ($req->has('user_id') && $req->user_id != '') {
            $employeesQuery->whereHas('users', function ($q) use ($req) {
                $q->where('users.id', $req->user_id);
            });
        }

        $hasFilter = false;
        if ($req->search || $req->department_id || $req->designation_id || $req->shift_id || $req->location_id || $req->division_id || $req->user_id) {
            $hasFilter = true;
        }

        $employees = $employeesQuery->get()->keyBy('id');
        $employeeIds = $employees->keys();

        // Fetch all attendances in range
        $attQuery = Attendance::whereBetween('created_at', [$fromDate . " 00:00:00", $toDate . " 23:59:59"])
            ->orderBy('created_at', 'ASC');
            
        if ($hasFilter) {
            $attQuery->whereIn('employee_id', $employeeIds);
        }
        
        $attendances = $attQuery->get();

        $viewType = $req->view_type ?: 'daily';
        $sortBy = $req->sort_by ?: 'date';
        $sortDir = $req->sort_dir ?: 'desc';

        // Group strictly by Daily Attendance Date first
        $dailyGrouped = [];
        foreach ($attendances as $att) {
            $timestamp = strtotime($att->created_at);
            $attendanceDateTimestamp = $timestamp;
            
            if (isset($employees[$att->employee_id])) {
                $emp = $employees[$att->employee_id];
                if ($emp->shift && $emp->shift->start_time && $emp->shift->end_time) {
                    if ($emp->shift->start_time > $emp->shift->end_time) {
                        $punchTime = date('H:i:s', $timestamp);
                        if ($punchTime <= '12:00:00') {
                            $attendanceDateTimestamp = strtotime('-1 day', $timestamp);
                        }
                    }
                }
            }

            $dateKey = date('Y-m-d', $attendanceDateTimestamp);
            
            if (!isset($dailyGrouped[$dateKey])) {
                $dailyGrouped[$dateKey] = ['raw_date' => $dateKey, 'employees' => []];
            }
            if (!isset($dailyGrouped[$dateKey]['employees'][$att->employee_id])) {
                $dailyGrouped[$dateKey]['employees'][$att->employee_id] = [];
            }
            $dailyGrouped[$dateKey]['employees'][$att->employee_id][] = $att;
        }

        $records = [];
        $grandTotalMinutes = 0;
        $grandTotalBreakMinutes = 0;

        // Step 1: Process daily logs
        $dailyRecords = [];
        foreach ($dailyGrouped as $dateKey => $periodData) {
            foreach ($periodData['employees'] as $empId => $logs) {
                if (!isset($employees[$empId])) continue; // Skip if employee doesn't match search/department

                $totalMinutes = 0;
                $totalBreakMinutes = 0;
                $checkInTime = null;
                $status = 'Complete';
                $pairs = [];

                foreach ($logs as $log) {
                    if ($log->type == 0) {
                        // Check In
                        $checkInTime = strtotime($log->created_at);
                        
                        if (count($pairs) > 0 && $pairs[count($pairs) - 1]['raw_out']) {
                            $lastOutTime = $pairs[count($pairs) - 1]['raw_out'];
                            if ($checkInTime > $lastOutTime) {
                                $totalBreakMinutes += round(($checkInTime - $lastOutTime) / 60);
                            }
                        }

                        $pairs[] = [
                            'in' => $log->created_at->timezone(timezone())->format('Y-m-d H:i:s'),
                            'raw_in' => $checkInTime,
                            'out' => null,
                            'raw_out' => null,
                            'minutes' => 0
                        ];
                    } else if ($log->type == 1) {
                        // Check Out
                        if ($checkInTime) {
                            $checkOutTime = strtotime($log->created_at);
                            $mins = round(($checkOutTime - $checkInTime) / 60);
                            $totalMinutes += $mins;
                            
                            if (count($pairs) > 0) {
                                $pairs[count($pairs) - 1]['out'] = $log->created_at->timezone(timezone())->format('Y-m-d H:i:s');
                                $pairs[count($pairs) - 1]['raw_out'] = $checkOutTime;
                                $pairs[count($pairs) - 1]['minutes'] = $mins;
                            }
                            
                            $checkInTime = null; // Reset for next pair
                        } else {
                            $pairs[] = [
                                'in' => null,
                                'raw_in' => null,
                                'out' => $log->created_at->timezone(timezone())->format('Y-m-d H:i:s'),
                                'raw_out' => strtotime($log->created_at),
                                'minutes' => 0
                            ];
                        }
                    }
                }
                
                foreach ($pairs as $p) {
                    if ($p['in'] !== null && $p['out'] === null) {
                        $status = 'Missing Checkout';
                    } else if ($p['in'] === null && $p['out'] !== null) {
                        $status = ($status == 'Missing Checkout') ? 'Incomplete Logs' : 'Missing Check-In';
                    }
                }

                if (count($pairs) == 0) {
                    $pairs[] = ['in' => null, 'out' => null, 'minutes' => 0];
                }

                if ($checkInTime !== null && $status == 'Complete') {
                    $status = 'Missing Checkout';
                }

                $dailyRecords[] = [
                    'raw_date' => $periodData['raw_date'],
                    'employee_id' => $empId,
                    'total_minutes' => $totalMinutes,
                    'total_break_minutes' => $totalBreakMinutes,
                    'status' => $status,
                    'pairs' => $pairs
                ];
            }
        }

        // Step 2: Aggregate according to viewType
        $aggregated = [];
        foreach ($dailyRecords as $dr) {
            $empId = $dr['employee_id'];
            $dailyDate = $dr['raw_date'];
            
            if ($viewType == 'monthly') {
                $aggKey = date('Y-m', strtotime($dailyDate));
                $aggRawDate = date('Y-m-01', strtotime($dailyDate));
            } else if ($viewType == 'total') {
                $aggKey = 'Total (' . date('M d', strtotime($fromDate)) . ' - ' . date('M d', strtotime($toDate)) . ')';
                $aggRawDate = $fromDate;
            } else {
                $aggKey = $dailyDate;
                $aggRawDate = $dailyDate;
            }

            // Key combines date group and employee ID
            $uniqueKey = $aggKey . '_' . $empId;

            if (!isset($aggregated[$uniqueKey])) {
                $aggregated[$uniqueKey] = [
                    'date' => $aggKey,
                    'raw_date' => $aggRawDate,
                    'employee' => $employees[$empId],
                    'total_minutes' => 0,
                    'total_break_minutes' => 0,
                    'status' => 'Complete',
                    'view_type' => $viewType,
                    'pairs' => []
                ];
            }

            $aggregated[$uniqueKey]['total_minutes'] += $dr['total_minutes'];
            $aggregated[$uniqueKey]['total_break_minutes'] += $dr['total_break_minutes'];
            
            if ($dr['status'] != 'Complete') {
                if ($aggregated[$uniqueKey]['status'] == 'Complete') {
                    $aggregated[$uniqueKey]['status'] = $dr['status'];
                } else if ($aggregated[$uniqueKey]['status'] != $dr['status']) {
                    $aggregated[$uniqueKey]['status'] = 'Incomplete Logs';
                }
            }

            // In monthly/total views, we might accumulate many pairs. 
            // For UI sanity, only include pairs if viewType is daily, 
            // OR if you want them all, we can merge. Let's merge them for now.
            $aggregated[$uniqueKey]['pairs'] = array_merge($aggregated[$uniqueKey]['pairs'], $dr['pairs']);
        }

        foreach ($aggregated as $agg) {
            $totalMinutes = $agg['total_minutes'];
            $totalBreakMinutes = $agg['total_break_minutes'];

            $hours = floor($totalMinutes / 60);
            $minutes = $totalMinutes % 60;
            $formattedTime = sprintf('%02d:%02d', $hours, $minutes);

            $breakHours = floor($totalBreakMinutes / 60);
            $breakMins = $totalBreakMinutes % 60;
            $formattedBreakTime = sprintf('%02d:%02d', $breakHours, $breakMins);

            $grandTotalMinutes += $totalMinutes;
            $grandTotalBreakMinutes += $totalBreakMinutes;

            $records[] = (object) [
                'date' => $agg['date'],
                'raw_date' => $agg['raw_date'],
                'employee' => $agg['employee'],
                'total_minutes' => $totalMinutes,
                'formatted_time' => $formattedTime,
                'total_break_minutes' => $totalBreakMinutes,
                'formatted_break_time' => $formattedBreakTime,
                'status' => $agg['status'],
                'view_type' => $agg['view_type'],
                'pairs' => $agg['pairs']
            ];
        }


        // Apply Sorting
        usort($records, function($a, $b) use ($sortBy, $sortDir) {
            if ($sortBy == 'name') {
                $res = strcasecmp($a->employee->name, $b->employee->name);
            } else if ($sortBy == 'hours') {
                $res = $a->total_minutes <=> $b->total_minutes;
            } else {
                // sort by date
                $res = strtotime($a->raw_date) <=> strtotime($b->raw_date);
            }
            return $sortDir == 'asc' ? $res : -$res;
        });

        if ($req->export) {
            return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\ViewExport(collect($records), 'admin.reports.working_hours_export', ['grandTotalMinutes' => $grandTotalMinutes, 'grandTotalBreakMinutes' => $grandTotalBreakMinutes]), 'working-hours-'.date('Y-m-d').'.xlsx');
        }

        // Paginate
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 100;
        $currentItems = array_slice($records, $perPage * ($currentPage - 1), $perPage);
        $paginatedRows = new LengthAwarePaginator($currentItems, count($records), $perPage, $currentPage, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
            'query' => $req->query()
        ]);
        $paginatedRows->setPath($req->url());
        
        $grandTotalHoursFormatted = sprintf('%02d:%02d', floor($grandTotalMinutes / 60), $grandTotalMinutes % 60);
        $grandTotalBreakFormatted = sprintf('%02d:%02d', floor($grandTotalBreakMinutes / 60), $grandTotalBreakMinutes % 60);

        $allEmployees = Employee::all(['id', 'name', 'employee_id']);

        return view('admin.reports.working_hours', [
            'paginatedRows' => $paginatedRows,
            'viewType' => $viewType,
            'departments' => $departments,
            'designations' => $designations,
            'shifts' => $shifts,
            'locations' => $locations,
            'divisions' => $divisions,
            'grandTotalHours' => $grandTotalHoursFormatted,
            'grandTotalBreak' => $grandTotalBreakFormatted,
            'allEmployees' => $allEmployees,
            'adminUsers' => $adminUsers
        ]);
    }

    /**
     * Determine if an open (no check-out) attendance pair should be flagged as a Missing Checkout.
     *
     * Rules:
     * 1. Without shift: Flagged only when the check-in calendar day is completed (today > checkInDate).
     * 2. With shift: Flagged only when BOTH conditions are met:
     *    - Condition A: Current server datetime >= Shift End datetime (shift is completed).
     *    - Condition B: Current server date > Shift End date (calendar day of shift end is completed).
     *
     * @param string $dateKey  The date string (Y-m-d) of the attendance day.
     * @param array  $pair     ['in' => 'Y-m-d H:i:s'|null, 'out' => 'Y-m-d H:i:s'|null]
     * @param mixed  $employee The Employee model (with 'shift' relation eager-loaded).
     * @return bool
     */
    private function isPairMissingCheckout(string $dateKey, array $pair, $employee): bool
    {
        // Only flag pairs that are missing an out-punch
        if ($pair['in'] === null || $pair['out'] !== null) {
            return false;
        }

        $tz = timezone();
        $now = now()->timezone($tz);
        $today = $now->format('Y-m-d');

        $shift = $employee->shift ?? null;

        if ($shift && $shift->end_time && $shift->start_time) {
            $startTime = \Carbon\Carbon::createFromFormat('H:i:s', $shift->start_time, $tz);
            $endTime   = \Carbon\Carbon::createFromFormat('H:i:s', $shift->end_time,   $tz);

            // Anchored shift end datetime for this attendance check-in date
            $shiftEnd = \Carbon\Carbon::parse($dateKey . ' ' . $shift->end_time, $tz);

            // Handle overnight shifts (e.g. 20:00 to 08:00 → shift ends next calendar day)
            if ($endTime->lte($startTime)) {
                $shiftEnd->addDay();
            }

            $shiftEndDate = $shiftEnd->format('Y-m-d');

            // Condition A: Shift must be completed
            $isShiftCompleted = $now->gte($shiftEnd);

            // Condition B: Calendar day of shift end must be completed
            $isCalendarDayCompleted = ($today > $shiftEndDate);

            // Both conditions must be satisfied
            return ($isShiftCompleted && $isCalendarDayCompleted);
        }

        // Without shift: Calendar day of check-in must be completed (today > check-in date)
        return ($today > $dateKey);
    }
}
