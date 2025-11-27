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

class RBiayaPemasaranController extends Controller
{
    public function report()
    {
        $cbg = Cbg::groupBy('CBG')->get();
        session()->put('filter_cbg', '');
        session()->put('filter_tglDari', date("d-m-Y"));
        session()->put('filter_tglSampai', date("d-m-Y"));

        $per = Perid::query()->get();

        return view('oreport_biaya_pemasaran.report')->with([
            'cbg' => $cbg,
            'per' => $per,
            'periode' => $per,
            'hasilPerSupBeli' => [],
            'hasilPerAgendaBeli' => [],
            'no_form' => '',
            'na_toko' => '',
            'typ_pers' => '',
            'alamat_pers' => '',
            'toko' => '',
            'selectedCbg' => '',
            'selectedPeriode' => '',
            'selectedSupplierDari' => '',
            'selectedSupplierSampai' => '',
            'selectedPromosi' => 0,
            'tglDr' => date('Y-m-d'),
            'tglSmp' => date('Y-m-d'),
        ]);
    }

    public function jasperBiayaPemasaranReport(Request $request)
    {
        // Set session variables
        session()->put('filter_cbg', $request->cbcbg);
        session()->put('filter_tglDari', $request->tglDr);
        session()->put('filter_tglSampai', $request->tglSmp);

        $cbg = $request->cbcbg ?? '';
        $tglDr = $request->tglDr ?? date('Y-m-d');
        $tglSmp = $request->tglSmp ?? date('Y-m-d');
        $supplierDari = $request->supplier_dari ?? '';
        $supplierSampai = $request->supplier_sampai ?? '';
        $promosi = $request->promosi ?? 0;

        $hasilPerSupBeli = [];
        $hasilPerAgendaBeli = [];
        $cbgMast = '';

        // First query: Get master CBG (store with status "MA")
        $cbgMastQuery = "SELECT KODE FROM toko WHERE STA = 'MA'";
        $cbgMastData = DB::select($cbgMastQuery);

        if (!empty($cbgMastData)) {
            $cbgMast = $cbgMastData[0]->KODE;

            // Second query: Call stored procedure for PER-SUP-BELI
            try {
                $hasilPerSupBeli = DB::select(
                    "CALL {$cbgMast}.adm_rekap_promosi(?, ?, ?, ?, ?, ?, ?)",
                    [
                        'PER-SUP-BELI',  // prosesx
                        $cbg,            // cbgx
                        $tglDr,          // tglx
                        $tglSmp,         // tgly
                        $supplierDari,   // supx
                        $supplierSampai, // supy
                        $promosi         // promx
                    ]
                );
            } catch (\Exception $e) {
                $hasilPerSupBeli = [];
                Log::error('Error calling adm_rekap_promosi for PER-SUP-BELI: ' . $e->getMessage());
            }

            // Third query: Call stored procedure for PER-AGENDA-BELI
            try {
                $hasilPerAgendaBeli = DB::select(
                    "CALL {$cbgMast}.adm_rekap_promosi(?, ?, ?, ?, ?, ?, ?)",
                    [
                        'PER-AGENDA-BELI', // prosesx
                        $cbg,              // cbgx
                        $tglDr,            // tglx
                        $tglSmp,           // tgly
                        $supplierDari,     // supx
                        $supplierSampai,   // supy
                        $promosi           // promx
                    ]
                );
            } catch (\Exception $e) {
                $hasilPerAgendaBeli = [];
                Log::error('Error calling adm_rekap_promosi for PER-AGENDA-BELI: ' . $e->getMessage());
            }
        }

        // Get store information
        $na_toko = '';
        $no_form = '';
        $typ_pers = '';
        $alamat_pers = '';

        if (!empty($cbg)) {
            $tokoQuery = "SELECT NA_TOKO, TYP_PERS, ALAMAT FROM toko WHERE KODE = ?";
            $tokoData = DB::select($tokoQuery, [$cbg]);

            if (!empty($tokoData)) {
                $na_toko = $tokoData[0]->NA_TOKO ?? '';
                $typ_pers = $tokoData[0]->TYP_PERS ?? '';
                $alamat_pers = $tokoData[0]->ALAMAT ?? '';
            }
        }

        // Prepare data array
        $data = [
            'hasilPerSupBeli' => $hasilPerSupBeli,
            'hasilPerAgendaBeli' => $hasilPerAgendaBeli,
            'na_toko' => $na_toko,
            'no_form' => $no_form,
            'typ_pers' => $typ_pers,
            'alamat_pers' => $alamat_pers,
            'toko' => $na_toko,
            'cbgMast' => $cbgMast,
            'cbg' => Cbg::groupBy('CBG')->get(),
            'periode' => Perid::query()->get(),
            'per' => Perid::query()->get(),
            'selectedCbg' => $cbg,
            'selectedPeriode' => $request->periode,
            'selectedSupplierDari' => $supplierDari,
            'selectedSupplierSampai' => $supplierSampai,
            'selectedPromosi' => $promosi,
            'tglDr' => $tglDr,
            'tglSmp' => $tglSmp,
        ];

        // Check if this is a filter request (for DataTable)
        if ($request->has('filter') && !$request->has('cetak')) {
            return view('oreport_biaya_pemasaran.report')->with($data);
        }

        // Handle Jasper report generation
        $file = 'biaya_pemasaran';
        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

        // Handle report generation
        if (!empty($hasilPerSupBeli) || !empty($hasilPerAgendaBeli)) {
            $reportTitle = 'Laporan Biaya Pemasaran';

            $PHPJasperXML->arrayParameter = array(
                "myTitle" => $reportTitle,
                "mySubTitle" => "Periode: " . $tglDr . " s/d " . $tglSmp,
                "myHeader" => "Toko: " . $na_toko . " | Supplier: " . $supplierDari . " - " . $supplierSampai,
                "cbg" => $cbg,
                "cbgMast" => $cbgMast,
                "supplierRange" => $supplierDari . " - " . $supplierSampai,
                "promosi" => $promosi
            );

            $PHPJasperXML->arrayParameter["perSupBeli"] = $hasilPerSupBeli;
            $PHPJasperXML->arrayParameter["perAgendaBeli"] = $hasilPerAgendaBeli;
            ob_end_clean();
            $PHPJasperXML->outpage("I");
        } else {
            return redirect()->back()->with('error', 'Tidak ada data untuk periode yang dipilih');
        }
    }
}
