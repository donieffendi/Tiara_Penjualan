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

class RPiuPerTahunController extends Controller
{
    public function index()
    {
		session()->put('filter_kodec1', '');
		session()->put('filter_namac1', '');
		session()->put('filter_kodec2', '');
		session()->put('filter_namac2', '');
		session()->put('filter_rekap', '');

        return view('oreport_piupertahun.index')->with(['hasil' => []]);
    }
	
	public function jasperPiuPerTahun(Request $request) 
	{
		$file 	= 'piupertahun';
		$PHPJasperXML = new PHPJasperXML();
		$PHPJasperXML->load_xml_file(base_path().('/app/reportc01/phpjasperxml/'.$file.'.jrxml'));

            $periode = session()->get('periode')['bulan'].'/'.session()->get('periode')['tahun'];
            $bulan = session()->get('periode')['bulan'];
            $tahun = session()->get('periode')['tahun'];
			$filtercust = " WHERE KODEC<>'' " ;
			if($request->kodec1)
			{
				$filtercust = " WHERE KODEC between '".$request->kodec1."' and '".$request->kodec2."' " ;
			}
		
			session()->put('filter_kodec1', $request->kodec1);
			session()->put('filter_namac1', $request->namac1);
			session()->put('filter_kodec2', $request->kodec2);
			session()->put('filter_namac2', $request->namac2);
			session()->put('filter_rekap', $request->rekap);
		
		$queryakum = DB::SELECT("SET @akum:=0;");
		
		$unionall = " UNION ALL ";
		$orderby = " ORDER BY KODEC, BULAN";
		$stringquery = '';
		for($i=1 ; $i<=12 ; $i++)
		{
			$stringquery = $stringquery . "SELECT KODEC, NAMAC, $i as BULAN,
			coalesce((SELECT sum(TOTAL) from jual WHERE KODEC=cust.KODEC and month(TGL)=$i and YEAR(TGL)='$tahun'),0) as TOTAL,
			coalesce((SELECT sum(BAYAR) from jual WHERE KODEC=cust.KODEC and month(TGL)=$i and YEAR(TGL)='$tahun'),0) as BAYAR,
			coalesce((SELECT sum(SISA) from jual WHERE KODEC=cust.KODEC and month(TGL)=$i and YEAR(TGL)='$tahun'),0) as SISA
			from cust $filtercust";
			if ($i<12) {$stringquery = $stringquery.$unionall;}
		}
		$stringquery = $stringquery.$orderby;
		if ($request->rekap==1)
		{
			$rangeCust = $request->namac1 . " - " . $request->namac2;
			if ($request->namac1=='' && $request->namac2=='') $rangeCust = 'Semua Customer';
			
			$stringquery = "SELECT 'Global $tahun' as KODEC, '$rangeCust' as NAMAC, BULAN, sum(TOTAL) as TOTAL, sum(BAYAR) as BAYAR, sum(SISA) as SISA from ($stringquery) as rekap 
			GROUP BY BULAN";
		}
		$query = DB::SELECT($stringquery);

		if($request->has('filter'))
		{
			return view('oreport_piupertahun.index')->with(['hasil' => $query]);
		}

		$data=[];
		foreach ($query as $key => $value)
		{
			array_push($data, array(
				'KODEC' => $query[$key]->KODEC,
				'NAMAC' => $query[$key]->NAMAC,
				'BULAN' => $query[$key]->BULAN,
				'TOTAL' => $query[$key]->TOTAL,
				'BAYAR' => $query[$key]->BAYAR,
				'SISA' => $query[$key]->SISA,
			));
		}
		$PHPJasperXML->setData($data);
		ob_end_clean();
		$PHPJasperXML->outpage("I");
	}
	
}
