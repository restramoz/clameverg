<footer class="bg-slate-950 text-slate-100" role="contentinfo">
    <div class="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 md:grid-cols-3 lg:px-8">
        <div>
            <a href="{{ route('home') }}" class="inline-flex items-center gap-3 rounded focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-400">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary text-lg font-black text-white">D</span>
                <span class="text-lg font-extrabold tracking-tight">{{ config('app.name', 'Bevehard') }}</span>
            </a>
            <p class="mt-4 max-w-sm text-sm leading-relaxed text-slate-300">Informasi layanan profesional dan program yang tercantum di katalog.</p>
        </div>

        <nav aria-label="Navigasi kaki halaman">
            <h2 class="text-xs font-bold uppercase tracking-[0.16em] text-amber-300">Jelajahi</h2>
            <ul class="mt-4 space-y-3 text-sm text-slate-300" role="list">
                <li><a class="hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-300" href="{{ route('home') }}#layanan">Katalog layanan</a></li>
                <li><a class="hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-300" href="{{ route('home') }}#wilayah">Direktori wilayah</a></li>
                <li><a class="hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-300" href="{{ route('articles.index') }}">Artikel</a></li>
            </ul>
        </nav>

        <div>
            <h2 class="text-xs font-bold uppercase tracking-[0.16em] text-amber-300">Informasi</h2>
            <p class="mt-4 text-sm leading-relaxed text-slate-300">Tanyakan rincian layanan yang belum tersedia di katalog melalui kanal konsultasi.</p>
            <div class="mt-4"><x-wa-cta variant="primary" size="sm" label="Tanya layanan" class="bg-amber-700 font-bold text-white hover:bg-amber-800" /></div>
        </div>
    </div>
    <div class="border-t border-slate-800">
        <div class="mx-auto flex max-w-7xl flex-col gap-2 px-4 py-5 text-xs text-slate-400 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
            <p>&copy; {{ date('Y') }} {{ config('app.name', 'Bevehard') }}.</p>
            <p>Informasi mengikuti rincian yang tersedia pada setiap halaman layanan.</p>
        </div>
    </div>
</footer>
