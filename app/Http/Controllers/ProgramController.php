<?php

namespace App\Http\Controllers;

use App\Services\RegionalService;
use App\Services\SchemeService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ProgramController extends Controller
{
    public function __construct(private readonly SchemeService $schemes) {}

    /**
     * Legacy /program/{slug} URLs permanently point to the canonical
     * /layanan/{slug} URL. Unknown slugs keep the same 404 as /layanan.
     * The query string is dropped: no legacy parameter is meaningful for the
     * canonical page.
     */
    public function showLegacy(string $slug): RedirectResponse
    {
        if ($this->schemes->find($slug) === null) {
            abort(404, 'Layanan atau program pelatihan tidak ditemukan.');
        }

        return redirect('/layanan/'.$slug, 301);
    }

    /**
     * Display the specified program or service page by slug.
     */
    public function show(string $slug): View
    {
        $program = $this->schemes->find($slug);

        if ($program === null) {
            abort(404, 'Layanan atau program pelatihan tidak ditemukan.');
        }

        // Recommend related schemes from the same authoritative catalog
        $relatedPrograms = collect($this->schemes->all())
            ->filter(fn (array $p, string $key): bool => $key !== $program['slug'])
            ->values()
            ->take(6)
            ->all();

        return view('programs.show', [
            'program' => $program,
            'relatedPrograms' => $relatedPrograms,
            'otherPrograms' => $relatedPrograms,
        ]);
    }

    /**
     * Display a city-specific service detail page preserving regional context.
     *
     * @param string $city      Slug of the city/regency (e.g. "bandung", "kab-bekasi")
     * @param string $program   Catalog slug (e.g. "perunggasan-jenjang-5")
     */
    public function cityProgram(string $city, string $program, RegionalService $regional): RedirectResponse
    {
        $cityData = $regional->findCity($city);
        abort_if($cityData === null, 404, 'Kota/Kabupaten tidak ditemukan.');
        $programData = $this->schemes->find($program);
        abort_if($programData === null, 404, 'Layanan tidak ditemukan.');

        $prefix = $cityData['type'] === 'Kota' ? 'kota-' : 'kabupaten-';
        return redirect()->route('region.program.detail', [
            'region' => $prefix.Str::slug($cityData['raw_name']),
            'program' => $programData['slug'],
        ], 301);
    }

    public function regionalProgram(Request $request, string $region, string $program, RegionalService $regional): View
    {
        $programData = $this->schemes->find($program);
        abort_if($programData === null, 404, 'Layanan tidak ditemukan.');

        if ($request->routeIs('province.program.detail')) {
            $data = $regional->findProvince($region);
            abort_if($data === null, 404, 'Provinsi tidak ditemukan.');
            $canonicalBase = rtrim((string) config('app.url'), '/');
            $context = [
                'name' => 'Provinsi '.$data['name'],
                'landingUrl' => route('regional.province', $data['slug']),
                'canonicalUrl' => $canonicalBase.route('province.program.detail', ['region' => $data['slug'], 'program' => $programData['slug']], false),
                'slug' => $data['slug'],
            ];
        } else {
            $data = $regional->findCity($region);
            abort_if($data === null, 404, 'Kota/Kabupaten tidak ditemukan.');
            $prefix = $data['type'] === 'Kota' ? 'kota-' : 'kabupaten-';
            $regionSlug = $prefix.Str::slug($data['raw_name']);
            $canonicalBase = rtrim((string) config('app.url'), '/');
            $context = [
                'name' => $data['name'],
                'landingUrl' => route('regional.city', $data['slug']),
                'canonicalUrl' => $canonicalBase.route('region.program.detail', ['region' => $regionSlug, 'program' => $programData['slug']], false),
                'slug' => $regionSlug,
            ];
        }

        $relatedPrograms = collect($this->schemes->all())
            ->reject(fn (array $item): bool => $item['slug'] === $programData['slug'])
            ->take(6)->all();
        $articles = collect();
        if (Schema::hasTable('articles') && Schema::hasColumn('articles', 'service_slug')) {
            $articles = \App\Models\Article::published()->where('service_slug', $programData['slug'])
                ->latest('published_at')->take(6)->get();
        }

        return view('programs.show', [
            'program' => $programData,
            'relatedPrograms' => $relatedPrograms,
            'otherPrograms' => $relatedPrograms,
            'region' => $context,
            'articles' => $articles,
            'pageTitle' => $programData['title'].' '.$context['name'],
            'pageDescription' => 'Informasi '.$programData['title'].' untuk '.$context['name'].'. Tinjau ruang lingkup dan rincian yang tersedia dalam katalog layanan Clameverg.',
            'waMessage' => 'Halo, saya ingin menanyakan informasi '.$programData['title'].' untuk '.$context['name'].'.',
        ]);
    }
    /**
     * Regional-contextual view of a shared catalog service.
     */
    public function regionalService(string $slug, RegionalService $regional): View
    {
        $program = $this->schemes->find($slug);
        abort_if($program === null, 404, 'Layanan atau program pelatihan tidak ditemukan.');

        $contextName = 'Konteks Regional Clameverg';
        $landingUrl = route('home');
        $canonicalUrl = rtrim((string) config('app.url'), '/').route('regional.services.show', ['slug' => $program['slug']], false);
        $lastRegional = session('last_regional_slug');
        if (is_string($lastRegional) && $lastRegional !== '') {
            $data = $regional->findProvince($lastRegional);
            if ($data !== null) {
                $contextName = 'Provinsi '.$data['name'];
                $landingUrl = route('regional.province', $data['slug']);
                $canonicalUrl = rtrim((string) config('app.url'), '/').route('regional.services.show', ['slug' => $program['slug']], false);
            }
        }

        $region = [
            'name' => $contextName,
            'landingUrl' => $landingUrl,
            'canonicalUrl' => $canonicalUrl,
            'slug' => $lastRegional ?: 'regional',
        ];

        $relatedPrograms = collect($this->schemes->all())
            ->reject(fn (array $item): bool => $item['slug'] === $program['slug'])
            ->take(6)->all();

        return view('programs.show', [
            'program' => $program,
            'relatedPrograms' => $relatedPrograms,
            'otherPrograms' => $relatedPrograms,
            'region' => $region,
            'pageTitle' => $program['title'].' '.$contextName,
            'pageDescription' => 'Informasi '.$program['title'].' untuk '.$contextName.'. Tinjau ruang lingkup dan rincian yang tersedia dalam katalog layanan Clameverg.',
            'waMessage' => 'Halo, saya ingin menanyakan informasi '.$program['title'].' untuk '.$contextName.'.',
        ]);
    }

    /**
     * City-contextual view of a shared catalog service.
     */
    public function cityService(string $slug, RegionalService $regional): View
    {
        $program = $this->schemes->find($slug);
        abort_if($program === null, 404, 'Layanan atau program pelatihan tidak ditemukan.');

        $contextName = 'Konteks Kota Clameverg';
        $landingUrl = route('home');
        $canonicalUrl = rtrim((string) config('app.url'), '/').route('city.services.show', ['slug' => $program['slug']], false);
        $lastCity = session('last_city_slug');
        if (is_string($lastCity) && $lastCity !== '') {
            $data = $regional->findCity($lastCity);
            if ($data !== null) {
                $contextName = $data['name'];
                $landingUrl = route('regional.city', $data['slug']);
                $canonicalUrl = rtrim((string) config('app.url'), '/').route('city.services.show', ['slug' => $program['slug']], false);
            }
        }

        $region = [
            'name' => $contextName,
            'landingUrl' => $landingUrl,
            'canonicalUrl' => $canonicalUrl,
            'slug' => $lastCity ?: 'city',
        ];

        $relatedPrograms = collect($this->schemes->all())
            ->reject(fn (array $item): bool => $item['slug'] === $program['slug'])
            ->take(6)->all();

        return view('programs.show', [
            'program' => $program,
            'relatedPrograms' => $relatedPrograms,
            'otherPrograms' => $relatedPrograms,
            'region' => $region,
            'pageTitle' => $program['title'].' '.$contextName,
            'pageDescription' => 'Informasi '.$program['title'].' untuk '.$contextName.'. Tinjau ruang lingkup dan rincian yang tersedia dalam katalog layanan Clameverg.',
            'waMessage' => 'Halo, saya ingin menanyakan informasi '.$program['title'].' untuk '.$contextName.'.',
        ]);
    }

    /**
     * Display a shared catalog service in its Regional or City geographic context.
     *
     * URL shape: /{geographic-slug}/{service-slug}
     */
    public function geographicService(string $geographic, string $service, RegionalService $regional): View
    {
        $programData = $this->schemes->find($service);
        abort_if($programData === null, 404, 'Layanan tidak ditemukan.');

        $province = $regional->findProvince($geographic);
        $city = null;
        if ($province === null) {
            $city = $regional->findCity($geographic);
        }
        abort_if($province === null && $city === null, 404, 'Wilayah tidak ditemukan.');

        if ($province !== null) {
            $contextName = 'Provinsi '.$province['name'];
            $landingUrl = route('regional.province', $province['slug']);
            $geographicSlug = $province['slug'];
        } else {
            $contextName = $city['name'];
            $landingUrl = route('regional.city', $city['slug']);
            $geographicSlug = $city['slug'];
        }

        $canonicalBase = rtrim((string) config('app.url'), '/');
        $context = [
            'name' => $contextName,
            'landingUrl' => $landingUrl,
            'canonicalUrl' => $canonicalBase.route('geographic.service.show', ['geographic' => $geographicSlug, 'service' => $programData['slug']], false),
            'slug' => $geographicSlug,
        ];

        $relatedPrograms = collect($this->schemes->all())
            ->reject(fn (array $item): bool => $item['slug'] === $programData['slug'])
            ->take(6)->all();

        $articles = collect();
        if (Schema::hasTable('articles') && Schema::hasColumn('articles', 'service_slug')) {
            $articles = \App\Models\Article::published()->where('service_slug', $programData['slug'])
                ->latest('published_at')->take(6)->get();
        }

        return view('programs.show', [
            'program' => $programData,
            'relatedPrograms' => $relatedPrograms,
            'otherPrograms' => $relatedPrograms,
            'region' => $context,
            'articles' => $articles,
            'pageTitle' => $programData['title'].' '.$contextName,
            'pageDescription' => 'Informasi '.$programData['title'].' untuk '.$contextName.'. Tinjau ruang lingkup dan rincian yang tersedia dalam katalog layanan Clameverg.',
            'waMessage' => 'Halo, saya ingin menanyakan informasi '.$programData['title'].' untuk '.$contextName.'.',
        ]);
    }

}
