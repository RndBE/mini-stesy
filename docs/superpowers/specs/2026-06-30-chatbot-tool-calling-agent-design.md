# Desain: Chatbot Agent Berbasis Tool-Calling + Streaming (gaya OpenClaw)

- **Tanggal:** 2026-06-30
- **Status:** Disetujui untuk perencanaan
- **Konteks repo:** `mini-stesy` (Laravel) — fitur "STESY Assistant"

## 1. Latar Belakang & Masalah

Chatbot sekarang memakai rantai **fast-path berbasis regex** di
`app/Http/Controllers/ChatbotController.php` (~1900 baris):
`isGreetingMessage`, `isComparisonQuestion`, `isChartQuestion`,
`isRainQuestion`, `isLoggerListQuestion`, dst. Tiap intent dideteksi pola kata,
lalu dijawab dengan format template deterministik; sisanya dilempar ke OpenAI
dengan seluruh konteks ("SYSTEM FACTS") disuntik ke system prompt sekaligus.

Masalah yang ingin diatasi:

1. **Jawaban terasa kaku** — banyak balasan berasal dari template, bukan bahasa
   natural.
2. **Deteksi intent rapuh** — bergantung kecocokan kata kunci; salah ketik /
   frasa di luar pola jatuh ke jalur yang salah.
3. **Controller gemuk** — satu kelas menampung orchestrasi, deteksi intent,
   query DB, format, dan integrasi AI.
4. **Terasa lambat** — balasan dikirim sebagai satu JSON penuh (tanpa
   streaming).

## 2. Tujuan

- **Cepat saat menjawab:** jawaban terasa instan via **streaming**.
- **Akurat saat mengambil data:** semua angka/nama/ID/grafik berasal dari DB
  lewat **tool deterministik**, GPT tidak pernah mengarang.
- **Luwes:** pemahaman intent diserahkan ke GPT (bukan regex).
- **Bersih:** orchestrasi dipecah jadi unit fokus yang mudah ditest.

### Non-Tujuan (YAGNI)

- Bukan platform multi-channel (WhatsApp/Telegram/dll) seperti OpenClaw penuh.
- Tidak ada voice / companion app / memori embedding semantik.
- Tidak mengubah model/penyedia AI (tetap OpenAI via konfig existing).
- Tidak mengubah skema basis data atau logika perhitungan sensor.

## 3. Acuan Arsitektur (OpenClaw → STESY)

Pola OpenClaw yang diadopsi (diskalakan ke satu aplikasi Laravel):

| Pola OpenClaw | Penerapan di STESY |
|---|---|
| Control plane / agent runtime turn-based | `ChatbotAgent` (loop GPT→tool→GPT) |
| Tool lifecycle: registrasi → invokasi → eksekusi + failure recovery | `ToolRegistry` + kelas `Tools/*` |
| Streaming replies / progress visibility | Endpoint SSE + frontend mengalir |
| Context engine (compaction / token pressure) | `ContextEngine` (konteks ramping + pangkas history) |
| Provider execution | `ProviderClient` (panggilan LLM + stream) |

## 4. Arsitektur

### 4.1 Struktur kode baru

```
app/Services/Chatbot/
├── ChatbotAgent.php        # orchestrator: loop turn GPT→tool→GPT
├── ToolRegistry.php        # registrasi tool + ekspor skema ke OpenAI
├── ContextEngine.php       # rakit konteks ringan + pangkas history
├── ProviderClient.php      # panggilan OpenAI (non-stream pass-1, stream pass-2)
└── Tools/
    ├── ChatbotTool.php          # interface/abstrak: name(), schema(), run($args, $user)
    ├── ListLoggersTool.php      # status & daftar online/offline
    ├── LoggerDetailTool.php     # snapshot terbaru satu pos
    ├── CompareLoggersTool.php   # komparasi antar pos
    ├── LoggerHistoryTool.php    # agregat/historis satu pos
    ├── LoggerChartTool.php      # deret waktu + payload widget grafik
    └── RainOverviewTool.php     # ikhtisar curah hujan lintas pos
```

`ChatbotController` menjadi tipis: validasi request → delegasi ke `ChatbotAgent`
→ kembalikan respons (stream atau JSON).

