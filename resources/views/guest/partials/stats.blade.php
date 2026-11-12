@php use App\Support\GuestFeatures; @endphp
<section class="py-10 border-y" style="border-color: var(--guest-border); background: #fff;">
    <div class="max-w-6xl mx-auto px-4 sm:px-6">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-8" data-guest-stagger>
            @foreach(GuestFeatures::stats() as $stat)
            <div class="text-center" data-guest-stagger-item>
                <p class="guest-stat-value">{{ $stat['value'] }}</p>
                <p class="mt-1 text-sm font-semibold text-[var(--guest-text-muted)]">{{ $stat['label'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>
