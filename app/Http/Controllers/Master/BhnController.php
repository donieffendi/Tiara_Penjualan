<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
// ganti 1

use App\Models\Master\Bhn;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use DB;
use Carbon\Carbon;

// ganti 2
class BhnController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
		
// ganti 3
        return view('master_bhn.index');
    }

// ganti 4
    // public function browse()
    // {
	// 	$bhn = DB::table('bhn')->select('KD_BHN', 'NA_BHN', 'SATUAN')->where('GOL', 'Y')->orderBy('KD_BHN', 'ASC')->get();
	// 	return response()->json($bhn);
	// }

    public function browse(Request $request)
    {   
        $kd_bhnx = $request->KD_BHN;
		$pkpx = $request->PKP;
        $golz = $request->GOL;

		$filter_kd_bhn='';

        if( $pkpx == '0' ){

            if (!empty($request->KD_BHN)) {
			
                $filter_kd_bhn = " WHERE KD_BHN ='".$request->KD_BHN."' ";
            } 
                
                $bhn = DB::SELECT("SELECT KD_BHN, TRIM(REPLACE(REPLACE(REPLACE(bhn.NA_BHN, '\n', ' '), '\r', ' '), '\t', ' ')) as NA_BHN,
                                bhn.SATUAN
                                FROM bhn
                                $filter_kd_bhn
                                AND PN='0'
                                AND GOL='$golz'
                                ORDER BY KD_BHN  ");
                            
            if	( empty($bhn) ) {
                
                $bhn = DB::SELECT("SELECT KD_BHN, TRIM(REPLACE(REPLACE(REPLACE(bhn.NA_BHN, '\n', ' '), '\r', ' '), '\t', ' ')) as NA_BHN,
                                    bhn.SATUAN
                                FROM bhn
                                WHERE PN='0'
                                AND GOL='$golz'
                                ORDER BY KD_BHN ");			
            }

        } elseif ($pkpx =! '0') {

            if (!empty($request->KD_BHN)) {
			
                $filter_kd_bhn = " WHERE KD_BHN ='".$request->KD_BHN."' ";
            } 
                
                $bhn = DB::SELECT("SELECT KD_BHN, TRIM(REPLACE(REPLACE(REPLACE(bhn.NA_BHN, '\n', ' '), '\r', ' '), '\t', ' ')) as NA_BHN,
                                bhn.SATUAN
                                FROM bhn
                                $filter_kd_bhn
                                AND PN<>'0'
                                AND GOL='$golz'
                                ORDER BY KD_BHN  ");
                            
            if	( empty($bhn) ) {
                
                $bhn = DB::SELECT("SELECT KD_BHN, TRIM(REPLACE(REPLACE(REPLACE(bhn.NA_BHN, '\n', ' '), '\r', ' '), '\t', ' ')) as NA_BHN,
                                    bhn.SATUAN
                                FROM bhn
                                WHERE PN<>'0'
                                AND GOL='$golz'
                                ORDER BY KD_BHN ");			
            }

        } else {

            if (!empty($request->KD_BHN)) {
			
                $filter_kd_bhn = " WHERE KD_BHN ='".$request->KD_BHN."' ";
            } 
                
                $bhn = DB::SELECT("SELECT KD_BHN, TRIM(REPLACE(REPLACE(REPLACE(bhn.NA_BHN, '\n', ' '), '\r', ' '), '\t', ' ')) as NA_BHN,
                                bhn.SATUAN
                                FROM bhn
                                $filter_kd_bhn
                                AND GOL='$golz'
                                ORDER BY KD_BHN  ");
                            
            if	( empty($bhn) ) {
                
                $bhn = DB::SELECT("SELECT KD_BHN, TRIM(REPLACE(REPLACE(REPLACE(bhn.NA_BHN, '\n', ' '), '\r', ' '), '\t', ' ')) as NA_BHN,
                                    bhn.SATUAN
                                FROM bhn
                                AND GOL='$golz'
                                ORDER BY KD_BHN ");			
            }
        }
        
        return response()->json($bhn);
    }
	
	public function browses()
    {
		$bhn = DB::table('bhn')->select('KD_BHN', 'NA_BHN', 'SATUAN')->where('GOL', 'S')->orderBy('KD_BHN', 'ASC')->get();
		return response()->json($bhn);
	}
	
	public function browsen()
    {
		$bhn = DB::table('bhn')->select('KD_BHN', 'NA_BHN', 'SATUAN')->where('GOL', 'Z')->orderBy('KD_BHN', 'ASC')->get();
		return response()->json($bhn);
	}
	
	public function browseb()
    {
		$bhn = DB::table('bhn')->select('KD_BHN', 'NA_BHN', 'SATUAN')->where('GOL', 'B')->orderBy('KD_BHN', 'ASC')->get();
		return response()->json($bhn);
	}
	
    public function browseall(Request $request)
    {
		// $bhn = DB::table('bhn')->select('KD_BHN', 'NA_BHN', 'SATUAN')->orderBy('KD_BHN', 'ASC')->get();
		

        $kd_bhnx = $request->KD_BHN;

		$filter_kd_bhn='';
		
         if (!empty($request->KD_BHN)) {
			
			$filter_kd_bhn = " WHERE KD_BHN ='".$request->KD_BHN."' ";
		} 
		
			$bhn = DB::SELECT("SELECT KD_BHN, TRIM(REPLACE(REPLACE(REPLACE(bhn.NA_BHN, '\n', ' '), '\r', ' '), '\t', ' ')) as NA_BHN,
                            bhn.SATUAN
                            FROM bhn
                            $filter_kd_bhn
                            ORDER BY KD_BHN  ");
						
		if	( empty($bhn) ) {
			
			$bhn = DB::SELECT("SELECT KD_BHN, TRIM(REPLACE(REPLACE(REPLACE(bhn.NA_BHN, '\n', ' '), '\r', ' '), '\t', ' ')) as NA_BHN,
                                bhn.SATUAN
                            FROM bhn
                            ORDER BY KD_BHN ");			
		}

        return response()->json($bhn);
	}
	

	
    public function getBhn()
    {
// ganti 5

        $bhn = Bhn::query();
		
// ganti 6
		
        return Datatables::of($bhn)
                ->addIndexColumn()
                ->addColumn('action', function($row) {
                    if (Auth::user()->divisi=="programmer" || Auth::user()->divisi=="owner" || Auth::user()->divisi=="purchase")
                    {
                        $btnPrivilege = 
                        '
                                <a class="dropdown-item" href="bhn/edit/?idx=' . $row->NO_ID . '&tipx=edit";
                                <i class="fas fa-edit"></i>
                                    Edit
                                </a>
                                <hr></hr>
                                <a class="dropdown-item btn btn-danger" onclick="return confirm(&quot; Apakah anda yakin ingin hapus? &quot;)" href="bhn/delete/'. $row->NO_ID .'">
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
                        <a class="btn btn-secondary dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-bars"></i>
                        </a>

                        <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                            <a class="dropdown-item" href="bhn/show/'. $row->NO_ID .'">
                            <i class="fas fa-eye"></i>
                                Lihat
                            </a>

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
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
 
 // ganti 8
        return view('master_bhn.create');
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
                'KD_BHN'       => 'required',
                'NA_BHN'      => 'required'
            ]
        );

        // Insert Header

// ganti 10

        $bhn = Bhn::create(
            [
                'KD_BHN'         => ($request['KD_BHN']==null) ? "" : $request['KD_BHN'],	
                'NA_BHN'         => ($request['NA_BHN']==null) ? "" : $request['NA_BHN'],	
				'GOL'            => ($request['GOL']==null) ? "" : $request['GOL'],
                'SATUAN'         => ($request['SATUAN'] == null) ? "" : $request['SATUAN'],
                'SATUAN_BELI'         => ($request['SATUAN_BELI'] == null) ? "" : $request['SATUAN_BELI'],
                'KODES'         => ($request['KODES'] == null) ? "" : $request['KODES'],
                'NAMAS'         => ($request['NAMAS'] == null) ? "" : $request['NAMAS'],
                'ACNOA'          => ($request['ACNOA'] == null) ? "" : $request['ACNOA'],
                'NACNOA'          => ($request['NACNOA'] == null) ? "" : $request['NACNOA'],
                'ACNOB'          => ($request['ACNOB'] == null) ? "" : $request['ACNOB'],
                'NACNOB'          => ($request['NACNOB'] == null) ? "" : $request['NACNOB'],
                'KALI'            => (float) str_replace(',', '', $request['KALI']),
                'ROP'            => (float) str_replace(',', '', $request['ROP']),
                'HJUAL'            => (float) str_replace(',', '', $request['HJUAL']),
                'SMIN'            => (float) str_replace(',', '', $request['SMIN']),
                'SMAX'            => (float) str_replace(',', '', $request['SMAX']),
                'PN'         => ($request['PN'] == null) ? "" : $request['PN'],

				'USRNM'          => Auth::user()->username,
				'TG_SMP'         => Carbon::now()
            ]
        );

//  ganti 11

        return redirect('/bhn')->with('statusInsert', 'Data baru berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */
	 
// ganti 12
	 
    public function show(Bhn $bhn)
    {

// ganti 13

        $bhnData = Bhn::where('NO_ID', $bhn->NO_ID)->first();

// ganti 14
        return view('master_bhn.show', $bhnData);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

// ganti 15

    public function edit(Request $request , Bhn $brg)
    {
		
// ganti 16
		
            $tipx = $request->tipx;

            $idx = $request->idx;
                        


            if ( $idx =='0' && $tipx=='undo'  )
            {
                $tipx ='top';
                
            }
            
            if ($tipx=='search') {
                
                
            $kodex = $request->kodex;
            
            $bingco = DB::SELECT("SELECT NO_ID, ACNO from bhn
                            where KD_BHN = '$kodex'						 
                            ORDER BY KD_BHN ASC  LIMIT 1" );
                            
                
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
                
            $bingco = DB::SELECT("SELECT NO_ID, KD_BHN from bhn      
                            ORDER BY KD_BHN ASC  LIMIT 1" );
                        
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
                
            $kodex = $request->kodex;
                
            $bingco = DB::SELECT("SELECT NO_ID, KD_BHN from bhn      
                        where KD_BHN < 
                        '$kodex' ORDER BY KD_BHN DESC LIMIT 1" );
                

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
                
                    
                $kodex = $request->kodex;

                $bingco = DB::SELECT("SELECT NO_ID, KD_BHN from bhn   
                        where KD_BHN > 
                        '$kodex' ORDER BY KD_BHN ASC LIMIT 1" );
                        
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
            
                $bingco = DB::SELECT("SELECT NO_ID, KD_BHN from bhn     
                        ORDER BY KD_BHN DESC  LIMIT 1" );
                        
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
                $bhn = Bhn::where('NO_ID', $idx )->first();	
            }
            else
            {
                $bhn = new Bhn;			 
            }

            $data = [
                        'header' => $bhn,
                    ];				
            return view('master_bhn.edit', $data)->with(['tipx' => $tipx, 'idx' => $idx ]);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

// ganti 18

    public function update(Request $request, Bhn $bhn )
    {
		
        $this->validate($request,
        [		
// ganti 19
                'KD_BHN'       => 'required',
                'NA_BHN'      => 'required'
            ]
        );
		
// ganti 20

        $bhn->update(
            [
              
                'NA_BHN'         => ($request['NA_BHN']==null) ? "" : $request['NA_BHN'],
                'SATUAN'       => ($request['SATUAN'] == null) ? "" : $request['SATUAN'],			 
                'SATUAN_BELI'       => ($request['SATUAN_BELI'] == null) ? "" : $request['SATUAN_BELI'],			 
                'KODES'       => ($request['KODES'] == null) ? "" : $request['KODES'],			 
                'NAMAS'       => ($request['NAMAS'] == null) ? "" : $request['NAMAS'],			 
                'PN'       => ($request['PN'] == null) ? "" : $request['PN'],			 
                'GOL'          => ($request['GOL'] == null) ? "" : $request['GOL'],
                'ACNOA'          => ($request['ACNOA'] == null) ? "" : $request['ACNOA'],
                'NACNOA'          => ($request['NACNOA'] == null) ? "" : $request['NACNOA'],
                'ACNOB'          => ($request['ACNOB'] == null) ? "" : $request['ACNOB'],
                'NACNOB'          => ($request['NACNOB'] == null) ? "" : $request['NACNOB'],
                'KALI'            => (float) str_replace(',', '', $request['KALI']),
                'ROP'            => (float) str_replace(',', '', $request['ROP']),
                'HJUAL'            => (float) str_replace(',', '', $request['HJUAL']),
                'SMIN'            => (float) str_replace(',', '', $request['SMIN']),
                'SMAX'            => (float) str_replace(',', '', $request['SMAX']),
				'USRNM'          => Auth::user()->username,
				'TG_SMP'         => Carbon::now()
            ]
        );
//  ganti 21

        return redirect('/bhn')->with('status', 'Data baru berhasil diedit');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */
	 
// ganti 22
	 
    public function destroy(Bhn $bhn)
    {

// ganti 23
        $deleteBhn = Bhn::find($bhn->NO_ID);

// ganti 24

        $deleteBhn->delete();

// ganti 
        return redirect('/bhn')->with('status', 'Data berhasil dihapus');
		
		
    }


    public function cekbahan(Request $request)
    {
        $getItem = DB::SELECT('select count(*) as ADA from bhn where KD_BHN ="' . $request->KD_BHN . '"');

        return $getItem;
    }
	
}
