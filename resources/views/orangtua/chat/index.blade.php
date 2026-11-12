<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3 min-w-0" data-tour="page-header">
            <a href="{{ route('dashboard') }}"
               class="shrink-0 inline-flex items-center justify-center h-8 w-8 rounded-lg hover:bg-black/5 transition-colors"
               style="color:#1A6B6B;"
               aria-label="Kembali">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div class="h-8 w-8 rounded-lg flex items-center justify-center shrink-0" style="background: #1A6B6B;">
                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
            </div>
            <div class="min-w-0">
                <h2 class="font-bold text-lg truncate" style="color: #2C2C2C;">Chat AI</h2>
                <p class="text-xs truncate" style="color:#9E9790;">Tanya seputar perkembangan anak</p>
            </div>
        </div>
    </x-slot>

    @php
        $anakNames = $anaks->pluck('name')->filter()->values();
        $sampleName = $anakNames->first() ?? 'anak';
        $initialMessages = $messages->map(fn ($m) => [
            'id' => $m->id,
            'role' => $m->role,
            'content' => $m->role === 'assistant'
                ? \App\Support\ChatPlainText::fromMarkdown($m->content)
                : $m->content,
            'created_at' => $m->created_at->format('H:i'),
        ])->values();
    @endphp

    <div data-tour="ortu-chat-messages" class="orangtua-chat-shell mx-auto w-full max-w-3xl lg:max-w-4xl lg:flex-1 lg:min-h-0"
         x-data="orangTuaChat({
            messages: @js($initialMessages),
            storeUrl: @js(route('orangtua.chat.messages.store')),
            destroyUrl: @js(route('orangtua.chat.destroy')),
            hasTokens: @js($hasTokens ?? false),
            tokenFallbackChat: @js($tokenFallbackChat ?? 'Maaf, fitur ini sedang terbatas.'),
            sampleQuestions: @js([
                'Bagaimana perkembangan ' . $sampleName . ' bulan ini?',
                'Apa kegiatan yang akan dilakukan minggu depan?',
                'Bagaimana kehadiran anak saya bulan ini?',
            ]),
         })">

        {{-- Mobile header dengan Hapus Riwayat --}}
        <div class="lg:hidden shrink-0 sticky top-0 z-20 bg-[#FAF6F0]/95 backdrop-blur-sm border-b border-black/5 pt-[max(env(safe-area-inset-top),0px)]">
            <div class="flex items-center justify-between gap-2 px-3 py-2 min-h-[2.75rem]">
                <div class="flex items-center gap-2 min-w-0 flex-1">
                    <a href="{{ route('dashboard') }}"
                       class="shrink-0 inline-flex items-center justify-center h-8 w-8 rounded-lg hover:bg-black/5 transition-colors"
                       style="color:#1A6B6B;"
                       aria-label="Kembali">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                    <div class="h-7 w-7 rounded-lg flex items-center justify-center shrink-0" style="background: #1A6B6B;">
                        <svg class="h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h2 class="font-semibold text-base truncate leading-tight" style="color: #2C2C2C;">Chat AI</h2>
                        <p class="text-[10px] truncate leading-tight mt-0.5" style="color:#9E9790;">Tanya seputar perkembangan anak</p>
                    </div>
                </div>
                <button type="button"
                        x-show="messages.length > 0"
                        @click="showClearModal = true"
                        class="shrink-0 text-[10px] font-semibold px-2.5 py-1.5 rounded-lg border transition-colors"
                        style="color:#9E9790;border-color:rgba(0,0,0,0.08);">
                    Hapus Riwayat
                </button>
            </div>
        </div>

        {{-- Messages (scroll area) --}}
        <div class="orangtua-chat-messages overflow-y-auto overscroll-contain px-3 md:px-4"
             x-ref="messageList"
             @scroll="onScroll()">

            <div class="min-h-full flex flex-col justify-end py-3 gap-2">
                <template x-if="messages.length === 0 && !loading">
                    <div class="flex flex-col items-center justify-center text-center px-4 py-10 my-auto">
                        <div class="h-14 w-14 rounded-2xl flex items-center justify-center mb-3 shadow-sm" style="background:#D0E8E8;">
                            <svg class="h-7 w-7" style="color:#1A6B6B;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                        </div>
                        <p class="text-sm font-semibold mb-1" style="color:#2C2C2C;">Mulai percakapan</p>
                        <p class="text-xs mb-4 max-w-xs leading-relaxed" style="color:#9E9790;">Tanyakan perkembangan, kegiatan, kehadiran, atau hal seputar anak di daycare.</p>
                        <div class="flex flex-col gap-2 w-full max-w-sm" data-tour="ortu-chat-suggestions">
                            <template x-for="(q, i) in sampleQuestions" :key="i">
                                <button type="button"
                                        @click="if (!hasTokens) return; input = q; sendMessage()"
                                        :disabled="!hasTokens"
                                        class="text-left text-xs px-4 py-3 rounded-2xl transition-colors shadow-sm border border-black/5 disabled:opacity-40 disabled:cursor-not-allowed"
                                        style="background:#FFFFFF;color:#2C2C2C;"
                                        x-text="q"></button>
                            </template>
                        </div>
                    </div>
                </template>

                <template x-for="msg in messages" :key="msg.id">
                    <div class="flex w-full" :class="msg.role === 'user' ? 'justify-end' : 'justify-start'">
                        <div class="chat-bubble max-w-[min(78%,20rem)] shadow-sm"
                             :class="msg.role === 'user' ? 'chat-bubble-user' : 'chat-bubble-ai'">
                            <p class="text-[14px] leading-[1.45] whitespace-pre-wrap break-words" x-text="displayContent(msg)"></p>
                            <div class="flex justify-end mt-1">
                                <span class="text-[10px] leading-none opacity-70" x-text="msg.created_at"></span>
                            </div>
                        </div>
                    </div>
                </template>

                <template x-if="loading">
                    <div class="flex justify-start">
                        <div class="chat-bubble chat-bubble-ai shadow-sm px-4 py-3">
                            <span class="inline-flex gap-1 text-[#9E9790]">
                                <span class="animate-bounce">●</span>
                                <span class="animate-bounce" style="animation-delay:0.12s">●</span>
                                <span class="animate-bounce" style="animation-delay:0.24s">●</span>
                            </span>
                        </div>
                    </div>
                </template>

                <div x-ref="scrollAnchor" class="h-px shrink-0" aria-hidden="true"></div>
            </div>
        </div>

        {{-- Error --}}
        <div x-show="error" x-cloak class="orangtua-chat-error mx-3 px-3 py-2 rounded-xl text-xs alert-danger" x-text="error"></div>

        <div x-show="!hasTokens" x-cloak class="mx-3 mb-2 px-3 py-2 rounded-xl text-xs" style="background:#FEE2E2; color:#DC2626;" x-text="tokenFallbackChat"></div>

        {{-- Composer --}}
        <div class="orangtua-chat-composer shrink-0 border-t bg-[#FAF6F0]/95 backdrop-blur-md px-3 md:px-4" data-tour="ortu-chat-input">
            <form @submit.prevent="sendMessage()" class="orangtua-chat-form flex items-center gap-2 min-w-0">
                <div class="orangtua-chat-field flex-1 min-w-0 rounded-full border bg-white shadow-sm flex items-center overflow-hidden"
                     style="border-color:rgba(0,0,0,0.08);">
                    <input type="text"
                           x-model="input"
                           x-ref="inputField"
                           maxlength="1000"
                           placeholder="Ketik pesan..."
                           autocomplete="off"
                           autocorrect="on"
                           enterkeyhint="send"
                           :disabled="loading || !hasTokens"
                           @keydown.enter.prevent="sendMessage()"
                           @focus="onComposerFocus()"
                           @blur="onComposerBlur()"
                           class="orangtua-chat-input w-full bg-transparent border-0 focus:ring-0 focus:outline-none text-sm">
                </div>
                <button type="submit"
                        :disabled="loading || !input.trim() || !hasTokens"
                        class="orangtua-chat-send shrink-0 rounded-full flex items-center justify-center text-white shadow-md transition-all disabled:opacity-40 disabled:shadow-none hover:brightness-110 active:scale-95"
                        style="background:#1A6B6B;"
                        aria-label="Kirim pesan">
                    <svg class="h-5 w-5 translate-x-px" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M3.4 20.4 20.45 12 3.4 3.6l.85 6.72L15.5 12l-11.25 1.68-.85 6.72z"/>
                    </svg>
                </button>
            </form>
        </div>

        {{-- Clear modal --}}
        <div x-show="showClearModal" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4" style="display:none;">
            <div class="absolute inset-0 bg-gray-900/50" @click="showClearModal = false"></div>
            <div class="relative bg-white rounded-2xl shadow-xl max-w-sm w-full p-6">
                <h3 class="font-bold text-lg mb-2" style="color:#2C2C2C;">Hapus riwayat chat?</h3>
                <p class="text-sm mb-5" style="color:#6B6560;">Riwayat chat akan dihapus dari tampilan Anda. Admin sekolah tetap dapat melihat riwayat sebelumnya.</p>
                <div class="flex gap-2 justify-end">
                    <button type="button" @click="showClearModal = false" class="px-4 py-2 text-sm font-medium rounded-xl" style="color:#6B6560;">Batal</button>
                    <button type="button" @click="clearHistory()" :disabled="clearing" class="btn-primary text-sm px-4 py-2">Hapus</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <style>
        .orangtua-chat-shell {
            display: flex;
            flex-direction: column;
            min-height: 0;
        }
        @media (max-width: 1023px) {
            .orangtua-chat-shell {
                position: fixed;
                left: 0;
                right: 0;
                top: 0;
                height: 100dvh;
                bottom: auto;
                z-index: 30;
                max-width: none;
                width: 100%;
                transition: top 0.08s ease-out, height 0.08s ease-out;
            }
            .orangtua-chat-composer {
                flex: 0 0 auto;
                padding-top: 0.5rem;
                padding-bottom: max(0.5rem, env(safe-area-inset-bottom, 0px));
            }
            body.orangtua-chat-keyboard-open .orangtua-chat-composer {
                padding-bottom: 0.375rem;
            }
            body.orangtua-chat-keyboard-open {
                overflow: hidden;
                touch-action: none;
            }
        }
        @media (min-width: 1024px) {
            .orangtua-chat-shell {
                height: 100%;
                min-height: 0;
            }
            .orangtua-chat-composer {
                position: sticky;
                bottom: 0;
                z-index: 10;
            }
        }
        .orangtua-chat-messages {
            flex: 1 1 auto;
            min-height: 0;
            background:
                radial-gradient(circle at 20% 10%, rgba(208, 232, 232, 0.35), transparent 45%),
                radial-gradient(circle at 80% 90%, rgba(245, 240, 232, 0.9), transparent 50%),
                #F5F0E8;
        }
        .chat-bubble {
            padding: 0.5rem 0.75rem 0.4rem;
            border-radius: 1rem;
        }
        .chat-bubble-user {
            background: #1A6B6B;
            color: #fff;
            border-bottom-right-radius: 0.25rem;
        }
        .chat-bubble-ai {
            background: #FFFFFF;
            color: #2C2C2C;
            border: 1px solid rgba(0, 0, 0, 0.06);
            border-bottom-left-radius: 0.25rem;
        }
        .orangtua-chat-form {
            height: 2.75rem;
            min-height: 2.75rem;
            max-height: 2.75rem;
        }
        .orangtua-chat-composer {
            flex: 0 0 auto;
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
        }
        .orangtua-chat-field {
            height: 2.5rem;
            min-height: 2.5rem;
            max-height: 2.5rem;
        }
        .orangtua-chat-input {
            height: 2.5rem;
            min-height: 2.5rem;
            max-height: 2.5rem;
            line-height: 2.5rem;
            padding: 0 0.75rem 0 1rem;
            margin: 0;
            box-sizing: border-box;
            -webkit-appearance: none;
            appearance: none;
        }
        .orangtua-chat-send {
            width: 2.5rem;
            height: 2.5rem;
            min-width: 2.5rem;
            min-height: 2.5rem;
        }
        @media (min-width: 768px) {
            .orangtua-chat-form {
                height: 3rem;
                min-height: 3rem;
                max-height: 3rem;
            }
            .orangtua-chat-field,
            .orangtua-chat-input {
                height: 2.75rem;
                min-height: 2.75rem;
                max-height: 2.75rem;
                line-height: 2.75rem;
            }
            .orangtua-chat-send {
                width: 2.75rem;
                height: 2.75rem;
                min-width: 2.75rem;
                min-height: 2.75rem;
            }
        }
    </style>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('orangTuaChat', (config) => ({
                messages: config.messages || [],
                input: '',
                loading: false,
                clearing: false,
                error: '',
                showClearModal: false,
                storeUrl: config.storeUrl,
                destroyUrl: config.destroyUrl,
                hasTokens: config.hasTokens === true,
                tokenFallbackChat: config.tokenFallbackChat || 'Maaf, fitur ini sedang terbatas.',
                sampleQuestions: config.sampleQuestions || [],

                plainText(content) {
                    if (!content) return '';
                    let text = String(content);
                    text = text.replace(/```[\s\S]*?```/g, '');
                    text = text.replace(/`([^`]+)`/g, '@$1');
                    text = text.replace(/\*\*([^*]+)\*\*/g, '@$1');
                    text = text.replace(/\*([^*]+)\*/g, '@$1');
                    text = text.replace(/__([^_]+)__/g, '@$1');
                    text = text.replace(/_([^_\n]+)_/g, '@$1');
                    text = text.replace(/^#{1,6}\s+/gm, '');
                    text = text.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '@$1');
                    return text.replace(/\n{3,}/g, '\n\n').trim();
                },

                displayContent(msg) {
                    return msg.role === 'assistant' ? this.plainText(msg.content) : msg.content;
                },

                scrollToBottom(behavior = 'auto') {
                    this.$nextTick(() => {
                        requestAnimationFrame(() => {
                            const anchor = this.$refs.scrollAnchor;
                            if (anchor) {
                                anchor.scrollIntoView({ behavior, block: 'end' });
                                return;
                            }
                            const el = this.$refs.messageList;
                            if (el) el.scrollTop = el.scrollHeight;
                        });
                    });
                },

                isMobileChat() {
                    return window.matchMedia('(max-width: 1023px)').matches;
                },

                updateViewport() {
                    if (!this.isMobileChat()) {
                        this.$el.style.top = '';
                        this.$el.style.height = '';
                        document.body.classList.remove('orangtua-chat-keyboard-open');
                        return;
                    }

                    const vv = window.visualViewport;
                    if (!vv) return;

                    this.$el.style.top = `${vv.offsetTop}px`;
                    this.$el.style.height = `${vv.height}px`;
                    this.scrollToBottom('auto');
                },

                onComposerFocus() {
                    document.body.classList.add('orangtua-chat-keyboard-open');
                    [50, 150, 350].forEach((delay) => {
                        setTimeout(() => {
                            this.updateViewport();
                            this.scrollToBottom('auto');
                        }, delay);
                    });
                },

                onComposerBlur() {
                    document.body.classList.remove('orangtua-chat-keyboard-open');
                    [100, 300].forEach((delay) => {
                        setTimeout(() => this.updateViewport(), delay);
                    });
                },

                bindViewportListeners() {
                    if (!this.isMobileChat() || !window.visualViewport) return;

                    this._onViewportChange = () => this.updateViewport();
                    window.visualViewport.addEventListener('resize', this._onViewportChange);
                    window.visualViewport.addEventListener('scroll', this._onViewportChange);
                    window.addEventListener('orientationchange', this._onViewportChange);
                },

                unbindViewportListeners() {
                    if (!this._onViewportChange) return;
                    window.visualViewport?.removeEventListener('resize', this._onViewportChange);
                    window.visualViewport?.removeEventListener('scroll', this._onViewportChange);
                    window.removeEventListener('orientationchange', this._onViewportChange);
                    document.body.classList.remove('orangtua-chat-keyboard-open');
                },

                onScroll() {},

                async sendMessage() {
                    const content = this.input.trim();
                    if (!content || this.loading || !this.hasTokens) return;

                    this.error = '';
                    const tempId = 'temp-' + Date.now();
                    const sentAt = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

                    this.messages.push({
                        id: tempId,
                        role: 'user',
                        content,
                        created_at: sentAt,
                    });
                    this.input = '';
                    this.scrollToBottom('smooth');

                    this.loading = true;

                    try {
                        const res = await fetch(this.storeUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            },
                            body: JSON.stringify({ content }),
                        });
                        const data = await res.json();
                        if (!res.ok) {
                            if (data.token_exhausted && Array.isArray(data.messages) && data.messages.length) {
                                this.messages = this.messages.filter((m) => m.id !== tempId);
                                data.messages.forEach((m) => {
                                    this.messages.push({
                                        id: m.id,
                                        role: m.role,
                                        content: m.role === 'assistant' ? this.plainText(m.content) : m.content,
                                        created_at: m.created_at
                                            ? new Date(m.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
                                            : new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }),
                                    });
                                });
                                this.hasTokens = false;
                                this.scrollToBottom('smooth');
                                return;
                            }
                            this.messages = this.messages.filter((m) => m.id !== tempId);
                            this.error = data.error || 'Gagal mengirim pesan.';
                            this.input = content;
                            return;
                        }

                        this.messages = this.messages.filter((m) => m.id !== tempId);
                        (data.messages || []).forEach((m) => {
                            this.messages.push({
                                id: m.id,
                                role: m.role,
                                content: m.role === 'assistant' ? this.plainText(m.content) : m.content,
                                created_at: new Date(m.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }),
                            });
                        });
                        this.scrollToBottom('smooth');
                    } catch (e) {
                        this.messages = this.messages.filter((m) => m.id !== tempId);
                        this.error = 'Gagal mengirim pesan. Periksa koneksi internet.';
                        this.input = content;
                    } finally {
                        this.loading = false;
                        this.scrollToBottom('smooth');
                    }
                },

                async clearHistory() {
                    if (this.clearing) return;
                    this.clearing = true;
                    this.error = '';
                    try {
                        const res = await fetch(this.destroyUrl, {
                            method: 'DELETE',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            },
                        });
                        if (!res.ok) {
                            const data = await res.json();
                            this.error = data.error || 'Gagal menghapus riwayat.';
                            return;
                        }
                        this.messages = [];
                        this.showClearModal = false;
                    } catch (e) {
                        this.error = 'Gagal menghapus riwayat.';
                    } finally {
                        this.clearing = false;
                    }
                },

                init() {
                    this.bindViewportListeners();
                    this.updateViewport();
                    this.scrollToBottom();
                    setTimeout(() => this.scrollToBottom(), 100);
                },

                destroy() {
                    this.unbindViewportListeners();
                },
            }));
        });
    </script>
    @endpush
</x-app-layout>
