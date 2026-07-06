<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\RecordsActivity;

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
