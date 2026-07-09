@extends('layouts.app')
@section('title', 'Transaksi Penjualan')

@section('content')
<style>
    .pos-search-container {
        position: relative;
        width: 100%;
    }
    .pos-search-input {
        font-size: 1.1rem;
        padding: 0.75rem 1rem;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    .pos-search-input:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
    }
    .search-results {
        border-radius: 8px;
        background: white;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
        display: flex;
        flex-direction: column;
        border: 1px solid #e9ecef;
    }
    .search-results:not(.d-none) {
        display: flex !important;
        flex-direction: column;
        max-height: 65vh;
        overflow-y: auto;
        margin-top: 8px;
        min-width: 100%;
    }
    .search-results::-webkit-scrollbar {
        width: 8px;
    }
    .search-results::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    .search-results::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }
    .search-results::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
    .search-result-item {
        padding: 1.25rem;
        border-bottom: 1px solid #f0f0f0;
        transition: background-color 0.2s ease, transform 0.1s ease;
        cursor: pointer;
        background: white;
        border: none;
        text-align: left;
        width: 100%;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1.5rem;
        flex-wrap: nowrap;
        min-height: 70px;
    }
    .search-result-item:hover {
        background-color: #f8f9fa;
        transform: translateX(4px);
    }
    .search-result-item:last-child {
        border-bottom: none;
    }
    .search-result-item:focus {
        outline: 2px solid #0d6efd;
        outline-offset: -2px;
    }
    }
    .search-result-item:last-child {
        border-bottom: none;
    }
    .search-result-item:hover {
        background-color: #f8f9fa;
        transform: translateX(4px);
    }
    .search-result-item:last-child {
        border-bottom: none;
    }
    .cart-table-container {
        border-radius: 8px;
        overflow: hidden;
    }
    .cart-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1.25rem;
        font-weight: 600;
        border-radius: 8px 8px 0 0;
    }
    .empty-cart {
        padding: 3rem 1rem;
        text-align: center;
        background: #f8f9fa;
        border-radius: 8px;
        color: #6c757d;
    }
    .empty-cart i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }
    .cart-item-row {
        border-bottom: 1px solid #f0f0f0;
        padding: 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: background-color 0.2s ease;
    }
    .cart-item-row:hover {
        background-color: #f8f9fa;
    }
    .cart-item-row:last-child {
        border-bottom: none;
    }
    .item-info {
        flex-grow: 1;
    }
    .item-name {
        font-weight: 600;
        color: #333;
        margin-bottom: 0.25rem;
    }
    .item-sku {
        font-size: 0.85rem;
        color: #6c757d;
    }
    .item-price {
        font-weight: 600;
        color: #0d6efd;
        min-width: 120px;
        text-align: right;
    }
    .qty-control {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: #f0f0f0;
        border-radius: 6px;
        padding: 0.25rem;
    }
    .qty-control button {
        width: 32px;
        height: 32px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border: none;
        background: white;
        color: #667eea;
        border-radius: 4px;
        cursor: pointer;
        font-weight: bold;
        transition: all 0.2s ease;
    }
    .qty-control button:hover {
        background: #667eea;
        color: white;
    }
    .qty-display {
        min-width: 50px;
        text-align: center;
        font-weight: 600;
    }
    .qty-input {
        min-width: 50px;
        width: 50px;
        padding: 0.25rem;
        text-align: center;
        font-weight: 600;
        border: none;
        background: white;
        color: #333;
        border-radius: 3px;
        font-size: 0.95rem;
        transition: all 0.2s ease;
    }
    .qty-input:focus {
        outline: none;
        border: 1px solid #667eea;
        box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.1);
    }
    .remove-btn {
        background: #ff6b6b;
        color: white;
        border: none;
        border-radius: 4px;
        padding: 0.5rem 0.75rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .remove-btn:hover {
        background: #ff5252;
    }
    .payment-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1.75rem;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
    }
    .payment-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        font-size: 0.95rem;
    }
    .payment-row.total {
        border-top: 2px solid rgba(255,255,255,0.2);
        border-bottom: 2px solid rgba(255,255,255,0.2);
        padding: 1rem 0;
        font-size: 1.3rem;
        font-weight: 700;
    }
    .payment-value {
        font-weight: 600;
    }
    .form-control, .form-select {
        border: 2px solid #e9ecef;
        border-radius: 6px;
        padding: 0.75rem 1rem;
        transition: all 0.3s ease;
        font-size: 0.95rem;
    }
    .form-control:focus, .form-select:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
    }
    /* Dropdown menu styling */
    .form-select {
        overflow: visible;
    }
    .form-select option {
        padding: 0.5rem 1rem;
    }
    .payment-btn {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white;
        padding: 0.9rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
        width: 100%;
        margin-top: 1rem;
    }
    .payment-btn:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
    }
    .payment-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    .form-label {
        font-weight: 600;
        color: white;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }
    .sticky-payment {
        position: sticky;
        top: 1rem;
    }
    .stats-row {
        display: flex;
        justify-content: space-around;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .stat-card {
        flex: 1;
        background: white;
        padding: 1rem;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        text-align: center;
    }
    .stat-label {
        color: #6c757d;
        font-size: 0.85rem;
        margin-bottom: 0.5rem;
    }
    .stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: #0d6efd;
    }
