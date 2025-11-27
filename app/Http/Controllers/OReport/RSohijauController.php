<?php

namespace App\Http\Controllers\OReport;

use App\Http\Controllers\Controller;
use App\Models\Master\Cbg;
use App\Models\Master\Brg;
use App\Models\Master\Perid;
use App\Services\GenerateBkt;


use Carbon\Carbon;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use DB;

include_once base_path()."/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";
use PHPJasperXML;

use \koolreport\laravel\Friendship;
use \koolreport\bootstrap4\Theme;

class RSohijauController extends Controller
{
	
   public function report()
    {
		// $cbg = Cbg::groupBy('CBG')->get();
		// session()->put('filter_cbg', '');

		$cbg = Auth::user()->CBG;

		$kd_brg = Brg::query()->get();
		$per = Perid::query()->get();
		session()->put('filter_per', '');

		session()->put('filter_kode1', '');
		session()->put('filter_kode2', '');
		session()->put('filter_nama1', '');
		session()->put('filter_nama2', '');
		session()->put('filter_supp', '');

        return view('oreport_sohijau.report')->with(['kd_brg' => $kd_brg])->with(['per' => $per])->with(['cbg' => $cbg])->with(['hasil' => []]);
    }
	
   
	public function jasperSohijauReport(Request $request) 
	{
		$cbg = Auth::user()->CBG;
		$file 	= 'sohijau';
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

			$filtercbg = " and brgd.CBG='".$cbg."' ";

		// if (!empty($request->KD_BRG))
		// {
		// 	$filterkode = " and vbrg.KD_BRG='".$request->KD_BRG."' ";
		// }
		// if (!empty($request->SUPP))
		// {
		// 	$filtersupp = " and vbrg.SUPP='".$request->SUPP."' ";
		// }
		
		
		// session()->put('filter_cbg', $request->cbg);
		session()->put('filter_per', $periode);
		session()->put('filter_kode1', $request->KD_BRG);
		session()->put('filter_kode2', $request->KD_BRG2);
		session()->put('filter_nama1', $request->NA_BRG);
		session()->put('filter_nama2', $request->NA_BRG2);
		session()->put('filter_supp', $request->SUPP);

;

		$bulan = substr($periode,0,2);
		$tahun = substr($periode,3,4);

		$brg  = $request->KD_BRG;
$sup  = $request->SUPP;
$gol = $request->GOL;
$kel = $request->KELOMPOK;
$tanda = $request->TANDA;
$where = [];
$bindings = []; 

// Add conditions to the WHERE clause if filters are provided
// if (!empty($brg)) {
//     $where[] = "vbrg.KD_BRG = ?";
//     $bindings[] = $brg;
// }
$filterkodes='';
if (!empty($request->KD_BRG) && !empty($request->KD_BRG2))
			{
				$KD_BRG = $request->KD_BRG;
				$KD_BRG2 = $request->KD_BRG2;
				$filterkodes = " AND brg.KD_BRG between '".$KD_BRG."' and '".$KD_BRG2."' ";
			}

if (!empty($sup)) {
    $where[] = "brg.KODES = ?";
    $bindings[] = $sup;
}

// if (!empty($gol)) {
//     $where[] = "brg.GOL = ?";
//     $bindings[] = $gol;
// }

// if (!empty($kel)) {
//     $where[] = "brg.KELOMPOK = ?";
//     $bindings[] = $kel;
// }

// if (!empty($tanda)) {
//     $where[] = "brg.TANDA = ?";
//     $bindings[] = $tanda;
// }

if (!empty($tahun)) {
    $where[] = "brgd.YER = ?";
    $bindings[] = $tahun;
}

// if (!empty($tanda)) {
//     $where[] = "brg.TANDA = ?";
//     $bindings[] = $tanda;
// }

// Combine WHERE conditions
$whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

$queryakum = DB::SELECT("SET @akum := 0;");
$sql = "
    SELECT 
        brg.KD_BRG, brg.NA_BRG, brg.KODES, brg.NAMAS,
        brgd.AW$bulan AS AW, brgd.MA$bulan AS MA, brgd.KE$bulan AS KE,
        brgd.LN$bulan AS LN, brgd.AK$bulan AS AK, brgd.HRT$bulan AS HRT,
        brgd.NIW$bulan AS NIW, brgd.NIM$bulan AS NIM, brgd.NIK$bulan AS NIK,
        brgd.NIL$bulan AS NIL, brgd.NIR$bulan AS NIR
    FROM 
        brg
    INNER JOIN 
        brgd ON brg.KD_BRG = brgd.KD_BRG
    
        $whereClause $filterkodes 
    GROUP BY 
        brg.KD_BRG
    ORDER BY 
        brg.KD_BRG;
";

// Execute the query with bindings
$query = DB::select($sql, $bindings);

// Run the query with the bound parameters
$query = DB::select($sql, $bindings);


		if($request->has('filter'))
		{
			$per = Perid::query()->get();
			// $cbg = Cbg::groupBy('CBG')->get();

			return view('oreport_sohijau.report')->with(['per' => $per])->with(['cbg' => $cbg])->with(['hasil' => $query]);
		}

		$data=[];
		foreach ($query as $key => $value)
		{
			array_push($data, array(
				'KD_BRG' => $query[$key]->KD_BRG,
				'NA_BRG' => $query[$key]->NA_BRG,
				'NAMAS' => $query[$key]->NAMAS,
				'KLK' => $query[$key]->KLK,
				'PPN' => $query[$key]->PPN,
				'GOL' => $query[$key]->GOL,
				'KELOMPOK' => $query[$key]->KELOMPOK,
				'TANDA' => $query[$key]->TANDA,
				// 'LN' => $query[$key]->LN,
				// 'AK' => $query[$key]->AK,
				// 'HRT' => $query[$key]->HRT,
				// 'HRT_2' => $query[$key]->HRT_2,
				// 'NIW' => $query[$key]->NIW,
				// 'NIM' => $query[$key]->NIM,
				// 'NIK' => $query[$key]->NIK,
				// 'NIL' => $query[$key]->NIL,
				// 'NIR' => $query[$key]->NIR,
			));
		}
		$PHPJasperXML->setData($data);
		$PHPJasperXML->arrayParameter = [
			'NO_BUKTI' => GenerateBkt::get('SO'.$cbg)
		];
		ob_end_clean();
		$PHPJasperXML->outpage("I");
	}
	
}
