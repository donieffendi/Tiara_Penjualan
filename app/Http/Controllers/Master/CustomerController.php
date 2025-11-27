<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;

use App\Models\Master\Cust;
use Illuminate\Http\Request;
use App\Models\Master\Brg;
use App\Models\Master\Sup;
use DataTables;
use Auth;
use DB;

class CustomerController extends Controller
{

    public function index() {
        return view('master_customer.index');
    }

    public function getCust(Request $request)
    {

        $cust = DB::SELECT("SELECT * from cust");
		
        return Datatables::of($cust)
                ->addIndexColumn()
                ->addColumn('action', function($row) {
					if (Auth::user()->divisi=="programmer" || Auth::user()->divisi=="owner" || Auth::user()->divisi=="sales")
					{   
                        // url untuk delete di index
                        $url = "'".url("cust/delete/" . $row->no_id )."'";
                        // batas
                        
                        $btnDelete = ' onclick="deleteRow('.$url.')"';
    
                        $btnPrivilege =
                            '
                                    <a class="dropdown-item" href="cust/edit/?idx=' . $row->no_id . '&tipx=edit";
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
                                <a hidden class="dropdown-item" href="cust/show/' . $row->no_id . '">
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
                
                'KODEC'       => 'required'

            ]

        );

        // Insert Header

        $cust = Cust::create(
            [
                'kodec'         => ($request['KODEC'] == null) ? "" : $request['KODEC'],
                'namac'           => ($request['NAMAC'] == null) ? "" : $request['NAMAC'],
                'alamat'         => ($request['ALAMAT'] == null) ? "" : $request['ALAMAT'],
                'kota'         => ($request['KOTA'] == null) ? "" : $request['KOTA'],
                'na_pemilik'        => ($request['NA_PEMILIK'] == null) ? "" : $request['NA_PEMILIK'],
                'telpon1'       => ($request['TELPON1'] == null) ? "" : $request['TELPON1'],
                'fax'          => ($request['FAX'] == null) ? "" : $request['FAX'],
                'hp'        => ($request['HP'] == null) ? "" : $request['HP'],
                'kontak'             => ($request['KONTAK'] == null) ? "" : $request['KONTAK'],
                'email'           => ($request['EMAIL'] == null) ? "" : $request['EMAIL'],
                'npwp'           => ($request['NPWP'] == null) ? "" : $request['NPWP'],
                'ket'             => ($request['KET'] == null) ? "" : $request['KET'],
                'bank'             => ($request['BANK'] == null) ? "" : $request['BANK'],
                'bank_nama'        => ($request['BANK_NAMA'] == null) ? "" : $request['BANK_NAMA'],
                'bank_cab'         => ($request['BANK_CAB'] == null) ? "" : $request['BANK_CAB'],
                'bank_kota'          => ($request['BANK_KOTA'] == null) ? "" : $request['BANK_KOTA'],
                'bank_rek'             => ($request['BANK_REK'] == null) ? "" : $request['BANK_REK'],
                'golongan'          => ($request['GOL'] == null) ? "" : $request['GOL'],
                'jenispjk'      => ($request['JENISPJK'] == null) ? "" : $request['JENISPJK'],		 
                'lim'           => (float) str_replace(',', '', $request['LIM']),		 
                'hari'            => ($request['HARI'] == null) ? "" : $request['HARI'],		 
				'USRNM'          => Auth::user()->username
            ]
        );

	    $kodecx = $request['KODEC'];
		
		$Cust = Cust::where('KODEC', $kodecx )->first();
		       
        return redirect('/cust')->with('status', 'Data baru berhasil ditambahkan');	
		
		}

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    

    // ganti 15

    public function edit(Request $request , Cust $cust)
    {

        $tipx = $request->tipx;

		$idx = $request->idx;
		
		if ( $idx =='0' && $tipx=='undo'  )
	    {
			$tipx ='top';
			
		   }
		   
		if ($tipx=='search') {
			
		   	
    	   $kodex = $request->kodex;
		   
		   $bingco = DB::SELECT("SELECT no_id, kodec from cust 
		                 where kodec = '$kodex'						 
		                 ORDER BY kodec ASC  LIMIT 1" );
						 
			
			if(!empty($bingco)) 
			{
				$idx = $bingco[0]->no_id;
			  }
			else
			{
				$idx = 0; 
			  }
		
					
		}
		
		if ($tipx=='top') {
			
		   $bingco = DB::SELECT("SELECT no_id, kodec from cust 
		                 ORDER BY kodec ASC  LIMIT 1" );
					 
			if(!empty($bingco)) 
			{
				$idx = $bingco[0]->no_id;
			}
			else
			{
				$idx = 0; 
			}
			  
		}
		
		
		if ($tipx=='prev' ) {
			
    	   $kodex = $request->kodex;
			
		   $bingco = DB::SELECT("SELECT no_id, kodec from cust 
		                 where kodec < 
					 '$kodex' ORDER BY kodec DESC LIMIT 1" );
			

			if(!empty($bingco)) 
			{
				$idx = $bingco[0]->no_id;
			}
			else
			{
				$idx = $idx; 
			}
			  

		}
		if ($tipx=='next' ) {
			
				
      	   $kodex = $request->kodex;
	   
		   $bingco = DB::SELECT("SELECT no_id, kodec from cust 
		                 where kodec > 
					 '$kodex' ORDER BY kodec ASC LIMIT 1" );
					 
			if(!empty($bingco)) 
			{
				$idx = $bingco[0]->no_id;
			}
			else
			{
				$idx = $idx; 
			}
			  
			
		}

		if ($tipx=='bottom') {
		  
    		$bingco = DB::SELECT("SELECT no_id, kodec from cust 
		              ORDER BY kodec DESC  LIMIT 1" );
					 
			if(!empty($bingco)) 
			{
				$idx = $bingco[0]->no_id;
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
		
		//   $kd_brg = $brg->KD_BRG; 
	
	  	if ( $idx != 0 ) 
		{
			$cust = Cust::where('NO_ID', $idx )->first();	
	    }
		else
		{
            $cust = new Cust;			 
		}

        // dd($sub);
		$data = [
					'header' => $cust,
			    ];			
                
        return view('master_customer.edit', $data)->with(['tipx' => $tipx, 'idx' => $idx ]);		
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */


    public function update(Request $request, Cust $cust)
    {

        $this->validate(
            $request,
            [

                'KODEC'       => 'required'
            ]
        );
 
        $tipx = 'edit';
		$idx = $request->idx;

        $cust->update(
            [

                'kodec'         => ($request['KODEC'] == null) ? "" : $request['KODEC'],
                'namac'           => ($request['NAMAC'] == null) ? "" : $request['NAMAC'],
                'alamat'         => ($request['ALAMAT'] == null) ? "" : $request['ALAMAT'],
                'kota'         => ($request['KOTA'] == null) ? "" : $request['KOTA'],
                'na_pemilik'        => ($request['NA_PEMILIK'] == null) ? "" : $request['NA_PEMILIK'],
                'telpon1'       => ($request['TELPON1'] == null) ? "" : $request['TELPON1'],
                'fax'          => ($request['FAX'] == null) ? "" : $request['FAX'],
                'hp'        => ($request['HP'] == null) ? "" : $request['HP'],
                'kontak'             => ($request['KONTAK'] == null) ? "" : $request['KONTAK'],
                'email'           => ($request['EMAIL'] == null) ? "" : $request['EMAIL'],
                'npwp'           => ($request['NPWP'] == null) ? "" : $request['NPWP'],
                'ket'             => ($request['KET'] == null) ? "" : $request['KET'],
                'bank'             => ($request['BANK'] == null) ? "" : $request['BANK'],
                'bank_nama'        => ($request['BANK_NAMA'] == null) ? "" : $request['BANK_NAMA'],
                'bank_cab'         => ($request['BANK_CAB'] == null) ? "" : $request['BANK_CAB'],
                'bank_kota'          => ($request['BANK_KOTA'] == null) ? "" : $request['BANK_KOTA'],
                'bank_rek'             => ($request['BANK_REK'] == null) ? "" : $request['BANK_REK'],
                'golongan'          => ($request['GOL'] == null) ? "" : $request['GOL'],
                'jenispjk'      => ($request['JENISPJK'] == null) ? "" : $request['JENISPJK'],		 
                'lim'           => (float) str_replace(',', '', $request['LIM']),		 
                'hari'            => ($request['HARI'] == null) ? "" : $request['HARI'],		 
				'USRNM'          => Auth::user()->username
            ]
        );

        ////////////////////////////////////////////////////

		return redirect('/cust')->with('status', 'Data berhasil diupdate');
		
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

}