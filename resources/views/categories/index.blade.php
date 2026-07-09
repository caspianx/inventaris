@extends('layouts.app')
@section('title', 'Kategori')

@section('content')
<div class="card">
    <div class="card-header">
        <form class="d-flex flex-column gap-3" method="GET" id="category-search-form">
            <div class="d-flex gap-2 align-items-center" style="flex-wrap: wrap;">
                <div class="position-relative" style="flex-grow: 1; min-width: 250px;">
                    <input type="text" name="search" id="category-search-input" class="form-control" placeholder="🔍 Cari kategori..." value="{{ request('search') }}" autocomplete="off">
                    <div id="category-search-suggestions" class="list-group position-absolute w-100 shadow-sm d-none" style="z-index: 1000; top: 100%;"></div>
                </div>
                <div class="text-muted small fw-semibold" style="white-space: nowrap;">
                    Total {{ $categories->total() }} kategori
                </div>
                @if(auth()->user()->canAccess('categories.manage'))
                    <button type="submit" form="bulk-delete-form" id="bulk-delete-btn" class="btn btn-outline-danger" disabled style="white-space: nowrap;">
                        <i class="bi bi-trash"></i> Hapus Terpilih
                    </button>
                @endif
                <a href="{{ route('categories.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Tambah Kategori
                </a>
            </div>
        </form>
    </div>

    @if(auth()->user()->canAccess('categories.manage'))
        <form id="bulk-delete-form" method="POST" action="{{ route('categories.bulk-delete') }}">
            @csrf
        </form>
    @endif

    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    @if(auth()->user()->canAccess('categories.manage'))
                        <th style="width: 80px;">
                            <div class="d-flex align-items-center gap-2">
                                <input type="checkbox" id="select-all-categories" aria-label="Pilih semua kategori">
                                <label for="select-all-categories" class="form-label mb-0 small text-muted" style="cursor: pointer;">Pilih Semua</label>
                            </div>
                        </th>
                    @endif
                    <th>Nama Kategori</th>
                    <th>Deskripsi</th>
                    <th class="text-end">Jumlah Barang</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $cat)
                    <tr>
                        @if(auth()->user()->canAccess('categories.manage'))
                            <td>
                                <input type="checkbox" class="category-checkbox" name="selected_items[]" value="{{ $cat->id }}" form="bulk-delete-form">
                            </td>
                        @endif
                        <td>
                            <strong style="color: var(--primary);">{{ $cat->name }}</strong>
                        </td>
                        <td>
                            <p class="mb-0" style="color: var(--gray-700); max-width: 300px;">{{ $cat->description ?: '-' }}</p>
                        </td>
                        <td class="text-end">
                            <span class="badge" style="background: rgba(99, 102, 241, 0.1); color: var(--primary);">
                                {{ $cat->items_count }} barang
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('categories.edit', $cat) }}" class="btn btn-outline-secondary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('categories.destroy', $cat) }}" method="POST" class="d-inline" data-confirm="Yakin hapus kategori ini?">
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
                        <td colspan="{{ auth()->user()->canAccess('categories.manage') ? 5 : 4 }}" class="text-center py-5">
                            <i class="bi bi-inbox" style="font-size: 2.5rem; color: var(--gray-300); display: block; margin-bottom: 1rem;"></i>
                            <div style="color: var(--gray-500); font-size: 1.1rem;">Belum ada kategori</div>
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
                    <h5 class="modal-title" id="confirmBulkDeleteModalLabel">Konfirmasi Hapus Kategori</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Anda akan menghapus <strong id="bulk-delete-count">0</strong> kategori terpilih.</p>
                    <p class="text-muted">Kategori yang masih memiliki barang akan dilewati.</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="confirmBulkDeleteButton">Hapus Kategori</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card-footer bg-white">
        {{ $categories->links() }}
    </div>
</div>

@if(auth()->user()->canAccess('categories.manage'))
<script>
const selectAllCheckbox = document.getElementById('select-all-categories');
const categoryCheckboxes = document.querySelectorAll('.category-checkbox');
const bulkDeleteBtn = document.getElementById('bulk-delete-btn');
const bulkDeleteForm = document.getElementById('bulk-delete-form');

function updateBulkDeleteButtonState() {
    const checkedBoxes = Array.from(categoryCheckboxes).filter(checkbox => checkbox.checked);
    const hasSelection = checkedBoxes.length > 0;

    if (bulkDeleteBtn) {
        bulkDeleteBtn.disabled = !hasSelection;
    }

    if (selectAllCheckbox) {
        selectAllCheckbox.checked = checkedBoxes.length > 0 && checkedBoxes.length === categoryCheckboxes.length;
        selectAllCheckbox.indeterminate = checkedBoxes.length > 0 && checkedBoxes.length < categoryCheckboxes.length;
    }
}

categoryCheckboxes.forEach(checkbox => {
    checkbox.addEventListener('change', updateBulkDeleteButtonState);
});

if (selectAllCheckbox) {
    selectAllCheckbox.addEventListener('change', function () {
        categoryCheckboxes.forEach(checkbox => {
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
        const selected = Array.from(categoryCheckboxes).filter(checkbox => checkbox.checked);

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

const categorySearchForm = document.getElementById('category-search-form');
const categorySearchInput = document.getElementById('category-search-input');
const categorySearchSuggestions = document.getElementById('category-search-suggestions');
let categorySearchTimer;

function hideCategorySuggestions() {
    categorySearchSuggestions.classList.add('d-none');
    categorySearchSuggestions.innerHTML = '';
}

categorySearchInput.addEventListener('input', function () {
    clearTimeout(categorySearchTimer);
    const search = this.value.trim();

    if (search.length < 2) {
        hideCategorySuggestions();
        return;
    }

    categorySearchTimer = setTimeout(() => {
        fetch(`{{ route('categories.autocomplete') }}?search=${encodeURIComponent(search)}`)
            .then(res => res.json())
            .then(items => {
                categorySearchSuggestions.innerHTML = '';

                if (!items.length) {
                    hideCategorySuggestions();
                    return;
                }

                items.forEach(item => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'list-group-item list-group-item-action';
                    button.innerHTML = `<div class="fw-semibold">${item.title}</div><small class="text-muted">${item.subtitle}</small>`;
                    button.addEventListener('click', () => {
                        categorySearchInput.value = item.value;
                        hideCategorySuggestions();
                        categorySearchForm.submit();
                    });
                    categorySearchSuggestions.appendChild(button);
                });

                categorySearchSuggestions.classList.remove('d-none');
            });
    }, 300);
});

document.addEventListener('click', function (event) {
    if (!categorySearchForm.contains(event.target)) {
        hideCategorySuggestions();
    }
});
</script>
@endif
@endsection
