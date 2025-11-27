<?php

namespace App\Http\Controllers\OReport;

use App\Http\Controllers\Controller;
use App\Models\Master\Cbg;
use App\Models\Master\Perid;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;

class RnondController extends Controller
{
    public function report()
    {
        $cbg = Cbg::groupBy('CBG')->get();
        session()->put('filter_cbg', '');
        session()->put('filter_tglDari', date("d-m-Y"));
        session()->put('filter_tglSampai', date("d-m-Y"));

        $per = Perid::query()->get();

        return view('oreport_nond.report')->with([
            'cbg' => $cbg,
            'periode' => $per,
            'hasil' => [],
            'hasilNonBeli' => [],
            'per' => $per,
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

    public function jasperNondReport(Request $request)
    {
        $file = 'nondreport';
        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

        // Set session variables
        session()->put('filter_cbg', $request->cbcbg);

        // Determine table prefix based on CBG
        $cbgTable = '';
        if (!empty($request->cbcbg)) {
            $cbgTable = $request->cbcbg . '.';
        }

        // Step 1: Get store and form information (translating from Delphi code)
        $tokoQuery = "SELECT toko.KODE, toko.TYP, toko.NA_TOKO, toko.NA_PERS, toko.TYP_PERS, toko.TYP_NPWP, toko.ALAMAT, tokoform.NO_BUKTI
                     FROM {$cbgTable}toko, {$cbgTable}tokoform
                     WHERE toko.TYP = tokoform.TYP
                           AND toko.KODE = ?
                           AND tokoform.KD_PRNT = ?";

        $tokoData = DB::select($tokoQuery, [$request->cbcbg, 'RNON-REPORT6']);

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

        // Step 2: Get store name separately (co.SQL.Text from Delphi)
        $tokoNameQuery = "SELECT NA_TOKO FROM {$cbgTable}toko WHERE toko.KODE = ?";
        $tokoNameData = DB::select($tokoNameQuery, [$request->cbcbg]);
        $toko = !empty($tokoNameData) ? $tokoNameData[0]->NA_TOKO : '';

        // Step 3: Main query for nonbeli data with required columns
        // Bukti Pembayaran, Agenda, Tgl, acno, Uraian, Reff, Total
        $periode = trim($request->periode ?? '');

        $nonBeliQuery = "SELECT nonbeli.NO_BUKTI as bukti_pembayaran,
                               nonbelid.agenda as agenda,
                               DATE(nonbeli.TGL) as tgl,
                               nonbelid.acno as acno,
                               nonbelid.uraian as uraian,
                               nonbelid.reff as reff,
                               nonbelid.total as total,
                               ? as no_form, ? as na_toko, ? as typ_pers, ? as typ_npwp, ? as alamat_pers,
                               ? as nmtoko
                        FROM {$cbgTable}nonbeli, {$cbgTable}nonbelid
                        WHERE nonbeli.NO_BUKTI = nonbelid.no_bukti
                              AND nonbeli.per = ?
                        ORDER BY nonbeli.no_bukti, nonbelid.agenda";

        $hasilNonBeli = DB::select($nonBeliQuery, [
            $no_form,      // :no_form
            $na_toko,      // :na_toko
            $typ_pers,     // :typ_pers
            $typ_npwp,     // :typ_npwp
            $alamat_pers,  // :alamat_pers
            $toko,         // :toko (nmtoko)
            $periode       // :per
        ]);

        return view('oreport_nond.report')->with([
            'cbg' => Cbg::groupBy('CBG')->get(),
            'periode' => Perid::query()->get(),
            'hasil' => $hasilNonBeli, // Main result
            'hasilNonBeli' => $hasilNonBeli,
            'per' => Perid::query()->get(),
            'no_form' => $no_form,
            'na_toko' => $na_toko,
            'typ_pers' => $typ_pers,
            'typ_npwp' => $typ_npwp,
            'alamat_pers' => $alamat_pers,
            'toko' => $toko,
            'selectedCbg' => $request->cbcbg,
            'selectedPeriode' => $periode,
            'tglDr' => $request->tglDr,
            'tglSmp' => $request->tglSmp,
        ]);
    }
}