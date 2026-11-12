@props([])

@php
    $tabs = \App\Support\AdminSettingsNav::tabsFor(auth()->user());
@endphp

@if(count($tabs) > 0)
    <div {{ $attributes->merge(['class' => 'card p-4 mb-5']) }}>
        <div class="flex flex-wrap gap-2">
            @foreach($tabs as $tab)
                <a href="{{ route($tab['route']) }}"
                   class="px-4 py-2 rounded-xl text-sm font-semibold border transition-all {{ $tab['active'] ? 'text-white border-transparent shadow-md' : 'text-[#6B6560] border-black/10 bg-white hover:bg-black/5' }}"
                   @if($tab['active']) style="background:#1A6B6B" @endif>
                    {{ $tab['label'] }}
                </a>
            @endforeach
        </div>
    </div>
@endif
