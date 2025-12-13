<?php

namespace App\Http\Controllers\OTools;

use App\Http\Controllers\Controller;
use App\Models\Master\Cbg;
use App\Models\Master\Perid;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;

class StokakhirController extends Controller
{
    /**
     * Halaman utama report - Route: /rkasirbantu
     */
    public function report()
    {
        $cbg = DB::SELECT("SELECT KODE FROM toko WHERE STA IN ('MA','CB','DC') ORDER BY NO_ID ASC");
        $per = DB::SELECT("SELECT PERIO from perid");

        // Initialize session variables
        session()->put('filter_cbg', '');
        session()->put('filter_per', '');

        return view('otools_stokakhir.report')->with([
            'cbg' => $cbg,
            'per' => $per,
            'hasilStokakhir' => []
        ]);
    }

    public function getStokakhirReport(Request $request)
    {
        $listCbg = DB::SELECT("SELECT KODE FROM toko WHERE STA IN ('MA','CB','DC') ORDER BY NO_ID ASC");
        $listPer = DB::SELECT("SELECT PERIO FROM perid ORDER BY PERIO ASC");
        $tab = $request->tab ?? 'periode';

        switch ($tab) {

            case 'periode':
                if (empty($request->cbg && $request->per)) {
                    return view('otools_stokakhir.report')->with([
                        'cbg' => $listCbg,
                        'per' => $listPer,
                        'hasilStokakhir' => [],
                        'error' => 'Cabang dan Periode harus dipilih untuk tab Periode.'
                    ]);
                }
                $hasilstokakhir = $this->getPeriode($request->cbg, $request->per);
                break;

            case 'stok':
                if (empty($request->cbg && $request->per)) {
                    return view('otools_stokakhir.report')->with([
                        'cbg' => $listCbg,
                        'per' => $listPer,
                        'hasilstokakhir' => [],
                        'error' => 'Cabang dan Periode harus dipilih untuk tab Stok.'
                    ]);
                }
                $hasilstokakhir = $this->getStok($request->cbg, $request->per);
                break;
        }

        return view('otools_stokakhir.report')->with([
            'cbg' => $listCbg,
            'per' => $listPer,
            'hasilStokakhir' => $hasilStokakhir,
            'tab' => $tab
        ]);
    }



