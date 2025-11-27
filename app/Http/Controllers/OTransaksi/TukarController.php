<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
// ganti 1

use App\Models\OTransaksi\Tukar;
use App\Models\OTransaksi\TukarDetail;
use App\Models\Master\Sup;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use DB;
use Carbon\Carbon;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;

// ganti 2
class TukarController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    var $judul = '';
    var $FLAGZ = '';

    function setFlag(Request $request)
    {
        if ($request->flagz == 'TK') {
            $this->judul = "Tukar Barang";
        } elseif ($request->flagz == 'PTB') {
            $this->judul = "Posting Tukar Barang";
        }

        $this->FLAGZ = $request->flagz;
    }

    public function index(Request $request)
    {
        $this->setFlag($request);
        if ($this->FLAGZ == 'TK') {
            $view = "otransaksi_tukar.index";
        } else if ($this->FLAGZ == 'PTB') {
            $view = "otransaksi_tukar.index_posting";
        } else {
            $view = "otransaksi_tukar.index";
        }
        return view($view)->with([
            'judul' => $this->judul,
            'flagz' => $this->FLAGZ,
        ]);
    }
    public function browse(Request $request)
    {
        $golz = $request->GOL;

        $CBG = Auth::user()->CBG;

        $tukar = DB::SELECT("SELECT distinct PO.NO_BUKTI , PO.KODEC, PO.NAMAC,
		                  PO.ALAMAT, PO.KOTA from tukar, tukard
                          WHERE PO.NO_BUKTI = POD.NO_BUKTI AND PO.GOL ='$golz' AND CBG = '$CBG'
                          AND POD.SISA > 0	");
        return restukarnse()->json($tukar);
    }

    public function browseuang(Request $request)
    {
        $CBG = Auth::user()->CBG;

        $tukar = DB::SELECT("SELECT NO_BUKTI,TGL,  KODEC, NAMAC, TOTAL,  BAYAR,
                        (TOTAL-BAYAR) AS SISA, ALAMAT, KOTA from tukar
		                WHERE LNS <> 1 AND CBG = '$CBG' ORDER BY NO_BUKTI; ");

        return response()->json($tukar);
    }


    public function index_posting(Request $request)
    {

        return view('otransaksi_tukar.post');
    }

    //SHELVI

    public function browse_detail(Request $request)
    {
        $filterbukti = '';
        if ($request->NO_PO) {

            $filterbukti = " WHERE a.NO_BUKTI='" . $request->NO_PO . "' AND a.KD_BRG = b.KD_BRG ";
        }
        $tukard = DB::SELECT("SELECT a.REC, a.KD_BRG, a.NA_BRG, a.SATUAN , a.QTY, a.HARGA, a.KIRIM, a.SISA,
                                b.SATUAN AS SATUAN_PO, a.QTY AS QTY_PO, '1' AS X
                            from tukard a, brg b
                            $filterbukti ORDER BY NO_BUKTI ");


        return response()->json($tukard);
    }


    public function browse_detail2(Request $request)
    {
        $filterbukti = '';
        if ($request->NO_PO) {

            $filterbukti = " WHERE NO_BUKTI='" . $request->NO_PO . "' AND a.KD_BRG = b.KD_BRG ";
        }
        $tukard = DB::SELECT("SELECT a.REC, a.KD_BRG, a.NA_BRG, a.SATUAN , a.QTY, a.HARGA, a.KIRIM, a.SISA,
                                b.SATUAN AS SATUAN_PO, a.QTY AS QTY_PO, '1' AS X
                            from tukard a, brg b
                            $filterbukti ORDER BY NO_BUKTI ");


        return response()->json($tukard);
    }
    // ganti 4



    public function getTukar(Request $request)
    {
        // ganti 5

        if ($request->session()->has('periode')) {
            $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
        } else {
            $periode = '';
        }

        $this->setFlag($request);
        $FLAGZ = $this->FLAGZ;
        $judul = $this->judul;

        $CBG = Auth::user()->CBG;

        $tukar = DB::SELECT("SELECT * from tukar  WHERE PER='$periode' and FLAG ='$this->FLAGZ'
                            AND CBG = '$CBG'
                            ORDER BY NO_BUKTI ");


        // ganti 6

        return Datatables::of($tukar)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                if (Auth::user()->divisi == "programmer") {
                    //CEK POSTED di index dan edit

                    // url untuk delete di index
                    $url = "'" . url("tukar/delete/" . $row->NO_ID . "/?flagz=" . $row->FLAG) . "'";
                    // batas

                    $btnEdit =   ($row->POSTED == 1) ? ' onclick= "alert(\'Transaksi ' . $row->NO_BUKTI . ' sudah diposting!\')" href="#" ' : ' href="tukar/edit/?idx=' . $row->NO_ID . '&tipx=edit&flagz=' . $row->FLAG . '&judul=' . $this->judul . '"';
                    $btnDelete = ($row->POSTED == 1) ? ' onclick= "alert(\'Transaksi ' . $row->NO_BUKTI . ' sudah diposting!\')" href="#" ' : ' onclick="deleteRow(' . $url . ')" ';


                    $btnPrivilege =
                        '
                                <a class="dropdown-item" ' . $btnEdit . '>
                                <i class="fas fa-edit"></i>
                                    Edit
                                </a>
                                <a class="dropdown-item btn btn-danger" href="tukar/cetak/' . $row->NO_ID . '">
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


            ->addColumn('cek', function ($row) {
                return
                    '
                    <input type="checkbox" name="cek[]" class="form-control cek" ' . (($row->POSTED == 1) ? "checked" : "") . '  value="' . $row->NO_ID . '" ' . (($row->POSTED == 2) ? "disabled" : "") . '></input>
                    ';
            })

            ->rawColumns(['action', 'cek'])
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

        $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];

        $bulan    = session()->get('periode')['bulan'];
        $tahun    = substr(session()->get('periode')['tahun'], -2);

        $query = DB::table('tukar')->select('NO_BUKTI')->where('PER', $periode)->where('FLAG', $this->FLAGZ)->where('CBG', $CBG)
            ->orderByDesc('NO_BUKTI')->limit(1)->get();

        if ($query != '[]') {
            $query = substr($query[0]->NO_BUKTI, -4);
            $query = str_pad($query + 1, 4, 0, STR_PAD_LEFT);
            $no_bukti = $this->FLAGZ . $CBG . $tahun . $bulan . '-' . $query;
        } else {
            $no_bukti = $this->FLAGZ . $CBG . $tahun . $bulan . '-0001';
        }

        $tukar = Tukar::create(
            [
                'NO_BUKTI'         => $no_bukti,
                'TGL'              => date('Y-m-d', strtotime($request['TGL'])),
                'PER'              => $periode,
                'FLAG'             => $this->FLAGZ,
                'NOTES'            => ($request['NOTES'] == null) ? "" : $request['NOTES'],
                'KODEC'            => ($request['KODEC'] == null) ? "" : $request['KODEC'],
                'NAMAC'            => ($request['NAMAC'] == null) ? "" : $request['NAMAC'],
                'ALAMAT'           => ($request['ALAMAT'] == null) ? "" : $request['ALAMAT'],
                'KOTA'             => ($request['KOTA'] == null) ? "" : $request['KOTA'],
                'TYPE'             => ($request['TYPE'] == null) ? "" : $request['TYPE'],
                'HARI'            => (float) str_replace(',', '', $request['HARI']),
                'TOTAL_QTY'        => (float) str_replace(',', '', $request['TTOTAL_QTY']),
                'TOTAL'            => (float) str_replace(',', '', $request['TTOTAL']),
                'USRNM'            => Auth::user()->username,
                'TG_SMP'           => Carbon::now(),
                'created_by'       => Auth::user()->username,
                'CBG'              => $CBG,
            ]
        );


        $REC        = $request->input('REC');
        $KD_BRG    = $request->input('KD_BRG');
        $NA_BRG    = $request->input('NA_BRG');
        $SATUAN    = $request->input('SATUAN');
        // $QTYC	= $request->input('QTYC');
        // $QTYR	= $request->input('QTYR');
        $QTY    = $request->input('QTY');
        $HARGA    = $request->input('HARGA');
        $TOTAL    = $request->input('TOTAL');
        $KET    = $request->input('KET');

        // Check jika value detail ada/tidak
        if ($REC) {
            foreach ($REC as $key => $value) {
                // Declare new data di Model
                $detail    = new TukarDetail;

                // Insert ke Database
                $detail->NO_BUKTI    = $no_bukti;
                $detail->REC         = $REC[$key];
                $detail->PER         = $periode;
                $detail->CBG         = $CBG;
                $detail->FLAG        = $this->FLAGZ;
                $detail->KD_BRG         = ($KD_BRG[$key] == null) ? "" :  $KD_BRG[$key];
                $detail->NA_BRG         = ($NA_BRG[$key] == null) ? "" :  $NA_BRG[$key];
                $detail->SATUAN         = ($SATUAN[$key] == null) ? "" :  $SATUAN[$key];
                $detail->HARGA         = (float) str_replace(',', '', $HARGA[$key]);
                $detail->TOTAL         = (float) str_replace(',', '', $TOTAL[$key]);
                $detail->QTY         = (float) str_replace(',', '', $QTY[$key]);
                $detail->KET         = ($KET[$key] == null) ? "" :  $KET[$key];
                $detail->save();
            }
        }

        $variablell = DB::select('call tukarins(?)', array($no_bukti));

        $no_buktix = $no_bukti;

        $tukar = Tukar::where('NO_BUKTI', $no_buktix)->first();


        DB::SELECT("UPDATE tukar, cust
                    SET tukar.NAMAC = cust.NAMAC, tukar.ALAMAT = cust.ALAMAT, tukar.KOTA = cust.KOTA, tukar.PKP=cust.PKP, tukar.HARI = cust.HARI  WHERE tukar.KODEC = cust.KODEC
                    AND tukar.NO_BUKTI='$no_buktix';");

        DB::SELECT("UPDATE tukar,  tukard
                            SET  tukard.ID =  tukar.NO_ID  WHERE  tukar.NO_BUKTI =  tukard.NO_BUKTI
							AND  tukar.NO_BUKTI='$no_buktix';");

        // return redirect('/tukar/edit/?idx=' . $tukar->NO_ID . '&tipx=edit&flagz=' . $this->FLAGZ . '&judul=' . $this->judul . '');
        return redirect('/tukar?flagz=' . $FLAGZ)->with(['judul' => $judul, 'flagz' => $FLAGZ]);
    }

    public function edit(Request $request, Tukar $tukar)
    {


        $per = session()->get('periode')['bulan'] . '/' . session()->get('periode')['tahun'];


        // $cekperid = DB::SELECT("SELECT POSTED from perid WHERE PERIO='$per'");
        // if ($cekperid[0]->POSTED==1)
        // {
        //     return redirect('/tukar')
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

            $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from tukar
		                 where PER ='$per' and FLAG ='$this->FLAGZ'
						 and NO_BUKTI = '$buktix' AND CBG = '$CBG'
		                 ORDER BY NO_BUKTI ASC  LIMIT 1");


            if (!empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = 0;
            }
        }

        if ($tipx == 'top') {


            $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from tukar
		                 where PER ='$per'
						 and FLAG ='$this->FLAGZ' AND CBG = '$CBG'
		                 ORDER BY NO_BUKTI ASC  LIMIT 1");


            if (!empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = 0;
            }
        }


        if ($tipx == 'prev') {

            $buktix = $request->buktix;

            $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from tukar
		             where PER ='$per'
					 and FLAG ='$this->FLAGZ' AND CBG = '$CBG'
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

            $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from tukar
		             where PER ='$per'
					 and FLAG ='$this->FLAGZ' AND CBG = '$CBG'
                     and NO_BUKTI >
					 '$buktix' ORDER BY NO_BUKTI ASC LIMIT 1");

            if (!empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = $idx;
            }
        }

        if ($tipx == 'bottom') {

            $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from tukar
						where PER ='$per'
						and FLAG ='$this->FLAGZ' AND CBG = '$CBG'
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
            $tukar = Tukar::where('NO_ID', $idx)->first();
        } else {
            $tukar = new Tukar;
            $tukar->TGL = Carbon::now();
        }

        $no_bukti = $tukar->NO_BUKTI;
        $tukarDetail = DB::table('tukard')->where('NO_BUKTI', $no_bukti)->orderBy('REC')->get();

        $data = [
            'header'        => $tukar,
            'detail'        => $tukarDetail

        ];

        $sup = DB::SELECT("SELECT KODEC, CONCAT(NAMAC,'-',KOTA) AS NAMAC FROM cust
		                 ORDER BY NAMAC ASC");


        return view('otransaksi_tukar.edit', $data)->with(['sup' => $sup])
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

    public function update(Request $request, Tukar $tukar)
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

        $variablell = DB::select('call tukardel(?)', array($tukar['NO_BUKTI']));

        $CBG = Auth::user()->CBG;

        $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];


        $tukar->update(
            [
                'TGL'              => date('Y-m-d', strtotime($request['TGL'])),
                'NOTES'            => ($request['NOTES'] == null) ? "" : $request['NOTES'],
                'KODEC'            => ($request['KODEC'] == null) ? "" : $request['KODEC'],
                'NAMAC'            => ($request['NAMAC'] == null) ? "" : $request['NAMAC'],
                'ALAMAT'           => ($request['ALAMAT'] == null) ? "" : $request['ALAMAT'],
                'KOTA'             => ($request['KOTA'] == null) ? "" : $request['KOTA'],
                'TYPE'             => ($request['TYPE'] == null) ? "" : $request['TYPE'],
                'HARI'             => ($request['HARI'] == null) ? "" : $request['HARI'],
                'TOTAL_QTY'        => (float) str_replace(',', '', $request['TTOTAL_QTY']),
                'TOTAL'        => (float) str_replace(',', '', $request['TTOTAL']),
                'USRNM'            => Auth::user()->username,
                'TG_SMP'           => Carbon::now(),
                'updated_by'       => Auth::user()->username,
                'FLAG'             => $this->FLAGZ,
                'CBG'              => $CBG,
            ]
        );

        $no_buktix = $tukar->NO_BUKTI;

        // Update Detail
        $length = sizeof($request->input('REC'));
        $NO_ID  = $request->input('NO_ID');

        $REC    = $request->input('REC');

        $KD_BRG    = $request->input('KD_BRG');
        $NA_BRG    = $request->input('NA_BRG');
        $SATUAN    = $request->input('SATUAN');
        $HARGA    = $request->input('HARGA');
        $TOTAL    = $request->input('TOTAL');
        $QTY    = $request->input('QTY');
        $KET    = $request->input('KET');

        $query = DB::table('tukard')->where('NO_BUKTI', $request->NO_BUKTI)->whereNotIn('NO_ID',  $NO_ID)->delete();

        // Update / Insert
        for ($i = 0; $i < $length; $i++) {
            // Insert jika NO_ID baru
            if ($NO_ID[$i] == 'new') {
                $insert = TukarDetail::create(
                    [
                        'NO_BUKTI'   => $request->NO_BUKTI,
                        'REC'        => $REC[$i],
                        'PER'        => $periode,
                        'CBG'        => $CBG,
                        'FLAG'       => $this->FLAGZ,
                        'KD_BRG'     => ($KD_BRG[$i] == null) ? "" :  $KD_BRG[$i],
                        'NA_BRG'     => ($NA_BRG[$i] == null) ? "" : $NA_BRG[$i],
                        'SATUAN'     => ($SATUAN[$i] == null) ? "" : $SATUAN[$i],
                        'KET'          => ($KET[$i] == null) ? "" : $KET[$i],
                        'HARGA'      => (float) str_replace(',', '', $HARGA[$i]),
                        'TOTAL'      => (float) str_replace(',', '', $TOTAL[$i]),
                        'QTY'        => (float) str_replace(',', '', $QTY[$i])

                    ]
                );
            } else {
                // Update jika NO_ID sudah ada
                $upsert = TukarDetail::updateOrCreate(
                    [
                        'NO_BUKTI'  => $request->NO_BUKTI,
                        'NO_ID'     => (int) str_replace(',', '', $NO_ID[$i])
                    ],

                    [
                        'REC'        => $REC[$i],

                        'KD_BRG'     => ($KD_BRG[$i] == null) ? "" :  $KD_BRG[$i],
                        'NA_BRG'     => ($NA_BRG[$i] == null) ? "" : $NA_BRG[$i],
                        'SATUAN'     => ($SATUAN[$i] == null) ? "" : $SATUAN[$i],
                        'KET'          => ($KET[$i] == null) ? "" : $KET[$i],
                        'HARGA'      => (float) str_replace(',', '', $HARGA[$i]),
                        'TOTAL'      => (float) str_replace(',', '', $TOTAL[$i]),
                        'QTY'        => (float) str_replace(',', '', $QTY[$i]),
                        'FLAG'       => $this->FLAGZ,
                        'PER'        => $periode,
                        'CBG'        => $CBG,
                    ]
                );
            }
        }

        $tukar = Tukar::where('NO_BUKTI', $no_buktix)->first();

        $no_bukti = $tukar->NO_BUKTI;

        $variablell = DB::select('call tukarins(?)', array($tukar['NO_BUKTI']));

        DB::SELECT("UPDATE tukar, cust
                    SET tukar.NAMAC = cust.NAMAC, tukar.ALAMAT = cust.ALAMAT, tukar.KOTA = cust.KOTA, tukar.PKP=cust.PKP, tukar.HARI = cust.HARI  WHERE tukar.KODEC = cust.KODEC
                    AND tukar.NO_BUKTI='$no_buktix';");

        DB::SELECT("UPDATE tukar,  tukard
                    SET  tukard.ID =  tukar.NO_ID  WHERE  tukar.NO_BUKTI =  tukard.NO_BUKTI
                    AND  tukar.NO_BUKTI='$no_bukti';");

        // return redirect('/tukar/edit/?idx=' . $tukar->NO_ID . '&tipx=edit&flagz=' . $this->FLAGZ . '&judul=' . $this->judul . '');
        return redirect('/tukar?flagz=' . $FLAGZ)->with(['judul' => $judul, 'flagz' => $FLAGZ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    // ganti 22

    public function destroy(Request $request, Tukar $tukar)
    {

        $this->setFlag($request);
        $FLAGZ = $this->FLAGZ;
        $judul = $this->judul;

        $per = session()->get('periode')['bulan'] . '/' . session()->get('periode')['tahun'];
        $cekperid = DB::SELECT("SELECT POSTED from perid WHERE PERIO='$per'");
        if ($cekperid[0]->POSTED == 1) {
            return redirect()->route('tukar')
                ->with('status', 'Maaf Periode sudah ditutup!')
                ->with(['judul' => $this->judul, 'flagz' => $this->FLAGZ]);
        }

        $variablell = DB::select('call tukardel(?)', array($tukar['NO_BUKTI']));

        $deleteTukar = Tukar::find($tukar->NO_ID);

        $deleteTukar->delete();

        return redirect('/tukar?flagz=' . $FLAGZ)->with(['judul' => $judul, 'flagz' => $FLAGZ])->with('statusHapus', 'Data ' . $tukar->NO_BUKTI . ' berhasil dihapus');
    }

    public function cetak(Tukar $tukar)
    {
        $no_tukar = $tukar->NO_BUKTI;

        // $kd_brg = strval($request->KD_BRG);
        // $kd_brgx = strval($kd_brg);

        $file     = 'tukarc';

        $flagz1 = $tukar->FLAG;
        $judul = '';

        if ($flagz1 == 'TK') {
            $judul = 'Penukaran Barang';
        }

        // if ( $flagz1 =='RB')
        // {
        //         $judul ='Retur Pembelian';
        // }

        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

        $query = DB::SELECT("SELECT tukar.NO_BUKTI, tukar.TGL, tukar.KODEC, tukar.NAMAC, tukar.TOTAL_QTY, tukar.NOTES, tukar.ALAMAT, tukar.NO_JUAL,
                                    tukar.KOTA, tukard.KD_BRG, tukard.NA_BRG, tukard.SATUAN, tukard.QTY, tukard.KET, tukar.USRNM
                            FROM tukar, tukard
                            WHERE tukar.NO_BUKTI='$no_tukar' AND tukar.NO_BUKTI = tukard.NO_BUKTI
                            ;
		");

        DB::SELECT("UPDATE tukar SET POSTED = 1 WHERE NO_BUKTI='$no_tukar';");

        $data = [];

        foreach ($query as $key => $value) {
            array_push($data, array(
                'NO_BUKTI' => $query[$key]->NO_BUKTI,
                'TGL'      => $query[$key]->TGL,
                'KODEC'    => $query[$key]->KODEC,
                'NAMAC'    => $query[$key]->NAMAC,
                'ALAMAT'    => $query[$key]->ALAMAT,
                'KOTA'    => $query[$key]->KOTA,
                'NOTES'    => $query[$key]->NOTES,
                'KD_BRG'    => $query[$key]->KD_BRG,

                'NA_BRG'    => $query[$key]->NA_BRG,
                'SATUAN'    => $query[$key]->SATUAN,
                'QTY'    => $query[$key]->QTY,
                'NO_JUAL'    => $query[$key]->NO_JUAL,
                'JUDUL'    => $judul,
                'USRNM'    => $query[$key]->USRNM,
            ));
        }

        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I");
    }



    public function posting(Request $request) {}


    public function getDetailTukar()
    {

        $no_bukti = $_GET['no_bukti'];
        $result = DB::table('tukard')->where('NO_BUKTI', $no_bukti)->get();

        return response()->json($result);;
    }

    public function posting_tukar(Request $request)
    {
        if (! $request->isMethod('post')) {
            return response()->json(['error' => 'Method Not Allowed'], 405);
        }

        $data = $request->input('posted');

        if (! $data) {
            return response()->json(['error' => 'Tidak ada data yang dikirim'], 400);
        }

        foreach ($data as $id => $posted) {
            DB::table('tukar')->where('NO_ID', $id)->update(['POSTED' => $posted]);
        }

        return response()->json(['message' => 'Status berhasil diperbarui']);
    }
}