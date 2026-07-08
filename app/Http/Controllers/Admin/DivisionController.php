<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Division;

class DivisionController extends Controller
{
    public function index()
    {
        $rows = Division::all();
        return view('admin.divisions.index', compact('rows'));
    }

    public function create()
    {
        return view('admin.divisions.create');
    }

    public function store(Request $req)
    {
        $req->validate(['name' => 'required|max:255|unique:divisions']);
        Division::create(['name' => $req->name]);
        \Cache::forget('divisions');
        return redirect('admin/divisions')->with('status', 'Division created successfully');
    }

    public function edit(Division $division)
    {
        return view('admin.divisions.edit', compact('division'));
    }

    public function update(Request $req, Division $division)
    {
        $req->validate(['name' => 'required|max:255|unique:divisions,name,' . $division->id]);
        $division->update(['name' => $req->name]);
        \Cache::forget('divisions');
        return redirect('admin/divisions')->with('status', 'Division updated successfully');
    }

    public function delete(Division $division)
    {
        $division->delete();
        \Cache::forget('divisions');
        return redirect('admin/divisions')->with('status', 'Division deleted successfully');
    }
}
