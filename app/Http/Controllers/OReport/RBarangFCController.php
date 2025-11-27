<?php

namespace App\Http\Controllers\OReport;

use App\Http\Controllers\Controller;
use App\Models\Master\Cbg;
use App\Models\Master\Perid;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;

class RBarangFCController extends Controller
{
    public function report()
    {
        $cbg = Cbg::groupBy('CBG')->get();

        // Initialize session filters
        session()->put('filter_cbg', '');
        session()->put('filter_gol', '');
        session()->put('filter_kodes1', '');
        session()->put('filter_namas1', '');
        session()->put('filter_brg1', '');
        session()->put('filter_nabrg1', '');
        session()->put('filter_tglDari', date("d-m-Y"));
        session()->put('filter_tglSampai', date("d-m-Y"));

        $per = Perid::query()->get();

        // Get toko/cabang data for dropdown
        try {
            $toko = DB::select("SELECT KODE FROM tgz.toko WHERE STA IN ('MA','CB','DC') ORDER BY NO_ID ASC");
            if (empty($toko)) {
                $toko = [
                    (object)[
                        'KODE' => null
                    ]
                ];
            }
        } catch (\Exception $e) {
            $toko = [
                (object)[
                    'KODE' => null
                ]
            ];
        }

        return view('oreport_BarangFC.report')->with([
            'cbg' => $cbg,
            'per' => $per,
            'toko' => $toko,
            'hasilAgenda' => [],
            'hasilTR' => [],
            'hasilKartu' => [],
            'hasilSub' => [],
            'hasilStock' => []
        ]);
    }

    /**
     * Get Barang FC Report data based on filters
     */
    public function getBarangFCReport(Request $request)
    {
        $cbg = Cbg::groupBy('CBG')->get();
        $per = Perid::query()->get();
        $toko = DB::select("SELECT KODE FROM tgz.toko WHERE STA IN ('MA','CB','DC') ORDER BY NO_ID ASC");

        $results = [
            'cbg' => $cbg,
            'per' => $per,
            'toko' => $toko,
            'hasilAgenda' => [],
            'hasilTR' => [],
            'hasilKartu' => [],
            'hasilSub' => [],
            'hasilStock' => []
        ];

        // Handle different report types based on request
        if ($request->has('report_type')) {
            switch ($request->report_type) {
                case 'kartu_stock':
                    $results['hasilKartu'] = $this->getKartuStockData($request);
                    break;
                case 'penjualan_kasir':
                    $results['hasilSub'] = $this->getPenjualanKasirData($request);
                    break;
                case 'stock_barang':
                    $results['hasilStock'] = $this->getStockBarangData($request);
                    break;
                default:
                    // Default behavior (existing logic)
                    $filterType = $request->input('filter_type', 'nosp');
                    $filterValue = $request->input('filter_value', '');

                    if (!empty($filterValue)) {
                        switch ($filterType) {
                            case 'nosp':
                                $whereFilter = "no_po = '$filterValue'";
                                break;
                            case 'notagi':
                                $whereFilter = "NO_TAGI = '$filterValue'";
                                break;
                            case 'kodesup':
                                $whereFilter = "kodes = '$filterValue'";
                                break;
                            case 'noagenda':
                                $whereFilter = "no_bukti = '$filterValue'";
                                break;
                            default:
                                $whereFilter = "1=1";
                        }
                    } else {
                        $whereFilter = "1=1";
                    }

                    $cbgTable = '';
                    if (!empty($request->cbcbg)) {
                        $cbgTable = $request->cbcbg . '.';
                    }

                    $agenda = "SELECT NO_TAGI, no_bukti, flag, tgl, kodes, namas, no_po, total_qty, total, nett, usrnm, cbg as beliz, 0 as posted
                        FROM {$cbgTable}BELIZ
                        WHERE (FLAG='TL' OR FLAG='TH') AND $whereFilter";

                    $tr = "SELECT NO_TAGI, no_bukti, flag, tgl, kodes, namas, no_po, total_qty, total, nett, usrnm, cbg as beliz, 1 as posted
                        FROM {$cbgTable}BELIZ
                        WHERE (FLAG='TR') AND $whereFilter";

                    $results['hasilAgenda'] = DB::select($agenda);
                    $results['hasilTR'] = DB::select($tr);
                    break;
            }
        }

        return view('oreport_BarangFC.report')->with($results);
    }

