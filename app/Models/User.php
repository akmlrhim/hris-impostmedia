<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'roles', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'roles' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }

    public function hasRole(UserRole $role): bool
    {
        return in_array($role->value, $this->roles ?? [], true);
    }

    /** @return UserRole[] */
    public function getRoleObjects(): array
    {
        return array_values(array_filter(
            array_map(fn ($r) => UserRole::tryFrom($r), $this->roles ?? [])
        ));
    }

    public function primaryRole(): ?UserRole
    {
        foreach ([UserRole::Admin, UserRole::HR, UserRole::Employee] as $role) {
            if ($this->hasRole($role)) {
                return $role;
            }
        }

        return null;
    }

    public function isAdminPanel(): bool
    {
        return $this->hasRole(UserRole::Admin) || $this->hasRole(UserRole::HR);
    }

    public function isEmployee(): bool
    {
        return $this->hasRole(UserRole::Employee);
    }
}
