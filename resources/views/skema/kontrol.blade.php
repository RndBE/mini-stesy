@extends('layouts.app')

@section('content')
<div x-data="gateControl()" class="min-h-screen bg-slate-50 text-slate-800 -m-4 sm:-m-0 sm:p-2">
    <!-- Header Area -->
    <div class="flex items-center justify-between mb-6 p-4 sm:p-0">
        <div class="flex items-center gap-4">
            <a href="{{ route('skema-irigasi.index') }}" class="flex items-center justify-center w-8 h-8 rounded-full hover:bg-slate-200 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-900" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-slate-800">
                    {{ $node['nama_logger'] ?? $node['label'] ?? $node['id'] }}
                </h1>
                <div class="flex items-center gap-1.5 mt-0.5">
                    @if(isset($node['is_online']) && $node['is_online'])
                        <span class="relative flex h-2.5 w-2.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-green-500"></span>
                        </span>
                        <span class="text-xs font-semibold text-green-600">Koneksi Terhubung</span>
                    @else
                        <span class="relative flex h-2.5 w-2.5">
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-500"></span>
                        </span>
                        <span class="text-xs font-medium text-red-600">Koneksi Terputus</span>
                    @endif
                </div>
            </div>
        </div>
        <div>
            @if(isset($node['id_logger']))
            <a href="{{ route('analisa.index', ['id_logger' => $node['id_logger']]) }}" class="flex items-center gap-2 px-6 py-2 bg-white border border-indigo-200 text-indigo-900 font-semibold rounded-lg shadow-sm hover:bg-indigo-50 hover:border-indigo-300 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                Analisa
            </a>
            @endif
        </div>
    </div>

    <!-- Main Content Area: Split 2 columns -->
    <div class="flex flex-col lg:flex-row gap-6">
        
        <!-- ======================= -->
        <!-- LEFT PANEL: Panel Kontrol -->
        <!-- ======================= -->
        <div class="w-full lg:w-[350px] flex-shrink-0 flex flex-col gap-4">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 w-full flex-1">
                <h2 class="text-lg font-bold text-slate-800 mb-4">Panel Kontrol</h2>
@if(isset($node['saluran']))
                <div class="mb-4 bg-indigo-50 border border-indigo-200 rounded-lg px-3 py-2">
                    <p class="text-[10px] font-bold text-indigo-500 uppercase tracking-wide">Saluran</p>
                    <p class="text-sm font-semibold text-indigo-900 leading-tight mt-0.5">{{ $node['saluran'] }}</p>
                    @if(isset($node['elevasi_m']))
                    <p class="text-[10px] text-indigo-400 mt-1">Elevasi: {{ $node['elevasi_m'] }} m dpl</p>
                    @endif
                </div>
                @endif
@if(isset($node['tma_hulu_cm']) || isset($node['debit_m3s']))
                <div class="grid grid-cols-2 gap-2 mb-5">
                    @if(isset($node['tma_hulu_cm']))
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-center">
                        <p class="text-[10px] font-bold text-blue-500 uppercase tracking-wide">TMA Hulu</p>
                        <p class="text-xl font-bold text-blue-800 leading-none mt-1">{{ $node['tma_hulu_cm'] }}</p>
                        <p class="text-[10px] text-blue-400 mt-0.5">cm</p>
                    </div>
                    @endif
                    @if(isset($node['tma_hilir_cm']))
                    <div class="bg-cyan-50 border border-cyan-200 rounded-lg p-3 text-center">
                        <p class="text-[10px] font-bold text-cyan-500 uppercase tracking-wide">TMA Hilir</p>
                        <p class="text-xl font-bold text-cyan-800 leading-none mt-1">{{ $node['tma_hilir_cm'] }}</p>
                        <p class="text-[10px] text-cyan-400 mt-0.5">cm</p>
                    </div>
                    @endif
                    @if(isset($node['debit_m3s']))
                    <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-3 text-center col-span-1">
                        <p class="text-[10px] font-bold text-emerald-500 uppercase tracking-wide">Debit</p>
                        <p class="text-xl font-bold text-emerald-800 leading-none mt-1">{{ number_format($node['debit_m3s'], 2) }}</p>
                        <p class="text-[10px] text-emerald-400 mt-0.5">m³/dtk</p>
                    </div>
                    @endif
                    @if(isset($node['kapasitas_m3s']))
                    <div class="bg-slate-50 border border-slate-200 rounded-lg p-3 text-center col-span-1">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Kapasitas</p>
                        <p class="text-xl font-bold text-slate-700 leading-none mt-1">{{ number_format($node['kapasitas_m3s'], 2) }}</p>
                        <p class="text-[10px] text-slate-400 mt-0.5">m³/dtk</p>
                    </div>
                    @endif
                </div>
                @endif

                <hr class="border-slate-200 mb-4">
