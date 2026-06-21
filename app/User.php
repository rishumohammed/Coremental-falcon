<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'username', 'geo_location', 'location', 'type', 'group_id', 'person_id', 'face_ids', 'is_locked', 'employee_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'created_at', 'updated_at', 'id'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'face_ids' => 'Array',
        'is_locked' => 'boolean'
    ];

    // if type general
    function employees()
    {
        return $this->belongsToMany('App\Employee', 'assigned_employees', 'user_id', 'employee_id');
    }

    // if type salesman
    function employee()
    {
        return $this->belongsTo('App\Employee', 'employee_id', 'employee_id');
    }

    // if type salesman
    function meetingAttendances()
    {
        return $this->hasMany('App\MeetingAttendance', 'salesman_id');
    }

    function username()
    {
        return 'username';
    }

    function findForPassport($username)
    {
        return $this->where('username', $username)->first();
    }

    function getGroupIdAttribute()
    {
        return 'f56cd35d-7d89-4eea-8e18-9d11a59e14f8';
    }
}
