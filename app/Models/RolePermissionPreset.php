<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RolePermissionPreset extends Model
{
    use HasFactory;

    protected $table = 'role_permission_presets';

    protected $fillable = ['name', 'label', 'permissions'];

    protected $casts = [
        'permissions' => 'array',
    ];
}
