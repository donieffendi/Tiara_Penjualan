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

class RBLMTFController extends Controller
{
    public function report()
    {
        $cbg = Cbg::groupBy('CBG')->get();
        session()->put('filter_cbg', '');
        session()->put('filter_tglDari', date("d-m-Y"));
        session()->put('filter_tglSampai', date("d-m-Y"));

        $per = Perid::query()->get();

        return view('oreport_BLMTF.report')->with([
            'cbg' => $cbg,
            'per' => $per,
            'periode' => $per,
            'hasilBayarTransfer' => [],
            'no_form' => '',
            'na_toko' => '',
            'typ_pers' => '',
            'alamat_pers' => '',
            'toko' => '',
            'selectedCbg' => '',
            'selectedStatus' => '',
            'tglDr' => date('Y-m-d'),
            'tglSmp' => date('Y-m-d'),
            'judule' => '',
        ]);
    }

    public function jasperBlmtfReport(Request $request)
    {
        // Set session variables
        session()->put('filter_cbg', $request->cbcbg);
        session()->put('filter_tglDari', $request->tglDr);
        session()->put('filter_tglSampai', $request->tglSmp);

        $cbg = $request->cbcbg ?? '';
        $tglDr = $request->tglDr ?? date('Y-m-d');
        $tglSmp = $request->tglSmp ?? date('Y-m-d');
        $statusTransfer = $request->status_transfer ?? 'Belum'; // 'Belum' or 'Sudah'

        $hasilBayarTransfer = [];
        $no_form = '';
        $na_toko = '';
        $typ_pers = '';
        $alamat_pers = '';
        $judule = "LAPORAN BAYAR TRANSFER - STATUS: " . strtoupper($statusTransfer);

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

            // Main query for bayar transfer based on status
            if (!empty($cbg)) {
                // Determine sukses condition based on status
                $suksesCondition = '';
                if (trim($statusTransfer) === 'Belum') {
                    $suksesCondition = 'sukses = 0';
                } else {
                    $suksesCondition = 'sukses IN ("1", "2")';
                }

                $transferQuery = "
                    SELECT ? as toko, ? as no_form, ? as typ_pers,
                           ? as tgl1, ? as tgl2,
                           no_bukti, kodes, namas, jtempo, total
                    FROM {$cbg}.tagi
                    WHERE {$suksesCondition}
                    AND kd_bank <> ''
                    AND jtempo BETWEEN ? AND ?
                    ORDER BY no_bukti";

                $hasilBayarTransfer = DB::select($transferQuery, [
                    $na_toko,          // toko
                    $no_form,          // no_form
                    $typ_pers,         // typ_pers
                    $tglDr,            // tgl1
                    $tglSmp,           // tgl2
                    $tglDr,            // jtempo between start
                    $tglSmp            // jtempo between end
                ]);
            }
        } catch (\Exception $e) {
            $hasilBayarTransfer = [];
            Log::error('Error in BLMTF query: ' . $e->getMessage());
        }

        // Prepare data array
        $data = [
            'hasilBayarTransfer' => $hasilBayarTransfer,
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
            'selectedStatus' => $statusTransfer,
            'tglDr' => $tglDr,
            'tglSmp' => $tglSmp,
        ];

        // Check if this is a filter request (for DataTable)
        if ($request->has('filter') && !$request->has('cetak')) {
            return view('oreport_BLMTF.report')->with($data);
        }

        // Handle Jasper report generation
        $file = 'bayar_transfer';
        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

