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

class RBarangMacetKosongController extends Controller
{
    public function report()
    {
        $cbg = Cbg::groupBy('CBG')->get();

        // Initialize session variables
        session()->put('filter_cbg', '');
        session()->put('filter_jenis', '');

        // Get jenis report berdasarkan cabang default
        $jenisReport = [];
        if ($cbg->isNotEmpty()) {
            $jenisReport = $this->getJenisReport($cbg->first()->CBG);
        }

        return view('oreport_macetkosong.report')->with([
            'cbg' => $cbg,
            'jenisReport' => $jenisReport,
            'hasilMacet' => []
        ]);
    }

    public function getBarangMacetKosongReport(Request $request)
    {
        $cbg = Cbg::groupBy('CBG')->get();

        // Set filter values to session
        session()->put('filter_cbg', $request->cbg);
        session()->put('filter_jenis', $request->jenis);

        $hasilMacet = [];
        $jenisReport = [];

        if (!empty($request->cbg)) {
            // Get jenis report berdasarkan cabang yang dipilih
            $jenisReport = $this->getJenisReport($request->cbg);

            // Validate jenis tidak boleh kosong
            if (empty($request->jenis)) {
                return redirect()->back()->withErrors(['jenis' => 'Jenis Report Tidak Boleh Kosong']);
            }

            // Get data barang macet
            $hasilMacet = $this->getMacetData($request->cbg, $request->jenis);
        }

        return view('oreport_macetkosong.report')->with([
            'cbg' => $cbg,
            'jenisReport' => $jenisReport,
            'hasilMacet' => $hasilMacet
        ]);
    }

