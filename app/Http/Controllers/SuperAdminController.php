<?php

namespace App\Http\Controllers;

use App\Models\User;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
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

        // Ambil tahun aktif (berdasarkan filter atau tahun saat ini)
        if ($selectedTahunId) {
            $tahunAktif = Tahun::find($selectedTahunId);
        } else {
            $tahunAktif = Tahun::where('tahun', now()->year)->first() ?? Tahun::orderBy('tahun', 'desc')->first();
            $selectedTahunId = $tahunAktif?->id;
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
        }])
        ->whereHas('users', function($q) {
            $q->role('Badan Publik');
        })
        ->whereHas('jawabans', function($q) use ($tahunAktif) {
            $q->where('tahun_id', $tahunAktif->id);
        });

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

            // Hitung nilai per indikator
            $nilaiPerIndikatorVerified = [];
            $totalNilaiSAQRaw = 0;       // SAQ raw (jawaban ya saja, belum diverifikasi)
            $totalNilaiSAQVerified = 0;  // SAQ verified (jawaban ya + is_verified === true)

            // Kita butuh semua indikator untuk kategori si body ini
            $bodyIndikators = Indikator::where('tahun_id', $tahunAktif->id)
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
                
                // 1. Raw SAQ (Unverified) - Hanya dari jawaban = 1 (Ya)
                $bobotYaRaw = Pertanyaan::whereIn('id',
                    $jawabans->filter(function($j) {
                        return $j->jawaban == 1;
                    })->pluck('pertanyaan_id')
                )->sum('bobot');

                $nilaiIndikatorRaw = $totalBobotPertanyaan > 0 ? round(($bobotYaRaw / $totalBobotPertanyaan) * $ind->bobot, 2) : 0;
                $totalNilaiSAQRaw += $nilaiIndikatorRaw;

                // 2. Verified SAQ - Jawaban = 1 (Ya) dan is_verified = true
                $bobotYaVerified = Pertanyaan::whereIn('id',
                    $jawabans->filter(function($j) {
                        return $j->jawaban == 1 && $j->is_verified === true;
                    })->pluck('pertanyaan_id')
                )->sum('bobot');

                $nilaiIndikatorVerified = $totalBobotPertanyaan > 0 ? round(($bobotYaVerified / $totalBobotPertanyaan) * $ind->bobot, 2) : 0;
                
                // Simpan nilai terverifikasi per indikator
                $nilaiPerIndikatorVerified[$ind->id] = [
                    'id'    => $ind->id,
                    'no'    => $ind->no,
                    'nama'  => $ind->nama_indikator,
                    'bobot' => $ind->bobot,
                    'nilai' => $nilaiIndikatorVerified
                ];
                
                $totalNilaiSAQVerified += $nilaiIndikatorVerified;
            }

            // Ambil data penilaian (presentasi & publish)
            $penilaian = Penilaian::where('public_body_id', $body->id)
                ->where('tahun_id', $tahunAktif->id)
                ->first();
            
            $nilaiPresentasi = $penilaian->nilai_presentasi ?? null;
            $isPublished     = $penilaian->is_published ?? false;

            // Hitung Total Score (berdasarkan yang sudah diverifikasi admin)
            $totalScore = 0;
            if ($nilaiPresentasi === null) {
                $totalScore = $totalNilaiSAQVerified;
            } else {
                $weightSAQ   = $tahunAktif->bobot_saq / 100;
                $weightPres  = $tahunAktif->bobot_presentasi / 100;
                $totalScore  = round(($totalNilaiSAQVerified * $weightSAQ) + ($nilaiPresentasi * $weightPres), 2);
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
                'nilai_saq'         => round($totalNilaiSAQRaw, 2),
                'nilai_saq_verified'=> round($totalNilaiSAQVerified, 2),
                'nilai_per_ind'     => $nilaiPerIndikatorVerified,
                'nilai_presentasi'  => $nilaiPresentasi,
                'file_bukti'        => $penilaian->file_bukti_presentasi ?? null,
                'total_score'       => $totalScore,
                'kualifikasi'       => $kualifikasi,
                'bg_class'          => $bgClass,
                'is_published'      => $isPublished,
                'waktu_publish'     => $penilaian?->tanggal_publish ? $penilaian->tanggal_publish->format('d/m/Y H:i') : '-',
                'kategori_id'       => $body->kategori_id,
                'body_indikators'   => $bodyIndikators
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
        $kategori   = $publicBody->kategori;
    
        $tahunSekarang = now()->year;
        $tahun = Tahun::where('tahun', $tahunSekarang)->first();
    
        if (!$tahun) {
            return back()->with('error', 'Tahun aktif tidak ditemukan.');
        }
    
        // ── Data ─────────────────────────────────────────────────────────────────
    
        $indikators = Indikator::where('tahun_id', $tahun->id)
            ->where('kategori_id', $kategori->id)
            ->orderBy('no')
            ->get();
    
        // Ambil pertanyaan level "judul" (parent) beserta semua turunannya
        $pertanyaanJudul = Pertanyaan::where('kategori_id', $kategori->id)
            ->where('tahun_id', $tahun->id)
            ->where('level', 'judul')
            ->whereNull('parent_id')
            ->with('childrenRecursive')
            ->orderBy('nomor')
            ->get();
    
        // Flat list pertanyaan (level = 'pertanyaan') untuk nilai total
        $pertanyaansFlat = Pertanyaan::where('kategori_id', $kategori->id)
            ->where('tahun_id', $tahun->id)
            ->where('level', 'pertanyaan')
            ->orderBy('nomor')
            ->get();
    
        $jawabans = Jawaban::where('public_body_id', $publicBody->id)
            ->where('tahun_id', $tahun->id)
            ->get()
            ->keyBy('pertanyaan_id');
    
        // Hitung Nilai Verifikator (persentase jawaban yang diverifikasi "Ya")
        $totalNilaiSAQVerified = 0;

        foreach ($indikators as $ind) {
            $ptnsInd = $pertanyaansFlat->where('indikator_id', $ind->id);
            $pertanyaanIds = $ptnsInd->pluck('id');
            
            $jawabansInd = $jawabans->filter(fn($j) => $pertanyaanIds->contains($j->pertanyaan_id));
            
            $totalBobotPertanyaan = $ptnsInd->sum('bobot');
            
            $bobotYaVerified = $ptnsInd->filter(function($p) use ($jawabans) {
                $j = $jawabans[$p->id] ?? null;
                return $j && $j->jawaban == 1 && $j->is_verified === true;
            })->sum('bobot');
            
            $nilaiInd = $totalBobotPertanyaan > 0
                ? round(($bobotYaVerified / $totalBobotPertanyaan) * $ind->bobot, 2)
                : 0;
            
            $nilaiPerIndikator[$ind->id] = $nilaiInd;
            $totalNilaiSAQVerified += $nilaiInd;
        }

        $nilaiVerifikator = round($totalNilaiSAQVerified, 2);
    
        // Ambil data penilaian (presentasi)
        $penilaian = Penilaian::where('public_body_id', $publicBody->id)
            ->where('tahun_id', $tahun->id)
            ->first();
        
        $nilaiPresentasi = $penilaian->nilai_presentasi ?? 0;

        // Hitung Nilai Akhir
        if ($nilaiPresentasi == 0) {
            $nilaiAkhir = $nilaiVerifikator;
        } else {
            $weightSAQ   = $tahun->bobot_saq / 100;
            $weightPres  = $tahun->bobot_presentasi / 100;
            $nilaiAkhir  = round(($nilaiVerifikator * $weightSAQ) + ($nilaiPresentasi * $weightPres), 2);
        }

        $kualifikasi = $this->getKualifikasi($nilaiAkhir);

        // Nilai per indikator (untuk ringkasan)
        $nilaiPerIndikator = [];
        foreach ($indikators as $ind) {
            $ptnsInd   = $pertanyaansFlat->where('indikator_id', $ind->id);
            $totalInd  = $ptnsInd->count();
            $yaInd     = $ptnsInd->filter(fn($p) => isset($jawabans[$p->id]) && $jawabans[$p->id]->is_verified == 1)->count();
            $nilaiPerIndikator[$ind->id] = $totalInd > 0 ? round(($yaInd / $totalInd) * 100, 2) : 0;
        }
    
        // ── Spreadsheet ──────────────────────────────────────────────────────────
    
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Worksheet');
    
        // Warna
        $GREY   = 'FF808080'; // header tabel
        $YELLOW = 'FFFFFF00'; // baris sub-judul (roman / huruf)
    
        // Helper: terapkan style ke range
        $applyStyle = function (string $range, array $style) use ($sheet) {
            $sheet->getStyle($range)->applyFromArray($style);
        };
    
        // Helper: merge & tulis
        $mergeWrite = function (string $from, string $to, $value, array $style = []) use ($sheet, $applyStyle) {
            $sheet->mergeCells("{$from}:{$to}");
            $sheet->getCell($from)->setValue($value);
            if ($style) {
                $applyStyle("{$from}:{$to}", $style);
            }
        };
    
        // ── Lebar kolom (sama dengan template) ───────────────────────────────────
        $sheet->getColumnDimension('A')->setWidth(53.7);
        $sheet->getColumnDimension('B')->setWidth(60.0);
        $sheet->getColumnDimension('C')->setWidth(14.57);
        $sheet->getColumnDimension('D')->setWidth(40.0);
        $sheet->getColumnDimension('E')->setWidth(40.0);
        $sheet->getColumnDimension('F')->setWidth(19.14);
        $sheet->getColumnDimension('G')->setWidth(14.57);
        $sheet->getColumnDimension('H')->setWidth(29.71);
        $sheet->getColumnDimension('I')->setWidth(35.85);
    
        // ── Baris 1: Judul utama ─────────────────────────────────────────────────
        $mergeWrite('A1', 'I1', 'LEMBAR HASIL PENILAIAN', [
            'font'      => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
    
        // ── Baris 2: Sub-judul ───────────────────────────────────────────────────
        $judulMonev = 'MONITORING DAN EVALUASI KETERBUKAAN INFORMASI BADAN PUBLIK '
            . strtoupper($publicBody->nama_badan) . ' TAHUN ' . $tahunSekarang;
        $mergeWrite('A2', 'I2', $judulMonev, [
            'font'      => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
    
        // ── Baris 4: Nama Badan Publik ───────────────────────────────────────────
        $mergeWrite('A4', 'I4',
            'BADAN PUBLIK : ' . $publicBody->nama_badan . ' ( ' . strtoupper($kategori->name) . ' )', [
            'font' => ['bold' => true],
        ]);
    
        // ── Baris 5: Tanggal Cetak ───────────────────────────────────────────────
        $mergeWrite('A5', 'I5', 'Tanggal Cetak : ' . now()->format('d F Y'), [
            'font' => ['bold' => true],
        ]);
    
        // ── Baris 8–9: Ringkasan Nilai ───────────────────────────────────────────
        $sheet->getCell('A8')->setValue('Nilai Verifikator');
        $sheet->getCell('B8')->setValue('Nilai Presentasi');
        $sheet->getCell('C8')->setValue('Nilai Akhir');
        $sheet->getCell('D8')->setValue('Kualifikasi');
    
        $sheet->getCell('A9')->setValue($nilaiVerifikator);
        $sheet->getCell('B9')->setValue($nilaiPresentasi);
        $sheet->getCell('C9')->setValue($nilaiAkhir);
        $sheet->getCell('D9')->setValue($kualifikasi);
    
        // ── Baris 10–13: Ringkasan Nilai per Indikator ───────────────────────────
        foreach ($indikators as $i => $ind) {
            $row = 10 + $i;
            $sheet->getCell("A{$row}")->setValue(
                ($i + 1) . ' ' . strtoupper($ind->nama_indikator) . ' : ' . ($nilaiPerIndikator[$ind->id] ?? 0)
            );
        }
    
        // ── Tulis blok setiap indikator ──────────────────────────────────────────
        $currentRow = 15; // mulai setelah baris ringkasan (beri sedikit jarak)
    
        foreach ($indikators as $indIdx => $ind) {
            // ── Baris judul indikator ────────────────────────────────────────────
            $sheet->mergeCells("A{$currentRow}:I{$currentRow}");
            $sheet->getCell("A{$currentRow}")->setValue(
                ($indIdx + 1) . ' ' . strtoupper($ind->nama_indikator)
            );
            $applyStyle("A{$currentRow}:I{$currentRow}", [
                'font'      => ['bold' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            ]);
            $currentRow++;
    
            // ── Baris deskripsi indikator ────────────────────────────────────────
            $sheet->mergeCells("A{$currentRow}:I{$currentRow}");
            $sheet->getCell("A{$currentRow}")->setValue($ind->deskripsi ?? '');
            $applyStyle("A{$currentRow}:I{$currentRow}", [
                'font'      => ['bold' => true],
                'alignment' => ['wrapText' => true],
            ]);
            $sheet->getRowDimension($currentRow)->setRowHeight(-1); // auto height
            $currentRow++;
            $currentRow++; // baris kosong
    
            // ── Header tabel ─────────────────────────────────────────────────────
            $headerRow1 = $currentRow;
            $headerRow2 = $currentRow + 1;
    
            // Row 1 header
            $sheet->getCell("A{$headerRow1}")->setValue('NO');
            $sheet->mergeCells("A{$headerRow1}:A{$headerRow2}");
    
            $sheet->getCell("B{$headerRow1}")->setValue('Pertanyaan');
            $sheet->mergeCells("B{$headerRow1}:B{$headerRow2}");
    
            $sheet->getCell("C{$headerRow1}")->setValue('Jawaban');
            $sheet->mergeCells("C{$headerRow1}:C{$headerRow2}");
    
            $sheet->getCell("D{$headerRow1}")->setValue('Bukti');
            $sheet->mergeCells("D{$headerRow1}:E{$headerRow1}"); // span Link + Dokumen
    
            $sheet->getCell("F{$headerRow1}")->setValue('Verifikasi');
            $sheet->mergeCells("F{$headerRow1}:F{$headerRow2}");
    
            $sheet->getCell("G{$headerRow1}")->setValue('Catatan');
            $sheet->mergeCells("G{$headerRow1}:G{$headerRow2}");
    
            $sheet->getCell("H{$headerRow1}")->setValue('Verifikator Admin');
            $sheet->mergeCells("H{$headerRow1}:H{$headerRow2}");
    
            $sheet->getCell("I{$headerRow1}")->setValue('Verifikator');
            $sheet->mergeCells("I{$headerRow1}:I{$headerRow2}");
    
            // Row 2 header (sub-header Bukti)
            $sheet->getCell("D{$headerRow2}")->setValue('Link');
            $sheet->getCell("E{$headerRow2}")->setValue('Dokumen');
    
            // Style header
            $applyStyle("A{$headerRow1}:I{$headerRow2}", [
                'font' => [
                    'bold'  => true,
                    'color' => ['rgb' => 'FF000000'],
                ],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $GREY],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                    'wrapText'   => true,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color'       => ['rgb' => 'FFCCCCCC'],
                    ],
                ],
            ]);
    
            $currentRow += 2;
    
            // ── Pertanyaan per indikator ──────────────────────────────────────────
            $judul_list = $pertanyaanJudul->where('indikator_id', $ind->id);
    
            foreach ($judul_list as $judul) {
                $currentRow = $this->_writeGroupRows(
                    $sheet,
                    [$judul],
                    $jawabans,
                    $currentRow,
                    $YELLOW,
                    $applyStyle
                );
            }
    
            $currentRow++; // baris kosong antar indikator
        }
    
        // ── Output ───────────────────────────────────────────────────────────────
        $filename = 'Rekap_Detail_Nilai_Verifikator_'
            . str_replace(' ', '_', $publicBody->nama_badan)
            . '_' . $tahunSekarang . '.xlsx';
    
        $writer = new Xlsx($spreadsheet);
    
        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control'       => 'max-age=0',
        ]);
    }
    
    // ── Helper: tulis baris grup (rekursif) ──────────────────────────────────────
    
    private function _writeGroupRows(
        $sheet,
        $items,
        $jawabans,
        int $currentRow,
        string $yellowHex,
        callable $applyStyle
    ): int {
        foreach ($items as $item) {
            if ($item->level === 'pertanyaan') {
                $jawaban = $jawabans[$item->id] ?? null;

                $jawabanText = '-';
                if ($jawaban) {
                    $jawabanText = $jawaban->jawaban == 1 ? 'Ya' : ($jawaban->jawaban == 0 ? 'Tidak' : '-');
                }

                $verifikasiText = 'Belum Diverifikasi';
                if ($jawaban && $jawaban->is_verified !== null) {
                    $verifikasiText = $jawaban->is_verified ? 'Iya' : 'Tidak';
                }

                // ── PERBAIKAN: ambil links (JSON array) dan dokumen_path ──
                $linkText = '';
                if ($jawaban && !empty($jawaban->links)) {
                    $links = is_array($jawaban->links) ? $jawaban->links : json_decode($jawaban->links, true);
                    $linkText = implode("\n", array_filter((array) $links));
                }

                $dokumenText = '';
                if ($jawaban && $jawaban->dokumen_path) {
                    // Ambil nama file saja dari path
                    $dokumenText = basename($jawaban->dokumen_path);
                }

                // ── PERBAIKAN: nama verifikator pakai verified_by ──
                $namaVerifikator = ' ';
                if ($jawaban && $jawaban->verified_by) {
                    $user = \App\Models\User::find($jawaban->verified_by);
                    $namaVerifikator = $user?->name ?? ' ';
                }

                $sheet->getCell("A{$currentRow}")->setValue($item->nomor);
                $sheet->getCell("B{$currentRow}")->setValue($item->pertanyaan_kuisioner);
                $sheet->getCell("C{$currentRow}")->setValue($jawabanText);
                $sheet->getCell("D{$currentRow}")->setValue($linkText);     // link dari JSON
                $sheet->getCell("E{$currentRow}")->setValue($dokumenText);  // nama file dokumen
                $sheet->getCell("F{$currentRow}")->setValue($verifikasiText);
                $sheet->getCell("G{$currentRow}")->setValue($jawaban->catatan_verifikasi ?? ' ');
                $sheet->getCell("H{$currentRow}")->setValue(' ');
                $sheet->getCell("I{$currentRow}")->setValue($namaVerifikator);

                // wrap text untuk kolom D (bisa multi-link)
                $sheet->getStyle("D{$currentRow}")->getAlignment()->setWrapText(true);

                $sheet->getStyle("H{$currentRow}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('808080');

                $applyStyle("A{$currentRow}:I{$currentRow}", [
                    'alignment' => [
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color'       => ['rgb' => 'FFCCCCCC'],
                        ],
                    ],
                ]);

                $sheet->getRowDimension($currentRow)->setRowHeight(-1);
                $currentRow++;

            } else {
                // baris sub-judul — tidak berubah
                $sheet->getCell("A{$currentRow}")->setValue($item->nomor ?? '');
                $sheet->getCell("B{$currentRow}")->setValue($item->pertanyaan_kuisioner ?? '');

                $applyStyle("A{$currentRow}:I{$currentRow}", [
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FFFF00'],
                    ],
                    'alignment' => [
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color'       => ['rgb' => 'FFCCCCCC'],
                        ],
                    ],
                ]);

                $currentRow++;

                if ($item->childrenRecursive && $item->childrenRecursive->count()) {
                    $currentRow = $this->_writeGroupRows(
                        $sheet,
                        $item->childrenRecursive,
                        $jawabans,
                        $currentRow,
                        $yellowHex,
                        $applyStyle
                    );
                }
            }
        }

        return $currentRow;
    }
    
    // ── Helper: tentukan kualifikasi berdasarkan nilai ───────────────────────────
    
    private function getKualifikasi(float $nilai): string
    {
        if ($nilai >= 90) return 'Informatif';
        if ($nilai >= 80) return 'Menuju Informatif';
        if ($nilai >= 60) return 'Cukup Informatif';
        if ($nilai >= 40) return 'Kurang Informatif';
        return 'Tidak Informatif';
    }
}
