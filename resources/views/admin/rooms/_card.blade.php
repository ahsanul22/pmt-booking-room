{{-- Kartu ringkas Daftar Ruangan (Admin). Data dari RoomController@index: branch, floor, facilities, pics, dan hitungan booking. --}}
@php
    $statusLabel = ['available' => 'Operasional', 'maintenance' => 'Dalam perawatan', 'unavailable' => 'Tidak dapat digunakan'][$item->status] ?? $item->status;
    $facilityCount = $item->facilities->count();
@endphp
<article @class(['group flex min-w-0 flex-col rounded-2xl border bg-surface p-4 shadow-sm transition hover:border-secondary hover:shadow-md sm:p-5', 'border-slate-200' => $item->is_active, 'border-dashed border-slate-300 opacity-80' => ! $item->is_active]) aria-labelledby="room-title-{{ $item->id }}">
    <div class="flex items-start gap-3">
        <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primaryDark text-white"><x-schedule-icon name="room" class="h-5 w-5" /></span>
        <div class="min-w-0 flex-1">
            <h3 id="room-title-{{ $item->id }}" class="break-words text-base font-bold leading-6 text-primaryDark sm:text-lg">
                <a href="{{ route('admin.rooms.show', $item) }}" class="hover:underline">{{ $item->name }}</a>
            </h3>
            <p class="mt-0.5 break-words text-sm leading-5 text-slate-600">{{ $item->code ?? 'Tanpa kode' }} &middot; {{ $item->floor?->name ?? 'Lantai belum ditentukan' }} &middot; {{ $item->branch?->name }}</p>
        </div>
        <span @class(['inline-flex shrink-0 items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold', 'bg-secondaryLight/30 text-primaryDark' => $item->is_active, 'bg-background text-slate-600' => ! $item->is_active])>
            <span @class(['h-1.5 w-1.5 rounded-full', 'bg-primary' => $item->is_active, 'bg-muted' => ! $item->is_active]) aria-hidden="true"></span>{{ $item->is_active ? 'Aktif' : 'Nonaktif' }}
        </span>
    </div>

    <ul class="mt-4 flex flex-wrap gap-2 text-sm" aria-label="Pengaturan ruangan">
        <li title="{{ $item->status }}" @class(['inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 font-medium', 'bg-secondaryLight/25 text-primaryDark' => $item->status === 'available', 'bg-warning/15 text-warningDark' => $item->status === 'maintenance', 'bg-danger/10 text-danger' => $item->status === 'unavailable'])>
            <span @class(['h-1.5 w-1.5 rounded-full', 'bg-primary' => $item->status === 'available', 'bg-warning' => $item->status === 'maintenance', 'bg-danger' => $item->status === 'unavailable']) aria-hidden="true"></span>{{ $statusLabel }}
        </li>
        <li title="{{ $item->access_type }}" class="inline-flex items-center rounded-lg bg-background px-2.5 py-1 text-slate-700">
            {{ $item->access_type === 'all' ? 'Semua pegawai' : 'Unit tertentu ('.$item->allowed_organizational_units_count.' unit)' }}
        </li>
        <li class="inline-flex items-center rounded-lg bg-background px-2.5 py-1 text-slate-700">Approval PIC: {{ $item->requires_approval ? 'Ya' : 'Tidak' }}</li>
    </ul>

    <dl class="mt-4 grid grid-cols-3 divide-x divide-slate-200 rounded-xl border border-slate-200 text-center">
        <div class="px-2 py-2.5"><dt class="text-xs text-slate-500">Booking hari ini</dt><dd class="mt-0.5 text-lg font-bold tabular-nums text-primaryDark">{{ $item->today_bookings_count }}</dd></div>
        <div class="px-2 py-2.5"><dt class="text-xs text-slate-500">Menunggu PIC</dt><dd @class(['mt-0.5 text-lg font-bold tabular-nums', 'text-warningDark' => $item->pending_bookings_count > 0, 'text-primaryDark' => ! $item->pending_bookings_count])>{{ $item->pending_bookings_count }}</dd></div>
        <div class="px-2 py-2.5"><dt class="text-xs text-slate-500">Fasilitas</dt><dd class="mt-0.5 text-lg font-bold tabular-nums text-primaryDark">{{ $facilityCount }}</dd></div>
    </dl>

    <div class="mt-4 space-y-2 text-sm leading-5">
        <p class="flex gap-2"><span class="w-16 shrink-0 text-slate-500">PIC</span>
            @if($item->pics->isNotEmpty())
                <span class="min-w-0 break-words font-medium text-text">{{ $item->pics->pluck('name')->join(', ') }}</span>
            @else
                <span class="font-semibold text-danger">Belum ada PIC</span>
            @endif
        </p>
        <div class="flex gap-2"><span class="w-16 shrink-0 text-slate-500">Fasilitas</span>
            <div class="flex min-w-0 flex-wrap gap-1.5">
                @forelse($item->facilities->take(3) as $facility)
                    <span class="max-w-full break-words rounded-md bg-background px-2 py-0.5 text-slate-700">{{ $facility->name }}</span>
                @empty
                    <span class="text-slate-500">Belum ada</span>
                @endforelse
                @if($facilityCount > 3)
                    <span class="rounded-md px-1 py-0.5 font-medium text-slate-500" title="{{ $item->facilities->skip(3)->pluck('name')->join(', ') }}">+{{ $facilityCount - 3 }} lainnya</span>
                @endif
            </div>
        </div>
    </div>

    <div class="mt-auto flex gap-2 pt-4">
        <a href="{{ route('admin.rooms.show', $item) }}" class="inline-flex min-h-[40px] flex-1 items-center justify-center gap-1.5 rounded-xl bg-primary px-4 py-2 text-sm font-semibold text-white transition hover:bg-primaryDark" aria-label="Kelola {{ $item->name }}">Kelola<x-schedule-icon name="arrow" class="h-4 w-4 shrink-0" /></a>
        <a href="{{ route('admin.schedule.index') }}" class="inline-flex min-h-[40px] items-center justify-center gap-1.5 rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-primary transition hover:bg-background" aria-label="Lihat jadwal {{ $item->name }}"><x-schedule-icon name="calendar" class="h-4 w-4 shrink-0" />Jadwal</a>
    </div>
</article>
