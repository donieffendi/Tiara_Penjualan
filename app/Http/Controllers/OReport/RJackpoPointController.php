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

class RJackpoPointController extends Controller
{
    public function report()
    {
        $cbg = Cbg::groupBy('CBG')->get();
        $per = Perid::query()->get();

        // Get distinct banks for dropdown
        $banks = $this->getDistinctBanks();

        // Initialize session variables sesuai dengan logika Delphi
        session()->put('filter_cbg', '');
        session()->put('filter_per', date("m-Y"));
        session()->put('filter_tgl1', now()->startOfMonth()->format('Y-m-d'));
        session()->put('filter_tgl2', now()->endOfMonth()->format('Y-m-d'));
        session()->put('filter_posted', ''); // All, Yes, No
        session()->put('filter_cbg2', '');
        session()->put('filter_tgl_poin1', now()->format('Y-m-d'));
        session()->put('filter_tgl_poin2', now()->format('Y-m-d'));
        session()->put('filter_bank', '');
        session()->put('filter_member_type', 'member'); // member, semua

        return view('oreport_jackpot_point.report')->with([
            'cbg' => $cbg,
            'per' => $per,
            'banks' => $banks,
            'hasilJackpot' => [],
            'hasilPoint' => [],
        ]);
    }

    public function getJackpoPointReport(Request $request)
    {
        $cbg = Cbg::groupBy('CBG')->get();
        $per = Perid::query()->get();
        $banks = $this->getDistinctBanks();

        // Get filter values
        $cbgCode = $request->cbg;
        $periode = $request->per;
        $tgl1 = $request->tgl1;
        $tgl2 = $request->tgl2;
        $posted = $request->posted; // all, yes, no
        $reportType = $request->report_type ?? 1; // 1=Jackpot, 2=Point

        // Store in session
        session()->put('filter_cbg', $cbgCode);
        session()->put('filter_per', $periode);
        session()->put('filter_tgl1', $tgl1);
        session()->put('filter_tgl2', $tgl2);
        session()->put('filter_posted', $posted);

        $hasilJackpot = [];
        $hasilPoint = [];

        if ($reportType == 1) {
            // Jackpot Report
            if (!empty($cbgCode) && !empty($periode) && !empty($tgl1) && !empty($tgl2)) {
                $hasilJackpot = $this->getJackpotData($cbgCode, $periode, $tgl1, $tgl2, $posted);
            }
        } else {
            // Point Report - akan dihandle oleh method terpisah
            $cbgCode2 = $request->cbg2;
            $tglPoin1 = $request->tgl_poin1;
            $tglPoin2 = $request->tgl_poin2;
            $bank = $request->bank;
            $memberType = $request->member_type ?? 'member';

            session()->put('filter_cbg2', $cbgCode2);
            session()->put('filter_tgl_poin1', $tglPoin1);
            session()->put('filter_tgl_poin2', $tglPoin2);
            session()->put('filter_bank', $bank);
            session()->put('filter_member_type', $memberType);

            if (!empty($cbgCode2) && !empty($tglPoin1) && !empty($tglPoin2) && !empty($bank)) {
                $hasilPoint = $this->getPointData($cbgCode2, $tglPoin1, $tglPoin2, $bank, $memberType);
            }
        }

        return view('oreport_jackpot_point.report')->with([
            'cbg' => $cbg,
            'per' => $per,
            'banks' => $banks,
            'hasilJackpot' => $hasilJackpot,
            'hasilPoint' => $hasilPoint,
            'reportType' => $reportType,
        ]);
    }

    // Sesuai dengan tampil procedure di Delphi untuk Jackpot
    private function getJackpotData($cbgCode, $periode, $tgl1, $tgl2, $posted)
    {
        $posCondition = '';
        if ($posted === 'yes') {
            $posCondition = 'AND posted = 1';
        } elseif ($posted === 'no') {
            $posCondition = 'AND posted = 0';
        }
        // Jika 'all' atau kosong, tidak ada kondisi tambahan

        // Get MA (master area) - assuming it's available in session or config
        $ma = session('ma_code', $cbgCode); // fallback to cbgCode if not available

        $query = "SELECT *,
                    :per as per,
                    :tgl1 as tgl1,
                    :tgl2 as tgl2
                  FROM {$ma}.jackpot
                  WHERE CBG = :cbg
                  AND CONCAT(LPAD(MONTH(tgl), 2, '0'), '/', LEFT(tgl, 4)) = :per
                  AND tgl >= :tgl1
                  AND tgl <= :tgl2
                  {$posCondition}
                  ORDER BY tgl DESC";

        return DB::select($query, [
            'cbg' => $cbgCode,
            'per' => $periode,
            'tgl1' => $tgl1,
            'tgl2' => $tgl2
        ]);
    }

