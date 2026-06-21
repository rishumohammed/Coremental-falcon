<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\LeaveType;

class LeaveTypeController extends Controller
{
    public function index()
    {
        $rows = LeaveType::all();
        return view('admin.leave_types.index', compact('rows'));
    }

    public function create()
    {
        return view('admin.leave_types.create');
    }

    public function store(Request $req)
    {
        $req->validate(['name' => 'required|max:255']);
        LeaveType::create(['name' => $req->name]);
        return redirect('admin/leave-types')->with('status', 'Leave type created successfully');
    }

    public function edit(LeaveType $leaveType)
    {
        return view('admin.leave_types.edit', compact('leaveType'));
    }

    public function update(Request $req, LeaveType $leaveType)
    {
        $req->validate(['name' => 'required|max:255']);
        $leaveType->update(['name' => $req->name]);
        return redirect('admin/leave-types')->with('status', 'Leave type updated successfully');
    }

    public function delete(LeaveType $leaveType)
    {
        $leaveType->delete();
        return redirect('admin/leave-types')->with('status', 'Leave type deleted successfully');
    }

    public function setDefault(LeaveType $leaveType)
    {
        LeaveType::where('id', '!=', $leaveType->id)->update(['is_default' => false]);
        $leaveType->update(['is_default' => true]);

        return redirect('admin/leave-types')->with('status', $leaveType->name . ' is now the default leave type.');
    }
}
