<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\Zsup;
use App\Models\Master\Acnox;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use DB;
use Carbon\Carbon;

class ZsupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('master_zsup.index');
    }



    public function browse_hari(Request $request)
    {
        $kodes = $request->KODES;

         $zsup = DB::SELECT("SELECT NO_ID, KODES, NAMAS, ALAMAT, KOTA, NOTBAY, KONTAK, AKTIF, CASE WHEN PKP = '1' THEN '(PKP)' ELSE '(NON PKP)' END AS PKP2,
                            PKP, HARI
                            FROM zsup WHERE KODES = '$kodes' "); 
	

        return response()->json($zsup);
    }
    
    public function browse(Request $request)
    {

		
    	if (!empty(request('q'))) {


                 $zsup = DB::SELECT("SELECT NO_ID, KODES, NAMAS, ALAMAT, KOTA, NOTBAY, KONTAK, AKTIF, CASE WHEN PKP = '1' THEN '(PKP)' ELSE '(NON PKP)' END AS PKP2,
                            PKP, HARI
                            FROM zsup WHERE NAMAS <> '' AND NAMAS LIKE ('%$request->q%') ORDER BY NAMAS "); 
	
    	    
        } else {
			$zsup = DB::SELECT("SELECT NO_ID, KODES, NAMAS, ALAMAT, KOTA, NOTBAY, KONTAK, AKTIF, CASE WHEN PKP = '1' THEN '(PKP)' ELSE '(NON PKP)' END AS PKP2,
                                PKP, HARI
                            FROM zsup
                            WHERE NAMAS <> ''
                            ORDER BY NAMAS ");			
		}
		
        return response()->json($zsup);
    }

	public function browsezsup(Request $request){
        $data =DB::SELECT("SELECT KODES, CONCAT(NAMAS.'-'.KOTA) AS NAMAS from zsup 
				WHERE NAMAS LIKE ('%'.$request->q.'%') ORDER BY NAMAS LIMIT 30 ");
        return response()->json($data);
    }
	
    public function getZsup( Request $request )
    {
		
        $zsup = DB::SELECT("SELECT * from zsup ORDER BY KODES ");
	
        return Datatables::of($zsup)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                if (Auth::user()->divisi=="programmer" || Auth::user()->divisi=="owner" || Auth::user()->divisi=="assistant" || Auth::user()->divisi=="accounting" || Auth::user()->divisi=="pembelian" || Auth::user()->divisi=="penjualan") 
                {   
                    // url untuk delete di index
                    $url = "'".url("zsup/delete/" . $row->NO_ID )."'";
                    // batas
                    
                    $btnDelete = ' onclick="deleteRow('.$url.')"';

                    $btnPrivilege =
                        '
                                <a class="dropdown-item" href="zsup/edit/?idx=' . $row->NO_ID . '&tipx=edit";                                <i class="fas fa-edit"></i>
                                    Edit
                                </a>
                                <hr>
                                </hr>

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
            // GANTI 8 SESUAI NAMA KOLOM DI NAVICAT //
            [
                'KODES'       => 'required',
                'NAMAS'       => 'required',
                'GOL'         => 'required'
            ]
        );

        // Insert Header

        $query = DB::table('zsup')->select('KODES')->orderByDesc('KODES')->limit(1)->get();

   //     if ($query != '[]') {
   //         $query = substr($query[0]->KODES, -4);
   //         $query = str_pad($query + 1, 6, 0, STR_PAD_LEFT);
   //         $kodes = 'S'. $query;
   //     } else {
   //         $kodes = 'S' . '000001';
   //     }
		
        $zsup = Zsup::create(
            [
//                'KODES'         => $kodes,

                'KODES'         => ($request['KODES'] == null) ? "" : $request['KODES'],				
                'NAMAS'         => ($request['NAMAS'] == null) ? "" : $request['NAMAS'],
                // 'KODESGD'         => ($request['KODESGD'] == null) ? "" : $request['KODESGD'],				
                // 'NAMASGD'         => ($request['NAMASGD'] == null) ? "" : $request['NAMASGD'],
                'ALAMAT'           => ($request['ALAMAT'] == null) ? "" : $request['ALAMAT'],
                // 'ALAMAT2'           => ($request['ALAMAT2'] == null) ? "" : $request['ALAMAT2'],
                'KOTA'            => ($request['KOTA'] == null) ? "" : $request['KOTA'],
                'GOL'            => ($request['GOL'] == null) ? "" : $request['GOL'],
                'FAX'            => ($request['FAX'] == null) ? "" : $request['FAX'],
                'TELPON1'       => (float) str_replace(',', '', $request['TELPON1']),
                'HP'            => (float) str_replace(',', '', $request['HP']),
                // 'AKT'           => (float) str_replace(',', '', $request['AKT']),
                'AKT'           => 1,
                'PKP'           => (float) str_replace(',', '', $request['PKP']),
                'KONTAK'        => ($request['KONTAK'] == null) ? "" : $request['KONTAK'],
                'EMAIL'           => ($request['EMAIL'] == null) ? "" : $request['EMAIL'],
                'NPWP'            => ($request['NPWP'] == null) ? "" : $request['NPWP'],
                'KET'            => ($request['KET'] == null) ? "" : $request['KET'],
                'BANK'            => ($request['BANK'] == null) ? "" : $request['BANK'],
                'BANK_CAB'      => ($request['BANK_CAB'] == null) ? "" : $request['BANK_CAB'],
                'BANK_KOTA'     => ($request['BANK_KOTA'] == null) ? "" : $request['BANK_KOTA'],
                'BANK_NAMA'     => ($request['BANK_NAMA'] == null) ? "" : $request['BANK_NAMA'],
                'BANK_REK'      => ($request['BANK_REK'] == null) ? "" : $request['BANK_REK'],
                'HARI'            => (float) str_replace(',', '', $request['HARI']),

                'NOREK'         => ($request['NOREK'] == null) ? "" : $request['NOREK'],
                'NOTBAY'        => ($request['NOTBAY'] == null) ? "" : $request['NOTBAY'],
                'LDT_NEW'       => ($request['LDT_NEW'] == null) ? "" : $request['LDT_NEW'],
                'LDT_REP'       => ($request['LDT_REP'] == null) ? "" : $request['LDT_REP'],
                'PLH'           => ($request['PLH'] == null) ? "" : $request['PLH'],
                'PLM'           => ($request['PLM'] == null) ? "" : $request['PLM'],
                'PLL'           => ($request['PLL'] == null) ? "" : $request['PLL'],
                'SKH'           => ($request['SKH'] == null) ? "" : $request['SKH'],
                'SKH_KET'       => ($request['SKH_KET'] == null) ? "" : $request['SKH_KET'],
                'SKM'           => ($request['SKM'] == null) ? "" : $request['SKM'],
                'SKM_KET'       => ($request['SKM_KET'] == null) ? "" : $request['SKM_KET'],
                'SKL'           => ($request['SKL'] == null) ? "" : $request['SKL'],
                'SKL_KET'       => ($request['SKL_KET'] == null) ? "" : $request['SKL_KET'],
                'KET'           => ($request['KET'] == null) ? "" : $request['KET'],
                'NKUALITAS'     => ($request['NKUALITAS'] == null) ? "" : $request['NKUALITAS'],
                'KUALITAS'      => (float) str_replace(',', '', $request['KUALITAS']),
                'NHARGA'        => ($request['NHARGA'] == null) ? "" : $request['NHARGA'],
                'NOTE_HARGA'    => (float) str_replace(',', '', $request['NOTE_HARGA']),
                'NPENGIRIMAN'   => ($request['NPENGIRIMAN'] == null) ? "" : $request['NPENGIRIMAN'],
                'PENGIRIMAN'    => (float) str_replace(',', '', $request['PENGIRIMAN']),
                'NKEAMANAN'     => ($request['NKEAMANAN'] == null) ? "" : $request['NKEAMANAN'],
                'KEAMANAN'      => (float) str_replace(',', '', $request['KEAMANAN']),
                'NKREDIT'       => ($request['NKREDIT'] == null) ? "" : $request['NKREDIT'],
                'KREDIT'        => (float) str_replace(',', '', $request['KREDIT']),
                'NPRODUKSI'     => ($request['NPRODUKSI'] == null) ? "" : $request['NPRODUKSI'],
                'PRODUKSI'      => (float) str_replace(',', '', $request['PRODUKSI']),
                'NPELAYANAN'    => ($request['NPELAYANAN'] == null) ? "" : $request['NPELAYANAN'],
                'PELAYANAN'     => (float) str_replace(',', '', $request['PELAYANAN']),
                'NISO'          => ($request['NISO'] == null) ? "" : $request['NISO'],
                'ISO'           => (float) str_replace(',', '', $request['ISO']),
                'NILAI'         => (float) str_replace(',', '', $request['NILAI']),

                'USRNM'          => Auth::user()->username,
                'TG_SMP'        => Carbon::now()
            ]
        );


	    $kodesx = $request['KODES'];
		
		$zsup = Zsup::where('KODES', $kodesx )->first();
					       
        //return redirect('/sup/edit/?idx=' . $sup->NO_ID . '&tipx=edit')->with('statusInsert', 'Data baru berhasil ditambahkan');
		return redirect('/zsup')->with('statusInsert', 'Data baru berhasil ditambahkan');		


    }

 
 
    public function edit(Request $request ,  Zsup $zsup)
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
		   
		   $bingco = DB::SELECT("SELECT NO_ID, KODES from zsup 
		                 where KODES = '$kodex'						 
		                 ORDER BY KODES ASC  LIMIT 1" );
						 
			
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
			
		   $bingco = DB::SELECT("SELECT NO_ID, KODES from zsup      
		                 ORDER BY KODES ASC  LIMIT 1" );
					 
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
			
		   $bingco = DB::SELECT("SELECT NO_ID, KODES from SUP     
		             where KODES < 
					 '$kodex' ORDER BY KODES DESC LIMIT 1" );
			

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
	   
		   $bingco = DB::SELECT("SELECT NO_ID, KODES from SUP    
		             where KODES > 
					 '$kodex' ORDER BY KODES ASC LIMIT 1" );
					 
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
		  
    		$bingco = DB::SELECT("SELECT NO_ID, KODES from SUP    
		              ORDER BY KODES DESC  LIMIT 1" );
					 
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
			$zsup = Zsup::where('NO_ID', $idx )->first();	
	     }
		 else
		 {
             $zsup = new Zsup;			 
		 }

		 $data = [
						'header' => $zsup,
			        ];				
			return view('master_zsup.edit', $data)->with(['tipx' => $tipx, 'idx' => $idx ])->with(['pilihbank' => $pilihbank]);
		 
	 
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Zsup $zsup)
    {

        $this->validate(
            $request,
            [
                'KODES'       => 'required',
                'NAMAS'      => 'required'
            ]
        );

		$tipx = 'edit';
		$idx = $request->idx;
		
        $zsup->update(
            [

                'NAMAS'       => $request['NAMAS'],
                // 'KODESGD'           => ($request['KODESGD'] == null) ? "" : $request['KODESGD'],
                // 'NAMASGD'            => ($request['NAMASGD'] == null) ? "" : $request['NAMASGD'],
                'ALAMAT'           => ($request['ALAMAT'] == null) ? "" : $request['ALAMAT'],
                // 'ALAMAT2'           => ($request['ALAMAT2'] == null) ? "" : $request['ALAMAT2'],
                'KOTA'            => ($request['KOTA'] == null) ? "" : $request['KOTA'],
                'TELPON1'       => (float) str_replace(',', '', $request['TELPON1']),
                'HP'            => (float) str_replace(',', '', $request['HP']),
                'FAX'            => ($request['FAX'] == null) ? "" : $request['FAX'],
                'AKT'           => (float) str_replace(',', '', $request['AKT']),
                'PKP'           => (float) str_replace(',', '', $request['PKP']),
                'KONTAK'        => ($request['KONTAK'] == null) ? "" : $request['KONTAK'],
                'EMAIL'           => ($request['EMAIL'] == null) ? "" : $request['EMAIL'],
                'NPWP'            => ($request['NPWP'] == null) ? "" : $request['NPWP'],
                'KET'            => ($request['KET'] == null) ? "" : $request['KET'],
                'BANK'            => ($request['BANK'] == null) ? "" : $request['BANK'],
                'BANK_CAB'      => ($request['BANK_CAB'] == null) ? "" : $request['BANK_CAB'],
                'BANK_KOTA'     => ($request['BANK_KOTA'] == null) ? "" : $request['BANK_KOTA'],
                'BANK_NAMA'     => ($request['BANK_NAMA'] == null) ? "" : $request['BANK_NAMA'],
                'BANK_REK'      => ($request['BANK_REK'] == null) ? "" : $request['BANK_REK'],
                'GOL'           => ($request['GOL'] == null) ? "" : $request['GOL'],
                'HARI'            => (float) str_replace(',', '', $request['HARI']),
                
                'NOREK'         => ($request['NOREK'] == null) ? "" : $request['NOREK'],
                'NOTBAY'        => ($request['NOTBAY'] == null) ? "" : $request['NOTBAY'],
                'LDT_NEW'       => ($request['LDT_NEW'] == null) ? "" : $request['LDT_NEW'],
                'LDT_REP'       => ($request['LDT_REP'] == null) ? "" : $request['LDT_REP'],
                'PLH'           => ($request['PLH'] == null) ? "" : $request['PLH'],
                'PLM'           => ($request['PLM'] == null) ? "" : $request['PLM'],
                'PLL'           => ($request['PLL'] == null) ? "" : $request['PLL'],
                'SKH'           => ($request['SKH'] == null) ? "" : $request['SKH'],
                'SKH_KET'       => ($request['SKH_KET'] == null) ? "" : $request['SKH_KET'],
                'SKM'           => ($request['SKM'] == null) ? "" : $request['SKM'],
                'SKM_KET'       => ($request['SKM_KET'] == null) ? "" : $request['SKM_KET'],
                'SKL'           => ($request['SKL'] == null) ? "" : $request['SKL'],
                'SKL_KET'       => ($request['SKL_KET'] == null) ? "" : $request['SKL_KET'],
                'KET'           => ($request['KET'] == null) ? "" : $request['KET'],
                'NKUALITAS'     => ($request['NKUALITAS'] == null) ? "" : $request['NKUALITAS'],
                'KUALITAS'      => (float) str_replace(',', '', $request['KUALITAS']),
                'NHARGA'        => ($request['NHARGA'] == null) ? "" : $request['NHARGA'],
                'NOTE_HARGA'    => (float) str_replace(',', '', $request['NOTE_HARGA']),
                'NPENGIRIMAN'   => ($request['NPENGIRIMAN'] == null) ? "" : $request['NPENGIRIMAN'],
                'PENGIRIMAN'    => (float) str_replace(',', '', $request['PENGIRIMAN']),
                'NKEAMANAN'     => ($request['NKEAMANAN'] == null) ? "" : $request['NKEAMANAN'],
                'KEAMANAN'      => (float) str_replace(',', '', $request['KEAMANAN']),
                'NKREDIT'       => ($request['NKREDIT'] == null) ? "" : $request['NKREDIT'],
                'KREDIT'        => (float) str_replace(',', '', $request['KREDIT']),
                'NPRODUKSI'     => ($request['NPRODUKSI'] == null) ? "" : $request['NPRODUKSI'],
                'PRODUKSI'      => (float) str_replace(',', '', $request['PRODUKSI']),
                'NPELAYANAN'    => ($request['NPELAYANAN'] == null) ? "" : $request['NPELAYANAN'],
                'PELAYANAN'     => (float) str_replace(',', '', $request['PELAYANAN']),
                'NISO'          => ($request['NISO'] == null) ? "" : $request['NISO'],
                'ISO'           => (float) str_replace(',', '', $request['ISO']),
                'NILAI'         => (float) str_replace(',', '', $request['NILAI']),

                'USRNM'          => Auth::user()->username,
                'TG_SMP'         => Carbon::now()
            ]
        );


        //return redirect('/sup/edit/?idx=' . $sup->NO_ID . '&tipx=edit');
		return redirect('/zsup')->with('statusInsert', 'Data baru berhasil diupdate');
				
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */
    public function destroy( Request $request, Zsup $zsup)
    {
        $deleteZsup = Zsup::find($zsup->NO_ID);
        $deleteZsup->delete();

        return redirect('/zsup')->with('status', 'Data berhasil dihapus');
    }

    public function cekzsup(Request $request)
    {
        $getItem = DB::SELECT('select count(*) as ADA from sup where KODES ="' . $request->KODES . '"');

        return $getItem;
    }
	
    public function getSelectKodes(Request $request)
    {
        $search = $request->search;
        $page = $request->page;
        if ($page == 0) {
            $xa = 0;
        } else {
            $xa = ($page - 1) * 10;
        }
        $perPage = 10;
        
        $hasil = DB::SELECT("SELECT KODES, NAMAS from zsup WHERE (KODES LIKE '%$search%' or NAMAS LIKE '%$search%') ORDER BY KODES LIMIT $xa,$perPage ");
        $selectajax = array();
        foreach ($hasil as $row => $value) {
            $selectajax[] = array(
                'id' => $hasil[$row]->KODES,
                'text' => $hasil[$row]->KODES,
                'namas' => $hasil[$row]->NAMAS,
            );
        }
        $select['total_count'] =  count($selectajax);
        $select['items'] = $selectajax;
        return response()->json($select);
    }
}
