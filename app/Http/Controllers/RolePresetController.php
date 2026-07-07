<?php

namespace App\Http\Controllers;

use App\Models\RolePermissionPreset;
use Illuminate\Http\Request;

class RolePresetController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'alpha_dash', 'max:50', 'unique:role_permission_presets,name'],
            'label' => ['nullable', 'string', 'max:100'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string'],
        ]);

        $preset = RolePermissionPreset::create([
            'name' => $validated['name'],
            'label' => $validated['label'] ?? null,
            'permissions' => $validated['permissions'] ?? [],
        ]);

        return response()->json(['success' => true, 'preset' => $preset], 201);
    }
}
