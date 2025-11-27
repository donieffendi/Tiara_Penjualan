<?php

namespace App\Http\Controllers\OReport;

use App\Http\Controllers\Controller;
use App\Models\Master\Cbg;
use App\Models\Master\Perid;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;

class RPenjualanPHController extends Controller
{
    public function report()
    {
        $cbg = Cbg::groupBy('CBG')->get();
        $per = Perid::query()->get();

        // Initialize session variables
        session()->put('filter_cbg', '');
        session()->put('filter_periode', '');
        session()->put('filter_tgl1', '');
        session()->put('filter_tgl2', '');

        return view('oreport_penjualanph.report')->with([
            'cbg' => $cbg,
            'per' => $per,
            'hasilPenjualanPH' => [],
            'hasilPB1' => []
        ]);
    }

    public function getPenjualanPHReport(Request $request)
    {
        $cbg = Cbg::groupBy('CBG')->get();
        $per = Perid::query()->get();

        // Set filter values to session
        session()->put('filter_cbg', $request->cbg);
        session()->put('filter_periode', $request->periode);
        session()->put('filter_tgl1', $request->tgl1);
        session()->put('filter_tgl2', $request->tgl2);

        $hasilPenjualanPH = [];
        $hasilPB1 = [];

        if (!empty($request->cbg) && !empty($request->periode)) {
            // Set tanggal berdasarkan periode jika tidak diisi manual
            $tgl1 = $request->tgl1 ?: $this->getStartOfMonth($request->periode);
            $tgl2 = $request->tgl2 ?: $this->getEndOfMonth($request->periode);

            session()->put('filter_tgl1', $tgl1);
            session()->put('filter_tgl2', $tgl2);

            $hasilPenjualanPH = $this->getPenjualanPHData($request->cbg, $request->periode, $tgl1, $tgl2);
            $hasilPB1 = $this->getPB1Data($request->cbg, $request->periode, $tgl1, $tgl2);
        }

        return view('oreport_penjualanph.report')->with([
            'cbg' => $cbg,
            'per' => $per,
            'hasilPenjualanPH' => $hasilPenjualanPH,
            'hasilPB1' => $hasilPB1
        ]);
    }

