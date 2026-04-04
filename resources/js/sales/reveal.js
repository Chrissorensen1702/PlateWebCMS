const revealElementsOnScroll = () => {
    const revealElements = Array.from(document.querySelectorAll('[data-reveal]'));

    if (! revealElements.length) {
        return;
    }

    document.documentElement.classList.add('js-ready');

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        revealElements.forEach((element) => element.classList.add('is-visible'));
        return;
    }

    if (! ('IntersectionObserver' in window)) {
        revealElements.forEach((element) => element.classList.add('is-visible'));
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (! entry.isIntersecting) {
                return;
            }

            entry.target.classList.add('is-visible');
            observer.unobserve(entry.target);
        });
    }, {
        threshold: 0.18,
        rootMargin: '0px 0px -12% 0px',
    });

    // Wait a frame so the hidden state paints before elements are observed.
    window.requestAnimationFrame(() => {
        window.requestAnimationFrame(() => {
            revealElements.forEach((element) => observer.observe(element));
        });
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', revealElementsOnScroll, { once: true });
} else {
    revealElementsOnScroll();
}
