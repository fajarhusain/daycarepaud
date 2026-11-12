@if($activeGeneration && !$activeGeneration->isFinished())
<div
    x-data="window.monevProgress({
        generationId: @js($activeGeneration->id),
        statusUrl: @js($statusRoute),
        initial: @js([
            'total' => $activeGeneration->total,
            'processed' => $activeGeneration->processed(),
            'percent' => $activeGeneration->progressPercent(),
            'status' => $activeGeneration->status,
            'failed' => $activeGeneration->failed,
        ])
    })"
    x-init="start()"
    class="card overflow-hidden mb-6 border-2"
    style="border-color:#1A6B6B; background:#F8FDFD;"
>
    <div class="px-6 py-5">
        <div class="flex items-center justify-between gap-4 mb-3">
            <div>
                <h4 class="font-bold text-sm" style="color:#1A6B6B;">Sedang generate ringkasan AI...</h4>
                <p class="text-xs mt-0.5" style="color:#6B6560;">Proses berjalan di background. Anda bisa tetap di halaman ini.</p>
            </div>
            <span class="text-lg font-bold tabular-nums" style="color:#1A6B6B;" x-text="processed + '/' + total">0/0</span>
        </div>

        <div class="relative w-full h-4 rounded-full overflow-hidden" style="background:#D0E8E8;">
            <div
                class="absolute top-0 left-0 h-full rounded-full transition-[width] duration-500 ease-out"
                :style="{
                    width: percent + '%',
                    minWidth: percent > 0 ? '0.5rem' : '0',
                    background: 'linear-gradient(90deg, #1A6B6B, #2D9B9B)',
                }"
            ></div>
        </div>

        <p class="text-xs mt-2 font-medium" style="color:#6B6560;">
            <span x-text="percent"></span>% selesai
            <span x-show="failed > 0" class="text-red-600" x-text="' · ' + failed + ' gagal'"></span>
        </p>
    </div>
</div>

@once
@push('scripts')
<script>
window.monevProgress = function monevProgress({ generationId, statusUrl, initial }) {
    return {
        generationId,
        statusUrl,
        total: initial.total,
        processed: initial.processed,
        completed: 0,
        skipped: 0,
        failed: initial.failed ?? 0,
        percent: Number(initial.percent) || 0,
        status: initial.status,
        pollTimer: null,
        start() {
            this.poll();
            this.pollTimer = setInterval(() => this.poll(), 2000);
        },
        async poll() {
            try {
                const res = await fetch(this.statusUrl, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                });
                if (!res.ok) {
                    return;
                }
                const data = await res.json();
                this.total = data.total;
                this.processed = data.processed;
                this.completed = data.completed;
                this.skipped = data.skipped;
                this.failed = data.failed;
                this.percent = Number(data.percent) || 0;
                this.status = data.status;
                if (data.is_finished) {
                    clearInterval(this.pollTimer);
                    setTimeout(() => window.location.reload(), 800);
                }
            } catch (e) {
                console.error('Monev progress poll failed', e);
            }
        },
    };
};
</script>
@endpush
@endonce
@endif
