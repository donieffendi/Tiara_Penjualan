<?php

namespace App\Http\Controllers\OLain;

use App\Http\Controllers\Controller;
// ganti 1

use App\Models\OLain\Ubahsus;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use DB;
use Carbon\Carbon;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";
use PHPJasperXML;

// ganti 2
class UbahsusController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
		
    public function index(Request $request)
    {
        // ganti 3
        return view('olain_ubahsus.index');
    }

    // ganti 4
    public function ambilDetail(Request $request)
    {
        $SUB1 = $request->SUB1;
        $SUB2 = $request->SUB2;

        $result = DB::SELECT("SELECT a.KD_BRG, a.NA_BRG, a.KET_KEM, a.KET_UK, b.LPH, b.KLK, a.DTR AS DTR_ORI, b.DTR AS DTR_LAMA,
                                    c.PANJANG, c.LEBAR, c.TINGGI, c.PANJANG_SHELF, c.SUSUN, c.MUKA, c.DTR_1M, c.DTR_MANUAL,
                                    c.DTR2, b.SRMIN AS SMIN, b.SRMAX AS SMAX 
                                FROM brg a, brgdt b, brg_dc_ts c
                                WHERE a.KD_BRG=b.KD_BRG 
                                AND a.KD_BRG=c.KD_BRG
                                AND a.SUB BETWEEN '$SUB1' AND '$SUB2'
                                AND a.DTR <> 0
                                ORDER BY a.KD_BRG");

        return response()->json($result);
    }

    public function getUbahsus(Request $request)
    {
        // ganti 5

       if ($request->session()->has('periode')) {
            $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
        } else {
            $periode = '';
        }

        $cbg = Auth::user()->CBG;
	
       $ubahsus = DB::SELECT("SELECT NO_ID, NO_BUKTI, TG_SMP, USRNM, POSTED, CBG
                                from usul_susun_dcts
                                where CBG='$cbg' and NO_BUKTI<>'+' and left(NO_BUKTI,2)='US'
                                    and PER='$periode'
                                GROUP BY NO_BUKTI
                                order by NO_BUKTI DESC");
	  
	   
        // ganti 6

        return Datatables::of($ubahsus)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                if (Auth::user()->divisi=="programmer" ) 
				{
                    // url untuk delete di index
                    $url = "'".url("ubahsus/delete/" . $row->NO_BUKTI )."'";
                    // batas

                    //CEK POSTED di index dan edit

                    $btnDelete = ' onclick="deleteRow('.$url.')"';

                    // $btnEdit =   ($row->POSTED == 1) ? ' onclick= "alert(\'Transaksi ' . $row->NO_BUKTI . ' sudah diposting!\')" href="#" ' : ' href="ubahsus/edit/?idx=' . $row->NO_ID . '&tipx=edit&flagz=' . $row->FLAG . '&judul=' . $this->judul . '&golz=' . $row->GOL . '"';					
                    // $btnDelete = ($row->POSTED == 1) ? ' onclick= "alert(\'Transaksi ' . $row->NO_BUKTI . ' sudah diposting!\')" href="#" ' : ' onclick="return confirm(&quot; Apakah anda yakin ingin hapus? &quot;)" href="ubahsus/delete/' . $row->NO_ID . '/?flagz=' . $row->FLAG . '&golz=' . $row->GOL .'" ';


                    $btnPrivilege =
                        '
                                <a class="dropdown-item" href="ubahsus/edit/?idx=' . $row->NO_ID . '&tipx=edit&buktix=' .$row->NO_BUKTI.'";> <i class="fas fa-edit"></i>
                                        Edit
                                    </a>
                                <a hidden class="dropdown-item btn btn-danger" href="jsubahsus_nonc/' . $row->NO_ID . '">
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
			
            ->rawColumns(['action'])
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
                // 'TGL'      => 'required',
            ]
        );

        //////     nomer otomatis
		
        $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];

        $bulan    = session()->get('periode')['bulan'];
        $tahun    = substr(session()->get('periode')['tahun'], -2);

        $CBG = Auth::user()->CBG;

        $cbgSuffix = [
            'TGZ' => 'Z',
            'SOP' => 'S',
            'TMM' => 'MM',
        ];

        $suffix = $cbgSuffix[$CBG] ?? '';

        $query = DB::table('usul_susun_dcts')->select('NO_BUKTI')->where('PER', $periode)->orderByDesc('NO_BUKTI')->limit(1)->get();

        if ($query->isNotEmpty()) {
            $last = $query->first()->NO_BUKTI;

            preg_match('/-(\d{4})/', $last, $m);

            $urut = isset($m[1]) ? ((int)$m[1] + 1) : 1;

            $query = str_pad($urut, 4, '0', STR_PAD_LEFT);
            $no_bukti = 'US' . $tahun . $bulan . '-' . $query . $suffix;
        } else {
            $no_bukti = 'US' . $tahun . $bulan . '-0001' . $suffix;
        }

        //////////////////////////////////////////////////////////////////////////
        $REC                = $request->input('REC');
		$KD_BRG	            = $request->input('KD_BRG');
		$NA_BRG	            = $request->input('NA_BRG');
		$KET_UK	            = $request->input('KET_UK');
		$KET_KEM	        = $request->input('KET_KEM');
		$PANJANG	        = $request->input('PANJANG');
		$LEBAR	            = $request->input('LEBAR');
		$TINGGI	            = $request->input('TINGGI');
		$PANJANG_SHELF	    = $request->input('PANJANG_SHELF');
		$SUSUN	            = $request->input('SUSUN');
        $MUKA	            = $request->input('MUKA');
        $DTR_1M	            = $request->input('DTR_1M');
        $DTR_MANUAL	        = $request->input('DTR_MANUAL');
        $KLK	            = $request->input('KLK');
        $LPH	            = $request->input('LPH');
        $KAPRAK	            = $request->input('KAPRAK');
        $PERLU              = $request->input('PERLU');
        $PERLUB             = $request->input('PERLUB');
        $DTR_ORI	        = $request->input('DTR_ORI');
        $DTR_LAMA	        = $request->input('DTR_LAMA');
        $DTR                = $request->input('DTR');
        $DTR2	            = $request->input('DTR2');
        $SMIN               = $request->input('SMIN');
        $SMAX               = $request->input('SMAX');

            if ($REC) {
                foreach ($REC as $key => $value) {

                    $ubahsus = Ubahsus::create(
                        [
                            'NO_BUKTI'         => $no_bukti,
                            'TG_SMP'           => Carbon::now(),
                            'PER'              => $periode,
                            'POSTED'           => isset($request['POSTED']) ? 1 : 0,
                            'NOTES'            => ($request['NOTES'] == null) ? "" : $request['NOTES'],
                            'KD_BRG'           => ($KD_BRG[$key]==null) ? "" :  $KD_BRG[$key],
                            'NA_BRG'           => ($NA_BRG[$key]==null) ? "" :  $NA_BRG[$key],
                            'KET_UK'           => ($KET_UK[$key]==null) ? "" :  $KET_UK[$key],
                            'KET_KEM'          => ($KET_KEM[$key]==null) ? "" :  $KET_KEM[$key],
                            'PANJANG'          => (float) str_replace(',', '', $PANJANG[$key]),
                            'LEBAR'            => (float) str_replace(',', '', $LEBAR[$key]),
                            'TINGGI'           => (float) str_replace(',', '', $TINGGI[$key]),
                            'PANJANG_SHELF'    => (float) str_replace(',', '', $PANJANG_SHELF[$key]),
                            'SUSUN'            => (float) str_replace(',', '', $SUSUN[$key]),
                            'MUKA'             => (float) str_replace(',', '', $MUKA[$key]),
                            'DTR_1M'           => (float) str_replace(',', '', $DTR_1M[$key]),
                            'DTR_MANUAL'       => (float) str_replace(',', '', $DTR_MANUAL[$key]),
                            'KLK'              => ($KLK[$key]==null) ? "" :  $KLK[$key],
                            'LPH'              => (float) str_replace(',', '', $LPH[$key]),
                            'KAPRAK'           => (float) str_replace(',', '', $KAPRAK[$key]),
                            'PERLU'            => (float) str_replace(',', '', $PERLU[$key]),
                            'PERLUB'           => (float) str_replace(',', '', $PERLUB[$key]),
                            'DTR_ORI'          => (float) str_replace(',', '', $DTR_ORI[$key]),
                            'DTR_LAMA'         => (float) str_replace(',', '', $DTR_LAMA[$key]),
                            'DTR'              => (float) str_replace(',', '', $DTR[$key]),
                            'DTR2'             => (float) str_replace(',', '', $DTR2[$key]),
                            'SMIN'             => (float) str_replace(',', '', $SMIN[$key]),
                            'SMAX'             => (float) str_replace(',', '', $SMAX[$key]),
                            'USRNM'            => Auth::user()->username,
                            'TGL_POSTED'       => isset($request['POSTED']) ? Carbon::now() : null,
                            'USRNM_POSTED'     => isset($request['POSTED']) ? Auth::user()->username : null,
                            'CBG'              => Auth::user()->CBG
                        ]
                    );	
                }
            }
        // return redirect('/ubahsus/edit/?idx=' . $ubahsus->NO_ID . '&tipx=edit&flagz=' . $FLAGZ . '&judul=' . $this->judul . '&golz=' . $this->GOLZ . '');
        return redirect('/ubahsus')->with('statusInsert', 'Data baru berhasil ditambahkan');		
    }


    // ganti 15

   
   public function edit( Request $request , Ubahsus $ubahsus)
    {


		$per = session()->get('periode')['bulan'] . '/' . session()->get('periode')['tahun'];
		
				
        // $cekperid = DB::SELECT("SELECT POSTED from perid WHERE PERIO='$per'");
        // if ($cekperid[0]->POSTED==1)
        // {
        //     return redirect('/beliz')
		// 	       ->with('status', 'Maaf Periode sudah ditutup!')
        //            ->with(['judul' => $judul, 'flagz' => $FLAGZ, 'golz' => $GOLZ]);
        // }
		
        $tipx = $request->tipx;

		$idx = $request->idx;
			

		
		if ( $idx =='0' && $tipx=='undo'  )
	    {
			$tipx ='top';
			
		   }
		   
		 
		   
		if ($tipx=='search') {
			
		   	
    	   $buktix = $request->buktix;
		   
		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from usul_susun_dcts
		                 where PER ='$per'  
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
			

		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from usul_susun_dcts 
		                 where PER ='$per' 
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
			
		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from usul_susun_dcts     
		             where PER ='$per' 
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
	   
		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from usul_susun_dcts    
		            where PER ='$per'  
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
		  
    		$bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from usul_susun_dcts
						where PER ='$per'
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
            $buktix = $request->buktix;
			$ubahsus = Ubahsus::where('NO_BUKTI', $buktix )->get();	
	     }
		 else
		 {
				$ubahsus = [];
		 }

		$data = [
            'header'        => $ubahsus,
            'tipx'          => $tipx,
            'idx'           => $idx
        ];
        
        // dd($ubahsus);
         
         return view('olain_ubahsus.edit', $data);
		//  ->with(['tipx' => $tipx, 'idx' => $idx, 'flagz' =>$this->FLAGZ, 'judul' => $this->judul, 'golz' =>$this->GOLZ ]);
      
    }



    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Resbeliznse
     */

    // ganti 18

    public function update(Request $request, Ubahsus $ubahsus)
    {

        $this->validate(
            $request,
            [
                // 'TGL'      => 'required',
            ]
        );
        // $variablell = DB::select('call belizdel(?)', array($beliz['NO_BUKTI']));
        
        $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];

		$length = sizeof($request->input('REC'));
        $NO_ID  = $request->input('NO_ID');

        $REC                = $request->input('REC');
		$KD_BRG	            = $request->input('KD_BRG');
		$NA_BRG	            = $request->input('NA_BRG');
		$KET_UK	            = $request->input('KET_UK');
		$KET_KEM	        = $request->input('KET_KEM');
		$PANJANG	        = $request->input('PANJANG');
		$LEBAR	            = $request->input('LEBAR');
		$TINGGI	            = $request->input('TINGGI');
		$PANJANG_SHELF	    = $request->input('PANJANG_SHELF');
		$SUSUN	            = $request->input('SUSUN');
        $MUKA	            = $request->input('MUKA');
        $DTR_1M	            = $request->input('DTR_1M');
        $DTR_MANUAL	        = $request->input('DTR_MANUAL');
        $KLK	            = $request->input('KLK');
        $LPH	            = $request->input('LPH');
        $KAPRAK	            = $request->input('KAPRAK');
        $PERLU              = $request->input('PERLU');
        $PERLUB             = $request->input('PERLUB');
        $DTR_ORI	        = $request->input('DTR_ORI');
        $DTR_LAMA	        = $request->input('DTR_LAMA');
        $DTR                = $request->input('DTR');
        $DTR2	            = $request->input('DTR2');
        $SMIN               = $request->input('SMIN');
        $SMAX               = $request->input('SMAX');	

        $query = DB::table('usul_susun_dcts')->where('NO_BUKTI', $request->NO_BUKTI)->whereNotIn('NO_ID',  $NO_ID)->delete();

        // Update / Insert
        for ($i = 0; $i < $length; $i++) {
            
            // Insert jika NO_ID baru
            if ($NO_ID[$i] == 'new') {
                $insert = Ubahsus::create(
                    [
                        'NO_BUKTI'         => $request->NO_BUKTI,
                        'TG_SMP'           => Carbon::now(),
                        'PER'              => $periode,
                        'POSTED'           => isset($request['POSTED']) ? $request['POSTED'] : '0',
                        'KD_BRG'           => ($KD_BRG[$i]==null) ? "" :  $KD_BRG[$i],
                        'NA_BRG'           => ($NA_BRG[$i]==null) ? "" :  $NA_BRG[$i],
                        'KET_UK'           => ($KET_UK[$i]==null) ? "" :  $KET_UK[$i],
                        'KET_KEM'          => ($KET_KEM[$i]==null) ? "" :  $KET_KEM[$i],
                        'PANJANG'          => (float) str_replace(',', '', $PANJANG[$i]),
                        'LEBAR'            => (float) str_replace(',', '', $LEBAR[$i]),
                        'TINGGI'           => (float) str_replace(',', '', $TINGGI[$i]),
                        'PANJANG_SHELF'   => (float) str_replace(',', '', $PANJANG_SHELF[$i]),
                        'SUSUN'            => (float) str_replace(',', '', $SUSUN[$i]),
                        'MUKA'             => (float) str_replace(',', '', $MUKA[$i]),
                        'DTR_1M'           => (float) str_replace(',', '', $DTR_1M[$i]),
                        'DTR_MANUAL'       => (float) str_replace(',', '', $DTR_MANUAL[$i]),
                        'KLK'              => ($KLK[$i]==null) ? "" :  $KLK[$i],
                        'LPH'              => (float) str_replace(',', '', $LPH[$i]),
                        'KAPRAK'           => (float) str_replace(',', '', $KAPRAK[$i]),
                        'PERLU'           => (float) str_replace(',', '', $PERLU[$i]),
                        'PERLUB'           => (float) str_replace(',', '', $PERLUB[$i]),
                        'DTR_ORI'          => (float) str_replace(',', '', $DTR_ORI[$i]),
                        'DTR_LAMA'         => (float) str_replace(',', '', $DTR_LAMA[$i]),
                        'DTR'              => (float) str_replace(',', '', $DTR[$i]),
                        'DTR2'             => (float) str_replace(',', '', $DTR2[$i]),
                        'SMIN'             => (float) str_replace(',', '', $SMIN[$i]),
                        'SMAX'             => (float) str_replace(',', '', $SMAX[$i]),
                        'USRNM'            => Auth::user()->username,
                        'TGL_POSTED'       => isset($request['POSTED']) ? Carbon::now() : null,
                        'USRNM_POSTED'     => isset($request['POSTED']) ? Auth::user()->username : null,
                        'CBG'              => Auth::user()->CBG
                    ]
                );
            } else {
                // Update jika NO_ID sudah ada
                $upsert = Ubahsus::updateOrCreate(
                    [
                        'NO_BUKTI'  => $request->NO_BUKTI,
                        'NO_ID'     => (int) str_replace(',', '', $NO_ID[$i])
                    ],

                    [
                        'KAPRAK'              => (float) str_replace(',', '', $KAPRAK[$i]),
                        'PERLU'               => (float) str_replace(',', '', $PERLU[$i]),
                        'PERLUB'              => (float) str_replace(',', '', $PERLUB[$i]),
                        'DTR'                 => (float) str_replace(',', '', $DTR[$i]),			
                    ]
                );
            }
        }

		return redirect('/ubahsus')->with('status', 'Data baru berhasil diedit');
	   
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Resbeliznse
     */

    // ganti 22

    public function destroy($no_bukti)
    {
        $deleted = \App\Models\Olain\Ubahsus::where('NO_BUKTI', $no_bukti)->delete();

        if ($deleted > 0) {
            return redirect('/ubahsus')->with('status', "Data dengan NO_BUKTI $no_bukti berhasil dihapus ($deleted baris)");
        } else {
            return redirect('/ubahsus')->with('status', "Data dengan NO_BUKTI $no_bukti tidak ditemukan");
        }
    }
}
