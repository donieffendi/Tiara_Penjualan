<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
// ganti 1

use App\Models\Master\Mkl;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use DB;
use Carbon\Carbon;

// ganti 2
class MklController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
		
// ganti 3
        return view('master_mkl.index');
    }

// ganti 4
    public function browse()
    {
		$Mkl = DB::table('Mkl')->select('KODE', 'NAMA')->orderBy('KODE', 'ASC')->get();
		return response()->json($Mkl);
	}



	
    public function getMkl()
    {
// ganti 5

        $mkl = Mkl::query();
		
// ganti 6
		
        return Datatables::of($mkl)
                ->addIndexColumn()
                ->addColumn('action', function($row) {
					if (Auth::user()->divisi=="programmer" || Auth::user()->divisi=="owner" || Auth::user()->divisi=="production")
					{
                        $btnPrivilege = 
                        '
                                <a class="dropdown-item" href="mkl/edit/?idx=' . $row->NO_ID . '&tipx=edit";
                                <i class="fas fa-edit"></i>
                                    Edit
                                </a>
                                <hr></hr>
                                <a class="dropdown-item btn btn-danger" onclick="return confirm(&quot; Apakah anda yakin ingin hapus? &quot;)" href="mkl/delete/'. $row->NO_ID .'">
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
                'KODE'       => 'required',
                'NAMA'       => 'required'
            ]
        );

        // Insert Header

// ganti 10
		
        $mkl = Mkl::create(
            [
                'KODE'         => ($request['KODE']==null) ? "" : $request['KODE'],	
                'NAMA'         => ($request['NAMA']==null) ? "" : $request['NAMA'],		
				'USRNM'        => Auth::user()->username,
				'TG_SMP'       => Carbon::now()
            ]
        );

//  ganti 11

	    $kodex = $request['KODE'];
		
		$mkl = Mkl::where('KODE', $kodex )->first();
					       
        //return redirect('/mkl/edit/?idx=' . $mkl->NO_ID . '&tipx=edit')->with('statusInsert', 'Data baru berhasil ditambahkan');
		return redirect('/mkl')->with('statusInsert', 'Data baru berhasil ditambahkan');		

    }



// ganti 15

    public function edit(Request $request ,  Mkl $mkl)
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
		   
		   $bingco = DB::SELECT("SELECT NO_ID, KODE from mkl 
		                 where KODE = '$kodex'						 
		                 ORDER BY KODE ASC  LIMIT 1" );
						 
			
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
			
		   $bingco = DB::SELECT("SELECT NO_ID, KODE from mkl    
		                 ORDER BY KODE ASC  LIMIT 1" );
					 
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
			
		   $bingco = DB::SELECT("SELECT NO_ID, KODE from MKL      
		             where KODE < 
					 '$kodex' ORDER BY ACNO DESC LIMIT 1" );
			

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
	   
		   $bingco = DB::SELECT("SELECT NO_ID, KODE from MKL    
		             where KODE > 
					 '$kodex' ORDER BY KODE ASC LIMIT 1" );
					 
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
		  
    		$bingco = DB::SELECT("SELECT NO_ID, KODE from MKL    
		              ORDER BY KODE DESC  LIMIT 1" );
					 
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
			$mkl = Mkl::where('NO_ID', $idx )->first();	
	     }
		 else
		 {
             $mkl = new Mkl;			 
		 }

		 $data = [
						'header' => $mkl,
			        ];				
			return view('master_mkl.edit', $data)->with(['tipx' => $tipx, 'idx' => $idx ]);
		 
	 
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

// ganti 18

    public function update(Request $request, Mkl $mkl )
    {
		
        $this->validate($request,
        [		
// ganti 19
                'KODE'       => 'required',
                'NAMA'       => 'required'
            ]
        );
		
// ganti 20
		$tipx = 'edit';
		$idx = $request->idx;
		
        $mkl->update(
            [
              
                'NAMA'          => ($request['NAMA']==null) ? "" : $request['NAMA'],		
				'USRNM'          => Auth::user()->username,
				'TG_SMP'         => Carbon::now()
            ]
        );
//  ganti 21

        //return redirect('/mkl/edit/?idx=' . $mkl->NO_ID . '&tipx=edit');
		return redirect('/mkl')->with('statusInsert', 'Data baru berhasil diupdate');	
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */
	 
// ganti 22
	 
    public function destroy( Request $request, Mkl $mkl)
    {

// ganti 23
        $deletemkl = Mkl::find($mkl->NO_ID);

// ganti 24

        $deletemkl->delete();

// ganti 
        return redirect('/mkl')->with('status', 'Data berhasil dihapus');
		
		
    }
	
	 public function cekmkl(Request $request)
    {
        $getItem = DB::SELECT('select count(*) as ADA from mkl where KODE ="' . $request->KODE . '"');

        return $getItem;
    }
	
}
