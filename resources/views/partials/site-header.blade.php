@php
    $onHome = request()->routeIs('home', 'home.page', 'mockup', 'mockup.page');
    $itemsUrl = route('laporan.index', 'ditemukan');
    $guideUrl = $onHome ? '#cara-kerja' : route('home') . '#cara-kerja';
@endphp
<a class="skip-link" href="#main-content">Langsung ke isi</a>
<header class="site-header">
    @include('partials.brand')
    <nav class="site-nav" aria-label="Navigasi utama">
        <a href="{{ $itemsUrl }}"
            @if (request()->routeIs('laporan.index', 'laporan.page', 'laporan.search.index', 'laporan.show')) aria-current="page" @endif>Barang</a>
        <a href="{{ $guideUrl }}">Cara kerja</a>
        <a href="{{ route('laporan.choose') }}"
            @if (request()->routeIs('laporan.choose', 'laporan.create')) aria-current="page" @endif>Laporan</a>
    </nav>
    <a class="nav-cta" href="{{ route('laporan.choose') }}">Buat laporan</a>
    <a class="mobile-header-search" href="{{ $itemsUrl }}" aria-label="Cari barang">
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle cx="10.5" cy="10.5" r="6.5" stroke="currentColor"
                stroke-width="1.7" />
            <path d="m15.5 15.5 4 4" stroke="currentColor" stroke-width="1.7"
                stroke-linecap="round" />
        </svg>
    </a>
</header>
