(() => {
    'use strict';

    const BREAKPOINT_TABLET = 1000;

    const STORAGE_KEYS = {
        THEME: 'app-theme',
        SIDEBAR: 'app-sidebar',
    };

    const CLASS_NAMES = {
        DARK: 'dark',
        SIDEBAR_OPEN: 'sidebar-open',
        SIDEBAR_COLLAPSED: 'sidebar-collapsed',
    };

    const SELECTORS = {
        SIDEBAR: '.app-layout .sidebar',
        SIDEBAR_TOGGLE: '#sidebar-toggle',
        SIDEBAR_LINK: '.app-layout .sidebar-menu a',
        THEME_TOGGLE: '#theme-toggle, [data-theme-toggle]',
        THEME_TEXT: '#themeToggleText, .theme-toggle-text',
    };

    const root = document.documentElement;
    const body = document.body;

    const getElement = (selector) => document.querySelector(selector);
    const getElements = (selector) => document.querySelectorAll(selector);

    const isTabletDown = () => window.innerWidth <= BREAKPOINT_TABLET;

    const getStorage = (key, fallback = null) => {
        try {
            return localStorage.getItem(key) ?? fallback;
        } catch {
            return fallback;
        }
    };

    const setStorage = (key, value) => {
        try {
            localStorage.setItem(key, value);
        } catch {
            return null;
        }
    };

    const setSidebarExpanded = (isExpanded) => {
        const toggleButton = getElement(SELECTORS.SIDEBAR_TOGGLE);

        if (!toggleButton) return;

        toggleButton.setAttribute('aria-expanded', isExpanded ? 'true' : 'false');
    };

    const closeMobileSidebar = () => {
        body.classList.remove(CLASS_NAMES.SIDEBAR_OPEN);
        setSidebarExpanded(false);
    };

    const toggleMobileSidebar = () => {
        const isOpen = body.classList.toggle(CLASS_NAMES.SIDEBAR_OPEN);

        setSidebarExpanded(isOpen);
    };

    const toggleDesktopSidebar = () => {
        const isCollapsed = body.classList.toggle(CLASS_NAMES.SIDEBAR_COLLAPSED);

        setStorage(STORAGE_KEYS.SIDEBAR, isCollapsed ? 'collapsed' : 'open');
        setSidebarExpanded(!isCollapsed);
    };

    const syncSidebarMode = () => {
        if (isTabletDown()) {
            body.classList.remove(CLASS_NAMES.SIDEBAR_COLLAPSED);
            closeMobileSidebar();
            return;
        }

        const savedSidebar = getStorage(STORAGE_KEYS.SIDEBAR, 'open');
        const isCollapsed = savedSidebar === 'collapsed';

        body.classList.toggle(CLASS_NAMES.SIDEBAR_COLLAPSED, isCollapsed);
        body.classList.remove(CLASS_NAMES.SIDEBAR_OPEN);

        setSidebarExpanded(!isCollapsed);
    };

    const handleSidebarToggle = (event) => {
        event.stopPropagation();

        if (isTabletDown()) {
            toggleMobileSidebar();
            return;
        }

        toggleDesktopSidebar();
    };

    const handleOutsideClick = (event) => {
        if (!isTabletDown()) return;
        if (!body.classList.contains(CLASS_NAMES.SIDEBAR_OPEN)) return;

        const sidebar = getElement(SELECTORS.SIDEBAR);
        const sidebarToggle = getElement(SELECTORS.SIDEBAR_TOGGLE);

        if (!sidebar || !sidebarToggle) return;

        const clickInsideSidebar = sidebar.contains(event.target);
        const clickOnToggle = sidebarToggle.contains(event.target);

        if (!clickInsideSidebar && !clickOnToggle) {
            closeMobileSidebar();
        }
    };

    const handleMenuClick = () => {
        if (isTabletDown()) {
            closeMobileSidebar();
        }
    };

    const handleEscape = (event) => {
        if (event.key === 'Escape' && isTabletDown()) {
            closeMobileSidebar();
        }
    };

    const updateThemeText = (theme) => {
        getElements(SELECTORS.THEME_TEXT).forEach((text) => {
            text.textContent = theme === 'dark' ? 'Dark' : 'Light';
        });
    };

    const updateThemeButtonState = (theme) => {
        getElements(SELECTORS.THEME_TOGGLE).forEach((button) => {
            button.setAttribute('aria-pressed', theme === 'dark' ? 'true' : 'false');
        });
    };

    const applyTheme = (theme) => {
        const selectedTheme = theme === 'dark' ? 'dark' : 'light';

        root.setAttribute('data-theme', selectedTheme);
        body.classList.toggle(CLASS_NAMES.DARK, selectedTheme === 'dark');

        setStorage(STORAGE_KEYS.THEME, selectedTheme);
        updateThemeText(selectedTheme);
        updateThemeButtonState(selectedTheme);
    };

    const toggleTheme = () => {
        const currentTheme = root.getAttribute('data-theme') || 'light';
        const nextTheme = currentTheme === 'dark' ? 'light' : 'dark';

        applyTheme(nextTheme);
    };

    const debounce = (callback, delay = 150) => {
        let timer = null;

        return (...args) => {
            clearTimeout(timer);

            timer = setTimeout(() => {
                callback(...args);
            }, delay);
        };
    };

    const bindEvents = () => {
        const sidebarToggle = getElement(SELECTORS.SIDEBAR_TOGGLE);

        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', handleSidebarToggle);
        }

        getElements(SELECTORS.SIDEBAR_LINK).forEach((link) => {
            link.addEventListener('click', handleMenuClick);
        });

        getElements(SELECTORS.THEME_TOGGLE).forEach((button) => {
            button.addEventListener('click', toggleTheme);
        });

        document.addEventListener('click', handleOutsideClick);
        document.addEventListener('keydown', handleEscape);
        window.addEventListener('resize', debounce(syncSidebarMode));
    };

    const initTheme = () => {
        const savedTheme = getStorage(STORAGE_KEYS.THEME, 'light');

        applyTheme(savedTheme);
    };

    const init = () => {
        initTheme();
        syncSidebarMode();
        bindEvents();
    };

    document.addEventListener('DOMContentLoaded', init);
})();