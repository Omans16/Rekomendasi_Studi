<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Sistem Rekomendasi — Rekomendasi Kuliah SMKN 1 Glagah</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/landingpage.css') }}">
</head>
<body>

<!-- ═══════════════════════════════════════════
     NAVBAR
════════════════════════════════════════════ -->
<nav class="navbar" id="navbar">
    <div class="nav-inner">
        <a href="#" class="nav-brand">
            <img src="{{ asset('images/Logos.png') }}" alt="Logo" class="nav-logo">
            <div class="nav-brand-text">
                <span class="nav-brand-main"> Sistem Rekomendasi</span>
                <span class="nav-brand-sub">SMKN 1 Glagah</span>
            </div>
        </a>
        <div class="nav-actions">
            <a href="{{ route('login') }}" class="btn-nav-outline">Masuk</a>
            <a href="{{ route('register') }}" class="btn-nav-fill">Daftar Sekarang</a>
        </div>
        <button class="nav-hamburger" id="hamburger" aria-label="Menu">
            <span></span><span></span><span></span>
        </button>
    </div>
</nav>

<!-- Mobile Menu -->
<div class="mobile-menu" id="mobileMenu">
    <a href="#cara-kerja" onclick="closeMobile()">Cara Kerja</a>
    <a href="#fitur" onclick="closeMobile()">Fitur</a>
    <a href="#statistik" onclick="closeMobile()">Statistik</a>
    <a href="#testimoni" onclick="closeMobile()">Testimoni</a>
    <div class="mobile-actions">
        <a href="{{ route('login') }}" class="btn-nav-outline">Masuk</a>
        <a href="{{ route('register') }}" class="btn-nav-fill">Daftar Sekarang</a>
    </div>
</div>



<!-- CTA BOTTOM -->
<section class="section cta-bottom">
    <div class="container">
        <div class="cta-box" data-reveal>
            <div class="cta-glow"></div>
            <div class="cta-badge">Gratis untuk siswa kelas 12 SMKN 1 Glagah</div>
            <h2 class="cta-title">Temukan arah studi lanjutmu<br>dengan rekomendasi<br><em>berbasis data alumni.</em></h2>
            <p class="cta-sub">
                Masukkan data nilai dan jurusan SMK, lalu sistem akan membantu menampilkan potensi studi lanjut serta rekomendasi universitas dan program studi yang relevan.
            </p>
            <div class="cta-actions">
            <a href="{{ route('register') }}" class="btn-primary-lg">
                Mulai Daftar Gratis
            </a>
            <a href="{{ route('login') }}" class="btn-ghost-lg">Sudah punya akun? Masuk</a>
            </div>
        </div>
    </div>
</section>


<!--FOOTER-->
<footer class="footer">
    <div class="container">
        <div class="footer-inner">
            <div class="footer-brand">
                <img src="{{ asset('images/Logos.png') }}" alt="Logo" class="footer-logo">
                <div>
                    <div class="footer-brand-name"> Sistem Rekomendasi</div>
                    <div class="footer-brand-sub">SMKN 1 Glagah Banyuwangi</div>
                </div>
            </div>
            <div class="footer-note">
                Sistem Rekomendasi Perguruan Tinggi berbasis Machine Learning<br>
                untuk siswa kelas 12 SMKN 1 Glagah Banyuwangi.
            </div>
            <div class="footer-copy">
                © {{ date('Y') }}  Sistem Rekomendasi · SMKN 1 Glagah · Banyuwangi
            </div>
        </div>
    </div>
</footer>

<script>
/*  Navbar scroll  */
const navbar = document.getElementById('navbar');
window.addEventListener('scroll', () => {
    navbar.classList.toggle('scrolled', window.scrollY > 40);
});

/*  Mobile menu  */
const hamburger = document.getElementById('hamburger');
const mobileMenu = document.getElementById('mobileMenu');
hamburger.addEventListener('click', () => {
    hamburger.classList.toggle('open');
    mobileMenu.classList.toggle('open');
});
function closeMobile() {
    hamburger.classList.remove('open');
    mobileMenu.classList.remove('open');
}

/*  Smooth scroll  */
document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', e => {
        const target = document.querySelector(a.getAttribute('href'));
        if (target) { e.preventDefault(); target.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
    });
});

