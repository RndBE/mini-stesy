# Tingkat Siaga: Dukungan ARR (Status Hujan BMKG) di Menu yang Sama

**Tanggal:** 2026-06-17
**Status:** Disetujui (menunggu review spec)

## 1. Latar Belakang & Tujuan

Menu **"Tingkat Siaga AWLR"** saat ini hanya mengatur level siaga untuk perangkat
AWLR (tinggi muka air). Perangkat **ARR** (Automatic Rain Recorder, `id_katlogger = 2`)
juga butuh konfigurasi "tingkat status", tetapi berbasis **klasifikasi hujan BMKG**
dengan dua periode akumulasi: **per jam** dan **per hari**.

Tujuan: jadikan **satu menu** ("Tingkat Siaga") yang menampung AWLR maupun ARR,
serta mengaktifkan **notifikasi ARR** yang dipicu oleh **akumulasi curah hujan
1 jam berjalan** (bukan pembacaan sesaat seperti AWLR).

Sebagian besar fondasi data **sudah ada**; pekerjaan utamanya adalah UI editor,
endpoint update, satu kolom status baru, dan perpindahan logika notifikasi ARR
agar memakai akumulasi per jam.

## 2. Kondisi Saat Ini (yang sudah ada)

- **AWLR**: tabel `tingkat_siaga_awlr` (`id_logger`, `id_status`, `nama`, `nilai`,
  `warna`, `status`). Dikelola `TingkatSiagaAwlrController` + view
  `resources/views/tingkat-siaga-awlr/index.blade.php`. "Notif aktif" = ada baris
  level dengan `status = 1`. `jeda_notif` tersimpan di `t_logger`.
- **ARR**: tabel `klasifikasi_hujan` (`id_klasifikasi`, `logger_id`, `waktu`
  = `perjam`/`perhari`, `debit_air` = nilai akumulasi mm (nama kolom legacy),
  `intensitas` = label). Model `App\Models\Klasifikasi_hujan`. **Belum ada UI editor.**
- **Beranda** (`BerandaController`) sudah menghitung akumulasi:
  - per jam = `SUM(kolom_hujan)` untuk `waktu` dalam jam kalender berjalan
    (`startOfHour()`..`endOfHour()`);
  - per hari = `SUM(kolom_hujan)` untuk hari berjalan;
  - lalu `resolveRainStatus($loggerId, $period, $value)` mencocokkan ke
    `klasifikasi_hujan` (ambil baris dengan `debit_air` terbesar yang `<= value`).
- **Notifikasi ARR saat ini** (`DataMasukController`, cabang `id_katlogger == 2`)
  memakai **pembacaan sesaat** sensor hujan + tabel `klasifikasi_threshold`
  (per kategori) — **akan diganti** (lihat §6).

## 3. Keputusan Desain (terkonfirmasi)

| Aspek | Keputusan |
|---|---|
| Menu | Satu menu, label sidebar diubah jadi **"Tingkat Siaga"**. Path/route `/tingkat-siaga-awlr` **dipertahankan** (hindari menyentuh `AuditLogService` & audit trail). |
| Tata letak | **Satu tabel** berisi AWLR + ARR, dengan kolom **Tipe** (badge). Modal Edit menyesuaikan tipe. |
| Editor ARR | **6 intensitas BMKG tetap** (label & warna fixed). User hanya mengubah **nilai akumulasi curah hujan (mm)** per baris, untuk Per Jam & Per Hari. Bukan "debit". |
| Lingkup ARR | Ambang klasifikasi + **toggle notifikasi** + **jeda notifikasi**. |
| Pemicu notif ARR | **Akumulasi 1 jam berjalan** (jam kalender, samakan dengan Beranda). |
| Ambang mulai notif | **Hujan Sedang ke atas** (Sedang / Lebat / Sangat Lebat). |
| Penyimpanan ON/OFF | Tambah kolom `status` (tinyint, default 1) ke `klasifikasi_hujan`, mirror pola AWLR. |

### Daftar intensitas BMKG (label & warna fixed)

Urutan tetap, warna mengikuti gambar/standar:

| # | state_key | Label (intensitas) | Warna |
|---|---|---|---|
| 1 | `tidak_hujan` | Tidak Hujan | `#22C55E` (hijau) |
| 2 | `hujan_sangat_ringan` | Hujan Sangat Ringan | `#38BDF8` (cyan) |
| 3 | `hujan_ringan` | Hujan Ringan | `#2563EB` (biru) |
| 4 | `hujan_sedang` | Hujan Sedang | `#FACC15` (kuning) |
| 5 | `hujan_lebat` | Hujan Lebat | `#F97316` (oranye) |
| 6 | `hujan_sangat_lebat` | Hujan Sangat Lebat | `#EF4444` (merah) |

