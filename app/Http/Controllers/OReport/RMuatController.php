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

class RMuatController extends Controller
{

   public function report()
    {
		session()->put('filter_gol', '');
		session()->put('filter_kodes1', '');
		session()->put('filter_namas1', '');
		session()->put('filter_tglDari', date("d-m-Y"));
		session()->put('filter_tglSampai', date("d-m-Y"));
		session()->put('filter_brg1', '');
		session()->put('filter_nabrg1', '');


        return view('oreport_muat.report')->with(['hasil' => []]);
    }
	
	 
	public function jasperMuatReport(Request $request) 
	{
		$file 	= 'muatn';
		$PHPJasperXML = new PHPJasperXML();
		$PHPJasperXML->load_xml_file(base_path().('/app/reportc01/phpjasperxml/'.$file.'.jrxml'));
		
			// Check Filter
			if (!empty($request->gol))
			{
				$filtergol = " and muat.GOL='".$request->gol."' ";
			}
			
			if (!empty($request->kodes))
			{
				$filterkodes = " and muat.KODES='".$request->kodes."' ";
			}
			
			if (!empty($request->tglDr) && !empty($request->tglSmp))
			{
				$tglDrD = date("Y-m-d", strtotime($request->tglDr));
				$tglSmpD = date("Y-m-d", strtotime($request->tglSmp));
				$filtertgl = " and muat.TGL between '".$tglDrD."' and '".$tglSmpD."' ";
			}	

			if (!empty($request->brg1))
			{
				$filterbrg = " and muat.KD_BRG='".$request->brg1."' ";
			}
			

			session()->put('filter_gol', $request->gol);
			session()->put('filter_kodes1', $request->kodes);
			session()->put('filter_namas1', $request->NAMAS);
			session()->put('filter_tglDari', $request->tglDr);
			session()->put('filter_tglSampai', $request->tglSmp);
			session()->put('filter_brg1', $request->brg1);
			session()->put('filter_nabrg1', $request->nabrg1);
			session()->put('filter_flag', $request->flag);
		

		if( $filtergol == 'J'){
			$query = DB::SELECT("SELECT muat.NO_BUKTI, DATE_FORMAT(NOW(),'%d/%m/%Y') AS TGL_CETAK, muat.TGL, muat.NO_BELI, muat.NO_PO, muat.KD_BRG, muat.NA_BRG, 
										muat.QTY_BELI,
										muatd.NO_CONT, muatd.SOPIR, muatd.NOPOL, muatd.TELP, muatd.SEAL, 
										muatd.T_CONT, muatd.QTY
								FROM muat, muatd 
								WHERE muat.NO_BUKTI=muatd.NO_BUKTI 
								$filtertgl $filtergol $filterkodes 
								/*order by muat.KODES,muat.NO_BUKTI*/;
							");
		
		} else {
			$query = DB::SELECT("SELECT muat.NO_BUKTI, DATE_FORMAT(NOW(),'%d/%m/%Y') AS TGL_CETAK, muat.TGL, muat.NO_BELI, muat.NO_PO, muat.KD_BRG, muat.NA_BRG, 
										muat.QTY_BELI,
										muatd.NO_CONT, muatd.SOPIR, muatd.NOPOL, muatd.TELP, muatd.SEAL, 
										muatd.T_CONT, muatd.QTY
								FROM muat, muatd 
								WHERE muat.NO_BUKTI=muatd.NO_BUKTI 
								$filtertgl $filtergol $filterkodes 
								/*order by muat.KODES,muat.NO_BUKTI*/;
							");
		}
			

		if($request->has('filter'))
		{
			return view('oreport_muat.report')->with(['hasil' => $query]);
		}

		$data=[];
		foreach ($query as $key => $value)
		{
			array_push($data, array(
				'NO_BUKTI' => $query[$key]->NO_BUKTI,
                'TGL'      => $query[$key]->TGL,
                'TGL_CETAK' => $query[$key]->TGL_CETAK,
                'NO_BELI'  => $query[$key]->NO_BELI,
                'NO_PO'    => $query[$key]->NO_PO,
                // 'KD_BRG'   => $query[$key]->KD_BRG,
                'KD_BRG'    => "`".strval($query[$key]->KD_BRG),
                'NA_BRG'   => $query[$key]->NA_BRG,
                'QTY_BELI' => $query[$key]->QTY_BELI,
                'NO_CONT'  => $query[$key]->NO_CONT,
                'SOPIR'    => $query[$key]->SOPIR,
                'NOPOL'    => $query[$key]->NOPOL,
                'TELP'     => $query[$key]->TELP,
                'SEAL'     => $query[$key]->SEAL,
                'T_CONT'   => $query[$key]->T_CONT,
                'QTY'      => $query[$key]->QTY,

			));
		}
		$PHPJasperXML->setData($data);
		ob_end_clean();
		$PHPJasperXML->outpage("I");
	}
	
}