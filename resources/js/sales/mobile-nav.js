const initializeMobileNav = () => {
    const nav = document.querySelector('[data-mobile-nav]');
    const toggle = document.querySelector('[data-mobile-nav-toggle]');

    if (! nav || ! toggle) {
        return;
    }

    const closeButtons = Array.from(document.querySelectorAll('[data-mobile-nav-close]'));
    const pageBody = document.body;

    const closeNav = () => {
        nav.classList.remove('marketing-mobile-nav--open');
        nav.setAttribute('aria-hidden', 'true');
        toggle.setAttribute('aria-expanded', 'false');
        pageBody.classList.remove('marketing-body--nav-open');
    };

    const openNav = () => {
        nav.classList.add('marketing-mobile-nav--open');
        nav.setAttribute('aria-hidden', 'false');
        toggle.setAttribute('aria-expanded', 'true');
        pageBody.classList.add('marketing-body--nav-open');
    };

    toggle.addEventListener('click', () => {
        if (nav.classList.contains('marketing-mobile-nav--open')) {
            closeNav();
            return;
        }

        openNav();
    });

    closeButtons.forEach((button) => {
        button.addEventListener('click', closeNav);
    });

    window.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeNav();
        }
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth >= 768) {
            closeNav();
        }
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeMobileNav, { once: true });
} else {
    initializeMobileNav();
}
