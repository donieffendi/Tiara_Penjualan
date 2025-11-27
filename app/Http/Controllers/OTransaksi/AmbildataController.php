<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use App\Http\Traits\Terbilang;

use App\Models\OTransaksi\Posted;
use App\Models\OTransaksi\Postedd;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use DB;
use Carbon\Carbon;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;


class AmbildataController extends Controller
{
	use Terbilang;
	
    public function index()
    {
        return view('otransaksi_ambildata.edit');
    }

    public function ambil(Request $request)
    {
        // BUKTI
        $NO_BUKTI	= $request->input('NO_BUKTI');
        // $filter_nobukti = '';
        // if($NO_BUKTI){
        //     $filter_nobukti = "AND NO_BUKTI='$NO_BUKTI'"
        // };

        // TGL
        // $TGL	    = date('Y-m-d', strtotime($request->input('TGL')));
        // $filter_tgl = '';
        // if($TGL){
        //     $filter_tgl = "AND TGL='$TGL'";
        // }

        // CBG
        $CBG       = Auth::user()->CBG;

        // if($CBG == 'Z'){
        //     DB::table('beli')->where('NO_BUKTI', $NO_BUKTI)->delete();
        //     DB::table('beli')->insertUsing(
        //         ['NO_BUKTI', 'KOES', 'NAMAS', 'TGL', 'FLAG', 'TOTAL_QTY', 'PER', 'CBG'],
        //         DB::table('tgz.hdh')->select('NO_BUKTI', 'KOES', 'NAMAS', 'TGL', 'FLAG', 'TOTAL_QTY', 'PER', 'CBG')
        //             ->where('NO_BUKTI', $NO_BUKTI)
        //     );


        //     DB::table('belid')->where('NO_BUKTI', $NO_BUKTI)->delete();
        //     DB::table('belid')->insertUsing(
        //         ['NO_BUKTI', 'KD_BRG', 'NA_BRG', 'FLAG', 'QTY', 'PER', 'CBG'],
        //         DB::table('tgz.hdhd')->select('NO_BUKTI', 'KD_BRGH', 'NA_BRGH', 'FLAG', 'QTY', 'PER', 'CBG')
        //             ->where('NO_BUKTI', $NO_BUKTI)
        //     );
        // }elseif($CBG == 'M'){
        //     DB::table('beli')->where('NO_BUKTI', $NO_BUKTI)->delete();
        //     DB::table('beli')->insertUsing(
        //         ['NO_BUKTI', 'KOES', 'NAMAS', 'TGL', 'FLAG', 'TOTAL_QTY', 'PER', 'CBG'],
        //         DB::table('tmm.hdh')->select('NO_BUKTI', 'KOES', 'NAMAS', 'TGL', 'FLAG', 'TOTAL_QTY', 'PER', 'CBG')
        //             ->where('NO_BUKTI', $NO_BUKTI)
        //     );


        //     DB::table('belid')->where('NO_BUKTI', $NO_BUKTI)->delete();
        //     DB::table('belid')->insertUsing(
        //         ['NO_BUKTI', 'KD_BRG', 'NA_BRG', 'FLAG', 'QTY', 'PER', 'CBG'],
        //         DB::table('tmm.hdhd')->select('NO_BUKTI', 'KD_BRGH', 'NA_BRGH', 'FLAG', 'QTY', 'PER', 'CBG')
        //             ->where('NO_BUKTI', $NO_BUKTI)
        //     );
        // }elseif($CBG == 'S'){
        //     DB::table('beli')->where('NO_BUKTI', $NO_BUKTI)->delete();
        //     DB::table('beli')->insertUsing(
        //         ['NO_BUKTI', 'KOES', 'NAMAS', 'TGL', 'FLAG', 'TOTAL_QTY', 'PER', 'CBG'],
        //         DB::table('sop.hdh')->select('NO_BUKTI', 'KOES', 'NAMAS', 'TGL', 'FLAG', 'TOTAL_QTY', 'PER', 'CBG')
        //             ->where('NO_BUKTI', $NO_BUKTI)
        //     );


        //     DB::table('belid')->where('NO_BUKTI', $NO_BUKTI)->delete();
        //     DB::table('belid')->insertUsing(
        //         ['NO_BUKTI', 'KD_BRG', 'NA_BRG', 'FLAG', 'QTY', 'PER', 'CBG'],
        //         DB::table('sop.hdhd')->select('NO_BUKTI', 'KD_BRGH', 'NA_BRGH', 'FLAG', 'QTY', 'PER', 'CBG')
        //             ->where('NO_BUKTI', $NO_BUKTI)
        //     );
        // };

        if($CBG == 'Z'){
            $header = DB::connection('tgz')->table('hdh')->where('NO_BUKTI', $NO_BUKTI)->get();
            $detail = DB::connection('tgz')->table('hdhd')->where('NO_BUKTI', $NO_BUKTI)->get();
        }elseif($CBG == 'M'){
            $header = DB::connection('tmm')->table('hdh')->where('NO_BUKTI', $NO_BUKTI)->get();
            $detail = DB::connection('tmm')->table('hdhd')->where('NO_BUKTI', $NO_BUKTI)->get();
        }elseif($CBG == 'S'){
            $header = DB::connection('sop')->table('hdh')->where('NO_BUKTI', $NO_BUKTI)->get();
            $detail = DB::connection('sop')->table('hdhd')->where('NO_BUKTI', $NO_BUKTI)->get();
        };

        foreach ($header as $row) {
            DB::connection('mysql')->table('hdh')->insert([
                'NO_BUKTI'   => $row->NO_BUKTI,
                'KOES'       => $row->KOES,
                'NAMAS'      => $row->NAMAS,
                'TGL'        => $row->TGL,
                'FLAG'       => $row->FLAG,
                'TOTAL_QTY'  => $row->TOTAL_QTY,
                'PER'        => $row->PER,
                'CBG'        => $row->CBG,
            ]);
        }

        foreach ($detail as $row) {
            DB::connection('mysql')->table('hdhd')->insert([
                'NO_BUKTI' => $row->NO_BUKTI,  
                'KD_BRG'   => $row->KD_BRGH,   
                'NA_BRG'   => $row->NA_BRGH,  
                'FLAG'     => $row->FLAG,     
                'QTY'      => $row->QTY,       
                'PER'      => $row->PER,       
                'CBG'      => $row->CBG, 
            ]);
        }


        return redirect('/ambildata')->with('status', 'Data baru berhasil diambil');
    }
}
