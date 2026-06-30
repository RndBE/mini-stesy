# Chatbot Tool-Calling Agent + Streaming Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Ganti fast-path regex chatbot dengan agent tool-calling (GPT memilih tool → backend ambil data DB akurat → GPT merangkai), plus streaming SSE agar terasa cepat.

**Architecture:** Orchestrator (`ChatbotAgent`) menjalankan loop turn GPT→tool→GPT (maks 1 putaran tool). Kapabilitas data jadi kelas Tool di registry; logika query/format existing dipindah ke `MonitoringData` (read-only, ter-scope `forUser`). `ProviderClient` menangani panggilan OpenAI non-stream (pass-1) & stream (pass-2). Endpoint SSE baru + endpoint `/ask` lama tetap.

**Tech Stack:** Laravel (PHP 8), OpenAI `chat/completions` (model `gpt-5`, tools + streaming), Alpine.js + Chart.js (frontend), PHPUnit.

## Global Constraints

- Chatbot **read-only**: tidak ada penulisan DB. (Lihat memory: konfirmasi sebelum tulis DB prod — tidak relevan di sini karena tak ada write.)
- Semua query data lewat scope `forUser($user)` (hak akses per role wajib ditegakkan).
- Konfig AI dari `config('services.ai_chatbot')`: `endpoint`, `key`, `model`, `verify_ssl`. Tidak ada env baru wajib.
- Agregasi: curah hujan = **SUM** (akumulasi); parameter lain = **AVG**. Jangan ubah angka yang sudah dihitung sistem.
- Status "online" = data terbaru < 60 menit dari `server_time`.
- Batas history percakapan: **8 turn** terakhir. Batas putaran tool: **1** (maks 2 panggilan LLM).
- Persona/aturan gaya tetap: formal, ringkas, ≤200 kata, jangan menyebut "AI/model/prompt", jangan tampilkan galat teknis.
- Endpoint lama `POST /chatbot/ask` (web, `auth`+CSRF) & `POST /v1/mobile/chatbot/ask` (Sanctum) **tetap berfungsi** (non-stream).
- Spec acuan: `docs/superpowers/specs/2026-06-30-chatbot-tool-calling-agent-design.md`.

---

## File Structure

```
app/Services/Chatbot/
├── MonitoringData.php          # (Task 1-2) read/query/resolve/format helpers, accept User
├── ContextEngine.php           # (Task 6) rakit konteks ringan + pangkas history
├── ProviderClient.php          # (Task 7) panggilan OpenAI: chat() non-stream, stream()
├── ChatbotAgent.php            # (Task 8) loop turn GPT→tool→GPT; ask() & stream()
├── ToolRegistry.php            # (Task 4) daftar tool, ekspor skema, dispatch
└── Tools/
    ├── ChatbotTool.php         # (Task 3) interface
    ├── ListLoggersTool.php     # (Task 5)
    ├── LoggerDetailTool.php    # (Task 5)
    ├── CompareLoggersTool.php  # (Task 5)
    ├── LoggerHistoryTool.php   # (Task 5)
    ├── LoggerChartTool.php     # (Task 5)
    └── RainOverviewTool.php    # (Task 5)

app/Http/Controllers/ChatbotController.php   # (Task 9-10) jadi tipis: ask()+stream()
resources/chatbot/AGENT.md, SKILLS.md        # (Task 6) update ke model tool
resources/views/partials/chatbot.blade.php   # (Task 10) konsumsi SSE
routes/web.php, routes/api.php               # (Task 9-10) tambah route stream
tests/Unit/Chatbot/*, tests/Feature/Chatbot/*
```

---

## Task 1: Ekstrak resolusi & konteks ke `MonitoringData` (accept User)

Pindahkan helper baca-data dari `ChatbotController` ke service baru, ubah dari `Request` ke `User` agar bisa dipanggil tool. Controller mendelegasikan agar perilaku lama tak berubah (paritas).

**Files:**
- Create: `app/Services/Chatbot/MonitoringData.php`
- Modify: `app/Http/Controllers/ChatbotController.php`
- Test: `tests/Unit/Chatbot/MonitoringDataTest.php`

**Interfaces:**
- Produces:
  - `MonitoringData::context(User $user, string $message=''): array` (dari `buildMonitoringContext`)
  - `MonitoringData::resolveLogger(User $user, string $query): ?array` (dari `resolveLoggerMention`, query dipakai sebagai teks)
  - `MonitoringData::resolveLoggers(User $user, string $query, int $max=3): array` (dari `resolveLoggerMentionsMulti`)
  - `MonitoringData::dateRange(string $phrase): ?array` (dari `requestedDateRangeFromMessage`)
  - `MonitoringData::granularity(string $phrase): ?string` (dari `requestedGranularity`)
  - `MonitoringData::defaultWeekRange(): array`
  - `MonitoringData::categoryDefinitions(): array`
  - `MonitoringData::isRainfallParam(array $param): bool`
  - `MonitoringData::groundedFacts(array $context): array`

- [ ] **Step 1: Tulis test paritas resolusi pos**

```php
// tests/Unit/Chatbot/MonitoringDataTest.php
namespace Tests\Unit\Chatbot;

use App\Models\User;
use App\Services\Chatbot\MonitoringData;
use Tests\TestCase;

class MonitoringDataTest extends TestCase
{
    public function test_resolve_logger_returns_null_when_no_match(): void
    {
        $user = User::factory()->create();
        $data = app(MonitoringData::class);

        $this->assertNull($data->resolveLogger($user, 'pos yang tidak ada xyz123'));
    }

    public function test_date_range_parses_weekly_phrase(): void
    {
        $data = app(MonitoringData::class);
        $range = $data->dateRange('data seminggu terakhir');

        $this->assertIsArray($range);
        $this->assertArrayHasKey('start', $range);
        $this->assertArrayHasKey('end', $range);
    }
}
```

(Catatan: bila `User::factory` tidak tersedia, gunakan pembuatan user sesuai pola test existing repo; jalankan `grep -rn "User::factory\|new User" tests/` untuk konfirmasi pola sebelum menulis.)

- [ ] **Step 2: Jalankan test, pastikan gagal**

Run: `php artisan test --filter=MonitoringDataTest`
Expected: FAIL — class `App\Services\Chatbot\MonitoringData` not found.

- [ ] **Step 3: Buat `MonitoringData` & pindahkan method**

Buat `app/Services/Chatbot/MonitoringData.php`. **Pindahkan verbatim** dari `ChatbotController` method berikut (beserta seluruh helper privat yang hanya dipakai mereka), lalu sesuaikan tanda tangan:

- `buildMonitoringContext(Request $request, string $message='')` → `context(User $user, string $message='')`; ganti tiap `$request->user()` menjadi `$user`.
- `resolveLoggerMention(Request $request, string $message)` → `resolveLogger(User $user, string $query)`; pakai `$query` sebagai teks pencocokan; `$request->user()`→`$user`.
- `resolveLoggerMentionsMulti(Request $request, string $message, int $max=3)` → `resolveLoggers(User $user, string $query, int $max=3)`.
- `requestedDateRangeFromMessage(string)` → `dateRange(string)`.
- `requestedGranularity(string)` → `granularity(string)`.
- `defaultWeekRange()`, `categoryDefinitions()`, `isRainfallParam(array)`, `groundedFacts(array)` → public, tanpa perubahan logika.

Struktur kelas:

```php
<?php
namespace App\Services\Chatbot;

use App\Models\User;
// ...import model/Support yang dipakai method yang dipindah (mis. SensorFamily, t_Logger)

class MonitoringData
{
    public function context(User $user, string $message = ''): array { /* eks buildMonitoringContext */ }
    public function resolveLogger(User $user, string $query): ?array { /* eks resolveLoggerMention */ }
    public function resolveLoggers(User $user, string $query, int $max = 3): array { /* eks resolveLoggerMentionsMulti */ }
    public function dateRange(string $phrase): ?array { /* eks requestedDateRangeFromMessage */ }
    public function granularity(string $phrase): ?string { /* eks requestedGranularity */ }
    public function defaultWeekRange(): array { /* eks */ }
    public function categoryDefinitions(): array { /* eks */ }
    public function isRainfallParam(array $param): bool { /* eks */ }
    public function groundedFacts(array $context): array { /* eks */ }
    // + helper privat yang ikut dipindah
}
```

