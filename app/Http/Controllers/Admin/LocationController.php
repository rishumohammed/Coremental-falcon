<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Location;

class LocationController extends Controller
{
    public function index()
    {
        $rows = Location::all();
        return view('admin.locations.index', compact('rows'));
    }

    public function create()
    {
        return view('admin.locations.create');
    }

    public function store(Request $req)
    {
        $req->validate(['name' => 'required|max:255|unique:locations']);
        Location::create(['name' => $req->name]);
        return redirect('admin/locations')->with('status', 'Location created successfully');
    }

    public function edit(Location $location)
    {
        return view('admin.locations.edit', compact('location'));
    }

    public function update(Request $req, Location $location)
    {
        $req->validate(['name' => 'required|max:255|unique:locations,name,' . $location->id]);
        $location->update(['name' => $req->name]);
        return redirect('admin/locations')->with('status', 'Location updated successfully');
    }

    public function delete(Location $location)
    {
        $location->delete();
        return redirect('admin/locations')->with('status', 'Location deleted successfully');
    }
}
