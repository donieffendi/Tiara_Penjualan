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

class RBDMController extends Controller
{
    public function report()
    {
        $cbg = Cbg::groupBy('CBG')->get();
        session()->put('filter_cbg', '');
        session()->put('filter_tglDari', date("d-m-Y"));
        session()->put('filter_tglSampai', date("d-m-Y"));

        $per = Perid::query()->get();

        return view('oreport_BDM.report')->with([
            'cbg' => $cbg,
            'per' => $per,
            'periode' => $per,
            'hasilTipe1' => [],
            'hasilTipe2' => [],
            'hasilTipe3' => [],
            'hasilTipe4' => [],
            'hasilTipe6' => [],
            'no_form' => '',
            'na_toko' => '',
            'typ_pers' => '',
            'alamat_pers' => '',
            'toko' => '',
            'selectedCbg' => '',
            'selectedPeriode' => '',
            'selectedSupplierDari' => '',
            'selectedSupplierSampai' => '',
            'tglDr' => date('Y-m-d'),
            'tglSmp' => date('Y-m-d'),
        ]);
    }

    public function jasperBeliDanMusnahReport(Request $request)
    {
        // Set session variables
        session()->put('filter_cbg', $request->cbcbg);
        session()->put('filter_tglDari', $request->tglDr);
        session()->put('filter_tglSampai', $request->tglSmp);

        $cbg = $request->cbcbg ?? '';
        $periode = $request->periode ?? '';
        $tglDr = $request->tglDr ?? date('Y-m-d');
        $tglSmp = $request->tglSmp ?? date('Y-m-d');
        $supplierDari = $request->supplier_dari ?? '';
        $supplierSampai = $request->supplier_sampai ?? '';

        $hasilTipe1 = [];
        $hasilTipe2 = [];
        $hasilTipe3 = [];
        $hasilTipe4 = [];
        $hasilTipe6 = [];

        // Get toko information first
        $na_toko = '';
        $toko = '';
        if (!empty($cbg)) {
            $tokoQuery = "SELECT NA_TOKO FROM toko WHERE KODE = ?";
            $tokoData = DB::select($tokoQuery, [$cbg]);
            if (!empty($tokoData)) {
                $na_toko = $tokoData[0]->NA_TOKO ?? '';
                $toko = $na_toko;
            }
        }

        // Get all active stores
        $storesQuery = "SELECT KODE FROM toko WHERE STA <> 'NS'";
        $stores = DB::select($storesQuery);

        // TIPE 1: Rekap Pembelian per Supplier
        if (true) { // Always generate tipe 1
            try {
                $no_form = '';
                $typ_pers = '';
                $alamat_pers = '';

                // Get form number for BELI-1
                if (!empty($cbg)) {
                    $formQuery = "SELECT toko.KODE, toko.TYP, toko.NA_TOKO, toko.NA_PERS, toko.TYP_PERS, toko.ALAMAT, tokoform.NO_BUKTI
                                  FROM toko, tokoform
                                  WHERE toko.TYP = tokoform.TYP
                                  AND toko.KODE = ?
                                  AND tokoform.KD_PRNT = 'BELI-1'";
                    $formData = DB::select($formQuery, [$cbg]);
                    if (!empty($formData)) {
                        $no_form = $formData[0]->NO_BUKTI ?? '';
                        $na_toko = $formData[0]->NA_TOKO ?? '';
                        $typ_pers = $formData[0]->TYP_PERS ?? '';
                        $alamat_pers = $formData[0]->ALAMAT ?? '';
                    }
                }

                // Build dynamic query for all stores
                $unionQueries = [];
                foreach ($stores as $store) {
                    $storeCode = $store->KODE;
                    $unionQueries[] = "
                        SELECT '{$no_form}' as no_form, '{$na_toko}' as na_toko, '{$typ_pers}' as typ_pers,
                               SUM(bruto) as bruto, SUM(prom) as prom, kodes, namas
                        FROM (
                            SELECT beliz.kodes AS KODES, SUM(BELIZD.TOTAL) AS BRUTO, SUP.NAMAS AS NAMAS,
                                   SUM(IF(rec=1, beliz.PROM, 0)) as prom
                            FROM {$storeCode}.BELIZ, {$storeCode}.BELIZD
                            LEFT JOIN {$storeCode}.BRG ON belizd.KD_BRG = brg.KD_BRG
                            LEFT JOIN {$storeCode}.SUP ON brg.supp = SUP.kodes
                            WHERE belizd.no_bukti = beliz.no_bukti
                            AND DATE(beliz.tgl) >= '{$tglDr}' AND DATE(beliz.tgl) <= '{$tglSmp}'
                            AND (beliz.flag = 'BL' OR beliz.flag = 'B3' OR beliz.flag = 'B5' OR beliz.flag = 'B8')
                            AND beliz.kodes >= '{$supplierDari}' AND beliz.kodes <= '{$supplierSampai}'
                            AND beliz.PER = '{$periode}' AND beliz.CBG = '{$cbg}'
                            GROUP BY BELIZ.KODES ORDER BY KODES
                        ) com1
                        GROUP BY KODES";
                }

                if (!empty($unionQueries)) {
                    $finalQuery = implode(' UNION ALL ', $unionQueries) . " ORDER BY kodes ASC";
                    $hasilTipe1 = DB::select($finalQuery);
                }
            } catch (\Exception $e) {
                $hasilTipe1 = [];
                Log::error('Error in Tipe 1 query: ' . $e->getMessage());
            }
        }

        // TIPE 2: Rekap Pembelian per Sub/Kelompok
        if (true) { // Always generate tipe 2
            try {
                $no_form = '';
                $typ_pers = '';
                $alamat_pers = '';

                // Get form number for BELI-2
                if (!empty($cbg)) {
                    $formQuery = "SELECT toko.KODE, toko.TYP, toko.NA_TOKO, toko.NA_PERS, toko.TYP_PERS, toko.ALAMAT, tokoform.NO_BUKTI
                                  FROM toko, tokoform
                                  WHERE toko.TYP = tokoform.TYP
                                  AND toko.KODE = ?
                                  AND tokoform.KD_PRNT = 'BELI-2'";
                    $formData = DB::select($formQuery, [$cbg]);
                    if (!empty($formData)) {
                        $no_form = $formData[0]->NO_BUKTI ?? '';
                        $na_toko = $formData[0]->NA_TOKO ?? '';
                        $typ_pers = $formData[0]->TYP_PERS ?? '';
                        $alamat_pers = $formData[0]->ALAMAT ?? '';
                    }
                }

                // Build dynamic query for all stores
                $unionQueries = [];
                foreach ($stores as $store) {
                    $storeCode = $store->KODE;
                    $unionQueries[] = "
                        SELECT brg.sub, brg.na_brg, brg.kd_brg, aotprice.KELOMPOK,
                               SUM(belizd.total) as bruto, SUM(IF(rec=1, beliz.PROM, 0)) as prom,
                               beliz.kodes as kodes
                        FROM {$storeCode}.beliz, {$storeCode}.belizd
                        LEFT JOIN {$storeCode}.brg ON belizd.KD_BRG = brg.KD_BRG
                        LEFT JOIN {$storeCode}.aotprice ON brg.sub = aotprice.SUB
                        WHERE belizd.no_bukti = beliz.no_bukti
                        AND (beliz.flag = 'BL' OR beliz.flag = 'B3' OR beliz.flag = 'B5' OR beliz.flag = 'B8')
                        AND beliz.PER = '{$periode}' AND beliz.CBG = '{$cbg}'
                        AND DATE(beliz.tgl) >= '{$tglDr}' AND DATE(beliz.tgl) <= '{$tglSmp}'
                        AND beliz.kodes >= '{$supplierDari}' AND beliz.kodes <= '{$supplierSampai}'
                        GROUP BY brg.kd_brg ORDER BY kd_brg";
                }

                if (!empty($unionQueries)) {
                    $subQuery = "SELECT '{$no_form}' as no_form, '{$na_toko}' as na_toko, '{$typ_pers}' as typ_pers,
                                        sub, kelompok, SUM(bruto) as bruto, SUM(prom) as prom, kodes, kd_brg, na_brg
                                 FROM (" . implode(' UNION ALL ', $unionQueries) . ") as olele
                                 GROUP BY sub ORDER BY sub";
                    $hasilTipe2 = DB::select($subQuery);
                }
            } catch (\Exception $e) {
                $hasilTipe2 = [];
                Log::error('Error in Tipe 2 query: ' . $e->getMessage());
            }
        }

        // TIPE 3: Rekap Musnah per Sub/Kelompok
        if (true) { // Always generate tipe 3
            try {
                $no_form = '';
                $typ_pers = '';
                $alamat_pers = '';

                // Get form number for BELI-3
                if (!empty($cbg)) {
                    $formQuery = "SELECT toko.KODE, toko.TYP, toko.NA_TOKO, toko.NA_PERS, toko.TYP_PERS, toko.ALAMAT, tokoform.NO_BUKTI
                                  FROM toko, tokoform
                                  WHERE toko.TYP = tokoform.TYP
                                  AND toko.KODE = ?
                                  AND tokoform.KD_PRNT = 'BELI-3'";
                    $formData = DB::select($formQuery, [$cbg]);
                    if (!empty($formData)) {
                        $no_form = $formData[0]->NO_BUKTI ?? '';
                        $na_toko = $formData[0]->NA_TOKO ?? '';
                        $typ_pers = $formData[0]->TYP_PERS ?? '';
                        $alamat_pers = $formData[0]->ALAMAT ?? '';
                    }
                }

                // Get stores with type2 not empty
                $storesMusnahQuery = "SELECT KODE FROM toko WHERE type2 <> ''";
                $storesMusnah = DB::select($storesMusnahQuery);

                // Build dynamic query for musnah
                $unionQueries = [];
                foreach ($storesMusnah as $store) {
                    $storeCode = $store->KODE;
                    $unionQueries[] = "
                        SELECT brg.sub, brg.na_brg, brg.kd_brg, aotprice.KELOMPOK,
                               SUM(MUSNAHd.total) as bruto, brg.supp as kodes
                        FROM {$storeCode}.musnah, {$storeCode}.musnahd
                        LEFT JOIN {$storeCode}.brg ON musnahd.KD_BRG = brg.KD_BRG
                        LEFT JOIN {$storeCode}.aotprice ON brg.sub = aotprice.SUB
                        WHERE musnahd.no_bukti = musnah.no_bukti
                        AND musnah.PER = '{$periode}' AND musnah.CBG = '{$cbg}'
                        AND (musnah.flag = 'MR' OR musnah.flag = 'MF')
                        AND musnah.tgl >= '{$tglDr}' AND musnah.tgl <= '{$tglSmp}'
                        AND brg.supp >= '{$supplierDari}' AND brg.supp <= '{$supplierSampai}'
                        GROUP BY brg.kd_brg ORDER BY kd_brg";
                }

                if (!empty($unionQueries)) {
                    $subQuery = "SELECT '{$no_form}' as no_form, '{$na_toko}' as na_toko, '{$typ_pers}' as typ_pers,
                                        sub, kelompok, SUM(bruto) as bruto, kodes, kd_brg, na_brg
                                 FROM (" . implode(' UNION ALL ', $unionQueries) . ") as olele
                                 GROUP BY sub ORDER BY sub";
                    $hasilTipe3 = DB::select($subQuery);
                }
            } catch (\Exception $e) {
                $hasilTipe3 = [];
                Log::error('Error in Tipe 3 query: ' . $e->getMessage());
            }
        }

        // TIPE 4: Rekap Musnah Beli
        if (true) { // Always generate tipe 4
            try {
                $no_form = '';
                $typ_pers = '';
                $alamat_pers = '';

                // Get form number for BELI-4
                if (!empty($cbg)) {
                    $formQuery = "SELECT toko.KODE, toko.TYP, toko.NA_TOKO, toko.NA_PERS, toko.TYP_PERS, toko.ALAMAT, tokoform.NO_BUKTI
                                  FROM toko, tokoform
                                  WHERE toko.TYP = tokoform.TYP
                                  AND toko.KODE = ?
                                  AND tokoform.KD_PRNT = 'BELI-4'";
                    $formData = DB::select($formQuery, [$cbg]);
                    if (!empty($formData)) {
                        $no_form = $formData[0]->NO_BUKTI ?? '';
                        $na_toko = $formData[0]->NA_TOKO ?? '';
                        $typ_pers = $formData[0]->TYP_PERS ?? '';
                        $alamat_pers = $formData[0]->ALAMAT ?? '';
                    }
                }

                // Get stores with type2 not empty
                $storesMusnahQuery = "SELECT KODE FROM toko WHERE type2 <> ''";
                $storesMusnah = DB::select($storesMusnahQuery);

                // Build dynamic query for rekap musnah beli
                $unionQueries = [];
                foreach ($storesMusnah as $store) {
                    $storeCode = $store->KODE;
                    $unionQueries[] = "
                        SELECT a.kodes KODES, a.namas namas, B.NO_BUKTI as NOBUKTI, B.KD_BRG barang,
                               B.qty as b_qty, B.harga as b_harga, B.total as b_total,
                               0 m_qty, 0 m_harga, 0 m_total
                        FROM {$storeCode}.belizd B, {$storeCode}.beliz a
                        WHERE b.no_bukti = a.no_bukti
                        AND B.per = '{$periode}' AND B.flag IN ('BL','B3','B5','B8')
                        AND a.tgl >= '{$tglDr}' AND a.tgl <= '{$tglSmp}'
                        AND a.kodes >= '{$supplierDari}' AND a.kodes <= '{$supplierSampai}'
                        AND a.CBG = '{$cbg}'
                        UNION ALL
                        SELECT n.supp kodes, '' namas, M.no_bukti NOBUKTI, M.KD_BRG barang,
                               0 b_qty, 0 b_harga, 0 b_total,
                               M.qty as m_qty, M.hb as m_harga, M.TOTAL as m_total
                        FROM {$storeCode}.musnah K, {$storeCode}.musnahd M
                        LEFT JOIN {$storeCode}.brg n ON m.kd_brg = n.kd_brg
                        WHERE k.no_bukti = m.no_bukti AND M.per = '{$periode}'
                        AND n.supp >= '{$supplierDari}' AND n.supp <= '{$supplierSampai}'
                        AND k.tgl >= '{$tglDr}' AND k.tgl <= '{$tglSmp}'
                        AND k.CBG = '{$cbg}'
                        AND m.flag IN ('MR','MF')";
                }

                if (!empty($unionQueries)) {
                    $subQuery = "SELECT '{$no_form}' as no_form, '{$na_toko}' as na_toko, '{$typ_pers}' as typ_pers,
                                        kodes, namas, NOBUKTI, barang, SUM(b_qty), b_harga, SUM(b_total) as beli,
                                        SUM(m_qty), m_harga, SUM(m_total) as musnah,
                                        SUM(b_total) - SUM(m_total) as gtotal
                                 FROM (" . implode(' UNION ALL ', $unionQueries) . ") as com1
                                 GROUP BY kodes ORDER BY kodes ASC";
                    $hasilTipe4 = DB::select($subQuery);
                }
            } catch (\Exception $e) {
                $hasilTipe4 = [];
                Log::error('Error in Tipe 4 query: ' . $e->getMessage());
            }
        }

        // TIPE 6: Rekap Beli (Musnah per Supplier)
        if (true) { // Always generate tipe 6
            try {
                $no_form = '';
                $typ_pers = '';
                $alamat_pers = '';

                // Get form number for BELI-6
                if (!empty($cbg)) {
                    $formQuery = "SELECT toko.KODE, toko.TYP, toko.NA_TOKO, toko.NA_PERS, toko.TYP_PERS, toko.ALAMAT, tokoform.NO_BUKTI
                                  FROM toko, tokoform
                                  WHERE toko.TYP = tokoform.TYP
                                  AND toko.KODE = ?
                                  AND tokoform.KD_PRNT = 'BELI-6'";
                    $formData = DB::select($formQuery, [$cbg]);
                    if (!empty($formData)) {
                        $no_form = $formData[0]->NO_BUKTI ?? '';
                        $na_toko = $formData[0]->NA_TOKO ?? '';
                        $typ_pers = $formData[0]->TYP_PERS ?? '';
                        $alamat_pers = $formData[0]->ALAMAT ?? '';
                    }
                }

                // Build dynamic query for rekap beli (musnah)
                $unionQueries = [];
                foreach ($stores as $store) {
                    $storeCode = $store->KODE;
                    $unionQueries[] = "
                        SELECT BRG.SUPP AS KODES, SUM(MUSNAHD.TOTAL) AS BRUTO, SUP.NAMAS AS NAMAS
                        FROM {$storeCode}.MUSNAH, {$storeCode}.MUSNAHD
                        LEFT JOIN {$storeCode}.BRG ON MUSNAHD.KD_BRG = BRG.KD_BRG
                        LEFT JOIN {$storeCode}.SUP ON BRG.SUPP = SUP.KODES
                        WHERE musnahd.no_bukti = musnah.no_bukti
                        AND musnah.PER = '{$periode}' AND musnah.CBG = '{$cbg}'
                        AND (musnah.flag = 'MR' OR musnah.flag = 'MF')
                        AND musnah.tgl >= '{$tglDr}' AND musnah.tgl <= '{$tglSmp}'
                        AND brg.supp >= '{$supplierDari}' AND brg.supp <= '{$supplierSampai}'
                        GROUP BY BRG.SUPP ORDER BY SUPP";
                }

                if (!empty($unionQueries)) {
                    $subQuery = "SELECT '{$no_form}' as no_form, '{$na_toko}' as na_toko, '{$typ_pers}' as typ_pers,
                                        SUM(bruto) as bruto, kodes, namas
                                 FROM (" . implode(' UNION ALL ', $unionQueries) . ") com1
                                 GROUP BY KODES ORDER BY kodes ASC";
                    $hasilTipe6 = DB::select($subQuery);
                }
            } catch (\Exception $e) {
                $hasilTipe6 = [];
                Log::error('Error in Tipe 6 query: ' . $e->getMessage());
            }
        }

        // Prepare data array
        $data = [
            'hasilTipe1' => $hasilTipe1,
            'hasilTipe2' => $hasilTipe2,
            'hasilTipe3' => $hasilTipe3,
            'hasilTipe4' => $hasilTipe4,
            'hasilTipe6' => $hasilTipe6,
            'na_toko' => $na_toko,
            'no_form' => $no_form ?? '',
            'typ_pers' => $typ_pers ?? '',
            'alamat_pers' => $alamat_pers ?? '',
            'toko' => $toko,
            'cbg' => Cbg::groupBy('CBG')->get(),
            'periode' => Perid::query()->get(),
            'per' => Perid::query()->get(),
            'selectedCbg' => $cbg,
            'selectedPeriode' => $periode,
            'selectedSupplierDari' => $supplierDari,
            'selectedSupplierSampai' => $supplierSampai,
            'tglDr' => $tglDr,
            'tglSmp' => $tglSmp,
        ];

        // Check if this is a filter request (for DataTable)
        if ($request->has('filter') && !$request->has('cetak')) {
            return view('oreport_BDM.report')->with($data);
        }

        // Handle Jasper report generation
        $file = 'beli_dan_musnah';
        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

        // Handle report generation
        if (!empty($hasilTipe1) || !empty($hasilTipe2) || !empty($hasilTipe3) || !empty($hasilTipe4) || !empty($hasilTipe6)) {
            $reportTitle = 'Laporan Beli Dan Musnah';

            $PHPJasperXML->arrayParameter = array(
                "myTitle" => $reportTitle,
                "mySubTitle" => "Periode: " . $tglDr . " s/d " . $tglSmp,
                "myHeader" => "Toko: " . $na_toko . " | Supplier: " . $supplierDari . " - " . $supplierSampai,
                "cbg" => $cbg,
                "periode" => $periode,
                "supplierRange" => $supplierDari . " - " . $supplierSampai
            );

            $PHPJasperXML->arrayParameter["tipe1"] = $hasilTipe1;
            $PHPJasperXML->arrayParameter["tipe2"] = $hasilTipe2;
            $PHPJasperXML->arrayParameter["tipe3"] = $hasilTipe3;
            $PHPJasperXML->arrayParameter["tipe4"] = $hasilTipe4;
            $PHPJasperXML->arrayParameter["tipe6"] = $hasilTipe6;
            ob_end_clean();
            $PHPJasperXML->outpage("I");
        } else {
            return redirect()->back()->with('error', 'Tidak ada data untuk periode yang dipilih');
        }
    }
}
