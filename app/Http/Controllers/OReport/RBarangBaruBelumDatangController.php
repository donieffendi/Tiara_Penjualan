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

class RBarangBaruBelumDatangController extends Controller
{
    public function report()
    {
        $cbg = DB::SELECT("SELECT KODE, STA FROM tgz.toko WHERE STA IN ('MA', 'CB') ORDER BY NO_ID ASC");

        session()->put('filter_cbg', session()->get('filter_cbg', ''));
        session()->put('filter_sub1', session()->get('filter_sub1', ''));
        session()->put('filter_sub2', session()->get('filter_sub2', 'ZZZ'));

        return view('oreport_barang_baru_belum_datang.report')->with([
            'hasilData' => [],
            // 'cabangList' => $this->getCabangList(),
            'cabangList' => $cbg,
        ]);
    }

    public function getBarangBaruBelumDatangReport(Request $request)
    {   
        $sub1 = $request->get('sub1', '');
        $sub2 = $request->get('sub2', 'ZZZ');
        $cbg = $request->get('cbg', $this->getCurrentCabang());

        session()->put('filter_cbg', $cbg);
        session()->put('filter_sub1', $sub1);
        session()->put('filter_sub2', $sub2);

        $hasilData = [];
        if ($request->get('process') == 1) {
            $hasilData = $this->getBarangBaruBelumDatangData($cbg, $sub1, $sub2);
        }
        
        return view('oreport_barang_baru_belum_datang.report')->with([
            'hasilData' => $hasilData,
            'cabangList' => $this->getCabangList(),
            'selectedCbg' => $cbg,
            'sub1' => $sub1,
            'sub2' => $sub2,
        ]);
    }

    private function getBarangBaruBelumDatangData($cbg, $sub1 = '', $sub2 = 'ZZZ')
    {   
        
        try {
            $query = "
                SELECT 
                    brg.ON_DC,
                    IF(brg.ON_DC=0, 'OTLET', COALESCE((SELECT KODE_DC FROM tgz.sup WHERE KODES=brg.SUPP LIMIT 1),'')) AS ondc,
                    DATEDIFF(NOW(), brg.tg_smp) AS tglbrg,
                    DATEDIFF(pod.TGO, brg.tg_smp) AS tglsp,
                    brg.sub,
                    brg.kd_brg,
                    brg.ket_kem,
                    brg.supp,
                    brgdt.cbg,
                    CONCAT(brg.na_brg,' ', brg.ket_uk) AS na_brg,
                    CONCAT(brgdt.kdlaku, brgdt.klk) AS kode,
                    DATE(brg.tg_smp) AS tgl_ada,
                    brgd.AK00 + brgdt.AK00 AS stok,
                    IF(
                        (brgdt.KDLAKU='4' OR brgdt.KDLAKU='5' OR brgdt.KDLAKU='6'),
                        IF(brgdt.SRMIN IS NULL, 0, brgdt.srmin),
                        IF(brgdt.smin IS NULL, 0, brgdt.SMin)
                    ) AS rop,
                    pod.no_bukti,
                    pod.qty,
                    (
                        SELECT DATE_FORMAT(po.tgl, '%Y-%m-%d') 
                        FROM tgz.po po
                        WHERE po.no_bukti = pod.no_bukti
                        LIMIT 1
                    ) AS tgl_po,
                    IF(brgdt.qty_trm>0, DATE(brgdt.tgl_trm), NULL) AS tgl_trm,
                    ? AS sub1,
                    ? AS sub2
                FROM tgz.brg brg
                LEFT JOIN tgz.brgdt brgdt ON brgdt.kd_brg = brg.kd_brg
                LEFT JOIN tgz.POd pod ON pod.kd_brg = brg.kd_brg AND pod.flag='PO'
                LEFT JOIN tgz.brgd brgd ON brgd.kd_brg = brg.kd_brg
                WHERE brgdt.cbg = ?
                AND brg.sub >= ?
                AND brg.sub <= ?
                AND LEFT(brg.na_brg, 1) = '*'
                AND brgdt.TD_OD = ''
                AND DATEDIFF(NOW(), brg.tg_smp) > 30
                AND brgdt.GAK00 + brgdt.AK00 <= 0
                AND DATE(brgdt.TGL_AW_TRM) = '2001-01-01'
                ORDER BY brg.ON_DC, brg.kd_brg
            ";

            $bindings = [$sub1, $sub2, $cbg, $sub1, $sub2];

            return DB::select($query, $bindings);
        } catch (\Exception $e) {
            Log::error('Error in getBarangBaruBelumDatangData: ' . $e->getMessage());
            return [];
        }
    }

    private function getCabangList()
    {
        try {
            return DB::select("SELECT KODE, STA FROM tgz.toko WHERE STA IN ('MA', 'CB') ORDER BY NO_ID ASC");
        } catch (\Exception $e) {
            Log::error('Error in getCabangList: ' . $e->getMessage());
            return [];
        }
    }

    private function getCurrentCabang()
    {
        return session('current_cabang', 'default_cabang');
    }

    public function jasperBarangBaruBelumDatangReport(Request $request)
    {
        $sub1 = $request->get('sub1', '');
        $sub2 = $request->get('sub2', 'ZZZ');
        $cbg = $request->get('cbg', $this->getCurrentCabang());

        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . '/app/reportc01/phpjasperxml/rbarang_baru_belum_datang.jrxml');

        $data = [];
        $results = $this->getBarangBaruBelumDatangData($cbg, $sub1, $sub2);

        foreach ($results as $row) {
            $data[] = [
                'CBG' => $row->cbg ?? '',
                'kd_brg' => $row->kd_brg ?? '',
                'na_brg' => $row->na_brg ?? '',
                'ket_kem' => $row->ket_kem ?? '',
                'kode' => $row->kode ?? '',
                'supp' => $row->supp ?? '',
                'tgl_ada' => $row->tgl_ada ?? '',
                'rop' => $row->rop ?? 0,
                'stok' => $row->stok ?? 0,
                'no_bukti' => $row->no_bukti ?? '',
                'tgl_order' => $row->tgl_order ?? '',
                'qty' => $row->qty ?? 0,
                'CABANG' => $cbg,
                'SUB1' => $sub1,
                'SUB2' => $sub2,
            ];
        }

        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I");
    }

    public function apiGetBarangBaruBelumDatangData(Request $request)
    {
        try {
            $sub1 = $request->get('sub1', '');
            $sub2 = $request->get('sub2', 'ZZZ');
            $cbg = $request->get('cbg', $this->getCurrentCabang());

            $hasil = $this->getBarangBaruBelumDatangData($cbg, $sub1, $sub2);

            return response()->json([
                'success' => true,
                'data' => $hasil,
                'cbg' => $cbg,
                'sub1' => $sub1,
                'sub2' => $sub2,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in apiGetBarangBaruBelumDatangData: ' . $e->getMessage());
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
