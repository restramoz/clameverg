<?php

namespace Tests\Feature;

use App\Services\SchemeService;
use Tests\TestCase;

class CityProgramDetailTest extends TestCase
{
    public function test_old_city_service_urls_redirect_to_the_shared_regional_template(): void
    {
        $slug = array_key_first(app(SchemeService::class)->all());
        $canonical = route('region.program.detail', ['region' => 'kota-bandung', 'program' => $slug]);

        $this->get(route('city.program.detail', ['city' => 'bandung', 'program' => $slug]))
            ->assertRedirect($canonical, 301);
        $this->get(route('city.program.legacy', ['city' => 'bandung', 'program' => $slug]))
            ->assertRedirect($canonical, 301);

        $this->get($canonical)->assertOk();
    }

    public function test_invalid_city_and_service_slugs_return_404(): void
    {
        $this->get('/kota-tidak-ada/brevet-ab')->assertNotFound();
        $this->get('/kota-bandung/not-a-service')->assertNotFound();
    }
}
