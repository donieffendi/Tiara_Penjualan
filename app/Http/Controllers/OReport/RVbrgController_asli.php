<?php

namespace App\Http\Controllers\OReport;

use App\Http\Controllers\Controller;
use App\Models\Master\Cbg;
use App\Models\Master\Vbrg;
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

class RVbrgController extends Controller
{
	
   public function report()
    {
		$cbg = Cbg::groupBy('CBG')->get();
		session()->put('filter_cbg', '');

		$kd_brg = Vbrg::query()->get();
		$per = Perid::query()->get();
		session()->put('filter_per', '');

        return view('oreport_vbrg.report')->with(['kd_brg' => $kd_brg])->with(['per' => $per])->with(['cbg' => $cbg])->with(['hasil' => []]);
    }
	
   
	public function jasperVbrgReport(Request $request) 
	{
		$file 	= 'vbrgpr';
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
			$filtercbg = " and vbrgd.CBG='".$request->cbg."' ";
		}
		
		
		session()->put('filter_cbg', $request->cbg);
		session()->put('filter_per', $periode);

		$bulan = substr($periode,0,2);
		$tahun = substr($periode,3,4);
		
		$queryakum = DB::SELECT("SET @akum:=0;");
		$query = DB::SELECT("SELECT vbrg.KD_BRG,vbrg.NA_BRG,vbrgd.AW$bulan as AW, vbrgd.MA$bulan as MA, 
		    vbrgd.KE$bulan as KE,vbrgd.LN$bulan as LN,vbrgd.AK$bulan as AK, 
			vbrgd.HRT$bulan as HRT,vbrgd.NIW$bulan as NIW,vbrgd.NIM$bulan as NIM,vbrgd.NIK$bulan as NIK,
		vbrgd.NIL$bulan as NIL,vbrgd.NIR$bulan as NIR
		FROM vbrg,vbrgd
		WHERE vbrg.KD_BRG=vbrgd.KD_BRG and vbrgd.YER='$tahun'
		$filtercbg
		group by KD_BRG
		order by KD_BRG;
		");

		

		if($request->has('filter'))
		{
			$per = Perid::query()->get();
			$cbg = Cbg::groupBy('CBG')->get();

			return view('oreport_vbrg.report')->with(['per' => $per])->with(['cbg' => $cbg])->with(['hasil' => $query]);
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
