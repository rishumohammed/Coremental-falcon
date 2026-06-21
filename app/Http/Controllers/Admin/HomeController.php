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
        $presentToday = \App\Attendance::whereDate('created_at', $today)
            ->where('type', 0) // Check-in
            ->distinct('employee_id')
            ->count('employee_id');

        $absentToday = max(0, $totalEmployees - $presentToday);

        $attendances = \App\Attendance::select('employee_id', 'type', 'created_at', 'id')
            ->orderBy('created_at', 'asc')
            ->get();
            
        $employees = \App\Employee::where('is_locked', false)->get()->keyBy('id');

        $grouped = [];
        foreach ($attendances as $log) {
            $dateKey = date('Y-m-d', strtotime($log->created_at->timezone(timezone())));
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
                $status = 'Complete';
                $pairs = [];

                foreach ($logs as $log) {
                    $logTime = date('Y-m-d H:i:s', strtotime($log->created_at->timezone(timezone())));
                    if ($log->type == 0) {
                        if ($checkInTime !== null) {
                            $pairs[] = ['in' => $checkInTime, 'out' => null];
                        }
                        $checkInTime = $logTime;
                    } else if ($log->type == 1) {
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
                    foreach ($pairs as $index => $p) {
                        if ($p['in'] !== null && $p['out'] === null) {
                            $missingRecords[] = (object) [
                                'date' => $dateKey,
                                'employee' => $employees[$empId],
                                'check_in_time' => $p['in']
                            ];
                        }
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
                'date' => date('M d', strtotime($date)),
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
}
