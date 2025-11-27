<?php
namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
// ganti 1

use App\Models\OTransaksi\Lbhijau;
use App\Models\OTransaksi\LbhijauDetail;
use App\Models\OTransaksi\LbhijauDTerkait;
use Auth;
use Carbon\Carbon;
use DataTables;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use PHPJasperXML;
use XBase\Enum\FieldType;
use XBase\Enum\TableType;
use XBase\Header\Column;
use XBase\Header\HeaderFactory;
use XBase\TableCreator;
use XBase\TableEditor;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

// ganti 2
class LbhijauController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resbelinse
     */
    public $judul = '';
    public $TYPEZ = '';

    public function setFlag(Request $request)
    {
        if ($request->typez == 'H') {
            $this->judul = "Promo Hadiah";
        }

        $this->TYPEZ = $request->typez;

    }

    public function index(Request $request)
    {

        $this->setFlag($request);
        // ganti 3
        return view('otransaksi_lbhijau.index')->with(['judul' => $this->judul, 'typez' => $this->TYPEZ]);

    }

    public function browse(Request $request)
    {

        $CBG = Auth::user()->CBG;

        $lbhijau = DB::SELECT("SELECT distinct lbhijau.NO_BUKTI , lbhijau.KODES, lbhijau.NAMAS,
		                  lbhijau.ALAMAT, lbhijau.KOTA, lbhijau.PKP, lbhijau.GUDANG, lbhijau.JTEMlbhijau, lbhijau.NOTES from lbhijau, lbhijaud
                          WHERE lbhijau.NO_BUKTI = lbhijaud.NO_BUKTI
                          AND lbhijau.CBG = '$CBG' AND lbhijaud.SISA > 0 AND POSTED = 1
                          GROUP BY NO_BUKTI ");
        return response()->json($lbhijau);
    }

    public function browseuang(Request $request)
    {
        $CBG = Auth::user()->CBG;

        $lbhijau = DB::SELECT("SELECT NO_BUKTI,TGL,  KODES, NAMAS from lbhijau
		                WHERE LNS <> 1 AND CBG = '$CBG' ORDER BY NO_BUKTI; ");

        return response()->json($lbhijau);
    }

    public function index_posting(Request $request)
    {

        return view('otransaksi_lbhijau.post');
    }

    public function browse_pod(Request $request)
    {
        $golx = $request->GOL;

        if ($golx == 'B') {

            $lbhijaud = DB::SELECT("SELECT a.REC, a.KD_BHN, a.NA_BHN, a.KD_BRG, a.NA_BRG, a.SATUAN , a.QTY, a.HARGA, a.KIRIM, a.SISA,
                                    b.SATUAN AS SATUAN_PO, a.QTY AS QTY_PO, b.KALI AS KALI, a.PPN, a.DPP, a.DISK
                                from pod a, bhn b
                                where a.NO_BUKTI='" . $request->nobukti . "' AND a.KD_BHN = b.KD_BHN");

        } else {

            $lbhijaud = DB::SELECT("SELECT a.REC, a.KD_BRG, a.NA_BRG, a.KD_BRG, a.NA_BRG, a.SATUAN , a.QTY, a.HARGA, a.KIRIM, a.SISA,
                                b.SATUAN AS SATUAN_PO, a.QTY AS QTY_PO, b.KALI AS KALI, a.PPN, a.DPP, a.DISK
                            from pod a, brg b
                            where a.NO_BUKTI='" . $request->nobukti . "' AND a.KD_BRG = b.KD_BRG");

        }

        return response()->json($pod);
    }

    public function browse_detail(Request $request)
    {
        $filterbukti = '';
        if ($request->NO_PO) {

            $filterbukti = " WHERE a.NO_BUKTI='" . $request->NO_PO . "' AND a.KD_BHN = b.KD_BHN ";
        }
        $lbhijaud = DB::SELECT("SELECT a.REC, a.KD_BHN, a.NA_BHN, a.SATUAN , a.QTY, a.HARGA, a.KIRIM, a.SISA,
                                b.SATUAN AS SATUAN_PO, a.QTY AS QTY_PO, b.KALI AS KALI
                            from pod a, bhn b
                            $filterbukti ORDER BY NO_BUKTI ");

        return response()->json($lbhijaud);
    }

    public function browse_detail2(Request $request)
    {
        $filterbukti = '';
        if ($request->NO_PO) {

            $filterbukti = " WHERE NO_BUKTI='" . $request->NO_PO . "' AND a.KD_BRG = b.KD_BRG ";
        }
        $lbhijaud = DB::SELECT("SELECT a.REC, a.KD_BRG, a.NA_BRG, a.SATUAN , a.QTY, a.HARGA, a.KIRIM, a.SISA,
                                b.SATUAN AS SATUAN_PO, a.QTY AS QTY_PO, b.KALI AS KALI
                            from lbhijaud a, brg b
                            $filterbukti ORDER BY NO_BUKTI ");

        return response()->json($lbhijaud);
    }
    // ganti 4

    public function getLbhijau(Request $request)
    {
        // ganti 5

        if ($request->session()->has('periode')) {
            $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
        } else {
            $periode = '';
        }

        $this->setFlag($request);
        $TYPEZ = $this->TYPEZ;
        $judul = $this->judul;

        $CBG = Auth::user()->CBG;
        // dd($periode, $TYPEZ);
        $lbhijau = DB::SELECT("SELECT *
                                from lbhijau
                                -- where PER='$periode' AND TYPE='$TYPEZ'
                                -- AND CBG = '$CBG'
                                ORDER BY NO_BUKTI ");

        // ganti 6

        return Datatables::of($lbhijau)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                if (Auth::user()->divisi == "programmer") {
                    //CEK POSTED di index dan edit

                    // url untuk delete di index
                    $url = "'" . url("lbhijau/delete/" . $row->NO_ID . "/?typez=" . $row->TYPE) . "'";
                    // batas

                    $btnEdit   = ' href="lbhijau/edit/?idx=' . $row->NO_ID . '&tipx=edit&typez=' . $row->TYPE . '&judul=' . $this->judul . '"';
                    $btnDelete = ' onclick="deleteRow(' . $url . ')"';

                    $btnPrivilege =
                    '
                                <a class="dropdown-item" ' . $btnEdit . '>
                                <i class="fas fa-edit"></i>
                                    Edit
                                </a>
                                <a class="dropdown-item btn btn-danger" href="lbhijau/cetak/' . $row->NO_ID . '">
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

        // ->addColumn('cek', function ($row) {
        //     return
        //         '
        //         <input type="checkbox" name="cek[]" class="form-control cek" ' . (($row->POSTED == 1) ? "checked" : "") . '  value="' . $row->NO_ID . '" ' . (($row->POSTED == 2) ? "disabled" : "") . '></input>
        //         ';

        // })

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
                //             'NO_PO'       => 'required',
                'TGL' => 'required',
            ]
        );

        //////     nomer otomatis
        $this->setFlag($request);
        $TYPEZ = $this->TYPEZ;
        $judul = $this->judul;

        $CBG = Auth::user()->CBG;

        $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];

        $bulan = session()->get('periode')['bulan'];
        $tahun = substr(session()->get('periode')['tahun'], -2);

        $query = DB::table('lbhijau')->select('NO_BUKTI')->where('PER', $periode)->where('CBG', $CBG)
            ->orderByDesc('NO_BUKTI')->limit(1)->get();

        if ($query != '[]') {
            $query    = substr($query[0]->NO_BUKTI, -4);
            $query    = str_pad($query + 1, 4, 0, STR_PAD_LEFT);
            $no_bukti = 'LH' . $tahun . $bulan . '-' . $query;
        } else {
            $no_bukti = 'LH' . $tahun . $bulan . '-0001';
        }

        $no_bukti2 = '';
        if ($request['TYPE'] == 'V') {
            $KD_BRG2 = $request->input('KD_BRG2');
            // Ambil no_bukti2 terakhir dari database
            // $lastRecord = Lbhijau::orderBy('NO_BUKTI2', 'desc')->first();

            // if (! $lastRecord) {
            //     $no_bukti2 = 'a0'; // Jika belum ada data, mulai dari 'a0'
            // } else {
            //     $lastNo = $lastRecord->NO_BUKTI2;
            //     preg_match('/([a-zA-Z]+)(\d+)/', $lastNo, $matches);

            //     if ($matches) {
            //         $charPart = $matches[1];       // Bagian huruf (a, b, c, ...)
            //         $numPart  = (int) $matches[2]; // Bagian angka (0, 1, 2, ...)

            //         if ($numPart < 9) {
            //             $numPart++; // Jika masih di bawah 9, naikkan angka
            //         } else {
            //             $numPart = 0; // Reset angka ke 0
            //             $charPart++;  // Naikkan huruf (misalnya dari 'a' ke 'b')
            //         }

            //         $no_bukti2 = $charPart . $numPart;
            //     } else {
            //         $no_bukti2 = 'a0'; // Fallback jika tidak terdeteksi
            //     }
            // }

            $no_bukti2 = $KD_BRG2[0];
        }

        $lbhijau = Lbhijau::create(
            [
                'NO_BUKTI'    => $no_bukti,
                'NO_BUKTI2'   => ($no_bukti2 == null) ? "" : $no_bukti2,
                'TGZ'         => (float) str_replace(',', '', $request['TGZ']),
                'SOP'         => (float) str_replace(',', '', $request['SOP']),
                'TMM'         => (float) str_replace(',', '', $request['TMM']),
                'KD_PRM'      => $no_bukti,
                'TYPE'        => ($request['TYPE'] == null) ? "" : $request['TYPE'],
                // 'KD_BRG'           => ($request['KD_BRG'] == null) ? "" : $request['KD_BRG'],
                'KONDISI'     => ($request['KONDISI'] == null) ? "" : $request['KONDISI'],
                'NKARTU'      => ($request['NKARTU'] == null) ? "" : $request['NKARTU'],
                'KODES'       => ($request['KODES'] == null) ? "" : $request['KODES'],
                'NAMAS'       => ($request['NAMAS'] == null) ? "" : $request['NAMAS'],
                'JNS'         => ($request['JNS'] == null) ? "" : $request['JNS'],
                'TGL'         => date('Y-m-d', strtotime($request['TGL'])),
                'QTY_BELI'    => (float) str_replace(',', '', $request['QTY_BELI']),
                'RP_BELI'     => (float) str_replace(',', '', $request['RP_BELI']),
                'RP_BELI_MAX' => (float) str_replace(',', '', $request['RP_BELI_MAX']),
                'KELIPATAN'   => (float) str_replace(',', '', $request['KELIPATAN']),
                'DAPAT'       => (float) str_replace(',', '', $request['DAPAT']),
                'TG_MULAI'    => date('Y-m-d', strtotime($request['TG_MULAI'])),
                'TG_AKHIR'    => date('Y-m-d', strtotime($request['TG_AKHIR'])),
                'JM_MULAI'    => date('H:i:s', strtotime($request['JM_MULAI'])),
                'JM_AKHIR'    => date('H:i:s', strtotime($request['JM_AKHIR'])),
                'PER'         => $periode,
                'TYPE'        => ($request['TYPE'] == null) ? "" : $request['TYPE'],
                'CBG'         => $CBG,
                'KET'         => ($request['KET'] == null) ? "" : $request['KET'],
                'BRG'         => ($request['BRG'] == null) ? "" : $request['BRG'],
                'USRNM'       => Auth::user()->username,
                'TG_SMP'      => Carbon::now(),

            ]
        );
        // ddd($request['KD_BRG']);

        $REC    = $request->input('REC');
        $KD_BRG = $request->input('KD_BRG');
        $NA_BRG = $request->input('NA_BRG');
        $QTY    = $request->input('QTY');

        // Check jika value detail ada/tidak
        if ($REC) {
            foreach ($REC as $key => $value) {
                // Declare new data di Model
                $detail = new LbhijauDetail;

                // Insert ke Database
                $detail->NO_BUKTI  = $no_bukti;
                $detail->NO_BUKTI2 = ($no_bukti2 == null) ? "" : $no_bukti2;
                $detail->REC       = $REC[$key];
                $detail->PER       = $periode;
                // $detail->TYPE        = $TYPEZ;
                $detail->KD_BRG = ($KD_BRG[$key] == null) ? "" : $KD_BRG[$key];
                $detail->NA_BRG = ($NA_BRG[$key] == null) ? "" : $NA_BRG[$key];
                $detail->QTY    = (float) str_replace(',', '', $QTY[$key]);
                $detail->save();
            }
        }

        $no_buktix = $no_bukti;
        
        $lbhijau = Lbhijau::where('NO_BUKTI', $no_buktix)->first();
        
        // masukkan dbf
        if ($request['TYPE'] == 'V') {
            $filePath1ijo = '\\\\192.168.0.2\\emailpbl\\barang_hadiah\\ijo.dbf';
            $filePath2ijo = '\\\\192.168.0.2\\emailpbl\\barang_hadiah\\'.$no_bukti2.'_ijo.dbf';
            $filePath3ijo = '\\\\192.168.0.2\\emailpbl\\barang_hadiah\\'.$no_bukti2.'.ijo'; // File akhir dengan ekstensi .ijb
            @File::copy($filePath1ijo, $filePath2ijo);


            $dataijo = DB::table('lbhijau')
                ->join('lbhijaud', 'lbhijau.NO_BUKTI', '=', 'lbhijaud.NO_BUKTI')
                ->select(
                    'lbhijau.NO_BUKTI2 AS NO_BUKTI',
                    DB::raw('SUM(lbhijaud.QTY) AS QTY_BELI'),
                    'lbhijau.DAPAT',
                    'lbhijaud.NA_BRG',
                    'lbhijau.KELIPATAN',
                    'lbhijau.KET',
                    DB::raw("DATE_FORMAT(lbhijau.tgl, '%Y-%m-%d') AS TGL"),
                    DB::raw("DATE_FORMAT(lbhijau.TG_MULAI, '%Y-%m-%d') AS TG_MULAI"),
                    DB::raw("DATE_FORMAT(lbhijau.TG_AKHIR, '%Y-%m-%d') AS TG_AKHIR")
                )
                ->where('lbhijau.NO_BUKTI', $no_buktix)
                ->get();
                

            foreach ($dataijo as $row1) {
                // IJO
                // composer require hisamu/php-xbase
                $downloadsPath = '\\\\192.168.0.2\\emailpbl\\barang_hadiah\\'.$no_bukti2.'_ijo.dbf';
                $table         = new TableEditor($downloadsPath);
                // Tambahkan data baru
                $record = $table->appendRecord()
                    ->set('kode', $row1->NO_BUKTI)
                    ->set('qty_beli', $row1->QTY_BELI)
                    ->set('dapat', $row1->QTY_BELI)
                    ->set('hadiah', $row1->NA_BRG)
                    ->set('produk', $row1->KET)
                    ->set('kelipatan', $row1->KELIPATAN == 1 ? 'Y' : 'T')
                    ->set('tgl', new \DateTime($row1->TGL))
                    ->set('tg_mulai', new \DateTime($row1->TG_MULAI))
                    ->set('tg_akhir', new \DateTime($row1->TG_AKHIR))
                ;

                $table->writeRecord($record);
                // Simpan perubahan
                $table->save();
                $table->close();
            }

            // Rename file ke ekstensi baru
            @File::move($filePath2ijo, $filePath3ijo);
        }

        DB::SELECT("UPDATE lbhijau, SUP
                    SET lbhijau.NAMAS = SUP.NAMAS WHERE lbhijau.KODES = SUP.KODES
                    AND lbhijau.NO_BUKTI='$no_buktix';");

        DB::SELECT("UPDATE lbhijau,  lbhijaud
                            SET  lbhijaud.ID =  lbhijau.NO_ID  WHERE  lbhijau.NO_BUKTI =  lbhijaud.NO_BUKTI
							AND  lbhijau.NO_BUKTI='$no_buktix';");

        // API
        $header = DB::SELECT("SELECT maxh,no_bukti,tgl,qty_beli,rp_beli,kelipatan,kondisi,jns,type,
                            kodes,namas,tg_mulai,tg_akhir,jm_mulai,jm_akhir,ket,usrnm,per,nkartu,
                            tgz,tmm,sop,brg FROM `lbhijau` WHERE NO_BUKTI='" . $no_buktix . "'");
        $detail = DB::SELECT("SELECT no_bukti,id,rec,kd_brg AS kd_brgh,na_brg AS na_brgh,qty FROM `lbhijaud` WHERE NO_BUKTI='" . $no_buktix . "'");

        $data = [
            "status" => "new",
            "type"   => "PROMO", // Ambil dari request
            "data"   => [(object) [
                "header" => $header,
                "detail" => $detail,
            ],
            ],
        ];

        $response = Http::asJson()->withHeaders([
            'Content-Type' => 'application/json',
        ])->post('http://192.168.0.2/admin-apf-app/public/api/sinkron_hadiah_hijau', $data);

        return redirect('/lbhijau/edit/?idx=' . $lbhijau->NO_ID . '&tipx=edit&typez=' . $this->TYPEZ . '&judul=' . $this->judul . '');

    }

    public function edit(Request $request, Lbhijau $lbhijau)
    {

        $per = session()->get('periode')['bulan'] . '/' . session()->get('periode')['tahun'];

        // $cekperid = DB::SELECT("SELECT POSTED from perid WHERE PERIO='$per'");
        // if ($cekperid[0]->POSTED==1)
        // {
        //     return redirect('/po')
        // 	       ->with('status', 'Maaf Periode sudah ditutup!')
        //            ->with(['judul' => $judul, 'typez' => $TYPEZ]);
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

            $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from lbhijau
		                 where PER ='$per' and TYPE ='$this->TYPEZ'
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

            $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from lbhijau
		                 where PER ='$per'
						 and TYPE ='$this->TYPEZ'
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

            $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from lbhijau
		             where PER ='$per'
					 and TYPE ='$this->TYPEZ'
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

            $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from lbhijau
		             where PER ='$per'
					 and TYPE ='$this->TYPEZ'
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

            $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from lbhijau
						where PER ='$per'
						and TYPE ='$this->TYPEZ'
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
            $lbhijau = Lbhijau::where('NO_ID', $idx)->first();
        } else {
            $lbhijau           = new Lbhijau;
            $lbhijau->TGL      = Carbon::now();
            $lbhijau->TG_MULAI = Carbon::now();
            $lbhijau->TG_AKHIR = Carbon::now();

        }

        $no_bukti       = $lbhijau->NO_BUKTI;
        $lbhijauDetail  = DB::table('lbhijaud')->where('NO_BUKTI', $no_bukti)->orderBy('REC')->get();
        $lbhijauD_Terkait = DB::table('lbhijaud_terkait')->where('NO_BUKTI', $no_bukti)->orderBy('NO_ID')->get();

        $data = [
            'header'  => $lbhijau,
            'detail'  => $lbhijauDetail,
            'detail2' => $lbhijauD_Terkait,

        ];

        $sup = DB::SELECT("SELECT KODES, CONCAT(NAMAS,'-',KOTA) AS NAMAS FROM SUP
		                 ORDER BY NAMAS ASC");

        return view('otransaksi_lbhijau.edit', $data)->with(['sup' => $sup])
            ->with(['tipx' => $tipx, 'idx' => $idx, 'typez' => $this->TYPEZ, 'judul' => $this->judul]);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Resbelinse
     */

    // ganti 18

    public function update(Request $request, Lbhijau $lbhijau)
    {

        $this->validate(
            $request,
            [

                'TGL' => 'required',
            ]
        );

        $this->setFlag($request);
        $TYPEZ = $this->TYPEZ;
        $judul = $this->judul;

        $CBG = Auth::user()->CBG;

        $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];

        $lbhijau->update(
            [
                'TGZ'         => (float) str_replace(',', '', $request['TGZ']),
                'SOP'         => (float) str_replace(',', '', $request['SOP']),
                'TMM'         => (float) str_replace(',', '', $request['TMM']),
                'TYPE'        => ($request['TYPE'] == null) ? "" : $request['TYPE'],
                'KD_BRG'      => ($request['KD_BRG'] == null) ? "" : $request['KD_BRG'],
                'KONDISI'     => ($request['KONDISI'] == null) ? "" : $request['KONDISI'],
                'KODES'       => ($request['KODES'] == null) ? "" : $request['KODES'],
                'NAMAS'       => ($request['NAMAS'] == null) ? "" : $request['NAMAS'],
                'JNS'         => ($request['JNS'] == null) ? "" : $request['JNS'],
                'KONDISI'     => ($request['KONDISI'] == null) ? "" : $request['KONDISI'],
                'NKARTU'      => ($request['NKARTU'] == null) ? "" : $request['NKARTU'],
                'TGL'         => date('Y-m-d', strtotime($request['TGL'])),
                'QTY_BELI'    => (float) str_replace(',', '', $request['QTY_BELI']),
                'RP_BELI'     => (float) str_replace(',', '', $request['RP_BELI']),
                'RP_BELI_MAX' => (float) str_replace(',', '', $request['RP_BELI_MAX']),
                'KELIPATAN'   => (float) str_replace(',', '', $request['KELIPATAN']),
                'DAPAT'       => (float) str_replace(',', '', $request['DAPAT']),
                'TG_MULAI'    => date('Y-m-d', strtotime($request['TG_MULAI'])),
                'TG_AKHIR'    => date('Y-m-d', strtotime($request['TG_AKHIR'])),
                'JM_MULAI'    => date('H:i:s', strtotime($request['JM_MULAI'])),
                'JM_AKHIR'    => date('H:i:s', strtotime($request['JM_AKHIR'])),
                'PER'         => $periode,
                // 'TYPE'             => 'H',
                'CBG'         => $CBG,
                'KET'         => ($request['KET'] == null) ? "" : $request['KET'],
                // 'BRG'              => ($request['BRG'] == null) ? "" : $request['BRG'],
                'USRNM'       => Auth::user()->username,
                'TG_SMP'      => Carbon::now(),
            ]
        );

        $no_buktix = $lbhijau->NO_BUKTI;

        // Update Detail
        $length = sizeof($request->input('REC'));
        $NO_ID  = $request->input('NO_ID');

        $REC = $request->input('REC');

        $KD_BRG = $request->input('KD_BRG');
        $NA_BRG = $request->input('NA_BRG');
        $QTY    = $request->input('QTY');

        $query = DB::table('lbhijaud')->where('NO_BUKTI', $request->NO_BUKTI)->whereNotIn('NO_ID', $NO_ID)->delete();

        // Update / Insert
        for ($i = 0; $i < $length; $i++) {
            // Insert jika NO_ID baru
            if ($NO_ID[$i] == 'new') {
                $insert = LbhijauDetail::create(
                    [
                        'NO_BUKTI' => $request->NO_BUKTI,
                        'REC'      => $REC[$i],
                        'PER'      => $periode,
                        'TYPE'     => $this->TYPEZ,
                        'KD_BRG'   => ($KD_BRG[$i] == null) ? "" : $KD_BRG[$i],
                        'NA_BRG'   => ($NA_BRG[$i] == null) ? "" : $NA_BRG[$i],
                        'QTY'      => (float) str_replace(',', '', $QTY[$i]),

                    ]
                );
            } else {
                // Update jika NO_ID sudah ada
                $upsert = LbhijauDetail::updateOrCreate(
                    [
                        'NO_BUKTI' => $request->NO_BUKTI,
                        'NO_ID'    => (int) str_replace(',', '', $NO_ID[$i]),
                    ],

                    [
                        'REC'    => $REC[$i],

                        'KD_BRG' => ($KD_BRG[$i] == null) ? "" : $KD_BRG[$i],
                        'NA_BRG' => ($NA_BRG[$i] == null) ? "" : $NA_BRG[$i],
                        'QTY'    => (float) str_replace(',', '', $QTY[$i]),
                        'TYPE'   => $this->TYPEZ,
                        'PER'    => $periode,
                    ]
                );
            }
        }

        $lbhijau = Lbhijau::where('NO_BUKTI', $no_buktix)->first();

        $no_bukti = $lbhijau->NO_BUKTI;

        DB::SELECT("UPDATE lbhijau, SUP
                    SET lbhijau.NAMAS = SUP.NAMAS  WHERE lbhijau.KODES = SUP.KODES
                    AND lbhijau.NO_BUKTI='$no_buktix';");

        DB::SELECT("UPDATE lbhijau,  lbhijaud
                    SET  lbhijaud.ID =  lbhijau.NO_ID  WHERE  lbhijau.NO_BUKTI =  lbhijaud.NO_BUKTI
                    AND  lbhijau.NO_BUKTI='$no_bukti';");

        // API
        $header = DB::SELECT("SELECT maxh,no_bukti,tgl,qty_beli,rp_beli,kelipatan,kondisi,jns,type,
                            kodes,namas,tg_mulai,tg_akhir,jm_mulai,jm_akhir,ket,usrnm,per,nkartu,
                            tgz,tmm,sop,brg FROM `lbhijau` WHERE NO_BUKTI='" . $no_buktix . "'");
        $detail = DB::SELECT("SELECT no_bukti,id,rec,kd_brg AS kd_brgh,na_brg AS na_brgh,qty FROM `lbhijaud` WHERE NO_BUKTI='" . $no_buktix . "'");

        $data = [
            "status" => "edit",
            "type"   => "PROMO", // Ambil dari request
            "data"   => [(object) [
                "header" => $header,
                "detail" => $detail,
            ],
            ],
        ];

        $response = Http::asJson()->withHeaders([
            'Content-Type' => 'application/json',
        ])->post('http://192.168.0.2/admin-apf-app/public/api/sinkron_hadiah_hijau', $data);

        return redirect('/lbhijau/edit/?idx=' . $lbhijau->NO_ID . '&tipx=edit&typez=' . $this->TYPEZ . '&judul=' . $this->judul . '');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Resbelinse
     */

    // ganti 22

    public function destroy(Request $request, Lbhijau $lbhijau)
    {

        $this->setFlag($request);
        $TYPEZ = $this->TYPEZ;
        $judul = $this->judul;

        $TYPEZ = $_GET['typez'];

        $per      = session()->get('periode')['bulan'] . '/' . session()->get('periode')['tahun'];
        $cekperid = DB::SELECT("SELECT POSTED from perid WHERE PERIO='$per'");
        if ($cekperid[0]->POSTED == 1) {
            return redirect()->route('lbhijau')
                ->with('status', 'Maaf Periode sudah ditutup!')
                ->with(['judul' => $this->judul, 'typez' => $this->TYPEZ]);
        }

        $deletePo = Lbhijau::find($lbhijau->NO_ID);

        $deletePo->delete();

        return redirect('/lbhijau?typez=' . $TYPEZ)
            ->with(['judul' => $judul, 'typez' => $this->TYPEZ])
            ->with('statusHapus', 'Data ' . $lbhijau->NO_BUKTI . ' berhasil dihapus');

    }

    public function cetak(Lbhijau $lbhijau)
    {
        $no_po = $lbhijau->NO_BUKTI;

        $file         = 'poc';
        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

        $query = DB::SELECT("SELECT po.NO_BUKTI, po.TGL, po.KODES, po.NAMAS, po.TOTAL_QTY, po.NOTES, po.ALAMAT,
                                    po.KOTA, pod.KD_BRG, pod.NA_BRG, pod.SATUAN, pod.QTY,
                                    pod.HARGA, pod.TOTAL, pod.KET, po.TPPN, po.NETT, po.GUDANG,
                                    po.JTEMPO, po.TDPP, po.TDISK, pod.DISK
                            FROM po, pod
                            WHERE po.NO_BUKTI='$no_po' AND po.NO_BUKTI = pod.NO_BUKTI
                            ;
		");

        $data = [];

        foreach ($query as $key => $value) {
            array_push($data, [
                'NO_BUKTI' => $query[$key]->NO_BUKTI,
                'TGL'      => $query[$key]->TGL,
                'JTEMPO'   => $query[$key]->JTEMPO,
                'KODES'    => $query[$key]->KODES,
                'NAMAS'    => $query[$key]->NAMAS,
                'ALAMAT'   => $query[$key]->ALAMAT,
                'KOTA'     => $query[$key]->KOTA,
                'KG'       => $query[$key]->KG,
                'HARGA'    => $query[$key]->HARGA,
                'TOTAL'    => $query[$key]->TOTAL,
                'BAYAR'    => $query[$key]->BAYAR,
                'NOTES'    => $query[$key]->NOTES,
                'KD_BRG'   => $query[$key]->KD_BRG,
                'NA_BRG'   => $query[$key]->NA_BRG,
                'SATUAN'   => $query[$key]->SATUAN,
                'QTY'      => $query[$key]->QTY,
                'PPN'      => $query[$key]->TPPN,
                'NETT'     => $query[$key]->NETT,
                'TDPP'     => $query[$key]->TDPP,
                'TDISK'    => $query[$key]->TDISK,
                'DISK'     => $query[$key]->DISK,
                'KET'      => $query[$key]->KET,
                'GUDANG'   => $query[$key]->GUDANG,
            ]);
        }

        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I");

        DB::SELECT("UPDATE PO SET POSTED = 1 WHERE PO.NO_BUKTI='$no_po';");

    }

    public function posting(Request $request)
    {

        $CEK      = $request->input('cek');
        $NO_BUKTI = $request->input('NO_BUKTI');

        $usrnmx = Auth::user()->username;

        $hasil = "";

        if ($CEK) {
            foreach ($CEK as $key => $value) {

                //$STA = $request->input('STA');

                $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
                $bulan   = session()->get('periode')['bulan'];
                $tahun   = substr(session()->get('periode')['tahun'], -2);

                $NO_BUKTIXZ = $NO_BUKTI[$key];

                DB::SELECT("UPDATE PO SET POSTED = 1 WHERE PO.NO_BUKTI='$NO_BUKTIXZ'");

            }
        } else {
            $hasil = $hasil . "Tidak ada PO yang dipilih! ; ";
        }

        if ($hasil != '') {
            return redirect('/lbhijau/index-posting')->with('status', 'Proses Posting PO ..')->with('gagal', $hasil);
        } else {
            return redirect('/lbhijau/index-posting')->with('status', 'Posting Posting PO selesai..');
        }

    }

    public function jtempo(Request $request)
    {
        $tgl   = $request->input('TGL');
        $hari  = substr($tgl, 0, 2);
        $bulan = substr($tgl, 3, 2);
        $tahun = substr($tgl, 6, 4);
        $harix = $request->HARI;

        $datex = Carbon::createFromDate($tahun, $bulan, $hari);

        $datex->addDays($harix);

        $datey = $datex->format('d-m-Y');
        return $datey;

    }

    public function getDetailPo()
    {

        $no_bukti = $_GET['no_bukti'];
        $result   = DB::table('lbhijaud')->where('NO_BUKTI', $no_bukti)->get();

        return response()->json($result);
    }

    // Metode untuk menambah gambar
    public function tampilkan(Request $request)
    {
        $filtersubitem = '';
        $filtersub     = '';
        $filtersupp    = '';
        if ($request->subitemtab2) {
            $filtersubitem = " AND KD_BRG='" . $request->subitemtab2 . "' ";
        }

        if ($request->sub2tab2) {
            $filtersub = " AND SUB>'" . $request->sub1tab2 . "' AND SUB<'" . $request->sub2tab2 . "' ";
        }

        if ($request->sup2tab2) {
            $filtersupp = " AND SUPP>'" . $request->sup1tab2 . "' AND SUPP<'" . $request->sup2tab2 . "' ";
        }

        // ddd($request->subitem);
        $tampilkan = DB::SELECT("SELECT KD_BRG AS KD_BRGTAB2,NA_BRG AS NA_BRGTAB2,KET_UK AS KET_UKTAB2,KET_KEM AS KET_KEMTAB2,NO_ID AS NO_IDTAB2 from masks WHERE NO_ID<>'' $filtersub $filtersupp $filtersubitem ORDER BY KD_BRG ");

        return response()->json($tampilkan);
    }

    // Metode untuk menambah gambar
    public function proseskan(Request $request)
    {
        $nobukti       = $request->nobukti;
        $nobukti2      = $request->nobukti2;
        $kdbrgtab2_all = $request->kdbrgtab2_all;
        $dataBarang    = $request->dataBarang;
        // dd($nobukti2);
        if (! empty($nobukti2)) {
            // Simpan data terkait
            foreach ($dataBarang as $barang) {
                LbhijauDTerkait::where('NO_BUKTI', $nobukti2)->delete();

                LbhijauDTerkait::create([
                    'KD_BRG'   => $barang['kd_brg'],
                    'NA_BRG'   => $barang['na_brg'],
                    'KET_UK'   => $barang['ket_uk'],
                    'KET_KEM'  => $barang['ket_kem'],
                    'NO_BUKTI' => $nobukti2,
                ]);
            }

            $filePath1ijb = '\\\\192.168.0.2\\emailpbl\\barang_hadiah\\ijb.dbf';
            $filePath2ijb = '\\\\192.168.0.2\\emailpbl\\barang_hadiah\\'.$nobukti2.'_ijb.dbf';
            $filePath3ijb = '\\\\192.168.0.2\\emailpbl\\barang_hadiah\\'.$nobukti2.'.ijb'; // File akhir dengan ekstensi .ijb
            @File::copy($filePath1ijb, $filePath2ijb);
            
            $filePath1ijk = '\\\\192.168.0.2\\emailpbl\\barang_hadiah\\ijk.dbf';
            $filePath2ijk = '\\\\192.168.0.2\\emailpbl\\barang_hadiah\\'.$nobukti2.'_ijk.dbf';
            $filePath3ijk = '\\\\192.168.0.2\\emailpbl\\barang_hadiah\\'.$nobukti2.'.ijk'; // File akhir dengan ekstensi .ijb
            @File::copy($filePath1ijk, $filePath2ijk);

            // Ambil data untuk penulisan ke file DBF
            $dataijk = DB::table('lbhijaud_terkait')
                ->join('lbhijau', 'lbhijaud_terkait.NO_BUKTI', '=', 'lbhijau.NO_BUKTI2')
                ->select(
                    'lbhijaud_terkait.NO_BUKTI',
                    DB::raw('RIGHT(lbhijaud_terkait.KD_BRG, 4) AS KD_BRG'),
                    DB::raw('LEFT(lbhijaud_terkait.KD_BRG, 3) AS SUB'),
                    'lbhijaud_terkait.NA_BRG',
                    'lbhijaud_terkait.KET_UK',
                    'lbhijaud_terkait.KET_KEM',
                    'lbhijau.KET',
                    DB::raw('lbhijau.TG_MULAI as TG_MULAI')
                )
                ->where('lbhijaud_terkait.NO_BUKTI', $nobukti2)
                ->get();

            // dd($dataijk);

            foreach ($dataijk as $row1) {
                // === TULIS DATA KE FILE IJK ===
                $downloadsPathIJK = '\\\\192.168.0.2\\emailpbl\\barang_hadiah\\'.$nobukti2.'_ijk.dbf';

                $tableIJK  = new \XBase\TableEditor($downloadsPathIJK);
                $recordIJK = $tableIJK->appendRecord()
                    ->set('sub', $row1->SUB)
                    ->set('noitem', $row1->KD_BRG)
                    ->set('nmbar', $row1->NA_BRG)
                    ->set('ket_uk', $row1->KET_UK)
                    ->set('hs', $row1->NO_BUKTI);
                $tableIJK->writeRecord($recordIJK);
                $tableIJK->save();
                $tableIJK->close();
                // dd($tableIJK);

                // === TULIS DATA KE FILE IJB ===
                $downloadsPathIJB = '\\\\192.168.0.2\\emailpbl\\barang_hadiah\\'.$nobukti2.'_ijb.dbf';
                $tableIJB         = new \XBase\TableEditor($downloadsPathIJB);
                $recordIJB        = $tableIJB->appendRecord()
                    ->set('SUB', $row1->SUB)
                    ->set('kdbar', $row1->KD_BRG)
                    ->set('hadiah_1', $row1->KET)
                    ->set('hadiah_2', $row1->NA_BRG)
                    ->set('tgh', new \DateTime($row1->TG_MULAI))
                    ->set('hs', $row1->NO_BUKTI);
                $tableIJB->writeRecord($recordIJB);
                $tableIJB->save();
                $tableIJB->close();
            }

            // Rename file ke ekstensi baru
            @File::move($filePath2ijb, $filePath3ijb);
            @File::move($filePath2ijk, $filePath3ijk);

            // Update data di tabel lbhijau
            DB::table('lbhijau')
                ->where('NO_BUKTI', $nobukti)
                ->update(['BRG' => $kdbrgtab2_all]);

        }else{
            // Update data di tabel lbhijau
            DB::table('lbhijau')
                ->where('NO_BUKTI', $nobukti)
                ->update(['BRG' => $kdbrgtab2_all]);
    
            // API Sinkronisasi
            $headerData = DB::select("SELECT maxh,no_bukti,tgl,qty_beli,rp_beli,kelipatan,kondisi,jns,type,
                            kodes,namas,tg_mulai,tg_akhir,jm_mulai,jm_akhir,ket,usrnm,per,nkartu,
                            tgz,tmm,sop,brg FROM `lbhijau` WHERE NO_BUKTI='" . $nobukti . "'");
            $detail = DB::select("SELECT no_bukti,id,rec,kd_brg AS kd_brgh,na_brg AS na_brgh,qty FROM `lbhijaud` WHERE NO_BUKTI='" . $nobukti . "'");
    
            $data = [
                "status" => "edit",
                "type"   => "PROMO",
                "data"   => [(object) [
                    "header" => $headerData,
                    "detail" => $detail,
                ]],
            ];
    
            $response = Http::asJson()->withHeaders([
                'Content-Type' => 'application/json',
            ])->post('http://192.168.0.2/admin-apf-app/public/api/sinkron_hadiah_hijau', $data);
        }

        return response()->json(['message' => 'HS updated successfully']);
    }

    // Metode untuk menambah gambar
    public function hapuskan(Request $request)
    {
        $nobukti       = $request->nobukti;
        $nobukti2       = $request->nobukti2;
        $kdbrgtab2_all = $request->kdbrgtab2_all;
        $id            = $request->id;

        // if($id != 0){
        //     DB::table('masks')
        //         ->where('NO_ID', $id)
        //         ->update(['HS' => '']);
        // }elseif($id == 0){
        //     DB::table('masks')
        //         ->where('HS', $nobukti)
        //         ->update(['HS' => '']);
        // }

        if ($id != 0) {
            DB::table('lbhijau')
                ->where('NO_BUKTI', $nobukti)
                ->update(['BRG' => $kdbrgtab2_all]);
        } elseif ($id == 0) {
            DB::table('lbhijau')
                ->where('NO_BUKTI', $nobukti)
                ->update(['BRG' => '']);
        }

        if (! empty($nobukti2)) {
            DB::table('lbhijaud_terkait')
            ->where('NO_BUKTI', $nobukti2)
            ->delete();
        }else{
            // API
            $header = DB::SELECT("SELECT maxh,no_bukti,tgl,qty_beli,rp_beli,kelipatan,kondisi,jns,type,
                                kodes,namas,tg_mulai,tg_akhir,jm_mulai,jm_akhir,ket,usrnm,per,nkartu,
                                tgz,tmm,sop,brg FROM `lbhijau` WHERE NO_BUKTI='" . $nobukti . "'");
            $detail = DB::SELECT("SELECT no_bukti,id,rec,kd_brg AS kd_brgh,na_brg AS na_brgh,qty FROM `lbhijaud` WHERE NO_BUKTI='" . $nobukti . "'");
    
            $data = [
                "status" => "edit",
                "type"   => "PROMO", // Ambil dari request
                "data"   => [(object) [
                    "header" => $header,
                    "detail" => $detail,
                ],
                ],
            ];
    
            $response = Http::asJson()->withHeaders([
                'Content-Type' => 'application/json',
            ])->post('http://192.168.0.2/admin-apf-app/public/api/sinkron_hadiah_hijau', $data);
        }


        return response()->json(['message' => 'HS delete successfully', 'id' => $id]);
    }

}