</style>

<div class="row g-3">
    <!-- MAIN CONTENT - LEFT SIDE -->
    <div class="col-lg-8">
        <!-- SEARCH INPUT -->
        <div class="card shadow-sm" style="border: none; border-radius: 12px; overflow: visible; position: relative; z-index: 1001;">
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 1.5rem; color: white; border-radius: 12px 12px 0 0;">
                <h6 class="mb-2" style="font-weight: 700; opacity: 0.9;">Scan atau Cari Barang</h6>
                <div class="pos-search-container">
                    <input type="text" id="search-input" class="pos-search-input w-100" placeholder="📱 Scan barcode atau ketik nama/SKU barang..." autocomplete="off" autofocus>
                    <div id="search-results" class="d-none search-results" style="z-index: 1010; top: 100%; left: 0; right: 0; position: absolute; width: 100%;"></div>
                </div>
                <small style="opacity: 0.8; display: block; margin-top: 0.75rem;">💡 Gunakan scan barcode atau ketik lalu tekan Enter untuk menambah barang</small>
            </div>
        </div>

        <!-- CART DISPLAY -->
        <div class="card shadow-sm mt-3" style="border: none; border-radius: 12px; overflow: hidden;">
            <div class="cart-header">
                <i class="bi bi-cart3"></i> Keranjang Belanja
            </div>
            <div id="cart-display">
                <div class="empty-cart">
                    <i class="bi bi-bag"></i>
                    <p class="mb-0">Keranjang kosong</p>
                    <small>Mulai dengan scan atau cari barang di atas</small>
                </div>
            </div>
        </div>
    </div>

    <!-- PAYMENT SECTION - RIGHT SIDE -->
    <div class="col-lg-4">
        <!-- ITEMS COUNT -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-label">Total Item</div>
                <div class="stat-value" id="stat-items">0</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Jumlah Barang</div>
                <div class="stat-value" id="stat-qty">0</div>
            </div>
        </div>

        <!-- PAYMENT FORM -->
        <div class="sticky-payment">
            <form method="POST" action="{{ route('sales.store') }}" id="pos-form">
                @csrf
                <input type="hidden" name="print_receipt" id="print-receipt-input" value="0">
                <div id="cart-hidden-inputs"></div>

                <!-- PAYMENT SUMMARY -->
                <div class="payment-section">
                    <div class="payment-row">
                        <span>Subtotal</span>
                        <span class="payment-value" id="display-subtotal">Rp 0</span>
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <label class="form-label">Diskon</label>
                        <div style="position: relative;">
                            <span style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: rgba(255,255,255,0.8);">Rp</span>
                            <input type="number" name="discount" id="discount-input" class="form-control" style="padding-left: 2.5rem;" value="0" min="0">
                        </div>
                    </div>

                    <div class="payment-row total">
                        <span>Total</span>
                        <span id="display-total">Rp 0</span>
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <label class="form-label">Metode Pembayaran</label>
                        <select name="payment_method" id="payment-method" class="form-select" required>
                            <option value="cash">💵 Tunai</option>
                            <option value="qris">📱 QRIS</option>
                            <option value="debit">🏧 Kartu Debit</option>
                            <option value="credit">💳 Kartu Kredit</option>
                        </select>
                    </div>

                    <div id="paid-amount-group" style="margin-bottom: 1rem;">
                        <label class="form-label">Jumlah Dibayar</label>
                        <div style="position: relative;">
                            <span style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: rgba(255,255,255,0.8);">Rp</span>
                            <input type="text" name="paid_amount" id="paid-amount-input" class="form-control" style="padding-left: 2.5rem;" inputmode="numeric" required>
                        </div>
                    </div>

                    <div class="payment-row" style="border-bottom: none; padding-bottom: 0;">
                        <span>Kembalian</span>
                        <span class="payment-value" id="display-change">Rp 0</span>
                    </div>

                    <div style="margin-top: 1.5rem;">
                        <label class="form-label">Catatan (Opsional)</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Contoh: Bungkus rapi, dll..." style="color: white; background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.3); resize: none;"></textarea>
                    </div>

                    <button type="button" class="payment-btn" id="submit-btn" disabled data-bs-toggle="modal" data-bs-target="#receiptChoiceModal">
                        <i class="bi bi-printer"></i> Bayar & Cetak Struk
                    </button>
                </div>

                <!-- QUICK ACTIONS -->
                <div style="margin-top: 1.5rem; display: flex; gap: 1rem;">
                    <button type="reset" class="btn btn-outline-secondary" style="flex-grow: 1;" onclick="if(confirm('Hapus semua item?')) { cart=[]; renderCart(); }">
                        <i class="bi bi-arrow-counterclockwise"></i> Reset
                    </button>
                    <a href="{{ route('sales.index') }}" class="btn btn-outline-primary" style="flex-grow: 1;">
                        <i class="bi bi-clock-history"></i> Riwayat
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="receiptChoiceModal" tabindex="-1" aria-labelledby="receiptChoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="receiptChoiceModalLabel">Cetak struk?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Pilih apakah Anda ingin membuka tampilan struk untuk dicetak sekarang.</p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" id="skip-receipt-btn">Tidak</button>
                <button type="button" class="btn btn-primary" id="print-receipt-btn">Cetak Struk</button>
            </div>
        </div>
    </div>
