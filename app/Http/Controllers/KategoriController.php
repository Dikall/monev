<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\Tahun;
use Illuminate\Http\Request;

class KategoriController extends Controller
{
    /**
     * Tampilkan semua kategori global (tidak difilter per tahun).
     */
    public function index()
    {
        $kategoris = Kategori::latest()->get();
        $tahuns    = Tahun::orderBy('tahun', 'desc')->get();

        return view('kategori.index', compact('kategoris', 'tahuns'));
    }

    public function create()
    {
        return view('kategori.create');
    }

    /**
     * Simpan kategori baru (tanpa tahun_id).
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:kategoris,name',
        ], [
            'name.unique' => 'Nama kategori sudah ada.',
        ]);

        Kategori::create([
            'name' => $request->name,
        ]);

        return redirect()
            ->back()
            ->with('success', 'Kategori berhasil ditambahkan');
    }

    public function show(Kategori $kategori)
    {
        return view('kategori.show', compact('kategori'));
    }

    public function edit(Kategori $kategori)
    {
        return view('kategori.edit', compact('kategori'));
    }

    /**
     * Update kategori (tanpa tahun_id).
     */
    public function update(Request $request, Kategori $kategori)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:kategoris,name,' . $kategori->id,
        ], [
            'name.unique' => 'Nama kategori sudah ada.',
        ]);

        $kategori->update([
            'name' => $request->name,
        ]);

        return redirect()
            ->back()
            ->with('success', 'Kategori berhasil diperbarui');
    }

    public function destroy(Kategori $kategori)
    {
        $kategori->delete();

        return redirect()
            ->back()
            ->with('success', 'Kategori berhasil dihapus');
    }
}
