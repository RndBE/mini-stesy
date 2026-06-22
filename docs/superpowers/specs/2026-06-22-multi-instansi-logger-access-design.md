# Multi-Instansi Logger Access (Akses Logger Lintas Instansi)

**Tanggal:** 2026-06-22
**Status:** Disetujui (siap masuk tahap implementation plan)

## Masalah

Saat ini setiap user terikat pada **satu** `instansi_id` (kolom di `t_user`). Visibilitas
logger ditentukan oleh `t_Logger::scopeForUser()`:

- **superadmin** → semua logger
- **instansi_admin** → logger dengan `instansi_id = user.instansi_id`
- **pegawai** → logger di instansinya **dan** terdaftar di pivot `user_logger_access`

Tidak ada jalan tengah: seorang user tidak bisa diberi akses ke logger milik instansi
**lain** tanpa menjadikannya superadmin (yang melihat *segalanya*). Superadmin perlu bisa
membuat user — mis. user **RND** milik perusahaan sendiri — yang **juga** dapat melihat
**logger spesifik pilihan** dari instansi lain (tidak otomatis semua, dipilih satu per satu).

## Keputusan Desain

Pendekatan **Opsi B**: tidak ada tabel baru. Pakai ulang pivot `user_logger_access`
`(user_id, logger_id)` yang sudah ada, dan **longgarkan** aturan "logger harus se-instansi
dengan user". Grant lintas instansi = mencentang logger spesifik.

Keputusan pendamping:

1. **Hanya superadmin** yang boleh memberi grant logger lintas instansi. instansi_admin
   tetap dibatasi instansinya sendiri (mencegah privilege escalation).
2. **Notifikasi mengikuti visibilitas** — logika di `FcmService` disamakan dengan scope,
   supaya user juga menerima notifikasi untuk logger tambahannya.
3. **Read-only** — grant ini hanya soal *melihat* logger (Beranda, Peta, Rekap, Analisa,
   Data Masuk, Tingkat Siaga) + notifikasi. Tidak menambah hak kelola/edit apa pun.
4. **Bukan auto-include**: jika superadmin mencentang "semua di instansi X", itu hanya
   mencentang logger yang ada **saat itu**. Logger baru yang ditambahkan ke instansi X
   kelak **tidak otomatis ikut** — harus dicentang ulang. (Trade-off yang diterima demi
   kesederhanaan; "pilih semua" hanyalah pemanis UI.)

## Model Data

Tidak ada migrasi tabel baru.

- `user_logger_access (id, user_id → t_user.id_user, logger_id → t_logger.id_logger, timestamps)`
  tetap apa adanya, dengan unique `(user_id, logger_id)`.
- Perubahan hanya pada **semantik**: pivot kini boleh memuat logger dari instansi mana pun,
  bukan hanya instansi user.

Peran pivot per role setelah perubahan:

| Role           | Isi pivot                                                              |
|----------------|-----------------------------------------------------------------------|
| superadmin     | tidak dipakai (melihat semua) → selalu di-sync kosong                  |
| instansi_admin | logger **tambahan** lintas instansi (di luar instansinya)             |
| pegawai        | **seluruh** himpunan logger yang boleh dilihat (boleh lintas instansi) |

## Komponen yang Diubah

### 1. `app/Models/t_Logger.php` — `scopeForUser()`

Tulis ulang menjadi satu logika OR. Instansi-match (untuk instansi_admin) **ATAU** ada di
pivot.

```php
public function scopeForUser($query, $user)
{
    if (!$user) {
        return $query->whereRaw('1 = 0');
    }

    if ($user->isSuperAdmin()) {
        return $query;
    }

    return $query->where(function ($q) use ($user) {
        if ($user->isInstansiAdmin()) {
            $q->where('instansi_id', $user->instansi_id);
        }
        $q->orWhereExists(function ($sub) use ($user) {
            $sub->selectRaw('1')
                ->from('user_logger_access as ula')
                ->whereColumn('ula.logger_id', 't_logger.id_logger')
                ->where('ula.user_id', $user->id_user);
        });
    });
}
```

Hasil per kasus:

- **pegawai** → `WHERE (EXISTS pivot)` (Laravel memperlakukan `orWhereExists` pertama dalam
  grup sebagai `where`). Sama seperti sekarang, tapi pivot kini boleh lintas instansi.
- **instansi_admin** → `WHERE (instansi_id = X OR EXISTS pivot)` — instansinya sendiri plus
  logger tambahan.
- pegawai dengan pivot kosong → tidak melihat apa pun (sama seperti sekarang).

> Catatan: pembungkus `where(function () { ... })` penting agar OR ter-grup dengan benar dan
> tidak "membocorkan" semua baris ketika digabung dengan filter lain di query pemanggil.

