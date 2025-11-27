<?php

namespace App\Http\Controllers\OReport;

use App\Http\Controllers\Controller;
use App\Models\Master\Cbg;
use App\Models\Master\Brg;
use App\Models\Master\Perid;

use Carbon\Carbon;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use DB;

include_once base_path()."/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";
use PHPJasperXML;

use \koolreport\laravel\Friendship;
use \koolreport\bootstrap4\Theme;

class RBrgController extends Controller
{
	
   public function report()
    {
		$cbg = Cbg::groupBy('CBG')->get();
		session()->put('filter_cbg', '');

		$kd_brg = Brg::query()->get();
		$per = Perid::query()->get();
		session()->put('filter_per', '');

        return view('oreport_brg.report')->with(['kd_brg' => $kd_brg])->with(['per' => $per])->with(['cbg' => $cbg])->with(['hasil' => []]);
    }
	
   
	public function jasperBrgReport(Request $request) 
	{
		$file 	= 'brgpr';
		$PHPJasperXML = new PHPJasperXML();
		$PHPJasperXML->load_xml_file(base_path().('/app/reportc01/phpjasperxml/'.$file.'.jrxml'));
		
		
        if ($request->session()->has('periode')) 
		{
			$periode = $request->session()->get('periode')['bulan']. '/' . $request->session()->get('periode')['tahun'];
		} else
		{
			$periode = '';
		}
		
		if($request['perio'])
		{
			$periode = $request['perio'];
		}
		
		if($request['cbg'])
		{
			$cbg = $request['cbg'];
		}

			
		if (!empty($request->cbg))
		{
			$filtercbg = " and brgd.CBG='".$request->cbg."' ";
		}
		
		
		session()->put('filter_cbg', $request->cbg);


		$bulan = substr($periode,0,2);
		$tahun = substr($periode,3,4);
		
		$queryakum = DB::SELECT("SET @akum:=0;");
		$query = DB::SELECT("
		SELECT brg.KD_BRG,brg.NA_BRG,brgd.AW$bulan as AW, brgd.MA$bulan as MA, 
		    brgd.KE$bulan as KE,brgd.LN$bulan as LN,brgd.AK$bulan as AK, 
			brgd.HRT$bulan as HRT,brgd.NIW$bulan as NIW,brgd.NIM$bulan as NIM,brgd.NIK$bulan as NIK,
		brgd.NIL$bulan as NIL,brgd.NIR$bulan as NIR
		FROM brg,brgd
		WHERE brg.KD_BRG=brgd.KD_BRG and brgd.YER='$tahun'
		$filtercbg
		group by KD_BRG
		order by KD_BRG;
		");

		session()->put('filter_per', $periode);

		if($request->has('filter'))
		{
			$per = Perid::query()->get();
			$cbg = Cbg::groupBy('CBG')->get();

			return view('oreport_brg.report')->with(['per' => $per])->with(['cbg' => $cbg])->with(['hasil' => $query]);
		}

		$data=[];
		foreach ($query as $key => $value)
		{
			array_push($data, array(
				'KD_BRG' => $query[$key]->KD_BRG,
                // 'KD_BRG'    => "`".strval($query[$key]->KD_BRG),
				'NA_BRG' => $query[$key]->NA_BRG,
				'AW' => $query[$key]->AW,
				'MA' => $query[$key]->MA,
				'KE' => $query[$key]->KE,
				'LN' => $query[$key]->LN,
				'AK' => $query[$key]->AK,
				'HRT' => $query[$key]->HRT,
				'HRT_2' => $query[$key]->HRT_2,
				'NIW' => $query[$key]->NIW,
				'NIM' => $query[$key]->NIM,
				'NIK' => $query[$key]->NIK,
				'NIL' => $query[$key]->NIL,
				'NIR' => $query[$key]->NIR,
			));
		}
		$PHPJasperXML->setData($data);
		ob_end_clean();
		$PHPJasperXML->outpage("I");
	}
	
}
