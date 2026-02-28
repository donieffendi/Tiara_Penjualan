<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Master\Brg;
use App\Models\Master\Sup;
use DataTables;
use Auth;
use DB;

include_once base_path()."/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";
use PHPJasperXML;

class RkplabelController extends Controller
{

    public function index() {
        return view('otransaksi_rkplabel.index');
    }

    public function getRkplabel(Request $request)
    {
        $tgl = $request->TGL;    
        $cbg = $request->CBG;
        // $cbg = Auth::user()->CBG;
        $tglSQL = date('Y-m-d', strtotime($tgl));
        
        // Panggil SP dengan 3 parameter yang pasti ada
        $sql = DB::select("CALL pjl_komponen_harga(?, ?, ?)", ['REKAP_KOMPONEN_HARIAN', $cbg, $tglSQL]);

        return Datatables::of($sql)
            ->addIndexColumn()
            ->make(true);
    }

    public function cetak(Request $request) 
	{
		$file 	= 'rekap_label_hr';
		$PHPJasperXML = new PHPJasperXML();
		$PHPJasperXML->load_xml_file(base_path().('/app/reportc01/phpjasperxml/'.$file.'.jrxml'));
		$params = [
			"TGL_CTK" => date('d/m/Y'),
		];
		$PHPJasperXML->arrayParameter=$params;
		
			$tgl = $request->TGL;
            $cbg = $request->CBG;
			
			$tglSQL = date('Y-m-d', strtotime($tgl));
			
			
		$query = DB::SELECT("CALL pjl_komponen_harga(?, ?, ?)", ['REKAP_KOMPONEN_HARIAN', $cbg, $tglSQL]);

		$data=[];
		
        $data = json_decode(json_encode($query), true);

		$PHPJasperXML->setData($data);
		ob_end_clean();
		$PHPJasperXML->outpage("I");
	}



}