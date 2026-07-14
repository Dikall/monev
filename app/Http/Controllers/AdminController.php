<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\PublicBody;
use App\Models\Kategori;
use App\Models\Jawaban;
use App\Models\Tahun;
use App\Models\Indikator;
use App\Models\Pertanyaan;
use App\Models\Penilaian;
use App\Models\Tenggat;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use App\Models\Notification;

class AdminController extends Controller
{
    public function index(): View
    {
        $users = User::role(['Admin', 'Super Admin'])->with('publicBodies.kategori')->get();
        
        // Ambil semua ID badan publik yang sudah di-set ke verifikator (admin) mana pun
        $assignedBodyIds = \Illuminate\Support\Facades\DB::table('admin_public_body')
            ->pluck('public_body_id')
            ->toArray();

        return view('superadmin.kelola_admin', [
            'users'           => $users,
            'bodies'          => PublicBody::with('kategori')->get(),
            'kategoris'       => Kategori::all(),
            'assignedBodyIds' => $assignedBodyIds,
        ]);
    }

    /**
     * Beranda Admin (Verifikator)
     * Menampilkan ringkasan per kategori: jumlah yang harus diverifikasi & sudah mengisi
     */
    public function dashboard()
    {
        $admin = Auth::user();

        // Ambil tahun aktif
        $tahunSekarang = now()->year;
        $tahun = Tahun::where('tahun', $tahunSekarang)->first();

        // Ambil ID badan publik yang di-assign ke admin
        $assignedBodies = $admin->hasRole('Super Admin') ? PublicBody::all() : $admin->publicBodies;
        $assignedKategoriIds = $assignedBodies->pluck('kategori_id')->unique();

        // Ambil hanya kategori yang memiliki badan publik yang di-assign ke admin
        $allKategoris = Kategori::whereIn('id', $assignedKategoriIds)->get();

        $kategoriStats = [];

        foreach ($allKategoris as $kategori) {
            $bodyIds = $assignedBodies->where('kategori_id', $kategori->id)->pluck('id');

            $totalVerifikasi = 0;
            $totalMengisi    = 0;

            if ($tahun && $bodyIds->isNotEmpty()) {
                // Cari public_body yang punya jawaban dengan is_submitted = true
                $totalVerifikasi = Jawaban::whereIn('public_body_id', $bodyIds)
                    ->where('tahun_id', $tahun->id)
                    ->where('is_submitted', true)
                    ->distinct('public_body_id')
                    ->count('public_body_id');

                // Cari public_body yang punya jawaban tapi BELUM submit
                $bodySubmitted = Jawaban::whereIn('public_body_id', $bodyIds)
                    ->where('tahun_id', $tahun->id)
                    ->where('is_submitted', true)
                    ->distinct()
                    ->pluck('public_body_id');

                $totalMengisi = Jawaban::whereIn('public_body_id', $bodyIds)
                    ->where('tahun_id', $tahun->id)
                    ->where('is_submitted', false)
                    ->whereNotIn('public_body_id', $bodySubmitted)
                    ->distinct('public_body_id')
                    ->count('public_body_id');
            }

            $kategoriStats[] = [
                'kategori'         => $kategori,
                'total_verifikasi' => $totalVerifikasi,
                'total_mengisi'    => $totalMengisi,
            ];
        }

        return view('admin.beranda', compact('admin', 'kategoriStats', 'tahun'));
    }

