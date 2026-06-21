<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Setting;
use App\Holiday;

class SettingController extends \App\Http\Controllers\Controller
{    
    /**
     * Settings Hub
     */
    public function index()
    {
        return view('admin.settings.index');
    }

    // ── General ──────────────────────────────────────────────────────────────

    public function general()
    {
        // Exclude weekend_days from the generic key/value form
        $rows = Setting::where('key', '!=', 'weekend_days')->get();
        return view('admin.settings.general', compact('rows'));
    }

    public function update(Request $req)
    {
        $data = $req->validate([            
            'val.*' => ['required']                        
        ]);

        foreach($data['val'] as $id => $val)
        {
            Setting::whereid($id)->update(['val' => $val]);
            
            $row = Setting::find($id);
            \Cache::forget('st_'.$row->key);
        }

        return redirect('admin/settings')->with('status', 'Settings updated successfully');        
    }

    public function clearExcelCache()
    {
        $path = storage_path('framework/cache/laravel-excel');
        if (\File::exists($path)) {
            \File::cleanDirectory($path);
        }
        return redirect('admin/settings/general')->with('status', 'Report cache cleared successfully.');
    }

    // ── Weekend Days ──────────────────────────────────────────────────────────

    public function weekend()
    {
        $setting = Setting::where('key', 'weekend_days')->first();
        $selectedDays = $setting ? array_map('intval', explode(',', $setting->val)) : [];
        return view('admin.settings.weekend', compact('selectedDays'));
    }

    public function updateWeekend(Request $req)
    {
        // days come as an array of integers e.g. [0, 6]
        $days = $req->input('days', []);
        $val  = implode(',', $days);

        Setting::updateOrCreate(
            ['key' => 'weekend_days'],
            ['label' => 'Weekend Days', 'val' => $val]
        );

        \Cache::forget('st_weekend_days');

        return redirect('admin/settings/weekend')->with('status', 'Weekend days updated successfully.');
    }

    // ── Public Holidays ───────────────────────────────────────────────────────

    public function holidays()
    {
        $holidays = Holiday::orderBy('date', 'asc')->get();
        $archivedHolidays = Holiday::onlyTrashed()->orderBy('date', 'asc')->get();
        return view('admin.settings.holidays', compact('holidays', 'archivedHolidays'));
    }

    public function storeHoliday(Request $req)
    {
        $req->validate([
            'date' => 'required|date|unique:holidays,date',
            'name' => 'nullable|string|max:255',
        ]);

        Holiday::create([
            'date' => $req->date,
            'name' => $req->name,
        ]);

        \Cache::forget('st_holidays');

        return redirect('admin/settings/holidays')->with('status', 'Holiday added successfully.');
    }

    public function deleteHoliday($id)
    {
        // Soft delete — date stays excluded from absent report but is archived
        Holiday::findOrFail($id)->delete();
        \Cache::forget('st_holidays');
        return redirect('admin/settings/holidays')->with('status', 'Holiday archived. It still protects past records.');
    }

    public function restoreHoliday($id)
    {
        Holiday::withTrashed()->findOrFail($id)->restore();
        \Cache::forget('st_holidays');
        return redirect('admin/settings/holidays')->with('status', 'Holiday restored successfully.');
    }

    public function forceDeleteHoliday($id)
    {
        Holiday::withTrashed()->findOrFail($id)->forceDelete();
        \Cache::forget('st_holidays');
        return redirect('admin/settings/holidays')->with('status', 'Holiday permanently deleted.');
    }
}
