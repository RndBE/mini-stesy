{{--
    Isi form Tambah/Ubah Manual Book. Semua nilai dikendalikan Alpine lewat
    `form` di komponen induk (kelola.blade.php), bukan `old()` langsung, supaya
    satu form bisa dipakai dua mode sekaligus dipulihkan saat validasi gagal.

    Tata letak dua kolom di lg+: kiri "apa dokumennya", kanan "siapa yang boleh
    melihat". Di layar kecil tetap satu kolom.
--}}
@php
    use App\Models\ManualBook;

    $usersByInstansi = $userOptions->groupBy(fn($user) => $user->instansi->nama ?? 'Tanpa Instansi');

    $visibilityChoices = [
        ManualBook::VISIBILITY_ALL => [
            'label' => 'Semua User',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>',
        ],
        ManualBook::VISIBILITY_INSTANSI => [
            'label' => 'Instansi Tertentu',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>',
        ],
        ManualBook::VISIBILITY_ROLE => [
            'label' => 'Role Tertentu',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>',
        ],
        ManualBook::VISIBILITY_SELECTED => [
            'label' => 'User Terpilih',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>',
        ],
    ];

    // Kata benda untuk penghitung "N ... dipilih" di bawah daftar target.
    $satuanTarget = [
        ManualBook::VISIBILITY_INSTANSI => 'instansi',
        ManualBook::VISIBILITY_ROLE => 'role',
        ManualBook::VISIBILITY_SELECTED => 'user',
    ];
@endphp

