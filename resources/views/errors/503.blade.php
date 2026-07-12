@extends('layouts.app')

@section('title', 'Layanan Tidak Tersedia')

@section('content')
<div class="container py-5">
    <div class="card mx-auto" style="max-width: 720px;">
        <div class="card-body text-center py-5">
            <div class="mb-4">
                <i class="bi bi-server" style="font-size: 4rem; color: #0d6efd;"></i>
            </div>
            <h1 class="h2 mb-3">503 — Layanan Tidak Tersedia</h1>
            <p class="text-muted mb-4">Sistem sedang dalam perawatan atau kelebihan beban. Silakan coba beberapa saat lagi.</p>
            <a href="{{ route('dashboard') }}" class="btn btn-primary">
                <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
            </a>
        </div>
    </div>
</div>
@endsection
