<?php

namespace App\Http\Controllers\OReport;

use App\Http\Controllers\Controller;
use App\Models\Master\Cbg;
use App\Models\Master\Perid;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;

class RDiskonHadiahBerjalanController extends Controller
{
    public function report()
    {
        $cbg = Cbg::groupBy('CBG')->get();
        $per = Perid::query()->get();

        // Initialize session variables sesuai dengan logika Delphi
        session()->put('filter_cbg', '');
        session()->put('filter_per', date("m-Y"));
        session()->put('filter_sub1', '');
        session()->put('filter_sub2', '');
        session()->put('filter_periode_hdh', '');
        session()->put('filter_kode_hadiah', '');

        return view('oreport_diskon_hadiah.report')->with([
            'cbg' => $cbg,
            'hasilDiskonBerjalan' => [],
            'hasilHadiahBerjalan' => [],
            'hasilHadiahBerakhir' => [],
            'hasilHadiahKeluar' => [],
            'per' => $per,
        ]);
    }

    public function getDiskonHadiahBerjalanReport(Request $request)
    {
        $cbg = Cbg::groupBy('CBG')->get();
        $per = Perid::query()->get();

        // Get filter values
        $cbgCode = $request->cbg;
        $sub1 = $request->sub1;
        $sub2 = $request->sub2;
        $periodeHdh = $request->periode_hdh;
        $kodeHadiah = $request->kode_hadiah;
        $tipeReport = $request->tipe_report ?? 1; // Default ke diskon berjalan

        // Store in session
        session()->put('filter_cbg', $cbgCode);
        session()->put('filter_sub1', $sub1);
        session()->put('filter_sub2', $sub2);
        session()->put('filter_periode_hdh', $periodeHdh);
        session()->put('filter_kode_hadiah', $kodeHadiah);

        $hasilDiskonBerjalan = [];
        $hasilHadiahBerjalan = [];
        $hasilHadiahBerakhir = [];
        $hasilHadiahKeluar = [];

        if (!empty($cbgCode)) {
            switch ($tipeReport) {
                case 1:
                    $hasilDiskonBerjalan = $this->getDiskonBerjalanData($cbgCode);
                    break;
                case 2:
                    $hasilHadiahBerjalan = $this->getHadiahBerjalanData($cbgCode, $sub1, $sub2);
                    break;
                case 3:
                    $hasilHadiahBerakhir = $this->getHadiahBerakhirData($cbgCode);
                    break;
                case 4:
                    $hasilHadiahKeluar = $this->getHadiahKeluarData($periodeHdh, $kodeHadiah);
                    break;
            }
        }

        return view('oreport_diskon_hadiah.report')->with([
            'cbg' => $cbg,
            'hasilDiskonBerjalan' => $hasilDiskonBerjalan,
            'hasilHadiahBerjalan' => $hasilHadiahBerjalan,
            'hasilHadiahBerakhir' => $hasilHadiahBerakhir,
            'hasilHadiahKeluar' => $hasilHadiahKeluar,
            'per' => $per,
            'tipeReport' => $tipeReport,
        ]);
    }

    // Sesuai dengan tampil procedure di Delphi
    private function getDiskonBerjalanData($cbgCode)
    {
        // Query sesuai dengan tampil procedure di Delphi
        $query = "SELECT
            B.KD_BRG,
            CONCAT(B.NA_BRG, ' ', B.KET_UK) as NA_BRG,
            B.KET_UK,
            A.no_bukti,
            A.tgl_mulai,
            A.tgl_sls,
            B.TH,
            C.HJ,
            C.HJ - B.TH as HJX,
            :cbg as cbg
        FROM {$cbgCode}.DIS A
        LEFT JOIN {$cbgCode}.disd B ON B.no_bukti = A.no_bukti
        LEFT JOIN {$cbgCode}.brgdt C ON C.KD_BRG = B.KD_BRG
        WHERE A.TGL_MULAI <= DATE(NOW())
        AND A.TGL_SLS >= DATE(NOW())
        AND A.CBG = :cbg_code
        ORDER BY no_bukti, kd_brg ASC";

        return DB::select($query, [
            'cbg' => $cbgCode,
            'cbg_code' => $cbgCode
        ]);
    }