    // Sesuai dengan tampil2 procedure di Delphi untuk Point/Bank
    private function getPointData($cbgCode, $tglPoin1, $tglPoin2, $bank, $memberType)
    {
        $memberCondition = '';
        $bankCondition = '';
        $groupBy = '';

        if ($memberType === 'member') {
            $memberCondition = 'AND LENGTH(A.kodeC) > 4';
            $bankCondition = 'AND B.NBANK = :nbank';
        } elseif ($memberType === 'member_grouped') {
            $memberCondition = 'AND LENGTH(A.kodeC) > 4';
            $groupBy = 'GROUP BY KODEC, TGL';
        }

        // Get month for archive table
        $monthQuery = DB::select("SELECT LPAD(MONTH(?), 2, '0') as X", [$tglPoin1]);
        $month = $monthQuery[0]->X;

        $query = "SET @NBANK := :nbank;
                  SET @TGL1 := :tgl1;
                  SET @TGL2 := :tgl2;

                  SELECT A.KODEC, A.NAMAC, A.KSR, A.TG_SMP, A.TG_SMP as TGL, A.STIKER,
                         B.TYPE, B.JUMLAH, B.NKARTU, B.NBANK
                  FROM {$cbgCode}.jual A, {$cbgCode}.jualby B
                  WHERE A.NO_BUKTI = B.NO_BUKTI
                  {$memberCondition}
                  AND A.KSR NOT IN ('90', '91', '92')
                  {$bankCondition}
                  AND DATE(A.TGL) BETWEEN @TGL1 AND @TGL2

                  UNION ALL

                  SELECT A.KODEC, A.NAMAC, A.KSR, A.TG_SMP, A.TG_SMP as TGL, A.STIKER,
                         B.TYPE, B.JUMLAH, B.NKARTU, B.NBANK
                  FROM {$cbgCode}.jual{$month} A, {$cbgCode}.jualby{$month} B
                  WHERE A.NO_BUKTI = B.NO_BUKTI
                  {$memberCondition}
                  AND A.KSR NOT IN ('90', '91', '92')
                  {$bankCondition}
                  AND DATE(A.TGL) BETWEEN @TGL1 AND @TGL2

                  {$groupBy}
                  ORDER BY KODEC, TG_SMP";

        return DB::select($query, [
            'nbank' => $bank,
            'tgl1' => $tglPoin1,
            'tgl2' => $tglPoin2
        ]);
    }

    // Get distinct banks untuk dropdown
    private function getDistinctBanks()
    {
        try {
            $query = "SELECT DISTINCT(BANK) as bank_code, NM_BANK as bank_name
                  FROM masbank
                  WHERE BANK != ''
                  ORDER BY NM_BANK";
            return DB::select($query);
        } catch (\Illuminate\Database\QueryException $e) {
            // Jika terjadi error (misal table tidak ada), outputkan array dengan satu elemen null
            return [
                (object)[
                    'bank_code' => null,
                    'bank_name' => null
                ]
            ];
        }

        return DB::select($query);
    }

    // Method untuk mendapatkan nama toko
    private function getNamaToko($cbgCode)
    {
        $result = DB::select("SELECT NA_TOKO from {$cbgCode}.toko WHERE KODE = :cbg", ['cbg' => $cbgCode]);
        return $result[0]->NA_TOKO ?? '';
    }

