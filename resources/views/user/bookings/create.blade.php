@extends('layouts.panel')
@section('title', 'Rencanakan Booking')
@section('breadcrumb', 'Booking Ruangan / Detail Pertemuan')
@section('content')
    <livewire:booking-preparation-form :room-id="$room->id" />
@endsection
