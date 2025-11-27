<?php
namespace App\Http\Controllers\OReport;

use App\Http\Controllers\Controller;
use App\Models\Master\Cbg;
use DB;
use Illuminate\Http\Request;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";
use PHPJasperXML;

class RPoController extends Controller
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

        return view('oreport_po.report')->with(['cbg' => $cbg])->with(['hasil' => []]);
    }

    public function jasperPoReport(Request $request)
    {
        $file         = 'pon';
        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));


        $filtertgl = '';

        if (!empty($request->tglDr) && !empty($request->tglSmp)) {
            $tglDrD  = date("Y-m-d", strtotime($request->tglDr));
            $tglSmpD = date("Y-m-d", strtotime($request->tglSmp));
            $filtertgl = " po.TGL BETWEEN '$tglDrD' AND '$tglSmpD' ";
        }

        session()->put('filter_gol', $request->gol);
        session()->put('filter_kodes1', $request->kodes);
        session()->put('filter_namas1', $request->NAMAS);
        session()->put('filter_tglDari', $request->tglDr);
        session()->put('filter_tglSampai', $request->tglSmp);
        session()->put('filter_brg1', $request->brg1);
        session()->put('filter_nabrg1', $request->nabrg1);
        session()->put('filter_cbg', $request->cbg);

        $brg  = $request->brg1;
        $sup  = $request->KODES;
        $jtempo = $request->JTEMPO;
        $where = [];
        $bindings = [];

        // Tambahkan kondisi jika filter tidak kosong
        if (!empty($brg)) {
            $where[] = "pod.KD_BRG = ?";
            $bindings[] = $brg;
        }

        if (!empty($sup)) {
            $where[] = "po.KODES = ?";
            $bindings[] = $sup;
        }


        if (!empty($jtempo)) {
            $where[] = "po.JTEMPO = ?";
            $bindings[] = $jtempo;
        }

        if (!empty($filtertgl)) {
            $where[] = $filtertgl;  // Pastikan $filtertgl sudah dalam format yang benar
        }


        // Gabungkan WHERE jika ada filter yang diisi, jika tidak kosongkan WHERE
        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

        // Query final
        $sql = "SELECT
            po.NO_BUKTI,
            DATE_FORMAT(po.TGL, '%d-%m-%Y') AS TGL,
            po.KODES,
            po.JTEMPO,
            po.NAMAS,
            pod.KD_BRG,
            pod.NA_BRG,
            pod.QTY,
            pod.HARGA,
            pod.TOTAL,
            po.NOTES,
            pod.SATUAN,
            po.GOL,
            pod.KIRIM,
            po.SISA
        FROM po
        JOIN pod ON po.NO_BUKTI = pod.NO_BUKTI
        $whereClause
        ORDER BY po.NO_BUKTI";

        // Eksekusi query
        $query = DB::select($sql, $bindings);


        if ($request->has('filter')) {
            $cbg = Cbg::groupBy('CBG')->get();

            return view('oreport_po.report')->with(['cbg' => $cbg])->with(['hasil' => $query]);
        }

        $data = [];
        foreach ($query as $key => $value) {
            array_push($data, [
                'NO_PO'  => $query[$key]->NO_BUKTI,
                'TGL'    => $query[$key]->TGL,
                'KODES'  => $query[$key]->KODES,
                'NAMAS'  => $query[$key]->NAMAS,
                'KD_BRG' => $query[$key]->KD_BRG,
                // 'KD_BRG'    => "`".strval($query[$key]->KD_BRG),
                'NA_BRG' => $query[$key]->NA_BRG,
                'QTY'    => $query[$key]->QTY,
                'HARGA'  => $query[$key]->HARGA,
                'TOTAL'  => $query[$key]->TOTAL,
                'KET'    => $query[$key]->KET,
                'GOL'    => $query[$key]->GOL,
                'KIRIM'  => $query[$key]->KIRIM,
                'SISA'   => $query[$key]->SISA,
            ]);
        }
        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I");
    }

}