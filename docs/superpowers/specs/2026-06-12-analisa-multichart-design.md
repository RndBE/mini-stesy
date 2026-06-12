# Menu Analisa — Mode Multi Parameter (Multichart Overlay)

**Tanggal:** 2026-06-12
**Status:** Disetujui untuk perencanaan

## Tujuan

Menambahkan mode **Multi Parameter** pada menu analisa (`analisadata/analisa.blade.php`,
"Konsol Analisa") agar user bisa **menumpuk beberapa parameter dari pos/logger yang
sama** ke dalam **satu grafik overlay**. Parameter dipilih lewat **checklist**; tiap
parameter dicentang menjadi satu garis rerata. Rentang waktu & logger memakai satu
kontrol yang sama untuk semua seri.

## Non-Tujuan

- Tidak mengubah mode single-parameter yang sudah ada (grafik + tabel + statistik
  rerata/min/maks tetap berfungsi persis seperti sekarang).
- Tidak mengubah controller, route, atau skema database. Fitur ini murni
  frontend — reuse penuh endpoint `analisa.data` yang sudah ada.
- Tidak menambahkan baris kartu status di atas (Status Logger / Battery / Alarm
  dll.) — panel grafik dibuat bersih ("hapus card atas").
- Tidak menyediakan tabel data atau export Excel khusus mode multi (v1 chart-only).

## Konteks Arsitektur (yang sudah ada)

Menu analisa saat ini bersifat **single-parameter**:

