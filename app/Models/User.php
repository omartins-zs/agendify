<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use Notifiable;

    protected $fillable = [
        'company_id',
        'name',
        'email',
        'password',
        'role',
        'status',
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
            'role' => UserRole::class,
            'status' => UserStatus::class,
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function isActive(): bool
    {
        return ($this->status ?? UserStatus::Inactive)->isActive();
    }

    public function hasRole(UserRole $role): bool
    {
        return $this->role === $role;
    }

    public function hasAnyRole(UserRole ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function canManageServices(): bool
    {
        return ($this->role ?? UserRole::Viewer)->canManageServices();
    }

    public function canManageAppointments(): bool
    {
        return ($this->role ?? UserRole::Viewer)->canManageAppointments();
    }

    public function canManageClients(): bool
    {
        return ($this->role ?? UserRole::Viewer)->canManageClients();
    }

    public function canManageAvailability(): bool
    {
        return ($this->role ?? UserRole::Viewer)->canManageAvailability();
    }
}
