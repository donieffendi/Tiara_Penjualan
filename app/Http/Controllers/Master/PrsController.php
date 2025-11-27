<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
// ganti 1

use App\Models\Master\Prs;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use DB;
use Carbon\Carbon;

// ganti 2
class PrsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
		
// ganti 3
        return view('master_prs.index');
    }

// ganti 4

// ganti 4
    public function browse()
    {
		$prs = DB::table('prs')->select('KD_PRS', 'NA_PRS')->where('GOL', 'Y')->orderBy('KD_PRS', 'ASC')->get();
		return response()->json($prs);
	}

	public function browses()
    {
		$prs = DB::table('prs')->select('KD_PRS', 'NA_PRS')->where('GOL', 'S')->orderBy('KD_PRS', 'ASC')->get();
		return response()->json($prs);
	}
	
	public function browsen()
    {
		$prs = DB::table('prs')->select('KD_PRS', 'NA_PRS')->where('GOL', 'Z')->orderBy('KD_PRS', 'ASC')->get();
		return response()->json($prs);
	}
	
	public function browseb()
    {
		$prs = DB::table('prs')->select('KD_PRS', 'NA_PRS')->orderBy('KD_PRS', 'ASC')->get();
		return response()->json($prs);
	}
	
	public function browseall()
    {
		$prs = DB::table('prs')->select('KD_PRS', 'NA_PRS')->orderBy('KD_PRS', 'ASC')->get();
		return response()->json($prs);
	}
	

	
    public function getPrs()
    {
// ganti 5

        $prs = Prs::query();
		
// ganti 6
		
        return Datatables::of($prs)
                ->addIndexColumn()
                ->addColumn('action', function($row) {
                    if (Auth::user()->divisi=="programmer" || Auth::user()->divisi=="owner" || Auth::user()->divisi=="production")
                    {
                        $btnPrivilege = 
                        '
                                <a class="dropdown-item" href="prs/edit/'. $row->NO_ID .'">
                                <i class="fas fa-edit"></i>
                                    Edit
                                </a>
                                <hr></hr>
                                <a class="dropdown-item btn btn-danger" onclick="return confirm(&quot; Apakah anda yakin ingin hapus? &quot;)" href="prs/delete/'. $row->NO_ID .'">
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
                            <a class="dropdown-item" href="prs/show/'. $row->NO_ID .'">
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
        return view('master_prs.create');
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
                'KD_PRS'       => 'required',
                'NA_PRS'      => 'required'
            ]
        );

        // Insert Header

// ganti 10

        $prs = Prs::create(
            [
                'KD_PRS'         => ($request['KD_PRS']==null) ? "" : $request['KD_PRS'],	
                'NA_PRS'         => ($request['NA_PRS']==null) ? "" : $request['NA_PRS'],
				'AKHIR'        	=> ($request['AKHIR']==null) ? 0 : $request['AKHIR'],
				'USRNM'          => Auth::user()->username,
				'TG_SMP'         => Carbon::now()
            ]
        );

//  ganti 11

        return redirect('/prs')->with('status', 'Data baru berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */
	 
// ganti 12
	 
    public function show(Prs $prs)
    {

// ganti 13

        $prsData = Prs::where('NO_ID', $prs->NO_ID)->first();

// ganti 14
        return view('master_prs.show', $prsData);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

// ganti 15

    public function edit(Prs $prs)
    {
		
// ganti 16
		
        $prsData = Prs::where('NO_ID', $prs->NO_ID)->first();

// ganti 17 
        return view('master_prs.edit', $prsData);
		

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

// ganti 18

    public function update(Request $request, Prs $prs )
    {
		
        $this->validate($request,
        [
		
// ganti 19
		
                'KD_PRS'       => 'required',
                'NA_PRS'      => 'required'
            ]
        );
		
// ganti 20
		
        $prs->update(
            [
				'KD_PRS'       	 => ($request['KD_PRS']==null) ? "" : $request['KD_PRS'],
                'NA_PRS'       	 => ($request['NA_PRS']==null) ? "" : $request['NA_PRS'],
				'AKHIR'        	=> ($request['AKHIR']==null) ? 0 : $request['AKHIR'],
				'USRNM'          => Auth::user()->username,
				'TG_SMP'         => Carbon::now()
				
				
            ]
        );


//  ganti 21

        return redirect('/prs')->with('status', 'Data baru berhasil diedit');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */
	 
// ganti 22
	 
    public function destroy(Prs $prs)
    {

// ganti 23
        $deletePrs = Prs::find($prs->NO_ID);

// ganti 24

        $deletePrs->delete();

// ganti 
        return redirect('/prs')->with('status', 'Data berhasil dihapus');
		
		
    }
}
