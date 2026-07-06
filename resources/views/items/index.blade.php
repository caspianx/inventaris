@extends('layouts.app')
@section('title', 'Master Barang')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <form class="d-flex gap-2" method="GET" id="item-search-form">
            <div class="position-relative">
                <input type="text" name="search" id="item-search-input" class="form-control" placeholder="Cari nama/SKU..." value="{{ request('search') }}" autocomplete="off">
                <div id="item-search-suggestions" class="list-group position-absolute w-100 shadow-sm d-none" style="z-index: 1000; top: 100%;"></div>
            </div>
            <select name="category_id" class="form-select">
                <option value="">Semua Kategori</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
            <div class="form-check align-self-center ms-2">
                <input type="checkbox" name="low_stock" value="1" class="form-check-input" id="lowStock" {{ request('low_stock') ? 'checked' : '' }} onchange="this.form.submit()">
                <label class="form-check-label" for="lowStock">Stok menipis</label>
            </div>
            <select name="per_page" class="form-select" onchange="this.form.submit()">
                @foreach([5, 10, 50, 100] as $option)
                    <option value="{{ $option }}" {{ (int) request('per_page', 10) === $option ? 'selected' : '' }}>{{ $option }} / halaman</option>
                @endforeach
            </select>
            <button class="btn btn-outline-secondary"><i class="bi bi-search"></i></button>
        </form>
        @if(auth()->user()->canAccess('items.create'))
            <a href="{{ route('items.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Tambah Barang</a>
        @endif
    </div>
    <form id="print-barcode-form" method="GET" action="{{ route('items.print-barcode') }}" target="_blank"></form>
    <div class="card-body p-0">
        <div class="px-3 pt-3">
            @if(auth()->user()->canAccess('items.print_barcode'))
                <button type="submit" form="print-barcode-form" id="print-selected-btn" class="btn btn-outline-primary btn-sm" disabled>
                    <i class="bi bi-printer"></i> Cetak Barcode Terpilih
                </button>
                <span id="selected-count-label" class="text-muted small ms-2"></span>
            @endif
        </div>
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    @if(auth()->user()->canAccess('items.print_barcode'))
                        <th><input type="checkbox" id="select-all-items"></th>
                    @endif
                    <th>SKU</th><th>Barcode</th><th>Nama</th><th>Kategori</th><th>Satuan</th>
                    <th class="text-end">Harga Beli</th><th class="text-end">Harga Jual</th>
                    <th class="text-end">Stok</th><th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                    <tr class="{{ $item->isLowStock() ? 'table-danger' : '' }}">
                        @if(auth()->user()->canAccess('items.print_barcode'))
                            <td>
                                <div class="d-flex align-items-center gap-1">
                                    <input type="checkbox" class="item-checkbox" name="ids[]" value="{{ $item->id }}" form="print-barcode-form">
                                    <input type="number" min="1" max="100" value="1" name="qty[{{ $item->id }}]" form="print-barcode-form"
                                           class="form-control form-control-sm qty-input" style="width: 55px;" title="Jumlah label" disabled>
                                </div>
                            </td>
                        @endif
                        <td>{{ $item->sku }}</td>
                        <td>
                            @if($item->barcode_path)
                                <img src="{{ asset($item->barcode_path) }}" alt="Barcode {{ $item->sku }}" style="width: 130px; height: auto;">
                            @else
                                <span class="text-muted small">Belum ada</span>
                            @endif
                        </td>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->category->name ?? '-' }}</td>
                        <td>{{ $item->unit }}</td>
                        <td class="text-end">Rp {{ number_format($item->purchase_price, 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($item->selling_price, 0, ',', '.') }}</td>
                        <td class="text-end fw-bold">{{ $item->current_stock }}</td>
                        <td class="text-end">
                            @if(auth()->user()->canAccess('items.print_barcode'))
                                <a href="{{ route('items.print-barcode', ['ids' => [$item->id]]) }}" target="_blank" class="btn btn-sm btn-outline-primary" title="Cetak Barcode"><i class="bi bi-printer"></i></a>
                            @endif
                            @if(auth()->user()->canAccess('items.edit'))
                                <a href="{{ route('items.edit', $item) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                            @endif
                            @if(auth()->user()->canAccess('items.delete'))
                                <form action="{{ route('items.destroy', $item) }}" method="POST" class="d-inline" data-confirm="Hapus barang ini?">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="{{ auth()->user()->canAccess('items.print_barcode') ? 10 : 9 }}" class="text-center text-muted py-4">Belum ada data barang</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
        <span class="text-muted small">
            @if($items->total() > 0)
                Menampilkan {{ $items->firstItem() }}-{{ $items->lastItem() }} dari {{ $items->total() }} barang
            @else
                Tidak ada data barang
            @endif
        </span>
        {{ $items->links() }}
    </div>
</div>

@if(auth()->user()->canAccess('items.print_barcode'))
<script>
const selectAllCheckbox = document.getElementById('select-all-items');
const itemCheckboxes = document.querySelectorAll('.item-checkbox');
const printSelectedBtn = document.getElementById('print-selected-btn');
const selectedCountLabel = document.getElementById('selected-count-label');

function updatePrintButtonState() {
    const checked = document.querySelectorAll('.item-checkbox:checked');
    printSelectedBtn.disabled = checked.length === 0;
    selectedCountLabel.textContent = checked.length > 0 ? `${checked.length} barang dipilih` : '';
    selectAllCheckbox.checked = checked.length === itemCheckboxes.length && itemCheckboxes.length > 0;
}

itemCheckboxes.forEach(checkbox => {
    checkbox.addEventListener('change', function () {
        const row = this.closest('tr');
        const qtyInput = row.querySelector('.qty-input');
        qtyInput.disabled = !this.checked;
        updatePrintButtonState();
    });
});

selectAllCheckbox.addEventListener('change', function () {
    itemCheckboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
        checkbox.closest('tr').querySelector('.qty-input').disabled = !this.checked;
    });
    updatePrintButtonState();
});
</script>
@endif

<script>
const itemSearchForm = document.getElementById('item-search-form');
const itemSearchInput = document.getElementById('item-search-input');
const itemSearchSuggestions = document.getElementById('item-search-suggestions');
let itemSearchTimer;

function hideItemSuggestions() {
    itemSearchSuggestions.classList.add('d-none');
    itemSearchSuggestions.innerHTML = '';
}

itemSearchInput.addEventListener('input', function () {
    clearTimeout(itemSearchTimer);
    const search = this.value.trim();

    if (search.length < 2) {
        hideItemSuggestions();
        return;
    }

    itemSearchTimer = setTimeout(() => {
        fetch(`{{ route('items.autocomplete') }}?search=${encodeURIComponent(search)}`)
            .then(res => res.json())
            .then(items => {
                itemSearchSuggestions.innerHTML = '';

                if (!items.length) {
                    hideItemSuggestions();
                    return;
                }

                items.forEach(item => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'list-group-item list-group-item-action';
                    button.innerHTML = `<div class="fw-semibold">${item.name}</div><small class="text-muted">${item.sku} - Stok: ${item.current_stock}</small>`;
                    button.addEventListener('click', () => {
                        itemSearchInput.value = item.name;
                        hideItemSuggestions();
                        itemSearchForm.submit();
                    });
                    itemSearchSuggestions.appendChild(button);
                });

                itemSearchSuggestions.classList.remove('d-none');
            });
    }, 300);
});

document.addEventListener('click', function (event) {
    if (!itemSearchForm.contains(event.target)) {
        hideItemSuggestions();
    }
});
</script>
@endsection
