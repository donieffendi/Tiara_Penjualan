<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
// ganti 1

use App\Models\OTransaksi\Stockb;
use App\Models\OTransaksi\StockbDetail;
use App\Models\Master\Sup;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use DB;
use Carbon\Carbon;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";
use PHPJasperXML;

// ganti 2
class StockbController extends Controller
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
        if ($request->flagz == 'KZ') {
            $this->judul = "Koreksi Stock Bahan";
        } else if ($request->flagz == 'RZ') {
            $this->judul = "Retur Hadiah Outlet";
        } else if ($request->flagz == 'TZ') {
            $this->judul = "Terima Retur Hadiah Outlet";
        } else if ($request->flagz == '') {
            $this->judul = "";
        }
        $this->FLAGZ = $request->flagz;

    }

    public function index(Request $request)
    {


	    $this->setFlag($request);
        // ganti 3
        return view('otransaksi_stockb.index')->with(['judul' => $this->judul, 'flagz' => $this->FLAGZ]);


    }

		public function browse(Request $request)
    {
        $golz = $request->GOL;

        $CBG = Auth::user()->CBG;

        $stockb = DB::SELECT("SELECT distinct PO.NO_BUKTI , PO.KODES, PO.NAMAS,
		                  PO.ALAMAT, PO.KOTA from stockb, stockbd
                          WHERE PO.NO_BUKTI = POD.NO_BUKTI AND PO.GOL ='$golz' AND CBG = '$CBG'
                          AND POD.SISA > 0	");
        return response()->json($stockb);
    }

    public function browseuang(Request $request)
    {
        $CBG = Auth::user()->CBG;

		$stockb = DB::SELECT("SELECT NO_BUKTI,TGL,  KODES, NAMAS, TOTAL,  BAYAR,
                        (TOTAL-BAYAR) AS SISA, ALAMAT, KOTA from stockb
		                WHERE LNS <> 1 AND CBG = '$CBG' ORDER BY NO_BUKTI; ");

        return response()->json($stockb);
    }


	public function index_posting(Request $request)
    {

        return view('otransaksi_stockb.post');
    }

	//SHELVI

	public function browse_detail(Request $request)
    {
		$filterbukti = '';
		if($request->NO_PO)
		{

			$filterbukti = " WHERE a.NO_BUKTI='".$request->NO_PO."' AND a.KD_BRG = b.KD_BRG ";
		}
		$stockbd = DB::SELECT("SELECT a.REC, a.KD_BRG, a.NA_BRG, a.SATUAN , a.QTY, a.HARGA, a.KIRIM, a.SISA,
                                b.SATUAN AS SATUAN_PO, a.QTY AS QTY_PO, '1' AS X
                            from stockbd a, brg b
                            $filterbukti ORDER BY NO_BUKTI ");


		return response()->json($stockbd);
	}


    public function browse_detail2(Request $request)
    {
		$filterbukti = '';
		if($request->NO_PO)
		{

			$filterbukti = " WHERE NO_BUKTI='".$request->NO_PO."' AND a.KD_BRG = b.KD_BRG ";
		}
		$stockbd = DB::SELECT("SELECT a.REC, a.KD_BRG, a.NA_BRG, a.SATUAN , a.QTY, a.HARGA, a.KIRIM, a.SISA,
                                b.SATUAN AS SATUAN_PO, a.QTY AS QTY_PO, '1' AS X
                            from stockbd a, brg b
                            $filterbukti ORDER BY NO_BUKTI ");


		return response()->json($stockbd);
	}

    public function browse_retur(Request $request)
    {
        $flagz = $request->FLAGZ;
        $retur = $request->RETUR;
        $CBG   = Auth::user()->CBG;

        $stockb = DB::SELECT("SELECT NO_BUKTI FROM stockb WHERE FLAG = '$flagz' AND CBG = '$CBG' ORDER BY NO_BUKTI ");

        return response()->json($stockb);
    }

    public function browse_dretur(Request $request)
    {
        $flagz = $request->FLAGZ;
        $retur = $request->RETUR;
        $CBG   = Auth::user()->CBG;

        $stockbd = DB::SELECT("SELECT stockbd.REC,stockbd.KD_BRG,stockbd.NA_BRG,stockbd.SATUAN,stockbd.QTYC,stockbd.QTYR,stockbd.QTY,stockbd.KET FROM stocka, stockbd
                            WHERE stocka.NO_BUKTI = stockbd.NO_BUKTI AND stocka.FLAG = '$flagz' AND stocka.CBG = '$CBG' AND stocka.NO_BUKTI='$retur' ORDER BY stockbd.REC ASC");

        return response()->json($stockbd);
    }
    // ganti 4



    public function getStockb(Request $request)
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

        $stockb = DB::SELECT("SELECT * from stockb
                            WHERE PER='$periode' and FLAG ='$this->FLAGZ' AND CBG = '$CBG'
                            ORDER BY NO_BUKTI ");


        // ganti 6

        return Datatables::of($stockb)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                if (Auth::user()->divisi=="programmer" )
				{
                    //CEK POSTED di index dan edit

                    // url untuk delete di index
                    $url = "'".url("stockb/delete/" . $row->NO_ID . "/?flagz=" . $row->FLAG)."'";
                    // batas

                    $btnEdit =   ($row->POSTED == 1) ? ' onclick= "alert(\'Transaksi ' . $row->NO_BUKTI . ' sudah diposting!\')" href="#" ' : ' href="stockb/edit/?idx=' . $row->NO_ID . '&tipx=edit&flagz=' . $row->FLAG . '&judul=' . $this->judul . '"';
                    $btnDelete = ($row->POSTED == 1) ? ' onclick= "alert(\'Transaksi ' . $row->NO_BUKTI . ' sudah diposting!\')" href="#" ' : ' onclick="deleteRow('.$url.')" ';


                    $btnPrivilege =
                        '
                                <a class="dropdown-item" ' . $btnEdit . '>
                                <i class="fas fa-edit"></i>
                                    Edit
                                </a>
                                <a class="dropdown-item btn btn-danger" href="cetak/' . $row->NO_ID . '">
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

        $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];

        $bulan    = session()->get('periode')['bulan'];
        $tahun    = substr(session()->get('periode')['tahun'], -2);

        $query = DB::table('stockb')->select('NO_BUKTI')->where('PER', $periode)->where('FLAG', $FLAGZ)->where('CBG', $CBG)
                ->orderByDesc('NO_BUKTI')->limit(1)->get();
// Tentukan kode berdasarkan judul
$kode = '';

if ($this->judul == "Retur Hadiah Outlet") {
    $kode = 'RZ';
} elseif ($this->judul == "Terima Retur Hadiah Outlet") {
    $kode = 'TZ';
}

if (!empty($query) && isset($query[0]->NO_BUKTI)) {
    $lastNumber = substr($query[0]->NO_BUKTI, -4);                      // Ambil 4 digit terakhir
    $newNumber  = str_pad((int)$lastNumber + 1, 4, '0', STR_PAD_LEFT); // Pastikan angka tetap 4 digit
    $no_bukti   = $kode . $CBG . $tahun . $bulan . '-' . $newNumber;
} else {
    $no_bukti = $kode . $CBG . $tahun . $bulan . '-0001';
}


        $stockb = Stockb::create(
            [
                'NO_BUKTI'         => $no_bukti,
                'TGL'              => date('Y-m-d', strtotime($request['TGL'])),
                'PER'              => $periode,
                'FLAG'             => $FLAGZ,
                'NOTES'            => ($request['NOTES']==null) ? "" : $request['NOTES'],
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
		$SATUAN	= $request->input('SATUAN');
		$QTYC	= $request->input('QTYC');
		$QTYR	= $request->input('QTYR');
		$QTY	= $request->input('QTY');
		$KET	= $request->input('KET');

        // Check jika value detail ada/tidak
        if ($REC) {
            foreach ($REC as $key => $value) {
                // Declare new data di Model
                $detail    = new StockbDetail;

                // Insert ke Database
                $detail->NO_BUKTI    = $no_bukti;
                $detail->REC         = $REC[$key];
                $detail->PER         = $periode;
                $detail->FLAG        = $FLAGZ;
                $detail->CBG         = $CBG;
				$detail->KD_BRG	     = ($KD_BRG[$key]==null) ? "" :  $KD_BRG[$key];
				$detail->NA_BRG	     = ($NA_BRG[$key]==null) ? "" :  $NA_BRG[$key];
				$detail->SATUAN	     = ($SATUAN[$key]==null) ? "" :  $SATUAN[$key];
				$detail->QTYC	     = (float) str_replace(',', '', $QTYC[$key]);
				$detail->QTYR	     = (float) str_replace(',', '', $QTYR[$key]);
				$detail->QTY	     = (float) str_replace(',', '', $QTY[$key]);
				$detail->KET	     = ($KET[$key]==null) ? "" :  $KET[$key];
                $detail->save();
            }
        }

        // $variablell = DB::select('call stockbins(?)', array($no_bukti));
        if ($FLAGZ == 'RZ') {
            $variablell = DB::select('call rstockbins(?)', [$no_bukti]);
        } else {
            $variablell = DB::select('call stockbins(?)', [$no_bukti]);

        }

		$no_buktix = $no_bukti;

		$stockb = Stockb::where('NO_BUKTI', $no_buktix )->first();


        DB::SELECT("UPDATE stockb,  stockbd
                            SET  stockbd.ID =  stockb.NO_ID  WHERE  stockb.NO_BUKTI =  stockbd.NO_BUKTI
							AND  stockb.NO_BUKTI='$no_buktix';");



        return redirect('/stockb/edit/?idx=' . $stockb->NO_ID . '&tipx=edit&flagz=' . $this->FLAGZ . '&judul=' . $this->judul . '');

    }

   public function edit( Request $request , Stockb $stockb)
    {


		$per = session()->get('periode')['bulan'] . '/' . session()->get('periode')['tahun'];


        // $cekperid = DB::SELECT("SELECT POSTED from perid WHERE PERIO='$per'");
        // if ($cekperid[0]->POSTED==1)
        // {
        //     return redirect('/stockb')
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

		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from stockb
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


		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from stockb
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

		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from stockb
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

		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from stockb
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

    		$bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from stockb
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
			$stockb = Stockb::where('NO_ID', $idx )->first();
	     }
		 else
		 {
				$stockb = new Stockb;
                $stockb->TGL = Carbon::now();


		 }

        $no_bukti = $stockb->NO_BUKTI;
        $stockbDetail = DB::table('stockbd')->where('NO_BUKTI', $no_bukti)->orderBy('REC')->get();

		$data = [
            'header'        => $stockb,
			'detail'        => $stockbDetail

        ];

 		$sup = DB::SELECT("SELECT KODES, CONCAT(NAMAS,'-',KOTA) AS NAMAS FROM SUP
		                 ORDER BY NAMAS ASC" );


         return view('otransaksi_stockb.edit', $data)->with(['sup' => $sup])
		 ->with(['tipx' => $tipx, 'idx' => $idx, 'flagz' => $this->FLAGZ, 'judul'=> $this->judul ]);


    }

  /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Resbelinse
     */

    // ganti 18

    public function update(Request $request, Stockb $stockb)
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

        // $variablell = DB::select('call stockbdel(?)', array($stockb['NO_BUKTI']));

        if ($FLAGZ == 'RZ') {
            $variablell = DB::select('call rstockbdel(?)', [$no_bukti]);
        } else {
            $variablell = DB::select('call stockbdel(?)', [$no_bukti]);

        }

        $CBG = Auth::user()->CBG;

        $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];


        $stockb->update(
            [
                'TGL'              => date('Y-m-d', strtotime($request['TGL'])),
                'NOTES'            => ($request['NOTES']==null) ? "" : $request['NOTES'],
                'TOTAL_QTY'        => (float) str_replace(',', '', $request['TTOTAL_QTY']),
				'USRNM'            => Auth::user()->username,
                'TG_SMP'           => Carbon::now(),
				'updated_by'       => Auth::user()->username,
                'FLAG'             => $FLAGZ,
                'CBG'              => $CBG,
            ]
        );

		$no_buktix = $stockb->NO_BUKTI;

        // Update Detail
        $length = sizeof($request->input('REC'));
        $NO_ID  = $request->input('NO_ID');

        $REC    = $request->input('REC');

        $KD_BRG	= $request->input('KD_BRG');
		$NA_BRG	= $request->input('NA_BRG');
		$SATUAN	= $request->input('SATUAN');
		$QTYC	= $request->input('QTYC');
		$QTYR	= $request->input('QTYR');
		$QTY	= $request->input('QTY');
		$KET	= $request->input('KET');

        $query = DB::table('stockbd')->where('NO_BUKTI', $request->NO_BUKTI)->whereNotIn('NO_ID',  $NO_ID)->delete();

        // Update / Insert
        for ($i = 0; $i < $length; $i++) {
            // Insert jika NO_ID baru
            if ($NO_ID[$i] == 'new') {
                $insert = StockbDetail::create(
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
                        'QTYC'      => (float) str_replace(',', '', $QTYC[$i]),
                        'QTYR'      => (float) str_replace(',', '', $QTYR[$i]),
						'QTY'        => (float) str_replace(',', '', $QTY[$i])

                    ]
                );
            } else {
                // Update jika NO_ID sudah ada
                $upsert = StockbDetail::updateOrCreate(
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
                        'QTYC'      => (float) str_replace(',', '', $QTYC[$i]),
                        'QTYR'      => (float) str_replace(',', '', $QTYR[$i]),
						'QTY'        => (float) str_replace(',', '', $QTY[$i]),
                        'FLAG'       => $this->FLAGZ,
                        'PER'        => $periode,
                        'CBG'        => $CBG,
                    ]
                );
            }
        }

 		$stockb = Stockb::where('NO_BUKTI', $no_buktix )->first();

        $no_bukti = $stockb->NO_BUKTI;

        // $variablell = DB::select('call stockbins(?)', array($stockb['NO_BUKTI']));

        if ($$FLAGZ == 'RZ') {
            $variablell = DB::select('call rstockbins(?)', [$no_bukti]);
        } else {
            $variablell = DB::select('call stockbins(?)', [$no_bukti]);

        }

        DB::SELECT("UPDATE stockb,  stockbd
                    SET  stockbd.ID =  stockb.NO_ID  WHERE  stockb.NO_BUKTI =  stockbd.NO_BUKTI
                    AND  stockb.NO_BUKTI='$no_bukti';");

        return redirect('/stockb/edit/?idx=' . $stockb->NO_ID . '&tipx=edit&flagz=' . $this->FLAGZ . '&judul=' . $this->judul . '');


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Resbelinse
     */

    // ganti 22

    public function destroy(Request $request, Stockb $stockb)
    {

		$this->setFlag($request);
        $FLAGZ = $this->FLAGZ;
        $judul = $this->judul;

		$per = session()->get('periode')['bulan'] . '/' . session()->get('periode')['tahun'];
        $cekperid = DB::SELECT("SELECT POSTED from perid WHERE PERIO='$per'");
        if ($cekperid[0]->POSTED==1)
        {
            return redirect()->route('stockb')
                ->with('status', 'Maaf Periode sudah ditutup!')
                ->with(['judul' => $this->judul, 'flagz' => $this->FLAGZ]);
        }

        // $variablell = DB::select('call stockbdel(?)', array($stockb['NO_BUKTI']));

        if ($FLAGZ == 'RZ') {
            $variablell = DB::select('call rstockbdel(?)', [$no_bukti]);
        } else {
            $variablell = DB::select('call stockbdel(?)', [$no_bukti]);

        }

        $deleteStockb = Stockb::find($stockb->NO_ID);

        $deleteStockb->delete();

       return redirect('/stockb?flagz='.$FLAGZ)->with(['judul' => $judul, 'flagz' => $FLAGZ ])->with('statusHapus', 'Data '.$stockb->NO_BUKTI.' berhasil dihapus');


    }

    public function cetak(Stockb $stockb)
    {
        $no_stockb = $stockb->NO_BUKTI;

        $file     = 'stockbc';
        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

        $query = DB::SELECT("
			SELECT NO_BUKTI,  TGL, KODES, NAMAS, TOTAL_QTY, NOTES, TOTAL, ALAMAT, KOTA
			FROM stockb
			WHERE stockb.NO_BUKTI='$no_stockb'
			ORDER BY NO_BUKTI;
		");

        $xno_stockb1       = $query[0]->NO_BUKTI;
        $xtgl1         = $query[0]->TGL;
        $xkodes1       = $query[0]->KODES;
        $xnamas1       = $query[0]->NAMAS;
        $xtotal1       = $query[0]->TOTAL_QTY;
        $xnotes1       = $query[0]->NOTES;
        $xharga1       = $query[0]->TOTAL;
        $xalamat1      = $query[0]->ALAMAT;
        $xkota1        = $query[0]->KOTA;

        $PHPJasperXML->arrayParameter = array("HARGA1" => (float) $xharga1, "TOTAL1" => (float) $xtotal1, "NO_PO1" => (string) $xno_stockb1,
                                     "TGL1" => (string) $xtgl1,  "KODES1" => (string) $xkodes1,  "NAMAS1" => (string) $xnamas1, "NOTES1" => (string) $xnotes1, "ALAMAT1" => (string) $xalamat1, "KOTA1" => (string) $xkota1 );
        $PHPJasperXML->arraysqltable = array();


        $query2 = DB::SELECT("
			SELECT NO_BUKTI, TGL, KODES, NAMAS, if(ALAMAT='','NOT-FOUND.png',ALAMAT) as ALAMAT, NO_PO,  IF ( FLAG='BL' , 'A','B' ) AS FLAG, AJU, BL, EMKL, KD_BRG, NA_BRG, KG, RPHARGA AS HARGA, RPTOTAL AS TOTAL, 0 AS BAYAR,  NOTES
			FROM beli
			WHERE beli.NO_PO='$no_stockb'  UNION ALL
			SELECT NO_BUKTI, TGL, KODES, NAMAS, if(ALAMAT='','NOT-FOUND.png',ALAMAT) as ALAMAT,  NO_PO,  'C' AS FLAG, '' AS AJU, '' AS BL, '' AS EMKL,  '' AS KD_BRG, '' AS NA_BRG, 0 AS KG,
			0 AS HARGA, 0 AS TOTAL, BAYAR, NOTES
			FROM hut
			WHERE hut.NO_PO='$no_stockb'
			ORDER BY TGL, FLAG, NO_BUKTI;
		");

        $data = [];

        foreach ($query2 as $key => $value) {
            array_push($data, array(
                'NO_BUKTI' => $query2[$key]->NO_BUKTI,
                'TGL'      => $query2[$key]->TGL,
                'KODES'    => $query2[$key]->KODES,
                'NAMAS'    => $query2[$key]->NAMAS,
                'ALAMAT'    => $query2[$key]->ALAMAT,
                'AJU'    => $query2[$key]->AJU,
                'BL'       => $query2[$key]->BL,
                'EMKL'    => $query2[$key]->EMKL,
                'KG'       => $query2[$key]->KG,
                'HARGA'    => $query2[$key]->HARGA,
                'TOTAL'    => $query2[$key]->TOTAL,
                'BAYAR'    => $query2[$key]->BAYAR,
                'NOTES'    => $query2[$key]->NOTES
            ));
        }

        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I");

    }



	 public function posting(Request $request)
    {


    }


	public function getDetailStockb(){

        $no_bukti = $_GET['no_bukti'];
        $result = DB::table('stockbd')->where('NO_BUKTI', $no_bukti)->get();

        return response()->json($result);;
    }





}
