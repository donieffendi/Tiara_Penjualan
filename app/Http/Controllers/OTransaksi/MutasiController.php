<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use App\Models\Master\Cbg;
use App\Models\Master\Perid;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

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
            'cbg' => $cbg,
            'per' => $per,
            'hasilMutasi' => []
        ]);
    }

    public function getMutasiReport(Request $request)
    {
        $listCbg = DB::SELECT("SELECT KODE FROM toko WHERE STA IN ('MA','CB','DC') ORDER BY NO_ID ASC");
        $listPer = DB::SELECT("SELECT PERIO FROM perid ORDER BY PERIO ASC");
        $sub = $request->sub ?? '';
        $tab = $request->tab ?? 'detail';

        switch ($tab) {

            case 'detail':
                if (empty($request->cbg)) {
                    return view('otransaksi_mutasi.index')->with([
                        'cbg' => $listCbg,
                        'per' => $listPer,
                        'hasilMutasi' => [],
                        'error' => 'Cabang harus dipilih untuk tab Detail.',
                        'tab' => $tab
                    ]);
                }
                $hasilMutasi = $this->getDetailMutasi($request->cbg);
                break;

            case 'summary':
                if (empty($request->cbg)) {
                    return view('otransaksi_mutasi.index')->with([
                        'cbg' => $listCbg,
                        'hasilMutasi' => [],
                        'error' => 'Cabang harus dipilih untuk tab Per Minggu.',
                        'tab' => $tab
                    ]);
                }
                $hasilMutasi = $this->getSummaryMutasi($request->cbg);
                break;
        }

        return view('otransaksi_mutasi.index')->with([
            'cbg' => $listCbg,
            'per' => $listPer,
            'hasilMutasi' => $hasilMutasi,
            'tab' => $tab
        ]);
    }

    public function getMutasiReportAjax(Request $request)
    {   
        $supp = $request->supp ?? '';
        $sub = $request->sub ?? '';
        $item = $request->item ?? '';
        $kode = $request->kode ?? '';
        $bcd = $request->bcd ?? '';
        $nama = $request->nama ?? '';
        $tab = $request->tab ?? 'detail';
        $cbg = $request->cbg ?? '';
        $transit = $request->transit ?? 0;
        $toko = $request->toko ?? 0;
        $subonly = $request->subonly ?? 0;
        // dd($request->all());
        try {
            switch ($tab) {
                case 'detail':
                    if (!$cbg) return response()->json(['success'=>false,'message'=>'CBG wajib'],400);
                    $data = $this->getDetailMutasi($cbg, $supp, $sub, $item, $bcd, $nama);
                    break;

                case 'summary':
                    if (!$cbg) return response()->json(['success'=>false,'message'=>'CBG wajib'],400);
                    $data = $this->getSummaryMutasi($cbg, $kode, $transit, $toko, $subonly);
                    break;

                default:
                    return response()->json(['success'=>false,'message'=>'Tab tidak dikenal'],400);
            }

            return response()->json(['success'=>true,'data'=>$data]);

        } catch (\Exception $e) {
            // debug sementara
            return response()->json([
                'success'=>false,
                'message'=>$e->getMessage(),
                'trace'=>$e->getTraceAsString()
            ],500);
        }
    }


    /**
     * Generate laporan Jasper - Route: /jasper-kasirbantu-report
     * Implementasi dari logika Delphi untuk generate report
     */
    public function jasperMutasiReport(Request $request)
    {
        $tab = $request->tab ?? 'detail';
        $cbg = $request->cbg;
        $supp = $request->supp ?? '';
        $sub = $request->sub ?? '';
        $item = $request->item ?? '';
        $kode = $request->kode ?? '';
        $bcd = $request->bcd ?? '';
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

        if (!empty($cbg)) {
            if ($tab == 'detail') {
                // Detail Report - repjuald data
                $results = $this->getDetailMutasi($cbg);

                foreach ($results as $row) {
                    $data[] = [
                        'CBG' => $row->CBG ?? '',
                        'SUB' => $row->SUB ?? '',
                        'SUB2' => $row->SUB2 ?? '',
                        'KD_BRG' => $row->KD_BRG ?? '',
                        'NA_BRG' => $row->NA_BRG ?? '',
                        'BARCODE' => $row->BARCODE ?? '',
                        'QTY' => $row->qty ?? 0,
                        'HARGA' => $row->harga ?? 0,
                        'DISKON' => $row->diskon ?? 0,
                        'DISC' => $row->disc ?? 0,
                        'PPN' => $row->ppn ?? '',
                        'NPPN' => $row->nppn ?? 0,
                        'DPP' => $row->dpp ?? 0,
                        'TKP' => $row->tkp ?? 0,
                        'TOTAL' => $row->total ?? 0,
                        'FLAG' => $row->flag ?? '',
                        'TYPE' => $row->type ?? '',
                        'PER' => $row->per ?? '',
                        'KODES' => $row->kodes ?? '',
                        'TGL' => $row->TGL ?? '',
                        'NAMA_TOKO' => $this->getNamaToko($cbg),
                        'REPORT_TYPE' => 'Laporan Detail Sales Manager',
                    ];
                }
            } else {
                // Summary Report - repjual data
                $results = $this->getSummaryMutasi($cbg);

                foreach ($results as $row) {
                    $data[] = array_merge((array) $row, [
                        'NAMA_TOKO' => $this->getNamaToko($cbg),
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
        $sql = "SELECT A.TYPE, A.KD_BRG,A.SUB, A.SUPP, A.KDBAR,A.NA_BRG, A.TARIK, A.MASA_EXP,
                        A.KET_UK,A.KET_KEM, ceiling(1.5*B.LPH*tgz.xx_hitklk(B.KLK)) SRMIN, round(2.5*B.LPH*tgz.xx_hitklk(b.KLK)) SRMAX,B.LPH,
                        B.KLK, IF (B.KDLAKU in ('0','1'), 'Gd. Transit', IF( B.KDLAKU='4','Toko',CONCAT('KODE ',B.KDLAKU) ) ) AS KDLAKU , B.DTR, B.GAK00 AS STOCKG,
                        B.AK00 AS STOCKT, B.RAK00 AS STOCKR, B.GAK00+B.AK00 AS STOK,
                        B.HB,B.HJ, B.LAMBAT, B.PSN AS STATPSN,
                        concat(B.TD_OD,'-',CAT_OD) AS TDOD, A.DTB,
                        IF( ( SELECT KODES FROM SUP_DC_TS WHERE KODES=A.SUPP LIMIT 1 ) IS NULL, '','Y' ) AS SUP_L,
                            ( SELECT KODE_DC FROM sup WHERE KODES=A.SUPP limit 1 ) KIRIM_KE,
                        A.SUPP,A.SP_L,A.SP_LF, A.SP_LZ,IF( A.ON_DC=0,'Y','' ) ON_DC,
                        IF(LEFT(A.NA_BRG,1)='3', B.DTR, C.DTR) DTR_DC, C.DTR2, C.DTR_MANUAL , A.Barcode, A.RETUR, A.KK
                FROM brg A, brgdt B LEFT JOIN BRG_DC_TS C ON B.KD_BRG=C.KD_BRG
                WHERE A.KD_BRG=B.KD_BRG and B.CBG=? AND A.SUPP=? AND A.SUB=? AND A.KD_BRG=? AND A.BARCODE=? AND A.NA_BRG LIKE ?";

        return DB::select($sql, [$cbg, $supp, $sub, $item, $bcd, "%{$nama}%"]);
    }

    private function getSummaryMutasi($cbg, $kode, $transit = 0, $toko = 0, $subonly = 0)
    {
        $results = collect();

        /** =====================================
         * 1. SALDO AWAL
         * ===================================== */
        $saldoAwal = DB::table('saldo_awal')
            ->selectRaw("'SALDO' as TYPE, KD_BRG, TGL, 0 as URT, 0 as MASUK, 0 as KELUAR, 0 as LAIN, SALDO as AWAL")
            ->where('CBG', $cbg)
            ->when($kode, fn($q) => $q->where('KD_BRG', 'like', "$kode%"))
            ->get();

        $results = $results->merge($saldoAwal);


        /** =====================================
         * 2. TRANSIT / PEMBELIAN / GUDANG (jika transit=1)
         * ===================================== */
        if ($transit) {
            $pembelian = DB::table('beliz')
                ->selectRaw("'BELI' as TYPE, KD_BRG, NA_BRG, NO_BUKTI, TGL, URT, QTY as MASUK, 0 as KELUAR, 0 as LAIN, 0 as AWAL")
                ->where('CBG', $cbg)
                ->when($kode, fn($q) => $q->where('KD_BRG', 'like', "$kode%"))
                ->get();

            $results = $results->merge($pembelian);
        }


        /** =====================================
         * 3. TOKO (OO,OD,OT,TS,JL,RF,EC)
         * ===================================== */
        if ($toko) {
            $jual = DB::table('juald')
                ->selectRaw("'JUAL' as TYPE, KD_BRG, NA_BRG, NO_BUKTI, TGL, URT, 0 as MASUK, QTY as KELUAR, 0 as LAIN, 0 as AWAL")
                ->where('CBG', $cbg)
                ->when($kode, fn($q) => $q->where('KD_BRG', 'like', "$kode%"))
                ->get();

            $results = $results->merge($jual);
        }


        /** =====================================
         * 4. RETUR / SUBONLY (VR,GR,OX)
         * ===================================== */
        if ($subonly) {
            $retur = DB::table('returd')
                ->selectRaw("'RETUR' as TYPE, KD_BRG, NA_BRG, NO_BUKTI, TGL, URT, QTY as MASUK, 0 as KELUAR, 0 as LAIN, 0 as AWAL")
                ->where('CBG', $cbg)
                ->when($kode, fn($q) => $q->where('KD_BRG', 'like', "$kode%"))
                ->get();

            $results = $results->merge($retur);
        }


        /** =====================================
         * 5. SORT BY MUTASI (sama Delphi)
         * ===================================== */
        $results = $results->sortBy([
            fn ($r) => $r->KD_BRG,
            fn ($r) => $r->TGL,
            fn ($r) => $r->URT,
        ])->values();


        /** =====================================
         * 6. HITUNG SALDO RUNNING
         * ===================================== */
        $saldoMap = [];

        foreach ($results as $r) {
            $k = $r->KD_BRG;

            if (!isset($saldoMap[$k])) {
                $saldoMap[$k] = $r->AWAL ?? 0;
            } else {
                $saldoMap[$k] = $saldoMap[$k] + ($r->MASUK ?? 0) - ($r->KELUAR ?? 0) + ($r->LAIN ?? 0);
            }

            $r->SALDO = $saldoMap[$k];
        }

        return $results->values();
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
                    $data = $this->getSummaryMutasi($cbg);
                    break;
            }

            if (empty($data)) {
                return response()->json(['message' => 'Tidak ada data'], 200);
            }

            $filename = "kasirbantu_{$tab}_" . date('YmdHis') . ".xlsx";

            return response()->json([
                'success' => true,
                'filename' => $filename,
                'total' => count($data)
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

            $data = $this->getKasirList($cbg);
            $namaToko = $this->getNamaToko($cbg);

            return view('oreport_kasirbantu.preview')->with([
                'data' => $data,
                'cbg' => $cbg,
                'namaToko' => $namaToko,
                'totalRecords' => count($data)
            ]);
        } catch (\Exception $e) {
            Log::error('Error in previewKasir: ' . $e->getMessage());
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}