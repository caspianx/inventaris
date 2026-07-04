@extends('layouts.app')
@section('title', 'Kategori')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <form class="d-flex gap-2" method="GET" id="category-search-form">
            <div class="position-relative">
                <input type="text" name="search" id="category-search-input" class="form-control" placeholder="Cari kategori..." value="{{ request('search') }}" autocomplete="off">
                <div id="category-search-suggestions" class="list-group position-absolute w-100 shadow-sm d-none" style="z-index: 1000; top: 100%;"></div>
            </div>
            <button class="btn btn-outline-secondary"><i class="bi bi-search"></i></button>
        </form>
        <a href="{{ route('categories.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Tambah Kategori</a>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead><tr><th>Nama</th><th>Deskripsi</th><th class="text-end">Jumlah Barang</th><th></th></tr></thead>
            <tbody>
                @forelse($categories as $cat)
                    <tr>
                        <td>{{ $cat->name }}</td>
                        <td>{{ $cat->description }}</td>
                        <td class="text-end">{{ $cat->items_count }}</td>
                        <td class="text-end">
                            <a href="{{ route('categories.edit', $cat) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('categories.destroy', $cat) }}" method="POST" class="d-inline" data-confirm="Hapus kategori ini?">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">Belum ada kategori</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $categories->links() }}</div>
</div>

<script>
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
@endsection