- [ ] **Step 4: Delegasikan dari controller (paritas sementara)**

Di `ChatbotController`, ganti badan method yang dipindah menjadi delegasi, agar `ask()` lama tetap jalan tanpa perubahan perilaku. Tambahkan dependency:

```php
public function __construct(private \App\Services\Chatbot\MonitoringData $data) {}

private function buildMonitoringContext(Request $request, string $message = ''): array
{ return $this->data->context($request->user(), $message); }

private function resolveLoggerMention(Request $request, string $message): ?array
{ return $this->data->resolveLogger($request->user(), $message); }

private function resolveLoggerMentionsMulti(Request $request, string $message, int $max = 3): array
{ return $this->data->resolveLoggers($request->user(), $message, $max); }

private function requestedDateRangeFromMessage(string $message): ?array
{ return $this->data->dateRange($message); }

private function requestedGranularity(string $message): ?string
{ return $this->data->granularity($message); }

private function defaultWeekRange(): array { return $this->data->defaultWeekRange(); }
private function categoryDefinitions(): array { return $this->data->categoryDefinitions(); }
private function isRainfallParam(array $param): bool { return $this->data->isRainfallParam($param); }
private function groundedFacts(array $context): array { return $this->data->groundedFacts($context); }
```

- [ ] **Step 5: Jalankan test + smoke existing**

Run: `php artisan test --filter=MonitoringDataTest`
Expected: PASS.
Run: `php artisan test --filter=Chatbot` (pastikan test chatbot existing, bila ada, tetap hijau).

- [ ] **Step 6: Commit**

```bash
git add app/Services/Chatbot/MonitoringData.php app/Http/Controllers/ChatbotController.php tests/Unit/Chatbot/MonitoringDataTest.php
git commit -m "refactor: extract chatbot monitoring/resolve helpers to MonitoringData"
```

---

## Task 2: Pindahkan formatter & builder ke `MonitoringData`

**Files:**
- Modify: `app/Services/Chatbot/MonitoringData.php`, `app/Http/Controllers/ChatbotController.php`
- Test: `tests/Unit/Chatbot/MonitoringDataFormatTest.php`

**Interfaces:**
- Consumes: Task 1 (`resolveLogger`, `dateRange`, `isRainfallParam`, `context`).
- Produces:
  - `summary(array $logger): string` (dari `formatLoggerSummary`)
  - `comparison(array $loggers, ?array $dateRange): string` (dari `formatLoggerComparison`)
  - `history(array $logger, array $dateRange, ?string $granularity=null): string` (dari `formatLoggerHistoricalData`)
  - `chart(array $logger, array $dateRange, string $hint='', ?string $granularity=null): ?array` (dari `buildLoggerChart`; return `['explanation'=>string,'chart'=>array]`)
  - `rainOverview(User $user): array` (dari `rainOverview`)
  - `groundedFallback(User $user, string $message, array $context): string` (dari `composeGroundedFallback`)

- [ ] **Step 1: Tulis test paritas formatter**

```php
// tests/Unit/Chatbot/MonitoringDataFormatTest.php
namespace Tests\Unit\Chatbot;

use App\Services\Chatbot\MonitoringData;
use Tests\TestCase;

class MonitoringDataFormatTest extends TestCase
{
    public function test_summary_renders_logger_name(): void
    {
        $data = app(MonitoringData::class);
        $logger = ['nama' => 'AWLR Sinduadi', 'id' => 'X1', 'kategori' => 'AWLR',
                   'lokasi' => '-', 'status' => 'online', 'last_time' => '2026-06-30 10:00:00',
                   'sensor_values' => [], 'params' => []];

        $text = $data->summary($logger);
        $this->assertStringContainsString('AWLR Sinduadi', $text);
    }
}
```

(Sesuaikan bentuk array `$logger` dengan yang dihasilkan `resolveLogger`; cek struktur aslinya di `MonitoringData::resolveLogger` sebelum menetapkan key.)

- [ ] **Step 2: Jalankan test, pastikan gagal**

Run: `php artisan test --filter=MonitoringDataFormatTest`
Expected: FAIL — method `summary` belum ada.

- [ ] **Step 3: Pindahkan formatter**

Pindahkan verbatim dari `ChatbotController` ke `MonitoringData` (public), sesuaikan tanda tangan:
- `formatLoggerSummary` → `summary`
- `formatLoggerComparison` → `comparison`
- `formatLoggerHistoricalData` → `history`
- `buildLoggerChart(array,array,string,?string)` → `chart(array,array,string,?string)` (parameter ke-3 `$message` jadi `$hint`)
- `rainOverview(Request)` → `rainOverview(User $user)` (`$request->user()`→`$user`)
- `composeGroundedFallback(Request,string,array)` → `groundedFallback(User,string,array)`

Ganti rujukan internal antar-method ke `$this->...` versi baru (mis. di dalam `groundedFallback` panggilan `isComparisonQuestion` tetap di controller untuk sekarang — lihat catatan). Bila `groundedFallback`/`comparison` memanggil deteksi intent (`isComparisonQuestion`/`isRainQuestion`), **biarkan deteksi itu tetap di controller** dan pindahkan hanya bagian pembentukan data; bila kopling terlalu erat, pindahkan juga method `isComparisonQuestion`/`isRainQuestion`/`isChartQuestion`/`isGreetingMessage`/`isLoggerListQuestion` ke `MonitoringData` sebagai public dan delegasikan dari controller (sama pola Task 1 Step 4).

- [ ] **Step 4: Delegasikan dari controller**

```php
private function formatLoggerSummary(array $logger): string
{ return $this->data->summary($logger); }

private function formatLoggerComparison(array $loggers, ?array $dateRange): string
{ return $this->data->comparison($loggers, $dateRange); }

private function formatLoggerHistoricalData(array $logger, array $dateRange, ?string $granularity = null): string
{ return $this->data->history($logger, $dateRange, $granularity); }

private function buildLoggerChart(array $logger, array $dateRange, string $message, ?string $granularity = null): ?array
{ return $this->data->chart($logger, $dateRange, $message, $granularity); }

private function rainOverview(Request $request): array
{ return $this->data->rainOverview($request->user()); }

private function composeGroundedFallback(Request $request, string $message, array $context): string
{ return $this->data->groundedFallback($request->user(), $message, $context); }
```

- [ ] **Step 5: Jalankan test**

Run: `php artisan test --filter=MonitoringData`
Expected: PASS (Task 1 + Task 2 test hijau).

- [ ] **Step 6: Commit**

```bash
git add app/Services/Chatbot/MonitoringData.php app/Http/Controllers/ChatbotController.php tests/Unit/Chatbot/MonitoringDataFormatTest.php
git commit -m "refactor: move chatbot formatters/builders to MonitoringData"
```

---

## Task 3: Interface `ChatbotTool`

**Files:**
- Create: `app/Services/Chatbot/Tools/ChatbotTool.php`
- Test: (tidak ada — interface murni; diuji lewat implementasi Task 5)

**Interfaces:**
- Produces: kontrak `name(): string`, `schema(): array`, `run(array $args, User $user): array`.

- [ ] **Step 1: Buat interface**

```php
<?php
namespace App\Services\Chatbot\Tools;

use App\Models\User;

interface ChatbotTool
{
    /** Nama unik tool, mis. "get_logger_history". */
    public function name(): string;

    /** Skema fungsi OpenAI: ['type'=>'function','function'=>[...]]. */
    public function schema(): array;

    /**
     * Eksekusi deterministik, ter-scope $user.
     * Return: ['text'=>string, 'data'?=>array, 'chart'?=>array].
     */
    public function run(array $args, User $user): array;
}
```

- [ ] **Step 2: Commit**

```bash
git add app/Services/Chatbot/Tools/ChatbotTool.php
git commit -m "feat: add ChatbotTool interface"
```

---

## Task 4: `ToolRegistry`

**Files:**
- Create: `app/Services/Chatbot/ToolRegistry.php`
- Test: `tests/Unit/Chatbot/ToolRegistryTest.php`

