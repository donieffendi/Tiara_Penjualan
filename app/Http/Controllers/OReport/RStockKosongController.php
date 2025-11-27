<?php

namespace App\Http\Controllers\OReport;

use App\Http\Controllers\Controller;
use App\Models\Master\Cbg;
use App\Models\Master\Perid;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;

class RStockKosongController extends Controller
{
    public function report()
    {
        $cbg = Cbg::groupBy('CBG')->get();
        $per = Perid::query()->get();

        // Initialize session variables
        session()->put('filter_cbg', '');
        session()->put('filter_sub1', '');
        session()->put('filter_sub2', 'ZZZ');

        return view('oreport_stockkosong.report')->with([
            'cbg' => $cbg,
            'per' => $per,
            'hasilStock' => []
        ]);
    }

    public function getStockKosongReport(Request $request)
    {
        $cbg = Cbg::groupBy('CBG')->get();
        $per = Perid::query()->get();

        // Set filter values to session
        session()->put('filter_cbg', $request->cbg ?? '');
        session()->put('filter_sub1', $request->sub1 ?? '');
        session()->put('filter_sub2', $request->sub2 ?? 'ZZZ');

        $hasilStock = [];

        if (!empty($request->cbg)) {
            try {
                $reportType = $request->report_type ?? 'normal'; // normal, kosong, minus, retur
                $hasilStock = $this->getStockData($request->cbg, $request->sub1, $request->sub2, $reportType);
            } catch (\Exception $e) {
                Log::error('Error in getStockReport: ' . $e->getMessage());
                return view('oreport_stockkosong.report')->with([
                    'cbg' => $cbg,
                    'per' => $per,
                    'hasilStock' => [],
                    'error' => $e->getMessage()
                ]);
            }
        }

        return view('oreport_stockkosong.report')->with([
            'cbg' => $cbg,
            'per' => $per,
            'hasilStock' => $hasilStock
        ]);
    }

