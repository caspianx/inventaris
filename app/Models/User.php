<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\RecordsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;

class User extends Authenticatable
{
    use HasFactory, Notifiable, RecordsActivity;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'role_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->getRoleName() === 'admin';
    }

    public function isManager(): bool
    {
        return $this->getRoleName() === 'manager';
    }

    public function isStaff(): bool
    {
        return $this->getRoleName() === 'staff';
    }

    /**
     * Check if user has access to a specific permission.
     * Admin has access to all permissions.
     *
     * @param  string  $permission  Permission name to check
     * @return bool True if user can access the permission
     */
    public function canAccess(string $permission): bool
    {
        $roleName = $this->getRoleName();

        if ($roleName === 'admin') {
            return true;
        }

        $permissions = Cache::remember(
            "role_permissions.{$roleName}",
            now()->addMinutes(10),
            function (): array {
                return RolePermission::where('role', $roleName)
                    ->pluck('permission')
                    ->all();
            }
        );

        return in_array($permission, $permissions, true);
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function getRoleName(): ?string
    {
        return $this->role?->name ?? $this->role;
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }
}
