<?php

namespace App\Http\Controllers\OReport;

use App\Http\Controllers\Controller;
use App\Models\Master\Cbg;
use App\Models\Master\Perid;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;

class RAgendaGudangController extends Controller
{
    public function report()
    {
        $cbg = Cbg::groupBy('CBG')->get();
        session()->put('filter_cbg', '');
        session()->put('filter_tglDari', date("d-m-Y"));
        session()->put('filter_tglSampai', date("d-m-Y"));

        $per = Perid::query()->get();

        return view('oreport_agenda_gudang.report')->with([
            'cbg' => $cbg,
            'per' => $per,
            'periode' => $per,
            'hasil' => [],
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
        ]);
    }

    public function jasperAgendaGudangReport(Request $request)
    {
        // Set session variables
        session()->put('filter_cbg', $request->cbcbg);
        session()->put('filter_tglDari', $request->tglDr);
        session()->put('filter_tglSampai', $request->tglSmp);

        $cbg = trim($request->cbcbg ?? '');
        $suplier = strtoupper($request->suplier ?? ''); // DC or non-DC
        $tglDr = $request->tglDr ?? date('Y-m-d');
        $tglSmp = $request->tglSmp ?? date('Y-m-d');

        $no_form = '';
        $na_toko = '';
        $typ_pers = '';
        $alamat_pers = '';
        $hasil = [];

        if (!empty($cbg)) {
            // Get form information (first query from Delphi)
            $formQuery = "SELECT A.KODE, A.TYP, A.NA_TOKO, A.NA_PERS, A.TYP_PERS, A.TYP_NPWP, A.ALAMAT, B.NO_BUKTI
                         FROM toko A, tokoform B
                         WHERE A.TYP = B.TYP
                               AND A.KODE = ?
                               AND B.KD_PRNT = ?";

            $formData = DB::select($formQuery, [$cbg, 'RTERIMA_GD']);

            if (!empty($formData)) {
                $no_form = $formData[0]->NO_BUKTI ?? '';
                $typ_pers = $formData[0]->TYP_PERS ?? '';
                $na_toko = $formData[0]->NA_TOKO ?? '';
                $alamat_pers = $formData[0]->ALAMAT ?? '';
            }

            // Main query (second query from Delphi) - Agenda Gudang
            $agendaQuery = "SELECT ? as nmtoko, ? as no_form, ? as typ_pers,
                                  ? as tgl1, ? as tgl2,
                                  A.NO_BUKTI, A.flag,
                                  A.tgl_posted, A.NO_PO, A.TGL, A.REF,
                                  A.KODES, A.NAMAS, A.total as bruto,
                                  A.PROM, A.ppn, A.total-A.prom as DPP,
                                  A.nett,
                                  CASE
                                      WHEN A.TYPE='FO' THEN B.FO_KLB
                                      WHEN A.TYPE='NF' THEN B.NF_KLB
                                      WHEN A.TYPE='PB' THEN B.PB_KLB
                                      WHEN A.TYPE='ST' THEN B.ST_KLB
                                      WHEN A.TYPE='FF' THEN B.FF_KLB
                                      WHEN B.FO_KLB<>0 THEN B.FO_KLB
                                      WHEN B.FF_KLB<>0 THEN B.FF_KLB
                                      WHEN B.NF_KLB<>0 THEN B.NF_KLB
                                      WHEN B.ST_KLB<>0 THEN B.ST_KLB
                                      WHEN B.PB_KLB<>0 THEN B.PB_KLB
                                      ELSE 0
                                  END AS KLB,
                                  A.cbg
                           FROM {$cbg}.beliz A, {$cbg}.sup B
                           WHERE A.KODES = B.KODES
                                 AND A.FLAG IN ('B3','B5','B8','BL')
                                 AND DATE(A.tgl) BETWEEN ? AND ?
                                 AND " . ($suplier === "DC"
                ? "A.KODES IN ('510C','510D','510E','510F','510G')"
                : "A.KODES NOT IN ('510C','510D','510E','510F','510G')") . "
                           ORDER BY A.TGL, A.FLAG, A.NO_BUKTI";

            $hasil = DB::select($agendaQuery, [
                $na_toko,        // nmtoko
                $no_form,        // no_form
                $typ_pers,       // typ_pers
                $tglDr,          // tgl1
                $tglSmp,         // tgl2
                $tglDr,          // tgl BETWEEN param 1
                $tglSmp          // tgl BETWEEN param 2
            ]);
        }

        // Prepare data array
        $data = [
            'hasil' => $hasil,
            'na_toko' => $na_toko,
            'no_form' => $no_form,
            'typ_pers' => $typ_pers,
            'alamat_pers' => $alamat_pers,
            'toko' => $na_toko,
            'cbg' => Cbg::groupBy('CBG')->get(),
            'periode' => Perid::query()->get(),
            'per' => Perid::query()->get(),
            'selectedCbg' => $cbg,
            'selectedPeriode' => $request->periode,
            'selectedSuplier' => $suplier,
            'tglDr' => $tglDr,
            'tglSmp' => $tglSmp,
        ];

        // Check if this is a filter request (for DataTable)
        if ($request->has('filter') && !$request->has('cetak')) {
            return view('oreport_agenda_gudang.report')->with($data);
        }

        // Handle Jasper report generation
        $file = 'agenda_gudang';
        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

        // Handle report generation
        if (!empty($hasil)) {
            $reportTitle = 'Laporan Agenda Gudang';
            $suplierTypeLabel = $suplier === 'DC' ? 'DC Supplier' : 'Non-DC Supplier';

            $PHPJasperXML->arrayParameter = array(
                "myTitle" => $reportTitle,
                "mySubTitle" => "Periode: " . $tglDr . " s/d " . $tglSmp,
                "myHeader" => "Toko: " . $na_toko . " | Supplier: " . $suplierTypeLabel,
                "suplierType" => $suplierTypeLabel,
                "cbg" => $cbg,
                "no_form" => $no_form,
                "typ_pers" => $typ_pers
            );

            $PHPJasperXML->arrayParameter["results"] = $hasil;
            ob_end_clean();
            $PHPJasperXML->outpage("I");
        } else {
            return redirect()->back()->with('error', 'Tidak ada data untuk periode yang dipilih');
        }
    }
}
