<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

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
            $cbg = DB::select("SELECT KODE FROM toko WHERE STA = 'MA'");
            $cbgMa = !empty($cbg) ? $cbg[0]->KODE : '';
        } catch (Exception $e) {
            $cbgMa = '';
        }

        $title = $jenisLap == 'CETAK_PENJUALAN'
            ? 'Laporan Promo Gayan - Penjualan'
            : 'Laporan Promo Gayan - Per Item';

        return view('promo_hadiah_partisipasi_supplier.index', [
            'cbgMa' => $cbgMa,
            'jenisLap' => $jenisLap,
            'title' => $title
        ]);
    }
    private function cetakPDFGlobal($file, $result)
    {
        foreach ($result as $row) {
            $row->TGL = now()->format('d/m/Y');
            $data[] = (array)$row;
        }
        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));
        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I");
    }
    public function cetakPDF(Request $request)
    {
       
        try {
            $PHPJasperXML = new PHPJasperXML();

            $jenisLap = $request->get('jenisLap');
            $tipePromo = $request->get('tipePromo');
            $cbgMa = $request->get('cbgMa');
            $tglDari = $request->get('tglDari');
            $tglSampai = $request->get('tglSampai');
            $reportName = $request->get('reportName');
            $result = DB::select(
                "CALL pjl_laporan_gayan(?, ?, ?, ?, ?)",
                [$jenisLap, $tipePromo, $cbgMa, $tglDari, $tglSampai]
            );
            
            $data = [];
            $file = '';
            if ($jenisLap == 'CETAK_PER_ITEM'){
                if ($tipePromo=='TURUN HARGA') {
                    $file = 'item_PGH';
                    
                    if($reportName == 'Promo_Turun_Harga_Penjualan'){
                        
                        $this->cetakPDFGlobal("item_PGH_global", $result, 'Laporan Promo Gayan - Per Item - Turun Harga');
                    }
                }
                if ($tipePromo == 'CASHBACK') {
                    $file = 'item_PGC';

                }
                if ($tipePromo == 'POIN') {
                    $file = 'item_PGP';

                }
            }else if ($jenisLap == 'CETAK_PENJUALAN'){
                  if ($tipePromo=='TURUN HARGA') {
                    $file = 'lap_PGH';
                    usort($result, function ($a, $b) {
                        return strcmp($a->SUPP, $b->SUPP);
                    });
                  
                }
                if ($tipePromo == 'CASHBACK') {
                    $file = 'lap_PGC';

                }
                if ($tipePromo == 'POIN') {
                    $file = 'lap_PGP';

                }
            }
            $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));


            foreach ($result as $row) {
                $row->TGL = now()->format('d/m/Y');
                $data[] = (array)$row;
                // if($row->SUPP == '620F'){
                // echo '<br>';
                // }

            }
            // echo json_encode($data);
            // return;
            $PHPJasperXML->setData($data);
            ob_end_clean();
            $PHPJasperXML->outpage("I");
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    /**
     * Method untuk cetak laporan
     */
    public function cetak(Request $request)
    {
        try {
            $jenisLap = $request->get('jenisLap');
            $tipePromo = $request->get('tipePromo');
            $cbgMa = $request->get('cbgMa');
            $tglDari = $request->get('tglDari');
            $tglSampai = $request->get('tglSampai');
            $cbg = session('user_cbg', '');

            if (empty($tipePromo)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cek pilihan promo'
                ]);
            }

            $result = DB::select(
                "CALL pjl_laporan_gayan(?, ?, ?, ?, ?)",
                [$jenisLap, $tipePromo, $cbgMa, $tglDari, $tglSampai]
            );
            
            if (empty($result)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data Tidak Ditemukan!'
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => $result,
                'tipePromo' => strtoupper($tipePromo),
                'jenisLap' => $jenisLap,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}