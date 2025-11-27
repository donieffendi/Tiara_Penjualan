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

class RBcController extends Controller
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

        return view('oreport_BC.report')->with([
            'cbg' => $cbg,
            'per' => $per,
            'user' => $user,
            'hasil' => []
        ]);
    }

    public function jasperBcReport(Request $request)
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
        // Tentukan nama tabel sesuai bulan
        $bulan = $request->bulan ?? '';
        $cbg = !empty($request->cbcbg) ? $request->cbcbg : '';

        $jualdTable = $cbg . '.juald' . $bulan;
        $jualTable = $cbg . '.jual' . $bulan;

        $sql = "SELECT
                ? as no_form,
                ? as na_toko,
                ? as typ_pers,
                ? as typ_npwp,
                ? as alamat_pers,
                {$jualdTable}.*,
                {$jualTable}.tgl,
                {$jualTable}.KSR,
                {$jualTable}.tgl as initanggal,
                {$jualTable}.usrnm,
                {$jualTable}.CBG as cabang,
                1 as posted
            FROM {$jualdTable}, {$jualTable}
            WHERE {$jualdTable}.no_bukti = {$jualTable}.no_bukti
                AND LENGTH({$jualdTable}.kd_brg) > 7
                AND {$jualTable}.cbg = ?
                AND {$jualTable}.tgl BETWEEN ? AND ?
            ORDER BY initanggal, KSR, {$jualdTable}.no_bukti, {$jualdTable}.bukti2";

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

            return view('oreport_BC.report')->with([
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
                'NO_FORM'      => $value->no_form ?? '',
                'NA_TOKO'      => $value->na_toko ?? '',
                'TYP_PERS'     => $value->typ_pers ?? '',
                'TYP_NPWP'     => $value->typ_npwp ?? '',
                'ALAMAT_PERS'  => $value->alamat_pers ?? '',
                'KD_BRG'       => $value->kd_brg ?? '',
                'NO_BUKTI'     => $value->no_bukti ?? '',
                'BKT2'         => $value->bukti2 ?? '',
                'QTY'          => $value->qty ?? 0,
                'HARGA'        => $value->harga ?? 0,
                'TGL'          => $value->tgl ?? '',
                'KSR'          => $value->KSR ?? '',
                'INITANGGAL'   => $value->initanggal ?? '',
                'USRNM'        => $value->usrnm ?? '',
                'CABANG'       => $value->cabang ?? '',
                'POSTED'       => $value->posted ?? 1,
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