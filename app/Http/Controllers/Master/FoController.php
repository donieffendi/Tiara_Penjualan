<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
// ganti 1

use App\Models\Master\Fo;
use App\Models\Master\FoDetail;
use App\Models\Master\FoDetail2;
use App\Models\Master\Sup;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use DB;
use Carbon\Carbon;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";
use PHPJasperXML;

// ganti 2
class FoController extends Controller
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
        if ( $request->flagz == 'FO' ) {
            $this->judul = "Formula";
        } 
        $this->FLAGZ = $request->flagz;
        $this->judul = $request->judul;

    }
		
    public function index(Request $request)
    {


	    $this->setFlag($request);
        // ganti 3
        return view('master_fo.index')->with(['judul' => $this->judul, 'flagz' => $this->FLAGZ ]);
	
		
    }
	
	public function browse()
    {
		$fo = DB::table('fo')->select('KD_BRG', 'NA_BRG', 'NOTES')->where('GOL', 'Y')->orderBy('KD_BRG', 'ASC')->get();
		return response()->json($fo);
	}


	public function index_posting(Request $request)
    {
 
        return view('master_fo.post');
    }
	  
	//SHELVI
	
	public function browse_detail(Request $request)
    {
		$filterbukti = '';
		if($request->NO_PO)
		{
	
			$filterbukti = " WHERE NO_BUKTI='".$request->NO_PO."' ";
		}
		$fod = DB::SELECT("SELECT REC, KD_BRG, NA_BRG, SATUAN, QTY,  HARGA, KIRIM, SISA from fod $filterbukti ORDER BY NO_BUKTI ");
	

		return response()->json($fod);
	}
    // ganti 4



    public function getFo(Request $request)
    {
        // ganti 5

       if ($request->session()->has('periode')) {
            $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
        } else {
            $periode = '';
        }

		$this->setFlag($request);	
        $fo = DB::SELECT("SELECT * from fo  WHERE FLAG='$this->FLAGZ'  ORDER BY NO_BUKTI ");
	  
	   
        // ganti 6

        return Datatables::of($fo)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                if (Auth::user()->divisi=="programmer" || Auth::user()->divisi=="owner" || Auth::user()->divisi=="production")
				{
                    //CEK POSTED di index dan edit

                    $btnEdit =   ($row->POSTED == 1) ? ' onclick= "alert(\'Transaksi ' . $row->NO_BUKTI . ' sudah diposting!\')" href="#" ' : ' href="fo/edit/?idx=' . $row->NO_ID . '&tipx=edit&flagz=' . $row->FLAG . '&judul=' . $this->judul . '"';					
                    $btnDelete = ($row->POSTED == 1) ? ' onclick= "alert(\'Transaksi ' . $row->NO_BUKTI . ' sudah diposting!\')" href="#" ' : ' onclick="return confirm(&quot; Apakah anda yakin ingin hapus? &quot;)" href="fo/delete/' . $row->NO_ID . '/?flagz=' . $row->FLAG . '" ';


                    $btnPrivilege =
                        '
                                <a class="dropdown-item" ' . $btnEdit . '>
                                <i class="fas fa-edit"></i>
                                    Edit
                                </a>
                                <a class="dropdown-item btn btn-danger" href="jspo_nonc/' . $row->NO_ID . '">
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
 //               'NO_PO'       => 'required',
                // 'TGL'      => 'required',
                // 'KODES'       => 'required'

            ]
        );

        //////     nomer otomatis
		$this->setFlag($request);
        $FLAGZ = $this->FLAGZ;
        $judul = $this->judul;
		
        $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];

        $bulan    = session()->get('periode')['bulan'];
        $tahun    = substr(session()->get('periode')['tahun'], -2);

        $query = DB::table('fo')->select('NO_BUKTI')->where('FLAG', 'FO')->orderByDesc('NO_BUKTI')->limit(1)->get();
		
		if ($query != '[]')
        {
            $query = substr($query[0]->NO_BUKTI, -4);
            $query = str_pad($query + 1, 4, 0, STR_PAD_LEFT);
            $no_bukti = 'FO'.'-'.$query;
        } else {
            $no_bukti = 'FO'.'-0001';
        }	

        $fo = Fo::create(
            [
                'NO_BUKTI'         => $no_bukti,
				'KD_BRG'           => ($request['KD_BRG']==null) ? "" : $request['KD_BRG'],	
                'NA_BRG'           => ($request['NA_BRG']==null) ? "" : $request['NA_BRG'],	
				'NOTES'            => ($request['NOTES']==null) ? "" : $request['NOTES'],
				'AKTIF'        	   => ($request['AKTIF']==null) ? 0 : $request['AKTIF'],
                'TOTAL_QTY'        => (float) str_replace(',', '', $request['TTOTAL_QTY']),
				'FLAG'             => $FLAGZ,
				'USRNM'            => Auth::user()->username,
				'TG_SMP'           => Carbon::now()
            ]
        );


		$RECX	= $request->input('RECX');
		$RECY	= $request->input('RECY');
		$KD_PRS	= $request->input('KD_PRS');
		$NA_PRS	= $request->input('NA_PRS');
		$KD_BHN	= $request->input('KD_BHN');
		$NA_BHN	= $request->input('NA_BHN');
		$SATUAN	= $request->input('SATUAN');
		$QTY	= $request->input('QTY');
		$KET	= $request->input('KET');
        
        $KD_PRS2 = $request->input('KD_PRS2');
		$NA_PRS2	= $request->input('NA_PRS2');

        // Check jika value detail ada/tidak
        if ($RECX) {
            foreach ($RECX as $key => $value) {
                // Declare new data di Model
                $detail    = new FoDetail;

                // Insert ke Database
                $detail->NO_BUKTI = $no_bukti;			
				$detail->REC	= $RECX[$key];
				$detail->KD_PRS	= ($KD_PRS[$key]==null) ? "" :  $KD_PRS[$key];
				$detail->NA_PRS	= ($NA_PRS[$key]==null) ? "" :  $NA_PRS[$key];
				$detail->KD_BHN	= ($KD_BHN[$key]==null) ? "" :  $KD_BHN[$key];
				$detail->NA_BHN	= ($NA_BHN[$key]==null) ? "" :  $NA_BHN[$key];
				$detail->SATUAN	= ($SATUAN[$key]==null) ? "" :  $SATUAN[$key];
				$detail->QTY	= (float) str_replace(',', '', $QTY[$key]);
				$detail->KET	= ($KET[$key]==null) ? "" :  $KET[$key];;				
                $detail->save();
            }
        }	

        if ($RECY) {
            foreach ($RECY as $key => $value) {
                // Declare new data di Model
                $detail2    = new FoDetail2;

                // Insert ke Database
                $detail2->NO_BUKTI = $no_bukti;			
				$detail2->REC	    = $RECY[$key];
				$detail2->KD_PRS	= ($KD_PRS2[$key]==null) ? "" :  $KD_PRS2[$key];
				$detail2->NA_PRS	= ($NA_PRS2[$key]==null) ? "" :  $NA_PRS2[$key];
                $detail2->save();
            }
        }	
		
		$no_buktix = $no_bukti;
		
		$fo = Fo::where('NO_BUKTI', $no_buktix )->first();


        DB::SELECT("UPDATE fo,  fod
                            SET  fod.ID =  fo.NO_ID  WHERE  fo.NO_BUKTI =  fod.NO_BUKTI 
							AND  fo.NO_BUKTI='$no_buktix';");

        DB::SELECT("UPDATE fo,  fod2
                            SET  fod2.ID =  fo.NO_ID  WHERE  fo.NO_BUKTI =  fod2.NO_BUKTI 
                            AND  fo.NO_BUKTI='$no_buktix';");

		
					 
        return redirect('/fo/edit/?idx=' . $fo->NO_ID . '&tipx=edit&flagz=' . $this->FLAGZ . '&judul=' . $this->judul . '');
		
    }

   public function edit( Request $request , Fo $fo)
    {


		$per = session()->get('periode')['bulan'] . '/' . session()->get('periode')['tahun'];
		
				
        // $cekperid = DB::SELECT("SELECT POSTED from perid WHERE PERIO='$per'");
        // if ($cekperid[0]->POSTED==1)
        // {
        //     return redirect('/fo')
		// 	       ->with('status', 'Maaf Periode sudah ditutup!')
        //            ->with(['judul' => $judul, 'flagz' => $FLAGZ]);
        // }
		
		$this->setFlag($request);
		
        $tipx = $request->tipx;

		$idx = $request->idx;
			

		
		if ( $idx =='0' && $tipx=='undo'  )
	    {
			$tipx ='top';
			
		   }
		   
		 
		   
		if ($tipx=='search') {
			
		   	
    	   $buktix = $request->buktix;
		   
		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from fo
		                 where PER ='$per' and FLAG ='$this->FLAGZ' 
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
			

		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from fo
		                 where PER ='$per' 
						 and FLAG ='$this->FLAGZ'    
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
			
		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from fo     
		             where PER ='$per' 
					 and FLAG ='$this->FLAGZ'  and NO_BUKTI < 
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
	   
		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from fo    
		             where PER ='$per'  
					 and FLAG ='$this->FLAGZ' and NO_BUKTI > 
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
		  
    		$bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from fo
						where PER ='$per'
						and FLAG ='$this->FLAGZ'   
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
			$fo = Fo::where('NO_ID', $idx )->first();	
	     }
		 else
		 {
				$fo = new Fo;
                $fo->TGL = Carbon::now();
				
				
		 }

        $no_bukti = $fo->NO_BUKTI;
        $foDetail = DB::table('fod')->where('NO_BUKTI', $no_bukti)->get();
        $foDetail2 = DB::table('fod2')->where('NO_BUKTI', $no_bukti)->get();
		
		$data = [
            'header'        => $fo,
			'detail'        => $foDetail,
			'detail2'        => $foDetail2

        ];
 
 		$sup = DB::SELECT("SELECT KODES, CONCAT(NAMAS,'-',KOTA) AS NAMAS FROM SUP 
		                 ORDER BY NAMAS ASC" );
		
         
         return view('master_fo.edit', $data)->with(['sup' => $sup])
		 ->with(['tipx' => $tipx, 'idx' => $idx, 'flagz' =>$this->FLAGZ, 'judul', $this->judul ]);
			 

    }

  /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Resbelinse
     */

    // ganti 18

    public function update(Request $request, Fo $fo)
    {

        $this->validate(
            $request,
            [

                // 'TGL'      => 'required',
                // 'KODES'       => 'required'
            ]
        );

		$this->setFlag($request);
        $FLAGZ = $this->FLAGZ;
        $judul = $this->judul;
		
		
        $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];


        $fo->update(
            [
                'KD_BRG'            => ($request['KD_BRG']==null) ? "" : $request['KD_BRG'],
				'NA_BRG'            => ($request['NA_BRG']==null) ? "" : $request['NA_BRG'],
                'NOTES'             => ($request['NOTES']==null) ? "" : $request['NOTES'],
				'AKTIF'        		=> ($request['AKTIF']==null) ? 0 : $request['AKTIF'],				
                'TOTAL_QTY'         => (float) str_replace(',', '', $request['TTOTAL_QTY']),
				'USRNM'             => Auth::user()->username,
				'TG_SMP'            => Carbon::now(),
            ]
        );

		$no_buktix = $fo->NO_BUKTI;
		
        // Update Detail
        $length = sizeof($request->input('REC'));
        $NO_ID  = $request->input('NO_ID');

        $REC    = $request->input('REC');

        $RECX	= $request->input('RECX');
		$RECY	= $request->input('RECY');
		$KD_PRS	= $request->input('KD_PRS');
		$NA_PRS	= $request->input('NA_PRS');
		$KD_BHN	= $request->input('KD_BHN');
		$NA_BHN	= $request->input('NA_BHN');
		$SATUAN	= $request->input('SATUAN');
		$QTY	= $request->input('QTY');
		$KET	= $request->input('KET');
        
        $KD_PRS2 = $request->input('KD_PRS2');
		$NA_PRS2	= $request->input('NA_PRS2');			

        $query = DB::table('fod')->where('NO_BUKTI', $request->NO_BUKTI)->whereNotIn('NO_ID',  $NO_ID)->delete();

        // Update / Insert
        for ($i = 0; $i < $length; $i++) {
            // Insert jika NO_ID baru
            if ($NO_ID[$i] == 'new') {
                $insert = FoDetail::create(
                    [
                        'NO_BUKTI'   => $request->NO_BUKTI,
                        'REC'        => $RECX[$i],
						'KD_PRS'     => ($KD_PRS[$i]==null) ? "" :  $KD_PRS[$i],
                        'NA_PRS'     => ($NA_PRS[$i]==null) ? "" : $NA_PRS[$i],
                        'KD_BHN'     => ($KD_BHN[$i]==null) ? "" :  $KD_BHN[$i],
                        'NA_BHN'     => ($NA_BHN[$i]==null) ? "" : $NA_BHN[$i],
						'SATUAN'     => ($SATUAN[$i]==null) ? "" : $SATUAN[$i],						
						'QTY'        => (float) str_replace(',', '', $QTY[$i]),
						'KET'     	 => ($KET[$i]==null) ? "" : $KET[$i]	
						
                    ]
                );
            } else {
                // Update jika NO_ID sudah ada
                $upsert = FoDetail::updateOrCreate(
                    [
                        'NO_BUKTI'  => $request->NO_BUKTI,
                        'NO_ID'     => (int) str_replace(',', '', $NO_ID[$i])
                    ],

                    [
                        'REC'        => $RECX[$i],

                        'KD_PRS'     => ($KD_PRS[$i]==null) ? "" :  $KD_PRS[$i],
                        'NA_PRS'     => ($NA_PRS[$i]==null) ? "" : $NA_PRS[$i],
                        'KD_BHN'     => ($KD_BHN[$i]==null) ? "" :  $KD_BHN[$i],
                        'NA_BHN'     => ($NA_BHN[$i]==null) ? "" : $NA_BHN[$i],
						'SATUAN'     => ($SATUAN[$i]==null) ? "" : $SATUAN[$i],						
						'QTY'        => (float) str_replace(',', '', $QTY[$i]),
						'KET'     	 => ($KET[$i]==null) ? "" : $KET[$i]							
                    ]
                );
            }
        }

        ////////////////////////////////////////////////////

        $query2 = DB::table('fod2')->where('NO_BUKTI', $request->NO_BUKTI)->whereNotIn('NO_ID',  $NO_ID)->delete();

        // Update / Insert
        for ($i = 0; $i < $length; $i++) {
            // Insert jika NO_ID baru
            if ($NO_ID[$i] == 'new') {
                $insert = FoDetail2::create(
                    [
                        'NO_BUKTI'   => $request->NO_BUKTI,
                        'REC'        => $RECY[$i],
						'KD_PRS'     => ($KD_PRS2[$i]==null) ? "" :  $KD_PRS2[$i],
                        'NA_PRS'     => ($NA_PRS2[$i]==null) ? "" : $NA_PRS2[$i],
						
                    ]
                );
            } else {
                // Update jika NO_ID sudah ada
                $upsert = FoDetail2::updateOrCreate(
                    [
                        'NO_BUKTI'  => $request->NO_BUKTI,
                        'NO_ID'     => (int) str_replace(',', '', $NO_ID[$i])
                    ],

                    [
                        'REC'        => $REC2[$i],

                        'KD_PRS'     => ($KD_PRS2[$i]==null) ? "" :  $KD_PRS2[$i],
                        'NA_PRS'     => ($NA_PRS2[$i]==null) ? "" : $NA_PRS2[$i],							
                    ]
                );
            }
        } 

        /////////////////////////////////////////////////////////////

 		$fo = Fo::where('NO_BUKTI', $no_buktix )->first();
					 
        return redirect('/fo/edit/?idx=' . $fo->NO_ID . '&tipx=edit&flagz=' . $this->FLAGZ . '&judul=' . $this->judul . '');	
		
	   
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Resbelinse
     */

    // ganti 22

    public function destroy(Request $request, Fo $fo)
    {

		$this->setFlag($request);
        $FLAGZ = $this->FLAGZ;
        $judul = $this->judul;
		
		$per = session()->get('periode')['bulan'] . '/' . session()->get('periode')['tahun'];
        $cekperid = DB::SELECT("SELECT POSTED from perid WHERE PERIO='$per'");
        if ($cekperid[0]->POSTED==1)
        {
            return redirect()->route('fo')
                ->with('status', 'Maaf Periode sudah ditutup!')
                ->with(['judul' => $this->judul, 'flagz' => $this->FLAGZ]);
        }
		
        $deleteFo = Fo::find($fo->NO_ID);

        $deleteFo->delete();

       return redirect('/fo?flagz='.$FLAGZ)->with(['judul' => $judul, 'flagz' => $FLAGZ ])->with('statusHapus', 'Data '.$fo->NO_BUKTI.' berhasil dihapus');


    }
    
    public function jspoc(Fo $fo)
    {
       
    }
	
	
	
	 public function posting(Request $request)
    {
      

    }
	
	
	
	
	
	
	
}
