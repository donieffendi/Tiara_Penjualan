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

class RSalesPenjualanSPMController extends Controller
{
    public function report()
    {
        // Get CBG data using query instead of model
        // $cbg = $this->getCbgList();
        $cbg = Cbg::groupBy('CBG')->get();

        // Get periods using query instead of model
        // $periods = $this->getPeriodsList();
        $periods = Perid::query()->get();

        // Get kasir list
        $kasirList = $this->getKasirList();

        // Initialize session variables
        session()->put('filter_cbg', '');
        session()->put('filter_kasir', '');
        session()->put('filter_user', '');
        session()->put('filter_periode', '');
        session()->put('filter_tanggal', '');
        session()->put('report_type', 1); // 1=Laporan per Kasir, 2=Laporan per User

        return view('oreport_sales_penjualan_spm.report')->with([
            'cbg' => $cbg,
            'periods' => $periods,
            'kasirList' => $kasirList,
            'hasilPerKasir' => [],
            'hasilPerUser' => [],
        ]);
    }

    public function getSalesPenjualanSPMReport(Request $request)
    {
        $cbg = $this->getCbgList();
        $periods = $this->getPeriodsList();
        $kasirList = $this->getKasirList();

        // Get filter values
        $cbgCode = $request->cbg;
        $kasir = $request->kasir;
        $user = $request->user;
        $periode = $request->periode;
        $tanggal = $request->tanggal;
        $reportType = $request->report_type ?? 1; // 1=Per Kasir, 2=Per User
//dd($request->all());

        // Store in session
        session()->put('filter_cbg', $cbgCode);
        session()->put('filter_kasir', $kasir);
        session()->put('filter_user', $user);
        session()->put('filter_periode', $periode);
        session()->put('filter_tanggal', $tanggal);
        session()->put('report_type', $reportType);

        $hasilPerKasir = [];
        $hasilPerUser = [];

        if (!empty($cbgCode)) {
            if ($reportType == 1) {
                // Laporan per Kasir (Button1Click logic)
                if (!empty($kasir) && !empty($tanggal)) {
                    $hasilPerKasir = $this->getSalesPenjualanPerKasir($cbgCode, $kasir, $tanggal, $periode);
					
                }
            } else {
                // Laporan per User (Button2Click logic)
                if (!empty($user) && !empty($tanggal) && !empty($periode)) {
                    $hasilPerUser = $this->getSalesPenjualanPerUser($cbgCode, $user, $tanggal, $periode);
                }
            }
        }
        
        $cbgx = Cbg::groupBy('CBG')->get();
        $periodsx = Perid::query()->get();
		
        return view('oreport_sales_penjualan_spm.report')->with([
            'cbg' => $cbgx,
            'periods' => $periodsx,
            'kasirList' => $kasirList,
            'hasilPerKasir' => $hasilPerKasir,
            'hasilPerUser' => $hasilPerUser,
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
     * Mengambil data kasir dari table noks (sesuai dengan FormShow di Delphi)
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
     * Get user list from database
     */
    private function getUserList()
    {
        try {
            $query = "SELECT DISTINCT usrnm FROM jual01 WHERE usrnm IS NOT NULL AND usrnm <> ''
                      UNION
                      SELECT DISTINCT usrnm FROM jual02 WHERE usrnm IS NOT NULL AND usrnm <> ''
                      UNION
                      SELECT DISTINCT usrnm FROM jual03 WHERE usrnm IS NOT NULL AND usrnm <> ''
                      UNION
                      SELECT DISTINCT usrnm FROM jual04 WHERE usrnm IS NOT NULL AND usrnm <> ''
                      UNION
                      SELECT DISTINCT usrnm FROM jual05 WHERE usrnm IS NOT NULL AND usrnm <> ''
                      UNION
                      SELECT DISTINCT usrnm FROM jual06 WHERE usrnm IS NOT NULL AND usrnm <> ''
                      UNION
                      SELECT DISTINCT usrnm FROM jual07 WHERE usrnm IS NOT NULL AND usrnm <> ''
                      UNION
                      SELECT DISTINCT usrnm FROM jual08 WHERE usrnm IS NOT NULL AND usrnm <> ''
                      UNION
                      SELECT DISTINCT usrnm FROM jual09 WHERE usrnm IS NOT NULL AND usrnm <> ''
                      UNION
                      SELECT DISTINCT usrnm FROM jual10 WHERE usrnm IS NOT NULL AND usrnm <> ''
                      UNION
                      SELECT DISTINCT usrnm FROM jual11 WHERE usrnm IS NOT NULL AND usrnm <> ''
                      UNION
                      SELECT DISTINCT usrnm FROM jual12 WHERE usrnm IS NOT NULL AND usrnm <> ''
                      ORDER BY usrnm";
            return DB::select($query);
        } catch (\Exception $e) {
            Log::error('Error in getUserList: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Sesuai dengan Button1Click di Delphi - Laporan Penjualan per Kasir
     * Mengambil data penjualan berdasarkan kasir dan tanggal
     */
    private function getSalesPenjualanPerKasir($cbgCode, $kasir, $tanggal, $periode = null)
    {
        try {
            // Tentukan periode MM berdasarkan input periode atau dari tanggal
            $MM = $this->determinePeriode($periode, $tanggal);

            // Query sesuai dengan logika Delphi Button1Click:
            $query = "SELECT
                        jual{$MM}.cbg,
                        jual{$MM}.KSR,
                        jual{$MM}.SHIFT,
                        jual{$MM}.tgl,
                        LEFT(TRIM(juald{$MM}.KD_BRG),3) AS SUB,
                        juald{$MM}.KD_BRG,
                        juald{$MM}.NA_BRG,
                        SUM(juald{$MM}.qty) as qty,
                        juald{$MM}.harga,
                        juald{$MM}.ppn,
                        SUM(juald{$MM}.nppn) as nppn,
                        SUM(juald{$MM}.dpp) as dpp,
                        SUM(juald{$MM}.tkp) as tkp,
                        SUM(juald{$MM}.total) as total,
                        CASE
                            WHEN juald{$MM}.ppn='1' THEN 'PPN Barang Produksi'
                            WHEN juald{$MM}.ppn='2' THEN 'PPN Barang Cukai'
                            WHEN juald{$MM}.ppn='3' THEN 'PPN Barang Import'
                            ELSE 'Tanpa PPN'
                        END AS PPN_KET
                      FROM {$cbgCode}.jual{$MM}, {$cbgCode}.juald{$MM}
                      WHERE
                        jual{$MM}.no_bukti = juald{$MM}.no_bukti
                        AND juald{$MM}.KD_BRG <> ''
                        AND jual{$MM}.flag = 'JL'
                        AND jual{$MM}.ksr = ?
                        AND jual{$MM}.tgl = ?
                      GROUP BY
                        jual{$MM}.CBG,
                        jual{$MM}.ksr,
                        jual{$MM}.SHIFT,
                        jual{$MM}.tgl,
                        juald{$MM}.KD_BRG,
                        juald{$MM}.ppn
                      ORDER BY
                        jual{$MM}.CBG,
                        jual{$MM}.KSR,
                        jual{$MM}.SHIFT,
                        juald{$MM}.PPN,
                        juald{$MM}.KD_BRG";

            $formattedDate = Carbon::parse($tanggal)->format('Y/m/d');
            return DB::select($query, [$kasir, $formattedDate]);
        } catch (\Exception $e) {
            Log::error('Error in getSalesPenjualanPerKasir: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Sesuai dengan Button2Click di Delphi - Laporan Penjualan per User
     * Mengambil data penjualan berdasarkan user dan tanggal
     */
    private function getSalesPenjualanPerUser($cbgCode, $user, $tanggal, $periode)
    {
        try {
            // Validasi periode wajib diisi (sesuai dengan validasi di Delphi)
            if (empty($periode)) {
                throw new \Exception('Periode Kosong. Wajib diisi!.');
            }

            // Tentukan periode MM
            $MM = $this->determinePeriode($periode, $tanggal);

            // Query sesuai dengan logika Delphi Button2Click:
            $query = "SELECT
                        jual{$MM}.cbg,
                        jual{$MM}.usrnm,
                        jual{$MM}.SHIFT,
                        jual{$MM}.tgl,
                        LEFT(TRIM(juald{$MM}.KD_BRG),3) AS SUB,
                        juald{$MM}.KD_BRG,
                        juald{$MM}.NA_BRG,
                        SUM(juald{$MM}.qty) as qty,
                        juald{$MM}.harga,
                        juald{$MM}.ppn,
                        SUM(juald{$MM}.nppn) as nppn,
                        SUM(juald{$MM}.dpp) as dpp,
                        SUM(juald{$MM}.tkp) as tkp,
                        SUM(juald{$MM}.total) as total,
                        CASE
                            WHEN juald{$MM}.ppn='1' THEN 'PPN Barang Produksi'
                            WHEN juald{$MM}.ppn='2' THEN 'PPN Barang Cukai'
                            WHEN juald{$MM}.ppn='3' THEN 'PPN Barang Import'
                            ELSE 'Tanpa PPN'
                        END AS PPN_KET
                      FROM {$cbgCode}.jual{$MM}, {$cbgCode}.juald{$MM}
                      WHERE
                        jual{$MM}.no_bukti = juald{$MM}.no_bukti
                        AND juald{$MM}.KD_BRG <> ''
                        AND jual{$MM}.flag = 'JL'
                        AND jual{$MM}.usrnm = ?
                        AND jual{$MM}.tgl = ?
                      GROUP BY
                        jual{$MM}.CBG,
                        jual{$MM}.usrnm,
                        jual{$MM}.SHIFT,
                        jual{$MM}.tgl,
                        juald{$MM}.KD_BRG,
                        juald{$MM}.ppn
                      ORDER BY
                        jual{$MM}.CBG,
                        jual{$MM}.usrnm,
                        jual{$MM}.SHIFT,
                        juald{$MM}.PPN,
                        juald{$MM}.KD_BRG";

            $formattedDate = Carbon::parse($tanggal)->format('Y/m/d');
            return DB::select($query, [$user, $formattedDate]);
        } catch (\Exception $e) {
            Log::error('Error in getSalesPenjualanPerUser: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Helper function untuk menentukan periode MM
     * Sesuai dengan logika txtperPropertiesChange di Delphi
     */
    private function determinePeriode($periode, $tanggal)
    {
        if (!empty($periode)) {
            // Ambil 2 karakter pertama dari periode (leftstr logic dari Delphi)
            return substr(trim($periode), 0, 2);
        } else if (!empty($tanggal)) {
            // Jika tidak ada periode, ambil dari bulan tanggal
            return Carbon::parse($tanggal)->format('m');
        }

        // Default ke bulan sekarang
        return Carbon::now()->format('m');
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

    public function jasperSalesPenjualanSPMReport(Request $request)
    {
        $reportType = $request->report_type ?? 1;
        $cbgCode = $request->cbg;
        $kasir = $request->kasir;
        $user = $request->user;
        $periode = $request->periode;
        $tanggal = $request->tanggal;

        // Tentukan file report berdasarkan tipe
        $file = ($reportType == 1) ? 'rsales_penjualan_kasir' : 'rsales_penjualan_user';

        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

        // Store filter values in session
        session()->put('filter_cbg', $cbgCode);
        session()->put('filter_kasir', $kasir);
        session()->put('filter_user', $user);
        session()->put('filter_periode', $periode);
        session()->put('filter_tanggal', $tanggal);
        session()->put('report_type', $reportType);

        $data = [];

        if (!empty($cbgCode) && !empty($tanggal)) {
            if ($reportType == 1 && !empty($kasir)) {
                // Laporan per Kasir
                $results = $this->getSalesPenjualanPerKasir($cbgCode, $kasir, $tanggal, $periode);

                foreach ($results as $row) {
                    $data[] = [
                        'CBG' => $cbgCode,
                        'KSR' => $row->KSR ?? '',
                        'SHIFT' => ($row->SHIFT ?? '') == 'P' ? 'Pagi' : 'Sore',
                        'TGL' => $row->tgl ?? '',
                        'SUB' => $row->SUB ?? '',
                        'KD_BRG' => $row->KD_BRG ?? '',
                        'NA_BRG' => $row->NA_BRG ?? '',
                        'QTY' => $row->qty ?? 0,
                        'HARGA' => $row->harga ?? 0,
                        'PPN' => $row->ppn ?? '',
                        'NPPN' => $row->nppn ?? 0,
                        'DPP' => $row->dpp ?? 0,
                        'TKP' => $row->tkp ?? 0,
                        'TOTAL' => $row->total ?? 0,
                        'PPN_KET' => $row->PPN_KET ?? '',
                        'NAMA_TOKO' => $this->getNamaToko($cbgCode),
                        'REPORT_TYPE' => 'Laporan Penjualan per Kasir',
                        'FILTER_KASIR' => $kasir,
                        'FILTER_TANGGAL' => $tanggal,
                        'DATE' => date('d/m/Y')
                    ];
                }
            } else if ($reportType == 2 && !empty($user) && !empty($periode)) {
                // Laporan per User
                $results = $this->getSalesPenjualanPerUser($cbgCode, $user, $tanggal, $periode);

                foreach ($results as $row) {
                    $data[] = [
                        'CBG' => $cbgCode,
                        'USRNM' => $row->usrnm ?? '',
                        'SHIFT' => $row->SHIFT ?? '',
                        'TGL' => $row->tgl ?? '',
                        'SUB' => $row->SUB ?? '',
                        'KD_BRG' => $row->KD_BRG ?? '',
                        'NA_BRG' => $row->NA_BRG ?? '',
                        'QTY' => $row->qty ?? 0,
                        'HARGA' => $row->harga ?? 0,
                        'PPN' => $row->ppn ?? '',
                        'NPPN' => $row->nppn ?? 0,
                        'DPP' => $row->dpp ?? 0,
                        'TKP' => $row->tkp ?? 0,
                        'TOTAL' => $row->total ?? 0,
                        'PPN_KET' => $row->PPN_KET ?? '',
                        'NAMA_TOKO' => $this->getNamaToko($cbgCode),
                        'REPORT_TYPE' => 'Laporan Penjualan per User',
                        'FILTER_USER' => $user,
                        'FILTER_PERIODE' => $periode,
                        'FILTER_TANGGAL' => $tanggal,
                        'DATE' => date('d/m/Y')
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
    public function apiGetSalesPenjualanData(Request $request)
    {
        try {
            $cbgCode = $request->cbg;
            $kasir = $request->kasir;
            $user = $request->user;
            $periode = $request->periode;
            $tanggal = $request->tanggal;
            $reportType = $request->report_type ?? 1;

            if (empty($cbgCode)) {
                return response()->json(['error' => 'Cabang harus dipilih'], 400);
            }

            if (empty($tanggal)) {
                return response()->json(['error' => 'Tanggal harus dipilih'], 400);
            }

            $hasil = [];

            if ($reportType == 1) {
                if (empty($kasir)) {
                    return response()->json(['error' => 'Kasir harus dipilih'], 400);
                }
                $hasil = $this->getSalesPenjualanPerKasir($cbgCode, $kasir, $tanggal, $periode);
            } else {
                if (empty($user)) {
                    return response()->json(['error' => 'User harus dipilih'], 400);
                }
                if (empty($periode)) {
                    return response()->json(['error' => 'Periode harus dipilih'], 400);
                }
                $hasil = $this->getSalesPenjualanPerUser($cbgCode, $user, $tanggal, $periode);
            }

            return response()->json([
                'success' => true,
                'data' => $hasil,
                'report_type' => $reportType,
                'nama_toko' => $this->getNamaToko($cbgCode)
            ]);
        } catch (\Exception $e) {
            Log::error('Error in apiGetSalesPenjualanData: ' . $e->getMessage());
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Method untuk export data ke Excel (sesuai dengan export functions di Delphi)
     */
    public function exportSalesPenjualanReport(Request $request)
    {
        try {
            $cbgCode = $request->cbg;
            $kasir = $request->kasir;
            $user = $request->user;
            $periode = $request->periode;
            $tanggal = $request->tanggal;
            $reportType = $request->report_type ?? 1;

            if (empty($cbgCode) || empty($tanggal)) {
                return response()->json(['error' => 'Cabang dan tanggal harus dipilih'], 400);
            }

            $data = [];
            $filename = '';

            if ($reportType == 1 && !empty($kasir)) {
                $results = $this->getSalesPenjualanPerKasir($cbgCode, $kasir, $tanggal, $periode);
                $filename = 'sales_penjualan_kasir_' . $cbgCode . '_' . $kasir . '_' . date('Y-m-d_H-i-s') . '.xlsx';

                foreach ($results as $row) {
                    $data[] = [
                        'Cabang' => $row->cbg ?? '',
                        'Kasir' => $row->KSR ?? '',
                        'Shift' => $row->SHIFT ?? '',
                        'Tanggal' => $row->tgl ?? '',
                        'Sub' => $row->SUB ?? '',
                        'Kode Barang' => $row->KD_BRG ?? '',
                        'Nama Barang' => $row->NA_BRG ?? '',
                        'Qty' => $row->qty ?? 0,
                        'Harga' => $row->harga ?? 0,
                        'PPN' => $row->ppn ?? '',
                        'NPPN' => $row->nppn ?? 0,
                        'DPP' => $row->dpp ?? 0,
                        'TKP' => $row->tkp ?? 0,
                        'Total' => $row->total ?? 0,
                        'Keterangan PPN' => $row->PPN_KET ?? '',
                    ];
                }
            } else if ($reportType == 2 && !empty($user) && !empty($periode)) {
                $results = $this->getSalesPenjualanPerUser($cbgCode, $user, $tanggal, $periode);
                $filename = 'sales_penjualan_user_' . $cbgCode . '_' . $user . '_' . date('Y-m-d_H-i-s') . '.xlsx';

                foreach ($results as $row) {
                    $data[] = [
                        'Cabang' => $row->cbg ?? '',
                        'User' => $row->usrnm ?? '',
                        'Shift' => $row->SHIFT ?? '',
                        'Tanggal' => $row->tgl ?? '',
                        'Sub' => $row->SUB ?? '',
                        'Kode Barang' => $row->KD_BRG ?? '',
                        'Nama Barang' => $row->NA_BRG ?? '',
                        'Qty' => $row->qty ?? 0,
                        'Harga' => $row->harga ?? 0,
                        'PPN' => $row->ppn ?? '',
                        'NPPN' => $row->nppn ?? 0,
                        'DPP' => $row->dpp ?? 0,
                        'TKP' => $row->tkp ?? 0,
                        'Total' => $row->total ?? 0,
                        'Keterangan PPN' => $row->PPN_KET ?? '',
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'data' => $data,
                'filename' => $filename
            ]);
        } catch (\Exception $e) {
            Log::error('Error in exportSalesPenjualanReport: ' . $e->getMessage());
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Method untuk mendapatkan summary/statistik data penjualan
     */
    public function getSummaryPenjualan($cbgCode, $kasir, $user, $tanggal, $periode, $reportType)
    {
        try {
            $MM = $this->determinePeriode($periode, $tanggal);
            $formattedDate = Carbon::parse($tanggal)->format('Y/m/d');

            if ($reportType == 1 && !empty($kasir)) {
                // Summary untuk kasir
                $query = "SELECT
                            COUNT(DISTINCT juald{$MM}.KD_BRG) as total_item,
                            SUM(juald{$MM}.qty) as total_qty,
                            SUM(juald{$MM}.total) as total_amount
                          FROM {$cbgCode}.jual{$MM}, {$cbgCode}.juald{$MM}
                          WHERE jual{$MM}.no_bukti = juald{$MM}.no_bukti
                          AND juald{$MM}.KD_BRG <> ''
                          AND jual{$MM}.flag = 'JL'
                          AND jual{$MM}.ksr = ?
                          AND jual{$MM}.tgl = ?";

                $result = DB::select($query, [$kasir, $formattedDate]);
            } else if ($reportType == 2 && !empty($user)) {
                // Summary untuk user
                $query = "SELECT
                            COUNT(DISTINCT juald{$MM}.KD_BRG) as total_item,
                            SUM(juald{$MM}.qty) as total_qty,
                            SUM(juald{$MM}.total) as total_amount
                          FROM {$cbgCode}.jual{$MM}, {$cbgCode}.juald{$MM}
                          WHERE jual{$MM}.no_bukti = juald{$MM}.no_bukti
                          AND juald{$MM}.KD_BRG <> ''
                          AND jual{$MM}.flag = 'JL'
                          AND jual{$MM}.usrnm = ?
                          AND jual{$MM}.tgl = ?";

                $result = DB::select($query, [$user, $formattedDate]);
            }

            return $result[0] ?? null;
        } catch (\Exception $e) {
            Log::error('Error in getSummaryPenjualan: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * API endpoint untuk mendapatkan summary data penjualan
     */
    public function apiGetPenjualanSummary(Request $request)
    {
        try {
            $cbgCode = $request->cbg;
            $kasir = $request->kasir;
            $user = $request->user;
            $periode = $request->periode;
            $tanggal = $request->tanggal;
            $reportType = $request->report_type ?? 1;

            if (empty($cbgCode) || empty($tanggal)) {
                return response()->json(['error' => 'Cabang dan tanggal harus dipilih'], 400);
            }

            $summary = $this->getSummaryPenjualan($cbgCode, $kasir, $user, $tanggal, $periode, $reportType);

            return response()->json([
                'success' => true,
                'summary' => $summary,
                'nama_toko' => $this->getNamaToko($cbgCode)
            ]);
        } catch (\Exception $e) {
            Log::error('Error in apiGetPenjualanSummary: ' . $e->getMessage());
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API untuk mendapatkan list user berdasarkan CBG
     */
    public function apiGetUserList(Request $request)
    {
        try {
            $cbgCode = $request->cbg;

            if (empty($cbgCode)) {
                return response()->json(['error' => 'CBG harus dipilih'], 400);
            }

            // Get user list from jual tables for specific CBG
            $query = "SELECT DISTINCT usrnm FROM {$cbgCode}.jual01 WHERE usrnm IS NOT NULL AND usrnm <> ''
                      UNION
                      SELECT DISTINCT usrnm FROM {$cbgCode}.jual02 WHERE usrnm IS NOT NULL AND usrnm <> ''
                      UNION
                      SELECT DISTINCT usrnm FROM {$cbgCode}.jual03 WHERE usrnm IS NOT NULL AND usrnm <> ''
                      UNION
                      SELECT DISTINCT usrnm FROM {$cbgCode}.jual04 WHERE usrnm IS NOT NULL AND usrnm <> ''
                      UNION
                      SELECT DISTINCT usrnm FROM {$cbgCode}.jual05 WHERE usrnm IS NOT NULL AND usrnm <> ''
                      UNION
                      SELECT DISTINCT usrnm FROM {$cbgCode}.jual06 WHERE usrnm IS NOT NULL AND usrnm <> ''
                      UNION
                      SELECT DISTINCT usrnm FROM {$cbgCode}.jual07 WHERE usrnm IS NOT NULL AND usrnm <> ''
                      UNION
                      SELECT DISTINCT usrnm FROM {$cbgCode}.jual08 WHERE usrnm IS NOT NULL AND usrnm <> ''
                      UNION
                      SELECT DISTINCT usrnm FROM {$cbgCode}.jual09 WHERE usrnm IS NOT NULL AND usrnm <> ''
                      UNION
                      SELECT DISTINCT usrnm FROM {$cbgCode}.jual10 WHERE usrnm IS NOT NULL AND usrnm <> ''
                      UNION
                      SELECT DISTINCT usrnm FROM {$cbgCode}.jual11 WHERE usrnm IS NOT NULL AND usrnm <> ''
                      UNION
                      SELECT DISTINCT usrnm FROM {$cbgCode}.jual12 WHERE usrnm IS NOT NULL AND usrnm <> ''
                      ORDER BY usrnm";

            $userList = DB::select($query);

            return response()->json([
                'success' => true,
                'data' => $userList
            ]);
        } catch (\Exception $e) {
            Log::error('Error in apiGetUserList: ' . $e->getMessage());
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API untuk mendapatkan list kasir berdasarkan CBG
     */
    public function apiGetKasirList(Request $request)
    {
        try {
            $cbgCode = $request->cbg;

            if (empty($cbgCode)) {
                return response()->json(['error' => 'CBG harus dipilih'], 400);
            }

            // Get kasir list from noks table for specific CBG
            $query = "SELECT kasir FROM {$cbgCode}.noks ORDER BY kasir";
            $kasirList = DB::select($query);

            return response()->json([
                'success' => true,
                'data' => $kasirList
            ]);
        } catch (\Exception $e) {
            Log::error('Error in apiGetKasirList: ' . $e->getMessage());
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Method untuk validasi data sebelum generate report
     */
    private function validateReportData($cbgCode, $kasir, $user, $tanggal, $periode, $reportType)
    {
        $errors = [];

        if (empty($cbgCode)) {
            $errors[] = 'Cabang harus dipilih';
        }

        if (empty($tanggal)) {
            $errors[] = 'Tanggal harus dipilih';
        }

        if ($reportType == 1) {
            if (empty($kasir)) {
                $errors[] = 'Kasir harus dipilih untuk laporan per kasir';
            }
        } else if ($reportType == 2) {
            if (empty($user)) {
                $errors[] = 'User harus dipilih untuk laporan per user';
            }
            if (empty($periode)) {
                $errors[] = 'Periode harus dipilih untuk laporan per user';
            }
        }

        return $errors;
    }

    /**
     * Method untuk mendapatkan detail transaksi berdasarkan no_bukti
     */
    public function getTransactionDetail(Request $request)
    {
        try {
            $cbgCode = $request->cbg;
            $noBukti = $request->no_bukti;
            $periode = $request->periode;

            if (empty($cbgCode) || empty($noBukti)) {
                return response()->json(['error' => 'Parameter tidak lengkap'], 400);
            }

            $MM = $this->determinePeriode($periode, null);

            $query = "SELECT
                        jual{$MM}.*,
                        juald{$MM}.*,
                        CASE
                            WHEN juald{$MM}.ppn='1' THEN 'PPN Barang Produksi'
                            WHEN juald{$MM}.ppn='2' THEN 'PPN Barang Cukai'
                            WHEN juald{$MM}.ppn='3' THEN 'PPN Barang Import'
                            ELSE 'Tanpa PPN'
                        END AS PPN_KET
                      FROM {$cbgCode}.jual{$MM}
                      LEFT JOIN {$cbgCode}.juald{$MM} ON jual{$MM}.no_bukti = juald{$MM}.no_bukti
                      WHERE jual{$MM}.no_bukti = ?
                      ORDER BY juald{$MM}.KD_BRG";

            $result = DB::select($query, [$noBukti]);

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getTransactionDetail: ' . $e->getMessage());
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Method untuk mendapatkan total penjualan per hari
     */
    public function getDailySalesTotal(Request $request)
    {
        try {
            $cbgCode = $request->cbg;
            $tanggal = $request->tanggal;
            $periode = $request->periode;

            if (empty($cbgCode) || empty($tanggal)) {
                return response()->json(['error' => 'Parameter tidak lengkap'], 400);
            }

            $MM = $this->determinePeriode($periode, $tanggal);
            $formattedDate = Carbon::parse($tanggal)->format('Y/m/d');

            $query = "SELECT
                        COUNT(DISTINCT jual{$MM}.no_bukti) as total_transaksi,
                        COUNT(DISTINCT jual{$MM}.ksr) as total_kasir,
                        COUNT(DISTINCT jual{$MM}.usrnm) as total_user,
                        SUM(juald{$MM}.qty) as total_qty,
                        SUM(juald{$MM}.total) as total_amount,
                        SUM(juald{$MM}.dpp) as total_dpp,
                        SUM(juald{$MM}.nppn) as total_ppn
                      FROM {$cbgCode}.jual{$MM}
                      LEFT JOIN {$cbgCode}.juald{$MM} ON jual{$MM}.no_bukti = juald{$MM}.no_bukti
                      WHERE jual{$MM}.tgl = ?
                      AND jual{$MM}.flag = 'JL'
                      AND juald{$MM}.KD_BRG <> ''";

            $result = DB::select($query, [$formattedDate]);

            return response()->json([
                'success' => true,
                'data' => $result[0] ?? null,
                'tanggal' => $tanggal,
                'nama_toko' => $this->getNamaToko($cbgCode)
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getDailySalesTotal: ' . $e->getMessage());
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Method untuk mendapatkan top selling products
     */
    public function getTopSellingProducts(Request $request)
    {
        try {
            $cbgCode = $request->cbg;
            $tanggal = $request->tanggal;
            $periode = $request->periode;
            $limit = $request->limit ?? 10;

            if (empty($cbgCode) || empty($tanggal)) {
                return response()->json(['error' => 'Parameter tidak lengkap'], 400);
            }

            $MM = $this->determinePeriode($periode, $tanggal);
            $formattedDate = Carbon::parse($tanggal)->format('Y/m/d');

            $query = "SELECT
                        juald{$MM}.KD_BRG,
                        juald{$MM}.NA_BRG,
                        SUM(juald{$MM}.qty) as total_qty,
                        SUM(juald{$MM}.total) as total_amount,
                        COUNT(DISTINCT jual{$MM}.no_bukti) as total_transaksi
                      FROM {$cbgCode}.jual{$MM}
                      LEFT JOIN {$cbgCode}.juald{$MM} ON jual{$MM}.no_bukti = juald{$MM}.no_bukti
                      WHERE jual{$MM}.tgl = ?
                      AND jual{$MM}.flag = 'JL'
                      AND juald{$MM}.KD_BRG <> ''
                      GROUP BY juald{$MM}.KD_BRG, juald{$MM}.NA_BRG
                      ORDER BY total_qty DESC
                      LIMIT ?";

            $result = DB::select($query, [$formattedDate, $limit]);

            return response()->json([
                'success' => true,
                'data' => $result,
                'tanggal' => $tanggal,
                'nama_toko' => $this->getNamaToko($cbgCode)
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getTopSellingProducts: ' . $e->getMessage());
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Method untuk mendapatkan performance kasir/user dalam periode tertentu
     */
    public function getPerformanceReport(Request $request)
    {
        try {
            $cbgCode = $request->cbg;
            $startDate = $request->start_date;
            $endDate = $request->end_date;
            $periode = $request->periode;
            $reportType = $request->report_type ?? 1; // 1=kasir, 2=user

            if (empty($cbgCode) || empty($startDate) || empty($endDate)) {
                return response()->json(['error' => 'Parameter tidak lengkap'], 400);
            }

            $MM = $this->determinePeriode($periode, $startDate);
            $formattedStartDate = Carbon::parse($startDate)->format('Y/m/d');
            $formattedEndDate = Carbon::parse($endDate)->format('Y/m/d');

            if ($reportType == 1) {
                // Performance per kasir
                $query = "SELECT
                            jual{$MM}.ksr,
                            COUNT(DISTINCT jual{$MM}.no_bukti) as total_transaksi,
                            SUM(juald{$MM}.qty) as total_qty,
                            SUM(juald{$MM}.total) as total_amount,
                            AVG(juald{$MM}.total) as avg_amount_per_transaction,
                            COUNT(DISTINCT jual{$MM}.tgl) as total_hari_kerja
                          FROM {$cbgCode}.jual{$MM}
                          LEFT JOIN {$cbgCode}.juald{$MM} ON jual{$MM}.no_bukti = juald{$MM}.no_bukti
                          WHERE jual{$MM}.tgl BETWEEN ? AND ?
                          AND jual{$MM}.flag = 'JL'
                          AND juald{$MM}.KD_BRG <> ''
                          GROUP BY jual{$MM}.ksr
                          ORDER BY total_amount DESC";
            } else {
                // Performance per user
                $query = "SELECT
                            jual{$MM}.usrnm,
                            COUNT(DISTINCT jual{$MM}.no_bukti) as total_transaksi,
                            SUM(juald{$MM}.qty) as total_qty,
                            SUM(juald{$MM}.total) as total_amount,
                            AVG(juald{$MM}.total) as avg_amount_per_transaction,
                            COUNT(DISTINCT jual{$MM}.tgl) as total_hari_kerja
                          FROM {$cbgCode}.jual{$MM}
                          LEFT JOIN {$cbgCode}.juald{$MM} ON jual{$MM}.no_bukti = juald{$MM}.no_bukti
                          WHERE jual{$MM}.tgl BETWEEN ? AND ?
                          AND jual{$MM}.flag = 'JL'
                          AND juald{$MM}.KD_BRG <> ''
                          GROUP BY jual{$MM}.usrnm
                          ORDER BY total_amount DESC";
            }

            $result = DB::select($query, [$formattedStartDate, $formattedEndDate]);

            return response()->json([
                'success' => true,
                'data' => $result,
                'period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ],
                'report_type' => $reportType == 1 ? 'kasir' : 'user',
                'nama_toko' => $this->getNamaToko($cbgCode)
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getPerformanceReport: ' . $e->getMessage());
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
