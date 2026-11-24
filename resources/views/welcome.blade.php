@extends('layouts.public')
@section('title', 'Selamat Datang di Booking Room')
@push('head')
    <link rel="preload" as="image" href="{{ asset('images/home-harbor.jpg') }}">
@endpush
@section('content')
<div class="grid w-full min-w-0 gap-8 sm:gap-12">
    {{-- Hero: foto pelabuhan dengan lapisan gradien agar teks tetap kontras. --}}
    <section aria-labelledby="welcome-title" class="relative isolate overflow-hidden rounded-3xl bg-primaryDark">
        <img src="{{ asset('images/home-harbor.jpg') }}" alt="" width="1536" height="1024" fetchpriority="high" class="absolute inset-0 -z-20 h-full w-full object-cover object-[70%_center]">
        <div aria-hidden="true" class="absolute inset-0 -z-10 bg-gradient-to-r from-primaryDark via-primaryDark/85 to-primaryDark/20"></div>
        <div class="grid gap-10 px-6 py-14 sm:px-10 sm:py-20 lg:grid-cols-[minmax(0,1.15fr)_minmax(0,0.85fr)] lg:items-center lg:px-14 lg:py-24">
            <div class="min-w-0 text-white">
                <p class="inline-flex items-center gap-2 rounded-full border border-white/25 bg-white/10 px-4 py-1.5 text-sm font-semibold text-secondaryLight backdrop-blur-sm">
                    <span aria-hidden="true" class="h-2 w-2 rounded-full bg-secondary"></span>Booking Ruang Rapat Internal
                </p>
                <h1 id="welcome-title" class="mt-6 max-w-2xl text-4xl font-bold leading-[1.1] tracking-tight sm:text-5xl lg:text-6xl">Pertemuan yang baik dimulai di <span class="text-secondaryLight">ruang yang tepat.</span></h1>
                <p class="mt-6 max-w-xl text-base leading-7 text-white/85 sm:text-lg sm:leading-8">Cek ketersediaan, ajukan booking, dan pantau persetujuan ruang rapat PT Pelindo Multi Terminal dalam satu tempat.</p>
                <div class="mt-9 flex flex-wrap gap-3">
                    <a href="{{ route('login') }}" class="inline-flex min-h-[48px] items-center gap-2 rounded-xl bg-surface px-6 py-3 text-base font-semibold text-primaryDark shadow-lg shadow-black/10 transition hover:bg-secondaryLight focus-visible:outline-white">Masuk sebagai Pegawai<x-schedule-icon name="arrow" class="h-4 w-4" /></a>
                    <a href="{{ route('guests.register') }}" class="inline-flex min-h-[48px] items-center gap-2 rounded-xl border border-white/40 px-6 py-3 text-base font-semibold text-white transition hover:bg-white/10 focus-visible:outline-white">Pendataan Tamu</a>
                </div>
            </div>

            <ul class="hidden min-w-0 gap-3 lg:grid" aria-label="Yang bisa dilakukan">
                @foreach ([
                    ['icon' => 'calendar', 'title' => 'Jadwal terkini', 'text' => 'Lihat jam yang sudah terisi sebelum mengajukan.'],
                    ['icon' => 'room', 'title' => 'Booking dalam satu form', 'text' => 'Ruangan, waktu, agenda, dan kebutuhan rapat.'],
                    ['icon' => 'clock', 'title' => 'Status yang jelas', 'text' => 'Pending, Approved, atau Rejected beserta alasannya.'],
                ] as $item)
                    <li class="flex items-start gap-4 rounded-2xl border border-white/15 bg-white/10 p-5 text-white backdrop-blur-md">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white/15 text-secondaryLight"><x-schedule-icon :name="$item['icon']" class="h-5 w-5" /></span>
                        <span class="min-w-0"><span class="block text-base font-semibold">{{ $item['title'] }}</span><span class="mt-1 block text-sm leading-6 text-white/75">{{ $item['text'] }}</span></span>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    {{-- Cara kerja --}}
    <section aria-labelledby="steps-title" class="min-w-0">
        <div class="max-w-2xl">
            <p class="text-sm font-bold uppercase tracking-[0.18em] text-primary">Cara kerja</p>
            <h2 id="steps-title" class="mt-3 text-2xl font-bold tracking-tight text-primaryDark sm:text-3xl">Tiga langkah, rapat siap.</h2>
        </div>
        <ol class="mt-8 grid gap-4 md:grid-cols-3">
            @foreach ([
                ['title' => 'Masuk', 'text' => 'Gunakan akun pegawai yang diberikan Admin kantor Anda.'],
                ['title' => 'Pilih ruangan dan waktu', 'text' => 'Sistem menolak jam yang bentrok, jadi slot yang Anda ajukan pasti masih kosong.'],
                ['title' => 'Pantau di My Booking', 'text' => 'Ruangan tertentu perlu persetujuan PIC. Hasilnya tampil di riwayat booking Anda.'],
            ] as $step)
                <li class="relative min-w-0 rounded-2xl border border-slate-200/80 bg-surface p-6 shadow-sm">
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-primary text-base font-bold text-white">{{ $loop->iteration }}</span>
                    <h3 class="mt-5 text-lg font-semibold text-primaryDark">{{ $step['title'] }}</h3>
                    <p class="mt-2 text-base leading-7 text-slate-600">{{ $step['text'] }}</p>
                </li>
            @endforeach
        </ol>
    </section>

    {{-- Peran pengguna --}}
    <section aria-labelledby="roles-title" class="min-w-0 rounded-3xl border border-slate-200/80 bg-surface p-6 shadow-sm sm:p-10">
        <div class="grid gap-8 lg:grid-cols-[minmax(0,0.8fr)_minmax(0,2fr)] lg:gap-12">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.18em] text-primary">Satu sistem</p>
                <h2 id="roles-title" class="mt-3 text-2xl font-bold tracking-tight text-primaryDark sm:text-3xl">Setiap peran punya ruang kerjanya.</h2>
                <p class="mt-4 text-base leading-7 text-slate-600">Menu dan data menyesuaikan peran serta kantor/cabang akun Anda.</p>
            </div>
            <ul class="grid gap-4 sm:grid-cols-3">
                @foreach ([
                    ['icon' => 'room', 'title' => 'Pegawai', 'text' => 'Mengajukan booking, mencantumkan tamu rapat, dan memantau status.'],
                    ['icon' => 'clock', 'title' => 'PIC Ruangan', 'text' => 'Meninjau dan memutuskan pengajuan untuk ruangan yang ditugaskan.'],
                    ['icon' => 'grid', 'title' => 'Admin', 'text' => 'Mengelola ruangan, akun, akses unit, serta laporan bulanan.'],
                ] as $role)
                    <li class="min-w-0 rounded-2xl bg-background p-5">
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-surface text-primary shadow-sm"><x-schedule-icon :name="$role['icon']" class="h-5 w-5" /></span>
                        <h3 class="mt-4 text-base font-semibold text-primaryDark">{{ $role['title'] }}</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">{{ $role['text'] }}</p>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    {{-- Ajakan untuk tamu --}}
    <section aria-labelledby="guest-title" class="flex flex-col items-start justify-between gap-6 rounded-3xl bg-gradient-to-r from-primaryDark to-primary p-6 text-white sm:p-10 md:flex-row md:items-center">
        <div class="max-w-2xl">
            <h2 id="guest-title" class="text-2xl font-bold tracking-tight sm:text-3xl">Datang sebagai tamu?</h2>
            <p class="mt-3 text-base leading-7 text-white/85">Isi pendataan tamu untuk melihat jadwal ruangan umum di kantor tujuan. Booking tetap diajukan oleh pegawai yang mengundang Anda.</p>
        </div>
        <a href="{{ route('guests.register') }}" class="inline-flex min-h-[48px] shrink-0 items-center gap-2 rounded-xl bg-surface px-6 py-3 text-base font-semibold text-primaryDark transition hover:bg-secondaryLight focus-visible:outline-white">Isi Pendataan Tamu<x-schedule-icon name="arrow" class="h-4 w-4" /></a>
    </section>
</div>
@endsection