</div>

@if(session('pos_success') || session('pos_error'))
    <div class="modal fade" id="posStatusModal" tabindex="-1" aria-labelledby="posStatusModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title" id="posStatusModalLabel">{{ session('pos_error') ? '❌ Cetak Struk Gagal' : '✅ Transaksi Berhasil' }}</h5>
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
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal" onclick="location.reload()">Transaksi Baru</button>
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
const cartDisplay = document.getElementById('cart-display');
const discountInput = document.getElementById('discount-input');
const paymentMethodSelect = document.getElementById('payment-method');
const paidAmountInput = document.getElementById('paid-amount-input');
const paidAmountGroup = document.getElementById('paid-amount-group');
const submitBtn = document.getElementById('submit-btn');
const printReceiptInput = document.getElementById('print-receipt-input');
const printReceiptBtn = document.getElementById('print-receipt-btn');
const skipReceiptBtn = document.getElementById('skip-receipt-btn');
const posForm = document.getElementById('pos-form');

function formatRp(value) {
    return 'Rp ' + Math.round(value).toLocaleString('id-ID');
}

// Format untuk input paid amount dengan separator ribuan
function formatPaidAmount(value) {
    // Hapus semua karakter non-digit
    const digits = value.toString().replace(/\D/g, '');
    if (!digits) return '';
    // Format dengan separator ribuan
    return parseInt(digits).toLocaleString('id-ID');
}

// Parse nilai dari formatted paid amount
function parsePaidAmount(value) {
    const digits = value.toString().replace(/\D/g, '');
    return parseInt(digits) || 0;
}

