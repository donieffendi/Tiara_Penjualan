<?php

namespace App\Http\Controllers\OReport;

use App\Http\Controllers\Controller;
use App\Models\Master\Cbg;
use App\Models\Master\Perid;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;

class RBelumSOController extends Controller
{
    public function report()
    {   
        // Initialize session variables
        session()->put('filter_sub', '');
        session()->put('filter_tglDari', date("d-m-Y"));
        session()->put('filter_tglSampai', date("d-m-Y"));
        session()->put('filter_belum', '');

        return view('oreport_belumso.report')->with([
            'belumSO' => []
        ]);
    }

    public function getBelumSOReport(Request $request)
    {
        $sub = $request->sub;
        $tglDrD = date("Y-m-d", strtotime($request->tglDr));
        $tglSmpD = date("Y-m-d", strtotime($request->tglSmp));
        $belum = $request->belum;

        // Set filter values to session
        session()->put('filter_sub', $sub);
        session()->put('filter_tglDari', $request->tglDr);
        session()->put('filter_tglSampai', $request->tglSmp);
        session()->put('filter_belum', $belum);

        $belumSO = [];
        // dd($request->all());
        if (!empty($request->sub)) {
            // Validate kode tidak boleh kosong
            if (empty($request->tglDr) || empty($request->tglSmp)) {
                return redirect()->back()->withErrors(['tglDr' => 'Tanggal Tidak Boleh Kosong']);
            }

            // Get data barang macet
            $belumSO = $this->getBelumSO($request->sub, $tglDrD, $tglSmpD, $request->belum);
        }

        return view('oreport_belumso.report')->with([
            'belumSO' => $belumSO
        ]);
    }

    /**
     * Get data barang macet berdasarkan jenis yang dipilih
     * Equivalent dengan logic dalam procedure Tampil
     */
    private function getBelumSO($sub, $tglDrD, $tglSmpD, $belum)
    {
        try {
            $filterBelumSO = '';
            if ($belum == '1') {
                $filterBelumSO = ' AND NO_BUKTI is null';
            }

            $result = DB::select("SELECT KD_BRG, NA_BRG, KET_UK, KET_KEM, BARCODE, STOK,
                                        coalesce(NO_BUKTI,'-') as NO_BUKTI, coalesce(TGL_SO,'') as TGL_SO, coalesce(QTY_SO,0) as QTY_SO, KET_SO FROM
                                        (
                                             SELECT x.KD_BRG, x.NA_BRG, x.KET_UK, x.KET_KEM, x.BARCODE, y.AK00+y.GAK00 as STOK,
                                         z.NO_BUKTI, date_format(z.TGL,'%d/%m/%Y') as TGL_SO, z.qty as QTY_SO, z.KET as KET_SO
                                             FROM brg x, brgdt y
                                             LEFT JOIN (SELECT a.NO_BUKTI, a.TGL, b.KD_BRG, b.ket, b.qty
                                               FROM stockbz a, stockbzd b
                                               WHERE a.NO_BUKTI=b.NO_BUKTI AND a.TGL_POSTED between '$tglDrD' and '$tglSmpD' AND a.POSTED=1)
                                         z on y.KD_BRG=z.KD_BRG
                                             WHERE x.KD_BRG=y.KD_BRG AND if('$sub'='', true, x.SUB='$sub')
                                        ) as rekap
                                        WHERE STOK>0 $filterBelumSO
                                        ORDER BY KD_BRG;");
            
            return $result;
        } catch (\Exception $e) {
            Log::error('Error in getBelumSO: ' . $e->getMessage());
            return [];
        }
    }

    private function escapeString($value)
    {
        // Ganti tanda kutip agar PHPJasperXML tidak crash
        $value = str_replace('"', '”', $value);   // tanda kutip ganda
        $value = str_replace("'", '’', $value);   // tanda kutip tunggal
        // Hapus newline atau carriage return
        $value = str_replace(["\r", "\n"], ' ', $value);
        // Hapus backslash
        $value = str_replace('\\', '', $value);
        return $value;
    }

    public function jasperBelumSOReport(Request $request)
    {   
        $file = 'belumso';
        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));
        $params = [
			"TGL_CTK" => date('d/m/Y'),
            "JAM"     => date('H:i:s'),
		];
		$PHPJasperXML->arrayParameter=$params;

        $tglDrD = date("Y-m-d", strtotime($request->tglDr));
        $tglSmpD = date("Y-m-d", strtotime($request->tglSmp));
        
        // Set session values
        session()->put('filter_sub', $request->sub);
        session()->put('filter_tglDari', $tglDrD);
        session()->put('filter_tglSampai', $tglSmpD);
        session()->put('filter_belum', $request->belum);

        $data = [];

        if (!empty($request->sub) && !empty($tglDrD) && !empty($tglSmpD)) {
            $belumSO = $this->getBelumSO($request->sub, $tglDrD, $tglSmpD, $request->belum);

            foreach ($belumSO as $key => $value) {
                $data[] = [
                    'KD_BRG' => isset($value->KD_BRG) ? $this->escapeString($value->KD_BRG) : '',
                    'NA_BRG' => isset($value->NA_BRG) ? $this->escapeString($value->NA_BRG) : '',
                    'KET_UK' => isset($value->KET_UK) ? $this->escapeString($value->KET_UK) : '',
                    'KET_KEM' => isset($value->KET_KEM) ? $this->escapeString($value->KET_KEM) : '',
                    'BARCODE' => isset($value->BARCODE) ? $this->escapeString($value->BARCODE) : '',
                    'STOK' => is_numeric($value->STOK) ? (float)$value->STOK : 0,
                    'TD_OD' => isset($value->TD_OD) ? $this->escapeString($value->TD_OD) : '',
                    'CAT_OD' => isset($value->CAT_OD) ? $this->escapeString($value->CAT_OD) : '',
                    'TGL_OD' => $value->TGL_OD ?? '',
                    'TGL_KSR' => $value->TGL_KSR ?? '',
                    'HARI' => is_numeric($value->HARI) ? (int)$value->HARI : 0,
                    'CBG' => isset($request->cbg) ? $this->escapeString($request->cbg) : '',
                    'FILTER_HARI' => $request->hari ?? 9999,
                    'TGL1' => $request->tgl1 ?? '',
                    'TGL2' => $request->tgl2 ?? '',
                    'FILTER_TGL' => $request->filter_tgl ? 'Ya' : 'Tidak'
                ];
            }
        }

        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I");
    }
}
