@extends('layouts.app')
@section('title', 'Edit Role')

@section('content')
<div class="card shadow-sm" style="max-width:600px;">
    <div class="card-body">
        <form method="POST" action="{{ route('roles.update', $role) }}">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label">Nama</label>
                <input type="text" class="form-control" value="{{ $role->name }}" disabled>
            </div>
            <div class="mb-3">
                <label class="form-label">Label</label>
                <input type="text" name="label" class="form-control" value="{{ old('label', $role->label) }}">
            </div>
            <button class="btn btn-primary">Update</button>
            <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">Batal</a>
        </form>
    </div>
</div>
@endsection
