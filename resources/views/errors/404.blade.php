@extends('layouts.app')

@section('title', 'Halaman Tidak Ditemukan')

@section('content')
<div class="container py-5">
    <div class="card mx-auto" style="max-width: 720px;">
        <div class="card-body text-center py-5">
            <div class="mb-4">
                <i class="bi bi-exclamation-triangle-fill text-warning" style="font-size: 4rem;"></i>
            </div>
            <h1 class="h2 mb-3">404 — Halaman Tidak Ditemukan</h1>
            <p class="text-muted mb-4">Halaman yang Anda cari tidak ditemukan atau mungkin sudah dipindahkan.</p>
            <a href="{{ route('dashboard') }}" class="btn btn-primary">
                <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
            </a>
        </div>
    </div>
</div>
@endsection
