<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
// ganti 1

use App\Models\OTransaksi\Jual;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use DB;
use Carbon\Carbon;

// ganti 2
class ManualController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        // ganti 3
        return view('otransaksi_manual.index');
    }

    // ganti 4
    public function browse(Request $request)
    {


    }
    

    public function getManual()
    {
        // ganti 5

        $manual = DB::SELECT("SELECT * from jual WHERE FLAG='JL' AND GOL='M' ORDER BY NO_BUKTI ");


        // ganti 6

        return Datatables::of($manual)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                if (Auth::user()->divisi=="programmer" || Auth::user()->divisi=="owner" || Auth::user()->divisi=="sales") 
                {   
                    // url untuk delete di index
                    $url = "'".url("manual/delete/" . $row->NO_ID )."'";
                    // batas

                    $btnDelete = ' onclick="deleteRow('.$url.')"';

                    $btnPrivilege =
                        '
                                <a class="dropdown-item" href="manual/edit/?idx=' . $row->NO_ID . '&tipx=edit";
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
                'TGL'       => 'required'
            ]
        );

        //////     nomer otomatis

        $CBG = Auth::user()->CBG;
		
        $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];

        $bulan    = session()->get('periode')['bulan'];
        $tahun    = substr(session()->get('periode')['tahun'], -2);

        $query = DB::table('jual')->select('NO_BUKTI')->where('PER', $periode)->where('FLAG', 'JL' )->where('GOL', 'M')->where('CBG', $CBG)->orderByDesc('NO_BUKTI')->limit(1)->get();

        

            if ($query != '[]') {
                $query = substr($query[0]->NO_BUKTI, -4);
                $query = str_pad($query + 1, 4, 0, STR_PAD_LEFT);
                $no_bukti = 'M' .  $CBG . $tahun . $bulan . '-' . $query;
            } else {
                $no_bukti = 'M' .  $CBG . $tahun . $bulan . '-0001';
            }



        // Insert Header

        // ganti 10

        $manual = Jual::create(
            [
                'NO_BUKTI'      => $no_bukti,
                'TGL'           => date('Y-m-d', strtotime($request['TGL'])),
                'KODEC'         => ($request['KODEC'] == null) ? "" : $request['KODEC'],
                'NAMAC'         => ($request['NAMAC'] == null) ? "" : $request['NAMAC'],		
                'POIN'          => (float) str_replace(',', '', $request['POIN']),		
                'NOTES'         => ($request['NOTES'] == null) ? "" : $request['NOTES'],
                'USRNM'         => Auth::user()->username,
                'CBG'           => $CBG,
                'TG_SMP'        => Carbon::now(),
                'PER'           => $periode,
                'FLAG'          => 'JL',						
                'GOL'           => 'M'	
            ]
        );

        //  ganti 11

	    $no_buktix = $no_bukti;

		$mnaual = Jual::where('NO_BUKTI', $no_buktix )->first();
        
        DB::SELECT("UPDATE jual, cust
                    SET jual.NAMAC = cust.NAMAC  WHERE jual.KODEC = cust.KODEC 
                    AND jual.NO_BUKTI='$no_buktix';");
					       
        //return redirect('/manual/edit/?idx=' . $manual->NO_ID . '&tipx=edit')->with('statusInsert', 'Data baru berhasil ditambahkan');
		return redirect('/manual')->with('statusInsert', 'Data baru berhasil ditambahkan');

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\OTransaksi\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    // ganti 12



    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\OTransaksi\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    // ganti 15

	public function edit(Request $request ,  Jual $manual)
    { 
        
        $pilihbank = DB::table('bang')->select('KODE', 'NAMA')->orderBy('KODE', 'ASC')->get();

        // ganti 16


		$tipx = $request->tipx;

		$idx = $request->idx;
					

		
		if ( $idx =='0' && $tipx=='undo'  )
	    {
			$tipx ='top';
			
		   }
		   

		if ($tipx=='search') {
			
		   	
    	   $kodex = $request->kodex;
		   
		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from jual 
		                 where PER ='$per' and FLAG ='JL'
                         and GOL ='M' 
                         AND CBG = '$CBG'					 
		                 ORDER BY NO_BUKTI ASC LIMIT 1" );
						 
			
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
			
    	   $kodex = $request->kodex;
			
		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from jual      
		                where PER ='$per' and FLAG ='JL'
                        and GOL ='M' 
                        AND CBG = '$CBG'
                        ORDER BY NO_BUKTI DESC LIMIT 1" );
			

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
	   
		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from jual    
		                where PER ='$per' and FLAG ='JL'
                        and GOL ='M' 
                        AND CBG = '$CBG' ORDER BY NO_BUKTI ASC LIMIT 1" );
					 
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
			$manual = Jual::where('NO_ID', $idx )->first();	
	     }
		 else
		 {
             $manual = new Jual;			 
		 }

		 $data = [
						'header' => $manual,
			        ];				
			return view('otransaksi_manual.edit', $data)->with(['tipx' => $tipx, 'idx' => $idx ]);
		 
	 
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\OTransaksi\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    // ganti 18

    public function update(Request $request, Jual $manual)
    {

        $this->validate(
            $request,
            [

                // ganti 19

                'TGL'      => 'required'
            ]
        );

        
        $CBG = Auth::user()->CBG;
 
        $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];

        // ganti 20
		$tipx = 'edit';
		$idx = $request->idx;

        $manual->update(
            [
                
                'TGL'           => date('Y-m-d', strtotime($request['TGL'])),
                'KODEC'         => ($request['KODEC'] == null) ? "" : $request['KODEC'],
                'NAMAC'         => ($request['NAMAC'] == null) ? "" : $request['NAMAC'],		
                'POIN'          => (float) str_replace(',', '', $request['POIN']),		
                'NOTES'         => ($request['NOTES'] == null) ? "" : $request['NOTES'],
                'USRNM'         => Auth::user()->username,
                'CBG'           => $CBG,
                'TG_SMP'        => Carbon::now(),
                'PER'           => $periode,
                'FLAG'          => 'JL',						
                'GOL'           => 'M'	
            ]
        );
        
        $no_bukti = $manual->NO_BUKTI;
        
        DB::SELECT("UPDATE jual, cust
                    SET jual.NAMAC = cust.NAMAC  WHERE jual.KODEC = cust.KODEC 
                    AND jual.NO_BUKTI='$no_bukti';");

        //  ganti 21

        //return redirect('/manual/edit/?idx=' . $manual->NO_ID . '&tipx=edit');
		return redirect('/manual')->with('statusInsert', 'Data baru berhasil diupdate');
		
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\OTransaksi\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    // ganti 22

    public function destroy( Request $request , Jual $manual)
    {

        // ganti 23
        $deleteManual = Jual::find($manual->NO_ID);

        // ganti 24

        $deleteManual->delete();

        // ganti 
        return redirect('/manual')->with('status', 'Data berhasil dihapus');
    }

    public function cekmanual(Request $request)
    {
        $getItem = DB::SELECT('select count(*) as ADA from jual where KODEC ="' . $request->KODEC . '"');

        return $getItem;
    }
}
