<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
// ganti 1

use App\Models\OTransaksi\Pp;
use App\Models\OTransaksi\PpDetail;
use App\Models\Master\Sup;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use DB;
use Carbon\Carbon;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";
use PHPJasperXML;

// ganti 2
class PpController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    var $judul = '';
    var $FLAGZ = '';
    var $GOLZ = '';

    public function setFlag(Request $request)
    {
        $this->FLAGZ = $request->flagz;
        $this->GOLZ  = $request->golz;

        if ($this->FLAGZ == 'PO' && $this->GOLZ == 'B') {
            $this->judul = "PO Bahan Baku";
        } else if ($this->FLAGZ == 'PO' && $this->GOLZ == 'J') {
            $this->judul = "PO Barang";
        } else if ($this->FLAGZ == 'PO' && $this->GOLZ == 'C') {
            $this->judul = "PO Customer";
        } else if ($this->FLAGZ == 'PP' && $this->GOLZ == 'P') {
            $this->judul = "PP Posting";
        } else {
            $this->judul = "Default Judul";
        }
    }

    public function index(Request $request)
    {
        $this->setFlag($request);

        if ($this->FLAGZ == 'PP' && $this->GOLZ == 'P') {
            $view = "otransaksi_pp.index_posting";
        } else {
            $view = "otransaksi_pp.index";
        }

        return view($view)->with([
            'judul' => $this->judul,
            'flagz' => $this->FLAGZ,
            'golz'  => $this->GOLZ,
        ]);
    }

	public function browse(Request $request)
    {
        // $golz = $request->GOL;

        $CBG = Auth::user()->CBG;

        //pp.GUDANG setelah pp.PKP dihapus
        $pp = DB::SELECT("SELECT distinct pp.NO_BUKTI AS NO_PP, pp.TGL
                          from pp
                          WHERE pp.POSTED = 1
                          ORDER BY pp.NO_BUKTI");

        return response()->json($pp);
    }

    public function browseuang(Request $request)
    {
        $CBG = Auth::user()->CBG;

		$pp = DB::SELECT("SELECT NO_BUKTI,TGL,  KODES, NAMAS, TOTAL,  BAYAR,
                                TOTAL-BAYAR) AS SISA, ALAMAT, KOTA from po
		                WHERE LNS <> 1 AND CBG = '$CBG' ORDER BY NO_BUKTI; ");

        return response()->json($pp);
    }


	public function index_posting(Request $request)
    {

        return view('otransaksi_pp.post');
    }

	public function browse_ppd(Request $request)
    {
            $ppd = DB::SELECT("SELECT a.REC, a.KD_BRG, a.NA_BRG, a.KD_BRG, a.NA_BRG, a.SATUAN , a.QTY
                                from ppd a
                                where a.NO_BUKTI='".$request->nobukti."' ");




		return response()->json($ppd);
	}

	public function browse_detail(Request $request)
    {
		$filterbukti = '';
		if($request->NO_PO)
		{

			$filterbukti = " WHERE a.NO_BUKTI='".$request->NO_PO."' AND a.KD_BHN = b.KD_BHN ";
		}
		$ppd = DB::SELECT("SELECT a.REC, a.KD_BHN, a.NA_BHN, a.SATUAN , a.QTY, a.HARGA, a.KIRIM, a.SISA,
                                b.SATUAN AS SATUAN_PO, a.QTY AS QTY_PO, b.KALI AS KALI
                            from ppd a, bhn b
                            $filterbukti ORDER BY NO_BUKTI ");


		return response()->json($ppd);
	}


    public function browse_detail2(Request $request)
    {
		$filterbukti = '';
		if($request->NO_PO)
		{

			$filterbukti = " WHERE NO_BUKTI='".$request->NO_PO."' AND a.KD_BRG = b.KD_BRG ";
		}
		$ppd = DB::SELECT("SELECT a.REC, a.KD_BRG, a.NA_BRG, a.SATUAN , a.QTY, a.HARGA, a.KIRIM, a.SISA,
                                b.SATUAN AS SATUAN_PO, a.QTY AS QTY_PO, b.KALI AS KALI
                            from ppd a, brg b
                            $filterbukti ORDER BY NO_BUKTI ");


		return response()->json($ppd);
	}
    // ganti 4



    public function getPp(Request $request)
    {
        // ganti 5

       if ($request->session()->has('periode')) {
            $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
        } else {
            $periode = '';
        }

		$this->setFlag($request);
        $FLAGZ = $this->FLAGZ;
        $GOLZ = $this->GOLZ;
        $judul = $this->judul;

        $CBG = Auth::user()->CBG;

        $pp = DB::SELECT("SELECT *, POSTED as cek from pp  WHERE PER='$periode' and FLAG ='$this->FLAGZ'
                        AND GOL ='$this->GOLZ' AND CBG = '$CBG' ORDER BY NO_BUKTI ");


        // ganti 6

        return Datatables::of($pp)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                if (Auth::user()->divisi=="programmer" )
				{
                    //CEK POSTED di index dan edit

                    // url untuk delete di index
                    $url = "'".url("pp/delete/" . $row->NO_ID . "/?flagz=" . $row->FLAG . "&golz=" . $row->GOL)."'";
                    // batas

                    $btnEdit =   ($row->POSTED == 1) ? ' onclick= "alert(\'Transaksi ' . $row->NO_BUKTI . ' sudah dippsting!\')" href="#" ' : ' href="pp/edit/?idx=' . $row->NO_ID . '&tipx=edit&flagz=' . $row->FLAG . '&judul=' . $this->judul . '&golz=' . $row->GOL . '"';
                    $btnDelete = ($row->POSTED == 1) ? ' onclick= "alert(\'Transaksi ' . $row->NO_BUKTI . ' sudah diposting!\')" href="#" ' : ' onclick="deleteRow('.$url.')"';


                    $btnPrivilege =
                        '
                                <a class="dropdown-item" ' . $btnEdit . '>
                                <i class="fas fa-edit"></i>
                                    Edit
                                </a>
                                <a class="dropdown-item btn btn-danger" href="pp/cetak/' . $row->NO_ID . '">
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
        $GOLZ = $this->GOLZ;
        $judul = $this->judul;

        $CBG = Auth::user()->CBG;

        $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];

        $bulan    = session()->get('periode')['bulan'];
        $tahun    = substr(session()->get('periode')['tahun'], -2);

        $query = DB::table('pp')->select('NO_BUKTI')->where('PER', $periode)->where('FLAG', 'PP')->where('CBG', $CBG)
                ->where('GOL', $this->GOLZ )->orderByDesc('NO_BUKTI')->limit(1)->get();

        if ($query != '[]') {
            $query = substr($query[0]->NO_BUKTI, -4);
            $query = str_pad($query + 1, 4, 0, STR_PAD_LEFT);
            $no_bukti = 'PP' . $CBG . $tahun . $bulan . '-' . $query;
        } else {
            $no_bukti = 'PP' . $CBG . $tahun . $bulan . '-0001';
        }



        $pp = Pp::create(
            [
                'NO_BUKTI'         => $no_bukti,
                'TGL'              => date('Y-m-d', strtotime($request['TGL'])),
                'PER'              => $periode,
                'FLAG'             => 'PP',
                'GOL'              => $GOLZ,
                'CBG'              => $CBG,
                'TOTAL_QTY'        => (float) str_replace(',', '', $request['TTOTAL_QTY']),
                'USRNM'            => Auth::user()->username,
                'TG_SMP'           => Carbon::now(),
				'created_by'       => Auth::user()->username,
            ]
        );


		$REC        = $request->input('REC');
		$KD_BRG     = $request->input('KD_BRG');
        $NA_BRG     = $request->input('NA_BRG');
        $SATUAN     = $request->input('SATUAN');
        $KODES     = $request->input('KODES');
        $QTY        = $request->input('QTY');
        $TAHAP1        = $request->input('TAHAP1');
        $TAHAP2        = $request->input('TAHAP2');
        $TAHAP3        = $request->input('TAHAP3');

        // Check jika value detail ada/tidak
        if ($REC) {
            foreach ($REC as $key => $value) {
                // Declare new data di Model
                $detail    = new PpDetail;

                // Insert ke Database
                $detail->NO_BUKTI    = $no_bukti;
                $detail->REC         = $REC[$key];
                $detail->PER         = $periode;
                $detail->FLAG        = $FLAGZ;
                $detail->GOL 	     = $GOLZ;
                $detail->KD_BRG      = ($KD_BRG[$key] == null) ? "" :  $KD_BRG[$key];
                $detail->NA_BRG      = ($NA_BRG[$key] == null) ? "" :  $NA_BRG[$key];
                $detail->SATUAN      = ($SATUAN[$key] == null) ? "" :  $SATUAN[$key];
                $detail->KODES      = ($KODES[$key] == null) ? "" :  $KODES[$key];
                $detail->QTY         = (float) str_replace(',', '', $QTY[$key]);
                $detail->TAHAP1         = (float) str_replace(',', '', $TAHAP1[$key]);
                $detail->TAHAP2         = (float) str_replace(',', '', $TAHAP2[$key]);
                $detail->TAHAP3         = (float) str_replace(',', '', $TAHAP3[$key]);
                $detail->save();
            }
        }

		$no_buktix = $no_bukti;

		$pp = Pp::where('NO_BUKTI', $no_buktix )->first();


        DB::SELECT("UPDATE pp,  ppd
                            SET  ppd.ID =  pp.NO_ID  WHERE  pp.NO_BUKTI =  ppd.NO_BUKTI
							AND  pp.NO_BUKTI='$no_buktix';");

        DB::SELECT("UPDATE ppd, sup
                SET ppd.PKP = sup.GOLONGAN,
                    ppd.PKP = CASE
                                WHEN sup.GOLONGAN = 'P0' THEN 0
                                ELSE 1
                            END
                WHERE ppd.KODES = sup.KODES
                AND ppd.NO_BUKTI = '$no_bukti';");


        // return redirect('/pp/edit/?idx=' . $pp->NO_ID . '&tipx=edit&flagz=' . $this->FLAGZ . '&golz=' . $this->GOLZ . '&judul=' . $this->judul . '');
        return redirect('/pp?flagz='.$FLAGZ.'&golz='.$GOLZ)->with(['judul' => $judul, 'golz' => $GOLZ, 'flagz' => $FLAGZ ]);

    }

   public function edit( Request $request , Pp $pp)
    {


		$per = session()->get('periode')['bulan'] . '/' . session()->get('periode')['tahun'];


        // $cekperid = DB::SELECT("SELECT POSTED from perid WHERE PERIO='$per'");
        // if ($cekperid[0]->POSTED==1)
        // {
        //     return redirect('/pp')
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

		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from pp
		                 where PER ='$per' and FLAG ='$this->FLAGZ'
                         and GOL ='$this->GOLZ'
                         AND CBG = '$CBG'
						 and NO_BUKTI = '$buktix'
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


		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from pp
		                 where PER ='$per'
						 and FLAG ='$this->FLAGZ'
                         and GOL ='$this->GOLZ'
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

		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from pp
		             where PER ='$per'
					 and FLAG ='$this->FLAGZ'
                     and GOL ='$this->GOLZ'
                     AND CBG = '$CBG'
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

		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from pp
		             where PER ='$per'
					 and FLAG ='$this->FLAGZ'
                     and GOL ='$this->GOLZ'
                     AND CBG = '$CBG'
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

    		$bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from pp
						where PER ='$per'
						and FLAG ='$this->FLAGZ'
                        and GOL ='$this->GOLZ'
                        AND CBG = '$CBG'
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
			$pp = Pp::where('NO_ID', $idx )->first();
	     }
		 else
		 {
				$pp = new Pp;
                $pp->TGL = Carbon::now();


		 }

        $no_bukti = $pp->NO_BUKTI;
        $ppDetail = DB::table('ppd')->where('NO_BUKTI', $no_bukti)->orderBy('REC')->get();

		$data = [
            'header'        => $pp,
			'detail'        => $ppDetail

        ];


         return view('otransaksi_pp.edit', $data)
		 ->with(['tipx' => $tipx, 'idx' => $idx, 'flagz' => $this->FLAGZ, 'golz' => $this->GOLZ, 'judul'=> $this->judul ]);


    }

  /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    // ganti 18

    public function update(Request $request, Pp $pp)
    {

        $this->validate(
            $request,
            [

                'TGL'      => 'required'
            ]
        );

		$this->setFlag($request);
        $GOLZ = $this->GOLZ;
        $FLAGZ = $this->FLAGZ;
        $judul = $this->judul;

        $CBG = Auth::user()->CBG;


        $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];


        $pp->update(
            [
                'TGL'              => date('Y-m-d', strtotime($request['TGL'])),
                'TOTAL_QTY'        => (float) str_replace(',', '', $request['TTOTAL_QTY']),
				'USRNM'            => Auth::user()->username,
                'TG_SMP'           => Carbon::now(),
				'updated_by'       => Auth::user()->username,
                'FLAG'             => 'PP',
                'GOL'              => $GOLZ,
                'CBG'              => $CBG,
            ]
        );

		$no_buktix = $pp->NO_BUKTI;

        // Update Detail
        $length = sizeof($request->input('REC'));
        $NO_ID  = $request->input('NO_ID');

        $REC    = $request->input('REC');

        $KD_BRG = $request->input('KD_BRG');
        $NA_BRG = $request->input('NA_BRG');
        $SATUAN = $request->input('SATUAN');
        $KODES = $request->input('KODES');
        $QTY    = $request->input('QTY');
        $TAHAP1    = $request->input('TAHAP1');
        $TAHAP2    = $request->input('TAHAP2');
        $TAHAP3    = $request->input('TAHAP3');

        $query = DB::table('ppd')->where('NO_BUKTI', $request->NO_BUKTI)->whereNotIn('NO_ID',  $NO_ID)->delete();

        // Update / Insert
        for ($i = 0; $i < $length; $i++) {
            // Insert jika NO_ID baru
            if ($NO_ID[$i] == 'new') {
                $insert = PpDetail::create(
                    [
                        'NO_BUKTI'   => $request->NO_BUKTI,
                        'REC'        => $REC[$i],
                        'PER'        => $periode,
                        'FLAG'       => $this->FLAGZ,
                        'GOL'        => $this->GOLZ,
                        'KD_BRG'     => ($KD_BRG[$i] == null) ? "" :  $KD_BRG[$i],
                        'NA_BRG'     => ($NA_BRG[$i] == null) ? "" :  $NA_BRG[$i],
                        'SATUAN'     => ($SATUAN[$i] == null) ? "" :  $SATUAN[$i],
                        'KODES'     => ($KODES[$i] == null) ? "" :  $KODES[$i],
                        'QTY'        => (float) str_replace(',', '', $QTY[$i]),
                        'TAHAP1'        => (float) str_replace(',', '', $TAHAP1[$i]),
                        'TAHAP2'        => (float) str_replace(',', '', $TAHAP2[$i]),
                        'TAHAP3'        => (float) str_replace(',', '', $TAHAP3[$i]),

                    ]
                );
            } else {
                // Update jika NO_ID sudah ada
                $upsert = PpDetail::updateOrCreate(
                    [
                        'NO_BUKTI'  => $request->NO_BUKTI,
                        'NO_ID'     => (int) str_replace(',', '', $NO_ID[$i])
                    ],

                    [
                        'REC'        => $REC[$i],

                        'KD_BRG'     => ($KD_BRG[$i] == null) ? "" :  $KD_BRG[$i],
                        'NA_BRG'     => ($NA_BRG[$i] == null) ? "" :  $NA_BRG[$i],
                        'SATUAN'     => ($SATUAN[$i] == null) ? "" :  $SATUAN[$i],
                        'KODES'     => ($KODES[$i] == null) ? "" :  $KODES[$i],
                        'QTY'        => (float) str_replace(',', '', $QTY[$i]),
                        'TAHAP1'        => (float) str_replace(',', '', $TAHAP1[$i]),
                        'TAHAP2'        => (float) str_replace(',', '', $TAHAP2[$i]),
                        'TAHAP3'        => (float) str_replace(',', '', $TAHAP3[$i]),
                        'FLAG'       => $this->FLAGZ,
                        'GOL'        => $this->GOLZ,
                        'PER'        => $periode,
                    ]
                );
            }
        }

 		$pp = Pp::where('NO_BUKTI', $no_buktix )->first();

        $no_bukti = $pp->NO_BUKTI;

        DB::SELECT("UPDATE pp,  ppd
                    SET  ppd.ID =  pp.NO_ID  WHERE  pp.NO_BUKTI =  ppd.NO_BUKTI
                    AND  pp.NO_BUKTI='$no_bukti';");

        DB::SELECT("UPDATE ppd, sup
                SET ppd.PKP = sup.GOLONGAN,
                    ppd.PKP = CASE
                                WHEN sup.GOLONGAN = 'P0' THEN 0
                                ELSE 1
                            END
                WHERE ppd.KODES = sup.KODES
                AND ppd.NO_BUKTI = '$no_bukti';");

        // return redirect('/pp/edit/?idx=' . $pp->NO_ID . '&tipx=edit&flagz=' . $this->FLAGZ . '&golz=' . $this->GOLZ . '&judul=' . $this->judul . '');
        return redirect('/pp?flagz='.$FLAGZ.'&golz='.$GOLZ)->with(['judul' => $judul, 'golz' => $GOLZ, 'flagz' => $FLAGZ ]);


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    // ganti 22

    public function destroy(Request $request, Pp $pp)
    {

		$this->setFlag($request);
        $FLAGZ = $this->FLAGZ;
        $GOLZ = $this->GOLZ;
        $judul = $this->judul;

        // ini dr mana $this->GOLZ?
        $GOLZ = $_GET['golz'];
        $FLAGZ = $_GET['flagz'];

		$per = session()->get('periode')['bulan'] . '/' . session()->get('periode')['tahun'];
        $cekperid = DB::SELECT("SELECT POSTED from perid WHERE PERIO='$per'");
        if ($cekperid[0]->POSTED==1)
        {
            return redirect()->route('pp')
                ->with('status', 'Maaf Periode sudah ditutup!')
                ->with(['judul' => $this->judul, 'flagz' => $this->FLAGZ, 'golz' => $this->GOLZ]);
        }

        $deletePp = Pp::find($pp->NO_ID);

        $deletePp->delete();
        // return redirect('/pp?flagz=' . $FLAGZ . '&golz=J')
        return redirect('/pp?flagz='. $FLAGZ.'&golz='.$GOLZ )
        ->with(['judul' => $judul, 'flagz' => $this->FLAGZ, 'golz' => $this->GOLZ])
        ->with('statusHapus', 'Data ' . $pp->NO_BUKTI . ' berhasil dihapus');

    }

    public function cetak(Pp $pp)
    {
        $no_pp = $pp->NO_BUKTI;

        $file     = 'ppc';
        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

        //pp.GUDANG setelah pp.NETT dihapus
        $query = DB::SELECT("SELECT pp.NO_BUKTI, pp.TGL, pp.TOTAL_QTY, pp.NOTES,
                                    ppd.KD_BRG, ppd.NA_BRG, ppd.SATUAN, ppd.QTY
                            FROM pp, ppd
                            WHERE pp.NO_BUKTI='$no_pp' AND pp.NO_BUKTI = ppd.NO_BUKTI
                            ;
		");


        $data = [];

        foreach ($query as $key => $value) {
            array_push($data, array(
                'NO_BUKTI' => $query[$key]->NO_BUKTI,
                'TGL'      => $query[$key]->TGL,
                'KD_BRG'    => $query[$key]->KD_BRG,
                'NA_BRG'    => $query[$key]->NA_BRG,
                'SATUAN'    => $query[$key]->SATUAN,
                'QTY'    => $query[$key]->QTY,
            ));
        }

        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I");

        DB::SELECT("UPDATE pp SET POSTED = 1 WHERE pp.NO_BUKTI='$no_pp';");

    }



	 public function posting(Request $request)
    {

        $CEK = $request->input('cek');
        $NO_BUKTI = $request->input('NO_BUKTI');

        $usrnmx = Auth::user()->username;

        $hasil = "";

        if ($CEK) {
            foreach ($CEK as $key => $value)
			{

                    //$STA = $request->input('STA');

					$periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
					$bulan    = session()->get('periode')['bulan'];
					$tahun    = substr(session()->get('periode')['tahun'], -2);

			   $NO_BUKTIXZ  = $NO_BUKTI[$key];


                    DB::SELECT("UPDATE PO SET POSTED = 1 WHERE PO.NO_BUKTI='$NO_BUKTIXZ'");

			}
		}
		else
		{
			$hasil = $hasil ."Tidak ada PO yang dipilih! ; ";
		}

					if($hasil!='')
					{
						return redirect('/pp/index-posting')->with('status', 'Proses Ppsting PO ..')->with('gagal', $hasil);
					}
					else
					{
						return redirect('/pp/index-posting')->with('status', 'Ppsting Ppsting PO selesai..');
					}







    }


	// public function jtempo ( Request $request)
    // {
	// 	$tgl = $request->input('TGL');
	// 	$hari = substr($tgl,0,2);
	// 	$bulan = substr($tgl,3,2);
	// 	$tahun = substr($tgl,6,4);
	// 	$harix = $request->HARI;

	// 	$datex = Carbon::createFromDate($tahun, $bulan, $hari );

    //     $datex ->addDays($harix);

    //     $datey = $datex->format('d-m-Y');
	// 	return  $datey;


	// }


	public function getDetailPp(){

        $no_bukti = $_GET['no_bukti'];
        $result = DB::table('ppd')->where('NO_BUKTI', $no_bukti)->get();

        return response()->json($result);;
    }

    public function posting_pp(Request $request)
    {
        if (! $request->isMethod('post')) {
            return response()->json(['error' => 'Method Not Allowed'], 405);
        }

        $data = $request->input('posted');

        if (! $data) {
            return response()->json(['error' => 'Tidak ada data yang dikirim'], 400);
        }

        foreach ($data as $id => $posted) {
            DB::table('pp')->where('NO_ID', $id)->update(['POSTED' => $posted]);
        }

        return response()->json(['message' => 'Status berhasil diperbarui']);
    }

}
