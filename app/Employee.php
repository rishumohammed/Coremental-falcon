<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $guarded = ['id'];

    protected $hidden = ['created_at', 'updated_id'];

    protected $casts = [
        'face_ids' => 'array',
        'is_locked' => 'boolean'
    ];


    function attendances()
    {
        return $this->hasMany('\App\Attendance');
    }

    public function department()
    {
        return $this->belongsTo(\App\Department::class);
    }

    public function designation()
    {
        return $this->belongsTo(\App\Designation::class);
    }

    public function shift()
    {
        return $this->belongsTo(\App\Shift::class);
    }

    public function location()
    {
        return $this->belongsTo(\App\Location::class);
    }

    public function division()
    {
        return $this->belongsTo(\App\Division::class);
    }

    public function users()
    {
        return $this->belongsToMany(\App\User::class, 'assigned_employees', 'employee_id', 'user_id');
    }

    public function getPhotoUrlAttribute()
    {
        if (isset($this->attributes['photo']) && !empty($this->attributes['photo'])) {
            return asset('uploads/employee_photos/' . $this->attributes['photo']);
        }
        $lastAttendance = $this->attendances()->whereNotNull('photo')->where('photo', '!=', '')->latest()->first();
        if ($lastAttendance && $lastAttendance->photo) {
            return asset('uploads/employee_attendance/' . $lastAttendance->photo);
        }
        return null;
    }
}
