<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background: #1A6B6B;">
                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                </svg>
            </div>
            <h2 class="font-bold text-xl" style="color: #2C2C2C;">Kritik &amp; Saran</h2>
        </div>
    </x-slot>

    <div class="app-page-shell">
        @if(session('success'))<div class="alert-success mb-5"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert-danger mb-5"><ul class="list-disc pl-5 text-sm">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul></div>@endif
        <div class="card overflow-hidden">
            <div class="px-6 py-4 border-b flex items-center justify-between gap-3" style="border-color:rgba(0,0,0,0.06);">
                <div>
                <h3 class="section-title">Masukan orang tua &amp; wali</h3>
                <p class="section-subtitle">Hanya masukan yang ditujukan ke sekolah Anda.</p>
                </div>
                <x-export-excel route="admin.kritik-saran.export" />
            </div>
            <div class="overflow-x-auto">
                <table class="data-table" data-tour="admin-kritik-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Pengirim</th>
                            <th>Sekolah</th>
                            <th>Kelas (anak)</th>
                            <th>Ringkasan</th>
                            <th class="text-center w-16">Foto</th>
                            <th class="text-right w-28">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($feedbacks as $fb)
                            @php
                                $kelasUnik = $fb->user?->anaks
                                    ? $fb->user->anaks->pluck('kelas.name')->filter()->unique()->values()
                                    : collect();
                                $kelasTeks = $kelasUnik->isNotEmpty()
                                    ? $kelasUnik->implode(', ')
                                    : ($fb->user?->anaks?->isNotEmpty() ? 'Anak belum masuk kelas' : '');
                            @endphp
                            <tr>
                                <td class="whitespace-nowrap text-sm" style="color:#6B6560;">{{ $fb->created_at->format('d M Y, H:i') }}</td>
                                <td class="text-sm">
                                    <span class="font-semibold" style="color:#2C2C2C;">{{ $fb->user?->name ?? ($fb->nama_bapak ?: '') }}</span>
                                    @if($fb->user?->email)
                                        <span class="block text-xs" style="color:#9E9790;">{{ $fb->user->email }}</span>
                                    @endif
                                </td>
                                <td class="text-sm max-w-[10rem]" style="color:#2C2C2C;">{{ $fb->sekolah?->name ?? '' }}</td>
                                <td class="text-sm max-w-[12rem]" style="color:#6B6560;">{{ $kelasTeks }}</td>
                                <td class="max-w-md">
                                    <p class="text-sm line-clamp-2" style="color:#6B6560;">{{ \Illuminate\Support\Str::limit($fb->message, 120) }}</p>
                                </td>
                                <td class="text-center">
                                    @if($fb->photo)
                                        <img src="{{ Storage::url($fb->photo) }}" class="h-10 w-10 rounded-lg object-cover mx-auto border border-gray-100" alt="">
                                    @else
                                        <span class="text-xs" style="color:#9E9790;"></span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('admin.kritik-saran.show', $fb) }}" @if($loop->first) data-tour="admin-kritik-action-detail" @endif class="row-action-icon" style="color:#1A6B6B;background:#D0E8E8;" title="Detail" aria-label="Detail"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-14 text-center text-sm" style="color:#9E9790;">Belum ada kritik atau saran masuk.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t" style="border-color:rgba(0,0,0,0.06);">
                <x-per-page-selector :paginator="$feedbacks" />
                {{ $feedbacks->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
