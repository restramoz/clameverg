<?php

namespace Tests\Unit;

use App\Services\OllamaService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OllamaServiceTest extends TestCase
{
    private function baseConfig(): void
    {
        config([
            'services.ollama.base_url' => 'http://dummy-ollama.local',
            'services.ollama.model' => 'dummy-model',
            'services.whatsapp.phone' => '081234567890',
        ]);
    }

    public function test_successful_ollama_response_is_parsed(): void
    {
        $this->baseConfig();

        $content = str_repeat('kata ', 7500);
        $body = [
            'message' => [
                'content' => json_encode([
                    'title' => 'Judul Test',
                    'slug' => 'judul-test',
                    'excerpt' => 'Excerpt test',
                    'content' => $content,
                    'seo_title' => 'SEO Title Test',
                    'meta_description' => 'Meta Description Test',
                    'suggested_faqs' => [['q' => 'Q1', 'a' => 'A1']],
                    'image_brief' => [
                        'concept' => 'Konsep',
                        'english_prompt' => 'Prompt',
                        'aspect_ratio' => '16:9',
                        'recommended_alt' => 'Alt',
                    ],
                ]),
            ],
        ];

        Http::fake(['*' => Http::response($body, 200)]);

        $service = new OllamaService();
        $result = $service->generateArticle([
            'city_name' => 'Bandung',
            'city_slug' => 'bandung',
            'service_slug' => 'brevet-ab',
            'word_count' => 7500,
            'topic' => 'Dummy Topic',
        ]);

        $this->assertTrue($result['success'] ?? false);
        $this->assertSame('Judul Test', $result['title']);
    }

    public function test_extracts_target_json_fields(): void
    {
        $this->baseConfig();

        $content = str_repeat('kata ', 7500);
        $body = [
            'message' => [
                'content' => json_encode([
                    'title' => 'Judul Test',
                    'slug' => 'judul-test',
                    'excerpt' => 'Excerpt test',
                    'content' => $content,
                    'seo_title' => 'SEO Title Test',
                    'meta_description' => 'Meta Description Test',
                    'suggested_faqs' => [['q' => 'Q1', 'a' => 'A1']],
                    'image_brief' => [
                        'concept' => 'Konsep',
                        'english_prompt' => 'Prompt',
                        'aspect_ratio' => '16:9',
                        'recommended_alt' => 'Alt',
                    ],
                ]),
            ],
        ];

        Http::fake(['*' => Http::response($body, 200)]);

        $service = new OllamaService();
        $result = $service->generateArticle([
            'city_name' => 'Bandung',
            'city_slug' => 'bandung',
            'service_slug' => 'brevet-ab',
            'word_count' => 7500,
            'topic' => 'Dummy Topic',
        ]);

        $this->assertTrue($result['success'] ?? false);
        $this->assertSame('judul-test', $result['slug']);
        $this->assertSame('Excerpt test', $result['excerpt']);
        $this->assertSame('SEO Title Test', $result['seo_title']);
        $this->assertSame('Meta Description Test', $result['meta_description']);
        $this->assertCount(1, $result['suggested_faqs']);
        $this->assertSame('Konsep', $result['image_brief']['concept']);
        $this->assertSame('Prompt', $result['image_brief']['english_prompt']);
        $this->assertSame('16:9', $result['image_brief']['aspect_ratio']);
        $this->assertSame('Alt', $result['image_brief']['recommended_alt']);
        $this->assertSame([
            'success', 'title', 'slug', 'excerpt', 'content', 'seo_title',
            'meta_description', 'suggested_faqs', 'image_brief', 'service_slug',
            'geo_review',
        ], array_keys($result));
    }

    public function test_geographically_generic_content_is_not_flagged(): void
    {
        $this->baseConfig();

        $paragraph = '<p>Artikel tetap umum tanpa menyebut provinsi kota atau kabupaten tertentu; '
            .'seluruh rincian dikonfirmasi melalui konsultasi sebelum dipublikasikan.</p>';
        $body = [
            'message' => [
                'content' => json_encode([
                    'title' => 'Artikel Generik',
                    // ~17 kata per paragraf: 450 kali ≈ 7.650 kata (dalam rentang 6.000–9.000).
                    'content' => '<h2>Ruang Lingkup Program</h2>'.str_repeat($paragraph, 450),
                ]),
            ],
        ];

        Http::fake(['*' => Http::response($body, 200)]);

        $service = new OllamaService();
        $result = $service->generateArticle([
            'service_slug' => 'brevet-ab',
            'word_count' => 7500,
            'topic' => 'Topik Umum',
        ]);

        $this->assertTrue($result['success']);
        $this->assertFalse($result['geo_review']['flagged']);
        $this->assertSame([], $result['geo_review']['references']);
    }

    public function test_geographic_references_in_generated_content_trigger_review(): void
    {
        $this->baseConfig();

        $paragraph = '<p>Pembahasan tetap generik pada bagian inti sehingga panjang artikel tercukupi '
            .'tanpa menambah klaim layanan yang tidak didukung katalog dalam konteks apa pun.</p>';
        $regional = '<h2>Ruang Lingkup Program di Jawa Barat</h2>'
            .'<p>Bagian ini menyebut <strong>Kota Bandung</strong> dan Kabupaten Bekasi, '
            .'misalnya skenario pelaksanaan di Bandar Lampung.</p>';
        $body = [
            'message' => [
                'content' => json_encode([
                    'title' => 'Artikel Wilayah',
                    // ~22 kata per paragraf: 350 kali ≈ 7.700 kata (dalam rentang 6.000–9.000).
                    'content' => str_repeat($paragraph, 350).$regional,
                ]),
            ],
        ];

        Http::fake(['*' => Http::response($body, 200)]);

        $service = new OllamaService();
        $result = $service->generateArticle([
            'service_slug' => 'brevet-ab',
            'word_count' => 7500,
            'topic' => 'Topik Wilayah',
        ]);

        $this->assertTrue($result['success']);
        $this->assertTrue($result['geo_review']['flagged']);

        $names = array_column($result['geo_review']['references'], 'name');
        $this->assertContains('Jawa Barat', $names);
        $this->assertContains('Kota Bandung', $names);
        $this->assertContains('Kabupaten Bekasi', $names);
        $this->assertContains('Bandar Lampung', $names);

        // The generated HTML is never rewritten by the detector.
        $this->assertStringContainsString('Kota Bandung', $result['content']);
    }

    public function test_seo_metadata_is_limited_to_prompted_lengths(): void
    {
        $service = new OllamaService();
        $result = $service->parseResponse(json_encode([
            'title' => 'Topik Artikel',
            'content' => '<p>Isi.</p>',
            'seo_title' => str_repeat('Judul SEO ', 10),
            'meta_description' => str_repeat('Deskripsi yang akurat. ', 10),
        ]), 'Topik Artikel', 'Indonesia', 7500);

        $this->assertSame(60, mb_strlen($result['seo_title']));
        $this->assertSame(155, mb_strlen($result['meta_description']));
    }

    public function test_continues_when_initial_article_below_6000(): void
    {
        $this->baseConfig();

        $firstContent = str_repeat('kata ', 1000);
        $secondContent = str_repeat('lanjut ', 5500);

        $call = 0;
        Http::fake(function () use (&$call, $firstContent, $secondContent) {
            $call++;
            if ($call === 1) {
                return Http::response([
                    'message' => [
                        'content' => json_encode([
                            'title' => 'Judul Test',
                            'slug' => 'judul-test',
                            'excerpt' => 'Excerpt test',
                            'content' => $firstContent,
                            'seo_title' => 'SEO Title Test',
                            'meta_description' => 'Meta Description Test',
                            'suggested_faqs' => [],
                            'image_brief' => [],
                        ]),
                    ],
                ], 200);
            }

            return Http::response([
                'message' => [
                    'content' => json_encode([
                        'title' => 'Judul Test',
                        'slug' => 'judul-test',
                        'excerpt' => 'Excerpt test',
                        'content' => $secondContent,
                        'seo_title' => 'SEO Title Test',
                        'meta_description' => 'Meta Description Test',
                        'suggested_faqs' => [],
                        'image_brief' => [],
                    ]),
                ],
            ], 200);
        });

        $service = new OllamaService();
        $result = $service->generateArticle([
            'city_name' => 'Bandung',
            'city_slug' => 'bandung',
            'service_slug' => 'brevet-ab',
            'word_count' => 7500,
            'topic' => 'Dummy Topic',
        ]);

        $this->assertTrue($result['success'] ?? false);
        $this->assertGreaterThanOrEqual(6000, $service->countWords($result['content']));
        Http::assertSent(function (object $request): bool {
            $system = $request['messages'][0]['content'];
            $user = $request['messages'][1]['content'];

            return str_contains($system, 'Jangan mengulang pembuka, kesimpulan, paragraf')
                && str_contains($system, 'Jangan mengarang statistik')
                && str_contains($user, '<artikel_sebelumnya>')
                && str_contains($user, 'Jangan menyalin atau mengulangnya');
        });
    }

    public function test_rejects_article_below_6000_after_continuation_attempts(): void
    {
        $this->baseConfig();

        $shortContent = str_repeat('kata ', 100);
        Http::fake(['*' => Http::response([
            'message' => [
                'content' => json_encode([
                    'title' => 'Judul Test',
                    'slug' => 'judul-test',
                    'excerpt' => 'Excerpt test',
                    'content' => $shortContent,
                    'seo_title' => 'SEO Title Test',
                    'meta_description' => 'Meta Description Test',
                    'suggested_faqs' => [],
                    'image_brief' => [],
                ]),
            ],
        ], 200)]);

        $service = new OllamaService();
        $result = $service->generateArticle([
            'city_name' => 'Bandung',
            'city_slug' => 'bandung',
            'service_slug' => 'brevet-ab',
            'word_count' => 7500,
            'topic' => 'Dummy Topic',
        ]);

        $this->assertFalse($result['success'] ?? true);
        $this->assertArrayHasKey('error', $result);
    }

    public function test_rejects_article_exceeding_9000_words(): void
    {
        $this->baseConfig();

        $overContent = str_repeat('kata ', 10000);
        Http::fake(['*' => Http::response([
            'message' => [
                'content' => json_encode([
                    'title' => 'Judul Test',
                    'slug' => 'judul-test',
                    'excerpt' => 'Excerpt test',
                    'content' => $overContent,
                    'seo_title' => 'SEO Title Test',
                    'meta_description' => 'Meta Description Test',
                    'suggested_faqs' => [],
                    'image_brief' => [],
                ]),
            ],
        ], 200)]);

        $service = new OllamaService();
        $result = $service->generateArticle([
            'city_name' => 'Bandung',
            'city_slug' => 'bandung',
            'service_slug' => 'brevet-ab',
            'word_count' => 7500,
            'topic' => 'Dummy Topic',
        ]);

        $this->assertFalse($result['success'] ?? true);
        $this->assertArrayHasKey('error', $result);
    }

    public function test_http_error_returns_failure(): void
    {
        $this->baseConfig();

        Http::fake(['*' => Http::response('Server Error', 500)]);

        $service = new OllamaService();
        $result = $service->generateArticle([
            'city_name' => 'Bandung',
            'city_slug' => 'bandung',
            'service_slug' => 'brevet-ab',
            'word_count' => 7500,
            'topic' => 'Dummy Topic',
        ]);

        $this->assertFalse($result['success'] ?? true);
        $this->assertArrayHasKey('error', $result);
    }

    public function test_timeout_returns_failure(): void
    {
        $this->baseConfig();

        Http::fake(['*' => function () {
            throw new ConnectionException('cURL error 28: Operation timed out');
        }]);

        $service = new OllamaService();
        $result = $service->generateArticle([
            'city_name' => 'Bandung',
            'city_slug' => 'bandung',
            'service_slug' => 'brevet-ab',
            'word_count' => 7500,
            'topic' => 'Dummy Topic',
        ]);

        $this->assertFalse($result['success'] ?? true);
        $this->assertArrayHasKey('error', $result);
    }

    public function test_malformed_response_can_be_recovered(): void
    {
        $this->baseConfig();

        $content = str_repeat('kata ', 7500);
        // Malformed outer JSON but valid content field.
        $raw = '{"title":"Judul Test","content":"' . str_replace('"', '\\"', $content) . '"';
        Http::fake(['*' => Http::response([
            'message' => ['content' => $raw],
        ], 200)]);

        $service = new OllamaService();
        $result = $service->generateArticle([
            'city_name' => 'Bandung',
            'city_slug' => 'bandung',
            'service_slug' => 'brevet-ab',
            'word_count' => 7500,
            'topic' => 'Dummy Topic',
        ]);

        $this->assertTrue($result['success'] ?? false);
        $this->assertSame('Dummy Topic', $result['title']);
    }

    public function test_preserves_metadata_and_whatsapp_callouts(): void
    {
        $this->baseConfig();

        $content = str_repeat('kata ', 7500);
        $body = [
            'message' => [
                'content' => json_encode([
                    'title' => 'Judul Test',
                    'slug' => 'judul-test',
                    'excerpt' => 'Excerpt test',
                    'content' => $content,
                    'seo_title' => 'SEO Title Test',
                    'meta_description' => 'Meta Description Test',
                    'suggested_faqs' => [['q' => 'Q1', 'a' => 'A1']],
                    'image_brief' => [
                        'concept' => 'Konsep',
                        'english_prompt' => 'Prompt',
                        'aspect_ratio' => '16:9',
                        'recommended_alt' => 'Alt',
                    ],
                ]),
            ],
        ];

        Http::fake(['*' => Http::response($body, 200)]);

        $service = new OllamaService();
        $result = $service->generateArticle([
            'city_name' => 'Bandung',
            'city_slug' => 'bandung',
            'service_slug' => 'brevet-ab',
            'word_count' => 7500,
            'topic' => 'Dummy Topic',
        ]);

        $this->assertTrue($result['success'] ?? false);
        $this->assertStringContainsString('bg-concrete-100', $result['content']);
        $calloutCount = substr_count($result['content'], 'bg-concrete-100');
        $this->assertSame(5, $calloutCount);
        $this->assertSame('Judul Test', $result['title']);
        $this->assertSame('Konsep', $result['image_brief']['concept']);
    }

    public function test_generated_html_is_sanitized_before_returning_to_editor(): void
    {
        $this->baseConfig();

        $content = '<p>'.str_repeat('kata ', 7500).'</p><script>alert("xss")</script>'
            .'<p onclick="evil()">Akhir artikel.</p>';
        Http::fake(['*' => Http::response([
            'message' => ['content' => json_encode([
                'title' => 'Artikel Uji Sanitasi',
                'slug' => 'artikel-uji-sanitasi',
                'excerpt' => 'Ringkasan.',
                'content' => $content,
                'seo_title' => 'Artikel Uji Sanitasi',
                'meta_description' => 'Ringkasan artikel.',
                'suggested_faqs' => [],
                'image_brief' => [],
            ])],
        ], 200)]);

        $result = (new OllamaService())->generateArticle([
            'city_name' => 'Bandung',
            'city_slug' => 'bandung',
            'service_slug' => 'brevet-ab',
            'word_count' => 7500,
            'topic' => 'Pemeliharaan mesin',
        ]);

        $this->assertTrue($result['success'] ?? false);
        $this->assertStringNotContainsString('<script', $result['content']);
        $this->assertStringNotContainsString('alert(', $result['content']);
        $this->assertStringNotContainsString('onclick', $result['content']);
        $this->assertStringContainsString('Akhir artikel.', $result['content']);
    }
}
