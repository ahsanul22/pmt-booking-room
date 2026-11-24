{{-- Layout publik sebelum login: beranda dan halaman error untuk tamu. --}}
@extends('layouts.app')

@section('document-title')
    @yield('title', 'Booking Room') &middot; Pelindo Multi Terminal
@endsection
@section('body-class', 'flex min-h-screen flex-col bg-background font-sans text-text antialiased')

@section('body')
    @include('layouts.partials.skip-link')
    <div class="flex min-h-screen flex-col">
        <header class="sticky top-0 z-30 border-b border-slate-200/80 bg-surface/90 backdrop-blur-md">
            <nav aria-label="Navigasi utama" class="mx-auto flex max-w-[1800px] items-center justify-between gap-4 px-5 py-4 sm:px-8">
                <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-3 rounded-lg text-primaryDark" aria-label="Pelindo Multi Terminal, Beranda">
                    <x-application-logo variant="light" class="h-8 w-auto max-w-[150px] object-contain sm:h-9 sm:max-w-[185px]" />
                    <span aria-hidden="true" class="hidden h-7 w-px bg-slate-200 sm:block"></span>
                    <span class="hidden text-base font-semibold text-primaryDark sm:block">Booking Room</span>
                </a>
                <div class="flex items-center gap-2 sm:gap-3">
                    <a href="{{ route('guests.register') }}" class="hidden min-h-[44px] items-center rounded-xl px-4 text-base font-semibold text-primaryDark transition hover:bg-background md:inline-flex">Pendataan Tamu</a>
                    <a href="{{ route('login') }}" class="workspace-button">Login</a>
                </div>
            </nav>
        </header>
        <main id="content" class="mx-auto w-full max-w-[1800px] flex-1 p-5 sm:p-8">@yield('content')</main>
    </div>
    @include('shared.footer')
@endsection
