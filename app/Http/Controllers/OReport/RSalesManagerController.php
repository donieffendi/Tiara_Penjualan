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

class RSalesManagerController extends Controller
{
    public function report()
    {
        // Get CBG data using query instead of model
        // $cbg = $this->getCbgList();
        $cbg = DB::SELECT("SELECT KODE, STA FROM tgz.toko WHERE STA IN ('MA', 'CB') ORDER BY NO_ID ASC");

        // Initialize session variables for sales manager report
        session()->put('filter_cbg', '');
        session()->put('report_type', 1); // 1=Detail Report (repjuald), 2=Summary Report (repjual)

        return view('oreport_sales_manager.report')->with([
            'cbg' => $cbg,
            'hasilDetail' => [],
            'hasilSummary' => [],
        ]);
    }

    public function getSalesManagerReport(Request $request)
    {
        $cbg = $this->getCbgList();

        // Get filter values
        $cbgCode = $request->cbg;
        $reportType = $request->report_type ?? 1; // 1=Detail, 2=Summary

        // Store in session
        session()->put('filter_cbg', $cbgCode);
        session()->put('report_type', $reportType);

        $hasilDetail = [];
        $hasilSummary = [];

        if (!empty($cbgCode)) {
            if ($reportType == 1) {
                // Detail Report - sesuai dengan Button1Click logic (repjuald)
                $hasilDetail = $this->getSalesDetailReport($cbgCode);
            } else {
                // Summary Report - sesuai dengan Button2Click logic (repjual)
                $hasilSummary = $this->getSalesSummaryReport($cbgCode);
            }
        }

        return view('oreport_sales_manager.report')->with([
            'cbg' => $cbg,
            'hasilDetail' => $hasilDetail,
            'hasilSummary' => $hasilSummary,
            'reportType' => $reportType,
        ]);
    }

