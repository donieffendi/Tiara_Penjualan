<?php
namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
// ganti 1

use App\Models\OTransaksi\Ubsup;
use App\Models\OTransaksi\UbsupDetail;
use App\Models\Master\Sup;
use App\Models\Master\SupDetail;
use Auth;
use Carbon\Carbon;
use DataTables;
use DB;
use Illuminate\Http\Request;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";
use PHPJasperXML;

// ganti 2
class UbsupController extends Controller
{
    protected $view;
    protected $judul;
    protected $FLAGZ;
    protected $GOLZ;

    public function setFlag(Request $request)
    {
        $this->FLAGZ = $request->flagz;
        $this->GOLZ  = $request->golz;

        switch (true) {
            case ($request->flagz == 'UE' && $request->golz == 'UE'):
                $this->judul = "Usulan Rubah Email Suplier";
                $this->view  = 'otransaksi_ubsup.index';
                break;
            case ($request->flagz == 'HS' && $request->golz == 'HS'):
                $this->judul = "Usulan Hapus Suplier";
                $this->view  = 'otransaksi_ubsup.index';
                break;
            case ($request->flagz == 'PU' && $request->golz == 'PU'):
                $this->judul = "Posting Usulan Hapus Suplier";
                $this->view  = 'otransaksi_ubsup.index_posting';
                break;
            case ($request->flagz == 'PE' && $request->golz == 'PE'):
                $this->judul = "Posting Usulan Ubah Email Suplier";
                $this->view  = 'otransaksi_ubsup.index_posting_email';
                break;
        }
    }

    public function index(Request $request)
    {
        $this->setFlag($request);
    
        // Cek apakah $this->view sudah terisi
        if (!$this->view) {
            return response()->json(['error' => 'View tidak ditemukan'], 400); // Atau alihkan ke halaman error
        }
    
        return view($this->view)->with([
            'judul' => $this->judul,
            'flagz' => $this->FLAGZ,
            'golz'  => $this->GOLZ
        ]);
    }

    public function index_post(Request $request)
    {

        return view('otransaksi_ubsup.post');

    }

    public function browse(Request $request)
    {
        $CBG = Auth::user()->CBG;

        $filterbukti = '';
        if ($request->NO_BUKTI) {
            $filterbukti = " AND ubsup.NO_BUKTI='" . $request->NO_BUKTI . "' ";
        }
        $ubsup = DB::SELECT("SELECT * from ubsup, ubsupd where ubsup.NO_BUKTI=ubsupd.NO_BUKTI $filterbukti ");

        return response()->json($ubsup);
    }
    public function getUbsup(Request $request)
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

        $ubsup = DB::SELECT("SELECT *, POSTED as cek1, POSTED1 as cek2 from ubsup  WHERE PER='$periode' AND CBG = '$CBG' AND FLAG = '$FLAGZ' ORDER BY NO_BUKTI ");

        // ganti 6

        return Datatables::of($ubsup)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                if (Auth::user()->divisi == "programmer" || Auth::user()->divisi == "non") {
                    //CEK POSTED di index dan edit

                    // url untuk delete di index
                    $url = "'" . url("ubsup/delete/" . $row->NO_ID . "/?flagz=" . $row->FLAG . "&golz=" . $row->GOL) . "'";
                    // batas

                    $btnEdit   = ($row->POSTED == 1) ? ' onclick= "alert(\'Transaksi ' . $row->NO_BUKTI . ' sudah dippsting!\')" href="#" ' : ' href="ubsup/edit/?idx=' . $row->NO_ID . '&tipx=edit&flagz=' . $row->FLAG . '&judul=' . $this->judul . '&golz=' . $row->GOL . '"';
                    $btnDelete = ($row->POSTED == 1) ? ' onclick= "alert(\'Transaksi ' . $row->NO_BUKTI . ' sudah diposting!\')" href="#" ' : ' onclick="deleteRow(' . $url . ')"';

                    $btnPrivilege =
                    '
                                <a class="dropdown-item" ' . $btnEdit . '>
                                <i class="fas fa-edit"></i>
                                    Edit
                                </a>
                                <a class="dropdown-item btn btn-danger" href="ubsup/cetak/' . $row->NO_ID . '">
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
        $query = DB::table('ubsup')
            ->where('NO_BUKTI', 'like', $FLAGZ . $CBG . $tahun . $bulan . '-%')
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
        $no_bukti = $FLAGZ . $CBG . $tahun . $bulan . '-' . $newNumber;

        $ubsup = Ubsup::create([
            'NO_BUKTI' => $no_bukti,
            'TGL'      => date('Y-m-d', strtotime($request['TGL'])),
            'PER'      => $periode,
            // 'FLAG'     => 'UE',
            'FLAG'     => $FLAGZ,
            'CBG'      => $CBG,
            'NOTES'    => $request['NOTES'],
        ]);

        $REC       = $request->input('REC');
        $KODES     = $request->input('KODES');
        $NAMAS     = $request->input('NAMAS');
        $EMAIL    = $request->input('EMAIL');
        $E_BARU    = $request->input('E_BARU');


        $FLAG = $FLAGZ;
        $PER  = $periode;
        $GOL  = $GOLZ;

        if ($REC) {
            foreach ($REC as $key => $value) {
                $detail            = new UbsupDetail;
                $detail->NO_BUKTI  = $no_bukti;
                $detail->REC       = $REC[$key] ?? '';
                $detail->KODES     = $KODES[$key] ?? '';
                $detail->NAMAS     = $NAMAS[$key] ?? '';
                $detail->EMAIL     = $EMAIL[$key] ?? '';
                $detail->E_BARU    = $E_BARU[$key] ?? '';
                $detail->save();
            }
        }
        // dd($detail);
        $no_buktix = $no_bukti;

        $ubsup = Ubsup::where('NO_BUKTI', $no_buktix)->first();

        DB::SELECT("UPDATE ubsup,  ubsupd
                            SET  ubsupd.NO_ID =  ubsup.NO_ID  WHERE  ubsup.NO_BUKTI =  ubsupd.NO_BUKTI
							AND  ubsup.NO_BUKTI='$no_buktix';");

        // return redirect('/pp/edit/?idx=' . $pp->NO_ID . '&tipx=edit&flagz=' . $this->FLAGZ . '&golz=' . $this->GOLZ . '&judul=' . $this->judul . '');
        return redirect('/ubsup?flagz=' . $FLAGZ . '&golz=' . $GOLZ)->with(['judul' => $judul, 'golz' => $GOLZ, 'flagz' => $FLAGZ]);

    }

    public function edit(Request $request, Ubsup $ubsup)
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

            $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from ubsup
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

            $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from ubsup
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

            $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from ubsup
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

            $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from ubsup
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

            $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from ubsup
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
            $ubsup = Ubsup::where('NO_ID', $idx)->first();
        } else {
            $ubsup      = new Ubsup;
            $ubsup->TGL = Carbon::now();

        }

