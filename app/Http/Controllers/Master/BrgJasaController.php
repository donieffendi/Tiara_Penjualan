<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;

use App\Models\Master\Tpondg;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Master\Brg;
use App\Models\Master\Jasa;
use DataTables;
use Auth;
use DB;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";
use PHPJasperXML;

class BrgJasaController extends Controller
{

    public function index() 
    {
        return view('master_keperluan_barang_dan_jasa.index');
    }

    public function browse_dept(Request $request)
    {
        $jasa = DB::SELECT("SELECT KD, DEP from nddafdep ORDER BY KD ASC");
        return response()->json($jasa);
    }

    public function getBrgJasa(Request $request)
    {
        $per = session()->get('periode')['bulan'] . '/' . session()->get('periode')['tahun'];

        $sql = DB::SELECT("SELECT NO_ID, NO_BUKTI, NA_BRG, QTY, SATUAN, UKURAN, HARGA, CBG, KD_DEPT, PER, 
                                TOTAL, BATAS1, TG_PBL, AKUNT, KET, TGL, DEPT, USRNM, POSTED
                                FROM tpondg 
                                GROUP BY  NO_BUKTI ORDER BY NO_BUKTI asc ");
                                        
        return Datatables::of($sql)
                ->addIndexColumn()
                ->addColumn('action', function($row) {
					if (Auth::user()->divisi=="programmer" || Auth::user()->divisi=="owner" || Auth::user()->divisi=="sales")
					{   

                        // url untuk delete di index
                        $url = "'".url("brg-jasa/delete/" . $row->NO_ID )."'";
                        // batas
                        
                        $btnDelete = ' onclick="deleteRow('.$url.')"';
    
                        $btnPrivilege =
                            '
                                    <a class="dropdown-item" href="brg-jasa/edit/?idx=' . $row->NO_ID . '&tipx=edit";
                                    <i class="fas fa-edit"></i>
                                        Edit
                                    </a>
                                    <a class="dropdown-item btn btn-danger" href="brg-jasa/print/' . $row->NO_BUKTI . '">
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
                                <a hidden class="dropdown-item" href="brg-jasa/show/' . $row->NO_ID . '">
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
                'TGL'       => 'required'
            ]

        );

        // $no_bukti = $request->no_bukti;
        $CBG = Auth::user()->CBG;
        $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];

 
        // buat no bukti
        $monthstring = substr($periode, 0, 2);
        $kode = 'ND' . substr($periode, -2) . substr($periode, 0, 2);

        // ambil type dari toko
        $rowToko = DB::table('toko')->where('KODE', $CBG)->first();
        $kode2   = $rowToko ? $rowToko->TYPE : '';

        // ambil nomor running dari notrans
        $rowNotrans = DB::table('notrans')
                    ->select(DB::raw("NOM{$monthstring} as NO_BUKTI"))
                    ->where('trans', 'TJASA')
                    ->where('PER', substr($periode, -4))
                    ->first();

        $r1 = ($rowNotrans ? $rowNotrans->NO_BUKTI : 0) + 1;

        DB::table('notrans')
                    ->where('trans', 'TJASA')
                    ->where('PER', substr($periode, -4))
                    ->update(["NOM{$monthstring}" => $r1]);

        $bkt1 = str_pad($r1, 4, "0", STR_PAD_LEFT);
        $no_buktix = $kode . '-' . $bkt1 . $kode2;

        // Insert 

        $length = sizeof($request->input('REC'));

        $REC    = $request->input('REC');
        $TGL   = $request->input('TGL');
        $NOTES    = $request->input('NOTES');
        $KD_DEPT  = $request->input('KD_DEPT');
        $DEPT  = $request->input('DEPT');
        $NA_BRG   = $request->input('NA_BRG');
        $QTY    = $request->input('QTY');
        $SATUAN  = $request->input('SATUAN');
        $UKURAN   = $request->input('UKURAN');
        $MERK    = $request->input('MERK');
        $HARGA  = $request->input('HARGA');
        $TOTAL   = $request->input('TOTAL');
        $BATAS1    = $request->input('BATAS1');
        $AKUNT  = $request->input('AKUNT');
                

        for ($i = 0; $i < $length; $i++) {
            Tpondg::create([
                'NO_BUKTI' => $no_buktix,
                'REC'      => $REC[$i],
                'TGL'            => date('Y-m-d', strtotime($TGL[$i])), 
                'NOTES'          => $NOTES[$i] ?? '', 
                'KD_DEPT'        => $KD_DEPT[$i] ?? '', 
                'DEPT'           => $DEPT[$i] ?? '', 
                'PER'            => $periode,
                'CBG'            => $CBG, 
                'NA_BRG'         => $NA_BRG[$i] ?? '', 
                'QTY'            => (float) str_replace(',', '', $QTY[$i]), 
                'SATUAN'         => $SATUAN[$i] ?? '', 
                'UKURAN'         => $UKURAN[$i] ?? '', 
                'MERK'           => $MERK[$i] ?? '', 
                'HARGA'          => (float) str_replace(',', '', $HARGA[$i]), 
                'TOTAL'          => (float) str_replace(',', '', $TOTAL[$i]), 
                'BATAS1'         => date('Y-m-d', strtotime($BATAS1[$i])), 
                'AKUNT'          => $AKUNT[$i] ?? '', 
                'KET'            => 'JASA', 
                'USRNM'          => Auth::user()->username,
                'TG_SMP'         => Carbon::now(),
            ]);
        }

        
        DB::table('tpondg')
            ->where('NO_BUKTI', $no_buktix)
            ->update([
                'TGL'     => date('Y-m-d', strtotime($request['TGL'])),
                'NOTES'   => $request['NOTES'] ?? '',
                'KD_DEPT' => $request['KD_DEPT'] ?? '',
                'DEPT'    => $request['DEPT'] ?? '',
            ]);

        // \Log::info('update brg jasa : ', [$brgJasa]);
	    return redirect('/brg-jasa')->with('status', 'Data baru berhasil ditambahkan');	
		
	}

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    

    // ganti 15

    public function edit(Request $request , Tpondg $brgJasa)
    {

        $tipx = $request->tipx;

		$idx = $request->idx;
		
		if ( $idx =='0' && $tipx=='undo'  )
	    {
			$tipx ='top';
			
		   }
		   
		if ($tipx=='search') {
			
		   	
    	   $kodex = $request->kodex;
		   
		   $bingco = DB::SELECT("SELECT NO_ID, KD_DEPT from tpondg
		                 where KD_DEPT = '$kodex'						 
		                 ORDER BY KD_DEPT ASC  LIMIT 1" );
						 
			
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
			
		   $bingco = DB::SELECT("SELECT NO_ID, KD_DEPT from tpondg
		                 ORDER BY KD_DEPT ASC  LIMIT 1" );
					 
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
			
		   $bingco = DB::SELECT("SELECT NO_ID, KD_DEPT from tpondg
		                 where KD_DEPT < 
					 '$kodex' ORDER BY KD_DEPT DESC LIMIT 1" );
			

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
	   
		   $bingco = DB::SELECT("SELECT NO_ID, KD_DEPT from tpondg
		                where KD_DEPT > 
					    '$kodex' ORDER BY KD_DEPT ASC LIMIT 1" );
					 
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
		  
    		$bingco = DB::SELECT("SELECT NO_ID, KD_DEPT from tpondg
		              ORDER BY KD_DEPT DESC  LIMIT 1" );
					 
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
			$brgJasa = Tpondg::where('NO_ID', $idx )->first();	
	    }
		else
		{
            $brgJasa = new Tpondg();
            $brgJasa->TGL = Carbon::now();			 
		}

        // dd($sub);
        $buktix = $brgJasa->NO_BUKTI;
        $detailBrg = DB::SELECT("SELECT * FROM tpondg WHERE NO_BUKTI = '$buktix'");
		$data = [
					'header' => $brgJasa,
                    'detail' => $detailBrg
			    ];		
                
        return view('master_keperluan_barang_dan_jasa.edit', $data)->with(['tipx' => $tipx, 'idx' => $idx ]);		
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */


    public function update(Request $request, Tpondg $brgJasa)
    {

        $this->validate(
            $request,
            [
                'NO_BUKTI'       => 'required'
            ]
        );

		
        $CBG = Auth::user()->CBG;
        $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];

        $tipx = 'edit';
		$idx = $request->idx;

        // $brgJasa->update(
        //     [
        //         'TGL'              => date('Y-m-d', strtotime($request['TGL'])),
        //         'NOTES'          => ($request['NOTES'] == null) ? "" : $request['NOTES'],
        //         'KD_DEPT'        => ($request['KD_DEPT'] == null) ? "" : $request['KD_DEPT'], 
        //         'DEPT'           => ($request['DEPT'] == null) ? "" : $request['DEPT'], 
                        
        //     ]
        // );
        
        $no_buktix = $request->NO_BUKTI;
		
        // Update Detail
        $length = sizeof($request->input('REC'));
        $NO_ID  = $request->input('NO_ID');

        $REC    = $request->input('REC');
        // $TGL     = $request->input('TGL');
        // $NOTES   = $request->input('NOTES');
        // $KD_DEPT = $request->input('KD_DEPT');
        // $DEPT    = $request->input('DEPT');
        $NA_BRG  = $request->input('NA_BRG');
        $QTY     = $request->input('QTY');
        $SATUAN  = $request->input('SATUAN');
        $UKURAN  = $request->input('UKURAN');
        $MERK    = $request->input('MERK');
        $HARGA   = $request->input('HARGA');
        $TOTAL   = $request->input('TOTAL');
        $BATAS1  = $request->input('BATAS1');
        $AKUNT   = $request->input('AKUNT');


        // dd($NACC);

        // Delete yang NO_ID tidak ada di input
        $query = DB::table('tpondg')->where('NO_BUKTI', $no_buktix)->whereNotIn('NO_ID',  $NO_ID)->delete();

        // Update / Insert
        for ($i = 0; $i < $length; $i++) {
            // Insert jika NO_ID baru
            if ($NO_ID[$i] == 'new') {
                $insert = Tpondg::create(
                    [ 
                        'NO_BUKTI'   => $no_buktix,
                        'REC'        => $REC[$i],
                        'PER'            => $periode,
                        'CBG'            => $CBG, 
                        'NA_BRG'         => $NA_BRG[$i] ?? '', 
                        'QTY'            => (float) str_replace(',', '', $QTY[$i]), 
                        'SATUAN'         => $SATUAN[$i] ?? '', 
                        'UKURAN'         => $UKURAN[$i] ?? '', 
                        'MERK'           => $MERK[$i] ?? '', 
                        'HARGA'          => (float) str_replace(',', '', $HARGA[$i]), 
                        'TOTAL'          => (float) str_replace(',', '', $TOTAL[$i]), 
                        'BATAS1'         => date('Y-m-d', strtotime($BATAS1[$i])), 
                        'AKUNT'          => $AKUNT[$i] ?? '', 
                        'KET'            => 'JASA', 
                        'USRNM'          => Auth::user()->username,
                        'TG_SMP'         => Carbon::now(),
                    ]
                );
            } else {
                // Update jika NO_ID sudah ada
                $update = Tpondg::updateOrCreate(
                    [
                        'NO_BUKTI'  => $no_buktix,
                        'NO_ID'     => (int) str_replace(',', '', $NO_ID[$i])
                    ],

                    [
                        'REC'        => $REC[$i],
                        'PER'            => $periode,
                        'CBG'            => $CBG, 
                        'NA_BRG'         => $NA_BRG[$i] ?? '', 
                        'QTY'            => (float) str_replace(',', '', $QTY[$i]), 
                        'SATUAN'         => $SATUAN[$i] ?? '', 
                        'UKURAN'         => $UKURAN[$i] ?? '', 
                        'MERK'           => $MERK[$i] ?? '', 
                        'HARGA'          => (float) str_replace(',', '', $HARGA[$i]), 
                        'TOTAL'          => (float) str_replace(',', '', $TOTAL[$i]), 
                        'BATAS1'         => date('Y-m-d', strtotime($BATAS1[$i])), 
                        'AKUNT'          => $AKUNT[$i] ?? '', 
                        'KET'            => 'JASA', 
                        'USRNM'          => Auth::user()->username,
                        'TG_SMP'         => Carbon::now(),
                    ]
                );
            }
        }

        // update semua row di detail tpondg dengan header yang sama
        DB::table('tpondg')
            ->where('NO_BUKTI', $no_buktix)
            ->update([
                'TGL'     => date('Y-m-d', strtotime($request['TGL'])),
                'NOTES'   => $request['NOTES'] ?? '',
                'KD_DEPT' => $request['KD_DEPT'] ?? '',
                'DEPT'    => $request['DEPT'] ?? '',
            ]);

        return redirect('/brg-jasa')->with('status', 'Data berhasil diupdate');
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

    public function Print(Jasa $jasa)
    {
        $no_jasa = $jasa->NO_BUKTI;

        $file     = 'Print_BarangJasa';
        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

        $query = DB::SELECT("SELECT tpondg.NO_BUKTI,tpondg.NA_BRG,tpondg.QTY,tpondg.SATUAN,tpondg.UKURAN,tpondg.HARGA,tpondg.CBG,tpondg.KD_DEPT,
                                    tpondg.MERK,tpondg.TOTAL,tpondg.BATAS1,tpondg.TG_PBL,tpondg.AKUNT,tpondg.KET,tpondg.TGL
                                FROM tpondg
                                WHERE tpondg.NO_BUKTI='$no_jasa'
		");

        $data = [];

        foreach ($query as $key => $value) {
            array_push($data, array(
                'NO_BUKTI' => $query[$key]->NO_BUKTI,
                'TGL'      => $query[$key]->TGL,
                'NA_BRG'    => $query[$key]->NA_BRG,
                'SATUAN'    => $query[$key]->SATUAN,
                'UKURAN'    => $query[$key]->UKURAN,
                'HARGA'    => $query[$key]->HARGA,
                'CBG'    => $query[$key]->CBG,
                'KD_DEPT'    => $query[$key]->KD_DEPT,
                'MERK'    => $query[$key]->MERK,
                'QTY'    => $query[$key]->QTY,
                'TOTAL'    => $query[$key]->TOTAL,
                'BATAS1'    => $query[$key]->BATAS1,
                'TG_PBL'    => $query[$key]->TG_PBL,
                'AKUNT'    => $query[$key]->AKUNT,
                'KET'    => $query[$key]->KET
            ));
        }

        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I");

    }

    public function PrintLap(Request $request)
    {   
        // $user = Auth::user()->username;
        $user = 'EKA SIM';

        $file = 'PrintLap_BarangJasa'; // ubah sesuai nama file .jrxml kamu, misalnya 'brg_list.jrxml'
        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

        $query = DB::SELECT("SELECT tpondg.NO_BUKTI,tpondg.NA_BRG,tpondg.QTY,tpondg.SATUAN,tpondg.UKURAN,tpondg.HARGA,tpondg.CBG,tpondg.KD_DEPT,
                 tpondg.TOTAL,tpondg.BATAS1,tpondg.TG_PBL,tpondg.AKUNT,tpondg.KET,tpondg.TGL,tpondg.dept
                 from tpondg
                 where USRNM='$user' 
                --  AND KET='JASA'
		");
    
        $data = [];
        foreach ($query as $key => $value) {
            array_push($data, array(
                'NO_BUKTI' => $query[$key]->NO_BUKTI,
                'TGL'      => $query[$key]->TGL,
                'NA_BRG'    => $query[$key]->NA_BRG,
                'SATUAN'    => $query[$key]->SATUAN,
                'UKURAN'    => $query[$key]->UKURAN,
                'HARGA'    => $query[$key]->HARGA,
                'CBG'    => $query[$key]->CBG,
                'KD_DEPT'    => $query[$key]->KD_DEPT,
                'MERK'    => $query[$key]->MERK,
                'QTY'    => $query[$key]->QTY,
                'TOTAL'    => $query[$key]->TOTAL,
                'BATAS1'    => $query[$key]->BATAS1,
                'TG_PBL'    => $query[$key]->TG_PBL,
                'AKUNT'    => $query[$key]->AKUNT,
                'KET'    => $query[$key]->KET
            ));
        }

        // Kirim data ke Jasper
        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I"); // "I" artinya inline (tampil di browser)
    }

}
