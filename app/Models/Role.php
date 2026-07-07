<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\RecordsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory, RecordsActivity;

    protected $fillable = [
        'name',
        'label',
    ];

    public function permissions()
    {
        return $this->hasMany(RolePermission::class, 'role', 'name');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'role_id');
    }
}
