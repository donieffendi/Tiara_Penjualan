<?php

namespace App\Http\Controllers\OReport;

use App\Http\Controllers\Controller;
use App\Models\Master\Cbg;
use App\Models\Master\Perid;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;

class ROmzetController extends Controller
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
        $user = User::query()->get();

        return view('oreport_omzet.report')->with([
            'cbg' => $cbg,
            'per' => $per,
            'user' => $user,
            'hasil' => []
        ]);
    }

    public function jasperOmzetReport(Request $request)
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

        // Ambil parameter dari request
        $tgl1 = !empty($request->tglDr) ? date("Y-m-d", strtotime($request->tglDr)) : null;
        $tgl2 = !empty($request->tglSmp) ? date("Y-m-d", strtotime($request->tglSmp)) : null;
        $per = $request->per ?? '';
        $cbgParam = $request->cbg ?? '';
        $no_form = $request->no_form ?? '';
        $na_toko = $request->na_toko ?? '';
        $typ_pers = $request->typ_pers ?? '';
        $typ_npwp = $request->typ_npwp ?? '';
        $alamat_pers = $request->alamat_pers ?? '';

        // Nama schema/tabel prefix
        $cbgTable = !empty($request->cbcbg) ? $request->cbcbg . '.' : '';

        // Query SQL yang diperbaiki
        $sql = "SELECT
                    ? as no_form,
                    ? as na_toko,
                    ? as typ_pers,
                    ? as typ_npwp,
                    ? as alamat_pers,
                    aotprice.SUB,
                    aotprice.KELOMPOK,
                    SUM({$cbgTable}juald.total) as total
                FROM {$cbgTable}jual
                INNER JOIN {$cbgTable}juald ON {$cbgTable}juald.no_bukti = {$cbgTable}jual.no_bukti
                INNER JOIN aotprice ON LEFT({$cbgTable}juald.KD_BRG, 3) = aotprice.SUB
                WHERE {$cbgTable}jual.flag = 'JL'
                    AND {$cbgTable}jual.CBG = ?
                    AND {$cbgTable}jual.per = ?
                    AND {$cbgTable}jual.tgl >= ?
                    AND {$cbgTable}jual.tgl <= ?
                GROUP BY aotprice.SUB, aotprice.KELOMPOK
                ORDER BY aotprice.SUB ASC";

        // Eksekusi query dengan parameter yang benar
        $query = DB::select($sql, [
            $no_form,
            $na_toko,
            $typ_pers,
            $typ_npwp,
            $alamat_pers,
            $cbgParam,
            $per,
            $tgl1,
            $tgl2,
        ]);


        if ($request->has('filter')) {
            $cbg = Cbg::groupBy('CBG')->get();
            $per = Perid::query()->get();
            $user = User::query()->get();

            return view('oreport_omzet.report')->with([
                'cbg' => $cbg,
                'per' => $per,
                'user' => $user,
                'hasil' => $query,
            ]);
        }

        // Untuk cetak report (Jasper)
        $data = [];
        foreach ($query as $key => $value) {
            array_push($data, [
                'NO_FORM' => $query[$key]->no_form ?? '',
                'NA_TOKO' => $query[$key]->na_toko ?? '',
                'TYP_PERS' => $query[$key]->typ_pers ?? '',
                'TYP_NPWP' => $query[$key]->typ_npwp ?? '',
                'ALAMAT_PERS' => $query[$key]->alamat_pers ?? '',
                'SUB' => $query[$key]->SUB ?? '',
                'KELOMPOK' => $query[$key]->KELOMPOK ?? '',
                'TOTAL' => $query[$key]->total ?? 0,
            ]);
        }

        $file = 'omzet';
        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

        $PHPJasperXML->transferDBtoArray("localhost", "root", "", "tiara");
        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I");
    }
}