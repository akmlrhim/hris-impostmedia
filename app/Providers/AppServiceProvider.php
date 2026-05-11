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

        // Admin has full access to all gates
        Gate::before(function (User $user) {
            if ($user->role === UserRole::Admin) {
                return true;
            }
        });

        // manage_users is Admin only — Gate::before handles it above
        Gate::define(Permission::ManageUsers->value, fn () => false);

        // All other permissions are configurable per role via the database
        foreach (Permission::configurable() as $permission) {
            Gate::define($permission->value, function (User $user) use ($permission) {
                static $cache = [];

                if (! ($user->role instanceof UserRole)) {
                    return false;
                }

                $key = "{$user->role->value}:{$permission->value}";

                if (! array_key_exists($key, $cache)) {
                    $cache[$key] = RolePermission::where('role', $user->role->value)
                        ->where('permission', $permission->value)
                        ->exists();
                }

                return $cache[$key];
            });
        }
    }
}
