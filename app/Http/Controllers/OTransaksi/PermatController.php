<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Master\Brg;
use App\Models\Master\Sup;
use DataTables;
use Auth;
use DB;

class PermatController extends Controller
{

    public function index() {
        return view('otransaksi_permat.index');
    }

    public function getPermat(Request $request)
    {
        $sub = $request->sub;
        $permat = DB::SELECT("SELECT  NO_ID, KD_BRG, NA_BRG, KET_UK, KET_KEM, TARIK, MASA_EXP, TARIK_TIPE from brg where sub='$sub' order by kd_brg");

    return DataTables::of($permat)
        ->addIndexColumn()
        ->addColumn('action', function($row) {
            $btnPrivilege = '';
            if (in_array(Auth::user()->divisi, ["programmer","owner","sales"])) {   
                $url = "'".url("permat/delete/" . $row->NO_ID )."'";
                $btnDelete = ' onclick="deleteRow('.$url.')"';
                
            }

            return '
            <div class="dropdown show" style="text-align: center">
                <a class="btn btn-secondary dropdown-toggle btn-sm" href="#" data-toggle="dropdown">
                    <i class="fas fa-bars"></i>
                </a>
                <div class="dropdown-menu">
                    <a hidden class="dropdown-item" href="permat/show/' . $row->NO_ID . '">
                        <i class="fas fa-eye"></i> Lihat
                    </a>
                    '.$btnPrivilege.'
                </div>
            </div>';
        })
        ->rawColumns(['action'])
        ->toJson();
    }

}