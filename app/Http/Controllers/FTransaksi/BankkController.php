<?php

namespace App\Http\Controllers\FTransaksi;

use App\Http\Controllers\Controller;
// ganti 1

use App\Models\FTransaksi\Bank;
use App\Models\FTransaksi\BankDetail;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use DB;
use Carbon\Carbon;

// ganti 2
class BankkController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
		
// ganti 3
        return view('ftransaksi_bankk.index');
    }

// ganti 4

    public function getBankk(Request $request)
    {
// ganti 5

       if ($request->session()->has('periode')) 
		{
			$periode = $request->session()->get('periode')['bulan']. '/' . $request->session()->get('periode')['tahun'];
		} else
		{
			$periode = '';
		}

        //   $po = DB::table('po')->select('*')->where('PER', $periode)->where('GOL', 'Y')->orderBy('NO_PO', 'ASC')->get();
           $bankk = DB::table('bank')->select('*')->where('TYPE', 'BBK')->where('PER', $periode)->orderBy('NO_BUKTI', 'ASC')->get();
	 
		
// ganti 6
		
        return Datatables::of($bankk)
                ->addIndexColumn()
                ->addColumn('action', function($row) {
                    if (Auth::user()->hasRole("superadmin") || Auth::user()->hasRole("operational"))
                    {

                        $btnEdit = ($row->POSTED == 1) ? ' onclick= "alert(\'Transaksi ' . $row->NO_BUKTI . ' sudah diposting!\')" href="#" ' : ' href="bankk/edit/' . $row->NO_ID . '" ';
                    $btnDelete = ($row->POSTED == 1) ? ' onclick= "alert(\'Transaksi ' . $row->NO_BUKTI . ' sudah diposting!\')" href="#" ' : ' href="bankk/delete/' . $row->NO_ID . '" ';

                    $btnPrivilege =
                        '
                                <a class="dropdown-item" ' . $btnEdit . '>
                                <i class="fas fa-edit"></i>
                                    Edit
                                </a>
                                <a class="dropdown-item btn btn-danger" href="jasper-bank-trans/' . $row->NO_ID . '">
                                    <i class="fa fa-trash" aria-hidden="true"></i>
                                    Print
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
                        <a class="btn btn-secondary dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-bars"></i>
                        </a>

                        <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                            <a class="dropdown-item" href="bankk/show/'. $row->NO_ID .'">
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
        return view('ftransaksi_bankk.create');
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


        $periode = $request->session()->get('periode')['bulan']. '/' . $request->session()->get('periode')['tahun'];
		
        $bulan	= session()->get('periode')['bulan'];
		$tahun	= substr(session()->get('periode')['tahun'],-2);
		$query = DB::table('bank')->select('NO_BUKTI')->where('PER', $periode)->where('TYPE', 'BBK')->orderByDesc('NO_BUKTI')->limit(1)->get();
		
		if ($query != '[]')
        {
            $query = substr($query[0]->NO_BUKTI, -4);
            $query = str_pad($query + 1, 4, 0, STR_PAD_LEFT);
            $no_bukti = 'BBK'.$tahun.$bulan.'-'.$query;
        } else {
            $no_bukti = 'BBK'.$tahun.$bulan.'-0001';
        }
        // Insert Header

// ganti 10


        $bank = Bank::create(
            [
                'NO_BUKTI'         => $no_bukti,
                'TGL'              => date('Y-m-d', strtotime($request['TGL'])),		
                'PER'              => $periode,
                'BACNO'            => ($request['BACNO']==null) ? "" : $request['BACNO'],			
                'BNAMA'            => ($request['BNAMA']==null) ? "" : $request['BNAMA'],
				'BG'	           => ($request['BG']==null) ? "" : $request['BG'],
				'JTEMPO'           => date('Y-m-d', strtotime($request['JTEMPO'])),
				'KET'              => ($request['KET']==null) ? "" : $request['KET'],
				'FLAG'             => 'B',
                'TYPE'			   => 'BBK',	
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
				$detail	= new BankDetail;
				
				// Insert ke Database
				$detail->NO_BUKTI = $no_bukti;			
				$detail->REC	= $REC[$key];
				$detail->PER	= $periode;
				$detail->FLAG	= 'B';
				$detail->TYPE	= 'BBK';
				$detail->ACNO	= $ACNO[$key];
				$detail->NACNO	= $NACNO[$key];
				$detail->URAIAN	= $URAIAN[$key];
				$detail->JUMLAH	= (float) str_replace(',', '', $JUMLAH[$key]);
				$detail->KREDIT	= (float) str_replace(',', '', $JUMLAH[$key]);				
				$detail->save();
			}
		}

//  ganti 11
		$variablell = DB::select('call bankins(?)',array($no_bukti));
		
        return redirect('/bankk')->with('statusInsert', 'Data baru berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */
	 
// ganti 12
	 
    public function show(Bank $bankk)
    {

        $no_bukti = $bankk->NO_BUKTI;
        $bankkDetail = DB::table('bankd')->where('NO_BUKTI', $no_bukti)->get();
		$data = [
			'header'		=> $bankk,
			'detail'		=> $bankkDetail
		];        
		
		return view('ftransaksi_bankk.show', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

// ganti 15

    public function edit(Bank $bankk)
    {

        $no_bukti = $bankk->NO_BUKTI;
        $bankkDetail = DB::table('bankd')->where('NO_BUKTI', $no_bukti)->get();
		$data = [
			'header'		=> $bankk,
			'detail'		=> $bankkDetail
		];        
		
		return view('ftransaksi_bankk.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

// ganti 18
    public function update(Request $request, Bank $bankk )
    {
		
        $this->validate($request,
        [
		
// ganti 19
		
                'TGL'      => 'required',
                'BACNO'       => 'required'
            ]
        );
		
// ganti 20
		$variablell = DB::select('call bankdel(?)',array($bankk['NO_BUKTI']));
    
		$periode = $request->session()->get('periode')['bulan']. '/' . $request->session()->get('periode')['tahun'];
			
        $bankk->update(
            [

                'TGL'              => date('Y-m-d', strtotime($request['TGL'])),		
                'BACNO'            => ($request['BACNO']==null) ? "" : $request['BACNO'],
                'BNAMA'            => ($request['BNAMA']==null) ? "" : $request['BNAMA'],
                'BG'			   => ($request['BG']==null) ? "" : $request['BG'],
				'JTEMPO'           => date('Y-m-d', strtotime($request['JTEMPO'])),
				'KET'              => ($request['KET']==null) ? "" : $request['KET'],	
                'JUMLAH'           => (float) str_replace(',', '', $request['TJUMLAH']),
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
		$query = DB::table('bankd')->where('NO_BUKTI', $request->NO_BUKTI)->whereNotIn('NO_ID',  $NO_ID)->delete();

        // Update / Insert
        for ($i=0;$i<$length;$i++) {
            // Insert jika NO_ID baru
            if ($NO_ID[$i] == 'new') {
                $insert = BankDetail::create(
                    [
                        'NO_BUKTI'   => $request->NO_BUKTI,
                        'REC'        => $REC[$i],
				        'PER'        => $periode,
                        'FLAG'       => 'B',	
                        'TYPE'       => 'BBK',  							
                        'ACNO'       => ($ACNO[$i]==null) ? "" :  $ACNO[$i],
                        'NACNO'      => ($NACNO[$i]==null) ? "" : $NACNO[$i],	
                        'URAIAN'     => ($URAIAN[$i]==null) ? "" : $URAIAN[$i],
                        'JUMLAH'     => (float) str_replace(',', '', $JUMLAH[$i]),
                        'DEBET'      => (float) str_replace(',', '', $JUMLAH[$i])
                    ]
                );
            } else {
                // Update jika NO_ID sudah ada
                $update = BankDetail::updateOrCreate(
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
		$variablell = DB::select('call bankins(?)',array($bankk['NO_BUKTI']));
		
        return redirect('/bankk')->with('status', 'Data baru berhasil diedit');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */
	 
// ganti 22
	 
    public function destroy(Bank $bankk)
    {

		$variablell = DB::select('call bankdel(?)',array($bankk['NO_BUKTI']));
		
// ganti 23
        $deleteBankk = Bank::find($bankk->NO_ID);

// ganti 24

        $deleteBankk->delete();

// ganti 
        return redirect('/bankk')->with('status', 'Data berhasil dihapus');
		
		
    }
}
