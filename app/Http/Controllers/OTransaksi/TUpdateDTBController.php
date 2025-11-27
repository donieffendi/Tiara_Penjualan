<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class TUpdateDTBController extends Controller
{
    public function index(Request $request)
    {
        try {
            $judul = 'Transaksi Update DTB';

            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                return view("otransaksi_TUpdateDTB.index")->with([
                    'judul' => $judul,
                    'error' => 'User tidak memiliki akses cabang (CBG). Hubungi administrator.'
                ]);
            }

            if (!$request->session()->has('periode')) {
                return view("otransaksi_TUpdateDTB.index")->with([
                    'judul' => $judul,
                    'warning' => 'Periode belum diset. Silakan set periode terlebih dahulu.'
                ]);
            }

            $periode = $request->session()->get('periode');
            $username = Auth::user()->username ?? 'system';

            // Buat tabel history DTB jika belum ada
            $this->createHistoTable($CBG);

            return view("otransaksi_TUpdateDTB.index")->with([
                'judul' => $judul,
                'cbg' => $CBG,
                'periode' => $periode,
                'username' => $username
            ]);
        } catch (\Exception $e) {
            Log::error('Error in TUpdateDTB index: ' . $e->getMessage());
            return view("otransaksi_TUpdateDTB.index")->with([
                'judul' => 'Transaksi Update DTB',
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    private function createHistoTable($cabang)
    {
        try {
            $connection = strtolower($cabang);

            // Buat tabel histo_dtb jika belum ada
            DB::connection($connection)->statement("
                CREATE TABLE IF NOT EXISTS histo_dtb (
                    KD_BRG VARCHAR(10) NOT NULL,
                    DTB_LAMA DECIMAL(10,2) DEFAULT 0,
                    DTB_BARU DECIMAL(10,2) DEFAULT 0,
                    TG_SMP DATETIME DEFAULT NULL,
                    USRNM VARCHAR(50) DEFAULT NULL,
                    PRIMARY KEY (KD_BRG)
                )
            ");
        } catch (\Exception $e) {
            Log::error('Error creating histo_dtb table: ' . $e->getMessage());
        }
    }

    public function cari_data(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            $filterDTB = $request->input('filter_dtb', 'ADA'); // ADA / KOSONG
            $filterSub = $request->input('filter_sub', ''); // Filter subitem

            $connection = strtolower($CBG);

            // Query untuk mengambil data master barang dengan DTB
            $query = "
                SELECT 
                    LEFT(a.kd_brg, 3) as sub,
                    RIGHT(a.kd_brg, 4) as item,
                    a.kd_brg,
                    a.na_brg,
                    a.ket_kem,
                    a.ket_uk,
                    COALESCE(b.lph, 0) as lph,
                    COALESCE(a.dtb, 0) as dtb,
                    COALESCE(b.dtr, 0) as dtr,
                    COALESCE(b.dtr2, 0) as dtr2,
                    COALESCE(b.dtr_ideal, 0) as dtr_ideal,
                    a.td_od,
                    a.cat_od,
                    DATE_FORMAT(a.tgl_od, '%d/%m/%Y') as tgl_od,
                    COALESCE(a.dtb, 0) as dtb_baru,
                    COALESCE(b.dtr_ideal, 0) as dtr2_baru,
                    0 as cek
                FROM brg a
                LEFT JOIN brgdt b ON a.kd_brg = b.kd_brg
                WHERE 1=1
            ";

            $params = [];

            // Filter berdasarkan DTB
            if (strtoupper($filterDTB) == 'ADA') {
                $query .= " AND a.dtb > 0";
            } elseif (strtoupper($filterDTB) == 'KOSONG') {
                $query .= " AND (a.dtb = 0 OR a.dtb IS NULL)";
            }

            // Filter berdasarkan subitem
            if (!empty($filterSub)) {
                $query .= " AND LEFT(a.kd_brg, 3) = ?";
                $params[] = $filterSub;
            }

            $query .= " ORDER BY a.kd_brg";

            $data = DB::connection($connection)->select($query, $params);

            return response()->json([
                'success' => true,
                'data' => $data,
                'count' => count($data)
            ]);
        } catch (\Exception $e) {
            Log::error('Error in cari_data: ' . $e->getMessage());
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function proses(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            $username = Auth::user()->username ?? 'system';

            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            $dataItems = $request->input('items', []);

            if (empty($dataItems)) {
                return response()->json(['error' => 'Tidak ada data untuk diproses'], 400);
            }

            $connection = strtolower($CBG);

            DB::connection($connection)->beginTransaction();

            $successCount = 0;
            $errorCount = 0;

            foreach ($dataItems as $item) {
                try {
                    // Cek apakah item ini ter-checklist (cek = 1)
                    if (isset($item['cek']) && $item['cek'] == 1) {
                        $kdBrg = $item['kd_brg'];
                        $dtbBaru = $item['dtb_baru'] ?? 0;
                        $dtbLama = $item['dtb'] ?? 0;

                        // Insert/Update ke tabel histo_dtb
                        DB::connection($connection)->statement("
                            INSERT INTO histo_dtb (KD_BRG, DTB_LAMA, DTB_BARU, TG_SMP, USRNM)
                            VALUES (?, ?, ?, NOW(), ?)
                            ON DUPLICATE KEY UPDATE
                                DTB_LAMA = VALUES(DTB_LAMA),
                                DTB_BARU = VALUES(DTB_BARU),
                                TG_SMP = NOW(),
                                USRNM = VALUES(USRNM)
                        ", [$kdBrg, $dtbLama, $dtbBaru, $username]);

                        $successCount++;
                    }
                } catch (\Exception $e) {
                    Log::error('Error updating item: ' . ($item['kd_brg'] ?? 'unknown') . ' - ' . $e->getMessage());
                    $errorCount++;
                }
            }

            // Post/Commit perubahan DTB ke tabel brg
            try {
                DB::connection($connection)->statement("
                    UPDATE brg a
                    INNER JOIN histo_dtb b ON a.kd_brg = b.KD_BRG
                    SET a.dtb = b.DTB_BARU,
                        a.usrnm = ?,
                        a.tg_smp = NOW()
                ", [$username]);
            } catch (\Exception $e) {
                Log::error('Error posting DTB: ' . $e->getMessage());
            }

            DB::connection($connection)->commit();

            $message = "Proses Update DTB selesai!<br>";
            $message .= "Berhasil: {$successCount} item<br>";
            if ($errorCount > 0) {
                $message .= "Gagal: {$errorCount} item";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'successCount' => $successCount,
                'errorCount' => $errorCount
            ]);
        } catch (\Exception $e) {
            if (isset($connection)) {
                DB::connection($connection)->rollBack();
            }
            Log::error('Error in proses: ' . $e->getMessage());
            return response()->json([
                'error' => 'Proses gagal: ' . $e->getMessage()
            ], 500);
        }
    }

    public function importExcel(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            $username = Auth::user()->username ?? 'system';

            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            if (!$request->hasFile('file')) {
                return response()->json(['error' => 'File tidak ditemukan'], 400);
            }

            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();

            // Validasi ekstensi file
            $extension = $file->getClientOriginalExtension();
            if (!in_array($extension, ['xls', 'xlsx'])) {
                return response()->json(['error' => 'File harus berformat Excel (.xls atau .xlsx)'], 400);
            }

            $connection = strtolower($CBG);

            // Buat tabel import sementara
            DB::connection($connection)->statement("
                CREATE TABLE IF NOT EXISTS excelimpdtb (
                    KD_BRG VARCHAR(10) DEFAULT NULL,
                    BARCODE VARCHAR(20) DEFAULT NULL,
                    DTB DECIMAL(10,2) DEFAULT 0,
                    TG_SMP DATETIME DEFAULT NULL,
                    USRNM VARCHAR(50) DEFAULT NULL,
                    NAMAFILE VARCHAR(255) DEFAULT NULL
                )
            ");

            // Truncate tabel import
            DB::connection($connection)->statement("TRUNCATE TABLE excelimpdtb");

            // Load file Excel
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            if (count($rows) < 2) {
                return response()->json(['error' => 'File Excel kosong atau tidak memiliki data'], 400);
            }

            // Cek header (baris pertama)
            $header = array_map('strtoupper', $rows[0]);
            $kolom = in_array('BARCODE', $header) ? 'BARCODE' : 'KD_BRG';

            DB::connection($connection)->beginTransaction();

            $importCount = 0;
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];

                if (empty($row[0])) {
                    continue; // Skip baris kosong
                }

                $kode = $row[0] ?? '';
                $dtb = $row[1] ?? 0;

                DB::connection($connection)->statement("
                    INSERT INTO excelimpdtb ({$kolom}, DTB, TG_SMP, USRNM, NAMAFILE)
                    VALUES (?, ?, NOW(), ?, ?)
                ", [$kode, $dtb, $username, $fileName]);

                $importCount++;
            }

            // Update DTB dari import ke histo_dtb
            if ($kolom == 'BARCODE') {
                DB::connection($connection)->statement("
                    INSERT INTO histo_dtb (KD_BRG, DTB_LAMA, DTB_BARU, TG_SMP, USRNM)
                    SELECT a.kd_brg, COALESCE(a.dtb, 0), b.DTB, NOW(), ?
                    FROM brg a
                    INNER JOIN excelimpdtb b ON a.barcode = b.BARCODE
                    ON DUPLICATE KEY UPDATE
                        DTB_BARU = VALUES(DTB_BARU),
                        TG_SMP = NOW(),
                        USRNM = VALUES(USRNM)
                ", [$username]);
            } else {
                DB::connection($connection)->statement("
                    INSERT INTO histo_dtb (KD_BRG, DTB_LAMA, DTB_BARU, TG_SMP, USRNM)
                    SELECT a.kd_brg, COALESCE(a.dtb, 0), b.DTB, NOW(), ?
                    FROM brg a
                    INNER JOIN excelimpdtb b ON a.kd_brg = b.KD_BRG
                    ON DUPLICATE KEY UPDATE
                        DTB_BARU = VALUES(DTB_BARU),
                        TG_SMP = NOW(),
                        USRNM = VALUES(USRNM)
                ", [$username]);
            }

            DB::connection($connection)->commit();

            return response()->json([
                'success' => true,
                'message' => "Berhasil import {$importCount} baris dari file Excel."
            ]);
        } catch (\Exception $e) {
            if (isset($connection)) {
                DB::connection($connection)->rollBack();
            }
            Log::error('Error in importExcel: ' . $e->getMessage());
            return response()->json([
                'error' => 'Import gagal: ' . $e->getMessage()
            ], 500);
        }
    }

    public function exportExcel(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            $filterDTB = $request->input('filter_dtb', 'ADA');
            $filterSub = $request->input('filter_sub', '');

            $connection = strtolower($CBG);

            // Query data
            $query = "
                SELECT 
                    LEFT(a.kd_brg, 3) as sub,
                    RIGHT(a.kd_brg, 4) as item,
                    a.na_brg,
                    a.ket_kem,
                    a.ket_uk,
                    COALESCE(b.lph, 0) as lph,
                    COALESCE(a.dtb, 0) as dtb,
                    COALESCE(b.dtr, 0) as dtr,
                    COALESCE(b.dtr_ideal, 0) as dtr_ideal,
                    COALESCE(b.dtr2, 0) as dtr2
                FROM brg a
                LEFT JOIN brgdt b ON a.kd_brg = b.kd_brg
                WHERE 1=1
            ";

            $params = [];

            if (strtoupper($filterDTB) == 'ADA') {
                $query .= " AND a.dtb > 0";
            } elseif (strtoupper($filterDTB) == 'KOSONG') {
                $query .= " AND (a.dtb = 0 OR a.dtb IS NULL)";
            }

            if (!empty($filterSub)) {
                $query .= " AND LEFT(a.kd_brg, 3) = ?";
                $params[] = $filterSub;
            }

            $query .= " ORDER BY a.kd_brg";

            $data = DB::connection($connection)->select($query, $params);

            // Buat file Excel
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Header
            $sheet->setCellValue('A1', 'Sub');
            $sheet->setCellValue('B1', 'Item');
            $sheet->setCellValue('C1', 'Nama Barang');
            $sheet->setCellValue('D1', 'Kemasan');
            $sheet->setCellValue('E1', 'Ukuran');
            $sheet->setCellValue('F1', 'LPH');
            $sheet->setCellValue('G1', 'DTB');
            $sheet->setCellValue('H1', 'DTR');
            $sheet->setCellValue('I1', 'DTR Ideal');
            $sheet->setCellValue('J1', 'DTR2');

            // Data
            $row = 2;
            foreach ($data as $item) {
                $sheet->setCellValue('A' . $row, $item->sub);
                $sheet->setCellValue('B' . $row, $item->item);
                $sheet->setCellValue('C' . $row, $item->na_brg);
                $sheet->setCellValue('D' . $row, $item->ket_kem);
                $sheet->setCellValue('E' . $row, $item->ket_uk);
                $sheet->setCellValue('F' . $row, $item->lph);
                $sheet->setCellValue('G' . $row, $item->dtb);
                $sheet->setCellValue('H' . $row, $item->dtr);
                $sheet->setCellValue('I' . $row, $item->dtr_ideal);
                $sheet->setCellValue('J' . $row, $item->dtr2);
                $row++;
            }

            // Auto size columns
            foreach (range('A', 'J') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Save file
            $fileName = 'UpdateDTB_' . $CBG . '_' . date('YmdHis') . '.xlsx';
            $writer = new Xlsx($spreadsheet);

            $tempFile = tempnam(sys_get_temp_dir(), $fileName);
            $writer->save($tempFile);

            return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error('Error in exportExcel: ' . $e->getMessage());
            return response()->json([
                'error' => 'Export gagal: ' . $e->getMessage()
            ], 500);
        }
    }
}
