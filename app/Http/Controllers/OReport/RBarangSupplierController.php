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

class RBarangSupplierController extends Controller
{
    public function report()
    {
        $cbg = Cbg::groupBy('CBG')->get();
        $per = Perid::query()->get();

        // Initialize session variables
        session()->put('filter_cbg', '');
        session()->put('filter_kodes', '');

        return view('oreport_barangsupplier.report')->with([
            'cbg' => $cbg,
            'per' => $per,
            'hasilBarangSupplier' => []
        ]);
    }

    public function getBarangSupplierReport(Request $request)
    {
        $cbg = Cbg::groupBy('CBG')->get();
        $per = Perid::query()->get();
    
        // Set filter values to session
        session()->put('filter_cbg', $request->cbg ?? '');
        session()->put('filter_kodes', $request->kodes ?? '');

        $hasilBarangSupplier = [];

        if (!empty($request->cbg) && !empty($request->kodes)) {
            try {
                $hasilBarangSupplier = $this->getBarangSupplierData($request->cbg, $request->kodes);
            } catch (\Exception $e) {
                Log::error('Error in getBarangSupplierReport: ' . $e->getMessage());
                return view('oreport_barangsupplier.report')->with([
                    'cbg' => $cbg,
                    'per' => $per,
                    'hasilBarangSupplier' => [],
                    'error' => $e->getMessage()
                ]);
            }
        }

        return view('oreport_barangsupplier.report')->with([
            'cbg' => $cbg,
            'per' => $per,
            'hasilBarangSupplier' => $hasilBarangSupplier
        ]);
    }

