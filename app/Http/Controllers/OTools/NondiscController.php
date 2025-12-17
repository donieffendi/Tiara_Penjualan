<?php

namespace App\Http\Controllers\OTools;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\OTools\Poin;
use DataTables;
use Auth;
use DB;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";
use PHPJasperXML;

class NondiscController extends Controller
{

    public function index() {
        return view('otools_nondisc.index');
    }

    public function lookupBrg(Request $request)
    {
        $kdBrg = $request->kd_brg;

        $brg = DB::table('brg')
            ->select('NA_BRG')
            ->where('KD_BRG', $kdBrg)
            ->first();

        if ($brg) {
            return response()->json([
                'success' => true,
                'na_brg'  => $brg->NA_BRG
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Barang tidak ditemukan'
        ], 404);
    }

    public function getNondisc(Request $request)
    {   
        $nondisc = DB::select("
                                SELECT 
                                    a.NO_ID,
                                    p.NO_ID AS poin_id,
                                    a.KD_BRG,
                                    a.NA_BRG,
                                    a.KET_UK,
                                    a.KET_KEM
                                FROM brg a
                                INNER JOIN poin p 
                                    ON p.TYPE = a.KD_BRG
                                AND p.FLAG = 'CR'
                            ");

        return Datatables::of($nondisc)
                    ->addIndexColumn()
                    ->addColumn('action', function($row) {
                        if (Auth::user()->divisi=="programmer" || Auth::user()->divisi=="owner" || Auth::user()->divisi=="sales")
                        {   

                            // url untuk delete di index
                            $url = "'".url("nondisc/delete/" . $row->poin_id )."'";
                            // batas
                            
                            $btnDelete = ' onclick="deleteRow('.$url.')"';
        
                            $btnPrivilege =
                                '
                                        <a class="dropdown-item btn btn-danger" ' . $btnDelete . '>
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
                                    <a hidden class="dropdown-item" href="brg-jasa/show/' . $row->NO_ID . '">
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


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    public function store(Request $request)
    {
        $request->validate([
            'kd_brg' => 'required|string'
        ]);

        $kdBrg = $request->kd_brg;
        $user  = Auth::user()->username;

        // ambil semua cabang MA & CB
        $cabangs = DB::table('toko')
            ->whereIn('STA', ['MA', 'CB'])
            ->pluck('KODE');

        DB::beginTransaction();
        try {
            foreach ($cabangs as $cbg) {

                // cek sudah ada atau belum
                $ada = DB::table('poin')
                    ->where('FLAG', 'CR')
                    ->where('TYPE', $kdBrg)
                    ->where('CBG', $cbg) // kalau ada kolom CBG
                    ->exists();

                if (!$ada) {
                    DB::table('poin')->insert([
                        'FLAG'   => 'CR',
                        'TYPE'   => $kdBrg,
                        'CBG'    => $cbg,      // sesuaikan kalau ada
                        'USRNM'  => $user,
                        'TG_SMP' => now(),
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Berhasil ditambahkan'
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // ganti 22

    public function destroy($id)
    {
        Poin::where('NO_ID', $id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data nondisc berhasil dihapus'
        ]);
    }
}