**Reuse:** logika perhitungan existing di `ChatbotController`
(`formatLoggerComparison`, `formatLoggerHistoricalData`, `buildLoggerChart`,
`formatLoggerSummary`, `rainOverview`, `resolveLoggerMention(s)`,
penentuan online/offline, agregasi hujan=SUM/lainnya=AVG) **dipindahkan atau
dibungkus** ke kelas Tool terkait — bukan ditulis ulang. Helper bersama yang
masih dipakai lintas tool (mis. resolusi nama pos, snapshot sensor, family
routing) ditempatkan agar dapat diakses tool (helper internal / kelas dukung).

### 4.2 Kontrak Tool

Setiap tool mengimplementasikan `ChatbotTool`:

- `name(): string` — nama unik (mis. `get_logger_history`).
- `schema(): array` — JSON Schema OpenAI (`type:function`, parameter + deskripsi
  dalam Bahasa Indonesia agar GPT memilih argumen tepat).
- `run(array $args, User $user): array` — eksekusi deterministik **ter-scope
  `forUser($user)`**, kembalikan:
  - `text` — ringkasan ter-ground untuk diberikan ke GPT.
  - `data?` — data terstruktur opsional (dipakai untuk grounding tambahan).
  - `chart?` — HANYA `LoggerChartTool`: payload widget (`type/labels/values/
    param/unit/agg/granularity/title`) yang dilampirkan ke respons frontend.

#### Skema argumen ringkas

| Tool | Argumen |
|---|---|
| `list_loggers` | `status` enum(`online`,`offline`,`all`) default `all` |
| `get_logger_detail` | `logger` string (nama/ID) |
| `compare_loggers` | `loggers` string[] (≥2), `date_range?` |
| `get_logger_history` | `logger` string, `date_range`, `granularity?` enum(`hourly`,`daily`) |
| `get_logger_chart` | `logger` string, `date_range?`, `granularity?` |
| `rain_overview` | — |

`date_range` diterima sebagai frasa natural / tanggal; backend menormalkan lewat
helper existing (`requestedDateRangeFromMessage` / setara). Bila tool gagal
me-resolve pos atau rentang, ia mengembalikan `text` yang menjelaskan datanya
tidak tersedia (failure recovery), bukan melempar exception ke loop.

### 4.3 Agent loop (`ChatbotAgent::run`)

1. `ContextEngine` merakit pesan: persona (SOUL+AGENT+SKILLS) + **konteks
   ringan** (lihat 4.4) + history (≤8 turn) + pesan user.
2. **Pass-1** → `ProviderClient` memanggil OpenAI **non-stream** dengan
   `tools` dari `ToolRegistry` dan `tool_choice:auto`.
3. Jika balasan berisi `tool_calls`: eksekusi tiap tool via registry
   (ter-scope user). Lampirkan hasil sebagai pesan `role:tool` (berisi `text`).
   Tangkap `chart` bila ada.
   - **Failure recovery:** tool yang error → pesan tool berisi keterangan gagal;
     loop tetap lanjut.
4. **Pass-2** → `ProviderClient` memanggil OpenAI **streaming** untuk merangkai
   jawaban final dari hasil tool.
5. **Batas putaran tool = 1** (maksimum 2 panggilan LLM) untuk menjaga latensi.
   Bila GPT tak meminta tool (mis. sapaan), Pass-1 langsung berisi jawaban →
   tetap di-stream ke klien (1 round trip).

### 4.4 Context engine (token pressure)

Konteks yang **selalu** disuntik diramping menjadi:

- `user_name`, `server_time`.
- `logger_total_visible`, jumlah online/offline.
- daftar `nama (id)` logger yang dapat diakses (untuk pencocokan GPT; dipangkas
  + flag `truncated` bila banyak).
- `category_definitions` (statik, kecil).

Data berat (snapshot per pos, agregat periode, deret grafik, ikhtisar hujan)
**tidak** lagi disuntik di awal — diambil on-demand lewat tool. Efek: prompt
lebih kecil → lebih cepat & lebih murah. History tetap dibatasi 8 turn terakhir
(perilaku existing).

### 4.5 Streaming (SSE)

- **Endpoint baru:**
  - Web: `POST /chatbot/stream` (middleware `auth`) → `StreamedResponse`
    (`text/event-stream`).
  - Mobile: `POST /v1/mobile/chatbot/stream` (Sanctum).
- **Format event SSE:**
  - `event: token` — potongan teks jawaban (Pass-2 streaming).
  - `event: chart` — payload widget grafik (dikirim sekali bila tool grafik
    dipanggil, sebelum/sesudah token).
  - `event: done` — penanda selesai (+ `source`).
  - `event: error` — pesan sopan bila gagal.
