<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
// ganti 1

use App\Models\Master\BrgchDetail;
use App\Models\Master\Brgch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use DataTables;
use Auth;
use DB;
use Carbon\Carbon;

// ganti 2
class PerhargaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */



    public function index()
    {

        // ganti 3
        return view('otransaksi_perharga.index');
    }


    // ganti 4
    public function tampil ( Request $request )
    {
        $data = [
            "jenis"     => $request->jenis,
            "type"      => $request->type, // Ambil dari request
            "cbg"       => Auth::user()->CBG, // Ambil dari request
            "na_file"   => $request->na_file, // Ambil dari request
        ];

        $response = Http::asJson()->withHeaders([
            'Content-Type' => 'application/json',
        ])->get('http://192.168.0.2/admin-apf-app/public/api/get-file', $data);
        
        $getdata = json_decode($response->body()); // object
        $datains = $getdata->data[0]; // ambil elemen pertama dari array
        $headerins = $datains->header; // ambil properti 'header'
        $detailins = $datains->detail; // ambil properti 'detail'

        $datainsert = [
            "jenis"     => $request->jenis,
            "type"      => $request->type, // Ambil dari request
            "cbg"       => Auth::user()->CBG, // Ambil dari request
            "na_file"   => $request->na_file, // Ambil dari request
            "data"   => [(object) [
                "header" => $headerins,
                "detail" => $detailins,
            ],
            ],
        ];
        $responseins = Http::asJson()->withHeaders([
            'Content-Type' => 'application/json',
        ])->post(url('api/pengesahan_brg'), $datainsert);
        
        // === Ambil nilai per1, per2, dan TGX otomatis dari database ===
        $periode = DB::selectOne("
            SELECT 
                LPAD(MONTH(DATE_SUB(DATE(NOW()), INTERVAL 30 DAY)), 2, '0') AS per1,
                LPAD(MONTH(DATE(NOW())), 2, '0') AS per2,
                DATE_SUB(DATE(NOW()), INTERVAL 30 DAY) AS TGX
        ");

        $per1 = $periode->per1;
        $per2 = $periode->per2;
        $tgx  = $periode->TGX;
        $cbg = Auth::user()->CBG;

        if ($responseins['success']) {
            $sql = "SELECT 
                        a.kd_brg,
                        REPLACE(a.NA_BRG, '*', '') AS na_brg,
                        a.ket_uk,
                        a.ket_kem,
                        a.hj,
                        a.lph,
                        IF(a.lhusul IS NULL, a.lph, a.lhusul) AS lhusul,
                        a.kdlaku,
                        a.klk,
                        a.dtr,
                        a.srmin,
                        a.srmax,
                        a.mo,
                        a.supp,
                        a.sp_l,
                        a.sp_lf,
                        a.lph_tm,
                        a.lph_tf
                    FROM (
                        SELECT 
                            BRG.KD_BRG,
                            BRGDT.NA_BRG,
                            BRG.KET_UK,
                            BRG.KET_KEM,
                            BRG.SP_L,
                            BRG.SP_LF,
                            BRG.LPH_TM,
                            BRG.LPH_TF,
                            BRGDT.HJ,
                            BRGDT.LPH,
                            BRGDT.KDLAKU,
                            IF(ROUND(INI.TOTAL_LK/30, 2) < BRGDT.LPH, BRGDT.LPH, ROUND(INI.TOTAL_LK/30, 2)) AS LHUSUL,
                            BRGDT.KLK,
                            BRGDT.DTR,
                            BRGDT.SRMIN,
                            BRGDT.SRMAX,
                            BRG.MO,
                            BRG.SUPP
                        FROM BRG
                        JOIN BRGDT ON BRG.KD_BRG = BRGDT.KD_BRG
                        JOIN BRGCH AS B ON BRG.KD_BRG = BRGDT.KD_BRG
                        LEFT JOIN (
                            SELECT kd_brg, na_brg, SUM(total_lk) AS TOTAL_LK, SUM(hari_lk) AS HARI_LK
                            FROM (
                                SELECT kd_brg, na_brg, SUM(total_lk) AS total_lk, COUNT(DISTINCT(tgl)) AS hari_lk
                                FROM (
                                    SELECT KD_BRG, NA_BRG, SUM(qty) AS total_lk, TGL
                                    FROM juald{$per1}
                                    WHERE tgl >= DATE_SUB(DATE(NOW()), INTERVAL 30 DAY)
                                        AND flag NOT IN ('OB', 'ZP', 'FC')
                                        AND cbg = ?
                                    GROUP BY TGL, KD_BRG
                                ) AS PERTGL
                                GROUP BY kd_brg
                                
                                UNION ALL
                                
                                SELECT kd_brg, na_brg, SUM(total_lk) AS total_lk, COUNT(DISTINCT(tgl)) AS hari_lk
                                FROM (
                                    SELECT KD_BRG, NA_BRG, SUM(qty) AS total_lk, TGL
                                    FROM juald{$per2}
                                    WHERE tgl >= DATE_SUB(DATE(NOW()), INTERVAL 30 DAY)
                                        AND flag NOT IN ('OB', 'ZP', 'FC')
                                        AND cbg = ?
                                    GROUP BY TGL, KD_BRG
                                ) AS PERTGL
                                GROUP BY kd_brg
                            ) AS ageng
                            GROUP BY kd_brg
                        ) AS INI ON BRGDT.KD_BRG = INI.KD_BRG
                        WHERE LEFT(BRG.NA_BRG, 1) = '*'
                        AND DATEDIFF(DATE(NOW()), DATE(BRGDT.TGL_AW_TRM)) > 30
                        AND DATE(BRGDT.TGL_AW_TRM) <> '2001-01-01'
                        AND BRGDT.CBG = ?
                        AND B.NA_FILE = ?
                        AND BRGDT.TD_OD <> '*'
                    ) AS a
                ";
            // Binding untuk :cbg (3x dipakai) dan :na_file
            $perharga = DB::select($sql, [$cbg, $cbg, $cbg, $request->na_file]);
        }  else {

                $perharga = DB::select("SELECT kd_brg, REPLACE(NA_BRG, '*', '') AS na_brg, ket_uk, ket_kem, hj, lph, 
                        IF(lhusul IS NULL, lph, lhusul) AS lhusul, kdlaku, klk, dtr, srmin, srmax, mo, supp, sp_l, sp_lf, lph_tm, lph_tf
                    FROM (
                        SELECT NULL AS kd_brg, NULL AS NA_BRG, NULL AS ket_uk, NULL AS ket_kem,
                            NULL AS hj, NULL AS lph, NULL AS lhusul, NULL AS kdlaku, NULL AS klk,
                            NULL AS dtr, NULL AS srmin, NULL AS srmax, NULL AS mo, NULL AS supp,
                            NULL AS sp_l, NULL AS sp_lf, NULL AS lph_tm, NULL AS lph_tf
                    ) AS kosong
                    WHERE FALSE
                ");

            }

        
        return Datatables::of($perharga)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                if (Auth::user()->divisi=="programmer" || Auth::user()->divisi=="owner" || Auth::user()->divisi=="sales")
                {
                    // url untuk delete di index
                    $url = "'".url("perharga/delete/" . $row->NO_ID )."'";
                    // batas

                    $btnDelete = ' onclick="deleteRow('.$url.')"';

                    $btnPrivilege =
                        '
                        ';
                } else {
                    $btnPrivilege = '';
                }

                $actionBtn =
                    '
                    <div class="dropdown show" style="text-align: center">
                        <a class="btn btn-secondary dropdown-toggle btn-sm" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-bars"></i>
                        </a>

                        <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                            <a hidden class="dropdown-item" href="brg/show/' . $row->NO_ID . '">
                            <i class="fas fa-eye"></i>
                                Lihat
                            </a>

                            
                        </div>
                    </div>
                    ';

                return $actionBtn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    // public function proses ( Request $request ){
    //     $na_file = $request->na_file;

    //     DB::SELECT("UPDATE brg a, 
    //                 (SELECT y.KD_BRG, y.MO, y.KET_KEM, y.KET FROM brgch x, brgchd y WHERE x.NO_BUKTI = y.NO_BUKTI AND x.NA_FILE = '" . $na_file . "') b 
    //                 SET a.MO = b.MO, a.KET_KEM = b.KET_KEM, a.KET = '". $na_file . "', a.USERX = '" . Auth::user()->username . "', a.TGLX = now(),
    //                 a.KEM_P = b.KEM_P WHERE a.KD_BRG = b.KD_BRG");
    //     DB::SELECT("UPDATE brgdt a, 
    //                 (SELECT y.KD_BRG, y.KLK, y.KET FROM brgch x, brgchd y WHERE x.NO_BUKTI = y.NO_BUKTI AND x.NA_FILE = '" . $na_file . "') b 
    //                 SET a.KLK = b.KLK, a.KETX = '". $na_file . "', a.USERX = '" . Auth::user()->username . "', a.TGLX = now(), WHERE a.KD_BRG = b.KD_BRG");
                    
    //     return response()->json(['success' => true]);
    // }


}