<?php
namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\SupSewa;
use Auth;
use DataTables;
use DB;
use Illuminate\Http\Request;

class SupSewaController extends Controller
{

    public function index()
    {
        return view('master_supplier_sewa.index');
    }

    public function getSupSewa(Request $request)
    {
        $brg = DB::select("SELECT * FROM supstand order by KODES");

        return Datatables::of($brg)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                if (Auth::user()->divisi == "programmer") {
                    $btnPrivilege =
                    '
                                    <a class="dropdown-item" href="sup-sewa/edit/?idx=' . $row->NO_ID . '&tipx=edit";
                                    <i class="fas fa-edit"></i>
                                        Edit
                                    </a>
                                    <hr></hr>
                                    <a class="dropdown-item btn btn-danger" onclick="return confirm(&quot; Apakah anda yakin ingin hapus? &quot;)" href="sup-sewa/delete/' . $row->NO_ID . '">
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
                                <a hidden class="dropdown-item" href="sup-sewa/show/' . $row->NO_ID . '">
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

    public function store(Request $request)
    {
        $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];

        $bulan = str_pad(session()->get('periode')['bulan'], 2, '0', STR_PAD_LEFT);
        $tahun = session()->get('periode')['tahun'];

        $query = DB::table('supstand')
            ->select('KODES')
            ->where('KODES', 'like', 'HR' . $tahun . $bulan . '%')
            ->orderByDesc('KODES')
            ->first();

        if ($query) {
            $lastNumber = intval(substr($query->KODES, -3));
            $newNumber  = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '001';
        }

        $no_bukti = 'SUP-SW' . $tahun . $bulan . $newNumber;

        // Insert Header

        // ganti 10

        $SupSewa = SupSewa::create(
            [
                'KODES'     => $no_bukti,
                'NAMAS'     => ($request['NAMAS'] == null) ? "" : $request['NAMAS'],
                'KD_DISTRIBUTOR'   => ($request['KD_DISTRIBUTOR'] == null) ? "" : $request['KD_DISTRIBUTOR'],
                // 'NAMA_DIST' => ($request['NAMA_DIST'] == null) ? "" : $request['NAMA_DIST'],
                'KTP'       => ($request['KTP'] == null) ? "" : $request['KTP'],
                'PRODUK'    => ($request['PRODUK'] == null) ? "" : $request['PRODUK'],
                'AL_PRSH'   => ($request['AL_PRSH'] == null) ? "" : $request['AL_PRSH'],
                'AL_PRSH2'  => ($request['AL_PRSH2'] == null) ? "" : $request['AL_PRSH2'],
                'KOTA'      => ($request['KOTA'] == null) ? "" : $request['KOTA'],
                'NO_TELP'   => ($request['NO_TELP'] == null) ? "" : $request['NO_TELP'],
                'S_PJK'   => ($request['S_PJK'] == null) ? "" : $request['S_PJK'],
                'NPWP'      => ($request['NPWP'] == null) ? "" : $request['NPWP'],
                'CARA_BYR'  => ($request['CARA_BYR'] == null) ? "" : $request['CARA_BYR'],
                'CARA_BYR2' => ($request['CARA_BYR2'] == null) ? "" : $request['CARA_BYR2'],
                'KET'       => ($request['KET'] == null) ? "" : $request['KET'],
                'EMAIL'     => ($request['EMAIL'] == null) ? "" : $request['EMAIL'],
            ]
        );

        //  ganti 11
        return redirect('/sup-sewa')->with('statusInsert', 'Data baru berhasil ditambahkan');

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    // ganti 15

    public function edit(Request $request, SupSewa $SupSewa)
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

            $bingco = DB::SELECT("SELECT NO_ID, KODES from supstand
		                 where KODES = '$kodex'
		                 ORDER BY KODES ASC  LIMIT 1");

            if (! empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = 0;
            }

        }

        if ($tipx == 'top') {

            $bingco = DB::SELECT("SELECT NO_ID, KODES from supstand
		                 ORDER BY KODES ASC  LIMIT 1");

            if (! empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = 0;
            }

        }

        if ($tipx == 'prev') {

            $kodex = $request->kodex;

            $bingco = DB::SELECT("SELECT NO_ID, KODES from supstand
		             where KODES <
					 '$kodex' ORDER BY KODES DESC LIMIT 1");

            if (! empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = $idx;
            }

        }
        if ($tipx == 'next') {

            $kodex = $request->kodex;

            $bingco = DB::SELECT("SELECT NO_ID, KODES from supstand
		             where KODES >
					 '$kodex' ORDER BY KODES ASC LIMIT 1");

            if (! empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = $idx;
            }

        }

        if ($tipx == 'bottom') {

            $bingco = DB::SELECT("SELECT NO_ID, KODES from supstand
		              ORDER BY KODES DESC  LIMIT 1");

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
            $sup_sewa = SupSewa::where('NO_ID', $idx)->first();
        } else {
            $sup_sewa = new SupSewa;
        }

        $data = [
            'header' => $sup_sewa,
        ];
        return view('master_supplier_sewa.edit', $data)->with(['tipx' => $tipx, 'idx' => $idx]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    public function update(Request $request, SupSewa $SupSewa)
    {
        // ganti 20

        $tipx = 'edit';
        $idx  = $request->idx;

        $SupSewa->update(
            [
                'NAMAS'     => ($request['NAMAS'] == null) ? "" : $request['NAMAS'],
                'KD_DIST'   => ($request['KD_DIST'] == null) ? "" : $request['KD_DIST'],
                'NAMA_DIST' => ($request['NAMA_DIST'] == null) ? "" : $request['NAMA_DIST'],
                'KTP'       => ($request['KTP'] == null) ? "" : $request['KTP'],
                'PRODUK'    => ($request['PRODUK'] == null) ? "" : $request['PRODUK'],
                'AL_PRSH'   => ($request['AL_PRSH'] == null) ? "" : $request['AL_PRSH'],
                'AL_PRSH2'  => ($request['AL_PRSH2'] == null) ? "" : $request['AL_PRSH2'],
                'KOTA'      => ($request['KOTA'] == null) ? "" : $request['KOTA'],
                'NO_TELP'   => ($request['NO_TELP'] == null) ? "" : $request['NO_TELP'],
                'STS_PJK'   => ($request['STS_PJK'] == null) ? "" : $request['STS_PJK'],
                'NPWP'      => ($request['NPWP'] == null) ? "" : $request['NPWP'],
                'CARA_BYR'  => ($request['CARA_BYR'] == null) ? "" : $request['CARA_BYR'],
                'CARA_BYR2' => ($request['CARA_BYR2'] == null) ? "" : $request['CARA_BYR2'],
                'KET'       => ($request['KET'] == null) ? "" : $request['KET'],
                'EMAIL'     => ($request['EMAIL'] == null) ? "" : $request['EMAIL']]
        );

        return redirect('/sup-sewa')->with('status', 'Data berhasil diupdate');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    // ganti 22

    public function destroy(Request $request, SupSewa $SupSewa)
    {

        // ganti 23
        $deleteSupSewa = SupSewa::find($SupSewa->NO_ID);

        // ganti 24

        $deleteSupSewa->delete();

        // ganti
        return redirect('/sup-sewa')->with('status', 'Data berhasil dihapus');
    }

}