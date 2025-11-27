<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
// ganti 1

use App\Models\OTransaksi\Hut;
use App\Models\OTransaksi\HutDetail;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use DB;
use Carbon\Carbon;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;

// ganti 2
class HutController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    var $judul = '';
    var $FLAGZ = '';

    function setFlag(Request $request)
    {
        if ( $request->flagz == 'B' ) {
            $this->judul = "Pembayaran Hutang";
        } elseif ( $request->flagz == 'PHU' ) {
            $this->judul = "Posting Pembayaran Hutang";
        }

        $this->FLAGZ = $request->flagz;


    }

    public function index(Request $request)
    {

	    $this->setFlag($request);
        if ($this->FLAGZ == 'B') {
            $view = "otransaksi_hut.index";
        } elseif($this->FLAGZ == 'PHU') {
            $view = "otransaksi_hut.index";
        }
        return view($view)->with([
            'judul' => $this->judul,
            'flagz' => $this->FLAGZ,
        ]);

    }
    
	public function browse(Request $request)
    {
        $CBG = Auth::user()->CBG;

        $filter_hut='';

        if (!empty($request->NO_HUT)) {

            $filter_hut = " AND NO_BUKTI ='".$request->NO_HUT."' ";
        }

            $hut = DB::SELECT("SELECT distinct NO_BUKTI AS NO_HUT, TGL, BAYAR
                                FROM hut
                                WHERE NO_KASIR = '' and CBG = '$CBG'
                                $filter_hut
                                ORDER BY NO_BUKTI  ");

        if	( empty($hut) ) {

            $hut = DB::SELECT("SELECT distinct NO_BUKTI AS NO_HUT, TGL, BAYAR
                                FROM hut
                                WHERE NO_KASIR = '' and CBG = '$CBG'
                                ORDER BY NO_BUKTI ");
        }

        return response()->json($hut);
    }


    public function getHut(Request $request)
    {
// ganti 5

	if ($request->session()->has('periode'))
		{
			$periode = $request->session()->get('periode')['bulan']. '/' . $request->session()->get('periode')['tahun'];
		} else
		{
			$periode = '';
		}

		$this->setFlag($request);

		$CBG = Auth::user()->CBG;

       $hut = DB::SELECT("SELECT NO_ID, NO_BUKTI,
	   TGL, KODES, NAMAS, KOTA, TOTAL, BAYAR, NOTES, POSTED, FLAG,
	   USRNM from hut
	   where PER = '$periode' AND CBG='$CBG' ORDER BY NO_BUKTI ");


// ganti 6

        return Datatables::of($hut)
                ->addIndexColumn()
                ->addColumn('action', function($row) {
                    if ( Auth::user()->divisi=="programmer" )
                    {

						$btnEdit =   ($row->POSTED == 1) ? ' onclick= "alert(\'Transaksi ' . $row->NO_BUKTI . ' sudah diposting!\')" href="#" ' : ' href="hut/edit/?idx=' . $row->NO_ID . '&tipx=edit&flagz=' . $row->FLAG . '&judul=' . $this->judul . '"';
						$btnDelete = ($row->POSTED == 1) ? ' onclick= "alert(\'Transaksi ' . $row->NO_BUKTI . ' sudah diposting!\')" href="#" ' : ' onclick="return confirm(&quot; Apakah anda yakin ingin hapus? &quot;)" href="hut/delete/' . $row->NO_ID . '/?flagz=' . $row->FLAG . '" ';



                        $btnPrivilege =
                        '
                                <a class="dropdown-item" '.$btnEdit.'>
                                <i class="fas fa-edit"></i>
                                    Edit
                                </a>
                                <a class="dropdown-item btn btn-danger" href="hut/cetak/' . $row->NO_ID . '">
                                    <i class="fa fa-print" aria-hidden="true"></i>
                                    Print
                                </a>
                                <hr></hr>
                                <a class="dropdown-item btn btn-danger" onclick="return confirm(&quot; Apakah anda yakin ingin hapus? &quot;)" ' . $btnDelete . '>

                                    <i class="fa fa-trash" aria-hidden="true"></i>
                                    Delete
                                </a>
                        ';
                    }
                    else
                    {
                        $btnPrivilege = '';
                    }

                    $actionBtn =
                    '
                    <div class="dropdown show" style="text-align: center">
                        <a class="btn btn-secondary dropdown-toggle btn-sm" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-bars"></i>
                        </a>

                        <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">


                            '.$btnPrivilege.'
                        </div>
                    </div>
                    ';

                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {


        $this->validate($request,
// GANTI 9

        [

                'TGL'      => 'required',

            ]
        );

//////     nomer otomatis

		$this->setFlag($request);
        $FLAGZ = $this->FLAGZ;
        $judul = $this->judul;

        $CBG = Auth::user()->CBG;

        $periode = $request->session()->get('periode')['bulan']. '/' . $request->session()->get('periode')['tahun'];

        $bulan	= session()->get('periode')['bulan'];
		$tahun	= substr(session()->get('periode')['tahun'],-2);

        $query = DB::table('hut')->select('NO_BUKTI')->where('PER', $periode)->where('FLAG', $FLAGZ)->where('CBG', $CBG)->orderByDesc('NO_BUKTI')->limit(1)->get();

        if ($query != '[]') {
            $query = substr($query[0]->NO_BUKTI, -4);
            $query = str_pad($query + 1, 4, 0, STR_PAD_LEFT);
            $no_bukti = 'HT' . $CBG . $tahun . $bulan . '-' . $query;
        } else {
            $no_bukti = 'HT' . $CBG . $tahun . $bulan . '-0001';
        }

        /////////////////////////////////////////////////////////////////////////////////

        $bulan    = session()->get('periode')['bulan'];
        $tahun    = substr(session()->get('periode')['tahun'], -2);
        $query2 = DB::table('bank')->select('NO_BUKTI')->where('PER', $periode)->where('TYPE', 'BBK')->where('CBG', $CBG)->orderByDesc('NO_BUKTI')->limit(1)->get();

        if ($query2 != '[]') {
            $query2 = substr($query2[0]->NO_BUKTI, -4);
            $query2 = str_pad($query2 + 1, 4, 0, STR_PAD_LEFT);
            $no_bukti2 = 'BBK' . $CBG . $tahun . $bulan . '-' . $query2;
        } else {
            $no_bukti2 = 'BBK' . $CBG . $tahun . $bulan . '-0001';
        }

        // Insert Header

// ganti 10

        $hut = Hut::create(
            [
                'NO_BUKTI'         => $no_bukti,
				'TGL'              => date('Y-m-d', strtotime($request['TGL'])),
                'PER'              => $periode,
                'KODES'            => ($request['KODES']==null) ? "" : $request['KODES'],
                'NAMAS'            => ($request['NAMAS']==null) ? "" : $request['NAMAS'],
                'NOREK'            => ($request['NOREK']==null) ? "" : $request['NOREK'],
				'FLAG'             => 'B',
				'NOTES'            => ($request['NOTES']==null) ? "" : $request['NOTES'],
				'BACNO'            => ($request['BACNO']==null) ? "" : $request['BACNO'],
				'BNAMA'            => ($request['BNAMA']==null) ? "" : $request['BNAMA'],
				'TYPE'            => ($request['TYPE']==null) ? "" : $request['TYPE'],
                'BAYAR'            => (float) str_replace(',', '', $request['TBAYAR']),
                'LAIN'             => (float) str_replace(',', '', $request['TLAIN']),

                'NO_BANK'          => $no_bukti2,
				'USRNM'            => Auth::user()->username,
                'CBG'              => $CBG,
				'TG_SMP'           => Carbon::now()
            ]
        );


		$REC	    = $request->input('REC');
		$NO_FAKTUR	= $request->input('NO_FAKTUR');
		$TOTAL	= $request->input('TOTAL');
		$BAYAR	= $request->input('BAYAR');
		$SISA	= $request->input('SISA');

		// Check jika value detail ada/tidak
		if ($REC) {
			foreach ($REC as $key => $value) {
				// Declare new data di Model
				$detail	= new HutDetail;

				// Insert ke Database
				$detail->NO_BUKTI = $no_bukti;
				$detail->REC	= $REC[$key];
				$detail->PER	= $periode;
				$detail->CBG	= $CBG;
				$detail->FLAG	= 'B';
				$detail->NO_FAKTUR = ($NO_FAKTUR[$key]==null) ? "" :  $NO_FAKTUR[$key];
				$detail->TOTAL	= (float) str_replace(',', '', $TOTAL[$key]);
				$detail->BAYAR	= (float) str_replace(',', '', $BAYAR[$key]);
				$detail->save();
			}
		}






//  ganti 11

		$variablell = DB::select('call hutins(?,?)', array($no_bukti, $no_bukti2));

        $no_buktix = $no_bukti;

		$hut = Hut::where('NO_BUKTI', $no_buktix )->first();

        DB::SELECT("UPDATE HUT, SUP
                            SET HUT.NAMAS = SUP.NAMAS  WHERE HUT.KODES = SUP.KODES
							AND HUT.NO_BUKTI='$no_buktix';");

        DB::SELECT("UPDATE HUT, ACCOUNT
                            SET HUT.BNAMA = ACCOUNT.NAMA  WHERE HUT.BACNO = ACCOUNT.ACNO
							AND HUT.NO_BUKTI='$no_buktix';");

        DB::SELECT("UPDATE hut, hutd
                            SET hutd.ID = hut.NO_ID  WHERE hut.NO_BUKTI = hutd.NO_BUKTI
							AND hut.NO_BUKTI='$no_buktix';");

        // return redirect('/hut/edit/?idx=' . $hut->NO_ID . '&tipx=edit&flagz=' . $FLAGZ . '&judul=' . $this->judul . '');
        return redirect('/hut?flagz='.$FLAGZ)->with(['judul' => $judul, 'flagz' => $FLAGZ ]);


    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

// ganti 12


   public function edit( Request $request , Hut $hut )
    {


		$per = session()->get('periode')['bulan'] . '/' . session()->get('periode')['tahun'];


        $cekperid = DB::SELECT("SELECT POSTED from perid WHERE PERIO='$per'");
        if ($cekperid[0]->POSTED==1)
        {
            return redirect('/hut')
			       ->with('status', 'Maaf Periode sudah ditutup!')
                   ->with(['judul' => $judul, 'flagz' => $FLAGZ]);
        }


		$this->setFlag($request);

        $tipx = $request->tipx;

		$idx = $request->idx;

        $CBG = Auth::user()->CBG;

		if ( $idx =='0' && $tipx=='undo'  )
	    {
			$tipx ='top';

		   }



		if ($tipx=='search') {


    	   $buktix = $request->buktix;

		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from hut
		                 where PER ='$per'  and NO_BUKTI = '$buktix'
                         AND CBG = '$CBG'
		                 ORDER BY NO_BUKTI ASC  LIMIT 1" );


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


		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from hut
		                 where PER ='$per'
                         AND CBG = '$CBG'
		                 ORDER BY NO_BUKTI ASC  LIMIT 1" );


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

    	   $buktix = $request->buktix;

		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from hut
		             where PER ='$per' and NO_BUKTI <
					 '$buktix'
                     AND CBG = '$CBG' ORDER BY NO_BUKTI DESC LIMIT 1" );


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


      	   $buktix = $request->buktix;

		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from hut
		             where PER ='$per'  and NO_BUKTI >
					 '$buktix'
                     AND CBG = '$CBG' ORDER BY NO_BUKTI ASC LIMIT 1" );

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

    		$bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from hut
						where PER ='$per'
                        AND CBG = '$CBG'ORDER BY NO_BUKTI DESC  LIMIT 1" );

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
			$hut = Hut::where('NO_ID', $idx )->first();
	     }
		 else
		 {
				$hut = new Hut;
                $hut->TGL = Carbon::now();


		 }

        $no_bukti = $hut->NO_BUKTI;
	    $hutDetail = DB::table('hutd')->where('NO_BUKTI', $no_bukti)->get();


		$data = [
            'header'        => $hut,
            'detail'        => $hutDetail,

        ];


         return view('otransaksi_hut.edit', $data)
		 ->with(['tipx' => $tipx, 'idx' => $idx, 'flagz' =>$this->FLAGZ, 'judul' => $this->judul ]);


    }


// ganti 18

    public function update(Request $request, Hut $hut )
    {

        $this->validate($request,
        [

// ganti 19
  //              'NO_PO'       => 'required',
                'TGL'      => 'required'

            ]
        );

// ganti 20
		$variablell = DB::select('call hutdel(?)',array($hut['NO_BUKTI']));

        // ganti 20
        $periode = $request->session()->get('periode')['bulan']. '/' . $request->session()->get('periode')['tahun'];

		$this->setFlag($request);
        $FLAGZ = $this->FLAGZ;
        $judul = $this->judul;

        $CBG = Auth::user()->CBG;


        $hut->update(
            [
				'TGL'              => date('Y-m-d', strtotime($request['TGL'])),
                'KODES'            => ($request['KODES']==null) ? "" : $request['KODES'],
				'NAMAS'				=>($request['NAMAS']==null) ? "" : $request['NAMAS'],
				'NOTES'            => ($request['NOTES']==null) ? "" : $request['NOTES'],
                'BAYAR'            => (float) str_replace(',', '', $request['TBAYAR']),
                'LAIN'            => (float) str_replace(',', '', $request['TLAIN']),
				'BACNO'            => ($request['BACNO']==null) ? "" : $request['BACNO'],
				'BNAMA'            => ($request['BNAMA']==null) ? "" : $request['BNAMA'],
				'TYPE'            => ($request['TYPE']==null) ? "" : $request['TYPE'],
				'USRNM'            => Auth::user()->username,
                'CBG'              => $CBG,
				'TG_SMP'           => Carbon::now()
            ]
        );



		$no_buktix = $hut->NO_BUKTI;

        // Update Detail
        $length = sizeof($request->input('REC'));
        $NO_ID  = $request->input('NO_ID');

        $REC	= $request->input('REC');
		$NO_FAKTUR = $request->input('NO_FAKTUR');
		$BAYAR	= $request->input('BAYAR');
		$TOTAL	= $request->input('TOTAL');

         $query = DB::table('hutd')->where('NO_BUKTI', $request->NO_BUKTI)->whereNotIn('NO_ID',  $NO_ID)->delete();

        // Update / Insert
        for ($i=0;$i<$length;$i++) {
            // Insert jika NO_ID baru
            if ($NO_ID[$i] == 'new') {
                $insert = HutDetail::create(
                    [
                        'NO_BUKTI'   => $request->NO_BUKTI,
                        'REC'        => $REC[$i],
				        'PER'        => $periode,
				        'CBG'        => $CBG,
                        'FLAG'       => 'B',
                        'NO_FAKTUR'  => ($NO_FAKTUR[$i]==null) ? "" :  $NO_FAKTUR[$i],
                        'TOTAL'      => (float) str_replace(',', '', $TOTAL[$i]),
                        'BAYAR'      => (float) str_replace(',', '', $BAYAR[$i]),

                    ]
                );
            } else {
                // Update jika NO_ID sudah ada
                $upsert = HutDetail::updateOrCreate(
                    [
                        'NO_BUKTI'  => $request->NO_BUKTI,
                        'NO_ID'     => (int) str_replace(',', '', $NO_ID[$i])
                    ],

                    [
                        'REC'        => $REC[$i],
                        'NO_FAKTUR'  => ($NO_FAKTUR[$i]==null) ? "" :  $NO_FAKTUR[$i],
                        'TOTAL'      => (float) str_replace(',', '', $TOTAL[$i]),
                        'BAYAR'      => (float) str_replace(',', '', $BAYAR[$i]),
                        'CBG'        => $CBG,

                    ]
                );
            }
        }

//  ganti 21
		$variablell = DB::select('call hutins(?)',array($hut['NO_BUKTI']));

        $no_buktix = $no_bukti;

		$hut = Hut::where('NO_BUKTI', $no_buktix )->first();


        DB::SELECT("UPDATE HUT, SUP
            SET HUT.NAMAS = SUP.NAMAS  WHERE HUT.KODES = SUP.KODES
            AND HUT.NO_BUKTI='$no_buktix';");

        DB::SELECT("UPDATE HUT, ACCOUNT
            SET HUT.BNAMA = ACCOUNT.NAMA  WHERE HUT.BACNO = ACCOUNT.ACNO
            AND HUT.NO_BUKTI='$no_buktix';");

        DB::SELECT("UPDATE hut, hutd
                            SET hutd.ID = hut.NO_ID  WHERE hut.NO_BUKTI = hutd.NO_BUKTI
                            AND hut.NO_BUKTI='$no_buktix';");

        // return redirect('/hut/edit/?idx=' . $hut->NO_ID . '&tipx=edit&flagz=' . $this->FLAGZ . '&judul=' . $this->judul . '');
        return redirect('/hut?flagz='.$FLAGZ)->with(['judul' => $judul, 'flagz' => $FLAGZ ]);


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

// ganti 22

    public function destroy(Request $request,  Hut $hut)
    {

		$this->setFlag($request);
        $FLAGZ = $this->FLAGZ;
        $judul = $this->judul;

		$per = session()->get('periode')['bulan'] . '/' . session()->get('periode')['tahun'];
        $cekperid = DB::SELECT("SELECT POSTED AS POSTED from perid WHERE PERIO='$per'");
        if ($cekperid[0]->POSTED==1)
        {
            return redirect()->route('hut')
                ->with('status', 'Maaf Periode sudah ditutup!')
                ->with(['judul' => $this->judul, 'flagz' => $this->FLAGZ]);
        }

	    $variablell = DB::select('call hutdel(?)',array($hut['NO_BUKTI']));


// ganti 23
        $deleteHut = Hut::find($hut->NO_ID);

// ganti 24

        $deleteHut->delete();

       return redirect('/hut?flagz='.$FLAGZ)->with(['judul' => $judul, 'flagz' => $FLAGZ ])->with('statusHapus', 'Data '.$hut->NO_BUKTI.' berhasil dihapus');

    }


    public function cetak(Hut $hut)
    {
        $no_hut = $hut->NO_BUKTI;

        $file     = 'hutc';
        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

        $query = DB::SELECT("SELECT hut.NO_BUKTI, hut.TGL, hut.KODES, hut.NAMAS, hut.BACNO, hut.BNAMA,
                                    hutd.NO_FAKTUR, hutd.TOTAL,
                                    hutd.BAYAR, hutd.SISA
                            FROM hut, hutd
                            WHERE hut.NO_BUKTI='$no_hut' AND hut.NO_BUKTI = hutd.NO_BUKTI
                            ;
		");


        $data = [];

        foreach ($query as $key => $value) {
            array_push($data, array(
                'NO_BUKTI' => $query[$key]->NO_BUKTI,
                'TGL'      => $query[$key]->TGL,
                'KODES'    => $query[$key]->KODES,
                'NAMAS'    => $query[$key]->NAMAS,
                'BACNO'    => $query[$key]->BACNO,
                'BNAMA'    => $query[$key]->BNAMA,
                'NO_FAKTUR'       => $query[$key]->NO_FAKTUR,
                'TOTAL'    => $query[$key]->TOTAL,
                'BAYAR'    => $query[$key]->BAYAR,
                'SISA'    => $query[$key]->SISA
            ));
        }

        DB::SELECT("UPDATE HUT SET POSTED=1 WHERE NO_BUKTI='$no_hut'");

        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I");
    }

    public function getDetailHut(){

        $no_bukti = $_GET['no_bukti'];
        $result = DB::table('hutd')->where('NO_BUKTI', $no_bukti)->get();

        return response()->json($result);;
    }

    public function browseuang(Request $request)
    {
        //	$beli = DB::table('beli')->select('NO_BUKTI', 'TGL', 'KODES','NAMAS', 'ALAMAT','KOTA', 'PERB','PERBB', 'SISA' )->where('PERB', '<>' ,'PERBB')->where('LNS', '<>',1)->where('GOL', 'Y')->orderBy('KODES', 'ASC')->get();
        $filterkodes = '';

		$CBG = Auth::user()->CBG;

		if($request->KODES)
		{

			// $filterkodes = " WHERE SISA <> 0 AND KODES='".$request->KODES."' ";
			$filterkodes = " KODES='".$request->KODES."' ";
		}

		$beli = DB::SELECT("SELECT NO_BUKTI, TGL, KODES,
		            NAMAS, TOTAL, BAYAR, SISA,NETT from beli
                    WHERE $filterkodes
                    AND SISA <> 0
                    ORDER BY NO_BUKTI ");

        return response()->json($beli);
    }

    public function posting_byr_hutang(Request $request)
    {
        if (! $request->isMethod('post')) {
            return response()->json(['error' => 'Method Not Allowed'], 405);
        }

        $data = $request->input('posted');

        if (! $data) {
            return response()->json(['error' => 'Tidak ada data yang dikirim'], 400);
        }

        foreach ($data as $id => $posted) {
            DB::table('hut')->where('NO_ID', $id)->update(['POSTED' => $posted]);
        }

        return response()->json(['message' => 'Status berhasil diperbarui']);
    }
}
