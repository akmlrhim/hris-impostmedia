<?php

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
