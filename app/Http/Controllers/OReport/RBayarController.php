<?php

namespace App\Http\Controllers\OReport;

use App\Http\Controllers\Controller;
use App\Models\Master\Cbg;
use App\Models\Master\Perid;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;

class RBayarController extends Controller
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


        return view('oreport_bayar.report')->with([
            'cbg' => $cbg,
            'hasilAgenda' => [],
            'per' => $per,
            'hasilTR' => []
        ]);
    }

    public function jasperBayarReport(Request $request)
    {
        $file         = 'bayarn';
        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));


        $filtertgl = '';

        if (!empty($request->tglDr) && !empty($request->tglSmp)) {
            $tglDrD  = date("Y-m-d", strtotime($request->tglDr));
            $tglSmpD = date("Y-m-d", strtotime($request->tglSmp));
            $filtertgl = " bayar.TGL BETWEEN '$tglDrD' AND '$tglSmpD' ";
        }

        session()->put('filter_gol', $request->gol);
        session()->put('filter_kodes1', $request->kodes);
        session()->put('filter_namas1', $request->NAMAS);
        session()->put('filter_tglDari', $request->tglDr);
        session()->put('filter_tglSampai', $request->tglSmp);
        session()->put('filter_brg1', $request->brg1);
        session()->put('filter_nabrg1', $request->nabrg1);
        session()->put('filter_cbg', $request->cbg);

        // Query final
        $cbgTable = '';
        if (!empty($request->cbcbg)) {
            $cbgTable = $request->cbcbg . '.';
        }

        // Ambil nilai radio button dari request
        $filterType = $request->input('filter_type', 'nosp');
        $filterValue = $request->input('filter_value', '');

        // Ambil nilai filter sesuai radio button
        if (!empty($filterValue)) {
            switch ($filterType) {
                case 'nosp':
                    $whereFilter = "no_po = '$filterValue'";
                    break;
                case 'notagi':
                    $whereFilter = "NO_TAGI = '$filterValue'";
                    break;
                case 'kodesup':
                    $whereFilter = "kodes = '$filterValue'";
                    break;
                case 'noagenda':
                    $whereFilter = "no_bukti = '$filterValue'";
                    break;
                default:
                    $whereFilter = "1=1";
            }
        } else {
            $whereFilter = "1=1"; // Tampilkan semua jika tidak ada filter
        }

        $agenda = "SELECT NO_TAGI, no_bukti, flag, tgl, kodes, namas, no_po, total_qty, total, nett, usrnm, cbg as beliz, 0 as posted
            FROM {$cbgTable}BELIZ
            WHERE (FLAG='TL' OR FLAG='TH') AND $whereFilter";

        $tr = "SELECT NO_TAGI, no_bukti, flag, tgl, kodes, namas, no_po, total_qty, total, nett, usrnm, cbg as beliz, 1 as posted
            FROM {$cbgTable}BELIZ
            WHERE (FLAG='TR') AND $whereFilter";

        // Eksekusi query
        $queryAgenda = DB::select($agenda);
        $queryTR = DB::select($tr);


        if ($request->has('filter')) {
            $cbg = Cbg::groupBy('CBG')->get();

            return view('oreport_bayar.report')->with([
                'cbg' => $cbg,
                'hasilAgenda' => $queryAgenda,
                'hasilTR' => $queryTR
            ]);
        }

        // Untuk cetak report
        if ($request->has('cetak') || $request->has('cetak_tr')) {
            $query = $request->has('cetak_tr') ? $queryTR : $queryAgenda;
        } else {
            $query = $queryAgenda; // default
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