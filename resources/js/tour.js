import { driver } from 'driver.js';
import 'driver.js/dist/driver.css';

const REPLAY_STORAGE_KEY = 'sipp_tour_replay';
const MODAL_STATE_KEYS = [
    'showCreateModal',
    'showEditModal',
    'showDeleteModal',
    'showDetailModal',
    'showInputModal',
    'showHistoryModal',
    'showAddSiswaModal',
    'showGenerateModal',
    'showApproveModal',
    'showRejectModal',
    'showBayarModal',
    'showHariHadirModal',
    'showDiskonModal',
];

let activeDriver = null;
let modalTourTimer = null;
let tourSectionProxy = null;
let tourSectionProxyHeader = null;
let tourSectionProxyScrollParent = null;
let tourSectionProxyResizeObserver = null;
let tourGeneration = 0;
let suppressMarkCompleteOnDestroy = false;
const MODAL_WAIT_INTERVAL_MS = 50;
const MODAL_WAIT_TIMEOUT_MS = 2000;
const ADVANCE_POLL_MS = 200;
const ADVANCE_STEP_DELAY_MS = 350;
const STEP_ELEMENT_WAIT_MS = 15000;

let advanceWatcherCleanup = null;
let suppressCloseModalsOnDestroy = false;

const MODAL_SECTION_SELECTOR = '[data-tour^="modal-create-section"], [data-tour^="modal-edit-section"]';
const MODAL_SCROLL_SELECTOR = '.modal-body, .modal-scroll';

function getHttpClient() {
    if (window.axios) {
        return Promise.resolve(window.axios);
    }

    return import('axios').then((module) => {
        window.axios = module.default;
        window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        const csrfToken = document.head.querySelector('meta[name="csrf-token"]');
        if (csrfToken) {
            window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.content;
        }
        return window.axios;
    });
}

function sleep(ms) {
    return new Promise((resolve) => setTimeout(resolve, ms));
}

function waitForAnimationFrames(count = 2) {
    return new Promise((resolve) => {
        let remaining = count;
        const tick = () => {
            remaining -= 1;
            if (remaining <= 0) {
                resolve();
                return;
            }
            requestAnimationFrame(tick);
        };
        requestAnimationFrame(tick);
    });
}

function isOverlayVisible(element) {
    if (!isElementVisible(element)) {
        return false;
    }

    const style = window.getComputedStyle(element);
    return parseFloat(style.opacity || '1') > 0;
}

function getModalStepSelectors(type) {
    const ctx = getContext();
    const steps = ctx?.modalSteps?.[type] ?? [];

    return steps
        .map((step) => step?.element)
        .filter((selector) => typeof selector === 'string' && selector.length > 0);
}

function isProgressiveModalTour(steps) {
    return Array.isArray(steps) && steps.some((step) => step?.advanceWhen);
}

function hasVisibleModalOverlay() {
    return [...document.querySelectorAll('.modal-overlay')].some(isOverlayVisible);
}

function areModalTourTargetsReady(type, steps = null) {
    const ctx = getContext();
    const modalSteps = steps ?? ctx?.modalSteps?.[type] ?? [];

    if (isProgressiveModalTour(modalSteps)) {
        const firstStep = modalSteps.find((step) => step?.element);
        if (!firstStep?.element) {
            return false;
        }

        const element = document.querySelector(firstStep.element);
        return element && isElementVisible(element);
    }

    const selectors = getModalStepSelectors(type);
    if (!selectors.length) {
        return false;
    }

    return selectors.every((selector) => {
        const element = document.querySelector(selector);
        return element && isElementVisible(element);
    });
}

async function waitForModalReady(type, generation) {
    const ctx = getContext();
    const modalSteps = ctx?.modalSteps?.[type] ?? [];
    const deadline = Date.now() + MODAL_WAIT_TIMEOUT_MS;

    while (Date.now() < deadline) {
        if (generation !== tourGeneration) {
            return false;
        }

        if (hasVisibleModalOverlay() && areModalTourTargetsReady(type, modalSteps)) {
            await waitForAnimationFrames(2);
            return generation === tourGeneration;
        }

        await sleep(MODAL_WAIT_INTERVAL_MS);
    }

    return false;
}

