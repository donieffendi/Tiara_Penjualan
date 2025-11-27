<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
// ganti 1

use App\Models\OTransaksi\Kmkirim;
use App\Models\OTransaksi\KmkirimDetail;
use App\Models\Master\Sup;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use DataTables;
use Auth;
use DB;
use Carbon\Carbon;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";
use PHPJasperXML;

// ganti 2
class KmkirimController extends Controller
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
        if ( $request->flagz == 'KK' ) {
            $this->judul = "Kumpulan Kirim";
        } else if ( $request->flagz == 'KM' ) {
            $this->judul = "Koreksi Stock Kumpulan";
        }elseif($this->FLAGZ == 'PK') {
            $this->judul = "Posting Kumpulan Kirim";
        }

        $this->FLAGZ = $request->flagz;

    }

    public function index(Request $request)
    {
        $this->setFlag($request);
        if ($this->FLAGZ == 'KK') {
            $view = "otransaksi_kmkirim.index";
        } elseif($this->FLAGZ == 'KM') {
            $view = "otransaksi_kmkirim.index";
        }elseif($this->FLAGZ == 'PK') {
            $view = "otransaksi_kmkirim.index_posting";
        }
        return view($view)->with([
            'judul' => $this->judul,
            'flagz' => $this->FLAGZ,
        ]);
    }

		public function browse(Request $request)
    {

        $CBG = Auth::user()->CBG;

        $kmkirim = DB::SELECT("SELECT distinct NO_BUKTI AS NO_EXPEDISI, CBG_TUJU from kmkirim
                          WHERE CBG_TUJU = '$CBG'
                        --   WHERE CBG = '$CBG'
                        --   AND kirimd.SISA > 0
                        ");
        return response()->json($kmkirim);
    }

    public function browse_kmkirimd(Request $request)
    {
            $kmkirimd = DB::SELECT("SELECT REC, KD_BRG, NA_BRG, SATUAN , QTY, KET
                                from kmkirimd
                                where NO_BUKTI='".$request->nobukti."' ");

		return response()->json($kmkirimd);
	}

    public function browse_terikum(Request $request)
    {

        $CBG = Auth::user()->CBG;
        $barcode = $request->BARCODE;

        $kmkirim = DB::SELECT("SELECT NO_BUKTI AS NO_KKIRIM, CBG from kmkirim
                          WHERE CBG_TUJU = '$CBG' AND BARCODE = '$barcode';
                        ");
        return response()->json($kmkirim);
    }

    public function browseuang(Request $request)
    {
        $CBG = Auth::user()->CBG;

		$kmkirim = DB::SELECT("SELECT NO_BUKTI,TGL,  KODES, NAMAS, TOTAL,  BAYAR,
                        (TOTAL-BAYAR) AS SISA, ALAMAT, KOTA from kmkirim
		                WHERE LNS <> 1 AND CBG = '$CBG' ORDER BY NO_BUKTI; ");

        return response()->json($kmkirim);
    }


	public function index_posting(Request $request)
    {

        return view('otransaksi_kmkirim.post');
    }

	//SHELVI

	public function browse_detail(Request $request)
    {
		$filterbukti = '';
		if($request->NO_PO)
		{

			$filterbukti = " WHERE a.NO_BUKTI='".$request->NO_PO."' AND a.KD_BRG = b.KD_BRG ";
		}
		$kmkirimd = DB::SELECT("SELECT a.REC, a.KD_BRG, a.NA_BRG, a.SATUAN , a.QTY, a.HARGA, a.KIRIM, a.SISA,
                                b.SATUAN AS SATUAN_PO, a.QTY AS QTY_PO, '1' AS X
                            from kmkirimd a, brg b
                            $filterbukti ORDER BY NO_BUKTI ");


		return response()->json($kmkirimd);
	}


    public function browse_detail2(Request $request)
    {
		$filterbukti = '';
		if($request->NO_PO)
		{

			$filterbukti = " WHERE NO_BUKTI='".$request->NO_PO."' AND a.KD_BRG = b.KD_BRG ";
		}
		$kmkirimd = DB::SELECT("SELECT a.REC, a.KD_BRG, a.NA_BRG, a.SATUAN , a.QTY, a.HARGA, a.KIRIM, a.SISA,
                                b.SATUAN AS SATUAN_PO, a.QTY AS QTY_PO, '1' AS X
                            from kmkirimd a, brg b
                            $filterbukti ORDER BY NO_BUKTI ");


		return response()->json($kmkirimd);
	}
    // ganti 4



    public function getKmkirim(Request $request)
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

        $kmkirim = DB::SELECT("SELECT * from kmkirim
                            WHERE PER='$periode' and FLAG ='$FLAGZ' AND CBG = '$CBG'
                            ORDER BY NO_BUKTI ");


        // ganti 6

        return Datatables::of($kmkirim)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                if (Auth::user()->divisi=="programmer" )
				{
                    //CEK POSTED di index dan edit

                    // url untuk delete di index
                    $url = "'".url("kmkirim/delete/" . $row->NO_ID . "/?flagz=" . $row->FLAG)."'";
                    // batas

                    $btnEdit =   ($row->POSTED == 1) ? ' onclick= "alert(\'Transaksi ' . $row->NO_BUKTI . ' sudah diposting!\')" href="#" ' : ' href="kmkirim/edit/?idx=' . $row->NO_ID . '&tipx=edit&flagz=' . $row->FLAG . '&judul=' . $this->judul . '"';
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

        $query = DB::table('kmkirim')->select('NO_BUKTI')->where('PER', $periode)->where('FLAG', $FLAGZ)->where('CBG', $CBG)
                ->orderByDesc('NO_BUKTI')->limit(1)->get();

        if ($query != '[]') {
            $query = substr($query[0]->NO_BUKTI, -4);
            $query = str_pad($query + 1, 4, 0, STR_PAD_LEFT);
            $no_bukti = $FLAGZ . $CBG . $tahun . $bulan . '-' . $query;
        } else {
            $no_bukti = $FLAGZ . $CBG . $tahun . $bulan . '-0001';
        }

        //UNTUK BARCODE (type code 128)
        // $length = 7;
        // $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'; // Kombinasi huruf & angka
        $barcode = Str::uuid();

        // for ($i = 0; $i < $length; $i++) {
        //     $barcode .= $characters[rand(0, strlen($characters) - 1)];
        // }
        // dd($no_bukti);
        $kmkirim = Kmkirim::create(
            [
                'NO_BUKTI'         => $no_bukti,
                'TGL'              => date('Y-m-d', strtotime($request['TGL'])),
                'PER'              => $periode,
                'FLAG'             => $FLAGZ,
                'BARCODE'          => ($barcode == null) ? "" : $barcode,
                'NOTES'            => ($request['NOTES']==null) ? "" : $request['NOTES'],
                'CBG_TUJU'         => ($request['CBG_TUJU']==null) ? "" : $request['CBG_TUJU'],
                // 'TOTAL_QTY'        => (float) str_replace(',', '', $request['TTOTAL_QTY']),
                'USRNM'            => Auth::user()->username,
                'TG_SMP'           => Carbon::now(),
				'created_by'       => Auth::user()->username,
				'CBG'              => $CBG,
            ]
        );


		$REC        = $request->input('REC');
        $NO_KIRIM   = $request->input('NO_KIRIM');
		$KD_BRG	    = $request->input('KD_BRG');
		$NA_BRG	    = $request->input('NA_BRG');
		$SATUAN	    = $request->input('SATUAN');
		$QTY	    = $request->input('QTY');
		$KET	    = $request->input('KET');
		$CBG_KIRIM	= $request->input('CBG_KIRIM');

        // Check jika value detail ada/tidak
        if ($REC) {
            foreach ($REC as $key => $value) {
                // Declare new data di Model
                $detail    = new KmkirimDetail;

                // Insert ke Database
                $detail->NO_BUKTI    = $no_bukti;
                $detail->REC         = $REC[$key];
                $detail->PER         = $periode;
                $detail->FLAG        = $FLAGZ;
                $detail->CBG         = $CBG;
				$detail->NO_KIRIM	 = ($NO_KIRIM[$key]==null) ? "" :  $NO_KIRIM[$key];
				$detail->KET	     = ($KET[$key]==null) ? "" :  $KET[$key];
				$detail->CBG_TUJU	 = ($CBG_KIRIM[$key]==null) ? "" :  $CBG_KIRIM[$key];
                $detail->save();
            }
        }

        // $variablell = DB::select('call kirimins(?)', array($no_bukti));

		$no_buktix = $no_bukti;

		$kmkirim = Kmkirim::where('NO_BUKTI', $no_buktix )->first();


        DB::SELECT("UPDATE kmkirim,  kmkirimd
                            SET  kmkirimd.ID =  kmkirim.NO_ID  WHERE  kmkirim.NO_BUKTI =  kmkirimd.NO_BUKTI
							AND  kmkirim.NO_BUKTI='$no_buktix';");



        return redirect('/kmkirim/edit/?idx=' . $kmkirim->NO_ID . '&tipx=edit&flagz=' . $this->FLAGZ . '&judul=' . $this->judul . '');

    }

   public function edit( Request $request , Kmkirim $kmkirim)
    {


        $pilihcbg = DB::table('compan')->select('EXT')->orderBy('EXT', 'ASC')->get();

		$per = session()->get('periode')['bulan'] . '/' . session()->get('periode')['tahun'];

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

		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from kmkirim
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


		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from kmkirim
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

		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from kmkirim
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

		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from kmkirim
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

    		$bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from kmkirim
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
			$kmkirim = Kmkirim::where('NO_ID', $idx )->first();
	     }
		 else
		 {
				$kmkirim = new Kmkirim;
                $kmkirim->TGL = Carbon::now();


		 }

        $no_bukti = $kmkirim->NO_BUKTI;
        $kmkirimDetail = DB::table('kmkirimd')->where('NO_BUKTI', $no_bukti)->orderBy('REC')->get();

		$data = [
            'header'        => $kmkirim,
			'detail'        => $kmkirimDetail

        ];

 		$sup = DB::SELECT("SELECT KODES, CONCAT(NAMAS,'-',KOTA) AS NAMAS FROM SUP
		                 ORDER BY NAMAS ASC" );


         return view('otransaksi_kmkirim.edit', $data)->with(['sup' => $sup])
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

    public function update(Request $request, Kmkirim $kmkirim)
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

        // $variablell = DB::select('call kirimdel(?)', array($kirim['NO_BUKTI']));

        $CBG = Auth::user()->CBG;

        $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];


        $kmkirim->update(
            [
                'TGL'              => date('Y-m-d', strtotime($request['TGL'])),
                'NOTES'            => ($request['NOTES']==null) ? "" : $request['NOTES'],
                'CBG_TUJU'            => ($request['CBG_TUJU']==null) ? "" : $request['CBG_TUJU'],
                'TOTAL_QTY'        => (float) str_replace(',', '', $request['TTOTAL_QTY']),
				'USRNM'            => Auth::user()->username,
                'TG_SMP'           => Carbon::now(),
				'updated_by'       => Auth::user()->username,
                'FLAG'             => 'KM',
                'CBG'              => $CBG,
            ]
        );

		$no_buktix = $kmkirim->NO_BUKTI;

        // Update Detail
        $length = sizeof($request->input('REC'));
        $NO_ID  = $request->input('NO_ID');

        $REC    = $request->input('REC');

        $KD_BRG	= $request->input('KD_BRG');
        $NO_KIRIM	= $request->input('NO_KIRIM');
		$NA_BRG	= $request->input('NA_BRG');
		$SATUAN	= $request->input('SATUAN');
		$QTY	= $request->input('QTY');
		$KET	= $request->input('KET');
		$CBG_TUJU	= $request->input('CBG_TUJU');

        $query = DB::table('kmkirimd')->where('NO_BUKTI', $request->NO_BUKTI)->whereNotIn('NO_ID',  $NO_ID)->delete();

        // Update / Insert
        for ($i = 0; $i < $length; $i++) {
            // Insert jika NO_ID baru
            if ($NO_ID[$i] == 'new') {
                $insert = KmkirimDetail::create(
                    [
                        'NO_BUKTI'   => $request->NO_BUKTI,
                        'REC'        => $REC[$i],
                        'PER'        => $periode,
                        'CBG'        => $CBG,
                        'FLAG'       => $this->FLAGZ,
                        'KD_BRG'     => ($KD_BRG[$i]==null) ? "" :  $KD_BRG[$i],
                        'NO_KIRIM'   => ($NO_KIRIM[$i]==null) ? "" :  $NO_KIRIM[$i],
                        'NA_BRG'     => ($NA_BRG[$i]==null) ? "" : $NA_BRG[$i],
                        'SATUAN'     => ($SATUAN[$i]==null) ? "" : $SATUAN[$i],
						'KET'     	 => ($KET[$i]==null) ? "" : $KET[$i],
						'QTY'        => (float) str_replace(',', '', $QTY[$i]),

                    ]
                );
            } else {
                // Update jika NO_ID sudah ada
                $upsert = KmkirimDetail::updateOrCreate(
                    [
                        'NO_BUKTI'  => $request->NO_BUKTI,
                        'NO_ID'     => (int) str_replace(',', '', $NO_ID[$i])
                    ],

                    [
                        'REC'        => $REC[$i],

                        'NO_KIRIM'   => ($NO_KIRIM[$i]==null) ? "" :  $NO_KIRIM[$i],
                        'KD_BRG'     => ($KD_BRG[$i]==null) ? "" :  $KD_BRG[$i],
                        'NA_BRG'     => ($NA_BRG[$i]==null) ? "" : $NA_BRG[$i],
                        'SATUAN'     => ($SATUAN[$i]==null) ? "" : $SATUAN[$i],
						'KET'     	 => ($KET[$i]==null) ? "" : $KET[$i],
						'QTY'        => (float) str_replace(',', '', $QTY[$i]),
                        'FLAG'       => $this->FLAGZ,
                        'PER'        => $periode,
                        'CBG'        => $CBG,
                    ]
                );
            }
        }

 		$kmkirim =Kmkirim::where('NO_BUKTI', $no_buktix )->first();

        $no_bukti = $kmkirim->NO_BUKTI;

        // $variablell = DB::select('call kirimins(?)', array($kirim['NO_BUKTI']));

        DB::SELECT("UPDATE kmkirim,  kmkirimd
                    SET  kmkirimd.ID =  kmkirim.NO_ID  WHERE  kmkirim.NO_BUKTI =  kmkirimd.NO_BUKTI
                    AND  kmkirim.NO_BUKTI='$no_bukti';");

        return redirect('/kmkirim/edit/?idx=' . $kmkirim->NO_ID . '&tipx=edit&flagz=' . $this->FLAGZ . '&judul=' . $this->judul . '');


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Resbelinse
     */

    // ganti 22

    public function destroy(Request $request, Kmkirim $kmkirim)
    {

		$this->setFlag($request);
        $FLAGZ = $this->FLAGZ;
        $judul = $this->judul;

		$per = session()->get('periode')['bulan'] . '/' . session()->get('periode')['tahun'];
        $cekperid = DB::SELECT("SELECT POSTED from perid WHERE PERIO='$per'");
        if ($cekperid[0]->POSTED==1)
        {
            return redirect()->route('kmkirim')
                ->with('status', 'Maaf Periode sudah ditutup!')
                ->with(['judul' => $this->judul, 'flagz' => $this->FLAGZ]);
        }

        // $variablell = DB::select('call kirimdel(?)', array($kirim['NO_BUKTI']));

        $deleteKmkirim = Kmkirim::find($kmkirim->NO_ID);

        $deleteKmkirim->delete();

       return redirect('/kmkirim?flagz='.$FLAGZ)->with(['judul' => $judul, 'flagz' => $FLAGZ ])->with('statusHapus', 'Data '.$kmkirim->NO_BUKTI.' berhasil dihapus');


    }

    public function cetak(Kmkirim $kmkirim)
    {
        $no_buktix = $kmkirim->NO_BUKTI;

        $file     = 'kmkirimc';
        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

        $query = DB::SELECT("SELECT kmkirim.NO_BUKTI, kmkirim.CBG_TUJU, kmkirim.TGL, 
				kmkirimd.KD_BRG, kmkirimd.NA_BRG, kmkirimd.QTY, kmkirimd.HARGA, kmkirimd.TOTAL
			FROM kmkirim, kmkirimd
            WHERE kmkirim.NO_BUKTI='$no_buktix' AND kmkirim.NO_BUKTI = kmkirimd.NO_BUKTI");

        $data = [];

        foreach ($query as $key => $value) {
            array_push($data, array(
                'NO_BUKTI' => $query[$key]->NO_BUKTI,
                'NO_KIRIM' => $query[$key]->NO_KIRIM,
                'TGL'      => $query[$key]->TGL,
                'KODES'    => $query[$key]->KODES,
                'NAMAS'    => $query[$key]->NAMAS,
                'ALAMAT'    => $query[$key]->ALAMAT,
                'AJU'    => $query[$key]->AJU,
                'BL'       => $query[$key]->BL,
                'EMKL'    => $query[$key]->EMKL,
                'KG'       => $query[$key]->KG,
                'HARGA'    => $query[$key]->HARGA,
                'TOTAL'    => $query[$key]->TOTAL,
                'BAYAR'    => $query[$key]->BAYAR,
                'NOTES'    => $query[$key]->NOTES
            ));
        }

        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I");

    }



	 public function posting(Request $request)
    {


    }

	public function getDetailKmkirim(){

        $no_bukti = $_GET['no_bukti'];
        $result = DB::table('kmkirimd')->where('NO_BUKTI', $no_bukti)->get();

        return response()->json($result);;
    }
    public function posting_stock_kmkirim(Request $request)
    {
        if (! $request->isMethod('post')) {
            return response()->json(['error' => 'Method Not Allowed'], 405);
        }

        $data = $request->input('posted');

        if (! $data) {
            return response()->json(['error' => 'Tidak ada data yang dikirim'], 400);
        }

        foreach ($data as $id => $posted) {
            DB::table('kmkirim')->where('NO_ID', $id)->update(['POSTED' => $posted]);
        }

        return response()->json(['message' => 'Status berhasil diperbarui']);
    }


}
