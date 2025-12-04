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

class RKealpaanSOController extends Controller
{
    public function report()
    {   
        // Initialize session variables
        session()->put('filter_tglDari', date("d-m-Y"));
        session()->put('filter_tglSampai', date("d-m-Y"));

        return view('oreport_kealpaanso.report')->with([
            'kealpaanSO' => []
        ]);
    }

    public function getKealpaanSOReport(Request $request)
    {
        $tglDrD = date("Y-m-d", strtotime($request->tglDr));
        $tglSmpD = date("Y-m-d", strtotime($request->tglSmp));

        // Set filter values to session
        session()->put('filter_tglDari', $request->tglDr);
        session()->put('filter_tglSampai', $request->tglSmp);

        $kealpaanSO = [];
        // dd($request->all());
        if (!empty($request->tglDr)) {
            // Validate kode tidak boleh kosong
            if (empty($request->tglSmp)) {
                return redirect()->back()->withErrors(['tglSmp' => 'Tanggal Tidak Boleh Kosong']);
            }

            // Get data barang macet
            $kealpaanSO = $this->getKealpaanSO($tglDrD, $tglSmpD);
        }

        return view('oreport_kealpaanso.report')->with([
            'kealpaanSO' => $kealpaanSO
        ]);
    }

    /**
     * Get data barang macet berdasarkan jenis yang dipilih
     * Equivalent dengan logic dalam procedure Tampil
     */
    private function getKealpaanSO($tglDrD, $tglSmpD)
    {
        try {

            $result = DB::select("SELECT date_format(tgl,'%d/%m/%Y') as TGL_SO, NO_SO, KD_BRG, NA_BRG, NO_BUKTI
                                        KET_UK, KET_KEM, BARCODE, JENIS, NOTAG, USRNM,
                                        (qty_rak+qty_rak_atas+qty_rak_bawah) as qty_so_tk, if(date(jam_toko)='2001-01-01','',time(jam_toko)) as jam_so_tk,
                                        (qty_gd_trans+qty_gd_lain) as qty_so_gd, if(date(jam_gudang)='2001-01-01','',time(jam_gudang)) as jam_so_gd
                                        FROM soscan
                                        WHERE NO_BUKTI='Y' AND date(jam_toko)='2001-01-01' AND date(jam_gudang)<>'2001-01-01'
                                        AND date(TGL) between '$tglDrD' and '$tglSmpD'");
            
            return $result;
        } catch (\Exception $e) {
            Log::error('Error in getKealpaanSO: ' . $e->getMessage());
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

    public function jasperKealpaanSOReport(Request $request)
    {   
        $file = 'kealpaanso';
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
        session()->put('filter_tglDari', $request->tglDr);
        session()->put('filter_tglSampai', $request->tglSmp);

        $data = [];

        if (!empty($tglDrD) && !empty($tglSmpD)) {
            $kealpaanSO = $this->getKealpaanSO($tglDrD, $tglSmpD);

            foreach ($kealpaanSO as $key => $value) {
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
