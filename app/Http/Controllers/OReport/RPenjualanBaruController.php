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

class RPenjualanBaruController extends Controller
{
    /**
     * Halaman utama report - Route: /rpenjualanbaru
     */
    public function report()
    {
        $cbg = Cbg::groupBy('CBG')->get();
        $per = Perid::query()->get();

        // Initialize session variables
        session()->put('filter_cbg', '');
        session()->put('filter_periode', '');
        session()->put('filter_tgl1', '');
        session()->put('filter_tgl2', '');
        session()->put('filter_sup1', '');
        session()->put('filter_sup2', '');
        session()->put('filter_sub1', '');
        session()->put('filter_sub2', '');
        session()->put('filter_kodec1', '');
        session()->put('filter_kodec2', '');
        session()->put('filter_kitir1', '');
        session()->put('filter_kitir2', '');
        session()->put('filter_group_detail', false);

        return view('oreport_penjualanbaru.report')->with([
            'cbg' => $cbg,
            'per' => $per,
            'hasilPenjualanBaru' => []
        ]);
    }

    /**
     * Get data penjualan baru report - Route: /get-penjualanbaru-report
     */
    public function getPenjualanBaruReport(Request $request)
    {
        $cbg = Cbg::groupBy('CBG')->get();
        $per = Perid::query()->get();

        // Set filter values to session
        session()->put('filter_cbg', $request->cbg ?? '');
        session()->put('filter_periode', $request->periode ?? '');
        session()->put('filter_tgl1', $request->tgl1 ?? '');
        session()->put('filter_tgl2', $request->tgl2 ?? '');
        session()->put('filter_sup1', $request->sup1 ?? '');
        session()->put('filter_sup2', $request->sup2 ?? '');
        session()->put('filter_sub1', $request->sub1 ?? '');
        session()->put('filter_sub2', $request->sub2 ?? '');
        session()->put('filter_kodec1', $request->kodec1 ?? '');
        session()->put('filter_kodec2', $request->kodec2 ?? '');
        session()->put('filter_kitir1', $request->kitir1 ?? '');
        session()->put('filter_kitir2', $request->kitir2 ?? '');
        session()->put('filter_group_detail', $request->group_detail ?? false);

        $hasilPenjualanBaru = [];

        if (!empty($request->cbg)) {
            try {
                $hasilPenjualanBaru = $this->getPenjualanBaruData($request);
            } catch (\Exception $e) {
                Log::error('Error in getPenjualanBaruReport: ' . $e->getMessage());
                return view('oreport_penjualanbaru.report')->with([
                    'cbg' => $cbg,
                    'per' => $per,
                    'hasilPenjualanBaru' => [],
                    'error' => $e->getMessage()
                ]);
            }
        }

        return view('oreport_penjualanbaru.report')->with([
            'cbg' => $cbg,
            'per' => $per,
            'hasilPenjualanBaru' => $hasilPenjualanBaru
        ]);
    }

