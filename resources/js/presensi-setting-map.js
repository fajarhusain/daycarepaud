import L from 'leaflet';
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';
import 'leaflet/dist/leaflet.css';

delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: markerIcon2x,
    iconUrl: markerIcon,
    shadowUrl: markerShadow,
});

const DEFAULT_CENTER = [-6.175392, 106.827153];
const DEFAULT_ZOOM = 13;
const NOMINATIM = 'https://nominatim.openstreetmap.org/search';

function parseCoordinate(raw) {
    if (!raw || typeof raw !== 'string') {
        return null;
    }
    const parts = raw.replace(/\s+/g, '').split(',');
    if (parts.length !== 2) {
        return null;
    }
    const lat = Number.parseFloat(parts[0]);
    const lng = Number.parseFloat(parts[1]);
    if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
        return null;
    }
    if (lat < -90 || lat > 90 || lng < -180 || lng > 180) {
        return null;
    }

    return { lat, lng };
}

function formatCoordinate(lat, lng) {
    return `${lat.toFixed(6)},${lng.toFixed(6)}`;
}

function readRadius(input) {
    const value = Number.parseInt(input?.value ?? '', 10);
    if (!Number.isFinite(value) || value < 10) {
        return 100;
    }

    return Math.min(value, 5000);
}

/** @type {import('leaflet').Map | null} */
let activeMap = null;

function teardownPresensiSettingMap() {
    if (activeMap) {
        activeMap.remove();
        activeMap = null;
    }
}

