<?php

namespace Tests\Unit;

use App\Services\OllamaService;
use App\Support\WhatsApp;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

/**
 * Verify that the addWhatsAppCallout() method injects exactly five callouts
 * using the fixed article consultation numbers from WhatsApp::consultationNumbers().
 *
 * Each of the five callouts rotates through the four consultation numbers.
 * All callouts are clickable with valid wa.me URLs, target="_blank", and
 * rel="noopener noreferrer" — regardless of global config.
 */
class WhatsAppCalloutTest extends TestCase
{
    protected function getService(): OllamaService
    {
        return new OllamaService();
    }

    protected function invokeCallout(array $result, string $city = 'TestCity'): array
    {
        $service = $this->getService();
        $ref = new \ReflectionMethod($service, 'addWhatsAppCallout');
        $ref->setAccessible(true);

        return $ref->invoke($service, $result, $city);
    }

    public function test_inserts_five_callouts_using_consultation_numbers(): void
    {
        // Global phone config should NOT affect article callouts.
        Config::set('services.whatsapp.phone', '081299887766');

        $result = [
            'content' => "<p>Paragraf pertama.</p><p>Paragraf kedua.</p><p>Paragraf ketiga.</p>",
        ];

        $updated = $this->invokeCallout($result);

        $content = $updated['content'];
        $this->assertEquals(5, substr_count($content, 'not-prose'), 'Expected exactly five WhatsApp callouts');

        // All five callouts use consultation numbers (rotating through the 4).
        $consultationNumbers = WhatsApp::consultationNumbers();
        foreach ($consultationNumbers as $number) {
            $url = WhatsApp::consultationUrl($number);
            $this->assertStringContainsString($url, $content, "Missing consultation URL for {$number}");
        }

        // The 5th callout wraps around to the first number again.
        $firstUrl = WhatsApp::consultationUrl($consultationNumbers[0]);
        $this->assertSame(
            2,
            substr_count($content, $firstUrl),
            "First consultation number should appear twice (callouts 1 and 5)"
        );

        // Each callout has a proper clickable link with target="_blank" and rel="noopener noreferrer"
        $this->assertStringContainsString('target="_blank"', $content);
        $this->assertStringContainsString('rel="noopener noreferrer"', $content);
        $this->assertStringContainsString('href="https://wa.me/', $content);
    }

    public function test_callouts_work_without_global_phone_config(): void
    {
        // Even with empty global config, article callouts still use consultation numbers.
        Config::set('services.whatsapp.phone', '');

        $result = [
            'content' => "<p>Paragraf pertama.</p><p>Paragraf kedua.</p><p>Paragraf ketiga.</p>",
        ];

        $updated = $this->invokeCallout($result);
        $content = $updated['content'];

        $this->assertEquals(5, substr_count($content, 'not-prose'), 'Callouts must still be present');

        // All callouts are clickable links (not fallback text).
        $this->assertStringContainsString('wa.me', $content);
        $this->assertStringContainsString('href=', $content);
        $this->assertStringNotContainsString('Hubungi admin untuk informasi konsultasi.', $content);

        // Verify all four consultation numbers appear.
        $consultationNumbers = WhatsApp::consultationNumbers();
        foreach ($consultationNumbers as $number) {
            $url = WhatsApp::consultationUrl($number);
            $this->assertStringContainsString($url, $content, "Missing consultation URL for {$number}");
        }
    }

    public function test_callouts_use_only_consultation_numbers_not_global_config(): void
    {
        // Set a global phone that is different from consultation numbers.
        Config::set('services.whatsapp.phone', '081299887766');

        $result = ['content' => str_repeat('<p>Isi paragraf artikel.</p>', 8)];

        $updated = $this->invokeCallout($result);
        $content = $updated['content'];

        // Global phone should NOT appear in callouts.
        $this->assertStringNotContainsString('6281299887766', $content, 'Global phone must not leak into article callouts.');

        // Only consultation numbers should appear.
        $consultationNumbers = WhatsApp::consultationNumbers();
        $foundConsultationNumbers = 0;
        foreach ($consultationNumbers as $number) {
            $normalized = WhatsApp::normalizePhone($number);
            if (str_contains($content, $normalized)) {
                $foundConsultationNumbers++;
            }
        }
        $this->assertSame(4, $foundConsultationNumbers, 'All four consultation numbers should appear in callouts.');
    }

    // ─── addArticleConsultationBlock() regression tests ────────────────────────

