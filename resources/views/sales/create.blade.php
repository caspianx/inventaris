@extends('layouts.app')
@section('title', 'Transaksi Penjualan')

@section('content')
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <label class="form-label fw-bold">Scan Barcode / Cari Barang</label>
                <div class="position-relative">
                    <input type="text" id="search-input" class="form-control form-control-lg" placeholder="Scan barcode atau ketik nama/SKU barang..." autocomplete="off" autofocus>
                    <div id="search-results" class="list-group position-absolute w-100 shadow-sm d-none" style="z-index: 1000; max-height: 300px; overflow-y: auto;"></div>
                </div>
                <div class="form-text">Gunakan alat scan barcode (otomatis Enter), atau ketik lalu tekan Enter / klik hasil pencarian.</div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Barang</th>
                            <th style="width:130px">Harga</th>
                            <th style="width:140px">Qty</th>
                            <th style="width:130px" class="text-end">Subtotal</th>
                            <th style="width:50px"></th>
                        </tr>
                    </thead>
                    <tbody id="cart-body">
                        <tr id="empty-cart-row">
                            <td colspan="5" class="text-center text-muted py-4">Keranjang masih kosong. Scan atau cari barang di atas.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm" style="position: sticky; top: 1rem;">
            <div class="card-body">
                <h5 class="mb-3">Pembayaran</h5>

                <form method="POST" action="{{ route('sales.store') }}" id="pos-form">
                    @csrf
                    <input type="hidden" name="print_receipt" value="1">
                    <div id="cart-hidden-inputs"></div>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal</span>
                        <span id="display-subtotal">Rp 0</span>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small text-muted mb-1">Diskon (Rp)</label>
                        <input type="number" name="discount" id="discount-input" class="form-control" value="0" min="0">
                    </div>

                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="fw-bold fs-5">Total</span>
                        <span class="fw-bold fs-5" id="display-total">Rp 0</span>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small text-muted mb-1">Metode Pembayaran</label>
                        <select name="payment_method" id="payment-method" class="form-select" required>
                            <option value="cash">Tunai</option>
                            <option value="qris">QRIS</option>
                            <option value="debit">Kartu Debit</option>
                            <option value="credit">Kartu Kredit</option>
                        </select>
                    </div>

                    <div class="mb-2" id="paid-amount-group">
                        <label class="form-label small text-muted mb-1">Jumlah Dibayar</label>
                        <input type="number" name="paid_amount" id="paid-amount-input" class="form-control" min="0" required>
                    </div>

                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Kembalian</span>
                        <span class="fw-bold" id="display-change">Rp 0</span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted mb-1">Catatan (opsional)</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 btn-lg" id="submit-btn" disabled>
                        <i class="bi bi-printer"></i> Bayar &amp; Cetak Struk
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@if(session('pos_success') || session('pos_error'))
    <div class="modal fade" id="posStatusModal" tabindex="-1" aria-labelledby="posStatusModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title" id="posStatusModalLabel">{{ session('pos_error') ? 'Cetak Struk Gagal' : 'Transaksi Berhasil' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>{{ session('pos_message') }}</p>
                    <p class="mb-0"><strong>ID Transaksi:</strong> {{ session('pos_sale_id') }}</p>
                    @if(session('pos_error'))
                        <div class="alert alert-danger mt-3" role="alert">
                            {{ session('pos_error') }}
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Lanjutkan Transaksi</button>
                </div>
            </div>
        </div>
    </div>
@endif

<script>
let cart = [];
let searchTimer;

const searchInput = document.getElementById('search-input');
const searchResults = document.getElementById('search-results');
const cartBody = document.getElementById('cart-body');
const discountInput = document.getElementById('discount-input');
const paymentMethodSelect = document.getElementById('payment-method');
const paidAmountInput = document.getElementById('paid-amount-input');
const paidAmountGroup = document.getElementById('paid-amount-group');
const submitBtn = document.getElementById('submit-btn');

function formatRp(value) {
    return 'Rp ' + Math.round(value).toLocaleString('id-ID');
}

// ==== Pencarian barang (scan barcode / ketik nama) ====
searchInput.addEventListener('input', function () {
    clearTimeout(searchTimer);
    const q = this.value.trim();

    if (q.length < 1) {
        searchResults.classList.add('d-none');
        return;
    }

    searchTimer = setTimeout(() => {
        fetch(`{{ route('items.pos-search') }}?search=${encodeURIComponent(q)}`)
            .then(res => res.json())
            .then(data => renderSearchResults(data));
    }, 250);
});

searchInput.addEventListener('keydown', function (e) {
    if (e.key !== 'Enter') return;
    e.preventDefault();

    const q = this.value.trim();
    if (!q) return;

    // Alat scan barcode mengetik SKU lalu Enter — coba cocokkan persis dulu.
    fetch(`{{ route('items.pos-search') }}?search=${encodeURIComponent(q)}`)
        .then(res => res.json())
        .then(data => {
            const exact = data.find(item => item.sku.toLowerCase() === q.toLowerCase());

            if (exact) {
                addToCart(exact);
                clearSearch();
            } else if (data.length === 1) {
                addToCart(data[0]);
                clearSearch();
            } else {
                renderSearchResults(data);
            }
        });
});

function clearSearch() {
    searchInput.value = '';
    searchResults.classList.add('d-none');
    searchInput.focus();
}

function renderSearchResults(items) {
    if (items.length === 0) {
        searchResults.innerHTML = '<div class="list-group-item text-muted small">Barang tidak ditemukan</div>';
        searchResults.classList.remove('d-none');
        return;
    }

    searchResults.innerHTML = items.map(item => `
        <button type="button" class="list-group-item list-group-item-action" onclick='addToCartFromJson(${JSON.stringify(JSON.stringify(item))})'>
            <div class="d-flex justify-content-between">
                <span><strong>${item.name}</strong><br><small class="text-muted">${item.sku} &middot; Stok: ${item.current_stock}</small></span>
                <span class="fw-bold">${formatRp(item.selling_price)}</span>
            </div>
        </button>
    `).join('');
    searchResults.classList.remove('d-none');
}

function addToCartFromJson(jsonStr) {
    addToCart(JSON.parse(jsonStr));
    clearSearch();
}

// ==== Kelola keranjang ====
function addToCart(item) {
    if (item.current_stock <= 0) {
        alert(`Stok "${item.name}" habis.`);
        return;
    }

    const existing = cart.find(line => line.id === item.id);

    if (existing) {
        if (existing.qty + 1 > item.current_stock) {
            alert(`Stok "${item.name}" tidak mencukupi. Tersedia: ${item.current_stock}.`);
            return;
        }
        existing.qty += 1;
    } else {
        cart.push({
            id: item.id,
            sku: item.sku,
            name: item.name,
            price: parseFloat(item.selling_price),
            stock: item.current_stock,
            qty: 1,
        });
    }

    renderCart();
}

function changeQty(index, delta) {
    const line = cart[index];
    const newQty = line.qty + delta;

    if (newQty < 1) {
        removeFromCart(index);
        return;
    }

    if (newQty > line.stock) {
        alert(`Stok "${line.name}" tidak mencukupi. Tersedia: ${line.stock}.`);
        return;
    }

    line.qty = newQty;
    renderCart();
}

function removeFromCart(index) {
    cart.splice(index, 1);
    renderCart();
}

function renderCart() {
    if (cart.length === 0) {
        cartBody.innerHTML = '<tr id="empty-cart-row"><td colspan="5" class="text-center text-muted py-4">Keranjang masih kosong. Scan atau cari barang di atas.</td></tr>';
        submitBtn.disabled = true;
    } else {
        cartBody.innerHTML = cart.map((line, i) => `
            <tr>
                <td>${line.name}<br><small class="text-muted">${line.sku}</small></td>
                <td>${formatRp(line.price)}</td>
                <td>
                    <div class="d-flex align-items-center gap-1">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="changeQty(${i}, -1)">-</button>
                        <span class="px-2">${line.qty}</span>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="changeQty(${i}, 1)">+</button>
                    </div>
                </td>
                <td class="text-end">${formatRp(line.price * line.qty)}</td>
                <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFromCart(${i})"><i class="bi bi-trash"></i></button></td>
            </tr>
        `).join('');
        submitBtn.disabled = false;
    }

    calculateTotals();
}

// ==== Kalkulasi total & kembalian ====
function calculateTotals() {
    const subtotal = cart.reduce((sum, line) => sum + (line.price * line.qty), 0);
    const discount = Math.min(parseFloat(discountInput.value) || 0, subtotal);
    const total = subtotal - discount;

    document.getElementById('display-subtotal').textContent = formatRp(subtotal);
    document.getElementById('display-total').textContent = formatRp(total);

    if (paymentMethodSelect.value !== 'cash') {
        paidAmountInput.value = total.toFixed(0);
    }

    calculateChange(total);
}

function calculateChange(total) {
    const paid = parseFloat(paidAmountInput.value) || 0;
    const change = paid - total;
    document.getElementById('display-change').textContent = formatRp(Math.max(change, 0));
}

discountInput.addEventListener('input', calculateTotals);
paidAmountInput.addEventListener('input', function () {
    const subtotal = cart.reduce((sum, line) => sum + (line.price * line.qty), 0);
    const discount = Math.min(parseFloat(discountInput.value) || 0, subtotal);
    calculateChange(subtotal - discount);
});

paymentMethodSelect.addEventListener('change', function () {
    const isCash = this.value === 'cash';
    paidAmountInput.readOnly = !isCash;
    paidAmountGroup.querySelector('label').textContent = isCash ? 'Jumlah Dibayar' : 'Jumlah Dibayar (otomatis = total)';
    calculateTotals();
});

// Tutup dropdown hasil pencarian kalau klik di luar
document.addEventListener('click', function (e) {
    if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
        searchResults.classList.add('d-none');
    }
});

// ==== Submit: bangun hidden input dari cart sebelum form terkirim ====
document.getElementById('pos-form').addEventListener('submit', function (e) {
    if (cart.length === 0) {
        e.preventDefault();
        return;
    }

    const container = document.getElementById('cart-hidden-inputs');
    container.innerHTML = '';
    cart.forEach((line, i) => {
        container.insertAdjacentHTML('beforeend', `
            <input type="hidden" name="items[${i}][item_id]" value="${line.id}">
            <input type="hidden" name="items[${i}][quantity]" value="${line.qty}">
        `);
    });
});

calculateTotals();
</script>

@if(session('pos_success') || session('pos_error'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const statusModalEl = document.getElementById('posStatusModal');
            if (statusModalEl && window.bootstrap && typeof window.bootstrap.Modal === 'function') {
                const statusModal = new bootstrap.Modal(statusModalEl);
                statusModal.show();
            }
        });
    </script>
@endif
@endsection
