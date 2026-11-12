@props(['pengumumans', 'autoOpen' => false])

@php
    $items = collect($pengumumans)->map(fn ($p) => [
        'id' => $p->id,
        'judul' => $p->judul,
        'kategori' => $p->kategori,
        'isi' => $p->isi,
        'mulai_tayang' => $p->mulai_tayang?->translatedFormat('d M Y'),
        'gambar_url' => $p->gambar ? Storage::url($p->gambar) : null,
        'baca_url' => route('orangtua.pengumuman.baca', $p),
    ])->values();
@endphp

@if($items->isNotEmpty())
<div x-data="{
    pengumumans: @js($items),
    currentIndex: 0,
    showPengumumanModal: false,
    showImageModal: false,
    activeImage: null,
    marking: false,
    get current() { return this.pengumumans[this.currentIndex] || null; },
    init() {
        if (@js($autoOpen) && this.pengumumans.length > 0) {
            this.showPengumumanModal = true;
        }
    },
    openAt(index) {
        this.currentIndex = index;
        this.showPengumumanModal = true;
    },
    async tutup() {
        if (!this.current || this.marking) return;
        this.marking = true;
        try {
            const res = await fetch(this.current.baca_url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '',
                    'Accept': 'application/json',
                },
            });
            if (!res.ok) throw new Error('Gagal menandai dibaca');
            if (this.currentIndex < this.pengumumans.length - 1) {
                this.currentIndex++;
            } else {
                this.showPengumumanModal = false;
            }
        } catch (e) {
            console.error(e);
            this.showPengumumanModal = false;
        } finally {
            this.marking = false;
        }
    }
}" @open-pengumuman.window="openAt($event.detail.index)" @tour-close-modals.window="showPengumumanModal=false; showImageModal=false">

    <div x-show="showPengumumanModal" class="modal-overlay modal-overlay--elevated" style="display:none;" x-cloak>
        <div x-show="showPengumumanModal" x-transition class="modal-box max-w-lg w-full mx-4 !p-0 flex flex-col max-h-[min(90dvh,calc(100dvh-2rem))]" @click.away="">
            <template x-if="current">
                <div class="flex flex-col min-h-0 flex-1">
                    <div class="relative shrink-0" :class="current.gambar_url ? '' : 'pt-12'">
                        <div x-show="current.gambar_url"
                             class="aspect-[16/10] bg-gray-100 overflow-hidden cursor-pointer"
                             @click="activeImage = current.gambar_url; showImageModal = true">
                            <img :src="current.gambar_url" class="w-full h-full object-cover" alt="Gambar pengumuman">
                        </div>

                        <div class="absolute top-3 right-3 z-20 flex items-center gap-2">
                            <span x-show="pengumumans.length > 1"
                                  class="text-xs font-semibold px-2 py-1 rounded-lg"
                                  :class="current.gambar_url ? 'bg-black/40 text-white backdrop-blur-sm' : 'bg-gray-100 text-gray-500'">
                                <span x-text="currentIndex + 1"></span>/<span x-text="pengumumans.length"></span>
                            </span>
                            <button type="button"
                                    @click="showPengumumanModal = false"
                                    class="h-8 w-8 rounded-full flex items-center justify-center transition"
                                    :class="current.gambar_url ? 'bg-black/40 text-white backdrop-blur-sm hover:bg-black/60' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'"
                                    aria-label="Tutup">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="flex-1 min-h-0 overflow-y-auto px-5 py-4 space-y-2.5">
                        <p x-show="current.mulai_tayang" class="text-[11px] font-semibold uppercase tracking-wider" style="color:#9E9790;" x-text="current.mulai_tayang"></p>
                        <h3 class="text-lg font-bold leading-snug pr-8" style="color:#2C2C2C;" x-text="current.judul"></h3>
                        <span class="inline-block text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full" style="background:#E8F4F4;color:#1A6B6B;" x-text="current.kategori"></span>
                        <p class="text-sm leading-relaxed whitespace-pre-wrap pb-1" style="color:#4A4540;" x-text="current.isi"></p>
                    </div>

                    <div class="px-5 py-4 border-t shrink-0" style="border-color:rgba(0,0,0,0.06);">
                        <button type="button" @click="tutup()" :disabled="marking" class="btn-primary w-full justify-center">
                            <span x-text="marking ? 'Menyimpan...' : 'Tandai sudah dibaca'"></span>
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <div x-show="showImageModal"
         class="modal-overlay modal-overlay--elevated modal-overlay--dark"
         style="display:none;"
         x-transition
         @keydown.escape.window="showImageModal = false">
        <div class="relative max-w-4xl w-full px-4" @click.away="showImageModal = false">
            <button type="button" class="absolute -top-10 right-4 text-white hover:text-gray-300" @click="showImageModal = false">
                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
            <img :src="activeImage" class="w-full h-auto max-h-[85vh] object-contain rounded-2xl shadow-2xl bg-white shadow-black/20" alt="Gambar pengumuman">
        </div>
    </div>
</div>
@endif
