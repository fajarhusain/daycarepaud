import * as Turbo from '@hotwired/turbo';

const OPEN_GROUPS_KEY = 'sidebarOpenGroups';
const NAV_SCROLL_KEY = 'sidebarNavScroll';

function persistSidebarState() {
    const nav = document.getElementById('sidebar-nav-scroll');
    if (nav) {
        sessionStorage.setItem(NAV_SCROLL_KEY, String(nav.scrollTop));
    }

    const openGroups = [];
    document.querySelectorAll('[data-sidebar-group]').forEach((el) => {
        const data = window.Alpine?.$data(el);
        if (data?.open) {
            const name = el.getAttribute('data-sidebar-group');
            if (name) {
                openGroups.push(name);
            }
        }
    });

    sessionStorage.setItem(OPEN_GROUPS_KEY, JSON.stringify(openGroups));
}

function restoreSidebarState() {
    const nav = document.getElementById('sidebar-nav-scroll');
    const scroll = sessionStorage.getItem(NAV_SCROLL_KEY);
    if (nav && scroll !== null && scroll !== '') {
        nav.scrollTop = Number.parseInt(scroll, 10) || 0;
    }

    let openGroups = [];
    try {
        openGroups = JSON.parse(sessionStorage.getItem(OPEN_GROUPS_KEY) || '[]');
    } catch {
        openGroups = [];
    }

    if (!Array.isArray(openGroups) || openGroups.length === 0) {
        return;
    }

    document.querySelectorAll('[data-sidebar-group]').forEach((el) => {
        const name = el.getAttribute('data-sidebar-group');
        if (!name || !openGroups.includes(name)) {
            return;
        }
        const data = window.Alpine?.$data(el);
        if (data && typeof data.open === 'boolean') {
            data.open = true;
        }
    });
}

function bootAlpineAfterNavigate() {
    if (!window.Alpine) {
        return;
    }

    if (!window.__alpineStarted) {
        window.Alpine.start();
        window.__alpineStarted = true;
    } else {
        window.Alpine.initTree(document.body);
    }

    requestAnimationFrame(() => {
        restoreSidebarState();
        document.querySelector('main.flex-1')?.scrollTo(0, 0);
    });
}

document.addEventListener('turbo:before-cache', persistSidebarState);
document.addEventListener('turbo:load', bootAlpineAfterNavigate);

// ponytail: Turbo must be referenced so the module side-effects (Drive) stay linked.
void Turbo;
