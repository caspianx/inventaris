<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\RolePermission;
use App\Support\RolePermissionRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with(['permissions'])->withCount('users')->orderBy('name')->paginate(15);

        return view('roles.index', compact('roles'));
    }

    public function create(Request $request)
    {
        $groups = RolePermissionRegistry::groups();
        $preset = $request->query('preset');
        $defaultPermissions = $preset ? RolePermissionRegistry::defaults($preset) : [];

        return view('roles.create', compact('groups', 'preset', 'defaultPermissions'));
    }

    public function store(Request $request)
    {
        $availablePermissions = RolePermissionRegistry::all();

        $validated = $request->validate([
            'name' => ['required', 'alpha_dash', 'max:50', 'unique:roles,name'],
            'label' => ['nullable', 'string', 'max:100'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in($availablePermissions)],
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'label' => $validated['label'] ?? null,
        ]);

        $selectedPermissions = collect($validated['permissions'] ?? [])
            ->filter(fn ($permission) => in_array($permission, $availablePermissions, true))
            ->values()
            ->all();

        foreach ($selectedPermissions as $permission) {
            RolePermission::create([
                'role' => $role->name,
                'permission' => $permission,
            ]);
        }

        Cache::forget("role_permissions.{$role->name}");

        return redirect()->route('roles.index')->with('success', 'Role berhasil dibuat dan izin berhasil disimpan.');
    }

    public function edit(Request $request, Role $role)
    {
        $groups = RolePermissionRegistry::groups();
        $selectedPermissions = $role->permissions()->pluck('permission')->all();
        $preset = $request->query('preset');
        $defaultPermissions = $preset ? RolePermissionRegistry::defaults($preset) : $selectedPermissions;

        return view('roles.edit', compact('role', 'groups', 'selectedPermissions', 'preset', 'defaultPermissions'));
    }

    public function update(Request $request, Role $role)
    {
        $availablePermissions = RolePermissionRegistry::all();

        $validated = $request->validate([
            'label' => ['nullable', 'string', 'max:100'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in($availablePermissions)],
        ]);

        $role->update([
            'label' => $validated['label'] ?? null,
        ]);

        RolePermission::where('role', $role->name)->delete();

        $selectedPermissions = collect($validated['permissions'] ?? [])
            ->filter(fn ($permission) => in_array($permission, $availablePermissions, true))
            ->values()
            ->all();

        foreach ($selectedPermissions as $permission) {
            RolePermission::create([
                'role' => $role->name,
                'permission' => $permission,
            ]);
        }

        Cache::forget("role_permissions.{$role->name}");

        return redirect()->route('roles.index')->with('success', 'Role berhasil diperbarui.');
    }

    public function destroy(Role $role)
    {
        // Prevent deleting a role that is still assigned to users.
        if ($role->users()->exists()) {
            return back()->with('error', 'Role tidak dapat dihapus karena masih digunakan oleh user. Hapus atau pindahkan user terlebih dahulu.');
        }

        // Also remove any role_permissions entries for cleanliness.
        RolePermission::where('role', $role->name)->delete();

        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Role berhasil dihapus.');
    }
}
