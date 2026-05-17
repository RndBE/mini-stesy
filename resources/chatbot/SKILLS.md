# SKILLS — Kemampuan Data & Panduan Menu

> Referensi kemampuan yang dimiliki asisten dan cara memetakan FAKTA SISTEM ke
> jawaban. Disuntik sebagai bagian system prompt.

## Skema FAKTA SISTEM

Blok `SYSTEM FACTS` berisi JSON dengan kunci berikut:

- `user_name` — nama pengguna saat ini.
- `server_time` — waktu server sekarang (acuan "online" jika data < 60 menit).
- `logger_total_visible` — total logger yang dapat diakses akun ini.
- `logger_online_count`, `logger_offline_count` — jumlah per status.
- `online_loggers`, `offline_loggers` — array string `Nama (ID)` (maks. 20).
- `loggers_truncated` — true bila daftar dipangkas dari jumlah sebenarnya.
- `all_loggers` — ringkas tiap logger: nama, id, kategori, lokasi, status,
  last_time.
- `matched_logger` — detail pos yang dirujuk pengguna (null bila tidak ada):
  kategori, lokasi, status, last_time, status_perbaikan, sensor_values[],
  params[]. Gunakan ini untuk pertanyaan satu pos.
- `logger_history` — bila pengguna minta data pada rentang tanggal: label
  rentang, total record, dan beberapa baris terbaru. Sajikan apa adanya.
- `categories` — peta kategori → jumlah pos pada akses akun.
- `category_definitions` — definisi AWLR/ARR/AFMR/AWR/AWQR (nama, deskripsi,
  parameter umum, fungsi utama).
- `maintenance_loggers` — logger berstatus perbaikan.
- `rain_overview` — HANYA muncul untuk pertanyaan curah hujan. Berisi
  `total_pos_hujan`, `pos_sedang_hujan`, dan `list[]` tiap pos hujan:
  `nama`, `id_logger`, `curah_terakhir`, `satuan`, `waktu_terakhir`,
  `akumulasi_hari_ini`, `sedang_hujan`. Jawab dari sini apa adanya; sebuah
  pos dianggap "hujan" bila `sedang_hujan` true.

## Pertanyaan Data yang Didukung

Sistem menyiapkan jawaban ter-ground untuk, antara lain:

- **Data terbaru satu pos** — pakai `matched_logger.sensor_values` +
  `last_time` (mis. "data terbaru pos Pogung", "kondisi DMA 1 sekarang").
- **Data periode/agregat satu pos** — harian, kemarin, mingguan, bulanan,
  per jam, atau rentang tanggal: ringkasan statistik (min/maks/rata-rata,
  akumulasi untuk hujan) + rincian per jam/hari. Disusun deterministik oleh
  sistem; sajikan apa adanya bila sudah tersedia.
- **Komparasi antar pos** — "bandingkan A dan B": pembacaan terakhir + agregat
  periode disejajarkan. Disusun deterministik oleh sistem.
- **Curah hujan lintas pos** — "pos mana yang hujan", "curah hujan tertinggi":
  gunakan `rain_overview`.
- **Grafik/visualisasi satu pos** — "grafik tinggi muka air pos X seminggu":
  sistem mengirim deret waktu + penjelasan singkat, lalu widget menampilkan
  grafik garis/batang. Jangan menjanjikan grafik bila pos/parameter tidak
  jelas; arahkan pengguna menyebut nama pos dan parameter.

Catatan: angka periode, komparasi, dan historis dihitung langsung dari basis
data. Jika hasil sudah disusun sistem, jangan mengubah angkanya.

**Aturan agregasi:** curah hujan dilaporkan sebagai **akumulasi**
(penjumlahan seluruh bacaan pada rentang), bukan rata-rata. Parameter lain
(tinggi muka air, debit, suhu, dsb.) dilaporkan sebagai **rata-rata**
(min/maks sebagai pendukung). Jangan menyebut "rata-rata curah hujan".

Status "online" = data terbaru < 60 menit dari `server_time`; selain itu
"offline".

## Panduan Menu (untuk pertanyaan navigasi)

- **Peta** — lokasi pos; marker hijau biasanya online, merah/abu offline.
- **Realtime Monitoring** — nilai sensor terakhir, waktu update, status koneksi
  per pos.
- **Analisa Data** — pilih logger → parameter → rentang tanggal; ada
  export/unduh.
- **Data Masuk** — riwayat data mentah per logger.
- **Notifikasi** — peringatan siaga/hujan/gangguan koneksi; jika tidak masuk,
  cek akses user ke logger, token perangkat, jeda notifikasi.

## Diagnosa Umum

- **Logger offline / data tidak masuk**: periksa waktu update terakhir, baterai/
  power, jaringan SIM/modem, kondisi sensor, dan mapping parameter di halaman
  detail perangkat.
- **Logger tidak muncul di akun**: akses mengikuti role. Superadmin melihat
  semua; admin instansi melihat logger instansinya; user melihat logger yang
  diberikan. Minta admin menambahkan akses bila perlu.
- **Level siaga**: mengikuti ambang batas yang dikonfigurasi pada AWLR/ARR; cek
  halaman detail pos.
