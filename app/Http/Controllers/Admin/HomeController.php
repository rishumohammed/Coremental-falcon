<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

class HomeController extends \App\Http\Controllers\Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));

        $totalEmployees = \App\Employee::where('is_locked', false)->count();

        // Calculate Present Today (unique employees checked in today)
        $presentToday = \App\Attendance::where('created_at', '>=', $today . ' 00:00:00')
            ->where('created_at', '<=', $today . ' 23:59:59')
            ->where('type', 0) // Check-in
            ->distinct('employee_id')
            ->count('employee_id');

        $absentToday = max(0, $totalEmployees - $presentToday);

        $sevenDaysAgo = date('Y-m-d', strtotime('-7 days'));
        $attendances = \App\Attendance::select('employee_id', 'type', 'created_at', 'id')
            ->where('created_at', '>=', $sevenDaysAgo . ' 00:00:00')
            ->orderBy('created_at', 'asc')
            ->get();
            
        // Load employees with their shift relationship for shift-aware evaluation
        $employees = \App\Employee::select('id', 'name', 'employee_id', 'shift_id')
            ->where('is_locked', false)
            ->with('shift')
            ->get()
            ->keyBy('id');

        $tz = timezone();
        $grouped = [];
        foreach ($attendances as $log) {
            $dateKey = $log->created_at->timezone($tz)->format('Y-m-d');
            $empId = $log->employee_id;
            
            if (!isset($grouped[$dateKey])) {
                $grouped[$dateKey] = ['employees' => []];
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
                    if ($log->type == 0) { // Check-In
                        if ($checkInTime !== null) {
                            $pairs[] = ['in' => $checkInTime, 'out' => null];
                        }
                        $checkInTime = $logTime;
                    } else if ($log->type == 1) { // Check-Out
                        if ($checkInTime !== null) {
                            $pairs[] = ['in' => $checkInTime, 'out' => $logTime];
                            $checkInTime = null;
                        } else {
                            $pairs[] = ['in' => null, 'out' => $logTime];
                        }
                    }
                }

                // Append any open check-in with no matching check-out
                if ($checkInTime !== null) {
                    $pairs[] = ['in' => $checkInTime, 'out' => null];
                }

                $employeeObj = $employees[$empId];
                foreach ($pairs as $p) {
                    if ($this->isPairMissingCheckout($dateKey, $p, $employeeObj)) {
                        $missingRecords[] = (object) [
                            'date'           => $dateKey,
                            'employee'       => $employeeObj,
                            'check_in_time'  => $p['in']
                        ];
                    }
                }
            }
        }

        usort($missingRecords, function($a, $b) {
            return strtotime($b->date) <=> strtotime($a->date);
        });

        // Count for yesterday to keep the KPI accurate
        $missingCheckoutsYesterdayCount = 0;
        foreach ($missingRecords as $record) {
            if ($record->date == $yesterday) {
                $missingCheckoutsYesterdayCount++;
            }
        }

        $latestMissingCheckouts = array_slice($missingRecords, 0, 10);

        $metrics = [
            'total_employees' => $totalEmployees,
            'present_today' => $presentToday,
            'absent_today' => $absentToday,
            'missing_checkouts_yesterday' => $missingCheckoutsYesterdayCount,
        ];

        // 1. Attendance Trend (Last 7 Days)
        $attendanceTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $count = \App\Attendance::whereDate('created_at', $date)
                ->where('type', 0)
                ->distinct('employee_id')
                ->count('employee_id');
            $attendanceTrend[] = [
                'date'  => date('M d', strtotime($date)),
                'count' => $count
            ];
        }

        // 2. Department Distribution
        $departmentDistribution = \DB::table('employees')
            ->join('departments', 'employees.department_id', '=', 'departments.id')
            ->where('employees.is_locked', false)
            ->select('departments.name', \DB::raw('count(*) as total'))
            ->groupBy('department_id', 'departments.name')
            ->get();

        return view('home', compact('metrics', 'attendanceTrend', 'departmentDistribution', 'latestMissingCheckouts'));
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
     * @param string $dateKey   Attendance date (Y-m-d).
     * @param array  $pair      ['in' => 'Y-m-d H:i:s'|null, 'out' => 'Y-m-d H:i:s'|null]
     * @param mixed  $employee  Employee model with 'shift' relation eager-loaded.
     * @return bool
     */
    private function isPairMissingCheckout(string $dateKey, array $pair, $employee): bool
    {
        // Only flag open pairs (has check-in, no check-out)
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
