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
                    <div class="mb-4 flex items-start gap-3" :class="message.role === 'user' ? 'justify-end' : 'justify-start'">
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
                            class="inline-flex h-auto min-h-0 w-fit items-center whitespace-pre-line rounded-2xl px-3 py-2 text-sm leading-5 shadow-sm"
                            :class="message.role === 'user'
                                ? 'max-w-[42%] rounded-br-md bg-[#303481] text-white'
                                : 'max-w-[58%] rounded-bl-md border border-slate-200 bg-white text-slate-700'"
                        >
                            <span class="m-0 block p-0" x-text="message.text"></span>
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

                <div x-show="loading" class="mb-4 flex items-start gap-3">
                    <div class="mt-2 h-10 w-10 shrink-0 animate-pulse rounded-full bg-[#303481]"></div>
                    <div class="w-56 rounded-2xl rounded-bl-md border border-slate-200 bg-white px-4 py-2.5 shadow-sm">
                        <div class="h-2 w-28 animate-pulse rounded-full bg-slate-200"></div>
                        <div class="mt-3 h-2 w-40 animate-pulse rounded-full bg-slate-200"></div>
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

    <script>
        function stesyChatbot() {
            return {
                open: false,
                input: '',
                loading: false,
                error: '',
                prompts: [
                    'Lihat data pos Pogung',
                    'Apa arti logger offline?',
                    'Buka panduan peta lokasi'
                ],
                messages: [
                    {
                        id: 'welcome',
                        role: 'assistant',
                        text: 'Halo, apakah ada yang bisa saya bantu?'
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

                    fetch('{{ route('chatbot.ask') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            message: text,
                            messages: this.messages
                                .filter((message) => message.id !== 'welcome')
                                .slice(-10)
                                .map((message) => ({
                                    role: message.role,
                                    text: message.text
                                }))
                        })
                    })
                    .then(async (response) => {
                        if (!response.ok) {
                            const payload = await response.json().catch(() => ({}));
                            throw new Error(payload.message || 'Chatbot belum bisa merespons.');
                        }
                        return response.json();
                    })
                    .then((payload) => {
                        this.messages.push({
                            id: `assistant-${Date.now()}`,
                            role: 'assistant',
                            text: payload.reply || this.resolveReply(text)
                        });
                    })
                    .catch((error) => {
                        this.error = error.message || 'Koneksi chatbot terganggu.';
                        this.messages.push({
                            id: `assistant-${Date.now()}`,
                            role: 'assistant',
                            text: this.resolveReply(text)
                        });
                    })
                    .finally(() => {
                        this.loading = false;
                        this.scrollDown();
                    });
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

                    return 'Saya belum terhubung ke mesin AI penuh, tetapi bisa dipakai sebagai asisten panduan. Untuk versi berikutnya, input ini bisa diarahkan ke endpoint chatbot agar jawabannya membaca data sistem secara langsung.';
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
