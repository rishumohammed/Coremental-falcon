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
}
