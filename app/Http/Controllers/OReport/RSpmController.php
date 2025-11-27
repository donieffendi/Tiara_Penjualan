<?php

namespace App\Http\Controllers\OReport;

use App\Http\Controllers\Controller;
use App\Models\Master\Cbg;
use App\Models\Master\Perid;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;

class RSpmController extends Controller
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

        // Data supplier untuk dropdown
        $supplier = collect([
            (object) ['KODE_SUP' => 'SPM', 'NAMA_SUP' => 'SPM (Standard)'],
            (object) ['KODE_SUP' => 'DC', 'NAMA_SUP' => 'DC (Distribution Center)'],
            (object) ['KODE_SUP' => 'SPM & TS', 'NAMA_SUP' => 'SPM & TS (SPM dan Trading System)'],
            (object) ['KODE_SUP' => 'TS', 'NAMA_SUP' => 'TS (Trading System)']
        ]);

        return view('oreport_spm.report')->with([
            'cbg' => $cbg,
            'supplier' => $supplier,
            'periode' => $per,
            'hasil' => [],
            'hasilPeriode' => [],
            'hasilSubBeli' => [],
            'hasilReportRetur' => [],
            'hasilSubRetur' => [],
            'hasilSubHut' => [],
            'hasilTransaksiLain' => [],
            'per' => $per,
            'no_form' => '',
            'na_toko' => '',
            'typ_pers' => '',
            'typ_npwp' => '',
            'alamat_pers' => '',
            'kode_toko' => '',
            'selectedSupplier' => 'SPM',
        ]);
    }

    public function jasperSpmReport(Request $request)
    {
        $file = 'spmreport';
        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

        $filtertgl = '';
        $filtersup = '';
        $flagbl = '';

        if (!empty($request->tglDr) && !empty($request->tglSmp)) {
            $tgl1 = date("Y-m-d", strtotime($request->tglDr));
            $tgl2 = date("Y-m-d", strtotime($request->tglSmp));
            $filtertgl = " TGL BETWEEN '$tgl1' AND '$tgl2' ";
        }

        // Filter supplier berdasarkan jenis
        $supplierType = strtoupper($request->supplier ?? 'SPM');

        if ($supplierType === 'DC') {
            $filtersup = ' AND beliz.KODES IN ("510C","510D","510E","510F","510G") ';
            $flagbl = ' AND beliz.FLAG="BL" ';
        } elseif ($supplierType === 'SPM & TS') {
            $filtersup = ' AND beliz.KODES NOT IN ("510C","510D","510E","510F","510G") ';
            $flagbl = ' AND beliz.FLAG IN ("BL","B3","B5","B8") ';
        } elseif ($supplierType === 'TS') {
            $filtersup = ' AND beliz.KODES IN ("542","542A","542B","1619","1729","3631","F1330","F1642","F1866") ';
            $flagbl = ' AND beliz.FLAG IN ("B3","B5") ';
        } else {
            $filtersup = ' AND beliz.KODES NOT IN ("510C","510D","510E","510F","510G","542","542A","542B","1619","1729","3631","F1330","F1642","F1866") ';
            $flagbl = ' AND beliz.FLAG IN ("BL","B8") ';
        }

        session()->put('filter_cbg', $request->cbcbg);

        $cbgTable = '';
        if (!empty($request->cbcbg)) {
            $cbgTable = $request->cbcbg . '.';
        }

        // Query untuk mendapatkan informasi toko dan form
        $tokoQuery = "SELECT toko.KODE, toko.TYP, toko.NA_TOKO, toko.NA_PERS, toko.TYP_PERS, toko.TYP_NPWP, toko.ALAMAT, tokoform.NO_BUKTI,
                             RIGHT(toko.FOLDER_DCTS,2) as KODE_TOKO
                     FROM {$cbgTable}toko, {$cbgTable}tokoform
                     WHERE toko.TYP = tokoform.TYP AND toko.KODE = ? AND tokoform.KD_PRNT = ?";

        $tokoData = DB::select($tokoQuery, [$request->cbcbg, 'BELI-1']);

        $no_form = '';
        $na_toko = '';
        $typ_pers = '';
        $typ_npwp = '';
        $alamat_pers = '';
        $kode_toko = '';

        if (!empty($tokoData)) {
            $no_form = $tokoData[0]->NO_BUKTI ?? '';
            $na_toko = $tokoData[0]->NA_TOKO ?? '';
            $typ_pers = $tokoData[0]->TYP_PERS ?? '';
            $typ_npwp = $tokoData[0]->TYP_NPWP ?? '';
            $alamat_pers = $tokoData[0]->ALAMAT ?? '';
            $kode_toko = $tokoData[0]->KODE_TOKO ?? '';
        }

        // Periode dari request
        $periode = trim($request->cbcbg ?? '');

        $hasil = collect();
        $hasilPeriode = collect();
        $hasilSubBeli = collect();
        $hasilReportRetur = collect();
        $hasilSubRetur = collect();
        $hasilSubHut = collect();
        $hasilTransaksiLain = collect();

        // Query untuk Periode Tab
        $periodeQuery = "SELECT beliz.NO_BUKTI, DATE(beliz.tgl) as TGL, beliz.NO_PO as NO_REF,
                                beliz.KODES, beliz.NAMAS,
                                CASE
                                    WHEN beliz.TYPE='FO' THEN sup.FO_KLB
                                    WHEN beliz.TYPE='NF' THEN sup.NF_KLB
                                    WHEN beliz.TYPE='PB' THEN sup.PB_KLB
                                    WHEN beliz.TYPE='ST' THEN sup.ST_KLB
                                    WHEN beliz.TYPE='FF' THEN sup.FF_KLB
                                    ELSE COALESCE(sup.FO_KLB, sup.FF_KLB, sup.NF_KLB, sup.ST_KLB, sup.PB_KLB, 0)
                                END AS KLB,
                                beliz.total as bruto, beliz.PROM, beliz.PPN
                        FROM {$cbgTable}beliz, {$cbgTable}sup
                        WHERE beliz.KODES = sup.KODES
                              AND beliz.PER = ? AND beliz.CBG = ?
                              AND beliz.tgl BETWEEN ? AND ?
                              AND beliz.no_tagi <> 'BATAL'
                              {$filtersup} {$flagbl}
                        ORDER BY beliz.TGL, beliz.NO_BUKTI";

        $hasilPeriode = DB::select($periodeQuery, [
            $periode,
            $request->cbcbg,
            $tgl1,
            $tgl2
        ]);

        // Query untuk Sub Pembelian (tipe 2)
        $subBeliQuery = "SELECT brg.sub, aotprice.KELOMPOK,
                                SUM(belizd.total) as bruto,
                                SUM(IF(rec=1,beliz.PROM,0)) as prom
                        FROM {$cbgTable}beliz, {$cbgTable}belizd
                        LEFT JOIN {$cbgTable}brg ON belizd.KD_BRG = brg.KD_BRG
                        LEFT JOIN {$cbgTable}aotprice ON brg.sub = aotprice.SUB
                        WHERE belizd.no_bukti = beliz.no_bukti
                              AND beliz.PER = ? AND beliz.CBG = ?
                              AND DATE(beliz.tgl_posted) BETWEEN ? AND ?
                              AND beliz.no_tagi <> 'BATAL'
                              {$filtersup} {$flagbl}
                        GROUP BY brg.sub
                        ORDER BY brg.sub";

        $hasilSubBeli = DB::select($subBeliQuery, [
            $periode,
            $request->cbcbg,
            $tgl1,
            $tgl2
        ]);

        // Query untuk Report Retur (tipe 6)
        $reportReturQuery = "SELECT beliz.NO_BUKTI, DATE(beliz.tgl) as tgl, beliz.NO_PO, beliz.KODES, beliz.NAMAS,
                                   beliz.total as bruto, beliz.PROM, beliz.PPN,
                                   CASE
                                       WHEN beliz.TYPE='FO' THEN sup.FO_KLB
                                       WHEN beliz.TYPE='NF' THEN sup.NF_KLB
                                       WHEN beliz.TYPE='PB' THEN sup.PB_KLB
                                       WHEN beliz.TYPE='ST' THEN sup.ST_KLB
                                       WHEN beliz.TYPE='FF' THEN sup.FF_KLB
                                       ELSE COALESCE(sup.FO_KLB, sup.FF_KLB, sup.NF_KLB, sup.ST_KLB, sup.PB_KLB, 0)
                                   END AS KLB
                            FROM {$cbgTable}beliz, {$cbgTable}sup
                            WHERE beliz.KODES = sup.KODES
                                  AND beliz.flag = 'RB' AND beliz.CBG = ?
                                  AND beliz.PER = ? AND DATE(beliz.tgl) BETWEEN ? AND ?
                                  AND beliz.no_tagi <> 'BATAL'
                                  {$filtersup}
                            ORDER BY beliz.NO_BUKTI ASC";

        $hasilReportRetur = DB::select($reportReturQuery, [
            $request->cbcbg,
            $periode,
            $tgl1,
            $tgl2
        ]);

        // Query untuk Sub Retur (tipe 3)
        $subReturQuery = "SELECT brg.sub, belizd.KELOMPOK,
                                 SUM(belizd.total) as bruto,
                                 SUM(IF(rec=1,beliz.PROM,0)) as prom
                         FROM {$cbgTable}beliz, {$cbgTable}belizd
                         LEFT JOIN {$cbgTable}brg ON brg.KD_BRG = belizd.KD_BRG
                         WHERE belizd.no_bukti = beliz.no_bukti
                               AND beliz.flag = 'RB' AND beliz.PER = ? AND beliz.CBG = ?
                               AND DATE(beliz.tgl) BETWEEN ? AND ?
                               AND beliz.no_tagi <> 'BATAL'
                               {$filtersup}
                         GROUP BY brg.sub
                         ORDER BY brg.sub";

        $hasilSubRetur = DB::select($subReturQuery, [
            $periode,
            $request->cbcbg,
            $tgl1,
            $tgl2
        ]);

        // Query untuk Sub Hutang (tipe 4)
        $subHutQuery = "SELECT brg.sub, aotprice.KELOMPOK,
                               SUM(belizd.total) as total,
                               SUM(IF(belizd.rec=1,beliz.PPN,0)) as ppn,
                               SUM(IF(belizd.rec=1,beliz.NETT,0)) as nett,
                               SUM(IF(belizd.rec=1,beliz.prom,0)) as prom
                       FROM {$cbgTable}beliz, {$cbgTable}belizd, {$cbgTable}brg, {$cbgTable}aotprice
                       WHERE beliz.flag = 'TH' AND beliz.PER = ?
                             AND beliz.tgl BETWEEN ? AND ?
                             AND beliz.no_bukti = belizd.no_bukti
                             AND belizd.KD_BRG = brg.KD_BRG
                             AND brg.sub = aotprice.SUB
                             {$filtersup}
                       GROUP BY brg.sub
                       ORDER BY brg.sub";

        $hasilSubHut = DB::select($subHutQuery, [
            $periode,
            $tgl1,
            $tgl2
        ]);

        // Query untuk Transaksi Lain-lain
        $transaksiLainQuery = "SELECT beliz.NO_BUKTI, beliz.TGL, belizd.nacc, beliz.KODES, beliz.cbg,
                                      UPPER(belizd.ket) AS KET,
                                      IF(belizd.total>0,belizd.total,0) as Debet,
                                      IF(belizd.total<0,belizd.total*-1,0) as Kredit
                              FROM {$cbgTable}beliz, {$cbgTable}belizd
                              WHERE belizd.no_bukti = beliz.no_bukti
                                    AND beliz.flag = 'TL'
                                    AND beliz.PER = ? AND beliz.tgl BETWEEN ? AND ?
                                    {$filtersup}
                              ORDER BY beliz.tgl, beliz.no_bukti";

        $hasilTransaksiLain = DB::select($transaksiLainQuery, [
            $periode,
            $tgl1,
            $tgl2
        ]);

        // Data supplier untuk dropdown
        $supplier = collect([
            (object) ['KODE_SUP' => 'SPM', 'NAMA_SUP' => 'SPM (Standard)'],
            (object) ['KODE_SUP' => 'DC', 'NAMA_SUP' => 'DC (Distribution Center)'],
            (object) ['KODE_SUP' => 'SPM & TS', 'NAMA_SUP' => 'SPM & TS (SPM dan Trading System)'],
            (object) ['KODE_SUP' => 'TS', 'NAMA_SUP' => 'TS (Trading System)']
        ]);

        $per = Perid::query()->get();

        return view('oreport_spm.report')->with([
            'cbg' => Cbg::groupBy('CBG')->get(),
            'supplier' => $supplier,
            'periode' => $per,
            'hasil' => $hasilPeriode, // Default untuk tab pertama
            'hasilPeriode' => $hasilPeriode,
            'hasilSubBeli' => $hasilSubBeli,
            'hasilReportRetur' => $hasilReportRetur,
            'hasilSubRetur' => $hasilSubRetur,
            'hasilSubHut' => $hasilSubHut,
            'hasilTransaksiLain' => $hasilTransaksiLain,
            'per' => $per,
            'no_form' => $no_form,
            'na_toko' => $na_toko,
            'typ_pers' => $typ_pers,
            'typ_npwp' => $typ_npwp,
            'alamat_pers' => $alamat_pers,
            'kode_toko' => $kode_toko,
            'selectedCbg' => $request->cbcbg,
            'selectedSupplier' => $supplierType,
            'selectedPeriode' => $periode,
            'tglDr' => $request->tglDr,
            'tglSmp' => $request->tglSmp,
        ]);
    }
}
