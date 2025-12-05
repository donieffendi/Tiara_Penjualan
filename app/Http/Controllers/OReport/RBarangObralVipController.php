<?php

namespace App\Http\Controllers\OReport;

use App\Http\Controllers\Controller;
use App\Models\Master\Cbg;
use App\Models\Master\Perid;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;

class RBarangObralVipController extends Controller
{
    public function report()
    {
        // Get CBG data using query instead of model
        // $cbg = $this->getCbgList();
        $cbg = Cbg::groupBy('CBG')->get();

        // Get periods using query instead of model
        // $periods = $this->getPeriodsList();
        $periods = Perid::query()->get();

        // Initialize session variables
        session()->put('filter_cbg', '');
        session()->put('filter_tanggal', '');
        session()->put('filter_periode', '');
        session()->put('filter_jam', '');
        session()->put('report_type', 1); // 1=Kode 3&8, 2=Food Center, 3=VIP, 4=Borong
        session()->put('filter_all', false);

        return view('oreport_barang_obran_vip.report')->with([
            'cbg' => $cbg,
            'periods' => $periods,
            'hasilKode38' => [],
            'hasilFoodCenter' => [],
            'hasilVip' => [],
            'hasilBorong' => [],
        ]);
    }

    public function getBarangObralVipReport(Request $request)
    {
        $cbg = Cbg::groupBy('CBG')->get();
        $periods = Perid::query()->get();

        // Get filter values
        $cbgCode = $request->cbg;
        $tanggal = $request->tanggal;
        $periode = $request->periode;
        $jam = $request->jam;
        $reportType = $request->report_type ?? 1;
        $all = $request->all ?? false;

        // Store in session
        session()->put('filter_cbg', $cbgCode);
        session()->put('filter_tanggal', $tanggal);
        session()->put('filter_periode', $periode);
        session()->put('filter_jam', $jam);
        session()->put('report_type', $reportType);
        session()->put('filter_all', $all);

        $hasilKode38 = [];
        $hasilFoodCenter = [];
        $hasilVip = [];
        $hasilBorong = [];

        if (!empty($cbgCode)) {
            switch ($reportType) {
                case 1: // Report Kode 3 & 8
                    if (!empty($tanggal) && !empty($periode)) {
                        $hasilKode38 = $this->getObralKode38($cbgCode, $tanggal, $periode);
                    }
                    break;

                case 2: // Report Food Center
                    if (!empty($tanggal) && !empty($periode)) {
                        $hasilFoodCenter = $this->getObralFoodCenter($cbgCode, $tanggal, $periode);
                    }
                    break;

                case 3: // Report VIP
                    if (!empty($periode)) {
                        $hasilVip = $this->getObralVip($cbgCode, $periode);
                    }
                    break;

                case 4: // Report Borong
                    if (!empty($tanggal)) {
                        $hasilBorong = $this->getObralBorong($cbgCode, $tanggal, $jam, $all);
                    }
                    break;
            }
        }

        return view('oreport_barang_obran_vip.report')->with([
            'cbg' => $cbg,
            'periods' => $periods,
            'hasilKode38' => $hasilKode38,
            'hasilFoodCenter' => $hasilFoodCenter,
            'hasilVip' => $hasilVip,
            'hasilBorong' => $hasilBorong,
            'reportType' => $reportType,
        ]);
    }

