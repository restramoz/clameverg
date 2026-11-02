<?php

namespace Tests\Unit;

use App\Services\RegionalMapService;
use Tests\TestCase;

class RegionalMapServiceTest extends TestCase
{
    public function test_missing_coordinates_return_null(): void
    {
        $service = app(RegionalMapService::class);

        $this->assertNull($service->getCityCoordinate('wilayah-fiktif-123'));
        $this->assertNull($service->getDistrictCoordinate('wilayah-fiktif-123', 'kecamatan-fiktif-456'));
    }

    public function test_verified_coordinates_return_valid_data(): void
    {
        $service = app(RegionalMapService::class);

        $city = $service->getCityCoordinate('bandung');
        $this->assertNotNull($city);
        $this->assertIsFloat($city['lat']);
        $this->assertIsFloat($city['lng']);
        $this->assertNotNull($city['source']);

        $district = $service->getDistrictCoordinate('bandung', 'coblong');
        $this->assertNotNull($district);
        $this->assertIsFloat($district['lat']);
        $this->assertIsFloat($district['lng']);

        // Distinct coordinates between parent city centroid and district
        $this->assertNotEquals($city['lat'], $district['lat']);
    }

    public function test_invalid_coordinate_range_is_rejected(): void
    {
        $path = resource_path('data/regional_coordinates.json');
        $original = file_get_contents($path);

        file_put_contents($path, json_encode([
            'cities' => ['test-city' => ['lat' => 100, 'lng' => 107]],
            'districts' => [],
        ]));

        try {
            $service = new RegionalMapService();
            $this->assertNull($service->getCityCoordinate('test-city'));
        } finally {
            file_put_contents($path, $original);
        }
    }
}
