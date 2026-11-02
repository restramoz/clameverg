<header class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 text-slate-900 shadow-sm backdrop-blur">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-3 rounded focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2" aria-label="{{ config('app.name', 'Devtyeng') }} beranda">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary text-lg font-black text-white">D</span>
            <span class="leading-tight">
                <span class="block text-lg font-extrabold tracking-tight">{{ config('app.name', 'Devtyeng') }}</span>
                <span class="block text-[11px] font-medium text-slate-600">Katalog layanan profesional</span>
            </span>
        </a>

        <nav class="hidden items-center gap-7 lg:flex" aria-label="Navigasi utama">
            <a href="{{ route('home') }}#layanan" class="text-sm font-semibold text-slate-700 hover:text-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">Layanan</a>
            <a href="{{ route('home') }}#wilayah" class="text-sm font-semibold text-slate-700 hover:text-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">Wilayah</a>
            <a href="{{ route('articles.index') }}" class="text-sm font-semibold text-slate-700 hover:text-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">Artikel</a>
            <x-wa-cta variant="primary" size="sm" label="Tanya layanan" class="bg-accent font-bold text-white hover:bg-amber-800" />
        </nav>

        <button id="mobile-nav-toggle" type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-lg border border-slate-300 text-slate-800 hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary lg:hidden" aria-expanded="false" aria-controls="mobile-nav-drawer" aria-label="Buka menu navigasi">
            <svg id="icon-menu-open" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            <svg id="icon-menu-close" class="hidden h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="m6 6 12 12M18 6 6 18"/></svg>
        </button>
    </div>

    <nav id="mobile-nav-drawer" class="hidden border-t border-slate-200 bg-white px-4 py-3 lg:hidden" aria-label="Navigasi utama seluler">
        <div class="mx-auto flex max-w-7xl flex-col gap-1">
            <a href="{{ route('home') }}#layanan" class="mobile-nav-link rounded-lg px-3 py-3 font-semibold text-slate-800 hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">Layanan</a>
            <a href="{{ route('home') }}#wilayah" class="mobile-nav-link rounded-lg px-3 py-3 font-semibold text-slate-800 hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">Wilayah</a>
            <a href="{{ route('articles.index') }}" class="mobile-nav-link rounded-lg px-3 py-3 font-semibold text-slate-800 hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">Artikel</a>
            <div class="pt-2"><x-wa-cta variant="primary" size="md" label="Tanya layanan" class="w-full justify-center bg-accent font-bold text-white hover:bg-amber-800" /></div>
        </div>
    </nav>
</header>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const button = document.getElementById('mobile-nav-toggle');
        const drawer = document.getElementById('mobile-nav-drawer');
        if (!button || !drawer) return;

        button.addEventListener('click', function () {
            const open = button.getAttribute('aria-expanded') !== 'true';
            button.setAttribute('aria-expanded', String(open));
            drawer.classList.toggle('hidden', !open);
            document.getElementById('icon-menu-open').classList.toggle('hidden', open);
            document.getElementById('icon-menu-close').classList.toggle('hidden', !open);
            button.setAttribute('aria-label', open ? 'Tutup menu navigasi' : 'Buka menu navigasi');
        });

        drawer.querySelectorAll('.mobile-nav-link').forEach(link => link.addEventListener('click', () => {
            drawer.classList.add('hidden');
            button.setAttribute('aria-expanded', 'false');
            document.getElementById('icon-menu-open').classList.remove('hidden');
            document.getElementById('icon-menu-close').classList.add('hidden');
        }));
    });
</script>
