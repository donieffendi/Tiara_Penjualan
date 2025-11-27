<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
// ganti 1

use App\Models\OTransaksi\Jual;
use App\Models\OTransaksi\Jualutbeli;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use DB;
use Carbon\Carbon;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;

// ganti 2
class UtjualController extends Controller
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
        if ( $request->flagz == 'JL' && $request->golz == 'Y' ) {
            $this->judul = "Penjualan Barang";
        } else if ( $request->flagz == 'TP' && $request->golz == 'Y' ) {
            $this->judul = "Transaksi Piutang";
        } else if ( $request->flagz == 'UM' && $request->golz == 'Y' ) {
            $this->judul = "Uang Muka Penjualan";
        }
				
        $this->FLAGZ = $request->flagz;
        $this->GOLZ = $request->golz;    
		
	}
	
    public function index(Request $request)
    {

        // ganti 3
        $this->setFlag($request);
        // ganti 3
        return view('otransaksi_utjual.index')->with(['judul' => $this->judul, 'golz' => $this->GOLZ , 'flagz' => $this->FLAGZ ]);
    }


    public function browse(Request $request)
    {
        //$utjual = DB::table('jual')->select('NO_BUKTI', 'TGL', 'KODEC','NAMAC', 'ALAMAT','KOTA', 'TOTAL','BAYAR','SISA')->where('NO_SO', $request['NO_SO'] )->where('SISA', '<>', 0 )->where('GOL', 'Y')->orderBy('KODEC', 'ASC')->get();

        $listutbeli = implode(",", $request->listutbeli);
        $inutbeli = '';
        if ($request->listutbeli) {
            $inutbeli = " and NO_BUKTI not in ($listutbeli) ";
        }

        $CBG = Auth::user()->CBG;
		
        $utjual = DB::SELECT("SELECT NO_BUKTI,TGL,KODEC,NAMAC,ALAMAT,KOTA,TOTAL,BAYAR,SISA,TRUCK,if(DATEDIFF(date(now()),TGL)>=30,'Y','') as LEBIH30 
        from jual
		WHERE NO_SO='" . $request['NO_SO'] . "' and SISA<>0 and GOL='Y' " . $inutbeli . " AND CBG = '$CBG' 
        ORDER BY KODEC;");

        return response()->json($utjual);
    }

    public function browseuang(Request $request)
    {
        //$beli = DB::table('beli')->select('NO_BUKTI', 'TGL', 'KODES','NAMAS', 'ALAMAT','KOTA', 'TOTAL','BAYAR','SISA')->where('KODES', $request['KODES'] )->where('SISA', '<>', 0 )->where('GOL', 'Y')->orderBy('KODES', 'ASC')->get();

		$listutbeli = implode(",", $request->listutbeli);
        $inutbeli = '';
        if ($request->listutbeli) {
            $inutbeli = " and NO_BUKTI not in ($listutbeli) ";
        }

        $CBG = Auth::user()->CBG;
		
        $utjual = DB::SELECT("SELECT NO_BUKTI,TGL, NO_SO, KODEC, NAMAC, RPTOTAL AS TOTAL, RPBAYAR AS BAYAR, RPSISA AS SISA  from jual
		WHERE  NO_SO='" . $request['NO_SO'] . "' AND RPSISA<>'0'  AND CBG = '$CBG'
        ORDER BY NO_BUKTI; ");

        return response()->json($utjual);
    }
	
	
    // ganti 4

    public function getUtjual(Request $request)
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
		
        $utjual = DB::SELECT("SELECT * from jual  where  PER ='$periode' and FLAG ='$this->FLAGZ' 
                AND GOL ='$this->GOLZ' AND CBG = '$CBG' ORDER BY NO_BUKTI ");

  
        // ganti 6

        return Datatables::of($utjual)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                if (Auth::user()->divisi=="programmer" || Auth::user()->divisi=="owner" || Auth::user()->divisi=="assistant" || Auth::user()->divisi=="penjualan") 
                {
 
                    $btnEdit =   ($row->POSTED == 1) ? ' onclick= "alert(\'Transaksi ' . $row->NO_BUKTI . ' sudah diposting!\')" href="#" ' : ' href="utjual/edit/?idx
					=' . $row->NO_ID . '&tipx=edit&flagz=' . $row->FLAG . '&golz=' . $row->GOL . '&judul=' . $this->judul . '"';
					
                    $btnDelete = ($row->POSTED == 1) ? ' onclick= "alert(\'Transaksi ' . $row->NO_BUKTI . ' sudah diposting!\')" href="#" ' : ' onclick="return confirm(&quot; Apakah anda yakin ingin hapus? &quot;)" href="utjual/delete/' . $row->NO_ID . '/?flagz=' . $row->FLAG . '&golz=' . $row->GOL . '" ';

 
                    $btnPrivilege =
                        '
                                <a class="dropdown-item" ' . $btnEdit . '>
                                <i class="fas fa-edit"></i>
                                    Edit
                                </a>
                                <a class="dropdown-item btn btn-danger" href="utjual/print/' . $row->NO_ID . '">
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
    public function store(Request $request)
    {


        $this->validate(
            $request,
            // GANTI 9

            [
                'NO_SO'       => 'required',
                'TGL'      => 'required',
                'KODEC'       => 'required',

            ]
        );

        // Insert Header
		$this->setFlag($request);
        $FLAGZ = $this->FLAGZ;
        $GOLZ = $this->GOLZ;
        $judul = $this->judul;	
		
        $CBG = Auth::user()->CBG;
		
        $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];

        $bulan    = session()->get('periode')['bulan'];
        $tahun    = substr(session()->get('periode')['tahun'], -2);
       
        $no_bukti ='';
		$no_bukti2 ='';
		
		if ( $request->flagz == 'JL' && $request->golz == 'Y' ) {

            $query = DB::table('jual')->select(DB::raw("TRIM(NO_BUKTI) AS NO_BUKTI"))->where('PER', $periode)
			         ->where('FLAG', 'JL')->where('GOL', 'Y')->where('CBG', $CBG)->orderByDesc('NO_BUKTI')->limit(1)->get();
			
			if ($query != '[]') {
            
				$query = substr($query[0]->NO_BUKTI, -4);
				$query = str_pad($query + 1, 4, 0, STR_PAD_LEFT);
				$no_bukti = 'JY' . $CBG . $tahun . $bulan . '-' . $query;
			
			} else {
				$no_bukti = 'JY' . $CBG . $tahun . $bulan . '-0001';
				}
		
        } else if ( $request->flagz == 'TP' ) {

            $query = DB::table('jual')->select(DB::raw("TRIM(NO_BUKTI) AS NO_BUKTI"))->where('PER', $periode)
			         ->where('FLAG', 'TP')->where('CBG', $CBG)->orderByDesc('NO_BUKTI')->limit(1)->get();
			
			if ($query != '[]') {
            
				$query = substr($query[0]->NO_BUKTI, -4);
				$query = str_pad($query + 1, 4, 0, STR_PAD_LEFT);
				$no_bukti = 'TPY' . $CBG . $tahun . $bulan . '-' . $query;
			
			} else {
				$no_bukti = 'TPY' . $CBG . $tahun . $bulan . '-0001';
				}
				
        } else if ( $request->flagz == 'UM' ) {
 
            $query = DB::table('jual')->select(DB::raw("TRIM(NO_BUKTI) AS NO_BUKTI"))->where('PER', $periode)
			         ->where('FLAG', 'UM')->where('CBG', $CBG)->orderByDesc('NO_BUKTI')->limit(1)->get();
			
			if ($query != '[]') {
            
				$query = substr($query[0]->NO_BUKTI, -4);
				$query = str_pad($query + 1, 4, 0, STR_PAD_LEFT);
				$no_bukti = 'UJ' . $CBG . $tahun . $bulan . '-' . $query;
			
			} else {
				$no_bukti = 'UJ' . $CBG . $tahun . $bulan . '-0001';
				}
 
 			$bulan    = session()->get('periode')['bulan'];
            $tahun    = substr(session()->get('periode')['tahun'], -2);
            $query2 = DB::table('bank')->select('NO_BUKTI')->where('PER', $periode)->where('TYPE', 'BBM')->where('CBG', $CBG)->orderByDesc('NO_BUKTI')->limit(1)->get();

            if ($query2 != '[]') {
                $query2 = substr($query2[0]->NO_BUKTI, -4);
                $query2 = str_pad($query2 + 1, 4, 0, STR_PAD_LEFT);
                $no_bukti2 = 'BBM' . $CBG . $tahun . $bulan . '-' . $query2;
            } else {
                $no_bukti2 = 'BBM' . $CBG . $tahun . $bulan . '-0001';
            }
			
			
        } 
        
		
        // Insert Header

        // ganti 10

        $utjual = Jual::create(
            [
                'NO_BUKTI'         => $no_bukti,
                'TGL'              => date('Y-m-d', strtotime($request['TGL'])),
                'PER'              => $periode,
                'NO_SO'            => ($request['NO_SO'] == null) ? "" : $request['NO_SO'],
                'KODEC'            => ($request['KODEC'] == null) ? "" : $request['KODEC'],
                'NAMAC'            => ($request['NAMAC'] == null) ? "" : $request['NAMAC'],
                'TRUCK'            => ($request['TRUCK'] == null) ? "" : $request['TRUCK'],
                'SOPIR'            => ($request['SOPIR'] == null) ? "" : $request['SOPIR'],
                'ALAMAT'           => ($request['ALAMAT'] == null) ? "" : $request['ALAMAT'],
                'KOTA'             => ($request['KOTA'] == null) ? "" : $request['KOTA'],
                'FLAG'             => $FLAGZ,
                // 'GOL'               => $GOLZ,
                'ACNOA'            => '113101',
                'NACNOA'           => 'PIUTANG DAGANG ',
                'ACNOB'            => ($request['ACNOB'] == null) ? "" : $request['ACNOB'],
                'NACNOB'           => ($request['NACNOB'] == null) ? "" : $request['NACNOB'],
                'NOTES'            => ($request['NOTES'] == null) ? "" : $request['NOTES'],
                'TOTAL'            => (float) str_replace(',', '', $request['TOTAL']),
                'NETT'           => ($FLAGZ == 'UM') ? (float) str_replace(',', '', $request['TOTAL'] ) * -1  : (float) str_replace(',', '', $request['TOTAL'] ),      
                'SISA'          => ($FLAGZ == 'UM') ? (float) str_replace(',', '', $request['TOTAL'] ) * -1  : (float) str_replace(',', '', $request['TOTAL'] ), 
                'BACNO'            => ($request['BACNO'] == null) ? "" : $request['BACNO'],
                'BNAMA'            => ($request['BNAMA'] == null) ? "" : $request['BNAMA'],				
                'NO_BANK'          => $no_bukti2,
                'USRNM'            => Auth::user()->username,
                'created_by'       => Auth::user()->username,
                'CBG'              => $CBG,
                'TG_SMP'           => Carbon::now()
            ]
        );


        //  ganti 11
		if ( $FLAGZ == 'UM' ) {
			// $variablell = DB::select('call ujins(?,?)', array($no_bukti, $no_bukti2));

        } else if ( $FLAGZ == 'TP' ) {
            // $variablell = DB::select('call tpiuins(?)', array($no_bukti));
        }
		

	    $no_buktix = $no_bukti;
		
		$utjual = Jual::where('NO_BUKTI', $no_buktix )->first();
					 
        //return redirect('/utjual/edit/?idx=' . $kas->NO_ID . '&tipx=edit&golz=' . $this->GOLZ . '&flagz=' . $this->FLAGZ . '&judul=' . $this->judul . '');
		return redirect('/utjual?flagz='.$FLAGZ)
		       ->with(['judul' => $judul, 'flagz' => $FLAGZ ]);

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    // ganti 12




   public function edit( Request $request , Jual $utjual)
    {


		$per = session()->get('periode')['bulan'] . '/' . session()->get('periode')['tahun'];
		
				
        $cekperid = DB::SELECT("SELECT POSTED from perid WHERE PERIO='$per'");
        if ($cekperid[0]->POSTED==1)
        {
            return redirect('/utjual')
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
		   
		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from jual
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
			

		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from jual 
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
			
		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from jual      
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
	   
		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from jual    
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
		  
    		$bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from jual  where PER ='$per'
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
			$utjual = Jual::where('NO_ID', $idx )->first();	
	     }
		 else
		 {
				$utjual = new Jual;
                $utjual->TGL = Carbon::now();
      
				
		 }

        $no_bukti = $utjual->NO_BUKTI;
				
		$data = [
            'header'        => $utjual,

        ];
 
         
         return view('otransaksi_utjual.edit', $data)
		 ->with(['tipx' => $tipx, 'idx' => $idx, 'flagz' =>$this->FLAGZ, 'judul' =>  $this->judul ]);
			 
    
      
    }


    // ganti 18

    public function update(Request $request, Jual $utjual)
    {

        $this->validate(
            $request,
            [

                // ganti 19

                'TGL'      => 'required',
                'NO_SO'       => 'required',
                'KODEC'       => 'required',
            ]
        );

        // ganti 20


		
		

		$this->setFlag($request);
        $FLAGZ = $this->FLAGZ;
        $judul = $this->judul;	
		
        $CBG = Auth::user()->CBG;
		
		if ( $FLAGZ == 'UM' ) {

            // $variablell = DB::select('call ujdel(?,?)', array($utjual['NO_BUKTI'], '0'));

        } else if ( $FLAGZ == 'TP' ) {
            // $variablell = DB::select('call tpiudel(?)', array($utjual['NO_BUKTI']));

        }
		
		

        $utjual->update(
            [
                'TGL'              => date('Y-m-d', strtotime($request['TGL'])),
                'NO_SO'            => ($request['NO_SO'] == null) ? "" : $request['NO_SO'],
                'KODEC'            => ($request['KODEC'] == null) ? "" : $request['KODEC'],
                'NAMAC'            => ($request['NAMAC'] == null) ? "" : $request['NAMAC'],
                'ALAMAT'           => ($request['ALAMAT'] == null) ? "" : $request['ALAMAT'],
                'KOTA'             => ($request['KOTA'] == null) ? "" : $request['KOTA'],
                'NOTES'            => ($request['NOTES'] == null) ? "" : $request['NOTES'],
                'TOTAL'            => (float) str_replace(',', '', $request['TOTAL']),
                'NETT'           => ($FLAGZ == 'UM') ? (float) str_replace(',', '', $request['TOTAL'] ) * -1  : (float) str_replace(',', '', $request['TOTAL'] ),      
                'SISA'          => ($FLAGZ == 'UM') ? (float) str_replace(',', '', $request['TOTAL'] ) * -1  : (float) str_replace(',', '', $request['TOTAL'] ),      
                'ACNOB'            => ($request['ACNOB'] == null) ? "" : $request['ACNOB'],
                'NACNOB'           => ($request['NACNOB'] == null) ? "" : $request['NACNOB'],			
                'BACNO'            => ($request['BACNO'] == null) ? "" : $request['BACNO'],
                'BNAMA'            => ($request['BNAMA'] == null) ? "" : $request['BNAMA'],				
                'USRNM'            => Auth::user()->username,
                'updated_by'       => Auth::user()->username,
                'CBG'              => $CBG,
                'TG_SMP'           => Carbon::now()
            ]
        );


        //  ganti 21
		if ( $FLAGZ == 'UM' ) {
         
     		// $variablell = DB::select('call ujins(?,?)', array($utjual['NO_BUKTI'], 'X'));

        } else if ( $FLAGZ == 'TP' ) {
            // $variablell = DB::select('call tpiuins(?)', array($utjual['NO_BUKTI']));
        }
		
		

	    $no_buktix = $utjual->NO_BUKTI;
		
		$utjual = Jual::where('NO_BUKTI', $no_buktix )->first();
					 
        //return redirect('/utjual/edit/?idx=' . $utjual->NO_ID . '&tipx=edit&golz=' . $this->GOLZ . '&flagz=' . $this->FLAGZ . '&judul=' . $this->judul . '');			
		return redirect('/utjual?flagz='.$FLAGZ)
		       ->with(['judul' => $judul, 'flagz' => $FLAGZ ]);
 
    }
	

    
   
	
    public function destroy( Request $request, Jual $utjual)
    {

		$this->setFlag($request);
        $FLAGZ = $this->FLAGZ;
        $GOLZ = $this->GOLZ;
        $judul = $this->judul;
		
		
		$per = session()->get('periode')['bulan'] . '/' . session()->get('periode')['tahun'];
 
		$cekperid = DB::SELECT("SELECT POSTED from perid WHERE PERIO='$per'");
        if ($cekperid[0]->POSTED==1)
        {
            return redirect()->route('utjual')
                ->with('status', 'Maaf Periode sudah ditutup!')
                ->with(['judul' => $judul, 'flagz' => $FLAGZ]);
				
        }
		
		if ( $FLAGZ == 'UM' ) {

			// $variablell = DB::select('call ujdel(?,?)', array($utjual['NO_BUKTI'], '1'));
		
        } else if ( $FLAGZ == 'TP' ) {
            // $variablell = DB::select('call tpiudel(?)', array($utjual['NO_BUKTI']));
        }
		
        // ganti 23
        $deleteJual = Jual::find($utjual->NO_ID);

        // ganti 24

        $deleteJual->delete();

        // ganti 
		return redirect('/utjual?flagz='.$FLAGZ)
		       ->with(['judul' => $judul, 'flagz' => $FLAGZ ])
			   ->with('statusHapus', 'Data '.$utjual->NO_BUKTI.' berhasil dihapus');
			   
			   
    }
	///////////////////////////////////
	 public function cetak(Jual $utjual)
    {
        $NO_JUAL = $utjual->NO_BUKTI;

        $file     = 'jualc';
        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));
		

        $query = DB::SELECT("
           SELECT NO_BUKTI,TGL,NO_SO,TRUCK, KODEC,NAMAC,KD_BRG,NA_BRG,KG,QTY, HARGA,TOTAL,NOTES from jual WHERE FLAG='JL' ORDER BY NO_BUKTI;
		");

        $data = [];

        $rec=1;
        $kdbrg = '';
        $nabrg = '';
        foreach ($query as $key => $value) {
            if($query[$key]->KD_BRG!='')
            {
                $kdbrg = $query[$key]->KD_BRG;
                $nabrg = $query[$key]->NA_BRG;
            }

           
                array_push($data, array(
				'NO_BUKTI' => $query[$key]->NO_BUKTI,
				'TGL' => $query[$key]->TGL,
				'NO_SO' => $query[$key]->NO_SO,
				'KODEC' => $query[$key]->KODEC,
				'NAMAC' => $query[$key]->NAMAC,
				'TRUCK' => $query[$key]->TRUCK,
				'KG' => $query[$key]->KG,
				'QTY' => $query[$key]->QTY,
				'NAMAC' => $query[$key]->NAMAC,
				'KD_BRG' => $query[$key]->KD_BRG,
				'NA_BRG' => $query[$key]->NA_BRG,
				'HARGA' => $query[$key]->HARGA,
				'TOTAL' => $query[$key]->TOTAL,
				'NOTES' => $query[$key]->NOTES,
               
            ));
            $rec++;
        }

        $PHPJasperXML->arrayParameter = array(
            "KD_BRG" => (string) $kdbrg,
            "NA_BRG" => (string) $nabrg,
        );
        //if ($utjual->CETAK==0) DB::SELECT("UPDATE utjual SET CETAK=1, TGL_CETAK=NOW() WHERE NO_BUKTI='$no_jual'");
		
        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I");
    }

}