    /**
     * Export rekap nilai kuesioner ke Excel (CSV)
     */
    public function exportExcel()
    {
        $admin = Auth::user();

        $tahunSekarang = now()->year;
        $tahun = Tahun::where('tahun', $tahunSekarang)->first();

        if (!$tahun) {
            return back()->with('error', 'Tahun aktif tidak ditemukan.');
        }

        // Ambil public bodies yang di-assign ke admin
        $assignedBodies = $admin->hasRole('Super Admin') 
            ? PublicBody::with('kategori')->get() 
            : $admin->publicBodies()->with('kategori')->get();
        $bodyIds = $assignedBodies->pluck('id');

        // Ambil indikator berdasarkan kategori-kategori yang ada
        $kategoriIds = $assignedBodies->pluck('kategori_id')->unique();

        $indikators = Indikator::where('tahun_id', $tahun->id)
            ->whereIn('kategori_id', $kategoriIds)
            ->orderBy('kategori_id')
            ->orderBy('no')
            ->get();

        // Header CSV
        $headers = ['No', 'Kategori', 'Nama Badan Publik', 'Status Submit'];

        foreach ($indikators as $ind) {
            $headers[] = $ind->nama_indikator . ' (Bobot: ' . $ind->bobot . ')';
        }
        $headers[] = 'Total Nilai';

        // Data rows
        $rows = [];

        $no = 1;
        foreach ($assignedBodies as $body) {
            $row = [
                $no++,
                $body->kategori->name ?? '-',
                $body->nama_badan,
            ];

            // Cek apakah sudah submit
            $sudahSubmit = Jawaban::where('public_body_id', $body->id)
                ->where('tahun_id', $tahun->id)
                ->where('is_submitted', true)
                ->exists();

            $row[] = $sudahSubmit ? 'Sudah Submit' : 'Belum Submit';

            $totalNilai = 0;

            // Hitung nilai per indikator
            $bodyIndikators = Indikator::where('tahun_id', $tahun->id)
                ->where('kategori_id', $body->kategori_id)
                ->orderBy('no')
                ->get();

            foreach ($indikators as $ind) {
                // Hanya hitung jika indikator sesuai kategori body
                if ($ind->kategori_id != $body->kategori_id) {
                    $row[] = '-';
                    continue;
                }

                $pertanyaanIds = Pertanyaan::where('level', 'pertanyaan')
                    ->where('indikator_id', $ind->id)
                    ->pluck('id');

                $jawabans = Jawaban::where('public_body_id', $body->id)
                    ->where('tahun_id', $tahun->id)
                    ->whereIn('pertanyaan_id', $pertanyaanIds)
                    ->get();

                $totalBobot = Pertanyaan::whereIn('id', $pertanyaanIds)->sum('bobot');
                
                // Effective "Ya" is when original answer was 1 AND it was NOT rejected by admin (is_verified !== false), or when original was 0 AND it was verified as Ya by admin (is_verified === true)
                $bobotYa = Pertanyaan::whereIn('id',
                    $jawabans->filter(function($j) {
                        return ($j->jawaban == 1 && $j->is_verified !== false) || ($j->jawaban == 0 && $j->is_verified === true);
                    })->pluck('pertanyaan_id')
                )->sum('bobot');

                $persentase = $totalBobot > 0 ? round(($bobotYa / $totalBobot) * 100, 2) : 0;
                $nilaiIndikator = $totalBobot > 0 ? round(($bobotYa / $totalBobot) * $ind->bobot, 2) : 0;

                $row[] = $nilaiIndikator;
                $totalNilai += $nilaiIndikator;
            }

            $row[] = round($totalNilai, 2);
            $rows[] = $row;
        }

        // Generate CSV
        $filename = 'rekap_nilai_kuesioner_' . $admin->name . '_' . $tahunSekarang . '.csv';

        $callback = function () use ($headers, $rows) {
            $file = fopen('php://output', 'w');

            // BOM for UTF-8 Excel
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, $headers, ';');
            foreach ($rows as $row) {
                fputcsv($file, $row, ';');
            }

            fclose($file);
        };

