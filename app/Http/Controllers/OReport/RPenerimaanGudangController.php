<?php

namespace App\Http\Controllers\OReport;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;

class RPenerimaanGudangController extends Controller
{
    public function report()
    {
        $cbg = $this->getCbgList();
        session()->put('filter_cbg', '');
        session()->put('filter_per', '');
        session()->put('filter_tgl', '');
        session()->put('filter_tipe', '');
        session()->put('report_type', 1);

        return view('oreport_penerimaan_gudang.report')->with([
            'cbg' => $cbg,
            'hasilDetail' => [],
            'hasilTipe' => [],
        ]);
    }

    public function getPenerimaanGudangReport(Request $request)
    {
        $cbg = $this->getCbgList();

        $cbgCode = $request->cbg;
        $periode = $request->per;
        $tanggal = $request->tgl;
        $tipe = $request->tipe;
        $reportType = $request->report_type ?? 1;

        session()->put('filter_cbg', $cbgCode);
        session()->put('filter_per', $periode);
        session()->put('filter_tgl', $tanggal);
        session()->put('filter_tipe', $tipe);
        session()->put('report_type', $reportType);

        $hasilDetail = [];
        $hasilTipe = [];

        if (!empty($cbgCode)) {
            if ($reportType == 1 && !empty($periode) && !empty($tanggal)) {
                $hasilDetail = $this->getPenerimaanDetailReport($cbgCode, $periode, $tanggal);
            } else if ($reportType == 2 && !empty($tipe)) {
                $hasilTipe = $this->getPenerimaanTipeReport($tipe);
            }
        }

        return view('oreport_penerimaan_gudang.report')->with([
            'cbg' => $cbg,
            'hasilDetail' => $hasilDetail,
            'hasilTipe' => $hasilTipe,
            'reportType' => $reportType,
        ]);
    }

    private function getCbgList()
    {
        try {
            $query = "SELECT KODE FROM tgz.toko WHERE STA IN ('MA','CB','DC') ORDER BY NO_ID ASC";
            return DB::select($query);
        } catch (\Exception $e) {
            Log::error('Error in getCbgList: ' . $e->getMessage());
            return [];
        }
    }

    private function getPenerimaanDetailReport($cbgCode, $periode, $tanggal)
    {
        try {
            $query = "SELECT beliz.no_bukti, beliz.tgl, brg.kd_brg,
                        brg.na_brg, brg.ket_uk, brg.ket_kem, nda.lph, belizd.qty,
                        belizd.harga, belizd.total, beliz.notes, beliz.usrnm
                      FROM {$cbgCode}.beliz,
                           {$cbgCode}.belizd, {$cbgCode}.brg
                      LEFT JOIN
                      ( SELECT KD_BRG, LPH from {$cbgCode}.brgdt where cbg=? ) as nda
                        on nda.KD_BRG=brg.KD_BRG
                      WHERE belizd.no_bukti=beliz.NO_BUKTI
                      AND brg.KD_BRG=belizd.KD_BRG
                      AND beliz.PER=?
                      AND date(beliz.tgl_posted)=?
                      AND beliz.flag='B3'
                      AND beliz.cbg=?
                      ORDER BY belizd.KD_BRG ASC";

            return DB::select($query, [$cbgCode, $periode, $tanggal, $cbgCode]);
        } catch (\Exception $e) {
            Log::error('Error in getPenerimaanDetailReport: ' . $e->getMessage());
            return [];
        }
    }

