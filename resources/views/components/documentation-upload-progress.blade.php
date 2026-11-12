<div x-show="docUploadActive"
     x-cloak
     class="mb-2 rounded-xl border border-teal-200/80 bg-teal-50/60 p-3 space-y-2"
     style="display: none;">
    <div class="flex items-center justify-between gap-2">
        <p class="text-xs font-semibold text-teal-900 leading-snug">Sedang mengupload media</p>
        <span class="text-[10px] font-bold text-teal-700 tabular-nums shrink-0" x-text="docUploadProgress + '%'"></span>
    </div>
    <div class="h-2 w-full rounded-full bg-teal-100/80 overflow-hidden">
        <div class="h-full rounded-full bg-teal-600 transition-[width] duration-200 ease-out"
             :style="'width:' + docUploadProgress + '%'"></div>
    </div>
</div>
