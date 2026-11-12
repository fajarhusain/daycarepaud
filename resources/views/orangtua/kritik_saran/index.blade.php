<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background: #1A6B6B;">
                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                </svg>
            </div>
            <h2 class="font-bold text-xl" style="color: #2C2C2C;">Saran & Kritik</h2>
        </div>
    </x-slot>

    <div class="app-page-shell"
         x-data="{ 
            showModal: {{ $errors->any() ? 'true' : 'false' }},
            showImageModal: false,
            activeImage: null,
            activeDownloadUrl: null,
            isEdit: false,
            editId: '',
            messageValue: '',
            initCreate() {
                this.isEdit = false;
                this.editId = '';
                this.messageValue = '';
                this.showModal = true;
            },
            initEdit(id, msg) {
                this.isEdit = true;
                this.editId = id;
                this.messageValue = msg;
                this.showModal = true;
            }
         }" @tour-close-modals.window="showModal=false">
        @if(session('success'))<div class="alert-success mb-5"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>{{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert-danger mb-5"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>{{ session('error') }}</div>@endif
        @if($errors->any())<div class="alert-danger mb-5"><ul class="list-disc pl-5 text-sm">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul></div>@endif

        <div class="mb-6 page-toolbar">
            
            <button type="button" data-tour="ortu-kritik-add-btn" data-tour-open-modal="create" @click="initCreate()" class="btn-primary text-sm inline-flex items-center gap-1.5">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                Kirim masukan baru
            </button>
        </div>

        <div class="space-y-5" data-tour="ortu-kritik-feed">
            @forelse($feedbacks as $fb)
                <article class="card overflow-hidden">
                    <div class="card-pad border-b page-toolbar" style="border-color:rgba(0,0,0,0.06);">
                        <div class="flex items-center gap-3">
                            <div class="shrink-0">
                                @if($fb->photo)
                                    <div class="h-10 w-10 rounded-lg overflow-hidden border border-gray-100 shadow-sm cursor-pointer" @click="activeImage = '{{ Storage::url($fb->photo) }}'; activeDownloadUrl = '{{ route('orangtua.kritik-saran.photo.download', $fb) }}'; showImageModal = true">
                                        <img src="{{ Storage::url($fb->photo) }}" class="h-full w-full object-cover">
                                    </div>
                                @else
                                    <div class="h-10 w-10 rounded-lg bg-gray-50 flex items-center justify-center text-gray-300">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    </div>
                                @endif
                            </div>
                            <div>
                                <p class="text-xs font-semibold" style="color:#9E9790;">{{ $fb->created_at->format('d M Y, H:i') }}</p>
                                <span class="badge badge-teal mt-0.5">{{ $fb->status ?? '' }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            @if($fb->status === 'Terkirim')
                                <button type="button" @if($loop->first) data-tour="ortu-kritik-action-edit" data-tour-open-modal="edit" @endif @click="initEdit('{{ $fb->id }}', '{{ addslashes($fb->message) }}')" class="h-8 w-8 rounded-lg bg-gray-50 flex items-center justify-center text-gray-400 hover:text-[#1A6B6B] hover:bg-[#D0E8E8] transition">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                </button>
                                <form action="{{ route('orangtua.kritik-saran.destroy', $fb) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pesan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" @if($loop->first) data-tour="ortu-kritik-action-delete" data-tour-demo-action="delete" @endif class="h-8 w-8 rounded-lg bg-gray-50 flex items-center justify-center text-gray-400 hover:text-red-600 hover:bg-red-50 transition">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </form>
                            @endif
                            <a href="{{ route('orangtua.kritik-saran.show', $fb) }}" class="row-action-icon" style="color:#1A6B6B;background:#D0E8E8;" title="Detail" aria-label="Detail"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></a>
                        </div>
                    </div>
                    <div class="card-body-pad">
                        <h3 class="text-xs font-bold uppercase tracking-wide mb-1.5" style="color:#6B6560;">Pesan Anda</h3>
                        <p class="text-sm leading-relaxed line-clamp-3" style="color:#2C2C2C;">{{ $fb->message }}</p>
                    </div>
                    @if(filled($fb->umpan_balik))
                        <div class="mx-5 mb-5 rounded-xl px-4 py-4 border" style="border-color:rgba(26,107,107,0.25); background:#F0FAFA;">
                            <h3 class="text-xs font-bold uppercase tracking-wide mb-2 flex items-center gap-1.5" style="color:#1A6B6B;">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" /></svg>
                                Tanggapan sekolah
                            </h3>
                            <p class="text-sm leading-relaxed whitespace-pre-wrap" style="color:#2C2C2C;">{{ $fb->umpan_balik }}</p>
                        </div>
                    @else
                        <div class="px-5 pb-4">
                            <p class="text-xs italic rounded-lg px-3 py-2" style="color:#9E9790;background:#FAF6F0;">Belum ada tanggapan dari sekolah.</p>
                        </div>
                    @endif
                </article>
            @empty
                <div class="card px-6 py-14 text-center text-sm" style="color:#9E9790;">
                    Belum ada masukan. Klik &ldquo;Kirim masukan baru&rdquo; untuk mengirim saran atau kritik pertama Anda.
                </div>
            @endforelse
        </div>

        <div class="mt-6">
                <x-per-page-selector :paginator="$feedbacks" />
                {{ $feedbacks->links() }}
            </div>

        <!-- Modal kirim/edit masukan -->
        <template x-teleport="body">
        <div x-show="showModal" class="modal-overlay modal-overlay--blur" style="display:none;">
            <div x-show="showModal" x-transition class="modal-box max-w-lg w-full" @click.away="showModal = false">
                <form method="POST" :action="isEdit ? '/orangtua/kritik-saran/' + editId : '{{ route('orangtua.kritik-saran.store') }}'" enctype="multipart/form-data">
                    @csrf
                    <template x-if="isEdit">
                        @method('PATCH')
                    </template>
                    <div class="modal-header">
                        <h3 class="section-title" x-text="isEdit ? 'Edit saran atau kritik' : 'Kirim saran atau kritik'"></h3>
                        <p class="section-subtitle mt-1">Pesan minimal 10 karakter akan dikirim ke pihak sekolah.</p>
                    </div>
                    <div class="modal-body space-y-4">
                        <div class="text-[13px] font-bold text-[#1A6B6B] mt-1 border-b pb-1" :data-tour="isEdit ? 'modal-edit-section-form' : 'modal-create-section-form'">Isi Pesan</div>
                        <div>
                            <label class="input-label">Pesan <span class="text-red-600">*</span></label>
                            <textarea name="message" x-model="messageValue" rows="5" class="input-field" placeholder="Tuliskan pesan Anda di sini..." required minlength="10"></textarea>
                            @error('message')
                                <p class="text-xs mt-1" style="color:#C0392B;">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="input-label">Lampiran Foto (Opsional)</label>
                            <input type="file" name="photo" class="input-field text-xs pt-2" accept="image/*">
                            <p class="text-[10px] text-gray-400 mt-1">Maks. 2MB. Format: JPG, PNG.</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" @click="showModal = false" class="btn-secondary">Batal</button>
                        <button type="submit" :data-tour="isEdit ? 'modal-edit-submit' : 'modal-create-submit'" class="btn-primary" x-text="isEdit ? 'Simpan Perbaruan' : 'Kirim Masukan'"></button>
                    </div>
                </form>
            </div>
        </div>
        </template>

        <x-image-lightbox />
    </div>
</x-app-layout>