    public function jasperJackpoPointReport(Request $request)
    {
        $reportType = $request->report_type ?? 1;
        $file = ($reportType == 1) ? 'rjackpot' : 'rpoint';

        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

        // Store filter values in session
        if ($reportType == 1) {
            // Jackpot Report
            $cbgCode = $request->cbg;
            $periode = $request->per;
            $tgl1 = $request->tgl1;
            $tgl2 = $request->tgl2;
            $posted = $request->posted;

            session()->put('filter_cbg', $cbgCode);
            session()->put('filter_per', $periode);
            session()->put('filter_tgl1', $tgl1);
            session()->put('filter_tgl2', $tgl2);
            session()->put('filter_posted', $posted);

            $data = [];
            if (!empty($cbgCode) && !empty($periode) && !empty($tgl1) && !empty($tgl2)) {
                $results = $this->getJackpotData($cbgCode, $periode, $tgl1, $tgl2, $posted);

                foreach ($results as $row) {
                    $data[] = [
                        'CBG' => $row->CBG ?? '',
                        'NO_BUKTI' => $row->NO_BUKTI ?? '',
                        'TGL' => $row->TGL ?? '',
                        'KODEC' => $row->KODEC ?? '',
                        'NAMAC' => $row->NAMAC ?? '',
                        'KSR' => $row->KSR ?? '',
                        'STIKER' => $row->STIKER ?? 0,
                        'JACKPOT' => $row->JACKPOT ?? 0,
                        'POSTED' => $row->POSTED ?? 0,
                        'PER' => $row->per ?? '',
                        'TGL1' => $row->tgl1 ?? '',
                        'TGL2' => $row->tgl2 ?? '',
                        'NAMA_TOKO' => $this->getNamaToko($cbgCode),
                    ];
                }
            }
        } else {
            // Point Report
            $cbgCode2 = $request->cbg2;
            $tglPoin1 = $request->tgl_poin1;
            $tglPoin2 = $request->tgl_poin2;
            $bank = $request->bank;
            $memberType = $request->member_type;

            session()->put('filter_cbg2', $cbgCode2);
            session()->put('filter_tgl_poin1', $tglPoin1);
            session()->put('filter_tgl_poin2', $tglPoin2);
            session()->put('filter_bank', $bank);
            session()->put('filter_member_type', $memberType);

            $data = [];
            if (!empty($cbgCode2) && !empty($tglPoin1) && !empty($tglPoin2) && !empty($bank)) {
                $results = $this->getPointData($cbgCode2, $tglPoin1, $tglPoin2, $bank, $memberType);

                foreach ($results as $row) {
                    $data[] = [
                        'KODEC' => $row->KODEC ?? '',
                        'NAMAC' => $row->NAMAC ?? '',
                        'KSR' => $row->KSR ?? '',
                        'TG_SMP' => $row->TG_SMP ?? '',
                        'TGL' => $row->TGL ?? '',
                        'STIKER' => $row->STIKER ?? 0,
                        'TYPE' => $row->TYPE ?? '',
                        'JUMLAH' => $row->JUMLAH ?? 0,
                        'NKARTU' => $row->NKARTU ?? '',
                        'NBANK' => $row->NBANK ?? '',
                        'CBG' => $cbgCode2,
                        'BANK_FILTER' => $bank,
                        'TGL_POIN1' => $tglPoin1,
                        'TGL_POIN2' => $tglPoin2,
                        'NAMA_TOKO' => $this->getNamaToko($cbgCode2),
                    ];
                }
            }
        }

        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I");
    }