- Kontrol di kolom kiri (`control-deck`, [analisa.blade.php:1367-1748](../../../resources/views/analisadata/analisa.blade.php#L1367-L1748)):
  `#loggerSelect`, `#parameterSelect` (single select), segmen rentang
  `input[name="range"]` (day/month/year/custom), beserta datepicker, lalu tombol
  Download Excel & Data Masuk.
- Area grafik di kolom kanan (`panel-card`, [analisa.blade.php:1750-1910](../../../resources/views/analisadata/analisa.blade.php#L1750-L1910)):
  `<canvas id="dataChart">` + `chartTitle` + tabel data.
- `loadData()` ([analisa.blade.php:2192-2230](../../../resources/views/analisadata/analisa.blade.php#L2192-L2230))
  fetch `route('analisa.data', id)` dengan query `parameter`, `range`, `date`,
  lalu `updateChart(data)` + `updateTable(data)`.
- Endpoint mengembalikan satu parameter:
  `{ labels[], chartData[] (rerata), minData[], maxData[], tableData[], rerata,
  minimum, maksimum, tipe_graf, akumulasi, klasifikasi }`
  ([AnalisaController::getChartData](../../../app/Http/Controllers/AnalisaController.php#L88),
  payload di [baris 435-448](../../../app/Http/Controllers/AnalisaController.php#L435-L448)).
- `$parameters` dioper ke view sebagai array `{ nama_parameter, kolom_sensor,
  satuan, tipe_graf, ... }` ([AnalisaController::index](../../../app/Http/Controllers/AnalisaController.php#L31-L47)).

Label dihasilkan konsisten dari rentang/tanggal yang sama untuk satu logger,
sehingga beberapa parameter pada logger & rentang yang sama menghasilkan label
yang sebanding (digabung lewat union label, lihat di bawah).

## Perilaku Mode

Toggle segmented di atas `deck-body`: `[ Single Parameter ] [ Multi Parameter ]`.
Default **Single**. State disimpan di variabel JS (`analysisMode`) + atribut pada
container; tidak perlu reload halaman.

| Elemen | Mode Single | Mode Multi |
|---|---|---|
| `#parameterSelect` (dropdown 1 param) | tampil | **disembunyikan** |
| Checklist parameter (baru) | disembunyikan | **tampil** |
| Logger select + segmen rentang + datepicker | tampil, aktif | tampil, aktif (dipakai bersama) |
| Panel grafik single (`#dataChart`) + tabel | tampil | **disembunyikan** |
| Panel multichart (baru) | disembunyikan | **tampil** |
| Tombol Download Excel & Data Masuk | tampil | **disembunyikan** |
| Tombol Download Chart | tampil (chart single) | tampil (chart multi) |

Saat user mengganti mode, logger, rentang, atau tanggal di mode multi → seluruh
seri yang tercentang dimuat ulang. Saat user mencentang/menghapus satu parameter
→ hanya grafik multi yang dimuat ulang.

## Pemilihan Parameter (Checklist)

- Diisi dari `$parameters` yang sudah dioper ke view (sama dengan sumber
  `#parameterSelect`). Tiap item: checkbox + label `str_replace('_',' ',nama)` +
  satuan kecil di kanan.
- Kontainer scrollable (mis. `max-h-64 overflow-auto`), gaya mengikuti deck yang ada.
- Aksi bantu: tautan kecil "Pilih semua" / "Hapus semua".
- Saat logger diganti, checklist harus mengikuti parameter logger baru. Karena
  daftar parameter saat ini hanya tersedia untuk logger awal yang dirender server,
  v1 memakai pendekatan **reload halaman saat ganti logger** (pola yang sudah ada:
  `#loggerSelect` mengubah URL/`loggerId`). Centang di-reset saat ganti logger.
  (Catatan implementasi: jika `#loggerSelect` saat ini sudah memuat ulang halaman
  ke logger lain, checklist otomatis ter-render ulang dari server — tidak perlu
  fetch daftar parameter via AJAX.)

## Pemuatan & Penggabungan Data

Tanpa endpoint baru. Untuk tiap parameter yang dicentang:

1. Panggil `route('analisa.data', loggerId)` dengan `parameter`, `range`, `date`
   yang sama (rentang/tanggal dibaca dari kontrol yang dipakai bersama).
2. Ambil `labels` + `chartData` (rerata). Abaikan min/max/tabel untuk mode ini.
3. Semua request dijalankan paralel (`Promise.all`).

Penggabungan seri:

- Bangun **union label** dari semua respons (urut sesuai urutan kemunculan; untuk
  rentang yang sama label umumnya identik, union hanya pengaman bila ada selisih).
- Petakan tiap seri ke union label via map `label → nilai`; label yang tak ada di
  suatu parameter diisi `null` (garis putus, bukan 0).
- Tiap parameter = satu dataset Chart.js (garis rerata, `pointRadius` kecil,
  `tension` halus seperti chart existing), warna dari palet siklik.

## Sumbu Y Ganda (Satuan Berbeda)

- Kelompokkan parameter tercentang berdasarkan `satuan`.
- Satuan **pertama** → sumbu kiri (`yLeft`), satuan **kedua** → sumbu kanan
  (`yRight`). Tiap dataset di-assign `yAxisID` sesuai satuannya. Contoh: Suhu (°C)
  kiri, RH (%) kanan — sesuai screenshot.
- Satuan ke-**3+** yang berbeda → fallback ke sumbu kiri (keterbatasan v1,
  ditandai di legend/judul bila perlu). Tidak menambah sumbu ke-3 demi kebersihan.
- Bila semua parameter bersatuan sama (mis. Ampere R/S/T = A) → hanya sumbu kiri.
- Label sumbu memuat satuan (mis. `°C`, `%`).

## Tata Letak / Tampilan

- Panel multichart menempati kolom kanan yang sama (`md:col-span-7 ...`) sebagai
  `panel-card` baru, menggantikan posisi panel single saat mode multi.
- Header: judul `"{Periode} - {Nama Pos}"` (reuse `getChartPostName()` /
  `updateChartTitle()` yang ada) + tombol "Download Chart".
- Tinggi canvas serupa chart existing; legend di bawah (`usePointStyle`).
- **Tanpa kartu status di atas** dan tanpa tabel data.
- Empty state (belum ada centang): tampilkan teks "Centang parameter untuk
  menampilkan grafik" di area canvas.

## Komponen & Isolasi

`analisadata/analisa.blade.php` sudah **4219 baris**. Agar tidak makin gemuk,
fitur dipisah:

- **`resources/views/analisadata/partials/multi_chart_panel.blade.php`** (baru) —
  markup toggle mode opsional + checklist + panel canvas multi. Di-`@include` di
  dalam `analisa.blade.php` pada kolom kiri (checklist) dan kolom kanan (panel).
- **`public/js/analisa-multichart.js`** (baru) — seluruh logika mode multi
  (toggle, baca checklist, fetch paralel, gabung label, build/update Chart.js,
  download). Di-load via `@push('scripts')` / `<script src>` setelah Chart.js,
  membaca `loggerId`/`route` lewat variabel global atau `data-*` attribute yang
  sudah disediakan view. Tidak menyentuh blok `<script>` inline 4200-baris selain
  hook minimal (mis. toggle visibilitas elemen).
- Sentuhan minimal di `analisa.blade.php`: tambah toggle, `@include` partial,
  sembunyikan/munculkan elemen sesuai mode, dan enqueue JS baru.

## Penanganan Error / Kasus Tepi

- Belum ada parameter dicentang → panel kosong + pesan, tidak ada fetch.
- Salah satu fetch gagal → seri itu dilewati (di-`catch` per-request), seri lain
  tetap tampil; tampilkan toast/log ringan, tidak membatalkan keseluruhan.
- Respons tanpa data (`labels` kosong) untuk satu parameter → seri itu kosong
  (garis hilang), tidak error.
- Nilai null/non-numerik → `null` pada dataset (garis terputus), konsisten pola
  existing.
- Ganti mode ke single → grafik multi disembunyikan, state single tidak terganggu.
- Logger diganti → halaman reload ke logger baru (pola existing), checklist & seri
  ter-reset.

## Rencana Uji / Verifikasi

Diverifikasi di lingkungan **dev** (bukan prod; tidak ada tulisan DB).

1. Buka menu analisa sebuah logger → mode default Single tetap berfungsi penuh
   (grafik + tabel + Download Excel/Data Masuk).
2. Klik toggle **Multi Parameter** → dropdown param & panel single hilang;
   checklist & panel multi muncul; Excel/Data Masuk tersembunyi.
3. Centang 1 parameter → muncul 1 garis rerata; judul & periode benar.
4. Centang 2 parameter **satu satuan** (mis. dua ampere) → 2 garis, satu sumbu kiri.
5. Centang 2 parameter **beda satuan** (mis. Suhu °C + RH %) → sumbu kiri & kanan,
   skala terpisah seperti screenshot.
6. Ganti rentang (Hari/Bulan/Tahun/Rentang) & tanggal → semua seri ikut update.
7. Hapus centang → garis hilang; kosongkan semua → empty state.
8. Download Chart → unduh gambar grafik multi.
9. Ganti logger → reload, checklist sesuai parameter logger baru, mode kembali
   sesuai default.
10. Responsif: desktop kolom kiri/kanan; mobile menumpuk (mengikuti grid existing).

## File yang Disentuh

- `resources/views/analisadata/analisa.blade.php` — toggle mode, `@include`
  partial, hook visibilitas elemen per mode, enqueue JS baru. (sentuhan minimal)
- `resources/views/analisadata/partials/multi_chart_panel.blade.php` — **baru**:
  checklist + panel canvas multi.
- `public/js/analisa-multichart.js` — **baru**: logika mode multi.

## Keputusan Terbuka (diselesaikan saat implementasi)

- Palet warna final per parameter (default: palet siklik konsisten dengan warna
  brand `#303481`, `#0fa3d1`, dst.).
- Penempatan persis toggle mode (di atas label "Parameter" pada deck) — final saat
  implementasi.
- Apakah `#loggerSelect` di mode multi reload halaman atau fetch checklist via
  AJAX — v1 reload (pola existing); AJAX bisa ditambah belakangan tanpa ubah
  struktur.
