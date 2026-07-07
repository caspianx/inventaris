@extends('layouts.app')
@section('title', 'Tambah Barang')

@section('content')
<div style="max-width: 800px; margin: 0 auto;">
    <div class="card">
        <div class="card-header">
            <i class="bi bi-box-seam"></i> Form Tambah Barang
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('items.store') }}">
                @csrf
                <div class="row g-4">
                    <!-- SKU (Auto-generated) -->
                    <div class="col-md-6">
                        <label class="form-label fw-600">SKU <span style="color: var(--danger);">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control" value="{{ old('sku', $autoSku) }}" readonly style="background: var(--gray-100);">
                            <span class="input-group-text" style="background: var(--gray-100);">
                                <i class="bi bi-lock" style="color: var(--gray-500);"></i>
                            </span>
                        </div>
                        <small style="color: var(--gray-500);">SKU dibuat otomatis, tidak dapat diubah</small>
                    </div>

                    <!-- Nama Barang -->
                    <div class="col-md-6">
                        <label class="form-label fw-600">Nama Barang <span style="color: var(--danger);">*</span></label>
                        <input type="text" name="name" id="name-input" class="form-control" value="{{ old('name') }}" placeholder="Contoh: Kemeja Putih XL" required autocomplete="off">
                        <div id="duplicate-warning" class="alert alert-warning mt-2 py-2 px-3 d-none" style="border-left: 4px solid var(--warning);"></div>
                    </div>

                    <!-- Kategori -->
                    <div class="col-md-6">
                        <label class="form-label fw-600">Kategori</label>
                        <div class="d-flex gap-2">
                            <select name="category_id" id="category-select" class="form-select">
                                <option value="">- Tanpa Kategori -</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @if(auth()->user()->canAccess('categories.manage'))
                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#newCategoryModal" title="Tambah kategori baru">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                            @endif
                        </div>
                    </div>

                    <!-- Satuan -->
                    <div class="col-md-6">
                        <label class="form-label fw-600">Satuan <span style="color: var(--danger);">*</span></label>
                        <input type="text" name="unit" class="form-control" value="{{ old('unit', 'pcs') }}" placeholder="pcs, box, pack, dll" required>
                    </div>

                    <!-- Harga Beli -->
                    <div class="col-md-6">
                        <label class="form-label fw-600">Harga Beli <span style="color: var(--danger);">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text" style="background: var(--gray-100); color: var(--gray-600);">Rp</span>
                            <input type="number" step="0.01" name="purchase_price" class="form-control numeric-autoselect" value="{{ old('purchase_price', 0) }}" placeholder="0" required>
                        </div>
                    </div>

                    <!-- Harga Jual -->
                    <div class="col-md-6">
                        <label class="form-label fw-600">Harga Jual <span style="color: var(--danger);">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text" style="background: var(--gray-100); color: var(--gray-600);">Rp</span>
                            <input type="number" step="0.01" name="selling_price" class="form-control numeric-autoselect" value="{{ old('selling_price', 0) }}" placeholder="0" required>
                        </div>
                    </div>

                    <!-- Stok Minimum -->
                    <div class="col-md-6">
                        <label class="form-label fw-600">Stok Minimum</label>
                        <input type="number" name="min_stock" class="form-control numeric-autoselect" value="{{ old('min_stock', 0) }}" placeholder="0">
                        <small style="color: var(--gray-500);">Akan memberikan notifikasi ketika stok dibawah nilai ini</small>
                    </div>

                    <!-- Stok Awal -->
                    <div class="col-md-6">
                        <label class="form-label fw-600">Stok Awal</label>
                        <input type="number" name="current_stock" class="form-control numeric-autoselect" value="{{ old('current_stock', 0) }}" placeholder="0" required>
                    </div>

                    <!-- Deskripsi -->
                    <div class="col-12">
                        <label class="form-label fw-600">Deskripsi</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Informasi tambahan tentang barang ini...">{{ old('description') }}</textarea>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-5 d-flex gap-2">
                    <button type="submit" id="submit-btn" class="btn btn-primary" style="padding: 0.75rem 2rem;">
                        <i class="bi bi-check-lg"></i> Simpan Barang
                    </button>
                    <a href="{{ route('items.index') }}" class="btn btn-outline-secondary" style="padding: 0.75rem 2rem;">
                        <i class="bi bi-x-lg"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let duplicateCheckTimer;
