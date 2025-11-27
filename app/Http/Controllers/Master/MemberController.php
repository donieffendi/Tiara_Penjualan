<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
// ganti 1

use App\Models\Master\Cust;
use App\Models\Master\Acnox;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use DB;
use Carbon\Carbon;

// ganti 2
class MemberController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        // ganti 3
        return view('master_member.index');
    }

    // ganti 4
    public function browse(Request $request)
    {


        if (!empty(request('q'))) {


            $member = DB::SELECT("SELECT NO_ID, KODEC, NAMAC, ALAMAT, KOTA, 
                       KONTAK, AKTIF, CASE WHEN PKP = '1' THEN '(PKP)' ELSE '(NON PKP)' END AS PKP2,
                       PKP, HARI
                       FROM member WHERE NAMAC LIKE ('%$request->q%') ORDER BY NAMAC "); 

       
        } else {
            $member = DB::SELECT("SELECT NO_ID, KODEC, NAMAC, ALAMAT, KOTA, 
                                KONTAK, AKTIF, CASE WHEN PKP = '1' THEN '(PKP)' ELSE '(NON PKP)' END AS PKP2,
                                PKP, HARI
                            FROM member
                            ORDER BY NAMAC ");			
        }
        
        return response()->json($member);
    }
    

    public function getMember()
    {
        // ganti 5

        $member = DB::SELECT("SELECT *, CASE WHEN BLOKIR = '1' THEN '(TERBLOKIR)' ELSE '(AKTIF)' END AS BLOKIR from cust  ORDER BY KODEC ");


        // ganti 6

        return Datatables::of($member)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                if (Auth::user()->divisi=="programmer" || Auth::user()->divisi=="owner" || Auth::user()->divisi=="sales") 
                {   
                    // url untuk delete di index
                    $url = "'".url("member/delete/" . $row->NO_ID )."'";
                    // batas

                    $btnDelete = ' onclick="deleteRow('.$url.')"';

                    $btnPrivilege =
                        '
                                <a class="dropdown-item" href="member/edit/?idx=' . $row->NO_ID . '&tipx=edit";
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
                // 'NO_MEMBER'       => 'required',
                'KODEC'       => 'required',
                'NAMAC'       => 'required'
				//,
                //'GOL'         => 'required'
            ]
        );

        // $CBG = Auth::user()->CBG;

        //UNTUK BARCODE (type code 128)
        $length = 13;
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'; // Kombinasi huruf & angka
        $barcode = '';

        for ($i = 0; $i < $length; $i++) {
            $barcode .= $characters[rand(0, strlen($characters) - 1)];
        }
        // Insert Header

        // ganti 10

        $member = Cust::create(
            [
                'NO_MEMBER'     => ($barcode == null) ? "" : $barcode,
                'KARYAWAN'      => ($request['KARYAWAN'] == null) ? "" : $request['KARYAWAN'],
                'INDUK'         => ($request['INDUK'] == null) ? "" : $request['INDUK'],
                'KODEC'         => ($request['KODEC'] == null) ? "" : $request['KODEC'],
                'NAMAC'         => ($request['NAMAC'] == null) ? "" : $request['NAMAC'],
                'ALAMAT'        => ($request['ALAMAT'] == null) ? "" : $request['ALAMAT'],
                'KOTA'          => ($request['KOTA'] == null) ? "" : $request['KOTA'],
                'T_LAHIR'       => ($request['T_LAHIR'] == null) ? "" : $request['T_LAHIR'],
                'TGL_LAHIR'     => date('Y-m-d', strtotime($request['TGL_LAHIR'])),
                'GENDER'        => ($request['GENDER'] == null) ? "" : $request['GENDER'],
                'AGAMA'         => ($request['AGAMA'] == null) ? "" : $request['AGAMA'],
                'STATUS'        => ($request['STATUS'] == null) ? "" : $request['STATUS'],
                'TGL_NIKAH'     => date('Y-m-d', strtotime($request['TGL_NIKAH'])),
                'TELPON1'       => ($request['TELPON1'] == null) ? "" : $request['TELPON1'],
                'FAX'           => ($request['FAX'] == null) ? "" : $request['FAX'],
                'PKP'           => (float) str_replace(',', '', $request['PKP']),				
                'NAMA_KARTU'    => ($request['NAMA_KARTU'] == null) ? "" : $request['NAMA_KARTU'],				
                'KET_KARTU'     => ($request['KET_KARTU'] == null) ? "" : $request['KET_KARTU'],			
                'ID_POIN'       => ($request['ID_POIN'] == null) ? "" : $request['ID_POIN'],			
                'JUM_POIN'      => (float) str_replace(',', '', $request['JUM_POIN']),						
                'HP'            => ($request['HP'] == null) ? "" : $request['HP'],
                'KONTAK'        => ($request['KONTAK'] == null) ? "" : $request['KONTAK'],
                'EMAIL'         => ($request['EMAIL'] == null) ? "" : $request['EMAIL'],
                'NPWP'          => ($request['NPWP'] == null) ? "" : $request['NPWP'],
                'KET'           => ($request['KET'] == null) ? "" : $request['KET'],
                'BLOKIR'           => (float) str_replace(',', '', $request['BLOKIR']),
                'USRNM'          => Auth::user()->username,
                // 'CBG'            => $CBG,
                'TG_SMP'         => Carbon::now()
            ]
        );

        //  ganti 11

	    $kodecx = $request['KODEC'];
		
		$member = Cust::where('KODEC', $kodecx )->first();
					       
        //return redirect('/member/edit/?idx=' . $member->NO_ID . '&tipx=edit')->with('statusInsert', 'Data baru berhasil ditambahkan');
		return redirect('/member')->with('statusInsert', 'Data baru berhasil ditambahkan');

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

	public function edit(Request $request ,  Cust $member)
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
		   
		   $bingco = DB::SELECT("SELECT NO_ID, ACNO from cust 
		                 where KODEC = '$kodex'						 
		                 ORDER BY KODEC ASC  LIMIT 1" );
						 
			
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
			
		   $bingco = DB::SELECT("SELECT NO_ID, KODEC from cust      
		                 ORDER BY KODEC ASC  LIMIT 1" );
					 
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
			
		   $bingco = DB::SELECT("SELECT NO_ID, KODEC from CUST      
		             where KODEC < 
					 '$kodex' ORDER BY KODEC DESC LIMIT 1" );
			

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
	   
		   $bingco = DB::SELECT("SELECT NO_ID, KODEC from CUST    
		             where KODEC > 
					 '$kodex' ORDER BY KODEC ASC LIMIT 1" );
					 
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
		  
    		$bingco = DB::SELECT("SELECT NO_ID, KODEC from CUST     
		              ORDER BY KODEC DESC  LIMIT 1" );
					 
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
			$member = Cust::where('NO_ID', $idx )->first();	
	     }
		 else
		 {
             $member = new Cust;			 
		 }

		 $data = [
						'header' => $member,
			        ];				
			return view('master_member.edit', $data)->with(['tipx' => $tipx, 'idx' => $idx ]);
		 
	 
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    // ganti 18

    public function update(Request $request, Cust $member)
    {

        $this->validate(
            $request,
            [

                // ganti 19

                // 'NO_MEMBER'  => 'required',
                'KODEC'      => 'required',
                'NAMAC'      => 'required'
            ]
        );

        
        // $CBG = Auth::user()->CBG;
        $barcode = $request['NO_MEMBER'];
        
        if($request['NO_MEMBER']==null || $request['NO_MEMBER']==''){
            //UNTUK BARCODE (type code 128)
            $length = 7;
            $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'; // Kombinasi huruf & angka
            $barcode = '';
    
            for ($i = 0; $i < $length; $i++) {
                $barcode .= $characters[rand(0, strlen($characters) - 1)];
            }
        }

        // ganti 20
		$tipx = 'edit';
		$idx = $request->idx;

        $member->update(
            [

                'NAMAC'         => $request['NAMAC'],
                // 'NO_MEMBER'     => ($barcode == null) ? "" : $barcode,
                'KARYAWAN'      => ($request['KARYAWAN'] == null) ? "" : $request['KARYAWAN'],
                'INDUK'         => ($request['INDUK'] == null) ? "" : $request['INDUK'],
                'ALAMAT'        => ($request['ALAMAT'] == null) ? "" : $request['ALAMAT'],
                'KOTA'          => ($request['KOTA'] == null) ? "" : $request['KOTA'],
                'T_LAHIR'       => ($request['T_LAHIR'] == null) ? "" : $request['T_LAHIR'],
                'TGL_LAHIR'     => date('Y-m-d', strtotime($request['TGL_LAHIR'])),
                'GENDER'        => ($request['GENDER'] == null) ? "" : $request['GENDER'],
                'AGAMA'         => ($request['AGAMA'] == null) ? "" : $request['AGAMA'],
                'STATUS'        => ($request['STATUS'] == null) ? "" : $request['STATUS'],
                'TGL_NIKAH'     => date('Y-m-d', strtotime($request['TGL_NIKAH'])),
                'TELPON1'       => ($request['TELPON1'] == null) ? "" : $request['TELPON1'],
                'FAX'           => ($request['FAX'] == null) ? "" : $request['FAX'],
                'PKP'           => (float) str_replace(',', '', $request['PKP']),				
                'NAMA_KARTU'    => ($request['NAMA_KARTU'] == null) ? "" : $request['NAMA_KARTU'],				
                'KET_KARTU'     => ($request['KET_KARTU'] == null) ? "" : $request['KET_KARTU'],			
                'ID_POIN'       => ($request['ID_POIN'] == null) ? "" : $request['ID_POIN'],			
                'JUM_POIN'      => (float) str_replace(',', '', $request['JUM_POIN']),						
                'HP'            => ($request['HP'] == null) ? "" : $request['HP'],
                'KONTAK'        => ($request['KONTAK'] == null) ? "" : $request['KONTAK'],
                'EMAIL'         => ($request['EMAIL'] == null) ? "" : $request['EMAIL'],
                'NPWP'          => ($request['NPWP'] == null) ? "" : $request['NPWP'],
                'KET'           => ($request['KET'] == null) ? "" : $request['KET'],
                'BLOKIR'           => (float) str_replace(',', '', $request['BLOKIR']),
                'USRNM'          => Auth::user()->username,
                // 'CBG'            => $CBG,
                'TG_SMP'         => Carbon::now()
            ]
        );


        //  ganti 21

        //return redirect('/member/edit/?idx=' . $member->NO_ID . '&tipx=edit');
		return redirect('/member')->with('statusInsert', 'Data baru berhasil diupdate');
		
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    // ganti 22

    public function destroy( Request $request , Cust $member)
    {

        // ganti 23
        $deleteMember = Cust::find($member->NO_ID);

        // ganti 24

        $deleteMember->delete();

        // ganti 
        return redirect('/member')->with('status', 'Data berhasil dihapus');
    }

    public function cekmember(Request $request)
    {
        $getItem = DB::SELECT('select count(*) as ADA from cust where KODEC ="' . $request->KODEC . '"');

        return $getItem;
    }
}
