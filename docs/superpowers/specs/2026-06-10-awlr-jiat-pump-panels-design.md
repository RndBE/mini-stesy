# AWLR Jiat — Panel Monitoring Pompa (Listrik 3-Fasa, Flow Meter, Kualitas Air)

**Tanggal:** 2026-06-10
**Status:** Disetujui untuk perencanaan

## Tujuan

Menampilkan 11 parameter baru pada halaman beranda **AWLR Jiat** untuk stasiun
sumur-dalam yang **berpompa** (`jiat_data.has_pump = true`):

- Listrik 3-fasa pompa: Voltage R/S/T, Ampere R/S/T
- Flow meter: Flow Rate, Flow Rate Signal
- Kualitas air: pH, Amonia, Suhu Air

Parameter ini hanya tampil di stasiun berpompa yang parameternya termapping;
stasiun AWLR Jiat lain tidak terpengaruh.

## Non-Tujuan

- Tidak mengubah diagram sumur / 4 kartu existing (Data Air Tanah, Elevasi
  Sensor, Muka Air Tanah, Elevasi Pompa).
- Tidak menulis apa pun ke database produksi. Mapping kolom `parameter_sensor`
  per-logger diisi user lewat UI admin (editData), bukan oleh perubahan ini.
- Tidak menambahkan parameter ke template AWLR umum (agar tidak terdorong
  otomatis ke semua stasiun AWLR).
- Tidak menyentuh cabang Non-JIAT pada `awlr.blade.php`.

## Konteks Arsitektur (yang sudah ada)

Rantai parameter existing yang dipakai ulang apa adanya:

`list_parameter` (katalog) → user mapping via UI → `parameter_sensor`
(per-logger) → `t_Logger::params` → `index.blade.php` `$findParamByBase()`
membaca `$latest->{kolom_sensor}` → render kartu.

