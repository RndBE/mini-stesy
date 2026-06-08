# Desain: Dukungan Keluarga Sensor 50 (`t_s50`)

- **Tanggal:** 2026-06-08
- **Status:** Disetujui (menunggu review spec sebelum penyusunan rencana implementasi)
- **Pendekatan:** A — tambah keluarga tabel lebar baru `t_s50` sejajar dengan `16`/`19`, + sentralisasi konfigurasi keluarga sensor.

## 1. Latar Belakang & Masalah

Sebuah logger kini mengirim **hingga 50 sensor**, sementara penyimpanan saat ini maksimum **19**. Sensor 20–50 **hilang diam-diam**: loop parse berhenti di 19, allowlist `array_intersect_key` membuang sisanya tanpa error/log, dan tabel memang tak punya kolom `sensor20+`.

Sudah dikonfirmasi dengan pemilik sistem:

- Ke-50 nilai itu **bermakna semua** dan harus tersimpan terpisah + bisa ditampilkan.
- Plafon realistis **~50, jarang berubah**, hanya beberapa logger jenis ini → solusi keluarga tabel lebar baru (bukan normalized/JSON).

## 2. Sistem Saat Ini (ringkas)

Penyimpanan **wide-column** (1 kolom per sensor). Dua "keluarga" tabel:

| Keluarga | Tabel historis | Snapshot terakhir | Kolom |
|---|---|---|---|
| 16 | `t_s16_01` | `temp_s16_latest` | sensor1–sensor16 |
| 19 | `t_s19_01` | `temp_s19_latest` | sensor1–sensor19 |

Jalur data:

1. **Ingestion** — Logger POST ke `/datamasuk` → `DataMasukController::datamasuk` (`app/Http/Controllers/DataMasukController.php:171`). Tabel ditentukan `resolveMainTable()` (`:389`) lewat regex `/^t_s(16|19)_\d{2,}$/` (`:401`); `maxSensor` & `tableTemp` dari `str_contains($tableMain,'19')` (`:211`,`:218`). Ada **remap hardcoded** untuk logger `20092` (sensor48/49/50 → sensor14/15/16) di `:239–245`.
2. **Alokasi tabel saat simpan logger** — `DeviceController::storeDataPerangkat`/`updateDataPerangkat` (`:632`,`:688`) memanggil `allocateMainTableForSensorCount()` (`:748`) yang melakukan sharding (max 5 logger/tabel, env `MAX_LOGGER_PER_TABLE`) dan `ensureTimeseriesTableExists()` (`:828`) yang membuat shard baru runtime.
3. **Konsumsi API** — `RealtimeApiController` (1 jam), `AnalisaApiController` (historis), `PetaApiController` (snapshot + 30+ key semantik). Arti tiap `sensorN` **per-logger** via tabel `parameter_sensor.kolom_sensor → nama_parameter`; default global di `list_parameter`. `ParameterIconResolver` resolve ikon by nama parameter (tak terkait jumlah sensor).

### Titik "19/16" yang terkunci (chokepoints)

| # | Lokasi | Bentuk |
|---|---|---|
| 1 | `database/migrations/2026_01_26_072601_create_timeseries_shards_and_latest.php` | lebar tabel `t_s19_01`/`temp_s19_latest` (loop `$i<=19`), trigger, view `v_temp_latest`, seed `ts_table_pool` |
| 2 | `DataMasukController.php:211,218,401,396` | `tableTemp`, `maxSensor`, regex `(16|19)`, fallback |
| 3 | `DeviceController.php:750,751,839,714–715,72,158` | `>=19?19:16`, prefix `t_s{16,19}_`, kolom shard, cek `$sameFamily` |
| 4 | `RealtimeApiController.php:~85,161` | `>=19?19:16` biner + regex |
| 5 | `AnalisaApiController.php:85` | regex `(16|19)` |
| 6 | Model `T_s16/T_s19/Temp_16s/Temp_19s` | `$fillable` enumerasi kolom |
| 7 | `resources/views/device/data_perangkat.blade.php:335–336,573–574,698` | `<option>` 16/19 + array `sensorOptions` |
| 8 | `DeviceController.php:646,702` | validasi `in:16,19` |

## 3. Tujuan & Non-Tujuan

**Tujuan**
- Logger dengan 20–50 sensor menyimpan **seluruh** pembacaan (historis + snapshot) dan dapat dibaca ketiga API.
- Operator dapat memilih **"50 Sensor"** di pengaturan logger (`/data-perangkat`), dan saat disimpan logger diarahkan ke keluarga `t_s50`.
- Hapus duplikasi regex `(16|19)` dengan **satu sumber kebenaran**.

**Non-Tujuan (YAGNI)**
- Tidak membuat format normalized atau kolom JSON.
- Tidak menambah keluarga sensor selain 50.
- Tidak mengubah perilaku remap logger `20092`.
- Tidak menyentuh path `DeviceController::store`/`update` (`:171`/`:317`) — keduanya tak menyetel `jumlah_sensor`.
- Tidak migrasi data logger lama (16/19 tetap di tempatnya).

