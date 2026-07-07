@extends('layouts.app')
@section('title', 'Kategori')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center" style="flex-wrap: wrap; gap: 1rem;">
            <div style="flex-grow: 1; min-width: 250px;">
                <form class="d-flex gap-2" method="GET" id="category-search-form">
                    <input type="text" name="search" id="category-search-input" class="form-control" placeholder="🔍 Cari kategori..." value="{{ request('search') }}" autocomplete="off">
                    <button class="btn btn-outline-secondary">
                        <i class="bi bi-search"></i>
                    </button>
                </form>
            </div>
            <a href="{{ route('categories.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Tambah Kategori
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Nama Kategori</th>
                    <th>Deskripsi</th>
                    <th class="text-end">Jumlah Barang</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $cat)
                    <tr>
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
                        <td colspan="4" class="text-center py-5">
                            <i class="bi bi-inbox" style="font-size: 2.5rem; color: var(--gray-300); display: block; margin-bottom: 1rem;"></i>
                            <div style="color: var(--gray-500); font-size: 1.1rem;">Belum ada kategori</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-footer bg-white">
        {{ $categories->links() }}
    </div>
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
