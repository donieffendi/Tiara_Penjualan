<?php
namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use App\Models\Master\Cbg;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;

class MutasiController extends Controller
{
    /**
     * Halaman utama report - Route: /rkasirbantu
     */
    public function index()
    {
        $cbg = DB::SELECT("SELECT KODE FROM toko WHERE STA IN ('MA','CB','DC') ORDER BY NO_ID ASC");
        $per = DB::SELECT("SELECT PERIO from perid");

        // Initialize session variables
        session()->put('filter_cbg', '');
        session()->put('filter_per', '');
        session()->put('filter_sub', '');
        session()->put('filter_yer', '');
        session()->put('filter_minggu', '');

        return view('otransaksi_mutasi.index')->with([
            'cbg'         => $cbg,
            'per'         => $per,
            'hasilMutasi' => [],
        ]);
    }

    public function getMutasiReport(Request $request)
    {
        $listCbg = DB::SELECT("SELECT KODE FROM toko WHERE STA IN ('MA','CB','DC') ORDER BY NO_ID ASC");
        $listPer = DB::SELECT("SELECT PERIO FROM perid ORDER BY PERIO ASC");
        $sub     = $request->sub ?? '';
        $tab     = $request->tab ?? 'detail';
        dd($tab);

        switch ($tab) {

            case 'detail':
                if (empty($request->cbg)) {
                    return view('otransaksi_mutasi.index')->with([
                        'cbg'         => $listCbg,
                        'per'         => $listPer,
                        'hasilMutasi' => [],
                        'error'       => 'Cabang harus dipilih untuk tab Detail.',
                        'tab'         => $tab,
                    ]);
                }
                $hasilMutasi = $this->getDetailMutasi($request->cbg);
                break;

            case 'summary':
                if (empty($request->cbg)) {
                    return view('otransaksi_mutasi.index')->with([
                        'cbg'         => $listCbg,
                        'hasilMutasi' => [],
                        'error'       => 'Cabang harus dipilih untuk tab Per Minggu.',
                        'tab'         => $tab,
                    ]);
                }
                $hasilMutasi = $this->getSummaryMutasi(
                    $request,
                    $request->cbg,
                    $request->kode ?? '',
                    $request->transit ?? 0,
                    $request->toko ?? 0,
                    $request->subonly ?? 0
                );
                break;
        }
        return view('otransaksi_mutasi.index')->with([
            'cbg'         => $listCbg,
            'per'         => $listPer,
            'hasilMutasi' => $hasilMutasi,
            'tab'         => $tab,
        ]);
    }

