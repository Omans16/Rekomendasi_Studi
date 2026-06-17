<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>StudyPath SMK</title>

    {{-- GLOBAL CSS --}}
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">

    {{-- CSS PER HALAMAN --}}
    @stack('styles')

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@600;700;800;900&display=swap" rel="stylesheet">

    <script>
        (function () {
            const savedTheme = localStorage.getItem('app-theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>
</head>
<body>

<div class="app">

    {{-- Sidebar --}}
    @include('components.sidebar')

    {{-- Main Area --}}
    <div class="main-wrapper">

        {{-- Navbar (FIXED HEADER) --}}
        @include('components.navbar')

        {{-- Content --}}
        <main class="content">
            @yield('content')
        </main>

    </div>

</div>

{{-- JS --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const themeToggle = document.getElementById('theme-toggle');
    const themeText = document.getElementById('themeToggleText');

    function applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        document.body.classList.toggle('dark', theme === 'dark');
        localStorage.setItem('app-theme', theme);

        if (themeText) {
            themeText.textContent = theme === 'dark' ? 'Dark' : 'Light';
        }
    }

    const savedTheme = localStorage.getItem('app-theme') || 'light';
    applyTheme(savedTheme);

    if (themeToggle) {
        themeToggle.addEventListener('click', function () {
            const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
            const nextTheme = currentTheme === 'dark' ? 'light' : 'dark';
            applyTheme(nextTheme);
        });
    }

    if (sidebarToggle) {
        if (localStorage.getItem('sidebar') === 'collapsed') {
            document.body.classList.add('sidebar-collapsed');
        }

        sidebarToggle.addEventListener('click', function () {
            document.body.classList.toggle('sidebar-collapsed');

            localStorage.setItem(
                'sidebar',
                document.body.classList.contains('sidebar-collapsed') ? 'collapsed' : 'open'
            );
        });
    }
});
</script>

@stack('scripts')

</body>
</html>