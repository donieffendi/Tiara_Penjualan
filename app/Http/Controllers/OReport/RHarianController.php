<?php

namespace App\Http\Controllers\OReport;

use App\Http\Controllers\Controller;
use App\Models\Master\Cbg;
use App\Models\Master\Perid;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;

class RHarianController extends Controller
{
    public function report()
    {
        $cbg = Cbg::groupBy('CBG')->get();
        session()->put('filter_cbg', '');

        session()->put('filter_gol', '');
        session()->put('filter_kodes1', '');
        session()->put('filter_namas1', '');
        session()->put('filter_brg1', '');
        session()->put('filter_nabrg1', '');
        session()->put('filter_tglDari', date("d-m-Y"));
        session()->put('filter_tglSampai', date("d-m-Y"));
        $per = Perid::query()->get();

        return view('oreport_harian.report')->with([
            'cbg' => $cbg,
            'per' => $per,
            'hasil' => []
        ]);
    }

    public function jasperBayarReport(Request $request)
    {
        $file         = 'bayarn';
        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));


        // Query final
        $cbgTable = '';
        if (!empty($request->cbcbg)) {
            $cbgTable = $request->cbcbg . '.';
        }

        $sql = "CALL " . $cbgTable . "adm_serahterima_fbayar(:prosesx, :cbgx, :filex, '', :flagx)";

        // Eksekusi query
        $query = DB::select($sql);


        if ($request->has('filter')) {
            $cbg = Cbg::groupBy('CBG')->get();

            return view('oreport_bayar.report')->with([
                'cbg' => $cbg,
                'hasil' => $query,
            ]);
        }

        $data = [];
        foreach ($query as $key => $value) {
            array_push($data, [
                'NO_TAGI' => $query[$key]->NO_TAGI ?? '',
                'NO_BUKTI'  => $query[$key]->no_bukti ?? '',
                'FLAG'   => $query[$key]->flag ?? '',
                'TGL'    => $query[$key]->tgl ?? '',
                'KODES'  => $query[$key]->kodes ?? '',
                'NAMAS'  => $query[$key]->namas ?? '',
                'NO_PO'  => $query[$key]->no_po ?? '',
                'TOTAL_QTY' => $query[$key]->total_qty ?? 0,
                'TOTAL'  => $query[$key]->total ?? 0,
                'NETT'   => $query[$key]->nett ?? 0,
                'USRNM'  => $query[$key]->usrnm ?? '',
                'CBG'    => $query[$key]->beliz ?? '',
                'POSTED' => $query[$key]->posted ?? 0,
            ]);
        }
        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I");
    }
}
