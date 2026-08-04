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
    public function index(Request $req)
    {
        $rows = Employee::query()->with(['department', 'division', 'designation', 'shift', 'location']);
        
        if ($req->search) {
            $rows->where(function($q) use ($req) {
                $q->where('employees.name', 'like', "%{$req->search}%")
                  ->orWhere('employees.employee_id', 'like', "%{$req->search}%")
                  ->orWhere('employees.person_id', 'like', "%{$req->search}%")
                  ->orWhereHas('department', function($q2) use ($req) {
                      $q2->where('departments.name', 'like', "%{$req->search}%");
                  });
            });
        }
        
        if ($req->has('status') && $req->status != '') {
            $rows->where('is_locked', $req->status);
        }

        if ($req->has('department_id') && $req->department_id != '') {
            $rows->where('department_id', $req->department_id);
        }

        if ($req->has('division_id') && $req->division_id != '') {
            $rows->where('division_id', $req->division_id);
        }
        
        if ($req->has('designation_id') && $req->designation_id != '') {
            $rows->where('designation_id', $req->designation_id);
        }
        
        if ($req->has('shift_id') && $req->shift_id != '') {
            $rows->where('shift_id', $req->shift_id);
        }
        
        if ($req->has('location_id') && $req->location_id != '') {
            $rows->where('location_id', $req->location_id);
        }

        $rows = $rows->paginate(50);
        $departments = \Cache::rememberForever('departments', fn() => \App\Department::all());
        $divisions = \Cache::rememberForever('divisions', fn() => \App\Division::all());
        $designations = \Cache::rememberForever('designations', fn() => \App\Designation::all());
        $shifts = \Cache::rememberForever('shifts', fn() => \App\Shift::all());
        $locations = \Cache::rememberForever('locations', fn() => \App\Location::all());
        $allEmployees = Employee::all(['id', 'name', 'employee_id']);
        return view('admin.employees.index', compact('rows', 'departments', 'divisions', 'designations', 'shifts', 'locations', 'allEmployees'));
    }

    public function create()
    {
        $departments = \Cache::rememberForever('departments', fn() => \App\Department::all());
        $designations = \Cache::rememberForever('designations', fn() => \App\Designation::all());
        $shifts = \Cache::rememberForever('shifts', fn() => \App\Shift::all());
        $locations = \Cache::rememberForever('locations', fn() => \App\Location::all());
        $divisions = \Cache::rememberForever('divisions', fn() => \App\Division::all());
        return view('admin.employees.create', compact('departments', 'designations', 'shifts', 'locations', 'divisions'));
    }

    public function store(Request $req)
    {
        $data = $req->validate([
            'employee_id' => ['required', 'unique:employees'],
            'name' => ['required', 'string', 'max:255'],                        
            'department_id' => ['nullable', 'exists:departments,id'],
            'designation_id' => ['nullable', 'exists:designations,id'],
            'shift_id' => ['nullable', 'exists:shifts,id'],
            'location_id' => ['nullable', 'exists:locations,id'],
            'division_id' => ['nullable', 'exists:divisions,id'],
            'id_number' => ['nullable', 'string', 'max:255'],
            'weekend_days' => ['nullable', 'array'],
            'weekend_days.*' => ['integer', 'min:0', 'max:6'],
            'is_locked' => ['required', 'in:0,1']
        ]);

        if (isset($data['weekend_days'])) {
            $data['weekend_days'] = implode(',', $data['weekend_days']);
        } else {
            $data['weekend_days'] = null;
        }

        Employee::create($data);

        return redirect('admin/employees')->with('status', 'Employee added successfully');        
    }

    public function edit(Employee $row)
    {
        $departments = \Cache::rememberForever('departments', fn() => \App\Department::all());
        $designations = \Cache::rememberForever('designations', fn() => \App\Designation::all());
        $shifts = \Cache::rememberForever('shifts', fn() => \App\Shift::all());
        $locations = \Cache::rememberForever('locations', fn() => \App\Location::all());
        $divisions = \Cache::rememberForever('divisions', fn() => \App\Division::all());
        return view('admin.employees.edit', compact('row', 'departments', 'designations', 'shifts', 'locations', 'divisions'));
    }

    public function update(Request $req, Employee $row)
    {
        $data = $req->validate([
            'employee_id' => ['required', 'unique:employees,employee_id,'.$row->id],
            'name' => ['required', 'string', 'max:255'],                        
            'department_id' => ['nullable', 'exists:departments,id'],
            'designation_id' => ['nullable', 'exists:designations,id'],
            'shift_id' => ['nullable', 'exists:shifts,id'],
            'location_id' => ['nullable', 'exists:locations,id'],
            'division_id' => ['nullable', 'exists:divisions,id'],
            'id_number' => ['nullable', 'string', 'max:255'],
            'weekend_days' => ['nullable', 'array'],
            'weekend_days.*' => ['integer', 'min:0', 'max:6'],
            'is_locked' => ['required', 'in:0,1']
        ]);

        if (isset($data['weekend_days'])) {
            $data['weekend_days'] = implode(',', $data['weekend_days']);
        } else {
            $data['weekend_days'] = null;
        }

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

    public function attendance(Request $req)
    {
        $req->flash();

        $employees = \App\Employee::select('id', 'name', 'employee_id')->get();
        $departments = \Cache::rememberForever('departments', fn() => \App\Department::all());
        $designations = \Cache::rememberForever('designations', fn() => \App\Designation::all());
        $shifts = \Cache::rememberForever('shifts', fn() => \App\Shift::all());
        $locations = \Cache::rememberForever('locations', fn() => \App\Location::all());
        $divisions = \Cache::rememberForever('divisions', fn() => \App\Division::all());
        $adminUsers = \App\User::orderBy('username')->get();

        $rows = Attendance::with(['employee.department', 'employee.designation', 'employee.shift', 'employee.location', 'employee.division', 'user']);

        if($req->search) {
            $rows->where(function($q) use ($req) {
                $q->whereHas('employee', function($q2) use ($req) {
                    $q2->where('name', 'like', "%{$req->search}%")
                       ->orWhere('employee_id', 'like', "%{$req->search}%");
                });
            });
        }

        if($req->employee_id)
            $rows->where('employee_id', $req->employee_id);

        if($req->has('type') && $req->type != '')
            $rows->where('type', $req->type);

        if($req->has('user_id') && $req->user_id != '') {
            $rows->whereHas('employee.users', function($q) use ($req) {
                $q->where('users.id', $req->user_id);
            });
        }

        if($req->has('department_id') || $req->has('designation_id') || $req->has('shift_id') || $req->has('location_id') || $req->has('division_id')) {
            $rows->whereHas('employee', function($q) use ($req) {
                if ($req->has('department_id') && $req->department_id != '') $q->where('department_id', $req->department_id);
                if ($req->has('designation_id') && $req->designation_id != '') $q->where('designation_id', $req->designation_id);
                if ($req->has('shift_id') && $req->shift_id != '') $q->where('shift_id', $req->shift_id);
                if ($req->has('location_id') && $req->location_id != '') $q->where('location_id', $req->location_id);
                if ($req->has('division_id') && $req->division_id != '') $q->where('division_id', $req->division_id);
            });
        }

        if($req->from_date)
            $rows->where('created_at', '>=', $req->from_date . ' 00:00:00');
        
        if($req->to_date)
            $rows->where('created_at', '<=', $req->to_date . ' 23:59:59');

        if(!$req->export)
        {
            $rows = $rows->orderBy('id', 'DESC')->paginate(100);
            return view('admin.employees.attendance', compact('rows', 'employees', 'departments', 'designations', 'shifts', 'locations', 'divisions', 'adminUsers'));
        }
        else
        {
            $rows = $rows->orderBy('created_at')->get();
            return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\ViewExport($rows, 'admin.employees.attendance-export'), 'employee-attendance-'.date('Y-m-d').'.xlsx');
        }
    }

    public function blocks(\App\Employee $row)
    {
        $blocks = \App\EmployeeBlock::with('leaveType')->where('employee_id', $row->id)->orderBy('start_date', 'DESC')->get();
        $leaveTypes = \Cache::rememberForever('leave_types', fn() => \App\LeaveType::all());
        return view('admin.employees.blocks', compact('row', 'blocks', 'leaveTypes'));
    }

    public function storeBlock(\Illuminate\Http\Request $req, \App\Employee $row)
    {
        $req->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);

        \App\EmployeeBlock::create([
            'employee_id' => $row->id,
            'leave_type_id' => $req->leave_type_id,
            'start_date' => $req->start_date,
            'end_date' => $req->end_date
        ]);

        return back()->with('status', 'Employee blocked successfully.');
    }

    public function deleteBlock(\App\EmployeeBlock $block)
    {
        $block->delete();
        return back()->with('status', 'Block removed successfully.');
    }
}
