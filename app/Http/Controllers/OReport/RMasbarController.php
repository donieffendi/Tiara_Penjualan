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

class RMasbarController extends Controller
{
	
   public function report()
    {
		$cbg = Cbg::groupBy('CBG')->get();
		session()->put('filter_cbg', '');

		$kd_brg = Vbrg::query()->get();
		$per = Perid::query()->get();
		session()->put('filter_per', '');

		session()->put('filter_kode1', '');
		session()->put('filter_kode2', '');
		session()->put('filter_nama1', '');
		session()->put('filter_nama2', '');
		session()->put('filter_supp', '');

        return view('oreport_masbar.report')->with(['kd_brg' => $kd_brg])->with(['per' => $per])->with(['cbg' => $cbg])->with(['hasil' => []]);
    }
	
   
	public function jasperMasbarReport(Request $request) 
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

		// if (!empty($request->KD_BRG))
		// {
		// 	$filterkode = " and vbrg.KD_BRG='".$request->KD_BRG."' ";
		// }
		// if (!empty($request->SUPP))
		// {
		// 	$filtersupp = " and vbrg.SUPP='".$request->SUPP."' ";
		// }
		
		
		session()->put('filter_cbg', $request->cbg);
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
				$filterkodes = " AND vbrg.KD_BRG between '".$KD_BRG."' and '".$KD_BRG2."' ";
			}

if (!empty($sup)) {
    $where[] = "vbrg.SUPP = ?";
    $bindings[] = $sup;
}

if (!empty($gol)) {
    $where[] = "vbrg.GOL = ?";
    $bindings[] = $gol;
}

if (!empty($kel)) {
    $where[] = "vbrg.KELOMPOK = ?";
    $bindings[] = $kel;
}

if (!empty($tanda)) {
    $where[] = "vbrg.TANDA = ?";
    $bindings[] = $tanda;
}

if (!empty($tahun)) {
    $where[] = "vbrgd.YER = ?";
    $bindings[] = $tahun;
}

if (!empty($tanda)) {
    $where[] = "vbrg.TANDA = ?";
    $bindings[] = $tanda;
}

// Combine WHERE conditions
$whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

$queryakum = DB::SELECT("SET @akum := 0;");
$sql = "
    SELECT 
        vbrg.KD_BRG, vbrg.NA_BRG, vbrg.SUPP, vbrg.KLK, vbrg.PPN, vbrg.TANDA, vbrg.GOL, vbrg.KELOMPOK,
        vbrgd.AW$bulan AS AW, vbrgd.MA$bulan AS MA, vbrgd.KE$bulan AS KE,
        vbrgd.LN$bulan AS LN, vbrgd.AK$bulan AS AK, vbrgd.HRT$bulan AS HRT,
        vbrgd.NIW$bulan AS NIW, vbrgd.NIM$bulan AS NIM, vbrgd.NIK$bulan AS NIK,
        vbrgd.NIL$bulan AS NIL, vbrgd.NIR$bulan AS NIR
    FROM 
        vbrg
    INNER JOIN 
        vbrgd ON vbrg.KD_BRG = vbrgd.KD_BRG
    
        $whereClause $filterkodes 
    GROUP BY 
        vbrg.KD_BRG
    ORDER BY 
        vbrg.KD_BRG;
";

// Execute the query with bindings
$query = DB::select($sql, $bindings);

// Run the query with the bound parameters
$query = DB::select($sql, $bindings);


		if($request->has('filter'))
		{
			$per = Perid::query()->get();
			$cbg = Cbg::groupBy('CBG')->get();

			return view('oreport_masbar.report')->with(['per' => $per])->with(['cbg' => $cbg])->with(['hasil' => $query]);
		}

		$data=[];
		foreach ($query as $key => $value)
		{
			array_push($data, array(
				'KD_BRG' => $query[$key]->KD_BRG,
                // 'KD_BRG'    => "`".strval($query[$key]->KD_BRG),
				'NA_BRG' => $query[$key]->NA_BRG,
				'SUPP' => $query[$key]->SUPP,
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
		ob_end_clean();
		$PHPJasperXML->outpage("I");
	}
	
}