- `$findParamByBase([...])` di [index.blade.php:202-215](../../../resources/views/beranda/index.blade.php#L202-L215)
  mencocokkan `parameter_utama` yang dinormalisasi (lowercase, spasi→`_`).
  Mapping dengan `parameter_utama = 'voltage_r'` dll. otomatis ketemu.
- Cabang JIAT aktif saat `subKategoriAwlr === 'jiat'`
  ([awlr.blade.php:4](../../../resources/views/beranda/categories/awlr.blade.php#L4)),
  yaitu `jiat_data.kedalaman_sumur > 0`
  ([index.blade.php:264-266](../../../resources/views/beranda/index.blade.php#L264-L266)).
- `jiat_data.has_pump` membedakan stasiun berpompa
  ([Jiat_data.php](../../../app/Models/Jiat_data.php), relasi `jiat()`).
- Stasiun dengan ~11 sensor tambahan + parameter sumur diperkirakan memakai
  family `t_s50` (`sensor_count >= 20`, lihat
  [SensorFamily.php](../../../app/Support/SensorFamily.php)). Perubahan ini tidak
  bergantung pada family tertentu — hanya pada nama kolom hasil mapping.

## Parameter Baru

Ditambahkan ke katalog [ListParameterSeeder.php](../../../database/seeders/ListParameterSeeder.php),
array `$parameters` (bukan ke array `$templates`).

| Nama | base (`parameter_utama`) | satuan | group | icon | status |
|---|---|---|---|---|---|
| Voltage R | `voltage_r` | V | LISTRIK | (lihat ikon) | baru |
| Voltage S | `voltage_s` | V | LISTRIK | (lihat ikon) | baru |
| Voltage T | `voltage_t` | V | LISTRIK | (lihat ikon) | baru |
| Ampere R | `ampere_r` | A | LISTRIK | (lihat ikon) | baru |
| Ampere S | `ampere_s` | A | LISTRIK | (lihat ikon) | baru |
| Ampere T | `ampere_t` | A | LISTRIK | (lihat ikon) | baru |
| Flow Rate | `flow_rate` | m³/h | FLOW | (lihat ikon) | baru |
| Flow Rate Signal | `flow_rate_signal` | % | FLOW | (lihat ikon) | baru |
| Amonia | `amonia` | mg/L | KUALITAS | (lihat ikon) | baru |
| pH | `ph_air` | – | KUALITAS | `icons/awgr/ph_air.svg` | reuse |
| Suhu Air | `suhu_air` | °C | KUALITAS | `icons/awgr/suhu_air.svg` | reuse |

- `ph_air` & `suhu_air` sudah ada di katalog (dipakai AWQR) — dipakai ulang, tidak
  diduplikasi.
- Group `LISTRIK`, `FLOW`, `KUALITAS` ditambahkan ke `parameter_groups` jika tabel
  itu ada (mengikuti pola `$groups` di seeder). Group hanya untuk organisasi
  katalog; rendering tetap by-base.
- **Ikon**: pH & Suhu Air pakai SVG existing. Untuk listrik/flow/amonia belum ada
  aset SVG. Default: kartu pakai ikon fallback Material/inline sederhana (mis.
  glyph "bolt"/"water"). Aset SVG khusus bisa ditambahkan belakangan tanpa
  mengubah struktur. (Keputusan ikon final diselesaikan saat implementasi.)

## Tata Letak

Full-width di bawah baris diagram sumur, di dalam cabang JIAT, **hanya** saat
`has_pump`. Grid 12 kolom:

```
┌────────── Diagram Sumur (8) ──────────┬── Parameter Logger (4) ──┐
│  (existing, tidak berubah)            │  (existing)              │
├───────────────────────────────────────┴──────────────────────────┤
│  POMPA & KELISTRIKAN (5)   │ FLOW METER (3) │ KUALITAS AIR (4)    │
│   ┌─────┬─────┬─────┐      │  Flow Rate     │  pH   Amonia       │
│   │  R  │  S  │  T  │      │  Signal        │  Suhu Air          │
│   ├─────┼─────┼─────┤ Volt │                │                    │
│   ├─────┼─────┼─────┤ Amp  │                │                    │
│   └─────┴─────┴─────┘      │                │                    │
└────────────────────────────┴────────────────┴────────────────────┘
```

- **Pompa & Kelistrikan** (col-span-5): grid 3 kolom R/S/T × 2 baris (Voltage/Ampere).
- **Flow Meter** (col-span-3): Flow Rate menonjol + Flow Rate Signal kecil.
- **Kualitas Air** (col-span-4): kartu pH, Amonia, Suhu Air.
- Mobile: semua menumpuk vertikal (`col-span-12`).
- Gaya kartu mengikuti [logger_health_cards.blade.php](../../../resources/views/beranda/categories/partials/logger_health_cards.blade.php):
  border slate, shadow, hover, status online/offline (grayscale saat offline).
- Tiap kartu/sel hanya dirender bila parameternya termapping (param object tidak
  null), konsisten dengan pola `@if ($pHumidity)` existing.

## Komponen & Isolasi

`awlr.blade.php` sudah ~770 baris. Agar tidak makin gemuk, panel baru dipisah ke
partial sendiri:

- **`resources/views/beranda/categories/partials/jiat_pump_panels.blade.php`** —
  menerima data parameter + nilai, merender 3 panel. Satu tanggung jawab:
  menampilkan panel pompa JIAT.
- Di [awlr.blade.php](../../../resources/views/beranda/categories/awlr.blade.php),
  setelah baris diagram (sekitar baris 488, masih di dalam `@if jiat`), tambah:
  `@if ($lg?->jiat?->has_pump) @include('beranda.categories.partials.jiat_pump_panels') @endif`.

### Aliran data (index.blade.php → include)

Di [index.blade.php](../../../resources/views/beranda/index.blade.php) sekitar
baris 233-290 (tempat `$findParamByBase` dipakai), tambah lookup 11 parameter dan
nilainya. Untuk menghindari 22 variabel lepas, dioper sebagai **dua array
asosiatif** ke `@include($kategoriView, [...])`:

```php
$pumpBases = [
    'voltage_r','voltage_s','voltage_t',
    'ampere_r','ampere_s','ampere_t',
    'flow_rate','flow_rate_signal',
    'amonia','ph_air','suhu_air',
];
$jiatPumpParams = [];   // base => param object (atau null)
$jiatPumpValues = [];   // base => nilai terbaru (atau null)
foreach ($pumpBases as $base) {
    $p = $findParamByBase([$base]);
    $jiatPumpParams[$base] = $p;
    $jiatPumpValues[$base] = ($latest && $p && $p->kolom_sensor)
        ? ($latest->{$p->kolom_sensor} ?? null) : null;
}
```

Kedua array ditambahkan ke daftar `@include` (sekitar baris 324-369). Partial
membaca config tampilan (label, base, satuan, panel) dari daftar statis di dalam
partial, lalu render dari kedua array tsb.

> Catatan: `ph_air`/`suhu_air` mungkin sudah dilookup di tempat lain (mis. `pTma`),
> namun lookup di sini independen dan murah; tidak ada konflik.

## Penanganan Error / Kasus Tepi

- Parameter tidak termapping → kartu tidak dirender (param null). Tidak ada error.
- Nilai null / non-numerik → tampil `-` (pola existing `{{ $val ?? '-' }}`).
- `has_pump` false / `jiat` null → seluruh blok panel tidak dirender.
- Offline → kartu grayscale + nilai opacity, mengikuti `$isOnline`/`$muted`.
- Tidak ada query DB baru di controller; semua nilai dari `$latest` (snapshot
  yang sudah dimuat).

## Rencana Uji / Verifikasi

Diverifikasi di lingkungan **dev** (bukan prod). Karena mapping kolom diisi user
via UI, verifikasi memakai data dev:

1. Seed/mapping dev sementara: petakan beberapa base (mis. `voltage_r`,
   `flow_rate`, `amonia`) ke kolom sensor pada satu logger JIAT `has_pump` di
   `MiniStesySeeder` **khusus dev**, isi nilai dummy di `temp_*_latest`.
2. Render beranda: panel muncul untuk stasiun berpompa, dengan grid 3-fasa,
   flow, dan kualitas air; nilai sesuai dummy; satuan benar.
3. Stasiun JIAT tanpa `has_pump` / tanpa mapping → panel tidak muncul.
4. Mode offline → grayscale.
5. Responsif: desktop grid sejajar, mobile menumpuk.

## File yang Disentuh

- `database/seeders/ListParameterSeeder.php` — 9 base baru + group, reuse 2.
- `resources/views/beranda/index.blade.php` — lookup `$pumpBases`, oper 2 array.
- `resources/views/beranda/categories/awlr.blade.php` — `@include` partial saat
  `has_pump` (cabang JIAT).
- `resources/views/beranda/categories/partials/jiat_pump_panels.blade.php` — baru.
- `database/seeders/MiniStesySeeder.php` — data dev untuk verifikasi (opsional,
  hanya dev).

## Keputusan Terbuka (diselesaikan saat implementasi)

- Aset ikon final untuk listrik/flow/amonia (fallback dulu, ganti belakangan).
- Tabel `parameter_groups` mendukung group baru atau tidak (cek saat seed).
