<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\RecordsActivity;

class RolePermission extends Model
{
    use HasFactory, RecordsActivity;

    protected $fillable = [
        'role',
        'permission',
    ];
}
