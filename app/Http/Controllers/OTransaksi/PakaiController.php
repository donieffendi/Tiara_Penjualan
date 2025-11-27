<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
// ganti 1

use App\Models\OTransaksi\Pakai;
use App\Models\OTransaksi\PakaiDetail;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use DB;
use Carbon\Carbon;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";
use PHPJasperXML;

// ganti 2
class PakaiController extends Controller
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
        if ( $request->flagz == 'PK' && $request->golz == 'J' ) {
            $this->judul = "Pemakaian";
        } else if ( $request->flagz == '' && $request->golz == '' ) {
            $this->judul = "";
        }
		
        $this->FLAGZ = $request->flagz;
        $this->GOLZ = $request->golz;


    }
       	
    public function index(Request $request)
    {

	    $this->setFlag($request);
        // ganti 3
        return view('otransaksi_pakai.index')->with(['judul' => $this->judul, 'flagz' => $this->FLAGZ, 'golz' => $this->GOLZ ]);
	
	
    }
	


	public function index_posting(Request $request)
    {
 
        return view('otransaksi_pakai.post');
    }

    public function cekOrderkWIP(Request $request)
    {
		$orderWIP = DB::SELECT("SELECT NO_BUKTI FROM orderk WHERE NO_SO='".$request->no_so."' and FLAG='OW' and FIN=0;");
		return response()->json($orderWIP);
    }
	
    public function browseXd(Request $request)
    {
		$xd = DB::SELECT("SELECT KD_PRS, NA_PRS, NO_PRS from orderkxd WHERE NO_BUKTI='".$request->nobukti."'  order by AKHIR,NO_PRS;");
		return response()->json($xd);
	}

    public function browseOk(Request $request)
    {
        $flag = $request->FLAG;

        $filterqty = "a.QTY-a.PROSES";

        // if($request->flag=="PW")
        // {
        //     $flag = "OW";
        //     $filterqty = "a.QTY_BHN-a.PROSES";
        // }

		$ok = DB::SELECT("SELECT 'A' JNS, a.NO_BUKTI, a.KD_BRG, a.NA_BRG, a.KD_BHN, a.NA_BHN, a.SATUAN, 'AWAL' KD_PRSX, 'AWAL' NA_PRSX, 
        b.NO_PRS, b.KD_PRS, b.NA_PRS, b.MASUK, b.KELUAR, b.LAIN, b.SISA, $filterqty AS PROSES,
        b.NO_ID ID_ODR, a.NO_ID ID_ODRX, A.ID_SOD, a.NO_SO, a.NO_FO 
        FROM orderk a, orderkxd b 
        where a.NO_BUKTI=b.NO_BUKTI 
        /*and b.NO_PRS<=1 and b.AKHIR=1*/
        AND $filterqty>0 
        -- AND a.FLAG='$flag'
        union ALL 
        SELECT 'B' JNS, a.NO_BUKTI, c.KD_BRG, c.NA_BRG, c.KD_BHN, c.NA_BHN, c.SATUAN, a.KD_PRS KD_PRSX, a.NA_PRS NA_PRSX, 
        b.NO_PRS, b.KD_PRS, b.NA_PRS, b.MASUK, b.KELUAR, b.LAIN, b.SISA, a.KELUAR-a.PROSES PROSES, 
        b.NO_ID ID_ODR, a.NO_ID ID_ODRX, c.ID_SOD, c.NO_SO, c.NO_FO 
        FROM orderkxd a, orderkxd b, orderk c 
        where a.NO_BUKTI=b.NO_BUKTI and a.NO_BUKTI=c.NO_BUKTI /*and b.NO_PRS>1 and a.NO_PRS-B.NO_PRS=-1 and b.AKHIR=1*/ AND a.KELUAR-a.PROSES>0 AND a.FLAG='$flag';");
		return response()->json($ok);
	}

    public function browsePrs(Request $request)
    {
		$prs = DB::SELECT("SELECT KD_PRS, NA_PRS from prs order by KD_PRS;");
		return response()->json($prs);
	}

    public function browseBhn(Request $request)
    {
		$bhn = DB::SELECT("SELECT KD_BHN, NA_BHN, JENIS, SATUAN from bhn order by KD_BHN;");
		return response()->json($bhn);
	}
	
    // ganti 4

    public function getPakai(Request $request)
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
		
        $pakai = DB::SELECT("SELECT * from pakai  where PER = '$periode' and FLAG ='$this->FLAGZ' AND GOL ='$this->GOLZ' 
                            AND CBG = '$CBG' ORDER BY NO_BUKTI ");
	   
        // ganti 6

        return Datatables::of($pakai)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                if ( Auth::user()->divisi=="programmer" ) 
				{
                    //CEK POSTED di index dan edit
                    $btnEdit =   ($row->POSTED == 1) ? ' onclick= "alert(\'Transaksi ' . $row->NO_BUKTI . ' sudah diposting!\')" href="#" ' : ' href="pakai/edit/?idx=' . $row->NO_ID . '&tipx=edit&flagz=' . $row->FLAG . '&judul=' . $this->judul . '&golz=' . $row->GOL . '"';					
                    $btnDelete = ($row->POSTED == 1) ? ' onclick= "alert(\'Transaksi ' . $row->NO_BUKTI . ' sudah diposting!\')" href="#" ' : ' onclick="return confirm(&quot; Apakah anda yakin ingin hapus? &quot;)" href="pakai/delete/' . $row->NO_ID . '/?flagz=' . $row->FLAG . '&golz=' . $row->GOL . '" ';


                    $btnPrivilege =
                        '
                                <a class="dropdown-item" ' . $btnEdit . '>
                                <i class="fas fa-edit"></i>
                                    Edit
                                </a>
                                <a class="dropdown-item btn btn-danger" href="cetak/' . $row->NO_ID . '">
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

        $query = DB::table('pakai')->select('NO_BUKTI')->where('PER', $periode)->where('FLAG', $FLAGZ )
                    ->where('CBG', $CBG )->orderByDesc('NO_BUKTI')->limit(1)->get();

        if ($query != '[]')
        {
            $query = substr($query[0]->NO_BUKTI, -4);
            $query = str_pad($query + 1, 4, 0, STR_PAD_LEFT);
            $no_bukti = 'PK'. $CBG . $tahun . $bulan . '-' . $query;
        } else {
            $no_bukti = 'PK'. $CBG . $tahun . $bulan . '-0001';
        }
		
//////////////////////////////////////////////////////////////////////////
       

        // Insert Header

        // ganti 10

        $pakai = Pakai::create(
            [
                'NO_BUKTI'         => $no_bukti,
                'TGL'              => date('Y-m-d', strtotime($request['TGL'])),
                'PER'              => $periode,
                'FLAG'             => $FLAGZ,						
                'GOL'              => $GOLZ,		
                'CBG'              => $CBG,		

                'NO_ORDER'         => ($request['NO_OK']==null) ? "" : $request['NO_OK'],
                'NO_SO'            => ($request['NO_SO']==null) ? "" : $request['NO_SO'],
                'KD_BRG'           => ($request['KD_BRG']==null) ? "" : $request['KD_BRG'],
                'NA_BRG'           => ($request['NA_BRG']==null) ? "" : $request['NA_BRG'],
                'KD_BHN'           => ($request['KD_BHN_H']==null) ? "" : $request['KD_BHN_H'],
                'NA_BHN'           => ($request['NA_BHN_H']==null) ? "" : $request['NA_BHN_H'],
                'QTY_IN'           => (float) str_replace(',', '', $request['QTYI']),
                'QTY_OUT'          => (float) str_replace(',', '', $request['QTYO']),
                'TOTAL_QTY'        => (float) str_replace(',', '', $request['TTOTAL_QTY']),
                'SATUAN'           => ($request['SATUANH']==null) ? "" : $request['SATUANH'],
                'NO_FO'            => ($request['NO_FO']==null) ? "" : $request['NO_FO'],
                'KD_PRS'           => ($request['KD_PRSH']==null) ? "" : $request['KD_PRSH'],			
                'NA_PRS'           => ($request['NA_PRSH']==null) ? "" : $request['NA_PRSH'],
				'NO_PRS'           => ($request['NO_PRS']==null) ? "" : $request['NO_PRS'],
				'NOTES'            => ($request['NOTES']==null) ? "" : $request['NOTES'],
       
	   
                'USRNM'            => Auth::user()->username,
                'TG_SMP'           => Carbon::now(),
				'created_by'       => Auth::user()->username,
            ]
        );


		$REC        = $request->input('REC');
		$KD_BHN	= $request->input('KD_BHN');
		$NA_BHN	= $request->input('NA_BHN');
		$SATUAN	= $request->input('SATUAN');
		$QTY	= $request->input('QTY');
		$QTYX	= $request->input('QTYX');
		$KET	= $request->input('KET');		 

        // Check jika value detail ada/tidak
        if ($REC) {
            foreach ($REC as $key => $value) {
                // Declare new data di Model
                $detail    = new PakaiDetail;

                // Insert ke Database
                $detail->NO_BUKTI    = $no_bukti;
                $detail->REC         = $REC[$key];
                $detail->PER         = $periode;
                $detail->FLAG        = $FLAGZ;		
                $detail->GOL 	     = $GOLZ;   

                $detail->KD_BHN	    = $KD_BHN[$key];
				$detail->NA_BHN	    = $NA_BHN[$key];	
				$detail->SATUAN	    = ($SATUAN[$key]==null) ? '' : $SATUAN[$key];	
				$detail->QTY	    = (float) str_replace(',', '', $QTY[$key]);
				$detail->QTYX	    = (float) str_replace(',', '', $QTYX[$key]);
				$detail->KET	    = ($KET[$key]==null) ? '' : $KET[$key];				
 		
                $detail->save();
            }
        }


        //  ganti 11
    //    $variablell = DB::select('call pakains(?)', array($no_bukti));

        $no_buktix = $no_bukti;
		
		$pakai = Pakai::where('NO_BUKTI', $no_buktix )->first();


        DB::SELECT("UPDATE pakai, pakaid
                            SET pakaid.ID = pakai.NO_ID  WHERE pakai.NO_BUKTI = pakaid.NO_BUKTI 
							AND pakai.NO_BUKTI='$no_buktix';");
					 
					 
        return redirect('/pakai/edit/?idx=' . $pakai->NO_ID . '&tipx=edit&flagz=' . $FLAGZ . '&golz=' . $this->GOLZ . '&judul=' . $this->judul . '');

    }



   public function edit( Request $request , Pakai $pakai)
    {


		$per = session()->get('periode')['bulan'] . '/' . session()->get('periode')['tahun'];
		
				
        $cekperid = DB::SELECT("SELECT POSTED from perid WHERE PERIO='$per'");
        if ($cekperid[0]->POSTED==1)
        {
            return redirect('/pakai')
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
		   
		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from pakai
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
			

		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from pakai
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
			
		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from pakai     
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
	   
		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from pakai    
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
		  
    		$bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from pakai
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
			$pakai = Pakai::where('NO_ID', $idx )->first();	
	     }
		 else
		 {
				$pakai = new Pakai;
                $pakai->TGL = Carbon::now();
      
				
		 }

        $no_bukti = $pakai->NO_BUKTI;
	    $pakaiDetail = DB::table('pakaid')->where('NO_BUKTI', $no_bukti)->orderBy('REC')->get();	
		
		$data = [
            'header'        => $pakai,
            'detail'        => $pakaiDetail,
			
        ];
 
         
      
         return view('otransaksi_pakai.edit', $data)
		 ->with(['tipx' => $tipx, 'idx' => $idx, 'flagz' => $this->FLAGZ, 'golz' => $this->GOLZ, 'judul'=> $this->judul ]);
      	 
 
 
    }

    // ganti 18

    public function update(Request $request, Pakai $pakai)
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
    //   $variablell = DB::select('call pakaidel(?)', array($pakai['NO_BUKTI']));

        $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];

        // ganti 20

        $pakai->update(
            [
                'TGL'              => date('Y-m-d', strtotime($request['TGL'])),
                
                'NO_ORDER'         => ($request['NO_OK']==null) ? "" : $request['NO_OK'],
                'NO_SO'            => ($request['NO_SO']==null) ? "" : $request['NO_SO'],
                'KD_BRG'           => ($request['KD_BRG']==null) ? "" : $request['KD_BRG'],
                'NA_BRG'           => ($request['NA_BRG']==null) ? "" : $request['NA_BRG'],
                'KD_BHN'           => ($request['KD_BHN_H']==null) ? "" : $request['KD_BHN_H'],
                'NA_BHN'           => ($request['NA_BHN_H']==null) ? "" : $request['NA_BHN_H'],
                'QTY_IN'           => (float) str_replace(',', '', $request['QTYI']),
                'QTY_OUT'          => (float) str_replace(',', '', $request['QTYO']),
                'TOTAL_QTY'        => (float) str_replace(',', '', $request['TTOTAL_QTY']),
                'SATUAN'           => ($request['SATUANH']==null) ? "" : $request['SATUANH'],
                'NO_FO'            => ($request['NO_FO']==null) ? "" : $request['NO_FO'],
                'KD_PRS'            => ($request['KD_PRSH']==null) ? "" : $request['KD_PRSH'],			
                'NA_PRS'            => ($request['NA_PRSH']==null) ? "" : $request['NA_PRSH'],
				'NO_PRS'           => ($request['NO_PRS']==null) ? "" : $request['NO_PRS'],
				'NOTES'            => ($request['NOTES']==null) ? "" : $request['NOTES'],


				'USRNM'            => Auth::user()->username,
                'TG_SMP'           => Carbon::now(),
				'updated_by'       => Auth::user()->username,
                'FLAG'             => $FLAGZ,						
                'GOL'              => $GOLZ,
                'CBG'              => $CBG,
            ]
        );


		$no_buktix = $pakai->NO_BUKTI;
		
        // Update Detail
        $length = sizeof($request->input('REC'));
        $NO_ID  = $request->input('NO_ID');

        $REC    = $request->input('REC');
        $KD_BHN	= $request->input('KD_BHN');
		$NA_BHN	= $request->input('NA_BHN');
		$SATUAN	= $request->input('SATUAN');
		$QTY	= $request->input('QTY');
		$QTYX	= $request->input('QTYX');
		$KET	= $request->input('KET');

        $query = DB::table('pakaid')->where('NO_BUKTI', $request->NO_BUKTI)->whereNotIn('NO_ID',  $NO_ID)->delete();

        // Update / Insert
        for ($i = 0; $i < $length; $i++) {
            // Insert jika NO_ID baru
            if ($NO_ID[$i] == 'new') {
                $insert = PakaiDetail::create(
                    [
                        'NO_BUKTI'   => $request->NO_BUKTI,
                        'REC'        => $REC[$i],
                        'PER'        => $periode,
                        'FLAG'       => $this->FLAGZ,
                        'GOL'        => $this->GOLZ,
                        'KD_BHN'     => ($KD_BHN[$i]==null) ? "" :  $KD_BHN[$i],
                        'NA_BHN'     => ($NA_BHN[$i]==null) ? "" : $NA_BHN[$i],	
                        'SATUAN'     => ($SATUAN[$i]==null) ? "" : $SATUAN[$i],
						'QTY'        => (float) str_replace(',', '', $QTY[$i]),
						'QTYX'       => (float) str_replace(',', '', $QTYX[$i]),
						'KET'        => ($KET[$i]==null) ? "" : $KET[$i],
						
                    ]
                );
            } else {
                // Update jika NO_ID sudah ada
                $upsert = PakaiDetail::updateOrCreate(
                    [
                        'NO_BUKTI'  => $request->NO_BUKTI,
                        'NO_ID'     => (int) str_replace(',', '', $NO_ID[$i])
                    ],

                    [
                        'REC'        => $REC[$i],
                      
                        'KD_BHN'     => ($KD_BHN[$i]==null) ? "" :  $KD_BHN[$i],
                        'NA_BHN'     => ($NA_BHN[$i]==null) ? "" : $NA_BHN[$i],	
                        'SATUAN'     => ($SATUAN[$i]==null) ? "" : $SATUAN[$i],
						'QTY'        => (float) str_replace(',', '', $QTY[$i]),
						'QTYX'        => (float) str_replace(',', '', $QTYX[$i]),
						'KET'        => ($KET[$i]==null) ? "" : $KET[$i],

                        'FLAG'       => $this->FLAGZ,
                        'GOL'        => $this->GOLZ,
                        'PER'        => $periode,
                    ]
                );
            }
        }


        //  ganti 21
        // $variablell = DB::select('call pakains(?)', array($pakai['NO_BUKTI']));

 		$pakai = Pakai::where('NO_BUKTI', $no_buktix )->first();

        $no_bukti = $pakai->NO_BUKTI;

         DB::SELECT("UPDATE pakai,  pakaid
                     SET  pakaid.ID =  pakai.NO_ID  WHERE  pakai.NO_BUKTI =  pakaid.NO_BUKTI 
                     AND  pakai.NO_BUKTI='$no_bukti';");
					 
        return redirect('/pakai/edit/?idx=' . $pakai->NO_ID . '&tipx=edit&flagz=' . $this->FLAGZ . '&judul=' . $this->judul . '&golz=' . $this->GOLZ . '');	
	
	
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Resbelinse
     */

    // ganti 22

    public function destroy(Request $request, Pakai $pakai)
    {

		$this->setFlag($request);
        $FLAGZ = $this->FLAGZ;
        $GOLZ = $this->GOLZ;
        $judul = $this->judul;
		
		$per = session()->get('periode')['bulan'] . '/' . session()->get('periode')['tahun'];
        $cekperid = DB::SELECT("SELECT POSTED AS POSTED from perid WHERE PERIO='$per'");
        if ($cekperid[0]->POSTED==1)
        {
            return redirect()->route('pakai')
                ->with('status', 'Maaf Periode sudah ditutup!')
                ->with(['judul' => $this->judul, 'flagz' => $this->FLAGZ, 'golz' => $this->GOLZ]);
        }
		
    //    $variablell = DB::select('call pakaidel(?)', array($pakai['NO_BUKTI']));//


        // ganti 23
		
        $deletePakai = Pakai::find($pakai->NO_ID);

        // ganti 24

        $deletePakai->delete();

        // ganti 
       return redirect('/pakai?flagz='.$FLAGZ.'&golz='.$GOLZ)->with(['judul' => $judul, 'flagz' => $FLAGZ, 'golz' => $GOLZ ])->with('statusHapus', 'Data '.$pakai->NO_BUKTI.' berhasil dihapus');


    }
    
    public function cetak(Pakai $pakai)
    {
        $no_pakai = $pakai->NO_BUKTI;
		
        $file     = 'pakaic';
        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

        $query = DB::SELECT("
            SELECT pakai.NO_BUKTI, pakai.TGL, pakaid.KD_BRG, pakaid.NA_BRG,  pakai.TGL, pakai.USRNM, pakai.PPN, pakai.NETT,
			pakaid.SATUAN, pakaid.QTY,  
			from pakai, pakaid 
			WHERE pakai.NO_BUKTI=pakaid.NO_BUKTI and pakai.NO_BUKTI='$no_pakai'
			ORDER BY pakai.NO_BUKTI;
		");

        $data = [];

        $rec=1;
        $kdbrg = '';
        $nabrg = '';
        foreach ($query as $key => $value) {

            array_push($data, array(
                'NO_BUKTI' => $no_pakai,
                // 'TGL'      => date("d/m/Y", strtotime($pakai->TGL)),
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
	
        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I");
    }
	
	
	
	 
	
	
	
	
	
	
}
