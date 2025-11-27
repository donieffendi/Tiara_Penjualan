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

class RKode8Controller extends Controller
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

        return view('oreport_kode8.report')->with([
            'cbg' => $cbg,
            'per' => $per,
            'user' => $user,
            'hasil' => []
        ]);
    }

    public function jasperKode8Report(Request $request)
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
        $bulan = $request->bulan ?? '';
        $cbg = !empty($request->cbcbg) ? $request->cbcbg : '';
        $jualTable = $cbg . '.jual' . $bulan;
        $jualdTable = $cbg . '.juald' . $bulan;

        $no_form = $request->no_form ?? '';
        $na_toko = $request->na_toko ?? '';
        $typ_pers = $request->typ_pers ?? '';
        $alamat_pers = $request->alamat_pers ?? '';
        $ksr = $request->ksr ?? '';
        $tgl1 = !empty($request->tgl1) ? date("Y-m-d", strtotime($request->tgl1)) : null;

        $sql = "SELECT
            ? as no_form,
            ? as na_toko,
            ? as typ_pers,
            ? as alamat_pers,
            {$jualTable}.cbg,
            {$jualTable}.KSR,
            {$jualTable}.SHIFT,
            {$jualTable}.tgl,
            LEFT(TRIM({$jualdTable}.KD_BRG),3) AS SUB,
            {$jualdTable}.KD_BRG,
            {$jualdTable}.NA_BRG,
            SUM({$jualdTable}.qty) as qty,
            {$jualdTable}.harga,
            {$jualdTable}.ppn,
            SUM({$jualdTable}.nppn) as nppn,
            SUM({$jualdTable}.dpp) as dpp,
            SUM({$jualdTable}.tkp) as tkp,
            SUM({$jualdTable}.total) as total,
            IF({$jualdTable}.ppn='1','PPN Barang Produksi',
            IF({$jualdTable}.ppn='2','PPN Barang Cukai',
                IF({$jualdTable}.ppn='3','PPN Barang Import','Tanpa PPN')
            )
            ) AS PPN_KET
        FROM {$jualTable}, {$jualdTable}
        WHERE {$jualTable}.no_bukti = {$jualdTable}.no_bukti
            AND {$jualdTable}.KD_BRG <> ''
            AND {$jualTable}.flag = 'JL'
            AND {$jualTable}.ksr = ?
            AND {$jualTable}.tgl = ?
        GROUP BY
            {$jualTable}.CBG,
            {$jualTable}.ksr,
            {$jualTable}.SHIFT,
            {$jualTable}.tgl,
            {$jualdTable}.KD_BRG,
            {$jualdTable}.ppn
        ORDER BY
            {$jualTable}.CBG,
            {$jualTable}.KSR,
            {$jualTable}.SHIFT,
            {$jualdTable}.PPN,
            {$jualdTable}.KD_BRG";

        $query = DB::select($sql, [
            $no_form,
            $na_toko,
            $typ_pers,
            $alamat_pers,
            $ksr,
            $tgl1,
        ]);


        if ($request->has('filter')) {
            $cbg = Cbg::groupBy('CBG')->get();
            $per = Perid::query()->get();
            $user = User::query()->get();

            return view('oreport_kode8.report')->with([
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
