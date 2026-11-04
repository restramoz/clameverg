<?php

namespace Tests\Feature;

use App\Services\RegionalService;
use App\Services\SchemeService;
use Tests\TestCase;

class RegionalPagesTest extends TestCase
{
    public function test_dataset_keeps_provinces_and_cities_only(): void
    {
        $regional = app(RegionalService::class);
        $this->assertCount(38, $regional->getAllProvinces());
        $this->assertCount(514, $regional->getAllCities());
    }

    public function test_province_and_city_landings_render_without_public_district_links(): void
    {
        $province = $this->get(route('regional.province', 'jawa-barat'))->assertOk();
        $province->assertSee('Provinsi Jawa Barat');
        $province->assertDontSee('/kecamatan/', false);

        $city = $this->get(route('regional.city', 'bandung'))->assertOk();
        $city->assertSee('Kota Bandung')
            ->assertSee(route('regional.province', 'jawa-barat'), false)
            ->assertDontSee('Kecamatan di Kota Bandung')
            ->assertDontSee('/kecamatan/', false)
            ->assertDontSee('Coblong');
    }

    public function test_city_and_regency_service_urls_use_generic_service_content_and_contextual_metadata(): void
    {
        $program = array_values(app(SchemeService::class)->all())[0];
        $serviceUrl = '/kota-malang/'.$program['slug'];
        $response = $this->get($serviceUrl)->assertOk();
        $response->assertSee($program['title'].' Kota Malang', false)
            ->assertSee('Informasi '.$program['title'].' untuk Kota Malang. Uraian layanan berikut tetap bersifat umum.')
            ->assertSee(e($program['introduction']), false)
            ->assertSee('<link rel="canonical" href="https://bevehard.my.id'.$serviceUrl.'">', false)
            ->assertSee('Kota Malang', false);

        $regencyUrl = '/kabupaten-malang/'.$program['slug'];
        $this->get($regencyUrl)->assertOk()
            ->assertSee($program['title'].' Kabupaten Malang', false)
            ->assertSee('<link rel="canonical" href="https://bevehard.my.id'.$regencyUrl.'">', false)
            ->assertSee(e($program['introduction']), false);
    }

    public function test_province_service_url_uses_the_same_catalog_body(): void
    {
        $program = array_values(app(SchemeService::class)->all())[0];
        $url = '/provinsi-jawa-timur/'.$program['slug'];
        $this->get($url)->assertOk()
            ->assertSee($program['title'].' Provinsi Jawa Timur', false)
            ->assertSee('<link rel="canonical" href="https://bevehard.my.id'.$url.'">', false)
            ->assertSee(e($program['introduction']), false);
    }

    public function test_invalid_region_and_service_slugs_return_404(): void
    {
        $this->get('/provinsi-tidak-ada/brevet-ab')->assertNotFound();
        $this->get('/kota-malang/not-a-service')->assertNotFound();
        $this->get('/kabupaten-tidak-ada/brevet-ab')->assertNotFound();
        $this->get(route('regional.province', 'tidak-ada'))->assertNotFound();
        $this->get(route('regional.city', 'tidak-ada'))->assertNotFound();
    }

    public function test_district_pages_and_legacy_district_urls_are_not_public(): void
    {
        foreach ([
            '/kecamatan/bandung/coblong',
            '/kecamatan-coblong',
            '/kota-bandung/kecamatan-coblong',
            '/pelatihan/kecamatan-coblong',
        ] as $path) {
            $this->get($path)->assertNotFound();
        }
    }

    public function test_sitemap_contains_only_supported_regional_levels(): void
    {
        $response = $this->get(route('sitemap'))->assertOk();
        $response->assertSee('https://bevehard.my.id/provinsi/jawa-barat', false)
            ->assertSee('https://bevehard.my.id/kota/bandung', false)
            ->assertDontSee('/kecamatan/', false)
            ->assertDontSee('coblong', false);
    }

    public function test_city_alias_and_legacy_service_paths_redirect_to_canonical_targets(): void
    {
        $this->get(route('regional.city', 'kota-bandung'))
            ->assertRedirect(route('regional.city', 'bandung'), 301);

        $slug = array_key_first(app(SchemeService::class)->all());
        $target = route('region.program.detail', ['region' => 'kota-bandung', 'program' => $slug]);
        $this->get(route('city.program.detail', ['city' => 'bandung', 'program' => $slug]))
            ->assertRedirect($target, 301);
    }
}
