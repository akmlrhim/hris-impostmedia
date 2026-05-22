<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Bersihkan role_permissions lama yang tidak relevan
        DB::table('role_permissions')
            ->whereIn('role', ['super_admin', 'manager'])
            ->delete();
    }

    public function down(): void {}
};
