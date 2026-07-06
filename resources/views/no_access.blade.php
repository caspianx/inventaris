@extends('layouts.app')
@section('title', 'Tidak Ada Akses')

@section('content')
<div class="card shadow-sm">
    <div class="card-body text-center py-5">
        <div class="mb-3">
            <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-warning bg-opacity-10" style="width:64px;height:64px;">
                <i class="bi bi-shield-exclamation text-warning fs-3"></i>
            </span>
        </div>
        <h5>Belum Ada Akses Menu</h5>
        <p class="text-muted mb-0">Akun Anda belum diberi akses ke menu apa pun. Silakan hubungi admin.</p>
    </div>
</div>
@endsection
