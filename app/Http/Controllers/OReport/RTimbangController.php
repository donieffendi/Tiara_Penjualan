<?php

namespace App\Http\Controllers\OReport;

use App\Http\Controllers\Controller;
use App\Models\Master\Sup;

use Carbon\Carbon;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use DB;

include_once base_path()."/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";
use PHPJasperXML;

class RTimbangController extends Controller
{

   public function report()
    {
		$kodes = Sup::query()->get();
        return view('oreport_timbang.report')->with(['kodes' => $kodes]);
    }
	
	public function getTimbangReport(Request $request)
    {
        $query = DB::connection('mysql')->table('timbang')->select('NO_BUKTI','TGL','KODE','NAMA','NA_BRG','SATU','DUA','SOPIR','KG1','MASUKTM','KELUARTM','TRUCK','NOTES', 
		DB::raw("(CASE
					WHEN FLAG ='BL' THEN 'PEMBELIAN'
					WHEN FLAG ='JL' THEN 'PENJUALAN'
					WHEN FLAG ='TP' THEN 'TITIP'
					ELSE 'STOCK'
				END) AS FLAG"));
			 
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
			
			if (!empty($request->kodes))
			{
				$query = $query->where('KODE', $request->kodes);
			}
			
			if (!empty($request->tglDr) && !empty($request->tglSmp))
			{
				$query = $query->whereBetween('TGL', [$tglDrD, $tglSmpD]);
			}

			if (!empty($request->flag))
			{
				if ($request->flag=="BELI") $query = $query->where('FLAG', 'BL');
				if ($request->flag=="JUAL") $query = $query->where('FLAG', 'JL');
				if ($request->flag=="TITIP") $query = $query->where('FLAG', 'TP');
				if ($request->flag=="STOCK") $query = $query->where('FLAG', 'ST');
				if ($request->flag=="K_MASUK") $query = $query->where('FLAG', 'SM');
				if ($request->flag=="K_KELUAR") $query = $query->where('FLAG', 'SK');
			}
			
			$query = $query->orderBy('NO_BUKTI', 'ASC')->get();
			return Datatables::of($query)->addIndexColumn()->make(true);
		}
		
    }	  
	 
	public function jasperTimbangReport(Request $request) 
	{
		$file 	= 'timbangn';
		$PHPJasperXML = new PHPJasperXML();
		$PHPJasperXML->load_xml_file(base_path().('/app/reportc01/phpjasperxml/'.$file.'.jrxml'));
		
			// Check Filter
			if (!empty($request->gol))
			{
				$filtergol = " and GOL='".$request->gol."' ";
			}
			
			if (!empty($request->kodes))
			{
				$filterkodes = " and KODE='".$request->kodes."' ";
			}
			
			if (!empty($request->tglDr) && !empty($request->tglSmp))
			{
				$tglDrD = date("Y-m-d", strtotime($request->tglDr));
				$tglSmpD = date("Y-m-d", strtotime($request->tglSmp));
				$filtertgl = " WHERE TGL between '".$tglDrD."' and '".$tglSmpD."' ";
			}	

			if (!empty($request->flag))
			{
				$filterflag = " ";
				if ($request->flag=="BELI") $filterflag = " and FLAG='BL' ";
				if ($request->flag=="JUAL") $filterflag = " and FLAG='JL' ";
				if ($request->flag=="TITIP") $filterflag = " and FLAG='TP' ";
				if ($request->flag=="STOCK") $filterflag = " and FLAG='ST' ";
				if ($request->flag=="K_MASUK") $filterflag = " and FLAG='SM' ";
				if ($request->flag=="K_KELUAR") $filterflag = " and FLAG='SK' ";
			}
		
		$query = DB::connection('mysql')->SELECT("
			SELECT NO_BUKTI,TGL,KODE,NAMA,NA_BRG, SATU, DUA, SOPIR, KG1,MASUKTM,KELUARTM,TRUCK,NOTES, 
			       CASE
					WHEN FLAG ='BL' THEN 'PEMBELIAN'
					WHEN FLAG ='JL' THEN 'PENJUALAN'
					WHEN FLAG ='TP' THEN 'TITIP'
					ELSE 'STOCK'
					END AS FLAG from timbang $filtertgl $filtergol $filterkodes $filterflag order by NO_BUKTI;
		");

		$data=[];
		foreach ($query as $key => $value)
		{
			array_push($data, array(
				'NO_BUKTI' => $query[$key]->NO_BUKTI,
				'TGL' => date("d/m/Y", strtotime($query[$key]->TGL)),
				'KODE' => $query[$key]->KODE,
				'NAMA' => $query[$key]->NAMA,
				'NA_BRG' => $query[$key]->NA_BRG,
				'KG1' => $query[$key]->KG1,
				'SATU' => $query[$key]->SATU,
				'DUA' => $query[$key]->DUA,
				'MASUKTM' => date("H:i:s", strtotime($query[$key]->MASUKTM)),
				'KELUARTM' => date("H:i:s", strtotime($query[$key]->KELUARTM)),
				'TRUCK' => $query[$key]->TRUCK,
				'NOTES' => $query[$key]->NOTES,
				'FLAG' => $query[$key]->FLAG,
			));
		}
		$PHPJasperXML->setData($data);
		ob_end_clean();
		$PHPJasperXML->outpage("I");
	}
	
}
