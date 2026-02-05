@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Instansi</h1>
                <p class="text-sm text-slate-500">Kelola daftar instansi dan relasinya ke user.</p>
            </div>

            <div class="flex items-center gap-3">
                @if (session('success'))
                    <div class="px-4 py-2 bg-emerald-100 text-emerald-700 rounded-lg text-sm font-semibold shadow-sm ring-1 ring-emerald-200">
                        {{ session('success') }}
                    </div>
                @endif

                <a href="{{ route('instansi.create') }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                    + Tambah Instansi
                </a>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600 whitespace-nowrap">
                    <thead class="bg-slate-100 text-xs font-semibold uppercase text-slate-700">
                        <tr>
                            <th scope="col" class="px-6 py-4">No</th>
                            <th scope="col" class="px-6 py-4">Nama</th>
                            <th scope="col" class="px-6 py-4">Alamat</th>
                            <th scope="col" class="px-6 py-4">Telepon</th>
                            <th scope="col" class="px-6 py-4">Jumlah User</th>
                            <th scope="col" class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @forelse ($instansi as $index => $row)
                            <tr class="hover:bg-slate-50">
                                <td class="whitespace-nowrap px-6 py-4 font-medium text-slate-900">{{ $index + 1 }}</td>
                                <td class="whitespace-nowrap px-6 py-4 font-semibold text-slate-900">{{ $row->nama }}</td>
                                <td class="px-6 py-4 whitespace-normal max-w-md">
                                    {{ $row->alamat ?? '-' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">{{ $row->telp ?? '-' }}</td>
                                <td class="whitespace-nowrap px-6 py-4">{{ $row->users_count }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('instansi.edit', $row) }}"
                                            class="inline-flex items-center rounded-md border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                            Edit
                                        </a>
                                        <form action="{{ route('instansi.destroy', $row) }}" method="POST"
                                            onsubmit="return confirm('Hapus instansi ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="inline-flex items-center rounded-md border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-sm text-slate-500">
                                    Belum ada data instansi.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
