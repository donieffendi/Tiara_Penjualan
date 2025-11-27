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

class RCetakUlangCashbackController extends Controller
{
    public function report()
    {
        $cbg = Cbg::groupBy('CBG')->get();

        // Initialize session variables
        session()->put('filter_cbg', '');
        session()->put('report_type', 1); // 1=Duplicate Barcode, 2=Different Barcode

        return view('oreport_cashback.report')->with([
            'cbg' => $cbg,
            'hasilDuplicate' => [],
            'hasilDifferent' => [],
        ]);
    }

    public function getBarcodeReport(Request $request)
    {
        $cbg = Cbg::groupBy('CBG')->get();

        // Get filter values
        $cbgCode = $request->cbg;
        $reportType = $request->report_type ?? 1; // 1=Duplicate Barcode, 2=Different Barcode

        // Store in session
        session()->put('filter_cbg', $cbgCode);
        session()->put('report_type', $reportType);

        $hasilDuplicate = [];
        $hasilDifferent = [];

        if (!empty($cbgCode)) {
            if ($reportType == 1) {
                // Duplicate Barcode Report
                $hasilDuplicate = $this->getDuplicateBarcodeData($cbgCode);
            } else {
                // Different Barcode Report
                $hasilDifferent = $this->getDifferentBarcodeData($cbgCode);
            }
        }

        return view('oreport_cashback.report')->with([
            'cbg' => $cbg,
            'hasilDuplicate' => $hasilDuplicate,
            'hasilDifferent' => $hasilDifferent,
            'reportType' => $reportType,
        ]);
    }

