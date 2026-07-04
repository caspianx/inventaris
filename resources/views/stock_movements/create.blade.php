@extends('layouts.app')
@section('title', 'Catat Mutasi Stok')

@section('content')
<div class="card shadow-sm" style="max-width: 600px;">
    <div class="card-body">
        <form method="POST" action="{{ route('stock-movements.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Barang</label>
                <select name="item_id" class="form-select" required>
                    <option value="">- Pilih Barang -</option>
                    @foreach($items as $it)
                        <option value="{{ $it->id }}" {{ old('item_id', $selectedItemId ?? null) == $it->id ? 'selected' : '' }}>
                            {{ $it->name }} (stok saat ini: {{ $it->current_stock }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Tipe Mutasi</label>
                <select name="type" class="form-select" required>
                    <option value="in" {{ old('type', $selectedType ?? 'in') == 'in' ? 'selected' : '' }}>Stok Masuk</option>
                    <option value="out" {{ old('type', $selectedType ?? 'in') == 'out' ? 'selected' : '' }}>Stok Keluar</option>
                    <option value="adjustment" {{ old('type', $selectedType ?? 'in') == 'adjustment' ? 'selected' : '' }}>Adjustment (set ulang jumlah stok)</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Jumlah</label>
                <input type="number" name="quantity" class="form-control" min="1" value="{{ old('quantity') }}" required>
                <div class="form-text">Untuk adjustment, isi jumlah stok akhir yang benar (bukan selisih).</div>
            </div>
            <div class="mb-3">
                <label class="form-label">Catatan</label>
                <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
            </div>
            <button class="btn btn-primary">Simpan</button>
            <a href="{{ route('stock-movements.index') }}" class="btn btn-outline-secondary">Batal</a>
        </form>
    </div>
</div>
@endsection
