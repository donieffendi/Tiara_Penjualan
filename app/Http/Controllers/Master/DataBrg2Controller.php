<?php
namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\Brgfc;
use Auth;
use DataTables;
use DB;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DataBrg2Controller extends Controller
{

    public function index()
    {
        return view('master_data_barang_2.index');
    }

    public function getDataBrg2(Request $request)
    {

        $brg = DB::SELECT("SELECT masks.NO_ID, masks.KD_BRG, masks.NA_BRG, masks.KET_UK, masks.SUPP, sup.NAMAS as NSUP, masks.KET_KEM, masks.HJ, masks.HB 
                            from masks left join sup on masks.SUPP=sup.KODES");

        return Datatables::of($brg)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                if (Auth::user()->divisi == "programmer") {
                    $btnPrivilege =
                    '
                                    <a class="dropdown-item" href="dbrg2/edit/?idx=' . $row->NO_ID . '&tipx=edit";
                                    <i class="fas fa-edit"></i>
                                        Edit
                                    </a>
                                    <a class="dropdown-item btn btn-danger" href="dbrg2/cetak/' . $row->NO_ID . '">
                                        <i class="fa fa-print" aria-hidden="true"></i>
                                        Print
                                    </a>
                                    <hr></hr>
                                    <a class="dropdown-item btn btn-danger" onclick="return confirm(&quot; Apakah anda yakin ingin hapus? &quot;)" href="dbrg2/delete/' . $row->NO_ID . '">
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
                                <a hidden class="dropdown-item" href="dbrg2/show/' . $row->NO_ID . '">
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

        $query = DB::table('brgFC')
            ->select('SUB')
            ->where('SUB', 'like', 'HR' . $tahun . $bulan . '%')
            ->orderByDesc('SUB')
            ->first();

        if ($query) {
            $lastNumber = intval(substr($query->SUB, -3));
            $newNumber  = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '001';
        }

        $no_bukti = 'BRGFC' . $tahun . $bulan . $newNumber;

        // Insert Header

        // ganti 10

        $brgFC = Brgfc::create(
            [
                'SUB'      => $no_bukti,
                'KD_BRG'   => ($request['KD_BRG'] == null) ? "" : $request['KD_BRG'],
                'NA_BRG'   => ($request['NA_BRG'] == null) ? "" : $request['NA_BRG'],
                'KELOMPOK' => ($request['KELOMPOK'] == null) ? "" : $request['KELOMPOK'],
                'TKP'      => ($request['TKP'] == null) ? "" : $request['TKP'],
                'FLAGSTOK' => ($request['FLAGSTOK'] == null) ? "" : $request['FLAGSTOK'],
                'HJ'       => (float) str_replace(',', '', $request['HJ']),
                'HB'       => (float) str_replace(',', '', $request['HB']),
                'MARGIN'   => (float) str_replace(',', '', $request['MARGIN']),
                'MARGIN2'  => (float) str_replace(',', '', $request['MARGIN2']),
                'DIS'      => (float) str_replace(',', '', $request['DIS']),
                'HJ2'      => (float) str_replace(',', '', $request['HJ2']),
                'SUPP'     => ($request['KODES'] == null) ? "" : $request['KODES'],
                'NAMAS'    => ($request['SUPP'] == null) ? "" : $request['SUPP'],
                'BARCODE'  => ($request['BARCODE'] == null) ? "" : $request['BARCODE'],
                'STAND'    => ($request['STAND'] == null) ? "" : $request['STAND'],
                'LOK_TG'   => ($request['LOK_TG'] == null) ? "" : $request['LOK_TG'],
                'TYPE'     => ($request['TYPE'] == null) ? "" : $request['TYPE']]
        );

        //  ganti 11

        return redirect('/dbrg2')->with('statusInsert', 'Data baru berhasil ditambahkan');

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    // ganti 15

    public function edit(Request $request, Brgfc $dbrg)
    {

        // ganti 16
        $tipx = $request->tipx;

        $idx = $request->idx;

        $cbg = Auth::user()->CBG;

        if ($idx == '0' && $tipx == 'undo') {
            $tipx = 'top';

        }

        if ($tipx == 'search') {

            $kodex = $request->kodex;

            $bingco = DB::SELECT("SELECT NO_ID, SUB from masks
		                 where SUB = '$kodex'
		                 ORDER BY SUB ASC  LIMIT 1");

            if (! empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = 0;
            }

        }

        if ($tipx == 'top') {

            $bingco = DB::SELECT("SELECT NO_ID, SUB from masks
		                 ORDER BY  ASC  LIMIT 1");

            if (! empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = 0;
            }

        }

        if ($tipx == 'prev') {

            $kodex = $request->kodex;

            $bingco = DB::SELECT("SELECT NO_ID, SUB from masks
		             where  <
					 '$kodex' ORDER BY  DESC LIMIT 1");

            if (! empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = $idx;
            }

        }
        if ($tipx == 'next') {

            $kodex = $request->kodex;

            $bingco = DB::SELECT("SELECT NO_ID,SUB  from masks
		             where  >
					 '$kodex' ORDER BY  ASC LIMIT 1");

            if (! empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = $idx;
            }

        }

        if ($tipx == 'bottom') {

            $bingco = DB::SELECT("SELECT NO_ID,SUB  from masks
		              ORDER BY  DESC  LIMIT 1");

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
            // $brgFC = Brgfc::where('NO_ID', $idx)->first();
            $brgFC = DB::table('masks')
                        ->leftJoin('sup', 'masks.SUPP', '=', 'sup.KODES')
                        ->select('masks.*', 'sup.NAMAS as NSUP')
                        ->where('masks.NO_ID', $idx)
                        ->first();
        } else {
            $brgFC = new Brgfc;
            // $brgFC = new \stdClass();
            $brgFC->JAM = date('H:i:s');
            $brgFC->JAMSLS = date('H:i:s');
            $brgFC->TGDIS_M = Carbon::now();
            $brgFC->TGDIS_A = Carbon::now();
        }

        $data = [
            'header' => $brgFC,
        ];

        return view('master_data_barang_2.edit', $data)->with(['tipx' => $tipx, 'idx' => $idx]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    public function update(Request $request, Brgfc $dbrg)
    {

        // ganti 20

        $tipx = 'edit';
        $idx  = $request->idx;

        $dbrg->update(
            [
                'KD_BRG'   => ($request['KD_BRG'] == null) ? "" : $request['KD_BRG'],
                'NA_BRG'   => ($request['NA_BRG'] == null) ? "" : $request['NA_BRG'],
                'KELOMPOK' => ($request['KELOMPOK'] == null) ? "" : $request['KELOMPOK'],
                'TKP'      => ($request['TKP'] == null) ? "" : $request['TKP'],
                'FLAGSTOK' => ($request['FLAGSTOK'] == null) ? "" : $request['FLAGSTOK'],
                'HJ'       => (float) str_replace(',', '', $request['HJ']),
                'HB'       => (float) str_replace(',', '', $request['HB']),
                'MARGIN'   => (float) str_replace(',', '', $request['MARGIN']),
                'MARGIN2'  => (float) str_replace(',', '', $request['MARGIN2']),
                'DIS'      => (float) str_replace(',', '', $request['DIS']),
                'HJ2'      => (float) str_replace(',', '', $request['HJ2']),
                'SUPP'     => ($request['SUPP'] == null) ? "" : $request['SUPP'],
                'NAMAS'    => ($request['NAMAS'] == null) ? "" : $request['NAMAS'],
                'BARCODE'  => ($request['BARCODE'] == null) ? "" : $request['BARCODE'],
                'STAND'    => ($request['STAND'] == null) ? "" : $request['STAND'],
                'LOK_TG'   => ($request['LOK_TG'] == null) ? "" : $request['LOK_TG'],
                'TYPE'     => ($request['TYPE'] == null) ? "" : $request['TYPE'],
            ]
        );

        return redirect('/dbrg2')->with('status', 'Data berhasil diupdate');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    // ganti 22

    public function destroy(Request $request, Brgfc $dbrg)
    {

        // ganti 23
        $deleteBrgfc = Brgfc::find($dbrg->NO_ID);

        // ganti 24

        $deleteBrgfc->delete();

        // ganti
        return redirect('/dbrg2')->with('status', 'Data berhasil dihapus');
    }

    public function cetak(Request $request)
    {
        $search = $request->input('search', ''); // ambil parameter search dari URL

        $file = 'dbrg2';
        $PHPJasperXML = new \PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path('app/reportc01/phpjasperxml/' . $file . '.jrxml'));

        // tambahkan kondisi pencarian jika ada input search
        $where = '';
        if (!empty($search)) {
            $where = "WHERE (KD_BRG LIKE '%$search%' OR NA_BRG LIKE '%$search%')";
        }

        $query = DB::SELECT("
            SELECT KD_BRG, NA_BRG, KET_UK, KET_KEM
            FROM brg
            $where
            ORDER BY KD_BRG
        ");

        $data = [];

        foreach ($query as $value) {
            $data[] = [
                'KD_BRG'   => $value->KD_BRG,
                'NA_BRG'   => $value->NA_BRG,
                'KET_UK'   => $value->KET_UK,
                'KET_KEM' => $value->KET_KEM,
            ];
        }

        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I"); // tampil langsung di browser
    }
}