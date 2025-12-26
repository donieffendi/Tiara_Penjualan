<?php

namespace App\Http\Controllers\OLain;

use App\Http\Controllers\Controller;

use App\Models\OLain\Disj;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use DB;

class DisjController extends Controller
{

    public function index() {
        return view('olain_disj.index');
    }

    public function getDisj(Request $request)
    {
    
        $disj = DB::SELECT("SELECT * FROM disj where DATE_sub(now(),INTERVAL 15 day)<=TGL_SELESAI or TGL_SELESAI='2001-01-01'");
        
        return Datatables::of($disj)
                ->addIndexColumn()
                ->addColumn('action', function($row) {
					if (Auth::user()->divisi=="programmer" || Auth::user()->divisi=="owner" || Auth::user()->divisi=="sales")
					{   
                        // url untuk delete di index
                        $url = "'".url("disj/delete/" . $row->NO_ID )."'";
                        $proses = "'".url("disj/proses/" . $row->NO_ID )."'";
                        // batas
                        
                        $btnDelete = ' onclick="deleteRow('.$url.')"';
                        $btnProses = ' onclick="prosesDisj('.$proses.')"';
    
                        $btnPrivilege =
                            '
                                    <a hidden class="dropdown-item" href="disj/edit/?idx=' . $row->NO_ID . '&tipx=edit";
                                    <i class="fas fa-edit"></i>
                                        Edit
                                    </a>

                                    <a class="dropdown-item btn btn-update" ' . $btnProses . '>
                                        <i class="fa fa-recycle" aria-hidden="true"></i>
                                        Proses
                                    </a>
                                    
                                    <a hidden class="dropdown-item btn btn-danger" href="disj/cetak/' . $row->NO_ID . '">
                                        <i class="fa fa-print" aria-hidden="true"></i>
                                        Print
                                    </a>
                                    <hr></hr>
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
                                <a hidden class="dropdown-item" href="disj/show/' . $row->NO_ID . '">
                                <i class="fas fa-eye"></i>
                                    Lihat
                                </a>
    
                                ' . $btnPrivilege . '
                            </div>
                        </div>
                        ';
    
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
    }

    public function proses($id)
    {
        DB::table('disj')
            ->where('NO_ID', $id)
            ->update([
                'TG_SMP' => DB::raw('NOW()')
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diproses'
        ]);
    }
}