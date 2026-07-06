@extends('layouts.app')
@section('title', 'Tambah Role')

@section('content')
<div class="card shadow-sm" style="max-width:600px;">
    <div class="card-body">
        <form method="POST" action="{{ route('roles.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Nama (machine)</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                <div class="form-text">Contoh: sales_rep, warehouse_manager — gunakan huruf, angka, dash atau underscore.</div>
            </div>
            <div class="mb-3">
                <label class="form-label">Label (tampil)</label>
                <input type="text" name="label" class="form-control" value="{{ old('label') }}">
            </div>
            <button class="btn btn-primary">Simpan</button>
            <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">Batal</a>
        </form>
    </div>
</div>
@endsection
