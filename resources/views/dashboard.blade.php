@extends('layouts.panel')

@section('title', $title)
@section('breadcrumb', 'Dashboard')

@section('content')
    @if(session('status'))
        <div role="status" class="mb-6 rounded-xl border border-secondaryLight bg-surface p-4 text-base text-primaryDark">{{ session('status') }}</div>
    @endif
    @can('access-pic')
        @include('pic.dashboard')
    @else
        @include('user.dashboard')
    @endcan
@endsection
