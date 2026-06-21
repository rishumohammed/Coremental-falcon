<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\MeetingAttendance;

class SalesmanController extends \App\Http\Controllers\Controller
{    
    public function meetingAttendance(Request $req)
    {
        $req->flash();

        $salesmans = \App\User::whereType('salesman')->get();

        $rows = MeetingAttendance::select('*');

        if($req->search) {
            $rows->where(function($q) use ($req) {
                $q->where('customer_name', 'like', "%{$req->search}%")
                  ->orWhere('purpose', 'like', "%{$req->search}%")
                  ->orWhere('meeting_notes', 'like', "%{$req->search}%");
            });
        }

        if($req->salesman_id)
            $rows->where('salesman_id', $req->salesman_id);

        if($req->has('type') && $req->type != '')
            $rows->where('type', $req->type);

        if($req->from_date)
            $rows->whereDate('created_at', '>=', $req->from_date);
        
        if($req->to_date)
            $rows->whereDate('created_at', '<=', $req->to_date);

        if(!$req->export)
        {
            $rows = $rows->orderBy('id', 'DESC')->paginate(100);
            return view('admin.salesman.attendance', compact('rows', 'salesmans'));
        }
        else
        {
            $rows = $rows->orderBy('created_at')->get();
            return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\ViewExport($rows, 'admin.salesman.attendance-export'), 'meeting-attendance-'.date('Y-m-d').'.xlsx');
        }
    }

}
