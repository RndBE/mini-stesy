@extends('layouts.app')

@php
    // Warna & ikon per format file. Ditulis sebagai kelas utuh supaya terbaca
    // pemindai Tailwind saat build.
    $formatStyles = [
        'pdf' => [
            'tile' => 'bg-red-50 text-red-600',
            'badge' => 'bg-red-100 text-red-700',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6z"/><path stroke-linecap="round" stroke-linejoin="round" d="M14 2v6h6"/>',
        ],
        'word' => [
            'tile' => 'bg-blue-50 text-blue-600',
            'badge' => 'bg-blue-100 text-blue-700',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6z"/><path stroke-linecap="round" stroke-linejoin="round" d="M14 2v6h6"/><path stroke-linecap="round" stroke-linejoin="round" d="M8 13.5h8M8 17h5"/>',
        ],
        'excel' => [
            'tile' => 'bg-emerald-50 text-emerald-600',
            'badge' => 'bg-emerald-100 text-emerald-700',
            'icon' => '<rect x="3" y="4" width="18" height="16" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="M3 9.5h18M3 14.5h18M9.5 4v16M15 4v16"/>',
        ],
        'powerpoint' => [
            'tile' => 'bg-orange-50 text-orange-600',
            'badge' => 'bg-orange-100 text-orange-700',
            'icon' => '<rect x="3" y="4" width="18" height="12" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 16v4M8.5 20h7"/>',
        ],
    ];

    $formatByExtension = [
        'pdf' => 'pdf',
        'doc' => 'word',
        'docx' => 'word',
        'xls' => 'excel',
        'xlsx' => 'excel',
        'ppt' => 'powerpoint',
        'pptx' => 'powerpoint',
    ];

    $gayaFormat = fn($book) => $formatStyles[$formatByExtension[$book->fileExtension()] ?? 'word'];

    // Payload untuk panel detail di kanan.
    $docs = $books->mapWithKeys(function ($book) use ($gayaFormat) {
        $gaya = $gayaFormat($book);

        return [$book->id => [
            'id' => $book->id,
            'judul' => $book->judul,
            'deskripsi' => (string) $book->deskripsi,
            'format' => strtoupper($book->fileExtension()),
            'ukuran' => $book->fileSizeLabel(),
            'diperbarui' => $book->updated_at?->timezone('Asia/Jakarta')->translatedFormat('d M Y') ?? '-',
            'isPdf' => $book->isPdf(),
            // Pratinjau file besar tidak dimuat otomatis supaya halaman tidak
            // berat saat dibuka, terutama di koneksi seluler.
            'ringan' => (int) $book->file_size <= 8 * 1024 * 1024,
            'previewUrl' => route('manual-book.preview', $book->id),
            'downloadUrl' => route('manual-book.download', $book->id),
            'tile' => $gaya['tile'],
            'badge' => $gaya['badge'],
            'icon' => $gaya['icon'],
        ]];
    })->all();

    $tampilkanPencarian = $books->count() >= 5;
@endphp

@section('content')
<style>[x-cloak]{display:none!important}</style>

@if ($books->isEmpty())
    {{-- Belum ada dokumen: tidak perlu dua panel --}}
    <div class="space-y-4">
        <h2 class="text-2xl font-bold tracking-tight text-slate-800">Manual Book</h2>

        <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-50">
                <svg class="h-7 w-7 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
            </div>
            <p class="mt-4 text-sm font-medium text-slate-600">Belum ada manual book untuk akun Anda.</p>
            <p class="mt-1 text-sm text-slate-400">
                @if ($canManage)
                    Unggah dokumen pertama lewat halaman Kelola.
                @else
                    Hubungi administrator jika Anda membutuhkan panduan penggunaan sistem.
                @endif
            </p>
            @if ($canManage)
                <a href="{{ route('manual-book.kelola') }}"
                    class="mt-5 inline-flex h-11 items-center gap-2 rounded-xl bg-slate-800 px-5 text-sm font-semibold text-white transition-colors hover:bg-slate-700">
                    Kelola Manual Book
                </a>
            @endif
        </div>
    </div>
