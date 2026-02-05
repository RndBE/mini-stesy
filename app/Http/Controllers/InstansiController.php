<?php

namespace App\Http\Controllers;

use App\Models\Instansi;
use Illuminate\Http\Request;

class InstansiController extends Controller
{
    public function index()
    {
        $instansi = Instansi::query()
            ->withCount('users')
            ->orderBy('nama')
            ->get();

        return view('instansi.index', [
            'title' => 'Instansi',
            'instansi' => $instansi,
        ]);
    }

    public function create()
    {
        return view('instansi.create', [
            'title' => 'Tambah Instansi',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100|unique:instansi,nama',
            'alamat' => 'nullable|string',
            'telp' => 'nullable|string|max:25',
        ]);

        Instansi::create($validated);

        return redirect()->route('instansi.index')
            ->with('success', 'Instansi berhasil ditambahkan.');
    }

    public function edit(Instansi $instansi)
    {
        return view('instansi.edit', [
            'title' => 'Edit Instansi',
            'instansi' => $instansi,
        ]);
    }

    public function update(Request $request, Instansi $instansi)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100|unique:instansi,nama,' . $instansi->id,
            'alamat' => 'nullable|string',
            'telp' => 'nullable|string|max:25',
        ]);

        $instansi->update($validated);

        return redirect()->route('instansi.index')
            ->with('success', 'Instansi berhasil diperbarui.');
    }

    public function destroy(Instansi $instansi)
    {
        $instansi->delete();

        return redirect()->route('instansi.index')
            ->with('success', 'Instansi berhasil dihapus.');
    }
}
