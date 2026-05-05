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

    <div x-data="deviceEditor()" x-cloak class="mt-2 space-y-3">

        <div class="flex items-center justify-end">
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


        <div class="overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-slate-200">
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
                            <th scope="col" class="px-6 py-4 text-center">Status</th>
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
                                                x-text="(param.nama_parameter || '-').replaceAll('_', ' ')"></span>
                                        </template>
                                    </div>
                                </td>
<td class="whitespace-nowrap px-6 py-4 text-center">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold"
                                        :class="device.status_perbaikan === 'perbaikan'
                                            ? 'bg-orange-100 text-orange-700'
                                            : 'bg-green-100 text-green-700'"
                                    >
                                        <span x-show="device.status_perbaikan !== 'perbaikan'">✓</span>
                                        <span x-show="device.status_perbaikan === 'perbaikan'">⚠</span>
                                        <span x-text="device.status_perbaikan === 'perbaikan' ? 'Perbaikan' : 'Normal'"></span>
                                    </span>
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
</div>
        </div>
<div x-show="showDetailModal" class="fixed inset-0 z-50" aria-labelledby="detail-modal-title"
            role="dialog" aria-modal="true" style="display: none;">
            <div x-show="showDetailModal" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/40 transition-opacity"
                aria-hidden="true" @click="closeDetailModal()"></div>

            <div class="fixed inset-0 flex items-center justify-center p-4 overflow-y-auto"
                @click="closeDetailModal()">
                <div x-show="showDetailModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                    class="relative w-full max-w-4xl overflow-hidden rounded-2xl bg-slate-100 text-left shadow-xl my-2 sm:my-4"
                    @click.stop>

                    <div class="flex items-start justify-between border-b border-slate-200 px-6 py-5">
                        <div class="flex items-start gap-3">
                            <div class="rounded-lg border border-slate-200 bg-white p-2 text-slate-700">
                                <img src="{{ asset('icons/detail_dark_icon.svg') }}" class="h-6 w-6">
                            </div>
                            <div>
                                <h3 id="detail-modal-title" class="text-xl font-bold text-slate-900">Detail Device</h3>
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

                    <div class="space-y-3 px-3 py-4 sm:space-y-4 sm:px-6 sm:py-5 max-h-[80vh] overflow-y-auto">
<div class="overflow-hidden rounded-lg border border-slate-200 bg-white">
                            <div class="flex items-center gap-2 border-b border-slate-200 px-4 py-3">
                                <img src="{{ asset('icons/identitas_icon.svg') }}" class="h-5 w-5">
                                <p class="text-base font-bold text-slate-900">Identitas Device</p>
                            </div>
                            <div class="grid grid-cols-1 gap-3 px-4 py-4 sm:grid-cols-3 sm:gap-6">
                                <div>
                                    <p class="text-xs uppercase tracking-wide text-slate-400">ID Logger</p>
                                    <p class="mt-1 text-sm font-bold text-slate-900" x-text="detailData.id_logger || '-'"></p>
                                </div>
                                <div>
                                    <p class="text-xs uppercase tracking-wide text-slate-400">Nama Pos</p>
                                    <p class="mt-1 text-sm font-bold text-slate-900" x-text="detailData.nama_lokasi || '-'"></p>
                                </div>
                                <div>
                                    <p class="text-xs uppercase tracking-wide text-slate-400">Nama Logger</p>
                                    <p class="mt-1 text-sm font-bold text-slate-900" x-text="detailData.nama_logger || '-'"></p>
                                </div>
                            </div>
                        </div>
<div class="overflow-hidden rounded-lg border border-slate-200 bg-white">
                            <div class="flex items-center gap-2 border-b border-slate-200 px-4 py-3">
                                <img src="{{ asset('icons/lokasi_icon.svg') }}" class="h-5 w-5">
                                <p class="text-base font-bold text-slate-900">Lokasi Pos</p>
                            </div>
                            <div class="grid grid-cols-1 gap-3 px-4 py-4 sm:grid-cols-3 sm:gap-6">
                                <div>
                                    <p class="text-xs uppercase tracking-wide text-slate-400">Alamat</p>
                                    <p class="mt-1 text-sm font-bold text-slate-900" x-text="detailData.alamat || '-'"></p>
                                </div>
                                <div>
                                    <p class="text-xs uppercase tracking-wide text-slate-400">Latitude</p>
                                    <p class="mt-1 text-sm font-bold text-slate-900" x-text="detailData.latitude || '-'"></p>
                                </div>
                                <div>
                                    <p class="text-xs uppercase tracking-wide text-slate-400">Longitude</p>
                                    <p class="mt-1 text-sm font-bold text-slate-900" x-text="detailData.longitude || '-'"></p>
                                </div>
                            </div>
                        </div>
<template x-if="isAwlrKategori(detailData.id_katlogger)">
                            <div class="overflow-hidden rounded-lg border border-slate-200 bg-white">
                                <div class="flex items-center gap-2 border-b border-slate-200 px-4 py-3">
                                    <img src="{{ asset('icons/sub_kategori_icon.svg') }}" class="h-5 w-5">
                                    <p class="text-base font-bold text-slate-900">Sub Kategori</p>
                                </div>
<template x-if="detailData.sub_kategori === 'JIAT'">
                                    <div class="grid grid-cols-2 gap-3 px-4 py-4 sm:grid-cols-5 sm:gap-6">
                                        <div>
                                            <p class="text-xs uppercase tracking-wide text-slate-400">Sub Kategori</p>
                                            <p class="mt-1 text-sm font-bold text-slate-900" x-text="detailData.sub_kategori"></p>
                                        </div>
                                        <div>
                                            <p class="text-xs uppercase tracking-wide text-slate-400">Kedalaman Sumur</p>
                                            <p class="mt-1 text-sm font-bold text-slate-900" x-text="detailData.kedalaman_sumur || '-'"></p>
                                        </div>
                                        <div>
                                            <p class="text-xs uppercase tracking-wide text-slate-400">Kedalaman Sensor</p>
                                            <p class="mt-1 text-sm font-bold text-slate-900" x-text="detailData.kedalaman_sensor || '-'"></p>
                                        </div>
                                        <div>
                                            <p class="text-xs uppercase tracking-wide text-slate-400">Kedalaman Pompa</p>
                                            <p class="mt-1 text-sm font-bold text-slate-900" x-text="detailData.kedalaman_pompa || '-'"></p>
                                        </div>
                                        <div>
                                            <p class="text-xs uppercase tracking-wide text-slate-400">Kontrol Pump</p>
                                            <p class="mt-1 text-sm font-bold text-slate-900" x-text="detailData.has_pump_label || '-'"></p>
                                        </div>
                                    </div>
                                </template>
<template x-if="detailData.sub_kategori === 'NON JIAT'">
                                    <div class="grid grid-cols-3 gap-3 px-4 py-4 sm:gap-6">
                                        <div>
                                            <p class="text-xs uppercase tracking-wide text-slate-400">Sub Kategori</p>
                                            <p class="mt-1 text-sm font-bold text-slate-900" x-text="detailData.sub_kategori"></p>
                                        </div>
                                        <div>
                                            <p class="text-xs uppercase tracking-wide text-slate-400">Jarak Sensor dengan Air</p>
                                            <p class="mt-1 text-sm font-bold text-slate-900"
                                                x-text="detailData.jarak_sensor_ke_air != null ? detailData.jarak_sensor_ke_air + ' m' : '-'"></p>
                                        </div>
                                        <div>
                                            <p class="text-xs uppercase tracking-wide text-slate-400">Tinggi Sensor</p>
                                            <p class="mt-1 text-sm font-bold text-slate-900"
                                                x-text="detailData.tinggi_sensor != null ? detailData.tinggi_sensor + ' m' : '-'"></p>
                                        </div>
                                    </div>
                                </template>
<template x-if="detailData.sub_kategori === '-'">
                                    <div class="px-4 py-4">
                                        <p class="text-sm text-slate-400">-</p>
                                    </div>
                                </template>
                            </div>
                        </template>
