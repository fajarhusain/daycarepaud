<x-app-layout>
    <div class="app-page-shell dashboard-stack">
        <div class="dashboard-kpi-grid" data-tour="superadmin-dashboard-stats">
            <x-dashboard.kpi label="Total Lembaga" :value="$totalLembaga" :href="route('superadmin.lembaga.index')" link-label="Kelola"
                icon="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            <x-dashboard.kpi label="Total Sekolah" :value="$totalSekolah" :href="route('superadmin.sekolah.index')" link-label="Lihat"
                icon="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            <x-dashboard.kpi label="Total Pengguna" :value="$totalUsers"
                icon="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            <x-dashboard.kpi label="AI Terkonfigurasi" :value="$totalAiConfigured" :href="route('superadmin.ai-setting.index')" link-label="Pengaturan"
                icon="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
        </div>

        @if(!empty($dashboardCharts))
            <x-dashboard-charts :charts="$dashboardCharts" />
        @endif

        <x-dashboard.panel title="Lembaga Terbaru">
            <div class="divide-y divide-black/5">
                @forelse($recentLembagas as $l)
                    <div class="card-pad flex justify-between items-center gap-3">
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold text-sm truncate text-[var(--text-primary)]">{{ $l->name }}</p>
                            <p class="text-xs truncate text-[var(--text-muted)]">{{ $l->address ?? '-' }}</p>
                        </div>
                        <span class="text-xs shrink-0 text-[var(--text-muted)]">{{ $l->created_at?->format('d M Y') }}</span>
                    </div>
                @empty
                    <p class="px-6 py-8 text-center text-sm text-[var(--text-muted)]">Belum ada lembaga.</p>
                @endforelse
            </div>
        </x-dashboard.panel>
    </div>

    @if(!empty($dashboardCharts ?? []))
        @push('scripts')
            @vite(['resources/js/dashboard-charts.js'])
            <script type="application/json" id="dashboard-charts-json">@json($dashboardCharts)</script>
        @endpush
    @endif
</x-app-layout>
