<?php

namespace App\Http\Controllers\FTransaksi;

use App\Http\Controllers\Controller;
// ganti 1

use App\Models\FTransaksi\Memo;
use App\Models\FTransaksi\MemoDetail;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use DB;
use Carbon\Carbon;



include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;



// ganti 2
class MemoController extends Controller
{

	var $judul = '';
    var $FLAGZ = '';


    function setFlag(Request $request)
    {
        if ( $request->flagz == 'M' ) {
            $this->judul = "Journal Penyesuaian";
        } 
		
        $this->FLAGZ = $request->flagz;


    }	 
	
    public function index(Request $request)
    {

        // ganti 3
        $this->setFlag($request);
        // ganti 3
        return view('ftransaksi_memo.index')->with(['judul' => $this->judul, 'flagz' => $this->FLAGZ ]);


    }

    // ganti 4

    public function getMemo(Request $request)
    {
        // ganti 5

        if ($request->session()->has('periode')) {
            $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
        } else {
            $periode = '';
        }

        //   $po = DB::table('po')->select('*')->where('PER', $periode)->where('GOL', 'Y')->orderBy('NO_PO', 'ASC')->get();

        $this->setFlag($request);

		$CBG = Auth::user()->CBG;

        $memo = DB::SELECT("SELECT * from memo  where  PER ='$periode' and FLAG ='$this->FLAGZ' AND CBG='$CBG' ORDER BY NO_BUKTI ");
	  
        // ganti 6

        return Datatables::of($memo)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                if (Auth::user()->divisi=="programmer" || Auth::user()->divisi=="owner" || Auth::user()->divisi=="assistant" || Auth::user()->divisi=="accounting") 
                {
                    
                    // url untuk delete di index
                    $url = "'".url("memo/delete/" . $row->NO_ID . "/?flagz=" . $row->FLAG)."'";
                    // batas

                    $btnEdit =   ($row->POSTED == 1) ? ' onclick= "alert(\'Transaksi ' . $row->NO_BUKTI . ' sudah diposting!\')" href="#" ' : ' href="memo/edit/?idx=' . $row->NO_ID . '&tipx=edit&flagz=' . $row->FLAG . '&judul=' . $this->judul . '"';					
                    $btnDelete = ($row->POSTED == 1) ? ' onclick= "alert(\'Transaksi ' . $row->NO_BUKTI . ' sudah diposting!\')" href="#" ' : ' onclick="deleteRow('.$url.')" ';
                    // $btnDelete = ($row->POSTED == 1) ? ' onclick= "alert(\'Transaksi ' . $row->NO_BUKTI . ' sudah diposting!\')" href="#" ' : ' onclick="return confirm(&quot; Apakah anda yakin ingin hapus? &quot;)" href="memo/delete/' . $row->NO_ID . '/?flagz=' . $row->FLAG . '" ';

                    $btnPrivilege =
                        '
                                <a class="dropdown-item" ' . $btnEdit . '>
                                <i class="fas fa-edit"></i>
                                    Edit
                                </a>
                                <a class="dropdown-item btn btn-danger" href="memo/cetak/' . $row->NO_ID . '">
                                    <i class="fa fa-trash" aria-hidden="true"></i>
                                    Print
                                </a> 									
                                <hr></hr>
                                <a class="dropdown-item btn btn-danger"  ' . $btnDelete . '>
   
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
                'NO_BUKTI'       => 'required',
                'TGL'      => 'required'

            ]
        );

		$this->setFlag($request);
        $FLAGZ = $this->FLAGZ;
        $judul = $this->judul;	
		
        $CBG = Auth::user()->CBG;