    /**
     * Get CBG list using direct query instead of model
     */
    private function getCbgList()
    {
        try {
            $query = "SELECT DISTINCT CBG FROM cbg ORDER BY CBG";
            return DB::select($query);
        } catch (\Exception $e) {
            Log::error('Error in getCbgList: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get periods list using direct query instead of model
     */
    private function getPeriodsList()
    {
        try {
            $query = "SELECT per FROM perid ORDER BY per DESC";
            return DB::select($query);
        } catch (\Exception $e) {
            Log::error('Error in getPeriodsList: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Report Obral Kode 3 & 8 (sesuai dengan rbKode8Click di Delphi)
     */
    private function getObralKode38($cbgCode, $tanggal, $periode)
    {
        try {
            // Tentukan periode MM
            $MM = $this->determinePeriode($periode, $tanggal);

            $query = "SELECT
                        'REPORT OBRAL KODE 3,8' AS JUDUL,
                        SUB2 as SUB,
                        KD_BRG,
                        NA_BRG,
                        SUM(qty) as qty,
                        SUM(total) as total,
                        harga
                      FROM juald{$MM}
                      WHERE flag='JL' AND type='KS'
                        AND cbg=?
                        AND diskon<>0
                        AND DATE(TGL)=?
                        AND KD_BRG IN (
                          SELECT disd.KD_BRG
                          FROM dis, disd
                          WHERE disd.no_bukti=DIS.no_bukti
                            AND DIS.CBG=?
                            AND DIS.flag='OB'
                            AND {$cbgCode}=1
                            AND TGL_SLS>=?
                        )
                      GROUP BY KD_BRG
                      ORDER BY KD_BRG";

            $formattedDate = Carbon::parse($tanggal)->format('Y-m-d');
            return DB::select($query, [$cbgCode, $formattedDate, $cbgCode, $formattedDate]);
        } catch (\Exception $e) {
            Log::error('Error in getObralKode38: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Report Obral Food Center (sesuai dengan rbFCClick di Delphi)
     */
    private function getObralFoodCenter($cbgCode, $tanggal, $periode)
    {
        try {
            // Tentukan periode MM
            $MM = $this->determinePeriode($periode, $tanggal);

            $query = "SELECT
                        'REPORT OBRAL FOOD CENTER' AS JUDUL,
                        SUB2 as SUB,
                        KD_BRG,
                        NA_BRG,
                        SUM(qty) as qty,
                        SUM(total) as total,
                        harga
                      FROM juald{$MM}
                      WHERE flag='FC'
                        AND type='KS'
                        AND cbg=?
                        AND diskon <>0
                        AND DATE(TGL)=?
                      GROUP BY KD_BRG
                      ORDER BY KD_BRG";

            $formattedDate = Carbon::parse($tanggal)->format('Y-m-d');
            return DB::select($query, [$cbgCode, $formattedDate]);
        } catch (\Exception $e) {
            Log::error('Error in getObralFoodCenter: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Report VIP (sesuai dengan rbVIPClick di Delphi)
     */
    private function getObralVip($cbgCode, $periode)
    {
        try {
            // Tentukan HJX berdasarkan CBG
            $HJX = $this->getHJXByCbg($cbgCode);

            // Tentukan periode bulan dan tahun
            $bulan = substr(trim($periode), 0, 2);
            $yer = substr(trim($periode), -4);

            // Hitung tanggal last dan now
            $tgllast = Carbon::create($yer, $bulan, 16)->subMonth()->format('Y-m-d');
            $tglnow = Carbon::create($yer, $bulan, 15)->format('Y-m-d');

            // Tentukan periode untuk query
            $MM = substr(trim($periode), 0, 2);
            $bln1 = $MM;

            // Periode kemarin
            $periodeKemarin = Carbon::create($yer, $bulan, 16)->subMonth();
            $bln2 = $periodeKemarin->format('m');

            $perx = $periodeKemarin->format('m/Y');
            $pernowx = Carbon::create($yer, $bulan, 15)->format('m/Y');

            $query = "SELECT
                        ? as dede,
                        sub,
                        KD_BRG,
                        NA_BRG,
                        SUM(qty) as qty,
                        HJX harga,
                        HJVIP hargavip,
                        SUM(HV) as total,
                        judul,
                        ROUND(SUM(qty)*HJVIP) TOTAL_VIP
                      FROM (
                        SELECT
                          'REPORT SALES VIP' AS JUDUL,
                          A.SUB2 as sub,
                          A.KD_BRG,
                          A.NA_BRG,
                          SUM(A.qty) as QTY,
                          C.{$HJX} HJX,
                          B.HJVIP,
                          SUM(A.total) as total,
                          A.per,
                          A.tgl,
                          (C.{$HJX}-B.HJVIP) X,
                          ROUND((C.{$HJX}-B.HJVIP)*SUM(A.qty)) HV
                        FROM juald{$bln1} A
                        LEFT JOIN masks C ON C.kd_brg=A.KD_BRG,
                        (SELECT
                           A.no_bukti, A.TGL_MULAI, A.TGL_SLS,
                           A.per, A.notes, B.KD_BRG, B.NA_BRG, B.HJVIP
                         FROM DIS A, disd B
                         WHERE A.no_bukti=B.no_bukti
                           AND TGZ=1
                           AND A.flag='PV'
                           AND A.{$cbgCode}=1
                        ) B
                        WHERE A.KD_BRG=B.KD_BRG
                          AND DATE(A.tgl)>=B.TGL_MULAI
                          AND DATE(A.tgl)<=B.TGL_SLS
                          AND A.CBG=?
                          AND A.flag='JL'
                          AND A.type='KS'
                          AND A.per = ?
                          AND A.tgl<= ?
                        GROUP BY A.KD_BRG

                        UNION ALL

                        SELECT
                          'REPORT SALES VIP' AS JUDUL,
                          A.SUB2 as sub,
                          A.KD_BRG,
                          A.NA_BRG,
                          SUM(A.qty) as QTY,
                          C.{$HJX} HJX,
                          B.HJVIP,
                          SUM(A.total) as total,
                          A.per,
                          A.tgl,
                          (C.{$HJX}-B.HJVIP) X,
                          ROUND((C.{$HJX}-B.HJVIP)*SUM(A.qty)) HV
                        FROM juald{$bln2} A
                        LEFT JOIN masks C ON C.kd_brg=A.KD_BRG,
                        (SELECT
                           A.no_bukti, A.TGL_MULAI, A.TGL_SLS,
                           A.per, A.notes, B.KD_BRG, B.NA_BRG, B.HJVIP
                         FROM DIS A, disd B
                         WHERE A.no_bukti=B.no_bukti
                           AND TGZ=1
                           AND A.flag='PV'
                           AND A.{$cbgCode}=1
                        ) B
                        WHERE A.KD_BRG=B.KD_BRG
                          AND DATE(A.tgl)>=B.TGL_MULAI
                          AND DATE(A.tgl)<=B.TGL_SLS
                          AND A.CBG=?
                          AND A.flag='JL'
                          AND A.type='KS'
                          AND A.per = ?
                          AND A.tgl<= ?
                        GROUP BY A.KD_BRG
                      ) as nda
                      GROUP BY kd_brg
                      ORDER BY KD_BRG ASC";

            $results = DB::select($query, [
                $tgllast,
                $cbgCode,
                $pernowx,
                $tglnow,
                $cbgCode,
                $perx,
                $tgllast
            ]);

            // Proses untuk menghitung summary per SUB (seperti dxMemData1 di Delphi)
            $summaryData = [];
            foreach ($results as $row) {
                $sub = $row->sub;
                if (isset($summaryData[$sub])) {
                    $summaryData[$sub]['qty'] += $row->qty;
                } else {
                    $summaryData[$sub] = [
                        'sub' => $sub,
                        'qty' => $row->qty
                    ];
                }
            }

            return [
                'detail' => $results,
                'summary' => array_values($summaryData),
                'info' => [
                    'perlast' => $perx,
                    'pernow' => $pernowx,
                    'tgllast' => $tgllast,
                    'tglnow' => $tglnow,
                    'total_records' => count($results)
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error in getObralVip: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Report Borong Kode 3 (sesuai dengan rbBorongClick di Delphi)
     */
    private function getObralBorong($cbgCode, $tanggal, $jam = null, $all = false)
    {
        try {
            $baseQuery = "SELECT * FROM (
                            SELECT
                              'REPORT BORONG KODE 3' AS JUDUL,
                              LEFT(juald.kd_brg,3) as sub,
                              no_bukti,
                              juald.KD_BRG,
                              NA_BRG,
                              SUM(qty) as qty,
                              SUM(total) as total,
                              TIME(TGL) as TIME,
                              ket_kem,
                              lph,
                              harga
                            FROM juald
                            LEFT JOIN (
                              SELECT
                                brg.KD_BRG, brg.KET_KEM, brgdt.LPH
                              FROM brg, brgdt
                              WHERE brg.KD_BRG=brgdt.KD_BRG
                                AND brgdt.CBG=?
                            ) as nda ON nda.KD_BRG=juald.KD_BRG
                            WHERE flag='JL'
                              AND type='KS'
                              AND cbg=?
                              AND DATE(TGL)=?
                              AND LEFT(NA_BRG,1)='3'";

            if ($all) {
                // Jika cbAll.Checked = True (tanpa filter jam)
                $baseQuery .= " GROUP BY no_bukti, juald.KD_BRG
                               ORDER BY juald.KD_BRG
                           ) as nda
                           WHERE qty>=3
                           ORDER BY na_brg ASC";
                echo $baseQuery;
                $formattedDate = Carbon::parse($tanggal)->format('Y-m-d');
                return DB::select($baseQuery, [$cbgCode, $cbgCode, $formattedDate]);
            } else {
                // Dengan filter jam
                $baseQuery .= " AND (HOUR(TGL)>=? AND HOUR(TGL)<=?)
                               GROUP BY no_bukti, juald.KD_BRG
                               ORDER BY juald.KD_BRG
                           ) as nda
                           WHERE qty>=3
                           ORDER BY na_brg ASC";

                $formattedDate = Carbon::parse($tanggal)->format('Y-m-d');
                $hourFrom = (int)$jam - 1;
                $hourTo = (int)$jam;

                return DB::select($baseQuery, [$cbgCode, $cbgCode, $formattedDate, $hourFrom, $hourTo]);
            }
        } catch (\Exception $e) {
            Log::error('Error in getObralBorong: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Helper function untuk menentukan periode MM
     */
    private function determinePeriode($periode, $tanggal)
    {
        if (!empty($periode)) {
            return substr(trim($periode), 0, 2);
        } else if (!empty($tanggal)) {
            return Carbon::parse($tanggal)->format('m');
        }
        return Carbon::now()->format('m');
    }

    /**
     * Helper function untuk menentukan HJX berdasarkan CBG
     */
    private function getHJXByCbg($cbgCode)
    {
        switch (trim($cbgCode)) {
            case 'TGZ':
                return 'HJGZ';
            case 'TMM':
                return 'HJMM';
            case 'SOP':
                return 'HJSP';
            default:
                return 'HJGZ'; // default
        }
    }

    /**
     * Method untuk mendapatkan nama toko menggunakan query langsung
     */
    private function getNamaToko($cbgCode)
    {
        try {
            $query = "SELECT NA_TOKO FROM toko WHERE KODE = ?";
            $result = DB::select($query, [$cbgCode]);
            return $result[0]->NA_TOKO ?? '';
        } catch (\Exception $e) {
            Log::error('Error in getNamaToko: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Jasper Report Generator
     */
    public function jasperBarangObralVipReport(Request $request)
    {
        $reportType = $request->report_type ?? 1;
        $cbgCode = $request->cbg;
        $tanggal = $request->tanggal;
        $periode = $request->periode;
        $jam = $request->jam;
        $all = $request->all ?? false;

        // Tentukan file report berdasarkan tipe
        $fileMap = [
            1 => 'rbarang_obral',
            2 => 'rbarang_obral',
            3 => 'rbarang_obral_vip',
            4 => 'rbarang_obral_borong'
        ];

        $file = $fileMap[$reportType] ?? 'rbarang_obral';

        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

        // Store filter values in session
        session()->put('filter_cbg', $cbgCode);
        session()->put('filter_tanggal', $tanggal);
        session()->put('filter_periode', $periode);
        session()->put('filter_jam', $jam);
        session()->put('report_type', $reportType);
        session()->put('filter_all', $all);

        $data = [];

        if (!empty($cbgCode)) {
            switch ($reportType) {
                case 1: // Kode 3 & 8
                    if (!empty($tanggal) && !empty($periode)) {
                        $results = $this->getObralKode38($cbgCode, $tanggal, $periode);
                        foreach ($results as $row) {
                            $data[] = [
                                'CBG' => $cbgCode,
                                'JUDUL' => $row->JUDUL ?? '',
                                'TGL_NOW' => now()->format("d/m/Y"),
                                'SUB' => $row->SUB ?? '',
                                'KD_BRG' => $row->KD_BRG ?? '',
                                'NA_BRG' => $row->NA_BRG ?? '',
                                'QTY' => $row->qty ?? 0,
                                'TOTAL' => $row->total ?? 0,
                                'HARGA' => $row->harga ?? 0,
                                'NAMA_TOKO' => $this->getNamaToko($cbgCode),
                                'TANGGAL' => $tanggal,
                                'PERIODE' => $periode,
                            ];
                        }
                    }
                    break;

                case 2: // Food Center
                    if (!empty($tanggal) && !empty($periode)) {
                        $results = $this->getObralFoodCenter($cbgCode, $tanggal, $periode);
                        foreach ($results as $row) {
                            $data[] = [
                                'TGL_NOW' => now()->format("d/m/Y"),
                                'CBG' => $cbgCode,
                                'JUDUL' => $row->JUDUL ?? '',
                                'SUB' => $row->SUB ?? '',
                                'KD_BRG' => $row->KD_BRG ?? '',
                                'NA_BRG' => $row->NA_BRG ?? '',
                                'QTY' => $row->qty ?? 0,
                                'TOTAL' => $row->total ?? 0,
                                'HARGA' => $row->harga ?? 0,
                                'NAMA_TOKO' => $this->getNamaToko($cbgCode),
                                'TANGGAL' => $tanggal,
                                'PERIODE' => $periode,
                            ];
                        }
                    }
                    break;

                case 3: // VIP
                    if (!empty($periode)) {
                        $results = $this->getObralVip($cbgCode, $periode);
                        if (isset($results['detail'])) {
                            foreach ($results['detail'] as $row) {
                                $data[] = [
                                'TGL_NOW' => now()->format("d/m/Y"),
                                    'CBG' => $cbgCode,
                                    'SUB' => $row->sub ?? '',
                                    'KD_BRG' => $row->KD_BRG ?? '',
                                    'NA_BRG' => $row->NA_BRG ?? '',
                                    'QTY' => $row->qty ?? 0,
                                    'HARGA' => $row->harga ?? 0,
                                    'HARGAVIP' => $row->hargavip ?? 0,
                                    'TOTAL' => $row->total ?? 0,
                                    'TOTAL_VIP' => $row->TOTAL_VIP ?? 0,
                                    'NAMA_TOKO' => $this->getNamaToko($cbgCode),
                                    'PERIODE' => $periode,
                                ];
                            }
                        }
                    }
                    break;

                case 4: // Borong
                    if (!empty($tanggal)) {
                        $results = $this->getObralBorong($cbgCode, $tanggal, $jam, $all);
                        foreach ($results as $row) {
                            $data[] = [
                                'TGL_NOW' => now()->format("d/m/Y"),
                                'CBG' => $cbgCode,
                                'JUDUL' => $row->JUDUL ?? '',
                                'SUB' => $row->sub ?? '',
                                'NO_BUKTI' => $row->no_bukti ?? '',
                                'KD_BRG' => $row->KD_BRG ?? '',
                                'NA_BRG' => $row->NA_BRG ?? '',
                                'QTY' => $row->qty ?? 0,
                                'TOTAL' => $row->total ?? 0,
                                'TIME' => $row->TIME ?? '',
                                'KET_KEM' => $row->ket_kem ?? '',
                                'LPH' => $row->lph ?? '',
                                'HARGA' => $row->harga ?? 0,
                                'NAMA_TOKO' => $this->getNamaToko($cbgCode),
                                'TANGGAL' => $tanggal,
                                'JAM' => $jam,
                                'ALL_TIME' => $all ? 'Ya' : 'Tidak',
                            ];
                        }
                    }
                    break;
            }
        }

        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I");
    }

    /**
     * API endpoint untuk mendukung AJAX calls dari frontend
     */
    public function apiGetBarangObralData(Request $request)
    {
        try {
            $cbgCode = $request->cbg;
            $tanggal = $request->tanggal;
            $periode = $request->periode;
            $jam = $request->jam;
            $reportType = $request->report_type ?? 1;
            $all = $request->all ?? false;

            if (empty($cbgCode)) {
                return response()->json(['error' => 'Cabang harus dipilih'], 400);
            }

            $hasil = [];

            switch ($reportType) {
                case 1: // Kode 3 & 8
                    if (empty($tanggal) || empty($periode)) {
                        return response()->json(['error' => 'Tanggal dan periode harus dipilih'], 400);
                    }
                    $hasil = $this->getObralKode38($cbgCode, $tanggal, $periode);
                    break;

                case 2: // Food Center
                    if (empty($tanggal) || empty($periode)) {
                        return response()->json(['error' => 'Tanggal dan periode harus dipilih'], 400);
                    }
                    $hasil = $this->getObralFoodCenter($cbgCode, $tanggal, $periode);
                    break;

                case 3: // VIP
                    if (empty($periode)) {
                        return response()->json(['error' => 'Periode harus dipilih'], 400);
                    }
                    $hasil = $this->getObralVip($cbgCode, $periode);
                    break;

                case 4: // Borong
                    if (empty($tanggal)) {
                        return response()->json(['error' => 'Tanggal harus dipilih'], 400);
                    }
                    $hasil = $this->getObralBorong($cbgCode, $tanggal, $jam, $all);
                    break;
            }

            return response()->json([
                'success' => true,
                'data' => $hasil,
                'report_type' => $reportType,
                'nama_toko' => $this->getNamaToko($cbgCode)
            ]);
        } catch (\Exception $e) {
            Log::error('Error in apiGetBarangObralData: ' . $e->getMessage());
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Method untuk export data ke Excel
     */
    public function exportBarangObralReport(Request $request)
    {
        try {
            $cbgCode = $request->cbg;
            $tanggal = $request->tanggal;
            $periode = $request->periode;
            $jam = $request->jam;
            $reportType = $request->report_type ?? 1;
            $all = $request->all ?? false;

            if (empty($cbgCode)) {
                return response()->json(['error' => 'Cabang harus dipilih'], 400);
            }

            $data = [];
            $filename = '';
            $reportTypeNames = [
                1 => 'kode38',
                2 => 'foodcenter',
                3 => 'vip',
                4 => 'borong'
            ];

            $hasil = [];

            switch ($reportType) {
                case 1: // Kode 3 & 8
                    $hasil = $this->getObralKode38($cbgCode, $tanggal, $periode);
                    $filename = 'barang_obral_' . $reportTypeNames[$reportType] . '_' . $cbgCode . '_' . date('Y-m-d_H-i-s') . '.xlsx';

                    foreach ($hasil as $row) {
                        $data[] = [
                            'Judul' => $row->JUDUL ?? '',
                            'Sub' => $row->SUB ?? '',
                            'Kode Barang' => $row->KD_BRG ?? '',
                            'Nama Barang' => $row->NA_BRG ?? '',
                            'Qty' => $row->qty ?? 0,
                            'Total' => $row->total ?? 0,
                            'Harga' => $row->harga ?? 0,
                        ];
                    }
                    break;

                case 2: // Food Center
                    $hasil = $this->getObralFoodCenter($cbgCode, $tanggal, $periode);
                    $filename = 'barang_obral_' . $reportTypeNames[$reportType] . '_' . $cbgCode . '_' . date('Y-m-d_H-i-s') . '.xlsx';

                    foreach ($hasil as $row) {
                        $data[] = [
                            'Judul' => $row->JUDUL ?? '',
                            'Sub' => $row->SUB ?? '',
                            'Kode Barang' => $row->KD_BRG ?? '',
                            'Nama Barang' => $row->NA_BRG ?? '',
                            'Qty' => $row->qty ?? 0,
                            'Total' => $row->total ?? 0,
                            'Harga' => $row->harga ?? 0,
                        ];
                    }
                    break;

                case 3: // VIP
                    $hasil = $this->getObralVip($cbgCode, $periode);
                    $filename = 'barang_obral_' . $reportTypeNames[$reportType] . '_' . $cbgCode . '_' . date('Y-m-d_H-i-s') . '.xlsx';

                    if (isset($hasil['detail'])) {
                        foreach ($hasil['detail'] as $row) {
                            $data[] = [
                                'Sub' => $row->sub ?? '',
                                'Kode Barang' => $row->KD_BRG ?? '',
                                'Nama Barang' => $row->NA_BRG ?? '',
                                'Qty' => $row->qty ?? 0,
                                'Harga' => $row->harga ?? 0,
                                'Harga VIP' => $row->hargavip ?? 0,
                                'Total' => $row->total ?? 0,
                                'Total VIP' => $row->TOTAL_VIP ?? 0,
                            ];
                        }
                    }
                    break;

                case 4: // Borong
                    $hasil = $this->getObralBorong($cbgCode, $tanggal, $jam, $all);
                    $filename = 'barang_obral_' . $reportTypeNames[$reportType] . '_' . $cbgCode . '_' . date('Y-m-d_H-i-s') . '.xlsx';

                    foreach ($hasil as $row) {
                        $data[] = [
                            'Judul' => $row->JUDUL ?? '',
                            'Sub' => $row->sub ?? '',
                            'No Bukti' => $row->no_bukti ?? '',
                            'Kode Barang' => $row->KD_BRG ?? '',
                            'Nama Barang' => $row->NA_BRG ?? '',
                            'Qty' => $row->qty ?? 0,
                            'Total' => $row->total ?? 0,
                            'Waktu' => $row->TIME ?? '',
                            'Keterangan Kemasan' => $row->ket_kem ?? '',
                            'LPH' => $row->lph ?? '',
                            'Harga' => $row->harga ?? 0,
                        ];
                    }
                    break;
            }

            return response()->json([
                'success' => true,
                'data' => $data,
                'filename' => $filename
            ]);
        } catch (\Exception $e) {
            Log::error('Error in exportBarangObralReport: ' . $e->getMessage());
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Method untuk mendapatkan summary/statistik data obral
     */
    public function getSummaryObral($cbgCode, $tanggal, $periode, $reportType)
    {
        try {
            $MM = $this->determinePeriode($periode, $tanggal);
            $formattedDate = Carbon::parse($tanggal)->format('Y-m-d');

            switch ($reportType) {
                case 1: // Kode 3 & 8
                    $query = "SELECT
                                COUNT(DISTINCT KD_BRG) as total_item,
                                SUM(qty) as total_qty,
                                SUM(total) as total_amount
                              FROM juald{$MM}
                              WHERE flag='JL' AND type='KS'
                                AND cbg=?
                                AND diskon<>0
                                AND DATE(TGL)=?";
                    $result = DB::select($query, [$cbgCode, $formattedDate]);
                    break;

                case 2: // Food Center
                    $query = "SELECT
                                COUNT(DISTINCT KD_BRG) as total_item,
                                SUM(qty) as total_qty,
                                SUM(total) as total_amount
                              FROM juald{$MM}
                              WHERE flag='FC' AND type='KS'
                                AND cbg=?
                                AND diskon<>0
                                AND DATE(TGL)=?";
                    $result = DB::select($query, [$cbgCode, $formattedDate]);
                    break;

                case 4: // Borong
                    $query = "SELECT
                                COUNT(DISTINCT KD_BRG) as total_item,
                                SUM(qty) as total_qty,
                                SUM(total) as total_amount
                              FROM juald
                              WHERE flag='JL' AND type='KS'
                                AND cbg=?
                                AND DATE(TGL)=?
                                AND LEFT(NA_BRG,1)='3'";
                    $result = DB::select($query, [$cbgCode, $formattedDate]);
                    break;

                default:
                    return null;
            }

            return $result[0] ?? null;
        } catch (\Exception $e) {
            Log::error('Error in getSummaryObral: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * API endpoint untuk mendapatkan summary data obral
     */
    public function apiGetObralSummary(Request $request)
    {
        try {
            $cbgCode = $request->cbg;
            $tanggal = $request->tanggal;
            $periode = $request->periode;
            $reportType = $request->report_type ?? 1;

            if (empty($cbgCode) || empty($tanggal)) {
                return response()->json(['error' => 'Cabang dan tanggal harus dipilih'], 400);
            }

            $summary = $this->getSummaryObral($cbgCode, $tanggal, $periode, $reportType);

            return response()->json([
                'success' => true,
                'summary' => $summary,
                'nama_toko' => $this->getNamaToko($cbgCode)
            ]);
        } catch (\Exception $e) {
            Log::error('Error in apiGetObralSummary: ' . $e->getMessage());
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Method untuk kirim data OB (seperti btnKirimOBClick di Delphi)
     */
    public function kirimDataOB(Request $request)
    {
        try {
            $cbgCode = $request->cbg;
            $tanggal = $request->tanggal;
            $ordke = $request->ordke ?? 'FSA';

            if (empty($cbgCode) || empty($tanggal)) {
                return response()->json(['error' => 'Cabang dan tanggal harus dipilih'], 400);
            }

            $formattedDate = Carbon::parse($tanggal)->format('Y-m-d');

            // Simulasi pemanggilan API seperti di Delphi
            // Dalam implementasi nyata, gunakan Guzzle HTTP client
            $url = "http://10.10.30.132:8080/export-dbf-app/public/export-obkode8";
            $params = [
                'db' => $cbgCode,
                'tgl' => $formattedDate,
                'ordke' => $ordke
            ];

            // Contoh implementasi dengan cURL (bisa diganti dengan Guzzle)
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url . '?' . http_build_query($params));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode == 200 && $response !== '500') {
                return response()->json([
                    'success' => true,
                    'message' => 'File OB ' . $cbgCode . ': ' . $response . ' Berhasil dikirimkan.'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'File Gagal Di Export.'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error in kirimDataOB: ' . $e->getMessage());
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Method untuk validasi data sebelum generate report
     */
    private function validateReportData($cbgCode, $tanggal, $periode, $reportType)
    {
        $errors = [];

        if (empty($cbgCode)) {
            $errors[] = 'Cabang harus dipilih';
        }

        switch ($reportType) {
            case 1: // Kode 3 & 8
            case 2: // Food Center
                if (empty($tanggal)) {
                    $errors[] = 'Tanggal harus dipilih';
                }
                if (empty($periode)) {
                    $errors[] = 'Periode harus dipilih';
                }
                break;

            case 3: // VIP
                if (empty($periode)) {
                    $errors[] = 'Periode harus dipilih untuk laporan VIP';
                }
                break;

            case 4: // Borong
                if (empty($tanggal)) {
                    $errors[] = 'Tanggal harus dipilih untuk laporan borong';
                }
                break;
        }

        return $errors;
    }

    /**
     * Method untuk mendapatkan top selling obral products
     */
    public function getTopObralProducts(Request $request)
    {
        try {
            $cbgCode = $request->cbg;
            $tanggal = $request->tanggal;
            $periode = $request->periode;
            $reportType = $request->report_type ?? 1;
            $limit = $request->limit ?? 10;

            if (empty($cbgCode) || empty($tanggal)) {
                return response()->json(['error' => 'Parameter tidak lengkap'], 400);
            }

            $MM = $this->determinePeriode($periode, $tanggal);
            $formattedDate = Carbon::parse($tanggal)->format('Y-m-d');

            $query = "";
            $params = [];

            switch ($reportType) {
                case 1: // Kode 3 & 8
                    $query = "SELECT
                                KD_BRG,
                                NA_BRG,
                                SUM(qty) as total_qty,
                                SUM(total) as total_amount,
                                COUNT(DISTINCT no_bukti) as total_transaksi
                              FROM juald{$MM}
                              WHERE flag='JL' AND type='KS'
                                AND cbg=?
                                AND diskon<>0
                                AND DATE(TGL)=?
                              GROUP BY KD_BRG, NA_BRG
                              ORDER BY total_qty DESC
                              LIMIT ?";
                    $params = [$cbgCode, $formattedDate, $limit];
                    break;

                case 2: // Food Center
                    $query = "SELECT
                                KD_BRG,
                                NA_BRG,
                                SUM(qty) as total_qty,
                                SUM(total) as total_amount,
                                COUNT(DISTINCT no_bukti) as total_transaksi
                              FROM juald{$MM}
                              WHERE flag='FC' AND type='KS'
                                AND cbg=?
                                AND diskon<>0
                                AND DATE(TGL)=?
                              GROUP BY KD_BRG, NA_BRG
                              ORDER BY total_qty DESC
                              LIMIT ?";
                    $params = [$cbgCode, $formattedDate, $limit];
                    break;
            }

            if (!empty($query)) {
                $result = DB::select($query, $params);
                return response()->json([
                    'success' => true,
                    'data' => $result,
                    'tanggal' => $tanggal,
                    'nama_toko' => $this->getNamaToko($cbgCode)
                ]);
            }

            return response()->json(['error' => 'Report type tidak didukung'], 400);
        } catch (\Exception $e) {
            Log::error('Error in getTopObralProducts: ' . $e->getMessage());
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