        $no_bukti   = $ubsup->NO_BUKTI;
        $ubsupDetail = DB::table('ubsupd')->where('NO_BUKTI', $no_bukti)->get();

        $data = [
            'header' => $ubsup,
            'detail' => $ubsupDetail,

        ];

        return view('otransaksi_ubsup.edit', $data)
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

    public function update(Request $request, Ubsup $ubsup)
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

        $ubsup->update([
            'TGL'        => date('Y-m-d', strtotime($request['TGL'])),
            'USRNM'      => Auth::user()->username,
            'updated_by' => Auth::user()->username,
            'FLAG'       => $FLAGZ,
            'GOL'        => $GOLZ,
            'CBG'        => $CBG,
            'NOTES'      => $request['NOTES'],
        ]);

        $no_buktix = $ubsup->NO_BUKTI;
        $NO_ID     = $request->input('NO_ID');
        $REC       = $request->input('REC');
        $KODES     = $request->input('KODES');
        $NAMAS     = $request->input('NAMAS');
        $EMAIL     = $request->input('EMAIL');
        $E_BARU    = $request->input('E_BARU');
        $FLAG      = $FLAGZ;
        $PER       = $periode;
        $GOL       = $GOLZ;

        // Hapus data yang tidak ada di request
        DB::table('ubsupd')->where('NO_BUKTI', $request->NO_BUKTI)->whereNotIn('NO_ID', $NO_ID)->delete();

        foreach ($REC as $key => $value) {
            if ($NO_ID[$key] == 'new') {
                UbsupDetail::create([
                    'NO_BUKTI'  => $request->NO_BUKTI,
                    'REC'       => $REC[$key],
                    'PER'       => $PER,
                    'FLAG'      => $FLAG,
                    'GOL'       => $GOL,
                    'KODES'     => $KODES[$key] ?? '',
                    'NAMAS'     => $NAMAS[$key] ?? '',
                    'EMAIL'     => $EMAIL[$key] ?? '',
                    'E_BARU'    => $E_BARU[$key] ?? '',
                ]);
            } else {
                UbsupDetail::updateOrCreate(
                    [
                        'NO_BUKTI' => $request->NO_BUKTI,
                        'NO_ID'    => (int) str_replace(',', '', $NO_ID[$key]),
                    ],
                    [
                        'REC'       => $REC[$key],
                        'KODES'     => $KODES[$key] ?? '',
                        'NAMAS'     => $NAMAS[$key] ?? '',
                        'EMAIL'     => $EMAIL[$key] ?? '',
                        'E_BARU'    => $E_BARU[$key] ?? '',
                        'FLAG'      => $FLAG,
                        'GOL'       => $GOL,
                        'PER'       => $PER,
                    ]
                );
            }
        }

        $ubsup = Ubsup::where('NO_BUKTI', $no_buktix)->first();

        $no_bukti = $ubsup->NO_BUKTI;