    // Sesuai dengan Panel6Click di Delphi
    private function getHadiahBerjalanData($cbgCode, $sub1, $sub2)
    {
        if (empty($sub1) || empty($sub2)) {
            $sub1 = '';
            $sub2 = 'ZZZZZZ'; // Default range
        }

        $query = "SELECT
            :cbg as cbg,
            lbhijau.NO_BUKTI,
            IF(lbhijau.TYPE='V','VARIAN',
                IF(lbhijau.TYPE='H','HIJAU',
                    IF(lbhijau.TYPE='B','BANK',
                        IF(lbhijau.TYPE='U','UANG','')))) as jenis,
            IF(lbhijau.kondisi='H','TOTAL BELANJA SEMUA BARANG',
                'TOTAL BELANJA BARANG PROMO') as kondisi,
            lbhijau.qty_beli,
            lbhijau.rp_beli,
            IF(lbhijau.kelipatan=1,'BERLAKU KELIPATAN',
                'TIDAK BERLAKU KELIPATAN') as keli,
            lbhijau.tg_mulai,
            lbhijau.tg_akhir,
            UPPER(lbhijau.ket) as ket,
            masks.kd_brg,
            masks.na_brg,
            masks.ket_uk,
            lbhijaud.KD_BRGH,
            lbhijaud.NA_BRGH
        FROM {$cbgCode}.lbhijau
        LEFT JOIN {$cbgCode}.masks ON (lbhijau.NO_BUKTI = masks.HS OR lbhijau.NO_BUKTI = masks.HV)
        LEFT JOIN {$cbgCode}.lbhijaud ON lbhijau.NO_BUKTI = lbhijaud.NO_BUKTI
        WHERE lbhijau.TG_MULAI <= DATE(NOW())
        AND lbhijau.TG_AKHIR >= DATE(NOW())
        AND masks.SUB BETWEEN :sub1 AND :sub2
        AND lbhijau.{$cbgCode} = 1
        AND lbhijaud.KD_BRGH != ''
        ORDER BY no_bukti, kd_brg";

        return DB::select($query, [
            'cbg' => $cbgCode,
            'sub1' => $sub1,
            'sub2' => $sub2
        ]);
    }

    // Sesuai dengan Panel11Click di Delphi
    private function getHadiahBerakhirData($cbgCode)
    {
        $query = " SELECT '$cbgCode' as cbg, lbhijau.NO_BUKTI, lbhijau.per,                 
                        IF(lbhijau.TYPE='V','VARIAN',                               
                        IF(lbhijau.TYPE='H','HIJAU',IF(lbhijau.TYPE='B',          
                        'BANK',IF(lbhijau.TYPE='U','UANG',''))))as jenis,       
                        if(lbhijau.kondisi='H','TOTAL BELANJA SEMUA BARANG',       
                        'TOTAL BELANJA BARANG PROMO') as kondisi,                     
                        lbhijau.qty_beli,lbhijau.rp_beli,                              
                        if(lbhijau.kelipatan=1,'BERLAKU KELIPATAN',                   
                        'TIDAK BERLAKU KELIPATAN') as keli,                           
                        lbhijau.tg_mulai,lbhijau.tg_akhir,UPPER(lbhijau.ket) as ket,   
                        brg.kd_brg, brg.na_brg, brg.ket_uk,                             
                        lbhijaud.KD_BRGH,lbhijaud.NA_BRGH                               
                    FROM lbhijau,lbhijaud,masks                                        
                    LEFT JOIN brg on masks.KD_BRG=brg.KD_BRG                           
                    WHERE lbhijau.NO_BUKTI=lbhijaud.NO_BUKTI                           
                        AND if(lbhijau.TYPE='V',lbhijau.KD_PRM=                       
                            masks.hv,lbhijau.KD_PRM=masks.hs)                           
                        AND lbhijau.TG_AKHIR<=DATE(NOW()) and lbhijau.$cbgCode=1          
                        ORDER BY lbhijau.no_bukti, brg.kd_brg  ";

        return DB::select($query);
    }