@else
<div x-data="{
        dokumen: @js($docs),
        terpilih: @js($books->first()->id),
        muatPratinjau: @js($docs[$books->first()->id]['ringan']),
        detailTerbuka: false,
        pratinjauPenuh: false,
        isMobile: window.matchMedia('(max-width: 1023px)').matches,
        q: '',

        init() {
            // Panel detail jadi layar penuh di mobile, kolom biasa di desktop.
            window.matchMedia('(max-width: 1023px)').addEventListener('change', (e) => {
                this.isMobile = e.matches;
                if (!e.matches) this.detailTerbuka = false;
            });

            // Esc / tombol keluar bawaan browser -> state ikut disinkronkan.
            document.addEventListener('fullscreenchange', () => {
                if (!document.fullscreenElement) this.pratinjauPenuh = false;
            });
        },

        get aktif() {
            return this.dokumen[this.terpilih] ?? null;
        },

        pilih(id) {
            this.terpilih = id;
            this.muatPratinjau = this.dokumen[id]?.ringan ?? false;
            if (this.isMobile) this.detailTerbuka = true;
        },

        /**
         * Coba Fullscreen API dulu supaya UI browser ikut tersembunyi. Kalau
         * ditolak (iOS Safari tidak mengizinkan elemen non-video), overlay CSS
         * tetap memberi tampilan penuh di dalam halaman.
         */
        async togglePenuh() {
            if (this.pratinjauPenuh) {
                if (document.fullscreenElement) {
                    try { await document.exitFullscreen(); } catch (e) { /* diabaikan */ }
                }
                this.pratinjauPenuh = false;
                return;
            }

            this.pratinjauPenuh = true;

            const wadah = this.$refs.wadahPratinjau;
            if (wadah?.requestFullscreen) {
                try { await wadah.requestFullscreen(); } catch (e) { /* pakai overlay saja */ }
            }
        },

        cocok(teks) {
            const kata = this.q.trim().toLowerCase();
            return kata === '' || teks.includes(kata);
        },

        get jumlahCocok() {
            const kata = this.q.trim().toLowerCase();
            return Object.values(this.dokumen).filter(
                (d) => kata === '' || (d.judul + ' ' + d.deskripsi).toLowerCase().includes(kata)
            ).length;
        },
    }"
    {{-- Esc untuk mode fallback; kalau Fullscreen API aktif, browser yang menangani
         Esc dan state disinkronkan lewat listener fullscreenchange. --}}
    @keydown.escape.window="if (pratinjauPenuh && !document.fullscreenElement) pratinjauPenuh = false"
    class="lg:grid lg:h-[calc(100vh-7rem)] lg:grid-cols-[17rem_1fr] lg:gap-4">

    {{-- ============ Panel kiri: daftar dokumen ============ --}}
    <aside class="flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white">
        <div class="border-b border-slate-200 px-4 py-4">
            {{-- items-center: judul kini satu baris, tanpa subjudul di bawahnya --}}
            <div class="flex items-center justify-between gap-2">
                <h2 class="min-w-0 truncate text-lg font-bold tracking-tight text-slate-800">Manual Book</h2>

                @if ($canManage)
                    <a href="{{ route('manual-book.kelola') }}" title="Kelola Manual Book"
                        class="shrink-0 rounded-lg border border-slate-200 p-2 text-slate-500 transition hover:bg-slate-50 hover:text-slate-700">
                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </a>
                @endif
            </div>

            @if ($tampilkanPencarian)
                <div class="relative mt-3">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
                    </svg>
                    <input type="search" x-model="q" placeholder="Cari dokumen…"
                        class="h-10 w-full rounded-xl border border-slate-200 bg-white pl-9 pr-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-200">
                </div>
            @endif
        </div>

        <div class="flex-1 space-y-1 overflow-y-auto p-2">
            @foreach ($books as $book)
                @php $gaya = $gayaFormat($book); @endphp

                <button type="button" @click="pilih({{ $book->id }})"
                    x-show="cocok(@js(mb_strtolower($book->judul . ' ' . ($book->deskripsi ?? ''))))"
                    :class="terpilih === {{ $book->id }}
                        ? 'border-blue-500 bg-blue-50'
                        : 'border-transparent hover:border-slate-200 hover:bg-slate-50'"
                    class="flex w-full items-start gap-3 rounded-xl border p-3 text-left transition">

                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg {{ $gaya['tile'] }}">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                            {!! $gaya['icon'] !!}
                        </svg>
                    </span>

                    {{-- Badge ditaruh di baris meta, bukan sebaris judul, supaya judul
                         dapat lebar penuh dan tidak terpotong terlalu cepat. --}}
                    <span class="min-w-0 flex-1">
                        {{-- Jangan tambahkan `block`: line-clamp butuh display:-webkit-box --}}
                        <span class="line-clamp-2 text-sm font-semibold leading-snug text-slate-800">{{ $book->judul }}</span>

                        {{-- Deskripsi ikut dicocokkan oleh kotak pencarian, jadi harus
                             terlihat di sini supaya jelas kenapa sebuah item cocok.
                             Dipotong 1 baris agar tinggi item tetap seragam. --}}
                        @if ($book->deskripsi)
                            <span class="mt-1 line-clamp-1 text-xs leading-relaxed text-slate-500">{{ $book->deskripsi }}</span>
                        @endif

                        <span class="mt-1.5 flex items-center gap-1.5">
                            <span class="rounded {{ $gaya['badge'] }} px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wide">
                                {{ $book->fileExtension() }}
                            </span>
                            <span class="truncate text-xs text-slate-400">
                                {{ $book->fileSizeLabel() }}
                                @if ($book->updated_at)
                                    · {{ $book->updated_at->timezone('Asia/Jakarta')->translatedFormat('d M Y') }}
                                @endif
                            </span>
                        </span>
                    </span>
                </button>
            @endforeach

            @if ($tampilkanPencarian)
                <div x-show="jumlahCocok === 0" x-cloak class="px-3 py-8 text-center">
                    <p class="text-sm text-slate-500">Tidak ada dokumen yang cocok.</p>
                    <button type="button" @click="q = ''" class="mt-1 text-sm font-semibold text-indigo-600 hover:underline">Hapus pencarian</button>
                </div>
            @endif
        </div>
    </aside>

    {{-- ============ Panel kanan: detail dokumen ============ --}}
    <section x-show="!isMobile || detailTerbuka" x-cloak
        :class="isMobile && detailTerbuka ? 'fixed inset-0 z-40 overflow-y-auto bg-white p-4' : 'mt-4 lg:mt-0 lg:overflow-hidden'">

        <template x-if="aktif">
            <div class="flex h-full flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white">

                {{-- Tombol kembali, hanya di mobile --}}
                <button type="button" x-show="isMobile" @click="detailTerbuka = false"
                    class="flex items-center gap-2 border-b border-slate-200 px-5 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali ke daftar
                </button>

                <div class="flex-1 overflow-y-auto">
                    {{-- Kepala dokumen --}}
                    <div class="border-b border-slate-200 px-6 py-4">
                        <div class="flex items-start gap-4">
                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl" :class="aktif.tile">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                                    x-html="aktif.icon"></svg>
                            </span>

                            {{-- Judul di kiri, tombol aksi di kanan sejajar dengannya.
                                 Di layar sempit tombol turun ke bawah (sm:) supaya
                                 judul tidak terdesak. --}}
                            <div class="min-w-0 flex-1 sm:flex sm:items-start sm:justify-between sm:gap-4">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="text-lg font-bold leading-snug text-slate-900" x-text="aktif.judul"></h3>
                                        <span class="rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide"
                                            :class="aktif.badge" x-text="aktif.format"></span>
                                    </div>
                                    {{-- Tetap text-sm (14px) supaya beda dari baris meta yang
                                         12px; tinggi baris dinormalkan agar tidak boros. --}}
                                    <p x-show="aktif.deskripsi" class="mt-1 text-sm text-slate-500" x-text="aktif.deskripsi"></p>

                                    {{-- Format tidak diulang di sini karena sudah jadi badge di
                                         samping judul; cukup ukuran & tanggal pembaruan. --}}
                                    <p class="mt-1.5 text-xs text-slate-400">
                                        <span x-text="aktif.ukuran"></span>
                                        <span class="text-slate-300">·</span>
                                        Diperbarui <span x-text="aktif.diperbarui"></span>
                                    </p>
                                </div>

                                {{-- Aksi: di kanan judul saat sm+, turun ke bawah di layar sempit.
                                     shrink-0 tanpa prefix sm: aman karena di bawah sm wrapper-nya
                                     bukan flex, jadi flex-shrink tidak berefek. --}}
                                <div class="mt-4 flex flex-wrap items-center gap-2 shrink-0 sm:mt-0">
                                    <a :href="aktif.downloadUrl"
                                        class="inline-flex h-10 items-center gap-2 rounded-xl bg-indigo-600 px-4 text-sm font-semibold text-white transition-colors hover:bg-indigo-700">
                                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v10m0 0l4-4m-4 4l-4-4" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 17v3h16v-3" />
                                        </svg>
                                        Unduh
                                    </a>

                                    <a :href="aktif.previewUrl" target="_blank" rel="noopener" x-show="aktif.isPdf"
                                        class="inline-flex h-10 items-center gap-2 rounded-xl border border-slate-200 px-4 text-sm font-semibold text-slate-700 transition-colors hover:bg-slate-50">
                                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                        Buka di tab baru
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Pratinjau.
                         Saat layar penuh, wadah ini diberi class `fixed` + role="dialog"
                         agar kena aturan layout [role="dialog"].fixed -> z-index 500,
                         sehingga tidak tertutup panel chatbot yang ada di z-[260]. --}}
                    <div class="px-6 py-5" x-ref="wadahPratinjau"
                        :role="pratinjauPenuh ? 'dialog' : null"
                        :aria-modal="pratinjauPenuh ? 'true' : null"
                        :class="pratinjauPenuh ? 'fixed inset-0 z-50 flex flex-col bg-white' : ''">

                        <div class="mb-2 flex items-center justify-between gap-2">
                            {{-- Saat layar penuh, label diganti judul dokumen supaya
                                 pembaca tetap tahu sedang membuka dokumen yang mana. --}}
                            <p class="min-w-0"
                                :class="pratinjauPenuh
                                    ? 'truncate text-sm font-semibold text-slate-700'
                                    : 'text-xs font-semibold uppercase tracking-wide text-slate-400'"
                                x-text="pratinjauPenuh ? aktif.judul : 'Pratinjau'"></p>

                            <button type="button" x-show="aktif.isPdf && muatPratinjau" @click="togglePenuh()"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-50">
                                <template x-if="!pratinjauPenuh">
                                    <svg viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 9V5a1 1 0 011-1h4M20 9V5a1 1 0 00-1-1h-4M4 15v4a1 1 0 001 1h4M20 15v4a1 1 0 01-1 1h-4" />
                                    </svg>
                                </template>
                                <template x-if="pratinjauPenuh">
                                    <svg viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 4v4a1 1 0 01-1 1H4m11-5v4a1 1 0 001 1h4M9 20v-4a1 1 0 00-1-1H4m11 5v-4a1 1 0 011-1h4" />
                                    </svg>
                                </template>
                                <span x-text="pratinjauPenuh ? 'Keluar layar penuh' : 'Layar penuh'"></span>
                            </button>
                        </div>

                        {{-- Hanya satu iframe yang pernah ada; src-nya ikut dokumen terpilih,
                             jadi tidak ada dokumen lain yang ikut termuat. --}}
                        <template x-if="aktif.isPdf && muatPratinjau">
                            <iframe :src="aktif.previewUrl" title="Pratinjau dokumen"
                                :class="pratinjauPenuh ? 'min-h-0 flex-1' : 'h-[65vh]'"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50"></iframe>
                        </template>

                        {{-- File besar: dimuat hanya saat diminta --}}
                        <template x-if="aktif.isPdf && !muatPratinjau">
                            <div class="flex h-[40vh] flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-slate-50 px-6 text-center">
                                <p class="text-sm font-medium text-slate-600">
                                    Dokumen ini berukuran <span x-text="aktif.ukuran"></span>.
                                </p>
                                <p class="mt-1 text-sm text-slate-400">Pratinjau tidak dimuat otomatis agar halaman tetap ringan.</p>
                                <button type="button" @click="muatPratinjau = true"
                                    class="mt-4 inline-flex h-10 items-center gap-2 rounded-xl bg-indigo-600 px-4 text-sm font-semibold text-white transition-colors hover:bg-indigo-700">
                                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    Muat Pratinjau
                                </button>
                            </div>
                        </template>

                        <template x-if="!aktif.isPdf">
                            <div class="flex h-[40vh] flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-slate-50 px-6 text-center">
                                <span class="flex h-12 w-12 items-center justify-center rounded-xl" :class="aktif.tile">
                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                                        x-html="aktif.icon"></svg>
                                </span>
                                <p class="mt-3 text-sm font-medium text-slate-600">
                                    Format <span x-text="aktif.format"></span> tidak bisa dipratinjau di browser.
                                </p>
                                <p class="mt-1 text-sm text-slate-400">Unduh dokumennya untuk membuka di aplikasi Office.</p>
                                <a :href="aktif.downloadUrl"
                                    class="mt-4 inline-flex h-10 items-center gap-2 rounded-xl bg-indigo-600 px-4 text-sm font-semibold text-white transition-colors hover:bg-indigo-700">
                                    Unduh Dokumen
                                </a>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </template>
    </section>
</div>
@endif
@endsection
