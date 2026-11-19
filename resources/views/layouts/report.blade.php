{{-- Layout cetak / PDF laporan bulanan, tanpa navigasi. --}}
@extends('layouts.app')

@section('document-title')
    @yield('title') · Pelindo Multi Terminal
@endsection
@section('body-class', 'report-page bg-background font-sans text-text')

@section('body')
    <main id="content" class="mx-auto max-w-[1600px] p-5 sm:p-8">
        <p class="mb-5 font-bold text-primaryDark">PT Pelindo Multi Terminal · Booking Ruang Rapat</p>
        @yield('content')
    </main>
@endsection
