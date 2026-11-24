{{-- Sidebar PIC / Admin / Super Admin. Menu mengikuti Gate; batas akses tetap di route dan controller. --}}
<aside class="relative flex flex-col bg-[#0A234E] text-white lg:sticky lg:top-0 lg:h-screen lg:overflow-y-auto">
    <!-- Sidebar Port Graphic Background -->
    <div aria-hidden="true" class="pointer-events-none absolute inset-0 z-0 overflow-hidden select-none">
        <img src="{{ asset('images/home-harbor.jpg') }}" alt="" class="h-full w-full object-cover object-[22%_center] opacity-40">
        <div class="absolute inset-0 bg-gradient-to-b from-[#0A234E]/95 via-[#0A234E]/85 to-[#0A234E]/60"></div>
    </div>

    <div class="relative z-10 flex items-center justify-between px-7 py-8">
        <a href="{{ route(auth()->user()->dashboardRouteName()) }}" aria-label="Pelindo Multi Terminal, Dashboard" class="inline-block focus:outline-none">
            <x-application-logo variant="dark" class="h-8 sm:h-9 w-auto max-w-[160px] sm:max-w-[175px] object-contain drop-shadow-sm" />
        </a>
        <button type="button" data-menu-toggle aria-expanded="false" aria-controls="schedule-navigation" class="rounded-lg p-3 hover:bg-white/10 focus-visible:outline-white lg:hidden" aria-label="Buka navigasi"><x-schedule-icon name="menu" /></button>
    </div>
    <div id="schedule-navigation" class="relative z-10 hidden flex-1 flex-col px-4 pb-5 lg:flex">
        <div class="mx-3 mb-8 border-t border-white/15 pt-5"><p class="text-base font-semibold tracking-wide text-white">Meeting Room</p><p class="mt-1 text-sm text-secondaryLight">Ruang untuk berkolaborasi.</p></div>
        <p class="px-3 pb-3 text-sm font-semibold uppercase tracking-[0.2em] text-secondaryLight">Workspace</p>
        <nav aria-label="Navigasi utama" class="space-y-1">
            <a @class(['schedule-nav', 'schedule-nav-active' => request()->routeIs('dashboard', 'pic.dashboard', 'admin.dashboard')]) @if(request()->routeIs('dashboard', 'pic.dashboard', 'admin.dashboard')) aria-current="page" @endif href="{{ route(auth()->user()->dashboardRouteName()) }}"><x-schedule-icon name="grid" />Dashboard</a>
            <a @class(['schedule-nav', 'schedule-nav-active' => request()->routeIs('*.schedule.index', 'schedule.index')]) @if(request()->routeIs('*.schedule.index', 'schedule.index')) aria-current="page" @endif href="{{ route(auth()->user()->can('access-admin') ? 'admin.schedule.index' : 'schedule.index') }}">
                <x-schedule-icon />Jadwal Ruangan
                @if(request()->routeIs('*.schedule.index', 'schedule.index'))
                    <span class="ml-auto h-1.5 w-1.5 rounded-full bg-secondary"></span>
                @endif
            </a>
            @can('access-pic')
                @cannot('access-admin')
                    <a @class(['schedule-nav', 'schedule-nav-active' => request()->routeIs('pic.approvals.index', 'pic.approvals.show')]) @if(request()->routeIs('pic.approvals.index', 'pic.approvals.show')) aria-current="page" @endif href="{{ route('pic.approvals.index') }}">
                        <x-schedule-icon name="clock" />
                        <span>Permintaan Approval</span>
                        @if(($pendingApprovalsCount ?? 0) > 0)
                            <span class="ml-auto inline-flex items-center justify-center rounded-full bg-danger px-2 py-0.5 text-xs font-bold text-white shadow-sm" aria-label="{{ $pendingApprovalsCount }} permintaan approval baru">
                                {{ $pendingApprovalsCount > 99 ? '99+' : $pendingApprovalsCount }}
                            </span>
                        @endif
                    </a>
                    <a @class(['schedule-nav', 'schedule-nav-active' => request()->routeIs('pic.rooms.*')]) @if(request()->routeIs('pic.rooms.*')) aria-current="page" @endif href="{{ route('pic.rooms.index') }}"><x-schedule-icon name="room" />Ruangan Saya</a>
                    <a @class(['schedule-nav', 'schedule-nav-active' => request()->routeIs('pic.approvals.history')]) @if(request()->routeIs('pic.approvals.history')) aria-current="page" @endif href="{{ route('pic.approvals.history') }}"><x-schedule-icon name="calendar" />Riwayat Approval</a>
                @endcannot
            @endcan
            @if(auth()->user()->role !== \App\Models\User::ROLE_ROOM_PIC)
            <a @class(['schedule-nav', 'schedule-nav-active' => request()->routeIs('admin.rooms.*', 'rooms.index', 'my-bookings.create', 'rooms.book')]) @if(request()->routeIs('admin.rooms.*', 'rooms.index', 'my-bookings.create', 'rooms.book')) aria-current="page" @endif href="{{ route(auth()->user()->can('access-admin') ? 'admin.rooms.index' : 'rooms.index') }}"><x-schedule-icon name="room" />{{ auth()->user()->can('access-admin') ? 'Daftar Ruangan' : 'Booking Ruangan' }}</a>
            @can('access-employee')
                <a @class(['schedule-nav', 'schedule-nav-active' => request()->routeIs('my-bookings.index', 'my-bookings.show')]) @if(request()->routeIs('my-bookings.index', 'my-bookings.show')) aria-current="page" @endif href="{{ route('my-bookings.index') }}"><x-schedule-icon name="clock" />Riwayat</a>
            @endcan
            @endif
            @can('access-admin')
                <a @class(['schedule-nav', 'schedule-nav-active' => request()->routeIs('admin.bookings.*')]) @if(request()->routeIs('admin.bookings.*')) aria-current="page" @endif href="{{ route('admin.bookings.index') }}"><x-schedule-icon name="clock" />Semua Booking</a>
            @endcan
            @can('access-admin')
                <a @class(['schedule-nav', 'schedule-nav-active' => request()->routeIs('admin.approvals.*', 'pic.approvals.*')]) @if(request()->routeIs('admin.approvals.*', 'pic.approvals.*')) aria-current="page" @endif href="{{ route('admin.approvals.index') }}">
                    <x-schedule-icon name="grid" />
                    <span>Approval Ruangan</span>
                    @if(($pendingApprovalsCount ?? 0) > 0)
                        <span class="ml-auto inline-flex items-center justify-center rounded-full bg-danger px-2 py-0.5 text-xs font-bold text-white shadow-sm" aria-label="{{ $pendingApprovalsCount }} pengajuan approval baru">
                            {{ $pendingApprovalsCount > 99 ? '99+' : $pendingApprovalsCount }}
                        </span>
                    @endif
                </a>
            @endcan
            @can('access-pic')
                <a @class(['schedule-nav', 'schedule-nav-active' => request()->routeIs('*.reports.*')]) @if(request()->routeIs('*.reports.*')) aria-current="page" @endif href="{{ route(auth()->user()->can('access-admin') ? 'admin.reports.index' : 'pic.reports.index') }}"><x-schedule-icon name="calendar" />Laporan Bulanan</a>
            @endcan
        </nav>
        @can('access-admin')
            <p class="px-3 pb-3 pt-8 text-sm font-semibold uppercase tracking-[0.2em] text-secondaryLight">Administrasi</p>
            <nav aria-label="Master data" class="space-y-1">
                @can('access-super-admin')<a @class(['schedule-nav', 'schedule-nav-active' => request()->routeIs('admin.branches.*')]) href="{{ route('admin.branches.index') }}"><x-schedule-icon name="grid" />Kantor & Cabang</a>@endcan
                <a @class(['schedule-nav', 'schedule-nav-active' => request()->routeIs('admin.users.*')]) @if(request()->routeIs('admin.users.*')) aria-current="page" @endif href="{{ route('admin.users.index') }}"><x-schedule-icon name="grid" />Pengguna</a>
                @can('access-super-admin')<a @class(['schedule-nav', 'schedule-nav-active' => request()->routeIs('admin.organizational-units.*')]) @if(request()->routeIs('admin.organizational-units.*')) aria-current="page" @endif href="{{ route('admin.organizational-units.index') }}"><x-schedule-icon name="grid" />Unit Organisasi</a>@endcan
                @can('access-super-admin')<a @class(['schedule-nav', 'schedule-nav-active' => request()->routeIs('admin.facilities.*')]) @if(request()->routeIs('admin.facilities.*')) aria-current="page" @endif href="{{ route('admin.facilities.index') }}"><x-schedule-icon name="grid" />Fasilitas</a>@endcan
                @can('access-super-admin')<a @class(['schedule-nav', 'schedule-nav-active' => request()->routeIs('admin.whatsapp.*')]) @if(request()->routeIs('admin.whatsapp.*')) aria-current="page" @endif href="{{ route('admin.whatsapp.index') }}"><x-schedule-icon name="clock" />Notifikasi</a>@endcan
            </nav>
        @endcan
        <div class="mt-auto pt-10">
            <div class="rounded-2xl border border-white/15 bg-white/10 p-4 shadow-sm backdrop-blur-md">
                <p class="text-sm font-semibold tracking-wider text-secondaryLight uppercase">SATU RUANG, BANYAK IDE.</p>
                <p class="mt-2 text-sm leading-5 text-white/75">Mulai kolaborasi yang baik dengan perencanaan yang tepat.</p>
            </div>

        </div>
    </div>
</aside>
