import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/guest.css',
                'resources/js/app.js',
                'resources/js/guest.js',
                'resources/js/guest-ascent.js',
                'resources/js/tour.js',
                'resources/js/kegiatan-calendar.js',
                'resources/js/dashboard-charts.js',
                'resources/js/presensi-setting-map.js',
            ],
            refresh: true,
        }),
    ],
});
