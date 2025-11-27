<?php

namespace App\Http\Controllers\OReport;

use App\Http\Controllers\Controller;
use App\Models\Master\Cbg;
use App\Models\Master\Perid;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;

class RKwitansiController extends Controller
{
    public function report()
    {
        $cbg = Cbg::groupBy('CBG')->get();
        session()->put('filter_cbg', '');
        session()->put('filter_tglDari', date("d-m-Y"));
        session()->put('filter_tglSampai', date("d-m-Y"));

        $per = Perid::query()->get();

        return view('oreport_kwitansi.report')->with([
            'cbg' => $cbg,
            'per' => $per,
            'periode' => $per,
            'hasil' => [],
            'no_form' => '',
            'na_toko' => '',
            'typ_pers' => '',
            'typ_npwp' => '',
            'alamat_pers' => '',
            'toko' => '',
            'selectedCbg' => '',
            'selectedPeriode' => '',
            'tglDr' => date('Y-m-d'),
            'tglSmp' => date('Y-m-d'),
        ]);
    }

    public function jasperKwitansiReport(Request $request)
    {
        // Set session variables
        session()->put('filter_cbg', $request->cbcbg);

        // Determine table prefix based on CBG
        $cbgTable = '';
        if (!empty($request->cbcbg)) {
            $cbgTable = $request->cbcbg . '.';
        }

        // Step 1: Get store and form information (NOMOR FORM PRINT section from Delphi)
        $tokoQuery = "SELECT toko.KODE, toko.TYP, toko.NA_TOKO, toko.NA_PERS, toko.TYP_PERS, toko.TYP_NPWP, toko.ALAMAT, tokoform.NO_BUKTI
                     FROM {$cbgTable}toko, {$cbgTable}tokoform
                     WHERE toko.TYP = tokoform.TYP
                           AND toko.KODE = ?
                           AND tokoform.KD_PRNT = ?";

        $tokoData = DB::select($tokoQuery, [$request->cbcbg, 'RSEWA-REPORT6']);

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

        // Step 2: Get store name separately (co.SQL from Delphi)
        $tokoNameQuery = "SELECT NA_TOKO FROM {$cbgTable}toko WHERE toko.KODE = ?";
        $tokoNameData = DB::select($tokoNameQuery, [$request->cbcbg]);
        $toko = !empty($tokoNameData) ? $tokoNameData[0]->NA_TOKO : '';

        // Prepare parameters
        $tglDr = $request->tglDr ?? date('Y-m-d');
        $tglSmp = $request->tglSmp ?? date('Y-m-d');
        $periode = trim($request->periode ?? '');

        // Step 3: Main PIU query based on Delphi logic (beli.SQL from Delphi)
        $piuQuery = "SELECT ? as no_form, ? as na_toko, ? as typ_pers, ? as typ_npwp, ? as alamat_pers,
                           ? as nmtoko, ? as tgl1, ? as tgl2,
                           IF(gol='P1','Y','T') as faktur, no_bukti,
                           cbg, per, tgl, kodec, namac, notes, dpp, ppn, total
                    FROM {$cbgTable}piu
                    WHERE per = ?
                          AND flag = 'PS'
                          AND tgl >= ?
                          AND tgl <= ?
                    ORDER BY IF(gol='P1','Y','T') ASC";

        $hasil = DB::select($piuQuery, [
            $no_form,       // :no_form
            $na_toko,       // :na_toko
            $typ_pers,      // :typ_pers
            $typ_npwp,      // :typ_npwp
            $alamat_pers,   // :alamat_pers
            $toko,          // :toko (nmtoko)
            $tglDr,         // :tgl1
            $tglSmp,        // :tgl2
            $periode,       // :per
            $tglDr,         // :tgl1
            $tglSmp         // :tgl2
        ]);

        // Prepare data array
        $data = [
            'hasil' => $hasil,
            'na_toko' => $na_toko,
            'no_form' => $no_form,
            'typ_pers' => $typ_pers,
            'typ_npwp' => $typ_npwp,
            'alamat_pers' => $alamat_pers,
            'toko' => $toko,
            'cbg' => Cbg::groupBy('CBG')->get(),
            'periode' => Perid::query()->get(),
            'per' => Perid::query()->get(),
            'selectedCbg' => $request->cbcbg,
            'selectedPeriode' => $request->periode,
            'tglDr' => $tglDr,
            'tglSmp' => $tglSmp,
        ];

        // Check if this is a filter request (for DataTable)
        if ($request->has('filter') && !$request->has('cetak')) {
            return view('oreport_kwitansi.report')->with($data);
        }

        // Handle Jasper report generation
        $file = 'kwitansi';
        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

        // Handle report generation
        if (!empty($hasil)) {
            $PHPJasperXML->arrayParameter = array(
                "myTitle" => "Laporan Kwitansi",
                "mySubTitle" => "Periode: " . $tglDr . " s/d " . $tglSmp,
                "myHeader" => "Toko: " . $na_toko
            );

            $PHPJasperXML->arrayParameter["results"] = $hasil;
            ob_end_clean();
            $PHPJasperXML->outpage("I");
        } else {
            return redirect()->back()->with('error', 'Tidak ada data untuk periode yang dipilih');
        }
    }
}
