<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Setting;

class SettingController extends \App\Http\Controllers\Controller
{    
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $rows = Setting::all();
        return view('admin.settings.index', compact('rows'));
    }

    public function update(Request $req)
    {
        $data = $req->validate([            
            'val.*' => ['required']                        
        ]);

        foreach($data['val'] as $id=>$val)
        {
            Setting::whereid($id)->update(['val'=>$val]);
            
            $row = Setting::find($id);

            \Cache::forget('st_'.$row->key);
        }

        return redirect('admin/settings')->with('status', 'Settings updated successfully');        
    }
}