function clearAdvanceWatcher() {
    if (advanceWatcherCleanup) {
        advanceWatcherCleanup();
        advanceWatcherCleanup = null;
    }
}

function getSectionRoot(selector) {
    if (!selector) {
        return null;
    }

    return document.querySelector(selector);
}

function getSectionFields(root) {
    if (!root) {
        return [];
    }

    return [...root.querySelectorAll('select, textarea, input:not([type=hidden]):not([type=file])')];
}

function isSectionAdvanceReady(selector, mode = 'section-input') {
    if (mode === 'section-optional') {
        return false;
    }

    const root = getSectionRoot(selector);
    if (!root || !isElementVisible(root)) {
        return false;
    }

    const fields = getSectionFields(root);
    if (!fields.length) {
        return false;
    }

    return fields.every((field) => {
        const value = (field.value || '').trim();

        if (field.tagName === 'SELECT') {
            return value !== '' && field.selectedIndex > 0;
        }

        return value.length > 0;
    });
}

function watchSectionAdvance(selector, mode, onReady) {
    clearAdvanceWatcher();

    if (mode === 'section-optional') {
        return;
    }

    let cancelled = false;

    const cleanup = () => {
        cancelled = true;
        clearInterval(interval);
        document.removeEventListener('change', onInput, true);
        document.removeEventListener('input', onInput, true);
        advanceWatcherCleanup = null;
    };

    const check = () => {
        if (cancelled) {
            return;
        }

        if (isSectionAdvanceReady(selector, mode)) {
            cleanup();
            onReady();
        }
    };

    const onInput = () => check();
    const interval = setInterval(check, ADVANCE_POLL_MS);
    document.addEventListener('change', onInput, true);
    document.addEventListener('input', onInput, true);
    advanceWatcherCleanup = cleanup;
    check();

    return cleanup;
}

async function waitForStepElement(selector, interactive = false, generation = tourGeneration) {
    const deadline = Date.now() + STEP_ELEMENT_WAIT_MS;

    while (Date.now() < deadline) {
        if (generation !== tourGeneration) {
            return null;
        }

        const element = findVisibleTourElement(selector);
        if (element && isElementVisible(element)) {
            if (interactive || !isModalSectionSelector(selector)) {
                return element;
            }

            return resolveModalSectionElement(selector);
        }

        await sleep(ADVANCE_POLL_MS);
    }

    return null;
}

function getContext() {
    return window.__tourContext ?? null;
}

function getReplaySession() {
    try {
        const raw = sessionStorage.getItem(REPLAY_STORAGE_KEY);
        return raw ? JSON.parse(raw) : null;
    } catch {
        return null;
    }
}

function startReplaySession(hub) {
    if (!hub) {
        return;
    }

    try {
        sessionStorage.setItem(REPLAY_STORAGE_KEY, JSON.stringify({
            hub,
            startedAt: new Date().toISOString(),
            completed: [],
        }));
    } catch (error) {
        console.warn('Tour replay session could not be saved.', error);
    }
}

function persistReplaySession(session) {
    try {
        sessionStorage.setItem(REPLAY_STORAGE_KEY, JSON.stringify(session));
    } catch (error) {
        console.warn('Tour replay session could not be updated.', error);
    }
}

function isSessionTourComplete(completeKey) {
    const session = getReplaySession();
    return session?.completed?.includes(completeKey) ?? false;
}

function markSessionTourComplete(completeKey) {
    const session = getReplaySession();
    if (!session) {
        return;
    }

    if (!Array.isArray(session.completed)) {
        session.completed = [];
    }

    if (!session.completed.includes(completeKey)) {
        session.completed.push(completeKey);
        persistReplaySession(session);
    }
}

function isReplayActive() {
    const session = getReplaySession();
    const ctx = getContext();

    if (!session?.hub || !ctx?.hubRoute) {
        return false;
    }

    return session.hub === ctx.hubRoute;
}

function isTourComplete(completeKey) {
    const ctx = getContext();
    if (ctx?.completed?.[completeKey]) {
        return true;
    }

    if (isReplayActive() && isSessionTourComplete(completeKey)) {
        return true;
    }

    return false;
}

function shouldRunTour(completeKey) {
    return !isTourComplete(completeKey);
}

