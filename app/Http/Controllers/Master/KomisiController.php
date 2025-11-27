<?php
namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\Rekananh;
use App\Models\Master\RekananhDetail;
use Auth;
use Carbon\Carbon;
use DataTables;
use DB;
use Illuminate\Http\Request;

class KomisiController extends Controller
{

    public function index()
    {
        return view('master_daftar_komisi.index');
    }

    public function browse_kode(Request $request)
    {
        $komisi = DB::SELECT("SELECT KODE, NAMA from rekanan ORDER BY KODE ASC");
        return response()->json($komisi);
    }

    public function browse_sub(Request $request)
    {
        $komisi = DB::SELECT("SELECT DISTINCT SUB, KELOMPOK from brg WHERE KELOMPOK <> '' ORDER BY SUB ASC");
        return response()->json($komisi);
    }

    public function getKomisi(Request $request)
    {
        $query = DB::select("SELECT * FROM rekananh");

        return Datatables::of($query)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                if (Auth::user()->divisi == "programmer") {

                    $btnPrivilege =
                    '
                                    <a class="dropdown-item" href="komisi/edit/?idx=' . $row->NO_ID . '&tipx=edit";
                                    <i class="fas fa-edit"></i>
                                        Edit
                                    </a>
                                    <hr></hr>
                                    <a class="dropdown-item btn btn-danger" onclick="return confirm(&quot; Apakah anda yakin ingin hapus? &quot;)" href="komisi/delete/' . $row->NO_ID . '">
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
                                <a hidden class="dropdown-item" href="komisi/show/' . $row->NO_ID . '">
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
        $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];

        $bulan = str_pad(session()->get('periode')['bulan'], 2, '0', STR_PAD_LEFT);
        $tahun = substr(session()->get('periode')['tahun'], -2);

        $query = DB::table('rekananh')
            ->select('NO_BUKTI')
            ->where('NO_BUKTI', 'like', $tahun . $bulan . '%')
            ->orderByDesc('NO_BUKTI')
            ->first();

        if ($query) {
            $lastNumber = intval(substr($query->NO_BUKTI, -3));
            $newNumber  = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '001';
        }

        $no_bukti = $tahun . $bulan . '-' . $newNumber;

        // Insert Header

        // ganti 10

        $komisi = Rekananh::create(
            [
                'NO_BUKTI' => $no_bukti,
                'TGL'      => date('Y-m-d', strtotime($request['TGL'])),
                'NAMA'     => ($request['NAMA'] == null) ? "" : $request['NAMA'],
                'KODE'     => ($request['KODE'] == null) ? "" : $request['KODE'],
                'TGLM'     => date('Y-m-d', strtotime($request['TGLM'])),
                'TGLS'     => date('Y-m-d', strtotime($request['TGLS'])),
                'NOTES'    => ($request['NOTES'] == null) ? "" : $request['NOTES'],
                'USRNM'    => Auth::user()->username,
                'TG_SMP'   => Carbon::now(),
            ]
        );

        $REC      = $request->input('REC');
        $MARGIN   = $request->input('MARGIN');
        $KELOMPOK = $request->input('KELOMPOK');
        $KOMISI   = $request->input('KOMISI');
        $SUB      = $request->input('SUB');

        // Check jika value detail ada/tidak
        if ($REC) {
            foreach ($REC as $key => $value) {
                // Declare new data di Model
                $detail = new RekananhDetail;

                // Insert ke Database
                $detail->NO_BUKTI = $no_bukti;
                $detail->REC      = $REC[$key];
                $detail->SUB      = ($SUB[$key] == null) ? "" : $SUB[$key];
                $detail->KELOMPOK      = ($KELOMPOK[$key] == null) ? "" : $KELOMPOK[$key];
                $detail->MARGIN   = (float) str_replace(',', '', $MARGIN[$key]);
                $detail->KOMISI   = (float) str_replace(',', '', $KOMISI[$key]);
                $detail->save();
            }
        }

        //  ganti 11
        //$variablell = DB::select('call absenins(?)', array($no_bukti));

        $no_buktix = $no_bukti;

        $komisi = Rekananh::where('NO_BUKTI', $no_buktix)->first();

        DB::SELECT("UPDATE REKANANH, REKANAND
                            SET REKANAND.ID = REKANANH.NO_ID  WHERE REKANANH.NO_BUKTI = REKANAND.NO_BUKTI
							AND REKANANH.NO_BUKTI='$no_buktix';");

        //  ganti 11
        return redirect('/komisi')->with('statusInsert', 'Data baru berhasil ditambahkan');

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    // ganti 15

    public function edit(Request $request, Rekananh $komisi)
    {

        // ganti 16
        $tipx = $request->tipx;

        $idx = $request->idx;

        $cbg = Auth::user()->CBG;

        if ($idx == '0' && $tipx == 'undo') {
            $tipx = 'top';

        }

        if ($tipx == 'search') {

            $kodex = $request->kodex;

            $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from rekananh
		                 where NO_BUKTI = '$kodex'
		                 ORDER BY NO_BUKTI ASC  LIMIT 1");

            if (! empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = 0;
            }

        }

        if ($tipx == 'top') {

            $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from rekananh
		                 ORDER BY NO_BUKTI ASC  LIMIT 1");

            if (! empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = 0;
            }

        }

        if ($tipx == 'prev') {

            $kodex = $request->kodex;

            $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from rekananh
		             where NO_BUKTI <
					 '$kodex' ORDER BY NO_BUKTI DESC LIMIT 1");

            if (! empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = $idx;
            }

        }
        if ($tipx == 'next') {

            $kodex = $request->kodex;

            $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from rekananh
		             where NO_BUKTI >
					 '$kodex' ORDER BY NO_BUKTI ASC LIMIT 1");

            if (! empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = $idx;
            }

        }

        if ($tipx == 'bottom') {

            $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from rekananh
		              ORDER BY NO_BUKTI DESC  LIMIT 1");

            if (! empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = 0;
            }

        }

        if ($tipx == 'undo' || $tipx == 'search') {

            $tipx = 'edit';

        }

        if ($idx != 0) {
            $komisi = Rekananh::where('NO_ID', $idx)->first();
        } else {
            $komisi = new Rekananh();
            $komisi->TGL = Carbon::now();
            $komisi->TGLM = Carbon::now();
            $komisi->TGLS = Carbon::now();
        }

        $no_bukti     = $komisi->NO_BUKTI;
        $komisiDetail = DB::table('rekanand')
            ->where('NO_BUKTI', $no_bukti)
            ->get();

        $data = [
            'header' => $komisi,
            'detail' => $komisiDetail,
        ];

        return view('master_daftar_komisi.edit', $data)->with(['tipx' => $tipx, 'idx' => $idx]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    public function update(Request $request, Rekanan $komisi)
    {

        // ganti 20

        $tipx = 'edit';
        $idx  = $request->idx;

        $komisi->update(
            [
                'TGL'      => date('Y-m-d', strtotime($request['TGL'])),
                'NAMA'     => ($request['NAMA'] == null) ? "" : $request['NAMA'],
                'KODE'     => ($request['KODE'] == null) ? "" : $request['KODE'],
                'TGLM'     => date('Y-m-d', strtotime($request['TGLM'])),
                'TGLS'     => date('Y-m-d', strtotime($request['TGLS'])),
                'NOTES'    => ($request['NOTES'] == null) ? "" : $request['NOTES'],
                'USRNM'    => Auth::user()->username,
                'TG_SMP'   => Carbon::now(),
            ]
        );

        $no_bukti = $komisi->BUKTI;
        $REC      = $request->input('REC');
        $MARGIN   = $request->input('MARGIN');
        $KELOMPOK = $request->input('KELOMPOK');
        $KOMISI   = $request->input('KOMISI');
        $SUB      = $request->input('SUB');

        RekananhDetail::where('NO_BUKTI', $no_bukti)->delete();
        if ($REC && is_array($REC)) {
            foreach ($REC as $key => $value) {
                RekananhDetail::create([
                    'NO_BUKTI' => $no_bukti,
                    'REC'      => $value,
                    'SUB'      => $SUB[$key] ?? '',
                    'KELOMPOK' => $KELOMPOK[$key],
                    'KOMISI'   => (float) str_replace(',', '', $KOMISI[$key]),
                    'MARGIN'   => (float) str_replace(',', '', $MARGIN[$key]),
                    'ID'       => $komisi->NO_ID, 
                ]);
            }
        }

        return redirect('/komisi')->with('status', 'Data berhasil diupdate');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    // ganti 22

    public function destroy(Request $request, Rekananh $komisi)
    {

        // ganti 23
        $deleteKomisi = Rekananh::find($komisi->NO_ID);

        // ganti 24

        $deleteKomisi->delete();

        // ganti
        return redirect('/komisi')->with('status', 'Data berhasil dihapus');
    }

}