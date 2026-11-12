@props([
    'title' => 'Menu makan hari ini',
    'menu' => null,
    'nutrition' => null,
    'href' => null,
    'linkLabel' => 'Kelola jadwal',
    'emptyText' => 'Atur menu agar siswa dan orang tua bisa melihatnya.',
    'emptyTitle' => 'Belum ada menu hari ini',
    'emptyActionLabel' => 'Atur menu',
])

@php
    use App\Support\DashboardCompactMenu;
    $menuItems = DashboardCompactMenu::items(is_string($menu) ? $menu : null);
    $isEmpty = $menuItems === [];
    $nutritionTrimmed = is_string($nutrition) ? trim($nutrition) : '';
    if ($nutritionTrimmed !== '' && mb_strlen($nutritionTrimmed) < 3) {
        $nutritionTrimmed = '';
    }
    $headTitle = $isEmpty ? $emptyTitle : $title;
@endphp

<div {{ $attributes->merge(['class' => 'card dashboard-compact-card']) }}>
    <div class="dashboard-compact-head dashboard-compact-head--solo">
        <h3 class="section-title">{{ $headTitle }}</h3>
    </div>
    <div class="dashboard-compact-body">
        @if($isEmpty)
            <div class="dashboard-compact-empty">
                <p class="dashboard-compact-meta m-0 text-sm leading-snug">{{ $emptyText }}</p>
                @if($href)
                    <a href="{{ $href }}" class="btn-primary dashboard-compact-cta">{{ $emptyActionLabel }}</a>
                @endif
            </div>
        @else
            <ul class="dashboard-menu-items">
                @foreach($menuItems as $item)
                    <li class="dashboard-menu-item">{{ $item }}</li>
                @endforeach
            </ul>
            @if($nutritionTrimmed !== '')
                <p class="dashboard-compact-meta m-0">{{ $nutritionTrimmed }}</p>
            @endif
            @if($href)
                <div class="dashboard-compact-foot">
                    <a href="{{ $href }}" class="dashboard-compact-link">{{ $linkLabel }}</a>
                </div>
            @endif
        @endif
    </div>
</div>
