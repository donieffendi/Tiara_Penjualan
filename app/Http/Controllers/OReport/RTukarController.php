<?php

namespace App\Http\Controllers\OReport;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Master\Cust;
use App\Models\Master\Cbg;
use DataTables;
use Auth;
use DB;

include_once base_path()."/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";
use PHPJasperXML;

use \koolreport\laravel\Friendship;
use \koolreport\bootstrap4\Theme;

class RTukarController extends Controller
{

  	public function report()
    {
		$cbg = Cbg::groupBy('CBG')->get();
		session()->put('filter_cbg', '');

		$kodec = Cust::orderBy('KODEC')->get();
		session()->put('filter_gol', '');
		session()->put('filter_kodec1', '');
		session()->put('filter_kodec2', '');
		session()->put('filter_namac1', '');
		session()->put('filter_tglDari', date("d-m-Y"));
		session()->put('filter_tglSampai', date("d-m-Y"));
		session()->put('filter_brg1', '');
		session()->put('filter_nabrg1', '');
		session()->put('filter_kdgd1', '');
	
        return view('oreport_tukar.report')->with(['kodec' => $kodec])->with(['cbg' => $cbg])->with(['hasil' => []]);
    }
	  

	public function jasperTukarReport(Request $request) 
	{
		$file 	= 'tukarn';
		$PHPJasperXML = new PHPJasperXML();
		$PHPJasperXML->load_xml_file(base_path().('/app/reportc01/phpjasperxml/'.$file.'.jrxml'));
		
			// Check Filter

			if($request['cbg'])
			{
				$cbg = $request['cbg'];
			}
			
			// if (!empty($request->kodec))
			// {
			// 	$filterkodec = " and so.KODEC='".$request->kodec."' ";
			// } 
		
			if (!empty($request->kodec) && !empty($request->kodec2))
			{
				$filterkodec = " WHERE a.KODEC between '".$kodec."' and '".$kodec2."' ";
			}
			
			if (!empty($request->tglDr) && !empty($request->tglSmp))
			{
				$tglDrD = date("Y-m-d", strtotime($request->tglDr));
				$tglSmpD = date("Y-m-d", strtotime($request->tglSmp));
				$filtertgl = " and a.TGL between '".$tglDrD."' and '".$tglSmpD."' ";
			}

			if (!empty($request->brg1))
			{
				$filterbrg = " and b.KD_BRG='".$request->brg1."' ";
			}

			if (!empty($request->kdgd1))
			{
				$filtergudang = " and a.GUDANG='".$request->kdgd1."' ";
			}
			
			if (!empty($request->cbg))
			{
				$filtercbg = " and a.CBG='".$request->cbg."' ";
			}
			
			
			$tgl_1 = date("Y-m-d", strtotime($request->tglDr));
			$tgl_2 = date("Y-m-d", strtotime($request->tglSmp));
			

			session()->put('filter_gol', $request->gol);
			session()->put('filter_kodec1', $request->kodec);
			session()->put('filter_kodec2', $request->kodec2);
			session()->put('filter_namac1', $request->NAMAC);
			session()->put('filter_tglDari', $request->tglDr);
			session()->put('filter_tglSampai', $request->tglSmp);
			session()->put('filter_brg1', $request->brg1);
			session()->put('filter_nabrg1', $request->nabrg1);
			session()->put('filter_kdgd1', $request->kdgd1);
			session()->put('filter_no_so1', $request->no_so1);
			session()->put('filter_cbg', $request->cbg);
			
		$query = DB::SELECT("
			
			SELECT a.NO_BUKTI, DATE_FORMAT(a.TGL, '%d-%m-%Y') AS TGL, a.KODEC, a.NAMAC, b.KD_BRG,b.NA_BRG,
					b.QTY, b.SATUAN
			from tukar a, tukard b 
			WHERE a.NO_BUKTI = b.NO_BUKTI 
			$filtertgl  $filterkodec $filtercbg 
			ORDER BY a.NO_BUKTI;

		");
      
		if($request->has('filter'))
		{
			$cbg = Cbg::groupBy('CBG')->get();

			return view('oreport_tukar.report')->with(['cbg' => $cbg])->with(['hasil' => $query]);
		}

		$data=[];
		foreach ($query as $key => $value)
		{
			array_push($data, array(
				'NO_BUKTI' => $query[$key]->NO_BUKTI,
				'TGL' => $query[$key]->TGL,
				'TGL_1' => $tgl_1,
				'TGL_2' => $tgl_2,
				'NO_SO' => $query[$key]->NO_SO,
				'KODEC' => $query[$key]->KODEC,
				'NAMAC' => $query[$key]->NAMAC,
				'TRUCK' => $query[$key]->TRUCK,
				'KG' => $query[$key]->KG,
				'QTY' => $query[$key]->QTY,
				'NAMAC' => $query[$key]->NAMAC,
				'KD_BRG' => $query[$key]->KD_BRG,
                // 'KD_BRG'    => "`".strval($query[$key]->KD_BRG),
				'NA_BRG' => $query[$key]->NA_BRG,
				'NA_BRG' => $query[$key]->NA_BRG,
				'GDG' => $query[$key]->GUDANG,
				'DPP' => $query[$key]->DPP,
				'PPN' => $query[$key]->PPN,
				'TOTAL' => $query[$key]->TOTAL,
				'HARGA' => $query[$key]->HARGA,
				'NOTES' => $query[$key]->NOTES,

			));
		}
		$PHPJasperXML->setData($data);
		ob_end_clean();
		$PHPJasperXML->outpage("I");
	}
	
}