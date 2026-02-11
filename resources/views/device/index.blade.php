@extends('layouts.app')

@section('content')
    <style>
        .input {
            @apply mt-1 w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm p-2 border;
        }

        .btn-primary {
            @apply bg-indigo-600 text-white px-4 py-2 rounded-md text-sm hover:bg-indigo-700;
        }

        .btn-secondary {
            @apply bg-white border px-4 py-2 rounded-md text-sm hover:bg-gray-100;
        }
    </style>

    <div x-data="deviceEditor()" x-cloak class="mt-6 space-y-6">

        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-slate-900">Pengaturan Device</h1>

            @if (session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
                    class="px-4 py-2 bg-emerald-100 text-emerald-700 rounded-lg text-sm font-semibold shadow-sm ring-1 ring-emerald-200">
                    {{ session('success') }}
                </div>
            @endif
        </div>


        <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600 whitespace-nowrap">
                    <thead class="bg-neutral-200 text-xs font-semibold uppercase text-neutral-950">
                        <tr>
                            <th scope="col" class="px-6 py-4">No</th>
                            <th scope="col" class="px-6 py-4">ID Logger</th>
                            <th scope="col" class="px-6 py-4">Nama Pos</th>
                            <th scope="col" class="px-6 py-4">Latitude</th>
                            <th scope="col" class="px-6 py-4">Longitude</th>
                            <th scope="col" class="px-6 py-4">Parameter</th>
                            <th scope="col" class="px-6 py-4">Kedalaman Sumur</th>
                            <th scope="col" class="px-6 py-4">Kedalaman Sensor</th>
                            <th scope="col" class="px-6 py-4">Kedalaman Pompa</th>
                            <th scope="col" class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @foreach ($devices as $index => $device)
                            <tr class="hover:bg-slate-50">
                                <td class="whitespace-nowrap px-6 py-4 font-medium text-slate-900">{{ $index + 1 }}</td>
                                <td class="whitespace-nowrap px-6 py-4">{{ $device['id_logger'] ?? '-' }}</td>
                                <td class="whitespace-nowrap px-6 py-4 font-medium text-slate-900">
                                    {{ $device['nama_lokasi'] ?? '-' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    {{ $device['lokasi'] ? $device['lokasi']->latitude : '-' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    {{ $device['lokasi'] ? $device['lokasi']->longitude : '-' }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-1.5 items-start">
                                        @foreach ($device['params'] as $param)
                                            @php
                                                $colorClass = 'bg-slate-100 text-slate-700'; // Default
                                                $name = strtolower($param['nama_parameter']);

                                                if (str_contains($name, 'humidity')) {
                                                    $colorClass = 'bg-sky-200 text-sky-700';
                                                } elseif (str_contains($name, 'muka')) {
                                                    $colorClass = 'bg-sky-100 text-sky-400';
                                                } elseif (str_contains($name, 'temp') || str_contains($name, 'suhu')) {
                                                    $colorClass = 'bg-orange-100 text-orange-700';
                                                } elseif (str_contains($name, 'bat') || str_contains($name, 'volt')) {
                                                    $colorClass = 'bg-emerald-100 text-emerald-700';
                                                } elseif (str_contains($name, 'kedalaman')) {
                                                    $colorClass = 'bg-amber-100 text-amber-700';
                                                } elseif (str_contains($name, 'tma')) {
                                                    $colorClass = 'bg-purple-100 text-purple-700';
                                                } elseif (str_contains($name, 'curah')) {
                                                    $colorClass = 'bg-green-100 text-green-700';
                                                }
                                                $displayName = $param['nama_parameter'];
                                            @endphp
                                            <span
                                                class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium {{ $colorClass }}">
                                                {{ $displayName ?? '-' }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    {{ $device['jiat']['kedalaman_sumur'] ?? '-' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    {{ $device['jiat']['kedalaman_sensor'] ?? '-' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    {{ $device['jiat']['kedalaman_pompa'] ?? '-' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-center">
                                    @permission('manage_device')
                                        {{-- <button @click='openModal(@js($device))' --}}
                                        <button @click="openModal({{ json_encode($device) }})"
                                            class="rounded-lg p-2 bg-slate-100 text-slate-950 hover:bg-slate-100 hover:text-indigo-600 transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                    @else
                                        <span class="text-xs text-slate-400">-</span>
                                    @endpermission
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-200 bg-white px-4 py-3 sm:px-6">
                {{-- Pagination would go here if needed --}}
            </div>
        </div>

        {{-- EDIT MODAL --}}
        <div x-show="isOpen" x-transition class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title"
            role="dialog" aria-modal="true" style="display: none;">

            <div class="flex min-h-screen items-end justify-center px-4 pb-20 pt-4 text-center sm:block sm:p-0">

                <!-- Overlay -->
                <div x-show="isOpen" x-transition.opacity class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                    @click="closeModal()"></div>

                <span class="hidden sm:inline-block sm:h-screen sm:align-middle">&#8203;</span>

                <!-- Modal Panel -->
                <div x-show="isOpen" x-transition
                    class="relative inline-block w-full max-w-4xl transform overflow-hidden rounded-xl bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:align-middle">

                    <!-- HEADER -->
                    <div class="flex items-center justify-between border-b px-6 py-4">
                        <h3 class="text-lg font-bold text-gray-900">Edit Pos</h3>
                        <button @click="closeModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form :action="editData.updateUrl" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="px-6 py-5 space-y-6 max-h-[70vh] overflow-y-auto">

                            <!-- Informasi Lokasi -->
                            <div class="bg-slate-50 p-4 rounded-lg border border-slate-100">
                                <h4 class="text-sm font-semibold text-gray-900 mb-4">Informasi Lokasi</h4>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700">Nama Pos</label>
                                        <input type="text" name="nama_lokasi" x-model="editData.nama_lokasi"
                                            class="mt-1 w-full rounded-md border-gray-300 shadow-sm p-2 border text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700">Latitude</label>
                                        <input type="text" name="latitude" x-model="editData.latitude"
                                            class="mt-1 w-full rounded-md border-gray-300 shadow-sm p-2 border text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700">Longitude</label>
                                        <input type="text" name="longitude" x-model="editData.longitude"
                                            class="mt-1 w-full rounded-md border-gray-300 shadow-sm p-2 border text-sm">
                                    </div>
                                </div>
                            </div>

                            <!-- Kedalaman Sensor -->
                            <div class="bg-slate-50 p-4 rounded-lg border border-slate-100">
                                <h4 class="text-sm font-semibold text-gray-900 mb-4">Kedalaman Sensor</h4>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700">Sumur (m)</label>
                                        <input type="number" step="0.01" name="kedalaman_sumur"
                                            x-model="editData.kedalaman_sumur"
                                            class="mt-1 w-full rounded-md border-gray-300 shadow-sm p-2 border text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700">Sensor (m)</label>
                                        <input type="number" step="0.01" name="kedalaman_sensor"
                                            x-model="editData.kedalaman_sensor"
                                            class="mt-1 w-full rounded-md border-gray-300 shadow-sm p-2 border text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700">Pompa (m)</label>
                                        <input type="number" step="0.01" name="kedalaman_pompa"
                                            x-model="editData.kedalaman_pompa"
                                            class="mt-1 w-full rounded-md border-gray-300 shadow-sm p-2 border text-sm">
                                    </div>
                                </div>
                            </div>

                            <!-- Parameter -->
                            <div class="bg-slate-50 p-4 rounded-lg border border-slate-100">
                                <h4 class="text-sm font-semibold text-gray-900 mb-4">List Parameter</h4>

                                <div class="overflow-x-auto">
                                    <table class="w-full text-sm border rounded-lg overflow-hidden">
                                        <thead class="bg-gray-100">
                                            <tr>
                                                <th class="p-2 text-left">Nama Parameter</th>
                                                <th class="p-2 text-left">Kolom Sensor</th>
                                                <th class="p-2 text-left">Satuan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <template x-for="(param, index) in editData.params"
                                                :key="param.id ?? 'param_' + index">
                                                <tr class="border-t">
                                                    <td class="p-2">
                                                        <input :name="'params[' + param.id_param + '][nama_parameter]'"
                                                            x-model="param.nama_parameter"
                                                            class="w-full rounded-md border-gray-300 shadow-sm p-2 border text-sm">
                                                    </td>
                                                    <td class="p-2">
                                                        {{-- <select :key="'sensor-' + param.id_param + '-' + param.kolom_sensor"
                                                            :name="'params[' + param.id_param + '][kolom_sensor]'"
                                                            x-model="param.kolom_sensor" class="w-full rounded-md border-gray-300 shadow-sm p-2 border text-sm">

                                                            <option value="">Pilih Sensor</option>
                                                            <template x-for="sensor in sensorOptions"
                                                                :key="sensor">
                                                                <option :value="sensor" x-text="sensor"></option>
                                                            </template>
                                                        </select> --}}
                                                        <select :name="'params[' + param.id_param + '][kolom_sensor]'"
                                                            class="w-full rounded-md border-gray-300 shadow-sm p-2 border text-sm"
                                                            @change="param.kolom_sensor = $event.target.value">
                                                            <option value="">Pilih Sensor</option>

                                                            <template x-for="sensor in sensorOptions"
                                                                :key="sensor">
                                                                <option :value="sensor" x-text="sensor"
                                                                    :selected="sensor === param.kolom_sensor"></option>
                                                            </template>
                                                        </select>

                                                    </td>
                                                    <td class="p-2">
                                                        <input :name="'params[' + param.id_param + '][satuan]'"
                                                            x-model="param.satuan"
                                                            class="w-24 rounded-md border-gray-300 shadow-sm p-2 border text-sm">
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>

                        <!-- FOOTER -->
                        <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3">
                            <button type="button" @click="closeModal()"
                                class="rounded-md border bg-white px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                Batal
                            </button>
                            <button type="submit"
                                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('deviceEditor', () => ({
                isOpen: false,
                ready: false,
                sensorOptions: [],
                editData: {
                    updateUrl: '',
                    id_logger: '',
                    nama_lokasi: '',
                    latitude: '',
                    longitude: '',
                    kedalaman_sumur: '',
                    kedalaman_sensor: '',
                    kedalaman_pompa: '',
                    params: []
                },

                init() {
                    this.sensorOptions = Array.from({
                        length: 19
                    }, (_, i) => 'sensor' + (i + 1))
                },

                openModal(device) {
                    Object.assign(this.editData, {
                        updateUrl: `/pengaturan-device/${device.id_logger}`,
                        id_logger: device.id_logger,
                        nama_lokasi: device.lokasi?.nama_lokasi ?? '',
                        latitude: device.lokasi?.latitude ?? '',
                        longitude: device.lokasi?.longitude ?? '',
                        kedalaman_sumur: device.jiat?.kedalaman_sumur ?? '',
                        kedalaman_sensor: device.jiat?.kedalaman_sensor ?? '',
                        kedalaman_pompa: device.jiat?.kedalaman_pompa ?? ''
                    })

                    // this.editData.params = (device.params ?? []).map(p => ({
                    //     id: p.id_param,
                    //     nama_parameter: p.nama_parameter ?? '',
                    //     kolom_sensor: p.kolom_sensor ?? '',
                    //     satuan: p.satuan ?? ''
                    // }))

                    // this.isOpen = true
                    // Kosongkan dulu biar Alpine reset DOM
                    this.editData.params = []

                    // Buka modal dulu
                    this.isOpen = true

                    // Tunggu DOM siap, baru inject params
                    this.$nextTick(() => {
                        this.editData.params = (device.params ?? []).map(p => ({
                            id_param: p.id_param,
                            nama_parameter: p.nama_parameter ?? '',
                            kolom_sensor: p.kolom_sensor ?? '',
                            satuan: p.satuan ?? ''
                        }))
                    })
                },

                closeModal() {
                    this.isOpen = false
                }
            }))
        })
    </script>
@endsection