function modalCompleteKey(type) {
    const ctx = getContext();
    const hubRoute = ctx?.hubRoute ?? ctx?.route;
    if (!hubRoute || !type) {
        return null;
    }

    return modalRouteKey(hubRoute, type);
}

function resetAlpineModalState() {
    if (!window.Alpine?.$data) {
        return;
    }

    document.querySelectorAll('.modal-overlay').forEach((overlay) => {
        if (!isOverlayVisible(overlay)) {
            return;
        }

        const alpineRoot = overlay.closest('[x-data]');
        if (!alpineRoot) {
            return;
        }

        const data = window.Alpine.$data(alpineRoot);
        MODAL_STATE_KEYS.forEach((key) => {
            if (key in data) {
                data[key] = false;
            }
        });
    });
}

function closeAllModals() {
    window.dispatchEvent(new CustomEvent('tour:close-modals', { bubbles: true }));
    resetAlpineModalState();
}

function findVisibleTourTrigger(type) {
    return [...document.querySelectorAll(`[data-tour-open-modal="${type}"]`)]
        .find((element) => isElementVisible(element)) ?? null;
}

async function ensureModalOpen(type) {
    if (hasVisibleModalOverlay()) {
        return true;
    }

    const trigger = findVisibleTourTrigger(type);
    if (!trigger) {
        return false;
    }

    trigger.click();
    await sleep(150);
    await waitForAnimationFrames(2);

    return hasVisibleModalOverlay();
}

function destroyActiveDriver() {
    clearAdvanceWatcher();
    destroyTourSectionProxy();
    if (!activeDriver) {
        return;
    }

    suppressMarkCompleteOnDestroy = true;
    activeDriver.destroy();
    suppressMarkCompleteOnDestroy = false;
    activeDriver = null;
}

function isModalSectionSelector(selector) {
    return typeof selector === 'string'
        && (selector.includes('modal-create-section') || selector.includes('modal-edit-section'));
}

function isModalSectionBoundary(element) {
    return element.matches(MODAL_SECTION_SELECTOR)
        || element.matches('[data-tour="modal-create-submit"]')
        || element.matches('[data-tour="modal-edit-submit"]')
        || element.matches('.modal-footer');
}

function isElementVisible(element) {
    if (!element || element.nodeType !== Node.ELEMENT_NODE) {
        return false;
    }

    const style = window.getComputedStyle(element);
    if (style.display === 'none' || style.visibility === 'hidden') {
        return false;
    }

    const rect = element.getBoundingClientRect();
    return rect.width > 0 || rect.height > 0;
}

function unionRects(rects) {
    if (!rects.length) {
        return null;
    }

    let top = rects[0].top;
    let left = rects[0].left;
    let right = rects[0].right;
    let bottom = rects[0].bottom;

    for (const rect of rects.slice(1)) {
        top = Math.min(top, rect.top);
        left = Math.min(left, rect.left);
        right = Math.max(right, rect.right);
        bottom = Math.max(bottom, rect.bottom);
    }

    return {
        top,
        left,
        width: right - left,
        height: bottom - top,
    };
}

function collectModalSectionRects(header) {
    const headerRect = header.getBoundingClientRect();
    const modalBody = header.closest('.modal-body, .modal-scroll');

    if (modalBody) {
        const bodyRect = modalBody.getBoundingClientRect();
        let bottom = bodyRect.bottom;

        for (const child of modalBody.children) {
            if (child === header) {
                continue;
            }

            const childRect = child.getBoundingClientRect();
            if (childRect.top <= headerRect.top + 1) {
                continue;
            }

            if (isModalSectionBoundary(child)) {
                bottom = childRect.top;
                break;
            }
        }

        const left = Math.min(headerRect.left, bodyRect.left);
        const right = Math.max(headerRect.right, bodyRect.right);

        return {
            top: headerRect.top,
            left,
            width: right - left,
            height: Math.max(bottom - headerRect.top, headerRect.height),
        };
    }

    const rects = [headerRect];
    let sibling = header.nextElementSibling;

    while (sibling && !isModalSectionBoundary(sibling)) {
        if (isElementVisible(sibling)) {
            rects.push(sibling.getBoundingClientRect());
        }
        sibling = sibling.nextElementSibling;
    }

    return unionRects(rects);
}

