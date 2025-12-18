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

            $per = $request->session()->get('periode');
            $periode = $per['bulan'] . '/' . $per['tahun'];
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
            // Buat tabel histo_dtb jika belum ada dengan prefix tgz.
            DB::statement("
                CREATE TABLE IF NOT EXISTS tgz.histo_dtb (
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

            Log::info('TUpdateDTB cari_data', [
                'CBG' => $CBG,
                'filterDTB' => $filterDTB,
                'filterSub' => $filterSub
            ]);

            // Query langsung untuk mengambil data master barang dengan DTB
            $query = "
                SELECT 
                    A.KD_BRG,
                    A.NA_BRG,
                    A.KET_KEM,
                    A.KET_UK,
                    A.SATUAN,
                    COALESCE(A.DTB, 0) as DTB,
                    COALESCE(A.DTR, 0) as DTR,
                    COALESCE(B.HARGA01, 0) as LPH,
                    ROUND(COALESCE(A.DTB, 0) / NULLIF(COALESCE(B.HARGA01, 0), 0), 2) as DTR_IDEAL,
                    COALESCE(A.DTR, 0) as DTR2,
                    A.SUB,
                    A.KDBAR,
                    0 as TD_OD
                FROM tgz.brg A
                LEFT JOIN tgz.brgdt B ON A.KD_BRG = B.KD_BRG AND B.CBG = ?
                WHERE 1=1
            ";

            $params = [$CBG];

            // Filter berdasarkan DTB (ADA = DTB > 0, KOSONG = DTB = 0 atau NULL)
            if ($filterDTB === 'KOSONG') {
                $query .= " AND (A.DTB IS NULL OR A.DTB = 0)";
            } else {
                $query .= " AND A.DTB > 0";
            }

            // Filter berdasarkan subitem jika ada
            if (!empty($filterSub)) {
                $query .= " AND A.KD_BRG LIKE ?";
                $params[] = $filterSub . '%';
            }

            $query .= " ORDER BY A.KD_BRG ASC";

            $data = DB::select($query, $params);

            Log::info('TUpdateDTB cari_data result', ['count' => count($data)]);

            return response()->json([
                'success' => true,
                'data' => $data,
                'count' => count($data)
            ]);
        } catch (\Exception $e) {
            Log::error('Error in cari_data: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
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

            DB::beginTransaction();

            $successCount = 0;
            $errorCount = 0;

            foreach ($dataItems as $item) {
                try {
                    // Cek apakah item ini ter-checklist (cek = 1)
                    if (isset($item['cek']) && $item['cek'] == 1) {
                        $kdBrg = $item['kd_brg'];
                        $dtbBaru = $item['dtb_baru'] ?? 0;

                        Log::info('Processing item', [
                            'kd_brg' => $kdBrg,
                            'dtb_baru' => $dtbBaru
                        ]);

                        // Insert/Update ke histo_dtb dengan prefix tgz.
                        // Ambil DTB lama dari brg
                        $dtbLamaQuery = DB::select("
                            SELECT COALESCE(DTB, 0) as DTB_LAMA 
                            FROM tgz.brg 
                            WHERE KD_BRG = ?
                        ", [$kdBrg]);

                        $dtbLama = $dtbLamaQuery[0]->DTB_LAMA ?? 0;

                        // Insert atau update histo_dtb
                        DB::statement("
                            INSERT INTO tgz.histo_dtb (KD_BRG, DTB_LAMA, DTB_BARU, TG_SMP, USRNM)
                            VALUES (?, ?, ?, NOW(), ?)
                            ON DUPLICATE KEY UPDATE
                                DTB_LAMA = ?,
                                DTB_BARU = ?,
                                TG_SMP = NOW(),
                                USRNM = ?
                        ", [$kdBrg, $dtbLama, $dtbBaru, $username, $dtbLama, $dtbBaru, $username]);

                        $successCount++;
                    }
                } catch (\Exception $e) {
                    Log::error('Error updating item: ' . ($item['kd_brg'] ?? 'unknown') . ' - ' . $e->getMessage(), [
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ]);
                    $errorCount++;
                }
            }

            // Post/Commit perubahan DTB ke tabel brg dengan prefix tgz.
            try {
                DB::statement("
                    UPDATE tgz.brg a
                    INNER JOIN tgz.histo_dtb b ON a.KD_BRG = b.KD_BRG
                    SET a.DTB = b.DTB_BARU,
                        a.USRNM = ?,
                        a.TG_SMP = NOW()
                ", [$username]);

                Log::info('DTB posted successfully', [
                    'successCount' => $successCount,
                    'username' => $username
                ]);
            } catch (\Exception $e) {
                Log::error('Error posting DTB: ' . $e->getMessage(), [
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]);
                throw $e; // Re-throw untuk rollback
            }

            DB::commit();

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
            DB::rollBack();
            Log::error('Error in proses: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
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

            // Buat tabel import sementara dengan prefix tgz.
            DB::statement("
                CREATE TABLE IF NOT EXISTS tgz.excelimpdtb (
                    KD_BRG VARCHAR(10) DEFAULT NULL,
                    BARCODE VARCHAR(20) DEFAULT NULL,
                    DTB DECIMAL(10,2) DEFAULT 0,
                    TG_SMP DATETIME DEFAULT NULL,
                    USRNM VARCHAR(50) DEFAULT NULL,
                    NAMAFILE VARCHAR(255) DEFAULT NULL
                )
            ");

            // Truncate tabel import
            DB::statement("TRUNCATE TABLE tgz.excelimpdtb");

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

            DB::beginTransaction();

            $importCount = 0;
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];

                if (empty($row[0])) {
                    continue; // Skip baris kosong
                }

                $kode = $row[0] ?? '';
                $dtb = $row[1] ?? 0;

                DB::statement("
                    INSERT INTO tgz.excelimpdtb ({$kolom}, DTB, TG_SMP, USRNM, NAMAFILE)
                    VALUES (?, ?, NOW(), ?, ?)
                ", [$kode, $dtb, $username, $fileName]);

                $importCount++;
            }

            // Update DTB dari import ke histo_dtb dengan prefix tgz.
            if ($kolom == 'BARCODE') {
                DB::statement("
                    INSERT INTO tgz.histo_dtb (KD_BRG, DTB_LAMA, DTB_BARU, TG_SMP, USRNM)
                    SELECT a.KD_BRG, COALESCE(a.DTB, 0), b.DTB, NOW(), ?
                    FROM tgz.brg a
                    INNER JOIN tgz.excelimpdtb b ON a.BARCODE = b.BARCODE
                    ON DUPLICATE KEY UPDATE
                        DTB_BARU = VALUES(DTB_BARU),
                        TG_SMP = NOW(),
                        USRNM = VALUES(USRNM)
                ", [$username]);
            } else {
                DB::statement("
                    INSERT INTO tgz.histo_dtb (KD_BRG, DTB_LAMA, DTB_BARU, TG_SMP, USRNM)
                    SELECT a.KD_BRG, COALESCE(a.DTB, 0), b.DTB, NOW(), ?
                    FROM tgz.brg a
                    INNER JOIN tgz.excelimpdtb b ON a.KD_BRG = b.KD_BRG
                    ON DUPLICATE KEY UPDATE
                        DTB_BARU = VALUES(DTB_BARU),
                        TG_SMP = NOW(),
                        USRNM = VALUES(USRNM)
                ", [$username]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Berhasil import {$importCount} baris dari file Excel."
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in importExcel: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
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

            // Query langsung untuk export
            $query = "
                SELECT 
                    A.KD_BRG,
                    A.NA_BRG,
                    A.KET_KEM,
                    A.KET_UK,
                    A.SATUAN,
                    COALESCE(A.DTB, 0) as DTB,
                    COALESCE(A.DTR, 0) as DTR,
                    COALESCE(B.HARGA01, 0) as LPH,
                    ROUND(COALESCE(A.DTB, 0) / NULLIF(COALESCE(B.HARGA01, 0), 0), 2) as DTR_IDEAL,
                    COALESCE(A.DTR, 0) as DTR2,
                    A.SUB,
                    A.KDBAR,
                    0 as TD_OD
                FROM tgz.brg A
                LEFT JOIN tgz.brgdt B ON A.KD_BRG = B.KD_BRG AND B.CBG = ?
                WHERE 1=1
            ";

            $params = [$CBG];

            if ($filterDTB === 'KOSONG') {
                $query .= " AND (A.DTB IS NULL OR A.DTB = 0)";
            } else {
                $query .= " AND A.DTB > 0";
            }

            if (!empty($filterSub)) {
                $query .= " AND A.KD_BRG LIKE ?";
                $params[] = $filterSub . '%';
            }

            $query .= " ORDER BY A.KD_BRG ASC";

            $data = DB::select($query, $params);
            // Buat file Excel
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Header
            $sheet->setCellValue('A1', 'Sub');
            $sheet->setCellValue('B1', 'Nama Barang');
            $sheet->setCellValue('C1', 'Kemasan');
            $sheet->setCellValue('D1', 'Ukuran');
            $sheet->setCellValue('E1', 'LPH');
            $sheet->setCellValue('F1', 'DTB');
            $sheet->setCellValue('G1', 'DTR');
            $sheet->setCellValue('H1', 'DTR Ideal');
            $sheet->setCellValue('I1', 'DTR2');

            // Data
            $row = 2;
            foreach ($data as $item) {
                $sheet->setCellValue('A' . $row, $item->KD_BRG);
                $sheet->setCellValue('B' . $row, $item->NA_BRG);
                $sheet->setCellValue('C' . $row, $item->KET_KEM);
                $sheet->setCellValue('D' . $row, $item->KET_UK);
                $sheet->setCellValue('E' . $row, $item->LPH);
                $sheet->setCellValue('F' . $row, $item->DTB);
                $sheet->setCellValue('G' . $row, $item->DTR);
                $sheet->setCellValue('H' . $row, $item->DTR_IDEAL);
                $sheet->setCellValue('I' . $row, $item->DTR2);
                $row++;
            }

            // Auto size columns
            foreach (range('A', 'I') as $col) {
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