## 4. Desain

### 4.1 Helper terpusat: `App\Support\SensorFamily`

Satu-satunya tempat yang tahu daftar keluarga sensor. Mengganti semua cek `(16|19)` yang tersebar.

```
FAMILIES = [16, 19, 50]                      // urut menaik

familyFor(int $sensorCount): int
    // PERTAHANKAN ambang lama (>=19?19:16) lalu tambahkan tier 50 di atas 19:
    //   >=20 → 50 ; >=19 → 19 ; selain itu → 16
    // contoh: 16→16, 17→16, 18→16, 19→19, 20..50→50, >50→50
    // (UI hanya mengeset 16/19/50, jadi 17/18 tak pernah terjadi; ambang ini
    //  menjamin NOL perubahan perilaku untuk logger 16/19 yang sudah ada)

maxSensorFor(string $tableName): int
    // ambil angka keluarga via regex capture /^t_s(\d+)_\d{2,}$/ (bukan str_contains)

familyOf(string $tableName): int             // alias semantik untuk maxSensorFor
tempTableFor(string $mainTable): string      // "temp_s{family}_latest"
mainTablePrefix(int $family): string         // "t_s{family}_"
isSupportedTable(string $tableName): bool    // regex /^t_s(16|19|50)_\d{2,}$/ + Schema::hasTable
```

**Catatan bug laten yang diperbaiki:** `maxSensor = str_contains($tableMain,'19') ? 19 : 16` salah untuk `t_s50_01` (menghasilkan 16). `SensorFamily::maxSensorFor()` memperbaikinya.

### 4.2 Skema DB — migration BARU (aditif)

File baru `database/migrations/2026_06_08_xxxxxx_create_t_s50_family.php`. **Tidak** mengedit migration lama (sudah jalan di prod). Isi `up()`:

- `Schema::create('t_s50_01', ...)` — `id`, `id_logger`, `waktu`, `sensor1..sensor50` (float), index `(id_logger,waktu)` & `waktu`, FK `id_logger` → `t_logger` (pola sama `t_s19_01`).
- `Schema::create('temp_s50_latest', ...)` — `id_logger` (PK), `waktu`, `sensor1..sensor50` (float nullable), `updated_at`, index `waktu`, FK.
- Seed `ts_table_pool`: `('t_s50_01', sensor_count=50, max_logger=5, is_active=1)` — pakai `updateOrInsert` agar idempoten.
- Trigger `trg_t_s50_01_to_temp` (AFTER INSERT mirror → `temp_s50_latest`, ON DUPLICATE KEY UPDATE), pola sama trigger 19. `DROP TRIGGER IF EXISTS` dulu.
- `CREATE OR REPLACE VIEW v_temp_latest` (DROP+CREATE) dengan **cabang UNION ketiga** untuk `temp_s50_latest` (sensor1–sensor50); cabang `s16`/`s19` di-pad `NULL` hingga sensor50.

`down()`: drop view (kembalikan versi 2-cabang), drop trigger 50, drop `t_s50_01` & `temp_s50_latest`, hapus baris pool.

> **Verifikasi sebelum implementasi:** cari konsumen `v_temp_latest`. Jika tak ada yang membaca kolom >sensor19 dari view, ekspansi view ke 50 kolom bersifat opsional/kosmetik dan bisa ditunda — keputusan dicatat di rencana implementasi.

### 4.3 Model baru

- `app/Models/T_s50.php` — tabel `t_s50_01`, `$fillable` sensor1..sensor50 (pola `T_s19`).
- `app/Models/Temp_50s.php` — tabel `temp_s50_latest`, `$fillable` sensor1..sensor50 (pola `Temp_19s`).

### 4.4 Ingestion — `DataMasukController`

- `:211` `tableTemp` → `SensorFamily::tempTableFor($tableMain)`.
- `:218` `maxSensor` → `SensorFamily::maxSensorFor($tableMain)`.
- `:401` `isSupportedTable` regex → `SensorFamily::isSupportedTable()` (mencakup 50).
- `:396` fallback `resolveMainTable` → pilih `SensorFamily::mainTablePrefix(SensorFamily::familyFor($sensorCount)).'01'`.
- `:408` `buildFallbackTableName` — pertahankan swap 16↔19 lama; **tanpa** cross-family fallback untuk 50 (50 tak punya pasangan).
- Loop parse (`:222`), allowlist (`:284`), null→0 (`:288`) **otomatis** ikut ke 50 karena berbasis `$maxSensor`.
- Remap `20092` (`:239–245`) **tidak diubah**.

### 4.5 Alokasi/sharding saat simpan logger — `DeviceController`

