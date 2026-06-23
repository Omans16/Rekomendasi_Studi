<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SEMEGAH - Sistem Rekomendasi</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet"
    >

    {{-- Theme loader agar tidak flicker saat halaman dibuka --}}
    <script>
        (function () {
            const savedTheme = localStorage.getItem('auth-theme') || 'dark';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>

    {{-- Styles --}}
    <link rel="stylesheet" href="{{ asset('css/landingpage.css') }}">

    {{-- Scripts --}}
    <script defer src="{{ asset('js/landingpage.js') }}"></script>
</head>

<body>
    {{-- NAVBAR --}}
    <nav class="navbar" id="navbar">
        <div class="nav-inner">
            <a href="{{ url('/') }}" class="nav-brand">
                <img src="{{ asset('images/Logos.png') }}" alt="Logo SMKN 1 Glagah" class="nav-logo">

                <div class="nav-brand-text">
                    <span class="nav-brand-main">Sistem Rekomendasi</span>
                    <span class="nav-brand-sub">SMKN 1 Glagah</span>
                </div>
            </a>

            <div class="nav-actions">
                <button type="button" class="theme-toggle" data-theme-toggle aria-label="Ubah tema">
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

                    <span class="theme-toggle-text">Dark</span>
                </button>

                <a href="{{ route('login') }}" class="btn-nav-outline">Masuk</a>
                <a href="{{ route('register') }}" class="btn-nav-fill">Daftar Sekarang</a>
            </div>

            <button
                type="button"
                class="nav-hamburger"
                id="hamburger"
                aria-label="Menu"
                aria-expanded="false"
            >
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </nav>

    {{-- MOBILE MENU --}}
    <div class="mobile-menu" id="mobileMenu">
        <button type="button" class="theme-toggle mobile-theme-toggle" data-theme-toggle aria-label="Ubah tema">
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

            <span class="theme-toggle-text">Dark</span>
        </button>

        <a href="{{ route('login') }}" class="btn-nav-outline">Masuk</a>
        <a href="{{ route('register') }}" class="btn-nav-fill">Daftar Sekarang</a>
    </div>

    {{-- CTA SECTION --}}
    <main>
        <section class="section cta-bottom">
            <div class="container">
                <div class="cta-box">
                    <div class="cta-glow"></div>

                    <div class="cta-badge">
                        Gratis untuk siswa kelas 12 SMKN 1 Glagah
                    </div>

                    <h2 class="cta-title">
                        Temukan arah studi lanjutmu<br>
                        dengan rekomendasi<br>
                        <em>berbasis data alumni.</em>
                    </h2>

                    <p class="cta-sub">
                        Masukkan data nilai dan jurusan SMK, lalu sistem akan membantu menampilkan potensi studi lanjut serta rekomendasi universitas dan program studi yang relevan.
                    </p>

                    <div class="cta-actions">
                        <a href="{{ route('register') }}" class="btn-primary-lg">
                            Mulai Daftar Gratis
                        </a>

                        <a href="{{ route('login') }}" class="btn-ghost-lg">
                            Sudah punya akun? Masuk
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    {{-- FOOTER --}}
    <footer class="footer">
        <div class="container">
            <div class="footer-inner">
                <div class="footer-brand">
                    <img src="{{ asset('images/Logos.png') }}" alt="Logo SMKN 1 Glagah" class="footer-logo">

                    <div>
                        <div class="footer-brand-name">Sistem Rekomendasi</div>
                        <div class="footer-brand-sub">SMKN 1 Glagah Banyuwangi</div>
                    </div>
                </div>

                <div class="footer-note">
                    Sistem Rekomendasi Perguruan Tinggi berbasis Machine Learning<br>
                    untuk siswa kelas 12 SMKN 1 Glagah Banyuwangi.
                </div>

                <div class="footer-copy">
                    © {{ date('Y') }} Sistem Rekomendasi · SMKN 1 Glagah · Banyuwangi
                </div>
            </div>
        </div>
    </footer>
</body>
</html>