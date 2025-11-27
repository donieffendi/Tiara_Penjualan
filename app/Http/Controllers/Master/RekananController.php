<?php
namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\Rekanan;
use Auth;
use Carbon\Carbon;
use DataTables;
use DB;
use Illuminate\Http\Request;
include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";
use PHPJasperXML;
class RekananController extends Controller
{

    public function index()
    {
        return view('master_rekanan.index');
    }

    public function getRekanan(Request $request)
    {

        $query = DB::select("SELECT * from rekanan order by kode");

        return Datatables::of($query)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                if (Auth::user()->divisi == "programmer") {
                    $btnPrivilege =
                    '
                                    <a class="dropdown-item" href="rekanan/edit/?idx=' . $row->NO_ID . '&tipx=edit";
                                    <i class="fas fa-edit"></i>
                                        Edit
                                    </a>
                                    <a class="dropdown-item btn btn-danger" href="rekanan/cetak/' . $row->NO_ID . '">
                                        <i class="fa fa-print" aria-hidden="true"></i>
                                        Print
                                    </a>
                                    <hr></hr>
                                    <a class="dropdown-item btn btn-danger" onclick="return confirm(&quot; Apakah anda yakin ingin hapus? &quot;)" href="rekanan/delete/' . $row->NO_ID . '">
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
                                <a hidden class="dropdown-item" href="brg/show/' . $row->NO_ID . '">
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

        $query = DB::table('rekanan')
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

        $no_bukti = 'REK' . $tahun . $bulan . $newNumber;

        // Insert Header

        // ganti 10

        $rekanan = Rekanan::create(
            [
                'KODE'   => $no_bukti,
                'NAMA'   => ($request['NAMA'] == null) ? "" : $request['NAMA'],
                'USRNM'  => Auth::user()->username,
                'TG_SMP' => Carbon::now(),
            ]
        );

        //  ganti 11
        return redirect('/rekanan')->with('statusInsert', 'Data baru berhasil ditambahkan');

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    // ganti 15

    public function edit(Request $request, Rekanan $rekanan)
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

            $bingco = DB::SELECT("SELECT NO_ID, KODE from rekanan
		                 where KODE = '$kodex'
		                 ORDER BY KODE ASC  LIMIT 1");

            if (! empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = 0;
            }

        }

        if ($tipx == 'top') {

            $bingco = DB::SELECT("SELECT NO_ID, KODE from rekanan
		                 ORDER BY KODE ASC  LIMIT 1");

            if (! empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = 0;
            }

        }

        if ($tipx == 'prev') {

            $kodex = $request->kodex;

            $bingco = DB::SELECT("SELECT NO_ID, KODE from rekanan
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

            $bingco = DB::SELECT("SELECT NO_ID, KODE from rekanan
		             where KODE >
					 '$kodex' ORDER BY KODE ASC LIMIT 1");

            if (! empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = $idx;
            }

        }

        if ($tipx == 'bottom') {

            $bingco = DB::SELECT("SELECT NO_ID, KODE from rekanan
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

        if ($idx != 0) {
            $rekanan = Rekanan::where('NO_ID', $idx)->first();
        } else {
            $rekanan = new Rekanan;
        }

        $data = [
            'header' => $rekanan,
        ];

        return view('master_rekanan.edit', $data)->with(['tipx' => $tipx, 'idx' => $idx]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    public function update(Request $request, Rekanan $rekanan)
    {
        // ganti 20
        $tipx = 'edit';
        $idx  = $request->idx;

        $rekanan->update(
            [
                'NAMA'   => ($request['NAMA'] == null) ? "" : $request['NAMA'],
                'USRNM'  => Auth::user()->username,
                'TG_SMP' => Carbon::now()]
        );
        
        return redirect('/rekanan')->with('status', 'Data berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    // ganti 22

    public function destroy(Request $request, Rekanan $rekanan)
    {

        // ganti 23
        $deleteRekanan = Rekanan::find($rekanan->NO_ID);

        // ganti 24

        $deleteRekanan->delete();

        // ganti
        return redirect('/rekanan')->with('status', 'Data berhasil dihapus');
    }

    public function cetak(Request $request)
    {
        $search = $request->input('search', ''); // ambil parameter search dari URL

        $file = 'Master_Rekanan';
        $PHPJasperXML = new \PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path('app/reportc01/phpjasperxml/' . $file . '.jrxml'));

        // tambahkan kondisi pencarian jika ada input search
        $where = '';
        if (!empty($search)) {
            $where = "WHERE (KODE LIKE '%$search%' OR NAMA LIKE '%$search%')";
        }

        $query = DB::SELECT("
            SELECT KODE, NAMA
            FROM rekanan
            $where
            ORDER BY KODE
        ");

        $data = [];

        foreach ($query as $value) {
            $data[] = [
                'KODE'   => $value->KODE,
                'NAMA'   => $value->NAMA
            ];
        }

        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I"); // tampil langsung di browser
    }

}