@extends('layouts.app')

@section('title', 'Layanan Pelatihan & Sertifikasi')

@section('meta_description', 'Jelajahi katalog Devtyeng yang memuat program pelatihan, pengembangan profesional, dan sertifikasi dari berbagai bidang. Tinjau informasi layanan dan konsultasikan kebutuhan Anda.')

@section('content')
@php
    $regionalService = resolve(\App\Services\RegionalService::class);
    $provinces = $regionalService->getAllProvinces();

    $schemeService = app(\App\Services\SchemeService::class);
    $allPrograms = $schemeService->all();

    /*
     * Kelompokkan berdasarkan kategori asli dari layanan.json.
     * Menggunakan metadata representatif untuk setiap kategori.
     */
    $categories = collect($allPrograms)
        ->groupBy(fn ($item) => $item['category_label'] ?? 'Layanan Lainnya')
        ->map(function ($items, $categoryName) use ($schemeService) {
            $slug = \Illuminate\Support\Str::slug($categoryName);
            return [
                'name' => $categoryName,
                'slug' => $slug,
                'items' => $items,
                'count' => $items->count(),
                'meta' => $schemeService::categoryMetadata($categoryName),
            ];
        })
        ->sortBy('name')
        ->values();

    $totalServices = count($allPrograms);
    $totalCategories = $categories->count();

    // Query artikel terpublikasi asli dari database
    $latestArticles = $latestArticles ?? (
        \Illuminate\Support\Facades\Schema::hasTable('articles')
            ? \App\Models\Article::published()->orderBy('published_at', 'desc')->take(3)->get()
            : collect()
    );

    $waPhone = \App\Support\WhatsApp::normalizePhone(config('services.whatsapp.phone'));
@endphp

