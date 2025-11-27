<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;

use App\Models\Master\MarginKsr;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Master\Brg;
use App\Models\Master\Sup;
use DataTables;
use Auth;
use DB;

class MarginKsrController extends Controller
{

    public function index() {
        return view('master_margin_kasir.index');
    }

    public function getMarginKsr(Request $request)
    {

        $mksr = DB::SELECT("SELECT * from marg order by jns");
		
        return Datatables::of($mksr)
                ->addIndexColumn()
                ->addColumn('action', function($row) {
					if (Auth::user()->divisi=="programmer" || Auth::user()->divisi=="owner" || Auth::user()->divisi=="sales")
					{   
                        // url untuk delete di index
                        $url = "'".url("margin-ksr/delete/" . $row->NO_ID )."'";
                        // batas
                        
                        $btnDelete = ' onclick="deleteRow('.$url.')"';
    
                        $btnPrivilege =
                            '
                                    <a class="dropdown-item" href="margin-ksr/edit/?idx=' . $row->NO_ID . '&tipx=edit";
                                    <i class="fas fa-edit"></i>
                                        Edit
                                    </a>
                                    <a class="dropdown-item btn btn-danger" href="margin-ksr/cetak/' . $row->NO_ID . '">
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
                                <a hidden class="dropdown-item" href="margin-ksr/show/' . $row->NO_ID . '">
                                <i class="fas fa-eye"></i>
                                    Lihat
                                </a>
    
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

                'JNS'       => 'required'

            ]

        );

        // Insert Header

        $marg = MarginKsr::create(
            [
                'JNS'         => ($request['JNS'] == null) ? "" : $request['JNS'],
                'NAMA'         => ($request['NAMA'] == null) ? "" : $request['NAMA'],
                'MARGIN'         => (float) str_replace(',', '', $request['MARGIN']),
                'USRNM'          => Auth::user()->username,
                'TG_SMP'         => Carbon::now()
            ]
        );

	    $kodex = $request['JNS'];
		
		$Marg = MarginKsr::where('JNS', $kodex )->first();

        return redirect('/margin-ksr')->with('status', 'Data baru berhasil ditambahkan');	
		
		}

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */


    public function edit(Request $request , MarginKsr $MrgKsr)
    {
        $tipx = $request->tipx;

		$idx = $request->idx;
		
		if ( $idx =='0' && $tipx=='undo'  )
	    {
			$tipx ='top';
			
		   }
		   
		if ($tipx=='search') {
			
		   	
    	   $kodex = $request->kodex;
		   
		   $bingco = DB::SELECT("SELECT NO_ID, JNS from marg
		                 where JNS = '$kodex'						 
		                 ORDER BY JNS ASC  LIMIT 1" );
						 
			
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
			
		   $bingco = DB::SELECT("SELECT NO_ID, JNS from marg
		                 ORDER BY JNS ASC  LIMIT 1" );
					 
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
			
		   $bingco = DB::SELECT("SELECT NO_ID, JNS from marg
		                 where JNS < 
					 '$kodex' ORDER BY JNS DESC LIMIT 1" );
			

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
	   
		   $bingco = DB::SELECT("SELECT NO_ID, JNS from marg
		                 where JNS > 
					 '$kodex' ORDER BY JNS ASC LIMIT 1" );
					 
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
		  
    		$bingco = DB::SELECT("SELECT NO_ID, JNS from marg
		              ORDER BY JNS DESC  LIMIT 1" );
					 
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
			$marg = MarginKsr::where('NO_ID', $idx )->first();	
	    }
		else
		{
            $marg = new MarginKsr();			 
		}

        // dd($sub);
		$data = [
					'header' => $marg,
			    ];	
                
        return view('master_margin_kasir.edit', $data)->with(['tipx' => $tipx, 'idx' => $idx ]);		
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */


    public function update(Request $request, MarginKsr $MrgKsr)
    {

        $this->validate(
            $request,
            [

                'JNS'       => 'required'
            ]
        );
		
        $tipx = 'edit';
		$idx = $request->idx;

        $MrgKsr->update(
            [

                'NAMA'            => ($request['NAMA'] == null) ? "" : $request['NAMA'],
                'MARGIN'         => (float) str_replace(',', '', $request['MARGIN']),
                'USRNM'          => Auth::user()->username,
                'TG_SMP'         => Carbon::now()

            ]
        );

        ////////////////////////////////////////////////////

        return redirect('/margin-ksr')->with('status', 'Data berhasil diupdate');
		
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    // ganti 22

    public function destroy(Request $request , MarginKsr $MrgKsr)
    {

        $deleteMarg = MarginKsr::find($MrgKsr->NO_ID);

        $deleteMarg->delete();

        return redirect('/margin-ksr')->with('status', 'Data berhasil dihapus');
    }

}