**Interfaces:**
- Consumes: `ChatbotTool` (Task 3).
- Produces:
  - `register(ChatbotTool $tool): void`
  - `schemas(): array` — array skema semua tool (untuk payload OpenAI `tools`).
  - `run(string $name, array $args, User $user): array` — dispatch; tool tak dikenal → `['text'=>'...tidak tersedia...']`.

- [ ] **Step 1: Tulis test**

```php
// tests/Unit/Chatbot/ToolRegistryTest.php
namespace Tests\Unit\Chatbot;

use App\Models\User;
use App\Services\Chatbot\ToolRegistry;
use App\Services\Chatbot\Tools\ChatbotTool;
use Tests\TestCase;

class ToolRegistryTest extends TestCase
{
    private function fakeTool(): ChatbotTool
    {
        return new class implements ChatbotTool {
            public function name(): string { return 'echo'; }
            public function schema(): array { return ['type'=>'function','function'=>['name'=>'echo']]; }
            public function run(array $args, User $user): array { return ['text'=>'ran:'.($args['x'] ?? '')]; }
        };
    }

    public function test_schemas_lists_registered_tools(): void
    {
        $r = new ToolRegistry();
        $r->register($this->fakeTool());
        $this->assertCount(1, $r->schemas());
        $this->assertSame('echo', $r->schemas()[0]['function']['name']);
    }

    public function test_run_dispatches_by_name(): void
    {
        $r = new ToolRegistry();
        $r->register($this->fakeTool());
        $out = $r->run('echo', ['x'=>'hi'], User::factory()->make());
        $this->assertSame('ran:hi', $out['text']);
    }

    public function test_run_unknown_tool_returns_unavailable(): void
    {
        $r = new ToolRegistry();
        $out = $r->run('nope', [], User::factory()->make());
        $this->assertArrayHasKey('text', $out);
    }
}
```

- [ ] **Step 2: Jalankan test, pastikan gagal**

Run: `php artisan test --filter=ToolRegistryTest`
Expected: FAIL — `ToolRegistry` not found.

- [ ] **Step 3: Implementasi**

```php
<?php
namespace App\Services\Chatbot;

use App\Models\User;
use App\Services\Chatbot\Tools\ChatbotTool;

class ToolRegistry
{
    /** @var array<string,ChatbotTool> */
    private array $tools = [];

    public function register(ChatbotTool $tool): void
    {
        $this->tools[$tool->name()] = $tool;
    }

    /** @return array<int,array> */
    public function schemas(): array
    {
        return array_values(array_map(fn (ChatbotTool $t) => $t->schema(), $this->tools));
    }

    public function run(string $name, array $args, User $user): array
    {
        $tool = $this->tools[$name] ?? null;
        if (! $tool) {
            return ['text' => "Tool \"{$name}\" tidak tersedia."];
        }

        try {
            return $tool->run($args, $user);
        } catch (\Throwable $e) {
            report($e);
            return ['text' => 'Maaf, data untuk permintaan itu tidak dapat diambil saat ini.'];
        }
    }
}
```

- [ ] **Step 4: Jalankan test**

