<!doctype html>
<html lang="id">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        @include('partials.favicon')

        @if ($preview)
            <meta name="robots" content="noindex, nofollow">
        @endif

        <title>
            {{ $preview ? 'Pratinjau konsep — Temuin Aja' : 'Temuin Aja — Barang Hilang dan Ditemukan' }}
        </title>

        @vite(['resources/css/home.css', 'resources/js/home.js'])

    </head>

    <body class="has-mobile-navigation {{ $preview ? 'preview' : 'live' }}">
        @if ($preview)
            <div class="preview-note">
                <span>Pratinjau konsep · Beranda alternatif</span>
                <a href="{{ route('home') }}">Kembali ke situs aktif</a>
            </div>
        @endif

        <div class="shell">
            @include('partials.site-header')

            <main id="main-content">
                @yield('content')
            </main>

            @include('partials.site-footer')

        </div>
        @include('partials.mobile-navigation')
        @include('partials.whatsapp-button')
    </body>

</html>
