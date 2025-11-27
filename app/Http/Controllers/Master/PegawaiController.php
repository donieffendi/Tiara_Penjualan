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
    public function browse(Request $request)
    {
        $pegawai = DB::SELECT("SELECT NO_ID, KODEP, NAMAP, ALAMAT, KOTA
                            FROM pegawai
                            WHERE STA='SALES'
                            ORDER BY KODEP");

        return response()->json($pegawai);
    }

    public function browse_sopir(Request $request)
    {
        $pegawai = DB::SELECT("SELECT NO_ID, KODEP, NAMAP, ALAMAT, KOTA
                            FROM pegawai
                            WHERE STA='SOPIR'
                            ORDER BY KODEP");

        return response()->json($pegawai);
    }


    public function getPegawai()
    {
        // ganti 5

        $pegawai = Pegawai::query();

        // ganti 6

        return Datatables::of($pegawai)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                if (Auth::user()->divisi=="programmer" || Auth::user()->divisi=="owner" || Auth::user()->divisi=="sales") 
                {

                    // url untuk delete di index
                    $url = "'".url("pegawai/delete/" . $row->NO_ID )."'";
                    // batas

                    $btnDelete = ' onclick="deleteRow('.$url.')"';

                    $btnPrivilege =
                        '
                                <a class="dropdown-item" href="pegawai/edit/?idx=' . $row->NO_ID . '&tipx=edit";
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
                'KODEP'       => 'required',
                'NAMAP'       => 'required'
				//,
                //'GOL'         => 'required'
            ]
        );





        // Insert Header

        // ganti 10

        $pegawai = Pegawai::create(
            [
                'KODEP'         => ($request['KODEP'] == null) ? "" : $request['KODEP'],
                'NAMAP'         => ($request['NAMAP'] == null) ? "" : $request['NAMAP'],
                'ALAMAT'           => ($request['ALAMAT'] == null) ? "" : $request['ALAMAT'],
                'KOTA'            => ($request['KOTA'] == null) ? "" : $request['KOTA'],
                // 'GOL'           => 'Y',
                'TELPON'       => ($request['TELPON'] == null) ? "" : $request['TELPON'],
                'NO_KTP'       => ($request['NO_KTP'] == null) ? "" : $request['NO_KTP'],
                'KONTAK'            => ($request['KONTAK'] == null) ? "" : $request['KONTAK'],
                'HP'            => ($request['HP'] == null) ? "" : $request['HP'],
                // 'PKP'           => (float) str_replace(',', '', $request['PKP']),
                'EMAIL'           => ($request['EMAIL'] == null) ? "" : $request['EMAIL'],
                'NPWP'            => ($request['NPWP'] == null) ? "" : $request['NPWP'],
                'KET'            => ($request['KET'] == null) ? "" : $request['KET'],
                'BANK'            => ($request['BANK'] == null) ? "" : $request['BANK'],
                'BANK_CAB'      => ($request['BANK_CAB'] == null) ? "" : $request['BANK_CAB'],
                'BANK_KOTA'     => ($request['BANK_KOTA'] == null) ? "" : $request['BANK_KOTA'],
                'BANK_NAMA'     => ($request['BANK_NAMA'] == null) ? "" : $request['BANK_NAMA'],
                'BANK_REK'      => ($request['BANK_REK'] == null) ? "" : $request['BANK_REK'],
                'LIM'            => ($request['LIM'] == null) ? "" : $request['LIM'],
                'STA'            => ($request['STA'] == null) ? "" : $request['STA'],
                'HARI'            => ($request['HARI'] == null) ? "" : $request['HARI'],
                'UMAKAN'            => (float) str_replace(',', '', $request['UMAKAN']),
                'KOM'            => (float) str_replace(',', '', $request['KOM']),
                'PINJAM'            => (float) str_replace(',', '', $request['PINJAM']),
                'USRNM'          => Auth::user()->username,
                'TG_SMP'         => Carbon::now()
            ]
        );

        //  ganti 11

	    $kodecx = $request['KODEP'];
		
		$pegawai = Pegawai::where('KODEP', $kodecx )->first();
					       
        //return redirect('/pegawai/edit/?idx=' . $pegawai->NO_ID . '&tipx=edit')->with('statusInsert', 'Data baru berhasil ditambahkan');
		return redirect('/pegawai')->with('statusInsert', 'Data baru berhasil ditambahkan');

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

	public function edit(Request $request ,  Pegawai $pegawai)
    { 
        
        // $pilihbank = DB::table('bang')->select('KODE', 'NAMA')->orderBy('KODE', 'ASC')->get();

        // ganti 16


		$tipx = $request->tipx;

		$idx = $request->idx;
					

		
		if ( $idx =='0' && $tipx=='undo'  )
	    {
			$tipx ='top';
			
		   }
		   

		if ($tipx=='search') {
			
		   	
    	   $kodex = $request->kodex;
		   
		   $bingco = DB::SELECT("SELECT NO_ID, ACNO from pegawai 
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
			
		   $bingco = DB::SELECT("SELECT NO_ID, KODEP from CUST      
		             where KODEP < 
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
	   
		   $bingco = DB::SELECT("SELECT NO_ID, KODEP from CUST    
		             where KODEP > 
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
		  
    		$bingco = DB::SELECT("SELECT NO_ID, KODEP from CUST     
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

    public function update(Request $request, Pegawai $pegawai)
    {

        $this->validate(
            $request,
            [

                // ganti 19

                'KODEP'       => 'required',
                'NAMAP'      => 'required'
            ]
        );

        // ganti 20
		$tipx = 'edit';
		$idx = $request->idx;

        $pegawai->update(
            [

                'NAMAP'         => $request['NAMAP'],
                'ALAMAT'           => ($request['ALAMAT'] == null) ? "" : $request['ALAMAT'],
                'KOTA'            => ($request['KOTA'] == null) ? "" : $request['KOTA'],
                'TELPON'       => ($request['TELPON'] == null) ? "" : $request['TELPON'],
                'HP'            => ($request['HP'] == null) ? "" : $request['HP'],
                'AKT'            => ($request['AKT'] == null) ? "" : $request['AKT'],
                // 'PKP'           => (float) str_replace(',', '', $request['PKP']),
                // 'GOL'            => ($request['GOL'] == null) ? "" : $request['GOL'],
                'KONTAK'        => ($request['KONTAK'] == null) ? "" : $request['KONTAK'],
                'EMAIL'           => ($request['EMAIL'] == null) ? "" : $request['EMAIL'],
                'NPWP'            => ($request['NPWP'] == null) ? "" : $request['NPWP'],
                'KET'            => ($request['KET'] == null) ? "" : $request['KET'],
                'BANK'            => ($request['BANK'] == null) ? "" : $request['BANK'],
                'BANK_CAB'      => ($request['BANK_CAB'] == null) ? "" : $request['BANK_CAB'],
                'BANK_KOTA'     => ($request['BANK_KOTA'] == null) ? "" : $request['BANK_KOTA'],
                'BANK_NAMA'     => ($request['BANK_NAMA'] == null) ? "" : $request['BANK_NAMA'],
                'BANK_REK'      => ($request['BANK_REK'] == null) ? "" : $request['BANK_REK'],
                'STA'           => ($request['STA'] == null) ? "" : $request['STA'],
                'NO_KTP'           => ($request['NO_KTP'] == null) ? "" : $request['NO_KTP'],
                'LIM'            => (float) str_replace(',', '', $request['LIM']),
                'HARI'            => (float) str_replace(',', '', $request['HARI']),
                'KOM'            => (float) str_replace(',', '', $request['KOM']),
                'PINJAM'            => (float) str_replace(',', '', $request['PINJAM']),
                'UMAKAN'            => (float) str_replace(',', '', $request['UMAKAN']),
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

    public function destroy( Request $request , Pegawai $pegawai)
    {

        // ganti 23
        $deletePegawai = Pegawai::find($pegawai->NO_ID);

        // ganti 24

        $deletePegawai->delete();

        // ganti 
        return redirect('/pegawai')->with('status', 'Data berhasil dihapus');
    }

    public function cekpegawai(Request $request)
    {
        $getItem = DB::SELECT('select count(*) as ADA from pegawai where KODEP ="' . $request->KODEP . '"');

        return $getItem;
    }
}
