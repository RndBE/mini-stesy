@extends('layouts.app')

@push('head')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #addDeviceMap {
            height: 320px;
            width: 100%;
            border-radius: 8px;
            z-index: 1;
        }
    </style>
@endpush

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

            <div class="flex items-center gap-3">
                @if (session('success'))
                    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
                        class="px-4 py-2 bg-emerald-100 text-emerald-700 rounded-lg text-sm font-semibold shadow-sm ring-1 ring-emerald-200">
                        {{ session('success') }}
                    </div>
                @endif

                @permission('manage_device')
                @endpermission
                <div class="relative w-full sm:w-64">
                    <input type="text" x-model="searchQuery" placeholder="Cari device..."
                        class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2 pr-10 text-sm text-slate-900 placeholder-slate-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    <svg class="absolute right-3 top-2.5 h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>
        </div>


        <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600 whitespace-nowrap">
                    <thead class="bg-neutral-200 text-xs font-semibold uppercase text-neutral-950">
                        <tr>
                            <th scope="col" class="px-6 py-4">No</th>
                            <th scope="col" class="px-6 py-4">ID Logger</th>
                            <th scope="col" class="px-6 py-4">Nama Pos</th>
                            <th scope="col" class="px-6 py-4">Alamat</th>
                            <th scope="col" class="px-6 py-4">Latitude</th>
                            <th scope="col" class="px-6 py-4">Longitude</th>
                            <th scope="col" class="px-6 py-4">Parameter</th>
                            <th scope="col" class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        <template x-for="(device, index) in filteredDevices()" :key="device.id_logger || index">
                            <tr class="hover:bg-slate-50">
                                <td class="whitespace-nowrap px-6 py-4 font-medium text-slate-900" x-text="index + 1"></td>
                                <td class="whitespace-nowrap px-6 py-4" x-text="device.id_logger || '-'"></td>
                                <td class="whitespace-nowrap px-6 py-4 font-medium text-slate-900"
                                    x-text="device.nama_lokasi || '-'"></td>
                                <td class="whitespace-nowrap px-6 py-4 font-medium text-slate-900"
                                    x-text="device.alamat || '-'"></td>
                                <td class="whitespace-nowrap px-6 py-4"
                                    x-text="device.lokasi ? device.lokasi.latitude : '-'"></td>
                                <td class="whitespace-nowrap px-6 py-4"
                                    x-text="device.lokasi ? device.lokasi.longitude : '-'"></td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-1.5 items-start">
                                        <template x-for="(param, paramIndex) in (device.params || [])"
                                            :key="param.id_param || paramIndex">
                                            <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium"
                                                :class="paramColorClass(param.nama_parameter)"
                                                x-text="param.nama_parameter || '-'"></span>
                                        </template>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-center">
                                    @permission('manage_device')
                                        <template x-if="!device.lokasi">
                                            <button @click="openAddModal(device)" title="Setup Device"
                                                class="rounded-lg p-2 bg-slate-100 text-slate-950 hover:bg-slate-100 hover:text-slate-600 transition-colors">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                            </button>
                                        </template>
                                        <template x-if="device.lokasi">
                                            <button @click="openDetailModal(device)" title="Detail Device"
                                                class="rounded-lg p-2 bg-emerald-100 hover:bg-emerald-200 transition-colors">
                                                <img src="{{ asset('icons/detail_icon.svg') }}"
                                                    class="h-5 w-5 transition duration-200 ease-out filter hover:invert hover:sepia hover:saturate-[700%] hover:hue-rotate-[85deg] hover:brightness-95"
                                                    alt="Detail">
                                            </button>
                                        </template>
                                        <template x-if="device.lokasi">
                                            <button @click="openModal(device)" title="Edit Device"
                                                class="rounded-lg p-2 bg-blue-100 hover:bg-blue-200 transition-colors">
                                                <img src="{{ asset('icons/edit_icon.svg') }}"
                                                    class="h-5 w-5 transition duration-200 ease-out filter hover:invert hover:sepia hover:saturate-[500%] hover:hue-rotate-[190deg] hover:brightness-90"
                                                    alt="Edit">
                                            </button>
                                        </template>
                                    @else
                                        <span class="text-xs text-slate-400">-</span>
                                    @endpermission
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <template x-if="filteredDevices().length === 0">
                <div class="p-6 text-center text-slate-500">
                    Tidak ada data device.
                </div>
            </template>

            <div class="border-t border-slate-200 bg-white px-4 py-3 sm:px-6">
                {{-- Pagination would go here if needed --}}
            </div>
        </div>

        {{-- DETAIL DEVICE MODAL --}}
        <div x-show="showDetailModal" class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="detail-modal-title" role="dialog" aria-modal="true" style="display: none;">
            <div class="flex min-h-screen items-end justify-center px-4 pb-20 pt-4 text-center sm:block sm:p-0">
                <div x-show="showDetailModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeDetailModal()">
                </div>

                <span class="hidden sm:inline-block sm:h-screen sm:align-middle">&#8203;</span>

                <div x-show="showDetailModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="relative inline-block w-full max-w-6xl transform overflow-hidden rounded-xl bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:align-middle">

                    <div class="flex items-start justify-between border-b border-slate-200 px-6 py-4">
                        <div class="flex items-start gap-3">
                            <div class="rounded-lg border border-slate-200 bg-white p-2 text-slate-700 px-4 py-3">
                                <img src="{{ asset('icons/detail_dark_icon.svg') }}" class="h-6 w-6">
                            </div>
                            <div>
                                <h3 id="detail-modal-title" class="text-2xl font-bold text-slate-900">Detail Device</h3>
                                <p class="text-sm text-slate-500">Informasi lengkap tentang device.</p>
                            </div>
                        </div>
                        <button @click="closeDetailModal()" class="text-slate-500 hover:text-slate-700">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 gap-4 px-4 py-4 lg:grid-cols-2">
                        <div class="space-y-4">
                            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
                                <div class="flex items-center gap-2 border-b border-slate-200 px-4 py-3">
                                    <img src="{{ asset('icons/identitas_icon.svg') }}" class="h-5 w-5">
                                    <p class="text-sm font-bold text-slate-900">Identitas Device</p>
                                </div>
                                <div class="grid grid-cols-1 gap-4 px-4 py-3 text-sm sm:grid-cols-3">
                                    <div>
                                        <p class="text-xs uppercase text-slate-400">ID Logger</p>
                                        <p class="mt-1 font-semibold text-slate-900" x-text="detailData.id_logger || '-'">
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs uppercase text-slate-400">Nama Pos</p>
                                        <p class="mt-1 font-semibold text-slate-900"
                                            x-text="detailData.nama_lokasi || '-'"></p>
                                    </div>
                                    <div>
                                        <p class="text-xs uppercase text-slate-400">Nama Logger</p>
                                        <p class="mt-1 font-semibold text-slate-900"
                                            x-text="detailData.nama_logger || '-'"></p>
                                    </div>
                                </div>
                            </div>

                            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
                                <div class="flex items-center gap-2 border-b border-slate-200 px-4 py-3">
                                    <img src="{{ asset('icons/lokasi_icon.svg') }}" class="h-5 w-5">
                                    <p class="text-sm font-bold text-slate-900">Lokasi Pos</p>
                                </div>
                                <div class="grid grid-cols-1 gap-4 px-4 py-3 text-sm sm:grid-cols-3">
                                    <div>
                                        <p class="text-xs uppercase text-slate-400">Alamat</p>
                                        <p class="mt-1 font-semibold text-slate-900" x-text="detailData.alamat || '-'">
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs uppercase text-slate-400">Latitude</p>
                                        <p class="mt-1 font-semibold text-slate-900" x-text="detailData.latitude || '-'">
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs uppercase text-slate-400">Longitude</p>
                                        <p class="mt-1 font-semibold text-slate-900" x-text="detailData.longitude || '-'">
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
                                <div class="flex items-center gap-2 border-b border-slate-200 px-4 py-3">
                                    <img src="{{ asset('icons/sub_kategori_icon.svg') }}" class="h-5 w-5">
                                    <p class="text-sm font-bold text-slate-900">Sub Kategori</p>
                                </div>
                                <div class="grid grid-cols-1 gap-4 px-4 py-3 text-sm sm:grid-cols-4">
                                    <div>
                                        <p class="text-xs uppercase text-slate-400">Sub Kategori</p>
                                        <p class="mt-1 font-semibold text-slate-900"
                                            x-text="detailData.sub_kategori || '-'"></p>
                                    </div>
                                    <div>
                                        <p class="text-xs uppercase text-slate-400">Kedalaman Sumur</p>
                                        <p class="mt-1 font-semibold text-slate-900"
                                            x-text="detailData.kedalaman_sumur || '-'"></p>
                                    </div>
                                    <div>
                                        <p class="text-xs uppercase text-slate-400">Kedalaman Sensor</p>
                                        <p class="mt-1 font-semibold text-slate-900"
                                            x-text="detailData.kedalaman_sensor || '-'"></p>
                                    </div>
                                    <div>
                                        <p class="text-xs uppercase text-slate-400">Kedalaman Pompa</p>
                                        <p class="mt-1 font-semibold text-slate-900"
                                            x-text="detailData.kedalaman_pompa || '-'"></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
                                <div class="flex items-center gap-2 border-b border-slate-200 px-4 py-3">
                                    <img src="{{ asset('icons/param_icon.svg') }}" class="h-5 w-5">
                                    <p class="text-sm font-bold text-slate-900">Detail Parameter</p>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="w-full text-sm text-slate-700">
                                        <thead class="bg-white text-xs uppercase text-slate-900">
                                            <tr>
                                                <th class="px-4 py-3 text-left">Parameter</th>
                                                <th class="px-4 py-3 text-left">Kolom Sensor</th>
                                                <th class="px-4 py-3 text-left">Satuan</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100">
                                            <template x-if="!(detailData.params || []).length">
                                                <tr>
                                                    <td colspan="3"
                                                        class="px-4 py-4 text-center text-sm text-slate-400">Tidak ada
                                                        parameter</td>
                                                </tr>
                                            </template>
                                            <template x-for="(param, index) in (detailData.params || [])"
                                                :key="param.id_param || index">
                                                <tr>
                                                    <td class="px-4 py-3 font-semibold text-slate-900"
                                                        x-text="param.nama_parameter || '-'"></td>
                                                    <td class="px-4 py-3" x-text="param.kolom_sensor || '-'"></td>
                                                    <td class="px-4 py-3" x-text="param.satuan || '-'"></td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- SETUP DEVICE MODAL --}}
        <div x-show="isAddOpen" x-transition class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title"
            role="dialog" aria-modal="true" style="display: none;">

            <div class="flex min-h-screen items-end justify-center px-4 pb-20 pt-4 text-center sm:block sm:p-0">

                <!-- Overlay -->
                <div x-show="isAddOpen" x-transition.opacity
                    class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeAddModal()"></div>

                <span class="hidden sm:inline-block sm:h-screen sm:align-middle">&#8203;</span>

                <!-- Modal Panel -->
                <div x-show="isAddOpen" x-transition
                    class="relative inline-block w-full max-w-5xl transform overflow-hidden rounded-xl bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:align-middle">

                    <!-- HEADER -->
                    <div class="flex items-center justify-between border-b px-6 py-4">
                        <h3 class="text-lg font-bold text-gray-900">Tambah Device</h3>
                        <button @click="closeAddModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form action="{{ route('device.store') }}" method="POST">
                        @csrf

                        <div class="px-6 py-5 space-y-6 max-h-[75vh] overflow-y-auto">

                            <!-- Nama Pos & lokasi -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700">Nama Pos</label>
                                    <input type="text" name="nama_lokasi" required
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm p-2 border text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700">Alamat</label>
                                    <input type="text" name="alamat" required
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm p-2 border text-sm">
                                </div>
                                {{-- <div>
                                    <label class="block text-xs font-medium text-gray-700">Pilih Instansi</label>
                                    <select name="pilih_instansi" required
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm p-2 border text-sm">
                                        <option value="">-- Pilih Instansi --</option>
                                        <template x-for="instansi in instansis" :key="instansi.id">
                                            <option :value="instansi.id" x-text="instansi.nama"></option>
                                        </template>
                                    </select>
                                </div> --}}
                            </div>

                            <!-- Latitude & Longitude -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700">Latitude</label>
                                    <input type="text" name="latitude" x-model="addData.latitude" required
                                        @input="updateAddMapFromInputs()"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm p-2 border text-sm"
                                        placeholder="Klik peta atau ketik manual">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700">Longitude</label>
                                    <input type="text" name="longitude" x-model="addData.longitude" required
                                        @input="updateAddMapFromInputs()"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm p-2 border text-sm"
                                        placeholder="Klik peta atau ketik manual">
                                </div>
                            </div>

                            <!-- Peta Lokasi di Peta -->
                            <div class="bg-slate-50 p-4 rounded-lg border border-slate-200">
                                <h4 class="text-sm font-semibold text-gray-900 mb-3">Pilih Lokasi di Peta</h4>
                                <div id="addDeviceMap" class="h-80 rounded-lg border border-gray-300"></div>
                                <p class="text-xs text-gray-500 mt-2">Klik peta untuk memilih koordinat, atau masukkan
                                    koordinat secara manual.</p>
                            </div>

                            <!-- Sub Kategori -->
                            <div class="bg-slate-50 p-4 rounded-lg border border-slate-200">
                                <h4 class="text-sm font-semibold text-gray-900 mb-3">Sub Kategori</h4>
                                <div class="flex gap-6">
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="sub_kategori" value="jiat"
                                            x-model="addData.subKategori"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <span class="ml-2 text-sm">JIAT</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="sub_kategori" value="non_jiat"
                                            x-model="addData.subKategori"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <span class="ml-2 text-sm">Non JIAT</span>
                                    </label>
                                </div>

                                <!-- JIAT Fields -->
                                <template x-if="addData.subKategori === 'jiat'">
                                    <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-2">Kedalaman
                                                Sumur</label>
                                            <div class="flex rounded-lg border border-gray-300 overflow-hidden bg-white">
                                                <input type="number" step="0.01" name="kedalaman_sumur"
                                                    x-model="addData.kedalaman_sumur"
                                                    class="w-full border-0 px-3 py-2 text-sm text-gray-800 focus:ring-0"
                                                    placeholder="100">
                                                <span
                                                    class="flex items-center border-l border-gray-300 px-3 text-sm text-gray-700 bg-gray-50">m</span>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-2">Kedalaman
                                                Sensor</label>
                                            <div class="flex rounded-lg border border-gray-300 overflow-hidden bg-white">
                                                <input type="number" step="0.01" name="kedalaman_sensor"
                                                    x-model="addData.kedalaman_sensor"
                                                    class="w-full border-0 px-3 py-2 text-sm text-gray-800 focus:ring-0"
                                                    placeholder="55">
                                                <span
                                                    class="flex items-center border-l border-gray-300 px-3 text-sm text-gray-700 bg-gray-50">m</span>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-2">Kedalaman
                                                Pompa</label>
                                            <div class="flex rounded-lg border border-gray-300 overflow-hidden bg-white">
                                                <input type="number" step="0.01" name="kedalaman_pompa"
                                                    x-model="addData.kedalaman_pompa"
                                                    class="w-full border-0 px-3 py-2 text-sm text-gray-800 focus:ring-0"
                                                    placeholder="60">
                                                <span
                                                    class="flex items-center border-l border-gray-300 px-3 text-sm text-gray-700 bg-gray-50">m</span>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <!-- Non JIAT Fields -->
                                <template x-if="addData.subKategori === 'non_jiat'">
                                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-2">Jarak Sensor dengan
                                                Air</label>
                                            <div class="flex rounded-lg border border-gray-300 overflow-hidden bg-white">
                                                <input type="number" step="0.01" name="kedalaman_sensor"
                                                    x-model="addData.kedalaman_sensor"
                                                    class="w-full border-0 px-3 py-2 text-sm text-gray-800 focus:ring-0"
                                                    placeholder="100">
                                                <span
                                                    class="flex items-center border-l border-gray-300 px-3 text-sm text-gray-700 bg-gray-50">m</span>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-2">Ketinggian
                                                Sensor</label>
                                            <div class="flex rounded-lg border border-gray-300 overflow-hidden bg-white">
                                                <input type="number" step="0.01" name="kedalaman_pompa"
                                                    x-model="addData.kedalaman_pompa"
                                                    class="w-full border-0 px-3 py-2 text-sm text-gray-800 focus:ring-0"
                                                    placeholder="60">
                                                <span
                                                    class="flex items-center border-l border-gray-300 px-3 text-sm text-gray-700 bg-gray-50">m</span>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <!-- Nama Logger -->
                            <div class="bg-slate-50 p-4 rounded-lg border border-slate-200">
                                <h4 class="text-sm font-semibold text-gray-900 mb-3">Nama Logger</h4>
                                <select name="nama_logger" @change="onLoggerChange($event)" required
                                    class="w-full rounded-md border-gray-300 shadow-sm p-2 border text-sm">
                                    <option value="">-- Pilih Nama Logger --</option>
                                    <template x-for="logger in loggers" :key="logger.id_logger">
                                        <option :value="logger.id_logger"
                                            x-text="`${logger.nama_logger} (${logger.sensor_count} sensor)`"></option>
                                    </template>
                                </select>
                                <p class="text-xs text-gray-500 mt-2" x-show="addData.selectedLogger">
                                    Kolom Sensor: <span class="font-semibold" x-text="addData.sensorCount"></span>
                                </p>
                            </div>

                            <!-- Daftar Parameter -->
                            <div class="rounded-xl border border-gray-300">
                                <div class="border-b border-gray-300 px-4 py-3">
                                    <h4 class="text-sm font-semibold text-gray-900">Daftar Parameter</h4>
                                </div>
                                <div class="px-4 py-3">
                                    <div
                                        class="grid grid-cols-[1.6fr_1fr_0.9fr_52px] gap-3 px-1 pb-2 text-sm font-semibold text-gray-900">
                                        <span>Nama Parameter</span>
                                        <span>Kolom Sensor</span>
                                        <span>Satuan</span>
                                        <span class="text-center">Aksi</span>
                                    </div>

                                    <div class="space-y-2">
                                        <template x-for="(param, index) in addData.params" :key="index">
                                            <div class="grid grid-cols-[1.6fr_1fr_0.9fr_52px] gap-3 items-start">
                                                <input :name="'params[' + index + '][nama_parameter]'"
                                                    x-model="param.nama_parameter" type="text" required
                                                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-800 focus:border-indigo-500 focus:ring-indigo-500">

                                                <select :name="'params[' + index + '][kolom_sensor]'"
                                                    x-model="param.kolom_sensor" required
                                                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-800 focus:border-indigo-500 focus:ring-indigo-500">
                                                    <option value="">Pilih Sensor</option>
                                                    <template x-for="sensor in addSensorOptions" :key="sensor">
                                                        <option :value="sensor" x-text="sensor"></option>
                                                    </template>
                                                </select>

                                                <input :name="'params[' + index + '][satuan]'" x-model="param.satuan"
                                                    type="text" required
                                                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-800 focus:border-indigo-500 focus:ring-indigo-500">

                                                <button type="button" @click="removeParameter(index)"
                                                    :disabled="addData.params.length === 1"
                                                    class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-red-500 text-white hover:bg-red-600 disabled:cursor-not-allowed disabled:opacity-50"
                                                    title="Hapus parameter">
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </template>
                                    </div>

                                    <button type="button" @click="addParameter()"
                                        class="mt-3 text-sm font-semibold text-indigo-800 hover:text-indigo-900">
                                        + Tambah Parameter
                                    </button>
                                </div>
                            </div>

                        </div>

                        <!-- FOOTER -->
                        <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3">
                            <button type="button" @click="closeAddModal()"
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

        {{-- EDIT MODAL --}}
        <div x-show="isOpen" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title"
            role="dialog" aria-modal="true" style="display: none;">

            <div class="flex min-h-screen items-end justify-center px-4 pb-20 pt-4 text-center sm:block sm:p-0">

                <!-- Overlay -->
                <div x-show="isOpen" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeModal()"></div>

                <span class="hidden sm:inline-block sm:h-screen sm:align-middle">&#8203;</span>

                <!-- Modal Panel -->
                <div x-show="isOpen" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="relative inline-block w-full max-w-6xl transform overflow-hidden rounded-xl bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:align-middle">

                    <!-- HEADER -->
                    <div class="flex items-center justify-between border-b px-6 py-4">
                        <h3 class="text-xl font-bold text-gray-900">Edit Device</h3>
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

                        <div class="px-6 py-5 space-y-5 max-h-[75vh] overflow-y-auto">
                            <input type="hidden" name="latitude" x-model="editData.latitude">
                            <input type="hidden" name="longitude" x-model="editData.longitude">

                            <div>
                                <label class="block text-sm font-semibold text-gray-900 mb-2">Nama Pos</label>
                                <input type="hidden" name="nama_lokasi" :value="editData.nama_lokasi">
                                <div
                                    class="w-full rounded-xl border border-gray-300 bg-gray-100 px-4 py-3 text-sm text-gray-800">
                                    <span x-text="editData.nama_lokasi || '-'"></span>
                                </div>
                            </div>

                            <div>
                                <h4 class="text-sm font-semibold text-gray-900 mb-2">Sub Kategori</h4>
                                <div class="flex items-center gap-8">
                                    <label class="inline-flex items-center gap-2 text-sm text-gray-800">
                                        <input type="radio" value="jiat" x-model="editData.subKategori"
                                            class="border-gray-400 text-indigo-700 focus:ring-indigo-500">
                                        JIAT
                                    </label>
                                    <label class="inline-flex items-center gap-2 text-sm text-gray-800">
                                        <input type="radio" value="non_jiat" x-model="editData.subKategori"
                                            class="border-gray-400 text-indigo-700 focus:ring-indigo-500">
                                        Non JIAT
                                    </label>
                                </div>
                            </div>

                            <!-- JIAT Fields -->
                            <template x-if="editData.subKategori === 'jiat'">
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-900 mb-2">Kedalaman
                                            Sumur</label>
                                        <div class="flex rounded-xl border border-gray-300 overflow-hidden">
                                            <input type="number" step="0.01" name="kedalaman_sumur"
                                                x-model="editData.kedalaman_sumur"
                                                class="w-full border-0 px-4 py-3 text-sm text-gray-800 focus:ring-0">
                                            <span
                                                class="flex items-center border-l border-gray-300 px-4 text-sm text-gray-700">m</span>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-900 mb-2">Kedalaman
                                            Sensor</label>
                                        <div class="flex rounded-xl border border-gray-300 overflow-hidden">
                                            <input type="number" step="0.01" name="kedalaman_sensor"
                                                x-model="editData.kedalaman_sensor"
                                                class="w-full border-0 px-4 py-3 text-sm text-gray-800 focus:ring-0">
                                            <span
                                                class="flex items-center border-l border-gray-300 px-4 text-sm text-gray-700">m</span>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-900 mb-2">Kedalaman
                                            Pompa</label>
                                        <div class="flex rounded-xl border border-gray-300 overflow-hidden">
                                            <input type="number" step="0.01" name="kedalaman_pompa"
                                                x-model="editData.kedalaman_pompa"
                                                class="w-full border-0 px-4 py-3 text-sm text-gray-800 focus:ring-0">
                                            <span
                                                class="flex items-center border-l border-gray-300 px-4 text-sm text-gray-700">m</span>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <!-- Non JIAT Fields -->
                            <template x-if="editData.subKategori === 'non_jiat'">
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-900 mb-2">Jarak Sensor dengan
                                            Air</label>
                                        <div class="flex rounded-xl border border-gray-300 overflow-hidden">
                                            <input type="number" step="0.01" name="kedalaman_sensor"
                                                x-model="editData.kedalaman_sensor"
                                                class="w-full border-0 px-4 py-3 text-sm text-gray-800 focus:ring-0">
                                            <span
                                                class="flex items-center border-l border-gray-300 px-4 text-sm text-gray-700">m</span>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-900 mb-2">Ketinggian
                                            Sensor</label>
                                        <div class="flex rounded-xl border border-gray-300 overflow-hidden">
                                            <input type="number" step="0.01" name="kedalaman_pompa"
                                                x-model="editData.kedalaman_pompa"
                                                class="w-full border-0 px-4 py-3 text-sm text-gray-800 focus:ring-0">
                                            <span
                                                class="flex items-center border-l border-gray-300 px-4 text-sm text-gray-700">m</span>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <div>
                                <label class="block text-sm font-semibold text-gray-900 mb-2">Nama Logger</label>
                                <div class="relative">
                                    <select x-model="editData.id_logger"
                                        class="w-full appearance-none rounded-xl border border-gray-300 px-4 py-3 pr-10 text-sm text-gray-800 focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="" disabled>Pilih nama logger</option>
                                        <option :value="editData.id_logger" x-text="editData.nama_logger"
                                            x-show="editData.nama_logger"></option>
                                        <template x-for="logger in loggers" :key="logger.id_logger">
                                            <option :value="logger.id_logger" x-text="logger.nama_logger"></option>
                                        </template>
                                    </select>
                                    <svg class="pointer-events-none absolute right-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-500"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>

                            <div class="rounded-xl border border-gray-300">
                                <div class="border-b border-gray-300 px-4 py-3">
                                    <h4 class="text-xl font-semibold text-gray-900">Daftar Parameter</h4>
                                </div>
                                <div class="px-4 py-3">
                                    <div
                                        class="grid grid-cols-[1.6fr_1fr_0.9fr_52px] gap-3 px-1 pb-2 text-sm font-semibold text-gray-900">
                                        <span>Nama Parameter</span>
                                        <span>Kolom Sensor</span>
                                        <span>Satuan</span>
                                        <span class="text-center">Aksi</span>
                                    </div>

                                    <div class="space-y-2">
                                        <template x-for="(param, index) in editData.params"
                                            :key="param.id_param || 'param_' + index">
                                            <div class="grid grid-cols-[1.6fr_1fr_0.9fr_52px] gap-3 items-start">
                                                <input type="hidden" :name="'params[' + index + '][id_param]'"
                                                    x-model="param.id_param">
                                                <input :name="'params[' + index + '][nama_parameter]'"
                                                    x-model="param.nama_parameter"
                                                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-800 focus:border-indigo-500 focus:ring-indigo-500">

                                                <select :name="'params[' + index + '][kolom_sensor]'"
                                                    x-model="param.kolom_sensor"
                                                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-800 focus:border-indigo-500 focus:ring-indigo-500">
                                                    <option value="">Pilih Sensor</option>
                                                    <template x-for="sensor in editSensorOptions" :key="sensor">
                                                        <option :value="sensor" x-text="sensor"
                                                            :selected="sensor === param.kolom_sensor"></option>
                                                    </template>
                                                </select>

                                                <input :name="'params[' + index + '][satuan]'" x-model="param.satuan"
                                                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-800 focus:border-indigo-500 focus:ring-indigo-500">

                                                <button type="button" @click="removeEditParameter(index)"
                                                    :disabled="editData.params.length === 1"
                                                    class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-red-500 text-white hover:bg-red-600 disabled:cursor-not-allowed disabled:opacity-50"
                                                    title="Hapus parameter">
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </template>
                                    </div>

                                    <button type="button" @click="addEditParameter()"
                                        class="mt-3 text-sm font-semibold text-indigo-800 hover:text-indigo-900">
                                        + Tambah Parameter
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- FOOTER -->
                        <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3">
                            <button type="button" @click="closeModal()"
                                class="rounded-xl border border-indigo-300 bg-white px-6 py-2.5 text-sm font-semibold text-indigo-900 hover:bg-indigo-50">
                                Batal
                            </button>
                            <button type="submit"
                                class="rounded-xl bg-indigo-800 px-6 py-2.5 text-sm font-semibold text-white hover:bg-indigo-900">
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
                searchQuery: '',
                allDevices: @json($devices),
                showDetailModal: false,
                isOpen: false,
                isAddOpen: false,
                ready: false,
                sensorOptions: [],
                editSensorOptions: [],
                addSensorOptions: [],
                instansis: [],
                loggers: [],
                addDeviceMap: null,
                addDeviceMarker: null,
                detailData: {
                    id_logger: '',
                    nama_lokasi: '',
                    nama_logger: '',
                    alamat: '',
                    latitude: '',
                    longitude: '',
                    sub_kategori: '-',
                    kedalaman_sumur: '-',
                    kedalaman_sensor: '-',
                    kedalaman_pompa: '-',
                    params: []
                },
                editData: {
                    updateUrl: '',
                    id_logger: '',
                    nama_lokasi: '',
                    nama_logger: '',
                    subKategori: 'jiat',
                    latitude: '',
                    longitude: '',
                    kedalaman_sumur: '',
                    kedalaman_sensor: '',
                    kedalaman_pompa: '',
                    params: []
                },
                addData: {
                    latitude: '',
                    longitude: '',
                    subKategori: '',
                    selectedLogger: null,
                    sensorCount: 16,
                    params: [{
                        nama_parameter: '',
                        kolom_sensor: '',
                        satuan: ''
                    }]
                },
                formdata: {
                    id_logger: '',
                    nama_lokasi: '',
                    latitude: '',
                    longitude: '',
                    params: []
                },

                filteredDevices() {
                    const devices = Array.isArray(this.allDevices) ? this.allDevices : [];
                    const normalize = (value) => String(value ?? '').toLowerCase();
                    const query = normalize(this.searchQuery).trim();

                    if (!query) {
                        return devices;
                    }

                    return devices.filter(device => {
                        return (
                            normalize(device.id_logger).includes(query) ||
                            normalize(device.nama_lokasi).includes(query) ||
                            normalize(device.alamat).includes(query)
                        );
                    });
                },

                paramColorClass(name) {
                    const value = (name || '').toLowerCase();

                    if (value.includes('humidity')) return 'bg-sky-200 text-sky-700';
                    if (value.includes('muka')) return 'bg-sky-100 text-sky-400';
                    if (value.includes('temp') || value.includes('suhu'))
                        return 'bg-orange-100 text-orange-700';
                    if (value.includes('bat') || value.includes('volt'))
                        return 'bg-emerald-100 text-emerald-700';
                    if (value.includes('kedalaman')) return 'bg-amber-100 text-amber-700';
                    if (value.includes('tma')) return 'bg-purple-100 text-purple-700';
                    if (value.includes('curah')) return 'bg-green-100 text-green-700';

                    return 'bg-slate-100 text-slate-700';
                },

                init() {
                    this.sensorOptions = Array.from({
                        length: 19
                    }, (_, i) => 'sensor' + (i + 1))

                    this.addSensorOptions = Array.from({
                        length: 16
                    }, (_, i) => 'sensor' + (i + 1))
                },

                async openAddModal() {
                    // Fetch dropdown data
                    try {
                        const response = await fetch('{{ route('device.create') }}')
                        const data = await response.json()
                        this.instansis = data.instansis
                        this.loggers = data.loggers
                    } catch (error) {
                        console.error('Error fetching data:', error)
                    }

                    this.isAddOpen = true

                    // Initialize map after modal is shown
                    this.$nextTick(() => {
                        this.initAddDeviceMap()
                    })
                },

                closeAddModal() {
                    this.isAddOpen = false
                    // Reset form
                    this.addData = {
                        latitude: '',
                        longitude: '',
                        subKategori: '',
                        selectedLogger: null,
                        sensorCount: 16,
                        params: [{
                            nama_parameter: '',
                            kolom_sensor: '',
                            satuan: ''
                        }]
                    }

                    // Destroy map
                    if (this.addDeviceMap) {
                        this.addDeviceMap.remove()
                        this.addDeviceMap = null
                        this.addDeviceMarker = null
                    }
                },

                initAddDeviceMap() {
                    this.$nextTick(() => {
                        if (!document.getElementById('addDeviceMap')) return

                        // Clean up existing map if any
                        if (this.addDeviceMap) {
                            this.addDeviceMap.remove()
                        }

                        // Default center: Jakarta
                        const defaultLat = this.addData.latitude || -6.200000
                        const defaultLng = this.addData.longitude || 106.816666
                        const defaultZoom = 13

                        this.addDeviceMap = L.map('addDeviceMap').setView([defaultLat,
                            defaultLng
                        ], defaultZoom)

                        L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                            maxZoom: 20,
                            subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
                        }).addTo(this.addDeviceMap)

                        // Create draggable marker if coordinates exist
                        if (this.addData.latitude && this.addData.longitude) {
                            this.addDeviceMarker = L.marker([defaultLat, defaultLng], {
                                draggable: true
                            }).addTo(this.addDeviceMap)
                        } else {
                            // Create marker at default position
                            this.addDeviceMarker = L.marker([defaultLat, defaultLng], {
                                draggable: true
                            }).addTo(this.addDeviceMap)
                            // Set initial coordinates
                            this.addData.latitude = defaultLat.toFixed(6)
                            this.addData.longitude = defaultLng.toFixed(6)
                        }

                        // Map click event - update coordinates and move marker
                        this.addDeviceMap.on('click', (e) => {
                            this.addData.latitude = e.latlng.lat.toFixed(6)
                            this.addData.longitude = e.latlng.lng.toFixed(6)
                            this.addDeviceMarker.setLatLng(e.latlng)
                        })

                        // Marker drag event - update coordinates as marker is dragged
                        this.addDeviceMarker.on('dragend', (e) => {
                            const position = e.target.getLatLng()
                            this.addData.latitude = position.lat.toFixed(6)
                            this.addData.longitude = position.lng.toFixed(6)
                        })
                    })
                },

                // Update map when coordinates are manually entered
                updateAddMapFromInputs() {
                    if (!this.addDeviceMap || !this.addDeviceMarker) return

                    const lat = parseFloat(this.addData.latitude)
                    const lng = parseFloat(this.addData.longitude)

                    if (!isNaN(lat) && !isNaN(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <=
                        180) {
                        this.addDeviceMarker.setLatLng([lat, lng])
                        this.addDeviceMap.setView([lat, lng], this.addDeviceMap.getZoom())
                    }
                },

                onLoggerChange(event) {
                    const loggerId = event.target.value
                    const logger = this.loggers.find(l => l.id_logger === loggerId)

                    if (logger) {
                        this.addData.selectedLogger = logger
                        this.addData.sensorCount = logger.sensor_count || 16

                        // Update sensor options based on sensor count
                        this.addSensorOptions = Array.from({
                            length: logger.sensor_count
                        }, (_, i) => 'sensor' + (i + 1))

                        // Reset parameters sensor selection
                        this.addData.params.forEach(param => {
                            param.kolom_sensor = ''
                        })
                    }
                },

                addParameter() {
                    this.addData.params.push({
                        nama_parameter: '',
                        kolom_sensor: '',
                        satuan: ''
                    })
                },

                removeParameter(index) {
                    if (this.addData.params.length > 1) {
                        this.addData.params.splice(index, 1)
                    }
                },

                addEditParameter() {
                    this.editData.params.push({
                        id_param: '',
                        nama_parameter: '',
                        kolom_sensor: '',
                        satuan: ''
                    })
                },

                removeEditParameter(index) {
                    if (this.editData.params.length > 1) {
                        this.editData.params.splice(index, 1)
                    }
                },

                openDetailModal(device) {
                    const jiat = device?.jiat ?? null
                    const hasJiat = !!jiat
                    const hasSumur = hasJiat && jiat.kedalaman_sumur !== null && jiat
                        .kedalaman_sumur !== ''

                    this.detailData = {
                        id_logger: device?.id_logger ?? '-',
                        nama_lokasi: device?.nama_lokasi ?? '-',
                        nama_logger: device?.nama_logger ?? '-',
                        alamat: device?.alamat ?? '-',
                        latitude: device?.lokasi?.latitude ?? '-',
                        longitude: device?.lokasi?.longitude ?? '-',
                        sub_kategori: hasJiat ? (hasSumur ? 'JIAT' : 'NON JIAT') : '-',
                        kedalaman_sumur: hasJiat && jiat.kedalaman_sumur !== null ?
                            `${jiat.kedalaman_sumur} m` : '-',
                        kedalaman_sensor: hasJiat && jiat.kedalaman_sensor !== null ?
                            `${jiat.kedalaman_sensor} m` : '-',
                        kedalaman_pompa: hasJiat && jiat.kedalaman_pompa !== null ?
                            `${jiat.kedalaman_pompa} m` : '-',
                        params: Array.isArray(device?.params) ? device.params : []
                    }

                    this.showDetailModal = true
                },

                closeDetailModal() {
                    this.showDetailModal = false
                },

                openModal(device) {
                    if (!this.loggers.length) {
                        fetch('{{ route('device.create') }}')
                            .then(response => response.json())
                            .then(data => {
                                this.loggers = data.loggers ?? []
                            })
                            .catch(error => {
                                console.error('Error fetching logger data:', error)
                            })
                    }

                    Object.assign(this.editData, {
                        updateUrl: `/pengaturan-device/${device.id_logger}`,
                        id_logger: device.id_logger,
                        nama_lokasi: device.lokasi?.nama_lokasi ?? '',
                        nama_logger: device.nama_logger ?? '',
                        subKategori: (device.jiat?.kedalaman_sumur ?? '') === '' ? 'non_jiat' :
                            'jiat',
                        latitude: device.lokasi?.latitude ?? '',
                        longitude: device.lokasi?.longitude ?? '',
                        kedalaman_sumur: device.jiat?.kedalaman_sumur ?? '',
                        kedalaman_sensor: device.jiat?.kedalaman_sensor ?? '',
                        kedalaman_pompa: device.jiat?.kedalaman_pompa ?? ''
                    })

                    // Kosongkan dulu biar Alpine reset DOM
                    this.editData.params = []

                    // Generate sensor options based on sensor_count BEFORE opening modal
                    const sensorCount = parseInt(device.sensor_count ?? 16, 10)
                    const validSensorCount = (Number.isNaN(sensorCount) || sensorCount <= 0) ? 16 :
                        sensorCount

                    // Always generate fresh sensor options based on actual device sensor count
                    const sensorsFromCount = Array.from({
                        length: validSensorCount
                    }, (_, i) => 'sensor' + (i + 1))

                    // Get sensors from existing params to preserve any custom values
                    const sensorsFromParams = (device.params ?? [])
                        .map(p => String(p.kolom_sensor || '').trim())
                        .filter(Boolean)

                    // Merge and sort sensor options
                    const mergedSensors = [...new Set([...sensorsFromCount, ...sensorsFromParams])]
                    this.editSensorOptions = mergedSensors.sort((a, b) => {
                        const aNum = parseInt(a.replace('sensor', ''), 10)
                        const bNum = parseInt(b.replace('sensor', ''), 10)
                        if (Number.isNaN(aNum) || Number.isNaN(bNum)) return a.localeCompare(b)
                        return aNum - bNum
                    })

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

                        if (!this.editData.params.length) {
                            this.addEditParameter()
                        }

                    })
                },

                closeModal() {
                    this.isOpen = false
                }
            }))
        })
    </script>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endsection
