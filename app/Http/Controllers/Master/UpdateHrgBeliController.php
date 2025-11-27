<?php
namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\SimBrg;
use Auth;
use DataTables;
use DB;
use Illuminate\Http\Request;

class UpdateHrgBeliController extends Controller
{

    public function getHargaAwal(Request $request)
    {
        $kode = $request->KD_BRG;

        $data = DB::table('brgdt')
            ->select('HB')
            ->where('KD_BRG', $kode)
            ->first();

        if ($data) {
            return response()->json(['HB' => $data->HB]);
        } else {
            return response()->json(['HB' => 0]);
        }
    }
    public function index()
    {   
        $CBG = DB::SELECT("SELECT KODE FROM toko WHERE STA IN ('MA','CB','DC') ORDER BY NO_ID ASC");

        return view('master_update_harga_beli.index')->with(['CBG' => $CBG]);
    }

    public function getUpdateHrgBeli(Request $request)
    {

        $cbg   = Auth::user()->CBG;
        $query = DB::select("SELECT * FROM sim_brg WHERE FLAG='HB' AND CBG='$cbg' ORDER BY TG_SMP DESC;");

        return Datatables::of($query)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                if (Auth::user()->divisi == "programmer") {

                    $btnPrivilege =
                    '
                                    <a class="dropdown-item" href="update-hrg-beli/edit/?idx=' . $row->NO_ID . '&tipx=edit";
                                    <i class="fas fa-edit"></i>
                                        Edit
                                    </a>
                                    <hr></hr>
                                    <a class="dropdown-item btn btn-danger" onclick="return confirm(&quot; Apakah anda yakin ingin hapus? &quot;)" href="update-hrg-beli/delete/' . $row->NO_ID . '">
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
                                <a hidden class="dropdown-item" href="brg/show/' . $row->NO_ID . '">
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

    // public function store(Request $request)
    // {

    //     $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];

    //     $bulan = str_pad(session()->get('periode')['bulan'], 2, '0', STR_PAD_LEFT);
    //     $tahun = session()->get('periode')['tahun'];

    //     $query = DB::table('sim_brg')
    //         ->select('KD_BRG')
    //         ->where('KD_BRG', 'like', 'UHB' . $tahun . $bulan . '%')
    //         ->orderByDesc('KD_BRG')
    //         ->first();

    //     if ($query) {
    //         $lastNumber = intval(substr($query->KODES, -3));
    //         $newNumber  = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
    //     } else {
    //         $newNumber = '001';
    //     }

    //     $no_bukti = 'UHB' . $tahun . $bulan . $newNumber;
        
    //     $user = Auth::user()->username;
    //     // Insert Header

    //     // ganti 10

    //     $cbg    = trim($request->input('CBG'));
    //     $kdBrg  = trim($request->input('KD_BRG'));
    //     $hbBaru = (float) str_replace(',', '', $request->input('HB_BARU'));
    //     $hbLama = (float) str_replace(',', '', $request->input('HB_LAMA'));

    //     // 1. UPDATE harga beli di {cbg}.brgdt
    //     DB::table('brgdt')
    //         ->where('KD_BRG', $kdBrg)
    //         ->update([
    //             'HB' => $hbBaru,
    //         ]);

    //     // 2. INSERT log ke tgz.sim_brg
    //     SimBrg::create([
    //         'KD_BRG'  => $kdBrg,
    //         'NA_BRG'  => $request->input('NA_BRG', ''),
    //         'KET_UK'  => $request->input('KET_UK', ''),
    //         'KET_KEM' => $request->input('KET_KEM', ''),
    //         'CRUD'    => 'UPDATE',
    //         'KET'     => $hbLama . ' -> ' . $hbBaru,
    //         'FLAG'    => 'HB',
    //         'CBG'     => $cbg,
    //         'TG_SMP'  => now(),
    //         'USERX'   => $user,
    //     ]);
    //     //  ganti 11
    //     return redirect('/update-hrg-beli')->with('statusInsert', 'Data baru berhasil ditambahkan');

    // }

    public function store(Request $request)
    {
        $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
        $bulan = str_pad(session()->get('periode')['bulan'], 2, '0', STR_PAD_LEFT);
        $tahun = session()->get('periode')['tahun'];

        // 🔹 Ambil nomor urut terakhir
        $query = DB::table('sim_brg')
            ->select('KD_BRG')
            ->where('KD_BRG', 'like', 'UHB' . $tahun . $bulan . '%')
            ->orderByDesc('KD_BRG')
            ->first();

        if ($query) {
            $lastNumber = intval(substr($query->KD_BRG, -3));
            $newNumber  = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '001';
        }

        $no_bukti = 'UHB' . $tahun . $bulan . $newNumber;
        $user = Auth::user()->username;

        // 🔸 Data dari input
        $cbg    = trim($request->input('CBG'));
        $kdBrg  = trim($request->input('KD_BRG'));
        $hbBaru = (float) str_replace(',', '', $request->input('HB_BARU'));

        // 🔹 Ambil data barang dari tabel brg (nama, ukuran, kemasan)
        $barang = DB::table('brg')
            ->select('NA_BRG', 'KET_UK', 'KET_KEM')
            ->where('KD_BRG', $kdBrg)
            ->first();

        // 🔹 Ambil harga lama dari tabel brgdt
        $brgdt = DB::table('brgdt')
            ->select('HB')
            ->where('KD_BRG', $kdBrg)
            ->first();

        $hbLama = (float) ($brgdt->HB ?? 0);

        // 🔹 Update harga di brgdt
        DB::table('brgdt')
            ->where('KD_BRG', $kdBrg)
            ->update(['HB' => $hbBaru]);

        // 🔹 Insert ke sim_brg (log perubahan)
        SimBrg::create([
            'KD_BRG'  => $kdBrg,
            'NA_BRG'  => $barang->NA_BRG ?? '',
            'KET_UK'  => $barang->KET_UK ?? '',
            'KET_KEM' => $barang->KET_KEM ?? '',
            'CRUD'    => 'UPDATE',
            'KET'     => number_format($hbLama, 0, '.', ',') . ' --> ' . number_format($hbBaru, 0, '.', ','),
            'FLAG'    => 'HB',
            'CBG'     => $cbg,
            'TG_SMP'  => now(),
            'USERX'   => $user,
        ]);

        return redirect('/update-hrg-beli')->with('statusInsert', 'Data baru berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    // ganti 15

    public function edit(Request $request, SimBrg $UpdateHrgBeli)
    {

        // ganti 16
        $tipx = $request->tipx;

        $idx = $request->idx;

        $cbg = Auth::user()->CBG;

        if ($idx == '0' && $tipx == 'undo') {
            $tipx = 'top';

        }

        if ($tipx == 'search') {

            $kodex = $request->kodex;

            $bingco = DB::SELECT("SELECT NO_ID, KD_BRG from sim_brg
		                 where KD_BRG = '$kodex'
		                 ORDER BY KD_BRG ASC  LIMIT 1");

            if (! empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = 0;
            }

        }

        if ($tipx == 'top') {

            $bingco = DB::SELECT("SELECT NO_ID, KD_BRG from sim_brg
		                 ORDER BY KD_BRG ASC  LIMIT 1");

            if (! empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = 0;
            }

        }

        if ($tipx == 'prev') {

            $kodex = $request->kodex;

            $bingco = DB::SELECT("SELECT NO_ID, KD_BRG from sim_brg
		             where KD_BRG <
					 '$kodex' ORDER BY KD_BRG DESC LIMIT 1");

            if (! empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = $idx;
            }

        }
        if ($tipx == 'next') {

            $kodex = $request->kodex;

            $bingco = DB::SELECT("SELECT NO_ID, KD_BRG from sim_brg
		             where KD_BRG >
					 '$kodex' ORDER BY KD_BRG ASC LIMIT 1");

            if (! empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = $idx;
            }

        }

        if ($tipx == 'bottom') {

            $bingco = DB::SELECT("SELECT NO_ID, KD_BRG from sim_brg
		              ORDER BY KD_BRG DESC  LIMIT 1");

            if (! empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = 0;
            }

        }

        if ($tipx == 'undo' || $tipx == 'search') {

            $tipx = 'edit';

        }

       if ($idx != 0) {
            $UpdateHrgBeli = SimBrg::where('NO_ID', $idx)->first();
        } else {
            $UpdateHrgBeli = new SimBrg;
        }

        $data = [
            'header' => $UpdateHrgBeli,
        ];

        return view('master_update_harga_beli.index', $data)->with(['tipx' => $tipx, 'idx' => $idx]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    // ganti 22

    public function destroy(Request $request, SimBrg $UpdateHrgBeli)
    {

        // ganti 23
        $deletex = SimBrg::find($brg->NO_ID);

        // ganti 24

        $deletex->delete();

        // ganti
        return redirect('/update-hrg-beli')->with('status', 'Data berhasil dihapus');
    }

}