<div class="overflow-hidden rounded-lg border border-slate-200 bg-white">
                            <div class="flex items-center gap-2 border-b border-slate-200 px-4 py-3">
                                <img src="{{ asset('icons/param_icon.svg') }}" class="h-5 w-5">
                                <p class="text-base font-bold text-slate-900">Detail Parameter</p>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm text-slate-700">
                                    <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                                        <tr>
                                            <th class="px-3 py-3 text-center w-8">#</th>
                                            <th class="px-4 py-3 text-left">Parameter</th>
                                            <th class="px-4 py-3 text-left">Kolom Sensor</th>
                                            <th class="px-4 py-3 text-left">Satuan</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <template x-if="!(detailData.params || []).length">
                                            <tr>
                                                <td colspan="4" class="px-4 py-4 text-center text-sm text-slate-400">Tidak ada parameter</td>
                                            </tr>
                                        </template>
                                        <template x-for="(param, index) in (detailData.params || [])" :key="param.id_param || index">
                                            <tr>
                                                <td class="px-3 py-3 text-center text-xs font-bold text-slate-400" x-text="index + 1"></td>
                                                <td class="px-4 py-3 font-semibold text-slate-900" x-text="(param.nama_parameter || '-').replaceAll('_', ' ')"></td>
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
<div x-show="isAddOpen" x-transition class="fixed inset-0 z-50" aria-labelledby="modal-title"
            role="dialog" aria-modal="true" style="display: none;">

            <div class="fixed inset-0 flex items-center justify-center p-4 overflow-y-auto" @click="closeAddModal()">

                <!-- Overlay -->
                <div x-show="isAddOpen" x-transition.opacity
                    class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeAddModal()"></div>

                <!-- Modal Panel -->
                <div x-show="isAddOpen" x-transition
                    class="relative w-full max-w-5xl overflow-hidden rounded-lg bg-white text-left shadow-xl" @click.stop>

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

                            <template x-if="isAddAwlr()">
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

                                    <template x-if="addData.subKategori === 'jiat'">
                                        <div>
                                            <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700 mb-2">Kedalaman
                                                        Sumur</label>
                                                    <div
                                                        class="flex rounded-lg border border-gray-300 overflow-hidden bg-white">
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
                                                    <div
                                                        class="flex rounded-lg border border-gray-300 overflow-hidden bg-white">
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
                                                    <div
                                                        class="flex rounded-lg border border-gray-300 overflow-hidden bg-white">
                                                        <input type="number" step="0.01" name="kedalaman_pompa"
                                                            x-model="addData.kedalaman_pompa"
                                                            class="w-full border-0 px-3 py-2 text-sm text-gray-800 focus:ring-0"
                                                            placeholder="60">
                                                        <span
                                                            class="flex items-center border-l border-gray-300 px-3 text-sm text-gray-700 bg-gray-50">m</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <input type="hidden" name="has_pump" :value="addData.subKategori === 'jiat' && addData.has_pump ? 1 : 0">
                                            <div class="mt-4 rounded-xl border border-slate-200 bg-white shadow-sm ring-1 ring-slate-100 p-4 transition-all duration-300 hover:shadow-md"
                                                :class="addData.has_pump ? 'border-emerald-200 bg-emerald-50/50' : '' ">
                                                <label class="flex items-center justify-between gap-4 cursor-pointer">
                                                    <div class="flex flex-col">
                                                        <span class="text-sm font-bold text-slate-800 flex items-center gap-2">
                                                            Kontrol Pump
                                                            <span class="hidden sm:inline-flex text-[10px] font-semibold tracking-wide uppercase px-2 py-0.5 rounded-full transition-colors duration-300"
                                                                :class="addData.has_pump ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500'"
                                                                x-text="addData.has_pump ? 'Aktif' : 'Nonaktif'">
                                                            </span>
                                                        </span>
                                                        <span class="mt-1 text-xs text-slate-500 leading-relaxed max-w-sm">
                                                            Aktifkan opsi ini untuk memunculkan aksi kontrol pump pada tabel device.
                                                        </span>
                                                    </div>

                                                    <button type="button"
                                                        @click="addData.has_pump = !addData.has_pump"
                                                        :aria-pressed="addData.has_pump"
                                                        class="relative inline-flex h-7 w-14 shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent transition-colors duration-300 ease-in-out focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 hover:brightness-110"
                                                        :class="addData.has_pump ? 'bg-emerald-500' : 'bg-slate-300'"
                                                        role="switch">
                                                        <span class="sr-only">Aktifkan Kontrol Pump</span>
                                                        <span aria-hidden="true"
                                                            class="pointer-events-none inline-block h-6 w-6 transform rounded-full bg-white shadow-md ring-0 transition-transform duration-300 ease-out"
                                                            :class="addData.has_pump ? 'translate-x-7' : 'translate-x-0'">
                                                        </span>
                                                    </button>
                                                </label>
                                            </div>
                                        </div>
                                    </template>

                                    <template x-if="addData.subKategori === 'non_jiat'">
                                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 mb-2">Jarak Sensor
                                                    dengan Air</label>
                                                <div
                                                    class="flex rounded-lg border border-gray-300 overflow-hidden bg-white">
                                                    <input type="number" step="0.01" name="jarak_sensor_ke_air"
                                                        x-model="addData.jarak_sensor_ke_air"
                                                        class="w-full border-0 px-3 py-2 text-sm text-gray-800 focus:ring-0"
                                                        placeholder="100">
                                                    <span
                                                        class="flex items-center border-l border-gray-300 px-3 text-sm text-gray-700 bg-gray-50">m</span>
                                                </div>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 mb-2">Ketinggian
                                                    Sensor</label>
                                                <div
                                                    class="flex rounded-lg border border-gray-300 overflow-hidden bg-white">
                                                    <input type="number" step="0.01" name="tinggi_sensor"
                                                        x-model="addData.tinggi_sensor"
                                                        class="w-full border-0 px-3 py-2 text-sm text-gray-800 focus:ring-0"
                                                        placeholder="60">
                                                    <span
                                                        class="flex items-center border-l border-gray-300 px-3 text-sm text-gray-700 bg-gray-50">m</span>
                                                </div>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 mb-2">Elevasi Maks
                                                    (Batas Atas Peil)</label>
                                                <div
                                                    class="flex rounded-lg border border-gray-300 overflow-hidden bg-white">
                                                    <input type="number" step="any" name="elevasi_max"
                                                        x-model="addData.elevasi_max"
                                                        class="w-full border-0 px-3 py-2 text-sm text-gray-800 focus:ring-0"
                                                        placeholder="cth: 5.5">
                                                    <span
                                                        class="flex items-center border-l border-gray-300 px-3 text-sm text-gray-700 bg-gray-50">m</span>
                                                </div>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 mb-2">Elevasi Min
                                                    (Batas Bawah Peil)</label>
                                                <div
                                                    class="flex rounded-lg border border-gray-300 overflow-hidden bg-white">
                                                    <input type="number" step="any" name="elevasi_min"
                                                        x-model="addData.elevasi_min"
                                                        class="w-full border-0 px-3 py-2 text-sm text-gray-800 focus:ring-0"
                                                        placeholder="cth: 0">
                                                    <span
                                                        class="flex items-center border-l border-gray-300 px-3 text-sm text-gray-700 bg-gray-50">m</span>
                                                </div>
                                            </div>
                                        </div>
                                    </template>

                                </div>
                            </template>

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
                            <div class="rounded-lg border border-gray-300">
                                <div class="border-b border-gray-300 px-4 py-3 flex items-center justify-between gap-3">
                                    <h4 class="text-sm font-semibold text-gray-900">Daftar Parameter</h4>
                                    <button type="button" @click="applyTemplateToAdd()"
                                        :disabled="!canApplyTemplateAdd()"
                                        class="inline-flex items-center rounded-md border border-indigo-200 bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-700 hover:bg-indigo-100 disabled:cursor-not-allowed disabled:opacity-50">
                                        Template Kategori (Opsional)
                                    </button>
                                </div>
                                <div class="px-4 py-3">
<div class="hidden sm:grid grid-cols-[24px_1.6fr_1fr_0.9fr_52px] gap-3 px-1 pb-2 text-sm font-semibold text-gray-900">
                                        <span class="text-center text-gray-400">#</span>
                                        <span>Nama Parameter</span>
                                        <span>Kolom Sensor</span>
                                        <span>Satuan</span>
                                        <span class="text-center">Aksi</span>
                                    </div>

                                    <div class="space-y-2">
                                        <template x-for="(param, index) in addData.params" :key="index">
<div class="rounded-lg border border-gray-100 bg-slate-50 p-3
                                                        sm:rounded-none sm:border-0 sm:bg-transparent sm:p-0
                                                        sm:grid sm:grid-cols-[24px_1.6fr_1fr_0.9fr_52px] sm:gap-3 sm:items-start">
                                                <input type="hidden" :name="'params[' + index + '][parameter_group_id]'"
                                                    x-model="param.parameter_group_id">
<div class="flex items-center justify-between mb-2 sm:mb-0 sm:justify-center sm:pt-2">
                                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-indigo-100 text-indigo-700 text-[10px] font-bold sm:w-4 sm:h-4" x-text="index + 1"></span>
                                                    <p class="text-[10px] font-semibold uppercase tracking-wide text-gray-400 sm:hidden">Parameter <span x-text="index + 1"></span></p>
                                                </div>
<div class="space-y-1">
                                                    <p class="text-[10px] font-semibold uppercase tracking-wide text-gray-500 sm:hidden">Nama Parameter</p>
                                                    <select x-model="param.list_parameter_id"
                                                        @change="applyListParameterToParamRow(param, addSensorOptions)"
                                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-xs text-gray-700 focus:border-indigo-500 focus:ring-indigo-500">
                                                        <option value="">Isi dari List Parameter (opsional)</option>
                                                        <template x-for="lp in listParameterOptions" :key="'add-list-' + lp.id">
                                                            <option :value="String(lp.id)"
x-text="lp.parameter_utama? `${(lp.nama_parameter || '').replaceAll('_',' ')} (${(lp.parameter_utama || '').replaceAll('_',' ')})`: (lp.nama_parameter || '').replaceAll('_',' ')">
                                                            </option>
                                                        </template>
                                                    </select>
                                                    <input :name="'params[' + index + '][nama_parameter]'"
                                                        x-model="param.nama_parameter" type="text" required
                                                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-800 focus:border-indigo-500 focus:ring-indigo-500">
                                                </div>
<div class="mt-2 grid grid-cols-2 gap-2 sm:contents">
                                                    <div>
                                                        <p class="mb-1 text-[10px] font-semibold uppercase tracking-wide text-gray-500 sm:hidden">Kolom Sensor</p>
                                                        <select :name="'params[' + index + '][kolom_sensor]'"
                                                            x-model="param.kolom_sensor" required
                                                            class="w-full rounded-lg border border-gray-300 px-2 py-2.5 text-sm text-gray-800 focus:border-indigo-500 focus:ring-indigo-500">
                                                            <option value="">Pilih</option>
                                                            <template x-for="sensor in addSensorOptions" :key="sensor">
                                                                <option :value="sensor" x-text="sensor"></option>
                                                            </template>
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <p class="mb-1 text-[10px] font-semibold uppercase tracking-wide text-gray-500 sm:hidden">Satuan</p>
                                                        <input :name="'params[' + index + '][satuan]'" x-model="param.satuan"
                                                            type="text" required placeholder="cth: m"
                                                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-800 focus:border-indigo-500 focus:ring-indigo-500">
                                                    </div>
                                                </div>
