@extends('layouts.app')
@section('title', 'Manajemen User')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center" style="flex-wrap: wrap; gap: 1rem;">
            <div style="flex-grow: 1; min-width: 250px;">
                <form class="d-flex gap-2" method="GET" id="user-search-form">
                    <input type="text" name="search" id="user-search-input" class="form-control" placeholder="🔍 Cari nama/email..." value="{{ request('search') }}" autocomplete="off">
                    <button class="btn btn-outline-secondary">
                        <i class="bi bi-search"></i>
                    </button>
                </form>
            </div>
            <a href="{{ route('users.create') }}" class="btn btn-primary">
                <i class="bi bi-person-plus"></i> Tambah User
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $u)
                    <tr>
                        <td>
                            <strong style="color: var(--primary);">{{ $u->name }}</strong>
                        </td>
                        <td>
                            <a href="mailto:{{ $u->email }}" style="text-decoration: none; color: var(--primary);">{{ $u->email }}</a>
                        </td>
                        <td>
                            <span class="badge" style="background: rgba(139, 92, 246, 0.1); color: var(--secondary);">{{ $u->role }}</span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('users.edit', $u) }}" class="btn btn-outline-secondary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @if($u->id !== auth()->id())
                                    <form action="{{ route('users.destroy', $u) }}" method="POST" class="d-inline" data-confirm="Yakin hapus user ini?">
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
                        <td colspan="4" class="text-center py-5">
                            <i class="bi bi-inbox" style="font-size: 2.5rem; color: var(--gray-300); display: block; margin-bottom: 1rem;"></i>
                            <div style="color: var(--gray-500); font-size: 1.1rem;">Belum ada user</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-footer bg-white">
        {{ $users->links() }}
    </div>
</div>

<script>
const userSearchForm = document.getElementById('user-search-form');
const userSearchInput = document.getElementById('user-search-input');
const userSearchSuggestions = document.getElementById('user-search-suggestions');
let userSearchTimer;

function hideUserSuggestions() {
    userSearchSuggestions.classList.add('d-none');
    userSearchSuggestions.innerHTML = '';
}

userSearchInput.addEventListener('input', function () {
    clearTimeout(userSearchTimer);
    const search = this.value.trim();

    if (search.length < 2) {
        hideUserSuggestions();
        return;
    }

    userSearchTimer = setTimeout(() => {
        fetch(`{{ route('users.autocomplete') }}?search=${encodeURIComponent(search)}`)
            .then(res => res.json())
            .then(items => {
                userSearchSuggestions.innerHTML = '';

                if (!items.length) {
                    hideUserSuggestions();
                    return;
                }

                items.forEach(item => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'list-group-item list-group-item-action';
                    button.innerHTML = `<div class="fw-semibold">${item.title}</div><small class="text-muted">${item.subtitle}</small>`;
                    button.addEventListener('click', () => {
                        userSearchInput.value = item.value;
                        hideUserSuggestions();
                        userSearchForm.submit();
                    });
                    userSearchSuggestions.appendChild(button);
                });

                userSearchSuggestions.classList.remove('d-none');
            });
    }, 300);
});

document.addEventListener('click', function (event) {
    if (!userSearchForm.contains(event.target)) {
        hideUserSuggestions();
    }
});
</script>
@endsection
