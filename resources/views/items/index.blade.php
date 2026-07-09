@extends('layouts.app')
@section('title', 'Master Barang')

@section('content')
<div class="card">
    <!-- FILTER & ACTION HEADER -->
    <div class="card-header">
        <form class="d-flex flex-column gap-3" method="GET" id="item-search-form">
            <!-- Row 1: Search, Category, Action Buttons -->
            <div class="d-flex gap-2 align-items-center" style="flex-wrap: wrap;">
                <div class="position-relative" style="flex-grow: 1; min-width: 250px;">
                    <input type="text" name="search" id="item-search-input" class="form-control" placeholder="🔍 Cari nama/SKU..." value="{{ request('search') }}" autocomplete="off">
                    <div id="item-search-suggestions" class="list-group position-absolute w-100 shadow-sm d-none" style="z-index: 1000; top: 100%;"></div>
                </div>
                <select name="category_id" class="form-select" style="max-width: 200px;">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
                @if(auth()->user()->canAccess('items.create'))
                    <a href="{{ route('items.create') }}" class="btn btn-primary" style="white-space: nowrap;">
                        <i class="bi bi-plus-lg"></i> Tambah Barang
                    </a>
                @endif
            </div>

            <!-- Row 2: Filters & Pagination -->
            <div class="d-flex gap-3 align-items-center" style="flex-wrap: wrap;">
                <div class="form-check mb-0">
                    <input type="checkbox" name="low_stock" value="1" class="form-check-input" id="lowStock" {{ request('low_stock') ? 'checked' : '' }} onchange="this.form.submit()">
                    <label class="form-check-label" for="lowStock" style="cursor: pointer; user-select: none;">
                        <i class="bi bi-exclamation-circle"></i> Stok Menipis Saja
                    </label>
                </div>
                <select name="per_page" class="form-select" style="max-width: 150px; width: auto;" onchange="this.form.submit()">
                    @foreach([10, 25, 50, 100] as $option)
                        <option value="{{ $option }}" {{ (int) request('per_page', 10) === $option ? 'selected' : '' }}>{{ $option }} / halaman</option>
                    @endforeach
                </select>
                @if(auth()->user()->canAccess('items.print_barcode'))
                    <button type="submit" form="print-barcode-form" id="print-selected-btn" class="btn btn-outline-primary" disabled style="margin-left: auto; white-space: nowrap;">
                        <i class="bi bi-printer"></i> Cetak Barcode
                        <span id="selected-count-label" style="margin-left: 0.5rem; font-weight: 600;"></span>
                    </button>
                @endif
                @if(auth()->user()->canAccess('items.delete'))
                    <button type="submit" form="bulk-delete-form" id="bulk-delete-btn" class="btn btn-outline-danger" disabled style="white-space: nowrap;">
                        <i class="bi bi-trash"></i> Hapus Terpilih
                    </button>
                @endif
            </div>
        </form>
    </div>

    <form id="print-barcode-form" method="GET" action="{{ route('items.print-barcode') }}" target="_blank"></form>
    @if(auth()->user()->canAccess('items.delete'))
        <form id="bulk-delete-form" method="POST" action="{{ route('items.bulk-delete') }}">
            @csrf
        </form>
    @endif

    <!-- TABLE -->
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    @if(auth()->user()->canAccess('items.print_barcode') || auth()->user()->canAccess('items.delete'))
                        <th style="width: 80px;">
                            <div class="d-flex align-items-center gap-2">
                                <input type="checkbox" id="select-all-items" aria-label="Pilih semua barang">
                                <label for="select-all-items" class="form-label mb-0 small text-muted" style="cursor: pointer;">Pilih Semua</label>
                            </div>
                        </th>
                    @endif
                    <th>SKU</th>
                    <th>Nama Barang</th>
                    <th>Kategori</th>
                    <th>Satuan</th>
                    <th class="text-end">Harga Beli</th>
                    <th class="text-end">Harga Jual</th>
                    <th class="text-end">Stok</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                    <tr class="{{ $item->isLowStock() ? 'table-danger' : '' }}" style="opacity: {{ $item->isLowStock() ? '1' : '1' }};">
                        @if(auth()->user()->canAccess('items.print_barcode') || auth()->user()->canAccess('items.delete'))
                            <td>
                                <input type="checkbox" class="item-checkbox item-checkbox-visible" name="ids[]" value="{{ $item->id }}" form="print-barcode-form">
                                <input type="checkbox" class="item-checkbox item-checkbox-hidden" name="selected_items[]" value="{{ $item->id }}" form="bulk-delete-form" style="display: none;">
                            </td>
                        @endif
                        <td>
                            <code style="background: var(--gray-100); padding: 0.25rem 0.5rem; border-radius: 4px;">{{ $item->sku }}</code>
                        </td>
                        <td>
                            <strong>{{ $item->name }}</strong>
                            @if($item->barcode_path)
                                <br><small style="color: var(--gray-500); display: flex; align-items: center; gap: 0.25rem; margin-top: 0.25rem;"><i class="bi bi-barcode"></i> Barcode tersedia</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge" style="background: var(--primary-light); color: #fff;">{{ $item->category->name ?? '-' }}</span>
                        </td>
                        <td>{{ $item->unit }}</td>
                        <td class="text-end">
                            <div style="font-size: 0.9rem; color: var(--gray-500);">Rp {{ number_format($item->purchase_price, 0, ',', '.') }}</div>
                        </td>
                        <td class="text-end">
                            <strong style="color: var(--primary);">Rp {{ number_format($item->selling_price, 0, ',', '.') }}</strong>
                        </td>
                        <td class="text-end">
                            <strong style="font-size: 1.1rem; color: {{ $item->isLowStock() ? '#ef4444' : '#10b981' }};">{{ $item->current_stock }}</strong>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm" role="group">
                                @if(auth()->user()->canAccess('items.edit'))
                                    <a href="{{ route('items.edit', $item) }}" class="btn btn-outline-secondary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                @endif
                                @if(auth()->user()->canAccess('items.print_barcode'))
                                    <a href="{{ route('items.print-barcode', ['ids' => [$item->id]]) }}" target="_blank" class="btn btn-outline-primary" title="Cetak Barcode">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                @endif
                                @if(auth()->user()->canAccess('items.delete'))
                                    <form action="{{ route('items.destroy', $item) }}" method="POST" class="d-inline" data-confirm="Yakin hapus barang ini?">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-outline-danger btn-sm" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ auth()->user()->canAccess('items.print_barcode') ? 9 : 8 }}" class="text-center py-5">
                            <i class="bi bi-inbox" style="font-size: 2.5rem; color: var(--gray-300); display: block; margin-bottom: 1rem;"></i>
                            <div style="color: var(--gray-500); font-size: 1.1rem;">Belum ada data barang</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="modal fade" id="confirmBulkDeleteModal" tabindex="-1" aria-labelledby="confirmBulkDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-sm">
                <div class="modal-header border-0">
                    <h5 class="modal-title" id="confirmBulkDeleteModalLabel">Konfirmasi Hapus Barang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Anda akan menghapus <strong id="bulk-delete-count">0</strong> barang terpilih.</p>
                    <p class="text-muted">Barang yang sudah memiliki riwayat stok, PO, atau penjualan akan dilewati.</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="confirmBulkDeleteButton">Hapus Barang</button>
                </div>
            </div>
        </div>
    </div>

    <!-- PAGINATION -->
    <div class="card-footer bg-white">
        <div class="d-flex justify-content-between align-items-center" style="flex-wrap: wrap; gap: 1rem;">
            <span class="text-muted small">
                @if($items->total() > 0)
                    Menampilkan <strong>{{ $items->firstItem() }}-{{ $items->lastItem() }}</strong> dari <strong>{{ $items->total() }}</strong> barang
                @else
                    Tidak ada data barang
                @endif
            </span>
            <div>
                {{ $items->links() }}
            </div>
        </div>
    </div>