<div class="mt-2 flex justify-end sm:mt-0 sm:block">
                                                    <button type="button" @click="removeParameter(index)"
                                                        :disabled="addData.params.length === 1"
                                                        class="inline-flex h-9 w-9 sm:h-12 sm:w-12 items-center justify-center rounded-lg bg-red-500 text-white hover:bg-red-600 disabled:cursor-not-allowed disabled:opacity-50"
                                                        title="Hapus parameter">
                                                        <svg class="h-4 w-4 sm:h-5 sm:w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </div>
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
<div x-show="isOpen" class="fixed inset-0 z-50" aria-labelledby="modal-title" role="dialog"
            aria-modal="true" style="display: none;">

            <div class="fixed inset-0 flex items-end sm:items-center justify-center p-0 sm:p-4 overflow-y-auto"
                @click="closeModal()">

                <!-- Overlay -->
                <div x-show="isOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                    class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeModal()"></div>

                <!-- Modal Panel -->
                <div x-show="isOpen" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                    class="relative w-full max-w-4xl overflow-hidden rounded-lg bg-white text-left shadow-xl my-4"
                    @click.stop>

                    <!-- HEADER -->
                    <div class="flex items-center justify-between border-b px-6 py-4">
                        <h3 class="text-lg font-bold text-gray-900">Edit Device</h3>
                        <button @click="closeModal()" class="text-gray-400 hover:text-gray-500">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form :action="editData.updateUrl" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="px-6 py-4 space-y-6 max-h-[70vh] overflow-y-auto">
                            <input type="hidden" name="latitude" x-model="editData.latitude">
                            <input type="hidden" name="longitude" x-model="editData.longitude">
<div class="bg-slate-50 p-4 rounded-lg border border-slate-100">
                                <h4 class="text-sm font-semibold text-gray-900 mb-4">Identitas Device</h4>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700">Nama Pos</label>
                                        <input type="hidden" name="nama_lokasi" :value="editData.nama_lokasi">
                                        <div class="mt-1 w-full rounded-md border border-gray-200 bg-gray-100 px-3 py-2 text-sm text-gray-600">
                                            <span x-text="editData.nama_lokasi || '-'"></span>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700">Nama Logger</label>
                                        <div class="mt-1">
                                            <select x-model="editData.id_logger"
                                                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-800 focus:border-indigo-500 focus:ring-indigo-500">
                                                <option value="" disabled>Pilih nama logger</option>
                                                <option :value="editData.id_logger" x-text="editData.nama_logger"
                                                    x-show="editData.nama_logger"></option>
                                                <template x-for="logger in loggers" :key="logger.id_logger">
                                                    <option :value="logger.id_logger" x-text="logger.nama_logger"></option>
                                                </template>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
<template x-if="isEditAwlr()">
                                <div class="bg-slate-50 p-4 rounded-lg border border-slate-100">
                                    <h4 class="text-sm font-semibold text-gray-900 mb-4">Sub Kategori</h4>
                                    <div class="flex items-center gap-8 mb-4">
                                        <label class="inline-flex items-center gap-2 text-sm text-gray-800">
                                            <input type="radio" name="sub_kategori" value="jiat"
                                                x-model="editData.subKategori"
                                                class="border-gray-400 text-indigo-700 focus:ring-indigo-500">
                                            JIAT
                                        </label>
                                        <label class="inline-flex items-center gap-2 text-sm text-gray-800">
                                            <input type="radio" name="sub_kategori" value="non_jiat"
                                                x-model="editData.subKategori"
                                                class="border-gray-400 text-indigo-700 focus:ring-indigo-500">
                                            Non JIAT
                                        </label>
                                    </div>

                                    <template x-if="editData.subKategori === 'jiat'">
                                        <div>
                                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700">Kedalaman Sumur</label>
                                                    <div class="mt-1 flex rounded-md border border-gray-300 overflow-hidden">
                                                        <input type="number" step="0.01" name="kedalaman_sumur"
                                                            x-model="editData.kedalaman_sumur"
                                                            class="w-full border-0 px-3 py-2 text-sm text-gray-800 focus:ring-0">
                                                        <span class="flex items-center border-l border-gray-300 px-3 text-sm text-gray-700 bg-gray-50">m</span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700">Kedalaman Sensor</label>
                                                    <div class="mt-1 flex rounded-md border border-gray-300 overflow-hidden">
                                                        <input type="number" step="0.01" name="kedalaman_sensor"
                                                            x-model="editData.kedalaman_sensor"
                                                            class="w-full border-0 px-3 py-2 text-sm text-gray-800 focus:ring-0">
                                                        <span class="flex items-center border-l border-gray-300 px-3 text-sm text-gray-700 bg-gray-50">m</span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700">Kedalaman Pompa</label>
                                                    <div class="mt-1 flex rounded-md border border-gray-300 overflow-hidden">
                                                        <input type="number" step="0.01" name="kedalaman_pompa"
                                                            x-model="editData.kedalaman_pompa"
                                                            class="w-full border-0 px-3 py-2 text-sm text-gray-800 focus:ring-0">
                                                        <span class="flex items-center border-l border-gray-300 px-3 text-sm text-gray-700 bg-gray-50">m</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <input type="hidden" name="has_pump" :value="editData.subKategori === 'jiat' && editData.has_pump ? 1 : 0">
                                            <div class="mt-4 rounded-xl border border-slate-200 bg-white shadow-sm ring-1 ring-slate-100 p-4 transition-all duration-300 hover:shadow-md"
                                                :class="editData.has_pump ? 'border-emerald-200 bg-emerald-50/50' : '' ">
                                                <label class="flex items-center justify-between gap-4 cursor-pointer">
                                                    <div class="flex flex-col">
                                                        <span class="text-sm font-bold text-slate-800 flex items-center gap-2">
                                                            Kontrol Pump
                                                            <span class="hidden sm:inline-flex text-[10px] font-semibold tracking-wide uppercase px-2 py-0.5 rounded-full transition-colors duration-300"
                                                                :class="editData.has_pump ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500'"
                                                                x-text="editData.has_pump ? 'Aktif' : 'Nonaktif'">
                                                            </span>
                                                        </span>
                                                        <span class="mt-1 text-xs text-slate-500 leading-relaxed max-w-sm">
                                                            Aktifkan opsi ini untuk memunculkan aksi kontrol pump pada tabel device.
                                                        </span>
                                                    </div>

                                                    <button type="button"
                                                        @click="editData.has_pump = !editData.has_pump"
                                                        :aria-pressed="editData.has_pump"
                                                        class="relative inline-flex h-7 w-14 shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent transition-colors duration-300 ease-in-out focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 hover:brightness-110"
                                                        :class="editData.has_pump ? 'bg-emerald-500' : 'bg-slate-300'"
                                                        role="switch">
                                                        <span class="sr-only">Aktifkan Kontrol Pump</span>
                                                        <span aria-hidden="true"
                                                            class="pointer-events-none inline-block h-6 w-6 transform rounded-full bg-white shadow-md ring-0 transition-transform duration-300 ease-out"
                                                            :class="editData.has_pump ? 'translate-x-7' : 'translate-x-0'">
                                                        </span>
                                                    </button>
                                                </label>
                                            </div>
                                        </div>
                                    </template>

                                    <template x-if="editData.subKategori === 'non_jiat'">
                                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700">Jarak Sensor dengan Air</label>
                                                <div class="mt-1 flex rounded-md border border-gray-300 overflow-hidden">
                                                    <input type="number" step="any" name="jarak_sensor_ke_air"
                                                        x-model="editData.jarak_sensor_ke_air"
                                                        class="w-full border-0 px-3 py-2 text-sm text-gray-800 focus:ring-0">
                                                    <span class="flex items-center border-l border-gray-300 px-3 text-sm text-gray-700 bg-gray-50">m</span>
                                                </div>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700">Ketinggian Sensor</label>
                                                <div class="mt-1 flex rounded-md border border-gray-300 overflow-hidden">
                                                    <input type="number" step="any" name="tinggi_sensor"
                                                        x-model="editData.tinggi_sensor"
                                                        class="w-full border-0 px-3 py-2 text-sm text-gray-800 focus:ring-0">
                                                    <span class="flex items-center border-l border-gray-300 px-3 text-sm text-gray-700 bg-gray-50">m</span>
                                                </div>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700">Elevasi Maks (Batas Atas Peil)</label>
                                                <div class="mt-1 flex rounded-md border border-gray-300 overflow-hidden">
                                                    <input type="number" step="any" name="elevasi_max"
                                                        x-model="editData.elevasi_max"
                                                        placeholder="cth: 5.5"
                                                        class="w-full border-0 px-3 py-2 text-sm text-gray-800 focus:ring-0">
                                                    <span class="flex items-center border-l border-gray-300 px-3 text-sm text-gray-700 bg-gray-50">m</span>
                                                </div>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700">Elevasi Min (Batas Bawah Peil)</label>
                                                <div class="mt-1 flex rounded-md border border-gray-300 overflow-hidden">
                                                    <input type="number" step="any" name="elevasi_min"
                                                        x-model="editData.elevasi_min"
                                                        placeholder="cth: 0"
                                                        class="w-full border-0 px-3 py-2 text-sm text-gray-800 focus:ring-0">
                                                    <span class="flex items-center border-l border-gray-300 px-3 text-sm text-gray-700 bg-gray-50">m</span>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            {{-- ── AFMR: Contact / Non-Contact ─────────────────────────── --}}
                            <template x-if="isEditAfmr()">
                                <div class="bg-slate-50 p-4 rounded-lg border border-slate-100">
                                    <h4 class="text-sm font-semibold text-gray-900 mb-4">Sub Kategori AFMR</h4>
                                    <div class="flex items-center gap-8 mb-4">
                                        <label class="inline-flex items-center gap-2 text-sm text-gray-800">
                                            <input type="radio" name="sub_kategori" value="contact"
                                                x-model="editData.subKategori"
                                                class="border-gray-400 text-indigo-700 focus:ring-indigo-500">
                                            Contact
                                        </label>
                                        <label class="inline-flex items-center gap-2 text-sm text-gray-800">
                                            <input type="radio" name="sub_kategori" value="non_contact"
                                                x-model="editData.subKategori"
                                                class="border-gray-400 text-indigo-700 focus:ring-indigo-500">
                                            Non-Contact
                                        </label>
                                    </div>


                                </div>
                            </template>

