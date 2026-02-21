@extends('layouts.app')

@section('content')
    <div x-data="dataMasukManager()" class="space-y-6 min-w-0 ">

        <div class="overflow-visible rounded-lg bg-white shadow-sm ring-1 ring-slate-200 px-6 py-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-900 mb-3">
                        ID Logger <span class="text-red-500">*</span>
                    </label>
                    <div class="relative" @click.outside="closeLoggerDropdown()">
                        <button type="button" @click="loggerDropdownOpen = !loggerDropdownOpen"
                            class="w-full h-11 rounded-lg border-2 border-slate-200 px-4 py-2 text-left text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent flex items-center justify-between bg-white hover:border-slate-300 transition-colors">
                            <span x-text="getLoggerLabel(filters.logger_id)" class="flex-1"></span>
                            <svg class="h-5 w-5 text-slate-400 transition-transform flex-shrink-0"
                                :class="loggerDropdownOpen ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <path d="M6 9l6 6 6-6" />
                            </svg>
                        </button>
                        <div x-show="loggerDropdownOpen" x-cloak x-transition
                            class="absolute top-full left-0 right-0 mt-2 bg-white border-2 border-slate-200 rounded-lg shadow-xl z-50 max-h-48 overflow-y-auto">
                            <input type="text" x-model="loggerSearchQuery" placeholder="Cari logger..."
                                class="sticky top-0 w-full px-4 py-3 border-b-2 border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            @foreach ($loggers as $logger)
                                <button type="button"
                                    @click="filters.logger_id = '{{ $logger->id_logger }}'; loggerDropdownOpen = false"
                                    class="w-full px-4 py-3 text-left text-sm text-slate-700 hover:bg-indigo-50 transition-colors first:pt-3 last:pb-3"
                                    :class="filters.logger_id === '{{ $logger->id_logger }}' ?
                                        'bg-indigo-100 text-indigo-900 font-semibold' : ''"
                                    :style="filterLogger('{{ $logger->id_logger }}', '{{ $logger->nama_logger ?? 'Logger' }}') ?
                                        '' : 'display: none'">
                                    <span class="font-medium">{{ $logger->id_logger }}</span>
                                    <span class="text-slate-500"> - {{ $logger->nama_logger ?? 'Logger' }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-900 mb-3">
                        Tanggal <span class="text-red-500">*</span>
                    </label>
                    <input type="date" x-model="filters.tanggal"
                        class="w-full h-11 rounded-lg border-2 border-slate-200 px-4 text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent hover:border-slate-300 transition-colors">
                </div>

                <div class="flex items-end gap-3">
                    <button @click="searchData()"
                        class="flex-1 h-12 rounded-lg bg-gradient-to-r from-indigo-600 to-indigo-700 text-white font-semibold hover:shadow-lg hover:shadow-indigo-500/30 transition-all duration-200 active:scale-95 flex items-center justify-center gap-2">
                        <svg x-show="!loading" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <svg x-show="loading" x-cloak class="animate-spin h-5 w-5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        <span x-show="!loading">Cari Data</span>
                        <span x-show="loading" x-cloak>Memproses...</span>
                    </button>
                    <button @click="resetFilter()"
                        class="h-12 px-6 rounded-lg border-2 border-slate-200 text-slate-700 font-semibold hover:bg-slate-50 hover:border-slate-300 transition-all duration-200 active:scale-95">
                        Reset
                    </button>
                </div>
            </div>

            <div x-show="errorMessage" x-cloak class="mt-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg">
                <div class="flex items-start gap-3">
                    <svg class="h-5 w-5 text-red-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd" />
                    </svg>
                    <div>
                        <p class="text-sm font-semibold text-red-800">Terjadi Kesalahan</p>
                        <p class="text-sm text-red-700 mt-1" x-text="errorMessage"></p>
                    </div>
                </div>
            </div>
        </div>


        <div x-show="dataLoaded" x-cloak class="grid grid-cols-1 md:grid-cols-4 gap-4 animate-in fade-in">
            <div
                class="overflow-hidden rounded-lg bg-gradient-to-br from-blue-50 to-blue-100 shadow-sm ring-1 ring-blue-200 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-blue-600 uppercase tracking-wide">ID Logger</p>
                        <p class="text-xl font-bold text-blue-900 mt-2 break-all" x-text="filters.logger_id"></p>
                    </div>
                    <svg class="h-12 w-12 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
            </div>

            <div
                class="overflow-hidden rounded-lg bg-gradient-to-br from-purple-50 to-purple-100 shadow-sm ring-1 ring-purple-200  px-6 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-purple-600 uppercase tracking-wide">Tanggal</p>
                        <p class="text-xl font-bold text-purple-900 mt-2 break-all" x-text="filters.tanggal"></p>
                    </div>
                    <svg class="h-12 w-12 text-purple-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>

            <div
                class="overflow-hidden rounded-lg bg-gradient-to-br from-emerald-50 to-emerald-100 shadow-sm ring-1 ring-emerald-200  px-6 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-emerald-600 uppercase tracking-wide">Total Data</p>
                        <p class="text-xl font-bold text-emerald-900 mt-2" x-text="tableData.length"></p>
                    </div>
                    <svg class="h-12 w-12 text-emerald-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
            </div>

            <div
                class="overflow-hidden rounded-lg bg-gradient-to-br from-orange-50 to-orange-100 shadow-sm ring-1 ring-orange-200  px-6 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-orange-600 uppercase tracking-wide">Kelengkapan</p>
                        <p class="text-xl font-bold text-orange-900 mt-2" x-text="dataCompleteness + '%'"></p>
                    </div>
                    <svg class="h-12 w-12 text-orange-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>


        <div x-show="dataLoaded" x-cloak
            class="overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-slate-200 animate-in fade-in min-w-0 max-w-full">
            <div
                class="px-6 py-3 bg-gradient-to-r from-slate-50 to-slate-100 border-b border-slate-200 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <h3 class="font-bold text-slate-900 text-lg">Data Detail Sensor</h3>
                </div>
                <button @click="exportToExcel()"
                    class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-green-500 to-green-600 px-4 py-2.5 text-sm font-semibold text-white hover:shadow-lg hover:shadow-green-500/20 transition-all duration-200 active:scale-95">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 3v12m0 0l-3-3m3 3l3-3M3 12a9 9 0 1118 0 9 9 0 01-18 0z" />
                    </svg>
                    Export ke Excel
                </button>
            </div>
            <div class="w-full max-w-full overflow-x-auto">
                <table class="min-w-max w-full text-left text-sm text-slate-600 whitespace-nowrap">
                    <thead
                        class="bg-slate-50 text-xs font-semibold uppercase text-slate-700 sticky top-0 border-b border-slate-200">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-slate-900">No</th>
                            <th scope="col" class="px-6 py-4 text-slate-900">Waktu Pencatatan</th>
                            <template x-for="(col, index) in columns" :key="index">
                                <th scope="col" class="px-6 py-4 text-center text-slate-900" x-text="col"></th>
                            </template>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        <template x-for="(row, idx) in tableData" :key="idx">
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 font-medium text-slate-900">
                                    <span
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-xs font-semibold text-slate-700"
                                        x-text="idx + 1"></span>
                                </td>
                                <td class="px-6 py-4 font-medium text-slate-900" x-text="formatWaktu(row.waktu)"></td>
                                <template x-for="(col, colIdx) in columns" :key="colIdx">
                                    <td class="px-6 py-4 text-center text-sm"
                                        :class="row[getColumnKey(col)] === 0 ? 'bg-red-50 text-red-700 font-semibold' :
                                            'text-slate-600'">
                                        <span
                                            x-text="row[getColumnKey(col)] !== null && row[getColumnKey(col)] !== undefined ? parseFloat(row[getColumnKey(col)]).toFixed(2) : '-'"></span>
                                    </td>
                                </template>
                            </tr>
                        </template>
                        <template x-if="tableData.length === 0 && dataLoaded">
                            <tr>
                                <td :colspan="columns.length + 2" class="px-6 py-12 text-center">
                                    <svg class="mx-auto h-10 w-10 text-slate-300 mb-3" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                    </svg>
                                    <p class="text-sm text-slate-500">Tidak ada data tersedia untuk tanggal yang dipilih
                                    </p>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <div x-show="!dataLoaded && !loading" x-cloak class="text-center py-16 px-6">
            <div class="flex justify-center mb-6">
                <div
                    class="relative h-24 w-24 rounded-full bg-gradient-to-br from-indigo-100 to-indigo-50 ring-4 ring-indigo-200 flex items-center justify-center">
                    <svg class="h-12 w-12 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
            </div>
            <h3 class="text-xl font-bold text-slate-900 mb-2">Belum Ada Data</h3>
            <p class="text-slate-500 mb-8 max-w-md mx-auto">Pilih logger dan tanggal, kemudian klik tombol "Cari" untuk
                menampilkan data sensor yang tersimpan.</p>
            <div class="inline-flex gap-3 bg-blue-50 px-6 py-4 rounded-xl ring-1 ring-blue-200">
                <svg class="h-5 w-5 text-blue-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2z"
                        clip-rule="evenodd" />
                </svg>
                <div class="text-left">
                    <p class="text-sm font-semibold text-blue-900">Petunjuk Penggunaan</p>
                    <p class="text-xs text-blue-700 mt-1">Pilih komponent logger dan tanggal untuk melihat data sensor
                        real-time</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function dataMasukManager() {
            return {
                filters: {
                    logger_id: '',
                    tanggal: new Date().toISOString().split('T')[0],
                },
                loggerDropdownOpen: false,
                loggerSearchQuery: '',
                loading: false,
                dataLoaded: false,
                errorMessage: '',
                tableData: [],
                columns: [],
                dataCompleteness: 0,

                getLoggerLabel(id) {
                    if (!id) return '-- Pilih Logger --';
                    const loggers = {!! json_encode($loggers->map(fn($l) => ['id' => $l->id_logger, 'nama' => $l->nama_logger ?? 'Logger'])) !!};
                    const logger = loggers.find(l => l.id == id);
                    return logger ? `${logger.id} - ${logger.nama}` : '-- Pilih Logger --';
                },

                filterLogger(id, nama) {
                    if (!this.loggerSearchQuery) return true;
                    const fullText = `${id} ${nama}`.toLowerCase();
                    return fullText.includes(this.loggerSearchQuery.toLowerCase());
                },

                closeLoggerDropdown() {
                    this.loggerDropdownOpen = false;
                },

                formatWaktu(waktu) {
                    if (!waktu) return '-';
                    const date = new Date(waktu);
                    return date.toLocaleString('id-ID', {
                        year: 'numeric',
                        month: '2-digit',
                        day: '2-digit',
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit'
                    });
                },

                getColumnKey(colName) {
                    // Convert "Muka Air Tanah" to "muka air tanah" (lowercase, with spaces preserved for matching)
                    return colName.toLowerCase();
                },

                async searchData() {
                    if (!this.filters.logger_id) {
                        this.errorMessage = 'Pilih logger terlebih dahulu.';
                        return;
                    }
                    if (!this.filters.tanggal) {
                        this.errorMessage = 'Pilih tanggal terlebih dahulu.';
                        return;
                    }

                    this.loading = true;
                    this.errorMessage = '';
                    this.dataLoaded = false;
                    this.tableData = [];
                    this.columns = [];

                    try {
                        const response = await fetch(
                            `/api/data-masuk?logger_id=${this.filters.logger_id}&tanggal=${this.filters.tanggal}`, {
                                headers: {
                                    'Accept': 'application/json',
                                }
                            });

                        const data = await response.json();
                        console.log('Response:', data);

                        if (response.ok && data.success) {
                            this.tableData = data.data || [];
                            this.columns = data.columns || [];
                            this.dataCompleteness = data.completeness || 0;
                            this.dataLoaded = true;

                            if (this.tableData.length === 0) {
                                this.errorMessage = 'Tidak ada data untuk logger dan tanggal yang dipilih.';
                            }
                        } else {
                            this.errorMessage = data.message || 'Gagal memuat data.';
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        this.errorMessage = 'Terjadi kesalahan saat memuat data: ' + error.message;
                    } finally {
                        this.loading = false;
                    }
                },

                resetFilter() {
                    this.filters.logger_id = '';
                    this.filters.tanggal = new Date().toISOString().split('T')[0];
                    this.tableData = [];
                    this.columns = [];
                    this.dataLoaded = false;
                    this.errorMessage = '';
                    this.dataCompleteness = 0;
                },

                exportToExcel() {
                    if (this.tableData.length === 0) {
                        alert('Tidak ada data untuk diekspor.');
                        return;
                    }

                    // Create workbook data
                    let csvContent = '\uFEFF'; // BOM for UTF-8

                    // Add header
                    csvContent += ['No', 'Waktu', ...this.columns].join(',') + '\n';

                    // Add data
                    this.tableData.forEach((row, idx) => {
                        const rowData = [
                            idx + 1,
                            this.formatWaktu(row.waktu),
                            ...this.columns.map(col => {
                                const val = row[this.getColumnKey(col)];
                                return val !== null && val !== undefined ? val : '';
                            })
                        ];
                        csvContent += rowData.join(',') + '\n';
                    });

                    // Create blob and download
                    const blob = new Blob([csvContent], {
                        type: 'text/csv;charset=utf-8;'
                    });
                    const link = document.createElement('a');
                    const url = URL.createObjectURL(blob);

                    link.setAttribute('href', url);
                    link.setAttribute('download', `data-masuk-${this.filters.logger_id}-${this.filters.tanggal}.csv`);
                    link.style.visibility = 'hidden';

                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                }
            };
        }
    </script>

    <style>
        [x-cloak] {
            display: none !important;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-in {
            animation: fadeIn 0.3s ease-out forwards;
        }
    </style>
@endsection
