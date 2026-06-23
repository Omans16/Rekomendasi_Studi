<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SEMEGAH - Sistem Rekomendasi')</title>

    {{-- Prevent theme flicker --}}
    <script>
        (function () {
            try {
                const savedTheme = localStorage.getItem('app-theme') || 'light';
                document.documentElement.setAttribute('data-theme', savedTheme);
            } catch (error) {
                document.documentElement.setAttribute('data-theme', 'light');
            }
        })();
    </script>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@600;700;800;900&display=swap"
        rel="stylesheet"
    >

    {{-- Icons --}}
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
    >

    {{-- Global CSS --}}
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">

    {{-- CSS per halaman --}}
    @stack('styles')
</head>
<body>

    <div class="app-layout">
        @include('components.sidebar')

        <div class="main-wrapper">
            @include('components.navbar')

            <main class="content" id="main-content">
                @yield('content')
            </main>
        </div>
    </div>

    {{-- Global JS --}}
    <script src="{{ asset('js/layout.js') }}" defer></script>

    {{-- JS per halaman --}}
    @stack('scripts')

</body>
</html>