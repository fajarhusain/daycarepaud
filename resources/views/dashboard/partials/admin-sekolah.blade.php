@php
    $adminUx = $adminDashboardUx ?? ['lead' => '', 'summary' => [], 'attention' => []];
    $adminChartCollection = collect($dashboardCharts ?? []);
    $adminChartsWide = $adminChartCollection->where('id', '!=', 'admin-presensi-bulan')->values()->all();
    $adminChartPresensi = $adminChartCollection->firstWhere('id', 'admin-presensi-bulan');
    $acts = $adminUx['activities'] ?? ['items' => [], 'total' => 0, 'href' => route('admin.kegiatan.index')];
    $sekolahName = $adminUx['sekolahName'] ?? 'Sekolah';
@endphp

<div class="dashboard-role dashboard-role--admin-sekolah">
    <x-dashboard.hero
        eyebrow="Admin Sekolah"
        :title="$sekolahName"
        :description="$adminUx['lead'] ?? ''"
    >
        <x-slot:actions>
            <a href="{{ route('admin.presensi.index') }}" class="dashboard-hero-action dashboard-hero-action--primary">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ \App\Support\DashboardIcons::path('check') }}" />
                </svg>
                Presensi siswa
            </a>
        </x-slot:actions>
    </x-dashboard.hero>

    <section aria-label="Ringkasan KPI" class="dashboard-summary-kpis">
        <div class="dashboard-kpi-grid" data-tour="dashboard-stats">
            @foreach($adminUx['summary'] ?? [] as $stat)
                <x-dashboard.kpi-context
                    :label="$stat['label']"
                    :value="$stat['value']"
                    :context="$stat['context'] ?? null"
                    :href="$stat['href'] ?? null"
                    :icon="$stat['icon'] ?? null"
                />
            @endforeach
        </div>
    </section>

    <x-dashboard.attention
        :items="$adminUx['attention'] ?? []"
        :overflow-href="$adminUx['attentionOverflowHref'] ?? null"
        data-tour="dashboard-attention"
    >
        @if($adminChartPresensi)
            <x-slot:belowPanel>
                <x-dashboard.chart-card
                    :chart="$adminChartPresensi"
                    spotlight
                    data-tour="dashboard-presensi-spotlight"
                />
            </x-slot:belowPanel>
        @endif
        <x-slot:aside>
            <div class="dashboard-attention-aside-stack" data-tour="dashboard-recent">
                <x-dashboard.panel title="Akses cepat" data-tour="dashboard-quick-links">
                    <nav class="dashboard-quick-grid" aria-label="Akses cepat">
                        <x-dashboard.quick-link :href="route('admin.anak.index')" title="Data Siswa" icon="{{ \App\Support\DashboardIcons::path('users') }}" />
                        <x-dashboard.quick-link :href="route('admin.presensi.index')" title="Presensi Siswa" icon="{{ \App\Support\DashboardIcons::path('check') }}" />
                        <x-dashboard.quick-link :href="route('admin.presensi-guru.index')" title="Presensi Guru" icon="{{ \App\Support\DashboardIcons::path('clock') }}" />
                        <x-dashboard.quick-link :href="route('admin.kegiatan.index')" title="Agenda Belajar" icon="{{ \App\Support\DashboardIcons::path('calendar') }}" />
                        <x-dashboard.quick-link :href="route('admin.pembayaran-bulanan.index')" title="Tagihan SPP" icon="{{ \App\Support\DashboardIcons::path('cash') }}" />
                        <x-dashboard.quick-link :href="route('admin.cashflow.index')" title="Buku Kas" icon="{{ \App\Support\DashboardIcons::path('chart') }}" />
                    </nav>
                </x-dashboard.panel>

                <x-dashboard.activity-list-card
                    title="Kegiatan hari ini"
                    :items="$acts['items'] ?? []"
                    :total="$acts['total'] ?? 0"
                    :href="$acts['href'] ?? route('admin.kegiatan.index')"
                    link-label="Lihat semua"
                />
            </div>
        </x-slot:aside>
    </x-dashboard.attention>

    @if(!empty($adminChartsWide))
        <section class="dashboard-patterns-section" aria-labelledby="admin-patterns-heading">
            <div class="dashboard-section-head">
                <h2 id="admin-patterns-heading" class="dashboard-section-title">Pola & distribusi</h2>
                <p class="dashboard-section-desc">Tren murid, kegiatan, dan sebaran kelas</p>
            </div>
            <x-dashboard-charts :charts="$adminChartsWide" />
        </section>
    @endif
</div>
