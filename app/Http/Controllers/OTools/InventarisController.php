<?php

namespace App\Http\Controllers\OTools;

use App\Http\Controllers\Controller;
use App\Models\Master\Cbg;
use App\Models\Master\Perid;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;

class InventarisController extends Controller
{
    /**
     * Halaman utama report - Route: /rkasirbantu
     */
    public function report()
    {
        $cbg = DB::SELECT("SELECT KODE FROM toko WHERE STA IN ('MA','CB','DC') ORDER BY NO_ID ASC");
        $per = DB::SELECT("SELECT PERIO from perid");

        // Initialize session variables
        session()->put('filter_cbg', '');
        session()->put('filter_per', '');

        return view('otools_inventaris.report')->with([
            'cbg' => $cbg,
            'per' => $per,
            'hasilInventaris' => []
        ]);
    }

    public function getInventarisReport(Request $request)
    {
        $listCbg = DB::SELECT("SELECT KODE FROM toko WHERE STA IN ('MA','CB','DC') ORDER BY NO_ID ASC");
        $listPer = DB::SELECT("SELECT PERIO FROM perid ORDER BY PERIO ASC");
        $tab = $request->tab ?? 'kodetg';

        switch ($tab) {

            case 'kodetg':
                if (empty($request->cbg && $request->per)) {
                    return view('otools_inventaris.report')->with([
                        'cbg' => $listCbg,
                        'per' => $listPer,
                        'hasilInventaris' => [],
                        'error' => 'Cabang dan Periode harus dipilih untuk tab Kode 3.'
                    ]);
                }
                $hasilInventaris = $this->getKode3($request->cbg, $request->per);
                break;

            case 'non':
                if (empty($request->cbg && $request->per)) {
                    return view('otools_inventaris.report')->with([
                        'cbg' => $listCbg,
                        'per' => $listPer,
                        'hasilInventaris' => [],
                        'error' => 'Cabang dan Periode harus dipilih untuk tab Non Kode 3.'
                    ]);
                }
                $hasilInventaris = $this->getNon($request->cbg, $request->per);
                break;
            
            case 'busana':
                if (empty($request->cbg && $request->per)) {
                    return view('otools_inventaris.report')->with([
                        'cbg' => $listCbg,
                        'per' => $listPer,
                        'hasilInventaris' => [],
                        'error' => 'Cabang dan Periode harus dipilih untuk tab Busana.'
                    ]);
                }
                $hasilInventaris = $this->getNon($request->cbg, $request->per);
                break;

            case 'pusat':
                if (empty($request->cbg)) {
                    return view('oreport_kasirbantu.report')->with([
                        'cbg' => $listCbg,
                        'per' => $listPer,
                        'hasilInventaris' => [],
                        'error' => 'Cabang dan Periode harus dipilih untuk tab Pusat Hidangan.'
                    ]);
                }
                $hasilInventaris = $this->getPusat($request->cbg, $request->per);
                break;
        }

        return view('oreport_kasirbantu.report')->with([
            'cbg' => $listCbg,
            'per' => $listPer,
            'hasilInventaris' => $hasilInventaris,
            'tab' => $tab
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


    private function getKode3(Request $request)
    {
        $cbg = $request->cbg;
        $per = $request->per;
        $cek = $request->cek;

        if($cek == 1){
            $sql = "CALL akt_inventarisasi('KODE3','$cbg','$per','AKHIR')";
        } else {
            $sql = "CALL akt_inventarisasi('KODE3','$cbg','$per','AWAL')";
        }

        return DB::select($sql);
    }

    private function getNon(Request $request)
    {
        $cbg = $request->cbg;
        $per = $request->per;
        $cek = $request->cek;

        if($cek == 1){
            $sql = "CALL akt_inventarisasi('SPM','$cbg','$per','AKHIR')";
        } else {
            $sql = "CALL akt_inventarisasi('SPM','$cbg','$per','AWAL')";
        }

        return DB::select($sql);
    }

    private function getBusana(Request $request)
    {
        $cbg = $request->cbg;
        $per = $request->per;
        $cek = $request->cek;

        if($cek == 1){
            $sql = "CALL akt_inventarisasi('BSN','$cbg','$per','AKHIR')";
        } else {
            $sql = "CALL akt_inventarisasi('BSN','$cbg','$per','AWAL')";
        }

        return DB::select($sql);
    }

    private function getPusat(Request $request)
    {
        $cbg = $request->cbg;
        $per = $request->per;
        $cek = $request->cek;

        if($cek == 1){
            $sql = "CALL akt_inventarisasi('PH','$cbg','$per','AKHIR')";
        } else {
            $sql = "CALL akt_inventarisasi('PH','$cbg','$per','AWAL')";
        }

        return DB::select($sql);
    }

    public function getInventarisReportAjax(Request $request)
    {
        $tab = $request->tab ?? 'kodetg';
        $cbg = $request->cbg ?? '';
        $per = $request->per ?? '';
        $cek = $request->cek;

        switch ($tab) {
            case 'kodetg':
                if (empty($cbg)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cabang Dan Periode harus dipilih untuk tab Kode 3.'
                    ], 400);
                }
                $data = $this->getKode3($cbg, $per, $cek);
                break;
            case 'non':
                if (empty($cbg && $per)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cabang Dan Periode harus dipilih untuk tab Non Kode 3.'
                    ], 400);
                }
                $data = $this->getNon($cbg, $per, $cek);
                break;
            case 'busana':
                if (empty($cbg)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cabang Dan Periode harus dipilih untuk tab Busana.'
                    ], 400);
                }
                $data = $this->getBusana($cbg, $per, $cek);
                break;
            case 'pusat':
                if (empty($cbg)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cabang Dan Periode harus dipilih untuk tab Pusat.'
                    ], 400);
                }
                $data = $this->getPusat($cbg, $per, $cek);
                break;
            }

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
}