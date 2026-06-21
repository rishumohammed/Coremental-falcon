<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

use App\Employee;
use App\Attendance;

class EmployeeController extends \App\Http\Controllers\Controller
{
    
    public function index(Request $req) 
    {
        $rows = \Auth::user()->employees;
        return $rows;
    }

    public function details(Request $req) 
    {
        $row = \Auth::user()->employees()->wherePersonId($req->get('person_id'))->first();
        return $row;
    }

    public function setPersonId(Employee $row, Request $req)
    {
        $rules = [
            'person_id'=>'required'
        ];

        $data = $req->all();

        $validator = \Validator::make($data, $rules);
        if($validator->fails())
        {
            return errRes($validator->errors()->toArray());
        }

        $row->update($data);

        return response()->json([
            'message'=>'Person ID set successfully'
        ]);
    }

    public function addFaceId(Employee $row, Request $req)
    {
        if($row->is_locked)
        {
            return response()->json([
                'message'=>'You can not add one more face id. Maximum face ids reached'
            ], 422);
        }

        $rules = [
            'face_id'=>'required'
        ];

        $data = $req->all();

        $validator = \Validator::make($data, $rules);
        if($validator->fails())
        {
            return errRes($validator->errors()->toArray());
        }

        $face_ids = $row->face_ids;
        $face_ids[] = $data['face_id'];
        $row->face_ids = $face_ids;

        if(count($face_ids)>=3)
        {
            $row->is_locked = 1;
        }

        $row->save();

        return response()->json([
            'message'=>'Face ID added successfully'
        ]);
    }

    public function clearPersonIds(Request $req)
    {
        $rules = [
            'person_ids'=>'required|array'
        ];

        $data = $req->all();

        $validator = \Validator::make($data, $rules);
        if($validator->fails())
        {
            return errRes($validator->errors()->toArray());
        }
        
        Employee::whereIn('person_id', $data['person_ids'])->update(['person_id'=>NULL]);

        return response()->json([
            'message'=>'Person IDs cleared successfully',
            'data'=>$data
        ]);
    }

    public function addCheckIn(Request $req)
    {
        $rules = [
            'entry_type'=>'required|in:0,1',
            'person_id'=>'required_if:entry_type,0|exists:employees',
            'employee_id'=>'required_if:entry_type,1|exists:employees',
            'photo'=>'required_if:entry_type,1|image',
            'lat'=>'nullable',
            'lng'=>'nullable',
            'device'=>'nullable'
        ];

        $data = $req->all();

        $validator = \Validator::make($data, $rules);
        if($validator->fails())
        {
            return errRes($validator->errors()->toArray());
        }
        
        $employee = false;
        if($data['entry_type'] == 0)
            $employee = \Auth::user()->employees()->wherePersonId($data['person_id'])->first();
        else
        {
            $employee = \Auth::user()->employees()
                                     ->where('employees.employee_id', $data['employee_id'])
                                     ->first();
        }

        if(isset($data['photo']) && $data['photo'])
        {
            $photo = $data['photo'];
            $filename = uniqid().time().'.'.$photo->extension();
            $photo->move('uploads/employee_attendance', $filename);
            $data['photo'] = $filename;
        }

        if(!$employee)
        {
            return response()->json([
                'message'=>'Employee does not assigned to the current user',
                'errors'=>[]
            ], 422);
        }

        $isBlocked = \App\EmployeeBlock::where('employee_id', $employee->id)
            ->whereDate('start_date', '<=', today())
            ->whereDate('end_date', '>=', today())
            ->first();

        if ($isBlocked) {
            return response()->json([
                'message' => 'Contact admin, you are temporarily blocked.',
                'errors' => []
            ], 422);
        }

        $isBlocked = \App\EmployeeBlock::where('employee_id', $employee->id)
            ->whereDate('start_date', '<=', today())
            ->whereDate('end_date', '>=', today())
            ->first();

        if ($isBlocked) {
            return response()->json([
                'message' => 'Contact admin, you are temporarily blocked.',
                'errors' => []
            ], 422);
        }

        $last_entry = Attendance::where('employee_id', $employee->id)->latest()->first();

        if($last_entry  && $last_entry->type == 0)
        {
            return response()->json([
                'message'=>'Consecutive check ins not allowed',
                'errors'=>[]
            ], 422);
        }

        $data['address'] = null;
        if($data['lat'] && !is_numeric($data['lat']))
        {
            $data['address'] = $data['lat'];
            $data['lat'] = null;            
        }
        
        $idata = [
            'employee_id'=>$employee->id,
            'type'=>0,
            'entry_type'=>$data['entry_type'],
            'lat'=>$data['lat'],
            'lng'=>$data['lng'],
            'address'=>$data['address'],
            'device'=>$data['device'],
            'user_id'=>\Auth::user()->id
        ];

        if(isset($data['photo']))
        {
            $idata['photo'] = $data['photo'];
        }

        Attendance::create($idata);

        return response()->json([
            'message'=>'Check In added successfully',
            'employee'=>$employee
        ]);
    }

    public function addCheckOut(Request $req)
    {
        $rules = [
            'entry_type'=>'required|in:0,1',
            'person_id'=>'required_if:entry_type,0|exists:employees',
            'employee_id'=>'required_if:entry_type,1|exists:employees',
            'photo'=>'nullable|required_if:entry_type,1|image',
            'lat'=>'nullable',
            'lng'=>'nullable',
            'device'=>'nullable'
        ];

        $data = $req->all();

        $validator = \Validator::make($data, $rules);
        if($validator->fails())
        {
            return errRes($validator->errors()->toArray());
        }

        $employee = false;
        if($data['entry_type'] == 0)
            $employee = \Auth::user()->employees()->wherePersonId($data['person_id'])->first();
        else
        {
            $employee = \Auth::user()->employees()
                                     ->where('employees.employee_id', $data['employee_id'])
                                     ->first();            
        }

        if(isset($data['photo']) && $data['photo'])
        {
            $photo = $data['photo'];
            $filename = uniqid().time().'.'.$photo->extension();
            $photo->move('uploads/employee_attendance', $filename);
            $data['photo'] = $filename;
        }

        if(!$employee)
        {
            return response()->json([
                    'message'=>'Employee does not assigned to the current user',
                    'errors'=>[]
                ], 422);
        }

        $last_entry = Attendance::where('employee_id', $employee->id)->latest()->first();

        if($last_entry && $last_entry->type == 1)
        {
            return response()->json([
                'message'=>'Consecutive check outs not allowed',
                'errors'=>[]
            ], 422);
        }

        $data['address'] = null;
        if($data['lat'] && !is_numeric($data['lat']))
        {
            $data['address'] = $data['lat'];
            $data['lat'] = null;            
        }
        
        $idata = [
            'employee_id'=>$employee->id,
            'type'=>1,
            'entry_type'=>$data['entry_type'],
            'lat'=>$data['lat'],
            'lng'=>$data['lng'],
            'address'=>$data['address'],
            'device'=>$data['device'],
            'user_id'=>\Auth::user()->id
        ];

        if(isset($data['photo']))
        {
            $idata['photo'] = $data['photo'];
        }

        Attendance::create($idata);


        return response()->json([
            'message'=>'Check Out added successfully',
            'employee'=>$employee
        ]);
    }
}
