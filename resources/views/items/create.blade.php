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
                    <!-- SKU dan Nama -->
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <label class="form-label fw-semibold mb-0">SKU <span class="text-danger">*</span></label>
                                <div class="text-muted" style="font-size: 0.9rem;">Apakah SKU sudah ada?</div>
                            </div>
                            <div class="btn-group" role="group" aria-label="Pilihan SKU">
                                <input type="radio" class="btn-check" name="sku_mode" id="sku-existing" value="existing" autocomplete="off" @checked(old('sku_mode', 'existing') === 'existing')>
                                <label class="btn btn-outline-primary btn-sm" for="sku-existing">Ada</label>

                                <input type="radio" class="btn-check" name="sku_mode" id="sku-generated" value="generated" autocomplete="off" @checked(old('sku_mode', 'existing') === 'generated')>
                                <label class="btn btn-outline-secondary btn-sm" for="sku-generated">Tidak Ada</label>
                            </div>
                        </div>
                        <div class="input-group">
                            <input type="text" name="sku" id="sku-input" class="form-control" value="{{ old('sku', '') }}" placeholder="Masukkan SKU jika sudah ada" style="background: var(--white);">
                            <span class="input-group-text" id="sku-icon" style="background: var(--white);">
                                <i class="bi bi-pencil" id="sku-icon-mark" style="color: var(--gray-500);"></i>
                            </span>
                        </div>
                        <div id="sku-warning" class="alert alert-danger mt-2 py-2 px-3 d-none" style="border-left: 4px solid var(--danger);"></div>
                        <div class="form-text" id="sku-help">Pilih “Ada” jika SKU sudah tersedia dan ingin diedit, atau “Tidak Ada” untuk membuat otomatis.</div>
                    </div>

                    <div class="col-md-6 d-flex flex-column justify-content-start">
                        <label class="form-label fw-semibold">Nama Barang <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name-input" class="form-control" value="{{ old('name') }}" placeholder="Contoh: Kemeja Putih XL" required autocomplete="off">
                        <div id="duplicate-warning" class="alert alert-warning mt-2 py-2 px-3 d-none" style="border-left: 4px solid var(--warning);"></div>
                    </div>

                    <!-- Kategori dan Satuan -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Kategori</label>
                        <div class="input-group">
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

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Satuan <span class="text-danger">*</span></label>
                        <input type="text" name="unit" class="form-control" value="{{ old('unit', 'pcs') }}" placeholder="pcs, box, pack, dll" required>
                    </div>

                    <!-- Harga -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Harga Beli <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text" style="background: var(--gray-100); color: var(--gray-600);">Rp</span>
                            <input type="number" step="0.01" name="purchase_price" class="form-control numeric-autoselect" value="{{ old('purchase_price', 0) }}" placeholder="0" required>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Harga Jual <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text" style="background: var(--gray-100); color: var(--gray-600);">Rp</span>
                            <input type="number" step="0.01" name="selling_price" class="form-control numeric-autoselect" value="{{ old('selling_price', 0) }}" placeholder="0" required>
                        </div>
                    </div>

                    <!-- Stok -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Stok Minimum</label>
                        <input type="number" name="min_stock" class="form-control numeric-autoselect" value="{{ old('min_stock', 0) }}" placeholder="0">
                        <div class="form-text">Akan memberikan notifikasi ketika stok dibawah nilai ini.</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Stok Awal</label>
                        <input type="number" name="current_stock" class="form-control numeric-autoselect" value="{{ old('current_stock', 0) }}" placeholder="0" required>
                    </div>

                    <!-- Deskripsi -->
                    <div class="col-12">
                        <label class="form-label fw-semibold">Deskripsi</label>
                        <textarea name="description" class="form-control" rows="4" placeholder="Informasi tambahan tentang barang ini...">{{ old('description') }}</textarea>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-4 d-flex justify-content-end gap-2">
                    <a href="{{ route('items.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-lg"></i> Batal
                    </a>
                    <button type="submit" id="submit-btn" class="btn btn-primary px-4">
                        <i class="bi bi-check-lg"></i> Simpan Barang
                    </button>
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
const skuExistingRadio = document.getElementById('sku-existing');
const skuGeneratedRadio = document.getElementById('sku-generated');
const skuInput = document.getElementById('sku-input');
const skuHelp = document.getElementById('sku-help');
const skuIconMark = document.getElementById('sku-icon-mark');
const skuWarning = document.getElementById('sku-warning');

function updateSkuMode() {
    const isExisting = skuExistingRadio.checked;

    if (isExisting) {
        skuInput.readOnly = false;
        skuInput.value = skuInput.value || '{{ old('sku', '') }}';
        skuInput.placeholder = 'Masukkan SKU jika sudah ada';
        skuHelp.textContent = 'Pilih “Ada” jika SKU sudah tersedia dan ingin diedit, atau “Tidak Ada” untuk membuat otomatis.';
        skuIconMark.className = 'bi bi-pencil';
        skuWarning.classList.add('d-none');
    } else {
        skuInput.readOnly = true;
        skuInput.value = '{{ old('sku', $autoSku) }}';
        skuInput.placeholder = 'SKU akan dibuat otomatis';
        skuHelp.textContent = 'SKU akan dihasilkan otomatis sesuai pola terbaru.';
        skuIconMark.className = 'bi bi-shuffle';
        skuWarning.classList.add('d-none');
    }
}

skuExistingRadio.addEventListener('change', updateSkuMode);
skuGeneratedRadio.addEventListener('change', updateSkuMode);

updateSkuMode();

let skuCheckTimer;

skuInput.addEventListener('input', function () {
    clearTimeout(skuCheckTimer);

    if (!skuExistingRadio.checked) {
        skuWarning.classList.add('d-none');
        return;
    }

    const skuValue = this.value.trim();

    if (skuValue.length < 2) {
        skuWarning.classList.add('d-none');
        return;
    }

    skuCheckTimer = setTimeout(() => {
        fetch(`{{ route('items.check-duplicate') }}?sku=${encodeURIComponent(skuValue)}`)
            .then(res => res.json())
            .then(data => {
                if (data.exists && data.type === 'sku') {
                    skuWarning.innerHTML = `<i class="bi bi-exclamation-triangle"></i> SKU <strong>${skuValue}</strong> sudah digunakan oleh barang <strong>${data.item.name}</strong>.`;
                    if (data.item.edit_url) {
                        skuWarning.innerHTML += ` <a href="${data.item.edit_url}" class="alert-link">Buka data</a>`;
                    }
                    skuWarning.classList.remove('d-none');
                } else {
                    skuWarning.classList.add('d-none');
                }
            });
    }, 300);
});

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
