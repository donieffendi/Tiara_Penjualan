<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
// ganti 1

use App\Models\OTransaksi\Ubahklk;
use App\Models\OTransaksi\UbahklkDetail;
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
class UbahklkController extends Controller
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
        if ( $request->flagz == 'K5' ) {
            $this->judul = "Usulan Perubahan KLK";
        }elseif ( $request->flagz == 'K6' ){
            $this->judul = "Posting Ambil Barang";
        }

        $this->FLAGZ = $request->flagz;

    }

    public function index(Request $request)
    {
        $this->setFlag($request);

        return view('otransaksi_ubahklk.index')->with(['judul' => $this->judul, 'flagz' => $this->FLAGZ ]);

    }
	public function browse(Request $request)
    {
        $KODE = $request->KODE;

        $ubahklk = DB::SELECT("SELECT A.KD_BRG AS KODE, A.KET_UK, A.KET_KEM, A.NA_BRG, B.LPH, B.KLK, B.SRMIN AS SMIN, B.SRMAX AS SMAX,
                                        CONCAT(A.na_brg,' ',A.ket_uk,'  ') URAIAN
                                        from brg A, brgdt B
                                        where A.KD_BRG=B.KD_BRG and B.yer='2025' and LEFT(A.NA_BRG,1)='5' /* year harusnya pakai now()*/
                                        and A.KD_BRG = '$KODE'");
        return response()->json($ubahklk);
    }
    // ganti 4



    public function getUbahklk(Request $request)
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

        $ubahklk = DB::SELECT("SELECT * FROM histo where FLAG='K5' AND PER='$periode' ORDER BY POSTED ASC, NO_BUKTI DESC");


        // ganti 6

        return Datatables::of($ubahklk)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                if (Auth::user()->divisi=="programmer" )
				{
                    //CEK POSTED di index dan edit

                    // url untuk delete di index
                    $url = "'".url("ubahklk/delete/" . $row->NO_ID . "/?flagz=" . $row->FLAG)."'";
                    // batas

                    $btnEdit =   ($row->POSTED == 1) ? ' onclick= "alert(\'Transaksi ' . $row->NO_BUKTI . ' sudah diposting!\')" href="#" ' : ' href="ubahklk/edit/?idx=' . $row->NO_ID . '&tipx=edit&flagz=' . $row->FLAG . '&judul=' . $this->judul . '"';
                    $btnDelete = ($row->POSTED == 1) ? ' onclick= "alert(\'Transaksi ' . $row->NO_BUKTI . ' sudah diposting!\')" href="#" ' : ' onclick="deleteRow('.$url.')" ';


                    $btnPrivilege =
                        '
                                <a class="dropdown-item" ' . $btnEdit . '>
                                <i class="fas fa-edit"></i>
                                    Edit
                                </a>
                                <a class="dropdown-item btn btn-danger" href="ubahklk/cetak/' . $row->NO_ID . '">
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

            ->rawColumns(['action', 'cek'])
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

        $cbgSuffix = [
            'TGZ' => 'Z',
            'SOP' => 'S',
            'TMM' => 'MM',
        ];

        $suffix = $cbgSuffix[$CBG] ?? '';

        $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];

        $bulan    = session()->get('periode')['bulan'];
        $tahun    = substr(session()->get('periode')['tahun'], -2);

        $query = DB::table('histo')->select('NO_BUKTI')->where('PER', $periode)->where('FLAG', $this->FLAGZ)->where('CBG', $CBG)
                ->orderByDesc('NO_BUKTI')->limit(1)->get();

        if ($query != '[]') {
            $last = $query[0]->NO_BUKTI;

            preg_match('/-(\d{4})/', $last, $m);

            $urut = isset($m[1]) ? ((int)$m[1] + 1) : 1;

            $query = str_pad($urut, 4, '0', STR_PAD_LEFT);
            $no_bukti = 'K5' . $tahun . $bulan . '-' . $query . $suffix;
        } else {
            $no_bukti = 'K5' . $tahun . $bulan . '-0001' . $suffix;
        }

        $ubahklk = Ubahklk::create(
            [
                'NO_BUKTI'         => $no_bukti,
                'TGL'              => date('Y-m-d', strtotime($request['TGL'])),
                'PER'              => $periode,
                'FLAG'             => $this->FLAGZ,
                'USRNM'            => Auth::user()->username,
				'CBG'              => $CBG,
            ]
        );


		$REC    = $request->input('REC');
		$KODE	= $request->input('KODE');
		$URAIAN	= $request->input('URAIAN');
		$KET_KEM	= $request->input('KET_KEM');
		$LPH	= $request->input('LPH');
		$KLK	= $request->input('KLK');
		$KLKBR	= $request->input('KLKBR');
		$SMIN	= $request->input('SMIN');
		$SMAX	= $request->input('SMAX');

        // Check jika value detail ada/tidak
        if ($REC) {
            foreach ($REC as $key => $value) {
                // Declare new data di Model
                $detail    = new UbahklkDetail;

                // Insert ke Database
                $detail->NO_BUKTI    = $no_bukti;
                $detail->REC         = $REC[$key];
                $detail->PER         = $periode;
				$detail->KODE	     = ($KODE[$key]==null) ? "" :  $KODE[$key];
				$detail->URAIAN	     = ($URAIAN[$key]==null) ? "" :  $URAIAN[$key];
				$detail->KET_KEM	 = ($KET_KEM[$key]==null) ? "" :  $KET_KEM[$key];
				$detail->LPH	     = (float) str_replace(',', '', $LPH[$key]);
				$detail->KLK	     = ($KLK[$key]==null) ? "" :  $KLK[$key];
				$detail->KLKBR	     = ($KLKBR[$key]==null) ? "" :  $KLKBR[$key];
				$detail->SMIN	     = (float) str_replace(',', '', $SMIN[$key]);
				$detail->SMAX	     = (float) str_replace(',', '', $SMAX[$key]);
                $detail->save();
            }
        }

		// $variablell = DB::select('call ambilins(?)', array($no_bukti));

		$no_buktix = $no_bukti;

		$ubahklk = Ubahklk::where('NO_BUKTI', $no_buktix )->first();

        DB::SELECT("UPDATE histo,  histod
                            SET  histod.ID =  histo.NO_ID  WHERE histo.NO_BUKTI =  histod.NO_BUKTI
							AND  histo.NO_BUKTI='$no_buktix';");

        return redirect('/ubahklk?flagz='.$FLAGZ)->with(['judul' => $judul, 'flagz' => $FLAGZ ]);

    }

   public function edit( Request $request , Ubahklk $ubahklk)
    {


		$per = session()->get('periode')['bulan'] . '/' . session()->get('periode')['tahun'];

        $this->setFlag($request);
        $FLAGZ = $this->FLAGZ;
        $judul = $this->judul;

        $cekperid = DB::SELECT("SELECT POSTED from perid WHERE PERIO='$per'");
        if ($cekperid[0]->POSTED==1)
        {
            return redirect('/ubahklk?flagz='.$FLAGZ)
			       ->with('status', 'Maaf Periode sudah ditutup!')
                   ->with(['judul' => $judul, 'flagz' => $FLAGZ]);
        }


        $tipx = $request->tipx;

		$idx = $request->idx;

        $CBG = Auth::user()->CBG;

		if ( $idx =='0' && $tipx=='undo'  )
	    {
			$tipx ='top';

		   }



		if ($tipx=='search') {


    	   $buktix = $request->buktix;

		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from histo
		                 where PER ='$per' and FLAG ='$FLAGZ'
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


		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from histo
		                 where PER ='$per'
						 and FLAG ='$FLAGZ' AND CBG = '$CBG'
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

		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from histo
		             where PER ='$per'
					 and FLAG ='$FLAGZ' AND CBG = '$CBG'
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

		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from histo
		             where PER ='$per'
					 and FLAG ='$FLAGZ' AND CBG = '$CBG'
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

    		$bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from histo
						where PER ='$per'
						and FLAG ='$FLAGZ' AND CBG = '$CBG'
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
			$ubahklk = Ubahklk::where('NO_ID', $idx )->first();
	     }
		 else
		 {
				$ubahklk = new Ubahklk;
                $ubahklk->TGL = Carbon::now();


		 }

        $no_bukti = $ubahklk->NO_BUKTI;
        $ubahklkDetail = DB::table('histod')->where('NO_BUKTI', $no_bukti)->orderBy('REC')->get();

		$data = [
            'header'        => $ubahklk,
			'detail'        => $ubahklkDetail

        ];


        return view('otransaksi_ubahklk.edit', $data)->with(['tipx' => $tipx, 'idx' => $idx, 'flagz' => $FLAGZ, 'judul'=> $judul ]);
    }

  /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    // ganti 18

    public function update(Request $request, Ubahklk $ubahklk)
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

        // $variablell = DB::select('call ambildel(?)', array($ambil['NO_BUKTI']));

        $CBG = Auth::user()->CBG;

        $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];


        $ubahklk->update(
            [
                'TGL'              => date('Y-m-d', strtotime($request['TGL'])),
				'USRNM'            => Auth::user()->username,
            ]
        );

		$no_buktix = $ubahklk->NO_BUKTI;

        // Update Detail
        $length = sizeof($request->input('REC'));
        $NO_ID  = $request->input('NO_ID');
        $REC    = $request->input('REC');
        $KODE	= $request->input('KODE');
		$URAIAN	= $request->input('URAIAN');
		$KET_KEM	= $request->input('KET_KEM');
		$LPH	= $request->input('LPH');
		$KLK	= $request->input('KLK');
		$KLKBR	= $request->input('KLKBR');
		$SMIN	= $request->input('SMIN');
		$SMAX	= $request->input('SMAX');

        $query = DB::table('histod')->where('NO_BUKTI', $request->NO_BUKTI)->whereNotIn('NO_ID',  $NO_ID)->delete();

        // Update / Insert
        for ($i = 0; $i < $length; $i++) {
            // Insert jika NO_ID baru
            if ($NO_ID[$i] == 'new') {
                $insert = UbahklkDetail::create(
                    [
                        'NO_BUKTI'   => $request->NO_BUKTI,
                        'REC'        => $REC[$i],
                        'PER'        => $periode,
                        'KODE'       => ($KODE[$i]==null) ? "" :  $KODE[$i],
                        'URAIAN'     => ($URAIAN[$i]==null) ? "" : $URAIAN[$i],
                        'KET_KEM'     => ($KET_KEM[$i]==null) ? "" : $KET_KEM[$i],
                        'LPH'        => (float) str_replace(',', '', $LPH[$i]),
						'KLK'     	 => ($KLK[$i]==null) ? "" : $KLK[$i],
                        'KLKBR'      => ($KLKBR[$i]==null) ? "" : $KLKBR[$i],
                        'SMIN'       => (float) str_replace(',', '', $SMIN[$i]),
						'SMAX'       => (float) str_replace(',', '', $SMAX[$i])

                    ]
                );
            } else {
                // Update jika NO_ID sudah ada
                $upsert = UbahklkDetail::updateOrCreate(
                    [
                        'NO_BUKTI'  => $request->NO_BUKTI,
                        'NO_ID'     => (int) str_replace(',', '', $NO_ID[$i])
                    ],

                    [
                        'REC'        => $REC[$i],
                        'KODE'       => ($KODE[$i]==null) ? "" :  $KODE[$i],
                        'URAIAN'     => ($URAIAN[$i]==null) ? "" : $URAIAN[$i],
                        'KET_KEM'    => ($KET_KEM[$i]==null) ? "" : $KET_KEM[$i],
                        'LPH'        => (float) str_replace(',', '', $LPH[$i]), 
						'KLK'     	 => ($KLK[$i]==null) ? "" : $KLK[$i],
                        'KLKBR'      => ($KLKBR[$i]==null) ? "" : $KLKBR[$i],
                        'SMIN'       => (float) str_replace(',', '', $SMIN[$i]),
						'SMAX'       => (float) str_replace(',', '', $SMAX[$i]),
                        'PER'        => $periode,
                    ]
                );
            }
        }

 		$ubahklk = Ambil::where('NO_BUKTI', $no_buktix )->first();

        $no_bukti = $ubahklk->NO_BUKTI;

        // $variablell = DB::select('call ambilins(?)', array($ambil['NO_BUKTI']));


        DB::SELECT("UPDATE histo,  histod
                    SET  histod.ID =  histo.NO_ID  WHERE  histo.NO_BUKTI =  histod.NO_BUKTI
                    AND  histo.NO_BUKTI='$no_bukti';");

        return redirect('/ubahklk?flagz='.$FLAGZ)->with(['judul' => $judul, 'flagz' => $FLAGZ ]);


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    // ganti 22

    public function destroy(Request $request, Ubahklk $ubahklk)
    {

		$this->setFlag($request);
        $FLAGZ = $this->FLAGZ;
        $judul = $this->judul;

		$per = session()->get('periode')['bulan'] . '/' . session()->get('periode')['tahun'];
        $cekperid = DB::SELECT("SELECT POSTED from perid WHERE PERIO='$per'");
        if ($cekperid[0]->POSTED==1)
        {
            return redirect()->route('/ubahklk?flagz='.$FLAGZ)
                ->with('status', 'Maaf Periode sudah ditutup!')
                ->with(['judul' => $this->judul, 'flagz' => $this->FLAGZ]);
        }

        // $variablell = DB::select('call ambildel(?)', array($ambil['NO_BUKTI']));

        $deleteAmbil = Ambil::find($ubahklk->NO_ID);

        $deleteAmbil->delete();

       return redirect('/ubahklk?flagz='.$FLAGZ)->with(['judul' => $judul, 'flagz' => $FLAGZ ])->with('statusHapus', 'Data '.$ubahklk->NO_BUKTI.' berhasil dihapus');


    }

    public function cetak(Ubahklk $ubahklk)
    {
        $no_ubah = $ubahklk->NO_BUKTI;

        // $kd_brg = strval($request->KD_BRG);
        // $kd_brgx = strval($kd_brg);

        $file     = 'klk_kode5';

        $flagz1 = $ubahklk->FLAG;
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

        $query = DB::SELECT("SELECT histo.NO_BUKTI, histo.TGL, histo.KODEC, histo.NAMAC, histo.TOTAL_QTY, histo.NOTES, histo.ALAMAT, histo.NO_JUAL,
                                    histo.KOTA, histod.KD_BRG, histod.NA_BRG, histod.SATUAN, histod.QTY, histod.KET, histo.USRNM
                            FROM histo, histod
                            WHERE histo.NO_BUKTI='$no_ubah' AND histo.NO_BUKTI = histod.NO_BUKTI
                            ;
		");

                DB::SELECT("UPDATE histo SET POSTED = 1 WHERE NO_BUKTI='$no_ubah';");

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
        $ids = json_decode($request->ids);

        if ($ids && count($ids) > 0) {
            DB::table('histo')
                ->whereIn('NO_ID', $ids)
                ->update([
                    'POSTED' => 1
                ]);
        }

        return redirect()->back()->with('status', 'Data berhasil di posting!');
    }

}