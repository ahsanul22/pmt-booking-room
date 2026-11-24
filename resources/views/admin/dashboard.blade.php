@extends('layouts.workspace')

@section('title', $title)
@section('breadcrumb', 'Dashboard')

@section('content')
    @if(session('status'))
        <div role="status" class="mb-6 rounded-xl border border-secondaryLight bg-surface p-4 text-base text-primaryDark">{{ session('status') }}</div>
    @endif

    <x-dashboard-hero eyebrow="Administrasi Workspace" :title="$title" description="Pantau kondisi ruangan, kelengkapan pengaturan, dan data pengguna.">
        <a href="{{ route('admin.rooms.create') }}" class="inline-flex min-h-[48px] items-center gap-2 rounded-xl bg-surface px-5 py-3 text-base font-semibold text-primaryDark transition hover:bg-secondaryLight focus-visible:outline-white"><x-schedule-icon name="plus" class="h-4 w-4" />Tambah Ruangan</a>
        <a href="{{ route('admin.schedule.index') }}" class="inline-flex min-h-[48px] items-center gap-2 rounded-xl border border-white/40 px-5 py-3 text-base font-semibold text-white transition hover:bg-white/10 focus-visible:outline-white"><x-schedule-icon name="calendar" class="h-4 w-4" />Jadwal Ruangan</a>
        <x-slot:aside>
            <dl class="grid gap-1 rounded-xl border border-white/20 bg-white/10 px-4 py-3 text-sm text-white/85 backdrop-blur-sm lg:min-w-[240px]">
                <div class="flex justify-between gap-4"><dt>Role</dt><dd class="font-semibold text-white">{{ $user->roleLabel() }}</dd></div>
                <div class="flex justify-between gap-4"><dt>Lokasi</dt><dd class="text-right font-semibold text-white">{{ $scopeLabel }}</dd></div>
                <div class="flex justify-between gap-4"><dt>Unit Kerja</dt><dd class="text-right font-semibold text-white">{{ $user->organizationalUnit?->name ?? 'Belum ditentukan' }}</dd></div>
            </dl>
        </x-slot:aside>
    </x-dashboard-hero>

    <section aria-label="Ringkasan administrasi" class="mb-6 grid gap-4 sm:grid-cols-3">
        @foreach($stats as $stat)
            <x-stat-card :label="$stat['label']" :value="$stat['value']" :description="$stat['description']" :icon="$stat['icon']" />
        @endforeach
    </section>

    <div class="space-y-6">
            <x-workspace-panel title="Kondisi Ruangan" description="Kondisi operasional ruangan aktif, terpisah dari ketersediaan jadwal.">
                <x-slot:headingActions>
                    <a href="{{ route('admin.rooms.index') }}" class="workspace-button-secondary">Kelola kondisi ruangan</a>
                </x-slot:headingActions>
                <div class="p-5 sm:p-6">
                    @if($activeRoomCount)
                        <div class="grid gap-5 lg:grid-cols-3">
                        @foreach($roomConditions as $condition)
                            <div class="min-w-0 rounded-xl bg-background p-4">
                                <p class="text-base text-slate-600">{{ $condition['label'] }}</p>
                                <p class="mb-4 mt-2 text-2xl font-bold text-primaryDark">{{ $condition['count'] }} <span class="text-base font-normal">ruangan</span></p>
                                <div class="h-3 overflow-hidden rounded-full bg-surface" aria-hidden="true"><div class="h-full rounded-full bg-primary" style="width: {{ $condition['percentage'] }}%"></div></div>
                            </div>
                        @endforeach
                        </div>
                    @else
                        <p class="text-base text-slate-600">Belum ada ruangan aktif. Tambahkan atau aktifkan ruangan melalui Daftar Ruangan.</p>
                    @endif
                </div>
            </x-workspace-panel>
            <x-workspace-panel title="Kelengkapan Pengaturan Ruangan" description="Lengkapi penanggung jawab dan fasilitas agar informasi ruangan siap digunakan.">
                <div class="grid gap-4 border-b border-slate-100 p-5 sm:grid-cols-2 sm:p-6">
                    <div class="rounded-xl bg-background p-4"><p class="text-sm text-slate-600">Belum memiliki PIC aktif</p><p class="mt-2 text-2xl font-bold text-primaryDark">{{ $missingPicCount }} <span class="text-base font-normal">ruangan</span></p></div>
                    <div class="rounded-xl bg-background p-4"><p class="text-sm text-slate-600">Fasilitas belum diatur</p><p class="mt-2 text-2xl font-bold text-primaryDark">{{ $missingFacilitiesCount }} <span class="text-base font-normal">ruangan</span></p></div>
                </div>
                <ul class="grid gap-4 p-5 sm:p-6 lg:grid-cols-2">
                    @forelse($attentionRooms as $room)
                        <li class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-slate-200/80 p-4">
                            <div class="min-w-0"><p class="break-words text-base font-semibold text-primaryDark">{{ $room->name }}</p><p class="mt-1 text-sm text-slate-600">{{ collect([(int) $room->active_pics_count === 0 ? 'PIC aktif belum ditetapkan' : null, (int) $room->facilities_count === 0 ? 'Fasilitas belum diatur' : null])->filter()->implode(' / ') }}</p></div>
                            <a href="{{ route('admin.rooms.show', $room) }}" class="workspace-button-secondary" aria-label="Lengkapi pengaturan {{ $room->name }}">Lengkapi</a>
                        </li>
                    @empty
                        <li class="text-base text-slate-600 lg:col-span-2">Tidak ada ruangan aktif yang perlu dilengkapi PIC atau fasilitasnya.</li>
                    @endforelse
                </ul>
                @if($attentionRooms->isNotEmpty())<p class="border-t border-slate-100 p-5 text-sm text-slate-600">Menampilkan maksimal 5 ruangan aktif. <a href="{{ route('admin.rooms.index') }}" class="font-semibold text-primary underline">Lihat seluruh ruangan</a></p>@endif
            </x-workspace-panel>

    </div>
@endsection
