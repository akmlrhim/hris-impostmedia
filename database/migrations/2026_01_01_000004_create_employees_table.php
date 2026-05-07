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
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('job_position_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('manager_id')->nullable()->constrained('employees')->nullOnDelete();

            $table->string('employee_number', 30)->unique();
            $table->string('full_name');
            $table->string('nickname', 80)->nullable();
            $table->string('gender', 10)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('place_of_birth', 100)->nullable();
            $table->string('religion', 30)->nullable();

            $table->string('nik_ktp', 20)->nullable()->unique();
            $table->string('npwp', 25)->nullable();
            $table->string('passport_number', 30)->nullable();
            $table->string('bpjs_kesehatan', 30)->nullable();
            $table->string('bpjs_ketenagakerjaan', 30)->nullable();

            $table->string('phone', 30)->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone', 30)->nullable();
            $table->string('emergency_contact_relation', 50)->nullable();

            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('province', 100)->nullable();
            $table->string('postal_code', 10)->nullable();

            $table->string('employment_status', 20)->default('permanent');
            $table->date('join_date')->nullable();
            $table->date('probation_end_date')->nullable();
            $table->date('contract_end_date')->nullable();
            $table->date('resign_date')->nullable();
            $table->text('resign_reason')->nullable();

            $table->string('bank_name', 60)->nullable();
            $table->string('bank_account_number', 30)->nullable();
            $table->string('bank_account_holder')->nullable();

            $table->string('ptkp_status', 10)->nullable();
            $table->decimal('basic_salary', 15, 2)->default(0);

            $table->string('avatar_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('employment_status');
            $table->index('is_active');
        });

        Schema::create('employee_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('type', 50);
            $table->string('name');
            $table->string('file_path');
            $table->date('issued_date')->nullable();
            $table->date('expired_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_documents');
        Schema::dropIfExists('employees');
    }
};
