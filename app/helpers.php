<?php

use App\Enums\Permission;
use Illuminate\Support\Facades\Gate;

if (! function_exists('rupiah')) {
    /**
     * Format angka jadi "Rp 1.000.000".
     * Null/kosong tetap menghasilkan "Rp 0" agar tidak ada string kosong di UI.
     */
    function rupiah(int|float|string|null $value, int $decimals = 0): string
    {
        $number = is_numeric($value) ? (float) $value : 0;

        return 'Rp '.number_format($number, $decimals, ',', '.');
    }
}

if (! function_exists('can_view_salary')) {
    /**
     * Hanya HR yang boleh melihat nominal gaji. Admin sekalipun disensor,
     * karena gate view_salary dikecualikan dari bypass Admin.
     */
    function can_view_salary(): bool
    {
        return Gate::allows(
            Permission::ViewSalary->value,
        );
    }
}

if (! function_exists('rupiah_masked')) {
    /**
     * Sama seperti rupiah(), tapi jadi "*****" untuk pengguna tanpa hak view_salary.
     * Pakai ini di seluruh panel admin agar nominal gaji tidak bocor ke non-HR.
     */
    function rupiah_masked(int|float|string|null $value, int $decimals = 0): string
    {
        return can_view_salary() ? rupiah($value, $decimals) : '*****';
    }
}

if (! function_exists('rupiah_short')) {
    /**
     * Format angka jadi bentuk ringkas: "Rp 1,2 jt", "Rp 500 rb", "Rp 1,5 M".
     * Cocok untuk kartu statistik dashboard.
     */
    function rupiah_short(int|float|string|null $value): string
    {
        $number = is_numeric($value) ? (float) $value : 0;
        $abs = abs($number);
        $sign = $number < 0 ? '-' : '';

        if ($abs >= 1_000_000_000) {
            return $sign.'Rp '.rtrim(rtrim(number_format($abs / 1_000_000_000, 1, ',', '.'), '0'), ',').' M';
        }
        if ($abs >= 1_000_000) {
            return $sign.'Rp '.rtrim(rtrim(number_format($abs / 1_000_000, 1, ',', '.'), '0'), ',').' jt';
        }
        if ($abs >= 1_000) {
            return $sign.'Rp '.number_format($abs / 1_000, 0, ',', '.').' rb';
        }

        return $sign.'Rp '.number_format($abs, 0, ',', '.');
    }
}
