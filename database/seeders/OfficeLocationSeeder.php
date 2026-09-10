<?php

namespace Database\Seeders;

use App\Models\OfficeLocation;
use Illuminate\Database\Seeder;

class OfficeLocationSeeder extends Seeder
{
    public function run(): void
    {
        $locations = [
            [
                'name' => 'Kantor Pusat Jakarta',
                'address' => 'Jl. Sudirman Kav. 52-53, Jakarta Selatan, DKI Jakarta',
                'latitude' => -6.2088,
                'longitude' => 106.8456,
                'radius_meters' => 150,
                'is_active' => true,
            ],
            [
                'name' => 'Kantor Cabang Surabaya',
                'address' => 'Jl. Basuki Rahmat No. 10-12, Surabaya, Jawa Timur',
                'latitude' => -7.2575,
                'longitude' => 112.7521,
                'radius_meters' => 100,
                'is_active' => true,
            ],
            [
                'name' => 'Kantor Cabang Bandung',
                'address' => 'Jl. Asia Afrika No. 15-17, Bandung, Jawa Barat',
                'latitude' => -6.9175,
                'longitude' => 107.6191,
                'radius_meters' => 120,
                'is_active' => false,
            ],
        ];

        foreach ($locations as $loc) {
            OfficeLocation::updateOrCreate(
                ['name' => $loc['name']],
                $loc,
            );
        }

        $this->command?->info('Lokasi kantor dibuat: '.count($locations).' lokasi.');
    }
}
