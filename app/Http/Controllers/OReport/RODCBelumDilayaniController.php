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

class RODCBelumDilayaniController extends Controller
{
    public function report()
    {
        $cbg = Cbg::groupBy('CBG')->get();
        $per = Perid::query()->get();

        // Initialize session variables
        session()->put('filter_cbg', '');

        return view('oreport_rodc_belumlayan.report')->with([
            'cbg' => $cbg,
            'per' => $per,
            'hasilRODCBelumLayan' => []
        ]);
    }

    public function getODCBelumDilayaniReport(Request $request)
    {
        $cbg = Cbg::groupBy('CBG')->get();
        $per = Perid::query()->get();

        // Set filter values to session
        session()->put('filter_cbg', $request->cbg ?? '');

        $hasilRODCBelumLayan = [];

        if (!empty($request->cbg)) {
            try {
                $hasilRODCBelumLayan = $this->getRODCBelumLayaniData($request->cbg);
            } catch (\Exception $e) {
                Log::error('Error in getRODCBelumLayaniReport: ' . $e->getMessage());
                return view('oreport_rodc_belumlayan.report')->with([
                    'cbg' => $cbg,
                    'per' => $per,
                    'hasilRODCBelumLayan' => [],
                    'error' => $e->getMessage()
                ]);
            }
        }

        return view('oreport_rodc_belumlayan.report')->with([
            'cbg' => $cbg,
            'per' => $per,
            'hasilRODCBelumLayan' => $hasilRODCBelumLayan
        ]);
    }

