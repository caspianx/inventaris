@extends('layouts.app')
@section('title', 'Edit Role')

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('roles.update', $role) }}">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nama</label>
                    <input type="text" class="form-control" value="{{ $role->name }}" disabled>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Label</label>
                    <input type="text" name="label" class="form-control" value="{{ old('label', $role->label) }}">
                </div>
            </div>

            <div class="mt-4">
                <h5 class="mb-3">Ubah Akses Menu</h5>
                <label class="form-label">Preset akses</label>
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <a href="{{ route('roles.edit', ['role' => $role->id, 'preset' => 'staff']) }}" class="btn btn-sm btn-outline-secondary">Staff</a>
                    <a href="{{ route('roles.edit', ['role' => $role->id, 'preset' => 'manager']) }}" class="btn btn-sm btn-outline-secondary">Manager</a>
                    <a href="{{ route('roles.edit', ['role' => $role->id, 'preset' => 'admin']) }}" class="btn btn-sm btn-outline-secondary">Admin</a>
                </div>
                @if($preset)
                    <div class="alert alert-info py-2">Preset {{ ucfirst($preset) }} diterapkan.</div>
                @endif
                @foreach($groups as $groupName => $permissions)
                    <div class="border rounded p-3 mb-3">
                        <h6 class="mb-3">{{ $groupName }}</h6>
                        <div class="row g-2">
                            @foreach($permissions as $permission => $label)
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission }}" id="perm_{{ Str::slug($permission) }}" {{ in_array($permission, old('permissions', $defaultPermissions), true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="perm_{{ Str::slug($permission) }}">{{ $label }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <button class="btn btn-primary">Update</button>
            <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">Batal</a>
        </form>
    </div>
</div>
@endsection