    // Sesuai dengan btnTampil4Click di Delphi
    private function getHadiahKeluarData($periode, $kodeHadiah)
    {
        if (empty($periode) || empty($kodeHadiah)) {
            return [];
        }

        // Ambil cbg dari session atau default
        $cbgCode = session('filter_cbg', '');
        if (empty($cbgCode)) {
            return [];
        }

        $query = "SELECT
            A.NO_BUKTI,
            A.TGL,
            A.NO_PO,
            C.KODES,
            C.NAMAS,
            B.KD_BRGH,
            B.NA_BRGH,
            SUM(B.qty) as QTY
        FROM {$cbgCode}.hdh A
        LEFT JOIN {$cbgCode}.hdhd B ON A.NO_BUKTI = B.no_bukti
        LEFT JOIN {$cbgCode}.brgh C ON B.KD_BRGH = C.KD_BRGH
        WHERE A.per = :per
        AND A.FLAG = 'HK'
        AND C.KD_BRGH = :kd_brgh
        GROUP BY A.TGL, B.KD_BRGH
        ORDER BY A.TGL ASC";

        return DB::select($query, [
            'per' => $periode,
            'kd_brgh' => $kodeHadiah
        ]);
    }

    // Method untuk mendapatkan nama toko
    private function getNamaToko($cbgCode)
    {
        $result = DB::select("SELECT NA_TOKO from {$cbgCode}.toko WHERE toko.KODE = :cbg", ['cbg' => $cbgCode]);
        return $result[0]->NA_TOKO ?? '';
    }

    // Method untuk mendapatkan info barang hadiah
    public function getInfoBarangHadiah($kodeHadiah, $cbgCode)
    {
        if (empty($kodeHadiah) || empty($cbgCode)) {
            return null;
        }

        $query = "SELECT KODES, NAMAS FROM {$cbgCode}.brgh WHERE KD_BRGH = :kd_brgh";
        $result = DB::select($query, ['kd_brgh' => $kodeHadiah]);

        return $result[0] ?? null;
    }

