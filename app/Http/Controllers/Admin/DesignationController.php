<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Designation;

class DesignationController extends Controller
{
    public function index()
    {
        $rows = Designation::all();
        return view('admin.designations.index', compact('rows'));
    }

    public function create()
    {
        return view('admin.designations.create');
    }

    public function store(Request $req)
    {
        $req->validate(['name' => 'required|max:255|unique:designations']);
        Designation::create(['name' => $req->name]);
        return redirect('admin/designations')->with('status', 'Designation created successfully');
    }

    public function edit(Designation $designation)
    {
        return view('admin.designations.edit', compact('designation'));
    }

    public function update(Request $req, Designation $designation)
    {
        $req->validate(['name' => 'required|max:255|unique:designations,name,' . $designation->id]);
        $designation->update(['name' => $req->name]);
        return redirect('admin/designations')->with('status', 'Designation updated successfully');
    }

    public function delete(Designation $designation)
    {
        $designation->delete();
        return redirect('admin/designations')->with('status', 'Designation deleted successfully');
    }
}
