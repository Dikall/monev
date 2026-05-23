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

        return view('indikator.index', compact('indikators', 'tahuns', 'kategoris', 'tahunId'));
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
        Indikator::create($request->all());
        return back();
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
        Indikator::findOrFail($id)->update($request->all());
        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        Indikator::findOrFail($id)->delete();
        return back();
    }
}
