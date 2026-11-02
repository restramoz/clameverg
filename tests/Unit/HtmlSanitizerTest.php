<?php

namespace Tests\Unit;

use App\Services\HtmlSanitizerService;
use Tests\TestCase;

class HtmlSanitizerTest extends TestCase
{
    private HtmlSanitizerService $sanitizer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sanitizer = new HtmlSanitizerService();
    }

    public function test_passes_clean_html_unchanged(): void
    {
        $html = '<p>Konten artikel K3 Listrik yang aman.</p>';
        $result = $this->sanitizer->sanitize($html);
        $this->assertStringContainsString('Konten artikel K3 Listrik yang aman.', $result);
    }

    public function test_strips_script_tags(): void
    {
        $html = '<p>Safe</p><script>alert("xss")</script>';
        $result = $this->sanitizer->sanitize($html);
        $this->assertStringNotContainsString('<script>', $result);
        $this->assertStringNotContainsString('alert(', $result);
        $this->assertStringContainsString('Safe', $result);
    }

    public function test_strips_on_event_attributes(): void
    {
        $html = '<p onclick="evil()">Click me</p>';
        $result = $this->sanitizer->sanitize($html);
        $this->assertStringNotContainsString('onclick', $result);
        $this->assertStringContainsString('Click me', $result);
    }

    public function test_strips_javascript_href(): void
    {
        $html = '<a href="javascript:alert(1)">Link</a>';
        $result = $this->sanitizer->sanitize($html);
        $this->assertStringNotContainsString('javascript:', $result);
    }

    public function test_strips_iframe_tags(): void
    {
        $html = '<p>Content</p><iframe src="https://evil.com"></iframe>';
        $result = $this->sanitizer->sanitize($html);
        $this->assertStringNotContainsString('<iframe', $result);
        $this->assertStringContainsString('Content', $result);
    }

    public function test_strips_object_tags(): void
    {
        $html = '<object data="evil.swf"></object><p>Text</p>';
        $result = $this->sanitizer->sanitize($html);
        $this->assertStringNotContainsString('<object', $result);
    }

    public function test_allows_standard_block_elements(): void
    {
        $html = '<h2>Judul</h2><h3>Sub</h3><p>Paragraf</p><ul><li>Item</li></ul><ol><li>Item</li></ol>';
        $result = $this->sanitizer->sanitize($html);
        $this->assertStringContainsString('<h2>', $result);
        $this->assertStringContainsString('<h3>', $result);
        $this->assertStringContainsString('<p>', $result);
        $this->assertStringContainsString('<ul>', $result);
        $this->assertStringContainsString('<li>', $result);
    }

    public function test_allows_strong_and_em(): void
    {
        $html = '<p><strong>Penting</strong> dan <em>penekanan</em>.</p>';
        $result = $this->sanitizer->sanitize($html);
        $this->assertStringContainsString('<strong>Penting</strong>', $result);
        $this->assertStringContainsString('<em>penekanan</em>', $result);
    }

    public function test_strips_style_attribute(): void
    {
        $html = '<p style="color:red; background: url(evil)">Text</p>';
        $result = $this->sanitizer->sanitize($html);
        $this->assertStringNotContainsString('style=', $result);
        $this->assertStringContainsString('Text', $result);
    }

    public function test_handles_empty_string(): void
    {
        $result = $this->sanitizer->sanitize('');
        $this->assertIsString($result);
    }

    public function test_handles_plain_text(): void
    {
        $result = $this->sanitizer->sanitize('Hanya teks biasa tanpa tag HTML.');
        $this->assertStringContainsString('Hanya teks biasa', $result);
    }
}
