<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
// ganti 1

use App\Models\Master\BrgchDetail;
use App\Models\Master\Brgch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use DataTables;
use Auth;
use DB;
use Carbon\Carbon;

// ganti 2
class PerbaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */



    public function index()
    {

        // ganti 3
        return view('master_perba.index');
    }


    // ganti 4
    public function getPerba( Request $request )
    {
        // ganti 5
        // $CBG = Auth::user()->CBG;

        if ($request->session()->has('periode')) {
            $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
        } else {
            $periode = '';
        }

        $bulan = substr($periode,0,2);

        $perba = DB::SELECT("SELECT a.NO_ID, b.NA_FILE, a.SUB, a.KD_BRG, a.NA_BRG, a.KET_UK, a.KLK, a.KET_KEM, a.FUNGSI, a.KMP, 
                                    a.KMP1, a.KMP2, a.NM_BRG, a.KET_UK2, a.KLK2, a.KET_KEM2, a.FUNGSI2, a.KMP_LAMA
                            FROM brgchd AS a, brgch AS b");

        // ganti 6

        return Datatables::of($perba)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                if (Auth::user()->divisi=="programmer" || Auth::user()->divisi=="owner" || Auth::user()->divisi=="sales")
                {
                    // url untuk delete di index
                    $url = "'".url("brg/delete/" . $row->NO_ID )."'";
                    // batas

                    $btnDelete = ' onclick="deleteRow('.$url.')"';

                    $btnPrivilege =
                        '
                                <a class="dropdown-item" href="brg/edit/?idx=' . $row->NO_ID . '&tipx=edit";
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


    public function store(Request $request)
    {


        $this->validate(
            $request,
            // GANTI 9

            [
                // 'KD_BRG'       => 'required'
                'NA_BRG'       => 'required'

            ]

        );

        $CBG = Auth::user()->CBG;


        // $query = DB::table('brg')->select('KD_BRG')->whereRaw("LEFT(KD_BRG, 2) = ?", ['HD'])->orderByDesc('KD_BRG')->first();
        // $kd_brg = '';
        // if ($query != '[]') {
        //     $query = substr($query->KD_BRG, 2, 7);
        //     $query = str_pad($query + 1, 5, 0, STR_PAD_LEFT);
        //     $kd_brg = 'HD'.$query;
        // } else {
        //     $kd_brg = 'HD00001' ;
        // }

        // UNTUK KD 2 DIGIT
            $kd_brg2 = '';
            // Ambil semua nomor bukti yang ada, urutkan ascending
            $allRecords = Brg::orderBy('KD_BRG2', 'asc')->pluck('KD_BRG2')->toArray();

            $missingNo = null;

            // Cek apakah ada nomor yang bolong
            $prevChar = 'a';
            $prevNum = 0;

            foreach ($allRecords as $record) {
                preg_match('/([a-zA-Z]+)(\d+)/', $record, $matches);
                if ($matches) {
                    $charPart = $matches[1];
                    $numPart  = (int) $matches[2];

                    // Cek apakah ada nomor yang bolong
                    if ($charPart == $prevChar && $numPart > $prevNum + 1) {
                        $missingNo = $prevChar . ($prevNum + 1);
                        break;
                    }

                    // Update nilai sebelumnya
                    $prevChar = $charPart;
                    $prevNum = $numPart;
                }
            }

            if ($missingNo) {
                $kd_brg2 = $missingNo; // Gunakan nomor yang bolong
            } else {
                // Tidak ada nomor bolong, buat nomor baru berdasarkan yang terakhir
                if (empty($allRecords)) {
                    $kd_brg2 = 'a0'; // Jika belum ada data, mulai dari 'a0'
                } else {
                    preg_match('/([a-zA-Z]+)(\d+)/', end($allRecords), $matches);
                    if ($matches) {
                        $charPart = $matches[1];
                        $numPart  = (int) $matches[2];

                        if ($numPart < 9) {
                            $numPart++;
                        } else {
                            $numPart = 0;
                            $charPart++;
                        }

                        $kd_brg2 = $charPart . $numPart;
                    } else {
                        $kd_brg2 = 'a0';
                    }
                }
            }
        // BATAS UNTUK KD 2 DIGIT

        // UNTUK KD_BRG
        $length = 7;
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'; // Huruf besar & angka
        $kd_brg = '';

        for ($i = 0; $i < $length; $i++) {
            $kd_brg .= $characters[rand(0, strlen($characters) - 1)];
        }

        //UNTUK BARCODE (type code 128)
        $length = 13;
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'; // Kombinasi huruf & angka
        $barcode = '';

        for ($i = 0; $i < $length; $i++) {
            $barcode .= $characters[rand(0, strlen($characters) - 1)];
        }
        // Insert Header

        // ganti 10

        $brg = Brg::create(
            [
                'KD_BRG'         => ($kd_brg == null) ? "" : $kd_brg,
                'KD_BRG2'        => ($kd_brg2 == null) ? "" : $kd_brg2,
                'NA_BRG'         => ($request['NA_BRG'] == null) ? "" : $request['NA_BRG'],
                'KODES'          => ($request['KODES'] == null) ? "" : $request['KODES'],
                'NAMAS'          => ($request['NAMAS'] == null) ? "" : $request['NAMAS'],
                'USRNM'          => Auth::user()->username,
                'CBG'            => $CBG,
                'TG_SMP'         => Carbon::now()
            ]
        );

        //  ganti 11

	    $kd_brgx = $request['KD_BRG'];

		$brg = Brg::where('KD_BRG', $kd_brgx )->first();

		$datax = DB::SELECT("SELECT KD_BRG as kd_brgh, NA_BRG as na_brgh, KODES as kodes, NAMAS as namas, USRNM as usrnm from brg WHERE KD_BRG='".$kd_brg."'");
        $data = [
            "status" => "new",
            "type" => "BARANG", // Ambil dari request
            "data" => $datax
        ];
		$response = Http::asJson()->withHeaders([
            'Content-Type' => 'application/json'
            ])->post('http://192.168.0.2/admin-apf-app/public/api/sinkron_hadiah_hijau', $data);


        // DB::SELECT("UPDATE brg,  brgdx
        //                     SET  brgdx.ID =  brg.NO_ID  WHERE  brg.KD_BRG =  brgdx.KD_BRG
		// 					AND  brg.KD_BRG='$kd_brgx';");

        //return redirect('/brg/edit/?idx=' . $brg->NO_ID . '&tipx=edit')->with('statusInsert', 'Data baru berhasil ditambahkan');
		return redirect('/brg')->with('statusInsert', 'Data baru berhasil ditambahkan');

		}

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */



    // ganti 15

    public function edit(Request $request , Brg $brg)
    {

        // ganti 16
        $tipx = $request->tipx;

		$idx = $request->idx;



		if ( $idx =='0' && $tipx=='undo'  )
	    {
			$tipx ='top';

		   }

		if ($tipx=='search') {


    	   $kodex = $request->kodex;

		   $bingco = DB::SELECT("SELECT NO_ID, KD_BRG from brg
		                 where KD_BRG = '$kodex'
		                 ORDER BY KD_BRG ASC  LIMIT 1" );


			if(!empty($bingco))
			{
				$idx = $bingco[0]->NO_ID;
			  }
			else
			{
				$idx = 0;
			  }


		}

		if ($tipx=='top') {

		   $bingco = DB::SELECT("SELECT NO_ID, KD_BRG from brg
		                 ORDER BY KD_BRG ASC  LIMIT 1" );

			if(!empty($bingco))
			{
				$idx = $bingco[0]->NO_ID;
			  }
			else
			{
				$idx = 0;
			  }

		}


		if ($tipx=='prev' ) {

    	   $kodex = $request->kodex;

		   $bingco = DB::SELECT("SELECT NO_ID, KD_BRG from BRG
		             where KD_BRG <
					 '$kodex' ORDER BY KD_BRG DESC LIMIT 1" );


			if(!empty($bingco))
			{
				$idx = $bingco[0]->NO_ID;
			  }
			else
			{
				$idx = $idx;
			  }





		}
		if ($tipx=='next' ) {


      	   $kodex = $request->kodex;

		   $bingco = DB::SELECT("SELECT NO_ID, KD_BRG from BRG
		             where KD_BRG >
					 '$kodex' ORDER BY KD_BRG ASC LIMIT 1" );

			if(!empty($bingco))
			{
				$idx = $bingco[0]->NO_ID;
			  }
			else
			{
				$idx = $idx;
			  }


		}

		if ($tipx=='bottom') {

    		$bingco = DB::SELECT("SELECT NO_ID, KD_BRG from BRG
		              ORDER BY KD_BRG DESC  LIMIT 1" );

			if(!empty($bingco))
			{
				$idx = $bingco[0]->NO_ID;
			  }
			else
			{
				$idx = 0;
			  }


		}


		if ( $tipx=='undo' || $tipx=='search' )
	    {

			$tipx ='edit';

		   }



	  	if ( $idx != 0 )
		{
			$brg = Brg::where('NO_ID', $idx )->first();
	     }
		 else
		 {
             $brg = new Brg;
		 }

        $kd_brg = $brg->KD_BRG;
        // $brgDetail = DB::table('brgdx')->where('KD_BRG', $kd_brg)->get();


		 $data = [
                    'header'        => $brg,
                    // 'detail'        => $brgDetail,
                ];
        return view('master_brg.edit', $data)->with(['tipx' => $tipx, 'idx' => $idx ]);





    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    // ganti 18

    public function update(Request $request, Brg $brg)
    {

        $this->validate(
            $request,
            [

                // ganti 19

                'KD_BRG'       => 'required',
                'NA_BRG'      => 'required'
            ]
        );

        // ganti 20

        $CBG = Auth::user()->CBG;

        $tipx = 'edit';
		$idx = $request->idx;

        $brg->update(
            [

                'NA_BRG'       => ($request['NA_BRG'] == null) ? "" : $request['NA_BRG'],
                'KODES'       => ($request['KODES'] == null) ? "" : $request['KODES'],
                'NAMAS'       => ($request['NAMAS'] == null) ? "" : $request['NAMAS'],
				'USRNM'          => Auth::user()->username,
				'CBG'            => $CBG,
                'TG_SMP'         => Carbon::now()

            ]
        );

        // $brg = Brg::where('KD_BRG', $kd_brgx )->first();

        //  ganti 21
		$datax = DB::SELECT("SELECT KD_BRG as kd_brgh, NA_BRG as na_brgh, KODES as kodes, NAMAS as namas, USRNM as usrnm from brg WHERE KD_BRG='".$brg->KD_BRG."'");
        $data = [
            "status" => "edit",
            "type" => "BARANG", // Ambil dari request
            "data" => $datax
        ];
		// dd($data);
		$response = Http::asJson()->withHeaders([
            'Content-Type' => 'application/json'
            ])->post('http://192.168.0.2/admin-apf-app/public/api/sinkron_hadiah_hijau', $data);
        //return redirect('/brg/edit/?idx=' . $brg->NO_ID . '&tipx=edit');
		return redirect('/brg')->with('status', 'Data berhasil diupdate');


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    // ganti 22

    public function destroy(Request $request , Brg $brg)
    {

        // ganti 23
        $deleteBrg = Brg::find($brg->NO_ID);
        $kd_brg = $deleteBrg->KD_BRG;
		$datax = DB::SELECT("SELECT KD_BRG as kd_brgh from brg WHERE KD_BRG='".$kd_brg."'");

        // ganti 24

        $deleteBrg->delete();

        $data = [
            "status" => "delete",
            "type" => "BARANG", // Ambil dari request
            "data" => datax
        ];
		$response = Http::asJson()->withHeaders([
            'Content-Type' => 'application/json'
            ])->post('http://192.168.0.2/admin-apf-app/public/api/sinkron_hadiah_hijau', $data);

        // ganti
        return redirect('/brg')->with('status', 'Data berhasil dihapus');
    }


    public function cekbarang(Request $request)
    {
        $getItem = DB::SELECT('select count(*) as ADA from brg where KD_BRG ="' . $request->KD_BRG . '"');

        return $getItem;
    }

    public function getSelectKdbrg(Request $request)
    {
        $search = $request->search;
        $page = $request->page;
        if ($page == 0) {
            $xa = 0;
        } else {
            $xa = ($page - 1) * 10;
        }
        $perPage = 10;

        $hasil = DB::SELECT("SELECT KD_BRG, NA_BRG, SATUAN from brg WHERE (KD_BRG LIKE '%$search%' or NA_BRG LIKE '%$search%') ORDER BY KD_BRG LIMIT $xa,$perPage ");
        $selectajax = array();
        foreach ($hasil as $row => $value) {
            $selectajax[] = array(
                'id' => $hasil[$row]->KD_BRG,
                'text' => $hasil[$row]->KD_BRG,
                'na_brg' => $hasil[$row]->NA_BRG,
                'satuan' => $hasil[$row]->SATUAN,
            );
        }
        $select['total_count'] =  count($selectajax);
        $select['items'] = $selectajax;
        return response()->json($select);
    }


}
