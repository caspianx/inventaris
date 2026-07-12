<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Migrate Existing Tables</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5" style="max-width: 900px;">
    <div class="card shadow-sm">
        <div class="card-body">
            <h4 class="mb-4">Migrate Existing Tables</h4>

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
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            <div class="d-flex justify-content-between align-items-center mb-2">
                <p class="mb-0">Daftar migrasi (menjalankan migrasi hanya membuat struktur tabel tanpa memasukkan data seed).</p>
                <div>
                    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#migrationsList" aria-expanded="false" aria-controls="migrationsList">
                        Toggle Migrations
                    </button>
                </div>
            </div>

            <form method="POST" action="{{ route('install.migrate.run') }}">
                @csrf
                <div class="collapse" id="migrationsList">
                    <div style="max-height:400px; overflow:auto;">
                        <table class="table table-striped">
                    <thead>
                    <tr>
                        <th>Migration</th>
                        <th>Table</th>
                        <th>Exists</th>
                        <th>Marked</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($items as $it)
                        <tr>
                            <td>{{ $it['migration'] }}</td>
                            <td>{{ $it['table'] ?? '-' }}</td>
                            <td>{{ $it['exists'] ? 'Yes' : 'No' }}</td>
                            <td>{{ $it['marked'] ? 'Yes' : 'No' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-3">
                    <button class="btn btn-primary">Run Migrations (struktur saja)</button>
                    <a class="btn btn-link" href="{{ route('install') }}">Back to Install</a>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</html>