<?php

namespace App\Http\Controllers;

use App\Models\Kuemonev;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class KuemonevController extends Controller
{
    // View publik tanpa login
    public function publicIndex()
    {
        $files = Kuemonev::latest()->get();
        return view('kuesioner-monev', compact('files'));
    }

    // Fungsi download untuk publik
    public function download($id)
    {
        $file = Kuemonev::findOrFail($id);
        $path = public_path('files/kuesioner' . $file->file_data);

        if (file_exists($path)) {
            return response()->download($path, $file->file_name.'.'.pathinfo($path, PATHINFO_EXTENSION));
        }

        return back()->with('error', 'File tidak ditemukan.');
    }

    // View untuk admin (CRUD)
    public function index()
    {
        $kuemonevs = Kuemonev::latest()->paginate(10);
        return view('superadmin.publikasi.kuemonev', compact('kuemonevs'));
    }

    // Simpan file ke public/files
    public function store(Request $request)
    {
        $request->validate([
            'dokumen' => 'required|file|mimes:pdf,xls,xlsx|max:5120'
        ]);

        $file = $request->file('dokumen');
        $filename = time().'_'.$file->getClientOriginalName();
        
        // Simpan langsung ke public/files
        $file->move(public_path('files/kuesioner'), $filename);

        Kuemonev::create([
            'file_name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'file_data' => $filename
        ]);

        return redirect()->route('kuemonev.index')->with('success', 'Dokumen berhasil diunggah.');
    }

    // Hapus file
    public function destroy($id)
    {
        $dokumen = Kuemonev::findOrFail($id);
        $path = public_path('files/kuesioner' . $dokumen->file_data);

        if (file_exists($path)) {
            unlink($path);
        }

        $dokumen->delete();
        return redirect()->route('kuemonev.index')->with('success', 'Dokumen berhasil dihapus.');
    }
}
