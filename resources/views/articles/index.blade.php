@extends('layouts.app')

@section('title', 'Artikel & Panduan')
@section('meta_description', 'Kumpulan artikel edukatif, panduan teknis, dan referensi seputar skema kompetensi serta program pelatihan Clameverg.')
@section('canonical_url', $canonicalUrl)

@section('content')
<section class="py-16 sm:py-20 bg-slate-50">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl mb-12">
            <span class="text-xs font-bold uppercase tracking-wider text-primary">Artikel</span>
            <h1 class="mt-2 text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">Panduan &amp; informasi Clameverg</h1>
            <p class="mt-3 text-slate-600 leading-relaxed">Artikel teknis dan referensi seputar skema kompetensi serta layanan yang tercantum dalam katalog.</p>
        </div>

        @if($articles->isEmpty())
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center">
                <p class="font-bold text-slate-900">Belum ada artikel yang diterbitkan.</p>
                <p class="mt-2 text-sm text-slate-600">Artikel dan panduan akan segera hadir.</p>
            </div>
        @else
            @php $first = $articles->first(); $rest = $articles->skip(1); @endphp

            <article class="grid lg:grid-cols-12 gap-6 mb-12 rounded-3xl bg-white border border-slate-200 overflow-hidden">
                @if($first->featured_image)
                    <div class="lg:col-span-5 aspect-[4/3] lg:aspect-auto">
                        <img src="/{{ ltrim($first->featured_image, '/') }}" alt="{{ $first->title }}" class="w-full h-full object-cover" loading="lazy">
                    </div>
                @endif
                <div class="lg:col-span-7 p-7 sm:p-10 flex flex-col justify-center">
                    <div class="text-xs text-slate-500 mb-3">
                        {{ ($first->published_at ?? $first->created_at)->format('d M Y') }}
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-black text-slate-900 leading-tight">
                        <a href="{{ route('articles.show', $first->slug) }}" class="hover:text-primary transition">{{ $first->title }}</a>
                    </h2>
                    @if($first->excerpt)
                        <p class="mt-4 text-slate-600 leading-relaxed">{{ $first->excerpt }}</p>
                    @endif
                    <a href="{{ route('articles.show', $first->slug) }}" class="mt-6 inline-flex text-sm font-bold text-primary hover:underline">Baca selengkapnya →</a>
                </div>
            </article>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($rest as $article)
                    <article class="rounded-2xl border border-slate-200 bg-white p-6 hover:border-primary/40 transition">
                        <div class="text-xs text-slate-500 mb-2">
                            {{ ($article->published_at ?? $article->created_at)->format('d M Y') }}
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 leading-snug">
                            <a href="{{ route('articles.show', $article->slug) }}" class="hover:text-primary transition">{{ $article->title }}</a>
                        </h3>
                        @if($article->excerpt)
                            <p class="mt-2 text-sm text-slate-600 line-clamp-3">{{ $article->excerpt }}</p>
                        @endif
                        <a href="{{ route('articles.show', $article->slug) }}" class="mt-4 inline-flex text-xs font-bold text-primary hover:underline">Baca →</a>
                    </article>
                @endforeach
            </div>
        @endif

        @if(method_exists($articles, 'hasPages') && $articles->hasPages())
            <div class="mt-12">{{ $articles->links() }}</div>
        @endif
    </div>
</section>
@endsection
