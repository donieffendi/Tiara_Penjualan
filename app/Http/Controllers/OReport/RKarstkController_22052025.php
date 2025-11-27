<?php
namespace App\Http\Controllers\OReport;

use App\Http\Controllers\Controller;
use App\Models\Master\Brg;
use App\Models\Master\Cbg;
// ganti 1

use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";
use PHPJasperXML;

// ganti 2
class RKarstkController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function kartu()
    {
        session()->put('filter_brg1', '');
        session()->put('filter_nabrg1', '');
        session()->put('filter_brg2', '');
        session()->put('filter_nabrg2', '');
        session()->put('filter_tglDr', now()->format('d-m-Y'));
        session()->put('filter_tglSmp', now()->format('d-m-Y'));
        $brg = Brg::orderBy('KD_BRG', 'ASC')->get();
        $cbg = Cbg::groupBy('CBG')->get();
// GANTI 3 //
        return view('oreport_brg.kartu')->with(['brg' => $brg])->with(['cbg' => $cbg])->with(['hasil' => []]);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function jasperStokKartu(Request $request)
    {
        $file         = 'karstk';
        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

        // Ganti format tanggal input agar sama dengan database
        $tglDrD  = date("Y-m-d", strtotime($request['tglDr']));
        $tglSmpD = date("Y-m-d", strtotime($request['tglSmp']));

        // Convert tanggal agar ambil start of day/end of day
        $tglDr  = Carbon::parse($request->tglDr)->startOfDay();
        $tglSmp = Carbon::parse($request->tglSmp)->endOfDay();

        $periode = date("m/Y", strtotime($request['tglDr']));
        $bulan   = date("m", strtotime($request['tglDr']));
        $tahun   = date("Y", strtotime($request['tglDr']));
        // $filterbrg = "KD_BRG<>'' " ;
        $filterhdh   = " AND hdhd.KD_BRG<>'' ";
        $filterambil = " AND ambild.KD_BRG<>'' ";
        $filterstock = " AND stockad.KD_BRG<>'' ";
        $brg1        = isset($request->brg1) ? trim($request->brg1) : '';
        $brg2        = isset($request->brg2) ? trim($request->brg2) : '';

        if ($request->brg1 && $request->brg2) {
            if ($request->brg1 === $request->brg2) {
                $filterbrg = "KD_BRG = '".$request->brg1."'";
                $filterbrg2   = "$request->brg1";
                $filterhdh   = " AND hdhd.KD_BRG = '" . $request->brg1 . "' ";
                $filterkirim = " AND hkirimd.KD_BRG = '" . $request->brg1 . "' ";
                $filterambil = " AND ambild.KD_BRG = '" . $request->brg1 . "' ";
                $filterstock = " AND stockad.KD_BRG = '" . $request->brg1 . "' ";
                $filterkirim = " AND hkirimd.KD_BRG = '" . $request->brg1 . "' ";
            } else {
                $filterbrg   = "KD_BRG BETWEEN '" . $request->brg1 . "' AND '" . $request->brg2 . "'";
                $filterbr2   = "AND KD_BRG BETWEEN '" . $request->brg1 . "' AND '" . $request->brg2 . "'";
                $filterhdh   = " AND hdhd.KD_BRG BETWEEN '" . $request->brg1 . "' AND '" . $request->brg2 . "' ";
                $filterambil = " AND ambild.KD_BRG BETWEEN '" . $request->brg1 . "' AND '" . $request->brg2 . "' ";
                $filterstock = " AND stockad.KD_BRG BETWEEN '" . $request->brg1 . "' AND '" . $request->brg2 . "' ";
                $filterkirim = " AND hkirimd.KD_BRG BETWEEN '" . $request->brg1 . "' AND '" . $request->brg2 . "' ";
            }
        }

        $tgawal = $tahun . '-' . $bulan . '-01';

        session()->put('filter_brg1', $request->brg1);
        session()->put('filter_nabrg1', $request->nabrg1);
        session()->put('filter_brg2', $request->brg2);
        session()->put('filter_nabrg2', $request->nabrg2);
        session()->put('filter_tglDr', $request->tglDr);
        session()->put('filter_tglSmp', $request->tglSmp);
        // dd($tglDrD,$bulan, $filterbrg,$tahun, $periode, $tglSmpD, $filterhdh, $filterambil, $filterstock );
        $queryakum = DB::SELECT("SET @akum:=0;");
        $query     = DB::SELECT("
        SELECT
            COALESCE(NO_BUKTI, '') AS NO_BUKTI,
            DATE_FORMAT(TGL, '%d-%m-%Y') AS TGL, KD_BRG, NA_BRG, URAIAN,
            AWAL, MASUK, KELUAR, LAIN,
            IF(@kdbrg<>KD_BRG, @akum:=AWAL+MASUK-KELUAR+LAIN, @akum:=@akum+AWAL+MASUK-KELUAR+LAIN) AS SALDO,
            @kdbrg:=KD_BRG AS ganti, URUTAN
        FROM (
            -- Saldo Awal
            SELECT NULL AS NO_BUKTI, '$tglDrD' AS TGL, KD_BRG, NA_BRG,
                'SALDO AWAL' AS URAIAN,
                SUM(AWAL) AS AWAL, 0 AS MASUK, 0 AS KELUAR, 0 AS LAIN, 1 AS URUTAN
            FROM (
                SELECT KD_BRG, NA_BRG, AW$bulan AS AWAL
                FROM brgd WHERE $filterbrg AND YER='$tahun'

                UNION ALL

                SELECT hdhd.KD_BRG, hdhd.NA_BRG, hdhd.QTY AS AWAL
                FROM hdh JOIN hdhd ON hdh.NO_BUKTI = hdhd.NO_BUKTI
                WHERE hdh.TGL<'$tglDrD' $filterhdh AND hdh.PER='$periode' AND hdhd.QTY <> 0

                UNION ALL

                SELECT ambild.KD_BRG, ambild.NA_BRG, (ambild.QTY * -1) AS AWAL
                FROM ambil JOIN ambild ON ambil.NO_BUKTI = ambild.NO_BUKTI
                WHERE ambil.TGL<'$tglDrD' $filterambil AND ambil.PER='$periode' AND ambild.QTY <> 0

                UNION ALL

                SELECT stockad.KD_BRG, stockad.NA_BRG, stockad.QTY AS AWAL
                FROM stocka JOIN stockad ON stocka.NO_BUKTI = stockad.NO_BUKTI
                WHERE stocka.TGL<'$tglDrD' $filterstock AND stocka.PER='$periode'
            ) AS AWAL00
            GROUP BY KD_BRG

            UNION ALL

            -- Transaksi lainnya dengan COALESCE untuk NO_BUKTI
            SELECT COALESCE(hdh.NO_BUKTI, '') AS NO_BUKTI, DATE_FORMAT(hdh.TGL, '%d-%m-%Y') AS TGL,
                hdhd.KD_BRG, hdhd.NA_BRG, CONCAT('BELI-', TRIM(hdh.NAMAS)) AS URAIAN,
                0 AS AWAL, hdhd.QTY AS MASUK, 0 AS KELUAR, 0 AS LAIN, 2 AS URUTAN
            FROM hdh JOIN hdhd ON hdh.NO_BUKTI = hdhd.NO_BUKTI
            WHERE hdh.TGL BETWEEN '$tglDrD' AND '$tglSmpD' $filterhdh AND hdhd.QTY <> 0 AND hdh.PER='$periode'

            UNION ALL

            SELECT COALESCE(ambil.NO_BUKTI, '') AS NO_BUKTI, DATE_FORMAT(ambil.TGL, '%d-%m-%Y') AS TGL,
                ambild.KD_BRG, ambild.NA_BRG, CONCAT('JUAL-', TRIM(ambil.NAMAC)) AS URAIAN,
                0 AS AWAL, 0 AS MASUK, ambild.QTY AS KELUAR, 0 AS LAIN, 4 AS URUTAN
            FROM ambil JOIN ambild ON ambil.NO_BUKTI = ambild.NO_BUKTI
            WHERE ambil.TGL BETWEEN '$tglDrD' AND '$tglSmpD' $filterambil AND ambil.PER='$periode'

            UNION ALL

            SELECT COALESCE(hkirim.NO_BUKTI, '') AS NO_BUKTI, DATE_FORMAT(hkirim.TGL, '%d-%m-%Y') AS TGL,
                hkirimd.KD_BRG, hkirimd.NA_BRG, CONCAT('KIRIM-', TRIM(hkirim.CBG_TUJU)) AS URAIAN,
                0 AS AWAL, 0 AS MASUK, hkirimd.QTY AS KELUAR, 0 AS LAIN, 4 AS URUTAN
            FROM hkirim JOIN hkirimd ON hkirim.NO_BUKTI = hkirimd.NO_BUKTI
            WHERE hkirim.TGL BETWEEN '$tglDrD' AND '$tglSmpD' $filterkirim AND hkirim.PER='$periode'

        ) AS kartustok;
    ");

        $brg = Brg::where('KD_BRG', '<>', 'ZZ')->get();
        if ($request->has('filter')) {
            return view('oreport_brg.kartu')->with(['brg' => $brg])->with(['hasil' => $query]);
        }

        $data = [];
        foreach ($query as $key => $value) {
            array_push($data, [
                'NO_BUKTI' => $query[$key]->NO_BUKTI,
                'TGL'      => $query[$key]->TGL,
                // 'KD_BRG' => $query[$key]->KD_BRG,
                'KD_BRG'   => "`" . strval($query[$key]->KD_BRG),
                'CBG'      => "`" . strval($query[$key]->CBG),
                'NA_BRG'   => $query[$key]->NA_BRG,
                'URAIAN'   => $query[$key]->URAIAN,
                'AWAL'     => $query[$key]->AWAL,
                'MASUK'    => $query[$key]->MASUK,
                'KELUAR'   => $query[$key]->KELUAR,
                'LAIN'     => $query[$key]->LAIN,
                'AKHIR'    => $query[$key]->SALDO,
            ]);
        }
        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I");
    }

}