{{-- =========================================================
     A. HERO SECTION
========================================================= --}}
<section id="home"
         class="relative overflow-hidden bg-[#0F172A] text-white"
         aria-labelledby="hero-heading">

    <!-- Industrial Ambient Accents -->
    <div class="pointer-events-none absolute -right-32 -top-32 h-96 w-96 rounded-full bg-[#B45309]/10 blur-3xl"
         aria-hidden="true"></div>
    <div class="pointer-events-none absolute -bottom-40 -left-20 h-96 w-96 rounded-full bg-[#1E40AF]/10 blur-3xl"
         aria-hidden="true"></div>

    <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 items-center gap-10 lg:grid-cols-12 lg:gap-12 xl:gap-16">

            <!-- Hero Text Content -->
            <div class="flex flex-col justify-center py-12 sm:py-16 lg:col-span-7 lg:py-24 xl:py-28">

                <span class="inline-flex self-start items-center gap-2 rounded-full border border-[#B45309]/30 bg-[#B45309]/10 px-4 py-2 text-xs font-bold tracking-wide text-amber-300 sm:text-sm">
                    <span class="h-2 w-2 rounded-full bg-[#B45309]"></span>
                    Devtyeng · Layanan Pelatihan &amp; Sertifikasi
                </span>

                <h1 id="hero-heading"
                    class="mt-6 text-3xl font-black leading-tight tracking-tight sm:text-5xl xl:text-6xl text-white">
                    Katalog Layanan &amp; Pelatihan
                    <span class="text-amber-300">Industri Terpadu</span>
                </h1>

                <p class="mt-6 max-w-2xl text-sm leading-relaxed text-[#C7CBD2] sm:text-lg">
                    Jelajahi katalog program pelatihan, pengembangan profesional, dan sertifikasi dari berbagai bidang. Pelajari informasi layanan dan konsultasikan kebutuhan Anda.
                </p>

                <!-- Hero Actions -->
                <div class="mt-8 flex flex-col gap-3.5 sm:flex-row">
                    <x-wa-cta
                        variant="primary"
                        size="lg"
                        label="Konsultasi via WhatsApp"
                        class="w-full bg-[#B45309] font-black text-white shadow-lg hover:bg-[#92400E] hover:text-white sm:w-auto"
                    />

                    <a href="#layanan"
                       class="inline-flex min-h-[48px] items-center justify-center rounded-xl border border-white/20 px-6 py-3 text-sm font-bold text-white transition hover:border-[#B45309] hover:text-[#B45309] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#B45309] sm:text-base">
                        Jelajahi Katalog Layanan
                        <span class="ml-2" aria-hidden="true">&darr;</span>
                    </a>
                </div>

                <!-- Hero Statistics -->
                <div class="mt-10 grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4">
                    <div class="rounded-2xl border border-white/10 bg-[#1E293B]/70 p-4 backdrop-blur-sm">
                        <p class="text-2xl font-black text-[#B45309] sm:text-3xl">
                            {{ number_format($totalServices, 0, ',', '.') }}
                        </p>
                        <p class="mt-1 text-xs leading-relaxed text-[#AEB4BE] sm:text-sm">
                            Layanan dalam Katalog
                        </p>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-[#1E293B]/70 p-4 backdrop-blur-sm">
                        <p class="text-2xl font-black text-white sm:text-3xl">
                            Beragam
                        </p>
                        <p class="mt-1 text-xs leading-relaxed text-[#AEB4BE] sm:text-sm">
                            Topik Layanan
                        </p>
                    </div>

                    <div class="col-span-2 sm:col-span-1 rounded-2xl border border-white/10 bg-[#1E293B]/70 p-4 backdrop-blur-sm">
                        <p class="text-2xl font-black text-white sm:text-3xl">
                            {{ count($provinces) }}
                        </p>
                        <p class="mt-1 text-xs leading-relaxed text-[#AEB4BE] sm:text-sm">
                            Provinsi dalam Direktori
                        </p>
                    </div>
                </div>
            </div>

            <!-- Hero Photography Visual -->
            <div class="relative pb-10 lg:col-span-5 lg:pb-0">
                <div class="relative overflow-hidden rounded-3xl border border-white/10 bg-[#1E293B] shadow-2xl">
                    <img src="/images/home/hero-industrial.jpg"
                         alt="Tenaga profesional industri bekerja di fasilitas teknologi modern"
                         width="1200"
                         height="900"
                         loading="eager"
                         fetchpriority="high"
                         decoding="async"
                         onerror="this.onerror=null;this.src='/images/home/sumber-teknisi.jpg';"
                         class="h-[300px] w-full object-cover sm:h-[420px] lg:h-[520px]" />

                    <div class="absolute inset-0 bg-gradient-to-t from-[#0F172A] via-[#0F172A]/50 to-transparent"
                         aria-hidden="true"></div>

                    <div class="absolute inset-x-0 bottom-0 p-5 sm:p-7">
                        <span class="inline-flex rounded-full border border-[#B45309]/30 bg-[#0F172A]/80 px-3 py-1.5 text-xs font-bold text-[#B45309] backdrop-blur-md">
                            Katalog Devtyeng
                        </span>

                        <h2 class="mt-3 text-xl font-black leading-snug text-white sm:text-2xl">
                            Referensi Program untuk Pengembangan Kompetensi
                        </h2>

                        <p class="mt-2 text-xs leading-relaxed text-[#C7CBD2] sm:text-sm">
                            Temukan informasi program dan materi yang tercantum dalam katalog, lalu konsultasikan kebutuhan Anda.
                        </p>
                    </div>
                </div>

                <div class="pointer-events-none absolute -bottom-3 -left-3 -z-0 h-28 w-28 rounded-3xl border border-[#B45309]/30 sm:-bottom-4 sm:-left-4"
                     aria-hidden="true"></div>
            </div>

        </div>
    </div>
</section>


{{-- =========================================================
     B. SERVICE CATEGORY CATALOG (MAIN PRIORITY)
========================================================= --}}
<section id="layanan"
         class="bg-[#F8FAFC] py-14 sm:py-20 lg:py-24"
         aria-labelledby="layanan-heading">

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

        <!-- Section Header -->
        <div class="mx-auto max-w-3xl text-center">
            <span class="inline-flex items-center gap-2 text-xs font-extrabold uppercase tracking-[0.2em] text-[#006E5E] sm:text-sm">
                <span class="h-1.5 w-6 rounded-full bg-[#B45309]"></span>
                Katalog Layanan &amp; Pelatihan
            </span>

            <h2 id="layanan-heading"
                class="mt-4 text-2xl font-black tracking-tight text-[#0F172A] sm:text-3xl lg:text-4xl">
                Jelajahi Katalog
                <span class="text-[#006E5E]">Pelatihan &amp; Sertifikasi</span>
            </h2>

            <p class="mx-auto mt-4 max-w-2xl text-sm leading-relaxed text-[#535B66] sm:text-base">
                Telusuri daftar layanan yang tersedia dan buka halaman informasi untuk melihat rincian setiap program.
            </p>

            <div class="mt-5 inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-xs font-bold text-[#1E293B] shadow-sm border border-[#E1E5EA]">
                <span class="h-2 w-2 rounded-full bg-[#B45309]"></span>
                {{ number_format($totalServices, 0, ',', '.') }} Entri Layanan
            </div>
        </div>

        <!-- Service Catalog -->
        <div class="mt-12 grid grid-cols-1 items-start gap-6 md:grid-cols-2 xl:grid-cols-3">

            @foreach ($categories as $category)
                @php
                    $categorySlug = $category['slug'];
                    $items = $category['items'];
                    $total = $category['count'];
                    $catMeta = $category['meta'];
                    $listId = 'category-list-' . $categorySlug;
                @endphp

                <article data-scheme-section="{{ $categorySlug }}"
                         data-scheme-category="{{ $category['name'] }}"
                         data-show-group
                         class="group flex min-w-0 flex-col overflow-hidden rounded-2xl border border-[#E1E5EA] bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:border-[#B45309]/50">

                    <!-- Category Card Top: Representative Image & Dark Overlay -->
                    <div class="relative aspect-[16/10] overflow-hidden bg-[#0F172A]">
                        <img src="{{ $catMeta['image'] }}"
                             alt="{{ $catMeta['alt'] }}"
                             width="800"
                             height="500"
                             loading="lazy"
                             decoding="async"
                             onerror="this.onerror=null;this.src='/images/home/hero-industrial.jpg';"
                             class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" />

                        <!-- Dark Overlay for High Text Contrast -->
                        <div class="absolute inset-0 bg-gradient-to-t from-[#0F172A] via-[#0F172A]/50 to-transparent"
                             aria-hidden="true"></div>

                        <!-- Service Count Badge (Top-Right) -->
                        <div class="absolute top-3.5 right-3.5">
                            <span class="inline-flex items-center gap-1.5 rounded-full border border-white/20 bg-[#0F172A]/85 px-3 py-1 text-xs font-black text-white backdrop-blur-md shadow-sm">
                                <span class="h-1.5 w-1.5 rounded-full bg-[#B45309]"></span>
                                {{ number_format($total, 0, ',', '.') }} Layanan
                            </span>
                        </div>

                        <!-- Category Title & Subject Label (Bottom-Left) -->
                        <div class="absolute inset-x-0 bottom-0 p-5">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-amber-300">
                                Layanan dalam Katalog
                            </span>
                            <h3 class="mt-1 text-lg font-black leading-snug text-white sm:text-xl drop-shadow-sm">
                                {{ $category['name'] }}
                            </h3>
                        </div>
                    </div>

                    <!-- Category Card Body -->
                    <div class="flex flex-1 flex-col p-5">

                        <!-- Contextual Description -->
                        <p class="text-xs sm:text-sm text-[#535B66] leading-relaxed mb-4">
                            {{ $catMeta['description'] }}
                        </p>

                        <!-- Contextual WhatsApp Consultation for Category -->
                        @if ($waPhone)
                            <a href="https://wa.me/{{ $waPhone }}?text={{ rawurlencode('Halo, saya ingin konsultasi mengenai layanan pada kategori ' . $category['name'] . '.') }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="mb-4 inline-flex items-center justify-between gap-2 rounded-xl border border-[#E1E5EA] bg-[#F8FAFB] px-3.5 py-2.5 text-xs font-bold text-[#0F172A] transition hover:border-[#B45309] hover:bg-white"
                               aria-label="Konsultasi kategori {{ $category['name'] }} via WhatsApp">
                                <span class="truncate">Konsultasi Kategori Ini</span>
                                <span class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-[#25D366] text-white">
                                    <svg class="h-3.5 w-3.5 fill-current" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M12.031 2C6.496 2 2 6.484 2 12.016c0 1.954.563 3.781 1.547 5.344L2.094 22l4.781-1.422a9.98 9.98 0 0 0 5.156 1.438c5.531 0 10.031-4.484 10.031-10.016C22.062 6.484 17.562 2 12.031 2zm5.844 14.281c-.25.688-1.25 1.344-1.781 1.406-.469.063-1.078.094-3.469-.906-2.906-1.219-4.75-4.188-4.906-4.375-.125-.188-1.188-1.594-1.188-3.031 0-1.438.75-2.156 1.031-2.438.281-.281.625-.344.844-.344.219 0 .438.016.625.016.219 0 .484-.078.75.563.281.688.938 2.313 1.031 2.484.094.188.156.406.031.625-.125.219-.188.344-.375.563-.188.219-.406.469-.578.625-.188.188-.375.406-.156.781.219.375.969 1.594 2.094 2.594 1.438 1.281 2.656 1.688 3.031 1.875.375.188.594.156.813-.094.219-.25.938-1.094 1.188-1.469.25-.375.5-.313.844-.188.344.125 2.188 1.031 2.563 1.219.375.188.625.281.719.438.094.156.094.906-.156 1.594z"/>
                                    </svg>
                                </span>
                            </a>
                        @endif

                        <!-- Services List Inside Category -->
                        <ul id="{{ $listId }}"
                            class="space-y-2 flex-1"
                            role="list">

                            @foreach ($items as $item)
                                @php
                                    $programSlug = $item['slug'] ?? null;
                                @endphp

                                @if ($programSlug)
                                    <li class="scheme-item {{ $loop->index >= 6 ? 'scheme-extra' : '' }}"
                                        @if ($loop->index >= 6) hidden @endif>

                                        <div class="flex min-w-0 items-center justify-between gap-2.5 rounded-xl border border-[#E8EBEF] bg-[#F8FAFB] px-3.5 py-2.5 transition hover:border-[#B45309]/50 hover:bg-white">

                                            <!-- Service Title Link -->
                                            <a href="{{ route('program.show', $programSlug) }}"
                                               class="min-w-0 flex-1 text-sm font-semibold text-[#0F172A] hover:text-[#008F7C] transition-colors leading-snug focus-visible:rounded focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#B45309]">
                                                {{ $item['title'] }}
                                            </a>

                                            <!-- Actions: Detail Link & WhatsApp Quick Action -->
                                            <div class="flex shrink-0 items-center gap-2">
                                                <a href="{{ route('program.show', $programSlug) }}"
                                                   class="inline-flex items-center text-xs font-bold text-[#006E5E] hover:text-[#0F172A] px-2 py-1 rounded transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#B45309]"
                                                   aria-label="Lihat detail {{ $item['title'] }}">
                                                    Detail
                                                    <span class="ml-1" aria-hidden="true">&rarr;</span>
                                                </a>

                                                @if ($waPhone)
                                                    <a href="https://wa.me/{{ $waPhone }}?text={{ rawurlencode($item['wa_message'] ?? ('Halo, saya ingin konsultasi mengenai ' . $item['title'] . '.')) }}"
                                                       target="_blank"
                                                       rel="noopener noreferrer"
                                                       class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-[#25D366]/10 text-[#25D366] hover:bg-[#25D366] hover:text-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#25D366]"
                                                       title="Konsultasi {{ $item['title'] }} via WhatsApp"
                                                       aria-label="Konsultasi {{ $item['title'] }} via WhatsApp">
                                                        <svg class="h-3.5 w-3.5 fill-current" viewBox="0 0 24 24" aria-hidden="true">
                                                            <path d="M12.031 2C6.496 2 2 6.484 2 12.016c0 1.954.563 3.781 1.547 5.344L2.094 22l4.781-1.422a9.98 9.98 0 0 0 5.156 1.438c5.531 0 10.031-4.484 10.031-10.016C22.062 6.484 17.562 2 12.031 2zm5.844 14.281c-.25.688-1.25 1.344-1.781 1.406-.469.063-1.078.094-3.469-.906-2.906-1.219-4.75-4.188-4.906-4.375-.125-.188-1.188-1.594-1.188-3.031 0-1.438.75-2.156 1.031-2.438.281-.281.625-.344.844-.344.219 0 .438.016.625.016.219 0 .484-.078.75.563.281.688.938 2.313 1.031 2.484.094.188.156.406.031.625-.125.219-.188.344-.375.563-.188.219-.406.469-.578.625-.188.188-.375.406-.156.781.219.375.969 1.594 2.094 2.594 1.438 1.281 2.656 1.688 3.031 1.875.375.188.594.156.813-.094.219-.25.938-1.094 1.188-1.469.25-.375.5-.313.844-.188.344.125 2.188 1.031 2.563 1.219.375.188.625.281.719.438.094.156.094.906-.156 1.594z"/>
                                                        </svg>
                                                    </a>
                                                @endif
                                            </div>

                                        </div>

                                    </li>
                                @endif
                            @endforeach

                        </ul>

                        <!-- Show All Toggle for Services Beyond 6 -->
                        @if ($total > 6)
                            <button type="button"
                                    class="js-show-all mt-4 inline-flex min-h-[44px] w-full items-center justify-center gap-2 rounded-xl border border-[#B45309]/50 bg-white px-4 py-2.5 text-sm font-bold text-[#006E5E] transition-all hover:bg-[#B45309] hover:text-[#0F172A] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#B45309]"
                                    data-all-label="Lihat Semua {{ $total }} Layanan"
                                    data-less-label="Tampilkan Lebih Sedikit"
                                    aria-expanded="false"
                                    aria-controls="{{ $listId }}">
                                Lihat Semua {{ $total }} Layanan
                                <span aria-hidden="true">&darr;</span>
                            </button>
                        @endif

                    </div>
                </article>
            @endforeach

        </div>

        @if ($totalServices === 0)
            <div class="mt-8 rounded-2xl border border-[#E1E5EA] bg-white p-8 text-center">
                <p class="font-semibold text-[#1E293B]">
                    Katalog layanan belum tersedia.
                </p>
                <p class="mt-2 text-sm text-[#535B66]">
                    Silakan hubungi kami untuk informasi lebih lanjut.
                </p>
            </div>
        @endif

    </div>
</section>


{{-- =========================================================
     C. TENTANG LAYANAN KAMI (KEUNGGULAN)
========================================================= --}}
<section id="keunggulan"
         class="overflow-hidden bg-[#0F172A] text-white"
         aria-labelledby="keunggulan-heading">

    <div class="grid grid-cols-1 lg:grid-cols-2">

        <!-- Industrial Workforce Image -->
        <div class="relative min-h-[300px] overflow-hidden sm:min-h-[400px] lg:min-h-[620px]">
            <img src="/images/home/feature-lineworker-ppe.jpg"
                 alt="Tenaga ahli keselamatan kerja industri dengan perlengkapan APD lengkap"
                 width="1200"
                 height="1600"
                 loading="lazy"
                 decoding="async"
                 onerror="this.onerror=null;this.src='/images/home/sumber-teknisi.jpg';"
                 class="absolute inset-0 h-full w-full object-cover" />

            <div class="absolute inset-0 bg-gradient-to-t from-[#0F172A]/90 via-[#0F172A]/40 to-transparent lg:bg-gradient-to-r lg:from-transparent lg:to-[#0F172A]"
                 aria-hidden="true"></div>

            <div class="absolute bottom-0 left-0 right-0 p-6 lg:hidden">
                <span class="text-xs font-bold uppercase tracking-widest text-[#B45309]">
                    Devtyeng
                </span>
                <h3 class="mt-2 text-2xl font-black text-white">
                    Standar Kompetensi &amp; Layanan Terarah
                </h3>
            </div>
        </div>

        <!-- High-Contrast Editorial Content -->
        <div class="flex flex-col justify-center px-6 py-14 sm:px-10 sm:py-18 lg:px-14 lg:py-20 xl:px-18">

            <span class="inline-flex self-start items-center gap-2 text-xs font-extrabold uppercase tracking-[0.2em] text-[#B45309] sm:text-sm">
                <span class="h-1.5 w-6 rounded-full bg-[#B45309]"></span>
                Tentang Layanan Kami
            </span>

            <h2 id="keunggulan-heading"
                class="mt-4 max-w-xl text-2xl font-black leading-tight tracking-tight text-white sm:text-3xl lg:text-4xl">
                Solusi Pelatihan &amp; Layanan Sesuai Kebutuhan Nyata
            </h2>

            <p class="mt-5 max-w-xl text-sm leading-relaxed text-[#C7CBD2] sm:text-base">
                Katalog Devtyeng memuat nama program pelatihan, pengembangan profesional, dan sertifikasi dari berbagai bidang. Buka informasi tiap layanan untuk meninjau rincian yang tersedia dan menanyakan hal yang belum tercantum.
            </p>

            <!-- Feature Points Grid -->
            <div class="mt-8 grid grid-cols-1 gap-5 sm:grid-cols-2">

                @foreach ([
                    ['M4 5h16M4 12h16M4 19h16', 'Pilihan Layanan', 'Katalog memuat program dari berbagai bidang pelatihan dan pengembangan profesional.'],
                    ['M12 3v18M3 12h18', 'Pelatihan & Sertifikasi', 'Tinjau nama dan informasi program yang tercantum dalam katalog.'],
                    ['M4 4h16v16H4zM8 9h8M8 13h8M8 17h5', 'Informasi Layanan', 'Rincian program dapat dikonsultasikan sesuai kebutuhan Anda.'],
                    ['M21 11.5a8.4 8.4 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.4 8.4 0 01-3.8-.9L3 21l1.9-5.7a8.4 8.4 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.4 8.4 0 013.8-.9h.5a8.5 8.5 0 018 8v.5z', 'Konsultasi via WhatsApp', 'Sampaikan pertanyaan mengenai layanan yang tercantum di katalog.'],
                ] as $feature)

                    <div class="flex items-start gap-3.5 rounded-xl border border-white/10 bg-[#1E293B]/60 p-4 backdrop-blur-sm">
                        <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-[#B45309]/30 bg-[#B45309]/10 text-[#B45309]">
                            <svg class="h-5 w-5"
                                 viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor"
                                 stroke-width="1.8"
                                 stroke-linecap="round"
                                 stroke-linejoin="round"
                                 aria-hidden="true">
                                <path d="{{ $feature[0] }}" />
                            </svg>
                        </span>

                        <div class="min-w-0">
                            <h3 class="text-sm font-bold text-white leading-snug">
                                {{ $feature[1] }}
                            </h3>
                            <p class="mt-1 text-xs text-[#AEB4BE] leading-relaxed">
                                {{ $feature[2] }}
                            </p>
                        </div>
                    </div>

                @endforeach

            </div>

            <div class="mt-9">
                <a href="#layanan"
                   class="inline-flex min-h-[48px] items-center justify-center rounded-xl bg-[#B45309] px-6 py-3 text-sm font-black text-[#0F172A] transition hover:bg-[#1E40AF] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white">
                    Jelajahi Katalog Layanan
                    <span class="ml-2" aria-hidden="true">&rarr;</span>
                </a>
            </div>

        </div>

    </div>
</section>


{{-- =========================================================
     D. DIREKTORI WILAYAH
========================================================= --}}
<section id="wilayah"
         class="border-b border-[#E1E5EA] bg-white py-14 sm:py-20"
         aria-labelledby="wilayah-heading">

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

        <div class="max-w-2xl">
            <span class="text-xs font-extrabold uppercase tracking-[0.2em] text-[#006E5E]">
                Jangkauan Wilayah
            </span>

            <h2 id="wilayah-heading"
                class="mt-3 text-2xl font-black tracking-tight text-[#0F172A] sm:text-3xl">
                Direktori Wilayah Layanan
            </h2>

            <p class="mt-3 text-sm leading-relaxed text-[#535B66] sm:text-base">
                Jelajahi ketersediaan informasi wilayah berdasarkan 38 provinsi di Indonesia untuk menemukan halaman kota dan kabupaten mitra.
            </p>

            <p class="mt-3 text-sm font-bold text-[#006E5E]">
                {{ count($provinces) }} provinsi tersedia dalam direktori wilayah.
            </p>
        </div>

        <!-- Provinces Grid -->
        <div id="wilayah-grid"
             data-show-group
             class="mt-8 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6">

            @foreach ($provinces as $prov)
                <a href="{{ route('regional.province', $prov['slug']) }}"
                   class="rounded-xl border border-[#E1E5EA] bg-[#F8FAFB] px-4 py-3.5 transition hover:border-[#B45309] hover:bg-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#B45309] {{ $loop->index >= 6 ? 'scheme-extra' : '' }}"
                   @if ($loop->index >= 6) hidden @endif>
                    <span class="block truncate text-xs font-bold text-[#0F172A] sm:text-sm">
                        {{ $prov['name'] }}
                    </span>
                </a>
            @endforeach

        </div>

        @if (count($provinces) > 6)
            <button type="button"
                    class="js-show-all mt-6 inline-flex min-h-[44px] items-center justify-center rounded-xl border border-[#B45309]/50 bg-white px-5 py-3 text-sm font-bold text-[#006E5E] transition hover:bg-[#B45309] hover:text-[#0F172A] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#B45309]"
                    data-all-label="Lihat Semua {{ count($provinces) }} Provinsi"
                    data-less-label="Tampilkan Lebih Sedikit"
                    aria-expanded="false"
                    aria-controls="wilayah-grid">
                Lihat Semua {{ count($provinces) }} Provinsi
            </button>
        @endif

    </div>
</section>


{{-- =========================================================
     E. LATEST ARTICLES (REAL DATABASE DATA ONLY)
========================================================= --}}
<section id="artikel"
         class="bg-[#F8FAFC] py-14 sm:py-20 lg:py-24"
         aria-labelledby="artikel-heading">

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

        <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">

            <div class="max-w-2xl">
                <span class="text-xs font-extrabold uppercase tracking-[0.2em] text-[#006E5E]">
                    Wawasan &amp; Informasi
                </span>

                <h2 id="artikel-heading"
                    class="mt-3 text-2xl font-black tracking-tight text-[#0F172A] sm:text-3xl lg:text-4xl">
                    Artikel &amp;
                    <span class="text-[#006E5E]">Informasi Terbaru</span>
                </h2>

                <p class="mt-4 text-sm leading-relaxed text-[#535B66] sm:text-base">
                    Artikel dan panduan seputar pelatihan, pengembangan profesional, dan topik yang tercantum dalam katalog Devtyeng.
                </p>
            </div>

            <a href="{{ route('articles.index') }}"
               class="inline-flex min-h-[46px] items-center justify-center rounded-xl border border-[#B45309] bg-white px-6 py-3 text-sm font-bold text-[#006E5E] transition hover:bg-[#B45309] hover:text-[#0F172A] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#B45309]">
                Semua Artikel
                <span class="ml-2" aria-hidden="true">&rarr;</span>
            </a>

        </div>

        <div class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">

            @forelse ($latestArticles as $article)
                @php
                    // Resolve actual image file or robust fallback
                    $articleImage = null;
                    if ($article->featured_image) {
                        $trimmed = ltrim($article->featured_image, '/');
                        if (file_exists(public_path($trimmed))) {
                            $articleImage = asset($trimmed);
                        } elseif (file_exists(public_path('storage/' . $trimmed))) {
                            $articleImage = asset('storage/' . $trimmed);
                        } else {
                            $articleImage = '/' . $trimmed;
                        }
                    }
                    $displayImage = $articleImage ?: '/images/home/hero-industrial.jpg';
                @endphp

                <article class="flex min-w-0 flex-col overflow-hidden rounded-2xl border border-[#E1E5EA] bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:border-[#B45309]/50">

                    <a href="{{ route('articles.show', $article->slug) }}"
                       class="block overflow-hidden focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#B45309]"
                       aria-label="{{ $article->title }}">
                        <div class="aspect-[16/10] overflow-hidden bg-[#1E293B]">
                            <img src="{{ $displayImage }}"
                                 alt="Ilustrasi artikel: {{ $article->title }}"
                                 loading="lazy"
                                 decoding="async"
                                 onerror="this.onerror=null;this.src='/images/home/hero-industrial.jpg';"
                                 class="h-full w-full object-cover transition-transform duration-500 hover:scale-105" />
                        </div>
                    </a>

                    <div class="flex flex-1 flex-col p-5 sm:p-6">

                        <div class="flex items-center justify-between gap-2">
                            <span class="self-start rounded-full bg-[#B45309]/10 px-3 py-1 text-[11px] font-extrabold uppercase tracking-wider text-[#006E5E]">
                                Devtyeng
                            </span>

                            @if ($article->target_city_slug)
                                <span class="rounded-full bg-[#F8FAFC] border border-[#E1E5EA] px-2.5 py-0.5 text-[10px] font-bold text-[#535B66]">
                                    {{ ucwords(str_replace('-', ' ', $article->target_city_slug)) }}
                                </span>
                            @endif
                        </div>

                        <h3 class="mt-3.5 text-base font-black leading-snug text-[#0F172A] sm:text-lg">
                            <a href="{{ route('articles.show', $article->slug) }}"
                               class="transition-colors hover:text-[#008F7C] focus-visible:rounded focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#B45309]">
                                {{ \Illuminate\Support\Str::limit($article->title, 72) }}
                            </a>
                        </h3>

                        <p class="mt-2 text-sm leading-relaxed text-[#535B66] line-clamp-3">
                            {{ \Illuminate\Support\Str::limit(strip_tags($article->excerpt ?: $article->content), 130) }}
                        </p>

                        <div class="mt-auto flex items-center justify-between gap-3 border-t border-[#E8EBEF] pt-4 mt-5">
                            @if ($article->published_at)
                                <time datetime="{{ $article->published_at->toDateString() }}"
                                      class="text-xs font-medium text-[#636974]">
                                    {{ $article->published_at->translatedFormat('d F Y') }}
                                </time>
                            @else
                                <span class="text-xs text-[#636974]">-</span>
                            @endif

                            <a href="{{ route('articles.show', $article->slug) }}"
                               class="inline-flex shrink-0 items-center text-sm font-bold text-[#006E5E] hover:text-[#0F172A]">
                                Baca Artikel
                                <span class="ml-1" aria-hidden="true">&rarr;</span>
                            </a>
                        </div>

                    </div>

                </article>

            @empty

                <div class="rounded-2xl border border-[#E1E5EA] bg-white p-8 text-center sm:col-span-2 lg:col-span-3">
                    <p class="font-bold text-[#0F172A]">
                        Belum ada artikel terpublikasi.
                    </p>
                    <p class="mt-2 text-sm text-[#535B66]">
                        Informasi dan panduan terbaru akan ditampilkan di bagian ini setelah tersedia.
                    </p>
                </div>

            @endforelse

        </div>

    </div>
</section>


{{-- =========================================================
     F. CTA KONSULTASI AKHIR
========================================================= --}}
<section id="kontak"
         class="relative overflow-hidden bg-[#0F172A] text-white"
         aria-labelledby="kontak-heading">

    <div class="pointer-events-none absolute -right-20 -top-24 h-80 w-80 rounded-full bg-[#B45309]/10 blur-3xl"
         aria-hidden="true"></div>
    <div class="pointer-events-none absolute -bottom-32 -left-20 h-80 w-80 rounded-full bg-[#1E40AF]/10 blur-3xl"
         aria-hidden="true"></div>

    <div class="relative mx-auto max-w-4xl px-4 py-16 text-center sm:px-6 sm:py-20 lg:px-8">

        <span class="mx-auto inline-flex h-14 w-14 items-center justify-center rounded-2xl border border-[#B45309]/30 bg-[#B45309]/10 text-[#B45309]">
            <svg class="h-7 w-7"
                 viewBox="0 0 24 24"
                 fill="none"
                 stroke="currentColor"
                 stroke-width="1.8"
                 stroke-linecap="round"
                 stroke-linejoin="round"
                 aria-hidden="true">
                <path d="M21 11.5a8.4 8.4 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.4 8.4 0 01-3.8-.9L3 21l1.9-5.7a8.4 8.4 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.4 8.4 0 013.8-.9h.5a8.5 8.5 0 018 8v.5z" />
            </svg>
        </span>

        <h2 id="kontak-heading"
            class="mt-6 text-2xl font-black tracking-tight sm:text-3xl lg:text-4xl text-white">
            Butuh Informasi Lebih Lanjut?
            <span class="text-[#B45309]">Konsultasikan Sekarang.</span>
        </h2>

        <p class="mx-auto mt-5 max-w-2xl text-sm leading-relaxed text-[#C7CBD2] sm:text-base lg:text-lg">
            Sampaikan kebutuhan Anda mengenai layanan pelatihan dan sertifikasi yang tercantum dalam katalog Devtyeng.
        </p>

        <div class="mt-8 flex justify-center">
            <x-wa-cta
                variant="primary"
                size="lg"
                label="Mulai Konsultasi via WhatsApp"
                class="w-full bg-[#B45309] font-black text-[#0F172A] shadow-lg hover:bg-[#92400E] hover:text-[#0F172A] sm:w-auto"
            />
        </div>

    </div>
</section>
@endsection
