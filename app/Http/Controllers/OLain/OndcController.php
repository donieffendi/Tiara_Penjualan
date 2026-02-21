<?php

namespace App\Http\Controllers\OLain;

use App\Http\Controllers\Controller;

use App\Models\OLain\Jackh;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Master\Brg;
use App\Models\Master\Sup;
use DataTables;
use Auth;
use DB;

class OndcController extends Controller
{

    public function index() {
        return view('olain_ondc.index');
    }

    public function getOndc(Request $request)
    {
        $ondc = $request->ondc;
        $sub1x = $request->sub1x;
        $sub2x = $request->sub2x;
        $sup1x = $request->sup1x;
        $sup2x = $request->sup2x;

        $ondcx = DB::SELECT("CALL sim_ambil_brg_ondc('MASTER','$ondc','$sub1x','$sub2x','$sup1x','$sup2x')");

        // dd($ondcx, $ondc, $sub1x, $sub2x, $sup1x, $sup2x);
        
        return Datatables::of($ondcx)
                ->addIndexColumn()
                ->make(true);
    }
    public function updateOndc(Request $request)
    {
        DB::table('brg')
            ->where('KD_BRG', $request->kd_brg)
            ->update([
                'ON_DC' => $request->on_dc
            ]);

        return response()->json(['success' => true]);
    }
}