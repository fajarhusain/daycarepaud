@php
    use App\Services\ActivityLogScopeService;
    $scope = app(ActivityLogScopeService::class);
@endphp
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background:#1A6B6B;">
                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
            </div>
            <div>
                <h2 class="font-bold text-xl" style="color:#2C2C2C;">Log Aktivitas</h2>
                <p class="text-xs" style="color:#9E9790;">Riwayat perubahan data di panel admin</p>
            </div>
        </div>
    </x-slot>

    <div class="app-page-shell space-y-5">
        @if(request()->routeIs('admin.activity-log.*'))
            <x-settings-tabs />
        @endif
        <div class="card p-4">
            <form method="GET" class="flex flex-wrap gap-3 items-end" data-tour="activity-log-filter">
                <div>
                    <label class="input-label">Dari</label>
                    <input type="date" name="from" value="{{ request('from') }}" class="input-field">
                </div>
                <div>
                    <label class="input-label">Sampai</label>
                    <input type="date" name="to" value="{{ request('to') }}" class="input-field">
                </div>
                <div>
                    <label class="input-label">Aksi</label>
                    <select name="event" class="input-field">
                        <option value="">Semua</option>
                        @foreach (['created' => 'Buat', 'updated' => 'Ubah', 'deleted' => 'Hapus'] as $val => $label)
                            <option value="{{ $val }}" @selected(request('event') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <x-toolbar.filter-icon variant="secondary" />
                <x-filter-reset :href="url()->current()" />
                @if(request()->routeIs('admin.activity-log.*'))
                    <x-export-excel route="admin.activity-log.export" />
                @endif
            </form>
        </div>

        <div class="card overflow-hidden">
            <div class="px-6 py-4 border-b" style="border-color:rgba(0,0,0,0.06);">
                <h3 class="section-title">Riwayat Aktivitas</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Pengguna</th>
                            <th>Aksi</th>
                            <th>Objek</th>
                            <th>Perubahan</th>
                            <th>Route</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activities as $activity)
                            <tr>
                                <td class="whitespace-nowrap text-xs" style="color:#9E9790;">
                                    {{ $activity->created_at?->format('d M Y H:i') }}
                                </td>
                                <td>{{ $activity->causer?->name ?? 'Sistem' }}</td>
                                <td>
                                    @php
                                        $eventLabel = match ($activity->event) {
                                            'created' => 'Buat',
                                            'updated' => 'Ubah',
                                            'deleted' => 'Hapus',
                                            default => $activity->event ?? '-',
                                        };
                                    @endphp
                                    <span class="text-xs font-semibold px-2 py-0.5 rounded-lg" style="background:#E8F2F2;color:#1A6B6B;">{{ $eventLabel }}</span>
                                </td>
                                <td class="text-sm">{{ $scope->subjectLabel($activity) }}</td>
                                <td class="text-xs max-w-xs truncate" style="color:#9E9790;" title="{{ $scope->changesSummary($activity) }}">
                                    {{ $scope->changesSummary($activity) }}
                                </td>
                                <td class="text-xs" style="color:#9E9790;">{{ $activity->properties['route_name'] ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-sm" style="color:#9E9790;">Belum ada aktivitas tercatat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t">
                <x-per-page-selector :paginator="$activities" />
                {{ $activities->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
