<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
// ganti 1

use App\Models\OTransaksi\Piu;
use App\Models\OTransaksi\PiuDetail;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use DB;
use Carbon\Carbon;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;

// ganti 2
class PiuController extends Controller
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
            $this->judul = "Pembayaran Piutang";
        } 
		
        $this->FLAGZ = $request->flagz;


    }

    public function index(Request $request)
    {

	    $this->setFlag($request);
        // ganti 3
        return view('otransaksi_piu.index')->with(['judul' => $this->judul, 'flagz' => $this->FLAGZ ]);
	
	
    }
	
// ganti 4

    public function getPiu(Request $request)
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
		
        $piu = DB::SELECT("SELECT NO_ID, NO_BUKTI, 
                                TGL, KODEC, NAMAC, KOTA, TOTAL, BAYAR, NOTES, FLAG, POSTED, 
                                USRNM from piu 
                           where PER = '$periode' AND CBG = '$CBG' ORDER BY NO_BUKTI ");
	   	
		
// ganti 6
		
        return Datatables::of($piu)
                ->addIndexColumn()
                ->addColumn('action', function($row) {
                    if ( Auth::user()->divisi=="programmer" ) 
                    {

                    $btnEdit =   ($row->POSTED == 1) ? ' onclick= "alert(\'Transaksi ' . $row->NO_BUKTI . ' sudah diposting!\')" href="#" ' : ' href="piu/edit/?idx=' . $row->NO_ID . '&tipx=edit&flagz=' . $row->FLAG . '&judul=' . $this->judul . '"';					
                    $btnDelete = ($row->POSTED == 1) ? ' onclick= "alert(\'Transaksi ' . $row->NO_BUKTI . ' sudah diposting!\')" href="#" ' : ' onclick="return confirm(&quot; Apakah anda yakin ingin hapus? &quot;)" href="piu/delete/' . $row->NO_ID . '/?flagz=' . $row->FLAG . '" ';


 						 												 
                        $btnPrivilege = 
                        '
                                <a class="dropdown-item" '.$btnEdit.'>
                                <i class="fas fa-edit"></i>
                                    Edit
                                </a>	
                                <a class="dropdown-item btn btn-danger" href="piu/cetak/' . $row->NO_ID . '">
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

        $query = DB::table('piu')->select('NO_BUKTI')->where('PER', $periode)->where('FLAG', $FLAGZ )->where('CBG', $CBG )
                ->orderByDesc('NO_BUKTI')->limit(1)->get();

        if ($query != '[]') {
            $query = substr($query[0]->NO_BUKTI, -4);
            $query = str_pad($query + 1, 4, 0, STR_PAD_LEFT);
            $no_bukti = 'PU' . $CBG . $tahun . $bulan . '-' . $query;
        } else {
            $no_bukti = 'PU' . $CBG . $tahun . $bulan . '-0001';
        }

		
        // Insert Header

// ganti 10
		
        $piu = Piu::create(
            [
                'NO_BUKTI'         => $no_bukti,	
				'TGL'              => date('Y-m-d', strtotime($request['TGL'])),		
                'PER'              => $periode,
                'KODEC'            => ($request['KODEC']==null) ? "" : $request['KODEC'],			
                'NAMAC'            => ($request['NAMAC']==null) ? "" : $request['NAMAC'],
				'FLAG'             => $FLAGZ,
				'NOTES'            => ($request['NOTES']==null) ? "" : $request['NOTES'],
                'BAYAR'            => (float) str_replace(',', '', $request['TBAYAR']),
                'LAIN'             => (float) str_replace(',', '', $request['TLAIN']),
                
				'KODEP'            => ($request['KODEP']==null) ? "" : $request['KODEP'],
				'NAMAP'            => ($request['NAMAP']==null) ? "" : $request['NAMAP'],
                'KOM'             => (float) str_replace(',', '', $request['KOM']),
                'TKOM'             => (float) str_replace(',', '', $request['TKOM']),
				'USRNM'            => Auth::user()->username,
				'TG_SMP'           => Carbon::now(),
				'CBG'              => $CBG
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
				$detail	= new PiuDetail;
				
				// Insert ke Database
				$detail->NO_BUKTI = $no_bukti;			
				$detail->REC	= $REC[$key];
				$detail->PER	= $periode;
				$detail->FLAG	= 'B';
				$detail->NO_FAKTUR = ($NO_FAKTUR[$key]==null) ? "" :  $NO_FAKTUR[$key];
				$detail->TOTAL	= (float) str_replace(',', '', $TOTAL[$key]);
				$detail->BAYAR	= (float) str_replace(',', '', $BAYAR[$key]);					
				$detail->save();
			}
		}
		

//  ganti 11
		// $variablell = DB::select('call piuins(?)',array($no_bukti));

       $no_buktix = $no_bukti;
		
		$piu = Piu::where('NO_BUKTI', $no_buktix )->first();


        DB::SELECT("UPDATE piu, piud
                            SET piud.ID = piu.NO_ID  WHERE piu.NO_BUKTI =
							piud.NO_BUKTI 
							AND piu.NO_BUKTI='$no_buktix';");

		
					 
        return redirect('/piu/edit/?idx=' . $piu->NO_ID . '&tipx=edit&flagz=' . $FLAGZ . '&judul=' . $this->judul . '');


    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */
	 
// ganti 12
	 
   
   public function edit( Request $request , Piu $piu )
    {


		$per = session()->get('periode')['bulan'] . '/' . session()->get('periode')['tahun'];
		
				
        $cekperid = DB::SELECT("SELECT POSTED from perid WHERE PERIO='$per'");
        if ($cekperid[0]->POSTED==1)
        {
            return redirect('/piu')
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
		   
		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from piu
		                 where PER ='$per' and NO_BUKTI = '$buktix'
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
			

		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from piu
		                 where PER ='$per' AND CBG = '$CBG'   
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
			
		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from piu     
		             where PER ='$per' AND CBG = '$CBG'
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
	   
		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from piu    
		             where PER ='$per' AND CBG = '$CBG'
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
		  
    		$bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from piu
						where PER ='$per' AND CBG = '$CBG'
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
			$piu = Piu::where('NO_ID', $idx )->first();	
	     }
		 else
		 {
				$piu = new Piu;
                $piu->TGL = Carbon::now();
      
				
		 }

        $no_bukti = $piu->NO_BUKTI;
	    $piuDetail = DB::table('piud')->where('NO_BUKTI', $no_bukti)->get();	
		
		
		$data = [
            'header'        => $piu,
            'detail'        => $piuDetail,
			
        ];
 
         
         return view('otransaksi_piu.edit', $data)
		 ->with(['tipx' => $tipx, 'idx' => $idx, 'flagz' =>$this->FLAGZ, 'judul' => $this->judul ]);
      
    }


// ganti 18

    public function update(Request $request, Piu $piu )
    {
		
        $this->validate($request,
        [
		
// ganti 19
  //              'NO_PO'       => 'required',
                'TGL'      => 'required'

            ]
        );
		
// ganti 20
		// $variablell = DB::select('call piudel(?)',array($piu['NO_BUKTI']));		

		$this->setFlag($request);
        $FLAGZ = $this->FLAGZ;
        $judul = $this->judul;
		
        $CBG = Auth::user()->CBG;
		
        // ganti 20
        $periode = $request->session()->get('periode')['bulan']. '/' . $request->session()->get('periode')['tahun'];
	
	
        $piu->update(
            [
				'TGL'              => date('Y-m-d', strtotime($request['TGL'])),
                'KODEC'            => ($request['KODEC']==null) ? "" : $request['KODEC'],	
				'NAMAC'			   =>($request['NAMAC']==null) ? "" : $request['NAMAC'],
				'NOTES'            => ($request['NOTES']==null) ? "" : $request['NOTES'],
                'BAYAR'            => (float) str_replace(',', '', $request['TBAYAR']),
                'LAIN'             => (float) str_replace(',', '', $request['TLAIN']),
                
				'KODEP'            => ($request['KODEP']==null) ? "" : $request['KODEP'],
				'NAMAP'            => ($request['NAMAP']==null) ? "" : $request['NAMAP'],
                'KOM'             => (float) str_replace(',', '', $request['KOM']),
                'TKOM'             => (float) str_replace(',', '', $request['TKOM']),
				'USRNM'            => Auth::user()->username,
				'TG_SMP'           => Carbon::now(),
				'CBG'              => $CBG,	
				'FLAG'             => $FLAGZ	
            ]
        );



		$no_buktix = $piu->NO_BUKTI;
		
        // Update Detail
        $length = sizeof($request->input('REC'));
        $NO_ID  = $request->input('NO_ID');
		
        $REC	= $request->input('REC');
		$NO_FAKTUR = $request->input('NO_FAKTUR');
		$BAYAR	= $request->input('BAYAR');
		$TOTAL	= $request->input('TOTAL');

         $query = DB::table('piud')->where('NO_BUKTI', $request->NO_BUKTI)->whereNotIn('NO_ID',  $NO_ID)->delete();

        // Update / Insert
        for ($i=0;$i<$length;$i++) {
            // Insert jika NO_ID baru
            if ($NO_ID[$i] == 'new') {
                $insert = PiuDetail::create(
                    [
                        'NO_BUKTI'   => $request->NO_BUKTI,
                        'REC'        => $REC[$i],
				        'PER'        => $periode,
                        'FLAG'       => 'B',	  							
                        'NO_FAKTUR'  => ($NO_FAKTUR[$i]==null) ? "" :  $NO_FAKTUR[$i],
                        'TOTAL'      => (float) str_replace(',', '', $TOTAL[$i]),
                        'BAYAR'      => (float) str_replace(',', '', $BAYAR[$i]),

                    ]
                );
            } else {
                // Update jika NO_ID sudah ada
                $upsert = PiuDetail::updateOrCreate(
                    [
                        'NO_BUKTI'  => $request->NO_BUKTI,
                        'NO_ID'     => (int) str_replace(',', '', $NO_ID[$i])
                    ],
    
                    [
                        'REC'        => $REC[$i],
                        'NO_FAKTUR'  => ($NO_FAKTUR[$i]==null) ? "" :  $NO_FAKTUR[$i],	
                        'TOTAL'      => (float) str_replace(',', '', $TOTAL[$i]),
                        'BAYAR'      => (float) str_replace(',', '', $BAYAR[$i]),

                    ]
                );
            }
        }


///////////////////////////////////////////

//  ganti 21
		// $variablell = DB::select('call piuins(?)',array($piu['NO_BUKTI']));
		

 		$piu = Piu::where('NO_BUKTI', $no_buktix )->first();
					 
        return redirect('/piu/edit/?idx=' . $piu->NO_ID . '&tipx=edit&flagz=' . $this->FLAGZ . '&judul=' . $this->judul . '');	
	
	
		
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */
	 
// ganti 22
	 
    public function destroy(Request $request,  Piu $piu)
    {

		$this->setFlag($request);
        $FLAGZ = $this->FLAGZ;
        $judul = $this->judul;
		
		$per = session()->get('periode')['bulan'] . '/' . session()->get('periode')['tahun'];
        $cekperid = DB::SELECT("SELECT POSTED AS POSTED from perid WHERE PERIO='$per'");
        if ($cekperid[0]->POSTED==1)
        {
            return redirect()->route('piu')
                ->with('status', 'Maaf Periode sudah ditutup!')
                ->with(['judul' => $this->judul, 'flagz' => $this->FLAGZ]);
        }
		
		// $variablell = DB::select('call piudel(?)',array($piu['NO_BUKTI']));
		
		
// ganti 23
        $deletePiu = Piu::find($piu->NO_ID);

// ganti 24

        $deletePiu->delete();

// ganti 
       return redirect('/piu?flagz='.$FLAGZ)->with(['judul' => $judul, 'flagz' => $FLAGZ ])->with('statusHapus', 'Data '.$piu->NO_BUKTI.' berhasil dihapus');

	
		
    }
    
   
    public function cetak(Piu $piu)
    {
        $no_piu = $piu->NO_BUKTI;

        $file     = 'piuc';
        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

        $query = DB::SELECT("SELECT piu.NO_BUKTI, piu.TGL, piu.KODEC, piu.NAMAC, piu.BACNO, piu.BNAMA, 
                                    piud.NO_FAKTUR, piud.TOTAL,
                                    piud.BAYAR, piud.SISA
                            FROM piu, piud
                            WHERE piu.NO_BUKTI='$no_piu' AND piu.NO_BUKTI = piud.NO_BUKTI 
                            ;
		");

        
        $data = [];

        foreach ($query as $key => $value) {
            array_push($data, array(
                'NO_BUKTI' => $query[$key]->NO_BUKTI,
                'TGL'      => $query[$key]->TGL,
                'KODEC'    => $query[$key]->KODEC,
                'NAMAC'    => $query[$key]->NAMAC,
                'BACNO'    => $query[$key]->BACNO,
                'BNAMA'    => $query[$key]->BNAMA,
                'NO_FAKTUR'       => $query[$key]->NO_FAKTUR,
                'TOTAL'    => $query[$key]->TOTAL,
                'BAYAR'    => $query[$key]->BAYAR,
                'SISA'    => $query[$key]->SISA
            ));
        }

        DB::SELECT("UPDATE piu SET POSTED=1 WHERE NO_BUKTI='$no_piu'");
		
        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I"); 
    }
 
    
}