- `allocateMainTableForSensorCount()` `:750–751` — `$normalizedSensorCount`/`$prefix` → `SensorFamily::familyFor()` & `mainTablePrefix()`.
- `ensureTimeseriesTableExists()` `:839` — `$maxSensor = $sensorCount>=19?19:16` → `SensorFamily::maxSensorFor()`/jumlah dari family (membuat 50 kolom untuk shard 50).
- `updateDataPerangkat` `:714–715` cek `$sameFamily` → bandingkan `SensorFamily::familyFor($sensorCount)` dengan `SensorFamily::familyOf($currentTable)`.
- `:72` & `:158` fallback `str_contains($d->tabel_main,'19')?19:16` → `SensorFamily::maxSensorFor()` (konsistensi tampilan).
- Validasi `:646` & `:702` `in:16,19` → `in:16,19,50`.

### 4.6 API baca

- `RealtimeApiController` `:~85` biner → `SensorFamily::familyFor()`; `:161` regex & `buildFallbackTable` → helper.
- `AnalisaApiController` `:85` regex → helper. Pembacaan kolom sudah dinamis via `parameter_sensor.kolom_sensor` → otomatis dukung 20–50.
- `PetaApiController` — pastikan `resolveLatestSnapshot()` mengarah ke `temp_s50_latest` untuk logger keluarga 50; mapping key semantik tetap via alias `parameter_sensor`.

### 4.7 UI pengaturan logger — `data_perangkat.blade.php`

- `<select>` create (`:335–336`) & edit (`:573–574`): tambah `<option value="50">50 Sensor</option>`.
- Array Alpine `sensorOptions` (`:698`): `['16','19']` → `['16','19','50']`.

### 4.8 Konfigurasi parameter (data, bukan kode)

Agar sensor20–50 tampil & berlabel di app, logger 50-sensor butuh baris `parameter_sensor` (`kolom_sensor → nama_parameter`, satuan, ikon). Slot ini di luar default `list_parameter`, diisi per-logger lewat admin/UI yang ada. `ParameterIconResolver` tak berubah.

## 5. Alur Data Setelah Perubahan

1. Operator pilih "50 Sensor" di `/data-perangkat` → `storeDataPerangkat` set `sensor_count=50`, `allocateMainTableForSensorCount(50)` → `t_s50_01` (atau shard berikutnya bila penuh).
2. Logger POST ke `/datamasuk` → `resolveMainTable` → `t_s50_01`, `maxSensor=50`, parse sensor1..50 → insert historis + upsert `temp_s50_latest`.
3. API membaca via keluarga 50; nilai dikembalikan per `kolom_sensor`; key semantik & ikon dari `parameter_sensor`.

## 6. Edge Case & Keamanan

- **DB produksi:** `.env` → MySQL prod. Migration & penyetelan `t_logger`/`parameter_sensor` dijalankan **hanya dengan konfirmasi eksplisit**. Tidak ada penulisan prod tanpa izin.
- Migration **aditif & idempoten** (`IF EXISTS`, `updateOrInsert`) — aman diulang.
- Logger lama (16/19) tak tersentuh.
- Logger 50 yang kirim health di slot tinggi (48/49/50) kini punya kolom asli → tak perlu remap.
- Shard dinamis (`t_s50_02`+) tak butuh trigger karena snapshot di-upsert manual di `datamasuk`.

## 7. Strategi Testing (TDD, di DB test — bukan prod)

- **Unit** `SensorFamily`: `familyFor(16/17/19/20/50/51)`, `maxSensorFor('t_s50_01')=50`, `tempTableFor`, `isSupportedTable`.
- **Feature** `POST /datamasuk` logger keluarga 50 dengan sensor1..50 → 50 kolom tersimpan di `t_s50_01` & `temp_s50_latest`.
- **Feature** simpan logger `jumlah_sensor=50` → `tabel_main` ber-keluarga 50 + tabel dibuat.
- **Feature** API Realtime/Analisa/Peta mengembalikan data 50-sensor.
- **Regression** logger 16/19 tetap berfungsi seperti semula.

## 8. Rencana Rollout (urut, tiap langkah konfirmasi)

1. Merge kode (helper, model, controller, blade, migration) — tanpa menyentuh prod.
2. Jalankan migration di prod **setelah konfirmasi**.
3. Set `t_logger` device baru (`sensor_count`, `tabel_main`) via UI `/data-perangkat`.
4. Konfigurasi `parameter_sensor` slot 20–50.
5. Verifikasi 1 payload nyata masuk penuh; cek tampilan app.

## 9. Daftar Berkas Tersentuh

**Baru**
- `database/migrations/2026_06_08_xxxxxx_create_t_s50_family.php`
- `app/Support/SensorFamily.php`
- `app/Models/T_s50.php`, `app/Models/Temp_50s.php`

**Diubah**
- `app/Http/Controllers/DataMasukController.php`
- `app/Http/Controllers/DeviceController.php`
- `app/Http/Controllers/Api/RealtimeApiController.php`
- `app/Http/Controllers/Api/AnalisaApiController.php`
- `app/Http/Controllers/Api/PetaApiController.php` (verifikasi `resolveLatestSnapshot`)
- `resources/views/device/data_perangkat.blade.php`
