<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
// ganti 1

use App\Models\OTransaksi\Po;
use App\Models\OTransaksi\PoDetail;
use App\Models\Master\Sup;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use DB;
use Carbon\Carbon;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";
use PHPJasperXML;

// ganti 2
class PoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    var $judul = '';
    var $FLAGZ = '';
    var $GOLZ = '';

    function setFlag(Request $request)
    {

        if ( $request->flagz == 'PO' && $request->golz == 'B' ) {
            $this->judul = "PO Bahan Baku";
        } else if ( $request->flagz == 'PO' && $request->golz == 'J' ) {
            $this->judul = "PO Barang";
        } else if ( $request->flagz == 'PO' && $request->golz == 'C' ) {
            $this->judul = "PO Customer";
        }else if ( $request->flagz == 'PP' && $request->golz == 'P' ) {
            $this->judul = "PO Posting";
        }

        $this->FLAGZ = $request->flagz;
        $this->GOLZ = $request->golz;

    }

    public function index(Request $request)
    {

	    $this->setFlag($request);

        if ($this->FLAGZ == 'PP' && $this->GOLZ == 'P') {
            $view = "otransaksi_po.index_posting";
        } elseif($this->FLAGZ == 'PC' && $this->GOLZ == 'C') {
            $view = "otransaksi_po.index_posting_pc";
        } else {
            $view = "otransaksi_po.index";
        }
        return view($view)->with([
            'judul' => $this->judul,
            'flagz' => $this->FLAGZ,
            'golz'  => $this->GOLZ,
        ]);
    }

    public function index_otomatis(Request $request)
    {
	    $this->setFlag($request);
        $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
        $tahap = $request->tahapz;
        $TandaiDetail = '';
        $tanda_pootomatis = '';

        if($tahap == 'tahap1'){
            DB::SELECT("UPDATE ppd SET  TANDA_POOTOMATIS1 = '0'  WHERE NO_PO1 = '' AND PER='$periode'");
            $TandaiDetail = DB::table('ppd')->select('NO_BUKTI', 'REC', 'KODES', 'NAMAS', 'QTY', 'TAHAP1', 'TAHAP2', 'TAHAP3')->where('PER', $periode)->where('NO_PO1', '')->where('TANDA_POOTOMATIS1', '0')->orderBy('KODES', 'ASC')->get();
            $tanda_pootomatis = 'TANDA_POOTOMATIS1';
            $no_po = 'NO_PO1';
        }elseif($tahap == 'tahap2'){
            DB::SELECT("UPDATE ppd SET  TANDA_POOTOMATIS2 = '0'  WHERE NO_PO2 = '' AND PER='$periode'");
            $TandaiDetail = DB::table('ppd')->select('NO_BUKTI', 'REC', 'KODES', 'NAMAS', 'QTY', 'TAHAP1', 'TAHAP2', 'TAHAP3')->where('PER', $periode)->where('NO_PO2', '')->where('TANDA_POOTOMATIS2', '0')->orderBy('KODES', 'ASC')->get();
            $tanda_pootomatis = 'TANDA_POOTOMATIS2';
            $no_po = 'NO_PO2';
        }elseif($tahap == 'tahap3'){
            DB::SELECT("UPDATE ppd SET  TANDA_POOTOMATIS3 = '0'  WHERE NO_PO3 = '' AND PER='$periode'");
            $TandaiDetail = DB::table('ppd')->select('NO_BUKTI', 'REC', 'KODES', 'NAMAS', 'QTY', 'TAHAP1', 'TAHAP2', 'TAHAP3')->where('PER', $periode)->where('NO_PO3', '')->where('TANDA_POOTOMATIS3', '0')->orderBy('KODES', 'ASC')->get();
            $tanda_pootomatis = 'TANDA_POOTOMATIS3';
            $no_po = 'NO_PO3';
        }

        $notanda = 1;
        $nomer = 1;
        $kodes = '';
        foreach($TandaiDetail as $item){
            $kodesx = $item->KODES;
            $buktix = $item->NO_BUKTI;
            $recx = $item->REC;

            if($kodes == '' || $kodes == $kodesx){
                $kodes = $kodesx;
                if($nomer <= 16){
                    DB::SELECT("UPDATE ppd SET  $tanda_pootomatis = '$notanda'  WHERE  NO_BUKTI = '$buktix' AND  REC='$recx' AND KODES = '$kodesx' AND PER='$periode'");
                    $nomer++;
                }else{
                    $notanda++;
                    $nomer=1;
                    DB::SELECT("UPDATE ppd SET  $tanda_pootomatis = '$notanda'  WHERE  NO_BUKTI = '$buktix' AND  REC='$recx' AND KODES = '$kodesx' AND PER='$periode'");
                    $nomer++;
                }
            }else{
                $notanda++;
                $kodes = $kodesx;
                $nomer = 1;
                DB::SELECT("UPDATE ppd SET  $tanda_pootomatis = '$notanda'  WHERE  NO_BUKTI = '$buktix' AND  REC='$recx' AND KODES = '$kodesx' AND PER='$periode'");
                $nomer++;
            }
        }

        $Detail = DB::table('ppd')->select('KODES', 'NAMAS', DB::raw($tanda_pootomatis . ' AS TANDA_POOTOMATIS'), DB::raw('SUM(QTY) AS TOTAL_QTY'), DB::raw('SUM(TAHAP1) AS TOTAL_TAHAP1'),
                                            DB::raw('SUM(TAHAP2) AS TOTAL_TAHAP2'), DB::raw('SUM(TAHAP3) AS TOTAL_TAHAP3'))
                                            ->where('PER', $periode)->where($no_po, '')->groupBy(['KODES', $tanda_pootomatis])
                                            ->orderBy('KODES', 'ASC')->orderBy($tanda_pootomatis, 'ASC')->get();
        $data = [
            'detail' => $Detail
        ];

       // ganti 3
        return view('otransaksi_po.index_otomatis',$data)->with(['judul' => $this->judul, 'flagz' => $this->FLAGZ, 'golz' => $this->GOLZ, 'tahapz' => $tahap ]);


    }

    public function detailotomatis(Request $request)
	{
        $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
		$kodes = $request->KODES;
		$nomer = $request->NOMER;
        $tahap = $request->TAHAP;

        $tanda_pootomatis = '';
        if($tahap == 'tahap1'){
            $tanda_pootomatis = 'TANDA_POOTOMATIS1';
        }elseif($tahap == 'tahap2'){
            $tanda_pootomatis = 'TANDA_POOTOMATIS2';
        }elseif($tahap == 'tahap3'){
            $tanda_pootomatis = 'TANDA_POOTOMATIS3';
        }
		$detaild = DB::SELECT("SELECT KODES,NO_BUKTI,KD_BRG,NA_BRG,QTY,TAHAP1,TAHAP2,TAHAP3 from ppd WHERE PER='$periode' AND KODES='$kodes' AND $tanda_pootomatis='$nomer' ORDER BY NO_BUKTI ASC");

        return response()->json($detaild);
	}

	public function browse(Request $request)
    {
        // $golz = $request->GOL;

        $CBG = Auth::user()->CBG;

        //po.GUDANG setelah po.PKP dihapus
        $po = DB::SELECT("SELECT distinct PO.NO_BUKTI , PO.KODES, PO.NAMAS,
	                    PO.ALAMAT, PO.KOTA, PO.PKP, PO.JTEMPO, PO.NOTES from po, pod
                        WHERE PO.NO_BUKTI = POD.NO_BUKTI
                        AND POD.SISA > 0 AND POSTED = 1
                        --   AND POSTED = 1
                        GROUP BY NO_BUKTI ");
        return response()->json($po);
    }

    public function browseuang(Request $request)
    {
        $CBG = Auth::user()->CBG;

		$po = DB::SELECT("SELECT NO_BUKTI,TGL,  KODES, NAMAS, TOTAL,  BAYAR,
                                TOTAL-BAYAR) AS SISA, ALAMAT, KOTA from po
		                WHERE LNS <> 1 AND CBG = '$CBG' ORDER BY NO_BUKTI; ");

        return response()->json($po);
    }


	public function index_posting(Request $request)
    {

        return view('otransaksi_po.post');
    }

	public function browse_pod(Request $request)
    {
            $pod = DB::SELECT("SELECT a.REC, a.KD_BRG, a.NA_BRG, a.SATUAN , a.QTY, a.HARGA, a.KIRIM, a.SISA, a.TOTAL,
                                        a.PPN, a.DPP, a.DISK
                                from pod a
                                where a.NO_BUKTI='".$request->nobukti."' ");




		return response()->json($pod);
	}

	public function browse_detail(Request $request)
    {
		$filterbukti = '';
		if($request->NO_PO)
		{

			$filterbukti = " WHERE a.NO_BUKTI='".$request->NO_PO."' AND a.KD_BHN = b.KD_BHN ";
		}
		$pod = DB::SELECT("SELECT a.REC, a.KD_BHN, a.NA_BHN, a.SATUAN , a.QTY, a.HARGA, a.KIRIM, a.SISA,
                                b.SATUAN AS SATUAN_PO, a.QTY AS QTY_PO, b.KALI AS KALI
                            from pod a, bhn b
                            $filterbukti ORDER BY NO_BUKTI ");


		return response()->json($pod);
	}


    public function browse_detail2(Request $request)
    {
		$filterbukti = '';
		if($request->NO_PO)
		{

			$filterbukti = " WHERE NO_BUKTI='".$request->NO_PO."' AND a.KD_BRG = b.KD_BRG ";
		}
		$pod = DB::SELECT("SELECT a.REC, a.KD_BRG, a.NA_BRG, a.SATUAN , a.QTY, a.HARGA, a.KIRIM, a.SISA,
                                b.SATUAN AS SATUAN_PO, a.QTY AS QTY_PO, b.KALI AS KALI
                            from pod a, brg b
                            $filterbukti ORDER BY NO_BUKTI ");


		return response()->json($pod);
	}
    // ganti 4



    public function getPo(Request $request)
    {
        // ganti 5

       if ($request->session()->has('periode')) {
            $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
        } else {
            $periode = '';
        }

		$this->setFlag($request);
        $FLAGZ = $this->FLAGZ;
        $GOLZ = $this->GOLZ;
        $judul = $this->judul;

        $CBG = Auth::user()->CBG;

        $po = DB::SELECT("SELECT *, POSTED as cek from po  WHERE PER='$periode' and FLAG ='$this->FLAGZ'
                        AND GOL ='$this->GOLZ' AND CBG = '$CBG' ORDER BY NO_BUKTI ");


        // ganti 6

        return Datatables::of($po)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                if (Auth::user()->divisi=="programmer" )
				{
                    //CEK POSTED di index dan edit

                    // url untuk delete di index
                    $url = "'".url("po/delete/" . $row->NO_ID . "/?flagz=" . $row->FLAG . "&golz=" . $row->GOL)."'";
                    // batas

                    $btnEdit =   ($row->POSTED == 1) ? ' onclick= "alert(\'Transaksi ' . $row->NO_BUKTI . ' sudah diposting!\')" href="#" ' : ' href="po/edit/?idx=' . $row->NO_ID . '&tipx=edit&flagz=' . $row->FLAG . '&judul=' . $this->judul . '&golz=' . $row->GOL . '"';
                    $btnDelete = ($row->POSTED == 1) ? ' onclick= "alert(\'Transaksi ' . $row->NO_BUKTI . ' sudah diposting!\')" href="#" ' : ' onclick="deleteRow('.$url.')"';


                    $btnPrivilege =
                        '
                                <a class="dropdown-item" ' . $btnEdit . '>
                                <i class="fas fa-edit"></i>
                                    Edit
                                </a>
                                <a class="dropdown-item btn btn-danger" href="po/cetak/' . $row->NO_ID . '">
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

            ->rawColumns(['action','cek'])
            ->make(true);
    }

    public function getPootomatis(Request $request)
    {
        // ganti 5

       if ($request->session()->has('periode')) {
            $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
        } else {
            $periode = '';
        }

		$this->setFlag($request);
        $FLAGZ = $this->FLAGZ;
        $GOLZ = $this->GOLZ;
        $judul = $this->judul;

        $CBG = Auth::user()->CBG;

        $po = DB::SELECT("SELECT KODES,NAMAS,SUM(TOTAL_QTY) AS TOTAL_QTY from pp  WHERE PER='$periode' GROUP BY KODES ORDER BY KODES ASC");


        // ganti 6

        return Datatables::of($po)->addIndexColumn()->make(true);
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
 //               'NO_PO'       => 'required',
                'TGL'      => 'required'


            ]
        );

        //////     nomer otomatis
		$this->setFlag($request);
        $FLAGZ = $this->FLAGZ;
        $GOLZ = $this->GOLZ;
        $judul = $this->judul;

        $CBG = Auth::user()->CBG;

        $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];

        $bulan    = session()->get('periode')['bulan'];
        $tahun    = substr(session()->get('periode')['tahun'], -2);

        $query = DB::table('po')->select('NO_BUKTI')->where('PER', $periode)->where('FLAG', 'PO')->where('CBG', $CBG)
                ->where('GOL', $this->GOLZ )->orderByDesc('NO_BUKTI')->limit(1)->get();

        if( $GOLZ=='B'){

            if ($query != '[]') {
                $query = substr($query[0]->NO_BUKTI, -4);
                $query = str_pad($query + 1, 4, 0, STR_PAD_LEFT);
                $no_bukti = 'PO' . $this->GOLZ . $CBG . $tahun . $bulan . '-' . $query;
            } else {
                $no_bukti = 'PO' . $this->GOLZ . $CBG . $tahun . $bulan . '-0001';
            }

        } elseif($GOLZ=='J') {

            if ($query != '[]') {
                $query = substr($query[0]->NO_BUKTI, -4);
                $query = str_pad($query + 1, 4, 0, STR_PAD_LEFT);
                $no_bukti = 'PO' . $CBG . $tahun . $bulan . '-' . $query;
            } else {
                $no_bukti = 'PO' . $CBG . $tahun . $bulan . '-0001';
            }

        } elseif($GOLZ=='C') {

            if ($query != '[]') {
                $query = substr($query[0]->NO_BUKTI, -4);
                $query = str_pad($query + 1, 4, 0, STR_PAD_LEFT);
                $no_bukti = 'POC' . $CBG . $tahun . $bulan . '-' . $query;
            } else {
                $no_bukti = 'POC' . $CBG . $tahun . $bulan . '-0001';
            }

        }



        $po = Po::create(
            [
                'NO_BUKTI'         => $no_bukti,
                'TGL'              => date('Y-m-d', strtotime($request['TGL'])),
                'JTEMPO'              => date('Y-m-d', strtotime($request['JTEMPO'])),
                'JTEMPO2'              => date('Y-m-d', strtotime($request['JTEMPO2'])),
                'JTEMPO3'              => date('Y-m-d', strtotime($request['JTEMPO3'])),
                'PER'              => $periode,
				'NO_PP'            => ($request['NO_PP'] == null) ? "" : $request['NO_PP'],
				'NO_SO'            => ($request['NO_SO'] == null) ? "" : $request['NO_SO'],
				'KODES'            => ($request['KODES'] == null) ? "" : $request['KODES'],
                'NAMAS'            => ($request['NAMAS'] == null) ? "" : $request['NAMAS'],
                'ALAMAT'           => ($request['ALAMAT'] == null) ? "" : $request['ALAMAT'],
                'KOTA'             => ($request['KOTA'] == null) ? "" : $request['KOTA'],
                'FLAG'             => 'PO',
                'GOL'              => $GOLZ,
                'CBG'              => $CBG,
                'NOTES'            => ($request['NOTES'] == null) ? "" : $request['NOTES'],
                // 'GUDANG'            => ($request['GUDANG'] == null) ? "" : $request['GUDANG'],
                'TOTAL_QTY'        => (float) str_replace(',', '', $request['TTOTAL_QTY']),
                'TOTAL'            => (float) str_replace(',', '', $request['TTOTAL']),
                'TDISK'            => (float) str_replace(',', '', $request['TDISK']),
                'TDPP'            => (float) str_replace(',', '', $request['TDPP']),
                'TPPN'            => (float) str_replace(',', '', $request['TPPN']),
                'NETT'            => (float) str_replace(',', '', $request['NETT']),
                'HARI'            => (float) str_replace(',', '', $request['HARI']),
                'PKP'            => (float) str_replace(',', '', $request['PKP']),
                'USRNM'            => Auth::user()->username,
                'TG_SMP'           => Carbon::now(),
				'created_by'       => Auth::user()->username,
            ]
        );


		$REC        = $request->input('REC');
		$KD_BRG     = $request->input('KD_BRG');
        $NA_BRG     = $request->input('NA_BRG');
        $SATUAN     = $request->input('SATUAN');
        $QTY        = $request->input('QTY');
        $HARGA      = $request->input('HARGA');
        $TOTAL      = $request->input('TOTAL');
        $KET        = $request->input('KET');
        $PPNX      = $request->input('PPNX');
        $DPP      = $request->input('DPP');
        $DISK      = $request->input('DISK');

        // Check jika value detail ada/tidak
        if ($REC) {
            foreach ($REC as $key => $value) {
                // Declare new data di Model
                $detail    = new PoDetail;

                // Insert ke Database
                $detail->NO_BUKTI    = $no_bukti;
                $detail->REC         = $REC[$key];
                $detail->PER         = $periode;
                $detail->FLAG        = $FLAGZ;
                $detail->GOL 	     = $GOLZ;
                $detail->KD_BRG      = ($KD_BRG[$key] == null) ? "" :  $KD_BRG[$key];
                $detail->NA_BRG      = ($NA_BRG[$key] == null) ? "" :  $NA_BRG[$key];
                $detail->SATUAN      = ($SATUAN[$key] == null) ? "" :  $SATUAN[$key];
                $detail->QTY         = (float) str_replace(',', '', $QTY[$key]);
                $detail->HARGA       = (float) str_replace(',', '', $HARGA[$key]);
                $detail->TOTAL       = (float) str_replace(',', '', $TOTAL[$key]);
                $detail->SISA       = (float) str_replace(',', '', $QTY[$key]);
                $detail->PPN       = (float) str_replace(',', '', $PPNX[$key]);
                $detail->DPP       = (float) str_replace(',', '', $DPP[$key]);
                $detail->DISK       = (float) str_replace(',', '', $DISK[$key]);

				$detail->KET         = ($KET[$key] == null) ? "" :  $KET[$key];
                $detail->save();
            }
        }

		$no_buktix = $no_bukti;

		$po = Po::where('NO_BUKTI', $no_buktix )->first();

        DB::SELECT("UPDATE po, sup
                    SET po.NAMAS = sup.NAMAS,
                        po.ALAMAT = sup.ALAMAT,
                        po.KOTA = sup.KOTA,
                        po.PKP = CASE
                                    WHEN sup.GOLONGAN = 'P0' THEN 0
                                    ELSE 1
                                END,
                        po.HARI = sup.HARI
                    WHERE po.KODES = sup.KODES
                    AND po.NO_BUKTI = '$no_buktix';");

        DB::SELECT("UPDATE po,  pod
                            SET  pod.ID =  po.NO_ID  WHERE  po.NO_BUKTI =  pod.NO_BUKTI
							AND  po.NO_BUKTI='$no_buktix';");



        // return redirect('/po/edit/?idx=' . $po->NO_ID . '&tipx=edit&flagz=' . $this->FLAGZ . '&golz=' . $this->GOLZ . '&judul=' . $this->judul . '');
        return redirect('/po?flagz='.$FLAGZ.'&golz='.$GOLZ)->with(['judul' => $judul, 'golz' => $GOLZ, 'flagz' => $FLAGZ ]);

    }

    public function storeotomatis(Request $request)
    {
        // $mentahanKodes = $request->input('kodes', []);
        // Hapus karakter terakhir dari setiap elemen dalam array
        // $selectedKodes = array_map(fn($kode) => substr($kode, 0, -1), $mentahanKodes);

        $this->setFlag($request);
        $FLAGZ = $this->FLAGZ;
        $GOLZ = $this->GOLZ;
        $judul = $this->judul;
        $CBG = Auth::user()->CBG;
        $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
        $bulan    = session()->get('periode')['bulan'];
        $tahun    = substr(session()->get('periode')['tahun'], -2);
        $selectedKodes = $request->input('kodes', []);
        $tkk = $request->input('tkk');
        $tahap = $request->input('tahap');

        $tanda_pootomatis = '';
        $no_po = '';
        if($tahap == 'tahap1'){
            $tanda_pootomatis = 'TANDA_POOTOMATIS1';
            $no_po = 'NO_PO1';
        }elseif($tahap == 'tahap2'){
            $tanda_pootomatis = 'TANDA_POOTOMATIS2';
            $no_po = 'NO_PO2';
        }elseif($tahap == 'tahap3'){
            $tanda_pootomatis = 'TANDA_POOTOMATIS3';
            $no_po = 'NO_PO3';
        }


        if (empty($selectedKodes)) {
            $header = DB::table('ppd')->select('KODES', 'NAMAS', 'ALAMAT', 'PKP','KOTA', DB::raw($tanda_pootomatis . ' AS TANDA_POOTOMATIS'), DB::raw('SUM('.$tahap.') AS TOTAL_QTY'))->where('PER', $periode)->groupBy('KODES',$tanda_pootomatis)
                                        ->orderBy('KODES', 'ASC')->orderBy($tanda_pootomatis, 'ASC')->get();
        }else{
            // Jika tidak ada KODES yang dipilih, hentikan eksekusi
            // $header = DB::table('ppd')->select('KODES', 'NAMAS', 'ALAMAT', 'PKP', 'KOTA', DB::raw('SUM(QTY) AS TOTAL_QTY'))->where('PER', $periode)->whereIn('KODES', $selectedKodes)
            //                         ->groupBy('KODES','TANDA_POOTOMATIS') ->orderBy('KODES', 'ASC')->get();
            $header = DB::table('ppd')->select('KODES', 'NAMAS', 'ALAMAT', 'PKP', 'KOTA', DB::raw($tanda_pootomatis . ' AS TANDA_POOTOMATIS'), DB::raw('SUM('.$tahap.') AS TOTAL_QTY'))->where('PER', $periode)
                                    ->whereRaw("CONCAT(KODES, $tanda_pootomatis) IN (?)", [implode("','", $selectedKodes)])
                                    ->groupBy('KODES',$tanda_pootomatis) ->orderBy('KODES', 'ASC')->orderBy($tanda_pootomatis, 'ASC')->get();
        }

        // ddd($header->PKP);
        foreach($header as $poheader){
            $query = DB::table('po')->select('NO_BUKTI')->where('PER', $periode)->where('FLAG', 'PO')->where('CBG', $CBG)
                    ->where('GOL', $this->GOLZ )->orderByDesc('NO_BUKTI')->limit(1)->get();

            if( $GOLZ=='B'){

                if ($query != '[]') {
                    $query = substr($query[0]->NO_BUKTI, -4);
                    $query = str_pad($query + 1, 4, 0, STR_PAD_LEFT);
                    $no_bukti = 'PO' . $this->GOLZ . $CBG . $tahun . $bulan . '-' . $query;
                } else {
                    $no_bukti = 'PO' . $this->GOLZ . $CBG . $tahun . $bulan . '-0001';
                }

            } elseif($GOLZ=='J') {

                if ($query != '[]') {
                    $query = substr($query[0]->NO_BUKTI, -4);
                    $query = str_pad($query + 1, 4, 0, STR_PAD_LEFT);
                    $no_bukti = 'PO' . $CBG . $tahun . $bulan . '-' . $query;
                } else {
                    $no_bukti = 'PO' . $CBG . $tahun . $bulan . '-0001';
                }

            } elseif($GOLZ=='C') {

                if ($query != '[]') {
                    $query = substr($query[0]->NO_BUKTI, -4);
                    $query = str_pad($query + 1, 4, 0, STR_PAD_LEFT);
                    $no_bukti = 'POC' . $CBG . $tahun . $bulan . '-' . $query;
                } else {
                    $no_bukti = 'POC' . $CBG . $tahun . $bulan . '-0001';
                }

            }

            $po = Po::create(
                [
                    'NO_BUKTI'         => $no_bukti,
                    'TGL'              => date('Y-m-d'),
                    'JTEMPO'           => date('Y-m-d', strtotime($tkk)),
                    'PER'              => $periode,
                    // 'NO_PP'            => ($request['NO_PP'] == null) ? "" : $request['NO_PP'],
                    // 'NO_SO'            => ($request['NO_SO'] == null) ? "" : $request['NO_SO'],
                    'KODES'            => $poheader->KODES,
                    'PKP'              => $poheader->PKP,
                    'NAMAS'            => $poheader->NAMAS,
                    'ALAMAT'           => $poheader->ALAMAT,
                    'KOTA'             => $poheader->KOTA,
                    'FLAG'             => 'PO',
                    'GOL'              => $GOLZ,
                    'CBG'              => $CBG,
                    // 'NOTES'            => ($request['NOTES'] == null) ? "" : $request['NOTES'],
                    // 'GUDANG'            => ($request['GUDANG'] == null) ? "" : $request['GUDANG'],
                    'TOTAL_QTY'        => $poheader->TOTAL_QTY,
                    // 'TOTAL'            => (float) str_replace(',', '', $request['TTOTAL']),
                    // 'TDISK'            => (float) str_replace(',', '', $request['TDISK']),
                    // 'TDPP'            => (float) str_replace(',', '', $request['TDPP']),
                    // 'TPPN'            => (float) str_replace(',', '', $request['TPPN']),
                    // 'NETT'            => (float) str_replace(',', '', $request['NETT']),
                    // 'HARI'            => (float) str_replace(',', '', $request['HARI']),
                    'USRNM'            => Auth::user()->username,
                    'TG_SMP'           => Carbon::now(),
                    'created_by'       => Auth::user()->username,
                ]
            );

            $detail = DB::table('ppd')->select('KODES','REC','NO_BUKTI', 'KD_BRG', 'NA_BRG', ''.$tahap.' AS QTY','SATUAN','HARGA','TOTAL','SISA','PPN','DPP','DISK',DB::raw($tanda_pootomatis . ' AS TANDA_POOTOMATIS'))
                                    ->where('PER', $periode)->where('KODES', $poheader->KODES)->where($tanda_pootomatis, $poheader->TANDA_POOTOMATIS)
                                    ->orderBy('KODES', 'ASC')->get();


            $rec = 1;
            foreach ($detail as $podetail) {
                // Declare new data di Model
                $detail    = new PoDetail;

                // Insert ke Database
                $detail->NO_BUKTI    = $no_bukti;
                $detail->REC         = $rec;
                $detail->PER         = $periode;
                $detail->FLAG        = $FLAGZ;
                $detail->GOL 	     = $GOLZ;
                $detail->KD_BRG      = $podetail->KD_BRG;
                $detail->NA_BRG      = $podetail->NA_BRG;
                $detail->SATUAN      = $podetail->SATUAN;
                $detail->NO_PP      = $podetail->NO_BUKTI;
                $detail->QTY         = (float) str_replace(',', '', $podetail->QTY);
                $detail->HARGA       = (float) str_replace(',', '', $podetail->HARGA);
                $detail->TOTAL       = (float) str_replace(',', '', $podetail->TOTAL);
                $detail->SISA       = (float) str_replace(',', '', $podetail->SISA);
                $detail->PPN       = (float) str_replace(',', '', $podetail->PPN);
                $detail->DPP       = (float) str_replace(',', '', $podetail->DPP);
                $detail->DISK       = (float) str_replace(',', '', $podetail->DISK);

                // $detail->KET         = ($KET[$key] == null) ? "" :  $KET[$key];
                $detail->save();

                $rec++;
                DB::SELECT("UPDATE ppd SET  $no_po = '$no_bukti'  WHERE  KD_BRG = $podetail->KD_BRG
                            AND NO_BUKTI='$podetail->NO_BUKTI' AND REC = '$podetail->REC' AND $tanda_pootomatis = '$podetail->TANDA_POOTOMATIS';");
            }

            $no_buktix = $no_bukti;
            $po = Po::where('NO_BUKTI', $no_buktix )->first();
            DB::SELECT("UPDATE PO, SUP
                        SET PO.NAMAS = SUP.NAMAS, PO.ALAMAT = SUP.ALAMAT, PO.KOTA = SUP.KOTA, PO.PKP=if((SUP.GOLONGAN='P0'),'0','1'), PO.HARI = SUP.HARI  WHERE PO.KODES = SUP.KODES
                        AND PO.NO_BUKTI='$no_buktix';");
            DB::SELECT("UPDATE po,  pod
                                SET  pod.ID =  po.NO_ID  WHERE  po.NO_BUKTI =  pod.NO_BUKTI
                                AND  po.NO_BUKTI='$no_buktix';");
        }

        // return redirect('/po/edit/?idx=' . $po->NO_ID . '&tipx=edit&flagz=' . $this->FLAGZ . '&golz=' . $this->GOLZ . '&judul=' . $this->judul . '');
        // return redirect('/po?flagz='.$FLAGZ.'&golz='.$GOLZ)->with(['judul' => $judul, 'golz' => $GOLZ, 'flagz' => $FLAGZ ]);

    }

   public function edit( Request $request , Po $po)
    {


		$per = session()->get('periode')['bulan'] . '/' . session()->get('periode')['tahun'];


        // $cekperid = DB::SELECT("SELECT POSTED from perid WHERE PERIO='$per'");
        // if ($cekperid[0]->POSTED==1)
        // {
        //     return redirect('/po')
		// 	       ->with('status', 'Maaf Periode sudah ditutup!')
        //            ->with(['judul' => $judul, 'flagz' => $FLAGZ]);
        // }

		$this->setFlag($request);

        $tipx = $request->tipx;

		$idx = $request->idx;

        $CBG = Auth::user()->CBG;

		if ( $idx =='0' && $tipx=='undo'  )
	    {
			$tipx ='top';

		   }



		if ($tipx=='search') {


    	   $buktix = $request->buktix;

		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from po
		                 where PER ='$per' and FLAG ='$this->FLAGZ'
                         and GOL ='$this->GOLZ'
                         AND CBG = '$CBG'
						 and NO_BUKTI = '$buktix'
		                 ORDER BY NO_BUKTI ASC  LIMIT 1" );


			if(!empty($bingco))
			{
				$idx = $bingco[0]->NO_ID;
			  }
			else
			{
				$idx = 0;
			  }


		}

		if ($tipx=='top') {


		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from po
		                 where PER ='$per'
						 and FLAG ='$this->FLAGZ'
                         and GOL ='$this->GOLZ'
                         AND CBG = '$CBG'
		                 ORDER BY NO_BUKTI ASC  LIMIT 1" );


			if(!empty($bingco))
			{
				$idx = $bingco[0]->NO_ID;
			  }
			else
			{
				$idx = 0;
			  }


		}


		if ($tipx=='prev' ) {

    	   $buktix = $request->buktix;

		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from po
		             where PER ='$per'
					 and FLAG ='$this->FLAGZ'
                     and GOL ='$this->GOLZ'
                     AND CBG = '$CBG'
                     and NO_BUKTI <
					 '$buktix' ORDER BY NO_BUKTI DESC LIMIT 1" );


			if(!empty($bingco))
			{
				$idx = $bingco[0]->NO_ID;
			  }
			else
			{
				$idx = $idx;
			  }

		}


		if ($tipx=='next' ) {


      	   $buktix = $request->buktix;

		   $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from po
		             where PER ='$per'
					 and FLAG ='$this->FLAGZ'
                     and GOL ='$this->GOLZ'
                     AND CBG = '$CBG'
                     and NO_BUKTI >
					 '$buktix' ORDER BY NO_BUKTI ASC LIMIT 1" );

			if(!empty($bingco))
			{
				$idx = $bingco[0]->NO_ID;
			  }
			else
			{
				$idx = $idx;
			  }


		}

		if ($tipx=='bottom') {

    		$bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from po
						where PER ='$per'
						and FLAG ='$this->FLAGZ'
                        and GOL ='$this->GOLZ'
                        AND CBG = '$CBG'
		                ORDER BY NO_BUKTI DESC  LIMIT 1" );

			if(!empty($bingco))
			{
				$idx = $bingco[0]->NO_ID;
			  }
			else
			{
				$idx = 0;
			  }


		}


		if ( $tipx=='undo' || $tipx=='search' )
	    {

			$tipx ='edit';

		   }



       	if ( $idx != 0 )
		{
			$po = Po::where('NO_ID', $idx )->first();
	     }
		 else
		 {
				$po = new Po;
                $po->TGL = Carbon::now();
                $po->JTEMPO = Carbon::now();
                $po->JTEMPO2 = Carbon::now();
                $po->JTEMPO3 = Carbon::now();


		 }

        $no_bukti = $po->NO_BUKTI;
        $poDetail = DB::table('pod')->where('NO_BUKTI', $no_bukti)->orderBy('REC')->get();

		$data = [
            'header'        => $po,
			'detail'        => $poDetail

        ];

 		$sup = DB::SELECT("SELECT KODES, CONCAT(NAMAS,'-',KOTA) AS NAMAS FROM SUP
		                 ORDER BY NAMAS ASC" );


         return view('otransaksi_po.edit', $data)->with(['sup' => $sup])
		 ->with(['tipx' => $tipx, 'idx' => $idx, 'flagz' => $this->FLAGZ, 'golz' => $this->GOLZ, 'judul'=> $this->judul ]);


    }

  /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    // ganti 18

    public function update(Request $request, Po $po)
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


        $po->update(
            [
                'TGL'              => date('Y-m-d', strtotime($request['TGL'])),
                'JTEMPO'              => date('Y-m-d', strtotime($request['JTEMPO'])),
                'JTEMPO2'              => date('Y-m-d', strtotime($request['JTEMPO2'])),
                'JTEMPO3'              => date('Y-m-d', strtotime($request['JTEMPO3'])),
                'NO_PP'            => ($request['NO_PP'] == null) ? "" : $request['NO_PP'],
                'NO_SO'            => ($request['NO_SO'] == null) ? "" : $request['NO_SO'],
                'KODES'            => ($request['KODES'] == null) ? "" : $request['KODES'],
                'NAMAS'            => ($request['NAMAS'] == null) ? "" : $request['NAMAS'],
                'ALAMAT'           => ($request['ALAMAT'] == null) ? "" : $request['ALAMAT'],
                'KOTA'             => ($request['KOTA'] == null) ? "" : $request['KOTA'],
                'NOTES'            => ($request['NOTES'] == null) ? "" : $request['NOTES'],
                // 'GUDANG'            => ($request['GUDANG'] == null) ? "" : $request['GUDANG'],
                'TOTAL_QTY'        => (float) str_replace(',', '', $request['TTOTAL_QTY']),
                'TOTAL'            => (float) str_replace(',', '', $request['TTOTAL']),
                'TDISK'            => (float) str_replace(',', '', $request['TDISK']),
                'TDPP'            => (float) str_replace(',', '', $request['TDPP']),
                'TPPN'            => (float) str_replace(',', '', $request['TPPN']),
                'HARI'             => (float) str_replace(',', '', $request['HARI']),
                'PKP'             => (float) str_replace(',', '', $request['PKP']),
				'USRNM'            => Auth::user()->username,
                'TG_SMP'           => Carbon::now(),
				'updated_by'       => Auth::user()->username,
                'FLAG'             => 'PO',
                'GOL'              => $GOLZ,
                'CBG'              => $CBG,
            ]
        );

		$no_buktix = $po->NO_BUKTI;

        // Update Detail
        $length = sizeof($request->input('REC'));
        $NO_ID  = $request->input('NO_ID');

        $REC    = $request->input('REC');

        $KD_BRG = $request->input('KD_BRG');
        $NA_BRG = $request->input('NA_BRG');
        $SATUAN = $request->input('SATUAN');
        $QTY    = $request->input('QTY');
        $HARGA    = $request->input('HARGA');
        $PPNX      = $request->input('PPNX');
        $DPP      = $request->input('DPP');
        $DISK      = $request->input('DISK');
        $TOTAL    = $request->input('TOTAL');
        $KET = $request->input('KET');

        $query = DB::table('pod')->where('NO_BUKTI', $request->NO_BUKTI)->whereNotIn('NO_ID',  $NO_ID)->delete();

        // Update / Insert
        for ($i = 0; $i < $length; $i++) {
            // Insert jika NO_ID baru
            if ($NO_ID[$i] == 'new') {
                $insert = PoDetail::create(
                    [
                        'NO_BUKTI'   => $request->NO_BUKTI,
                        'REC'        => $REC[$i],
                        'PER'        => $periode,
                        'FLAG'       => $this->FLAGZ,
                        'GOL'        => $this->GOLZ,
                        'KD_BRG'     => ($KD_BRG[$i] == null) ? "" :  $KD_BRG[$i],
                        'NA_BRG'     => ($NA_BRG[$i] == null) ? "" :  $NA_BRG[$i],
                        'SATUAN'     => ($SATUAN[$i] == null) ? "" :  $SATUAN[$i],
                        'QTY'        => (float) str_replace(',', '', $QTY[$i]),
                        'HARGA'      => (float) str_replace(',', '', $HARGA[$i]),
                        'TOTAL'      => (float) str_replace(',', '', $TOTAL[$i]),
                        'SISA'      => (float) str_replace(',', '', $QTY[$i]),
                        'PPN'      => (float) str_replace(',', '', $PPNX[$i]),
                        'DPP'      => (float) str_replace(',', '', $DPP[$i]),
                        'DISK'      => (float) str_replace(',', '', $DISK[$i]),

                        'KET'        => ($KET[$i] == null) ? "" :  $KET[$i],

                    ]
                );
            } else {
                // Update jika NO_ID sudah ada
                $upsert = PoDetail::updateOrCreate(
                    [
                        'NO_BUKTI'  => $request->NO_BUKTI,
                        'NO_ID'     => (int) str_replace(',', '', $NO_ID[$i])
                    ],

                    [
                        'REC'        => $REC[$i],

                        'KD_BRG'     => ($KD_BRG[$i] == null) ? "" :  $KD_BRG[$i],
                        'NA_BRG'     => ($NA_BRG[$i] == null) ? "" :  $NA_BRG[$i],
                        'SATUAN'     => ($SATUAN[$i] == null) ? "" :  $SATUAN[$i],
                        'QTY'        => (float) str_replace(',', '', $QTY[$i]),
                        'HARGA'      => (float) str_replace(',', '', $HARGA[$i]),
                        'TOTAL'      => (float) str_replace(',', '', $TOTAL[$i]),
                        'SISA'        => (float) str_replace(',', '', $QTY[$i]),
                        'PPN'      => (float) str_replace(',', '', $PPNX[$i]),
                        'DPP'      => (float) str_replace(',', '', $DPP[$i]),
                        'DISK'      => (float) str_replace(',', '', $DISK[$i]),
                        'FLAG'       => $this->FLAGZ,
                        'GOL'        => $this->GOLZ,
                        'PER'        => $periode,
                        'KET'        => ($KET[$i] == null) ? "" :  $KET[$i],
                    ]
                );
            }
        }

 		$po = Po::where('NO_BUKTI', $no_buktix )->first();

        $no_bukti = $po->NO_BUKTI;

        DB::SELECT("UPDATE po, sup
                    SET po.NAMAS = sup.NAMAS,
                        po.ALAMAT = sup.ALAMAT,
                        po.KOTA = sup.KOTA,
                        po.PKP = CASE
                                    WHEN sup.GOLONGAN = 'P0' THEN 0
                                    ELSE 1
                                END,
                        po.HARI = sup.HARI
                    WHERE po.KODES = sup.KODES
                    AND po.NO_BUKTI = '$no_buktix';");


        DB::SELECT("UPDATE po,  pod
                    SET  pod.ID =  po.NO_ID  WHERE  po.NO_BUKTI =  pod.NO_BUKTI
                    AND  po.NO_BUKTI='$no_bukti';");

        // return redirect('/po/edit/?idx=' . $po->NO_ID . '&tipx=edit&flagz=' . $this->FLAGZ . '&golz=' . $this->GOLZ . '&judul=' . $this->judul . '');
        return redirect('/po?flagz='.$FLAGZ.'&golz='.$GOLZ)->with(['judul' => $judul, 'golz' => $GOLZ, 'flagz' => $FLAGZ ]);


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    // ganti 22

    public function destroy(Request $request, Po $po)
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
        if ($cekperid[0]->POSTED==1)
        {
            return redirect()->route('po')
                ->with('status', 'Maaf Periode sudah ditutup!')
                ->with(['judul' => $this->judul, 'flagz' => $this->FLAGZ, 'golz' => $this->GOLZ]);
        }

        $deletePo = Po::find($po->NO_ID);

        $deletePo->delete();
        // return redirect('/po?flagz=' . $FLAGZ . '&golz=J')
        return redirect('/po?flagz='. $FLAGZ.'&golz='.$GOLZ )
        ->with(['judul' => $judul, 'flagz' => $this->FLAGZ, 'golz' => $this->GOLZ])
        ->with('statusHapus', 'Data ' . $po->NO_BUKTI . ' berhasil dihapus');





    }

    public function cetak(Po $po)
    {
        $no_po = $po->NO_BUKTI;

        $file     = 'poc';
        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

        //po.GUDANG setelah po.NETT dihapus
        $query = DB::SELECT("SELECT po.NO_BUKTI, po.TGL, po.KODES, po.NAMAS, po.TOTAL_QTY, po.NOTES, po.ALAMAT,
                                    po.KOTA, pod.KD_BRG, pod.NA_BRG, pod.SATUAN, pod.QTY,
                                    pod.HARGA, pod.TOTAL, pod.KET, po.TPPN, po.NETT,
                                    po.JTEMPO, po.TDPP, po.TDISK, pod.DISK
                            FROM po, pod
                            WHERE po.NO_BUKTI='$no_po' AND po.NO_BUKTI = pod.NO_BUKTI
                            ;
		");


        $data = [];

        foreach ($query as $key => $value) {
            array_push($data, array(
                'NO_BUKTI' => $query[$key]->NO_BUKTI,
                'TGL'      => $query[$key]->TGL,
                'JTEMPO'      => $query[$key]->JTEMPO,
                'KODES'    => $query[$key]->KODES,
                'NAMAS'    => $query[$key]->NAMAS,
                'ALAMAT'    => $query[$key]->ALAMAT,
                'KOTA'    => $query[$key]->KOTA,
                'KG'       => $query[$key]->KG,
                'HARGA'    => $query[$key]->HARGA,
                'TOTAL'    => $query[$key]->TOTAL,
                'BAYAR'    => $query[$key]->BAYAR,
                'NOTES'    => $query[$key]->NOTES,
                // 'KD_BRG'    => "`".strval($query[$key]->KD_BRG),
                'KD_BRG'    => $query[$key]->KD_BRG,
                'NA_BRG'    => $query[$key]->NA_BRG,
                'SATUAN'    => $query[$key]->SATUAN,
                'QTY'    => $query[$key]->QTY,
                'PPN'    => $query[$key]->TPPN,
                'NETT'    => $query[$key]->NETT,
                'TDPP'    => $query[$key]->TDPP,
                'TDISK'    => $query[$key]->TDISK,
                'DISK'    => $query[$key]->DISK,
                'KET'    => $query[$key]->KET,
                // 'GUDANG'    => $query[$key]->GUDANG
            ));
        }

        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I");

        DB::SELECT("UPDATE PO SET POSTED = 1 WHERE PO.NO_BUKTI='$no_po';");

    }



	 public function posting(Request $request)
    {

        $CEK = $request->input('cek');
        $NO_BUKTI = $request->input('NO_BUKTI');

        $usrnmx = Auth::user()->username;

        $hasil = "";

        if ($CEK) {
            foreach ($CEK as $key => $value)
			{

                    //$STA = $request->input('STA');

					$periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
					$bulan    = session()->get('periode')['bulan'];
					$tahun    = substr(session()->get('periode')['tahun'], -2);

			   $NO_BUKTIXZ  = $NO_BUKTI[$key];


                    DB::SELECT("UPDATE PO SET POSTED = 1 WHERE PO.NO_BUKTI='$NO_BUKTIXZ'");

			}
		}
		else
		{
			$hasil = $hasil ."Tidak ada PO yang dipilih! ; ";
		}

					if($hasil!='')
					{
						return redirect('/po/index-posting')->with('status', 'Proses Posting PO ..')->with('gagal', $hasil);
					}
					else
					{
						return redirect('/po/index-posting')->with('status', 'Posting Posting PO selesai..');
					}







    }


	public function jtempo ( Request $request)
    {
		$tgl = $request->input('TGL');
		$hari = substr($tgl,0,2);
		$bulan = substr($tgl,3,2);
		$tahun = substr($tgl,6,4);
		$harix = $request->HARI;

		$datex = Carbon::createFromDate($tahun, $bulan, $hari );

        $datex ->addDays($harix);

        $datey = $datex->format('d-m-Y');
		return  $datey;


	}

	public function getDetailPo(){

        $no_bukti = $_GET['no_bukti'];
        $result = DB::table('pod')->where('NO_BUKTI', $no_bukti)->get();

        return response()->json($result);;
    }

    public function posting_po(Request $request)
    {
        if (! $request->isMethod('post')) {
            return response()->json(['error' => 'Method Not Allowed'], 405);
        }

        $data = $request->input('posted');

        if (! $data) {
            return response()->json(['error' => 'Tidak ada data yang dikirim'], 400);
        }

        foreach ($data as $id => $posted) {
            DB::table('po')->where('NO_ID', $id)->update(['POSTED' => $posted]);
        }

        return response()->json(['message' => 'Status berhasil diperbarui']);
    }

    public function posting_pc(Request $request)
    {
        if (! $request->isMethod('post')) {
            return response()->json(['error' => 'Method Not Allowed'], 405);
        }

        $data = $request->input('posted');

        if (! $data) {
            return response()->json(['error' => 'Tidak ada data yang dikirim'], 400);
        }

        foreach ($data as $id => $posted) {
            DB::table('po')->where('NO_ID', $id)->update(['POSTED' => $posted]);
        }

        return response()->json(['message' => 'Status berhasil diperbarui']);
    }

}
