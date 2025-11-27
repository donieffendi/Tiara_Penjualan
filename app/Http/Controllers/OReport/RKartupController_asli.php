<?php

namespace App\Http\Controllers\OReport;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\Master\Cust;

use Illuminate\Http\Request;
use DataTables;
use Auth;
use DB;

include_once base_path()."/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";
use PHPJasperXML;

use \koolreport\laravel\Friendship;
use \koolreport\bootstrap4\Theme;

class RKartupController extends Controller
{
    public function kartu()
    {
		$cust = Cust::where('KODEC', '<>','ZZ')->get();
		session()->put('filter_gol', '');
		session()->put('filter_kodec1', '');
		session()->put('filter_namac1', '');
		session()->put('filter_tglDari', date("d-m-Y"));
		session()->put('filter_tglSampai', date("d-m-Y"));
		
        return view('oreport_piu.kartu')->with(['kodec' => $cust])->with(['hasil' => []]);
    }
	
	public function getPiuKartu(Request $request)
    {
		$periode = $request->session()->get('periode')['bulan']. '/' . $request->session()->get('periode')['tahun'];
		$bulan = substr($periode,0,2);
		$tahun = substr($periode,3,4);
		$cust = '';
		$tgawal = $tahun.'-'.$bulan.'-01';
		
		if ($request->ajax())
		{
			// Ganti format tanggal input agar sama dengan database
			$tglDrD = date("Y-m-d", strtotime($request['tglDr']));
            $tglSmpD = date("Y-m-d", strtotime($request['tglSmp']));
			
			// Convert tanggal agar ambil start of day/end of day
			$tglDr = Carbon::parse($request->tglDr)->startOfDay();
            $tglSmp = Carbon::parse($request->tglSmp)->endOfDay();
			
			if (!empty($request->kodec))
			{
				$cust = " AND KODEC='".$request->kodec."' ";
			}
			
			// Check Filter
			/*
			if (!empty($request->tglDr) && !empty($request->tglSmp))
			{
				$query = $query->whereBetween('TGL', [$tglDrD, $tglSmp]);
			}
			*/
			
			$periode = date("m/Y", strtotime($request['tglDr']));
			$bulan = date("m", strtotime($request['tglDr']));
			$tahun = date("Y", strtotime($request['tglDr']));
			$tgawal = $tahun.'-'.$bulan.'-01';
		}
		
		$queryakum = DB::SELECT("SET @akum:=0;");
		$query = DB::SELECT("
		SELECT *,@akum:=@akum+TOTAL-BAYAR SALDO from
		(

			SELECT NO_BUKTI, TGL, KODEC, NAMAC, PER$bulan AS TOTAL, 0 AS BAYAR 
			from jualx where YER='$tahun' $cust  union all

			SELECT NO_BUKTI, TGL, KODEC, NAMAC, 0 AS TOTAL, BAYAR  
			from piu where PER='$periode' $cust 
		)  as kartup;"
		);
		
		return Datatables::of($query)->addIndexColumn()->make(true);
		
    }	 
	 
    public function sisa()
    {
		$cust = Cust::where('KODEC', '<>','ZZ')->get();
		session()->put('filter_gol', '');
		session()->put('filter_kodec1', '');
		session()->put('filter_namac1', '');
		session()->put('filter_kodet1', '');
		session()->put('filter_namat1', '');
		// session()->put('filter_tglDari', date("d-m-Y"));
		// session()->put('filter_tglSampai', date("d-m-Y"));
		session()->put('filter_lebih30', '');
		session()->put('filter_perio', session()->get('periode')['bulan']. '/' . session()->get('periode')['tahun']);
		
        return view('oreport_piu.sisa')->with(['kodec' => $cust])->with(['hasil' => []]);
    }
	
