<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::first();
        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'hotel_name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'nit' => 'required|string|max:20',
            'logo_path' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $settings = Setting::first();
        if (!$settings) {
            $settings = new Setting();
        }

        $settings->hotel_name = $request->hotel_name;
        $settings->address = $request->address;
        $settings->nit = $request->nit;

        if ($request->hasFile('logo_path')) {
            $path = $request->file('logo_path')->store('logos', 'public');
            $settings->logo_path = $path;
        }

        $settings->save();

        return redirect()->route('settings.index')->with('success', 'Configuración actualizada correctamente.');
    }
}
