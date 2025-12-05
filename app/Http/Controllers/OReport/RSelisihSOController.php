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

class RSelisihSOController extends Controller
{
    public function report()
    {
        $cbg = Cbg::groupBy('CBG')->get();
        
        // Initialize session variables
        session()->put('filter_cbg', '');
        session()->put('filter_tglDari', date("d-m-Y"));
		session()->put('filter_tglSampai', date("d-m-Y"));

        return view('oreport_selisihso.report')->with([
            'cbg' => $cbg,
            'selisihSO' => []
        ]);
    }

    public function getSelisihSOReport(Request $request)
    {
        $listCBG = Cbg::groupBy('CBG')->get(); // ⬅ hanya untuk dropdown list
        $cbg = $request->cbg; // ⬅ kode cabang untuk SP
        $tglDrD = date("Y-m-d", strtotime($request->tglDr));
        $tglSmpD = date("Y-m-d", strtotime($request->tglSmp)); 

        // Set filter values to session
        session()->put('filter_cbg', $request->cbg);
        session()->put('filter_tglDari', $request->tglDr);
        session()->put('filter_tglSampai', $request->tglSmp);

        $selisihSO = [];
        // dd($request->all());
        if (!empty($request->cbg)) {
            // Validate kode tidak boleh kosong
            if (empty($request->tglDr) || empty($request->tglSmp)) {
                return redirect()->back()->withErrors(['kode' => 'Tanggal Tidak Boleh Kosong']);
            }

            // Get data barang macet
            $selisihSO = $this->getSelisihSO($request->cbg, $tglDrD, $tglSmpD);
            
        }

        return view('oreport_selisihso.report')->with([
            'cbg' => $listCBG,
            'selisihSO' => $selisihSO
        ]);
    }


    /**
     * Get data barang macet berdasarkan jenis yang dipilih
     * Equivalent dengan logic dalam procedure Tampil
     */
    private function getSelisihSO($cbg, $tglDr, $tglSmp)
    {
        try {
            $kode = Auth::user()->divisi;
            // Filter data barang berdasarkan kode
            $flagkode = '';
            if ($kode == 'penjualan'|| $kode == 'programmer') {
                $flagkode = " AND A.FLAG in ('AO')";
            } else if ($kode == 'gudang') {
                $flagkode = " AND A.FLAG in ('GS')";
            }
            
            $result = DB::select("SELECT CONCAT(left(A.NOLAP,2),right(A.NOLAP,5)) as BUKT, A.TGL, A.NO_BUKTI, A.NOLAP,
                                        B.KD_BRG, B.NA_BRG, B.KET_UK, B.KET_KEM, C.BARCODE, B.HJ, B.HB, B.SALDO, B.QTY, b.RIIL,
                                        if(B.QTY<0, '-', '+') JNS, concat(right(B.KD_BRG,4),'-',left(B.KD_BRG,3)) as ITEMSUB,
                                        if(B.JENIS_SO=0,'',B.JENIS_SO) JENIS_SO, B.KET, B.NOTAG
                                  FROM stockbz A, stockbzd B
                                  LEFT JOIN brg C ON C.KD_BRG=B.KD_BRG
                                  WHERE	A.NO_BUKTI=B.NO_BUKTI 
                                  $flagkode 
                                  AND B.QTY<>0
                                  AND A.TGL between '$tglDr' and '$tglSmp'
                                  ORDER BY A.TGL, A.NO_BUKTI");
            
            return $result;
        } catch (\Exception $e) {
            Log::error('Error in getStokKD: ' . $e->getMessage());
            return [];
        }
    }
}