	public function getPiuSisa(Request $request)
    {
		$periode = $request->session()->get('periode')['bulan']. '/' . $request->session()->get('periode')['tahun'];
		$bulan = substr($periode,0,2);
		$tahun = substr($periode,3,4);
		$cust = '';
		$tgawal = $tahun.'-'.$bulan.'-01';
		
			// Ganti format tanggal input agar sama dengan database
			$tglDrD = date("Y-m-d", strtotime($request['tglDr']));
            		$tglSmpD = date("Y-m-d", strtotime($request['tglSmp']));
			
			// Convert tanggal agar ambil start of day/end of day
			$tglDr = Carbon::parse($request->tglDr)->startOfDay();
            		$tglSmp = Carbon::parse($request->tglSmp)->endOfDay();
			
			$periode = date("m/Y", strtotime($request['tglDr']));
			$bulan = date("m", strtotime($request['tglDr']));
			$tahun = date("Y", strtotime($request['tglDr']));
			$tgawal = $tahun.'-'.$bulan.'-01';
			$filterkodec='';
			if (!empty($request->kodec))
			{
				$filterkodec = " and KODEC='".$request->kodec."' ";
			}
			
			$filterlebih30=" ";
			if ($request->lebih30==1)
			{
				$filterlebih30 = " and DATEDIFF(date(now()),TGL)>=30 ";
			}

		
		$query = DB::SELECT("
		SELECT NO_BUKTI, TGL, KODEC, NAMAC, NAMAT, 
		(CASE 
			WHEN month(TGL)=1 then PER01-PERB01
			WHEN month(TGL)=2 then PER02-PERB02
			WHEN month(TGL)=3 then PER03-PERB03
			WHEN month(TGL)=4 then PER04-PERB04
			WHEN month(TGL)=5 then PER05-PERB05
			WHEN month(TGL)=6 then PER06-PERB06
			WHEN month(TGL)=7 then PER07-PERB07
			WHEN month(TGL)=8 then PER08-PERB08
			WHEN month(TGL)=9 then PER09-PERB09
			WHEN month(TGL)=10 then PER10-PERB10
			WHEN month(TGL)=11 then PER11-PERB11
			WHEN month(TGL)=12 then PER12-PERB12
		END ) as SISA
		from jualx where
		(CASE 
			WHEN month(TGL)=1 then PER01-PERB01<>0
			WHEN month(TGL)=2 then PER02-PERB02<>0
			WHEN month(TGL)=3 then PER03-PERB03<>0
			WHEN month(TGL)=4 then PER04-PERB04<>0
			WHEN month(TGL)=5 then PER05-PERB05<>0
			WHEN month(TGL)=6 then PER06-PERB06<>0
			WHEN month(TGL)=7 then PER07-PERB07<>0
			WHEN month(TGL)=8 then PER08-PERB08<>0
			WHEN month(TGL)=9 then PER09-PERB09<>0
			WHEN month(TGL)=10 then PER10-PERB10<>0
			WHEN month(TGL)=11 then PER11-PERB11<>0
			WHEN month(TGL)=12 then PER12-PERB12<>0
		END ) and TGL between '$tglDrD' and '$tglSmpD' $filterkodec $filterlebih30
		order by KODEC,NO_BUKTI;
		;"
		);
		
		return Datatables::of($query)->addIndexColumn()->make(true);
		
    }	

