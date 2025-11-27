<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use DataTables;
use Auth;
use DB;
use Carbon\Carbon;

class PostingSLSController extends Controller
{
    public function index(Request $request)
    {
        if(strtolower($request->JNS)=='po')
        {
            $judul = "Purchase Order";
        }
        else if(strtolower($request->JNS)=='so')
        {
            $judul = "Sales Order";
        }
        return view('oposting_sls.postsls')->with(['judul' => $judul, 'jenis' => strtolower($request->JNS)]);
    }

    public function getPostSLS(Request $request)
    {
        if ($request->session()->has('periode')) {
            $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
        } else {
            $periode = '';
        }

        $querysls = DB::table($request->JNS)->select('*')->where('SLS', 0)->orderBy('NO_BUKTI', 'ASC')->get();

        return Datatables::of($querysls)
            ->addIndexColumn()
            ->addColumn('cek', function ($row) {
                return
                    '
                    <input type="checkbox" name="cek[]" class="form-control cek" ' . (($row->SLS == 1) ? "checked" : "") . '  value="' . $row->NO_ID . '" ' . (($row->SLS == 1) ? "disabled" : "") . '></input>
                    ';
            })
            ->rawColumns(['cek'])
            ->make(true);
    }

    public function posting(Request $request)
    {
        $CEK = $request->input('cek');

        $hasil = "";

        if ($CEK) {
            foreach ($CEK as $key => $value) {
                DB::SELECT("UPDATE ".$request->input('jenis')." 
                            SET SLS=1 
                            WHERE NO_ID=" . $CEK[$key] . ";");
            }
        }
        else
        {
            $hasil = $hasil ."Tidak ada Transaksi yang dipilih! ; ";
        }

        if($hasil!='')
        {
            return redirect('/postingsls/index?JNS='.$request->input('jenis'))->with('status', 'Proses Posting selesai..')->with('gagal', $hasil);
        }
        else
        {
            return redirect('/postingsls/index?JNS='.$request->input('jenis'))->with('status', 'Proses Posting selesai..');
        }
    }

}
