<?php

namespace Tests\Unit;

use App\Services\GeographicContentService;
use Tests\TestCase;

class GeographicContentServiceTest extends TestCase
{
    private function detector(): GeographicContentService
    {
        return app(GeographicContentService::class);
    }

    /**
     * @param list<array{kind: string, name: string}> $references
     */
    private function names(array $references): array
    {
        return array_column($references, 'name');
    }

    public function test_generic_article_content_has_no_references(): void
    {
        $html = '<p>Artikel ini membahas layanan secara umum. Pembahasan tidak menyebut provinsi, '
            .'kota, kabupaten, atau kecamatan tertentu; semua rincian dikonfirmasi melalui konsultasi.</p>';

        $this->assertSame([], $this->detector()->findReferences($html));
    }

    public function test_province_name_is_detected(): void
    {
        $names = $this->names($this->detector()->findReferences(
            '<p>Program dapat menjawab kebutuhan pembaca di Jawa Barat.</p>'
        ));

        $this->assertSame(['Jawa Barat'], $names);
    }

    public function test_city_and_regency_names_are_detected_with_their_type(): void
    {
        $names = $this->names($this->detector()->findReferences(
            '<p>Sekenario pelaksanaan di Kota Surabaya meniru praktik Kabupaten Bekasi.</p>'
        ));

        $this->assertSame(['Kota Surabaya', 'Kabupaten Bekasi'], $names);
    }

    public function test_regency_abbreviation_kab_is_detected(): void
    {
        $names = $this->names($this->detector()->findReferences(
            '<p>Cara pandang yang sama berlaku pada kab. Tegal.</p>'
        ));

        $this->assertContains('Kabupaten Tegal', $names);
    }

    public function test_multi_word_region_names_are_detected(): void
    {
        $names = $this->names($this->detector()->findReferences(
            '<h2>Wawasan bagi pembaca Bandar Lampung</h2>'
                .'<p>Praktik serupa dijelaskan untuk Kabupaten Bandung Barat dan DKI Jakarta.</p>'
        ));

        $names = array_map('mb_strtolower', $names);
        $this->assertContains('bandar lampung', $names);
        $this->assertContains('kabupaten bandung barat', $names);
    }

    public function test_papua_and_papua_barat_are_not_double_reported(): void
    {
        $names = $this->names($this->detector()->findReferences(
            '<p>Pembahasan wilayah Papua Barat diangkat tanpa menambah klaim.</p>'
        ));

        $this->assertSame(['Papua Barat'], $names);
    }

    public function test_false_positives_in_ordinary_prose_are_avoided(): void
    {
        // "kota"/"kabupaten"/"provinsi" as generic words, lowercase common
        // noun collisions ("batu", "medan"), and near-miss wording.
        $html = '<p>Benda ringan dalum medan magnet dibahas; batu bata disebut kecil '
            .'dan tidak ada kota baru, renungan utama untuk provinsi apa pun atau kabupaten di Indonesia.</p>';

        $this->assertSame([], $this->detector()->findReferences($html));
    }

    public function test_substring_mimics_do_not_match_region_names(): void
    {
        // Word boundaries matter: "bandung" inside a longer word or a
        // lowercase common usage must not be reported.
        $html = '<p>Merek abendung berbeda dengan visibilitas kajian.</p>';

        $this->assertSame([], $this->detector()->findReferences($html));
    }

    public function test_geographic_references_inside_html_text_are_detected(): void
    {
        $names = $this->names($this->detector()->findReferences(
            '<p>Pembaca sering mempertanyakan <strong>Jawa Tengah</strong> '
            .'sekaligus <em>kota Semarang</em> dalam satu paragraf edukatif panjang.</p>'
        ));

        // "kota Semarang" uses the type prefix, so it is detected too.
        $namesLower = array_map('mb_strtolower', $names);
        $this->assertContains('jawa tengah', $namesLower);
        $this->assertContains('kota semarang', $namesLower);
    }

    public function test_geographic_references_in_headings_are_detected(): void
    {
        $detector = $this->detector();
        $names = $this->names($detector->findReferences(
            '<h2>Menjelang tenggat di Jawa Timur</h2><ul><li>bagida diringkas</li></ul><h3>Kota Malang</h3>'
        ));

        $this->assertSame(['Jawa Timur', 'Kota Malang'], $names);
    }

    public function test_html_entities_are_decoded_before_detection(): void
    {
        $names = $this->names($this->detector()->findReferences(
            '<p>Air hidup Jawa&#32;Barat dipelajari dalam segmen edukasi.</p>'
        ));

        $this->assertContains('Jawa Barat', $names);
    }

    public function test_empty_and_textless_content_return_no_references(): void
    {
        $detector = $this->detector();

        $this->assertSame([], $detector->findReferences(''));
        $this->assertSame([], $detector->findReferences('   '));
        $this->assertSame([], $detector->findReferences('<p>&nbsp;</p>'));
    }
}
