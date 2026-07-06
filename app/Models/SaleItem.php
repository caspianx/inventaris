<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\RecordsActivity;

class SaleItem extends Model
{
    use HasFactory, RecordsActivity;

    protected $fillable = [
        'sale_id',
        'item_id',
        'item_name',
        'item_sku',
        'price',
        'quantity',
        'subtotal',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
