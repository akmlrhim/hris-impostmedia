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

        // Admin role mendapat akses penuh ke semua gate, kecuali view_salary
        Gate::before(function (User $user, string $ability) {
            // Nominal gaji sengaja dikecualikan dari bypass Admin, jadi biarkan
            // gate-nya sendiri yang memutuskan (null = lanjut ke Gate::define).
            if ($ability === Permission::ViewSalary->value) {
                return null;
            }

            if ($user->hasRole(UserRole::Admin)) {
                return true;
            }
        });

        // manage_users hanya Admin - ditangani Gate::before di atas
        Gate::define(Permission::ManageUsers->value, fn () => false);

        // view_salary hanya HR - Admin pun melihat nominal gaji tersensor
        Gate::define(
            Permission::ViewSalary->value,
            fn (User $user) => $user->hasRole(UserRole::HR),
        );

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
