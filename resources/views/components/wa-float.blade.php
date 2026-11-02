@php
    $effectivePhone = $phone ?? config('services.whatsapp.phone');
    $waUrl = \App\Support\WhatsApp::url($message, $effectivePhone);
    // When no valid recipient is configured, fall back to universal wa.me
    if ($waUrl === null) {
        $encodedMessage = rawurlencode($message ?? \App\Support\WhatsApp::defaultMessage());
        $waUrl = "https://wa.me/?text={$encodedMessage}";
    }
@endphp

<aside aria-label="Layanan Bantuan WhatsApp" class="fixed bottom-5 right-5 z-40 print:hidden">
    <a href="{{ $waUrl }}"
       target="_blank"
       rel="noopener noreferrer"
       class="group relative flex items-center justify-center w-14 h-14 bg-[#25D366] hover:bg-[#20BD5A] text-white rounded-full shadow-2xl transition-all duration-300 transform hover:scale-110 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-[#25D366]/40"
       aria-label="Konsultasi cepat via WhatsApp (buka chat di tab baru)">
        <span class="absolute -top-1 -right-1 flex h-3.5 w-3.5">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#25D366] opacity-75"></span>
            <span class="relative inline-flex rounded-full h-3.5 w-3.5 bg-white border-2 border-[#25D366]"></span>
        </span>
        <svg class="w-8 h-8 fill-current text-white transition-transform duration-200 group-hover:scale-105" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M12.031 2C6.496 2 2 6.484 2 12.016c0 1.954.563 3.781 1.547 5.344L2.094 22l4.781-1.422a9.98 9.98 0 0 0 5.156 1.438c5.531 0 10.031-4.484 10.031-10.016C22.062 6.484 17.562 2 12.031 2zm5.844 14.281c-.25.688-1.25 1.344-1.781 1.406-.469.063-1.078.094-3.469-.906-2.906-1.219-4.75-4.188-4.906-4.375-.125-.188-1.188-1.594-1.188-3.031 0-1.438.75-2.156 1.031-2.438.281-.281.625-.344.844-.344.219 0 .438.016.625.016.219 0 .484-.078.75.563.281.688.938 2.313 1.031 2.484.094.188.156.406.031.625-.125.219-.188.344-.375.563-.188.219-.406.469-.578.625-.188.188-.375.406-.156.781.219.375.969 1.594 2.094 2.594 1.438 1.281 2.656 1.688 3.031 1.875.375.188.594.156.813-.094.219-.25.938-1.094 1.188-1.469.25-.375.5-.313.844-.188.344.125 2.188 1.031 2.563 1.219.375.188.625.281.719.438.094.156.094.906-.156 1.594z"/>
        </svg>
        <span class="sr-only">Chat WhatsApp</span>
    </a>
</aside>
