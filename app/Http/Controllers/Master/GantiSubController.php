<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;

use App\Models\Master\Ganti;
use App\Models\Master\Gantid;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Master\Brg;
use App\Models\Master\Sup;
use DataTables;
use Auth;
use DB;

class GantiSubController extends Controller
{   

    public function browse_barang(Request $request)
    {
        $kd_brg = $request->KD_BRG;

        $gsub = DB::table('brg')->select('KD_BRG', 'NA_BRG', 'KET_KEM', 'KET_UK')->where('KD_BRG', $kd_brg)->orderBy('KD_BRG', 'ASC')->get();
        return response()->json($gsub); 
    
	}

    public function posting($id)
    {
        $data = Ganti::find($id);

        if (!$data) {
            return redirect()->back()->with('error', 'Data tidak ditemukan.');
        }

        // Update field POSTED
        $data->POSTED = 1;
        $data->save();

        return redirect()->back()->with('success', 'Data berhasil diposting.');
    }


    public function index() {
        return view('master_ganti_sub_item.index');
    }

    public function getSub(Request $request)
    {
    
        $cbg = Auth::user()->CBG;
        $per = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];

        $sub = DB::SELECT("SELECT * FROM ganti where per='$per' and FLAG='KD' and CBG='$cbg' order by no_bukti");

        return Datatables::of($sub)
            ->addIndexColumn()
            ->addColumn('action', function($row) {

                $btnPrivilege = '';

                if (Auth::user()->divisi == "programmer" || Auth::user()->divisi == "owner" || Auth::user()->divisi == "sales") {   

                    // URL untuk masing-masing aksi
                    $urlPrint   = url("gsub/print/" . $row->NO_ID);
                    $urlPosting = url("gsub/posting/" . $row->NO_ID);
                    $urlDelete  = url("gsub/delete/" . $row->NO_ID);

                    // tombol-tombol aksi
                    $btnPrivilege = '
                        <a class="dropdown-item" href="gsub/edit/?idx=' . $row->NO_ID . '&tipx=edit">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <hr>
                        <a class="dropdown-item btn btn-danger" href="gsub/cetak/' . $row->NO_ID . '">
                            <i class="fa fa-print" aria-hidden="true"></i>
                            Print
                        </a>
                        <a class="dropdown-item" href="gsub/posting/' . $row->NO_ID . '">
                            <i class="fas fa-check"></i> Posting
                        </a>
                        <a class="dropdown-item text-danger" href="' . $urlDelete . '">
                            <i class="fa fa-trash"></i> Delete
                        </a>
                    ';
                }

                // dropdown utama
                $actionBtn = '
                    <div class="dropdown show" style="text-align: center">
                        <a class="btn btn-secondary dropdown-toggle btn-sm" href="#" role="button" 
                            id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-bars"></i>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                            <a hidden class="dropdown-item" href="gsub/show/' . $row->NO_ID . '">
                                <i class="fas fa-eye"></i> Lihat
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
                'tgl'       => 'required'

            ]

        );

        // $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
        // $cbg     = Auth::user()->CBG;
        // $usrnm   = Auth::user()->name;

        // // Generate no_bukti 
        // $noBukti = $request->no_bukti;
        // $bulan   = substr($periode, 0, 2);
        // $tahun   = substr($periode, 3, 4);

        // // Ambil counter dari notrans
        // $row = DB::table('notrans')
        //         ->where('trans', 'GNTSUB')
        //         ->where('per', $tahun)
        //         ->first();

        // $counter = $row ? $row->{'NOM'.$bulan} : 0;
        // $counter++;

        // // Update counter
        // DB::table('notrans')
        //         ->where('trans', 'GNTSUB')
        //         ->where('per', $tahun)
        //         ->update(['NOM'.$bulan => $counter]);

        // $kode2   = DB::table('toko')->where('kode', $cbg)->value('type');
        // $noUrut  = str_pad($counter, 4, '0', STR_PAD_LEFT);
        // $no_bukti = 'KD' . $tahun . $bulan . '-' . $noUrut . $kode2;

        $CBG = Auth::user()->CBG;

        $periode = session('periode.bulan') . '/' . session('periode.tahun');
        $bulan   = session('periode.bulan');
        $tahun   = substr(session('periode.tahun'), -2);

        // Ambil kode cabang dari tabel toko
        $kode2 = DB::table('toko')->where('kode', $CBG)->value('type');

        // Ambil nomor bukti terakhir
        $lastNo = DB::table('ganti')
            ->where('per', $periode)
            ->where('FLAG', 'KD')
            ->where('CBG', $CBG)
            ->max('no_bukti');

