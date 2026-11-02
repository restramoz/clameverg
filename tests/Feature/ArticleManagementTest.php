<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use App\Services\SchemeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArticleManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function actingAsAdmin(): static
    {
        return $this->actingAs($this->admin());
    }

    // ─── Index ────────────────────────────────────────────────────────────────

    public function test_admin_article_index_loads(): void
    {
        $this->actingAsAdmin()
            ->get(route('admin.articles.index'))
            ->assertOk()
            ->assertSee('Artikel');
    }

    public function test_admin_article_index_shows_all_articles(): void
    {
        $published = Article::create([
            'title' => 'Artikel Published', 'slug' => 'artikel-published',
            'content' => '<p>Isi.</p>', 'status' => 'published', 'published_at' => now(),
        ]);
        $draft = Article::create([
            'title' => 'Artikel Draft', 'slug' => 'artikel-draft',
            'content' => '<p>Isi.</p>', 'status' => 'draft',
        ]);

        $response = $this->actingAsAdmin()
            ->get(route('admin.articles.index'));

        $response->assertOk()
            ->assertSee($published->title)
            ->assertSee($draft->title);
    }

    // ─── Create ───────────────────────────────────────────────────────────────

    public function test_admin_create_form_loads(): void
    {
        $this->actingAsAdmin()
            ->get(route('admin.articles.create'))
            ->assertOk()
            ->assertSee('Tambah Artikel');
    }

    public function test_admin_can_store_draft_article(): void
    {
        $this->actingAsAdmin()
            ->post(route('admin.articles.store'), [
                'title'   => 'Panduan K3 Listrik',
                'content' => '<p>Panduan pelatihan K3 Listrik.</p>',
                'status'  => 'draft',
            ])
            ->assertRedirect(route('admin.articles.index'));

        $this->assertDatabaseHas('articles', [
            'title'  => 'Panduan K3 Listrik',
            'status' => 'draft',
        ]);
    }

    public function test_admin_can_store_published_article(): void
    {
        $this->actingAsAdmin()
            ->post(route('admin.articles.store'), [
                'title'   => 'Prosedur Kerja Aman Listrik',
                'content' => '<p>Prosedur kerja aman instalasi listrik.</p>',
                'status'  => 'published',
            ])
            ->assertRedirect(route('admin.articles.index'));

        $this->assertDatabaseHas('articles', [
            'title'  => 'Prosedur Kerja Aman Listrik',
            'status' => 'published',
        ]);

        $article = Article::where('slug', 'prosedur-kerja-aman-listrik')->first();
        $this->assertNotNull($article->published_at);
    }

    public function test_admin_can_store_and_update_the_selected_service(): void
    {
        $serviceSlug = array_key_first(app(SchemeService::class)->all());
        $this->actingAsAdmin()->post(route('admin.articles.store'), [
            'title' => 'Artikel layanan',
            'content' => '<p>Konten umum.</p>',
            'status' => 'draft',
            'service_slug' => $serviceSlug,
        ])->assertRedirect(route('admin.articles.index'));

        $article = Article::where('slug', 'artikel-layanan')->firstOrFail();
        $this->assertSame($serviceSlug, $article->service_slug);

        $this->actingAsAdmin()->put(route('admin.articles.update', $article->id), [
            'title' => $article->title,
            'content' => '<p>Konten umum diperbarui.</p>',
            'status' => 'draft',
            'service_slug' => 'not-in-catalog',
        ])->assertSessionHasErrors('service_slug');
    }

    public function test_store_requires_title_and_content(): void
    {
        $this->actingAsAdmin()
            ->post(route('admin.articles.store'), [
                'title'   => '',
                'content' => '',
                'status'  => 'draft',
            ])
            ->assertSessionHasErrors(['title', 'content']);
    }

    public function test_store_auto_generates_slug_from_title(): void
    {
        $this->actingAsAdmin()
            ->post(route('admin.articles.store'), [
                'title'   => 'Rencana Keselamatan Listrik 2024',
                'content' => '<p>Konten RKK.</p>',
                'status'  => 'draft',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('articles', [
            'slug' => 'rencana-keselamatan-listrik-2024',
        ]);
    }

    public function test_store_prevents_duplicate_slug(): void
    {
        Article::create([
            'title' => 'K3 Muda', 'slug' => 'k3-muda',
            'content' => '<p>Isi.</p>', 'status' => 'draft',
        ]);

        $this->actingAsAdmin()
            ->post(route('admin.articles.store'), [
                'title'   => 'K3 Muda',
                'content' => '<p>Isi baru.</p>',
                'status'  => 'draft',
            ])
            ->assertRedirect(route('admin.articles.index'));

        $this->assertEquals(1, Article::where('slug', 'k3-muda')->count());
        $this->assertEquals(1, Article::where('slug', 'k3-muda-1')->count());
    }

    // ─── Edit ─────────────────────────────────────────────────────────────────

    public function test_admin_edit_form_loads(): void
    {
        $article = Article::create([
            'title' => 'Edit Me', 'slug' => 'edit-me',
            'content' => '<p>Isi lama.</p>', 'status' => 'draft',
        ]);

        $this->actingAsAdmin()
            ->get(route('admin.articles.edit', $article->id))
            ->assertOk()
            ->assertSee('Edit Me');
    }

    public function test_admin_edit_404_for_nonexistent(): void
    {
        $this->actingAsAdmin()
            ->get(route('admin.articles.edit', 99999))
            ->assertNotFound();
    }

    // ─── Update ───────────────────────────────────────────────────────────────

    public function test_admin_can_update_article(): void
    {
        $article = Article::create([
            'title' => 'Original Title', 'slug' => 'original-title',
            'content' => '<p>Konten lama.</p>', 'status' => 'draft',
        ]);

        $this->actingAsAdmin()
            ->put(route('admin.articles.update', $article->id), [
                'title'   => 'Updated Title K3',
                'slug'    => 'updated-title-k3',
                'content' => '<p>Konten baru yang lebih lengkap.</p>',
                'status'  => 'draft',
            ])
            ->assertRedirect(route('admin.articles.index'));

        $this->assertDatabaseHas('articles', [
            'id'    => $article->id,
            'title' => 'Updated Title K3',
        ]);
    }

    public function test_admin_can_publish_draft(): void
    {
        $article = Article::create([
            'title' => 'Draft Siap Publish', 'slug' => 'draft-siap-publish',
            'content' => '<p>Konten.</p>', 'status' => 'draft',
        ]);

        $this->actingAsAdmin()
            ->put(route('admin.articles.update', $article->id), [
                'title'   => $article->title,
                'content' => $article->content,
                'status'  => 'published',
            ])
            ->assertRedirect(route('admin.articles.index'));

        $article->refresh();
        $this->assertEquals('published', $article->status);
        $this->assertNotNull($article->published_at);
    }

    // ─── Delete ───────────────────────────────────────────────────────────────

    public function test_admin_can_delete_article(): void
    {
        $article = Article::create([
            'title' => 'To Be Deleted', 'slug' => 'to-be-deleted',
            'content' => '<p>.</p>', 'status' => 'draft',
        ]);

        $this->actingAsAdmin()
            ->delete(route('admin.articles.destroy', $article->id))
            ->assertRedirect(route('admin.articles.index'));

        $this->assertDatabaseMissing('articles', ['id' => $article->id]);
    }

    // ─── Guest Protection ─────────────────────────────────────────────────────

    public function test_guest_cannot_access_article_crud(): void
    {
        $article = Article::create([
            'title' => 'Guarded', 'slug' => 'guarded',
            'content' => '<p>.</p>', 'status' => 'draft',
        ]);

        $this->get(route('admin.articles.index'))->assertRedirect(route('login'));
        $this->get(route('admin.articles.create'))->assertRedirect(route('login'));
        $this->post(route('admin.articles.store'), [])->assertRedirect(route('login'));
        $this->get(route('admin.articles.edit', $article->id))->assertRedirect(route('login'));
        $this->put(route('admin.articles.update', $article->id), [])->assertRedirect(route('login'));
        $this->delete(route('admin.articles.destroy', $article->id))->assertRedirect(route('login'));
    }
}
