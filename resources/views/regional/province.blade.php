@extends('layouts.app')

@section('title', 'Pelatihan & Sertifikasi di Provinsi ' . $province['name'])
@section('meta_description', 'Informasi katalog layanan dengan konteks Provinsi ' . $province['name'] . '. Tinjau ruang lingkup umum dan rincian yang tercantum pada setiap layanan.')
@section('wa_message', $waMessage)

@section('content')
<div class="bg-slate-100 border-b border-slate-200">
    <x-breadcrumb :items="[
        ['label' => 'Wilayah', 'url' => null],
        ['label' => $province['name'], 'url' => null],
    ]" />
</div>

<section class="relative overflow-hidden bg-slate-950 text-white">
    <div class="absolute inset-0 opacity-30">
        <div class="absolute -top-24 right-0 h-72 w-72 rounded-full bg-primary/40 blur-3xl"></div>
        <div class="absolute bottom-0 left-0 h-72 w-72 rounded-full bg-teal-500/20 blur-3xl"></div>
    </div>

    <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-16 sm:py-20">
        <div class="grid lg:grid-cols-12 gap-10 items-end">
            <div class="lg:col-span-8">
                <span class="text-xs font-semibold uppercase tracking-[0.22em] text-teal-300">Wilayah</span>
                <h1 class="mt-4 text-4xl sm:text-5xl font-black tracking-tight leading-tight">
                    Program Clameverg di <span class="text-teal-300">Provinsi {{ $province['name'] }}</span>
                </h1>
                <p class="mt-5 text-base sm:text-lg text-slate-300 leading-relaxed max-w-2xl">
                    Halaman ini menyediakan informasi katalog layanan dengan konteks regional. Nama wilayah ini tidak menyatakan adanya kantor atau penyelenggaraan setempat.
                </p>
                <div class="mt-8 flex flex-col sm:flex-row gap-4">
                    <x-wa-cta variant="primary" size="lg" :message="$waMessage" label="Konsultasi Provinsi {{ $province['name'] }}" class="min-h-[48px] px-7 rounded-2xl bg-teal-500 text-slate-950 font-black hover:bg-teal-400 shadow-none" />
                    <a href="#layanan" class="inline-flex items-center justify-center min-h-[48px] px-7 rounded-2xl border border-white/20 text-white font-bold hover:bg-white/10 transition">Lihat program →</a>
                </div>
            </div>

            <div class="lg:col-span-4 rounded-3xl border border-white/10 bg-white/5 p-6 backdrop-blur">
                <dl class="grid grid-cols-2 gap-4">
                    <div>
                        <dt class="text-xs text-slate-400">Pulau</dt>
                        <dd class="mt-1 font-bold">{{ $province['island'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-400">Kab/Kota</dt>
                        <dd class="mt-1 font-bold">{{ $province['total_cities'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-400">Layanan</dt>
                        <dd class="mt-1 font-bold">{{ count($programs) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-400">Lingkup</dt>
                        <dd class="mt-1 font-bold text-teal-300">Clameverg</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</section>

<section class="py-8 bg-amber-50 border-b border-amber-200">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex items-start gap-4">
            <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <p class="text-sm text-amber-900 leading-relaxed">
                <strong>Catatan penting:</strong> halaman ini adalah portal informasi dan konsultasi program. Detail pelatihan, jadwal, dan persyaratan administrasi dapat ditanyakan melalui WhatsApp.
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

@include('components.service-catalog', ['catalogContext' => 'regional', 'catalogGeographicSlug' => $province['slug']])

{{-- Proses & Syarat Umum --}}
<section class="py-14 sm:py-20 bg-slate-50">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mb-4">Proses &amp; Syarat Umum</h2>
        <p class="text-slate-600 leading-relaxed max-w-3xl">Untuk mengetahui proses, persyaratan, materi, dan informasi layanan secara lengkap, silakan lihat halaman layanan terkait atau hubungi Clameverg melalui kanal resmi.</p>
        <div class="mt-6 grid gap-4 sm:grid-cols-2 max-w-4xl">
            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <h3 class="font-bold text-slate-900 mb-2">Rincian layanan</h3>
                <p class="text-sm text-slate-600 leading-relaxed">Rincian proses, persyaratan, dan materi setiap layanan dapat dilihat pada halaman layanan terkait.</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <h3 class="font-bold text-slate-900 mb-2">Konsultasi</h3>
                <p class="text-sm text-slate-600 leading-relaxed">Untuk rincian lebih lanjut, hubungi Clameverg melalui kanal resmi. Tautan WhatsApp tersedia pada halaman ini dan di halaman layanan.</p>
            </div>
        </div>
    </div>
</section>

{{-- Pertanyaan Umum --}}
<section class="py-14 sm:py-20 bg-white">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mb-6">Pertanyaan Umum</h2>
        <div class="space-y-3 max-w-4xl">
            <details class="rounded-xl border border-slate-200 p-4"><summary class="font-bold cursor-pointer">Bagaimana cara mendapatkan informasi layanan di Provinsi {{ $province['name'] }}?</summary><p class="mt-3 text-slate-600">Telusuri katalog layanan di halaman ini, lalu buka halaman detail layanan terkait untuk melihat rincian. Hubungi Clameverg melalui kanal resmi untuk konsultasi.</p></details>
            <details class="rounded-xl border border-slate-200 p-4"><summary class="font-bold cursor-pointer">Bagaimana cara mengetahui persyaratan program?</summary><p class="mt-3 text-slate-600">Persyaratan setiap program dapat dilihat pada halaman layanan terkait. Untuk rincian lebih lanjut, hubungi Clameverg melalui kanal resmi.</p></details>
            <details class="rounded-xl border border-slate-200 p-4"><summary class="font-bold cursor-pointer">Bagaimana cara mengetahui materi atau silabus layanan?</summary><p class="mt-3 text-slate-600">Materi atau silabus layanan tersedia pada halaman detail layanan terkait. Hubungi Clameverg untuk penjelasan lebih rinci.</p></details>
            <details class="rounded-xl border border-slate-200 p-4"><summary class="font-bold cursor-pointer">Bagaimana cara menghubungi Clameverg?</summary><p class="mt-3 text-slate-600">Gunakan tombol WhatsApp yang tersedia pada halaman ini atau halaman layanan. Seluruh konsultasi program disampaikan melalui kanal resmi Clameverg.</p></details>
            <details class="rounded-xl border border-slate-200 p-4"><summary class="font-bold cursor-pointer">Apakah informasi layanan dapat dikonsultasikan terlebih dahulu?</summary><p class="mt-3 text-slate-600">Ya. Anda dapat menghubungi Clameverg terlebih dahulu untuk membahas program yang diminati. Konsultasi tidak menjanjikan penetapan program tanpa konfirmasi.</p></details>
            <details class="rounded-xl border border-slate-200 p-4"><summary class="font-bold cursor-pointer">Apakah halaman regional menyatakan kantor setempat?</summary><p class="mt-3 text-slate-600">Tidak. Halaman regional adalah konteks informasi, bukan penunjukan kantor setempat.</p></details>
        </div>
    </div>
</section>

{{-- Related Cities --}}
<section class="py-14 sm:py-20 bg-white">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mb-6">Kota &amp; Kabupaten di Provinsi {{ $province['name'] }}</h2>
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach(($province['cities'] ?? []) as $cityItem)
                <a href="{{ route('regional.city', $cityItem['slug']) }}" class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 hover:border-primary/40 hover:text-primary transition">
                    {{ $cityItem['name'] }}
                </a>
            @endforeach
        </div>
    </div>
</section>

<section class="py-20 sm:py-24 bg-primary text-white">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl sm:text-4xl font-black tracking-tight">Butuh rincian program di {{ $province['name'] }}?</h2>
        <p class="mt-4 text-white/80 leading-relaxed">Sampaikan kebutuhan Anda melalui WhatsApp. Tim Clameverg akan membantu memilih program yang sesuai.</p>
        <div class="mt-8 flex justify-center">
            <x-wa-cta variant="primary" size="lg" :message="$waMessage" label="Konsultasi via WhatsApp" class="min-h-[52px] px-8 rounded-2xl bg-white text-primary font-black hover:bg-slate-100 shadow-lg" />
        </div>
    </div>
</section>
@endsection