    public function jasperDiskonHadiahBerjalanReport(Request $request)
    {
        $tipeReport = $request->tipe_report ?? 1;
        $file = '';

        // Tentukan file jasper berdasarkan tipe report
        switch ($tipeReport) {
            case 1:
                $file = 'rdiskon_berjalan';
                break;
            case 2:
                $file = 'rhadiah_berjalan';
                break;
            case 3:
                $file = 'rhadiah_berakhir';
                break;
            case 4:
                $file = 'rhadiah_keluar';
                break;
            default:
                $file = 'rdiskon_berjalan';
        }

        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

        // Store filter values in session
        $cbgCode = $request->cbg;
        $sub1 = $request->sub1;
        $sub2 = $request->sub2;
        $periodeHdh = $request->periode_hdh;
        $kodeHadiah = $request->kode_hadiah;

        session()->put('filter_cbg', $cbgCode);
        session()->put('filter_sub1', $sub1);
        session()->put('filter_sub2', $sub2);
        session()->put('filter_periode_hdh', $periodeHdh);
        session()->put('filter_kode_hadiah', $kodeHadiah);

        // Get data based on report type
        $data = [];
        if (!empty($cbgCode)) {
            switch ($tipeReport) {
                case 1:
                    $results = $this->getDiskonBerjalanData($cbgCode);
                    foreach ($results as $row) {
                        $data[] = [
                            'KD_BRG' => $row->KD_BRG ?? '',
                            'NA_BRG' => $row->NA_BRG ?? '',
                            'KET_UK' => $row->KET_UK ?? '',
                            'NO_BUKTI' => $row->no_bukti ?? '',
                            'TGL_MULAI' => $row->tgl_mulai ?? '',
                            'TGL_SLS' => $row->tgl_sls ?? '',
                            'TH' => $row->TH ?? 0,
                            'HJ' => $row->HJ ?? 0,
                            'HJX' => $row->HJX ?? 0,
                            'CBG' => $row->cbg ?? '',
                            'NAMA_TOKO' => $this->getNamaToko($cbgCode),
                        ];
                    }
                    break;

                case 2:
                    $results = $this->getHadiahBerjalanData($cbgCode, $sub1, $sub2);
                    foreach ($results as $row) {
                        $data[] = [
                            'CBG' => $row->cbg ?? '',
                            'NO_BUKTI' => $row->NO_BUKTI ?? '',
                            'JENIS' => $row->jenis ?? '',
                            'KONDISI' => $row->kondisi ?? '',
                            'QTY_BELI' => $row->qty_beli ?? 0,
                            'RP_BELI' => $row->rp_beli ?? 0,
                            'KELIPATAN' => $row->keli ?? '',
                            'TG_MULAI' => $row->tg_mulai ?? '',
                            'TG_AKHIR' => $row->tg_akhir ?? '',
                            'KET' => $row->ket ?? '',
                            'KD_BRG' => $row->kd_brg ?? '',
                            'NA_BRG' => $row->na_brg ?? '',
                            'KET_UK' => $row->ket_uk ?? '',
                            'KD_BRGH' => $row->KD_BRGH ?? '',
                            'NA_BRGH' => $row->NA_BRGH ?? '',
                            'NAMA_TOKO' => $this->getNamaToko($cbgCode),
                        ];
                    }
                    break;

                case 3:
                    $results = $this->getHadiahBerakhirData($cbgCode);
                    foreach ($results as $row) {
                        $data[] = [
                            'CBG' => $row->cbg ?? '',
                            'NO_BUKTI' => $row->NO_BUKTI ?? '',
                            'PER' => $row->per ?? '',
                            'JENIS' => $row->jenis ?? '',
                            'KONDISI' => $row->kondisi ?? '',
                            'QTY_BELI' => $row->qty_beli ?? 0,
                            'RP_BELI' => $row->rp_beli ?? 0,
                            'KELIPATAN' => $row->keli ?? '',
                            'TG_MULAI' => $row->tg_mulai ?? '',
                            'TG_AKHIR' => $row->tg_akhir ?? '',
                            'KET' => $row->ket ?? '',
                            'KD_BRG' => $row->kd_brg ?? '',
                            'NA_BRG' => $row->na_brg ?? '',
                            'KET_UK' => $row->ket_uk ?? '',
                            'KD_BRGH' => $row->KD_BRGH ?? '',
                            'NA_BRGH' => $row->NA_BRGH ?? '',
                            'NAMA_TOKO' => $this->getNamaToko($cbgCode),
                            'TGL_CTK' => date('d/m/Y'),
                        ];
                    }
                    break;

                case 4:
                    $results = $this->getHadiahKeluarData($periodeHdh, $kodeHadiah);
                    $infoBarang = $this->getInfoBarangHadiah($kodeHadiah, $cbgCode);

                    foreach ($results as $row) {
                        $data[] = [
                            'NO_BUKTI' => $row->NO_BUKTI ?? '',
                            'TGL' => $row->TGL ?? '',
                            'NO_PO' => $row->NO_PO ?? '',
                            'KODES' => $row->KODES ?? '',
                            'NAMAS' => $row->NAMAS ?? '',
                            'KD_BRGH' => $row->KD_BRGH ?? '',
                            'NA_BRGH' => $row->NA_BRGH ?? '',
                            'QTY' => $row->QTY ?? 0,
                            'PERIODE' => $periodeHdh,
                            'KODE_HADIAH' => $kodeHadiah,
                            'INFO_BARANG' => ($infoBarang ? $infoBarang->KODES . ' - ' . $infoBarang->NAMAS : ''),
                            'NAMA_TOKO' => $this->getNamaToko($cbgCode),
                            'TGL_CTK' => date('d/m/Y'),
                        ];
                    }
                    break;
            }
        }
		
		//dd($data);

        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I");
    }

