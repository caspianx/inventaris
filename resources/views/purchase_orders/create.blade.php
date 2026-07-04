@extends('layouts.app')
@section('title', 'Buat Purchase Order')

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('purchase-orders.store') }}">
            @csrf
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label">Supplier</label>
                    <div class="d-flex gap-2">
                        <select name="supplier_id" id="supplier-select" class="form-select" required>
                            <option value="">- Pilih Supplier -</option>
                            @foreach($suppliers as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-outline-primary flex-shrink-0" data-bs-toggle="modal" data-bs-target="#newSupplierModal" title="Tambah supplier baru">
                            <i class="bi bi-plus-lg"></i>
                        </button>
                    </div>
                    <div class="form-text">Tidak ada supplier yang sesuai? Klik tombol + untuk menambahkan supplier baru.</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tanggal Order</label>
                    <input type="date" name="order_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Estimasi Tiba</label>
                    <input type="date" name="expected_date" class="form-control">
                </div>
            </div>

            <hr>
            <h6>Daftar Barang</h6>
            <table class="table" id="item-table">
                <thead>
                    <tr><th>Barang</th><th style="width:120px">Qty</th><th style="width:160px">Harga Satuan</th><th style="width:160px">Subtotal</th><th></th></tr>
                </thead>
                <tbody id="item-rows"></tbody>
            </table>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addRow()"><i class="bi bi-plus"></i> Tambah Baris</button>

            <div class="text-end mt-3">
                <h5>Total: Rp <span id="grand-total">0</span></h5>
            </div>

            <div class="mb-3">
                <label class="form-label">Catatan</label>
                <textarea name="notes" class="form-control" rows="2"></textarea>
            </div>

            <button class="btn btn-primary">Simpan PO</button>
            <a href="{{ route('purchase-orders.index') }}" class="btn btn-outline-secondary">Batal</a>
        </form>
    </div>
</div>

<template id="row-template">
    <tr>
        <td>
            <select name="items[__i__][item_id]" class="form-select item-select" required>
                <option value="">- Pilih Barang -</option>
                @foreach($items as $it)
                    <option value="{{ $it->id }}" data-price="{{ $it->purchase_price }}">{{ $it->name }}</option>
                @endforeach
            </select>
        </td>
        <td><input type="number" name="items[__i__][quantity]" class="form-control qty-input" min="1" value="1" required></td>
        <td><input type="number" step="0.01" name="items[__i__][price]" class="form-control price-input" required></td>
        <td><span class="subtotal">0</span></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this)"><i class="bi bi-trash"></i></button></td>
    </tr>
</template>

<script>
let rowIndex = 0;

function addRow() {
    const template = document.getElementById('row-template').innerHTML.replaceAll('__i__', rowIndex);
    document.getElementById('item-rows').insertAdjacentHTML('beforeend', template);
    rowIndex++;
    bindRowEvents();
}

function removeRow(btn) {
    btn.closest('tr').remove();
    calculateTotal();
}

function bindRowEvents() {
    document.querySelectorAll('.item-select').forEach(sel => {
        sel.onchange = function () {
            const price = this.selectedOptions[0]?.dataset.price || 0;
            this.closest('tr').querySelector('.price-input').value = price;
            calculateRow(this.closest('tr'));
        };
    });
    document.querySelectorAll('.qty-input, .price-input').forEach(inp => {
        inp.oninput = function () { calculateRow(this.closest('tr')); };
    });
}

function calculateRow(row) {
    const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
    const price = parseFloat(row.querySelector('.price-input').value) || 0;
    row.querySelector('.subtotal').textContent = (qty * price).toLocaleString('id-ID');
    calculateTotal();
}

function calculateTotal() {
    let total = 0;
    document.querySelectorAll('#item-rows tr').forEach(row => {
        const qty = parseFloat(row.querySelector('.qty-input')?.value) || 0;
        const price = parseFloat(row.querySelector('.price-input')?.value) || 0;
        total += qty * price;
    });
    document.getElementById('grand-total').textContent = total.toLocaleString('id-ID');
}

addRow(); // baris pertama otomatis
</script>

<div class="modal fade" id="newSupplierModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="new-supplier-form">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Supplier Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="new-supplier-error" class="alert alert-danger d-none"></div>
                    <div class="mb-3">
                        <label class="form-label">Nama Supplier</label>
                        <input type="text" name="name" id="new-supplier-name" class="form-control" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Kontak</label>
                        <input type="text" name="contact_person" id="new-supplier-contact" class="form-control">
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Telepon</label>
                            <input type="text" name="phone" id="new-supplier-phone" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" id="new-supplier-email" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea name="address" id="new-supplier-address" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="new-supplier-submit">Simpan Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const newSupplierForm = document.getElementById('new-supplier-form');
const newSupplierModal = new bootstrap.Modal(document.getElementById('newSupplierModal'));
const newSupplierError = document.getElementById('new-supplier-error');
const newSupplierSubmit = document.getElementById('new-supplier-submit');
const supplierSelect = document.getElementById('supplier-select');

newSupplierForm.addEventListener('submit', function (e) {
    e.preventDefault();
    newSupplierError.classList.add('d-none');
    newSupplierSubmit.disabled = true;
    newSupplierSubmit.textContent = 'Menyimpan...';

    fetch('{{ route('suppliers.store') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
        },
        body: JSON.stringify({
            name: document.getElementById('new-supplier-name').value,
            contact_person: document.getElementById('new-supplier-contact').value,
            phone: document.getElementById('new-supplier-phone').value,
            email: document.getElementById('new-supplier-email').value,
            address: document.getElementById('new-supplier-address').value,
        }),
    })
    .then(async (res) => {
        const data = await res.json();

        if (!res.ok) {
            const message = data.message || (data.errors ? Object.values(data.errors)[0][0] : 'Gagal menyimpan supplier.');
            throw new Error(message);
        }

        return data;
    })
    .then((supplier) => {
        const option = document.createElement('option');
        option.value = supplier.id;
        option.textContent = supplier.name;
        option.selected = true;
        supplierSelect.appendChild(option);

        newSupplierForm.reset();
        newSupplierModal.hide();
    })
    .catch((err) => {
        newSupplierError.textContent = err.message;
        newSupplierError.classList.remove('d-none');
    })
    .finally(() => {
        newSupplierSubmit.disabled = false;
        newSupplierSubmit.textContent = 'Simpan Supplier';
    });
});
</script>
@endsection
