<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use DataTables;
use Auth;
use DB;

class BrgBaruController extends Controller
{
    //
    public function index() {
        return view('master_brg_baru.index');
    }

    public function Tampil(Request $request)
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
            $brgBaru = DB::SELECT("SELECT NO_ID, SUB, KD_BRG, KD_BRG, KET_UK, KET_KEM,
                                        SUPP, HB, D1, D2, D3, LPH_GZ, LPH_SO, LPH_TM, DTR,
                                        KDLAKU, `TYPE`, MIN_TOKO, MAX_TOKO, MIN_GDG, MAX_GDG,
                                        HJ_GZ, HB_GZ, KLK, TARIK, MASA_TARIK, KEM_P, KMP1, KMP2 
                                        from brgchd WHERE NA_FILE = '" . $request->na_file . "'");
        } else {
            $brgBaru = DB::SELECT("SELECT NO_ID, SUB, KD_BRG, KD_BRG, KET_UK, KET_KEM,
                                        SUPP, HB, D1, D2, D3, LPH_GZ, LPH_SO, LPH_TM, DTR,
                                        KDLAKU, `TYPE`, MIN_TOKO, MAX_TOKO, MIN_GDG, MAX_GDG,
                                        HJ_GZ, HB_GZ, KLK, TARIK, MASA_TARIK, KEM_P, KMP1, KMP2 
                                        from brgchd WHERE 1=1 AND FALSE");
        }
        return Datatables::of($brgBaru)
                ->addIndexColumn()
                ->addColumn('action', function($row) {
					if (Auth::user()->divisi=="programmer" || Auth::user()->divisi=="owner" || Auth::user()->divisi=="assistant" || Auth::user()->divisi=="accounting" || Auth::user()->divisi=="pembelian" || Auth::user()->divisi=="penjualan")
					{   
                        // url untuk delete di index
                        $url = "'".url("brg-baru/delete/" . $row->NO_ID )."'";
                        // batas
                        
                        $btnDelete = ' onclick="deleteRow('.$url.')"';
    
                        $btnPrivilege =
                            '
                                    <a class="dropdown-item" href="brg-baru/edit/?idx=' . $row->NO_ID . '&tipx=edit";                                <i class="fas fa-edit"></i>
                                        Edit
                                    </a>
                                    <hr>
                                    </hr>
    
                                    <a hidden class="dropdown-item btn btn-danger" ' . $btnDelete . '>
       
                                        <i class="fa fa-trash" aria-hidden="true"></i>
                                        Delete
                                    </a> 
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
    
                                ' . $btnPrivilege . '
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

        // DB::SELECT("UPDATE brg a, 
        //             (SELECT y.KD_BRG, y.MO, y.KET_KEM, y.KET FROM brgch x, brgchd y WHERE x.NO_BUKTI = y.NO_BUKTI AND x.NA_FILE = '" . $na_file . "') b 
        //             SET a.MO = b.MO, a.KET_KEM = b.KET_KEM, a.KET = '". $na_file . "', a.USERX = '" . Auth::user()->username . "', a.TGLX = now(),
        //             a.KEM_P = b.KEM_P WHERE a.KD_BRG = b.KD_BRG");
        // DB::SELECT("UPDATE brgdt a, 
        //             (SELECT y.KD_BRG, y.KLK, y.KET FROM brgch x, brgchd y WHERE x.NO_BUKTI = y.NO_BUKTI AND x.NA_FILE = '" . $na_file . "') b 
        //             SET a.KLK = b.KLK, a.KETX = '". $na_file . "', a.USERX = '" . Auth::user()->username . "', a.TGLX = now(), WHERE a.KD_BRG = b.KD_BRG");
                    
        return response()->json(['success' => true]);
    }
}
