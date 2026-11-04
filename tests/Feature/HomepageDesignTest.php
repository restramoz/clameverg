<?php

namespace Tests\Feature;

use App\Services\SchemeService;
use Tests\TestCase;

class HomepageDesignTest extends TestCase
{
    public function test_homepage_renders_devtyeng_and_catalog_content(): void
    {
        $response = $this->get('/');
        $response->assertOk()->assertSee('Bevehard');
        $response->assertSee(array_values(app(SchemeService::class)->all())[0]['title']);
        $response->assertDontSee('Certyang');
    }

    public function test_homepage_has_accessible_landmarks_and_skip_link(): void
    {
        $response = $this->get('/');
        $response->assertOk()
            ->assertSee('href="#main-content"', false)
            ->assertSee('id="main-content"', false)
            ->assertSee('<header', false)
            ->assertSee('<footer', false)
            ->assertSee('aria-label="Navigasi utama"', false)
            ->assertSee('focus-visible:ring', false);
    }

    public function test_homepage_canonical_uses_production_domain(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('<link rel="canonical" href="https://bevehard.my.id/">', false)
            ->assertSee('property="og:url" content="https://bevehard.my.id/"', false);
    }

    public function test_whatsapp_cta_uses_configuration_and_falls_back_without_a_number(): void
    {
        config(['services.whatsapp.phone' => '081234567890']);
        $this->get('/')->assertOk()->assertSee('https://wa.me/6281234567890', false);

        config(['services.whatsapp.phone' => '']);
        $this->get('/')->assertOk()
            ->assertDontSee('https://wa.me/62', false)
            ->assertSee('Hubungi admin untuk informasi konsultasi');
    }
}
