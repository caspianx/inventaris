@extends('layouts.app')
@section('title', 'Pengaturan Toko')

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        <!-- PENGATURAN DASAR TOKO -->
        <div class="mb-4">
            <h5 class="mb-3"><i class="bi bi-building"></i> Pengaturan Dasar Toko</h5>
            <form method="POST" action="{{ route('store-settings.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row g-4">
                    <div class="col-md-7">
                        <div class="mb-3">
                            <label class="form-label">Nama Toko</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $storeSetting->name) }}" required autofocus>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Alamat Toko</label>
                            <textarea name="address" class="form-control" rows="4">{{ old('address', $storeSetting->address) }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Logo Toko</label>
                            <input type="file" name="logo" class="form-control" accept="image/png,image/jpeg,image/webp">
                            <div class="form-text">Format: JPG, PNG, atau WEBP. Maksimal 2 MB.</div>
                        </div>

                        @if($storeSetting->logo_path)
                            <div class="form-check mb-3">
                                <input type="checkbox" name="remove_logo" value="1" class="form-check-input" id="removeLogo">
                                <label class="form-check-label" for="removeLogo">Hapus logo saat ini</label>
                            </div>
                        @endif

                        <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan</button>
                    </div>

                    <div class="col-md-5">
                        <div class="border rounded p-3 bg-light">
                            <div class="text-muted small mb-2">Preview Struk</div>
                            @if(isset($receiptPreviewBase64))
                                <iframe src="data:text/html;base64,{{ $receiptPreviewBase64 }}" style="width:100%; border:1px solid #ddd; height:560px;" title="Preview Struk"></iframe>
                            @else
                                <div class="text-muted small mb-2">Preview Logo Toko</div>
                                @if($storeSetting->logo_path)
                                    <img src="{{ asset($storeSetting->logo_path) }}" alt="Logo" class="img-fluid" style="max-width: 200px;">
                                @else
                                    <p class="text-muted mb-0">Belum ada logo</p>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- PENGATURAN PRINT -->
        <div class="mt-4 pt-4 border-top">
            <h5 class="mb-3"><i class="bi bi-printer"></i> Pengaturan Print Struk</h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <h6 class="card-title">Pengaturan Print</h6>
                            <p class="card-text text-muted small">Atur printer default, ukuran kertas, dan opsi cetak otomatis.</p>
                            <a href="#printSettings" class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse">
                                <i class="bi bi-chevron-down"></i> Buka Pengaturan
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <h6 class="card-title">Desain Struk</h6>
                            <p class="card-text text-muted small">Atur konten header, item, dan footer pada struk cetak.</p>
                            <a href="#receiptSettings" class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse">
                                <i class="bi bi-chevron-down"></i> Buka Pengaturan
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PRINT SETTINGS COLLAPSE -->
            <div class="collapse mt-3" id="printSettings">
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="{{ route('store-settings.update') }}">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label class="form-label">Printer Default</label>
                                @if(!empty($printerOptions))
                                    <select id="defaultPrinterSelect" name="default_printer" class="form-select">
                                        <option value="">-- Pilih printer --</option>
                                        @foreach($printerOptions as $printer)
                                            <option value="{{ $printer }}" {{ old('default_printer', $storeSetting->default_printer) === $printer ? 'selected' : '' }}>{{ $printer }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <input id="defaultPrinterSelect" type="text" name="default_printer" class="form-control" value="{{ old('default_printer', $storeSetting->default_printer) }}" placeholder="Nama printer (mis. 'EPSON_TM-T20')">
                                @endif
                                <div class="form-text">Pilih printer yang tersedia di komputer.</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Ukuran Kertas Struk</label>
                                <select name="receipt_size" class="form-select">
                                    <option value="58mm" {{ old('receipt_size', $storeSetting->receipt_size) === '58mm' ? 'selected' : '' }}>58mm</option>
                                    <option value="80mm" {{ old('receipt_size', $storeSetting->receipt_size) === '80mm' ? 'selected' : '' }}>80mm</option>
                                    <option value="roll" {{ old('receipt_size', $storeSetting->receipt_size) === 'roll' ? 'selected' : '' }}>Roll (Custom)</option>
                                </select>
                            </div>

                            <div class="mb-3 form-check">
                                <input type="hidden" name="auto_print_receipt" value="0">
                                <input type="checkbox" name="auto_print_receipt" value="1" class="form-check-input" id="autoPrint" {{ old('auto_print_receipt', $storeSetting->auto_print_receipt) ? 'checked' : '' }}>
                                <label class="form-check-label" for="autoPrint">Cetak struk otomatis setelah pembayaran</label>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Jumlah Salinan Struk</label>
                                <input type="number" name="receipt_copies" class="form-control" value="{{ old('receipt_copies', $storeSetting->receipt_copies ?? 1) }}" min="1" max="10">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Batas Hari Simpan Struk</label>
                                <input type="number" name="receipt_retention_days" class="form-control" value="{{ old('receipt_retention_days', $storeSetting->receipt_retention_days ?? 30) }}" min="1" max="3650">
                                <div class="form-text">Struk lama akan dihapus otomatis setelah melewati batas hari ini.</div>
                            </div>

                            <div class="mb-3 form-check">
                                <input type="hidden" name="show_receipt_logo" value="0">
                                <input type="checkbox" name="show_receipt_logo" value="1" class="form-check-input" id="showReceiptLogo" {{ old('show_receipt_logo', $storeSetting->show_receipt_logo) ? 'checked' : '' }}>
                                <label class="form-check-label" for="showReceiptLogo">Tampilkan logo pada struk</label>
                            </div>

                            <input type="hidden" name="name" value="{{ old('name', $storeSetting->name) }}">
                            <input type="hidden" name="address" value="{{ old('address', $storeSetting->address) }}">

                            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- RECEIPT SETTINGS COLLAPSE -->
            <div class="collapse mt-3" id="receiptSettings">
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="{{ route('store-settings.update') }}">
                            @csrf
                            @method('PUT')

                            <h6 class="mb-3">Header Struk</h6>
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Judul</label>
                                    <input type="text" name="receipt_header_title" class="form-control" value="{{ old('receipt_header_title', $storeSetting->receipt_header_title) }}" placeholder="Contoh: Toko Maju Jaya">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Subjudul</label>
                                    <input type="text" name="receipt_header_subtitle" class="form-control" value="{{ old('receipt_header_subtitle', $storeSetting->receipt_header_subtitle) }}" placeholder="Contoh: Grosir & Eceran">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Baris Tambahan</label>
                                    <textarea name="receipt_header_extra" class="form-control" rows="2" placeholder="Contoh: Jl. Contoh No. 10, Bandung">{{ old('receipt_header_extra', $storeSetting->receipt_header_extra) }}</textarea>
                                </div>
                            </div>

                            <div class="border-top my-3"></div>
                            <h6 class="mb-3">Informasi Transaksi</h6>
                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input type="hidden" name="receipt_show_invoice_number" value="0">
                                        <input type="checkbox" name="receipt_show_invoice_number" value="1" class="form-check-input" id="showInvoiceNumber" {{ old('receipt_show_invoice_number', $storeSetting->receipt_show_invoice_number) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="showInvoiceNumber">No. Invoice</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input type="hidden" name="receipt_show_date_time" value="0">
                                        <input type="checkbox" name="receipt_show_date_time" value="1" class="form-check-input" id="showDateTime" {{ old('receipt_show_date_time', $storeSetting->receipt_show_date_time) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="showDateTime">Tanggal & Jam</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input type="hidden" name="receipt_show_cashier" value="0">
                                        <input type="checkbox" name="receipt_show_cashier" value="1" class="form-check-input" id="showCashier" {{ old('receipt_show_cashier', $storeSetting->receipt_show_cashier) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="showCashier">Kasir</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Label Kasir</label>
                                    <input type="text" name="receipt_cashier_label" class="form-control" value="{{ old('receipt_cashier_label', $storeSetting->receipt_cashier_label) }}">
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check mt-2">
                                        <input type="hidden" name="receipt_show_table" value="0">
                                        <input type="checkbox" name="receipt_show_table" value="1" class="form-check-input" id="showTable" {{ old('receipt_show_table', $storeSetting->receipt_show_table) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="showTable">Tampilkan Tabel</label>
                                    </div>
                                    <input type="text" name="receipt_table_label" class="form-control mt-2" value="{{ old('receipt_table_label', $storeSetting->receipt_table_label) }}" placeholder="Label Tabel">
                                </div>
                            </div>

                            <div class="border-top my-3"></div>
                            <h6 class="mb-3">Kolom Item</h6>
                            <div class="row g-3 mb-3">
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="hidden" name="receipt_show_item_sku" value="0">
                                        <input type="checkbox" name="receipt_show_item_sku" value="1" class="form-check-input" id="showItemSku" {{ old('receipt_show_item_sku', $storeSetting->receipt_show_item_sku) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="showItemSku">SKU</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="hidden" name="receipt_show_item_quantity" value="0">
                                        <input type="checkbox" name="receipt_show_item_quantity" value="1" class="form-check-input" id="showItemQty" {{ old('receipt_show_item_quantity', $storeSetting->receipt_show_item_quantity) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="showItemQty">Qty</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="hidden" name="receipt_show_item_price" value="0">
                                        <input type="checkbox" name="receipt_show_item_price" value="1" class="form-check-input" id="showItemPrice" {{ old('receipt_show_item_price', $storeSetting->receipt_show_item_price) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="showItemPrice">Harga</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="hidden" name="receipt_show_item_subtotal" value="0">
                                        <input type="checkbox" name="receipt_show_item_subtotal" value="1" class="form-check-input" id="showItemSubtotal" {{ old('receipt_show_item_subtotal', $storeSetting->receipt_show_item_subtotal) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="showItemSubtotal">Subtotal</label>
                                    </div>
                                </div>
                            </div>

                            <div class="border-top my-3"></div>
                            <h6 class="mb-3">Pembayaran & Pajak</h6>
                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input type="hidden" name="receipt_show_payment_method" value="0">
                                        <input type="checkbox" name="receipt_show_payment_method" value="1" class="form-check-input" id="showPaymentMethod" {{ old('receipt_show_payment_method', $storeSetting->receipt_show_payment_method) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="showPaymentMethod">Metode Bayar</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" name="receipt_payment_label" class="form-control form-control-sm" value="{{ old('receipt_payment_label', $storeSetting->receipt_payment_label) }}" placeholder="Label Bayar">
                                </div>
                                <div class="col-md-4">
                                    <input type="text" name="receipt_change_label" class="form-control form-control-sm" value="{{ old('receipt_change_label', $storeSetting->receipt_change_label) }}" placeholder="Label Kembalian">
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input type="hidden" name="receipt_show_tax_line" value="0">
                                        <input type="checkbox" name="receipt_show_tax_line" value="1" class="form-check-input" id="showTaxLine" {{ old('receipt_show_tax_line', $storeSetting->receipt_show_tax_line) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="showTaxLine">Tampilkan Pajak</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <input type="text" name="receipt_tax_label" class="form-control form-control-sm" value="{{ old('receipt_tax_label', $storeSetting->receipt_tax_label) }}" placeholder="Label Pajak">
                                </div>
                                <div class="col-md-3">
                                    <input type="number" step="0.01" name="receipt_tax_rate" class="form-control form-control-sm" value="{{ old('receipt_tax_rate', $storeSetting->receipt_tax_rate) }}" placeholder="Rate %">
                                </div>
                            </div>

                            <div class="border-top my-3"></div>
                            <h6 class="mb-3">Footer</h6>
                            <div class="mb-3">
                                <label class="form-label">Teks Terima Kasih</label>
                                <input type="text" name="receipt_thank_you_text" class="form-control" value="{{ old('receipt_thank_you_text', $storeSetting->receipt_thank_you_text) }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Catatan Footer</label>
                                <textarea name="receipt_footer_note" class="form-control" rows="2" placeholder="Contoh: Telp: 08123456789">{{ old('receipt_footer_note', $storeSetting->receipt_footer_note) }}</textarea>
                            </div>

                            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- SIMULASI PRINT -->
            <div class="mt-4 pt-3">
                <h6 class="mb-3">Uji Simulasi Print</h6>
                <form action="{{ route('store-settings.simulate-print') }}" method="POST" class="row g-3">
                    @csrf
                    <div class="col-md-6">
                        <label class="form-label">Nama Printer</label>
                        <input id="simulatePrinterInput" type="text" name="printer" class="form-control" value="{{ old('printer', $storeSetting->default_printer ?? 'POS-1') }}" placeholder="Contoh: POS-1">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Jumlah Salinan</label>
                        <input type="number" name="copies" class="form-control" value="{{ old('copies', $storeSetting->receipt_copies ?? 2) }}" min="1" max="10">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-outline-success">
                            <i class="bi bi-play-circle"></i> Jalankan Simulasi
                        </button>
                    </div>
                </form>

                @if(session('simulation_output'))
                    <div class="mt-3 p-3 bg-dark text-white rounded">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong>Output Simulasi</strong>
                            <span class="badge bg-success">{{ session('simulation_command') }}</span>
                        </div>
                        <pre class="mb-0" style="white-space: pre-wrap; word-wrap: break-word;">{{ session('simulation_output') }}</pre>
                    </div>
                @endif
            </div>
        </div>

        <!-- PENGATURAN CASH DRAWER -->
        @if($hasCashDrawer)
            <div class="mt-4 pt-4 border-top">
                <h5 class="mb-3"><i class="bi bi-cash-coin"></i> Pengaturan Cash Drawer</h5>
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="{{ route('store-settings.update') }}">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label class="form-label">Driver</label>
                                <select name="cash_drawer_driver" class="form-select">
                                    <option value="network" {{ old('cash_drawer_driver', $storeSetting->cash_drawer_driver ?? 'network') === 'network' ? 'selected' : '' }}>Network / API (HTTP)</option>
                                    <option value="printer" {{ old('cash_drawer_driver', $storeSetting->cash_drawer_driver ?? 'network') === 'printer' ? 'selected' : '' }}>Printer (ESC/POS pulse)</option>
                                    <option value="none" {{ old('cash_drawer_driver', $storeSetting->cash_drawer_driver ?? 'network') === 'none' ? 'selected' : '' }}>Tidak Ada</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Alamat Perangkat</label>
                                <input type="text" name="cash_drawer_address" class="form-control" value="{{ old('cash_drawer_address', $storeSetting->cash_drawer_address) }}" placeholder="Alamat perangkat (mis. http://192.168.1.100/open)">
                                <div class="form-text">Network: masukkan URL. Printer: masukkan path ke device/printer share.</div>
                            </div>

                            <input type="hidden" name="name" value="{{ old('name', $storeSetting->name) }}">
                            <input type="hidden" name="address" value="{{ old('address', $storeSetting->address) }}">

                            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan</button>
                        </form>
                    </div>
                </div>

                <!-- SIMULASI CASH DRAWER -->
                <div class="mt-4 pt-3">
                    <h6 class="mb-3">Uji Simulasi Cash Drawer</h6>
                    <form id="testCashDrawerForm" method="POST" action="{{ route('store-settings.test-cash-drawer') }}">
                        @csrf
                        <input type="hidden" name="cash_drawer_address" value="{{ old('cash_drawer_address', $storeSetting->cash_drawer_address) }}">
                        <button id="testCashDrawerBtn" type="submit" class="btn btn-outline-primary">
                            <i class="bi bi-plug"></i> Jalankan Simulasi
                        </button>
                    </form>
                    <div id="testCashDrawerResult" class="mt-3" aria-live="polite"></div>
                </div>
            </div>
        @endif

        <!-- LINK KE PROFIL PENGGUNA -->
        <div class="mt-4 pt-4 border-top">
            <a href="{{ route('profile.edit') }}" class="btn btn-secondary">
                <i class="bi bi-person-gear"></i> Pengaturan Perangkat Pengguna
            </a>
            <div class="form-text mt-2">Atur perangkat dan preferensi personal Anda di halaman profil.</div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Sync default printer with simulate printer
        const defaultPrinter = document.getElementById('defaultPrinterSelect');
        const simulatePrinter = document.getElementById('simulatePrinterInput');

        if (defaultPrinter && simulatePrinter) {
            const syncValue = function () {
                simulatePrinter.value = defaultPrinter.value || simulatePrinter.value;
            };
            defaultPrinter.addEventListener('change', syncValue);
            syncValue();
        }

        // Handle cash drawer test form
        const testForm = document.getElementById('testCashDrawerForm');
        if (!testForm) {
            return;
        }

        const resultWrap = document.getElementById('testCashDrawerResult');
        const submitButton = document.getElementById('testCashDrawerBtn');
        const addressInput = document.querySelector('input[name="cash_drawer_address"]');
        const hiddenAddressInput = testForm.querySelector('input[name="cash_drawer_address"]');

        testForm.addEventListener('submit', async function (event) {
            event.preventDefault();

            if (addressInput && hiddenAddressInput) {
                hiddenAddressInput.value = addressInput.value;
            }

            const originalLabel = submitButton ? submitButton.innerHTML : '';
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Mengirim...';
            }

            if (resultWrap) {
                resultWrap.innerHTML = '';
            }

            try {
                const formData = new FormData(testForm);
                const response = await fetch(testForm.action, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json().catch(function () {
                    return null;
                });

                if (response.ok && data && data.success) {
                    if (resultWrap) {
                        resultWrap.innerHTML = '<div class="alert alert-success">' + data.message + '</div>';
                    }
                } else {
                    const message = data && data.message ? data.message : 'HTTP ' + response.status + ' - ' + response.statusText;
                    if (resultWrap) {
                        resultWrap.innerHTML = '<div class="alert alert-danger">' + message + '</div>';
                    }
                }
            } catch (error) {
                if (resultWrap) {
                    resultWrap.innerHTML = '<div class="alert alert-danger">' + error.message + '</div>';
                }
            } finally {
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalLabel;
                }
            }
        });
    });
</script>
@endsection