Run: `php artisan test --filter=ToolRegistryTest`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add app/Services/Chatbot/ToolRegistry.php tests/Unit/Chatbot/ToolRegistryTest.php
git commit -m "feat: add chatbot ToolRegistry (schemas + dispatch + failure recovery)"
```

---

## Task 5: Enam kelas Tool

Setiap tool membungkus `MonitoringData`. Buat satu per langkah dengan test masing-masing; commit di akhir tiap tool. Semua tool: `__construct(private MonitoringData $data) {}`.

**Files:**
- Create: `app/Services/Chatbot/Tools/{ListLoggers,LoggerDetail,CompareLoggers,LoggerHistory,LoggerChart,RainOverview}Tool.php`
- Test: `tests/Unit/Chatbot/Tools/*Test.php`

**Interfaces:**
- Consumes: `ChatbotTool` (Task 3), `MonitoringData` (Task 1-2).
- Produces (nama tool): `list_loggers`, `get_logger_detail`, `compare_loggers`, `get_logger_history`, `get_logger_chart`, `rain_overview`.

### 5a. `LoggerDetailTool` (pola acuan)

- [ ] **Step 1: Test**

```php
// tests/Unit/Chatbot/Tools/LoggerDetailToolTest.php
namespace Tests\Unit\Chatbot\Tools;

use App\Models\User;
use App\Services\Chatbot\MonitoringData;
use App\Services\Chatbot\Tools\LoggerDetailTool;
use Mockery;
use Tests\TestCase;

class LoggerDetailToolTest extends TestCase
{
    public function test_returns_summary_text_when_logger_found(): void
    {
        $logger = ['nama'=>'AWLR Sinduadi','id'=>'X1'];
        $data = Mockery::mock(MonitoringData::class);
        $data->shouldReceive('resolveLogger')->andReturn($logger);
        $data->shouldReceive('summary')->with($logger)->andReturn('Pos AWLR Sinduadi: ...');

        $tool = new LoggerDetailTool($data);
        $out = $tool->run(['logger'=>'Sinduadi'], User::factory()->make());

        $this->assertStringContainsString('AWLR Sinduadi', $out['text']);
    }

    public function test_returns_not_found_text_when_missing(): void
    {
        $data = Mockery::mock(MonitoringData::class);
        $data->shouldReceive('resolveLogger')->andReturn(null);

        $tool = new LoggerDetailTool($data);
        $out = $tool->run(['logger'=>'zzz'], User::factory()->make());

        $this->assertStringContainsString('tidak ditemukan', strtolower($out['text']));
    }
}
```

- [ ] **Step 2: Jalankan, pastikan gagal**

Run: `php artisan test --filter=LoggerDetailToolTest`
Expected: FAIL — class not found.

- [ ] **Step 3: Implementasi**

```php
<?php
namespace App\Services\Chatbot\Tools;

use App\Models\User;
use App\Services\Chatbot\MonitoringData;

class LoggerDetailTool implements ChatbotTool
{
    public function __construct(private MonitoringData $data) {}

    public function name(): string { return 'get_logger_detail'; }

    public function schema(): array
    {
        return ['type'=>'function','function'=>[
            'name'=>$this->name(),
            'description'=>'Ambil kondisi/pembacaan sensor terbaru satu pos/logger berdasarkan nama atau ID.',
            'parameters'=>['type'=>'object','properties'=>[
                'logger'=>['type'=>'string','description'=>'Nama atau ID pos, mis. "AWLR Sinduadi" atau "X1".'],
            ],'required'=>['logger']],
        ]];
    }

    public function run(array $args, User $user): array
    {
        $logger = $this->data->resolveLogger($user, (string)($args['logger'] ?? ''));
        if (! $logger) {
            return ['text' => 'Pos yang dimaksud tidak ditemukan pada akses akun ini. Periksa ejaan atau minta akses ke admin.'];
        }
        return ['text' => $this->data->summary($logger), 'data' => $logger];
    }
}
```

- [ ] **Step 4: Jalankan test** — Run: `php artisan test --filter=LoggerDetailToolTest` → PASS.

### 5b. `ListLoggersTool`

- [ ] **Step 5: Implementasi + test**

```php
<?php
namespace App\Services\Chatbot\Tools;

use App\Models\User;
use App\Services\Chatbot\MonitoringData;

class ListLoggersTool implements ChatbotTool
{
    public function __construct(private MonitoringData $data) {}
    public function name(): string { return 'list_loggers'; }

    public function schema(): array
    {
        return ['type'=>'function','function'=>[
            'name'=>$this->name(),
            'description'=>'Daftar & jumlah logger beserta status online/offline pada akses akun.',
            'parameters'=>['type'=>'object','properties'=>[
                'status'=>['type'=>'string','enum'=>['online','offline','all'],'description'=>'Filter status; default all.'],
            ]],
        ]];
    }

    public function run(array $args, User $user): array
    {
        $ctx = $this->data->context($user);
        $facts = $this->data->groundedFacts($ctx);
        $status = $args['status'] ?? 'all';

        $payload = [
            'total' => $facts['logger_total_visible'] ?? 0,
            'online_count' => $facts['logger_online_count'] ?? 0,
            'offline_count' => $facts['logger_offline_count'] ?? 0,
        ];
        if ($status !== 'offline') { $payload['online_loggers'] = $facts['online_loggers'] ?? []; }
        if ($status !== 'online') { $payload['offline_loggers'] = $facts['offline_loggers'] ?? []; }
        $payload['truncated'] = $facts['loggers_truncated'] ?? false;

        return ['text' => json_encode($payload, JSON_UNESCAPED_UNICODE), 'data' => $payload];
    }
}
```

Test (`ListLoggersToolTest`): mock `MonitoringData::context` & `groundedFacts`, assert `text` memuat jumlah online/offline.

- [ ] **Step 6: Jalankan** — `php artisan test --filter=ListLoggersToolTest` → PASS.

### 5c. `CompareLoggersTool`

- [ ] **Step 7: Implementasi + test**

```php
<?php
namespace App\Services\Chatbot\Tools;

use App\Models\User;
use App\Services\Chatbot\MonitoringData;

class CompareLoggersTool implements ChatbotTool
{
    public function __construct(private MonitoringData $data) {}
    public function name(): string { return 'compare_loggers'; }

    public function schema(): array
    {
        return ['type'=>'function','function'=>[
            'name'=>$this->name(),
            'description'=>'Bandingkan dua atau lebih pos: pembacaan terakhir + agregat periode.',
            'parameters'=>['type'=>'object','properties'=>[
                'loggers'=>['type'=>'array','items'=>['type'=>'string'],'description'=>'Daftar nama/ID pos (minimal 2).'],
                'date_range'=>['type'=>'string','description'=>'Frasa periode opsional, mis. "minggu ini", "1-7 Juni".'],
            ],'required'=>['loggers']],
        ]];
    }

    public function run(array $args, User $user): array
    {
        $names = array_values(array_filter(array_map('strval', (array)($args['loggers'] ?? []))));
        if (count($names) < 2) {
            return ['text' => 'Sebutkan minimal dua pos untuk dibandingkan.'];
        }
        $loggers = $this->data->resolveLoggers($user, implode(' , ', $names), max(count($names), 3));
        if (count($loggers) < 2) {
            return ['text' => 'Pos yang dibandingkan tidak cukup ditemukan pada akses akun ini.'];
        }
        $range = isset($args['date_range']) ? $this->data->dateRange((string)$args['date_range']) : null;
        return ['text' => $this->data->comparison($loggers, $range)];
    }
}
```

Test (`CompareLoggersToolTest`): args `loggers` < 2 → pesan minta dua pos; resolveLoggers mengembalikan 2 → `comparison` dipanggil.

- [ ] **Step 8: Jalankan** — `php artisan test --filter=CompareLoggersToolTest` → PASS.

### 5d. `LoggerHistoryTool`

- [ ] **Step 9: Implementasi + test**

```php
<?php
namespace App\Services\Chatbot\Tools;

use App\Models\User;
use App\Services\Chatbot\MonitoringData;

class LoggerHistoryTool implements ChatbotTool
{
    public function __construct(private MonitoringData $data) {}
    public function name(): string { return 'get_logger_history'; }

    public function schema(): array
    {
        return ['type'=>'function','function'=>[
            'name'=>$this->name(),
            'description'=>'Agregat/historis satu pos pada rentang waktu (min/maks/rata-rata; hujan=akumulasi).',
            'parameters'=>['type'=>'object','properties'=>[
                'logger'=>['type'=>'string','description'=>'Nama/ID pos.'],
                'date_range'=>['type'=>'string','description'=>'Frasa periode, mis. "kemarin", "1-7 Juni", "bulan ini".'],
                'granularity'=>['type'=>'string','enum'=>['hourly','daily'],'description'=>'Granularitas opsional.'],
            ],'required'=>['logger','date_range']],
        ]];
    }

    public function run(array $args, User $user): array
    {
        $logger = $this->data->resolveLogger($user, (string)($args['logger'] ?? ''));
        if (! $logger) { return ['text' => 'Pos tidak ditemukan pada akses akun ini.']; }
        $range = $this->data->dateRange((string)($args['date_range'] ?? ''));
        if (! $range) { return ['text' => 'Rentang waktu tidak dapat dipahami. Sebutkan tanggal atau periode yang jelas.']; }
        $gran = isset($args['granularity']) ? (string)$args['granularity'] : null;
        return ['text' => $this->data->history($logger, $range, $gran)];
    }
}
```

Test (`LoggerHistoryToolTest`): logger null → not found; range null → pesan rentang; happy path → `history` dipanggil.

- [ ] **Step 10: Jalankan** — `php artisan test --filter=LoggerHistoryToolTest` → PASS.

### 5e. `LoggerChartTool`

- [ ] **Step 11: Implementasi + test**

```php
<?php
namespace App\Services\Chatbot\Tools;

use App\Models\User;
use App\Services\Chatbot\MonitoringData;

class LoggerChartTool implements ChatbotTool
{
    public function __construct(private MonitoringData $data) {}
    public function name(): string { return 'get_logger_chart'; }

    public function schema(): array
    {
        return ['type'=>'function','function'=>[
            'name'=>$this->name(),
            'description'=>'Buat grafik deret waktu satu pos (untuk permintaan "grafik/visualisasi"). Mengembalikan data grafik untuk ditampilkan.',
            'parameters'=>['type'=>'object','properties'=>[
                'logger'=>['type'=>'string','description'=>'Nama/ID pos.'],
                'date_range'=>['type'=>'string','description'=>'Frasa periode opsional; default 7 hari.'],
                'granularity'=>['type'=>'string','enum'=>['hourly','daily'],'description'=>'Granularitas opsional.'],
            ],'required'=>['logger']],
        ]];
    }

    public function run(array $args, User $user): array
    {
        $logger = $this->data->resolveLogger($user, (string)($args['logger'] ?? ''));
        if (! $logger) { return ['text' => 'Pos untuk grafik tidak ditemukan pada akses akun ini.']; }
        $range = isset($args['date_range']) ? $this->data->dateRange((string)$args['date_range']) : null;
        $range = $range ?: $this->data->defaultWeekRange();
        $gran = isset($args['granularity']) ? (string)$args['granularity'] : null;

        $chart = $this->data->chart($logger, $range, (string)($args['logger'] ?? ''), $gran);
        if (! $chart) { return ['text' => 'Data grafik untuk pos itu tidak tersedia pada rentang tersebut.']; }

        return ['text' => $chart['explanation'], 'chart' => $chart['chart']];
    }
}
```

Test (`LoggerChartToolTest`): mock `chart` mengembalikan `['explanation'=>'...','chart'=>[...]]`; assert `out['chart']` ada; logger null → not found.

- [ ] **Step 12: Jalankan** — `php artisan test --filter=LoggerChartToolTest` → PASS.

### 5f. `RainOverviewTool`

- [ ] **Step 13: Implementasi + test**

```php
<?php
namespace App\Services\Chatbot\Tools;

use App\Models\User;
use App\Services\Chatbot\MonitoringData;

class RainOverviewTool implements ChatbotTool
{
    public function __construct(private MonitoringData $data) {}
    public function name(): string { return 'rain_overview'; }

    public function schema(): array
    {
        return ['type'=>'function','function'=>[
            'name'=>$this->name(),
            'description'=>'Ikhtisar curah hujan lintas pos: pos mana sedang hujan, akumulasi hari ini.',
            'parameters'=>['type'=>'object','properties'=>new \stdClass()],
        ]];
    }

    public function run(array $args, User $user): array
    {
        $overview = $this->data->rainOverview($user);
        return ['text' => json_encode($overview, JSON_UNESCAPED_UNICODE), 'data' => $overview];
    }
}
```

Test (`RainOverviewToolTest`): mock `rainOverview` → assert `text` JSON memuat kunci yang diharapkan.

- [ ] **Step 14: Jalankan semua tool** — `php artisan test --filter=Tools` → PASS.

- [ ] **Step 15: Commit**

```bash
git add app/Services/Chatbot/Tools tests/Unit/Chatbot/Tools
git commit -m "feat: add six chatbot data tools wrapping MonitoringData"
```

---

## Task 6: `ContextEngine` + persona update

**Files:**
- Create: `app/Services/Chatbot/ContextEngine.php`
- Modify: `app/Services/ChatbotPersona.php` (terima konteks ringan), `resources/chatbot/AGENT.md`, `resources/chatbot/SKILLS.md`
- Test: `tests/Unit/Chatbot/ContextEngineTest.php`

**Interfaces:**
- Consumes: `MonitoringData` (Task 1).
- Produces:
  - `ContextEngine::lightContext(User $user): array` — `user_name, server_time, logger_total_visible, online/offline counts, logger_names (array "Nama (id)", dipangkas), loggers_truncated, category_definitions`.
  - `ContextEngine::history(array $turns): array` — pangkas 8 turn terakhir → `[['role'=>,'content'=>], ...]` (role hanya user/assistant, konten non-kosong).

- [ ] **Step 1: Test**

```php
// tests/Unit/Chatbot/ContextEngineTest.php
namespace Tests\Unit\Chatbot;

use App\Services\Chatbot\ContextEngine;
use App\Services\Chatbot\MonitoringData;
use Mockery;
use Tests\TestCase;

class ContextEngineTest extends TestCase
{
    public function test_history_keeps_last_8_and_filters_empty(): void
    {
        $engine = new ContextEngine(Mockery::mock(MonitoringData::class));
        $turns = [];
        for ($i = 0; $i < 12; $i++) { $turns[] = ['role'=>'user','text'=>"m{$i}"]; }
        $turns[] = ['role'=>'assistant','text'=>'   ']; // dibuang

        $out = $engine->history($turns);
        $this->assertCount(8, $out);
        $this->assertSame('content', array_key_first($out[0]) === 'role' ? 'role' : 'content') ?: null;
        $this->assertSame('m11', $out[7]['content']);
    }
}
```

(Sederhanakan assertion bila perlu; inti: maksimal 8, kosong terbuang, urutan terjaga, key `role`/`content`.)

- [ ] **Step 2: Jalankan, pastikan gagal** — `php artisan test --filter=ContextEngineTest` → FAIL.

- [ ] **Step 3: Implementasi**

```php
<?php
namespace App\Services\Chatbot;

use App\Models\User;

class ContextEngine
{
    public function __construct(private MonitoringData $data) {}

    public function lightContext(User $user): array
    {
        $ctx = $this->data->context($user);
        $facts = $this->data->groundedFacts($ctx);

        $names = collect($facts['all_loggers'] ?? [])
            ->map(fn ($l) => ($l['nama'] ?? '-').' ('.($l['id'] ?? '-').')')
            ->take(80)->values()->all();

        return [
            'user_name' => $facts['user_name'] ?? 'Pengguna',
            'server_time' => $facts['server_time'] ?? now()->format('Y-m-d H:i:s'),
            'logger_total_visible' => $facts['logger_total_visible'] ?? 0,
            'logger_online_count' => $facts['logger_online_count'] ?? 0,
            'logger_offline_count' => $facts['logger_offline_count'] ?? 0,
            'logger_names' => $names,
            'loggers_truncated' => count($names) < ($facts['logger_total_visible'] ?? 0),
            'category_definitions' => $facts['category_definitions'] ?? $this->data->categoryDefinitions(),
        ];
    }

    /** @return array<int,array{role:string,content:string}> */
    public function history(array $turns): array
    {
        return collect($turns)
            ->filter(fn ($t) => in_array($t['role'] ?? '', ['user','assistant'], true))
            ->map(fn ($t) => ['role'=>$t['role'], 'content'=>trim((string)($t['text'] ?? ''))])
            ->filter(fn ($t) => $t['content'] !== '')
            ->take(-8)->values()->all();
    }
}
```

- [ ] **Step 4: Jalankan test** — `php artisan test --filter=ContextEngineTest` → PASS.

- [ ] **Step 5: Persona terima konteks ringan**

`ChatbotPersona::systemPrompt(array $facts)` tetap menerima array; pada pemanggil baru (Task 8) kita kirim `lightContext`. Tidak perlu ubah `systemPrompt` bila ia hanya `json_encode($facts)` ke blok fakta. Verifikasi badan `systemPrompt` (baris 54-69) — bila ia menamai kunci spesifik yang kini hilang (mis. `online_loggers`), longgarkan agar hanya menampilkan apa yang ada.

- [ ] **Step 6: Update AGENT.md & SKILLS.md**

Di `resources/chatbot/AGENT.md` — ganti bagian "Aturan Grounding" agar merujuk tool, bukan SYSTEM FACTS:

```markdown
## Aturan Grounding (WAJIB)

