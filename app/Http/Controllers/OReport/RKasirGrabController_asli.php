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

class RKasirGrabController extends Controller
{
    /**
     * Tampilan utama report (sesuai dengan FormShow di Delphi)
     */
    public function report()
    {
        
        // provide dropdown data to the view to avoid undefined variables in Blade
        // $cbgList = $this->getCbgList();
        // $periods = $this->getPeriodsList();
        // $cbg = Cbg::groupBy('CBG')->get();
        $cbg = DB::SELECT("SELECT KODE FROM toko WHERE STA IN ('MA','CB','DC') ORDER BY NO_ID ASC");
        $kasir = DB::SELECT("SELECT KASIR FROM noks ORDER BY KASIR");
        $periods = Perid::query()->get();

        session()->put('filter_cbg', '');
        session()->put('filter_kasir', '');
        session()->put('filter_periode', '');

        return view('oreport_kasir_grab.report')->with([
            'hasilData' => [],
            'cbg' => $cbg,
            'kasir' => $kasir,
            'periods' => $periods,
        ]);
    }

    /**
     * Fungsi utama untuk mendapatkan data kasir grab
     * Menyesuaikan dengan logika query yang ada di Delphi
     */
    public function getSalesKasirGrabReport(Request $request)
    {
        $cbgCode = $request->cbg;
        $kasir = $request->kasir;
        $periode = $request->periode;

        session()->put('filter_cbg', $cbgCode);
        session()->put('filter_kasir', $kasir);
        session()->put('filter_periode', $periode);

        $hasilData = [];

        if (!empty($cbgCode) && !empty($periode)) {
            $hasilData = $this->getKasirGrabData($cbgCode, $kasir, $periode);
        }

        // Load dropdown lagi agar view bisa render
        $cbgList = DB::SELECT("SELECT KODE FROM toko WHERE STA IN ('MA','CB','DC') ORDER BY NO_ID ASC");
        $kasirList = DB::SELECT("SELECT KASIR FROM noks ORDER BY KASIR");
        $periodList = Perid::query()->get();

        return view('oreport_kasir_grab.report')->with([
            'hasilData' => $hasilData,
            'cbg' => $cbgList,
            'kasir' => $kasirList,
            'periods' => $periodList,
        ]);
    }


