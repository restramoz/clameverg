<?php

namespace Tests\Feature;

use App\Services\SchemeService;
use Tests\TestCase;

class ProgramPagesTest extends TestCase
{
    private function catalog(): array
    {
        return app(SchemeService::class)->all();
    }

    public function test_catalog_service_pages_render_from_existing_slugs(): void
    {
        $catalog = $this->catalog();
        $this->assertGreaterThanOrEqual(408, count($catalog));

        foreach (array_slice($catalog, 0, 5, true) as $slug => $program) {
            $this->get(route('program.show', $slug))
                ->assertOk()
                ->assertSee($program['title'])
                ->assertSee('Mengenal Lebih Dekat '.$program['title']);
        }
    }

    public function test_catalog_and_legacy_service_paths_resolve(): void
    {
        $slug = array_key_first($this->catalog());
        $this->assertSame('/layanan/'.$slug, parse_url(route('program.show', $slug), PHP_URL_PATH));
        $this->assertSame('/program/'.$slug, parse_url(route('program.legacy.show', $slug), PHP_URL_PATH));
        // /program/{slug} is legacy: it must permanently redirect to /layanan/{slug}.
        $this->get('/program/'.$slug)
            ->assertStatus(301)
            ->assertRedirect('/layanan/'.$slug);
        $this->get('/layanan/not-a-real-service')->assertNotFound();
    }

    public function test_service_page_has_canonical_and_accessible_breadcrumbs(): void
    {
        $slug = array_key_first($this->catalog());
        $this->get(route('program.show', $slug))->assertOk()
            ->assertSee('<link rel="canonical" href="https://clameverg.my.id/layanan/'.$slug.'">', false)
            ->assertSee('aria-label="Breadcrumb"', false)
            ->assertSee('itemtype="https://schema.org/BreadcrumbList"', false)
            ->assertSee('aria-current="page"', false);
    }

    public function test_service_pages_show_disclosures_and_faqs_without_guarantees(): void
    {
        $program = array_values($this->catalog())[0];
        $response = $this->get(route('program.show', $program['slug']))->assertOk();
        $response->assertSee('Informasi Penyelenggaraan &amp; Pelaksanaan', false)
            ->assertSee('<details', false)
            ->assertSee('<summary', false)
            ->assertSee('Pertanyaan Umum Seputar')
            ->assertDontSee('jaminan lulus 100%')
            ->assertDontSee('Sertifikat dijamin terbit');
    }

    public function test_whatsapp_cta_uses_configured_number_and_service_message(): void
    {
        config(['services.whatsapp.phone' => '089988776655']);
        $program = array_values($this->catalog())[0];
        $this->get(route('program.show', $program['slug']))->assertOk()
            ->assertSee('https://wa.me/6289988776655?text='.rawurlencode($program['wa_message']), false);
    }

    public function test_related_service_links_stay_in_the_catalog(): void
    {
        $catalog = $this->catalog();
        $slugs = array_keys($catalog);
        $response = $this->get(route('program.show', $slugs[0]))->assertOk();

        foreach (array_slice($slugs, 1, 3) as $slug) {
            $response->assertSee(route('program.show', $slug), false);
        }
    }
}