        return Response::stream($callback, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Export rekap nilai kuesioner ke Excel XLSX terformat (via Python/openpyxl)
     */
    public function exportExcelFormatted()
    {
        $admin = Auth::user();

        $tahunSekarang = now()->year;
        $tahun = Tahun::where('tahun', $tahunSekarang)->first();

        if (!$tahun) {
            return back()->with('error', 'Tahun aktif tidak ditemukan.');
        }

        // Ambil public bodies yang di-assign ke admin
        $assignedBodies = $admin->hasRole('Super Admin')
            ? PublicBody::with('kategori')->get()
            : $admin->publicBodies()->with('kategori')->get();

        $kategoriIds = $assignedBodies->pluck('kategori_id')->unique();

        $indikators = Indikator::where('tahun_id', $tahun->id)
            ->whereIn('kategori_id', $kategoriIds)
            ->orderBy('kategori_id')
            ->orderBy('no')
            ->get();

        // Bangun data baris
        $rows = [];
        $no = 1;

        foreach ($assignedBodies as $body) {
            $userBp        = $body->users()->role('Badan Publik')->first();
            $namaResponden = $userBp?->nama_responden ?? '-';
            $isSubmitted   = Jawaban::where('public_body_id', $body->id)
                ->where('tahun_id', $tahun->id)
                ->where('is_submitted', true)
                ->exists();

            $nilaiPerIndikator   = [];
            $totalNilaiKuesioner = 0;

            foreach ($indikators as $ind) {
                if ($ind->kategori_id != $body->kategori_id) {
                    $nilaiPerIndikator[$ind->id] = null; // beda kategori
                    continue;
                }

                $pertanyaanIds = Pertanyaan::where('level', 'pertanyaan')
                    ->where('indikator_id', $ind->id)
                    ->pluck('id');

                $jawabans = Jawaban::where('public_body_id', $body->id)
                    ->where('tahun_id', $tahun->id)
                    ->whereIn('pertanyaan_id', $pertanyaanIds)
                    ->get();

                $totalBobot = Pertanyaan::whereIn('id', $pertanyaanIds)->sum('bobot');

                $bobotYa = Pertanyaan::whereIn('id',
                    $jawabans->filter(fn($j) => ($j->jawaban == 1 && $j->is_verified !== false) || ($j->jawaban == 0 && $j->is_verified === true))
                        ->pluck('pertanyaan_id')
                )->sum('bobot');

                $nilaiIndikator = $totalBobot > 0
                    ? round(($bobotYa / $totalBobot) * $ind->bobot, 2)
                    : 0;

                $nilaiPerIndikator[$ind->id] = $nilaiIndikator;
                $totalNilaiKuesioner += $nilaiIndikator;
            }

            $penilaian       = Penilaian::where('public_body_id', $body->id)
                ->where('tahun_id', $tahun->id)->first();
            $nilaiPresentasi = $penilaian?->nilai_presentasi ?? null;
            $isPublished     = $penilaian?->is_published ?? false;

            $totalScore = $nilaiPresentasi !== null
                ? round(($totalNilaiKuesioner * 0.7) + ($nilaiPresentasi * 0.3), 2)
                : null;

            $rows[] = [
                'no'                  => $no++,
                'nama_badan'          => $body->nama_badan,
                'kategori'            => $body->kategori->name ?? '-',
                'nama_responden'      => $namaResponden,
                'nilai_per_indikator' => $nilaiPerIndikator,
                'total_kuesioner'     => round($totalNilaiKuesioner, 2),
                'nilai_presentasi'    => $nilaiPresentasi,
                'total_score'         => $totalScore,
                'is_submitted'        => $isSubmitted,
                'is_published'        => $isPublished,
            ];
        }

        // Serialisasi data ke JSON agar bisa dikirim ke Python
        $dataJson = json_encode([
            'verifikator_name' => $admin->name ?? $admin->username,
            'tahun'            => $tahunSekarang,
            'tanggal_cetak'    => now()->translatedFormat('d F Y'),
            'indikators'       => $indikators->map(fn($i) => [
                'id'             => $i->id,
                'no'             => $i->no,
                'nama_indikator' => strtoupper($i->nama_indikator),
                'bobot'          => $i->bobot,
            ])->values()->toArray(),
            'rows' => $rows,
        ]);

        // Tulis JSON ke file temp
        $tmpJson = tempnam(sys_get_temp_dir(), 'monev_') . '.json';
        $tmpXlsx = tempnam(sys_get_temp_dir(), 'monev_') . '.xlsx';

        file_put_contents($tmpJson, $dataJson);

        // Path ke script Python
        $scriptPath = base_path('app/Console/Scripts/generate_rekap_excel.py');

        // Windows: gunakan "python", Linux/Mac: "python3"
        $pythonBin = PHP_OS_FAMILY === 'Windows' ? 'python' : 'python3';
        $cmd = $pythonBin . ' ' . escapeshellarg($scriptPath)
             . ' ' . escapeshellarg($tmpJson)
             . ' ' . escapeshellarg($tmpXlsx);

        exec($cmd, $output, $exitCode);

        @unlink($tmpJson);

        if ($exitCode !== 0 || !file_exists($tmpXlsx)) {
            return back()->with('error', 'Gagal membuat file Excel: ' . implode("\n", $output));
        }

        $filename = 'Rekap_Nilai_' . ($admin->name ?? $admin->username) . '_' . $tahunSekarang . '.xlsx';

        return Response::download($tmpXlsx, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /**
     * List Akun Badan Publik per Kategori
     * Menampilkan 2 tabel: Mengisi Kuesioner & Tidak Mengisi
     */
    public function listAkun($kategoriId)
    {
        $admin = Auth::user();

        $kategori = Kategori::findOrFail($kategoriId);

        // Ambil tahun aktif
        $tahunSekarang = now()->year;
        $tahun = Tahun::where('tahun', $tahunSekarang)->first();

        if (!$tahun) {
            return back()->with('error', 'Tahun aktif tidak ditemukan.');
        }

        // Ambil indikator untuk kategori ini
        $indikators = Indikator::where('tahun_id', $tahun->id)
            ->where('kategori_id', $kategoriId)
            ->orderBy('no')
            ->get();

        // Cari ID badan publik yang sudah submit
        $bodiesSudahSubmitIds = Jawaban::where('tahun_id', $tahun->id)
            ->where('is_submitted', true)
            ->pluck('public_body_id')
            ->unique();

        // ID Badan Publik yang di-assign ke admin ini:
        $assignedBodiesIds = $admin->hasRole('Super Admin') 
            ? PublicBody::where('kategori_id', $kategoriId)->pluck('id') 
            : $admin->publicBodies()->pluck('public_bodies.id');

        // Ambil semua public bodies di kategori ini yang di-assign ke admin (untuk auto-submit)
        $allBodies = PublicBody::where('kategori_id', $kategoriId)
            ->whereIn('id', $assignedBodiesIds)
            ->get();

        // Ambil tenggat untuk kategori ini
        $tenggat = Tenggat::where('kategori_id', $kategoriId)->first();

        // FITUR AUTO SUBMIT
        if ($tenggat && now()->gt($tenggat->waktu_nonaktif)) {
            Jawaban::where('tahun_id', $tahun->id)
                ->whereIn('public_body_id', $allBodies->pluck('id'))
                ->where('is_submitted', false)
                ->update([
                    'is_submitted' => true,
                    'submitted_at' => $tenggat->waktu_nonaktif
                ]);


            // Refresh data setelah auto-submit
            $bodiesSudahSubmitIds = Jawaban::where('tahun_id', $tahun->id)
                ->where('is_submitted', true)
                ->pluck('public_body_id')
                ->unique();
        }

        // 1. Sudah Submit
        $bodiesMengisiRaw = PublicBody::where('kategori_id', $kategoriId)
            ->whereIn('id', $assignedBodiesIds)
            ->whereIn('id', $bodiesSudahSubmitIds)
            ->get();

        // 2. Belum Submit
        $bodiesTidakMengisiRaw = PublicBody::where('kategori_id', $kategoriId)
            ->whereIn('id', $assignedBodiesIds)
            ->whereNotIn('id', $bodiesSudahSubmitIds)
            ->get();

        // --- PROSES DATA (HITUNG SKOR) ---
        $processData = function($bodies) use ($tahun, $indikators) {
            $data = [];
            foreach ($bodies as $body) {
                // Cek apakah sudah submit
                $isSubmitted = Jawaban::where('public_body_id', $body->id)
                    ->where('tahun_id', $tahun->id)
                    ->where('is_submitted', true)
                    ->exists();

                // Ambil user badan publik (responden)
                $userBp = $body->users()->role('Badan Publik')->first();
                $namaResponden = $userBp->nama_responden ?? '-';

                // Hitung nilai per indikator
                $nilaiPerIndikator = [];
                $totalNilaiKuesioner = 0;

                foreach ($indikators as $ind) {
                    $pertanyaanIds = Pertanyaan::where('level', 'pertanyaan')
                        ->where('indikator_id', $ind->id)
                        ->pluck('id');

                    $jawabans = Jawaban::where('public_body_id', $body->id)
                        ->where('tahun_id', $tahun->id)
                        ->whereIn('pertanyaan_id', $pertanyaanIds)
                        ->get();

                    $totalBobotPertanyaan = Pertanyaan::whereIn('id', $pertanyaanIds)->sum('bobot');
                    
                    // Effective "Ya" is when original answer was 1 AND it was NOT rejected by admin (is_verified !== false), or when original was 0 AND it was verified as Ya by admin (is_verified === true)
                    $bobotYa = Pertanyaan::whereIn('id',
                        $jawabans->filter(function($j) {
                            return ($j->jawaban == 1 && $j->is_verified !== false) || ($j->jawaban == 0 && $j->is_verified === true);
                        })->pluck('pertanyaan_id')
                    )->sum('bobot');

                    $nilaiIndikator = $totalBobotPertanyaan > 0 ? round(($bobotYa / $totalBobotPertanyaan) * $ind->bobot, 2) : 0;
                    $nilaiPerIndikator[$ind->id] = $nilaiIndikator;
                    $totalNilaiKuesioner += $nilaiIndikator;
                }

                $penilaian = Penilaian::where('public_body_id', $body->id)->where('tahun_id', $tahun->id)->first();
                $nilaiPresentasi = $penilaian->nilai_presentasi ?? null;
                $isPublished = $penilaian->is_published ?? false;

                $totalScore = null;
                if ($nilaiPresentasi !== null) {
                    $totalScore = round(($totalNilaiKuesioner * 0.7) + ($nilaiPresentasi * 0.3), 2);
                }

                $unverifiedCount = Jawaban::where('public_body_id', $body->id)
                    ->where('tahun_id', $tahun->id)
                    ->whereNull('is_verified')
                    ->count();
                $isFullyVerified = ($unverifiedCount === 0);
                $canPublish = $isFullyVerified && ($nilaiPresentasi !== null);

                $data[] = [
                    'id'                  => $body->id,
                    'nama_badan'          => $body->nama_badan,
                    'nama_responden'      => $namaResponden,
                    'nilai_per_indikator' => $nilaiPerIndikator,
                    'total_kuesioner'     => round($totalNilaiKuesioner, 2),
                    'nilai_presentasi'    => $nilaiPresentasi,
                    'total_score'         => $totalScore,
                    'is_published'        => $isPublished,
                    'can_publish'         => $canPublish,
                    'penilaian_id'        => $penilaian->id ?? null,
                    'is_submitted'        => $isSubmitted,
                    'body'                => $body, // Keep for potential use in view
                ];
            }
            return $data;
        };

        $bodiesMengisi      = $processData($bodiesMengisiRaw);
        $bodiesTidakMengisi = $processData($bodiesTidakMengisiRaw);

        return view('admin.list-akun', compact(
            'admin', 'kategori', 'tahun', 'indikators',
            'bodiesMengisi', 'bodiesTidakMengisi', 'tenggat'
        ));
    }

    /**
     * Toggle publish nilai untuk badan publik
     */
    public function publishNilai(Request $request, PublicBody $publicBody)
    {
        $admin = Auth::user();
        if (!$admin->hasRole('Super Admin') && !$admin->publicBodies->contains($publicBody->id)) {
            abort(403, 'Anda tidak memiliki akses ke badan publik ini.');
        }

        $tahunSekarang = now()->year;
        $tahun = Tahun::where('tahun', $tahunSekarang)->firstOrFail();

        $penilaian = Penilaian::firstOrCreate(
            ['public_body_id' => $publicBody->id, 'tahun_id' => $tahun->id]
        );

        // Jika akan dipublish, periksa kelengkapan
        if (!$penilaian->is_published) {
            $unverifiedCount = Jawaban::where('public_body_id', $publicBody->id)
                ->where('tahun_id', $tahun->id)
                ->whereNull('is_verified')
                ->count();
            
            if ($unverifiedCount > 0 || $penilaian->nilai_presentasi === null) {
                return back()->with('error', 'Gagal publish: Verifikasi belum selesai atau nilai presentasi belum diisi.');
            }
        }

        $penilaian->is_published = !$penilaian->is_published;
        $penilaian->tanggal_publish = $penilaian->is_published ? now() : null;
        $penilaian->save();

        if ($penilaian->is_published) {
            // NOTIFIKASI KE BADAN PUBLIK (semua user di bawah BP ini)
            $bpUsers = $publicBody->users;
            foreach ($bpUsers as $u) {
                Notification::create([
                    'user_id' => $u->id,
                    'title' => 'NILAI TERPUBLISH',
                    'message' => 'Hasil penilaian kuesioner Anda sudah dipublish dan dapat dilihat.',
                ]);
            }

            // NOTIFIKASI KE SUPER ADMIN
            $superAdmins = User::role('Super Admin')->get();
            foreach ($superAdmins as $sa) {
                Notification::create([
                    'user_id' => $sa->id,
                       'title' => 'PUBLISH NILAI',
                    'message' => "Admin mempublish nilai untuk Badan Publik {$publicBody->nama_badan}.",
                ]);
            }
        }

        $status = $penilaian->is_published ? 'dipublish' : 'di-unpublish';
        return back()->with('success', "Nilai berhasil {$status}.");
    }

    /**
     * Halaman verifikasi jawaban per badan publik
     */
    public function verifikasiPage($publicBodyId)
    {
        $admin = Auth::user();

        if (!$admin->hasRole('Super Admin') && !$admin->publicBodies->contains($publicBodyId)) {
            abort(403, 'Anda tidak memiliki akses ke badan publik ini.');
        }

        $publicBody = PublicBody::with('kategori')->findOrFail($publicBodyId);
        $kategori   = $publicBody->kategori;

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

        // Ambil pertanyaan per indikator (hierarki)
        $pertanyaanPerIndikator = [];
        foreach ($indikators as $ind) {
            $pertanyaans = Pertanyaan::where('indikator_id', $ind->id)
                ->where('level', 'judul')
                ->with('childrenRecursive')
                ->orderBy('nomor')
                ->get();
            $pertanyaanPerIndikator[$ind->id] = $pertanyaans;
        }

        // Ambil jawaban
        $jawabans = Jawaban::with('verifikator')->where('public_body_id', $publicBody->id)
            ->where('tahun_id', $tahun->id)
            ->get()
            ->keyBy('pertanyaan_id');

        // Ambil user badan publik (responden)
        $userBp = $publicBody->users()->role('Badan Publik')->first();

        // Ambil data penilaian (untuk status publish)
        $penilaian = Penilaian::where('public_body_id', $publicBody->id)
            ->where('tahun_id', $tahun?->id)
            ->first();

        return view('admin.verifikasi', compact(
            'admin', 'publicBody', 'kategori', 'tahun',
            'indikators', 'pertanyaanPerIndikator', 'jawabans', 'userBp', 'penilaian'
        ));
    }

    /**
     * Auto-save verifikasi (AJAX)
     */
    public function autoSaveVerifikasi(Request $request)
    {
        if (!$request->ajax() && !$request->wantsJson()) {
            return response()->json(['status' => 'error', 'message' => 'Bukan request AJAX.'], 400);
        }

        $admin = Auth::user();
        $publicBodyId = $request->input('public_body_id');

        if (!$admin->hasRole('Super Admin') && !$admin->publicBodies->contains($publicBodyId)) {
            return response()->json(['status' => 'error', 'message' => 'Anda tidak memiliki akses ke badan publik ini.'], 403);
        }

        $verifikasi   = $request->input('verifikasi', []);
        $catatan      = $request->input('catatan', []);

        $tahunSekarang = now()->year;
        $tahun = Tahun::where('tahun', $tahunSekarang)->first();

        $allPertanyaanIds = array_unique(array_merge(array_keys($verifikasi), array_keys($catatan)));

        $count = 0;
        foreach ($allPertanyaanIds as $pertanyaanId) {
            $jawaban = Jawaban::where('pertanyaan_id', $pertanyaanId)
                ->where('public_body_id', $publicBodyId)
                ->where('tahun_id', $tahun?->id)
                ->first();

            if (!$jawaban) continue;

            // Update is_verified if present in request
            if (isset($verifikasi[$pertanyaanId])) {
                $isVerified = $verifikasi[$pertanyaanId];
                $jawaban->is_verified = ($isVerified === '1' || $isVerified === 1) ? true : (($isVerified === '0' || $isVerified === 0) ? false : null);
            }
            
            // Update catatan if present in request
            if (isset($catatan[$pertanyaanId])) {
                $jawaban->catatan_verifikasi = $catatan[$pertanyaanId];
            }

            $jawaban->verified_by = $admin->id;
            $jawaban->verified_at = now();
            $jawaban->save();
            $count++;
        }

        return response()->json([
            'status'  => 'success',
            'message' => "Auto-save berhasil ({$count} jawaban).",
        ]);
    }

    /**
     * Simpan verifikasi (full submit)
     */
    public function simpanVerifikasi(Request $request, $publicBodyId)
    {
        $admin = Auth::user();

        $publicBodyId = $publicBodyId; // passed via URL
        if (!$admin->hasRole('Super Admin') && !$admin->publicBodies->contains($publicBodyId)) {
            abort(403, 'Anda tidak memiliki akses ke badan publik ini.');
        }

        $verifikasi   = $request->input('verifikasi', []);
        $catatan      = $request->input('catatan', []);
        
        $tahunSekarang = now()->year;
        $tahun = Tahun::where('tahun', $tahunSekarang)->first();

        $allPertanyaanIds = array_unique(array_merge(array_keys($verifikasi), array_keys($catatan)));

        foreach ($allPertanyaanIds as $pertanyaanId) {
            $jawaban = Jawaban::where('pertanyaan_id', $pertanyaanId)
                ->where('public_body_id', $publicBodyId)
                ->where('tahun_id', $tahun?->id)
                ->first();
                
            if (!$jawaban) continue;

            if (isset($verifikasi[$pertanyaanId])) {
                $isVerified = $verifikasi[$pertanyaanId];
                $jawaban->is_verified = ($isVerified === '1' || $isVerified === 1) ? true : (($isVerified === '0' || $isVerified === 0) ? false : null);
            }

            if (isset($catatan[$pertanyaanId])) {
                $jawaban->catatan_verifikasi = $catatan[$pertanyaanId];
            }

            $jawaban->verified_by = $admin->id;
            $jawaban->verified_at = now();
            $jawaban->save();
        }

        return redirect()
            ->route('admin.list-akun', $request->input('kategori_id'))
            ->with('success', 'Verifikasi berhasil disimpan.');
    }

    public function create()
    {
        //
    }

    /**
     * FIX 1: Hapus validasi wajib public_body_ids agar verifikator bisa
     *         dibuat tanpa badan publik dulu (set belakangan lewat modal Set).
     *         Jika ingin tetap wajib, tambahkan field hidden / multi-select
     *         di modal Tambah juga.
     */
    public function store(Request $request)
    {
        $isEmail = filter_var($request->username_email, FILTER_VALIDATE_EMAIL);

        $request->validate([
            'name'           => 'required|string|max:255',
            'username_email' => [
                'required',
                'string',
                'max:100',
                'unique:users,username',
                'unique:users,email',
                $isEmail ? 'email' : '',
            ],
            'password'       => 'required|min:6',
        ], [
            'username_email.unique' => 'Username atau Email sudah terdaftar.',
            'username_email.email'  => 'Format email tidak valid.',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'telepon'  => $request->telepon,
            'username' => $request->username_email,
            'email'    => $isEmail ? $request->username_email : null,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole('Admin');

        // Sync badan publik hanya jika dikirim
        if ($request->filled('public_body_ids')) {
            $user->publicBodies()->sync($request->public_body_ids);
        }

        return back()->with('success', 'Berhasil tambah verifikator');
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        //
    }

    /**
     * FIX 2: Inisialisasi $data sebelum digunakan, hapus validasi duplikat,
     *         dan sertakan name + telepon dalam update.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if ($user->hasRole('Super Admin')) {
            $request->validate([
                'password' => 'required|min:6|confirmed',
            ]);

            $user->update([
                'password' => Hash::make($request->password),
            ]);

            return back()->with('success', 'Password Super Admin berhasil diperbarui');
        }

        $isEmail = filter_var($request->username_email, FILTER_VALIDATE_EMAIL);

        $request->validate([
            'name'           => 'nullable|string|max:255',
            'telepon'        => 'nullable|string|max:20',
            'username_email' => [
                'required',
                'string',
                'max:100',
                'unique:users,username,' . $id,
                'unique:users,email,' . $id,
                $isEmail ? 'email' : '',
            ],
            'password'       => 'nullable|min:6|confirmed',
        ], [
            'username_email.unique' => 'Username atau Email sudah terdaftar.',
            'username_email.email'  => 'Format email tidak valid.',
        ]);

        // Inisialisasi $data dengan field yang boleh diupdate
        $data = [
            'name'     => $request->name,
            'telepon'  => $request->telepon,
            'username' => $request->username_email,
            'email'    => $isEmail ? $request->username_email : null,
        ];

        // Hanya update password jika diisi
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        // Sync badan publik hanya jika dikirim
        if ($request->filled('public_body_ids')) {
            $user->publicBodies()->sync($request->public_body_ids);
        }

        return back()->with('success', 'Berhasil update');
    }

    /**
     * FIX 3: Gunakan sync() — sudah benar. Masalah double entry ada di
     *         frontend (filterBodies tidak dipanggil), bukan di sini.
     */
    public function setPublicBody(Request $request, $id)
    {
        $request->validate([
            'public_body_ids' => 'nullable|array',
        ]);

        $user = User::findOrFail($id);

        // Jika tidak ada yang dipilih (dikosongkan), sync dengan array kosong
        $user->publicBodies()->sync($request->input('public_body_ids', []));

        return back()->with('success', 'Badan publik berhasil diperbarui');
    }

    /**
     * Export rekap nilai per kategori (sudah & belum mengisi) ke Excel XLSX
     */
    public function exportListAkun($kategoriId)
    {
        $admin = Auth::user();

        $kategori = Kategori::findOrFail($kategoriId);

        $tahunSekarang = now()->year;
        $tahun = Tahun::where('tahun', $tahunSekarang)->first();

        if (!$tahun) {
            return back()->with('error', 'Tahun aktif tidak ditemukan.');
        }

        // Ambil indikator untuk kategori ini
        $indikators = Indikator::where('tahun_id', $tahun->id)
            ->where('kategori_id', $kategoriId)
            ->orderBy('no')
            ->get();

        // Cari ID badan publik yang sudah submit
        $bodiesSudahSubmitIds = Jawaban::where('tahun_id', $tahun->id)
            ->where('is_submitted', true)
            ->pluck('public_body_id')
            ->unique();

        // ID Badan Publik yang di-assign ke admin ini
        $assignedBodiesIds = $admin->hasRole('Super Admin')
            ? PublicBody::where('kategori_id', $kategoriId)->pluck('id')
            : $admin->publicBodies()->pluck('public_bodies.id');

        // Ambil semua public bodies di kategori ini (untuk auto-submit)
        $allBodies = PublicBody::where('kategori_id', $kategoriId)
            ->whereIn('id', $assignedBodiesIds)
            ->get();

        // FITUR AUTO SUBMIT
        $tenggat = Tenggat::where('kategori_id', $kategoriId)->first();
        if ($tenggat && now()->gt($tenggat->waktu_nonaktif)) {
            Jawaban::where('tahun_id', $tahun->id)
                ->whereIn('public_body_id', $allBodies->pluck('id'))
                ->where('is_submitted', false)
                ->update([
                    'is_submitted' => true,
                    'submitted_at' => $tenggat->waktu_nonaktif
                ]);

            $bodiesSudahSubmitIds = Jawaban::where('tahun_id', $tahun->id)
                ->where('is_submitted', true)
                ->pluck('public_body_id')
                ->unique();
        }

        // 1. Sudah Submit
        $bodiesMengisiRaw = PublicBody::where('kategori_id', $kategoriId)
            ->whereIn('id', $assignedBodiesIds)
            ->whereIn('id', $bodiesSudahSubmitIds)
            ->get();

        // 2. Belum Submit
        $bodiesTidakMengisiRaw = PublicBody::where('kategori_id', $kategoriId)
            ->whereIn('id', $assignedBodiesIds)
            ->whereNotIn('id', $bodiesSudahSubmitIds)
            ->get();

        // --- Fungsi proses data ---
        $processData = function ($bodies, $status) use ($tahun, $indikators) {
            $data = [];
            $no = 1;
            foreach ($bodies as $body) {
                $userBp = $body->users()->role('Badan Publik')->first();
                $namaResponden = $userBp->nama_responden ?? '-';

                $nilaiPerIndikator = [];
                $totalNilaiKuesioner = 0;

                foreach ($indikators as $ind) {
                    $pertanyaanIds = Pertanyaan::where('level', 'pertanyaan')
                        ->where('indikator_id', $ind->id)
                        ->pluck('id');

                    $jawabans = Jawaban::where('public_body_id', $body->id)
                        ->where('tahun_id', $tahun->id)
                        ->whereIn('pertanyaan_id', $pertanyaanIds)
                        ->get();

                    $totalBobot = Pertanyaan::whereIn('id', $pertanyaanIds)->sum('bobot');

                    $bobotYa = Pertanyaan::whereIn('id',
                        $jawabans->filter(fn($j) => ($j->jawaban == 1 && $j->is_verified !== false) || ($j->jawaban == 0 && $j->is_verified === true))
                            ->pluck('pertanyaan_id')
                    )->sum('bobot');

                    $nilaiIndikator = $totalBobot > 0
                        ? round(($bobotYa / $totalBobot) * $ind->bobot, 2)
                        : 0;

                    $nilaiPerIndikator[$ind->id] = $nilaiIndikator;
                    $totalNilaiKuesioner += $nilaiIndikator;
                }

                $penilaian = Penilaian::where('public_body_id', $body->id)
                    ->where('tahun_id', $tahun->id)
                    ->first();
                $nilaiPresentasi = $penilaian->nilai_presentasi ?? null;

                $totalScore = $nilaiPresentasi !== null
                    ? round(($totalNilaiKuesioner * 0.7) + ($nilaiPresentasi * 0.3), 2)
                    : null;

                // Status detail untuk "belum mengisi"
                if ($status === 'tidak') {
                    $hasAnyJawaban = collect($nilaiPerIndikator)->sum() > 0;
                    $statusLabel = $hasAnyJawaban ? 'Sedang Mengisi' : 'Belum Mulai';
                } else {
                    $statusLabel = 'Sudah Submit';
                }

                $data[] = [
                    'no'                  => $no++,
                    'nama_badan'          => $body->nama_badan,
                    'nama_responden'      => $namaResponden,
                    'status'              => $statusLabel,
                    'nilai_per_indikator' => $nilaiPerIndikator,
                    'total_kuesioner'     => round($totalNilaiKuesioner, 2),
                    'nilai_presentasi'    => $nilaiPresentasi,
                    'total_score'         => $totalScore,
                ];
            }
            return $data;
        };

        $rowsMengisi = $processData($bodiesMengisiRaw, 'mengisi');
        $rowsTidak   = $processData($bodiesTidakMengisiRaw, 'tidak');

        $type = request('type', 'all');

        // Serialisasi ke JSON untuk Python
        $dataJson = json_encode([
            'verifikator_name' => $admin->name ?? $admin->username,
            'tahun'            => $tahunSekarang,
            'tanggal_cetak'    => now()->translatedFormat('d F Y'),
            'kategori'         => $kategori->name,
            'export_type'      => $type,
            'indikators'       => $indikators->map(fn($i) => [
                'id'             => $i->id,
                'no'             => $i->no,
                'nama_indikator' => strtoupper($i->nama_indikator),
                'bobot'          => $i->bobot,
            ])->values()->toArray(),
            'rows_mengisi' => ($type === 'all' || $type === 'mengisi') ? $rowsMengisi : [],
            'rows_tidak'   => ($type === 'all' || $type === 'tidak') ? $rowsTidak : [],
        ]);

        $tmpJson = tempnam(sys_get_temp_dir(), 'monev_list_') . '.json';
        $tmpXlsx = tempnam(sys_get_temp_dir(), 'monev_list_') . '.xlsx';

        file_put_contents($tmpJson, $dataJson);

        $scriptPath = base_path('app/Console/Scripts/generate_list_akun_excel.py');
        $pythonBin  = PHP_OS_FAMILY === 'Windows' ? 'python' : 'python3';
        $cmd = $pythonBin . ' ' . escapeshellarg($scriptPath)
             . ' ' . escapeshellarg($tmpJson)
             . ' ' . escapeshellarg($tmpXlsx);

        exec($cmd, $output, $exitCode);

        @unlink($tmpJson);

        if ($exitCode !== 0 || !file_exists($tmpXlsx)) {
            return back()->with('error', 'Gagal membuat file Excel: ' . implode("\n", $output));
        }

        $safeKategori = preg_replace('/[\/\\\\]/', '-', $kategori->name);
        $namaFile = 'Rekap_Nilai_' . $safeKategori . '_' . $tahunSekarang . '.xlsx';

        return Response::download($tmpXlsx, $namaFile, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        $user->syncRoles([]);
        $user->delete();

        return back()->with('success', 'Berhasil hapus');
    }
}