    /**
     * Mengimplementasikan logika dari procedure tampil() pada Delphi
     * Query: SELECT :kodes as kodes, KD_BRG, NA_BRG, KET_UK, SUPP FROM BRG WHERE SUPP=:KODES ORDER BY KD_BRG ASC
     */
    private function getBarangSupplierData($cbg, $kodes)
    {
        try {
            // Validasi input
            $this->validateInput($cbg, $kodes);

            // Validate cabang exists in toko table
            $cabangExists = DB::table('toko')
                ->where('KODE', $cbg)
                ->whereIn('STA', ['MA', 'CB', 'DC'])
                ->exists();
            // dd($cabangExists);

            if (!$cabangExists) {
                throw new \Exception('Cabang tidak valid atau tidak aktif!');
            }

            $hasilData = DB::select("SELECT '$kodes' as KODES,
                       KD_BRG,
                       NA_BRG,
                       KET_UK,
                       SUPP
                FROM brg
                WHERE SUPP = '$kodes'
                ORDER BY KD_BRG ASC");

            // Transform data sesuai format yang dibutuhkan
            $result = [];
            foreach ($hasilData as $item) {
                $result[] = [
                    'KODES' => $item->KODES,
                    'KD_BRG' => $item->KD_BRG,
                    'NA_BRG' => $item->NA_BRG,
                    'KET_UK' => $item->KET_UK,
                    'SUPP' => $item->SUPP
                ];
            }

            // Log activity
            $this->logActivity('get_barang_supplier', $cbg, $kodes, count($result));

            return $result;
        } catch (\Exception $e) {
            Log::error('Error in getBarangSupplierData: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Helper functions dari Delphi
     * Function LeftStr(S:String;Count:integer):String;
     */
    private function leftStr($string, $count)
    {
        return substr($string, 0, $count);
    }

    /**
     * Function RightStr(S:String;Count:integer):String;
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
     * Get daftar supplier untuk dropdown
     * Implementasi untuk mendukung pencarian supplier
     */
    public function getSupplierList($cbg)
    {
        try {
            if (empty($cbg)) {
                return [];
            }

            $query = "
                SELECT DISTINCT SUPP
                FROM {$cbg}.brg
                WHERE SUPP IS NOT NULL
                  AND SUPP != ''
                  AND SUPP != '0'
                ORDER BY SUPP ASC
            ";

            return DB::select($query);
        } catch (\Exception $e) {
            Log::error('Error in getSupplierList: ' . $e->getMessage());
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
            if (empty($request->cbg) || empty($request->kodes)) {
                return response()->json(['error' => 'Cabang dan Kode Supplier harus diisi!'], 400);
            }

            $data = $this->getBarangSupplierData($request->cbg, $request->kodes);

            if (empty($data)) {
                return response()->json(['message' => 'Tidak ada data untuk diekspor'], 200);
            }

            // Implementasi export Excel menggunakan library yang sesuai
            $filename = 'barang_supplier_' . $request->cbg . '_' . str_replace(['/', '\\', ' '], '_', $request->kodes) . '_' . date('YmdHis') . '.xlsx';

            // Return download response
            return response()->json(['success' => true, 'filename' => $filename]);
        } catch (\Exception $e) {
            Log::error('Error in exportToExcel: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Generate laporan Jasper
     * Implementasi dari btnSubBeliClick yang memanggil frxreport1.ShowReport()
     */
    public function jasperBarangSupplierReport(Request $request)
    {
        try {
            $file = 'report_barang_supplier'; // Sesuaikan dengan nama file jasper report
            $PHPJasperXML = new PHPJasperXML();
            $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

            // Set session values
            session()->put('filter_cbg', $request->cbg ?? '');
            session()->put('filter_kodes', $request->kodes ?? '');

            $data = [];

            if (!empty($request->cbg) && !empty($request->kodes)) {
                $hasilBarangSupplier = $this->getBarangSupplierData($request->cbg, $request->kodes);

                foreach ($hasilBarangSupplier as $item) {
                    $data[] = [
                        'KODES' => $item['KODES'] ?? '',
                        'KD_BRG' => $item['KD_BRG'] ?? '',
                        'NA_BRG' => $item['NA_BRG'] ?? '',
                        'KET_UK' => $item['KET_UK'] ?? '',
                        'SUPP' => $item['SUPP'] ?? '',
                        // Tambahan informasi yang mungkin dibutuhkan untuk report
                        'CBG' => $request->cbg,
                        'TANGGAL_CETAK' => date('d-m-Y')
                    ];
                }
            }

            $PHPJasperXML->setData($data);
            // dd($data);

            // Set parameters untuk report
            $PHPJasperXML->arrayParameter = [
                "CBG" => $request->cbg ?? '',
                "KODES" => $request->kodes ?? '',
                "TANGGAL_CETAK" => date('d/m/Y')
            ];

            ob_end_clean();
            $PHPJasperXML->outpage("I"); // I = Inline view, D = Download, F = Save to file

        } catch (\Exception $e) {
            Log::error('Error in jasperBarangSupplierReport: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Search barang by supplier code
     * Implementasi dari logika search yang ada di form
     */
    public function searchBarangBySupplier(Request $request)
    {
        try {
            $cbg = $request->cbg;
            $kodes = trim($request->kodes);

            if (empty($cbg) || empty($kodes)) {
                return response()->json(['error' => 'Cabang dan Kode Supplier harus diisi!'], 400);
            }

            $data = $this->getBarangSupplierData($cbg, $kodes);

            return response()->json([
                'success' => true,
                'data' => $data,
                'total_records' => count($data)
            ]);
        } catch (\Exception $e) {
            Log::error('Error in searchBarangBySupplier: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Validasi input parameters
     */
    private function validateInput($cbg, $kodes)
    {
        if (empty($cbg)) {
            throw new \Exception('Cabang harus diisi!');
        }

        if (empty($kodes)) {
            throw new \Exception('Kode Supplier harus diisi!');
        }

        // Validate cabang format
        if (!preg_match('/^[A-Z0-9]+$/', $cbg)) {
            throw new \Exception('Format cabang tidak valid!');
        }

        // Validate kodes format - bisa berisi karakter alphanumeric dan beberapa karakter khusus
        if (!preg_match('/^[A-Z0-9\-_\/\s]+$/i', $kodes)) {
            throw new \Exception('Format kode supplier tidak valid!');
        }

        return true;
    }

    /**
     * Helper method untuk logging
     */
    private function logActivity($action, $cbg, $kodes, $recordCount = 0)
    {
        Log::info("BarangSupplier: {$action}", [
            'cbg' => $cbg,
            'kodes' => $kodes,
            'record_count' => $recordCount,
            'user' => auth()->user()->id ?? 'system',
            'timestamp' => now()
        ]);
    }

    /**
     * Get nama toko berdasarkan kode cabang
     * Implementasi dari query: SELECT NA_TOKO from toko where toko.KODE=:cbg
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
     * Method untuk mendukung AJAX request dari view
     */
    public function ajaxGetBarangSupplier(Request $request)
    {
        try {
            $cbg = $request->get('cbg');
            $kodes = $request->get('kodes');

            if (empty($cbg) || empty($kodes)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parameter tidak lengkap',
                    'data' => []
                ]);
            }

            $data = $this->getBarangSupplierData($cbg, $kodes);

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diambil',
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
    public function previewBarangSupplier(Request $request)
    {
        try {
            $cbg = $request->cbg;
            $kodes = $request->kodes;

            if (empty($cbg) || empty($kodes)) {
                return redirect()->back()->with('error', 'Cabang dan Kode Supplier harus diisi!');
            }

            $data = $this->getBarangSupplierData($cbg, $kodes);
            $namaToko = $this->getNamaToko($cbg);

            return view('oreport_barangsupplier.preview')->with([
                'data' => $data,
                'cbg' => $cbg,
                'kodes' => $kodes,
                'namaToko' => $namaToko,
                'totalRecords' => count($data)
            ]);
        } catch (\Exception $e) {
            Log::error('Error in previewBarangSupplier: ' . $e->getMessage());
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}