<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
// ganti 1

// use App\Models\OTransaksi\Ambil;
// use App\Models\OTransaksi\AmbilDetail;
use App\Models\Master\Sup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use DataTables;
use Auth;
use DB;
use Carbon\Carbon;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";
use PHPJasperXML;

// ganti 2
class PostlbktController extends Controller
{
    public function index(Request $request)
    {   
        return view('otransaksi_postlbkt.index');
    }

    // ganti 4
    public function getPostlbkt(Request $request)
    {
        // ganti 5

       if ($request->session()->has('periode')) {
            $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
        } else {
            $periode = '';
        }

        $CBG = Auth::user()->CBG;

        $ambil = DB::SELECT("SELECT NO_ID,CONCAT(LEFT(NOLAP,2),RIGHT(NOLAP,5)) as BUKT,NO_BUKTI,TGL,NOTES,TOTAL_QTY,FLAG,POSTED FROM stockb WHERE CBG='$CBG' and POSTED = 0 AND FLAG='AK' order by NO_BUKTI");


        // ganti 6

        return Datatables::of($ambil)
            ->addIndexColumn()
			->addColumn('cek', function ($row) {
                return
                    '
                    <input type="checkbox" name="cek[]" class="form-control cek" ' . (($row->POSTED == 1) ? "checked" : "") . '  value="' . $row->NO_ID . '" ' . (($row->POSTED == 2) ? "disabled" : "") . '></input>
                    ';

            })

            ->rawColumns(['cek'])
            ->make(true);
    }

    public function posting(Request $request)
    {
        $ids = json_decode($request->ids);

        if ($ids && count($ids) > 0) {
            DB::table('stockb')
                ->whereIn('NO_ID', $ids)
                ->update([
                    'POSTED' => 1
                ]);
        }

        return redirect()->back()->with('status', 'Data berhasil di posting!');
    }

}