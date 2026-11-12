import { Calendar } from '@fullcalendar/core';
import idLocale from '@fullcalendar/core/locales/id';
import dayGridPlugin from '@fullcalendar/daygrid';
import listPlugin from '@fullcalendar/list';

function formatKelasDisplay(raw) {
    const s = String(raw).trim();
    if (!s) {
        return '';
    }

    return s
        .split(/\s+/)
        .map((word) => {
            const lower = word.toLowerCase();
            if (lower === 'kelas') {
                return 'Kelas';
            }
            if (word.length === 1) {
                return word.toUpperCase();
            }

            return lower.charAt(0).toUpperCase() + lower.slice(1);
        })
        .join(' ');
}

function makeKelasBadge(kelas, onEvent = false) {
    const span = document.createElement('span');
    span.className = onEvent ? 'jurnal-fc-kelas-chip jurnal-fc-kelas-chip--on-event' : 'badge badge-teal jurnal-fc-kelas-chip';
    span.textContent = formatKelasDisplay(kelas);
    return span;
}

function buildListEventContent(arg) {
    const detail = arg.event.extendedProps.detail || {};
    const title = arg.event.title;
    const kelas = detail.kelas_name || '';
    const desc = detail.description || '';

    const container = document.createElement('div');
    container.className = 'jurnal-fc-list-event py-0.5';

    const titleEl = document.createElement('div');
    titleEl.className = 'jurnal-fc-list-event-title';
    titleEl.textContent = title;
    container.appendChild(titleEl);

    if (kelas && kelas !== '-') {
        const meta = document.createElement('div');
        meta.className = 'jurnal-fc-list-event-meta';
        meta.appendChild(makeKelasBadge(kelas));
        container.appendChild(meta);
    }

    if (desc) {
        const descEl = document.createElement('p');
        descEl.className = 'jurnal-fc-list-desc';
        descEl.textContent = desc;
        container.appendChild(descEl);
    }

    return { domNodes: [container] };
}

function buildDayGridEventContent(arg) {
    const detail = arg.event.extendedProps.detail || {};
    const kelas = detail.kelas_name || '';

    const wrap = document.createElement('div');
    wrap.className = 'jurnal-fc-day-event';

    const titleEl = document.createElement('div');
    titleEl.className = 'jurnal-fc-day-event-title';
    titleEl.textContent = arg.event.title;
    wrap.appendChild(titleEl);

    if (kelas && kelas !== '-') {
        wrap.appendChild(makeKelasBadge(kelas, true /* on colored event */));
    }

    return { domNodes: [wrap] };
}

function loadEventsFromPage() {
    const jsonEl = document.getElementById('kegiatan-calendar-json');
    if (jsonEl?.textContent) {
        try {
            const data = JSON.parse(jsonEl.textContent.trim());
            return Array.isArray(data) ? data : [];
        } catch {
            return [];
        }
    }
    return [];
}

function initKegiatanCalendar() {
    const mount = document.getElementById('kegiatan-calendar-mount');
    if (!mount) {
        return;
    }
    if (mount.dataset.fcInitialized === '1') {
        return;
    }
    mount.dataset.fcInitialized = '1';

    const url = new window.URL(window.location.href);
    const viewFromUrl = url.searchParams.get('view') || 'listMonth';

    const events = loadEventsFromPage();
    const yearFromData = parseInt(mount.dataset.year || '', 10);
    const monthFromData = parseInt(mount.dataset.month || '', 10);
    const dayFromData = parseInt(mount.dataset.day || '1', 10);
    const initialDate =
        yearFromData > 0 && monthFromData >= 1 && monthFromData <= 12
            ? `${yearFromData}-${String(monthFromData).padStart(2, '0')}-${String(dayFromData).padStart(2, '0')}`
            : undefined;

    const calendarEl = document.createElement('div');
    calendarEl.className = 'jurnal-fc-inner min-h-[520px]';
    mount.appendChild(calendarEl);

    let calInstance;
    let isInternalNavigation = false;

    calInstance = new Calendar(calendarEl, {
        plugins: [dayGridPlugin, listPlugin],
        initialView: viewFromUrl,
        initialDate,
        locale: idLocale,
        buttonText: {
            today: 'Hari ini',
            month: 'Bulan',
            list: 'Daftar',
        },
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'listMonth,dayGridMonth',
        },
        eventOrder: function (a, b) {
            // Newest first in list view
            const aDate = a.start ? a.start.valueOf() : 0;
            const bDate = b.start ? b.start.valueOf() : 0;
            return bDate - aDate;
        },
        height: 'auto',
        events,
        views: {
            listMonth: {
                eventContent: buildListEventContent,
            },
            dayGridMonth: {
                eventContent: buildDayGridEventContent,
            },
        },
        eventClick(info) {
            info.jsEvent.preventDefault();
            window.dispatchEvent(
                new CustomEvent('kegiatan-cal-click', {
                    detail: {
                        id: info.event.id,
                        title: info.event.title,
                        start: info.event.startStr,
                        extendedProps: info.event.extendedProps || {},
                    },
                }),
            );
        },
        datesSet(dateInfo) {
            if (dateInfo.view.type === 'listMonth') {
                const tbody = mount.querySelector('.fc-list-table tbody');
                if (tbody && !tbody.dataset.reversed) {
                    const rows = Array.from(tbody.rows);
                    const groups = [];
                    let currentGroup = null;
                    rows.forEach(row => {
                        if (row.classList.contains('fc-list-day')) {
                            if (currentGroup) groups.push(currentGroup);
                            currentGroup = [row];
                        } else if (currentGroup) {
                            currentGroup.push(row);
                        }
                    });
                    if (currentGroup) groups.push(currentGroup);
                    groups.reverse();
                    tbody.innerHTML = '';
                    groups.forEach(group => group.forEach(row => tbody.appendChild(row)));
                    tbody.dataset.reversed = '1';
                }
            }

            if (isInternalNavigation) {
                return;
            }

            const d = dateInfo.view.currentStart || calInstance.getDate();
            const y = d.getFullYear();
            const m = d.getMonth() + 1;
            const currentView = dateInfo.view.type;

            const curUrl = new window.URL(window.location.href);
            const curY = parseInt(curUrl.searchParams.get('year') || '0', 10);
            const curM = parseInt(curUrl.searchParams.get('month') || '0', 10);
            const curV = curUrl.searchParams.get('view') || 'listMonth';

            // Refresh if year/month changed or if view changed
            if (curY === y && curM === m && curV === currentView) {
                return;
            }

            isInternalNavigation = true;
            curUrl.searchParams.set('year', String(y));
            curUrl.searchParams.set('month', String(m));
            curUrl.searchParams.set('view', currentView);

            window.location.href = curUrl.toString();
        },
    });

    calInstance.render();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initKegiatanCalendar);
} else {
    initKegiatanCalendar();
}
