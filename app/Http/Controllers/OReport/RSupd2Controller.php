<?php

namespace App\Http\Controllers\OReport;

use App\Http\Controllers\Controller;
use App\Models\Master\Supd2;
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

class RSupd2Controller extends Controller
{

   public function report()
    {
		$cbg = Cbg::groupBy('CBG')->get();
		session()->put('filter_cbg', '');

		$supp = Supd2::query()->get();
		$per = Perid::query()->get();
		session()->put('filter_per', '');

		session()->put('filter_kodes1', '');
		session()->put('filter_namas1', '');
		
        return view('oreport_supd2.report')->with(['supp' => $supp])->with(['per' => $per])->with(['cbg' => $cbg])->with(['hasil' => []]);
    }
	

	 
	public function jasperSupd2Report(Request $request) 
	{
		$file 	= 'suppr';
		$PHPJasperXML = new PHPJasperXML();
		$PHPJasperXML->load_xml_file(base_path().('/app/reportc01/phpjasperxml/'.$file.'.jrxml'));
		

        if ($request->session()->has('periode')) 
		{
			$periode = $request->session()->get('periode')['bulan']. '/' . $request->session()->get('periode')['tahun'];
		} else
		{
			$periode = '';
		}
			
		if($request['cbg'])
		{
			$cbg = $request['cbg'];
		}

		if (!empty($request->cbg))
		{
			$filtercbg = " and supd.CBG='".$request->cbg."' ";
		}
		
		if (!empty($request->KODES))
		{
			$filtersupp = " and supd2.SUPP='".$request->SUPP."' ";
		}
		
		$bulan = substr($periode,0,2);
		$tahun = substr($periode,3,4);
		session()->put('filter_cbg', $request->cbg);
		session()->put('filter_kodes1', $request->SUPP);
		/*            
		$query = DB::SELECT("
			SELECT sup.KODES,sup.NAMAS,supd.AW$bulan as AW,supd.MA$bulan as MA, 
			supd.KE$bulan as KE,supd.LN$bulan as LN,supd.AK$bulan as AK 
			from sup,supd 
			WHERE sup.KODES=supd.KODES and supd.YER='$tahun';
		");
		*/        
		
		// $queryakum = DB::SELECT("SET @tglx:=last_day(concat('$tahun','-','$bulan','-01'));");
		// $query = DB::SELECT("
		// SELECT '$periode'as PERIOD, supd.KODES, supd.NAMAS, supd.NO_ID, 
		// supd.AW$bulan as AW, supd.MA$bulan as MA, 
		// supd.KE$bulan as KE, supd.LN$bulan as LN, supd.ak$bulan as AK,
		// coalesce(xxx.SATU,0) SATU, coalesce(xxx.DUA,0) DUA, coalesce(xxx.TIGA,0) TIGA,
		// coalesce(xxx.SATU,0)+coalesce(xxx.DUA,0)+coalesce(xxx.TIGA,0) as SALDO 
		// from sup,supd 
		// left join 
		// (
		//     SELECT KODES, sum(if(DATEDIFF(@tglx,TGL)<30,belix.PER$bulan-belix.PERB$bulan,0)) as SATU,
		//     sum(if(DATEDIFF(@tglx,TGL)BETWEEN 30 and 60,belix.PER$bulan-belix.PERB$bulan,0)) as DUA,
		//     sum(if(DATEDIFF(@tglx,TGL)>60,belix.PER$bulan-belix.PERB$bulan,0)) as TIGA 
		//     from belix 
		//     where belix.YER='$tahun' and belix.PER$bulan-belix.PERB$bulan<>0
		//     GROUP BY KODES
		// ) as xxx on supd.KODES=xxx.KODES
		// where sup.KODES = supd.KODES
		// $filterkodes
		// order by sup.KODES;
		// ");
		$query = DB::SELECT("SELECT supd2.SUPP, supd2.SUB, supd2.NOITEM, supd2.KD_BRG, supd2.KLK1,
									supd2.HARGA, supd2.PPN from supd2
									-- WHERE sup.KODES = supd2.SUPP
									ORDER BY supd2.SUPP
		");

		$per = Perid::query()->get();
		session()->put('filter_per', $periode);

		if($request->has('filter'))
		{
			$cbg = Cbg::groupBy('CBG')->get();
			return view('oreport_supd2.report')->with(['cbg' => $cbg])->with(['per' => $per])->with(['hasil' => $query]);
		}

		$data=[];
		foreach ($query as $key => $value)
		{
			array_push($data, array(
				'SUPP' => $query[$key]->SUPP,
				'NOITEM' => $query[$key]->NOITEM,
				'KD_BRG' => $query[$key]->KD_BRG,
				'KLK1' => $query[$key]->KLK1,
				'HARGA' => $query[$key]->HARGA,
				'PPN' => $query[$key]->PPN,
			));
		}
		$PHPJasperXML->setData($data);
		ob_end_clean();
		$PHPJasperXML->outpage("I");
	}
	
}
