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

class RCustController extends Controller
{
   public function report()
    {
		$cbg = Cbg::groupBy('CBG')->get();
		session()->put('filter_cbg', '');

		$kodec = Cust::query()->get();
		$per = Perid::query()->get();
		session()->put('filter_per', '');
		
        return view('oreport_cust.report')->with(['kodec' => $kodec])->with(['per' => $per])->with(['cbg' => $cbg])->with(['hasil' => []]);
    }
	
	
	 
	public function jasperCustReport(Request $request) 
	{
		$file 	= 'custpr';
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
		/*     
		$query = DB::SELECT("
			SELECT cust.KODEC,cust.NAMAC,custd.AW$bulan as AW,custd.MA$bulan as MA, 
			custd.KE$bulan as KE,custd.LN$bulan as LN,custd.AK$bulan as AK 
			from cust,custd 
			WHERE cust.KODEC=custd.KODEC and custd.YER='$tahun';
		");
		*/        
		
		$queryakum = DB::SELECT("SET @tglx:=last_day(concat('$tahun','-','$bulan','-01'));");
		$query = DB::SELECT("
		SELECT '$periode'as PERIOD, custd.KODEC, custd.NAMAC, custd.NO_ID, 
		custd.AW$bulan as AW, custd.MA$bulan as MA, 
		custd.KE$bulan as KE, custd.LN$bulan as LN, custd.ak$bulan as AK,
		coalesce(xxx.SATU,0) SATU, coalesce(xxx.DUA,0) DUA, coalesce(xxx.TIGA,0) TIGA,
		coalesce(xxx.SATU,0)+coalesce(xxx.DUA,0)+coalesce(xxx.TIGA,0) as SALDO 
		from cust,custd 
		left join 
		(
		    SELECT KODEC, sum(if(DATEDIFF(@tglx,TGL)<30,jualx.PER$bulan-jualx.PERB$bulan,0)) as SATU,
		    sum(if(DATEDIFF(@tglx,TGL)BETWEEN 30 and 60,jualx.PER$bulan-jualx.PERB$bulan,0)) as DUA,
		    sum(if(DATEDIFF(@tglx,TGL)>60,jualx.PER$bulan-jualx.PERB$bulan,0)) as TIGA 
		    from jualx 
		    where jualx.YER='$tahun' and jualx.PER$bulan-jualx.PERB$bulan<>0
		    GROUP BY KODEC
		) as xxx on custd.KODEC=xxx.KODEC
		where cust.KODEC = custd.KODEC #and cust.KODEC >=@KODEC1 and cust.KODEC<=@KODEC2 
		and custd.YER='$tahun' and ( custd.AW$bulan<>0 or custd.MA$bulan<>0 or custd.KE$bulan<>0 
		or custd.LN$bulan<>0 or custd.AK$bulan<>0 ) 
		order by cust.KODEC;
		");

		session()->put('filter_per', $periode);

		if($request->has('filter'))
		{
			$per = Perid::query()->get();
			$cbg = Cbg::groupBy('CBG')->get();
			return view('oreport_cust.report')->with(['per' => $per])->with(['cbg' => $cbg])->with(['hasil' => $query]);
		}

		$data=[];
		foreach ($query as $key => $value)
		{
			array_push($data, array(
				'KODEC' => $query[$key]->KODEC,
				'NAMAC' => $query[$key]->NAMAC,
				'AW' => $query[$key]->AW,
				'MA' => $query[$key]->MA,
				'KE' => $query[$key]->KE,
				'LN' => $query[$key]->LN,
				'AK' => $query[$key]->AK,
				'SATU' => $query[$key]->SATU,
				'DUA' => $query[$key]->DUA,
				'TIGA' => $query[$key]->TIGA,
				'SALDO' => $query[$key]->SALDO,
			));
		}
		$PHPJasperXML->setData($data);
		ob_end_clean();
		$PHPJasperXML->outpage("I");
	}

}
