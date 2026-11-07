{{-- =========================================================
     B. SERVICE CATEGORY CATALOG (MAIN PRIORITY)
========================================================= --}}
@php
    $catalogContext = $catalogContext ?? 'general';
    $catalogGeographicSlug = $catalogGeographicSlug ?? null;
@endphp
<section id="layanan"
         class="bg-[#F8FAFC] py-14 sm:py-20 lg:py-24"
         aria-labelledby="layanan-heading">

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

        <!-- Section Header -->
        <div class="mx-auto max-w-3xl text-center">
            <span class="inline-flex items-center gap-2 text-xs font-extrabold uppercase tracking-[0.2em] text-[#006E5E] sm:text-sm">
                <span class="h-1.5 w-6 rounded-full bg-[#14B8A6]"></span>
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
                <span class="h-2 w-2 rounded-full bg-[#14B8A6]"></span>
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
                         class="group flex min-w-0 flex-col overflow-hidden rounded-2xl border border-[#E1E5EA] bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:border-[#14B8A6]/50">

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
                                <span class="h-1.5 w-1.5 rounded-full bg-[#14B8A6]"></span>
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
                               class="mb-4 inline-flex items-center justify-between gap-2 rounded-xl border border-[#E1E5EA] bg-[#F8FAFB] px-3.5 py-2.5 text-xs font-bold text-[#0F172A] transition hover:border-[#14B8A6] hover:bg-white"
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

                                        <div class="flex min-w-0 items-center justify-between gap-2.5 rounded-xl border border-[#E8EBEF] bg-[#F8FAFB] px-3.5 py-2.5 transition hover:border-[#14B8A6]/50 hover:bg-white">

                                            <!-- Service Title Link -->
                                            <a href="{{ ($catalogContext === 'general' || ! $catalogGeographicSlug)
                                    ? route('program.show', $programSlug)
                                    : route('geographic.service.show', ['geographic' => $catalogGeographicSlug, 'service' => $programSlug]) }}"
                                               class="min-w-0 flex-1 text-sm font-semibold text-[#0F172A] hover:text-[#008F7C] transition-colors leading-snug focus-visible:rounded focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#14B8A6]">
                                                {{ $item['title'] }}
                                            </a>

                                            <!-- Actions: Detail Link & WhatsApp Quick Action -->
                                            <div class="flex shrink-0 items-center gap-2">
                                                <a href="{{ ($catalogContext === 'general' || ! $catalogGeographicSlug)
                                    ? route('program.show', $programSlug)
                                    : route('geographic.service.show', ['geographic' => $catalogGeographicSlug, 'service' => $programSlug]) }}"
                                                   class="inline-flex items-center text-xs font-bold text-[#006E5E] hover:text-[#0F172A] px-2 py-1 rounded transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#14B8A6]"
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
                                    class="js-show-all mt-4 inline-flex min-h-[44px] w-full items-center justify-center gap-2 rounded-xl border border-[#14B8A6]/50 bg-white px-4 py-2.5 text-sm font-bold text-[#006E5E] transition-all hover:bg-[#14B8A6] hover:text-[#0F172A] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#14B8A6]"
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


