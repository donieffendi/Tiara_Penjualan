<?php

namespace App\Http\Controllers\OReport;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\Master\Brg;
use App\Models\Master\Cbg;

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
class RKarstk_vbrgController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function kartu()
    {
		session()->put('filter_brg1', '');
		session()->put('filter_nabrg1', '');
		session()->put('filter_brg2', '');
		session()->put('filter_nabrg2', '');
		session()->put('filter_tglDr', now()->format('d-m-Y'));
		session()->put('filter_tglSmp', now()->format('d-m-Y'));
		$brg = Brg::orderBy('KD_BRG', 'ASC')->get();
		$cbg = Cbg::groupBy('CBG')->get();
// GANTI 3 //
        return view('oreport_vbrg.kartu')->with(['brg' => $brg])->with(['cbg' => $cbg])->with(['hasil' => []]);
		
    }
	


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
	public function jasperStokKartu(Request $request) 
	{
		$file 	= 'karstk';
		$PHPJasperXML = new PHPJasperXML();
		$PHPJasperXML->load_xml_file(base_path().('/app/reportc01/phpjasperxml/'.$file.'.jrxml'));
		
            // Ganti format tanggal input agar sama dengan database
            $tglDrD = date("Y-m-d", strtotime($request['tglDr']));
            $tglSmpD = date("Y-m-d", strtotime($request['tglSmp']));
            
            // Convert tanggal agar tukar start of day/end of day
            $tglDr = Carbon::parse($request->tglDr)->startOfDay();
            $tglSmp = Carbon::parse($request->tglSmp)->endOfDay();
            
            $periode = date("m/Y", strtotime($request['tglDr']));
            $bulan = date("m", strtotime($request['tglDr']));
            $tahun = date("Y", strtotime($request['tglDr']));
			$filterbrg = " AND KD_BRG<>'' " ;
			$filterbeli = " AND belid.KD_BRG<>'' " ;
			$filtertukar = " AND tukard.KD_BRG<>'' " ;
			$filterstock = " AND stockad.KD_BRG<>'' " ;
			if($request->brg1)
			{
				$filterbrg = " AND KD_BRG between '".$request->brg1."' and '".$request->brg2."' " ;
				$filterbeli = " AND belid.KD_BRG between'".$request->brg1."' and '".$request->brg2."' " ;
				$filtertukar = " AND tukard.KD_BRG between '".$request->brg1."' and '".$request->brg2."' " ;
				$filterstock = " AND stockad.KD_BRG between '".$request->brg1."' and '".$request->brg2."' " ;
			}
            $tgawal = $tahun.'-'.$bulan.'-01';
		
			session()->put('filter_brg1', $request->brg1);
			session()->put('filter_nabrg1', $request->nabrg1);
			session()->put('filter_brg2', $request->brg2);
			session()->put('filter_nabrg2', $request->nabrg2);
			session()->put('filter_tglDr', $request->tglDr);
			session()->put('filter_tglSmp', $request->tglSmp);

		$queryakum = DB::SELECT("SET @akum:=0;");
		$query = DB::SELECT("
        	SELECT *, if(@kdbrg<>KD_BRG,@akum:=AWAL+MASUK-KELUAR+LAIN,@akum:=@akum+AWAL+MASUK-KELUAR+LAIN) as SALDO,@kdbrg:=KD_BRG as ganti, URUTAN from
		(
			SELECT ' ' AS NO_BUKTI, '$tglDrD'  AS TGL, KD_BRG AS KD_BRG, NA_BRG AS NA_BRG, 
			'SALDO AWAL' URAIAN, 
			SUM(AWAL) AS AWAL, 0 MASUK, 0 KELUAR, 0 AS LAIN, 1 as URUTAN
			from
			(

				SELECT CONCAT(KD_BRG,'-',CBG) AS KD_BRG , NA_BRG, AW$bulan AS AWAL 
				from brgd WHERE KD_BRG='$brg' and YER='$tahun'
				
				UNION ALL
				
				SELECT CONCAT(belid.KD_BRG,'-',belid.CBG) AS KD_BRG, belid.NA_BRG, belid.QTY AS AWAL 
				from beli, belid where beli.NO_BUKTI = belid.NO_BUKTI and beli.TGL<'$tglDrD' 
				and belid.KD_BRG='$brg' and beli.PER='$periode' and  belid.QTY <> 0  union all
		
				SELECT CONCAT(tukard.KD_BRG,'-',tukard.CBG) AS KD_BRG, tukard.NA_BRG, ( tukard.QTY * -1 ) AS AWAL 
				from tukar, tukard where tukar.NO_BUKTI = tukard.NO_BUKTI and tukar.TGL<'$tglDrD' 
				and tukard.KD_BRG='$brg' and tukar.PER='$periode' and  tukard.QTY <> 0  union all
				
				
				SELECT CONCAT(stockad.KD_BRG,'-',stockad.CBG) as KD_BRG, stockad.NA_BRG, stockad.QTY AS AWAL 
				from stocka, stockad where stocka.NO_BUKTI = stockad.NO_BUKTI and stocka.TGL<'$tglDrD' 
				and stockad.KD_BRG='$brg' and stocka.PER='$periode' 

				
			) as AWAL00
			group by KD_BRG 
			UNION ALL

			SELECT beli.NO_BUKTI,  DATE_FORMAT(beli.TGL, '%d-%m-%Y') AS TGL, CONCAT(belid.KD_BRG,'-',belid.CBG) AS KD_BRG, belid.NA_BRG, CONCAT('BELI-',TRIM(beli.NAMAS)) AS URAIAN, 0 AWAL, belid.QTY AS MASUK, 0 AS KELUAR, 0 AS LAIN,  2 as URUTAN 
			from beli, belid where beli.TGL BETWEEN '$tglDrD' and '$tglSmpD' $filterbeli and belid.QTY <> 0 and beli.PER='$periode' union all


			SELECT tukar.NO_BUKTI, DATE_FORMAT(tukar.TGL, '%d-%m-%Y') AS TGL, CONCAT(tukard.KD_BRG,'-',tukard.CBG) AS KD_BRG,  tukard.NA_BRG, CONCAT('JUAL-',TRIM(tukar.NAMAC)) AS URAIAN, 0 AWAL, 0 AS MASUK, tukard.QTY AS KELUAR,  0 AS LAIN, 4 as URUTAN  
			from tukar, tukard where tukar.TGL BETWEEN '$tglDrD' and '$tglSmpD' $filtertukar and tukar.PER='$periode' union all

			SELECT stocka.NO_BUKTI, DATE_FORMAT(stocka.TGL, '%d-%m-%Y') AS TGL, CONCAT(stockad.KD_BRG,'-',stockad.CBG) as KD_BRG, stockad.NA_BRG, CONCAT('KOREKSI-') AS URAIAN, 0 AWAL, 0 AS MASUK, 0 AS KELUAR, stockad.QTY AS LAIN, 5 as URUTAN  
			from stocka, stockad where stocka.NO_BUKTI = stockad.NO_BUKTI and stocka.TGL BETWEEN '$tglDrD' and '$tglSmpD' $filterstock and stocka.PER='$periode' 
			
			order by KD_BRG, TGL, NO_BUKTI, URUTAN ASC
			
		) as kartustok  ;
		");

		$brg = Brg::where('KD_BRG', '<>','ZZ')->get();
		if($request->has('filter'))
		{
			return view('oreport_vbrg.kartu')->with(['brg' => $brg])->with(['hasil' => $query]);
		}

		$data=[];
		foreach ($query as $key => $value)
		{
			array_push($data, array(
				'NO_BUKTI' => $query[$key]->NO_BUKTI,
				'TGL' => $query[$key]->TGL,
				// 'KD_BRG' => $query[$key]->KD_BRG,
                'KD_BRG'    => "`".strval($query[$key]->KD_BRG),
                'CBG'    => "`".strval($query[$key]->CBG),
				'NA_BRG' => $query[$key]->NA_BRG,
				'URAIAN' => $query[$key]->URAIAN,
				'AWAL' => $query[$key]->AWAL,
				'MASUK' => $query[$key]->MASUK,
				'KELUAR' => $query[$key]->KELUAR,
				'LAIN' => $query[$key]->LAIN,
				'AKHIR' => $query[$key]->SALDO,
			));
		}
		$PHPJasperXML->setData($data);
		ob_end_clean();
		$PHPJasperXML->outpage("I");
	}
	
}
