<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $guarded = ['id'];

    function user()
    {
        return $this->belongsTo('App\User');
    }
    function employee()
    {
        return $this->belongsTo('App\Employee');
    }

    function getTypeLabelAttribute()
    {
        return $this->type==0?'Check In':'Check Out';
    }

    function getEntryTypeLabelAttribute()
    {
        if ($this->entry_type == 1) {
            return $this->type == 0 ? 'Manual by Admin' : 'Manual';
        }
        return 'Automatic';
    }

    function getPhotoUrlAttribute()
    {
        if($this->photo)
            return asset('uploads/employee_attendance/'.$this->photo);
    }
}
