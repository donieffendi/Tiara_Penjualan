<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use DataTables;
use Auth;
use DB;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";
use PHPJasperXML;

class SogController extends Controller
{

    public function index() {
        return view('master_sog.index');
    }

    public function getSog(Request $request)
    {
        $sog = DB::SELECT("SELECT * from tgz.aotprice where type not in ('','BSN') ORDER BY SUB ASC");

    return Datatables::of($sog)
        ->addIndexColumn()
        
        ->make(true);
    }

    public function updateTanggal(Request $request)
    {
        $tgl = null;

        if (!empty($request->value)) {
            $tgl = \Carbon\Carbon::createFromFormat('d-m-Y', $request->value)->format('Y-m-d');
        }

        DB::table('aotprice')
            ->where('SUB', $request->sub)
            ->update([
                $request->kolom => $tgl,
                'USER_SO' => Auth::user()->username,
                'TG_SMP_SO' => now()
            ]);

        return response()->json(['success' => true]);
    }

}
