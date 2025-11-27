<?php

namespace App\Http\Controllers\OReport;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;

class RBarangHappyFreshController extends Controller
{
    public function report()
    {
        session()->put('filter_active_only', true);

        return view('oreport_barang_happy_fresh.report')->with([
            'hasilData' => [],
        ]);
    }

    public function getBarangHappyFreshReport(Request $request)
    {
        $activeOnly = $request->has('active_only') ? (bool)$request->active_only : true;

        session()->put('filter_active_only', $activeOnly);

        $hasilData = [];
        $hasilData = $this->getBarangHappyFreshData($activeOnly);

        return view('oreport_barang_happy_fresh.report')->with([
            'hasilData' => $hasilData,
            'activeOnly' => $activeOnly,
        ]);
    }

    private function getBarangHappyFreshData($activeOnly = true)
    {
        try {
            $cabang = Auth::user()->CBG;
            $filterValue = $activeOnly ? 1 : 0;
            // dd($cabang, $filterValue);

            $query = "CALL {$cabang}.pjl_brg_rekanan(?, ?, 'HF')";

            return DB::select($query, [$cabang, $filterValue]);
        } catch (\Exception $e) {
            Log::error('Error in getBarangHappyFreshData: ' . $e->getMessage());
            return [];
        }
    }

    private function getCurrentCabang()
    {
        return session('current_cabang', 'default_cabang');
    }

    public function jasperBarangHappyFreshReport(Request $request)
    {
        $activeOnly = $request->has('active_only') ? (bool)$request->active_only : true;

        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . '/app/reportc01/phpjasperxml/rbarang_happyfresh.jrxml');

        session()->put('filter_active_only', $activeOnly);

        $data = [];
        $results = $this->getBarangHappyFreshData($activeOnly);

        foreach ($results as $row) {
            $data[] = [
                'StoreNumber' => $row->StoreNumber ?? '',
                'Barcode' => $row->Barcode ?? '',
                'SKU' => $row->SKU ?? '',
                'Brand' => $row->Brand ?? '',
                'Product_Description' => $row->Product_Description ?? '',
                'Category' => $row->Category ?? '',
                'Sub_Category' => $row->Sub_Category ?? '',
                'Normal_Price' => $row->Normal_Price ?? 0,
                'Promo_Price' => $row->Promo_Price ?? 0,
                'Sales_Unit' => $row->Sales_Unit ?? '',
                'Store_Stock' => $row->Store_Stock ?? 0,
                'Consignment' => $row->Consignment ?? '',
                'CABANG' => $this->getCurrentCabang(),
                'FILTER_TYPE' => $activeOnly ? 'Aktif' : 'Semua',
                'REPORT_TYPE' => 'Laporan Barang HappyFresh',
            ];
        }

        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I");
    }

    public function apiGetBarangHappyFreshData(Request $request)
    {
        try {
            $activeOnly = $request->has('active_only') ? (bool)$request->active_only : true;

            $hasil = $this->getBarangHappyFreshData($activeOnly);

            return response()->json([
                'success' => true,
                'data' => $hasil,
                'filter_active_only' => $activeOnly,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in apiGetBarangHappyFreshData: ' . $e->getMessage());
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}