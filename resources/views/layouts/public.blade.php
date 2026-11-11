<!doctype html>
<html lang="id">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        @include('partials.favicon')

        <title>
            @yield('title', 'Temuin Aja')
        </title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])

    </head>

    <body class="site-body has-mobile-navigation">
        @include('partials.site-header')
        <main id="main-content">
            @yield('content')
        </main>
        @include('partials.site-footer')
        @include('partials.mobile-navigation')
        @include('partials.whatsapp-button')
    </body>

</html>