<div class="grid gap-6 lg:grid-cols-2">

    {{-- ============ Kolom kiri: informasi dokumen ============ --}}
    <div class="space-y-5">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Informasi Dokumen</p>

        {{-- Judul --}}
        <div>
            <label for="mb_judul" class="block text-sm font-medium text-slate-700">Judul Dokumen</label>
            <input type="text" name="judul" id="mb_judul" x-model="form.judul" required
                class="mt-1 block w-full rounded-xl border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                placeholder="Contoh: Panduan Penggunaan Aplikasi Mobile">
            @error('judul')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        {{-- Deskripsi --}}
        <div>
            <label for="mb_deskripsi" class="block text-sm font-medium text-slate-700">
                Deskripsi <span class="font-normal text-slate-400">(opsional)</span>
            </label>
            <textarea name="deskripsi" id="mb_deskripsi" rows="3" x-model="form.deskripsi"
                class="mt-1 block w-full rounded-xl border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                placeholder="Ringkasan singkat isi dokumen ini."></textarea>
            @error('deskripsi')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        {{-- File --}}
        <div>
            <span class="block text-sm font-medium text-slate-700"
                x-text="mode === 'edit' ? 'Ganti File' : 'File Dokumen'"></span>

            {{-- File yang sedang terpasang, hanya saat mode ubah --}}
            <template x-if="mode === 'edit' && form.fileName">
                <div class="mt-1 flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
                    <svg class="h-5 w-5 flex-shrink-0 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    <div class="min-w-0">
                        <p class="truncate font-medium text-slate-700" x-text="form.fileName"></p>
                        <p class="text-xs text-slate-500" x-text="form.fileMeta"></p>
                    </div>
                </div>
            </template>

            {{-- Dropzone: input file asli disembunyikan pakai sr-only (tetap bisa
                 difokus browser sehingga validasi `required` tetap jalan). --}}
            <label
                class="mt-2 flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center transition hover:border-blue-400 hover:bg-blue-50">
                <input type="file" name="file" class="sr-only" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx"
                    :required="mode === 'create'"
                    @change="pilihFile($event)">

                <svg class="h-7 w-7 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 16V8m0 0l-3 3m3-3l3 3" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 16.5V19a1 1 0 01-1 1H5a1 1 0 01-1-1v-2.5" />
                </svg>

                <template x-if="!fileDipilih">
                    <span class="mt-2 block">
                        <span class="block text-sm font-semibold text-slate-700">Klik untuk pilih file</span>
                        <span class="mt-0.5 block text-xs text-slate-500">PDF, Word, Excel, atau PowerPoint · maksimal {{ (int) (ManualBook::MAX_FILE_KB / 1024) }} MB</span>
                    </span>
                </template>

                <template x-if="fileDipilih">
                    <span class="mt-2 block min-w-0">
                        <span class="block truncate text-sm font-semibold text-slate-800" x-text="fileDipilih.name"></span>
                        <span class="mt-0.5 block text-xs text-slate-500" x-text="labelUkuran(fileDipilih.size) + ' · klik untuk ganti'"></span>
                    </span>
                </template>
            </label>

            <p class="mt-1.5 text-xs text-slate-500">
                PDF bisa dibaca langsung di browser, format lain akan terunduh.
                <span x-show="mode === 'edit'" x-cloak>Biarkan kosong jika tidak ingin mengganti file.</span>
            </p>
            {{-- Ditolak di sisi klien: file tidak pernah dikirim --}}
            <p x-show="galatFile" x-cloak class="mt-1 text-xs text-red-600" x-text="galatFile"></p>

            @error('file')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        {{-- Status --}}
        <div class="border-t border-slate-200 pt-5">
            <span class="block text-sm font-medium text-slate-700">Status</span>
            <label class="relative mt-2 inline-flex cursor-pointer items-center">
                {{-- Hidden input menjaga nilai tetap terkirim saat checkbox tidak dicentang. --}}
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" class="peer sr-only" x-model="form.is_active">
                <div class="peer h-7 w-14 rounded-full bg-slate-200 after:absolute after:left-[2px] after:top-[2px] after:h-6 after:w-6 after:rounded-full after:border after:border-slate-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-blue-600 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-none"></div>
                <span class="ml-3 text-sm font-medium text-slate-700">Aktif (tampil ke user)</span>
            </label>
        </div>
    </div>

    {{-- ============ Kolom kanan: hak akses ============ --}}
    {{-- space-y-5 disamakan dengan kolom kiri supaya jarak label ke isi sejajar --}}
    <div class="space-y-5 border-t border-slate-200 pt-6 lg:border-l lg:border-t-0 lg:pl-6 lg:pt-0">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Hak Akses</p>

        <div class="space-y-2">
            @foreach ($visibilityChoices as $value => $pilihan)
                @php $aktif = "form.visibility === '{$value}'"; @endphp

                <div>
                    {{-- Status visual dikendalikan Alpine, bukan peer-checked:, karena
                         titik radio & ikon berada sebagai turunan sehingga tidak
                         terjangkau varian peer. --}}
                    <label class="block cursor-pointer">
                        <input type="radio" name="visibility" value="{{ $value }}" class="sr-only"
                            x-model="form.visibility" @change="form.targets = []">

                        <span class="block rounded-xl border px-4 py-3 transition"
                            :class="{{ $aktif }} ? 'border-blue-500 bg-blue-50' : 'border-slate-200 hover:border-slate-300'">
                            {{-- items-center: kartu kini satu baris, tanpa keterangan --}}
                            <span class="flex items-center gap-3">
                                {{-- Titik radio --}}
                                <span class="flex h-4 w-4 shrink-0 items-center justify-center rounded-full border bg-white"
                                    :class="{{ $aktif }} ? 'border-blue-600' : 'border-slate-300'">
                                    <span class="h-2 w-2 rounded-full transition-colors"
                                        :class="{{ $aktif }} ? 'bg-blue-600' : 'bg-transparent'"></span>
                                </span>

                                {{-- Ikon mode --}}
                                <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    :class="{{ $aktif }} ? 'text-blue-600' : 'text-slate-400'">
                                    {!! $pilihan['icon'] !!}
                                </svg>

                                <span class="min-w-0 flex-1 truncate text-sm font-semibold"
                                    :class="{{ $aktif }} ? 'text-blue-900' : 'text-slate-700'">{{ $pilihan['label'] }}</span>
                            </span>
                        </span>
                    </label>

                    {{-- Daftar target sengaja di LUAR <label>: kalau di dalam, klik
                         checkbox akan ikut mengaktifkan radio induknya. --}}
                    @if ($value !== ManualBook::VISIBILITY_ALL)
                        <div x-show="{{ $aktif }}" x-cloak class="ml-4 mt-2 border-l-2 border-blue-100 pl-4">
                            @if ($value === ManualBook::VISIBILITY_INSTANSI)
                                <div class="max-h-44 space-y-1 overflow-y-auto rounded-xl border border-slate-200 bg-slate-50 p-3">
                                    @forelse ($instansiOptions as $instansi)
                                        <label class="flex cursor-pointer items-center gap-3 rounded-lg p-1.5 transition hover:bg-white">
                                            <input type="checkbox" name="targets[]" value="{{ $instansi->id }}" x-model="form.targets"
                                                :disabled="!({{ $aktif }})"
                                                class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                            <span class="text-sm font-medium text-slate-800">{{ $instansi->nama }}</span>
                                        </label>
                                    @empty
                                        <p class="py-3 text-center text-sm text-slate-400">Belum ada instansi terdaftar.</p>
                                    @endforelse
                                </div>
                            @elseif ($value === ManualBook::VISIBILITY_ROLE)
                                <div class="max-h-44 space-y-1 overflow-y-auto rounded-xl border border-slate-200 bg-slate-50 p-3">
                                    @forelse ($roleOptions as $role)
                                        <label class="flex cursor-pointer items-center gap-3 rounded-lg p-1.5 transition hover:bg-white">
                                            <input type="checkbox" name="targets[]" value="{{ $role->role_name }}" x-model="form.targets"
                                                :disabled="!({{ $aktif }})"
                                                class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                            <span class="text-sm font-medium text-slate-800">{{ $role->role_name }}</span>
                                        </label>
                                    @empty
                                        <p class="py-3 text-center text-sm text-slate-400">Belum ada role terdaftar.</p>
                                    @endforelse
                                </div>
                            @else
                                <div class="max-h-56 space-y-3 overflow-y-auto rounded-xl border border-slate-200 bg-slate-50 p-3">
                                    @forelse ($usersByInstansi as $namaInstansi => $users)
                                        <div>
                                            <p class="mb-1 text-xs font-semibold uppercase tracking-wide text-slate-400">{{ $namaInstansi }}</p>
                                            @foreach ($users as $user)
                                                <label class="flex cursor-pointer items-center gap-3 rounded-lg p-1.5 transition hover:bg-white">
                                                    <input type="checkbox" name="targets[]" value="{{ $user->id_user }}" x-model="form.targets"
                                                        :disabled="!({{ $aktif }})"
                                                        class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                                    <span class="truncate text-sm font-medium text-slate-800">{{ $user->nama }}</span>
                                                    <span class="ml-auto shrink-0 rounded-full bg-slate-200 px-2 py-0.5 text-xs text-slate-600">{{ $user->level_user }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    @empty
                                        <p class="py-3 text-center text-sm text-slate-400">Belum ada user terdaftar.</p>
                                    @endforelse
                                </div>
                            @endif

                            <p class="mt-1.5 text-xs text-slate-500" x-show="form.targets.length > 0" x-cloak>
                                <span x-text="form.targets.length"></span> {{ $satuanTarget[$value] }} dipilih
                            </p>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        @error('visibility')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
        @error('targets')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
</div>
