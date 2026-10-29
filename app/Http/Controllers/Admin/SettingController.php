<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function edit()
    {
        $values = [];
        foreach (array_keys(Setting::defaults()) as $key) {
            $values[$key] = Setting::get($key);
        }

        return view('admin.settings', ['s' => $values]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'nullable|string|max:5000',
            'settings.gst_percent' => 'nullable|numeric|min:0|max:100',
            'settings.urgent_surcharge_percent' => 'nullable|numeric|min:0|max:500',
            'settings.quotation_valid_days' => 'nullable|integer|min:1|max:730',
            'settings.next_quotation_number' => 'nullable|integer|min:1',
            'settings.next_invoice_number' => 'nullable|integer|min:1',
            'settings.next_lab_code' => 'nullable|integer|min:1',
            'settings.next_enquiry_number' => 'nullable|integer|min:1',
        ]);

        $allowed = array_keys(Setting::defaults());
        foreach ($data['settings'] as $key => $value) {
            if (in_array($key, $allowed, true)) {
                Setting::put($key, $value ?? '');
            }
        }
        Setting::flushCache();

        return back()->with('success', 'Settings saved.');
    }
}