<div class="mb-5">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Pilih Pintu</label>
                    <div class="relative">
                        <select x-model="selectedGateId" class="block w-full pl-3 pr-10 py-2.5 text-sm border-indigo-200 bg-indigo-50/50 rounded-lg focus:outline-none focus:ring-0 focus:border-indigo-400 appearance-none text-indigo-900 font-semibold">
                            <template x-for="gate in gates" :key="gate.sensor_id">
                                <option :value="gate.sensor_id" x-text="gate.name"></option>
                            </template>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-indigo-500">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>
<div class="bg-slate-50/50 border border-slate-100 rounded-xl p-4 mb-4">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-bold text-slate-700 text-sm">Bukaan Pintu Saat Ini</h3>
                        @if(isset($node['last_time']))
                        <span class="text-[10px] font-bold text-green-600 font-mono bg-green-50 px-2 py-1 rounded">{{ $node['last_time'] }}</span>
                        @endif
                    </div>
                    <div class="bg-white border border-slate-200 rounded-lg p-3 text-center shadow-sm">
                        <p class="text-xs font-semibold text-slate-500 mb-1" x-text="selectedGateName"></p>
                        <p class="text-2xl font-bold text-slate-800"><span x-text="currentValue"></span> <span class="text-sm text-slate-400">%</span></p>
                        <p class="text-xs text-slate-400 mt-1" x-text="'(Maksimal pintu: ' + activeMaxBukaan + ' cm)'"></p>
                    </div>
                </div>

                <button @click="submitControl()"
                    :disabled="workflowRunning"
                    class="w-full bg-[#2e3188] hover:bg-indigo-900 text-white font-bold py-3.5 px-4 rounded-lg shadow-md transition-all active:scale-95 text-sm disabled:opacity-60 disabled:cursor-not-allowed"
                    :class="workflowRunning ? 'animate-pulse' : ''">
                    <span x-text="workflowRunning ? 'Mengirim...' : 'Kirim Perintah Kontrol'"></span>
                </button>

            </div>
        </div>
<div x-show="workflowVisible" x-cloak class="fixed inset-0 z-[200]" role="dialog" aria-modal="true" style="display:none;">
<div x-show="workflowVisible"
                x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-slate-900/40 transition-opacity"
                @click="!workflowRunning && (workflowVisible = false)"></div>

            <div class="fixed inset-0 flex items-center justify-center p-4">
                <div x-show="workflowVisible"
                    x-transition:enter="ease-out duration-250"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                    class="relative w-full max-w-sm rounded-2xl bg-white shadow-2xl overflow-hidden"
                    @click.stop>
<div class="px-6 pt-6 pb-4 border-b border-slate-100">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
<div class="flex h-10 w-10 items-center justify-center rounded-xl"
                                    :class="workflowRunning ? 'bg-indigo-100' : workflowSuccess ? 'bg-emerald-100' : 'bg-red-100'">
                                    <template x-if="workflowRunning">
                                        <span class="inline-block h-5 w-5 rounded-full border-2 border-indigo-600 border-t-transparent animate-spin"></span>
                                    </template>
                                    <template x-if="!workflowRunning && workflowSuccess">
                                        <svg class="h-5 w-5 text-emerald-600" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 010 1.42l-7.2 7.2a1 1 0 01-1.415 0l-3-3a1 1 0 111.414-1.42l2.293 2.295 6.493-6.495a1 1 0 011.415 0z" clip-rule="evenodd" />
                                        </svg>
                                    </template>
                                    <template x-if="!workflowRunning && !workflowSuccess">
                                        <svg class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    </template>
                                </div>
                                <div>
                                    <p class="text-base font-bold text-slate-900"
                                       x-text="workflowRunning ? 'Mengirim Perintah...' : workflowSuccess ? 'Berhasil Dikirim!' : 'Gagal Mengirim'"></p>
                                    <p class="text-xs text-slate-500 mt-0.5"
                                       x-text="workflowRunning ? 'Menunggu respons dari logger...' : workflowSuccess ? 'Perintah kontrol berhasil dieksekusi.' : 'Periksa koneksi dan coba lagi.'"></p>
                                </div>
                            </div>
