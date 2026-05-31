<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenggat;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TenggatController extends Controller
{
    /**
     * Tampilkan daftar tenggat beserta semua kategori global
     * yang belum memiliki tenggat (tanpa filter per tahun).
     */
    public function index()
    {
        $tenggats = Tenggat::with('kategori')
            ->latest()
            ->get();

        // Kategori global yang belum punya tenggat
        $kategoris = Kategori::doesntHave('tenggat')
            ->orderBy('name')
            ->get();

        return view('superadmin.kelola_tenggat', compact('tenggats', 'kategoris'));
    }

    public function create()
    {
        return view('superadmin.kelola_tenggat');
    }

    /**
     * Simpan tenggat baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'kategori_id'      => 'required|exists:kategoris,id|unique:tenggats,kategori_id',
            'tanggal_aktif'    => 'required|date',
            'jam_aktif'        => 'required',
            'tanggal_nonaktif' => 'required|date|after_or_equal:tanggal_aktif',
            'jam_nonaktif'     => 'required',
        ]);

        $waktuAktif = Carbon::parse(
            $request->tanggal_aktif . ' ' . $request->jam_aktif
        );

        $waktuNonaktif = Carbon::parse(
            $request->tanggal_nonaktif . ' ' . $request->jam_nonaktif
        );

        Tenggat::create([
            'kategori_id'    => $request->kategori_id,
            'waktu_aktif'    => $waktuAktif,
            'waktu_nonaktif' => $waktuNonaktif,
        ]);

        return redirect()->back()
            ->with('success', 'Tenggat berhasil ditambahkan');
    }

    public function show(Tenggat $tenggat)
    {
        //
    }

    public function edit($id)
    {
        $tenggat = Tenggat::findOrFail($id);
        return view('superadmin.kelola_tenggat', compact('tenggat'));
    }

    /**
     * Update tenggat.
     */
    public function update(Request $request, Tenggat $tenggat)
    {
        $request->validate([
            'kategori_id'      => 'required|exists:kategoris,id',
            'tanggal_aktif'    => 'required|date',
            'jam_aktif'        => 'required',
            'tanggal_nonaktif' => 'required|date|after_or_equal:tanggal_aktif',
            'jam_nonaktif'     => 'required',
        ]);

        $waktuAktif = Carbon::parse(
            $request->tanggal_aktif . ' ' . $request->jam_aktif
        );

        $waktuNonaktif = Carbon::parse(
            $request->tanggal_nonaktif . ' ' . $request->jam_nonaktif
        );

        $tenggat->update([
            'kategori_id'    => $request->kategori_id,
            'waktu_aktif'    => $waktuAktif,
            'waktu_nonaktif' => $waktuNonaktif,
        ]);

        return redirect()->back()
            ->with('success', 'Tenggat berhasil diperbarui');
    }

    public function destroy(Tenggat $tenggat)
    {
        $tenggat->delete();

        return redirect()->back()
            ->with('success', 'Tenggat berhasil dihapus');
    }
}
