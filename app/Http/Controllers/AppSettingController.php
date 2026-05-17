<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AppSettingController extends Controller
{
    public function index()
    {
        $settings = AppSetting::all()->pluck('value', 'key');
        return view('superadmin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'logo' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'cover' => 'nullable|image|mimes:png,jpg,jpeg|max:5120',
            'alamat' => 'nullable|string',
            'no_telp' => 'nullable|string',
            'email' => 'nullable|email',
            'website' => 'nullable|url',
            'facebook' => 'nullable|url',
            'instagram' => 'nullable|url',
            'twitter' => 'nullable|url',
            'youtube' => 'nullable|url',
        ]);

        $data = $request->except(['_token', 'logo', 'cover']);

        // Handle Logo
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            $oldLogo = AppSetting::where('key', 'logo')->first();
            if ($oldLogo && $oldLogo->value) {
                $oldPath = str_replace('/storage/', 'public/', $oldLogo->value);
                Storage::delete($oldPath);
            }
            
            $logoPath = $request->file('logo')->store('public/settings');
            $data['logo'] = Storage::url($logoPath);
        }

        // Handle Cover
        if ($request->hasFile('cover')) {
            // Delete old cover if exists
            $oldCover = AppSetting::where('key', 'cover')->first();
            if ($oldCover && $oldCover->value) {
                $oldPath = str_replace('/storage/', 'public/', $oldCover->value);
                Storage::delete($oldPath);
            }

            $coverPath = $request->file('cover')->store('public/settings');
            $data['cover'] = Storage::url($coverPath);
        }

        foreach ($data as $key => $value) {
            AppSetting::updateOrCreate(['key' => $key], ['value' => $value]);
            Cache::forget('app_settings');
        }

        return redirect()->back()->with('success', 'Pengaturan berhasil diperbarui.');
    }
}