        // Handle report generation
        if (!empty($hasilBayarTransfer)) {
            $reportTitle = $judule;

            $PHPJasperXML->arrayParameter = array(
                "myTitle" => $reportTitle,
                "mySubTitle" => "Periode: " . $tglDr . " s/d " . $tglSmp,
                "myHeader" => "Toko: " . $na_toko . " | Status: " . $statusTransfer,
                "cbg" => $cbg,
                "statusTransfer" => $statusTransfer,
                "judule" => $judule,
                "nmtoko" => $na_toko,
                "no_form" => $no_form,
                "typ_pers" => $typ_pers,
                "tgl1" => $tglDr,
                "tgl2" => $tglSmp
            );

            $PHPJasperXML->arrayParameter["bayarTransfer"] = $hasilBayarTransfer;
            ob_end_clean();
            $PHPJasperXML->outpage("I");
        } else {
            return redirect()->back()->with('error', 'Tidak ada data untuk periode yang dipilih');
        }
    }

    public function jasperInstruksiPembayaran(Request $request)
    {
        // Get no_bukti from request
        $noBukti = $request->no_bukti ?? '';
        $cbg = $request->cbcbg ?? '';

        if (empty($noBukti)) {
            return redirect()->back()->with('error', 'No Bukti tidak ditemukan');
        }

        $no_form = '';
        $na_toko = '';
        $typ_pers = '';
        $alamat_pers = '';

        try {
            // Get form information for Instruksi Pembayaran
            if (!empty($cbg)) {
                $formQuery = "SELECT A.KODE, A.TYP, A.NA_TOKO, A.NA_PERS, A.TYP_PERS, A.TYP_NPWP, A.ALAMAT, B.NO_BUKTI
                              FROM toko A, tokoform B
                              WHERE A.TYP = B.TYP
                              AND A.KODE = ?
                              AND B.KD_PRNT = 'TGZ-AKK2-055.3'";
                $formData = DB::select($formQuery, [$cbg]);

                if (!empty($formData)) {
                    $no_form = $formData[0]->NO_BUKTI ?? '';
                    $typ_pers = $formData[0]->TYP_PERS ?? '';
                    $na_toko = $formData[0]->NA_TOKO ?? '';
                    $alamat_pers = $formData[0]->ALAMAT ?? '';
                }
            }

            // Complex query for payment instruction with bank logic
            $instruksiQuery = "
                SELECT ? as no_form, ? as na_toko, ? as typ_pers,
                       ? as alamat_pers, ? as usrnm,
                       IF(tagi.BYR = 'API', '014',
                          IF(tagi.KD_BANK NOT IN ('008','009','014','011','013','022'),
                             IF(tagi.KD_BANK = '015', '022', '011'),
                             tagi.KD_BANK
                          )
                       ) as X,
                       IF(tagi.BYR = 'API' OR tagi.BYR = 'TRF',
                          CONCAT((SELECT IF(tagi.BYR = 'API', '014',
                                           IF(tagi.KD_BANK NOT IN ('008','009','014','011','013','022'),
                                              IF(tagi.KD_BANK = '015', '022', '011'),
                                              tagi.KD_BANK
                                           )
                                         )),
                                 RIGHT(LEFT(TRIM(tagi.NO_BUKTI), 6), 4),
                                 RIGHT(TRIM(tagi.NO_BUKTI), 4)
                          ),
                          ''
                       ) as nom,
                       tagi.tgl, (@buk := tagi.no_bukti) as bukit, tagi.JTEMPO,
                       tagi.no_bukti, tagi.notes, tagi.nama_b, tagi.penagih,
                       tagid.no_trm, tagid.tgl_trm, tagid.ket,
                       tagi.kodes, tagi.retur, tagi.lain, tagi.badm, tagid.TOTAL0,
                       IF(LENGTH(NO_PJK) > 5, RIGHT(NO_PJK, 8), '') AS POT,
                       tagid.TOTAL, tagi.namas, tagi.kota,
                       tagi.alamat, tagi.golongan, tagi.byr, tagi.klb, tagi.norek,
                       tagi.anb, tagi.tgl_trf, tagi.type,
                       IF(badm > 0, 'Dipotong Biaya Bank', '') as ketby,
                       UPPER(tagid.no_sp) as no_sp, tagid.no_pjk,
                       tagi.LAIN as lainh, tagi.NO_TRANS, tagi.prom as bprom,
                       tagi.total as totalall, tagid.jns, sup.gol_brg,
                       (SELECT SUM(tagid.pot) FROM {$cbg}.tagid WHERE jns = 'TL' AND no_bukti = @buk) as potpn,
                       IF(UPPER(tagi.byr) LIKE 'API', 'BCA',
                          IF(tagi.byr = 'TRF',
                             IF(tagi.nama_b LIKE '%BCA%' AND tagi.NAMA_B NOT LIKE '%syariah%', 'BCA',
                                IF(tagi.nama_b LIKE '%BNI%' AND tagi.NAMA_B NOT LIKE '%syariah%', 'BNI',
                                   IF(tagi.nama_b LIKE '%MANDIRI%' AND tagi.NAMA_B NOT LIKE '%syariah%', 'MANDIRI',
                                      IF(tagi.nama_b LIKE '%CIMB%' AND tagi.NAMA_B NOT LIKE '%syariah%', 'CIMB NIAGA',
                                         IF(tagi.nama_b LIKE '%PERMATA%' AND tagi.NAMA_B NOT LIKE '%syariah%', 'PERMATA',
                                            IF(tagi.nama_b LIKE '%DANAMON%' AND tagi.NAMA_B NOT LIKE '%syariah%', 'DANAMON',
                                               IF(tagi.nama_b LIKE '%SINARMAS%' AND tagi.NAMA_B NOT LIKE '%syariah%', 'BCA', 'BCA')
                                            )
                                         )
                                      )
                                   )
                                )
                             ),
                             'PERMATA'
                          )
                       ) as AGENG_GANTENG
                FROM {$cbg}.tagi, {$cbg}.tagid, {$cbg}.sup
                WHERE tagi.no_bukti = tagid.no_bukti
                AND tagi.kodes = sup.kodes
                AND tagi.no_bukti = ?
                AND (jns = 'BL' OR jns = 'B8' OR jns = 'B3' OR jns = 'B5')
                ORDER BY tagid.tgl_trm ASC, tagid.no_trm ASC";

            $hasilInstruksi = DB::select($instruksiQuery, [
                $no_form,          // no_form
                $na_toko,          // na_toko
                $typ_pers,         // typ_pers
                $alamat_pers,      // alamat_pers
                'Admin',           // usrnm (you might want to get this from session)
                $noBukti           // XD (no_bukti parameter)
            ]);

            // Handle Jasper report generation for Instruksi Pembayaran
            $file = 'instruksi_pembayaran';
            $PHPJasperXML = new PHPJasperXML();
            $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

            if (!empty($hasilInstruksi)) {
                $reportTitle = 'INSTRUKSI PEMBAYARAN';

                $PHPJasperXML->arrayParameter = array(
                    "myTitle" => $reportTitle,
                    "mySubTitle" => "No. Bukti: " . $noBukti,
                    "myHeader" => "Toko: " . $na_toko,
                    "no_form" => $no_form,
                    "na_toko" => $na_toko,
                    "typ_pers" => $typ_pers,
                    "alamat_pers" => $alamat_pers,
                    "usrnm" => 'Admin'
                );

                $PHPJasperXML->arrayParameter["instruksiPembayaran"] = $hasilInstruksi;
                ob_end_clean();
                $PHPJasperXML->outpage("I");
            } else {
                return redirect()->back()->with('error', 'Tidak ada data instruksi pembayaran');
            }
        } catch (\Exception $e) {
            Log::error('Error in Instruksi Pembayaran: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error generating instruksi pembayaran');
        }
    }
}