<div class="bg-slate-50 p-4 rounded-lg border border-slate-100">
                                <h4 class="text-sm font-semibold text-gray-900 mb-3">Pilih Lokasi di Peta</h4>
                                <div class="grid grid-cols-2 gap-4 mb-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700">Latitude</label>
                                        <input type="text" x-model="editData.latitude"
                                            @input="updateEditMapFromInputs()"
                                            placeholder="-6.200000"
                                            class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-800 focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700">Longitude</label>
                                        <input type="text" x-model="editData.longitude"
                                            @input="updateEditMapFromInputs()"
                                            placeholder="106.816666"
                                            class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-800 focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                </div>
                                <div id="editDeviceMap" class="h-64 rounded-lg border border-gray-300"></div>
                                <p class="text-xs text-gray-500 mt-2">Klik peta atau geser marker untuk mengisi koordinat secara otomatis.</p>
                            </div>

                            <div class="rounded-lg border-2 border-indigo-200 bg-indigo-50"
                                x-data="{
                                    uploading: false,
                                    fotoError: '',
                                    fotoSuccess: '',
                                    get fotos() { return editData.fotos || []; },
                                    async uploadFoto(e) {
                                        const file = e.target.files[0];
                                        if (!file) return;
                                        const formData = new FormData();
                                        formData.append('foto', file);
                                        this.uploading = true;
                                        this.fotoError = '';
                                        try {
                                            const res = await fetch(`/pengaturan-device/${editData.id_logger}/foto`, {
                                                method: 'POST',
                                                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                                                body: formData,
                                            });
                                            const d = await res.json();
                                            if (d.success) {
                                                if (!editData.fotos) editData.fotos = [];
                                                editData.fotos.push(d.data);
                                                const dev = allDevices.find(x => x.id_logger === editData.id_logger);
                                                if (dev) {
                                                    if (!dev.fotos) dev.fotos = [];
                                                    dev.fotos.push(d.data);
                                                }
                                                this.fotoSuccess = 'Foto berhasil diupload!';
                                                setTimeout(() => this.fotoSuccess = '', 3000);
                                            } else {
                                                this.fotoError = d.message || 'Gagal mengupload foto.';
                                                if (d.errors) this.fotoError = Object.values(d.errors).flat()[0];
                                            }
                                        } catch(e) { this.fotoError = 'Gagal menghubungi server.'; }
                                        finally { 
                                            this.uploading = false; 
                                            e.target.value = null;
                                        }
                                    },
                                    async deleteFoto(idFoto) {
                                        if(!confirm('Yakin ingin menghapus foto ini?')) return;
                                        try {
                                            const res = await fetch(`/pengaturan-device/foto/${idFoto}`, {
                                                method: 'DELETE',
                                                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                                            });
                                            const d = await res.json();
                                            if (d.success) {
                                                editData.fotos = editData.fotos.filter(f => f.id !== idFoto);
                                                const dev = allDevices.find(x => x.id_logger === editData.id_logger);
                                                if (dev) dev.fotos = editData.fotos;
                                            }
                                        } catch(e) {}
                                    },
                                    async setUtama(idFoto) {
                                        try {
                                            const res = await fetch(`/pengaturan-device/foto/${idFoto}/utama`, {
                                                method: 'PUT',
                                                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                                            });
                                            const d = await res.json();
                                            if (d.success) {
                                                editData.fotos = editData.fotos.map(f => {
                                                    f.foto_utama = f.id === idFoto;
                                                    return f;
                                                });
                                                const dev = allDevices.find(x => x.id_logger === editData.id_logger);
                                                if (dev) dev.fotos = editData.fotos;
                                            }
                                        } catch(e) {}
                                    }
                                }">
                                <div class="flex items-center justify-between border-b border-indigo-200 px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <svg class="h-5 w-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <h4 class="text-sm font-semibold text-gray-900">Dokumentasi Pos</h4>
                                    </div>
                                    <div class="relative inline-block">
                                        <input type="file" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" @change="uploadFoto($event)" :disabled="uploading">
                                        <button type="button" class="relative z-0 inline-flex items-center gap-1.5 rounded-full bg-indigo-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-indigo-700 disabled:opacity-50 cursor-pointer" :disabled="uploading">
                                            <span x-show="!uploading">+ Upload Foto</span>
                                            <span x-show="uploading">Mengunggah...</span>
                                        </button>
                                    </div>
                                </div>
                                <div class="p-4">
                                    <div x-show="fotoError" x-cloak class="mb-3 rounded-lg bg-red-50 border border-red-200 px-3 py-2 text-sm text-red-700" x-text="fotoError"></div>
                                    <div x-show="fotoSuccess" x-cloak class="mb-3 rounded-lg bg-green-50 border border-green-200 px-3 py-2 text-sm text-green-700" x-text="fotoSuccess"></div>
                                    
                                    <template x-if="fotos.length === 0">
                                        <p class="text-center text-xs text-gray-500 py-4">Belum ada dokumentasi untuk pos ini.</p>
                                    </template>
                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                        <template x-for="f in fotos" :key="f.id">
                                            <div class="relative rounded-lg overflow-hidden border border-gray-200 bg-gray-100" style="aspect-ratio: 1/1;">
                                                <img :src="f.url_foto" class="w-full h-full object-cover">
                                                
                                                <div class="absolute inset-0 flex flex-col justify-between p-2" style="background: linear-gradient(to bottom, rgba(0,0,0,0.5) 0%, transparent 40%, transparent 60%, rgba(0,0,0,0.5) 100%);">
                                                    <div class="flex justify-end">
                                                        <button type="button" @click.prevent="deleteFoto(f.id)" class="bg-red-500 text-white rounded-full hover:bg-red-600 shadow-sm" style="padding: 6px;" title="Hapus Foto">
                                                            <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                        </button>
                                                    </div>
                                                    <div class="flex justify-center pb-1">
                                                        <button x-show="!f.foto_utama" type="button" @click.prevent="setUtama(f.id)" class="bg-white/90 text-slate-800 text-[10px] font-bold px-2 py-1 rounded shadow hover:bg-white transition-colors">Jadikan Utama</button>
                                                    </div>
                                                </div>
                                                <div x-show="f.foto_utama" class="absolute bottom-2 left-2 bg-indigo-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded shadow">
                                                    Utama
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-slate-50 rounded-lg border border-slate-100">
                                <div class="border-b border-slate-200 px-4 py-3 flex items-center justify-between gap-3">
                                    <h4 class="text-sm font-semibold text-gray-900">Daftar Parameter</h4>
                                    <button type="button" @click="applyTemplateToEdit()"
                                        :disabled="!canApplyTemplateEdit()"
                                        class="inline-flex items-center rounded-md border border-indigo-200 bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-700 hover:bg-indigo-100 disabled:cursor-not-allowed disabled:opacity-50">
                                        Template Kategori (Opsional)
                                    </button>
                                </div>
                                <div class="px-4 py-3">
<div class="hidden sm:grid grid-cols-[24px_1.6fr_1fr_0.9fr_52px] gap-3 px-1 pb-2 text-sm font-semibold text-gray-900">
                                        <span class="text-center text-gray-400">#</span>
                                        <span>Nama Parameter</span>
                                        <span>Kolom Sensor</span>
                                        <span>Satuan</span>
                                        <span class="text-center">Aksi</span>
                                    </div>

                                    <div class="space-y-2">
                                        <template x-for="(param, index) in editData.params"
                                            :key="param.id_param || 'param_' + index">
<div class="rounded-lg border border-gray-100 bg-slate-50 p-3
                                                        sm:rounded-none sm:border-0 sm:bg-transparent sm:p-0
                                                        sm:grid sm:grid-cols-[24px_1.6fr_1fr_0.9fr_52px] sm:gap-3 sm:items-start">
                                                <input type="hidden" :name="'params[' + index + '][id_param]'"
                                                    x-model="param.id_param">
                                                <input type="hidden" :name="'params[' + index + '][parameter_group_id]'"
                                                    x-model="param.parameter_group_id">
<div class="flex items-center justify-between mb-2 sm:mb-0 sm:justify-center sm:pt-2">
                                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-indigo-100 text-indigo-700 text-[10px] font-bold sm:w-4 sm:h-4" x-text="index + 1"></span>
