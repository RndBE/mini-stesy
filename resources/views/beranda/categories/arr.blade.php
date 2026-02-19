<div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
    @include('beranda.categories.partials.logger_header')

    <div class="grid grid-cols-12 gap-4 p-5 md:grid-cols-12">
        <div class="col-span-12 md:col-span-8 space-y-3 md:border-r md:border-slate-200 md:pr-2">
            <div class="text-md font-semibold text-slate-700">Data Curah Hujan</div>

            <div class="rounded-xl border border-cyan-200 bg-cyan-50 p-5">
                <div class="text-xs font-semibold tracking-wide text-cyan-700">CURAH HUJAN</div>
                <div class="mt-2 text-4xl font-extrabold text-slate-900">
                    {{ $curahHujan ?? '-' }}
                    <span class="text-sm font-semibold text-slate-500">mm</span>
                </div>
                <div class="mt-2 text-xs text-slate-600">
                    Parameter: {{ $pRain?->nama_parameter ?? 'Tidak terkonfigurasi' }}
                </div>
            </div>
        </div>

        <div class="col-span-12 md:col-span-4 space-y-3">
            <div class="text-md font-semibold text-slate-700">Kesehatan Logger</div>
            @include('beranda.categories.partials.logger_health_cards')
        </div>
    </div>
</div>
