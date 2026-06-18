<div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
    @include('beranda.categories.partials.logger_header')

    <div class="grid grid-cols-12 gap-4 p-5 md:grid-cols-12">
        @if ($pRain)
        <div class="col-span-12 md:col-span-8 space-y-3 md:border-r md:border-slate-200 md:pr-4 ">
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
                $loggerWaktu   = $lg->latest_waktu ? \Carbon\Carbon::parse($lg->latest_waktu) : now();
                $loggerHour    = (int) $loggerWaktu->format('H');
                $isPagi        = ($loggerHour >= 6 && $loggerHour < 18);
                $waktuSuffix   = $isPagi ? '' : '_malam';
                $timeAwareStates = ['tidak_hujan', 'hujan_sangat_ringan', 'hujan_ringan'];

                $iconStatePerJam = preg_match('/^[a-z0-9_]+$/', (string) $stateHujanPerJam)
                    ? $stateHujanPerJam
                    : 'tidak_hujan';
                $iconStateHarian = preg_match('/^[a-z0-9_]+$/', (string) $stateHujanHarian)
                    ? $stateHujanHarian
                    : 'tidak_hujan';
                if (in_array($iconStatePerJam, $timeAwareStates)) {
                    $iconStatePerJam .= $waktuSuffix;
                }
                if (in_array($iconStateHarian, $timeAwareStates)) {
                    $iconStateHarian .= $waktuSuffix;
                }

                $defaultIcon = asset('klasifikasi_hujan/tidak_hujan.png');

                // Klik card → halaman Analisa dengan parameter hujan terpilih
                $analisaUrl = route('analisa.index', [
                    'id_logger' => $lg->id_logger,
                    'parameter' => $pRain->nama_parameter,
                ]);
            @endphp

            <div class="grid grid-cols-2 gap-4 lg:grid-cols-2 relative">
                <a href="{{ $analisaUrl }}" title="Lihat analisa curah hujan"
                    class="group block relative overflow-hidden rounded-xl border px-3 py-3 text-center transition hover:border-blue-400 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-blue-300">
                    <div class="text-[10px] sm:text-xs font-semibold tracking-wide text-slate-700">AKUMULASI
                        HUJAN PER JAM</div>

                    <img src="{{ asset('klasifikasi_hujan/' . $iconStatePerJam . '.png') }}"
                        onerror="this.onerror=null;this.src='{{ $defaultIcon }}';"
                        alt="{{ $statusHujanPerJam ?? 'Status Hujan' }}"
                        class="pointer-events-none absolute right-[-0.5rem] top-8 h-20 w-20 sm:top-10 sm:h-28 sm:w-28 lg:h-32 lg:w-32 2xl:top-7 2xl:h-36 2xl:w-36 object-contain {{ $muted ? 'opacity-60' : '' }}">
                    <div class="mt-20 sm:mt-24 xl:mt-28 2xl:mt-32"></div>

                    <div
                        class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 whitespace-nowrap pt-1 {{ $muted ? 'opacity-60' : '' }}">
                        {{ $displayPerJam }}
                        <span class="text-xs sm:text-sm font-semibold">mm</span>
                    </div>
                    <div class="inline-flex text-[10px] sm:text-xs font-semibold uppercase {{ $muted ? 'opacity-60' : '' }}">
                        {{ $statusHujanPerJam ?? '-' }}
                    </div>

                </a>

                <a href="{{ $analisaUrl }}" title="Lihat analisa curah hujan"
                    class="group block relative overflow-hidden rounded-xl border px-3 py-3 text-center transition hover:border-blue-400 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-blue-300">
                    <div class="text-[10px] sm:text-xs font-semibold tracking-wide text-slate-700">
                        AKUMULASI HUJAN HARIAN</div>

                    <img src="{{ asset('klasifikasi_hujan/' . $iconStateHarian . '.png') }}"
                        onerror="this.onerror=null;this.src='{{ $defaultIcon }}';"
                        alt="{{ $statusHujanHarian ?? 'Status Hujan' }}"
                        class="pointer-events-none absolute right-[-0.5rem] top-8 h-20 w-20 sm:top-10 sm:h-28 sm:w-28 lg:h-32 lg:w-32 2xl:top-7 2xl:h-36 2xl:w-36 object-contain {{ $muted ? 'opacity-60' : '' }}">
                    <div class="mt-20 sm:mt-24 xl:mt-28 2xl:mt-32"></div>

                    <div
                        class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 whitespace-nowrap pt-1 {{ $muted ? 'opacity-60' : '' }}">
                        {{ $displayHarian }}
                        <span class="text-xs sm:text-sm font-semibold">mm</span>
                    </div>
                    <div class="inline-flex text-[10px] sm:text-xs font-semibold uppercase {{ $muted ? 'opacity-60' : '' }}">
                        {{ $statusHujanHarian ?? '-' }}
                    </div>
                </a>
            </div>
        </div>
        @endif

        @if ($pHumidity || $pBattery || $pTemp)
        <div class="col-span-12 md:col-span-4 space-y-3">
            <div class="text-md font-semibold text-slate-700">Parameter Logger</div>
            @include('beranda.categories.partials.logger_health_cards')
        </div>
        @endif
    </div>
</div>