<button @click="workflowVisible = false"
                                class="ml-2 flex h-7 w-7 items-center justify-center rounded-full text-slate-400 transition-colors"
                                :class="workflowRunning ? 'opacity-30 cursor-not-allowed' : 'hover:bg-slate-100 hover:text-slate-700'"
                                :disabled="workflowRunning">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
<div class="px-6 pt-4">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Overall Progress</span>
                            <span class="text-xs font-bold"
                                  :class="workflowSuccess && !workflowRunning ? 'text-emerald-600' : 'text-slate-700'"
                                  x-text="`${workflowPercent()}%`"></span>
                        </div>
                        <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full transition-all duration-500"
                                 :class="workflowSuccess && !workflowRunning ? 'bg-emerald-500' : 'bg-indigo-500'"
                                 :style="`width: ${workflowPercent()}%`"></div>
                        </div>
                    </div>
<div class="px-6 py-4 space-y-2">
                        <template x-for="step in workflowSteps" :key="step.key">
                            <div class="rounded-xl border p-3 transition-all"
                                :class="step.status === 'done' ? 'border-emerald-200 bg-emerald-50/60' : step.status === 'active' ? 'border-indigo-200 bg-indigo-50/50' : step.status === 'error' ? 'border-red-200 bg-red-50/50' : 'border-slate-100 bg-slate-50/60 opacity-60'">
                                <div class="flex items-center gap-2.5">
                                    <div class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-lg"
                                        :class="step.status === 'done' ? 'bg-emerald-100' : step.status === 'active' ? 'bg-indigo-100' : step.status === 'error' ? 'bg-red-100' : 'bg-slate-100'">
                                        <template x-if="step.status === 'done'">
                                            <svg class="h-3.5 w-3.5 text-emerald-500" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 010 1.42l-7.2 7.2a1 1 0 01-1.415 0l-3-3a1 1 0 111.414-1.42l2.293 2.295 6.493-6.495a1 1 0 011.415 0z" clip-rule="evenodd" />
                                            </svg>
                                        </template>
                                        <template x-if="step.status === 'active'">
                                            <span class="inline-block h-3.5 w-3.5 rounded-full border-2 border-indigo-500 border-t-transparent animate-spin"></span>
                                        </template>
                                        <template x-if="step.status === 'error'">
                                            <svg class="h-3.5 w-3.5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            </svg>
                                        </template>
                                        <template x-if="step.status === 'pending'">
                                            <span class="h-1.5 w-1.5 rounded-full bg-slate-300"></span>
                                        </template>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-semibold leading-tight"
                                           :class="step.status === 'pending' ? 'text-slate-400' : step.status === 'error' ? 'text-red-700' : 'text-slate-800'"
                                           x-text="step.title"></p>
                                        <p class="text-xs mt-0.5 leading-tight"
                                           :class="step.status === 'error' ? 'text-red-500' : 'text-slate-400'"
                                           x-text="step.subtitle"></p>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
