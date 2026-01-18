<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\Request;

class AppSettingsController extends Controller
{
    public function index()
    {
        $default = (float) config('photoblast.row_gap_ratio', 0.012);
        $rowGap = AppSetting::getFloat('row_gap_ratio', $default);

        $flowTimeoutDefault = 8;
        $flowTimeoutMinutes = (int) (AppSetting::getString('tempcollage.flow_timeout_minutes', (string) $flowTimeoutDefault) ?: $flowTimeoutDefault);
        if ($flowTimeoutMinutes <= 0) $flowTimeoutMinutes = $flowTimeoutDefault;

        return view('admin.settings.index', [
            'row_gap_ratio' => $rowGap,
            'row_gap_default' => $default,
            'flow_timeout_minutes' => $flowTimeoutMinutes,
            'flow_timeout_default' => $flowTimeoutDefault,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'row_gap_ratio' => 'required|numeric|min:0|max:0.060',
            'flow_timeout_minutes' => 'required|integer|min:1|max:60',
        ]);

        $ratio = (float) $request->input('row_gap_ratio');
        $ratio = max(0.000, min(0.060, $ratio));

        AppSetting::setString('row_gap_ratio', (string) $ratio);

        $mins = (int) $request->input('flow_timeout_minutes', 8);
        $mins = max(1, min(60, $mins));
        AppSetting::setString('tempcollage.flow_timeout_minutes', (string) $mins);

        return redirect()->route('admin.settings.index')->with('message', 'Setting disimpan.');
    }
}
