<?php

namespace App\Http\Controllers\OTools;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Master\Brg;
use App\Models\Master\Sup;
use App\Models\Master\Perid;
use DataTables;
use Auth;
use DB;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";
use PHPJasperXML;

class CetakksrController extends Controller
{

    public function index() {
        $cbgList = DB::SELECT("SELECT KODE FROM toko WHERE STA IN ('MA','CB')");
        $per = Perid::query()->get();

        session()->put('filter_bukti1', '000');
        session()->put('filter_bukti2', 'ZZZ');
        session()->put('filter_tglDari', date("d-m-Y"));
        session()->put('filter_tglSampai', date("d-m-Y"));
        session()->put('filter_per', '');
        session()->put('filter_ksr', '');
        session()->put('filter_cbg', '');

        return view('otools_cetakksr.index')->with([
            'cbg' => $cbgList,
            'per' => $per
        ]);
    }

    public function getCetakksr(Request $request)
    {   
        $x1 = date("Y-m-d", strtotime($request->tglDr));
        $x2 = date("Y-m-d", strtotime($request->tglSmp));
        $per = $request->per;
        $ksr = $request->ksr;
        $bukti1 = $request->bukti1;
        $bukti2 = $request->bukti2;
        $cbg = $request->cbg;
        
        // dd($x1,$x2,$per,$ksr,$bukti1,$bukti2,$cbg);
        $cetakksr = DB::SELECT("CALL CETAK_ULANG_KASIR('$x1','$x2','$per','$ksr','$bukti1','$bukti2','$cbg')");

    return Datatables::of($cetakksr)
                ->addIndexColumn()
                ->make(true);
    }

    public function Print(Request $request)
    {
        $x1 = date("Y-m-d", strtotime($request->tglDr));
        $x2 = date("Y-m-d", strtotime($request->tglSmp));
        $per = $request->per;
        $ksr = $request->ksr;
        $bukti1 = $request->bukti1;
        $bukti2 = $request->bukti2;
        $cbg = $request->cbg;

        $file = 'print_cetakksr';
        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));
        $params = [
			"TGL" => date('d/m/Y'),
		];
		$PHPJasperXML->arrayParameter=$params;

        $query = DB::SELECT("CALL CETAK_ULANG_KASIR('$x1','$x2','$per','$ksr','$bukti1','$bukti2','$cbg')");

        $data = [];
        $data = json_decode(json_encode($query), true);

        // Kirim data ke Jasper
        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I"); // "I" artinya inline (tampil di browser)
    }


    


}
