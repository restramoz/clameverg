@props([
    'citySlug' => null,
    'districtSlug' => null,
    'regionName' => null,
])

@php
    $regionalService = resolve(\App\Services\RegionalService::class);

    $queryParts = [];
    $resolvedName = null;
    $level = 'unknown';

    if (!empty($districtSlug) && !empty($citySlug)) {
        // District level: find district with its city context
        $district = $regionalService->findDistrict((string) $districtSlug, (string) $citySlug);
        if ($district) {
            $level = 'district';
            $resolvedName = $district['name']; // District name
            $queryParts[] = $district['name'];
            $queryParts[] = $district['city_name']; // City name (e.g., "Kabupaten Malang" or "Kota Surabaya")
            $queryParts[] = $district['province'];  // Province name
        }
    } elseif (!empty($citySlug)) {
        // City level: find city with its province
        $city = $regionalService->findCity((string) $citySlug);
        if ($city) {
            $level = 'city';
            $resolvedName = $city['name'];
            $queryParts[] = $city['name'];          // City name (e.g., "Kabupaten Malang")
            $queryParts[] = $city['province'];      // Province name
        }
    }

    // Fallback to provided regionName if resolution failed
    $displayName = $resolvedName ?? $regionName ?? 'Wilayah';
    $queryParts[] = 'Indonesia';

    $mapId = 'regional-map-' . md5((string) $citySlug . '/' . (string) $districtSlug);
    $mapsQuery = implode(', ', $queryParts);
    $mapsEmbedUrl = 'https://maps.google.com/maps?q=' . urlencode($mapsQuery) . '&output=embed';
    $mapsLinkUrl = 'https://www.google.com/maps/search/?api=1&query=' . urlencode($mapsQuery);
@endphp

<section class="py-12 bg-dark-mahogany border-b border-dark-mahogany" aria-labelledby="{{ $mapId }}-heading">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mb-6">
            <span class="text-xs font-bold uppercase tracking-wider text-amber-500 bg-dark-mahogany px-3 py-1 rounded-full border border-burnt-brick/20">
                Peta Wilayah
            </span>
            <h2 id="{{ $mapId }}-heading" class="text-xl sm:text-2xl font-extrabold text-amber-500 mt-2">
                Konteks Administratif {{ $displayName }}
            </h2>
            <p class="text-sm text-amber-500 mt-2 leading-relaxed">
                Peta menampilkan konteks administratif <strong class="text-amber-500">{{ $displayName }}</strong>.
                @if ($level === 'district')
                    Kecamatan <strong class="text-amber-500">{{ $resolvedName }}</strong>, {{ $queryParts[1] ?? '' }}, {{ $queryParts[2] ?? '' }}.
                @elseif ($level === 'city')
                    {{ $resolvedName }}, {{ $queryParts[1] ?? '' }}.
                @endif
                Titik pada peta menunjukkan pusat administratif wilayah dan <strong class="text-amber-500">bukan lokasi kantor atau tempat pelaksanaan program terkonfirmasi</strong>.
            </p>
        </div>

        @if ($resolvedName)
            <div class="relative rounded-2xl overflow-hidden border border-dark-mahogany shadow-sm bg-dark-mahogany">
                <div class="aspect-video w-full">
                    <iframe
                        id="{{ $mapId }}"
                        title="Peta {{ $displayName }}"
                        src="{{ $mapsEmbedUrl }}"
                        style="border:0; width:100%; height:100%;"
                        allowfullscreen=""
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        role="application"
                        aria-label="Peta lokasi {{ $mapsQuery }}">
                    </iframe>
                </div>
            </div>

            <div class="mt-3 flex flex-wrap items-center justify-between gap-2 text-xs text-amber-500">
                <span>Query: {{ $mapsQuery }}</span>
                <a
                    href="{{ $mapsLinkUrl }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-1.5 text-amber-500 hover:text-amber-500 font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 rounded"
                    aria-label="Buka {{ $mapsQuery }} di Google Maps di tab baru"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                    <span>Buka di Google Maps</span>
                </a>
            </div>
        @else
            <div class="rounded-2xl border border-dark-mahogany bg-dark-mahogany p-6 text-sm text-amber-500" role="status">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <p class="font-bold text-amber-500">Data Wilayah Tidak Ditemukan</p>
                        <p class="mt-1 text-amber-500">
                            Tidak dapat memetakan {{ $regionName ?? 'wilayah ini' }} ke data administratif BPS.
                            Informasi program dapat ditanyakan melalui WhatsApp.
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>
</section>