    /**
     * Get Kartu Stock data (equivalent to Button5Click in Delphi)
     */
    private function getKartuStockData(Request $request)
    {
        $periode = $request->input('periode');
        $cbg = trim($request->input('cbg', ''));
        $kode_barang = trim($request->input('kode_barang', ''));

        if (empty($periode) || empty($cbg) || empty($kode_barang)) {
            return [];
        }

        // Extract month from periode (assuming format MM/YYYY)
        $bulan = substr($periode, 0, 2);
        $cbgTable = $cbg . '.';

        $query = "
            SELECT *, @AKHIR:=@AKHIR+AWAL+MASUK-KELUAR+LAIN AS SALDO
            FROM (
                SELECT belifc{$bulan}.no_bukti, belifc{$bulan}.TGL, belifcd{$bulan}.KD_BRG, belifcd{$bulan}.NA_BRG,
                       0 as awal, belifcd{$bulan}.qty AS MASUK, 0 AS KELUAR, 0 AS LAIN, belifc{$bulan}.FLAG, 1 AS URT
                FROM {$cbgTable}belifc{$bulan}, {$cbgTable}belifcd{$bulan}
                WHERE belifc{$bulan}.NO_BUKTI = belifcd{$bulan}.no_bukti
                  AND belifc{$bulan}.per = :periode
                  AND belifc{$bulan}.CBG = :cbg
                  AND belifc{$bulan}.flag = 'FB'
                  AND belifc{$bulan}.POSTED = 1
                  AND belifcd{$bulan}.KD_BRG = :kode_barang

                UNION ALL

                SELECT jual{$bulan}.no_bukti, jual{$bulan}.TGL, juald{$bulan}.KD_BRG, juald{$bulan}.NA_BRG,
                       0 as awal, 0 AS MASUK, juald{$bulan}.qty AS KELUAR, 0 AS LAIN, jual{$bulan}.FLAG, 2 AS URT
                FROM {$cbgTable}jual{$bulan}, {$cbgTable}juald{$bulan}
                WHERE jual{$bulan}.NO_BUKTI = juald{$bulan}.no_bukti
                  AND jual{$bulan}.per = :periode
                  AND jual{$bulan}.CBG = :cbg
                  AND jual{$bulan}.flag = 'FC'
                  AND jual{$bulan}.POSTED = 1
                  AND juald{$bulan}.type = 'KS'
                  AND juald{$bulan}.KD_BRG = :kode_barang

                UNION ALL

                SELECT stockbz.no_bukti, stockbz.TGL, stockbzd.KD_BRG, stockbzd.NA_BRG,
                       0 as awal, 0 AS MASUK, 0 AS KELUAR, stockbzd.qty AS LAIN, stockbz.FLAG, 3 AS URT
                FROM {$cbgTable}stockbz, {$cbgTable}stockbzd
                WHERE stockbz.NO_BUKTI = stockbzd.no_bukti
                  AND stockbz.per = :periode
                  AND stockbz.CBG = :cbg
                  AND stockbz.flag = 'FS'
                  AND stockbz.POSTED = 1
                  AND stockbzd.KD_BRG = :kode_barang
            ) AS AA
            JOIN (SELECT @AKHIR:=0) AS BB ON 1=1
            ORDER BY KD_BRG ASC, tgl ASC, URT ASC
        ";

        try {
            return DB::select($query, [
                'periode' => $periode,
                'cbg' => $cbg,
                'kode_barang' => $kode_barang
            ]);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get Penjualan Kasir data (equivalent to Button1Click in Delphi)
     */
    private function getPenjualanKasirData(Request $request)
    {
        $periode = $request->input('periode');
        $cbg = trim($request->input('cbg', ''));
        $kasir = trim($request->input('kasir', ''));

        if (empty($periode) || empty($cbg) || empty($kasir)) {
            return [];
        }

        $bulan = substr($periode, 0, 2);
        $cbgTable = $cbg . '.';

        $query = "
            SELECT juald{$bulan}.SUB2, juald{$bulan}.KD_BRG,
                   SUM(juald{$bulan}.qty) as qty, SUM(juald{$bulan}.total) as total,
                   nda.KELOMPOK, nda.TYPE, nda.STAND
            FROM {$cbgTable}jual{$bulan}, {$cbgTable}juald{$bulan}
            LEFT JOIN (
                SELECT sub, STAND, TYPE, KELOMPOK
                FROM {$cbgTable}brgfc
            ) as nda ON nda.sub = juald{$bulan}.SUB2
            WHERE jual{$bulan}.NO_BUKTI = juald{$bulan}.no_bukti
              AND jual{$bulan}.per = :periode
              AND jual{$bulan}.KSR = :kasir
              AND jual{$bulan}.CBG = :cbg
              AND jual{$bulan}.flag = 'FC'
              AND jual{$bulan}.POSTED = 1
              AND juald{$bulan}.type = 'KS'
            GROUP BY juald{$bulan}.SUB2
            ORDER BY juald{$bulan}.SUB2 ASC
        ";

        try {
            return DB::select($query, [
                'periode' => $periode,
                'kasir' => $kasir,
                'cbg' => $cbg
            ]);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get Stock Barang data (equivalent to Button7Click in Delphi)
     */
    private function getStockBarangData(Request $request)
    {
        $cbg = trim($request->input('cbg', ''));
        $sub1 = trim($request->input('sub1', ''));
        $sub2 = trim($request->input('sub2', 'ZZZ'));

        if (empty($cbg)) {
            return [];
        }

        $cbgTable = $cbg . '.';

        $query = "
            SELECT TRIM(CONCAT(brgfc.NA_BRG, ' ', brgfc.KET_UK)) as NA_BRG,
                   brgfc.sub, brgfc.KDBAR, brgfc.KD_BRG, brgfc.STAND, brgfc.TYPE,
                   brgfc.KELOMPOK, brgfc.KET_UK, brgfc.KET_KEM, brgfc.DIS,
                   brgfc.SUPP, brgfc.TKP, brgfc.HJ, brgfc.HB, brgfc.BARCODE,
                   brgfcd.AW00 as AW, brgfcd.MA00 as MA, brgfcd.KE00 as KE,
                   brgfcd.LN00 as LN, brgfcd.AK00 as saldo
            FROM {$cbgTable}brgfc, {$cbgTable}brgfcd
            WHERE brgfc.KD_BRG = brgfcd.KD_BRG
              AND brgfcd.cbg = :cbg
              AND brgfcd.yer = YEAR(NOW())
              AND brgfc.sub >= :sub1
              AND brgfc.sub <= :sub2
            ORDER BY brgfc.kd_brg ASC
        ";

        try {
            return DB::select($query, [
                'cbg' => $cbg,
                'sub1' => $sub1,
                'sub2' => $sub2
            ]);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get product by sub code (equivalent to txtkode1KeyDown logic)
     */
    public function getProductBySub(Request $request)
    {
        $sub = $request->input('sub');

        if (empty($sub)) {
            return response()->json(['success' => false, 'message' => 'Sub code required']);
        }

        try {
            $product = DB::select("SELECT kd_brg FROM brg WHERE sub = ?", [$sub]);

            if (count($product) > 0) {
                return response()->json([
                    'success' => true,
                    'kd_brg' => $product[0]->kd_brg
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving product'
            ]);
        }
    }

    public function jasperBayarReport(Request $request)
    {
        $file = 'bayarn';
        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

        $filtertgl = '';

        if (!empty($request->tglDr) && !empty($request->tglSmp)) {
            $tglDrD = date("Y-m-d", strtotime($request->tglDr));
            $tglSmpD = date("Y-m-d", strtotime($request->tglSmp));
            $filtertgl = " bayar.TGL BETWEEN '$tglDrD' AND '$tglSmpD' ";
        }

        // Update session filters
        session()->put('filter_gol', $request->gol);
        session()->put('filter_kodes1', $request->kodes);
        session()->put('filter_namas1', $request->NAMAS);
        session()->put('filter_tglDari', $request->tglDr);
        session()->put('filter_tglSampai', $request->tglSmp);
        session()->put('filter_brg1', $request->brg1);
        session()->put('filter_nabrg1', $request->nabrg1);
        session()->put('filter_cbg', $request->cbg);

        // Handle different report types
        if ($request->has('report_type') && in_array($request->report_type, ['kartu_stock', 'penjualan_kasir', 'stock_barang'])) {
            return $this->generateSpecialReport($request);
        }

        // Original logic for default reports
        $cbgTable = '';
        if (!empty($request->cbcbg)) {
            $cbgTable = $request->cbcbg . '.';
        }

        $filterType = $request->input('filter_type', 'nosp');
        $filterValue = $request->input('filter_value', '');

        if (!empty($filterValue)) {
            switch ($filterType) {
                case 'nosp':
                    $whereFilter = "no_po = '$filterValue'";
                    break;
                case 'notagi':
                    $whereFilter = "NO_TAGI = '$filterValue'";
                    break;
                case 'kodesup':
                    $whereFilter = "kodes = '$filterValue'";
                    break;
                case 'noagenda':
                    $whereFilter = "no_bukti = '$filterValue'";
                    break;
                default:
                    $whereFilter = "1=1";
            }
        } else {
            $whereFilter = "1=1";
        }

        $agenda = "SELECT NO_TAGI, no_bukti, flag, tgl, kodes, namas, no_po, total_qty, total, nett, usrnm, cbg as beliz, 0 as posted
            FROM {$cbgTable}BELIZ
            WHERE (FLAG='TL' OR FLAG='TH') AND $whereFilter";

        $tr = "SELECT NO_TAGI, no_bukti, flag, tgl, kodes, namas, no_po, total_qty, total, nett, usrnm, cbg as beliz, 1 as posted
            FROM {$cbgTable}BELIZ
            WHERE (FLAG='TR') AND $whereFilter";

        $queryAgenda = DB::select($agenda);
        $queryTR = DB::select($tr);

        if ($request->has('filter')) {
            $cbg = Cbg::groupBy('CBG')->get();
            $toko = DB::select("SELECT KODE FROM tgz.toko WHERE STA IN ('MA','CB','DC') ORDER BY NO_ID ASC");

            return view('oreport_BarangFC.report')->with([
                'cbg' => $cbg,
                'toko' => $toko,
                'hasilAgenda' => $queryAgenda,
                'hasilTR' => $queryTR
            ]);
        }

        // Generate report
        if ($request->has('cetak') || $request->has('cetak_tr')) {
            $query = $request->has('cetak_tr') ? $queryTR : $queryAgenda;
        } else {
            $query = $queryAgenda;
        }

        $data = [];
        foreach ($query as $key => $value) {
            array_push($data, [
                'NO_TAGI' => $query[$key]->NO_TAGI ?? '',
                'NO_BUKTI' => $query[$key]->no_bukti ?? '',
                'FLAG' => $query[$key]->flag ?? '',
                'TGL' => $query[$key]->tgl ?? '',
                'KODES' => $query[$key]->kodes ?? '',
                'NAMAS' => $query[$key]->namas ?? '',
                'NO_PO' => $query[$key]->no_po ?? '',
                'TOTAL_QTY' => $query[$key]->total_qty ?? 0,
                'TOTAL' => $query[$key]->total ?? 0,
                'NETT' => $query[$key]->nett ?? 0,
                'USRNM' => $query[$key]->usrnm ?? '',
                'CBG' => $query[$key]->beliz ?? '',
                'POSTED' => $query[$key]->posted ?? 0,
            ]);
        }

        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I");
    }

    /**
     * Generate special reports for new functionalities
     */
    private function generateSpecialReport(Request $request)
    {
        switch ($request->report_type) {
            case 'kartu_stock':
                $data = $this->getKartuStockData($request);
                $reportFile = 'kartu_stock';
                break;
            case 'penjualan_kasir':
                $data = $this->getPenjualanKasirData($request);
                $reportFile = 'penjualan_kasir';
                break;
            case 'stock_barang':
                $data = $this->getStockBarangData($request);
                $reportFile = 'stock_barang';
                break;
            default:
                return response()->json(['error' => 'Invalid report type'], 400);
        }

        // Convert object to array for Jasper
        $reportData = [];
        foreach ($data as $row) {
            $reportData[] = (array) $row;
        }

        try {
            $PHPJasperXML = new PHPJasperXML();
            $PHPJasperXML->load_xml_file(base_path() . "/app/reportc01/phpjasperxml/{$reportFile}.jrxml");
            $PHPJasperXML->setData($reportData);
            ob_end_clean();
            $PHPJasperXML->outpage("I");
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to generate report: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Export data to Excel (equivalent to Button6Click and Button8Click)
     */
    public function exportToExcel(Request $request)
    {
        $reportType = $request->input('report_type');
        $data = [];

        switch ($reportType) {
            case 'kartu_stock':
                $data = $this->getKartuStockData($request);
                $filename = 'kartu_stock_' . date('Y-m-d_H-i-s') . '.xlsx';
                break;
            case 'penjualan_kasir':
                $data = $this->getPenjualanKasirData($request);
                $filename = 'penjualan_kasir_' . date('Y-m-d_H-i-s') . '.xlsx';
                break;
            case 'stock_barang':
                $data = $this->getStockBarangData($request);
                $filename = 'stock_barang_' . date('Y-m-d_H-i-s') . '.xlsx';
                break;
            default:
                return response()->json(['error' => 'Invalid export type'], 400);
        }

        // Convert to array for export
        $exportData = [];
        foreach ($data as $row) {
            $exportData[] = (array) $row;
        }

        return response()->json([
            'success' => true,
            'data' => $exportData,
            'filename' => $filename
        ]);
    }
}
