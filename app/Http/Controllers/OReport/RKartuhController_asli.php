<?php

namespace App\Http\Controllers\OReport;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\Master\Sup;
use App\Models\Master\Cbg;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use DB;

include_once base_path()."/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";
use PHPJasperXML;

use \koolreport\laravel\Friendship;
use \koolreport\bootstrap4\Theme;

class RKartuhController extends Controller
{

    public function kartu()
    {
		$cbg = Cbg::groupBy('CBG')->get();
		session()->put('filter_cbg', '');

		$sup = Sup::where('KODES', '<>','ZZ')->get();
		session()->put('filter_gol', '');
		session()->put('filter_kodes1', '');
		session()->put('filter_namas1', '');
		session()->put('filter_tglDari', date("d-m-Y"));
		session()->put('filter_tglSampai', date("d-m-Y"));
		
        return view('oreport_hut.kartu')->with(['cbg' => $cbg])->with(['kodes' => $sup])->with(['hasil' => []]);
    }
	
	public function getHutKartu(Request $request)
    {
		$periode = $request->session()->get('periode')['bulan']. '/' . $request->session()->get('periode')['tahun'];
		$bulan = substr($periode,0,2);
		$tahun = substr($periode,3,4);
		$sup = '';
		$filterbelix = '';
		$tgawal = $tahun.'-'.$bulan.'-01';
		
		if ($request->ajax())
		{
			// Ganti format tanggal input agar sama dengan database
			$tglDrD = date("Y-m-d", strtotime($request['tglDr']));
            $tglSmpD = date("Y-m-d", strtotime($request['tglSmp']));
			
			// Convert tanggal agar ambil start of day/end of day
			$tglDr = Carbon::parse($request->tglDr)->startOfDay();
            $tglSmp = Carbon::parse($request->tglSmp)->endOfDay();
            
			if (!empty($request->kodes))
			{
				$filterbelix = " AND belix.KODES='".$request->kodes."' ";
				$sup = " AND KODES='".$request->kodes."' ";
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

			SELECT NO_BUKTI, TGL, KODES, NAMAS, PER$bulan AS TOTAL, 0 AS BAYAR 
			from belix where belix.YER='$tahun' $filterbelix union all

			SELECT NO_BUKTI, TGL, KODES, NAMAS, 0 AS TOTAL, BAYAR  
			from hut where PER='$periode' $sup
			
		) as  kartuh;"
		);
		
		return Datatables::of($query)->addIndexColumn()->make(true);
		
    }	 
	 
	public function sisa()
    {

		$cbg = Cbg::groupBy('CBG')->get();
		session()->put('filter_cbg', '');

		$sup = Sup::where('KODES', '<>','ZZ')->get();
		session()->put('filter_gol', '');
		session()->put('filter_kodes1', '');
		session()->put('filter_namas1', '');
		// session()->put('filter_tglDari', date("d-m-Y"));
		// session()->put('filter_tglSampai', date("d-m-Y"));
		session()->put('filter_perio', session()->get('periode')['bulan']. '/' . session()->get('periode')['tahun']);
		session()->put('filter_flag', '');
		
        return view('oreport_hut.sisa')->with(['kodes' => $sup])->with(['cbg' => $cbg])->with(['hasil' => []]);
    }
	
	public function getHutSisa(Request $request)
    {
		$periode = $request->session()->get('periode')['bulan']. '/' . $request->session()->get('periode')['tahun'];
		$bulan = substr($periode,0,2);
		$tahun = substr($periode,3,4);
		$sup = '';
		$tgawal = $tahun.'-'.$bulan.'-01';
		
		if ($request->ajax())
		{
			// Ganti format tanggal input agar sama dengan database
			$tglDrD = date("Y-m-d", strtotime($request['tglDr']));
            $tglSmpD = date("Y-m-d", strtotime($request['tglSmp']));
			
			// Convert tanggal agar ambil start of day/end of day
			$tglDr = Carbon::parse($request->tglDr)->startOfDay();
            $tglSmp = Carbon::parse($request->tglSmp)->endOfDay();
			
			// Check Filter
			/*
			if (!empty($request->acno))
			{
				$query = $query->where('BACNO', $request->acno);
			}
			
			if (!empty($request->tglDr) && !empty($request->tglSmp))
			{
				$query = $query->whereBetween('TGL', [$tglDrD, $tglSmp]);
			}
			*/
			$filterkodes='';
			if (!empty($request->kodes))
			{
				$filterkodes = " and KODES='".$request->kodes."' ";
			}
			
			$periode = date("m/Y", strtotime($request['tglDr']));
			//$bulan = date("m", strtotime($request['tglDr']));
			//$tahun = date("Y", strtotime($request['tglDr']));
			$tgawal = $tahun.'-'.$bulan.'-01';
		}


		
		$query = DB::SELECT("
        	SELECT NO_BUKTI, TGL, KODES, NAMAS, 
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
			from belix WHERE
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
			END ) and TGL between '$tglDrD' and '$tglSmpD' $filterkodes 
			order by KODES,NO_BUKTI;
        	;"
		);
		
