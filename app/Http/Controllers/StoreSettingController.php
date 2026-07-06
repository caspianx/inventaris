<?php

namespace App\Http\Controllers;

use App\Models\StoreSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class StoreSettingController extends Controller
{
    public function edit()
    {
        $storeSetting = StoreSetting::current();

        return view('store_settings.edit', compact('storeSetting'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
        ]);

        $storeSetting = StoreSetting::current();

        $data = [
            'name' => $validated['name'],
            'address' => $validated['address'] ?? null,
        ];

        if ($request->boolean('remove_logo') && $storeSetting->logo_path) {
            File::delete(public_path($storeSetting->logo_path));
            $data['logo_path'] = null;
        }

        if ($request->hasFile('logo')) {
            if ($storeSetting->logo_path) {
                File::delete(public_path($storeSetting->logo_path));
            }

            $directory = public_path('store-logos');

            if (!File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
            }

            $file = $request->file('logo');
            $filename = 'store-logo-' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($directory, $filename);

            $data['logo_path'] = 'store-logos/' . $filename;
        }

        $storeSetting->update($data);

        return redirect()->route('store-settings.edit')->with('success', 'Pengaturan toko berhasil diperbarui.');
    }
}
