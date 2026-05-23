<?php

namespace App\Http\Controllers;

use App\Models\Pertanyaan;
use App\Models\Tahun;
use App\Models\Kategori;
use App\Models\Indikator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class ImportPertanyaanController extends Controller
{
    public function downloadTemplate()
    {
        $tahuns     = Tahun::orderBy('tahun')->get();
        $kategoris  = Kategori::orderBy('name')->get();
        $indikators = Indikator::with([
            'tahun:id,tahun',
            'kategori:id,name',
        ])->orderBy('nama_indikator')->get();
 
        $spreadsheet = new Spreadsheet();
 
        /*
        |--------------------------------------------------------------------------
        | SHEET REFERENSI (hidden)
        |--------------------------------------------------------------------------
        */
        $refSheet = $spreadsheet->getActiveSheet();
        $refSheet->setTitle('Referensi');
 
        // Kolom A — Tahun
        foreach ($tahuns as $i => $tahun) {
            $refSheet->setCellValue('A' . ($i + 1), $tahun->tahun);
        }
 
        // Kolom B — Kategori
        foreach ($kategoris as $i => $kategori) {
            $refSheet->setCellValue('B' . ($i + 1), $kategori->name);
        }
 
        // Kolom C — Indikator (format: "NAMA | Tahun: X | Kategori: Y")
        foreach ($indikators as $i => $indikator) {
            $text = $indikator->nama_indikator
                . ' | Tahun: '    . $indikator->tahun?->tahun
                . ' | Kategori: ' . $indikator->kategori?->name;
 
            $refSheet->setCellValue('C' . ($i + 1), $text);
        }
 
        // Kolom D — Level
        $levels = ['judul', 'subjudul', 'pertanyaan'];
        foreach ($levels as $i => $level) {
            $refSheet->setCellValue('D' . ($i + 1), $level);
        }
 
        $refSheet->setSheetState(Worksheet::SHEETSTATE_HIDDEN);
 
        /*
        |--------------------------------------------------------------------------
        | SHEET IMPORT
        |--------------------------------------------------------------------------
        */
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Import Pertanyaan');
 
        $headers = [
            'A' => 'Tahun *',
            'B' => 'Kategori *',
            'C' => 'Indikator *',
            'D' => 'Level *',
            'E' => 'Parent Nomor',
            'F' => 'Nomor (Opsional)',
            'G' => 'Pertanyaan / Judul *',
            'H' => 'Bobot',
        ];
 
        foreach ($headers as $col => $text) {
            $sheet->setCellValue($col . '1', $text);
            $sheet->getStyle($col . '1')->applyFromArray([
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '991B1B']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ]);
        }
 
        $sheet->getColumnDimension('A')->setWidth(14);
        $sheet->getColumnDimension('B')->setWidth(28);
        $sheet->getColumnDimension('C')->setWidth(45);
        $sheet->getColumnDimension('D')->setWidth(18);
        $sheet->getColumnDimension('E')->setWidth(18);
        $sheet->getColumnDimension('F')->setWidth(18);
        $sheet->getColumnDimension('G')->setWidth(60);
        $sheet->getColumnDimension('H')->setWidth(12);
 
        /*
        |--------------------------------------------------------------------------
        | CONTOH DATA (baris 2–4)
        |--------------------------------------------------------------------------
        */
        $firstTahun     = $tahuns->first();
        $firstKategori  = $kategoris->first();
        $firstIndikator = $indikators->first();
 
        $indikatorText = $firstIndikator
            ? ($firstIndikator->nama_indikator
                . ' | Tahun: '    . $firstIndikator->tahun?->tahun
                . ' | Kategori: ' . $firstIndikator->kategori?->name)
            : 'Indikator';
 
        // Baris 2 — judul
        $sheet->setCellValue('A2', $firstTahun?->tahun    ?? 2026);
        $sheet->setCellValue('B2', $firstKategori?->name  ?? 'Kategori');
        $sheet->setCellValue('C2', $indikatorText);
        $sheet->setCellValue('D2', 'judul');
        $sheet->setCellValue('F2', 'I');
        $sheet->setCellValue('G2', 'TATA KELOLA ORGANISASI');
 
        // Baris 3 — subjudul
        $sheet->setCellValue('A3', $sheet->getCell('A2')->getValue());
        $sheet->setCellValue('B3', $sheet->getCell('B2')->getValue());
        $sheet->setCellValue('C3', $sheet->getCell('C2')->getValue());
        $sheet->setCellValue('D3', 'subjudul');
        $sheet->setCellValue('E3', 'I');
        $sheet->setCellValue('F3', 'A');
        $sheet->setCellValue('G3', 'Struktur Organisasi');
 
        // Baris 4 — pertanyaan
        $sheet->setCellValue('A4', $sheet->getCell('A2')->getValue());
        $sheet->setCellValue('B4', $sheet->getCell('B2')->getValue());
        $sheet->setCellValue('C4', $sheet->getCell('C2')->getValue());
        $sheet->setCellValue('D4', 'pertanyaan');
        $sheet->setCellValue('E4', 'A');
        $sheet->setCellValue('F4', '1');
        $sheet->setCellValue('G4', 'Apakah terdapat struktur organisasi yang jelas?');
        $sheet->setCellValue('H4', 100);
 
        /*
        |--------------------------------------------------------------------------
        | DROPDOWN VALIDATION (baris 2–500)
        |--------------------------------------------------------------------------
        */
        $maxRow = 500;
 
        for ($row = 2; $row <= $maxRow; $row++) {
 
            // Tahun
            $tv = $sheet->getCell("A{$row}")->getDataValidation();
            $tv->setType(DataValidation::TYPE_LIST);
            $tv->setShowDropDown(true);
            $tv->setFormula1("='Referensi'!\$A\$1:\$A\$" . max(1, $tahuns->count()));
 
            // Kategori
            $kv = $sheet->getCell("B{$row}")->getDataValidation();
            $kv->setType(DataValidation::TYPE_LIST);
            $kv->setShowDropDown(true);
            $kv->setFormula1("='Referensi'!\$B\$1:\$B\$" . max(1, $kategoris->count()));
 
            // Indikator
            $iv = $sheet->getCell("C{$row}")->getDataValidation();
            $iv->setType(DataValidation::TYPE_LIST);
            $iv->setShowDropDown(true);
            $iv->setFormula1("='Referensi'!\$C\$1:\$C\$" . max(1, $indikators->count()));
 
            // Level
            $lv = $sheet->getCell("D{$row}")->getDataValidation();
            $lv->setType(DataValidation::TYPE_LIST);
            $lv->setShowDropDown(true);
            $lv->setFormula1("='Referensi'!\$D\$1:\$D\$4");
 
            // Bobot
            $bv = $sheet->getCell("H{$row}")->getDataValidation();
            $bv->setType(DataValidation::TYPE_WHOLE);
            $bv->setOperator(DataValidation::OPERATOR_BETWEEN);
            $bv->setFormula1(0);
            $bv->setFormula2(100);
        }
 
        /*
        |--------------------------------------------------------------------------
        | SHEET PANDUAN
        |--------------------------------------------------------------------------
        */
        $guide = $spreadsheet->createSheet();
        $guide->setTitle('Panduan');
 
        $guide->setCellValue('A1', 'PANDUAN IMPORT PERTANYAAN');
        $guide->getStyle('A1')->applyFromArray(['font' => ['bold' => true, 'size' => 14]]);
 
        $guides = [
            ['KOLOM',         'KETERANGAN'],
            ['Tahun',         'Pilih dari dropdown'],
            ['Kategori',      'Pilih dari dropdown'],
            ['Indikator',     'Pilih dari dropdown — format: "NAMA | Tahun: X | Kategori: Y"'],
            ['Level',         'judul / subjudul / pertanyaan'],
            ['Parent Nomor',  'Subjudul wajib parent Judul, Pertanyaan wajib parent Subjudul'],
            ['Nomor',         'Boleh kosong, otomatis generate'],
            ['Bobot',         'Wajib untuk level pertanyaan (0–100, total per indikator = 100)'],
        ];
 
        foreach ($guides as $rowIndex => $rowData) {
            foreach ($rowData as $colIndex => $value) {
                $guide->setCellValueByColumnAndRow($colIndex + 1, $rowIndex + 3, $value);
            }
        }
 
        $guide->getColumnDimension('A')->setWidth(28);
        $guide->getColumnDimension('B')->setWidth(70);
 
        /*
        |--------------------------------------------------------------------------
        | OUTPUT
        |--------------------------------------------------------------------------
        */
        $spreadsheet->setActiveSheetIndex(1);
 
        $writer = new Xlsx($spreadsheet);
 
        return response()->stream(
            fn () => $writer->save('php://output'),
            200,
            [
                'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="template_import_pertanyaan.xlsx"',
            ]
        );
    }
 
    public function import(Request $request)
    {
        \Log::info('==== IMPORT METHOD TRIGGERED ====', ['files' => $request->file('file_excel') ? 'yes' : 'no']);
 
        $request->validate([
            'file_excel' => 'required|mimes:xlsx,xls|max:5120',
        ]);
 
        DB::beginTransaction();
 
        try {
 
            $spreadsheet = IOFactory::load(
                $request->file('file_excel')->getPathname()
            );
 
            $sheet = $spreadsheet->getSheetByName('Import Pertanyaan');
 
            if (!$sheet) {
                throw new \Exception('Sheet "Import Pertanyaan" tidak ditemukan.');
            }
 
            $rows = $sheet->toArray(null, true, true, true);
 
            $inserted   = 0;
            $errors     = [];
            $warnings   = [];  // peringatan informatif (tidak memblokir import)
            $nomorMap   = [];    // key: "{indikator_id}|{level}|{nomor}"  → id
 
            // Counter auto-numbering
            $judulCounter = [];
            $subCounter   = [];
            $tanyaCounter = [];
 
            // Peta Kolom Default
            $colMap = [
                'tahun'      => 'A',
                'kategori'   => 'B',
                'indikator'  => 'C',
                'level'      => 'D',
                'parent'     => 'E',
                'nomor'      => 'F',
                'pertanyaan' => 'G',
                'bobot'      => 'H',
            ];
 
            // Cari index kolom dinamis berdasarkan header di baris 1
            if (isset($rows[1])) {
                foreach ($rows[1] as $col => $val) {
                    $val = strtolower(trim((string) $val));
                    if (str_contains($val, 'tahun')) $colMap['tahun'] = $col;
                    elseif (str_contains($val, 'kategori')) $colMap['kategori'] = $col;
                    elseif (str_contains($val, 'indikator')) $colMap['indikator'] = $col;
                    elseif (str_contains($val, 'level')) $colMap['level'] = $col;
                    elseif (str_contains($val, 'parent')) $colMap['parent'] = $col;
                    elseif (str_contains($val, 'nomor') && !str_contains($val, 'parent')) $colMap['nomor'] = $col;
                    elseif (str_contains($val, 'pertanyaan') || str_contains($val, 'judul')) $colMap['pertanyaan'] = $col;
                    elseif (str_contains($val, 'bobot')) $colMap['bobot'] = $col;
                }
            }
 
            foreach ($rows as $rowNum => $row) {
 
                // Lewati header
                if ($rowNum <= 1) {
                    continue;
                }
 
                // ----------------------------------------------------------------
                // AMBIL & NORMALKAN NILAI DARI CELL
                // ----------------------------------------------------------------
 
                // Kolom Dinamis
                $tahunRaw  = $row[$colMap['tahun']] ?? '';
                $tahun     = (int) trim((string) $tahunRaw);
 
                $kategori  = trim((string) ($row[$colMap['kategori']] ?? ''));
 
                $indikatorRaw  = trim((string) ($row[$colMap['indikator']] ?? ''));
                $indikatorNama = trim(explode(' | Tahun:', $indikatorRaw)[0]);
 
                $level         = strtolower(trim((string) ($row[$colMap['level']] ?? '')));
 
                $parentNomor   = trim((string) ($row[$colMap['parent']] ?? ''));
 
                $nomor         = trim((string) ($row[$colMap['nomor']] ?? ''));
 
                $pertanyaan    = trim((string) ($row[$colMap['pertanyaan']] ?? ''));
 
                $bobot         = trim((string) ($row[$colMap['bobot']] ?? ''));
 
                // Lewati baris kosong
                if (!$tahun && !$kategori && !$indikatorNama && !$pertanyaan) {
                    continue;
                }
                
                \Log::info("Row {$rowNum} extracted data", [
                    'tahun' => $tahun,
                    'kategori' => $kategori,
                    'indikatorNama' => $indikatorNama,
                    'level' => $level,
                    'parentNomor' => $parentNomor,
                    'nomor' => $nomor,
                    'pertanyaan' => $pertanyaan,
                    'bobot' => $bobot,
                ]);
 
                // ----------------------------------------------------------------
                // VALIDASI FIELD WAJIB
                // ----------------------------------------------------------------
                if (!$tahun || !$kategori || !$indikatorNama || !$level || !$pertanyaan) {
                    $errors[] = "Baris {$rowNum}: field wajib belum lengkap.";
                    continue;
                }
 
                // ----------------------------------------------------------------
                // CARI MODEL TAHUN
                // ----------------------------------------------------------------
                $tahunModel = Tahun::where('tahun', $tahun)->first();
 
                if (!$tahunModel) {
                    $errors[] = "Baris {$rowNum}: tahun '{$tahun}' tidak ditemukan di database.";
                    continue;
                }
 
                // ----------------------------------------------------------------
                // CARI MODEL KATEGORI
                // ----------------------------------------------------------------
                $kategoriModel = Kategori::where('name', $kategori)->first();
 
                if (!$kategoriModel) {
                    $errors[] = "Baris {$rowNum}: kategori '{$kategori}' tidak ditemukan di database.";
                    continue;
                }
 
                // ----------------------------------------------------------------
                // CARI MODEL INDIKATOR
                // Cocokkan nama_indikator + tahun_id + kategori_id
                // ----------------------------------------------------------------
                $indikatorModel = Indikator::where('nama_indikator', $indikatorNama)
                    ->where('tahun_id',    $tahunModel->id)
                    ->where('kategori_id', $kategoriModel->id)
                    ->first();
 
                if (!$indikatorModel) {
                    $errors[] = "Baris {$rowNum}: indikator '{$indikatorNama}' tidak ditemukan untuk tahun {$tahun} dan kategori '{$kategori}'.";
                    continue;
                }
 
                // ----------------------------------------------------------------
                // VALIDASI LEVEL
                // ----------------------------------------------------------------
                if (!in_array($level, ['judul', 'subjudul', 'pertanyaan'])) {
                    $errors[] = "Baris {$rowNum}: level '{$level}' tidak valid. Harus judul, subjudul, atau pertanyaan.";
                    continue;
                }
 
                // ----------------------------------------------------------------
                // AUTO NUMBERING (jika kolom F kosong)
                // ----------------------------------------------------------------
                if ($nomor === '') {
 
                    if ($level === 'judul') {
                        $judulCounter[$indikatorModel->id] =
                            ($judulCounter[$indikatorModel->id] ?? 0) + 1;
                        $nomor = $this->toRoman($judulCounter[$indikatorModel->id]);
                    }
 
                    if ($level === 'subjudul') {
                        $key = $indikatorModel->id . '|' . $parentNomor;
                        $subCounter[$key] = ($subCounter[$key] ?? 0) + 1;
                        $nomor = chr(64 + $subCounter[$key]);
                    }
 
                    if ($level === 'pertanyaan') {
                        $key = $indikatorModel->id . '|' . $parentNomor;
                        $tanyaCounter[$key] = ($tanyaCounter[$key] ?? 0) + 1;
                        $nomor = (string) $tanyaCounter[$key];
                    }
                }
 
                // ----------------------------------------------------------------
                // RESOLUSI PARENT ID
                // ----------------------------------------------------------------
                $parentId = null;
 
                if ($level === 'subjudul') {
 
                    if (!$parentNomor) {
                        $errors[] = "Baris {$rowNum}: subjudul wajib memiliki Parent Nomor (kolom E) yang merujuk ke judul.";
                        continue;
                    }
 
                    $parentKey = $indikatorModel->id . '|judul|' . $parentNomor;
 
                    if (!isset($nomorMap[$parentKey])) {
                        // Coba cari di database juga (kalau sudah ada sebelumnya)
                        $parentDb = Pertanyaan::where('indikator_id', $indikatorModel->id)
                            ->where('level', 'judul')
                            ->where('nomor', $parentNomor)
                            ->first();
 
                        if ($parentDb) {
                            $nomorMap[$parentKey] = $parentDb->id;
                        } else {
                            $errors[] = "Baris {$rowNum}: parent judul dengan nomor '{$parentNomor}' tidak ditemukan. Pastikan baris judul ada di atas baris ini.";
                            continue;
                        }
                    }
 
                    $parentId = $nomorMap[$parentKey];
                }
 
                if ($level === 'pertanyaan') {
 
                    if (!$parentNomor) {
                        $errors[] = "Baris {$rowNum}: pertanyaan wajib memiliki Parent Nomor (kolom E) yang merujuk ke subjudul.";
                        continue;
                    }
 
                    $parentKey = $indikatorModel->id . '|subjudul|' . $parentNomor;
 
                    if (!isset($nomorMap[$parentKey])) {
                        // Coba cari di database juga
                        $parentDb = Pertanyaan::where('indikator_id', $indikatorModel->id)
                            ->where('level', 'subjudul')
                            ->where('nomor', $parentNomor)
                            ->first();
 
                        if ($parentDb) {
                            $nomorMap[$parentKey] = $parentDb->id;
                        } else {
                            $errors[] = "Baris {$rowNum}: parent subjudul dengan nomor '{$parentNomor}' tidak ditemukan. Pastikan baris subjudul ada di atas baris ini.";
                            continue;
                        }
                    }
 
                    $parentId = $nomorMap[$parentKey];
                }
 
                // ----------------------------------------------------------------
                // CEK DUPLIKASI
                // ----------------------------------------------------------------
                $exists = Pertanyaan::where('indikator_id', $indikatorModel->id)
                    ->where('level',     $level)
                    ->where('parent_id', $parentId)
                    ->where('nomor',     $nomor)
                    ->exists();
 
                if ($exists) {
                    $errors[] = "Baris {$rowNum}: nomor '{$nomor}' pada level '{$level}' sudah ada di indikator '{$indikatorNama}'.";
                    continue;
                }
 
                // ----------------------------------------------------------------
                // VALIDASI BOBOT (hanya untuk level pertanyaan)
                // ----------------------------------------------------------------
                $bobotValue = 0;
 
                if ($level === 'pertanyaan') {
 
                    if ($bobot === '' || $bobot === null) {
                        $errors[] = "Baris {$rowNum}: bobot wajib diisi untuk level pertanyaan.";
                        continue;
                    }
 
                    if (!is_numeric($bobot)) {
                        $errors[] = "Baris {$rowNum}: bobot harus berupa angka, ditemukan '{$bobot}'.";
                        continue;
                    }
 
                    $bobotValue = (int) $bobot;
 
                    if ($bobotValue < 0 || $bobotValue > 100) {
                        $errors[] = "Baris {$rowNum}: bobot harus antara 0–100.";
                        continue;
                    }
 
                    // Cek total bobot (yang sudah ada di DB, termasuk yang diinsert di sesi ini)
                    $dbBobot = Pertanyaan::where('indikator_id', $indikatorModel->id)
                        ->where('level', 'pertanyaan')
                        ->sum('bobot');
 
                    if (($dbBobot + $bobotValue) > 100) {
                        // Peringatan saja — data tetap dimasukkan.
                        // Sistem akan menghitung maksimal 100 poin saat rekap nilai.
                        $warnings[] = "⚠️ Info Baris {$rowNum}: total bobot indikator '{$indikatorNama}' melewati 100 (sudah ada: {$dbBobot}, ditambah: {$bobotValue}). Data tetap tersimpan.";
                    }
                }
 
                // ----------------------------------------------------------------
                // INSERT
                // ----------------------------------------------------------------
                $data = Pertanyaan::create([
                    'tahun_id'             => $tahunModel->id,
                    'kategori_id'          => $kategoriModel->id,
                    'indikator_id'         => $indikatorModel->id,
                    'level'                => $level,
                    'is_parent'            => in_array($level, ['judul', 'subjudul']),
                    'parent_id'            => $parentId,
                    'nomor'                => $nomor,
                    'pertanyaan_kuisioner' => $pertanyaan,
                    'bobot'                => $bobotValue,
                ]);
 
                // Simpan ke map agar bisa dirujuk sebagai parent di baris berikutnya
                $mapKey = $indikatorModel->id . '|' . $level . '|' . $nomor;
                $nomorMap[$mapKey] = $data->id;
 
                $inserted++;
            }
 
            \Log::info('Import debug completed', [
                'colMap' => $colMap,
                'rows_count' => count($rows),
                'inserted' => $inserted,
                'errors' => $errors
            ]);
 
            DB::commit();
 
            return back()->with([
                'success'       => "Import berhasil. {$inserted} data ditambahkan.",
                'import_errors' => $errors,
                'import_warnings' => $warnings,
            ]);
 
        } catch (\Throwable $e) {
 
            DB::rollBack();
            \Log::error('Import exception: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
 
            return back()->with('error', 'Import gagal: ' . $e->getMessage());
        }
    }
 
    // ----------------------------------------------------------------
    // HELPER: konversi integer ke angka Romawi
    // ----------------------------------------------------------------
    private function toRoman(int $number): string
    {
        $map = [
            1000 => 'M', 900 => 'CM', 500 => 'D', 400 => 'CD',
            100  => 'C', 90  => 'XC', 50  => 'L', 40  => 'XL',
            10   => 'X', 9   => 'IX', 5   => 'V', 4   => 'IV',
            1    => 'I',
        ];
 
        $result = '';
 
        foreach ($map as $value => $roman) {
            while ($number >= $value) {
                $result  .= $roman;
                $number  -= $value;
            }
        }
 
        return $result;
    }
}