        $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];

        $bulan    = session()->get('periode')['bulan'];
        $tahun    = substr(session()->get('periode')['tahun'], -2);
        $query = DB::table('memo')->select('NO_BUKTI')->where('PER', $periode)->where('FLAG', 'M')->where('CBG', $CBG)->orderByDesc('NO_BUKTI')->limit(1)->get();

        if ($query != '[]') {
            $query = substr($query[0]->NO_BUKTI, -4);
            $query = str_pad($query + 1, 4, 0, STR_PAD_LEFT);
            $no_bukti = 'M' . $CBG . $tahun . $bulan . '-' . $query;
        } else {
            $no_bukti = 'M' . $CBG . $tahun . $bulan . '-0001';
        }


        // Insert Header

        // ganti 10

        $memo = Memo::create(
            [
                'NO_BUKTI'         => $no_bukti,
                'TGL'              => date('Y-m-d', strtotime($request['TGL'])),
                'PER'              => $periode,
                'KET'              => ($request['KET'] == null) ? "" : $request['KET'],
                'FLAG'             => 'M',
                'JUMLAH'           => (float) str_replace(',', '', $request['TJUMLAH']),
                'DEBET'            => (float) str_replace(',', '', $request['TJUMLAH']),
                'KREDIT'           => (float) str_replace(',', '', $request['TJUMLAH']),
                'USRNM'            => Auth::user()->username,
                'created_by'       => Auth::user()->username,
                'CBG'              => $CBG,
                'TG_SMP'           => Carbon::now()
            ]
        );

        // Insert Detail
        $REC    = $request->input('REC');
        $ACNO    = $request->input('ACNO');
        $NACNO    = $request->input('NACNO');
        $ACNOB    = $request->input('ACNOB');
        $NACNOB    = $request->input('NACNOB');
        $DEBET    = $request->input('DEBET');
        $KREDIT    = $request->input('KREDIT');
        $URAIAN    = $request->input('URAIAN');
        $JUMLAH    = $request->input('JUMLAH');
        // Check jika value detail ada/tidak
        if ($REC) {
            foreach ($REC as $key => $value) {
                // Declare new data di Model
                $detail    = new MemoDetail;

                // Insert ke Database
                $detail->NO_BUKTI = $no_bukti;
                $detail->REC    = $REC[$key];
                $detail->PER    = $periode;
                $detail->FLAG    = 'M';
                $detail->ACNO    =($ACNO[$key] == null) ? "" :  $ACNO[$key];
                $detail->NACNO    = ($NACNO[$key] == null) ? "" :  $NACNO[$key];
                $detail->ACNOB    = ($ACNOB[$key] == null) ? "" :  $ACNOB[$key];
                $detail->NACNOB    = ($NACNOB[$key] == null) ? "" :  $NACNOB[$key];
                $detail->URAIAN    = ($URAIAN[$key] == null) ? "" :  $URAIAN[$key];
                $detail->JUMLAH    = (float) str_replace(',', '', $JUMLAH[$key]);
                $detail->DEBET    = (float) str_replace(',', '', $JUMLAH[$key]);
                $detail->KREDIT    = (float) str_replace(',', '', $JUMLAH[$key]);
                $detail->save();
            }
        }

        //  ganti 11
        $variablell = DB::select('call memoins(?)', array($no_bukti));


	    $no_buktix = $no_bukti;
		
		$memo = Memo::where('NO_BUKTI', $no_buktix )->first();

        DB::SELECT("UPDATE MEMO, MEMOD
                            SET MEMOD.ID = MEMO.NO_ID  WHERE MEMO.NO_BUKTI = MEMOD.NO_BUKTI 
							AND MEMO.NO_BUKTI='$no_buktix';");
							
        //return redirect('/memo/edit/?idx=' . $memo->NO_ID . '&tipx=edit&flagz=' . $this->FLAGZ . '&judul=' . $this->judul . '');
		return redirect('/memo?flagz='.$FLAGZ)->with(['judul' => $judul, 'flagz' => $FLAGZ ]);
    }


   

    // ganti 15

    public function edit(Request $request, Memo $memo)
    {

		$per = session()->get('periode')['bulan'] . '/' . session()->get('periode')['tahun'];
        $cekperid = DB::SELECT("SELECT POSTED from perid WHERE PERIO='$per'");
        if ($cekperid[0]->POSTED==1)
        {
            return redirect('/memo')->with('status', 'Maaf Periode sudah ditutup!');
        }
		

		$this->setFlag($request);

        $tipx = $request->tipx;

		$idx = $request->idx;
			
        $CBG = Auth::user()->CBG;		
		
		if ( $idx =='0' && $tipx=='undo'  )
	    {
			$tipx ='top';
			
		   }
		   
		   
		
		if ($tipx=='top') {
			
		   	
		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from MEMO 
		                 where PER ='$per' and FLAG ='$this->FLAGZ'     
		                 and CBG = '$CBG' ORDER BY NO_BUKTI ASC  LIMIT 1" );
						 
					
		
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
			
		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from memo      
		             where PER ='$per' and FLAG ='$this->FLAGZ' and NO_BUKTI < 
					 '$buktix' and CBG = '$CBG'
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
			
				
      	   $buktix = $request->buktix;
	   
		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from memo    
		             where PER ='$per' and FLAG ='$this->FLAGZ' and NO_BUKTI > 
					 '$buktix' and CBG = '$CBG'
                     ORDER BY NO_BUKTI ASC LIMIT 1" );
					 
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
		  
    		$bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from memo  
			            where PER ='$per' and FLAG ='$this->FLAGZ'   
		                and CBG = '$CBG' ORDER BY NO_BUKTI DESC  LIMIT 1" );
					 
			if(!empty($bingco)) 
			{
				$idx = $bingco[0]->NO_ID;
			  }
			else
			{
				$idx = 0; 
			  }
			  
			
		}

        
		if ( $tipx=='undo'  )
	    {
        
			$tipx ='edit';
			
		   }

		

       	if ( $idx != 0 ) 
		{
			$memo = memo::where('NO_ID', $idx )->first();	
	     }
		 else
		 {
				$memo = new Memo;
                $memo->TGL = Carbon::now();
      
				
		 }

 
		
        $no_bukti = $memo->NO_BUKTI;
				
        $memoDetail = DB::table('memod')->where('NO_BUKTI', $no_bukti)->get();
        $data = [
            'header'        => $memo,
            'detail'        => $memoDetail
        ];
 
         
         return view('ftransaksi_memo.edit', $data)
		 ->with(['tipx' => $tipx, 'idx' => $idx, 'flagz' =>$this->FLAGZ, 'judul'=> $this->judul ]);
	

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    // ganti 18

    public function update(Request $request, Memo $memo)
    {

        $this->validate(
            $request,
            [

                // ganti 10

                'TGL'      => 'required'
            ]
        );


		$this->setFlag($request);
        $FLAGZ = $this->FLAGZ;
        $judul = $this->judul;	
		
        // ganti 20
        $variablell = DB::select('call memodel(?)', array($memo['NO_BUKTI']));

        $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];

        $CBG = Auth::user()->CBG;

        $memo->update(
            [

                'TGL'              => date('Y-m-d', strtotime($request['TGL'])),
                'JUMLAH'           => (float) str_replace(',', '', $request['TJUMLAH']),
                'DEBET'            => (float) str_replace(',', '', $request['TJUMLAH']),
                'KREDIT'           => (float) str_replace(',', '', $request['TJUMLAH']),
                'KET'              => ($request['KET'] == null) ? "" : $request['KET'],
                'USRNM'            => Auth::user()->username,
                'updated_by'       => Auth::user()->username,
                'CBG'              => $CBG,
                'TG_SMP'           => Carbon::now()
            ]
        );

		$no_buktix = $memo->NO_BUKTI;
		
        // Update Detail
        $length = sizeof($request->input('REC'));
        $NO_ID  = $request->input('NO_ID');

        $REC    = $request->input('REC');
        $ACNO   = $request->input('ACNO');
        $NACNO  = $request->input('NACNO');
        $ACNOB   = $request->input('ACNOB');
        $NACNOB  = $request->input('NACNOB');
        $URAIAN = $request->input('URAIAN');
        $JUMLAH = $request->input('JUMLAH');


        // Delete yang NO_ID tidak ada di input       
        $query = DB::table('memod')->where('NO_BUKTI', $request->NO_BUKTI)->whereNotIn('NO_ID',  $NO_ID)->delete();

        // Update / Insert
        for ($i = 0; $i < $length; $i++) {
            // Insert jika NO_ID baru
            if ($NO_ID[$i] == 'new') {
                $insert = MemoDetail::create(
                    [
                        'NO_BUKTI'   => $no_buktix,
                        'REC'        => $REC[$i],
                        'PER'        => $periode,
                        'FLAG'       => 'M',
                        'ACNO'       => ($ACNO[$i] == null) ? "" :  $ACNO[$i],
                        'NACNO'      => ($NACNO[$i] == null) ? "" : $NACNO[$i],
                        'ACNOB'      => ($ACNOB[$i] == null) ? "" :  $ACNOB[$i],
                        'NACNOB'     => ($NACNOB[$i] == null) ? "" : $NACNOB[$i],
                        'URAIAN'     => ($URAIAN[$i] == null) ? "" : $URAIAN[$i],
                        'JUMLAH'     => (float) str_replace(',', '', $JUMLAH[$i]),
                        'DEBET'      => (float) str_replace(',', '', $JUMLAH[$i]),
                        'KREDIT'      => (float) str_replace(',', '', $JUMLAH[$i]),

                    ]
                );
            } else {
                // Update jika NO_ID sudah ada
                $update = MemoDetail::updateOrCreate(
                    [
                        'NO_BUKTI'  => $no_buktix,
                        'NO_ID'     => (int) str_replace(',', '', $NO_ID[$i])
                    ],

                    [
                        'REC'        => $REC[$i],
                        'ACNO'       => ($ACNO[$i] == null) ? "" :  $ACNO[$i],
                        'NACNO'      => ($NACNO[$i] == null) ? "" : $NACNO[$i],
                        'ACNOB'       => ($ACNOB[$i] == null) ? "" :  $ACNOB[$i],
                        'NACNOB'      => ($NACNOB[$i] == null) ? "" : $NACNOB[$i],
                        'URAIAN'     => ($URAIAN[$i] == null) ? "" : $URAIAN[$i],
                        'JUMLAH'     => (float) str_replace(',', '', $JUMLAH[$i]),
                        'DEBET'      => (float) str_replace(',', '', $JUMLAH[$i]),
                        'KREDIT'      => (float) str_replace(',', '', $JUMLAH[$i]),

                    ]
                );
            }
        }



        //  ganti 21
        $variablell = DB::select('call memoins(?)', array($memo['NO_BUKTI']));

		$memo = Memo::where('NO_BUKTI', $no_buktix )->first();

        DB::SELECT("UPDATE MEMO, MEMOD
                            SET MEMOD.ID = MEMO.NO_ID  WHERE MEMO.NO_BUKTI = MEMOD.NO_BUKTI 
							AND MEMO.NO_BUKTI='$no_buktix';");
							
        //return redirect('/memo/edit/?idx=' . $memo->NO_ID . '&tipx=edit&flagz=' . $this->FLAGZ . '&judul=' . $this->judul . '');
		return redirect('/memo?flagz='.$FLAGZ)->with(['judul' => $judul, 'flagz' => $FLAGZ ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    // ganti 22

    public function destroy( Request $request, Memo $memo)
    {

		$this->setFlag($request);
        $FLAGZ = $this->FLAGZ;
        $judul = $this->judul;
		
		$per = session()->get('periode')['bulan'] . '/' . session()->get('periode')['tahun'];
        $cekperid = DB::SELECT("SELECT POSTED from perid WHERE PERIO='$per'");
        if ($cekperid[0]->POSTED==1)
        {
            return redirect('/memo')->with('status', 'Maaf Periode sudah ditutup!');
        }
		
        $variablell = DB::select('call memodel(?)', array($memo['NO_BUKTI']));


        // ganti 23
        $deleteMemo = Memo::find($memo->NO_ID);

        // ganti 24

        $deleteMemo->delete();

        // ganti 
        // ganti 
		return redirect('/memo?flagz='.$FLAGZ)->with(['judul' => $judul, 'flagz' => $FLAGZ ])->with('statusHapus', 'Data '.$memo->NO_BUKTI.' berhasil dihapus');


    }

    public function cetak (Memo $memo)
    {
        $no_bukti = $memo->NO_BUKTI;

        $file     = 'memoc';
        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

        $query = DB::SELECT("
			SELECT memo.NO_BUKTI,memo.TGL,memo.KET,
            memod.REC,memod.ACNO,memod.NACNO,memod.URAIAN,
            memod.JUMLAH as DEBET, memod.JUMLAH as KREDIT
			FROM memo, memod 
			WHERE memo.NO_BUKTI=memod.NO_BUKTI and memo.NO_BUKTI='$no_bukti' 
			ORDER BY memo.NO_BUKTI;
		");
		
		$query2 = DB::SELECT("
			SELECT NAMA from compan ;
		");

        $data = [];
        foreach ($query as $key => $value) {
            array_push($data, array(
                'NO_BUKTI' => $query[$key]->NO_BUKTI,
                'TGL' => $query[$key]->TGL,
                'KET' => $query[$key]->KET,
                'BNAMA' => $query[$key]->BNAMA,
                'REC' => $query[$key]->REC,
                'ACNO' => $query[$key]->ACNO,
                'NACNO' => $query[$key]->NACNO,
                'URAIAN' => $query[$key]->URAIAN,
                'DEBET' => $query[$key]->DEBET,
                'KREDIT' => $query[$key]->KREDIT,
				'NAMA' => $query2[0]->NAMA
            ));
        }
        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I");
    }

    public function getDetailMemo(){

        $no_bukti = $_GET['no_bukti'];
        $result = DB::table('memod')->where('NO_BUKTI', $no_bukti)->get();
        
        return response()->json($result);;
    }


}
