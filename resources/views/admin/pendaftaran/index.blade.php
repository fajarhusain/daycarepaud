<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background: #1A6B6B;"><svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg></div>
            <h2 class="font-bold text-xl" style="color: #2C2C2C;">Pendaftaran Siswa Baru</h2>
        </div>
    </x-slot>

    <div class="app-page-shell"
         x-data="{ showRejectModal: false, rejectRoute: '', rejectData: {}, openReject(r, d){ this.rejectRoute=r; this.rejectData=d; this.showRejectModal=true; } }" @tour-close-modals.window="showRejectModal=false">

        @if(session('success'))<div class="alert-success mb-5"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert-danger mb-5"><ul class="list-disc pl-5 text-sm">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul></div>@endif

        <form method="get" class="card card-pad toolbar-form mb-5">
            <div class="toolbar-field md:min-w-[16rem] md:flex-[2]">
                <label class="input-label">Cari Pendaftaran</label>
                <input type="text" name="search" value="{{ request('search') }}" class="input-field"
                    placeholder="Nama anak, orang tua, atau email...">
            </div>
            <div class="toolbar-actions">
                <button type="submit" class="btn-primary h-9 w-9 md:h-11 md:w-11 p-0 shrink-0" title="Cari" aria-label="Cari">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </button>
                <x-filter-reset :href="route('admin.pendaftaran.index')" compact />
                <x-export-excel route="admin.pendaftaran.export" :icon-only="true" />
            </div>
        </form>

        {{-- PENDING --}}
        <div class="card overflow-hidden mb-6" data-tour="admin-pendaftaran-pending">
            <div class="px-6 py-4 border-b" style="border-color:rgba(0,0,0,0.06); background: linear-gradient(to right, #E8F5F5, #F0FAFA);">
                <div class="flex items-center gap-3">
                    <div>
                        <h3 class="section-title">Menunggu Persetujuan</h3>
                        <p class="section-subtitle">{{ $pending->total() }} pendaftar baru membutuhkan tindakan</p>
                    </div>
                    @if($pending->total() > 0)
                    <span class="ml-auto inline-flex items-center justify-center h-7 px-3 rounded-full text-xs font-bold text-white" style="background:#FF8C42;">{{ $pending->total() }}</span>
                    @endif
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead><tr><th>Nama Anak</th><th>Orang Tua</th><th>Email</th><th>Tipe</th><th>Tgl. Lahir</th><th>Catatan Ortu</th><th class="text-right">Aksi</th></tr></thead>
                    <tbody>
                        @forelse($pending as $a)
                        @php
                            $isAnakTambahan = $a->user && $a->user->anaks->where('status', 'approved')->isNotEmpty();
                        @endphp
                        <tr>
                            <td>
                                <div class="flex items-center gap-2">
                                    <x-foto-profil :path="$a->photo" :name="$a->name" size="sm" />
                                    <span class="font-semibold" style="color:#2C2C2C;">{{ $a->name }}</span>
                                </div>
                            </td>
                            <td>{{ $a->parent_name ?? $a->user->name }}</td>
                            <td class="text-sm" style="color:#9E9790;">{{ $a->user->email }}</td>
                            <td>
                                @if($isAnakTambahan)
                                    <span class="badge badge-teal text-[10px]">Anak tambahan</span>
                                @else
                                    <span class="text-xs" style="color:#9E9790;">Baru</span>
                                @endif
                            </td>
                            <td>
                                {{ $a->dob ? \Carbon\Carbon::parse($a->dob)->format('d M Y') : '-' }}
                                @if($a->dob)<span class="text-[10px] block text-[#1A6B6B] font-bold">{{ $a->age }}</span>@endif
                            </td>
                            <td class="max-w-xs text-sm italic" style="color:#9E9790;">{{ $a->catatan_ortu ?? '-' }}</td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <form method="POST" action="{{ route('admin.pendaftaran.approve', $a) }}">
                                        @csrf
                                        <button type="submit" @if($loop->first) data-tour="admin-pendaftaran-action-approve" @endif class="row-action-icon shrink-0" style="background:#1A6B6B;" title="Setujui" aria-label="Setujui"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></button>
                                    </form>
                                    <button @if($loop->first) data-tour="admin-pendaftaran-action-reject" data-tour-open-modal="reject" @endif @click="openReject('{{ route('admin.pendaftaran.reject', $a) }}', {{ json_encode(['name' => $a->name]) }})" class="row-action-icon" style="color:#C0392B;background:#FAD7D2;" title="Tolak" aria-label="Tolak"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="py-10 text-center" style="color:#9E9790;"> Tidak ada pendaftaran yang menunggu persetujuan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t" style="border-color:rgba(0,0,0,0.06);">
                <x-per-page-selector :paginator="$pending" param="pending_per_page" />
                {{ $pending->links() }}
            </div>
        </div>

        {{-- APPROVED --}}
        <div class="card overflow-hidden mb-6" data-tour="admin-pendaftaran-approved">
            <div class="px-6 py-4 border-b" style="border-color:rgba(0,0,0,0.06);">
                <h3 class="section-title"> Telah Disetujui</h3>
                <p class="section-subtitle">Siswa aktif di sekolah ini</p>
            </div>
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead><tr><th>Nama Anak</th><th>Orang Tua</th><th>Email</th><th>Tgl. Lahir</th><th>Disetujui Pada</th></tr></thead>
                    <tbody>
                        @forelse($approved as $a)
                        <tr>
                            <td>
                                <div class="flex items-center gap-2">
                                    <x-foto-profil :path="$a->photo" :name="$a->name" size="sm" />
                                    <span class="font-semibold" style="color:#2C2C2C;">{{ $a->name }}</span>
                                </div>
                            </td>
                            <td>{{ $a->parent_name ?? $a->user->name }}</td>
                            <td class="text-sm" style="color:#9E9790;">{{ $a->user->email }}</td>
                            <td>{{ $a->dob ? \Carbon\Carbon::parse($a->dob)->format('d M Y') : '-' }}</td>
                            <td class="text-sm" style="color:#9E9790;">{{ $a->updated_at->format('d M Y') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="py-4 md:py-8 text-center" style="color:#9E9790;">Belum ada yang disetujui.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t" style="border-color:rgba(0,0,0,0.06);">
                <x-per-page-selector :paginator="$approved" param="approved_per_page" />
                {{ $approved->links() }}
            </div>
        </div>

        {{-- REJECTED --}}
        <div class="card overflow-hidden">
            <div class="px-6 py-4 border-b" style="border-color:rgba(0,0,0,0.06);">
                <h3 class="section-title"> Ditolak</h3>
                <p class="section-subtitle">Pendaftaran yang tidak disetujui</p>
            </div>
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead><tr><th>Nama Anak</th><th>Orang Tua</th><th>Alasan Penolakan</th></tr></thead>
                    <tbody>
                        @forelse($rejected as $a)
                        <tr>
                            <td>
                                <div class="flex items-center gap-2">
                                    <x-foto-profil :path="$a->photo" :name="$a->name" size="sm" />
                                    <span class="font-semibold" style="color:#2C2C2C;">{{ $a->name }}</span>
                                </div>
                            </td>
                            <td>{{ $a->parent_name ?? $a->user->name }}</td>
                            <td class="text-sm italic" style="color:#9E9790;">{{ $a->catatan_admin ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="py-4 md:py-8 text-center" style="color:#9E9790;">Tidak ada penolakan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t" style="border-color:rgba(0,0,0,0.06);">
                <x-per-page-selector :paginator="$rejected" param="rejected_per_page" />
                {{ $rejected->links() }}
            </div>
        </div>

        {{-- REJECT MODAL --}}
        <div x-show="showRejectModal" data-tour="modal-reject" class="modal-overlay" style="display:none;">
            <div x-show="showRejectModal" x-transition class="modal-box max-w-md" @click.away="showRejectModal=false">
                <form :action="rejectRoute" method="POST">
                    @csrf
                    <div class="modal-header"><h3 class="section-title">Tolak Pendaftaran</h3><p class="section-subtitle mt-1">Penolakan untuk: <strong x-text="rejectData.name"></strong></p></div>
                    <div class="modal-body space-y-4">
                        <div><label class="input-label">Alasan Penolakan (opsional, akan dilihat admin)</label><textarea name="catatan_admin" rows="3" class="input-field" placeholder="Contoh: Kuota kelas sudah penuh..."></textarea></div>
                    </div>
                    <div class="modal-footer"><button type="button" @click="showRejectModal=false" class="btn-secondary">Batal</button><button type="submit" class="btn-danger">Tolak Pendaftaran</button></div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