- **Endpoint lama tetap:** `POST /chatbot/ask` dan
  `POST /v1/mobile/chatbot/ask` dipertahankan (non-stream, balas `{reply,
  source, configured, chart}`) memakai `ChatbotAgent` yang sama tanpa streaming,
  demi kompatibilitas klien existing.
- **Frontend** (`resources/views/partials/chatbot.blade.php`, Alpine):
  konsumsi SSE pada endpoint stream; render teks mengalir; saat `event: chart`
  diterima, render Chart.js seperti sekarang (`renderChart`). Fallback ke
  `/chatbot/ask` bila streaming gagal/diblokir.

### 4.6 Persona

`resources/chatbot/AGENT.md` & `SKILLS.md` diperbarui: dari instruksi
"gunakan SYSTEM FACTS yang disuntik" menjadi "panggil tool untuk mengambil
data; jangan mengarang angka/nama; bila tool melaporkan data tidak ada,
sampaikan apa adanya". `ChatbotPersona::systemPrompt` menerima konteks ringan
(bukan seluruh fakta). Aturan gaya (formal, ringkas, ≤200 kata, tidak menyebut
AI/model/prompt) tetap.

## 5. Konfigurasi

Memakai konfig existing `config/services.php` → `ai_chatbot`
(`endpoint`, `key`, `model` = `gpt-5`, `verify_ssl`). Tidak ada env baru wajib.
Catatan: model harus mendukung tool-calling + streaming pada endpoint
`chat/completions` (gpt-5 mendukung).

## 6. Error Handling & Fallback

- **AI tidak terkonfigurasi** (`key`/`model`/`endpoint` kosong) → jalur
  deterministik `composeGroundedFallback()` dipertahankan (dipindah ke layanan
  agar dapat dipanggil tanpa AI).
- **GPT timeout / provider error** → event `error` (stream) atau
  `composeGroundedFallback` (non-stream); tidak membocorkan detail teknis.
- **Tool error** → failure recovery di loop (lihat 4.3).
- **Timeout:** per panggilan LLM ≤25s (existing); total maksimum 2 panggilan.

## 7. Pertimbangan Akses & Keamanan

- Semua tool query lewat scope `forUser()` (hak akses per role tetap).
- Endpoint web tetap `auth` + CSRF; mobile tetap Sanctum.
- Tidak ada penulisan DB; chatbot read-only.

## 8. Pengujian

- **Unit per tool:** argumen valid/invalid, pos tidak ditemukan, rentang
  kosong, agregasi hujan=SUM vs lainnya=AVG, scope `forUser`.
- **ToolRegistry:** ekspor skema, dispatch nama tool, tool tak dikenal.
- **ChatbotAgent (provider di-mock):** alur tanpa tool (sapaan), alur 1 tool,
  alur tool error → recovery, cap putaran tool.
- **ContextEngine:** pemangkasan history, truncation daftar logger.
- **Endpoint:** SSE menghasilkan urutan event benar; `/ask` lama tetap balas
  bentuk JSON lama; akses (auth/Sanctum) tertegak.
- **Fallback:** AI mati → balasan deterministik.

## 9. Rencana Migrasi (urutan implementasi kasar)

1. Ekstraksi: pindahkan helper perhitungan existing ke `Tools/*` + dukungannya
   (tanpa ubah perilaku) — refactor terverifikasi.
2. `ToolRegistry` + `ChatbotTool` + skema.
3. `ContextEngine` (konteks ramping) + perbarui persona.
4. `ProviderClient` (non-stream + stream) + `ChatbotAgent` (loop, cap, recovery).
5. Endpoint `/chatbot/ask` (non-stream) dipindah ke agent — verifikasi paritas.
6. Endpoint SSE `/chatbot/stream` + frontend Alpine streaming + chart event.
7. Bersihkan fast-path lama dari `ChatbotController`.

## 10. Risiko

- **Latensi pertanyaan data** = 2 panggilan LLM; dimitigasi prompt ramping +
  streaming Pass-2 (terasa cepat) + cap 1 putaran tool.
- **Dukungan streaming di lingkungan host** (buffering proxy/PHP-FPM) — perlu
  `X-Accel-Buffering: no` + flush eksplisit; sediakan fallback non-stream.
- **Konsistensi tool-calling gpt-5** — `tool_choice:auto` + deskripsi argumen
  jelas; failure recovery menahan output buruk.
