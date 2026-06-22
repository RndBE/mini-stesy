# Multi-Instansi Logger Access Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Let a superadmin grant a normal user read-only visibility to specific loggers from *other* instansi, reusing the existing `user_logger_access` pivot (no new table).

**Architecture:** Relax the "logger must be in the user's own instansi" rule in three places — the `t_Logger::scopeForUser()` query scope, the `UserController::syncLoggerAccessForUser()` assignment guard, and the `FcmService::getLoggerWarningTokens()` notification query — then update the user create/edit modal so a superadmin can browse and tick loggers across all instansi (grouped by instansi). Cross-instansi granting stays superadmin-only.

**Tech Stack:** Laravel 11, PHP 8, PHPUnit 11.5 (SQLite `:memory:` for tests), Alpine.js + Blade + Tailwind for the UI.

## Global Constraints

- **No new database table.** Reuse `user_logger_access (user_id → t_user.id_user, logger_id → t_logger.id_logger)`.
- **Cross-instansi grants are superadmin-only.** A non-superadmin actor may only assign loggers from their own instansi.
- **Read-only.** Grants affect logger *visibility* and *notifications* only — never management/edit rights.
- **No auto-include.** Granting is per-logger checkbox; a "select all in instansi X" control only ticks loggers that exist at that moment.
- **Tests** must follow the existing pattern: build a minimal schema by hand in `setUp()` (see `tests/Feature/TingkatSiagaUnifiedTest.php`, `tests/Feature/ArrRainStatusTest.php`) and run against SQLite `:memory:`. Do **not** use the real migrations and never touch the prod DB.
- **Roles:** `superadmin`, `instansi_admin`/`admin`, `pegawai`/`user` — detected via `t_User::isSuperAdmin()/isInstansiAdmin()/isPegawai()`.
- Run the full suite with: `php artisan test` (or a single file with `php artisan test --filter=<Class>`).

---

## File Structure

| File | Responsibility | Change |
|------|----------------|--------|
| `app/Models/t_Logger.php` | `scopeForUser()` visibility logic | Modify |
| `app/Http/Controllers/UserController.php` | `syncLoggerAccessForUser()` assignment guard | Modify |
| `app/Services/FcmService.php` | `getLoggerWarningTokens()` notification audience | Modify |
| `resources/views/users/index.blade.php` | Create/edit user modal: logger picker (Alpine JS + template) | Modify |
| `tests/Feature/MultiInstansiScopeForUserTest.php` | Scope unit coverage | Create |
| `tests/Feature/MultiInstansiUserControllerTest.php` | Controller assignment + gate coverage | Create |
| `tests/Feature/MultiInstansiFcmTokensTest.php` | Notification audience coverage | Create |

---

## Task 1: Cross-instansi visibility in `scopeForUser()`

**Files:**
- Modify: `app/Models/t_Logger.php:35-57`
- Test: `tests/Feature/MultiInstansiScopeForUserTest.php` (create)

