<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Services\RegionalService;
use App\Services\SchemeService;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Generate dynamic XML sitemap.
     */
    public function index(RegionalService $regional, SchemeService $schemes): Response
    {
        $urls = [];
        $baseUrl = rtrim((string) config('app.url'), '/');

        // 1. Static Home & Article index
        $urls[] = [
            'loc' => $baseUrl.route('home', [], false),
            'lastmod' => now()->toAtomString(),
            'changefreq' => 'daily',
            'priority' => '1.0',
        ];

        $urls[] = [
            'loc' => $baseUrl.route('articles.index', [], false),
            'lastmod' => now()->toAtomString(),
            'changefreq' => 'daily',
            'priority' => '0.8',
        ];

        // 2. Catalog schemes
        $programs = $schemes->all();
        foreach ($programs as $slug => $item) {
            $urls[] = [
                'loc' => $baseUrl.route('program.show', $slug, false),
                'lastmod' => now()->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.9',
            ];
        }

        // 3. Published Articles
        if (\Illuminate\Support\Facades\Schema::hasTable('articles')) {
            $articles = Article::published()->latest('updated_at')->get(['slug', 'updated_at']);
            foreach ($articles as $article) {
                $urls[] = [
                    'loc' => $baseUrl.route('articles.show', $article->slug, false),
                    'lastmod' => $article->updated_at->toAtomString(),
                    'changefreq' => 'weekly',
                    'priority' => '0.7',
                ];
            }
        }

        // 4. Regional Provinces
        $provinces = $regional->getAllProvinces();
        foreach ($provinces as $prov) {
            $urls[] = [
                'loc' => $baseUrl.route('regional.province', $prov['slug'], false),
                'lastmod' => now()->startOfMonth()->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => '0.6',
            ];
        }

        // 5. Regional Cities (514)
        $cities = $regional->getAllCities();
        foreach ($cities as $city) {
            $urls[] = [
                'loc' => $baseUrl.route('regional.city', $city['slug'], false),
                'lastmod' => now()->startOfMonth()->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => '0.5',
            ];
        }

        $xml = view('sitemap', compact('urls'))->render();

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
        ]);
    }
}
