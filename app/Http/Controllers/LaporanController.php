<?php

namespace App\Http\Controllers;

use App\Models\Laporan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class LaporanController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function publicIndex()
    {
        $files = Laporan::latest()->get();
        return view('laporan-monev', compact('files'));
    }

    public function download($id)
    {
        $file = Laporan::findOrFail($id);
        $path = public_path('files/laporan/' . $file->file_data); // Tambah '/' sebelum $file->file_data

        if (file_exists($path)) {
            return response()->download($path, $file->file_name . '.' . pathinfo($path, PATHINFO_EXTENSION));
        }

        return back()->with('error', 'File tidak ditemukan.');
    }

    // View untuk admin (CRUD)
    public function index()
    {
        $laporans = Laporan::latest()->paginate(10);
        return view('superadmin.publikasi.lapmonev', compact('laporans'));
    }

    // Simpan file ke public/files
    public function store(Request $request)
    {
        $request->validate([
            'dokumen' => 'required|file|mimes:pdf,xls,xlsx|max:5120'
        ]);

        $file = $request->file('dokumen');
        $filename = time() . '_' . $file->getClientOriginalName();
        
        // Pastikan folder ada
        if (!file_exists(public_path('files/laporan'))) {
            mkdir(public_path('files/laporan'), 0775, true);
        }

        $file->move(public_path('files/laporan'), $filename);

        Laporan::create([
            'file_name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'file_data' => $filename
        ]);

        return redirect()->back()->with('success', 'Dokumen berhasil diunggah.');
    }

    public function destroy($id)
    {
        $dokumen = Laporan::findOrFail($id);
        $path = public_path('files/laporan/' . $dokumen->file_data); // Tambah '/' sebelum $dokumen->file_data

        if (file_exists($path)) {
            unlink($path);
        }

        $dokumen->delete();
        return redirect()->back()->with('success', 'Dokumen berhasil dihapus.');
    }
}
