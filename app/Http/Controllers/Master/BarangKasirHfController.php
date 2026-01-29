<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Master\Brg;
use App\Models\Master\Sup;
use App\Models\Master\Sub;
use DataTables;
use Auth;
use DB;

class BarangKasirHfController extends Controller
{

    public function index()
    {
        return view('master_usulan_barang_kasir_hf.index');
    }

    public function getUsulanBrgHf(Request $request)
    {
        try {
            // $sql = DB::SELECT("SELECT 'NO_ID','SUB','SUB2','KDBAR','KD_BRG','NA_BRG','SUPP','KET_UK','KET_KEM' FROM masks");

            $sql = DB::table('masks')
                ->select('NO_ID', 'SUB', 'SUB2', 'KDBAR', 'KD_BRG', 'NA_BRG', 'SUPP', 'KET_UK', 'KET_KEM', 'HB', 'JTD')
                ->where('SUB', '=', $request->sub)
                ->get();

            // \Log::info('sql : ', [$sql]);
            return Datatables::of($sql)
                ->addIndexColumn()

                ->rawColumns(['action'])
                ->make(true);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error fetching data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request) {}

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */



    public function edit(Request $request) {}

    public function getSub(Request $request)
    {
        try {
            $query = Sub::select('SUB', 'KELOMPOK')
                ->orderBy('SUB', 'ASC');

            if ($request->has('q')) {
                $search = $request->q;
                $query->where(function ($q) use ($search) {
                    $q->where('SUB', 'like', '%' . $search . '%')
                        ->orWhere('KELOMPOK', 'like', '%' . $search . '%');
                });
            }

            $subs = $query->get();

            return response()->json($subs);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error fetching sub data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */


    public function update(Request $request) {}

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    public function proses(Request $request)
    {
        try {
            DB::beginTransaction();

            $items = $request->items;

            if (empty($items)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tidak ada data untuk diproses'
                ], 400);
            }

            $updated = 0;
            foreach ($items as $item) {
                $affected = DB::table('masks')
                    ->where('KD_BRG', $item['KD_BRG'])
                    ->update([
                        'JTD' => $item['JTD'],
                    ]);
                $updated += $affected;
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Proses berhasil! ' . $updated . ' item yang dicentang telah diupdate (JTD = 1).'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Proses gagal: ' . $e->getMessage()
            ], 500);
        }
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
