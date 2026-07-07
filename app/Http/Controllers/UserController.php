<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::when($request->search, fn ($q) => $q->where(function ($query) use ($request) {
            $query->where('name', 'like', "%{$request->search}%")
                ->orWhere('email', 'like', "%{$request->search}%");
        }))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('users.index', compact('users'));
    }

    public function autocomplete(Request $request)
    {
        $search = trim((string) $request->query('search'));

        if (strlen($search) < 2) {
            return response()->json([]);
        }

        $users = User::where(function ($query) use ($search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        })
            ->orderBy('name')
            ->limit(10)
            ->get(['name', 'email', 'role']);

        return response()->json($users->map(fn ($user) => [
            'value' => $user->name,
            'title' => $user->name,
            'subtitle' => $user->email.' - '.strtoupper($user->role),
        ]));
    }

    public function create()
    {
        $roles = Role::orderBy('name')->get();

        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $roleNames = Role::pluck('name')->all();

        // If name already exists, return early with a suggested alternative
        $name = trim((string) $request->input('name'));
        if ($name !== '' && User::where('name', $name)->exists()) {
            $suggestion = $this->makeUniqueNameSuggestion($name);
            return back()->withInput()->withErrors(['name' => 'Nama tersebut sudah digunakan. Gunakan saran nama berikut atau pilih lain.'])->with('suggested_name', $suggestion);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:users,name'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            'role' => ['required', 'in:'.implode(',', $roleNames)],
        ], [
            'name.unique' => 'Nama tersebut sudah digunakan. Silakan pilih nama lain.',
        ]);

        $role = Role::where('name', $validated['role'])->first();

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'role_id' => $role?->id,
        ]);

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        $roles = Role::orderBy('name')->get();

        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $roleNames = Role::pluck('name')->all();

        // If name already exists on another user, return with suggestion
        $name = trim((string) $request->input('name'));
        $other = User::where('name', $name)->where('id', '!=', $user->id)->first();
        if ($name !== '' && $other) {
            $suggestion = $this->makeUniqueNameSuggestion($name);
            return back()->withInput()->withErrors(['name' => 'Nama tersebut sudah digunakan oleh pengguna lain.'])->with('suggested_name', $suggestion);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:users,name,'.$user->id],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'role' => ['required', 'in:'.implode(',', $roleNames)],
            'password' => ['nullable', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ], [
            'name.unique' => 'Nama tersebut sudah digunakan. Silakan pilih nama lain.',
        ]);

        // Cegah admin tidak sengaja menurunkan/menghilangkan role admin miliknya sendiri
        // (bisa berakibat tidak ada lagi admin yang bisa mengelola user).
        if ($user->id === $request->user()->id && $validated['role'] !== 'admin') {
            return back()->with('error', 'Anda tidak bisa mengubah role akun sendiri.')->withInput();
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];
        $user->role_id = Role::where('name', $validated['role'])->value('id');

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui.');
    }

    /**
     * Generate a unique name suggestion by appending a numeric suffix.
     */
    protected function makeUniqueNameSuggestion(string $base): string
    {
        // Suggestion format: base + number (no underscore), keep only alnum and dash/underscore
        $baseClean = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '', $base));
        $candidate = $baseClean;
        $i = 1;
        while (User::where('name', $candidate)->exists()) {
            $candidate = $baseClean.'-'.$i;
            $i++;
            if ($i > 1000) break;
        }

        return $candidate;
    }

    /**
     * AJAX endpoint to check if a name is available and provide a suggestion.
     */
    public function checkName(Request $request)
    {
        $name = trim((string) $request->query('name'));
        if ($name === '') {
            return response()->json(['available' => false, 'suggestion' => ''], 200);
        }

        $available = ! User::where('name', $name)->exists();
        $suggestion = $available ? $name : $this->makeUniqueNameSuggestion($name);

        return response()->json(['available' => $available, 'suggestion' => $suggestion], 200);
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Anda tidak bisa menghapus akun Anda sendiri.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
    }
}
