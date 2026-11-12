<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3 min-w-0" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center shrink-0" style="background:#1A6B6B;">
                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
            </div>
            <div class="min-w-0">
                <h2 class="font-bold text-xl truncate" style="color:#2C2C2C;">Chat {{ $chat->user?->name ?? 'Orang Tua' }}</h2>
                <p class="text-xs truncate section-subtitle mt-0.5 mb-0">
                    Anak: {{ $chat->user?->anaks?->pluck('name')->filter()->unique()->implode(', ') ?: '—' }}
                </p>
            </div>
        </div>
    </x-slot>

    <div class="app-page-shell">
        <div class="max-w-3xl mx-auto space-y-4">
            <div class="card overflow-hidden">
                <div class="card-pad toolbar-form border-b w-full" style="border-color:rgba(0,0,0,0.06);">
                    <div class="toolbar-actions w-full sm:w-auto sm:ml-0">
                        <a href="{{ route('admin.orangtua-chat.index') }}"
                            class="btn-secondary toolbar-icon shrink-0 inline-flex items-center justify-center !p-0 h-9 w-9 md:h-11 md:w-11"
                            title="Kembali" aria-label="Kembali">
                            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        </a>
                    </div>
                </div>
                <div class="px-4 md:px-6 py-3 text-xs sm:text-sm flex flex-wrap gap-x-4 gap-y-1 border-b" style="background:#FAF6F0;border-color:rgba(0,0,0,0.06);color:#6B6560;">
                    @if($chat->user?->email)
                        <span>{{ $chat->user->email }}</span>
                    @endif
                    <span>{{ $messages->count() }} pesan · mode baca saja</span>
                    @if($chat->cleared_at)
                        <span class="badge text-[10px]" style="background:#FFF3E0;color:#E65100;">Dihapus ortu {{ $chat->cleared_at->translatedFormat('d M Y, H:i') }}</span>
                    @else
                        <span class="badge badge-teal text-[10px]">Aktif</span>
                    @endif
                </div>
            </div>

            @if($chat->cleared_at)
                <div class="alert-danger text-sm flex items-start gap-2">
                    <svg class="h-5 w-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Orang tua menghapus riwayat dari tampilan mereka pada {{ $chat->cleared_at->translatedFormat('d F Y, H:i') }}. Riwayat lengkap tetap tersimpan di bawah.</span>
                </div>
            @endif

            <div class="admin-chat-panel rounded-2xl border border-black/5 overflow-hidden shadow-sm flex flex-col bg-white card" data-tour="chat-thread">
                <div id="admin-chat-thread" class="admin-chat-thread flex-1 overflow-y-auto overscroll-contain px-4 py-4">
                    @forelse($messages as $msg)
                        @if($msg->role === 'user')
                            <div class="flex justify-end mb-3">
                                <div class="flex flex-col items-end max-w-[min(78%,22rem)]">
                                    <span class="text-[10px] font-semibold mb-1 px-1" style="color:#9E9790;">Orang Tua</span>
                                    <div class="admin-chat-bubble admin-chat-bubble-user shadow-sm">
                                        <p class="text-[14px] leading-[1.45] whitespace-pre-wrap break-words text-left">{{ $msg->content }}</p>
                                        <div class="flex justify-end mt-1">
                                            <span class="text-[10px] leading-none opacity-70">{{ $msg->created_at->translatedFormat('d M Y, H:i') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="flex justify-start mb-3">
                                <div class="flex flex-col items-start max-w-[min(78%,22rem)]">
                                    <span class="text-[10px] font-semibold mb-1 px-1" style="color:#9E9790;">Asisten AI</span>
                                    <div class="admin-chat-bubble admin-chat-bubble-ai shadow-sm">
                                        <p class="text-[14px] leading-[1.45] whitespace-pre-wrap break-words text-left">{{ $msg->content }}</p>
                                        <div class="flex justify-end mt-1">
                                            <span class="text-[10px] leading-none opacity-60">{{ $msg->created_at->translatedFormat('d M Y, H:i') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @empty
                        <div class="flex flex-col items-center justify-center text-center py-16 px-4">
                            <div class="h-14 w-14 rounded-2xl flex items-center justify-center mb-3 shadow-sm" style="background:#D0E8E8;">
                                <svg class="h-7 w-7" style="color:#1A6B6B;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                            </div>
                            <p class="text-sm font-semibold mb-1" style="color:#2C2C2C;">Belum ada pesan</p>
                            <p class="text-xs max-w-xs" style="color:#9E9790;">Percakapan antara orang tua dan asisten AI akan muncul di sini.</p>
                        </div>
                    @endforelse
                </div>

                <div class="shrink-0 px-4 py-2.5 border-t border-black/5 text-center text-[11px]" style="background:#FAF6F0;color:#9E9790;" data-tour="chat-compose">
                    Mode baca saja · {{ $messages->count() }} pesan
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <style>
        .admin-chat-panel {
            height: calc(100dvh - 13rem);
            min-height: 22rem;
        }
        .admin-chat-thread {
            background:
                radial-gradient(circle at 20% 10%, rgba(208, 232, 232, 0.35), transparent 45%),
                radial-gradient(circle at 80% 90%, rgba(245, 240, 232, 0.9), transparent 50%),
                #F5F0E8;
        }
        .admin-chat-bubble {
            padding: 0.5rem 0.75rem 0.4rem;
            border-radius: 1rem;
            width: 100%;
        }
        .admin-chat-bubble-user {
            background: #1A6B6B;
            color: #fff;
            border-bottom-right-radius: 0.25rem;
        }
        .admin-chat-bubble-ai {
            background: #FFFFFF;
            color: #2C2C2C;
            border: 1px solid rgba(0, 0, 0, 0.06);
            border-bottom-left-radius: 0.25rem;
        }
    </style>
    @if($messages->isNotEmpty())
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const el = document.getElementById('admin-chat-thread');
            if (el) el.scrollTop = el.scrollHeight;
        });
    </script>
    @endif
    @endpush
</x-app-layout>
