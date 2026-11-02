<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

/**
 * Reads verified regional coordinates from a separate documented dataset.
 *
 * Coordinates are intentionally not fabricated. The dataset may be empty until
 * reliable sources have been verified and recorded.
 */
class RegionalMapService
{
    private static ?array $data = null;

    private static function dataPath(): string
    {
        return resource_path('data/regional_coordinates.json');
    }

    private static function initialize(): void
    {
        if (self::$data !== null) {
            return;
        }

        $path = self::dataPath();
        if (! File::exists($path)) {
            self::$data = ['cities' => [], 'districts' => []];

            return;
        }

        $decoded = json_decode((string) File::get($path), true);
        if (! is_array($decoded)) {
            self::$data = ['cities' => [], 'districts' => []];

            return;
        }

        self::$data = [
            'cities' => is_array($decoded['cities'] ?? null) ? $decoded['cities'] : [],
            'districts' => is_array($decoded['districts'] ?? null) ? $decoded['districts'] : [],
        ];
    }

    /**
     * @return array{lat: float, lng: float, source?: string, verified_at?: string}|null
     */
    public function getCityCoordinate(string $citySlug): ?array
    {
        self::initialize();
        $entry = self::$data['cities'][trim($citySlug)] ?? null;

        return $this->normalizeCoordinate($entry);
    }

    /**
     * @return array{lat: float, lng: float, source?: string, verified_at?: string}|null
     */
    public function getDistrictCoordinate(string $citySlug, string $districtSlug): ?array
    {
        self::initialize();
        $key = trim($citySlug).'/'.trim($districtSlug);
        $entry = self::$data['districts'][$key] ?? null;

        return $this->normalizeCoordinate($entry);
    }

    private function normalizeCoordinate(mixed $entry): ?array
    {
        if (! is_array($entry)) {
            return null;
        }

        $lat = $entry['lat'] ?? null;
        $lng = $entry['lng'] ?? null;

        if (! is_numeric($lat) || ! is_numeric($lng)) {
            return null;
        }

        $lat = (float) $lat;
        $lng = (float) $lng;

        if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
            return null;
        }

        return [
            'lat' => $lat,
            'lng' => $lng,
            'source' => isset($entry['source']) ? (string) $entry['source'] : null,
            'verified_at' => isset($entry['verified_at']) ? (string) $entry['verified_at'] : null,
        ];
    }
}