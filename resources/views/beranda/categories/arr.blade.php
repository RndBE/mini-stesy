<div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
    @include('beranda.categories.partials.logger_header')

    <div class="grid grid-cols-12 gap-4 p-5 md:grid-cols-12">
        <div class="col-span-12 md:col-span-8 space-y-3 md:border-r md:border-slate-200 md:pr-4">
            <div class="text-md font-semibold text-slate-700">Data Curah Hujan</div>
            @php
                $displayPerJam = is_numeric($curahHujanPerJam ?? null)
                    ? number_format((float) $curahHujanPerJam, 2)
                    : '-';
                $displayHarian = is_numeric($curahHujanHarian ?? null)
                    ? number_format((float) $curahHujanHarian, 2)
                    : '-';
                $jamRange = now()->format('H:00') . ' - ' . now()->format('H:59');
                $tanggalHarian = now()->format('d M Y');
                $iconStatePerJam = preg_match('/^[a-z0-9_]+$/', (string) $stateHujanPerJam)
                    ? $stateHujanPerJam
                    : 'tidak_hujan';
                $iconStateHarian = preg_match('/^[a-z0-9_]+$/', (string) $stateHujanHarian)
                    ? $stateHujanHarian
                    : 'tidak_hujan';
                $defaultIcon = asset('klasifikasi_hujan/tidak_hujan.png');
            @endphp

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2 relative">
                <div class="relative overflow-hidden rounded-xl border px-4 py-3 text-center">
                    <div class="text-xs font-semibold tracking-wide text-slate-700">AKUMULASI HUJAN PER JAM</div>

                    <img src="{{ asset('klasifikasi_hujan/' . $iconStatePerJam . '.png') }}"
                        onerror="this.onerror=null;this.src='{{ $defaultIcon }}';"
                        alt="{{ $statusHujanPerJam ?? 'Status Hujan' }}"
                        class="pointer-events-none absolute right-[-1rem] top-7 h-36 w-36 object-contain">
                    <div class="mt-32"></div>

                    <div class=" text-4xl font-extrabold text-slate-900 pt-2">
                        {{ $displayPerJam }}
                        <span class="text-sm font-semibold ">mm</span>
                    </div>
                    {{-- <div class="mt-1 text-[11px] font-medium text-slate-600">{{ $jamRange }}</div> --}}

                    <div class=" inline-flex  text-xs font-semibold  uppercase">
                        {{ $statusHujanPerJam ?? '-' }}
                    </div>

                </div>

                <div class="relative overflow-hidden rounded-xl border  px-4 py-3 text-center">
                    <div class="text-xs font-semibold tracking-wide text-slate-700 ">AKUMULASI HUJAN HARIAN</div>

                    <img src="{{ asset('klasifikasi_hujan/' . $iconStateHarian . '.png') }}"
                        onerror="this.onerror=null;this.src='{{ $defaultIcon }}';"
                        alt="{{ $statusHujanHarian ?? 'Status Hujan' }}"
                        class="pointer-events-none absolute right-[-1rem] top-7 h-36 w-36 object-contain">
                    <div class="mt-32"></div>

                    <div class=" text-4xl font-extrabold text-slate-900 pt-2">
                        {{ $displayHarian }}
                        <span class="text-sm font-semibold ">mm</span>
                    </div>
                    <div class="inline-flex text-xs font-semibold uppercase">
                        {{ $statusHujanHarian ?? '-' }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-span-12 md:col-span-4 space-y-3">
            <div class="text-md font-semibold text-slate-700">Kesehatan Logger</div>
            @include('beranda.categories.partials.logger_health_cards')
        </div>
    </div>
</div>
