<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background: #1A6B6B;"><svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.701 2.701 0 00-1.5-.454M9 6v2m3-2v2m3-2v2M9 3h.01M12 3h.01M15 3h.01M21 21v-7a2 2 0 00-2-2H5a2 2 0 00-2 2v7h18zm-3-9v-2a2 2 0 00-2-2H8a2 2 0 00-2 2v2h12z" /></svg></div>
            <h2 class="font-bold text-xl" style="color: #2C2C2C;">Jadwal Menu Makanan</h2>
        </div>
    </x-slot>
    <div class="app-page-shell" 
         x-data="{ showImageModal: false, activeImage: null, activeDownloadUrl: null }">

        <div class="card mb-6">
            <div class="card-pad border-b" style="background:#FAF6F0; border-color: rgba(0,0,0,0.06);">
                <div class="space-y-4 md:space-y-6">
                    <div class="space-y-1">
                        <h3 class="text-base sm:text-xl font-bold" style="color:#2C2C2C;">Filter Menu Makanan</h3>
                    </div>
                    
                    <form data-tour="ortu-menu-date-filter" method="get" action="{{ route('orangtua.menu-makanan.index') }}" class="filter-toolbar-inline">
                        <div class="filter-toolbar-field">
                            <label class="text-[11px] font-bold uppercase tracking-wider mb-1.5 block" style="color:#1A6B6B;">Tanggal Dari</label>
                            <input type="date" name="start_date" value="{{ $startDate }}" class="input-field w-full h-9 md:h-11 text-xs font-bold border-black/10 transition focus:border-teal-500" required style="background:white;">
                        </div>
                        <div class="filter-toolbar-field">
                            <label class="text-[11px] font-bold uppercase tracking-wider mb-1.5 block" style="color:#1A6B6B;">Sampai Dengan</label>
                            <input type="date" name="end_date" value="{{ $endDate }}" class="input-field w-full h-9 md:h-11 text-xs font-bold border-black/10 transition focus:border-teal-500" required style="background:white;">
                        </div>
                        <div class="filter-toolbar-actions">
                            <button type="submit" class="btn-primary h-9 w-9 md:h-11 md:w-11 p-0 shrink-0" title="Tampilkan Menu" aria-label="Tampilkan Menu">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                            </button>
                            <x-filter-reset :href="route('orangtua.menu-makanan.index')" compact />
                        </div>
                    </form>
                </div>
            </div>
            <div class="px-5 sm:px-6 py-3 border-t text-sm flex flex-wrap items-center gap-x-2 gap-y-1" style="background:#FAF6F0; border-color:rgba(0,0,0,0.04); color:#6B6560;">
                <span class="inline-flex items-center gap-1.5 shrink-0">
                    <svg class="h-4 w-4 shrink-0 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Rentang Jadwal:</span>
                </span>
                <span style="color:#2C2C2C;">
                    {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d M Y') }} – {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d M Y') }}
                </span>
            </div>
        </div>

        <div class="card overflow-hidden">
            <div class="card-pad border-b" style="border-color:rgba(0,0,0,0.06);">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="section-title">Jadwal Menu Makanan</h3>
                        
                    </div>
                </div>
            </div>
            <div class="divide-y" data-tour="ortu-menu-list" style="divide-color:rgba(0,0,0,0.05);">
                @forelse($menus as $m)
                <div class="card-pad flex flex-col sm:flex-row gap-4 sm:gap-6">
                    <div class="flex gap-2 shrink-0 overflow-x-auto pb-1 sm:pb-0 no-scrollbar">
                        @if($m->photo)
                            <div class="h-20 w-20 sm:h-28 sm:w-28 rounded-2xl overflow-hidden border border-gray-100 cursor-pointer shadow-sm hover:ring-2 hover:ring-teal-500/20 transition-all shrink-0" 
                                 @click="activeImage = '{{ Storage::url($m->photo) }}'; activeDownloadUrl = '{{ route('orangtua.menu-makanan.photo.download', ['menu_makanan' => $m, 'field' => 'photo']) }}'; showImageModal = true">
                                <img src="{{ Storage::url($m->photo) }}" class="w-full h-full object-cover">
                            </div>
                        @endif
                        @if($m->photo_kegiatan)
                            <div class="h-20 w-20 sm:h-28 sm:w-28 rounded-2xl overflow-hidden border border-gray-100 cursor-pointer shadow-sm hover:ring-2 hover:ring-teal-500/20 transition-all shrink-0"
                                 @click="activeImage = '{{ Storage::url($m->photo_kegiatan) }}'; activeDownloadUrl = '{{ route('orangtua.menu-makanan.photo.download', ['menu_makanan' => $m, 'field' => 'photo_kegiatan']) }}'; showImageModal = true">
                                <img src="{{ Storage::url($m->photo_kegiatan) }}" class="w-full h-full object-cover">
                            </div>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center gap-2 mb-1.5">
                                @if(\Carbon\Carbon::parse($m->date)->isToday())
                                    <span class="badge badge-green">Hari Ini</span>
                                @endif
                                <p class="text-sm font-bold" style="color:#1A6B6B;">{{ \Carbon\Carbon::parse($m->date)->translatedFormat('l, d M Y') }}</p>
                            </div>
                            <p class="font-bold text-[15px] sm:text-base leading-snug" style="color:#2C2C2C;">{{ $m->menu }}</p>
                            @if($m->nutrition_info)
                                <div class="flex items-start gap-1.5 mt-2 p-2 rounded-lg bg-gray-50 border border-gray-100">
                                    <span class="text-xs shrink-0">ℹ️</span>
                                    <p class="text-[11px] font-medium leading-relaxed" style="color:#6B7280;">{{ $m->nutrition_info }}</p>
                                </div>
                            @endif
                        </div>

                        {{-- Final Voting UI --}}
                        <div class="mt-4 flex flex-wrap items-center gap-2">
                            @php $myVote = $m->votes->first()?->vote_type; @endphp
                            <form action="{{ route('orangtua.menu-makanan.vote') }}" method="POST" class="shrink-0">
                                @csrf
                                <input type="hidden" name="menu_makanan_id" value="{{ $m->id }}">
                                <input type="hidden" name="vote_type" value="like">
                                <button type="submit" class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $myVote === 'like' ? 'bg-[#1A6B6B] text-white shadow-md' : 'bg-teal-50 text-teal-700 hover:bg-teal-100 border border-teal-100/50' }}">
                                    <svg class="h-3.5 w-3.5" fill="{{ $myVote === 'like' ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M14 10h4.757c1.246 0 2.228 1.053 2.115 2.285l-1.157 12.63c-.105 1.157-1.077 2.085-2.238 2.085H6.115c-1.161 0-2.133-.928-2.238-2.085L2.72 12.285C2.607 11.053 3.589 10 4.835 10H8.5l.5-5a3 3 0 013 3v2h2z" /></svg>
                                    {{ $m->likes_count }} Suka
                                </button>
                            </form>
                            <form action="{{ route('orangtua.menu-makanan.vote') }}" method="POST" class="shrink-0">
                                @csrf
                                <input type="hidden" name="menu_makanan_id" value="{{ $m->id }}">
                                <input type="hidden" name="vote_type" value="dislike">
                                <button type="submit" class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $myVote === 'dislike' ? 'bg-[#FF8C42] text-white shadow-md' : 'bg-orange-50 text-orange-700 hover:bg-orange-100 border border-orange-100/50' }}">
                                    <svg class="h-3.5 w-3.5" fill="{{ $myVote === 'dislike' ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14H5.243c-1.246 0-2.228-1.053-2.115-2.285l1.157-12.63C4.39 1.157 5.362.23 6.523.23h11.362c1.161 0 2.133.928 2.238 2.085l1.157 12.63c.113 1.232-.869 2.285-2.115 2.285H15.5l-.5 5a3 3 0 01-3-3v-2h-2z" /></svg>
                                    {{ $m->dislikes_count }} Tidak Suka
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @empty
                <div class="px-6 py-16 text-center">
                    <p class="text-sm" style="color:#9E9790;">Belum ada jadwal menu yang tersedia.</p>
                </div>
                @endforelse
            </div>
            <div class="px-6 py-4 border-t" style="border-color:rgba(0,0,0,0.06);">
                <x-per-page-selector :paginator="$menus" />
                {{ $menus->links() }}
            </div>
        </div>

        <x-image-lightbox />
    </div>
</x-app-layout>
