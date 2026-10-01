<?php

use App\Enums\Permission;
use Illuminate\Support\Facades\Gate;

if (! function_exists('rupiah')) {
    function rupiah(int|float|string|null $value, int $decimals = 0): string
    {
        $number = is_numeric($value) ? (float) $value : 0;

        return 'Rp '.number_format($number, $decimals, ',', '.');
    }
}

if (! function_exists('can_view_salary')) {
    function can_view_salary(): bool
    {
        return Gate::allows(
            Permission::ViewSalary->value,
        );
    }
}

if (! function_exists('rupiah_masked')) {
    function rupiah_masked(int|float|string|null $value, int $decimals = 0): string
    {
        return can_view_salary() ? rupiah($value, $decimals) : '*****';
    }
}

if (! function_exists('rupiah_short')) {
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
