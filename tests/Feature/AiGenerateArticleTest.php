<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\OllamaService;
use App\Services\SchemeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiGenerateArticleTest extends TestCase
{
    use RefreshDatabase;

    private function serviceSlug(): string
    {
        return (string) array_key_first(app(SchemeService::class)->all());
    }

    private function bindFakeOllama(array $response): void
    {
        $mock = $this->createMock(OllamaService::class);
        $mock->method('generateArticle')->willReturn($response);
        $this->app->instance(OllamaService::class, $mock);
    }

    public function test_word_count_is_validated(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        foreach ([5000, 9001] as $words) {
            $this->actingAs($admin)->postJson(route('admin.articles.ai-generate'), [
                'service_slug' => $this->serviceSlug(),
                'word_count' => $words,
            ])->assertUnprocessable();
        }
    }

    public function test_service_context_is_required_and_catalog_validated(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->postJson(route('admin.articles.ai-generate'), [
            'word_count' => 7500,
        ])->assertUnprocessable()->assertJsonValidationErrors('service_slug');

        $this->actingAs($admin)->postJson(route('admin.articles.ai-generate'), [
            'service_slug' => 'not-in-catalog',
            'word_count' => 7500,
        ])->assertUnprocessable()->assertJsonValidationErrors('service_slug');
    }

    public function test_valid_service_is_passed_to_synchronous_generation(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $slug = $this->serviceSlug();
        $mock = $this->createMock(OllamaService::class);
        $mock->expects($this->once())
            ->method('generateArticle')
            ->with($this->callback(fn (array $params): bool => $params['service_slug'] === $slug && ! isset($params['city_name'])))
            ->willReturn([
                'success' => true,
                'service_slug' => $slug,
                'title' => 'Panduan layanan umum',
                'slug' => 'panduan-layanan-umum',
                'excerpt' => 'Ringkasan umum.',
                'content' => '<p>Konten generik.</p>',
                'seo_title' => 'Panduan layanan | Clameverg',
                'meta_description' => 'Informasi umum.',
                'suggested_faqs' => [],
                'image_brief' => [],
            ]);
        $this->app->instance(OllamaService::class, $mock);

        $this->actingAs($admin)->postJson(route('admin.articles.ai-generate'), [
            'service_slug' => $slug,
            'topic' => 'Panduan umum layanan',
            'word_count' => 7500,
        ])->assertOk()
            ->assertJsonStructure(['success', 'service_slug', 'title', 'slug', 'content'])
            ->assertJson(['success' => true, 'service_slug' => $slug]);
    }

    public function test_generation_failure_returns_clear_error(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->bindFakeOllama(['success' => false, 'error' => 'Koneksi ke Ollama gagal.']);

        $this->actingAs($admin)->postJson(route('admin.articles.ai-generate'), [
            'service_slug' => $this->serviceSlug(),
            'word_count' => 7500,
        ])->assertStatus(500)->assertJson(['success' => false, 'error' => 'Koneksi ke Ollama gagal.']);
    }

    public function test_generation_requires_admin_authentication(): void
    {
        $this->postJson(route('admin.articles.ai-generate'), [
            'service_slug' => $this->serviceSlug(), 'word_count' => 7500,
        ])->assertUnauthorized();

        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user)->postJson(route('admin.articles.ai-generate'), [
            'service_slug' => $this->serviceSlug(), 'word_count' => 7500,
        ])->assertForbidden();
    }
}
