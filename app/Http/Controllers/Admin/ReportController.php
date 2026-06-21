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

        $employeesQuery = Employee::query();

        if ($req->search) {
            $employeesQuery->where(function ($q) use ($req) {
                $q->where('name', 'like', "%{$req->search}%")
                  ->orWhere('employee_id', 'like', "%{$req->search}%");
            });
        }

        $employees = $employeesQuery->get();

        // Get all attendances in the date range
        $attendances = Attendance::select('employee_id', \DB::raw('DATE(created_at) as date'))
            ->whereBetween('created_at', [$fromDate . " 00:00:00", $toDate . " 23:59:59"])
            ->groupBy('employee_id', \DB::raw('DATE(created_at)'))
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
        $allLeaveTypes = \App\LeaveType::all();

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

        foreach ($period as $dt) {
            $dateStr = $dt->format("Y-m-d");

            // Skip weekends
            $dayOfWeek = (int) $dt->format('w'); // 0=Sun … 6=Sat
            if (in_array($dayOfWeek, $weekendDays)) {
                continue;
            }

            // Skip public holidays
            if (in_array($dateStr, $holidayDates)) {
                continue;
            }
            
            // Pluck employee IDs that have attendance on this specific date
            $attendancesOnDate = isset($attendances[$dateStr]) 
                ? $attendances[$dateStr]->pluck('employee_id')->toArray() 
                : [];
                
            $leavesOnDate = isset($employeeLeaves[$dateStr]) 
                ? $employeeLeaves[$dateStr]->keyBy('employee_id') 
                : collect();

            foreach ($employees as $emp) {
                if (!in_array($emp->id, $attendancesOnDate)) {
                    $leaveTypeId = null;

                    if ($leavesOnDate->has($emp->id)) {
                        // 1. Manual Override takes highest priority
                        $leaveTypeId = $leavesOnDate->get($emp->id)->leave_type_id;
                    } else {
                        // 2. Block Reason takes second priority
                        $block = $employeeBlocks->where('employee_id', $emp->id)
                            ->where('start_date', '<=', $dateStr)
                            ->where('end_date', '>=', $dateStr)
                            ->first();
                        
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

        $employeesQuery = Employee::query()->with('department');

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

        $attendances = Attendance::select('employee_id', \DB::raw('DATE(created_at) as date'))
            ->whereBetween('created_at', [$fromDate . " 00:00:00", $toDate . " 23:59:59"])
            ->groupBy('employee_id', \DB::raw('DATE(created_at)'))
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
        $allLeaveTypes = \App\LeaveType::all();

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

        foreach ($period as $dt) {
            $dateStr = $dt->format("Y-m-d");

            if (in_array((int)$dt->format('w'), $weekendDays)) continue;
            if (in_array($dateStr, $holidayDates)) continue;
            
            $attendancesOnDate = isset($attendances[$dateStr]) ? $attendances[$dateStr]->pluck('employee_id')->toArray() : [];
            $leavesOnDate = isset($employeeLeaves[$dateStr]) ? $employeeLeaves[$dateStr]->keyBy('employee_id') : collect();

            foreach ($employees as $emp) {
                if (!in_array($emp->id, $attendancesOnDate)) {
                    $leaveTypeId = null;

                    if ($leavesOnDate->has($emp->id)) {
                        $leaveTypeId = $leavesOnDate->get($emp->id)->leave_type_id;
                    } else {
                        $block = $employeeBlocks->where('employee_id', $emp->id)
                            ->where('start_date', '<=', $dateStr)
                            ->where('end_date', '>=', $dateStr)
                            ->first();
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

        $departments = \App\Department::all();
        $allEmployees = Employee::all(['id', 'name', 'employee_id']);

        return view('admin.reports.leaves', compact('paginatedRows', 'allLeaveTypes', 'departments', 'allEmployees'));
    }

    public function missingCheckouts(Request $req)
    {
        $req->flash();

        // Fetch all history to clear the queue
        $fromDate = '2020-01-01';
        $toDate = date('Y-m-d');

        $departments = \App\Department::all();

        $employeesQuery = Employee::query();

        if ($req->search) {
            $employeesQuery->where(function ($q) use ($req) {
                $q->where('name', 'like', "%{$req->search}%")
                  ->orWhere('employee_id', 'like', "%{$req->search}%");
            });
        }
        
        if ($req->has('department_id') && $req->department_id != '') {
            $employeesQuery->where('department_id', $req->department_id);
        }

        $employeesCollection = $employeesQuery->get();
        $employees = $employeesCollection->keyBy('id');
        $employeeIds = $employees->keys();

        $attendances = Attendance::select('employee_id', 'type', 'created_at', 'id', 'photo', 'entry_type')
            ->whereIn('employee_id', $employeeIds)
            ->whereBetween('created_at', [$fromDate . " 00:00:00", $toDate . " 23:59:59"])
            ->orderBy('created_at', 'asc')
            ->get();

        $grouped = [];
        foreach ($attendances as $log) {
            $dateKey = date('Y-m-d', strtotime($log->created_at->timezone(timezone())));
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
                $status = 'Complete';
                $pairs = [];

                foreach ($logs as $log) {
                    $logTime = date('Y-m-d H:i:s', strtotime($log->created_at->timezone(timezone())));
                    
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

                foreach ($pairs as $p) {
                    if ($p['in'] !== null && $p['out'] === null) {
                        $status = 'Missing Checkout';
                    } else if ($p['in'] === null && $p['out'] !== null) {
                        $status = ($status == 'Missing Checkout') ? 'Incomplete Logs' : 'Missing Check-In';
                    }
                }

                if ($checkInTime !== null && $status == 'Complete') {
                    $status = 'Missing Checkout';
                    $pairs[] = ['in' => $checkInTime, 'out' => null];
                }

                if ($status === 'Missing Checkout' || $status === 'Incomplete Logs') {
                    // Find exactly which pair has the missing checkout
                    foreach ($pairs as $index => $p) {
                        if ($p['in'] !== null && $p['out'] === null) {
                            $missingRecords[] = (object) [
                                'date' => $dateKey,
                                'employee' => $employees[$empId],
                                'check_in_time' => $p['in'],
                                'pair_index' => $index
                            ];
                        }
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

        $departments = \App\Department::all();
        $designations = \App\Designation::all();
        $shifts = \App\Shift::all();
        $locations = \App\Location::all();

        $employeesQuery = Employee::query();

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

        $employees = $employeesQuery->get()->keyBy('id');

        // Fetch all attendances in range
        $attendances = Attendance::whereBetween('created_at', [$fromDate . " 00:00:00", $toDate . " 23:59:59"])
            ->orderBy('created_at', 'ASC')
            ->get();

        $viewType = $req->view_type ?: 'daily';
        $sortBy = $req->sort_by ?: 'date';
        $sortDir = $req->sort_dir ?: 'desc';

        // Group by Date/Period, then by Employee
        $grouped = [];
        foreach ($attendances as $att) {
            $timestamp = strtotime($att->created_at);
            if ($viewType == 'monthly') {
                $dateKey = date('Y-m', $timestamp);
                $rawDate = date('Y-m-01', $timestamp);
            } else if ($viewType == 'total') {
                $dateKey = 'Total (' . date('M d', strtotime($fromDate)) . ' - ' . date('M d', strtotime($toDate)) . ')';
                $rawDate = $fromDate;
            } else {
                $dateKey = date('Y-m-d', $timestamp);
                $rawDate = date('Y-m-d', $timestamp);
            }
            
            if (!isset($grouped[$dateKey])) {
                $grouped[$dateKey] = ['raw_date' => $rawDate, 'employees' => []];
            }
            if (!isset($grouped[$dateKey]['employees'][$att->employee_id])) {
                $grouped[$dateKey]['employees'][$att->employee_id] = [];
            }
            $grouped[$dateKey]['employees'][$att->employee_id][] = $att;
        }

        $records = [];
        $grandTotalMinutes = 0;

        foreach ($grouped as $dateKey => $periodData) {
            foreach ($periodData['employees'] as $empId => $logs) {
                if (!isset($employees[$empId])) continue; // Skip if employee doesn't match search/department/locked

                $totalMinutes = 0;
                $checkInTime = null;
                $status = 'Complete';
                $pairs = [];

                foreach ($logs as $log) {
                    if ($log->type == 0) {
                        // Check In
                        $checkInTime = strtotime($log->created_at);
                        $pairs[] = [
                            'in' => $log->created_at,
                            'out' => null,
                            'minutes' => 0
                        ];
                    } else if ($log->type == 1) {
                        // Check Out
                        if ($checkInTime) {
                            $checkOutTime = strtotime($log->created_at);
                            $mins = round(($checkOutTime - $checkInTime) / 60);
                            $totalMinutes += $mins;
                            
                            if (count($pairs) > 0) {
                                $pairs[count($pairs) - 1]['out'] = $log->created_at;
                                $pairs[count($pairs) - 1]['minutes'] = $mins;
                            }
                            
                            $checkInTime = null; // Reset for next pair
                        } else {
                            $pairs[] = [
                                'in' => null,
                                'out' => $log->created_at,
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

                // If loop finished and checkInTime is still set, they missed a checkout
                if ($checkInTime !== null && $status == 'Complete') {
                    $status = 'Missing Checkout';
                }

                $hours = floor($totalMinutes / 60);
                $minutes = $totalMinutes % 60;
                $formattedTime = sprintf('%02d:%02d', $hours, $minutes);

                $grandTotalMinutes += $totalMinutes;

                $records[] = (object) [
                    'date' => $dateKey,
                    'raw_date' => $periodData['raw_date'],
                    'employee' => $employees[$empId],
                    'total_minutes' => $totalMinutes,
                    'formatted_time' => $formattedTime,
                    'status' => $status,
                    'view_type' => $viewType,
                    'pairs' => $pairs
                ];
            }
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
            return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\ViewExport(collect($records), 'admin.reports.working_hours_export', ['grandTotalMinutes' => $grandTotalMinutes]), 'working-hours-'.date('Y-m-d').'.xlsx');
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

        $allEmployees = Employee::all(['id', 'name', 'employee_id']);

        return view('admin.reports.working_hours', [
            'paginatedRows' => $paginatedRows,
            'viewType' => $viewType,
            'departments' => $departments,
            'designations' => $designations,
            'shifts' => $shifts,
            'locations' => $locations,
            'grandTotalHours' => $grandTotalHoursFormatted,
            'allEmployees' => $allEmployees
        ]);
    }
}
