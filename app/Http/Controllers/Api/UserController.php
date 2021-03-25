<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

class UserController extends \App\Http\Controllers\Controller
{
    
    public function index(Request $req) 
    {
        $row = $req->user();
        $row->employees = $row->employees;
        return $row;
    }

    public function setGroupId(Request $req)
    {
        $rules = [
            'group_id'=>'required'
        ];

        $data = $req->all();

        $validator = \Validator::make($data, $rules);
        if($validator->fails())
        {
            return errRes($validator->errors()->toArray());
        }

        \Auth::user()->update($data);

        return response()->json([
            'message'=>'Group ID set successfully'
        ]);
    }
}
