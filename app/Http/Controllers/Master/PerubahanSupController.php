<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Master\Brg;
use App\Models\Master\Sup;
use DataTables;
use Auth;
use DB;
use Carbon\Carbon;

class PerubahanSupController extends Controller
{

    public function index()
    {
        return view('master_pengajuan_perubahan_supplier.index');
    }

    public function getPerubahanSup(Request $request)
    {
        // Query untuk mengambil data supplier
        $sql = "SELECT * FROM sup ORDER BY KODES";
        $sup = DB::select($sql);

        return Datatables::of($sup)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                if (Auth::user()->divisi == "programmer" || Auth::user()->divisi == "owner" || Auth::user()->divisi == "sales") {
                    // url untuk delete di index
                    $url = "'" . url("perubahan_sup/delete/" . $row->NO_ID) . "'";
                    // batas

                    $btnDelete = ' onclick="deleteRow(' . $url . ')"';

                    $btnPrivilege =
                        '
                                    <a class="dropdown-item" href="perubahan_sup/edit/?idx=' . $row->NO_ID . '&tipx=edit">
                                    <i class="fas fa-edit"></i>
                                        Edit
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
                                <a hidden class="dropdown-item" href="perubahan_sup/show/' . $row->NO_ID . '">
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
        $this->validate(
            $request,
            [
                'NAMAS'       => 'required'
            ]
        );

        // Generate kode supplier baru
        $query = DB::table('sup')->select('KODES')->orderByDesc('KODES')->first();

        $kodes = '';
        if ($query) {
            $query = $query->KODES;
            $query = str_pad($query + 1, 4, 0, STR_PAD_LEFT);
            $kodes = $query;
        } else {
            $kodes = '0001';
        }

        // Insert Supplier
        $sup = Sup::create(
            [
                // 'KODES'          => ($kodes == null) ? "" : $kodes,
                'KODES'          => ($request['KODES'] == null) ? "" : $request['KODES'],
                'NO_SUPL'        => ($request['NO_SUPL'] == null) ? "" : $request['NO_SUPL'],
                'NAMAS'          => ($request['NAMAS'] == null) ? "" : $request['NAMAS'],
                'ALMT_K'         => ($request['ALMT_K'] == null) ? "" : $request['ALMT_K'],
                'KOTA'           => ($request['KOTA'] == null) ? "" : $request['KOTA'],
                'TLP_K'          => ($request['TLP_K'] == null) ? "" : $request['TLP_K'],
                'NO_FAX'         => ($request['NO_FAX'] == null) ? "" : $request['NO_FAX'],
                'PEMILIK'        => ($request['PEMILIK'] == null) ? "" : $request['PEMILIK'],
                'ALMT_R'         => ($request['ALMT_R'] == null) ? "" : $request['ALMT_R'],
                'USRNM'          => Auth::user()->username,
                'TG_SMP'         => Carbon::now(),
            ]
        );

        return redirect('/perubahan_sup')->with('statusInsert', 'Data baru berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */



    // ganti 15

    public function edit(Request $request, Sup $sup)
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

            $bingco = DB::SELECT("SELECT NO_ID, KODES from sup
		                 where KODES = '$kodex'
		                 ORDER BY KODES ASC  LIMIT 1");


            if (!empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = 0;
            }
        }

        if ($tipx == 'top') {

            $bingco = DB::SELECT("SELECT NO_ID, KODES from sup
		                 ORDER BY KODES ASC  LIMIT 1");

            if (!empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = 0;
            }
        }


        if ($tipx == 'prev') {

            $kodex = $request->kodex;

            $bingco = DB::SELECT("SELECT NO_ID, KODES from sup
		             where KODES <
					 '$kodex' ORDER BY KODES DESC LIMIT 1");


            if (!empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = $idx;
            }
        }
        if ($tipx == 'next') {


            $kodex = $request->kodex;

            $bingco = DB::SELECT("SELECT NO_ID, KODES from sup
		             where KODES >
					 '$kodex' ORDER BY KODES ASC LIMIT 1");

            if (!empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = $idx;
            }
        }

        if ($tipx == 'bottom') {

            $bingco = DB::SELECT("SELECT NO_ID, KODES from sup
		              ORDER BY KODES DESC  LIMIT 1");

            if (!empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = 0;
            }
        }


        if ($tipx == 'undo' || $tipx == 'search') {

            $tipx = 'edit';
        }

        if ($idx != 0) {
            $sup = Sup::where('NO_ID', $idx)->first();
        } else {
            $sup = new Sup;
        }

        $data = [
            'header'           => $sup,
        ];

        return view('master_pengajuan_perubahan_supplier.edit', $data)->with(['tipx' => $tipx, 'idx' => $idx]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */


    public function update(Request $request, Sup $sup)
    {

        $this->validate(
            $request,
            [
                'KODES'       => 'required',
                'NAMAS'       => 'required'
            ]
        );

        $tipx = 'edit';
        $idx = $request->idx;

        $sup->update(
            [
                'NO_SUPL'        => ($request['NO_SUPL'] == null) ? "" : $request['NO_SUPL'],
                'NAMAS'          => ($request['NAMAS'] == null) ? "" : $request['NAMAS'],
                'ALMT_K'         => ($request['ALMT_K'] == null) ? "" : $request['ALMT_K'],
                'KOTA'           => ($request['KOTA'] == null) ? "" : $request['KOTA'],
                'TLP_K'          => ($request['TLP_K'] == null) ? "" : $request['TLP_K'],
                'NO_FAX'         => ($request['NO_FAX'] == null) ? "" : $request['NO_FAX'],
                'PEMILIK'        => ($request['PEMILIK'] == null) ? "" : $request['PEMILIK'],
                'ALMT_R'         => ($request['ALMT_R'] == null) ? "" : $request['ALMT_R'],
                'USRNM'          => Auth::user()->username,
                'TG_SMP'         => Carbon::now(),
            ]
        );

        return redirect('/perubahan_sup')->with('status', 'Data berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    // ganti 22

    public function destroy(Request $request, Sup $sup)
    {

        // ganti 23
        $deleteSup = Sup::find($sup->NO_ID);

        // ganti 24

        $deleteSup->delete();

        // ganti
        return redirect('/perubahan_sup')->with('status', 'Data berhasil dihapus');
    }
}
