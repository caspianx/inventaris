@extends('layouts.app')
@section('title', 'Pengaturan Toko')

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
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
                        <div class="form-text">Pilih printer yang tersedia di komputer. Jika daftar tidak muncul, bisa mengetik manual.</div>
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
                        <label class="form-label">Ukuran Kertas Struk</label>
                        <select name="receipt_size" class="form-select">
                            <option value="58mm" {{ old('receipt_size', $storeSetting->receipt_size) === '58mm' ? 'selected' : '' }}>58mm</option>
                            <option value="80mm" {{ old('receipt_size', $storeSetting->receipt_size) === '80mm' ? 'selected' : '' }}>80mm</option>
                            <option value="roll" {{ old('receipt_size', $storeSetting->receipt_size) === 'roll' ? 'selected' : '' }}>Roll (Custom)</option>
                        </select>
                        <div class="form-text">Pilih ukuran kertas yang akan digunakan untuk mencetak struk.</div>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="hidden" name="show_receipt_logo" value="0">
                        <input type="checkbox" name="show_receipt_logo" value="1" class="form-check-input" id="showReceiptLogo" {{ old('show_receipt_logo', $storeSetting->show_receipt_logo) ? 'checked' : '' }}>
                        <label class="form-check-label" for="showReceiptLogo">Tampilkan logo pada struk</label>
                    </div>

                    @if($storeSetting->logo_path)
                        <div class="form-check mb-3">
                            <input type="checkbox" name="remove_logo" value="1" class="form-check-input" id="removeLogo">
                            <label class="form-check-label" for="removeLogo">Hapus logo saat ini</label>
                        </div>
                    @endif

                    <div class="border-top my-4"></div>
                    <h5 class="mb-3">Pengaturan Isi Struk</h5>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Judul Struk</label>
                            <input type="text" name="receipt_header_title" class="form-control" value="{{ old('receipt_header_title', $storeSetting->receipt_header_title) }}" placeholder="Contoh: Toko Maju Jaya">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Subjudul Struk</label>
                            <input type="text" name="receipt_header_subtitle" class="form-control" value="{{ old('receipt_header_subtitle', $storeSetting->receipt_header_subtitle) }}" placeholder="Contoh: Grosir & Eceran">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Baris Tambahan Header</label>
                            <textarea name="receipt_header_extra" class="form-control" rows="2" placeholder="Contoh: Jl. Contoh No. 10, Bandung">{{ old('receipt_header_extra', $storeSetting->receipt_header_extra) }}</textarea>
                        </div>
                    </div>

                    <div class="row g-3 mt-3">
                        <div class="col-md-4">
                            <div class="form-check">
                                <input type="hidden" name="receipt_show_invoice_number" value="0">
                                <input type="checkbox" name="receipt_show_invoice_number" value="1" class="form-check-input" id="showInvoiceNumber" {{ old('receipt_show_invoice_number', $storeSetting->receipt_show_invoice_number) ? 'checked' : '' }}>
                                <label class="form-check-label" for="showInvoiceNumber">Tampilkan No. Invoice</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input type="hidden" name="receipt_show_date_time" value="0">
                                <input type="checkbox" name="receipt_show_date_time" value="1" class="form-check-input" id="showDateTime" {{ old('receipt_show_date_time', $storeSetting->receipt_show_date_time) ? 'checked' : '' }}>
                                <label class="form-check-label" for="showDateTime">Tampilkan Tanggal</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input type="hidden" name="receipt_show_cashier" value="0">
                                <input type="checkbox" name="receipt_show_cashier" value="1" class="form-check-input" id="showCashier" {{ old('receipt_show_cashier', $storeSetting->receipt_show_cashier) ? 'checked' : '' }}>
                                <label class="form-check-label" for="showCashier">Tampilkan Kasir</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Label Kasir</label>
                            <input type="text" name="receipt_cashier_label" class="form-control" value="{{ old('receipt_cashier_label', $storeSetting->receipt_cashier_label) }}">
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input type="hidden" name="receipt_show_table" value="0">
                                <input type="checkbox" name="receipt_show_table" value="1" class="form-check-input" id="showTable" {{ old('receipt_show_table', $storeSetting->receipt_show_table) ? 'checked' : '' }}>
                                <label class="form-check-label" for="showTable">Tampilkan Tabel</label>
                            </div>
                            <input type="text" name="receipt_table_label" class="form-control mt-2" value="{{ old('receipt_table_label', $storeSetting->receipt_table_label) }}" placeholder="Label Tabel">
                        </div>
                    </div>

                    <div class="row g-3 mt-3">
                        <div class="col-md-3">
                            <div class="form-check">
                                <input type="hidden" name="receipt_show_item_sku" value="0">
                                <input type="checkbox" name="receipt_show_item_sku" value="1" class="form-check-input" id="showItemSku" {{ old('receipt_show_item_sku', $storeSetting->receipt_show_item_sku) ? 'checked' : '' }}>
                                <label class="form-check-label" for="showItemSku">Tampilkan SKU</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check">
                                <input type="hidden" name="receipt_show_item_quantity" value="0">
                                <input type="checkbox" name="receipt_show_item_quantity" value="1" class="form-check-input" id="showItemQty" {{ old('receipt_show_item_quantity', $storeSetting->receipt_show_item_quantity) ? 'checked' : '' }}>
                                <label class="form-check-label" for="showItemQty">Tampilkan Qty</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check">
                                <input type="hidden" name="receipt_show_item_price" value="0">
                                <input type="checkbox" name="receipt_show_item_price" value="1" class="form-check-input" id="showItemPrice" {{ old('receipt_show_item_price', $storeSetting->receipt_show_item_price) ? 'checked' : '' }}>
                                <label class="form-check-label" for="showItemPrice">Tampilkan Harga</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check">
                                <input type="hidden" name="receipt_show_item_subtotal" value="0">
                                <input type="checkbox" name="receipt_show_item_subtotal" value="1" class="form-check-input" id="showItemSubtotal" {{ old('receipt_show_item_subtotal', $storeSetting->receipt_show_item_subtotal) ? 'checked' : '' }}>
                                <label class="form-check-label" for="showItemSubtotal">Tampilkan Subtotal</label>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mt-3">
                        <div class="col-md-4">
                            <div class="form-check">
                                <input type="hidden" name="receipt_show_payment_method" value="0">
                                <input type="checkbox" name="receipt_show_payment_method" value="1" class="form-check-input" id="showPaymentMethod" {{ old('receipt_show_payment_method', $storeSetting->receipt_show_payment_method) ? 'checked' : '' }}>
                                <label class="form-check-label" for="showPaymentMethod">Tampilkan Metode Bayar</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="receipt_payment_label" class="form-control" value="{{ old('receipt_payment_label', $storeSetting->receipt_payment_label) }}" placeholder="Label Bayar">
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="receipt_change_label" class="form-control" value="{{ old('receipt_change_label', $storeSetting->receipt_change_label) }}" placeholder="Label Kembalian">
                        </div>
                    </div>

                    <div class="row g-3 mt-3">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input type="hidden" name="receipt_show_tax_line" value="0">
                                <input type="checkbox" name="receipt_show_tax_line" value="1" class="form-check-input" id="showTaxLine" {{ old('receipt_show_tax_line', $storeSetting->receipt_show_tax_line) ? 'checked' : '' }}>
                                <label class="form-check-label" for="showTaxLine">Tampilkan Baris Pajak</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="receipt_tax_label" class="form-control" value="{{ old('receipt_tax_label', $storeSetting->receipt_tax_label) }}" placeholder="Label Pajak">
                        </div>
                        <div class="col-md-3">
                            <input type="number" step="0.01" name="receipt_tax_rate" class="form-control" value="{{ old('receipt_tax_rate', $storeSetting->receipt_tax_rate) }}" placeholder="Rate %">
                        </div>
                    </div>

                    <div class="mb-3 mt-3">
                        <label class="form-label">Teks Terima Kasih</label>
                        <input type="text" name="receipt_thank_you_text" class="form-control" value="{{ old('receipt_thank_you_text', $storeSetting->receipt_thank_you_text) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan Footer</label>
                        <textarea name="receipt_footer_note" class="form-control" rows="2" placeholder="Contoh: Telp: 08123456789">{{ old('receipt_footer_note', $storeSetting->receipt_footer_note) }}</textarea>
                    </div>

                    <button class="btn btn-primary"><i class="bi bi-save"></i> Simpan Pengaturan</button>
                </form>

                <div class="mt-4 border-top pt-3">
                    <h6>Uji Perintah Simulasi</h6>
                    <form action="{{ route('store-settings.simulate-print') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Nama Printer</label>
                            <input id="simulatePrinterInput" type="text" name="printer" class="form-control" value="{{ old('printer', $storeSetting->default_printer ?? 'POS-1') }}" placeholder="Contoh: POS-1 atau EPSON_TM-T20">
                            <div class="form-text">Nama printer yang akan dipakai untuk simulasi cetak struk.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Jumlah Salinan</label>
                            <input type="number" name="copies" class="form-control" value="{{ old('copies', $storeSetting->receipt_copies ?? 2) }}" min="1" max="10">
                            <div class="form-text">Jumlah salinan struk yang akan disimulasikan.</div>
                        </div>

                        <button type="submit" class="btn btn-outline-success">
                            <i class="bi bi-play-circle"></i> Jalankan Simulasi Print
                        </button>
                        <div class="form-text text-muted mt-2">Menjalankan perintah <code>php artisan simulate:all-features --printer="POS-1" --copies=2 --auto</code> menggunakan nilai input di atas.</div>
                    </form>

                    @if(session('simulation_output'))
                        <div class="mt-4 p-3 bg-dark text-white rounded">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <strong>Output Simulasi</strong>
                                </div>
                                <span class="badge bg-success">Command: {{ session('simulation_command') }}</span>
                            </div>
                            <pre class="mb-0" style="white-space: pre-wrap; word-wrap: break-word;">{{ session('simulation_output') }}</pre>
                        </div>
                    @endif
                </div>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    var defaultPrinter = document.getElementById('defaultPrinterSelect');
                    var simulatePrinter = document.getElementById('simulatePrinterInput');

                    if (!defaultPrinter || !simulatePrinter) {
                        return;
                    }

                    var syncValue = function () {
                        simulatePrinter.value = defaultPrinter.value || simulatePrinter.value;
                    };

                    defaultPrinter.addEventListener('change', function () {
                        syncValue();
                    });

                    syncValue();
                });
            </script>
        </form>

                <div class="col-md-5">
                    <div class="border rounded p-3 bg-light">
                        <div class="text-muted small mb-2">Preview Struk Cetak</div>
                        @php
                            $previewWidth = match($storeSetting->receipt_size ?? '80mm') {
                                '58mm' => '220px',
                                '80mm' => '300px',
                                'roll' => '340px',
                                default => '300px',
                            };
                        @endphp
                        <div class="bg-white rounded border p-3 mx-auto" id="previewReceipt" style="width: {{ $previewWidth }};">
                            <div class="text-center mb-3">
                                <img id="previewLogo" data-logo-src="{{ $storeSetting->logo_path ? asset($storeSetting->logo_path) : '' }}" src="{{ $storeSetting->logo_path && $storeSetting->show_receipt_logo ? asset($storeSetting->logo_path) : '' }}" alt="Logo" class="mb-2" style="max-width: 100px; max-height: 70px; object-fit: contain; display: {{ $storeSetting->logo_path && $storeSetting->show_receipt_logo ? 'block' : 'none' }}; margin: 0 auto;">
                                <strong id="previewHeaderTitle" style="font-size: 1rem; display: block;">{{ strtoupper(trim($storeSetting->receipt_header_title) !== '' ? $storeSetting->receipt_header_title : $storeSetting->name) }}</strong>
                                <div id="previewHeaderSubtitle" class="text-muted small" style="white-space: pre-line; {{ $storeSetting->receipt_header_subtitle ? '' : 'display:none;' }}">{{ $storeSetting->receipt_header_subtitle }}</div>
                                <div id="previewHeaderAddress" class="text-muted small" style="white-space: pre-line; {{ $storeSetting->address ? '' : 'display:none;' }}">{{ $storeSetting->address }}</div>
                                <div id="previewHeaderExtra" class="text-muted small" style="white-space: pre-line; {{ $storeSetting->receipt_header_extra ? '' : 'display:none;' }}">{{ $storeSetting->receipt_header_extra }}</div>
                            </div>

                            <div class="border-top my-2"></div>

                            <div id="previewMetaBlock">
                                <div class="d-flex justify-content-between small mb-1" id="previewInvoiceDateRow">
                                    <span id="previewInvoiceNumber" style="display: {{ $storeSetting->receipt_show_invoice_number ? 'inline' : 'none' }}">No. Invoice: INV-1234</span>
                                    <span id="previewDateTime" style="display: {{ $storeSetting->receipt_show_date_time ? 'inline' : 'none' }}">{{ now()->format('d/m/Y H:i:s') }}</span>
                                </div>
                                <div class="d-flex justify-content-between small mb-1" id="previewCashierRow" style="display: {{ $storeSetting->receipt_show_cashier ? 'flex' : 'none' }};">
                                    <span id="previewCashierLabel">{{ $storeSetting->receipt_cashier_label }}:</span>
                                    <span>Admin</span>
                                </div>
                                <div class="d-flex justify-content-between small mb-1" id="previewTableRow" style="display: {{ $storeSetting->receipt_show_table ? 'flex' : 'none' }};">
                                    <span id="previewTableLabel">{{ $storeSetting->receipt_table_label }}:</span>
                                    <span>7/01</span>
                                </div>
                                <div class="border-top my-2"></div>
                            </div>

                            <div class="small" id="previewItems">
                                <div class="d-flex justify-content-between mb-1">
                                    <span id="previewItem1Name">2x Latte Macchiato</span>
                                    <span id="previewItem1Price">Rp 45.000</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span id="previewItem2Name">1x Gloki</span>
                                    <span id="previewItem2Price">Rp 50.000</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span id="previewItem3Name">1x Schweinschnitzel</span>
                                    <span id="previewItem3Price">Rp 220.000</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span id="previewItem4Name">1x Chässpatzli</span>
                                    <span id="previewItem4Price">Rp 185.000</span>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between small mb-1" id="previewSubtotalRow" style="display: {{ $storeSetting->receipt_show_item_subtotal ? 'flex' : 'none' }};">
                                <span>Subtotal</span>
                                <span>Rp 300.000</span>
                            </div>

                            <div class="border-top my-2"></div>

                            <div class="d-flex justify-content-between fw-bold mb-1">
                                <span>Total :</span>
                                <span id="previewTotal">Rp 300.000</span>
                            </div>

                            <div class="d-flex justify-content-between small mb-1" id="previewTaxRow" style="display: {{ $storeSetting->receipt_show_tax_line ? 'flex' : 'none' }};">
                                <span id="previewTaxLabel">{{ $storeSetting->receipt_tax_label }} {{ number_format($storeSetting->receipt_tax_rate, 2) }}%</span>
                                <span id="previewTaxValue">Rp {{ number_format(300000 * $storeSetting->receipt_tax_rate / 100, 0, ',', '.') }}</span>
                            </div>

                            <div class="border-top my-2"></div>

                            <div id="previewPaymentBlock" style="display: {{ $storeSetting->receipt_show_payment_method ? 'block' : 'none' }};">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span id="previewPaymentLabel">{{ $storeSetting->receipt_payment_label }}:</span>
                                    <span>Rp 320.000</span>
                                </div>
                                <div class="d-flex justify-content-between small mb-1">
                                    <span id="previewChangeLabel">{{ $storeSetting->receipt_change_label }}:</span>
                                    <span>Rp 20.000</span>
                                </div>
                                <div class="border-top my-2"></div>
                            </div>

                            <div class="text-center small mt-2" id="previewThankYou">{{ $storeSetting->receipt_thank_you_text }}</div>

                            <div class="text-center text-muted small mt-2" id="previewFooterNote" style="display: {{ $storeSetting->receipt_footer_note ? 'block' : 'none' }}; white-space: pre-line;">{{ $storeSetting->receipt_footer_note }}</div>

                            <div class="text-center text-muted small mt-2">
                                Ukuran: <span id="previewReceiptSize">{{ strtoupper($storeSetting->receipt_size ?? '80mm') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        function updatePreview() {
            const getValue = selector => document.querySelector(selector)?.value || '';
            const getChecked = selector => {
                const element = document.querySelector(selector);
                return element ? element.checked : false;
            };
            const getSelect = selector => document.querySelector(selector)?.value || '';

            const previewLogo = document.getElementById('previewLogo');
            const logoPath = previewLogo?.dataset.logoSrc || '';
            const showLogo = getChecked('input[type="checkbox"][name="show_receipt_logo"]') && logoPath;
            if (previewLogo) {
                previewLogo.style.display = showLogo ? 'block' : 'none';
                previewLogo.src = showLogo ? logoPath : '';
            }

            const headerTitle = getValue('input[name="receipt_header_title"]') || getValue('input[name="name"]');
            document.getElementById('previewHeaderTitle').textContent = headerTitle ? headerTitle.toUpperCase() : '';
            document.getElementById('previewHeaderSubtitle').textContent = getValue('input[name="receipt_header_subtitle"]');
            document.getElementById('previewHeaderSubtitle').style.display = getValue('input[name="receipt_header_subtitle"]') ? 'block' : 'none';
            document.getElementById('previewHeaderAddress').textContent = getValue('textarea[name="address"]');
            document.getElementById('previewHeaderAddress').style.display = getValue('textarea[name="address"]') ? 'block' : 'none';
            document.getElementById('previewHeaderExtra').textContent = getValue('textarea[name="receipt_header_extra"]');
            document.getElementById('previewHeaderExtra').style.display = getValue('textarea[name="receipt_header_extra"]') ? 'block' : 'none';

            const showInvoice = getChecked('input[type="checkbox"][name="receipt_show_invoice_number"]');
            const showDate = getChecked('input[type="checkbox"][name="receipt_show_date_time"]');
            document.getElementById('previewInvoiceNumber').style.display = showInvoice ? 'inline' : 'none';
            document.getElementById('previewDateTime').style.display = showDate ? 'inline' : 'none';
            document.getElementById('previewInvoiceDateRow').style.display = showInvoice || showDate ? 'flex' : 'none';

            const showCashier = getChecked('input[type="checkbox"][name="receipt_show_cashier"]');
            document.getElementById('previewCashierRow').style.display = showCashier ? 'flex' : 'none';
            document.getElementById('previewCashierLabel').textContent = getValue('input[name="receipt_cashier_label"]') + ':';

            const showTable = getChecked('input[type="checkbox"][name="receipt_show_table"]');
            document.getElementById('previewTableRow').style.display = showTable ? 'flex' : 'none';
            document.getElementById('previewTableLabel').textContent = getValue('input[name="receipt_table_label"]') + ':';

            const showQty = getChecked('input[type="checkbox"][name="receipt_show_item_quantity"]');
            const showSku = getChecked('input[type="checkbox"][name="receipt_show_item_sku"]');
            const showPrice = getChecked('input[type="checkbox"][name="receipt_show_item_price"]');
            const showSubtotal = getChecked('input[type="checkbox"][name="receipt_show_item_subtotal"]');

            document.getElementById('previewItem1Name').textContent = showQty ? '2x Latte Macchiato' : 'Latte Macchiato';
            document.getElementById('previewItem2Name').textContent = showQty ? '1x Gloki' : 'Gloki';
            document.getElementById('previewItem3Name').textContent = showQty ? '1x Schweinschnitzel' : 'Schweinschnitzel';
            document.getElementById('previewItem4Name').textContent = showQty ? '1x Chässpatzli' : 'Chässpatzli';

            if (showSku) {
                document.getElementById('previewItem1Name').textContent += ' (SKU: LTM001)';
                document.getElementById('previewItem2Name').textContent += ' (SKU: GLK002)';
                document.getElementById('previewItem3Name').textContent += ' (SKU: SCH003)';
                document.getElementById('previewItem4Name').textContent += ' (SKU: CHS004)';
            }

            document.getElementById('previewItem1Price').textContent = showPrice ? 'Rp 45.000' : '';
            document.getElementById('previewItem2Price').textContent = showPrice ? 'Rp 50.000' : '';
            document.getElementById('previewItem3Price').textContent = showPrice ? 'Rp 220.000' : '';
            document.getElementById('previewItem4Price').textContent = showPrice ? 'Rp 185.000' : '';
            document.getElementById('previewItem1Price').style.visibility = showPrice ? 'visible' : 'hidden';
            document.getElementById('previewItem2Price').style.visibility = showPrice ? 'visible' : 'hidden';
            document.getElementById('previewItem3Price').style.visibility = showPrice ? 'visible' : 'hidden';
            document.getElementById('previewItem4Price').style.visibility = showPrice ? 'visible' : 'hidden';

            document.getElementById('previewSubtotalRow').style.display = showSubtotal ? 'flex' : 'none';

            const showTax = getChecked('input[type="checkbox"][name="receipt_show_tax_line"]');
            document.getElementById('previewTaxRow').style.display = showTax ? 'flex' : 'none';
            document.getElementById('previewTaxLabel').textContent = getValue('input[name="receipt_tax_label"]') + ' ' + parseFloat(getValue('input[name="receipt_tax_rate"]') || 0).toFixed(2) + '%';
            const taxValue = Math.round(300000 * (parseFloat(getValue('input[name="receipt_tax_rate"]') || 0) / 100));
            document.getElementById('previewTaxValue').textContent = 'Rp ' + taxValue.toLocaleString('id-ID');

            const showPayment = getChecked('input[type="checkbox"][name="receipt_show_payment_method"]');
            document.getElementById('previewPaymentBlock').style.display = showPayment ? 'block' : 'none';
            document.getElementById('previewPaymentLabel').textContent = getValue('input[name="receipt_payment_label"]') + ':';
            document.getElementById('previewChangeLabel').textContent = getValue('input[name="receipt_change_label"]') + ':';

            document.getElementById('previewThankYou').textContent = getValue('input[name="receipt_thank_you_text"]');
            const footerNote = getValue('textarea[name="receipt_footer_note"]');
            document.getElementById('previewFooterNote').textContent = footerNote;
            document.getElementById('previewFooterNote').style.display = footerNote ? 'block' : 'none';

            document.getElementById('previewReceiptSize').textContent = getSelect('select[name="receipt_size"]').toUpperCase();
            document.getElementById('previewReceipt').style.width = getSelect('select[name="receipt_size"]') === '58mm' ? '220px' : getSelect('select[name="receipt_size"]') === 'roll' ? '340px' : '300px';
        }

        const inputs = document.querySelectorAll('input[name^="receipt_"], textarea[name^="receipt_"], input[name="name"], textarea[name="address"], select[name="receipt_size"], input[type="checkbox"][name="show_receipt_logo"]');
        inputs.forEach(input => {
            const eventType = input.type === 'checkbox' ? 'change' : 'input';
            input.addEventListener(eventType, updatePreview);
            input.addEventListener('change', updatePreview);
        });

        updatePreview();
    });
</script>
@endsection
