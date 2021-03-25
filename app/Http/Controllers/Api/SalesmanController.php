<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

use App\User;
use App\MeetingAttendance;
use App\Attendance;

class SalesmanController extends \App\Http\Controllers\Controller
{    
    public function setPersonId(Request $req)
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

        \Auth::user()->update($data);
        \Auth::user()->employee()->update($data);

        return response()->json([
            'message'=>'Person ID set successfully'
        ]);
    }

    public function addFaceId(Request $req)
    {
        if(\Auth::user()->is_locked)
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
        
        $face_ids = \Auth::user()->face_ids;
        $face_ids[] = $data['face_id'];

        $udata = ['face_ids' => $face_ids];

        if(count($face_ids)>=3)
        {
            $udata['is_locked'] = 1;
        }

        \Auth::user()->update($udata);
        \Auth::user()->employee()->update($data);

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
        
        User::whereType('type', 'salesman')
            ->whereIn('person_id', $data['person_ids'])
            ->update(['person_id'=>NULL]);

        Employee::whereIn('person_id', $data['person_ids'])
                ->update(['person_id'=>NULL]);

        return response()->json([
            'message'=>'Person IDs cleared successfully',
            'data'=>$data
        ]);
    }

    public function addMeetingCheckIn(Request $req)
    {
        $rules = [
            'entry_type'=>'required|in:0,1',
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

        if(isset($data['photo']) && $data['photo'])
        {
            $photo = $data['photo'];
            $filename = uniqid().time().'.'.$photo->extension();
            $photo->move('uploads/meeting_attendance', $filename);
            $data['photo'] = $filename;
        }

        $last_entry = MeetingAttendance::where('salesman_id', \Auth::user()->id)->latest()->first();

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
            'salesman_id'=>\Auth::user()->id,
            'type'=>0,
            'entry_type'=>$data['entry_type'],
            'lat'=>$data['lat'],
            'lng'=>$data['lng'],
            'address'=>$data['address'],
            'device'=>$data['device']
        ];

        if(isset($data['photo']))
        {
            $idata['photo'] = $data['photo'];
        }

        MeetingAttendance::create($idata);

        return response()->json([
            'message'=>'Check In added successfully'
        ]);
    }

    public function addMeetingCheckOut(Request $req)
    {
        $rules = [
            'entry_type'=>'required|in:0,1',
            'photo'=>'required_if:entry_type,1|image',
            'lat'=>'nullable',
            'lng'=>'nullable',
            'customer_name'=>'required',
            'purpose'=>'required',
            'meeting_notes'=>'required',
            'device'=>'nullable'
        ];

        $data = $req->all();

        $validator = \Validator::make($data, $rules);
        if($validator->fails())
        {
            return errRes($validator->errors()->toArray());
        }

        if(isset($data['photo']) && $data['photo'])
        {
            $photo = $data['photo'];
            $filename = uniqid().time().'.'.$photo->extension();
            $photo->move('uploads/meeting_attendance', $filename);
            $data['photo'] = $filename;
        }

        $last_entry = MeetingAttendance::where('salesman_id', \Auth::user()->id)->latest()->first();

        if($last_entry  && $last_entry->type == 1)
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
            'salesman_id'=>\Auth::user()->id,
            'type'=>1,
            'entry_type'=>$data['entry_type'],
            'lat'=>$data['lat'],
            'lng'=>$data['lng'],
            'address'=>$data['address'],
            'customer_name' => $data['customer_name'],
            'purpose' => $data['purpose'],
            'meeting_notes' => $data['meeting_notes'],
            'device'=>$data['device']
        ];

        if(isset($data['photo']) && $data['photo'])
        {
            $idata['photo'] = $data['photo'];
        }

        MeetingAttendance::create($idata);

        return response()->json([
            'message'=>'Check Out added successfully'
        ]);
    }

    public function addCheckIn(Request $req)
    {
        $rules = [
            'entry_type'=>'required|in:0,1',
            'photo'=>'nullable|required_if:entry_type,1|image',
            'lat'=>'nullable',
            'lng'=>'nullable',
            'device'=>'nullable',
        ];

        $data = $req->all();

        $validator = \Validator::make($data, $rules);
        if($validator->fails())
        {
            return errRes($validator->errors()->toArray());
        }

        if(isset($data['photo']) && $data['photo'])
        {
            $photo = $data['photo'];
            $filename = uniqid().time().'.'.$photo->extension();
            $photo->move('uploads/employee_attendance', $filename);
            $data['photo'] = $filename;
        }

        $last_entry = Attendance::where('employee_id', \Auth::user()->employee->id)->latest()->first();

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
            'employee_id'=>\Auth::user()->employee->id,
            'type'=>0,
            'entry_type'=>$data['entry_type'],
            'lat'=>$data['lat'],
            'lng'=>$data['lng'],
            'address'=>$data['address'],
            'device'=>$data['device']
        ];

        if(isset($data['photo']) && $data['photo'])
        {
            $idata['photo'] = $data['photo'];
        }

        Attendance::create($idata);

        return response()->json([
            'message'=>'Check In added successfully'
        ]);
    }
    
    public function addCheckOut(Request $req)
    {
        $rules = [
            'entry_type'=>'required|in:0,1',
            'photo'=>'nullable|required_if:entry_type,1|image',
            'lat'=>'nullable',
            'lng'=>'nullable',
            'device'=>'nullable',
        ];

        $data = $req->all();

        $validator = \Validator::make($data, $rules);
        if($validator->fails())
        {
            return errRes($validator->errors()->toArray());
        }

        if(isset($data['photo']) && $data['photo'])
        {
            $photo = $data['photo'];
            $filename = uniqid().time().'.'.$photo->extension();
            $photo->move('uploads/employee_attendance', $filename);
            $data['photo'] = $filename;
        }

        $last_entry = Attendance::where('employee_id', \Auth::user()->employee->id)->latest()->first();

        if($last_entry  && $last_entry->type == 1)
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
            'employee_id'=>\Auth::user()->employee->id,
            'type'=>1,
            'entry_type'=>$data['entry_type'],
            'lat'=>$data['lat'],
            'lng'=>$data['lng'],
            'address'=>$data['address'],
            'device'=>$data['device']
        ];

        if(isset($data['photo']) && $data['photo'])
        {
            $idata['photo'] = $data['photo'];
        }

        Attendance::create($idata);

        return response()->json([
            'message'=>'Check Out added successfully'
        ]);
    }
    
}
