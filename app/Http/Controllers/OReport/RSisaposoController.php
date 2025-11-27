<?php

namespace App\Http\Controllers\OReport;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Master\Cust;
use DataTables;
use Auth;
use DB;

include_once base_path()."/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";
use PHPJasperXML;

use \koolreport\laravel\Friendship;
use \koolreport\bootstrap4\Theme;

class RSisaposoController extends Controller
{

	public function index()
    {
		session()->put('filter_kodet1', '');
		session()->put('filter_namat1', '');
		session()->put('filter_brg1', '');
		session()->put('filter_nabrg1', '');
		session()->put('filter_perio', session()->get('periode')['bulan']. '/' . session()->get('periode')['tahun']);

        return view('oreport_sisaposo.report')->with(['hasil' => []]);
    }

	public function jasperSisaposo(Request $request) 
	{
		$file 	= 'sisaposon';
		$PHPJasperXML = new PHPJasperXML();
		$PHPJasperXML->load_xml_file(base_path().('/app/reportc01/phpjasperxml/'.$file.'.jrxml'));
		
			// Check Filter
            $periode = session()->get('periode')['bulan'].'/'.session()->get('periode')['tahun'];
			$filterkodet = "";
			$filter_perio = "";
			$kdbrg = "";
			$nabrg = "";
			if (!empty($request->kodet))
			{
				$filterkodet = " WHERE a.KODET='".$request->kodet."' ";
			}
			
			if (!empty($request->brg1))
			{
				$kdbrg = $request->brg1;
				$nabrg = $request->nabrg1;
			}

			if (!empty($request->perio))
			{
				$periode = $request->perio;
				$filter_perio = " and PER='".$request->perio."' ";
			}

			session()->put('filter_kodet1', $request->kodet);
			session()->put('filter_namat1', $request->NAMAT);
			session()->put('filter_brg1', $request->brg1);
			session()->put('filter_nabrg1', $request->nabrg1);
			session()->put('filter_perio', $request->perio);
			
		$query = DB::SELECT("SELECT KODET, NAMAT, KD_BRG, NA_BRG, coalesce(SISAPO,0) as SISAPO, coalesce(SISASO,0) as SISASO, coalesce(SISASO,0)-coalesce(SISAPO,0) as KIRIM, '".$periode."' as PER from 
		(
			SELECT KODET, NAMAT, '$kdbrg' as KD_BRG, '$nabrg' as NA_BRG, 
			(SELECT sum(SISA) from po WHERE KODET=tujuan.KODET and KD_BRG='$kdbrg' and SLS=0) as SISAPO, 
			(SELECT sum(SISA) from so WHERE KODET=tujuan.KODET and KD_BRG='$kdbrg' and SLS=0) as SISASO 
			from tujuan
		) as sisaposo
		WHERE SISAPO IS NOT null or SISASO IS NOT null
		ORDER BY KODET;
		"); 
		
		if($request->has('filter'))
		{
			return view('oreport_sisaposo.report')->with(['hasil' => $query]);
		}

		$data=[];
		foreach ($query as $key => $value)
		{
			array_push($data, array(
				'KODET' => $query[$key]->KODET,
				'NAMAT' => $query[$key]->NAMAT,
				'SISAPO' => $query[$key]->SISAPO,
				'SISASO' => $query[$key]->SISASO,
				'KIRIM' => $query[$key]->KIRIM,
				'KD_BRG' => $query[$key]->KD_BRG,
				'NA_BRG' => $query[$key]->NA_BRG,
				'PER' => $query[$key]->PER,
			));
		}
		$PHPJasperXML->setData($data);
		ob_end_clean();
		$PHPJasperXML->outpage("I");
	}
	
}