@extends('layouts.app')
@section('title', 'Edit User')

@section('content')
<div class="card shadow-sm" style="max-width: 600px;">
    <div class="card-body">
        <form method="POST" action="{{ route('users.update', $user) }}">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label">Nama</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Role</label>
                <select name="role" class="form-select" required {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                    @foreach($roles as $r)
                        <option value="{{ $r->name }}" {{ old('role', $user->role) == $r->name ? 'selected' : '' }}>{{ $r->label ?? ucfirst($r->name) }}</option>
                    @endforeach
                </select>
                @if($user->id === auth()->id())
                    <div class="form-text text-warning">Anda tidak bisa mengubah role akun sendiri.</div>
                    <input type="hidden" name="role" value="{{ $user->role }}">
                @endif
            </div>
            <div class="mb-3">
                <label class="form-label">Password Baru</label>
                <input type="password" name="password" class="form-control">
                <div class="form-text">Kosongkan jika tidak ingin mengubah password.</div>
            </div>
            <div class="mb-3">
                <label class="form-label">Konfirmasi Password Baru</label>
                <input type="password" name="password_confirmation" class="form-control">
            </div>
            <button class="btn btn-primary">Update</button>
            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Batal</a>
        </form>
    </div>
</div>
@endsection
