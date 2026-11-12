<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background: #1A6B6B;"><svg
                    class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg></div>
            <h2 class="font-bold text-xl" style="color: #2C2C2C;">Laporan Pencapaian Anak</h2>
        </div>
    </x-slot>
    <div class="app-page-shell" x-data="{
        showImageModal: false, activeImage: '', activeDownloadUrl: null, activeMediaType: 'image',
        openMedia(url, downloadUrl) {
            this.activeImage = url;
            this.activeDownloadUrl = downloadUrl ?? null;
            this.activeMediaType = (typeof window.isVideoPath === 'function' && window.isVideoPath(url)) ? 'video' : 'image';
            this.showImageModal = true;
        }
    }">
        <div class="card mb-5">
            <div class="card-pad border-b space-y-4 md:space-y-5" style="border-color:rgba(0,0,0,0.06);">
                <div class="space-y-1">
                    <h3 class="section-title mb-0">Filter laporan</h3>
                </div>
                <form data-tour="ortu-pencapaian-filter" method="get" action="{{ route('orangtua.pencapaian.index') }}"
                    class="filter-toolbar-inline">
                    @if($anakList->count() > 1)
                        <div class="filter-toolbar-field">
                            <label class="input-label" for="ortu-penc-anak">Anak</label>
                            <select id="ortu-penc-anak" name="filter_anak_id" class="input-field w-full h-9 md:h-11 min-w-0">
                                <option value="">Semua Anak</option>
                                @foreach($anakList as $anak)
                                    <option value="{{ $anak->id }}" @selected($filterAnakId === $anak->id)>{{ $anak->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="filter-toolbar-field">
                        <label class="input-label" for="ortu-penc-tanggal-dari">Dari</label>
                        <input id="ortu-penc-tanggal-dari" type="date" name="tanggal_dari" value="{{ $tanggalDari }}" class="input-field w-full h-9 md:h-11 min-w-0">
                    </div>
                    <div class="filter-toolbar-field">
                        <label class="input-label" for="ortu-penc-tanggal-sampai">Sampai</label>
                        <input id="ortu-penc-tanggal-sampai" type="date" name="tanggal_sampai" value="{{ $tanggalSampai }}" class="input-field w-full h-9 md:h-11 min-w-0">
                    </div>
                    <div class="filter-toolbar-field">
                        <label class="input-label" for="ortu-penc-aspek">Aspek</label>
                        <select id="ortu-penc-aspek" name="aspek" class="input-field w-full h-9 md:h-11 min-w-0">
                            <option value="">Semua aspek</option>
                            <option value="{{ \App\Support\FilterAspekPencapaian::UMUM }}"
                                @selected($filterAspekRaw === \App\Support\FilterAspekPencapaian::UMUM)>Umum / tanpa aspek
                            </option>
                            @foreach($aspekPilihan as $asp)
                                <option value="{{ $asp }}" @selected($filterAspekRaw === $asp)>{{ $asp }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-toolbar-actions">
                        <button type="submit" class="btn-primary h-9 w-9 md:h-11 md:w-11 p-0 shrink-0" title="Terapkan" aria-label="Terapkan filter">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </button>
                        <x-filter-reset :href="route('orangtua.pencapaian.index')" compact />
                    </div>
                </form>
            </div>
            @if($filterAktif)
                <div class="card-pad py-3 text-sm space-y-1" style="background:#FAF6F0; color:#6B6560;">
                    @if($filterTanggalAktif)
                        <p> Rentang tanggal input: <strong
                                style="color:#2C2C2C;">{{ \Carbon\Carbon::parse($tanggalDari)->translatedFormat('d M Y') }}</strong>
                            – <strong
                                style="color:#2C2C2C;">{{ \Carbon\Carbon::parse($tanggalSampai)->translatedFormat('d M Y') }}</strong>
                        </p>
                    @endif
                    @if($filterAnakId)
                        <p>Anak: <strong
                                style="color:#2C2C2C;">{{ $anakList->firstWhere('id', $filterAnakId)?->name ?? '' }}</strong>
                        </p>
                    @endif
                    @if($filterAspek)
                        <p>Aspek: <strong
                                style="color:#2C2C2C;">{{ $filterAspek === \App\Support\FilterAspekPencapaian::UMUM ? 'Umum / tanpa aspek' : $filterAspek }}</strong>
                        </p>
                    @endif
                </div>
            @endif
        </div>
        <details class="card mb-5 overflow-hidden">
            <summary class="px-5 py-4 cursor-pointer list-none font-semibold text-sm flex items-center gap-2"
                style="color:#1A6B6B;">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Penjelasan tingkat penilaian
                <span class="text-xs font-normal ml-auto" style="color:#9E9790;">Klik untuk membuka</span>
            </summary>
            <div class="px-5 pb-5 pt-0 border-t text-sm space-y-3 leading-relaxed"
                style="border-color:rgba(0,0,0,0.06); color:#5A5A5A;">
                <p class="pt-4">Nilai mengacu pada penilaian perkembangan anak usia dini (biasanya dicatat per indikator
                    matrikulasi).</p>
                <ul class="space-y-2 pl-1">
                    @forelse($skalaLegenda as $sk)
                    <li>
                        <span class="inline-block font-bold px-2 py-0.5 rounded text-xs mr-2 leading-snug"
                            style="background:{{ $sk->color }};">{{ $sk->label }}</span>
                        <span class="text-gray-500">({{ $sk->code }})</span>
                         Penilaian capaian pada indikator matrikulasi.
                    </li>
                    @empty
                    <li class="text-gray-500">Belum ada skala capaian yang dikonfigurasi sekolah.</li>
                    @endforelse
                </ul>
            </div>
        </details>

        <div class="card overflow-hidden" data-tour="ortu-pencapaian-reports">
            <div class="px-3 py-3 sm:card-pad border-b" style="border-color:rgba(0,0,0,0.06);">
                <h3 class="section-title text-base sm:text-lg mb-0">Daftar per kegiatan</h3>
                <p class="text-xs sm:text-sm mt-1 mb-0 leading-relaxed" style="color:#9E9790;">Ketuk kegiatan untuk melihat indikator dan catatan guru.</p>
            </div>
            <div class="divide-y" style="border-color:rgba(0,0,0,0.06);">
                @forelse($groupedPencapaian as $bundleKey => $rows)
                    @php
                        $first = $rows->first();
                        $detailRows = $rows
                            ->filter(fn ($p) => \App\Support\FilterAspekPencapaian::rowMatches($filterAspek, $p))
                            ->sortBy(fn ($p) => ($p->matrikulasi->aspek ?? '') . ($p->matrikulasi->indicator ?? ''));
                    @endphp
                    <details class="group" @if($loop->first) open @endif>
                        <summary class="px-3 py-3 sm:card-pad cursor-pointer list-none [&::-webkit-details-marker]:hidden hover:bg-[#FAF9F6]/80 active:bg-[#FAF9F6] transition">
                            <div class="flex gap-3 sm:gap-4">
                                <x-foto-profil :path="$first->anak->photo ?? null" :name="$first->anak->name ?? '?'" size="sm" class="shrink-0 self-start mt-0.5 sm:mt-0 sm:!h-10 sm:!w-10" />
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0">
                                            <p class="font-bold text-sm leading-tight line-clamp-2 normal-case" style="color:#2C2C2C;">{{ $first->anak->name ?? '-' }}</p>
                                            @if($first->anak && $first->anak->dob)
                                                <p class="text-[10px] font-medium mt-0.5" style="color:#1A6B6B;">{{ $first->anak->age }}</p>
                                            @endif
                                        </div>
                                        <svg class="h-5 w-5 shrink-0 text-gray-400 transition-transform duration-200 group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                                    </div>

                                    @if($first->kegiatan)
                                        <p class="font-semibold text-[13px] sm:text-sm leading-snug mt-2.5" style="color:#1A6B6B;">{{ $first->kegiatan->title }}</p>
                                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1.5 mt-2 text-[11px] sm:text-xs" style="color:#9E9790;">
                                            <time datetime="{{ \Carbon\Carbon::parse($first->kegiatan->date)->toDateString() }}">{{ \Carbon\Carbon::parse($first->kegiatan->date)->translatedFormat('d M Y') }}</time>
                                            <span class="text-gray-300" aria-hidden="true">·</span>
                                            <span class="inline-flex items-center gap-1 min-w-0 max-w-[55%] sm:max-w-none">
                                                <x-foto-profil :path="$first->pengajar->photo ?? null" :name="$first->pengajar->name ?? 'Guru'" size="xs" class="shrink-0" />
                                                <span class="truncate">{{ $first->pengajar->name ?? 'Guru' }}</span>
                                            </span>
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full shrink-0" style="color:#975A16; background:#FFF8EB;">{{ $detailRows->count() }} indikator</span>
                                        </div>
                                    @else
                                        <p class="text-sm font-medium text-gray-700 mt-2">Evaluasi tanpa kegiatan terkait</p>
                                        <div class="flex flex-wrap items-center gap-2 mt-1.5 text-[11px]" style="color:#9E9790;">
                                            <span>{{ \Carbon\Carbon::parse($first->created_at)->translatedFormat('d M Y') }}</span>
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full" style="color:#975A16; background:#FFF8EB;">{{ $detailRows->count() }} indikator</span>
                                        </div>
                                    @endif
                                </div>
                                @if($first->photo)
                                    <div class="shrink-0 hidden sm:block">
                                        <img src="{{ Storage::url($first->photo) }}" class="h-14 w-14 object-cover rounded-xl ring-1 ring-black/5" alt="">
                                    </div>
                                @endif
                            </div>
                        </summary>
                        <div class="px-3 pb-3 sm:px-6 sm:pb-5 pt-0 border-t" style="border-color:rgba(0,0,0,0.06); background:#FAFAF8;">
                            @if($first->photo)
                                <button type="button"
                                    class="mt-3 w-full sm:w-auto flex items-center gap-2.5 text-xs font-semibold rounded-lg px-2 py-2 sm:px-0 sm:py-0 hover:bg-white/80 sm:hover:bg-transparent transition"
                                    style="color:#1A6B6B;"
                                    @click.stop="openMedia('{{ Storage::url($first->photo) }}', '{{ route('orangtua.pencapaian.photos.download-bundle', ['anak_id' => $first->anak_id, 'kegiatan_id' => $first->kegiatan_id]) }}')">
                                    <img src="{{ Storage::url($first->photo) }}" class="h-11 w-11 sm:h-12 sm:w-12 rounded-lg object-cover ring-1 ring-black/5 shrink-0" alt="">
                                    <span>Lihat dokumentasi</span>
                                </button>
                            @endif
                            <div class="sm:rounded-xl sm:border sm:overflow-hidden mt-2 sm:mt-3" style="border-color:rgba(0,0,0,0.08); background:#fff;">
                                    {{-- Desktop View --}}
                                    <div class="hidden sm:block overflow-x-auto">
                                        <table class="w-full text-sm">
                                            <thead>
                                                <tr style="background:#F5F5F3;">
                                                    <th class="text-left px-3 py-2 text-xs font-semibold"
                                                        style="color:#5A5A5A;">Aspek / indikator</th>
                                                    <th class="text-left px-3 py-2 text-xs font-semibold w-24"
                                                        style="color:#5A5A5A;">Nilai</th>
                                                    <th class="text-left px-3 py-2 text-xs font-semibold"
                                                        style="color:#5A5A5A;">Catatan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($detailRows as $p)
                                                    <tr class="border-t" style="border-color:rgba(0,0,0,0.05);">
                                                        <td class="px-3 py-2 align-top">
                                                            @if($p->matrikulasi)
                                                                <span class="font-semibold text-xs block"
                                                                    style="color:#1A6B6B;">{{ $p->matrikulasi->aspek ?: 'Aspek' }}</span>
                                                                <span class="text-xs mt-0.5 block"
                                                                    style="color:#5A5A5A;">{{ $p->matrikulasi->indicator }}</span>
                                                                @if(filled($p->matrikulasi->description))
                                                                    <p class="text-xs mt-1.5" style="color:#6B6560;">
                                                                        {{ $p->matrikulasi->description }}</p>
                                                                @endif
                                                                @if(filled($p->matrikulasi->tujuan))
                                                                    <p class="text-xs mt-2 rounded-lg px-2 py-1.5"
                                                                        style="background:#F5F5F3; color:#4A4A4A;"><strong
                                                                            style="color:#1A6B6B;">Tujuan pembelajaran:</strong>
                                                                        {{ $p->matrikulasi->tujuan }}</p>
                                                                @endif
                                                                @if(filled($p->matrikulasi->strategi))
                                                                    <p class="text-xs mt-1.5 rounded-lg px-2 py-1.5"
                                                                        style="background:#F0FAFA; color:#4A4A4A;"><strong
                                                                            style="color:#1A6B6B;">Strategi:</strong>
                                                                        {{ $p->matrikulasi->strategi }}</p>
                                                                @endif
                                                            @else
                                                                <span class="text-xs italic" style="color:#9E9790;">Catatan lama</span>
                                                            @endif
                                                        </td>
                                                        <td class="px-3 py-2 align-top">
                                                            <span
                                                                class="text-xs font-bold px-2 py-1 rounded inline-block leading-snug max-w-[12rem]"
                                                                style="background:{{ \App\Support\LabelSkorPencapaian::color($p->score, $p->anak?->sekolah_id) }};">{{ \App\Support\LabelSkorPencapaian::label($p->score, $p->anak?->sekolah_id) }}</span>
                                                        </td>
                                                        <td class="px-3 py-2 text-xs align-top" style="color:#6B6560;">
                                                            {{ $p->feedback ?: '' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    {{-- Mobile View --}}
                                    <div class="sm:hidden divide-y divide-gray-100/80">
                                        @foreach($detailRows as $p)
                                            <details class="bg-white group/ind">
                                                <summary class="px-3 py-3 cursor-pointer list-none [&::-webkit-details-marker]:hidden">
                                                    <div class="flex items-start justify-between gap-2">
                                                        <span class="text-[10px] font-bold text-[#1A6B6B] uppercase tracking-wide leading-tight">{{ $p->matrikulasi->aspek ?: 'Aspek' }}</span>
                                                        <svg class="h-4 w-4 shrink-0 text-gray-400 mt-0.5 transition-transform duration-200 group-open/ind:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                                                    </div>
                                                    <p class="text-[13px] font-semibold text-gray-900 leading-snug mt-1 line-clamp-2">{{ $p->matrikulasi->indicator ?? 'Indikator' }}</p>
                                                    <span class="inline-block mt-2 text-[10px] font-bold px-2.5 py-1 rounded-full leading-snug" style="background:{{ \App\Support\LabelSkorPencapaian::color($p->score, $p->anak?->sekolah_id) }}; color:#fff;">
                                                        {{ \App\Support\LabelSkorPencapaian::label($p->score, $p->anak?->sekolah_id) }}
                                                    </span>
                                                </summary>
                                                <div class="px-3 pb-3 pt-0 text-xs leading-relaxed border-t border-gray-50 space-y-2.5">
                                                    @if($p->matrikulasi && filled($p->matrikulasi->indicator))
                                                        <p class="pt-2.5 text-gray-700">{{ $p->matrikulasi->indicator }}</p>
                                                    @endif
                                                    @if($p->matrikulasi && filled($p->matrikulasi->description))
                                                        <p class="text-gray-500">{{ $p->matrikulasi->description }}</p>
                                                    @endif
                                                    @if($p->matrikulasi && filled($p->matrikulasi->tujuan))
                                                        <p class="text-gray-600"><span class="font-semibold text-[#1A6B6B]">Tujuan:</span> {{ $p->matrikulasi->tujuan }}</p>
                                                    @endif
                                                    @if($p->matrikulasi && filled($p->matrikulasi->strategi))
                                                        <p class="text-gray-600"><span class="font-semibold text-[#1A6B6B]">Strategi:</span> {{ $p->matrikulasi->strategi }}</p>
                                                    @endif
                                                    @if($p->feedback)
                                                        <p class="text-gray-600 italic border-l-2 pl-2.5" style="border-color:#1A6B6B33;"><span class="font-semibold not-italic text-gray-500">Catatan guru:</span> {{ $p->feedback }}</p>
                                                    @endif
                                                </div>
                                            </details>
                                        @endforeach
                                    </div>
                                </div>
                        </div>
                    </details>
                @empty
                    <div class="px-6 py-16 text-center text-sm" style="color:#9E9790;">Belum ada laporan evaluasi.</div>
                @endforelse
            </div>
            <div class="px-3 py-3 sm:card-pad border-t" style="border-color:rgba(0,0,0,0.06);">
                <x-per-page-selector :paginator="$groupedPencapaian" />
                {{ $groupedPencapaian->links() }}
            </div>
        </div>

        <x-image-lightbox />
    </div>
</x-app-layout>
