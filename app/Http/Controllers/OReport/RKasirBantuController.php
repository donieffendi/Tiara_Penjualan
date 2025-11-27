<?php

namespace App\Http\Controllers\OReport;

use App\Http\Controllers\Controller;
use App\Models\Master\Cbg;
use App\Models\Master\Perid;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;

class RKasirBantuController extends Controller
{
    /**
     * Halaman utama report - Route: /rkasirbantu
     */
    public function report()
    {
        $cbg = DB::SELECT("SELECT KODE FROM toko WHERE STA IN ('MA','CB','DC') ORDER BY NO_ID ASC");

        // Initialize session variables
        session()->put('filter_cbg', '');
        session()->put('filter_kodes', '');
        session()->put('filter_periode', '');

        return view('oreport_kasirbantu.report')->with([
            'cbg' => $cbg,
            'hasilKasirBantu' => []
        ]);
    }

    public function getKasirBantuReport(Request $request)
    {
        $listCbg = DB::SELECT("SELECT KODE FROM toko WHERE STA IN ('MA','CB','DC') ORDER BY NO_ID ASC");
        $tab = $request->tab ?? 'detail';

        switch ($tab) {

            case 'detail':
                $hasilKasirBantu = $this->getDetailKasirBantu();
                break;

            case 'summary':
                $hasilKasirBantu = $this->getSummaryKasirBantu();
                break;

            case 'kasir':
                if (empty($request->cbg)) {
                    return view('oreport_kasirbantu.report')->with([
                        'cbg' => $listCbg,
                        'hasilKasirBantu' => [],
                        'error' => 'Cabang harus dipilih untuk tab kasir.'
                    ]);
                }
                $hasilKasirBantu = $this->getKasirList($request->cbg);
                break;
        }

        return view('oreport_kasirbantu.report')->with([
            'cbg' => $listCbg,
            'hasilKasirBantu' => $hasilKasirBantu,
            'tab' => $tab
        ]);
    }

