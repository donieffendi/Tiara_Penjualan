<?php

namespace App\Http\Controllers\OReport;

use App\Http\Controllers\Controller;
use App\Models\Master\Cbg;
use App\Models\Master\Perid;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;

class RRencanaOrderKode8Controller extends Controller
{
    private $cbgMast = '';

    public function __construct()
    {
        // Initialize cbgMast similar to Delphi FormShow
        try {
            $result = DB::table('tgz.toko')
                ->select('KODE')
                ->where('STA', 'MA')
                ->first();

            $this->cbgMast = $result ? $result->KODE : '';
        } catch (\Exception $e) {
            Log::error('Error initializing cbgMast: ' . $e->getMessage());
            $this->cbgMast = '';
        }
    }

    public function report()
    {
        Log::info('Generating report for RRencanaOrderKode8');
        $cbg = Cbg::groupBy('CBG')->get();
        $per = Perid::query()->get();

        // Initialize session variables
        session()->put('filter_cbg', '');
        session()->put('filter_sub1', '');
        session()->put('filter_no_rencana', '');
        session()->put('filter_ulang', false);

        // Initialize table similar to Delphi FormShow
        $this->initializeTable(session('filter_cbg', ''));

        return view('oreport_rencanakode8.report')->with([
            'cbg' => $cbg,
            'per' => $per,
            'hasilRencanaOrderKode8' => [],
            'cbgMast' => $this->cbgMast
        ]);
    }

    public function getRencanaOrderKode8Report(Request $request)
    {
        $cbg = Cbg::groupBy('CBG')->get();
        $per = Perid::query()->get();

        // Set filter values to session
        session()->put('filter_cbg', $request->cbg ?? '');
        session()->put('filter_sub1', $request->sub1 ?? '');
        session()->put('filter_no_rencana', $request->no_rencana ?? '');
        session()->put('filter_ulang', $request->ulang == '1');

        $hasilRencanaOrderKode8 = [];

        if (!empty($request->cbg)) {
            try {
                // Validate input similar to Delphi btnTampilClick
                $this->validateInput($request);

                // Check if data already processed for today (similar to Delphi logic)
                if (!session('filter_ulang', false) && empty($request->no_rencana)) {
                    $checkResult = $this->checkExistingData($request->cbg, $request->sub1);
                    if ($checkResult['ada'] > 0) {
                        return view('oreport_rencanakode8.report')->with([
                            'cbg' => $cbg,
                            'per' => $per,
                            'hasilRencanaOrderKode8' => [],
                            'warning' => 'Rencana Order hari ini sudah diproses. Silahkan gunakan fitur cetak ulang atau buat yang baru.',
                            'show_ulang_option' => true
                        ]);
                    }
                }

                $hasilRencanaOrderKode8 = $this->getRencanaOrderKode8Data(
                    $request->cbg,
                    $request->sub1,
                    $request->no_rencana,
                    session('filter_ulang', false)
                );
            } catch (\Exception $e) {
                Log::error('Error in getRencanaOrderKode8Report: ' . $e->getMessage());
                return view('oreport_rencanakode8.report')->with([
                    'cbg' => $cbg,
                    'per' => $per,
                    'hasilRencanaOrderKode8' => [],
                    'error' => $e->getMessage()
                ]);
            }
        }

        return view('oreport_rencanakode8.report')->with([
            'cbg' => $cbg,
            'per' => $per,
            'hasilRencanaOrderKode8' => $hasilRencanaOrderKode8
        ]);
    }

    /**
     * Initialize table similar to Delphi FormShow procedure
     */
    private function initializeTable($cbg)
    {
        if (empty($this->cbgMast) || empty($cbg)) {
            return;
        }

        try {
            DB::select("CALL {$this->cbgMast}.gd_koreksi_tgl_produksi(?, ?, ?, ?, ?)", [
                'TABEL',
                '',
                $cbg,
                '',
                ''
            ]);
        } catch (\Exception $e) {
            Log::error('Error in initializeTable: ' . $e->getMessage());
        }
    }

    /**
     * Check existing data similar to Delphi CEK_STOK_NOL_KD8
     */
    private function checkExistingData($cbg, $sub1)
    {
        try {
            if (empty($this->cbgMast)) {
                return ['ada' => 0];
            }

            $result = DB::select("CALL {$this->cbgMast}.gd_koreksi_tgl_produksi(?, ?, ?, ?, ?)", [
                'CEK_STOK_NOL_KD8',
                '',
                $cbg,
                $sub1,
                ''
            ]);

            return [
                'ada' => isset($result[0]) ? ($result[0]->ADA ?? 0) : 0
            ];
        } catch (\Exception $e) {
            Log::error('Error in checkExistingData: ' . $e->getMessage());
            return ['ada' => 0];
        }
    }

