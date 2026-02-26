<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
// ganti 1

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
class RekaprakController extends Controller
{
    public function index(Request $request)
    {
        $cbg = DB::SELECT("SELECT KODE FROM toko WHERE STA IN ('MA','CB')");
        session()->put('filter_cbg', '');
        session()->put('filter_tgl', date("d-m-Y"));

        return view('otransaksi_rekaprak.index')->with(['cbg' => $cbg]);
    }
    // ganti 4


    public function getRekaprak(Request $request)
    {
        // ganti 5

       if ($request->session()->has('periode')) {
            $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
        } else {
            $periode = '';
        }

        $CBG = $request->cbg;
        $tgl = $request->tgl;

        $tglx = date('Y-m-d', strtotime($tgl));
        
        // dd($request->all());

        $rekaprak = DB::SELECT("CALL pjl_komponen_harga('REKAP_KOMPONEN_HARIAN', '$CBG', '$tglx')");


        // ganti 6

        return Datatables::of($rekaprak)
            ->addIndexColumn()
            ->make(true);
    }

    public function cetak(Request $request)
    {
        $file = 'rpt_rak_harian';
        $CBG = $request->cbg;
        $tgl = $request->tgl;

        $tglx = date('Y-m-d', strtotime($tgl));
        $data = DB::SELECT("CALL pjl_komponen_harga('REKAP_KOMPONEN_HARIAN', '$CBG', '$tglx')");
        // dd($data);
        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

        $cleanData = json_decode(json_encode($data), true);
        $PHPJasperXML->setData($cleanData);
        $PHPJasperXML->setParameter([
            'DATE' => date('d/m/Y'),
        ]);
        ob_end_clean();
        $PHPJasperXML->outpage("I");
    }

}