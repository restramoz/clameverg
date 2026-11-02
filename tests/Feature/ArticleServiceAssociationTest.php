<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleServiceAssociationTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): static
    {
        return $this->actingAs(User::factory()->create(['is_admin' => true]));
    }

    private function staleArticle(array $overrides = []): Article
    {
        // 'removed-service' never exists in the shipped catalog: a stale
        // association left behind by a past catalog revision.
        return Article::create(array_merge([
            'title' => 'Artikel Asosiasi Usang',
            'slug' => 'artikel-asosiasi-usang',
            'excerpt' => 'Ringkasan.',
            'content' => '<p>Isi artikel.</p>',
            'status' => 'draft',
            'service_slug' => 'removed-service',
        ], $overrides));
    }

    private function updatePayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Artikel Asosiasi Usang',
            'slug' => 'artikel-asosiasi-usang',
            'content' => '<p>Isi artikel.</p>',
            'status' => 'draft',
        ], $overrides);
    }

    public function test_unrelated_update_preserves_stale_service_slug(): void
    {
        $article = $this->staleArticle();

        $this->actingAsAdmin()
            ->put(route('admin.articles.update', $article->id), $this->updatePayload([
                'title' => 'Judul Berbeda Tanpa Menyentuh Layanan',
                'slug' => 'artikel-asosiasi-usang',
            ]))
            ->assertRedirect(route('admin.articles.index'))
            ->assertSessionHas('success');

        $this->assertSame('removed-service', $article->fresh()->service_slug);
        $this->assertSame('Judul Berbeda Tanpa Menyentuh Layanan', $article->fresh()->title);
    }

    public function test_explicit_unchanged_stale_service_slug_survives_update(): void
    {
        $article = $this->staleArticle();

        $this->actingAsAdmin()
            ->put(route('admin.articles.update', $article->id), $this->updatePayload([
                'service_slug' => 'removed-service',
            ]))
            ->assertRedirect(route('admin.articles.index'))
            ->assertSessionHas('success');

        $this->assertSame('removed-service', $article->fresh()->service_slug);
    }

    public function test_new_unknown_service_slug_is_rejected_without_saving(): void
    {
        $article = $this->staleArticle();

        $this->actingAsAdmin()
            ->put(route('admin.articles.update', $article->id), $this->updatePayload([
                'service_slug' => 'never-existed-anywhere',
            ]))
            ->assertSessionHasErrors('service_slug');

        $this->assertSame('removed-service', $article->fresh()->service_slug);
    }

    public function test_new_selection_from_current_catalog_is_accepted(): void
    {
        $article = $this->staleArticle();
        $catalog = app(\App\Services\SchemeService::class)->all();
        $slug = array_key_first($catalog);

        $this->actingAsAdmin()
            ->put(route('admin.articles.update', $article->id), $this->updatePayload([
                'service_slug' => $slug,
            ]))
            ->assertSessionHas('success');

        $this->assertSame($slug, $article->fresh()->service_slug);
    }

    public function test_explicit_empty_selection_clears_stale_association(): void
    {
        $article = $this->staleArticle();

        $this->actingAsAdmin()
            ->put(route('admin.articles.update', $article->id), $this->updatePayload([
                'service_slug' => '',
            ]))
            ->assertSessionHas('success');

        $this->assertNull($article->fresh()->service_slug);
    }

    public function test_edit_form_displays_stale_association_clearly(): void
    {
        $article = $this->staleArticle();

        $this->actingAsAdmin()
            ->get(route('admin.articles.edit', $article->id))
            ->assertOk()
            ->assertSee('removed-service (tidak lagi ada di katalog)', false)
            ->assertSee('tidak ditemukan di katalog saat ini', false);
    }

    public function test_regional_service_page_does_not_list_unrelated_articles(): void
    {
        // Stale/other-service article pointing at the same city must not leak
        // into a different service's regional page.
        $this->staleArticle([
            'title' => 'Artikel Layanan Umum',
            'slug' => 'artikel-layanan-umum',
            'status' => 'published',
            'published_at' => now(),
            'target_city_slug' => 'kota-malang',
        ]);
        $relevant = $this->staleArticle([
            'title' => 'Artikel Brevet AB Malang',
            'slug' => 'artikel-brevet-ab-malang',
            'status' => 'published',
            'published_at' => now(),
            'service_slug' => 'brevet-ab',
            'target_city_slug' => 'kota-malang',
        ]);

        $this->get('/kota-malang/brevet-ab')
            ->assertOk()
            ->assertSee($relevant->title, false)
            ->assertDontSee('Artikel Layanan Umum');
    }

    public function test_store_rejects_unknown_service_slug_for_new_articles(): void
    {
        $this->actingAsAdmin()
            ->post(route('admin.articles.store'), $this->updatePayload([
                'service_slug' => 'removed-service',
                '_token' => csrf_token(),
            ]))
            ->assertSessionHasErrors('service_slug');

        $this->assertDatabaseMissing('articles', ['title' => 'Artikel Asosiasi Usang']);
    }
}
