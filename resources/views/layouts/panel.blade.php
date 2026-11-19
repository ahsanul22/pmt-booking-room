{{--
    Layout untuk halaman yang dipakai lebih dari satu role (dashboard, jadwal, ruangan, booking, error).
    Memilih otomatis: User -> employee, PIC/Admin/Super Admin -> workspace, tamu/akun tanpa akses -> public.
    Halaman cukup menulis @extends('layouts.panel') lalu mengisi title, breadcrumb, dan content.
--}}
@extends(match (true) {
    ! auth()->user()?->can('access-general') => 'layouts.public',
    auth()->user()->role === \App\Models\User::ROLE_USER => 'layouts.employee',
    default => 'layouts.workspace',
})
