<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
// ganti 1

use Illuminate\Http\Request;
use DataTables;
use Auth;
use DB;
use Carbon\Carbon;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";
use PHPJasperXML;

// ganti 2
class PosthistoController extends Controller
{
    public function index(Request $request)
    {
       return view('otransaksi_posthisto.index');
    }
    public function getPosthisto(Request $request)
    {
        // ganti 5

       if ($request->session()->has('periode')) {
            $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
        } else {
            $periode = '';
        }

        $CBG = Auth::user()->CBG;
        $user = Auth::user()->username;

        $post = DB::SELECT("SELECT NO_ID,NO_BUKTI,FLAG,                                                
                            if(FLAG='UH' or flag='U3','PERUBAHAN HARGA',                   
                            if(FLAG='UK','PERUBAHAN DATA','PENGHAPUSAN BARANG')) as STAT,  
                            CBG,TGL, POSTED FROM histo                                                   
                            where posted='0'                                                       
                            AND cbg='$CBG' 
                            --    AND USRNM='$user'                                         
                            order by no_bukti desc ");
        // ganti 6

        return Datatables::of($post)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                if (Auth::user()->divisi=="programmer" )
				{
                    //CEK POSTED di index dan edit

                    // url untuk delete di index
                    
                    // batas
                } else {
                    $btnPrivilege = '';
                }

                $actionBtn =
                    '
                    <div class="dropdown show" style="text-align: center">
                        <a class="btn btn-secondary dropdown-toggle btn-sm" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-bars"></i>
                        </a>

                        <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">


                            
                        </div>
                    </div>
                    ';

                return $actionBtn;
            })


			->addColumn('cek', function ($row) {
                return
                    '
                    <input type="checkbox" name="cek[]" class="form-control cek" ' . (($row->POSTED == 1) ? "checked" : "") . '  value="' . $row->NO_ID . '" ' . (($row->POSTED == 2) ? "disabled" : "") . '></input>
                    ';

            })

            ->rawColumns(['action','cek'])
            ->make(true);
    }

    public function posting(Request $request)
    {
        if (! $request->isMethod('post')) {
            return response()->json(['error' => 'Method Not Allowed'], 405);
        }

        $ids = $request->input('ids');

        if (empty($ids)) {
            return response()->json(['error' => 'Tidak ada data yang dikirim'], 400);
        }

        // Update field POSTED menjadi 1 untuk semua NO_ID yang dipilih
        DB::table('histo')
            ->whereIn('NO_ID', $ids)
            ->update(['POSTED' => 1]);

        return response()->json(['message' => 'Data berhasil diposting!']);
    }

}
