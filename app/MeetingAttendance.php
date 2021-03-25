<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MeetingAttendance extends Model
{
    protected $guarded = ['id'];

    function salesman()
    {
        return  $this->belongsTo('App\User', 'salesman_id');
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
            return asset('uploads/meeting_attendance/'.$this->photo);
    }

}
