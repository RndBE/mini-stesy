@extends('layouts.app')

@push('head')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .flatpickr-calendar { font-size: 14px; }
        .flatpickr-day.selected, .flatpickr-day.selected:hover { background: #3730a3; border-color: #3730a3; }
        .flatpickr-day:hover { background: #e0e7ff; }
    </style>
@endpush

@section('content')
    <div class="space-y-3" x-data="dataPerangkat">

        <div class="flex items-center justify-end mt-2">
            <div class="flex flex-col sm:flex-row items-center gap-3">
                @if (session('success'))
                    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
                        class="px-4 py-2 bg-emerald-100 text-emerald-700 rounded-lg text-sm font-semibold shadow-sm ring-1 ring-emerald-200">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="w-full sm:w-auto flex items-center gap-3">
                    <div class="relative w-full sm:w-64">
                        <input type="text" x-model="searchQuery" @input="currentPage = 1"
                            placeholder="Cari perangkat..."
                            class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        <svg class="absolute right-3 top-2.5 h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    @permission('manage_data_perangkat')
                        <button @click="openCreateModal()"
                            class="inline-flex items-center gap-2 rounded-lg bg-blue-900 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800 whitespace-nowrap">
                            + Tambah Data
                        </button>
                    @endpermission
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-slate-200">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600 whitespace-nowrap">
                    <thead class="bg-neutral-200 text-xs font-semibold text-neutral-950 uppercase">
                        <tr>
                            <th scope="col" class="px-6 py-4">No</th>
                            <th scope="col" class="px-6 py-4">ID Logger</th>
                            <th scope="col" class="px-6 py-4">Nama Perangkat</th>
                            <th scope="col" class="px-6 py-4">Kategori</th>
                            <th scope="col" class="px-6 py-4">Instansi</th>
                            <th scope="col" class="px-6 py-4">Seri</th>
                            <th scope="col" class="px-6 py-4">Serial Number</th>
                            <th scope="col" class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        <template x-for="(device, index) in paginatedDevices()" :key="device.id_logger || index">
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 text-center" x-text="rowNumber(index)"></td>
                                <td class="px-6 py-4 text-slate-900" x-text="device.id_logger || '-'"></td>
                                <td class="px-6 py-4 font-medium text-slate-900" x-text="device.nama_logger || '-'"></td>
                                <td class="px-6 py-4" x-text="device.kategori"></td>
                                <td class="px-6 py-4" x-text="device.instansi"></td>
                                <td class="px-6 py-4" x-text="device.seri"></td>
                                <td class="px-6 py-4 font-mono text-xs" x-text="device.serial_number"></td>
                                <td class="px-6 py-4 text-center">
                                    @permission('manage_data_perangkat')
                                        <button @click="openModal(device)" title="Detail Device"
                                            class="group rounded-lg p-2 bg-emerald-100 hover:bg-emerald-100 transition-colors">
                                            <img src="{{ asset('icons/detail_icon.svg') }}"
                                                class="h-5 w-5 transition duration-200 ease-out filter hover:invert hover:sepia hover:saturate-[700%] hover:hue-rotate-[85deg] hover:brightness-95"
                                                alt="Detail">
                                        </button>
                                        <button @click="openEditModal(device)"
                                            class="rounded-lg p-2 bg-blue-100 hover:bg-blue-200 transition-colors">
                                            <img src="{{ asset('icons/edit_icon.svg') }}"
                                                class="h-5 w-5 transition duration-200 ease-out filter hover:invert hover:sepia hover:saturate-[500%] hover:hue-rotate-[190deg] hover:brightness-90"
                                                alt="Edit">
                                        </button>
                                    @else
                                        <span class="text-xs bg-slate-100 text-slate-950">-</span>
                                    @endpermission
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <template x-if="filteredDevices().length === 0">
                <div class="p-6 text-center text-slate-500">
                    Tidak ada data perangkat.
                </div>
            </template>

            <div class="border-t border-slate-200 bg-white px-4 py-3 sm:px-6">
                <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                    <p class="text-xs text-slate-500 sm:text-sm">
                        Menampilkan
                        <span class="font-semibold text-slate-700" x-text="paginationStart()"></span>
                        –
                        <span class="font-semibold text-slate-700" x-text="paginationEnd()"></span>
                        dari
                        <span class="font-semibold text-slate-700" x-text="paginationTotal()"></span>
                        data
                    </p>

                    <div
                        class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between xl:justify-end">
                        <div
                            class="flex items-center gap-2 shrink-0 whitespace-nowrap text-xs font-medium text-slate-600 sm:text-sm">
                            <span>Tampilkan</span>
                            <span class="relative inline-block shrink-0" x-data="{ perPageOpen: false }"
                                @click.outside="perPageOpen = false"
                                @keydown.escape.window="perPageOpen = false">
                                <button type="button" @click="perPageOpen = !perPageOpen"
                                    :aria-expanded="perPageOpen" aria-haspopup="listbox"
                                    class="inline-flex h-10 w-14 items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                                    <span x-text="perPage"></span>
                                    <svg class="h-3.5 w-3.5 text-slate-400 transition-transform duration-200"
                                        :class="perPageOpen ? 'rotate-180' : ''"
                                        viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd"
                                        d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z"
                                        clip-rule="evenodd" />
                                    </svg>
                                </button>

                                <div x-show="perPageOpen" style="display: none;"
                                    x-transition:enter="transition ease-out duration-100"
                                    x-transition:enter-start="opacity-0 translate-y-1"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    x-transition:leave="transition ease-in duration-75"
                                    x-transition:leave-start="opacity-100 translate-y-0"
                                    x-transition:leave-end="opacity-0 translate-y-1"
                                    class="absolute bottom-full right-0 z-40 mb-1 w-14 overflow-hidden rounded-lg border border-slate-200 bg-white py-1 shadow-lg"
                                    role="listbox">
                                    <template x-for="option in perPageOptions" :key="option">
                                        <button type="button"
                                            @click="perPage = option; currentPage = 1; perPageOpen = false"
                                            :aria-selected="Number(perPage) === Number(option)"
                                            :class="Number(perPage) === Number(option)
                                                ? 'bg-[#303481] text-white'
                                                : 'text-slate-700 hover:bg-slate-50'"
                                            class="block w-full px-3 py-2 text-left text-sm font-medium"
                                            role="option" x-text="option">
                                        </button>
                                    </template>
                                </div>
                            </span>
                        </div>

                        <nav class="flex items-center justify-between gap-1" aria-label="Pagination Data Perangkat">
                            <button type="button" @click="previousPage()" :disabled="currentPage <= 1"
                                class="min-h-10 rounded-lg border border-slate-300 bg-white px-3 text-xs font-semibold text-slate-600 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40 sm:text-sm">
                                Sebelumnya
                            </button>

                            <div class="hidden items-center gap-1 sm:flex">
                                <template x-for="page in paginationPages()" :key="page">
                                    <button type="button" @click="goToPage(page)" x-text="page"
                                        :aria-current="page === currentPage ? 'page' : null"
                                        :class="page === currentPage
                                            ? 'border-[#303481] bg-[#303481] text-white'
                                            : 'border-slate-300 bg-white text-slate-600 hover:bg-slate-50'"
                                        class="h-10 min-w-10 rounded-lg border px-2 text-sm font-semibold">
                                    </button>
                                </template>
                            </div>

                            <span class="px-2 text-xs font-semibold text-slate-500 sm:hidden"
                                x-text="`${currentPage} / ${totalPages()}`"></span>

                            <button type="button" @click="nextPage()" :disabled="currentPage >= totalPages()"
                                class="min-h-10 rounded-lg border border-slate-300 bg-white px-3 text-xs font-semibold text-slate-600 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40 sm:text-sm">
                                Selanjutnya
                            </button>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
        <div x-show="showDetailModal" style="display: none;" class="fixed inset-0 z-[500]"
            aria-labelledby="detail-modal-title" role="dialog" aria-modal="true">
            <div x-show="showDetailModal" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/40 transition-opacity"
                aria-hidden="true" @click="closeModal()"></div>

            <div class="fixed inset-0 flex items-center justify-center p-4 overflow-y-auto" @click="closeModal()">

                <div x-show="showDetailModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                    class="relative w-full max-w-4xl overflow-hidden rounded-2xl bg-slate-100 text-left shadow-xl my-2 sm:my-4" @click.stop>

                    <div class="flex items-start justify-between border-b border-slate-200 px-6 py-5">
                        <div class="flex items-start gap-3">
                            <div class="rounded-lg border border-slate-200 bg-white p-2 text-slate-700">
                                <img src="{{ asset('icons/detail_dark_icon.svg') }}" class="h-5 w-5">
                            </div>
                            <div>
                                <h3 id="detail-modal-title" class="text-xl font-bold text-slate-900">Detail Perangkat</h3>
                                <p class="text-sm text-slate-500">Informasi lengkap tentang perangkat.</p>
                            </div>
                        </div>
                        <button @click="closeModal()" class="text-slate-600 hover:text-slate-900">
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
                                <p class="text-base font-bold text-slate-900">Identitas Perangkat</p>
                            </div>
                            <div class="grid grid-cols-1 gap-3 px-4 py-4 sm:grid-cols-3 sm:gap-6">
                                <div>
                                    <p class="text-xs uppercase tracking-wide text-slate-400">Nama Perangkat</p>
                                    <p class="mt-1 text-sm font-bold text-slate-900"
                                        x-text="detailData.nama_logger || '-'"></p>
                                </div>
                                <div>
                                    <p class="text-xs uppercase tracking-wide text-slate-400">Seri Perangkat</p>
                                    <p class="mt-1 text-sm font-bold text-slate-900" x-text="detailData.seri || '-'"></p>
                                </div>
                                <div>
                                    <p class="text-xs uppercase tracking-wide text-slate-400">Serial Number</p>
                                    <p class="mt-1 text-sm font-bold text-slate-900 break-all"
                                        x-text="detailData.serial_number || '-'"></p>
                                </div>
                            </div>
                        </div>

                        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white">
                            <div class="flex items-center gap-2 border-b border-slate-200 px-4 py-3">
                                <img src="{{ asset('icons/gear_icon.svg') }}" class="h-5 w-5">
                                <p class="text-base font-bold text-slate-900">Informasi Operasional</p>
                            </div>
                            <div class="grid grid-cols-2 gap-3 px-4 py-4 sm:grid-cols-4 sm:gap-6">
                                <div>
                                    <p class="text-xs uppercase tracking-wide text-slate-400">Sensor</p>
                                    <p class="mt-1 text-sm font-bold text-slate-900"
                                        x-text="detailData.sensor_type || '-'"></p>
                                </div>
                                <div>
                                    <p class="text-xs uppercase tracking-wide text-slate-400">IMEI</p>
                                    <p class="mt-1 text-sm font-bold text-slate-900" x-text="detailData.imei || '-'"></p>
                                </div>
                                <div>
                                    <p class="text-xs uppercase tracking-wide text-slate-400">Tanggal Pemasangan</p>
                                    <p class="mt-1 text-sm font-bold text-slate-900"
                                        x-text="formatDate(detailData.tanggal_pemasangan)"></p>
                                </div>
                                <div>
                                    <p class="text-xs uppercase tracking-wide text-slate-400">Masa Garansi</p>
                                    <p class="mt-1 text-sm font-bold text-slate-900"
                                        x-text="formatDate(detailData.masa_garansi)"></p>
                                </div>
                            </div>
                        </div>

                        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white">
                            <div class="flex items-center gap-2 border-b border-slate-200 px-4 py-3">
                                <img src="{{ asset('icons/penjaga_icon.svg') }}" class="h-5 w-5">
                                <p class="text-base font-bold text-slate-900">Penjaga</p>
                            </div>
                            <div class="grid grid-cols-2 gap-3 px-4 py-4 sm:grid-cols-2 sm:gap-6">
                                <div>
                                    <p class="text-xs uppercase tracking-wide text-slate-400">Nama</p>
                                    <p class="mt-1 text-sm font-bold text-slate-900"
                                        x-text="detailData.nama_penjaga || '-'"></p>
                                </div>
                                <div>
                                    <p class="text-xs uppercase tracking-wide text-slate-400">Nomor HP</p>
                                    <p class="mt-1 text-sm font-bold text-slate-900" x-text="detailData.no_hp || '-'"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div x-show="showCreateModal" style="display: none;" class="fixed inset-0 z-[500]"
            aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div x-show="showCreateModal" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                aria-hidden="true" @click="closeCreateModal()"></div>

            <div class="fixed inset-0 flex items-center justify-center p-4 overflow-y-auto" @click="closeCreateModal()">

                <div x-show="showCreateModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                    class="relative w-full max-w-2xl overflow-hidden rounded-lg bg-white text-left shadow-xl my-4" @click.stop>


                    <div class="flex items-center justify-between border-b px-6 py-4">
                        <h3 class="text-lg font-bold text-gray-900" id="modal-title">Tambah Data Perangkat</h3>
                        <button @click="closeCreateModal()" class="text-gray-400 hover:text-gray-500">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form action="{{ route('device.storeDataPerangkat') }}" method="POST">
                        @csrf
                        <div class="px-6 py-4 space-y-6 max-h-[70vh] overflow-y-auto">


                            <div class="bg-slate-50 p-4 rounded-lg border border-slate-100">
                                <h4 class="text-sm font-semibold text-gray-900 mb-4 ">Identitas Perangkat</h4>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div>
                                        <label for="create_id_logger" class="block text-xs font-medium text-gray-700">ID
                                            Logger</label>
                                        <input type="text" name="id_logger" id="create_id_logger"
                                            x-model="formData.id_logger"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border"
                                            placeholder="Contoh: 10001">
                                    </div>
                                    <div>
                                        <label for="create_nama_logger"
                                            class="block text-xs font-medium text-gray-700">Nama
                                            Perangkat</label>
                                        <input type="text" name="nama_logger" id="create_nama_logger" required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                                    </div>
                                    <div>
                                        <label for="create_kategori"
                                            class="block text-xs font-medium text-gray-700">Kategori
                                            Perangkat</label>
<select class="hidden sm:block mt-1 w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm p-2" x-model="formData.id_katlogger">
                                            <option value="">Pilih Kategori</option>
                                            @foreach ($kategoris as $kategori)
                                                <option value="{{ $kategori->id_katlogger }}">{{ $kategori->nama_kategori }}</option>
                                            @endforeach
                                        </select>
<div class="sm:hidden mt-1 relative" x-data="{ openCKat: false }">
                                            <button type="button" @click="openCKat = !openCKat"
                                                class="w-full flex items-center justify-between rounded-md border border-gray-300 px-3 py-2 bg-white text-sm text-left focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                                :class="formData.id_katlogger ? 'text-gray-900' : 'text-gray-400'">
                                                <span class="truncate" x-text="kategoris.find(k => String(k.id_katlogger) === String(formData.id_katlogger))?.nama_kategori || 'Pilih Kategori'"></span>
                                                <svg class="w-4 h-4 text-gray-400 flex-shrink-0 transition-transform" :class="openCKat ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                            </button>
                                            <div x-show="openCKat" @click.outside="openCKat = false"
                                                class="w-full mt-1 bg-white border border-gray-200 rounded-md shadow max-h-44 overflow-y-auto">
                                                <div @click="formData.id_katlogger = ''; openCKat = false"
                                                    class="px-3 py-2 text-sm text-gray-400 hover:bg-slate-50 cursor-pointer">Pilih Kategori</div>
                                                <template x-for="k in kategoris" :key="k.id_katlogger">
                                                    <div @click="formData.id_katlogger = String(k.id_katlogger); openCKat = false"
                                                        :class="String(formData.id_katlogger) === String(k.id_katlogger) ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-900 hover:bg-slate-50'"
                                                        class="px-3 py-2 text-sm cursor-pointer truncate"
                                                        x-text="k.nama_kategori"></div>
                                                </template>
                                            </div>
                                        </div>
                                        <input type="hidden" name="id_katlogger" :value="formData.id_katlogger">
                                    </div>
                                    <div>
                                        <label for="create_instansi"
                                            class="block text-xs font-medium text-gray-700">Instansi
                                        </label>
<select class="hidden sm:block mt-1 w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm p-2" x-model="formData.instansi_id">
                                            <option value="">Pilih Instansi</option>
                                            @foreach ($instansis as $instansi)
                                                <option value="{{ $instansi->id }}">{{ $instansi->nama }}</option>
                                            @endforeach
                                        </select>
<div class="sm:hidden mt-1 relative" x-data="{ openCInst: false }">
                                            <button type="button" @click="openCInst = !openCInst"
                                                class="w-full flex items-center justify-between rounded-md border border-gray-300 px-3 py-2 bg-white text-sm text-left focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                                :class="formData.instansi_id ? 'text-gray-900' : 'text-gray-400'">
                                                <span class="truncate" x-text="instansis.find(i => String(i.id) === String(formData.instansi_id))?.nama || 'Pilih Instansi'"></span>
                                                <svg class="w-4 h-4 text-gray-400 flex-shrink-0 transition-transform" :class="openCInst ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                            </button>
                                            <div x-show="openCInst" @click.outside="openCInst = false"
                                                class="w-full mt-1 bg-white border border-gray-200 rounded-md shadow max-h-44 overflow-y-auto">
                                                <div @click="formData.instansi_id = ''; openCInst = false"
                                                    class="px-3 py-2 text-sm text-gray-400 hover:bg-slate-50 cursor-pointer">Pilih Instansi</div>
                                                <template x-for="inst in instansis" :key="inst.id">
                                                    <div @click="formData.instansi_id = String(inst.id); openCInst = false"
                                                        :class="String(formData.instansi_id) === String(inst.id) ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-900 hover:bg-slate-50'"
                                                        class="px-3 py-2 text-sm cursor-pointer truncate"
                                                        x-text="inst.nama"></div>
                                                </template>
                                            </div>
                                        </div>
                                        <input type="hidden" name="instansi_id" :value="formData.instansi_id">
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 mt-4">
                                    <div>
                                        <label for="create_jumlah_sensor" class="block text-xs font-medium text-gray-700">
                                            Jumlah Sensor</label>
<select class="hidden sm:block mt-1 w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm p-2" x-model="formData.jumlah_sensor">
                                            <option value="">Pilih Sensor</option>
                                            <option value="16">16 Sensor</option>
                                            <option value="19">19 Sensor</option>
                                            <option value="50">50 Sensor</option>
                                        </select>
<div class="sm:hidden mt-1 relative" x-data="{ openCSensor: false }">
                                            <button type="button" @click="openCSensor = !openCSensor"
                                                class="w-full flex items-center justify-between rounded-md border border-gray-300 px-3 py-2 bg-white text-sm text-left focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                                :class="formData.jumlah_sensor ? 'text-gray-900' : 'text-gray-400'">
                                                <span x-text="formData.jumlah_sensor ? formData.jumlah_sensor + ' Sensor' : 'Pilih Sensor'"></span>
                                                <svg class="w-4 h-4 text-gray-400 flex-shrink-0 transition-transform" :class="openCSensor ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                            </button>
                                            <div x-show="openCSensor" @click.outside="openCSensor = false"
                                                class="w-full mt-1 bg-white border border-gray-200 rounded-md shadow">
                                                <div @click="formData.jumlah_sensor = ''; openCSensor = false"
                                                    class="px-3 py-2 text-sm text-gray-400 hover:bg-slate-50 cursor-pointer">Pilih Sensor</div>
                                                <template x-for="s in sensorOptions" :key="s">
                                                    <div @click="formData.jumlah_sensor = s; openCSensor = false"
                                                        :class="formData.jumlah_sensor === s ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-900 hover:bg-slate-50'"
                                                        class="px-3 py-2 text-sm cursor-pointer"
                                                        x-text="s + ' Sensor'"></div>
                                                </template>
                                            </div>
                                        </div>
                                        <input type="hidden" name="jumlah_sensor" :value="formData.jumlah_sensor">
                                    </div>
                                    <div>
                                        <label for="create_seri" class="block text-xs font-medium text-gray-700">Seri
                                            Perangkat</label>
                                        <input type="text" name="seri" id="create_seri"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                                    </div>
                                    <div>
                                        <label for="create_serial_number"
                                            class="block text-xs font-medium text-gray-700">Serial
                                            Number</label>
                                        <input type="text" name="serial_number" id="create_serial_number"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                                    </div>
                                </div>
                            </div>


                            <div class="bg-slate-50 p-4 rounded-lg border border-slate-100">
                                <h4 class="text-sm font-semibold text-gray-900 mb-4">Informasi Operasional</h4>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div>
                                        <label for="create_sensor_type"
                                            class="block text-xs font-medium text-gray-700">Sensor</label>
                                        <input type="text" name="sensor_type" id="create_sensor_type"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                                    </div>
                                    <div>
                                        <label for="create_imei"
                                            class="block text-xs font-medium text-gray-700">IMEI</label>
                                        <input type="text" name="imei" id="create_imei"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                                    </div>
                                    <div>
                                        <label for="create_nama_penjaga"
                                            class="block text-xs font-medium text-gray-700">Nama
                                            Penjaga</label>
                                        <input type="text" name="nama_penjaga" id="create_nama_penjaga"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                                    </div>
                                    <div>
                                        <label for="create_no_hp" class="block text-xs font-medium text-gray-700">No
                                            HP</label>
                                        <input type="text" name="no_hp" id="create_no_hp"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 mt-4">
                                    <div>
                                        <label for="create_tanggal_pemasangan"
                                            class="block text-xs font-medium text-gray-700">Tanggal Pemasangan</label>
                                        <div class="relative mt-1">
                                            <input type="date" name="tanggal_pemasangan"
                                                id="create_tanggal_pemasangan"
                                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                                        </div>
                                    </div>
                                    <div>
                                        <label for="create_masa_garansi"
                                            class="block text-xs font-medium text-gray-700">Masa
                                            Garansi</label>
                                        <div class="relative mt-1">
                                            <input type="date" name="masa_garansi" id="create_masa_garansi"
                                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                                        </div>
                                    </div>
                                    <div>
                                        <label for="create_awal_kontrak"
                                            class="block text-xs font-medium text-gray-700">Awal Kontrak</label>
                                        <div class="relative mt-1">
                                            <input type="date" name="awal_kontrak" id="create_awal_kontrak"
                                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3">
                            <button type="button" @click="closeCreateModal()"
                                class="h-11 sm:h-auto flex-1 sm:flex-none inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                Batal
                            </button>
                            <button type="submit"
                                class="h-11 sm:h-auto flex-1 sm:flex-none inline-flex justify-center rounded-md border border-transparent bg-indigo-900 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>


        <div x-show="showEditModal" style="display: none;" class="fixed inset-0 z-[500]"
            aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div x-show="showEditModal" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                aria-hidden="true" @click="closeEditModal()"></div>

            <div class="fixed inset-0 flex items-center justify-center p-4 overflow-y-auto" @click="closeEditModal()">

                <div x-show="showEditModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                    class="relative w-full max-w-2xl overflow-hidden rounded-lg bg-white text-left shadow-xl my-4" @click.stop>


                    <div class="flex items-center justify-between border-b px-6 py-4">
                        <h3 class="text-lg font-bold text-gray-900" id="modal-title">Edit Data Perangkat</h3>
                        <button @click="closeEditModal()" class="text-gray-400 hover:text-gray-500">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form :action="updateUrl" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="px-6 py-4 space-y-6 max-h-[70vh] overflow-y-auto">


                            <div class="bg-slate-50 p-4 rounded-lg border border-slate-100">
                                <h4 class="text-sm font-semibold text-gray-900 mb-4 ">Identitas Perangkat</h4>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div>
                                        <label for="edit_id_logger" class="block text-xs font-medium text-gray-700">
                                            ID Logger
                                        </label>
                                        <input type="text" id="edit_id_logger" x-model="formData.id_logger" readonly
                                            class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 text-gray-600 shadow-sm sm:text-sm p-2 border cursor-not-allowed">
                                        <input type="hidden" name="id_logger" :value="formData.id_logger">
                                    </div>
                                    <div>
                                        <label for="edit_nama_logger" class="block text-xs font-medium text-gray-700">Nama
                                            Perangkat</label>
                                        <input type="text" name="nama_logger" id="edit_nama_logger"
                                            x-model="formData.nama_logger"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                                    </div>
                                    <div>
                                        <label for="edit_kategori"
                                            class="block text-xs font-medium text-gray-700">Kategori
                                            Perangkat</label>
<select class="hidden sm:block mt-1 w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm p-2" x-model="formData.id_katlogger">
                                            <option value="">Pilih Kategori</option>
                                            @foreach ($kategoris as $kategori)
                                                <option value="{{ $kategori->id_katlogger }}">{{ $kategori->nama_kategori }}</option>
                                            @endforeach
                                        </select>
<div class="sm:hidden mt-1 relative" x-data="{ openEKat: false }">
                                            <button type="button" @click="openEKat = !openEKat"
                                                class="w-full flex items-center justify-between rounded-md border border-gray-300 px-3 py-2 bg-white text-sm text-left focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                                :class="formData.id_katlogger ? 'text-gray-900' : 'text-gray-400'">
                                                <span class="truncate" x-text="kategoris.find(k => String(k.id_katlogger) === String(formData.id_katlogger))?.nama_kategori || 'Pilih Kategori'"></span>
                                                <svg class="w-4 h-4 text-gray-400 flex-shrink-0 transition-transform" :class="openEKat ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                            </button>
                                            <div x-show="openEKat" @click.outside="openEKat = false"
                                                class="w-full mt-1 bg-white border border-gray-200 rounded-md shadow max-h-44 overflow-y-auto">
                                                <div @click="formData.id_katlogger = ''; openEKat = false"
                                                    class="px-3 py-2 text-sm text-gray-400 hover:bg-slate-50 cursor-pointer">Pilih Kategori</div>
                                                <template x-for="k in kategoris" :key="k.id_katlogger">
                                                    <div @click="formData.id_katlogger = String(k.id_katlogger); openEKat = false"
                                                        :class="String(formData.id_katlogger) === String(k.id_katlogger) ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-900 hover:bg-slate-50'"
                                                        class="px-3 py-2 text-sm cursor-pointer truncate"
                                                        x-text="k.nama_kategori"></div>
                                                </template>
                                            </div>
                                        </div>
                                        <input type="hidden" name="id_katlogger" :value="formData.id_katlogger">
                                    </div>
                                    <div>
                                        <label for="edit_instansi"
                                            class="block text-xs font-medium text-gray-700">Instansi</label>
<select class="hidden sm:block mt-1 w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm p-2" x-model="formData.instansi_id">
                                            <option value="">Pilih Instansi</option>
                                            @foreach ($instansis as $instansi)
                                                <option value="{{ $instansi->id }}">{{ $instansi->nama }}</option>
                                            @endforeach
                                        </select>
<div class="sm:hidden mt-1 relative" x-data="{ openEInst: false }">
                                            <button type="button" @click="openEInst = !openEInst"
                                                class="w-full flex items-center justify-between rounded-md border border-gray-300 px-3 py-2 bg-white text-sm text-left focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                                :class="formData.instansi_id ? 'text-gray-900' : 'text-gray-400'">
                                                <span class="truncate" x-text="instansis.find(i => String(i.id) === String(formData.instansi_id))?.nama || 'Pilih Instansi'"></span>
                                                <svg class="w-4 h-4 text-gray-400 flex-shrink-0 transition-transform" :class="openEInst ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                            </button>
                                            <div x-show="openEInst" @click.outside="openEInst = false"
                                                class="w-full mt-1 bg-white border border-gray-200 rounded-md shadow max-h-44 overflow-y-auto">
                                                <div @click="formData.instansi_id = ''; openEInst = false"
                                                    class="px-3 py-2 text-sm text-gray-400 hover:bg-slate-50 cursor-pointer">Pilih Instansi</div>
                                                <template x-for="inst in instansis" :key="inst.id">
                                                    <div @click="formData.instansi_id = String(inst.id); openEInst = false"
                                                        :class="String(formData.instansi_id) === String(inst.id) ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-900 hover:bg-slate-50'"
                                                        class="px-3 py-2 text-sm cursor-pointer truncate"
                                                        x-text="inst.nama"></div>
                                                </template>
                                            </div>
                                        </div>
                                        <input type="hidden" name="instansi_id" :value="formData.instansi_id">
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 mt-4">
                                    <div>
                                        <label for="edit_jumlah_sensor" class="block text-xs font-medium text-gray-700">
                                            Jumlah Sensor</label>
<select class="hidden sm:block mt-1 w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm p-2" x-model="formData.jumlah_sensor">
                                            <option value="">Pilih Sensor</option>
                                            <option value="16">16 Sensor</option>
                                            <option value="19">19 Sensor</option>
                                            <option value="50">50 Sensor</option>
                                        </select>
<div class="sm:hidden mt-1 relative" x-data="{ openESensor: false }">
                                            <button type="button" @click="openESensor = !openESensor"
                                                class="w-full flex items-center justify-between rounded-md border border-gray-300 px-3 py-2 bg-white text-sm text-left focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                                :class="formData.jumlah_sensor ? 'text-gray-900' : 'text-gray-400'">
                                                <span x-text="formData.jumlah_sensor ? formData.jumlah_sensor + ' Sensor' : 'Pilih Sensor'"></span>
                                                <svg class="w-4 h-4 text-gray-400 flex-shrink-0 transition-transform" :class="openESensor ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                            </button>
                                            <div x-show="openESensor" @click.outside="openESensor = false"
                                                class="w-full mt-1 bg-white border border-gray-200 rounded-md shadow">
                                                <div @click="formData.jumlah_sensor = ''; openESensor = false"
                                                    class="px-3 py-2 text-sm text-gray-400 hover:bg-slate-50 cursor-pointer">Pilih Sensor</div>
                                                <template x-for="s in sensorOptions" :key="s">
                                                    <div @click="formData.jumlah_sensor = s; openESensor = false"
                                                        :class="String(formData.jumlah_sensor) === s ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-900 hover:bg-slate-50'"
                                                        class="px-3 py-2 text-sm cursor-pointer"
                                                        x-text="s + ' Sensor'"></div>
                                                </template>
                                            </div>
                                        </div>
                                        <input type="hidden" name="jumlah_sensor" :value="formData.jumlah_sensor">
                                    </div>
                                    <div>
                                        <label for="edit_seri" class="block text-xs font-medium text-gray-700">Seri
                                            Perangkat</label>
                                        <input type="text" name="seri" id="edit_seri" x-model="formData.seri"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                                    </div>
                                    <div>
                                        <label for="edit_serial_number"
                                            class="block text-xs font-medium text-gray-700">Serial
                                            Number</label>
                                        <input type="text" name="serial_number" id="edit_serial_number"
                                            x-model="formData.serial_number"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                                    </div>
                                </div>
                            </div>


                            <div class="bg-slate-50 p-4 rounded-lg border border-slate-100">
                                <h4 class="text-sm font-semibold text-gray-900 mb-4">Informasi Operasional</h4>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div>
                                        <label for="sensor_type"
                                            class="block text-xs font-medium text-gray-700">Sensor</label>
                                        <input type="text" name="sensor_type" id="sensor_type"
                                            x-model="formData.sensor_type"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                                    </div>
                                    <div>
                                        <label for="imei" class="block text-xs font-medium text-gray-700">IMEI</label>
                                        <input type="text" name="imei" id="imei" x-model="formData.imei"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                                    </div>
                                    <div>
                                        <label for="nama_penjaga" class="block text-xs font-medium text-gray-700">Nama
                                            Penjaga</label>
                                        <input type="text" name="nama_penjaga" id="nama_penjaga"
                                            x-model="formData.nama_penjaga"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                                    </div>
                                    <div>
                                        <label for="no_hp" class="block text-xs font-medium text-gray-700">No
                                            HP</label>
                                        <input type="text" name="no_hp" id="no_hp" x-model="formData.no_hp"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 mt-4">
                                    <div>
                                        <label for="tanggal_pemasangan"
                                            class="block text-xs font-medium text-gray-700">Tanggal Pemasangan</label>
                                        <div class="relative mt-1">
                                            <input type="date" name="tanggal_pemasangan" id="tanggal_pemasangan"
                                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                                        </div>
                                    </div>
                                    <div>
                                        <label for="masa_garansi" class="block text-xs font-medium text-gray-700">Masa
                                            Garansi</label>
                                        <div class="relative mt-1">
                                            <input type="date" name="masa_garansi" id="masa_garansi"
                                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                                        </div>
                                    </div>
                                    <div>
                                        <label for="awal_kontrak" class="block text-xs font-medium text-gray-700">Awal
                                            kontrak</label>
                                        <div class="relative mt-1">
                                            <input type="date" name="awal_kontrak" id="awal_kontrak"
                                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3">
                            <button type="button" @click="closeEditModal()"
                                class="h-11 sm:h-auto flex-1 sm:flex-none inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                Batal
                            </button>
                            <button type="submit"
                                class="h-11 sm:h-auto flex-1 sm:flex-none inline-flex justify-center rounded-md border border-transparent bg-indigo-900 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('dataPerangkat', () => ({
                showDetailModal: false,
                showCreateModal: false,
                showEditModal: false,
                searchQuery: '',
                currentPage: 1,
                perPage: 10,
                perPageOptions: [10, 25, 50],
                allDevices: @json($devices),
                kategoris: @json($kategoris),
                instansis: @json($instansis),
                sensorOptions: ['16', '19', '50'],
                _fpCreate: [],
                _fpEdit: [],
                detailData: {
                    nama_logger: '-',
                    seri: '-',
                    serial_number: '-',
                    sensor_type: '-',
                    imei: '-',
                    tanggal_pemasangan: '-',
                    masa_garansi: '-',
                    nama_penjaga: '-',
                    no_hp: '-',
                },
                formData: {
                    id_logger: '',
                    nama_logger: '',
                    id_katlogger: '',
                    instansi_id: '',
                    seri: '',
                    serial_number: '',
                    sensor_type: '',
                    no_hp: '',
                    tanggal_pemasangan: '',
                    masa_garansi: '',
                    nama_penjaga: '',
                    jumlah_sensor: '',
                    imei: '',
                },
                updateUrl: '',

                _flatpickrOpts(onChangeFn) {
                    return {
                        dateFormat: 'Y-m-d',
                        allowInput: false,
                        disableMobile: true,
                        locale: { firstDayOfWeek: 1 },
                        onChange: onChangeFn,
                    };
                },

                _initFlatpickrCreate() {
                    this._fpCreate.forEach(fp => fp.destroy());
                    this._fpCreate = [];
                    if (window.innerWidth >= 768) return; // desktop/iPad: gunakan native date picker
                    const ids = [
                        { id: 'create_tanggal_pemasangan', field: 'tanggal_pemasangan' },
                        { id: 'create_masa_garansi',       field: 'masa_garansi' },
                        { id: 'create_awal_kontrak',       field: 'awal_kontrak' },
                    ];
                    ids.forEach(({ id, field }) => {
                        const el = document.getElementById(id);
                        if (!el) return;
                        el.type = 'text';
                        el.readOnly = true;
                        el.placeholder = 'Pilih tanggal';
                        const fp = flatpickr(el, this._flatpickrOpts((_, dateStr) => {
                            this.formData[field] = dateStr;
                        }));
                        if (this.formData[field]) fp.setDate(this.formData[field], false);
                        this._fpCreate.push(fp);
                    });
                },

                _initFlatpickrEdit() {
                    this._fpEdit.forEach(fp => fp.destroy());
                    this._fpEdit = [];
                    if (window.innerWidth >= 768) return; // desktop/iPad: gunakan native date picker
                    const ids = [
                        { id: 'tanggal_pemasangan', field: 'tanggal_pemasangan' },
                        { id: 'masa_garansi',       field: 'masa_garansi' },
                        { id: 'awal_kontrak',       field: 'awal_kontrak' },
                    ];
                    ids.forEach(({ id, field }) => {
                        const el = document.getElementById(id);
                        if (!el) return;
                        el.type = 'text';
                        el.readOnly = true;
                        el.placeholder = 'Pilih tanggal';
                        const fp = flatpickr(el, this._flatpickrOpts((_, dateStr) => {
                            this.formData[field] = dateStr;
                        }));
                        if (this.formData[field]) fp.setDate(this.formData[field], false);
                        this._fpEdit.push(fp);
                    });
                },

                filteredDevices() {
                    const q = (this.searchQuery || '').trim();
                    if (!q) return this.allDevices;
                    const fuse = new Fuse(this.allDevices, {
                        threshold: 0.35,
                        keys: ['nama_logger', 'instansi', 'nama_penjaga']
                    });
                    const fuzzyResults = fuse.search(q).map(r => r.item);
                    const ql = q.toLowerCase();
                    const exactResults = this.allDevices.filter(d =>
                        (d.id_logger && d.id_logger.toLowerCase().includes(ql)) ||
                        (d.seri && d.seri.toLowerCase().includes(ql)) ||
                        (d.serial_number && d.serial_number.toLowerCase().includes(ql)) ||
                        (d.no_hp && d.no_hp.toLowerCase().includes(ql)) ||
                        (d.kategori && d.kategori.toLowerCase().includes(ql))
                    );

                    const seen = new Set();
                    return [...fuzzyResults, ...exactResults].filter(d => {
                        if (seen.has(d.id_logger)) return false;
                        seen.add(d.id_logger);
                        return true;
                    });
                },

                paginationTotal() {
                    return this.filteredDevices().length
                },

                totalPages() {
                    return Math.max(1, Math.ceil(this.paginationTotal() / Number(this.perPage || 10)))
                },

                paginatedDevices() {
                    const devices = this.filteredDevices()
                    const lastPage = Math.max(1, Math.ceil(devices.length / Number(this.perPage || 10)))
                    const page = Math.min(Math.max(1, Number(this.currentPage) || 1), lastPage)
                    if (page !== this.currentPage) this.currentPage = page
                    const start = (page - 1) * Number(this.perPage || 10)
                    return devices.slice(start, start + Number(this.perPage || 10))
                },

                paginationStart() {
                    if (!this.paginationTotal()) return 0
                    return ((this.currentPage - 1) * Number(this.perPage || 10)) + 1
                },

                paginationEnd() {
                    return Math.min(
                        this.currentPage * Number(this.perPage || 10),
                        this.paginationTotal()
                    )
                },

                paginationPages() {
                    const total = this.totalPages()
                    if (total <= 5) return Array.from({ length: total }, (_, index) => index + 1)
                    let start = Math.max(1, this.currentPage - 2)
                    let end = Math.min(total, start + 4)
                    start = Math.max(1, end - 4)
                    return Array.from({ length: end - start + 1 }, (_, index) => start + index)
                },

                goToPage(page) {
                    this.currentPage = Math.min(Math.max(1, Number(page) || 1), this.totalPages())
                },

                previousPage() {
                    this.goToPage(this.currentPage - 1)
                },

                nextPage() {
                    this.goToPage(this.currentPage + 1)
                },

                rowNumber(index) {
                    return ((this.currentPage - 1) * Number(this.perPage || 10)) + index + 1
                },

                formatDate(dateString) {
                    if (!dateString || dateString === '-') return '-';

                    const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                    ];

                    try {
                        const date = new Date(dateString);
                        const day = date.getDate();
                        const month = months[date.getMonth()];
                        const year = date.getFullYear();
                        return `${day} ${month} ${year}`;
                    } catch (e) {
                        return dateString;
                    }
                },

                openModal(device) {
                    this.detailData = {
                        nama_logger: device.nama_logger || '-',
                        seri: device.seri || '-',
                        serial_number: device.serial_number || '-',
                        sensor_type: device.sensor_type || '-',
                        imei: device.imei || '-',
                        tanggal_pemasangan: device.tanggal_pemasangan || '-',
                        masa_garansi: device.masa_garansi || '-',
                        nama_penjaga: device.nama_penjaga || '-',
                        no_hp: device.no_hp || '-',
                    };
                    this.showDetailModal = true;
                },

                closeModal() {
                    this.showDetailModal = false;
                },

                openCreateModal() {
                    this.formData = {
                        id_logger: '',
                        nama_logger: '',
                        id_katlogger: '',
                        instansi_id: '',
                        seri: '',
                        serial_number: '',
                        sensor_type: '',
                        no_hp: '',
                        tanggal_pemasangan: '',
                        masa_garansi: '',
                        nama_penjaga: '',
                        jumlah_sensor: '',
                        imei: '',
                        awal_kontrak: '',
                    };
                    this.showCreateModal = true;
                    this.$nextTick(() => this._initFlatpickrCreate());
                },

                closeCreateModal() {
                    this.showCreateModal = false;
                    this._fpCreate.forEach(fp => fp.destroy());
                    this._fpCreate = [];
                    ['create_tanggal_pemasangan', 'create_masa_garansi', 'create_awal_kontrak'].forEach(id => {
                        const el = document.getElementById(id);
                        if (el && el.type === 'text') { el.type = 'date'; el.readOnly = false; el.removeAttribute('placeholder'); }
                    });
                },

                openEditModal(device) {
                    this.formData = {
                        id_logger: device.id_logger,
                        nama_logger: device.nama_logger,
                        jumlah_sensor: device.jumlah_sensor,
                        imei: device.imei,
                        id_katlogger: device.id_katlogger,
                        instansi_id: device.instansi_id || '',
                        seri: device.seri !== '-' ? device.seri : '',
                        serial_number: device.serial_number !== '-' ? device.serial_number : '',
                        sensor_type: device.sensor_type !== '-' ? device.sensor_type : '',
                        no_hp: device.no_hp !== '-' ? device.no_hp : '',
                        tanggal_pemasangan: device.tanggal_pemasangan || '',
                        masa_garansi: device.masa_garansi || '',
                        nama_penjaga: device.nama_penjaga !== '-' ? device.nama_penjaga : '',
                        awal_kontrak: device.awal_kontrak || '',
                    };

                    this.updateUrl = `{{ url('/data-perangkat') }}/${device.id_logger}`;
                    this.showEditModal = true;
                    this.$nextTick(() => this._initFlatpickrEdit());
                },

                closeEditModal() {
                    this.showEditModal = false;
                    this._fpEdit.forEach(fp => fp.destroy());
                    this._fpEdit = [];
                    ['tanggal_pemasangan', 'masa_garansi', 'awal_kontrak'].forEach(id => {
                        const el = document.getElementById(id);
                        if (el && el.type === 'text') { el.type = 'date'; el.readOnly = false; el.removeAttribute('placeholder'); }
                    });
                }
            }));
        });
    </script>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
@endpush

@endsection
