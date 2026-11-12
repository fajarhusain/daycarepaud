<template x-teleport="body">
<div x-show="showImageModal"
     class="modal-overlay modal-overlay--elevated modal-overlay--dark"
     style="display: none;"
     x-transition
     @keydown.escape.window="showImageModal = false; activeDownloadUrl = null; activeMediaType = 'image'">
    <div class="relative max-w-4xl w-full" @click.away="showImageModal = false; activeDownloadUrl = null; activeMediaType = 'image'">
        <div class="absolute -top-12 right-0 flex items-center gap-3">
            <a x-show="activeDownloadUrl || activeImage"
               :href="activeDownloadUrl || activeImage"
               :download="activeDownloadUrl ? null : true"
               class="h-10 w-10 rounded-full bg-white/15 hover:bg-white/25 flex items-center justify-center text-white transition border border-white/20"
               title="Unduh"
               @click.stop>
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
            </a>
            <button type="button" class="text-white hover:text-gray-300 transition flex items-center gap-2" @click="showImageModal = false; activeDownloadUrl = null; activeMediaType = 'image'">
                <span class="text-xs font-bold uppercase tracking-widest text-white/50 hidden sm:inline">Klik di mana saja untuk tutup</span>
                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <template x-if="activeMediaType === 'video'">
            <video :src="activeImage" class="w-full h-auto max-h-[85vh] rounded-2xl shadow-2xl bg-black shadow-black/20" controls playsinline></video>
        </template>
        <template x-if="activeMediaType !== 'video'">
            <img :src="activeImage" class="w-full h-auto max-h-[85vh] object-contain rounded-2xl shadow-2xl bg-white shadow-black/20">
        </template>
    </div>
</div>
</template>
