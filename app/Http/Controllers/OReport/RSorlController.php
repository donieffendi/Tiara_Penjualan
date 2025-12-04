<?php

namespace App\Http\Controllers\OReport;

use App\Http\Controllers\Controller;
use App\Models\Master\Cbg;
use App\Models\Master\Perid;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;

class RSorlController extends Controller
{
    public function report()
    {
        $cbg = Cbg::groupBy('CBG')->get();
        $per = Perid::query()->get();
        
        // Initialize session variables
        session()->put('filter_cbg', '');
        session()->put('filter_per', '');

        return view('oreport_sorl.report')->with([
            'cbg' => $cbg,
            'per' => $per,
            'soRL' => []
        ]);
    }

    public function getSorlReport(Request $request)
    {
        $listCBG = Cbg::groupBy('CBG')->get(); // ⬅ hanya untuk dropdown list
        $listPER = Perid::query()->get();
        $per = $request->per;
        $cbg = $request->cbg; 

        // Set filter values to session
        session()->put('filter_cbg', $request->cbg);
        session()->put('filter_per', $request->per);

        $soRL = [];

        if (!empty($request->cbg)) {
            // Validate kode tidak boleh kosong
            if (empty($request->per)) {
                return redirect()->back()->withErrors(['per' => 'Periode Tidak Boleh Kosong']);
            }

            // Get data barang macet
            $soRL = $this->getSorl($request->cbg, $request->per);
            
        }

        return view('oreport_sorl.report')->with([
            'cbg' => $listCBG,
            'per' => $listPER,
            'soRL' => $soRL
        ]);
    }


    /**
     * Get data barang macet berdasarkan jenis yang dipilih
     * Equivalent dengan logic dalam procedure Tampil
     */
    private function getSorl($cbg, $per)
    {
        try {
            $result = DB::select("SELECT * FROM sorl                            
                                    WHERE per='$per' and st_rl='R'              
                                    ORDER BY kd_brg");
            
            return $result;
        } catch (\Exception $e) {
            Log::error('Error in getStokKD: ' . $e->getMessage());
            return [];
        }
    }
}
