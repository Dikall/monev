<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Jawaban;
use App\Models\Kategori;
use App\Models\Indikator;
use App\Models\Pertanyaan;
use App\Models\Tahun;
use App\Models\Tenggat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class KuesionerController extends Controller
{
    /**
     * Tampilkan halaman kuesioner untuk Badan Publik
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user->is_aktif) {
            return view('badanpublik.kuesioner.index', ['tidak_aktif' => true]);
        }

        $publicBody = $user->publicBody;
        if (!$publicBody) abort(403, 'Badan publik tidak ditemukan.');

        $tahunSekarang = now()->year;
        $tahun = Tahun::where('tahun', $tahunSekarang)->first();
        if (!$tahun) abort(403, 'Tahun aktif tidak ditemukan.');

        $kategoriAktif = $publicBody->kategori;
        $kategoriId    = $kategoriAktif?->id;
        if (!$kategoriId) abort(403, 'Badan publik belum memiliki kategori.');

        $indikators = Indikator::where('tahun_id', $tahun->id)
            ->where('kategori_id', $kategoriId)
            ->orderBy('no')
            ->get();

        $indikatorId    = $request->get('indikator_id', $indikators->first()?->id);
        $indikatorAktif = $indikators->firstWhere('id', $indikatorId);

        $tenggat  = Tenggat::where('kategori_id', $kategoriId)->first();
        $now      = now();
        $isOpen   = $tenggat && $now->gte($tenggat->waktu_aktif) && $now->lte($tenggat->waktu_nonaktif);
        $isClosed = $tenggat && $now->gt($tenggat->waktu_nonaktif);

        // ── Ambil hierarki pertanyaan menggunakan childrenRecursive ──
        $pertanyaans = Pertanyaan::where('indikator_id', $indikatorId)
            ->where('level', 'judul')
            ->with('childrenRecursive')
            ->orderBy('nomor')
            ->get();

        // ── Jawaban ──
        $jawabans = Jawaban::where('public_body_id', $publicBody->id)
            ->where('tahun_id', $tahun->id)
            ->whereHas('pertanyaan', fn($q) => $q->where('indikator_id', $indikatorId))
            ->get()
            ->keyBy('pertanyaan_id');

        return view('badanpublik.kuesioner.index', compact(
            'indikators', 'indikatorAktif', 'indikatorId',
            'kategoriAktif', 'kategoriId',
            'pertanyaans', 'jawabans',
            'isOpen', 'isClosed', 'tenggat',
            'publicBody', 'tahun',
        ));
    }

    /**
     * Simpan jawaban kuesioner (draft - bisa disimpan berkali-kali)
     */
    public function store(Request $request)
    {
        $user = Auth::user();
 
        if (!$user->is_aktif) {
            return back()->with('error', 'Akun Anda belum aktif.');
        }
 
        $publicBody = $user->publicBody;
        if (!$publicBody) {
            return back()->with('error', 'Badan publik tidak ditemukan.');
        }
 
        $tahunSekarang = now()->year;
        $tahun = Tahun::where('tahun', $tahunSekarang)->firstOrFail();
 
        $kategoriId = $request->input('kategori_id');
        $tenggat    = Tenggat::where('kategori_id', $kategoriId)->first();
        $now        = now();
 
        if (!$tenggat
            || !$now->gte($tenggat->waktu_aktif)
            || !$now->lte($tenggat->waktu_nonaktif)
        ) {
            return back()->with('error',
                'Periode pengisian kuesioner sudah berakhir atau belum dibuka.'
            );
        }
 
        $this->prosesJawaban($request, $user, $publicBody, $tahun);
 
        return back()->with('success', 'Jawaban berhasil disimpan.');
    }

    public function autoSave(Request $request)
    {
        // Hanya terima request AJAX
        if (!$request->ajax() && !$request->wantsJson()) {
            return response()->json(['status' => 'error', 'message' => 'Bukan request AJAX.'], 400);
        }
 
        $user = Auth::user();
 
        if (!$user || !$user->is_aktif) {
            return response()->json(['status' => 'error', 'message' => 'Akun tidak aktif.'], 403);
        }
 
        $publicBody = $user->publicBody;
        if (!$publicBody) {
            return response()->json(['status' => 'error', 'message' => 'Badan publik tidak ditemukan.'], 403);
        }
 
        $tahunSekarang = now()->year;
        $tahun = Tahun::where('tahun', $tahunSekarang)->first();
        if (!$tahun) {
            return response()->json(['status' => 'error', 'message' => 'Tahun tidak ditemukan.'], 404);
        }
 
        $kategoriId = $request->input('kategori_id');
        $tenggat    = Tenggat::where('kategori_id', $kategoriId)->first();
        $now        = now();
 
        if (!$tenggat
            || !$now->gte($tenggat->waktu_aktif)
            || !$now->lte($tenggat->waktu_nonaktif)
        ) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Periode pengisian sudah berakhir atau belum dibuka.',
            ], 403);
        }
 
        // Jangan auto-save jika sudah di-submit
        $sudahSubmit = Jawaban::where('public_body_id', $publicBody->id)
            ->where('tahun_id', $tahun->id)
            ->where('is_submitted', true)
            ->exists();
 
        if ($sudahSubmit) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Kuesioner sudah di-submit, jawaban terkunci.',
            ], 403);
        }
 
        $jawabans = $request->input('jawaban', []);
        $links    = $request->input('links', []);
 
        foreach ($jawabans as $pertanyaanId => $jawabanValue) {
            $data = [
                'user_id'        => $user->id,
                'public_body_id' => $publicBody->id,
                'pertanyaan_id'  => $pertanyaanId,
                'tahun_id'       => $tahun->id,
                'jawaban'        => $jawabanValue,
            ];
 
            if (!empty($links[$pertanyaanId])) {
                $rawLinks = is_array($links[$pertanyaanId]) ? $links[$pertanyaanId] : [$links[$pertanyaanId]];
                $data['links'] = array_values(array_filter($rawLinks, fn($l) => !empty($l) && trim($l) !== ''));
            } else {
                $data['links'] = null;
            }
 
            Jawaban::updateOrCreate(
                [
                    'public_body_id' => $publicBody->id,
                    'pertanyaan_id'  => $pertanyaanId,
                    'tahun_id'       => $tahun->id,
                ],
                $data
            );
        }
 
        return response()->json([
            'status'  => 'success',
            'message' => 'Auto-save berhasil.',
            'saved'   => count($jawabans),
        ]);
    }

    private function prosesJawaban(Request $request, $user, $publicBody, $tahun): void
    {
        $jawabans = $request->input('jawaban', []);
        $links    = $request->input('links', []);
        $dokumens = $request->file('dokumen', []);
 
        foreach ($jawabans as $pertanyaanId => $jawabanValue) {
            $data = [
                'user_id'        => $user->id,
                'public_body_id' => $publicBody->id,
                'pertanyaan_id'  => $pertanyaanId,
                'tahun_id'       => $tahun->id,
                'jawaban'        => $jawabanValue,
            ];
 
            if (!empty($links[$pertanyaanId])) {
                $rawLinks = is_array($links[$pertanyaanId]) ? $links[$pertanyaanId] : [$links[$pertanyaanId]];
                $data['links'] = array_values(array_filter($rawLinks, fn($l) => !empty($l) && trim($l) !== ''));
            } else {
                $data['links'] = null;
            }
 
            if (isset($dokumens[$pertanyaanId])) {
                $file = $dokumens[$pertanyaanId];
 
                if ($file->getSize() > 5 * 1024 * 1024) {
                    // Skip file terlalu besar — error ditangani di controller store()
                    continue;
                }
 
                $existing = Jawaban::where('public_body_id', $publicBody->id)
                    ->where('pertanyaan_id', $pertanyaanId)
                    ->where('tahun_id', $tahun->id)
                    ->first();
 
                if ($existing?->dokumen_path) {
                    Storage::disk('public')->delete($existing->dokumen_path);
                }
 
                $data['dokumen_path'] = $file->store(
                    "dokumen_kuesioner/{$publicBody->id}",
                    'public'
                );
            }
 
            Jawaban::updateOrCreate(
                [
                    'public_body_id' => $publicBody->id,
                    'pertanyaan_id'  => $pertanyaanId,
                    'tahun_id'       => $tahun->id,
                ],
                $data
            );
        }
    }
}