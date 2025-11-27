<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;
use Auth;

use \koolreport\laravel\Friendship;
use \koolreport\bootstrap4\Theme;

class ChartController extends Controller
{
    public function index()
    {
        return view('dashboard');
    }

    public function chart() 
    {
        $data = [
            'belikg'    => array(),
            'belirp'    => array(),
            'jualkg'    => array(),
            'jualrp'    => array(),
        ];

        for ($i=0;$i<12;$i++) {
            $month = str_pad($i+1, 2, 0, STR_PAD_LEFT);
            
            $queryKg = DB::table('beli')->select(DB::raw('SUM(KG) as kg'))->where('PER', $month.'/2023')->get();
            array_push($data['belikg'], ($queryKg[0]->kg==null) ? 0 : $queryKg[0]->kg );

            $queryRp = DB::table('beli')->select(DB::raw('SUM(TOTAL) as rp'))->where('PER', $month.'/2023')->get();
            array_push($data['belirp'], ($queryRp[0]->rp==null) ? 0 : $queryRp[0]->rp );

            
            $queryKg = DB::table('jual')->select(DB::raw('SUM(KG) as kg'))->where('PER', $month.'/2023')->get();
            array_push($data['jualkg'], ($queryKg[0]->kg==null) ? 0 : $queryKg[0]->kg );

            $queryRp = DB::table('jual')->select(DB::raw('SUM(TOTAL) as rp'))->where('PER', $month.'/2023')->get();
            array_push($data['jualrp'], ($queryRp[0]->rp==null) ? 0 : $queryRp[0]->rp );
        } 

        //Grid Beli
		$queryakum = DB::SELECT("SET @akum:=0;");
		$queryBeli = DB::SELECT("SELECT NO_BUKTI,DATE_FORMAT(TGL,'%d/%m/%Y') as TGL,KODES,NAMAS,TOTAL,BAYAR,DATEDIFF(date(NOW()),TGL) as HARI from beli where FLAG in ('BL','BD','BN') and BAYAR=0 and DATEDIFF(date(NOW()),TGL)>30;");
        
        //Grid Jual
		$queryakum = DB::SELECT("SET @akum:=0;");
		$queryJual = DB::SELECT("SELECT NO_BUKTI,DATE_FORMAT(TGL,'%d/%m/%Y') as TGL,KODEC,NAMAC,TOTAL,BAYAR,DATEDIFF(date(NOW()),TGL) as HARI from jual where FLAG in ('JL') and BAYAR=0 and DATEDIFF(date(NOW()),TGL)>30;");
        
        return view('chart', $data)->with(['hasilBeli' => $queryBeli])->with(['hasilJual' => $queryJual]);
    }
}
