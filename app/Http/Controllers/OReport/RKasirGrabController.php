<?php
namespace App\Http\Controllers\OReport;

use App\Http\Controllers\Controller;
use Auth;
use Carbon\Carbon;
use DataTables;
use DB;
use Illuminate\Http\Request;
class RKasirGrabController extends Controller
{

    public function report()
    {
        return view('oreport_kasir_grab.report');
    }

    public function getKasirgrab(Request $request)
    {

        $query = DB::select("SELECT A.SUB,A.KD_BRG, concat(A.NA_BRG,' ',C.KET_UK) NA_BRG,  IF(B.CAT_OD LIKE '%TP%' AND (B.AK00 + B.GAK00)<1,'X','OKE') XX,
                                    HJGZ HJ_ASLI, 
                                    CEIL(( HJGZ +( HJGZ *0.1))/100)*100 HJ_GRAB,
                                    if((date(now()) BETWEEN A.TGDIS_M and A.TGDIS_A) and (time(now())  
                                                                            BETWEEN A.JAM and A.JAMSLS), THGZ,0) as TRN_HRG, 
                                    (SELECT IF(TRN_HRG>0,A.TGDIS_M,'' ) ) TGL1,
                                    (SELECT IF(TRN_HRG>0,A.TGDIS_A,'' ) ) TGL2,
                                    (SELECT HJ_GRAB-TRN_HRG) HARGA, B.LPH, B.AK00+GAK00 STOK, concat(B.TD_OD,B.CAT_OD) BINTANG
                            FROM masks A, brgdt B
                            LEFT JOIN brg C on C.KD_BRG=B.KD_BRG
                            WHERE A.KD_BRG=B.KD_BRG
                            AND B.LPH>0
                            HAVING XX='OKE' 
                            ");

        return Datatables::of($query)
            ->addIndexColumn()
            
            ->make(true);
    }

}