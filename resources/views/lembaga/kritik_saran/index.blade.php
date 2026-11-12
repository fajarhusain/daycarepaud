<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background: #1A6B6B;"><svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" /></svg></div>
            <h2 class="font-bold text-xl" style="color: #2C2C2C;">Kritik & Saran</h2>
        </div>
    </x-slot>
    <div class="app-page-shell">
        @if(session('success'))<div class="alert-success mb-5"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert-danger mb-5"><ul class="list-disc pl-5 text-sm">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul></div>@endif
        <div class="card overflow-hidden">
            <div class="px-6 py-4 border-b" style="border-color:rgba(0,0,0,0.06);">
                <h3 class="section-title">Kotak Masukan</h3>
                <p class="section-subtitle">Semua kritik dan saran yang diterima dari orang tua dan masyarakat</p>
            </div>
            <div class="divide-y" data-tour="lembaga-kritik-list" style="divide-color:rgba(0,0,0,0.05);">
                @forelse($feedbacks as $fb)
                <div class="px-6 py-5">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="h-8 w-8 rounded-xl flex items-center justify-center text-sm font-bold text-white shrink-0" style="background:#1A6B6B;">{{ substr($fb->user?->name ?? $fb->nama_bapak ?? 'A', 0, 1) }}</div>
                                <div>
                                    <span class="font-semibold text-sm" style="color:#2C2C2C;">{{ $fb->user?->name ?? $fb->nama_bapak ?? 'Anonim' }}</span>
                                    <span class="text-xs ml-2" style="color:#9E9790;">{{ \Carbon\Carbon::parse($fb->created_at)->diffForHumans() }}</span>
                                </div>
                            </div>
                            <p class="text-sm leading-relaxed pl-11" style="color:#6B6560;">"{{ $fb->message }}"</p>
                        </div>
                        <span class="badge badge-teal shrink-0">{{ $fb->status ?? 'Masukan' }}</span>
                    </div>
                </div>
                @empty
                <div class="px-6 py-16 text-center">
                    <div class="h-14 w-14 rounded-2xl mx-auto mb-4 flex items-center justify-center" style="background:#EDE8DF;"><svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color:#9E9790;"><path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" /></svg></div>
                    <p class="text-sm" style="color:#9E9790;">Belum ada kritik atau saran masuk.</p>
                </div>
                @endforelse
            </div>
            <div class="px-6 py-4 border-t" style="border-color:rgba(0,0,0,0.06);">
                <x-per-page-selector :paginator="$feedbacks" />
                {{ $feedbacks->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