    /**
     * Generate laporan Jasper - Route: /jasper-penjualanbaru-report
     */
    public function jasperPenjualanBaruReport(Request $request)
    {
        try {
            $file = 'penjualanbaru'; // Nama file jasper report
            $PHPJasperXML = new PHPJasperXML();
            $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

            // Set session values
            session()->put('filter_cbg', $request->cbg ?? '');
            session()->put('filter_periode', $request->periode ?? '');
            session()->put('filter_tgl1', $request->tgl1 ?? '');
            session()->put('filter_tgl2', $request->tgl2 ?? '');

            $data = [];

            if (!empty($request->cbg)) {
                $hasilPenjualanBaru = $this->getPenjualanBaruData($request);

                foreach ($hasilPenjualanBaru as $item) {
                    $data[] = [
                        'CBG' => $item['CBG'] ?? '',
                        'NO_BUKTI' => $item['NO_BUKTI'] ?? '',
                        'TGL' => $item['TGL'] ?? '',
                        'KD_BRG' => $item['KD_BRG'] ?? '',
                        'NA_BRG' => $item['NA_BRG'] ?? '',
                        'QTY' => $item['QTY'] ?? 0,
                        'HARGA' => $item['HARGA'] ?? 0,
                        'HARGA_VIP' => $item['HARGA_VIP'] ?? 0,
                        'DISKON' => $item['DISKON'] ?? 0,
                        'DISC' => $item['DISC'] ?? 0,
                        'NPPN' => $item['NPPN'] ?? 0,
                        'DPP' => $item['DPP'] ?? 0,
                        'TOTAL' => $item['TOTAL'] ?? 0,
                        'KET_UK' => $item['KET_UK'] ?? '',
                        'SUPP' => $item['SUPP'] ?? '',
                        'LPH' => $item['LPH'] ?? '',
                        'TANGGAL_CETAK' => date('Y-m-d H:i:s')
                    ];
                }
            }

            $PHPJasperXML->setData($data);

            // Set parameters untuk report
            $PHPJasperXML->arrayParameter = [
                "CBG" => $request->cbg ?? '',
                "PERIODE" => $request->periode ?? '',
                "TGL1" => $request->tgl1 ?? '',
                "TGL2" => $request->tgl2 ?? '',
                "SUP1" => $request->sup1 ?? '',
                "SUP2" => $request->sup2 ?? '',
                "SUB1" => $request->sub1 ?? '',
                "SUB2" => $request->sub2 ?? '',
                "TANGGAL_CETAK" => date('d/m/Y H:i:s'),
                "NAMA_TOKO" => $this->getNamaToko($request->cbg ?? '')
            ];

            if (ob_get_length()) {
                ob_end_clean();
            }
            $PHPJasperXML->outpage("I");

        } catch (\Exception $e) {
            Log::error('Error in jasperPenjualanBaruReport: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Mengimplementasikan logika dari Delphi untuk mendapatkan data penjualan baru
     * Disesuaikan dengan query kompleks dari procedure Delphi
     */
    private function getPenjualanBaruData(Request $request)
    {
        try {
            // Validasi input
            $this->validateInput($request);

            $cbg = strtoupper($request->cbg); // Pastikan uppercase
            $periode = $request->periode ?? '';
            $bulan = !empty($periode) ? $this->leftStr(trim($periode), 2) : '';

            // Pastikan bulan valid (01 s/d 12)
            if (!empty($bulan) && (!is_numeric($bulan) || $bulan < 1 || $bulan > 12)) {
                throw new \Exception('Format bulan pada periode tidak valid!');
            }

            $tableExists = DB::select("SHOW TABLES FROM {$cbg} LIKE 'jual{$bulan}'");
            $tableDExists = DB::select("SHOW TABLES FROM {$cbg} LIKE 'juald{$bulan}'");

            if (!$tableExists || !$tableDExists) {
                throw new \Exception("Tabel penjualan untuk periode {$periode} tidak tersedia!");
            }

            // Set default date range if not provided
            $tgl1 = $request->tgl1;
            $tgl2 = $request->tgl2;

            if (empty($tgl1) || empty($tgl2)) {
                if (!empty($periode)) {
                    [$month, $year] = explode('/', $periode);
                    $tgl1 = Carbon::create($year, $month, 1)->format('Y-m-d');
                    $tgl2 = Carbon::create($year, $month, 1)->endOfMonth()->format('Y-m-d');
                } else {
                    $tgl1 = $tgl2 = date('Y-m-d'); // fallback tanggal hari ini
                }
            }

            // Default values for filters
            $sup1 = $request->sup1 ?? '';
            $sup2 = $request->sup2 ?? 'ZZZZZZ';
            $sub1 = $request->sub1 ?? '';
            $sub2 = $request->sub2 ?? 'ZZZZZZ';
            $kodec1 = $request->kodec1 ?? '';
            $kodec2 = $request->kodec2 ?? 'ZZZZZZ';
            $kitir1 = $request->kitir1 ?? '';
            $kitir2 = $request->kitir2 ?? 'ZZZZZZ';

            $cbg = $request->cbg;
            $groupDetail = $request->group_detail ?? false;

            $additionalFilter = '';
            $groupByClause = '';

            $params = [
                'cbg' => $cbg,
                'sup1' => $request->sup1 ?? '',
                'sup2' => $request->sup2 ?? 'ZZZZZZ',
                'sub1' => $request->sub1 ?? '',
                'sub2' => $request->sub2 ?? 'ZZZZZZ',
                'tgl1' => $tgl1,
                'tgl2' => $tgl2,
            ];

            if ($groupDetail) {
                $additionalFilter = "
                    AND jual{$bulan}.no_bukti >= '$kitir1'
                    AND jual{$bulan}.no_bukti <= '$kitir2'
                    AND jual{$bulan}.kodec >= '$kodec1'
                    AND jual{$bulan}.kodec <= '$kodec2'
                ";
                $groupByClause = "GROUP BY juald{$bulan}.no_bukti, juald{$bulan}.KD_BRG";

            } else {
                $groupByClause = "GROUP BY juald{$bulan}.KD_BRG";
            }


            // Build the main query following Delphi logic exactly
            $sql = "
                SELECT AA.*, brg.KET_UK, brg.supp, CC.LPH, '$tgl1' as tgl1, '$tgl2' as tgl2
                FROM (
                    SELECT
                        jual{$bulan}.no_bukti,
                        jual{$bulan}.TGL as tgl,
                        juald{$bulan}.KD_BRG,
                        juald{$bulan}.NA_BRG,
                        sum(juald{$bulan}.qty) as qty,
                        juald{$bulan}.harga as harga,
                        juald{$bulan}.hargavip hvip,
                        sum(juald{$bulan}.diskon) as diskon,
                        juald{$bulan}.disc as disc,
                        sum(juald{$bulan}.nppn) as nppn,
                        sum(juald{$bulan}.dpp) as dpp,
                        sum(juald{$bulan}.total) as total,
                        juald{$bulan}.flag,
                        juald{$bulan}.type,
                        juald{$bulan}.per
                    FROM {$cbg}.juald{$bulan}, {$cbg}.jual{$bulan}
                    WHERE jual{$bulan}.CBG = '$cbg'
                        AND jual{$bulan}.no_bukti = juald{$bulan}.no_bukti
                        AND jual{$bulan}.flag = 'JL'
                        AND jual{$bulan}.tgl >= '$tgl1'
                        AND jual{$bulan}.tgl <= '$tgl2'
                        {$additionalFilter}
                    {$groupByClause}
                ) AS AA, {$cbg}.BRG, {$cbg}.BRGDT CC
                WHERE AA.KD_BRG = brg.KD_BRG
                    AND AA.KD_BRG = CC.KD_BRG
                    AND brg.supp >= '$sup1'
                    AND brg.supp <= '$sup2'
                    AND brg.sub >= '$sub1'
                    AND brg.sub <= '$sub2'
                ORDER BY AA.KD_BRG, AA.no_bukti
            ";

            // Prepare parameters
            $params = [
                'cbg' => $cbg,
                'sup1' => $sup1,
                'sup2' => $sup2,
                'sub1' => $sub1,
                'sub2' => $sub2,
                'tgl1' => $tgl1,
                'tgl2' => $tgl2
            ];

            // Add additional parameters if group detail is enabled
            if ($groupDetail) {
                $params['kitir1'] = $kitir1;
                $params['kitir2'] = $kitir2;
                $params['kodec1'] = $kodec1;
                $params['kodec2'] = $kodec2;
            }

            $results = DB::select($sql);

            // Transform data sesuai format yang dibutuhkan
            $result = [];
            foreach ($results as $item) {
                $result[] = [
                    'CBG' => $cbg,
                    'NO_BUKTI' => $item->no_bukti ?? '',
                    'TGL' => $item->tgl ?? '',
                    'KD_BRG' => $item->KD_BRG ?? '',
                    'NA_BRG' => $item->NA_BRG ?? '',
                    'QTY' => (float)($item->qty ?? 0),
                    'HARGA' => (float)($item->harga ?? 0),
                    'HARGA_VIP' => (float)($item->hvip ?? 0),
                    'DISKON' => (float)($item->diskon ?? 0),
                    'DISC' => (float)($item->disc ?? 0),
                    'NPPN' => (float)($item->nppn ?? 0),
                    'DPP' => (float)($item->dpp ?? 0),
                    'TOTAL' => (float)($item->total ?? 0),
                    'FLAG' => $item->flag ?? '',
                    'TYPE' => $item->type ?? '',
                    'PER' => $item->per ?? '',
                    'KET_UK' => $item->KET_UK ?? '',
                    'SUPP' => $item->supp ?? '',
                    'LPH' => $item->LPH ?? '',
                    'TGL1' => $item->tgl1 ?? '',
                    'TGL2' => $item->tgl2 ?? ''
                ];
            }

            // Log activity
            $this->logActivity('get_penjualan_baru', $cbg, $bulan, count($result));

            return $result;
        } catch (\Exception $e) {
            Log::error('Error in getPenjualanBaruData: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Helper functions dari Delphi - tetap dipertahankan untuk kompatibilitas
     */
    private function leftStr($string, $count)
    {
        return substr($string, 0, $count);
    }

    private function rightStr($string, $count)
    {
        $length = strlen($string);
        if ($length < $count) {
            return $string;
        }
        return substr($string, $length - $count, $count);
    }

    /**
     * Get supplier list untuk dropdown
     */
    public function getSupplierList($cbg)
    {
        try {
            if (empty($cbg)) {
                return [];
            }

            $query = "
                SELECT DISTINCT supp
                FROM {$cbg}.brg
                WHERE supp IS NOT NULL
                  AND supp != ''
                ORDER BY supp ASC
            ";

            return DB::select($query);
        } catch (\Exception $e) {
            Log::error('Error in getSupplierList: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get sub category list untuk dropdown
     */
    public function getSubCategoryList($cbg)
    {
        try {
            if (empty($cbg)) {
                return [];
            }

            $query = "
                SELECT DISTINCT sub
                FROM {$cbg}.brg
                WHERE sub IS NOT NULL
                  AND sub != ''
                ORDER BY sub ASC
            ";

            return DB::select($query);
        } catch (\Exception $e) {
            Log::error('Error in getSubCategoryList: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Export ke Excel
     */
    public function exportToExcel(Request $request)
    {
        try {
            if (empty($request->cbg)) {
                return response()->json(['error' => 'Cabang harus diisi!'], 400);
            }

            $data = $this->getPenjualanBaruData($request);

            if (empty($data)) {
                return response()->json(['message' => 'Tidak ada data untuk diekspor'], 200);
            }

            // Generate filename
            $filename = 'penjualan_baru_' . $request->cbg;
            if (!empty($request->periode)) {
                $filename .= '_' . str_replace(['/', '\\', ' '], '_', $request->periode);
            }
            $filename .= '_' . date('YmdHis') . '.xlsx';

            return response()->json(['success' => true, 'filename' => $filename]);
        } catch (\Exception $e) {
            Log::error('Error in exportToExcel: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Search penjualan baru dengan AJAX
     */
    public function searchPenjualanBaru(Request $request)
    {
        try {
            if (empty($request->cbg)) {
                return response()->json(['error' => 'Cabang harus diisi!'], 400);
            }

            $data = $this->getPenjualanBaruData($request);

            return response()->json([
                'success' => true,
                'data' => $data,
                'total_records' => count($data)
            ]);
        } catch (\Exception $e) {
            Log::error('Error in searchPenjualanBaru: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Validasi input parameters
     */
    private function validateInput(Request $request)
    {
        if (empty($request->cbg)) {
            throw new \Exception('Cabang harus diisi!');
        }

        // Validate cabang format
        if (!preg_match('/^[A-Z0-9]+$/', $request->cbg)) {
            throw new \Exception('Format cabang tidak valid!');
        }

        // Validate periode format jika diisi
        if (!empty($request->periode) && !preg_match('/^[0-9]{2}\/[0-9]{4}$/', $request->periode)) {
            throw new \Exception('Format periode tidak valid! Gunakan format MM/YYYY');
        }

        // Validate date format
        if (!empty($request->tgl1) && !strtotime($request->tgl1)) {
            throw new \Exception('Format tanggal awal tidak valid!');
        }

        if (!empty($request->tgl2) && !strtotime($request->tgl2)) {
            throw new \Exception('Format tanggal akhir tidak valid!');
        }

        return true;
    }

    /**
     * Helper method untuk logging
     */
    private function logActivity($action, $cbg, $periode = '', $recordCount = 0)
    {
        Log::info("PenjualanBaru: {$action}", [
            'cbg' => $cbg,
            'periode' => $periode,
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
     * Method untuk mendukung AJAX request dari view
     */
    public function ajaxGetPenjualanBaru(Request $request)
    {
        try {
            if (empty($request->get('cbg'))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parameter cabang tidak boleh kosong',
                    'data' => []
                ]);
            }

            $data = $this->getPenjualanBaruData($request);

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diambil',
                'data' => $data,
                'total' => count($data)
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
     * Method untuk preview data sebelum print
     */
    public function previewPenjualanBaru(Request $request)
    {
        try {
            if (empty($request->cbg)) {
                return redirect()->back()->with('error', 'Cabang harus diisi!');
            }

            $data = $this->getPenjualanBaruData($request);
            $namaToko = $this->getNamaToko($request->cbg);

            return view('oreport_penjualanbaru.preview')->with([
                'data' => $data,
                'cbg' => $request->cbg,
                'periode' => $request->periode ?? '',
                'tgl1' => $request->tgl1 ?? '',
                'tgl2' => $request->tgl2 ?? '',
                'namaToko' => $namaToko,
                'totalRecords' => count($data)
            ]);
        } catch (\Exception $e) {
            Log::error('Error in previewPenjualanBaru: ' . $e->getMessage());
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Get summary data untuk dashboard atau report summary
     */
    public function getSummaryPenjualanBaru(Request $request)
    {
        try {
            if (empty($request->cbg)) {
                return [];
            }

            $periode = $request->periode ?? '';
            $bulan = '';
            if (!empty($periode)) {
                $bulan = $this->leftStr(trim($periode), 2);
            }

            $cbg = $request->cbg;
            $tgl1 = $request->tgl1 ?? '';
            $tgl2 = $request->tgl2 ?? '';

            $query = DB::table("{$cbg}.juald{$bulan} as jd")
                ->join("{$cbg}.jual{$bulan} as j", 'j.no_bukti', '=', 'jd.no_bukti')
                ->select([
                    DB::raw("COUNT(DISTINCT jd.KD_BRG) as TOTAL_BARANG"),
                    DB::raw("COUNT(DISTINCT j.no_bukti) as TOTAL_TRANSAKSI"),
                    DB::raw("COALESCE(SUM(jd.qty), 0) as TOTAL_QTY"),
                    DB::raw("COALESCE(SUM(jd.total), 0) as TOTAL_AMOUNT"),
                    DB::raw("COALESCE(AVG(jd.total), 0) as AVG_AMOUNT")
                ])
                ->where('j.CBG', $cbg)
                ->where('j.flag', 'JL');

            if (!empty($tgl1) && !empty($tgl2)) {
                $query->whereBetween('j.tgl', [$tgl1, $tgl2]);
            }

            $result = $query->first();

            return [
                'TOTAL_BARANG' => (int)$result->TOTAL_BARANG,
                'TOTAL_TRANSAKSI' => (int)$result->TOTAL_TRANSAKSI,
                'TOTAL_QTY' => (float)$result->TOTAL_QTY,
                'TOTAL_AMOUNT' => (float)$result->TOTAL_AMOUNT,
                'AVG_AMOUNT' => (float)$result->AVG_AMOUNT
            ];
        } catch (\Exception $e) {
            Log::error('Error in getSummaryPenjualanBaru: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get periode list from existing data
     */
    public function getPeriodeListFromData($cbg)
    {
        try {
            if (empty($cbg)) {
                return [];
            }

            // Get available periode from Perid model
            $periodeList = Perid::query()
                ->select('PERID')
                ->orderBy('PERID', 'desc')
                ->get()
                ->pluck('PERID')
                ->toArray();

            return $periodeList;
        } catch (\Exception $e) {
            Log::error('Error in getPeriodeListFromData: ' . $e->getMessage());
            return [];
        }
    }
}