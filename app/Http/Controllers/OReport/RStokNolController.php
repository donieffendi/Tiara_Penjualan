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

class RStokNolController extends Controller
{
    public function report()
    {
        $cbg = Cbg::groupBy('CBG')->get();
        $per = Perid::query()->get();

        // Initialize session variables - mengadaptasi dari FormShow Delphi
        session()->put('filter_cbg', $this->getMasterCabang());
        session()->put('filter_hari', 9999);
        session()->put('filter_tgl', false);
        session()->put('tgl1', Carbon::today()->format('Y-m-d'));
        session()->put('tgl2', Carbon::today()->format('Y-m-d'));

        return view('oreport_stoknol.report')->with([
            'cbg' => $cbg,
            'per' => $per,
            'hasilStokNol' => []
        ]);
    }

    /**
     * Mengadaptasi logika FormShow Delphi untuk mendapatkan cabang master
     */
    private function getMasterCabang()
    {
        try {
            $masterCbg = DB::table('tgz.toko')
                ->select('KODE')
                ->where('STA', 'MA')
                ->first();

            return $masterCbg ? $masterCbg->KODE : '';
        } catch (\Exception $e) {
            Log::error('Error getting master cabang: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Main method untuk mendapatkan laporan stok nol
     * Mengadaptasi dari procedure Tampil dalam Delphi
     */
    public function getStokNolReport(Request $request)
    {
        $cbg = Cbg::groupBy('CBG')->get();
        $per = Perid::query()->get();

        // Set filter values to session
        session()->put('filter_cbg', $request->cbg ?? '');
        session()->put('filter_hari', $request->hari ?? 9999);
        session()->put('filter_tgl', $request->filter_tgl ?? false);
        session()->put('tgl1', $request->tgl1 ?? Carbon::today()->format('Y-m-d'));
        session()->put('tgl2', $request->tgl2 ?? Carbon::today()->format('Y-m-d'));

        $hasilStokNol = [];

        if (!empty($request->cbg)) {
            $hasilStokNol = $this->getStokNolData($request);
        }

        return view('oreport_stoknol.report')->with([
            'cbg' => $cbg,
            'per' => $per,
            'hasilStokNol' => $hasilStokNol
        ]);
    }

    /**
     * Implementasi logika Tampil dari Delphi
     * Query untuk mengambil data barang dengan stok nol/negatif
     */
    private function getStokNolData(Request $request)
    {
        try {
            // Validasi input - mengadaptasi dari Delphi
            $hari = $request->hari ?? 9999;
            if ($hari < 0) {
                throw new \Exception('Selisih Hari tidak bisa < 0');
            }

            $cbg = $request->cbg;
            $filterTgl = $request->filter_tgl ?? false;
            $tgl1 = $request->tgl1 ?? Carbon::today()->format('Y-m-d');
            $tgl2 = $request->tgl2 ?? Carbon::today()->format('Y-m-d');

            // Validate cabang exists
            $this->validateCabang($cbg);

            // Build filter tanggal seperti di Delphi
            $filterTglQuery = '';
            $params = ['hari' => $hari];

            if ($filterTgl) {
                $filterTglQuery = ' AND DATE(b.TGL_KSR) BETWEEN :tgl1 AND :tgl2 ';
                $params['tgl1'] = $tgl1;
                $params['tgl2'] = $tgl2;
            }

            // Query utama - adaptasi dari SQL Delphi dengan beberapa penyesuaian
            $query = "
                SELECT * FROM (
                    SELECT
                        a.KD_BRG,
                        a.NA_BRG,
                        a.KET_UK,
                        a.KET_KEM,
                        a.BARCODE,
                        (b.GAK00 + b.AK00) as STOK,
                        b.TD_OD,
                        b.CAT_OD,
                        DATE(b.TGL_OD) as TGL_OD,
                        DATE(b.TGL_KSR) as TGL_KSR,
                        DATEDIFF(b.TGL_KSR, b.TGL_OD) as HARI
                    FROM {$cbg}.brg a
                    INNER JOIN {$cbg}.brgdt b ON a.KD_BRG = b.KD_BRG
                    WHERE (b.GAK00 + b.AK00) <= 0
                      AND b.TD_OD = '*'
                      AND DATE(b.TGL_OD) <= DATE(b.TGL_KSR)
                      AND LEFT(a.NA_BRG, 1) != '#'
                      {$filterTglQuery}
                ) as rekap
                WHERE HARI <= :hari
                ORDER BY TGL_OD DESC
            ";

            $result = DB::select($query, $params);

            // Log activity
            $this->logActivity('getStokNolData', $cbg, '', count($result));

            return $result;
        } catch (\Exception $e) {
            Log::error('Error in getStokNolData: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Validasi cabang - mengadaptasi validasi dari Delphi
     */
    private function validateCabang($cbg)
    {
        if (empty($cbg)) {
            throw new \Exception('Cabang harus dipilih!');
        }

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
     * Export ke Excel - mengadaptasi dari Button2Click Delphi
     */
    public function exportToExcel(Request $request)
    {
        try {
            $this->validateInput($request);

            $data = $this->getStokNolData($request);

            if (empty($data)) {
                return response()->json(['message' => 'Tidak ada data stok nol untuk diekspor'], 200);
            }

            // Format filename seperti di Delphi
            $filename = 'stok_nol_' . $request->cbg . '_' . Carbon::now()->format('YmdHis') . '.xlsx';
            $filepath = storage_path('app/exports/' . $filename);

            // Check if file exists - mengadaptasi dari SaveDialog Delphi
            if (file_exists($filepath)) {
                throw new \Exception('File already exists. Cannot overwrite.');
            }

            // Implementasi export Excel (placeholder - sesuaikan dengan library yang digunakan)
            $this->generateExcelFile($data, $filepath);

            return response()->download($filepath, $filename)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error('Error in exportToExcel: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function escapeString($value)
    {
        // Ganti tanda kutip agar PHPJasperXML tidak crash
        $value = str_replace('"', '”', $value);   // tanda kutip ganda
        $value = str_replace("'", '’', $value);   // tanda kutip tunggal
        // Hapus newline atau carriage return
        $value = str_replace(["\r", "\n"], ' ', $value);
        // Hapus backslash
        $value = str_replace('\\', '', $value);
        return $value;
    }

    /**
     * Generate laporan PDF - mengadaptasi dari Button1Click Delphi
     */
    public function jasperStokNolReport(Request $request)
    {
        try {
            $this->validateInput($request);

            $data = $this->getStokNolData($request);

            if (empty($data)) {
                return response()->json(['message' => 'Tidak ada data untuk ditampilkan'], 200);
            }

            $file = 'lap_stok_nol_tanda';
            $PHPJasperXML = new PHPJasperXML();
            $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));
            $params = [
                "TGL_CTK" => date('d/m/Y'),
            ];
            $PHPJasperXML->arrayParameter=$params;
            // Format data untuk Jasper Report
            $jasperData = [];
            foreach ($data as $item) {
                $jasperData[] = [
                    'KD_BRG' => isset($item->KD_BRG) ? $this->escapeString($item->KD_BRG) : '',
                    'NA_BRG' => isset($item->NA_BRG) ? $this->escapeString($item->NA_BRG) : '',
                    'KET_UK' => isset($item->KET_UK) ? $this->escapeString($item->KET_UK) : '',
                    'KET_KEM' => isset($item->KET_KEM) ? $this->escapeString($item->KET_KEM) : '',
                    'BARCODE' => isset($item->BARCODE) ? $this->escapeString($item->BARCODE) : '',
                    'STOK' => is_numeric($item->STOK) ? (float)$item->STOK : 0,
                    'TD_OD' => isset($item->TD_OD) ? $this->escapeString($item->TD_OD) : '',
                    'CAT_OD' => isset($item->CAT_OD) ? $this->escapeString($item->CAT_OD) : '',
                    'TGL_OD' => $item->TGL_OD ?? '',
                    'TGL_KSR' => $item->TGL_KSR ?? '',
                    'HARI' => is_numeric($item->HARI) ? (int)$item->HARI : 0,
                    'CBG' => isset($request->cbg) ? $this->escapeString($request->cbg) : '',
                    'FILTER_HARI' => $request->hari ?? 9999,
                    'TGL1' => $request->tgl1 ?? '',
                    'TGL2' => $request->tgl2 ?? '',
                    'FILTER_TGL' => $request->filter_tgl ? 'Ya' : 'Tidak'
                ];
            }

            $PHPJasperXML->setData($jasperData);
            ob_end_clean();
            $PHPJasperXML->outpage("I");
        } catch (\Exception $e) {
            Log::error('Error in jasperStokNolReport: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get data untuk preview di grid - mengadaptasi tampilan grid Delphi
     */
    public function getPreviewData(Request $request)
    {
        try {
            $this->validateInput($request);

            $data = $this->getStokNolData($request);

            // Format data untuk ditampilkan di grid seperti cxGrid di Delphi
            $formattedData = collect($data)->map(function ($item) {
                return [
                    'KD_BRG' => $item->KD_BRG,
                    'NA_BRG' => $item->NA_BRG,
                    'KET_UK' => $item->KET_UK,
                    'KET_KEM' => $item->KET_KEM,
                    'BARCODE' => $item->BARCODE,
                    'STOK' => number_format($item->STOK, 0),
                    'TGL_OD' => Carbon::parse($item->TGL_OD)->format('d/m/Y'),
                    'TGL_KSR' => Carbon::parse($item->TGL_KSR)->format('d/m/Y'),
                    'HARI' => $item->HARI,
                    'STATUS' => $item->STOK <= 0 ? 'Stok Nol/Negatif' : 'Stok Rendah'
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedData,
                'total' => count($data)
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getPreviewData: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get daftar cabang yang valid - mengadaptasi dari query com1 Delphi
     */
    public function getCabangList()
    {
        try {
            $query = "
                SELECT KODE, NAMA, STA
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
     * Validasi input parameters - mengadaptasi validasi dari Delphi
     */
    private function validateInput(Request $request)
    {
        $cbg = $request->cbg;
        $hari = $request->hari ?? 9999;

        if (empty($cbg)) {
            throw new \Exception('Cabang harus dipilih!');
        }

        if ($hari < 0) {
            throw new \Exception('Selisih Hari tidak bisa < 0');
        }

        // Validate format tanggal jika filter tanggal aktif
        if ($request->filter_tgl) {
            $tgl1 = $request->tgl1;
            $tgl2 = $request->tgl2;

            if (empty($tgl1) || empty($tgl2)) {
                throw new \Exception('Tanggal mulai dan tanggal akhir harus diisi!');
            }

            if (Carbon::parse($tgl1)->gt(Carbon::parse($tgl2))) {
                throw new \Exception('Tanggal mulai tidak boleh lebih besar dari tanggal akhir!');
            }
        }

        return true;
    }

    /**
     * Generate Excel file - implementasi export Excel
     */
    private function generateExcelFile($data, $filepath)
    {
        // Implementasi export Excel menggunakan library yang sesuai
        // Contoh menggunakan PhpSpreadsheet (jika tersedia)
        // atau bisa menggunakan format CSV sederhana

        $headers = [
            'Kode Barang',
            'Nama Barang',
            'Ukuran',
            'Kemasan',
            'Barcode',
            'Stok',
            'Tanggal OD',
            'Tanggal KSR',
            'Selisih Hari',
            'Status'
        ];

        $csvContent = implode(',', $headers) . "\n";

        foreach ($data as $row) {
            $csvRow = [
                '"' . ($row->KD_BRG ?? '') . '"',
                '"' . ($row->NA_BRG ?? '') . '"',
                '"' . ($row->KET_UK ?? '') . '"',
                '"' . ($row->KET_KEM ?? '') . '"',
                '"' . ($row->BARCODE ?? '') . '"',
                $row->STOK ?? 0,
                '"' . ($row->TGL_OD ?? '') . '"',
                '"' . ($row->TGL_KSR ?? '') . '"',
                $row->HARI ?? 0,
                '"' . ($row->STOK <= 0 ? 'Stok Nol/Negatif' : 'Stok Rendah') . '"'
            ];
            $csvContent .= implode(',', $csvRow) . "\n";
        }

        // Ensure directory exists
        $directory = dirname($filepath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents($filepath, $csvContent);
    }

    /**
     * Method untuk mendapatkan summary data
     */
    public function getSummaryData(Request $request)
    {
        try {
            $this->validateInput($request);

            $cbg = $request->cbg;
            $hari = $request->hari ?? 9999;
            $filterTgl = $request->filter_tgl ?? false;
            $tgl1 = $request->tgl1 ?? Carbon::today()->format('Y-m-d');
            $tgl2 = $request->tgl2 ?? Carbon::today()->format('Y-m-d');

            // Build filter tanggal
            $filterTglQuery = '';
            $params = ['hari' => $hari];

            if ($filterTgl) {
                $filterTglQuery = ' AND DATE(b.TGL_KSR) BETWEEN :tgl1 AND :tgl2 ';
                $params['tgl1'] = $tgl1;
                $params['tgl2'] = $tgl2;
            }

            // Query untuk summary data
            $summaryQuery = "
                SELECT
                    COUNT(*) as TOTAL_ITEM,
                    SUM(CASE WHEN (b.GAK00 + b.AK00) < 0 THEN 1 ELSE 0 END) as STOK_NEGATIF,
                    SUM(CASE WHEN (b.GAK00 + b.AK00) = 0 THEN 1 ELSE 0 END) as STOK_NOL,
                    AVG(DATEDIFF(b.TGL_KSR, b.TGL_OD)) as AVG_HARI,
                    MAX(DATEDIFF(b.TGL_KSR, b.TGL_OD)) as MAX_HARI,
                    MIN(DATEDIFF(b.TGL_KSR, b.TGL_OD)) as MIN_HARI
                FROM {$cbg}.brg a
                INNER JOIN {$cbg}.brgdt b ON a.KD_BRG = b.KD_BRG
                WHERE (b.GAK00 + b.AK00) <= 0
                  AND b.TD_OD = '*'
                  AND DATE(b.TGL_OD) <= DATE(b.TGL_KSR)
                  AND LEFT(a.NA_BRG, 1) != '#'
                  AND DATEDIFF(b.TGL_KSR, b.TGL_OD) <= :hari
                  {$filterTglQuery}
            ";

            $summary = DB::select($summaryQuery, $params);

            return response()->json([
                'success' => true,
                'summary' => $summary[0] ?? null
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getSummaryData: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Method untuk mendapatkan detail barang berdasarkan kode
     */
    public function getDetailBarang(Request $request)
    {
        try {
            $cbg = $request->cbg;
            $kdBrg = $request->kd_brg;

            if (empty($cbg) || empty($kdBrg)) {
                throw new \Exception('Cabang dan Kode Barang harus diisi!');
            }

            $this->validateCabang($cbg);

            $query = "
                SELECT
                    a.KD_BRG,
                    a.NA_BRG,
                    a.KET_UK,
                    a.KET_KEM,
                    a.BARCODE,
                    b.GAK00,
                    b.AK00,
                    (b.GAK00 + b.AK00) as STOK_TOTAL,
                    b.TD_OD,
                    b.CAT_OD,
                    b.TGL_OD,
                    b.TGL_KSR,
                    DATEDIFF(b.TGL_KSR, b.TGL_OD) as HARI,
                    b.LPH,
                    b.KLK,
                    b.DTR
                FROM {$cbg}.brg a
                INNER JOIN {$cbg}.brgdt b ON a.KD_BRG = b.KD_BRG
                WHERE a.KD_BRG = :kd_brg
            ";

            $result = DB::select($query, ['kd_brg' => $kdBrg]);

            return response()->json([
                'success' => true,
                'data' => $result[0] ?? null
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getDetailBarang: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Method untuk mendapatkan data berdasarkan range tanggal tertentu
     */
    public function getDataByDateRange(Request $request)
    {
        try {
            $cbg = $request->cbg;
            $startDate = $request->start_date;
            $endDate = $request->end_date;

            if (empty($cbg) || empty($startDate) || empty($endDate)) {
                throw new \Exception('Cabang, tanggal mulai, dan tanggal akhir harus diisi!');
            }

            $this->validateCabang($cbg);

            // Override request untuk menggunakan filter tanggal
            $request->merge([
                'filter_tgl' => true,
                'tgl1' => $startDate,
                'tgl2' => $endDate,
                'hari' => 9999 // Set hari maksimal agar tidak membatasi
            ]);

            $data = $this->getStokNolData($request);

            return response()->json([
                'success' => true,
                'data' => $data,
                'total' => count($data)
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getDataByDateRange: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Helper method untuk logging - tetap mempertahankan dari versi sebelumnya
     */
    private function logActivity($action, $cbg, $sub = '', $recordCount = 0)
    {
        Log::info("StokNol: {$action}", [
            'cbg' => $cbg,
            'sub' => $sub,
            'record_count' => $recordCount,
            'user' => auth()->user()->id ?? 'system',
            'timestamp' => now()
        ]);
    }

    // ===== LEGACY METHODS (untuk backward compatibility) =====

    /**
     * Legacy method dari controller sebelumnya - untuk compatibility
     */
    public function getCekPerubahanLPHReport(Request $request)
    {
        $cbg = Cbg::groupBy('CBG')->get();
        $per = Perid::query()->get();

        session()->put('filter_cbg', $request->cbg);
        session()->put('filter_sub', $request->sub);

        $hasilCekLPH = [];

        if (!empty($request->cbg) && !empty($request->sub)) {
            $hasilCekLPH = $this->getCekPerubahanLPHData($request->cbg, $request->sub);
        }

        return view('oreport_cekperubahanlph.report')->with([
            'cbg' => $cbg,
            'per' => $per,
            'hasilCekLPH' => $hasilCekLPH
        ]);
    }

    private function getCekPerubahanLPHData($cbg, $sub)
    {
        try {
            if (empty($cbg) || empty($sub)) {
                throw new \Exception('Cabang dan SUB harus diisi!');
            }

            $cabangExists = DB::table('tgz.toko')
                ->where('KODE', $cbg)
                ->whereIn('STA', ['MA', 'CB', 'DC'])
                ->exists();

            if (!$cabangExists) {
                throw new \Exception('Cabang tidak valid atau tidak aktif!');
            }

            $hasilData = [];

            $query = "
                SELECT B.CBG, A.SUB, A.KD_BRG, A.NA_BRG, A.KET_UK, A.KET_KEM,
                       A.LPH_TM AS LPH_TMM, A.LPH_TF AS LPH_SOP,
                       A.SP_L, A.SP_LF, A.SP_LZ, B.LPH, B.KLK, B.DTR,
                       B.KDLAKU, B.SRMIN, B.SRMAX, B.SMIN, B.SMAX
                FROM {$cbg}.brg A
                INNER JOIN {$cbg}.brgdt B ON A.KD_BRG = B.KD_BRG
                WHERE A.TD_OD = ''
                  AND B.LPH > 0
                  AND A.SUB = :sub
                ORDER BY A.KD_BRG ASC
            ";

            $barangList = DB::select($query, ['sub' => $sub]);

            foreach ($barangList as $barang) {
                $newCalculation = $this->callPjlLphchProcedure(
                    $barang->KD_BRG,
                    $barang->LPH,
                    $barang->DTR
                );

                $hasChanges = $this->checkForChanges($barang, $newCalculation);

                if ($hasChanges) {
                    $finalCalculation = $this->applyBusinessLogic($barang, $newCalculation, $cbg);

                    $hasilData[] = [
                        'CBG' => $barang->CBG,
                        'SUB' => $barang->SUB,
                        'KD_BRG' => $barang->KD_BRG,
                        'NA_BRG' => $barang->NA_BRG,
                        'KET_UK' => $barang->KET_UK,
                        'KET_KEM' => $barang->KET_KEM,
                        'LPH' => $barang->LPH,
                        'KLK' => $barang->KLK,
                        'DTR' => $barang->DTR,
                        'KDLAKU' => $barang->KDLAKU,
                        'SMIN_OLD' => $barang->SMIN,
                        'SMAX_OLD' => $barang->SMAX,
                        'SRMIN_OLD' => $barang->SRMIN,
                        'SRMAX_OLD' => $barang->SRMAX,
                        'KDLAKU_OLD' => $barang->KDLAKU,
                        'SMIN_NEW' => $finalCalculation['smin'],
                        'SMAX_NEW' => $finalCalculation['smax'],
                        'SRMIN_NEW' => $finalCalculation['srmin'],
                        'SRMAX_NEW' => $finalCalculation['srmax'],
                        'KDLAKU_NEW' => $finalCalculation['kdlaku'],
                        'IS_SMIN_CHANGED' => ($barang->SMIN != $finalCalculation['smin']),
                        'IS_SMAX_CHANGED' => ($barang->SMAX != $finalCalculation['smax']),
                        'IS_SRMIN_CHANGED' => ($barang->SRMIN != $finalCalculation['srmin']),
                        'IS_SRMAX_CHANGED' => ($barang->SRMAX != $finalCalculation['srmax']),
                        'IS_KDLAKU_CHANGED' => ($barang->KDLAKU != $finalCalculation['kdlaku'])
                    ];
                }
            }

            return $hasilData;
        } catch (\Exception $e) {
            Log::error('Error in getCekPerubahanLPHData: ' . $e->getMessage());
            throw $e;
        }
    }

    private function callPjlLphchProcedure($kdBrg, $lph, $dtr)
    {
        try {
            $result = DB::select('CALL pjl_lphch(?, ?, ?, ?)', [
                $kdBrg,
                '',
                $lph,
                $dtr
            ]);

            if (!empty($result)) {
                return (array) $result[0];
            }

            return $this->calculateLPHValues($kdBrg, $lph, $dtr);
        } catch (\Exception $e) {
            Log::warning('Stored procedure pjl_lphch not found or failed: ' . $e->getMessage());
            return $this->calculateLPHValues($kdBrg, $lph, $dtr);
        }
    }

    private function calculateLPHValues($kdBrg, $lph, $dtr)
    {
        $smin = 0;
        $smax = 0;
        $srmin = 0;
        $srmax = 0;
        $kdlaku = '';

        if ($lph > 0 && $dtr > 0) {
            $smin = round($lph * $dtr * 0.8, 0);
            $smax = round($lph * $dtr * 1.2, 0);
            $srmin = round($lph * $dtr * 0.6, 0);
            $srmax = round($lph * $dtr * 1.5, 0);

            if ($lph >= 10) {
                $kdlaku = '2';
            } elseif ($lph >= 5) {
                $kdlaku = '3';
            } else {
                $kdlaku = '4';
            }
        }

        return [
            'SMIN' => $smin,
            'SMAX' => $smax,
            'SRMIN' => $srmin,
            'SRMAX' => $srmax,
            'KDLAKU' => $kdlaku
        ];
    }

    private function checkForChanges($barangLama, $barangBaru)
    {
        return (
            $barangLama->SMIN != $barangBaru['SMIN'] ||
            $barangLama->SMAX != $barangBaru['SMAX'] ||
            $barangLama->SRMIN != $barangBaru['SRMIN'] ||
            $barangLama->SRMAX != $barangBaru['SRMAX'] ||
            $barangLama->KDLAKU != $barangBaru['KDLAKU']
        );
    }

    private function applyBusinessLogic($barang, $newCalculation, $cbg)
    {
        $smin = $newCalculation['SMIN'];
        $smax = $newCalculation['SMAX'];
        $srmin = $newCalculation['SRMIN'];
        $srmax = $newCalculation['SRMAX'];
        $kdlaku = $newCalculation['KDLAKU'];

        // Logic khusus untuk DCK (sesuai dengan kondisi di Delphi)
        if (trim($cbg) == 'DCK') {
            return [
                'smin' => 0,
                'smax' => 0,
                'srmin' => $smin,
                'srmax' => $smax,
                'kdlaku' => $kdlaku
            ];
        }

        // Logic untuk cabang selain DCK
        // Jika kdlaku 4, 5, atau 6, maka smin dan smax = 0
        if (in_array($kdlaku, ['4', '5', '6'])) {
            $smin = 0;
            $smax = 0;
        }

        return [
            'smin' => $smin,
            'smax' => $smax,
            'srmin' => $srmin,
            'srmax' => $srmax,
            'kdlaku' => $kdlaku
        ];
    }

    /**
     * Get daftar SUB untuk dropdown - legacy method
     */
    public function getSubList($cbg)
    {
        try {
            if (empty($cbg)) {
                return [];
            }

            $query = "
                SELECT DISTINCT A.SUB
                FROM {$cbg}.brg A
                INNER JOIN {$cbg}.brgdt B ON A.KD_BRG = B.KD_BRG
                WHERE A.TD_OD = ''
                  AND B.LPH > 0
                  AND A.SUB IS NOT NULL
                  AND A.SUB != ''
                ORDER BY A.SUB ASC
            ";

            return DB::select($query);
        } catch (\Exception $e) {
            Log::error('Error in getSubList: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Legacy jasper report method untuk cek perubahan LPH
     */
    public function jasperCekPerubahanLPHReport(Request $request)
    {
        try {
            $file = 'cekperubahanlph';
            $PHPJasperXML = new PHPJasperXML();
            $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

            session()->put('filter_cbg', $request->cbg);
            session()->put('filter_sub', $request->sub);

            $data = [];

            if (!empty($request->cbg) && !empty($request->sub)) {
                $hasilCekLPH = $this->getCekPerubahanLPHData($request->cbg, $request->sub);

                foreach ($hasilCekLPH as $item) {
                    $data[] = [
                        'CBG' => $item['CBG'] ?? '',
                        'SUB' => $item['SUB'] ?? '',
                        'KD_BRG' => $item['KD_BRG'] ?? '',
                        'NA_BRG' => $item['NA_BRG'] ?? '',
                        'KET_UK' => $item['KET_UK'] ?? '',
                        'KET_KEM' => $item['KET_KEM'] ?? '',
                        'LPH' => $item['LPH'] ?? 0,
                        'KDLAKU_OLD' => $item['KDLAKU_OLD'] ?? '',
                        'SMIN_OLD' => $item['SMIN_OLD'] ?? 0,
                        'SMAX_OLD' => $item['SMAX_OLD'] ?? 0,
                        'SRMIN_OLD' => $item['SRMIN_OLD'] ?? 0,
                        'SRMAX_OLD' => $item['SRMAX_OLD'] ?? 0,
                        'KDLAKU_NEW' => $item['KDLAKU_NEW'] ?? '',
                        'SMIN_NEW' => $item['SMIN_NEW'] ?? 0,
                        'SMAX_NEW' => $item['SMAX_NEW'] ?? 0,
                        'SRMIN_NEW' => $item['SRMIN_NEW'] ?? 0,
                        'SRMAX_NEW' => $item['SRMAX_NEW'] ?? 0,
                        'IS_SMIN_CHANGED' => $item['IS_SMIN_CHANGED'] ? 'Y' : 'N',
                        'IS_SMAX_CHANGED' => $item['IS_SMAX_CHANGED'] ? 'Y' : 'N',
                        'IS_SRMIN_CHANGED' => $item['IS_SRMIN_CHANGED'] ? 'Y' : 'N',
                        'IS_SRMAX_CHANGED' => $item['IS_SRMAX_CHANGED'] ? 'Y' : 'N',
                        'IS_KDLAKU_CHANGED' => $item['IS_KDLAKU_CHANGED'] ? 'Y' : 'N',
                    ];
                }
            }

            $PHPJasperXML->setData($data);
            ob_end_clean();
            $PHPJasperXML->outpage("I");
        } catch (\Exception $e) {
            Log::error('Error in jasperCekPerubahanLPHReport: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ===== UTILITY METHODS =====

    /**
     * Method untuk mengecek koneksi database cabang
     */
    public function checkCabangConnection($cbg)
    {
        try {
            $this->validateCabang($cbg);

            // Test query sederhana untuk cek koneksi
            $testQuery = "SELECT COUNT(*) as total FROM {$cbg}.brg LIMIT 1";
            $result = DB::select($testQuery);

            return response()->json([
                'success' => true,
                'connected' => true,
                'message' => 'Koneksi database cabang berhasil'
            ]);
        } catch (\Exception $e) {
            Log::error('Error checking cabang connection: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'connected' => false,
                'message' => 'Gagal terhubung ke database cabang: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Method untuk mendapatkan statistik stok per kategori
     */
    public function getStatistikStok(Request $request)
    {
        try {
            $this->validateInput($request);

            $cbg = $request->cbg;
            $hari = $request->hari ?? 9999;
            $filterTgl = $request->filter_tgl ?? false;
            $tgl1 = $request->tgl1 ?? Carbon::today()->format('Y-m-d');
            $tgl2 = $request->tgl2 ?? Carbon::today()->format('Y-m-d');

            $filterTglQuery = '';
            $params = ['hari' => $hari];

            if ($filterTgl) {
                $filterTglQuery = ' AND DATE(b.TGL_KSR) BETWEEN :tgl1 AND :tgl2 ';
                $params['tgl1'] = $tgl1;
                $params['tgl2'] = $tgl2;
            }

            // Query statistik berdasarkan kategori OD
            $statistikQuery = "
                SELECT
                    b.CAT_OD as KATEGORI,
                    COUNT(*) as JUMLAH_ITEM,
                    SUM(CASE WHEN (b.GAK00 + b.AK00) < 0 THEN 1 ELSE 0 END) as STOK_NEGATIF,
                    SUM(CASE WHEN (b.GAK00 + b.AK00) = 0 THEN 1 ELSE 0 END) as STOK_NOL,
                    AVG(DATEDIFF(b.TGL_KSR, b.TGL_OD)) as AVG_HARI,
                    MAX(DATEDIFF(b.TGL_KSR, b.TGL_OD)) as MAX_HARI
                FROM {$cbg}.brg a
                INNER JOIN {$cbg}.brgdt b ON a.KD_BRG = b.KD_BRG
                WHERE (b.GAK00 + b.AK00) <= 0
                  AND b.TD_OD = '*'
                  AND DATE(b.TGL_OD) <= DATE(b.TGL_KSR)
                  AND LEFT(a.NA_BRG, 1) != '#'
                  AND DATEDIFF(b.TGL_KSR, b.TGL_OD) <= :hari
                  {$filterTglQuery}
                GROUP BY b.CAT_OD
                ORDER BY JUMLAH_ITEM DESC
            ";

            $statistik = DB::select($statistikQuery, $params);

            return response()->json([
                'success' => true,
                'statistik' => $statistik
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getStatistikStok: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Method untuk mendapatkan data paginasi
     */
    public function getStokNolPaginated(Request $request)
    {
        try {
            $this->validateInput($request);

            $cbg = $request->cbg;
            $hari = $request->hari ?? 9999;
            $filterTgl = $request->filter_tgl ?? false;
            $tgl1 = $request->tgl1 ?? Carbon::today()->format('Y-m-d');
            $tgl2 = $request->tgl2 ?? Carbon::today()->format('Y-m-d');
            $page = $request->page ?? 1;
            $limit = $request->limit ?? 50;
            $offset = ($page - 1) * $limit;

            $filterTglQuery = '';
            $params = ['hari' => $hari, 'limit' => $limit, 'offset' => $offset];

            if ($filterTgl) {
                $filterTglQuery = ' AND DATE(b.TGL_KSR) BETWEEN :tgl1 AND :tgl2 ';
                $params['tgl1'] = $tgl1;
                $params['tgl2'] = $tgl2;
            }

            // Query dengan pagination
            $query = "
                SELECT * FROM (
                    SELECT
                        a.KD_BRG,
                        a.NA_BRG,
                        a.KET_UK,
                        a.KET_KEM,
                        a.BARCODE,
                        (b.GAK00 + b.AK00) as STOK,
                        b.TD_OD,
                        b.CAT_OD,
                        DATE(b.TGL_OD) as TGL_OD,
                        DATE(b.TGL_KSR) as TGL_KSR,
                        DATEDIFF(b.TGL_KSR, b.TGL_OD) as HARI
                    FROM {$cbg}.brg a
                    INNER JOIN {$cbg}.brgdt b ON a.KD_BRG = b.KD_BRG
                    WHERE (b.GAK00 + b.AK00) <= 0
                      AND b.TD_OD = '*'
                      AND DATE(b.TGL_OD) <= DATE(b.TGL_KSR)
                      AND LEFT(a.NA_BRG, 1) != '#'
                      {$filterTglQuery}
                ) as rekap
                WHERE HARI <= :hari
                ORDER BY TGL_OD DESC
                LIMIT :limit OFFSET :offset
            ";

            // Query untuk total count
            $countQuery = "
                SELECT COUNT(*) as total FROM (
                    SELECT a.KD_BRG
                    FROM {$cbg}.brg a
                    INNER JOIN {$cbg}.brgdt b ON a.KD_BRG = b.KD_BRG
                    WHERE (b.GAK00 + b.AK00) <= 0
                      AND b.TD_OD = '*'
                      AND DATE(b.TGL_OD) <= DATE(b.TGL_KSR)
                      AND LEFT(a.NA_BRG, 1) != '#'
                      AND DATEDIFF(b.TGL_KSR, b.TGL_OD) <= :hari
                      {$filterTglQuery}
                ) as rekap
            ";

            $countParams = ['hari' => $hari];
            if ($filterTgl) {
                $countParams['tgl1'] = $tgl1;
                $countParams['tgl2'] = $tgl2;
            }

            $result = DB::select($query, $params);
            $totalCount = DB::select($countQuery, $countParams)[0]->total ?? 0;

            return response()->json([
                'success' => true,
                'data' => $result,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $limit,
                    'total' => $totalCount,
                    'last_page' => ceil($totalCount / $limit)
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getStokNolPaginated: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Method untuk mendapatkan data berdasarkan filter kode barang
     */
    public function getDataByKodeBarang(Request $request)
    {
        try {
            $cbg = $request->cbg;
            $kodeBarang = $request->kode_barang;

            if (empty($cbg)) {
                throw new \Exception('Cabang harus dipilih!');
            }

            $this->validateCabang($cbg);

            // Build query dengan filter kode barang
            $query = "
                SELECT * FROM (
                    SELECT
                        a.KD_BRG,
                        a.NA_BRG,
                        a.KET_UK,
                        a.KET_KEM,
                        a.BARCODE,
                        (b.GAK00 + b.AK00) as STOK,
                        b.TD_OD,
                        b.CAT_OD,
                        DATE(b.TGL_OD) as TGL_OD,
                        DATE(b.TGL_KSR) as TGL_KSR,
                        DATEDIFF(b.TGL_KSR, b.TGL_OD) as HARI
                    FROM {$cbg}.brg a
                    INNER JOIN {$cbg}.brgdt b ON a.KD_BRG = b.KD_BRG
                    WHERE (b.GAK00 + b.AK00) <= 0
                      AND b.TD_OD = '*'
                      AND DATE(b.TGL_OD) <= DATE(b.TGL_KSR)
                      AND LEFT(a.NA_BRG, 1) != '#'
            ";

            $params = [];

            // Add filter kode barang jika ada
            if (!empty($kodeBarang)) {
                $query .= " AND a.KD_BRG LIKE :kode_barang ";
                $params['kode_barang'] = '%' . $kodeBarang . '%';
            }

            $query .= ") as rekap ORDER BY TGL_OD DESC LIMIT 100";

            $result = DB::select($query, $params);

            return response()->json([
                'success' => true,
                'data' => $result,
                'total' => count($result)
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getDataByKodeBarang: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Method untuk mendapatkan data berdasarkan kategori OD
     */
    public function getDataByKategori(Request $request)
    {
        try {
            $this->validateInput($request);

            $cbg = $request->cbg;
            $kategori = $request->kategori;
            $hari = $request->hari ?? 9999;

            $query = "
                SELECT * FROM (
                    SELECT
                        a.KD_BRG,
                        a.NA_BRG,
                        a.KET_UK,
                        a.KET_KEM,
                        a.BARCODE,
                        (b.GAK00 + b.AK00) as STOK,
                        b.TD_OD,
                        b.CAT_OD,
                        DATE(b.TGL_OD) as TGL_OD,
                        DATE(b.TGL_KSR) as TGL_KSR,
                        DATEDIFF(b.TGL_KSR, b.TGL_OD) as HARI
                    FROM {$cbg}.brg a
                    INNER JOIN {$cbg}.brgdt b ON a.KD_BRG = b.KD_BRG
                    WHERE (b.GAK00 + b.AK00) <= 0
                      AND b.TD_OD = '*'
                      AND DATE(b.TGL_OD) <= DATE(b.TGL_KSR)
                      AND LEFT(a.NA_BRG, 1) != '#'
            ";

            $params = ['hari' => $hari];

            // Add filter kategori jika ada
            if (!empty($kategori)) {
                $query .= " AND b.CAT_OD = :kategori ";
                $params['kategori'] = $kategori;
            }

            $query .= ") as rekap WHERE HARI <= :hari ORDER BY TGL_OD DESC";

            $result = DB::select($query, $params);

            return response()->json([
                'success' => true,
                'data' => $result,
                'total' => count($result)
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getDataByKategori: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Method untuk mendapatkan daftar kategori OD yang tersedia
     */
    public function getKategoriList($cbg)
    {
        try {
            if (empty($cbg)) {
                return response()->json(['error' => 'Cabang harus dipilih!'], 400);
            }

            $this->validateCabang($cbg);

            $query = "
                SELECT DISTINCT b.CAT_OD as KATEGORI, COUNT(*) as JUMLAH
                FROM {$cbg}.brg a
                INNER JOIN {$cbg}.brgdt b ON a.KD_BRG = b.KD_BRG
                WHERE (b.GAK00 + b.AK00) <= 0
                  AND b.TD_OD = '*'
                  AND b.CAT_OD IS NOT NULL
                  AND b.CAT_OD != ''
                GROUP BY b.CAT_OD
                ORDER BY JUMLAH DESC
            ";

            $result = DB::select($query);

            return response()->json([
                'success' => true,
                'kategori' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getKategoriList: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Method untuk backup dan restore functionality (jika diperlukan)
     */
    public function backupStokNolData(Request $request)
    {
        try {
            $this->validateInput($request);

            $data = $this->getStokNolData($request);

            if (empty($data)) {
                throw new \Exception('Tidak ada data untuk di-backup');
            }

            $filename = 'backup_stoknol_' . $request->cbg . '_' . Carbon::now()->format('YmdHis') . '.json';
            $filepath = storage_path('app/backups/' . $filename);

            // Ensure directory exists
            $directory = dirname($filepath);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            // Save as JSON for easy restore
            $backupData = [
                'created_at' => Carbon::now()->toISOString(),
                'cbg' => $request->cbg,
                'filters' => [
                    'hari' => $request->hari ?? 9999,
                    'filter_tgl' => $request->filter_tgl ?? false,
                    'tgl1' => $request->tgl1 ?? '',
                    'tgl2' => $request->tgl2 ?? ''
                ],
                'data' => $data
            ];

            file_put_contents($filepath, json_encode($backupData, JSON_PRETTY_PRINT));

            $this->logActivity('backup_data', $request->cbg, '', count($data));

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil di-backup',
                'filename' => $filename,
                'total_records' => count($data)
            ]);
        } catch (\Exception $e) {
            Log::error('Error in backupStokNolData: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
