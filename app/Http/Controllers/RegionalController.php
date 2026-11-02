<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Services\RegionalService;
use App\Services\SchemeService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class RegionalController extends Controller
{
    public function __construct(private readonly RegionalService $regional, private readonly SchemeService $schemes) {}

    public function province(string $slug): View
    {
        $province = $this->regional->findProvince($slug);
        abort_if($province === null, 404, 'Provinsi tidak ditemukan.');

        return view('regional.province', [
            'province' => $province,
            'programs' => $this->schemes->all(),
            'waMessage' => "Halo, saya ingin menanyakan informasi program untuk Provinsi {$province['name']}.",
        ]);
    }

    public function city(string $slug): View|RedirectResponse
    {
        $city = $this->regional->findCity($slug);
        abort_if($city === null, 404, 'Kota/Kabupaten tidak ditemukan.');

        if ($slug !== $city['slug']) {
            return redirect()->route('regional.city', $city['slug'], 301);
        }

        $articles = collect();
        if (Schema::hasTable('articles')) {
            $articles = Article::published()->where('target_city_slug', $slug)
                ->latest('published_at')->take(6)->get();
        }

        return view('regional.city', [
            'city' => $city,
            'nearbyCities' => $this->regional->getProvinceCities($city['province_code'], $city['code'], 8),
            'programs' => $this->schemes->all(),
            'articles' => $articles,
            'regionalProgramPrefix' => ($city['type'] === 'Kota' ? 'kota-' : 'kabupaten-').Str::slug($city['raw_name']),
            'waMessage' => "Halo, saya ingin menanyakan informasi program untuk {$city['name']}.",
        ]);
    }

    public function legacyCityRedirect(string $slug): RedirectResponse
    {
        return redirect()->route('regional.city', $slug, 301);
    }
}
