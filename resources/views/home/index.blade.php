@extends('layouts.home')

@section('content')
    @include('home.hero')

    <div>
        @include('home.reports')
        @include('home.journey')
    </div>
    @include('home.found-cta')
@endsection
