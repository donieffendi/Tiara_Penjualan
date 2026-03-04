<?php

namespace App\Http\Controllers\OLain;

use App\Http\Controllers\Controller;
// ganti 1
use Illuminate\Support\Facades\Http;
use App\Models\OLain\Khusus;
use App\Models\OLain\KhususDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;


// ganti 2
class KhususController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    var $judul = '';
    var $FLAGZ = '';

    public function setFlag(Request $request)
    {
        $this->FLAGZ = $request->flagz;
        $this->GOLZ  = $request->golz;

        if ($this->FLAGZ == 'PT') {
            $this->judul = "SP Khusus Ke DC";
        }  else {
            $this->judul = "Default Judul";
        }
    }

    public function index(Request $request)
    {
        $this->setFlag($request);

        return view('olain_khusus.index')->with(['judul' => $this->judul, 'flagz' => $this->FLAGZ]);
    }

    public function ambilDetail(Request $request)
    {
        $SUB1 = $request->SUB1;
        $SUB2 = $request->SUB2;
        $LPH1 = $request->LPH1;
        $LPH2 = $request->LPH2;
        $SUPP = $request->SUPP;

        $result = DB::SELECT("SELECT a.KD_BRG, a.NA_BRG, a.KET_KEM, a.SUB, a.SUPP, b.LPH, b.HB as HARGA, b.GAK00 AS STOK, b.AK00 AS STOKZ
                                FROM brg a, brgdt b
                                WHERE a.KD_BRG=b.KD_BRG
                                AND a.SUB BETWEEN '$SUB1' AND '$SUB2'
                                AND b.LPH BETWEEN '$LPH1' AND '$LPH2'
                                AND a.SUPP='$SUPP' ORDER BY a.KD_BRG");

        return response()->json($result);
    }
    public function ambilDetailKdBrg(Request $request)
    {
        // dd($request->all());
        // periode
        if ($request->session()->has('periode')) {
            $bulan = $request->session()->get('periode')['bulan'];
        } else {
            return response()->json([]);
        }

        $item    = $request->KD_BRG;
        $barcode = $request->BARCODE;

        $query = DB::table('brg as a')
            ->join('brgdt as b', 'a.KD_BRG', '=', 'b.KD_BRG')
            ->selectRaw("
            b.AK{$bulan} AS QTY,
            a.BARCODE,
            a.KD_BRG,
            a.NA_BRG,
            a.KET_KEM,
            b.KLK,
            b.HJ,
            b.HB AS HARGA,
            a.KET,
            b.LPH,
            b.GAK00 AS STOK, b.AK00 AS STOKZ
        ");

        if (! empty($barcode)) {
            $query->where('a.BARCODE', $barcode);
        } elseif (! empty($item)) {
            $query->where('a.KD_BRG', $item);
        }

        $brg = $query->get();

        return response()->json($brg);
    }

    // ganti 4



    public function getKhusus(Request $request)
    {
        // ganti 5

        if ($request->session()->has('periode')) {
            $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
        } else {
            $periode = '';
        }

        $tipe = $request->tipe;

        $this->setFlag($request);
        $FLAGZ = $this->FLAGZ;
        $judul = $this->judul;

        $CBG = Auth::user()->CBG;

        // $khusus = DB::SELECT("SELECT * FROM po_dc_ts where per='$periode'
        //                      AND FLAG='PT' and type='DC' and golongan='$tipe' order by NO_BUKTI ");

        $khusus = DB::SELECT("SELECT * FROM po_dc_ts WHERE FLAG='PT' and type='DC' order by NO_BUKTI ");


        // ganti 6

        return Datatables::of($khusus)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                if (Auth::user()->divisi == "programmer") {
                    //CEK POSTED di index dan edit

                    // url untuk delete di index
                    $url = "'" . url("khusus/delete/" . $row->NO_ID . "/?flagz=" . $row->FLAG) . "'";
                    // batas

                    $btnEdit =   ($row->POSTED == 1) ? ' onclick= "alert(\'Transaksi ' . $row->NO_BUKTI . ' sudah diposting!\')" href="#" ' : ' href="khusus/edit/?idx=' . $row->NO_ID . '&tipx=edit&flagz=' . $row->FLAG . '&judul=' . $this->judul . '"';
                    $btnDelete = ($row->POSTED == 1) ? ' onclick= "alert(\'Transaksi ' . $row->NO_BUKTI . ' sudah diposting!\')" href="#" ' : ' onclick="deleteRow(' . $url . ')"';
                    // $btnPrint =   ' href="khusus/cetak/' . $row->NO_ID . '" target="_blank';

                $btnPrivilege =
                        '
                                <a class="dropdown-item" ' . $btnEdit . '>
                                <i class="fas fa-edit"></i>
                                    Edit
                                </a>
                                <a class="dropdown-item btn btn-danger" href="khusus/cetak/' . $row->NO_ID . '">
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
                'TGL'      => 'required'


            ]
        );

        //////     nomer otomatis
        $this->setFlag($request);
        $FLAGZ = $this->FLAGZ;
        $judul = $this->judul;

        $CBG = Auth::user()->CBG;

        $cbgSuffix = [
            'TGZ' => 'Z',
            'SOP' => 'S',
            'TMM' => 'MM',
        ];

        $suffix = $cbgSuffix[$CBG] ?? '';

        $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];

        $bulan    = session()->get('periode')['bulan'];
        $tahun    = substr(session()->get('periode')['tahun'], -2);

        $query = DB::table('po_dc_ts')->select('NO_BUKTI')->where('PER', $periode)->where('FLAG', 'PT')->where('CBG', $CBG)
            ->orderByDesc('NO_BUKTI')->limit(1)->get();

        if ($query != '[]') {
            // $query = substr($query[0]->NO_BUKTI, -4);
            // $query = str_pad($query + 1, 4, 0, STR_PAD_LEFT);
            $last = $query[0]->NO_BUKTI;

            preg_match('/-(\d{4})/', $last, $m);

            $urut = isset($m[1]) ? ((int)$m[1] + 1) : 1;

            $query = str_pad($urut, 4, '0', STR_PAD_LEFT);
            $no_bukti = 'PT' . $tahun . $bulan . '-' . $query . $suffix;
        } else {
            $no_bukti = 'PT' . $suffix . $tahun . $bulan . '-0001';
        }



        $khusus = Khusus::create(
            [
                'NO_BUKTI'         => $no_bukti,
                'TGL'              => date('Y-m-d', strtotime($request['TGL'])),
                'PER'              => $periode,
                'FLAG'             => 'PT',
                'TYPE'             => 'DC',
                'GOLONGAN'         => ($request['GOLONGAN'] == null) ? "" : $request['GOLONGAN'],
                'KODES'            => '510C',
                'NAMAS'            => 'ADIKARYA PANGAN FRESHINDO',
                'KS'               => 'Y',
                'UTUH'             => 'U',
                'CBG'              => $CBG,
                'TOTAL_QTY'        => (float) str_replace(',', '', $request['TTOTAL_QTY']),
                'USRNM'            => Auth::user()->username,
                'TG_SMP'           => Carbon::now(),
            ]
        );


        $REC        = $request->input('REC');
        $KD_BRG     = $request->input('KD_BRG');
        $NA_BRG     = $request->input('NA_BRG');
        $KET_KEM     = $request->input('KET_KEM');
        $QTY        = $request->input('QTY');
        $HARGA        = $request->input('HARGA');
        $TOTAL        = $request->input('TOTAL');
        $STOK        = $request->input('STOK');
        $STOKZ        = $request->input('STOKZ');

        // Check jika value detail ada/tidak
        if ($REC) {
            foreach ($REC as $key => $value) {
                // Declare new data di Model
                $detail    = new KhususDetail;
                // Insert ke Database
                $detail->NO_BUKTI    = $no_bukti;
                $detail->rec         = $REC[$key];
                $detail->PER         = $periode;
                $detail->FLAG        = $FLAGZ;
                $detail->KD_BRG      = ($KD_BRG[$key] == null) ? "" :  $KD_BRG[$key];
                $detail->NA_BRG      = ($NA_BRG[$key] == null) ? "" :  $NA_BRG[$key];
                $detail->KET_KEM      = ($KET_KEM[$key] == null) ? "" :  $KET_KEM[$key];
                $detail->QTY         = (float) str_replace(',', '', $QTY[$key]);
                $detail->HARGA         = (float) str_replace(',', '', $HARGA[$key]);
                $detail->TOTAL         = (float) str_replace(',', '', $TOTAL[$key]);
                $detail->STOK         = (float) str_replace(',', '', $STOK[$key]);
                $detail->STOKZ         = (float) str_replace(',', '', $STOKZ[$key]);
                $detail->save();
            }
        }

        $no_buktix = $no_bukti;

        $khusus = Khusus::where('NO_BUKTI', $no_buktix)->first();


        DB::SELECT("UPDATE po_dc_ts,  pod_dc_ts
                            SET  pod_dc_ts.ID =  po_dc_ts.NO_ID  WHERE  po_dc_ts.NO_BUKTI =  pod_dc_ts.NO_BUKTI
							AND  po_dc_ts.NO_BUKTI='$no_buktix';");

        // return redirect('/pp/edit/?idx=' . $pp->NO_ID . '&tipx=edit&flagz=' . $this->FLAGZ . '&golz=' . $this->GOLZ . '&judul=' . $this->judul . '');
        return redirect('/khusus?flagz=' . $FLAGZ )->with(['judul' => $judul, 'flagz' => $FLAGZ]);
    }

    public function edit(Request $request, Khusus $khusus)
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

            $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from po_dc_ts
		                 where PER ='$per' and FLAG ='$this->FLAGZ'
                         AND CBG = '$CBG'
						 and NO_BUKTI = '$buktix'
		                 ORDER BY NO_BUKTI ASC  LIMIT 1");


            if (!empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = 0;
            }
        }

        if ($tipx == 'top') {


            $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from po_dc_ts
		                 where PER ='$per'
						 and FLAG ='$this->FLAGZ'
                         AND CBG = '$CBG'
		                 ORDER BY NO_BUKTI ASC  LIMIT 1");


            if (!empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = 0;
            }
        }


        if ($tipx == 'prev') {

            $buktix = $request->buktix;

            $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from po_dc_ts
		             where PER ='$per'
					 and FLAG ='$this->FLAGZ'
                     AND CBG = '$CBG'
                     and NO_BUKTI <
					 '$buktix' ORDER BY NO_BUKTI DESC LIMIT 1");


            if (!empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = $idx;
            }
        }


        if ($tipx == 'next') {


            $buktix = $request->buktix;

            $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from po_dc_ts
		             where PER ='$per'
					 and FLAG ='$this->FLAGZ'
                     AND CBG = '$CBG'
                     and NO_BUKTI >
					 '$buktix' ORDER BY NO_BUKTI ASC LIMIT 1");

            if (!empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = $idx;
            }
        }

        if ($tipx == 'bottom') {

            $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from po_dc_ts
						where PER ='$per'
						and FLAG ='$this->FLAGZ'
                        AND CBG = '$CBG'
		                ORDER BY NO_BUKTI DESC  LIMIT 1");

            if (!empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = 0;
            }
        }


        if ($tipx == 'undo' || $tipx == 'search') {

            $tipx = 'edit';
        }



        if ($idx != 0) {
            $khusus = Khusus::where('NO_ID', $idx)->first();
        } else {
            $khusus = new Khusus;
            $khusus->TGL = Carbon::now();
        }

        $no_bukti = $khusus->NO_BUKTI;
        // $khususDetail = DB::table('pod_dc_ts')->where('NO_BUKTI', $no_bukti)->orderBy('rec')->get();
        $khususDetail = DB::table('pod_dc_ts as p')
                            ->leftJoin('brgdt as b', 'p.KD_BRG', '=', 'b.KD_BRG')
                            ->where('p.NO_BUKTI', $no_bukti)
                            ->orderBy('p.rec')
                            ->select('p.*', 'b.LPH')
                            ->get();

        $data = [
            'header'        => $khusus,
            'detail'        => $khususDetail

        ];


        return view('olain_khusus.edit', $data)
            ->with(['tipx' => $tipx, 'idx' => $idx, 'flagz' => $this->FLAGZ, 'judul' => $this->judul]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    // ganti 18
    public function update(Request $request, Khusus $khusus)
    {

        $this->validate(
            $request,
            [

                'TGL'      => 'required'
            ]
        );

        $this->setFlag($request);
        $FLAGZ = $this->FLAGZ;
        $judul = $this->judul;

        $CBG = Auth::user()->CBG;


        $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];


        $khusus->update(
            [
                'TGL'              => date('Y-m-d', strtotime($request['TGL'])),
                'TOTAL_QTY'        => (float) str_replace(',', '', $request['TTOTAL_QTY']),
                'GOLONGAN'         => ($request['GOLONGAN'] == null) ? "" : $request['GOLONGAN'],
                'USRNM'            => Auth::user()->username,
                'TG_SMP'           => Carbon::now(),
                'updated_by'       => Auth::user()->username,
                'FLAG'             => 'PT',
                'CBG'              => $CBG,
            ]
        );

        $no_buktix = $khusus->NO_BUKTI;

        // Update Detail
        $length = sizeof($request->input('REC'));
        $NO_ID  = $request->input('NO_ID');

        $REC    = $request->input('REC');

        $KD_BRG = $request->input('KD_BRG');
        $NA_BRG = $request->input('NA_BRG');
        $SATUAN = $request->input('KET_KEM');
        $QTY    = $request->input('QTY');
        $HARGA    = $request->input('HARGA');
        $TOTAL    = $request->input('TOTAL');
        $STOK    = $request->input('STOK');
        $STOKZ    = $request->input('STOKZ');

        $query = DB::table('pod_dc_ts')->where('NO_BUKTI', $request->NO_BUKTI)->whereNotIn('NO_ID',  $NO_ID)->delete();

        // Update / Insert
        for ($i = 0; $i < $length; $i++) {
            // Insert jika NO_ID baru
            if ($NO_ID[$i] == 'new') {
                $insert = KhususDetail::create(
                    [
                        'NO_BUKTI'   => $request->NO_BUKTI,
                        'rec'        => $REC[$i],
                        'PER'        => $periode,
                        'FLAG'       => $this->FLAGZ,
                        'KD_BRG'     => ($KD_BRG[$i] == null) ? "" :  $KD_BRG[$i],
                        'NA_BRG'     => ($NA_BRG[$i] == null) ? "" :  $NA_BRG[$i],
                        'SATUAN'     => ($SATUAN[$i] == null) ? "" :  $SATUAN[$i],
                        'KODES'     => ($KODES[$i] == null) ? "" :  $parts[0],
                        'NAMAS'     => ($KODES[$i] == null) ? "" :  $parts[1],
                        'QTY'        => (float) str_replace(',', '', $QTY[$i]),
                        'HARGA'        => (float) str_replace(',', '', $HARGA[$i]),
                        'TOTAL'        => (float) str_replace(',', '', $TOTAL[$i]),
                        'STOK'        => (float) str_replace(',', '', $STOK[$i]),
                        'STOKZ'        => (float) str_replace(',', '', $STOKZ[$i]),

                        ]
                    );
                } else {
                    // Update jika NO_ID sudah ada
                    $upsert = KhususDetail::updateOrCreate(
                        [
                        'NO_BUKTI'  => $request->NO_BUKTI,
                        'NO_ID'     => (int) str_replace(',', '', $NO_ID[$i])
                    ],

                    [
                        'rec'        => $REC[$i],

                        'KD_BRG'     => ($KD_BRG[$i] == null) ? "" :  $KD_BRG[$i],
                        'NA_BRG'     => ($NA_BRG[$i] == null) ? "" :  $NA_BRG[$i],
                        'SATUAN'     => ($SATUAN[$i] == null) ? "" :  $SATUAN[$i],
                        'KODES'     => ($KODES[$i] == null) ? "" :  $parts[0],
                        'NAMAS'     => ($KODES[$i] == null) ? "" :  $parts[1],
                        'QTY'        => (float) str_replace(',', '', $QTY[$i]),
                        'HARGA'        => (float) str_replace(',', '', $HARGA[$i]),
                        'TOTAL'        => (float) str_replace(',', '', $TOTAL[$i]),
                        'STOK'        => (float) str_replace(',', '', $STOK[$i]),
                        'STOKZ'        => (float) str_replace(',', '', $STOKZ[$i]),
                        'FLAG'       => $this->FLAGZ,
                        'PER'        => $periode,
                        ]
                    );
                }
            }

        $khusus = Khusus::where('NO_BUKTI', $no_buktix)->first();

        $no_bukti = $khusus->NO_BUKTI;

        DB::SELECT("UPDATE po_dc_ts,  pod_dc_ts
                    SET  pod_dc_ts.ID =  po_dc_ts.NO_ID  WHERE  po_dc_ts.NO_BUKTI =  pod_dc_ts.NO_BUKTI
                    AND  po_dc_ts.NO_BUKTI='$no_bukti';");

        // return redirect('/pp/edit/?idx=' . $pp->NO_ID . '&tipx=edit&flagz=' . $this->FLAGZ . '&golz=' . $this->GOLZ . '&judul=' . $this->judul . '');
        return redirect('/khusus?flagz=' . $FLAGZ)->with(['judul' => $judul, 'flagz' => $FLAGZ]);
    }
    /**
     * Remove the specified resource from storage.
    *
    * @param  \App\Models\Master\Rute  $rute
    * @return \Illuminate\Http\Response
    */

    // ganti 22

    public function destroy(Request $request, Khusus $khusus)
    {

    $this->setFlag($request);
    $FLAGZ = $this->FLAGZ;
    $judul = $this->judul;

    $FLAGZ = $_GET['flagz'];

    $per = session()->get('periode')['bulan'] . '/' . session()->get('periode')['tahun'];
    $cekperid = DB::SELECT("SELECT POSTED from perid WHERE PERIO='$per'");
        if ($cekperid[0]->POSTED == 1) {
            return redirect()->route('khusus')
                ->with('status', 'Maaf Periode sudah ditutup!')
                ->with(['judul' => $this->judul, 'flagz' => $this->FLAGZ]);
            }

            $deleteKhusus = Khusus::find($khusus->NO_ID);

            $deleteKhusus->delete();
            // return redirect('/pp?flagz=' . $FLAGZ . '&golz=J')
            return redirect('/khusus?flagz=' . $FLAGZ)
            ->with(['judul' => $judul, 'flagz' => $this->FLAGZ])
            ->with('statusHapus', 'Data ' . $khusus->NO_BUKTI . ' berhasil dihapus');
        }

        public function cetak(Khusus $khusus)
        {
            $no_khusus = $khusus->NO_BUKTI;

            $file     = 'pob';
            $PHPJasperXML = new PHPJasperXML();
            $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

            //pp.GUDANG setelah pp.NETT dihapus
            $query = DB::SELECT("SELECT a.NO_BUKTI, a.TGL, b.qty, a.KODES, a.NAMAS,
                                    b.KD_BRG, b.NA_BRG, b.harga, b.total
                            FROM po_dc_ts a, pod_dc_ts b
                            WHERE a.NO_BUKTI='$no_khusus' AND a.NO_BUKTI = b.NO_BUKTI
                            ;

		");


            $data = [];

            $data = json_decode(json_encode($query), true);

            $PHPJasperXML->setData($data);
            ob_end_clean();
            $PHPJasperXML->outpage("I");

        // DB::SELECT("UPDATE pp SET POSTED = 1 WHERE pp.NO_BUKTI='$no_pp';");
    }
}