    /**
     * Get CBG list using direct query instead of model
     */
    private function getCbgList()
    {
        try {
            $query = "
                SELECT KODE, STA
                FROM tgz.toko
                WHERE STA IN ('MA', 'CB')
                ORDER BY NO_ID ASC
            ";

            return DB::select($query);
        } catch (\Exception $e) {
            Log::error('Error in getCabangList: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Sesuai dengan Button1Click di Delphi - Detail Sales Report (repjuald)
     * Mengambil data detail penjualan dari repjuald
     */
    private function getSalesDetailReport($cbgCode)
    {
        try {
            // Query sesuai dengan logika Button1Click:
            // perincian.SQL.Text:='select * from repjuald where cbg=:cbg ';
            $query = "SELECT
                        SUB,
                        SUB2,
                        KD_BRG,
                        NA_BRG,
                        BARCODE,
                        qty,
                        harga,
                        diskon,
                        disc,
                        ppn,
                        nppn,
                        dpp,
                        tkp,
                        total,
                        flag,
                        type,
                        per,
                        kodes,
                        TGL,
                        CBG
                      FROM repjuald
                      WHERE cbg = ?
                      ORDER BY TGL, KD_BRG";

            return DB::select($query, [$cbgCode]);
        } catch (\Exception $e) {
            Log::error('Error in getSalesDetailReport: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Sesuai dengan Button2Click di Delphi - Summary Sales Report (repjual)
     * Mengambil data summary penjualan dari repjual
     */
    private function getSalesSummaryReport($cbgCode)
    {
        try {
            // Query sesuai dengan logika Button2Click:
            // ksr.SQL.Text:='select * from repjual where cbg=:cbg ';
            $query = "SELECT
                        *
                      FROM repjual
                      WHERE cbg = ?
                      ORDER BY TGL";

            return DB::select($query, [$cbgCode]);
        } catch (\Exception $e) {
            Log::error('Error in getSalesSummaryReport: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Method untuk mendapatkan nama toko menggunakan query langsung
     */
    private function getNamaToko($cbgCode)
    {
        try {
            $query = "SELECT NA_TOKO FROM {$cbgCode}.toko WHERE KODE = ?";
            $result = DB::select($query, [$cbgCode]);
            return $result[0]->NA_TOKO ?? '';
        } catch (\Exception $e) {
            Log::error('Error in getNamaToko: ' . $e->getMessage());
            return '';
        }
    }

    public function jasperSalesManagerReport(Request $request)
    {
        $reportType = $request->report_type ?? 1;
        $cbgCode = $request->cbg;

        // Tentukan file report berdasarkan tipe (sesuai dengan struktur Delphi)
        $file = ($reportType == 1) ? 'rsales_manager_detail' : 'rsales_manager_summary';

        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

        // Store filter values in session
        session()->put('filter_cbg', $cbgCode);
        session()->put('report_type', $reportType);

        $data = [];

        if (!empty($cbgCode)) {
            if ($reportType == 1) {
                // Detail Report - repjuald data
                $results = $this->getSalesDetailReport($cbgCode);

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
                        'NAMA_TOKO' => $this->getNamaToko($cbgCode),
                        'REPORT_TYPE' => 'Laporan Detail Sales Manager',
                    ];
                }
            } else {
                // Summary Report - repjual data
                $results = $this->getSalesSummaryReport($cbgCode);

                foreach ($results as $row) {
                    $data[] = array_merge((array) $row, [
                        'NAMA_TOKO' => $this->getNamaToko($cbgCode),
                        'REPORT_TYPE' => 'Laporan Summary Sales Manager',
                    ]);
                }
            }
        }

        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I");
    }

    /**
     * API endpoint untuk mendukung AJAX calls dari frontend
     */
    public function apiGetSalesManagerData(Request $request)
    {
        try {
            $cbgCode = $request->cbg;
            $reportType = $request->report_type ?? 1;

            if (empty($cbgCode)) {
                return response()->json(['error' => 'Cabang harus dipilih'], 400);
            }

            $hasil = [];

            if ($reportType == 1) {
                $hasil = $this->getSalesDetailReport($cbgCode);
            } else {
                $hasil = $this->getSalesSummaryReport($cbgCode);
            }

            return response()->json([
                'success' => true,
                'data' => $hasil,
                'report_type' => $reportType,
                'nama_toko' => $this->getNamaToko($cbgCode)
            ]);
        } catch (\Exception $e) {
            Log::error('Error in apiGetSalesManagerData: ' . $e->getMessage());
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Method untuk export data ke Excel (sesuai dengan export functions di Delphi)
     */
    public function exportSalesManagerReport(Request $request)
    {
        try {
            $cbgCode = $request->cbg;
            $reportType = $request->report_type ?? 1;

            if (empty($cbgCode)) {
                return response()->json(['error' => 'Cabang harus dipilih'], 400);
            }

            $data = [];
            $filename = '';

            if ($reportType == 1) {
                $results = $this->getSalesDetailReport($cbgCode);
                $filename = 'sales_manager_detail_' . $cbgCode . '_' . date('Y-m-d_H-i-s') . '.xlsx';

                foreach ($results as $row) {
                    $data[] = [
                        'Cabang' => $row->CBG ?? '',
                        'Sub' => $row->SUB ?? '',
                        'Sub2' => $row->SUB2 ?? '',
                        'Kode Barang' => $row->KD_BRG ?? '',
                        'Nama Barang' => $row->NA_BRG ?? '',
                        'Barcode' => $row->BARCODE ?? '',
                        'Qty' => $row->qty ?? 0,
                        'Harga' => $row->harga ?? 0,
                        'Diskon' => $row->diskon ?? 0,
                        'Disc' => $row->disc ?? 0,
                        'PPN' => $row->ppn ?? '',
                        'NPPN' => $row->nppn ?? 0,
                        'DPP' => $row->dpp ?? 0,
                        'TKP' => $row->tkp ?? 0,
                        'Total' => $row->total ?? 0,
                        'Flag' => $row->flag ?? '',
                        'Type' => $row->type ?? '',
                        'Periode' => $row->per ?? '',
                        'Kodes' => $row->kodes ?? '',
                        'Tanggal' => $row->TGL ?? '',
                    ];
                }
            } else {
                $results = $this->getSalesSummaryReport($cbgCode);
                $filename = 'sales_manager_summary_' . $cbgCode . '_' . date('Y-m-d_H-i-s') . '.xlsx';

                foreach ($results as $row) {
                    $data[] = (array) $row;
                }
            }

            return response()->json([
                'success' => true,
                'data' => $data,
                'filename' => $filename
            ]);
        } catch (\Exception $e) {
            Log::error('Error in exportSalesManagerReport: ' . $e->getMessage());
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Method untuk mendapatkan summary statistik berdasarkan CBG
     */
    public function getSalesManagerSummary(Request $request)
    {
        try {
            $cbgCode = $request->cbg;
            $reportType = $request->report_type ?? 1;

            if (empty($cbgCode)) {
                return response()->json(['error' => 'Cabang harus dipilih'], 400);
            }

            $summary = [];

            if ($reportType == 1) {
                // Summary untuk detail report
                $query = "SELECT
                            COUNT(*) as total_records,
                            COUNT(DISTINCT KD_BRG) as total_items,
                            SUM(qty) as total_qty,
                            SUM(total) as total_amount,
                            AVG(total) as avg_amount
                          FROM repjuald
                          WHERE cbg = ?";

                $result = DB::select($query, [$cbgCode]);
                $summary = $result[0] ?? null;
            } else {
                // Summary untuk summary report
                $query = "SELECT
                            COUNT(*) as total_records,
                            SUM(CASE WHEN total IS NOT NULL THEN total ELSE 0 END) as total_amount
                          FROM repjual
                          WHERE cbg = ?";

                $result = DB::select($query, [$cbgCode]);
                $summary = $result[0] ?? null;
            }

            return response()->json([
                'success' => true,
                'summary' => $summary,
                'nama_toko' => $this->getNamaToko($cbgCode)
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getSalesManagerSummary: ' . $e->getMessage());
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Method untuk mendapatkan data detail berdasarkan kodes (sesuai dengan DblClick logic)
     */
    public function getDetailByKodes(Request $request)
    {
        try {
            $kodes = $request->kodes;
            $cbgCode = $request->cbg;

            if (empty($kodes) || empty($cbgCode)) {
                return response()->json(['error' => 'Parameter tidak lengkap'], 400);
            }

            // Query untuk mendapatkan detail berdasarkan kodes
            $query = "SELECT * FROM repjuald WHERE cbg = ? AND kodes = ? ORDER BY KD_BRG";
            $result = DB::select($query, [$cbgCode, $kodes]);

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getDetailByKodes: ' . $e->getMessage());
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Method untuk validasi data sebelum generate report
     */
    private function validateReportData($cbgCode, $reportType)
    {
        $errors = [];

        if (empty($cbgCode)) {
            $errors[] = 'Cabang harus dipilih';
        }

        if (!in_array($reportType, [1, 2])) {
            $errors[] = 'Tipe report tidak valid';
        }

        return $errors;
    }

    /**
     * Helper function untuk string operations (sesuai dengan LeftStr, RightStr di Delphi)
     */
    private function leftStr($string, $count)
    {
        return substr($string, 0, $count);
    }

    private function rightStr($string, $count)
    {
        if (strlen($string) < $count) {
            return $string;
        }
        return substr($string, strlen($string) - $count);
    }

    /**
     * Method untuk mendapatkan data berdasarkan filter tambahan
     */
    public function getFilteredSalesData(Request $request)
    {
        try {
            $cbgCode = $request->cbg;
            $reportType = $request->report_type ?? 1;
            $startDate = $request->start_date;
            $endDate = $request->end_date;
            $subCategory = $request->sub_category;

            if (empty($cbgCode)) {
                return response()->json(['error' => 'Cabang harus dipilih'], 400);
            }

            $query = '';
            $params = [$cbgCode];

            if ($reportType == 1) {
                $query = "SELECT * FROM repjuald WHERE cbg = ?";

                if (!empty($startDate) && !empty($endDate)) {
                    $query .= " AND TGL BETWEEN ? AND ?";
                    $params[] = $startDate;
                    $params[] = $endDate;
                }

                if (!empty($subCategory)) {
                    $query .= " AND SUB = ?";
                    $params[] = $subCategory;
                }

                $query .= " ORDER BY TGL, KD_BRG";
            } else {
                $query = "SELECT * FROM repjual WHERE cbg = ?";

                if (!empty($startDate) && !empty($endDate)) {
                    $query .= " AND TGL BETWEEN ? AND ?";
                    $params[] = $startDate;
                    $params[] = $endDate;
                }

                $query .= " ORDER BY TGL";
            }

            $result = DB::select($query, $params);

            return response()->json([
                'success' => true,
                'data' => $result,
                'nama_toko' => $this->getNamaToko($cbgCode)
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getFilteredSalesData: ' . $e->getMessage());
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}