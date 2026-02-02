@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <h1 class="text-2xl font-bold text-slate-900">Pengaturan Device</h1>

        <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-100 text-xs font-semibold uppercase text-slate-700">
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
                                <td class="whitespace-nowrap px-6 py-4">{{ $device->id_logger }}</td>
                                <td class="whitespace-nowrap px-6 py-4 font-medium text-slate-900">{{ $device->nama_logger }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">{{ $device->lokasi?->latitude ?? '-' }}</td>
                                <td class="whitespace-nowrap px-6 py-4">{{ $device->lokasi?->longitude ?? '-' }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-1.5 items-start">
                                        @foreach ($device->params as $param)
                                            @php
                                                $colorClass = 'bg-slate-100 text-slate-700'; // Default
                                                $name = strtolower($param->nama_parameter);
                                                
                                                if (str_contains($name, 'muka air') || str_contains($name, 'humidity')) {
                                                    $colorClass = 'bg-sky-100 text-sky-700';
                                                } elseif (str_contains($name, 'temp') || str_contains($name, 'suhu')) {
                                                    $colorClass = 'bg-orange-100 text-orange-700';
                                                } elseif (str_contains($name, 'bat') || str_contains($name, 'volt')) {
                                                    $colorClass = 'bg-emerald-100 text-emerald-700';
                                                } elseif (str_contains($name, 'kedalaman')) {
                                                     $colorClass = 'bg-amber-100 text-amber-700';
                                                }

                                                 // Simplify names for display as per screenshot if needed, or use DB value
                                                 // The screenshot shows "Muka Air Tanah", "Humidity Logger", etc.
                                                 $displayName = $param->nama_parameter;
                                            @endphp
                                            <span
                                                class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium {{ $colorClass }}">
                                                {{ $displayName }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    {{ $device->jiat?->kedalaman_sumur ? $device->jiat->kedalaman_sumur . ' m' : '-' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    {{ $device->jiat?->kedalaman_sensor ? $device->jiat->kedalaman_sensor . ' m' : '-' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    {{ $device->jiat?->kedalaman_pompa ? $device->jiat->kedalaman_pompa . ' m' : '-' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-center">
                                    <button class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-indigo-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
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
    </div>
@endsection
