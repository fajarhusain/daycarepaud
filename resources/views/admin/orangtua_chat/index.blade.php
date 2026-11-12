<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center shrink-0" style="background:#1A6B6B;">
                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
            </div>
            <h2 class="font-bold text-xl" style="color:#2C2C2C;">Chat Orang Tua</h2>
        </div>
    </x-slot>

    <div class="app-page-shell">
        <div class="card overflow-hidden">
            <div class="px-4 md:px-6 py-4 border-b" style="border-color:rgba(0,0,0,0.06);">
                <h3 class="section-title">Riwayat chat AI orang tua</h3>
                <p class="section-subtitle">Pantau pertanyaan orang tua seputar perkembangan anak di sekolah Anda.</p>
            </div>

            <form method="get" action="{{ route('admin.orangtua-chat.index') }}" data-tour="admin-chat-filter" class="card-pad toolbar-form border-b w-full" style="border-color:rgba(0,0,0,0.06);">
                <div class="toolbar-field md:min-w-[12rem] md:flex-[2]">
                    <label class="input-label">Cari</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="input-field w-full" placeholder="Nama ortu, email, atau anak…">
                </div>
                <div class="toolbar-field md:min-w-[9rem] md:max-w-[11rem]">
                    <label class="input-label">Kelas anak</label>
                    <select name="kelas_id" class="input-field w-full">
                        <option value="">Semua</option>
                        @foreach($kelas as $k)
                            <option value="{{ $k->id }}" @selected((string) request('kelas_id') === (string) $k->id)>{{ $k->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="toolbar-field md:min-w-[9rem] md:max-w-[11rem]">
                    <label class="input-label">Status</label>
                    <select name="status" class="input-field w-full">
                        <option value="" @selected(! request('status'))>Semua</option>
                        <option value="aktif" @selected(request('status') === 'aktif')>Aktif</option>
                        <option value="dihapus" @selected(request('status') === 'dihapus')>Dihapus ortu</option>
                    </select>
                </div>
                <div class="toolbar-actions">
                    <x-toolbar.filter-icon label="Filter" variant="secondary" />
                    <x-filter-reset :href="route('admin.orangtua-chat.index')" compact />
                </div>
            </form>

            <div class="table-responsive">
                <table class="data-table" data-tour="admin-chat-table">
                    <thead>
                        <tr>
                            <th>Terakhir aktif</th>
                            <th>Orang tua</th>
                            <th>Anak</th>
                            <th class="text-center">Pesan</th>
                            <th>Cuplikan terakhir</th>
                            <th>Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($chats as $chat)
                            @php
                                $lastMsg = $chat->messages->first();
                                $anakNames = $chat->user?->anaks?->pluck('name')->filter()->unique()->values() ?? collect();
                            @endphp
                            <tr>
                                <td class="whitespace-nowrap text-sm" style="color:#6B6560;">{{ $chat->updated_at->translatedFormat('d M Y, H:i') }}</td>
                                <td class="text-sm">
                                    <span class="font-semibold" style="color:#2C2C2C;">{{ $chat->user?->name ?? '—' }}</span>
                                    @if($chat->user?->email)
                                        <span class="block text-xs" style="color:#9E9790;">{{ $chat->user->email }}</span>
                                    @endif
                                </td>
                                <td class="text-sm max-w-[10rem]" style="color:#6B6560;">
                                    {{ $anakNames->isNotEmpty() ? $anakNames->implode(', ') : '—' }}
                                </td>
                                <td class="text-center text-sm tabular-nums" style="color:#2C2C2C;">{{ $chat->messages_count }}</td>
                                <td class="max-w-md">
                                    @if($lastMsg)
                                        <p class="text-xs mb-0.5" style="color:#9E9790;">{{ $lastMsg->role === 'user' ? 'Ortu' : 'AI' }} · {{ $lastMsg->created_at->translatedFormat('d M, H:i') }}</p>
                                        <p class="text-sm line-clamp-2" style="color:#6B6560;">{{ \Illuminate\Support\Str::limit($lastMsg->content, 100) }}</p>
                                    @else
                                        <span class="text-sm" style="color:#9E9790;">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($chat->cleared_at)
                                        <span class="badge text-[10px]" style="background:#FFF3E0;color:#E65100;">Dihapus ortu</span>
                                    @else
                                        <span class="badge badge-teal text-[10px]">Aktif</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <x-table.action-icon :href="route('admin.orangtua-chat.show', $chat)" label="Detail" variant="detail"
                                        @if($loop->first) data-tour="admin-chat-action-detail" @endif />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-6 md:py-12 text-center text-sm" style="color:#9E9790;">Tidak ada riwayat chat sesuai filter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 md:px-6 py-4 border-t" style="border-color:rgba(0,0,0,0.06);">
                <x-per-page-selector :paginator="$chats" />
                {{ $chats->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
