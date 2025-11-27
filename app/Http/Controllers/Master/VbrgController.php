<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
// ganti 1

use App\Models\Master\Vbrg;
use App\Models\Master\VbrgDetail;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use DB;
use Carbon\Carbon;

// ganti 2
class VbrgController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */



    public function index()
    {

        // ganti 3
        return view('master_vbrg.index');
    }


    // ganti 4

    // ganti 4

    public function browse_koreksi(Request $request)
	
    {

        $vbrg = DB::table('vbrg')->select('KD_BRG', 'NA_BRG', 'SATUAN')->orderBy('KD_BRG', 'ASC')->get();
        return response()->json($vbrg); 
    
	}

    public function browse_beli(Request $request)
    {   
		$kd_brgx = $request->KD_BRG;
		$pkpx = $request->PKP;
        $golz = $request->GOL;
        $CBG = Auth::user()->CBG;

		$filter_kd_brg='';

            if (!empty($request->KD_BRG)) {
			
                $filter_kd_brg = " AND vbrg.KD_BRG ='".$request->KD_BRG."' ";
            } 
                
                $vbrg = DB::SELECT("SELECT vbrg.KD_BRG, TRIM(REPLACE(REPLACE(REPLACE(vbrg.NA_BRG, '\n', ' '), '\r', ' '), '\t', ' ')) as NA_BRG,
                                vbrg.SATUAN_BELI AS SATUAN , HJUAL , SUPP AS KODES, vbrgd.AK12
                                FROM vbrg, vbrgd 
                                WHERE vbrg.KD_BRG = vbrgd.KD_BRG
                                $filter_kd_brg 
                                AND vbrgd.CBG = '$CBG'
                                -- AND vbrg. GOL='$golz'
                                ORDER BY vbrg.KD_BRG  ");
                            
            if	( empty($vbrg) ) {
                
                $vbrg = DB::SELECT("SELECT vbrg.KD_BRG, TRIM(REPLACE(REPLACE(REPLACE(vbrg.NA_BRG, '\n', ' '), '\r', ' '), '\t', ' ')) as NA_BRG,
                                        vbrg.SATUAN_BELI AS SATUAN, HJUAL , SUPP AS KODES, vbrgd.AK12
                                FROM vbrg, vbrgd
                                WHERE vbrg.KD_BRG = vbrgd.KD_BRG AND vbrgd.CBG = '$CBG'
                                -- AND vbrg. GOL='$golz'
                                ORDER BY vbrg.KD_BRG ");			
            }
        
        return response()->json($vbrg);
    }

    public function browse(Request $request)
    {   
		$kd_brgx = $request->KD_BRG;
		$pkpx = $request->PKP;
		$ringx = $request->RING;
        $golz = $request->GOL;

		$filter_kd_brg='';

        if( $pkpx == '0' ){

            if (!empty($request->KD_BRG)) {
			
                $filter_kd_brg = " WHERE vbrg.KD_BRG ='".$request->KD_BRG."' ";
            } 
                
                $vbrg = DB::SELECT("SELECT vbrg.KD_BRG, TRIM(REPLACE(REPLACE(REPLACE(vbrg.NA_BRG, '\n', ' '), '\r', ' '), '\t', ' ')) as NA_BRG,
                                    vbrg.SATUAN, vbrgdx.HARGA AS HARGA1, vbrgdx.HARGA2, vbrgdx.HARGA3, vbrgdx.HARGA4, vbrgdx.HARGA5,
                                    vbrgdx.HARGA6, vbrgdx.HARGA7, vbrg.KD_GRUP, vbrg.TYPE_KOM, vbrg.KOM
                                FROM vbrg, vbrgdx
                                $filter_kd_brg and vbrg.KD_BRG = vbrgdx.KD_BRG
                                AND vbrg.PN='0' AND vbrgdx.RING = '$ringx'
                                -- AND brg. GOL='$golz'
                                ORDER BY vbrg.KD_BRG  ");
                            
            if	( empty($vbrg) ) {
                
                $vbrg = DB::SELECT("SELECT vbrg.KD_BRG, TRIM(REPLACE(REPLACE(REPLACE(vbrg.NA_BRG, '\n', ' '), '\r', ' '), '\t', ' ')) as NA_BRG,
                                    vbrg.SATUAN, vbrgdx.HARGA AS HARGA1, vbrgdx.HARGA2, vbrgdx.HARGA3, vbrgdx.HARGA4, vbrgdx.HARGA5,
                                    vbrgdx.HARGA6, vbrgdx.HARGA7, vbrg.KD_GRUP, vbrg.TYPE_KOM, vbrg.KOM
                                FROM vbrg, vbrgdx
                                WHERE vbrg.KD_BRG = vbrgdx.KD_BRG
                                AND vbrg.PN='0' AND vbrgdx.RING = '$ringx'
                                -- AND vbrg. GOL='$golz'
                                ORDER BY vbrg.KD_BRG ");			
            }

        } elseif ($pkpx =! '0')  {

            if (!empty($request->KD_BRG)) {
			
                $filter_kd_brg = " WHERE vbrg.KD_BRG ='".$request->KD_BRG."' ";
            } 
                
                $vbrg = DB::SELECT("SELECT vbrg.KD_BRG, TRIM(REPLACE(REPLACE(REPLACE(vbrg.NA_BRG, '\n', ' '), '\r', ' '), '\t', ' ')) as NA_BRG,
                                        vbrg.SATUAN, vbrgdx.HARGA AS HARGA1, vbrgdx.HARGA2, vbrgdx.HARGA3, vbrgdx.HARGA4, vbrgdx.HARGA5,
                                vbrgdx.HARGA6, vbrgdx.HARGA7, vbrg.KD_GRUP, vbrg.TYPE_KOM, vbrg.KOM
                                FROM vbrg, vbrgdx
                                $filter_kd_brg AND vbrg.KD_BRG = vbrgdx.KD_BRG
                                AND vbrg.PN<>'0' AND vbrgdx.RING = '$ringx'
                                -- AND vbrg.GOL='$golz'
                                ORDER BY vbrg.KD_BRG  ");
                            
            if	( empty($vbrg) ) {
                
                $vbrg = DB::SELECT("SELECT vbrg.KD_BRG, TRIM(REPLACE(REPLACE(REPLACE(vbrg.NA_BRG, '\n', ' '), '\r', ' '), '\t', ' ')) as NA_BRG,
                                        vbrg.SATUAN, vbrgdx.HARGA AS HARGA1, vbrgdx.HARGA2, vbrgdx.HARGA3, vbrgdx.HARGA4, vbrgdx.HARGA5,
                                vbrgdx.HARGA6, vbrgdx.HARGA7, vbrg.KD_GRUP, vbrg.TYPE_KOM, vbrg.KOM
                                FROM vbrg, vbrgdx
                                WHERE vbrg.PN<>'0' AND vbrg.KD_BRG = vbrgdx.KD_BRG
                                -- AND vbrg.GOL='$golz' 
                                AND vbrgdx.RING = '$ringx'
                                ORDER BY vbrg.KD_BRG ");			
            }

        }



        return response()->json($vbrg);
    }

    public function getVbrg( Request $request )
    {
        // ganti 5

        $vbrg = DB::SELECT("SELECT * from vbrg ORDER BY KD_BRG ");
	

        // ganti 6

        return Datatables::of($vbrg)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                if (Auth::user()->divisi=="programmer" || Auth::user()->divisi=="owner" || Auth::user()->divisi=="sales") 
                {   
                    // url untuk delete di index
                    $url = "'".url("vbrg/delete/" . $row->NO_ID )."'";
                    // batas

                    $btnDelete = ' onclick="deleteRow('.$url.')"';

                    $btnPrivilege =
                        '
                                <a class="dropdown-item" href="vbrg/edit/?idx=' . $row->NO_ID . '&tipx=edit";
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
                            <a hidden class="dropdown-item" href="vbrg/show/' . $row->NO_ID . '">
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
            // GANTI 9

            [
                'NA_BRG'       => 'required'

            ]

        );

        $query = DB::table('vbrg')->select('KD_BRG')->orderByDesc('KD_BRG')->first();
        
        $kd_brg = '';
        if ($query) {
            
            $query = $query->KD_BRG;
            $query = str_pad($query + 1, 4, 0, STR_PAD_LEFT);
            $kd_brg = $query;
            
        } else {
            $kd_brg = '0001' ;
        }

        $CBG = Auth::user()->CBG;
 
        // Insert Header

        // ganti 10

        $vbrg = Vbrg::create(
            [
                'KD_BRG'         => ($kd_brg == null) ? "" : $kd_brg,
                'NA_BRG'         => ($request['NA_BRG'] == null) ? "" : $request['NA_BRG'],
                'GOL'            => ($request['GOL'] == null) ? "" : $request['GOL'],
                'SATUAN'         => ($request['SATUAN'] == null) ? "" : $request['SATUAN'],
                'KET_UK'         => ($request['KET_UK'] == null) ? "" : $request['KET_UK'],
                'KET_KEM'        => ($request['KET_KEM'] == null) ? "" : $request['KET_KEM'],
                // 'NAMAS'          => ($request['NAMAS'] == null) ? "" : $request['NAMAS'],
                // 'ACNOA'          => ($request['ACNOA'] == null) ? "" : $request['ACNOA'],
                // 'NACNOA'         => ($request['NACNOA'] == null) ? "" : $request['NACNOA'],
                // 'ACNOB'          => ($request['ACNOB'] == null) ? "" : $request['ACNOB'],
                // 'NACNOB'         => ($request['NACNOB'] == null) ? "" : $request['NACNOB'],
                'DIAMETER'       => (float) str_replace(',', '', $request['DIAMETER']),
                'TEBAL'          => (float) str_replace(',', '', $request['TEBAL']),
                'PANJANG'        => (float) str_replace(',', '', $request['PANJANG']),
                'KG'             => (float) str_replace(',', '', $request['KG']),
                'SMIN'           => (float) str_replace(',', '', $request['SMIN']),
                'SMAX'           => (float) str_replace(',', '', $request['SMAX']),
                'HB'             => (float) str_replace(',', '', $request['HB']),
                'HS'             => (float) str_replace(',', '', $request['HS']),
                'HB_NAIK'        => (float) str_replace(',', '', $request['HB_NAIK']),
                'H_MINC'         => (float) str_replace(',', '', $request['H_MINC']),
                'LEBAR'          => (float) str_replace(',', '', $request['LEBAR']),
                'PN'             => ($request['PN'] == null) ? "" : $request['PN'],
                'GROUP'          => ($request['GROUP'] == null) ? "" : $request['GROUP'],
                'SUB_GROUP'      => ($request['SUB_GROUP'] == null) ? "" : $request['SUB_GROUP'],
                'USRNM'          => Auth::user()->username,
                'TG_SMP'         => Carbon::now(),

                'BL_PER'         => date('Y-m-d', strtotime($request['BL_PER'])),
                'BL_AKR'         => date('Y-m-d', strtotime($request['BL_AKR'])),
                'JL_AKR'         => date('Y-m-d', strtotime($request['JL_AKR'])),
                'SUPP'           => ($request['KODES'] == null) ? "" : $request['KODES'],
                'KLK'            => ($request['KLK'] == null) ? "" : $request['KLK'],
                'LOKASI'         => ($request['LOKASI'] == null) ? "" : $request['LOKASI'],
                'KELOMPOK'       => ($request['KELOMPOK'] == null) ? "" : $request['KELOMPOK'],
                'UP_HB'          => ($request['UP_HB'] == null) ? "" : $request['UP_HB'],
                'HJUAL'          => (float) str_replace(',', '', $request['HJUAL']),
                'CBG'            => $CBG
            ]
        );

        //  ganti 11

	    $kd_brgx = $request['KD_BRG'];
		
		$vbrg = Vbrg::where('KD_BRG', $kd_brgx )->first();

        // DB::SELECT("UPDATE vbrg,  vbrgdx
        //                     SET  vbrgdx.ID =  vbrg.NO_ID  WHERE  vbrg.KD_BRG =  vbrgdx.KD_BRG 
		// 					AND  vbrg.KD_BRG='$kd_brgx';");
					       
        //return redirect('/vbrg/edit/?idx=' . $vbrg->NO_ID . '&tipx=edit')->with('statusInsert', 'Data baru berhasil ditambahkan');
		return redirect('/vbrg')->with('statusInsert', 'Data baru berhasil ditambahkan');	
		
		}

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    

    // ganti 15

    public function edit(Request $request , Vbrg $vbrg)
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
		   
		   $bingco = DB::SELECT("SELECT NO_ID, KD_BRG from vbrg
		                 where KD_BRG = '$kodex'						 
		                 ORDER BY KD_BRG ASC  LIMIT 1" );
						 
			
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
			
		   $bingco = DB::SELECT("SELECT NO_ID, KD_BRG from vbrg      
		                 ORDER BY KD_BRG ASC  LIMIT 1" );
					 
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
			
		   $bingco = DB::SELECT("SELECT NO_ID, KD_BRG from VBRG      
		             where KD_BRG < 
					 '$kodex' ORDER BY KD_BRG DESC LIMIT 1" );
			

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
	   
		   $bingco = DB::SELECT("SELECT NO_ID, KD_BRG from VBRG   
		             where KD_BRG > 
					 '$kodex' ORDER BY KD_BRG ASC LIMIT 1" );
					 
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
		  
    		$bingco = DB::SELECT("SELECT NO_ID, KD_BRG from VBRG     
		              ORDER BY KD_BRG DESC  LIMIT 1" );
					 
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
			$vbrg = Vbrg::where('NO_ID', $idx )->first();	
	     }
		 else
		 {
             $vbrg = new Vbrg;			 
		 }

        $kd_brg = $vbrg->KD_BRG;
        // $vbrgDetail = DB::table('vbrgdx')->where('KD_BRG', $kd_brg)->get();


		 $data = [
                    'header'        => $vbrg,
                    // 'detail'        => $vbrgDetail,
                ];				
                
        return view('master_vbrg.edit', $data)->with(['tipx' => $tipx, 'idx' => $idx ]);
		 
 
       
		
		
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    // ganti 18

    public function update(Request $request, Vbrg $vbrg)
    {

        $this->validate(
            $request,
            [

                // ganti 19

                'KD_BRG'       => 'required',
                'NA_BRG'       => 'required'
            ]
        );

        // ganti 20
		
        $CBG = Auth::user()->CBG;
 
        $tipx = 'edit';
		$idx = $request->idx;

        $vbrg->update(
            [

                'NA_BRG'         => ($request['NA_BRG'] == null) ? "" : $request['NA_BRG'],
                'GOL'            => ($request['GOL'] == null) ? "" : $request['GOL'],
                'SATUAN'         => ($request['SATUAN'] == null) ? "" : $request['SATUAN'],
                'KET_UK'         => ($request['KET_UK'] == null) ? "" : $request['KET_UK'],
                'KET_KEM'        => ($request['KET_KEM'] == null) ? "" : $request['KET_KEM'],
                // 'NAMAS'          => ($request['NAMAS'] == null) ? "" : $request['NAMAS'],
                // 'ACNOA'          => ($request['ACNOA'] == null) ? "" : $request['ACNOA'],
                // 'NACNOA'         => ($request['NACNOA'] == null) ? "" : $request['NACNOA'],
                // 'ACNOB'          => ($request['ACNOB'] == null) ? "" : $request['ACNOB'],
                // 'NACNOB'         => ($request['NACNOB'] == null) ? "" : $request['NACNOB'],
                'DIAMETER'       => (float) str_replace(',', '', $request['DIAMETER']),
                'TEBAL'          => (float) str_replace(',', '', $request['TEBAL']),
                'PANJANG'        => (float) str_replace(',', '', $request['PANJANG']),
                'KG'             => (float) str_replace(',', '', $request['KG']),
                'SMIN'           => (float) str_replace(',', '', $request['SMIN']),
                'SMAX'           => (float) str_replace(',', '', $request['SMAX']),
                'HB'             => (float) str_replace(',', '', $request['HB']),
                'HS'             => (float) str_replace(',', '', $request['HS']),
                'HB_NAIK'        => (float) str_replace(',', '', $request['HB_NAIK']),
                'H_MINC'         => (float) str_replace(',', '', $request['H_MINC']),
                'LEBAR'          => (float) str_replace(',', '', $request['LEBAR']),
                'PN'             => ($request['PN'] == null) ? "" : $request['PN'],
                'GROUP'          => ($request['GROUP'] == null) ? "" : $request['GROUP'],
                'SUB_GROUP'      => ($request['SUB_GROUP'] == null) ? "" : $request['SUB_GROUP'],		 
                'SUPP'           => ($request['KODES'] == null) ? "" : $request['KODES'],		 
                'KLK'            => ($request['KLK'] == null) ? "" : $request['KLK'],		 
				'USRNM'          => Auth::user()->username,
                'TG_SMP'         => Carbon::now(),

                'BL_PER'         => date('Y-m-d', strtotime($request['BL_PER'])),
                'BL_AKR'         => date('Y-m-d', strtotime($request['BL_AKR'])),
                'JL_AKR'         => date('Y-m-d', strtotime($request['JL_AKR'])),
                'SUPP'           => ($request['KODES'] == null) ? "" : $request['KODES'],
                'KLK'            => ($request['KLK'] == null) ? "" : $request['KLK'],
                'LOKASI'         => ($request['LOKASI'] == null) ? "" : $request['LOKASI'],
                'KELOMPOK'       => ($request['KELOMPOK'] == null) ? "" : $request['KELOMPOK'],
                'UP_HB'          => ($request['UP_HB'] == null) ? "" : $request['UP_HB'],
                'HJUAL'          => (float) str_replace(',', '', $request['HJUAL']),
                'CBG'            => $CBG
            ]
        );

        ////////////////////////////////////////////////////

        // $vbrg = Vbrg::where('KD_BRG', $kd_brgx )->first();

        //  ganti 21

        //return redirect('/brg/edit/?idx=' . $vbrg->NO_ID . '&tipx=edit');
        // return redirect('/vbrg/edit/?idx=' . $vbrg->NO_ID . '&tipx=edit');	
		return redirect('/vbrg')->with('status', 'Data berhasil diupdate');
		
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    // ganti 22

    public function destroy(Request $request , Vbrg $vbrg)
    {

        // ganti 23
        $deleteVbrg = Vbrg::find($vbrg->NO_ID);

        // ganti 24

        $deleteVbrg->delete();

        // ganti 
        return redirect('/vbrg')->with('status', 'Data berhasil dihapus');
    }


    public function cekvbarang(Request $request)
    {
        $getItem = DB::SELECT('select count(*) as ADA from vbrg where KD_BRG ="' . $request->KDBRG . '"');

        return $getItem;
    }
	
    public function getSelectKdbrg(Request $request)
    {
        $search = $request->search;
        $page = $request->page;
        if ($page == 0) {
            $xa = 0;
        } else {
            $xa = ($page - 1) * 10;
        }
        $perPage = 10;
        
        $hasil = DB::SELECT("SELECT KD_BRG, NA_BRG, SATUAN from vbrg WHERE (KD_BRG LIKE '%$search%' or NA_BRG LIKE '%$search%') ORDER BY KD_BRG LIMIT $xa,$perPage ");
        $selectajax = array();
        foreach ($hasil as $row => $value) {
            $selectajax[] = array(
                'id' => $hasil[$row]->KD_BRG,
                'text' => $hasil[$row]->KD_BRG,
                'na_brg' => $hasil[$row]->NA_BRG,
                'satuan' => $hasil[$row]->SATUAN,
            );
        }
        $select['total_count'] =  count($selectajax);
        $select['items'] = $selectajax;
        return response()->json($select);
    }
}
