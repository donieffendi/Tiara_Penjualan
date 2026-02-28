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

class RSalesPenjualanEDCController extends Controller
{
    public function report()
    {
        $cbg = Cbg::groupBy('CBG')->get();
        $per = Perid::query()->get();
        
        // Initialize session variables
        session()->put('filter_cbg', '');
        session()->put('filter_per', '');

        return view('oreport_sales_penjualan_edc.report')->with([
            'cbg' => $cbg,
            'per' => $per,
            'EDC' => []
        ]);
    }

    public function getSalesPenjualanEDCReport(Request $request)
    {
        $listCBG = Cbg::groupBy('CBG')->get(); // ⬅ hanya untuk dropdown list
        $listPER = Perid::query()->get();
        $per = $request->per;
        $cbg = $request->cbg; 

        // Set filter values to session
        session()->put('filter_cbg', $request->cbg);
        session()->put('filter_per', $request->per);

        $EDC = [];

        if (!empty($request->cbg)) {
            // Validate kode tidak boleh kosong
            if (empty($request->per)) {
                return redirect()->back()->withErrors(['per' => 'Periode Tidak Boleh Kosong']);
            }

            // Get data barang macet
            $EDC = $this->getData($request->cbg, $request->per);
            
        }

        return view('oreport_sales_penjualan_edc.report')->with([
            'cbg' => $listCBG,
            'per' => $listPER,
            'EDC' => $EDC
        ]);
    }


    /**
     * Get data barang macet berdasarkan jenis yang dipilih
     * Equivalent dengan logic dalam procedure Tampil
     */
    private function getData($cbg, $per)
    {   
        $cbgCode = strtolower($cbg); // Convert CBG to lowercase $cbg;
        $MM = substr($per, 0, 2);
        try {
            $result = DB::select("SELECT A.NO_BUKTI, A.TGL, A.KODEC, A.NAMAC, B.TYPE2, B.JUMLAH, B.NKARTU, B.NBANK, B.MID, B.RESI
                            FROM {$cbgCode}.jual{$MM} A, {$cbgCode}.jualby{$MM} B
                            WHERE A.NO_BUKTI=B.NO_BUKTI AND B.TYPE='BANK'  
                            ORDER BY B.NBANK ASC, A.TGL ASC");
            
            return $result;
        } catch (\Exception $e) {
            Log::error('Error in getStokKD: ' . $e->getMessage());
            return [];
        }
    }

    public function jasperSalesPenjualanEDCReport(Request $request)
    {   
        $file = 'sales_penjualan_edc';        
        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));
        $params = [
			"TGL_CTK" => date('d/m/Y'),
            "JAM"     => date('H:i:s'),
		];
		$PHPJasperXML->arrayParameter=$params;

        // Set session values
        session()->put('filter_cbg', $request->cbg);
        session()->put('filter_per', $request->per);

        $data = [];

        if (!empty($request->cbg) && !empty($request->per)) {
            $EDC = $this->getData($request->cbg, $request->per);

            foreach ($EDC as $key => $value) {
                $data[] = [
                    'NO_BUKTI' => $EDC[$key]->NO_BUKTI,
                    'TGL' => $EDC[$key]->TGL,
                    'KODEC' => $EDC[$key]->KODEC,
                    'NAMAC' => $EDC[$key]->NAMAC,
                    'TYPE2' => $EDC[$key]->TYPE2,
                    'MID' => $EDC[$key]->MID,
                    'JUMLAH' => $EDC[$key]->JUMLAH,
                    'NBANK' => $EDC[$key]->NBANK,
                    'NKARTU' => $EDC[$key]->NKARTU,
                    'RESI' => $EDC[$key]->RESI,
                ];
            }
        }

        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I");
    }
}