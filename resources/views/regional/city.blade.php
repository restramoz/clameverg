@extends('layouts.app')

@section('title', 'Pelatihan & Sertifikasi di ' . $city['name'])
@section('meta_description', 'Informasi katalog layanan dengan konteks ' . $city['name'] . '. Tinjau ruang lingkup umum dan rincian yang tercantum pada setiap layanan.')
@section('wa_message', $waMessage)

@section('content')
<noscript>
    <style>.scheme-extra[hidden]{display:block!important;}</style>
</noscript>
    {{-- Breadcrumb --}}
    <div class="bg-neutral-100 border-b border-neutral-200">
        <x-breadcrumb :items="[
            ['label' => 'Wilayah', 'url' => null],
            ['label' => $city['province'], 'url' => route('regional.province', $city['province_slug'])],
            ['label' => $city['name'], 'url' => null],
        ]" />
    </div>

    {{-- Hero --}}
    <section class="relative bg-industry-black text-white overflow-hidden border-b border-dark-mahogany" aria-labelledby="city-hero-heading">
        <div class="absolute inset-0 opacity-10 pointer-events-none" aria-hidden="true">
            <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
                <defs><pattern id="grid-c" width="40" height="40" patternUnits="userSpaceOnUse"><path d="M 40 0 L 0 0 0 40" fill="none" stroke="#FFF" stroke-width="1"/></pattern></defs>
                <rect width="100%" height="100%" fill="url(#grid-c)" />
            </svg>
        </div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 lg:py-20">
            <div class="max-w-3xl">
                <!-- Mobile hero image - Responsive with aspect-ratio -->
                <div class="lg:hidden mb-5 -mx-4 sm:-mx-6 lg:-mx-8">
                    <img src="/images/home/feature-lineworker-ppe.jpg" alt="Tenaga profesional industri bekerja dengan perlengkapan keselamatan" width="1600" height="900" loading="eager" fetchpriority="high" decoding="async" class="hero-image-mobile w-full object-cover object-center rounded-xl" />
                </div>
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-burnt-brick border border-burnt-brick/80 text-white text-xs sm:text-sm font-semibold mb-5 shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-safety-orange animate-pulse"></span>
                    Skema Kompetensi &amp; Layanan Konsultasi
                </div>
                <h1 id="city-hero-heading" class="font-extrabold tracking-tight text-white leading-tight uppercase text-[clamp(1.75rem,4.5vw,2.5rem)] sm:text-[clamp(2rem,5vw,3rem)] lg:text-[clamp(2.25rem,4.5vw,3.5rem)]">
                    Skema Kompetensi Clameverg<br><span class="text-safety-orange">{{ $city['name'] }}</span>
                </h1>
                <p class="mt-6 text-base sm:text-lg text-neutral-200 leading-relaxed">
                    Informasi katalog layanan dengan konteks <strong class="text-white">{{ $city['name'] }}</strong>, Provinsi {{ $city['province'] }}. Nama wilayah ini tidak menyatakan adanya kantor atau penyelenggaraan setempat.
                </p>
                <div class="mt-7 flex flex-col sm:flex-row gap-3 sm:gap-4 w-full">
                    <x-wa-cta variant="primary" size="lg" :message="$waMessage" label="Konsultasi Wilayah {{ $city['name'] }}" class="w-full sm:w-auto font-bold shadow-lg" />
                </div>
                <div class="mt-8 pt-6 border-t border-neutral-800 grid grid-cols-2 gap-4 sm:gap-6">
                    <div class="min-w-0"><div class="text-[10px] sm:text-xs text-neutral-400 uppercase tracking-wider">Provinsi</div><div class="text-sm sm:text-base font-extrabold text-white mt-0.5 truncate">{{ $city['province'] }}</div></div>
                    <div class="min-w-0"><div class="text-[10px] sm:text-xs text-neutral-400 uppercase tracking-wider">Pulau</div><div class="text-sm sm:text-base font-extrabold text-amber-500 mt-0.5">{{ $city['island'] }}</div></div>
                    <div class="min-w-0"><div class="text-[10px] sm:text-xs text-neutral-400 uppercase tracking-wider">Layanan</div><div class="text-sm sm:text-base font-extrabold text-white mt-0.5">{{ count($programs) }} Skema</div></div>
                    <div class="min-w-0"><div class="text-[10px] sm:text-xs text-neutral-400 uppercase tracking-wider">Program</div><div class="text-sm sm:text-base font-extrabold text-amber-500 mt-0.5">Pelatihan &amp; Sertifikasi</div></div>
                </div>
            </div>
        </div>
    </section>

    {{-- Disclaimer --}}
    <section class="py-10 bg-neutral-50 border-b border-neutral-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-start gap-4">
                <svg class="w-6 h-6 text-burnt-brick shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <p class="text-sm text-dark-mahogany leading-relaxed">
                    <strong class="text-industry-black">Catatan penting:</strong> Halaman ini adalah halaman informasi dan konsultasi program. Informasi program yang tercantum dapat ditinjau pada halaman ini. Jadwal dan penyelenggaraan perlu dikonfirmasi sesuai program yang diminati.
                </p>
            </div>
        </div>
    </section>

    @php
        $schemeService = app(\App\Services\SchemeService::class);
        $allPrograms = $programs;
        $categories = collect($programs)->groupBy(fn ($item) => $item['category_label'] ?? 'Layanan Lainnya')->map(function ($items, $categoryName) use ($schemeService) {
            return [
                'name' => $categoryName,
                'slug' => \Illuminate\Support\Str::slug($categoryName),
                'items' => $items,
                'count' => $items->count(),
                'meta' => $schemeService::categoryMetadata($categoryName),
            ];
        })->sortBy('name')->values();
        $totalServices = count($programs);
        $waPhone = \App\Support\WhatsApp::normalizePhone(config('services.whatsapp.phone'));
    @endphp

    @include('components.service-catalog', ['catalogContext' => 'city', 'catalogGeographicSlug' => $city['slug']])

    {{-- Proses & Syarat Umum --}}
    <section id="proses-syarat" class="py-14 sm:py-20 bg-neutral-50 border-b border-neutral-200" aria-labelledby="city-proses-heading">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mb-8">
                <span class="text-xs font-bold uppercase tracking-wider text-burnt-brick bg-neutral-100 px-3 py-1 rounded-full border border-burnt-brick/20">Proses &amp; Syarat</span>
                <h2 id="city-proses-heading" class="mt-3 font-extrabold text-industry-black tracking-tight text-[clamp(1.5rem,4vw,2rem)] sm:text-[clamp(1.75rem,4.5vw,2.25rem)]">
                    Proses &amp; Syarat Umum
                </h2>
                <p class="mt-3 text-sm sm:text-base text-dark-mahogany leading-relaxed">
                    Untuk mengetahui proses, persyaratan, materi, dan informasi layanan secara lengkap, silakan lihat halaman layanan terkait atau hubungi Clameverg melalui kanal resmi.
                </p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-4xl">
                <div class="bg-white rounded-xl border border-neutral-200 p-5">
                    <h3 class="font-bold text-industry-black mb-2">Rincian layanan</h3>
                    <p class="text-sm text-dark-mahogany leading-relaxed">Rincian proses, persyaratan, dan materi setiap layanan dapat dilihat pada halaman layanan terkait.</p>
                </div>
                <div class="bg-white rounded-xl border border-neutral-200 p-5">
                    <h3 class="font-bold text-industry-black mb-2">Konsultasi</h3>
                    <p class="text-sm text-dark-mahogany leading-relaxed">Untuk rincian lebih lanjut, hubungi Clameverg melalui kanal resmi WhatsApp. Tautan WhatsApp tersedia pada halaman ini dan di halaman layanan.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- FAQ --}}
    <section id="faq" class="py-14 sm:py-20 bg-white border-b border-neutral-200" aria-labelledby="city-faq-heading">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <span class="text-xs font-bold uppercase tracking-wider text-burnt-brick bg-neutral-100 px-3 py-1 rounded-full border border-burnt-brick/20">Tanya Jawab</span>
                <h2 id="city-faq-heading" class="mt-3 text-2xl font-extrabold text-industry-black tracking-tight">Pertanyaan Umum: Layanan di {{ $city['name'] }}</h2>
            </div>
            <div class="space-y-4">
                <details class="group bg-white rounded-xl border border-neutral-200 p-5 sm:p-6 open:border-burnt-brick shadow-xs">
                    <summary class="flex justify-between items-center gap-4 font-bold text-industry-black cursor-pointer list-none focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-burnt-brick rounded">
                        <span>Bagaimana cara mendapatkan informasi layanan di {{ $city['name'] }}?</span>
                        <span class="w-7 h-7 rounded-full bg-neutral-100 flex items-center justify-center shrink-0 group-open:rotate-180 transition-transform">
                            <svg class="w-4 h-4 text-burnt-brick" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </span>
                    </summary>
                    <p class="mt-4 pt-4 border-t border-neutral-100 text-sm text-dark-mahogany leading-relaxed">Telusuri katalog layanan di halaman ini, lalu buka halaman detail layanan terkait untuk melihat rincian. Hubungi Clameverg melalui kanal resmi untuk konsultasi.</p>
                </details>
                <details class="group bg-white rounded-xl border border-neutral-200 p-5 sm:p-6 open:border-burnt-brick shadow-xs">
                    <summary class="flex justify-between items-center gap-4 font-bold text-industry-black cursor-pointer list-none focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-burnt-brick rounded">
                        <span>Bagaimana cara mengetahui persyaratan program?</span>
                        <span class="w-7 h-7 rounded-full bg-neutral-100 flex items-center justify-center shrink-0 group-open:rotate-180 transition-transform">
                            <svg class="w-4 h-4 text-burnt-brick" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </span>
                    </summary>
                    <p class="mt-4 pt-4 border-t border-neutral-100 text-sm text-dark-mahogany leading-relaxed">Persyaratan setiap program dapat dilihat pada halaman layanan terkait. Untuk rincian lebih lanjut, hubungi Clameverg melalui kanal resmi.</p>
                </details>
                <details class="group bg-white rounded-xl border border-neutral-200 p-5 sm:p-6 open:border-burnt-brick shadow-xs">
                    <summary class="flex justify-between items-center gap-4 font-bold text-industry-black cursor-pointer list-none focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-burnt-brick rounded">
                        <span>Bagaimana cara mengetahui materi atau silabus layanan?</span>
                        <span class="w-7 h-7 rounded-full bg-neutral-100 flex items-center justify-center shrink-0 group-open:rotate-180 transition-transform">
                            <svg class="w-4 h-4 text-burnt-brick" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </span>
                    </summary>
                    <p class="mt-4 pt-4 border-t border-neutral-100 text-sm text-dark-mahogany leading-relaxed">Materi atau silabus layanan tersedia pada halaman detail layanan terkait. Hubungi Clameverg untuk penjelasan lebih rinci.</p>
                </details>
                <details class="group bg-white rounded-xl border border-neutral-200 p-5 sm:p-6 open:border-burnt-brick shadow-xs">
                    <summary class="flex justify-between items-center gap-4 font-bold text-industry-black cursor-pointer list-none focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-burnt-brick rounded">
                        <span>Bagaimana cara menghubungi Clameverg?</span>
                        <span class="w-7 h-7 rounded-full bg-neutral-100 flex items-center justify-center shrink-0 group-open:rotate-180 transition-transform">
                            <svg class="w-4 h-4 text-burnt-brick" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </span>
                    </summary>
                    <p class="mt-4 pt-4 border-t border-neutral-100 text-sm text-dark-mahogany leading-relaxed">Gunakan tombol WhatsApp yang tersedia pada halaman ini atau halaman layanan. Seluruh konsultasi program disampaikan melalui kanal resmi Clameverg.</p>
                </details>
                <details class="group bg-white rounded-xl border border-neutral-200 p-5 sm:p-6 open:border-burnt-brick shadow-xs">
                    <summary class="flex justify-between items-center gap-4 font-bold text-industry-black cursor-pointer list-none focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-burnt-brick rounded">
                        <span>Apakah informasi layanan dapat dikonsultasikan terlebih dahulu?</span>
                        <span class="w-7 h-7 rounded-full bg-neutral-100 flex items-center justify-center shrink-0 group-open:rotate-180 transition-transform">
                            <svg class="w-4 h-4 text-burnt-brick" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </span>
                    </summary>
                    <p class="mt-4 pt-4 border-t border-neutral-100 text-sm text-dark-mahogany leading-relaxed">Ya. Anda dapat menghubungi Clameverg terlebih dahulu untuk membahas program yang diminati. Konsultasi tidak menjanjikan penetapan program tanpa konfirmasi.</p>
                </details>
            </div>
        </div>
    </section>


    <x-regional-map :citySlug="$city['slug']" :regionName="$city['name']" />

    {{-- Articles --}}
    @if(isset($articles) && count($articles) > 0)
    <section id="artikel" class="py-14 sm:py-20 bg-white border-b border-neutral-200" aria-labelledby="city-article-heading">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 id="city-article-heading" class="text-2xl font-extrabold text-industry-black mb-2">
                Artikel &amp; Panduan di {{ $city['name'] }}
            </h2>
            <p class="text-sm text-dark-mahogany mb-8">Kumpulan artikel edukatif terkait skema kompetensi dan Clameverg untuk wilayah {{ $city['name'] }}.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($articles as $art)
                <a href="{{ route('articles.show', $art->slug) }}" class="group flex flex-col bg-white rounded-xl border border-neutral-200 hover:border-burnt-brick hover:shadow-md transition-all overflow-hidden focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-burnt-brick">
                    {{-- Featured Image --}}
                    @if($art->featured_image)
                        <div class="relative overflow-hidden bg-neutral-100 aspect-video max-h-[240px]">
                            <img
                                src="/{{ ltrim($art->featured_image, '/') }}"
                                alt="{{ $art->title }}"
                                class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-300"
                                loading="lazy"
                            >
                        </div>
                    @else
                        <div class="aspect-video bg-gradient-to-br from-neutral-100 to-neutral-200 flex items-center justify-center">
                            <svg class="w-10 h-10 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                    @endif
                    <div class="p-6 flex-1 flex flex-col">
                        <h3 class="text-lg font-bold text-industry-black mb-2 group-hover:text-burnt-brick transition-colors line-clamp-2">{{ $art->title }}</h3>
                        <p class="text-xs sm:text-sm text-dark-mahogany flex-1 leading-relaxed line-clamp-3">{{ $art->excerpt }}</p>
                        <div class="mt-4 pt-4 border-t border-neutral-100 text-xs font-bold text-burnt-brick flex items-center justify-between">
                            <span>Baca Selengkapnya</span>
                            <span>&rarr;</span>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- Nearby Cities --}}
    @if(count($nearbyCities) > 0)
    <section class="py-14 sm:py-20 bg-neutral-50 border-b border-neutral-200" aria-labelledby="city-nearby-heading">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 id="city-nearby-heading" class="text-xl font-extrabold text-industry-black mb-6">
                Kabupaten &amp; Kota Lain di Provinsi {{ $city['province'] }}
            </h2>
            <div class="flex flex-wrap gap-3">
                @foreach($nearbyCities as $c)
                <a href="{{ route('regional.city', $c['slug']) }}" class="inline-flex px-4 py-2 rounded-full bg-white border border-neutral-200 hover:border-burnt-brick text-sm text-dark-mahogany hover:text-burnt-brick transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-burnt-brick">
                    {{ $c['name'] }}
                </a>
                @endforeach
                <a href="{{ route('regional.province', $city['province_slug']) }}" class="inline-flex px-4 py-2 rounded-full bg-white border border-burnt-brick text-sm font-semibold text-burnt-brick hover:bg-neutral-100 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-burnt-brick">
                    Lihat semua Provinsi {{ $city['province'] }} &rarr;
                </a>
            </div>
        </div>
    </section>
    @endif

    {{-- Final CTA --}}
    <section class="py-16 sm:py-24 bg-industry-black text-white" aria-labelledby="city-cta-heading">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="max-w-3xl mx-auto">
                <h2 id="city-cta-heading" class="text-2xl sm:text-3xl font-extrabold uppercase tracking-tight">
                    Konsultasi Program Clameverg<br><span class="text-safety-orange">{{ $city['name'] }}</span>
                </h2>
                <p class="mt-4 text-base text-neutral-200 leading-relaxed">
                    Hubungi kami untuk menanyakan informasi program pelatihan dan sertifikasi yang tercantum dalam katalog Clameverg.
                </p>
                <div class="mt-7 flex flex-col sm:flex-row items-center justify-center gap-3 sm:gap-4 w-full">
                    <x-wa-cta variant="primary" size="lg" :message="$waMessage" label="Konsultasi Sekarang" class="w-full sm:w-auto font-bold shadow-lg" />
                    <x-button :href="route('regional.province', $city['province_slug'])" variant="outline" size="lg" class="w-full sm:w-auto text-white border-2 border-neutral-400 hover:bg-neutral-800">Provinsi {{ $city['province'] }}</x-button>
                </div>
                <div class="mt-10 pt-8 border-t border-neutral-800 flex flex-wrap justify-center gap-4 text-xs text-neutral-400">
                    <a href="{{ route('home') }}#pelatihan" class="hover:text-amber-500 transition-colors">Skema Kompetensi</a>
                    <span>•</span>
                    <a href="{{ route('home') }}#jasa" class="hover:text-amber-500 transition-colors">Layanan Konsultasi</a>
                    <span>•</span>
                    <a href="{{ route('regional.province', $city['province_slug']) }}" class="hover:text-amber-500 transition-colors">Provinsi {{ $city['province'] }}</a>
                </div>
            </div>
        </div>
    </section>



@endsection
