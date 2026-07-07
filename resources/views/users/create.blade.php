@extends('layouts.app')
@section('title', 'Tambah User')

@section('content')
<div class="card shadow-sm" style="max-width: 600px;">
    <div class="card-body">
        <form method="POST" action="{{ route('users.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Nama</label>
                <input type="text" name="name" id="user-name-input" class="form-control" value="{{ old('name') }}" required autofocus>
                @if(session('suggested_name'))
                    <div class="form-text text-warning">Nama sudah dipakai. Saran: <a href="#" id="apply-suggested-name">{{ session('suggested_name') }}</a></div>
                @endif
                <div id="name-suggestion-area" class="form-text text-info" style="display:none;"></div>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Role</label>
                <select name="role" class="form-select" required>
                    @foreach($roles as $r)
                        <option value="{{ $r->name }}" {{ old('role') == $r->name ? 'selected' : '' }}>{{ $r->label ?? ucfirst($r->name) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" class="form-control" required>
            </div>
            <button class="btn btn-primary">Simpan</button>
            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Batal</a>
        </form>
    </div>
</div>
<script>
    document.addEventListener('click', function (e) {
        if (e.target && e.target.id === 'apply-suggested-name') {
            e.preventDefault();
            var suggested = e.target.innerText.trim();
            var input = document.getElementById('user-name-input');
            if (input) input.value = suggested;
            e.target.parentNode.style.display = 'none';
        }
    });

    // Debounced AJAX check for name availability
    (function() {
        var timer = null;
        var input = document.getElementById('user-name-input');
        var area = document.getElementById('name-suggestion-area');
        if (!input) return;
        input.addEventListener('input', function () {
            clearTimeout(timer);
            timer = setTimeout(async function () {
                var name = input.value.trim();
                if (!name) { area.style.display = 'none'; return; }
                try {
                    const url = new URL("{{ route('users.check-name') }}", window.location.origin);
                    url.searchParams.set('name', name);
                    const res = await fetch(url.toString(), { credentials: 'same-origin' });
                    if (!res.ok) throw new Error('Network');
                    const json = await res.json();
                    if (json.available) {
                        area.innerText = 'Nama tersedia.';
                        area.style.color = 'green';
                    } else {
                        area.innerHTML = 'Nama sudah dipakai. Saran: <a href="#" id="apply-suggested-name">'+json.suggestion+'</a>';
                        area.style.color = 'orange';
                    }
                    area.style.display = 'block';
                } catch (err) {
                    area.style.display = 'none';
                }
            }, 400);
        });
    })();
</script>
@endsection
