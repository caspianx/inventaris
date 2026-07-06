<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\RecordsActivity;

class StoreSetting extends Model
{
    use HasFactory, RecordsActivity;

    protected $fillable = [
        'name',
        'address',
        'logo_path',
    ];

    public static function current(): self
    {
        return static::query()->first() ?? static::query()->create([
            'name' => 'Inventory App',
            'address' => 'Jl. Contoh Alamat No. 123',
        ]);
    }
}
