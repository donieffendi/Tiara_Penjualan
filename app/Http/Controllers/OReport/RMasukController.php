<?php

namespace App\Http\Controllers\OReport;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\Master\Cbg;

use Illuminate\Http\Request;
use DataTables;
use Auth;
use DB;

include_once base_path()."/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";
use PHPJasperXML;

use \koolreport\laravel\Friendship;
use \koolreport\bootstrap4\Theme;

class RMasukController extends Controller
{

   public function report()
    {
		$cbg = Cbg::groupBy('CBG')->get();
		session()->put('filter_cbg', '');

		session()->put('filter_gol', '');
		session()->put('filter_kodes1', '');
		session()->put('filter_kodes2', '');
		session()->put('filter_tglDari', date("d-m-Y"));
		session()->put('filter_tglSampai', date("d-m-Y"));
		session()->put('filter_brg1', '');
		session()->put('filter_nabrg1', '');


        return view('oreport_masuk.report')->with(['cbg' => $cbg])->with(['hasil' => []]);
    }
	
	 
	public function jasperMasukReport(Request $request) 
	{
		$file 	= 'masukn';
		$PHPJasperXML = new PHPJasperXML();
		$PHPJasperXML->load_xml_file(base_path().('/app/reportc01/phpjasperxml/'.$file.'.jrxml'));
		
			// Check Filter
			if (!empty($request->gol))
			{
				$filtergol = " and masuk.GOL='".$request->gol."' ";
			}

			if (!empty($request->kodes) && !empty($request->kodes2))
			{
				$filterkodes = " and masuk.KODES between '".$kodes."' and '".$kodes2."' ";
			}
			
			if (!empty($request->tglDr) && !empty($request->tglSmp))
			{
				$tglDrD = date("Y-m-d", strtotime($request->tglDr));
				$tglSmpD = date("Y-m-d", strtotime($request->tglSmp));
				$filtertgl = " and masuk.TGL between '".$tglDrD."' and '".$tglSmpD."' ";
			}	

			if (!empty($request->brg1))
			{
				$filterbrg = " and masukd.KD_BRG='".$request->brg1."' ";
			}
			
			if($request['cbg'])
			{
				$cbg = $request['cbg'];
			}

			if (!empty($request->cbg))
			{
				$filtercbg = " and masuk.CBG='".$request->cbg."' ";
			}

			session()->put('filter_gol', $request->gol);
			session()->put('filter_kodes1', $request->kodes);
			session()->put('filter_kodes2', $request->kodes2);
			session()->put('filter_tglDari', $request->tglDr);
			session()->put('filter_tglSampai', $request->tglSmp);
			session()->put('filter_brg1', $request->brg1);
			session()->put('filter_nabrg1', $request->nabrg1);
			session()->put('filter_flag', $request->flag);
			session()->put('filter_cbg', $request->cbg);
		

		
			$query = DB::SELECT("SELECT trim(masuk.NO_BUKTI) as NO_BUKTI, DATE_FORMAT(masuk.TGL, '%d-%m-%Y') AS TGL, masuk.NO_PO, masuk.KODES, 
									masuk.NAMAS, masukd.KD_BRG, masukd.NA_BRG,
									masukd.QTY, masukd.HARGA, masukd.TOTAL, masuk.GOL, masukd.PPN, (masukd.TOTAL + masukd.PPN) AS NETT 
								from masuk,masukd 
								WHERE masuk.NO_BUKTI=masukd.NO_BUKTI 
								$filtertgl $filtergol $filterkodes $filtercbg
								/*order by masuk.KODES,masuk.NO_BUKTI*/;
			");
			

		if($request->has('filter'))
		{
			$cbg = Cbg::groupBy('CBG')->get();
			return view('oreport_masuk.report')->with(['cbg' => $cbg])->with(['hasil' => $query]);
		}

		$data=[];
		foreach ($query as $key => $value)
		{
			array_push($data, array(
				'NO_BUKTI' => $query[$key]->NO_BUKTI,
				'TGL' => $query[$key]->TGL,
				'NO_PO' => $query[$key]->NO_PO,
				'KODES' => $query[$key]->KODES,
				'NAMAS' => $query[$key]->NAMAS,
				'KD_BRG' => $query[$key]->KD_BRG,
                // 'KD_BRG'    => "`".strval($query[$key]->KD_BRG),
				'NA_BRG' => $query[$key]->NA_BRG,
				'KD_BHN' => $query[$key]->KD_BHN,
				'NA_BHN' => $query[$key]->NA_BHN,
				'KG' => $query[$key]->KG,
				'QTY' => $query[$key]->QTY,
				'HARGA' => $query[$key]->HARGA,
				'TOTAL' => $query[$key]->TOTAL,
				'PPN' => $query[$key]->PPN,
				'NETT' => $query[$key]->NETT,
				'NOTES' => $query[$key]->NOTES,

			));
		}
		$PHPJasperXML->setData($data);
		ob_end_clean();
		$PHPJasperXML->outpage("I");
	}
	
}