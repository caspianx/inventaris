<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrintFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'filename',
        'print_count',
        'printer_name',
        'last_printed_at',
    ];

    protected $casts = [
        'last_printed_at' => 'datetime',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
}
