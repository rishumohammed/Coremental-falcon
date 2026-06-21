<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\MeetingAttendance;

class SalesmanController extends \App\Http\Controllers\Controller
{    
    public function meetingAttendance()
    {
        $rows = MeetingAttendance::orderBy('id', 'DESC')->paginate(100);
        return view('admin.salesman.attendance', compact('rows'));
    }
}
