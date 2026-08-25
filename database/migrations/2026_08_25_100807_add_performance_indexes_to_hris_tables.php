<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->index(['attendance_date', 'status']);
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex('attendances_attendance_date_index');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->index(['is_active', 'full_name']);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndex('employees_is_active_index');
        });

        Schema::table('leave_requests', function (Blueprint $table) {
            $table->index(['employee_id', 'status']);
            $table->index(['status', 'created_at']);
            $table->index(['start_date', 'end_date']);
        });

        Schema::table('remote_work_requests', function (Blueprint $table) {
            $table->index(['status', 'created_at']);
        });

        Schema::table('overtime_requests', function (Blueprint $table) {
            $table->index(['status', 'created_at']);
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->index(['action', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex('activity_logs_action_index');
            $table->dropIndex('activity_logs_user_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->index('action');
            $table->index('user_id');
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex('activity_logs_action_created_at_index');
            $table->dropIndex('activity_logs_user_id_created_at_index');
        });

        Schema::table('overtime_requests', function (Blueprint $table) {
            $table->dropIndex('overtime_requests_status_created_at_index');
        });

        Schema::table('remote_work_requests', function (Blueprint $table) {
            $table->dropIndex('remote_work_requests_status_created_at_index');
        });

        Schema::table('leave_requests', function (Blueprint $table) {
            $table->index('employee_id');
        });

        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropIndex('leave_requests_employee_id_status_index');
            $table->dropIndex('leave_requests_status_created_at_index');
            $table->dropIndex('leave_requests_start_date_end_date_index');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->index('is_active');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndex('employees_is_active_full_name_index');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->index('attendance_date');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex('attendances_attendance_date_status_index');
        });
    }
};
