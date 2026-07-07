@extends('layouts.app')
@section('title', 'Profil Saya')

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PUT')

            <h5>Pengaturan Perangkat</h5>

            <div class="mb-3 form-check">
                <input type="hidden" name="has_cash_drawer" value="0">
                <input type="checkbox" name="has_cash_drawer" value="1" class="form-check-input" id="hasCashDrawer" {{ old('has_cash_drawer', $user->has_cash_drawer) ? 'checked' : '' }}>
                <label class="form-check-label" for="hasCashDrawer">Saya memiliki perangkat Cash Drawer (laci kas)</label>
            </div>

            <div class="mb-3 form-check">
                <input type="hidden" name="auto_open_cash_drawer" value="0">
                <input type="checkbox" name="auto_open_cash_drawer" value="1" class="form-check-input" id="autoOpenCashDrawer" {{ old('auto_open_cash_drawer', $user->auto_open_cash_drawer) ? 'checked' : '' }}>
                <label class="form-check-label" for="autoOpenCashDrawer">Buka otomatis setelah pembayaran</label>
            </div>

            <button class="btn btn-primary">Simpan</button>
        </form>

        <div class="mt-3">
            <form method="POST" action="{{ route('profile.test-cash-drawer') }}">
                @csrf
                <button class="btn btn-outline-primary"><i class="bi bi-plug"></i> Tes Buka Cash Drawer</button>
                <div class="form-text mt-2">Mengirim percobaan buka cash drawer menggunakan alamat pada Pengaturan Toko.</div>
            </form>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const profileTestForm = document.querySelector('form[action="{{ route('profile.test-cash-drawer') }}"]');
                if (!profileTestForm) return;

                const resultWrap = document.createElement('div');
                resultWrap.className = 'mt-2';
                profileTestForm.parentNode.insertBefore(resultWrap, profileTestForm.nextSibling);

                profileTestForm.addEventListener('submit', async function (e) {
                    e.preventDefault();
                    const btn = profileTestForm.querySelector('button[type="submit"]');
                    const original = btn.innerHTML;
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Mengirim...';

                    try {
                        const fd = new FormData(profileTestForm);
                        const res = await fetch(profileTestForm.action, {
                            method: 'POST',
                            body: fd,
                            credentials: 'same-origin',
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        });

                        const data = await res.json().catch(() => null);
                        if (res.ok && data && data.success) {
                            resultWrap.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
                        } else {
                            const msg = data && data.message ? data.message : `HTTP ${res.status} - ${res.statusText}`;
                            resultWrap.innerHTML = `<div class="alert alert-danger">${msg}</div>`;
                        }
                    } catch (err) {
                        resultWrap.innerHTML = `<div class="alert alert-danger">${err.message}</div>`;
                    } finally {
                        btn.disabled = false;
                        btn.innerHTML = original;
                    }
                });
            });
        </script>
    </div>
</div>
@endsection
