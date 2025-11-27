<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Master\Brg;
use App\Models\Master\Sup;
use DataTables;
use Auth;
use DB;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";
use PHPJasperXML;

class UbahhrgvipController extends Controller
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

        if ( $request->flagz == 'PV') {
            $this->judul = "Pelaksanaan Perubahan Harga VIP";
        } 

        $this->FLAGZ = $request->flagz;

    }

    public function index(Request $request) {
        $this->setFlag($request);

        return view('otransaksi_ubahhrgvip.index')->with([
            'judul' => $this->judul,
            'flagz' => $this->FLAGZ
        ]);
    }

    public function getUbahhrgvip(Request $request)
    {
        $cbg = Auth::user()->CBG;
        $per = session()->get('periode')['bulan'] . '/' . session()->get('periode')['tahun'];

        // Panggil SP dengan 5 parameter yang pasti ada
        $sql = DB::select("SELECT NO_BUKTI,TGL,NOTES,if('$cbg'='TGZ',TGZ,if('$cbg'='TMM',TMM,SOP)) as POSTED
                            FROM DIS 
                            where per='$per' 
                            and flag='PV' 
                            group by no_bukti 
                            order by NO_BUKTI desc");

        return Datatables::of($sql)
            ->addIndexColumn()
            ->make(true);
    }

    public function print(Request $request)
    {
        $file = 'ubahhrgvip';
        $flagz = $request->input('flagz'); // dikirim dari tombol Print

        $per = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
        $cbg = Auth::user()->CBG; // sesuaikan kalau kamu simpan di session

        // --- Load file Jasper ---
        $PHPJasperXML = new \PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path('app/reportc01/phpjasperxml/' . $file . '.jrxml'));

        // --- Query data yang sama dengan DataTables ---
        $query = DB::select("
            SELECT 
                dis.NO_BUKTI,
                disd.KD_BRG,
                disd.NA_BRG,
                disd.KET_UK,
                disd.KET_KEM,
                disd.HJVIP
            FROM dis
            JOIN disd ON dis.NO_BUKTI = disd.NO_BUKTI
            WHERE dis.flag = ?
            AND dis.per = ?
            AND dis.cbg = ?
            ORDER BY dis.NO_BUKTI
        ", [$flagz, $per, $cbg]);

        // --- Konversi hasil query ke array untuk Jasper ---
        $data = [];
        foreach ($query as $row) {
            $data[] = [
                'NO_BUKTI' => $row->NO_BUKTI,
                'KD_BRG'   => $row->KD_BRG,
                'NA_BRG'   => $row->NA_BRG,
                'KET_UK'   => $row->KET_UK,
                'KET_KEM'  => $row->KET_KEM,
                'HJVIP'    => $row->HJVIP,
            ];
        }

        // --- Kirim parameter global ke Jasper (untuk header laporan) ---
        $PHPJasperXML->arrayParameter = [
            'FLAGZ' => (string) $flagz,
            'PER'   => (string) $per,
            'CBG'   => (string) $cbg,
        ];

        $PHPJasperXML->setData($data);

        ob_end_clean();
        $PHPJasperXML->outpage("I"); // "I" = tampil di browser langsung
    }


}