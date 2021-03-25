<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['val'];

    protected  $hidden = ['id','created_at', 'updated_at'];

    public static function timezone()
    {
        return \Cache::rememberForever('st_timezone', function(){
            return Setting::where('key', 'timezone')->first()->val;
        });
    }
}
