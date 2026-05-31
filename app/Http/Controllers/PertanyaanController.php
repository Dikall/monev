<?php

namespace App\Http\Controllers;

use App\Models\Pertanyaan;
use App\Models\Tahun;
use App\Models\Kategori;
use App\Models\Indikator;
use Illuminate\Http\Request;

class PertanyaanController extends Controller
{
    public function index(Request $request)
    {
        $tahuns     = Tahun::all();
        $kategoris  = Kategori::all();
        $indikators = Indikator::all();

        $parentJudul = Pertanyaan::where('level', 'judul')
            ->select('id', 'pertanyaan_kuisioner', 'indikator_id', 'nomor')
            ->get();

        $parentSubJudul = Pertanyaan::where('level', 'subjudul')
            ->select('id', 'pertanyaan_kuisioner', 'indikator_id', 'nomor', 'parent_id')
            ->get();

        $bobotPerIndikator = Pertanyaan::where('level', 'pertanyaan')
            ->selectRaw('indikator_id, SUM(bobot) as total')
            ->groupBy('indikator_id')
            ->pluck('total', 'indikator_id');

        $query = Pertanyaan::with(['tahun', 'kategori', 'indikator', 'parent']);

        if ($request->tahun_id)     $query->where('tahun_id',     $request->tahun_id);
        if ($request->kategori_id)  $query->where('kategori_id',  $request->kategori_id);
        if ($request->indikator_id) $query->where('indikator_id', $request->indikator_id);

        $raw = $query->get();

        // Kelompokkan
        $judul = $raw->where('level', 'judul')->sortBy('nomor');
        $sub   = $raw->where('level', 'subjudul');
        $tanya = $raw->where('level', 'pertanyaan');

        $result = collect();

        foreach ($judul as $j) {
            $result->push($j);

            // SUB JUDUL dari JUDUL
            $subList = $sub->where('parent_id', $j->id)->sortBy('nomor');

            foreach ($subList as $s) {
                $result->push($s);

                // PERTANYAAN dari SUB JUDUL
                $tanyaList = $tanya->where('parent_id', $s->id)->sortBy('nomor');

                foreach ($tanyaList as $t) {
                    $result->push($t);
                }
            }

            // PERTANYAAN LANGSUNG (tanpa subjudul)
            $tanyaLangsung = $tanya->where('parent_id', $j->id)->sortBy('nomor');

            foreach ($tanyaLangsung as $t) {
                $result->push($t);
            }
        }

        $pertanyaans = $result;

        return view('superadmin.kelola_pertanyaan', compact(
            'pertanyaans', 'tahuns', 'kategoris', 'indikators',
            'parentJudul', 'parentSubJudul', 'bobotPerIndikator'
        ));
    }

    public function create() {}

    public function store(Request $request)
    {
        $request->validate([
            'tahun_id'             => 'required',
            'kategori_id'          => 'required',
            'indikator_id'         => 'required',
            'level'                => 'required|in:judul,subjudul,pertanyaan',
            'nomor'                => 'required',
            'pertanyaan_kuisioner' => 'required',
            'parent_id'            => 'nullable|exists:pertanyaans,id',
            'bobot'                => 'nullable|numeric|min:0',
        ]);

        $level    = $request->level;
        // Gunakan filled() agar string kosong "" dianggap null
        $parentId = $request->filled('parent_id') ? (int) $request->parent_id : null;

        // Validasi struktur hierarki
        if ($level === 'subjudul' && !$parentId) {
            return back()->with('error', 'Subjudul wajib pilih parent Judul.')->withInput();
        }

        if ($level === 'pertanyaan' && $parentId) {
            $parent = Pertanyaan::find($parentId);
            if ($parent && $parent->level !== 'subjudul') {
                return back()->with('error', 'Parent pertanyaan harus Sub Judul.')->withInput();
            }
        }

        Pertanyaan::create([
            'tahun_id'             => $request->tahun_id,
            'kategori_id'          => $request->kategori_id,
            'indikator_id'         => $request->indikator_id,
            'is_parent'            => in_array($level, ['judul', 'subjudul']),
            'level'                => $level,
            'parent_id'            => $level === 'judul' ? null : $parentId,
            'nomor'                => $request->nomor,
            'pertanyaan_kuisioner' => $request->pertanyaan_kuisioner,
            'bobot'                => $level === 'pertanyaan' ? (int) $request->bobot : 0,
        ]);

        return back()->with('success', 'Berhasil tambah pertanyaan.');
    }

    public function show(Pertanyaan $pertanyaan) {}

    public function edit(Pertanyaan $pertanyaan) {}

    public function update(Request $request, $id)
    {
        $request->validate([
            'tahun_id'             => 'required',
            'kategori_id'          => 'required',
            'indikator_id'         => 'required',
            'level'                => 'required|in:judul,subjudul,pertanyaan',
            'nomor'                => 'required',
            'pertanyaan_kuisioner' => 'required',
            'bobot'                => 'required_if:level,pertanyaan|nullable|numeric|min:0',
            'parent_id'            => 'nullable|exists:pertanyaans,id',
        ]);

        $data     = Pertanyaan::findOrFail($id);
        $level    = $request->level;
        // Gunakan filled() agar string kosong "" dianggap null
        $parentId = $request->filled('parent_id') ? (int) $request->parent_id : null;

        if ($level === 'subjudul' && !$parentId) {
            return back()->with('error', 'Subjudul wajib punya parent Judul.')->withInput();
        }

        if ($level === 'pertanyaan' && $parentId) {
            $parent = Pertanyaan::find($parentId);
            if ($parent && $parent->level !== 'subjudul') {
                return back()->with('error', 'Parent pertanyaan harus Sub Judul.')->withInput();
            }
        }

        $data->update([
            'tahun_id'             => $request->tahun_id,
            'kategori_id'          => $request->kategori_id,
            'indikator_id'         => $request->indikator_id,
            'is_parent'            => in_array($level, ['judul', 'subjudul']),
            'level'                => $level,
            'parent_id'            => $level === 'judul' ? null : $parentId,
            'nomor'                => $request->nomor,
            'pertanyaan_kuisioner' => $request->pertanyaan_kuisioner,
            'bobot'                => $level === 'pertanyaan' ? (int) $request->bobot : 0,
        ]);

        return back()->with('success', 'Berhasil update pertanyaan.');
    }

    public function destroy($id)
    {
        Pertanyaan::findOrFail($id)->delete();
        return back()->with('success', 'Berhasil hapus pertanyaan.');
    }
}