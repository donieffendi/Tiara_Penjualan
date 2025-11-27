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

class RRekapBeliJualController extends Controller
{
    public function index()
    {
		session()->put('filter_brg1', '');
		session()->put('filter_nabrg1', '');
		session()->put('filter_brg2', '');
		session()->put('filter_nabrg2', '');

        return view('oreport_rekapbelijual.index')->with(['hasil' => []]);
    }
	
	public function jasperRekapBeliJual(Request $request) 
	{
		$file 	= 'rekapbelijual';
		$PHPJasperXML = new PHPJasperXML();
		$PHPJasperXML->load_xml_file(base_path().('/app/reportc01/phpjasperxml/'.$file.'.jrxml'));

            $periode = session()->get('periode')['bulan'].'/'.session()->get('periode')['tahun'];
            $bulan = session()->get('periode')['bulan'];
            $tahun = session()->get('periode')['tahun'];
			$filterbrg = " WHERE KD_BRG<>'' " ;
			if($request->brg1)
			{
				$filterbrg = " WHERE KD_BRG between '".$request->brg1."' and '".$request->brg2."' " ;
			}
		
			session()->put('filter_brg1', $request->brg1);
			session()->put('filter_nabrg1', $request->nabrg1);
			session()->put('filter_brg2', $request->brg2);
			session()->put('filter_nabrg2', $request->nabrg2);
		
		$flagbeli = "'BL','BD','BN'";
		$flagjual = "'JL'";
		$queryakum = DB::SELECT("SET @akum:=0;");
		
		$unionall = " UNION ALL ";
		$orderby = " ORDER BY KD_BRG, BULAN";
		$stringquery = '';
		for($i=1 ; $i<=12 ; $i++)
		{
			$stringquery = $stringquery . "SELECT KD_BRG, NA_BRG, $i as BULAN,
			coalesce((SELECT sum(KG) from beli WHERE KD_BRG=brg.KD_BRG and FLAG in ($flagbeli) and month(TGL)=$i and YEAR(TGL)='$tahun'),0) as QTY_BELI, 
			coalesce((SELECT sum(KG) from beli WHERE KD_BRG=brg.KD_BRG and FLAG ='BL' and month(TGL)=$i and YEAR(TGL)='$tahun'),0) as QTY_BELI_Y, 
			coalesce((SELECT sum(KG3) from jual WHERE KD_BRG=brg.KD_BRG and FLAG in ($flagjual) and month(TGL)=$i and YEAR(TGL)='$tahun'),0) as QTY_JUAL,
			coalesce((SELECT sum(TOTAL) from beli WHERE KD_BRG=brg.KD_BRG and FLAG in ($flagbeli) and month(TGL)=$i and YEAR(TGL)='$tahun'),0) as BELI, 
			coalesce((SELECT sum(TOTAL1) from beli WHERE KD_BRG=brg.KD_BRG and FLAG ='BL' and month(TGL)=$i and YEAR(TGL)='$tahun'),0) as BELI_Y,
			coalesce((SELECT sum(TOTAL1)/SUM(KG) from beli WHERE KD_BRG=brg.KD_BRG and FLAG ='BL' and month(TGL)=$i and YEAR(TGL)='$tahun'),0) as AVG_BELI_Y,
			coalesce((SELECT sum(TOTAL1)/SUM(KG) from beli WHERE KD_BRG=brg.KD_BRG and FLAG ='BL' and  YEAR(TGL)='$tahun'),0) as AVG_BELI_Y1,
			(coalesce((SELECT sum(TOTAL1)/SUM(KG) from beli WHERE KD_BRG=brg.KD_BRG and FLAG ='BL' and  YEAR(TGL)='$tahun'),0) + coalesce((SELECT sum(TOTAL1)/SUM(KG) from beli WHERE KD_BRG=brg.KD_BRG and FLAG ='BL' and  YEAR(TGL)=($tahun-1)),0)) / if((SELECT COUNT(*) from beli WHERE KD_BRG=brg.KD_BRG and FLAG='BL' and YEAR(TGL)='$tahun')>0 and (SELECT COUNT(*) from beli WHERE KD_BRG=brg.KD_BRG and FLAG='BL' and YEAR(TGL)=($tahun-1))>0, 2, 1) as AVG_BELI_ALL,
			coalesce((SELECT sum(TOTAL) from jual WHERE KD_BRG=brg.KD_BRG and FLAG in ($flagjual) and month(TGL)=$i and YEAR(TGL)='$tahun'),0) as JUAL,
			coalesce((SELECT sum(TOTAL) from jual WHERE KD_BRG=brg.KD_BRG and FLAG in ($flagjual) and month(TGL)=$i and YEAR(TGL)='$tahun'),0)-coalesce((SELECT sum(TOTAL) from beli WHERE KD_BRG=brg.KD_BRG and FLAG in ($flagbeli) and month(TGL)=$i and YEAR(TGL)='$tahun'),0) as TOTAL
			from brg $filterbrg";
			if ($i<12) {$stringquery = $stringquery.$unionall;}
		}
		$stringquery = $stringquery.$orderby;
		$query = DB::SELECT($stringquery);

		if($request->has('filter'))
		{
			return view('oreport_rekapbelijual.index')->with(['hasil' => $query]);
		}

		$data=[];
		foreach ($query as $key => $value)
		{
			array_push($data, array(
				'KD_BRG' => $query[$key]->KD_BRG,
				'NA_BRG' => $query[$key]->NA_BRG,
				'SISAPO' => $query[$key]->SISAPO,
				'SISASO' => $query[$key]->SISASO,
				'BUTUH' => $query[$key]->BUTUH,
				'HRT12' => $query[$key]->HRT12,
			));
		}
		$PHPJasperXML->setData($data);
		ob_end_clean();
		$PHPJasperXML->outpage("I");
	}
	
}