    protected function invokeConsultationBlock(array $result): array
    {
        $service = $this->getService();
        $ref = new \ReflectionMethod($service, 'addArticleConsultationBlock');
        $ref->setAccessible(true);

        return $ref->invoke($service, $result);
    }

    
    public function test_consultation_block_adds_four_clickable_number_links(): void
     {
       	$result = ['content' => '<p>Konten artikel.</p>'];

       	$updated = $this->invokeConsultationBlock($result);
    	$content = $updated['content'];

    	$expectedLinks = [
        '08118500177' => 'https://wa.me/628118500177',
        '081399810272' => 'https://wa.me/6281399810272',
        '087894580806' => 'https://wa.me/6287894580806',
        '087894580649' => 'https://wa.me/6287894580649',
    	];

    	$this->assertStringContainsString(
        	'article-consultation-numbers',
       	 	$content
    );

    $this->assertStringContainsString(
        'Konsultasi via WhatsApp',
        $content
    );

    foreach ($expectedLinks as $number => $url) {
        $this->assertStringContainsString(
            'href="' . $url . '"',
            $content,
            "Missing WhatsApp URL for {$number}"
        );

        $this->assertStringContainsString(
            '>' . $number . '</a>',
            $content,
            "Missing clickable phone number: {$number}"
        );

        $this->assertSame(
            1,
            substr_count($content, $url),
            "URL {$url} should appear exactly once"
        );

        $this->assertSame(
            0,
            substr_count($content, 'Konsultasi WhatsApp ' . $number),
            "Deprecated button label should not exist for {$number}"
        );
    }
}


    
public function test_consultation_block_is_idempotent_no_duplicates_on_second_run(): void
{
    $result = ['content' => '<p>Konten artikel.</p>'];

    $firstRun = $this->invokeConsultationBlock($result);
    $secondRun = $this->invokeConsultationBlock($firstRun);

    $content = $secondRun['content'];

    foreach ([
        '628118500177',
        '6281399810272',
        '6287894580806',
        '6287894580649',
    ] as $phone) {
        $this->assertSame(
            1,
            substr_count($content, $phone),
            "Phone URL {$phone} should appear exactly once"
        );
    }

    $this->assertSame(
        1,
        substr_count($content, 'article-consultation-numbers'),
        'Only one consultation wrapper should exist'
    );
}


    public function test_consultation_block_skips_when_complete_block_already_exists(): void
    {
        // Simulate content that already has a complete block
        $existingBlock = '<div class="article-consultation-numbers my-6 p-4 sm:p-6 rounded-2xl bg-neutral-50 border border-neutral-200 not-prose">'
            .'<h3 class="text-xs font-bold text-dark-mahogany uppercase tracking-wider">Konsultasi via WhatsApp</h3>'
            .'<div class="mt-3 flex flex-wrap gap-2.5 sm:gap-3">'
            .'<a href="https://wa.me/628118500177" target="_blank" rel="noopener noreferrer">Konsultasi WhatsApp 08118500177</a>'
            .'<a href="https://wa.me/6281399810272" target="_blank" rel="noopener noreferrer">Konsultasi WhatsApp 081399810272</a>'
            .'<a href="https://wa.me/6287894580806" target="_blank" rel="noopener noreferrer">Konsultasi WhatsApp 087894580806</a>'
            .'<a href="https://wa.me/6287894580649" target="_blank" rel="noopener noreferrer">Konsultasi WhatsApp 087894580649</a>'
            .'</div>'
            .'<div class="mt-3 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-2.5 sm:gap-3">'
            .'<a href="https://wa.me/628118500177" target="_blank" rel="noopener noreferrer">08118500177</a>'
            .'<a href="https://wa.me/6281399810272" target="_blank" rel="noopener noreferrer">081399810272</a>'
            .'<a href="https://wa.me/6287894580806" target="_blank" rel="noopener noreferrer">087894580806</a>'
            .'<a href="https://wa.me/6287894580649" target="_blank" rel="noopener noreferrer">087894580649</a>'
            .'</div>'
            .'</div>';

        $result = ['content' => '<p>Konten artikel.</p>'.$existingBlock];

        $updated = $this->invokeConsultationBlock($result);
        $content = $updated['content'];

        // Content should be unchanged
        $this->assertEquals($result['content'], $content);
    }

    public function test_consultation_block_completes_partial_block(): void
    {
        // Simulate content with only partial block (missing some URLs)
        $partialBlock = '<div class="article-consultation-numbers">'
            .'<a href="https://wa.me/628118500177">Konsultasi WhatsApp 08118500177</a>'
            .'<a href="https://wa.me/6281399810272">Konsultasi WhatsApp 081399810272</a>'
            .'</div>';

        $result = ['content' => '<p>Konten artikel.</p>'.$partialBlock];

        $updated = $this->invokeConsultationBlock($result);
        $content = $updated['content'];

        // Should now have all four URLs (the partial block was incomplete, so new complete block added)
        foreach (['628118500177', '6281399810272', '6287894580806', '6287894580649'] as $url) {
            $this->assertStringContainsString($url, $content);
        }
    }

    public function test_consultation_block_uses_whatsapp_helper_for_urls(): void
    {
        $result = ['content' => '<p>Konten artikel.</p>'];

        $updated = $this->invokeConsultationBlock($result);
        $content = $updated['content'];

        // Verify URLs are exactly what WhatsApp::consultationUrl() produces
        foreach (\App\Support\WhatsApp::consultationNumbers() as $number) {
            $expectedUrl = \App\Support\WhatsApp::consultationUrl($number);
            $this->assertStringContainsString($expectedUrl, $content, "URL for {$number} should match WhatsApp::consultationUrl()");
        }
    }
}
