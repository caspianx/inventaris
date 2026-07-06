<?php

namespace App\Http\Controllers;

use App\Models\RolePermission;
use App\Support\RolePermissionRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RolePermissionController extends Controller
{
    public function edit()
    {
        $roles = RolePermissionRegistry::roles();
        $groups = RolePermissionRegistry::groups();
        $rolePermissions = RolePermission::query()
            ->get()
            ->groupBy('role')
            ->map(fn ($items) => $items->pluck('permission')->all());

        return view('role_permissions.edit', compact('roles', 'groups', 'rolePermissions'));
    }

    public function update(Request $request)
    {
        $roles = array_keys(RolePermissionRegistry::roles());
        $availablePermissions = RolePermissionRegistry::all();

        $validated = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['array'],
            'permissions.*.*' => ['string', Rule::in($availablePermissions)],
        ]);

        DB::transaction(function () use ($roles, $availablePermissions, $validated) {
            foreach ($roles as $role) {
                $selected = collect($validated['permissions'][$role] ?? [])
                    ->intersect($availablePermissions)
                    ->values()
                    ->all();

                if ($role === 'admin') {
                    $selected = $availablePermissions;
                }

                RolePermission::where('role', $role)->delete();

                foreach ($selected as $permission) {
                    RolePermission::create([
                        'role' => $role,
                        'permission' => $permission,
                    ]);
                }

                Cache::forget("role_permissions.{$role}");
            }
        });

        return redirect()->route('role-permissions.edit')->with('success', 'Pengaturan akses role berhasil diperbarui.');
    }
}
