<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\RolePermission;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with(['permissions'])->withCount('users')->orderBy('name')->paginate(15);

        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        return view('roles.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'alpha_dash', 'max:50', 'unique:roles,name'],
            'label' => ['nullable', 'string', 'max:100'],
        ]);

        Role::create($validated);

        return redirect()->route('roles.index')->with('success', 'Role berhasil dibuat.');
    }

    public function edit(Role $role)
    {
        return view('roles.edit', compact('role'));
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'label' => ['nullable', 'string', 'max:100'],
        ]);

        $role->update($validated);

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