    /**
     * Get data Rencana Order Kode 8 sesuai filter
     * Implementasi dari logika Delphi tampil procedure
     */
    private function getRencanaOrderKode8Data($cbg, $sub1, $noRencana = '', $isUlang = false)
    {
        try {
            // Validasi input
            $this->validateInput((object)[
                'cbg' => $cbg,
                'sub1' => $sub1,
                'no_rencana' => $noRencana,
                'ulang' => $isUlang ? '1' : '0'
            ]);

            if (empty($this->cbgMast)) {
                throw new \Exception('Master cabang tidak ditemukan!');
            }

            // Call stored procedure similar to Delphi tampil procedure
            $jenise = 'REPORT_STOK_NOL_KD8';
            $userLogin = auth()->user()->name ?? 'SYSTEM';

            $hasilData = DB::select("CALL {$this->cbgMast}.gd_koreksi_tgl_produksi(?, ?, ?, ?, ?)", [
                $jenise,
                trim($noRencana),
                $cbg,
                $sub1,
                $userLogin
            ]);

            // Transform data sesuai format yang dibutuhkan untuk datatable
            $result = [];
            foreach ($hasilData as $item) {
                $result[] = [
                    'NO_ID' => $item->NO_ID ?? '',
                    'KD_BRG' => $item->KD_BRG ?? '',
                    'NA_BRG' => $item->NA_BRG ?? '',
                    'KET_UK' => $item->KET_UK ?? '',
                    'SUPP' => $item->SUPP ?? '',
                    'STOK' => number_format($item->STOK ?? 0, 0),
                    'LPH' => number_format($item->LPH ?? 0, 0),
                    'DTR' => number_format($item->DTR ?? 0, 0),
                    'SRMIN' => number_format($item->SRMIN ?? 0, 2),
                    'QTY_ORDER' => number_format($item->QTY_ORDER ?? 0, 0),
                    'NAMAFILE' => $item->NAMAFILE ?? '',
                    'POSTED' => $item->POSTED ?? 0,
                    'TGL_BUAT' => $item->TGL_BUAT ?? '',
                    'USER_BUAT' => $item->USER_BUAT ?? '',
                    // Additional fields for analysis
                    'SUB' => substr($item->KD_BRG ?? '', 0, 3),
                    'STATUS' => $this->getStatusKeterangan($item),
                ];
            }

            // Log activity
            $this->logActivity('get_rencana_order_kode8', $cbg, "SUB: {$sub1}, NoRencana: {$noRencana}", count($result));

            return $result;
        } catch (\Exception $e) {
            Log::error('Error in getRencanaOrderKode8Data: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get status keterangan berdasarkan data item
     */
    private function getStatusKeterangan($item)
    {
        $posted = $item->POSTED ?? 0;
        $qtyOrder = $item->QTY_ORDER ?? 0;

        if ($posted == 1) {
            return 'POSTED';
        } elseif ($qtyOrder > 0) {
            return 'READY';
        } else {
            return 'DRAFT';
        }
    }

    /**
     * Post data similar to Delphi btnPostClick
     */
    public function postRencanaOrder(Request $request)
    {
        try {
            $cbg = $request->cbg ?? '';
            $namaFile = $request->namafile ?? '';
            $posted = $request->posted ?? 0;

            if ($posted == 1) {
                throw new \Exception('Data sudah terposting, tidak bisa diubah!');
            }

            if (empty($namaFile)) {
                throw new \Exception('Nama file tidak ditemukan!');
            }

            if (empty($this->cbgMast)) {
                throw new \Exception('Master cabang tidak ditemukan!');
            }

            $userLogin = auth()->user()->name ?? 'SYSTEM';

            // Call stored procedure for posting
            DB::select("CALL {$this->cbgMast}.gd_koreksi_tgl_produksi(?, ?, ?, ?, ?)", [
                'POST_STOK_NOL_KD8',
                $namaFile,
                $cbg,
                '',
                $userLogin
            ]);

            $this->logActivity('post_rencana_order', $cbg, "File: {$namaFile}", 1);

            return response()->json([
                'success' => true,
                'message' => 'Posting selesai.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in postRencanaOrder: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Delete item similar to Delphi cxGrid1DBTableView1KeyUp (Delete key)
     */
    public function deleteItem(Request $request)
    {
        try {
            $cbg = $request->cbg ?? '';
            $noId = $request->no_id ?? '';
            $posted = $request->posted ?? 0;

            if ($posted == 1) {
                throw new \Exception('Data sudah terposting, tidak bisa diubah!');
            }

            if (empty($noId)) {
                throw new \Exception('ID item tidak ditemukan!');
            }

            if (empty($this->cbgMast)) {
                throw new \Exception('Master cabang tidak ditemukan!');
            }

            $userLogin = auth()->user()->name ?? 'SYSTEM';

            // Call stored procedure for deleting item
            DB::select("CALL {$this->cbgMast}.gd_koreksi_tgl_produksi(?, ?, ?, ?, ?)", [
                'DELETE_STOK_NOL_KD8',
                $noId,
                $cbg,
                '',
                $userLogin
            ]);

            $this->logActivity('delete_item', $cbg, "NoID: {$noId}", 1);

            return response()->json([
                'success' => true,
                'message' => 'Item berhasil dihapus.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in deleteItem: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get nama toko berdasarkan kode cabang
     */
    private function getNamaToko($cbg)
    {
        try {
            $result = DB::table('tgz.toko')
                ->select('NAMA_TOKO')
                ->where('KODE', $cbg)
                ->whereIn('STA', ['MA', 'CB', 'DC'])
                ->first();

            return $result ? $result->NAMA_TOKO : '';
        } catch (\Exception $e) {
            Log::error('Error in getNamaToko: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Get daftar cabang yang valid
     */
    public function getCabangList()
    {
        try {
            $query = "
            SELECT KODE, NAMA_TOKO as NAMA, STA
            FROM tgz.toko
            WHERE STA IN ('MA', 'CB', 'DC')
            ORDER BY NO_ID ASC
            ";

            return DB::select($query);
        } catch (\Exception $e) {
            Log::error('Error in getCabangList: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Validasi input parameters similar to Delphi btnTampilClick validation
     */
    private function validateInput($request)
    {
        $isUlang = ($request->ulang ?? '0') == '1';
        $noRencana = trim($request->no_rencana ?? '');
        $sub1 = trim($request->sub1 ?? '');
        $cbg = trim($request->cbg ?? '');

        if (empty($cbg)) {
            throw new \Exception('Cabang harus dipilih!');
        }

        // Validation similar to Delphi logic
        if ($isUlang && empty($noRencana)) {
            throw new \Exception('No. Rencana Order masih kosong');
        }

        if (!$isUlang && empty($sub1)) {
            throw new \Exception('Sub masih kosong');
        }

        if (!$isUlang && strtoupper(trim($sub1)) == 'ALL') {
            // In web version, we can handle this differently than showing message dialog
            // For now, we'll allow it but log a warning
            Log::warning('User requested all sub data', ['cbg' => $cbg, 'user' => auth()->user()->name ?? 'SYSTEM']);
        }

        // Validate cabang format
        if (!preg_match('/^[A-Z0-9]+$/', $cbg)) {
            throw new \Exception('Format cabang tidak valid!');
        }

        // Validate SUB format if provided
        if (!empty($sub1) && strtoupper($sub1) != 'ALL' && !preg_match('/^[0-9]{3}$/', $sub1)) {
            throw new \Exception('Format Sub tidak valid! Harus 3 digit angka atau "ALL".');
        }

        // Validate cabang exists in toko table
        $cabangExists = DB::table('tgz.toko')
            ->where('KODE', $cbg)
            ->whereIn('STA', ['MA', 'CB', 'DC'])
            ->exists();

        if (!$cabangExists) {
            throw new \Exception('Cabang tidak valid atau tidak aktif!');
        }

        return true;
    }

    /**
     * Helper method untuk logging
     */
    private function logActivity($action, $cbg, $additionalInfo = '', $recordCount = 0)
    {
        Log::info("RencanaOrderKode8: {$action}", [
            'cbg' => $cbg,
            'additional_info' => $additionalInfo,
            'record_count' => $recordCount,
            'user' => auth()->user()->name ?? 'system',
            'timestamp' => now()
        ]);
    }

    /**
     * Generate Jasper Report similar to Delphi frxRnolkd8.ShowReport()
     */
    public function jasperRencanaOrderKode8Report(Request $request)
    {
        try {
            $cbg = $request->cbg ?? '';
            $sub1 = $request->sub1 ?? '';
            $noRencana = $request->no_rencana ?? '';
            $isUlang = ($request->ulang ?? '0') == '1';

            if (empty($cbg)) {
                return redirect()->back()->with('error', 'Parameter cabang harus diisi untuk generate report!');
            }

            // Get data
            $data = $this->getRencanaOrderKode8Data($cbg, $sub1, $noRencana, $isUlang);
            $namaToko = $this->getNamaToko($cbg);

            if (empty($data)) {
                return redirect()->back()->with('error', 'Tidak ada data untuk dicetak!');
            }

            // Check if data is posted (similar to Delphi logic)
            $isPosted = false;
            $namaFile = '';
            if (!empty($data)) {
                $isPosted = ($data[0]['POSTED'] ?? 0) == 1;
                $namaFile = $data[0]['NAMAFILE'] ?? '';
            }

            // Prepare Jasper parameters
            $parameters = [
                'REPORT_TITLE' => 'LAPORAN REKAP RENCANA PENGORDERAN KODE 8',
                'COMPANY_NAME' => 'PT. SUMBER ALFARIA TRIJAYA',
                'CABANG' => $cbg,
                'NAMA_TOKO' => $namaToko,
                'SUB' => $sub1,
                'NO_RENCANA' => $noRencana,
                'NAMA_FILE' => $namaFile,
                'STATUS_POSTED' => $isPosted ? 'POSTED' : 'DRAFT',
                'TANGGAL_CETAK' => date('d/m/Y H:i:s'),
                'USER_CETAK' => auth()->user()->name ?? 'System'
            ];

            // Generate report using PHPJasperXML
            $jasper = new PHPJasperXML();
            $jasper->load_xml_file(resource_path('jasper/rencanaorderkode8_report.jrxml'));

            // Set parameters
            $jasper->arrayParameter = $parameters;

            // Set data source
            $jasper->arraysqltable = $data;

            // Load and generate PDF
            $jasper->xml_dismantle();
            $jasper->outpage('pdf', 'Laporan_Rencana_Order_Kode8_' . $cbg . '_' . date('Ymd') . '.pdf');
        } catch (\Exception $e) {
            Log::error('Error in jasperRencanaOrderKode8Report: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal generate report: ' . $e->getMessage());
        }
    }

    /**
     * Get summary statistics for dashboard
     */
    public function getSummaryStats($data)
    {
        if (empty($data)) {
            return [
                'total_items' => 0,
                'total_posted' => 0,
                'total_draft' => 0,
                'total_qty_order' => 0,
                'total_stok' => 0
            ];
        }

        $stats = [
            'total_items' => count($data),
            'total_posted' => 0,
            'total_draft' => 0,
            'total_qty_order' => 0,
            'total_stok' => 0
        ];

        foreach ($data as $item) {
            // Convert formatted numbers back to numeric for calculation
            $stok = (float) str_replace(',', '', $item['STOK']);
            $qtyOrder = (float) str_replace(',', '', $item['QTY_ORDER']);

            $stats['total_stok'] += $stok;
            $stats['total_qty_order'] += $qtyOrder;

            // Count by status
            if (($item['POSTED'] ?? 0) == 1) {
                $stats['total_posted']++;
            } else {
                $stats['total_draft']++;
            }
        }

        return $stats;
    }

    /**
     * Export to Excel similar to Delphi btnExcelClick
     */
    public function exportToExcel(Request $request)
    {
        try {
            $cbg = $request->cbg ?? '';
            $sub1 = $request->sub1 ?? '';
            $noRencana = $request->no_rencana ?? '';
            $isUlang = ($request->ulang ?? '0') == '1';

            if (empty($cbg)) {
                throw new \Exception('Parameter cabang harus diisi!');
            }

            // Get data
            $data = $this->getRencanaOrderKode8Data($cbg, $sub1, $noRencana, $isUlang);

            if (empty($data)) {
                throw new \Exception('Tidak ada data untuk diekspor!');
            }

            // Create Excel file using PhpSpreadsheet or similar
            // This is a simplified version - you may want to use a proper Excel library
            $filename = 'Rencana_Order_Kode8_' . $cbg . '_' . date('Ymd') . '.xlsx';

            // For now, return JSON data - implement Excel export based on your preferred library
            return response()->json([
                'success' => true,
                'data' => $data,
                'filename' => $filename
            ]);
        } catch (\Exception $e) {
            Log::error('Error in exportToExcel: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}