        DB::SELECT("UPDATE ubsup,  ubsupd
                    SET  ubsupd.NO_ID =  ubsup.NO_ID  WHERE  ubsup.NO_BUKTI =  ubsupd.NO_BUKTI
                    AND  ubsup.NO_BUKTI='$no_bukti';");

        // DB::SELECT("UPDATE ubsup
        //             SET posted = 1
        //             WHERE no_bukti = '$no_bukti'
        //             AND NOT EXISTS (
        //                 SELECT 1
        //                 FROM ubsupd
        //                 WHERE no_bukti = '$no_bukti'
        //                 AND (posted <> 1 OR posted1 <> 1)
        //             )");

        // return redirect('/pp/edit/?idx=' . $pp->NO_ID . '&tipx=edit&flagz=' . $this->FLAGZ . '&golz=' . $this->GOLZ . '&judul=' . $this->judul . '');
        return redirect('/ubsup?flagz=' . $FLAGZ . '&golz=' . $GOLZ)->with(['judul' => $judul, 'golz' => $GOLZ, 'flagz' => $FLAGZ]);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    // ganti 22

    public function destroy(Request $request, Ubsup $ubsup)
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
            return redirect()->route('ubsup')
                ->with('status', 'Maaf Periode sudah ditutup!')
                ->with(['judul' => $this->judul, 'flagz' => $this->FLAGZ, 'golz' => $this->GOLZ]);
        }

        $deleteUbsup = Ubsup::find($ubsup->NO_ID);

        $deleteUbsup->delete();
        // return redirect('/pp?flagz=' . $FLAGZ . '&golz=J')
        return redirect('/ubsup?flagz=' . $FLAGZ . '&golz=' . $GOLZ)
            ->with(['judul' => $judul, 'flagz' => $this->FLAGZ, 'golz' => $this->GOLZ])
            ->with('statusHapus', 'Data ' . $ubsup->NO_BUKTI . ' berhasil dihapus');

    }

    public function cetak(Ubsup $ubsup)
    {
        $no_pp = $ubsup->NO_BUKTI;

        $file         = 'ppc';
        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

        //pp.GUDANG setelah pp.NETT dihapus
        $query = DB::SELECT("SELECT ubsup.NO_BUKTI, ubsup.TGL, ubsup.TOTAL_QTY, ubsup.NOTES,
                                    ubsupd.KD_BRG, ubsupd.NA_BRG, ubsupd.SATUAN, ubsupd.QTY
                            FROM ubsup, ubsupd
                            WHERE ubsup.NO_BUKTI='$no_pp' AND ubsup.NO_BUKTI = ubsupd.NO_BUKTI
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

    // public function posting(Request $request)
    // {

    //     $CEK      = $request->input('cek');
    //     $NO_BUKTI = $request->input('NO_BUKTI');

    //     // $usrnmx = Auth::user()->username;

    //     $hasil = "";

    //     if ($CEK) {
    //         foreach ($CEK as $key => $value) {

    //             //$STA = $request->input('STA');

    //             $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
    //             $bulan   = session()->get('periode')['bulan'];
    //             $tahun   = substr(session()->get('periode')['tahun'], -2);

    //             $NO_BUKTIXZ = $NO_BUKTI[$key];

    //             DB::SELECT("UPDATE ubsup SET POSTED = 1 WHERE PO.NO_BUKTI='$NO_BUKTIXZ'");

    //         }
    //     } else {
    //         $hasil = $hasil . "Tidak ada Usulan yang dipilih! ; ";
    //     }

    //     if ($hasil != '') {
    //         return redirect('/ubsup/index-posting')->with('status', 'Proses Approvement Usulan ..')->with('gagal', $hasil);
    //     } else {
    //         return redirect('/ubsup/index-posting')->with('status', 'Approvement Usulan  selesai..');
    //     }

    // }

    public function getDetailUbsup()
    {

        $no_bukti = $_GET['no_bukti'];
        $result   = DB::table('ubsupd')->where('NO_BUKTI', $no_bukti)->get();

        return response()->json($result);
    }

    public function posting_hapus_sup(Request $request)
    {
        if (! $request->isMethod('post')) {
            return response()->json(['error' => 'Method Not Allowed'], 405);
        }

        $data = $request->input('posted');

        if (! $data) {
            return response()->json(['error' => 'Tidak ada data yang dikirim'], 400);
        }

        foreach ($data as $id => $posted) {
            DB::table('ubsup')->where('NO_ID', $id)->update(['POSTED' => $posted]);
        }

        return response()->json(['message' => 'Status berhasil diperbarui']);
    }

    public function posting_usul_emsup(Request $request)
    {
        if (! $request->isMethod('post')) {
            return response()->json(['error' => 'Method Not Allowed'], 405);
        }

        $data = $request->input('posted');

        if (! $data) {
            return response()->json(['error' => 'Tidak ada data yang dikirim'], 400);
        }

        foreach ($data as $id => $posted) {
            DB::table('ubsup')->where('NO_ID', $id)->update(['POSTED' => $posted]);
        }

        return response()->json(['message' => 'Status berhasil diperbarui']);
    }
}
