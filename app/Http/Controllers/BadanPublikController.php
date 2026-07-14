<?php
 
namespace App\Http\Controllers;
 
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Jawaban;
use App\Models\Kategori;
use App\Models\Indikator;
use App\Models\Pertanyaan;
use App\Models\Penilaian;
use App\Models\Tahun;
use App\Models\Tenggat;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Notification;
 
class BadanPublikController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();

        // Cek verifikasi akun
        if (!$user->is_aktif) {
            return view('badanpublik.beranda', [
                'tidak_aktif' => true,
                'user' => $user,
                'publicBody' => $user->publicBody
            ]);
        }

        $publicBody = $user->publicBody;
        if (!$publicBody) abort(403, 'Badan publik tidak ditemukan.');

        $tahunSekarang = now()->year;
        $tahun         = Tahun::where('tahun', $tahunSekarang)->first();
        if (!$tahun) abort(403, 'Tahun aktif tidak ditemukan.');

        $kategoriAktif = $publicBody->kategori;
        $kategoriId    = $kategoriAktif?->id;
        if (!$kategoriId) abort(403, 'Badan publik belum memiliki kategori.');

        $tenggat  = Tenggat::where('kategori_id', $kategoriId)->first();
        $now      = now();
        $isOpen   = $tenggat
            && $now->gte($tenggat->waktu_aktif)
            && $now->lte($tenggat->waktu_nonaktif);
        $isClosed = $tenggat && $now->gt($tenggat->waktu_nonaktif);

        // Cek apakah sudah di-submit
        $sudahSubmit = Jawaban::where('public_body_id', $publicBody->id)
            ->where('tahun_id', $tahun->id)
            ->where('is_submitted', true)
            ->exists();

        // Hitung total pertanyaan & yang sudah dijawab
        $indikators = Indikator::where('tahun_id', $tahun->id)
            ->where('kategori_id', $kategoriId)
            ->orderBy('no')
            ->get();
            
        $pertanyaanIds = Pertanyaan::where('level', 'pertanyaan')
            ->whereIn('indikator_id', $indikators->pluck('id'))
            ->pluck('id');

        $totalPertanyaan = $pertanyaanIds->count();
        
        $jawabans = Jawaban::where('public_body_id', $publicBody->id)
            ->where('tahun_id', $tahun->id)
            ->whereIn('pertanyaan_id', $pertanyaanIds)
            ->whereNotNull('jawaban')
            ->get();

        $totalDijawab = 0;
        foreach ($jawabans as $j) {
            if ($j->jawaban == 0) {
                $totalDijawab++;
            } elseif ($j->jawaban == 1) {
                $hasLinks = !empty($j->links) && is_array($j->links) && count(array_filter($j->links)) > 0;
                $hasFile  = !empty($j->dokumen_path);
                if ($hasLinks || $hasFile) {
                    $totalDijawab++;
                }
            }
        }

        $persen = $totalPertanyaan > 0 ? round(($totalDijawab / $totalPertanyaan) * 100) : 0;

        // Terakhir diperbarui
        $terakhirDiperbarui = Jawaban::where('public_body_id', $publicBody->id)
            ->where('tahun_id', $tahun->id)
            ->max('updated_at');

        // Indikator yang belum lengkap
        $indikatorBelumLengkap = [];
        foreach ($indikators as $ind) {
            $pIds = Pertanyaan::where('level', 'pertanyaan')
                ->where('indikator_id', $ind->id)
                ->pluck('id');
            
            $jInd = Jawaban::where('public_body_id', $publicBody->id)
                ->where('tahun_id', $tahun->id)
                ->whereIn('pertanyaan_id', $pIds)
                ->whereNotNull('jawaban')
                ->get();

            $countLengkap = 0;
            foreach ($jInd as $j) {
                if ($j->jawaban == 0) {
                    $countLengkap++;
                } elseif ($j->jawaban == 1) {
                    $hasLinks = !empty($j->links) && is_array($j->links) && count(array_filter($j->links)) > 0;
                    $hasFile  = !empty($j->dokumen_path);
                    if ($hasLinks || $hasFile) {
                        $countLengkap++;
                    }
                }
            }
            
            if ($countLengkap < $pIds->count()) {
                $indikatorBelumLengkap[] = $ind->nama_indikator;
            }
        }

        return view('badanpublik.beranda', compact(
            'user', 'publicBody', 'kategoriAktif', 'tahun',
            'tenggat', 'isOpen', 'isClosed', 'sudahSubmit',
            'totalPertanyaan', 'totalDijawab', 'persen',
            'terakhirDiperbarui', 'indikatorBelumLengkap'
        ));
    }
 
    // ─────────────────────────────────────────────────────────
    // TAB KUESIONER — Halaman ringkasan + tombol Edit / Submit
    // ─────────────────────────────────────────────────────────
    public function kuesionerTab(Request $request)
    {
        $user = Auth::user();
 
        // Cek verifikasi akun
        if (!$user->is_aktif) {
            return view('badanpublik.kuesioner.tab_beranda_kuesioner', ['tidak_aktif' => true]);
        }
 
        $publicBody = $user->publicBody;
        if (!$publicBody) abort(403, 'Badan publik tidak ditemukan.');
 
        $tahunSekarang = now()->year;
        $tahun         = Tahun::where('tahun', $tahunSekarang)->first();
        if (!$tahun) abort(403, 'Tahun aktif tidak ditemukan.');
 
        $kategoriAktif = $publicBody->kategori;
        $kategoriId    = $kategoriAktif?->id;
        if (!$kategoriId) abort(403, 'Badan publik belum memiliki kategori.');
 
        $tenggat  = Tenggat::where('kategori_id', $kategoriId)->first();
        $now      = now();
        $isOpen   = $tenggat
            && $now->gte($tenggat->waktu_aktif)
            && $now->lte($tenggat->waktu_nonaktif);
        $isClosed = $tenggat && $now->gt($tenggat->waktu_nonaktif);
 
        // Cek apakah sudah di-submit
        $sudahSubmit = Jawaban::where('public_body_id', $publicBody->id)
            ->where('tahun_id', $tahun->id)
            ->where('is_submitted', true)
            ->exists();
 
        // Hitung total pertanyaan & yang sudah dijawab
        $indikators   = Indikator::where('tahun_id', $tahun->id)
            ->where('kategori_id', $kategoriId)
            ->get();
        $pertanyaanIds = Pertanyaan::where('level', 'pertanyaan')
            ->whereIn('indikator_id', $indikators->pluck('id'))
            ->pluck('id');
 
        $totalPertanyaan = $pertanyaanIds->count();
        $jawabans = Jawaban::where('public_body_id', $publicBody->id)
            ->where('tahun_id', $tahun->id)
            ->whereIn('pertanyaan_id', $pertanyaanIds)
            ->whereNotNull('jawaban')
            ->get();

        $totalDijawab = 0;
        foreach ($jawabans as $j) {
            if ($j->jawaban == 0) {
                $totalDijawab++;
            } elseif ($j->jawaban == 1) {
                $hasLinks = !empty($j->links) && is_array($j->links) && count(array_filter($j->links)) > 0;
                $hasFile  = !empty($j->dokumen_path);
                if ($hasLinks || $hasFile) {
                    $totalDijawab++;
                }
            }
        }
 
        return view('badanpublik.kuesioner.tab_beranda_kuesioner', compact(
            'user', 'publicBody', 'kategoriAktif', 'tahun',
            'tenggat', 'isOpen', 'isClosed',
            'sudahSubmit', 'totalPertanyaan', 'totalDijawab',
        ));
    }
 
    // ─────────────────────────────────────────────────────────
    // SUBMIT KUESIONER — Kunci jawaban (is_submitted = true)
    // ─────────────────────────────────────────────────────────
    public function submitKuesioner(Request $request)
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
        $tahun         = Tahun::where('tahun', $tahunSekarang)->firstOrFail();
 
        // Validasi tenggat
        $kategoriId = $publicBody->kategori?->id;
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

        // Tandai semua jawaban milik public body ini sebagai submitted
        Jawaban::where('public_body_id', $publicBody->id)
            ->where('tahun_id', $tahun->id)
            ->update([
                'is_submitted'  => true,
                'submitted_at'  => now(),
            ]);

        // NOTIFIKASI KE SUPER ADMIN & VERIFIKATOR
        $superAdmins = User::role('Super Admin')->get();
        foreach ($superAdmins as $sa) {
            Notification::create([
                'user_id' => $sa->id,
                'title' => 'SUBMIT KUESIONER',
                'message' => "Badan Publik {$publicBody->nama_badan} sudah submit kuesioner.",
            ]);
        }

        $verifikators = $publicBody->admins;
        foreach ($verifikators as $ver) {
            Notification::create([
                'user_id' => $ver->id,
                'title' => 'SUBMIT KUESIONER',
                'message' => "Badan Publik {$publicBody->nama_badan} sudah submit kuesioner.",
            ]);
        }
 
        return redirect()
            ->route('kuesioner.tab')
            ->with('success', 'Kuesioner berhasil di-submit. Jawaban telah dikunci.');
    }
 
    // ─────────────────────────────────────────────────────────
    // HASIL PENILAIAN
    // ─────────────────────────────────────────────────────────
    public function hasilPenilaian()
    {
        $user = Auth::user();
 
        if (!$user->is_aktif) {
            return view('badanpublik.kuesioner.hasil_penilaian_kuesioner', ['tidak_aktif' => true]);
        }
 
        $publicBody = $user->publicBody;
        if (!$publicBody) abort(403, 'Badan publik tidak ditemukan.');
 
        $tahunSekarang = now()->year;
        $tahun         = Tahun::where('tahun', $tahunSekarang)->first();
        if (!$tahun) abort(403, 'Tahun aktif tidak ditemukan.');
 
        $kategoriAktif = $publicBody->kategori;
 
        // Ambil penilaian
        $penilaian = Penilaian::where('public_body_id', $publicBody->id)
            ->where('tahun_id', $tahun->id)
            ->first();

        // Hanya tampilkan jika sudah di-publish oleh admin
        $sudahDinilai = ($penilaian && $penilaian->is_published);
 
        // Ambil jawaban untuk ditampilkan ringkasan per indikator
        $indikators = Indikator::where('tahun_id', $tahun->id)
            ->where('kategori_id', $kategoriAktif?->id)
            ->orderBy('no')
            ->get();
 
        $ringkasanPerIndikator = [];
        $totalNilaiSAQ = 0;

        foreach ($indikators as $ind) {
            $pertanyaanIds = Pertanyaan::where('level', 'pertanyaan')
                ->where('indikator_id', $ind->id)
                ->pluck('id');

            $jawabans = Jawaban::where('public_body_id', $publicBody->id)
                ->where('tahun_id', $tahun->id)
                ->whereIn('pertanyaan_id', $pertanyaanIds)
                ->get();

            $totalBobotPertanyaan = Pertanyaan::whereIn('id', $pertanyaanIds)->sum('bobot');
            
            // Effective "Ya" is when original answer was 1 AND it was verified by admin (is_verified === true), or when original was 0 AND it was verified as Ya by admin (is_verified === true)
            $effectiveYaJawabans = $jawabans->filter(fn($j) => ($j->jawaban == 1 && $j->is_verified === true) || ($j->jawaban == 0 && $j->is_verified === true));
            $bobotYa = Pertanyaan::whereIn('id', $effectiveYaJawabans->pluck('pertanyaan_id'))->sum('bobot');

            $nilaiIndikator = $totalBobotPertanyaan > 0 ? round(($bobotYa / $totalBobotPertanyaan) * $ind->bobot, 2) : 0;
            $totalNilaiSAQ += $nilaiIndikator;

            $ringkasanPerIndikator[] = [
                'indikator'   => $ind,
                'total'       => $pertanyaanIds->count(),
                'dijawab_ya'  => $effectiveYaJawabans->count(),
                'dijawab_tidak' => $jawabans->filter(fn($j) => ($j->jawaban == 0 && $j->is_verified !== true) || ($j->jawaban == 1 && $j->is_verified === false))->count(),
                'bobot_ya'    => $bobotYa,
                'total_bobot' => $totalBobotPertanyaan,
                'persentase'  => $totalBobotPertanyaan > 0 ? round(($bobotYa / $totalBobotPertanyaan) * 100, 2) : 0,
            ];
        }

        if ($sudahDinilai) {
            // Hitung Skor Akhir (SAQ + Presentasi)
            $nilaiPresentasi = $penilaian->nilai_presentasi;
            
            if ($nilaiPresentasi === null) {
                // Jika presentasi belum ada, SAQ = 100%
                $totalScore = $totalNilaiSAQ;
            } else {
                // Gunakan bobot dari tabel tahun
                $weightSAQ   = ($tahun->bobot_saq ?? 70) / 100;
                $weightPres  = ($tahun->bobot_presentasi ?? 30) / 100;
                $totalScore  = ($totalNilaiSAQ * $weightSAQ) + ($nilaiPresentasi * $weightPres);
            }
            
            $penilaian->skor_total = round($totalScore, 2);
            
            // Tentukan Predikat
            if ($penilaian->skor_total >= 90) {
                $penilaian->predikat = 'Informatif';
            } elseif ($penilaian->skor_total >= 80) {
                $penilaian->predikat = 'Menuju Informatif';
            } elseif ($penilaian->skor_total >= 60) {
                $penilaian->predikat = 'Cukup Informatif';
            } elseif ($penilaian->skor_total >= 40) {
                $penilaian->predikat = 'Kurang Informatif';
            } else {
                $penilaian->predikat = 'Tidak Informatif';
            }
        }
 
        return view('badanpublik.kuesioner.hasil_penilaian_kuesioner', compact(
            'user', 'publicBody', 'kategoriAktif', 'tahun',
            'penilaian', 'sudahDinilai', 'ringkasanPerIndikator',
        ));
    }
 
    // ─────────────────────────────────────────────────────────
    // Metode lain (bawaan resource)
    // ─────────────────────────────────────────────────────────
    public function index(): View
    {
        //
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
