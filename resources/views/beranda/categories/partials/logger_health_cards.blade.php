<div class="grid grid-cols-1 gap-3">
    <a href="{{ route('analisa.index', $lg->id_logger) }}{{ $pHumidity ? '?parameter=' . urlencode($pHumidity->nama_parameter) : '' }}"
        class="block rounded-lg border border-slate-200 bg-white sm:px-2 2xl:px-4 sm:py-2 2xl:py-3 shadow-sm transition-all hover:shadow-md hover:border-blue-300">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-8 w-8 2xl:h-12 2xl:w-12 items-center justify-center rounded-lg border border-slate-200 bg-slate-50 overflow-hidden">
                    <img src="{{ asset('icons/beranda/' . ($isOnline ? 'humidity_online.svg' : 'humidity_offline.svg')) }}" alt="Humidity"
                        class="h-full w-full object-cover {{ $iconClass }}">
                </div>
                <div class="leading-tight">
                    <div class="text-xs font-normal tracking-wider text-slate-500">HUMIDITY</div>
                    <div class="text-2xl font-extrabold text-slate-900 {{ $muted ? 'opacity-60' : '' }}">
                        {{ $humidity ?? '-' }}
                        <span class="text-xs font-bold text-slate-500">%</span>
                    </div>
                </div>
            </div>
        </div>
    </a>

    <a href="{{ route('analisa.index', $lg->id_logger) }}{{ $pBattery ? '?parameter=' . urlencode($pBattery->nama_parameter) : '' }}"
        class="block rounded-lg border border-slate-200 bg-white sm:px-2 2xl:px-4 sm:py-2 2xl:py-3 shadow-sm transition-all hover:shadow-md hover:border-green-300">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-8 w-8 2xl:h-12 2xl:w-12 items-center justify-center rounded-lg border border-slate-200 bg-slate-50 overflow-hidden">
                    <img src="{{ asset('icons/beranda/' . ($isOnline ? 'battery_online.svg' : 'battery_offline.svg')) }}" alt="Battery"
                        class="h-full w-full object-cover {{ $iconClass }}">
                </div>
                <div class="leading-tight">
                    <div class="text-xs font-normal tracking-wider text-slate-500">BATTERY</div>
                    <div class="text-2xl font-extrabold text-slate-900 {{ $muted ? 'opacity-60' : '' }}">
                        {{ $battery ?? '-' }}
                        <span class="text-xs font-bold text-slate-500">Volt</span>
                    </div>
                </div>
            </div>
        </div>
    </a>

    <a href="{{ route('analisa.index', $lg->id_logger) }}{{ $pTemp ? '?parameter=' . urlencode($pTemp->nama_parameter) : '' }}"
        class="block rounded-lg border border-slate-200 bg-white sm:px-2 2xl:px-4 sm:py-2 2xl:py-3 shadow-sm transition-all hover:shadow-md hover:border-orange-300">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-8 w-8 2xl:h-12 2xl:w-12 items-center justify-center rounded-lg border border-slate-200 bg-slate-50 overflow-hidden">
                    <img src="{{ asset('icons/beranda/' . ($isOnline ? 'temper_online.svg' : 'temper_offline.svg')) }}" alt="Temperature"
                        class="h-full w-full object-cover {{ $iconClass }}">
                </div>
                <div class="leading-tight">
                    <div class="text-xs font-normal tracking-wider text-slate-500">TEMPERATURE</div>
                    <div class="text-2xl font-extrabold text-slate-900 {{ $muted ? 'opacity-60' : '' }}">
                        {{ $temp ?? '-' }}
                        <span class="text-xs font-bold text-slate-500">°C</span>
                    </div>
                </div>
            </div>
        </div>
    </a>
</div>
