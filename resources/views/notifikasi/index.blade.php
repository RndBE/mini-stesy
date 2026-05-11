@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold tracking-tight text-slate-800">Kirim Notifikasi</h2>
    </div>

    @if (session('success'))
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
        <p class="text-sm font-medium text-emerald-800">{{ session('success') }}</p>
    </div>
    @endif

    @if ($errors->any())
    <div class="rounded-xl border border-red-200 bg-red-50 p-4">
        <ul class="list-disc pl-5 text-sm text-red-700 space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Form Kirim Notifikasi --}}
    <div class="rounded-2xl border border-slate-200 bg-white">
        <div class="border-b border-slate-200 px-6 py-4">
            <h3 class="text-lg font-semibold text-slate-800">Buat Notifikasi Baru</h3>
            <p class="mt-1 text-sm text-slate-500">Kirim notifikasi push ke user yang dipilih atau semua user aktif.</p>
        </div>

        <div class="p-6">
            <form action="{{ route('notifikasi.send') }}" method="POST" class="space-y-5">
                @csrf

                {{-- Penerima --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Penerima</label>
                    <div class="flex items-center gap-4 mb-3">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="recipient_type" value="all" id="recipient_all"
                                class="text-blue-600 focus:ring-blue-500"
                                onchange="toggleUserSelect(this)"
                                {{ old('recipient_type', 'all') === 'all' ? 'checked' : '' }}>
                            <span class="text-sm font-medium text-slate-700">Semua User Aktif</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="recipient_type" value="selected" id="recipient_selected"
                                class="text-blue-600 focus:ring-blue-500"
                                onchange="toggleUserSelect(this)"
                                {{ old('recipient_type') === 'selected' ? 'checked' : '' }}>
                            <span class="text-sm font-medium text-slate-700">Pilih User Tertentu</span>
                        </label>
                    </div>

                    {{-- Multi-select user --}}
                    <div id="user_select_wrap" class="{{ old('recipient_type') === 'selected' ? '' : 'hidden' }}">
                        <p class="text-xs text-slate-500 mb-2">Hanya menampilkan user yang sudah login di aplikasi mobile (punya FCM token aktif).</p>
                        <div class="border border-slate-200 rounded-xl max-h-56 overflow-y-auto p-3 space-y-2 bg-slate-50">
                            @forelse($usersWithToken as $u)
                            <label class="flex items-center gap-3 cursor-pointer hover:bg-white rounded-lg p-1.5 transition">
                                <input type="checkbox" name="recipient_ids[]" value="{{ $u->id_user }}"
                                    class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                                    {{ in_array($u->id_user, old('recipient_ids', [])) ? 'checked' : '' }}>
                                <span class="text-sm text-slate-800 font-medium">{{ $u->nama }}</span>
                                <span class="text-xs text-slate-500">{{ '@' . $u->username }}</span>
                                <span class="ml-auto text-xs bg-slate-200 text-slate-600 px-2 py-0.5 rounded-full">{{ $u->level_user }}</span>
                            </label>
                            @empty
                            <p class="text-sm text-slate-400 text-center py-4">Belum ada user yang login di aplikasi mobile.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Title --}}
                <div>
                    <label for="title" class="block text-sm font-medium text-slate-700">Judul Notifikasi</label>
                    <input type="text" name="title" id="title"
                        class="mt-1 block w-full rounded-xl border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                        value="{{ old('title') }}" placeholder="Contoh: Informasi Penting" required>
                </div>

                {{-- Body --}}
                <div>
                    <label for="body" class="block text-sm font-medium text-slate-700">Isi Pesan</label>
                    <textarea name="body" id="body" rows="4"
                        class="mt-1 block w-full rounded-xl border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                        placeholder="Tulis isi pesan notifikasi di sini..." required>{{ old('body') }}</textarea>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        Kirim Notifikasi
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Riwayat Notifikasi --}}
    <div class="rounded-2xl border border-slate-200 bg-white">
        <div class="border-b border-slate-200 px-6 py-4">
            <h3 class="text-lg font-semibold text-slate-800">Riwayat Notifikasi</h3>
            <p class="mt-1 text-sm text-slate-500">50 notifikasi terakhir yang dikirim (custom maupun peringatan otomatis).</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Tipe</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Judul</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Pesan</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Penerima</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Dikirim Oleh</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($histories as $h)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-4 py-3">
                            @if($h->type === 'warning')
                                <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-700">⚠ Peringatan</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-semibold text-blue-700">📣 Custom</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 font-medium text-slate-800 max-w-[180px] truncate">{{ $h->title }}</td>
                        <td class="px-4 py-3 text-slate-600 max-w-[220px] truncate">{{ $h->body }}</td>
                        <td class="px-4 py-3 text-slate-600">
                            @if($h->recipient_type === 'all')
                                Semua user
                            @elseif($h->recipient_type === 'automatic')
                                Otomatis
                            @else
                                {{ $h->recipient_count }} user
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-600">
                            {{ $h->sender?->nama ?? 'Sistem' }}
                        </td>
                        <td class="px-4 py-3 text-slate-500 whitespace-nowrap">
                            {{ $h->created_at->timezone('Asia/Jakarta')->format('d M Y H:i') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-slate-400">Belum ada riwayat notifikasi.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function toggleUserSelect(radio) {
    const wrap = document.getElementById('user_select_wrap');
    wrap.classList.toggle('hidden', radio.value !== 'selected');
}
// Init on load
document.addEventListener('DOMContentLoaded', function () {
    const selected = document.getElementById('recipient_selected');
    if (selected && selected.checked) {
        document.getElementById('user_select_wrap').classList.remove('hidden');
    }
});
</script>
@endsection
