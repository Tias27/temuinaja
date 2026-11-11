<!doctype html>
<html lang="id">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex, nofollow">
        @include('partials.favicon')
        <title>@yield('title', 'Panel petugas') - Temuin Aja</title>
        @vite(['resources/css/admin.css', 'resources/js/app.js'])
    </head>

    <body class="site-body admin-body">
        <a class="skip-link" href="#main-content">Langsung ke isi</a>
        <header class="site-header admin-header">
            @include('partials.brand')
            <span class="admin-header-label">Ruang petugas</span>
            @auth('admin')
                <div class="admin-user">
                    <span>{{ auth('admin')->user()->nama_admin }}</span>
                    <form method="post" action="{{ route('admin.logout') }}">
                        @csrf
                        <button class="button" type="submit">Keluar</button>
                    </form>
                </div>
            @else
                <a class="text-link" href="{{ route('home') }}">Ke beranda</a>
            @endauth
        </header>
        @auth('admin')
            <nav class="admin-navigation" aria-label="Navigasi petugas">
                <a href="{{ route('admin.index') }}"
                    @if (request()->routeIs('admin.index', 'admin.show')) aria-current="page" @endif>
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M8 4h12v16H8M4 8h8M4 12h8M4 16h8" stroke="currentColor"
                            stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    Kelola laporan
                </a>
                <a href="{{ route('admin.settings.index') }}"
                    @if (request()->routeIs('admin.settings.*')) aria-current="page" @endif>
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M4 7h16M4 17h16M8 4v6m8 4v6" stroke="currentColor" stroke-width="1.6"
                            stroke-linecap="round" />
                    </svg>
                    Pengaturan
                </a>
            </nav>
        @endauth
        <main class="admin-main" id="main-content">
            @if (session('sukses'))
                <div class="alert" role="status">{{ session('sukses') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-error" role="alert">{{ $errors->first() }}</div>
            @endif
            @yield('content')
        </main>
        @include('partials.site-footer')
    </body>

</html>
