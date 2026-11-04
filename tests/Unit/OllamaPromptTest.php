<?php

namespace Tests\Unit;

use App\Services\OllamaService;
use App\Services\SchemeService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OllamaPromptTest extends TestCase
{
    private function fakeSuccessfulOllama(): void
    {
        config([
            'services.ollama.base_url' => 'http://dummy-ollama.local',
            'services.ollama.model' => 'dummy-model',
        ]);

        Http::fake(['*' => Http::response([
            'message' => ['content' => json_encode([
                'title' => 'Panduan layanan',
                'slug' => 'panduan-layanan',
                'excerpt' => 'Ringkasan umum.',
                'content' => str_repeat('kata ', 7500),
                'seo_title' => 'Panduan layanan | Bevehard',
                'meta_description' => 'Informasi umum layanan.',
                'suggested_faqs' => [],
                'image_brief' => [],
            ])],
        ], 200)]);
    }

    public function test_service_is_required_and_missing_context_never_calls_ollama(): void
    {
        $this->fakeSuccessfulOllama();

        $result = (new OllamaService())->generateArticle(['topic' => 'Topik bebas']);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Pilih layanan katalog', $result['error']);
        Http::assertNothingSent();
    }

    public function test_selected_service_is_primary_and_prompt_contains_no_region_context(): void
    {
        $this->fakeSuccessfulOllama();
        $schemes = new SchemeService();
        $slug = array_key_first($schemes->all());
        $service = $schemes->find($slug);

        $result = (new OllamaService($schemes))->generateArticle([
            'service_slug' => $slug,
            'topic' => 'Panduan umum layanan terpilih',
            'city_name' => 'Kota Bandung',
            'district_name' => 'Coblong',
            'word_count' => 7500,
        ]);

        $this->assertTrue($result['success']);
        $this->assertSame($slug, $result['service_slug']);
        Http::assertSent(function (object $request) use ($service): bool {
            $system = $request['messages'][0]['content'];
            $user = $request['messages'][1]['content'];

            return str_contains($system, 'Judul layanan persis dari katalog Bevehard: '.$service['title'])
                && str_contains($system, 'Jangan menyebut provinsi, kota, kabupaten, kecamatan')
                && str_contains($user, $service['title'])
                && ! str_contains($user, 'Bandung')
                && ! str_contains($user, 'Coblong');
        });
    }

    public function test_invalid_service_fails_before_api_request(): void
    {
        $this->fakeSuccessfulOllama();

        $result = (new OllamaService())->generateArticle(['service_slug' => 'not-in-catalog']);

        $this->assertFalse($result['success']);
        Http::assertNothingSent();
    }

    public function test_prompt_preserves_claim_and_content_safeguards(): void
    {
        $this->fakeSuccessfulOllama();
        $slug = array_key_first(app(SchemeService::class)->all());

        (new OllamaService())->generateArticle(['service_slug' => $slug]);

        Http::assertSent(function (object $request): bool {
            $system = $request['messages'][0]['content'];

            return str_contains($system, 'otoritas sertifikasi resmi')
                && str_contains($system, 'lembaga pemerintah')
                && str_contains($system, 'lembaga terakreditasi')
                && str_contains($system, 'pemegang lisensi')
                && str_contains($system, 'mitra institusi')
                && str_contains($system, 'Jangan menjanjikan atau menjamin kelulusan');
        });
    }
}