1. Untuk SEMUA angka, nama logger, ID, status, nilai sensor, agregat, dan
   grafik — PANGGIL TOOL yang sesuai (list_loggers, get_logger_detail,
   compare_loggers, get_logger_history, get_logger_chart, rain_overview).
   JANGAN mengarang angka/nama/ID.
2. Konteks ringan yang disuntik (daftar nama pos, jumlah, definisi kategori)
   hanya untuk memahami pertanyaan & memilih argumen tool — bukan sumber angka.
3. Bila tool melaporkan data tidak tersedia, sampaikan apa adanya; jangan menebak.
4. Untuk permintaan grafik/visualisasi, panggil get_logger_chart.
```

Di `resources/chatbot/SKILLS.md` — ganti "Skema FAKTA SISTEM" menjadi "Tool yang tersedia" dengan ringkasan tiap tool & argumennya (sesuai Task 5 schema). Pertahankan bagian agregasi (hujan=SUM, lainnya=AVG), status online <60 menit, dan Panduan Menu.

- [ ] **Step 7: Commit**

```bash
git add app/Services/Chatbot/ContextEngine.php app/Services/ChatbotPersona.php resources/chatbot/AGENT.md resources/chatbot/SKILLS.md tests/Unit/Chatbot/ContextEngineTest.php
git commit -m "feat: add ContextEngine + switch persona docs to tool model"
```

---

## Task 7: `ProviderClient` (non-stream + stream)

**Files:**
- Create: `app/Services/Chatbot/ProviderClient.php`
- Test: `tests/Unit/Chatbot/ProviderClientTest.php`

**Interfaces:**
- Produces:
  - `configured(): bool`
  - `chat(array $messages, array $tools = []): ?array` — POST non-stream; return `choices.0.message` (array berisi `content` dan/atau `tool_calls`) atau null bila gagal.
  - `stream(array $messages, callable $onToken): ?string` — POST `stream:true`; panggil `$onToken(string $delta)` per token; return teks penuh atau null.

- [ ] **Step 1: Test (Http fake, non-stream)**

```php
// tests/Unit/Chatbot/ProviderClientTest.php
namespace Tests\Unit\Chatbot;

use App\Services\Chatbot\ProviderClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ProviderClientTest extends TestCase
{
    public function test_chat_returns_message_on_success(): void
    {
        config(['services.ai_chatbot.endpoint'=>'https://api.test/v1/chat/completions',
                'services.ai_chatbot.key'=>'k','services.ai_chatbot.model'=>'gpt-5']);
        Http::fake(['*'=>Http::response(['choices'=>[['message'=>['content'=>'halo']]]], 200)]);

        $msg = app(ProviderClient::class)->chat([['role'=>'user','content'=>'hi']]);
        $this->assertSame('halo', $msg['content']);
    }

    public function test_chat_returns_null_on_error(): void
    {
        config(['services.ai_chatbot.endpoint'=>'https://api.test/v1/chat/completions',
                'services.ai_chatbot.key'=>'k','services.ai_chatbot.model'=>'gpt-5']);
        Http::fake(['*'=>Http::response('boom', 500)]);

        $this->assertNull(app(ProviderClient::class)->chat([['role'=>'user','content'=>'hi']]));
    }
}
```

- [ ] **Step 2: Jalankan, pastikan gagal** — `php artisan test --filter=ProviderClientTest` → FAIL.

- [ ] **Step 3: Implementasi**

```php
<?php
namespace App\Services\Chatbot;

use Illuminate\Support\Facades\Http;

