<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use  App\Setting;

class SettingController extends \App\Http\Controllers\Controller
{    
    public function index(Request $req) 
    {
        $rdata = Setting::pluck('val', 'key');
        $rdata['confidence_threshold'] = (float)$rdata['confidence_threshold'];
        if(\Auth::user()->type == 'general')
        $rdata['factory_location'] = \Auth::user()->geo_location;
        return $rdata;
    }
}
