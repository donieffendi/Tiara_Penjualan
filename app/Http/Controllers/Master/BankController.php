<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
// ganti 1

use App\Models\FTransaksi\Bank;
use App\Models\FTransaksi\BankDetail;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;

// ganti 2
class BankController extends Controller
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
        if ($request->flagz == 'BBK') {
            $this->judul = "Journal Bank Keluar";
        } else if ($request->flagz == 'BBM') {
            $this->judul = "Journal Bank Masuk";
        }

        $this->FLAGZ = $request->flagz;
    }

    public function index(Request $request)
    {

        // ganti 3
        $this->setFlag($request);
        // ganti 3
        return view('master_bank.index')->with(['judul' => $this->judul, 'flagz' => $this->FLAGZ]);
    }

    // ganti 4

    public function getBank(Request $request)
    {
        // ganti 5

        if ($request->session()->has('periode')) {
            $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
        } else {
            $periode = '';
        }

        $CBG = Auth::user()->CBG;


        $this->setFlag($request);

        $bank = DB::SELECT("SELECT * from bank  where  PER ='$periode' and TYPE ='$this->FLAGZ' AND CBG='$CBG' ORDER BY NO_BUKTI ");

        // ganti 6

        return Datatables::of($bank)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                if (Auth::user()->divisi == "programmer" || Auth::user()->divisi == "owner" || Auth::user()->divisi == "assistant" || Auth::user()->divisi == "accounting") {

                    // url untuk delete di index
                    $url = "'" . url("bank/delete/" . $row->NO_ID . "/?flagz=" . $row->FLAG) . "'";
                    // batas

                    $btnPrivilege =


                        $btnEdit =   ($row->POSTED == 1) ? ' onclick= "alert(\'Transaksi ' . $row->NO_BUKTI . ' sudah diposting!\')" href="#" ' : ' href="bank/edit/?idx=' . $row->NO_ID . '&tipx=edit&flagz=' . $row->TYPE . '&judul=' . $this->judul . '"';
                    $btnDelete = ($row->POSTED == 1) ? ' onclick= "alert(\'Transaksi ' . $row->NO_BUKTI . ' sudah diposting!\')" href="#" ' : ' onclick="deleteRow(' . $url . ')" ';


                    $btnPrivilege =
                        '
                                <a class="dropdown-item" ' . $btnEdit . '>
                                <i class="fas fa-edit"></i>
                                    Edit
                                </a>
                                <a class="dropdown-item btn btn-danger" href="bank/cetak/' . $row->NO_ID . '">
                                    <i class="fa fa-trash" aria-hidden="true"></i>
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

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */


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
                'NO_BUKTI'       => 'required',
                'TGL'      => 'required',
                'BACNO'       => 'required'

            ]
        );


        $this->setFlag($request);
        $FLAGZ = $this->FLAGZ;
        $judul = $this->judul;

        $CBG = Auth::user()->CBG;

        //////     nomer otomatis

        $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];

        $bulan    = session()->get('periode')['bulan'];
        $tahun    = substr(session()->get('periode')['tahun'], -2);
        $query = DB::table('bank')->select('NO_BUKTI')->where('PER', $periode)->where('TYPE', $this->FLAGZ)->where('CBG', $CBG)->orderByDesc('NO_BUKTI')->limit(1)->get();

        if ($query != '[]') {
            $query = substr($query[0]->NO_BUKTI, -4);
            $query = str_pad($query + 1, 4, 0, STR_PAD_LEFT);
            $no_bukti = $this->FLAGZ . $CBG . $tahun . $bulan . '-' . $query;
        } else {
            $no_bukti = $this->FLAGZ . $CBG . $tahun . $bulan . '-0001';
        }



        // Insert Header

        // ganti 10

        $bank = Bank::create(
            [
                'NO_BUKTI'         => $no_bukti,
                'TGL'              => date('Y-m-d', strtotime($request['TGL'])),
                'PER'              => $periode,
                'BACNO'            => ($request['BACNO'] == null) ? "" : $request['BACNO'],
                'BNAMA'            => ($request['BNAMA'] == null) ? "" : $request['BNAMA'],
                'BG'               => ($request['BG'] == null) ? "" : $request['BG'],
                'JTEMPO'           => date('Y-m-d', strtotime($request['JTEMPO'])),
                'KET'              => ($request['KET'] == null) ? "" : $request['KET'],
                'FLAG'             => 'B',
                'TYPE'             => $FLAGZ,
                'JUMLAH'           => (float) str_replace(',', '', $request['TJUMLAH']),
                'USRNM'            => Auth::user()->username,
                'created_by'       => Auth::user()->username,
                'CBG'              => $CBG,
                'TG_SMP'           => Carbon::now()
            ]
        );

        // Insert Detail
        $CODE   = $request->input('CODE');
        $NAME   = $request->input('NAME');
        $BANK_CODE = $request->input('BANK_CODE');


        //  ganti 11
        $variablell = DB::select('call bankins(?)', array($no_bukti));

        $no_buktix = $no_bukti;

        $bank = Bank::where('NO_BUKTI', $no_buktix)->first();

        DB::SELECT("UPDATE BANK, ACCOUNT
                            SET BANK.BNAMA = ACCOUNT.NAMA  WHERE BANK.BACNO = ACCOUNT.ACNO
							AND BANK.NO_BUKTI='$no_buktix';");

        DB::SELECT("UPDATE bank, hut
                    SET bank.NO_BUKTI = hut.NO_KASIR WHERE bank.NO_BUKTI='$no_buktix';");

        DB::SELECT("UPDATE BANK, BANKD
                            SET BANKD.ID = BANK.NO_ID  WHERE BANK.NO_BUKTI = BANKD.NO_BUKTI
							AND BANK.NO_BUKTI='$no_buktix';");

        //return redirect('/bank/edit/?idx=' . $bank->NO_ID . '&tipx=edit&flagz=' . $this->FLAGZ . '&judul=' . $this->judul . '');
        return redirect('/bank?flagz=' . $FLAGZ)->with(['judul' => $judul, 'flagz' => $FLAGZ]);
    }


    // ganti 15

    public function edit(Request $request, Bank $bank)
    {

        $per = session()->get('periode')['bulan'] . '/' . session()->get('periode')['tahun'];
        $cekperid = DB::SELECT("SELECT POSTED from perid WHERE PERIO='$per'");
        if ($cekperid[0]->POSTED == 1) {
            return redirect('/bank')->with('status', 'Maaf Periode sudah ditutup!');
        }

        $this->setFlag($request);

        $tipx = $request->tipx;

        $idx = $request->idx;

        $CBG = Auth::user()->CBG;

        if ($idx == '0' && $tipx == 'undo') {
            $tipx = 'top';
        }



        if ($tipx == 'top') {


            $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from BANK
		                 where PER ='$per' and TYPE ='$this->FLAGZ'
                         AND CGB = '$CBG'
		                 ORDER BY NO_BUKTI ASC  LIMIT 1");



            if (!empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = 0;
            }
        }


        if ($tipx == 'prev') {

            $buktix = $request->buktix;

            $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from BANK
		             where PER ='$per' and TYPE ='$this->FLAGZ' and NO_BUKTI <
					 '$buktix'
                     AND CBG = '$CBG'
                     ORDER BY NO_BUKTI DESC LIMIT 1");


            if (!empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = $idx;
            }
        }


        if ($tipx == 'next') {


            $buktix = $request->buktix;

            $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from BANK
		             where PER ='$per' and TYPE ='$this->FLAGZ' and NO_BUKTI >
					 '$buktix'
                     AND CBG = '$CBG'
                     ORDER BY NO_BUKTI ASC LIMIT 1");

            if (!empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = $idx;
            }
        }

        if ($tipx == 'bottom') {

            $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from Bank
            		  where PER ='$per' and TYPE ='$this->FLAGZ'
		              AND CBG = '$CBG'
                      ORDER BY NO_BUKTI DESC  LIMIT 1");

            if (!empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = 0;
            }
        }


        if ($tipx == 'undo') {

            $tipx = 'edit';
        }



        if ($idx != 0) {
            $bank = Bank::where('NO_ID', $idx)->first();
        } else {
            $bank = new Bank;
            $bank->TGL = Carbon::now();
        }

        $no_bukti = $bank->NO_BUKTI;

        $bankDetail = DB::table('bankd')->where('NO_BUKTI', $no_bukti)->get();
        $data = [
            'header'        => $bank,
            'detail'        => $bankDetail
        ];

        return view('master_bank.edit', $data)
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
    public function update(Request $request, Bank $bank)
    {

        $this->validate(
            $request,
            [
                // ganti 19
                'TGL'      => 'required',
                'BACNO'       => 'required'
            ]
        );

        // ganti 20
        $variablell = DB::select('call bankdel(?)', array($bank['NO_BUKTI']));


        $this->setFlag($request);
        $FLAGZ = $this->FLAGZ;
        $judul = $this->judul;

        $CBG = Auth::user()->CBG;

        $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];


        $bank->update(
            [
                'CODE'             => ($request['CODE'] == null) ? "" : $request['CODE'],
                'NAME'             => ($request['NAME'] == null) ? "" : $request['NAME'],
                'BANK_CODE'        => ($request['BANK_CODE'] == null) ? "" : $request['BANK_CODE'],
            ]
        );

        $no_buktix = $bank->NO_BUKTI;

        // Update Detail
        $length = sizeof($request->input('REC'));
        $NO_ID  = $request->input('NO_ID');
        $REC    = $request->input('REC');
        $ACNO   = $request->input('ACNO');
        $NACNO  = $request->input('NACNO');
        $NO_HUT  = $request->input('NO_HUT');
        $URAIAN = $request->input('URAIAN');
        $JUMLAH = $request->input('JUMLAH');

        // Delete yang NO_ID tidak ada di input
        $query = DB::table('bankd')->where('NO_BUKTI', $request->NO_BUKTI)->whereNotIn('NO_ID',  $NO_ID)->delete();

        // Update / Insert
        for ($i = 0; $i < $length; $i++) {
            // Insert jika NO_ID baru
            if ($NO_ID[$i] == 'new') {
                $insert = BankDetail::create(
                    [
                        'CODE'      => ($request['CODE'] == null) ? "" : $request['CODE'],
                        'NAME'      => ($request['NAME'] == null) ? "" : $request['NAME'],
                        'BANK_CODE' => ($request['BANK_CODE'] == null) ? "" : $request['BANK_CODE'],
                    ]
                );
            } else {
                // Update jika NO_ID sudah ada
                $update = BankDetail::updateOrCreate(
                    [
                        'NO_BUKTI'  => $no_buktix,
                        'NO_ID'     => (int) str_replace(',', '', $NO_ID[$i])
                    ],

                    [
                        'REC'        => $REC[$i],
                        'ACNO'       => ($ACNO[$i] == null) ? "" :  $ACNO[$i],
                        'NACNO'      => ($NACNO[$i] == null) ? "" : $NACNO[$i],
                        'NO_FAKTUR'  => ($NO_HUT[$i] == null) ? "" : $NO_HUT[$i],
                        'URAIAN'     => ($URAIAN[$i] == null) ? "" : $URAIAN[$i],
                        'JUMLAH'     => (float) str_replace(',', '', $JUMLAH[$i]),
                        'DEBET'      => ($FLAGZ == 'BBM') ? (float) str_replace(',', '', $JUMLAH[$i]) : 0,
                        'KREDIT'     => ($FLAGZ == 'BBK') ? (float) str_replace(',', '', $JUMLAH[$i]) : 0
                    ]
                );
            }
        }



        //  ganti 21
        $variablell = DB::select('call bankins(?)', array($bank['NO_BUKTI']));
        $bank = Bank::where('NO_BUKTI', $no_buktix)->first();
        DB::SELECT("UPDATE BANK, ACCOUNT
                            SET BANK.BNAMA = ACCOUNT.NAMA  WHERE BANK.BACNO = ACCOUNT.ACNO
							AND BANK.NO_BUKTI='$no_buktix';");

        DB::SELECT("UPDATE bank, hut
                    SET bank.NO_BUKTI = hut.NO_KASIR WHERE bank.NO_BUKTI='$no_buktix';");

        DB::SELECT("UPDATE BANK, BANKD
                            SET BANKD.ID = BANK.NO_ID  WHERE BANK.NO_BUKTI = BANKD.NO_BUKTI
							AND BANK.NO_BUKTI='$no_buktix';");

        //return redirect('/bank/edit/?idx=' . $bank->NO_ID . '&tipx=edit&flagz=' . $this->FLAGZ . '&judul=' . $this->judul . '');
        return redirect('/bank?flagz=' . $FLAGZ)->with(['judul' => $judul, 'flagz' => $FLAGZ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    // ganti 22

    public function destroy(Request $request, Bank $bank)
    {


        $this->setFlag($request);
        $FLAGZ = $this->FLAGZ;
        $judul = $this->judul;


        $per = session()->get('periode')['bulan'] . '/' . session()->get('periode')['tahun'];
        $cekperid = DB::SELECT("SELECT POSTED from perid WHERE PERIO='$per'");
        if ($cekperid[0]->POSTED == 1) {
            return redirect('/bank')->with('status', 'Maaf Periode sudah ditutup!');
        }

        $variablell = DB::select('call bankdel(?)', array($bank['NO_BUKTI']));


        // ganti 23
        $deleteBank = Bank::find($bank->NO_ID);

        // ganti 24

        $deleteBank->delete();

        // ganti
        return redirect('/bank?flagz=' . $FLAGZ)->with(['judul' => $judul, 'flagz' => $FLAGZ])->with('statusHapus', 'Data ' . $bank->NO_BUKTI . ' berhasil dihapus');
    }

    public function cetak(Bank $bank)
    {
        $no_bukti = $bank->NO_BUKTI;

        $file     = 'bankc';
        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

        $judul = '';
        if ($bank->TYPE == 'BBK') {
            $judul = 'Bukti Bank Keluar';
        } else {
            $judul = 'Bukti Bank Masuk';
        }



        $query = DB::SELECT("
			SELECT bank.NO_BUKTI,bank.TGL,bank.KET,bank.BNAMA,
            bankd.REC,bankd.ACNO,bankd.NACNO,bankd.URAIAN,bankd.JUMLAH as JUMLAH
			FROM bank, bankd
			WHERE bank.NO_BUKTI=bankd.NO_BUKTI and bank.NO_BUKTI='$no_bukti'
			ORDER BY bank.NO_BUKTI;
		");

        $query2 = DB::SELECT("
			SELECT NAMA from compan ;
		");

        $data = [];
        foreach ($query as $key => $value) {
            array_push($data, array(
                'NO_BUKTI' => $query[$key]->NO_BUKTI,
                'TGL' => $query[$key]->TGL,
                'KET' => $query[$key]->KET,
                'BNAMA' => $query[$key]->BNAMA,
                'REC' => $query[$key]->REC,
                'ACNO' => $query[$key]->ACNO,
                'NACNO' => $query[$key]->NACNO,
                'URAIAN' => $query[$key]->URAIAN,
                'JUMLAH' => $query[$key]->JUMLAH,
                'JUDUL' => $judul,
                'NAMA' => $query2[0]->NAMA
            ));
        }
        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I");
    }

    public function getDetailBank()
    {

        $no_bukti = $_GET['no_bukti'];
        $result = DB::table('bankd')->where('NO_BUKTI', $no_bukti)->get();

        return response()->json($result);;
    }
}
