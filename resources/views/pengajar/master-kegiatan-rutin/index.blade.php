<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background:#1A6B6B;">
                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7l3 3 3-3"/>
                </svg>
            </div>
            <h2 class="font-bold text-xl" style="color:#2C2C2C;">Kegiatan Rutin</h2>
        </div>
    </x-slot>

    <div class="app-page-shell">
        @if(session('success'))
            <div class="alert-success mb-5">
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('success') }}
            </div>
        @endif

        <div class="card overflow-hidden">
            <div class="card-pad page-toolbar border-b" style="border-color:rgba(0,0,0,0.06);">
                <div class="min-w-0">
                    <h3 class="section-title mb-0">Master Kegiatan Rutin</h3>
                    <p class="section-subtitle mt-1">
                        {{ auth()->user()->canManageKegiatanRutinMaster()
                            ? 'Kelola kegiatan rutin dan kelas pesertanya.'
                            : 'Pilih kegiatan untuk memperbarui pencapaian siswa.' }}
                    </p>
                </div>
                <div class="toolbar-actions">
                    @if(auth()->user()->usesAdminKegiatanRutinRoutes())
                        <x-export-excel route="admin.master-kegiatan-rutin.export" />
                    @endif
                    @if(auth()->user()->canManageKegiatanRutinMaster())
                        <x-icon-btn href="{{ route(auth()->user()->kegiatanRutinRoutePrefix().'kegiatan-rutin.index') }}"
                            label="Input Rutin Harian" variant="secondary"
                            icon="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7l3 3 3-3" />
                        <x-icon-btn href="{{ route(auth()->user()->kegiatanRutinRoutePrefix().'master-kegiatan-rutin.create') }}"
                            label="Tambah Kegiatan" variant="primary" icon="M12 4v16m8-8H4" data-tour="pg-master-rutin-add-btn" />
                    @endif
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="data-table" data-tour="pg-master-rutin-table">
                    <thead>
                        <tr>
                            <th>Nama Kegiatan</th>
                            <th>Aspek</th>
                            <th>Matrikulasi</th>
                            <th>Peserta (Kelas)</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($masters as $master)
                            <tr>
                                <td class="font-semibold" style="color:#2C2C2C;">{{ $master->nama_kegiatan }}</td>
                                <td>{{ $master->aspek }}</td>
                                <td class="text-sm" style="color:#6B6560;">{{ $master->matrikulasi ? $master->matrikulasi->indicator : '—' }}</td>
                                <td>
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($master->kelas as $k)
                                            <span class="badge badge-teal">{{ $k->name }}</span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route((auth()->user()->kegiatanRutinRoutePrefix()).'master-kegiatan-rutin.show', $master) }}" @if($loop->first) data-tour="pg-master-rutin-action-detail" @endif
                                            class="row-action-icon" style="color:#1A6B6B;background:#E8F5F5;" title="Detail Capaian Siswa" aria-label="Detail Capaian Siswa">
                                            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                        @if(auth()->user()->canManageKegiatanRutinMaster())
                                            <a href="{{ route((auth()->user()->kegiatanRutinRoutePrefix()).'master-kegiatan-rutin.edit', $master) }}" @if($loop->first) data-tour="pg-master-rutin-action-edit" @endif
                                                class="row-action-icon" style="color:#1A6B6B;background:#D0E8E8;" title="Edit Kegiatan" aria-label="Edit Kegiatan">
                                                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </a>
                                            <form action="{{ route((auth()->user()->kegiatanRutinRoutePrefix()).'master-kegiatan-rutin.destroy', $master) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');" class="inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" @if($loop->first) data-tour="pg-master-rutin-action-delete" @endif
                                                    class="row-action-icon" style="color:#C0392B;background:#FAD7D2;" title="Hapus Kegiatan" aria-label="Hapus Kegiatan">
                                                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-12 text-center" style="color:#9E9790;">Belum ada data master kegiatan rutin.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t" style="border-color:rgba(0,0,0,0.06);">
                <x-per-page-selector :paginator="$masters" />
                {{ $masters->withQueryString()->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