function scrollModalTourTarget(element) {
    if (!element) {
        return;
    }

    const header = element.classList?.contains('tour-section-highlight-proxy')
        ? tourSectionProxyHeader
        : element.closest(MODAL_SECTION_SELECTOR) ?? element;

    if (!header) {
        return;
    }

    const scrollParent = header.closest(MODAL_SCROLL_SELECTOR);
    if (!scrollParent) {
        element.scrollIntoView({ block: 'center', inline: 'nearest', behavior: 'auto' });
        return;
    }

    const padding = 16;
    const targetRect = header.getBoundingClientRect();
    const parentRect = scrollParent.getBoundingClientRect();
    const delta = targetRect.top - parentRect.top - padding;

    scrollParent.scrollTop += delta;
}

function positionTourSectionProxy() {
    if (!tourSectionProxy || !tourSectionProxyHeader) {
        return;
    }

    const box = collectModalSectionRects(tourSectionProxyHeader);
    if (!box) {
        return;
    }

    tourSectionProxy.style.top = `${box.top}px`;
    tourSectionProxy.style.left = `${box.left}px`;
    tourSectionProxy.style.width = `${box.width}px`;
    tourSectionProxy.style.height = `${box.height}px`;
}

function handleTourSectionProxyScroll() {
    positionTourSectionProxy();
    activeDriver?.refresh?.();
}

function handleTourSectionProxyResize() {
    positionTourSectionProxy();
    activeDriver?.refresh?.();
}

function destroyTourSectionProxy() {
    if (tourSectionProxyScrollParent) {
        tourSectionProxyScrollParent.removeEventListener('scroll', handleTourSectionProxyScroll);
    }

    if (tourSectionProxyResizeObserver) {
        tourSectionProxyResizeObserver.disconnect();
        tourSectionProxyResizeObserver = null;
    }

    window.removeEventListener('resize', handleTourSectionProxyResize);

    tourSectionProxyScrollParent = null;
    tourSectionProxyHeader = null;

    if (tourSectionProxy) {
        tourSectionProxy.remove();
        tourSectionProxy = null;
    }
}

function observeTourSectionProxyResize(header) {
    if (typeof ResizeObserver === 'undefined') {
        return;
    }

    const modalBody = header.closest('.modal-body, .modal-scroll');
    if (!modalBody) {
        return;
    }

    tourSectionProxyResizeObserver = new ResizeObserver(() => {
        positionTourSectionProxy();
        activeDriver?.refresh?.();
    });
    tourSectionProxyResizeObserver.observe(modalBody);
}

function createTourSectionProxy(header) {
    destroyTourSectionProxy();

    scrollModalTourTarget(header);

    const box = collectModalSectionRects(header);
    if (!box || box.height <= 36) {
        return header;
    }

    tourSectionProxyHeader = header;
    tourSectionProxyScrollParent = header.closest(MODAL_SCROLL_SELECTOR);

    tourSectionProxy = document.createElement('div');
    tourSectionProxy.className = 'tour-section-highlight-proxy';
    tourSectionProxy.setAttribute('aria-hidden', 'true');
    document.body.appendChild(tourSectionProxy);
    positionTourSectionProxy();

    if (tourSectionProxyScrollParent) {
        tourSectionProxyScrollParent.addEventListener('scroll', handleTourSectionProxyScroll, { passive: true });
    }

    observeTourSectionProxyResize(header);
    window.addEventListener('resize', handleTourSectionProxyResize, { passive: true });

    return tourSectionProxy;
}

function resolveModalSectionElement(selector) {
    const header = document.querySelector(selector);
    if (!header) {
        return null;
    }

    return createTourSectionProxy(header);
}

function refreshTourHighlight() {
    if (tourSectionProxy) {
        positionTourSectionProxy();
    }

    requestAnimationFrame(() => {
        activeDriver?.refresh?.();
    });
}

function modalRouteKey(route, type) {
    return `${route}.modal.${type}`;
}

