@extends('layouts.app')

@push('head')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
@endpush

@section('content')
    <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
        <div class="text-sm font-semibold text-slate-900">Peta Lokasi</div>
        <div class="mt-3 .h-[560px] w-full overflow-hidden rounded-xl ring-1 ring-slate-200" id="map"></div>
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const points = @json($points);

        const fallback = [-7.79558, 110.36949];
        const first = points.length ? [points[0].lat, points[0].lng] : fallback;

        const map = L.map('map').setView(first, points.length ? 10 : 9);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19
        }).addTo(map);

        points.forEach(p => {
            const marker = L.marker([p.lat, p.lng]).addTo(map);
            marker.bindPopup(`<b>${p.nama_logger}</b><br>ID: ${p.id_logger}<br>Lokasi: ${p.nama_lokasi ?? '-'}`);
        });
    </script>
@endpush
