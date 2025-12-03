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

class RHdhOverMacetController extends Controller
{
    public function report()
    {
        $cbg = Cbg::groupBy('CBG')->get();
        
        // Initialize session variables
        session()->put('filter_cbg', '');
        session()->put('filter_per', '');
        session()->put('filter_jenis', '');

        return view('oreport_hdhovermacet.report')->with([
            'cbg' => $cbg,
            'hasilMacet' => []
        ]);
    }

    public function getHdhOverMacetReport(Request $request)
    {
        $listCBG = Cbg::groupBy('CBG')->get(); // ⬅ hanya untuk dropdown list
        $cbg = $request->cbg; // ⬅ kode cabang untuk SP

        $per = $request->per;
        $yer = $request->yer;
        $jenis = $request->jenis;
        // Set filter values to session
        session()->put('filter_cbg', $request->cbg);
        session()->put('filter_jenis', $request->jenis);
        session()->put('filter_per', $per);
        session()->put('filter_yer', $yer);

        $hasilMacet = [];

        if (!empty($request->cbg)) {
            // Validate jenis tidak boleh kosong
            if (empty($request->jenis)) {
                return redirect()->back()->withErrors(['jenis' => 'Jenis Report Tidak Boleh Kosong']);
            }

            // Get data barang macet
            $hasilMacet = $this->getMacetData($request->cbg, $request->jenis, $request->per, $request->yer);
            
        }

        return view('oreport_hdhovermacet.report')->with([
            'cbg' => $listCBG,
            'hasilMacet' => $hasilMacet
        ]);
    }


    /**
     * Get data barang macet berdasarkan jenis yang dipilih
     * Equivalent dengan logic dalam procedure Tampil
     */
    private function getMacetData($cbg, $jenis, $per, $yer)
    {
        try {
            // Call stored procedure dengan parameter cbg dan jenis
            $result = DB::select('CALL tgz.pjl_rekap_hdh_poin(?, ?, ?, ?)', [$jenis, $cbg, $per, $yer]);
            
            return $result;
        } catch (\Exception $e) {
            Log::error('Error in getMacetData: ' . $e->getMessage());
            return [];
        }
    }
}