    /**
     * Fungsi untuk mendapatkan data kasir grab dari database
     * Query disesuaikan dengan logika yang kemungkinan ada di Delphi
     */
    private function getKasirGrabData($cbgCode, $kasir = null, $periode = null)
    {
        try {
            // Tentukan periode MM (sesuai dengan logika LeftStr di Delphi)
            $MM = $this->determinePeriode($periode);

            // Base query untuk kasir grab
            $query = "SELECT
                        jual{$MM}.cbg,
                        jual{$MM}.ksr,
                        jual{$MM}.tgl,
                        jual{$MM}.shift,
                        jual{$MM}.no_bukti,
                        juald{$MM}.kd_brg,
                        juald{$MM}.na_brg,
                        juald{$MM}.qty,
                        juald{$MM}.harga,
                        juald{$MM}.total,
                        juald{$MM}.dpp,
                        juald{$MM}.nppn
                      FROM {$cbgCode}.jual{$MM}
                      INNER JOIN {$cbgCode}.juald{$MM} ON jual{$MM}.no_bukti = juald{$MM}.no_bukti
                      WHERE jual{$MM}.flag = 'JL'
                      AND juald{$MM}.kd_brg <> ''";

            $params = [];

            // Filter kasir jika dipilih
            if (!empty($kasir)) {
                $query .= " AND jual{$MM}.ksr = ?";
                $params[] = $kasir;
            }

            // Tambahkan ordering
            $query .= " ORDER BY jual{$MM}.tgl, jual{$MM}.ksr, jual{$MM}.no_bukti";

            $result = DB::select($query, $params);

            // Jika tidak ada data, kembalikan null
            if (empty($result)) {
                return null;
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('Error in getKasirGrabData: ' . $e->getMessage());
            // Jika error, kembalikan null
            return null;
        }
    }

    /**
     * Helper function untuk menentukan periode MM
     * Mengimplementasikan logika LeftStr dari Delphi
     */
    private function determinePeriode($periode)
    {
        if (!empty($periode)) {
            // Implementasi LeftStr(S, 2) dari Delphi
            return substr(trim($periode), 0, 2);
        }

        // Default ke bulan sekarang jika periode kosong
        return Carbon::now()->format('m');
    }

    /**
     * Fungsi export ke Excel (sesuai dengan btnExcellClick di Delphi)
     * Mengimplementasikan logika export yang ada di Delphi
     */
    public function exportToExcel(Request $request)
    {
        try {
            $cbgCode = $request->cbg;
            $kasir = $request->kasir;
            $periode = $request->periode;

            // Validasi input
            if (empty($cbgCode) || empty($periode)) {
                return response()->json([
                    'error' => 'Cabang dan periode harus dipilih'
                ], 400);
            }

            // Get data untuk export
            $data = $this->getKasirGrabData($cbgCode, $kasir, $periode);

            if (empty($data)) {
                return response()->json([
                    'error' => 'Data tidak ditemukan'
                ], 400);
            }

            // Format data untuk Excel
            $exportData = [];
            foreach ($data as $row) {
                $exportData[] = [
                    'Cabang' => $row->cbg ?? '',
                    'Kasir' => $row->ksr ?? '',
                    'Tanggal' => $row->tgl ?? '',
                    'Shift' => $row->shift ?? '',
                    'No Bukti' => $row->no_bukti ?? '',
                    'Kode Barang' => $row->kd_brg ?? '',
                    'Nama Barang' => $row->na_brg ?? '',
                    'Qty' => $row->qty ?? 0,
                    'Harga' => $row->harga ?? 0,
                    'Total' => $row->total ?? 0,
                    'DPP' => $row->dpp ?? 0,
                    'PPN' => $row->nppn ?? 0,
                ];
            }

            // Generate filename sesuai dengan pattern di Delphi
            $filename = 'kasir_grab_' . $cbgCode . '_' . date('Y-m-d_H-i-s') . '.xlsx';

            return response()->json([
                'success' => true,
                'data' => $exportData,
                'filename' => $filename
            ]);
        } catch (\Exception $e) {
            Log::error('Error in exportToExcel: ' . $e->getMessage());
            return response()->json([
                'error' => 'Terjadi kesalahan saat export: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get CBG list untuk dropdown
     */
    private function getCbgList()
    {
        try {
            $query = "SELECT DISTINCT CBG FROM user ORDER BY cbg";
            $result = DB::select($query);

            // return [['cbg' => null]];

            // return $result;
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Table cbg tidak ditemukan: ' . $e->getMessage());
            return [['cbg' => null]];
        } catch (\Exception $e) {
            Log::error('Error in getCbgList: ' . $e->getMessage());
            return [['cbg' => null]];
        }
    }

    /**
     * Get periods list untuk dropdown
     */
    private function getPeriodsList()
    {
        try {
            $query = "SELECT PERIO FROM perid ORDER BY per DESC";
            return DB::select($query);
        } catch (\Exception $e) {
            Log::error('Error in getPeriodsList: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get kasir list dari table noks (sesuai dengan FormShow di Delphi)
     */
    private function getKasirList()
    {
        try {
            $query = "SELECT kasir FROM noks ORDER BY kasir";
            return DB::select($query);
        } catch (\Exception $e) {
            Log::error('Error in getKasirList: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * API endpoint untuk mendapatkan data kasir grab
     */
    public function apiGetKasirGrabData(Request $request)
    {
        try {
            $cbgCode = $request->cbg;
            $kasir = $request->kasir;
            $periode = $request->periode;

            if (empty($cbgCode) || empty($periode)) {
                return response()->json([
                    'error' => 'Cabang dan periode harus dipilih'
                ], 400);
            }

            $hasil = $this->getKasirGrabData($cbgCode, $kasir, $periode);

            return response()->json([
                'success' => true,
                'data' => $hasil,
                'count' => count($hasil)
            ]);
        } catch (\Exception $e) {
            Log::error('Error in apiGetKasirGrabData: ' . $e->getMessage());
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API untuk mendapatkan list dropdown data
     */
    public function apiGetDropdownData(Request $request)
    {
        try {
            $type = $request->type;
            $cbgCode = $request->cbg;

            $data = [];

            switch ($type) {
                case 'cbg':
                    $data = $this->getCbgList();
                    break;
                case 'periode':
                    $data = $this->getPeriodsList();
                    break;
                case 'kasir':
                    if (!empty($cbgCode)) {
                        // Get kasir untuk CBG tertentu
                        $query = "SELECT kasir FROM {$cbgCode}.noks ORDER BY kasir";
                        $data = DB::select($query);
                    } else {
                        $data = $this->getKasirList();
                    }
                    break;
            }

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Error in apiGetDropdownData: ' . $e->getMessage());
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Method untuk validasi data input
     */
    private function validateInputData($cbgCode, $periode)
    {
        $errors = [];

        if (empty($cbgCode)) {
            $errors[] = 'Cabang harus dipilih';
        }

        if (empty($periode)) {
            $errors[] = 'Periode harus dipilih';
        }

        return $errors;
    }

    /**
     * Generate Jasper Report (jika diperlukan)
     */
    public function jasperKasirGrabReport(Request $request)
    {
        try {
            $cbgCode = $request->cbg;
            $kasir = $request->kasir;
            $periode = $request->periode;

            // Validasi
            $errors = $this->validateInputData($cbgCode, $periode);
            if (!empty($errors)) {
                return response()->json(['error' => implode(', ', $errors)], 400);
            }

            $PHPJasperXML = new PHPJasperXML();
            $PHPJasperXML->load_xml_file(base_path() . '/app/reportc01/phpjasperxml/rkasir_grab.jrxml');

            // Get data untuk report
            $results = $this->getKasirGrabData($cbgCode, $kasir, $periode);

            $data = [];
            foreach ($results as $row) {
                $data[] = [
                    'CBG' => $row->cbg ?? '',
                    'KSR' => $row->ksr ?? '',
                    'TGL' => $row->tgl ?? '',
                    'SHIFT' => $row->shift ?? '',
                    'NO_BUKTI' => $row->no_bukti ?? '',
                    'KD_BRG' => $row->kd_brg ?? '',
                    'NA_BRG' => $row->na_brg ?? '',
                    'QTY' => $row->qty ?? 0,
                    'HARGA' => $row->harga ?? 0,
                    'TOTAL' => $row->total ?? 0,
                    'DPP' => $row->dpp ?? 0,
                    'NPPN' => $row->nppn ?? 0,
                    'PERIODE' => $periode,
                    'KASIR_FILTER' => $kasir ?? 'Semua Kasir',
                ];
            }

            $PHPJasperXML->setData($data);
            ob_end_clean();
            $PHPJasperXML->outpage("I");
        } catch (\Exception $e) {
            Log::error('Error in jasperKasirGrabReport: ' . $e->getMessage());
            return response()->json([
                'error' => 'Terjadi kesalahan saat generate report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Method untuk mendapatkan summary data
     */
    public function getSummaryData($cbgCode, $kasir, $periode)
    {
        try {
            $MM = $this->determinePeriode($periode);

            $query = "SELECT
                        COUNT(DISTINCT jual{$MM}.no_bukti) as total_transaksi,
                        COUNT(DISTINCT juald{$MM}.kd_brg) as total_item,
                        SUM(juald{$MM}.qty) as total_qty,
                        SUM(juald{$MM}.total) as total_amount
                      FROM {$cbgCode}.jual{$MM}
                      INNER JOIN {$cbgCode}.juald{$MM} ON jual{$MM}.no_bukti = juald{$MM}.no_bukti
                      WHERE jual{$MM}.flag = 'JL'
                      AND juald{$MM}.kd_brg <> ''";

            $params = [];

            if (!empty($kasir)) {
                $query .= " AND jual{$MM}.ksr = ?";
                $params[] = $kasir;
            }

            $result = DB::select($query, $params);
            return $result[0] ?? null;
        } catch (\Exception $e) {
            Log::error('Error in getSummaryData: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * API endpoint untuk mendapatkan summary
     */
    public function apiGetSummaryData(Request $request)
    {
        try {
            $cbgCode = $request->cbg;
            $kasir = $request->kasir;
            $periode = $request->periode;

            if (empty($cbgCode) || empty($periode)) {
                return response()->json([
                    'error' => 'Cabang dan periode harus dipilih'
                ], 400);
            }

            $summary = $this->getSummaryData($cbgCode, $kasir, $periode);

            return response()->json([
                'success' => true,
                'summary' => $summary
            ]);
        } catch (\Exception $e) {
            Log::error('Error in apiGetSummaryData: ' . $e->getMessage());
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
