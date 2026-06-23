document.addEventListener('DOMContentLoaded', () => {
    const root = document.documentElement;
    const hamburger = document.getElementById('hamburger');
    const mobileMenu = document.getElementById('mobileMenu');
    const themeButtons = document.querySelectorAll('[data-theme-toggle]');

    function setLandingTheme(theme) {
        root.setAttribute('data-theme', theme);
        localStorage.setItem('auth-theme', theme);

        document.querySelectorAll('.theme-toggle-text').forEach((text) => {
            text.textContent = theme === 'light' ? 'Light' : 'Dark';
        });

        themeButtons.forEach((button) => {
            button.setAttribute('aria-pressed', theme === 'light' ? 'true' : 'false');
        });
    }

    function closeMobileMenu() {
        if (!hamburger || !mobileMenu) return;

        hamburger.classList.remove('open');
        hamburger.setAttribute('aria-expanded', 'false');
        mobileMenu.classList.remove('open');
    }

    const currentTheme = root.getAttribute('data-theme') || localStorage.getItem('auth-theme') || 'dark';
    setLandingTheme(currentTheme);

    themeButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const activeTheme = root.getAttribute('data-theme') || 'dark';
            const nextTheme = activeTheme === 'light' ? 'dark' : 'light';

            setLandingTheme(nextTheme);
        });
    });

    if (hamburger && mobileMenu) {
        hamburger.addEventListener('click', () => {
            const isOpen = mobileMenu.classList.toggle('open');

            hamburger.classList.toggle('open', isOpen);
            hamburger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });

        document.addEventListener('click', (event) => {
            const isClickInsideMenu = mobileMenu.contains(event.target);
            const isClickOnHamburger = hamburger.contains(event.target);

            if (!isClickInsideMenu && !isClickOnHamburger) {
                closeMobileMenu();
            }
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth > 768) {
                closeMobileMenu();
            }
        });
    }
});