    public function getKasirBantuReportAjax(Request $request)
{
    $tab = $request->tab ?? 'detail';
    $cbg = $request->cbg ?? '';

    switch ($tab) {
        case 'detail':
            $data = $this->getDetailKasirBantu();
            break;
        case 'summary':
            $data = $this->getSummaryKasirBantu();
            break;
        case 'kasir':
            if (empty($cbg)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cabang harus dipilih untuk tab kasir.'
                ], 400);
            }
            $data = $this->getKasirList($cbg);
            break;
    }

    return response()->json([
        'success' => true,
        'data' => $data
    ]);
}



    /**
     * Generate laporan Jasper - Route: /jasper-kasirbantu-report
     * Implementasi dari logika Delphi untuk generate report
     */
    public function jasperKasirBantuReport(Request $request)
    {
        try {
            // Cek cbg wajib diisi
            if (empty($request->cbg)) {
                return response()->json(['error' => 'Cabang harus dipilih.'], 400);
            }

            $file = 'kasirbantu'; 
            $PHPJasperXML = new PHPJasperXML();
            $PHPJasperXML->load_xml_file(base_path('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

            $cbg = preg_replace('/[^A-Za-z0-9_]/', '', $request->cbg) . ".";

            // ===========================
            // SQL sesuai Delphi TAB KASIR
            // ===========================
            $sql = "
                SELECT 
                    jual.NO_BUKTI,
                    jual.tgl AS TGL,
                    jual.CBG
                FROM {$cbg}jual jual
                WHERE jual.FLAG = 'OB'
                AND jual.CBG = ?
                GROUP BY jual.NO_BUKTI, jual.tgl, jual.CBG
                ORDER BY jual.NO_BUKTI
            ";

            $rows = DB::select($sql, [$request->cbg]);

            // Format data untuk Jasper
            $data = array_map(function ($item) {
                return [
                    'NO_BUKTI' => $item->NO_BUKTI,
                    'TGL'      => $item->TGL,
                    'CBG'      => $item->CBG,
                    'TANGGAL_CETAK' => date('Y-m-d H:i:s'),
                ];
            }, $rows);

            $PHPJasperXML->setData($data);

            // Parameter tambahan jika butuh di jasper
            $PHPJasperXML->arrayParameter = [
                "CBG" => $request->cbg,
                "TANGGAL_CETAK" => date('d/m/Y H:i:s')
            ];

            ob_end_clean();
            $PHPJasperXML->outpage("I");

        } catch (\Exception $e) {
            Log::error('Error Jasper Kasir: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    private function getDetailKasirBantu()
    {
        $cbg = Auth::user()->CBG . '.'; 

        $sql = "
            SELECT 
                jual.no_bukti AS NO_BUKTI,
                jual.tgl AS TGL,
                juald.KD_BRG AS KD_BRG,
                juald.NA_BRG AS NA_BRG,
                juald.qty AS QTY,
                jual.cbg AS CBG
            FROM {$cbg}jual jual
            JOIN {$cbg}juald juald ON jual.no_bukti = juald.no_bukti
            WHERE jual.flag = 'OB'
            ORDER BY NO_BUKTI
        ";

        return DB::select($sql);
    }


    private function getSummaryKasirBantu()
    {
        $cbg = Auth::user()->CBG . '.';

        $sql = "
            SELECT 
                juald.KD_BRG,
                juald.NA_BRG,
                SUM(juald.qty) AS QTY,
                jual.cbg AS CBG
            FROM {$cbg}jual jual
            JOIN {$cbg}juald juald ON jual.no_bukti = juald.no_bukti
            WHERE jual.flag = 'OB'
            GROUP BY juald.KD_BRG, juald.NA_BRG, jual.cbg
            ORDER BY juald.KD_BRG
        ";

        return DB::select($sql);
    }

    public function getKasirList($cbg)
    {
        $cbgTable = trim($cbg) . ".";  // untuk nama tabel
        $cbgParam = trim($cbg);        // untuk parameter WHERE

        $sql = "
            SELECT jual.no_bukti AS NO_BUKTI, jual.tgl AS TGL, jual.cbg AS CBG
            FROM {$cbgTable}jual jual
            WHERE jual.flag = 'OB'
            AND jual.cbg = ?
            GROUP BY no_bukti, tgl, cbg
            ORDER BY no_bukti
        ";

        return DB::select($sql, [$cbgParam]);
    }

    /**
     * Get daftar cabang yang valid
     */
    public function getCabangList()
    {
        try {
            $query = "
                SELECT KODE, STA
                FROM toko
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
            $tab = $request->tab ?? 'detail';
            $cbg = $request->cbg ?? '';

            switch ($tab) {

                case 'detail':
                    $data = $this->getDetailKasirBantu();
                    break;

                case 'summary':
                    $data = $this->getSummaryKasirBantu();
                    break;

                case 'kasir':
                    if (empty($cbg)) {
                        return response()->json(['error' => 'Cabang harus diisi!'], 400);
                    }
                    $data = $this->getKasirList($cbg);
                    break;
            }

            if (empty($data)) {
                return response()->json(['message' => 'Tidak ada data'], 200);
            }

            $filename = "kasirbantu_{$tab}_" . date('YmdHis') . ".xlsx";

            return response()->json([
                'success' => true,
                'filename' => $filename,
                'total' => count($data)
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    /**
     * Search kasir bantu dengan AJAX
     */
    public function searchKasirBantu(Request $request)
    {
        try {
            $tab = $request->tab ?? 'detail';
            $cbg = $request->cbg ?? '';

            switch ($tab) {

                case 'detail':
                    $data = $this->getDetailKasirBantu();
                    break;

                case 'summary':
                    $data = $this->getSummaryKasirBantu();
                    break;

                case 'kasir':
                    if (empty($cbg)) {
                        return response()->json([
                            'success' => false,
                            'error' => 'Cabang harus diisi!'
                        ], 400);
                    }
                    $data = $this->getKasirList($cbg);
                    break;
            }

            return response()->json([
                'success' => true,
                'data' => $data,
                'total_records' => count($data)
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get nama toko berdasarkan kode cabang
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
     * Method untuk preview data sebelum print
     */
    public function previewKasirBantu(Request $request)
    {
        try {
            $cbg = $request->cbg;

            if (empty($cbg)) {
                return redirect()->back()->with('error', 'Cabang harus diisi!');
            }

            $data = $this->getKasirList($cbg);
            $namaToko = $this->getNamaToko($cbg);

            return view('oreport_kasirbantu.preview')->with([
                'data' => $data,
                'cbg' => $cbg,
                'namaToko' => $namaToko,
                'totalRecords' => count($data)
            ]);
        } catch (\Exception $e) {
            Log::error('Error in previewKasir: ' . $e->getMessage());
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}