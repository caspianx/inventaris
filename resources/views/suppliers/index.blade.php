@extends('layouts.app')
@section('title', 'Supplier')

@section('content')
<div class="card">
    <div class="card-header">
        <form class="d-flex flex-column gap-3" method="GET" id="supplier-search-form">
            <div class="d-flex gap-2 align-items-center" style="flex-wrap: wrap;">
                <div class="position-relative" style="flex-grow: 1; min-width: 250px;">
                    <input type="text" name="search" id="supplier-search-input" class="form-control" placeholder="🔍 Cari supplier..." value="{{ request('search') }}" autocomplete="off">
                    <div id="supplier-search-suggestions" class="list-group position-absolute w-100 shadow-sm d-none" style="z-index: 1000; top: 100%;"></div>
                </div>
                <div class="text-muted small fw-semibold" style="white-space: nowrap;">
                    Total {{ $suppliers->total() }} supplier
                </div>
                @if(auth()->user()->canAccess('suppliers.manage'))
                    <button type="submit" form="bulk-delete-form" id="bulk-delete-btn" class="btn btn-outline-danger" disabled style="white-space: nowrap;">
                        <i class="bi bi-trash"></i> Hapus Terpilih
                    </button>
                @endif
                <a href="{{ route('suppliers.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Tambah Supplier
                </a>
            </div>
        </form>
    </div>

    @if(auth()->user()->canAccess('suppliers.manage'))
        <form id="bulk-delete-form" method="POST" action="{{ route('suppliers.bulk-delete') }}">
            @csrf
        </form>
    @endif

    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    @if(auth()->user()->canAccess('suppliers.manage'))
                        <th style="width: 80px;">
                            <div class="d-flex align-items-center gap-2">
                                <input type="checkbox" id="select-all-suppliers" aria-label="Pilih semua supplier">
                                <label for="select-all-suppliers" class="form-label mb-0 small text-muted" style="cursor: pointer;">Pilih Semua</label>
                            </div>
                        </th>
                    @endif
                    <th>Nama Supplier</th>
                    <th>Kontak Pesan</th>
                    <th>Telepon</th>
                    <th>Email</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($suppliers as $s)
                    <tr>
                        @if(auth()->user()->canAccess('suppliers.manage'))
                            <td>
                                <input type="checkbox" class="supplier-checkbox" name="selected_items[]" value="{{ $s->id }}" form="bulk-delete-form">
                            </td>
                        @endif
                        <td>
                            <strong style="color: var(--primary);">{{ $s->name }}</strong>
                        </td>
                        <td>
                            <div style="font-weight: 600; color: var(--gray-700);">{{ $s->contact_person }}</div>
                        </td>
                        <td>
                            <a href="tel:{{ $s->phone }}" style="text-decoration: none; color: var(--primary);">
                                <i class="bi bi-telephone"></i> {{ $s->phone }}
                            </a>
                        </td>
                        <td>
                            <a href="mailto:{{ $s->email }}" style="text-decoration: none; color: var(--primary);">
                                <i class="bi bi-envelope"></i> {{ $s->email }}
                            </a>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('suppliers.edit', $s) }}" class="btn btn-outline-secondary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('suppliers.destroy', $s) }}" method="POST" class="d-inline" data-confirm="Yakin hapus supplier ini?">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-outline-danger btn-sm" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ auth()->user()->canAccess('suppliers.manage') ? 6 : 5 }}" class="text-center py-5">
                            <i class="bi bi-inbox" style="font-size: 2.5rem; color: var(--gray-300); display: block; margin-bottom: 1rem;"></i>
                            <div style="color: var(--gray-500); font-size: 1.1rem;">Belum ada supplier</div>
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
                    <h5 class="modal-title" id="confirmBulkDeleteModalLabel">Konfirmasi Hapus Supplier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Anda akan menghapus <strong id="bulk-delete-count">0</strong> supplier terpilih.</p>
                    <p class="text-muted">Supplier yang memiliki riwayat PO akan dilewati.</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="confirmBulkDeleteButton">Hapus Supplier</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card-footer bg-white">
        {{ $suppliers->links() }}
    </div>