    /**
     * Implementasi logika dari procedure tampil() pada Delphi
     * Mengambil data RO DC yang belum dilayani dengan kondisi:
     * - PSN_DC = "*" (sudah dipesan DC)
     * - DATEDIFF(CURDATE(),date(TGL_PSN_DC)) = 2 (2 hari dari tanggal pesan DC)
     * - Tahun berjalan
     */
    private function getRODCBelumLayaniData($cbg)
    {
        try {
            // Validasi input
            $this->validateInput($cbg);

            // Get nama toko berdasarkan kode cabang (implementasi dari Delphi)
            $namaToko = $this->getNamaToko($cbg);

            if (empty($namaToko)) {
                throw new \Exception('Cabang tidak ditemukan atau tidak valid!');
            }

            // Query utama berdasarkan logika Delphi
            // SELECT :nmtoko as NA_TOKO, a.KD_BRG, a.NA_BRG, a.KET_UK, a.KET_KEM, b.PSN_DC,
            // b.TGL_PSN_DC, b.TD_OD, b.CAT_OD, b.TGL_OD
            // FROM sop.brg a, sop.brgdt b
            // WHERE a.KD_BRG=b.KD_BRG AND b.YER=year(now()) AND b.PSN_DC="*"
            // AND DATEDIFF(CURDATE(),date(b.TGL_PSN_DC))=2
            // ORDER BY a.KD_BRG
            $currentYear = date('Y');

            $query = "
                SELECT :namaToko as NA_TOKO,
                       a.KD_BRG,
                       a.NA_BRG,
                       a.KET_UK,
                       a.KET_KEM,
                       b.PSN_DC,
                       b.TGL_PSN_DC,
                       b.TD_OD,
                       b.CAT_OD,
                       b.TGL_OD
                FROM {$cbg}.brg a
                INNER JOIN {$cbg}.brgdt b ON a.KD_BRG = b.KD_BRG
                WHERE b.YER = :currentYear
                  AND b.PSN_DC = '*'
                  AND DATEDIFF(CURDATE(), DATE(b.TGL_PSN_DC)) = 2
                ORDER BY a.KD_BRG ASC
            ";

            $hasilData = DB::select($query, [
                'namaToko' => $namaToko,
                'currentYear' => $currentYear
            ]);

            // Transform data sesuai format yang dibutuhkan
            $result = [];
            foreach ($hasilData as $item) {
                $result[] = [
                    'NA_TOKO' => $item->NA_TOKO,
                    'KD_BRG' => $item->KD_BRG,
                    'NA_BRG' => $item->NA_BRG,
                    'KET_UK' => $item->KET_UK ?? '',
                    'KET_KEM' => $item->KET_KEM ?? '',
                    'PSN_DC' => $item->PSN_DC,
                    'TGL_PSN_DC' => $item->TGL_PSN_DC,
                    'TD_OD' => $item->TD_OD ?? '',
                    'CAT_OD' => $item->CAT_OD ?? '',
                    'TGL_OD' => $item->TGL_OD
                ];
            }

            // Log activity
            $this->logActivity('get_rodc_belum_layani', $cbg, '', count($result));

            return $result;
        } catch (\Exception $e) {
            Log::error('Error in getRODCBelumLayaniData: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get nama toko berdasarkan kode cabang
     * Implementasi dari query Delphi: SELECT NAMA_TOKO from toko WHERE KODE=:cbg
     */
    public function getNamaToko($cbg)
    {
        try {
            $result = DB::table('tgz.toko')
                ->select('NAMA_TOKO')
                ->where('KODE', $cbg)
                ->whereIn('STA', ['MA', 'CB', 'DC']) // Pastikan toko aktif
                ->first();

            return $result ? $result->NAMA_TOKO : '';
        } catch (\Exception $e) {
            Log::error('Error in getNamaToko: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Get daftar cabang yang valid
     */
    public function getCabangList()
    {
        try {
            $query = "
                SELECT KODE, NAMA_TOKO as NAMA, STA
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
     * Implementasi dari btnExcelClick pada Delphi
     */
    public function exportToExcel(Request $request)
    {
        try {
            if (empty($request->cbg)) {
                return response()->json(['error' => 'Cabang harus diisi!'], 400);
            }

            $data = $this->getRODCBelumLayaniData($request->cbg);

            if (empty($data)) {
                return response()->json(['message' => 'Tidak ada data untuk diekspor'], 200);
            }

            // Implementasi export Excel
            $filename = 'rodc_belum_layani_' . $request->cbg . '_' . date('YmdHis') . '.xlsx';

            // Prepare data for export
            $exportData = [];
            $exportData[] = ['Nama Toko', 'Kode Barang', 'Nama Barang', 'Ukuran', 'Kemasan', 'Status DC', 'Tgl Pesan DC', 'TD OD', 'Cat OD', 'Tgl OD'];

            foreach ($data as $item) {
                $exportData[] = [
                    $item['NA_TOKO'],
                    $item['KD_BRG'],
                    $item['NA_BRG'],
                    $item['KET_UK'],
                    $item['KET_KEM'],
                    $item['PSN_DC'],
                    $item['TGL_PSN_DC'],
                    $item['TD_OD'],
                    $item['CAT_OD'],
                    $item['TGL_OD']
                ];
            }

            // Return download response
            return response()->json([
                'success' => true,
                'filename' => $filename,
                'data' => $exportData
            ]);
        } catch (\Exception $e) {
            Log::error('Error in exportToExcel: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Generate laporan Jasper
     * Implementasi dari btnCetakClick yang memanggil frxRordDC_belumlayan.ShowReport()
     */
    public function jasperRODCBelumLayaniReport(Request $request)
    {
        try {
            if (empty($request->cbg)) {
                return response()->json(['error' => 'Cabang harus diisi!'], 400);
            }

            $data = $this->getRODCBelumLayaniData($request->cbg);

            if (empty($data)) {
                return response()->json(['message' => 'Tidak ada data untuk dicetak'], 200);
            }

            $file = 'rodc_belum_layani'; // Sesuaikan dengan nama file jasper report
            $PHPJasperXML = new PHPJasperXML();
            $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

            // Set session values
            session()->put('filter_cbg', $request->cbg ?? '');

            $reportData = [];
            foreach ($data as $item) {
                $reportData[] = [
                    'NA_TOKO' => $item['NA_TOKO'] ?? '',
                    'KD_BRG' => $item['KD_BRG'] ?? '',
                    'NA_BRG' => $item['NA_BRG'] ?? '',
                    'KET_UK' => $item['KET_UK'] ?? '',
                    'KET_KEM' => $item['KET_KEM'] ?? '',
                    'PSN_DC' => $item['PSN_DC'] ?? '',
                    'TGL_PSN_DC' => $item['TGL_PSN_DC'] ?? '',
                    'TD_OD' => $item['TD_OD'] ?? '',
                    'CAT_OD' => $item['CAT_OD'] ?? '',
                    'TGL_OD' => $item['TGL_OD'] ?? '',
                    // Tambahan informasi untuk report
                    'CBG' => $request->cbg,
                    'TANGGAL_CETAK' => date('Y-m-d H:i:s'),
                    'TOTAL_RECORDS' => count($data)
                ];
            }

            $PHPJasperXML->setData($reportData);

            // Set parameters untuk report
            $PHPJasperXML->arrayParameter = [
                "CBG" => $request->cbg ?? '',
                "NAMA_TOKO" => $data[0]['NA_TOKO'] ?? '',
                "TANGGAL_CETAK" => date('d/m/Y H:i:s'),
                "TOTAL_RECORDS" => count($data)
            ];

            ob_end_clean();
            $PHPJasperXML->outpage("I"); // I = Inline view, D = Download, F = Save to file

        } catch (\Exception $e) {
            Log::error('Error in jasperRODCBelumLayaniReport: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Search RODC belum layani by cabang
     */
    public function searchRODCBelumLayani(Request $request)
    {
        try {
            $cbg = $request->cbg;

            if (empty($cbg)) {
                return response()->json(['error' => 'Cabang harus diisi!'], 400);
            }

            $data = $this->getRODCBelumLayaniData($cbg);

            return response()->json([
                'success' => true,
                'data' => $data,
                'total_records' => count($data),
                'message' => count($data) > 0 ? 'Data berhasil ditemukan' : 'Tidak ada data..'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in searchRODCBelumLayani: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Validasi input parameters
     */
    private function validateInput($cbg)
    {
        if (empty($cbg)) {
            throw new \Exception('Cabang harus diisi!');
        }

        // Validate cabang format
        if (!preg_match('/^[A-Z0-9]+$/', $cbg)) {
            throw new \Exception('Format cabang tidak valid!');
        }

        // Validate cabang exists in toko table
        $cabangExists = DB::table('tgz.toko')
            ->where('KODE', $cbg)
            ->whereIn('STA', ['MA', 'CB', 'DC'])
            ->exists();

        if (!$cabangExists) {
            throw new \Exception('Cabang tidak valid atau tidak aktif!');
        }

        return true;
    }

    /**
     * Helper method untuk logging
     */
    private function logActivity($action, $cbg, $additionalInfo = '', $recordCount = 0)
    {
        Log::info("RODCBelumLayani: {$action}", [
            'cbg' => $cbg,
            'additional_info' => $additionalInfo,
            'record_count' => $recordCount,
            'user' => auth()->user()->id ?? 'system',
            'timestamp' => now()
        ]);
    }

    /**
     * Method untuk mendukung AJAX request dari view
     */
    public function ajaxGetRODCBelumLayani(Request $request)
    {
        try {
            $cbg = $request->get('cbg');

            if (empty($cbg)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parameter cabang harus diisi',
                    'data' => []
                ]);
            }

            $data = $this->getRODCBelumLayaniData($cbg);

            return response()->json([
                'success' => true,
                'message' => count($data) > 0 ? 'Data berhasil diambil' : 'Tidak ada data..',
                'data' => $data,
                'total' => count($data)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ]);
        }
    }

    /**
     * Method untuk preview data sebelum print
     */
    public function previewRODCBelumLayani(Request $request)
    {
        try {
            $cbg = $request->cbg;

            if (empty($cbg)) {
                return redirect()->back()->with('error', 'Cabang harus diisi!');
            }

            $data = $this->getRODCBelumLayaniData($cbg);
            $namaToko = $this->getNamaToko($cbg);

            return view('oreport_rodc_belumlayan.preview')->with([
                'data' => $data,
                'cbg' => $cbg,
                'namaToko' => $namaToko,
                'totalRecords' => count($data),
                'tanggalCetak' => date('d/m/Y H:i:s')
            ]);
        } catch (\Exception $e) {
            Log::error('Error in previewRODCBelumLayani: ' . $e->getMessage());
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Get summary data untuk dashboard
     */
    public function getSummaryRODCBelumLayani(Request $request)
    {
        try {
            $cbg = $request->get('cbg', '');

            if (empty($cbg)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parameter cabang diperlukan'
                ]);
            }

            $currentYear = date('Y');

            // Summary query berdasarkan logika Delphi
            $summaryQuery = "
                SELECT
                    COUNT(*) as total_item,
                    COUNT(DISTINCT a.KD_BRG) as total_kode_barang,
                    DATE(MIN(b.TGL_PSN_DC)) as earliest_order_date,
                    DATE(MAX(b.TGL_PSN_DC)) as latest_order_date
                FROM {$cbg}.brg a
                INNER JOIN {$cbg}.brgdt b ON a.KD_BRG = b.KD_BRG
                WHERE b.YER = :currentYear
                  AND b.PSN_DC = '*'
                  AND DATEDIFF(CURDATE(), DATE(b.TGL_PSN_DC)) = 2
            ";

            $summary = DB::select($summaryQuery, [
                'currentYear' => $currentYear
            ]);

            $result = [
                'total_item' => $summary[0]->total_item ?? 0,
                'total_kode_barang' => $summary[0]->total_kode_barang ?? 0,
                'earliest_order_date' => $summary[0]->earliest_order_date ?? null,
                'latest_order_date' => $summary[0]->latest_order_date ?? null,
                'nama_toko' => $this->getNamaToko($cbg)
            ];

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getSummaryRODCBelumLayani: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}