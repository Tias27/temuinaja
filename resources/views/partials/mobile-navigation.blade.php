@php
    $mobileHome = request()->routeIs('home', 'home.page', 'mockup', 'mockup.page');
    $mobileGuide = $mobileHome ? '#cara-kerja' : route('home') . '#cara-kerja';
@endphp
<nav class="mobile-navigation" aria-label="Navigasi utama ponsel">
    <a href="{{ route('home') }}" data-mobile-nav="home"
        @if ($mobileHome) aria-current="page" @endif>
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1V10Z"
                stroke="currentColor" stroke-width="1.6" stroke-linejoin="round" />
        </svg>
        <span>Beranda</span>
    </a>
    <a href="{{ route('laporan.index', 'ditemukan') }}" data-mobile-nav="items"
        @if (request()->routeIs('laporan.index', 'laporan.page', 'laporan.search.index', 'laporan.show')) aria-current="page" @endif>
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M4 8h16l1 12H3L4 8Z" stroke="currentColor" stroke-width="1.6"
                stroke-linejoin="round" />
            <path d="M8 9V6a4 4 0 0 1 8 0v3" stroke="currentColor" stroke-width="1.6"
                stroke-linecap="round" />
        </svg>
        <span>Barang</span>
    </a>
    <a href="{{ route('laporan.choose') }}" data-mobile-nav="report"
        @if (request()->routeIs('laporan.choose', 'laporan.create', 'laporan.confirmation')) aria-current="page" @endif>
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M14 3H5v18h14V8l-5-5Zm0 0v5h5M8 12h8m-8 4h5" stroke="currentColor"
                stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <span>Laporan</span>
    </a>
    <a href="{{ $mobileGuide }}" data-mobile-nav="guide">
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.6" />
            <path d="M9.5 9a2.5 2.5 0 0 1 5 .2c0 1.8-2.5 2-2.5 3.8m0 3v.1" stroke="currentColor"
                stroke-width="1.6" stroke-linecap="round" />
        </svg>
        <span>Cara kerja</span>
    </a>
</nav>
