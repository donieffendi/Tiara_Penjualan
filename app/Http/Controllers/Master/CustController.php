<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
// ganti 1

use App\Models\Master\Cust;
use App\Models\Master\Acnox;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// ganti 2
class CustController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        // ganti 3
        return view('master_cust_used.index');
    }

    // ganti 4
    public function browse(Request $request)
    {


        // if (!empty(request('q'))) {


        //      $cust = DB::SELECT("SELECT a.NO_ID, a.KODEC, a.NAMAC, a.ALAMAT, a.KOTA,  a.AKTIF,
        //                             CASE WHEN a.PKP = '1' THEN '(PKP)' ELSE '(NON PKP)' END AS PKP2,
        //                             a.PKP, a.KODEP, a.NAMAP, a.RING, b.KOM, a.HARI
        //                     FROM cust a, pegawai b
        //                     WHERE a.KODEP = b.KODEP and A.NAMAC LIKE ('%$request->q%') ORDER BY NAMAC ");


        // } else {

        //      $cust = DB::SELECT("SELECT a.NO_ID, a.KODEC, a.NAMAC, a.ALAMAT, a.KOTA,  a.AKTIF,
        //                             CASE WHEN a.PKP = '1' THEN '(PKP)' ELSE '(NON PKP)' END AS PKP2,
        //                             a.PKP, a.KODEP, a.NAMAP, a.RING, b.KOM, a.HARI
        //                     FROM cust a, pegawai b
        //                     WHERE a.KODEP = b.KODEP  ORDER BY NAMAC ");

        // }

        if (!empty(request('q'))) {


            $cust = DB::SELECT("SELECT NO_ID, KODEC, NAMAC, ALAMAT, KOTA,
                    KONTAK, AKTIF, CASE WHEN PKP = '1' THEN '(PKP)' ELSE '(NON PKP)' END AS PKP2,
                    PKP, HARI
                    FROM cust WHERE NAMAC LIKE ('%$request->q%') ORDER BY NAMAC ");
        } else {
            $cust = DB::SELECT("SELECT NO_ID, KODEC, NAMAC, ALAMAT, KOTA,
                            KONTAK, AKTIF, CASE WHEN PKP = '1' THEN '(PKP)' ELSE '(NON PKP)' END AS PKP2,
                            PKP, HARI
                        FROM cust
                        ORDER BY NAMAC ");
        }

        return response()->json($cust);
    }


    public function browse_hari(Request $request)
    {
        $kodec = $request->KODEC;

        $cust = DB::SELECT("SELECT a.NO_ID, a.KODEC, a.NAMAC, a.ALAMAT, a.KOTA,  a.AKTIF,
                                CASE WHEN a.PKP = '1' THEN '(PKP)' ELSE '(NON PKP)' END AS PKP2,
                                a.PKP
                        FROM cust a
                        WHERE a.KODEC = '$kodec' ");



        return response()->json($cust);
    }


    public function getCust()
    {
        // ganti 5

        $cust = DB::SELECT("SELECT * from cust  ORDER BY KODEC ");

        // ganti 6

        return Datatables::of($cust)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                if (Auth::user()->divisi == "programmer" || Auth::user()->divisi == "owner" || Auth::user()->divisi == "sales") {
                    // url untuk delete di index
                    $url = "'" . url("cust/delete/" . $row->NO_ID) . "'";
                    // batas

                    $btnDelete = ' onclick="deleteRow(' . $url . ')"';

                    $btnPrivilege =
                        '
                            <a class="dropdown-item" href="cust/edit/?idx=' . $row->NO_ID . '&tipx=edit";
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
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {


        $this->validate(
            $request,
            // GANTI 9

            [
                // 'KODEC'       => 'required',
                'NAMAC'       => 'required'
                //,
                //'GOL'         => 'required'
            ]
        );

        // UNTUK KD_BRG
        $length = 7;
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'; // Huruf besar & angka
        $kodec = '';

        for ($i = 0; $i < $length; $i++) {
            $kodec .= $characters[rand(0, strlen($characters) - 1)];
        }


        //UNTUK BARCODE (type code 128)
        $length = 7;
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'; // Kombinasi huruf & angka
        $barcode = '';

        for ($i = 0; $i < $length; $i++) {
            $barcode .= $characters[rand(0, strlen($characters) - 1)];
        }
        // $CBG = Auth::user()->CBG;

        // Insert Header

        // ganti 10

        $cust = Cust::create(
            [
                'KODEC'         => ($kodec == null) ? "" : $kodec,
                'NAMAC'         => ($request['NAMAC'] == null) ? "" : $request['NAMAC'],
                'ALAMAT'        => ($request['ALAMAT'] == null) ? "" : $request['ALAMAT'],
                'KOTA'          => ($request['KOTA'] == null) ? "" : $request['KOTA'],
                'TELPON1'       => ($request['TELPON1'] == null) ? "" : $request['TELPON1'],
                'FAX'           => ($request['FAX'] == null) ? "" : $request['FAX'],
                'HP'            => ($request['HP'] == null) ? "" : $request['HP'],
                'KONTAK'        => ($request['KONTAK'] == null) ? "" : $request['KONTAK'],
                'EMAIL'         => ($request['EMAIL'] == null) ? "" : $request['EMAIL'],
                'NPWP'          => ($request['NPWP'] == null) ? "" : $request['NPWP'],
                'KET'           => ($request['KET'] == null) ? "" : $request['KET'],
                'NAMA_PEMILIK'  => ($request['NAMA_PEMILIK'] == null) ? "" : $request['NAMA_PEMILIK'],
                'GOL'           => ($request['GOL'] == null) ? "" : $request['GOL'],
                'PKP_NPKP'      => ($request['PKP_NPKP'] == null) ? "" : $request['PKP_NPKP'],
                'PKP'           => (float) str_replace(',', '', $request['PKP']),
                'BANK'          => ($request['BANK'] == null) ? "" : $request['BANK'],
                'BANK_CAB'      => ($request['BANK_CAB'] == null) ? "" : $request['BANK_CAB'],
                'BANK_KOTA'     => ($request['BANK_KOTA'] == null) ? "" : $request['BANK_KOTA'],
                'BANK_NAMA'     => ($request['BANK_NAMA'] == null) ? "" : $request['BANK_NAMA'],
                'BANK_REK'      => ($request['BANK_REK'] == null) ? "" : $request['BANK_REK'],
                'LIM'           => ($request['LIM'] == null) ? "" : $request['LIM'],
                'hari'          => ($request['hari'] == null) ? "" : $request['hari'],
                'USRNM'         => Auth::user()->username,
                'TG_SMP'        => Carbon::now()
            ]
        );

        //  ganti 11

        $kodecx = $request['KODEC'];

        $cust = Cust::where('KODEC', $kodecx)->first();

        //return redirect('/cust/edit/?idx=' . $cust->NO_ID . '&tipx=edit')->with('statusInsert', 'Data baru berhasil ditambahkan');
        return redirect('/cust')->with('statusInsert', 'Data baru berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    // ganti 12



    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    // ganti 15

    public function edit(Request $request,  Cust $cust)
    {

        $pilihbank = DB::table('bang')->select('KODE', 'NAMA')->orderBy('KODE', 'ASC')->get();

        // ganti 16


        $tipx = $request->tipx;

        $idx = $request->idx;



        if ($idx == '0' && $tipx == 'undo') {
            $tipx = 'top';
        }


        if ($tipx == 'search') {


            $kodex = $request->kodex;

            $bingco = DB::SELECT("SELECT NO_ID, ACNO from cust
                        where KODEC = '$kodex'
                        ORDER BY KODEC ASC  LIMIT 1");


            if (!empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = 0;
            }
        }

        if ($tipx == 'top') {

            $bingco = DB::SELECT("SELECT NO_ID, KODEC from cust
                        ORDER BY KODEC ASC  LIMIT 1");

            if (!empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = 0;
            }
        }


        if ($tipx == 'prev') {

            $kodex = $request->kodex;

            $bingco = DB::SELECT("SELECT NO_ID, KODEC from CUST
                    where KODEC <
                    '$kodex' ORDER BY KODEC DESC LIMIT 1");


            if (!empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = $idx;
            }
        }
        if ($tipx == 'next') {


            $kodex = $request->kodex;

            $bingco = DB::SELECT("SELECT NO_ID, KODEC from CUST
                    where KODEC >
                    '$kodex' ORDER BY KODEC ASC LIMIT 1");

            if (!empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = $idx;
            }
        }

        if ($tipx == 'bottom') {

            $bingco = DB::SELECT("SELECT NO_ID, KODEC from CUST
                    ORDER BY KODEC DESC  LIMIT 1");

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
            $cust = Cust::where('NO_ID', $idx)->first();
        } else {
            $cust = new Cust;
        }

        $data = [
            'header' => $cust,
        ];
        return view('master_cust_used.edit', $data)->with(['tipx' => $tipx, 'idx' => $idx])->with(['pilihbank' => $pilihbank]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    // ganti 18

    public function update(Request $request, Cust $cust)
    {

        $this->validate(
            $request,
            [

                // ganti 19

                'KODEC'       => 'required',
                'NO_MEMBER'       => 'required',
                'NAMAC'      => 'required'
            ]
        );


        // $CBG = Auth::user()->CBG;

        // ganti 20
        $tipx = 'edit';
        $idx = $request->idx;

        $cust->update(
            [
                'KODEC'         => ($request['KODEC'] == null) ? "" : $request['KODEC'],
                'NAMAC'         => ($request['NAMAC'] == null) ? "" : $request['NAMAC'],
                'ALAMAT'        => ($request['ALAMAT'] == null) ? "" : $request['ALAMAT'],
                'KOTA'          => ($request['KOTA'] == null) ? "" : $request['KOTA'],
                'TELPON1'       => ($request['TELPON1'] == null) ? "" : $request['TELPON1'],
                'FAX'           => ($request['FAX'] == null) ? "" : $request['FAX'],
                'HP'            => ($request['HP'] == null) ? "" : $request['HP'],
                'KONTAK'        => ($request['KONTAK'] == null) ? "" : $request['KONTAK'],
                'EMAIL'         => ($request['EMAIL'] == null) ? "" : $request['EMAIL'],
                'NPWP'          => ($request['NPWP'] == null) ? "" : $request['NPWP'],
                'KET'           => ($request['KET'] == null) ? "" : $request['KET'],
                'NAMA_PEMILIK'  => ($request['NAMA_PEMILIK'] == null) ? "" : $request['NAMA_PEMILIK'],
                'GOL'           => ($request['GOL'] == null) ? "" : $request['GOL'],
                'PKP_NPKP'      => ($request['PKP_NPKP'] == null) ? "" : $request['PKP_NPKP'],
                'PKP'           => (float) str_replace(',', '', $request['PKP']),
                'BANK'          => ($request['BANK'] == null) ? "" : $request['BANK'],
                'BANK_CAB'      => ($request['BANK_CAB'] == null) ? "" : $request['BANK_CAB'],
                'BANK_KOTA'     => ($request['BANK_KOTA'] == null) ? "" : $request['BANK_KOTA'],
                'BANK_NAMA'     => ($request['BANK_NAMA'] == null) ? "" : $request['BANK_NAMA'],
                'BANK_REK'      => ($request['BANK_REK'] == null) ? "" : $request['BANK_REK'],
                'LIM'           => ($request['LIM'] == null) ? "" : $request['LIM'],
                'hari'          => ($request['hari'] == null) ? "" : $request['hari'],
                'USRNM'         => Auth::user()->username,
                'TG_SMP'        => Carbon::now()
            ]
        );


        //  ganti 21

        //return redirect('/cust/edit/?idx=' . $cust->NO_ID . '&tipx=edit');
        return redirect('/cust')->with('statusInsert', 'Data baru berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    // ganti 22

    public function destroy(Request $request, Cust $cust)
    {

        // ganti 23
        $deleteCust = Cust::find($cust->NO_ID);

        // ganti 24

        $deleteCust->delete();

        // ganti
        return redirect('/cust')->with('status', 'Data berhasil dihapus');
    }

    public function cekcust(Request $request)
    {
        $getItem = DB::SELECT('select count(*) as ADA from cust where KODEC ="' . $request->KODEC . '"');

        return $getItem;
    }
}
