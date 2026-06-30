@auth
    <div
        x-data="stesyChatbot()"
        x-cloak
        class="fixed inset-0 z-[260] pointer-events-none"
        @keydown.escape.window="open = false"
    >
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-[0.98]"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-[0.98]"
            class="pointer-events-auto fixed inset-x-4 top-[11vh] mx-auto flex h-[min(78vh,720px)] max-w-[850px] origin-top flex-col overflow-hidden rounded-[1.35rem] border border-white/80 bg-slate-50/95 shadow-[0_30px_90px_-35px_rgba(15,23,42,0.52),inset_0_1px_0_rgba(255,255,255,0.8)] backdrop-blur-xl sm:top-[12vh]"
            style="display: none;"
        >
            <div class="relative flex h-[60px] shrink-0 items-center justify-between border-b border-slate-200/80 bg-white px-5 text-slate-900">
                <div class="flex min-w-0 items-center gap-3">
                    <div class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-[#303481]/10 text-[#303481] shadow-[inset_0_1px_0_rgba(255,255,255,0.8)]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3l1.5 4.5L18 9l-4.5 1.5L12 15l-1.5-4.5L6 9l4.5-1.5L12 3z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 15l.8 2.2L22 18l-2.2.8L19 21l-.8-2.2L16 18l2.2-.8L19 15z" />
                        </svg>
                    </div>
                    <div class="flex min-w-0 items-center gap-3">
                        <h2 class="truncate text-lg font-extrabold tracking-tight">STESY Assistant</h2>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-[#303481] px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-white">
                            <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-white/70"></span>
                            Beta
                        </span>
                    </div>
                </div>
                <button
                    type="button"
                    class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 active:scale-[0.98]"
                    @click="open = false"
                    aria-label="Tutup chat"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6L6 18" />
                    </svg>
                </button>
            </div>

            <div class="min-h-0 flex-1 overflow-y-auto px-5 py-4" x-ref="messages">
                <template x-for="message in messages" :key="message.id">
                    <div class="stesy-chat-message mb-4 flex items-start gap-3" :class="message.role === 'user' ? 'justify-end' : 'justify-start'">
                        <template x-if="message.role !== 'user'">
                            <div class="mt-2 inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#303481] text-white shadow-[0_12px_24px_-14px_rgba(48,52,129,0.8)]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8V4H8" />
                                    <rect x="5" y="8" width="14" height="10" rx="3" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h.01M15 12h.01M9 16h6" />
                                </svg>
                            </div>
                        </template>
                        <div
                            class="group/message inline-flex h-auto min-h-0 w-fit flex-col overflow-hidden rounded-2xl text-sm leading-5 shadow-sm"
                            :class="message.role === 'user'
                                ? 'max-w-[42%] rounded-br-md bg-[#303481] px-3 py-2 text-white'
                                : (message.chart
                                    ? 'stesy-assistant-bubble w-full max-w-[88%] rounded-bl-md border border-slate-200 bg-white text-slate-700'
                                    : 'stesy-assistant-bubble max-w-[62%] rounded-bl-md border border-slate-200 bg-white text-slate-700')"
                        >
                            <template x-if="message.role === 'user'">
                                <span class="m-0 block whitespace-pre-line p-0" x-text="message.text"></span>
                            </template>

                            <template x-if="message.role !== 'user'">
                                <div class="relative">
                                    <div class="stesy-rich-reply px-4 py-3">
                                        <template x-if="message.isTyping">
                                            <span class="block whitespace-pre-line" x-text="message.displayText || message.text"></span>
                                        </template>
                                        <template x-if="!message.isTyping">
                                            <div x-html="formatReply(message.text)"></div>
                                        </template>
                                    </div>
                                    <template x-if="message.chart">
                                        <div class="px-4 pb-3">
                                            <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-3">
                                                <p class="mb-2 truncate text-xs font-bold text-slate-700" x-text="message.chart.title"></p>
                                                <div class="relative h-[230px] w-full">
                                                    <canvas :id="'stesy-chart-' + message.id"></canvas>
                                                </div>
                                                <p class="mt-2 text-[11px] font-medium text-slate-400"
                                                   x-text="message.chart.agg + ' per ' + (message.chart.granularity === 'hourly' ? 'jam' : 'hari') + ' • satuan ' + (message.chart.unit || '-')"></p>
                                            </div>
                                        </div>
                                    </template>
                                    <span
                                        x-show="message.isTyping"
                                        class="stesy-type-caret absolute bottom-3 inline-block h-4 w-1 rounded-full bg-current"
                                        aria-hidden="true"
                                    ></span>
                                    <div
                                        x-show="!message.isTyping && message.id !== 'welcome'"
                                        x-transition:enter="transition duration-300 ease-out"
                                        x-transition:enter-start="opacity-0 translate-y-1"
                                        x-transition:enter-end="opacity-100 translate-y-0"
                                        class="flex items-center justify-end border-t border-slate-100 bg-slate-50/70 px-3 py-2"
                                    >
                                        <button
                                            type="button"
                                            class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-semibold text-slate-500 transition duration-300 hover:bg-white hover:text-[#303481] active:scale-[0.97]"
                                            @click="copyMessage(message)"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                                <rect x="8" y="8" width="11" height="11" rx="2" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 15H4a2 2 0 01-2-2V5a2 2 0 012-2h8a2 2 0 012 2v1" />
                                            </svg>
                                            <span x-text="message.copied ? 'Tersalin' : 'Salin'"></span>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                <div x-show="messages.length === 1" class="ml-[52px] flex max-w-[680px] flex-wrap gap-2 pb-1">
                    <template x-for="prompt in prompts" :key="prompt">
                        <button
                            type="button"
                            class="group inline-flex max-w-[220px] items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-2 text-left text-xs font-semibold leading-5 text-slate-700 shadow-sm transition duration-300 hover:-translate-y-0.5 hover:border-[#303481]/25 hover:bg-[#303481]/5 active:scale-[0.98]"
                            @click="ask(prompt)"
                        >
                            <span class="truncate" x-text="prompt"></span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0 text-slate-400 transition group-hover:translate-x-0.5 group-hover:text-[#303481]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6" />
                            </svg>
                        </button>
                    </template>
                </div>

                <div
                    x-show="loading"
                    x-transition:enter="transition duration-300 ease-out"
                    x-transition:enter-start="opacity-0 translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition duration-200 ease-in"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 translate-y-1"
                    class="mb-4 flex items-start gap-3"
                    aria-live="polite"
                >
                    <div class="stesy-loading-avatar mt-2 inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#303481] text-white shadow-[0_12px_24px_-14px_rgba(48,52,129,0.8)]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8V4H8" />
                            <rect x="5" y="8" width="14" height="10" rx="3" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h.01M15 12h.01M9 16h6" />
                        </svg>
                    </div>
                    <div class="stesy-loading-bubble rounded-2xl rounded-bl-md border border-slate-200 bg-white px-4 py-3 text-slate-600 shadow-sm">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold tracking-wide text-slate-500">Menyusun jawaban</span>
                            <span class="flex items-center gap-1" aria-hidden="true">
                                <span class="stesy-loading-dot"></span>
                                <span class="stesy-loading-dot"></span>
                                <span class="stesy-loading-dot"></span>
                            </span>
                        </div>
                        <div class="mt-3 space-y-2">
                            <span class="stesy-loading-line block h-2 w-44 rounded-full bg-slate-100"></span>
                            <span class="stesy-loading-line block h-2 w-32 rounded-full bg-slate-100"></span>
                        </div>
                    </div>
                </div>
            </div>

            <form class="shrink-0 bg-slate-50 px-5 py-4" @submit.prevent="ask(input)">
                <label for="stesy-chatbot-input" class="mb-2 block text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Pesan</label>
                <div class="flex min-h-[72px] items-center gap-3 rounded-[1.35rem] border border-[#303481]/20 bg-white px-4 py-2 shadow-[0_16px_42px_-30px_rgba(48,52,129,0.45),inset_0_1px_0_rgba(255,255,255,0.9)] transition duration-300 focus-within:border-[#303481] focus-within:shadow-[0_18px_48px_-32px_rgba(48,52,129,0.68),inset_0_1px_0_rgba(255,255,255,0.9)]">
                    <textarea
                        id="stesy-chatbot-input"
                        x-model="input"
                        rows="1"
                        class="max-h-28 min-h-10 flex-1 resize-none border-0 bg-transparent px-0 py-2 text-sm leading-6 text-slate-800 placeholder:text-slate-400 focus:ring-0"
                        placeholder="Tulis pertanyaan..."
                    ></textarea>
                    <button
                        type="button"
                        class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-slate-400 transition hover:bg-slate-100 hover:text-[#303481] active:scale-[0.96]"
                        aria-label="Input suara"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3a3 3 0 00-3 3v6a3 3 0 006 0V6a3 3 0 00-3-3z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7 7 0 01-14 0M12 18v3M9 21h6" />
                        </svg>
                    </button>
                    <button
                        type="submit"
                        class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-[#303481] transition hover:bg-[#303481]/10 active:scale-[0.96] disabled:cursor-not-allowed disabled:text-slate-300"
                        :disabled="loading || input.trim().length === 0"
                        aria-label="Kirim pesan"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M22 2L11 13" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M22 2l-7 20-4-9-9-4 20-7z" />
                        </svg>
                    </button>
                </div>
                <p x-show="error" class="mt-2 text-xs font-medium text-red-600" x-text="error"></p>
            </form>
        </div>

        <button
            type="button"
            class="pointer-events-auto fixed bottom-5 right-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-[#303481] text-white shadow-[0_18px_45px_-22px_rgba(48,52,129,0.9),inset_0_1px_0_rgba(255,255,255,0.22)] transition duration-300 hover:-translate-y-0.5 hover:bg-[#10134B] active:scale-[0.97] sm:bottom-6 sm:right-6 sm:h-16 sm:w-36"
            @click="open = !open"
            :aria-label="open ? 'Tutup STESY Assistant' : 'Buka STESY Assistant'"
        >
            <span class="absolute -right-0.5 -top-0.5 h-4 w-4 rounded-full border-2 border-white bg-[#303481]" x-show="!open"></span>
            <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 sm:mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h7M5 19l3.4-2H18a3 3 0 003-3V7a3 3 0 00-3-3H6a3 3 0 00-3 3v7a3 3 0 003 3" />
            </svg>
            <svg x-show="open" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 sm:mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6L6 18" />
            </svg>
            <span class="hidden text-sm font-bold sm:inline" x-text="open ? 'Tutup' : 'Assistant'"></span>
        </button>
    </div>

    <style>
        .stesy-loading-avatar {
            position: relative;
            isolation: isolate;
        }

        .stesy-chat-message {
            animation: stesy-message-in 320ms cubic-bezier(0.16, 1, 0.3, 1) both;
            will-change: transform, opacity;
        }

        .stesy-type-caret {
            animation: stesy-caret 900ms cubic-bezier(0.16, 1, 0.3, 1) infinite;
        }

        .stesy-assistant-bubble {
            box-shadow:
                0 18px 45px -34px rgba(15, 23, 42, 0.42),
                inset 0 1px 0 rgba(255, 255, 255, 0.86);
        }

        .stesy-rich-reply {
            min-width: 220px;
            white-space: normal;
        }

        .stesy-rich-reply p {
            margin: 0;
            line-height: 1.6;
        }

        .stesy-rich-reply p + p,
        .stesy-rich-reply p + .stesy-response-list,
        .stesy-rich-reply .stesy-response-list + p {
            margin-top: 0.65rem;
        }

        .stesy-rich-reply strong {
            color: #1f2937;
            font-weight: 800;
        }

        .stesy-response-title {
            color: #111827;
            font-weight: 800;
            letter-spacing: -0.01em;
        }

        .stesy-response-list {
            display: grid;
            gap: 0.45rem;
            margin: 0.7rem 0 0;
        }

        .stesy-response-item {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr);
            gap: 0.55rem;
            align-items: start;
            line-height: 1.55;
        }

        .stesy-response-item:has(.is-dot) {
            margin-left: 1rem;
        }

        .stesy-response-marker {
            display: inline-flex;
            min-width: 1.35rem;
            height: 1.35rem;
            align-items: center;
            justify-content: center;
            border-radius: 9999px;
            background: rgba(48, 52, 129, 0.08);
            color: #303481;
            font-size: 0.68rem;
            font-weight: 800;
            line-height: 1;
            transform: translateY(0.1rem);
        }

        .stesy-response-marker.is-dot {
            min-width: 0.42rem;
            width: 0.42rem;
            height: 0.42rem;
            margin-top: 0.45rem;
            background: #303481;
        }

        .stesy-loading-avatar::before {
            content: "";
            position: absolute;
            inset: -4px;
            z-index: -1;
            border-radius: 9999px;
            border: 1px solid rgba(48, 52, 129, 0.2);
            animation: stesy-loading-ring 1.5s cubic-bezier(0.16, 1, 0.3, 1) infinite;
        }

        .stesy-loading-bubble {
            min-width: 230px;
        }

        .stesy-loading-dot {
            width: 5px;
            height: 5px;
            border-radius: 9999px;
            background: #303481;
            opacity: 0.45;
            animation: stesy-loading-dot 1.1s cubic-bezier(0.16, 1, 0.3, 1) infinite;
        }

        .stesy-loading-dot:nth-child(2) {
            animation-delay: 120ms;
        }

        .stesy-loading-dot:nth-child(3) {
            animation-delay: 240ms;
        }

        .stesy-loading-line {
            position: relative;
            overflow: hidden;
        }

        .stesy-loading-line::after {
            content: "";
            position: absolute;
            inset: 0;
            transform: translateX(-110%);
            background: linear-gradient(90deg, transparent, rgba(48, 52, 129, 0.12), transparent);
            animation: stesy-loading-shimmer 1.35s cubic-bezier(0.16, 1, 0.3, 1) infinite;
        }

        .stesy-loading-line:nth-child(2)::after {
            animation-delay: 160ms;
        }

        @keyframes stesy-loading-dot {
            0%, 80%, 100% {
                opacity: 0.35;
                transform: translateY(0) scale(0.92);
            }
            35% {
                opacity: 1;
                transform: translateY(-3px) scale(1);
            }
        }

        @keyframes stesy-loading-ring {
            0% {
                opacity: 0.52;
                transform: scale(0.92);
            }
            100% {
                opacity: 0;
                transform: scale(1.42);
            }
        }

        @keyframes stesy-loading-shimmer {
            100% {
                transform: translateX(110%);
            }
        }

        @keyframes stesy-message-in {
            from {
                opacity: 0;
                transform: translateY(8px) scale(0.985);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes stesy-caret {
            0%, 55% {
                opacity: 0.95;
            }
            56%, 100% {
                opacity: 0.18;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .stesy-chat-message,
            .stesy-type-caret,
            .stesy-loading-avatar::before,
            .stesy-loading-dot,
            .stesy-loading-line::after {
                animation: none;
            }
        }
    </style>

    <script>
        function stesyChatbot() {
            return {
                open: false,
                input: '',
                loading: false,
                error: '',
                revealTimers: new Map(),
                chartInstances: new Map(),
                _chartJsPromise: null,
                prompts: [
                    'Ada berapa logger yang offline?',
                    'Pos mana saja yang sedang hujan?',
                    'Grafik tinggi muka air pos AWLR Sinduadi seminggu',
                    'Bandingkan dua pos AWLR',
                    'Rincian curah hujan per jam hari ini'
                ],
                messages: [
                    {
                        id: 'welcome',
                        role: 'assistant',
                        text: 'Selamat datang. Saya STESY Assistant. Ada yang bisa dibantu terkait pemantauan logger Anda?'
                    }
                ],
                ask(value) {
                    const text = (value || '').trim();
                    if (!text || this.loading) return;

                    this.error = '';
                    this.input = '';
                    this.messages.push({
                        id: `user-${Date.now()}`,
                        role: 'user',
                        text
                    });
                    this.loading = true;
                    this.scrollDown();

                    const history = this.messages
                        .filter((m) => m.id !== 'welcome')
                        .slice(-10)
                        .map((m) => ({
                            role: m.role,
                            text: String(m.text || '').slice(0, 650)
                        }));

                    this.sendMessage(text, history).finally(() => {
                        this.loading = false;
                        this.scrollDown();
                    });
                },
                async sendMessage(text, history) {
                    let res;
                    try {
                        res = await fetch('{{ route('chatbot.stream') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'text/event-stream',
                            },
                            body: JSON.stringify({ message: text, messages: history }),
                        });
                    } catch (_) {
                        return this.fallbackAsk(text, history);
                    }

                    if (!res.ok || !res.body) {
                        return this.fallbackAsk(text, history);
                    }

                    const message = this.pushAssistantReply('', null, true);
                    this.loading = false;
                    const reader = res.body.getReader();
                    const decoder = new TextDecoder();
                    let buf = '';

                    try {
                        while (true) {
                            const { value, done } = await reader.read();
                            if (done) break;
                            buf += decoder.decode(value, { stream: true });
                            let idx;
                            while ((idx = buf.indexOf('\n\n')) !== -1) {
                                const raw = buf.slice(0, idx);
                                buf = buf.slice(idx + 2);
                                const ev = (raw.match(/^event: (.*)$/m) || [])[1];
                                const dataLine = (raw.match(/^data: (.*)$/m) || [])[1];
                                if (!dataLine) continue;
                                let payload;
                                try { payload = JSON.parse(dataLine); } catch (_) { continue; }

                                if (ev === 'token') {
                                    message.text += payload.text;
                                    message.displayText += payload.text;
                                    this.updateMessage(message.id, { text: message.text, displayText: message.displayText, isTyping: true });
                                    this.scrollDown();
                                } else if (ev === 'chart') {
                                    message.chart = payload.chart;
                                    this.updateMessage(message.id, { chart: payload.chart });
                                    this.$nextTick(() => {
                                        this.renderChart(message.id, payload.chart);
                                        this.scrollDown();
                                    });
                                } else if (ev === 'done') {
                                    // payload.source is available but not yet consumed by the UI — skipping for now.
                                    this.updateMessage(message.id, { isTyping: false });
                                } else if (ev === 'error') {
                                    this.updateMessage(message.id, { text: payload.message, displayText: payload.message, isTyping: false });
                                }
                            }
                        }
                    } catch (_) {
                        this.updateMessage(message.id, { isTyping: false });
                    }
                },
                async fallbackAsk(text, history) {
                    const startedAt = Date.now();
                    try {
                        const response = await fetch('{{ route('chatbot.ask') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify({ message: text, messages: history }),
                        });
                        if (!response.ok) {
                            const err = await response.json().catch(() => ({}));
                            throw new Error(err.message || 'Chatbot belum bisa merespons.');
                        }
                        const payload = await response.json();
                        await this.waitForMinimumLoading(startedAt);
                        this.pushAssistantReply(payload.reply || this.resolveReply(text), payload.chart || null);
                    } catch (error) {
                        await this.waitForMinimumLoading(startedAt);
                        this.error = error.message || 'Koneksi chatbot terganggu.';
                        this.pushAssistantReply(this.resolveReply(text));
                    }
                },
                pushAssistantReply(reply, chart = null, streaming = false) {
                    const fullText = String(reply || '').trim();
                    const message = {
                        id: `assistant-${Date.now()}`,
                        role: 'assistant',
                        text: fullText,
                        displayText: chart ? fullText : '',
                        isTyping: streaming || (!chart && fullText.length > 0),
                        copied: false,
                        chart: chart
                    };

                    this.messages.push(message);
                    this.scrollDown();

                    if (!streaming) {
                        if (chart) {
                            // Pesan grafik: tampilkan penjelasan langsung lalu render kanvas.
                            this.$nextTick(() => {
                                this.renderChart(message.id, chart);
                                this.scrollDown();
                            });
                            return message;
                        }

                        if (fullText) {
                            this.revealAssistantMessage(message.id, fullText);
                        } else {
                            this.updateMessage(message.id, { isTyping: false });
                        }
                    }

                    return message;
                },
                ensureChartJs() {
                    if (window.Chart) return Promise.resolve(window.Chart);
                    if (this._chartJsPromise) return this._chartJsPromise;

                    this._chartJsPromise = new Promise((resolve, reject) => {
                        const s = document.createElement('script');
                        s.src = 'https://cdn.jsdelivr.net/npm/chart.js';
                        s.async = true;
                        s.onload = () => resolve(window.Chart);
                        s.onerror = () => reject(new Error('Gagal memuat pustaka grafik.'));
                        document.head.appendChild(s);
                    });

                    return this._chartJsPromise;
                },
                renderChart(messageId, chart) {
                    this.ensureChartJs().then((Chart) => {
                        const canvas = document.getElementById('stesy-chart-' + messageId);
                        if (!canvas || !Chart) return;

                        if (this.chartInstances.has(messageId)) {
                            this.chartInstances.get(messageId).destroy();
                        }

                        const accent = '#303481';
                        const instance = new Chart(canvas.getContext('2d'), {
                            type: chart.type === 'bar' ? 'bar' : 'line',
                            data: {
                                labels: chart.labels,
                                datasets: [{
                                    label: (chart.param || 'Nilai') + (chart.unit ? ' (' + chart.unit + ')' : ''),
                                    data: chart.values,
                                    borderColor: accent,
                                    backgroundColor: chart.type === 'bar' ? 'rgba(48,52,129,0.55)' : 'rgba(48,52,129,0.12)',
                                    borderWidth: 2,
                                    fill: chart.type !== 'bar',
                                    tension: 0.35,
                                    pointRadius: chart.values.length > 40 ? 0 : 3,
                                    pointHoverRadius: 4
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                interaction: { mode: 'index', intersect: false },
                                plugins: {
                                    legend: { display: true, labels: { boxWidth: 12, font: { size: 11 } } },
                                    tooltip: { enabled: true }
                                },
                                scales: {
                                    x: { ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 8, font: { size: 10 } }, grid: { display: false } },
                                    y: { beginAtZero: false, ticks: { font: { size: 10 } }, grid: { color: 'rgba(15,23,42,0.06)' } }
                                }
                            }
                        });

                        this.chartInstances.set(messageId, instance);
                    }).catch(() => {
                        /* Pustaka grafik gagal dimuat — penjelasan teks tetap tampil. */
                    });
                },
                revealAssistantMessage(messageId, fullText) {
                    if (!fullText) {
                        this.updateMessage(messageId, { isTyping: false });
                        return;
                    }

                    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                    if (prefersReducedMotion) {
                        this.updateMessage(messageId, {
                            displayText: fullText,
                            isTyping: false
                        });
                        return;
                    }

                    const totalLength = fullText.length;
                    const chunkSize = Math.max(2, Math.ceil(totalLength / 180));
                    let cursor = 0;
                    let ticks = 0;

                    const timer = window.setInterval(() => {
                        ticks++;
                        cursor = Math.min(cursor + chunkSize, totalLength);
                        this.updateMessage(messageId, {
                            displayText: fullText.slice(0, cursor)
                        });

                        if (cursor >= totalLength) {
                            window.clearInterval(timer);
                            this.revealTimers.delete(messageId);
                            this.updateMessage(messageId, {
                                displayText: fullText,
                                isTyping: false
                            });
                        }

                        if (ticks % 4 === 0 || cursor >= totalLength) {
                            this.scrollDown();
                        }
                    }, 18);

                    this.revealTimers.set(messageId, timer);
                },
                updateMessage(messageId, changes) {
                    const index = this.messages.findIndex((item) => item.id === messageId);
                    if (index === -1) return;

                    this.messages.splice(index, 1, {
                        ...this.messages[index],
                        ...changes
                    });
                },
                formatReply(text) {
                    const safeText = this.escapeHtml(String(text || ''));
                    const lines = safeText.split(/\r?\n/);
                    const chunks = [];
                    let listItems = [];

                    const flushList = () => {
                        if (listItems.length === 0) return;
                        chunks.push(`<div class="stesy-response-list">${listItems.join('')}</div>`);
                        listItems = [];
                    };

                    lines.forEach((rawLine, index) => {
                        const line = rawLine.trim();

                        if (!line) {
                            flushList();
                            return;
                        }

                        const numbered = line.match(/^(\d+)\.\s+(.+)$/);
                        if (numbered) {
                            listItems.push(
                                `<div class="stesy-response-item"><span class="stesy-response-marker">${numbered[1]}</span><span>${this.inlineFormat(numbered[2])}</span></div>`
                            );
                            return;
                        }

                        const bullet = line.match(/^-\s+(.+)$/);
                        if (bullet) {
                            listItems.push(
                                `<div class="stesy-response-item"><span class="stesy-response-marker is-dot"></span><span>${this.inlineFormat(bullet[1])}</span></div>`
                            );
                            return;
                        }

                        flushList();
                        const className = index === 0 || /:$/.test(line) ? ' class="stesy-response-title"' : '';
                        chunks.push(`<p${className}>${this.inlineFormat(line)}</p>`);
                    });

                    flushList();
                    return chunks.join('');
                },
                inlineFormat(text) {
                    return String(text)
                        .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
                        .replace(/\*(.+?)\*/g, '<strong>$1</strong>');
                },
                escapeHtml(value) {
                    return String(value)
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#039;');
                },
                async copyMessage(message) {
                    const text = message.text || '';
                    if (!text) return;

                    try {
                        await navigator.clipboard.writeText(text);
                        message.copied = true;
                        window.setTimeout(() => {
                            message.copied = false;
                        }, 1400);
                    } catch (_) {
                        const textarea = document.createElement('textarea');
                        textarea.value = text;
                        textarea.setAttribute('readonly', '');
                        textarea.style.position = 'fixed';
                        textarea.style.opacity = '0';
                        document.body.appendChild(textarea);
                        textarea.select();
                        document.execCommand('copy');
                        document.body.removeChild(textarea);
                        message.copied = true;
                        window.setTimeout(() => {
                            message.copied = false;
                        }, 1400);
                    }
                },
                waitForMinimumLoading(startedAt) {
                    const minimumMs = 450;
                    const remaining = Math.max(0, minimumMs - (Date.now() - startedAt));

                    return new Promise((resolve) => setTimeout(resolve, remaining));
                },
                resolveReply(text) {
                    const query = text.toLowerCase();

                    if (query.includes('real') || query.includes('monitoring') || query.includes('data')) {
                        return 'Untuk data real-time, buka menu Realtime Monitoring. Pilih pos atau kategori logger, lalu cek nilai sensor terakhir, waktu update, dan status koneksinya.';
                    }

                    if (query.includes('offline') || query.includes('putus') || query.includes('status')) {
                        return 'Status offline biasanya berarti logger belum mengirim data terbaru atau koneksi perangkat terputus. Cek waktu data terakhir, baterai, dan jaringan di halaman detail perangkat.';
                    }

                    if (query.includes('peta') || query.includes('lokasi') || query.includes('pos')) {
                        return 'Untuk melihat lokasi pos, buka menu Peta. Marker menunjukkan posisi logger dan bisa dibuka untuk melihat informasi pos terkait.';
                    }

                    if (query.includes('siaga') || query.includes('banjir') || query.includes('hujan')) {
                        return 'Level siaga mengikuti ambang batas yang dikonfigurasi pada data AWLR atau ARR. Cek halaman detail pos untuk melihat klasifikasi dan parameter pendukung.';
                    }

                    return 'Koneksi ke layanan asisten sedang terganggu. Silakan coba kirim ulang pertanyaan Anda dalam beberapa saat. Untuk sementara, status logger dan data pos dapat ditinjau melalui menu Peta atau Realtime Monitoring.';
                },
                scrollDown() {
                    this.$nextTick(() => {
                        const el = this.$refs.messages;
                        if (el) el.scrollTop = el.scrollHeight;
                    });
                }
            };
        }
    </script>
@endauth