    // API endpoints untuk mendukung AJAX calls dari frontend
    public function apiGetJackpoPoint(Request $request)
    {
        try {
            $reportType = $request->report_type ?? 1;

            if ($reportType == 1) {
                // Jackpot Report
                $cbgCode = $request->cbg;
                $periode = $request->per;
                $tgl1 = $request->tgl1;
                $tgl2 = $request->tgl2;
                $posted = $request->posted;

                if (empty($cbgCode) || empty($periode) || empty($tgl1) || empty($tgl2)) {
                    return response()->json(['error' => 'Parameter tidak lengkap untuk laporan jackpot'], 400);
                }

                $hasil = $this->getJackpotData($cbgCode, $periode, $tgl1, $tgl2, $posted);
            } else {
                // Point Report
                $cbgCode2 = $request->cbg2;
                $tglPoin1 = $request->tgl_poin1;
                $tglPoin2 = $request->tgl_poin2;
                $bank = $request->bank;
                $memberType = $request->member_type ?? 'member';

                if (empty($cbgCode2) || empty($tglPoin1) || empty($tglPoin2) || empty($bank)) {
                    return response()->json(['error' => 'Parameter tidak lengkap untuk laporan point'], 400);
                }

                $hasil = $this->getPointData($cbgCode2, $tglPoin1, $tglPoin2, $bank, $memberType);
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

    public function apiGetDetailJackpoPoint(Request $request)
    {
        try {
            $reportType = $request->report_type ?? 1;
            $noBukti = $request->no_bukti;
            $cbgCode = $request->cbg;

            if (empty($noBukti) || empty($cbgCode)) {
                return response()->json(['error' => 'No Bukti dan Cabang harus diisi'], 400);
            }

            $detail = $this->getDetailData($cbgCode, $noBukti, $reportType);

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

    private function getDetailData($cbgCode, $noBukti, $reportType)
    {
        $details = [];

        if ($reportType == 1) {
            // Detail jackpot
            $query = "SELECT * FROM {$cbgCode}.jackpot WHERE NO_BUKTI = :no_bukti";
            $details = DB::select($query, ['no_bukti' => $noBukti]);
        } else {
            // Detail point - bisa disesuaikan dengan kebutuhan
            $query = "SELECT A.*, B.*
                      FROM {$cbgCode}.jual A
                      LEFT JOIN {$cbgCode}.jualby B ON A.NO_BUKTI = B.NO_BUKTI
                      WHERE A.NO_BUKTI = :no_bukti";
            $details = DB::select($query, ['no_bukti' => $noBukti]);
        }

        return $details;
    }

    public function apiGetThermalPrintJackpoPoint(Request $request)
    {
        try {
            $reportType = $request->report_type ?? 1;
            $noBukti = $request->no_bukti;
            $cbgCode = $request->cbg;

            if (empty($noBukti) || empty($cbgCode)) {
                return response()->json(['error' => 'No Bukti dan Cabang harus diisi'], 400);
            }

            // Get detail data for thermal print
            $data = $this->getDetailData($cbgCode, $noBukti, $reportType);
            $namaToko = $this->getNamaToko($cbgCode);

            return response()->json([
                'success' => true,
                'data' => $data,
                'nama_toko' => $namaToko
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    // Helper method untuk mendapatkan summary data
    public function getSummaryJackpot($cbgCode, $periode, $tgl1, $tgl2)
    {
        $ma = session('ma_code', $cbgCode);

        $query = "SELECT
                    COUNT(*) as total_transaksi,
                    SUM(JACKPOT) as total_jackpot,
                    SUM(STIKER) as total_stiker,
                    COUNT(CASE WHEN POSTED = 1 THEN 1 END) as total_posted,
                    COUNT(CASE WHEN POSTED = 0 THEN 1 END) as total_unposted
                  FROM {$ma}.jackpot
                  WHERE CBG = :cbg
                  AND CONCAT(LPAD(MONTH(tgl), 2, '0'), '/', LEFT(tgl, 4)) = :per
                  AND tgl >= :tgl1
                  AND tgl <= :tgl2";

        $result = DB::select($query, [
            'cbg' => $cbgCode,
            'per' => $periode,
            'tgl1' => $tgl1,
            'tgl2' => $tgl2
        ]);

        return $result[0] ?? null;
    }

    // Helper method untuk mendapatkan summary point data
    public function getSummaryPoint($cbgCode, $tglPoin1, $tglPoin2, $bank, $memberType)
    {
        $memberCondition = '';
        $bankCondition = '';

        if ($memberType === 'member') {
            $memberCondition = 'AND LENGTH(A.kodeC) > 4';
            $bankCondition = 'AND B.NBANK = :nbank';
        }

        // Get month for archive table
        $monthQuery = DB::select("SELECT LPAD(MONTH(?), 2, '0') as X", [$tglPoin1]);
        $month = $monthQuery[0]->X;

        $query = "SELECT
                    COUNT(DISTINCT A.KODEC) as total_member,
                    COUNT(*) as total_transaksi,
                    SUM(B.JUMLAH) as total_jumlah
                  FROM {$cbgCode}.jual A, {$cbgCode}.jualby B
                  WHERE A.NO_BUKTI = B.NO_BUKTI
                  {$memberCondition}
                  AND A.KSR NOT IN ('90', '91', '92')
                  {$bankCondition}
                  AND DATE(A.TGL) BETWEEN :tgl1 AND :tgl2";

        $params = [
            'tgl1' => $tglPoin1,
            'tgl2' => $tglPoin2
        ];

        if ($memberType === 'member') {
            $params['nbank'] = $bank;
        }

        $result = DB::select($query, $params);
        return $result[0] ?? null;
    }
}