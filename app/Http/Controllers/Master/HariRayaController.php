<?php
namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\Hraya;
use Auth;
use DataTables;
use DB;
use Illuminate\Http\Request;

class HariRayaController extends Controller
{

    public function index()
    {
        return view('master_daftar_hari_raya.index');
    }

    public function getHariRaya(Request $request)
    {

        $query = DB::table('hraya')->orderBy('kode');

        return Datatables::of($query)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                if (Auth::user()->divisi == "programmer") {

                    $btnPrivilege =
                    '
                                    <a class="dropdown-item" href="hraya/edit/?idx=' . $row->NO_ID . '&tipx=edit";
                                    <i class="fas fa-edit"></i>
                                        Edit
                                    </a>
                                    <a class="dropdown-item btn btn-danger" href="hraya/cetak/' . $row->NO_ID . '">
                                        <i class="fa fa-print" aria-hidden="true"></i>
                                        Print
                                    </a>
                                    <hr></hr>
                                    <a class="dropdown-item btn btn-danger" onclick="return confirm(&quot; Apakah anda yakin ingin hapus? &quot;)" href="hraya/delete/' . $row->NO_ID . '">
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
                                <a hidden class="dropdown-item" href="hraya/show/' . $row->NO_ID . '">
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
        $tahun = session()->get('periode')['tahun']; 

        $query = DB::table('hraya')
            ->select('KODE')
            ->where('KODE', 'like', 'HR' . $tahun . $bulan . '%')
            ->orderByDesc('KODE')
            ->first();

        if ($query) {
            $lastNumber = intval(substr($query->KODE, -3));
            $newNumber  = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '001';
        }

        $no_bukti = 'HR' . $tahun . $bulan . $newNumber;

        // Insert Header

        // ganti 10

        $hraya = Hraya::create(
            [
                'KODE'    => $no_bukti,
                'NAMA'    => ($request['NAMA'] == null) ? "" : $request['NAMA'],
                'TGL'     => ($request['TGL'] == null) ? "" : $request['TGL'],
                'TGL_SLS' => ($request['TGL_SLS'] == null) ? "" : $request['TGL_SLS'],
            ]
        );

        return redirect('/hraya')->with('statusInsert', 'Data baru berhasil ditambahkan');

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    // ganti 15

    public function edit(Request $request, Hraya $hraya)
    {

        // ganti 16
        $tipx = $request->tipx;

        $idx = $request->idx;

        if ($idx == '0' && $tipx == 'undo') {
            $tipx = 'top';

        }

        if ($tipx == 'search') {

            $kodex = $request->kodex;

            $bingco = DB::SELECT("SELECT NO_ID, KODE from hraya
		                 where KODE = '$kodex'
		                 ORDER BY KODE ASC  LIMIT 1");

            if (! empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = 0;
            }

        }

        if ($tipx == 'top') {

            $bingco = DB::SELECT("SELECT NO_ID, KODE from hraya
		                 ORDER BY KODE ASC  LIMIT 1");

            if (! empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = 0;
            }

        }

        if ($tipx == 'prev') {

            $kodex = $request->kodex;

            $bingco = DB::SELECT("SELECT NO_ID, KODE from hraya
		             where KODE <
					 '$kodex' ORDER BY KODE DESC LIMIT 1");

            if (! empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = $idx;
            }

        }
        if ($tipx == 'next') {

            $kodex = $request->kodex;

            $bingco = DB::SELECT("SELECT NO_ID, KODE from hraya
		             where KODE >
					 '$kodex' ORDER BY KODE ASC LIMIT 1");

            if (! empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = $idx;
            }

        }

        if ($tipx == 'bottom') {

            $bingco = DB::SELECT("SELECT NO_ID, KODE from hraya
		              ORDER BY KODE DESC  LIMIT 1");

            if (! empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = 0;
            }

        }

        if ($tipx == 'undo' || $tipx == 'search') {

            $tipx = 'edit';

        }

        //   $kd_brg = $brg->KD_BRG;

        if ($idx != 0) {
            $hraya = Hraya::where('NO_ID', $idx)->first();
        } else {
            $hraya = new Hraya;
        }

        $data = [
            'header' => $hraya,
        ];

        return view('master_daftar_hari_raya.edit', $data)->with(['tipx' => $tipx, 'idx' => $idx]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    public function update(Request $request, Hraya $hraya)
    {

        $this->validate(
            $request,
            [
                // ganti 19
                'KODE' => 'required',
            ]
        );

        // ganti 20

        $hraya->update(
            [

                'NAMA'    => ($request['NAMA'] == null) ? "" : $request['NAMA'],
                'TGL'     => ($request['TGL'] == null) ? "" : $request['TGL'],
                'TGL_SLS' => ($request['TGL_SLS'] == null) ? "" : $request['TGL_SLS'],
            ]
        );

        ////////////////////////////////////////////////////
        return redirect('/hraya')->with('status', 'Data berhasil diupdate');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    // ganti 22

    public function destroy(Request $request, Hraya $hraya)
    {

        // ganti 23
        $deleteHraya = Hraya::find($hraya->NO_ID);

        // ganti 24

        $deleteHraya->delete();

        // ganti
        return redirect('/hraya')->with('status', 'Data berhasil dihapus');
    }

    public function cetak(Hraya $hraya, Request $request)
    {
        $no_hraya = $hraya->KODES;

        $file     = 'hariraya';
        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

        $query = DB::SELECT("SELECT beli.NO_BUKTI, beli.TGL, beli.KODES, beli.NAMAS, beli.TOTAL_QTY, beli.NOTES, beli.ALAMAT,
                                    beli.KOTA, belid.KD_BRG, belid.NA_BRG, belid.SATUAN, belid.QTY,
                                    belid.HARGA AS HARGA, belid.TOTAL, belid.KET,  beli.NETT, beli.USRNM, belid.PPN, belid.DPP
                            FROM beli, belid
                            WHERE beli.NO_BUKTI='$no_beli' AND beli.NO_BUKTI = belid.NO_BUKTI
                            ;
		");

                DB::SELECT("UPDATE beli SET POSTED = 1 WHERE NO_BUKTI='$no_beli';");

        $data = [];

        foreach ($query as $key => $value) {
            array_push($data, array(
                'NO_BUKTI' => $query[$key]->NO_BUKTI,
                'TGL'      => $query[$key]->TGL,
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
                'NA_BRG'    => $query[$key]->NA_BRG,
                'SATUAN'    => $query[$key]->SATUAN,
                'QTY'    => $query[$key]->QTY,
                'DISK'    => $query[$key]->DISK,
                'NETT'    => $query[$key]->NETT,
                'KET'    => $query[$key]->KET,
                'NO_PO'    => $query[$key]->NO_PO,
                'JUDUL'    => $judul,
                'USRNM'    => $query[$key]->USRNM,
                'KALI'    => $query[$key]->KALI,
                'TPPN'    => $query[$key]->TPPN,
                'TDISK'    => $query[$key]->TDISK,
                'TDPP'    => $query[$key]->TDPP,
                'PPN'    => $query[$key]->PPN,
                'DPP'    => $query[$key]->DPP
            ));
        }

        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I");

    }
}