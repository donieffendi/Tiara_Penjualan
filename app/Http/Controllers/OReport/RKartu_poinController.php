<?php

namespace App\Http\Controllers\OReport;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\Master\Cust;
// ganti 1

use Illuminate\Http\Request;
use DataTables;
use Auth;
use DB;

include_once base_path()."/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";
use PHPJasperXML;

use \koolreport\laravel\Friendship;
use \koolreport\bootstrap4\Theme;

// ganti 2
class RKartu_poinController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function kartu()
    {
		session()->put('filter_cust1', '');
		session()->put('filter_nacust1', '');
		session()->put('filter_cust2', '');
		session()->put('filter_nacust2', '');
		session()->put('filter_tglDr', now()->format('d-m-Y'));
		session()->put('filter_tglSmp', now()->format('d-m-Y'));
		$cust = Cust::orderBy('KODEC', 'ASC')->get();
// GANTI 3 //
        return view('oreport_piu.kartu')->with(['cust' => $cust])->with(['hasil' => []]);
		
    }
	


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
	public function jasperPoinKartu(Request $request) 
	{
		$file 	= 'kartu_poin';
		$PHPJasperXML = new PHPJasperXML();
		$PHPJasperXML->load_xml_file(base_path().('/app/reportc01/phpjasperxml/'.$file.'.jrxml'));
		
            // Ganti format tanggal input agar sama dengan database
            $tglDrD = date("Y-m-d", strtotime($request['tglDr']));
            $tglSmpD = date("Y-m-d", strtotime($request['tglSmp']));
            
            // Convert tanggal agar ambil start of day/end of day
            $tglDr = Carbon::parse($request->tglDr)->startOfDay();
            $tglSmp = Carbon::parse($request->tglSmp)->endOfDay();
            
            $periode = date("m/Y", strtotime($request['tglDr']));
            $bulan = date("m", strtotime($request['tglDr']));
            $tahun = date("Y", strtotime($request['tglDr']));
			$filtercust = " AND KODEC<>'' " ;
			$filterjual = " AND jual.KODEC<>'' " ;
			$filtertukar = " AND tukar.KODEC<>'' " ;

			if($request->cust1)
			{
				$filtercust = " AND KODEC between '".$request->cust1."' and '".$request->cust2."' " ;
				$filterjual = " AND jual.KODEC between'".$request->cust1."' and '".$request->cust2."' " ;
				$filtertukar = " AND tukar.KODEC between '".$request->cust1."' and '".$request->cust2."' " ;
			}
            $tgawal = $tahun.'-'.$bulan.'-01';
		
			session()->put('filter_cust1', $request->cust1);
			session()->put('filter_nacust1', $request->nacust1);
			session()->put('filter_cust2', $request->cust2);
			session()->put('filter_nacust2', $request->nacust2);
			session()->put('filter_tglDr', $request->tglDr);
			session()->put('filter_tglSmp', $request->tglSmp);

		$queryakum = DB::SELECT("SET @akum:=0;");
		$query = DB::SELECT("
        	SELECT *, if(@kdcust<>KODEC,@akum:=AWAL+MASUK-KELUAR+LAIN,@akum:=@akum+AWAL+MASUK-KELUAR+LAIN) as SALDO,@kdcust:=KODEC as ganti, URUTAN from
		(
			SELECT ' ' AS NO_BUKTI, '$tglDrD'  AS TGL, KODEC AS KODEC, NAMAC AS NAMAC, 
			'SALDO AWAL' URAIAN, 
			SUM(AWAL) AS AWAL, 0 MASUK, 0 KELUAR, 0 AS LAIN, 1 as URUTAN
			from
			(

				SELECT CONCAT(KODEC,'-',CBG) AS KODEC , NAMAC, AW$bulan AS AWAL 
				from custd WHERE KODEC='$cust' and YER='$tahun'
				
				UNION ALL
				
				SELECT CONCAT(KODEC,'-',CBG) AS KODEC, NAMAC, POIN AS AWAL 
				from jual where TGL<'$tglDrD' 
				and KODEC='$cust' and PER='$periode' and  POIN <> 0  union all
		
				SELECT CONCAT(KODEC,'-',CBG) AS KODEC, NAMAC, ( TOTAL * -1 ) AS AWAL 
				from tukar where TGL<'$tglDrD' 
				and KODEC='$cust' and PER='$periode' and  TOTAL <> 0  
				
	
			) as AWAL00
			group by KODEC 
			UNION ALL

			SELECT NO_BUKTI,  DATE_FORMAT(TGL, '%d-%m-%Y') AS TGL, CONCAT(KODEC,'-',CBG) AS KODEC, NAMAC,
			'Point_Masuk-' AS URAIAN, 0 AWAL, poin AS MASUK, 0 AS KELUAR, 0 AS LAIN,  2 as URUTAN 
			from jual where TGL BETWEEN '$tglDrD' and '$tglSmpD' $filterjual and poin <> 0 and PER='$periode' union all


			SELECT NO_BUKTI, DATE_FORMAT(TGL, '%d-%m-%Y') AS TGL, CONCAT(KODEC,'-',CBG) AS KODEC, NAMAC, 'Point-Keluar' AS URAIAN, 0 AWAL, 
			      0 AS MASUK, TOTAL AS KELUAR,  0 AS LAIN, 4 as URUTAN  
			from tukar where TGL BETWEEN '$tglDrD' and '$tglSmpD' $filtertukar and PER='$periode' 


			order by KODEC, TGL, NO_BUKTI, URUTAN ASC
			
		) as kartustok  ;
		");

		$cust = Cust::where('KODEC', '<>','ZZ')->get();
		if($request->has('filter'))
		{
			return view('oreport_piu.kartu')->with(['cust' => $cust])->with(['hasil' => $query]);
		}

		$data=[];
		foreach ($query as $key => $value)
		{
			array_push($data, array(
				'NO_BUKTI' => $query[$key]->NO_BUKTI,
				'TGL' => $query[$key]->TGL,
				// 'KODEC' => $query[$key]->KODEC,
                'KODEC'    => "`".strval($query[$key]->KODEC),
                'CBG'    => "`".strval($query[$key]->CBG),
				'NAMAC' => $query[$key]->NAMAC,
				'URAIAN' => $query[$key]->URAIAN,
				'AWAL' => $query[$key]->AWAL,
				'TOTAL' => $query[$key]->MASUK,
				'BAYAR' => $query[$key]->KELUAR,
				'LAIN' => $query[$key]->LAIN,
				'AKHIR' => $query[$key]->SALDO,
			));
		}
		$PHPJasperXML->setData($data);
		ob_end_clean();
		$PHPJasperXML->outpage("I");
	}
	
}