function toDriverStep(rawStep, generation) {
    const selector = rawStep.element;
    const advanceWhen = rawStep.advanceWhen;
    const isInteractiveStep = Boolean(advanceWhen);
    const popover = {
        title: rawStep.title,
        description: rawStep.description,
        side: rawStep.side ?? 'bottom',
        align: rawStep.align ?? 'start',
    };

    if (advanceWhen === 'section-input') {
        popover.showButtons = ['previous', 'close'];
    }

    const step = {
        popover,
        onHighlightStarted: async (element) => {
            clearAdvanceWatcher();

            if (generation !== tourGeneration) {
                return;
            }

            if (isInteractiveStep) {
                destroyTourSectionProxy();

                let target = element;
                if (!target
                    || target.classList?.contains('tour-section-highlight-proxy')
                    || !isElementVisible(target)) {
                    target = await waitForStepElement(selector, true, generation);
                }

                if (generation !== tourGeneration) {
                    return;
                }

                if (target) {
                    scrollModalTourTarget(target);
                }

                refreshTourHighlight();

                if (advanceWhen === 'section-input') {
                    watchSectionAdvance(selector, advanceWhen, () => {
                        setTimeout(() => {
                            if (activeDriver && generation === tourGeneration) {
                                activeDriver.moveNext();
                            }
                        }, ADVANCE_STEP_DELAY_MS);
                    });
                }

                return;
            }

            if (element?.classList?.contains('tour-section-highlight-proxy')) {
                refreshTourHighlight();
            } else if (element) {
                scrollModalTourTarget(element);
                refreshTourHighlight();
            }
        },
        onHighlighted: () => {
            refreshTourHighlight();
        },
        onDeselected: () => {
            clearAdvanceWatcher();
        },
    };

    if (isInteractiveStep) {
        step.disableActiveInteraction = false;
        step.element = () => document.querySelector(selector);
    } else if (isModalSectionSelector(selector)) {
        step.element = () => resolveModalSectionElement(selector);
    } else {
        step.element = () => findVisibleTourElement(selector);
    }

    return step;
}

function isNavTourSelector(selector) {
    return typeof selector === 'string' && selector.includes('data-tour="nav-');
}

function findVisibleTourElement(selector) {
    if (!selector || isNavTourSelector(selector)) {
        return null;
    }

    const roots = [document.querySelector('main'), document.body].filter(Boolean);

    for (const root of roots) {
        for (const element of root.querySelectorAll(selector)) {
            if (element.closest('aside, nav.lg\\:hidden') || !isElementVisible(element)) {
                continue;
            }

            return element;
        }
    }

    return null;
}

async function prepareVisibleSteps(rawSteps, generation = tourGeneration) {
    const prepared = [];

    for (const rawStep of rawSteps) {
        if (generation !== tourGeneration) {
            return [];
        }

        if (!rawStep?.element || isNavTourSelector(rawStep.element)) {
            continue;
        }

        if (rawStep.advanceWhen) {
            prepared.push(toDriverStep(rawStep, generation));
            continue;
        }

        if (!findVisibleTourElement(rawStep.element)) {
            continue;
        }

        prepared.push(toDriverStep(rawStep, generation));
    }

    return prepared;
}

async function markTourComplete(route) {
    const ctx = getContext();
    if (!ctx?.completeUrl) {
        return;
    }

    if (!ctx.completed) {
        ctx.completed = {};
    }
    ctx.completed[route] = new Date().toISOString();

    if (isReplayActive()) {
        markSessionTourComplete(route);
    }

    const client = await getHttpClient();

    try {
        await client.post(ctx.completeUrl, { route });
    } catch (error) {
        console.warn('Gagal menyimpan status tour selesai.', error);
    }
}

function runDriver(steps, { completeRoute, closeModalsOnDestroy = false, generation = tourGeneration }) {
    let markedComplete = false;

    activeDriver = driver({
        showProgress: true,
        progressText: '{{current}} dari {{total}}',
        nextBtnText: 'Lanjut',
        prevBtnText: 'Kembali',
        doneBtnText: 'Selesai',
        allowClose: true,
        disableActiveInteraction: false,
        overlayOpacity: 0.55,
        stagePadding: 8,
        stageRadius: 12,
        popoverClass: 'sipp-tour-popover',
        onNextClick: (_element, _step, { driver: activeTour }) => {
            const isLastStep = activeTour.getActiveIndex() === steps.length - 1;

            if (isLastStep) {
                activeTour.destroy();
                return;
            }

            activeTour.moveNext();
        },
        onDestroyed: () => {
            clearAdvanceWatcher();
            destroyTourSectionProxy();
            if (closeModalsOnDestroy && !suppressCloseModalsOnDestroy) {
                closeAllModals();
            }
            // ponytail: dismiss (X) = selesai; suppressMarkCompleteOnDestroy saat ganti tour
            if (
                !suppressMarkCompleteOnDestroy
                && !markedComplete
                && generation === tourGeneration
            ) {
                markedComplete = true;
                markTourComplete(completeRoute);
            }
            activeDriver = null;
        },
        steps,
    });

    activeDriver.drive();
}

