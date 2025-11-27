<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Master\Brg;
use App\Models\Master\Sup;
use DataTables;
use Auth;
use DB;

class RkplabelController extends Controller
{

    public function index() {
        return view('otransaksi_rkplabel.index');
    }

    public function getRkplabel(Request $request)
    {
        $tgl = $request->TGL;    
        $cbg = $request->CBG;
        // $cbg = Auth::user()->CBG;
        $tglSQL = date('Y-m-d', strtotime($tgl));
        
        // Panggil SP dengan 3 parameter yang pasti ada
        $sql = DB::select("CALL pjl_komponen_harga(?, ?, ?)", ['REKAP_KOMPONEN_HARIAN', $cbg, $tglSQL]);

        return Datatables::of($sql)
            ->addIndexColumn()
            ->make(true);
    }



}