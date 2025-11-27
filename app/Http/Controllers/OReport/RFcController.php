<?php

namespace App\Http\Controllers\OReport;

use App\Http\Controllers\Controller;
use App\Models\Master\Cbg;
use App\Models\Master\Perid;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;

class RFcController extends Controller
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


        return view('oreport_fc.report')->with([
            'cbg' => $cbg,
            'hasilAgenda' => [],
            'per' => $per,
            'hasilTR' => [],
            'hasilFC' => [],
            'no_form' => '',
            'na_toko' => '',
            'typ_pers' => '',
            'typ_npwp' => '',
            'alamat_pers' => '',
            'isSameMonth' => true,
        ]);
    }

    public function jasperFcReport(Request $request)
    {
        $file = 'fcreport';
        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

        $filtertgl = '';

        if (!empty($request->tglDr) && !empty($request->tglSmp)) {
            $tgl1 = date("Y-m-d", strtotime($request->tglDr));
            $tgl2 = date("Y-m-d", strtotime($request->tglSmp));
            $filtertgl = " TGL BETWEEN '$tgl1' AND '$tgl2' ";
        }

        session()->put('filter_cbg', $request->cbcbg);

        $cbgTable = '';
        if (!empty($request->cbcbg)) {
            $cbgTable = $request->cbcbg . '.';
        }

        // Query untuk mendapatkan informasi toko dan form
        $tokoQuery = "SELECT toko.KODE, toko.TYP, toko.NA_TOKO, toko.NA_PERS, toko.TYP_PERS, toko.TYP_NPWP, toko.ALAMAT, tokoform.NO_BUKTI
                     FROM {$cbgTable}toko, {$cbgTable}tokoform
                     WHERE toko.TYP = tokoform.TYP AND toko.KODE = ? AND tokoform.KD_PRNT = ?";

        $tokoData = DB::select($tokoQuery, [$request->cbcbg, 'JUALFC-REPORT3']);

        $no_form = '';
        $na_toko = '';
        $typ_pers = '';
        $typ_npwp = '';
        $alamat_pers = '';

        if (!empty($tokoData)) {
            $no_form = $tokoData[0]->NO_BUKTI ?? '';
            $na_toko = $tokoData[0]->NA_TOKO ?? '';
            $typ_pers = $tokoData[0]->TYP_PERS ?? '';
            $typ_npwp = $tokoData[0]->TYP_NPWP ?? '';
            $alamat_pers = $tokoData[0]->ALAMAT ?? '';
        }

        // Tentukan periode (bulin = bulan logic)
        $currentMonth = date('m');
        $requestMonth = date('m', strtotime($request->tglDr));
        $isSameMonth = ($currentMonth == $requestMonth);

        // Query FC Report berdasarkan kondisi bulan
        if ($isSameMonth) {
            // Query untuk bulan yang sama (menggunakan jualz dan jualzd)
            $fcReportQuery = "SELECT ageng.*, brgfc.TYPE, brgfc.STAND
                             FROM (
                                 SELECT jualzd.KD_BRG, jualzd.NA_BRG,
                                        SUM(jualzd.qty) as qtylaku,
                                        SUM(jualzd.total) as totlaku,
                                        ? as no_form,
                                        ? as na_toko,
                                        ? as typ_pers,
                                        ? as typ_npwp,
                                        ? as alamat_pers,
                                        SUM(IF(jualzd.disc > 0, jualzd.qty, 0)) as qtyob,
                                        SUM(IF(jualzd.disc > 0, jualzd.total, 0)) as totob,
                                        SUM(IF(jualzd.disc = 0, jualzd.qty, 0)) as qtynm,
                                        SUM(IF(jualzd.disc = 0, jualzd.total, 0)) as totnm,
                                        jualzd.tgl
                                 FROM {$cbgTable}jualz, {$cbgTable}jualzd
                                 WHERE jualz.no_bukti = jualzd.no_bukti
                                   AND jualzd.flag = 'FC'
                                   AND jualzd.tgl BETWEEN ? AND ?
                                 GROUP BY jualzd.tgl, jualzd.KD_BRG
                                 ORDER BY jualzd.tgl, brgfc.STAND, brgfc.TYPE
                             ) as ageng, {$cbgTable}brgfc
                             WHERE ageng.kd_brg = brgfc.kd_brg
                             ORDER BY ageng.tgl, brgfc.STAND, brgfc.TYPE";

            $queryFC = DB::select($fcReportQuery, [
                $no_form,
                $na_toko,
                $typ_pers,
                $typ_npwp,
                $alamat_pers,
                $tgl1,
                $tgl2
            ]);
        } else {
            // Query untuk bulan berbeda (menggunakan dynamic table jlzd)
            $jlzd = $cbgTable . 'jualzd' . date('Ym', strtotime($request->tglDr));

            $fcReportQuery = "SELECT ageng.*, brgfc.TYPE, brgfc.STAND
                             FROM (
                                 SELECT {$jlzd}.KD_BRG, {$jlzd}.NA_BRG,
                                        SUM({$jlzd}.qty) as qtylaku,
                                        SUM({$jlzd}.total) as totlaku,
                                        ? as no_form,
                                        ? as na_toko,
                                        ? as typ_pers,
                                        ? as typ_npwp,
                                        ? as alamat_pers,
                                        SUM(IF({$jlzd}.disc > 0, {$jlzd}.qty, 0)) as qtyob,
                                        SUM(IF({$jlzd}.disc > 0, {$jlzd}.total, 0)) as totob,
                                        SUM(IF({$jlzd}.disc = 0, {$jlzd}.qty, 0)) as qtynm,
                                        SUM(IF({$jlzd}.disc = 0, {$jlzd}.total, 0)) as totnm,
                                        {$jlzd}.tgl
                                 FROM {$jlzd}
                                 WHERE {$jlzd}.flag = 'FC'
                                   AND {$jlzd}.tgl BETWEEN ? AND ?
                                 GROUP BY {$jlzd}.tgl, {$jlzd}.KD_BRG
                                 ORDER BY {$jlzd}.tgl, brgfc.STAND, brgfc.TYPE
                             ) as ageng, {$cbgTable}brgfc
                             WHERE ageng.kd_brg = brgfc.kd_brg
                             ORDER BY ageng.tgl, brgfc.STAND, brgfc.TYPE";

            $queryFC = DB::select($fcReportQuery, [
                $no_form,
                $na_toko,
                $typ_pers,
                $typ_npwp,
                $alamat_pers,
                $tgl1,
                $tgl2
            ]);
        }

        // Menggunakan informasi periode dari request
        $peri = trim($request->cbcbg ?? ''); // periode dari cbcbg
        $bulan = date('Ym', strtotime($request->tglDr)); // bulan dari tanggal request

        // Query TR menggunakan tabel jual dinamis berdasarkan bulan
        $trReportQuery = "SELECT ? as no_form, ? as na_toko, ? as typ_pers, ? as typ_npwp, ? as alamat_pers,
                                 ? as tinggil, ? as periode, tgl,
                                 SUM(ppn * 10) as penjualan,
                                 SUM(ppn) as PB1
                          FROM {$cbgTable}jual{$bulan}
                          WHERE FLAG = 'FC'
                            AND per = ?
                          GROUP BY tgl
                          ORDER BY tgl";

        $queryTR = DB::select($trReportQuery, [
            $no_form,                                    // no_form
            $na_toko,                                    // na_toko
            $typ_pers,                                   // typ_pers
            $typ_npwp,                                   // typ_npwp
            $alamat_pers,                               // alamat_pers
            date('d/m/Y', strtotime($request->tglSmp)), // tinggil (tgl2 formatted)
            $peri,                                      // periode
            $peri                                       // periode for WHERE clause
        ]);

        return view('oreport_fc.report')->with([
            'cbg' => Cbg::groupBy('CBG')->get(),
            'hasilTR' => $queryTR,
            'hasilFC' => $queryFC,
            'no_form' => $no_form,
            'na_toko' => $na_toko,
            'typ_pers' => $typ_pers,
            'typ_npwp' => $typ_npwp,
            'alamat_pers' => $alamat_pers,
            'isSameMonth' => $isSameMonth,
            'selectedCbg' => $request->cbcbg,
            'tglDr' => $request->tglDr,
            'tglSmp' => $request->tglSmp,
        ]);

        // Untuk cetak report
        if ($request->has('cetak_tr')) {
            $query = $request->has('cetak_tr') ? $queryTR : $queryFC;
        } elseif ($request->has('cetak_fc')) {
            $query = $queryFC;
        }

        $data = [];

        // Jika ini adalah FC report, gunakan struktur data yang berbeda
        if ($request->has('cetak_fc')) {
            foreach ($query as $key => $value) {
                array_push($data, [
                    'KD_BRG' => $query[$key]->KD_BRG ?? '',
                    'NA_BRG' => $query[$key]->NA_BRG ?? '',
                    'TYPE' => $query[$key]->TYPE ?? '',
                    'STAND' => $query[$key]->STAND ?? '',
                    'TGL' => $query[$key]->tgl ?? '',
                    'QTYLAKU' => $query[$key]->qtylaku ?? 0,
                    'TOTLAKU' => $query[$key]->totlaku ?? 0,
                    'QTYOB' => $query[$key]->qtyob ?? 0,
                    'TOTOB' => $query[$key]->totob ?? 0,
                    'QTYNM' => $query[$key]->qtynm ?? 0,
                    'TOTNM' => $query[$key]->totnm ?? 0,
                    'NO_FORM' => $query[$key]->no_form ?? '',
                    'NA_TOKO' => $query[$key]->na_toko ?? '',
                    'TYP_PERS' => $query[$key]->typ_pers ?? '',
                    'TYP_NPWP' => $query[$key]->typ_npwp ?? '',
                    'ALAMAT_PERS' => $query[$key]->alamat_pers ?? '',
                ]);
            }
        } else {
            // Struktur data untuk TR report
            foreach ($query as $key => $value) {
                array_push($data, [
                    'NO_FORM' => $query[$key]->no_form ?? '',
                    'NA_TOKO' => $query[$key]->na_toko ?? '',
                    'TYP_PERS' => $query[$key]->typ_pers ?? '',
                    'TYP_NPWP' => $query[$key]->typ_npwp ?? '',
                    'ALAMAT_PERS' => $query[$key]->alamat_pers ?? '',
                    'TINGGIL' => $query[$key]->tinggil ?? '',
                    'PERIODE' => $query[$key]->periode ?? '',
                    'TGL' => $query[$key]->tgl ?? '',
                    'PENJUALAN' => $query[$key]->penjualan ?? 0,
                    'PB1' => $query[$key]->PB1 ?? 0,
                ]);
            }
        }

        $PHPJasperXML->transferDBtoArray("localhost", "root", "", "tiara");
        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I");
    }
}