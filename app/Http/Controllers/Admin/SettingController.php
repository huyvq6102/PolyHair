<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SettingService;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    protected $settingService;

    public function __construct(SettingService $settingService)
    {
        $this->settingService = $settingService;
    }

    /**
     * Display the settings form.
     */
    public function index()
    {
        $setting = $this->settingService->getFirst();
        
        if (!$setting) {
            $setting = $this->settingService->create([
                'logo' => '',
                'file_ico' => '',
                'title' => '',
                'introduce' => '',
                'slogan' => '',
            ]);
        }

        return view('admin.settings.index', compact('setting'));
    }

    /**
     * Update the settings.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'file_ico' => 'nullable|image|mimes:ico|max:1024',
            'title' => 'required|string|max:191',
            'introduce' => 'nullable|string',
            'slogan' => 'nullable|string|max:255',
        ]);

        $setting = $this->settingService->getFirst();
        
        if (!$setting) {
            $setting = $this->settingService->create($validated);
        }

        if ($request->hasFile('logo')) {
            // Delete old logo
            if ($setting->logo && file_exists(public_path('legacy/images/' . $setting->logo))) {
                unlink(public_path('legacy/images/' . $setting->logo));
            }
            
            $logo = $request->file('logo');
            $logoName = time() . '_' . $logo->getClientOriginalName();
            $logo->move(public_path('legacy/images'), $logoName);
            $validated['logo'] = $logoName;
        }

        if ($request->hasFile('file_ico')) {
            // Delete old ico
            if ($setting->file_ico && file_exists(public_path('legacy/images/' . $setting->file_ico))) {
                unlink(public_path('legacy/images/' . $setting->file_ico));
            }
            
            $ico = $request->file('file_ico');
            $icoName = time() . '_' . $ico->getClientOriginalName();
            $ico->move(public_path('legacy/images'), $icoName);
            $validated['file_ico'] = $icoName;
        }

        $this->settingService->update($setting->id, $validated);

        return redirect()->route('admin.settings.index')
            ->with('success', 'Cài đặt đã được cập nhật thành công!');
    }
}