	public function jasperPiuSisaReport(Request $request) 
	{
		$file 	= 'sisap';
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
			$tgawal = $tahun.'-'.$bulan.'-01';
			
			$periode = $request->session()->get('periode')['bulan']. '/' . $request->session()->get('periode')['tahun'];
			if(!empty($request->perio))
			{
				$periode = $request->perio;
			}
			$bulan = substr($periode,0,2);
			$tahun = substr($periode,3,4);
			
			if (!empty($request->kodet))
			{
				$filterkodet = " WHERE KODET='".$request->kodet."' ";
			}
			
			$filterkodec='';
			if (!empty($request->kodec))
			{
				$filterkodec = " and KODEC='".$request->kodec."' ";
			}
			
/* 			$filterlebih30=" ";
			if ($request->lebih30==1)
			{
				$filterlebih30 = " and DATEDIFF(date(now()),TGL)>=30 ";
			} */

			session()->put('filter_gol', $request->gol);
			session()->put('filter_kodec1', $request->kodec);
			session()->put('filter_namac1', $request->NAMAC);

			// session()->put('filter_tglDari', $request->tglDr);
			// session()->put('filter_tglSampai', $request->tglSmp);
			session()->put('filter_perio', $request->perio);
		//	session()->put('filter_lebih30', $request->lebih30);

		$query = DB::SELECT("
		SELECT * from 
		(
			SELECT NO_BUKTI, TGL, KODEC, NAMAC, 
			(PER$bulan-PERB$bulan) as SISA
			from jualx where PER$bulan-PERB$bulan<>0 $filterkodec 
		) as sisap
		$filterkodet
		order by KODEC,NO_BUKTI
		");
		
		if($request->has('filter'))
		{
			return view('oreport_piu.sisa')->with(['hasil' => $query]);
		}

		$data=[];
		foreach ($query as $key => $value)
		{
			array_push($data, array(
				'NO_BUKTI' => $query[$key]->NO_BUKTI,
				'TGL' => $query[$key]->TGL,
				'KODEC' => $query[$key]->KODEC,
				'NAMAC' => $query[$key]->NAMAC,
				'TOTAL' => $query[$key]->SISA,
				'NO_SO' => $query[$key]->NO_SO,
			));
		}
		$PHPJasperXML->setData($data);
		ob_end_clean();
		$PHPJasperXML->outpage("I");
	}
	
	public function jasperPiuKartu(Request $request) 
	{
		$file 	= 'kartup';
		$PHPJasperXML = new PHPJasperXML();
		$PHPJasperXML->load_xml_file(base_path().('/app/reportc01/phpjasperxml/'.$file.'.jrxml'));
	
			// Ganti format tanggal input agar sama dengan database
			$tglDrD = date("Y-m-d", strtotime($request['tglDr']));
            $tglSmpD = date("Y-m-d", strtotime($request['tglSmp']));
			
			// Convert tanggal agar ambil start of day/end of day
			$tglDr = Carbon::parse($request->tglDr)->startOfDay();
            $tglSmp = Carbon::parse($request->tglSmp)->endOfDay();
			
			//$periode = date("m/Y", strtotime($request['tglDr']));
			//$bulan = date("m", strtotime($request['tglDr']));
			//$tahun = date("Y", strtotime($request['tglDr']));
			
			$periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
     		$bulan = substr($periode,0,2);
	    	$tahun = substr($periode,3,4);
			$cust = '';
			$tgawal = $tahun.'-'.$bulan.'-01';
			
			if (!empty($request->kodec))
			{
				$cust = " AND KODEC='".$request->kodec."' ";
			}
		
			session()->put('filter_gol', $request->gol);
			session()->put('filter_kodec1', $request->kodec);
			session()->put('filter_namac1', $request->NAMAC);
			session()->put('filter_tglDari', $request->tglDr);
			session()->put('filter_tglSampai', $request->tglSmp);

		$queryakum = DB::SELECT("SET @akum:=0;");
		$query = DB::SELECT("
        SELECT *,@akum:=@akum+TOTAL-BAYAR SALDO from
		(

			SELECT NO_BUKTI, TGL, KODEC, NAMAC, PER$bulan AS TOTAL, 0 AS BAYAR 
			from jualx where YER='$tahun' and PER$bulan<>0 $cust union all

			SELECT NO_BUKTI, TGL, KODEC, NAMAC, 0 AS TOTAL, BAYAR  
			from piu where PER='$periode' $cust 
		)  as kartup;
		");

		if($request->has('filter'))
		{
			return view('oreport_piu.kartu')->with(['hasil' => $query]);
		}

		$data=[];
		foreach ($query as $key => $value)
		{
			array_push($data, array(
				'NO_BUKTI' => $query[$key]->NO_BUKTI,
				'TGL' => $query[$key]->TGL,
				'KODEC' => $query[$key]->KODEC,
				'NAMAC' => $query[$key]->NAMAC,
				'TOTAL' => $query[$key]->TOTAL,
				'BAYAR' => $query[$key]->BAYAR,
				'SALDO' => $query[$key]->SALDO,
			));
		}
		$PHPJasperXML->setData($data);
		ob_end_clean();
		$PHPJasperXML->outpage("I");
	}
	
}