Nilai default ambang (mm) saat baris belum ada — mengikuti gambar referensi:

- **Per Jam:** 0 / 0.1 / 1 / 5 / 10 / 20
- **Per Hari:** 0 / 0.1 / 5 / 20 / 50 / 100

> Operator tampilan: baris pertama ditampilkan `=` (Tidak Hujan = 0), sisanya `≥`.
> Ini murni presentasi; logika klasifikasi tetap "ambil ambang terbesar yang `<= value`".

## 4. Perubahan Data

### 4.1 Migration: tambah kolom `status` ke `klasifikasi_hujan`

```php
Schema::table('klasifikasi_hujan', function (Blueprint $table) {
    $table->tinyInteger('status')->default(1)->after('intensitas');
});
```

- Default `1` agar data ARR yang sudah ada otomatis "aktif".
- **`down()`** drop kolom `status`.
- ⚠️ **Prod:** tabel target ada di DB prod yang live. Migration disiapkan, tetapi
  **eksekusi ke prod menunggu konfirmasi pemilik**. Sebelum eksekusi, verifikasi
  tabel `klasifikasi_hujan` benar-benar ada & terisi di prod (migrasi historis
  belum tentu sudah dijalankan — lih. catatan t_s50). Bila kosong untuk suatu
  logger, halaman tetap aman: baris default BMKG dibuat saat penyimpanan pertama.

### 4.2 Model

- `Klasifikasi_hujan`: tambahkan `status` ke `$fillable`, cast `status` → integer.

## 5. Komponen Backend

### 5.1 Service bersama: `App\Support\ArrRainStatus`

Memindahkan logika akumulasi + klasifikasi yang kini inline di `BerandaController`
ke satu kelas, agar **display (Beranda)** dan **notifikasi (DataMasuk)** identik
dan tidak drift.

Antarmuka:

```php
class ArrRainStatus
{
    /** SUM kolom hujan untuk jam kalender berjalan; null bila tabel/kolom invalid. */
    public static function hourlyAccumulation(t_Logger $logger): ?float;

    /** SUM kolom hujan untuk hari kalender berjalan. */
    public static function dailyAccumulation(t_Logger $logger): ?float;

    /** Cocokkan nilai ke klasifikasi_hujan (period perjam/perhari) → label intensitas. */
    public static function classify(string $loggerId, string $period, ?float $value): ?string;

    /** True bila notifikasi aktif untuk logger ini (ada baris status=1). */
    public static function notifEnabled(string $loggerId): bool;
}
```

- Memuat ulang helper yang ada (`canQueryRainTable`, deteksi kolom hujan via
  `parameter_sensor`/`params`, perhitungan window jam) ke dalam service.
- `BerandaController` direfaktor memanggil service ini (perilaku tampilan tidak berubah).

### 5.2 Controller (perluas yang ada)

`TingkatSiagaAwlrController` (nama dipertahankan; deskripsi diperluas):

- **`index()`**: query logger diperluas → AWLR **dan** ARR. Setiap row diberi
  field `tipe` (`'AWLR'` | `'ARR'`). Untuk ARR, `buildRows()` mengembalikan:
  `klasifikasi` = `{ perjam: [...], perhari: [...] }` (6 baris tiap periode,
  di-merge dengan default bila belum lengkap), `status_notifikasi_bool`
  (dari `ArrRainStatus::notifEnabled`), `jeda_notif`.
- **Query logger**: `awlrLoggersQuery()` → `loggersQuery()` yang mencakup
  `id_katlogger IN (1,2)` atau kategori `awlr`/`arr`.
- **`update($idLogger)`**: bercabang berdasarkan tipe logger:
  - **AWLR** → perilaku saat ini (simpan ke `tingkat_siaga_awlr`).
  - **ARR** → validasi & simpan ke `klasifikasi_hujan`:
    - upsert 6 baris × 2 periode berdasarkan `(logger_id, waktu, intensitas)`;
      hanya `debit_air` yang berubah (label/warna fixed dari konstanta server-side).
    - `status_notifikasi` true → set semua baris logger `status = 1`,
      simpan `jeda_notif` ke `t_logger`. False → set semua baris `status = 0`.
- Validasi ARR: `debit_air` numeric ≥ 0; periode lengkap 6 baris; nilai menaik
  (opsional, beri peringatan bila tidak monoton).

### 5.3 Routing

Tetap dua route (`tingkat-siaga-awlr.index`, `tingkat-siaga-awlr.update`).
`update` kini menangani AWLR & ARR via deteksi tipe di controller.

