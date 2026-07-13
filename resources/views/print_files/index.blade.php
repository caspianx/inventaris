@extends('layouts.app')
@section('title', 'Cetak Struk')

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="mb-0">Cetak Struk</h5>
                <p class="text-muted mb-0">Lihat file struk yang dihasilkan, unduh, cetak ulang, atau hapus.</p>
            </div>
            <a href="{{ route('print-files.index') }}" class="btn btn-outline-secondary">Refresh</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <form action="{{ route('print-files.index') }}" method="GET" class="row g-3 mb-4">
            <div class="col-md-4">
                <label class="form-label">Sale ID</label>
                <input type="text" name="sale_id" value="{{ request('sale_id') }}" class="form-control" placeholder="Cari sale ID">
            </div>
            <div class="col-md-4">
                <label class="form-label">Printer Name</label>
                <input type="text" name="printer_name" value="{{ request('printer_name') }}" class="form-control" placeholder="Cari printer name">
            </div>
            <div class="col-md-4 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="{{ route('print-files.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>

        <form action="{{ route('print-files.bulk-delete') }}" method="POST" id="bulk-delete-form">
            @csrf
            <div class="d-flex justify-content-between align-items-center mb-3 gap-2">
                <div class="form-check mb-0">
                    <input class="form-check-input" type="checkbox" id="select-all">
                    <label class="form-check-label" for="select-all">Pilih Semua</label>
                </div>
                <button type="submit" class="btn btn-danger btn-sm" id="bulk-delete-btn" disabled>Hapus Terpilih</button>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 1rem"></th>
                            <th>Nama File</th>
                            <th>Sale ID</th>
                            <th>Printer</th>
                            <th>Print Count</th>
                            <th>Terakhir Dicetak</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($files as $file)
                        <tr>
                            <td>
                                <input class="form-check-input file-checkbox" type="checkbox" name="selected_files[]" value="{{ $file->id }}">
                            </td>
                            <td>{{ $file->filename }}</td>
                            <td>{{ $file->sale?->id ?? '-' }}</td>
                            <td>{{ $file->printer_name ?? '-' }}</td>
                            <td>{{ $file->print_count }}</td>
                            <td>{{ $file->last_printed_at?->format('Y-m-d H:i:s') ?? '-' }}</td>
                            <td class="text-end">
                                <a href="{{ route('print-files.download', $file->filename) }}" class="btn btn-sm btn-outline-primary">Unduh</a>
                                @if($file->sale)
                                    <a href="{{ route('print-files.reprint', $file->sale->id) }}" target="_blank" rel="noopener" class="btn btn-sm btn-secondary">Preview Struk</a>
                                @endif
                                <button type="button" class="btn btn-sm btn-danger" onclick="confirmSingleDelete('{{ route('print-files.destroy', $file) }}')">Hapus</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">Belum ada file struk.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        </form>

        <form action="" method="POST" id="single-delete-form" style="display:none;">
            @csrf
            @method('DELETE')
        </form>

        <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-sm">
                    <div class="modal-header border-0 align-items-start">
                        <div class="d-flex align-items-center gap-3">
                            <span class="bg-danger text-white rounded-circle p-3 d-inline-flex align-items-center justify-content-center">
                                <i class="bi bi-trash-fill fs-4"></i>
                            </span>
                            <div>
                                <h5 class="modal-title" id="confirmDeleteModalLabel">Hapus File Struk</h5>
                                <p class="mb-0 text-muted small">File struk akan dihapus secara permanen dari penyimpanan.</p>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-0" id="confirmDeleteModalMessage">Apakah Anda yakin ingin menghapus file struk ini?</p>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-danger" id="confirmDeleteButton">Hapus</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const selectAll = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('.file-checkbox');
    const bulkDeleteBtn = document.getElementById('bulk-delete-btn');

    function updateBulkDeleteState() {
        const anyChecked = Array.from(checkboxes).some(cb => cb.checked);
        bulkDeleteBtn.disabled = !anyChecked;
    }

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateBulkDeleteState();
        });
    }

    checkboxes.forEach(cb => cb.addEventListener('change', function () {
        if (!this.checked) {
            selectAll.checked = false;
        }
        updateBulkDeleteState();
    }));

    const bulkDeleteForm = document.getElementById('bulk-delete-form');
    if (bulkDeleteForm) {
        bulkDeleteForm.addEventListener('submit', function (e) {
            if (!Array.from(checkboxes).some(cb => cb.checked)) {
                e.preventDefault();
                alert('Pilih minimal satu file untuk dihapus.');
            } else if (!confirm('Hapus file struk yang dipilih?')) {
                e.preventDefault();
            }
        });
    }

    const confirmDeleteModal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
    const confirmDeleteButton = document.getElementById('confirmDeleteButton');
    let pendingDeleteUrl = null;

    function confirmSingleDelete(actionUrl) {
        pendingDeleteUrl = actionUrl;
        const message = document.getElementById('confirmDeleteModalMessage');
        message.textContent = 'Apakah Anda yakin ingin menghapus file struk ini? Tindakan ini tidak dapat dibatalkan.';
        confirmDeleteModal.show();
    }

    if (confirmDeleteButton) {
        confirmDeleteButton.addEventListener('click', function () {
            if (!pendingDeleteUrl) {
                return;
            }

            const singleDeleteForm = document.getElementById('single-delete-form');
            if (!singleDeleteForm) {
                return;
            }

            singleDeleteForm.action = pendingDeleteUrl;
            singleDeleteForm.submit();
        });
    }
</script>
@endsection
