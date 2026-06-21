<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
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
        if (auth()->user()->type == 'admin') {
            return redirect('/admin');
        }

        return view('home', [
            'metrics' => [
                'total_employees' => 0,
                'total_users' => 0,
                'today_employee_attendance' => 0,
                'today_salesman_attendance' => 0,
                'present_today' => 0,
                'absent_today' => 0,
                'missing_checkouts_yesterday' => 0,
            ],
            'attendanceTrend' => [],
            'departmentDistribution' => [],
            'latestMissingCheckouts' => []
        ]);
    }
}