async function startPageTour({ force = false } = {}) {
    const generation = ++tourGeneration;
    const ctx = getContext();
    if (!ctx?.steps?.length) {
        return;
    }

    if (!force && !shouldRunTour(ctx.route)) {
        return;
    }

    destroyActiveDriver();
    closeAllModals();

    const steps = await prepareVisibleSteps(ctx.steps, generation);
    if (!steps.length || generation !== tourGeneration) {
        return;
    }

    runDriver(steps, {
        completeRoute: ctx.route,
        closeModalsOnDestroy: true,
        generation,
    });
}

async function startModalTour(type, { force = false } = {}) {
    const generation = ++tourGeneration;
    const ctx = getContext();
    if (!ctx?.route || !ctx.modalSteps?.[type]?.length) {
        return;
    }

    const hubRoute = ctx.hubRoute ?? ctx.route;
    const completeKey = modalRouteKey(hubRoute, type);
    if (!force && !shouldRunTour(completeKey)) {
        return;
    }

    const steps = await prepareVisibleSteps(ctx.modalSteps?.[type], generation);
    if (!steps.length || generation !== tourGeneration) {
        return;
    }

    suppressCloseModalsOnDestroy = true;
    destroyActiveDriver();
    suppressCloseModalsOnDestroy = false;

    if (generation !== tourGeneration) {
        return;
    }

    await ensureModalOpen(type);

    if (generation !== tourGeneration) {
        return;
    }

    const ready = await waitForModalReady(type, generation);
    if (!ready || generation !== tourGeneration) {
        return;
    }

    runDriver(steps, {
        completeRoute: completeKey,
        closeModalsOnDestroy: false,
        generation,
    });
}

function scheduleModalTour(type) {
    clearTimeout(modalTourTimer);
    modalTourTimer = setTimeout(() => startModalTour(type), 0);
}

window.restartPageTour = () => {
    const ctx = getContext();
    if (!ctx?.hubRoute) {
        return;
    }

    startReplaySession(ctx.hubRoute);
    startPageTour({ force: true });
};

function shouldAutoStartPageTour() {
    const ctx = getContext();
    if (!ctx?.steps?.length) {
        return false;
    }

    if (ctx.isHubPage && isReplayActive()) {
        return false;
    }

    return shouldRunTour(ctx.route);
}

function scheduleAutoTour() {
    setTimeout(() => {
        if (!shouldAutoStartPageTour()) {
            return;
        }

        startPageTour();
    }, 400);
}

function handleTourPageHide() {
    clearTimeout(modalTourTimer);
    modalTourTimer = null;
    destroyActiveDriver();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', scheduleAutoTour, { once: true });
    document.addEventListener('turbo:load', scheduleAutoTour);
} else {
    scheduleAutoTour();
}

window.addEventListener('pagehide', handleTourPageHide);

document.addEventListener('click', (event) => {
    const tourTrigger = event.target.closest('[data-tour-trigger]');
    if (tourTrigger) {
        event.preventDefault();
        window.restartPageTour();
        return;
    }

    const openTrigger = event.target.closest('[data-tour-open-modal]');
    const demoAction = event.target.closest('[data-tour-demo-action]');
    const type = openTrigger?.dataset.tourOpenModal ?? demoAction?.dataset.tourDemoAction;

    if (type) {
        const ctx = getContext();
        if (!ctx?.modalSteps?.[type]?.length) {
            return;
        }

        const completeKey = modalCompleteKey(type);
        if (completeKey && !shouldRunTour(completeKey)) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();
        scheduleModalTour(type);
    }
});
