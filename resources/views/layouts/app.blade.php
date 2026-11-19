{{--
    Kerangka HTML tunggal. Semua layout lain meng-extend file ini, jadi <head>, aset Vite,
    dan indikator loading cukup diubah di sini.

    Section yang tersedia:
    - document-title  : isi <title>
    - body-class      : class <body> (default latar aplikasi)
    - body            : isi halaman
    - without-page-loading : isi section ini untuk mematikan indikator loading
    Stack: head (tambahan di <head>), scripts (sebelum </body>).
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{-- Section sudah di-escape saat dirender; trim hanya membuang spasi dari penulisan section multi-baris. --}}
    <title>{!! trim($__env->yieldContent('document-title', 'Booking Ruang Rapat · Pelindo Multi Terminal')) !!}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="@yield('body-class', 'bg-background font-sans text-text antialiased')">
    @yield('body')
    @stack('scripts')
    @unless(View::hasSection('without-page-loading'))
        @include('shared.page-loading')
    @endunless
</body>
</html>
