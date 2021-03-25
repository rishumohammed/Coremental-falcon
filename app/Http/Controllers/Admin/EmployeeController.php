<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Employee;
use App\Attendance;

class EmployeeController extends \App\Http\Controllers\Controller
{    
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $rows = Employee::paginate(50);
        return view('admin.employees.index', compact('rows'));
    }

    public function create()
    {
        return view('admin.employees.create');
    }

    public function store(Request $req)
    {
        $data = $req->validate([
            'employee_id' => ['required', 'unique:employees'],
            'name' => ['required', 'string', 'max:255'],                        
            'is_locked' => ['required', 'in:0,1']
        ]);

        Employee::create($data);

        return redirect('admin/employees')->with('status', 'Employee added successfully');        
    }

    public function edit(Employee $row)
    {
        return view('admin.employees.edit', compact('row'));
    }

    public function update(Request $req, Employee $row)
    {
        $data = $req->validate([
            'employee_id' => ['required', 'unique:employees,employee_id,'.$row->id],
            'name' => ['required', 'string', 'max:255'],                        
            'is_locked' => ['required', 'in:0,1']
        ]);

        $row->update($data);

        return redirect('admin/employees')->with('status', 'Employee updated successfully');        
    }

    public function delete(Employee $row)
    {
        if($row->person_id)
        {
            return redirect()->back()->with('error', 'Person ID exists. You have to delete Person ID using Mobile App to delete this employee.');
        }
        $row->attendances()->delete();
        $row->delete();

        return redirect()->back()->with('status', 'Employee data deleted successfully');
    }

    public function attendance()
    {
        $rows = Attendance::orderBy('id', 'DESC')->paginate(100);
        return view('admin.employees.attendance', compact('rows'));
    }
}