        if ($lastNo) {
            // ambil angka 4 digit di tengah (misal dari KD2510-0003A → ambil '0003')
            $lastNumber = (int) substr($lastNo, 8, 4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        $no_bukti = 'KD' . $tahun . $bulan . '-' . $newNumber . $kode2;

 
        // Insert Header

        $gsub = Ganti::create(
            [
                'no_bukti'=> $no_bukti, 
                'tgl'     => date('Y-m-d', strtotime($request['tgl'])),
                'notes'   => ($request['notes'] == null) ? "" : $request['notes'],
                'per'     => $periode,
                'FLAG'    => 'KD',
                'usrnm'   => Auth::user()->username,
                'tg_smp'  => Carbon::now(),
                'CBG'     => $CBG
            ]
        );

	    $parentID = $gsub->NO_ID;

        // Insert detail data
        $length = sizeof($request->input('rec'));

        $rec    = $request->input('rec');
        $KD_BRG   = $request->input('KD_BRG');
        $KD_BRG2  = $request->input('KD_BRG2');
        $NA_BRG  = $request->input('NA_BRG');
        $ket_uk   = $request->input('ket_uk');
        $ket_kem  = $request->input('ket_kem');

        // for ($i = 0; $i < $length; $i++) {
        //     Gantid::create([
        //         'ID'       => $parentID,
        //         'NO_BUKTI' => $no_bukti,
        //         'REC'      => $REC[$i],
        //         'PER'      => $periode,
        //         'FLAG'     => 'KD',
        //         'KD_BRG'   => $KD_BRG[$i],
        //         'KD_BRG2'  => $KD_BRG2[$i],
        //         'NA_BRG'   => $NA_BRG[$i],
        //         'KET_UK'   => $KET_UK[$i],
        //         'KET_KEM'  => $KET_KEM[$i],
        //         // 'KET'      => $KET[$i],
        //         'CBG'      => $cbg,
        //         'USRNM'    => Auth::user()->username,
        //         'TG_SMP'   => Carbon::now(),
        //     ]);
        // }

        // Check jika value detail ada/tidak
        if ($rec) {
            foreach ($rec as $key => $value) {
                // Declare new data di Model
                $detail    = new Gantid;

                // Insert ke Database
                $detail->no_bukti    = $no_bukti;
                $detail->rec         = $rec[$key];
                $detail->per         = $periode;
                $detail->CBG         = $CBG;
                $detail->FLAG        = 'KD';
				$detail->KD_BRG	     = ($KD_BRG[$key]==null) ? "" :  $KD_BRG[$key];
                $detail->KD_BRG2	 = ($KD_BRG2[$key]==null) ? "" :  $KD_BRG2[$key];
				$detail->NA_BRG	     = ($NA_BRG[$key]==null) ? "" :  $NA_BRG[$key];
				$detail->ket_uk	     = ($ket_uk[$key]==null) ? "" :  $ket_uk[$key];
                $detail->ket_kem	 = ($ket_kem[$key]==null) ? "" :  $ket_kem[$key];
                $detail->save();
            }
        }

        $no_buktix = $no_bukti;

		$gsub = Ganti::where('no_bukti', $no_buktix )->first();

        DB::SELECT("UPDATE ganti,  gantid
                            SET  gantid.ID =  ganti.NO_ID  WHERE  ganti.no_bukti =  gantid.no_bukti
							AND  ganti.no_bukti='$no_buktix';");

        return redirect('/gsub')->with('status', 'Data baru berhasil ditambahkan');	
		
	}

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    public function edit(Request $request , Ganti $gsub)
    {

        $tipx = $request->tipx;

		$idx = $request->idx;
					
        $cbg = Auth::user()->CBG;
		
		if ( $idx =='0' && $tipx=='undo'  )
	    {
			$tipx ='top';
			
		   }
		   
		if ($tipx=='search') {
			
		   	
    	   $buktix = $request->buktix;
		   
		   $bingco = DB::SELECT("SELECT NO_ID, no_bukti from ganti
		                 where no_bukti = '$buktix'						 
		                 ORDER BY no_bukti ASC  LIMIT 1" );
						 
			
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
			
		   $bingco = DB::SELECT("SELECT NO_ID, no_bukti from ganti
		                 ORDER BY no_bukti ASC  LIMIT 1" );
					 
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
			
    	   $buktix = $request->buktix;
			
		   $bingco = DB::SELECT("SELECT NO_ID, no_bukti from ganti
		                 where no_bukti < 
					    '$buktix' ORDER BY no_bukti DESC LIMIT 1" );
			

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
			
				
      	   $buktix = $request->buktix;
	   
		   $bingco = DB::SELECT("SELECT NO_ID, no_bukti from ganti
		                 where no_bukti > 
					    '$buktix' ORDER BY no_bukti ASC LIMIT 1" );
					 
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
		  
    		$bingco = DB::SELECT("SELECT NO_ID, no_bukti from ganti
		              ORDER BY no_bukti DESC  LIMIT 1" );
					 
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
			$gsub = Ganti::where('NO_ID', $idx )->first();	
            // dd($gsub->no_bukti);
	    }
		else
		{
            $gsub = new Ganti();
            $gsub->tgl = Carbon::now();			 
		}

        // dd($sub);
        $no_idx = $gsub->NO_ID;
        $detailGsub = DB::SELECT("SELECT * FROM gantid WHERE ID = '$no_idx'");
        // dd($detailGsub);
		$data = [
					'header' => $gsub,
                    'detail' => $detailGsub
			    ];			
                
        return view('master_ganti_sub_item.edit', $data)->with(['tipx' => $tipx, 'idx' => $idx ]);		
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */


    public function update(Request $request, Ganti $gsub)
    {
        // dd($request->no_bukti, $request->all());
        $this->validate(
            $request,
            [

                'tgl'       => 'required'
            ]
        );

        $CBG     = Auth::user()->CBG;
        $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
		$parentID = $gsub->NO_ID;

        $gsub->update(
            [
                'tgl'    => date('Y-m-d', strtotime($request['tgl'])),
                'notes'  => ($request['notes']==null) ? "" : $request['notes'],
                'usrnm'  => Auth::user()->username,
                'tg_smp' => Carbon::now()
            ]
        );

		$no_buktix = $request->no_bukti;
        
        // Update Detail
        $length = sizeof($request->input('rec'));
        $NO_ID  = $request->input('NO_ID');

        $rec    = $request->input('rec');
        $KD_BRG   = $request->input('KD_BRG');
        $NA_BRG  = $request->input('NA_BRG');
        $ket_uk   = $request->input('ket_uk');
        $ket_kem  = $request->input('ket_kem');
        $KD_BRG2  = $request->input('KD_BRG2');

        // Delete yang NO_ID tidak ada di input
        $query = DB::table('gantid')->where('no_bukti', $no_buktix)->whereNotIn('NO_ID',  $NO_ID)->delete();

        // Update / Insert
        for ($i = 0; $i < $length; $i++) {
            // Insert jika NO_ID baru
            if ($NO_ID[$i] == 'new') {
                $insert = Gantid::create(
                    [
                        'no_bukti'   => $request->no_bukti,
                        'rec'        => $rec[$i],
                        'per'        => $periode,
                        'FLAG'     => 'KD',
                        'KD_BRG'   => ($KD_BRG[$i]==null) ? "" :  $KD_BRG[$i],
                        'KD_BRG2'  => ($KD_BRG2[$i]==null) ? "" :  $KD_BRG2[$i],
                        'NA_BRG'   => ($NA_BRG[$i]==null) ? "" :  $NA_BRG[$i],
                        'ket_uk'   => ($ket_uk[$i]==null) ? "" :  $ket_uk[$i],
                        'ket_kem'  => ($ket_kem[$i]==null) ? "" :  $ket_kem[$i],
                        'CBG'      => $CBG,
                        'usrnm'    => Auth::user()->username,
                    ]
                );
            } else {
                // Update jika NO_ID sudah ada
                $update = Gantid::updateOrCreate(
                    [
                        'no_bukti'  => $request->no_bukti,
                        'NO_ID'     => (int) str_replace(',', '', $NO_ID[$i])
                    ],

                    [
                        'rec'        => $rec[$i],
                        'per'        => $periode,
                        'FLAG'     => 'KD',
                        'KD_BRG'   => ($KD_BRG[$i]==null) ? "" :  $KD_BRG[$i],
                        'KD_BRG2'  => ($KD_BRG2[$i]==null) ? "" :  $KD_BRG2[$i],
                        'NA_BRG'   => ($NA_BRG[$i]==null) ? "" :  $NA_BRG[$i],
                        'ket_uk'   => ($ket_uk[$i]==null) ? "" :  $ket_uk[$i],
                        'ket_kem'  => ($ket_kem[$i]==null) ? "" :  $ket_kem[$i],
                        'CBG'      => $CBG,
                        'usrnm'    => Auth::user()->username,
                    ]
                );
            }
        }
		
		$gsub = Ganti::where('no_bukti', $no_buktix )->first();
		
        $no_bukti = $gsub->no_bukti;

         DB::SELECT("UPDATE ganti,  gantid
                    SET  gantid.ID =  ganti.NO_ID  WHERE  ganti.no_bukti =  gantid.no_bukti
                    AND  ganti.no_bukti='$no_bukti';");
        
        return redirect('/gsub')->with('status', 'Data berhasil diupdate');
		
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    // ganti 22

    // public function destroy(Request $request , Ganti $gsub)
    // {

    //     DB::table('gantid')->where('ID', $gsub->NO_ID)->delete();
    //     $deleteGsub = Ganti::find($gsub->NO_ID);

    //     $deleteGsub->delete();

    //     return redirect('/gsub')->with('status', 'Data berhasil dihapus');
    // }
    public function destroy(Ganti $gsub)
    {
        DB::table('gantid')->where('ID', $gsub->NO_ID)->delete();
        $gsub->delete();

        return redirect('/gsub')->with('status', 'Data berhasil dihapus');
    }

}