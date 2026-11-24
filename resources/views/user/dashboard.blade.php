<x-dashboard-hero :eyebrow="$title" :title="'Selamat datang, '.$user->name" description="Ruang yang tepat. Ide yang hebat. Pilih ruangan, tentukan waktu dan agenda, lalu pantau statusnya di sini.">
    <a href="{{ route('rooms.index') }}" class="inline-flex min-h-[48px] items-center gap-2 rounded-xl bg-surface px-5 py-3 text-base font-semibold text-primaryDark transition hover:bg-secondaryLight focus-visible:outline-white">Booking Ruangan<x-schedule-icon name="arrow" class="h-4 w-4" /></a>
    <a href="{{ route('schedule.index') }}" class="inline-flex min-h-[48px] items-center gap-2 rounded-xl border border-white/40 px-5 py-3 text-base font-semibold text-white transition hover:bg-white/10 focus-visible:outline-white"><x-schedule-icon name="calendar" class="h-4 w-4" />Jadwal Ruangan</a>
</x-dashboard-hero>

<section aria-label="Ringkasan booking Anda" class="mb-6 grid gap-4 sm:grid-cols-3">
    @foreach($stats as $stat)
        <x-stat-card :label="$stat['label']" :value="$stat['value']" :description="$stat['description']" :icon="$stat['icon']" :tone="$stat['tone']" />
    @endforeach
</section>

<div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
    <x-workspace-panel title="Rapat Mendatang" description="Booking Pending dan Approved yang belum berakhir.">
        <x-slot:headingActions>
            <a href="{{ route('my-bookings.index') }}" class="workspace-button-secondary">My Booking</a>
        </x-slot:headingActions>
        @if($upcomingBookings->isNotEmpty())
            <ul class="divide-y divide-slate-100 p-2 sm:p-3">
                @foreach($upcomingBookings as $booking)
                    @include('shared.dashboard-booking-item', ['booking' => $booking, 'href' => route('my-bookings.show', $booking), 'meta' => $booking->agenda])
                @endforeach
            </ul>
        @else
            <div class="px-6 py-12 text-center">
                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-background text-primary"><x-schedule-icon name="calendar" class="h-7 w-7" /></span>
                <h3 class="mt-4 text-base font-semibold text-primaryDark">Belum ada rapat terjadwal</h3>
                <p class="mx-auto mt-2 max-w-sm text-sm leading-6 text-slate-600">Cari ruangan yang sesuai dan ajukan booking. Pengajuan Anda akan tampil di sini.</p>
                <a href="{{ route('rooms.index') }}" class="mt-5 inline-flex items-center gap-2 text-base font-semibold text-primary hover:underline">Mulai booking<x-schedule-icon name="arrow" class="h-4 w-4" /></a>
            </div>
        @endif
    </x-workspace-panel>

    <aside class="min-w-0 space-y-5">
        @include('shared.dashboard-account')
        <section aria-labelledby="tips-title" class="rounded-2xl border border-secondaryLight/70 bg-secondaryLight/15 p-6">
            <h2 id="tips-title" class="font-bold text-primaryDark">Sebelum mengajukan</h2>
            <ul class="mt-3 space-y-2 text-sm leading-6 text-slate-700">
                <li class="flex gap-2"><span aria-hidden="true" class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-primary"></span>Cek Jadwal Ruangan agar waktu yang dipilih tidak bentrok.</li>
                <li class="flex gap-2"><span aria-hidden="true" class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-primary"></span>Ruangan tertentu memerlukan persetujuan PIC.</li>
                <li class="flex gap-2"><span aria-hidden="true" class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-primary"></span>Cantumkan nama tamu bila rapat dihadiri pihak luar.</li>
            </ul>
        </section>
    </aside>
</div>
