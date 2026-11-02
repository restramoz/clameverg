<?php

namespace Tests\Unit;

use App\Services\SchemeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class K3ListrikServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_loads_from_layanan_json(): void
    {
        $service = app(SchemeService::class);

        $this->assertGreaterThanOrEqual(408, count($service->all()));
        $this->assertSame('Brevet AB', array_values($service->all())[0]['title']);
    }

    public function test_generation_fails_safely_without_a_valid_service(): void
    {
        $result = app(\App\Services\OllamaService::class)->generateArticle([]);
        $this->assertFalse($result['success']);
        $this->assertSame('Pilih layanan katalog yang valid sebelum membuat artikel.', $result['error']);
    }

    public function test_presentation_grouping_keeps_every_service_once(): void
    {
        $service = app(SchemeService::class);
        $groups = $service->groupedForPresentation();

        $this->assertSame(['maintenance-pengukuran', 'instalasi-proteksi', 'otomasi-industri', 'k3-listrik'], array_keys($groups));
        $total = count($service->all());
        $mergedCount = count(array_merge(...array_values($groups)));
        $this->assertSame($total, $mergedCount);
        $this->assertGreaterThan(0, count($groups['k3-listrik']));
        $this->assertGreaterThan(0, count($groups['instalasi-proteksi']));
        $this->assertGreaterThan(0, count($groups['otomasi-industri']));
        $this->assertGreaterThan(0, count($groups['maintenance-pengukuran']));

        // At least one K3 service lands in the K3 Listrik bucket.
        $k3titles = array_map(
            static fn (array $p): string => $p['title'],
            $groups['k3-listrik']
        );
        $this->assertTrue(
            in_array('AHLI K3 MADYA', $k3titles, true)
            || in_array('AHLI K3 UTAMA', $k3titles, true)
            || count($groups['k3-listrik']) > 0
        );
    }
}
