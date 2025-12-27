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

    public function browse(Request $request)
    {
        // $golz = $request->GOL;


        //pp.GUDANG setelah pp.PKP dihapus
        $pp = DB::SELECT("SELECT  DISTINCT
                    pp.NO_BUKTI AS NO_PP,
                    pp.TGL,CASE 
                    WHEN ppd.NO_PO1 IS NULL OR ppd.NO_PO1 = '' THEN 'TAHAP 1'
                    WHEN ppd.NO_PO2 IS NULL OR ppd.NO_PO2 = '' THEN 'TAHAP 2'
                    WHEN ppd.NO_PO3 IS NULL OR ppd.NO_PO3 = '' THEN 'TAHAP 3'
                    ELSE 'ERROR'
                        END AS NOTES
                FROM pp 
                LEFT JOIN ppd ON pp.NO_BUKTI = ppd.NO_BUKTI
                WHERE 
                    pp.POSTED = 1 
                    AND pp.NOTES = 'OTOMATIS'
                    AND (
                        ppd.NO_PO1 IS NULL OR ppd.NO_PO1 = '' OR
                        ppd.NO_PO2 IS NULL OR ppd.NO_PO2 = '' OR
                        ppd.NO_PO3 IS NULL OR ppd.NO_PO3 = ''
                    )
                ORDER BY pp.NO_BUKTI;

                ");
        return response()->json($pp);
    }

    public function browseuang(Request $request)
    {
        $CBG = Auth::user()->CBG;

        $pp = DB::SELECT("SELECT NO_BUKTI,TGL,  KODES, NAMAS, TOTAL,  BAYAR,
                                TOTAL-BAYAR) AS SISA, ALAMAT, KOTA from po
		                WHERE LNS <> 1 AND CBG = '$CBG' ORDER BY NO_BUKTI; ");

        return response()->json($pp);
    }

    public function browse_detail(Request $request)
    {
        $filterbukti = '';
        if ($request->NO_PO) {

            $filterbukti = " WHERE a.NO_BUKTI='" . $request->NO_PO . "' AND a.KD_BHN = b.KD_BHN ";
        }
        $ppd = DB::SELECT("SELECT a.REC, a.KD_BHN, a.NA_BHN, a.SATUAN , a.QTY, a.HARGA, a.KIRIM, a.SISA,
                                b.SATUAN AS SATUAN_PO, a.QTY AS QTY_PO, b.KALI AS KALI
                            from ppd a, bhn b
                            $filterbukti ORDER BY NO_BUKTI ");


        return response()->json($ppd);
    }


    public function browse_detail2(Request $request)
    {
        $filterbukti = '';
        if ($request->NO_PO) {

            $filterbukti = " WHERE NO_BUKTI='" . $request->NO_PO . "' AND a.KD_BRG = b.KD_BRG ";
        }
        $ppd = DB::SELECT("SELECT a.REC, a.KD_BRG, a.NA_BRG, a.SATUAN , a.QTY, a.HARGA, a.KIRIM, a.SISA,
                                b.SATUAN AS SATUAN_PO, a.QTY AS QTY_PO, b.KALI AS KALI
                            from ppd a, brg b
                            $filterbukti ORDER BY NO_BUKTI ");


        return response()->json($ppd);
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
                    $btnPrint =   ' href="khusus/cetak/' . $row->NO_ID . '" target="_blank';
                    
                $btnPrivilege =
                        '
                                <a class="dropdown-item" ' . $btnEdit . '>
                                <i class="fas fa-edit"></i>
                                    Edit
                                </a>
                                <a class="dropdown-item btn btn-danger" '.$btnPrint.' href="khusus/cetak_khusus/?nobukti=' . $row->NO_BUKTI . '&flagz=' . $row->FLAG . '">
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

        $suffix = $cbgSuffix[$cbg] ?? '';

        $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];

        $bulan    = session()->get('periode')['bulan'];
        $tahun    = substr(session()->get('periode')['tahun'], -2);

        $query = DB::table('po_dc_ts')->select('NO_BUKTI')->where('PER', $periode)->where('FLAG', 'PT')->where('CBG', $CBG)
            ->orderByDesc('NO_BUKTI')->limit(1)->get();

        if ($query != '[]') {
            $query = substr($query[0]->NO_BUKTI, -4);
            $query = str_pad($query + 1, 4, 0, STR_PAD_LEFT);
            $no_bukti = 'PT' . $tahun . $bulan . '-' . $query . $suffix;
        } else {
            $no_bukti = 'PT' . $suffix . $tahun . $bulan . '-0001';
        }



        $pp = Pp::create(
            [
                'NO_BUKTI'         => $no_bukti,
                'TGL'              => date('Y-m-d', strtotime($request['TGL'])),
                'PER'              => $periode,
                'FLAG'             => 'PT',
                'CBG'              => $CBG,
                'TOTAL_QTY'        => (float) str_replace(',', '', $request['TTOTAL_QTY']),
                'USRNM'            => Auth::user()->username,
                'TG_SMP'           => Carbon::now(),
                'created_by'       => Auth::user()->username,
            ]
        );


        $REC        = $request->input('REC');
        $KD_BRG     = $request->input('KD_BRG');
        $NA_BRG     = $request->input('NA_BRG');
        $SATUAN     = $request->input('SATUAN');
        $KODES     = $request->input('KODES');
        $QTY        = $request->input('QTY');
        $TAHAP1        = $request->input('TAHAP1');
        $TAHAP2        = $request->input('TAHAP2');
        $TAHAP3        = $request->input('TAHAP3');

        // Check jika value detail ada/tidak
        if ($REC) {
            foreach ($REC as $key => $value) {
                // Declare new data di Model
                $detail    = new PpDetail;
                $parts = explode('-', $KODES[$key]);
                
                // Insert ke Database
                $detail->NO_BUKTI    = $no_bukti;
                $detail->REC         = $REC[$key];
                $detail->PER         = $periode;
                $detail->FLAG        = $FLAGZ;
                $detail->GOL          = $GOLZ;
                $detail->KD_BRG      = ($KD_BRG[$key] == null) ? "" :  $KD_BRG[$key];
                $detail->NA_BRG      = ($NA_BRG[$key] == null) ? "" :  $NA_BRG[$key];
                $detail->SATUAN      = ($SATUAN[$key] == null) ? "" :  $SATUAN[$key];
                $detail->KODES       = ($KODES[$key] == null) ? "" :  $parts[0];
                $detail->NAMAS       = ($KODES[$key] == null) ? "" :  $parts[1];
                $detail->QTY         = (float) str_replace(',', '', $QTY[$key]);
                $detail->TAHAP1         = (float) str_replace(',', '', $TAHAP1[$key]);
                $detail->TAHAP2         = (float) str_replace(',', '', $TAHAP2[$key]);
                $detail->TAHAP3         = (float) str_replace(',', '', $TAHAP3[$key]);
                $detail->save();
            }
        }

        $no_buktix = $no_bukti;

        $pp = Pp::where('NO_BUKTI', $no_buktix)->first();


        DB::SELECT("UPDATE pp,  ppd
                            SET  ppd.ID =  pp.NO_ID  WHERE  pp.NO_BUKTI =  ppd.NO_BUKTI
							AND  pp.NO_BUKTI='$no_buktix';");
  

        DB::SELECT("UPDATE ppd, zsup
                SET ppd.PKP = zsup.GOLONGAN,
                    ppd.PKP = CASE
                                WHEN zsup.GOLONGAN = 'P0' THEN 0
                                ELSE 1
                            END
                WHERE ppd.KODES = zsup.KODES
                AND ppd.NO_BUKTI = '$no_bukti';");


        // return redirect('/pp/edit/?idx=' . $pp->NO_ID . '&tipx=edit&flagz=' . $this->FLAGZ . '&golz=' . $this->GOLZ . '&judul=' . $this->judul . '');
        return redirect('/pp?flagz=' . $FLAGZ . '&golz=' . $GOLZ)->with(['judul' => $judul, 'golz' => $GOLZ, 'flagz' => $FLAGZ]);
    }

    public function edit(Request $request, Pp $pp)
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

            $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from pp
		                 where PER ='$per' and FLAG ='$this->FLAGZ'
                         and GOL ='$this->GOLZ'
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


            $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from pp
		                 where PER ='$per'
						 and FLAG ='$this->FLAGZ'
                         and GOL ='$this->GOLZ'
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

            $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from pp
		             where PER ='$per'
					 and FLAG ='$this->FLAGZ'
                     and GOL ='$this->GOLZ'
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

            $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from pp
		             where PER ='$per'
					 and FLAG ='$this->FLAGZ'
                     and GOL ='$this->GOLZ'
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

            $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from pp
						where PER ='$per'
						and FLAG ='$this->FLAGZ'
                        and GOL ='$this->GOLZ'
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
            $pp = Pp::where('NO_ID', $idx)->first();
            $pp->namaCabang = DB::table('compan')->where('KODE', $pp->GUDANG)->value('NAMA');
        } else {
            $pp = new Pp;
            $pp->TGL = Carbon::now();
        }

        $no_bukti = $pp->NO_BUKTI;
        $ppDetail = DB::table('ppd')->where('NO_BUKTI', $no_bukti)->orderBy('REC')->get();

        $data = [
            'header'        => $pp,
            'detail'        => $ppDetail

        ];


        return view('otransaksi_pp.edit', $data)
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
    public function browseSupplier(Request $request)
    {
        $KD_BRG = $request->KD_BRG;
        
        $sup = DB::SELECT("SELECT  vbrgdw.HARGA, vbrgdw.DISC, vbrgdw.DISC2, vbrgdw.DISC3, vbrgdw.DISC4, zsup.KODES, zsup.NAMAS, CONCAT(zsup.KODES,'-',zsup.NAMAS) AS NAMAS2, ALAMAT, KOTA, EMAIL, NOTBAY, KONTAK, AKTIF, CASE WHEN PKP = '1' THEN '(PKP)' ELSE '(NON PKP)' END AS PKP2, 
                            PKP, HARI from 	zsup JOIN vbrgdw on zsup.KODES = vbrgdw.KODES WHERE vbrgdw.KD_BRG = '$KD_BRG'
                        ORDER BY KODES; ");


        return response()->json($sup);

    }
    public function update(Request $request, Pp $pp)
    {

        $this->validate(
            $request,
            [

                'TGL'      => 'required'
            ]
        );

        $this->setFlag($request);
        $GOLZ = $this->GOLZ;
        $FLAGZ = $this->FLAGZ;
        $judul = $this->judul;

        $CBG = Auth::user()->CBG;


        $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];


        $pp->update(
            [
                'TGL'              => date('Y-m-d', strtotime($request['TGL'])),
                'TOTAL_QTY'        => (float) str_replace(',', '', $request['TTOTAL_QTY']),
                'USRNM'            => Auth::user()->username,
                'TG_SMP'           => Carbon::now(),
                'updated_by'       => Auth::user()->username,
                'FLAG'             => 'PP',
                'GOL'              => $GOLZ,
                'CBG'              => $CBG,
                'GUDANG'              => $request->COMPAN,
            ]
        );

        $no_buktix = $pp->NO_BUKTI;

        // Update Detail
        $length = sizeof($request->input('REC'));
        $NO_ID  = $request->input('NO_ID');

        $REC    = $request->input('REC');

        $KD_BRG = $request->input('KD_BRG');
        $NA_BRG = $request->input('NA_BRG');
        $SATUAN = $request->input('SATUAN');
        $KODES = $request->input('KODES');
        $QTY    = $request->input('QTY');
        $TAHAP1    = $request->input('TAHAP1');
        $TAHAP2    = $request->input('TAHAP2');
        $TAHAP3    = $request->input('TAHAP3');

        $query = DB::table('ppd')->where('NO_BUKTI', $request->NO_BUKTI)->whereNotIn('NO_ID',  $NO_ID)->delete();

        // Update / Insert
        for ($i = 0; $i < $length; $i++) {
            // Insert jika NO_ID baru
            $parts = explode('-', $KODES[$i]);
            if ($NO_ID[$i] == 'new') {
                $insert = PpDetail::create(
                    [
                        'NO_BUKTI'   => $request->NO_BUKTI,
                        'REC'        => $REC[$i],
                        'PER'        => $periode,
                        'FLAG'       => $this->FLAGZ,
                        'GOL'        => $this->GOLZ,
                        'KD_BRG'     => ($KD_BRG[$i] == null) ? "" :  $KD_BRG[$i],
                        'NA_BRG'     => ($NA_BRG[$i] == null) ? "" :  $NA_BRG[$i],
                        'SATUAN'     => ($SATUAN[$i] == null) ? "" :  $SATUAN[$i],
                        'KODES'     => ($KODES[$i] == null) ? "" :  $parts[0],
                        'NAMAS'     => ($KODES[$i] == null) ? "" :  $parts[1],
                        'QTY'        => (float) str_replace(',', '', $QTY[$i]),
                        'TAHAP1'        => (float) str_replace(',', '', $TAHAP1[$i]),
                        'TAHAP2'        => (float) str_replace(',', '', $TAHAP2[$i]),
                        'TAHAP3'        => (float) str_replace(',', '', $TAHAP3[$i]),
                        
                        ]
                    );
                } else {
                    // Update jika NO_ID sudah ada
                    $upsert = PpDetail::updateOrCreate(
                        [
                            'NO_BUKTI'  => $request->NO_BUKTI,
                        'NO_ID'     => (int) str_replace(',', '', $NO_ID[$i])
                    ],
                    
                    [
                        'REC'        => $REC[$i],
                        
                        'KD_BRG'     => ($KD_BRG[$i] == null) ? "" :  $KD_BRG[$i],
                        'NA_BRG'     => ($NA_BRG[$i] == null) ? "" :  $NA_BRG[$i],
                        'SATUAN'     => ($SATUAN[$i] == null) ? "" :  $SATUAN[$i],
                        'KODES'     => ($KODES[$i] == null) ? "" :  $parts[0],
                        'NAMAS'     => ($KODES[$i] == null) ? "" :  $parts[1],
                        'QTY'        => (float) str_replace(',', '', $QTY[$i]),
                        'TAHAP1'        => (float) str_replace(',', '', $TAHAP1[$i]),
                        'TAHAP2'        => (float) str_replace(',', '', $TAHAP2[$i]),
                        'TAHAP3'        => (float) str_replace(',', '', $TAHAP3[$i]),
                        'FLAG'       => $this->FLAGZ,
                        'GOL'        => $this->GOLZ,
                        'PER'        => $periode,
                        ]
                    );
                }
            }
            
        $pp = Pp::where('NO_BUKTI', $no_buktix)->first();

        $no_bukti = $pp->NO_BUKTI;
        
        DB::SELECT("UPDATE pp,  ppd
                    SET  ppd.ID =  pp.NO_ID  WHERE  pp.NO_BUKTI =  ppd.NO_BUKTI
                    AND  pp.NO_BUKTI='$no_bukti';");

        DB::SELECT("UPDATE ppd, sup
                SET ppd.PKP = sup.GOLONGAN,
                    ppd.PKP = CASE
                                WHEN sup.GOLONGAN = 'P0' THEN 0
                                ELSE 1
                            END
                WHERE ppd.KODES = sup.KODES
                AND ppd.NO_BUKTI = '$no_bukti';");

// return redirect('/pp/edit/?idx=' . $pp->NO_ID . '&tipx=edit&flagz=' . $this->FLAGZ . '&golz=' . $this->GOLZ . '&judul=' . $this->judul . '');
return redirect('/pp?flagz=' . $FLAGZ . '&golz=' . $GOLZ)->with(['judul' => $judul, 'golz' => $GOLZ, 'flagz' => $FLAGZ]);
}
private function createPoFromPp($supplier, $items, $gudang)
{
  
    $CBG = Auth::user()->CBG;
    $periode = session()->get('periode')['bulan'] . '/' . session()->get('periode')['tahun'];
    $bulan = session()->get('periode')['bulan'];
    $tahun = substr(session()->get('periode')['tahun'], -2);
    $createdPos = [];

    // Get last PO number once at the beginning
    $query = DB::table('po')->select('NO_BUKTI')
        ->where('PER', $periode)
        ->where('FLAG', 'PO')
        ->where('CBG', $CBG)
        ->where('GOL', 'J')
        ->orderByDesc('NO_BUKTI')
        ->limit(1)
        ->get();

    if ($query->isNotEmpty()) {
        $lastNumber = (int)substr($query[0]->NO_BUKTI, -4);
    } else {
        $lastNumber = 0;
    }

    // Loop untuk setiap tahap (TAHAP1, TAHAP2, TAHAP3)
    for ($tahap = 1; $tahap <= 3; $tahap++) {
        $tahapField = 'TAHAP' . $tahap;
        
        // Skip jika tidak ada item dengan qty di tahap ini
        $hasItems = false;
        foreach ($items as $item) {
            if (isset($item->$tahapField) && (float)$item->$tahapField > 0) {
                $hasItems = true;
                break;
            }
        }

        if (!$hasItems) {
            continue;
        }

        // Increment nomor untuk setiap tahap
        $lastNumber++;
        $newNumber = str_pad($lastNumber, 4, 0, STR_PAD_LEFT);
        $no_bukti = 'PO' . $CBG . $tahun . $bulan . '-' . $newNumber;

        // Calculate totals untuk tahap ini
        $totalQty = 0;
        $totalAmount = 0;
        $totalDisk = 0;
        $totalDpp = 0;
        $totalPpn = 0;
        
        foreach ($items as $item) {
            if (isset($item->$tahapField)) {
                $qtyTahap = (float)$item->$tahapField;
                if ($qtyTahap > 0) {
                    $totalQty += $qtyTahap;
                    
                    // Get harga and discount data
                    $HARGA = DB::table('vbrgdw')->where('KD_BRG', $item->KD_BRG)->where('KODES', $supplier->KODES)->value('HARGA') ?? 0;
                    $DISK = DB::table('vbrgdw')->where('KD_BRG', $item->KD_BRG)->where('KODES', $supplier->KODES)->value('DISC') ?? 0;
                    $DISK2 = DB::table('vbrgdw')->where('KD_BRG', $item->KD_BRG)->where('KODES', $supplier->KODES)->value('DISC2') ?? 0;
                    $DISK3 = DB::table('vbrgdw')->where('KD_BRG', $item->KD_BRG)->where('KODES', $supplier->KODES)->value('DISC3') ?? 0;
                    $DISK4 = DB::table('vbrgdw')->where('KD_BRG', $item->KD_BRG)->where('KODES', $supplier->KODES)->value('DISC4') ?? 0;
                    
                    // Calculate TOTAL per item (same as JavaScript formula)
                    $itemTotal = ((((($qtyTahap * $HARGA) * (100 - $DISK) / 100) * (100 - $DISK2) / 100) * (100 - $DISK3) / 100) * (100 - $DISK4) / 100);
                    $totalAmount += $itemTotal;
                    $totalDisk += $DISK; // Sum of discount percentages
                    
                    // Calculate DPP and PPN based on PKP
                    if ($supplier->PKP == 0) {
                        // Non PKP
                        $dpp = $itemTotal;
                        $ppn = 0;
                    } else {
                        // PKP
                        $dpp = $itemTotal * 100 / 111;
                        $ppn = $itemTotal - $dpp;
                    }
                    
                    $totalDpp += $dpp;
                    $totalPpn += $ppn;
                }
            }
        }
        
        $totalNett = $totalDpp + $totalPpn;
        // Create PO header
        $po = Po::create([
            'NO_BUKTI' => $no_bukti,
            'TGL' => now()->format('Y-m-d'),
            'JTEMPO'  => now()->addDays(7)->format('Y-m-d'),
            'JTEMPO2' => now()->addDays(7)->format('Y-m-d'),
            'JTEMPO3' => now()->addDays(7)->format('Y-m-d'),
            'PER' => $periode,
            'KODES' => $supplier->KODES,
            'NAMAS' => $supplier->NAMAS,
            'ALAMAT' => $supplier->ALAMAT,
            'KOTA' => $supplier->KOTA,
            'FLAG' => 'PO',
            'GOL' => 'J',
            'CBG' => $CBG,
            'GUDANG' => $gudang,
            'TOTAL_QTY' => $totalQty,
            'TOTAL' => $totalAmount,
            'TDISK' => $totalDisk,
            'TDPP' => $totalDpp,
            'TPPN' => $totalPpn,
            'NETT' => $totalNett,
            'HARI' => 7,
            'PKP' => $supplier->PKP,
            'USRNM' => Auth::user()->username,
            'TG_SMP' => now(),
            'NOTES' =>'Supplier: '. $supplier->NAMAS . ' TAHAP ' . $tahap,

        ]);

        // Create PO details untuk tahap ini
        foreach ($items as $index => $item) {
            $qtyTahap = isset($item->$tahapField) ? (float)$item->$tahapField : 0;

            // Skip jika qty tahap ini 0
            if ($qtyTahap <= 0) {
                continue;
            }
            $HARGA = DB::table('vbrgdw')->where('KD_BRG', $item->KD_BRG)->where('KODES', $supplier->KODES)->value('HARGA');
            $DISK = DB::table('vbrgdw')->where('KD_BRG', $item->KD_BRG)->where('KODES', $supplier->KODES)->value('DISC');
            $DISK2 = DB::table('vbrgdw')->where('KD_BRG', $item->KD_BRG)->where('KODES', $supplier->KODES)->value('DISC2');
            $DISK3 = DB::table('vbrgdw')->where('KD_BRG', $item->KD_BRG)->where('KODES', $supplier->KODES)->value('DISC3');
            $DISK4 = DB::table('vbrgdw')->where('KD_BRG', $item->KD_BRG)->where('KODES', $supplier->KODES)->value('DISC4');
            $detail = new PoDetail;
            $detail->NO_BUKTI = $no_bukti;
            $detail->REC = $item->REC;
            $detail->PER = $periode;
            $detail->FLAG = 'PO';
            $detail->GOL = 'J';
            $detail->KD_BRG = $item->KD_BRG ?? '';
            $detail->NA_BRG = $item->NA_BRG ?? '';
            $detail->SATUAN = $item->SATUAN ?? '';
            $detail->QTY = $qtyTahap;
            $detail->HARGA = $HARGA;
            $detail->TOTAL = (((($qtyTahap * $HARGA) * (100 - $DISK) / 100) * (100 - $DISK2) / 100) * (100 - $DISK3) / 100) * (100 - $DISK4) / 100;
            $detail->SISA = $qtyTahap;
            
            if ($supplier->PKP == 0) {
                $detail->DPP = $detail->TOTAL;
                $detail->PPN = 0;
            } else {
                $detail->DPP = $detail->TOTAL * 100 / 111;
                $detail->PPN = $detail->TOTAL - $detail->DPP;
            }
            $detail->HARGALAMA = $HARGA;
            $detail->DISKLAMA = $DISK;
            $detail->DISK = $DISK;
            $detail->DISK2 = $DISK2;
            $detail->DISK3 = $DISK3;
            $detail->DISK4 = $DISK4;
            $detail->DISKLAMA2 = $DISK2;
            $detail->DISKLAMA3 = $DISK3;
            $detail->DISKLAMA4 = $DISK4;
            $detail->CBG = $CBG;
            $detail->save();
        }

        // Update supplier info and relationships
        $no_buktix = $no_bukti;

        // Update supplier information from zsup table
        DB::SELECT("UPDATE po, zsup
                SET po.NAMAS = zsup.NAMAS,
                    po.ALAMAT = zsup.ALAMAT,
                    po.KOTA = zsup.KOTA,
                    po.PKP = CASE
                                WHEN zsup.GOLONGAN = 'P0' THEN 0
                                ELSE 1
                            END,
                    po.HARI = zsup.HARI
                WHERE po.KODES = zsup.KODES
                AND po.NO_BUKTI = '$no_buktix'");

        // Update detail ID references
        DB::SELECT("UPDATE po, pod
                SET pod.ID = po.NO_ID 
                WHERE po.NO_BUKTI = pod.NO_BUKTI
                AND po.NO_BUKTI = '$no_buktix'");

        // Update ppd dengan NO_PO berdasarkan tahap
        $noPoField = 'NO_PO' . $tahap;
        $tahapFieldWhere = 'TAHAP' . $tahap;
        DB::statement(
            "UPDATE ppd SET $noPoField = ? WHERE NO_BUKTI IN (
                        SELECT DISTINCT NO_BUKTI FROM (
                            SELECT NO_BUKTI FROM ppd WHERE KODES = ? AND NAMAS = ?
                        ) AS temp
                    ) AND KODES = ? AND NAMAS = ? AND $tahapFieldWhere > 0",
            [$no_buktix, $supplier->KODES, $supplier->NAMAS, $supplier->KODES, $supplier->NAMAS]
        );

        $createdPos[] = $no_buktix;
        
        Log::info("Created PO: $no_bukti for TAHAP $tahap with total QTY: $totalQty");
    }

    return $createdPos;
}
/**
 * Remove the specified resource from storage.
*
* @param  \App\Models\Master\Rute  $rute
* @return \Illuminate\Http\Response
*/

// ganti 22

    public function destroy(Request $request, Pp $pp)
    {
        
    $this->setFlag($request);
    $FLAGZ = $this->FLAGZ;
    $GOLZ = $this->GOLZ;
    $judul = $this->judul;
    
    // ini dr mana $this->GOLZ?
    $GOLZ = $_GET['golz'];
    $FLAGZ = $_GET['flagz'];
    
    $per = session()->get('periode')['bulan'] . '/' . session()->get('periode')['tahun'];
    $cekperid = DB::SELECT("SELECT POSTED from perid WHERE PERIO='$per'");
        if ($cekperid[0]->POSTED == 1) {
            return redirect()->route('pp')
                ->with('status', 'Maaf Periode sudah ditutup!')
                ->with(['judul' => $this->judul, 'flagz' => $this->FLAGZ, 'golz' => $this->GOLZ]);
            }
            
            $deletePp = Pp::find($pp->NO_ID);
            
            $deletePp->delete();
            // return redirect('/pp?flagz=' . $FLAGZ . '&golz=J')
            return redirect('/pp?flagz=' . $FLAGZ . '&golz=' . $GOLZ)
            ->with(['judul' => $judul, 'flagz' => $this->FLAGZ, 'golz' => $this->GOLZ])
            ->with('statusHapus', 'Data ' . $pp->NO_BUKTI . ' berhasil dihapus');
        }

        public function cetak(Pp $pp)
        {
            $no_pp = $pp->NO_BUKTI;
            
            $file     = 'ppc';
            $PHPJasperXML = new PHPJasperXML();
            $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));
            
            //pp.GUDANG setelah pp.NETT dihapus
            $query = DB::SELECT("SELECT pp.NO_BUKTI, pp.TGL, pp.TOTAL_QTY, pp.NOTES, pp.POSTED,
                                    ppd.KD_BRG, ppd.NA_BRG, ppd.SATUAN, ppd.QTY, ppd.TAHAP1, ppd.TAHAP2, ppd.TAHAP3, vbrg.KET_UK, ppd.KET, vbrg.HJUAL
                            FROM pp, ppd, vbrg
                            WHERE pp.NO_BUKTI='$no_pp' AND pp.NO_BUKTI = ppd.NO_BUKTI
                            AND ppd.KD_BRG = vbrg.KD_BRG
                            ;
            
		");

           
            $data = [];
            foreach ($query as $key => $value) {
                array_push($data, array(
                    'NO_BUKTI' => $query[0]->NO_BUKTI,
                    'TGL'      => $query[0]->TGL,
                    'KD_BRG'    => $query[$key]->KD_BRG,
                    'NA_BRG'    => $query[$key]->NA_BRG,
                    'SATUAN'    => $query[$key]->SATUAN,
                    'QTY'    => $query[$key]->QTY,
                    'TAHAP1'    => $query[$key]->TAHAP1,
                    'TAHAP2'    => $query[$key]->TAHAP2,
                    'TAHAP3'    => $query[$key]->TAHAP3,
                    'H_JUAL'    => $query[$key]->HJUAL,
                    'KET'    => $query[$key]->KET == null ? '-' : $query[$key]->KET,
                    'KET_UK'    => $query[$key]->KET_UK,
                ));
            }
    
            $PHPJasperXML->setData($data);
            ob_end_clean();
            $PHPJasperXML->outpage("I");
        if ($pp->NOTES <> 'OTOMATIS') {

            if ($pp->POSTED != 1) {
            $gudang = DB::SELECT("SELECT GUDANG FROM pp WHERE pp.NO_BUKTI='$no_pp'")[0]->GUDANG;            
                // Get all data efficiently in o ne query
                $suppliers = DB::SELECT("
                    SELECT DISTINCT 
                        ppd.KODES, 
                        ppd.NAMAS,
                        COALESCE(zsup.ALAMAT, '') as ALAMAT,
                        COALESCE(zsup.KOTA, '') as KOTA,
                        CASE
                                WHEN COALESCE(zsup.GOLONGAN, '') = 'P0' THEN 0
                                ELSE 1 
                        END AS  PKP 
                    FROM ppd 
                    LEFT JOIN zsup ON ppd.KODES = zsup.KODES AND ppd.NAMAS = zsup.NAMAS
                    WHERE ppd.NO_BUKTI = ?
                ", [$no_pp]);
                
                // Get all PPD items grouped by supplier
                $allItems = DB::SELECT("
                    SELECT 
                        REC, KD_BRG, NA_BRG, SATUAN, QTY, KODES, NAMAS, TAHAP1, TAHAP2, TAHAP3
                    FROM ppd 
                    WHERE NO_BUKTI = ? 
                    ORDER BY KODES, NAMAS, REC
                ", [$no_pp]);
                // Group items by supplier
                $groupedItems = [];
                foreach ($allItems as $item) {
                    $key = $item->KODES . '|' . $item->NAMAS;
                    if (!isset($groupedItems[$key])) {
                        $groupedItems[$key] = [];
                    }
                    $groupedItems[$key][] = $item;
                }
                    foreach ($suppliers as $supplier) {
                        $key = $supplier->KODES . '|' . $supplier->NAMAS;
                        $items = $groupedItems[$key] ?? [];
                        
                        if (empty($items)) continue;
                        
                        try {
                            Log::info("Creating PO for supplier: {$supplier->KODES} - {$supplier->NAMAS} with " . count($items) . " items");
                            $noBuktiPo = $this->createPoFromPp($supplier, $items,$gudang);
                            Log::info("Successfully created PO: $noBuktiPo");
                        } catch (\Exception $e) {
                            Log::error("Failed to create PO for supplier {$supplier->KODES}: " . $e->getMessage());
                            Log::error("Stack trace: " . $e->getTraceAsString());
                        }
                    }
                }
            }
            
        DB::SELECT("UPDATE pp SET POSTED = 1 WHERE pp.NO_BUKTI='$no_pp';");
    }

    
    
    public function posting(Request $request)
    {
        
        $CEK = $request->input('cek');
        $NO_BUKTI = $request->input('NO_BUKTI');

        $usrnmx = Auth::user()->username;

        $hasil = "";
        
        if ($CEK) {
            foreach ($CEK as $key => $value) {
                
                //$STA = $request->input('STA');
                
                $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
                $bulan    = session()->get('periode')['bulan'];
                $tahun    = substr(session()->get('periode')['tahun'], -2);
                
                $NO_BUKTIXZ  = $NO_BUKTI[$key];

                
                DB::SELECT("UPDATE PO SET POSTED = 1 WHERE PO.NO_BUKTI='$NO_BUKTIXZ'");
            }
        } else {
            $hasil = $hasil . "Tidak ada PO yang dipilih! ; ";
        }
        
        if ($hasil != '') {
            return redirect('/pp/index-posting')->with('status', 'Proses Posting PO ..')->with('gagal', $hasil);
        } else {
            return redirect('/pp/index-posting')->with('status', 'Posting PO selesai..');
        }
    }
    

    // public function jtempo ( Request $request)
    // {
        // 	$tgl = $request->input('TGL');
    // 	$hari = substr($tgl,0,2);
    // 	$bulan = substr($tgl,3,2);
    // 	$tahun = substr($tgl,6,4);
    // 	$harix = $request->HARI;
    
    // 	$datex = Carbon::createFromDate($tahun, $bulan, $hari );
    
    //     $datex ->addDays($harix);
    
    //     $datey = $datex->format('d-m-Y');
    // 	return  $datey;
    
    
    // }
    
    
    public function getDetailPp()
    {
        
        $no_bukti = $_GET['no_bukti'];
        $result = DB::table('ppd')->where('NO_BUKTI', $no_bukti)->get();
        
        return response()->json($result);;
    }

    public function posting_pp(Request $request)
    {
        if (! $request->isMethod('post')) {
            return response()->json(['error' => 'Method Not Allowed'], 405);
        }
        
        $data = $request->input('posted');
        
        if (! $data) {
            return response()->json(['error' => 'Tidak ada data yang dikirim'], 400);
        }
        
        foreach ($data as $id => $posted) {
            DB::table('pp')->where('NO_ID', $id)->update(['POSTED' => $posted]);
        }

        return response()->json(['message' => 'Status berhasil diperbarui']);
    }

    /**
     * Create PO directly from PP data (internal method, no HTTP)
     */

    public function ppOtomatis()
    {
        $bulan = session()->get('periode')['bulan'];
        $tahun = (int)session()->get('periode')['tahun'];
        $periode = session()->get('periode')['bulan'] . '/' . session()->get('periode')['tahun'];
        $CBG = 'DC';
        $total_qty = 0;
        $periodeAwal = $bulan<7?true:false;
        $query = "";
        if($periodeAwal){
            $query = "SELECT KD_BRG, SUM(QTY) AS QTY FROM belid, beli WHERE belid.no_bukti = beli.no_bukti AND beli.POSTED = 1 AND beli.PER >= '07/" . ($tahun - 1) . "' and beli.PER <= '12/" . ($tahun - 1) . "' AND beli.FLAG = 'BL' GROUP BY KD_BRG";
            
        }
        else{
            $query = "SELECT KD_BRG, SUM(QTY) AS QTY FROM belid, beli WHERE belid.no_bukti = beli.no_bukti AND beli.POSTED = 1 AND beli.PER >= '01/$tahun' AND beli.PER <= '06/$tahun' AND beli.FLAG = 'BL' GROUP BY KD_BRG";
        }
        $data = DB::SELECT($query);
        foreach($data as $item){
            if($periodeAwal){
                $dataBarang =DB::SELECT("SELECT AW02 AS STOK FROM VBRGD WHERE KD_BRG = '$item->KD_BRG' AND CBG = 'DC'");
            }else{
                $dataBarang =DB::SELECT("SELECT AW01 AS STOK FROM VBRGD WHERE KD_BRG = '$item->KD_BRG' AND CBG = 'DC'");
            }
            echo $dataBarang[0]->STOK;
            $item->ppQty = $item->QTY - $dataBarang[0]->STOK;
            $total_qty += $item->ppQty;
            echo '<br>';
            echo $item->QTY;
            echo '<br>';
            echo $item->ppQty;
            echo '<br>';
            
        }

        $query = DB::table('pp')->select('NO_BUKTI')->where('PER', $periode)->where('FLAG', 'PP')
            ->where('GOL', 'J')->orderByDesc('NO_BUKTI')->limit(1)->get();

        if ($query != '[]') {
            $query = substr($query[0]->NO_BUKTI, -4);
            $query = str_pad($query + 1, 4, 0, STR_PAD_LEFT);
            $no_bukti = 'PP' . $CBG . substr($tahun,2) . $bulan . '-' . $query;
        } else {
            $no_bukti = 'PP' . $CBG . substr($tahun,2) . $bulan . '-0001';
        }



        $pp = Pp::create(
            [
                'NO_BUKTI'         => $no_bukti,
                'TGL'              => now(),
                'PER'              => $periode,
                'FLAG'             => 'PP',
                'GOL'              => 'J',
                'CBG'              => $CBG,
                'NOTES'              => 'OTOMATIS',
                'GUDANG'           => $CBG,
                'TOTAL_QTY'        => $total_qty,
                'USRNM'            => Auth::user()->username,
                'TG_SMP'           => Carbon::now(),
                'created_by'       => Auth::user()->username,
            ]
        );
        foreach($data as $item){
            if($item->ppQty > 0){
                PpDetail::create(
                    [
                        'NO_BUKTI'   => $no_bukti,
                        'ID'   => $pp->NO_ID,
                        'REC'        => PpDetail::where('NO_BUKTI', $no_bukti)->count() + 1,
                        'PER'        => $periode,
                        'FLAG'       => 'PP',
                        'GOL'        => 'J',
                        'KD_BRG'     => $item->KD_BRG,
                        'NA_BRG'     => DB::table('vbrg')->where('KD_BRG', $item->KD_BRG)->value('NA_BRG'),
                        'SATUAN'     => DB::table('vbrg')->where('KD_BRG', $item->KD_BRG)->value('SATUAN'),
                        'KODES'     => DB::table('vbrg')->where('KD_BRG', $item->KD_BRG)->value('KODES'),
                        'NAMAS'     => DB::table('vbrg')->where('KD_BRG', $item->KD_BRG)->value('NAMAS'),
                        'QTY'        => $item->ppQty,
                    ]
                );
            }
        }
        return redirect()->to("pp/edit?idx={$pp->NO_ID}&tipx=edit&flagz=PP&judul=Default Judul&golz=J");
    }
}
