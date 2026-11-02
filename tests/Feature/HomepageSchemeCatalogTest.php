<?php

namespace Tests\Feature;

use App\Services\SchemeService;
use Tests\TestCase;

class HomepageSchemeCatalogTest extends TestCase
{
    public function test_homepage_renders_the_original_408_services_and_links_to_details(): void
    {
        $catalog = app(SchemeService::class)->all();
        $this->assertCount(408, $catalog);

        $response = $this->get('/');
        $response->assertOk()
            ->assertSee(array_values($catalog)[0]['title'])
            ->assertSee(array_values($catalog)[407]['title'])
            ->assertSee(route('program.show', array_key_first($catalog)), false)
            ->assertSee('js-show-all', false);
    }

    public function test_service_cards_resolve_and_service_content_is_catalog_driven(): void
    {
        $first = array_values(app(SchemeService::class)->all())[0];
        $this->get(route('program.show', $first['slug']))->assertOk()->assertSee($first['title']);
        $this->get('/layanan/does-not-exist')->assertNotFound();
    }

    public function test_static_home_assets_exist(): void
    {
        foreach (['hero.webp', 'agribisnis.webp', 'lsp.webp', 'training.webp', 'consult.webp'] as $file) {
            $this->assertFileExists(public_path('images/home/'.$file));
        }
    }
}
