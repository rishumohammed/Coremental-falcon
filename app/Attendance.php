<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $guarded = ['id'];

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
        return $this->entry_type==0?'Automatic':'Manual';
    }

    function getPhotoUrlAttribute()
    {
        if($this->photo)
            return asset('uploads/employee_attendance/'.$this->photo);
    }
}
