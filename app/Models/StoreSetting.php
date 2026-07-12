<?php

namespace App\Models;

use App\Traits\RecordsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreSetting extends Model
{
    use HasFactory, RecordsActivity;

    protected $fillable = [
        'name',
        'address',
        'logo_path',
        'default_printer',
        'auto_print_receipt',
        'receipt_copies',
        'receipt_size',
        'receipt_retention_days',
        'show_receipt_logo',
        'receipt_header_title',
        'receipt_header_subtitle',
        'receipt_header_extra',
        'receipt_show_invoice_number',
        'receipt_show_date_time',
        'receipt_show_cashier',
        'receipt_cashier_label',
        'receipt_show_table',
        'receipt_table_label',
        'receipt_show_tax_line',
        'receipt_tax_label',
        'receipt_tax_rate',
        'receipt_show_payment_method',
        'receipt_payment_label',
        'receipt_change_label',
        'receipt_show_item_sku',
        'receipt_show_item_quantity',
        'receipt_show_item_price',
        'receipt_show_item_subtotal',
        'receipt_thank_you_text',
        'receipt_footer_note',
        'cash_drawer_driver',
        'cash_drawer_address',
    ];

    public static function current(): self
    {
        return static::query()->first() ?? static::query()->create([
            'name' => 'Inventory App',
            'address' => 'Jl. Contoh Alamat No. 123',
            'default_printer' => null,
            'auto_print_receipt' => false,
            'receipt_copies' => 1,
            'receipt_size' => '80mm',
            'receipt_retention_days' => 30,
            'show_receipt_logo' => true,
            'receipt_header_title' => null,
            'receipt_header_subtitle' => null,
            'receipt_header_extra' => null,
            'receipt_show_invoice_number' => true,
            'receipt_show_date_time' => true,
            'receipt_show_cashier' => true,
            'receipt_cashier_label' => 'Kasir',
            'receipt_show_table' => false,
            'receipt_table_label' => 'Tabel',
            'receipt_show_tax_line' => false,
            'receipt_tax_label' => 'Pajak',
            'receipt_tax_rate' => 0,
            'receipt_show_payment_method' => true,
            'receipt_payment_label' => 'Bayar',
            'receipt_change_label' => 'Kembalian',
            'receipt_show_item_sku' => true,
            'receipt_show_item_quantity' => true,
            'receipt_show_item_price' => true,
            'receipt_show_item_subtotal' => true,
            'receipt_thank_you_text' => 'Terima kasih atas kunjungan Anda!',
            'receipt_footer_note' => null,
            'cash_drawer_driver' => 'network',
            'cash_drawer_address' => null,
        ]);
    }
}
