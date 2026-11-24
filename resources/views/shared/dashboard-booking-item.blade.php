{{-- Satu baris booking pada dashboard. Variabel: $booking, $href, $meta (opsional). --}}
@php($date = \Carbon\CarbonImmutable::parse($booking->date)->locale('id'))
<li>
    <a href="{{ $href }}" class="group flex items-center gap-4 rounded-xl p-3 transition hover:bg-background sm:p-4">
        <span class="flex h-14 w-14 shrink-0 flex-col items-center justify-center rounded-xl bg-secondaryLight/25 text-primaryDark">
            <span class="text-xs font-semibold uppercase text-primary">{{ $date->translatedFormat('M') }}</span>
            <span class="text-xl font-bold leading-none">{{ $date->format('d') }}</span>
        </span>
        <span class="min-w-0 flex-1">
            <span class="block truncate text-base font-semibold text-primaryDark">{{ $booking->room?->name ?? 'Ruangan' }}</span>
            <span class="mt-0.5 block text-sm text-slate-600">{{ $date->translatedFormat('l') }}, {{ substr($booking->start_time, 0, 5) }}–{{ substr($booking->end_time, 0, 5) }} WIB</span>
            @if(! empty($meta))<span class="mt-0.5 block truncate text-sm text-slate-500">{{ $meta }}</span>@endif
        </span>
        <x-booking-status :status="$booking->status" />
    </a>
</li>
