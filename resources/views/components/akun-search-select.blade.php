@props([
    'name',
    'options',
    'value' => '',
    'required' => false,
    'placeholder' => 'Ketik kode atau nama akun…',
    'syncField' => null,
])

@php
    $optionsJson = collect($options)->map(function ($a) {
        $nama = (string) ($a->nama ?? '');

        return [
            'id' => (string) $a->id,
            'label' => $a->kode.'  '.$nama,
            'search' => strtolower($a->kode.' '.$nama.' '.($a->uraian ?? '').' '.($a->snp ?? '').' '.($a->komponen ?? '')),
        ];
    })->values()->all();
    $initial = (string) old($name, $value);
@endphp

<div
    x-data="{
        selected: @js($initial),
        q: '',
        open: false,
        pos: { top: 0, left: 0, width: 0 },
        options: @js($optionsJson),
        labelFor(id) {
            const o = this.options.find(x => x.id === String(id));
            return o ? o.label : '';
        },
        syncQ() { this.q = this.selected ? this.labelFor(this.selected) : ''; },
        get filteredOptions() {
            const t = this.q.toLowerCase().trim();
            if (!t || t === this.labelFor(this.selected).toLowerCase()) return this.options;
            return this.options.filter(o => o.search.includes(t) || o.label.toLowerCase().includes(t));
        },
        pick(id) {
            this.selected = String(id);
            this.syncQ();
            this.open = false;
        },
        onInput() {
            this.open = true;
            this.updatePosition();
            if (this.q !== this.labelFor(this.selected)) this.selected = '';
        },
        updatePosition() {
            const el = this.$refs.searchInput;
            if (!el) return;
            const r = el.getBoundingClientRect();
            this.pos = { top: r.bottom + 4, left: r.left, width: r.width };
        },
        setFromDetail(detail) {
            if (!detail) return;
            const id = (detail.id === null || detail.id === undefined || detail.id === '') ? '' : String(detail.id);
            this.selected = id;
            this.q = detail.label || this.labelFor(id);
        },
    }"
    x-init="syncQ()"
    @if($syncField)
    @akun-search-sync.window="
        if ($event.detail?.field !== @js($syncField)) return;
        setFromDetail($event.detail);
    "
    @endif
    class="relative"
    @click.outside="open = false"
    {{ $attributes->except(['class']) }}
>
    <input type="hidden" name="{{ $name }}" :value="selected" @if($required) required @endif>
    <input
        type="text"
        x-ref="searchInput"
        class="input-field {{ $attributes->get('class') }}"
        x-model="q"
        @focus="updatePosition(); open = true"
        @input="onInput()"
        placeholder="{{ $placeholder }}"
        autocomplete="off"
        role="combobox"
        aria-autocomplete="list"
        :aria-expanded="open"
    >
    @if(count($optionsJson) === 0)
        <p class="text-xs mt-1" style="color:#9E9790;">Belum ada akun. Cek Kode Rekening.</p>
    @endif
    <template x-teleport="body">
        <ul
            x-show="open && filteredOptions.length"
            x-cloak
            x-transition.opacity.duration.100ms
            :style="`position:fixed;top:${pos.top}px;left:${pos.left}px;width:${pos.width}px;z-index:9999`"
            class="max-h-60 overflow-y-auto rounded-lg border bg-white shadow-lg text-sm list-none p-1"
            style="border-color:rgba(0,0,0,0.1);"
            role="listbox"
            @mousedown.stop
            @click.stop
        >
            <template x-for="opt in filteredOptions" :key="opt.id">
                <li
                    class="px-3 py-2 rounded-md cursor-pointer hover:bg-teal-50"
                    :class="selected === opt.id && 'bg-teal-50 font-medium'"
                    role="option"
                    @mousedown.prevent.stop="pick(opt.id)"
                    x-text="opt.label"
                ></li>
            </template>
        </ul>
    </template>
    <p x-show="open && q.trim() && filteredOptions.length === 0" class="text-xs mt-1 px-1" style="color:#9E9790;">Tidak ada akun yang cocok.</p>
</div>
