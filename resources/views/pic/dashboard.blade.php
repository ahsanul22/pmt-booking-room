<x-dashboard-hero eyebrow="Workspace PIC Ruangan" :title="$title" description="Periksa pengajuan dan pastikan ketersediaan ruangan tanggung jawab Anda.">
    <a href="{{ route('pic.approvals.index') }}" class="inline-flex min-h-[48px] items-center gap-2 rounded-xl bg-surface px-5 py-3 text-base font-semibold text-primaryDark transition hover:bg-secondaryLight focus-visible:outline-white">Permintaan Approval<x-schedule-icon name="arrow" class="h-4 w-4" /></a>
    <a href="{{ route('pic.rooms.index') }}" class="inline-flex min-h-[48px] items-center gap-2 rounded-xl border border-white/40 px-5 py-3 text-base font-semibold text-white transition hover:bg-white/10 focus-visible:outline-white"><x-schedule-icon name="room" class="h-4 w-4" />Ruangan Saya</a>
</x-dashboard-hero>

<section aria-label="Ringkasan tugas PIC" class="mb-6 grid gap-4 sm:grid-cols-3">
    @foreach($stats as $stat)
        <x-stat-card :label="$stat['label']" :value="$stat['value']" :description="$stat['description']" :icon="$stat['icon']" :tone="$loop->first && $stat['value'] > 0 ? 'warning' : 'primary'" />
    @endforeach
</section>

<div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
    <x-workspace-panel title="Menunggu Tinjauan Anda" description="Tinjau permintaan approval ruangan Anda, mulai dari jadwal terdekat.">
        <x-slot:headingActions>
            <a href="{{ route('pic.approvals.index') }}" class="workspace-button-secondary">Buka permintaan</a>
        </x-slot:headingActions>
        @if($pendingApprovals->isNotEmpty())
            <ul class="divide-y divide-slate-100 p-2 sm:p-3">
                @foreach($pendingApprovals as $booking)
                    @include('shared.dashboard-booking-item', ['booking' => $booking, 'href' => route('pic.approvals.show', $booking), 'meta' => $booking->applicant?->name])
                @endforeach
            </ul>
        @else
            <div class="px-6 py-12 text-center">
                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-background text-primary"><x-schedule-icon name="clock" class="h-7 w-7" /></span>
                <h3 class="mt-4 text-base font-semibold text-primaryDark">Semua pengajuan sudah ditinjau</h3>
                <p class="mx-auto mt-2 max-w-sm text-sm leading-6 text-slate-600">Pengajuan baru untuk ruangan Anda akan muncul di sini.</p>
            </div>
        @endif
    </x-workspace-panel>

    <aside class="min-w-0 space-y-5">
        @include('shared.dashboard-account')
        <a href="{{ route('pic.approvals.history') }}" class="group flex items-center gap-4 rounded-2xl border border-slate-200/80 bg-surface p-5 shadow-sm transition hover:border-secondary">
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-background text-primary"><x-schedule-icon name="calendar" class="h-5 w-5" /></span>
            <span class="min-w-0 flex-1"><span class="block font-semibold text-primaryDark">Riwayat Approval</span><span class="mt-0.5 block text-sm text-slate-600">Keputusan yang sudah Anda buat.</span></span>
            <x-schedule-icon name="arrow" class="h-4 w-4 shrink-0 text-primary transition group-hover:translate-x-1" />
        </a>
    </aside>
</div>
