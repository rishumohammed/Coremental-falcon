<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\User;
use App\Employee;
use App\MeetingAttendance;
use App\Attendance;
use Illuminate\Support\Facades\Log;

class SalesmanController extends \App\Http\Controllers\Controller
{    
    protected function validateGeofence($reqLat, $reqLng)
    {
        $user = \Auth::user();
        if (!$user || !$user->geo_location) return true;

        $parts = explode(',', $user->geo_location);
        if (count($parts) != 2) return true;

        $userLat = trim($parts[0]);
        $userLng = trim($parts[1]);

        if (!$reqLat || !$reqLng) return false;

        $earthRadius = 6371000; // meters
        $lat1 = deg2rad((float)$userLat);
        $lon1 = deg2rad((float)$userLng);
        $lat2 = deg2rad((float)$reqLat);
        $lon2 = deg2rad((float)$reqLng);

        $latDelta = $lat2 - $lat1;
        $lonDelta = $lon2 - $lon1;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($lonDelta / 2), 2)));
        $distance = $angle * $earthRadius;

        $radius = 50;
        try {
            $radius = $user->geo_radius ?? \DB::table('settings')->where('key', 'geo_radius')->value('val') ?? 50;
        } catch (\Throwable $e) {
            $radius = $user->geo_radius ?? 50;
        }

        return $distance <= $radius;
    }

    private function getSalesmanEmployee(?Request $req = null)
    {
        $user = \Auth::user();
        $employee = null;
        if ($user) {
            $employee = $user->employee;
            if (!$employee && $user->employee_id) {
                $employee = Employee::where('employee_id', $user->employee_id)->first();
            }
        }
        if (!$employee && $req) {
            $personId = trim($req->input('person_id') ?? '');
            $employeeId = trim($req->input('employee_id') ?? '');
            if (!empty($personId)) {
                $employee = Employee::where('person_id', $personId)->first();
                if (!$employee && strlen($personId) > 36) {
                    $extractedId = (int)substr($personId, 36);
                    if ($extractedId > 0) {
                        $employee = Employee::find($extractedId);
                    }
                }
                if (!$employee && strpos($personId, '.') !== false) {
                    $parts = explode('.', $personId);
                    if (strlen($parts[0]) > 36) {
                        $employee = Employee::find((int)substr($parts[0], 36));
                    }
                    if (!$employee && !empty($parts[1])) {
                        $employee = Employee::where('employee_id', $parts[1])->first();
                    }
                }
                if (!$employee && preg_match('/(\d+)$/', $personId, $matches)) {
                    $employee = Employee::find((int)$matches[1]);
                }
            }
            if (!$employee && !empty($employeeId)) {
                $employee = Employee::where('employee_id', $employeeId)->first();
            }
        }
        if (!$employee && $user) {
            $employee = $user->employees()->first();
        }
        if (!$employee) {
            $employee = Employee::first();
        }
        return $employee;
    }

    public function setPersonId(Request $req)
    {
        try {
            $rules = [
                'person_id'=>'required'
            ];

            $data = $req->all();

            $validator = \Validator::make($data, $rules);
            if($validator->fails())
            {
                return response()->json([
                    'message' => 'The person_id field is required.',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = \Auth::user();
            if ($user) {
                $user->update($data);
            }
            $employee = $this->getSalesmanEmployee($req);
            if ($employee) {
                $employee->update($data);
            }

            return response()->json([
                'message'=>'Person ID set successfully'
            ]);
        } catch (\Throwable $e) {
            Log::error('Salesman setPersonId error: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function addFaceId(Request $req)
    {
        try {
            $user = \Auth::user();
            if($user && $user->is_locked)
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
                return response()->json([
                    'message' => 'The face_id field is required.',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $face_ids = ($user ? $user->face_ids : []) ?? [];
            $face_ids[] = $data['face_id'];

            $udata = ['face_ids' => $face_ids];

            if(count($face_ids)>=3)
            {
                $udata['is_locked'] = 1;
            }

            if ($user) {
                $user->update($udata);
            }
            $employee = $this->getSalesmanEmployee($req);
            if ($employee) {
                $employee->update([
                    'face_ids' => $face_ids,
                    'is_locked' => count($face_ids)>=3 ? 1 : 0
                ]);
            }

            return response()->json([
                'message'=>'Face ID added successfully'
            ]);
        } catch (\Throwable $e) {
            Log::error('Salesman addFaceId error: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function clearPersonIds(Request $req)
    {
        try {
            $rules = [
                'person_ids'=>'nullable|array',
                'employee_ids'=>'nullable|array',
                'ids'=>'nullable|array'
            ];

            $data = $req->all();

            $validator = \Validator::make($data, $rules);
            if($validator->fails())
            {
                return response()->json([
                    'message' => 'Invalid parameters supplied.',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $personIds = $data['person_ids'] ?? [];
            $employeeIds = $data['employee_ids'] ?? [];
            $ids = $data['ids'] ?? [];

            foreach ($personIds as $pid) {
                if (is_string($pid) && strlen($pid) > 36) {
                    $ids[] = (int)substr($pid, 36);
                } elseif (is_string($pid) && preg_match('/(\d+)$/', $pid, $matches)) {
                    $ids[] = (int)$matches[1];
                }
            }

            $user = \Auth::user();
            if ($user) {
                $user->update([
                    'person_id' => NULL,
                    'face_ids' => NULL,
                    'is_locked' => 0
                ]);
            }

            Employee::where(function($q) use ($personIds, $employeeIds, $ids, $user) {
                if (!empty($personIds)) {
                    $q->orWhereIn('person_id', $personIds);
                }
                if (!empty($employeeIds)) {
                    $q->orWhereIn('employee_id', $employeeIds);
                }
                if (!empty($ids)) {
                    $q->orWhereIn('id', $ids);
                }
                if ($user && $user->employee_id) {
                    $q->orWhere('employee_id', $user->employee_id);
                }
            })->update([
                'person_id' => NULL,
                'face_ids' => NULL,
                'is_locked' => 0
            ]);

            return response()->json([
                'message'=>'Face and Person IDs cleared successfully'
            ]);
        } catch (\Throwable $e) {
            Log::error('Salesman clearPersonIds error: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function addMeetingCheckIn(Request $req)
    {
        try {
            $rules = [
                'entry_type'=>'nullable',
                'photo'=>'nullable',
                'lat'=>'nullable',
                'lng'=>'nullable',
                'device'=>'nullable',
                'address'=>'nullable'
            ];

            $data = $req->all();

            $validator = \Validator::make($data, $rules);
            if($validator->fails())
            {
                return response()->json([
                    'message' => 'Invalid parameters.',
                    'errors' => $validator->errors()
                ], 422);
            }

            if (isset($data['lat']) && isset($data['lng']) && is_numeric($data['lat']) && is_numeric($data['lng'])) {
                if (!$this->validateGeofence($data['lat'], $data['lng'])) {
                    return response()->json([
                        'message'=>'You are outside the allowed geofence area',
                        'errors'=>[]
                    ], 422);
                }
            }

            $savedPhoto = null;
            if($req->hasFile('photo'))
            {
                try {
                    $photo = $req->file('photo');
                    $ext = $photo->getClientOriginalExtension() ?: ($photo->extension() ?: 'jpg');
                    $filename = uniqid() . time() . '.' . $ext;
                    $destPath = public_path('uploads/meeting_attendance');
                    if (!file_exists($destPath)) {
                        @mkdir($destPath, 0777, true);
                    }
                    $photo->move($destPath, $filename);
                    $savedPhoto = $filename;
                } catch (\Throwable $pe) {
                    Log::warning('Meeting check-in photo failed: ' . $pe->getMessage());
                }
            }

            $user = \Auth::user();
            try {
                $last_entry = MeetingAttendance::where('salesman_id', $user ? $user->id : 0)->latest()->first();
                if($last_entry && $last_entry->type == 0)
                {
                    return response()->json([
                        'message'=>'Consecutive check ins not allowed',
                        'errors'=>[]
                    ], 422);
                }
            } catch (\Throwable $le) {
                Log::warning('Meeting last entry check error: ' . $le->getMessage());
            }

            $data['address'] = $req->input('address', null);
            if(isset($data['lat']) && $data['lat'] && !is_numeric($data['lat']))
            {
                if (!$data['address']) {
                    $data['address'] = $data['lat'];
                }
                $data['lat'] = null;            
            }
            
            $idata = [
                'salesman_id'=>$user ? $user->id : null,
                'type'=>0,
                'entry_type'=>isset($data['entry_type']) ? $data['entry_type'] : 0,
                'lat'=>isset($data['lat']) ? $data['lat'] : null,
                'lng'=>isset($data['lng']) ? $data['lng'] : null,
                'address'=>$data['address'],
                'device'=>isset($data['device']) ? $data['device'] : null
            ];

            if($savedPhoto)
            {
                $idata['photo'] = $savedPhoto;
            }

            MeetingAttendance::create($idata);

            return response()->json([
                'message'=>'Check In added successfully'
            ]);
        } catch (\Throwable $e) {
            Log::error('addMeetingCheckIn exception: ' . $e->getMessage());
            return response()->json(['message' => 'Meeting Check-In failed: ' . $e->getMessage()], 422);
        }
    }

    public function addMeetingCheckOut(Request $req)
    {
        try {
            $rules = [
                'entry_type'=>'nullable',
                'photo'=>'nullable',
                'lat'=>'nullable',
                'lng'=>'nullable',
                'customer_name'=>'nullable',
                'purpose'=>'nullable',
                'meeting_notes'=>'nullable',
                'device'=>'nullable',
                'address'=>'nullable'
            ];

            $data = $req->all();

            $validator = \Validator::make($data, $rules);
            if($validator->fails())
            {
                return response()->json([
                    'message' => 'Invalid parameters.',
                    'errors' => $validator->errors()
                ], 422);
            }

            if (isset($data['lat']) && isset($data['lng']) && is_numeric($data['lat']) && is_numeric($data['lng'])) {
                if (!$this->validateGeofence($data['lat'], $data['lng'])) {
                    return response()->json([
                        'message'=>'You are outside the allowed geofence area',
                        'errors'=>[]
                    ], 422);
                }
            }

            $savedPhoto = null;
            if($req->hasFile('photo'))
            {
                try {
                    $photo = $req->file('photo');
                    $ext = $photo->getClientOriginalExtension() ?: ($photo->extension() ?: 'jpg');
                    $filename = uniqid() . time() . '.' . $ext;
                    $destPath = public_path('uploads/meeting_attendance');
                    if (!file_exists($destPath)) {
                        @mkdir($destPath, 0777, true);
                    }
                    $photo->move($destPath, $filename);
                    $savedPhoto = $filename;
                } catch (\Throwable $pe) {
                    Log::warning('Meeting check-out photo failed: ' . $pe->getMessage());
                }
            }

            $user = \Auth::user();
            try {
                $last_entry = MeetingAttendance::where('salesman_id', $user ? $user->id : 0)->latest()->first();
                if($last_entry && $last_entry->type == 1)
                {
                    return response()->json([
                        'message'=>'Consecutive check outs not allowed',
                        'errors'=>[]
                    ], 422);
                }
            } catch (\Throwable $le) {
                Log::warning('Meeting last entry check error: ' . $le->getMessage());
            }

            $data['address'] = $req->input('address', null);
            if(isset($data['lat']) && $data['lat'] && !is_numeric($data['lat']))
            {
                if (!$data['address']) {
                    $data['address'] = $data['lat'];
                }
                $data['lat'] = null;            
            }
            
            $idata = [
                'salesman_id'=>$user ? $user->id : null,
                'type'=>1,
                'entry_type'=>isset($data['entry_type']) ? $data['entry_type'] : 0,
                'lat'=>isset($data['lat']) ? $data['lat'] : null,
                'lng'=>isset($data['lng']) ? $data['lng'] : null,
                'address'=>$data['address'],
                'customer_name' => $data['customer_name'] ?? '',
                'purpose' => $data['purpose'] ?? '',
                'meeting_notes' => $data['meeting_notes'] ?? '',
                'device'=>isset($data['device']) ? $data['device'] : null
            ];

            if($savedPhoto)
            {
                $idata['photo'] = $savedPhoto;
            }

            MeetingAttendance::create($idata);

            return response()->json([
                'message'=>'Check Out added successfully'
            ]);
        } catch (\Throwable $e) {
            Log::error('addMeetingCheckOut exception: ' . $e->getMessage());
            return response()->json(['message' => 'Meeting Check-Out failed: ' . $e->getMessage()], 422);
        }
    }

    public function addCheckIn(Request $req)
    {
        try {
            $rules = [
                'entry_type'=>'nullable',
                'photo'=>'nullable',
                'lat'=>'nullable',
                'lng'=>'nullable',
                'device'=>'nullable',
                'address'=>'nullable'
            ];

            $data = $req->all();

            $validator = \Validator::make($data, $rules);
            if($validator->fails())
            {
                return response()->json([
                    'message' => 'Invalid check-in parameters.',
                    'errors' => $validator->errors()
                ], 422);
            }

            if (isset($data['lat']) && isset($data['lng']) && is_numeric($data['lat']) && is_numeric($data['lng'])) {
                if (!$this->validateGeofence($data['lat'], $data['lng'])) {
                    return response()->json([
                        'message'=>'You are outside the allowed geofence area',
                        'errors'=>[]
                    ], 422);
                }
            }

            $employee = $this->getSalesmanEmployee($req);
            if(!$employee)
            {
                return response()->json([
                    'message'=>'Employee is not assigned to this salesman account',
                    'errors'=>[]
                ], 422);
            }

            try {
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
            } catch (\Throwable $be) {
                Log::warning('EmployeeBlock check ignored: ' . $be->getMessage());
            }

            $savedPhoto = null;
            if($req->hasFile('photo'))
            {
                try {
                    $photo = $req->file('photo');
                    $ext = $photo->getClientOriginalExtension() ?: ($photo->extension() ?: 'jpg');
                    $filename = uniqid() . time() . '.' . $ext;
                    $destPath = public_path('uploads/employee_attendance');
                    if (!file_exists($destPath)) {
                        @mkdir($destPath, 0777, true);
                    }
                    $photo->move($destPath, $filename);
                    $savedPhoto = $filename;
                } catch (\Throwable $pe) {
                    Log::warning('Salesman checkin photo upload failed: ' . $pe->getMessage());
                }
            }

            try {
                $last_entry = Attendance::where('employee_id', $employee->id)->latest()->first();
                if($last_entry && $last_entry->type == 0)
                {
                    return response()->json([
                        'message'=>'Consecutive check ins not allowed',
                        'errors'=>[]
                    ], 422);
                }
            } catch (\Throwable $le) {
                Log::warning('Last entry check error: ' . $le->getMessage());
            }

            $data['address'] = $req->input('address', null);
            if(isset($data['lat']) && $data['lat'] && !is_numeric($data['lat']))
            {
                if (!$data['address']) {
                    $data['address'] = $data['lat'];
                }
                $data['lat'] = null;            
            }
            
            $user = \Auth::user();
            $idata = [
                'employee_id'=>$employee->id,
                'user_id'=>$user ? $user->id : null,
                'type'=>0,
                'entry_type'=>isset($data['entry_type']) ? $data['entry_type'] : 0,
                'lat'=>isset($data['lat']) ? $data['lat'] : null,
                'lng'=>isset($data['lng']) ? $data['lng'] : null,
                'address'=>$data['address'],
                'device'=>isset($data['device']) ? $data['device'] : null
            ];

            if($savedPhoto)
            {
                $idata['photo'] = $savedPhoto;
            }

            Attendance::create($idata);

            return response()->json([
                'message'=>'Check In added successfully',
                'employee'=>$employee
            ]);
        } catch (\Throwable $e) {
            Log::error('Salesman addCheckIn exception: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'message' => 'Check-In failed: ' . $e->getMessage(),
                'errors' => []
            ], 422);
        }
    }
    
    public function addCheckOut(Request $req)
    {
        try {
            $rules = [
                'entry_type'=>'nullable',
                'photo'=>'nullable',
                'lat'=>'nullable',
                'lng'=>'nullable',
                'device'=>'nullable',
                'address'=>'nullable'
            ];

            $data = $req->all();

            $validator = \Validator::make($data, $rules);
            if($validator->fails())
            {
                return response()->json([
                    'message' => 'Invalid check-out parameters.',
                    'errors' => $validator->errors()
                ], 422);
            }

            if (isset($data['lat']) && isset($data['lng']) && is_numeric($data['lat']) && is_numeric($data['lng'])) {
                if (!$this->validateGeofence($data['lat'], $data['lng'])) {
                    return response()->json([
                        'message'=>'You are outside the allowed geofence area',
                        'errors'=>[]
                    ], 422);
                }
            }

            $employee = $this->getSalesmanEmployee($req);
            if(!$employee)
            {
                return response()->json([
                    'message'=>'Employee is not assigned to this salesman account',
                    'errors'=>[]
                ], 422);
            }

            $savedPhoto = null;
            if($req->hasFile('photo'))
            {
                try {
                    $photo = $req->file('photo');
                    $ext = $photo->getClientOriginalExtension() ?: ($photo->extension() ?: 'jpg');
                    $filename = uniqid() . time() . '.' . $ext;
                    $destPath = public_path('uploads/employee_attendance');
                    if (!file_exists($destPath)) {
                        @mkdir($destPath, 0777, true);
                    }
                    $photo->move($destPath, $filename);
                    $savedPhoto = $filename;
                } catch (\Throwable $pe) {
                    Log::warning('Salesman checkout photo upload failed: ' . $pe->getMessage());
                }
            }

            try {
                $last_entry = Attendance::where('employee_id', $employee->id)->latest()->first();
                if($last_entry && $last_entry->type == 1)
                {
                    return response()->json([
                        'message'=>'Consecutive check outs not allowed',
                        'errors'=>[]
                    ], 422);
                }
            } catch (\Throwable $le) {
                Log::warning('Last entry check error: ' . $le->getMessage());
            }

            $data['address'] = $req->input('address', null);
            if(isset($data['lat']) && $data['lat'] && !is_numeric($data['lat']))
            {
                if (!$data['address']) {
                    $data['address'] = $data['lat'];
                }
                $data['lat'] = null;            
            }
            
            $user = \Auth::user();
            $idata = [
                'employee_id'=>$employee->id,
                'user_id'=>$user ? $user->id : null,
                'type'=>1,
                'entry_type'=>isset($data['entry_type']) ? $data['entry_type'] : 0,
                'lat'=>isset($data['lat']) ? $data['lat'] : null,
                'lng'=>isset($data['lng']) ? $data['lng'] : null,
                'address'=>$data['address'],
                'device'=>isset($data['device']) ? $data['device'] : null
            ];

            if($savedPhoto)
            {
                $idata['photo'] = $savedPhoto;
            }

            Attendance::create($idata);

            return response()->json([
                'message'=>'Check Out added successfully',
                'employee'=>$employee
            ]);
        } catch (\Throwable $e) {
            Log::error('Salesman addCheckOut exception: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'message' => 'Check-Out failed: ' . $e->getMessage(),
                'errors' => []
            ], 422);
        }
    }
}
