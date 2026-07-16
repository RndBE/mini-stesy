<div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
    @include('beranda.categories.partials.logger_header')

    <div class="grid grid-cols-12 gap-4 p-5 md:grid-cols-12">
        @if ($pRain)
            <div class="col-span-12 space-y-3 md:col-span-8 md:border-r md:border-slate-200 md:pr-4">
                <div class="text-balance text-md font-semibold text-slate-700">Data Curah Hujan</div>
                @include('beranda.categories.partials.rainfall_cards')
            </div>
        @endif

        @if ($pHumidity || $pBattery || $pTemp)
            <div class="col-span-12 space-y-3 md:col-span-4">
                <div class="text-balance text-md font-semibold text-slate-700">Parameter Logger</div>
                @include('beranda.categories.partials.logger_health_cards')
            </div>
        @endif
    </div>
</div>
