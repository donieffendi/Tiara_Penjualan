<?php

namespace App\Http\Controllers\OReport;

use App\Http\Controllers\Controller;
use Carbon\Carbon;

use Illuminate\Http\Request;
use DataTables;
use Auth;
use DB;

include_once base_path()."/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";
use PHPJasperXML;

use \koolreport\laravel\Friendship;
use \koolreport\bootstrap4\Theme;

class RStokPerTahunController extends Controller
{
    public function index()
    {
		session()->put('filter_brg1', '');
		session()->put('filter_nabrg1', '');
		session()->put('filter_brg2', '');
		session()->put('filter_nabrg2', '');

        return view('oreport_stokpertahun.index')->with(['hasil' => []]);
    }
	
	public function jasperStokPerTahun(Request $request) 
	{
		$file 	= 'stokpertahun';
		$PHPJasperXML = new PHPJasperXML();
		$PHPJasperXML->load_xml_file(base_path().('/app/reportc01/phpjasperxml/'.$file.'.jrxml'));

            $periode = session()->get('periode')['bulan'].'/'.session()->get('periode')['tahun'];
            $bulan = session()->get('periode')['bulan'];
            $tahun = session()->get('periode')['tahun'];
			$filterbrg = " AND KD_BRG<>'' " ;
			if($request->brg1)
			{
				$filterbrg = " AND KD_BRG between '".$request->brg1."' and '".$request->brg2."' " ;
			}
		
			session()->put('filter_brg1', $request->brg1);
			session()->put('filter_nabrg1', $request->nabrg1);
			session()->put('filter_brg2', $request->brg2);
			session()->put('filter_nabrg2', $request->nabrg2);
		
		$queryakum = DB::SELECT("SET @akum:=0;");
		
		$unionall = " UNION ALL ";
		$orderby = " ORDER BY KD_BRG, BULAN";
		$stringquery = '';
		for($i=1 ; $i<=12 ; $i++)
		{
			$stringquery = $stringquery . "SELECT KD_BRG, NA_BRG, $i as BULAN,
			AW".str_pad($i, 2, 0, STR_PAD_LEFT)." as AWAL, MA".str_pad($i, 2, 0, STR_PAD_LEFT)." as MASUK, KE".str_pad($i, 2, 0, STR_PAD_LEFT)." as KELUAR, LN".str_pad($i, 2, 0, STR_PAD_LEFT)." as LAIN, AK".str_pad($i, 2, 0, STR_PAD_LEFT)." as AKHIR 
			from brgd WHERE YER='$tahun' $filterbrg";
			if ($i<12) {$stringquery = $stringquery.$unionall;}
		}
		$stringquery = $stringquery.$orderby;
		$query = DB::SELECT($stringquery);

		if($request->has('filter'))
		{
			return view('oreport_stokpertahun.index')->with(['hasil' => $query]);
		}

		$data=[];
		foreach ($query as $key => $value)
		{
			array_push($data, array(
				'KD_BRG' => $query[$key]->KD_BRG,
				'NA_BRG' => $query[$key]->NA_BRG,
				'BULAN' => $query[$key]->BULAN,
				'AWAL' => $query[$key]->AWAL,
				'MASUK' => $query[$key]->MASUK,
				'KELUAR' => $query[$key]->KELUAR,
				'LAIN' => $query[$key]->LAIN,
				'AKHIR' => $query[$key]->AKHIR,
			));
		}
		$PHPJasperXML->setData($data);
		ob_end_clean();
		$PHPJasperXML->outpage("I");
	}
	
}
