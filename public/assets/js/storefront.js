(() => {
    'use strict';

    const header = document.querySelector('[data-site-header]');
    const menuToggle = document.querySelector('[data-menu-toggle]');
    const navigation = document.querySelector('[data-navigation]');
    const searchToggle = document.querySelector('[data-search-toggle]');
    const searchPanel = document.querySelector('[data-search-panel]');
    const bottomSearch = document.querySelector('[data-bottom-search]');
    const bottomCart = document.querySelector('[data-bottom-cart]');

    if (!header) {
        return;
    }

    const setMenuOpen = (open) => {
        if (!menuToggle || !navigation) {
            return;
        }

        menuToggle.setAttribute('aria-expanded', String(open));
        menuToggle.querySelector('.sr-only').textContent = open ? 'Close navigation' : 'Open navigation';
        navigation.classList.toggle('is-open', open);
        document.body.classList.toggle('menu-is-open', open);
    };

    const setSearchOpen = (open) => {
        if (!searchToggle || !searchPanel) {
            return;
        }

        searchToggle.setAttribute('aria-expanded', String(open));
        searchPanel.hidden = !open;

        if (open) {
            setMenuOpen(false);
            searchPanel.querySelector('input')?.focus();
        }
    };

    menuToggle?.addEventListener('click', () => {
        const open = menuToggle.getAttribute('aria-expanded') !== 'true';
        setSearchOpen(false);
        setMenuOpen(open);
    });

    navigation?.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => setMenuOpen(false));
    });

    searchToggle?.addEventListener('click', () => {
        setSearchOpen(searchToggle.getAttribute('aria-expanded') !== 'true');
    });

    bottomSearch?.addEventListener('click', () => {
        setSearchOpen(true);
        header.scrollIntoView({behavior: 'smooth', block: 'start'});
    });

    bottomCart?.addEventListener('click', () => {
        document.querySelector('[data-cart-open]')?.click();
    });

    document.addEventListener('click', (event) => {
        if (
            searchPanel
            && searchToggle
            && !searchPanel.hidden
            && event.target instanceof Node
            && !searchPanel.contains(event.target)
            && !searchToggle.contains(event.target)
        ) {
            setSearchOpen(false);
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            setMenuOpen(false);
            setSearchOpen(false);
        }
    });

    const updateHeader = () => {
        header.classList.toggle('is-scrolled', window.scrollY > 12);
    };

    updateHeader();
    window.addEventListener('scroll', updateHeader, {passive: true});

    const desktopNavigation = window.matchMedia('(min-width: 56.01rem)');
    const handleNavigationViewport = (event) => {
        if (event.matches) {
            setMenuOpen(false);
        }
    };

    desktopNavigation.addEventListener('change', handleNavigationViewport);
})();
