<?php

namespace App\Http\Controllers\OReport;

use App\Http\Controllers\Controller;
use App\Models\Master\Cust;
use App\Models\Master\Perid;
use App\Models\Master\Cbg;

use Carbon\Carbon;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use DB;

include_once base_path()."/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";
use PHPJasperXML;

use \koolreport\laravel\Friendship;
use \koolreport\bootstrap4\Theme;

class RVipController extends Controller
{
   public function report()
    {
		$cbg = Cbg::groupBy('CBG')->get();
		session()->put('filter_cbg', '');

		$kodec = Cust::query()->get();
		$per = Perid::query()->get();
		session()->put('filter_per', '');
		
        return view('oreport_vip.report')->with(['kodec' => $kodec])->with(['per' => $per])->with(['cbg' => $cbg])->with(['hasil' => []]);
    }
	
	
	 
	public function jasperVipReport(Request $request) 
	{
		$file 	= 'vippr';
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
			$filtercbg = " and custd.CBG='".$request->cbg."' ";
		}
		
			

		$bulan = substr($periode,0,2);
		$tahun = substr($periode,3,4);
		
		
		// $queryakum = DB::SELECT("SET @tglx:=last_day(concat('$tahun','-','$bulan','-01'));");
		$query = DB::SELECT("SELECT KODEC, NAMAC, ALAMAT, TPOIN from cust
								WHERE TPOIN >= 1500;
		");

		session()->put('filter_per', $periode);

		if($request->has('filter'))
		{
			$per = Perid::query()->get();
			$cbg = Cbg::groupBy('CBG')->get();
			return view('oreport_vip.report')->with(['per' => $per])->with(['cbg' => $cbg])->with(['hasil' => $query]);
		}

		$data=[];
		foreach ($query as $key => $value)
		{
			array_push($data, array(
				'KODEC' => $query[$key]->KODEC,
				'NAMAC' => $query[$key]->NAMAC,
				'ALAMAT' => $query[$key]->ALAMAT,
				'TPOIN' => $query[$key]->TPOIN,
				// 'KE' => $query[$key]->KE,
				// 'LN' => $query[$key]->LN,
				// 'AK' => $query[$key]->AK,
				// 'SATU' => $query[$key]->SATU,
				// 'DUA' => $query[$key]->DUA,
				// 'TIGA' => $query[$key]->TIGA,
				// 'SALDO' => $query[$key]->SALDO,
			));
		}
		$PHPJasperXML->setData($data);
		ob_end_clean();
		$PHPJasperXML->outpage("I");
	}

}
