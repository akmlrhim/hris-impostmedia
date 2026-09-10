<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            OfficeLocationSeeder::class,
            PayrollComponentSeeder::class,
            AttendanceSeeder::class,
            PayrollPeriodSeeder::class,
        ]);
    }
}