    // API endpoints untuk mendukung AJAX calls dari frontend
    public function apiGetDiskonHadiahBerjalan(Request $request)
    {
        try {
            $cbgCode = $request->cbg;
            $tipeReport = $request->tipe_report ?? 1;
            $sub1 = $request->sub1 ?? '';
            $sub2 = $request->sub2 ?? '';
            $periodeHdh = $request->periode_hdh ?? '';
            $kodeHadiah = $request->kode_hadiah ?? '';

            if (empty($cbgCode)) {
                return response()->json(['error' => 'Cabang harus dipilih'], 400);
            }

            $hasil = [];
            switch ($tipeReport) {
                case 1:
                    $hasil = $this->getDiskonBerjalanData($cbgCode);
                    break;
                case 2:
                    $hasil = $this->getHadiahBerjalanData($cbgCode, $sub1, $sub2);
                    break;
                case 3:
                    $hasil = $this->getHadiahBerakhirData($cbgCode);
                    break;
                case 4:
                    if (empty($periodeHdh) || empty($kodeHadiah)) {
                        return response()->json(['error' => 'Periode dan Kode Hadiah harus diisi'], 400);
                    }
                    $hasil = $this->getHadiahKeluarData($periodeHdh, $kodeHadiah);
                    $infoBarang = $this->getInfoBarangHadiah($kodeHadiah, $cbgCode);

                    return response()->json([
                        'success' => true,
                        'data' => $hasil,
                        'info_barang' => $infoBarang
                    ]);
                    break;
            }

            return response()->json([
                'success' => true,
                'data' => $hasil
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function apiGetDetailDiskonHadiahBerjalan(Request $request)
    {
        try {
            $cbgCode = $request->cbg;
            $noBukti = $request->no_bukti;
            $tipeReport = $request->tipe_report ?? 1;

            if (empty($cbgCode) || empty($noBukti)) {
                return response()->json(['error' => 'Cabang dan No Bukti harus diisi'], 400);
            }

            $detail = $this->getDetailData($cbgCode, $noBukti, $tipeReport);

            return response()->json([
                'success' => true,
                'data' => $detail
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getDetailData($cbgCode, $noBukti, $tipeReport)
    {
        $details = [];

        if ($tipeReport == 1) {
            // Detail diskon
            $query = "SELECT
                A.no_bukti,
                A.tgl_mulai,
                A.tgl_sls,
                A.ket,
                B.KD_BRG,
                B.NA_BRG,
                B.KET_UK,
                B.TH,
                C.HJ,
                C.HJ - B.TH as SELISIH
            FROM {$cbgCode}.DIS A
            LEFT JOIN {$cbgCode}.disd B ON A.no_bukti = B.no_bukti
            LEFT JOIN {$cbgCode}.brgdt C ON B.KD_BRG = C.KD_BRG
            WHERE A.no_bukti = :no_bukti";

            $details = DB::select($query, ['no_bukti' => $noBukti]);
        } else {
            // Detail hadiah
            $query = "SELECT
                A.NO_BUKTI,
                A.TYPE,
                A.kondisi,
                A.qty_beli,
                A.rp_beli,
                A.kelipatan,
                A.tg_mulai,
                A.tg_akhir,
                A.ket,
                B.KD_BRGH,
                B.NA_BRGH
            FROM {$cbgCode}.lbhijau A
            LEFT JOIN {$cbgCode}.lbhijaud B ON A.NO_BUKTI = B.NO_BUKTI
            WHERE A.NO_BUKTI = :no_bukti";

            $details = DB::select($query, ['no_bukti' => $noBukti]);
        }

        return $details;
    }

    public function apiGetThermalPrintDiskonHadiahBerjalan(Request $request)
    {
        try {
            // Untuk sementara return empty karena tidak ada logic thermal print di Delphi code
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
