<?php

namespace App\Http\Controllers;

use App\Models\Pertanyaan;
use App\Models\Tahun;
use App\Models\Kategori;
use App\Models\Indikator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ImportPertanyaanController extends Controller
{
    // =========================================================
    // DOWNLOAD TEMPLATE
    // =========================================================
    public function downloadTemplate()
    {
        $tahuns     = Tahun::orderBy('tahun')->get();
        $kategoris  = Kategori::orderBy('name')->get();
        $indikators = Indikator::orderBy('nama_indikator')->get();
        $levels     = ['judul', 'subjudul', 'pertanyaan'];

        // Ambil semua pertanyaan level judul & subjudul untuk referensi parent
        $juduls    = Pertanyaan::where('level', 'judul')
                        ->orderBy('nomor')
                        ->get(['id', 'nomor', 'pertanyaan_kuisioner', 'indikator_id']);
        $subjuduls = Pertanyaan::where('level', 'subjudul')
                        ->orderBy('nomor')
                        ->get(['id', 'nomor', 'pertanyaan_kuisioner', 'indikator_id', 'parent_id']);

        $spreadsheet = new Spreadsheet();

        // ─────────────────────────────────────────────────────
        // SHEET 1 — Referensi  (hidden, menyimpan data referensi)
        // ─────────────────────────────────────────────────────
        $ref = $spreadsheet->getActiveSheet();
        $ref->setTitle('Referensi');

        // Kolom A : Tahun
        foreach ($tahuns as $i => $t) {
            $ref->setCellValue('A' . ($i + 1), (string) $t->tahun);
        }

        // Kolom B : Kategori
        foreach ($kategoris as $i => $k) {
            $ref->setCellValue('B' . ($i + 1), $k->name);
        }

        // Kolom C : Indikator
        foreach ($indikators as $i => $ind) {
            $ref->setCellValue('C' . ($i + 1), $ind->nama_indikator);
        }

        // Kolom D : Level
        foreach ($levels as $i => $lv) {
            $ref->setCellValue('D' . ($i + 1), $lv);
        }

        // Kolom E : Judul (untuk referensi dropdown Parent di sheet utama)
        // Format: "NomorJudul - Teks Judul [IndikatorID]"
        foreach ($juduls as $i => $j) {
            $ref->setCellValue('E' . ($i + 1), $j->nomor . ' - ' . $j->pertanyaan_kuisioner);
        }

        // Kolom F : Subjudul (untuk referensi dropdown Parent)
        foreach ($subjuduls as $i => $s) {
            $ref->setCellValue('F' . ($i + 1), $s->nomor . ' - ' . $s->pertanyaan_kuisioner);
        }

        // Panduan penomoran
        $ref->setCellValue('G1', 'judul=angka romawi (I,II,III...)');
        $ref->setCellValue('G2', 'subjudul=huruf besar (A,B,C...)');
        $ref->setCellValue('G3', 'pertanyaan=angka (1,2,3...)');

        $ref->setSheetState(Worksheet::SHEETSTATE_HIDDEN);

        // ─────────────────────────────────────────────────────
        // SHEET 2 — Panduan
        // ─────────────────────────────────────────────────────
        $guide = $spreadsheet->createSheet();
        $guide->setTitle('Panduan');

        $guideContent = [
            ['PANDUAN PENGISIAN TEMPLATE IMPORT PERTANYAAN', ''],
            ['', ''],
            ['KOLOM', 'KETERANGAN'],
            ['Tahun', 'Pilih dari dropdown (data dari database)'],
            ['Kategori', 'Pilih dari dropdown (data dari database)'],
            ['Indikator', 'Pilih dari dropdown (data dari database)'],
            ['Level', 'Pilih: judul / subjudul / pertanyaan'],
            ['Parent Nomor', 'Kosongkan jika level=judul. Untuk subjudul: isi nomor judul parent (mis: I). Untuk pertanyaan: isi nomor subjudul parent (mis: A). Boleh kosong jika pertanyaan langsung di bawah judul.'],
            ['Nomor', 'BOLEH DIKOSONGKAN — sistem akan mengisi otomatis (I, A, 1). Atau isi manual.'],
            ['Pertanyaan / Judul', 'Teks pertanyaan, judul, atau sub judul'],
            ['Bobot', 'Wajib diisi untuk level=pertanyaan. Total per indikator sebaiknya = 100. Kosongkan untuk judul/subjudul.'],
            ['', ''],
            ['URUTAN PENGISIAN (PENTING!)', ''],
            ['', 'Urutan baris HARUS: Judul → Sub Judul → Pertanyaan'],
            ['', 'Sistem membaca dari atas ke bawah, parent harus muncul lebih dulu dari anaknya'],
            ['', ''],
            ['SISTEM PENOMORAN OTOMATIS', ''],
            ['', 'Jika kolom Nomor dikosongkan, sistem akan mengisi otomatis:'],
            ['', '  - Level judul     → I, II, III, IV, ...'],
            ['', '  - Level subjudul  → A, B, C, D, ...  (per judul)'],
            ['', '  - Level pertanyaan→ 1, 2, 3, 4, ...  (per subjudul atau per indikator)'],
            ['', ''],
            ['CONTOH DATA', ''],
            ['Tahun', 'Kategori', 'Indikator', 'Level', 'Parent Nomor', 'Nomor', 'Pertanyaan / Judul', 'Bobot'],
            ['2024', 'Kategori A', 'Indikator 1', 'judul', '', 'I', 'Tata Kelola Organisasi', ''],
            ['2024', 'Kategori A', 'Indikator 1', 'subjudul', 'I', 'A', 'Struktur Organisasi', ''],
            ['2024', 'Kategori A', 'Indikator 1', 'pertanyaan', 'A', '1', 'Apakah terdapat struktur organisasi yang jelas?', '20'],
            ['2024', 'Kategori A', 'Indikator 1', 'pertanyaan', 'A', '2', 'Apakah terdapat uraian tugas yang terdokumentasi?', '30'],
            ['2024', 'Kategori A', 'Indikator 1', 'subjudul', 'I', 'B', 'Kepemimpinan', ''],
            ['2024', 'Kategori A', 'Indikator 1', 'pertanyaan', 'B', '3', 'Apakah pimpinan memberikan arahan yang jelas?', '50'],
            ['', ''],
            ['CATATAN', ''],
            ['', 'Kolom Parent Nomor mengacu pada kolom Nomor baris parent (persis sama, termasuk huruf kapital)'],
            ['', 'Pertanyaan tanpa subjudul: kosongkan Parent Nomor untuk level=pertanyaan'],
            ['', 'Sistem mendeteksi duplikat berdasarkan kombinasi Indikator + Nomor'],
        ];

        foreach ($guideContent as $rowIdx => $rowData) {
            foreach ($rowData as $colIdx => $cellVal) {
                $guide->setCellValue(
                    Coordinate::stringFromColumnIndex($colIdx + 1) . ($rowIdx + 1),
                    $cellVal
                );
            }
        }

        $guide->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'B91C1C']],
        ]);
        $guide->getStyle('A3:B3')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '374151']],
        ]);
        $guide->getStyle('A23:H23')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1D4ED8']],
        ]);
        foreach (range(24, 30) as $idx => $row) {
            $color = ($idx % 2 === 0) ? 'F9FAFB' : 'EFF6FF';
            $guide->getStyle("A{$row}:H{$row}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $color]],
            ]);
        }
        $guide->getColumnDimension('A')->setWidth(20);
        $guide->getColumnDimension('B')->setWidth(65);
        foreach (['C','D','E','F','G','H'] as $col) {
            $guide->getColumnDimension($col)->setWidth(18);
        }

        // ─────────────────────────────────────────────────────
        // SHEET 3 — Import Pertanyaan (sheet utama)
        // ─────────────────────────────────────────────────────
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Import Pertanyaan');
        $spreadsheet->setActiveSheetIndex(2);

        // ── Header ──
        $headers = [
            'A' => ['text' => 'Tahun *',              'width' => 12],
            'B' => ['text' => 'Kategori *',            'width' => 28],
            'C' => ['text' => 'Indikator *',           'width' => 38],
            'D' => ['text' => 'Level *',               'width' => 14],
            'E' => ['text' => 'Parent Nomor',          'width' => 18],
            'F' => ['text' => 'Nomor (opsional)',      'width' => 18],
            'G' => ['text' => 'Pertanyaan / Judul *',  'width' => 55],
            'H' => ['text' => 'Bobot',                 'width' => 10],
        ];

        foreach ($headers as $col => $info) {
            $sheet->setCellValue($col . '1', $info['text']);
            $sheet->getStyle($col . '1')->applyFromArray([
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'B91C1C']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '991B1B']]],
            ]);
            $sheet->getColumnDimension($col)->setWidth($info['width']);
        }
        $sheet->getRowDimension(1)->setRowHeight(28);

        // ── Baris 2 — contoh/placeholder ──
        $exampleRow = [
            'A2' => '(pilih dari dropdown)',
            'B2' => '(pilih dari dropdown)',
            'C2' => '(pilih dari dropdown)',
            'D2' => '(judul / subjudul / pertanyaan)',
            'E2' => '(nomor parent, mis: I atau A)',
            'F2' => '(otomatis jika kosong)',
            'G2' => '(isi teks pertanyaan atau judul)',
            'H2' => '(angka, untuk pertanyaan saja)',
        ];
        foreach ($exampleRow as $cell => $val) {
            $sheet->setCellValue($cell, $val);
            $sheet->getStyle($cell)->applyFromArray([
                'font'      => ['italic' => true, 'color' => ['rgb' => '9CA3AF'], 'size' => 9],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F9FAFB']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            ]);
        }

        // Komentar panduan di header
        $note  = "Panduan:\n";
        $note .= "- Urutan: Judul → Sub Judul → Pertanyaan\n";
        $note .= "- Kolom Nomor otomatis: I/II/III (judul), A/B/C (subjudul), 1/2/3 (pertanyaan)\n";
        $note .= "- Parent Nomor: isi nomor parent persis seperti kolom Nomor baris parent\n";
        $note .= "- Bobot: wajib untuk level=pertanyaan, total per indikator = 100";
        $sheet->getComment('A1')->getText()->createTextRun($note);
        $sheet->getComment('A1')->setWidth('420pt');
        $sheet->getComment('A1')->setHeight('130pt');

        // ── Dropdown validasi untuk baris 3–500 ──
        $maxRow = 500;

        $tahunCount    = max(1, count($tahuns));
        $kategoriCount = max(1, count($kategoris));
        $indikCount    = max(1, count($indikators));
        $levelCount    = max(1, count($levels));
        $judulCount    = max(1, count($juduls));

        // Apakah ada data judul/subjudul di Referensi ?
        $hasJudul    = count($juduls) > 0;
        $hasSubjudul = count($subjuduls) > 0;

        for ($row = 3; $row <= $maxRow; $row++) {

            // Warna zebra
            if ($row % 2 === 0) {
                $sheet->getStyle("A{$row}:H{$row}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEF2F2']],
                ]);
            }

            // Dropdown Tahun (kolom A)
            $vTahun = $sheet->getCell("A{$row}")->getDataValidation();
            $vTahun->setType(DataValidation::TYPE_LIST)
                   ->setShowErrorMessage(true)
                   ->setErrorTitle('Tahun tidak valid')
                   ->setError('Pilih tahun dari daftar yang tersedia.')
                   ->setFormula1("'Referensi'!\$A\$1:\$A\${$tahunCount}");

            // Dropdown Kategori (kolom B)
            $vKat = $sheet->getCell("B{$row}")->getDataValidation();
            $vKat->setType(DataValidation::TYPE_LIST)
                 ->setShowErrorMessage(true)
                 ->setErrorTitle('Kategori tidak valid')
                 ->setError('Pilih kategori dari daftar yang tersedia.')
                 ->setFormula1("'Referensi'!\$B\$1:\$B\${$kategoriCount}");

            // Dropdown Indikator (kolom C)
            $vInd = $sheet->getCell("C{$row}")->getDataValidation();
            $vInd->setType(DataValidation::TYPE_LIST)
                 ->setShowErrorMessage(true)
                 ->setErrorTitle('Indikator tidak valid')
                 ->setError('Pilih indikator dari daftar yang tersedia.')
                 ->setFormula1("'Referensi'!\$C\$1:\$C\${$indikCount}");

            // Dropdown Level (kolom D)
            $vLvl = $sheet->getCell("D{$row}")->getDataValidation();
            $vLvl->setType(DataValidation::TYPE_LIST)
                 ->setShowErrorMessage(true)
                 ->setErrorTitle('Level tidak valid')
                 ->setError('Pilih: judul, subjudul, atau pertanyaan.')
                 ->setFormula1("'Referensi'!\$D\$1:\$D\${$levelCount}");

            // Dropdown Parent Nomor (kolom E)
            // Menampilkan judul yang sudah ada di database sebagai referensi
            if ($hasJudul) {
                $vParent = $sheet->getCell("E{$row}")->getDataValidation();
                $vParent->setType(DataValidation::TYPE_LIST)
                        ->setShowErrorMessage(false) // Tidak error jika isi manual
                        ->setShowInputMessage(true)
                        ->setPromptTitle('Parent Nomor')
                        ->setPrompt('Pilih dari daftar judul/subjudul yang ada, atau ketik nomor parent secara manual (mis: I, A).')
                        ->setFormula1("'Referensi'!\$E\$1:\$E\${$judulCount}");
            }

            // Validasi Bobot: angka 0–100 (kolom H)
            $vBobot = $sheet->getCell("H{$row}")->getDataValidation();
            $vBobot->setType(DataValidation::TYPE_DECIMAL)
                   ->setOperator(DataValidation::OPERATOR_BETWEEN)
                   ->setShowErrorMessage(true)
                   ->setErrorTitle('Bobot tidak valid')
                   ->setError('Bobot harus angka antara 0 dan 100.')
                   ->setFormula1('0')
                   ->setFormula2('100');
        }

        // Freeze header
        $sheet->freezePane('A3');

        // Border seluruh area data
        $sheet->getStyle("A1:H{$maxRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_HAIR,
                    'color'       => ['rgb' => 'E5E7EB'],
                ],
            ],
        ]);

        // ─────────────────────────────────────────────────────
        // SHEET 4 — Referensi Parent (visible, bantu user)
        // ─────────────────────────────────────────────────────
        if ($hasJudul || $hasSubjudul) {
            $refParent = $spreadsheet->createSheet();
            $refParent->setTitle('Ref Parent');

            // Header
            $refHeaders = ['Nomor', 'Teks Judul / Sub Judul', 'Level', 'Nama Indikator'];
            foreach ($refHeaders as $ci => $h) {
                $col = Coordinate::stringFromColumnIndex($ci + 1);
                $refParent->setCellValue($col . '1', $h);
                $refParent->getStyle($col . '1')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1D4ED8']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
            }

            // Data judul
            $rowRef = 2;
            foreach ($juduls as $j) {
                $indNama = $indikators->firstWhere('id', $j->indikator_id)?->nama_indikator ?? '-';
                $refParent->setCellValue('A' . $rowRef, $j->nomor);
                $refParent->setCellValue('B' . $rowRef, $j->pertanyaan_kuisioner);
                $refParent->setCellValue('C' . $rowRef, 'judul');
                $refParent->setCellValue('D' . $rowRef, $indNama);
                $refParent->getStyle("A{$rowRef}:D{$rowRef}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DBEAFE']],
                    'font' => ['bold' => true],
                ]);
                $rowRef++;

                // Sub judul dari judul ini
                $children = $subjuduls->where('parent_id', $j->id);
                foreach ($children as $s) {
                    $refParent->setCellValue('A' . $rowRef, $s->nomor);
                    $refParent->setCellValue('B' . $rowRef, '   ' . $s->pertanyaan_kuisioner);
                    $refParent->setCellValue('C' . $rowRef, 'subjudul');
                    $refParent->setCellValue('D' . $rowRef, $indNama);
                    $refParent->getStyle("A{$rowRef}:D{$rowRef}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EFF6FF']],
                    ]);
                    $rowRef++;
                }
            }

            $refParent->getColumnDimension('A')->setWidth(12);
            $refParent->getColumnDimension('B')->setWidth(55);
            $refParent->getColumnDimension('C')->setWidth(14);
            $refParent->getColumnDimension('D')->setWidth(35);
            $refParent->freezePane('A2');

            // Keterangan di baris 1 kolom E
            $refParent->setCellValue('F1', '← Gunakan kolom Nomor di sheet Import Pertanyaan kolom E (Parent Nomor)');
            $refParent->getStyle('F1')->applyFromArray([
                'font' => ['italic' => true, 'color' => ['rgb' => 'DC2626']],
            ]);
            $refParent->getColumnDimension('F')->setWidth(55);
        }

        // ─────────────────────────────────────────────────────
        // Output
        // ─────────────────────────────────────────────────────
        $writer   = new Xlsx($spreadsheet);
        $filename = 'template_import_pertanyaan_' . date('Ymd') . '.xlsx';

        return response()->stream(
            fn () => $writer->save('php://output'),
            200,
            [
                'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control'       => 'max-age=0',
            ]
        );
    }

    // =========================================================
    // IMPORT
    // =========================================================
    public function import(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|mimes:xlsx,xls|max:5120',
        ], [
            'file_excel.required' => 'File Excel wajib diunggah.',
            'file_excel.mimes'    => 'File harus berformat .xlsx atau .xls.',
            'file_excel.max'      => 'Ukuran file maksimal 5 MB.',
        ]);

        DB::beginTransaction();

        try {
            $spreadsheet = IOFactory::load(
                $request->file('file_excel')->getPathname()
            );

            $ws = $spreadsheet->getSheetByName('Import Pertanyaan')
                ?? $spreadsheet->getActiveSheet();

            $rows = $ws->toArray(null, true, true, true);

            // ── Mapping database ──────────────────────────────
            $tahunMap = Tahun::all()->mapWithKeys(
                fn ($t) => [strtolower(trim((string) $t->tahun)) => $t->id]
            );
            $kategoriMap = Kategori::all()->mapWithKeys(
                fn ($k) => [strtolower(trim($k->name)) => $k->id]
            );
            $indikMap = Indikator::all()->mapWithKeys(
                fn ($i) => [strtolower(trim($i->nama_indikator)) => $i->id]
            );

            // ── State penomoran otomatis ──────────────────────
            $judulCounter      = [];
            $subjudulCounter   = [];
            $pertanyaanCounter = [];

            // Map nomor → id (untuk relasi parent)
            // key: "indikatorId|nomor_lowercase"
            $nomorMap = [];

            // Seed nomorMap dari data yang sudah ada di DB
            // supaya baris baru bisa merujuk parent yang sudah ada
            $existingJuduls    = Pertanyaan::where('level', 'judul')->get(['id', 'nomor', 'indikator_id']);
            $existingSubjuduls = Pertanyaan::where('level', 'subjudul')->get(['id', 'nomor', 'indikator_id']);
            foreach ($existingJuduls as $ej) {
                $nomorMap["{$ej->indikator_id}|" . strtolower($ej->nomor)] = $ej->id;
            }
            foreach ($existingSubjuduls as $es) {
                $nomorMap["{$es->indikator_id}|" . strtolower($es->nomor)] = $es->id;
            }

            $inserted = 0;
            $errors   = [];

            foreach ($rows as $rowNum => $row) {

                // Skip 2 baris pertama (header + baris placeholder)
                if ($rowNum <= 2) {
                    continue;
                }

                $rawTahun    = trim($row['A'] ?? '');
                $rawKategori = trim($row['B'] ?? '');
                $rawIndik    = trim($row['C'] ?? '');
                $level       = strtolower(trim($row['D'] ?? ''));
                $rawParent   = trim($row['E'] ?? '');
                $nomor       = trim($row['F'] ?? '');
                $teks        = trim($row['G'] ?? '');
                $bobot       = trim($row['H'] ?? '');

                // Skip baris kosong sepenuhnya
                if (!$rawTahun && !$rawKategori && !$rawIndik && !$teks) {
                    continue;
                }

                // ── Normalisasi: jika user memilih dari dropdown "Nomor - Teks", ambil nomornya saja
                // (kolom E bisa berisi "I - Tata Kelola" dari dropdown, ambil bagian sebelum " - ")
                if (str_contains($rawParent, ' - ')) {
                    $rawParent = trim(explode(' - ', $rawParent)[0]);
                }

                // ── Validasi kolom wajib ──────────────────────
                $missingCols = [];
                if (!$rawTahun)    $missingCols[] = 'Tahun';
                if (!$rawKategori) $missingCols[] = 'Kategori';
                if (!$rawIndik)    $missingCols[] = 'Indikator';
                if (!$level)       $missingCols[] = 'Level';
                if (!$teks)        $missingCols[] = 'Pertanyaan/Judul';

                if (!empty($missingCols)) {
                    $errors[] = "Baris {$rowNum}: kolom wajib belum diisi → " . implode(', ', $missingCols) . ".";
                    continue;
                }

                // ── Validasi nilai level ──────────────────────
                if (!in_array($level, ['judul', 'subjudul', 'pertanyaan'])) {
                    $errors[] = "Baris {$rowNum}: level '{$level}' tidak valid. Gunakan: judul / subjudul / pertanyaan.";
                    continue;
                }

                // ── Lookup database ───────────────────────────
                $tahunId = $tahunMap->get(strtolower($rawTahun));
                if (!$tahunId) {
                    $errors[] = "Baris {$rowNum}: tahun '{$rawTahun}' tidak ditemukan di database.";
                    continue;
                }

                $kategoriId = $kategoriMap->get(strtolower($rawKategori));
                if (!$kategoriId) {
                    $errors[] = "Baris {$rowNum}: kategori '{$rawKategori}' tidak ditemukan di database.";
                    continue;
                }

                $indikatorId = $indikMap->get(strtolower($rawIndik));
                if (!$indikatorId) {
                    $errors[] = "Baris {$rowNum}: indikator '{$rawIndik}' tidak ditemukan di database.";
                    continue;
                }

                // ── Penomoran otomatis jika kolom Nomor kosong ─
                if ($nomor === '') {
                    $nomor = $this->generateNomor(
                        $level, $indikatorId, $rawParent,
                        $judulCounter, $subjudulCounter, $pertanyaanCounter
                    );
                } else {
                    $this->syncCounter(
                        $level, $indikatorId, $nomor, $rawParent,
                        $judulCounter, $subjudulCounter, $pertanyaanCounter
                    );
                }

                // ── Relasi parent ─────────────────────────────
                $parentId = null;

                if ($level === 'subjudul') {
                    if (!$rawParent) {
                        $errors[] = "Baris {$rowNum}: subjudul wajib memiliki parent Judul (isi kolom Parent Nomor).";
                        continue;
                    }
                    $mapKey = "{$indikatorId}|" . strtolower($rawParent);
                    if (!isset($nomorMap[$mapKey])) {
                        $errors[] = "Baris {$rowNum}: judul parent '{$rawParent}' belum ditemukan. Pastikan judul muncul SEBELUM subjudul.";
                        continue;
                    }
                    $parentId = $nomorMap[$mapKey];
                }

                if ($level === 'pertanyaan' && $rawParent !== '') {
                    $mapKey = "{$indikatorId}|" . strtolower($rawParent);
                    if (!isset($nomorMap[$mapKey])) {
                        $errors[] = "Baris {$rowNum}: parent '{$rawParent}' belum ditemukan. Pastikan baris parent muncul lebih dulu.";
                        continue;
                    }
                    $parentId = $nomorMap[$mapKey];
                }

                // ── Validasi duplikat ─────────────────────────
                $exists = Pertanyaan::where('indikator_id', $indikatorId)
                    ->where('nomor', $nomor)
                    ->exists();

                if ($exists) {
                    $errors[] = "Baris {$rowNum}: nomor '{$nomor}' pada indikator '{$rawIndik}' sudah ada di database.";
                    continue;
                }

                // ── Validasi bobot ────────────────────────────
                $bobotValue = 0;

                if ($level === 'pertanyaan') {
                    if ($bobot === '') {
                        $errors[] = "Baris {$rowNum}: bobot wajib diisi untuk level pertanyaan.";
                        continue;
                    }
                    if (!is_numeric($bobot)) {
                        $errors[] = "Baris {$rowNum}: bobot harus berupa angka.";
                        continue;
                    }
                    $bobotValue = (float) $bobot;
                    $totalBobot = Pertanyaan::where('indikator_id', $indikatorId)
                                           ->where('level', 'pertanyaan')
                                           ->sum('bobot');
                    if ($totalBobot + $bobotValue > 100) {
                        $errors[] = "Baris {$rowNum}: total bobot indikator '{$rawIndik}' melebihi 100 (saat ini {$totalBobot}, ditambah {$bobotValue}).";
                        continue;
                    }
                }

                // ── Simpan ke database ────────────────────────
                $p = Pertanyaan::create([
                    'tahun_id'             => $tahunId,
                    'kategori_id'          => $kategoriId,
                    'indikator_id'         => $indikatorId,
                    'level'                => $level,
                    'is_parent'            => in_array($level, ['judul', 'subjudul']),
                    'parent_id'            => $parentId,
                    'nomor'                => $nomor,
                    'pertanyaan_kuisioner' => $teks,
                    'bobot'                => $bobotValue,
                ]);

                $nomorMap["{$indikatorId}|" . strtolower($nomor)] = $p->id;
                $inserted++;
            }

            DB::commit();

            $msg = "Import selesai. {$inserted} data berhasil ditambahkan.";

            if (!empty($errors)) {
                $msg .= ' ' . count($errors) . ' baris dilewati karena error.';
                return redirect()->back()
                    ->with('success', $msg)
                    ->with('import_errors', $errors);
            }

            return redirect()->back()->with('success', $msg);

        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }

    // =========================================================
    // HELPER — Generate nomor otomatis
    // =========================================================
    private function generateNomor(
        string $level,
        int    $indikatorId,
        string $rawParent,
        array  &$judulCounter,
        array  &$subjudulCounter,
        array  &$pertanyaanCounter
    ): string {
        if ($level === 'judul') {
            $judulCounter[$indikatorId] = ($judulCounter[$indikatorId] ?? 0) + 1;
            return $this->toRoman($judulCounter[$indikatorId]);
        }

        if ($level === 'subjudul') {
            $key = "{$indikatorId}|" . strtolower($rawParent);
            $subjudulCounter[$key] = ($subjudulCounter[$key] ?? 0) + 1;
            return $this->toLetter($subjudulCounter[$key]);
        }

        $key = "{$indikatorId}|" . strtolower($rawParent);
        $pertanyaanCounter[$key] = ($pertanyaanCounter[$key] ?? 0) + 1;
        return (string) $pertanyaanCounter[$key];
    }

    private function syncCounter(
        string $level,
        int    $indikatorId,
        string $nomor,
        string $rawParent,
        array  &$judulCounter,
        array  &$subjudulCounter,
        array  &$pertanyaanCounter
    ): void {
        if ($level === 'judul') {
            $val = $this->fromRoman(strtoupper($nomor));
            if ($val > ($judulCounter[$indikatorId] ?? 0)) {
                $judulCounter[$indikatorId] = $val;
            }
            return;
        }

        if ($level === 'subjudul') {
            $key = "{$indikatorId}|" . strtolower($rawParent);
            $val = $this->fromLetter(strtoupper($nomor));
            if ($val > ($subjudulCounter[$key] ?? 0)) {
                $subjudulCounter[$key] = $val;
            }
            return;
        }

        if (is_numeric($nomor)) {
            $key = "{$indikatorId}|" . strtolower($rawParent);
            $val = (int) $nomor;
            if ($val > ($pertanyaanCounter[$key] ?? 0)) {
                $pertanyaanCounter[$key] = $val;
            }
        }
    }

    private function toRoman(int $n): string
    {
        $map = [
            1000=>'M',900=>'CM',500=>'D',400=>'CD',
            100=>'C',90=>'XC',50=>'L',40=>'XL',
            10=>'X',9=>'IX',5=>'V',4=>'IV',1=>'I',
        ];
        $result = '';
        foreach ($map as $value => $numeral) {
            while ($n >= $value) { $result .= $numeral; $n -= $value; }
        }
        return $result;
    }

    private function fromRoman(string $s): int
    {
        $map = ['M'=>1000,'D'=>500,'C'=>100,'L'=>50,'X'=>10,'V'=>5,'I'=>1];
        $result = 0; $prev = 0;
        foreach (array_reverse(str_split($s)) as $ch) {
            $val = $map[$ch] ?? 0;
            $result += ($val < $prev) ? -$val : $val;
            $prev = $val;
        }
        return $result;
    }

    private function toLetter(int $n): string
    {
        $result = '';
        while ($n > 0) {
            $n--;
            $result = chr(65 + ($n % 26)) . $result;
            $n = intdiv($n, 26);
        }
        return $result;
    }

    private function fromLetter(string $s): int
    {
        $s = strtoupper($s); $result = 0;
        foreach (str_split($s) as $ch) {
            $result = $result * 26 + (ord($ch) - 64);
        }
        return $result;
    }
}