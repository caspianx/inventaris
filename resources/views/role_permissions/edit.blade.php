@extends('layouts.app')
@section('title', 'Pengaturan Akses Role')

@section('content')
<form method="POST" action="{{ route('role-permissions.update') }}">
    @csrf
    @method('PUT')

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <div>
                <h6 class="mb-0">Hak Akses Menu & Fitur</h6>
                <div class="text-muted small">Admin selalu memiliki akses penuh.</div>
            </div>
            <button class="btn btn-primary"><i class="bi bi-save"></i> Simpan Akses</button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="min-width: 260px;">Menu / Fitur</th>
                            @foreach($roles as $role => $label)
                                <th class="text-center" style="width: 160px;">{{ $label }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($groups as $group => $permissions)
                            <tr class="table-secondary">
                                <th colspan="{{ count($roles) + 1 }}">{{ $group }}</th>
                            </tr>
                            @foreach($permissions as $permission => $label)
                                <tr>
                                    <td>{{ $label }}</td>
                                    @foreach($roles as $role => $roleLabel)
                                        @php
                                            $checked = in_array($permission, $rolePermissions[$role] ?? [], true);
                                            $isAdmin = $role === 'admin';
                                        @endphp
                                        <td class="text-center">
                                            <input type="checkbox"
                                                   name="permissions[{{ $role }}][]"
                                                   value="{{ $permission }}"
                                                   class="form-check-input"
                                                   {{ $checked || $isAdmin ? 'checked' : '' }}
                                                   {{ $isAdmin ? 'disabled' : '' }}>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white text-end">
            <button class="btn btn-primary"><i class="bi bi-save"></i> Simpan Akses</button>
        </div>
    </div>
</form>
@endsection
