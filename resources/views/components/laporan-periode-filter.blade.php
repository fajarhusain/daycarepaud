@props(['action', 'periode', 'resetHref' => null])

<form method="GET" action="{{ $action }}" class="card p-4 mb-6 flex flex-wrap gap-4 items-end" x-data="{ tipe: '{{ $periode['tipe'] }}' }">
    <div>
        <label class="input-label">Periode</label>
        <div class="flex flex-wrap gap-3 mt-1">
            <label class="inline-flex items-center gap-2 text-sm">
                <input type="radio" name="tipe" value="bulanan" x-model="tipe">
                Bulanan
            </label>
            <label class="inline-flex items-center gap-2 text-sm">
                <input type="radio" name="tipe" value="tahunan" x-model="tipe">
                Tahunan
            </label>
        </div>
    </div>
    <div x-show="tipe === 'bulanan'" x-cloak>
        <label class="input-label">Bulan</label>
        <select name="bulan" class="input-field w-40">
            @foreach(range(1, 12) as $m)
                <option value="{{ $m }}" @selected($periode['bulan'] == $m)>
                    {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="input-label">Tahun</label>
        <select name="tahun" class="input-field w-28">
            @foreach(range(now()->year - 2, now()->year + 1) as $y)
                <option value="{{ $y }}" @selected($periode['tahun'] == $y)>{{ $y }}</option>
            @endforeach
        </select>
    </div>
    {{ $slot }}
    <x-toolbar.filter-icon label="Tampilkan" />
    <x-filter-reset :href="$resetHref ?? $action" />
</form>
