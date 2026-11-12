@php
    use App\Support\IndonesianMonths;

    $months = IndonesianMonths::NAMES;
    $periodeLabel = IndonesianMonths::label($bulan, $tahun);
    $search = $search ?? '';
    $hasTokens = $hasTokens ?? false;
    $tokenBalance = $tokenBalance ?? 0;
    $tokenFallbackMonev = $tokenFallbackMonev ?? 'Maaf, fitur ini sedang terbatas.';
    $canBulkSelected = $aiReady && $hasTokens && $tokenBalance > 0 && empty($activeGeneration);
    $tourPrefix = $tourPrefix ?? 'admin-monev';
    $exportRoute = $exportRoute ?? null;
@endphp

@if(session('success'))
    <div class="alert-success mb-5">
        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        {{ session('success') }}
    </div>
@endif
@if(session('warning'))
    <div class="alert-danger mb-5">{{ session('warning') }}</div>
@endif
@if($errors->any())
    <div class="alert-danger mb-5">
        <ul class="list-disc pl-5 text-sm">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if(!empty($statusRoute) && !empty($activeGeneration))
    @include('monev._generation-progress', ['statusRoute' => $statusRoute, 'activeGeneration' => $activeGeneration])
@endif

<div class="card mb-6" data-tour="{{ $tourPrefix }}-summary">
    <div class="px-5 sm:px-6 py-5 border-b space-y-5" style="background:#FAF6F0; border-color: rgba(0,0,0,0.06);">
        <div class="space-y-1">
            <h3 class="text-xl font-bold" style="color:#2C2C2C;">Ringkasan Monev Matrikulasi</h3>
            <p class="text-sm leading-relaxed m-0 max-w-3xl" style="color:#9E9790;">
                Monitoring & evaluasi perkembangan siswa berbasis data pencapaian matrikulasi per bulan.
            </p>
        </div>

        <form data-tour="{{ $tourPrefix }}-filters" method="get" action="{{ $indexRoute }}"
            class="filter-toolbar-inline">
            <div class="filter-toolbar-field">
                <label class="input-label" for="monev-search">Cari Siswa</label>
                <input id="monev-search" type="search" name="search" value="{{ $search }}" placeholder="Nama siswa..."
                    class="input-field w-full h-9 md:h-11">
            </div>
            @if($kelasList->count() > 1)
                <div class="filter-toolbar-field">
                    <label class="input-label" for="monev-kelas">Kelas</label>
                    <select id="monev-kelas" name="kelas_id" class="input-field w-full h-9 md:h-11">
                        <option value="">Semua Kelas</option>
                        @foreach($kelasList as $k)
                            <option value="{{ $k->id }}" @selected($filterKelasId == $k->id)>{{ $k->name }}</option>
                        @endforeach
                    </select>
                </div>
            @elseif($filterKelasId)
                <input type="hidden" name="kelas_id" value="{{ $filterKelasId }}">
            @endif
            <div class="filter-toolbar-field">
                <label class="input-label" for="monev-bulan">Bulan</label>
                <select id="monev-bulan" name="bulan" class="input-field w-full h-9 md:h-11">
                    @foreach($months as $num => $name)
                        <option value="{{ $num }}" @selected($bulan == $num)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-toolbar-field">
                <label class="input-label" for="monev-tahun">Tahun</label>
                <select id="monev-tahun" name="tahun" class="input-field w-full h-9 md:h-11">
                    @for($y = now()->year; $y >= now()->year - 2; $y--)
                        <option value="{{ $y }}" @selected($tahun == $y)>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="filter-toolbar-actions">
                <button type="submit" class="btn-primary h-9 w-9 md:h-11 md:w-11 p-0 shrink-0" title="Terapkan Filter" aria-label="Terapkan Filter">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </button>
                <x-filter-reset :href="$indexRoute" compact />
                @if($exportRoute)
                    <x-export-excel :route="$exportRoute" :icon-only="true" />
                @endif
            </div>
        </form>
    </div>

    <div class="px-5 sm:px-6 py-4 border-b flex flex-col md:flex-row md:flex-wrap md:items-center md:justify-between gap-4" style="border-color: rgba(0,0,0,0.06); background:#fff;">
        <div class="flex flex-col sm:flex-row sm:items-center gap-3 min-w-0 flex-1">
            <span class="inline-flex items-center gap-2 text-xs font-semibold px-3 py-1.5 rounded-lg shrink-0" style="background:#E8F5F5; color:#1A6B6B;">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                {{ $periodeLabel }}
            </span>
            @if($isCurrentMonth)
                <p class="text-xs m-0" style="color:#9E9790;">
                    Ringkasan otomatis lengkap tersedia setiap tanggal 1 bulan berikutnya.
                </p>
            @endif
        </div>

        <div class="w-full md:w-auto md:max-w-full shrink-0">
            @if($isCurrentMonth && $canManual && $aiReady && $hasTokens)
                <form method="post" action="{{ $generateRoute }}" onsubmit="return confirm('Generate ringkasan AI untuk semua siswa dalam scope ini? Proses berjalan di background.');">
                    @csrf
                    @if($filterKelasId)
                        <input type="hidden" name="kelas_id" value="{{ $filterKelasId }}">
                    @endif
                    <button type="submit" class="btn-primary text-sm whitespace-nowrap">
                        Generate Semua (Bulan Ini)
                    </button>
                </form>
            @elseif($isCurrentMonth && $canManual && $aiReady && !$hasTokens)
                <span class="inline-flex items-center text-xs font-medium px-3 py-2 rounded-lg whitespace-nowrap" style="background:#FEE2E2; color:#DC2626;">
                    Token AI habis
                </span>
            @elseif($isCurrentMonth && $activeGeneration && !$activeGeneration->isFinished())
                <span class="inline-flex items-center text-xs font-medium px-3 py-2 rounded-lg animate-pulse whitespace-nowrap" style="background:#E8F5F5; color:#1A6B6B;">
                    Generate sedang berjalan...
                </span>
            @elseif($isCurrentMonth && !$canManual)
                <span class="inline-flex items-center text-xs font-medium px-3 py-2 rounded-lg border whitespace-normal sm:whitespace-nowrap" style="background:#EDE8DF; color:#4A4540; border-color: rgba(0,0,0,0.08);">
                    Generate semua bulan ini sudah dipakai
                </span>
            @endif
        </div>
    </div>

    @if($isCurrentMonth && !$aiReady)
        <div class="px-5 sm:px-6 py-3 text-sm border-b" style="background:#FFF8E6; color:#8A6D00; border-color: rgba(0,0,0,0.06);">
            Pengaturan AI belum dikonfigurasi. Minta admin lembaga mengisi API Key di menu Pengaturan AI.
        </div>
    @elseif($aiReady && !$hasTokens)
        <div class="px-5 sm:px-6 py-3 text-sm border-b" style="background:#FEE2E2; color:#DC2626; border-color: rgba(0,0,0,0.06);">
            {{ $tokenFallbackMonev }}
        </div>
    @endif
