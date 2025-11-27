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

class RTFBRGController extends Controller
{
    public function report()
    {
        $cbg = Cbg::groupBy('CBG')->get();
        session()->put('filter_cbg', '');
        session()->put('filter_tglDari', date("d-m-Y"));
        session()->put('filter_tglSampai', date("d-m-Y"));

        $per = Perid::query()->get();

        return view('oreport_TFBRG.report')->with([
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

    public function jasperTFBRGReport(Request $request)
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
        $hasilDataTable3 = [];
        $hasilDataTable4 = [];

        // Get toko information first
        $na_toko = '';
        $toko = '';
        $typ_pers = '';
        $alamat_pers = '';
        $no_form = '';

        // Validate CBG selection - ensure it's not main store
        $pusatQuery = "SELECT kode FROM toko WHERE sta = 'MA'";
        $pusatData = DB::select($pusatQuery);
        $pusat = !empty($pusatData) ? $pusatData[0]->kode : '';

        if (!empty($cbg) && $cbg === $pusat) {
            return redirect()->back()->with('error', $pusat . ' bukan outlet, pilih cabang outlet!');
        }

        if (!empty($cbg)) {
            // Get store information and form details
            $tokoQuery = "SELECT toko.KODE, toko.TYP, toko.NA_TOKO, toko.NA_PERS, toko.TYP_PERS, toko.ALAMAT, tokoform.NO_BUKTI
                          FROM toko, tokoform
                          WHERE toko.TYP = tokoform.TYP
                          AND toko.KODE = ?
                          AND tokoform.KD_PRNT = '---'";
            $tokoData = DB::select($tokoQuery, [$cbg]);
            if (!empty($tokoData)) {
                $na_toko = $tokoData[0]->NA_TOKO ?? '';
                $typ_pers = $tokoData[0]->TYP_PERS ?? '';
                $alamat_pers = $tokoData[0]->ALAMAT ?? '';
                $no_form = $tokoData[0]->NO_BUKTI ?? '';
                $toko = $na_toko;
            }
        }

        // DataTable 1: Main distribution analysis with outlet order data
        if (!empty($cbg)) {
            try {
                $query1 = "
                    SELECT *,
                    IF((QTY_OO <> QTY_BZ) AND (QTY_BZ <> 0) AND (SELISIH <> 0),
                        IF(QTY_OZ = 0, 'BELUM DIBUATKAN PENOLAKAN OUTLET',
                            IF(QTY_OZ <> 0 AND QTY_OX = 0, 'BELUM DITERIMA TGZ',
                                IF((QTY_OZ <> QTY_OX) AND (QTY_OZ <> 0), 'QTY TOLAK TIDAK SAMA', '')
                            )
                        ), ''
                    ) as TOLAK
                    FROM (
                        SELECT
                            ? as no_form, ? as na_toko, ? as typ_pers, ? as alamat_pers,
                            ? as cbg, ? as toko,
                            CONCAT(DAY(?), ' - ', DAY(?), ' ', MONTH(?), '/', YEAR(?)) as tgl,
                            aotprice.SUB, aotprice.KELOMPOK,
                            SUM(B.TOTAL) as TOTAL_OO, SUM(B.QTY) as QTY_OO,
                            SUM(B.HARGA * B.QTY_CBG) as TOTAL_BZ, SUM(B.QTY_CBG) as QTY_BZ,
                            SUM(B.HARGA * B.QTY_OX) as TOTAL_OX, SUM(B.QTY_OX) as QTY_OX,
                            SUM(B.QTY_OZ) as QTY_OZ,
                            SUM(QTY - (IF(QTY_CBG = 0, QTY, QTY_CBG + QTY_OX))) as SELISIH,
                            aotprice.ACC_BELI as acno
                        FROM stockaz A, stockazd B
                        LEFT JOIN aotprice ON aotprice.SUB = LEFT(B.KD_BRG, 3)
                        WHERE A.NO_BUKTI = B.NO_BUKTI
                        AND A.FLAG = 'OO'
                        AND A.tgl_posted <> CURDATE()
                        AND TRIM(A.NOTES) = ?
                        AND A.TGL BETWEEN ? AND ?
                        GROUP BY aotprice.SUB
                        ORDER BY aotprice.SUB ASC
                    ) nda";

                $hasilDataTable1 = DB::select($query1, [
                    $no_form,
                    $na_toko,
                    $typ_pers,
                    $alamat_pers,
                    $cbg,
                    $toko,
                    $tglDr,
                    $tglSmp,
                    $tglDr,
                    $tglDr,
                    $cbg,
                    $tglDr,
                    $tglSmp
                ]);
            } catch (\Exception $e) {
                $hasilDataTable1 = [];
                Log::error('Error in DataTable 1 query: ' . $e->getMessage());
            }
        }

        // DataTable 2: Detailed breakdown by sub-category for summary report
        if (!empty($cbg)) {
            try {
                $query2 = "
                    SELECT *,
                    QTY_OO - QTY_BZ as S_QTY,
                    TOTAL_OO - TOTAL_BZ as S_TOTAL
                    FROM (
                        SELECT
                            ? as cbg, ? as toko,
                            CONCAT(DAY(?), ' - ', DAY('2019-09-30'), ' ', MONTH(?), '/', YEAR(?)) as tgl,
                            SUM(nda.qtyk) as QTY_OO,
                            SUM(IF(nda.qtyt IS NULL, 0, nda.qtyt)) as QTY_BZ,
                            SUM(nda.totalk) as TOTAL_OO,
                            SUM(IF(nda.totalt IS NULL, 0, nda.totalt)) as TOTAL_BZ,
                            aotprice.sub, aotprice.kelompok,
                            aotprice.ACC_BELI as acno
                        FROM (
                            SELECT too.NO_BUKTI, too.BKTK, too.KD_BRG,
                                too.NA_BRG, too.SUB, too.KELOMPOK,
                                too.QTY as qtyk, bz.QTY as qtyt,
                                too.HARGA as HARGAk, bz.HARGA as hargat,
                                too.TOTAL as totalk, bz.TOTAL as totalt,
                                (too.QTY) - (IFNULL(bz.QTY, 0)) as selisih,
                                IF(((too.QTY) - (IFNULL(bz.QTY, 0))) = 0, '', too.NO_BUKTI) as TOLAK
                            FROM dck.too
                            LEFT JOIN {$cbg}.tbd as bz ON bz.BKTK = too.NO_BUKTI AND bz.KD_BRG = too.KD_BRG
                            WHERE DATE(too.tgl_posted) >= ?
                            AND DATE(too.tgl_posted) <= ?
                            AND too.CBG = ?
                        ) as nda
                        LEFT JOIN aotprice ON aotprice.SUB = nda.sub
                        GROUP BY nda.sub
                    ) as sas
                    ORDER BY sub ASC";

                $hasilDataTable2 = DB::select($query2, [
                    $cbg,
                    $toko,
                    $tglDr,
                    $tglDr,
                    $tglDr,
                    $tglDr,
                    $tglSmp,
                    $cbg
                ]);
            } catch (\Exception $e) {
                $hasilDataTable2 = [];
                Log::error('Error in DataTable 2 query: ' . $e->getMessage());
            }
        }

        // DataTable 3: Detailed per document analysis
        if (!empty($cbg)) {
            try {
                $query3 = "
                    SELECT *,
                    QTY_OO - QTY_BZ as S_QTY,
                    TOTAL_OO - TOTAL_BZ as S_TOTAL
                    FROM (
                        SELECT
                            ? as toko,
                            CONCAT(DAY(?), ' - ', DAY(?), ' ', MONTH(?), '/', YEAR(?)) as tgl,
                            no_bukti,
                            SUM(nda.qtyk) as QTY_OO,
                            SUM(IF(nda.qtyt IS NULL, 0, nda.qtyt)) as QTY_BZ,
                            SUM(nda.totalk) as TOTAL_OO,
                            SUM(IF(nda.totalt IS NULL, 0, nda.totalt)) as TOTAL_BZ,
                            nda.sub, nda.kd_brg, nda.na_brg,
                            aotprice.ACC_BELI as acno
                        FROM (
                            SELECT too.NO_BUKTI, too.BKTK, too.KD_BRG,
                                too.NA_BRG, too.SUB, too.KELOMPOK,
                                too.QTY as qtyk, bz.QTY as qtyt,
                                too.HARGA as HARGAk, bz.HARGA as hargat,
                                too.TOTAL as totalk, bz.TOTAL as totalt,
                                (too.QTY) - (IFNULL(bz.QTY, 0)) as selisih,
                                IF(((too.QTY) - (IFNULL(bz.QTY, 0))) = 0, '', too.NO_BUKTI) as TOLAK
                            FROM dck.too
                            LEFT JOIN {$cbg}.tbd as bz ON bz.BKTK = too.NO_BUKTI AND bz.KD_BRG = too.KD_BRG
                            WHERE DATE(too.tgl_posted) >= ?
                            AND DATE(too.tgl_posted) <= ?
                            AND too.CBG = ?
                        ) as nda
                        LEFT JOIN aotprice ON aotprice.SUB = nda.sub
                        GROUP BY nda.no_bukti, nda.kd_brg
                    ) as sas
                    ORDER BY no_bukti, kd_brg";

                $hasilDataTable3 = DB::select($query3, [
                    $toko,
                    $tglDr,
                    $tglDr,
                    $tglDr,
                    $tglDr,
                    $tglDr,
                    $tglSmp,
                    $cbg
                ]);
            } catch (\Exception $e) {
                $hasilDataTable3 = [];
                Log::error('Error in DataTable 3 query: ' . $e->getMessage());
            }
        }

        // DataTable 4: Second main query for detailed outlet analysis (from second button)
        if (!empty($cbg)) {
            try {
                $query4 = "
                    SELECT *,
                    IF((QTY_OO <> QTY_BZ) AND (QTY_BZ <> 0) AND (SELISIH <> 0),
                        IF(QTY_OZ = 0, 'BELUM DIBUATKAN PENOLAKAN OUTLET',
                            IF(QTY_OZ <> 0 AND QTY_OX = 0, 'BELUM DITERIMA TGZ',
                                IF((QTY_OZ <> QTY_OX) AND (QTY_OZ <> 0), 'QTY TOLAK TIDAK SAMA', '')
                            )
                        ), ''
                    ) as TOLAK
                    FROM (
                        SELECT
                            ? as toko,
                            CONCAT(DAY(?), ' - ', DAY(?), ' ', MONTH(?), '/', YEAR(?)) as tgl,
                            B.KD_BRG, B.NA_BRG,
                            SUM(B.TOTAL) as TOTAL_OO, SUM(B.QTY) as QTY_OO,
                            SUM(B.HARGA * B.QTY_CBG) as TOTAL_BZ, SUM(B.QTY_CBG) as QTY_BZ,
                            SUM(B.HARGA * B.QTY_OX) as TOTAL_OX, SUM(B.QTY_OX) as QTY_OX,
                            SUM(B.QTY_OZ) as QTY_OZ,
                            SUM(QTY - (IF(QTY_CBG = 0, QTY, QTY_CBG + QTY_OX))) as SELISIH,
                            aotprice.ACC_BELI as acno
                        FROM stockaz A, stockazd B
                        LEFT JOIN aotprice ON aotprice.SUB = LEFT(B.KD_BRG, 3)
                        WHERE A.NO_BUKTI = B.NO_BUKTI
                        AND A.FLAG = 'OO'
                        AND A.tgl_posted <> CURDATE()
                        AND TRIM(A.NOTES) = ?
                        AND A.TGL BETWEEN ? AND ?
                        GROUP BY B.KD_BRG
                        ORDER BY B.KD_BRG ASC
                    ) nda";

                $hasilDataTable4 = DB::select($query4, [
                    $toko,
                    $tglDr,
                    $tglSmp,
                    $tglDr,
                    $tglDr,
                    $cbg,
                    $tglDr,
                    $tglSmp
                ]);
            } catch (\Exception $e) {
                $hasilDataTable4 = [];
                Log::error('Error in DataTable 4 query: ' . $e->getMessage());
            }
        }

        // Prepare data array
        $data = [
            'hasilDataTable1' => $hasilDataTable1,
            'hasilDataTable2' => $hasilDataTable2,
            'hasilDataTable3' => $hasilDataTable3,
            'hasilDataTable4' => $hasilDataTable4,
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
            return view('oreport_TFBRG.report')->with($data);
        }

        // Handle Jasper report generation
        $file = 'distribusi_outlet';
        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

        // Handle report generation
        if (!empty($hasilDataTable1) || !empty($hasilDataTable2) || !empty($hasilDataTable3) || !empty($hasilDataTable4)) {
            $reportTitle = 'Laporan Distribusi Outlet';

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
            $PHPJasperXML->arrayParameter["dataTable3"] = $hasilDataTable3;
            $PHPJasperXML->arrayParameter["dataTable4"] = $hasilDataTable4;
            ob_end_clean();
            $PHPJasperXML->outpage("I");
        } else {
            return redirect()->back()->with('error', 'Tidak ada data untuk periode yang dipilih');
        }
    }
}