    public function getMutasiReportAjax(Request $request)
    {
        $supp    = $request->supp ?? '';
        $sub     = $request->sub ?? '';
        $item    = $request->item ?? '';
        $kode    = $request->kode ?? '';
        $bcd     = $request->bcd ?? '';
        $nama    = $request->nama ?? '';
        $tab     = $request->tab ?? 'detail';
        $cbg     = $request->cbg ?? '';
        $transit = $request->transit ?? 0;
        $toko    = $request->toko ?? 0;
        $subonly = $request->subonly ?? 0;
        try {
            switch ($tab) {
                case 'detail':
                    if (! $cbg) {
                        return response()->json(['success' => false, 'message' => 'CBG wajib'], 400);
                    }

                    $data = $this->getDetailMutasi($cbg, $supp, $sub, $item, $bcd, $nama);
                    break;

                case 'summary':
                    if (! $cbg) {
                        return response()->json(['success' => false, 'message' => 'CBG wajib'], 400);
                    }

                    $data = $this->getSummaryMutasi($request, $cbg, $kode, $transit, $toko, $subonly);

                    break;

                default:
                    return response()->json(['success' => false, 'message' => 'Tab tidak dikenal'], 400);
            }

            return response()->json(['success' => true, 'data' => $data]);

        } catch (\Exception $e) {
            // debug sementara
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ], 500);
        }
    }

    /**
     * Generate laporan Jasper - Route: /jasper-kasirbantu-report
     * Implementasi dari logika Delphi untuk generate report
     */
    public function jasperMutasiReport(Request $request)
    {
        $tab  = $request->tab ?? 'detail';
        $cbg  = $request->cbg;
        $supp = $request->supp ?? '';
        $sub  = $request->sub ?? '';
        $item = $request->item ?? '';
        $kode = $request->kode ?? '';
        $bcd  = $request->bcd ?? '';
        $nama = $request->nama ?? '';

        // Tentukan file report berdasarkan tipe (sesuai dengan struktur Delphi)
        $file = ($tab == 'detail') ? 'mutasi_detail' : 'mutasi_summary';

        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

        // Store filter values in session
        session()->put('filter_cbg', $cbg);
        session()->put('filter_sub', $sub);
        session()->put('filter_item', $item);
        session()->put('filter_kode', $kode);
        session()->put('filter_bcd', $bcd);
        session()->put('filter_nama', $nama);
        session()->put('filter_supp', $supp);
        session()->put('report_type', $tab);

        $data = [];

        if (! empty($cbg)) {
            if ($tab == 'detail') {
                // Detail Report - repjuald data
                $results = $this->getDetailMutasi($cbg);

                foreach ($results as $row) {
                    $data[] = [
                        'CBG'         => $row->CBG ?? '',
                        'SUB'         => $row->SUB ?? '',
                        'SUB2'        => $row->SUB2 ?? '',
                        'KD_BRG'      => $row->KD_BRG ?? '',
                        'NA_BRG'      => $row->NA_BRG ?? '',
                        'BARCODE'     => $row->BARCODE ?? '',
                        'QTY'         => $row->qty ?? 0,
                        'HARGA'       => $row->harga ?? 0,
                        'DISKON'      => $row->diskon ?? 0,
                        'DISC'        => $row->disc ?? 0,
                        'PPN'         => $row->ppn ?? '',
                        'NPPN'        => $row->nppn ?? 0,
                        'DPP'         => $row->dpp ?? 0,
                        'TKP'         => $row->tkp ?? 0,
                        'TOTAL'       => $row->total ?? 0,
                        'FLAG'        => $row->flag ?? '',
                        'TYPE'        => $row->type ?? '',
                        'PER'         => $row->per ?? '',
                        'KODES'       => $row->kodes ?? '',
                        'TGL'         => $row->TGL ?? '',
                        'NAMA_TOKO'   => $this->getNamaToko($cbg),
                        'REPORT_TYPE' => 'Laporan Detail Sales Manager',
                    ];
                }
            } else {
                // Summary Report - repjual data
                $results = $this->getSummaryMutasi(
                    $request,
                    $cbg,
                    $kode,
                    $request->transit ?? 0,
                    $request->toko ?? 0,
                    $request->subonly ?? 0
                );

                foreach ($results as $row) {
                    $data[] = array_merge((array) $row, [
                        'NAMA_TOKO'   => $this->getNamaToko($cbg),
                        'REPORT_TYPE' => 'Laporan Summary Sales Manager',
                    ]);
                }
            }
        }

        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I");
    }

    private function getDetailMutasi($cbg, $supp, $sub, $item, $bcd, $nama)
    {

        $query = DB::table('brg as A')
            ->join('brgdt as B', function ($join) use ($cbg) {
                $join->on('A.KD_BRG', '=', 'B.KD_BRG')
                    ->where('B.CBG', '=', $cbg);
            })
            ->leftJoin('BRG_DC_TS as C', 'B.KD_BRG', '=', 'C.KD_BRG')
            ->selectRaw("
        A.TYPE, A.KD_BRG, A.SUB, A.SUPP, A.KDBAR, A.NA_BRG,
        A.TARIK, A.MASA_EXP, A.KET_UK, A.KET_KEM,
        ceiling(1.5*B.LPH*tgz.xx_hitklk(B.KLK)) SRMIN,
        round(2.5*B.LPH*tgz.xx_hitklk(B.KLK)) SRMAX,
        B.LPH, B.KLK,
        IF (B.KDLAKU in ('0','1'), 'Gd. Transit',
            IF(B.KDLAKU='4','Toko',CONCAT('KODE ',B.KDLAKU))) AS KDLAKU,
        B.DTR, B.GAK00 AS STOCKG, B.AK00 AS STOCKT,
        B.RAK00 AS STOCKR, B.GAK00+B.AK00 AS STOK,
        B.HB, B.HJ, B.LAMBAT, B.PSN AS STATPSN,
        concat(B.TD_OD,'-',CAT_OD) AS TDOD,
        A.DTB,
        IF((SELECT KODES FROM SUP_DC_TS WHERE KODES=A.SUPP LIMIT 1) IS NULL,'','Y') AS SUP_L,
        (SELECT KODE_DC FROM sup WHERE KODES=A.SUPP LIMIT 1) KIRIM_KE,
        A.SUPP, A.SP_L, A.SP_LF, A.SP_LZ,
        IF(A.ON_DC=0,'Y','') ON_DC,
        IF(LEFT(A.NA_BRG,1)='3', B.DTR, C.DTR) DTR_DC,
        C.DTR2, C.DTR_MANUAL,
        A.Barcode, A.RETUR, A.KK
    ");
        if (! empty($supp)) {
            $query->where('A.SUPP', $supp);
        }

        if (! empty($sub)) {
            $query->where('A.SUB', $sub);
        }

        if (! empty($item)) {
            $query->where('A.KD_BRG', $item);
        }

        if (! empty($bcd)) {
            $query->where('A.BARCODE', $bcd);
        }

        if (! empty($nama)) {
            $query->where('A.NA_BRG', 'like', "%{$nama}%");
        }
        return $query->get();
    }

    private function getSummaryMutasi($request, $cbg, $kode, $transit = 0, $toko = 0, $subonly = 0)
    {
        $query2 = [];

        if ($request->session()->has('periode')) {
            $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
        } else {
            $periode = '';
        }

        $bulan = $request->session()->get('periode')['bulan'];
        $tahun = $request->session()->get('periode')['tahun'];
        if ($toko == 1) {
            $query2 = DB::SELECT("SELECT * ,@AKHIR:=@AKHIR+AWAL+MASUK-KELUAR+LAIN AS SALDO FROM
                                    (SELECT 'Saldo Awal' as no_bukti, (SELECT MIN(TGL)
                                    FROM beliz, belizd
                                    WHERE beliz.NO_BUKTI = belizd.NO_BUKTI
                                    AND beliz.CBG='$cbg'
                                    AND beliz.PER='$periode'
                                    AND belizd.KD_BRG='$kode') AS tgl,kd_brg,NA_BRG,IF($bulan=MONTH(NOW()),AW00,AW$bulan) as awal
                                    ,0 as masuk,0 as keluar,0 AS LAIN,'AW' AS FLAG, 0 AS URT
                                    from brgdt where YER='$tahun' and KD_BRG='$kode' and cbg='$cbg'
                                    -- and aw'+bulan+'<>0
                                    UNION ALL

                                    SELECT beliz.no_bukti,beliz.TGL,belizd.KD_BRG,belizd.NA_BRG,0 as awal,
                                    belizd.qty AS MASUK,0 AS KELUAR,0 AS LAIN,beliz.FLAG,1 AS URT
                                    FROM beliz,belizd
                                    WHERE beliz.NO_BUKTI=belizd.NO_BUKTI and
                                    beliz.CBG='$cbg'
                                    AND beliz.PER='$periode' and  ( beliz.flag='BL' or beliz.flag='BZ'  or beliz.flag='BD'
                                    or beliz.flag='B3' or beliz.flag='B5' or beliz.flag='B8'  ) AND
                                    belizd.kd_brg='$kode' AND belizd.qty<>0  and belizd.kdlaku<>'0' and belizd.kdlaku<>'1'
                                UNION ALL
                                -- Order Toko                                                                                                                         '
                                    SELECT stockaz.NO_PO as no_bukti,stockaz.TGL,stockazd.KD_BRG,stockazd.NA_BRG,0 as awal,
                                    stockazd.QTY  AS MASUK, 0 AS KELUAR,0 AS LAIN,stockaz.FLAG,2 AS URT
                                    FROM stockaz,stockazd  WHERE stockaz.NO_BUKTI=stockazd.NO_BUKTI
                                    and stockaz.PER='$periode' and stockaz.cbg='$cbg'  and stockaz.flag='OT'
                                    and stockazd.KD_BRG='$kode' AND stockazd.qty<>0
                                UNION ALL

                                -- OO - Order Outlet
                                    SELECT stockaz.no_bukti,stockaz.TGL,stockazd.KD_BRG,stockazd.NA_BRG,0 as awal,
                                    0 AS MASUK,stockazd.QTY AS KELUAR,0 AS LAIN,stockaz.FLAG,2 AS URT
                                    FROM stockaz,stockazd  WHERE stockaz.NO_BUKTI=stockazd.NO_BUKTI
                                    and stockaz.PER='$periode'  and  stockaz.flag='OO'  and  stockazd.abl<>'GD'
                                    and stockazd.KD_BRG='$kode' and stockaz.cbg='$cbg' AND stockazd.qty<>0
                                UNION ALL
                                -- OD
                                    SELECT stockaz.no_bukti,stockaz.TGL,stockazd.KD_BRG,stockazd.NA_BRG,0 as awal,
                                    0 AS MASUK,stockazd.QTY AS KELUAR,0 AS LAIN,stockaz.FLAG,2 AS URT
                                    FROM stockaz,stockazd  WHERE stockaz.NO_BUKTI=stockazd.NO_BUKTI
                                    and stockaz.PER='$periode'  and  stockaz.flag='OD'
                                    and stockazd.KD_BRG='$kode' and stockaz.cbg='$cbg' AND stockazd.qty<>0
                                UNION ALL
                                -- JT
                                    SELECT stockaz.no_bukti,stockaz.TGL,stockazd.KD_BRG,stockazd.NA_BRG,0 as awal,
                                    stockazd.qty AS MASUK, 0 AS KELUAR,0 AS LAIN,stockaz.FLAG,2 AS URT
                                    FROM stockaz,stockazd WHERE stockaz.NO_BUKTI=stockazd.NO_BUKTI
                                    and stockaz.CBG='$cbg' and stockaz.PER='$periode'  and stockaz.flag='JT' and stockazd.KD_BRG='$kode'
                                    AND stockazd.qty<>0 and  stockazd.kdlaku<>'0' and stockazd.kdlaku<>'1'
                                UNION ALL
                                -- OP
                                    SELECT stockaz.no_bukti,stockaz.TGL,stockazd.KD_BRG,stockazd.NA_BRG,0 as awal,
                                    stockazd.QTY  AS MASUK, 0 AS KELUAR,0 AS LAIN,stockaz.FLAG,2 AS URT
                                    FROM stockaz,stockazd  WHERE stockaz.NO_BUKTI=stockazd.NO_BUKTI and stockaz.PER='$periode'  and
                                    stockaz.flag='OP' AND stockazd.JNS='A' and stockazd.KD_BRG='$kode'
                                    and stockaz.cbg='$cbg'  AND stockazd.qty<>0
                                UNION ALL
                                -- OP
                                    SELECT stockaz.no_bukti,stockaz.TGL,stockazd.KD_BRG,stockazd.NA_BRG,0 as awal,
                                    0  AS MASUK, stockazd.QTY AS KELUAR,0 AS LAIN,stockaz.FLAG,3 AS URT
                                    FROM stockaz,stockazd  WHERE stockaz.NO_BUKTI=stockazd.NO_BUKTI
                                    and stockaz.PER='$periode'  and
                                    stockaz.flag='OP' AND stockazd.JNS='B' AND  stockazd.TYP='T'
                                    and stockazd.KD_BRG='$kode' and stockaz.cbg='$cbg'  AND stockazd.qty<>0
                                -- VR
                                UNION ALL
                                    SELECT retur.no_bukti,retur.TGL,returd.KD_BRG,returd.NA_BRG,0 as awal,
                                    0  AS MASUK, 0 AS KELUAR, returd.qty  AS LAIN,retur.FLAG,5 AS URT
                                    FROM retur,returd
                                    WHERE retur.NO_BUKTI=returd.NO_BUKTI and retur.PER='$periode'
                                    and retur.flag='VR' and returd.KD_BRG='$kode'
                                    and returd.kdlaku='5' and retur.POSTED=1
                                    and retur.cbg='$cbg' AND returd.qty<>0
                                -- GR
                                UNION ALL
                                    SELECT retur.no_bukti,retur.TGL,returd.KD_BRG,returd.NA_BRG,0 as awal,
                                    0  AS MASUK, 0 AS KELUAR, returd.qty  AS LAIN,retur.FLAG,5 AS URT
                                    FROM retur,returd
                                    WHERE retur.NO_BUKTI=returd.NO_BUKTI and retur.PER='$periode'
                                    and retur.flag='GR' and returd.KD_BRG='$kode'
                                    and retur.cbg='$cbg' AND returd.qty<>0 and retur.POSTED=1
                                -- OX
                                UNION ALL
                                    SELECT retur.no_bukti,retur.TGL,returd.KD_BRG,returd.NA_BRG,0 as awal,
                                    0  AS MASUK, 0 AS KELUAR, returd.qty  AS LAIN,retur.FLAG,5 AS URT
                                    FROM retur,returd
                                    WHERE retur.NO_BUKTI=returd.NO_BUKTI
                                    and retur.cbg='$cbg' and retur.PER='$periode'
                                    and  retur.flag='OX' and retur.POSTED=1
                                    and returd.KD_BRG='$kode' and retur.cbg='$cbg'  AND returd.qty<>0
                                    and returd.kdlaku<>'0' and returd.kdlaku<>'1'
                                -- PM
                                UNION ALL
                                    SELECT stockbz.no_bukti,stockbz.TGL,stockbzd.KD_BRG,stockbzd.NA_BRG,0 as awal,
                                    0  AS MASUK, 0 AS KELUAR, stockbzd.qty*-1  AS LAIN,stockbz.FLAG,5 AS URT
                                    FROM stockbz,stockbzd
                                    WHERE stockbz.NO_BUKTI=stockbzd.NO_BUKTI and stockbz.PER='$periode'
                                    and stockbz.flag='PM' and stockbzd.KD_BRG='$kode' and
                                    stockbz.cbg='$cbg'  AND stockbzd.qty<>0
                                -- PM
                                UNION ALL
                                    SELECT stockbz.no_bukti,stockbz.TGL,stockbzd.KD_BRG2 as KD_BRG, stockbzd.NA_BRG2 as NA_BRG,0 as awal,
                                    0  AS MASUK, 0 AS KELUAR, stockbzd.qty2  AS LAIN,stockbz.FLAG,5 AS URT
                                    FROM stockbz,stockbzd WHERE stockbz.NO_BUKTI=stockbzd.NO_BUKTI and stockbz.PER='$periode'
                                    and stockbz.flag='PM' and stockbzd.KD_BRG2='$kode' and stockbz.cbg='$cbg'
                                    AND stockbzd.qty2<>0
                                -- MT - AO - AK
                                UNION ALL
                                    SELECT stockbz.no_bukti,stockbz.TGL,stockbzd.KD_BRG,stockbzd.NA_BRG,0 as awal,
                                    0  AS MASUK, 0 AS KELUAR, stockbzd.qty  AS LAIN,stockbz.FLAG,5 AS URT
                                    FROM stockbz,stockbzd
                                    WHERE stockbz.NO_BUKTI=stockbzd.NO_BUKTI and stockbz.PER='$periode'
                                    and (stockbz.flag='MT' or stockbz.flag='AO' or stockbz.flag='AK')
                                    and stockbzd.KD_BRG='$kode' and stockbz.cbg='$cbg'  AND stockbzd.qty<>0
                                UNION ALL
                                -- TS   | -Toko +Retur | semua kdlaku
                                    SELECT stockbz.no_bukti,stockbz.tgl_posted TGL,stockbzd.KD_BRG,stockbzd.NA_BRG,0 as awal,
                                    0  AS MASUK, stockbzd.qty AS KELUAR, 0  AS LAIN,stockbz.FLAG,5 AS URT
                                    FROM stockbz,stockbzd
                                    WHERE stockbz.NO_BUKTI=stockbzd.NO_BUKTI and
                                    stockbz.CBG='$cbg' and stockbz.PER='$periode'
                                    and stockbz.flag='TS' and stockbzd.KD_BRG='$kode'
                                    AND stockbzd.qty<>0
                                UNION ALL
                                -- JL - RF - EC
                                    SELECT no_bukti,TGL,KD_BRG,NA_BRG,0 as awal, 0 AS MASUK,QTY AS KELUAR,
                                    0 AS LAIN,FLAG,3 AS URT FROM juald$bulan  WHERE  cbg='$cbg' AND  PER='$periode'
                                    AND (FLAG='JL' OR FLAG='RF' OR flag='EC') and juald$bulan.KD_BRG='$kode'
                                UNION ALL
                                -- BS
                                    SELECT  survey.NO_BUKTI, survey.TGL, surveyd.KD_BRG, surveyd.NA_BRG,
                                    0 as awal, surveyd.R_PBL AS MASUK, 0 AS KELUAR, 0  AS LAIN,
                                    survey.flag, 5 AS URT
                                    FROM survey,surveyd
                                    WHERE survey.NO_BUKTI=surveyd.AG_PBL AND survey.POSTED=1
                                    AND surveyd.KDLAKU<>'0' AND surveyd.KDLAKU<>'1' AND survey.flag='BS'
                                    AND survey.PER='$periode' AND survey.CBG='$cbg' AND surveyd.KD_BRG='$kode'
                                UNION ALL
                                -- PS
                                    SELECT  survey.NO_BUKTI, survey.TGL, surveyd.KD_BRG, surveyd.NA_BRG,
                                    0 as awal, surveyd.R_PJL AS MASUK, 0 AS KELUAR, 0  AS LAIN,
                                    survey.flag, 5 AS URT
                                    FROM survey,surveyd
                                    WHERE survey.NO_BUKTI=surveyd.AG_PJL AND survey.POSTED=1
                                    AND surveyd.KDLAKU<>'0' AND surveyd.KDLAKU<>'1' AND survey.flag='PS'
                                    AND survey.PER='$periode' AND survey.CBG='$cbg' AND surveyd.KD_BRG='$kode'
                                ) AS AA  JOIN (SELECT @AKHIR:=0 ) AS BB ON 1=1  ORDER BY (CASE WHEN FLAG = 'AW' THEN 0 ELSE 1 END),
                                TGL,
                                URT,
                                no_bukti ");

        }

        if ($subonly == 1) {
            $query2 = DB::SELECT("SELECT * ,@AKHIR:=@AKHIR+AWAL+MASUK-KELUAR+LAIN AS SALDO
                                FROM
                                ( SELECT '' as tg_smp, 'Saldo Awal' AS no_bukti,'' AS TGL, KD_BRG, NA_BRG,
                                                    RAW$bulan as awal,0  AS MASUK, 0 AS KELUAR,0  AS LAIN,'' AS FLAG,0 AS URT
                                                    FROM brgdt WHERE
                                                BRGDT.cbg='$cbg' and KD_BRG='$kode' AND	RAW$bulan<>0
                                UNION ALL

                                SELECT A.tg_smp, A.no_bukti,A.TGL, B.KD_BRG,B.NA_BRG,
                                0 AS AWAL,0  AS MASUK, 0 AS KELUAR, B.qty  AS LAIN,
                                A.FLAG,1 AS URT
                                FROM stockbz A,stockbzd B
                                WHERE
                                A.NO_BUKTI=B.NO_BUKTI
                                and A.CBG='$cbg' and A.PER='$periode'
                                and A.flag='BS' and B.KD_BRG='$kode' AND B.qty<>0
                                UNION ALL

                                SELECT stockbz.tg_smp, stockbz.no_bukti,stockbz.TGL,stockbzd.KD_BRG,stockbzd.NA_BRG,
                                0 AS AWAL,0  AS MASUK, 0 AS KELUAR, stockbzd.qty  AS LAIN,
                                stockbz.FLAG,1 AS URT
                                FROM stockbz,stockbzd
                                WHERE
                                stockbz.NO_BUKTI=stockbzd.NO_BUKTI
                                and stockbz.CBG='$cbg' and stockbz.PER='$periode'
                                and stockbz.flag='TS' and stockbzd.KD_BRG='$kode' AND stockbzd.qty<>0
                                and ( stockbzd.kdlaku<>'0' and stockbzd.kdlaku<>'1')
                                UNION ALL


                                SELECT beliz.tg_smp, beliz.no_bukti,beliz.TGL,belizd.KD_BRG,belizd.NA_BRG,
                                0 as awal,0  AS MASUK, belizd.qty  AS KELUAR, 0  AS LAIN,beliz.FLAG,3 AS URT
                                FROM beliz,belizd
                                WHERE beliz.NO_BUKTI=belizd.NO_BUKTI
                                and beliz.cbg='$cbg' AND beliz.POSTED=1 and beliz.PER='$periode' and
                                beliz.flag='RB' and belizd.KD_BRG='$kode'
                                AND belizd.qty<>0
                                UNION ALL


                                SELECT retur.tg_smp, retur.no_bukti,retur.TGL,returd.KD_BRG,returd.NA_BRG,
                                0 as awal,0  AS MASUK, 0 AS KELUAR, returd.qty*-1  AS LAIN,retur.FLAG,4 AS URT
                                FROM retur,returd
                                WHERE retur.NO_BUKTI=returd.NO_BUKTI
                                and retur.cbg='$cbg' AND retur.POSTED=1 and retur.PER='$periode'
                                and retur.flag='RG' and returd.KD_BRG='$kode'
                                AND returd.qty<>0
                                UNION ALL


                            SELECT retur.tg_smp, retur.no_bukti,retur.TGL,returd.KD_BRG,returd.NA_BRG,
                                                0 as awal,0  AS MASUK, returd.qty AS KELUAR, 0  AS LAIN,retur.FLAG,5 AS URT
                                                FROM retur,returd
                                                WHERE retur.NO_BUKTI=returd.NO_BUKTI
                                                and retur.cbg='$cbg' AND retur.POSTED=1
                                    and retur.PER='$periode'  and retur.flag='VR'   and returd.KD_BRG='$kode'
                                                AND returd.qty<>0
                                    and returd.kdlaku='5'
                            UNION ALL


                            SELECT retur.tg_smp, retur.no_bukti,retur.TGL,returd.KD_BRG,returd.NA_BRG,
                                    0 AS AWAL,0  AS MASUK, 0 AS KELUAR, returd.qty  AS LAIN,
                                    retur.FLAG,6 AS URT FROM retur,returd
                                    WHERE retur.NO_BUKTI=returd.NO_BUKTI
                                    and retur.CBG='$cbg' AND retur.POSTED=1
                                    and retur.PER='$periode'  and retur.flag='RZ'
                                    and returd.KD_BRG='$kode' AND returd.qty<>0
                                    and returd.kdlaku<>'0' and returd.kdlaku<>'1'
                            UNION ALL


                            SELECT retur.tg_smp, retur.no_bukti,retur.TGL,returd.KD_BRG,returd.NA_BRG,
                                                0 as awal,0  AS MASUK, returd.qty  AS KELUAR, 0  AS LAIN,retur.FLAG,7 AS URT
                                                FROM retur,returd
                                                WHERE retur.NO_BUKTI=returd.NO_BUKTI
                                                and retur.cbg='$cbg' AND retur.POSTED=1 and retur.PER='$periode'
                                                and retur.flag='GR' and returd.KD_BRG='$kode'
                                            AND returd.qty<>0
                            UNION ALL


                            SELECT musnah.tg_smp, musnah.no_bukti,musnah.TGL,musnahd.KD_BRG,musnahd.NA_BRG,
                                                0 as awal,0  AS MASUK, musnahd.qty  AS KELUAR, 0  AS LAIN,musnah.FLAG,8 AS URT
                                                FROM musnah,musnahd
                                                WHERE musnah.NO_BUKTI=musnahd.NO_BUKTI
                                                and musnah.cbg='$cbg' AND musnah.POSTED=1
                                    and musnah.PER='$periode'  and
                                                (musnah.flag='MR' or musnah.flag='MF') and musnahd.KD_BRG='$kode'
                                                AND musnahd.qty<>0
                            UNION ALL


                            SELECT stockbz.tg_smp, stockbz.no_bukti,stockbz.TGL,stockbzd.KD_BRG,stockbzd.NA_BRG,
                                    0 AS AWAL,0  AS MASUK, 0 AS KELUAR, stockbzd.qty AS LAIN,
                                    stockbz.FLAG,9 AS URT FROM stockbz,stockbzd
                                    WHERE stockbz.NO_BUKTI=stockbzd.NO_BUKTI
                                    and stockbz.PER='$periode' and stockbz.flag='KR'
                                    and stockbzd.KD_BRG='$kode'  and stockbz.CBG='$cbg' AND stockbzd.qty<>0
                            ) AS AA
                            JOIN (SELECT @AKHIR:=0 ) AS BB ON 1=1 ORDER BY tgl,urt ASC");

        }

        if ($transit == 1) {

            $query2 = DB::SELECT("SELECT * ,@AKHIR:=@AKHIR+AWAL+MASUK-KELUAR+LAIN AS SALDO FROM
                            (SELECT '' as tg_smp,
                            'Saldo Awal' as no_bukti,'' as tgl,kd_brg,NA_BRG,
                            aw$bulan as awal,0 as masuk,0 as keluar,0 AS LAIN,
                            AW$bulan AS FLAG,0 AS URT from brgd where yer='$tahun'
                            and KD_BRG='$kode' and cbg='$cbg' and aw$bulan<>0
                            UNION ALL

                            SELECT A.tg_smp, A.no_bukti,A.TGL, B.KD_BRG,B.NA_BRG,
                            0 AS AWAL, 0  AS MASUK, B.qty AS KELUAR, 0  AS LAIN,
                            A.FLAG,1 AS URT
                            FROM stockbz A,stockbzd B
                            WHERE
                            A.NO_BUKTI=B.NO_BUKTI
                            and A.CBG='$cbg' and A.PER='$periode'
                            and A.flag='BS' and B.KD_BRG='$kode' AND B.qty<>0
                            UNION ALL

                            SELECT stockbz.tg_smp, stockbz.no_bukti,stockbz.TGL,stockbzd.KD_BRG,stockbzd.NA_BRG,
                            0 AS AWAL,0  AS MASUK, 0 AS KELUAR, stockbzd.qty  AS LAIN,
                            stockbz.FLAG,1 AS URT
                            FROM stockbz, stockbzd
                            WHERE
                            stockbz.NO_BUKTI=stockbzd.NO_BUKTI
                            and stockbz.CBG='$cbg' and stockbz.PER='$periode'
                            and stockbz.flag='TS' and stockbzd.KD_BRG='$kode' AND stockbzd.qty<>0
                            and ( stockbzd.kdlaku='0' or stockbzd.kdlaku='1')

                            UNION ALL

                            SELECT retur.tg_smp, retur.no_bukti,retur.TGL,returd.KD_BRG,returd.NA_BRG,
                            0 as awal,0  AS MASUK, returd.qty AS KELUAR, 0  AS LAIN,retur.FLAG,2 AS URT
                            FROM retur,returd
                            WHERE retur.NO_BUKTI=returd.NO_BUKTI
                            and retur.cbg='$cbg' AND retur.POSTED=1 and retur.PER='$periode'  and
                            ( retur.flag='RR' )  and returd.KD_BRG='$kode'
                            AND returd.qty<>0
                            and ( returd.kdlaku='0' or returd.kdlaku='1')
                            UNION all
                                SELECT beliz.tg_smp, beliz.no_bukti,beliz.TGL,belizd.KD_BRG,belizd.NA_BRG,0 AS AWAL,
                                belizd.qty AS MASUK,0 AS KELUAR,0 AS LAIN,beliz.FLAG,3 AS URT
                                FROM beliz, belizd
                                WHERE beliz.NO_BUKTI=belizd.NO_BUKTI
                                and beliz.CBG='$cbg'
                                AND beliz.PER='$periode' and
                                ( beliz.flag='BL' or beliz.flag='BZ'  or beliz.flag='BD' )  AND belizd.kd_brg='$kode'
                                AND belizd.qty<>0  and ( belizd.kdlaku='0' or belizd.kdlaku='1')
                            UNION all
                                SELECT stockaz.tg_smp, stockaz.NO_PO as no_bukti,stockaz.TGL,stockazd.KD_BRG,
                                stockazd.NA_BRG,0 AS AWAL,0 AS MASUK, stockazd.QTY AS KELUAR,0 AS LAIN,
                                stockaz.FLAG,4 AS URT FROM stockaz, stockazd WHERE stockaz.NO_BUKTI=stockazd.NO_BUKTI
                                and stockaz.PER='$periode'  and stockaz.flag='OT' and stockazd.KD_BRG='$kode'
                                and  stockaz.CBG='$cbg' AND stockazd.qty<>0
                            UNION all
                                SELECT stockaz.tg_smp, stockaz.no_bukti,stockaz.TGL,stockazd.KD_BRG,stockazd.NA_BRG,
                                0 AS AWAL,0 AS MASUK, stockazd.QTY AS KELUAR,0 AS LAIN,
                                stockaz.FLAG,5 AS URT FROM stockaz, stockazd WHERE stockaz.NO_BUKTI=stockazd.NO_BUKTI
                                and stockaz.PER='$periode'  and stockaz.flag='OO' and stockazd.abl='GD'
                                and stockazd.KD_BRG='$kode' and  stockaz.CBG='$cbg' AND stockazd.qty<>0
                            UNION all

                                SELECT stockaz.tg_smp, stockaz.no_bukti,stockaz.TGL,stockazd.KD_BRG,stockazd.NA_BRG,
                                0 AS AWAL,stockazd.qty AS MASUK, 0 AS KELUAR,0 AS LAIN,
                                stockaz.FLAG,6 AS URT
                                FROM stockaz, stockazd
                                WHERE stockaz.NO_BUKTI=stockazd.NO_BUKTI
                                and stockaz.CBG='$cbg'
                                and stockaz.PER='$periode'  and stockaz.flag='JT' and stockazd.KD_BRG='$kode'
                                AND stockazd.qty<>0 and ( stockazd.kdlaku='0' or stockazd.kdlaku='1')
                            UNION all

                                SELECT stockaz.tg_smp, stockaz.no_bukti,stockaz.TGL,stockazd.KD_BRG,stockazd.NA_BRG,
                                0 AS AWAL,0  AS MASUK, stockazd.QTY AS KELUAR,0 AS LAIN,
                                stockaz.FLAG,7 AS URT FROM stockaz, stockazd WHERE stockaz.NO_BUKTI=stockazd.NO_BUKTI
                                and stockaz.PER='$periode'  and stockaz.flag='OP' AND stockazd.JNS='B'
                                AND  stockazd.TYP='G' and stockazd.KD_BRG='$kode' and
                                stockaz.CBG='$cbg' AND stockazd.qty<>0
                            UNION all


                                SELECT retur.tg_smp, retur.no_bukti,retur.TGL,returd.KD_BRG,returd.NA_BRG,
                                0 AS AWAL,0  AS MASUK, 0 AS KELUAR, returd.qty  AS LAIN,
                                retur.FLAG,8 AS URT FROM retur, returd
                                WHERE retur.NO_BUKTI=returd.NO_BUKTI
                                and retur.CBG='$cbg'   and retur.posted=1
                                and retur.PER='$periode'  and ( retur.flag='RZ' OR retur.flag='OX')
                                and returd.KD_BRG='$kode' AND returd.qty<>0
                                and ( returd.kdlaku='0' or returd.kdlaku='1')
                            UNION all

                                SELECT retur.tg_smp, retur.no_bukti,retur.TGL,returd.KD_BRG,returd.NA_BRG,
                                0 AS AWAL,0  AS MASUK, returd.qty *-1 AS KELUAR, 0 AS LAIN,
                                retur.FLAG,9 AS URT FROM retur,returd
                                WHERE retur.NO_BUKTI=returd.NO_BUKTI
                                and retur.CBG='$cbg' and retur.posted=1
                                and retur.PER='$periode' and retur.flag='RG' and returd.KD_BRG='$kode'
                                AND returd.qty<>0 and ( returd.kdlaku='0' or returd.kdlaku='1')

                            UNION all
                                SELECT stockbz.tg_smp, stockbz.no_bukti,stockbz.TGL,stockbzd.KD_BRG,stockbzd.NA_BRG,
                                0 AS AWAL,0  AS MASUK, 0 AS KELUAR, stockbzd.qty  AS LAIN,
                                stockbz.FLAG,10 AS URT FROM stockbz,stockbzd WHERE stockbz.NO_BUKTI=stockbzd.NO_BUKTI
                                and stockbz.PER='$periode' and ( stockbz.flag='GS' OR stockbz.flag='MG' )
                                and stockbzd.KD_BRG='$kode'  and stockbz.CBG='$cbg' AND stockbzd.qty<>0
                            UNION ALL
                                SELECT  survey.tg_smp, survey.NO_BUKTI, survey.TGL, surveyd.KD_BRG, surveyd.NA_BRG,
                                0 as awal, surveyd.R_PBL AS MASUK, 0 AS KELUAR, 0  AS LAIN,
                                survey.flag, 11 AS URT FROM survey,surveyd
                                WHERE survey.NO_BUKTI=surveyd.AG_PBL and survey.posted=1
                                AND (surveyd.KDLAKU='0' OR surveyd.KDLAKU='1') AND survey.flag='BS'
                                AND survey.PER='$periode' AND survey.CBG='$cbg' AND surveyd.KD_BRG='$kode'
                            UNION ALL
                                SELECT  survey.tg_smp, survey.NO_BUKTI, survey.TGL, surveyd.KD_BRG, surveyd.NA_BRG,
                                0 as awal, surveyd.R_PJL AS MASUK, 0 AS KELUAR, 0  AS LAIN,
                                survey.flag, 12 AS URT FROM survey,surveyd
                                WHERE survey.NO_BUKTI=surveyd.AG_PJL  and survey.posted=1
                                AND (surveyd.KDLAKU='0' OR surveyd.KDLAKU='1') AND survey.flag='PS'
                                AND survey.PER='$periode' AND survey.CBG='$cbg' AND surveyd.KD_BRG='$kode'
                            ) AS AA
                        JOIN (SELECT @AKHIR:=0 ) AS BB ON 1=1
                        ORDER BY tg_smp asc, urt asc");
        }

        session()->put('filter_per', $periode);
        session()->put('filter_kode', $kode);

        return $query2;

        // if ($request->has('filter')) {
        //     $per = Perid::query()->get();
        //     return view('otransaksi_mutasi.index')->with(['per' => $per])->with(['hasil' => $query2]);
        // }

        // $file         = 'karstk_mutasi';
        // $PHPJasperXML = new PHPJasperXML();
        // $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));
        // $data = [];
        // foreach ($query2 as $key => $value) {
        //     array_push($data, [
        //         'NO_BUKTI' => $query2[$key]->no_bukti,
        //         'TGL'      => $query2[$key]->TGL,
        //         'kd_brg'   => $query2[$key]->kd_brg,
        //         'CBG'      => $query2[$key]->CBG,
        //         'NA_BRG'   => $query2[$key]->NA_BRG,
        //         'URAIAN'   => $query2[$key]->URAIAN,
        //         'awal'     => $query2[$key]->awal,
        //         'MASUK'    => $query2[$key]->masuk,
        //         'KELUAR'   => $query2[$key]->keluar,
        //         'LAIN'     => $query2[$key]->LAIN,
        //         'AKHIR'    => $query2[$key]->SALDO,
        //     ]);
        // }
        // $PHPJasperXML->setData($data);
        // ob_end_clean();
        // $PHPJasperXML->outpage("I");
    }

    /**
     * Get daftar cabang yang valid
     */
    public function getCabangList()
    {
        try {
            $query = "
                SELECT KODE, STA
                FROM toko
                WHERE STA IN ('MA', 'CB', 'DC')
                ORDER BY NO_ID ASC
            ";

            return DB::select($query);
        } catch (\Exception $e) {
            Log::error('Error in getCabangList: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Export ke Excel
     */
    public function exportToExcel(Request $request)
    {
        try {
            $tab = $request->tab ?? 'detail';
            $cbg = $request->cbg ?? '';

            switch ($tab) {

                case 'detail':
                    if (empty($cbg)) {
                        return response()->json(['error' => 'Cabang harus diisi!'], 400);
                    }
                    $data = $this->getDetailMutasi($cbg);
                    break;

                case 'summary':
                    if (empty($cbg)) {
                        return response()->json(['error' => 'Cabang harus diisi!'], 400);
                    }
                    $data = $this->getSummaryMutasi(
                        $request,
                        $cbg,
                        $request->kode ?? '',
                        $request->transit ?? 0,
                        $request->toko ?? 0,
                        $request->subonly ?? 0
                    );
                    break;
            }

            if (empty($data)) {
                return response()->json(['message' => 'Tidak ada data'], 200);
            }

            $filename = "kasirbantu_{$tab}_" . date('YmdHis') . ".xlsx";

            return response()->json([
                'success'  => true,
                'filename' => $filename,
                'total'    => count($data),
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get nama toko berdasarkan kode cabang
     */
    public function getNamaToko($cbg)
    {
        try {
            $result = DB::table('tgz.toko')
                ->select('NA_TOKO')
                ->where('KODE', $cbg)
                ->first();

            return $result ? $result->NA_TOKO : '';
        } catch (\Exception $e) {
            Log::error('Error in getNamaToko: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Method untuk preview data sebelum print
     */
    public function previewMutasi(Request $request)
    {
        try {
            $cbg = $request->cbg;

            if (empty($cbg)) {
                return redirect()->back()->with('error', 'Cabang harus diisi!');
            }

            $data     = $this->getKasirList($cbg);
            $namaToko = $this->getNamaToko($cbg);

            return view('oreport_kasirbantu.preview')->with([
                'data'         => $data,
                'cbg'          => $cbg,
                'namaToko'     => $namaToko,
                'totalRecords' => count($data),
            ]);
        } catch (\Exception $e) {
            Log::error('Error in previewKasir: ' . $e->getMessage());
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
