<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Semegah Sistem Rekomendasi</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@700;800;900&display=swap" rel="stylesheet">

    <script>
        (function () {
            const savedTheme = localStorage.getItem('auth-theme') || 'dark';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>

    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body>

<button type="button" class="theme-toggle" id="themeToggle" aria-label="Ubah tema">
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
    <span id="themeToggleText">Dark</span>
</button>





<div class="auth-wrap">

    {{-- ── LEFT PANEL ── --}}
    <div class="panel-left">
        <div class="gear-bg">
            <div class="gear-ring gear-ring-1"><div class="gear-dot"></div></div>
            <div class="gear-ring gear-ring-2"></div>
        </div>
        <div class="corner-tl"></div>
        <div class="corner-br"></div>

        <div class="logo-wrap">
            <div class="logo-halo">
                <img src="{{ asset('images/Logos.png') }}" alt="Logo SMKN 1 Glagah Banyuwangi">
            </div>
            <div class="school-name">
                <div class="smk">SEMEGAH</div>
                <div class="school-divider"></div>
                <div class="full">SMK Negeri 1 Glagah Banyuwangi</div>
            </div>
        </div>

        <div class="left-tagline">
            <h2>Sistem Rekomendasi<br><span>Perguruan Tinggi</span></h2>
            <p>Temukan universitas dan program studi terbaik berdasarkan data nilai dan alumni SMKN 1 Glagah.</p>
        </div>

        <div class="stats-row">
            <div class="stat-item">
                <span class="stat-num">1500+</span>
                <span class="stat-label">Data Alumni</span>
            </div>
            <div class="stat-item">
                <span class="stat-num">30+</span>
                <span class="stat-label">Universitas</span>
            </div>
            <div class="stat-item">
                <span class="stat-num">90%</span>
                <span class="stat-label">Akurasi AI</span>
            </div>
        </div>
    </div>

    {{-- ── RIGHT PANEL ── --}}
    <div class="panel-right">
        <div class="form-box">

            <div class="form-eyebrow">Sistem Rekomendasi · SMKN 1 Glagah</div>
            <div class="form-title">Masuk ke akun</div>
            <div class="form-sub">Gunakan NISN dan password yang di daftarkan.</div>

            @if(session('success'))
                <div class="alert alert-success">
                    <div class="alert-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">
                    <div class="alert-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    </div>
                    <div>{{ session('error') }}</div>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <div class="alert-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    </div>
                    <ul class="alert-list">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('login.proses') }}" method="POST">
                @csrf

                <div class="field">
                    <label for="nisn">NISN</label>
                    <div class="field-wrap">
                        <svg viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                        <input type="text" id="nisn" name="nisn"
                               value="{{ old('nisn') }}"
                               placeholder="Masukkan NISN"
                               autocomplete="username">
                    </div>
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <div class="field-wrap">
                        <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        <input type="password" id="password" name="password"
                               placeholder="Masukkan password"
                               autocomplete="current-password">
                        <button type="button" class="toggle-pw" onclick="togglePw('password', this)" aria-label="Lihat password">
                            <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>

                <div class="remember-row">
                    <label class="remember-label">
                        <input type="checkbox" name="remember" value="1"> Ingat saya
                    </label>
                </div>

                <button type="submit" class="btn-submit">
                    <svg width="16" height="16" viewBox="0 0 24 24"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                    Masuk
                </button>
            </form>

            <div class="divider">atau</div>

            <div class="register-cta">
                Belum punya akun? <a href="{{ route('register') }}">Daftar</a>
            </div>

        </div>
    </div>

</div>

<script>
function setAuthTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('auth-theme', theme);

    const text = document.getElementById('themeToggleText');
    if (text) {
        text.textContent = theme === 'light' ? 'Light' : 'Dark';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const currentTheme = document.documentElement.getAttribute('data-theme') || 'dark';
    setAuthTheme(currentTheme);

    const themeToggle = document.getElementById('themeToggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', function () {
            const activeTheme = document.documentElement.getAttribute('data-theme') || 'dark';
            const nextTheme = activeTheme === 'light' ? 'dark' : 'light';
            setAuthTheme(nextTheme);
        });
    }

    document.querySelectorAll('.modal-overlay').forEach(function (overlay) {
        overlay.addEventListener('click', function (e) {
            if (e.target === this) {
                this.classList.remove('show');
            }
        });
    });
});

function closeModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.remove('show');
    }
}

function togglePw(id, btn) {
    const input = document.getElementById(id);
    if (!input || !btn) return;

    const isHidden = input.type === 'password';
    input.type = isHidden ? 'text' : 'password';

    btn.innerHTML = isHidden
        ? `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>`
        : `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>`;
}

function checkStrength(val) {
    const el = document.getElementById('pwStrength');
    const label = document.getElementById('pwLabel');
    const bars = ['bar1', 'bar2', 'bar3'].map(id => document.getElementById(id));

    if (!el || !label || bars.some(bar => !bar)) return;

    if (!val) {
        el.classList.remove('visible');
        return;
    }

    el.classList.add('visible');

    let score = 0;
    if (val.length >= 8) score++;
    if (/[A-Z]/.test(val) || /[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val) || val.length >= 12) score++;

    const cls = ['active-weak', 'active-medium', 'active-strong'];
    const lbls = ['Terlalu lemah', 'Cukup kuat', 'Kuat'];
    const lcls = ['weak', 'medium', 'strong'];

    bars.forEach(function (bar, index) {
        bar.className = 'pw-bar' + (index < score ? ' ' + cls[score - 1] : '');
    });

    label.textContent = lbls[score - 1] || 'Terlalu pendek';
    label.className = 'pw-label ' + (lcls[score - 1] || 'weak');
}

function checkConfirm(val) {
    const pw = document.getElementById('password');
    const hint = document.getElementById('confirmHint');

    if (!pw || !hint) return;

    if (!val) {
        hint.textContent = '';
        hint.className = 'field-hint';
        return;
    }

    if (val === pw.value) {
        hint.textContent = 'Password cocok';
        hint.className = 'field-hint is-success';
    } else {
        hint.textContent = 'Password belum cocok';
        hint.className = 'field-hint is-error';
    }
}
</script>

</body>
</html>