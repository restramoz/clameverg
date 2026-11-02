<?php

namespace Tests\Feature;

use App\Services\SchemeService;
use Tests\TestCase;

class LegacyServiceRedirectTest extends TestCase
{
    public function test_legacy_program_slugs_permanently_redirect_to_layanan(): void
    {
        $slug = array_key_first(app(SchemeService::class)->all());

        // Query parameters are dropped: none of the legacy parameters is
        // meaningful for the canonical page.
        $this->get('/program/'.$slug.'?utm_source=old-campaign&utm_medium=email')
            ->assertStatus(301)
            ->assertRedirect('/layanan/'.$slug);
    }

    public function test_unknown_legacy_slug_returns_404_like_canonical_route(): void
    {
        $this->get('/program/not-a-real-service')->assertNotFound();
        $this->get('/layanan/not-a-real-service')->assertNotFound();
    }

    public function test_canonical_layanan_route_renders_without_redirect(): void
    {
        $slug = array_key_first(app(SchemeService::class)->all());

        $this->get('/layanan/'.$slug)->assertOk();
    }

    public function test_no_redirect_loop(): void
    {
        $slug = array_key_first(app(SchemeService::class)->all());

        // Reaching /layanan/{slug} must not bounce back to /program/{slug};
        // the followed request renders the canonical page directly.
        $this->followingRedirects()->get('/program/'.$slug)
            ->assertOk()
            ->assertSee('<link rel="canonical" href="https://devtyeng.my.id/layanan/'.$slug.'">', false);
    }
}
