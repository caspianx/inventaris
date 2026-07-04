@extends('layouts.app')
@section('title', 'Edit Barang')

@section('content')
<div class="card shadow-sm" style="max-width: 700px;">
    <div class="card-body">
        <form method="POST" action="{{ route('items.update', $item) }}">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">SKU</label>
                    <input type="text" name="sku" class="form-control" value="{{ old('sku', $item->sku) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nama Barang</label>
                    <input type="text" name="name" id="name-input" class="form-control" value="{{ old('name', $item->name) }}" required autocomplete="off">
                    <div id="duplicate-warning" class="alert alert-warning mt-2 py-2 px-3 d-none"></div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Kategori</label>
                    <select name="category_id" class="form-select">
                        <option value="">- Tanpa Kategori -</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $item->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Satuan</label>
                    <input type="text" name="unit" class="form-control" value="{{ old('unit', $item->unit) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Harga Beli</label>
                    <input type="number" step="0.01" name="purchase_price" class="form-control" value="{{ old('purchase_price', $item->purchase_price) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Harga Jual</label>
                    <input type="number" step="0.01" name="selling_price" class="form-control" value="{{ old('selling_price', $item->selling_price) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Stok Minimum</label>
                    <input type="number" name="min_stock" class="form-control" value="{{ old('min_stock', $item->min_stock) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Stok Saat Ini</label>
                    <input type="number" class="form-control" value="{{ $item->current_stock }}" disabled>
                    <div class="form-text">Ubah stok lewat menu Stok Masuk/Keluar.</div>
                </div>
                <div class="col-12">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" class="form-control" rows="2">{{ old('description', $item->description) }}</textarea>
                </div>
            </div>
            <div class="mt-4">
                <button class="btn btn-primary">Update</button>
                <a href="{{ route('items.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<script>
let duplicateCheckTimer;
const nameInput = document.getElementById('name-input');
const warningBox = document.getElementById('duplicate-warning');

nameInput.addEventListener('input', function () {
    clearTimeout(duplicateCheckTimer);
    const name = this.value.trim();

    if (name.length < 2) {
        warningBox.classList.add('d-none');
        return;
    }

    duplicateCheckTimer = setTimeout(() => {
        const params = new URLSearchParams({
            name,
            exclude_id: '{{ $item->id }}',
        });

        fetch(`{{ route('items.check-duplicate') }}?${params.toString()}`)
            .then(res => res.json())
            .then(data => {
                if (data.exists) {
                    warningBox.innerHTML = `<i class="bi bi-exclamation-triangle"></i> Barang "<strong>${data.item.name}</strong>" (SKU: ${data.item.sku}) sudah terdaftar dengan stok saat ini <strong>${data.item.current_stock}</strong>. `
                        + `<a href="${data.item.edit_url}" class="alert-link">Buka data ini</a>.`;
                    warningBox.classList.remove('d-none');
                } else {
                    warningBox.classList.add('d-none');
                }
            });
    }, 400);
});
</script>
@endsection
