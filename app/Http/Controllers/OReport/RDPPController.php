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

class RDPPController extends Controller
{
    public function report()
    {
        $cbg = Cbg::groupBy('CBG')->get();
        session()->put('filter_cbg', '');
        session()->put('filter_tglDari', date("d-m-Y"));
        session()->put('filter_tglSampai', date("d-m-Y"));

        $per = Perid::query()->get();

        return view('oreport_DPP.report')->with([
            'cbg' => $cbg,
            'per' => $per,
            'periode' => $per,
            'hasilAgendaPerTanggal' => [],
            'no_form' => '',
            'na_toko' => '',
            'typ_pers' => '',
            'alamat_pers' => '',
            'toko' => '',
            'selectedCbg' => '',
            'selectedSuplier' => '',
            'tglDr' => date('Y-m-d'),
            'tglSmp' => date('Y-m-d'),
            'judule' => '',
        ]);
    }

    public function jasperDppReport(Request $request)
    {
        // Set session variables
        session()->put('filter_cbg', $request->cbcbg);
        session()->put('filter_tglDari', $request->tglDr);
        session()->put('filter_tglSampai', $request->tglSmp);

        $cbg = $request->cbcbg ?? '';
        $tglDr = $request->tglDr ?? date('Y-m-d');
        $tglSmp = $request->tglSmp ?? date('Y-m-d');
        $filterSup = strtoupper($request->suplier ?? '');

        $hasilAgendaPerTanggal = [];
        $no_form = '';
        $na_toko = '';
        $typ_pers = '';
        $alamat_pers = '';
        $judule = "LAPORAN AGENDA PER TANGGAL " . $filterSup;

        try {
            // Get form information for RTERIMA_GD
            if (!empty($cbg)) {
                $formQuery = "SELECT A.KODE, A.TYP, A.NA_TOKO, A.NA_PERS, A.TYP_PERS, A.TYP_NPWP, A.ALAMAT, B.NO_BUKTI
                              FROM toko A, tokoform B
                              WHERE A.TYP = B.TYP
                              AND A.KODE = ?
                              AND B.KD_PRNT = 'RTERIMA_GD'";
                $formData = DB::select($formQuery, [$cbg]);

                if (!empty($formData)) {
                    $no_form = $formData[0]->NO_BUKTI ?? '';
                    $typ_pers = $formData[0]->TYP_PERS ?? '';
                    $na_toko = $formData[0]->NA_TOKO ?? '';
                    $alamat_pers = $formData[0]->ALAMAT ?? '';
                }
            }

            // Main query for agenda per tanggal
            if (!empty($cbg)) {
                $agendaQuery = "
                    SELECT ? as judule, ? as nmtoko, ? as no_form, ? as typ_pers,
                           ? as tgl1, ? as tgl2,
                           A.NO_BUKTI, A.flag,
                           A.tgl_posted, A.NO_PO, A.TGL, A.REF,
                           A.KODES, A.NAMAS, SUM(A.total) as bruto,
                           SUM(A.PROM) as prom, SUM(A.ppn) as ppn, (SUM(A.total) - SUM(A.prom)) as DPP,
                           SUM(A.nett) as nett,
                           CASE
                               WHEN A.TYPE = 'FO' THEN B.FO_KLB
                               WHEN A.TYPE = 'NF' THEN B.NF_KLB
                               WHEN A.TYPE = 'PB' THEN B.PB_KLB
                               WHEN A.TYPE = 'ST' THEN B.ST_KLB
                               WHEN A.TYPE = 'FF' THEN B.FF_KLB
                               ELSE CASE
                                   WHEN B.FO_KLB <> 0 THEN B.FO_KLB
                                   WHEN B.FF_KLB <> 0 THEN B.FF_KLB
                                   WHEN B.NF_KLB <> 0 THEN B.NF_KLB
                                   WHEN B.ST_KLB <> 0 THEN B.ST_KLB
                                   WHEN B.PB_KLB <> 0 THEN B.PB_KLB
                                   ELSE 0
                               END
                           END AS KLB,
                           A.cbg
                    FROM {$cbg}.beliz A, {$cbg}.sup B
                    WHERE A.KODES = B.KODES
                    AND A.FLAG IN ('B3', 'B5', 'B8', 'BL')
                    AND DATE(A.tgl_posted) BETWEEN ? AND ?
                    AND CASE
                        WHEN ? = 'DC' THEN A.KODES IN ('510C', '510D', '510E', '510F', '510G')
                        ELSE A.KODES NOT IN ('510C', '510D', '510E', '510F', '510G')
                    END
                    GROUP BY DATE(A.tgl_posted)
                    ORDER BY DATE(A.TGL_posted)";

                $hasilAgendaPerTanggal = DB::select($agendaQuery, [
                    $judule,           // judule
                    $na_toko,          // nmtoko
                    $no_form,          // no_form
                    $typ_pers,         // typ_pers
                    $tglDr,            // tgl1
                    $tglSmp,           // tgl2
                    $tglDr,            // tgl_posted between start
                    $tglSmp,           // tgl_posted between end
                    $filterSup         // filtersup
                ]);
            }
        } catch (\Exception $e) {
            $hasilAgendaPerTanggal = [];
            Log::error('Error in DPP query: ' . $e->getMessage());
        }

        // Prepare data array
        $data = [
            'hasilAgendaPerTanggal' => $hasilAgendaPerTanggal,
            'na_toko' => $na_toko,
            'no_form' => $no_form,
            'typ_pers' => $typ_pers,
            'alamat_pers' => $alamat_pers,
            'toko' => $na_toko,
            'judule' => $judule,
            'cbg' => Cbg::groupBy('CBG')->get(),
            'periode' => Perid::query()->get(),
            'per' => Perid::query()->get(),
            'selectedCbg' => $cbg,
            'selectedSuplier' => $filterSup,
            'tglDr' => $tglDr,
            'tglSmp' => $tglSmp,
        ];

        // Check if this is a filter request (for DataTable)
        if ($request->has('filter') && !$request->has('cetak')) {
            return view('oreport_DPP.report')->with($data);
        }

        // Handle Jasper report generation
        $file = 'agenda_per_tanggal';
        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

        // Handle report generation
        if (!empty($hasilAgendaPerTanggal)) {
            $reportTitle = $judule;

            $PHPJasperXML->arrayParameter = array(
                "myTitle" => $reportTitle,
                "mySubTitle" => "Periode: " . $tglDr . " s/d " . $tglSmp,
                "myHeader" => "Toko: " . $na_toko . " | Filter Supplier: " . $filterSup,
                "cbg" => $cbg,
                "filterSup" => $filterSup,
                "judule" => $judule,
                "nmtoko" => $na_toko,
                "no_form" => $no_form,
                "typ_pers" => $typ_pers,
                "tgl1" => $tglDr,
                "tgl2" => $tglSmp
            );

            $PHPJasperXML->arrayParameter["agendaPerTanggal"] = $hasilAgendaPerTanggal;
            ob_end_clean();
            $PHPJasperXML->outpage("I");
        } else {
            return redirect()->back()->with('error', 'Tidak ada data untuk periode yang dipilih');
        }
    }
}
