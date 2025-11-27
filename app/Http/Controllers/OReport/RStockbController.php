<?php

namespace App\Http\Controllers\OReport;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Master\Brg;
use App\Models\Master\Cbg;
use DataTables;
use Auth;
use DB;

include_once base_path()."/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";
use PHPJasperXML;

use \koolreport\laravel\Friendship;
use \koolreport\bootstrap4\Theme;

class RStockbController extends Controller
{

    public function report()
    {
		$cbg = Cbg::groupBy('CBG')->get();
		session()->put('filter_cbg', '');

		$kd_brg = Brg::query()->get();
		session()->put('filter_tglDari', date("d-m-Y"));
		session()->put('filter_tglSampai', date("d-m-Y"));
		session()->put('filter_type', '');
		session()->put('filter_flag', '');
		session()->put('filter_posted', '');

    //     return view('oreport_stockb.report')->with(['kd_brg' => $kd_brg])->with(['cbg' => $cbg])->with(['hasil' => []]);
    // }
	return view('oreport_stockb.report')->with(['hasil' => []]);
	}
	
	public function getStockbReport(Request $request)
    {
			
 		$query = DB::table('stockbd')
		->select('NO_BUKTI',  'KD_BRG', 'NA_BRG', 'QTY')->get();
		
					
		if ($request->ajax())
		{
			// Ganti format tanggal input agar sama dengan database
			$tglDrD = date("Y-m-d", strtotime($request['tglDr']));
            		$tglSmpD = date("Y-m-d", strtotime($request['tglSmp']));
			
			// Convert tanggal agar ambil start of day/end of day
			//$tglDr = Carbon::parse($request->tglDr)->startOfDay();
    			$tglSmp = Carbon::parse($request->tglSmp)->endOfDay();
			
			// Check Filter
			
			if (!empty($request->kd_brg))
			{
				$query = $query->where('KD_BRG', $request->kd_brg);
			}
			
			if (!empty($request->tglDr) && !empty($request->tglSmp))
			{
				$query = $query->whereBetween('TGL', [$tglDrD, $tglSmp]);
			}
			
			return Datatables::of($query)->addIndexColumn()->make(true);
		}
		
    }	  
	 
	public function jasperStockbReport(Request $request) 
{
    $file = 'stockn';
    $PHPJasperXML = new PHPJasperXML();
    $PHPJasperXML->load_xml_file(base_path().('/app/reportc01/phpjasperxml/'.$file.'.jrxml'));

    // Ganti format tanggal input agar sama dengan database
    $tglDrD = date("Y-m-d", strtotime($request['tglDr']));
    $tglSmpD = date("Y-m-d", strtotime($request['tglSmp']));

    // Convert tanggal agar ambil start of day/end of day
    $tglSmp = Carbon::parse($request->tglSmp)->endOfDay();

    // Initialize the filter strings
    $filtertgl = '';
    $filterkdbrg = '';
    $filterflag = '';
    $filterposted = '';
    $filtercbg = '';

    // Apply filters dynamically
    if (!empty($request->kd_brg)) {
        $filterkdbrg = " AND sb.KD_BRG = '".$request->kd_brg."' ";
    }

    if (!empty($request->tglDr) && !empty($request->tglSmp)) {
        $filtertgl = " AND s.TGL BETWEEN '".$tglDrD."' AND '".$tglSmpD."' ";
    }

    if (!empty($request->flag)) {
        $filterflag = " AND sb.FLAG = '".$request->flag."' ";
    }

    if (!empty($request->posted)) {
        $filterposted = " AND s.POSTED = '".$request->posted."' ";
    }

    if (!empty($request->cbg)) {
        $filtercbg = " AND s.CBG = '".$request->cbg."' ";
    }

    // Combine all filters
    $filter = $filtertgl . $filterflag . $filterposted . $filterkdbrg . $filtercbg;

    // Save session filters
    session()->put('filter_tglDari', $request->tglDr);
    session()->put('filter_tglSampai', $request->tglSmp);
    session()->put('filter_posted', $request->posted);
    session()->put('filter_cbg', $request->cbg);
    session()->put('filter_flag', $request->flag);

    // Query for report
    $query = DB::select("
        SELECT *
        FROM stockb s
        JOIN stockbd sb ON s.NO_BUKTI = sb.NO_BUKTI
        WHERE 1=1 $filter
    ");

    // If filter is applied, return the report view
    if ($request->has('filter')) {
        $cbg = Cbg::groupBy('CBG')->get();
        return view('oreport_stockb.report')->with(['cbg' => $cbg])->with(['hasil' => $query]);
    }

    // Prepare data for Jasper report
    $data = [];
    foreach ($query as $key => $value) {
        array_push($data, [
            'NO_BUKTI' => $value->NO_BUKTI,
            'TGL' => $value->TGL,
            'KD_BRG' => $value->KD_BRG,
            'NA_BRG' => $value->NA_BRG,
            'KG' => $value->KG,
            'NOTES' => $value->NOTES,
        ]);
    }

    $PHPJasperXML->setData($data);
    ob_end_clean();
    $PHPJasperXML->outpage("I");
}
}