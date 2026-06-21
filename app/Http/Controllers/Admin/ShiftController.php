<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Shift;

class ShiftController extends Controller
{
    public function index()
    {
        $rows = Shift::all();
        return view('admin.shifts.index', compact('rows'));
    }

    public function create()
    {
        return view('admin.shifts.create');
    }

    public function store(Request $req)
    {
        $req->validate(['name' => 'required|max:255|unique:shifts']);
        Shift::create(['name' => $req->name]);
        return redirect('admin/shifts')->with('status', 'Shift created successfully');
    }

    public function edit(Shift $shift)
    {
        return view('admin.shifts.edit', compact('shift'));
    }

    public function update(Request $req, Shift $shift)
    {
        $req->validate(['name' => 'required|max:255|unique:shifts,name,' . $shift->id]);
        $shift->update(['name' => $req->name]);
        return redirect('admin/shifts')->with('status', 'Shift updated successfully');
    }

    public function delete(Shift $shift)
    {
        $shift->delete();
        return redirect('admin/shifts')->with('status', 'Shift deleted successfully');
    }
}
