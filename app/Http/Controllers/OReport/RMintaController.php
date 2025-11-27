<?php

namespace App\Http\Controllers\OReport;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Master\Sup;
use DataTables;
use Auth;
use DB;

include_once base_path()."/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";
use PHPJasperXML;

use \koolreport\laravel\Friendship;
use \koolreport\bootstrap4\Theme;

class RMintaController extends Controller
{

  	public function report()
    {
		$kodes = Sup::orderBy('KODES')->get();
		session()->put('filter_gol', '');
		session()->put('filter_kodes1', '');
		session()->put('filter_namas1', '');
		session()->put('filter_tglDari', date("d-m-Y"));
		session()->put('filter_tglSampai', date("d-m-Y"));
		session()->put('filter_posted', '');
		session()->put('filter_brg1', '');
		session()->put('filter_nabrg1', '');
		session()->put('filter_flag', 'SJ');

        return view('oreport_minta.report')->with(['kodes' => $kodes])->with(['hasil' => []]);
    }
	
	

	public function jasperMintaReport(Request $request) 
	{
		$file 	= 'mintan';
		$PHPJasperXML = new PHPJasperXML();
		$PHPJasperXML->load_xml_file(base_path().('/app/reportc01/phpjasperxml/'.$file.'.jrxml'));
		
			// Check Filter
			if (!empty($request->gol))
			{
				$filtergol = " and GOL='".$request->gol."' ";
			}
			
			if (!empty($request->kodes))
			{
				$filterkodes = " and KODES='".$request->kodes."' ";
			}
			

			if (!empty($request->tglDr) && !empty($request->tglSmp))
			{
				$tglDrD = date("Y-m-d", strtotime($request->tglDr));
				$tglSmpD = date("Y-m-d", strtotime($request->tglSmp));
				$filtertgl = " AND TGL between '".$tglDrD."' and '".$tglSmpD."' ";
			}
			
			if (!empty($request->posted))
			{
				$tandaposted = $request->posted=='Y' ? 1 : 0;
				$filterposted = " and POSTED='".$tandaposted."' ";
			}
			
			if (!empty($request->brg1))
			{
				$filterbrg = " and KD_BRG='".$request->brg1."' ";
			}
			
			if (!empty($request->flag))
			{
				$filterflag = " and FLAG='".$request->flag."' ";
			}
			
			session()->put('filter_gol', $request->gol);
			session()->put('filter_kodes1', $request->kodes);
			session()->put('filter_namas1', $request->namas);
			session()->put('filter_tglDari', $request->tglDr);
			session()->put('filter_tglSampai', $request->tglSmp);
			session()->put('filter_posted', $request->posted);
			session()->put('filter_brg1', $request->brg1);
			session()->put('filter_nabrg1', $request->nabrg1);
			session()->put('filter_flag', $request->flag);
			
		$query = DB::SELECT("SELECT minta.NO_BUKTI, DATE_FORMAT(minta.TGL, '%d-%m-%Y') AS TGL, mintad.KD_BRG, mintad.NA_BRG, 
									mintad.HARGA, mintad.QTY, mintad.QTY_KIRIM
							FROM minta, mintad 
						WHERE minta.NO_BUKTI=mintad.NO_BUKTI 
						$filtertgl $filtergol $filterkodec 
						/*order by terima.KODEC,terima.NO_BUKTI*/;
		");
		
		if($request->has('filter'))
		{
			return view('oreport_minta.report')->with(['hasil' => $query]);
		}
        
		$data=[];
		foreach ($query as $key => $value)
		{
			array_push($data, array(
				'NO_BUKTI' => $query[$key]->NO_BUKTI,
                'TGL'      => $query[$key]->TGL,
                'NO_MUAT'  => $query[$key]->NO_MUAT,
                'NO_BELI'  => $query[$key]->NO_BELI,
                'NO_PO'    => $query[$key]->NO_PO,
                'KD_BRG'   => $query[$key]->KD_BRG,
                'NA_BRG'   => $query[$key]->NA_BRG,
                'QTY_BELI' => $query[$key]->QTY_BELI,
                'NO_CONT'  => $query[$key]->NO_CONT,
                'SOPIR'    => $query[$key]->SOPIR,
                'NOPOL'    => $query[$key]->NOPOL,
                'TELP'     => $query[$key]->TELP,
                'SEAL'     => $query[$key]->SEAL,
                'T_CONT'   => $query[$key]->T_CONT,
                'QTYA'      => $query[$key]->QTYA,
                'BENDELA'      => $query[$key]->BENDELA,
                'IKATA'      => $query[$key]->IKATA,
                'QTYB'      => $query[$key]->QTYB,
                'BENDELB'      => $query[$key]->BENDELB,
                'IKATB'      => $query[$key]->IKATB,
			));
		}
		$PHPJasperXML->setData($data);
		ob_end_clean();
		$PHPJasperXML->outpage("I");
	}
	
}