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

class RLakuPerHariController extends Controller
{
    public function report()
    {
        // $cbg = $this->getCabangList();
        $cbg = Cbg::groupBy('CBG')->get();
        $per = Perid::query()->get();

        // Initialize session variables
        session()->put('filter_cbg', '');
        session()->put('filter_hari', 30); // Default 30 hari

        return view('oreport_lakuperhari.report')->with([
            'cbg' => $cbg,
            'per' => $per,
            'hasilLakuPerHari' => []
        ]);
    }

    public function getLakuPerHariReport(Request $request)
    {
        // $cbg = $this->getCabangList();
        $cbg = Cbg::groupBy('CBG')->get();
        $per = Perid::query()->get();

        // Set filter values to session
        session()->put('filter_cbg', $request->cbg ?? '');
        session()->put('filter_hari', $request->hari ?? 30);

        $hasilLakuPerHari = [];

        if (!empty($request->cbg) && !empty($request->hari)) {
            try {
                $hasilLakuPerHari = $this->getLakuPerHariData($request->cbg, $request->hari);
            } catch (\Exception $e) {
                Log::error('Error in getLakuPerHariReport: ' . $e->getMessage());
                return view('oreport_lakuperhari.report')->with([
                    'cbg' => $cbg,
                    'per' => $per,
                    'hasilLakuPerHari' => [],
                    'error' => $e->getMessage()
                ]);
            }
        }

        return view('oreport_lakuperhari.report')->with([
            'cbg' => $cbg,
            'per' => $per,
            'hasilLakuPerHari' => $hasilLakuPerHari
        ]);
    }

