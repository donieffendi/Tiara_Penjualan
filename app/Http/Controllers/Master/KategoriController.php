<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
// ganti 1

use App\Models\Master\Kategori;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use DB;
use Carbon\Carbon;

// ganti 2
class KategoriController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        // ganti 3
        return view('master_kategori.index');
    }

    // ganti 4
    public function browse(Request $request)
    {
        $kategori = DB::SELECT("SELECT NO_ID, KODE, HARI
                            FROM kategori
                            ORDER BY KODE");

        return response()->json($kategori);
    }




    public function getKategori()
    {
        // ganti 5

        $kategori = Kategori::query();

        // ganti 6

        return Datatables::of($kategori)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                if (Auth::user()->divisi=="programmer" || Auth::user()->divisi=="owner" || Auth::user()->divisi=="sales") 
                {   
                    // url untuk delete di index
                    $url = "'".url("kategori/delete/" . $row->NO_ID )."'";
                    // batas

                    $btnDelete = ' onclick="deleteRow('.$url.')"';

                    $btnPrivilege =
                        '
                                <a class="dropdown-item" href="kategori/edit/?idx=' . $row->NO_ID . '&tipx=edit";
                                <i class="fas fa-edit"></i>
                                    Edit
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
                'KODE'       => 'required'
            ]
        );





        // Insert Header

        // ganti 10

        $kategori = Kategori::create(
            [
                'KODE'         => ($request['KODE'] == null) ? "" : $request['KODE'],
                'HARI'            => (float) str_replace(',', '', $request['HARI']),
                'PERSEN'            => (float) str_replace(',', '', $request['PERSEN']),
                'USRNM'          => Auth::user()->username,
                'TG_SMP'         => Carbon::now()
            ]
        );

        //  ganti 11

	    $kodex = $request['KODE'];
		
		$kategori = Kategori::where('KODE', $kodex )->first();
					       
        return redirect('/kategori')->with('statusInsert', 'Data baru berhasil ditambahkan');

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    // ganti 12



    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    // ganti 15

	public function edit(Request $request ,  Kategori $kategori)
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
		   
		   $bingco = DB::SELECT("SELECT NO_ID, ACNO from kategori 
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
			
		   $bingco = DB::SELECT("SELECT NO_ID, KODE from kategori      
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
			
		   $bingco = DB::SELECT("SELECT NO_ID, KODE from kategori      
		             where KODE < 
					 '$kodex' ORDER BY KODE DESC LIMIT 1" );
			

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
	   
		   $bingco = DB::SELECT("SELECT NO_ID, KODE from kategori    
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
		  
    		$bingco = DB::SELECT("SELECT NO_ID, KODE from kategori     
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
			$kategori = Kategori::where('NO_ID', $idx )->first();	
	     }
		 else
		 {
             $kategori = new Kategori;			 
		 }

		 $data = [
                    'header' => $kategori,
                ];				
			return view('master_kategori.edit', $data)->with(['tipx' => $tipx, 'idx' => $idx ]);
		 
	 
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    // ganti 18

    public function update(Request $request, Kategori $kategori)
    {

        $this->validate(
            $request,
            [

                // ganti 19

                'KODE'       => 'required',
                'HARI'      => 'required'
            ]
        );

        // ganti 20
		$tipx = 'edit';
		$idx = $request->idx;

        $kategori->update(
            [

                'HARI'            => (float) str_replace(',', '', $request['HARI']),
                'PERSEN'            => (float) str_replace(',', '', $request['PERSEN']),
                'USRNM'          => Auth::user()->username,
                'TG_SMP'         => Carbon::now()
            ]
        );


        //  ganti 21

        return redirect('/kategori')->with('statusInsert', 'Data baru berhasil diupdate');
		
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    // ganti 22

    public function destroy( Request $request , Kategori $kategori)
    {

        // ganti 23
        $deleteKategori = Kategori::find($kategori->NO_ID);

        // ganti 24

        $deleteKategori->delete();

        // ganti 
        return redirect('/kategori')->with('status', 'Data berhasil dihapus');
    }

    public function cekkategori(Request $request)
    {
        $getItem = DB::SELECT('select count(*) as ADA from kategori where KODE ="' . $request->KODE . '"');

        return $getItem;
    }
}
