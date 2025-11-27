<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
// ganti 1

use App\Models\OTransaksi\Hterima;
use App\Models\OTransaksi\HterimaDetail;
use App\Models\Master\Sup;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use DB;
use Carbon\Carbon;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";
use PHPJasperXML;

// ganti 2
class HterimaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resbelinse
     */
    var $judul = '';
    var $FLAGZ = '';

    function setFlag(Request $request)
    {
        if ( $request->flagz == 'TA' ) {
            $this->judul = "Stock Terima";
        } else if ( $request->flagz == 'MT' ) {
            $this->judul = "Koreksi Stock Mutasi";
        } else if ( $request->flagz == 'PTH' ) {
            $this->judul = "Posting Tetima Hijau";
        }

        $this->FLAGZ = $request->flagz;

    }

    public function index(Request $request)
    {
        $this->setFlag($request);
        if ($this->FLAGZ == 'TA') {
            $view = "otransaksi_hterima.index";
        } elseif($this->FLAGZ == 'MT') {
            $view = "otransaksi_hterima.index";
        } elseif($this->FLAGZ == 'PTH') {
            $view = "otransaksi_hterima.index_posting";
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

        $hterima = DB::SELECT("SELECT distinct hterima.NO_BUKTI , hterima.KODES, hterima.NAMAS,
		                  hterima.ALAMAT, hterima.KOTA from hterima, hterimad
                          WHERE hterima.NO_BUKTI = hterimad.NO_BUKTI  AND CBG = '$CBG'
                          AND hterimad.SISA > 0	");
        return response()->json($hterima);
    }

    public function browseuang(Request $request)
    {
        $CBG = Auth::user()->CBG;

		$hterima = DB::SELECT("SELECT NO_BUKTI,TGL,  KODES, NAMAS, TOTAL,  BAYAR,
                        (TOTAL-BAYAR) AS SISA, ALAMAT, KOTA from hterima
		                WHERE LNS <> 1 AND CBG = '$CBG' ORDER BY NO_BUKTI; ");

        return response()->json($hterima);
    }


	public function index_posting(Request $request)
    {

        return view('otransaksi_hterima.post');
    }

	//SHELVI

	public function browse_detail(Request $request)
    {
		$filterbukti = '';
		if($request->NO_PO)
		{

			$filterbukti = " WHERE a.NO_BUKTI='".$request->NO_PO."' AND a.KD_BRG = b.KD_BRG ";
		}
		$hterimad = DB::SELECT("SELECT a.REC, a.KD_BRG, a.NA_BRG, a.SATUAN , a.QTY, a.HARGA, a.KIRIM, a.SISA,
                                b.SATUAN AS SATUAN_PO, a.QTY AS QTY_PO, '1' AS X
                            from hterimad a, brg b
                            $filterbukti ORDER BY NO_BUKTI ");


		return response()->json($hterimad);
	}


    public function browse_detail2(Request $request)
    {
		$filterbukti = '';
		if($request->NO_PO)
		{

			$filterbukti = " WHERE NO_BUKTI='".$request->NO_PO."' AND a.KD_BRG = b.KD_BRG ";
		}
		$hterimad = DB::SELECT("SELECT a.REC, a.KD_BRG, a.NA_BRG, a.SATUAN , a.QTY, a.HARGA, a.KIRIM, a.SISA,
                                b.SATUAN AS SATUAN_PO, a.QTY AS QTY_PO, '1' AS X
                            from hterimad a, brg b
                            $filterbukti ORDER BY NO_BUKTI ");


		return response()->json($hterimad);
	}
    // ganti 4



    public function getHterima(Request $request)
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

        $hterima = DB::SELECT("SELECT * from hterima
                            WHERE PER='$periode' AND FLAG = '$FLAGZ' AND CBG = '$CBG'
                            ORDER BY NO_BUKTI ");


        // ganti 6

        return Datatables::of($hterima)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                if (Auth::user()->divisi=="programmer" )
				{
                    //CEK POSTED di index dan edit

                    // url untuk delete di index
                    $url = "'".url("hterima/delete/" . $row->NO_ID . "/?flagz=" . $row->FLAG)."'";
                    // batas

                    $btnEdit =   ($row->POSTED == 1) ? ' onclick= "alert(\'Transaksi ' . $row->NO_BUKTI . ' sudah diposting!\')" href="#" ' : ' href="hterima/edit/?idx=' . $row->NO_ID . '&tipx=edit&flagz=' . $row->FLAG . '&judul=' . $this->judul . '"';
                    $btnDelete = ($row->POSTED == 1) ? ' onclick= "alert(\'Transaksi ' . $row->NO_BUKTI . ' sudah diposting!\')" href="#" ' : ' onclick="deleteRow('.$url.')" ';


                    $btnPrivilege =
                        '
                                <a class="dropdown-item" ' . $btnEdit . '>
                                <i class="fas fa-edit"></i>
                                    Edit
                                </a>
                                <a class="dropdown-item btn btn-danger" href="hterima/cetak/' . $row->NO_ID . '">
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
     * @return \Illuminate\Http\Resbelinse
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

        $datakr = DB::table('hkirim')->where('NO_BUKTI', $request['NO_KIRIM'])->first();
        $CBGKR = $datakr->CBG;

        $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];

        $bulan    = session()->get('periode')['bulan'];
        $tahun    = substr(session()->get('periode')['tahun'], -2);

        $query = DB::table('hterima')->select('NO_BUKTI')->where('PER', $periode)->where('FLAG', $FLAGZ)->where('CBG', $CBG)
                ->orderByDesc('NO_BUKTI')->limit(1)->get();

        if ($query != '[]') {
            $query = substr($query[0]->NO_BUKTI, -4);
            $query = str_pad($query + 1, 4, 0, STR_PAD_LEFT);
            $no_bukti = $FLAGZ . $CBG . $tahun . $bulan . '-' . $query;
        } else {
            $no_bukti = $FLAGZ . $CBG . $tahun . $bulan . '-0001';
        }

        $hterima = Hterima::create(
            [
                'NO_BUKTI'         => $no_bukti,
                'TGL'              => date('Y-m-d', strtotime($request['TGL'])),
                'PER'              => $periode,
                'FLAG'             => $FLAGZ,
                'NO_KIRIM'         => ($request['NO_KIRIM']==null) ? "" : $request['NO_KIRIM'],
                'NOTES'            => ($request['NOTES']==null) ? "" : $request['NOTES'],
                'CBG_DARI'         => $CBGKR,
                'TOTAL_QTY'        => (float) str_replace(',', '', $request['TTOTAL_QTY']),
                'USRNM'            => Auth::user()->username,
                'TG_SMP'           => Carbon::now(),
				'created_by'       => Auth::user()->username,
				'CBG'              => $CBG,
            ]
        );


		$REC        = $request->input('REC');
		$KD_BRG	    = $request->input('KD_BRG');
		$NA_BRG	    = $request->input('NA_BRG');
		$SATUAN	    = $request->input('SATUAN');
		$QTY	    = $request->input('QTY');
		$QTY1	    = $request->input('QTY1');
		$HARGA	    = $request->input('HARGA');
		$TOTAL	    = $request->input('TOTAL');
		$KET	    = $request->input('KET');

        // Check jika value detail ada/tidak
        if ($REC) {
            foreach ($REC as $key => $value) {
                // Declare new data di Model
                $detail    = new HterimaDetail;

                // Insert ke Database
                $detail->NO_BUKTI    = $no_bukti;
                $detail->REC         = $REC[$key];
                $detail->PER         = $periode;
                $detail->FLAG        = $FLAGZ;
                $detail->CBG         = $CBG;
				$detail->KD_BRG	     = ($KD_BRG[$key]==null) ? "" :  $KD_BRG[$key];
				$detail->NA_BRG	     = ($NA_BRG[$key]==null) ? "" :  $NA_BRG[$key];
				$detail->SATUAN	     = ($SATUAN[$key]==null) ? "" :  $SATUAN[$key];
				$detail->QTY	     = (float) str_replace(',', '', $QTY[$key]);
				$detail->QTY1	     = (float) str_replace(',', '', $QTY1[$key]);
				$detail->HARGA	     = (float) str_replace(',', '', $HARGA[$key]);
				$detail->TOTAL	     = (float) str_replace(',', '', $TOTAL[$key]);
				$detail->KET	     = ($KET[$key]==null) ? "" :  $KET[$key];
                $detail->save();
            }
        }

        $variablell = DB::select('call hterimains(?)', array($no_bukti));

		$no_buktix = $no_bukti;

		$hterima = Hterima::where('NO_BUKTI', $no_buktix )->first();


        DB::SELECT("UPDATE hterima,  hterimad
                            SET  hterimad.ID =  hterima.NO_ID  WHERE  hterima.NO_BUKTI =  hterimad.NO_BUKTI
							AND  hterima.NO_BUKTI='$no_buktix';");



        return redirect('/hterima/edit/?idx=' . $hterima->NO_ID . '&tipx=edit&flagz=' . $this->FLAGZ . '&judul=' . $this->judul . '');

    }

   public function edit( Request $request , Hterima $hterima)
    {

        $pilihcbg = DB::table('compan')->select('EXT')->orderBy('EXT', 'ASC')->get();

		$per = session()->get('periode')['bulan'] . '/' . session()->get('periode')['tahun'];


        // $cekperid = DB::SELECT("SELECT POSTED from perid WHERE PERIO='$per'");
        // if ($cekperid[0]->POSTED==1)
        // {
        //     return redirect('/hterima')
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

		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from hterima
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


		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from hterima
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

		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from hterima
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

		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from hterima
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

    		$bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from hterima
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
			$hterima = Hterima::where('NO_ID', $idx )->first();
	     }
		 else
		 {
				$hterima = new Hterima;
                $hterima->TGL = Carbon::now();


		 }

        $no_bukti = $hterima->NO_BUKTI;
        $hterimaDetail = DB::table('hterimad')->where('NO_BUKTI', $no_bukti)->orderBy('REC')->get();

		$data = [
            'header'        => $hterima,
			'detail'        => $hterimaDetail

        ];

 		$sup = DB::SELECT("SELECT KODES, CONCAT(NAMAS,'-',KOTA) AS NAMAS FROM SUP
		                 ORDER BY NAMAS ASC" );


         return view('otransaksi_hterima.edit', $data)->with(['sup' => $sup])
		 ->with(['tipx' => $tipx, 'idx' => $idx, 'flagz' => $this->FLAGZ, 'judul'=> $this->judul ])->with(['pilihcbg' => $pilihcbg]);


    }

  /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Resbelinse
     */

    // ganti 18

    public function update(Request $request, Hterima $hterima)
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

        $variablell = DB::select('call hterimadel(?)', array($hterima['NO_BUKTI']));

        $CBG = Auth::user()->CBG;

        $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];


        $hterima->update(
            [
                'TGL'              => date('Y-m-d', strtotime($request['TGL'])),
                'NOTES'            => ($request['NOTES']==null) ? "" : $request['NOTES'],
                'NO_KIRIM'         => ($request['NO_KIRIM']==null) ? "" : $request['NO_KIRIM'],
                'CBG_DARI'         => ($request['CBG_TUJU']==null) ? "" : $request['CBG_TUJU'],
                'TOTAL_QTY'        => (float) str_replace(',', '', $request['TTOTAL_QTY']),
				'USRNM'            => Auth::user()->username,
                'TG_SMP'           => Carbon::now(),
				'updated_by'       => Auth::user()->username,
                'FLAG'             => $FLAGZ,
                'CBG'              => $CBG,
            ]
        );

		$no_buktix = $hterima->NO_BUKTI;

        // Update Detail
        $length = sizeof($request->input('REC'));
        $NO_ID  = $request->input('NO_ID');

        $REC    = $request->input('REC');

        $KD_BRG	= $request->input('KD_BRG');
		$NA_BRG	= $request->input('NA_BRG');
		$SATUAN	= $request->input('SATUAN');
		$QTY	= $request->input('QTY');
		$QTY1	= $request->input('QTY1');
		$HARGA	= $request->input('HARGA');
		$TOTAL	= $request->input('TOTAL');
		$KET	= $request->input('KET');
		$CBG_TUJU	= $request->input('CBG_TUJU');

        $query = DB::table('hterimad')->where('NO_BUKTI', $request->NO_BUKTI)->whereNotIn('NO_ID',  $NO_ID)->delete();

        // Update / Insert
        for ($i = 0; $i < $length; $i++) {
            // Insert jika NO_ID baru
            if ($NO_ID[$i] == 'new') {
                $insert = HterimaDetail::create(
                    [
                        'NO_BUKTI'   => $request->NO_BUKTI,
                        'REC'        => $REC[$i],
                        'PER'        => $periode,
                        'CBG'        => $CBG,
                        'FLAG'       => $this->FLAGZ,
                        'KD_BRG'     => ($KD_BRG[$i]==null) ? "" :  $KD_BRG[$i],
                        'NA_BRG'     => ($NA_BRG[$i]==null) ? "" : $NA_BRG[$i],
                        'SATUAN'     => ($SATUAN[$i]==null) ? "" : $SATUAN[$i],
						'KET'     	 => ($KET[$i]==null) ? "" : $KET[$i],
						'QTY'        => (float) str_replace(',', '', $QTY[$i]),
						'QTY1'        => (float) str_replace(',', '', $QTY1[$i]),
						'HARGA'        => (float) str_replace(',', '', $HARGA[$i]),
						'TOTAL'        => (float) str_replace(',', '', $TOTAL[$i]),

                    ]
                );
            } else {
                // Update jika NO_ID sudah ada
                $upsert = HterimaDetail::updateOrCreate(
                    [
                        'NO_BUKTI'  => $request->NO_BUKTI,
                        'NO_ID'     => (int) str_replace(',', '', $NO_ID[$i])
                    ],

                    [
                        'REC'        => $REC[$i],

                        'KD_BRG'     => ($KD_BRG[$i]==null) ? "" :  $KD_BRG[$i],
                        'NA_BRG'     => ($NA_BRG[$i]==null) ? "" : $NA_BRG[$i],
                        'SATUAN'     => ($SATUAN[$i]==null) ? "" : $SATUAN[$i],
						'KET'     	 => ($KET[$i]==null) ? "" : $KET[$i],
						'QTY'        => (float) str_replace(',', '', $QTY[$i]),
						'QTY1'        => (float) str_replace(',', '', $QTY1[$i]),
						'HARGA'        => (float) str_replace(',', '', $HARGA[$i]),
						'TOTAL'        => (float) str_replace(',', '', $TOTAL[$i]),
                        'FLAG'       => $this->FLAGZ,
                        'PER'        => $periode,
                        'CBG'        => $CBG,
                    ]
                );
            }
        }

 		$hterima =Hterima::where('NO_BUKTI', $no_buktix )->first();

        $no_bukti = $hterima->NO_BUKTI;

        $variablell = DB::select('call hterimains(?)', array($hterima['NO_BUKTI']));

        DB::SELECT("UPDATE hterima,  hterimad
                    SET  hterimad.ID =  hterima.NO_ID  WHERE  hterima.NO_BUKTI =  hterimad.NO_BUKTI
                    AND  hterima.NO_BUKTI='$no_bukti';");

        return redirect('/hterima/edit/?idx=' . $hterima->NO_ID . '&tipx=edit&flagz=' . $this->FLAGZ . '&judul=' . $this->judul . '');


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Resbelinse
     */

    // ganti 22

    public function destroy(Request $request, Hterima $hterima)
    {

		$this->setFlag($request);
        $FLAGZ = $this->FLAGZ;
        $judul = $this->judul;

		$per = session()->get('periode')['bulan'] . '/' . session()->get('periode')['tahun'];
        $cekperid = DB::SELECT("SELECT POSTED from perid WHERE PERIO='$per'");
        if ($cekperid[0]->POSTED==1)
        {
            return redirect()->route('hterima')
                ->with('status', 'Maaf Periode sudah ditutup!')
                ->with(['judul' => $this->judul, 'flagz' => $this->FLAGZ]);
        }

        $variablell = DB::select('call hterimadel(?)', array($hterima['NO_BUKTI']));

        $deleteHterima = Hterima::find($hterima->NO_ID);

        $deleteHterima->delete();

       return redirect('/hterima?flagz='.$FLAGZ)->with(['judul' => $judul, 'flagz' => $FLAGZ ])->with('statusHapus', 'Data '.$hterima->NO_BUKTI.' berhasil dihapus');


    }

    public function cetak(Hterima $hterima, Request $request)
    {
        $no_hterima = $hterima->NO_BUKTI;

        $flagz1 = $hterima->FLAG;
        $judul ='';

        if ( $flagz1 =='TA')
        {
                $judul ='Stock Terima Hijau';

        }

        if ( $flagz1 =='PTH')
        {
                $judul ='Posting Terima Hijau';
        }

        $file     = 'hdhcTerima';
        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

        $query = DB::SELECT(
            "SELECT hterima.NO_BUKTI, hterima.CBG_DARI, hterima.TGL, 
				hterimad.KD_BRG, hterimad.NA_BRG, hterimad.QTY, hterimad.HARGA, hterimad.TOTAL
			FROM hterima, hterimad
            WHERE hterima.NO_BUKTI='$no_hterima' AND hterima.NO_BUKTI = hterimad.NO_BUKTI"
        );

        $data = [];

        foreach ($query as $key => $value) {
            array_push($data, array(
                'NO_BUKTI' => $query[$key]->NO_BUKTI,
                'TGL'      => $query[$key]->TGL,
                'CBG_DARI' => $query[$key]->CBG_DARI,
                'KD_BRG'   => $query[$key]->KD_BRG,
                'NA_BRG'   => $query[$key]->NA_BRG,
                'QTY'      => $query[$key]->QTY,
                'QTY1'     => $query[$key]->QTY1,
                'HARGA'    => $query[$key]->HARGA,
                'TOTAL'    => $query[$key]->TOTAL
            ));
        }

        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I");

    }



	 public function posting(Request $request)
    {


    }


	public function getDetailHterima(){

        $no_bukti = $_GET['no_bukti'];
        $result = DB::table('hterimad')->where('NO_BUKTI', $no_bukti)->get();

        return response()->json($result);;
    }

    public function posting_terima_hijau(Request $request)
    {
        if (! $request->isMethod('post')) {
            return response()->json(['error' => 'Method Not Allowed'], 405);
        }

        $data = $request->input('posted');

        if (! $data) {
            return response()->json(['error' => 'Tidak ada data yang dikirim'], 400);
        }

        foreach ($data as $id => $posted) {
            DB::table('hterima')->where('NO_ID', $id)->update(['POSTED' => $posted]);
        }

        return response()->json(['message' => 'Status berhasil diperbarui']);
    }

}