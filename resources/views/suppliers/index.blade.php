@extends('layouts.app')
@section('title', 'Supplier')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <form class="d-flex gap-2" method="GET" id="supplier-search-form">
            <div class="position-relative">
                <input type="text" name="search" id="supplier-search-input" class="form-control" placeholder="Cari supplier..." value="{{ request('search') }}" autocomplete="off">
                <div id="supplier-search-suggestions" class="list-group position-absolute w-100 shadow-sm d-none" style="z-index: 1000; top: 100%;"></div>
            </div>
            <button class="btn btn-outline-secondary"><i class="bi bi-search"></i></button>
        </form>
        <a href="{{ route('suppliers.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Tambah Supplier</a>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead><tr><th>Nama</th><th>Kontak</th><th>Telepon</th><th>Email</th><th></th></tr></thead>
            <tbody>
                @forelse($suppliers as $s)
                    <tr>
                        <td>{{ $s->name }}</td>
                        <td>{{ $s->contact_person }}</td>
                        <td>{{ $s->phone }}</td>
                        <td>{{ $s->email }}</td>
                        <td class="text-end">
                            <a href="{{ route('suppliers.edit', $s) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('suppliers.destroy', $s) }}" method="POST" class="d-inline" data-confirm="Hapus supplier ini?">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Belum ada supplier</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $suppliers->links() }}</div>
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
