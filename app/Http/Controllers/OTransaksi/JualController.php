<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
// ganti 1

use App\Models\OTransaksi\Jual;
use App\Models\OTransaksi\JualDetail;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use DB;
use Carbon\Carbon;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";
use PHPJasperXML;

// ganti 2
class JualController extends Controller
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
            $this->judul = "Penjualan Bahan Baku";
        } else if ( $request->flagz == 'AJ' && $request->golz == 'B' ) {
            $this->judul = "Retur Penjualan Bahan Baku";
        } else if ( $request->flagz == 'JL' && $request->golz == 'J' ) {
            $this->judul = "Penjualan Barang";
        } else if ( $request->flagz == 'AJ' && $request->golz == 'J' ) {
            $this->judul = "Retur Penjualan Barang";
        }
		
        $this->FLAGZ = $request->flagz;
        $this->GOLZ = $request->golz;


    }
       	
    public function index(Request $request)
    {

	    $this->setFlag($request);
        // ganti 3
        return view('otransaksi_jual.index')->with(['judul' => $this->judul, 'flagz' => $this->FLAGZ, 'golz' => $this->GOLZ ]);
	
	
    }

	public function index_posting(Request $request)
    {
 
        return view('otransaksi_jual.post');
    }

    public function browse(Request $request)
    {
        $golz = $request->GOL;

		$CBG = Auth::user()->CBG;

        $jual = DB::SELECT("SELECT jual.NO_BUKTI, jual.NO_SO, jual.KODEC, jual.NAMAC, 
		                  jual.ALAMAT, jual.KOTA, jual.KODEP, jual.NAMAP, 
                          JUALD.KD_BRG, JUALD.NA_BRG, JUALD.QTY, 
                          JUALD.HARGA, JUALD.TOTAL from jual, juald 
                          WHERE jual.NO_BUKTI = jualD.NO_BUKTI AND jual.GOL ='$golz'
                          AND jual.CBG = '$CBG' AND NO_RETUR =''  ");
        return response()->json($jual);
    }


    public function browseSurat(Request $request)
    {
        $golz = $request->GOL;

		$CBG = Auth::user()->CBG;

        $jual = DB::SELECT("SELECT surats.NO_BUKTI, surats.NO_SPM, surats.NO_SO, surats.KODEC, surats.NAMAC, 
		                  surats.ALAMAT, surats.KOTA , surats.KODEP, surats.NAMAP,  suratsd.KD_BRG, suratsd.NA_BRG, suratsd.QTY, suratsd.HARGA, suratsd.TOTAL from surats, suratsd 
                          WHERE surats.NO_BUKTI = suratsd.NO_BUKTI AND surats.GOL ='$golz'
                          AND surats.CBG = '$CBG' AND surats.NO_JUAL =''  ");
        return response()->json($jual);
    }

	public function browse_detail(Request $request)
    {

        // $filterbukti = '';
        // if($request->NO_SO)
        // {

        //     $filterbukti = " WHERE NO_BUKTI='".$request->NO_SO."' ";
        // }
        $spmd= DB::SELECT("SELECT REC, KD_BRG, NA_BRG, SATUAN , QTY, HARGA, TOTAL, KET
                            from suratsd
                            where NO_BUKTI='".$request->nobukti."' ORDER BY NO_BUKTI ");
	

		return response()->json($spmd);
	}


    public function browseuang(Request $request)
    {

		$filterkodec = '';
	   
		$CBG = Auth::user()->CBG;

		if($request->KODEC)
		{
	
			// $filterkodec = " WHERE SISA <> 0 AND KODEC='".$request->KODEC."' ";
			$filterkodec = " WHERE KODEC='".$request->KODEC."' ";
		}
		
		$jual = DB::SELECT("SELECT NO_BUKTI, TGL, KODEC, 
                        NAMAC, NETT AS TOTAL, BAYAR, SISA from jual
                        $filterkodec AND CBG = '$CBG' 
                        ORDER BY NO_BUKTI ");
 
        return response()->json($jual);
    }
	
    // ganti 4

    public function getJual(Request $request)
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

        $jual = DB::SELECT("SELECT * from jual  where PER = '$periode' and FLAG ='$this->FLAGZ' 
                            AND GOL ='$this->GOLZ' AND CBG='$CBG' ORDER BY NO_BUKTI ");
	   
        // ganti 6

        return Datatables::of($jual)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                if ( Auth::user()->divisi=="programmer" ) 
				{
                    //CEK POSTED di index dan edit
                    $btnEdit =   ($row->POSTED == 1) ? ' onclick= "alert(\'Transaksi ' . $row->NO_BUKTI . ' sudah diposting!\')" href="#" ' : ' href="jual/edit/?idx=' . $row->NO_ID . '&tipx=edit&flagz=' . $row->FLAG . '&judul=' . $this->judul . '&golz=' . $row->GOL . '"';					
                    $btnDelete = ($row->POSTED == 1) ? ' onclick= "alert(\'Transaksi ' . $row->NO_BUKTI . ' sudah diposting!\')" href="#" ' : ' onclick="return confirm(&quot; Apakah anda yakin ingin hapus? &quot;)" href="jual/delete/' . $row->NO_ID . '/?flagz=' . $row->FLAG . '&golz=' . $row->GOL . '" ';


                    $btnPrivilege =
                        '
                                <a class="dropdown-item" ' . $btnEdit . '>
                                <i class="fas fa-edit"></i>
                                    Edit
                                </a>
                                <a class="dropdown-item btn btn-danger" href="jual/cetak/' . $row->NO_ID . '">
                                    <i class="fa fa-print" aria-hidden="true"></i>
                                    Print
                                </a> 									
                                <hr></hr>
                                <a class="dropdown-item btn btn-danger"  ' . $btnDelete . '>
   
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
                        <a class="btn btn-secondary dropdown-toggle btn-sm" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-hasbelipup="true" aria-expanded="false">
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

        $query = DB::table('jual')->select('NO_BUKTI')->where('PER', $periode)->where('FLAG', $FLAGZ )->where('CBG', $CBG)->orderByDesc('NO_BUKTI')->limit(1)->get();

        if( $GOLZ=='B'){

            if ($query != '[]') {
                $query = substr($query[0]->NO_BUKTI, -4);
                $query = str_pad($query + 1, 4, 0, STR_PAD_LEFT);
                $no_bukti = $this->FLAGZ . $this->GOLZ . $CBG . $tahun . $bulan . '-' . $query;
            } else {
                $no_bukti = $this->FLAGZ . $this->GOLZ . $CBG . $tahun . $bulan . '-0001';
            }

        } elseif($GOLZ=='J') {

            if ($query != '[]') {
                $query = substr($query[0]->NO_BUKTI, -4);
                $query = str_pad($query + 1, 4, 0, STR_PAD_LEFT);
                $no_bukti = $this->FLAGZ . $CBG .  $tahun . $bulan . '-' . $query;
            } else {
                $no_bukti = $this->FLAGZ . $CBG .  $tahun . $bulan . '-0001';
            }

        }

        
		
//////////////////////////////////////////////////////////////////////////
       

        // Insert Header

        // ganti 10

        $jual = Jual::create(
            [
                'NO_BUKTI'         => $no_bukti,
                'TGL'              => date('Y-m-d', strtotime($request['TGL'])),
                'PER'              => $periode,
                'FLAG'             => $FLAGZ,						
                'GOL'              => $GOLZ,			
                'NO_SO'            => ($request['NO_SO'] == null) ? "" : $request['NO_SO'],
                'NO_JUAL'            => ($request['NO_JUAL'] == null) ? "" : $request['NO_JUAL'],
                'NO_SURATS'            => ($request['NO_SURATS'] == null) ? "" : $request['NO_SURATS'],
                'NO_SPM'            => ($request['NO_SPM'] == null) ? "" : $request['NO_SPM'],

                'KODEP'            => ($request['KODEP'] == null) ? "" : $request['KODEP'],
                'NAMAP'            => ($request['NAMAP'] == null) ? "" : $request['NAMAP'],
                
                'KODEC'            => ($request['KODEC'] == null) ? "" : $request['KODEC'],
                'NAMAC'            => ($request['NAMAC'] == null) ? "" : $request['NAMAC'],
                'ALAMAT'           => ($request['ALAMAT'] == null) ? "" : $request['ALAMAT'],
                'KOTA'             => ($request['KOTA'] == null) ? "" : $request['KOTA'],

                'NOTES'            => ($request['NOTES'] == null) ? "" : $request['NOTES'],
                'TOTAL_QTY'        => (float) str_replace(',', '', $request['TTOTAL_QTY']),
                'TOTAL'            => (float) str_replace(',', '', $request['TTOTAL']),
                'PPN'            => (float) str_replace(',', '', $request['PPN']),
                'NETT'            => (float) str_replace(',', '', $request['NETT']),
                'SISA'            => (float) str_replace(',', '', $request['NETT']),
       
	   
                'USRNM'            => Auth::user()->username,
                'TG_SMP'           => Carbon::now(),
				'created_by'       => Auth::user()->username,
                'CBG'              => $CBG,
            ]
        );


		$REC        = $request->input('REC');
		$KD_BHN     = $request->input('KD_BHN');
        $NA_BHN     = $request->input('NA_BHN');
		$KD_BRG     = $request->input('KD_BRG');
        $NA_BRG     = $request->input('NA_BRG');
        $SATUAN     = $request->input('SATUAN');
        $KET     = $request->input('KET');
        $QTY        = $request->input('QTY');
        $HARGA        = $request->input('HARGA');
        $PPNX        = $request->input('PPN');
        $DPP        = $request->input('DPP');
	    $TOTAL        = $request->input('TOTAL');		 

        // Check jika value detail ada/tidak
        if ($REC) {
            foreach ($REC as $key => $value) {
                // Declare new data di Model
                $detail    = new JualDetail;

                // Insert ke Database
                $detail->NO_BUKTI    = $no_bukti;
                $detail->REC         = $REC[$key];
                $detail->PER         = $periode;
                $detail->FLAG        = $FLAGZ;		
                $detail->GOL 	     = $GOLZ;               
                $detail->KD_BHN      = ($KD_BHN[$key] == null) ? "" :  $KD_BHN[$key];
                $detail->NA_BHN      = ($NA_BHN[$key] == null) ? "" :  $NA_BHN[$key];          
                $detail->KD_BRG      = ($KD_BRG[$key] == null) ? "" :  $KD_BRG[$key];
                $detail->NA_BRG      = ($NA_BRG[$key] == null) ? "" :  $NA_BRG[$key];
                $detail->SATUAN      = ($SATUAN[$key] == null) ? "" :  $SATUAN[$key];				
                $detail->KET      = ($KET[$key] == null) ? "" :  $KET[$key];				
                $detail->QTY         = (float) str_replace(',', '', $QTY[$key]);
                $detail->HARGA         = (float) str_replace(',', '', $HARGA[$key]);
                $detail->TOTAL         = (float) str_replace(',', '', $TOTAL[$key]);				
 		
                $detail->save();
            }
        }


        //  ganti 11
        $variablell = DB::select('call jualins(?)', array($no_bukti));

        $no_buktix = $no_bukti;
		
		$jual = Jual::where('NO_BUKTI', $no_buktix )->first();

        DB::SELECT("UPDATE jual, juald
                            SET juald.ID = jual.NO_ID  WHERE jual.NO_BUKTI = juald.NO_BUKTI 
							AND jual.NO_BUKTI='$no_buktix';");
					 
					 
        return redirect('/jual/edit/?idx=' . $jual->NO_ID . '&tipx=edit&flagz=' . $FLAGZ . '&golz=' . $this->GOLZ . '&judul=' . $this->judul . '');

    }



   public function edit( Request $request , Jual $jual)
    {


		$per = session()->get('periode')['bulan'] . '/' . session()->get('periode')['tahun'];
		
				
        $cekperid = DB::SELECT("SELECT POSTED from perid WHERE PERIO='$per'");
        if ($cekperid[0]->POSTED==1)
        {
            return redirect('/jual')
			       ->with('status', 'Maaf Periode sudah ditutup!')
                   ->with(['judul' => $judul, 'flagz' => $FLAGZ, 'golz' => $GOLZ]);
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
                         and GOL ='$this->GOLZ' 
						 and NO_BUKTI = '$buktix'
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
			

		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from jual
		                 where PER ='$per' and FLAG ='$this->FLAGZ'
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
			
		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from jual     
		             where PER ='$per' and FLAG ='$this->FLAGZ'
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
	   
		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from jual    
		                    where PER ='$per' and FLAG ='$this->FLAGZ'
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
		  
    		$bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from jual
						 where PER ='$per' and FLAG ='$this->FLAGZ'
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
			$jual = Jual::where('NO_ID', $idx )->first();	
	     }
		 else
		 {
				$jual = new Jual;
                $jual->TGL = Carbon::now();
      
				
		 }

        $no_bukti = $jual->NO_BUKTI;
	    $jualDetail = DB::table('juald')->where('NO_BUKTI', $no_bukti)->orderBy('REC')->get();	
		
		$data = [
            'header'        => $jual,
            'detail'        => $jualDetail,
			
        ];
 
         
      
         return view('otransaksi_jual.edit', $data)
		 ->with(['tipx' => $tipx, 'idx' => $idx, 'flagz' => $this->FLAGZ, 'golz' => $this->GOLZ, 'judul'=> $this->judul ]);
      	 
 
 
    }

    // ganti 18

    public function update(Request $request, Jual $jual)
    {

        $this->validate(
            $request,
            [

                // ganti 19

                'TGL'      => 'required'


            ]
        );

		$this->setFlag($request);
        $FLAGZ = $this->FLAGZ;
        $GOLZ = $this->GOLZ;
        $judul = $this->judul;
		
        $CBG = Auth::user()->CBG;
		
        // ganti 20
        $variablell = DB::select('call jualdel(?)', array($jual['NO_BUKTI']));

        $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];

        // ganti 20

        $jual->update(
            [
                'TGL'              => date('Y-m-d', strtotime($request['TGL'])),
                'NO_SO'            => ($request['NO_SO'] == null) ? "" : $request['NO_SO'],
                'NO_SURATS'            => ($request['NO_SURATS'] == null) ? "" : $request['NO_SURATS'],
                'NO_SPM'            => ($request['NO_SPM'] == null) ? "" : $request['NO_SPM'],
                'NO_JUAL'            => ($request['NO_JUAL'] == null) ? "" : $request['NO_JUAL'],

                'KODEP'            => ($request['KODEP'] == null) ? "" : $request['KODEP'],
                'NAMAP'            => ($request['NAMAP'] == null) ? "" : $request['NAMAP'],
                
                'KODEC'            => ($request['KODEC'] == null) ? "" : $request['KODEC'],
                'NAMAC'            => ($request['NAMAC'] == null) ? "" : $request['NAMAC'],
                'ALAMAT'           => ($request['ALAMAT'] == null) ? "" : $request['ALAMAT'],
                'KOTA'             => ($request['KOTA'] == null) ? "" : $request['KOTA'],


                'NOTES'            => ($request['NOTES'] == null) ? "" : $request['NOTES'],
                'TOTAL_QTY'        => (float) str_replace(',', '', $request['TTOTAL_QTY']),
                'TOTAL'            => (float) str_replace(',', '', $request['TTOTAL']),
                'PPN'            => (float) str_replace(',', '', $request['PPN']),
                'NETT'            => (float) str_replace(',', '', $request['NETT']),
                'SISA'            => (float) str_replace(',', '', $request['NETT']),


				'USRNM'            => Auth::user()->username,
                'TG_SMP'           => Carbon::now(),
				'updated_by'       => Auth::user()->username,
                'CBG'              => $CBG,
                'FLAG'             => $FLAGZ,						
                'GOL'              => $GOLZ,
            ]
        );


		$no_buktix = $jual->NO_BUKTI;
		
        // Update Detail
        $length = sizeof($request->input('REC'));
        $NO_ID  = $request->input('NO_ID');

        $REC    = $request->input('REC');
        $KD_BHN = $request->input('KD_BHN');
        $NA_BHN = $request->input('NA_BHN');
        $KD_BRG = $request->input('KD_BRG');
        $NA_BRG = $request->input('NA_BRG');
        $SATUAN = $request->input('SATUAN');		
        $KET = $request->input('KET');		
        $QTY    = $request->input('QTY');
        $HARGA    = $request->input('HARGA');
        $PPNX    = $request->input('PPNX');
        $DPP    = $request->input('DPP');
        $TOTAL    = $request->input('TOTAL');	

        $query = DB::table('juald')->where('NO_BUKTI', $request->NO_BUKTI)->whereNotIn('NO_ID',  $NO_ID)->delete();

        // Update / Insert
        for ($i = 0; $i < $length; $i++) {
            // Insert jika NO_ID baru
            if ($NO_ID[$i] == 'new') {
                $insert = JualDetail::create(
                    [
                        'NO_BUKTI'   => $request->NO_BUKTI,
                        'REC'        => $REC[$i],
                        'PER'        => $periode,
                        'FLAG'       => $this->FLAGZ,
                        'GOL'        => $this->GOLZ,
                        'KD_BHN'     => ($KD_BHN[$i] == null) ? "" :  $KD_BHN[$i],
                        'NA_BHN'     => ($NA_BHN[$i] == null) ? "" :  $NA_BHN[$i],
                        'KD_BRG'     => ($KD_BRG[$i] == null) ? "" :  $KD_BRG[$i],
                        'NA_BRG'     => ($NA_BRG[$i] == null) ? "" :  $NA_BRG[$i],
                        'SATUAN'     => ($SATUAN[$i] == null) ? "" :  $SATUAN[$i],						
                        'KET'     => ($KET[$i] == null) ? "" :  $KET[$i],						
                        'QTY'        => (float) str_replace(',', '', $QTY[$i]),
                        'HARGA'        => (float) str_replace(',', '', $HARGA[$i]),

                        'TOTAL'        => (float) str_replace(',', '', $TOTAL[$i]),
						
                    ]
                );
            } else {
                // Update jika NO_ID sudah ada
                $upsert = JualDetail::updateOrCreate(
                    [
                        'NO_BUKTI'  => $request->NO_BUKTI,
                        'NO_ID'     => (int) str_replace(',', '', $NO_ID[$i])
                    ],

                    [
                        'REC'        => $REC[$i],
                      
                        'KD_BHN'     => ($KD_BHN[$i] == null) ? "" :  $KD_BHN[$i],
                        'NA_BHN'     => ($NA_BHN[$i] == null) ? "" :  $NA_BHN[$i],
                        'KD_BRG'     => ($KD_BRG[$i] == null) ? "" :  $KD_BRG[$i],
                        'NA_BRG'     => ($NA_BRG[$i] == null) ? "" :  $NA_BRG[$i],
                        'SATUAN'     => ($SATUAN[$i] == null) ? "" :  $SATUAN[$i],						
                        'KET'        => ($KET[$i] == null) ? "" :  $KET[$i],						
                        'QTY'        => (float) str_replace(',', '', $QTY[$i]),
                        'HARGA'      => (float) str_replace(',', '', $HARGA[$i]),
                        'TOTAL'      => (float) str_replace(',', '', $TOTAL[$i]),

                        'FLAG'       => $this->FLAGZ,
                        'GOL'        => $this->GOLZ,
                        'PER'        => $periode,
                    ]
                );
            }
        }


        //  ganti 21
        $variablell = DB::select('call jualins(?)', array($jual['NO_BUKTI']));

 		$jual = Jual::where('NO_BUKTI', $no_buktix )->first();

        $no_bukti = $jual->NO_BUKTI;

         DB::SELECT("UPDATE jual,  juald
                     SET  juald.ID =  jual.NO_ID  WHERE  jual.NO_BUKTI =  juald.NO_BUKTI 
                     AND  jual.NO_BUKTI='$no_bukti';");
					 
        return redirect('/jual/edit/?idx=' . $jual->NO_ID . '&tipx=edit&flagz=' . $this->FLAGZ . '&judul=' . $this->judul . '&golz=' . $this->GOLZ . '');	
	
	
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Resbelinse
     */

    // ganti 22

    public function destroy(Request $request, Jual $jual)
    {

		$this->setFlag($request);
        $FLAGZ = $this->FLAGZ;
        $GOLZ = $this->GOLZ;
        $judul = $this->judul;
		
		$per = session()->get('periode')['bulan'] . '/' . session()->get('periode')['tahun'];
        $cekperid = DB::SELECT("SELECT POSTED AS POSTED from perid WHERE PERIO='$per'");
        if ($cekperid[0]->POSTED==1)
        {
            return redirect()->route('jual')
                ->with('status', 'Maaf Periode sudah ditutup!')
                ->with(['judul' => $this->judul, 'flagz' => $this->FLAGZ, 'golz' => $this->GOLZ]);
        }
		
       $variablell = DB::select('call jualdel(?)', array($jual['NO_BUKTI']));//


        // ganti 23
		
        $deleteJual = Jual::find($jual->NO_ID);

        // ganti 24

        $deleteJual->delete();

        // ganti 
       return redirect('/jual?flagz='.$FLAGZ.'&golz='.$GOLZ)->with(['judul' => $judul, 'flagz' => $FLAGZ, 'golz' => $GOLZ ])->with('statusHapus', 'Data '.$jual->NO_BUKTI.' berhasil dihapus');


    }
    
    public function cetak(Jual $jual)
    {
        $no_jual = $jual->NO_BUKTI;
		
        $file     = 'jualc';
        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

        $query = DB::SELECT("
            SELECT jual.NO_BUKTI, jual.TGL, juald.KD_BRG, juald.NA_BRG,  jual.TGL, jual.USRNM, jual.PPN, jual.NETT,
			juald.SATUAN, juald.QTY  
			from jual, juald 
			WHERE jual.NO_BUKTI=juald.NO_BUKTI and jual.NO_BUKTI='$no_jual'
			ORDER BY jual.NO_BUKTI;
		");

        $data = [];

        $rec=1;
        $kdbrg = '';
        $nabrg = '';
        foreach ($query as $key => $value) {

            array_push($data, array(
                'NO_BUKTI' => $no_jual,
                // 'TGL'      => date("d/m/Y", strtotime($jual->TGL)),
                'TGL'      => $query[$key]->TGL,               
                'REC'      => $rec,
                'KD_BRG'   => $query[$key]->KD_BRG,
                'NA_BRG'   => $query[$key]->NA_BRG,
                'QTY'      => $query[$key]->QTY,				
				'SATUAN'    => $query[$key]->SATUAN,
		    	'USRNM'    => $query[$key]->USRNM,



            ));
            $rec++;
        }

        DB::SELECT("UPDATE JUAL SET POSTED=1 WHERE NO_BUKTI='$no_jual'");
	
        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I");
    }
	
	
	
	 
	
	
	
	
	
	
}
