{{-- Layout PIC, Admin, dan Super Admin: sidebar biru gelap + topbar. Halaman mengisi title, breadcrumb, content. --}}
@extends('layouts.app')

@section('document-title')
    @yield('title', 'Jadwal Ruangan')
@endsection

@section('body')
    @include('layouts.partials.skip-link')
    <div class="flex min-h-screen flex-col lg:grid lg:grid-cols-[248px_minmax(0,1fr)]">
        @include('layouts.partials.workspace-sidebar')
        <div class="min-w-0 flex-1">
            <div class="flex min-h-screen flex-col">
                @include('layouts.partials.workspace-topbar')
                <main id="content" @class(['mx-auto w-full flex-1 p-5 lg:p-9', 'max-w-[1800px]' => request()->routeIs('dashboard', '*.dashboard'), 'max-w-[1600px]' => ! request()->routeIs('dashboard', '*.dashboard')])>@yield('content')</main>
            </div>
            @include('shared.footer')
        </div>
    </div>
@endsection
