<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Master\Brg;
use App\Models\Master\Sup;
use DataTables;
use Auth;
use DB;

class BarangKasirTdController extends Controller
{

    public function index() {
        return view('master_usulan_barang_kasir_td.index');
    }

    public function getUsulanBrgTd(Request $request)
    {
    
        // $sql = DB::SELECT("SELECT 'NO_ID','SUB','SUB2','KDBAR','KD_BRG','NA_BRG','SUPP','KET_UK','KET_KEM' FROM masks");

        $sql = DB::table('masks')
                    ->select('NO_ID','SUB','SUB2','KDBAR','KD_BRG','NA_BRG','SUPP','KET_UK','KET_KEM','HB','CEK', 'JTD') 
                    ->where('SUB', '=', $request->sub )
                    ->get();

        // \Log::info('sql : ', [$sql]);
        return Datatables::of($sql)
                ->addIndexColumn()
                
                ->rawColumns(['action'])
                ->make(true);
    }

    public function store(Request $request)
    {


    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    

    public function edit(Request $request)
    {
		
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */


    public function update(Request $request)
    {

		
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    public function proses (Request $request)
    {

        $items = $request->items; 

        foreach ($items as $item) {
            DB::table('masks')
                ->where('KD_BRG', $item['KD_BRG'])
                ->update([
                    'JTD' => $item['JTD'],
                ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Proses berhasil dijalankan'
        ]);

    }

    // public function destroy(Request $request , Brg $brg)
    // {

    //     // ganti 23
    //     $deleteBrg = Brg::find($brg->NO_ID);

    //     // ganti 24

    //     $deleteBrg->delete();

    //     // ganti 
    //     return redirect('/brg')->with('status', 'Data berhasil dihapus');
    // }



}