// ==== Search functionality ====
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
        searchResults.innerHTML = '<div style="padding: 1.5rem; text-align: center; color: #6c757d; width: 100%;">Barang tidak ditemukan</div>';
        searchResults.classList.remove('d-none');
        return;
    }

    searchResults.innerHTML = items.map(item => `
        <button type="button" class="search-result-item" onclick='addToCartFromJson(${JSON.stringify(JSON.stringify(item))})'>
            <div style="flex: 1; min-width: 200px;">
                <div style="font-weight: 600; color: #333; margin-bottom: 0.25rem;">${item.name}</div>
                <div style="font-size: 0.85rem; color: #6c757d;">
                    <span>SKU: ${item.sku}</span>
                    <span style="margin-left: 0.5rem;">Stok: <strong style="color: #0d6efd;">${item.current_stock}</strong></span>
                </div>
            </div>
            <div style="font-weight: 700; color: #0d6efd; min-width: 120px; text-align: right; white-space: nowrap;">${formatRp(item.selling_price)}</div>
        </button>
    `).join('');
    searchResults.classList.remove('d-none');
}

function addToCartFromJson(jsonStr) {
    addToCart(JSON.parse(jsonStr));
    clearSearch();
}

// ==== Cart management ====
function addToCart(item) {
    if (item.current_stock <= 0) {
        alert(`❌ Stok "${item.name}" habis.`);
        return;
    }

    const existing = cart.find(line => line.id === item.id);

    if (existing) {
        if (existing.qty + 1 > item.current_stock) {
            alert(`❌ Stok "${item.name}" tidak mencukupi. Tersedia: ${item.current_stock}.`);
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
        alert(`❌ Stok "${line.name}" tidak mencukupi. Tersedia: ${line.stock}.`);
        return;
    }

    line.qty = newQty;
    renderCart();
}

function setQty(index, value) {
    const line = cart[index];
    const newQty = parseInt(value) || 0;

    if (newQty < 1) {
        removeFromCart(index);
        return;
    }

    if (newQty > line.stock) {
        alert(`❌ Stok "${line.name}" tidak mencukupi. Tersedia: ${line.stock}.`);
        renderCart(); // Re-render untuk reset input ke qty sebelumnya
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
        cartDisplay.innerHTML = `
            <div class="empty-cart">
                <i class="bi bi-bag"></i>
                <p class="mb-0">Keranjang kosong</p>
                <small>Mulai dengan scan atau cari barang di atas</small>
            </div>
        `;
        submitBtn.disabled = true;
    } else {
        cartDisplay.innerHTML = cart.map((line, i) => `
            <div class="cart-item-row">
                <div class="item-info">
                    <div class="item-name">${line.name}</div>
                    <div class="item-sku">SKU: ${line.sku}</div>
                </div>
                <div class="item-price">${formatRp(line.price)}</div>
                <div class="qty-control">
                    <button type="button" onclick="changeQty(${i}, -1)">−</button>
                    <input type="number" class="qty-input" value="${line.qty}" min="1" max="${line.stock}" onchange="setQty(${i}, this.value)" onkeypress="if(event.key==='Enter') setQty(${i}, this.value)">
                    <button type="button" onclick="changeQty(${i}, 1)">+</button>
                </div>
                <div style="min-width: 120px; text-align: right;">
                    <div style="font-weight: 600; color: #0d6efd;">${formatRp(line.price * line.qty)}</div>
                </div>
                <button type="button" class="remove-btn" onclick="removeFromCart(${i})" title="Hapus"><i class="bi bi-trash"></i></button>
            </div>
        `).join('');
        submitBtn.disabled = false;
    }

    calculateTotals();
    updateStats();
}

function updateStats() {
    const totalItems = cart.length;
    const totalQty = cart.reduce((sum, line) => sum + line.qty, 0);
    document.getElementById('stat-items').textContent = totalItems;
    document.getElementById('stat-qty').textContent = totalQty;
}

// ==== Calculations ====
function calculateTotals() {
    const subtotal = cart.reduce((sum, line) => sum + (line.price * line.qty), 0);
    const discount = Math.min(parseFloat(discountInput.value) || 0, subtotal);
    const total = subtotal - discount;

    document.getElementById('display-subtotal').textContent = formatRp(subtotal);
    document.getElementById('display-total').textContent = formatRp(total);

    // Otomatis isi jumlah dibayar sesuai total dengan format rupiah
    paidAmountInput.value = formatPaidAmount(total.toFixed(0));

    calculateChange(total);
}

function calculateChange(total) {
    const paid = parsePaidAmount(paidAmountInput.value) || 0;
    const change = paid - total;
    document.getElementById('display-change').textContent = formatRp(Math.max(change, 0));
}

discountInput.addEventListener('input', calculateTotals);
paidAmountInput.addEventListener('input', function () {
    // Format display dengan separator ribuan saat user mengetik
    this.value = formatPaidAmount(this.value);
    
    const subtotal = cart.reduce((sum, line) => sum + (line.price * line.qty), 0);
    const discount = Math.min(parseFloat(discountInput.value) || 0, subtotal);
    calculateChange(subtotal - discount);
});

paymentMethodSelect.addEventListener('change', function () {
    const isCash = this.value === 'cash';
    // Untuk cash: user bisa ubah jumlah dibayar
    // Untuk non-cash: otomatis dan tidak bisa diubah
    paidAmountInput.readOnly = !isCash;
    paidAmountGroup.querySelector('label').textContent = isCash ? 'Jumlah Dibayar' : 'Jumlah Dibayar (Otomatis)';
    calculateTotals();
});

document.addEventListener('click', function (e) {
    if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
        searchResults.classList.add('d-none');
    }
});