<div x-show="!workflowRunning" class="px-6 pb-5">
                        <button @click="workflowVisible = false"
                            class="w-full py-2.5 rounded-xl text-sm font-bold transition-all active:scale-95"
                            :class="workflowSuccess ? 'bg-emerald-600 hover:bg-emerald-700 text-white' : 'bg-red-600 hover:bg-red-700 text-white'">
                            <span x-text="workflowSuccess ? 'Selesai' : 'Tutup'"></span>
                        </button>
                    </div>

                </div>
            </div>
        </div>

        <!-- ======================= -->
        <!-- RIGHT PANEL: Kontrol Visual -->
        <!-- ======================= -->
        <div class="w-full lg:flex-1 bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex flex-col items-center justify-center min-h-[500px]">
            <div class="flex items-center justify-between w-full mb-8">
                <h2 class="text-xl font-bold text-slate-800">Kontrol Pintu</h2>
            </div>
            
            <!-- Graphic Card Wrapper -->
            <div class="bg-slate-50 rounded-2xl border border-slate-200 p-6 sm:p-8 w-full max-w-md relative flex flex-col items-center">
                <!-- Header Indicator -->
                <div class="flex items-center justify-between w-full mb-8">
                    <h3 class="font-bold text-slate-800" x-text="selectedGateName"></h3>
                    <div class="flex gap-1.5">
                        <span class="w-4 h-4 rounded-full bg-red-500 text-[9px] text-white flex items-center justify-center font-bold">R</span>
                        <span class="w-4 h-4 rounded-full bg-yellow-400 text-[9px] text-white flex items-center justify-center font-bold">S</span>
                        <span class="w-4 h-4 rounded-full bg-green-500 text-[9px] text-white flex items-center justify-center font-bold">T</span>
                    </div>
                </div>

                <!-- SVG Graphics Visualization -->
                <!-- The visuals copy the concept of blue structures and a brown gate descending -->
                <div class="relative w-72 h-72 mb-10 flex justify-center">
                    <!-- Background Water / Flow (Optional abstract) -->
                    
                    <!-- Frame SVG dari Desainer -->
                    <img src="{{ asset('kontrol-pintu/frame-pintu.svg') }}" class="absolute inset-0 w-full h-full object-contain z-20 pointer-events-none drop-shadow-md" alt="Frame Pintu" />

                    <!-- Bounding Bx untuk lintasan pintu (Dari bawah palang atas sampai dasar wadah) -->
                    <div class="absolute top-28 bottom-1 left-10 right-10 z-0 overflow-hidden flex flex-col justify-end">
                        <!-- Movable SVG Gate -->
                        <img src="{{ asset('kontrol-pintu/gate.svg') }}" 
                             class="w-full object-contain drop-shadow-md" 
                             :style="`transform: translateY(-${(targetValue / 100) * 65}%); transition: transform 0.3s linear;`" 
                             alt="Daun Pintu" />
                    </div>
                </div>

                <!-- Target Adjustment Control -->
                <div class="w-full">
                    <p class="text-center text-sm font-bold text-slate-800 mb-3">Set Ketinggian</p>
                    <div class="flex items-center justify-center gap-3">
                        <button @click="decrement()" class="w-10 h-10 rounded shadow-sm bg-indigo-100 hover:bg-indigo-200 text-indigo-900 font-bold text-xl flex flex-shrink-0 items-center justify-center transition-colors">
                            -
                        </button>
                        <div class="bg-white border border-slate-200 w-24 h-12 rounded shadow-inner flex items-center justify-center">
                            <input type="number" min="0" x-model.number="targetValue" class="w-full h-full text-center text-2xl font-bold text-slate-800 border-none outline-none focus:ring-0 p-0 appearance-none" style="-moz-appearance: textfield;" />
                        </div>
                        <button @click="increment()" class="w-10 h-10 rounded shadow-sm bg-[#2e3188] hover:bg-indigo-900 text-white font-bold text-xl flex flex-shrink-0 items-center justify-center transition-colors">
                            +
                        </button>
                        <span class="text-sm font-bold text-slate-500 ml-1">%</span>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function gateControl() {
        return {
            gates: @json($node['gates'] ?? [['sensor_id' => 'sensor1', 'name' => 'Pintu Utama', 'bukaan_persen' => $node['bukaan_persen'] ?? 0]]),
            selectedGateId: '',
            currentValue: 0,
            targetValue: 0,
            idLogger: '{{ $node['id_logger'] ?? '' }}',
            workflowVisible: false,
            workflowRunning: false,
            workflowSuccess: false,
            workflowSteps: [],
            workflowTimers: [],

            init() {
                if (this.gates.length > 0) {
                    this.selectedGateId = this.gates[0].sensor_id;
                    this.updateValuesFromGate();
                }
                
                this.$watch('selectedGateId', () => {
                    this.updateValuesFromGate();
                });
            },

            get selectedGateName() {
                let gate = this.gates.find(g => g.sensor_id === this.selectedGateId);
                return gate ? gate.name : 'Pilih Pintu';
            },

            updateValuesFromGate() {
                let gate = this.gates.find(g => g.sensor_id === this.selectedGateId);
                if (gate) {
                    let max = gate.max_cm || 100;
                    let percentage = Math.round((gate.bukaan_persen / max) * 100);
                    if (percentage < 0) percentage = 0;
                    if (percentage > 100) percentage = 100;

                    this.currentValue = percentage;
                    this.targetValue = percentage;
                }
            },

            get activeMaxBukaan() {
                let gate = this.gates.find(g => g.sensor_id === this.selectedGateId);
                return gate ? (gate.max_cm || 100) : 100;
            },

            increment() {
                if (this.targetValue < 100) {
                    this.targetValue += 1;
                }
            },
            decrement() {
                if (this.targetValue > 0) {
                    this.targetValue -= 1;
                }
            },
            workflowPercent() {
                if (!this.workflowSteps.length) return 0
                const done = this.workflowSteps.filter(s => s.status === 'done').length
                const active = this.workflowSteps.some(s => s.status === 'active') ? 0.65 : 0
                return Math.round(((done + active) / this.workflowSteps.length) * 100)
            },

            resetWorkflow() {
                this.workflowTimers.forEach(t => clearTimeout(t))
                this.workflowTimers = []
                this.workflowRunning = false
                this.workflowSuccess = false
                this.workflowSteps = []
            },

            markStep(key, status, subtitle) {
                this.workflowSteps = this.workflowSteps.map(s =>
                    s.key === key ? { ...s, status, subtitle: subtitle ?? s.subtitle } : s
                )
            },
            getGateVisualHeight() {
                let percent = this.targetValue;
                let barrierHeight = 100 - percent; 
                if (barrierHeight < 0) barrierHeight = 0;
                if (barrierHeight > 100) barrierHeight = 100;
                return barrierHeight;
            },

            submitControl() {
                if (!this.idLogger) {
                    Swal.fire('Error', 'ID Logger tidak ditemukan pada node ini', 'error');
                    return;
                }

                Swal.fire({
                    title: 'Kirim Perintah Kontrol?',
                    text: `Pintu ${this.selectedGateName} akan mengubah target bukaan menjadi ${this.targetValue}%.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#2e3188',
                    cancelButtonColor: '#ef4444',
                    confirmButtonText: 'Ya, Eksekusi!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (!result.isConfirmed) return
                    this.resetWorkflow()
                    this.workflowVisible = true
                    this.workflowRunning = true
                    this.workflowSuccess = false

                    const targetGate = this.selectedGateName
                    const targetPct  = this.targetValue

                    this.workflowSteps = [
                        { key: 'confirm', title: 'Confirm action',            subtitle: `Target: ${targetGate} → ${targetPct}%`,   status: 'done'   },
                        { key: 'mqtt',   title: 'Connecting to MQTT broker',  subtitle: 'Connecting...',                           status: 'active' },
                        { key: 'logger', title: 'Connecting to logger',        subtitle: 'Waiting for device session...',           status: 'pending' },
                        { key: 'ack',    title: 'Sending command & waiting ACK', subtitle: 'Waiting response from logger...',      status: 'pending' },
                    ]
                    this.workflowTimers.push(setTimeout(() => {
                        this.markStep('mqtt', 'done', 'MQTT connected')
                        this.markStep('logger', 'active', 'Connecting...')
                    }, 1100))
                    this.workflowTimers.push(setTimeout(() => {
                        this.markStep('logger', 'done', 'Logger connected')
                        this.markStep('ack', 'active', 'Sending command...')

                        fetch("{{ route('awgc.command.store') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                node_skema_id: '{{ $node['id'] ?? '' }}',
                                id_logger: this.idLogger,
                                sensor_id: this.selectedGateId,
                                target_bukaan_persen: this.targetValue,
                                elevasi_target_cm: Math.round((this.targetValue / 100) * this.activeMaxBukaan)
                            })
                        })
                        .then(res => {
                            if (!res.ok) return res.json().then(e => { throw new Error(e.message || 'HTTP ' + res.status) })
                            return res.json()
                        })
                        .then(data => {
                            if (data.success) {
                                this.markStep('ack', 'done', 'Acknowledgment received')
                                this.workflowRunning = false
                                this.workflowSuccess = true
                                this.currentValue = this.targetValue

                                let gateIndex = this.gates.findIndex(g => g.sensor_id === this.selectedGateId)
                                if (gateIndex !== -1) {
                                    this.gates[gateIndex].bukaan_persen = Math.round((this.targetValue / 100) * this.activeMaxBukaan)
                                }
                                this.workflowTimers.push(setTimeout(() => {
                                    this.workflowVisible = false
                                }, 5000))
                            } else {
                                this.markStep('ack', 'error', data.message || 'Perintah tidak dapat diproses.')
                                this.workflowRunning = false
                                this.workflowSuccess = false
                            }
                        })
                        .catch(err => {
                            console.error(err)
                            this.markStep('ack', 'error', err.message || 'Kesalahan saat menghubungi API.')
                            this.workflowRunning = false
                            this.workflowSuccess = false
                        })
                    }, 2400))
                })
            }
        }
    }
</script>
@endpush
@endsection
