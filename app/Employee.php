<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $guarded = ['id'];

    protected $hidden = ['created_at', 'updated_id'];

    protected $casts = [
        'face_ids' => 'Array',
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
}
