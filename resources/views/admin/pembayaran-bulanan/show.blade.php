<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background: #1A6B6B;">
                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
            </div>
            <div>
                <h2 class="font-bold text-xl" style="color: #2C2C2C;">Detail Pembayaran</h2>
                <p class="text-sm" style="color:#9E9790;">{{ $pembayaran->anak->name }} - {{ $pembayaran->getPeriodeLabel() }}</p>
            </div>
        </div>
    </x-slot>

    <div class="app-page-shell"
         x-data="{ showDiskonModal: {{ $errors->hasAny(['diskon_mode', 'diskon_id', 'diskon_manual_nominal', 'diskon_manual_keterangan']) ? 'true' : 'false' }}, showApproveModal: false, showRejectModal: false, showBatalkanModal: false, showItemModal: false, showImageModal: false, activeImage: null, activeDownloadUrl: null, itemModalEditId: null, itemModalNama: '', itemModalJumlah: 0 }" @tour-close-modals.window="showDiskonModal=false; showApproveModal=false; showRejectModal=false; showBatalkanModal=false; showItemModal=false">

        @if(session('success'))<div class="alert-success mb-5">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert-danger mb-5"><ul class="list-disc pl-5 text-sm">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul></div>@endif

        @php $totalTambahan = $pembayaran->getTotalBiayaTambahan(); @endphp

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="card p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="section-title">Status</h3>
                        <span class="badge badge-{{ $pembayaran->status_badge }}">{{ $pembayaran->status_label }}</span>
                    </div>
                    @if($pembayaran->catatan_admin)
                        <div class="p-3 rounded-lg" style="background:#F9FAFB;">
                            <p class="text-xs font-semibold mb-1" style="color:#9E9790;">Catatan:</p>
                            <p class="text-sm">{{ $pembayaran->catatan_admin }}</p>
                        </div>
                    @endif
                </div>

                <div class="card p-6" data-tour="pembayaran-rincian">
                    <h3 class="section-title mb-4">Rincian Perhitungan</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between py-2 border-b" style="border-color:rgba(0,0,0,0.06);">
                            <span class="text-sm" style="color:#9E9790;">Biaya Bulanan</span>
                            <span class="font-semibold">{{ $pembayaran->getBiayaPerHariFormatted() }}</span>
                        </div>
                        @php $items = $pembayaran->items; @endphp
                        <div class="flex justify-between py-2 border-b" style="border-color:rgba(0,0,0,0.06);">
                            <div class="flex items-center gap-2">
                                <span class="text-sm" style="color:#9E9790;">Biaya Lain</span>
                                @if($pembayaran->isPending())
                                    <button type="button"
                                            @click="itemModalEditId=null; itemModalNama=''; itemModalJumlah=0; showItemModal=true"
                                            class="text-xs px-2 py-0.5 rounded" style="color:#1A6B6B;background:#D0E8E8;">+ Tambah</button>
                                @endif
                            </div>
                            <span></span>
                        </div>
                        @if($items && $items->count() > 0)
                            @foreach($items as $item)
                                <div class="flex justify-between pl-4 py-1 text-sm items-center gap-2" style="color:#6B6560;">
                                    <div class="flex items-center gap-2">
                                        <span>{{ $item->nama_item }}</span>
                                        @if($pembayaran->isPending())
                                            <button type="button" class="text-xs px-1.5 py-0.5 rounded hover:opacity-70"
                                                    style="color:#D97706;background:#FEF3C7;"
                                                    @click="itemModalEditId={{ $item->id }}; itemModalNama='{{ e($item->nama_item) }}'; itemModalJumlah={{ $item->jumlah }}; showItemModal=true">Edit</button>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span>+ Rp {{ number_format($item->jumlah, 0, ',', '.') }}</span>
                                        @if($pembayaran->isPending())
                                            <form action="{{ route('admin.pembayaran-bulanan.items.destroy', [$pembayaran, $item]) }}" method="POST"
                                                  onsubmit="return confirm('Hapus biaya ini?')" class="inline-flex">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-xs font-bold px-1.5 py-0.5 rounded leading-none hover:opacity-70"
                                                        style="color:#C0392B;background:#FEE2E2;">&times;</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @endif
                        <div class="flex justify-between py-2 border-b" style="border-color:rgba(0,0,0,0.06);">
                            <div class="flex items-center gap-2">
                                <span class="text-sm" style="color:#9E9790;">Hari Hadir</span>
                            </div>
                            <span class="font-semibold" style="color:#1A6B6B;">{{ $pembayaran->hari_hadir }} hari</span>
                        </div>
                        <div class="flex justify-between py-2 border-b" style="border-color:rgba(0,0,0,0.06);">
                            <span class="text-sm" style="color:#9E9790;">Subtotal</span>
                            <span class="font-semibold">{{ $pembayaran->getSubtotalFormatted() }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b" style="border-color:rgba(0,0,0,0.06);">
                            <div class="flex items-center gap-2">
                                <span class="text-sm" style="color:#9E9790;">Diskon</span>
                                <button @click="showDiskonModal=true" class="text-xs px-2 py-0.5 rounded" style="color:#1A6B6B;background:#D0E8E8;">Edit</button>
                            </div>
                            <span class="font-semibold" style="color:#C0392B;">
                                @if($pembayaran->nilai_diskon > 0)
                                    @if($pembayaran->diskon)
                                        {{ $pembayaran->diskon->nama_diskon }} (-{{ $pembayaran->getNilaiDiskonFormatted() }})
                                    @elseif($pembayaran->diskon_keterangan)
                                        {{ $pembayaran->diskon_keterangan }} (-{{ $pembayaran->getNilaiDiskonFormatted() }})
                                    @else
                                        -{{ $pembayaran->getNilaiDiskonFormatted() }}
                                    @endif
                                @else
                                    -
                                @endif
                            </span>
                        </div>
                        @if($totalTambahan > 0 && $pembayaran->nilai_diskon > 0)
                            <div class="flex justify-between py-2 text-xs" style="color:#6B6560;">
                                <span>Total sebelum diskon</span>
                                <span>Rp {{ number_format($pembayaran->subtotal + $totalTambahan, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between py-3 px-4 rounded-lg" style="background:#D0E8E8;">
                            <span class="font-bold text-lg" style="color:#1A6B6B;">Total Bayar</span>
                            <span class="font-bold text-2xl" style="color:#1A6B6B;">{{ $pembayaran->getTotalFormatted() }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="card p-6">
                    <h3 class="section-title mb-4">Info Siswa</h3>
                    <div class="space-y-3 text-sm">
                        <div><p class="text-xs" style="color:#9E9790;">Nama</p><p class="font-semibold">{{ $pembayaran->anak->name }}</p></div>
                        <div><p class="text-xs" style="color:#9E9790;">Kelas</p><p class="font-semibold">{{ $pembayaran->anak->kelas->name ?? '-' }}</p></div>
                        <div><p class="text-xs" style="color:#9E9790;">Periode</p><p class="font-semibold">{{ $pembayaran->getPeriodeLabel() }}</p></div>
                        <div><p class="text-xs" style="color:#9E9790;">Jenis Biaya</p><p class="font-semibold">{{ $pembayaran->biayaBulananSekolah->nama_biaya ?? '-' }}</p></div>
                    </div>
                </div>

                @if($pembayaran->bukti_transfer)
                    <div class="card p-6" data-tour="pembayaran-bukti">
                        <h3 class="section-title mb-4">Bukti Transfer</h3>
                        <img src="{{ Storage::url($pembayaran->bukti_transfer) }}" alt="Bukti"
                            class="w-full rounded-lg border cursor-pointer"
                            @click="activeImage = '{{ Storage::url($pembayaran->bukti_transfer) }}'; activeDownloadUrl = '{{ route('admin.pembayaran-bulanan.bukti.download', $pembayaran) }}'; showImageModal = true">
                    </div>
                @endif

                @if($pembayaran->isPending())
                    <div class="card p-6 space-y-3">
                        <button data-tour="pembayaran-approve-btn" data-tour-open-modal="approve" @click="showApproveModal=true" class="btn-primary w-full justify-center">Lunas</button>
                        <button data-tour="pembayaran-reject-btn" data-tour-open-modal="reject" @click="showRejectModal=true" class="btn-danger w-full justify-center">Tolak</button>
                    </div>
                @elseif($pembayaran->isApproved())
                    <div class="card p-6 space-y-3">
                        <p class="text-xs" style="color:#9E9790;">Tagihan lunas tidak bisa dihapus langsung. Batalkan pelunasan dulu jika ada kesalahan, lalu hapus dari daftar rekap.</p>
                        <button type="button" @click="showBatalkanModal=true" class="btn-secondary w-full justify-center">Batalkan pelunasan</button>
                    </div>
                @endif

                <a href="{{ route('admin.pembayaran-bulanan.index', ['bulan' => $pembayaran->periode_bulan, 'tahun' => $pembayaran->periode_tahun]) }}" class="btn-secondary w-full justify-center">Kembali</a>
            </div>
        </div>

        <!-- DISKON -->
        @php
            $diskonModeAwal = $pembayaran->diskon_id
                ? 'master'
                : (((float) $pembayaran->nilai_diskon > 0 || filled($pembayaran->diskon_keterangan)) ? 'manual' : 'none');
        @endphp
        <div x-show="showDiskonModal" class="modal-overlay" style="display:none;">
            <div x-show="showDiskonModal" x-transition class="modal-box max-w-sm" @click.away="showDiskonModal=false">
                <form action="{{ route('admin.pembayaran-bulanan.update-diskon', $pembayaran) }}" method="POST"
                      x-data="{
                          mode: @js(old('diskon_mode', $diskonModeAwal)),
                          diskonId: @js((string) old('diskon_id', $pembayaran->diskon_id ?? '')),
                          nominal: @js(old('diskon_manual_nominal', $diskonModeAwal === 'manual' ? (int) round((float) $pembayaran->nilai_diskon) : '')),
                          keterangan: @js(old('diskon_manual_keterangan', $pembayaran->diskon_keterangan ?? '')),
                      }">
                    @csrf @method('PATCH')
                    <input type="hidden" name="diskon_mode" :value="mode">
                    <div class="modal-header"><h3 class="section-title">Edit Diskon</h3></div>
                    <div class="modal-body space-y-3">
                        <div>
                            <label class="input-label">Jenis</label>
                            <select x-model="mode" class="input-field">
                                <option value="none">Tanpa Diskon</option>
                                <option value="master">Dari master</option>
                                <option value="manual">Manual</option>
                            </select>
                        </div>
                        <div x-show="mode === 'master'">
                            <label class="input-label">Diskon</label>
                            <select name="diskon_id" x-model="diskonId" class="input-field" :required="mode === 'master'" :disabled="mode !== 'master'">
                                <option value="">Pilih diskon</option>
                                @foreach(\App\Models\Diskon::where('sekolah_id', auth()->user()->sekolah_id)->where('is_aktif', true)->get() as $diskon)
                                    <option value="{{ $diskon->id }}">{{ $diskon->nama_diskon }} ({{ $diskon->getNilaiFormatted() }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div x-show="mode === 'manual'" class="space-y-3">
                            <div>
                                <label class="input-label">Nominal (Rp)</label>
                                <input type="number" name="diskon_manual_nominal" x-model="nominal" min="1" step="1" placeholder="0" class="input-field" :required="mode === 'manual'" :disabled="mode !== 'manual'">
                            </div>
                            <div>
                                <label class="input-label">Keterangan</label>
                                <input type="text" name="diskon_manual_keterangan" x-model="keterangan" maxlength="255" placeholder="Contoh: diskon saudara" class="input-field" :required="mode === 'manual'" :disabled="mode !== 'manual'">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" @click="showDiskonModal=false" class="btn-secondary">Batal</button><button type="submit" class="btn-primary">Simpan</button></div>
                </form>
            </div>
        </div>

        <!-- APPROVE -->
        <div x-show="showApproveModal" data-tour="modal-approve" class="modal-overlay" style="display:none;">
            <div x-show="showApproveModal" x-transition class="modal-box max-w-sm" @click.away="showApproveModal=false">
                <form action="{{ route('admin.pembayaran-bulanan.approve', $pembayaran) }}" method="POST">
                    @csrf @method('PATCH')
                    <div class="modal-body text-center py-6">
                        <h3 class="section-title">Tandai Lunas?</h3>
                        <p class="section-subtitle mt-1">Total: {{ $pembayaran->getTotalFormatted() }}</p>
                        <textarea name="catatan_admin" rows="2" placeholder="Catatan (opsional)" class="input-field mt-4"></textarea>
                    </div>
                    <div class="modal-footer"><button type="button" @click="showApproveModal=false" class="btn-secondary">Batal</button><button type="submit" class="btn-primary">Ya, Lunas</button></div>
                </form>
            </div>
        </div>

        <!-- BATALKAN LUNAS -->
        <div x-show="showBatalkanModal" class="modal-overlay" style="display:none;">
            <div x-show="showBatalkanModal" x-transition class="modal-box max-w-sm" @click.away="showBatalkanModal=false">
                <form action="{{ route('admin.pembayaran-bulanan.batalkan-lunas', $pembayaran) }}" method="POST">
                    @csrf @method('PATCH')
                    <div class="modal-body text-center py-6">
                        <h3 class="section-title">Batalkan pelunasan?</h3>
                        <p class="section-subtitle mt-2">Jurnal pelunasan &amp; cashflow akan dihapus. Tagihan kembali ke <strong>Menunggu</strong> (bisa dihapus dari rekap).</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" @click="showBatalkanModal=false" class="btn-secondary">Batal</button>
                        <button type="submit" class="btn-danger">Ya, batalkan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- REJECT -->
        <div x-show="showRejectModal" data-tour="modal-reject" class="modal-overlay" style="display:none;">
            <div x-show="showRejectModal" x-transition class="modal-box max-w-sm" @click.away="showRejectModal=false">
                <form action="{{ route('admin.pembayaran-bulanan.reject', $pembayaran) }}" method="POST">
                    @csrf @method('PATCH')
                    <div class="modal-body text-center py-6">
                        <h3 class="section-title">Tolak Pembayaran?</h3>
                        <textarea name="catatan_admin" rows="2" placeholder="Alasan penolakan" required class="input-field mt-4"></textarea>
                    </div>
                    <div class="modal-footer"><button type="button" @click="showRejectModal=false" class="btn-secondary">Batal</button><button type="submit" class="btn-danger">Ya, Tolak</button></div>
                </form>
            </div>
        </div>

        <!-- MODAL TAMBAH/EDIT BIAYA LAIN -->
        <div x-show="showItemModal" class="modal-overlay" style="display:none;">
            <div x-show="showItemModal" x-transition class="modal-box max-w-sm" @click.away="showItemModal=false">
                <form :action="itemModalEditId
                    ? '{{ route('admin.pembayaran-bulanan.items.update', ['pembayaran' => $pembayaran->id, 'item' => '__ID__']) }}'.replace('__ID__', itemModalEditId)
                    : '{{ route('admin.pembayaran-bulanan.items.store', $pembayaran) }}'" method="POST">
                    @csrf
                    <template x-if="itemModalEditId"><input type="hidden" name="_method" value="PATCH"></template>
                    <div class="modal-header"><h3 class="section-title" x-text="itemModalEditId ? 'Edit Biaya Lain' : 'Tambah Biaya Lain'"></h3></div>
                    <div class="modal-body space-y-3">
                        <div>
                            <label class="input-label">Nama Biaya</label>
                            <input type="text" name="nama_item" x-model="itemModalNama" placeholder="Contoh: Popok, Ekstra" class="input-field" required>
                        </div>
                        <div>
                            <label class="input-label">Jumlah (Rp)</label>
                            <input type="number" name="jumlah" x-model="itemModalJumlah" min="1" placeholder="0" class="input-field" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" @click="showItemModal=false" class="btn-secondary">Batal</button>
                        <button type="submit" class="btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        <x-image-lightbox />
    </div>
</x-app-layout>