    /**
     * Get jenis report berdasarkan stored procedure
     * Equivalent dengan: com1.SQL.Text:='call tgz.pjl_brg_macet(:cbg,:jns);'
     */
    private function getJenisReport($cbg)
    {
        try {
            // Call stored procedure untuk mendapatkan jenis report
            $result = DB::select('CALL tgz.pjl_brg_macet(?, ?)', [$cbg, '']);

            $jenisReport = [];
            foreach ($result as $row) {
                if (isset($row->JNS) && !empty($row->JNS)) {
                    $jenisReport[] = $row->JNS;
                }
            }

            // Remove duplicates dan sort
            $jenisReport = array_unique($jenisReport);
            sort($jenisReport);

            return $jenisReport;
        } catch (\Exception $e) {
            Log::error('Error in getJenisReport: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get data barang macet berdasarkan jenis yang dipilih
     * Equivalent dengan logic dalam procedure Tampil
     */
    private function getMacetData($cbg, $jenis)
    {
        try {
            // Call stored procedure dengan parameter cbg dan jenis
            $result = DB::select('CALL tgz.pjl_brg_macet(?, ?)', [$cbg, trim($jenis)]);

            return $result;
        } catch (\Exception $e) {
            Log::error('Error in getMacetData: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Alternative method jika stored procedure tidak tersedia
     * Implementasi manual berdasarkan logic yang dikomentari di Delphi
     */
    private function getMacetDataManual($cbg, $jenis)
    {
        try {
            $currentYear = date('Y');
            $currentMonth = date('m');

            if ($jenis === 'Barang Macet') {
                // Query untuk Barang Macet
                // Barang yang sudah masuk bulan lalu tapi belum keluar bulan ini
                $query = "
                    SELECT A.KD_BRG, A.NA_BRG, B.SUPP, B.KET_UK, B.KET_KEM, B.TYPE,
                           A.KDLAKU, A.AK00, A.GAK00,
                           DATE(A.TGL_TRM) as TGL_TRM,
                           DATE(A.TGL_AT) as TGL_AT
                    FROM {$cbg}.brgdt A
                    LEFT JOIN {$cbg}.brg B ON B.KD_BRG = A.KD_BRG
                    WHERE MONTH(DATE(A.TGL_AT)) < MONTH(CURDATE())
                      AND YEAR(DATE(A.TGL_AT)) = YEAR(CURDATE())
                      AND A.TD_OD = ''
                      AND A.CAT_OD = ''
                      AND A.LPH > 0
                    ORDER BY B.TYPE ASC, B.KD_BRG ASC
                ";

                return DB::select($query);
            } elseif ($jenis === 'Barang Kosong') {
                // Query untuk Barang Kosong
                // Barang yang kosong di bulan ini
                $query = "
                    SELECT A.KD_BRG, A.NA_BRG, B.SUPP, B.KET_UK, B.KET_KEM, B.TYPE,
                           A.KDLAKU, A.AK00, A.GAK00,
                           DATE(A.TGL_TRM) as TGL_TRM,
                           DATE(A.TGL_BK) as TGL_BK
                    FROM {$cbg}.brgdt A
                    LEFT JOIN {$cbg}.brg B ON B.KD_BRG = A.KD_BRG
                    WHERE MONTH(DATE(A.TGL_BK)) = MONTH(CURDATE())
                      AND YEAR(DATE(A.TGL_BK)) = YEAR(CURDATE())
                      AND A.TD_OD = ''
                      AND A.CAT_OD = ''
                      AND A.LPH > 0
                    ORDER BY B.TYPE ASC, B.KD_BRG ASC
                ";

                return DB::select($query);
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Error in getMacetDataManual: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Generate Jasper Report
     * Equivalent dengan frxReport1.ShowReport();
     */
    public function jasperBarangMacetKosongReport(Request $request)
    {   
        $file = 'barangmacet';
        if ($request->jenis == 'Barang Macet > 31 Hari (KK)' || $request->jenis == 'Barang Macet > 31 Hari (Non KK)') {
            $file = 'barangmacet_31';
        } else if ($request->jenis == 'Barang Macet > 150 Hari (KK)' || $request->jenis == 'Barang Macet > 150 Hari (Non KK)'){

            $file = 'barangmacet_150';
        }
        
        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));
        $params = [
			"TGL_CTK" => date('d/m/Y'),
            "JAM"     => date('H:i:s'),
		];
		$PHPJasperXML->arrayParameter=$params;

        // Set session values
        session()->put('filter_cbg', $request->cbg);
        session()->put('filter_jenis', $request->jenis);

        $data = [];

        if (!empty($request->cbg) && !empty($request->jenis)) {
            $hasilMacet = $this->getMacetData($request->cbg, $request->jenis);

            foreach ($hasilMacet as $key => $value) {
                $data[] = [
                    'BKT' => $hasilMacet[$key]->BKT,
                    'SUB' => $hasilMacet[$key]->SUB,
                    'KDBAR' => $hasilMacet[$key]->KDBAR,
                    'KET_UK' => $hasilMacet[$key]->KET_UK,
                    'KET_KEM' => $hasilMacet[$key]->KET_KEM,
                    'HJ' => $hasilMacet[$key]->HJ,
                    'DTR' => $hasilMacet[$key]->DTR,
                    'LPH_LL' => $hasilMacet[$key]->LPH_LL,
                    'LPH' => $hasilMacet[$key]->LPH,
                    'AK00' => $hasilMacet[$key]->AK00,
                    'HARI' => $hasilMacet[$key]->HARI,
                    'TGL_TRM' => $hasilMacet[$key]->TGL_TRM,
                    'QTY_TRM' => $hasilMacet[$key]->QTY_TRM,
                    'RHPS' => $hasilMacet[$key]->RHPS,
                    'KD_BRG' => $hasilMacet[$key]->KD_BRG,
                    'NA_BRG' => $hasilMacet[$key]->NA_BRG,
                    'SUPP' => $hasilMacet[$key]->SUPP,
                    'NAMAS' => $hasilMacet[$key]->NAMAS,
                ];
            }
        }

        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I");
    }

    /**
     * Export to Excel
     * Equivalent dengan ExportGridToXLSX di Delphi
     */
    public function exportToExcel(Request $request)
    {
        try {
            // Validate required parameters
            if (empty($request->cbg) || empty($request->jenis)) {
                return redirect()->back()->withErrors(['export' => 'Cabang dan Jenis Report harus dipilih untuk export']);
            }

            $hasilMacet = $this->getMacetData($request->cbg, $request->jenis);

            if (empty($hasilMacet)) {
                return redirect()->back()->withErrors(['export' => 'Tidak ada data untuk di-export']);
            }

            // Set headers untuk download Excel
            $filename = 'Barang_Macet_' . $request->cbg . '_' . str_replace(' ', '_', $request->jenis) . '_' . date('Y-m-d') . '.xlsx';

            return response()->streamDownload(function () use ($hasilMacet) {
                $handle = fopen('php://output', 'w');

                // Write CSV headers
                $headers = [
                    'Kode Barang',
                    'Nama Barang',
                    'Supplier',
                    'Keterangan Ukuran',
                    'Keterangan Kemasan',
                    'Type',
                    'Kode Laku',
                    'AK00',
                    'GAK00',
                    'Tanggal Terima',
                    'Tanggal AT',
                    'Tanggal BK'
                ];
                fputcsv($handle, $headers);

                // Write data
                foreach ($hasilMacet as $row) {
                    fputcsv($handle, [
                        $row->KD_BRG ?? '',
                        $row->NA_BRG ?? '',
                        $row->SUPP ?? '',
                        $row->KET_UK ?? '',
                        $row->KET_KEM ?? '',
                        $row->TYPE ?? '',
                        $row->KDLAKU ?? '',
                        $row->AK00 ?? 0,
                        $row->GAK00 ?? 0,
                        $row->TGL_TRM ?? '',
                        $row->TGL_AT ?? '',
                        $row->TGL_BK ?? ''
                    ]);
                }

                fclose($handle);
            }, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in exportToExcel: ' . $e->getMessage());
            return redirect()->back()->withErrors(['export' => 'Terjadi kesalahan saat export data']);
        }
    }

    /**
     * Get Jenis Report via AJAX
     * Untuk update dropdown jenis saat cabang berubah
     */
    public function getJenisReportAjax(Request $request)
    {
        try {
            $cbg = $request->cbg;

            if (empty($cbg)) {
                return response()->json(['success' => false, 'message' => 'Cabang tidak boleh kosong']);
            }

            $jenisReport = $this->getJenisReport($cbg);

            return response()->json([
                'success' => true,
                'data' => $jenisReport
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getJenisReportAjax: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan sistem']);
        }
    }

    /**
     * Helper method untuk validasi cabang
     */
    private function validateCabang($cbg)
    {
        $cabangExists = Cbg::where('CBG', $cbg)->exists();
        if (!$cabangExists) {
            return ['valid' => false, 'message' => 'Cabang tidak ditemukan'];
        }

        return ['valid' => true, 'message' => ''];
    }

    /**
     * Helper method untuk format tanggal jika diperlukan
     */
    private function formatTanggal($tanggal)
    {
        if (empty($tanggal)) {
            return '';
        }

        try {
            return date('d/m/Y', strtotime($tanggal));
        } catch (\Exception $e) {
            return $tanggal;
        }
    }
}
