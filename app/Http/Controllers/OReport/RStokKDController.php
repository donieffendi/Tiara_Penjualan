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

class RStokKDController extends Controller
{
    public function report()
    {
        $cbg = Cbg::groupBy('CBG')->get();
        
        // Initialize session variables
        session()->put('filter_cbg', '');
        session()->put('filter_kode', '');

        return view('oreport_stokkd.report')->with([
            'cbg' => $cbg,
            'stokKD' => []
        ]);
    }

    public function getStokKDReport(Request $request)
    {
        $listCBG = Cbg::groupBy('CBG')->get(); // ⬅ hanya untuk dropdown list
        $cbg = $request->cbg; // ⬅ kode cabang untuk SP

        $kode = $request->kode;
        // Set filter values to session
        session()->put('filter_cbg', $request->cbg);
        session()->put('filter_kode', $request->kode);

        $stokKD = [];

        if (!empty($request->cbg)) {
            // Validate kode tidak boleh kosong
            if (empty($request->kode)) {
                return redirect()->back()->withErrors(['kode' => 'Kode Report Tidak Boleh Kosong']);
            }

            // Get data barang macet
            $stokKD = $this->getStokKD($request->cbg, $request->kode);
            
        }

        return view('oreport_stokkd.report')->with([
            'cbg' => $listCBG,
            'stokKD' => $stokKD
        ]);
    }


    /**
     * Get data barang macet berdasarkan jenis yang dipilih
     * Equivalent dengan logic dalam procedure Tampil
     */
    private function getStokKD($cbg, $kode)
    {
        try {
            // Filter data barang berdasarkan kode
            $flagkode = '';
            if ($kode == '3') {
                $flagkode = " AND b.KDLAKU='4'";
            } else if ($kode == '2') {
                $flagkode = " AND b.KDLAKU in ('0','1')";
            }
            
            $result = DB::select("SELECT a.SUB, a.KD_BRG, a.NA_BRG, a.KET_UK, a.KET_KEM, a.BARCODE, 
                                         b.AK00, b.GAK00, b.AK00+b.GAK00 as SALDO, b.TD_OD, b.KDLAKU
                                  FROM brg a, brgdt b 
                                  WHERE a.KD_BRG=b.KD_BRG and b.YER=year(now()) $flagkode
                                  ORDER BY a.KD_BRG");
            
            return $result;
        } catch (\Exception $e) {
            Log::error('Error in getStokKD: ' . $e->getMessage());
            return [];
        }
    }
}
