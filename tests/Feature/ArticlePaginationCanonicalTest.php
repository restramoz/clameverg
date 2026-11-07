<?php

namespace Tests\Feature;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticlePaginationCanonicalTest extends TestCase
{
    use RefreshDatabase;

    private function createArticles(int $count): void
    {
        for ($i = 1; $i <= $count; $i++) {
            Article::create([
                'title' => 'Artikel Pengujian '.$i,
                'slug' => 'artikel-pengujian-'.$i,
                'excerpt' => 'Ringkasan artikel pengujian '.$i.'.',
                'content' => '<p>Konten artikel pengujian nomor '.$i.'.</p>',
                'status' => 'published',
                'published_at' => now()->subDay(),
            ]);
        }
    }

    public function test_first_page_canonical_is_the_clean_url(): void
    {
        $this->createArticles(13);

        $this->get('/artikel')
            ->assertOk()
            ->assertSee('<link rel="canonical" href="https://clameverg.my.id/artikel">', false);
    }

    public function test_second_page_canonical_is_self_referencing(): void
    {
        $this->createArticles(13);

        $this->get('/artikel?page=2')
            ->assertOk()
            ->assertSee('<link rel="canonical" href="https://clameverg.my.id/artikel?page=2">', false)
            // OG url must match the canonical.
            ->assertSee('<meta property="og:url" content="https://clameverg.my.id/artikel?page=2">', false);
    }

    public function test_later_valid_page_keeps_its_own_canonical(): void
    {
        $this->createArticles(25);

        $this->get('/artikel?page=3')
            ->assertOk()
            ->assertSee('<link rel="canonical" href="https://clameverg.my.id/artikel?page=3">', false);
    }

    public function test_invalid_or_out_of_range_pages_fall_back_to_clean_canonical(): void
    {
        $this->createArticles(25);

        $this->get('/artikel?page=abc')
            ->assertOk()
            ->assertSee('<link rel="canonical" href="https://clameverg.my.id/artikel">', false);

        $this->get('/artikel?page=999')
            ->assertOk()
            ->assertSee('<link rel="canonical" href="https://clameverg.my.id/artikel">', false);
    }

    public function test_arbitrary_query_parameters_are_never_appended_to_canonical(): void
    {
        $this->createArticles(13);

        $this->get('/artikel?page=2&foo=bar&utm_source=campaign')
            ->assertOk()
            ->assertSee('<link rel="canonical" href="https://clameverg.my.id/artikel?page=2">', false);
    }

    public function test_article_detail_canonical_is_unchanged(): void
    {
        $this->createArticles(1);

        $this->get('/artikel/artikel-pengujian-1?foo=bar')
            ->assertOk()
            ->assertSee('<link rel="canonical" href="https://clameverg.my.id/artikel/artikel-pengujian-1">', false);
    }
}
