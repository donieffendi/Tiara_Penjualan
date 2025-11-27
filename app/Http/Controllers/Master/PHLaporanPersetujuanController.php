<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use PHPJasperXML;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

class PHLaporanPersetujuanController extends Controller
{
    public function index()
    {
        try {
            $cbgMst = DB::selectOne("SELECT KODE FROM toko WHERE STA='MA'");
            $cbgMst = $cbgMst ? $cbgMst->KODE : '';
        } catch (\Illuminate\Database\QueryException $e) {
            $cbgMst = '';
        }

        try {
            $taunIni = DB::selectOne("SELECT YEAR(NOW()) as taunini");
        } catch (\Illuminate\Database\QueryException $e) {
            $taunIni = null;
        }
        $taunIni = $taunIni ? $taunIni->taunini : date('Y');

        $periodeList = [];
        for ($j = 2024; $j <= $taunIni; $j++) {
            for ($i = 1; $i <= 12; $i++) {
                $periodeList[] = sprintf('%02d', $i) . '/' . $j;
            }
        }

        return view('promo_hadiah_laporan_persetujuan.index', [
            'title' => 'Laporan Persetujuan Sewa',
            'cbgMst' => $cbgMst,
            'periodeList' => $periodeList
        ]);
    }

    public function getData(Request $request)
    {
        try {
            $periode = $request->get('periode');
            $cbg = $request->get('cbg', '');

            $cbgMst = DB::selectOne("SELECT KODE FROM toko WHERE STA='MA'");
            $cbgMst = $cbgMst ? $cbgMst->KODE : '';

            if (empty($periode)) {
                return response()->json(['success' => false, 'message' => 'Cek Periode!']);
            }

            $query = "CALL " . $cbgMst . ".pmsr_report_sewa(?, ?, '', ?, '', 0)";
            $data = DB::select($query, ['PERSETUJUAN_SEWA', $cbg, $periode]);

            // dd($data);

            if (empty($data)) {
                return response()->json(['success' => false, 'message' => 'Tidak ada data..']);
            }

            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    public function exportExcel(Request $request)
    {
        try {
            $periode = $request->get('periode');
            $cbg = $request->get('cbg', '');

            $cbgMst = DB::selectOne("SELECT KODE FROM toko WHERE STA='MA'");
            $cbgMst = $cbgMst ? $cbgMst->KODE : '';

            $query = "CALL " . $cbgMst . ".pmsr_report_sewa(?, ?, '', ?, '', 0)";
            $data = DB::select($query, ['PERSETUJUAN_SEWA', $cbg, $periode]);

            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

     public function print(Request $request)
    {
        try {
            $periode = $request->get('periode');
            $cbg = $request->get('cbg', '');
            $TGL = Carbon::now()->format('d-m-Y');


            $cbgMst = DB::selectOne("SELECT KODE FROM toko WHERE STA='MA'");
            $cbgMst = $cbgMst ? $cbgMst->KODE : '';

            $query = "CALL " . $cbgMst . ".pmsr_report_sewa(?, ?, '', ?, '', 0)";
            $data = DB::select($query, ['PERSETUJUAN_SEWA', $cbg, $periode]);

            $file         = 'print_laporan_persetujuan';
            $PHPJasperXML = new PHPJasperXML();
            $PHPJasperXML->load_xml_file(base_path("/app/reportc01/phpjasperxml/{$file}.jrxml"));

            // $PHPJasperXML->setData($data);
            $cleanData  = json_decode(json_encode($data), true);
            $PHPJasperXML->arrayParameter = [
            "TGL"   => $TGL,
            ];

            $PHPJasperXML->setData($cleanData);

            ob_end_clean();
            $PHPJasperXML->outpage("I");
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
            }
    }
}