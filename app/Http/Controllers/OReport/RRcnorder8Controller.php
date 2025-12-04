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

class RRcnorder8Controller extends Controller
{
    public function report()
    {   
        $cbg = Cbg::groupBy('CBG')->get();

        // Initialize session variables
        session()->put('filter_cbg', '');
        session()->put('filter_sub', '');
        session()->put('filter_ulang', '');
        session()->put('filter_nobukti', '');

        return view('oreport_rcnorder8.report')->with([
            'cbg' => $cbg,
            'rcnorder8' => []
        ]);
    }

    public function getRcnorder8Report(Request $request)
    {
        $listCBG = Cbg::groupBy('CBG')->get(); // ⬅ hanya untuk dropdown list
        $cbg = $request->cbg;
        $sub = $request->sub;
        $ulang = $request->ulang;
        $nobukti = $request->nobukti;

        // Set filter values to session
        session()->put('filter_cbg', $request->cbg);
        session()->put('filter_sub', $request->sub);
        session()->put('filter_ulang', $request->ulang);
        session()->put('filter_nobukti', $request->nobukti);

        $rcnorder8 = [];
        // dd($request->all());
        if (!empty($request->cbg)) {
            // Validate kode tidak boleh kosong
            if (empty($request->sub)) {
                return redirect()->back()->withErrors(['sub' => 'Sub Tidak Boleh Kosong']);
            }

            if ($ulang == '1') {
                if (empty($request->nobukti)) {
                    return redirect()->back()->withErrors(['nobukti' => 'No Bukti Tidak Boleh Kosong']);
                }
            }
            // Get data barang macet
            $rcnorder8 = $this->getRcnorder8($request->cbg, $request->sub, $request->ulang, $request->nobukti);
        }

        return view('oreport_rcnorder8.report')->with([
            'cbg' => $listCBG,
            'rcnorder8' => $rcnorder8
        ]);
    }

    /**
     * Get data barang macet berdasarkan jenis yang dipilih
     * Equivalent dengan logic dalam procedure Tampil
     */
    private function getRcnorder8($cbg, $sub, $ulang, $nobukti)
    {
        try {

            if ($ulang == 0) {
                $result = DB::select('CALL gd_koreksi_tgl_produksi (?, ?, ?, ?, ?)', ['REPORT_STOK_NOL_KD8', '', $cbg, $sub, '']);
            } else {
                $result = DB::select('CALL gd_koreksi_tgl_produksi (?, ?, ?, ?, ?)', ['REPORT_STOK_NOL_KD8', $nobukti, $cbg, $sub, '']);
            }
            
            return $result;
        } catch (\Exception $e) {
            Log::error('Error in getRcnorder8: ' . $e->getMessage());
            return [];
        }
    }
}
