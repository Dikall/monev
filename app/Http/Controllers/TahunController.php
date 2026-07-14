<?php

namespace App\Http\Controllers;

use App\Models\Tahun;
use Illuminate\Http\Request;

class TahunController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tahuns = Tahun::orderBy('tahun', 'desc')->get();
        return view('superadmin.kelola_tahun', compact('tahuns'));
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
            'tahun' => 'required'
        ]);

        Tahun::create([
            'tahun' => $request->tahun
        ]);

        return redirect()->route('superadmin.tahun.index')->with('success','Tahun berhasil ditambahkan');
    }


    /**
     * Display the specified resource.
     */
    public function show(Tahun $tahun)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tahun $tahun)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'tahun' => 'required'
        ]);

        $tahun = Tahun::findOrFail($id);
        $tahun->update([
            'tahun' => $request->tahun
        ]);

        return redirect()->route('superadmin.tahun.index')->with('success','Tahun berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        Tahun::findOrFail($id)->delete();

        return redirect()->route('superadmin.tahun.index')->with('success','Tahun berhasil dihapus');
    }
}
