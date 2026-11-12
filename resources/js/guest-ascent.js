function bindHeaderDaftarDropdown() {
    const root = document.querySelector('.guest-daftar-dropdown');
    if (!root) {
        return;
    }
    const toggle = root.querySelector('.guest-daftar-toggle');
    if (!toggle) {
        return;
    }

    toggle.addEventListener('click', (event) => {
        event.stopPropagation();
        const open = root.classList.toggle('is-open');
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });

    document.addEventListener('click', (event) => {
        if (!root.contains(event.target)) {
            root.classList.remove('is-open');
            toggle.setAttribute('aria-expanded', 'false');
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            root.classList.remove('is-open');
            toggle.setAttribute('aria-expanded', 'false');
        }
    });

    root.querySelectorAll('.guest-daftar-link').forEach((link) => {
        link.addEventListener('click', () => {
            root.classList.remove('is-open');
            toggle.setAttribute('aria-expanded', 'false');
        });
    });
}

function bindOffcanvas() {
    const offcanvases = document.querySelectorAll('.offcanva');
    const triggers = document.querySelectorAll('.offcanvaTragger');
    const closes = document.querySelectorAll('.offcanvaClose');
    const overlays = document.querySelectorAll('.offcanva-overlay');

    const open = () => {
        offcanvases.forEach((el) => {
            el.classList.add('right-0');
            el.classList.remove('-right-full');
        });
        overlays.forEach((el) => {
            el.classList.remove('invisible');
            el.classList.add('visible');
        });
    };

    const close = () => {
        offcanvases.forEach((el) => {
            el.classList.add('-right-full');
            el.classList.remove('right-0');
        });
        overlays.forEach((el) => {
            el.classList.remove('visible');
            el.classList.add('invisible');
        });
    };

    triggers.forEach((item) => item.addEventListener('click', open));
    closes.forEach((item) => item.addEventListener('click', close));
    overlays.forEach((item) => item.addEventListener('click', close));
}

function bindStickyHeader() {
    const header = document.getElementById('header');
    const headerContainer = document.getElementById('header-container');
    const topHeader = document.getElementById('top-header');
    if (!header || !headerContainer) {
        return;
    }

    let prevScrollpos = window.scrollY;
    window.addEventListener('scroll', () => {
        const currentScrollPos = window.scrollY;
        const topOffset = topHeader ? topHeader.clientHeight : 0;

        if (prevScrollpos > currentScrollPos && currentScrollPos > headerContainer.clientHeight) {
            header.style.top = topHeader ? `-${topOffset}px` : '0px';
            header.classList.add('header-pinned');
        } else if (currentScrollPos > 0) {
            header.style.top = `-${headerContainer.clientHeight}px`;
            header.classList.remove('header-pinned');
        } else {
            header.style.top = '0px';
            header.classList.remove('header-pinned');
        }
        prevScrollpos = currentScrollPos;
    }, { passive: true });
}

function bindPortfolioTabs() {
    const targetTabs = document.querySelectorAll('.target-tab');
    const targetCards = document.querySelectorAll('.target-card');
    if (!targetTabs.length || !targetCards.length) {
        return;
    }

    const displayCard = (listAttribute) => {
        targetCards.forEach((item) => {
            const cardAttribute = item.getAttribute('data-target');
            if (cardAttribute === listAttribute) {
                item.classList.remove('absolute', 'invisible', 'translate-y-10', 'opacity-0');
                item.classList.add('relative', 'visible', 'translate-y-0', 'opacity-100');
            } else {
                item.classList.add('absolute', 'invisible', 'translate-y-10', 'opacity-0');
                item.classList.remove('relative', 'visible', 'translate-y-0', 'opacity-100');
            }
        });
    };

    targetTabs.forEach((tab) => {
        tab.addEventListener('click', (e) => {
            const clicked = e.currentTarget;
            targetTabs.forEach((t) => t.classList.remove('active-tab'));
            clicked.classList.add('active-tab');
            displayCard(clicked.getAttribute('data-target'));
        });
    });
}

function bindAccordion() {
    const accordingItems = document.querySelectorAll('.according-item');
    const accordingBtns = document.querySelectorAll('.according-btn');
    if (!accordingBtns.length) {
        return;
    }

    accordingBtns.forEach((btn) => {
        btn.addEventListener('click', (event) => {
            accordingItems.forEach((item) => {
                item.setAttribute('data-open', 'false');
                item.classList.remove('active-accor');
            });
            const parent = event.currentTarget.parentNode;
            parent.setAttribute('data-open', 'true');
            parent.classList.add('active-accor');
        });
    });
}

function bindScrollUp() {
    const scrollUp = document.getElementById('scroll-up');
    if (!scrollUp) {
        return;
    }
    scrollUp.addEventListener('click', () => {
        window.scrollTo({ top: 0, left: 0, behavior: 'smooth' });
    });
}

function initSwipers() {
    if (typeof window.Swiper === 'undefined') {
        return;
    }
    if (document.querySelector('.testimonial-swiper')) {
        new window.Swiper('.testimonial-swiper', {
            autoplay: { delay: 5000 },
            loop: true,
            spaceBetween: 30,
            slidesPerView: 1,
        });
    }
    if (document.querySelector('.service-swiper')) {
        new window.Swiper('.service-swiper', {
            autoplay: { delay: 5000 },
            loop: true,
            spaceBetween: 0,
            centeredSlides: true,
            pagination: {
                el: '.service-pagination',
                clickable: true,
            },
            breakpoints: {
                576: { slidesPerView: 2 },
                1290: { slidesPerView: 3 },
            },
        });
    }
}

function initWow() {
    const existing = document.querySelector('script[data-guest-wow]');
    if (existing) {
        return;
    }
    const base = document.body?.dataset?.ascentAssets || '/ascent/assets';
    const script = document.createElement('script');
    script.src = `${base}/js/wow.min.js`;
    script.dataset.guestWow = '1';
    script.onload = () => {
        if (typeof window.WOW === 'function') {
            new window.WOW().init();
        }
    };
    document.body.appendChild(script);
}

bindHeaderDaftarDropdown();
bindOffcanvas();
bindStickyHeader();
bindPortfolioTabs();
bindAccordion();
bindScrollUp();
initSwipers();
initWow();
