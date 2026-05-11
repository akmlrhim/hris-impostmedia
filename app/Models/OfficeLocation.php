<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'address', 'latitude', 'longitude', 'radius_meters', 'is_active'])]
class OfficeLocation extends Model
{
    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function distanceTo(float $lat, float $lng): float
    {
        $R = 6371000;
        $dLat = deg2rad($lat - $this->latitude);
        $dLon = deg2rad($lng - $this->longitude);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($this->latitude)) * cos(deg2rad($lat)) * sin($dLon / 2) ** 2;

        return $R * 2 * asin(sqrt($a));
    }

    public function isWithinRadius(float $lat, float $lng): bool
    {
        return $this->distanceTo($lat, $lng) <= $this->radius_meters;
    }
}
