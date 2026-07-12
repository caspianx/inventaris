<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install Admin - Inventory App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5" style="max-width: 560px;">
    <div class="card shadow-sm">
        <div class="card-body">
            <h4 class="mb-4">Install Admin Pertama</h4>

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('status'))
                <div class="alert alert-success d-flex justify-content-between align-items-center" role="alert" id="install-success-alert">
                    <div class="me-3">{{ session('status') }}</div>
                    <div>
                        <button type="button" class="btn btn-success btn-sm" id="install-success-ok">OK</button>
                    </div>
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded', function(){
                        var okBtn = document.getElementById('install-success-ok');
                        if(!okBtn) return;
                        var redirectTimer = null;
                        @if(session('redirect_to_migrate'))
                            // auto-redirect after 2.5s if OK not clicked
                            redirectTimer = setTimeout(function(){
                                window.location.href = '{{ route('install.migrate') }}';
                            }, 2500);
                        @endif

                        okBtn.addEventListener('click', function(){
                            @if(session('redirect_to_migrate'))
                                if(redirectTimer) clearTimeout(redirectTimer);
                                window.location.href = '{{ route('install.migrate') }}';
                            @else
                                var a = document.getElementById('install-success-alert'); if(a) a.style.display = 'none';
                            @endif
                        });
                    });
                </script>
            @endif

            @if(!empty($database_error))
                <div class="alert alert-warning">{{ $database_error }}</div>
            @endif

            @if(request()->query('db_unavailable'))
                <div class="alert alert-warning">Tidak dapat terhubung ke database. Silakan periksa konfigurasi database dan coba lagi.</div>
            @endif

            @if (! $ready)
                <div class="alert alert-warning">
                    Tabel pengguna belum ada. Tekan tombol di bawah untuk menjalankan <code>php artisan migrate --force</code> dan seed otomatis.
                </div>
            @endif

            @if (! $ready)
                <div class="card border-0 bg-light p-3 mb-4">
                    <h6 class="mb-3">Konfigurasi Database</h6>
                    <form method="POST" action="{{ route('install.setup') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Jenis Database</label>
                            <select name="db_connection" class="form-select">
                                <option value="mysql" selected>MySQL</option>
                                <option value="sqlite">SQLite</option>
                                <option value="pgsql">PostgreSQL</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Host</label>
                            <input type="text" name="db_host" class="form-control" value="127.0.0.1">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Port</label>
                            <input type="text" name="db_port" class="form-control" value="3306">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Database</label>
                            <input type="text" name="db_database" class="form-control" placeholder="inventory_app" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="db_username" class="form-control" value="root">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="db_password" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Create Database</button>
                    </form>
                    <!-- Menu Migrate Database link removed per request; migrate runs from migrate menu -->
                </div>
            @endif

                <!-- redirect handled by OK button when needed -->

            @if ($errors->has('install'))
                <div class="alert alert-danger mt-3">{{ $errors->first('install') }}</div>
            @endif

            @if ($ready)
                <p>Belum ada admin di database. Isi form berikut untuk membuat admin pertama.</p>
                <form method="POST" action="{{ route('install.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Nama Admin</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Buat Admin</button>
                </form>
            @endif
        </div>
    </div>
</div>
</body>
</html>