/*  Scroll reveal  */
const reveals = document.querySelectorAll('[data-reveal]');
const revealObs = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const delay = entry.target.dataset.delay || 0;
            setTimeout(() => entry.target.classList.add('revealed'), parseInt(delay));
            revealObs.unobserve(entry.target);
        }
    });
}, { threshold: 0.12 });
reveals.forEach(el => revealObs.observe(el));

/* Counter animation  */
const counters = document.querySelectorAll('[data-count]');
const counterObs = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (!entry.isIntersecting) return;
        const el = entry.target;
        const target = parseInt(el.dataset.count);
        const duration = 1800;
        const start = performance.now();
        function tick(now) {
            const elapsed = now - start;
            const progress = Math.min(elapsed / duration, 1);
            const ease = 1 - Math.pow(1 - progress, 3);
            el.textContent = Math.round(ease * target);
            if (progress < 1) requestAnimationFrame(tick);
        }
        requestAnimationFrame(tick);
        counterObs.unobserve(el);
    });
}, { threshold: 0.5 });
counters.forEach(el => counterObs.observe(el));

/*  Gear canvas  */
(function() {
    const canvas = document.getElementById('gearCanvas');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    let W, H, particles = [], animId;
    const YELLOW = 'rgba(245,200,0,';

    function resize() {
        W = canvas.width  = canvas.offsetWidth;
        H = canvas.height = canvas.offsetHeight;
    }
    resize();
    window.addEventListener('resize', resize);

    function Particle() {
        this.reset();
    }
    Particle.prototype.reset = function() {
        this.x = Math.random() * W;
        this.y = Math.random() * H;
        this.r = Math.random() * 1.2 + 0.3;
        this.alpha = Math.random() * 0.35 + 0.05;
        this.vx = (Math.random() - 0.5) * 0.25;
        this.vy = (Math.random() - 0.5) * 0.25;
    };
    Particle.prototype.update = function() {
        this.x += this.vx;
        this.y += this.vy;
        if (this.x < 0 || this.x > W || this.y < 0 || this.y > H) this.reset();
    };

    for (let i = 0; i < 110; i++) particles.push(new Particle());

    function drawRing(cx, cy, r, alpha, rotation) {
        ctx.save();
        ctx.translate(cx, cy);
        ctx.rotate(rotation);
        ctx.beginPath();
        ctx.arc(0, 0, r, 0, Math.PI * 2);
        ctx.strokeStyle = YELLOW + alpha + ')';
        ctx.lineWidth = 1;
        ctx.stroke();
        const teeth = Math.round(r / 12);
        for (let i = 0; i < teeth; i++) {
            const angle = (i / teeth) * Math.PI * 2;
            const x1 = Math.cos(angle) * (r - 4);
            const y1 = Math.sin(angle) * (r - 4);
            const x2 = Math.cos(angle) * (r + 5);
            const y2 = Math.sin(angle) * (r + 5);
            ctx.beginPath();
            ctx.moveTo(x1, y1);
            ctx.lineTo(x2, y2);
            ctx.strokeStyle = YELLOW + (alpha * 0.6) + ')';
            ctx.lineWidth = 2.5;
            ctx.stroke();
        }
        ctx.restore();
    }

    let rot1 = 0, rot2 = 0, rot3 = 0;
    function draw() {
        ctx.clearRect(0, 0, W, H);
        rot1 += 0.001; rot2 -= 0.0007; rot3 += 0.0005;
        drawRing(W * 0.82, H * 0.22, 130, 0.06, rot1);
        drawRing(W * 0.82, H * 0.22,  72, 0.04, rot2);
        drawRing(W * 0.12, H * 0.75, 100, 0.05, rot2);
        drawRing(W * 0.12, H * 0.75,  52, 0.03, rot3);
        drawRing(W * 0.5,  H * 1.05,  180, 0.03, rot3);

        particles.forEach(p => {
            p.update();
            ctx.beginPath();
            ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
            ctx.fillStyle = YELLOW + p.alpha + ')';
            ctx.fill();
        });
        animId = requestAnimationFrame(draw);
    }
    draw();
})();
</script>
</body>
</html>