class ProviderClient
{
    public function configured(): bool
    {
        return (bool) config('services.ai_chatbot.key')
            && (bool) config('services.ai_chatbot.model')
            && (bool) config('services.ai_chatbot.endpoint');
    }

    public function chat(array $messages, array $tools = []): ?array
    {
        try {
            $payload = [
                'model' => config('services.ai_chatbot.model'),
                'max_completion_tokens' => 600,
                'messages' => $messages,
            ];
            if ($tools) { $payload['tools'] = $tools; $payload['tool_choice'] = 'auto'; }

            $res = $this->request()->post(config('services.ai_chatbot.endpoint'), $payload);
            if (! $res->successful()) { report(new \RuntimeException('Chatbot provider error: '.$res->body())); return null; }

            return data_get($res->json(), 'choices.0.message');
        } catch (\Throwable $e) { report($e); return null; }
    }

    public function stream(array $messages, callable $onToken): ?string
    {
        try {
            $res = $this->request()->withOptions(['stream'=>true])->post(
                config('services.ai_chatbot.endpoint'),
                ['model'=>config('services.ai_chatbot.model'),'max_completion_tokens'=>600,'messages'=>$messages,'stream'=>true]
            );
            if (! $res->successful()) { report(new \RuntimeException('Chatbot stream error: '.$res->status())); return null; }

            $full = '';
            $body = $res->toPsrResponse()->getBody();
            $buffer = '';
            while (! $body->eof()) {
                $buffer .= $body->read(1024);
                while (($pos = strpos($buffer, "\n")) !== false) {
                    $line = trim(substr($buffer, 0, $pos));
                    $buffer = substr($buffer, $pos + 1);
                    if (! str_starts_with($line, 'data:')) { continue; }
                    $data = trim(substr($line, 5));
                    if ($data === '' || $data === '[DONE]') { continue; }
                    $delta = data_get(json_decode($data, true), 'choices.0.delta.content');
                    if (is_string($delta) && $delta !== '') { $full .= $delta; $onToken($delta); }
                }
            }
            return $full;
        } catch (\Throwable $e) { report($e); return null; }
    }

    private function request()
    {
        $verify = filter_var(config('services.ai_chatbot.verify_ssl', true), FILTER_VALIDATE_BOOL);
        $req = Http::timeout(25)->withToken(config('services.ai_chatbot.key'))->acceptJson();
        return $verify ? $req : $req->withoutVerifying();
    }
}
```

- [ ] **Step 4: Jalankan test** — `php artisan test --filter=ProviderClientTest` → PASS.

- [ ] **Step 5: Commit**

```bash
git add app/Services/Chatbot/ProviderClient.php tests/Unit/Chatbot/ProviderClientTest.php
git commit -m "feat: add chatbot ProviderClient (chat + stream)"
```

---

## Task 8: `ChatbotAgent` (orchestrator loop)

**Files:**
- Create: `app/Services/Chatbot/ChatbotAgent.php`
- Modify: `app/Providers/AppServiceProvider.php` (registrasi tool ke `ToolRegistry`)
- Test: `tests/Unit/Chatbot/ChatbotAgentTest.php`

**Interfaces:**
- Consumes: `ProviderClient` (Task 7), `ToolRegistry` (Task 4), `ContextEngine` (Task 6), `MonitoringData` (Task 2), `ChatbotPersona`.
- Produces:
  - `ask(User $user, string $message, array $turns=[]): array` — return `['reply'=>string,'source'=>'ai'|'local','configured'=>bool,'chart'=>?array]`.
  - `stream(User $user, string $message, array $turns, callable $onToken, callable $onChart): array` — stream Pass-2; return `['source'=>...,'chart'=>?array]` (teks via callback).

- [ ] **Step 1: Test (provider di-mock, alur 1 tool)**

```php
// tests/Unit/Chatbot/ChatbotAgentTest.php
namespace Tests\Unit\Chatbot;

use App\Models\User;
use App\Services\Chatbot\{ChatbotAgent, ContextEngine, MonitoringData, ProviderClient, ToolRegistry};
use App\Services\ChatbotPersona;
use Mockery;
use Tests\TestCase;

class ChatbotAgentTest extends TestCase
{
    public function test_ask_no_tool_returns_direct_reply(): void
    {
        $provider = Mockery::mock(ProviderClient::class);
        $provider->shouldReceive('configured')->andReturnTrue();
        $provider->shouldReceive('chat')->once()->andReturn(['content'=>'Selamat datang.']);

        $agent = $this->makeAgent($provider, new ToolRegistry());
        $out = $agent->ask(User::factory()->make(), 'halo');

        $this->assertSame('ai', $out['source']);
        $this->assertSame('Selamat datang.', $out['reply']);
    }

    public function test_ask_with_tool_call_executes_then_answers(): void
    {
        $registry = new ToolRegistry();
        $registry->register(new class implements \App\Services\Chatbot\Tools\ChatbotTool {
            public function name(): string { return 'list_loggers'; }
            public function schema(): array { return ['type'=>'function','function'=>['name'=>'list_loggers']]; }
            public function run(array $a, User $u): array { return ['text'=>'{"offline_count":3}']; }
        });

        $provider = Mockery::mock(ProviderClient::class);
        $provider->shouldReceive('configured')->andReturnTrue();
        $provider->shouldReceive('chat')->twice()->andReturn(
            ['tool_calls'=>[['id'=>'c1','type'=>'function','function'=>['name'=>'list_loggers','arguments'=>'{}']]]],
            ['content'=>'Ada 3 logger offline.']
        );

        $agent = $this->makeAgent($provider, $registry);
        $out = $agent->ask(User::factory()->make(), 'berapa yang offline?');

        $this->assertStringContainsString('3 logger offline', $out['reply']);
    }

    private function makeAgent(ProviderClient $provider, ToolRegistry $registry): ChatbotAgent
    {
        $data = Mockery::mock(MonitoringData::class);
        $context = Mockery::mock(ContextEngine::class);
        $context->shouldReceive('lightContext')->andReturn(['user_name'=>'T']);
        $context->shouldReceive('history')->andReturn([]);
        return new ChatbotAgent($provider, $registry, $context, $data, app(ChatbotPersona::class));
    }
}
```

- [ ] **Step 2: Jalankan, pastikan gagal** — `php artisan test --filter=ChatbotAgentTest` → FAIL.

- [ ] **Step 3: Implementasi**

```php
<?php
namespace App\Services\Chatbot;

use App\Models\User;
use App\Services\ChatbotPersona;

class ChatbotAgent
{
    public function __construct(
        private ProviderClient $provider,
        private ToolRegistry $registry,
        private ContextEngine $context,
        private MonitoringData $data,
        private ChatbotPersona $persona,
    ) {}

    public function ask(User $user, string $message, array $turns = []): array
    {
        if (! $this->provider->configured()) {
            return $this->fallback($user, $message);
        }

        $messages = $this->seedMessages($user, $message, $turns);
        $first = $this->provider->chat($messages, $this->registry->schemas());
        if ($first === null) { return $this->fallback($user, $message); }

        $chart = null;

        if (! empty($first['tool_calls'])) {
            $messages[] = $this->assistantToolCallMessage($first);
            foreach ($first['tool_calls'] as $call) {
                [$result, $callChart] = $this->execute($call, $user);
                if ($callChart) { $chart = $callChart; }
                $messages[] = [
                    'role' => 'tool',
                    'tool_call_id' => $call['id'] ?? '',
                    'content' => $result['text'] ?? '',
                ];
            }
            $second = $this->provider->chat($messages); // pass-2, tanpa tools (cap 1 putaran)
            if ($second === null) { return $this->fallback($user, $message); }
            return ['reply'=>trim((string)($second['content'] ?? '')), 'source'=>'ai', 'configured'=>true, 'chart'=>$chart];
        }

        return ['reply'=>trim((string)($first['content'] ?? '')), 'source'=>'ai', 'configured'=>true, 'chart'=>null];
    }

