@auth
    @php
        $user = auth()->user();

        $roleLabel = match ($user->role) {
            'siswa' => 'Siswa',
            'guru_bk' => 'Guru BK',
            'admin' => 'Admin',
            default => 'User',
        };

        $initial = strtoupper(substr($user->name, 0, 1));
    @endphp
@endauth

<header class="navbar">
    <div class="nav-left">
        <button
            id="sidebar-toggle"
            class="menu-toggle"
            type="button"
            aria-label="Toggle sidebar"
            aria-controls="app-sidebar"
            aria-expanded="false"
        >
            <i class="fa-solid fa-bars"></i>
        </button>
    </div>

    <div class="nav-right">
        @auth
            <div class="user-chip">
                <div class="user-avatar">
                    {{ $initial }}
                </div>

                <div class="user-info">
                    <strong>{{ $user->name }}</strong>
                    <span>{{ $roleLabel }}</span>
                </div>
            </div>

            <form action="{{ route('logout') }}" method="POST" class="logout-form">
                @csrf

                <button type="submit" class="logout-btn">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Logout</span>
                </button>
            </form>
        @endauth

        <button
            type="button"
            class="theme-toggle"
            id="theme-toggle"
            data-theme-toggle
            aria-label="Ubah tema"
            aria-pressed="false"
        >
            <span class="icon-moon" aria-hidden="true">
                <svg viewBox="0 0 24 24">
                    <path d="M21 12.8A9 9 0 1 1 11.2 3A7 7 0 0 0 21 12.8z"/>
                </svg>
            </span>

            <span class="icon-sun" aria-hidden="true">
                <svg viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="4"/>
                    <path d="M12 2v2"/>
                    <path d="M12 20v2"/>
                    <path d="M4.93 4.93l1.41 1.41"/>
                    <path d="M17.66 17.66l1.41 1.41"/>
                    <path d="M2 12h2"/>
                    <path d="M20 12h2"/>
                    <path d="M6.34 17.66l-1.41 1.41"/>
                    <path d="M19.07 4.93l-1.41 1.41"/>
                </svg>
            </span>

            <span id="themeToggleText" class="theme-toggle-text">Light</span>
        </button>
    </div>
</header>