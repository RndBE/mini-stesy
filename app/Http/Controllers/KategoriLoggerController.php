<?php

namespace App\Http\Controllers;

use App\Models\Kategori_logger;
use Illuminate\Http\Request;

class KategoriLoggerController extends Controller
{
    public function index()
    {
        $kategoris = Kategori_logger::query()
            ->withCount('logger')
            ->orderBy('nama_kategori')
            ->get();

        return view('kategori.index', [
            'title' => 'Kategori Logger',
            'kategoris' => $kategoris,
        ]);
    }

    public function create()
    {
        return view('kategori.create', [
            'title' => 'Tambah Kategori Logger',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kategori' => 'required|string|max:191|unique:kategori_logger,nama_kategori',
            'kepanjangan' => 'required|string',
            'icon_app' => 'required|string|max:25',
            'view' => 'required|integer|min:0',
        ]);

        $validated['nama_kategori'] = strtoupper(trim((string) $validated['nama_kategori']));
        $validated['kepanjangan'] = trim((string) $validated['kepanjangan']);
        $validated['icon_app'] = trim((string) $validated['icon_app']);

        Kategori_logger::create($validated);

        return redirect()->route('kategori.index')
            ->with('success', 'Kategori logger berhasil ditambahkan.');
    }

    public function edit(Kategori_logger $kategori)
    {
        return view('kategori.edit', [
            'title' => 'Edit Kategori Logger',
            'kategori' => $kategori,
        ]);
    }

    public function update(Request $request, Kategori_logger $kategori)
    {
        $validated = $request->validate([
            'nama_kategori' => 'required|string|max:191|unique:kategori_logger,nama_kategori,' . $kategori->id_katlogger . ',id_katlogger',
            'kepanjangan' => 'required|string',
            'icon_app_file' => 'required|image|mimes:png,jpg,jpeg,webp,svg|max:2048',
            'view' => 'required|integer|min:0',
        ]);

        $validated['nama_kategori'] = strtoupper(trim((string) $validated['nama_kategori']));
        $validated['kepanjangan'] = trim((string) $validated['kepanjangan']);

        if ($request->hasFile('icon_app_file')) {
            $file = $request->file('icon_app_file');
            $ext = strtolower((string) $file->getClientOriginalExtension());
            $filename = 'k' . $kategori->id_katlogger . '_' . time() . '.' . $ext;
            $targetDir = public_path('kategori');

            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            $file->move($targetDir, $filename);
            $validated['icon_app'] = $filename;
        }

        unset($validated['icon_app_file']);

        $kategori->update($validated);

        return redirect()->route('kategori.index')
            ->with('success', 'Kategori logger berhasil diperbarui.');
    }

    public function destroy(Kategori_logger $kategori)
    {
        $kategori->delete();

        return redirect()->route('kategori.index')
            ->with('success', 'Kategori logger berhasil dihapus.');
    }
}
