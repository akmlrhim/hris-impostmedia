<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Admin has all access via Gate::before — no DB entries needed for Admin.
        // Only HR permissions are stored; Employee has no admin panel access.
        $defaults = [
            UserRole::HR->value => [
                'manage_employees',
                'manage_payroll',
                'manage_attendance',
                'manage_shifts',
                'manage_holidays',
                'manage_announcements',
            ],
        ];

        DB::table('role_permissions')->delete();

        $rows = [];
        $now = now();
        foreach ($defaults as $role => $permissions) {
            foreach ($permissions as $permission) {
                $rows[] = [
                    'role' => $role,
                    'permission' => $permission,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('role_permissions')->insert($rows);
    }
}
