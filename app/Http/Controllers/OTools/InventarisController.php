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
        try {
            $listCbg = DB::SELECT("SELECT KODE FROM toko WHERE STA IN ('MA','CB','DC') ORDER BY NO_ID ASC");
            $listPer = DB::SELECT("SELECT PERIO FROM perid ORDER BY PERIO ASC");
            $tab = $request->tab ?? 'kodetg';
            $cbg = $request->cbg;
            $per = $request->per;
            $cek = $request->cek;

            // Jika filter kosong, tampilkan semua data
            $hasilInventaris = [];

            switch ($tab) {
                case 'kodetg':
                    $hasilInventaris = $this->getKode3($cbg, $per, $cek);
                    break;

                case 'non':
                    $hasilInventaris = $this->getNon($cbg, $per, $cek);
                    break;

                case 'busana':
                    $hasilInventaris = $this->getBusana($cbg, $per, $cek);
                    break;

                case 'pusat':
                    $hasilInventaris = $this->getPusat($cbg, $per, $cek);
                    break;

                default:
                    return view('otools_inventaris.report')->with([
                        'cbg' => $listCbg,
                        'per' => $listPer,
                        'hasilInventaris' => [],
                        'error' => 'Tab tidak valid.',
                        'tab' => $tab
                    ]);
            }

            return view('otools_inventaris.report')->with([
                'cbg' => $listCbg,
                'per' => $listPer,
                'hasilInventaris' => $hasilInventaris,
                'tab' => $tab,
                'success' => 'Data berhasil dimuat.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error getInventarisReport: ' . $e->getMessage());

            return view('otools_inventaris.report')->with([
                'cbg' => $listCbg ?? [],
                'per' => $listPer ?? [],
                'hasilInventaris' => [],
                'error' => 'Terjadi kesalahan saat memuat data: ' . $e->getMessage(),
                'tab' => $tab ?? 'kodetg'
            ]);
        }
    }



    /**
     * Generate laporan Jasper - Route: /jasper-kasirbantu-report
     * Implementasi dari logika Delphi untuk generate report
     */
    public function jasperInventarisReport(Request $request)
    {
        try {
            $tab = $request->tab ?? 'kodetg';
            $cbg = $request->cbg;
            $per = $request->per;
            $cek = $request->cek;

            switch ($tab) {
                case 'kodetg':
                    $file = 'print_invent_kode3';
                    $results = $this->getKode3($cbg, $per, $cek);
                    break;
                case 'non':
                    $file = 'print_invent_nonkode3';
                    $results = $this->getNon($cbg, $per, $cek);
                    break;
                case 'busana':
                    $file = 'print_invent_busana';
                    $results = $this->getBusana($cbg, $per, $cek);
                    break;
                case 'pusat':
                    $file = 'print_invent_pusat';
                    $results = $this->getPusat($cbg, $per, $cek);
                    break;
                default:
                    abort(404, 'Jenis report tidak dikenali');
            }

            $data = json_decode(json_encode($results), true);

            $PHPJasperXML = new PHPJasperXML();
            $PHPJasperXML->load_xml_file(base_path('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));
            $params = [
                "TGL" => date('d/m/Y'),
            ];
            $PHPJasperXML->arrayParameter = $params;

            $PHPJasperXML->setData($data);
            ob_end_clean();
            $PHPJasperXML->outpage("I");
        } catch (\Exception $e) {
            Log::error('Error jasperInventarisReport: ' . $e->getMessage());
            abort(500, 'Terjadi kesalahan saat generate report: ' . $e->getMessage());
        }
    }


    private function getKode3($cbg, $per, $cek)
    {
        try {
            // Jika cabang dan periode kosong, gunakan stored procedure untuk semua data
            if (empty($cbg) && empty($per)) {
                $waktu = ($cek == 1) ? 'AKHIR' : 'AWAL';
                $sql = "CALL akt_inventarisasi_all('KODE3', '$waktu')";
            } else {
                // Jika ada filter, gunakan stored procedure biasa
                $waktu = ($cek == 1) ? 'AKHIR' : 'AWAL';
                $cbgParam = $cbg ?? '';
                $perParam = $per ?? '';
                $sql = "CALL akt_inventarisasi('KODE3', '$cbgParam', '$perParam', '$waktu')";
            }

            return DB::select($sql);
        } catch (\Exception $e) {
            Log::error('Error getKode3: ' . $e->getMessage());
            throw $e;
        }
    }

    private function getNon($cbg, $per, $cek)
    {
        try {
            // Jika cabang dan periode kosong, gunakan stored procedure untuk semua data
            if (empty($cbg) && empty($per)) {
                $waktu = ($cek == 1) ? 'AKHIR' : 'AWAL';
                $sql = "CALL akt_inventarisasi_all('SPM', '$waktu')";
            } else {
                // Jika ada filter, gunakan stored procedure biasa
                $waktu = ($cek == 1) ? 'AKHIR' : 'AWAL';
                $cbgParam = $cbg ?? '';
                $perParam = $per ?? '';
                $sql = "CALL akt_inventarisasi('SPM', '$cbgParam', '$perParam', '$waktu')";
            }

            return DB::select($sql);
        } catch (\Exception $e) {
            Log::error('Error getNon: ' . $e->getMessage());
            throw $e;
        }
    }

    private function getBusana($cbg, $per, $cek)
    {
        try {
            // Jika cabang dan periode kosong, gunakan stored procedure untuk semua data
            if (empty($cbg) && empty($per)) {
                $waktu = ($cek == 1) ? 'AKHIR' : 'AWAL';
                $sql = "CALL akt_inventarisasi_all('BSN', '$waktu')";
            } else {
                // Jika ada filter, gunakan stored procedure biasa
                $waktu = ($cek == 1) ? 'AKHIR' : 'AWAL';
                $cbgParam = $cbg ?? '';
                $perParam = $per ?? '';
                $sql = "CALL akt_inventarisasi('BSN', '$cbgParam', '$perParam', '$waktu')";
            }

            return DB::select($sql);
        } catch (\Exception $e) {
            Log::error('Error getBusana: ' . $e->getMessage());
            throw $e;
        }
    }

    private function getPusat($cbg, $per, $cek)
    {
        try {
            // Jika cabang dan periode kosong, gunakan stored procedure untuk semua data
            if (empty($cbg) && empty($per)) {
                $waktu = ($cek == 1) ? 'AKHIR' : 'AWAL';
                $sql = "CALL akt_inventarisasi_all('PH', '$waktu')";
            } else {
                // Jika ada filter, gunakan stored procedure biasa
                $waktu = ($cek == 1) ? 'AKHIR' : 'AWAL';
                $cbgParam = $cbg ?? '';
                $perParam = $per ?? '';
                $sql = "CALL akt_inventarisasi('PH', '$cbgParam', '$perParam', '$waktu')";
            }

            return DB::select($sql);
        } catch (\Exception $e) {
            Log::error('Error getPusat: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getInventarisReportAjax(Request $request)
    {
        try {
            $tab = $request->tab ?? 'kodetg';
            $cbg = $request->cbg ?? '';
            $per = $request->per ?? '';
            $cek = $request->cek ?? 0;

            $data = [];

            switch ($tab) {
                case 'kodetg':
                    $data = $this->getKode3($cbg, $per, $cek);
                    break;
                case 'non':
                    $data = $this->getNon($cbg, $per, $cek);
                    break;
                case 'busana':
                    $data = $this->getBusana($cbg, $per, $cek);
                    break;
                case 'pusat':
                    $data = $this->getPusat($cbg, $per, $cek);
                    break;
                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Tab tidak valid.'
                    ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dimuat.',
                'data' => $data,
                'count' => count($data)
            ]);
        } catch (\Exception $e) {
            Log::error('Error getInventarisReportAjax: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memuat data: ' . $e->getMessage()
            ], 500);
        }
    }
}
