<?php

namespace App\Http\Controllers\OReport;

use App\Http\Controllers\Controller;
use App\Models\Master\Cbg;
use App\Models\Master\Perid;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;

class RReturController extends Controller
{
    public function report()
    {
        $cbg = Cbg::groupBy('CBG')->get();
        session()->put('filter_cbg', '');
        session()->put('filter_tglDari', date("d-m-Y"));
        session()->put('filter_tglSampai', date("d-m-Y"));

        $per = Perid::query()->get();

        return view('oreport_retur.report')->with([
            'cbg' => $cbg,
            'per' => $per,
            'periode' => $per,
            'hasil' => [],
            'hasilSudahBayar' => [],
            'hasilBelumBayar' => [],
            'no_form' => '',
            'na_toko' => '',
            'typ_pers' => '',
            'alamat_pers' => '',
            'toko' => '',
            'selectedCbg' => '',
            'selectedPeriode' => '',
            'selectedSuplier' => '',
            'tglDr' => date('Y-m-d'),
            'tglSmp' => date('Y-m-d'),
            'tipe' => '1', // Default to Retur Sudah Bayar
        ]);
    }

    public function jasperReturReport(Request $request)
    {
        // Set session variables
        session()->put('filter_cbg', $request->cbcbg);
        session()->put('filter_tglDari', $request->tglDr);
        session()->put('filter_tglSampai', $request->tglSmp);

        $tipe = $request->tipe ?? '1'; // Default to type 1 (Retur Sudah Bayar)
        $suplier = $request->suplier ?? ''; // DC or non-DC

        // DC Supplier filter logic from Delphi
        $filterDCsup = '';
        if (strtoupper($suplier) === 'DC') {
            $filterDCsup = ' AND beliz.KODES IN ("510C","510D","510E","510F","510G") ';
        } else {
            $filterDCsup = ' AND beliz.KODES NOT IN ("510C","510D","510E","510F","510G") ';
        }

        // Get store name (co.SQL from Delphi)
        $tokoQuery = "SELECT NA_TOKO FROM toko WHERE KODE = ?";
        $tokoData = DB::select($tokoQuery, [$request->cbcbg]);
        $toko = !empty($tokoData) ? $tokoData[0]->NA_TOKO : '';

        $no_form = '';
        $na_toko = '';
        $typ_pers = '';
        $alamat_pers = '';
        $hasil = [];

        if ($tipe == '1') {
            // RETUR SUDAH BAYAR (tipe=1)

            // Get form information for BELI-1
            $formQuery = "SELECT toko.KODE, toko.TYP, toko.NA_TOKO, toko.NA_PERS, toko.TYP_PERS, toko.ALAMAT, tokoform.NO_BUKTI
                         FROM toko, tokoform
                         WHERE toko.TYP = tokoform.TYP
                               AND toko.KODE = ?
                               AND tokoform.KD_PRNT = ?";

            $formData = DB::select($formQuery, [$request->cbcbg, 'BELI-1']);

            if (!empty($formData)) {
                $no_form = $formData[0]->NO_BUKTI ?? '';
                $na_toko = $formData[0]->NA_TOKO ?? '';
                $typ_pers = $formData[0]->TYP_PERS ?? '';
                $alamat_pers = $formData[0]->ALAMAT ?? '';
            }

            // Get all active stores
            $storesQuery = "SELECT kode FROM toko WHERE sta <> 'NS'";
            $stores = DB::select($storesQuery);

            $unionQueries = [];

            // Build UNION query for all stores
            foreach ($stores as $index => $store) {
                $storeCode = $store->kode;

                $query = "SELECT ? as no_form, ? as na_toko, ? as typ_pers, ? as nmtoko,
                                beliz.NO_BUKTI, DATE(beliz.tgl) as tgl, beliz.NO_tagi,
                                beliz.KODES, beliz.NAMAS, beliz.total as bruto,
                                beliz.PROM, beliz.ppn, beliz.total-beliz.prom as DPP,
                                beliz.nett,
                                CASE
                                    WHEN beliz.TYPE='FO' THEN sup.FO_KLB
                                    WHEN beliz.TYPE='NF' THEN sup.NF_KLB
                                    WHEN beliz.TYPE='PB' THEN sup.PB_KLB
                                    WHEN beliz.TYPE='ST' THEN sup.ST_KLB
                                    WHEN beliz.TYPE='FF' THEN sup.FF_KLB
                                    WHEN sup.FO_KLB<>0 THEN sup.FO_KLB
                                    WHEN sup.FF_KLB<>0 THEN sup.FF_KLB
                                    WHEN sup.NF_KLB<>0 THEN sup.NF_KLB
                                    WHEN sup.ST_KLB<>0 THEN sup.ST_KLB
                                    WHEN sup.PB_KLB<>0 THEN sup.PB_KLB
                                    ELSE 0
                                END AS KLB
                         FROM {$storeCode}.beliz, {$storeCode}.sup
                         WHERE beliz.no_bukti = beliz.no_bukti
                               AND beliz.KODES = sup.KODES
                               AND beliz.flag = 'RB'
                               AND beliz.CBG = ?
                               AND sup.KODES <> 'AAA'
                               {$filterDCsup}
                               AND beliz.no_tagi <> ''
                               AND DATE(beliz.tgl) >= ?
                               AND DATE(beliz.tgl) <= ?";

                $unionQueries[] = $query;
            }

            // Combine all queries with UNION ALL
            $fullQuery = implode(' UNION ALL ', $unionQueries) . ' ORDER BY NO_BUKTI ASC';

            // Prepare parameters for all queries
            $params = [];
            foreach ($stores as $store) {
                $params = array_merge($params, [
                    $no_form,
                    $na_toko,
                    $typ_pers,
                    $toko,
                    $request->cbcbg,
                    $request->tglDr ?? date('Y-m-d'),
                    $request->tglSmp ?? date('Y-m-d')
                ]);
            }

            $hasil = DB::select($fullQuery, $params);
        } elseif ($tipe == '6') {
            // RETUR BELUM BAYAR (tipe=6)

            // Get form information for RBLMPOT
            $formQuery = "SELECT toko.KODE, toko.TYP, toko.NA_TOKO, toko.NA_PERS, toko.TYP_PERS, toko.ALAMAT, tokoform.NO_BUKTI
                         FROM toko, tokoform
                         WHERE toko.TYP = tokoform.TYP
                               AND toko.KODE = ?
                               AND tokoform.KD_PRNT = ?";

            $formData = DB::select($formQuery, [$request->cbcbg, 'RBLMPOT']);

            if (!empty($formData)) {
                $no_form = $formData[0]->NO_BUKTI ?? '';
                $na_toko = $formData[0]->NA_TOKO ?? '';
                $typ_pers = $formData[0]->TYP_PERS ?? '';
                $alamat_pers = $formData[0]->ALAMAT ?? '';
            }

            // Complex query for belum bayar (unpaid returns)
            $returQuery = "SELECT *,
                                 CASE
                                     WHEN NETTNOTA = 0 THEN '  TIDAK ADA NOTA'
                                     WHEN NETTNOTA < NETTRETUR THEN '  NILAI NOTA KURANG DARI NILAI RETUR'
                                     ELSE ''
                                 END as KETXX
                          FROM (
                              SELECT ? as no_form, ? as na_toko, ? as typ_pers, ? as nmtoko,
                                     beliz.NO_BUKTI, DATE(beliz.tgl) as tgl, beliz.NO_tagi,
                                     beliz.KODES, beliz.NAMAS, beliz.total as bruto,
                                     beliz.PROM, beliz.ppn, beliz.total-beliz.prom as DPP,
                                     beliz.nett,
                                     CASE
                                         WHEN beliz.TYPE='FO' THEN sup.FO_KLB
                                         WHEN beliz.TYPE='NF' THEN sup.NF_KLB
                                         WHEN beliz.TYPE='PB' THEN sup.PB_KLB
                                         WHEN beliz.TYPE='ST' THEN sup.ST_KLB
                                         WHEN beliz.TYPE='FF' THEN sup.FF_KLB
                                         WHEN sup.FO_KLB<>0 THEN sup.FO_KLB
                                         WHEN sup.FF_KLB<>0 THEN sup.FF_KLB
                                         WHEN sup.NF_KLB<>0 THEN sup.NF_KLB
                                         WHEN sup.ST_KLB<>0 THEN sup.ST_KLB
                                         WHEN sup.PB_KLB<>0 THEN sup.PB_KLB
                                         ELSE 0
                                     END AS KLB,
                                     COALESCE((SELECT SUM(nett) FROM {$request->cbcbg}.beliz aa
                                              WHERE aa.KODES = beliz.KODES
                                                    AND aa.NO_TAGI = ''
                                                    AND aa.FLAG = 'BL'), 0) as NETTNOTA,
                                     COALESCE((SELECT SUM(nett) FROM {$request->cbcbg}.beliz aa
                                              WHERE aa.KODES = beliz.KODES
                                                    AND aa.NO_TAGI = ''
                                                    AND aa.FLAG = 'RB'), 0) as NETTRETUR
                              FROM {$request->cbcbg}.beliz, {$request->cbcbg}.sup
                              WHERE beliz.no_bukti = beliz.no_bukti
                                    AND beliz.KODES = sup.KODES
                                    AND beliz.flag = 'RB'
                                    AND beliz.CBG = ?
                                    AND sup.KODES <> 'AAA'
                                    {$filterDCsup}
                                    AND beliz.no_tagi = ''
                          ) as rekapbelumpotong
                          WHERE NETTRETUR > NETTNOTA
                          ORDER BY KODES, NO_BUKTI";

            $hasil = DB::select($returQuery, [
                $no_form,
                $na_toko,
                $typ_pers,
                $toko,
                $request->cbcbg
            ]);
        }

        // Prepare data array
        $data = [
            'hasil' => $hasil,
            'hasilSudahBayar' => $tipe == '1' ? $hasil : [],
            'hasilBelumBayar' => $tipe == '6' ? $hasil : [],
            'na_toko' => $na_toko,
            'no_form' => $no_form,
            'typ_pers' => $typ_pers,
            'alamat_pers' => $alamat_pers,
            'toko' => $toko,
            'cbg' => Cbg::groupBy('CBG')->get(),
            'periode' => Perid::query()->get(),
            'per' => Perid::query()->get(),
            'selectedCbg' => $request->cbcbg,
            'selectedPeriode' => $request->periode,
            'selectedSuplier' => $suplier,
            'tglDr' => $request->tglDr ?? date('Y-m-d'),
            'tglSmp' => $request->tglSmp ?? date('Y-m-d'),
            'tipe' => $tipe,
        ];

        // Check if this is a filter request (for DataTable)
        if ($request->has('filter') && !$request->has('cetak')) {
            return view('oreport_retur.report')->with($data);
        }

        // Handle Jasper report generation
        $file = $tipe == '1' ? 'retur_sudah_bayar' : 'retur_belum_bayar';
        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

        // Handle report generation
        if (!empty($hasil)) {
            $reportTitle = $tipe == '1' ? 'Laporan Retur Sudah Bayar' : 'Laporan Retur Belum Bayar';

            $PHPJasperXML->arrayParameter = array(
                "myTitle" => $reportTitle,
                "mySubTitle" => "Periode: " . ($request->tglDr ?? date('Y-m-d')) . " s/d " . ($request->tglSmp ?? date('Y-m-d')),
                "myHeader" => "Toko: " . $na_toko,
                "suplierType" => $suplier == 'DC' ? 'DC Supplier' : 'Non-DC Supplier'
            );

            $PHPJasperXML->arrayParameter["results"] = $hasil;
            ob_end_clean();
            $PHPJasperXML->outpage("I");
        } else {
            return redirect()->back()->with('error', 'Tidak ada data untuk periode yang dipilih');
        }
    }
}
