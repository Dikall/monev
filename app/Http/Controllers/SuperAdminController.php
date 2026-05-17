<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Models\Kategori;
use App\Models\Tahun;
use App\Models\PublicBody;
use App\Models\Jawaban;
use App\Models\Indikator;
use App\Models\Pertanyaan;
use App\Models\Penilaian;

class SuperAdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view-dashboard', ['only' => ['index','show']]);
    }

    public function index(Request $request): View
    {
        $selectedTahunId = $request->input('tahun_id');
        $tahuns = Tahun::orderBy('tahun', 'desc')->get();
        
        if ($selectedTahunId) {
            $tahunAktif = Tahun::find($selectedTahunId);
        } else {
            $tahunAktif = Tahun::orderBy('tahun', 'desc')->first();
            $selectedTahunId = $tahunAktif?->id;
        }

        $kategoris = Kategori::all();
        $stats = [];

        foreach ($kategoris as $kategori) {
            $publicBodyIds = PublicBody::where('kategori_id', $kategori->id)->pluck('id');

            $terdaftar = User::role('Badan Publik')
                ->whereIn('public_body_id', $publicBodyIds)
                ->count();

            $terverifikasi = User::role('Badan Publik')
                ->whereIn('public_body_id', $publicBodyIds)
                ->where('is_aktif', true)
                ->count();

            $sudahMengisi = PublicBody::where('kategori_id', $kategori->id)
                ->whereHas('jawabans', function ($query) use ($selectedTahunId) {
                    $query->where('tahun_id', $selectedTahunId)
                          ->where('is_submitted', true);
                })->count();

            $stats[] = (object)[
                'kategori' => $kategori->name,
                'terdaftar' => $terdaftar,
                'terverifikasi' => $terverifikasi,
                'sudah_mengisi' => $sudahMengisi,
            ];
        }

        return view('superadmin.dashboard', compact('tahuns', 'selectedTahunId', 'stats', 'tahunAktif'));
    }

    /**
     * Show the form for creating a new resource.
     */

    public function create(): View
    {
        return view('superadmin.create', [
            'roles' => Role::pluck('name')->all()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $input = $request->all();
        $input['password'] = Hash::make($request->password);

        $user = User::create($input);
        $user->assignRole($request->roles);

        return redirect()->route('superadmin.index')
            ->withSuccess('New user is added successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): View
    {
        return view('superadmin.show', [
            'user' => $user
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user): View
    {
        // Check Only Super Admin can update his own Profile
        if ($user->hasRole('Super Admin')){
            if($user->id != auth()->user()->id){
                abort(403, 'USER DOES NOT HAVE THE RIGHT PERMISSIONS');
            }
        }

        return view('superadmin.edit', [
            'user' => $user,
            'roles' => Role::pluck('name')->all(),
            'userRoles' => $user->roles->pluck('name')->all()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $input = $request->all();
        if(!empty($request->password)){
            $input['password'] = Hash::make($request->password);
        }else{
            $input = $request->except('password');
        }

        $user->update($input);
        $user->syncRoles($request->roles);

        return redirect()->back()
            ->withSuccess('User is updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
    // About if user is Super Admin or User ID belongs to Auth User
        if ($user->hasRole('Super Admin') || $user->id == auth()->user()->id)
        {
            abort(403, 'USER DOES NOT HAVE THE RIGHT PERMISSIONS');
        }

        $user->syncRoles([]);
        $user->delete();
        return redirect()->route('superadmin.index')
            ->withSuccess('User is deleted successfully.');
    }

    /**
     * Rekap Nilai (Super Admin)
     */
    public function rekapNilai(Request $request): View
    {
        $admin = Auth::user();

        // Filters
        $selectedTahunId      = $request->input('tahun_id');
        $selectedKategoriId   = $request->input('kategori_id');
        $selectedPublishDate  = $request->input('tanggal_publish');
        $search               = $request->input('search');

        $tahuns    = Tahun::orderBy('tahun', 'desc')->get();
        $kategoris = Kategori::all();

        // Ambil tahun aktif (berdasarkan filter atau terbaru)
        if ($selectedTahunId) {
            $tahunAktif = Tahun::find($selectedTahunId);
        } else {
            $tahunAktif = Tahun::orderBy('tahun', 'desc')->first();
        }

        if (!$tahunAktif) {
            // Jika belum ada data tahun sama sekali
            return view('superadmin.rekap-nilai', [
                'admin' => $admin,
                'tahuns' => $tahuns,
                'kategoris' => $kategoris,
                'bodies' => [],
                'indikators' => [],
                'tahunAktif' => null
            ]);
        }

        // Ambil indikator untuk tabel (jika kategori dipilih)
        $indikators = [];
        if ($selectedKategoriId) {
            $indikators = Indikator::where('tahun_id', $tahunAktif->id)
                ->where('kategori_id', $selectedKategoriId)
                ->orderBy('no')
                ->get();
        }

        // Query Badan Publik
        $query = PublicBody::with(['kategori', 'users' => function($q) {
            $q->role('Badan Publik');
        }]);

        if ($selectedKategoriId) {
            $query->where('kategori_id', $selectedKategoriId);
        }

        if ($selectedPublishDate) {
            $query->whereHas('penilaians', function($q) use ($selectedPublishDate, $tahunAktif) {
                $q->where('tahun_id', $tahunAktif->id)
                  ->whereDate('tanggal_publish', $selectedPublishDate);
            });
        }

        if ($search) {
            $query->where('nama_badan', 'like', '%' . $search . '%');
        }

        $bodiesRaw = $query->get();

        // Proses Data Nilai
        $bodies = [];
        foreach ($bodiesRaw as $body) {
            // Ambil nama responden
            $userBp = $body->users->first();
            $namaResponden = $userBp->nama_responden ?? '-';

            // Hitung nilai per indikator (hanya yang sudah diverifikasi)
            $nilaiPerIndikator = [];
            $totalNilaiSAQ = 0;

            // Kita butuh semua indikator untuk kategori si body ini (jika tidak difilter kategori)
            $bodyIndikators = $selectedKategoriId ? $indikators : Indikator::where('tahun_id', $tahunAktif->id)
                ->where('kategori_id', $body->kategori_id)
                ->orderBy('no')
                ->get();

            foreach ($bodyIndikators as $ind) {
                $pertanyaanIds = Pertanyaan::where('level', 'pertanyaan')
                    ->where('indikator_id', $ind->id)
                    ->pluck('id');

                $jawabans = Jawaban::where('public_body_id', $body->id)
                    ->where('tahun_id', $tahunAktif->id)
                    ->whereIn('pertanyaan_id', $pertanyaanIds)
                    ->get();

                $totalBobotPertanyaan = Pertanyaan::whereIn('id', $pertanyaanIds)->sum('bobot');
                
                // Nilai SAQ hanya dihitung dari jawaban yang is_verified = true
                $bobotYaVerified = Pertanyaan::whereIn('id',
                    $jawabans->filter(function($j) {
                        return $j->jawaban == 1 && $j->is_verified === true;
                    })->pluck('pertanyaan_id')
                )->sum('bobot');

                $nilaiIndikator = $totalBobotPertanyaan > 0 ? round(($bobotYaVerified / $totalBobotPertanyaan) * $ind->bobot, 2) : 0;
                $nilaiPerIndikator[$ind->id] = $nilaiIndikator;
                $totalNilaiSAQ += $nilaiIndikator;
            }

            // Ambil data penilaian (presentasi & publish)
            $penilaian = Penilaian::where('public_body_id', $body->id)
                ->where('tahun_id', $tahunAktif->id)
                ->first();
            
            $nilaiPresentasi = $penilaian->nilai_presentasi ?? null;
            $isPublished     = $penilaian->is_published ?? false;

            // Hitung Total Score
            // Jika presentasi belum ada, SAQ = 100%
            // Jika ada, gunakan bobot dari tabel tahun
            $totalScore = 0;
            if ($nilaiPresentasi === null) {
                $totalScore = $totalNilaiSAQ;
            } else {
                $weightSAQ   = $tahunAktif->bobot_saq / 100;
                $weightPres  = $tahunAktif->bobot_presentasi / 100;
                $totalScore  = round(($totalNilaiSAQ * $weightSAQ) + ($nilaiPresentasi * $weightPres), 2);
            }

            // Tentukan Kualifikasi
            $kualifikasi = '';
            $bgClass = '';
            if ($totalScore >= 90) {
                $kualifikasi = 'Informatif';
                $bgClass = 'bg-green-500 text-white';
            } elseif ($totalScore >= 80) {
                $kualifikasi = 'Menuju Informatif';
                $bgClass = 'bg-blue-500 text-white';
            } elseif ($totalScore >= 60) {
                $kualifikasi = 'Cukup Informatif';
                $bgClass = 'bg-yellow-500 text-white';
            } elseif ($totalScore >= 40) {
                $kualifikasi = 'Kurang Informatif';
                $bgClass = 'bg-red-500 text-white';
            } else {
                $kualifikasi = 'Tidak Informatif';
                $bgClass = 'bg-black text-white';
            }

            $bodies[] = [
                'id'                => $body->id,
                'nama_badan'        => $body->nama_badan,
                'nama_responden'    => $namaResponden,
                'nilai_saq'         => round($totalNilaiSAQ, 2),
                'nilai_per_ind'     => $nilaiPerIndikator,
                'nilai_presentasi'  => $nilaiPresentasi,
                'file_bukti'        => $penilaian->file_bukti_presentasi ?? null,
                'total_score'       => $totalScore,
                'kualifikasi'       => $kualifikasi,
                'bg_class'          => $bgClass,
                'is_published'      => $isPublished,
                'waktu_publish'     => $penilaian?->tanggal_publish ? $penilaian->tanggal_publish->format('d/m/Y H:i') : '-',
                'kategori_id'       => $body->kategori_id,
                'body_indikators'   => $bodyIndikators // Untuk iterasi kolom jika kategori tidak difilter
            ];
        }

        return view('superadmin.rekap-nilai', compact(
            'admin', 'tahuns', 'kategoris', 'bodies', 'indikators', 'tahunAktif'
        ));
    }

    /**
     * Update Bobot Nilai (SAQ & Presentasi)
     */
    public function updateBobot(Request $request): RedirectResponse
    {
        $request->validate([
            'tahun_id'         => 'required|exists:tahuns,id',
            'bobot_saq'        => 'required|numeric|min:0|max:100',
            'bobot_presentasi' => 'required|numeric|min:0|max:100',
        ]);

        if (($request->bobot_saq + $request->bobot_presentasi) != 100) {
            return back()->with('error', 'Total bobot harus 100%.');
        }

        $tahun = Tahun::findOrFail($request->tahun_id);
        $tahun->update([
            'bobot_saq'        => $request->bobot_saq,
            'bobot_presentasi' => $request->bobot_presentasi,
        ]);

        return back()->with('success', 'Bobot nilai berhasil diperbarui.');
    }

    /**
     * Update Nilai Presentasi & Upload Bukti
     */
    public function updatePresentasi(Request $request, $publicBodyId): RedirectResponse
    {
        $request->validate([
            'tahun_id'         => 'required|exists:tahuns,id',
            'nilai_presentasi' => 'required|numeric|min:0|max:100',
            'file_bukti'       => 'nullable|file|mimes:pdf,jpg,png,jpeg|max:2048',
        ]);

        $penilaian = Penilaian::firstOrNew([
            'public_body_id' => $publicBodyId,
            'tahun_id'       => $request->tahun_id
        ]);

        $penilaian->nilai_presentasi = $request->nilai_presentasi;

        if ($request->hasFile('file_bukti')) {
            // Hapus file lama jika ada
            if ($penilaian->file_bukti_presentasi) {
                Storage::delete('public/' . $penilaian->file_bukti_presentasi);
            }
            $path = $request->file('file_bukti')->store('bukti_presentasi', 'public');
            $penilaian->file_bukti_presentasi = $path;
        }

        $penilaian->save();

        return back()->with('success', 'Nilai presentasi berhasil disimpan.');
    }

    /**
     * Unpublish / Reset Publish Nilai
     */
    public function resetPublish($publicBodyId): RedirectResponse
    {
        $tahunSekarang = now()->year;
        $tahun = Tahun::where('tahun', $tahunSekarang)->firstOrFail();

        $penilaian = Penilaian::where('public_body_id', $publicBodyId)
            ->where('tahun_id', $tahun->id)
            ->first();

        if ($penilaian) {
            $penilaian->is_published = false;
            $penilaian->tanggal_publish = null;
            $penilaian->save();
        }

        return back()->with('success', 'Publikasi nilai berhasil dibatalkan.');
    }

    /**
     * Export Detail Verifikasi per Badan Publik ke Excel (CSV)
     */
    public function exportDetailExcel($publicBodyId)
    {
        $publicBody = PublicBody::with('kategori')->findOrFail($publicBodyId);
        $kategori = $publicBody->kategori;

        $tahunSekarang = now()->year;
        $tahun = Tahun::where('tahun', $tahunSekarang)->first();

        if (!$tahun) {
            return back()->with('error', 'Tahun aktif tidak ditemukan.');
        }

        // Ambil indikator untuk kategori ini
        $indikators = Indikator::where('tahun_id', $tahun->id)
            ->where('kategori_id', $kategori->id)
            ->orderBy('no')
            ->get();

        // Ambil semua pertanyaan (flat list untuk export)
        $pertanyaans = Pertanyaan::where('kategori_id', $kategori->id)
            ->where('tahun_id', $tahun->id)
            ->where('level', 'pertanyaan')
            ->orderBy('nomor')
            ->get();

        // Ambil jawaban
        $jawabans = Jawaban::where('public_body_id', $publicBody->id)
            ->where('tahun_id', $tahun->id)
            ->get()
            ->keyBy('pertanyaan_id');

        $headers = ['No', 'Indikator', 'Pertanyaan', 'Jawaban Badan Publik', 'Hasil Verifikasi', 'Catatan Verifikasi'];
        $rows = [];

        foreach ($pertanyaans as $p) {
            $jawaban = $jawabans[$p->id] ?? null;
            
            $jawabanText = '-';
            if ($jawaban) {
                $jawabanText = ($jawaban->jawaban == 1) ? 'Ya' : (($jawaban->jawaban == 0) ? 'Tidak' : '-');
            }

            $verifikasiText = 'Belum Diverifikasi';
            if ($jawaban && $jawaban->is_verified !== null) {
                $verifikasiText = $jawaban->is_verified ? 'Sesuai (Ya)' : 'Tidak Sesuai (Tidak)';
            }

            $rows[] = [
                $p->nomor,
                $p->indikator->nama_indikator ?? '-',
                $p->pertanyaan_kuisioner,
                $jawabanText,
                $verifikasiText,
                $jawaban->catatan_verifikasi ?? '-'
            ];
        }

        $filename = 'Detail_Verifikasi_' . str_replace(' ', '_', $publicBody->nama_badan) . '_' . $tahunSekarang . '.csv';

        $callback = function () use ($headers, $rows) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM
            fputcsv($file, $headers, ';');
            foreach ($rows as $row) {
                fputcsv($file, $row, ';');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
