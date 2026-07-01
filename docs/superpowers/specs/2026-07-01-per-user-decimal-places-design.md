# Design: Format Desimal Pengukuran Per-User

**Tanggal:** 2026-07-01
**Status:** Disetujui (menunggu review spec)

## Ringkasan Masalah

User `spam_wosusokas` meminta agar **semua nilai pengukuran** yang tampil di aplikasi
ditampilkan dengan **1 angka di belakang koma**, khusus untuk akun tersebut. User lain
tidak boleh berubah.

Saat ini format angka **di-hardcode** dan tersebar di banyak tempat dengan jumlah desimal
berbeda-beda:
- Blade beranda: `number_format(..., 1..2)` (arr/awqr/afmr/awlr)
- Controller Analisa: `round(..., 2..4)`
- JS chart Analisa: `.toFixed(2..3)`

Belum ada mekanisme setting per-user apa pun (hanya ada access control per-logger).

## Keputusan Desain

| Aspek | Keputusan |
|-------|-----------|
| Cakupan | **Semua** tampilan pengukuran di aplikasi |
| Mekanisme | **Setting per-user di DB** (kolom baru di `t_user`) |
| Perilaku default | User tanpa setting → tidak berubah sama sekali |
| Semantik "1 desimal" | Selalu 1 angka di belakang koma, dibulatkan: `12` → `12.0`, `12.34` → `12.3` |

## Definisi "Pengukuran"

**Termasuk** (nilai sensor fisik): tinggi muka air / TMA, debit, curah hujan (per jam,
harian, akumulasi), pH, suhu, flow rate, pressure, totalizer, tegangan baterai.

**TIDAK termasuk**: persentase kelengkapan data, jumlah/count baris, koordinat
(latitude/longitude), zoom peta, ID/nomor.

## Arsitektur

### 1. Penyimpanan Setting (DB)

Tambah kolom pada tabel `t_user`:

```
decimal_places TINYINT UNSIGNED NULL DEFAULT NULL
```

Semantik:
- `NULL` → pakai perilaku default per-konteks yang ada sekarang (fallback ke `$default`).
- angka `d` → override **semua** format pengukuran user tsb menjadi `d` desimal.

Data awal: `UPDATE t_user SET decimal_places = 1 WHERE username = 'spam_wosusokas'`.

> ⚠️ **Prod DB write.** `ALTER TABLE` + `UPDATE` dijalankan ke DB prod
> (`72.60.78.159` / `db_ministesynew`). Disiapkan sebagai migration Laravel + perintah
> SQL, TIDAK dieksekusi tanpa persetujuan eksplisit user.

### 2. Titik Format Terpusat

**Sumber kebenaran desimal user aktif** — helper tunggal, mis. accessor di model `t_User`
atau fungsi:

```php
function decimal_places_user(): ?int {
    return auth()->check() ? auth()->user()->decimal_places : null;
}
```

**PHP / Blade** — helper `fmt_ukur($nilai, $default)`:

```php
function fmt_ukur($nilai, int $default): string {
    $d = decimal_places_user() ?? $default;
    return number_format((float) $nilai, $d);
}
```

- Konsisten dengan pemakaian `number_format` yang sudah ada (pemisah ribuan tetap).
- `$default` = jumlah desimal lama di titik itu, sehingga user lain 0% berubah.

**JavaScript (chart Analisa)** — inject nilai user ke frontend:

```blade
<script>window.__decimalUkur = @json(decimal_places_user());</script>
```

Lalu buat helper JS `fmtUkur(nilai, def)` yang memakai `window.__decimalUkur ?? def`, dan
ganti semua `.toFixed(2/3)` pada nilai pengukuran dengan helper ini.

### 3. Titik yang Diubah (arahkan ke helper)

- `resources/views/beranda/categories/arr.blade.php` — curah hujan per jam & harian
- `resources/views/beranda/categories/awqr.blade.php` — pH, suhu
- `resources/views/beranda/categories/afmr.blade.php` — flow rate, totalizer, pressure
- `resources/views/beranda/categories/awlr.blade.php` — muka air, TMA, debit
- `resources/views/beranda/categories/default.blade.php` — nilai metrik generik
- `app/Http/Controllers/AnalisaController.php` — rounding rerata/min/max/akumulasi untuk
  tampilan (hati-hati: pisahkan nilai untuk tampilan vs data mentah bila perlu)
- `resources/views/analisadata/analisa.blade.php` — tooltip chart, y-axis ticks, kartu
  total, kartu rata-rata, tegangan baterai, tabel, export CSV
- Titik lain yang ditemukan saat penyisiran (realtime monitoring, tabel/detail logger,
  API response yang menampilkan nilai sensor)

Metode penyisiran: `grep` untuk `number_format`, `->toFixed(`, `round(` di seluruh
`resources/views` dan `app/Http/Controllers`, lalu klasifikasikan tiap kemunculan sebagai
"pengukuran" (arahkan ke helper) atau "bukan" (biarkan).

## Error Handling & Edge Cases

- User belum login / `decimal_places` NULL → fallback ke `$default` (perilaku lama).
- Nilai non-numerik / null → helper tetap aman (`(float)` cast), tampil `0.0` atau tetap
  pakai guard `'-'` yang sudah ada di view (guard dipertahankan, format hanya untuk nilai
  numerik).
- Nilai `d` besar/aneh di DB → kolom `TINYINT UNSIGNED`, praktis dibatasi 0–255; nilai
  wajar 0–4. Tidak ada validasi UI karena setting diisi manual via DB untuk saat ini.

## Testing

- **Unit:** `fmt_ukur()` — `d` override vs default; input int/float/string/null.
- **Manual/verifikasi:** login sebagai `spam_wosusokas` (di lokal, DB dev bila ada) →
  beranda & analisa menampilkan 1 desimal; login user lain → tidak berubah.
- Regressi: bandingkan tampilan sebelum/sesudah untuk user tanpa setting (harus identik).

## Yang TIDAK Dikerjakan (YAGNI)

- Tidak ada halaman UI untuk mengatur `decimal_places` (diisi manual via DB untuk sekarang).
- Tidak mengubah persentase, count, koordinat.
- Tidak refactor format yang tidak berkaitan dengan pengukuran.