<p class="text-[10px] font-semibold uppercase tracking-wide text-gray-400 sm:hidden">Parameter <span x-text="index + 1"></span></p>
                                                </div>
<div class="space-y-1">
                                                    <p class="text-[10px] font-semibold uppercase tracking-wide text-gray-500 sm:hidden">Nama Parameter</p>
                                                    <select x-model="param.list_parameter_id"
                                                        @change="applyListParameterToParamRow(param, editSensorOptions)"
                                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-xs text-gray-700 focus:border-indigo-500 focus:ring-indigo-500">
                                                        <option value="">Isi dari List Parameter (opsional)</option>
                                                        <template x-for="lp in listParameterOptions" :key="'edit-list-' + lp.id">
                                                            <option :value="String(lp.id)" 
                                                                x-text="lp.parameter_utama? `${(lp.nama_parameter || '').replaceAll('_',' ')} (${(lp.parameter_utama || '').replaceAll('_',' ')})`: (lp.nama_parameter || '').replaceAll('_',' ')">
                                                            </option>
                                                        </template>
                                                    </select>
                                                    <input :name="'params[' + index + '][nama_parameter]'"
                                                        x-model="param.nama_parameter"
                                                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-800 focus:border-indigo-500 focus:ring-indigo-500">
                                                </div>
<div class="mt-2 grid grid-cols-2 gap-2 sm:contents">
                                                    <div>
                                                        <p class="mb-1 text-[10px] font-semibold uppercase tracking-wide text-gray-500 sm:hidden">Kolom Sensor</p>
                                                        <select :name="'params[' + index + '][kolom_sensor]'"
                                                            x-model="param.kolom_sensor"
                                                            class="w-full rounded-lg border border-gray-300 px-2 py-2.5 text-sm text-gray-800 focus:border-indigo-500 focus:ring-indigo-500">
                                                            <option value="">Pilih</option>
                                                            <template x-for="sensor in editSensorOptions" :key="sensor">
                                                                <option :value="sensor" x-text="sensor"
                                                                    :selected="sensor === param.kolom_sensor"></option>
                                                            </template>
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <p class="mb-1 text-[10px] font-semibold uppercase tracking-wide text-gray-500 sm:hidden">Satuan</p>
                                                        <input :name="'params[' + index + '][satuan]'" x-model="param.satuan"
                                                            placeholder="cth: m"
                                                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-800 focus:border-indigo-500 focus:ring-indigo-500">
                                                    </div>
                                                </div>
<div class="mt-2 flex justify-end sm:mt-0 sm:block">
                                                    <button type="button" @click="removeEditParameter(index)"
                                                        :disabled="editData.params.length === 1"
                                                        class="inline-flex h-9 w-9 sm:h-12 sm:w-12 items-center justify-center rounded-lg bg-red-500 text-white hover:bg-red-600 disabled:cursor-not-allowed disabled:opacity-50"
                                                        title="Hapus parameter">
                                                        <svg class="h-4 w-4 sm:h-5 sm:w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </template>
                                    </div>

                                    <button type="button" @click="addEditParameter()"
                                        class="mt-3 text-sm font-semibold text-indigo-800 hover:text-indigo-900">
                                        + Tambah Parameter
                                    </button>
                                </div>
                            </div>

<div class="rounded-lg border-2 border-orange-200 bg-orange-50"
                                x-data="{
                                    perbaikanLoading: false,
                                    perbaikanError: '',
                                    perbaikanSuccess: '',
                                    pForm: { keterangan: '', tanggal_perbaikan: '', petugas: '' },

                                    get statusPerbaikan() { return editData.status_perbaikan || 'normal'; },
                                    get perbaikanHistory() { return editData.perbaikan_history || []; },

                                    async toggleNormal() {
                                        if (this.statusPerbaikan === 'normal') return;
                                        this.perbaikanLoading = true;
                                        this.perbaikanError = '';
                                        try {
                                            const res = await fetch(`/pengaturan-device/${editData.id_logger}/perbaikan/selesai`, {
                                                method: 'PUT',
                                                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json' },
                                            });
                                            const d = await res.json();
                                            if (d.success) {
                                                editData.status_perbaikan = 'normal';
                                                const dev = devices.find(x => x.id_logger === editData.id_logger);
                                                if (dev) dev.status_perbaikan = 'normal';
                                                this.perbaikanSuccess = 'Status berhasil dikembalikan ke Normal.';
                                                setTimeout(() => this.perbaikanSuccess = '', 3000);
                                            } else {
                                                this.perbaikanError = d.message || 'Gagal mengubah status.';
                                            }
                                        } catch(e) { this.perbaikanError = 'Gagal menghubungi server.'; }
                                        finally { this.perbaikanLoading = false; }
                                    },

                                    async submitPerbaikan() {
                                        if (!this.pForm.keterangan.trim()) { this.perbaikanError = 'Keterangan harus diisi.'; return; }
                                        if (!this.pForm.tanggal_perbaikan) { this.perbaikanError = 'Tanggal perbaikan harus diisi.'; return; }
                                        if (!this.pForm.petugas.trim()) { this.perbaikanError = 'Petugas harus diisi.'; return; }
                                        this.perbaikanLoading = true;
                                        this.perbaikanError = '';
                                        try {
                                            const res = await fetch(`/pengaturan-device/${editData.id_logger}/perbaikan`, {
                                                method: 'POST',
                                                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json' },
                                                body: JSON.stringify(this.pForm),
                                            });
                                            const d = await res.json();
                                            if (d.success) {
                                                editData.status_perbaikan = 'perbaikan';
                                                editData.perbaikan_history = d.history || editData.perbaikan_history;
                                                const dev = devices.find(x => x.id_logger === editData.id_logger);
                                                if (dev) { dev.status_perbaikan = 'perbaikan'; dev.perbaikan_history = d.history; }
                                                this.pForm = { keterangan: '', tanggal_perbaikan: '', petugas: '' };
                                                this.perbaikanSuccess = 'Catatan perbaikan berhasil disimpan!';
                                                setTimeout(() => this.perbaikanSuccess = '', 3000);
                                            } else {
                                                this.perbaikanError = d.message || 'Gagal menyimpan.';
                                                if (d.errors) this.perbaikanError = Object.values(d.errors).flat()[0];
                                            }
                                        } catch(e) { this.perbaikanError = 'Gagal menghubungi server.'; }
                                        finally { this.perbaikanLoading = false; }
                                    }
                                }"
                            >
                                <div class="flex items-center justify-between border-b border-orange-200 px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <svg class="h-5 w-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        <h4 class="text-sm font-semibold text-gray-900">Status Perbaikan</h4>
                                    </div>
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold"
                                        :class="statusPerbaikan === 'perbaikan' ? 'bg-orange-500 text-white' : 'bg-green-500 text-white'"
                                        x-text="statusPerbaikan === 'perbaikan' ? '⚠ Sedang Perbaikan' : '✓ Normal'">
                                    </span>
                                </div>

                                <div class="p-4 space-y-4">
                                    <div x-show="perbaikanError" x-cloak class="rounded-lg bg-red-50 border border-red-200 px-3 py-2 text-sm text-red-700" x-text="perbaikanError"></div>
                                    <div x-show="perbaikanSuccess" x-cloak class="rounded-lg bg-green-50 border border-green-200 px-3 py-2 text-sm text-green-700" x-text="perbaikanSuccess"></div>
<div class="flex gap-2">
                                        <button type="button" @click="toggleNormal()" :disabled="statusPerbaikan === 'normal' || perbaikanLoading"
                                            class="flex-1 py-2.5 rounded-lg text-sm font-semibold border-2 transition-all"
                                            :class="statusPerbaikan === 'normal' ? 'bg-green-600 border-green-600 text-white' : 'bg-white border-green-300 text-green-700 hover:bg-green-50'">
                                            ✓ Normal
                                        </button>
                                        <button type="button" @click="editData.status_perbaikan = 'perbaikan'" :disabled="perbaikanLoading"
                                            class="flex-1 py-2.5 rounded-lg text-sm font-semibold border-2 transition-all"
                                            :class="statusPerbaikan === 'perbaikan' ? 'bg-orange-500 border-orange-500 text-white' : 'bg-white border-orange-300 text-orange-700 hover:bg-orange-50'">
                                            ⚠ Perbaikan
                                        </button>
                                    </div>
<div x-show="statusPerbaikan === 'perbaikan'" x-cloak
                                        x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 -translate-y-2"
                                        x-transition:enter-end="opacity-100 translate-y-0"
                                        class="space-y-3 rounded-lg border border-orange-300 bg-white p-4">
                                        <p class="text-xs font-semibold text-orange-700 uppercase tracking-wide">Catatan Perbaikan Baru</p>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Keterangan <span class="text-red-500">*</span></label>
                                            <textarea x-model="pForm.keterangan" rows="3" placeholder="Contoh: Sensor rusak, baterai lemah, kalibrasi ulang..."
                                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-800 focus:border-orange-400 focus:ring-orange-300 resize-none"></textarea>
                                        </div>
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 mb-1">Tanggal Perbaikan <span class="text-red-500">*</span></label>
                                                <input type="date" x-model="pForm.tanggal_perbaikan"
                                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-800 focus:border-orange-400 focus:ring-orange-300">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 mb-1">Petugas <span class="text-red-500">*</span></label>
                                                <input type="text" x-model="pForm.petugas" placeholder="Nama teknisi..."
                                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-800 focus:border-orange-400 focus:ring-orange-300">
                                            </div>
                                        </div>
                                        <button type="button" @click="submitPerbaikan()" :disabled="perbaikanLoading"
                                            class="w-full rounded-lg bg-orange-500 py-2.5 text-sm font-semibold text-white hover:bg-orange-600 transition-colors disabled:opacity-50">
                                            <span x-show="!perbaikanLoading">💾 Simpan Catatan Perbaikan</span>
                                            <span x-show="perbaikanLoading" x-cloak>Menyimpan...</span>
                                        </button>
                                    </div>
