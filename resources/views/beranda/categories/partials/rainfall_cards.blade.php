@if ($pRain)
    @php
        $displayPerJam = is_numeric($curahHujanPerJam ?? null)
            ? \App\Support\DisplayFormat::ukur($curahHujanPerJam, 2)
            : '-';
        $displayHarian = is_numeric($curahHujanHarian ?? null)
            ? \App\Support\DisplayFormat::ukur($curahHujanHarian, 2)
            : '-';
        $loggerWaktu = $lg->latest_waktu ? \Carbon\Carbon::parse($lg->latest_waktu) : now();
        $loggerHour = (int) $loggerWaktu->format('H');
        $waktuSuffix = ($loggerHour >= 6 && $loggerHour < 18) ? '' : '_malam';
        $timeAwareStates = ['tidak_hujan', 'hujan_sangat_ringan', 'hujan_ringan'];

        $iconStatePerJam = preg_match('/^[a-z0-9_]+$/', (string) $stateHujanPerJam)
            ? $stateHujanPerJam
            : 'tidak_hujan';
        $iconStateHarian = preg_match('/^[a-z0-9_]+$/', (string) $stateHujanHarian)
            ? $stateHujanHarian
            : 'tidak_hujan';

        if (in_array($iconStatePerJam, $timeAwareStates, true)) {
            $iconStatePerJam .= $waktuSuffix;
        }

        if (in_array($iconStateHarian, $timeAwareStates, true)) {
            $iconStateHarian .= $waktuSuffix;
        }

        $defaultRainIcon = asset('klasifikasi_hujan/tidak_hujan.png');
        $rainAnalysisUrl = route('analisa.index', [
            'id_logger' => $lg->id_logger,
            'parameter' => $pRain->nama_parameter,
        ]);

        $rainCards = [
            [
                'label' => 'Akumulasi Harian',
                'value' => $displayHarian,
                'status' => $statusHujanHarian ?? '-',
                'icon_state' => $iconStateHarian,
            ],
            [
                'label' => 'Akumulasi 1 Jam',
                'value' => $displayPerJam,
                'status' => $statusHujanPerJam ?? '-',
                'icon_state' => $iconStatePerJam,
            ],
        ];
    @endphp

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
        @foreach ($rainCards as $rainCard)
            <a href="{{ $rainAnalysisUrl }}" title="Lihat analisa curah hujan"
                class="group flex min-h-56 flex-col items-center overflow-hidden rounded-xl border border-slate-200 bg-white px-4 py-4 text-center shadow-sm hover:border-slate-300 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-sky-300 {{ $muted ? 'grayscale opacity-70' : '' }}">
                <span class="text-balance text-[10px] font-semibold uppercase text-slate-600 sm:text-xs">
                    {{ $rainCard['label'] }}
                </span>

                <span class="flex flex-1 items-center justify-center py-2">
                    <img src="{{ asset('klasifikasi_hujan/' . $rainCard['icon_state'] . '.png') }}"
                        onerror="this.onerror=null;this.src='{{ $defaultRainIcon }}';"
                        alt="{{ $rainCard['status'] }}"
                        class="h-28 w-36 object-contain sm:h-32 sm:w-40">
                </span>

                <span class="whitespace-nowrap text-2xl font-extrabold tabular-nums text-slate-950 sm:text-3xl">
                    {{ $rainCard['value'] }}
                    <span class="text-xs font-semibold text-slate-500 sm:text-sm">mm</span>
                </span>
                <span class="mt-1 line-clamp-2 text-[9px] font-medium uppercase text-slate-500 sm:text-[10px]">
                    {{ $rainCard['status'] }}
                </span>
            </a>
        @endforeach
    </div>
@endif