**Interfaces:**
- Consumes: nothing new.
- Produces: `t_Logger::scopeForUser($query, $user)` — query scope. After this task: `superadmin` → all loggers; `instansi_admin` → loggers where `instansi_id = user.instansi_id` **OR** `id_logger` in `user_logger_access` for the user; `pegawai`/other → loggers where `id_logger` in `user_logger_access` for the user; `null` user → none.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/MultiInstansiScopeForUserTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Models\t_Logger;
use App\Models\t_User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MultiInstansiScopeForUserTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (['user_logger_access', 't_logger', 't_user'] as $t) {
            Schema::dropIfExists($t);
        }

        Schema::create('t_user', function (Blueprint $table) {
            $table->increments('id_user');
            $table->string('nama');
            $table->string('username');
            $table->string('password')->nullable();
            $table->string('level_user');
            $table->unsignedInteger('instansi_id')->nullable();
            $table->string('status')->nullable();
        });

        Schema::create('t_logger', function (Blueprint $table) {
            $table->increments('id');
            $table->string('id_logger', 15)->unique();
            $table->unsignedInteger('instansi_id')->nullable();
            $table->string('nama_logger');
        });

        Schema::create('user_logger_access', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('logger_id', 15);
            $table->unique(['user_id', 'logger_id']);
        });

        // Instansi 1: L1, L2 ; Instansi 2: L3, L4
        DB::table('t_logger')->insert([
            ['id_logger' => 'L1', 'instansi_id' => 1, 'nama_logger' => 'Logger 1'],
            ['id_logger' => 'L2', 'instansi_id' => 1, 'nama_logger' => 'Logger 2'],
            ['id_logger' => 'L3', 'instansi_id' => 2, 'nama_logger' => 'Logger 3'],
            ['id_logger' => 'L4', 'instansi_id' => 2, 'nama_logger' => 'Logger 4'],
        ]);
    }

    private function makeUser(string $level, ?int $instansiId): t_User
    {
        $id = DB::table('t_user')->insertGetId([
            'nama' => $level,
            'username' => $level . '_' . ($instansiId ?? 'x') . '_' . uniqid(),
            'password' => 'x',
            'level_user' => $level,
            'instansi_id' => $instansiId,
            'status' => 'aktif',
        ]);

        return t_User::query()->findOrFail($id);
    }

    private function grant(t_User $user, array $loggerIds): void
    {
        foreach ($loggerIds as $logger) {
            DB::table('user_logger_access')->insert([
                'user_id' => $user->id_user,
                'logger_id' => $logger,
            ]);
        }
    }

    private function visible(t_User $user): array
    {
        return t_Logger::query()->forUser($user)->pluck('id_logger')->sort()->values()->all();
    }

    public function test_superadmin_sees_all_loggers(): void
    {
        $user = $this->makeUser('superadmin', null);
        $this->assertSame(['L1', 'L2', 'L3', 'L4'], $this->visible($user));
    }

    public function test_instansi_admin_sees_own_instansi_plus_cross_instansi_grant(): void
    {
        $user = $this->makeUser('instansi_admin', 1);
        $this->grant($user, ['L4']); // cross-instansi extra
        $this->assertSame(['L1', 'L2', 'L4'], $this->visible($user));
    }

    public function test_instansi_admin_without_grant_sees_only_own_instansi(): void
    {
        $user = $this->makeUser('instansi_admin', 1);
        $this->assertSame(['L1', 'L2'], $this->visible($user));
    }

    public function test_pegawai_sees_only_granted_loggers_including_cross_instansi(): void
    {
        $user = $this->makeUser('pegawai', 1);
        $this->grant($user, ['L1', 'L3']); // one own-instansi, one cross-instansi
        $this->assertSame(['L1', 'L3'], $this->visible($user));
    }

    public function test_pegawai_without_grant_sees_nothing(): void
    {
        $user = $this->makeUser('pegawai', 1);
        $this->assertSame([], $this->visible($user));
    }

    public function test_null_user_sees_nothing(): void
    {
        $this->assertSame([], t_Logger::query()->forUser(null)->pluck('id_logger')->all());
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=MultiInstansiScopeForUserTest`
Expected: FAIL — `test_pegawai_sees_only_granted_loggers_including_cross_instansi` fails because the current scope ANDs an `instansi_id = user.instansi_id` filter, so `L3` (instansi 2) is excluded; `test_instansi_admin_sees_own_instansi_plus_cross_instansi_grant` fails because the admin branch returns early without the pivot OR.

- [ ] **Step 3: Write minimal implementation**

Replace `scopeForUser` in `app/Models/t_Logger.php:35-57` with:

```php
    public function scopeForUser($query, $user)
    {
        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        if (method_exists($user, 'isSuperAdmin') ? $user->isSuperAdmin() : ($user->level_user === 'superadmin')) {
            return $query;
        }

        return $query->where(function ($q) use ($user) {
            // instansi_admin melihat semua logger di instansinya sendiri.
            if (method_exists($user, 'isInstansiAdmin') && $user->isInstansiAdmin()) {
                $q->where('instansi_id', $user->instansi_id);
            }

            // Semua non-superadmin: tambah logger yang diberikan eksplisit
            // lewat pivot (boleh lintas instansi).
            $q->orWhereExists(function ($sub) use ($user) {
                $sub->selectRaw('1')
                    ->from('user_logger_access as ula')
                    ->whereColumn('ula.logger_id', 't_logger.id_logger')
                    ->where('ula.user_id', $user->id_user);
            });
        });
    }
```

Note: the outer `where(function () { ... })` groups the OR so it cannot leak rows when the scope is combined with other `where`s in the caller. For a pegawai the closure adds only `orWhereExists(...)`, which Laravel treats as a leading `where` → `WHERE (EXISTS ...)`.

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=MultiInstansiScopeForUserTest`
Expected: PASS (6 tests).

- [ ] **Step 5: Run the existing suite to check for regressions**

Run: `php artisan test`
Expected: PASS (no previously-passing test breaks).

- [ ] **Step 6: Commit**

```bash
git add app/Models/t_Logger.php tests/Feature/MultiInstansiScopeForUserTest.php
git commit -m "feat: allow cross-instansi logger visibility in scopeForUser"
```

---

## Task 2: Relax `syncLoggerAccessForUser()` for cross-instansi grants

**Files:**
- Modify: `app/Http/Controllers/UserController.php:372-407`
- Test: `tests/Feature/MultiInstansiUserControllerTest.php` (create)

**Interfaces:**
- Consumes: `t_Logger::scopeForUser()` from Task 1.
- Produces: `UserController::syncLoggerAccessForUser(t_User $user, ?array $loggerIds, t_User $actor)` — after this task: applies to `pegawai` (full set, ≥1 required) **and** `instansi_admin` (optional extras); other roles synced to empty. Assignable loggers = `t_Logger::forUser($actor)`, additionally restricted to `instansi_id = actor.instansi_id` when the actor is **not** superadmin. Tested through `UserController::store(Request)` called directly (bypassing route middleware, mirroring `tests/Feature/TingkatSiagaUnifiedTest.php`).

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/MultiInstansiUserControllerTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Http\Controllers\UserController;
use App\Models\t_User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MultiInstansiUserControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (['user_logger_access', 't_logger', 't_user', 'instansi', 'roles'] as $t) {
            Schema::dropIfExists($t);
        }

        Schema::create('roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('role_name')->unique();
        });

        Schema::create('instansi', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nama');
        });

        Schema::create('t_user', function (Blueprint $table) {
            $table->increments('id_user');
            $table->string('nama');
            $table->string('username')->unique();
            $table->string('password');
            $table->string('level_user');
            $table->unsignedInteger('instansi_id')->nullable();
            $table->string('status')->nullable();
            $table->string('suspend_reason')->nullable();
        });

        Schema::create('t_logger', function (Blueprint $table) {
            $table->increments('id');
            $table->string('id_logger', 15)->unique();
            $table->unsignedInteger('instansi_id')->nullable();
            $table->string('nama_logger');
        });

        Schema::create('user_logger_access', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('logger_id', 15);
            $table->unique(['user_id', 'logger_id']);
        });

        DB::table('roles')->insert([
            ['role_name' => 'superadmin'],
            ['role_name' => 'instansi_admin'],
            ['role_name' => 'pegawai'],
        ]);
        DB::table('instansi')->insert([
            ['id' => 1, 'nama' => 'Instansi Satu'],
            ['id' => 2, 'nama' => 'Instansi Dua'],
        ]);
        DB::table('t_logger')->insert([
            ['id_logger' => 'L1', 'instansi_id' => 1, 'nama_logger' => 'Logger 1'],
            ['id_logger' => 'L3', 'instansi_id' => 2, 'nama_logger' => 'Logger 3'],
        ]);
    }

    private function actor(string $level, ?int $instansiId): t_User
    {
        $id = DB::table('t_user')->insertGetId([
            'nama' => $level,
            'username' => 'actor_' . $level . '_' . uniqid(),
            'password' => 'x',
            'level_user' => $level,
            'instansi_id' => $instansiId,
            'status' => 'aktif',
        ]);

        return t_User::query()->findOrFail($id);
    }

    private function storeRequest(array $overrides): Request
    {
        return Request::create('/users', 'POST', array_merge([
            'nama' => 'RND',
            'username' => 'rnd_' . uniqid(),
            'password' => 'secret123',
            'level_user' => 'pegawai',
            'instansi_id' => 1,
        ], $overrides));
    }

    public function test_superadmin_can_grant_cross_instansi_loggers_to_pegawai(): void
    {
        $this->actingAs($this->actor('superadmin', null));

        $request = $this->storeRequest([
            'level_user' => 'pegawai',
            'instansi_id' => 1,
            'logger_access' => ['L1', 'L3'], // L3 is cross-instansi
        ]);

        (new UserController())->store($request);

        $user = t_User::query()->where('nama', 'RND')->firstOrFail();
        $granted = DB::table('user_logger_access')
            ->where('user_id', $user->id_user)
            ->pluck('logger_id')->sort()->values()->all();

        $this->assertSame(['L1', 'L3'], $granted);
    }

    public function test_superadmin_can_grant_extra_loggers_to_instansi_admin(): void
    {
        $this->actingAs($this->actor('superadmin', null));

        $request = $this->storeRequest([
            'level_user' => 'instansi_admin',
            'instansi_id' => 1,
            'logger_access' => ['L3'], // extra cross-instansi logger
        ]);

        (new UserController())->store($request);

        $user = t_User::query()->where('nama', 'RND')->firstOrFail();
        $granted = DB::table('user_logger_access')
            ->where('user_id', $user->id_user)
            ->pluck('logger_id')->all();

        $this->assertSame(['L3'], $granted);
    }

    public function test_pegawai_without_logger_is_rejected(): void
    {
        $this->actingAs($this->actor('superadmin', null));

        $request = $this->storeRequest([
            'level_user' => 'pegawai',
            'instansi_id' => 1,
            'logger_access' => [],
        ]);

        $this->expectException(ValidationException::class);
        (new UserController())->store($request);
    }

    public function test_instansi_admin_actor_cannot_grant_cross_instansi(): void
    {
        $this->actingAs($this->actor('instansi_admin', 1));

        $request = $this->storeRequest([
            'level_user' => 'pegawai',
            'instansi_id' => 1,
            'logger_access' => ['L3'], // L3 is instansi 2 — outside actor's authority
        ]);

        $this->expectException(ValidationException::class);
        (new UserController())->store($request);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=MultiInstansiUserControllerTest`
Expected: FAIL — `test_superadmin_can_grant_cross_instansi_loggers_to_pegawai` fails because the current `syncLoggerAccessForUser` filters assignable loggers by `where('instansi_id', $user->instansi_id)`, so `L3` is rejected as "di luar instansi user"; `test_superadmin_can_grant_extra_loggers_to_instansi_admin` fails because the current method syncs non-pegawai users to an empty set.

- [ ] **Step 3: Write minimal implementation**

Replace `syncLoggerAccessForUser` in `app/Http/Controllers/UserController.php:372-407` with:

```php
    /**
     * @param array<int, string>|null $loggerIds
     */
    private function syncLoggerAccessForUser(t_User $user, ?array $loggerIds, t_User $actor): void
    {
        // Hanya pegawai (himpunan penuh) & instansi_admin (tambahan lintas
        // instansi) yang punya grant eksplisit. Role lain tidak.
        if (!$user->isPegawai() && !$user->isInstansiAdmin()) {
            $user->accessibleLoggers()->sync([]);
            return;
        }

        $loggerIds = collect($loggerIds ?? [])
            ->filter(fn($id) => is_string($id) || is_numeric($id))
            ->map(fn($id) => trim((string) $id))
            ->filter()
            ->unique()
            ->values();

        // Pegawai wajib minimal 1 logger; instansi_admin boleh tanpa tambahan.
        if ($user->isPegawai() && $loggerIds->isEmpty()) {
            throw ValidationException::withMessages([
                'logger_access' => 'Minimal pilih 1 logger untuk akun pegawai.',
            ]);
        }

        if ($loggerIds->isEmpty()) {
            $user->accessibleLoggers()->sync([]);
            return;
        }

        // Hanya superadmin yang boleh memberi logger lintas instansi.
        // Actor lain dibatasi pada instansinya sendiri.
        $assignable = t_Logger::query()->forUser($actor);
        if (!$actor->isSuperAdmin()) {
            $assignable->where('instansi_id', $actor->instansi_id);
        }

        $allowedLoggers = $assignable
            ->whereIn('id_logger', $loggerIds)
            ->pluck('id_logger')
            ->map(fn($id) => (string) $id)
            ->values();

        if ($allowedLoggers->count() !== $loggerIds->count()) {
            throw ValidationException::withMessages([
                'logger_access' => 'Ada logger yang tidak valid atau di luar wewenang Anda.',
            ]);
        }

        $user->accessibleLoggers()->sync($allowedLoggers->all());
    }
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=MultiInstansiUserControllerTest`
Expected: PASS (4 tests).

- [ ] **Step 5: Run the existing suite to check for regressions**

Run: `php artisan test`
Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/UserController.php tests/Feature/MultiInstansiUserControllerTest.php
git commit -m "feat: allow superadmin to grant cross-instansi loggers when managing users"
```

---

## Task 3: Notification parity in `FcmService::getLoggerWarningTokens()`

**Files:**
- Modify: `app/Services/FcmService.php:221-258`
- Test: `tests/Feature/MultiInstansiFcmTokensTest.php` (create)

**Interfaces:**
- Consumes: nothing from earlier tasks (independent query, must mirror Task 1's rules).
- Produces: `FcmService::getLoggerWarningTokens(string $idLogger)` (private) — after this task the `instansi_admin`/`admin` branch matches when the user's `instansi_id` equals the logger's instansi **OR** the user has a `user_logger_access` row for that logger. The `pegawai`/`user` branch is already pivot-based and unchanged. Tested via reflection (the `FcmService` constructor only reads config — no network).

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/MultiInstansiFcmTokensTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Services\FcmService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ReflectionMethod;
use Tests\TestCase;

class MultiInstansiFcmTokensTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (['fcm_tokens', 'user_logger_access', 't_logger', 't_user'] as $t) {
            Schema::dropIfExists($t);
        }

        Schema::create('t_user', function (Blueprint $table) {
            $table->increments('id_user');
            $table->string('nama');
            $table->string('username')->unique();
            $table->string('level_user');
            $table->unsignedInteger('instansi_id')->nullable();
            $table->string('status')->nullable();
        });

        Schema::create('t_logger', function (Blueprint $table) {
            $table->increments('id');
            $table->string('id_logger', 15)->unique();
            $table->unsignedInteger('instansi_id')->nullable();
            $table->string('nama_logger');
        });

        Schema::create('user_logger_access', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('logger_id', 15);
            $table->unique(['user_id', 'logger_id']);
        });

        Schema::create('fcm_tokens', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('fcm_token');
        });

        // Logger L3 lives in instansi 2.
        DB::table('t_logger')->insert([
            ['id_logger' => 'L3', 'instansi_id' => 2, 'nama_logger' => 'Logger 3'],
        ]);
    }

    private function user(string $level, ?int $instansiId, string $token, array $grants = []): void
    {
        $id = DB::table('t_user')->insertGetId([
            'nama' => $level,
            'username' => $level . '_' . uniqid(),
            'level_user' => $level,
            'instansi_id' => $instansiId,
            'status' => 'aktif',
        ]);
        DB::table('fcm_tokens')->insert(['user_id' => $id, 'fcm_token' => $token]);
        foreach ($grants as $logger) {
            DB::table('user_logger_access')->insert(['user_id' => $id, 'logger_id' => $logger]);
        }
    }

    private function tokensFor(string $idLogger): array
    {
        $method = new ReflectionMethod(FcmService::class, 'getLoggerWarningTokens');
        $method->setAccessible(true);

        return collect($method->invoke(new FcmService(), $idLogger))->sort()->values()->all();
    }

    public function test_warning_audience_includes_cross_instansi_grants(): void
    {
        $this->user('superadmin', null, 'tok-super');                 // always
        $this->user('instansi_admin', 2, 'tok-admin-same');           // same instansi as L3
        $this->user('instansi_admin', 1, 'tok-admin-grant', ['L3']);  // cross-instansi grant
        $this->user('instansi_admin', 1, 'tok-admin-none');           // no grant, other instansi -> excluded
        $this->user('pegawai', 1, 'tok-pegawai-grant', ['L3']);       // cross-instansi grant
        $this->user('pegawai', 2, 'tok-pegawai-none');                // no grant -> excluded

        $this->assertSame(
            ['tok-admin-grant', 'tok-admin-same', 'tok-pegawai-grant', 'tok-super'],
            $this->tokensFor('L3')
        );
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=MultiInstansiFcmTokensTest`
Expected: FAIL — `tok-admin-grant` (an instansi_admin in instansi 1 with a cross-instansi grant to L3) is missing, because the current admin branch only matches `u.instansi_id = logger.instansi_id`.

- [ ] **Step 3: Write minimal implementation**

In `app/Services/FcmService.php`, replace the admin `orWhere(...)` branch (currently `app/Services/FcmService.php:239-243`) so it matches instansi OR a pivot grant. The full `where(function ($query) use ($logger) { ... })` block becomes:

```php
            ->where(function ($query) use ($logger) {
                $query->whereRaw('LOWER(u.level_user) = ?', ['superadmin'])
                    ->orWhere(function ($adminQuery) use ($logger) {
                        $adminQuery
                            ->whereIn(DB::raw('LOWER(u.level_user)'), ['instansi_admin', 'admin'])
                            ->where(function ($scope) use ($logger) {
                                $scope->where('u.instansi_id', $logger->instansi_id)
                                    ->orWhereExists(function ($accessQuery) use ($logger) {
                                        $accessQuery->selectRaw('1')
                                            ->from('user_logger_access as ula')
                                            ->whereColumn('ula.user_id', 'u.id_user')
                                            ->where('ula.logger_id', $logger->id_logger);
                                    });
                            });
                    })
                    ->orWhere(function ($pegawaiQuery) use ($logger) {
                        $pegawaiQuery
                            ->whereIn(DB::raw('LOWER(u.level_user)'), ['pegawai', 'user'])
                            ->whereExists(function ($accessQuery) use ($logger) {
                                $accessQuery->selectRaw('1')
                                    ->from('user_logger_access as ula')
                                    ->whereColumn('ula.user_id', 'u.id_user')
                                    ->where('ula.logger_id', $logger->id_logger);
                            });
                    });
            })
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=MultiInstansiFcmTokensTest`
Expected: PASS (1 test).

- [ ] **Step 5: Run the existing suite to check for regressions**

Run: `php artisan test`
Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add app/Services/FcmService.php tests/Feature/MultiInstansiFcmTokensTest.php
git commit -m "feat: send logger warnings to users with cross-instansi grants"
```

---

## Task 4: User modal — pick loggers across instansi (UI)

**Files:**
- Modify: `resources/views/users/index.blade.php` — Alpine methods at `:642-679`, create-modal picker at `:261-280`, edit-modal picker at `:423-441`.

**Interfaces:**
- Consumes: existing Alpine globals `this.loggerOptions` (items: `{id_logger, nama_logger, instansi_id}`), `this.instansiList` (items: `{id, nama}`), `this.isSuperAdminUser`; existing methods `toggleLoggerAccess(formType, id, checked)`, `getForm(formType)`, `isPegawaiRole(role)`, `getFormInstansiId(formType)`.
- Produces: new Alpine methods `isInstansiAdminRole(role)`, `shouldShowLoggerPicker(role)`, `getLoggerGroups(formType)`, `isInstansiGroupAllChecked(formType, group)`, `toggleInstansiGroup(formType, group, checked)`; modified `getLoggerOptions(formType)`, `handleRoleChange(formType, roleName)`.

> **Note on testing:** the picker's grouping and visibility is client-side Alpine logic that PHPUnit cannot exercise. This task is verified manually (Step 6) using the `verify` skill. The server already sends the correct data (`UserController::index()` builds `$loggerOptions` via `forUser($currentUser)`, which returns all instansi for a superadmin), so no controller change is needed.

- [ ] **Step 1: Add the new helper methods**

In `resources/views/users/index.blade.php`, immediately after `isPegawaiRole(role)` (ends at `:627`), add:

```javascript
                isInstansiAdminRole(role) {
                    const normalized = this.normalizeRole(role);
                    return normalized === 'instansi_admin' || normalized === 'admin';
                },

                shouldShowLoggerPicker(role) {
                    if (this.isPegawaiRole(role)) return true;
                    // Superadmin boleh memberi logger tambahan lintas instansi
                    // ke seorang instansi_admin.
                    return this.isSuperAdminUser && this.isInstansiAdminRole(role);
                },
```

- [ ] **Step 2: Make `getLoggerOptions` show all instansi for a superadmin**

Replace `getLoggerOptions(formType)` (`:642-647`) with:

```javascript
                getLoggerOptions(formType) {
                    // Superadmin memilih dari semua instansi; user lain hanya
                    // dari instansi (terpilih) miliknya.
                    if (this.isSuperAdminUser) {
                        return this.loggerOptions;
                    }

                    const instansiId = this.getFormInstansiId(formType);
                    if (!instansiId) return [];

                    return this.loggerOptions.filter((l) => String(l.instansi_id) === instansiId);
                },
```

- [ ] **Step 3: Add grouping + per-instansi select-all helpers**

Immediately after the new `getLoggerOptions` (from Step 2), add:

```javascript
                getLoggerGroups(formType) {
                    const groups = {};
                    this.getLoggerOptions(formType).forEach((l) => {
                        const key = String(l.instansi_id);
                        if (!groups[key]) {
                            const inst = this.instansiList.find((i) => String(i.id) === key);
                            groups[key] = {
                                instansi_id: key,
                                instansi_nama: inst ? inst.nama : 'Tanpa Instansi',
                                loggers: [],
                            };
                        }
                        groups[key].loggers.push(l);
                    });
                    return Object.values(groups);
                },

                isInstansiGroupAllChecked(formType, group) {
                    const form = this.getForm(formType);
                    return group.loggers.length > 0
                        && group.loggers.every((l) => form.logger_access.includes(String(l.id_logger)));
                },

                toggleInstansiGroup(formType, group, checked) {
                    group.loggers.forEach((l) =>
                        this.toggleLoggerAccess(formType, String(l.id_logger), checked));
                },
```

- [ ] **Step 4: Update `handleRoleChange` to keep grants for the picker-eligible roles**

Replace `handleRoleChange(formType, roleName)` (`:669-679`) with:

```javascript
                handleRoleChange(formType, roleName) {
                    const form = this.getForm(formType);
                    form.level_user = roleName;

                    if (!this.shouldShowLoggerPicker(form.level_user)) {
                        form.logger_access = [];
                        return;
                    }

                    this.syncSelectedLoggerAccess(formType);
                },
```

- [ ] **Step 5: Update both modal pickers to show for eligible roles and render grouped**

In the **create** modal, replace the block at `:261-280` with:

```html
                            <div x-show="shouldShowLoggerPicker(createForm.level_user)" x-cloak>
                                <div class="flex items-center justify-between mb-2">
                                    <label class="block text-sm font-semibold">Akses Logger / Pos</label>
                                    <span class="text-xs text-slate-500"
                                        x-text="`${createForm.logger_access.length} dipilih`"></span>
                                </div>
                                <p class="text-xs text-slate-500 mb-2" x-show="!isPegawaiRole(createForm.level_user)" x-cloak>
                                    Logger tambahan (opsional) lintas instansi untuk admin ini.</p>
                                <div class="max-h-48 overflow-y-auto border border-gray-200 rounded-lg p-3 space-y-3">
                                    <template x-for="group in getLoggerGroups('create')" :key="'create-grp-' + group.instansi_id">
                                        <div>
                                            <label class="flex items-center gap-2 text-xs font-semibold text-slate-600 mb-1"
                                                x-show="isSuperAdminUser">
                                                <input type="checkbox" class="rounded border-slate-300"
                                                    :checked="isInstansiGroupAllChecked('create', group)"
                                                    @change="toggleInstansiGroup('create', group, $event.target.checked)">
                                                <span x-text="group.instansi_nama"></span>
                                            </label>
                                            <div class="space-y-2 pl-1">
                                                <template x-for="logger in group.loggers" :key="'create-log-' + logger.id_logger">
                                                    <label class="flex items-center gap-2 text-sm">
                                                        <input type="checkbox" class="rounded border-slate-300"
                                                            :checked="createForm.logger_access.includes(String(logger.id_logger))"
                                                            @change="toggleLoggerAccess('create', String(logger.id_logger), $event.target.checked)">
                                                        <span x-text="`${logger.nama_logger} (${logger.id_logger})`"></span>
                                                    </label>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                    <div x-show="getLoggerGroups('create').length === 0" class="text-xs text-slate-500">
                                        Logger belum tersedia.</div>
                                </div>
                            </div>
```

In the **edit** modal, replace the block at `:423-441` with the same markup but with `create` → `edit` and `createForm` → `editForm` throughout:

```html
                                <div x-show="shouldShowLoggerPicker(editForm.level_user)" x-cloak>
                                    <div class="flex items-center justify-between mb-2">
                                        <label class="block text-sm font-semibold">Akses Logger / Pos</label>
                                        <span class="text-xs text-slate-500"
                                            x-text="`${editForm.logger_access.length} dipilih`"></span>
                                    </div>
                                    <p class="text-xs text-slate-500 mb-2" x-show="!isPegawaiRole(editForm.level_user)" x-cloak>
                                        Logger tambahan (opsional) lintas instansi untuk admin ini.</p>
                                    <div class="max-h-48 overflow-y-auto border border-gray-200 rounded-lg p-3 space-y-3">
                                        <template x-for="group in getLoggerGroups('edit')" :key="'edit-grp-' + group.instansi_id">
                                            <div>
                                                <label class="flex items-center gap-2 text-xs font-semibold text-slate-600 mb-1"
                                                    x-show="isSuperAdminUser">
                                                    <input type="checkbox" class="rounded border-slate-300"
                                                        :checked="isInstansiGroupAllChecked('edit', group)"
                                                        @change="toggleInstansiGroup('edit', group, $event.target.checked)">
                                                    <span x-text="group.instansi_nama"></span>
                                                </label>
                                                <div class="space-y-2 pl-1">
                                                    <template x-for="logger in group.loggers" :key="'edit-log-' + logger.id_logger">
                                                        <label class="flex items-center gap-2 text-sm">
                                                            <input type="checkbox" class="rounded border-slate-300"
                                                                :checked="editForm.logger_access.includes(String(logger.id_logger))"
                                                                @change="toggleLoggerAccess('edit', String(logger.id_logger), $event.target.checked)">
                                                            <span x-text="`${logger.nama_logger} (${logger.id_logger})`"></span>
                                                        </label>
                                                    </template>
                                                </div>
                                            </div>
                                        </template>
                                        <div x-show="getLoggerGroups('edit').length === 0" class="text-xs text-slate-500">
                                            Logger belum tersedia.</div>
                                    </div>
                                </div>
```

> Leave the client-side submit guards (`isPegawaiRole(...) && logger_access.length === 0` at `:726` and `:844`) unchanged — pegawai still requires ≥1 logger, instansi_admin extras stay optional.

- [ ] **Step 6: Manual verification (use the `verify` skill)**

Run the app and, logged in as a **superadmin**:
1. Open **Tambah User**. Choose role **pegawai**, pick an instansi for the home. The "Akses Logger / Pos" picker shows loggers **grouped by instansi name across all instansi** (not just the home instansi). Tick one logger from instansi A and one from instansi B; the "dipilih" counter reflects both. Save → reopen via **Edit** → both ticks persist (one is cross-instansi).
2. Create another user with role **instansi_admin**. The picker now appears (previously hidden) with the "Logger tambahan (opsional)" hint. Leaving it empty saves fine; ticking an extra cross-instansi logger saves and persists.
3. The per-instansi group header checkbox ticks/unticks all loggers in that group.
4. Log in as an **instansi_admin** (non-superadmin) and open Tambah User → the picker for a pegawai shows **only** that admin's own-instansi loggers (no cross-instansi groups), confirming the gate.

Confirm each behaves as described before committing.

- [ ] **Step 7: Commit**

```bash
git add resources/views/users/index.blade.php
git commit -m "feat: superadmin can pick cross-instansi loggers in user modal"
```

---

## Self-Review

**Spec coverage:**
- Spec §"Komponen yang Diubah" 1 (`scopeForUser`) → Task 1. ✔
- 2 (`syncLoggerAccessForUser`) → Task 2. ✔
- 3 (`FcmService`) → Task 3. ✔
- 4 (Blade UI) → Task 4. ✔
- Decision #1 (superadmin-only grant) → Task 2 Step 3 (`!$actor->isSuperAdmin()` restriction) + Task 2 test `test_instansi_admin_actor_cannot_grant_cross_instansi` + Task 4 gate (Step 6.4). ✔
- Decision #2 (notifications follow visibility) → Task 3. ✔
- Decision #3 (read-only) → no management code touched; only visibility/notification queries + the picker. ✔
- Decision #4 (no auto-include; select-all is a snapshot) → Task 4 `toggleInstansiGroup` ticks current loggers only. ✔
- "No new table" → no migration task; pivot reused. ✔
- Testing plan (unit scope, controller, FcmService, regression) → Tasks 1-3 tests + each task's "run existing suite" step. ✔

**Placeholder scan:** No TBD/TODO/"handle edge cases"/"similar to Task N". Every code step shows complete code. ✔

**Type/name consistency:** `forUser` used consistently; `syncLoggerAccessForUser(t_User, ?array, t_User)` signature unchanged from current call sites (`store`/`update` at `:128`/`:269`); new Alpine methods (`shouldShowLoggerPicker`, `getLoggerGroups`, `isInstansiGroupAllChecked`, `toggleInstansiGroup`, `isInstansiAdminRole`) are defined in Task 4 and referenced only within Task 4's templates. `user_logger_access` columns (`user_id`, `logger_id`) consistent across all tasks and tests. ✔
