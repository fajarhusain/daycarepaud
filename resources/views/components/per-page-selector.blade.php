@props([
    'paginator',
    'param' => 'per_page',
    'label' => 'Tampilkan',
])

@php
    $current = (int) request()->query($param, $paginator->perPage());
    $pageName = method_exists($paginator, 'getPageName') ? $paginator->getPageName() : 'page';
@endphp

<div class="flex items-center justify-between gap-4 flex-wrap mb-4 pagination-info-wrapper">
    <div class="text-[11px] font-medium tracking-tight" style="color: #9E9790;">
        Menampilkan {{ $paginator->firstItem() ?? 0 }}-{{ $paginator->lastItem() ?? 0 }} dari {{ $paginator->total() }} data
    </div>
    <div class="flex items-center gap-3">
        <label for="{{ $param }}" class="text-[11px] font-medium" style="color: #6B6560;">{{ $label }}</label>
        <div class="relative inline-flex items-center group">
            <select
                id="{{ $param }}"
                data-per-page-selector="1"
                data-per-page-param="{{ $param }}"
                data-per-page-page-name="{{ $pageName }}"
                class="appearance-none bg-[#F5F0E8] border-none rounded-full pl-4 pr-10 py-1.5 text-xs font-bold text-[#2C2C2C] shadow-[inset_2px_2px_5px_rgba(0,0,0,0.05)] focus:ring-0 cursor-pointer min-w-[70px] transition-all hover:bg-[#EDE8DF]"
            >
                @foreach(\App\Support\PaginationPerPage::allowed() as $option)
                    <option value="{{ $option }}" @selected($current === $option)>{{ $option }}</option>
                @endforeach
            </select>
            <div class="absolute right-3 pointer-events-none text-[#9E9790] group-hover:text-[#1A6B6B] transition-colors">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </div>
        </div>
    </div>
</div>

<style>
    /* Sembunyikan summary bawaan Laravel (Showing X to Y...) secara agresif */
    .pagination-info-wrapper ~ nav div p.text-sm.text-gray-700,
    .pagination-info-wrapper ~ nav div.hidden.sm\:flex-1.sm\:flex.sm\:items-center.sm\:justify-between > div:first-child {
        display: none !important;
    }
    
    /* Pusatkan pagination buttons dan hapus border/padding berlebih */
    .pagination-info-wrapper ~ nav div.hidden.sm\:flex-1.sm\:flex.sm\:items-center.sm\:justify-between {
        justify-content: center !important;
        border: none !important;
        padding: 0 !important;
    }

    /* Sembunyikan summary di mobile */
    .pagination-info-wrapper ~ nav .flex.justify-between.flex-1.sm\:hidden p {
        display: none !important;
    }

    /* FIX TEMA GELAP: Paksa pagination links ke tema terang (cream/teal) */
    .pagination-info-wrapper ~ nav span[aria-current="page"] span {
        background-color: #1A6B6B !important;
        color: #ffffff !important;
        border-color: #1A6B6B !important;
    }

    .pagination-info-wrapper ~ nav a, 
    .pagination-info-wrapper ~ nav span {
        background-color: #FAF6F0 !important;
        color: #2C2C2C !important;
        border-color: rgba(0,0,0,0.08) !important;
    }

    .pagination-info-wrapper ~ nav a:hover {
        background-color: #EDE8DF !important;
        color: #1A6B6B !important;
    }

    /* Container navigasi */
    .pagination-info-wrapper ~ nav {
        background: transparent !important;
    }

    .pagination-info-wrapper ~ nav div.hidden.sm\:flex-1.sm\:flex.sm\:items-center.sm\:justify-between {
        background: transparent !important;
    }
    
    /* Hilangkan background gelap jika ada di container */
    .pagination-info-wrapper ~ nav .relative.z-0.inline-flex.shadow-sm.rounded-md,
    .pagination-info-wrapper ~ nav span.relative.z-0.inline-flex.rounded-md.shadow-sm,
    .pagination-info-wrapper ~ nav .flex.items-center.justify-between,
    .pagination-info-wrapper ~ nav .flex.justify-between.flex-1.sm\:hidden,
    .pagination-info-wrapper ~ nav div[class*="bg-"] {
        background: transparent !important;
        background-color: transparent !important;
        box-shadow: none !important;
        border: none !important;
    }
</style>
