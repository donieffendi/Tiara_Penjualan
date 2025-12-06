<?php
namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;

class PHPartisipasiSupplierController extends Controller
{
    /**
     * Halaman untuk Laporan Promo Gayan - Penjualan
     */
    public function indexPenjualan(Request $request)
    {
        return $this->index($request, 'CETAK_PENJUALAN');
    }

    /**
     * Halaman untuk Laporan Promo Gayan - Per Item
     */
    public function indexPerItem(Request $request)
    {
        return $this->index($request, 'CETAK_PER_ITEM');
    }

    /**
     * Method utama untuk menampilkan halaman
     */
    private function index(Request $request, $jenisLap)
    {
        try {
            $cbg   = DB::select("SELECT KODE FROM toko WHERE STA = 'MA'");
            $cbgMa = ! empty($cbg) ? $cbg[0]->KODE : '';
        } catch (Exception $e) {
            $cbgMa = '';
        }

        $title = $jenisLap == 'CETAK_PENJUALAN'
            ? 'Laporan Promo Gayan - Penjualan'
            : 'Laporan Promo Gayan - Per Item';

        return view('promo_hadiah_partisipasi_supplier.index', [
            'cbgMa'    => $cbgMa,
            'jenisLap' => $jenisLap,
            'title'    => $title,
        ]);
    }
    private function cetakPDFGlobal($file, $result)
    {
        foreach ($result as $row) {
            $row->TGL = now()->format('d/m/Y');
            $data[]   = (array) $row;
        }
        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));
        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I");
    }

//     public function cetakPDF(Request $request)
// {
//     try {

//         $jenisLap   = $request->get('jenisLap');
//         $tipePromo  = $request->tipePromo;
//         $cbgMa      = 'TGZ';
//         $tglDari    = $request->tglDari;
//         $tglSampai  = $request->tglSampai;
//         $reportName = $request->reportName;
//         $TGL        = Carbon::now()->format('d/m/Y');

//         $result = DB::select(
//             "CALL pjl_laporan_gayan(?, ?, ?, ?, ?)",
//             [$jenisLap, $tipePromo, $cbgMa, $tglDari, $tglSampai]
//         );

//         if (empty($result)) {
//             return "Data tidak ditemukan";
//         }

//         // ================================
//         // PILIH FILE JRXML
//         // ================================
//         $files = [];

//         if ($jenisLap == 'CETAK_PER_ITEM') {

//             if ($tipePromo == 'TURUN HARGA') {
//                 // <<< INI PERBAIKANNYA
//                 $files = ['item_PGH_global', 'item_PGH'];
//             } elseif ($tipePromo == 'CASHBACK') {
//                 $files = ['item_PGC'];
//             } elseif ($tipePromo == 'POIN') {
//                 $files = ['item_PGP'];
//             }

//         } elseif ($jenisLap == 'CETAK_PENJUALAN') {

//             if ($tipePromo == 'TURUN HARGA') {
//                 $files = ['revaluasi_rlap_PGH'];
//             } elseif ($tipePromo == 'CASHBACK') {
//                 $files = ['lap_PGC'];
//             } elseif ($tipePromo == 'POIN') {
//                 $files = ['lap_PGP'];
//             }
//         }

//         if (empty($files)) {
//             return "Template laporan tidak ditemukan.";
//         }

//         // Jika file > 1, tampilkan semua PDF satu per satu (multi output)
//         $cleanData = json_decode(json_encode($result), true);

//         foreach ($files as $file) {

//             $path = base_path("app/reportc01/phpjasperxml/{$file}.jrxml");

//             if (!file_exists($path)) {
//                 return "File JRXML tidak ditemukan: {$path}";
//             }

//             $PHPJasperXML = new \PHPJasperXML();
//             $PHPJasperXML->load_xml_file($path);
//             $PHPJasperXML->setData($cleanData);
//             $PHPJasperXML->arrayParameter = [
//                 "TGL_CETAK" => $TGL,
//             ];

//             if (ob_get_length()) {
//                 ob_end_clean();
//             }

//             // Tampilkan 1 PDF lalu stop loop
//             $PHPJasperXML->outpage("I");
//             return; // <-- penting agar 1 response = 1 PDF
//         }

//     } catch (\Exception $e) {
//         return $e->getMessage();
//     }
// }
    public function cetakPDF(Request $request)
    {
        try {

            $jenisLap   = $request->jenisLap;
            $tipePromo  = $request->tipePromo;
            $cbgMa      = 'TGZ';
            $tglDari    = $request->tglDari;
            $tglSampai  = $request->tglSampai;
            $reportName = $request->reportName;
            $fileIndex  = $request->file ?? 0;
            $TGL        = Carbon::now()->format('d/m/Y');

            $result = DB::select(
                "CALL pjl_laporan_gayan(?, ?, ?, ?, ?)",
                [$jenisLap, $tipePromo, $cbgMa, $tglDari, $tglSampai]
            );

            if (empty($result)) {
                return "Data tidak ditemukan";
            }

            $files = [];

            if ($jenisLap == 'CETAK_PER_ITEM') {

                if ($tipePromo == 'TURUN HARGA') {
                    $files = ['item_PGH_global', 'item_PGH'];
                } elseif ($tipePromo == 'CASHBACK') {
                    $files = ['item_PGC'];
                } elseif ($tipePromo == 'POIN') {
                    $files = ['item_PGP'];
                }

            } elseif ($jenisLap == 'CETAK_PENJUALAN') {

                if ($tipePromo == 'TURUN HARGA') {
                    $files = ['revaluasi_rlap_PGH'];
                } elseif ($tipePromo == 'CASHBACK') {
                    $files = ['lap_PGC'];
                } elseif ($tipePromo == 'POIN') {
                    $files = ['lap_PGP'];
                }
            }

            if (empty($files)) {
                return "Template laporan tidak ditemukan.";
            }

            if (! isset($files[$fileIndex])) {
                return "Tidak ada file untuk index $fileIndex";
            }

            $file = $files[$fileIndex];
            $path = base_path("app/reportc01/phpjasperxml/{$file}.jrxml");

            if (! file_exists($path)) {
                return "File JRXML tidak ditemukan: {$path}";
            }

            $cleanData = json_decode(json_encode($result), true);

            $PHPJasperXML = new \PHPJasperXML();
            $PHPJasperXML->load_xml_file($path);
            $PHPJasperXML->setData($cleanData);
            $PHPJasperXML->arrayParameter = [
                "TGL_CETAK" => $TGL,
            ];

            if (ob_get_length()) {
                ob_end_clean();
            }

            $PHPJasperXML->outpage("I");

        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Method untuk cetak laporan
     */
    public function cetak(Request $request)
    {
        try {
            $jenisLap  = $request->get('jenisLap');
            $tipePromo = $request->get('tipePromo');
            $cbgMa     = $request->get('cbgMa');
            $tglDari   = $request->get('tglDari');
            $tglSampai = $request->get('tglSampai');
            $cbg       = session('user_cbg', '');

            if (empty($tipePromo)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cek pilihan promo',
                ]);
            }

            $result = DB::select(
                "CALL pjl_laporan_gayan(?, ?, ?, ?, ?)",
                [$jenisLap, $tipePromo, $cbgMa, $tglDari, $tglSampai]
            );

            if (empty($result)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data Tidak Ditemukan!',
                ]);
            }

            return response()->json([
                'success'   => true,
                'data'      => $result,
                'tipePromo' => strtoupper($tipePromo),
                'jenisLap'  => $jenisLap,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
}