<template x-if="perbaikanHistory.length > 0">
                                        <div>
                                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Riwayat Perbaikan</p>
                                            <div class="space-y-2 max-h-52 overflow-y-auto pr-1">
                                                <template x-for="(item, i) in perbaikanHistory" :key="item.id || i">
                                                    <div class="rounded-lg border border-gray-200 bg-white px-3 py-2.5">
                                                        <div class="flex items-start justify-between gap-2">
                                                            <div class="flex-1 min-w-0">
                                                                <p class="text-sm font-medium text-gray-900 leading-snug" x-text="item.keterangan"></p>
                                                                <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-0.5 text-xs text-gray-500">
                                                                    <span>📅 <span x-text="item.tanggal_perbaikan"></span></span>
                                                                    <span>👤 <span x-text="item.petugas"></span></span>
                                                                    <span>🕒 <span x-text="item.created_at"></span></span>
                                                                </div>
                                                            </div>
                                                            <span class="flex-shrink-0 rounded-full px-2 py-0.5 text-xs font-semibold"
                                                                :class="item.status_akhir === 'selesai' ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700'"
                                                                x-text="item.status_akhir === 'selesai' ? 'Selesai' : 'Proses'">
                                                            </span>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                    <template x-if="perbaikanHistory.length === 0">
                                        <p class="text-center text-xs text-gray-400 py-2">Belum ada riwayat perbaikan untuk logger ini.</p>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- FOOTER -->
                        <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3">
                            <button type="button" @click="closeModal()"
                                class="h-11 sm:h-auto flex-1 sm:flex-none inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                                Batal
                            </button>
                            <button type="submit"
                                class="h-11 sm:h-auto flex-1 sm:flex-none inline-flex justify-center rounded-md border border-transparent bg-indigo-900 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-800">
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
                showPumpModal: false,
                isOpen: false,
                isAddOpen: false,
                ready: false,
                sensorOptions: [],
                editSensorOptions: [],
                addSensorOptions: [],
                instansis: [],
                loggers: [],
                templateMap: @json($templateMap ?? []),
                listParameterOptions: @json($listParameters ?? []),
                awlrCategoryIds: @json($awlrCategoryIds ?? []),
                afmrCategoryIds: @json($afmrCategoryIds ?? []),
                addDeviceMap: null,
                addDeviceMarker: null,
                editDeviceMap: null,
                editDeviceMarker: null,
                detailData: {
                    id_logger: '',
                    id_katlogger: '',
                    nama_lokasi: '',
                    nama_logger: '',
                    alamat: '',
                    latitude: '',
                    longitude: '',
                    sub_kategori: '-',
                    has_pump_label: '-',
                    kedalaman_sumur: '-',
                    kedalaman_sensor: '-',
                    kedalaman_pompa: '-',
                    params: []
                },
                editData: {
                    updateUrl: '',
                    id_logger: '',
                    id_katlogger: '',
                    nama_lokasi: '',
                    nama_logger: '',
                    subKategori: 'jiat',
                    has_pump: false,
                    latitude: '',
                    longitude: '',
                    kedalaman_sumur: '',
                    kedalaman_sensor: '',
                    kedalaman_pompa: '',
                    jarak_sensor_ke_air: '',
                    tinggi_sensor: '',
                    elevasi_max: '',
                    elevasi_min: '',
                    // AFMR Contact
                    lebar_sungai: '',
                    kedalaman_rata: '',
                    koefisien_debit: '',
                    params: [],
                    fotos: []
                },
                addData: {
                    latitude: '',
                    longitude: '',
                    subKategori: '',
                    has_pump: false,
                    kedalaman_sumur: '',
                    kedalaman_sensor: '',
                    kedalaman_pompa: '',
                    jarak_sensor_ke_air: '',
                    tinggi_sensor: '',
                    elevasi_max: '',
                    elevasi_min: '',
                    selectedLogger: null,
                    id_katlogger: '',
                    sensorCount: 16,
                    params: [{
                        list_parameter_id: '',
                        nama_parameter: '',
                        kolom_sensor: '',
                        satuan: '',
                        parameter_group_id: ''
                    }]
                },
                formdata: {
                    id_logger: '',
                    nama_lokasi: '',
                    latitude: '',
                    longitude: '',
                    params: []
                },
                pumpControlData: {
                    id_logger: '',
                    nama_lokasi: '',
                    nama_logger: '',
                    preview_state: 'off'
                },
                pumpWorkflow: {
                    target_state: 'off',
                    command_name: '',
                    visible: false,
                    running: false,
                    success: false,
                    steps: []
                },
                pumpWorkflowTimers: [],

                filteredDevices() {
                    const devices = Array.isArray(this.allDevices) ? this.allDevices : [];
                    const q = (this.searchQuery || '').trim();
                    if (!q) return devices;
                    const fuse = new Fuse(devices, {
                        threshold: 0.35,
                        keys: ['nama_lokasi', 'alamat']
                    });
                    const fuzzyResults = fuse.search(q).map(r => r.item);
                    const ql = q.toLowerCase();
                    const exactResults = devices.filter(d =>
                        d.id_logger && d.id_logger.toLowerCase().includes(ql)
                    );

                    const seen = new Set();
                    return [...fuzzyResults, ...exactResults].filter(d => {
                        if (seen.has(d.id_logger)) return false;
                        seen.add(d.id_logger);
                        return true;
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
                    try {
                        const response = await fetch('{{ route('device.create') }}')
                        const data = await response.json()
                        this.instansis = data.instansis
                        this.loggers = data.loggers
                    } catch (error) {
                        console.error('Error fetching data:', error)
                    }

                    this.isAddOpen = true
                    this.$nextTick(() => {
                        this.initAddDeviceMap()
                    })
                },

                closeAddModal() {
                    this.isAddOpen = false
                    this.addData = {
                        latitude: '',
                        longitude: '',
                        subKategori: '',
                        has_pump: false,
                        kedalaman_sumur: '',
                        kedalaman_sensor: '',
                        kedalaman_pompa: '',
                        jarak_sensor_ke_air: '',
                        tinggi_sensor: '',
                        elevasi_max: '',
                        elevasi_min: '',
                        selectedLogger: null,
                        id_katlogger: '',
                        sensorCount: 16,
                        params: [{
                            list_parameter_id: '',
                            nama_parameter: '',
                            kolom_sensor: '',
                            satuan: '',
                            parameter_group_id: ''
                        }]
                    }
                    if (this.addDeviceMap) {
                        this.addDeviceMap.remove()
                        this.addDeviceMap = null
                        this.addDeviceMarker = null
                    }
                },

                initAddDeviceMap() {
                    this.$nextTick(() => {
                        if (!document.getElementById('addDeviceMap')) return
                        if (this.addDeviceMap) {
                            this.addDeviceMap.remove()
                        }
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

                        const customMarkerIcon = L.icon({
                            iconUrl: '{{ asset("icons/marker_lokasi.svg") }}',
                            iconSize:    [34, 56],
                            iconAnchor:  [17, 56],
                            popupAnchor: [0, -56]
                        });
                        if (this.addData.latitude && this.addData.longitude) {
                            this.addDeviceMarker = L.marker([defaultLat, defaultLng], {
                                draggable: true,
                                icon: customMarkerIcon
                            }).addTo(this.addDeviceMap)
                        } else {
                            this.addDeviceMarker = L.marker([defaultLat, defaultLng], {
                                draggable: true,
                                icon: customMarkerIcon
                            }).addTo(this.addDeviceMap)
                            this.addData.latitude = defaultLat.toFixed(6)
                            this.addData.longitude = defaultLng.toFixed(6)
                        }
                        this.addDeviceMap.on('click', (e) => {
                            this.addData.latitude = e.latlng.lat.toFixed(6)
                            this.addData.longitude = e.latlng.lng.toFixed(6)
                            this.addDeviceMarker.setLatLng(e.latlng)
                        })
                        this.addDeviceMarker.on('dragend', (e) => {
                            const position = e.target.getLatLng()
                            this.addData.latitude = position.lat.toFixed(6)
                            this.addData.longitude = position.lng.toFixed(6)
                        })
                    })
                },
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

                initEditDeviceMap() {
                    if (!document.getElementById('editDeviceMap')) return

                    if (this.editDeviceMap) {
                        this.editDeviceMap.remove()
                        this.editDeviceMap = null
                        this.editDeviceMarker = null
                    }

                    const defaultLat = parseFloat(this.editData.latitude) || -6.200000
                    const defaultLng = parseFloat(this.editData.longitude) || 106.816666
                    const defaultZoom = 13

                    this.editDeviceMap = L.map('editDeviceMap').setView([defaultLat, defaultLng], defaultZoom)

                    L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                        maxZoom: 20,
                        subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
                    }).addTo(this.editDeviceMap)

                    const customMarkerIcon = L.icon({
                        iconUrl: '{{ asset("icons/marker_lokasi.svg") }}',
                        iconSize:    [34, 56],
                        iconAnchor:  [17, 56],
                        popupAnchor: [0, -56]
                    })

                    this.editDeviceMarker = L.marker([defaultLat, defaultLng], {
                        draggable: true,
                        icon: customMarkerIcon
                    }).addTo(this.editDeviceMap)

                    this.editDeviceMap.on('click', (e) => {
                        this.editData.latitude  = e.latlng.lat.toFixed(6)
                        this.editData.longitude = e.latlng.lng.toFixed(6)
                        this.editDeviceMarker.setLatLng(e.latlng)
                    })

                    this.editDeviceMarker.on('dragend', (e) => {
                        const position = e.target.getLatLng()
                        this.editData.latitude  = position.lat.toFixed(6)
                        this.editData.longitude = position.lng.toFixed(6)
                    })

                    setTimeout(() => {
                        this.editDeviceMap.invalidateSize()
                    }, 150)
                },

                updateEditMapFromInputs() {
                    if (!this.editDeviceMap || !this.editDeviceMarker) return

                    const lat = parseFloat(this.editData.latitude)
                    const lng = parseFloat(this.editData.longitude)

                    if (!isNaN(lat) && !isNaN(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
                        this.editDeviceMarker.setLatLng([lat, lng])
                        this.editDeviceMap.setView([lat, lng], this.editDeviceMap.getZoom())
                    }
                },

                onLoggerChange(event) {
                    const loggerId = event.target.value
                    const logger = this.loggers.find(l => l.id_logger === loggerId)

                    if (logger) {
                        this.addData.selectedLogger = logger
                        this.addData.id_katlogger = logger.id_katlogger || ''
                        this.addData.sensorCount = logger.sensor_count || 16
                        this.addSensorOptions = Array.from({
                            length: logger.sensor_count
                        }, (_, i) => 'sensor' + (i + 1))
                        this.addData.params.forEach(param => {
                            param.kolom_sensor = ''
                        })

                        if (this.isAwlrKategori(this.addData.id_katlogger)) {
                            if (this.addData.subKategori !== 'jiat' && this.addData.subKategori !==
                                'non_jiat') {
                                this.addData.subKategori = 'non_jiat'
                            }
                        } else {
                            this.addData.subKategori = ''
                            this.addData.has_pump = false
                            this.addData.kedalaman_sumur = ''
                            this.addData.kedalaman_sensor = ''
                            this.addData.kedalaman_pompa = ''
                        }
                    } else {
                        this.addData.subKategori = ''
                        this.addData.has_pump = false
                        this.addData.kedalaman_sumur = ''
                        this.addData.kedalaman_sensor = ''
                        this.addData.kedalaman_pompa = ''
                        this.addData.id_katlogger = ''
                    }
                },

                isAwlrKategori(kategoriId) {
                    const parsedId = Number(kategoriId)
                    if (Number.isNaN(parsedId) || !parsedId) return false
                    return (this.awlrCategoryIds || []).map(id => Number(id)).includes(parsedId)
                },

                isAfmrKategori(kategoriId) {
                    const parsedId = Number(kategoriId)
                    if (Number.isNaN(parsedId) || !parsedId) return false
                    return (this.afmrCategoryIds || []).map(id => Number(id)).includes(parsedId)
                },

                isAddAwlr() {
                    return this.isAwlrKategori(this.addData.id_katlogger)
                },

                isEditAwlr() {
                    return this.isAwlrKategori(this.editData.id_katlogger)
                },

                isEditAfmr() {
                    return this.isAfmrKategori(this.editData.id_katlogger)
                },

                canControlPump(device) {
                    return !!(!device?.nonjiat && Number(device?.jiat?.has_pump ?? 0))
                },

                addParameter() {
                    this.addData.params.push({
                        list_parameter_id: '',
                        nama_parameter: '',
                        kolom_sensor: '',
                        satuan: '',
                        parameter_group_id: ''
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
                        list_parameter_id: '',
                        nama_parameter: '',
                        kolom_sensor: '',
                        satuan: '',
                        parameter_group_id: ''
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
                    const isNonJiat = !!device?.nonjiat
                    const isJiat = !isNonJiat && parseFloat(jiat?.kedalaman_sumur ?? 0) > 0

                    this.detailData = {
                        id_logger: device?.id_logger ?? '-',
                        id_katlogger: device?.id_katlogger ?? '',
                        nama_lokasi: device?.nama_lokasi ?? '-',
                        nama_logger: device?.nama_logger ?? '-',
                        alamat: device?.alamat ?? '-',
                        latitude: device?.lokasi?.latitude ?? '-',
                        longitude: device?.lokasi?.longitude ?? '-',
                        sub_kategori: isNonJiat ? 'NON JIAT' : (isJiat ? 'JIAT' : '-'),
                        has_pump_label: this.canControlPump(device) ? 'Aktif' : 'Tidak aktif',
                        kedalaman_sumur: hasJiat && jiat.kedalaman_sumur !== null ?
                            `${jiat.kedalaman_sumur} m` : '-',
                        kedalaman_sensor: hasJiat && jiat.kedalaman_sensor !== null ?
                            `${jiat.kedalaman_sensor} m` : '-',
                        kedalaman_pompa: hasJiat && jiat.kedalaman_pompa !== null ?
                            `${jiat.kedalaman_pompa} m` : '-',
                        jarak_sensor_ke_air: device?.nonjiat?.jarak_sensor_ke_air != null ? parseFloat(device.nonjiat.jarak_sensor_ke_air) : null,
                        tinggi_sensor: device?.nonjiat?.tinggi_sensor != null ? parseFloat(device.nonjiat.tinggi_sensor) : null,
                        params: Array.isArray(device?.params) ? device.params : []
                    }

                    this.showDetailModal = true
                },

                closeDetailModal() {
                    this.showDetailModal = false
                },

                openPumpModal(device) {
                    this.pumpControlData = {
                        id_logger: device?.id_logger ?? '',
                        nama_lokasi: device?.nama_lokasi ?? '',
                        nama_logger: device?.nama_logger ?? '',
                        preview_state: 'off'
                    }

                    this.resetPumpWorkflow()
                    this.showPumpModal = true
                },

                closePumpModal() {
                    this.resetPumpWorkflow()
                    this.showPumpModal = false
                },

                setPumpPreviewState(state) {
                    this.pumpControlData.preview_state = state === 'on' ? 'on' : 'off'
                },

                clearPumpWorkflowTimers() {
                    this.pumpWorkflowTimers.forEach((timerId) => window.clearTimeout(timerId))
                    this.pumpWorkflowTimers = []
                },

                resetPumpWorkflow() {
                    this.clearPumpWorkflowTimers()
                    this.pumpWorkflow = {
                        target_state: 'off',
                        command_name: '',
                        visible: false,
                        running: false,
                        success: false,
                        error: null,
                        steps: []
                    }
                },

                buildPumpWorkflowSteps(commandName) {
                    return [{
                            key: 'confirm',
                            title: 'Confirm action',
                            subtitle: `Sent command: ${commandName}`,
                            status: 'done'
                        },
                        {
                            key: 'mqtt',
                            title: 'Connecting to MQTT broker',
                            subtitle: 'Connecting...',
                            status: 'active'
                        },
                        {
                            key: 'logger',
                            title: 'Mengirim perintah ke logger',
                            subtitle: 'Mengirim perintah...',
                            status: 'pending'
                        }
                    ]
                },

                pumpWorkflowPercent() {
                    const steps = this.pumpWorkflow.steps || []
                    if (!steps.length) return 0

                    const doneCount = steps.filter((step) => step.status === 'done').length
                    const activeBonus = steps.some((step) => step.status === 'active') ? 0.65 : 0

                    return Math.round(((doneCount + activeBonus) / steps.length) * 100)
                },

                pumpWorkflowCardClasses(status) {
                    if (status === 'done') {
                        return 'border-emerald-200 bg-emerald-50/70'
                    }

                    if (status === 'active') {
                        return 'border-emerald-200 bg-emerald-50/40 shadow-[0_0_0_1px_rgba(16,185,129,0.05)]'
                    }

                    if (status === 'error') {
                        return 'border-red-200 bg-red-50/70'
                    }

                    return 'border-slate-200 bg-white/70 opacity-80'
                },

                pumpWorkflowIconWrapClasses(status) {
                    if (status === 'done') {
                        return 'bg-emerald-100'
                    }

                    if (status === 'active') {
                        return 'bg-emerald-100/80'
                    }

                    if (status === 'error') {
                        return 'bg-red-100'
                    }

                    return 'bg-slate-100'
                },

                pumpWorkflowActiveBarWidth(index) {
                    const widthMap = {
                        0: 100,
                        1: 52,
                        2: 68,
                        3: 84
                    }

                    return widthMap[index] ?? 55
                },

                markPumpStep(stepKey, status, subtitle = null) {
                    this.pumpWorkflow.steps = this.pumpWorkflow.steps.map((step) => {
                        if (step.key !== stepKey) return step

                        return {
                            ...step,
                            status,
                            subtitle: subtitle ?? step.subtitle
                        }
                    })
                },

                async runPumpAction(targetState) {
                    const nextState = targetState === 'on' ? 'on' : 'off'
                    const commandName = nextState === 'on' ? 'turn_on_pump' : 'turn_off_pump'

                    this.resetPumpWorkflow()
                    this.pumpWorkflow = {
                        target_state: nextState,
                        command_name: commandName,
                        visible: true,
                        running: true,
                        success: false,
                        error: null,
                        steps: this.buildPumpWorkflowSteps(commandName)
                    }

                    try {
                        this.markPumpStep('mqtt', 'active', 'Menghubungkan ke MQTT broker...')

                        const fetchPromise = fetch('{{ route("pump.command") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                id_logger: this.pumpControlData.id_logger,
                                action: nextState,
                            }),
                        })

                        // Setelah ~1 detik, anggap MQTT sudah konek
                        await new Promise(r => setTimeout(r, 1000))
                        this.markPumpStep('mqtt', 'done', 'MQTT connected')
                        this.markPumpStep('logger', 'active', 'Menunggu respon dari logger...')

                        const res = await fetchPromise
                        const data = await res.json()

                        if (!res.ok) {
                            const failStep = data.step || 'mqtt_connect'

                            if (failStep === 'mqtt_connect') {
                                this.markPumpStep('mqtt', 'error', data.message || 'Gagal terhubung ke broker')
                                this.markPumpStep('logger', 'pending', 'Mengirim perintah...')
                            } else {
                                this.markPumpStep('logger', 'error', data.message || 'Logger tidak merespons')
                            }

                            this.pumpWorkflow.error = data.message || 'Gagal mengirim perintah'
                            this.pumpWorkflow.running = false
                            return
                        }

                        // Sukses — logger merespons
                        this.markPumpStep('logger', 'done', data.pump?.msg || 'Respon diterima dari logger')
                        this.pumpWorkflow.running = false
                        this.pumpWorkflow.success = true
                        this.setPumpPreviewState(nextState)

                        this.pumpWorkflowTimers.push(window.setTimeout(() => {
                            this.pumpWorkflow.visible = false
                        }, 3000))

                    } catch (e) {
                        console.error('Pump command error:', e)
                        this.markPumpStep('mqtt', 'error', 'Network error: ' + (e.message || 'Tidak dapat terhubung'))
                        this.pumpWorkflow.error = 'Gagal menghubungi server'
                        this.pumpWorkflow.running = false
                    }
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
                        id_katlogger: device.id_katlogger ?? '',
                        nama_lokasi: device.lokasi?.nama_lokasi ?? '',
                        nama_logger: device.nama_logger ?? '',
                        subKategori: device.nonjiat
                            ? 'non_jiat'
                            : (parseFloat(device.jiat?.kedalaman_sumur ?? 0) > 0 ? 'jiat' : 'non_jiat'),
                        has_pump: !!Number(device.jiat?.has_pump ?? 0),
                        latitude: device.lokasi?.latitude ?? '',
                        longitude: device.lokasi?.longitude ?? '',
                        kedalaman_sumur: device.jiat?.kedalaman_sumur ?? '',
                        kedalaman_sensor: device.jiat?.kedalaman_sensor ?? '',
                        kedalaman_pompa: device.jiat?.kedalaman_pompa ?? '',
                        jarak_sensor_ke_air: device.nonjiat?.jarak_sensor_ke_air != null ? parseFloat(device.nonjiat.jarak_sensor_ke_air) : (device.afmr_noncontact?.jarak_sensor_ke_air != null ? parseFloat(device.afmr_noncontact.jarak_sensor_ke_air) : ''),
                        tinggi_sensor: device.nonjiat?.tinggi_sensor != null ? parseFloat(device.nonjiat.tinggi_sensor) : (device.afmr_noncontact?.tinggi_sensor != null ? parseFloat(device.afmr_noncontact.tinggi_sensor) : ''),
                        elevasi_max: device.nonjiat?.elevasi_max != null ? parseFloat(device.nonjiat.elevasi_max) : (device.afmr_noncontact?.elevasi_max != null ? parseFloat(device.afmr_noncontact.elevasi_max) : ''),
                        elevasi_min: device.nonjiat?.elevasi_min != null ? parseFloat(device.nonjiat.elevasi_min) : (device.afmr_noncontact?.elevasi_min != null ? parseFloat(device.afmr_noncontact.elevasi_min) : ''),
                        // AFMR sub_kategori detection
                        subKategori: device.nonjiat
                            ? 'non_jiat'
                            : (parseFloat(device.jiat?.kedalaman_sumur ?? 0) > 0 ? 'jiat'
                            : (device.afmr_contact ? 'contact'
                            : (device.afmr_noncontact ? 'non_contact' : 'non_contact'))),
                        // AFMR Contact fields
                        lebar_sungai: device.afmr_contact?.lebar_sungai != null ? parseFloat(device.afmr_contact.lebar_sungai) : '',
                        kedalaman_rata: device.afmr_contact?.kedalaman_rata != null ? parseFloat(device.afmr_contact.kedalaman_rata) : '',
                        koefisien_debit: device.afmr_contact?.koefisien_debit != null ? parseFloat(device.afmr_contact.koefisien_debit) : '',
                        fotos: device.fotos ? [...device.fotos] : [],
                    })
                    this.editData.params = []
                    const sensorCount = parseInt(device.sensor_count ?? 16, 10)
                    const validSensorCount = (Number.isNaN(sensorCount) || sensorCount <= 0) ? 16 :
                        sensorCount
                    const sensorsFromCount = Array.from({
                        length: validSensorCount
                    }, (_, i) => 'sensor' + (i + 1))
                    const sensorsFromParams = (device.params ?? [])
                        .map(p => String(p.kolom_sensor || '').trim())
                        .filter(Boolean)
                    const mergedSensors = [...new Set([...sensorsFromCount, ...sensorsFromParams])]
                    this.editSensorOptions = mergedSensors.sort((a, b) => {
                        const aNum = parseInt(a.replace('sensor', ''), 10)
                        const bNum = parseInt(b.replace('sensor', ''), 10)
                        if (Number.isNaN(aNum) || Number.isNaN(bNum)) return a.localeCompare(b)
                        return aNum - bNum
                    })
                    this.isOpen = true
                    this.$nextTick(() => {
                        this.initEditDeviceMap()
                    })
                    this.$nextTick(() => {
                        this.editData.params = (device.params ?? []).map(p => ({
                            id_param: p.id_param,
                            list_parameter_id: '',
                            nama_parameter: (p.nama_parameter ?? '').toString().replaceAll('_', ' '),
                            kolom_sensor: p.kolom_sensor ?? '',
                            satuan: p.satuan ?? '',
                            parameter_group_id: p.parameter_group_id ?? ''
                        }))

                        if (!this.editData.params.length) {
                            this.addEditParameter()
                        }

                    })
                },

                closeModal() {
                    this.isOpen = false
                    if (this.editDeviceMap) {
                        this.editDeviceMap.remove()
                        this.editDeviceMap = null
                        this.editDeviceMarker = null
                    }
                },

                canApplyTemplateAdd() {
                    return !!this.addData.id_katlogger && this.templateRowsByKategori(this.addData
                            .id_katlogger)
                        .length > 0
                },

                canApplyTemplateEdit() {
                    return !!this.editData.id_katlogger && this.templateRowsByKategori(this.editData
                            .id_katlogger)
                        .length > 0
                },

                templateRowsByKategori(kategoriId) {
                    if (!kategoriId) return []
                    return this.templateMap[String(kategoriId)] || []
                },

                normalizeSensorOption(sensor, options) {
                    const value = String(sensor || '').trim()
                    if (!value) return ''
                    return options.includes(value) ? value : ''
                },

                applyListParameterToParamRow(param, sensorOptions = []) {
                    if (!param) return

                    const selectedId = String(param.list_parameter_id || '').trim()
                    if (!selectedId) return

                    const selected = (this.listParameterOptions || []).find((item) => String(item
                            .id) ===
                        selectedId)
                    if (!selected) return
                    const np = (selected.nama_parameter || '').toString()
                    param.nama_parameter = np ? np.replaceAll('_', ' ') : (param.nama_parameter || '')

                    const pu = (selected.parameter_utama || '').toString()
                    if (pu) param.parameter_utama = pu.replaceAll('_', ' ')

                    if (selected.default_satuan) {
                        param.satuan = selected.default_satuan
                    }

                    const sensor = this.normalizeSensorOption(selected.default_kolom_sensor,
                        sensorOptions)
                    if (sensor) {
                        param.kolom_sensor = sensor
                    }

                    param.parameter_group_id = selected.default_parameter_group_id || ''
                },

                applyTemplateToAdd() {
                    const templateRows = this.templateRowsByKategori(this.addData.id_katlogger)
                    if (!templateRows.length) return

                    const mapped = templateRows.map((row) => ({
                        list_parameter_id: row.list_parameter_id ? String(row
                            .list_parameter_id) : '',
                        nama_parameter: row.nama_parameter || '',
                        kolom_sensor: this.normalizeSensorOption(row.kolom_sensor_default,
                            this.addSensorOptions),
                        satuan: row.satuan || '',
                        parameter_group_id: row.parameter_group_id || '',
                    }))

                    this.addData.params = mapped.length ? mapped : [{
                        list_parameter_id: '',
                        nama_parameter: '',
                        kolom_sensor: '',
                        satuan: '',
                        parameter_group_id: ''
                    }]
                },

                applyTemplateToEdit() {
                    const templateRows = this.templateRowsByKategori(this.editData.id_katlogger)
                    if (!templateRows.length) return

                    const mapped = templateRows.map((row) => ({
                        id_param: '',
                        list_parameter_id: row.list_parameter_id ? String(row
                            .list_parameter_id) : '',
                        nama_parameter: row.nama_parameter || '',
                        kolom_sensor: this.normalizeSensorOption(row.kolom_sensor_default,
                            this.editSensorOptions),
                        satuan: row.satuan || '',
                        parameter_group_id: row.parameter_group_id || '',
                    }))

                    this.editData.params = mapped.length ? mapped : [{
                        id_param: '',
                        list_parameter_id: '',
                        nama_parameter: '',
                        kolom_sensor: '',
                        satuan: '',
                        parameter_group_id: ''
                    }]
                }
            }))
        })
    </script>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endsection
