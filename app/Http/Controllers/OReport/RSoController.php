<?php

namespace App\Http\Controllers\OReport;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Master\Cust;
use DataTables;
use Auth;
use DB;

include_once base_path()."/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";
use PHPJasperXML;

use \koolreport\laravel\Friendship;
use \koolreport\bootstrap4\Theme;

class RSoController extends Controller
{

	public function report()
    {
		$kodec = Cust::query()->get();
		session()->put('filter_gol', '');
		session()->put('filter_kodec1', '');
		session()->put('filter_namac1', '');
		session()->put('filter_kodet1', '');
		session()->put('filter_namat1', '');
		session()->put('filter_tglDari', date("d-m-Y"));
		session()->put('filter_tglSampai', date("d-m-Y"));
		session()->put('filter_sls', '');
		session()->put('filter_brg1', '');
		session()->put('filter_nabrg1', '');

        return view('oreport_so.report')->with(['kodec' => $kodec])->with(['hasil' => []]);
    }
	
	

	public function jasperSoReport(Request $request) 
	{
		$file 	= 'son';
		$PHPJasperXML = new PHPJasperXML();
		$PHPJasperXML->load_xml_file(base_path().('/app/reportc01/phpjasperxml/'.$file.'.jrxml'));
		
			// Check Filter
			if (!empty($request->gol))
			{
				$filtergol = " and so.GOL='".$request->gol."' ";
			}
			
			if (!empty($request->kodec))
			{
				$filterkodec = " and so.KODEC='".$request->kodec."' ";
			}
			
			if (!empty($request->kodet))
			{
				$filterkodet = " and KODET='".$request->kodet."' ";
			}
			
			if (!empty($request->tglDr) && !empty($request->tglSmp))
			{
				$tglDrD = date("Y-m-d", strtotime($request->tglDr));
				$tglSmpD = date("Y-m-d", strtotime($request->tglSmp));
				$filtertgl = " AND so.TGL between '".$tglDrD."' and '".$tglSmpD."' ";
			}
			
			if (!empty($request->sls))
			{
				$sls = $request->sls=='Y' ? '1' : '0';
				$filtersls = " and so.SLS='".$sls."' ";
			}
			
			if (!empty($request->brg1))
			{
				$filterbrg = " and sod.KD_BRG='".$request->brg1."' ";
			}

			session()->put('filter_gol', $request->gol);
			session()->put('filter_kodec1', $request->kodec);
			session()->put('filter_namac1', $request->NAMAC);
			session()->put('filter_tglDari', $request->tglDr);
			session()->put('filter_tglSampai', $request->tglSmp);
			session()->put('filter_sls', $request->sls);
			session()->put('filter_brg1', $request->brg1);
			session()->put('filter_nabrg1', $request->nabrg1);
		
		if($filtergol == 'B'){
				$query = DB::SELECT("SELECT so.NO_BUKTI AS NO_BUKTI, so.TGL AS TGL, so.KODEC AS KODEC, so.NAMAC AS NAMAC, sod.KD_BHN AS KD_BRG, sod.NA_BHN AS NA_BRG, 
								sod.QTY AS QTY, sod.HARGA AS HARGA, sod.TOTAL AS TOTAL, sod.KET AS KET, so.GOL AS GOL 
						from so,sod
						WHERE so.NO_BUKTI=sod.NO_BUKTI
						$filtertgl $filtergol $filterkodec 
						/*order by so.KODEC,so.NO_BUKTI*/;
				"); 

		} else {

			$query = DB::SELECT("SELECT so.NO_BUKTI AS NO_BUKTI, so.TGL AS TGL, so.KODEC AS KODEC, so.NAMAC AS NAMAC, sod.KD_BRG AS KD_BRG, sod.NA_BRG AS NA_BRG, 
								sod.QTY AS QTY, sod.HARGA AS HARGA, sod.TOTAL AS TOTAL, sod.KET AS KET, so.GOL AS GOL  
						from so,sod
						WHERE so.NO_BUKTI=sod.NO_BUKTI
						$filtertgl $filtergol $filterkodec 
						/*order by so.KODEC,so.NO_BUKTI*/;
				");
		}
		
		
		if($request->has('filter'))
		{
			return view('oreport_so.report')->with(['hasil' => $query]);
		}

		$data=[];
		foreach ($query as $key => $value)
		{
			array_push($data, array(
				'NO_SO' => $query[$key]->NO_SO,
				'TGL' => $query[$key]->TGL,
				'KODEC' => $query[$key]->KODEC,
				'NAMAC' => $query[$key]->NAMAC,
				'KD_BRG' => $query[$key]->KD_BRG,
				'NA_BRG' => $query[$key]->NA_BRG,
				'KG' => $query[$key]->KG,
				'HARGA' => $query[$key]->HARGA,
				'TOTAL' => $query[$key]->TOTAL,
				'NOTES' => $query[$key]->NOTES,
				'KIRIM' => $query[$key]->KIRIM,
				'SISA' => $query[$key]->SISA,
			));
		}
		$PHPJasperXML->setData($data);
		ob_end_clean();
		$PHPJasperXML->outpage("I");
	}
	
}
