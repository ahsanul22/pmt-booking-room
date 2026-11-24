{{-- Banner pembuka dashboard semua role. Slot default = tombol aksi; slot "aside" = konten kanan (opsional). --}}
@props(['eyebrow', 'title', 'description' => null, 'titleId' => 'dashboard-title'])
<section aria-labelledby="{{ $titleId }}" {{ $attributes->class(['relative isolate mb-6 overflow-hidden rounded-3xl bg-primaryDark text-white shadow-sm']) }}>
    <img src="{{ asset('images/home-harbor.jpg') }}" alt="" width="1536" height="1024" class="absolute inset-y-0 right-0 -z-20 hidden h-full w-3/5 object-cover object-[60%_65%] md:block">
    <div aria-hidden="true" class="absolute inset-0 -z-10 bg-gradient-to-r from-primaryDark from-40% via-primaryDark/90 to-primary/40"></div>
    <div class="grid gap-8 px-6 py-8 sm:px-10 sm:py-10 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-end">
        <div class="min-w-0">
            <p class="text-sm font-bold uppercase tracking-[0.2em] text-secondaryLight">{{ $eyebrow }}</p>
            <h1 id="{{ $titleId }}" class="mt-3 break-words text-3xl font-bold tracking-tight sm:text-4xl">{{ $title }}</h1>
            @if($description)<p class="mt-3 max-w-2xl text-base leading-7 text-white/80">{{ $description }}</p>@endif
            @if(trim($slot) !== '')<div class="mt-7 flex flex-wrap gap-3">{{ $slot }}</div>@endif
        </div>
        @isset($aside)<div class="min-w-0">{{ $aside }}</div>@endisset
    </div>
</section>