    public function stream(User $user, string $message, array $turns, callable $onToken, callable $onChart): array
    {
        if (! $this->provider->configured()) {
            $fb = $this->fallback($user, $message);
            $onToken($fb['reply']);
            return ['source'=>'local', 'chart'=>null];
        }

        $messages = $this->seedMessages($user, $message, $turns);
        $first = $this->provider->chat($messages, $this->registry->schemas());
        if ($first === null) {
            $fb = $this->fallback($user, $message); $onToken($fb['reply']); return ['source'=>'local','chart'=>null];
        }

        if (! empty($first['tool_calls'])) {
            $messages[] = $this->assistantToolCallMessage($first);
            $chart = null;
            foreach ($first['tool_calls'] as $call) {
                [$result, $callChart] = $this->execute($call, $user);
                if ($callChart) { $chart = $callChart; $onChart($callChart); }
                $messages[] = ['role'=>'tool','tool_call_id'=>$call['id'] ?? '','content'=>$result['text'] ?? ''];
            }
            $this->provider->stream($messages, $onToken);
            return ['source'=>'ai', 'chart'=>$chart];
        }

        // tak ada tool → stream langsung dari pesan awal (panggil ulang stream untuk konten)
        $this->provider->stream($messages, $onToken);
        return ['source'=>'ai', 'chart'=>null];
    }

    private function seedMessages(User $user, string $message, array $turns): array
    {
        $light = $this->context->lightContext($user);
        return array_merge(
            [['role'=>'developer', 'content'=>$this->persona->systemPrompt($light)]],
            $this->context->history($turns),
            [['role'=>'user', 'content'=>$message]],
        );
    }

    private function assistantToolCallMessage(array $first): array
    {
        return ['role'=>'assistant', 'content'=>$first['content'] ?? null, 'tool_calls'=>$first['tool_calls']];
    }

    /** @return array{0:array,1:?array} [result, chart] */
    private function execute(array $call, User $user): array
    {
        $name = data_get($call, 'function.name', '');
        $args = json_decode((string) data_get($call, 'function.arguments', '{}'), true) ?: [];
        $result = $this->registry->run($name, $args, $user);
        return [$result, $result['chart'] ?? null];
    }

    private function fallback(User $user, string $message): array
    {
        $ctx = $this->data->context($user, $message);
        return [
            'reply' => $this->data->groundedFallback($user, $message, $ctx),
            'source' => 'local',
            'configured' => $this->provider->configured(),
            'chart' => null,
        ];
    }
}
```

(Catatan stream tanpa tool: jika perlu hindari dobel panggilan, implementasi boleh memakai hasil `$first['content']` langsung bila ada; sederhananya panggil `stream()` sekali seperti di atas. Verifikasi perilaku saat menjalankan Task 10.)

- [ ] **Step 4: Registrasi tool di service provider**

Di `app/Providers/AppServiceProvider.php` method `boot()` (atau `register()`):

```php
$this->app->singleton(\App\Services\Chatbot\ToolRegistry::class, function ($app) {
    $r = new \App\Services\Chatbot\ToolRegistry();
    foreach ([
        \App\Services\Chatbot\Tools\ListLoggersTool::class,
        \App\Services\Chatbot\Tools\LoggerDetailTool::class,
        \App\Services\Chatbot\Tools\CompareLoggersTool::class,
        \App\Services\Chatbot\Tools\LoggerHistoryTool::class,
        \App\Services\Chatbot\Tools\LoggerChartTool::class,
        \App\Services\Chatbot\Tools\RainOverviewTool::class,
    ] as $tool) { $r->register($app->make($tool)); }
    return $r;
});
```

- [ ] **Step 5: Jalankan test** — `php artisan test --filter=ChatbotAgentTest` → PASS.

- [ ] **Step 6: Commit**

```bash
git add app/Services/Chatbot/ChatbotAgent.php app/Providers/AppServiceProvider.php tests/Unit/Chatbot/ChatbotAgentTest.php
git commit -m "feat: add ChatbotAgent orchestrator + register tools"
```

---

## Task 9: Pindahkan `/chatbot/ask` ke agent (paritas non-stream)

**Files:**
- Modify: `app/Http/Controllers/ChatbotController.php`
- Test: `tests/Feature/Chatbot/ChatbotAskTest.php`

**Interfaces:**
- Consumes: `ChatbotAgent::ask` (Task 8).

- [ ] **Step 1: Feature test (Http fake provider)**

```php
// tests/Feature/Chatbot/ChatbotAskTest.php
namespace Tests\Feature\Chatbot;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ChatbotAskTest extends TestCase
{
    public function test_ask_returns_reply_shape(): void
    {
        config(['services.ai_chatbot.endpoint'=>'https://api.test/v1/chat/completions',
                'services.ai_chatbot.key'=>'k','services.ai_chatbot.model'=>'gpt-5']);
        Http::fake(['*'=>Http::response(['choices'=>[['message'=>['content'=>'Halo.']]]], 200)]);

        $res = $this->actingAs(User::factory()->create())
            ->postJson(route('chatbot.ask'), ['message'=>'halo']);

        $res->assertOk()->assertJsonStructure(['reply','source','configured','chart']);
    }
}
```

- [ ] **Step 2: Jalankan, pastikan gagal/relevan** — `php artisan test --filter=ChatbotAskTest` (mungkin masih lulus dgn implementasi lama; tujuan: jaga paritas saat refactor di Step 3).

- [ ] **Step 3: Ramping-kan `ask()`**

Ganti badan `ChatbotController::ask` menjadi delegasi ke agent. Hapus pemanggilan fast-path lama dari `ask()` (tetapi JANGAN hapus method-nya dulu — Task 10):

```php
public function __construct(
    private \App\Services\Chatbot\MonitoringData $data,
    private \App\Services\Chatbot\ChatbotAgent $agent,
) {}

public function ask(Request $request): JsonResponse
{
    $validated = $request->validate([
        'message' => ['required','string','min:2','max:700'],
        'messages' => ['sometimes','array','max:12'],
        'messages.*.role' => ['required_with:messages','in:user,assistant'],
        'messages.*.text' => ['required_with:messages','string','max:700'],
    ]);

    $out = $this->agent->ask($request->user(), trim($validated['message']), $validated['messages'] ?? []);

    return response()->json([
        'reply' => \Illuminate\Support\Str::limit($out['reply'], 1600, '...'),
        'source' => $out['source'],
        'configured' => $out['configured'],
        'chart' => $out['chart'],
    ]);
}
```

- [ ] **Step 4: Jalankan test** — `php artisan test --filter=ChatbotAskTest` → PASS.

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/ChatbotController.php tests/Feature/Chatbot/ChatbotAskTest.php
git commit -m "refactor: route /chatbot/ask through ChatbotAgent"
```

---

## Task 10: Endpoint SSE + frontend streaming + bersihkan fast-path

**Files:**
- Modify: `routes/web.php`, `routes/api.php`, `app/Http/Controllers/ChatbotController.php`, `resources/views/partials/chatbot.blade.php`
- Test: `tests/Feature/Chatbot/ChatbotStreamTest.php`

**Interfaces:**
- Consumes: `ChatbotAgent::stream` (Task 8).

- [ ] **Step 1: Route**

`routes/web.php` (dekat baris 157):

```php
Route::middleware(['auth'])->post('/chatbot/stream', [ChatbotController::class, 'stream'])->name('chatbot.stream');
```

`routes/api.php` (blok chatbot, ~baris 59):

```php
Route::post('/chatbot/stream', [\App\Http\Controllers\ChatbotController::class, 'stream']);
```

- [ ] **Step 2: Controller `stream()`**

