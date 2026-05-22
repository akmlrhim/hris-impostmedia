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
            $table->foreignId('manager_id')->nullable()->constrained('employees')->nullOnDelete();

            $table->string('employee_number', 30)->unique();
            $table->string('full_name');
            $table->string('nickname', 80)->nullable();
            $table->string('gender', 10)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('place_of_birth', 100)->nullable();
            $table->string('religion', 30)->nullable();

            $table->string('phone', 30)->nullable();

            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('province', 100)->nullable();
            $table->string('postal_code', 10)->nullable();

            $table->string('employment_status', 20)->default('permanent');
            $table->date('join_date')->nullable();
            $table->date('probation_end_date')->nullable();
            $table->date('contract_end_date')->nullable();
            $table->string('bank_name', 60)->nullable();
            $table->string('bank_account_number', 30)->nullable();
            $table->string('bank_account_holder')->nullable();

            $table->decimal('basic_salary', 15, 2)->default(0);

            $table->string('avatar_path')->nullable();
            $table->json('face_descriptor')->nullable();
            $table->string('work_type', 10)->default('wfa');
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
