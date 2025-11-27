<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
// ganti 1

use App\Models\Master\Greet;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use DB;
use Carbon\Carbon;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";
use PHPJasperXML;

// ganti 2
class GreetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
		
    public function index(Request $request)
    {
        // ganti 3
        return view('master_greet.index');
    }

    // ganti 4
    public function getGreet(Request $request)
    {
       $greet = DB::SELECT("SELECT * FROM greet ORDER BY BARIS");
	  
	   
        // ganti 6

       return Datatables::of($greet)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {

                if (Auth::user()->divisi == "programmer") {

                    // URL untuk delete dan edit
                    $urlDelete = "'" . url("greet/delete/" . $row->BARIS) . "'";
                    $urlEdit = url('greet/edit/' . $row->BARIS);

                    // Tombol aksi (dropdown)
                    $btnPrivilege = '
                        <a class="dropdown-item" href="' . $urlEdit . '">
                            <i class="fas fa-edit"></i> Edit
                        </a>

                        <a class="dropdown-item" href="jsgreet/' . $row->BARIS . '">
                            <i class="fa fa-print" aria-hidden="true"></i> Print
                        </a>

                        <hr>

                        <a class="dropdown-item text-danger" href="#" onclick="deleteRow(' . $urlDelete . ')">
                            <i class="fa fa-trash" aria-hidden="true"></i> Delete
                        </a>
                    ';
                } else {
                    $btnPrivilege = '';
                }

                $actionBtn = '
                    <div class="dropdown show text-center">
                        <a class="btn btn-secondary dropdown-toggle btn-sm" href="#" role="button"
                        id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
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


//////////////////////////////////////////////////////////////////////////////////


			
			
			
			
///            ->rawColumns(['action'])
 //           ->make(true);
//    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $katas = $request->input('KATA'); // array dari input

        // Bersihkan tabel dulu (jika hanya ingin simpan 3 baris tiap kali)
        Greet::truncate();
        foreach ($katas as $index => $kata) {
            if ($kata) {
                Greet::create([
                    'BARIS' => $index + 1,
                    'KATA'  => $kata,
                ]);
            }
        }

        return redirect('/greet')->with('success', 'Data berhasil disimpan!');
    }


    // ganti 15

   
    // public function edit($baris)
    // {
    //     $greet = Greet::where('BARIS', $baris)->get();

    //     $data = [
    //         'header' => $greet,
    //         'tipx'   => 'edit',
    //         'idx'    => $baris,  // <---- tambahkan ini
    //     ];
    //     return dd($greet);
    //     return view('otransaksi_greet.edit', $data);
    // }

    // public function edit($baris)
    // {
    //     // Ambil data sesuai baris yang diklik
    //     $header = Greet::where('BARIS', $baris)->first();

    //     if (!$header) {
    //         return redirect('greet')->with('error', 'Data tidak ditemukan');
    //     }

    //     // Pisahkan KATA menjadi array (kalau disimpan dengan '|')
    //     $header->KATA = explode('|', $header->KATA);

    //     $idx  = $baris;
    //     $tipx = 'edit';

    //     return view('master_greet.edit', compact('header', 'tipx', 'idx'));
    // }

    public function edit(Request $request, $baris)
    {
        // Jika mode = new, hapus data lama dari session agar old() kosong
        if ($request->get('tipx') == 'new') {
            session()->forget('_old_input');
        }

        $greet = Greet::where('BARIS', $baris)->first();

        if (!$greet && $request->get('tipx') != 'new') {
            return redirect('greet')->with('error', 'Data tidak ditemukan');
        }

        // Jika tipx=new maka header kosong (buat input baru)
        if ($request->get('tipx') == 'new') {
            $header = (object)[ 'KATA' => ['', '', ''] ]; // inisialisasi kosong
        } else {
            $header = $greet;
            $header->KATA = explode('|', $header->KATA ?? '||'); // pecah kata jika ada
        }

        $data = [
            'header' => $header,
            'idx'    => $baris,
            'tipx'   => $request->get('tipx') ?? 'edit',
        ];

        return view('master_greet.edit', $data);
    }


    // ganti 18

    public function update(Request $request)
    {
        $katas = $request->input('KATA');

        foreach ($katas as $index => $kata) {
            $baris = $index + 1;

            if (!empty($kata)) {
                DB::statement("
                    INSERT INTO greet (BARIS, KATA)
                    VALUES (?, ?)
                    ON DUPLICATE KEY UPDATE KATA = VALUES(KATA)
                ", [$baris, $kata]);
            } else {
                DB::table('greet')->where('BARIS', $baris)->delete();
            }
        }

        return redirect('/greet')->with('success', 'Data berhasil diupdate!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Resbeliznse
     */

    // ganti 22

    public function destroy($baris)
    {
        $deleted = \App\Models\Master\Greet::where('BARIS', $baris)->delete();

        if ($deleted > 0) {
            return redirect('/greet')->with('status', "Data Baris $baris berhasil dihapus ($deleted baris)");
        } else {
            return redirect('/greet')->with('status', "Data Baris $baris tidak ditemukan");
        }
    }
}