</div>

@if(auth()->user()->canAccess('items.print_barcode') || auth()->user()->canAccess('items.delete'))
<script>
const selectAllCheckbox = document.getElementById('select-all-items');
const itemCheckboxes = document.querySelectorAll('.item-checkbox-visible');
const printSelectedBtn = document.getElementById('print-selected-btn');
const selectedCountLabel = document.getElementById('selected-count-label');
const bulkDeleteBtn = document.getElementById('bulk-delete-btn');
const bulkDeleteForm = document.getElementById('bulk-delete-form');

function updatePrintButtonState() {
    const checkedBoxes = Array.from(itemCheckboxes).filter(checkbox => checkbox.checked);
    const hasSelection = checkedBoxes.length > 0;

    if (printSelectedBtn) {
        printSelectedBtn.disabled = !hasSelection;
    }

    if (bulkDeleteBtn) {
        bulkDeleteBtn.disabled = !hasSelection;
    }

    if (selectedCountLabel) {
        selectedCountLabel.textContent = hasSelection ? `${checkedBoxes.length} barang dipilih` : '';
    }

    if (selectAllCheckbox) {
        selectAllCheckbox.checked = checkedBoxes.length > 0 && checkedBoxes.length === itemCheckboxes.length;
        selectAllCheckbox.indeterminate = checkedBoxes.length > 0 && checkedBoxes.length < itemCheckboxes.length;
    }
}

itemCheckboxes.forEach(checkbox => {
    checkbox.addEventListener('change', function () {
        const hiddenMirror = document.querySelector(`input[type="checkbox"][name="selected_items[]"][value="${this.value}"][form="bulk-delete-form"]`);
        if (hiddenMirror) {
            hiddenMirror.checked = this.checked;
        }
        updatePrintButtonState();
    });
});

if (selectAllCheckbox) {
    selectAllCheckbox.addEventListener('change', function () {
        itemCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
            const hiddenMirror = document.querySelector(`input[type="checkbox"][name="selected_items[]"][value="${checkbox.value}"][form="bulk-delete-form"]`);
            if (hiddenMirror) {
                hiddenMirror.checked = this.checked;
            }
        });
        updatePrintButtonState();
    });
}

const bulkDeleteModalElement = document.getElementById('confirmBulkDeleteModal');
const bulkDeleteModal = bulkDeleteModalElement ? new bootstrap.Modal(bulkDeleteModalElement) : null;
const bulkDeleteCount = document.getElementById('bulk-delete-count');
const confirmBulkDeleteButton = document.getElementById('confirmBulkDeleteButton');

if (bulkDeleteBtn) {
    bulkDeleteBtn.addEventListener('click', function (event) {
        event.preventDefault();
        const selected = Array.from(itemCheckboxes).filter(checkbox => checkbox.checked);

        if (selected.length === 0) {
            return;
        }

        if (bulkDeleteCount) {
            bulkDeleteCount.textContent = selected.length;
        }

        if (bulkDeleteModal) {
            bulkDeleteModal.show();
        }
    });
}

if (confirmBulkDeleteButton && bulkDeleteForm) {
    confirmBulkDeleteButton.addEventListener('click', function () {
        bulkDeleteForm.submit();
    });
}

updatePrintButtonState();
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
