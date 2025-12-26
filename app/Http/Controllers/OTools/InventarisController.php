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
     * Halaman utama report - Route: /inventaris
     * Data akan dimuat via AJAX saat halaman dibuka
     */
    public function report()
    {
        try {
            $cbg = DB::SELECT("SELECT KODE FROM toko WHERE STA IN ('MA','CB','DC') ORDER BY NO_ID ASC");
            $per = DB::SELECT("SELECT PERIO from perid ORDER BY PERIO DESC");

            // Get default values
            $defaultCbg = !empty($cbg) ? $cbg[0]->KODE : '';
            $defaultPer = !empty($per) ? $per[0]->PERIO : '';

            return view('otools_inventaris.report')->with([
                'cbg' => $cbg,
                'per' => $per,
                'defaultCbg' => $defaultCbg,
                'defaultPer' => $defaultPer,
                'hasilInventaris' => []
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in inventaris report: ' . $e->getMessage());
            return view('otools_inventaris.report')->with([
                'cbg' => [],
                'per' => [],
                'defaultCbg' => '',
                'defaultPer' => '',
                'hasilInventaris' => [],
                'error' => 'Terjadi kesalahan saat memuat halaman: ' . $e->getMessage()
            ]);
        }
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
                $hasilInventaris = $this->getKode3($request->cbg, $request->per, $request->cek);
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
                $hasilInventaris = $this->getNon($request->cbg, $request->per, $request->cek);
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
                $hasilInventaris = $this->getNon($request->cbg, $request->per, $request->cek);
                break;

            case 'pusat':
                if (empty($request->cbg)) {
                    return view('otools_inventaris.report')->with([
                        'cbg' => $listCbg,
                        'per' => $listPer,
                        'hasilInventaris' => [],
                        'error' => 'Cabang dan Periode harus dipilih untuk tab Pusat Hidangan.'
                    ]);
                }
                $hasilInventaris = $this->getPusat($request->cbg, $request->per, $request->cek);
                break;
        }

        return view('otools_inventaris.report')->with([
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
    public function jasperInventarisReport(Request $request)
    {
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
    }


    private function getKode3($cbg, $per, $cek)
    {
        try {
            $status = ($cek == 1) ? 'AKHIR' : 'AWAL';

            \Log::info("Calling getKode3: CBG=$cbg, PER=$per, STATUS=$status");

            // Query untuk Kode 3 (non SPM, non Busana, non PH)
            $sql = "SELECT 
                        h.NO_BUKTI as NO_FORM,
                        h.TGL,
                        h.SUB,
                        h.SUB as KELOMPOK,
                        SUM(d.saldo * d.HB) as SALDO,
                        SUM(d.riil * d.HB) as TOTAL,
                        SUM((d.riil - d.saldo) * d.HB) as SELISIH,
                        SUM(CASE WHEN d.saldo - d.riil > 0 THEN d.saldo - d.riil ELSE 0 END) as GANTUNG,
                        SUM(CASE WHEN d.saldo - d.riil > 0 THEN (d.saldo - d.riil) * d.HB ELSE 0 END) as RP_GANTUNG,
                        SUM((d.riil - d.saldo) * d.HB) as RP_SELISIH,
                        h.CBG as NA_TOKO,
                        '' as barcode,
                        '' as totz
                    FROM stockb h
                    INNER JOIN stockbd d ON h.NO_BUKTI = d.NO_BUKTI
                    WHERE h.CBG = ?
                        AND h.PER = ?
                        AND h.FLAG2 = ?
                        AND d.JNS NOT IN ('SPM', 'BSN', 'PH')
                    GROUP BY h.NO_BUKTI, h.TGL, h.SUB, h.CBG
                    ORDER BY h.NO_BUKTI";

            $result = DB::select($sql, [$cbg, $per, $status]);

            return $result;
        } catch (\Exception $e) {
            \Log::error('Error in getKode3: ' . $e->getMessage());
            throw new \Exception('Gagal mengambil data Kode 3: ' . $e->getMessage());
        }
    }

    private function getNon($cbg, $per, $cek)
    {
        try {
            $status = ($cek == 1) ? 'AKHIR' : 'AWAL';

            \Log::info("Calling getNon: CBG=$cbg, PER=$per, STATUS=$status");

            // Query untuk Non Kode 3 (SPM)
            $sql = "SELECT 
                        h.NO_BUKTI as NO_FORM,
                        h.TGL,
                        h.SUB,
                        h.SUB as KELOMPOK,
                        SUM(d.saldo * d.HB) as SALDO,
                        SUM(d.riil * d.HB) as TOTAL,
                        SUM((d.riil - d.saldo) * d.HB) as SELISIH,
                        SUM(CASE WHEN d.saldo - d.riil > 0 THEN d.saldo - d.riil ELSE 0 END) as GANTUNG,
                        SUM(CASE WHEN d.saldo - d.riil > 0 THEN (d.saldo - d.riil) * d.HB ELSE 0 END) as RP_GANTUNG,
                        SUM((d.riil - d.saldo) * d.HB) as RP_SELISIH,
                        h.CBG as NA_TOKO,
                        '' as barcode,
                        '' as totz
                    FROM stockb h
                    INNER JOIN stockbd d ON h.NO_BUKTI = d.NO_BUKTI
                    WHERE h.CBG = ?
                        AND h.PER = ?
                        AND h.FLAG2 = ?
                        AND d.JNS = 'SPM'
                    GROUP BY h.NO_BUKTI, h.TGL, h.SUB, h.CBG
                    ORDER BY h.NO_BUKTI";

            $result = DB::select($sql, [$cbg, $per, $status]);

            return $result;
        } catch (\Exception $e) {
            \Log::error('Error in getNon: ' . $e->getMessage());
            throw new \Exception('Gagal mengambil data Non Kode 3: ' . $e->getMessage());
        }
    }

    private function getBusana($cbg, $per, $cek)
    {
        try {
            $status = ($cek == 1) ? 'AKHIR' : 'AWAL';

            \Log::info("Calling getBusana: CBG=$cbg, PER=$per, STATUS=$status");

            // Query untuk Busana
            $sql = "SELECT 
                        h.NO_BUKTI as NO_FORM,
                        h.TGL,
                        h.SUB,
                        h.SUB as KELOMPOK,
                        SUM(d.saldo * d.HB) as SALDO,
                        SUM(d.riil * d.HB) as TOTAL,
                        SUM((d.riil - d.saldo) * d.HB) as SELISIH,
                        SUM(CASE WHEN d.saldo - d.riil > 0 THEN d.saldo - d.riil ELSE 0 END) as GANTUNG,
                        SUM(CASE WHEN d.saldo - d.riil > 0 THEN (d.saldo - d.riil) * d.HB ELSE 0 END) as RP_GANTUNG,
                        SUM((d.riil - d.saldo) * d.HB) as RP_SELISIH,
                        h.CBG as NA_TOKO,
                        '' as barcode,
                        '' as totz
                    FROM stockb h
                    INNER JOIN stockbd d ON h.NO_BUKTI = d.NO_BUKTI
                    WHERE h.CBG = ?
                        AND h.PER = ?
                        AND h.FLAG2 = ?
                        AND d.JNS = 'BSN'
                    GROUP BY h.NO_BUKTI, h.TGL, h.SUB, h.CBG
                    ORDER BY h.NO_BUKTI";

            $result = DB::select($sql, [$cbg, $per, $status]);

            return $result;
        } catch (\Exception $e) {
            \Log::error('Error in getBusana: ' . $e->getMessage());
            throw new \Exception('Gagal mengambil data Busana: ' . $e->getMessage());
        }
    }

    private function getPusat($cbg, $per, $cek)
    {
        try {
            $status = ($cek == 1) ? 'AKHIR' : 'AWAL';

            \Log::info("Calling getPusat: CBG=$cbg, PER=$per, STATUS=$status");

            // Query untuk Pusat Hidangan
            $sql = "SELECT 
                        h.NO_BUKTI as NO_FORM,
                        h.TGL,
                        h.SUB,
                        h.SUB as KELOMPOK,
                        SUM(d.saldo * d.HB) as SALDO,
                        SUM(d.riil * d.HB) as TOTAL,
                        SUM((d.riil - d.saldo) * d.HB) as SELISIH,
                        SUM(CASE WHEN d.saldo - d.riil > 0 THEN d.saldo - d.riil ELSE 0 END) as GANTUNG,
                        SUM(CASE WHEN d.saldo - d.riil > 0 THEN (d.saldo - d.riil) * d.HB ELSE 0 END) as RP_GANTUNG,
                        SUM((d.riil - d.saldo) * d.HB) as RP_SELISIH,
                        h.CBG as NA_TOKO,
                        '' as barcode,
                        '' as totz
                    FROM stockb h
                    INNER JOIN stockbd d ON h.NO_BUKTI = d.NO_BUKTI
                    WHERE h.CBG = ?
                        AND h.PER = ?
                        AND h.FLAG2 = ?
                        AND d.JNS = 'PH'
                    GROUP BY h.NO_BUKTI, h.TGL, h.SUB, h.CBG
                    ORDER BY h.NO_BUKTI";

            $result = DB::select($sql, [$cbg, $per, $status]);

            return $result;
        } catch (\Exception $e) {
            \Log::error('Error in getPusat: ' . $e->getMessage());
            throw new \Exception('Gagal mengambil data Pusat Hidangan: ' . $e->getMessage());
        }
    }

    /**
     * AJAX endpoint untuk load data inventaris
     * Dipanggil saat pertama load halaman atau saat ganti tab/filter
     */
    public function getInventarisReportAjax(Request $request)
    {
        try {
            $tab = $request->tab ?? 'kodetg';
            $cbg = $request->cbg ?? '';
            $per = $request->per ?? '';
            $cek = $request->cek ?? 0;

            // Get default values if not provided
            if (empty($cbg)) {
                $defaultCbg = DB::SELECT("SELECT KODE FROM toko WHERE STA IN ('MA','CB','DC') ORDER BY NO_ID ASC LIMIT 1");
                $cbg = !empty($defaultCbg) ? $defaultCbg[0]->KODE : '';
            }

            if (empty($per)) {
                $defaultPer = DB::SELECT("SELECT PERIO FROM perid ORDER BY PERIO DESC LIMIT 1");
                $per = !empty($defaultPer) ? $defaultPer[0]->PERIO : '';
            }

            // Validate required parameters
            if (empty($cbg)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cabang tidak ditemukan. Pastikan data toko tersedia.'
                ], 400);
            }

            if (empty($per)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Periode tidak ditemukan. Pastikan data periode tersedia.'
                ], 400);
            }

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
                        'message' => 'Tab tidak dikenali: ' . $tab
                    ], 400);
            }

            return response()->json([
                'success' => true,
                'data' => $data,
                'tab' => $tab,
                'cbg' => $cbg,
                'per' => $per,
                'cek' => $cek,
                'count' => count($data)
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in getInventarisReportAjax: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memuat data: ' . $e->getMessage(),
                'error_detail' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }
}
