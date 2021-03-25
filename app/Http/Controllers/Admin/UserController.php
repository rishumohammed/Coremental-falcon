<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\User;
use App\Employee;
use App\AssignedEmployee;

class UserController extends \App\Http\Controllers\Controller
{    
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $rows = User::where('type', '!=', 'admin')->paginate(50);
        return view('admin.users.index', compact('rows'));
    }

    public function create()
    {
        $employee_ids = Employee::pluck('employee_id');
        return view('admin.users.create', compact('employee_ids'));
    }

    public function store(Request $req)
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'location' => ['nullable'],
            'geo_location' => ['nullable', 'regex:/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?),[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/'],
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'type' => ['required', 'in:general,salesman'],
            'password' => ['required', 'string', 'min:8', 'confirmed']
        ];

        if($req->has('type') && $req->get('type')=='salesman')
        {
            $rules['employee_id'] = ['required', 'unique:users,employee_id'];
        }

        $data = $req->validate($rules);

        unset($data['password_confirmation']);
        $data['password'] = \Hash::make($data['password']);
        $row = User::create($data);

        /*Employee::create([
            'employee_id' => $row->employee_id,
            'name' => $row->name,
            'is_locked' => false,
            'is_salesman' => true
        ]);*/

        return redirect('admin/users')->with('status', 'User added successfully');
    }

    public function edit(User $row)
    {
        if($row->type == 'admin')
            abort(404);

        $employee_ids = Employee::pluck('employee_id');

        return view('admin.users.edit', compact('row', 'employee_ids'));
    }

    public function update(Request $req, User $row)
    {
        $employee = Employee::whereEmployeeId($row->employee_id)->first();

        $employee_row_id = $employee?$employee->id:0;

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'location' => ['nullable'],
            'geo_location' => ['nullable', 'regex:/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?),[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username,'.$row->id],            
            'password' => ['nullable', 'string', 'min:8', 'confirmed']
        ];

        if($employee->type == 'salesman')
        {
            $rules['employee_id'] = ['required', 'unique:employees,employee_id,'.$employee->id];
        }

        $data = $req->validate($rules);
       

        if($data['password'])
        {
            unset($data['password_confirmation']);
            $data['password'] = \Hash::make($data['password']);
        }
        else
        {
            unset($data['password']);
            unset($data['password_confirmation']);
        }
        
        $row->update($data);

        /*$row->employee()->update([
            'employee_id' => $row->employee_id,
            'name' => $row->name
        ]);*/

        return redirect('admin/users')->with('status', 'User updated successfully');
    }

    public function delete(User $row)
    {
        if($row->type == 'admin')
            abort(404);
            
        //$row->attendances()->delete();
        //$row->meetings()->delete();
        $row->delete();

        return redirect()->back()->with('status', 'User data deleted successfully');
    }


    public function assignedEmployees(User $row)
    {
        $unassigned_employees = Employee::whereNotIn('id', function($qry)
                                    use($row){
                                    $qry->select('employee_id')
                                        ->from('assigned_employees')
                                        ->where('user_id', $row->id);
                                })->get();
        $user = $row;
        $rows = $row->employees()->paginate(50);
        return view('admin.users.assigned-employees.index', compact('user', 'rows',  'unassigned_employees'));
    }

    public function assignEmployee(Request $req, User $user)
    {
        $data = $req->validate([
            'employee_ids' => ['required', 'array']
        ]);

        $user->employees()->attach($data['employee_ids']);
        
        return redirect('admin/users/'.$user->id.'/assigned-employees')->with('status', 'Employee assigned successfully');
    }

    public function unassignEmployee(User $user, $employee_id)
    {
        $user->employees()->detach(['employee_id'=>$employee_id]);
        
        return redirect('admin/users/'.$user->id.'/assigned-employees')->with('status', 'Employee assignement deleted successfully');
    }

}