    /**
     * Generate laporan Jasper - Route: /jasper-kasirbantu-report
     * Implementasi dari logika Delphi untuk generate report
     */
    public function jasperStokakhirReport(Request $request)
    {   
        DB::statement("CALL cek_brg_double()");

        $tab = $request->tab ?? 'periode';
        $cbg = $request->cbg;
        $per = $request->per;

        switch ($tab) {
            case 'periode':
                $file = 'print_laporan_stok_akhir';
                $results = $this->getPeriode($cbg, $per);
                break;
            case 'stok':
                $file = 'print_laporan_stok_akhir';
                $results = $this->getStok($cbg, $per);
                break;
            default:
                abort(404, 'Jenis report tidak dikenali');
        }

        $data = json_decode(json_encode($results), true);

        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path('/app/reportc01/phpjasperxml/'.$file.'.jrxml'));
        $params = [
			"TGL" => date('d/m/Y'),
		];
        $PHPJasperXML->arrayParameter=$params;

        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I");
    }


    private function getPeriode($cbg, $per)
    {   
        $yearini = DB::selectOne("SELECT YEAR(NOW()) as yearini")->yearini;
        $yearitu = substr($per, -4);
        $bulan = substr($per, 0, 2);
        
        if ((int)$yearini === (int)$yearitu) {
            $sql = "SELECT 
                        (SELECT NA_TOKO FROM toko WHERE KODE = '$cbg') AS na_toko,
                        (SELECT NA_PERS FROM toko WHERE KODE = '$cbg') AS na_pers,
                        (SELECT TYP_PERS FROM toko WHERE KODE = '$cbg') AS typ_pers,
                        EE.CBG,EE.SUB,'' as KD, aotprice.KELOMPOK,
                        SUM(TOTAL_T) AS TK, SUM(TOTAL_G) AS GD,
                        ROUND( SUM(TOTAL_T) + SUM(TOTAL_G) ) TOTALX,
                        hb, hbb, SUM(hb) as jumhb, sum(qty) as qty, sum(aw) as aw,
                        sum(ma) as ma, sum(ke) as ke, sum(ln) as ln, sum(ak) as ak
                    FROM
                        (select A.ak$bulan as qty, A.HB,
                            A.harga$bulan as hbb, A.cbg, B.sub,
                            round(  A.ak$bulan *A.harga$bulan ) as TOTAL_T,
                            0 AS TOTAL_G, A.aw$bulan as AW, A.ma$bulan as MA,
                            A.ke$bulan as KE, A.ln$bulan as LN, A.ak$bulan as AK
                            FROM BRGDT A, BRG B                 
                            WHERE A.KD_BRG=B.KD_BRG and A.CBG='$cbg'                           
                    UNION ALL
                            select 0 as qty,0 as HB, 0 as hbb, A.cbg,B.sub,           
                            0 AS TOTAL_T , round(  A.ak$bulan *A.harga$bulan ) as TOTAL_G,
                            A.aw$bulan as AW,A.ma$bulan as MA,
                            A.ke$bulan as KE,A.ln$bulan as LN,A.ak$bulan as AK
                            FROM BRGD A, BRG B
                            WHERE A.KD_BRG=B.KD_BRG and A.CBG='$cbg'
                        ) AS EE
                    LEFT JOIN aotprice ON EE.sub=aotprice.SUB
                    GROUP BY EE.CBG,EE.SUB ORDER BY cbg,SUB";
            } else {
                $sql = "SELECT 
                            (SELECT NA_TOKO FROM toko WHERE KODE = '$cbg') AS na_toko,
                            (SELECT NA_PERS FROM toko WHERE KODE = '$cbg') AS na_pers,
                            (SELECT TYP_PERS FROM toko WHERE KODE = '$cbg') AS typ_pers,
                            EE.CBG,EE.SUB,'' as KD,aotprice.KELOMPOK,SUM(TOTAL_T) AS TK,SUM(TOTAL_G) AS GD,  ROUND( SUM(TOTAL_T) + SUM(TOTAL_G) ) TOTALX,
                            hb,hbb, SUM(hb) as jumhb, sum(qty) as qty, sum(aw) as aw, sum(ma) as ma, sum(ke) as ke, sum(ln) as ln, sum(ak) as ak
                        FROM
                            (select brgdt$yearitu.ak$bulan as qty,brgdt$yearitu.HB, brgdt$yearitu.harga$bulan as hbb, brgdt$yearitu.cbg,brg.sub,
                            round( brgdt$yearitu.ak$bulan *brgdt$yearitu.harga$bulan ) as TOTAL_T ,0 AS TOTAL_G,brgdt$yearitu.aw$bulan as AW,brgdt$yearitu.ma$bulan as MA,brgdt$yearitu.ke$bulan as KE,brgdt$yearitu.ln$bulan as LN,brgdt$yearitu.ak$bulan as AK
                                FROM brgdt$yearitu,BRG WHERE brgdt$yearitu.KD_BRG=brg.KD_BRG and brgdt$yearitu.CBG='$cbg'
                        UNION ALL
                            select 0 as qty,0 as HB, 0 as hbb, brgd$yearitu.cbg,brg.sub,
                            0 AS TOTAL_T ,round( brgd$yearitu.ak$bulan *brgd$yearitu.harga$bulan ) as TOTAL_G,brgd$yearitu.aw$bulan as AW,brgd$yearitu.ma$bulan as MA,brgd$yearitu.ke$bulan as KE,brgd$yearitu.ln$bulan as LN,brgd$yearitu.ak$bulan as AK
                                FROM brgd$yearitu,BRG WHERE brgd$yearitu.KD_BRG=brg.KD_BRG and brgd$yearitu.CBG='$cbg' ) AS EE
                        LEFT JOIN aotprice ON EE.sub=aotprice.SUB GROUP BY EE.CBG,EE.SUB ORDER BY cbg,SUB";
            }

        return DB::select($sql);
    }

    private function getStok($cbg, $per)
    {   
        $yearini = DB::selectOne("SELECT YEAR(NOW()) as yearini")->yearini;
        $yearitu = substr($per, -4);
        $bulan = substr($per, 0, 2);
        
        if ((int)$yearini === (int)$yearitu) {
            $sql = "SELECT 
                        (SELECT NA_TOKO FROM toko WHERE KODE = '$cbg') AS na_toko,
                        (SELECT NA_PERS FROM toko WHERE KODE = '$cbg') AS na_pers,
                        (SELECT TYP_PERS FROM toko WHERE KODE = '$cbg') AS typ_pers,
                        EE.CBG,EE.SUB,'' as KD, aotprice.KELOMPOK,
                        SUM(TOTAL_T) AS TK, SUM(TOTAL_G) AS GD,
                        ROUND( SUM(TOTAL_T) + SUM(TOTAL_G) ) TOTALX,
                        hb, hbb, SUM(hb) as jumhb, sum(qty) as qty, sum(aw) as aw,
                        sum(ma) as ma, sum(ke) as ke, sum(ln) as ln, sum(ak) as ak
                    FROM
                        (select A.ak$bulan as qty, A.HB,
                            A.harga$bulan as hbb, A.cbg, B.sub,
                            round(  A.ak$bulan *A.harga$bulan ) as TOTAL_T,
                            0 AS TOTAL_G, A.aw$bulan as AW, A.ma$bulan as MA,
                            A.ke$bulan as KE, A.ln$bulan as LN, A.ak$bulan as AK
                            FROM BRGDT A, BRG B                 
                            WHERE A.KD_BRG=B.KD_BRG and A.CBG='$cbg'                           
                    UNION ALL
                            select 0 as qty,0 as HB, 0 as hbb, A.cbg,B.sub,           
                            0 AS TOTAL_T , round(  A.ak$bulan *A.harga$bulan ) as TOTAL_G,
                            A.aw$bulan as AW,A.ma$bulan as MA,
                            A.ke$bulan as KE,A.ln$bulan as LN,A.ak$bulan as AK
                            FROM BRGD A, BRG B
                            WHERE A.KD_BRG=B.KD_BRG and A.CBG='$cbg'
                        ) AS EE
                    LEFT JOIN aotprice ON EE.sub=aotprice.SUB
                    GROUP BY EE.CBG,EE.SUB ORDER BY cbg,SUB";
            } else {
                $sql = "SELECT 
                            (SELECT NA_TOKO FROM toko WHERE KODE = '$cbg') AS na_toko,
                            (SELECT NA_PERS FROM toko WHERE KODE = '$cbg') AS na_pers,
                            (SELECT TYP_PERS FROM toko WHERE KODE = '$cbg') AS typ_pers,
                            EE.CBG,EE.SUB,'' as KD,aotprice.KELOMPOK,SUM(TOTAL_T) AS TK,SUM(TOTAL_G) AS GD,  ROUND( SUM(TOTAL_T) + SUM(TOTAL_G) ) TOTALX,
                            hb,hbb, SUM(hb) as jumhb, sum(qty) as qty, sum(aw) as aw, sum(ma) as ma, sum(ke) as ke, sum(ln) as ln, sum(ak) as ak
                        FROM
                            (select brgdt$yearitu.ak$bulan as qty,brgdt$yearitu.HB, brgdt$yearitu.harga$bulan as hbb, brgdt$yearitu.cbg,brg.sub,
                            round( brgdt$yearitu.ak$bulan *brgdt$yearitu.harga$bulan ) as TOTAL_T ,0 AS TOTAL_G,brgdt$yearitu.aw$bulan as AW,brgdt$yearitu.ma$bulan as MA,brgdt$yearitu.ke$bulan as KE,brgdt$yearitu.ln$bulan as LN,brgdt$yearitu.ak$bulan as AK
                                FROM brgdt$yearitu,BRG WHERE brgdt$yearitu.KD_BRG=brg.KD_BRG and brgdt$yearitu.CBG='$cbg'
                        UNION ALL
                            select 0 as qty,0 as HB, 0 as hbb, brgd$yearitu.cbg,brg.sub,
                            0 AS TOTAL_T ,round( brgd$yearitu.ak$bulan *brgd$yearitu.harga$bulan ) as TOTAL_G,brgd$yearitu.aw$bulan as AW,brgd$yearitu.ma$bulan as MA,brgd$yearitu.ke$bulan as KE,brgd$yearitu.ln$bulan as LN,brgd$yearitu.ak$bulan as AK
                                FROM brgd$yearitu,BRG WHERE brgd$yearitu.KD_BRG=brg.KD_BRG and brgd$yearitu.CBG='$cbg' ) AS EE
                        LEFT JOIN aotprice ON EE.sub=aotprice.SUB GROUP BY EE.CBG,EE.SUB ORDER BY cbg,SUB";
            }

        return DB::select($sql);
    }

    public function getStokakhirReportAjax(Request $request)
    {   
        $this->runCekBrgDouble();

        $tab = $request->tab ?? 'periode';
        $cbg = $request->cbg ?? '';
        $per = $request->per ?? '';

        switch ($tab) {
            case 'periode':
                if (empty($cbg)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cabang Dan Periode harus dipilih untuk tab Periode.'
                    ], 400);
                }
                $data = $this->getPeriode($cbg, $per);
                break;
            case 'stok':
                if (empty($cbg)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cabang Dan Periode harus dipilih untuk tab Stok.'
                    ], 400);
                }
                $data = $this->getStok($cbg, $per);
                break;
            }

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    private function runCekBrgDouble()
    {
        DB::statement("CALL cek_brg_double()");
    }
}