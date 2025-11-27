<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
// ganti 1

use App\Models\OTransaksi\Muat;
use App\Models\OTransaksi\MuatDetail;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use DB;
use Carbon\Carbon;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";
use PHPJasperXML;

// ganti 2
class MuatController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
	 
    var $judul = '';
    var $FLAGZ = '';
    var $GOLZ = '';
	
    function setFlag(Request $request)
    {
        if ( $request->flagz == 'MT' && $request->golz == 'B' ) {
            $this->judul = "Muat Bahan Baku";
        } else if ( $request->flagz == 'MT' && $request->golz == 'J' ) {
            $this->judul = "Muat Barang";
        } 
		
        $this->FLAGZ = $request->flagz;
        $this->GOLZ = $request->golz;


    }
		
    public function index(Request $request)
    {


	    $this->setFlag($request);

        // ganti 3
        return view('otransaksi_muat.index')->with(['judul' => $this->judul, 'flagz' => $this->FLAGZ , 'golz' => $this->GOLZ]);
	
		
    }
	

	public function index_posting(Request $request)
    {
 
        return view('otransaksi_muat.post');
    }

    public function browse(Request $request)
    {
        $golz = $request->GOL;

		$CBG = Auth::user()->CBG;

        $muat = DB::SELECT("SELECT muat.NO_BUKTI , muat.TGL, muat.NO_BELI, muat.NO_PO, muat.KD_BRG, 
		                  muat.NA_BRG, muatd.QTY AS QTY_MUAT, muat.QTY_BELI as QTY_BELI, muatd.NO_CONT,
                          muatd.SOPIR, muatd.NOPOL, muatd.SEAL
                          from muat, muatd 
                          WHERE muat.NO_BUKTI = muatd.NO_BUKTI AND muat.FLAG='MT' AND muatd.no_terima = ''  
                          AND muat.GOL ='$golz'
                          AND muat.CBG = '$CBG' order by muat.TGL");
        return response()->json($muat);
    }
	
	
    public function browseuang(Request $request)
    {
        //	$muat = DB::table('muat')->select('NO_BUKTI', 'TGL', 'KODES','NAMAS', 'ALAMAT','KOTA', 'PERB','PERBB', 'SISA' )->where('PERB', '<>' ,'PERBB')->where('LNS', '<>',1)->where('GOL', 'Y')->orderBy('KODES', 'ASC')->get();
        $filterkodes = '';
	   
		$CBG = Auth::user()->CBG;

		if($request->KODES)
		{
	
			// $filterkodes = " WHERE SISA <> 0 AND KODES='".$request->KODES."' ";
			$filterkodes = " WHERE KODES='".$request->KODES."' ";
		}
		
		$muat = DB::SELECT("SELECT NO_BUKTI, TGL, KODES, 
		            NAMAS, TOTAL, BAYAR, SISA from muat
		            $filterkodes 
                    AND muat.CBG = '$CBG'
                    ORDER BY NO_BUKTI ");
 
        return response()->json($muat);
    }
	//SHELVI
	
	
    // ganti 4



    public function getMuat(Request $request)
    {
        // ganti 5

       if ($request->session()->has('periode')) {
            $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
        } else {
            $periode = '';
        }

		$this->setFlag($request);	
        
		$CBG = Auth::user()->CBG;

        $muat = DB::SELECT("SELECT * from muat  WHERE PER='$periode' and FLAG = '$this->FLAGZ' 
                and GOL = '$this->GOLZ' AND CBG='$CBG' ORDER BY NO_BUKTI ");
	  
	   
        // ganti 6

        return Datatables::of($muat)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                if (Auth::user()->divisi=="programmer" ) 
				{
                    //CEK POSTED di index dan edit

                    $btnEdit =   ($row->POSTED == 1) ? ' onclick= "alert(\'Transaksi ' . $row->NO_BUKTI . ' sudah diposting!\')" href="#" ' : ' href="muat/edit/?idx=' . $row->NO_ID . '&tipx=edit&flagz=' . $row->FLAG . '&judul=' . $this->judul . '&golz=' . $row->GOL . '"';					
                    $btnDelete = ($row->POSTED == 1) ? ' onclick= "alert(\'Transaksi ' . $row->NO_BUKTI . ' sudah diposting!\')" href="#" ' : ' onclick="return confirm(&quot; Apakah anda yakin ingin hapus? &quot;)" href="muat/delete/' . $row->NO_ID . '/?flagz=' . $row->FLAG . '&golz=' . $row->GOL .'" ';


                    $btnPrivilege =
                        '
                                <a class="dropdown-item" ' . $btnEdit . '>
                                <i class="fas fa-edit"></i>
                                    Edit
                                </a>
                                <a class="dropdown-item btn btn-danger" href="muat/cetak/' . $row->NO_ID . '">
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


			
			
			
			
///            ->rawColumns(['action'])
 //           ->make(true);
//    }



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
 //               'NO_PO'       => 'required',
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

        $query = DB::table('muat')->select('NO_BUKTI')->where('PER', $periode)->where('FLAG', $FLAGZ )->where('GOL', $GOLZ )->where('CBG', $CBG)->orderByDesc('NO_BUKTI')->limit(1)->get();

        if($GOLZ=='J') {

            if ($query != '[]') {
                $query = substr($query[0]->NO_BUKTI, -4);
                $query = str_pad($query + 1, 4, 0, STR_PAD_LEFT);
                $no_bukti = $this->FLAGZ . $CBG .  $tahun . $bulan . '-' . $query;
            } else {
                $no_bukti = $this->FLAGZ . $CBG .  $tahun . $bulan . '-0001';
            }

        } elseif($GOLZ=='N') {

            if ($query != '[]') {
                $query = substr($query[0]->NO_BUKTI, -4);
                $query = str_pad($query + 1, 4, 0, STR_PAD_LEFT);
                $no_bukti = $this->FLAGZ . $this->GOLZ . $CBG . $tahun . $bulan . '-' . $query;
            } else {
                $no_bukti = $this->FLAGZ . $this->GOLZ . $CBG . $tahun . $bulan . '-0001';
            }

        }
        

		
//////////////////////////////////////////////////////////////////////////
       

        // Insert Header

        // ganti 10

        $muat = Muat::create(
            [
                'NO_BUKTI'          => $no_bukti,
                'TGL'               => date('Y-m-d', strtotime($request['TGL'])),
                'PER'               => $periode,
				'NO_PO'             => ($request['NO_PO'] == null) ? "" : $request['NO_PO'],
				'NO_BELI'           => ($request['NO_BELI'] == null) ? "" : $request['NO_BELI'],
                'KD_BRG'            => ($request['KD_BRG'] == null) ? "" : $request['KD_BRG'],
                'NA_BRG'            => ($request['NA_BRG'] == null) ? "" : $request['NA_BRG'],
                'FLAG'              => $FLAGZ,					
                'GOL'               => $GOLZ,		
                'TOTAL_QTY'         => (float) str_replace(',', '', $request['TOTAL_QTY']),
                'QTY_BELI'          => (float) str_replace(',', '', $request['QTY_BELI']),
                'USRNM'             => Auth::user()->username,
                'TG_SMP'            => Carbon::now(),
				'created_by'        => Auth::user()->username,
                'CBG'               => $CBG,
            ]
        );


		$REC        = $request->input('REC');
		$NO_CONT    = $request->input('NO_CONT');
        $SOPIR      = $request->input('SOPIR');
		$NOPOL      = $request->input('NOPOL');
        $TELP       = $request->input('TELP');
        $SEAL       = $request->input('SEAL');
        $T_CONT     = $request->input('T_CONT');
        $QTY        = $request->input('QTY');

        // Check jika value detail ada/tidak
        if ($REC) {
            foreach ($REC as $key => $value) {
                // Declare new data di Model
                $detail    = new MuatDetail;

                // Insert ke Database
                $detail->NO_BUKTI    = $no_bukti;
                $detail->REC         = $REC[$key];
                $detail->PER         = $periode;
                $detail->FLAG        = $FLAGZ;		
                $detail->GOL         = $GOLZ;		
               
                $detail->NO_CONT     = ($NO_CONT[$key] == null) ? "" :  $NO_CONT[$key];
                $detail->SOPIR       = ($SOPIR[$key] == null) ? "" :  $SOPIR[$key];
                $detail->NOPOL       = ($NOPOL[$key] == null) ? "" :  $NOPOL[$key];
                $detail->TELP        = ($TELP[$key] == null) ? "" :  $TELP[$key];
                $detail->SEAL        = ($SEAL[$key] == null) ? "" :  $SEAL[$key];				
                $detail->T_CONT      = (float) str_replace(',', '', $T_CONT[$key]);
                $detail->QTY         = (float) str_replace(',', '', $QTY[$key]);				
                $detail->save();
            }
        }	
		
		


        //  ganti 11
        $variablell = DB::select('call muatins(?)', array($no_bukti));
		$no_buktix = $no_bukti;
		
		$muat = Muat::where('NO_BUKTI', $no_buktix )->first();


        DB::SELECT("UPDATE muat,  muatd
                            SET  muatd.ID = muat.NO_ID  WHERE  muat.NO_BUKTI =  muatd.NO_BUKTI 
							AND  muat.NO_BUKTI='$no_buktix';");

		
					 
        return redirect('/muat/edit/?idx=' . $muat->NO_ID . '&tipx=edit&flagz=' . $FLAGZ . '&judul=' . $this->judul . '&golz=' . $this->GOLZ . '');

					
    }


    // ganti 15

   
   public function edit( Request $request , Muat $muat)
    {


		$per = session()->get('periode')['bulan'] . '/' . session()->get('periode')['tahun'];
		
				
        // $cekperid = DB::SELECT("SELECT POSTED from perid WHERE PERIO='$per'");
        // if ($cekperid[0]->POSTED==1)
        // {
        //     return redirect('/muat')
		// 	       ->with('status', 'Maaf Periode sudah ditutup!')
        //            ->with(['judul' => $judul, 'flagz' => $FLAGZ, 'golz' => $GOLZ]);
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
		   
		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from muat
		                 where PER ='$per' and FLAG ='$this->FLAGZ' and GOL ='$this->GOLZ' 
						 and NO_BUKTI = '$buktix'						 
		                 and CBG = '$CBG' ORDER BY NO_BUKTI ASC  LIMIT 1" );
						 
			
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
			

		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from muat 
		                 where PER ='$per' 
						 and FLAG ='$this->FLAGZ' and GOL ='$this->GOLZ'    
		                 and CBG = '$CBG' ORDER BY NO_BUKTI ASC  LIMIT 1" );
						 
		
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
			
		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from muat     
		             where PER ='$per' 
					 and FLAG ='$this->FLAGZ' and GOL ='$this->GOLZ'  and NO_BUKTI < 
					 '$buktix' and CBG = '$CBG' ORDER BY NO_BUKTI DESC LIMIT 1" );
			

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
	   
		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from muat    
		             where PER ='$per'  
					 and FLAG ='$this->FLAGZ' and GOL ='$this->GOLZ' and NO_BUKTI > 
					 '$buktix' and CBG = '$CBG' ORDER BY NO_BUKTI ASC LIMIT 1" );
					 
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
		  
    		$bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from muat
						where PER ='$per'
						and FLAG ='$this->FLAGZ' and GOL ='$this->GOLZ'   
		                and CBG = '$CBG' ORDER BY NO_BUKTI DESC  LIMIT 1" );
					 
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
			$muat = Muat::where('NO_ID', $idx )->first();	
	     }
		 else
		 {
				$muat = new Muat;
                $muat->TGL = Carbon::now();
				
				
		 }

        $no_bukti = $muat->NO_BUKTI;
        $muatDetail = DB::table('muatd')->where('NO_BUKTI', $no_bukti)->orderBy('REC')->get();
		
		$data = [
            'header'        => $muat,
			'detail'        => $muatDetail

        ];
 
         
         return view('otransaksi_muat.edit', $data)
		 ->with(['tipx' => $tipx, 'idx' => $idx, 'flagz' =>$this->FLAGZ, 'judul' => $this->judul, 'golz' =>$this->GOLZ ]);
      
    }



    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    // ganti 18

    public function update(Request $request, Muat $muat)
    {

        $this->validate(
            $request,
            [

                // ganti 19

 //               'NO_PO'       => 'required',
                'TGL'      => 'required'


            ]
        );

        // ganti 20
        $variablell = DB::select('call muatdel(?)', array($muat['NO_BUKTI']));

		$this->setFlag($request);
        $FLAGZ = $this->FLAGZ;
        $GOLZ = $this->GOLZ;
        $judul = $this->judul;
		
        $CBG = Auth::user()->CBG;
		
        $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];

        // ganti 20

        $muat->update(
            [
                'TGL'               => date('Y-m-d', strtotime($request['TGL'])),
				'NO_PO'             => ($request['NO_PO'] == null) ? "" : $request['NO_PO'],
				'NO_BELI'           => ($request['NO_BELI'] == null) ? "" : $request['NO_BELI'],
                'KD_BRG'            => ($request['KD_BRG'] == null) ? "" : $request['KD_BRG'],
                'NA_BRG'            => ($request['NA_BRG'] == null) ? "" : $request['NA_BRG'],
                'FLAG'              => $FLAGZ,					
                'GOL'               => $GOLZ,		
                'TOTAL_QTY'         => (float) str_replace(',', '', $request['TOTAL_QTY']),
                'QTY_BELI'          => (float) str_replace(',', '', $request['QTY_BELI']),
				'USRNM'             => Auth::user()->username,
                'TG_SMP'            => Carbon::now(),
				'updated_by'        => Auth::user()->username,
                'CBG'               => $CBG,
            ]
        );

		$no_buktix = $muat->NO_BUKTI;
		
        // Update Detail
        $length = sizeof($request->input('REC'));
        $NO_ID  = $request->input('NO_ID');

        $REC    = $request->input('REC');

        $NO_CONT    = $request->input('NO_CONT');
        $SOPIR      = $request->input('SOPIR');
		$NOPOL      = $request->input('NOPOL');
        $TELP       = $request->input('TELP');
        $SEAL       = $request->input('SEAL');
        $T_CONT     = $request->input('T_CONT');
        $QTY        = $request->input('QTY');		

        $query = DB::table('muatd')->where('NO_BUKTI', $request->NO_BUKTI)->whereNotIn('NO_ID',  $NO_ID)->delete();

        // Update / Insert
        for ($i = 0; $i < $length; $i++) {
            // Insert jika NO_ID baru
            if ($NO_ID[$i] == 'new') {
                $insert = MuatDetail::create(
                    [
                        'NO_BUKTI'   => $request->NO_BUKTI,
                        'REC'        => $REC[$i],
                        'PER'        => $periode,
                        'FLAG'       => $this->FLAGZ,
                        'GOL'        => $this->GOLZ,
                        'NO_CONT'    => ($NO_CONT[$i] == null) ? "" :  $NO_CONT[$i],
                        'SOPIR'      => ($SOPIR[$i] == null) ? "" :  $SOPIR[$i],
                        'NOPOL'      => ($NOPOL[$i] == null) ? "" :  $NOPOL[$i],
                        'TELP'       => ($TELP[$i] == null) ? "" :  $TELP[$i],
                        'SEAL'       => ($SEAL[$i] == null) ? "" :  $SEAL[$i],						
                        'T_CONT'     => (float) str_replace(',', '', $T_CONT[$i]),
                        'QTY'        => (float) str_replace(',', '', $QTY[$i]),
						
                    ]
                );
            } else {
                // Update jika NO_ID sudah ada
                $upsert = MuatDetail::updateOrCreate(
                    [
                        'NO_BUKTI'  => $request->NO_BUKTI,
                        'NO_ID'     => (int) str_replace(',', '', $NO_ID[$i])
                    ],

                    [
                        'REC'        => $REC[$i],

                        
                        'NO_CONT'    => ($NO_CONT[$i] == null) ? "" :  $NO_CONT[$i],
                        'SOPIR'      => ($SOPIR[$i] == null) ? "" :  $SOPIR[$i],
                        'NOPOL'      => ($NOPOL[$i] == null) ? "" :  $NOPOL[$i],
                        'TELP'       => ($TELP[$i] == null) ? "" :  $TELP[$i],
                        'SEAL'       => ($SEAL[$i] == null) ? "" :  $SEAL[$i],						
                        'T_CONT'     => (float) str_replace(',', '', $T_CONT[$i]),
                        'QTY'        => (float) str_replace(',', '', $QTY[$i]),
                        'FLAG'       => $this->FLAGZ,
                        'GOL'        => $this->GOLZ,						
                    ]
                );
            }
        }


        //  ganti 21
        $variablell = DB::select('call muatins(?)', array($muat['NO_BUKTI']));

 		$muat = Muat::where('NO_BUKTI', $no_buktix )->first();

        $no_bukti = $muat->NO_BUKTI;

        DB::SELECT("UPDATE muat,  muatd
                    SET  muatd.ID =  muat.NO_ID  WHERE  muat.NO_BUKTI =  muatd.NO_BUKTI 
                    AND  muat.NO_BUKTI='$no_bukti';");
					 
        return redirect('/muat/edit/?idx=' . $muat->NO_ID . '&tipx=edit&flagz=' . $this->FLAGZ . '&judul=' . $this->judul .  '&golz=' . $this->GOLZ . '');	
		
	   
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    // ganti 22

    public function destroy(Request $request, Muat $muat)
    {

		$this->setFlag($request);
        $FLAGZ = $this->FLAGZ;
        $GOLZ = $this->GOLZ;
        $judul = $this->judul;
		
		$per = session()->get('periode')['bulan'] . '/' . session()->get('periode')['tahun'];
        $cekperid = DB::SELECT("SELECT POSTED from perid WHERE PERIO='$per'");
        if ($cekperid[0]->POSTED==1)
        {
            return redirect()->route('muat')
                ->with('status', 'Maaf Periode sudah ditutup!')
                ->with(['judul' => $this->judul, 'flagz' => $this->FLAGZ, 'golz' => $this->GOLZ]);
        }
		
		
        $variablell = DB::select('call muatdel(?)', array($muat['NO_BUKTI']));//


        // ganti 23
		
        $deleteMuat = Muat::find($muat->NO_ID);

        // ganti 24

        $deleteMuat->delete();

        // ganti 

       return redirect('/muat?flagz='.$FLAGZ.'&golz='.$GOLZ)->with(['judul' => $judul, 'flagz' => $FLAGZ, 'golz' => $GOLZ ])->with('statusHapus', 'Data '.$muat->NO_BUKTI.' berhasil dihapus');


    }
    
    
    public function cetak(Muat $muat)
    {
        $no_muat = $muat->NO_BUKTI;

        $file     = 'muatc';
        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

        $query = DB::SELECT("SELECT muat.NO_BUKTI, muat.TGL, muat.NO_BELI, muat.NO_PO, muat.KD_BRG, muat.NA_BRG, 
                                    muat.QTY_BELI,
                                    muatd.NO_CONT, muatd.SOPIR, muatd.NOPOL, muatd.TELP, muatd.SEAL, 
                                    muatd.T_CONT, muatd.QTY
                            FROM muat, muatd 
                            WHERE muat.NO_BUKTI = muatd.NO_BUKTI AND muat.NO_BUKTI='$no_muat' 
                            ;
		");

        
        $data = [];

        foreach ($query as $key => $value) {
            array_push($data, array(
                'NO_BUKTI' => $query[$key]->NO_BUKTI,
                'TGL'      => $query[$key]->TGL,
                'NO_BELI'  => $query[$key]->NO_BELI,
                'NO_PO'    => $query[$key]->NO_PO,
                'KD_BRG'   => $query[$key]->KD_BRG,
                'NA_BRG'   => $query[$key]->NA_BRG,
                'QTY_BELI' => $query[$key]->QTY_BELI,
                'NO_CONT'  => $query[$key]->NO_CONT,
                'SOPIR'    => $query[$key]->SOPIR,
                'NOPOL'    => $query[$key]->NOPOL,
                'TELP'     => $query[$key]->TELP,
                'SEAL'     => $query[$key]->SEAL,
                'T_CONT'   => $query[$key]->T_CONT,
                'QTY'      => $query[$key]->QTY,
            ));
        }
		
        DB::SELECT("UPDATE MUAT SET POSTED=1 WHERE NO_BUKTI='$no_muat'");

        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I");
       
    }
	
	 public function posting(Request $request)
    {
      

    }
	
	
	
	
	
	
	
}