const nameInput = document.getElementById('name-input');
const warningBox = document.getElementById('duplicate-warning');
const submitBtn = document.getElementById('submit-btn');

nameInput.addEventListener('input', function () {
    clearTimeout(duplicateCheckTimer);
    const name = this.value.trim();

    if (name.length < 2) {
        warningBox.classList.add('d-none');
        return;
    }

    duplicateCheckTimer = setTimeout(() => {
        fetch(`{{ route('items.check-duplicate') }}?name=${encodeURIComponent(name)}`)
            .then(res => res.json())
            .then(data => {
                if (data.exists) {
                    let actionText = 'Gunakan data barang yang sudah ada, bukan membuat barang baru.';

                    if (data.item.edit_url && data.item.stock_url) {
                        actionText = `<a href="${data.item.edit_url}" class="alert-link">Buka data ini</a> atau gunakan menu <a href="${data.item.stock_url}" class="alert-link">Stok Masuk</a> untuk menambah stoknya.`;
                    } else if (data.item.edit_url) {
                        actionText = `<a href="${data.item.edit_url}" class="alert-link">Buka data ini</a>.`;
                    } else if (data.item.stock_url) {
                        actionText = `Gunakan menu <a href="${data.item.stock_url}" class="alert-link">Stok Masuk</a> untuk menambah stoknya.`;
                    }

                    warningBox.innerHTML = `<i class="bi bi-exclamation-triangle"></i> Barang "<strong>${data.item.name}</strong>" (SKU: ${data.item.sku}) sudah terdaftar dengan stok saat ini <strong>${data.item.current_stock}</strong>. ${actionText}`;
                    warningBox.classList.remove('d-none');
                } else {
                    warningBox.classList.add('d-none');
                }
            });
    }, 400); // debounce biar tidak spam request tiap ketikan
});
</script>
@if(auth()->user()->canAccess('categories.manage'))
<div class="modal fade" id="newCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="new-category-form">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Kategori Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="new-category-error" class="alert alert-danger d-none"></div>
                    <div class="mb-3">
                        <label class="form-label">Nama Kategori</label>
                        <input type="text" name="name" id="new-category-name" class="form-control" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi (opsional)</label>
                        <textarea name="description" id="new-category-description" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="new-category-submit">Simpan Kategori</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<script>
// Fitur 1: Tambah kategori baru langsung dari form Tambah Barang (via modal, tanpa reload halaman)
@if(auth()->user()->canAccess('categories.manage'))
const newCategoryForm = document.getElementById('new-category-form');
const newCategoryModalEl = document.getElementById('newCategoryModal');
const newCategoryModal = new bootstrap.Modal(newCategoryModalEl);
const newCategoryError = document.getElementById('new-category-error');
const newCategorySubmit = document.getElementById('new-category-submit');
const categorySelect = document.getElementById('category-select');

newCategoryForm.addEventListener('submit', function (e) {
    e.preventDefault();
    newCategoryError.classList.add('d-none');
    newCategorySubmit.disabled = true;
    newCategorySubmit.textContent = 'Menyimpan...';

    fetch('{{ route('categories.store') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
        },
        body: JSON.stringify({
            name: document.getElementById('new-category-name').value,
            description: document.getElementById('new-category-description').value,
        }),
    })
    .then(async (res) => {
        const data = await res.json();

        if (!res.ok) {
            const message = data.message || (data.errors ? Object.values(data.errors)[0][0] : 'Gagal menyimpan kategori.');
            throw new Error(message);
        }

        return data;
    })
    .then((category) => {
        const option = document.createElement('option');
        option.value = category.id;
        option.textContent = category.name;
        option.selected = true;
        categorySelect.appendChild(option);

        newCategoryForm.reset();
        newCategoryModal.hide();
    })
    .catch((err) => {
        newCategoryError.textContent = err.message;
        newCategoryError.classList.remove('d-none');
    })
    .finally(() => {
        newCategorySubmit.disabled = false;
        newCategorySubmit.textContent = 'Simpan Kategori';
    });
});
@endif

// Fitur 2: Angka 0 default otomatis terpilih (ter-select) saat field pertama kali difokus,
// jadi begitu mulai mengetik, angka 0 langsung tergantikan tanpa perlu dihapus manual dulu.
document.querySelectorAll('.numeric-autoselect').forEach(function (input) {
    input.addEventListener('focus', function () {
        this.select();
    });
});
</script>
@endsection
