{{-- Layout role User (pegawai): navbar atas. Halaman mengisi title dan content. --}}
@extends('layouts.app')

@section('document-title')
    @yield('title', 'Booking Room')
@endsection

@section('body')
    @include('layouts.partials.skip-link')
    <div class="flex min-h-screen flex-col">
        @include('layouts.partials.employee-navbar')
        <main id="content" class="mx-auto w-full max-w-[1800px] flex-1 p-5 sm:p-8">
            @if(request()->routeIs('dashboard', 'schedule.index', 'my-bookings.create', 'rooms.book', 'rooms.index', 'rooms.show', 'my-bookings.index', 'my-bookings.show'))
                @yield('content')
            @else
                <div class="user-module min-w-0 rounded-2xl border border-slate-200/80 bg-surface p-5 shadow-sm sm:p-6">@yield('content')</div>
            @endif
        </main>
    </div>
    @include('shared.footer')
@endsection
