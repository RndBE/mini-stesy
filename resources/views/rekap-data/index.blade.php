@extends('layouts.app')

@section('content')
    <div x-data="rekapData()" class="space-y-4">

        {{-- Filter Bar --}}
        <div class="overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-slate-200 px-6 py-4">
            <div class="flex flex-col sm:flex-row items-stretch sm:items-end gap-3">
                <div class="flex-1 min-w-0">
                    <label class="block text-sm font-semibold text-slate-900 mb-2">Rentang Tanggal</label>
                    <div class="relative w-full" id="rkpWrap">
                        <input type="text" id="rkpRangeText"
                            class="w-full h-11 calendar-input text-sm rounded-lg pr-10 border border-slate-300 focus:outline-none focus:ring-4 focus:ring-blue-200 focus:border-blue-700 text-slate-700"
                            placeholder="YYYY-MM-DD — YYYY-MM-DD" autocomplete="off" readonly />
                        <button type="button" id="rkpBtn"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </button>

                        {{-- Floating Panel --}}
                        <div id="rkpPanel"
                            class="fixed w-[calc(100vw-32px)] max-w-[640px] rounded-xl border border-slate-200 bg-white shadow-lg p-4 z-[9999] hidden">

                            {{-- Start / End boxes --}}
                            <div class="flex items-center gap-3">
                                <div class="flex-1">
                                    <div id="rkpStartBox"
                                        class="h-9 rounded-lg border border-slate-200 bg-white flex items-center justify-center text-sm text-slate-700">
                                    </div>
                                </div>
                                <div class="w-8 flex items-center justify-center text-slate-400">→</div>
                                <div class="flex-1">
                                    <div id="rkpEndBox"
                                        class="h-9 rounded-lg border border-slate-200 bg-white flex items-center justify-center text-sm text-slate-700">
                                    </div>
                                </div>
                            </div>

                            <div class="mt-2 text-center text-xs text-slate-600">
                                <span id="rkpDays">0 hari</span>
                            </div>

                            {{-- Dual calendar grid --}}
                            <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-4">
                                {{-- Left calendar --}}
                                <div class="rounded-xl border border-slate-200 p-3">
                                    <div class="flex items-center justify-between">
                                        <button type="button" id="rkpPrev"
                                            class="h-8 w-8 rounded-lg border border-slate-200 hover:bg-slate-50 flex items-center justify-center">‹</button>
                                        <div class="flex items-center gap-2">
                                            <div class="relative">
                                                <button type="button" id="rkpMonthBtnL"
                                                    class="h-7 rounded-full bg-slate-100 px-3 text-xs font-semibold text-slate-700 inline-flex items-center gap-2 hover:bg-slate-200">
                                                    <span id="rkpMonthLabelL"></span>
                                                    <svg width="12" height="12" viewBox="0 0 20 20" fill="none"><path d="M5 7.5L10 12.5L15 7.5" stroke="#64748B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                </button>
                                                <div id="rkpMonthMenuL"
                                                    class="absolute left-0 mt-1 w-36 rounded-xl border border-slate-200 bg-white shadow-lg p-1 hidden z-50 max-h-48 overflow-auto">
                                                    <div id="rkpMonthItemsL" class="text-sm"></div>
                                                </div>
                                            </div>
                                            <div class="relative">
                                                <button type="button" id="rkpYearBtnL"
                                                    class="h-7 rounded-full bg-slate-100 px-3 text-xs font-semibold text-slate-700 inline-flex items-center gap-2 hover:bg-slate-200">
                                                    <span id="rkpYearLabelL"></span>
                                                    <svg width="12" height="12" viewBox="0 0 20 20" fill="none"><path d="M5 7.5L10 12.5L15 7.5" stroke="#64748B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                </button>
                                                <div id="rkpYearMenuL"
                                                    class="absolute left-0 mt-1 w-24 rounded-xl border border-slate-200 bg-white shadow-lg p-1 hidden z-50 max-h-48 overflow-auto">
                                                    <div id="rkpYearItemsL" class="text-sm"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="w-8"></div>
                                    </div>
                                    <div class="mt-3 grid grid-cols-7 gap-1 text-xs text-slate-500">
                                        <div class="text-center">Sen</div>
                                        <div class="text-center">Sel</div>
                                        <div class="text-center">Rab</div>
                                        <div class="text-center">Kam</div>
                                        <div class="text-center">Jum</div>
                                        <div class="text-center">Sab</div>
                                        <div class="text-center">Min</div>
                                    </div>
                                    <div id="rkpGridL" class="mt-1 grid grid-cols-7"></div>
                                </div>

                                {{-- Right calendar --}}
                                <div class="rounded-xl border border-slate-200 p-3">
                                    <div class="flex items-center justify-between">
                                        <div class="w-8"></div>
                                        <div class="flex items-center gap-2">
                                            <div class="relative">
                                                <button type="button" id="rkpMonthBtnR"
                                                    class="h-7 rounded-full bg-slate-100 px-3 text-xs font-semibold text-slate-700 inline-flex items-center gap-2 hover:bg-slate-200">
                                                    <span id="rkpMonthLabelR"></span>
                                                    <svg width="12" height="12" viewBox="0 0 20 20" fill="none"><path d="M5 7.5L10 12.5L15 7.5" stroke="#64748B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                </button>
                                                <div id="rkpMonthMenuR"
                                                    class="absolute left-0 mt-1 w-36 rounded-xl border border-slate-200 bg-white shadow-lg p-1 hidden z-50 max-h-48 overflow-auto">
                                                    <div id="rkpMonthItemsR" class="text-sm"></div>
                                                </div>
                                            </div>
                                            <div class="relative">
                                                <button type="button" id="rkpYearBtnR"
                                                    class="h-7 rounded-full bg-slate-100 px-3 text-xs font-semibold text-slate-700 inline-flex items-center gap-2 hover:bg-slate-200">
                                                    <span id="rkpYearLabelR"></span>
                                                    <svg width="12" height="12" viewBox="0 0 20 20" fill="none"><path d="M5 7.5L10 12.5L15 7.5" stroke="#64748B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                </button>
                                                <div id="rkpYearMenuR"
                                                    class="absolute left-0 mt-1 w-24 rounded-xl border border-slate-200 bg-white shadow-lg p-1 hidden z-50 max-h-48 overflow-auto">
                                                    <div id="rkpYearItemsR" class="text-sm"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" id="rkpNext"
                                            class="h-8 w-8 rounded-lg border border-slate-200 hover:bg-slate-50 flex items-center justify-center">›</button>
                                    </div>
                                    <div class="mt-3 grid grid-cols-7 gap-1 text-xs text-slate-500">
                                        <div class="text-center">Sen</div>
                                        <div class="text-center">Sel</div>
                                        <div class="text-center">Rab</div>
                                        <div class="text-center">Kam</div>
                                        <div class="text-center">Jum</div>
                                        <div class="text-center">Sab</div>
                                        <div class="text-center">Min</div>
                                    </div>
                                    <div id="rkpGridR" class="mt-1 grid grid-cols-7"></div>
                                </div>
                            </div>

                            {{-- Panel footer --}}
                            <div class="mt-4 flex justify-end gap-2">
                                <button type="button" id="rkpClear"
                                    class="px-4 py-2 text-xs rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50">Batal</button>
                                <button type="button" id="rkpApply"
                                    class="px-4 py-2 text-xs rounded-lg bg-[#303481] text-white hover:bg-[#10134B]">Terapkan</button>
                            </div>
                        </div>

                        {{-- Hidden inputs used by Alpine fetchData() --}}
                        <input type="hidden" id="rkpStartHidden" x-ref="startDate">
                        <input type="hidden" id="rkpEndHidden" x-ref="endDate">
                    </div>
                </div>

                <div class="flex items-center gap-3 flex-shrink-0 sm:flex-shrink w-full sm:w-auto">
                    <button @click="fetchData()"
                        class="flex-1 sm:flex-none h-11 px-6 rounded-lg bg-gradient-to-r from-blue-900 to-blue-800 text-white font-semibold hover:shadow-lg hover:shadow-blue-900/30 transition-all duration-200 active:scale-95 flex items-center justify-center gap-2">
                        <svg x-show="!loading" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <svg x-show="loading" x-cloak class="animate-spin h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-show="!loading">Cari</span>
                        <span x-show="loading" x-cloak>Memuat...</span>
                    </button>
                    <button @click="resetFilter()"
                        class="flex-1 sm:flex-none h-11 px-4 rounded-lg border-2 border-slate-200 text-slate-700 font-semibold hover:bg-slate-50 hover:border-slate-300 transition-all duration-200 active:scale-95">
                        Reset
                    </button>
                </div>
            </div>

            <div x-show="errorMessage" x-cloak class="mt-4 p-3 bg-red-50 border-l-4 border-red-500 rounded-lg">
                <p class="text-sm text-red-700 font-medium" x-text="errorMessage"></p>
            </div>
        </div>


        {{-- Summary Cards --}}
        <div x-show="dataLoaded" x-cloak class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <div class="rounded-lg bg-gradient-to-br from-blue-50 to-blue-100 ring-1 ring-blue-200 px-5 py-4">
                <p class="text-xs font-semibold text-blue-600 uppercase tracking-wide">Total Logger</p>
                <p class="text-2xl font-bold text-blue-900 mt-1" x-text="loggers.length"></p>
            </div>
            <div class="rounded-lg bg-gradient-to-br from-emerald-50 to-emerald-100 ring-1 ring-emerald-200 px-5 py-4">
                <p class="text-xs font-semibold text-emerald-600 uppercase tracking-wide">Rata-rata Kelengkapan</p>
                <p class="text-2xl font-bold text-emerald-900 mt-1" x-text="overallAvg + '%'"></p>
            </div>
            <div class="rounded-lg bg-gradient-to-br from-purple-50 to-purple-100 ring-1 ring-purple-200 px-5 py-4">
                <p class="text-xs font-semibold text-purple-600 uppercase tracking-wide">Rentang Hari</p>
                <p class="text-2xl font-bold text-purple-900 mt-1" x-text="dates.length + ' hari'"></p>
            </div>
            <div class="rounded-lg bg-gradient-to-br from-orange-50 to-orange-100 ring-1 ring-orange-200 px-5 py-4">
                <p class="text-xs font-semibold text-orange-600 uppercase tracking-wide">Logger Kurang Lengkap</p>
                <p class="text-2xl font-bold text-orange-900 mt-1" x-text="loggers.filter(l => l.overall_pct < 95).length"></p>
            </div>
        </div>

        {{-- Main Table --}}
        <div x-show="dataLoaded" x-cloak class="overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-slate-200">
            {{-- Legend --}}
            <div class="px-6 py-3 border-b border-slate-200 flex flex-wrap items-center gap-4 bg-slate-50">
                <span class="text-xs font-semibold text-slate-600 uppercase tracking-wide mr-2">Keterangan:</span>
                <div class="flex items-center gap-1.5">
                    <span class="inline-block h-3 w-3 rounded-full bg-emerald-500"></span>
                    <span class="text-xs text-slate-600">≥ 95% (Baik)</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="inline-block h-3 w-3 rounded-full bg-yellow-400"></span>
                    <span class="text-xs text-slate-600">60–94% (Sedang)</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="inline-block h-3 w-3 rounded-full bg-red-500"></span>
                    <span class="text-xs text-slate-600">< 60% (Kurang)</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="inline-block h-3 w-3 rounded-full bg-slate-300"></span>
                    <span class="text-xs text-slate-600">Belum ada data</span>
                </div>
            </div>

            <div class="overflow-x-auto" style="-webkit-overflow-scrolling: touch; scroll-behavior: smooth;">
                <table class="w-full text-left text-sm text-slate-600 whitespace-nowrap border-separate border-spacing-0">
                    <thead class="text-xs font-bold text-neutral-950 uppercase">
                        {{-- Row 1: Month groups (sticky label columns span both rows) --}}
                        <tr class="bg-neutral-300">
                            {{-- NO: disembunyikan di mobile --}}
                            <th class="hidden sm:table-cell sticky left-0 z-30 bg-neutral-300 px-3 py-2 min-w-[2.5rem] text-center" rowspan="2">No</th>
                            {{-- ID Logger: disembunyikan di mobile --}}
                            <th class="hidden sm:table-cell sticky sm:left-[2.5rem] z-30 bg-neutral-300 px-4 py-2 min-w-[7rem]" rowspan="2">ID Logger</th>
                            {{-- Nama Logger: sticky left-0 di mobile, left-[9.5rem] di sm+ --}}
                            <th class="sticky left-0 sm:left-[9.5rem] z-30 bg-neutral-300 px-4 py-2 min-w-[8rem] sm:min-w-[11rem]" rowspan="2"
                                style="box-shadow: 4px 0 6px rgba(0,0,0,0.12)">Nama Logger</th>
                            <template x-for="group in monthGroups" :key="group.label">
                                <th class="px-4 py-2 text-center border-l-2 border-neutral-400"
                                    :colspan="group.count"
                                    x-text="group.label">
                                </th>
                            </template>
                        </tr>
                        {{-- Row 2: Individual day numbers --}}
                        <tr class="bg-neutral-200">
                            <template x-for="date in dates" :key="date">
                                <th class="px-3 py-2 text-center min-w-[5.5rem] border-l border-neutral-300"
                                    x-text="formatDay(date)"></th>
                            </template>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        <template x-for="(logger, idx) in loggers" :key="logger.id">
                            <tr class="hover:bg-slate-50 transition-colors">
                                {{-- No: hidden di mobile --}}
                                <td class="hidden sm:table-cell sticky left-0 z-10 bg-white px-3 py-3 text-center font-medium text-slate-500 min-w-[2.5rem] border-r border-slate-100" x-text="idx + 1"></td>
                                {{-- ID Logger: hidden di mobile --}}
                                <td class="hidden sm:table-cell sticky sm:left-[2.5rem] z-10 bg-white px-4 py-3 font-mono text-xs text-slate-800 min-w-[7rem] border-r border-slate-100" x-text="logger.id"></td>
                                {{-- Nama Logger: left-0 di mobile, left-[9.5rem] di sm+ --}}
                                <td class="sticky left-0 sm:left-[9.5rem] z-10 bg-white px-3 sm:px-4 py-3 font-semibold text-slate-900 min-w-[8rem] sm:min-w-[11rem] text-sm"
                                    style="box-shadow: 4px 0 6px rgba(0,0,0,0.09)" x-text="logger.name"></td>
                                {{-- Per-day cells --}}
                                <template x-for="day in logger.days" :key="day.date">
                                    <td class="px-4 py-3 text-center">
                                        <template x-if="day.expected === 0">
                                            <span class="text-slate-400 text-xs">—</span>
                                        </template>
                                        <template x-if="day.expected > 0">
                                            <div class="flex flex-col items-center gap-1">
                                                <span class="text-sm font-bold"
                                                    :class="pctTextClass(day.pct)"
                                                    x-text="day.pct + '%'">
                                                </span>
                                                <div class="w-full h-1.5 rounded-full bg-slate-200 overflow-hidden">
                                                    <div class="h-full rounded-full transition-all duration-500"
                                                        :class="pctBarClass(day.pct)"
                                                        :style="`width: ${Math.min(100, day.pct)}%`">
                                                    </div>
                                                </div>
                                                <span class="text-[10px] text-slate-500 leading-tight">
                                                    <span x-text="day.count.toLocaleString('id-ID')"></span>/<span x-text="day.expected.toLocaleString('id-ID')"></span>
                                                </span>
                                            </div>
                                        </template>
                                    </td>
                                </template>
                            </tr>
                        </template>

                        {{-- Empty state --}}
                        <template x-if="loggers.length === 0 && dataLoaded">
                            <tr>
                                <td :colspan="5 + dates.length" class="px-6 py-12 text-center text-sm text-slate-500">
                                    Tidak ada data untuk rentang tanggal yang dipilih.
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Empty state (before search) --}}
        <div x-show="!dataLoaded && !loading" x-cloak class="text-center py-16 px-6">
            <div class="flex justify-center mb-6">
                <div class="h-24 w-24 rounded-full bg-gradient-to-br from-blue-100 to-blue-50 ring-4 ring-blue-200 flex items-center justify-center">
                    <svg class="h-12 w-12 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
            <h3 class="text-xl font-bold text-slate-900 mb-2">Rekap Data Kelengkapan</h3>
            <p class="text-slate-500 max-w-md mx-auto">Pilih rentang tanggal, kemudian klik <strong>Cari</strong> untuk menampilkan rekap kelengkapan data seluruh logger.</p>
        </div>
    </div>

    <script>
        function rekapData() {
            const today = new Date();
            const sevenDaysAgo = new Date(today);
            sevenDaysAgo.setDate(today.getDate() - 6);

            const fmt = (d) => d.toISOString().split('T')[0];

            return {
                filters: {
                    start_date: fmt(sevenDaysAgo),
                    end_date: fmt(today),
                },
                loading: false,
                dataLoaded: false,
                errorMessage: '',
                dates: [],
                loggers: [],

                get overallAvg() {
                    if (!this.loggers.length) return 0;
                    const sum = this.loggers.reduce((a, l) => a + l.overall_pct, 0);
                    return Math.round(sum / this.loggers.length * 10) / 10;
                },

                async fetchData() {
                    // Read from hidden inputs set by the custom range picker
                    const startInput = document.getElementById('rkpStartHidden');
                    const endInput   = document.getElementById('rkpEndHidden');
                    const sd = (startInput && startInput.value) || this.filters.start_date;
                    const ed = (endInput   && endInput.value)   || this.filters.end_date;

                    if (!sd || !ed) {
                        this.errorMessage = 'Pilih rentang tanggal terlebih dahulu.';
                        return;
                    }

                    const start = new Date(sd);
                    const end   = new Date(ed);
                    const diffDays = Math.round((end - start) / 86400000);

                    if (diffDays < 0) {
                        this.errorMessage = 'Tanggal mulai harus sebelum tanggal akhir.';
                        return;
                    }
                    if (diffDays > 30) {
                        this.errorMessage = 'Rentang tanggal maksimal 31 hari.';
                        return;
                    }

                    this.filters.start_date = sd;
                    this.filters.end_date   = ed;
                    this.loading      = true;
                    this.errorMessage = '';
                    this.dataLoaded   = false;
                    this.dates        = [];
                    this.loggers      = [];

                    try {
                        const params = new URLSearchParams({ start_date: sd, end_date: ed });
                        const resp = await fetch(`/api/rekap-data?${params}`, {
                            headers: { 'Accept': 'application/json' }
                        });
                        const data = await resp.json();

                        if (resp.ok && data.success) {
                            this.dates      = data.dates;
                            this.loggers    = data.loggers;
                            this.dataLoaded = true;
                        } else {
                            this.errorMessage = data.message || 'Gagal memuat data.';
                        }
                    } catch (err) {
                        this.errorMessage = 'Terjadi kesalahan saat menghubungi server.';
                    } finally {
                        this.loading = false;
                    }
                },

                resetFilter() {
                    const today = new Date();
                    const sevenDaysAgo = new Date(today);
                    sevenDaysAgo.setDate(today.getDate() - 6);
                    const fmt = (d) => d.toISOString().split('T')[0];
                    const sd = fmt(sevenDaysAgo), ed = fmt(today);
                    this.filters = { start_date: sd, end_date: ed };
                    this.dataLoaded   = false;
                    this.dates        = [];
                    this.loggers      = [];
                    this.errorMessage = '';
                    // Also reset the picker UI (syncs selStart/selEnd inside the picker)
                    if (typeof window.rkpSetRange === 'function') {
                        window.rkpSetRange(sd, ed);
                    }
                },

                // e.g. "18" — just the day number for the second header row
                formatDay(dateStr) {
                    const d = new Date(dateStr + 'T00:00:00');
                    return d.toLocaleDateString('id-ID', { day: '2-digit' });
                },

                // Compute month groups for colspan header, e.g. [{label:'Jan 2025', count:10}, {label:'Feb 2025', count:20}]
                get monthGroups() {
                    if (!this.dates.length) return [];
                    const groups = [];
                    let current = null;
                    for (const dateStr of this.dates) {
                        const d = new Date(dateStr + 'T00:00:00');
                        const label = d.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
                        if (current && current.label === label) {
                            current.count++;
                        } else {
                            current = { label, count: 1 };
                            groups.push(current);
                        }
                    }
                    return groups;
                },

                pctClass(pct) {
                    if (pct >= 95) return 'bg-emerald-100 text-emerald-800';
                    if (pct >= 60) return 'bg-yellow-100 text-yellow-800';
                    if (pct >  0)  return 'bg-red-100 text-red-800';
                    return 'bg-slate-100 text-slate-500';
                },

                pctBarClass(pct) {
                    if (pct >= 95) return 'bg-emerald-500';
                    if (pct >= 60) return 'bg-yellow-400';
                    return 'bg-red-500';
                },

                pctTextClass(pct) {
                    if (pct >= 95) return 'text-emerald-700';
                    if (pct >= 60) return 'text-yellow-600';
                    return 'text-red-600';
                },
            };
        }
    </script>

    <script>
    // ── Rekap Data Range Picker ──────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        (function initRkpRangePicker() {
            const MONTHS_ID  = ['Januari','Februari','Maret','April','Mei','Juni',
                                'Juli','Agustus','September','Oktober','November','Desember'];
            const MONTHS_SHORT = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];

            const wrap      = document.getElementById('rkpWrap');
            const panel     = document.getElementById('rkpPanel');
            const btn       = document.getElementById('rkpBtn');
            const rangeText = document.getElementById('rkpRangeText');
            const startBox  = document.getElementById('rkpStartBox');
            const endBox    = document.getElementById('rkpEndBox');
            const daysLabel = document.getElementById('rkpDays');
            const startHid  = document.getElementById('rkpStartHidden');
            const endHid    = document.getElementById('rkpEndHidden');
            const applyBtn  = document.getElementById('rkpApply');
            const clearBtn  = document.getElementById('rkpClear');
            const prevBtn   = document.getElementById('rkpPrev');
            const nextBtn   = document.getElementById('rkpNext');
            const gridL     = document.getElementById('rkpGridL');
            const gridR     = document.getElementById('rkpGridR');

            if (!wrap || !panel || !btn || !rangeText || !gridL || !gridR) return;

            // ── Helpers ──────────────────────────────────────────────────────
            const pad     = n => String(n).padStart(2, '0');
            const keyOf   = d => `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;
            const fmtDisp = d => `${d.getDate()} ${MONTHS_SHORT[d.getMonth()]} ${d.getFullYear()}`;
            const daysDiff = (a, b) => Math.round(
                (new Date(b.getFullYear(), b.getMonth(), b.getDate()) -
                 new Date(a.getFullYear(), a.getMonth(), a.getDate())) / 86400000
            ) + 1;

            // ── State ────────────────────────────────────────────────────────
            const now = new Date();
            const ago6 = new Date(now); ago6.setDate(now.getDate() - 6);

            // Applied = what was last Terapkan-ed (persisted)
            let appliedStart = new Date(ago6.getFullYear(), ago6.getMonth(), ago6.getDate());
            let appliedEnd   = new Date(now.getFullYear(), now.getMonth(), now.getDate());

            // Temp = live selection inside the open panel
            let tempStart = new Date(appliedStart);
            let tempEnd   = new Date(appliedEnd);

            // picking=false → next click sets START; picking=true → next click sets END
            let picking = false;

            // Left calendar view (right is always left + 1 month)
            let viewLeft = new Date(tempStart.getFullYear(), tempStart.getMonth(), 1);

            function syncRightFromLeft() {
                return new Date(viewLeft.getFullYear(), viewLeft.getMonth() + 1, 1);
            }

            // ── Position panel ───────────────────────────────────────────────
            function positionPanel() {
                const wasHidden = panel.classList.contains('hidden');
                if (wasHidden) {
                    panel.style.visibility = 'hidden';
                    panel.classList.remove('hidden');
                }
                const pw = panel.offsetWidth  || 640;
                const ph = panel.offsetHeight || 400;
                if (wasHidden) {
                    panel.classList.add('hidden');
                    panel.style.visibility = '';
                }
                const r    = wrap.getBoundingClientRect();
                const winW = window.innerWidth;
                const winH = window.innerHeight;
                let top  = r.bottom + 6;
                let left = r.left;
                if (left + pw > winW - 8) left = winW - pw - 8;
                if (left < 8) left = 8;
                if (top + ph > winH - 8) {
                    top = r.top - ph - 6;
                    if (top < 8) top = 8;
                }
                panel.style.top  = top + 'px';
                panel.style.left = left + 'px';
            }

            // ── Header labels ────────────────────────────────────────────────
            function setHeaderLabels() {
                const lY = viewLeft.getFullYear(), lM = viewLeft.getMonth();
                const rV = syncRightFromLeft();
                document.getElementById('rkpMonthLabelL').textContent = MONTHS_ID[lM];
                document.getElementById('rkpYearLabelL').textContent  = String(lY);
                document.getElementById('rkpMonthLabelR').textContent = MONTHS_ID[rV.getMonth()];
                document.getElementById('rkpYearLabelR').textContent  = String(rV.getFullYear());
            }

            // ── Top start/end boxes ──────────────────────────────────────────
            function updateTopInfo() {
                startBox.textContent = fmtDisp(tempStart);
                endBox.textContent   = fmtDisp(tempEnd);
                daysLabel.textContent = `${daysDiff(tempStart, tempEnd)} hari`;
                rangeText.value = `${keyOf(tempStart)} — ${keyOf(tempEnd)}`;
            }

            // ── Render one calendar grid ──────────────────────────────────────
            function isBetween(d, a, b) {
                const t  = new Date(d.getFullYear(), d.getMonth(), d.getDate()).getTime();
                const ta = new Date(a.getFullYear(), a.getMonth(), a.getDate()).getTime();
                const tb = new Date(b.getFullYear(), b.getMonth(), b.getDate()).getTime();
                return t >= ta && t <= tb;
            }

            function renderMonthGrid(targetGrid, viewMonth) {
                const y = viewMonth.getFullYear(), m = viewMonth.getMonth();
                const first = new Date(y, m, 1);
                const daysInMonth = new Date(y, m + 1, 0).getDate();
                const offset = (first.getDay() + 6) % 7; // Mon=0

                targetGrid.innerHTML = '';

                // Empty offset cells
                for (let i = 0; i < offset; i++) {
                    const e = document.createElement('div');
                    e.className = 'h-9';
                    targetGrid.appendChild(e);
                }

                for (let d = 1; d <= daysInMonth; d++) {
                    const cur  = new Date(y, m, d);
                    const isS  = keyOf(cur) === keyOf(tempStart);
                    const isE  = keyOf(cur) === keyOf(tempEnd);
                    const isSE = isS && isE; // single-day (start === end)
                    const inRg = !isSE && isBetween(cur, tempStart, tempEnd);

                    // ── Outer wrapper: full-width grid cell ──────────────────
                    const wrapper = document.createElement('div');
                    wrapper.className = 'relative h-9 flex items-center justify-center cursor-pointer';

                    // ── Strip background (absolute, sits behind the circle) ──
                    if (!isSE && (inRg || isS || isE)) {
                        const strip = document.createElement('div');
                        strip.className = 'absolute inset-y-0 bg-[#E9EAFB] pointer-events-none';
                        if (isS)      strip.style.cssText = 'left:50%;right:0';  // right half for start
                        else if (isE) strip.style.cssText = 'left:0;right:50%';  // left half for end
                        else          strip.style.cssText = 'left:0;right:0';    // full width for in-range
                        wrapper.appendChild(strip);
                    }

                    // ── Inner circle / day number ─────────────────────────────
                    const circle = document.createElement('div');
                    circle.textContent = String(d);
                    if (isS || isE) {
                        circle.className = 'relative z-10 w-8 h-8 rounded-full flex items-center justify-center text-[11px] bg-[#303481] text-white font-bold';
                    } else if (inRg) {
                        circle.className = 'relative z-10 w-8 h-8 flex items-center justify-center text-[11px] text-slate-700';
                    } else {
                        circle.className = 'relative z-10 w-8 h-8 rounded-full flex items-center justify-center text-[11px] text-slate-700 hover:bg-slate-100';
                    }
                    wrapper.appendChild(circle);

                    wrapper.addEventListener('click', (e) => {
                        e.stopPropagation();
                        const clicked = new Date(y, m, d);
                        if (!picking) {
                            // First click → set both start & end to this date
                            tempStart = clicked;
                            tempEnd   = clicked;
                            picking   = true;
                        } else {
                            // Second click → set end, auto-swap if before start
                            tempEnd = clicked;
                            if (tempEnd.getTime() < tempStart.getTime()) {
                                const t = tempStart; tempStart = tempEnd; tempEnd = t;
                            }
                            picking = false;
                        }
                        updateTopInfo();
                        render();
                    });

                    targetGrid.appendChild(wrapper);
                }
            }

            // ── Full render ──────────────────────────────────────────────────
            function render() {
                setHeaderLabels();
                updateTopInfo();
                renderMonthGrid(gridL, viewLeft);
                renderMonthGrid(gridR, syncRightFromLeft());
            }

            // ── Open / Close ─────────────────────────────────────────────────
            function openPanel() {
                panel.classList.remove('hidden');
                positionPanel();
                closeMenus();
                render();
            }
            function closePanel() {
                panel.classList.add('hidden');
                picking   = false;
                closeMenus();
            }

            // ── Month/Year dropdowns ─────────────────────────────────────────
            function closeMenus() {
                ['L','R'].forEach(s => {
                    const mm = document.getElementById('rkpMonthMenu' + s);
                    const ym = document.getElementById('rkpYearMenu'  + s);
                    if (mm) mm.classList.add('hidden');
                    if (ym) ym.classList.add('hidden');
                });
            }

            function buildMonthMenu(side) {
                const itemsEl = document.getElementById('rkpMonthItems' + side);
                if (!itemsEl) return;
                itemsEl.innerHTML = '';
                MONTHS_ID.forEach((name, idx) => {
                    const b = document.createElement('button');
                    b.type = 'button';
                    b.className = 'w-full text-left px-3 py-1.5 rounded-lg hover:bg-slate-100 text-xs text-slate-700';
                    b.textContent = name;
                    b.addEventListener('click', () => {
                        if (side === 'L') {
                            viewLeft = new Date(viewLeft.getFullYear(), idx, 1);
                        } else {
                            // Adjust viewLeft so right shows the chosen month
                            const rV = syncRightFromLeft();
                            const newR = new Date(rV.getFullYear(), idx, 1);
                            viewLeft = new Date(newR.getFullYear(), newR.getMonth() - 1, 1);
                        }
                        closeMenus();
                        render();
                    });
                    itemsEl.appendChild(b);
                });
            }

            function buildYearMenu(side) {
                const itemsEl = document.getElementById('rkpYearItems' + side);
                if (!itemsEl) return;
                itemsEl.innerHTML = '';
                const curY = (side === 'L' ? viewLeft : syncRightFromLeft()).getFullYear();
                for (let y = curY - 10; y <= curY + 10; y++) {
                    const b = document.createElement('div');
                    b.textContent = String(y);
                    b.className = 'px-3 py-1.5 rounded-lg cursor-pointer hover:bg-slate-100 text-xs text-center' +
                                  (y === curY ? ' font-bold text-[#303481]' : ' text-slate-700');
                    b.addEventListener('click', () => {
                        if (side === 'L') {
                            viewLeft = new Date(y, viewLeft.getMonth(), 1);
                        } else {
                            const rV  = syncRightFromLeft();
                            const newR = new Date(y, rV.getMonth(), 1);
                            viewLeft   = new Date(newR.getFullYear(), newR.getMonth() - 1, 1);
                        }
                        closeMenus();
                        render();
                    });
                    itemsEl.appendChild(b);
                }
            }

            ['L','R'].forEach(side => {
                const mBtn = document.getElementById('rkpMonthBtn' + side);
                const yBtn = document.getElementById('rkpYearBtn'  + side);
                const mMenu = document.getElementById('rkpMonthMenu' + side);
                const yMenu = document.getElementById('rkpYearMenu'  + side);

                if (mBtn) mBtn.addEventListener('click', e => {
                    e.stopPropagation();
                    buildMonthMenu(side);
                    mMenu.classList.toggle('hidden');
                    yMenu.classList.add('hidden');
                    document.getElementById('rkpMonthMenu' + (side === 'L' ? 'R' : 'L')).classList.add('hidden');
                    document.getElementById('rkpYearMenu'  + (side === 'L' ? 'R' : 'L')).classList.add('hidden');
                });
                if (yBtn) yBtn.addEventListener('click', e => {
                    e.stopPropagation();
                    buildYearMenu(side);
                    yMenu.classList.toggle('hidden');
                    mMenu.classList.add('hidden');
                    document.getElementById('rkpMonthMenu' + (side === 'L' ? 'R' : 'L')).classList.add('hidden');
                    document.getElementById('rkpYearMenu'  + (side === 'L' ? 'R' : 'L')).classList.add('hidden');
                });
                if (mMenu) mMenu.addEventListener('click', e => e.stopPropagation());
                if (yMenu) yMenu.addEventListener('click', e => e.stopPropagation());
            });

            // ── Navigation ───────────────────────────────────────────────────
            if (prevBtn) prevBtn.addEventListener('click', () => {
                viewLeft = new Date(viewLeft.getFullYear(), viewLeft.getMonth() - 1, 1);
                render();
            });
            if (nextBtn) nextBtn.addEventListener('click', () => {
                viewLeft = new Date(viewLeft.getFullYear(), viewLeft.getMonth() + 1, 1);
                render();
            });

            // ── Batal (cancel) ────────────────────────────────────────────────
            clearBtn.addEventListener('click', () => {
                tempStart = new Date(appliedStart);
                tempEnd   = new Date(appliedEnd);
                picking   = false;
                viewLeft  = new Date(tempStart.getFullYear(), tempStart.getMonth(), 1);
                closePanel();
            });

            // ── Terapkan (apply) ──────────────────────────────────────────────
            applyBtn.addEventListener('click', () => {
                if (!tempStart || !tempEnd) return;
                const diff = daysDiff(tempStart, tempEnd) - 1; // exclusive diff for 31-day cap
                if (diff > 30) {
                    alert('Rentang tanggal maksimal 31 hari.');
                    return;
                }

                appliedStart = new Date(tempStart);
                appliedEnd   = new Date(tempEnd);

                const sd = keyOf(appliedStart);
                const ed = keyOf(appliedEnd);

                startHid.value  = sd;
                endHid.value    = ed;
                rangeText.value = `${sd} — ${ed}`;

                // Sync to Alpine component
                const alpineEl = document.querySelector('[x-data]');
                if (alpineEl && alpineEl._x_dataStack) {
                    const comp = alpineEl._x_dataStack[0];
                    if (comp && comp.filters) {
                        comp.filters.start_date = sd;
                        comp.filters.end_date   = ed;
                    }
                }
                closePanel();
            });

            // ── Open/close triggers ───────────────────────────────────────────
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                panel.classList.contains('hidden') ? openPanel() : closePanel();
            });
            rangeText.addEventListener('focus', openPanel);

            document.addEventListener('click', (e) => {
                const path = typeof e.composedPath === 'function' ? e.composedPath() : [];
                const inside = path.length
                    ? (path.includes(wrap) || path.includes(panel))
                    : (wrap.contains(e.target) || panel.contains(e.target));
                if (!inside) closePanel();
            });
            document.addEventListener('scroll', () => {
                if (!panel.classList.contains('hidden')) positionPanel();
            }, true);
            window.addEventListener('resize', () => {
                if (!panel.classList.contains('hidden')) positionPanel();
            });

            // ── Expose for Alpine resetFilter() ──────────────────────────────
            window.rkpSetRange = function (sd, ed) {
                const parseYMD = s => { const p = s.split('-'); return new Date(+p[0], +p[1]-1, +p[2]); };
                appliedStart = sd ? parseYMD(sd) : new Date();
                appliedEnd   = ed ? parseYMD(ed) : new Date();
                tempStart = new Date(appliedStart);
                tempEnd   = new Date(appliedEnd);
                picking   = false;
                startHid.value  = sd || '';
                endHid.value    = ed || '';
                rangeText.value = (sd && ed) ? `${sd} — ${ed}` : '';
                viewLeft = new Date(tempStart.getFullYear(), tempStart.getMonth(), 1);
                render();
            };

            // ── Init with default 7-day range ────────────────────────────────
            startHid.value  = keyOf(appliedStart);
            endHid.value    = keyOf(appliedEnd);
            rangeText.value = `${keyOf(appliedStart)} — ${keyOf(appliedEnd)}`;
            render();
            closePanel(); // start hidden
        })();
    });
    </script>

    <style>
        [x-cloak] { display: none !important; }
        .calendar-input { padding-left: 0.75rem; }
    </style>
@endsection
