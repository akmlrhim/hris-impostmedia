<?php

namespace App\Enums;

enum Permission: string
{
    case ManageEmployees = 'manage_employees';
    case ManagePayroll = 'manage_payroll';
    case ManageAttendance = 'manage_attendance';
    case ManageShifts = 'manage_shifts';
    case ManageHolidays = 'manage_holidays';
    case ManageAnnouncements = 'manage_announcements';
    case ManageOfficeLocations = 'manage_office_locations';
    case ManageRemoteWork = 'manage_remote_work';
    case ManageLeave = 'manage_leave';
    case ManageUsers = 'manage_users';

    public function label(): string
    {
        return match ($this) {
            self::ManageEmployees => 'Kelola Karyawan',
            self::ManagePayroll => 'Kelola Payroll',
            self::ManageAttendance => 'Kelola Absensi',
            self::ManageShifts => 'Kelola Shift & Jadwal',
            self::ManageHolidays => 'Kelola Hari Libur',
            self::ManageAnnouncements => 'Kelola Pengumuman',
            self::ManageOfficeLocations => 'Kelola Lokasi Kantor',
            self::ManageRemoteWork => 'Kelola Pengajuan WFA/WFH',
            self::ManageLeave => 'Kelola Pengajuan Cuti & Izin',
            self::ManageUsers => 'Kelola Pengguna & Hak Akses',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::ManageEmployees => 'Menambah, mengedit, dan menonaktifkan data karyawan',
            self::ManagePayroll => 'Membuat dan memproses payroll karyawan',
            self::ManageAttendance => 'Melihat dan mengelola data absensi seluruh karyawan',
            self::ManageShifts => 'Mengelola shift kerja dan jadwal karyawan',
            self::ManageHolidays => 'Mengelola hari libur nasional dan internal',
            self::ManageAnnouncements => 'Membuat dan menerbitkan pengumuman',
            self::ManageOfficeLocations => 'Mengelola lokasi kantor dan radius geofencing',
            self::ManageRemoteWork => 'Menyetujui atau menolak pengajuan WFA/WFH karyawan',
            self::ManageLeave => 'Menyetujui atau menolak pengajuan cuti dan izin karyawan',
            self::ManageUsers => 'Mengelola akun pengguna dan konfigurasi hak akses (Super Admin)',
        };
    }

    /** Permissions that can be toggled per role (excludes ManageUsers - Admin only). */
    public static function configurable(): array
    {
        return array_values(array_filter(self::cases(), fn ($p) => $p !== self::ManageUsers));
    }
}
