<?php

namespace Database\Seeders;

use App\Models\PayrollComponent;
use Illuminate\Database\Seeder;

class PayrollComponentSeeder extends Seeder
{
    public function run(): void
    {
        $components = [
            ['code' => 'BASIC', 'name' => 'Gaji Pokok', 'type' => 'earning', 'calculation_type' => 'fixed', 'default_amount' => 0, 'is_taxable' => true, 'is_bpjs_subject' => true, 'is_active' => true],
            ['code' => 'TRANSPORT', 'name' => 'Tunjangan Transportasi', 'type' => 'earning', 'calculation_type' => 'fixed', 'default_amount' => 500000, 'is_taxable' => true, 'is_bpjs_subject' => false, 'is_active' => true],
            ['code' => 'MEAL', 'name' => 'Tunjangan Makan', 'type' => 'earning', 'calculation_type' => 'fixed', 'default_amount' => 350000, 'is_taxable' => true, 'is_bpjs_subject' => false, 'is_active' => true],
            ['code' => 'HOUSING', 'name' => 'Tunjangan Rumah', 'type' => 'earning', 'calculation_type' => 'fixed', 'default_amount' => 0, 'is_taxable' => true, 'is_bpjs_subject' => false, 'is_active' => true],
            ['code' => 'OVERTIME', 'name' => 'Lembur', 'type' => 'earning', 'calculation_type' => 'fixed', 'default_amount' => 0, 'is_taxable' => true, 'is_bpjs_subject' => false, 'is_active' => true],
            ['code' => 'BPJS_KES', 'name' => 'BPJS Kesehatan', 'type' => 'deduction', 'calculation_type' => 'percentage', 'default_amount' => 1, 'is_taxable' => false, 'is_bpjs_subject' => false, 'is_active' => true],
            ['code' => 'BPJS_JKK', 'name' => 'BPJS JKK', 'type' => 'deduction', 'calculation_type' => 'percentage', 'default_amount' => 0.24, 'is_taxable' => false, 'is_bpjs_subject' => false, 'is_active' => true],
            ['code' => 'BPJS_JKM', 'name' => 'BPJS JKM', 'type' => 'deduction', 'calculation_type' => 'percentage', 'default_amount' => 0.3, 'is_taxable' => false, 'is_bpjs_subject' => false, 'is_active' => true],
            ['code' => 'BPJS_JHT', 'name' => 'BPJS JHT (Karyawan)', 'type' => 'deduction', 'calculation_type' => 'percentage', 'default_amount' => 2, 'is_taxable' => false, 'is_bpjs_subject' => false, 'is_active' => true],
            ['code' => 'BPJS_JP', 'name' => 'BPJS JP (Karyawan)', 'type' => 'deduction', 'calculation_type' => 'percentage', 'default_amount' => 1, 'is_taxable' => false, 'is_bpjs_subject' => false, 'is_active' => true],
            ['code' => 'PPH21', 'name' => 'PPh 21', 'type' => 'deduction', 'calculation_type' => 'fixed', 'default_amount' => 0, 'is_taxable' => false, 'is_bpjs_subject' => false, 'is_active' => true],
        ];

        foreach ($components as $comp) {
            PayrollComponent::updateOrCreate(
                ['code' => $comp['code']],
                $comp,
            );
        }

        $this->command?->info('Komponen payroll dibuat: '.count($components).' komponen.');
    }
}