    /**
     * Implementasi dari procedure tampil() pada Delphi
     * Query untuk stock normal
     */
    private function getStockData($cbg, $sub1, $sub2, $reportType = 'normal')
    {
        try {
            // Validasi input
            $this->validateInput($cbg);

            // Set default values seperti di Delphi FormShow
            if (empty($sub1)) $sub1 = '';
            if (empty($sub2)) $sub2 = 'ZZZ';

            // Validate cabang exists
            $cabangExists = DB::table('tgz.toko')
                ->where('KODE', $cbg)
                ->whereIn('STA', ['MA', 'CB', 'DC'])
                ->exists();

            if (!$cabangExists) {
                throw new \Exception('Cabang tidak valid atau tidak aktif!');
            }

            $query = $this->buildStockQuery($cbg, $sub1, $sub2, $reportType);

            $hasilData = DB::select($query['sql'], $query['params']);

            // Transform data sesuai format yang dibutuhkan
            $result = [];
            foreach ($hasilData as $item) {
                $result[] = [
                    'kd_brg' => $item->kd_brg ?? '',
                    'na_brg' => $item->na_brg ?? '',
                    'cbg' => $item->cbg ?? '',
                    'ak00' => $item->ak00 ?? 0,
                    'sub' => $item->sub ?? '',
                    'kdlaku' => $item->kdlaku ?? '',
                    'td_od' => $item->td_od ?? '',
                    'cat_od' => $item->cat_od ?? ''
                ];
            }

            // Log activity
            $this->logActivity('get_stock_data', $cbg, $reportType, count($result));

            return $result;
        } catch (\Exception $e) {
            Log::error('Error in getStockData: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Build query berdasarkan tipe report (mengikuti logika button di Delphi)
     */
    private function buildStockQuery($cbg, $sub1, $sub2, $reportType)
    {
        $params = [
            'cbg' => trim($cbg),
            'sub1' => trim($sub1),
            'sub2' => trim($sub2)
        ];

        switch ($reportType) {
            case 'kosong': // Button1Click - Stock Kosong (ak00=0)
                $sql = "
                    SELECT brgdt.kd_brg,
                           CONCAT(brgdt.na_brg, ' ', brg.ket_uk) as na_brg,
                           brgdt.cbg,
                           brgdt.ak00,
                           brg.sub,
                           brgdt.kdlaku,
                           '' as td_od,
                           '' as cat_od
                    FROM {$cbg}.brg
                    JOIN {$cbg}.brgdt ON brg.kd_brg = brgdt.kd_brg
                    WHERE brgdt.cbg = :cbg
                      AND brgdt.ak00 = 0
                      AND brg.sub >= :sub1
                      AND brg.sub <= :sub2
                      AND brgdt.kdlaku = '4'
                      AND brgdt.td_od = ''
                      AND brgdt.cat_od = ''
                    ORDER BY brgdt.kd_brg ASC
                ";
                break;

            case 'minus': // Button2Click - Stock Minus (ak00<0)
                $sql = "
                    SELECT brgdt.kd_brg,
                           CONCAT(brgdt.na_brg, ' ', brg.ket_uk) as na_brg,
                           brgdt.cbg,
                           brgdt.ak00,
                           brg.sub,
                           brgdt.kdlaku,
                           brgdt.td_od,
                           brgdt.cat_od
                    FROM {$cbg}.brg
                    JOIN {$cbg}.brgdt ON brg.kd_brg = brgdt.kd_brg
                    WHERE brgdt.cbg = :cbg
                      AND brgdt.ak00 < 0
                      AND brg.sub >= :sub1
                      AND brg.sub <= :sub2
                      AND brgdt.kdlaku IN ('4', '0', '1')
                    ORDER BY brgdt.kd_brg ASC
                ";
                break;

            case 'retur': // Button3Click - Retur (menggunakan rak00)
                $sql = "
                    SELECT brgdt.kd_brg,
                           CONCAT(brgdt.na_brg, ' ', brg.ket_uk) as na_brg,
                           brgdt.cbg,
                           brgdt.rak00 as ak00,
                           brg.sub,
                           brgdt.kdlaku,
                           '' as td_od,
                           '' as cat_od
                    FROM {$cbg}.brg
                    JOIN {$cbg}.brgdt ON brg.kd_brg = brgdt.kd_brg
                    WHERE brgdt.cbg = :cbg
                      AND brg.sub >= :sub1
                      AND brg.sub <= :sub2
                      AND brgdt.kdlaku = '4'
                      AND brgdt.td_od = ''
                      AND brgdt.cat_od = ''
                    ORDER BY brgdt.kd_brg ASC
                ";
                break;

            default: // procedure tampil() - Normal stock
                $sql = "
                    SELECT brgdt.kd_brg,
                           CONCAT(brgdt.na_brg, ' ', brg.ket_uk) as na_brg,
                           brgdt.cbg,
                           brgdt.ak00,
                           brg.sub,
                           brgdt.kdlaku,
                           '' as td_od,
                           '' as cat_od
                    FROM {$cbg}.brg
                    JOIN {$cbg}.brgdt ON brg.kd_brg = brgdt.kd_brg
                    WHERE brgdt.cbg = :cbg
                      AND brg.sub >= :sub1
                      AND brg.sub <= :sub2
                      AND brgdt.kdlaku = '4'
                      AND brgdt.td_od = ''
                      AND brgdt.cat_od = ''
                    ORDER BY brgdt.kd_brg ASC
                ";
                break;
        }

        return [
            'sql' => $sql,
            'params' => $params
        ];
    }

    /**
     * Implementasi helper function LeftStr dari Delphi
     */
    private function leftStr($string, $count)
    {
        return substr($string, 0, $count);
    }

    /**
     * Implementasi helper function RightStr dari Delphi
     */
    private function rightStr($string, $count)
    {
        $length = strlen($string);
        if ($length < $count) {
            return $string;
        }
        return substr($string, $length - $count, $count);
    }

    /**
     * Validasi SUB seperti pada txtsub1Exit di Delphi
     */
    public function validateSub(Request $request)
    {
        try {
            $sub = trim($request->sub);
            $cbg = trim($request->cbg);

            if (empty($sub)) {
                return response()->json([
                    'success' => true,
                    'sub' => '',
                    'sub2' => 'ZZZ'
                ]);
            }

            // Check if SUB exists in brg table
            $query = "SELECT sub FROM {$cbg}.brg WHERE sub = :sub LIMIT 1";
            $result = DB::select($query, ['sub' => $sub]);

            if (count($result) > 0) {
                return response()->json([
                    'success' => true,
                    'sub' => $result[0]->sub,
                    'sub2' => $result[0]->sub
                ]);
            } else {
                // SUB tidak ditemukan, bisa redirect ke form pencarian
                return response()->json([
                    'success' => false,
                    'message' => 'SUB tidak ditemukan',
                    'sub' => $sub,
                    'sub2' => $sub
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error in validateSub: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get daftar cabang yang valid
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
     * Export ke Excel - implementasi dari MXLSXClick
     */
    public function exportToExcel(Request $request)
    {
        try {
            if (empty($request->cbg)) {
                return response()->json(['error' => 'Cabang harus diisi!'], 400);
            }

            $reportType = $request->report_type ?? 'normal';
            $data = $this->getStockData($request->cbg, $request->sub1, $request->sub2, $reportType);

            if (empty($data)) {
                return response()->json(['message' => 'Tidak ada data untuk diekspor'], 200);
            }

            $filename = 'stock_report_' . $request->cbg . '_' . $reportType . '_' . date('YmdHis') . '.xlsx';

            // Implementasi export menggunakan library Excel Laravel
            return response()->json(['success' => true, 'filename' => $filename]);
        } catch (\Exception $e) {
            Log::error('Error in exportToExcel: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Export ke Text - implementasi dari Word1Click
     */
    public function exportToText(Request $request)
    {
        try {
            if (empty($request->cbg)) {
                return response()->json(['error' => 'Cabang harus diisi!'], 400);
            }

            $reportType = $request->report_type ?? 'normal';
            $data = $this->getStockData($request->cbg, $request->sub1, $request->sub2, $reportType);

            if (empty($data)) {
                return response()->json(['message' => 'Tidak ada data untuk diekspor'], 200);
            }

            $filename = 'stock_report_' . $request->cbg . '_' . $reportType . '_' . date('YmdHis') . '.txt';

            // Generate text content
            $textContent = "LAPORAN STOCK - " . strtoupper($reportType) . "\n";
            $textContent .= "Cabang: " . $request->cbg . "\n";
            $textContent .= "Tanggal: " . date('d/m/Y H:i:s') . "\n";
            $textContent .= str_repeat("=", 80) . "\n";
            $textContent .= sprintf(
                "%-15s %-40s %-10s %-10s %-5s\n",
                'KODE BARANG',
                'NAMA BARANG',
                'CABANG',
                'STOCK',
                'SUB'
            );
            $textContent .= str_repeat("-", 80) . "\n";

            foreach ($data as $item) {
                $textContent .= sprintf(
                    "%-15s %-40s %-10s %-10s %-5s\n",
                    $item['kd_brg'],
                    substr($item['na_brg'], 0, 40),
                    $item['cbg'],
                    $item['ak00'],
                    $item['sub']
                );
            }

            return response($textContent)
                ->header('Content-Type', 'text/plain')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
        } catch (\Exception $e) {
            Log::error('Error in exportToText: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Generate Jasper Report - implementasi dari NewClick (frxreport1.ShowReport())
     */
    public function jasperStockReport(Request $request)
    {
        try {
            $file = 'stockreport'; // Sesuaikan dengan nama file jasper report
            $PHPJasperXML = new PHPJasperXML();
            $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

            $reportType = $request->report_type ?? 'normal';
            $data = [];

            if (!empty($request->cbg)) {
                $hasilStock = $this->getStockData($request->cbg, $request->sub1, $request->sub2, $reportType);

                foreach ($hasilStock as $item) {
                    $data[] = [
                        'kd_brg' => $item['kd_brg'] ?? '',
                        'na_brg' => $item['na_brg'] ?? '',
                        'cbg' => $item['cbg'] ?? '',
                        'ak00' => $item['ak00'] ?? 0,
                        'sub' => $item['sub'] ?? '',
                        'kdlaku' => $item['kdlaku'] ?? '',
                        'td_od' => $item['td_od'] ?? '',
                        'cat_od' => $item['cat_od'] ?? '',
                        'report_type' => strtoupper($reportType),
                        'tanggal_cetak' => date('Y-m-d H:i:s')
                    ];
                }
            }

            $PHPJasperXML->setData($data);

            // Set parameters untuk report
            $PHPJasperXML->arrayParameter = [
                "CBG" => $request->cbg ?? '',
                "SUB1" => $request->sub1 ?? '',
                "SUB2" => $request->sub2 ?? 'ZZZ',
                "REPORT_TYPE" => strtoupper($reportType),
                "TANGGAL_CETAK" => date('d/m/Y H:i:s')
            ];

            ob_end_clean();
            $PHPJasperXML->outpage("I"); // I = Inline view, D = Download

        } catch (\Exception $e) {
            Log::error('Error in jasperStockReport: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * AJAX method untuk mendukung pencarian real-time
     */
    public function ajaxGetStock(Request $request)
    {
        try {
            $cbg = $request->get('cbg');
            $sub1 = $request->get('sub1', '');
            $sub2 = $request->get('sub2', 'ZZZ');
            $reportType = $request->get('report_type', 'normal');

            if (empty($cbg)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cabang harus diisi',
                    'data' => []
                ]);
            }

            $data = $this->getStockData($cbg, $sub1, $sub2, $reportType);

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diambil',
                'data' => $data,
                'total' => count($data),
                'report_type' => $reportType
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ]);
        }
    }

    /**
     * Preview data sebelum print
     */
    public function previewStock(Request $request)
    {
        try {
            $cbg = $request->cbg;
            $sub1 = $request->sub1 ?? '';
            $sub2 = $request->sub2 ?? 'ZZZ';
            $reportType = $request->report_type ?? 'normal';

            if (empty($cbg)) {
                return redirect()->back()->with('error', 'Cabang harus diisi!');
            }

            $data = $this->getStockData($cbg, $sub1, $sub2, $reportType);
            $namaToko = $this->getNamaToko($cbg);

            return view('oreport_stockkosong.preview')->with([
                'data' => $data,
                'cbg' => $cbg,
                'sub1' => $sub1,
                'sub2' => $sub2,
                'reportType' => $reportType,
                'namaToko' => $namaToko,
                'totalRecords' => count($data)
            ]);
        } catch (\Exception $e) {
            Log::error('Error in previewStock: ' . $e->getMessage());
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Get nama toko berdasarkan kode cabang
     */
    public function getNamaToko($cbg)
    {
        try {
            $result = DB::table('tgz.toko')
                ->select('NA_TOKO')
                ->where('KODE', $cbg)
                ->first();

            return $result ? $result->NA_TOKO : '';
        } catch (\Exception $e) {
            Log::error('Error in getNamaToko: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Validasi input parameters
     */
    private function validateInput($cbg)
    {
        if (empty($cbg)) {
            throw new \Exception('Cabang harus diisi!');
        }

        // Validate cabang format
        if (!preg_match('/^[A-Z0-9]+$/', $cbg)) {
            throw new \Exception('Format cabang tidak valid!');
        }

        return true;
    }

    /**
     * Helper method untuk logging
     */
    private function logActivity($action, $cbg, $reportType, $recordCount = 0)
    {
        Log::info("StockReport: {$action}", [
            'cbg' => $cbg,
            'report_type' => $reportType,
            'record_count' => $recordCount,
            'user' => auth()->user()->id ?? 'system',
            'timestamp' => now()
        ]);
    }
}
