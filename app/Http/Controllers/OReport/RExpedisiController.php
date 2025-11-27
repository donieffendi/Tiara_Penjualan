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

class RExpedisiController extends Controller
{
    public function index()
    {
		session()->put('filter_tglDari', date("d-m-Y"));
		session()->put('filter_tglSampai', date("d-m-Y"));
		session()->put('filter_kodes1', '');
		session()->put('filter_namas1', '');
		session()->put('filter_kodes2', '');
		session()->put('filter_namas2', '');
		session()->put('filter_milik', '');
		session()->put('filter_bayar', '');

        return view('oreport_expedisi.index')->with(['hasil' => []]);
    }
	
	public function jasperExpedisi(Request $request) 
	{
		$file 	= 'expedisi';
		$PHPJasperXML = new PHPJasperXML();
		$PHPJasperXML->load_xml_file(base_path().('/app/reportc01/phpjasperxml/'.$file.'.jrxml'));

			$tglDrD = date("Y-m-d", strtotime($request['tglDr']));
			$tglSmpD = date("Y-m-d", strtotime($request['tglSmp']));
			$filtersup = " " ;
			if($request->kodes1)
			{
				$filtersup = " AND KODES between '".$request->kodes1."' and '".$request->kodes2."' " ;
			}

			$filtermilik = " " ;
			if($request->milik)
			{
				$filtermilik = " AND TRUCK in (SELECT NOPOL from truck WHERE MILIK='".$request->milik."') " ;
			}

			$filterbayar = " " ;
			if($request->bayar)
			{
				$filterbayar = $request->bayar=='Y' ? " AND BAYAR<>0 " : " AND BAYAR=0 ";
			}
		
			session()->put('filter_tglDari', $request->tglDr);
			session()->put('filter_tglSampai', $request->tglSmp);
			session()->put('filter_kodes1', $request->kodes1);
			session()->put('filter_namas1', $request->namas1);
			session()->put('filter_kodes2', $request->kodes2);
			session()->put('filter_namas2', $request->namas2);
			session()->put('filter_milik', $request->milik);
			session()->put('filter_bayar', $request->bayar);
		
		$queryakum = DB::SELECT("SET @akum:=0;");
		$query = DB::SELECT("SELECT NO_BUKTI,DATE_FORMAT(TGL,'%d/%m/%y') as TGL,KODES,NAMAS,ALAMAT,KOTA,TRUCK,KODET,NAMAT,KG,HARGA,B_MSOL,B_INAP,TOTAL,BAYAR, 
		(SELECT GROUP_CONCAT(NO_BUKTI,' (',DATE_FORMAT(TGL,'%d/%m/%y'),')') from hut WHERE NO_BUKTI in (SELECT NO_BUKTI from hutd WHERE NO_FAKTUR=a.NO_BUKTI)) as NO_HUT,
		(SELECT DATE_FORMAT(TGL,'%d/%m/%y') from jual WHERE NO_BUKTI=a.NO_JUAL) as TGL_BONGKAR, (SELECT DATE_FORMAT(TGL_SURATS,'%d/%m/%y') from jual WHERE NO_BUKTI=a.NO_JUAL) as TGL_MUAT,
		(SELECT TOTAL1 from beli WHERE NO_BUKTI=a.NO_UM) as NOM_UM
		FROM beli a WHERE ((FLAG='BL' and LEFT(NO_BUKTI,2)='BZ') or FLAG='BN') and TGL between '$tglDrD' and '$tglSmpD' $filtersup $filtermilik $filterbayar   ORDER BY RIGHT(TRIM(TGL_MUAT),2) , SUBSTR(TGL_MUAT,4,2), LEFT(TGL_MUAT,2) ASC  ; ") ;
 
		if($request->has('filter'))
		{
			return view('oreport_expedisi.index')->with(['hasil' => $query]);
		}

		$data=[];
		foreach ($query as $key => $value)
		{
			array_push($data, array(
				'NO_BUKTI' 	=> $query[$key]->NO_BUKTI,
				'TGL' 		=> $query[$key]->TGL,
				'TGL_MUAT' 	=> $query[$key]->TGL_MUAT,
				'TGL_BONGKAR'=> $query[$key]->TGL_BONGKAR,
				'KODES' 	=> $query[$key]->KODES,
				'NAMAS' 	=> $query[$key]->NAMAS,
				'ALAMAT' 	=> $query[$key]->ALAMAT,
				'KOTA' 		=> $query[$key]->KOTA,
				'TRUCK' 	=> $query[$key]->TRUCK,
				'KODET' 	=> $query[$key]->KODET,
				'NAMAT' 	=> $query[$key]->NAMAT,
				'KG' 		=> $query[$key]->KG,
				'HARGA' 	=> $query[$key]->HARGA,
				'B_MSOL' 	=> $query[$key]->B_MSOL,
				'B_INAP' 	=> $query[$key]->B_INAP,
				'TOTAL' 	=> $query[$key]->TOTAL,
				'BAYAR' 	=> $query[$key]->BAYAR,
				'NO_HUT' 	=> $query[$key]->NO_HUT,
				'NOM_UM' 	=> $query[$key]->NOM_UM,
			));
		}
		$PHPJasperXML->setData($data);
		ob_end_clean();
		$PHPJasperXML->outpage("I");
	}
	
}