</div>

<div class="card overflow-hidden" x-data="window.monevBulkTable()">
    <div class="px-6 py-4 border-b flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3" style="border-color: rgba(0,0,0,0.06);">
        <div>
            <h3 class="section-title">Daftar Siswa</h3>
            <p class="section-subtitle">{{ $anaks->total() }} siswa pada periode {{ $periodeLabel }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2" x-show="selected.length > 0" x-cloak>
            <span class="text-xs font-semibold px-2 py-1 rounded" style="background:#E8F5F5; color:#1A6B6B;" x-text="selected.length + ' dipilih'"></span>
            @if($canBulkSelected)
            <form method="post" action="{{ $bulkGenerateRoute }}" class="inline" @submit="submitBulkGenerate($event)">
                @csrf
                <input type="hidden" name="tahun" value="{{ $tahun }}">
                <input type="hidden" name="bulan" value="{{ $bulan }}">
                @if($filterKelasId)
                    <input type="hidden" name="kelas_id" value="{{ $filterKelasId }}">
                @endif
                <template x-for="id in selected" :key="'gen-' + id">
                    <input type="hidden" name="anak_ids[]" :value="id">
                </template>
                <button type="submit" class="text-xs font-semibold px-3 py-2 rounded-lg text-white" style="background:#1A6B6B;" :disabled="selected.length === 0 || selected.length > {{ (int) $tokenBalance }}">
                    Generate Terpilih
                </button>
            </form>
            @elseif(!$aiReady)
                <span class="text-xs" style="color:#9E9790;">Generate terpilih membutuhkan pengaturan AI.</span>
            @elseif(!$hasTokens)
                <span class="text-xs" style="color:#DC2626;">Token AI habis.</span>
            @endif
            <form method="post" action="{{ $bulkResetRoute }}" class="inline" @submit="submitBulkReset($event)">
                @csrf
                <input type="hidden" name="tahun" value="{{ $tahun }}">
                <input type="hidden" name="bulan" value="{{ $bulan }}">
                <template x-for="id in selected" :key="'reset-' + id">
                    <input type="hidden" name="anak_ids[]" :value="id">
                </template>
                <button type="submit" class="text-xs font-semibold px-3 py-2 rounded-lg border" style="color:#C0392B; border-color:#F5C6C0; background:#FFF5F5;" :disabled="selected.length === 0">
                    Reset Terpilih
                </button>
            </form>
        </div>
    </div>

    @if($anaks->isEmpty())
        <div class="px-6 py-16 text-center text-sm" style="color:#9E9790;">
            @if($search)
                Tidak ada siswa yang cocok dengan pencarian "{{ $search }}".
            @else
                Belum ada siswa aktif dalam scope ini.
            @endif
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm" data-tour="{{ $tourPrefix }}-table">
                <thead>
                    <tr class="border-b text-left" style="border-color: rgba(0,0,0,0.06); background:#FAF6F0;">
                        <th class="px-4 py-3 w-10">
                            <input type="checkbox" class="h-4 w-4 rounded border-gray-300 cursor-pointer" style="accent-color:#1A6B6B;"
                                :checked="allSelected" @change="toggleAll($event.target.checked)" title="Pilih semua di halaman ini">
                        </th>
                        <th class="px-4 py-3 font-semibold" style="color:#6B6560;">Nama Siswa</th>
                        <th class="px-4 py-3 font-semibold" style="color:#6B6560;">Kelas</th>
                        <th class="px-4 py-3 font-semibold" style="color:#6B6560;">Status</th>
                        <th class="px-4 py-3 font-semibold" style="color:#6B6560;">Tanggal Generate</th>
                        <th class="px-4 py-3 font-semibold text-right" style="color:#6B6560;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($anaks as $anak)
                        @php $summary = $summaries->get($anak->id); @endphp
                        <tr class="border-b hover:bg-black/[0.02]" style="border-color: rgba(0,0,0,0.04);">
                            <td class="px-4 py-3">
                                <input type="checkbox" class="h-4 w-4 rounded border-gray-300 cursor-pointer" style="accent-color:#1A6B6B;"
                                    value="{{ $anak->id }}"
                                    :checked="selected.includes({{ $anak->id }})"
                                    @change="toggleOne({{ $anak->id }}, $event.target.checked)">
                            </td>
                            <td class="px-4 py-3 font-medium" style="color:#2C2C2C;">{{ $anak->name }}</td>
                            <td class="px-4 py-3" style="color:#6B6560;">{{ $anak->kelas?->name ?? '' }}</td>
                            <td class="px-4 py-3">
                                @if(!$summary)
                                    <span class="text-xs font-medium px-2 py-1 rounded" style="background:#F5F5F5; color:#9E9790;">Belum ada</span>
                                @elseif($summary->sumber === 'otomatis')
                                    <span class="text-xs font-medium px-2 py-1 rounded" style="background:#D0E8E8; color:#1A6B6B;">Otomatis</span>
                                @else
                                    <span class="text-xs font-medium px-2 py-1 rounded" style="background:#FDE9BC; color:#8A6D00;">Manual</span>
                                @endif
                            </td>
                            <td class="px-4 py-3" style="color:#6B6560;">
                                {{ $summary?->generated_at?->translatedFormat('d M Y H:i') ?? '' }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if($summary)
                                    <a href="{{ route($showRouteName, ['anak' => $anak->id, 'tahun' => $tahun, 'bulan' => $bulan]) }}"
                                       class="text-sm font-semibold" style="color:#1A6B6B;">Lihat</a>
                                @else
                                    <span class="text-xs" style="color:#C0BAB4;"></span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t" style="border-color: rgba(0,0,0,0.06);">
                <x-per-page-selector :paginator="$anaks" />
                {{ $anaks->links() }}
            </div>
    @endif
</div>

@once
@push('scripts')
<script>
window.monevBulkTable = function monevBulkTable() {
    const allIds = @json($anaks->pluck('id')->values());
    const tokenBalance = @json((int) ($tokenBalance ?? 0));

    return {
        selected: [],
        tokenBalance,
        get allSelected() {
            return allIds.length > 0 && this.selected.length === allIds.length;
        },
        toggleAll(checked) {
            this.selected = checked ? [...allIds] : [];
        },
        toggleOne(id, checked) {
            if (checked) {
                if (!this.selected.includes(id)) this.selected.push(id);
            } else {
                this.selected = this.selected.filter(i => i !== id);
            }
        },
        prepareSubmit(event) {
            if (this.selected.length === 0) {
                event.preventDefault();
                return false;
            }
            return true;
        },
        submitBulkGenerate(event) {
            if (!this.prepareSubmit(event)) {
                return;
            }
            if (this.selected.length > this.tokenBalance) {
                event.preventDefault();
                alert('Token AI tidak cukup. Dibutuhkan ' + this.selected.length + ' token, tersedia ' + this.tokenBalance + '.');
                return;
            }
            if (!confirm('Generate ringkasan untuk siswa terpilih? Proses berjalan di background.')) {
                event.preventDefault();
            }
        },
        submitBulkReset(event) {
            if (!this.prepareSubmit(event)) {
                return;
            }
            if (!confirm('Reset ringkasan siswa terpilih? Data summary akan dihapus.')) {
                event.preventDefault();
            }
        }
    };
};
</script>
@endpush
@endonce