### 2. `app/Http/Controllers/UserController.php` — `syncLoggerAccessForUser()`

Longgarkan agar:

- Role yang berhak punya grant: **pegawai** (himpunan penuh, wajib ≥1) **dan**
  **instansi_admin** (tambahan, opsional/boleh kosong). superadmin → selalu sync kosong.
- Logger assignable = `t_Logger::forUser($actor)`; **jika actor bukan superadmin**, tambahkan
  `where('instansi_id', $actor->instansi_id)`. Ini gerbang keamanan: hanya superadmin yang
  bisa memberi logger lintas instansi.
- Hapus pembatasan lama `where('instansi_id', $user->instansi_id)` (yang memblokir
  lintas instansi).

```php
private function syncLoggerAccessForUser(t_User $user, ?array $loggerIds, t_User $actor): void
{
    // Hanya pegawai & instansi_admin yang punya grant eksplisit.
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

Validasi `store()`/`update()` (`logger_access.*` → `Rule::exists('t_logger','id_logger')`)
sudah menerima logger mana pun; tidak perlu diubah.

### 3. `app/Services/FcmService.php` — `getLoggerWarningTokens()`

Samakan dengan scope. Hanya cabang **instansi_admin** yang perlu ditambah "ATAU punya grant
pivot"; cabang pegawai sudah murni pivot-based (otomatis mendukung lintas instansi).

Cabang instansi_admin menjadi:

```php
->orWhere(function ($adminQuery) use ($logger) {
    $adminQuery
        ->whereIn(DB::raw('LOWER(u.level_user)'), ['instansi_admin', 'admin'])
        ->where(function ($w) use ($logger) {
            $w->where('u.instansi_id', $logger->instansi_id)
              ->orWhereExists(function ($accessQuery) use ($logger) {
                  $accessQuery->selectRaw('1')
                      ->from('user_logger_access as ula')
                      ->whereColumn('ula.user_id', 'u.id_user')
                      ->where('ula.logger_id', $logger->id_logger);
              });
        });
})
```

### 4. `resources/views/users/index.blade.php` (+ Alpine JS di file yang sama)

- Tampilkan picker "Akses Logger / Pos" untuk role **pegawai** *dan* **instansi_admin**
  (bukan hanya pegawai). Saat ini `x-show="isPegawaiRole(...)"` → ubah agar mencakup
  instansi_admin. Untuk instansi_admin beri label/keterangan bahwa ini "logger tambahan
  (opsional, lintas instansi)".
- Saat actor = **superadmin**: `getLoggerOptions()` menampilkan logger dari **semua**
  instansi, **dikelompokkan per instansi** (pakai `instansi_id` pada tiap opsi + `instansiList`
  untuk label). Hentikan pemfilteran ke `createForm.instansi_id` saja.
- Saat actor = **instansi_admin**: perilaku tetap (hanya logger instansinya).
- Tambahkan checkbox "pilih semua" per grup instansi sebagai pemanis (mencentang yang ada
  saat itu; lihat keputusan #4).

Controller `index()` sudah mengirim `loggerOptions` via `forUser($currentUser)` (untuk
superadmin = semua logger lintas instansi, lengkap dengan `instansi_id`) dan `instansiList`
penuh. Jadi **tidak ada perubahan data di controller index** — hanya logika tampilan/JS.

## Pengujian

- **Unit (scopeForUser)**: superadmin (semua), instansi_admin tanpa grant (= instansinya),
  instansi_admin dengan grant lintas instansi (= instansinya ∪ grant), pegawai (hanya grant,
  termasuk lintas instansi), pegawai pivot kosong (kosong), user tanpa login (kosong).
- **Feature (UserController)**: superadmin membuat user RND dengan logger lintas instansi →
  pivot tersimpan benar; instansi_admin actor mencoba assign logger instansi lain → ditolak
  ("di luar wewenang"); pegawai tanpa logger → ditolak ("minimal 1").
- **FcmService**: instansi_admin dengan grant lintas instansi masuk daftar token saat logger
  tambahannya warning; pegawai dengan grant lintas instansi juga.
- **Regresi**: perilaku superadmin/instansi_admin/pegawai existing tetap sama bila tanpa grant
  lintas instansi.

## Di Luar Cakupan (YAGNI)

- Tabel `user_instansi_access` / grant "satu instansi penuh otomatis" (auto-include logger
  baru). Sengaja tidak dibuat — lihat keputusan #4.
- Hak kelola/edit di instansi lain (tetap read-only).
- Memberi instansi_admin kemampuan menggrant lintas instansi (tetap superadmin-only).