    private function getPenerimaanTipeReport($tipe)
    {
        try {
            if ($tipe == 'BL') {
                $query = "SELECT A.GOLONGAN, A.FLAG, A.no_bukti,B.KD_BRG,
                            concat(B.NA_BRG,' ',B.KET_UK) NA_BRG,
                            B.qty,B.harga, B.hargak, B.hj HJX,
                            round(((((B.harga*(100-B.diskon1)/100)*
                            (100-B.diskon2)/100)*(100+B.MARGIN)/100)*
                            ((if ( A.GOLONGAN NOT IN ('P0*','P3') ,110,100))/100)),-1) as HARUS, B.kdlaku,
                            ( SELECT RIGHT(HARUS,2) ) X,
                            ( SELECT IF(X>50, (HARUS-X)+120,HARUS ) ) HARUSX
                          FROM beliz A, belizd B
                          WHERE A.no_bukti = B.no_bukti
                          AND A.TGL=CURDATE() AND A.flag='BL'
                          GROUP BY B.KD_BRG
                          HAVING HJX<>HARUSX
                          ORDER BY A.FLAG, A.NO_BUKTI, B.KD_BRG ASC";
            } else if ($tipe == 'B3') {
                $query = "SELECT A.GOLONGAN, A.FLAG, A.no_bukti,B.KD_BRG,
                            concat(B.NA_BRG,' ',B.KET_UK) NA_BRG, C.hj HJX,
                            round(((((((B.harga*(100-B.diskon1)/100)*
                            (100-B.diskon2)/100)* (100-B.diskon3)/100))*
                            (100+D.MARGIN)/100)*((if (D.ppn='1',110,100))/100)),-1) as HARUS, B.kdlaku,
                            ( SELECT RIGHT(HARUS,2) ) X,
                            ( SELECT IF(X>20, (HARUS-X)+120,HARUS ) ) HARUSX
                          FROM beliz A, belizd B
                          LEFT JOIN brgdt C ON C.KD_BRG=B.KD_BRG
                          LEFT JOIN brg D ON D.KD_BRG=B.KD_BRG
                          WHERE A.no_bukti = B.no_bukti
                          and A.TGL=CURDATE() AND A.flag='B3'
                          GROUP BY B.KD_BRG
                          HAVING HJX<>HARUSX
                          ORDER BY A.FLAG, A.NO_BUKTI, B.KD_BRG ASC";
            } else if ($tipe == 'B5') {
                $query = "SELECT A.GOLONGAN, A.FLAG, A.no_bukti,B.KD_BRG,
                            concat(B.NA_BRG,' ',B.KET_UK) NA_BRG, B.hj HJX,
                            if ( A.GOLONGAN='P0',0,0.1 ) PX,
                            (SELECT B.harga * PX) ppnx,
                            (SELECT round((B.harga+ppnx)*(B.MARGIN/100)) ) marginx,
                            (SELECT ROUND(B.harga+ppnx+marginx) ) BULAT,
                            (SELECT ROUND(BULAT/10)*10 ) HARUS, B.kdlaku,
                            ( SELECT RIGHT(HARUS,2) ) X,
                            ( SELECT IF(X>20, (HARUS-X)+120,HARUS ) ) HARUSX
                          FROM beliz A, belizd B
                          WHERE A.no_bukti = B.no_bukti
                          and A.TGL=CURDATE() AND A.flag='B5'
                          GROUP BY B.KD_BRG
                          HAVING HJX<>HARUSX
                          ORDER BY A.FLAG, A.NO_BUKTI, B.KD_BRG ASC";
            } else if ($tipe == 'B8') {
                $query = "SELECT A.GOLONGAN, A.FLAG, A.no_bukti,B.KD_BRG,
                            concat(B.NA_BRG,' ',B.KET_UK) NA_BRG, B.kdlaku, B.hj HJX,
                            round((((((B.harga*(100-B.diskon1)/100)*
                            (100-B.diskon2)/100)* (100-B.diskon3)/100))*(100+B.MARGIN)/100)*((if (
                            (A.golongan='P0') or (A.golongan='P1') ,110,100))/100)) as HARUS,
                            ( SELECT RIGHT(HARUS,2) ) X,
                            ( SELECT IF(X>50, (HARUS-X)+120,HARUS ) ) HARUSX
                          FROM beliz A, belizd B
                          WHERE A.no_bukti = B.no_bukti
                          and A.TGL=CURDATE() AND A.flag='B8'
                          GROUP BY B.KD_BRG
                          HAVING HJX<>HARUSX
                          ORDER BY A.FLAG, A.NO_BUKTI, B.KD_BRG ASC";
            } else {
                return [];
            }

            return DB::select($query);
        } catch (\Exception $e) {
            Log::error('Error in getPenerimaanTipeReport: ' . $e->getMessage());
            return [];
        }
    }

    public function jasperPenerimaanGudangReport(Request $request)
    {
        $reportType = $request->report_type ?? 1;
        $cbgCode = $request->cbg;
        $periode = $request->per;
        $tanggal = $request->tgl;
        $tipe = $request->tipe;

        $file = ($reportType == 1) ? 'rpenerimaan_gudang_detail' : 'rpenerimaan_gudang_tipe';

        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

        session()->put('filter_cbg', $cbgCode);
        session()->put('filter_per', $periode);
        session()->put('filter_tgl', $tanggal);
        session()->put('filter_tipe', $tipe);
        session()->put('report_type', $reportType);

        $data = [];

        if ($reportType == 1 && !empty($cbgCode) && !empty($periode) && !empty($tanggal)) {
            $results = $this->getPenerimaanDetailReport($cbgCode, $periode, $tanggal);

            foreach ($results as $row) {
                $data[] = [
                    'NO_BUKTI' => $row->no_bukti ?? '',
                    'TGL' => $row->tgl ?? '',
                    'KD_BRG' => $row->kd_brg ?? '',
                    'NA_BRG' => $row->na_brg ?? '',
                    'KET_UK' => $row->ket_uk ?? '',
                    'KET_KEM' => $row->ket_kem ?? '',
                    'LPH' => $row->lph ?? '',
                    'QTY' => $row->qty ?? 0,
                    'HARGA' => $row->harga ?? 0,
                    'TOTAL' => $row->total ?? 0,
                    'NOTES' => $row->notes ?? '',
                    'USRNM' => $row->usrnm ?? '',
                    'CBG' => $cbgCode,
                    'PERIODE' => $periode,
                    'TANGGAL' => $tanggal,
                    'REPORT_TYPE' => 'Laporan Penerimaan Gudang Detail',
                ];
            }
        } else if ($reportType == 2 && !empty($tipe)) {
            $results = $this->getPenerimaanTipeReport($tipe);

            foreach ($results as $row) {
                $data[] = array_merge((array) $row, [
                    'TIPE' => $tipe,
                    'REPORT_TYPE' => 'Laporan Penerimaan Gudang Tipe ' . $tipe,
                ]);
            }
        }

        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I");
    }

    public function apiGetPenerimaanGudangData(Request $request)
    {
        try {
            $cbgCode = $request->cbg;
            $periode = $request->per;
            $tanggal = $request->tgl;
            $tipe = $request->tipe;
            $reportType = $request->report_type ?? 1;

            $hasil = [];

            if ($reportType == 1) {
                if (empty($cbgCode) || empty($periode) || empty($tanggal)) {
                    return response()->json(['error' => 'Parameter tidak lengkap untuk detail report'], 400);
                }
                $hasil = $this->getPenerimaanDetailReport($cbgCode, $periode, $tanggal);
            } else {
                if (empty($tipe)) {
                    return response()->json(['error' => 'Tipe harus dipilih'], 400);
                }
                $hasil = $this->getPenerimaanTipeReport($tipe);
            }

            return response()->json([
                'success' => true,
                'data' => $hasil,
                'report_type' => $reportType,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in apiGetPenerimaanGudangData: ' . $e->getMessage());
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
