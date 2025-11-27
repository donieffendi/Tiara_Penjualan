<?php
namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
// ganti 1

use App\Models\OTransaksi\Stocka;
use App\Models\OTransaksi\StockaDetail;
use Auth;
use Carbon\Carbon;
use DataTables;
use DB;
use Illuminate\Http\Request;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";
use PHPJasperXML;

// ganti 2
class StockaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resbelinse
     */
    public $judul = '';
    public $FLAGZ = '';

    public function setFlag(Request $request)
    {
        $this->FLAGZ = $request->flagz;

        switch ($request->flagz) {
            case 'KB':
                $this->judul = "Koreksi Stock Bahan";
                $this->view  = 'otransaksi_stocka.index';
                break;
            case 'KZ':
                $this->judul = "Koreksi Stock";
                $this->view  = 'otransaksi_stocka.index';
                break;
            case 'RO':
                $this->judul = "Retur";
                $this->view  = 'otransaksi_stocka.index';
                break;
            case 'TG':
                $this->judul = "Terima Retur";
                $this->view  = 'otransaksi_stocka.index';
                break;
            case 'PT':
                $this->judul = "Posting Retur Outlet";
                $this->view  = 'otransaksi_stocka.index_posting';
                break;
            case 'PR':
                $this->judul = "Posting Terima Retur Outlet";
                $this->view  = 'otransaksi_stocka.index_posting_terima';
                break;
        }
    }

    public function index(Request $request)
    {
        $this->setFlag($request);
        return view($this->view)->with(['judul' => $this->judul, 'flagz' => $this->FLAGZ]);
    }

    public function browse(Request $request)
    {
        $golz = $request->GOL;

        $CBG = Auth::user()->CBG;

        $stocka = DB::SELECT("SELECT distinct PO.NO_BUKTI , PO.KODES, PO.NAMAS,
		                  PO.ALAMAT, PO.KOTA from stocka, stockad
                          WHERE PO.NO_BUKTI = POD.NO_BUKTI AND PO.GOL ='$golz'
                          AND po.CBG = '$CBG' AND POD.SISA > 0");
        return resstockanse()->json($stocka);
    }

    public function browseuang(Request $request)
    {
        $CBG = Auth::user()->CBG;

        $stocka = DB::SELECT("SELECT NO_BUKTI,TGL,  KODES, NAMAS, TOTAL,  BAYAR,
                                (TOTAL-BAYAR) AS SISA, ALAMAT, KOTA from stocka
		                    WHERE LNS <> 1 AND CBG = '$CBG' ORDER BY NO_BUKTI; ");

        return response()->json($stocka);
    }

    public function index_posting(Request $request)
    {

        return view('otransaksi_stocka.post');
    }

    //SHELVI

    public function browse_detail(Request $request)
    {
        $filterbukti = '';
        if ($request->NO_PO) {

            $filterbukti = " WHERE a.NO_BUKTI='" . $request->NO_PO . "' AND a.KD_BRG = b.KD_BRG ";
        }
        $stockad = DB::SELECT("SELECT a.REC, a.KD_BRG, a.NA_BRG, a.SATUAN , a.QTY, a.HARGA, a.KIRIM, a.SISA,
                                b.SATUAN AS SATUAN_PO, a.QTY AS QTY_PO, '1' AS X
                            from stockad a, bhn b
                            $filterbukti ORDER BY NO_BUKTI ");

        return response()->json($stockad);
    }

    public function browse_detail2(Request $request)
    {
        $filterbukti = '';
        if ($request->NO_PO) {

            $filterbukti = " WHERE NO_BUKTI='" . $request->NO_PO . "' AND a.KD_BRG = b.KD_BRG ";
        }
        $stockad = DB::SELECT("SELECT a.REC, a.KD_BRG, a.NA_BRG, a.SATUAN , a.QTY, a.HARGA, a.KIRIM, a.SISA,
                                b.SATUAN AS SATUAN_PO, a.QTY AS QTY_PO, '1' AS X
                            from stockad a, brg b
                            $filterbukti ORDER BY NO_BUKTI ");

        return response()->json($stockad);
    }

    public function browse_retur(Request $request)
    {
        $flagz = $request->FLAGZ;
        $retur = $request->RETUR;
        $CBG   = Auth::user()->CBG;

        $stocka = DB::SELECT("SELECT NO_BUKTI FROM stocka WHERE FLAG = '$flagz' AND CBG = '$CBG' ORDER BY NO_BUKTI ");

        return response()->json($stocka);
    }

    public function browse_dretur(Request $request)
    {
        $flagz = $request->FLAGZ;
        $retur = $request->RETUR;
        $CBG   = Auth::user()->CBG;

        $stockad = DB::SELECT("SELECT stockad.REC,stockad.KD_BRG,stockad.NA_BRG,stockad.SATUAN,stockad.QTYC,stockad.QTYR,stockad.QTY,stockad.KET FROM stocka, stockad
                            WHERE stocka.NO_BUKTI = stockad.NO_BUKTI AND stocka.FLAG = '$flagz' AND stocka.CBG = '$CBG' AND stocka.NO_BUKTI='$retur' ORDER BY stockad.REC ASC");

        return response()->json($stockad);
    }
    // ganti 4

    public function getStocka(Request $request)
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

        $stocka = DB::SELECT("SELECT * from stocka  WHERE PER='$periode' and FLAG ='$this->FLAGZ'
                            AND CBG = '$CBG' ORDER BY NO_BUKTI ");

        // ganti 6

        return Datatables::of($stocka)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                if (Auth::user()->divisi == "programmer") {
                    //CEK POSTED di index dan edit

                    $btnEdit   = ($row->POSTED == 1) ? ' onclick= "alert(\'Transaksi ' . $row->NO_BUKTI . ' sudah diposting!\')" href="#" ' : ' href="stocka/edit/?idx=' . $row->NO_ID . '&tipx=edit&flagz=' . $this->FLAGZ . '&judul=' . $this->judul . '"';
                    $btnDelete = ($row->POSTED == 1) ? ' onclick= "alert(\'Transaksi ' . $row->NO_BUKTI . ' sudah diposting!\')" href="#" ' : ' onclick="return confirm(&quot; Apakah anda yakin ingin hapus? &quot;)" href="stocka/delete/' . $row->NO_ID . '/?flagz=' . $this->FLAGZ . '" ';

                    $btnPrivilege =
                    '
                                <a class="dropdown-item" ' . $btnEdit . '>
                                <i class="fas fa-edit"></i>
                                    Edit
                                </a>
                                <a class="dropdown-item btn btn-danger" href="cetak/' . $row->NO_ID . '">
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
                return;
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
     * @return \Illuminate\Http\Resbelinse
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
        $judul = $this->judul;

        $CBG = Auth::user()->CBG;

        $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];

        $bulan = session()->get('periode')['bulan'];
        $tahun = substr(session()->get('periode')['tahun'], -2);

        // $query = DB::table('stocka')->select('NO_BUKTI')->where('PER', $periode)->where('FLAG', 'KB')->where('CBG', $CBG)
        //         ->orderByDesc('NO_BUKTI')->limit(1)->get();

        $query = DB::table('stocka')->select('NO_BUKTI')->where('PER', $periode)->where('FLAG', $FLAGZ)->where('CBG', $CBG)
            ->orderByDesc('NO_BUKTI')->limit(1)->get();

        if ($query != '[]') {
            $query    = substr($query[0]->NO_BUKTI, -4);
            $query    = str_pad($query + 1, 4, 0, STR_PAD_LEFT);
            $no_bukti = $FLAGZ . $CBG . $tahun . $bulan . '-' . $query;
        } else {
            $no_bukti = $FLAGZ . $CBG . $tahun . $bulan . '-0001';
        }

        $stocka = Stocka::create(
            [
                'NO_BUKTI'   => $no_bukti,
                'TGL'        => date('Y-m-d', strtotime($request['TGL'])),
                'PER'        => $periode,
                'FLAG'       => $FLAGZ,
                'NOTES'      => ($request['NOTES'] == null) ? "" : $request['NOTES'],
                'TOTAL_QTY'  => (float) str_replace(',', '', $request['TTOTAL_QTY']),
                'USRNM'      => Auth::user()->username,
                'TG_SMP'     => Carbon::now(),
                'created_by' => Auth::user()->username,
                'CBG'        => $CBG,
            ]
        );

        $REC    = $request->input('REC');
        $KD_BRG = $request->input('KD_BRG');
        $NA_BRG = $request->input('NA_BRG');
        $SATUAN = $request->input('SATUAN');
        $QTYC   = $request->input('QTYC');
        $QTYR   = $request->input('QTYR');
        $QTY    = $request->input('QTY');
        $KET    = $request->input('KET');

        // Check jika value detail ada/tidak
        if ($REC) {
            foreach ($REC as $key => $value) {
                // Declare new data di Model
                $detail = new StockaDetail;

                // Insert ke Database
                $detail->NO_BUKTI = $no_bukti;
                $detail->REC      = $REC[$key];
                $detail->PER      = $periode;
                $detail->FLAG     = $FLAGZ;
                $detail->CBG      = $CBG;
                $detail->KD_BRG   = ($KD_BRG[$key] == null) ? "" : $KD_BRG[$key];
                $detail->NA_BRG   = ($NA_BRG[$key] == null) ? "" : $NA_BRG[$key];
                $detail->SATUAN   = ($SATUAN[$key] == null) ? "" : $SATUAN[$key];
                $detail->QTYC     = (float) str_replace(',', '', $QTYC[$key]);
                $detail->QTYR     = (float) str_replace(',', '', $QTYR[$key]);
                $detail->QTY      = (float) str_replace(',', '', $QTY[$key]);
                $detail->KET      = ($KET[$key] == null) ? "" : $KET[$key];
                $detail->save();
            }
        }

        //$variablell = DB::select('call stockains(?)', [$no_bukti]);

        if ($FLAGZ == 'RO') {
            $variablell = DB::select('call rstockains(?)', [$no_bukti]);
        } else {
            $variablell = DB::select('call stockains(?)', [$no_bukti]);

        }
        $no_buktix = $no_bukti;

        $stocka = Stocka::where('NO_BUKTI', $no_buktix)->first();

        DB::SELECT("UPDATE stocka,  stockad
                            SET  stockad.ID =  stocka.NO_ID  WHERE  stocka.NO_BUKTI =  stockad.NO_BUKTI
							AND  stocka.NO_BUKTI='$no_buktix';");

        return redirect('/stocka/edit/?idx=' . $stocka->NO_ID . '&tipx=edit&flagz=' . $this->FLAGZ . '&judul=' . $this->judul . '');

    }

    public function edit(Request $request, Stocka $stocka)
    {

        $per = session()->get('periode')['bulan'] . '/' . session()->get('periode')['tahun'];

        // $cekperid = DB::SELECT("SELECT POSTED from perid WHERE PERIO='$per'");
        // if ($cekperid[0]->POSTED==1)
        // {
        //     return redirect('/stocka')
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

            $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from stocka
		                 where PER ='$per' and FLAG ='$this->FLAGZ'
						 and NO_BUKTI = '$buktix' AND CBG = '$CBG'
		                 ORDER BY NO_BUKTI ASC  LIMIT 1");

            if (! empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = 0;
            }

        }

        if ($tipx == 'top') {

            $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from stocka
		                 where PER ='$per'
						 and FLAG ='$this->FLAGZ'
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

            $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from stocka
		             where PER ='$per'
					 and FLAG ='$this->FLAGZ'
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

            $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from stocka
		             where PER ='$per'
					 and FLAG ='$this->FLAGZ'
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

            $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from stocka
						where PER ='$per'
						and FLAG ='$this->FLAGZ'
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
            $stocka = Stocka::where('NO_ID', $idx)->first();
        } else {
            $stocka      = new Stocka;
            $stocka->TGL = Carbon::now();

        }

        $no_bukti     = $stocka->NO_BUKTI;
        $stockaDetail = DB::table('stockad')->where('NO_BUKTI', $no_bukti)->orderBy('REC')->get();

        $data = [
            'header' => $stocka,
            'detail' => $stockaDetail,

        ];

        $sup = DB::SELECT("SELECT KODES, CONCAT(NAMAS,'-',KOTA) AS NAMAS FROM SUP
		                 ORDER BY NAMAS ASC");

        return view('otransaksi_stocka.edit', $data)->with(['sup' => $sup])
            ->with(['tipx' => $tipx, 'idx' => $idx, 'flagz' => $this->FLAGZ, 'judul' => $this->judul]);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Resbelinse
     */

    // ganti 18

    public function update(Request $request, Stocka $stocka)
    {

        $this->validate(
            $request,
            [

                'TGL' => 'required',
            ]
        );

        $this->setFlag($request);
        $FLAGZ = $this->FLAGZ;
        $judul = $this->judul;

        // $variablell = DB::select('call stockadel(?)', [$stocka['NO_BUKTI']]);
        if ($FLAGZ == 'RO') {
            $variablell = DB::select('call rstockadel(?)', [$no_bukti]);
        } else {
            $variablell = DB::select('call stockadel(?)', [$no_bukti]);

        }

        $CBG = Auth::user()->CBG;

        $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];

        $stocka->update(
            [
                'TGL'        => date('Y-m-d', strtotime($request['TGL'])),
                'NOTES'      => ($request['NOTES'] == null) ? "" : $request['NOTES'],
                'TOTAL_QTY'  => (float) str_replace(',', '', $request['TTOTAL_QTY']),
                'USRNM'      => Auth::user()->username,
                'TG_SMP'     => Carbon::now(),
                'updated_by' => Auth::user()->username,
                'FLAG'       => 'KB',
                'CBG'        => $CBG,
            ]
        );

        $no_buktix = $stocka->NO_BUKTI;

        // Update Detail
        $length = sizeof($request->input('REC'));
        $NO_ID  = $request->input('NO_ID');

        $REC = $request->input('REC');

        $KD_BRG = $request->input('KD_BRG');
        $NA_BRG = $request->input('NA_BRG');
        $SATUAN = $request->input('SATUAN');
        $QTYC   = $request->input('QTYC');
        $QTYR   = $request->input('QTYR');
        $QTY    = $request->input('QTY');
        $KET    = $request->input('KET');

        $query = DB::table('stockad')->where('NO_BUKTI', $request->NO_BUKTI)->whereNotIn('NO_ID', $NO_ID)->delete();

        // Update / Insert
        for ($i = 0; $i < $length; $i++) {
            // Insert jika NO_ID baru
            if ($NO_ID[$i] == 'new') {
                $insert = StockaDetail::create(
                    [
                        'NO_BUKTI' => $request->NO_BUKTI,
                        'REC'      => $REC[$i],
                        'PER'      => $periode,
                        'FLAG'     => $this->FLAGZ,
                        'CBG'      => $CBG,
                        'KD_BRG'   => ($KD_BRG[$i] == null) ? "" : $KD_BRG[$i],
                        'NA_BRG'   => ($NA_BRG[$i] == null) ? "" : $NA_BRG[$i],
                        'SATUAN'   => ($SATUAN[$i] == null) ? "" : $SATUAN[$i],
                        'KET'      => ($KET[$i] == null) ? "" : $KET[$i],
                        'QTYC'     => (float) str_replace(',', '', $QTYC[$i]),
                        'QTYR'     => (float) str_replace(',', '', $QTYR[$i]),
                        'QTY'      => (float) str_replace(',', '', $QTY[$i]),

                    ]
                );
            } else {
                // Update jika NO_ID sudah ada
                $upsert = StockaDetail::updateOrCreate(
                    [
                        'NO_BUKTI' => $request->NO_BUKTI,
                        'NO_ID'    => (int) str_replace(',', '', $NO_ID[$i]),
                    ],

                    [
                        'REC'    => $REC[$i],

                        'KD_BRG' => ($KD_BRG[$i] == null) ? "" : $KD_BRG[$i],
                        'NA_BRG' => ($NA_BRG[$i] == null) ? "" : $NA_BRG[$i],
                        'SATUAN' => ($SATUAN[$i] == null) ? "" : $SATUAN[$i],
                        'KET'    => ($KET[$i] == null) ? "" : $KET[$i],
                        'QTYC'   => (float) str_replace(',', '', $QTYC[$i]),
                        'QTYR'   => (float) str_replace(',', '', $QTYR[$i]),
                        'QTY'    => (float) str_replace(',', '', $QTY[$i]),
                        'FLAG'   => $this->FLAGZ,
                        'PER'    => $periode,
                        'CBG'    => $CBG,
                    ]
                );
            }
        }

        $stocka = Stocka::where('NO_BUKTI', $no_buktix)->first();

        $no_bukti = $stocka->NO_BUKTI;

        // $variablell = DB::select('call stockains(?)', [$stocka['NO_BUKTI']]);
        if ($$FLAGZ == 'RO') {
            $variablell = DB::select('call rstockains(?)', [$no_bukti]);
        } else {
            $variablell = DB::select('call stockains(?)', [$no_bukti]);

        }

        DB::SELECT("UPDATE stocka,  stockad
                    SET  stockad.ID =  stocka.NO_ID  WHERE  stocka.NO_BUKTI =  stockad.NO_BUKTI
                    AND  stocka.NO_BUKTI='$no_bukti';");

        return redirect('/stocka/edit/?idx=' . $stocka->NO_ID . '&tipx=edit&flagz=' . $this->FLAGZ . '&judul=' . $this->judul . '');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Resbelinse
     */

    // ganti 22

    public function destroy(Request $request, Stocka $stocka)
    {

        $this->setFlag($request);
        $FLAGZ = $this->FLAGZ;
        $judul = $this->judul;

        $per      = session()->get('periode')['bulan'] . '/' . session()->get('periode')['tahun'];
        $cekperid = DB::SELECT("SELECT POSTED from perid WHERE PERIO='$per'");
        if ($cekperid[0]->POSTED == 1) {
            return redirect()->route('stocka')
                ->with('status', 'Maaf Periode sudah ditutup!')
                ->with(['judul' => $this->judul, 'flagz' => $this->FLAGZ]);
        }

        // $variablell = DB::select('call stockadel(?)', [$stocka['NO_BUKTI']]);
        if ($FLAGZ == 'RO') {
            $variablell = DB::select('call rstockadel(?)', [$no_bukti]);
        } else {
            $variablell = DB::select('call stockadel(?)', [$no_bukti]);

        }

        $deleteStocka = Stocka::find($stocka->NO_ID);

        $deleteStocka->delete();

        return redirect('/stocka?flagz=' . $FLAGZ)->with(['judul' => $judul, 'flagz' => $FLAGZ])->with('statusHapus', 'Data ' . $stocka->NO_BUKTI . ' berhasil dihapus');

    }

    public function cetak(Stocka $stocka)
    {
        $no_stocka = $stocka->NO_BUKTI;

        $file         = 'stockac';
        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

        $query = DB::SELECT("
			SELECT NO_BUKTI,  TGL, KODES, NAMAS, TOTAL_QTY, NOTES, TOTAL, ALAMAT, KOTA
			FROM stocka
			WHERE stocka.NO_BUKTI='$no_stocka'
			ORDER BY NO_BUKTI;
		");

        $xno_stocka1 = $query[0]->NO_BUKTI;
        $xtgl1       = $query[0]->TGL;
        $xkodes1     = $query[0]->KODES;
        $xnamas1     = $query[0]->NAMAS;
        $xtotal1     = $query[0]->TOTAL_QTY;
        $xnotes1     = $query[0]->NOTES;
        $xharga1     = $query[0]->TOTAL;
        $xalamat1    = $query[0]->ALAMAT;
        $xkota1      = $query[0]->KOTA;

        $PHPJasperXML->arrayParameter = ["HARGA1" => (float) $xharga1, "TOTAL1" => (float) $xtotal1, "NO_PO1"  => (string) $xno_stocka1,
            "TGL1"                                    => (string) $xtgl1, "KODES1"  => (string) $xkodes1, "NAMAS1" => (string) $xnamas1, "NOTES1" => (string) $xnotes1, "ALAMAT1" => (string) $xalamat1, "KOTA1" => (string) $xkota1];
        $PHPJasperXML->arraysqltable = [];

        $query2 = DB::SELECT("
			SELECT NO_BUKTI, TGL, KODES, NAMAS, if(ALAMAT='','NOT-FOUND.png',ALAMAT) as ALAMAT, NO_PO,  IF ( FLAG='BL' , 'A','B' ) AS FLAG, AJU, BL, EMKL, KD_BRG, NA_BRG, KG, RPHARGA AS HARGA, RPTOTAL AS TOTAL, 0 AS BAYAR,  NOTES
			FROM beli
			WHERE beli.NO_PO='$no_stocka'  UNION ALL
			SELECT NO_BUKTI, TGL, KODES, NAMAS, if(ALAMAT='','NOT-FOUND.png',ALAMAT) as ALAMAT,  NO_PO,  'C' AS FLAG, '' AS AJU, '' AS BL, '' AS EMKL,  '' AS KD_BRG, '' AS NA_BRG, 0 AS KG,
			0 AS HARGA, 0 AS TOTAL, BAYAR, NOTES
			FROM hut
			WHERE hut.NO_PO='$no_stocka'
			ORDER BY TGL, FLAG, NO_BUKTI;
		");

        $data = [];

        foreach ($query2 as $key => $value) {
            array_push($data, [
                'NO_BUKTI' => $query2[$key]->NO_BUKTI,
                'TGL'      => $query2[$key]->TGL,
                'KODES'    => $query2[$key]->KODES,
                'NAMAS'    => $query2[$key]->NAMAS,
                'ALAMAT'   => $query2[$key]->ALAMAT,
                'AJU'      => $query2[$key]->AJU,
                'BL'       => $query2[$key]->BL,
                'EMKL'     => $query2[$key]->EMKL,
                'KG'       => $query2[$key]->KG,
                'HARGA'    => $query2[$key]->HARGA,
                'TOTAL'    => $query2[$key]->TOTAL,
                'BAYAR'    => $query2[$key]->BAYAR,
                'NOTES'    => $query2[$key]->NOTES,
            ]);
        }

        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I");

    }

    public function posting(Request $request)
    {

    }

    public function posting_retur_outlet(Request $request)
    {
        if (! $request->isMethod('post')) {
            return response()->json(['error' => 'Method Not Allowed'], 405);
        }

        $data = $request->input('posted');

        if (! $data) {
            return response()->json(['error' => 'Tidak ada data yang dikirim'], 400);
        }

        foreach ($data as $id => $posted) {
            DB::table('stocka')->where('NO_BUKTI', $id)->update(['POSTED' => $posted]);
        }

        return response()->json(['message' => 'Status berhasil diperbarui']);
    }

    public function posting_terima_retur_outlet(Request $request)
    {
        if (! $request->isMethod('post')) {
            return response()->json(['error' => 'Method Not Allowed'], 405);
        }

        $data = $request->input('posted');

        if (! $data) {
            return response()->json(['error' => 'Tidak ada data yang dikirim'], 400);
        }

        foreach ($data as $id => $posted) {
            DB::table('stocka')->where('NO_BUKTI', $id)->update(['POSTED' => $posted]);
        }

        return response()->json(['message' => 'Status berhasil diperbarui']);
    }

}