</div>

@if(auth()->user()->canAccess('suppliers.manage'))
<script>
const selectAllCheckbox = document.getElementById('select-all-suppliers');
const supplierCheckboxes = document.querySelectorAll('.supplier-checkbox');
const bulkDeleteBtn = document.getElementById('bulk-delete-btn');
const bulkDeleteForm = document.getElementById('bulk-delete-form');

function updateBulkDeleteButtonState() {
    const checkedBoxes = Array.from(supplierCheckboxes).filter(checkbox => checkbox.checked);
    const hasSelection = checkedBoxes.length > 0;

    if (bulkDeleteBtn) {
        bulkDeleteBtn.disabled = !hasSelection;
    }

    if (selectAllCheckbox) {
        selectAllCheckbox.checked = checkedBoxes.length > 0 && checkedBoxes.length === supplierCheckboxes.length;
        selectAllCheckbox.indeterminate = checkedBoxes.length > 0 && checkedBoxes.length < supplierCheckboxes.length;
    }
}

supplierCheckboxes.forEach(checkbox => {
    checkbox.addEventListener('change', updateBulkDeleteButtonState);
});

if (selectAllCheckbox) {
    selectAllCheckbox.addEventListener('change', function () {
        supplierCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkDeleteButtonState();
    });
}

const bulkDeleteModalElement = document.getElementById('confirmBulkDeleteModal');
const bulkDeleteModal = bulkDeleteModalElement ? new bootstrap.Modal(bulkDeleteModalElement) : null;
const bulkDeleteCount = document.getElementById('bulk-delete-count');
const confirmBulkDeleteButton = document.getElementById('confirmBulkDeleteButton');

if (bulkDeleteBtn) {
    bulkDeleteBtn.addEventListener('click', function (event) {
        event.preventDefault();
        const selected = Array.from(supplierCheckboxes).filter(checkbox => checkbox.checked);

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

if (confirmBulkDeleteButton) {
    confirmBulkDeleteButton.addEventListener('click', function () {
        if (bulkDeleteForm) {
            bulkDeleteForm.submit();
        }
    });
}

const supplierSearchForm = document.getElementById('supplier-search-form');
const supplierSearchInput = document.getElementById('supplier-search-input');
const supplierSearchSuggestions = document.getElementById('supplier-search-suggestions');
let supplierSearchTimer;

function hideSupplierSuggestions() {
    supplierSearchSuggestions.classList.add('d-none');
    supplierSearchSuggestions.innerHTML = '';
}

supplierSearchInput.addEventListener('input', function () {
    clearTimeout(supplierSearchTimer);
    const search = this.value.trim();

    if (search.length < 2) {
        hideSupplierSuggestions();
        return;
    }

    supplierSearchTimer = setTimeout(() => {
        fetch(`{{ route('suppliers.autocomplete') }}?search=${encodeURIComponent(search)}`)
            .then(res => res.json())
            .then(items => {
                supplierSearchSuggestions.innerHTML = '';

                if (!items.length) {
                    hideSupplierSuggestions();
                    return;
                }

                items.forEach(item => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'list-group-item list-group-item-action';
                    button.innerHTML = `<div class="fw-semibold">${item.title}</div><small class="text-muted">${item.subtitle}</small>`;
                    button.addEventListener('click', () => {
                        supplierSearchInput.value = item.value;
                        hideSupplierSuggestions();
                        supplierSearchForm.submit();
                    });
                    supplierSearchSuggestions.appendChild(button);
                });

                supplierSearchSuggestions.classList.remove('d-none');
            });
    }, 300);
});

document.addEventListener('click', function (event) {
    if (!supplierSearchForm.contains(event.target)) {
        hideSupplierSuggestions();
    }
});
</script>
@endif
@endsection
