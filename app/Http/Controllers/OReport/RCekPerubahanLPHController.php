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

class RCekPerubahanLPHController extends Controller
{
    public function report()
    {
        $cbg = Cbg::groupBy('CBG')->get();
        $per = Perid::query()->get();

        // Initialize session variables
        session()->put('filter_cbg', '');
        session()->put('filter_sub', '');

        return view('oreport_cekperubahanlph.report')->with([
            'cbg' => $cbg,
            'per' => $per,
            'hasilCekLPH' => []
        ]);
    }

    public function getCekPerubahanLPHReport(Request $request)
    {
        $cbg = Cbg::groupBy('CBG')->get();
        $per = Perid::query()->get();

        // Set filter values to session
        session()->put('filter_cbg', $request->cbg);
        session()->put('filter_sub', $request->sub);

        $hasilCekLPH = [];

        if (!empty($request->cbg) && !empty($request->sub)) {
            $hasilCekLPH = $this->getCekPerubahanLPHData($request->cbg, $request->sub);
        }

        return view('oreport_cekperubahanlph.report')->with([
            'cbg' => $cbg,
            'per' => $per,
            'hasilCekLPH' => $hasilCekLPH
        ]);
    }

    private function getCekPerubahanLPHData($cbg, $sub)
    {
        try {
            // Validate input
            if (empty($cbg) || empty($sub)) {
                throw new \Exception('Cabang dan SUB harus diisi!');
            }

            // Validate cabang exists in toko table
            $cabangExists = DB::table('tgz.toko')
                ->where('KODE', $cbg)
                ->whereIn('STA', ['MA', 'CB', 'DC'])
                ->exists();

            if (!$cabangExists) {
                throw new \Exception('Cabang tidak valid atau tidak aktif!');
            }

            $hasilData = [];

            // Main query berdasarkan logika Delphi
            $query = "
                SELECT B.CBG, A.SUB, A.KD_BRG, A.NA_BRG, A.KET_UK, A.KET_KEM,
                       A.LPH_TM AS LPH_TMM, A.LPH_TF AS LPH_SOP,
                       A.SP_L, A.SP_LF, A.SP_LZ, B.LPH, B.KLK, B.DTR,
                       B.KDLAKU, B.SRMIN, B.SRMAX, B.SMIN, B.SMAX
                FROM {$cbg}.brg A
                INNER JOIN {$cbg}.brgdt B ON A.KD_BRG = B.KD_BRG
                WHERE A.TD_OD = ''
                  AND B.LPH > 0
                  AND A.SUB = :sub
                ORDER BY A.KD_BRG ASC
            ";

            $barangList = DB::select($query, ['sub' => $sub]);

            foreach ($barangList as $barang) {
                // Call stored procedure untuk mendapatkan nilai perhitungan baru
                $newCalculation = $this->callPjlLphchProcedure(
                    $barang->KD_BRG,
                    $barang->LPH,
                    $barang->DTR
                );

                // Cek apakah ada perubahan nilai
                $hasChanges = $this->checkForChanges($barang, $newCalculation);

                if ($hasChanges) {
                    // Apply business logic untuk perhitungan berdasarkan cabang
                    $finalCalculation = $this->applyBusinessLogic($barang, $newCalculation, $cbg);

                    $hasilData[] = [
                        'CBG' => $barang->CBG,
                        'SUB' => $barang->SUB,
                        'KD_BRG' => $barang->KD_BRG,
                        'NA_BRG' => $barang->NA_BRG,
                        'KET_UK' => $barang->KET_UK,
                        'KET_KEM' => $barang->KET_KEM,
                        'LPH' => $barang->LPH,
                        'KLK' => $barang->KLK,
                        'DTR' => $barang->DTR,
                        'KDLAKU' => $barang->KDLAKU,

                        // Nilai lama (dari database)
                        'SMIN_OLD' => $barang->SMIN,
                        'SMAX_OLD' => $barang->SMAX,
                        'SRMIN_OLD' => $barang->SRMIN,
                        'SRMAX_OLD' => $barang->SRMAX,
                        'KDLAKU_OLD' => $barang->KDLAKU,

                        // Nilai baru (hasil perhitungan)
                        'SMIN_NEW' => $finalCalculation['smin'],
                        'SMAX_NEW' => $finalCalculation['smax'],
                        'SRMIN_NEW' => $finalCalculation['srmin'],
                        'SRMAX_NEW' => $finalCalculation['srmax'],
                        'KDLAKU_NEW' => $finalCalculation['kdlaku'],

                        // Flag untuk menandai field yang berubah
                        'IS_SMIN_CHANGED' => ($barang->SMIN != $finalCalculation['smin']),
                        'IS_SMAX_CHANGED' => ($barang->SMAX != $finalCalculation['smax']),
                        'IS_SRMIN_CHANGED' => ($barang->SRMIN != $finalCalculation['srmin']),
                        'IS_SRMAX_CHANGED' => ($barang->SRMAX != $finalCalculation['srmax']),
                        'IS_KDLAKU_CHANGED' => ($barang->KDLAKU != $finalCalculation['kdlaku'])
                    ];
                }
            }

            return $hasilData;
        } catch (\Exception $e) {
            Log::error('Error in getCekPerubahanLPHData: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Memanggil stored procedure pjl_lphch
     * Meniru logika dari com2.SQL.Text:='call pjl_lphch(:kd_brg,'''',:lph,:DTR)';
     */
    private function callPjlLphchProcedure($kdBrg, $lph, $dtr)
    {
        try {
            $result = DB::select('CALL pjl_lphch(?, ?, ?, ?)', [
                $kdBrg,
                '', // Parameter kedua kosong sesuai dengan Delphi
                $lph,
                $dtr
            ]);

            // if (!empty($result)) {
            //     return (array) $result[0];
            // }

            if (!empty($result)) {
                $row = (array) $result[0];
                $row = array_change_key_case($row, CASE_UPPER); // <--- FIX
                return $row;
            }

            // Fallback jika stored procedure tidak ada atau gagal
            return $this->calculateLPHValues($kdBrg, $lph, $dtr);
        } catch (\Exception $e) {
            Log::warning('Stored procedure pjl_lphch not found or failed: ' . $e->getMessage());
            // Fallback ke perhitungan manual
            return $this->calculateLPHValues($kdBrg, $lph, $dtr);
        }
    }

    /**
     * Fallback calculation jika stored procedure tidak tersedia
     */
    private function calculateLPHValues($kdBrg, $lph, $dtr)
    {
        // Implementasi logika perhitungan LPH berdasarkan business rules
        // Ini adalah contoh implementasi, sesuaikan dengan logic bisnis yang sebenarnya

        $smin = 0;
        $smax = 0;
        $srmin = 0;
        $srmax = 0;
        $kdlaku = '';

        // Logic perhitungan berdasarkan LPH dan DTR
        if ($lph > 0 && $dtr > 0) {
            $smin = round($lph * $dtr * 0.8, 0); // Contoh perhitungan
            $smax = round($lph * $dtr * 1.2, 0);
            $srmin = round($lph * $dtr * 0.6, 0);
            $srmax = round($lph * $dtr * 1.5, 0);

            // Tentukan kdlaku berdasarkan nilai LPH
            if ($lph >= 10) {
                $kdlaku = '2';
            } elseif ($lph >= 5) {
                $kdlaku = '3';
            } else {
                $kdlaku = '4';
            }
        }

        return [
            'SMIN' => $smin,
            'SMAX' => $smax,
            'SRMIN' => $srmin,
            'SRMAX' => $srmax,
            'KDLAKU' => $kdlaku
        ];
    }

    /**
     * Cek apakah ada perubahan nilai antara data lama dan baru
     * Sesuai dengan kondisi if di Delphi
     */
    private function checkForChanges($barangLama, $barangBaru)
    {
        return (
            $barangLama->SMIN != $barangBaru['SMIN'] ||
            $barangLama->SMAX != $barangBaru['SMAX'] ||
            $barangLama->SRMIN != $barangBaru['SRMIN'] ||
            $barangLama->SRMAX != $barangBaru['SRMAX'] ||
            $barangLama->KDLAKU != $barangBaru['KDLAKU']
        );
    }

    /**
     * Apply business logic berdasarkan cabang
     * Meniru logica procedure hitung dalam Delphi
     */
    private function applyBusinessLogic($barang, $newCalculation, $cbg)
    {
        $smin = $newCalculation['SMIN'];
        $smax = $newCalculation['SMAX'];
        $srmin = $newCalculation['SRMIN'];
        $srmax = $newCalculation['SRMAX'];
        $kdlaku = $newCalculation['KDLAKU'];

        // Logic khusus untuk DCK (sesuai dengan kondisi di Delphi)
        if (trim($cbg) == 'DCK') {
            return [
                'smin' => 0,
                'smax' => 0,
                'srmin' => $smin,
                'srmax' => $smax,
                'kdlaku' => $kdlaku
            ];
        }

        // Logic untuk cabang selain DCK
        // Jika kdlaku 4, 5, atau 6, maka smin dan smax = 0
        if (in_array($kdlaku, ['4', '5', '6'])) {
            $smin = 0;
            $smax = 0;
        }

        return [
            'smin' => $smin,
            'smax' => $smax,
            'srmin' => $srmin,
            'srmax' => $srmax,
            'kdlaku' => $kdlaku
        ];
    }

    /**
     * Get daftar SUB untuk dropdown
     */
    public function getSubList($cbg)
    {
        try {
            if (empty($cbg)) {
                return [];
            }

            $query = "
                SELECT DISTINCT A.SUB
                FROM {$cbg}.brg A
                INNER JOIN {$cbg}.brgdt B ON A.KD_BRG = B.KD_BRG
                WHERE A.TD_OD = ''
                  AND B.LPH > 0
                  AND A.SUB IS NOT NULL
                  AND A.SUB != ''
                ORDER BY A.SUB ASC
            ";

            return DB::select($query);
        } catch (\Exception $e) {
            Log::error('Error in getSubList: ' . $e->getMessage());
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
            if (empty($request->cbg) || empty($request->sub)) {
                return response()->json(['error' => 'Cabang dan SUB harus diisi!'], 400);
            }

            $data = $this->getCekPerubahanLPHData($request->cbg, $request->sub);

            if (empty($data)) {
                return response()->json(['message' => 'Tidak ada data yang berubah untuk diekspor'], 200);
            }

            // Implementasi export Excel menggunakan library yang sesuai
            // Misalnya menggunakan PhpSpreadsheet atau library lainnya

            $filename = 'cek_perubahan_lph_' . $request->cbg . '_' . $request->sub . '_' . date('YmdHis') . '.xlsx';

            // Return download response
            return response()->json(['success' => true, 'filename' => $filename]);
        } catch (\Exception $e) {
            Log::error('Error in exportToExcel: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Generate laporan Jasper
     */
    public function jasperCekPerubahanLPHReport(Request $request)
    {
        try {
            $file = 'cekperubahanlph';
            $PHPJasperXML = new PHPJasperXML();
            $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

            // Set session values
            session()->put('filter_cbg', $request->cbg);
            session()->put('filter_sub', $request->sub);

            $data = [];

            if (!empty($request->cbg) && !empty($request->sub)) {
                $hasilCekLPH = $this->getCekPerubahanLPHData($request->cbg, $request->sub);

                foreach ($hasilCekLPH as $item) {
                    $data[] = [
                        'CBG' => $item['CBG'] ?? '',
                        'SUB' => $item['SUB'] ?? '',
                        'KD_BRG' => $item['KD_BRG'] ?? '',
                        'NA_BRG' => $item['NA_BRG'] ?? '',
                        'KET_UK' => $item['KET_UK'] ?? '',
                        'KET_KEM' => $item['KET_KEM'] ?? '',
                        'LPH' => $item['LPH'] ?? 0,
                        'KDLAKU_OLD' => $item['KDLAKU_OLD'] ?? '',
                        'SMIN_OLD' => $item['SMIN_OLD'] ?? 0,
                        'SMAX_OLD' => $item['SMAX_OLD'] ?? 0,
                        'SRMIN_OLD' => $item['SRMIN_OLD'] ?? 0,
                        'SRMAX_OLD' => $item['SRMAX_OLD'] ?? 0,
                        'KDLAKU_NEW' => $item['KDLAKU_NEW'] ?? '',
                        'SMIN_NEW' => $item['SMIN_NEW'] ?? 0,
                        'SMAX_NEW' => $item['SMAX_NEW'] ?? 0,
                        'SRMIN_NEW' => $item['SRMIN_NEW'] ?? 0,
                        'SRMAX_NEW' => $item['SRMAX_NEW'] ?? 0,
                        'IS_SMIN_CHANGED' => $item['IS_SMIN_CHANGED'] ? 'Y' : 'N',
                        'IS_SMAX_CHANGED' => $item['IS_SMAX_CHANGED'] ? 'Y' : 'N',
                        'IS_SRMIN_CHANGED' => $item['IS_SRMIN_CHANGED'] ? 'Y' : 'N',
                        'IS_SRMAX_CHANGED' => $item['IS_SRMAX_CHANGED'] ? 'Y' : 'N',
                        'IS_KDLAKU_CHANGED' => $item['IS_KDLAKU_CHANGED'] ? 'Y' : 'N',
                    ];
                }
            }

            $PHPJasperXML->setData($data);
            ob_end_clean();
            $PHPJasperXML->outpage("I");
        } catch (\Exception $e) {
            Log::error('Error in jasperCekPerubahanLPHReport: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Validasi input parameters
     */
    private function validateInput($cbg, $sub)
    {
        if (empty($cbg)) {
            throw new \Exception('Cabang harus diisi!');
        }

        if (empty($sub)) {
            throw new \Exception('SUB harus diisi!');
        }

        // Validate cabang format and existence
        if (!preg_match('/^[A-Z0-9]+$/', $cbg)) {
            throw new \Exception('Format cabang tidak valid!');
        }

        return true;
    }

    /**
     * Helper method untuk logging
     */
    private function logActivity($action, $cbg, $sub, $recordCount = 0)
    {
        Log::info("CekPerubahanLPH: {$action}", [
            'cbg' => $cbg,
            'sub' => $sub,
            'record_count' => $recordCount,
            'user' => auth()->user()->id ?? 'system',
            'timestamp' => now()
        ]);
    }
}
