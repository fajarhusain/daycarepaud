@props(['charts' => []])

@if(count($charts) > 0)
    <section class="dashboard-chart-grid" data-tour="dashboard-charts" aria-label="Grafik ringkasan">
        @foreach($charts as $chart)
            <x-dashboard.chart-card
                :chart="$chart"
                :class="($loop->last && $loop->count % 2 === 1) ? 'lg:col-span-2 lg:max-w-3xl lg:mx-auto lg:w-full' : ''"
            />
        @endforeach
    </section>
@endif
