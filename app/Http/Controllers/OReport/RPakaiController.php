<?php

namespace App\Http\Controllers\OReport;

use App\Http\Controllers\Controller;
// ganti 1
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Master\Cust;
use DataTables;
use Auth;
use DB;

// ganti 2
class RPakaiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function report()
    {
       	$kodec = Cust::orderBy('KODEC')->get();
		//return $acno;
// GANTI 3 //
        return view('oreport_pakai.report')->with(['kodec' => $kodec]);
		
    }
	
		public function getPakaiReport(Request $request)
    {
        $query = DB::table('pakai')
			->join('pakaid', 'pakai.NO_BUKTI', '=', 'pakaid.NO_BUKTI')
			->select('pakai.NO_BUKTI', 'pakai.TGL', 'pakaid.KD_BHN', 'pakaid.NA_BHN', 'pakaid.QTY', 'pakaid.KET', 'pakai.GOL')->get();
						
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
				$query = $query->whereBetween('TGL', [$tglDrD, $tglSmpD]);
			}
			
			return Datatables::of($query)->addIndexColumn()->make(true);
		}
		
    }	  
	 


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */


	public function jasperPakaiReport(Request $request) 
	{
		$file 	= 'pakai';
		$PHPJasperXML = new PHPJasperXML();
		$PHPJasperXML->load_xml_file(base_path().('/app/reportc01/phpjasperxml/'.$file.'.jrxml'));
	
			// Check Filter
			if (!empty($request->gol))
			{
				$filtergol = " and pakai.GOL='".$request->gol."' ";
			}
			
			if (!empty($request->kodec))
			{
				$filterkodec = " and pakai.KODEC='".$request->kodec."' ";
			}
			
			if (!empty($request->tglDr) && !empty($request->tglSmp))
			{
				$tglDrD = date("Y-m-d", strtotime($request->tglDr));
				$tglSmpD = date("Y-m-d", strtotime($request->tglSmp));
				$filtertgl = " and hut.TGL between '".$tglDrD."' and '".$tglSmpD."' ";
			}
			
		$query = DB::SELECT("
			SELECT pakai.NO_BUKTI,pakai.TGL,pakai.KODEC,pakai.NAMAC,pakaid.KD_BHN, pakaid.NA_BHN, pakaid.QTY, pakaid.KET,pakai.GOL from pakaid,pakaid WHERE pakai.NO_BUKTI=pakai.NO_BUKTI $filtertgl $filtergol $filterkodec /*order by pakai.KODEC,pakai.NO_BUKTI*/;
		");
		

		$data=[];
		foreach ($query as $key => $value)
		{
			array_push($data, array(
				'NO_BUKTI' => $query[$key]->NO_BUKTI,
				'TGL' => $query[$key]->TGL,
				'KODEC' => $query[$key]->KODEC,
				'NAMAC' => $query[$key]->NAMAC,
				'KD_BHN' => $query[$key]->KD_BHN,
				'NA_BHN' => $query[$key]->NA_BHN,
				'QTY' => $query[$key]->QTY,
				'KET' => $query[$key]->KET,
				'GOL' => $query[$key]->GOL,
			));
		}
		$PHPJasperXML->setData($data);
		ob_end_clean();
		$PHPJasperXML->outpage("I");
	}
	
	
	

	
}
