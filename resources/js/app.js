import './bootstrap';
import './documentation-media';
import './turbo-nav';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

document.addEventListener('change', (e) => {
    const select = e.target;
    if (!(select instanceof HTMLSelectElement)) {
        return;
    }

    if (!select.matches('select[data-per-page-selector]')) {
        return;
    }

    const param = select.dataset.perPageParam || 'per_page';
    const pageName = select.dataset.perPagePageName || 'page';
    const url = new URL(window.location.href);

    url.searchParams.set(param, select.value);
    url.searchParams.set(pageName, '1');

    window.location.assign(url.toString());
});