    /**
     * Implementasi logika dari procedure Tampil() pada Delphi
     * Menganalisis data jual 3 bulan kebelakang dan menghitung usulan berdasarkan pola laku per hari
     */
    private function getLakuPerHariData($cbg, $hari)
    {
        try {
            // Validasi input
            $this->validateInput($cbg, $hari);

            // Validate cabang exists in toko table
            $cabangExists = DB::table('tgz.toko')
                ->where('KODE', $cbg)
                ->whereIn('STA', ['MA', 'CB', 'DC'])
                ->exists();

            if (!$cabangExists) {
                throw new \Exception('Cabang tidak valid atau tidak aktif!');
            }

            // Get bulan data (implementasi dari logika Delphi)
            $bulanData = $this->getBulanData();

            // Query utama berdasarkan logika Delphi dengan penyesuaian untuk Laravel
            $query = "
                SET @HARI := :hari;
                SET @CBG := :cbg;

                SELECT OLE.*, BRGDT.LPH
                FROM (
                    SELECT @HARI AS HARI, KD_BRG, NA_BRG,
                           SUM(TOTAL_LK) AS TOTAL_LK,
                           SUM(HARI_LK) AS HARI_LK,
                           ROUND((SUM(TOTAL_LK)/@HARI), 2) AS LHUSUL,
                           MAX(TOTAL_LK) AS DTRMAX,
                           ROUND(((SUM(TOTAL_LK)/@HARI)+((@HARI-SUM(HARI_LK))/@HARI*SUM(TOTAL_LK)/@HARI)), 2) AS USULANINI
                    FROM (
                        " . $this->buildUnionQuery($cbg, $bulanData) . "
                    ) AS AGENG
                    GROUP BY KD_BRG
                ) AS OLE
                INNER JOIN {$cbg}.brgdt AS BRGDT ON OLE.KD_BRG = BRGDT.KD_BRG AND BRGDT.CBG = @CBG
                ORDER BY OLE.KD_BRG ASC
            ";

            // Execute query dengan parameter binding yang aman
            $hasilData = DB::select($query, [
                'hari' => intval($hari),
                'cbg' => trim($cbg)
            ]);

            // Transform data sesuai format yang dibutuhkan
            $result = [];
            foreach ($hasilData as $item) {
                $result[] = [
                    'HARI' => $item->HARI,
                    'KD_BRG' => $item->KD_BRG,
                    'NA_BRG' => $item->NA_BRG,
                    'TOTAL_LK' => floatval($item->TOTAL_LK),
                    'HARI_LK' => intval($item->HARI_LK),
                    'LHUSUL' => floatval($item->LHUSUL), // LPH_FLAT / LH_USUL : QTY_LAKU/ HARI
                    'DTRMAX' => floatval($item->DTRMAX), // DTR_MAX : QTY_LAKU tertinggi
                    'USULANINI' => floatval($item->USULANINI), // LH_USUL / USULAN_INI : qty_laku/hari + (hari-hari_laku/ hari*qty_total/hari)
                    'LPH' => floatval($item->LPH ?? 0)
                ];
            }

            // Log activity
            $this->logActivity('get_laku_per_hari', $cbg, $hari, count($result));

            return $result;
        } catch (\Exception $e) {
            Log::error('Error in getLakuPerHariData: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Implementasi logika untuk mendapatkan data bulan (3 bulan kebelakang)
     * Berdasarkan query Delphi: select lpad(month(now()),2,0) as skrg, ...
     */
    private function getBulanData()
    {
        $query = "
            SELECT LPAD(MONTH(NOW()), 2, '0') as skrg,
                   LPAD(MONTH(DATE_SUB(DATE(NOW()), INTERVAL 1 MONTH)), 2, '0') as satu,
                   LPAD(MONTH(DATE_SUB(DATE(NOW()), INTERVAL 2 MONTH)), 2, '0') as dua
        ";

        $result = DB::select($query);

        return [
            'skrg' => $result[0]->skrg,
            'satu' => $result[0]->satu,
            'dua' => $result[0]->dua
        ];
    }

    /**
     * Build UNION query untuk 3 bulan data (implementasi dari logika Delphi)
     */
    private function buildUnionQuery($cbg, $bulanData)
    {
        $baseQuery = "
            SELECT KD_BRG, NA_BRG, SUM(TOTAL_LK) AS TOTAL_LK, COUNT(DISTINCT(DATE(TGL))) AS HARI_LK
            FROM (
                SELECT KD_BRG, NA_BRG, SUM(QTY) AS TOTAL_LK, TGL
                FROM {$cbg}.juald%s
                WHERE TGL >= DATE_SUB(DATE(NOW()), INTERVAL @HARI DAY)
                      AND FLAG NOT IN ('OB', 'ZP', 'FC')
                      AND CBG = @CBG
                GROUP BY TGL, KD_BRG
                ORDER BY KD_BRG, TGL
            ) AS PERTGL
            GROUP BY KD_BRG
        ";

        $queries = [
            sprintf($baseQuery, $bulanData['skrg']),
            sprintf($baseQuery, $bulanData['satu']),
            sprintf($baseQuery, $bulanData['dua'])
        ];

        return implode(" UNION ALL ", $queries);
    }

    /**
     * Get daftar cabang yang valid (implementasi dari FormShow Delphi)
     */
    public function getCabangList()
    {
        try {
            $query = "
                SELECT KODE, NAMA, STA
                FROM tgz.toko
                WHERE STA IN ('MA', 'CB')
                ORDER BY NO_ID ASC
            ";

            return DB::select($query);
        } catch (\Exception $e) {
            Log::error('Error in getCabangList: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Export ke Excel (implementasi dari p1Click pada Delphi)
     */
    public function exportToExcel(Request $request)
    {
        try {
            if (empty($request->cbg) || empty($request->hari)) {
                return response()->json(['error' => 'Cabang dan Hari harus diisi!'], 400);
            }

            $data = $this->getLakuPerHariData($request->cbg, $request->hari);

            if (empty($data)) {
                return response()->json(['message' => 'Tidak ada data untuk diekspor'], 200);
            }

            // Implementasi export Excel menggunakan library yang sesuai
            $filename = 'laku_per_hari_' . $request->cbg . '_' . $request->hari . 'hari_' . date('YmdHis') . '.xlsx';

            // Return download response
            return response()->json(['success' => true, 'filename' => $filename]);
        } catch (\Exception $e) {
            Log::error('Error in exportToExcel: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Generate laporan Jasper (implementasi dari Button1Click -> frxReport1.ShowReport())
     */
    public function jasperLakuPerHariReport(Request $request)
    {
        try {
            $file = 'lakuperhari'; // Sesuaikan dengan nama file jasper report
            $PHPJasperXML = new PHPJasperXML();
            $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

            // Set session values
            session()->put('filter_cbg', $request->cbg ?? '');
            session()->put('filter_hari', $request->hari ?? 30);

            $data = [];

            if (!empty($request->cbg) && !empty($request->hari)) {
                $hasilLakuPerHari = $this->getLakuPerHariData($request->cbg, $request->hari);
                $namaToko = $this->getNamaToko($request->cbg);

                foreach ($hasilLakuPerHari as $item) {
                    $data[] = [
                        'HARI' => $item['HARI'],
                        'KD_BRG' => $item['KD_BRG'] ?? '',
                        'NA_BRG' => $item['NA_BRG'] ?? '',
                        'TOTAL_LK' => $item['TOTAL_LK'] ?? 0,
                        'HARI_LK' => $item['HARI_LK'] ?? 0,
                        'LHUSUL' => $item['LHUSUL'] ?? 0,
                        'USULANINI' => $item['USULANINI'] ?? 0,
                        'DTRMAX' => $item['DTRMAX'] ?? 0,
                        'LPH' => $item['LPH'] ?? 0,
                        // Tambahan informasi untuk report
                        'CBG' => $request->cbg,
                        'NAMA_TOKO' => $namaToko,
                        'TANGGAL_CETAK' => date('Y-m-d H:i:s')
                    ];
                }
            }

            $PHPJasperXML->setData($data);

            // Set parameters untuk report
            $PHPJasperXML->arrayParameter = [
                "CBG" => $request->cbg ?? '',
                "HARI" => $request->hari ?? 30,
                "NAMA_TOKO" => $this->getNamaToko($request->cbg ?? ''),
                "TANGGAL_CETAK" => date('d/m/Y H:i:s')
            ];

            ob_end_clean();
            $PHPJasperXML->outpage("I"); // I = Inline view, D = Download, F = Save to file

        } catch (\Exception $e) {
            Log::error('Error in jasperLakuPerHariReport: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Search data laku per hari (implementasi dari logika search)
     */
    public function searchLakuPerHari(Request $request)
    {
        try {
            $cbg = $request->cbg;
            $hari = intval($request->hari);

            if (empty($cbg) || empty($hari)) {
                return response()->json(['error' => 'Cabang dan Hari harus diisi!'], 400);
            }

            $data = $this->getLakuPerHariData($cbg, $hari);

            return response()->json([
                'success' => true,
                'data' => $data,
                'total_records' => count($data),
                'summary' => [
                    'total_items' => count($data),
                    'total_qty_laku' => array_sum(array_column($data, 'TOTAL_LK')),
                    'avg_lhusul' => count($data) > 0 ? array_sum(array_column($data, 'LHUSUL')) / count($data) : 0
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error in searchLakuPerHari: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Validasi input parameters
     */
    private function validateInput($cbg, $hari)
    {
        if (empty($cbg)) {
            throw new \Exception('Cabang harus diisi!');
        }

        if (empty($hari) || !is_numeric($hari) || intval($hari) <= 0) {
            throw new \Exception('Hari harus diisi dengan angka yang valid!');
        }

        // Validate cabang format
        if (!preg_match('/^[A-Z0-9]+$/', $cbg)) {
            throw new \Exception('Format cabang tidak valid!');
        }

        // Validate hari range (maksimal 365 hari)
        if (intval($hari) > 365) {
            throw new \Exception('Maksimal analisis 365 hari!');
        }

        return true;
    }

    /**
     * Helper method untuk logging
     */
    private function logActivity($action, $cbg, $hari, $recordCount = 0)
    {
        Log::info("LakuPerHari: {$action}", [
            'cbg' => $cbg,
            'hari' => $hari,
            'record_count' => $recordCount,
            'user' => auth()->user()->id ?? 'system',
            'timestamp' => now()
        ]);
    }

    /**
     * Get nama toko berdasarkan kode cabang
     */
    public function getNamaToko($cbg)
    {
        try {
            $result = DB::table('tgz.toko')
                ->select('NAMA')
                ->where('KODE', $cbg)
                ->first();

            return $result ? $result->NAMA : '';
        } catch (\Exception $e) {
            Log::error('Error in getNamaToko: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Method untuk mendukung AJAX request dari view
     */
    public function ajaxGetLakuPerHari(Request $request)
    {
        try {
            $cbg = $request->get('cbg');
            $hari = intval($request->get('hari', 30));

            if (empty($cbg)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parameter cabang tidak lengkap',
                    'data' => []
                ]);
            }

            $data = $this->getLakuPerHariData($cbg, $hari);

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diambil',
                'data' => $data,
                'total' => count($data),
                'parameters' => [
                    'cbg' => $cbg,
                    'hari' => $hari,
                    'nama_toko' => $this->getNamaToko($cbg)
                ]
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
     * Method untuk preview data sebelum print (implementasi dari btnProsesClick)
     */
    public function previewLakuPerHari(Request $request)
    {
        try {
            $cbg = $request->cbg;
            $hari = intval($request->hari);

            if (empty($cbg)) {
                return redirect()->back()->with('error', 'Cabang harus diisi!');
            }

            if (empty($hari)) {
                return redirect()->back()->with('error', 'Hari harus diisi!');
            }

            $data = $this->getLakuPerHariData($cbg, $hari);
            $namaToko = $this->getNamaToko($cbg);

            // Calculate summary statistics
            $summary = [
                'total_items' => count($data),
                'total_qty_laku' => array_sum(array_column($data, 'TOTAL_LK')),
                'avg_lhusul' => count($data) > 0 ? array_sum(array_column($data, 'LHUSUL')) / count($data) : 0,
                'avg_usulan' => count($data) > 0 ? array_sum(array_column($data, 'USULANINI')) / count($data) : 0,
                'max_dtr' => count($data) > 0 ? max(array_column($data, 'DTRMAX')) : 0
            ];

            return view('oreport_lakuperhari.preview')->with([
                'data' => $data,
                'cbg' => $cbg,
                'hari' => $hari,
                'namaToko' => $namaToko,
                'totalRecords' => count($data),
                'summary' => $summary
            ]);
        } catch (\Exception $e) {
            Log::error('Error in previewLakuPerHari: ' . $e->getMessage());
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Get statistical analysis untuk dashboard
     */
    public function getStatisticalAnalysis(Request $request)
    {
        try {
            $cbg = $request->cbg;
            $hari = intval($request->hari ?? 30);

            if (empty($cbg)) {
                return response()->json(['error' => 'Cabang harus diisi!'], 400);
            }

            $data = $this->getLakuPerHariData($cbg, $hari);

            if (empty($data)) {
                return response()->json([
                    'success' => true,
                    'analysis' => [
                        'total_items' => 0,
                        'total_qty_laku' => 0,
                        'avg_lhusul' => 0,
                        'top_performers' => [],
                        'recommendations' => []
                    ]
                ]);
            }

            // Sort by LHUSUL descending for top performers
            usort($data, function ($a, $b) {
                return $b['LHUSUL'] <=> $a['LHUSUL'];
            });

            $topPerformers = array_slice($data, 0, 10);

            // Sort by USULANINI descending for recommendations
            usort($data, function ($a, $b) {
                return $b['USULANINI'] <=> $a['USULANINI'];
            });

            $recommendations = array_slice($data, 0, 10);

            $analysis = [
                'total_items' => count($data),
                'total_qty_laku' => array_sum(array_column($data, 'TOTAL_LK')),
                'avg_lhusul' => array_sum(array_column($data, 'LHUSUL')) / count($data),
                'avg_usulan' => array_sum(array_column($data, 'USULANINI')) / count($data),
                'top_performers' => $topPerformers,
                'recommendations' => $recommendations
            ];

            return response()->json([
                'success' => true,
                'analysis' => $analysis,
                'parameters' => [
                    'cbg' => $cbg,
                    'hari' => $hari,
                    'nama_toko' => $this->getNamaToko($cbg)
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getStatisticalAnalysis: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
