<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Composite indexes matched to the filter+sort pairs the admin and employee
 * screens actually run. Each single-column index that becomes a strict leftmost
 * prefix of a new composite is dropped, so the index count stays flat and the
 * write path does not get slower.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Dashboard: WHERE attendance_date = ? AND status IN (...)
        Schema::table('attendances', function (Blueprint $table) {
            $table->index(['attendance_date', 'status']);
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex('attendances_attendance_date_index');
        });

        // Roster absensi: WHERE is_active = 1 ORDER BY full_name
        Schema::table('employees', function (Blueprint $table) {
            $table->index(['is_active', 'full_name']);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndex('employees_is_active_index');
        });

        // leave_requests belum punya index sama sekali di luar foreign key,
        // padahal query-nya sama persis dengan dua tabel pengajuan lainnya.
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->index(['employee_id', 'status']);
            $table->index(['status', 'created_at']);
            $table->index(['start_date', 'end_date']);
        });

        // Daftar admin: WHERE status = ? ORDER BY created_at DESC, plus pendingCount.
        Schema::table('remote_work_requests', function (Blueprint $table) {
            $table->index(['status', 'created_at']);
        });

        Schema::table('overtime_requests', function (Blueprint $table) {
            $table->index(['status', 'created_at']);
        });

        // Tabel yang tumbuh paling cepat: setiap aksi admin menulis satu baris.
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->index(['action', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });

        // Dijalankan setelah komposit di atas ada, supaya foreign key user_id
        // tetap punya index yang menaunginya saat index lama dilepas.
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

        // MySQL melepas index bawaan foreign key employee_id begitu komposit
        // di atas menaunginya, jadi index tunggalnya harus dikembalikan dulu —
        // tanpa ini foreign key kehilangan penopang dan drop-nya ditolak.
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
