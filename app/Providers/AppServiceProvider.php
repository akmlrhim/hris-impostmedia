<?php

namespace App\Providers;

use App\Enums\Permission;
use App\Enums\UserRole;
use App\Models\RolePermission;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Carbon::setLocale('id');

        // Admin role mendapat akses penuh ke semua gate
        Gate::before(function (User $user) {
            if ($user->hasRole(UserRole::Admin)) {
                return true;
            }
        });

        // manage_users hanya Admin - ditangani Gate::before di atas
        Gate::define(Permission::ManageUsers->value, fn () => false);

        // Semua permission lain dicek dari role_permissions berdasarkan semua role yang dimiliki
        foreach (Permission::configurable() as $permission) {
            Gate::define($permission->value, function (User $user) use ($permission) {
                static $cache = [];

                foreach ($user->getRoleObjects() as $role) {
                    $key = "{$role->value}:{$permission->value}";

                    if (! array_key_exists($key, $cache)) {
                        $cache[$key] = RolePermission::where('role', $role->value)
                            ->where('permission', $permission->value)
                            ->exists();
                    }

                    if ($cache[$key]) {
                        return true;
                    }
                }

                return false;
            });
        }
    }
}
