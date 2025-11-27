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

class RPiuController extends Controller
{
 	public function report()
    {
		$kodec = Cust::orderBy('KODEC')->get();
		session()->put('filter_gol', '');
		session()->put('filter_kodec1', '');
		session()->put('filter_namac1', '');
		session()->put('filter_kodet1', '');
		session()->put('filter_namat1', '');
		session()->put('filter_tglDari', date("d-m-Y"));
		session()->put('filter_tglSampai', date("d-m-Y"));
		
        return view('oreport_piu.report')->with(['kodec' => $kodec])->with(['hasil' => []]);
    }
	
	public function getPiuReport(Request $request)
    {
        $query = DB::table('piu')
			->join('piud', 'piu.NO_BUKTI', '=', 'piud.NO_BUKTI')
			->select('piu.NO_BUKTI','piu.NO_SO','piu.TGL', 'piu.KODEC','piu.NAMAC','piud.NO_FAKTUR','piud.TOTAL','piud.BAYAR','piud.SISA','piu.GOL')->get();
			
		if ($request->ajax())
		{
			// Ganti format tanggal input agar sama dengan database
			$tglDrD = date("Y-m-d", strtotime($request['tglDr']));
            		$tglSmpD = date("Y-m-d", strtotime($request['tglSmp']));
			
			// Convert tanggal agar ambil start of day/end of day
			//$tglDr = Carbon::parse($request->tglDr)->startOfDay();
            		$tglSmp = Carbon::parse($request->tglSmp)->endOfDay();
			
			// Check Filter
			if (!empty($request->gol))
			{
				$query = $query->where('GOL', $request->gol);
			}
			
			if (!empty($request->kodec))
			{
				$query = $query->where('KODEC', $request->kodec);
			}
			
			if (!empty($request->tglDr) && !empty($request->tglSmp))
			{
				$query = $query->whereBetween('TGL', [$tglDrD, $tglSmp]);
			}
			
			return Datatables::of($query)->addIndexColumn()->make(true);
		}
		
    }	  

	public function jasperPiuReport(Request $request) 
	{
		$file 	= 'piun';
		$PHPJasperXML = new PHPJasperXML();
		$PHPJasperXML->load_xml_file(base_path().('/app/reportc01/phpjasperxml/'.$file.'.jrxml'));
		
			// Check Filter
			if (!empty($request->kodet))
			{
				$filterkodet = " WHERE KODET='".$request->kodet."' ";
			}
			
			if (!empty($request->gol))
			{
				$filtergol = " and piu.GOL='".$request->gol."' ";
			}
			
			if (!empty($request->kodec))
			{
				$filterkodec = " and piu.KODEC='".$request->kodec."' ";
			}
			
			if (!empty($request->tglDr) && !empty($request->tglSmp))
			{
				$tglDrD = date("Y-m-d", strtotime($request->tglDr));
				$tglSmpD = date("Y-m-d", strtotime($request->tglSmp));
				$filtertgl = " and piu.TGL between '".$tglDrD."' and '".$tglSmpD."' ";
			}
			
			session()->put('filter_gol', $request->gol);
			session()->put('filter_kodec1', $request->kodec);
			session()->put('filter_namac1', $request->NAMAC);
			session()->put('filter_kodet1', $request->kodet);
			session()->put('filter_namat1', $request->NAMAT);
			session()->put('filter_tglDari', $request->tglDr);
			session()->put('filter_tglSampai', $request->tglSmp);

		$query = DB::SELECT("
		SELECT * from 
		(
			SELECT piu.NO_BUKTI,piu.BACNO,piu.BNAMA,piu.NO_SO,piu.TGL,piu.KODEC,piu.NAMAC,piud.NO_FAKTUR,piud.TOTAL,piud.BAYAR,piud.SISA,piu.GOL, 
			(SELECT KODET from so WHERE NO_BUKTI=(SELECT NO_SO from jual WHERE NO_BUKTI=piud.NO_FAKTUR limit 1) limit 1) as KODET,
			(SELECT NAMAT from so WHERE NO_BUKTI=(SELECT NO_SO from jual WHERE NO_BUKTI=piud.NO_FAKTUR limit 1) limit 1) as NAMAT 
			from piu,piud WHERE piu.NO_BUKTI=piud.NO_BUKTI  $filtertgl $filtergol $filterkodec
		) as rpiu
		$filterkodet
		");

		if($request->has('filter'))
		{
			return view('oreport_piu.report')->with(['hasil' => $query]);
		}
        
		$data=[];
		foreach ($query as $key => $value)
		{
			array_push($data, array(
				'NO_BUKTI' => $query[$key]->NO_BUKTI,
				'BACNO' => $query[$key]->BACNO,
				'BNAMA' => $query[$key]->BNAMA,
				'NO_SO' => $query[$key]->NO_SO,
				'TGL' => $query[$key]->TGL,
				'KODEC' => $query[$key]->KODEC,
				'NAMAC' => $query[$key]->NAMAC,
				'NO_FAKTUR' => $query[$key]->NO_FAKTUR,
				'TOTAL' => $query[$key]->TOTAL,
				'BAYAR' => $query[$key]->BAYAR,
				'SISA' => $query[$key]->SISA,
				'GOL' => $query[$key]->GOL,
				'NAMAT' => $query[$key]->NAMAT,
			));
		}
		$PHPJasperXML->setData($data);
		ob_end_clean();
		$PHPJasperXML->outpage("I");
	}
	
}
