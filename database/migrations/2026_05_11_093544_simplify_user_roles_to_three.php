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
		// super_admin → admin (Admin is now the highest role)
		DB::table('users')->where('role', 'super_admin')->update(['role' => 'admin']);

		DB::table('users')->where('role', 'manager')->update(['role' => 'hr']);

		DB::table('role_permissions')
			->whereIn('role', ['super_admin', 'manager'])
			->delete();
	}

	public function down(): void {}
};