function buildCartInputs() {
    const container = document.getElementById('cart-hidden-inputs');
    container.innerHTML = '';
    cart.forEach((line, i) => {
        container.insertAdjacentHTML('beforeend', `
            <input type="hidden" name="items[${i}][item_id]" value="${line.id}">
            <input type="hidden" name="items[${i}][quantity]" value="${line.qty}">
        `);
    });
}

function submitPosForm(printReceipt) {
    printReceiptInput.value = printReceipt ? '1' : '0';
    paidAmountInput.value = parsePaidAmount(paidAmountInput.value);
    buildCartInputs();
    posForm.requestSubmit();
}

posForm.addEventListener('submit', function (e) {
    if (cart.length === 0) {
        e.preventDefault();
        alert('Tambahkan barang ke keranjang terlebih dahulu');
        return;
    }

    const subtotal = cart.reduce((sum, line) => sum + (line.price * line.qty), 0);
    const discount = Math.min(parseFloat(discountInput.value) || 0, subtotal);
    const total = subtotal - discount;
    const paid = parsePaidAmount(paidAmountInput.value) || 0;

    if (paid < total) {
        e.preventDefault();
        paidAmountInput.focus();
        paidAmountInput.style.borderColor = '#ff6b6b';
        paidAmountInput.style.boxShadow = '0 0 0 3px rgba(255, 107, 107, 0.1)';

        setTimeout(() => {
            paidAmountInput.style.borderColor = '';
            paidAmountInput.style.boxShadow = '';
        }, 3000);
        return;
    }

    paidAmountInput.value = parsePaidAmount(paidAmountInput.value);
    buildCartInputs();
});

if (printReceiptBtn) {
    printReceiptBtn.addEventListener('click', function () {
        const modalEl = document.getElementById('receiptChoiceModal');
        if (window.bootstrap && typeof window.bootstrap.Modal === 'function') {
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) {
                modal.hide();
            }
        }
        submitPosForm(true);
    });
}

if (skipReceiptBtn) {
    skipReceiptBtn.addEventListener('click', function () {
        const modalEl = document.getElementById('receiptChoiceModal');
        if (window.bootstrap && typeof window.bootstrap.Modal === 'function') {
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) {
                modal.hide();
            }
        }
        submitPosForm(false);
    });
}

calculateTotals();
updateStats();
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