```php
use Symfony\Component\HttpFoundation\StreamedResponse;

public function stream(Request $request): StreamedResponse
{
    $validated = $request->validate([
        'message' => ['required','string','min:2','max:700'],
        'messages' => ['sometimes','array','max:12'],
        'messages.*.role' => ['required_with:messages','in:user,assistant'],
        'messages.*.text' => ['required_with:messages','string','max:700'],
    ]);

    $user = $request->user();
    $message = trim($validated['message']);
    $turns = $validated['messages'] ?? [];

    $response = new StreamedResponse(function () use ($user, $message, $turns) {
        $emit = function (string $event, array $data) {
            echo "event: {$event}\n";
            echo 'data: '.json_encode($data, JSON_UNESCAPED_UNICODE)."\n\n";
            if (function_exists('ob_get_level') && ob_get_level() > 0) { @ob_flush(); }
            flush();
        };

        try {
            $meta = $this->agent->stream(
                $user, $message, $turns,
                fn (string $token) => $emit('token', ['text'=>$token]),
                fn (array $chart) => $emit('chart', ['chart'=>$chart]),
            );
            $emit('done', ['source'=>$meta['source'] ?? 'ai']);
        } catch (\Throwable $e) {
            report($e);
            $emit('error', ['message'=>'Maaf, terjadi gangguan. Coba lagi sebentar.']);
        }
    });

    $response->headers->set('Content-Type', 'text/event-stream');
    $response->headers->set('Cache-Control', 'no-cache');
    $response->headers->set('X-Accel-Buffering', 'no');
    return $response;
}
```

- [ ] **Step 3: Feature test SSE**

```php
// tests/Feature/Chatbot/ChatbotStreamTest.php
namespace Tests\Feature\Chatbot;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ChatbotStreamTest extends TestCase
{
    public function test_stream_emits_token_and_done(): void
    {
        config(['services.ai_chatbot.endpoint'=>'https://api.test/v1/chat/completions',
                'services.ai_chatbot.key'=>'k','services.ai_chatbot.model'=>'gpt-5']);
        // pass-1 non-stream (no tool) lalu stream: fake balikan stream sederhana
        Http::fake(['*'=>Http::response(['choices'=>[['message'=>['content'=>'Halo.']]]], 200)]);

        $res = $this->actingAs(User::factory()->create())
            ->post(route('chatbot.stream'), ['message'=>'halo']);

        $res->assertOk();
        $res->assertHeader('Content-Type', 'text/event-stream; charset=UTF-8');
        $content = $res->streamedContent();
        $this->assertStringContainsString('event: done', $content);
    }
}
```

(Bila `stream()` provider sulit di-fake dgn Http::fake streaming, sederhanakan: pada lingkungan test, `ChatbotAgent::stream` jalur tanpa-tool akan memanggil `ProviderClient::stream`; pertimbangkan mem-bind mock `ProviderClient` di test via `$this->mock(ProviderClient::class)` agar `stream` memanggil `$onToken('Halo.')` lalu selesai. Sesuaikan assertion ke `event: token`/`event: done`.)

- [ ] **Step 4: Jalankan, pastikan gagal lalu lulus** — `php artisan test --filter=ChatbotStreamTest`.

- [ ] **Step 5: Frontend Alpine konsumsi SSE**

Di `resources/views/partials/chatbot.blade.php`, ganti blok `fetch('{{ route('chatbot.ask') }}'...)` (sekitar baris 462-490) dengan konsumsi SSE memakai `fetch` + `ReadableStream` (EventSource tak mendukung POST). Pola:

```javascript
async sendMessage(text, history) {
    const res = await fetch('{{ route('chatbot.stream') }}', {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'text/event-stream'},
        body: JSON.stringify({ message: text, messages: history }),
    });
    if (!res.ok || !res.body) { return this.fallbackAsk(text, history); } // fallback ke /chatbot/ask

    const message = this.pushAssistantReply('', null);   // mulai kosong, isi mengalir
    const reader = res.body.getReader();
    const decoder = new TextDecoder();
    let buf = '';
    while (true) {
        const { value, done } = await reader.read();
        if (done) break;
        buf += decoder.decode(value, { stream: true });
        let idx;
        while ((idx = buf.indexOf('\n\n')) !== -1) {
            const raw = buf.slice(0, idx); buf = buf.slice(idx + 2);
            const ev = (raw.match(/^event: (.*)$/m) || [])[1];
            const dataLine = (raw.match(/^data: (.*)$/m) || [])[1];
            if (!dataLine) continue;
            const payload = JSON.parse(dataLine);
            if (ev === 'token') { message.displayText += payload.text; message.isTyping = true; }
            else if (ev === 'chart') { message.chart = payload.chart; this.$nextTick(() => this.renderChart(message.id, payload.chart)); }
            else if (ev === 'done') { message.isTyping = false; }
            else if (ev === 'error') { message.displayText = payload.message; message.isTyping = false; }
        }
    }
}
```

Sesuaikan dengan struktur `pushAssistantReply`/`message` existing (return objek message agar bisa di-mutate). Pertahankan `fallbackAsk()` yang memanggil endpoint `/chatbot/ask` lama bila streaming gagal. Pastikan `renderChart` existing dipakai ulang.

- [ ] **Step 6: Hapus fast-path lama dari controller**

Hapus method yang tak lagi dipakai dari `ChatbotController`: `reply`, `aiReply`, dan wrapper delegasi fast-path yang tak dipanggil lagi (`isGreetingMessage`, `isComparisonQuestion`, `isChartQuestion`, `isRainQuestion`, `isLoggerListQuestion`, serta wrapper `formatLogger*`, `buildLoggerChart`, `rainOverview`, `groundedFacts`, `composeGroundedFallback`, `requested*`, `defaultWeekRange`, `categoryDefinitions`, `isRainfallParam`, `resolveLoggerMention(s)`, `buildMonitoringContext`) **jika** sudah tak ada pemanggil tersisa di controller. Verifikasi dengan:

Run: `grep -nE "isGreetingMessage|isComparisonQuestion|isChartQuestion|formatLogger|buildLoggerChart|composeGroundedFallback|buildMonitoringContext|aiReply|->reply\(" app/Http/Controllers/ChatbotController.php`
Expected: tidak ada hasil (selain definisi yang akan dihapus). Hapus definisi yang tak terpakai sehingga controller hanya berisi `__construct`, `ask`, `stream`.

- [ ] **Step 7: Jalankan seluruh test chatbot**

Run: `php artisan test --filter=Chatbot`
Expected: semua PASS.

- [ ] **Step 8: Verifikasi manual (gunakan skill `verify`/`run`)**

Jalankan app, buka chatbot, uji: "halo" (mengalir, cepat), "ada berapa logger offline?" (tool list), "grafik tinggi muka air pos <X> seminggu" (grafik tampil), "bandingkan pos A dan B". Konfirmasi streaming terlihat & grafik render.

- [ ] **Step 9: Commit**

```bash
git add routes/web.php routes/api.php app/Http/Controllers/ChatbotController.php resources/views/partials/chatbot.blade.php tests/Feature/Chatbot/ChatbotStreamTest.php
git commit -m "feat: SSE streaming chatbot endpoint + frontend; remove legacy fast-paths"
```

---

## Self-Review (sudah dijalankan saat penulisan)

- **Spec coverage:** struktur kode (Task 1-8), tool registry & 6 tool (Task 4-5), agent loop cap-1 (Task 8), context engine ramping (Task 6), streaming SSE + frontend (Task 10), endpoint lama tetap (Task 9), persona update (Task 6), fallback (Task 8), error/recovery (Task 4 registry + Task 8 + Task 10), akses forUser (Task 1/5), pengujian (tiap task). ✔
- **Placeholder scan:** tidak ada TODO/TBD; instruksi "pindahkan method X" menyertakan nama & tanda tangan persis. ✔
- **Type consistency:** nama method `MonitoringData` (context/resolveLogger/resolveLoggers/dateRange/granularity/summary/comparison/history/chart/rainOverview/groundedFallback/groundedFacts/categoryDefinitions/isRainfallParam) konsisten dipakai di Task 5/6/8; `ChatbotTool::run` return `['text','data?','chart?']` konsisten; `ProviderClient::chat` return message-array dipakai di agent. ✔

## Catatan risiko implementasi

- **Streaming di host:** PHP-FPM/Nginx bisa mem-buffer; header `X-Accel-Buffering: no` + `flush()` disertakan, plus fallback `/chatbot/ask`. Uji di lingkungan target.
- **Http::fake + streaming** kurang ideal untuk uji `ProviderClient::stream`; gunakan mock `ProviderClient` di test Feature stream (lihat Task 10 Step 3).
- **Paritas refactor (Task 1-2):** karena memindah ~1000 baris, jalankan `php artisan test` penuh + smoke manual setelah Task 2 sebelum lanjut.
