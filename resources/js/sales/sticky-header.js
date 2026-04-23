const initializeStickyHeader = () => {
    const pageBody = document.body;
    const header = document.querySelector('.marketing-header');

    if (! pageBody || ! header) {
        return;
    }

    let ticking = false;

    const syncHeaderState = () => {
        ticking = false;
        pageBody.classList.toggle('marketing-body--header-condensed', window.scrollY > 24);
    };

    const requestSync = () => {
        if (ticking) {
            return;
        }

        ticking = true;
        window.requestAnimationFrame(syncHeaderState);
    };

    syncHeaderState();

    window.addEventListener('scroll', requestSync, { passive: true });
    window.addEventListener('resize', requestSync);
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeStickyHeader, { once: true });
} else {
    initializeStickyHeader();
}
