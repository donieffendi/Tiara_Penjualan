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

        $ondc = DB::SELECT("CALL sim_ambil_brg_ondc('MASTER','$ondc','$sub1x','$sub2x','$sup1x','$sup2x')");
        
        return Datatables::of($ondc)
                ->addIndexColumn()
                ->make(true);
    }
}