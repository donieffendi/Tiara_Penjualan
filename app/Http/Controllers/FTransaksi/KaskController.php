<?php

namespace App\Http\Controllers\FTransaksi;

use App\Http\Controllers\Controller;
// ganti 1

use App\Models\FTransaksi\Kas;
use App\Models\FTransaksi\KasDetail;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use DB;
use Carbon\Carbon;

include_once base_path()."/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";
use PHPJasperXML;



// ganti 2
class KaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
		
// ganti 3
        return view('ftransaksi_kask.index');
    }

// ganti 4

    public function getKask(Request $request)
    {
// ganti 5

		if ($request->session()->has('periode')) 
		{
			$periode = $request->session()->get('periode')['bulan']. '/' . $request->session()->get('periode')['tahun'];
		} else
		{
			$periode = '';
		}

           $kask = DB::table('kas')->select('*')->where('PER', $periode)->where('TYPE', 'BKK')->orderBy('NO_BUKTI', 'ASC')->get();
	 		
// ganti 6
		
         return Datatables::of($kask)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                if (Auth::user()->hasRole("superadmin") || Auth::user()->hasRole("operational")) {

                    $btnEdit = ($row->POSTED == 1) ? ' onclick= "alert(\'Transaksi ' . $row->NO_BUKTI . ' sudah diposting!\')" href="#" ' : ' href="kask/edit/' . $row->NO_ID . '" ';
                    $btnDelete = ($row->POSTED == 1) ? ' onclick= "alert(\'Transaksi ' . $row->NO_BUKTI . ' sudah diposting!\')" href="#" ' : ' href="kask/delete/' . $row->NO_ID . '" ';

                    $btnPrivilege =
                        '
                                <a class="dropdown-item" ' . $btnEdit . '>
                                <i class="fas fa-edit"></i>
                                    Edit
                                </a>
                                <a class="dropdown-item btn btn-danger" href="kask/print/' . $row->NO_ID . '">
                                    <i class="fa fa-trash" aria-hidden="true"></i>
                                    Print
                                </a> 									
                                <hr></hr>
                                <a class="dropdown-item btn btn-danger" ' . $btnDelete . '>
                                    <i class="fa fa-trash" aria-hidden="true"></i>
                                    Delete
                                </a> 
                        ';
                    } 
                    else
                    {
                        $btnPrivilege = '';
                    }

                    $actionBtn = 
                    '
                    <div class="dropdown show" style="text-align: center">
                        <a class="btn btn-secondary dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-bars"></i>
                        </a>

                        <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                            <a class="dropdown-item" href="kask/show/'. $row->NO_ID .'">
                            <i class="fas fa-eye"></i>
                                Lihat
                            </a>

                            '.$btnPrivilege.'
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
    public function create()
    {
 
 // ganti 8
        return view('ftransaksi_kask.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        
        $this->validate($request,
// GANTI 9

        [
                'NO_BUKTI'       => 'required',
                'TGL'      => 'required',
                'BACNO'       => 'required'

            ]
        );



/////////// NOMER OTOMATIS
        $periode = $request->session()->get('periode')['bulan']. '/' . $request->session()->get('periode')['tahun'];
		
        $bulan	= session()->get('periode')['bulan'];
		$tahun	= substr(session()->get('periode')['tahun'],-2);
		$query = DB::table('kas')->select('NO_BUKTI')->where('PER', $periode)->where('TYPE', 'BKK')->orderByDesc('NO_BUKTI')->limit(1)->get();
		
		if ($query != '[]')
        {
            $query = substr($query[0]->NO_BUKTI, -4);
            $query = str_pad($query + 1, 4, 0, STR_PAD_LEFT);
            $no_bukti = 'BKK'.$tahun.$bulan.'-'.$query;
        } else {
            $no_bukti = 'BKK'.$tahun.$bulan.'-0001';
        }


        // Insert Header

// ganti 10

         $kask = Kas::create(
            [
                'NO_BUKTI'         => $no_bukti,
                'TGL'              => date('Y-m-d', strtotime($request['TGL'])),		
                'PER'              => $periode,
                'BACNO'            => ($request['BACNO']==null) ? "" : $request['BACNO'],			
                'BNAMA'            => ($request['BNAMA']==null) ? "" : $request['BNAMA'],
				'FLAG'             => 'K',
                'TYPE'      	   => 'BKK',
				'KET'              => ($request['KET']==null) ? "" : $request['KET'],			
                'JUMLAH'           => (float) str_replace(',', '', $request['TJUMLAH']),
				'USRNM'            => Auth::user()->username,
				'TG_SMP'           => Carbon::now()
            ]
        );
	// Insert Detail
		$REC	= $request->input('REC');
		$ACNO	= $request->input('ACNO');
		$NACNO	= $request->input('NACNO');
		$URAIAN	= $request->input('URAIAN');
		$JUMLAH	= $request->input('JUMLAH');

		// Check jika value detail ada/tidak
		if ($REC) {
			foreach ($REC as $key => $value) {
				// Declare new data di Model
				$detail	= new KasDetail;
				
				// Insert ke Database
				$detail->NO_BUKTI = $no_bukti;			
				$detail->REC	= $REC[$key];
				$detail->PER	= $periode;
				$detail->FLAG	= 'K';
				$detail->TYPE	= 'BKK';
				$detail->ACNO	= $ACNO[$key];
				$detail->NACNO	= $NACNO[$key];
				$detail->URAIAN	= $URAIAN[$key];
				$detail->JUMLAH	= (float) str_replace(',', '', $JUMLAH[$key]);
				$detail->KREDIT	= (float) str_replace(',', '', $JUMLAH[$key]);			
				$detail->save();
			}
		}
		
//  ganti 11
		$variablell = DB::select('call kasins(?)',array($no_bukti));
		
        return redirect('/kask')->with('statusInsert', 'Data baru berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */
	 
// ganti 12
	 
    public function show(Kas $kask)
    {

        $no_bukti = $kask->NO_BUKTI;
        $kaskDetail = DB::table('kasd')->where('NO_BUKTI', $no_bukti)->get();
		$data = [
			'header'		=> $kask,
			'detail'		=> $kaskDetail
		];        
		
		return view('ftransaksi_kask.show', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

// ganti 15

    public function edit(Kas $kask)
    {

        $no_bukti = $kask->NO_BUKTI;
        $kaskDetail = DB::table('kasd')->where('NO_BUKTI', $no_bukti)->get();
		$data = [
			'header'		=> $kask,
			'detail'		=> $kaskDetail
		];        
		
		return view('ftransaksi_kask.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

// ganti 18

    public function update(Request $request, Kas $kask )
    {
		
        $this->validate($request,
        [
		
// ganti 19
		
                'TGL'      => 'required',
                'BACNO'       => 'required'
            ]
        );
		
// ganti 20
		$variablell = DB::select('call kasdel(?)',array($kask['NO_BUKTI']));
     
        $periode = $request->session()->get('periode')['bulan']. '/' . $request->session()->get('periode')['tahun'];
			
        $kask->update(
            [

                'TGL'              => date('Y-m-d', strtotime($request['TGL'])),		
                'BACNO'            => ($request['BACNO']==null) ? "" : $request['BACNO'],
                'BNAMA'            => ($request['BNAMA']==null) ? "" : $request['BNAMA'],
                'JUMLAH'           => (float) str_replace(',', '', $request['TJUMLAH']),
				'KET'              => ($request['KET']==null) ? "" : $request['KET'],				
				'USRNM'            => Auth::user()->username,
				'TG_SMP'           => Carbon::now()
            ]
        );


        // Update Detail
        $length = sizeof($request->input('REC'));
		$NO_ID  = $request->input('NO_ID');
				
        $REC    = $request->input('REC');
        $ACNO   = $request->input('ACNO');
        $NACNO  = $request->input('NACNO');
        $URAIAN = $request->input('URAIAN');
        $JUMLAH = $request->input('JUMLAH');
       
        // Delete yang NO_ID tidak ada di input
        $query = DB::table('kasd')->where('NO_BUKTI', $request->NO_BUKTI)->whereNotIn('NO_ID',  $NO_ID)->delete();

        // Update / Insert
        for ($i=0;$i<$length;$i++) {
            // Insert jika NO_ID baru
            if ($NO_ID[$i] == 'new') {
                $insert = KasDetail::create(
                    [
                        'NO_BUKTI'   => $request->NO_BUKTI,
                        'REC'        => $REC[$i],
				        'PER'        => $periode,
                        'FLAG'       => 'K',	
                        'TYPE'       => 'BKK',  							
                        'ACNO'       => ($ACNO[$i]==null) ? "" :  $ACNO[$i],
                        'NACNO'      => ($NACNO[$i]==null) ? "" : $NACNO[$i],	
                        'URAIAN'     => ($URAIAN[$i]==null) ? "" : $URAIAN[$i],
                        'JUMLAH'     => (float) str_replace(',', '', $JUMLAH[$i]),
                        'DEBET'      => (float) str_replace(',', '', $JUMLAH[$i])
                    ]
                );
            } else {
                // Update jika NO_ID sudah ada
                $upsert = KasDetail::updateOrCreate(
                    [
                        'NO_BUKTI'  => $request->NO_BUKTI,
                        'NO_ID'     => (int) str_replace(',', '', $NO_ID[$i])
                    ],
    
                    [
                        'REC'        => $REC[$i],
                        'ACNO'       => ($ACNO[$i]==null) ? "" :  $ACNO[$i],
                        'NACNO'      => ($NACNO[$i]==null) ? "" : $NACNO[$i],	
                        'URAIAN'     => ($URAIAN[$i]==null) ? "" : $URAIAN[$i],
                        'JUMLAH'     => (float) str_replace(',', '', $JUMLAH[$i]),
                        'DEBET'      => (float) str_replace(',', '', $JUMLAH[$i])
                    ]
                );
            }
        }

//  ganti 21
		$variablell = DB::select('call kasins(?)',array($kask['NO_BUKTI']));
		
        return redirect('/kask')->with('status', 'Data baru berhasil diedit');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */
	 
// ganti 22
	 
    public function destroy(Kas $kask)
    {

		$variablell = DB::select('call kasdel(?)',array($kask['NO_BUKTI']));
// ganti 23
        $deleteKask = Kas::find($kask->NO_ID);

// ganti 24

        $deleteKask->delete();

// ganti 
        return redirect('/kask')->with('status', 'Data berhasil dihapus');
		
		
    }
}
