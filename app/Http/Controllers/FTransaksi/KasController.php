<?php

namespace App\Http\Controllers\FTransaksi;

use App\Http\Controllers\Controller;
// ganti 1

use App\Models\FTransaksi\Kas;
use App\Models\FTransaksi\KasDetail;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use DB;
use Carbon\Carbon;


include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;




// ganti 2
class KasController extends Controller
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
        if ( $request->flagz == 'BKK' ) {
            // $this->judul = "Sumber Dana Keluar";
            $this->judul = "Kas Keluar";
        } else if ( $request->flagz == 'BKM' ) {
            // $this->judul = "Sumber Dana Masuk";
            $this->judul = "Kas Masuk";
        }
		
        $this->FLAGZ = $request->flagz;
    
		

    }	 
	 
	 
	 
    public function index(Request $request)
    {

        // ganti 3
        $this->setFlag($request);
        // ganti 3
        return view('ftransaksi_kas.index')->with(['judul' => $this->judul, 'flagz' => $this->FLAGZ ]);
    }

    // ganti 4


    public function browse_bukti(Request $request)
    {
        $NO_BUKTI = $request->NO_BUKTI;

        $kel = DB::SELECT("SELECT KAS.NO_ID AS NO_IDHX, KAS.NO_BUKTI, KAS.TGL, KAS.BACNO, KAS.BNAMA, KAS.KET, KAS.JUMLAH AS TJUMLAH,
		                   KASD.REC, KASD.NO_ID, KASD.ACNO, KASD.NACNO, KASD.URAIAN, KASD.JUMLAH, KASD.VAL FROM 
						   KAS, KASD WHERE KAS.NO_BUKTI = KASD.NO_BUKTI and KASD.VAL = 0 
						   AND KAS.NO_BUKTI ='$NO_BUKTI' ");
       
		return response()->json($kel);
    }
	
    public function getKas(Request $request)
    {
        // ganti 5

        if ($request->session()->has('periode')) {
            $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
        } else {
            $periode = '';
        }

		$CBG = Auth::user()->CBG;

        $this->setFlag($request);
		
        $kas = DB::SELECT("SELECT * from kas  where  PER ='$periode' and TYPE ='$this->FLAGZ' AND CBG='$CBG' ORDER BY NO_BUKTI ");
	   

        // ganti 6

        return Datatables::of($kas)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                if (Auth::user()->divisi=="programmer" || Auth::user()->divisi=="owner" || Auth::user()->divisi=="assistant" || Auth::user()->divisi=="accounting") 
                {
                    // CEK  SUDAH POSTED di INDEX dan EDIT
                    // <a class="dropdown-item btn btn-danger" onclick="return confirm(&quot; Apakah anda yakin ingin hapus? &quot;)" href="kas/delete/'. $row->NO_ID .'">
				
                    // url untuk delete di index
                    $url = "'".url("kas/delete/" . $row->NO_ID . "/?flagz=" . $row->FLAG)."'";
                    // batas

                    $btnEdit =   ($row->POSTED == 1) ? ' onclick= "alert(\'Transaksi ' . $row->NO_BUKTI . ' sudah diposting!\')" href="#" ' : ' href="kas/edit/?idx=' . $row->NO_ID . '&tipx=edit&flagz=' . $row->TYPE . '&judul=' . $this->judul . '"';
				
                    $btnDelete = ($row->POSTED == 1) ? ' onclick= "alert(\'Transaksi ' . $row->NO_BUKTI . ' sudah diposting!\')" href="#" ' : ' onclick="deleteRow('.$url.')" ';



             

                    $btnPrivilege =
                        '								
                                <a class="dropdown-item" ' . $btnEdit . '>
                                <i class="fas fa-edit"></i>
                                    Edit
                                </a>
                                <a class="dropdown-item btn btn-danger" href="kas/cetak/' . $row->NO_ID . '">
                                    <i class="fa fa-trash" aria-hidden="true"></i>
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
    public function store(Request $request, Kas $kas )
    {
		
        $this->validate(
            $request,
            // GANTI 9

            [
                'NO_BUKTI'     => 'required',
                'TGL'          => 'required',
                'BACNO'        => 'required'

            ]
        );

        //////     nomer otomatis

		
		$this->setFlag($request);
        $FLAGZ = $this->FLAGZ;
        $judul = $this->judul;	
		
        $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
        
        $CBG = Auth::user()->CBG;

        $bulan    = session()->get('periode')['bulan'];
        $tahun    = substr(session()->get('periode')['tahun'], -2);
        $query = DB::table('kas')->select('NO_BUKTI')->where('PER', $periode)->where('TYPE', $this->FLAGZ)->where('CBG', $CBG)->orderByDesc('NO_BUKTI')->limit(1)->get();

        // Check apakah No Bukti terakhir NULL
        if ($query != '[]') {
            $query = substr($query[0]->NO_BUKTI, -4);
            $query = str_pad($query + 1, 4, 0, STR_PAD_LEFT);
            $no_bukti = $this->FLAGZ . $CBG . $tahun . $bulan . '-' . $query;
        } else {
            $no_bukti = $this->FLAGZ . $CBG . $tahun . $bulan . '-0001';
        }




        // Insert Header

        // ganti 10
  
        $kas = Kas::create(
            [
                'NO_BUKTI'         => $no_bukti,
                'TGL'              => date('Y-m-d', strtotime($request['TGL'])),
                'PER'              => $periode,
                'BACNO'            => ($request['BACNO'] == null) ? "" : $request['BACNO'],
                'BNAMA'            => ($request['BNAMA'] == null) ? "" : $request['BNAMA'],
                'FLAG'             => 'K',
                'TYPE'             => $FLAGZ,
                'KET'              => ($request['KET'] == null) ? "" : $request['KET'],
                'JUMLAH'           => (float) str_replace(',', '', $request['TJUMLAH']),
                'USRNM'            => Auth::user()->username,
                'created_by'       => Auth::user()->username,
                'CBG'              => $CBG,
                'TG_SMP'           => Carbon::now()

            ]
        );

        // Insert Detail
        $REC    = $request->input('REC');
        $ACNO    = $request->input('ACNO');
        $NACNO    = $request->input('NACNO');
        $NO_HUT    = $request->input('NO_HUT');
        $URAIAN    = $request->input('URAIAN');
        $JUMLAH    = $request->input('JUMLAH');

        // Check jika value detail ada/tidak
        if ($REC) {
            foreach ($REC as $key => $value) {
                // Declare new data di Model
                $detail    = new KasDetail;

                // Insert ke Database
                $detail->NO_BUKTI       = $no_bukti;
                $detail->REC            = $REC[$key];
                $detail->PER            = $periode;
                $detail->FLAG           = 'K';
                $detail->TYPE           = $FLAGZ;
                $detail->ACNO           = ($ACNO[$key] == null) ? "" :  $ACNO[$key];
                $detail->NACNO          = ($NACNO[$key] == null) ? "" :  $NACNO[$key];
                $detail->NO_FAKTUR      = ($NO_HUT[$key] == null) ? "" :  $NO_HUT[$key];
                $detail->URAIAN         = ($URAIAN[$key] == null) ? "" :  $URAIAN[$key];
                $detail->JUMLAH         = (float) str_replace(',', '', $JUMLAH[$key]);
                $detail->DEBET          =  ($this->FLAGZ == 'BKM') ? (float) str_replace(',', '', $JUMLAH[$key]) : 0 ;
                $detail->KREDIT         =  ($this->FLAGZ == 'BKK') ? (float) str_replace(',', '', $JUMLAH[$key]) : 0 ;
                $detail->save();
            }
        }

        //  ganti 11
        $variablell = DB::select('call kasins(?)', array($no_bukti));
		
	    $no_buktix = $no_bukti;
		
		$kas = Kas::where('NO_BUKTI', $no_buktix )->first();

        DB::SELECT("UPDATE KAS, ACCOUNT
                            SET KAS.BNAMA = ACCOUNT.NAMA  WHERE KAS.BACNO = ACCOUNT.ACNO 
							AND KAS.NO_BUKTI='$no_buktix';");

        DB::SELECT("UPDATE kas, hut
                    SET kas.NO_BUKTI = hut.NO_KASIR WHERE kas.NO_BUKTI='$no_buktix';");
							
        DB::SELECT("UPDATE KAS, KASD
                            SET KASD.ID = KAS.NO_ID  WHERE KAS.NO_BUKTI = KASD.NO_BUKTI 
							AND KAS.NO_BUKTI='$no_buktix';");
							
        //return redirect('/kas/edit/?idx=' . $kas->NO_ID . '&tipx=edit&flagz=' . $this->FLAGZ . '&judul=' . $this->judul . '');
		return redirect('/kas?flagz='.$FLAGZ)->with(['judul' => $judul, 'flagz' => $FLAGZ ]);
			
    }


    // ganti 15

    public function edit( Request $request , Kas $kas)
    {


		$per = session()->get('periode')['bulan'] . '/' . session()->get('periode')['tahun'];
		
				
        $cekperid = DB::SELECT("SELECT POSTED from perid WHERE PERIO='$per'");
        if ($cekperid[0]->POSTED==1)
        {
            return redirect('/kas')
			       ->with('status', 'Maaf Periode sudah ditutup!')
                   ->with(['judul' => $judul, 'flagz' => $FLAGZ]);
        }
		
		$this->setFlag($request);
		
        $tipx = $request->tipx;

        $CBG = Auth::user()->CBG;

		$idx = $request->idx;
			

		
		if ( $idx =='0' && $tipx=='undo'  )
	    {
			$tipx ='top';
			
		   }
		   
		 
		   
		if ($tipx=='search') {
			
		   	
    	   $buktix = $request->buktix;
		   
		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from kas 
		                 where PER ='$per' and TYPE ='$this->FLAGZ' 
						 and NO_BUKTI = '$buktix' and CBG = '$CBG'					 
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
			

		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from kas 
		                 where PER ='$per' and TYPE ='$this->FLAGZ' 
                         and CBG = '$CBG'	    
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
			
		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from KAS      
		             where PER ='$per' and TYPE ='$this->FLAGZ' 
                     and CBG = '$CBG' and NO_BUKTI < 
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
	   
		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from KAS    
		             where PER ='$per' and TYPE ='$this->FLAGZ' 
                     and CBG = '$CBG' and NO_BUKTI > 
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
		  
    		$bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from KAS  where PER ='$per'
            			and TYPE ='$this->FLAGZ' 
                        and CBG = '$CBG'   
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
			$kas = Kas::where('NO_ID', $idx )->first();	
	     }
		 else
		 {
				$kas = new Kas;
                $kas->TGL = Carbon::now();
      
				
		 }

        $no_bukti = $kas->NO_BUKTI;				
        $kasDetail = DB::table('kasd')->where('NO_BUKTI', $no_bukti)->get();

        $data = [
            'header'        => $kas,
            'detail'        => $kasDetail
        ];
 
         
         return view('ftransaksi_kas.edit', $data)
		 ->with(['tipx' => $tipx, 'idx' => $idx, 'flagz' =>$this->FLAGZ, 'judul'=> $this->judul ]);
			 
    
      
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    // ganti 18

    public function update(Request $request, Kas $kas)
    {
        // return $request;
        $this->validate(
            $request,
            [
                // ganti 19
                'TGL'       => 'required',
                'BACNO'     => 'required'
            ]
        );

        // ganti 20
		
		
        $variablell = DB::select('call kasdel(?)', array($kas['NO_BUKTI']));
    
		
		$this->setFlag($request);
        $FLAGZ = $this->FLAGZ;
        $judul = $this->judul;	
		
        $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];

        $CBG = Auth::user()->CBG;
	  
        $kas->update(
            [

                'TGL'              => date('Y-m-d', strtotime($request['TGL'])),
                'BACNO'            => ($request['BACNO'] == null) ? "" : $request['BACNO'],
                'BNAMA'            => ($request['BNAMA'] == null) ? "" : $request['BNAMA'],
                'JUMLAH'           => (float) str_replace(',', '', $request['TJUMLAH']),
                'KET'              => ($request['KET'] == null) ? "" : $request['KET'],
                'USRNM'            => Auth::user()->username,
                'updated_by'       => Auth::user()->username,
                'CBG'              => $CBG,
                'TG_SMP'           => Carbon::now()

            ]
        );

		
		$no_buktix = $kas->NO_BUKTI;
		
        // Update Detail
        $length = sizeof($request->input('REC'));
		$NO_ID  = $request->input('NO_ID');
		$REC    = $request->input('REC');
        $ACNO    = $request->input('ACNO');
        $NACNO    = $request->input('NACNO');
        $NO_HUT    = $request->input('NO_HUT');
        $URAIAN    = $request->input('URAIAN');
        $JUMLAH    = $request->input('JUMLAH');




        // Delete yang NO_ID tidak ada di input
        $query = DB::table('kasd')->where('NO_BUKTI', $request->NO_BUKTI)->whereNotIn('NO_ID',  $NO_ID)->delete();

        
        // Update / Insert
        for ($i = 0; $i < $length; $i++) {
            // Insert jika NO_ID baru
            if ($NO_ID[$i] == 'new') {
                $insert = KasDetail::create(
                    [
                        'NO_BUKTI'   => $no_buktix,
                        'REC'        => $REC[$i],
                        'PER'        => $periode,
                        'FLAG'       => 'K',
                        'TYPE'       =>  $FLAGZ,
                        'ACNO'       => ($ACNO[$i] == null) ? "" :  $ACNO[$i],
                        'NACNO'      => ($NACNO[$i] == null) ? "" : $NACNO[$i],
                        'NO_FAKTUR'  => ($NO_HUT[$i] == null) ? "" : $NO_HUT[$i],
                        'URAIAN'     => ($URAIAN[$i] == null) ? "" : $URAIAN[$i],
                        'JUMLAH'     => (float) str_replace(',', '', $JUMLAH[$i]),
                        'DEBET'      =>  ($FLAGZ == 'BKM') ? (float) str_replace(',', '', $JUMLAH[$i]) : 0 ,
                        'KREDIT'     =>  ($FLAGZ == 'BKK') ? (float) str_replace(',', '', $JUMLAH[$i]) : 0 

					
						
						
						

                    ]
                );
            } else {
                // Update jika NO_ID sudah ada
                $update = KasDetail::updateOrCreate(
                    [
                        'NO_BUKTI'  => $no_buktix,
                        'NO_ID'     => (int) str_replace(',', '', $NO_ID[$i])
                    ],

                    [
                        'REC'        => $REC[$i],
                        'ACNO'       => ($ACNO[$i] == null) ? "" :  $ACNO[$i],
                        'NACNO'      => ($NACNO[$i] == null) ? "" : $NACNO[$i],
                        'NO_FAKTUR'  => ($NO_HUT[$i] == null) ? "" : $NO_HUT[$i],
                        'URAIAN'     => ($URAIAN[$i] == null) ? "" : $URAIAN[$i],
                        'JUMLAH'     => (float) str_replace(',', '', $JUMLAH[$i]),
                        'DEBET'      =>  ($FLAGZ == 'BKM') ? (float) str_replace(',', '', $JUMLAH[$i]) : 0 ,
                        'KREDIT'     =>  ($FLAGZ == 'BKK') ? (float) str_replace(',', '', $JUMLAH[$i]) : 0 

                    ]
                );
            }
        }

 
        // ganti 21
        $variablell = DB::select('call kasins(?)', array($kas['NO_BUKTI']));
		
		$kas = Kas::where('NO_BUKTI', $no_buktix )->first();

        DB::SELECT("UPDATE KAS, ACCOUNT
                            SET KAS.BNAMA = ACCOUNT.NAMA  WHERE KAS.BACNO = ACCOUNT.ACNO 
							AND KAS.NO_BUKTI='$no_buktix';");

        DB::SELECT("UPDATE kas, hut
                    SET kas.NO_BUKTI = hut.NO_KASIR WHERE kas.NO_BUKTI='$no_buktix';");
							
        DB::SELECT("UPDATE KAS, KASD
                            SET KASD.ID = KAS.NO_ID  WHERE KAS.NO_BUKTI = KASD.NO_BUKTI 
							AND KAS.NO_BUKTI='$no_buktix';");
							
		return redirect('/kas?flagz='.$FLAGZ)->with(['judul' => $judul, 'flagz' => $FLAGZ  ]);

	}


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    // ganti 22

    public function destroy( Request $request, Kas $kas)
    {
        
		$this->setFlag($request);
        $FLAGZ = $this->FLAGZ;
        $judul = $this->judul;
		
		$per = session()->get('periode')['bulan'] . '/' . session()->get('periode')['tahun'];
        $cekperid = DB::SELECT("SELECT POSTED from perid WHERE PERIO='$per'");
		
		
        if ($cekperid[0]->POSTED==1)
        {
            return redirect()->route('kas')
                ->with('status', 'Maaf Periode sudah ditutup!')
                ->with(['judul' => $judul, 'flagz' => $FLAGZ]);
        }
		
		
		
		
        $variablell = DB::select('call kasdel(?)', array($kas['NO_BUKTI']));

        // ganti 23
        $deleteKas = Kas::find($kas->NO_ID);

        // ganti 24
        $deleteKas->delete();

		 
        // ganti 
		return redirect('/kas?flagz='.$FLAGZ)->with(['judul' => $judul, 'flagz' => $FLAGZ ])->with('statusHapus', 'Data '.$kas->NO_BUKTI.' berhasil dihapus');


    }


    public function cetak (Kas $kas)
    {
        $no_bukti = $kas->NO_BUKTI;

        $file     = 'kasc';
        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

		$judul = '';
		if($kas->TYPE =='BKK'){
			$judul ='Bukti Kas Keluar';
		} else {
			$judul = 'Bukti Kas Masuk';
		}
		
        $query = DB::SELECT("
			SELECT kas.NO_BUKTI,kas.TGL,kas.KET,kas.BNAMA,
            kasd.REC,kasd.ACNO,kasd.NACNO,kasd.URAIAN,kasd.JUMLAH as JUMLAH 
			FROM kas, kasd 
			WHERE kas.NO_BUKTI=kasd.NO_BUKTI and kas.NO_BUKTI='$no_bukti' 
			ORDER BY kas.NO_BUKTI;
		");
		
		$query2 = DB::SELECT("
			SELECT NAMA from compan ;
		");
		

        $data = [];
        foreach ($query as $key => $value) {
            array_push($data, array(
                'NO_BUKTI' => $query[$key]->NO_BUKTI,
                'TGL' => $query[$key]->TGL,
                'KET' => $query[$key]->KET,
                'BNAMA' => $query[$key]->BNAMA,
                'REC' => $query[$key]->REC,
                'ACNO' => $query[$key]->ACNO,
                'NACNO' => $query[$key]->NACNO,
                'URAIAN' => $query[$key]->URAIAN,
                'JUMLAH' => $query[$key]->JUMLAH,
                'JUDUL' => $judul,
				'NAMA' => $query2[0]->NAMA
            ));
        }
        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I");
    }

    public function getDetailKas(){

        $no_bukti = $_GET['no_bukti'];
        $result = DB::table('kasd')->where('NO_BUKTI', $no_bukti)->get();
        
        return response()->json($result);;
    }

    
}
