@php
    /** @var int $slotNumber */
    /** @var \App\Models\AiProviderSlot|null $slot */
    /** @var string $title */
    /** @var string $subtitle */
    /** @var bool $required */
    $slot = $slot ?? null;
    $old = old('slots.'.$slotNumber, []);
    $provider = $old['ai_provider'] ?? $slot?->ai_provider ?? ($required ? 'sumopod' : '');
    $model = $old['ai_model'] ?? $slot?->ai_model ?? ($required ? 'gpt-4o-mini' : '');
    $baseUrl = $old['ai_base_url'] ?? $slot?->ai_base_url ?? '';
    $enabled = array_key_exists('is_enabled', $old)
        ? (bool) $old['is_enabled']
        : (bool) ($slot?->is_enabled ?? $required);
@endphp

<div class="rounded-2xl border overflow-hidden"
    style="border-color:rgba(0,0,0,0.08);"
    x-data="{
        providers: @js($providers),
        selectedProvider: @js($provider),
        enabled: @js($required ? true : $enabled),
        onProviderChange() {
            const preset = this.providers[this.selectedProvider];
            if (!preset?.default_model) return;
            const modelInput = $refs.modelInput;
            if (!modelInput || modelInput.dataset.userEdited === '1') return;
            modelInput.value = preset.default_model;
        },
        providerHint() {
            return this.providers[this.selectedProvider]?.hint ?? '';
        }
    }">
    <div class="px-5 py-4 flex flex-wrap items-center justify-between gap-3" style="background:#FAF6F0; border-bottom:1px solid rgba(0,0,0,0.06);">
        <div>
            <div class="font-bold text-sm" style="color:#2C2C2C;">{{ $title }}</div>
            <div class="text-xs mt-0.5" style="color:#9E9790;">{{ $subtitle }}</div>
        </div>
        @if(! $required)
            <label class="inline-flex items-center gap-2 text-xs font-semibold cursor-pointer" style="color:#1A6B6B;">
                <input type="hidden" name="slots[{{ $slotNumber }}][is_enabled]" value="0">
                <input type="checkbox" name="slots[{{ $slotNumber }}][is_enabled]" value="1"
                    x-model="enabled"
                    class="rounded border-gray-300">
                Aktifkan slot
            </label>
        @else
            <input type="hidden" name="slots[{{ $slotNumber }}][is_enabled]" value="1">
            <span class="text-[10px] font-bold uppercase tracking-wide px-2 py-1 rounded-lg" style="background:#D0E8E8; color:#1A6B6B;">Wajib</span>
        @endif
    </div>

    <div class="px-5 py-5 space-y-4" x-show="enabled" x-cloak>
        <div>
            <label class="input-label" for="slot_{{ $slotNumber }}_provider">Provider AI</label>
            <select id="slot_{{ $slotNumber }}_provider"
                name="slots[{{ $slotNumber }}][ai_provider]"
                class="input-field @error('slots.'.$slotNumber.'.ai_provider') border-red-500 @enderror"
                x-model="selectedProvider"
                @change="onProviderChange()"
                @if($required) required @endif>
                @unless($required)
                    <option value=""> Pilih provider </option>
                @endunless
                @foreach($providers as $key => $providerMeta)
                    <option value="{{ $key }}" @selected($provider === $key)>{{ $providerMeta['label'] }}</option>
                @endforeach
            </select>
            @error('slots.'.$slotNumber.'.ai_provider')
                <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>
            @enderror
            <p class="text-[11px] mt-1" style="color:#9E9790;" x-text="providerHint()"></p>
        </div>

        <div x-show="selectedProvider === 'custom'" x-cloak>
            <label class="input-label" for="slot_{{ $slotNumber }}_base_url">Base URL</label>
            <input type="url" id="slot_{{ $slotNumber }}_base_url"
                name="slots[{{ $slotNumber }}][ai_base_url]"
                class="input-field @error('slots.'.$slotNumber.'.ai_base_url') border-red-500 @enderror"
                value="{{ $baseUrl }}"
                placeholder="https://api.example.com/v1"
                :required="enabled && selectedProvider === 'custom'">
            @error('slots.'.$slotNumber.'.ai_base_url')
                <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>
            @enderror
            <p class="text-[11px] mt-1" style="color:#9E9790;">Endpoint OpenAI-compatible, tanpa trailing slash.</p>
        </div>

        <div>
            <label class="input-label" for="slot_{{ $slotNumber }}_api_key">API Key</label>
            <input type="password" id="slot_{{ $slotNumber }}_api_key"
                name="slots[{{ $slotNumber }}][ai_api_key]"
                class="input-field @error('slots.'.$slotNumber.'.ai_api_key') border-red-500 @enderror"
                placeholder="{{ $slot?->apiKeyNeedsReentry() ? 'Masukkan ulang API Key' : ($slot?->hasValidApiKey() ? '••••••••••••••••••• (terisi  kosongkan jika tidak ingin mengubah)' : 'Masukkan API Key') }}"
                autocomplete="new-password">
            @error('slots.'.$slotNumber.'.ai_api_key')
                <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>
            @enderror
            <p class="text-[11px] mt-1" style="color:#9E9790;">Disimpan terenkripsi. Kosongkan jika tidak ingin mengubah key tersimpan.</p>
        </div>

        <div>
            <label class="input-label" for="slot_{{ $slotNumber }}_model">Nama Model AI</label>
            <input type="text" id="slot_{{ $slotNumber }}_model"
                name="slots[{{ $slotNumber }}][ai_model]"
                x-ref="modelInput"
                class="input-field @error('slots.'.$slotNumber.'.ai_model') border-red-500 @enderror"
                value="{{ $model }}"
                placeholder="gpt-4o-mini"
                @input="$event.target.dataset.userEdited = '1'"
                @if($required) required @endif>
            @error('slots.'.$slotNumber.'.ai_model')
                <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>
