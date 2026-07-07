<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - {{ $storeSetting->name ?? 'Inventory App' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>body { background: #1e2a3a; min-height: 100vh; display: flex; align-items: center; }</style>
</head>
<body>
<div class="container" style="max-width: 400px;">
    <div class="card shadow">
        <div class="card-body p-4">
            <h4 class="text-center mb-4">
                @if(!empty($storeSetting->logo_path))
                    <img src="{{ asset($storeSetting->logo_path) }}" alt="{{ $storeSetting->name ?? 'Inventory App' }}" style="height:32px; width:auto; margin-right:8px; vertical-align:middle;">
                @else
                    <i class="bi bi-box-seam"></i>
                @endif
                {{ $storeSetting->name ?? 'Inventory App' }}
            </h4>

            @if($errors->any())
                <div class="alert alert-danger">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Nama</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" name="remember" class="form-check-input" id="remember">
                    <label class="form-check-label" for="remember">Ingat saya</label>
                </div>
                <button type="submit" class="btn btn-primary w-100">Masuk</button>
            </form>
            <p class="text-center mt-3 mb-0 text-muted small">Akun baru hanya dapat dibuat oleh admin melalui menu Manajemen User.</p>
        </div>
    </div>
</div>
</body>
</html>
