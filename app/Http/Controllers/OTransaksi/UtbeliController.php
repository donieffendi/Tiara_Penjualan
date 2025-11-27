<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;

use App\Models\OTransaksi\Beli;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use DB;
use Carbon\Carbon;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;

class UtbeliController extends Controller
{

    var $judul = '';
    var $FLAGZ = '';
    var $GOLZ = '';


    function setFlag(Request $request)
    {
		
        if ( $request->flagz == 'TH') {
            $this->judul = "Transaksi Hutang";
        } else if ( $request->flagz == 'UM' ) {
            $this->judul = "Uang Muka Pembelian ";
        }
		
		
        $this->FLAGZ = $request->flagz;
        $this->GOLZ = $request->golz;    
		

    }	 


    public function index(Request $request)
    {

        // ganti 3
        $this->setFlag($request);
        // ganti 3
        return view('otransaksi_utbeli.index')->with(['judul' => $this->judul, 'golz' => $this->GOLZ , 'flagz' => $this->FLAGZ ]);
    }




    public function browse(Request $request)
    {
        //$utbeli = DB::table('utbeli')->select('NO_BUKTI', 'TGL', 'KODES','NAMAS', 'ALAMAT','KOTA', 'TOTAL','BAYAR','SISA')->where('KODES', $request['KODES'] )->where('SISA', '<>', 0 )->where('GOL', 'Y')->orderBy('KODES', 'ASC')->get();

        $CBG = Auth::user()->CBG;
		
        $utbeli = DB::SELECT("SELECT NO_BUKTI,TGL, NO_PO, KODES, NAMAS, ALAMAT, KOTA,KD_BHN, NA_BHN, KG, HARGA, ( JCONT- SCONT ) AS KIRIM, SCONT AS SISA, NOTES, RPRATE, EMKL, BL, AJU 
                            from utbeli
							WHERE  SCONT > 0 and GOL='$request->GOL' and YEAR(BELI.TGL) >= 2024 
                            AND CBG = '$CBG'
                            ORDER BY KODES; ");

        return response()->json($utbeli);
    }

    public function browseuang(Request $request)
    {

		$no_pox = $request->NO_PO;
		$golx = $request->GOL;
		
        $CBG = Auth::user()->CBG;
		
        $utbeli = DB::SELECT("SELECT NO_BUKTI,TGL, NO_PO, KODES, NAMAS, RPTOTAL AS TOTAL, RPBAYAR AS BAYAR, RPSISA AS SISA
                            from utbeli
                            WHERE NO_PO='$no_pox' AND RPSISA<>0 and GOL='$golx' AND CBG = '$CBG'
                            ORDER BY NO_BUKTI; ");
        
        return response()->json($utbeli);
    }

    public function getUtbeli(Request $request)
    {
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
		
        $utbeli = DB::SELECT("SELECT * from beli  where  PER ='$periode' and FLAG ='$this->FLAGZ' 
                AND GOL ='$this->GOLZ' AND CBG = '$CBG' ORDER BY NO_BUKTI ");
		    
         return Datatables::of($utbeli)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                if (Auth::user()->divisi=="programmer" || Auth::user()->divisi=="owner" || Auth::user()->divisi=="assistant") 
                {

                    $btnEdit =   ($row->POSTED == 1) ? ' onclick= "alert(\'Transaksi ' . $row->NO_BUKTI . ' sudah diposting!\')" href="#" ' : ' href="utbeli/edit/?idx
					=' . $row->NO_ID . '&tipx=edit&flagz=' . $row->FLAG . '&golz=' . $row->GOL . '&judul=' . $this->judul . '"';
					
                    $btnDelete = ($row->POSTED == 1) ? ' onclick= "alert(\'Transaksi ' . $row->NO_BUKTI . ' sudah diposting!\')" href="#" ' : ' onclick="return confirm(&quot; Apakah anda yakin ingin hapus? &quot;)"  href="utbeli/delete/' . $row->NO_ID . '/?flagz=' . $row->FLAG . '&golz=' . $row->GOL . '" ';


                    $btnPrivilege =
                        '
                                <a class="dropdown-item" ' . $btnEdit . '>
                                <i class="fas fa-edit"></i>
                                    Edit
                                </a>
                                <a class="dropdown-item btn btn-danger" href="jsutbelic/' . $row->NO_ID . '">
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



    public function store(Request $request)
    {

        $this->validate(
            $request,
            [
           //     'NO_PO'       => 'required',
                'TGL'      => 'required',
            ]
        );


		
		$this->setFlag($request);
        $FLAGZ = $this->FLAGZ;
        $GOLZ = $this->GOLZ;
        $judul = $this->judul;	
		
        $CBG = Auth::user()->CBG;
		
        // Generate Nomor Bukti
        $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];


        $bulan    = session()->get('periode')['bulan'];
        $tahun    = substr(session()->get('periode')['tahun'], -2);

        $no_bukti ='';
        $no_bukti2 ='';
		
		if ( $request->flagz == 'BL'  ) {

            $query = DB::table('beli')->select(DB::raw("TRIM(NO_BUKTI) AS NO_BUKTI"))->where('PER', $periode)
			         ->where('FLAG', 'BL')->where('GOL', 'Y')->where('CBG', $CBG)->orderByDesc('NO_BUKTI')->limit(1)->get();
			
			if ($query != '[]') {
            
				$query = substr($query[0]->NO_BUKTI, -4);
				$query = str_pad($query + 1, 4, 0, STR_PAD_LEFT);
				$no_bukti = 'BY' . $CBG . $tahun . $bulan . '-' . $query;
			
			} else {
				$no_bukti = 'BY' . $CBG . $tahun . $bulan . '-0001';
				}
		
        } else if ( $request->flagz == 'BL' && $request->golz == 'Z' ) {

            $query = DB::table('beli')->select(DB::raw("TRIM(NO_BUKTI) AS NO_BUKTI"))->where('PER', $periode)
			         ->where('FLAG', 'BL')->where('GOL', 'Z')->orderByDesc('NO_BUKTI')->limit(1)->get();
			
			if ($query != '[]') {
            
				$query = substr($query[0]->NO_BUKTI, -4);
				$query = str_pad($query + 1, 4, 0, STR_PAD_LEFT);
				$no_bukti = 'BZ' . $tahun . $bulan . '-' . $query;
			
			} else {
				$no_bukti = 'BZ' . $tahun . $bulan . '-0001';
				}


        } else if ( $request->flagz == 'TH'  ) {

            $query = DB::table('beli')->select(DB::raw("TRIM(NO_BUKTI) AS NO_BUKTI"))->where('PER', $periode)
			         ->where('FLAG', 'TH')->where('CBG', $CBG)->orderByDesc('NO_BUKTI')->limit(1)->get();
			
			if ($query != '[]') {
            
				$query = substr($query[0]->NO_BUKTI, -4);
				$query = str_pad($query + 1, 4, 0, STR_PAD_LEFT);
				$no_bukti = 'AY' . $CBG . $tahun . $bulan . '-' . $query;
			
			} else {
				$no_bukti = 'AY' . $CBG . $tahun . $bulan . '-0001';
				}
				
        } else if ( $request->flagz == 'TH' && $request->golz == 'Z' ) {

            $query = DB::table('beli')->select(DB::raw("TRIM(NO_BUKTI) AS NO_BUKTI"))->where('PER', $periode)
			         ->where('FLAG', 'TH')->where('GOL', 'Z')->orderByDesc('NO_BUKTI')->limit(1)->get();
			
			if ($query != '[]') {
            
				$query = substr($query[0]->NO_BUKTI, -4);
				$query = str_pad($query + 1, 4, 0, STR_PAD_LEFT);
				$no_bukti = 'AZ' . $tahun . $bulan . '-' . $query;
			
			} else {
				$no_bukti = 'AZ' . $tahun . $bulan . '-0001';
				}

        } else if ( $request->flagz == 'UM') {
 
            $query = DB::table('beli')->select(DB::raw("TRIM(NO_BUKTI) AS NO_BUKTI"))->where('PER', $periode)
			         ->where('FLAG', 'UM')->where('CBG', $CBG)->orderByDesc('NO_BUKTI')->limit(1)->get();
			
			if ($query != '[]') {
            
				$query = substr($query[0]->NO_BUKTI, -4);
				$query = str_pad($query + 1, 4, 0, STR_PAD_LEFT);
				$no_bukti = 'UM' . $CBG . $tahun . $bulan . '-' . $query;
			
			} else {
				$no_bukti = 'UM' . $CBG . $tahun . $bulan . '-0001';
				}
				
			$bulan    = session()->get('periode')['bulan'];
            $tahun    = substr(session()->get('periode')['tahun'], -2);
            $query2 = DB::table('bank')->select('NO_BUKTI')->where('PER', $periode)->where('TYPE', 'BBK')->where('CBG', $CBG)
                    ->orderByDesc('NO_BUKTI')->limit(1)->get();

            if ($query2 != '[]') {
                $query2 = substr($query2[0]->NO_BUKTI, -4);
                $query2 = str_pad($query2 + 1, 4, 0, STR_PAD_LEFT);
                $no_bukti2 = 'BBK' . $CBG . $tahun . $bulan . '-' . $query2;
            } else {
                $no_bukti2 = 'BBK' . $CBG . $tahun . $bulan . '-0001';
            }
			
				
 
        } else if ( $request->flagz == 'UM' && $request->golz == 'Z' ) {

            $query = DB::table('beli')->select(DB::raw("TRIM(NO_BUKTI) AS NO_BUKTI"))->where('PER', $periode)
			         ->where('FLAG', 'UM')->where('GOL', 'Y')->orderByDesc('NO_BUKTI')->limit(1)->get();
			
			if ($query != '[]') {
            
				$query = substr($query[0]->NO_BUKTI, -4);
				$query = str_pad($query + 1, 4, 0, STR_PAD_LEFT);
				$no_bukti = 'UN' . $tahun . $bulan . '-' . $query;
			
			} else {
				$no_bukti = 'UN' . $tahun . $bulan . '-0001';
				}


			$bulan    = session()->get('periode')['bulan'];
            $tahun    = substr(session()->get('periode')['tahun'], -2);
            $query2 = DB::table('bank')->select('NO_BUKTI')->where('PER', $periode)->where('TYPE', 'BBK')->where('CBG', $CBG)
                    ->orderByDesc('NO_BUKTI')->limit(1)->get();

            if ($query2 != '[]') {
                $query2 = substr($query2[0]->NO_BUKTI, -4);
                $query2 = str_pad($query2 + 1, 4, 0, STR_PAD_LEFT);
                $no_bukti2 = 'BBK' . $CBG . $tahun . $bulan . '-' . $query2;
            } else {
                $no_bukti2 = 'BBK' . $CBG . $tahun . $bulan . '-0001';
            }
			
			
        }
        
           $ACNOB ='';
           $NACNOB ='';
           
           if ( $FLAGZ =='Y' )
           {
              $ACNOB =  '211101';
              $NACNOB = 'HUTANG DAGANG';
               
           }

           if ( $GOLZ =='Z' )
           {
               
              $ACNOB =  '211103';
              $NACNOB = 'HUTANG NON';
              
           }
           

        // Insert Header
        $utbeli = Beli::create(
            [
                'NO_BUKTI'         => $no_bukti,
                'TGL'              => date('Y-m-d', strtotime($request['TGL'])),
                'PER'              => $periode,
                'NO_PO'            => ($request['NO_PO'] == null) ? "" : $request['NO_PO'],
                'KODES'            => ($request['KODES'] == null) ? "" : $request['KODES'],
                'NAMAS'            => ($request['NAMAS'] == null) ? "" : $request['NAMAS'],
                'ALAMAT'           => ($request['ALAMAT'] == null) ? "" : $request['ALAMAT'],
                'KOTA'             => ($request['KOTA'] == null) ? "" : $request['KOTA'],
                'FLAG'             =>  $FLAGZ,
                'NOTES'            => ($request['NOTES'] == null) ? "" : $request['NOTES'],
                'NETT'          => ($FLAGZ == 'UM') ? (float) str_replace(',', '', $request['TOTAL'] ) * -1  : (float) str_replace(',', '', $request['RPTOTAL'] ),      
				'SISA'           => ($FLAGZ == 'UM') ? (float) str_replace(',', '', $request['TOTAL'] ) * -1  : (float) str_replace(',', '', $request['RPTOTAL'] ),      
                'ACNOA'            => ($request['ACNOA'] == null) ? "" : $request['ACNOA'],
                'NACNOA'           => ($request['NACNOA'] == null) ? "" : $request['NACNOA'],
                'ACNOB'            => $ACNOB,
                'NACNOB'           => $NACNOB,
                'BACNO'            => ($request['BACNO'] == null) ? "" : $request['BACNO'],
                'BNAMA'            => ($request['BNAMA'] == null) ? "" : $request['BNAMA'],
                'TYPE'             => ($request['TYPE'] == null) ? "" : $request['TYPE'],
                'NO_BANK'          => $no_bukti2,				
                'USRNM'            => Auth::user()->username,
                'created_by'       => Auth::user()->username,
                'CBG'              => $CBG,
                'TG_SMP'           => Carbon::now()
            ]
        );


		if ( $FLAGZ == 'UM' ) {
			 $variablell = DB::select('call umins(?,?)', array($no_bukti, $no_bukti2));

        } else if ( $FLAGZ == 'TH' ) {
             $variablell = DB::select('call thutins(?)', array($no_bukti));
        }
		

	    $no_buktix = $no_bukti;
		
		$utbeli = Beli::where('NO_BUKTI', $no_buktix )->first();
					 
		return redirect('/utbeli?flagz='.$FLAGZ.'&golz='.$GOLZ)
	   ->with(['judul' => $judul, 'golz' => $GOLZ, 'flagz' => $FLAGZ ]);

    }


   public function edit( Request $request , Beli $utbeli)
    {


		$per = session()->get('periode')['bulan'] . '/' . session()->get('periode')['tahun'];
		
				
        $cekperid = DB::SELECT("SELECT POSTED from perid WHERE PERIO='$per'");
        if ($cekperid[0]->POSTED==1)
        {
            return redirect('/utbeli')
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
		   
		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from beli
		                 where PER ='$per' and FLAG ='$this->FLAGZ' 
						 and GOL ='$this->GOLZ' and NO_BUKTI = '$buktix'
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
			

		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from beli 
		                 where PER ='$per' and GOL ='$this->GOLZ'
						 and FLAG ='$this->FLAGZ' 
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
			
		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from beli     
		             where PER ='$per' and GOL ='$this->GOLZ' 
					 and FLAG ='$this->FLAGZ' AND CBG = $CBG
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
	   
		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from beli    
		             where PER ='$per' and GOL ='$this->GOLZ' 
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
		  
    		$bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from beli
						where PER ='$per' and GOL ='$this->GOLZ' 
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
			$utbeli = Beli::where('NO_ID', $idx )->first();	
	     }
		 else
		 {
				$utbeli = new Beli;
                $utbeli->TGL = Carbon::now();
      
				
		 }

        $no_bukti = $utbeli->NO_BUKTI;
				
		$data = [
            'header'        => $utbeli,

        ];
 
         
         return view('otransaksi_utbeli.edit', $data)
		 ->with(['tipx' => $tipx, 'idx' => $idx, 'golz' =>$this->GOLZ, 'flagz' =>$this->FLAGZ, 'judul' => $this->judul ]);
			 
    
      
    }




    public function update(Request $request, Beli $utbeli)
    {
        $this->validate(
            $request,
            [
                'TGL'      => 'required',

            ]
        );

		$this->setFlag($request);
        $FLAGZ = $this->FLAGZ;
        $GOLZ = $this->GOLZ;
        $judul = $this->judul;	
		
        $CBG = Auth::user()->CBG;
		
		if ( $FLAGZ == 'UM' ) {

             $variablell = DB::select('call umdel(?,?)', array($utbeli['NO_BUKTI'], '0'));


        } else if ( $FLAGZ == 'TH' ) {
             $variablell = DB::select('call thutdel(?)', array($utbeli['NO_BUKTI']));

        }
		
		

        $utbeli->update(
            [
                'TGL'              => date('Y-m-d', strtotime($request['TGL'])),
				'NO_BL'            => ($request['NO_BL'] == null) ? "" : $request['NO_BL'],
                'NO_PO'            => ($request['NO_PO'] == null) ? "" : $request['NO_PO'],
                'KODES'            => ($request['KODES'] == null) ? "" : $request['KODES'],
                'NAMAS'            => ($request['NAMAS'] == null) ? "" : $request['NAMAS'],
                'ALAMAT'           => ($request['ALAMAT'] == null) ? "" : $request['ALAMAT'],
                'KOTA'             => ($request['KOTA'] == null) ? "" : $request['KOTA'],
                'NOTES'            => ($request['NOTES'] == null) ? "" : $request['NOTES'],
                'TOTAL'            => (float) str_replace(',', '', $request['TOTAL']),
                'NETT'          => ( $FLAGZ == 'UM') ? (float) str_replace(',', '', $request['TOTAL'] ) * -1  : (float) str_replace(',', '', $request['RPTOTAL'] ),      
				'SISA'           => ( $FLAGZ == 'UM') ? (float) str_replace(',', '', $request['TOTAL'] ) * -1  : (float) str_replace(',', '', $request['RPTOTAL'] ),      
                		
                'ACNOA'            => ($request['ACNOA'] == null) ? "" : $request['ACNOA'],				
				'NACNOA'           => ($request['NACNOA'] == null) ? "" : $request['NACNOA'],
                'BACNO'            => ($request['BACNO'] == null) ? "" : $request['BACNO'],
                'BNAMA'            => ($request['BNAMA'] == null) ? "" : $request['BNAMA'],				
                'TYPE'             => ($request['TYPE'] == null) ? "" : $request['TYPE'],				
                'USRNM'            => Auth::user()->username,
                'updated_by'       => Auth::user()->username,
                'CBG'              => $CBG,
                'TG_SMP'           => Carbon::now()
            ]
        );

		$no_buktix = $utbeli->NO_BUKTI;
		
	

		if ( $FLAGZ == 'UM' ) {
          
    	     $variablell = DB::select('call umins(?,?)', array($utbeli['NO_BUKTI'], 'X'));

        } else if ( $FLAGZ == 'TH' ) {
             $variablell = DB::select('call thutins(?)', array($utbeli['NO_BUKTI']));
        }
		
		

		$utbeli = Beli::where('NO_BUKTI', $no_buktix )->first();
					 
		return redirect('/utbeli?flagz='.$FLAGZ.'&golz='.$GOLZ)
	   ->with(['judul' => $judul, 'golz' => $GOLZ, 'flagz' => $FLAGZ ]);

    }

    public function destroy( Request $request, Beli $utbeli)
    {

		$this->setFlag($request);
        $FLAGZ = $this->FLAGZ;
        $GOLZ = $this->GOLZ;
        $judul = $this->judul;
		
		$per = session()->get('periode')['bulan'] . '/' . session()->get('periode')['tahun'];
        $cekperid = DB::SELECT("SELECT POSTED from perid WHERE PERIO='$per'");
        if ($cekperid[0]->POSTED==1)
        {
            return redirect()->route('utbeli')
                ->with('status', 'Maaf Periode sudah ditutup!')
                ->with(['judul' => $judul, 'golz' => $GOLZ, 'flagz' => $FLAGZ]);
				
        }
				
				
				
				
				
		if ( $FLAGZ == 'UM' ) {
           
		    $variablell = DB::select('call umdel(?,?)', array($utbeli['NO_BUKTI'], '1'));

        } else if ( $FLAGZ == 'TH' ) {
             $variablell = DB::select('call thutdel(?)', array($utbeli['NO_BUKTI']));
        }
		
        $deleteBeli = Beli::find($utbeli->NO_ID);
        $deleteBeli->delete();

		return redirect('/utbeli?flagz='.$FLAGZ.'&golz='.$GOLZ)
		       ->with(['judul' => $judul, 'golz' => $GOLZ, 'flagz' => $FLAGZ ])
			   ->with('statusHapus', 'Data '.$utbeli->NO_BUKTI.' berhasil dihapus');



    }

    public function repost(Beli $utbeli)
    {
        DB::SELECT("UPDATE utbeli SET POSTED=0 WHERE NO_ID=".$utbeli->NO_ID." AND FLAG in ('BD','BN')");
        return redirect('/utbelin')->with('status', 'Data '.$utbeli->NO_BUKTI.' berhasil dibuka posting');
    }
	
	
	public function jsutbelic(Beli $utbeli)
    {
        $no_utbeli = $utbeli->NO_BUKTI;

        $file     = 'utbelic';
        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

        $query = DB::SELECT("
			SELECT NO_BUKTI,  TGL, KODES, NAMAS, KD_BHN, NA_BHN, KG, HARGA, TOTAL, NOTES, AJU, BL, EMKL
			FROM utbeli 
			WHERE utbeli.NO_BUKTI='$no_utbeli' 
			ORDER BY NO_BUKTI;
		");

        $xno_utbeli1   = $query[0]->NO_BUKTI;
        $xtgl1     = $query[0]->TGL;
        $xkodes1   = $query[0]->KODES;
        $xnamas1   = $query[0]->NAMAS;
        $xnotes1   = $query[0]->NOTES;
        $xkd_brg1  = $query[0]->KD_BHN;
        $xna_brg1  = $query[0]->NA_BHN;
        $xkg1      = $query[0]->KG;
        $xaju1     = $query[0]->AJU;
        $xbl1      = $query[0]->BL;
        $xemkl1    = $query[0]->EMKL;
        $xharga1   = $query[0]->HARGA;
        $xtotal1   = $query[0]->TOTAL;
        
        $PHPJasperXML->arrayParameter = array("HARGA1" => (float) $xharga1, "TOTAL1" => (float) $xtotal1, "KG1" => (float) $xkg1, 
										"NO_BELI1" => (string) $xno_utbeli1, "TGL1" => (string) $xtgl1, 
										"KODES1" => (string) $xkodes1,  "NAMAS1" => (string) $xnamas1,
										"KD_BHN1" => (string) $xkd_brg1, "AJU1" => (string) $xaju1,
										"BL1" => (string) $xbl1, "EMKL1" => (string) $xemkl1,
										"NA_BHN1" => (string) $xna_brg1,  "NOTES1" => (string) $xnotes1 );
        $PHPJasperXML->arraysqltable = array();


        $query2 = DB::SELECT("
			SELECT NO_BUKTI, TGL, TRUCK, NO_CONT, KG1, KG, SEAL, GUDANG, NOTES, NO_BL, SUSUT
			FROM terima 
			WHERE terima.NO_BL='$no_utbeli'  
			ORDER BY TGL, NO_BUKTI;
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
                'TRUCK'    => $query2[$key]->TRUCK,
                'SEAL'    => $query2[$key]->SEAL,
                'GUDANG'    => $query2[$key]->GUDANG,
                'JCONT'    => $query2[$key]->NO_CONT,
                'KG'       => $query2[$key]->KG,
                'KG1'       => $query2[$key]->KG1,
                'HARGA'    => $query2[$key]->HARGA,
                'SUSUT'    => $query2[$key]->SUSUT,
                'BAYAR'    => $query2[$key]->BAYAR,
                'NOTES'    => $query2[$key]->NOTES
            ));
        }
		
        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I");
    }
}