function initPresensiSettingMap() {
    const mapEl = document.getElementById('presensi-setting-map');
    const coordInput = document.getElementById('location_coordinate');
    const radiusInput = document.querySelector('input[name="radius_m"]');
    const searchInput = document.getElementById('map-search-query');
    const searchBtn = document.getElementById('map-search-btn');
    const searchResults = document.getElementById('map-search-results');
    const detectBtn = document.getElementById('btn-deteksi-lokasi');
    const statusEl = document.getElementById('deteksi-status');
    const hasilEl = document.getElementById('deteksi-hasil');

    if (!mapEl || !coordInput) {
        return;
    }

    teardownPresensiSettingMap();

    const parsed = parseCoordinate(coordInput.value);
    const center = parsed ? [parsed.lat, parsed.lng] : DEFAULT_CENTER;
    const zoom = parsed ? 16 : DEFAULT_ZOOM;

    const map = L.map(mapEl, { scrollWheelZoom: true }).setView(center, zoom);
    activeMap = map;

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    }).addTo(map);

    let marker = null;
    let radiusCircle = null;

    const syncHasil = (lat, lng, extra = '') => {
        if (!hasilEl) {
            return;
        }
        hasilEl.textContent = `${lat.toFixed(6)}, ${lng.toFixed(6)}${extra}`;
    };

    const setMarker = (lat, lng, { pan = true } = {}) => {
        if (marker) {
            marker.setLatLng([lat, lng]);
        } else {
            marker = L.marker([lat, lng], { draggable: true }).addTo(map);
            marker.on('dragend', () => {
                const pos = marker.getLatLng();
                coordInput.value = formatCoordinate(pos.lat, pos.lng);
                syncHasil(pos.lat, pos.lng);
                updateRadiusCircle(pos.lat, pos.lng);
            });
        }

        coordInput.value = formatCoordinate(lat, lng);
        syncHasil(lat, lng);
        updateRadiusCircle(lat, lng);
        if (pan) {
            map.panTo([lat, lng]);
        }
    };

    const updateRadiusCircle = (lat, lng) => {
        const radius = readRadius(radiusInput);
        if (radiusCircle) {
            radiusCircle.setLatLng([lat, lng]);
            radiusCircle.setRadius(radius);
        } else {
            radiusCircle = L.circle([lat, lng], {
                radius,
                color: '#1A6B6B',
                fillColor: '#1A6B6B',
                fillOpacity: 0.12,
                weight: 2,
            }).addTo(map);
        }
    };

    if (parsed) {
        setMarker(parsed.lat, parsed.lng, { pan: false });
    }

    coordInput.addEventListener('change', () => {
        const next = parseCoordinate(coordInput.value);
        if (!next) {
            return;
        }
        setMarker(next.lat, next.lng);
    });

    radiusInput?.addEventListener('input', () => {
        if (!marker) {
            return;
        }
        const pos = marker.getLatLng();
        updateRadiusCircle(pos.lat, pos.lng);
    });

    map.on('click', (event) => {
        setMarker(event.latlng.lat, event.latlng.lng);
    });

    const clearSearchResults = () => {
        if (searchResults) {
            searchResults.innerHTML = '';
            searchResults.classList.add('hidden');
        }
    };

    const renderSearchResults = (items) => {
        if (!searchResults) {
            return;
        }
        searchResults.innerHTML = '';
        if (!items.length) {
            searchResults.innerHTML = '<p class="px-3 py-2 text-xs" style="color:#9A9188;">Lokasi tidak ditemukan.</p>';
            searchResults.classList.remove('hidden');

            return;
        }

        items.forEach((item) => {
            const lat = Number.parseFloat(item.lat);
            const lng = Number.parseFloat(item.lon);
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'w-full text-left px-3 py-2 text-sm hover:bg-black/5 border-b last:border-b-0';
            button.style.borderColor = 'rgba(0,0,0,0.06)';
            button.textContent = item.display_name;
            button.addEventListener('click', () => {
                setMarker(lat, lng);
                map.setView([lat, lng], 16);
                clearSearchResults();
                if (searchInput) {
                    searchInput.value = item.display_name.split(',')[0];
                }
            });
            searchResults.appendChild(button);
        });
        searchResults.classList.remove('hidden');
    };

    const runSearch = async () => {
        const query = (searchInput?.value ?? '').trim();
        if (query.length < 3) {
            if (statusEl) {
                statusEl.textContent = 'Ketik minimal 3 karakter untuk mencari daerah.';
                statusEl.style.color = '#C0392B';
            }

            return;
        }

        if (searchBtn) {
            searchBtn.disabled = true;
        }
        if (statusEl) {
            statusEl.textContent = 'Mencari lokasi…';
            statusEl.style.color = '#6B6560';
        }

        try {
            const url = new URL(NOMINATIM);
            url.searchParams.set('q', query);
            url.searchParams.set('format', 'json');
            url.searchParams.set('limit', '6');
            url.searchParams.set('countrycodes', 'id');

            const response = await fetch(url.toString(), {
                headers: { Accept: 'application/json' },
            });
            if (!response.ok) {
                throw new Error('search failed');
            }
            const data = await response.json();
            renderSearchResults(Array.isArray(data) ? data : []);
            if (statusEl) {
                statusEl.textContent = 'Pilih hasil pencarian atau klik peta untuk menyesuaikan titik.';
                statusEl.style.color = '#1A6B6B';
            }
        } catch {
            if (statusEl) {
                statusEl.textContent = 'Gagal mencari lokasi. Coba lagi.';
                statusEl.style.color = '#C0392B';
            }
        } finally {
            if (searchBtn) {
                searchBtn.disabled = false;
            }
        }
    };

    searchBtn?.addEventListener('click', runSearch);
    searchInput?.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            runSearch();
        }
    });

    detectBtn?.addEventListener('click', () => {
        if (!navigator.geolocation) {
            if (statusEl) {
                statusEl.textContent = 'Browser tidak mendukung deteksi lokasi.';
                statusEl.style.color = '#C0392B';
            }

            return;
        }

        detectBtn.disabled = true;
        if (statusEl) {
            statusEl.textContent = 'Mengambil lokasi…';
            statusEl.style.color = '#6B6560';
        }

        navigator.geolocation.getCurrentPosition(
            (position) => {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                setMarker(lat, lng);
                map.setView([lat, lng], 17);
                syncHasil(lat, lng, ` (akurasi ±${Math.round(position.coords.accuracy)} m)`);
                if (statusEl) {
                    statusEl.textContent = 'Lokasi terdeteksi. Simpan form untuk menerapkan.';
                    statusEl.style.color = '#1A6B6B';
                }
                detectBtn.disabled = false;
            },
            () => {
                if (statusEl) {
                    statusEl.textContent = 'Gagal mengambil lokasi. Izinkan akses lokasi lalu coba lagi.';
                    statusEl.style.color = '#C0392B';
                }
                detectBtn.disabled = false;
            },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 },
        );
    });

    requestAnimationFrame(() => {
        map.invalidateSize();
        requestAnimationFrame(() => map.invalidateSize());
    });
}

document.addEventListener('turbo:before-cache', teardownPresensiSettingMap);
document.addEventListener('turbo:load', initPresensiSettingMap);

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPresensiSettingMap);
} else {
    initPresensiSettingMap();
}
