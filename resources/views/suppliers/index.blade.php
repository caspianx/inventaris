@extends('layouts.app')
@section('title', 'Supplier')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center" style="flex-wrap: wrap; gap: 1rem;">
            <div style="flex-grow: 1; min-width: 250px;">
                <form class="d-flex gap-2" method="GET" id="supplier-search-form">
                    <input type="text" name="search" id="supplier-search-input" class="form-control" placeholder="🔍 Cari supplier..." value="{{ request('search') }}" autocomplete="off">
                    <button class="btn btn-outline-secondary">
                        <i class="bi bi-search"></i>
                    </button>
                </form>
            </div>
            <a href="{{ route('suppliers.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Tambah Supplier
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
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
                        <td colspan="5" class="text-center py-5">
                            <i class="bi bi-inbox" style="font-size: 2.5rem; color: var(--gray-300); display: block; margin-bottom: 1rem;"></i>
                            <div style="color: var(--gray-500); font-size: 1.1rem;">Belum ada supplier</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-footer bg-white">
        {{ $suppliers->links() }}
    </div>
</div>

<script>
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
@endsection
