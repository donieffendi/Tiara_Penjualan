<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
// ganti 1

use App\Models\Master\Pegawai;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use DB;
use Carbon\Carbon;

// ganti 2
class PegawaiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
		
// ganti 3
        return view('master_pegawai.index');
    }

// ganti 4
    public function browse()
    {
		$Pegawai = DB::table('Pegawai')->select('KODEP', 'NAMAP')->orderBy('KODEP', 'ASC')->get();
		return response()->json($Pegawai);
	}



	
    public function getPegawai()
    {
// ganti 5

        $pegawai = Pegawai::query();
		
// ganti 6
		
        return Datatables::of($pegawai)
                ->addIndexColumn()
                ->addColumn('action', function($row) {
					if (Auth::user()->divisi=="programmer" || Auth::user()->divisi=="owner" || Auth::user()->divisi=="production")
					{
                        $btnPrivilege = 
                        '
                                <a class="dropdown-item" href="pegawai/edit/?idx=' . $row->NO_ID . '&tipx=edit";
                                <i class="fas fa-edit"></i>
                                    Edit
                                </a>
                                <hr></hr>
                                <a class="dropdown-item btn btn-danger" onclick="return confirm(&quot; Apakah anda yakin ingin hapus? &quot;)" href="pegawai/delete/'. $row->NO_ID .'">
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
                'KODEP'       => 'required',
                'NAMAP'       => 'required'
            ]
        );

        // Insert Header

// ganti 10
		
        $pegawai = Pegawai::create(
            [
                'KODEP'         => ($request['KODEP']==null) ? "" : $request['KODEP'],	
                'NAMAP'         => ($request['NAMAP']==null) ? "" : $request['NAMAP'],		
				'USRNM'        => Auth::user()->username,
				'TG_SMP'       => Carbon::now()
            ]
        );

//  ganti 11

	    $kodex = $request['KODEP'];
		
		$pegawai = Pegawai::where('KODEP', $kodex )->first();
					       
        //return redirect('/pegawai/edit/?idx=' . $pegawai->NO_ID . '&tipx=edit')->with('statusInsert', 'Data baru berhasil ditambahkan');
		return redirect('/pegawai')->with('statusInsert', 'Data baru berhasil ditambahkan');		

    }



// ganti 15

    public function edit(Request $request ,  Pegawai $pegawai)
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
		   
		   $bingco = DB::SELECT("SELECT NO_ID, KODEP from pegawai 
		                 where KODEP = '$kodex'						 
		                 ORDER BY KODEP ASC  LIMIT 1" );
						 
			
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
			
		   $bingco = DB::SELECT("SELECT NO_ID, KODEP from pegawai    
		                 ORDER BY KODEP ASC  LIMIT 1" );
					 
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
			
		   $bingco = DB::SELECT("SELECT NO_ID, KODEP from pegawai      
		             where KODE < 
					 '$kodex' ORDER BY KODEP DESC LIMIT 1" );
			

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
	   
		   $bingco = DB::SELECT("SELECT NO_ID, KODEP from pegawai    
		             where KODE > 
					 '$kodex' ORDER BY KODEP ASC LIMIT 1" );
					 
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
		  
    		$bingco = DB::SELECT("SELECT NO_ID, KODE from Pegawai    
		              ORDER BY KODEP DESC  LIMIT 1" );
					 
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
			$pegawai = Pegawai::where('NO_ID', $idx )->first();	
	     }
		 else
		 {
             $pegawai = new Pegawai;			 
		 }

		 $data = [
						'header' => $pegawai,
			        ];				
			return view('master_pegawai.edit', $data)->with(['tipx' => $tipx, 'idx' => $idx ]);
		 
	 
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

// ganti 18

    public function update(Request $request, Pegawai $pegawai )
    {
		
        $this->validate($request,
        [		
// ganti 19
                'KODEP'       => 'required',
                'NAMAP'       => 'required'
            ]
        );
		
// ganti 20
		$tipx = 'edit';
		$idx = $request->idx;
		
        $pegawai->update(
            [
              
                'NAMAP'          => ($request['NAMAP']==null) ? "" : $request['NAMAP'],		
				'USRNM'          => Auth::user()->username,
				'TG_SMP'         => Carbon::now()
            ]
        );
//  ganti 21

        //return redirect('/pegawai/edit/?idx=' . $pegawai->NO_ID . '&tipx=edit');
		return redirect('/pegawai')->with('statusInsert', 'Data baru berhasil diupdate');	
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */
	 
// ganti 22
	 
    public function destroy( Request $request, Pegawai $pegawau )
    {

// ganti 23
        $deletepegawai = Pegawai::find($pegawai->NO_ID);

// ganti 24

        $deletepegawai->delete();

// ganti 
        return redirect('/pegawai')->with('status', 'Data berhasil dihapus');
		
		
    }
	
	 public function cekpegawai(Request $request)
    {
        $getItem = DB::SELECT('select count(*) as ADA from pegawai where KODEP ="' . $request->KODEP . '"');

        return $getItem;
    }
	
}
