<?php

namespace Tests\Unit;

use App\Services\SchemeService;
use ReflectionMethod;
use Tests\TestCase;

class SchemeServiceTest extends TestCase
{
    protected SchemeService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(SchemeService::class);
    }

    /**
     * Load a scheme catalog from an alternate path without touching the real file.
     */
    protected function loadFixture(string $json): array
    {
        $path = tempnam(sys_get_temp_dir(), 'schemes_test_');
        file_put_contents($path, $json);

        $method = new ReflectionMethod(SchemeService::class, 'load');
        $method->setAccessible(true);

        return $method->invoke($this->service, $path);
    }

    public function test_catalog_loads_all_services_from_layanan_json(): void
    {
        $decoded = json_decode(file_get_contents(base_path('layanan.json')), true);
        $this->assertCount(408, $decoded);

        // The protected 408 legacy services remain in the unified catalog.
        $this->assertSame(408, $this->service->sources()['layanan']['count']);
        foreach ($decoded as $name) {
            $this->assertNotNull($this->service->find(trim((string) $name)), "Missing legacy service: {$name}");
        }
    }

    public function test_catalog_has_no_competency_units_from_layanan_json(): void
    {
        $total = array_sum(array_map(
            static fn (array $program): int => count($program['units']),
            $this->service->all()
        ));

        // layanan.json services carry no competency units.
        $this->assertSame(0, $total);
    }

    public function test_source_metadata_is_reachable(): void
    {
        $sources = $this->service->sources();

        $this->assertArrayHasKey('layanan', $sources);
        $this->assertSame('Layanan', $sources['layanan']['title']);
        $this->assertSame('layanan.json', $sources['layanan']['file']);
        $this->assertGreaterThan(0, $sources['layanan']['count']);
    }

    public function test_find_returns_scheme_by_derived_slug(): void
    {
        $slug = array_key_first($this->service->all());
        $scheme = $this->service->find($slug);

        $this->assertNotNull($scheme);
        $this->assertSame('Brevet AB', $scheme['title']);
        $this->assertSame('layanan', $scheme['source']);
    }

    public function test_find_is_case_and_spacing_tolerant(): void
    {
        $first = array_values($this->service->all())[0];
        $this->assertNotNull($this->service->find($first['title']));
    }

    public function test_unknown_slug_returns_null(): void
    {
        $this->assertNull($this->service->find('training-ahli-k3-listrik'));
    }

    public function test_slug_collisions_get_deterministic_suffix(): void
    {
        $fixture = json_encode([
            'title' => 'fixture',
            'schemes' => [
                ['name' => 'Teknisi Pelindian', 'lsp' => null, 'units' => []],
                ['name' => 'Teknisi Pelindian', 'lsp' => null, 'units' => []],
                ['name' => 'Teknisi Pelindian', 'lsp' => null, 'units' => []],
            ],
        ]);

        $programs = $this->loadFixture($fixture);

        $this->assertArrayHasKey('teknisi-pelindian', $programs);
        $this->assertArrayHasKey('teknisi-pelindian-2', $programs);
        $this->assertArrayHasKey('teknisi-pelindian-3', $programs);
    }

    public function test_excluded_services_are_not_in_catalog(): void
    {
        $titles = array_map(
            static fn (array $program): string => $program['title'],
            $this->service->all()
        );

        $this->assertNotContains('Ahli K3 Umum', $titles);
        $this->assertNotContains('Vaksin COVID-19', $titles);
        $this->assertNotContains('Obat Tradisional', $titles);
    }

    public function test_every_program_matches_required_view_shape(): void
    {
        foreach ($this->service->all() as $slug => $program) {
            foreach ([
                'slug', 'category', 'category_label', 'level', 'title', 'badge',
                'meta_title', 'meta_description', 'hero_heading', 'hero_highlight',
                'hero_subheading', 'wa_message', 'introduction', 'target_audience',
                'roles_and_responsibilities', 'curriculum_topics', 'training_info', 'faqs',
            ] as $key) {
                $this->assertArrayHasKey($key, $program, "Scheme {$slug} is missing [{$key}].");
            }

            $this->assertSame($slug, $program['slug']);
            $this->assertSame(
                ['jadwal', 'durasi', 'investasi', 'persyaratan', 'otorisasi'],
                array_keys($program['training_info'])
            );
        }
    }

    public function test_missing_source_file_throws(): void
    {
        $this->expectException(\RuntimeException::class);

        $method = new ReflectionMethod(SchemeService::class, 'load');
        $method->setAccessible(true);
        $method->invoke($this->service, base_path('definitely-missing-schemes.json'));
    }

    public function test_invalid_json_throws(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->loadFixture('{"title": "broken"');
    }

    public function test_unexpected_structure_throws(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->loadFixture(json_encode(['title' => 'Agribisnis Ambisisi']));
    }

    public function test_scheme_without_name_throws(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->loadFixture(json_encode(['schemes' => [['lsp' => null, 'units' => []]]]));
    }
}
