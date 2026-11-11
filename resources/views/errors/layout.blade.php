@extends('layouts.public')

@section('title', 'Halaman tidak tersedia - Temuin Aja')

@section('content')
    <section class="error-page">
        <span class="error-code" aria-hidden="true">@yield('code')</span>
        <span class="eyebrow">TEMUIN AJA</span>
        <h1 class="page-title">@yield('heading')</h1>
        <p class="page-intro">@yield('message')</p>
        <div class="actions">
            <a class="button accent" href="{{ route('home') }}">Kembali ke beranda</a>
            <a class="text-link" href="{{ route('laporan.index', 'ditemukan') }}">Cari barang temuan</a>
        </div>
    </section>
@endsection
