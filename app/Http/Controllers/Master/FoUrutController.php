<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;

use App\Models\Master\Fo;
use App\Models\Master\Fod2;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use DB;
use Carbon\Carbon;

class FoUrutController extends Controller
{
    public function index()
    {
        return view('master_fourut.index');
    }

    public function getFourut()
    {
        $fo = Fo::query();
		
        return Datatables::of($fo)
                ->addIndexColumn()
                ->addColumn('action', function($row) {
                    if (Auth::user()->divisi=="programmer" || Auth::user()->divisi=="owner" || Auth::user()->divisi=="production")
                    {
                        $btnPrivilege = 
                        '
                                <a class="dropdown-item" href="fourut/edit/'. $row->NO_ID .'">
                                <i class="fas fa-edit"></i>
                                    Edit
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
                            <a class="dropdown-item" href="fourut/show/'. $row->NO_ID .'">
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

    public function show(Fo $fo)
    {
		$no_bukti = $fo->NO_BUKTI;
        $foDetail = DB::table('fod2')->where('NO_BUKTI', $no_bukti)->get();
		$data = [
			'header'		=> $fo,
			'detail'		=> $foDetail
		];
		
		return view('master_fourut.show', $data);
    }

	public function edit(Fo $fo)
    {
        $foData = Fo::where('NO_ID', $fo->NO_ID)->first();
        $no_bukti = $fo->NO_BUKTI;
        $foDetail = DB::table('fod2')->where('NO_BUKTI', $no_bukti)->get();
		$data = [
			'header'		=> $fo,
			'detail'		=> $foDetail
		];        

        return view('master_fourut.edit', $data);
    }

    public function update(Request $request, Fo $fo )
    {
        $this->validate($request,
            [
                'NO_BUKTI'       => 'required',
            ]
        );
		
        $fo->update(
            [
				'USRNM'             => Auth::user()->username,
				'TG_SMP'            => Carbon::now(),
            ]
        );
		
        $length = sizeof($request->input('NO_PRS'));
        $NO_ID  = $request->input('NO_ID');
		
		$NO_PRS	= $request->input('NO_PRS');
		$KD_PRS	= $request->input('KD_PRS');
		$NA_PRS	= $request->input('NA_PRS');
		
       // Delete yang NO_ID tidak ada di input
        $query = DB::table('fod2')->where('NO_BUKTI', $request->NO_BUKTI)->whereNotIn('NO_ID',  $NO_ID)->delete();

        // Update / Insert
        for ($i=0;$i<$length;$i++) {
            // Insert jika NO_ID baru
            if ($NO_ID[$i] == 'new') {
                $insert = Fod2::create(
                    [
                        'NO_BUKTI'   => $request->NO_BUKTI,
                        'ID'         => $fo->NO_ID,
                        'NO_PRS'     => ($NO_PRS[$i]==null) ? "" : $NO_PRS[$i],
						'KD_PRS'     => ($KD_PRS[$i]==null) ? "" :  $KD_PRS[$i],
                        'NA_PRS'     => ($NA_PRS[$i]==null) ? "" : $NA_PRS[$i],
                    ]
                );
            } else {
                // Update jika NO_ID sudah ada
                $update = Fod2::updateOrCreate(
                    [
                        'NO_BUKTI'  => $request->NO_BUKTI,
                        'NO_ID'     => (int) str_replace(',', '', $NO_ID[$i])
                    ],
    
                    [
                        'ID'        => $fo->NO_ID,
                        'NO_PRS'    => ($NO_PRS[$i]==null) ? "" : $NO_PRS[$i],
						'KD_PRS'    => ($KD_PRS[$i]==null) ? "" :  $KD_PRS[$i],
                        'NA_PRS'    => ($NA_PRS[$i]==null) ? "" : $NA_PRS[$i],
                    ]
                );
            }
        }	 
        
        DB::SELECT("UPDATE fod2 SET AWAL=0, AKHIR=0 WHERE NO_BUKTI='".$fo->NO_BUKTI."' ");  
        DB::SELECT("UPDATE fod2 SET AWAL=1 WHERE NO_BUKTI='".$fo->NO_BUKTI."' and NO_PRS=(SELECT min(NO_PRS) from fod2 WHERE NO_BUKTI='".$fo->NO_BUKTI."') ");  
        DB::SELECT("UPDATE fod2 SET AKHIR=1 WHERE NO_BUKTI='".$fo->NO_BUKTI."' and NO_PRS=(SELECT max(NO_PRS) from fod2 WHERE NO_BUKTI='".$fo->NO_BUKTI."') ");  
	   
        return redirect('/fourut')->with('status', 'Urutan Formula '.$fo->NO_BUKTI.' berhasil diedit');
    }

}
