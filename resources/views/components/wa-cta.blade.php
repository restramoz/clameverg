@props([
    'phone' => null,
    'message' => null,
    'variant' => 'wa',
    'size' => 'md',
    'label' => 'Konsultasi via WhatsApp',
    'showIcon' => true,
])

@php
    // If no explicit phone is provided, fall back to the globally configured one.
    $effectivePhone = $phone ?? config('services.whatsapp.phone');
    $waUrl = \App\Support\WhatsApp::url($message, $effectivePhone);
    $textLabel = $slot->isNotEmpty() ? (string) $slot : $label;
@endphp

@if ($waUrl !== null)
    <x-button :href="$waUrl" :variant="$variant" :size="$size" target="_blank" rel="noopener noreferrer" aria-label="{{ strip_tags($textLabel) }} (buka chat WhatsApp di tab baru)" {{ $attributes }}>
        @if ($showIcon)
            <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M12.031 2C6.496 2 2 6.484 2 12.016c0 1.954.563 3.781 1.547 5.344L2.094 22l4.781-1.422a9.98 9.98 0 0 0 5.156 1.438c5.531 0 10.031-4.484 10.031-10.016C22.062 6.484 17.562 2 12.031 2zm5.844 14.281c-.25.688-1.25 1.344-1.781 1.406-.469.063-1.078.094-3.469-.906-2.906-1.219-4.75-4.188-4.906-4.375-.125-.188-1.188-1.594-1.188-3.031 0-1.438.75-2.156 1.031-2.438.281-.281.625-.344.844-.344.219 0 .438.016.625.016.219 0 .484-.078.75.563.281.688.938 2.313 1.031 2.484.094.188.156.406.031.625-.125.219-.188.344-.375.563-.188.219-.406.469-.578.625-.188.188-.375.406-.156.781.219.375.969 1.594 2.094 2.594 1.438 1.281 2.656 1.688 3.031 1.875.375.188.594.156.813-.094.219-.25.938-1.094 1.188-1.469.25-.375.5-.313.844-.188.344.125 2.188 1.031 2.563 1.219.375.188.625.281.719.438.094.156.094.906-.156 1.594z"/>
            </svg>
        @endif
        <span>{{ $slot->isNotEmpty() ? $slot : $label }}</span>
    </x-button>
@else
    {{-- No usable phone configured: render neutral non-link contact text --}}
    {{-- instead of a clickable button that cannot work. CTA styling helpers --}}
    {{-- passed by callers are intentionally ignored here. --}}
    <span class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg bg-concrete-100 border border-concrete-300 text-dark-mahogany font-bold text-sm sm:text-base">
        <span aria-hidden="true">•</span>
        {{ \App\Support\WhatsApp::fallbackText() }}
    </span>
@endif
