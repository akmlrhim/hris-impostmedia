<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('employees', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->nullable()->unique()->constrained()->cascadeOnDelete();

			// personal 
			$table->string('employee_number', 30)->unique();
			$table->string('full_name');
			$table->string('nickname', 80)->nullable();
			$table->string('gender', 10)->nullable();
			$table->date('date_of_birth')->nullable();
			$table->string('place_of_birth', 100)->nullable();
			$table->string('work_type', 10)->default('wfa');
			$table->string('phone', 30)->nullable();
			$table->string('nik', 20);
			$table->string('email')->nullable();
			$table->text('address')->nullable();

			// pendidikan
			$table->string('last_education', 100);
			$table->string('major_school_university');

			// bank 
			$table->string('bank_name', 60)->nullable();
			$table->string('bank_account_number', 30)->nullable();
			$table->string('bank_account_holder')->nullable();

			// gaji 
			$table->decimal('basic_salary', 15, 2)->default(0);

			// kontak darurat
			$table->string('emergency_contact_name', 100);
			$table->string('emergency_contact_number', 24);

			// periode kontrak 
			$table->date('contract_start_date')->nullable();
			$table->date('contract_end_date')->nullable();

			$table->string('avatar_path')->nullable();
			$table->json('face_descriptor')->nullable();
			$table->boolean('is_active')->default(true);
			$table->timestamps();
			$table->softDeletes();

			$table->index('employment_status');
			$table->index('is_active');
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('employees');
	}
};
