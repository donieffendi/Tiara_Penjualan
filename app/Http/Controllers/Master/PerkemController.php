<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
// ganti 1

use App\Models\Master\BrgchDetail;
use App\Models\Master\Brgch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use DataTables;
use Auth;
use DB;
use Carbon\Carbon;

// ganti 2
class PerkemController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */



    public function index()
    {

        // ganti 3
        return view('master_perkem.index');
    }


    // ganti 4
    public function tampil ( Request $request )
    {
        $data = [
            "jenis"     => $request->jenis,
            "type"      => $request->type, // Ambil dari request
            "cbg"       => Auth::user()->CBG, // Ambil dari request
            "na_file"   => $request->na_file, // Ambil dari request
        ];

        $response = Http::asJson()->withHeaders([
            'Content-Type' => 'application/json',
        ])->get('http://192.168.0.2/admin-apf-app/public/api/get-file', $data);
        
        $getdata = json_decode($response->body()); // object
        $datains = $getdata->data[0]; // ambil elemen pertama dari array
        $headerins = $datains->header; // ambil properti 'header'
        $detailins = $datains->detail; // ambil properti 'detail'

        $datainsert = [
            "jenis"     => $request->jenis,
            "type"      => $request->type, // Ambil dari request
            "cbg"       => Auth::user()->CBG, // Ambil dari request
            "na_file"   => $request->na_file, // Ambil dari request
            "data"   => [(object) [
                "header" => $headerins,
                "detail" => $detailins,
            ],
            ],
        ];
        $responseins = Http::asJson()->withHeaders([
            'Content-Type' => 'application/json',
        ])->post(url('api/pengesahan_brg'), $datainsert);
        
        if ($responseins['success']) {
            $perkem = DB::SELECT("SELECT a.NO_ID, a.NO_BUKTI, b.NA_FILE, a.SUB, a.KDBAR, a.NA_BRG, c.KET_UK, a.KET_KEM AS KEM_BR, a.KLK AS KLK_BR, a.KEM_P, c.SUPP AS SUPPLAMA, a.SUPP AS SUPPBARU, 
                            a.TG_SMP, d.KLK AS KLK_LM, a.MO AS MO_BR, c.MO AS MO_LM, a.OUT, c.KET_KEM AS KEM_LM, a.KET_KEM
                            FROM  brgch AS b, brgchd AS a LEFT JOIN brg AS c ON a.KD_BRG = c.KD_BRG LEFT JOIN brgdt AS d ON a.KD_BRG = d.KD_BRG 
                            WHERE a.NO_BUKTI = b.NO_BUKTI AND b.NA_FILE='" . $request->na_file . "'");

        }else {
            $perkem = DB::SELECT("SELECT a.NO_ID, a.NO_BUKTI, b.NA_FILE, a.SUB, a.KDBAR, a.NA_BRG, c.KET_UK, a.KET_KEM AS KEM_BR, a.KLK AS KLK_BR, a.KEM_P, c.SUPP AS SUPPLAMA, a.SUPP AS SUPPBARU, 
                            a.TG_SMP, d.KLK AS KLK_LM, a.MO AS MO_BR, c.MO AS MO_LM, a.OUT, c.KET_KEM AS KEM_LM, a.KET_KEM
                            FROM  brgch AS b, brgchd AS a LEFT JOIN brg AS c ON a.KD_BRG = c.KD_BRG LEFT JOIN brgdt AS d ON a.KD_BRG = d.KD_BRG 
                            WHERE a.NO_BUKTI = b.NO_BUKTI AND FALSE");
        }
        
        return Datatables::of($perkem)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                if (Auth::user()->divisi=="programmer" || Auth::user()->divisi=="owner" || Auth::user()->divisi=="sales")
                {
                    // url untuk delete di index
                    $url = "'".url("brg/delete/" . $row->NO_ID )."'";
                    // batas

                    $btnDelete = ' onclick="deleteRow('.$url.')"';

                    $btnPrivilege =
                        '
                        ';
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
                            <a hidden class="dropdown-item" href="brg/show/' . $row->NO_ID . '">
                            <i class="fas fa-eye"></i>
                                Lihat
                            </a>

                            
                        </div>
                    </div>
                    ';

                return $actionBtn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function proses ( Request $request ){
        $na_file = $request->na_file;

        DB::SELECT("UPDATE brg a, 
                    (SELECT y.KD_BRG, y.MO, y.KET_KEM, y.KET FROM brgch x, brgchd y WHERE x.NO_BUKTI = y.NO_BUKTI AND x.NA_FILE = '" . $na_file . "') b 
                    SET a.MO = b.MO, a.KET_KEM = b.KET_KEM, a.KET = '". $na_file . "', a.USERX = '" . Auth::user()->username . "', a.TGLX = now(),
                    a.KEM_P = b.KEM_P WHERE a.KD_BRG = b.KD_BRG");
        DB::SELECT("UPDATE brgdt a, 
                    (SELECT y.KD_BRG, y.KLK, y.KET FROM brgch x, brgchd y WHERE x.NO_BUKTI = y.NO_BUKTI AND x.NA_FILE = '" . $na_file . "') b 
                    SET a.KLK = b.KLK, a.KETX = '". $na_file . "', a.USERX = '" . Auth::user()->username . "', a.TGLX = now(), WHERE a.KD_BRG = b.KD_BRG");
                    
        return response()->json(['success' => true]);
    }


}
