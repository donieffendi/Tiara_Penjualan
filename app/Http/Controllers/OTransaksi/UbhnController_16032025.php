<?php
namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
// ganti 1

use App\Models\OTransaksi\Ubhn;
use App\Models\OTransaksi\UbhnDetail;
use Auth;
use Carbon\Carbon;
use DataTables;
use DB;
use Illuminate\Http\Request;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";
use PHPJasperXML;

// ganti 2
class UbhnController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public $judul = '';
    public $FLAGZ = '';
    public $GOLZ  = '';

    public function setFlag(Request $request)
    {
        if ($request->flagz == 'U' && $request->golz == 'U') {
            $this->judul = "Permintaan Ulasan";
        }
        $this->FLAGZ = $request->flagz;
        $this->GOLZ  = $request->golz;

    }

    public function index(Request $request)
    {

        $this->setFlag($request);
        // ganti 3
        return view('otransaksi_ubhn.index')->with(['judul' => $this->judul, 'flagz' => $this->FLAGZ, 'golz' => $this->GOLZ]);

    }

    public function index_post(Request $request)
    {

        return view('otransaksi_ubhn.post');

    }

    // public function acc(Request $request, $id)
    // {
    //     DB::table('ubhn')->where('NO_BUKTI', $id)->update(['POSTED' => 1, 'TOLAK' => 0]);
    //     return response()->json(['message' => 'ACC berhasil']);
    // }

    // public function tolak(Request $request, $id)
    // {
    //     DB::table('ubhn')->where('NO_BUKTI', $id)->update(['TOLAK' => 1, 'POSTED' => 0]);
    //     return response()->json(['message' => 'Tolak berhasil']);
    // }

    // public function acc2(Request $request, $id)
    // {
    //     DB::table('ubhn')->where('NO_BUKTI', $id)->update(['POSTED1' => 1, 'TOLAK' => 0]);
    //     return response()->json(['message' => 'ACC 2 berhasil']);
    // }

    // public function tolak2(Request $request, $id)
    // {
    //     DB::table('ubhn')->where('NO_BUKTI', $id)->update(['TOLAK' => 1, 'POSTED1' => 0]);
    //     return response()->json(['message' => 'Tolak 2 berhasil']);
    // }

    public function acc(Request $request, $id)
    {
        if (! $request->isMethod('post')) {
            return response()->json(['error' => 'Method Not Allowed'], 405);
        }

        DB::table('ubhn')->where('NO_BUKTI', $id)->update(['POSTED' => 1, 'TOLAK' => 0]);
        return response()->json(['message' => 'ACC berhasil']);
    }

    public function tolak(Request $request, $id)
    {
        if (! $request->isMethod('post')) {
            return response()->json(['error' => 'Method Not Allowed'], 405);
        }

        DB::table('ubhn')->where('NO_BUKTI', $id)->update(['TOLAK' => 1, 'POSTED' => 0]);
        return response()->json(['message' => 'TOLAK berhasil']);
    }

    public function acc2(Request $request, $id)
    {
        if (! $request->isMethod('post')) {
            return response()->json(['error' => 'Method Not Allowed'], 405);
        }

        DB::table('ubhn')->where('NO_BUKTI', $id)->update(['POSTED1' => 1, 'TOLAK1' => 0]);
        return response()->json(['message' => 'ACC 2 berhasil']);
    }

    public function tolak2(Request $request, $id)
    {
        if (! $request->isMethod('post')) {
            return response()->json(['error' => 'Method Not Allowed'], 405);
        }

        DB::table('ubhn')->where('NO_BUKTI', $id)->update(['TOLAK1' => 1, 'POSTED1' => 0]);
        return response()->json(['message' => 'TOLAK 2 berhasil']);
    }

    public function browse(Request $request)
    {
        $CBG = Auth::user()->CBG;

        $filterbukti = '';
        if ($request->NO_BUKTI) {
            $filterbukti = " AND ubhn.NO_BUKTI='" . $request->NO_BUKTI . "' ";
        }
        $ubhn = DB::SELECT("SELECT * from ubhn, ubhnd where ubhn.NO_BUKTI=ubhnd.NO_BUKTI $filterbukti ");

        return response()->json($ubhn);
    }
    public function getUbhn(Request $request)
    {
        // ganti 5

        if ($request->session()->has('periode')) {
            $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
        } else {
            $periode = '';
        }

        $this->setFlag($request);
        $FLAGZ = $this->FLAGZ;
        $GOLZ  = $this->GOLZ;
        $judul = $this->judul;

        $CBG = Auth::user()->CBG;

        $ubhn = DB::SELECT("SELECT *, POSTED as cek1, POSTED1 as cek2 from ubhn  WHERE PER='$periode' AND CBG = '$CBG' ORDER BY NO_BUKTI ");

        // ganti 6

        return Datatables::of($ubhn)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                if (Auth::user()->divisi == "programmer") {
                    //CEK POSTED di index dan edit

                    // url untuk delete di index
                    $url = "'" . url("ubhn/delete/" . $row->NO_ID . "/?flagz=" . $row->FLAG . "&golz=" . $row->GOL) . "'";
                    // batas

                    $btnEdit   = ($row->POSTED == 1) ? ' onclick= "alert(\'Transaksi ' . $row->NO_BUKTI . ' sudah dippsting!\')" href="#" ' : ' href="ubhn/edit/?idx=' . $row->NO_ID . '&tipx=edit&flagz=' . $row->FLAG . '&judul=' . $this->judul . '&golz=' . $row->GOL . '"';
                    $btnDelete = ($row->POSTED == 1) ? ' onclick= "alert(\'Transaksi ' . $row->NO_BUKTI . ' sudah diposting!\')" href="#" ' : ' onclick="deleteRow(' . $url . ')"';

                    $btnPrivilege =
                    '
                                <a class="dropdown-item" ' . $btnEdit . '>
                                <i class="fas fa-edit"></i>
                                    Edit
                                </a>
                                <a class="dropdown-item btn btn-danger" href="ubhn/cetak/' . $row->NO_ID . '">
                                    <i class="fa fa-print" aria-hidden="true"></i>
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

//////////////////////////////////////////////////////////////////////////////////

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
                'TGL' => 'required',

            ]
        );

        //////     nomer otomatis
        $this->setFlag($request);
        $FLAGZ = $this->FLAGZ;
        $GOLZ  = $this->GOLZ;
        $judul = $this->judul;

        $CBG = Auth::user()->CBG;

        $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];

        $bulan = session()->get('periode')['bulan'];
        $tahun = substr(session()->get('periode')['tahun'], -2);

        // Ambil NO_BUKTI terakhir
        $query = DB::table('ubhn')
            ->where('NO_BUKTI', 'like', 'U' . $CBG . $tahun . $bulan . '-%')
            ->orderBy('NO_BUKTI', 'desc')
            ->first();

        if (! empty($query)) {
// Ambil 4 digit terakhir (increment number)
            $lastNumber = (int) substr($query->NO_BUKTI, -4);
            $newNumber  = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

// Format NO_BUKTI
        $no_bukti = 'U' . $CBG . $tahun . $bulan . '-' . $newNumber;

        $ubhn = Ubhn::create([
            'NO_BUKTI' => $no_bukti,
            'TGL'      => date('Y-m-d', strtotime($request['TGL'])),
            'PER'      => $periode,
            'FLAG'     => 'U',
            'CBG'      => $CBG,
            'NOTES'    => $request['NOTES'],
        ]);

        $REC       = $request->input('REC');
        $KODES     = $request->input('KODES');
        $NAMAS     = $request->input('NAMAS');
        $NM_BRG    = $request->input('NM_BRG');
        $KET       = $request->input('KET');
        $MERK      = $request->input('MERK');
        $UKURAN    = $request->input('UKURAN');
        $KD_BRG    = $request->input('KD_BRG');
        $KLP       = $request->input('KLP');
        $HARGA     = $request->input('HRG');
        $DISC      = $request->input('DISC');
        $BY_ANGKUT = $request->input('BY_ANGKUT');
        $PPN       = $request->input('PPN');
        $KET_KMS   = $request->input('KET_KMS');
        $MO        = $request->input('MO');
        $KLK       = $request->input('KLK');
        $N_POINT   = $request->input('N_POINT');
        $KIRA_LPP  = $request->input('KIRA_LPP');
        $KET_X     = $request->input('KET_X');
        $KET_PB    = $request->input('KET_PB');
        $POSTED    = $request->input('POSTED');
        $POSTED1   = $request->input('POSTED1');
        $TOLAK     = $request->input('TOLAK');
        $TOLAK1    = $request->input('TOLAK1');

        $FLAG = 'U';
        $PER  = $periode;
        $GOL  = $GOLZ;

        if ($REC) {
            foreach ($REC as $key => $value) {
                $detail            = new UbhnDetail;
                $detail->NO_BUKTI  = $no_bukti;
                $detail->KODES     = $KODES[$key] ?? '';
                $detail->NAMAS     = $NAMAS[$key] ?? '';
                $detail->NM_BRG    = $NM_BRG[$key] ?? '';
                $detail->KET       = $KET[$key] ?? '';
                $detail->MERK      = $MERK[$key] ?? '';
                $detail->UKURAN    = $UKURAN[$key] ?? '';
                $detail->KD_BRG    = $KD_BRG[$key] ?? '';
                $detail->KLP       = $KLP[$key] ?? '';
                $detail->HARGA     = $HARGA[$key] ?? '';
                $detail->DISC      = $DISC[$key] ?? '';
                $detail->BY_ANGKUT = $BY_ANGKUT[$key] ?? '';
                $detail->PPN       = $PPN[$key] ?? '';
                $detail->KET_KMS   = $KET_KMS[$key] ?? '';
                $detail->MO        = $MO[$key] ?? '';
                $detail->KLK       = $KLK[$key] ?? '';
                $detail->N_POINT   = $N_POINT[$key] ?? '';
                $detail->KIRA_LPP  = $KIRA_LPP[$key] ?? '';
                $detail->KET_X     = $KET_X[$key] ?? '';
                $detail->KET_PB    = $KET_PB[$key] ?? '';
                $detail->FLAG      = $FLAG;
                $detail->PER       = $PER;
                $detail->POSTED    = $POSTED[$key] ?? '';
                $detail->POSTED1   = $POSTED1[$key] ?? '';
                $detail->TOLAK     = $TOLAK[$key] ?? '';
                $detail->TOLAK1    = $TOLAK1[$key] ?? '';
                $detail->save();
            }
        }
        // dd($detail);
        $no_buktix = $no_bukti;

        $ubhn = Ubhn::where('NO_BUKTI', $no_buktix)->first();

        DB::SELECT("UPDATE ubhn,  ubhnd
                            SET  ubhnd.NO_ID =  ubhn.NO_ID  WHERE  ubhn.NO_BUKTI =  ubhnd.NO_BUKTI
							AND  ubhn.NO_BUKTI='$no_buktix';");

        // return redirect('/pp/edit/?idx=' . $pp->NO_ID . '&tipx=edit&flagz=' . $this->FLAGZ . '&golz=' . $this->GOLZ . '&judul=' . $this->judul . '');
        return redirect('/ubhn?flagz=' . $FLAGZ . '&golz=' . $GOLZ)->with(['judul' => $judul, 'golz' => $GOLZ, 'flagz' => $FLAGZ]);

    }

    public function edit(Request $request, Ubhn $ubhn)
    {

        $per = session()->get('periode')['bulan'] . '/' . session()->get('periode')['tahun'];

        // $cekperid = DB::SELECT("SELECT POSTED from perid WHERE PERIO='$per'");
        // if ($cekperid[0]->POSTED==1)
        // {
        //     return redirect('/pp')
        // 	       ->with('status', 'Maaf Periode sudah ditutup!')
        //            ->with(['judul' => $judul, 'flagz' => $FLAGZ]);
        // }

        $this->setFlag($request);

        $tipx = $request->tipx;

        $idx = $request->idx;

        $CBG = Auth::user()->CBG;

        if ($idx == '0' && $tipx == 'undo') {
            $tipx = 'top';

        }

        if ($tipx == 'search') {

            $buktix = $request->buktix;

            $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from ubhn
		                 where PER ='$per' and FLAG ='$this->FLAGZ'
                         and GOL ='$this->GOLZ'
                         AND CBG = '$CBG'
						 and NO_BUKTI = '$buktix'
		                 ORDER BY NO_BUKTI ASC  LIMIT 1");

            if (! empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = 0;
            }

        }

        if ($tipx == 'top') {

            $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from ubhn
		                 where PER ='$per'
						 and FLAG ='$this->FLAGZ'
                         and GOL ='$this->GOLZ'
                         AND CBG = '$CBG'
		                 ORDER BY NO_BUKTI ASC  LIMIT 1");

            if (! empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = 0;
            }

        }

        if ($tipx == 'prev') {

            $buktix = $request->buktix;

            $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from ubhn
		             where PER ='$per'
					 and FLAG ='$this->FLAGZ'
                     and GOL ='$this->GOLZ'
                     AND CBG = '$CBG'
                     and NO_BUKTI <
					 '$buktix' ORDER BY NO_BUKTI DESC LIMIT 1");

            if (! empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = $idx;
            }

        }

        if ($tipx == 'next') {

            $buktix = $request->buktix;

            $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from ubhn
		             where PER ='$per'
					 and FLAG ='$this->FLAGZ'
                     and GOL ='$this->GOLZ'
                     AND CBG = '$CBG'
                     and NO_BUKTI >
					 '$buktix' ORDER BY NO_BUKTI ASC LIMIT 1");

            if (! empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = $idx;
            }

        }

        if ($tipx == 'bottom') {

            $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from ubhn
						where PER ='$per'
						and FLAG ='$this->FLAGZ'
                        and GOL ='$this->GOLZ'
                        AND CBG = '$CBG'
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
            $ubhn = Ubhn::where('NO_ID', $idx)->first();
        } else {
            $ubhn      = new Ubhn;
            $ubhn->TGL = Carbon::now();

        }

        $no_bukti   = $ubhn->NO_BUKTI;
        $ubhnDetail = DB::table('ubhnd')->where('NO_BUKTI', $no_bukti)->get();

        $data = [
            'header' => $ubhn,
            'detail' => $ubhnDetail,

        ];

        return view('otransaksi_ubhn.edit', $data)
            ->with(['tipx' => $tipx, 'idx' => $idx, 'flagz' => $this->FLAGZ, 'golz' => $this->GOLZ, 'judul' => $this->judul]);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    // ganti 18

    public function update(Request $request, Ubhn $ubhn)
    {

        $this->validate(
            $request,
            [

                'TGL' => 'required',
            ]
        );

        $this->setFlag($request);
        $GOLZ  = $this->GOLZ;
        $FLAGZ = $this->FLAGZ;
        $judul = $this->judul;

        $CBG = Auth::user()->CBG;

        $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];

        $ubhn->update([
            'TGL'        => date('Y-m-d', strtotime($request['TGL'])),
            'USRNM'      => Auth::user()->username,
            'updated_by' => Auth::user()->username,
            'FLAG'       => 'U',
            'GOL'        => $GOLZ,
            'CBG'        => $CBG,
            'NOTES'      => $request['NOTES'],
        ]);

        $no_buktix = $ubhn->NO_BUKTI;
        $NO_ID     = $request->input('NO_ID');
        $REC       = $request->input('REC');
        $KODES     = $request->input('KODES');
        $NAMAS     = $request->input('NAMAS');
        $NM_BRG    = $request->input('NM_BRG');
        $KET       = $request->input('KET');
        $MERK      = $request->input('MERK');
        $UKURAN    = $request->input('UKURAN');
        $KD_BRG    = $request->input('KD_BRG');
        $KLP       = $request->input('KLP');
        $HARGA     = $request->input('HARGA');
        $DISC      = $request->input('DISC');
        $BY_ANGKUT = $request->input('BY_ANGKUT');
        $PPN       = $request->input('PPN');
        $KET_KMS   = $request->input('KET_KMS');
        $MO        = $request->input('MO');
        $KLK       = $request->input('KLK');
        $N_POINT   = $request->input('N_POINT');
        $KIRA_LPP  = $request->input('KIRA_LPP');
        $KET_X     = $request->input('KET_X');
        $KET_PB    = $request->input('KET_PB');
        $POSTED    = $request->input('POSTED');
        $POSTED1   = $request->input('POSTED1');
        $TOLAK     = $request->input('TOLAK');
        $TOLAK1    = $request->input('TOLAK1');
        $FLAG      = 'U';
        $PER       = $periode;
        $GOL       = $GOLZ;

        // Hapus data yang tidak ada di request
        DB::table('ubhnd')->where('NO_BUKTI', $request->NO_BUKTI)->whereNotIn('NO_ID', $NO_ID)->delete();

        foreach ($REC as $key => $value) {
            if ($NO_ID[$key] == 'new') {
                UbhnDetail::create([
                    'NO_BUKTI'  => $request->NO_BUKTI,
                    'REC'       => $REC[$key],
                    'PER'       => $PER,
                    'FLAG'      => $FLAG,
                    'GOL'       => $GOL,
                    'KODES'     => $KODES[$key] ?? '',
                    'NAMAS'     => $NAMAS[$key] ?? '',
                    'NM_BRG'    => $NM_BRG[$key] ?? '',
                    'KET'       => $KET[$key] ?? '',
                    'MERK'      => $MERK[$key] ?? '',
                    'UKURAN'    => $UKURAN[$key] ?? '',
                    'KD_BRG'    => $KD_BRG[$key] ?? '',
                    'KLP'       => $KLP[$key] ?? '',
                    'HARGA'     => $HARGA[$key] ?? '',
                    'DISC'      => $DISC[$key] ?? '',
                    'BY_ANGKUT' => $BY_ANGKUT[$key] ?? '',
                    'PPN'       => $PPN[$key] ?? '',
                    'KET_KMS'   => $KET_KMS[$key] ?? '',
                    'MO'        => $MO[$key] ?? '',
                    'KLK'       => $KLK[$key] ?? '',
                    'N_POINT'   => $N_POINT[$key] ?? '',
                    'KIRA_LPP'  => $KIRA_LPP[$key] ?? '',
                    'KET_X'     => $KET_X[$key] ?? '',
                    'KET_PB'    => $KET_PB[$key] ?? '',
                    'POSTED'    => $POSTED[$key] ?? '',
                    'POSTED1'   => $POSTED1[$key] ?? '',
                    'TOLAK'     => $TOLAK[$key] ?? '',
                    'TOLAK1'    => $TOLAK1[$key] ?? '',
                ]);
            } else {
                UbhnDetail::updateOrCreate(
                    [
                        'NO_BUKTI' => $request->NO_BUKTI,
                        'NO_ID'    => (int) str_replace(',', '', $NO_ID[$key]),
                    ],
                    [
                        'REC'       => $REC[$key],
                        'KODES'     => $KODES[$key] ?? '',
                        'NAMAS'     => $NAMAS[$key] ?? '',
                        'NM_BRG'    => $NM_BRG[$key] ?? '',
                        'KET'       => $KET[$key] ?? '',
                        'MERK'      => $MERK[$key] ?? '',
                        'UKURAN'    => $UKURAN[$key] ?? '',
                        'KD_BRG'    => $KD_BRG[$key] ?? '',
                        'KLP'       => $KLP[$key] ?? '',
                        'HARGA'     => $HARGA[$key] ?? '',
                        'DISC'      => $DISC[$key] ?? '',
                        'BY_ANGKUT' => $BY_ANGKUT[$key] ?? '',
                        'PPN'       => $PPN[$key] ?? '',
                        'KET_KMS'   => $KET_KMS[$key] ?? '',
                        'MO'        => $MO[$key] ?? '',
                        'KLK'       => $KLK[$key] ?? '',
                        'N_POINT'   => $N_POINT[$key] ?? '',
                        'KIRA_LPP'  => $KIRA_LPP[$key] ?? '',
                        'KET_X'     => $KET_X[$key] ?? '',
                        'KET_PB'    => $KET_PB[$key] ?? '',
                        'POSTED'    => $POSTED[$key] ?? '',
                        'POSTED1'   => $POSTED1[$key] ?? '',
                        'TOLAK'     => $TOLAK[$key] ?? '',
                        'TOLAK1'    => $TOLAK1[$key] ?? '',
                        'FLAG'      => $FLAG,
                        'GOL'       => $GOL,
                        'PER'       => $PER,
                    ]
                );
            }
        }

        $ubhn = Ubhn::where('NO_BUKTI', $no_buktix)->first();

        $no_bukti = $ubhn->NO_BUKTI;

        DB::SELECT("UPDATE ubhn,  ubhnd
                    SET  ubhnd.NO_ID =  ubhn.NO_ID  WHERE  ubhn.NO_BUKTI =  ubhnd.NO_BUKTI
                    AND  ubhn.NO_BUKTI='$no_bukti';");

        // return redirect('/pp/edit/?idx=' . $pp->NO_ID . '&tipx=edit&flagz=' . $this->FLAGZ . '&golz=' . $this->GOLZ . '&judul=' . $this->judul . '');
        return redirect('/ubhn?flagz=' . $FLAGZ . '&golz=' . $GOLZ)->with(['judul' => $judul, 'golz' => $GOLZ, 'flagz' => $FLAGZ]);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    // ganti 22

    public function destroy(Request $request, Ubhn $ubhn)
    {

        $this->setFlag($request);
        $FLAGZ = $this->FLAGZ;
        $GOLZ  = $this->GOLZ;
        $judul = $this->judul;

        // ini dr mana $this->GOLZ?
        $GOLZ  = $_GET['golz'];
        $FLAGZ = $_GET['flagz'];

        $per      = session()->get('periode')['bulan'] . '/' . session()->get('periode')['tahun'];
        $cekperid = DB::SELECT("SELECT POSTED from perid WHERE PERIO='$per'");
        if ($cekperid[0]->POSTED == 1) {
            return redirect()->route('ubhn')
                ->with('status', 'Maaf Periode sudah ditutup!')
                ->with(['judul' => $this->judul, 'flagz' => $this->FLAGZ, 'golz' => $this->GOLZ]);
        }

        $deleteUbhn = Ubhn::find($ubhn->NO_ID);

        $deleteUbhn->delete();
        // return redirect('/pp?flagz=' . $FLAGZ . '&golz=J')
        return redirect('/ubhn?flagz=' . $FLAGZ . '&golz=' . $GOLZ)
            ->with(['judul' => $judul, 'flagz' => $this->FLAGZ, 'golz' => $this->GOLZ])
            ->with('statusHapus', 'Data ' . $ubhn->NO_BUKTI . ' berhasil dihapus');

    }

    public function cetak(Ubhn $ubhn)
    {
        $no_pp = $pp->NO_BUKTI;

        $file         = 'ppc';
        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

        //pp.GUDANG setelah pp.NETT dihapus
        $query = DB::SELECT("SELECT pp.NO_BUKTI, pp.TGL, pp.TOTAL_QTY, pp.NOTES,
                                    ppd.KD_BRG, ppd.NA_BRG, ppd.SATUAN, ppd.QTY
                            FROM pp, ppd
                            WHERE pp.NO_BUKTI='$no_pp' AND pp.NO_BUKTI = ppd.NO_BUKTI
                            ;
		");

        $data = [];

        foreach ($query as $key => $value) {
            array_push($data, [
                'NO_BUKTI' => $query[$key]->NO_BUKTI,
                'TGL'      => $query[$key]->TGL,
                'KD_BRG'   => $query[$key]->KD_BRG,
                'NA_BRG'   => $query[$key]->NA_BRG,
                'SATUAN'   => $query[$key]->SATUAN,
                'QTY'      => $query[$key]->QTY,
            ]);
        }

        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I");

        DB::SELECT("UPDATE pp SET POSTED = 1 WHERE pp.NO_BUKTI='$no_pp';");

    }

    public function posting(Request $request)
    {

        $CEK      = $request->input('cek');
        $NO_BUKTI = $request->input('NO_BUKTI');

        // $usrnmx = Auth::user()->username;

        $hasil = "";

        if ($CEK) {
            foreach ($CEK as $key => $value) {

                //$STA = $request->input('STA');

                $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
                $bulan   = session()->get('periode')['bulan'];
                $tahun   = substr(session()->get('periode')['tahun'], -2);

                $NO_BUKTIXZ = $NO_BUKTI[$key];

                DB::SELECT("UPDATE UBHN SET POSTED = 1 WHERE PO.NO_BUKTI='$NO_BUKTIXZ'");

            }
        } else {
            $hasil = $hasil . "Tidak ada Usulan yang dipilih! ; ";
        }

        if ($hasil != '') {
            return redirect('/ubhn/index-posting')->with('status', 'Proses Approvement Usulan ..')->with('gagal', $hasil);
        } else {
            return redirect('/ubhn/index-posting')->with('status', 'Approvement Usulan  selesai..');
        }

    }

    public function getDetailUbhn()
    {

        $no_bukti = $_GET['no_bukti'];
        $result   = DB::table('ppd')->where('NO_BUKTI', $no_bukti)->get();

        return response()->json($result);
    }

}
