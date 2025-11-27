<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;

use App\Models\Master\Masbank;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Master\Brg;
use App\Models\Master\Sup;
use DataTables;
use Auth;
use DB;

class BankBayarController extends Controller
{

    public function index() {
        return view('master_daftar_bank_pembayaran.index');
    }

    public function getBank(Request $request)
    {
    
        $bank = DB::SELECT("SELECT * from masbank ");
        
        return Datatables::of($bank)
                ->addIndexColumn()
                ->addColumn('action', function($row) {
					if (Auth::user()->divisi=="programmer" || Auth::user()->divisi=="owner" || Auth::user()->divisi=="sales")
					{   
                        // url untuk delete di index
                        $url = "'".url("bank-byr/delete/" . $row->NO_ID )."'";
                        // batas
                        
                        $btnDelete = ' onclick="deleteRow('.$url.')"';
    
                        $btnPrivilege =
                            '
                                    <a class="dropdown-item" href="bank-byr/edit/?idx=' . $row->NO_ID . '&tipx=edit";
                                    <i class="fas fa-edit"></i>
                                        Edit
                                    </a>
                                    <a class="dropdown-item btn btn-danger" href="bank-byr/cetak/' . $row->NO_ID . '">
                                        <i class="fa fa-print" aria-hidden="true"></i>
                                        Print
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
                                <a hidden class="dropdown-item" href="bank-byr/show/' . $row->NO_ID . '">
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
                'KODE'       => 'required'

            ]

        );

        // Insert Header

        $masbank = Masbank::create(
            [
                'KODE'         => ($request['KODE'] == null) ? "" : $request['KODE'],
                'TYPE'           => ($request['TYPE'] == null) ? "" : $request['TYPE'],
                'NM_BANK'         => ($request['NM_BANK'] == null) ? "" : $request['NM_BANK'],
                'NOBANK'         => ($request['NOBANK'] == null) ? "" : $request['NOBANK'],
                'BANK'        => ($request['BANK'] == null) ? "" : $request['BANK'],
                'BATAS'       => ($request['BATAS'] == null) ? "" : $request['BATAS'],
                'CR_CARD'          => ($request['CR_CARD'] == null) ? "" : $request['CR_CARD'],
                'BY_CARD'          => ($request['BY_CARD'] == null) ? "" : $request['BY_CARD'],	 
				'USRNM'          => Auth::user()->username,
                'TG_SMP'         => Carbon::now()
            ]
        );

	    $kodex = $request['KODE'];
		
		$Masbank = Masbank::where('KODE', $kodex )->first();

        return redirect('/bank-byr')->with('status', 'Data baru berhasil ditambahkan');	
		
		}

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    

    // ganti 15

    public function edit(Request $request , Masbank $masbank)
    {

        $tipx = $request->tipx;

		$idx = $request->idx;
					
        $cbg = Auth::user()->CBG;
		
		if ( $idx =='0' && $tipx=='undo'  )
	    {
			$tipx ='top';
			
		   }
		   
		if ($tipx=='search') {
			
		   	
    	   $kodex = $request->kodex;
		   
		   $bingco = DB::SELECT("SELECT NO_ID, KODE from masbank
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
			
		   $bingco = DB::SELECT("SELECT NO_ID, KODE from masbank      
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
			
		   $bingco = DB::SELECT("SELECT NO_ID, KODE from masbank      
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
	   
		   $bingco = DB::SELECT("SELECT NO_ID, KODE from masbank   
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
		  
    		$bingco = DB::SELECT("SELECT NO_ID, KODE from masbank     
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
		
		//   $kd_brg = $brg->KD_BRG; 
	
	  	if ( $idx != 0 ) 
		{
			$masbank = Masbank::where('NO_ID', $idx )->first();	
	    }
		else
		{
            $masbank = new Masbank;			 
		}

        // dd($sub);
		$data = [
					'header' => $masbank,
			    ];				
                
        return view('master_daftar_bank_pembayaran.edit', $data)->with(['tipx' => $tipx, 'idx' => $idx ]);		
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */


    public function update(Request $request, Masbank $masbank)
    {

        $this->validate(
            $request,
            [

                'KODE'       => 'required',

            ]
        );
 
        $tipx = 'edit';
		$idx = $request->idx;
        // dd($request->all(), $masbank);

        $masbank->update(
            [

                'KODE'         => ($request['KODE'] == null) ? "" : $request['KODE'],
                'TYPE'           => ($request['TYPE'] == null) ? "" : $request['TYPE'],
                'NM_BANK'         => ($request['NM_BANK'] == null) ? "" : $request['NM_BANK'],
                'NOBANK'         => ($request['NOBANK'] == null) ? "" : $request['NOBANK'],
                'BANK'        => ($request['BANK'] == null) ? "" : $request['BANK'],
                'BATAS'       => ($request['BATAS'] == null) ? "" : $request['BATAS'],
                'CR_CARD'          => ($request['CR_CARD'] == null) ? "" : $request['CR_CARD'],
                'BY_CARD'          => ($request['BY_CARD'] == null) ? "" : $request['BY_CARD'],	 
				'USRNM'          => Auth::user()->username,
                'TG_SMP'         => Carbon::now(),
            ]
        );

        ////////////////////////////////////////////////////

        // $brg = Brg::where('KD_BRG', $kd_brgx )->first();

        //  ganti 21

        //return redirect('/brg/edit/?idx=' . $brg->NO_ID . '&tipx=edit');
        // return redirect('/brg/edit/?idx=' . $Brg->NO_ID . '&tipx=edit');	
		return redirect('/bank-byr')->with('status', 'Data berhasil diupdate');
		
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    // ganti 22

    public function destroy(Request $request , Masbank $masbank)
    {

        // ganti 23
        $deleteMasbank = Masbank::find($masbank->NO_ID);

        // ganti 24

        $deleteMasbank->delete();

        // ganti 
        return redirect('/brg')->with('status', 'Data berhasil dihapus');
    }

    public function cetak(Masbank $masbank, Request $request)
    {
        $no_masbank = $masbank->KODE;

        $file     = 'bankbayar';
        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

        $query = DB::SELECT("SELECT beli.NO_BUKTI, beli.TGL, beli.KODES, beli.NAMAS, beli.TOTAL_QTY, beli.NOTES, beli.ALAMAT,
                                    beli.KOTA, belid.KD_BRG, belid.NA_BRG, belid.SATUAN, belid.QTY,
                                    belid.HARGA AS HARGA, belid.TOTAL, belid.KET,  beli.NETT, beli.USRNM, belid.PPN, belid.DPP
                            FROM beli, belid
                            WHERE beli.NO_BUKTI='$no_beli' AND beli.NO_BUKTI = belid.NO_BUKTI
                            ;
		");

                DB::SELECT("UPDATE beli SET POSTED = 1 WHERE NO_BUKTI='$no_beli';");

        $data = [];

        foreach ($query as $key => $value) {
            array_push($data, array(
                'NO_BUKTI' => $query[$key]->NO_BUKTI,
                'TGL'      => $query[$key]->TGL,
                'KODES'    => $query[$key]->KODES,
                'NAMAS'    => $query[$key]->NAMAS,
                'ALAMAT'    => $query[$key]->ALAMAT,
                'KOTA'    => $query[$key]->KOTA,
                'KG'       => $query[$key]->KG,
                'HARGA'    => $query[$key]->HARGA,
                'TOTAL'    => $query[$key]->TOTAL,
                'BAYAR'    => $query[$key]->BAYAR,
                'NOTES'    => $query[$key]->NOTES,
                // 'KD_BRG'    => "`".strval($query[$key]->KD_BRG),
                'KD_BRG'    => $query[$key]->KD_BRG,

                'NA_BRG'    => $query[$key]->NA_BRG,
                'NA_BRG'    => $query[$key]->NA_BRG,
                'SATUAN'    => $query[$key]->SATUAN,
                'QTY'    => $query[$key]->QTY,
                'DISK'    => $query[$key]->DISK,
                'NETT'    => $query[$key]->NETT,
                'KET'    => $query[$key]->KET,
                'NO_PO'    => $query[$key]->NO_PO,
                'JUDUL'    => $judul,
                'USRNM'    => $query[$key]->USRNM,
                'KALI'    => $query[$key]->KALI,
                'TPPN'    => $query[$key]->TPPN,
                'TDISK'    => $query[$key]->TDISK,
                'TDPP'    => $query[$key]->TDPP,
                'PPN'    => $query[$key]->PPN,
                'DPP'    => $query[$key]->DPP
            ));
        }

        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I");

    }
}