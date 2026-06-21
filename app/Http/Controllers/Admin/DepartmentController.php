<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Department;

class DepartmentController extends Controller
{
    public function index()
    {
        $rows = Department::all();
        return view('admin.departments.index', compact('rows'));
    }

    public function create()
    {
        return view('admin.departments.create');
    }

    public function store(Request $req)
    {
        $req->validate(['name' => 'required|max:255|unique:departments']);
        Department::create(['name' => $req->name]);
        return redirect('admin/departments')->with('status', 'Department created successfully');
    }

    public function edit(Department $department)
    {
        return view('admin.departments.edit', compact('department'));
    }

    public function update(Request $req, Department $department)
    {
        $req->validate(['name' => 'required|max:255|unique:departments,name,' . $department->id]);
        $department->update(['name' => $req->name]);
        return redirect('admin/departments')->with('status', 'Department updated successfully');
    }

    public function delete(Department $department)
    {
        $department->delete();
        return redirect('admin/departments')->with('status', 'Department deleted successfully');
    }
}
