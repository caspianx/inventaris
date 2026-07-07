<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\RecordsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory, RecordsActivity;

    protected $fillable = [
        'invoice_number',
        'user_id',
        'subtotal',
        'discount',
        'total',
        'payment_method',
        'paid_amount',
        'change_amount',
        'notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function printFile()
    {
        return $this->hasOne(PrintFile::class);
    }
}
