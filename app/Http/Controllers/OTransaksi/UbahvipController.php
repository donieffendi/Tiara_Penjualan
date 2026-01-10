<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
// ganti 1

use App\Models\OTransaksi\Ubahvip;
use App\Models\OTransaksi\UbahvipDetail;
use App\Models\Master\Sup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use DataTables;
use Auth;
use DB;
use Carbon\Carbon;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";
use PHPJasperXML;

// ganti 2
class UbahvipController extends Controller
{
    public function index(Request $request)
    {

        return view('otransaksi_ubahvip.index');
    }
    // ganti 4


    public function getUbahvip(Request $request)
    {
        // ganti 5

       if ($request->session()->has('periode')) {
            $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
        } else {
            $periode = '';
        }

        $CBG = Auth::user()->CBG;

        $ubahvip = DB::SELECT("SELECT no_bukti,tgl,notes,if('$CBG'='TGZ',TGZ,if('$CBG'='TMM',TMM,SOP)) as POSTED
                FROM dis where per='$periode' and flag='PV' group by no_bukti order by no_bukti desc");


        // ganti 6

        return Datatables::of($ubahvip)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                if (Auth::user()->divisi=="programmer" )
				{
                    $btnPrivilege =
                        '
                                <a class="dropdown-item btn btn-danger" href="ubahvip/cetak/' . $row->NO_ID . '">
                                    <i class="fa fa-print" aria-hidden="true"></i>
                                    Print
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

    public function cetak(Ubahvip $ubahvip)
    {
        $no_ubah = $ubahvip->NO_BUKTI;

        $file     = 'ubahhrgvip';
        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

        $periode = session()->get('periode')['bulan'] . '/' . session()->get('periode')['tahun'];
        $CBG = Auth::user()->CBG;

        $query = DB::SELECT("SELECT dis.no_bukti,disd.KD_BRG,
                                    disd.NA_BRG,disd.ket_uk,disd.ket_kem,disd.HJVIP
                                    FROM dis,disd WHERE dis.FLAG='PV' AND dis.no_bukti=disd.no_bukti AND dis.no_bukti='$no_ubah'
                                    AND dis.per='$periode' AND dis.CBG='$CBG' ORDER BY dis.no_bukti
		");

        $data = [];

        $data = json_decode(json_encode($query), true);

        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I");

    }

}