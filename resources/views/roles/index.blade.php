@extends('layouts.app')
@section('title', 'Manajemen Role')

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        <a href="{{ route('roles.create') }}" class="btn btn-primary mb-3">Tambah Role</a>
        <table class="table">
            <thead><tr><th>Nama</th><th>Label</th><th>Pengguna</th><th>Akses</th><th></th></tr></thead>
            <tbody>
                @foreach($roles as $role)
                    <tr>
                        <td>{{ $role->name }}</td>
                        <td>{{ $role->label }}</td>
                        <td>{{ $role->users_count ?? 0 }}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-info" data-permissions='@json($role->permissions->pluck("permission"))' onclick="openPermissionsModal(this)">Lihat Akses</button>
                        </td>
                        <td>
                            <a href="{{ route('roles.edit', $role) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                            @if(($role->users_count ?? 0) > 0)
                                <button class="btn btn-sm btn-outline-secondary" disabled title="Role ini masih digunakan oleh user; pindahkan atau hapus user terlebih dahulu">Hapus</button>
                            @else
                                <button class="btn btn-sm btn-outline-danger" onclick="openDeleteModal('{{ $role->id }}', '{{ $role->label ?? $role->name }}')">Hapus</button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Permissions Modal -->
        <div id="permissionsModal" class="modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); align-items:center; justify-content:center;">
            <div style="background:#fff; padding:1rem; border-radius:6px; width:520px; max-width:95%;">
                <h5 id="permissionsModalTitle">Akses Role</h5>
                <ul id="permissionsList" style="max-height:300px; overflow:auto; padding-left:1rem;"></ul>
                <div style="text-align:right; margin-top:0.5rem;"><button class="btn btn-sm btn-outline-secondary" onclick="closePermissionsModal()">Tutup</button></div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div id="deleteModal" class="modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); align-items:center; justify-content:center;">
            <div style="background:#fff; padding:1rem; border-radius:6px; width:420px; max-width:95%;">
                <h5 id="deleteModalTitle">Konfirmasi Hapus Role</h5>
                <p id="deleteModalBody">Anda yakin ingin menghapus role ini? Tindakan ini akan menghapus pengaturan akses role terkait.</p>
                <form id="deleteRoleForm" method="POST" style="display:inline">@csrf @method('DELETE')
                    <button class="btn btn-danger">Ya, Hapus</button>
                    <button type="button" class="btn btn-outline-secondary" onclick="closeDeleteModal()">Batal</button>
                </form>
            </div>
        </div>

        <script>
            function openPermissionsModal(btn) {
                const perms = JSON.parse(btn.getAttribute('data-permissions') || '[]');
                const title = btn.closest('tr').querySelector('td:nth-child(2)').innerText || 'Role';
                document.getElementById('permissionsModalTitle').innerText = 'Akses: ' + title;
                const list = document.getElementById('permissionsList');
                list.innerHTML = '';
                if (perms.length === 0) {
                    list.innerHTML = '<li><em>Tidak ada permission terdaftar.</em></li>';
                } else {
                    perms.forEach(p => {
                        const li = document.createElement('li'); li.textContent = p; list.appendChild(li);
                    });
                }
                const m = document.getElementById('permissionsModal'); m.style.display = 'flex';
            }
            function closePermissionsModal() { document.getElementById('permissionsModal').style.display = 'none'; }

            function openDeleteModal(roleId, roleLabel) {
                document.getElementById('deleteModalTitle').innerText = 'Hapus Role: ' + roleLabel;
                document.getElementById('deleteModalBody').innerText = 'Anda yakin ingin menghapus role "' + roleLabel + '"? Aksi ini akan menghapus permission terkait.';
                const form = document.getElementById('deleteRoleForm');
                form.action = '/roles/' + roleId;
                document.getElementById('deleteModal').style.display = 'flex';
            }
            function closeDeleteModal() { document.getElementById('deleteModal').style.display = 'none'; }
        </script>

        {{ $roles->links() }}
    </div>
</div>
@endsection
