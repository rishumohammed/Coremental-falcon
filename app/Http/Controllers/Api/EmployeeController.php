<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Employee;
use App\Attendance;
use Illuminate\Support\Facades\Log;

class EmployeeController extends \App\Http\Controllers\Controller
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

    private function resolveEmployee(Request $req)
    {
        $personId = trim($req->input('person_id') ?? '');
        $employeeId = trim($req->input('employee_id') ?? '');
        $employee = null;
        $user = \Auth::user();

        // 1. Direct match by person_id (assigned or global)
        if (!empty($personId)) {
            if ($user) {
                $employee = $user->employees()->where('employees.person_id', $personId)->first();
            }
            if (!$employee) {
                $employee = Employee::where('person_id', $personId)->first();
            }
        }

        // 2. Direct match by employee_id (assigned or global)
        if (!$employee && !empty($employeeId)) {
            $empCodeSpaces = str_replace('_', ' ', $employeeId);
            if ($user) {
                $employee = $user->employees()->where('employees.employee_id', $employeeId)->first()
                         ?: $user->employees()->where('employees.employee_id', $empCodeSpaces)->first();
            }
            if (!$employee) {
                $employee = Employee::where('employee_id', $employeeId)->first()
                         ?: Employee::where('employee_id', $empCodeSpaces)->first();
            }
            if (!$employee && is_numeric($employeeId)) {
                $employee = Employee::find((int)$employeeId);
            }
        }

        // 3. Dot split (e.g. 7a4d51c1-8e68-4b9e-a23b-6c7f5d92a1d31614.FGC100)
        if (!$employee && !empty($personId) && strpos($personId, '.') !== false) {
            $parts = explode('.', $personId);
            $pidPart = trim($parts[0]);
            $empCodePart = trim($parts[1] ?? '');
            $empCodeClean = str_replace('_', ' ', $empCodePart);

            if (!empty($empCodePart)) {
                $employee = Employee::where('employee_id', $empCodePart)->first()
                         ?: Employee::where('employee_id', $empCodeClean)->first();
            }
            if (!$employee && strlen($pidPart) > 36) {
                $extractedId = (int)substr($pidPart, 36);
                if ($extractedId > 0) {
                    $employee = Employee::find($extractedId)
                             ?: Employee::where('employee_id', (string)$extractedId)->first();
                }
            }
            if (!$employee && is_numeric($empCodePart)) {
                $employee = Employee::find((int)$empCodePart);
            }
        }

        // 4. Substring after 36-char UUID prefix
        if (!$employee && !empty($personId) && strlen($personId) > 36) {
            $extractedId = (int)substr($personId, 36);
            if ($extractedId > 0) {
                if ($user) {
                    $employee = $user->employees()->where('employees.id', $extractedId)->first();
                }
                if (!$employee) {
                    $employee = Employee::find($extractedId)
                             ?: Employee::where('employee_id', (string)$extractedId)->first();
                }
            }
        }

        // 5. Trailing digits extraction
        if (!$employee && !empty($personId) && preg_match('/(\d+)$/', $personId, $matches)) {
            $numericId = (int)$matches[1];
            if ($numericId > 0) {
                if ($user) {
                    $employee = $user->employees()->where('employees.id', $numericId)->first();
                }
                if (!$employee) {
                    $employee = Employee::find($numericId)
                             ?: Employee::where('employee_id', (string)$numericId)->first();
                }
            }
        }

        // 6. Search inside face_ids
        if (!$employee && !empty($personId)) {
            $employee = Employee::where('face_ids', 'like', "%{$personId}%")->first();
        }

        // 7. Try finding by user's linked employee profile
        if (!$employee && $user && $user->employee_id) {
            $employee = Employee::where('employee_id', $user->employee_id)->first();
        }

        // If employee was found, auto-sync person_id and user assignment if needed
        if ($employee) {
            try {
                if (!empty($personId) && $employee->person_id !== $personId) {
                    $employee->update(['person_id' => $personId]);
                }
                if ($user && !$user->employees()->where('employees.id', $employee->id)->exists()) {
                    $user->employees()->syncWithoutDetaching([$employee->id]);
                }
            } catch (\Throwable $e) {
                Log::warning('Employee auto-sync warning: ' . $e->getMessage());
            }
        }

        return $employee;
    }
    
    public function index(Request $req) 
    {
        try {
            $user = \Auth::user();
            $rows = $user ? $user->employees : collect();
            if ($rows->isEmpty()) {
                $rows = Employee::take(500)->get();
            }
            return response()->json($rows);
        } catch (\Throwable $e) {
            Log::error('Employee index error: ' . $e->getMessage());
            return response()->json(Employee::take(500)->get());
        }
    }

    public function details(Request $req) 
    {
        try {
            $user = \Auth::user();
            if ($user && $user->type == 'salesman') {
                $row = $this->resolveEmployee($req) ?: ($user->employee ?: Employee::where('employee_id', $user->employee_id)->first());
                if (!$row) {
                    $row = $user->employees()->first();
                }
                return response()->json($row ?: []);
            }

            $row = $this->resolveEmployee($req);
            return response()->json($row ?: []);
        } catch (\Throwable $e) {
            Log::error('Employee details error: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function setPersonId(Employee $row, Request $req)
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

            $row->update($data);

            $user = \Auth::user();
            if ($user && !$user->employees()->where('employees.id', $row->id)->exists()) {
                $user->employees()->syncWithoutDetaching([$row->id]);
            }

            return response()->json([
                'message'=>'Person ID set successfully'
            ]);
        } catch (\Throwable $e) {
            Log::error('setPersonId error: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function addFaceId(Employee $row, Request $req)
    {
        try {
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
                return response()->json([
                    'message' => 'The face_id field is required.',
                    'errors' => $validator->errors()
                ], 422);
            }

            $face_ids = $row->face_ids ?: [];
            $face_ids[] = $data['face_id'];
            $row->face_ids = $face_ids;

            if(count($face_ids)>=3)
            {
                $row->is_locked = 1;
            }

            $row->save();

            $user = \Auth::user();
            if ($user && !$user->employees()->where('employees.id', $row->id)->exists()) {
                $user->employees()->syncWithoutDetaching([$row->id]);
            }

            return response()->json([
                'message'=>'Face ID added successfully'
            ]);
        } catch (\Throwable $e) {
            Log::error('addFaceId error: ' . $e->getMessage());
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

            Employee::where(function($q) use ($personIds, $employeeIds, $ids) {
                if (!empty($personIds)) {
                    $q->orWhereIn('person_id', $personIds);
                }
                if (!empty($employeeIds)) {
                    $q->orWhereIn('employee_id', $employeeIds);
                }
                if (!empty($ids)) {
                    $q->orWhereIn('id', $ids);
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
            Log::error('clearPersonIds error: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function addCheckIn(Request $req)
    {
        try {
            $rules = [
                'entry_type'=>'nullable',
                'person_id'=>'nullable',
                'employee_id'=>'nullable',
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
            
            $employee = $this->resolveEmployee($req);

            if(!$employee)
            {
                return response()->json([
                    'message'=>'Employee not found. Please ensure employee is registered and face is enrolled.',
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
                    Log::warning('CheckIn photo upload failed: ' . $pe->getMessage());
                }
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
                'type'=>0,
                'entry_type'=>isset($data['entry_type']) ? $data['entry_type'] : 0,
                'lat'=>isset($data['lat']) ? $data['lat'] : null,
                'lng'=>isset($data['lng']) ? $data['lng'] : null,
                'address'=>$data['address'],
                'device'=>isset($data['device']) ? $data['device'] : null,
                'user_id'=>$user ? $user->id : null
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
            Log::error('addCheckIn exception: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
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
                'person_id'=>'nullable',
                'employee_id'=>'nullable',
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

            $employee = $this->resolveEmployee($req);

            if(!$employee)
            {
                return response()->json([
                    'message'=>'Employee not found. Please ensure employee is registered and face is enrolled.',
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
                    Log::warning('CheckOut photo upload failed: ' . $pe->getMessage());
                }
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
                'type'=>1,
                'entry_type'=>isset($data['entry_type']) ? $data['entry_type'] : 0,
                'lat'=>isset($data['lat']) ? $data['lat'] : null,
                'lng'=>isset($data['lng']) ? $data['lng'] : null,
                'address'=>$data['address'],
                'device'=>isset($data['device']) ? $data['device'] : null,
                'user_id'=>$user ? $user->id : null
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
            Log::error('addCheckOut exception: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'message' => 'Check-Out failed: ' . $e->getMessage(),
                'errors' => []
            ], 422);
        }
    }
}
