import Alpine from 'alpinejs';
import { animate, inView } from 'motion';

window.Alpine = Alpine;
Alpine.start();

const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

function reveal(el, props = {}, options = {}) {
    animate(el, { opacity: [0, 1], y: [16, 0], ...props }, {
        duration: 0.45,
        easing: 'ease-out',
        ...options,
    });
}

function initGuestAnimations() {
    if (prefersReduced) {
        document.querySelectorAll('[data-guest-animate], [data-guest-stagger-item]').forEach((el) => {
            el.style.opacity = '1';
            el.style.transform = 'none';
        });
        return;
    }

    document.querySelectorAll('[data-guest-animate="hero"]').forEach((el, i) => {
        reveal(el, {}, { delay: i * 0.1, duration: 0.55 });
    });

    inView('[data-guest-animate="fade-up"]', (el) => {
        reveal(el, { y: [20, 0] });
    }, { margin: '-60px 0px -60px 0px', amount: 0.15 });

    inView('[data-guest-stagger]', (container) => {
        container.querySelectorAll('[data-guest-stagger-item]').forEach((child, i) => {
            reveal(child, { y: [20, 0] }, { delay: i * 0.07, duration: 0.4 });
        });
    }, { amount: 0.12 });

    document.querySelectorAll('[data-guest-hover]').forEach((card) => {
        card.addEventListener('mouseenter', () => {
            animate(card, { y: -4 }, { duration: 0.2, easing: 'ease-out' });
        });
        card.addEventListener('mouseleave', () => {
            animate(card, { y: 0 }, { duration: 0.2, easing: 'ease-out' });
        });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initGuestAnimations);
} else {
    initGuestAnimations();
}

const guestNav = document.getElementById('guest-nav');
if (guestNav) {
    const onScroll = () => guestNav.classList.toggle('scrolled', window.scrollY > 20);
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
}
