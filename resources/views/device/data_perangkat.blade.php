@extends('layouts.app')

@section('content')
    <div class="space-y-6" x-data="dataPerangkat">

        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Data Perangkat</h1>
                <p class="text-sm text-slate-500">Kelola data perangkat logger yang terpasang</p>
            </div>
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
                        <svg class="absolute right-3 top-2.5 h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <button @click="openCreateModal()"
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-900 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800 whitespace-nowrap">
                        + Tambah Data
                    </button>
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600 whitespace-nowrap">
                    <thead class="bg-neutral-200 text-xs font-bold text-neutral-950 uppercase">
                        <tr>
                            <th scope="col" class="px-6 py-4">No</th>
                            <th scope="col" class="px-6 py-4">ID Logger</th>
                            <th scope="col" class="px-6 py-4">Nama Perangkat</th>
                            <th scope="col" class="px-6 py-4">Kategori</th>
                            <th scope="col" class="px-6 py-4">Seri</th>
                            <th scope="col" class="px-6 py-4">Serial Number</th>
                            <th scope="col" class="px-6 py-4">Sensor</th>
                            <th scope="col" class="px-6 py-4">No HP</th>
                            <th scope="col" class="px-6 py-4">Tanggal Pemasangan</th>
                            <th scope="col" class="px-6 py-4">Masa Garansi</th>
                            <th scope="col" class="px-6 py-4">Nama Penjaga</th>
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
                                <td class="px-6 py-4" x-text="device.seri"></td>
                                <td class="px-6 py-4 font-mono text-xs" x-text="device.serial_number"></td>
                                <td class="px-6 py-4" x-text="device.sensor_type"></td>
                                <td class="px-6 py-4" x-text="device.no_hp"></td>
                                <td class="px-6 py-4" x-text="formatDate(device.tanggal_pemasangan)"></td>
                                <td class="px-6 py-4" x-text="formatDate(device.masa_garansi)"></td>
                                <td class="px-6 py-4" x-text="device.nama_penjaga"></td>
                                <td class="px-6 py-4 text-center">
                                    @permission('manage_data_perangkat')
                                        <button @click="openEditModal(device)"
                                            class="rounded-lg p-2 bg-slate-100 text-slate-950 hover:bg-slate-100 hover:text-indigo-600 transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
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
                {{-- Pagination --}}
            </div>
        </div>

        <!-- Create Modal -->
        <div x-show="showCreateModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex min-h-screen items-end justify-center px-4 pb-20 pt-4 text-center sm:block sm:p-0">
                <div x-show="showCreateModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                    class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"
                    @click="closeCreateModal()"></div>

                <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>

                <div x-show="showCreateModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="relative inline-block overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl sm:align-middle">

                    <!-- Header -->
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

                            <!-- Identitas Perangkat -->
                            <div class="bg-slate-50 p-4 rounded-lg border border-slate-100">
                                <h4 class="text-sm font-semibold text-gray-900 mb-4 ">Identitas Perangkat</h4>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div>
                                        <label for="create_nama_logger" class="block text-xs font-medium text-gray-700">Nama
                                            Perangkat</label>
                                        <input type="text" name="nama_logger" id="create_nama_logger"
                                            required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                                    </div>
                                    <div>
                                        <label for="create_kategori" class="block text-xs font-medium text-gray-700">Kategori
                                            Perangkat</label>
                                        <select id="create_kategori" name="id_katlogger"
                                            required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                                            <option value="">Pilih Kategori</option>
                                            @foreach ($kategoris as $kategori)
                                                <option value="{{ $kategori->id_katlogger }}">
                                                    {{ $kategori->nama_kategori }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label for="create_seri" class="block text-xs font-medium text-gray-700">Seri
                                            Perangkat</label>
                                        <input type="text" name="seri" id="create_seri"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                                    </div>
                                    <div>
                                        <label for="create_serial_number" class="block text-xs font-medium text-gray-700">Serial
                                            Number</label>
                                        <input type="text" name="serial_number" id="create_serial_number"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                                    </div>
                                </div>
                            </div>

                            <!-- Informasi Operasional -->
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
                                        <label for="create_no_hp" class="block text-xs font-medium text-gray-700">No
                                            HP</label>
                                        <input type="text" name="no_hp" id="create_no_hp"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                                    </div>
                                    <div>
                                        <label for="create_tanggal_pemasangan"
                                            class="block text-xs font-medium text-gray-700">Tanggal Pemasangan</label>
                                        <div class="relative mt-1">
                                            <input type="date" name="tanggal_pemasangan" id="create_tanggal_pemasangan"
                                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                                        </div>
                                    </div>
                                    <div>
                                        <label for="create_masa_garansi" class="block text-xs font-medium text-gray-700">Masa
                                            Garansi</label>
                                        <div class="relative mt-1">
                                            <input type="date" name="masa_garansi" id="create_masa_garansi"
                                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                                        </div>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label for="create_nama_penjaga" class="block text-xs font-medium text-gray-700">Nama
                                            Penjaga</label>
                                        <input type="text" name="nama_penjaga" id="create_nama_penjaga"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
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

        <!-- Edit Modal -->
        <div x-show="showEditModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex min-h-screen items-end justify-center px-4 pb-20 pt-4 text-center sm:block sm:p-0">
                <div x-show="showEditModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                    class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"
                    @click="closeEditModal()"></div>

                <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>

                <div x-show="showEditModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="relative inline-block overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl sm:align-middle">

                    <!-- Header -->
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

                            <!-- Identitas Perangkat -->
                            <div class="bg-slate-50 p-4 rounded-lg border border-slate-100">
                                <h4 class="text-sm font-semibold text-gray-900 mb-4 ">Identitas Perangkat</h4>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div>
                                        <label for="nama_logger" class="block text-xs font-medium text-gray-700">Nama
                                            Perangkat</label>
                                        <input type="text" name="nama_logger" id="nama_logger"
                                            x-model="formData.nama_logger"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                                    </div>
                                    <div>
                                        <label for="kategori" class="block text-xs font-medium text-gray-700">Kategori
                                            Perangkat</label>
                                        <select id="kategori" name="id_katlogger" x-model="formData.id_katlogger"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                                            <option value="">Pilih Kategori</option>
                                            @foreach ($kategoris as $kategori)
                                                <option value="{{ $kategori->id_katlogger }}">
                                                    {{ $kategori->nama_kategori }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label for="seri" class="block text-xs font-medium text-gray-700">Seri
                                            Perangkat</label>
                                        <input type="text" name="seri" id="seri" x-model="formData.seri"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                                    </div>
                                    <div>
                                        <label for="serial_number" class="block text-xs font-medium text-gray-700">Serial
                                            Number</label>
                                        <input type="text" name="serial_number" id="serial_number"
                                            x-model="formData.serial_number"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                                    </div>
                                </div>
                            </div>

                            <!-- Informasi Operasional -->
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
                                        <label for="no_hp" class="block text-xs font-medium text-gray-700">No
                                            HP</label>
                                        <input type="text" name="no_hp" id="no_hp" x-model="formData.no_hp"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                                    </div>
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
                                    <div class="sm:col-span-2">
                                        <label for="nama_penjaga" class="block text-xs font-medium text-gray-700">Nama
                                            Penjaga</label>
                                        <input type="text" name="nama_penjaga" id="nama_penjaga"
                                            x-model="formData.nama_penjaga"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
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
                showCreateModal: false,
                showEditModal: false,
                searchQuery: '',
                allDevices: @json($devices),
                formData: {
                    id_logger: '',
                    nama_logger: '',
                    id_katlogger: '',
                    seri: '',
                    serial_number: '',
                    sensor_type: '',
                    no_hp: '',
                    tanggal_pemasangan: '',
                    masa_garansi: '',
                    nama_penjaga: ''
                },
                updateUrl: '',

                filteredDevices() {
                    if (!this.searchQuery.trim()) {
                        return this.allDevices;
                    }

                    const query = this.searchQuery.toLowerCase();
                    return this.allDevices.filter(device => {
                        return (
                            (device.id_logger && device.id_logger.toLowerCase().includes(query)) ||
                            (device.nama_logger && device.nama_logger.toLowerCase().includes(query)) ||
                            (device.kategori && device.kategori.toLowerCase().includes(query)) ||
                            (device.serial_number && device.serial_number.toLowerCase().includes(query)) ||
                            (device.no_hp && device.no_hp.toLowerCase().includes(query)) ||
                            (device.nama_penjaga && device.nama_penjaga.toLowerCase().includes(query))
                        );
                    });
                },

                formatDate(dateString) {
                    if (!dateString || dateString === '-') return '-';

                    const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                                   'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

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

                openCreateModal() {
                    this.formData = {
                        id_logger: '',
                        nama_logger: '',
                        id_katlogger: '',
                        seri: '',
                        serial_number: '',
                        sensor_type: '',
                        no_hp: '',
                        tanggal_pemasangan: '',
                        masa_garansi: '',
                        nama_penjaga: ''
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
                        id_katlogger: device.id_katlogger,
                        seri: device.seri !== '-' ? device.seri : '',
                        serial_number: device.serial_number !== '-' ? device.serial_number : '',
                        sensor_type: device.sensor_type !== '-' ? device.sensor_type : '',
                        no_hp: device.no_hp !== '-' ? device.no_hp : '',
                        tanggal_pemasangan: device.tanggal_pemasangan || '',
                        masa_garansi: device.masa_garansi || '',
                        nama_penjaga: device.nama_penjaga !== '-' ? device.nama_penjaga : ''
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