## 6. Notifikasi ARR (inti perubahan perilaku)

Di `DataMasukController`, cabang ARR (`id_katlogger == 2`) ditulis ulang:

1. Hitung **akumulasi jam berjalan** via `ArrRainStatus::hourlyAccumulation($logger)`.
2. Klasifikasikan via `ArrRainStatus::classify($id_logger, 'perjam', $accum)`
   terhadap `klasifikasi_hujan` (ambang yang diatur user).
3. Notifikasi dikirim hanya bila **semua** berikut benar:
   - `ArrRainStatus::notifEnabled($id_logger)` (toggle ON), **dan**
   - intensitas hasil klasifikasi termasuk **Hujan Sedang / Lebat / Sangat Lebat**, **dan**
   - aturan jeda terpenuhi (state berubah **atau** `jeda_notif` menit terlewati) —
     reuse mekanisme `logger_notification_states` yang sudah ada.
4. Judul/isi FCM: format ARR yang sudah ada
   (mis. "Peringatan Hujan Lebat - <nama_logger>"), nilai memakai akumulasi mm/jam.

Catatan: `klasifikasi_threshold` (per kategori) **tidak lagi** dipakai untuk
memicu notifikasi ARR; tetap boleh dipakai untuk ikon/warna state di Beranda
(tidak diubah pada lingkup ini).

## 7. Frontend (Blade + Alpine)

File: `resources/views/tingkat-siaga-awlr/index.blade.php` (diperluas).

- **Tabel**: tambah kolom **Tipe** (badge AWLR biru / ARR teal). Kolom
  "Level Siaga" menampilkan ringkasan sesuai tipe (AWLR: level + `m`; ARR:
  ringkas "Per Jam / Per Hari" + indikator aktif).
- **Modal Edit** memakai `x-if` bercabang `editForm.tipe`:
  - **AWLR**: editor existing (tidak diubah).
  - **ARR**: toggle notifikasi + jeda (komponen sama seperti AWLR) lalu **dua
    panel** "Tingkat Status Per Jam" dan "Tingkat Status Per Hari". Tiap panel =
    6 baris fixed: swatch warna (read-only) + label (read-only) + operator
    (`=`/`≥`) + input angka **Curah Hujan** dengan satuan **mm**.
  - Label & warna ARR tidak dapat diedit (konsisten BMKG); hanya angka mm.
- **Submit**: payload ARR = `{ tipe:'ARR', status_notifikasi, jeda_notif,
  klasifikasi: { perjam:[{intensitas,debit_air}], perhari:[...] } }` → `PUT`
  ke route yang sama; server mengabaikan label/warna kiriman demi keamanan.
- Sidebar (`resources/views/partials/sidebar.blade.php`): teks link diganti
  "Tingkat Siaga AWLR" → **"Tingkat Siaga"** (route & ikon tetap).

## 8. Audit Log

`AuditLogService` sudah mengenali `tingkat-siaga-awlr.update`. Karena route tidak
berubah, audit tetap jalan. Deskripsi audit untuk ARR ditambahkan agar entri
menyebut "klasifikasi hujan ARR" bila tipe = ARR (perubahan kecil, opsional).

## 9. Pengujian

- **Unit `ArrRainStatus`**: akumulasi jam/hari (data dummy), klasifikasi naik,
  `notifEnabled` true/false sesuai kolom `status`.
- **Feature `update` ARR**: simpan ambang, toggle ON/OFF mengubah `status`
  semua baris, validasi nilai negatif ditolak, baris default dibuat saat kosong.
- **Feature `update` AWLR**: regresi — perilaku lama tidak berubah.
- **Notifikasi**: akumulasi jam < Sedang → tidak ada notif; ≥ Sedang & toggle ON
  & jeda terpenuhi → notif terkirim; toggle OFF → tidak ada notif.
- **Beranda regresi**: status per jam/hari tetap sama setelah refactor ke service.

## 10. Risiko & Catatan

- **Prod schema (kolom `status`)**: perubahan DB live → eksekusi migration ke prod
  hanya setelah konfirmasi pemilik; verifikasi keberadaan & isi `klasifikasi_hujan`
  di prod lebih dahulu.
- **Refactor BerandaController**: pastikan hasil akumulasi & status identik
  sebelum/sesudah pindah ke service (uji regresi).
- **Konsistensi window**: notif & tampilan sama-sama pakai jam kalender berjalan,
  sehingga angka yang dinotifikasi = angka yang tampil di Beranda.
- **Out of scope**: mengubah pemicu/format notifikasi AWLR; mengubah ikon state
  ARR di Beranda; rename route/controller/file.
