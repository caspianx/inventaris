@extends('layouts.app')
@section('title', 'Tambah Role')

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('roles.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nama (machine)</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    <div class="form-text">Contoh: sales_rep, warehouse_manager — gunakan huruf, angka, dash atau underscore.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Label (tampil)</label>
                    <input type="text" name="label" class="form-control" value="{{ old('label') }}">
                </div>
            </div>

            <div class="mt-4">
                <label class="form-label">Preset akses</label>
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <a href="{{ route('roles.create', ['preset' => 'staff']) }}" class="btn btn-sm btn-outline-secondary">Staff</a>
                    <a href="{{ route('roles.create', ['preset' => 'manager']) }}" class="btn btn-sm btn-outline-secondary">Manager</a>
                    <a href="{{ route('roles.create', ['preset' => 'admin']) }}" class="btn btn-sm btn-outline-secondary">Admin</a>
                </div>
                @if($preset)
                    <div class="alert alert-info py-2">Preset {{ ucfirst($preset) }} diterapkan.</div>
                @endif
                <div class="d-flex align-items-center gap-2 mt-2">
                    <input id="presetNameInput" type="text" class="form-control form-control-sm" placeholder="Nama preset (mis. gudang_staff)" style="max-width:260px">
                    <input id="presetLabelInput" type="text" class="form-control form-control-sm" placeholder="Label (tampilan)" style="max-width:260px">
                    <button id="savePresetBtn" type="button" class="btn btn-sm btn-outline-primary">Save Preset</button>
                </div>
                <div id="presetFeedback" class="form-text text-success mt-1" style="display:none;"></div>
            </div>

            <div class="mt-4">
                <h5 class="mb-3">Pilih Akses Menu</h5>
                @foreach($groups as $groupName => $permissions)
                    <div class="border rounded p-3 mb-3">
                        <h6 class="mb-3">{{ $groupName }}</h6>
                        <div class="row g-2">
                            @foreach($permissions as $permission => $label)
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission }}" id="perm_{{ Str::slug($permission) }}" {{ in_array($permission, old('permissions', $defaultPermissions), true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="perm_{{ Str::slug($permission) }}">{{ $label }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <button class="btn btn-primary">Simpan</button>
            <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">Batal</a>
        </form>
    </div>
</div>
<script>
    document.getElementById('savePresetBtn').addEventListener('click', async function () {
        const name = document.getElementById('presetNameInput').value.trim();
        const label = document.getElementById('presetLabelInput').value.trim();
        if (!name) {
            alert('Masukkan nama preset (format machine, gunakan huruf, angka, dash).');
            return;
        }

        // collect checked permissions
        const checked = Array.from(document.querySelectorAll('input[name="permissions[]"]:checked')).map(i => i.value);

        try {
            const res = await fetch('{{ route('role-presets.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ name, label, permissions: checked })
            });

            if (!res.ok) {
                const err = await res.json().catch(() => null);
                throw new Error(err?.message || 'Gagal menyimpan preset');
            }

            const data = await res.json();
            const fb = document.getElementById('presetFeedback');
            fb.innerText = 'Preset tersimpan: ' + (data.preset.label || data.preset.name);
            fb.style.display = 'block';
        } catch (ex) {
            alert('Gagal menyimpan preset: ' + (ex.message || ex));
        }
    });
</script>
@endsection
