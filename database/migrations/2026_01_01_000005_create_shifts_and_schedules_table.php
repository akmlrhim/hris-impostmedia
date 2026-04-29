<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->time('start_time');
            $table->time('end_time');
            $table->time('break_start')->nullable();
            $table->time('break_end')->nullable();
            $table->unsignedInteger('late_tolerance_minutes')->default(15);
            $table->unsignedInteger('early_leave_tolerance_minutes')->default(0);
            $table->boolean('is_overnight')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('work_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shift_id')->constrained();
            $table->date('work_date');
            $table->string('day_type', 20)->default('workday');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'work_date']);
            $table->index('work_date');
        });

        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_national')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holidays');
        Schema::dropIfExists('work_schedules');
        Schema::dropIfExists('shifts');
    }
};
