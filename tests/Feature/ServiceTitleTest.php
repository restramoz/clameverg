<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceTitleTest extends TestCase
{
    use RefreshDatabase;
    /**
     * Count case-insensitive occurrences of the brand inside the <title> tag.
     */
    private function brandCountInTitle(string $html): int
    {
        $this->assertMatchesRegularExpression('/<title>(.*?)<\/title>/s', $html);
        preg_match('/<title>(.*?)<\/title>/s', $html, $matches);

        return substr_count(mb_strtolower($matches[1]), 'devtyeng');
    }

    private function titleTag(string $html): string
    {
        preg_match('/<title>(.*?)<\/title>/s', $html, $matches);

        return trim(html_entity_decode($matches[1], ENT_QUOTES, 'UTF-8'));
    }

    public function test_generic_service_title_contains_brand_exactly_once(): void
    {
        $slug = array_key_first(app(\App\Services\SchemeService::class)->all());

        // meta_title already carries the brand ("Judul | Devtyeng"): the
        // layout must not append it a second time.
        $html = $this->get('/layanan/'.$slug)->assertOk()->getContent();

        $this->assertSame(1, $this->brandCountInTitle($html));
        $this->assertStringContainsString(' | ', $this->titleTag($html));
        $this->assertStringNotContainsString('— Devtyeng', $this->titleTag($html));
    }

    public function test_regional_service_title_keeps_regional_context_and_single_brand(): void
    {
        $html = $this->get('/kota-malang/brevet-ab')->assertOk()->getContent();
        $title = $this->titleTag($html);

        $this->assertSame(1, $this->brandCountInTitle($html));
        $this->assertStringContainsString('Kota Malang', $title);
        $this->assertStringEndsWith('— Devtyeng', $title);
    }

    public function test_og_and_twitter_titles_match_the_document_title(): void
    {
        $html = $this->get('/artikel')->assertOk()->getContent();
        $title = $this->titleTag($html);

        $this->assertMatchesRegularExpression('/<meta property="og:title" content="[^"]*Devtyeng[^"]*">/', $html);
        $this->assertMatchesRegularExpression('/<meta name="twitter:title" content="[^"]*Devtyeng[^"]*">/', $html);

        preg_match('/<meta property="og:title" content="(.*?)">/', $html, $og);
        preg_match('/<meta name="twitter:title" content="(.*?)">/', $html, $tw);

        $this->assertSame($title, html_entity_decode($og[1], ENT_QUOTES, 'UTF-8'));
        $this->assertSame($title, html_entity_decode($tw[1], ENT_QUOTES, 'UTF-8'));
    }
}
