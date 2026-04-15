<div class="grid grid-cols-3 gap-2 md:grid-cols-1 md:gap-2">
<a href="{{ route('analisa.index', $lg->id_logger) }}{{ $pHumidity ? '?parameter=' . urlencode($pHumidity->nama_parameter) : '' }}"
        class="block rounded-lg border border-slate-200 bg-white shadow-sm transition-all hover:shadow-md hover:border-blue-300 px-2 py-2 sm:px-3 sm:py-2.5">
        <div class="flex flex-col md:flex-row items-center md:items-center gap-1 md:gap-3">
            <div class="flex-shrink-0 flex h-7 w-7 sm:h-8 sm:w-8 items-center justify-center rounded-md border border-slate-200 bg-slate-50 overflow-hidden">
                <img src="{{ asset('icons/beranda/' . ($isOnline ? 'humidity_online.svg' : 'humidity_offline.svg')) }}"
                    alt="Humidity" class="h-full w-full object-cover {{ $iconClass }}">
            </div>
            <div class="leading-tight text-center md:text-left">
                <div class="text-[8px] sm:text-[10px] font-semibold tracking-wider text-slate-400 uppercase truncate overflow-hidden">Humidity</div>
                <div class="text-sm sm:text-base md:text-xl font-extrabold text-slate-900 {{ $muted ? 'opacity-60' : '' }}">
                    {{ $humidity ?? '-' }}<span class="text-[10px] sm:text-xs font-bold text-slate-400 ml-0.5">%</span>
                </div>
            </div>
        </div>
    </a>
<a href="{{ route('analisa.index', $lg->id_logger) }}{{ $pBattery ? '?parameter=' . urlencode($pBattery->nama_parameter) : '' }}"
        class="block rounded-lg border border-slate-200 bg-white shadow-sm transition-all hover:shadow-md hover:border-green-300 px-2 py-2 sm:px-3 sm:py-2.5">
        <div class="flex flex-col md:flex-row items-center md:items-center gap-1 md:gap-3">
            <div class="flex-shrink-0 flex h-7 w-7 sm:h-8 sm:w-8 items-center justify-center rounded-md border border-slate-200 bg-slate-50 overflow-hidden">
                <img src="{{ asset('icons/beranda/' . ($isOnline ? 'battery_online.svg' : 'battery_offline.svg')) }}"
                    alt="Battery" class="h-full w-full object-cover {{ $iconClass }}">
            </div>
            <div class="leading-tight text-center md:text-left min-w-0 w-full">
                <div class="text-[8px] sm:text-[10px] font-semibold tracking-wider text-slate-400 uppercase truncate overflow-hidden">Battery</div>
                <div class="text-sm sm:text-base md:text-xl font-extrabold text-slate-900 {{ $muted ? 'opacity-60' : '' }}">
                    {{ $battery ?? '-' }}<span class="text-[10px] sm:text-xs font-bold text-slate-400 ml-0.5">V</span>
                </div>
            </div>
        </div>
    </a>
<a href="{{ route('analisa.index', $lg->id_logger) }}{{ $pTemp ? '?parameter=' . urlencode($pTemp->nama_parameter) : '' }}"
        class="block rounded-lg border border-slate-200 bg-white shadow-sm transition-all hover:shadow-md hover:border-orange-300 px-2 py-2 sm:px-3 sm:py-2.5">
        <div class="flex flex-col md:flex-row items-center md:items-center gap-1 md:gap-3">
            <div class="flex-shrink-0 flex h-7 w-7 sm:h-8 sm:w-8 items-center justify-center rounded-md border border-slate-200 bg-slate-50 overflow-hidden">
                <img src="{{ asset('icons/beranda/' . ($isOnline ? 'temper_online.svg' : 'temper_offline.svg')) }}"
                    alt="Temperature" class="h-full w-full object-cover {{ $iconClass }}">
            </div>
            <div class="leading-tight text-center md:text-left min-w-0 w-full overflow-hidden">
                <div class="text-[8px] sm:text-[10px] font-semibold tracking-wider text-slate-400 uppercase truncate">Temperature</div>
                <div class="text-sm sm:text-base md:text-xl font-extrabold text-slate-900 {{ $muted ? 'opacity-60' : '' }}">
                    {{ $temp ?? '-' }}<span class="text-[10px] sm:text-xs font-bold text-slate-400 ml-0.5">Â°C</span>
                </div>
            </div>
        </div>
    </a>

</div>
