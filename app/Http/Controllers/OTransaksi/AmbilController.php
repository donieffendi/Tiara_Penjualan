<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
// ganti 1

use App\Models\OTransaksi\Ambil;
use App\Models\OTransaksi\AmbilDetail;
use App\Models\Master\Sup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use DataTables;
use Auth;
use DB;
use Carbon\Carbon;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";
use PHPJasperXML;

// ganti 2
class AmbilController extends Controller
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
        if ( $request->flagz == 'AM' ) {
            $this->judul = "Ambil Barang";
        }elseif ( $request->flagz == 'PAM' ){
            $this->judul = "Posting Ambil Barang";
        }

        $this->FLAGZ = $request->flagz;

    }

    public function index(Request $request)
    {
        $this->setFlag($request);
        if ($this->FLAGZ == 'AM') {
            $view = "otransaksi_ambil.index";
        } elseif($this->FLAGZ == 'PAM') {
            $view = "otransaksi_ambil.index_posting";
        }
        return view($view)->with([
            'judul' => $this->judul,
            'flagz' => $this->FLAGZ,
        ]);

    }
	public function browse(Request $request)
    {
        $golz = $request->GOL;

        $CBG = Auth::user()->CBG;

        $ambil = DB::SELECT("SELECT distinct PO.NO_BUKTI , PO.KODEC, PO.NAMAC,
		                  PO.ALAMAT, PO.KOTA from ambil, ambild
                          WHERE PO.NO_BUKTI = POD.NO_BUKTI AND PO.GOL ='$golz' AND CBG = '$CBG'
                          AND POD.SISA > 0	");
        return response()->json($ambil);
    }

    public function browseuang(Request $request)
    {
        $CBG = Auth::user()->CBG;

		$ambil = DB::SELECT("SELECT NO_BUKTI,TGL,  KODEC, NAMAC, TOTAL,  BAYAR,
                        (TOTAL-BAYAR) AS SISA, ALAMAT, KOTA from ambil
		                WHERE LNS <> 1 AND CBG = '$CBG' ORDER BY NO_BUKTI; ");

        return response()->json($ambil);
    }

    public function browse_jual(Request $request)
    {
    	// ling
        // $CBG       = Auth::user()->CBG;
        // if($CBG == 'Z'){
        //     $jual = DB::connection('tgz')->SELECT("SELECT distinct juald2.NO_BUKTI , juald2.TGL
        //                     from juald2 ");
        // }elseif($CBG == 'M'){
        //     $jual = DB::connection('tmm')->SELECT("SELECT distinct juald2.NO_BUKTI , juald2.TGL
        //                     from juald2 ");
        // }elseif($CBG == 'S'){
        //     $jual = DB::connection('sop')->SELECT("SELECT distinct juald2.NO_BUKTI , juald2.TGL
        //                     from juald2 ");
        // };


        $jual = DB::SELECT("SELECT distinct xjual.NO_BUKTI , xjual.TGL
                            from xjual ");

        return response()->json($jual);
    }

    public function browse_juald(Request $request)
    {
        // ling
		// $CBG       = Auth::user()->CBG;
        // if($CBG == 'Z'){
        //     $jual = DB::connection('tgz')->SELECT("SELECT juald2d.REC, juald2d.KD_BRGH AS KD_BRG, juald2d.NA_BRGH AS NA_BRG, juald2d.SATUAN , juald2d.QTY
        //                                             from juald2d
        //                                             where NO_BUKTI='".$request->nobukti."'");
        // }elseif($CBG == 'M'){
        //     $jual = DB::connection('tmm')->SELECT("SELECT juald2d.REC, juald2d.KD_BRGH AS KD_BRG, juald2d.NA_BRGH AS NA_BRG, juald2d.SATUAN , juald2d.QTY
        //                                             from juald2d
        //                                             where NO_BUKTI='".$request->nobukti."'");
        // }elseif($CBG == 'S'){
        //     $jual = DB::connection('sop')->SELECT("SELECT juald2d.REC, juald2d.KD_BRGH AS KD_BRG, juald2d.NA_BRGH AS NA_BRG, juald2d.SATUAN , juald2d.QTY
        //                                             from juald2d
        //                                             where NO_BUKTI='".$request->nobukti."'");
        // };

        $juald = DB::SELECT("SELECT xjuald.REC, xjuald.KD_BRG, xjuald.NA_BRG, xjuald.SATUAN , xjuald.QTY
                            from xjuald
                            where NO_BUKTI='".$request->nobukti."'");

        return response()->json($juald);
    }


	public function index_posting(Request $request)
    {

        return view('otransaksi_ambil.post');
    }

	//SHELVI

	public function browse_detail(Request $request)
    {
		$filterbukti = '';
		if($request->NO_PO)
		{

			$filterbukti = " WHERE a.NO_BUKTI='".$request->NO_PO."' AND a.KD_BRG = b.KD_BRG ";
		}
		$ambild = DB::SELECT("SELECT a.REC, a.KD_BRG, a.NA_BRG, a.SATUAN , a.QTY, a.HARGA, a.KIRIM, a.SISA,
                                b.SATUAN AS SATUAN_PO, a.QTY AS QTY_PO, '1' AS X
                            from ambild a, brg b
                            $filterbukti ORDER BY NO_BUKTI ");


		return response()->json($ambild);
	}


    public function browse_detail2(Request $request)
    {
		$filterbukti = '';
		if($request->NO_PO)
		{

			$filterbukti = " WHERE NO_BUKTI='".$request->NO_PO."' AND a.KD_BRG = b.KD_BRG ";
		}
		$ambild = DB::SELECT("SELECT a.REC, a.KD_BRG, a.NA_BRG, a.SATUAN , a.QTY, a.HARGA, a.KIRIM, a.SISA,
                                b.SATUAN AS SATUAN_PO, a.QTY AS QTY_PO, '1' AS X
                            from ambild a, brg b
                            $filterbukti ORDER BY NO_BUKTI ");


		return response()->json($ambild);
	}
    // ganti 4



    public function getAmbil(Request $request)
    {
        // ganti 5

       if ($request->session()->has('periode')) {
            $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
        } else {
            $periode = '';
        }

		$this->setFlag($request);
        $FLAGZ = $this->FLAGZ;
        $judul = $this->judul;

        $CBG = Auth::user()->CBG;

        $ambil = DB::SELECT("SELECT * from ambil  WHERE PER='$periode' and FLAG ='$this->FLAGZ'
                            AND CBG = '$CBG'
                            ORDER BY NO_BUKTI ");


        // ganti 6

        return Datatables::of($ambil)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                if (Auth::user()->divisi=="programmer" )
				{
                    //CEK POSTED di index dan edit

                    // url untuk delete di index
                    $url = "'".url("ambil/delete/" . $row->NO_ID . "/?flagz=" . $row->FLAG)."'";
                    // batas

                    $btnEdit =   ($row->POSTED == 1) ? ' onclick= "alert(\'Transaksi ' . $row->NO_BUKTI . ' sudah diposting!\')" href="#" ' : ' href="ambil/edit/?idx=' . $row->NO_ID . '&tipx=edit&flagz=' . $row->FLAG . '&judul=' . $this->judul . '"';
                    $btnDelete = ($row->POSTED == 1) ? ' onclick= "alert(\'Transaksi ' . $row->NO_BUKTI . ' sudah diposting!\')" href="#" ' : ' onclick="deleteRow('.$url.')" ';


                    $btnPrivilege =
                        '
                                <a class="dropdown-item" ' . $btnEdit . '>
                                <i class="fas fa-edit"></i>
                                    Edit
                                </a>
                                <a class="dropdown-item btn btn-danger" href="ambil/cetak/' . $row->NO_ID . '">
                                    <i class="fa fa-print" aria-hidden="true"></i>
                                    Print
                                </a>
                                <hr></hr>
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


                            ' . $btnPrivilege . '
                        </div>
                    </div>
                    ';

                return $actionBtn;
            })


			->addColumn('cek', function ($row) {
                return
                    '
                    <input type="checkbox" name="cek[]" class="form-control cek" ' . (($row->POSTED == 1) ? "checked" : "") . '  value="' . $row->NO_ID . '" ' . (($row->POSTED == 2) ? "disabled" : "") . '></input>
                    ';

            })

            ->rawColumns(['action','cek'])
            ->make(true);
    }


//////////////////////////////////////////////////////////////////////////////////

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
                'TGL'      => 'required'

            ]
        );

        //////     nomer otomatis
		$this->setFlag($request);
        $FLAGZ = $this->FLAGZ;
        $judul = $this->judul;

        $CBG = Auth::user()->CBG;

        $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];

        $bulan    = session()->get('periode')['bulan'];
        $tahun    = substr(session()->get('periode')['tahun'], -2);

        $query = DB::table('ambil')->select('NO_BUKTI')->where('PER', $periode)->where('FLAG', $this->FLAGZ)->where('CBG', $CBG)
                ->orderByDesc('NO_BUKTI')->limit(1)->get();

        if ($query != '[]') {
            $query = substr($query[0]->NO_BUKTI, -4);
            $query = str_pad($query + 1, 4, 0, STR_PAD_LEFT);
            $no_bukti = $this->FLAGZ . $CBG . $tahun . $bulan . '-' . $query;
        } else {
            $no_bukti = $this->FLAGZ . $CBG . $tahun . $bulan . '-0001';
        }

        $ambil = Ambil::create(
            [
                'NO_BUKTI'         => $no_bukti,
                'TGL'              => date('Y-m-d', strtotime($request['TGL'])),
                'PER'              => $periode,
                'FLAG'             => $this->FLAGZ,
                'NO_JUAL'            => ($request['NO_JUAL']==null) ? "" : $request['NO_JUAL'],
                'NOTES'            => ($request['NOTES']==null) ? "" : $request['NOTES'],
				'KODEC'            => ($request['KODEC'] == null) ? "" : $request['KODEC'],
                'NAMAC'            => ($request['NAMAC'] == null) ? "" : $request['NAMAC'],
                'ALAMAT'           => ($request['ALAMAT'] == null) ? "" : $request['ALAMAT'],
                'KOTA'             => ($request['KOTA'] == null) ? "" : $request['KOTA'],
                'HARI'            => (float) str_replace(',', '', $request['HARI']),
                'TOTAL_QTY'        => (float) str_replace(',', '', $request['TTOTAL_QTY']),
                'USRNM'            => Auth::user()->username,
                'TG_SMP'           => Carbon::now(),
				'created_by'       => Auth::user()->username,
				'CBG'              => $CBG,
            ]
        );


		$REC        = $request->input('REC');
		$KD_BRG	= $request->input('KD_BRG');
		$NA_BRG	= $request->input('NA_BRG');
		// $SATUAN	= $request->input('SATUAN');
		// $QTYC	= $request->input('QTYC');
		// $QTYR	= $request->input('QTYR');
		$QTY	= $request->input('QTY');
		$KET	= $request->input('KET');

        // Check jika value detail ada/tidak
        if ($REC) {
            foreach ($REC as $key => $value) {
                // Declare new data di Model
                $detail    = new AmbilDetail;

                // Insert ke Database
                $detail->NO_BUKTI    = $no_bukti;
                $detail->REC         = $REC[$key];
                $detail->PER         = $periode;
                $detail->FLAG        = $this->FLAGZ;
				$detail->KD_BRG	     = ($KD_BRG[$key]==null) ? "" :  $KD_BRG[$key];
				$detail->NA_BRG	     = ($NA_BRG[$key]==null) ? "" :  $NA_BRG[$key];
				// $detail->SATUAN	     = ($SATUAN[$key]==null) ? "" :  $SATUAN[$key];
				// $detail->QTYC	     = (float) str_replace(',', '', $QTYC[$key]);
				// $detail->QTYR	     = (float) str_replace(',', '', $QTYR[$key]);
				$detail->QTY	     = (float) str_replace(',', '', $QTY[$key]);
				$detail->KET	     = ($KET[$key]==null) ? "" :  $KET[$key];
				$detail->CBG	     = $CBG;
                $detail->save();
            }
        }

		$variablell = DB::select('call ambilins(?)', array($no_bukti));

		$no_buktix = $no_bukti;

		$ambil = Ambil::where('NO_BUKTI', $no_buktix )->first();

        DB::SELECT("UPDATE AMBIL, CUST
                    SET AMBIL.NAMAC = CUST.NAMAC, AMBIL.ALAMAT = CUST.ALAMAT, AMBIL.KOTA = CUST.KOTA, AMBIL.PKP=CUST.PKP, AMBIL.HARI = CUST.HARI  WHERE AMBIL.KODEC = CUST.KODEC
                    AND AMBIL.NO_BUKTI='$no_buktix';");

        DB::SELECT("UPDATE ambil,  ambild
                            SET  ambild.ID =  ambil.NO_ID  WHERE  ambil.NO_BUKTI =  ambild.NO_BUKTI
							AND  ambil.NO_BUKTI='$no_buktix';");

        // // Data yang dikirim ke API/Kirim request ke API

        $data = [
            "status" => "save",
            "outlet" => $request->CBG, // Ambil dari request
            "kitir" => $request->NO_JUAL,
            "no_ambil" => $no_buktix
        ];
        
        $response = Http::asJson()->withHeaders([
            'Content-Type' => 'application/json'
        ])->post("http://192.168.0.2/admin-apf-app/public/api/tukar_hadiah", $data);

        // return redirect('/ambil/edit/?idx=' . $ambil->NO_ID . '&tipx=edit&flagz=' . $this->FLAGZ . '&judul=' . $this->judul . '');
        return redirect('/ambil?flagz='.$FLAGZ)->with(['judul' => $judul, 'flagz' => $FLAGZ ]);

    }

   public function edit( Request $request , Ambil $ambil)
    {


		$per = session()->get('periode')['bulan'] . '/' . session()->get('periode')['tahun'];


        // $cekperid = DB::SELECT("SELECT POSTED from perid WHERE PERIO='$per'");
        // if ($cekperid[0]->POSTED==1)
        // {
        //     return redirect('/ambil')
		// 	       ->with('status', 'Maaf Periode sudah ditutup!')
        //            ->with(['judul' => $judul, 'flagz' => $FLAGZ]);
        // }

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

		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from ambil
		                 where PER ='$per' and FLAG ='$this->FLAGZ'
						 and NO_BUKTI = '$buktix' AND CBG = '$CBG'
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


		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from ambil
		                 where PER ='$per'
						 and FLAG ='$this->FLAGZ' AND CBG = '$CBG'
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

		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from ambil
		             where PER ='$per'
					 and FLAG ='$this->FLAGZ' AND CBG = '$CBG'
                     and NO_BUKTI <
					 '$buktix' ORDER BY NO_BUKTI DESC LIMIT 1" );


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

		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from ambil
		             where PER ='$per'
					 and FLAG ='$this->FLAGZ' AND CBG = '$CBG'
                     and NO_BUKTI >
					 '$buktix' ORDER BY NO_BUKTI ASC LIMIT 1" );

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

    		$bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from ambil
						where PER ='$per'
						and FLAG ='$this->FLAGZ' AND CBG = '$CBG'
		              ORDER BY NO_BUKTI DESC  LIMIT 1" );

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
			$ambil = Ambil::where('NO_ID', $idx )->first();
	     }
		 else
		 {
				$ambil = new Ambil;
                $ambil->TGL = Carbon::now();


		 }

        $no_bukti = $ambil->NO_BUKTI;
        $ambilDetail = DB::table('ambild')->where('NO_BUKTI', $no_bukti)->orderBy('REC')->get();

		$data = [
            'header'        => $ambil,
			'detail'        => $ambilDetail

        ];

 		$sup = DB::SELECT("SELECT KODEC, CONCAT(NAMAC,'-',KOTA) AS NAMAC FROM CUST
		                 ORDER BY NAMAC ASC" );


         return view('otransaksi_ambil.edit', $data)->with(['sup' => $sup])
		 ->with(['tipx' => $tipx, 'idx' => $idx, 'flagz' => $this->FLAGZ, 'judul'=> $this->judul ]);


    }

  /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    // ganti 18

    public function update(Request $request, Ambil $ambil)
    {

        $this->validate(
            $request,
            [

                'TGL'      => 'required'
            ]
        );

		$this->setFlag($request);
        $FLAGZ = $this->FLAGZ;
        $judul = $this->judul;

        $variablell = DB::select('call ambildel(?)', array($ambil['NO_BUKTI']));

        $CBG = Auth::user()->CBG;

        $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];


        $ambil->update(
            [
                'TGL'              => date('Y-m-d', strtotime($request['TGL'])),
                'NO_JUAL'            => ($request['NO_JUAL']==null) ? "" : $request['NO_JUAL'],
                'NOTES'            => ($request['NOTES']==null) ? "" : $request['NOTES'],
                'KODEC'            => ($request['KODEC'] == null) ? "" : $request['KODEC'],
                'NAMAC'            => ($request['NAMAC'] == null) ? "" : $request['NAMAC'],
                'ALAMAT'           => ($request['ALAMAT'] == null) ? "" : $request['ALAMAT'],
                'KOTA'             => ($request['KOTA'] == null) ? "" : $request['KOTA'],
                'TOTAL_QTY'        => (float) str_replace(',', '', $request['TTOTAL_QTY']),
				'USRNM'            => Auth::user()->username,
                'TG_SMP'           => Carbon::now(),
				'updated_by'       => Auth::user()->username,
                'FLAG'             => $this->FLAGZ,
                'CBG'              => $CBG,
            ]
        );

		$no_buktix = $ambil->NO_BUKTI;

        // Update Detail
        $length = sizeof($request->input('REC'));
        $NO_ID  = $request->input('NO_ID');

        $REC    = $request->input('REC');

        $KD_BRG	= $request->input('KD_BRG');
		$NA_BRG	= $request->input('NA_BRG');
		// $SATUAN	= $request->input('SATUAN');
		// $QTYC	= $request->input('QTYC');
		// $QTYR	= $request->input('QTYR');
		$QTY	= $request->input('QTY');
		$KET	= $request->input('KET');

        $query = DB::table('ambild')->where('NO_BUKTI', $request->NO_BUKTI)->whereNotIn('NO_ID',  $NO_ID)->delete();

        // Update / Insert
        for ($i = 0; $i < $length; $i++) {
            // Insert jika NO_ID baru
            if ($NO_ID[$i] == 'new') {
                $insert = AmbilDetail::create(
                    [
                        'NO_BUKTI'   => $request->NO_BUKTI,
                        'REC'        => $REC[$i],
                        'PER'        => $periode,
                        'FLAG'       => $this->FLAGZ,
                        'KD_BRG'     => ($KD_BRG[$i]==null) ? "" :  $KD_BRG[$i],
                        'NA_BRG'     => ($NA_BRG[$i]==null) ? "" : $NA_BRG[$i],
                        // 'SATUAN'     => ($SATUAN[$i]==null) ? "" : $SATUAN[$i],
						'KET'     	 => ($KET[$i]==null) ? "" : $KET[$i],
                        // 'QTYC'      => (float) str_replace(',', '', $QTYC[$i]),
                        // 'QTYR'      => (float) str_replace(',', '', $QTYR[$i]),
						'QTY'        => (float) str_replace(',', '', $QTY[$i])

                    ]
                );
            } else {
                // Update jika NO_ID sudah ada
                $upsert = AmbilDetail::updateOrCreate(
                    [
                        'NO_BUKTI'  => $request->NO_BUKTI,
                        'NO_ID'     => (int) str_replace(',', '', $NO_ID[$i])
                    ],

                    [
                        'REC'        => $REC[$i],

                        'KD_BRG'     => ($KD_BRG[$i]==null) ? "" :  $KD_BRG[$i],
                        'NA_BRG'     => ($NA_BRG[$i]==null) ? "" : $NA_BRG[$i],
                        // 'SATUAN'     => ($SATUAN[$i]==null) ? "" : $SATUAN[$i],
						'KET'     	 => ($KET[$i]==null) ? "" : $KET[$i],
                        // 'QTYC'      => (float) str_replace(',', '', $QTYC[$i]),
                        // 'QTYR'      => (float) str_replace(',', '', $QTYR[$i]),
						'QTY'        => (float) str_replace(',', '', $QTY[$i]),
                        'FLAG'       => $this->FLAGZ,
                        'PER'        => $periode,
                    ]
                );
            }
        }

 		$ambil = Ambil::where('NO_BUKTI', $no_buktix )->first();

        $no_bukti = $ambil->NO_BUKTI;

        $variablell = DB::select('call ambilins(?)', array($ambil['NO_BUKTI']));

        DB::SELECT("UPDATE AMBIL, CUST
                    SET AMBIL.NAMAC = CUST.NAMAC, AMBIL.ALAMAT = CUST.ALAMAT, AMBIL.KOTA = CUST.KOTA, AMBIL.PKP=CUST.PKP, AMBIL.HARI = CUST.HARI  WHERE AMBIL.KODEC = CUST.KODEC
                    AND AMBIL.NO_BUKTI='$no_buktix';");


        DB::SELECT("UPDATE ambil,  ambild
                    SET  ambild.ID =  ambil.NO_ID  WHERE  ambil.NO_BUKTI =  ambild.NO_BUKTI
                    AND  ambil.NO_BUKTI='$no_bukti';");

        // return redirect('/ambil/edit/?idx=' . $ambil->NO_ID . '&tipx=edit&flagz=' . $this->FLAGZ . '&judul=' . $this->judul . '');
        return redirect('/ambil?flagz='.$FLAGZ)->with(['judul' => $judul, 'flagz' => $FLAGZ ]);


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    // ganti 22

    public function destroy(Request $request, Ambil $ambil)
    {

		$this->setFlag($request);
        $FLAGZ = $this->FLAGZ;
        $judul = $this->judul;

		$per = session()->get('periode')['bulan'] . '/' . session()->get('periode')['tahun'];
        $cekperid = DB::SELECT("SELECT POSTED from perid WHERE PERIO='$per'");
        if ($cekperid[0]->POSTED==1)
        {
            return redirect()->route('ambil')
                ->with('status', 'Maaf Periode sudah ditutup!')
                ->with(['judul' => $this->judul, 'flagz' => $this->FLAGZ]);
        }

        $variablell = DB::select('call ambildel(?)', array($ambil['NO_BUKTI']));

        $deleteAmbil = Ambil::find($ambil->NO_ID);

        $deleteAmbil->delete();

       return redirect('/ambil?flagz='.$FLAGZ)->with(['judul' => $judul, 'flagz' => $FLAGZ ])->with('statusHapus', 'Data '.$ambil->NO_BUKTI.' berhasil dihapus');


    }

    public function cetak(Ambil $ambil)
    {
        $no_ambil = $ambil->NO_BUKTI;

        // $kd_brg = strval($request->KD_BRG);
        // $kd_brgx = strval($kd_brg);

        $file     = 'ambilc';

        $flagz1 = $ambil->FLAG;
        $judul ='';

        if ( $flagz1 =='AM')
        {
                $judul ='Pengambilan Barang';

        }

        // if ( $flagz1 =='RB')
        // {
        //         $judul ='Retur Pembelian';
        // }

        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

        $query = DB::SELECT("SELECT ambil.NO_BUKTI, ambil.TGL, ambil.KODEC, ambil.NAMAC, ambil.TOTAL_QTY, ambil.NOTES, ambil.ALAMAT, ambil.NO_JUAL,
                                    ambil.KOTA, ambild.KD_BRG, ambild.NA_BRG, ambild.SATUAN, ambild.QTY, ambild.KET, ambil.USRNM
                            FROM ambil, ambild
                            WHERE ambil.NO_BUKTI='$no_ambil' AND ambil.NO_BUKTI = ambild.NO_BUKTI
                            ;
		");

                DB::SELECT("UPDATE ambil SET POSTED = 1 WHERE NO_BUKTI='$no_ambil';");

        $data = [];

        foreach ($query as $key => $value) {
            array_push($data, array(
                'NO_BUKTI' => $query[$key]->NO_BUKTI,
                'TGL'      => $query[$key]->TGL,
                'KODEC'    => $query[$key]->KODEC,
                'NAMAC'    => $query[$key]->NAMAC,
                'ALAMAT'    => $query[$key]->ALAMAT,
                'KOTA'    => $query[$key]->KOTA,
                'NOTES'    => $query[$key]->NOTES,
                'KD_BRG'    => $query[$key]->KD_BRG,

                'NA_BRG'    => $query[$key]->NA_BRG,
                // 'SATUAN'    => $query[$key]->SATUAN,
                'QTY'    => $query[$key]->QTY,
                'NO_JUAL'    => $query[$key]->NO_JUAL,
                'JUDUL'    => $judul,
                'USRNM'    => $query[$key]->USRNM,
            ));
        }

        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I");

    }



	 public function posting(Request $request)
    {


    }


	public function getDetailAmbil(){

        $no_bukti = $_GET['no_bukti'];
        $result = DB::table('ambild')->where('NO_BUKTI', $no_bukti)->get();

        return response()->json($result);;
    }
    public function posting_ambil_brg(Request $request)
    {
        if (! $request->isMethod('post')) {
            return response()->json(['error' => 'Method Not Allowed'], 405);
        }

        $data = $request->input('posted');

        if (! $data) {
            return response()->json(['error' => 'Tidak ada data yang dikirim'], 400);
        }

        foreach ($data as $id => $posted) {
            DB::table('ambil')->where('NO_ID', $id)->update(['POSTED' => $posted]);
        }

        return response()->json(['message' => 'Status berhasil diperbarui']);
    }

}