@extends('layouts.app')
@section('title', 'Pengaturan Toko')

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('store-settings.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row g-4">
                <div class="col-md-7">
                    <div class="mb-3">
                        <label class="form-label">Nama Toko</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $storeSetting->name) }}" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Alamat Toko</label>
                        <textarea name="address" class="form-control" rows="4">{{ old('address', $storeSetting->address) }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Logo Toko</label>
                        <input type="file" name="logo" class="form-control" accept="image/png,image/jpeg,image/webp">
                        <div class="form-text">Format: JPG, PNG, atau WEBP. Maksimal 2 MB.</div>
                    </div>

                    @if($storeSetting->logo_path)
                        <div class="form-check mb-3">
                            <input type="checkbox" name="remove_logo" value="1" class="form-check-input" id="removeLogo">
                            <label class="form-check-label" for="removeLogo">Hapus logo saat ini</label>
                        </div>
                    @endif

                    <button class="btn btn-primary"><i class="bi bi-save"></i> Simpan Pengaturan</button>
                </div>

                <div class="col-md-5">
                    <div class="border rounded p-3 bg-light">
                        <div class="text-muted small mb-2">Preview</div>
                        <div class="bg-white rounded border p-3 text-center">
                            @if($storeSetting->logo_path)
                                <img src="{{ asset($storeSetting->logo_path) }}" alt="Logo {{ $storeSetting->name }}" class="mb-2" style="max-width: 120px; max-height: 80px; object-fit: contain;">
                            @else
                                <div class="d-inline-flex align-items-center justify-content-center bg-secondary bg-opacity-10 rounded mb-2" style="width:80px;height:80px;">
                                    <i class="bi bi-shop fs-2 text-secondary"></i>
                                </div>
                            @endif
                            <h5 class="mb-1">{{ old('name', $storeSetting->name) }}</h5>
                            <div class="text-muted small" style="white-space: pre-line;">{{ old('address', $storeSetting->address) ?: '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
