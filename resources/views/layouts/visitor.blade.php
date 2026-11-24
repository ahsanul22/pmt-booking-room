{{-- Layout tamu (/tamu). Isi section visitor-background untuk memakai latar pelabuhan. --}}
@extends('layouts.app')

@section('document-title')
    @yield('title') · Pelindo Multi Terminal
@endsection
@section('body-class', 'relative isolate flex min-h-screen flex-col bg-background font-sans text-text antialiased')
@section('without-page-loading', '1')

@section('body')
    @hasSection('visitor-background')
        <div aria-hidden="true" class="pointer-events-none fixed inset-0 -z-10">
            <img src="{{ asset('images/home-harbor.jpg') }}" alt="" class="h-full w-full object-cover opacity-60">
            <div class="absolute inset-0 bg-gradient-to-b from-background/85 via-background/65 to-secondaryLight/50"></div>
        </div>
    @endif
    @include('layouts.partials.skip-link')
    <header class="border-b border-slate-200/80 bg-surface/90 px-5 py-4 backdrop-blur-md">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-3 rounded-lg" aria-label="Pelindo Multi Terminal, Beranda">
                <x-application-logo variant="light" class="h-8 w-auto max-w-[150px] object-contain sm:h-9 sm:max-w-[185px]" />
                <span aria-hidden="true" class="hidden h-7 w-px bg-slate-200 sm:block"></span>
                <span class="hidden text-base font-semibold text-primaryDark sm:block">Layanan Tamu</span>
            </a>
            <a href="{{ route('home') }}" class="inline-flex min-h-[44px] items-center rounded-xl px-4 text-base font-semibold text-primaryDark transition hover:bg-background">Beranda</a>
        </div>
    </header>
    <main id="content" class="mx-auto flex w-full max-w-7xl flex-1 flex-col p-5 lg:p-9">
        @hasSection('visitor-background')
            @yield('content')
        @else
            <h1 class="mb-4 text-3xl font-bold text-primaryDark">@yield('title')</h1>
            <x-form-feedback />
            @yield('content')
        @endif
    </main>
    @include('shared.footer')
@endsection