    /**
     * Mengambil data penjualan PH berdasarkan logika Delphi
     * Meniru query tampil procedure dari TFrrjualfc.tampil
     */
    private function getPenjualanPHData($cbg, $periode, $tgl1, $tgl2)
    {
        try {
            // Validasi input
            $this->validateInput($cbg, $periode, $tgl1, $tgl2);

            // Extract MM dari periode (2 karakter pertama)
            $MM = substr(trim($periode), 0, 2);

            // Query utama berdasarkan logika Delphi bsn.SQL.Text
            $query = "
                SELECT ageng.*, brgfc.TYPE, brgfc.STAND, brgfc.supp AS KODES, brgfc.NAMAS
                FROM (
                    SELECT juald{$MM}.KD_BRG, juald{$MM}.NA_BRG,
                           SUM(juald{$MM}.qty) AS qtylaku,
                           SUM(juald{$MM}.total) AS totlaku,
                           SUM(IF(juald{$MM}.disc > 0, qty, 0)) AS qtyob,
                           SUM(IF(juald{$MM}.disc > 0, juald{$MM}.total, 0)) AS totob,
                           SUM(IF(juald{$MM}.disc = 0, juald{$MM}.qty, 0)) AS qtynm,
                           SUM(IF(juald{$MM}.disc = 0, juald{$MM}.total, 0)) AS totnm,
                           DATE(juald{$MM}.tgl) AS tgl
                    FROM {$cbg}.jual{$MM}, {$cbg}.juald{$MM}
                    WHERE jual{$MM}.no_bukti = juald{$MM}.no_bukti
                      AND juald{$MM}.flag = 'FC'
                      AND juald{$MM}.tgl BETWEEN :tgl1 AND :tgl2
                    GROUP BY tgl, KD_BRG
                    ORDER BY tgl, STAND, juald{$MM}.TYPE
                ) AS ageng, {$cbg}.brgfc
                WHERE ageng.kd_brg = brgfc.kd_brg
                ORDER BY tgl, STAND, TYPE
            ";

            $result = DB::select($query, [
                'tgl1' => $tgl1,
                'tgl2' => $tgl2
            ]);

            $this->logActivity('getPenjualanPHData', $cbg, $periode, count($result));

            return $result;
        } catch (\Exception $e) {
            Log::error('Error in getPenjualanPHData: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Mengambil data PB1 berdasarkan logika Delphi
     * Meniru query pebe.SQL.Text dari procedure tampil
     */
    private function getPB1Data($cbg, $periode, $tgl1, $tgl2)
    {
        try {
            // Extract MM dari periode
            $MM = substr(trim($periode), 0, 2);

            // Query PB1 berdasarkan logika Delphi
            $query = "
                SELECT :tinggi AS tinggil, :period AS periode, tgl,
                       SUM(ppn * 10) AS penjualan, SUM(ppn) AS PB1
                FROM {$cbg}.jual{$MM}
                WHERE FLAG = 'FC'
                  AND tgl BETWEEN :tgl1 AND :tgl2
                GROUP BY tgl
                ORDER BY tgl
            ";

            $result = DB::select($query, [
                'tinggi' => Carbon::parse($tgl2)->format('d/m/Y'),
                'period' => $MM,
                'tgl1' => $tgl1,
                'tgl2' => $tgl2
            ]);

            return $result;
        } catch (\Exception $e) {
            Log::error('Error in getPB1Data: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Mendapatkan tanggal awal bulan berdasarkan periode
     * Meniru logika cbperiodePropertiesChange dari Delphi
     */
    private function getStartOfMonth($periode)
    {
        try {
            // Format periode: MM/YYYY
            $dateParts = explode('/', $periode);
            if (count($dateParts) != 2) {
                throw new \Exception('Format periode tidak valid. Gunakan MM/YYYY');
            }

            $month = $dateParts[0];
            $year = $dateParts[1];

            return Carbon::createFromDate($year, $month, 1)->format('Y-m-d');
        } catch (\Exception $e) {
            Log::error('Error in getStartOfMonth: ' . $e->getMessage());
            return Carbon::now()->startOfMonth()->format('Y-m-d');
        }
    }

    /**
     * Mendapatkan tanggal akhir bulan berdasarkan periode
     * Meniru logika cbperiodePropertiesChange dari Delphi
     */
    private function getEndOfMonth($periode)
    {
        try {
            // Format periode: MM/YYYY
            $dateParts = explode('/', $periode);
            if (count($dateParts) != 2) {
                throw new \Exception('Format periode tidak valid. Gunakan MM/YYYY');
            }

            $month = $dateParts[0];
            $year = $dateParts[1];

            return Carbon::createFromDate($year, $month, 1)->endOfMonth()->format('Y-m-d');
        } catch (\Exception $e) {
            Log::error('Error in getEndOfMonth: ' . $e->getMessage());
            return Carbon::now()->endOfMonth()->format('Y-m-d');
        }
    }

    /**
     * Fungsi helper untuk Left String (meniru LeftStr dari Delphi)
     */
    private function leftStr($string, $count)
    {
        return substr($string, 0, $count);
    }

    /**
     * Fungsi helper untuk Right String (meniru RightStr dari Delphi)
     */
    private function rightStr($string, $count)
    {
        $length = strlen($string);
        if ($length < $count) {
            return $string;
        }
        return substr($string, $length - $count, $count);
    }

    /**
     * Export to Excel (meniru MXLSXClick dari Delphi)
     */
    public function exportToExcel(Request $request)
    {
        try {
            if (empty($request->cbg) || empty($request->periode)) {
                return response()->json(['error' => 'Cabang dan Periode harus diisi!'], 400);
            }

            $tgl1 = $request->tgl1 ?: $this->getStartOfMonth($request->periode);
            $tgl2 = $request->tgl2 ?: $this->getEndOfMonth($request->periode);

            $data = $this->getPenjualanPHData($request->cbg, $request->periode, $tgl1, $tgl2);

            if (empty($data)) {
                return response()->json(['message' => 'Tidak ada data untuk diekspor'], 200);
            }

            // Implementasi export Excel
            $filename = 'penjualan_ph_' . $request->cbg . '_' . str_replace('/', '', $request->periode) . '_' . date('YmdHis') . '.xlsx';

            return response()->json(['success' => true, 'filename' => $filename]);
        } catch (\Exception $e) {
            Log::error('Error in exportToExcel: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Generate laporan Jasper
     * Meniru frxreport2.ShowReport() dari Delphi
     */
    public function jasperPenjualanPHReport(Request $request)
    {
        try {
            $file = 'penjualanph';
            $PHPJasperXML = new PHPJasperXML();
            $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

            // Set session values
            session()->put('filter_cbg', $request->cbg);
            session()->put('filter_periode', $request->periode);
            session()->put('filter_tgl1', $request->tgl1);
            session()->put('filter_tgl2', $request->tgl2);

            $data = [];

            if (!empty($request->cbg) && !empty($request->periode)) {
                $tgl1 = $request->tgl1 ?: $this->getStartOfMonth($request->periode);
                $tgl2 = $request->tgl2 ?: $this->getEndOfMonth($request->periode);

                $hasilPenjualanPH = $this->getPenjualanPHData($request->cbg, $request->periode, $tgl1, $tgl2);
                $hasilPB1 = $this->getPB1Data($request->cbg, $request->periode, $tgl1, $tgl2);

                // Format data untuk laporan Jasper
                foreach ($hasilPenjualanPH as $item) {
                    $data[] = [
                        'CBG' => $request->cbg,
                        'PERIODE' => $request->periode,
                        'TGL1' => $tgl1,
                        'TGL2' => $tgl2,
                        'TGL' => $item->tgl ?? '',
                        'KD_BRG' => $item->KD_BRG ?? '',
                        'NA_BRG' => $item->NA_BRG ?? '',
                        'TYPE' => $item->TYPE ?? '',
                        'STAND' => $item->STAND ?? '',
                        'KODES' => $item->KODES ?? '',
                        'NAMAS' => $item->NAMAS ?? '',
                        'QTYLAKU' => $item->qtylaku ?? 0,
                        'TOTLAKU' => $item->totlaku ?? 0,
                        'QTYOB' => $item->qtyob ?? 0,
                        'TOTOB' => $item->totob ?? 0,
                        'QTYNM' => $item->qtynm ?? 0,
                        'TOTNM' => $item->totnm ?? 0,
                    ];
                }

                // Jika tidak ada data penjualan, tambahkan data PB1 saja
                if (empty($data) && !empty($hasilPB1)) {
                    foreach ($hasilPB1 as $pb1) {
                        $data[] = [
                            'CBG' => $request->cbg,
                            'PERIODE' => $request->periode,
                            'TGL1' => $tgl1,
                            'TGL2' => $tgl2,
                            'TGL' => $pb1->tgl ?? '',
                            'PENJUALAN' => $pb1->penjualan ?? 0,
                            'PB1' => $pb1->PB1 ?? 0,
                            'TINGGIL' => $pb1->tinggil ?? '',
                        ];
                    }
                }
            }

            $PHPJasperXML->setData($data);
            ob_end_clean();
            $PHPJasperXML->outpage("I");
        } catch (\Exception $e) {
            Log::error('Error in jasperPenjualanPHReport: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get daftar periode yang tersedia
     * Meniru perid.SQL dari FormShow procedure
     */
    public function getPeriodeList()
    {
        try {
            $query = "SELECT perio FROM perid ORDER BY perio DESC";
            return DB::select($query);
        } catch (\Exception $e) {
            Log::error('Error in getPeriodeList: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get daftar cabang yang valid
     */
    public function getCabangList()
    {
        try {
            $query = "
                SELECT KODE, NAMA, STA
                FROM tgz.toko
                WHERE STA IN ('MA', 'CB', 'DC', 'FC')
                ORDER BY NO_ID ASC
            ";

            return DB::select($query);
        } catch (\Exception $e) {
            Log::error('Error in getCabangList: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Validasi input parameters
     */
    private function validateInput($cbg, $periode, $tgl1 = null, $tgl2 = null)
    {
        if (empty($cbg)) {
            throw new \Exception('Cabang harus diisi!');
        }

        if (empty($periode)) {
            throw new \Exception('Periode harus diisi!');
        }

        // Validate cabang format and existence
        if (!preg_match('/^[A-Z0-9]+$/', $cbg)) {
            throw new \Exception('Format cabang tidak valid!');
        }

        // Validate periode format MM/YYYY
        if (!preg_match('/^\d{2}\/\d{4}$/', $periode)) {
            throw new \Exception('Format periode tidak valid! Gunakan MM/YYYY');
        }

        // Validate tanggal jika diisi
        if ($tgl1 && !$this->isValidDate($tgl1)) {
            throw new \Exception('Format tanggal mulai tidak valid!');
        }

        if ($tgl2 && !$this->isValidDate($tgl2)) {
            throw new \Exception('Format tanggal akhir tidak valid!');
        }

        // Validate cabang exists in database
        $cabangExists = DB::table('tgz.toko')
            ->where('KODE', $cbg)
            ->whereIn('STA', ['MA', 'CB', 'DC', 'FC'])
            ->exists();

        if (!$cabangExists) {
            throw new \Exception('Cabang tidak valid atau tidak aktif!');
        }

        return true;
    }

    /**
     * Helper method untuk validasi tanggal
     */
    private function isValidDate($date)
    {
        try {
            Carbon::parse($date);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Helper method untuk logging activity
     */
    private function logActivity($action, $cbg, $periode, $recordCount = 0)
    {
        Log::info("PenjualanPH: {$action}", [
            'cbg' => $cbg,
            'periode' => $periode,
            'record_count' => $recordCount,
            'user' => auth()->user()->id ?? 'system',
            'timestamp' => now()
        ]);
    }

    /**
     * Get summary data (meniru logic untuk ringkasan)
     */
    public function getSummaryData($cbg, $periode, $tgl1, $tgl2)
    {
        try {
            $MM = substr(trim($periode), 0, 2);

            $query = "
                SELECT
                    COUNT(DISTINCT DATE(juald{$MM}.tgl)) AS total_hari,
                    SUM(juald{$MM}.qty) AS total_qty,
                    SUM(juald{$MM}.total) AS total_penjualan,
                    COUNT(DISTINCT juald{$MM}.KD_BRG) AS total_item
                FROM {$cbg}.jual{$MM}, {$cbg}.juald{$MM}
                WHERE jual{$MM}.no_bukti = juald{$MM}.no_bukti
                  AND juald{$MM}.flag = 'FC'
                  AND juald{$MM}.tgl BETWEEN :tgl1 AND :tgl2
            ";

            $result = DB::select($query, [
                'tgl1' => $tgl1,
                'tgl2' => $tgl2
            ]);

            return !empty($result) ? $result[0] : null;
        } catch (\Exception $e) {
            Log::error('Error in getSummaryData: ' . $e->getMessage());
            return null;
        }
    }
}
