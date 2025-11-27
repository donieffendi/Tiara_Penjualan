<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;

use App\Models\Master\Brg;
use Illuminate\Http\Request;
use App\Models\Master\Sub;
use App\Models\Master\Sup;
use DataTables;
use Auth;
use DB;

class SubController extends Controller
{

    public function index() {
        return view('master_sub.index');
    }

    public function getSub(Request $request)
    {
    
        $sub =  DB::SELECT("SELECT * from aotprice order by sub ASC");
        
        return Datatables::of($sub)
                ->addIndexColumn()
                ->addColumn('action', function($row) {
					if (Auth::user()->divisi=="programmer" || Auth::user()->divisi=="owner" || Auth::user()->divisi=="sales")
					{   
                        // url untuk delete di index
                        $url = "'".url("sub/delete/" . $row->SUB )."'";
                        // batas
                        
                        $btnDelete = ' onclick="deleteRow('.$url.')"';
    
                        $btnPrivilege =
                            '
                                    <a class="dropdown-item" href="sub/edit/?idx=' . $row->SUB . '&tipx=edit";
                                    <i class="fas fa-edit"></i>
                                        Edit
                                    </a>
                                    <hr></hr>
                                    <a hidden class="dropdown-item btn btn-danger" ' . $btnDelete . '>
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
                                <a hidden class="dropdown-item" href="sub/show/' . $row->SUB . '">
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
                'SUB'       => 'required'

            ]

        );

        $query = DB::table('aotprice')->select('SUB')->orderByDesc('SUB')->first();
        
 
        // Insert Header

        $sub = Sub::create(
            [
                'SUB'         => ($request['SUB'] == null) ? "" : $request['SUB'],
                'KELOMPOK'         => ($request['KELOMPOK'] == null) ? "" : $request['KELOMPOK'],
                'PERSEN'          => (float) str_replace(',', '', $request['PERSEN']),
                'PERSEN_HJ'       => (float) str_replace(',', '', $request['PERSEN_HJ']),
                'TYPE'           => ($request['TYPE'] == null) ? "" : $request['TYPE'],
            ]
        );

        //  ganti 11

	    $subx = $request['SUB'];
		
		$Sub = Sub::where('SUB', $subx )->first();
		       
        //return redirect('/brg/edit/?idx=' . $brg->NO_ID . '&tipx=edit')->with('statusInsert', 'Data baru berhasil ditambahkan');
		return redirect('/sub')->with('status', 'Data baru berhasil ditambahkan');	
		
		}

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    

    // ganti 15

    public function edit(Request $request , Sub $sub)
    {

        $tipx = $request->tipx;

		$idx = $request->idx;
					
        $cbg = Auth::user()->CBG;
		
		if ( $idx =='0' && $tipx=='undo'  )
	    {
			$tipx ='top';
			
		}
		   
		if ($tipx=='search') {
			
    	    $subx = $request->subx;
		   
		    $bingco = DB::SELECT("SELECT SUB from aotprice
		                 where SUB = '$subx'						 
		                 ORDER BY SUB ASC  LIMIT 1" );
						 
			
			if(!empty($bingco)) 
			{
				$idx = $bingco[0]->SUB;
			}
			else
			{
				$idx = 0; 
			}
		
					
		}
		
		if ($tipx=='top') {
			
		    $bingco = DB::SELECT("SELECT SUB from aotprice      
		                 ORDER BY SUB ASC  LIMIT 1" );
					 
			if(!empty($bingco)) 
			{
				$idx = $bingco[0]->SUB;
			}
			else
			{
				$idx = 0; 
			}
			  
		}
		
		
		if ($tipx=='prev' ) {
			
    	    $subx = $request->subx;
			
		    $bingco = DB::SELECT("SELECT SUB from aotprice      
		             where SUB < 
					 '$subx' ORDER BY SUB DESC LIMIT 1" );
			

			if(!empty($bingco)) 
			{
				$idx = $bingco[0]->SUB;
			}
			else
			{
				$idx = $idx; 
			}
			  
			  
			  
			  

		}
		if ($tipx=='next' ) {
			
				
      	    $kodex = $request->kodex;
	   
		    $bingco = DB::SELECT("SELECT SUB from aotprice      
		             where SUB > 
					 '$subx' ORDER BY SUB ASC LIMIT 1" );
					 
			if(!empty($bingco)) 
			{
				$idx = $bingco[0]->SUB;
			}
			else
			{
				$idx = $idx; 
			}
			  
			
		}

		if ($tipx=='bottom') {
		  
    		$bingco = DB::SELECT("SELECT SUB from aotprice
		              ORDER BY SUB DESC  LIMIT 1" );
					 
			if(!empty($bingco)) 
			{
				$idx = $bingco[0]->SUB;
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
			$sub = Sub::where('SUB', $idx )->first();	
	    }
		else
		{
            $sub = new Sub;			 
		}

        // dd($sub);
		$data = [
					'header' => $sub,
			    ];			
                
        return view('master_sub.edit', $data)->with(['tipx' => $tipx, 'idx' => $idx ]);		
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */


    public function update(Request $request, Sub $sub)
    {

        $this->validate(
            $request,
            [

                'SUB'       => 'required'
            ]
        );
		
 
        $tipx = 'edit';
		$idx = $request->idx;
        $subx = $request->SUB;

        $sub->update(
            [
                
                'SUB'         => ($request['SUB'] == null) ? "" : $request['SUB'],
                'KELOMPOK'         => ($request['KELOMPOK'] == null) ? "" : $request['KELOMPOK'],
                'PERSEN'          => (float) str_replace(',', '', $request['PERSEN']),
                'PERSEN_HJ'       => (float) str_replace(',', '', $request['PERSEN_HJ']),
                'TYPE'           => ($request['TYPE'] == null) ? "" : $request['TYPE'],
            ]
        );

        // di dhelpy ada update ini juga, tp nanti tanyakan lagi aja
        // $brg->where('SUB', $subx)
        //     ->whereRaw('LEFT(KD_BRG,3) = ?', [$subx])
        //     ->update([
        //         'MARGIN'     => (float) str_replace(',', '', $request['PERSEN']),
        //         'KETX'       => 'Update Margin Sub',
        //         'USERX'      => Auth::user()->username,
        //         'TGLX'       => Now()
        //     ]);

        ////////////////////////////////////////////////////

		return redirect('/sub')->with('status', 'Data berhasil diupdate');
		
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    // ganti 22

    public function destroy(Request $request , Brg $brg)
    {

        // ganti 23
        $deleteBrg = Brg::find($brg->NO_ID);

        // ganti 24

        $deleteBrg->delete();

        // ganti 
        return redirect('/brg')->with('status', 'Data berhasil dihapus');
    }

    public function cekSub(Request $request)
    {
        $sub = $request->SUB;
        \Log::info('sub : ', [$sub]);

        $getItem = DB::SELECT('select count(*) as ADA from aotprice where sub ="' . $sub . '"');

        return $getItem;
    }

}