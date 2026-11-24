{{-- Kartu ringkasan angka pada dashboard. --}}
@props(['label', 'value', 'description' => null, 'icon' => 'grid', 'tone' => 'primary'])
<div {{ $attributes->class(['relative min-w-0 overflow-hidden rounded-2xl border border-slate-200/80 bg-surface p-5 shadow-sm']) }}>
    <span aria-hidden="true" @class(['absolute inset-y-0 left-0 w-1', 'bg-primary' => $tone === 'primary', 'bg-warning' => $tone === 'warning', 'bg-secondary' => $tone === 'secondary'])></span>
    <div class="flex items-start justify-between gap-3">
        <h2 class="text-base font-medium text-slate-600">{{ $label }}</h2>
        <span @class(['rounded-xl p-2.5', 'bg-secondaryLight/25 text-primary' => $tone !== 'warning', 'bg-warning/15 text-warningDark' => $tone === 'warning'])><x-schedule-icon :name="$icon" class="h-5 w-5" /></span>
    </div>
    <p class="mt-3 text-3xl font-bold tabular-nums text-primaryDark">{{ $value }}</p>
    @if($description)<p class="mt-1 text-sm text-slate-500">{{ $description }}</p>@endif
</div>
