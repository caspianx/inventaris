@extends('layouts.app')

@section('title', 'Terjadi Kesalahan')

@section('content')
<div class="container py-5">
    <div class="card mx-auto" style="max-width: 720px;">
        <div class="card-body text-center py-5">
            <div class="mb-4">
                <i class="bi bi-bug-fill text-danger" style="font-size: 4rem;"></i>
            </div>
            <h1 class="h2 mb-3">500 — Terjadi Kesalahan</h1>
            <p class="text-muted mb-4">Maaf, ada masalah di server. Silakan coba lagi nanti atau hubungi administrator.</p>
            <a href="{{ route('dashboard') }}" class="btn btn-primary">
                <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
            </a>
        </div>
    </div>
</div>
@endsection
