<?php

namespace App\Http\Controllers;

use App\Models\PublicBody;
use App\Models\Kategori;
use Illuminate\Http\Request;

class PublicBodyController extends Controller
{
    /**
     * Tampilkan semua badan publik dengan pilihan kategori global.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        $publicBodies = PublicBody::with('kategori')->paginate($perPage)->withQueryString();

        // Semua kategori global (tidak difilter per tahun)
        $kategoris = Kategori::orderBy('name')->get();

        return view('bpublik.index', compact('publicBodies', 'kategoris'));
    }

    public function create()
    {
        //
    }

    /**
     * Simpan badan publik baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_badan'  => 'required|string|max:255',
            'kategori_id' => 'required|exists:kategoris,id',
        ]);

        PublicBody::create([
            'nama_badan'    => $request->nama_badan,
            'kategori_id'   => $request->kategori_id,
            'is_registered' => false,
        ]);

        return redirect()
            ->route('superadmin.bpublik.index')
            ->with('success', 'Badan publik berhasil ditambahkan');
    }

    public function show(PublicBody $bpublik)
    {
        //
    }

    public function edit(PublicBody $bpublik)
    {
        //
    }

    /**
     * Update badan publik.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_badan'  => 'required|string|max:255',
            'kategori_id' => 'required|exists:kategoris,id',
        ]);

        $publicBody = PublicBody::findOrFail($id);

        $publicBody->update([
            'nama_badan'  => $request->nama_badan,
            'kategori_id' => $request->kategori_id,
        ]);

        return redirect()
            ->route('superadmin.bpublik.index')
            ->with('success', 'Badan publik berhasil diperbarui');
    }

    /**
     * Hapus badan publik dari master data.
     * Akun user yang terhubung TIDAK ikut dihapus (biarkan null).
     */
    public function destroy($id)
    {
        $publicBody = PublicBody::findOrFail($id);
        $publicBody->delete();

        return redirect()
            ->route('superadmin.bpublik.index')
            ->with('success', 'Badan publik berhasil dihapus dari master data');
    }
}
