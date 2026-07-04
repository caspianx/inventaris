@extends('layouts.app')
@section('title', 'Manajemen User')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <form class="d-flex gap-2" method="GET" id="user-search-form">
            <div class="position-relative">
                <input type="text" name="search" id="user-search-input" class="form-control" placeholder="Cari nama/email..." value="{{ request('search') }}" autocomplete="off">
                <div id="user-search-suggestions" class="list-group position-absolute w-100 shadow-sm d-none" style="z-index: 1000; top: 100%;"></div>
            </div>
            <button class="btn btn-outline-secondary"><i class="bi bi-search"></i></button>
        </form>
        <a href="{{ route('users.create') }}" class="btn btn-primary"><i class="bi bi-person-plus"></i> Tambah User</a>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead><tr><th>Nama</th><th>Email</th><th>Role</th><th></th></tr></thead>
            <tbody>
                @forelse($users as $u)
                    <tr>
                        <td>{{ $u->name }}</td>
                        <td>{{ $u->email }}</td>
                        <td><span class="badge bg-secondary text-uppercase">{{ $u->role }}</span></td>
                        <td class="text-end">
                            <a href="{{ route('users.edit', $u) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                            @if($u->id !== auth()->id())
                                <form action="{{ route('users.destroy', $u) }}" method="POST" class="d-inline" data-confirm="Hapus user ini?">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">Belum ada user</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $users->links() }}</div>
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
