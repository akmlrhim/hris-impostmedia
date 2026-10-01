<?php

use App\Enums\Permission;
use App\Enums\UserRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Grant the overtime permission to every role that already reviews remote work requests. */
    public function up(): void
    {
        $roles = DB::table('role_permissions')
            ->where('permission', Permission::ManageRemoteWork->value)
            ->pluck('role')
            ->push(UserRole::HR->value)
            ->unique();

        $now = now();

        foreach ($roles as $role) {
            $alreadyGranted = DB::table('role_permissions')
                ->where('role', $role)
                ->where('permission', Permission::ManageOvertime->value)
                ->exists();

            if ($alreadyGranted) {
                continue;
            }

            DB::table('role_permissions')->insert([
                'role' => $role,
                'permission' => Permission::ManageOvertime->value,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('role_permissions')
            ->where('permission', Permission::ManageOvertime->value)
            ->delete();
    }
};
