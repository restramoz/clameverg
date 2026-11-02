@php
    // Fixed consultation contacts used on generated articles only. These are
    // intentionally separate from the configurable site-wide WhatsApp number.
    $contacts = config('services.whatsapp.callout_contacts', \App\Support\WhatsApp::consultationNumbers());
@endphp

@if(!empty($contacts))
<section
    aria-label="Nomor WhatsApp layanan konsultasi"
    {{ $attributes->merge(['class' => 'article-consultation-numbers mt-4 p-4 sm:p-6 rounded-2xl bg-neutral-50 border border-neutral-200']) }}
>
    <h3 class="text-xs font-bold text-dark-mahogany uppercase tracking-wider">
        Konsultasi via WhatsApp
    </h3>

    {{-- Four consultation buttons, grouped together --}}
    <div class="mt-3 flex flex-wrap gap-2.5 sm:gap-3">
        @foreach($contacts as $contact)
            <a
                href="{{ \App\Support\WhatsApp::consultationUrl($contact) }}"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-safety-orange hover:bg-orange-600 text-white text-sm font-bold transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-safety-orange min-h-[44px]"
            >
                <span>Konsultasi WhatsApp</span>
                <span class="sr-only">{{ $contact }}</span>
            </a>
        @endforeach
    </div>

    {{-- Four clickable phone numbers immediately beneath the buttons --}}
    <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-2.5 sm:gap-3">
        @foreach($contacts as $contact)
            <a
                href="{{ \App\Support\WhatsApp::consultationUrl($contact) }}"
                target="_blank"
                rel="noopener noreferrer"
                class="flex items-center justify-center px-3 py-2.5 rounded-lg bg-white border border-neutral-200 text-sm font-bold text-dark-mahogany hover:text-burnt-brick hover:border-burnt-brick/50 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-safety-orange tracking-wide min-h-[44px]"
            >
                {{ $contact }}
            </a>
        @endforeach
    </div>
</section>
@endif
