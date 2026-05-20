{{--
    Floating AI Copilot widget. Drop <x-ai-copilot-widget /> into admin layout.
    Uses Alpine.js (already loaded in admin layout).
--}}
<div x-data="aiCopilotWidget()" x-cloak>
    {{-- Floating button --}}
    <button @click="open = true; $nextTick(() => $refs.input.focus())"
            x-show="!open"
            class="fixed bottom-6 right-6 z-40 group">
        <div class="relative">
            <div class="absolute inset-0 bg-gradient-to-r from-violet-500 to-primary-600 rounded-full blur-lg opacity-50 group-hover:opacity-75 transition-opacity"></div>
            <div class="relative w-14 h-14 rounded-full bg-gradient-to-r from-violet-600 to-primary-600 flex items-center justify-center shadow-xl hover:scale-105 transition-transform">
                <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
            </div>
        </div>
    </button>

    {{-- Drawer --}}
    <div x-show="open"
         x-transition:enter="transform transition ease-out duration-200"
         x-transition:enter-start="translate-y-8 opacity-0"
         x-transition:enter-end="translate-y-0 opacity-100"
         x-transition:leave="transform transition ease-in duration-150"
         x-transition:leave-start="translate-y-0 opacity-100"
         x-transition:leave-end="translate-y-8 opacity-0"
         class="fixed bottom-6 right-6 z-50 w-96 max-w-[90vw] bg-white dark:bg-dark-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-dark-700 flex flex-col"
         style="height: 540px;">

        {{-- Header --}}
        <div class="px-4 py-3 border-b border-gray-100 dark:border-dark-700 bg-gradient-to-r from-violet-600 to-primary-600 rounded-t-2xl flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <p class="text-white font-semibold text-sm">AI Copilot</p>
                    <p class="text-white/70 text-xs">Sorularını yaz, sayfada işini hızlandır</p>
                </div>
            </div>
            <button @click="open = false" class="p-1 rounded-lg hover:bg-white/10 text-white">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Messages --}}
        <div class="flex-1 overflow-y-auto p-3 space-y-3" x-ref="scroll">
            <template x-for="(msg, i) in messages" :key="i">
                <div :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                    <div :class="msg.role === 'user'
                            ? 'bg-primary-600 text-white rounded-2xl rounded-tr-none px-3 py-2 max-w-[80%]'
                            : 'bg-gray-100 dark:bg-dark-700 text-gray-800 dark:text-gray-200 rounded-2xl rounded-tl-none px-3 py-2 max-w-[80%]'">
                        <p class="text-sm whitespace-pre-wrap" x-text="msg.content"></p>
                    </div>
                </div>
            </template>

            <template x-if="loading">
                <div class="flex justify-start">
                    <div class="bg-gray-100 dark:bg-dark-700 rounded-2xl rounded-tl-none px-3 py-2">
                        <div class="flex space-x-1">
                            <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:0ms"></span>
                            <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:150ms"></span>
                            <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:300ms"></span>
                        </div>
                    </div>
                </div>
            </template>

            <template x-if="messages.length === 0 && !loading">
                <div class="text-center py-8">
                    <div class="w-12 h-12 mx-auto rounded-2xl bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center mb-3">
                        <svg class="w-6 h-6 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-300 font-medium">Hızlı başla:</p>
                    <div class="mt-3 space-y-1.5">
                        <template x-for="(s, i) in quick" :key="i">
                            <button @click="ask(s)" class="block w-full text-left text-xs px-3 py-2 bg-gray-50 dark:bg-dark-700 hover:bg-gray-100 dark:hover:bg-dark-600 text-gray-700 dark:text-gray-300 rounded-lg" x-text="s"></button>
                        </template>
                    </div>
                </div>
            </template>
        </div>

        {{-- Input --}}
        <form @submit.prevent="send()" class="border-t border-gray-100 dark:border-dark-700 p-3 flex space-x-2">
            <input x-ref="input" x-model="draft"
                   placeholder="Bir şey sor… (örn. bu hafta hot lead'lerimi listele)"
                   class="flex-1 px-3 py-2 text-sm bg-gray-50 dark:bg-dark-700 border-0 rounded-xl text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-violet-500">
            <button type="submit" :disabled="loading || !draft.trim()" class="px-3 py-2 bg-gradient-to-r from-violet-600 to-primary-600 text-white rounded-xl disabled:opacity-50">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                </svg>
            </button>
        </form>
    </div>
</div>

<script>
function aiCopilotWidget() {
    return {
        open: false,
        loading: false,
        draft: '',
        messages: [],
        quick: [
            'Bu hafta en sıcak (hot) lead\'lerimi listele',
            'Kadıköy\'de 3+1, 5M altı ilan göster',
            'Bugün arayacağım kişileri öner',
            'Bu lead için bir takip mesajı yaz',
        ],
        async ask(text) {
            this.draft = text;
            await this.send();
        },
        async send() {
            const m = (this.draft || '').trim();
            if (!m || this.loading) return;
            this.messages.push({ role: 'user', content: m });
            this.draft = '';
            this.loading = true;
            this.scroll();
            try {
                const res = await fetch('{{ route("admin.ai.copilot.chat") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                    body: JSON.stringify({
                        message: m,
                        history: this.messages.slice(-10).map(x => ({ role: x.role, content: x.content })),
                    }),
                });
                const data = await res.json();
                this.messages.push({ role: 'assistant', content: data.message || 'Yanıt alınamadı.' });
            } catch (e) {
                this.messages.push({ role: 'assistant', content: 'Bağlantı hatası: ' + e.message });
            }
            this.loading = false;
            this.scroll();
        },
        scroll() {
            this.$nextTick(() => {
                if (this.$refs.scroll) this.$refs.scroll.scrollTop = this.$refs.scroll.scrollHeight;
            });
        },
    };
}
</script>
