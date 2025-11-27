<?php

namespace App\Http\Controllers\OReport;

use App\Http\Controllers\Controller;
use App\Models\Master\Cbg;
use App\Models\Master\Perid;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;

class RRCNReturController extends Controller
{
    public function report()
    {
        $cbg = Cbg::groupBy('CBG')->get();
        session()->put('filter_cbg', '');
        session()->put('filter_tglDari', date("d-m-Y"));
        session()->put('filter_tglSampai', date("d-m-Y"));

        $per = Perid::query()->get();

        return view('oreport_RCNRetur.report')->with([
            'cbg' => $cbg,
            'per' => $per,
            'periode' => $per,
            'hasilDataTable1' => [],
            'hasilDataTable2' => [],
            'hasilDataTable3' => [],
            'hasilDataTable4' => [],
            'no_form' => '',
            'na_toko' => '',
            'typ_pers' => '',
            'alamat_pers' => '',
            'toko' => '',
            'selectedCbg' => '',
            'selectedPeriode' => '',
            'tglDr' => date('Y-m-d'),
            'tglSmp' => date('Y-m-d'),
        ]);
    }

    public function jasperRCNReturReport(Request $request)
    {
        // Set session variables
        session()->put('filter_cbg', $request->cbcbg);
        session()->put('filter_tglDari', $request->tglDr);
        session()->put('filter_tglSampai', $request->tglSmp);

        $cbg = $request->cbcbg ?? '';
        $periode = $request->periode ?? '';
        $tglDr = $request->tglDr ?? date('Y-m-d');
        $tglSmp = $request->tglSmp ?? date('Y-m-d');

        $hasilDataTable1 = [];
        $hasilDataTable2 = [];

        // Get toko information first
        $na_toko = '';
        $toko = '';
        $typ_pers = '';
        $alamat_pers = '';
        $no_form = '';

        // Get store name
        if (!empty($cbg)) {
            $tokoQuery = "SELECT NA_TOKO FROM toko WHERE KODE = ?";
            $tokoData = DB::select($tokoQuery, [$cbg]);
            if (!empty($tokoData)) {
                $toko = $tokoData[0]->NA_TOKO;
            }
        }

        // DataTable 1: Return Planning (tipe=1) - RENCANA RETUR
        if (!empty($cbg)) {
            try {
                // Get form information for RETUR-1
                $formQuery = "SELECT toko.KODE, toko.TYP, toko.NA_TOKO, toko.NA_PERS, toko.TYP_PERS, toko.ALAMAT, tokoform.NO_BUKTI
                              FROM toko, tokoform
                              WHERE toko.TYP = tokoform.TYP
                              AND toko.KODE = ?
                              AND tokoform.KD_PRNT = 'RETUR-1'";
                $formData = DB::select($formQuery, [$cbg]);

                if (!empty($formData)) {
                    $no_form = $formData[0]->NO_BUKTI ?? '';
                    $na_toko = $formData[0]->NA_TOKO ?? '';
                    $typ_pers = $formData[0]->TYP_PERS ?? '';
                    $alamat_pers = $formData[0]->ALAMAT ?? '';
                }

                // Get list of non-NS stores
                $storesQuery = "SELECT kode FROM toko WHERE sta <> 'NS'";
                $stores = DB::select($storesQuery);

                $unionQueries = [];
                $params = [];

                foreach ($stores as $index => $store) {
                    $storeCode = $store->kode;

                    if ($index === 0) {
                        $unionQueries[] = "SELECT ? as no_form, ? as na_toko, ? as typ_pers, ? as nmtoko,
                                          a.NO_BUKTI, a.bktk, DATE(a.tgl) as tgl, a.NO_tagi, a.KODES, a.NAMAS,
                                          a.total as bruto, a.PROM, a.ppn, a.total - a.prom as DPP, a.nett,
                                          IF(a.TYPE = 'FO', b.FO_KLB,
                                             IF(a.TYPE = 'NF', b.NF_KLB,
                                                IF(a.TYPE = 'PB', b.PB_KLB,
                                                   IF(a.TYPE = 'ST', b.ST_KLB,
                                                      IF(a.TYPE = 'FF', b.FF_KLB,
                                                         IF(b.FO_KLB <> 0, FO_KLB,
                                                            IF(FF_KLB <> 0, FF_KLB,
                                                               IF(NF_KLB <> 0, NF_KLB,
                                                                  IF(ST_KLB <> 0, ST_KLB,
                                                                     IF(PB_KLB <> 0, PB_KLB, 0)
                                                                  )
                                                               )
                                                            )
                                                         )
                                                      )
                                                   )
                                                )
                                             )
                                          ) AS KLB
                                          FROM {$storeCode}.Retur a, {$storeCode}.sup b
                                          WHERE a.no_bukti = a.no_bukti
                                          AND a.KODES = b.KODES
                                          AND a.flag = 'RR'
                                          AND a.CBG = ?
                                          AND a.PER = ?
                                          AND a.bktk = ''
                                          AND DATE(a.tgl) >= ?
                                          AND DATE(a.tgl) <= ?";
                    } else {
                        $unionQueries[] = "UNION ALL SELECT ? as no_form, ? as na_toko, ? as typ_pers, ? as nmtoko,
                                          a.NO_BUKTI, a.bktk, DATE(a.tgl) as tgl, a.NO_tagi, a.KODES, a.NAMAS,
                                          a.total as bruto, a.PROM, a.ppn, a.total - a.prom as DPP, a.nett,
                                          IF(a.TYPE = 'FO', b.FO_KLB,
                                             IF(a.TYPE = 'NF', b.NF_KLB,
                                                IF(a.TYPE = 'PB', b.PB_KLB,
                                                   IF(a.TYPE = 'ST', b.ST_KLB,
                                                      IF(a.TYPE = 'FF', b.FF_KLB,
                                                         IF(b.FO_KLB <> 0, FO_KLB,
                                                            IF(FF_KLB <> 0, FF_KLB,
                                                               IF(NF_KLB <> 0, NF_KLB,
                                                                  IF(ST_KLB <> 0, ST_KLB,
                                                                     IF(PB_KLB <> 0, PB_KLB, 0)
                                                                  )
                                                               )
                                                            )
                                                         )
                                                      )
                                                   )
                                                )
                                             )
                                          ) AS KLB
                                          FROM {$storeCode}.Retur a, {$storeCode}.sup b
                                          WHERE a.no_bukti = a.no_bukti
                                          AND a.KODES = b.KODES
                                          AND a.flag = 'RR'
                                          AND a.CBG = ?
                                          AND a.PER = ?
                                          AND a.bktk = ''
                                          AND DATE(a.tgl) >= ?
                                          AND DATE(a.tgl) <= ?";
                    }

                    // Add parameters for each query
                    $params = array_merge($params, [
                        $no_form,
                        $na_toko,
                        $typ_pers,
                        $toko,
                        $cbg,
                        $periode,
                        $tglDr,
                        $tglSmp
                    ]);
                }

                $finalQuery = implode(' ', $unionQueries) . " ORDER BY NO_BUKTI ASC";
                $hasilDataTable1 = DB::select($finalQuery, $params);
            } catch (\Exception $e) {
                $hasilDataTable1 = [];
                Log::error('Error in DataTable 1 (Return Planning) query: ' . $e->getMessage());
            }
        }

        // DataTable 2: Return without payment (tipe=6) - RETUR BELUM ADA BAYAR
        if (!empty($cbg)) {
            try {
                // Get form information for RETUR-6
                $formQuery6 = "SELECT toko.KODE, toko.TYP, toko.NA_TOKO, toko.NA_PERS, toko.TYP_PERS, toko.ALAMAT, tokoform.NO_BUKTI
                               FROM toko, tokoform
                               WHERE toko.TYP = tokoform.TYP
                               AND toko.KODE = ?
                               AND tokoform.KD_PRNT = 'RETUR-6'";
                $formData6 = DB::select($formQuery6, [$cbg]);

                $no_form_6 = '';
                $na_toko_6 = '';
                $typ_pers_6 = '';
                $alamat_pers_6 = '';

                if (!empty($formData6)) {
                    $no_form_6 = $formData6[0]->NO_BUKTI ?? '';
                    $na_toko_6 = $formData6[0]->NA_TOKO ?? '';
                    $typ_pers_6 = $formData6[0]->TYP_PERS ?? '';
                    $alamat_pers_6 = $formData6[0]->ALAMAT ?? '';
                }

                // Get list of non-NS stores
                $storesQuery = "SELECT kode FROM toko WHERE sta <> 'NS'";
                $stores = DB::select($storesQuery);

                $unionQueries = [];
                $params = [];

                foreach ($stores as $index => $store) {
                    $storeCode = $store->kode;

                    if ($index === 0) {
                        $unionQueries[] = "SELECT ? as no_form, ? as na_toko, ? as typ_pers, ? as nmtoko,
                                          a.NO_BUKTI, a.bktk, DATE(a.tgl) as tgl, a.NO_tagi, a.KODES, a.NAMAS,
                                          a.total as bruto, a.PROM, a.ppn, a.total - a.prom as DPP, a.nett,
                                          IF(a.TYPE = 'FO', b.FO_KLB,
                                             IF(a.TYPE = 'NF', b.NF_KLB,
                                                IF(a.TYPE = 'PB', b.PB_KLB,
                                                   IF(a.TYPE = 'ST', b.ST_KLB,
                                                      IF(a.TYPE = 'FF', b.FF_KLB,
                                                         IF(b.FO_KLB <> 0, FO_KLB,
                                                            IF(FF_KLB <> 0, FF_KLB,
                                                               IF(NF_KLB <> 0, NF_KLB,
                                                                  IF(ST_KLB <> 0, ST_KLB,
                                                                     IF(PB_KLB <> 0, PB_KLB, 0)
                                                                  )
                                                               )
                                                            )
                                                         )
                                                      )
                                                   )
                                                )
                                             )
                                          ) AS KLB
                                          FROM {$storeCode}.Retur a, {$storeCode}.sup b
                                          WHERE a.no_bukti = a.no_bukti
                                          AND a.KODES = b.KODES
                                          AND a.flag = 'RR'
                                          AND a.CBG = ?
                                          AND a.PER = ?
                                          AND a.bktk <> ''
                                          AND DATE(a.tgl) >= ?
                                          AND DATE(a.tgl) <= ?";
                    } else {
                        $unionQueries[] = "UNION ALL SELECT ? as no_form, ? as na_toko, ? as typ_pers, ? as nmtoko,
                                          a.NO_BUKTI, a.bktk, DATE(a.tgl) as tgl, a.NO_tagi, a.KODES, a.NAMAS,
                                          a.total as bruto, a.PROM, a.ppn, a.total - a.prom as DPP, a.nett,
                                          IF(a.TYPE = 'FO', b.FO_KLB,
                                             IF(a.TYPE = 'NF', b.NF_KLB,
                                                IF(a.TYPE = 'PB', b.PB_KLB,
                                                   IF(a.TYPE = 'ST', b.ST_KLB,
                                                      IF(a.TYPE = 'FF', b.FF_KLB,
                                                         IF(b.FO_KLB <> 0, FO_KLB,
                                                            IF(FF_KLB <> 0, FF_KLB,
                                                               IF(NF_KLB <> 0, NF_KLB,
                                                                  IF(ST_KLB <> 0, ST_KLB,
                                                                     IF(PB_KLB <> 0, PB_KLB, 0)
                                                                  )
                                                               )
                                                            )
                                                         )
                                                      )
                                                   )
                                                )
                                             )
                                          ) AS KLB
                                          FROM {$storeCode}.Retur a, {$storeCode}.sup b
                                          WHERE a.no_bukti = a.no_bukti
                                          AND a.KODES = b.KODES
                                          AND a.flag = 'RR'
                                          AND a.CBG = ?
                                          AND a.PER = ?
                                          AND a.bktk <> ''
                                          AND DATE(a.tgl) >= ?
                                          AND DATE(a.tgl) <= ?";
                    }

                    // Add parameters for each query
                    $params = array_merge($params, [
                        $no_form_6,
                        $na_toko_6,
                        $typ_pers_6,
                        $toko,
                        $cbg,
                        $periode,
                        $tglDr,
                        $tglSmp
                    ]);
                }

                $finalQuery = implode(' ', $unionQueries) . " ORDER BY NO_BUKTI ASC";
                $hasilDataTable2 = DB::select($finalQuery, $params);
            } catch (\Exception $e) {
                $hasilDataTable2 = [];
                Log::error('Error in DataTable 2 (Return without payment) query: ' . $e->getMessage());
            }
        }

        // Prepare data array
        $data = [
            'hasilDataTable1' => $hasilDataTable1,
            'hasilDataTable2' => $hasilDataTable2,
            'hasilDataTable3' => [],
            'hasilDataTable4' => [],
            'na_toko' => $na_toko,
            'no_form' => $no_form,
            'typ_pers' => $typ_pers,
            'alamat_pers' => $alamat_pers,
            'toko' => $toko,
            'cbg' => Cbg::groupBy('CBG')->get(),
            'periode' => Perid::query()->get(),
            'per' => Perid::query()->get(),
            'selectedCbg' => $cbg,
            'selectedPeriode' => $periode,
            'tglDr' => $tglDr,
            'tglSmp' => $tglSmp,
        ];

        // Check if this is a filter request (for DataTable)
        if ($request->has('filter') && !$request->has('cetak')) {
            return view('oreport_RCNRetur.report')->with($data);
        }

        // Handle Jasper report generation
        $file = 'retur_rencana';
        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

        // Handle report generation
        if (!empty($hasilDataTable1) || !empty($hasilDataTable2)) {
            $reportTitle = 'Laporan Rencana Retur';

            $PHPJasperXML->arrayParameter = array(
                "myTitle" => $reportTitle,
                "mySubTitle" => "Periode: " . $tglDr . " s/d " . $tglSmp,
                "myHeader" => "Toko: " . $na_toko . " | CBG: " . $cbg,
                "cbg" => $cbg,
                "periode" => $periode,
                "na_toko" => $na_toko,
                "no_form" => $no_form
            );

            $PHPJasperXML->arrayParameter["dataTable1"] = $hasilDataTable1;
            $PHPJasperXML->arrayParameter["dataTable2"] = $hasilDataTable2;
            ob_end_clean();
            $PHPJasperXML->outpage("I");
        } else {
            return redirect()->back()->with('error', 'Tidak ada data untuk periode yang dipilih');
        }
    }
}
