<?php

namespace App\Http\Controllers\OReport;

use App\Http\Controllers\Controller;
use App\Models\Master\Cbg;
use App\Models\Master\Perid;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;

class RSurveiHargaJualExpiredController extends Controller
{
    public function report()
    {
        $cbg = Cbg::groupBy('CBG')->get();

        session()->put('filter_cbg', '');
        session()->put('filter_cbg', $this->getCurrentCabang());
        session()->put('filter_no_exp', '');

        return view('oreport_survei_harga_jual_expired.report')->with([
            'hasilData' => [],
            // 'cabangList' => $this->getCabangList(),
            'cabangList' => $cbg,
        ]);
    }

    public function getSurveiHargaJualExpiredReport(Request $request)
    {
        $cbg = $request->get('cbg', $this->getCurrentCabang());
        $noExp = $request->get('no_exp', '');
        $ulang = $request->has('ulang') ? true : false;

        session()->put('filter_cbg', $cbg);
        session()->put('filter_no_exp', $noExp);

        $hasilData = [];
        if ($request->has('process')) {
            $hasilData = $this->getSurveiHargaJualExpiredData($cbg, $noExp, $ulang);
        }

        $cbgx = Cbg::groupBy('CBG')->get();

        return view('oreport_survei_harga_jual_expired.report')->with([
            'hasilData' => $hasilData,
            'cabangList' => $cbgx,
            'selectedCbg' => $cbg,
            'noExp' => $noExp,
            'ulang' => $ulang,
        ]);
    }

    private function getSurveiHargaJualExpiredData($cbg, $noExp = '', $ulang = false)
    {
        try {
            $cbgMst = $this->getCabangMaster();
            $jenis = $ulang ? 'PROSES_ULANG' : 'PROSES_REPORT';
            $fileParam = $ulang ? trim($noExp) : '';

            $query = "CALL {$cbgMst}.pjl_survei_expd(?, ?, ?)";

            return DB::select($query, [$jenis, $cbg, $fileParam]);
        } catch (\Exception $e) {
            Log::error('Error in getSurveiHargaJualExpiredData: ' . $e->getMessage());
            return [];
        }
    }

    private function getCabangList()
    {
        try {
            return DB::select("SELECT kode_cbg, nama_cbg FROM ma.cabang ORDER BY kode_cbg");
        } catch (\Exception $e) {
            Log::error('Error in getCabangList: ' . $e->getMessage());
            return [];
        }
    }

    private function getCurrentCabang()
    {
        return session('current_cabang', 'default_cabang');
    }

    private function getCabangMaster()
    {
        try {
            $result = DB::select('SELECT KODE FROM toko WHERE STA="MA"');
            return $result[0]->KODE ?? 'default';
        } catch (\Exception $e) {
            Log::error('Error in getCabangMaster: ' . $e->getMessage());
            return 'default';
        }
    }

    public function jasperSurveiHargaJualExpiredReport(Request $request)
    {
        $cbg = $request->get('cbg', $this->getCurrentCabang());
        $noExp = $request->get('no_exp', '');
        $ulang = $request->has('ulang') ? true : false;

        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . '/app/reportc01/phpjasperxml/rpt_survei_jualexp.jrxml');

        $data = [];
        $results = $this->getSurveiHargaJualExpiredData($cbg, $noExp, $ulang);

        foreach ($results as $row) {
            $data[] = [
                'NO_EXP' => $row->NO_EXP ?? '',
                'KODES' => $row->KODE ?? '',
                'TGL' => $row->TGL ?? '',
                'URAIAN' => $row->URAIAN ?? '',
                'KET_UK' => $row->KET_UK ?? '',
                'HJBR' => $row->HJBR ?? 0,
                'TGL_TRM' => $row->TGL_TRM ?? '',
                'KET' => $row->KET ?? '',
				'TGL_CTK'  => date('Y-m-d'),
            ];
        }

        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I");
    }

    public function apiGetSurveiHargaJualExpiredData(Request $request)
    {
        try {
            $cbg = $request->get('cbg', $this->getCurrentCabang());
            $noExp = $request->get('no_exp', '');
            $ulang = $request->has('ulang') ? true : false;

            $hasil = $this->getSurveiHargaJualExpiredData($cbg, $noExp, $ulang);

            return response()->json([
                'success' => true,
                'data' => $hasil,
                'cbg' => $cbg,
                'no_exp' => $noExp,
                'ulang' => $ulang,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in apiGetSurveiHargaJualExpiredData: ' . $e->getMessage());
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
