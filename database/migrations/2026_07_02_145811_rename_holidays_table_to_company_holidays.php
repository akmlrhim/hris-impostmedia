<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		if (Schema::hasTable('holidays') && ! Schema::hasTable('company_holidays')) {
			Schema::rename('holidays', 'company_holidays');
		}
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		if (Schema::hasTable('company_holidays') && ! Schema::hasTable('holidays')) {
			Schema::rename('company_holidays', 'holidays');
		}
	}
};
