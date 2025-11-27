<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
// ganti 1

use App\Models\OTransaksi\Surats;
use App\Models\OTransaksi\SuratsDetail;
use App\Models\Master\Sup;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use DB;
use Carbon\Carbon;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";
use PHPJasperXML;

// ganti 2
class SuratsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resbelinse
     */
    var $judul = '';
    var $FLAGZ = '';
    var $GOLZ = '';
	
    function setFlag(Request $request)
    {
        if ( $request->flagz == 'JL' && $request->golz == 'B' ) {
            $this->judul = "Surat Jalan Bahan Baku";
        } else if ( $request->flagz == 'JL' && $request->golz == 'J' ){
            $this->judul = "Surat Jalan";
        } else if ( $request->flagz == 'AJ' && $request->golz == 'B' ){
            $this->judul = "Retur Surat Jalan Bahan";
        } else if ( $request->flagz == 'AJ' && $request->golz == 'J' ){
            $this->judul = "Retur Surat Jalan";
        }

        $this->FLAGZ = $request->flagz;
        $this->GOLZ = $request->golz;


    }
		
    public function index(Request $request)
    {


	    $this->setFlag($request);
        // ganti 3
        return view('otransaksi_surats.index')->with(['judul' => $this->judul, 'flagz' => $this->FLAGZ, 'golz' => $this->GOLZ ]);
	
		
    }

    public function browse(Request $request)
    {
        $golz = $request->GOL;

        $CBG = Auth::user()->CBG;
		
        $surats = DB::SELECT("SELECT  surats.NO_BUKTI, surats.NO_SPM, surats.NO_SO, surats.KODEC, surats.NAMAC, 
		                  surats.ALAMAT, surats.KOTA, suratsd.KD_BRG, suratsd.NA_BRG, suratsd.QTY, suratsd.HARGA, suratsd.TOTAL from surats, suratsd 
                          WHERE surats.NO_BUKTI = suratsD.NO_BUKTI AND surats.GOL ='$golz' 
                          AND surats.CBG = '$CBG' AND surats.No_RETUR ='' 	");
        return response()->json($surats);
    }
	
	public function browseCust(Request $request)
    {
        // $golz = $request->GOL;

		// $so = DB::SELECT("SELECT KODEC,NAMAC,ALAMAT,KOTA from cust where AKT ='1' order by KODEC");
		$so = DB::SELECT("SELECT KODEC,NAMAC,ALAMAT,KOTA from cust  order by KODEC");
		return response()->json($so);
	}

    public function browseSpm(Request $request)
    {
        $golz = $request->GOL;

        $CBG = Auth::user()->CBG;
		
		$so = DB::SELECT("SELECT  spm.NO_BUKTI, spm.TGL, spm.NO_SO, spm.KODEC, spm.NAMAC, spm.ALAMAT, spm.KOTA, spm.KODEP, spm.NAMAP, spmd.KD_BRG, spmd.NA_BRG, spmd.SATUAN, spmd.QTY from spm, spmd 
                        WHERE spm.NO_BUKTI=spmd.NO_BUKTI AND spm.CBG = '$CBG' 
                        and spm.NO_SURATS =''  AND spm.GOL ='$golz' ");
		return response()->json($so);
	}
	
	public function browse_detail(Request $request)
    {

        // $filterbukti = '';
        // if($request->NO_SO)
        // {

        //     $filterbukti = " WHERE NO_BUKTI='".$request->NO_SO."' ";
        // }
        $spmd= DB::SELECT("SELECT REC, KD_BRG, NA_BRG, SATUAN , QTY, HARGA, TOTAL, KET
                            from spmd
                            where NO_BUKTI='".$request->nobukti."' ORDER BY NO_BUKTI ");
	

		return response()->json($spmd);
	}


    public function browse_detail2(Request $request)
    {
		$filterbukti = '';
		if($request->NO_PO)
		{
	
			$filterbukti = " WHERE NO_BUKTI='".$request->NO_PO."' AND a.KD_BRG = b.KD_BRG ";
		}
		$suratsd = DB::SELECT("SELECT a.REC, a.KD_BRG, a.NA_BRG, a.SATUAN , a.QTY, a.HARGA, a.KIRIM, a.SISA, 
                                b.SATUAN AS SATUAN_PO, a.QTY AS QTY_PO, '1' AS X 
                            from suratsd a, brg b
                            $filterbukti ORDER BY NO_BUKTI ");
	

		return response()->json($suratsd);
	}
    // ganti 4



    public function getSurats(Request $request)
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
		
        $surats = DB::SELECT("SELECT * from surats  WHERE PER='$periode' and FLAG ='$this->FLAGZ' 
                            and GOL ='$this->GOLZ' AND CBG = '$CBG' ORDER BY NO_BUKTI ");
	  
	   
        // ganti 6

        return Datatables::of($surats)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                if (Auth::user()->divisi=="programmer" ) 
				{
                    //CEK POSTED di index dan edit

                    $btnEdit =   ($row->POSTED == 1) ? ' onclick= "alert(\'Transaksi ' . $row->NO_BUKTI . ' sudah diposting!\')" href="#" ' : ' href="surats/edit/?idx=' . $row->NO_ID . '&tipx=edit&flagz=' . $row->FLAG . '&judul=' . $this->judul  . '&golz=' . $row->GOL  . '"';					
                    $btnDelete = ($row->POSTED == 1) ? ' onclick= "alert(\'Transaksi ' . $row->NO_BUKTI . ' sudah diposting!\')" href="#" ' : ' onclick="return confirm(&quot; Apakah anda yakin ingin hapus? &quot;)" href="surats/delete/' . $row->NO_ID . '/?flagz=' . $row->FLAG . '&golz=' . $row->GOL .'" ';


                    $btnPrivilege =
                        '
                                <a class="dropdown-item" ' . $btnEdit . '>
                                <i class="fas fa-edit"></i>
                                    Edit
                                </a>
                                <a class="dropdown-item btn btn-danger" href="surats/cetak/' . $row->NO_ID . '">
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
                'NO_BUKTI'   => 'required',
                'TGL'        => 'required',
                'KODEC'      => 'required',
                'TRUCK'     => 'required',
                'SOPIR'     => 'required',

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

        $query = DB::table('surats')->select('NO_BUKTI')->where('PER', $periode)->where('FLAG', $this->FLAGZ)
                ->where('GOL', $this->GOLZ)->where('CBG', $CBG)->orderByDesc('NO_BUKTI')->limit(1)->get();
		
        if( $GOLZ=='B'){

            if ($query != '[]')
            {
                $query = substr($query[0]->NO_BUKTI, -4);
                $query = str_pad($query + 1, 4, 0, STR_PAD_LEFT);
                $no_bukti = 'SJ'. $this->GOLZ . $CBG . $tahun . $bulan . '-' . $query;
            } else {
                $no_bukti = 'SJ'. $this->GOLZ . $CBG . $tahun . $bulan . '-0001' ;
            }	

        } elseif($GOLZ=='J') {

            if ($query != '[]')
            {
                $query = substr($query[0]->NO_BUKTI, -4);
                $query = str_pad($query + 1, 4, 0, STR_PAD_LEFT);
                $no_bukti = 'SJ'. $CBG . $tahun . $bulan . '-' . $query;
            } else {
                $no_bukti = 'SJ'. $CBG . $tahun . $bulan . '-0001' ;
            }	

        }


			

        $surats = Surats::create(
            [
                'NO_BUKTI'      => $no_bukti,
                'TGL'           => date('Y-m-d', strtotime($request['TGL'])),	
                'JTEMPO'           => date('Y-m-d', strtotime($request['JTEMPO'])),	
                'PER'           => $periode,			
                'FLAG'          => $FLAGZ,							
                'GOL'           => $GOLZ,
                'NO_SPM'         => ($request['NO_SPM']==null) ? "" : $request['NO_SPM'],
                'NO_SO'         => ($request['NO_SO']==null) ? "" : $request['NO_SO'],
                'TRUCK'         => ($request['TRUCK']==null) ? "" : $request['TRUCK'],
                'SOPIR'         => ($request['SOPIR']==null) ? "" : $request['SOPIR'],
                'VIA'           => ($request['VIA']==null) ? "" : $request['VIA'],

                'KODEP'         => ($request['KODEP']==null) ? "" : $request['KODEP'],	
				'NAMAP'		    => ($request['NAMAP']==null) ? "" : $request['NAMAP'],
				
                'KODEC'         => ($request['KODEC']==null) ? "" : $request['KODEC'],	
				'NAMAC'		    =>($request['NAMAC']==null) ? "" : $request['NAMAC'],
				'ALAMAT'		=>($request['ALAMAT']==null) ? "" : $request['ALAMAT'],
				'KOTA'		    =>($request['KOTA']==null) ? "" : $request['KOTA'],
				'NOTES'			=>($request['NOTES']==null) ? "" : $request['NOTES'],
                'TOTAL_QTY'     => (float) str_replace(',', '', $request['TQTY']),
                'TOTAL'      	=> (float) str_replace(',', '', $request['TTOTAL']),
				'USRNM'         => Auth::user()->username,
				'TG_SMP'        => Carbon::now(),
				'CBG'           => $CBG,
            ]
        );


		$REC	= $request->input('REC');
		$NO_SO	= $request->input('NO_SO');
		// $TYP	= $request->input('TYP');
		// $NO_TERIMA = $request->input('NO_TERIMA');
		$KD_BRG	= $request->input('KD_BRG');
		$NA_BRG	= $request->input('NA_BRG');
		$KD_BHN	= $request->input('KD_BHN');
		$NA_BHN	= $request->input('NA_BHN');
		$SATUAN	= $request->input('SATUAN');
		$QTY	= $request->input('QTY');
		$HARGA	= $request->input('HARGA');
		$TOTAL	= $request->input('TOTAL');
		// $MERK	= $request->input('MERK');	
		// $NO_SERI = $request->input('NO_SERI');	
		$KET	= $request->input('KET');	
		// $ID_SOD	= $request->input('ID_SOD');		

		// Check jika value detail ada/tidak
		if ($REC) {
			foreach ($REC as $key => $value) {
				// Declare new data di Model
				$detail	= new SuratsDetail;
				$idSurats = DB::table('surats')->select('NO_ID')->where('NO_BUKTI', $no_bukti)->get();
				// Insert ke Database
				$detail->NO_BUKTI = $no_bukti;	
				// $detail->NO_SO	= $NO_SO[$key];
				$detail->REC	= $REC[$key];
				$detail->PER	= $periode;
				$detail->FLAG	= $FLAGZ;
				$detail->GOL	= $GOLZ;
				// $detail->TYP	= ($TYP[$key]==null) ? '' : $TYP[$key];
				// $detail->NO_TERIMA	= ($NO_TERIMA[$key]==null) ? '' : $NO_TERIMA[$key];
				$detail->KD_BRG	= ($GOLZ == 'B' ) ? ($KD_BRG[$key]==null) : $KD_BRG[$key];
				$detail->NA_BRG	= ($GOLZ == 'B' ) ? ($NA_BRG[$key]==null) : $NA_BRG[$key];
				$detail->KD_BHN	= ($GOLZ == 'J' ) ? ($KD_BHN[$key]==null) : $KD_BHN[$key];
				$detail->NA_BHN	= ($GOLZ == 'J' ) ? ($NA_BHN[$key]==null) : $NA_BHN[$key];
				$detail->SATUAN	= ($SATUAN[$key]==null) ? '' : $SATUAN[$key];
				$detail->QTY	= (float) str_replace(',', '', $QTY[$key]);
				$detail->SISA	= (float) str_replace(',', '', $QTY[$key]);
				$detail->HARGA	= (float) str_replace(',', '', $HARGA[$key]);
				$detail->TOTAL	= (float) str_replace(',', '', $TOTAL[$key]);
				// $detail->MERK	= ($MERK[$key]==null) ? '' : $MERK[$key];
				// $detail->NO_SERI	= ($NO_SERI[$key]==null) ? '' : $NO_SERI[$key];
				$detail->KET	= ($KET[$key]==null) ? '' : $KET[$key];
				$detail->ID	    = $idSurats[0]->NO_ID;
				// $detail->ID_SOD	= ($ID_SOD[$key]==null) ? '' : $ID_SOD[$key];
				$detail->save();
			}
		}	
		
		$no_buktix = $no_bukti;
		
		$surats = Surats::where('NO_BUKTI', $no_buktix )->first();

        // DB::SELECT("CALL suratsins('$no_buktix')");

        DB::SELECT("UPDATE surats,  suratsd
                            SET  suratsd.ID =  surats.NO_ID  WHERE  surats.NO_BUKTI =  suratsd.NO_BUKTI 
							AND  surats.NO_BUKTI='$no_buktix';");

		
					 
        return redirect('/surats/edit/?idx=' . $surats->NO_ID . '&tipx=edit&flagz=' . $this->FLAGZ . '&judul=' . $this->judul . '&golz=' . $this->GOLZ . '');
		
    }

   public function edit( Request $request , Surats $surats)
    {


		$per = session()->get('periode')['bulan'] . '/' . session()->get('periode')['tahun'];
		
				
        // $cekperid = DB::SELECT("SELECT POSTED from perid WHERE PERIO='$per'");
        // if ($cekperid[0]->POSTED==1)
        // {
        //     return redirect('/surats')
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
		   
		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from surats
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
			

		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from surats
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
			
		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from surats     
		             where PER ='$per' 
					 and FLAG ='$this->FLAGZ' AND CBG = '$CBG' and NO_BUKTI < 
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
	   
		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from surats    
		             where PER ='$per'  
					 and FLAG ='$this->FLAGZ' AND CBG = '$CBG' and NO_BUKTI > 
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
		  
    		$bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from surats
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
			$surats = Surats::where('NO_ID', $idx )->first();	
	     }
		 else
		 {
				$surats = new Surats;
                $surats->TGL = Carbon::now();
				
				
		 }

        $no_bukti = $surats->NO_BUKTI;
        $suratsDetail = DB::table('suratsd')->where('NO_BUKTI', $no_bukti)->orderBy('REC')->get();
		
		$data = [
            'header'        => $surats,
			'detail'        => $suratsDetail

        ];
 
 		$sup = DB::SELECT("SELECT KODES, CONCAT(NAMAS,'-',KOTA) AS NAMAS FROM SUP 
		                 ORDER BY NAMAS ASC" );
		
         
         return view('otransaksi_surats.edit', $data)->with(['sup' => $sup])
		 ->with(['tipx' => $tipx, 'idx' => $idx, 'flagz' => $this->FLAGZ, 'golz' =>$this->GOLZ, 'judul'=> $this->judul ]);
			 

    }

  /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Resbelinse
     */

    // ganti 18

    public function update(Request $request, Surats $surats)
    {

        $this->validate(
            $request,
            [

                'NO_BUKTI'   => 'required',
                'TGL'        => 'required',
                'KODEC'      => 'required',
                'TRUCK'     => 'required',
                'SOPIR'     => 'required',
            ]
        );

		$this->setFlag($request);
        $GOLZ = $this->GOLZ;
        $FLAGZ = $this->FLAGZ;
        $judul = $this->judul;
		
        $CBG = Auth::user()->CBG;
		
        $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];


        $surats->update(
            [
                'TGL'           => date('Y-m-d', strtotime($request['TGL'])),	
                'JTEMPO'           => date('Y-m-d', strtotime($request['JTEMPO'])),	
                'NO_SO'         => ($request['NO_SO']==null) ? "" : $request['NO_SO'],
                'NO_SPM'         => ($request['NO_SO']==null) ? "" : $request['NO_SPM'],
                'TRUCK'         => ($request['TRUCK']==null) ? "" : $request['TRUCK'],
                'SOPIR'         => ($request['SOPIR']==null) ? "" : $request['SOPIR'],
                'VIA'           => ($request['VIA']==null) ? "" : $request['VIA'],
                
                'KODEP'         => ($request['KODEP']==null) ? "" : $request['KODEP'],	
				'NAMAP'		    => ($request['NAMAP']==null) ? "" : $request['NAMAP'],
				
                'KODEC'         => ($request['KODEC']==null) ? "" : $request['KODEC'],	
				'NAMAC'		    =>($request['NAMAC']==null) ? "" : $request['NAMAC'],
				'ALAMAT'		=>($request['ALAMAT']==null) ? "" : $request['ALAMAT'],
				'KOTA'		    =>($request['KOTA']==null) ? "" : $request['KOTA'],
				'NOTES'			=>($request['NOTES']==null) ? "" : $request['NOTES'],
                'TOTAL_QTY'     => (float) str_replace(',', '', $request['TQTY']),
                'TOTAL'      	=> (float) str_replace(',', '', $request['TTOTAL']),
				'USRNM'         => Auth::user()->username,						
                'GOL'           => $GOLZ,
                'FLAG'          => $FLAGZ,
				'TG_SMP'        => Carbon::now(),					
				'CBG'           => $CBG,					
                
            ]
        );

		$no_buktix = $surats->NO_BUKTI;
		
        // Update Detail
        $length = sizeof($request->input('REC'));
        $NO_ID  = $request->input('NO_ID');
		$REC	= $request->input('REC');
		// $NO_SO	= $request->input('NO_SO');
		// $TYP	= $request->input('TYP');
		// $NO_TERIMA = $request->input('NO_TERIMA');
		$KD_BRG	= $request->input('KD_BRG');
		$NA_BRG	= $request->input('NA_BRG');
		$KD_BHN	= $request->input('KD_BHN');
		$NA_BHN	= $request->input('NA_BHN');
		$SATUAN	= $request->input('SATUAN');
		$QTY	= $request->input('QTY');
		$HARGA	= $request->input('HARGA');
		$TOTAL	= $request->input('TOTAL');
		// $MERK	= $request->input('MERK');	
		// $NO_SERI = $request->input('NO_SERI');	
		$KET	= $request->input('KET');	
		$ID_SOD	= $request->input('ID_SOD');	
       
       // Delete yang NO_ID tidak ada di input
        $query = DB::table('suratsd')->where('NO_BUKTI', $surats->NO_BUKTI)->whereNotIn('NO_ID',  $NO_ID)->delete();

        // Update / Insert
        for ($i=0;$i<$length;$i++) {
            // Insert jika NO_ID baru
            if ($NO_ID[$i] == 'new') {
                $insert = SuratsDetail::create(
                    [
                        'NO_BUKTI'   => $surats->NO_BUKTI,
                        // 'NO_SO'      => ($NO_SO[$i]==null) ? "" :  $NO_SO[$i],
                        'REC'        => $REC[$i],
				        'PER'        => $surats->PER,	
				        'FLAG'       => $FLAGZ,					
				        'GOL'        => $GOLZ,					
                        // 'TYP'        => ($TYP[$i]==null) ? "" :  $TYP[$i],
                        // 'NO_TERIMA'  => ($NO_TERIMA[$i]==null) ? "" :  $NO_TERIMA[$i],	
                        'KD_BRG'     => ($GOLZ == 'B' ) ? ($KD_BRG[$i]==null) :  $KD_BRG[$i],
                        'NA_BRG'     => ($GOLZ == 'B' ) ? ($NA_BRG[$i]==null) : $NA_BRG[$i],	
                        'KD_BHN'     => ($GOLZ == 'J' ) ? ($KD_BHN[$i]==null) :  $KD_BHN[$i],
                        'NA_BHN'     => ($GOLZ == 'J' ) ? ($NA_BHN[$i]==null) : $NA_BHN[$i],	
                        'SATUAN'     => ($SATUAN[$i]==null) ? "" : $SATUAN[$i],
						'QTY'      	 => (float) str_replace(',', '', $QTY[$i]),
						'SISA'       => (float) str_replace(',', '', $QTY[$i]),
						'HARGA'      => (float) str_replace(',', '', $HARGA[$i]),
						'TOTAL'      => (float) str_replace(',', '', $TOTAL[$i]),
                        // 'MERK'       => ($MERK[$i]==null) ? "" : $MERK[$i],
                        // 'NO_SERI'    => ($NO_SERI[$i]==null) ? "" : $NO_SERI[$i],
                        'KET'        => ($KET[$i]==null) ? "" : $KET[$i],
                        'ID'         => $surats->NO_ID,
                        // 'ID_SOD'     => ($ID_SOD[$i]==null) ? "" : $ID_SOD[$i],
                    ]
                );
            } else {
                // Update jika NO_ID sudah ada
                $update = SuratsDetail::updateOrCreate(
                    [
                        'NO_BUKTI'  => $surats->NO_BUKTI,
                        'NO_ID'     => (int) str_replace(',', '', $NO_ID[$i])
                    ],
    
                    [
                        // 'NO_SO'      => ($NO_SO[$i]==null) ? "" :  $NO_SO[$i],
                        'REC'        => $REC[$i],
				        'FLAG'       => $FLAGZ,					
				        'GOL'        => $GOLZ,					
                        // 'TYP'        => ($TYP[$i]==null) ? "" :  $TYP[$i],
                        // 'NO_TERIMA'  => ($NO_TERIMA[$i]==null) ? "" :  $NO_TERIMA[$i],	
                        'KD_BRG'     => ($GOLZ == 'B' ) ? ($KD_BRG[$i]==null) :  $KD_BRG[$i],
                        'NA_BRG'     => ($GOLZ == 'B' ) ? ($NA_BRG[$i]==null) : $NA_BRG[$i],	
                        'KD_BHN'     => ($GOLZ == 'J' ) ? ($KD_BHN[$i]==null) :  $KD_BHN[$i],
                        'NA_BHN'     => ($GOLZ == 'J' ) ? ($NA_BHN[$i]==null) : $NA_BHN[$i],	
                        'SATUAN'     => ($SATUAN[$i]==null) ? "" : $SATUAN[$i],
						'QTY'      	 => (float) str_replace(',', '', $QTY[$i]),
						'SISA'       => (float) str_replace(',', '', $QTY[$i]),
						'HARGA'      => (float) str_replace(',', '', $HARGA[$i]),
						'TOTAL'      => (float) str_replace(',', '', $TOTAL[$i]),
                        // 'MERK'       => ($MERK[$i]==null) ? "" : $MERK[$i],
                        // 'NO_SERI'    => ($NO_SERI[$i]==null) ? "" : $NO_SERI[$i],
                        'KET'        => ($KET[$i]==null) ? "" : $KET[$i],
                        // 'ID_SOD'     => ($ID_SOD[$i]==null) ? "" : $ID_SOD[$i],
                    ]
                );
            }
        }	   
	   
        // DB::SELECT("CALL suratsins('$surats->NO_BUKTI')");

 		$surats = Surats::where('NO_BUKTI', $no_buktix )->first();

        $no_bukti = $surats->NO_BUKTI;

        DB::SELECT("UPDATE surats,  suratsd
                    SET  suratsd.ID =  surats.NO_ID  WHERE  surats.NO_BUKTI =  suratsd.NO_BUKTI 
                    AND  surats.NO_BUKTI='$no_bukti';");
					 
        return redirect('/surats/edit/?idx=' . $surats->NO_ID . '&tipx=edit&flagz=' . $this->FLAGZ . '&judul=' . $this->judul . '&golz=' . $this->GOLZ . '');	
		
	   
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Resbelinse
     */

    // ganti 22

    public function destroy(Request $request, Surats $surats)
    {

		$this->setFlag($request);
        $FLAGZ = $this->FLAGZ;
        $GOLZ = $this->GOLZ;
        $judul = $this->judul;
		
		$per = session()->get('periode')['bulan'] . '/' . session()->get('periode')['tahun'];
        $cekperid = DB::SELECT("SELECT POSTED from perid WHERE PERIO='$per'");
        if ($cekperid[0]->POSTED==1)
        {
            return redirect()->route('surats')
                ->with('status', 'Maaf Periode sudah ditutup!')
                ->with(['judul' => $this->judul, 'flagz' => $this->FLAGZ, 'golz' => $this->GOLZ]);
        }
        
        // DB::SELECT("CALL suratsdel('$surats->NO_BUKTI')");
		
        $deleteSurats = Surats::find($surats->NO_ID);

        $deleteSurats->delete();

       return redirect('/surats?flagz='.$FLAGZ.'&golz='.$GOLZ)->with(['judul' => $judul, 'flagz' => $FLAGZ, 'golz' => $GOLZ ])->with('statusHapus', 'Data '.$surats->NO_BUKTI.' berhasil dihapus');


    }
    
    public function cetak(Surats $surats)
    {
        $no_surats = $surats->NO_BUKTI;

        $file     = 'suratsc';
        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

        $query = DB::SELECT("SELECT surats.NO_BUKTI, surats.TGL, surats.NAMAC, surats.NO_SO, suratsd.KD_BRG, suratsd.NA_BRG, 
                                    suratsd.QTY, suratsd.HARGA, suratsd.TOTAL, surats.KODEP, surats.NAMAP, 
                                    surats.ALAMAT, surats.KOTA
                            FROM surats, suratsd 
                            WHERE surats.NO_BUKTI = suratsd.NO_BUKTI AND surats.NO_BUKTI='$no_surats' 
                            ;
		");

        
        $data = [];

        foreach ($query as $key => $value) {
            array_push($data, array(
                'NO_BUKTI' => $query[$key]->NO_BUKTI,
                'TGL'      => $query[$key]->TGL,
                'NAMAC'  => $query[$key]->NAMAC,
                'NO_SO'    => $query[$key]->NO_SO,
                'KD_BRG'   => $query[$key]->KD_BRG,
                'NA_BRG'   => $query[$key]->NA_BRG,
                'QTY_BELI' => $query[$key]->QTY_BELI,
                'ALAMAT'  => $query[$key]->ALAMAT,
                'KOTA'    => $query[$key]->KOTA,
                'NOPOL'    => $query[$key]->NOPOL,
                'TELP'     => $query[$key]->TELP,
                'SEAL'     => $query[$key]->SEAL,
                'T_CONT'   => $query[$key]->T_CONT,
                'QTY'      => $query[$key]->QTY,
            ));
        }
		
        DB::SELECT("UPDATE SURATS SET POSTED=1 WHERE NO_BUKTI='$no_surats'");

        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I");
       
       
    }
	
	
	
	 public function posting(Request $request)
    {
      

    }
	
	
	
	
	
	
	
}
