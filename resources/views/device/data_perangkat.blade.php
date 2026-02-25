@extends('layouts.app')

@section('content')
    <div class="space-y-3" x-data="dataPerangkat">

        <div class="flex items-center justify-between mt-2">
            <div class="flex flex-col sm:flex-row items-center gap-3">
                @if (session('success'))
                    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
                        class="px-4 py-2 bg-emerald-100 text-emerald-700 rounded-lg text-sm font-semibold shadow-sm ring-1 ring-emerald-200">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="w-full sm:w-auto flex items-center gap-3">
                    <div class="relative w-full">
                        <input type="text" x-model="searchQuery" placeholder="Cari perangkat..."
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
                    <thead class="bg-neutral-200 text-xs font-bold text-neutral-950 uppercase">
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
                        <template x-for="(device, index) in filteredDevices()" :key="index">
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 text-center" x-text="index + 1"></td>
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

            </div>
        </div>
        <div x-show="showDetailModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="detail-modal-title" role="dialog" aria-modal="true">
            <div class="flex min-h-screen items-end justify-center px-4 pb-20 pt-4 text-center sm:block sm:p-0">
                <div x-show="showDetailModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/40 transition-opacity"
                    aria-hidden="true" @click="closeModal()"></div>

                <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>

                <div x-show="showDetailModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="relative inline-block w-full max-w-4xl transform overflow-hidden rounded-2xl bg-slate-100 text-left align-bottom shadow-xl transition-all sm:my-8 sm:align-middle">

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

                    <div class="space-y-4 px-4 py-5 sm:px-6">
                        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white">
                            <div class="flex items-center gap-2 border-b border-slate-200 px-4 py-3">
                                <img src="{{ asset('icons/identitas_icon.svg') }}" class="h-5 w-5">
                                <p class="text-base font-bold text-slate-900">Identitas Perangkat</p>
                            </div>
                            <div class="grid grid-cols-1 gap-4 px-4 py-4 sm:grid-cols-3 sm:gap-6">
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
                            <div class="grid grid-cols-1 gap-4 px-4 py-4 sm:grid-cols-4 sm:gap-6">
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
                            <div class="grid grid-cols-1 gap-4 px-4 py-4 sm:grid-cols-2 sm:gap-6">
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


        <div x-show="showCreateModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex min-h-screen items-end justify-center px-4 pb-20 pt-4 text-center sm:block sm:p-0">
                <div x-show="showCreateModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                    aria-hidden="true" @click="closeCreateModal()"></div>

                <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>

                <div x-show="showCreateModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="relative inline-block overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl sm:align-middle">


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
                                        <select id="create_kategori" name="id_katlogger" required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                                            <option value="">Pilih Kategori</option>
                                            @foreach ($kategoris as $kategori)
                                                <option value="{{ $kategori->id_katlogger }}">
                                                    {{ $kategori->nama_kategori }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label for="create_instansi"
                                            class="block text-xs font-medium text-gray-700">Instansi
                                        </label>
                                        <select id="create_instansi" name="instansi_id" required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                                            <option value="">Pilih Instansi</option>
                                            @foreach ($instansis as $instansi)
                                                <option value="{{ $instansi->id }}">
                                                    {{ $instansi->nama }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 mt-4">
                                    <div>
                                        <label for="create_jumlah_sensor" class="block text-xs font-medium text-gray-700">
                                            Jumlah Sensor</label>
                                        <select name="jumlah_sensor" id="create_jumlah_sensor" required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                                            <option value="">Pilih Sensor</option>
                                            <option value="16">16 Sensor</option>
                                            <option value="19">19 Sensor</option>
                                        </select>
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
                        <div class="bg-gray-50 px-6 py-4 flex flex-row-reverse gap-3">
                            <button type="submit"
                                class="inline-flex w-full justify-center rounded-md border border-transparent bg-indigo-900 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm">
                                Simpan
                            </button>
                            <button type="button" @click="closeCreateModal()"
                                class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>


        <div x-show="showEditModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex min-h-screen items-end justify-center px-4 pb-20 pt-4 text-center sm:block sm:p-0">
                <div x-show="showEditModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                    aria-hidden="true" @click="closeEditModal()"></div>

                <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>

                <div x-show="showEditModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="relative inline-block overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl sm:align-middle">


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
                                        <select id="edit_kategori" name="id_katlogger" x-model="formData.id_katlogger"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                                            <option value="">Pilih Kategori</option>
                                            @foreach ($kategoris as $kategori)
                                                <option value="{{ $kategori->id_katlogger }}">
                                                    {{ $kategori->nama_kategori }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label for="edit_instansi"
                                            class="block text-xs font-medium text-gray-700">Instansi</label>
                                        <select id="edit_instansi" name="instansi_id" x-model="formData.instansi_id"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                                            <option value="">Pilih Instansi</option>
                                            @foreach ($instansis as $instansi)
                                                <option value="{{ $instansi->id }}">
                                                    {{ $instansi->nama }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 mt-4">
                                    <div>
                                        <label for="edit_jumlah_sensor" class="block text-xs font-medium text-gray-700">
                                            Jumlah Sensor</label>
                                        <select name="jumlah_sensor" id="edit_jumlah_sensor"
                                            x-model="formData.jumlah_sensor"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                                            <option value="">Pilih Sensor</option>
                                            <option value="16">16 Sensor</option>
                                            <option value="19">19 Sensor</option>
                                        </select>
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
                                                x-model="formData.tanggal_pemasangan"
                                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                                        </div>
                                    </div>
                                    <div>
                                        <label for="masa_garansi" class="block text-xs font-medium text-gray-700">Masa
                                            Garansi</label>
                                        <div class="relative mt-1">
                                            <input type="date" name="masa_garansi" id="masa_garansi"
                                                x-model="formData.masa_garansi"
                                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                                        </div>
                                    </div>
                                    <div>
                                        <label for="awal_kontrak" class="block text-xs font-medium text-gray-700">Awal
                                            kontrak</label>
                                        <div class="relative mt-1">
                                            <input type="date" name="awal_kontrak" id="awal_kontrak"
                                                x-model="formData.awal_kontrak"
                                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="bg-gray-50 px-6 py-4 flex flex-row-reverse gap-3">
                            <button type="submit"
                                class="inline-flex w-full justify-center rounded-md border border-transparent bg-indigo-900 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm">
                                Simpan
                            </button>
                            <button type="button" @click="closeEditModal()"
                                class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
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
                allDevices: @json($devices),
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

                filteredDevices() {
                    const q = (this.searchQuery || '').trim();
                    if (!q) return this.allDevices;

                    // Fuzzy on name/text fields
                    const fuse = new Fuse(this.allDevices, {
                        threshold: 0.7,
                        keys: ['nama_logger', 'instansi', 'nama_penjaga']
                    });
                    const fuzzyResults = fuse.search(q).map(r => r.item);

                    // Exact on code/ID fields
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
                        if (seen.has(d.id)) return false;
                        seen.add(d.id);
                        return true;
                    });
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
                },

                closeCreateModal() {
                    this.showCreateModal = false;
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
                },

                closeEditModal() {
                    this.showEditModal = false;
                }
            }));
        });
    </script>
@endsection