    /**
     * Sesuai dengan query pertama di procedure tampil Delphi
     * Mencari barcode yang duplikat di table masks
     */
    private function getDuplicateBarcodeData($cbgCode)
    {
        try {
            // Query sesuai dengan logika Delphi:
            // select kd_brg,na_brg,KET_UK,KET_KEM,BARCODE,JNS from masks
            // where barcode in(select barcode from (select barcode,count(barcode) as ini from masks GROUP BY barcode)as ole where ini>1)
            // ORDER BY BARCODE

            $query = "SELECT
                        m.kd_brg,
                        m.na_brg,
                        m.KET_UK,
                        m.KET_KEM,
                        m.BARCODE,
                        m.JNS
                      FROM {$cbgCode}.masks m
                      WHERE m.barcode IN (
                          SELECT barcode
                          FROM (
                              SELECT barcode, COUNT(barcode) as ini
                              FROM {$cbgCode}.masks
                              GROUP BY barcode
                          ) as ole
                          WHERE ole.ini > 1
                      )
                      ORDER BY m.BARCODE";

            return DB::select($query);
        } catch (\Exception $e) {
            Log::error('Error in getDuplicateBarcodeData: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Sesuai dengan query kedua di procedure tampil Delphi
     * Mencari perbedaan barcode antara master (brg) dan kasir (masks)
     */
    private function getDifferentBarcodeData($cbgCode)
    {
        try {
            // Query sesuai dengan logika Delphi:
            // select brg.KD_BRG,brg.NA_BRG,brg.KET_UK,brg.KET_KEM,brg.BARCODE as barcodemaster,masks.BARCODE as barcodekasir
            // from brg,masks where brg.KD_BRG=masks.kd_brg and brg.BARCODE<>masks.BARCODE

            $query = "SELECT
                        brg.KD_BRG,
                        brg.NA_BRG,
                        brg.KET_UK,
                        brg.KET_KEM,
                        brg.BARCODE as barcodemaster,
                        masks.BARCODE as barcodekasir
                      FROM {$cbgCode}.brg, {$cbgCode}.masks
                      WHERE brg.KD_BRG = masks.kd_brg
                      AND brg.BARCODE <> masks.BARCODE";

            return DB::select($query);
        } catch (\Exception $e) {
            Log::error('Error in getDifferentBarcodeData: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Method untuk mendapatkan nama toko
     */
    private function getNamaToko($cbgCode)
    {
        try {
            $result = DB::select("SELECT NA_TOKO from {$cbgCode}.toko WHERE KODE = ?", [$cbgCode]);
            return $result[0]->NA_TOKO ?? '';
        } catch (\Exception $e) {
            Log::error('Error in getNamaToko: ' . $e->getMessage());
            return '';
        }
    }

    public function jasperBarcodeReport(Request $request)
    {
        $reportType = $request->report_type ?? 1;
        $cbgCode = $request->cbg;

        // Tentukan file report berdasarkan tipe
        $file = ($reportType == 1) ? 'rbarcode_duplicate' : 'rbarcode_different';

        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

        // Store filter values in session
        session()->put('filter_cbg', $cbgCode);
        session()->put('report_type', $reportType);

        $data = [];

        if (!empty($cbgCode)) {
            if ($reportType == 1) {
                // Duplicate Barcode Report
                $results = $this->getDuplicateBarcodeData($cbgCode);

                foreach ($results as $row) {
                    $data[] = [
                        'CBG' => $cbgCode,
                        'KD_BRG' => $row->kd_brg ?? '',
                        'NA_BRG' => $row->na_brg ?? '',
                        'KET_UK' => $row->KET_UK ?? '',
                        'KET_KEM' => $row->KET_KEM ?? '',
                        'BARCODE' => $row->BARCODE ?? '',
                        'JNS' => $row->JNS ?? '',
                        'NAMA_TOKO' => $this->getNamaToko($cbgCode),
                        'REPORT_TYPE' => 'Laporan Barcode Duplikat',
                    ];
                }
            } else {
                // Different Barcode Report
                $results = $this->getDifferentBarcodeData($cbgCode);

                foreach ($results as $row) {
                    $data[] = [
                        'CBG' => $cbgCode,
                        'KD_BRG' => $row->KD_BRG ?? '',
                        'NA_BRG' => $row->NA_BRG ?? '',
                        'KET_UK' => $row->KET_UK ?? '',
                        'KET_KEM' => $row->KET_KEM ?? '',
                        'BARCODE_MASTER' => $row->barcodemaster ?? '',
                        'BARCODE_KASIR' => $row->barcodekasir ?? '',
                        'NAMA_TOKO' => $this->getNamaToko($cbgCode),
                        'REPORT_TYPE' => 'Laporan Perbedaan Barcode Master vs Kasir',
                    ];
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
    public function apiGetBarcodeData(Request $request)
    {
        try {
            $cbgCode = $request->cbg;
            $reportType = $request->report_type ?? 1;

            if (empty($cbgCode)) {
                return response()->json(['error' => 'Cabang harus dipilih'], 400);
            }

            $hasil = [];

            if ($reportType == 1) {
                $hasil = $this->getDuplicateBarcodeData($cbgCode);
            } else {
                $hasil = $this->getDifferentBarcodeData($cbgCode);
            }

            return response()->json([
                'success' => true,
                'data' => $hasil,
                'report_type' => $reportType,
                'nama_toko' => $this->getNamaToko($cbgCode)
            ]);
        } catch (\Exception $e) {
            Log::error('Error in apiGetBarcodeData: ' . $e->getMessage());
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Method untuk mendapatkan detail barcode tertentu
     */
    public function apiGetBarcodeDetail(Request $request)
    {
        try {
            $cbgCode = $request->cbg;
            $barcode = $request->barcode;
            $reportType = $request->report_type ?? 1;

            if (empty($cbgCode) || empty($barcode)) {
                return response()->json(['error' => 'Cabang dan Barcode harus diisi'], 400);
            }

            $detail = [];

            if ($reportType == 1) {
                // Detail untuk duplicate barcode
                $query = "SELECT
                            kd_brg,
                            na_brg,
                            KET_UK,
                            KET_KEM,
                            BARCODE,
                            JNS,
                            CREATED_AT,
                            UPDATED_AT
                          FROM {$cbgCode}.masks
                          WHERE BARCODE = ?
                          ORDER BY kd_brg";

                $detail = DB::select($query, [$barcode]);
            } else {
                // Detail untuk different barcode
                $query = "SELECT
                            brg.KD_BRG,
                            brg.NA_BRG,
                            brg.KET_UK,
                            brg.KET_KEM,
                            brg.BARCODE as barcodemaster,
                            masks.BARCODE as barcodekasir,
                            brg.CREATED_AT as brg_created,
                            masks.CREATED_AT as masks_created
                          FROM {$cbgCode}.brg, {$cbgCode}.masks
                          WHERE brg.KD_BRG = masks.kd_brg
                          AND (brg.BARCODE = ? OR masks.BARCODE = ?)";

                $detail = DB::select($query, [$barcode, $barcode]);
            }

            return response()->json([
                'success' => true,
                'data' => $detail
            ]);
        } catch (\Exception $e) {
            Log::error('Error in apiGetBarcodeDetail: ' . $e->getMessage());
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Method untuk export data ke Excel (sesuai dengan Button1Click dan Button2Click di Delphi)
     */
    public function exportBarcodeReport(Request $request)
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
                $results = $this->getDuplicateBarcodeData($cbgCode);
                $filename = 'barcode_duplicate_' . $cbgCode . '_' . date('Y-m-d_H-i-s') . '.xlsx';

                foreach ($results as $row) {
                    $data[] = [
                        'Kode Barang' => $row->kd_brg ?? '',
                        'Nama Barang' => $row->na_brg ?? '',
                        'Ukuran' => $row->KET_UK ?? '',
                        'Kemasan' => $row->KET_KEM ?? '',
                        'Barcode' => $row->BARCODE ?? '',
                        'Jenis' => $row->JNS ?? '',
                    ];
                }
            } else {
                $results = $this->getDifferentBarcodeData($cbgCode);
                $filename = 'barcode_different_' . $cbgCode . '_' . date('Y-m-d_H-i-s') . '.xlsx';

                foreach ($results as $row) {
                    $data[] = [
                        'Kode Barang' => $row->KD_BRG ?? '',
                        'Nama Barang' => $row->NA_BRG ?? '',
                        'Ukuran' => $row->KET_UK ?? '',
                        'Kemasan' => $row->KET_KEM ?? '',
                        'Barcode Master' => $row->barcodemaster ?? '',
                        'Barcode Kasir' => $row->barcodekasir ?? '',
                    ];
                }
            }

            // Menggunakan Laravel Excel atau library lain untuk export
            // Implementasi tergantung pada library yang digunakan
            return response()->json([
                'success' => true,
                'data' => $data,
                'filename' => $filename
            ]);
        } catch (\Exception $e) {
            Log::error('Error in exportBarcodeReport: ' . $e->getMessage());
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Method untuk mendapatkan summary/statistik data
     */
    public function getSummaryBarcode($cbgCode, $reportType)
    {
        try {
            if ($reportType == 1) {
                // Summary untuk duplicate barcode
                $query = "SELECT
                            COUNT(DISTINCT barcode) as total_barcode_duplicate,
                            COUNT(*) as total_item_duplicate
                          FROM {$cbgCode}.masks m
                          WHERE m.barcode IN (
                              SELECT barcode
                              FROM (
                                  SELECT barcode, COUNT(barcode) as ini
                                  FROM {$cbgCode}.masks
                                  GROUP BY barcode
                              ) as ole
                              WHERE ole.ini > 1
                          )";
            } else {
                // Summary untuk different barcode
                $query = "SELECT
                            COUNT(*) as total_different_barcode
                          FROM {$cbgCode}.brg, {$cbgCode}.masks
                          WHERE brg.KD_BRG = masks.kd_brg
                          AND brg.BARCODE <> masks.BARCODE";
            }

            $result = DB::select($query);
            return $result[0] ?? null;
        } catch (\Exception $e) {
            Log::error('Error in getSummaryBarcode: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * API endpoint untuk mendapatkan summary data
     */
    public function apiGetBarcodeSummary(Request $request)
    {
        try {
            $cbgCode = $request->cbg;
            $reportType = $request->report_type ?? 1;

            if (empty($cbgCode)) {
                return response()->json(['error' => 'Cabang harus dipilih'], 400);
            }

            $summary = $this->getSummaryBarcode($cbgCode, $reportType);

            return response()->json([
                'success' => true,
                'summary' => $summary,
                'nama_toko' => $this->getNamaToko($cbgCode)
            ]);
        } catch (\Exception $e) {
            Log::error('Error in apiGetBarcodeSummary: ' . $e->getMessage());
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}