		return Datatables::of($query)->addIndexColumn()->make(true);
		
    }	 

	public function jasperHutSisaReport(Request $request) 
	{
		$file 	= 'sisah';
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
			
			$filterkodes='';
			if (!empty($request->kodes))
			{
				$filterkodes = " and KODES='".$request->kodes."' ";
			}
			
			$filterflag='';
			if (!empty($request->flag))
			{
				if ($request->flag=="BELI")
				{
					$filterflag = " and FLAG in ('BL','BD','BN') ";
				}
				else if ($request->flag=="UM")
				{
					$filterflag = " and FLAG='UM' ";
				}
				else if ($request->flag=="THUT")
				{
					$filterflag = " and FLAG='TH' ";
				}
			}

			if($request['cbg'])
			{
				$cbg = $request['cbg'];
			}

			if (!empty($request->cbg))
			{
				$filtercbg = " and hut.CBG='".$request->cbg."' ";
			}
			
			session()->put('filter_gol', $request->gol);
			session()->put('filter_kodes1', $request->kodes);
			session()->put('filter_namas1', $request->NAMAS);
			// session()->put('filter_tglDari', $request->tglDr);
			// session()->put('filter_tglSampai', $request->tglSmp);
			session()->put('filter_perio', $request->perio);
			session()->put('filter_lebih30', $request->lebih30);
			session()->put('filter_flag', $request->flag);
			session()->put('filter_cbg', $request->cbg);

		$query = DB::SELECT("
        	SELECT NO_BUKTI, TGL, trim(KODES) as KODES, NAMAS, (PER$bulan-PERB$bulan) as SISA, FLAG
			from belix WHERE PER$bulan-PERB$bulan<>0 $filterkodes $filterflag $filtercbg
			order by KODES,NO_BUKTI;
		");
		
		if($request->has('filter'))
		{
			return view('oreport_hut.sisa')->with(['cbg' => $cbg])->with(['hasil' => $query]);
		}

		$data=[];
		foreach ($query as $key => $value)
		{
			array_push($data, array(
				'NO_BUKTI' => $query[$key]->NO_BUKTI,
				'TGL' => date("d/m/Y", strtotime($query[$key]->TGL)),
				'KODES' => $query[$key]->KODES,
				'NAMAS' => $query[$key]->NAMAS,
				'TOTAL' => $query[$key]->SISA,
			));
		}
		$PHPJasperXML->setData($data);
		ob_end_clean();
		$PHPJasperXML->outpage("I");
	}
	
	public function jasperHutKartu(Request $request) 
	{
		
		$file 	= 'kartuh';
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
			$tgawal = $tahun.'-'.$bulan.'-01';
			
			$periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
     		$bulan = substr($periode,0,2);
	    	$tahun = substr($periode,3,4);
		
			$sup = '';
			$filterbelix = '';
			if (!empty($request->kodes))
			{
				$filterbelix = " AND belix.KODES='".$request->kodes."' ";
				$sup = " AND KODES='".$request->kodes."' ";
			}

			session()->put('filter_gol', $request->gol);
			session()->put('filter_kodes1', $request->kodes);
			session()->put('filter_namas1', $request->NAMAS);
			session()->put('filter_tglDari', $request->tglDr);
			session()->put('filter_tglSampai', $request->tglSmp);
		
		$queryakum = DB::SELECT("SET @akum:=0;");
		$query = DB::SELECT("
        	SELECT *,@akum:=@akum+TOTAL-BAYAR SALDO from
		(

			SELECT NO_BUKTI, TGL, KODES, NAMAS, PER$bulan AS TOTAL, 0 AS BAYAR 
			from belix where belix.YER='$tahun' and PER$bulan<>0 $filterbelix union all

			SELECT NO_BUKTI, TGL, KODES, NAMAS, 0 AS TOTAL, BAYAR  
			from hut where PER='$periode' $sup $filtercbg
			
		) as  kartuh;
		");

		if($request->has('filter'))
		{
			$cbg = Cbg::groupBy('CBG')->get();
			return view('oreport_hut.kartu')->with(['hasil' => $query]);
		}

		$data=[];
		foreach ($query as $key => $value)
		{
			array_push($data, array(
				'NO_BUKTI' => $query[$key]->NO_BUKTI,
				'TGL' => $query[$key]->TGL,
				'KODES' => $query[$key]->KODES,
				'NAMAS' => $query[$key]->NAMAS,
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
