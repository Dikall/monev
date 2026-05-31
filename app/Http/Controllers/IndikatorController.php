<?php

namespace App\Http\Controllers;

use App\Models\Tahun;
use App\Models\Kategori;
use App\Models\Indikator;
use Illuminate\Http\Request;

class IndikatorController extends Controller
{
    public function index(Request $request)
    {
        $tahuns = Tahun::orderBy('tahun', 'desc')->get();
        $kategoris = Kategori::orderBy('name')->get();

        $defaultTahun = $tahuns->firstWhere('tahun', now()->year) ?? $tahuns->first();
        $tahunId = $request->tahun_id ?? ($defaultTahun?->id ?? null);

        $indikators = Indikator::with(['tahun', 'kategori'])
            ->where('tahun_id', $tahunId)
            ->get()
            ->sortBy(function ($item) {
                $kategoriName = $item->kategori->name ?? '';
                $noPad = str_pad($item->no, 5, '0', STR_PAD_LEFT);
                return strtolower($kategoriName) . '-' . $noPad;
            })
            ->values();

        $bobotSums = Indikator::selectRaw('tahun_id, kategori_id, SUM(bobot) as total')
            ->groupBy('tahun_id', 'kategori_id')
            ->get()
            ->mapWithKeys(function ($item) {
                return ["{$item->tahun_id}-{$item->kategori_id}" => floatval($item->total)];
            });

        return view('superadmin.kelola_indikator', compact('indikators', 'tahuns', 'kategoris', 'tahunId', 'bobotSums'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'tahun_id'       => 'required|exists:tahuns,id',
            'kategori_id'    => 'required|exists:kategoris,id',
            'no'             => 'required',
            'nama_indikator' => 'required|string|max:255',
            'bobot'          => 'required|numeric|min:0|max:100',
            'keterangan'     => 'nullable|string',
        ]);

        $tahunId = $request->tahun_id;
        $kategoriId = $request->kategori_id;
        $newBobot = $request->bobot;

        $currentTotal = Indikator::where('tahun_id', $tahunId)
            ->where('kategori_id', $kategoriId)
            ->sum('bobot');

        if (($currentTotal + $newBobot) > 100) {
            $kategoriName = Kategori::find($kategoriId)?->name ?? 'kategori ini';
            return back()->withInput()->with('error', "Gagal menambahkan indikator. Total bobot indikator untuk {$kategoriName} pada tahun terpilih melebihi 100% (saat ini: {$currentTotal}%, ingin ditambah: {$newBobot}%).");
        }

        Indikator::create($request->all());
        return back()->with('success', 'Indikator berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Indikator $indikator)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Indikator $indikator)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'tahun_id'       => 'required|exists:tahuns,id',
            'kategori_id'    => 'required|exists:kategoris,id',
            'no'             => 'required',
            'nama_indikator' => 'required|string|max:255',
            'bobot'          => 'required|numeric|min:0|max:100',
            'keterangan'     => 'nullable|string',
        ]);

        $indikator = Indikator::findOrFail($id);
        $tahunId = $request->tahun_id;
        $kategoriId = $request->kategori_id;
        $newBobot = $request->bobot;

        $currentTotal = Indikator::where('tahun_id', $tahunId)
            ->where('kategori_id', $kategoriId)
            ->where('id', '!=', $id)
            ->sum('bobot');

        if (($currentTotal + $newBobot) > 100) {
            $kategoriName = Kategori::find($kategoriId)?->name ?? 'kategori ini';
            return back()->withInput()->with('error', "Gagal memperbarui indikator. Total bobot indikator untuk {$kategoriName} pada tahun terpilih melebihi 100% (saat ini di luar indikator ini: {$currentTotal}%, ingin diubah menjadi: {$newBobot}%).");
        }

        $indikator->update($request->all());
        return back()->with('success', 'Indikator berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        Indikator::findOrFail($id)->delete();
        return back()->with('success', 'Indikator berhasil dihapus.');
    }
}
