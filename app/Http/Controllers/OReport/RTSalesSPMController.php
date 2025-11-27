<?php

namespace App\Http\Controllers\OReport;

use App\Http\Controllers\Controller;
use App\Models\Master\Cbg;
use App\Models\Master\Perid;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;

class RTSalesSPMController extends Controller
{
    public function report()
    {
        $cbg = Cbg::groupBy('CBG')->get();

        // Initialize session filters
        session()->put('filter_cbg', '');
        session()->put('filter_gol', '');
        session()->put('filter_kodes1', '');
        session()->put('filter_namas1', '');
        session()->put('filter_brg1', '');
        session()->put('filter_nabrg1', '');
        session()->put('filter_tglDari', date("d-m-Y"));
        session()->put('filter_tglSampai', date("d-m-Y"));

        $per = Perid::query()->get();
        $user = User::query()->get();

        // Get kasir/user list for dropdown (handle missing table error)
        try {
            $kasir = DB::table('noks')->select('kasir')->get();
        } catch (\Illuminate\Database\QueryException $e) {
            $kasir = null;
        }

        return view('otransaksi_SalesSPM.report')->with([
            'cbg' => $cbg,
            'per' => $per,
            'user' => $user,
            'kasir' => $kasir,
            'hasil' => []
        ]);
    }

    /**
     * Get TSalesSPM Report data - equivalent to Button1Click in Delphi
     */
    public function getTSalesSPMReport(Request $request)
    {
        try {
            // Validate required parameters
            $request->validate([
                'ksr' => 'required',
                'tgl1' => 'required|date'
            ]);

            $bulan = $this->getBulanFromPeriode();
            $cbg = session('current_cbg', ''); // Get current CBG from session

            // Build table names dynamically
            $cbgPrefix = !empty($cbg) ? $cbg . '.' : '';
            $jualTable = $cbgPrefix . 'jual' . $bulan;
            $jualdTable = $cbgPrefix . 'juald' . $bulan;

            // Get form details (equivalent to Delphi's report form setup)
            $formDetails = $this->getReportFormDetails();

            $ksr = trim($request->ksr);
            $tgl1 = date('Y-m-d', strtotime($request->tgl1));

            // Main query - equivalent to PERINCIAN query in Delphi
            $sql = "SELECT
                ? as no_form,
                ? as na_toko,
                ? as typ_pers,
                ? as alamat_pers,
                {$jualTable}.cbg,
                {$jualTable}.KSR,
                {$jualTable}.SHIFT,
                {$jualTable}.tgl,
                LEFT(TRIM({$jualdTable}.KD_BRG), 3) AS SUB,
                {$jualdTable}.KD_BRG,
                {$jualdTable}.NA_BRG,
                SUM({$jualdTable}.qty) as qty,
                {$jualdTable}.harga,
                {$jualdTable}.ppn,
                SUM({$jualdTable}.nppn) as nppn,
                SUM({$jualdTable}.dpp) as dpp,
                SUM({$jualdTable}.tkp) as tkp,
                SUM({$jualdTable}.total) as total,
                CASE
                    WHEN {$jualdTable}.ppn = '1' THEN 'PPN Barang Produksi'
                    WHEN {$jualdTable}.ppn = '2' THEN 'PPN Barang Cukai'
                    WHEN {$jualdTable}.ppn = '3' THEN 'PPN Barang Import'
                    ELSE 'Tanpa PPN'
                END AS PPN_KET
            FROM {$jualTable}, {$jualdTable}
            WHERE {$jualTable}.no_bukti = {$jualdTable}.no_bukti
                AND {$jualdTable}.KD_BRG <> ''
                AND {$jualTable}.flag = 'JL'
                AND {$jualTable}.ksr = ?
                AND {$jualTable}.tgl = ?
            GROUP BY
                {$jualTable}.CBG,
                {$jualTable}.ksr,
                {$jualTable}.SHIFT,
                {$jualTable}.tgl,
                {$jualdTable}.KD_BRG,
                {$jualdTable}.ppn
            ORDER BY
                {$jualTable}.CBG,
                {$jualTable}.KSR,
                {$jualTable}.SHIFT,
                {$jualdTable}.PPN,
                {$jualdTable}.KD_BRG";

            $hasil = DB::select($sql, [
                $formDetails['no_form'],
                $formDetails['na_toko'],
                $formDetails['typ_pers'],
                $formDetails['alamat_pers'],
                $ksr,
                $tgl1
            ]);

            // Update session filters
            session()->put('filter_ksr', $ksr);
            session()->put('filter_tgl1', $request->tgl1);

            // Return as JSON for AJAX requests
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $hasil,
                    'message' => 'Data berhasil dimuat'
                ]);
            }

            // Return view for regular requests
            $cbg = Cbg::groupBy('CBG')->get();
            $per = Perid::query()->get();
            $user = User::query()->get();
            try {
                $kasir = DB::table('noks')->select('kasir')->get();
            } catch (\Illuminate\Database\QueryException $e) {
                $kasir = null;
            }

            return view('otransaksi_SalesSPM.report')->with([
                'cbg' => $cbg,
                'per' => $per,
                'user' => $user,
                'kasir' => $kasir,
                'hasil' => $hasil,
            ]);
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ], 500);
            }

            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    /**
     * Generate Jasper Report - equivalent to frxRPerincian.ShowReport() in Delphi
     */
    public function jasperTSalesSPMReport(Request $request)
    {
        try {
            // Validate required parameters
            $request->validate([
                'ksr' => 'required',
                'tgl1' => 'required|date'
            ]);

            $bulan = $this->getBulanFromPeriode();
            $cbg = session('current_cbg', '');

            // Build table names dynamically
            $cbgPrefix = !empty($cbg) ? $cbg . '.' : '';
            $jualTable = $cbgPrefix . 'jual' . $bulan;
            $jualdTable = $cbgPrefix . 'juald' . $bulan;

            // Get form details
            $formDetails = $this->getReportFormDetails();

            $ksr = trim($request->ksr);
            $tgl1 = date('Y-m-d', strtotime($request->tgl1));

            // Execute the same query as getTSalesSPMReport
            $sql = "SELECT
                ? as no_form,
                ? as na_toko,
                ? as typ_pers,
                ? as alamat_pers,
                {$jualTable}.cbg,
                {$jualTable}.KSR,
                {$jualTable}.SHIFT,
                {$jualTable}.tgl,
                LEFT(TRIM({$jualdTable}.KD_BRG), 3) AS SUB,
                {$jualdTable}.KD_BRG,
                {$jualdTable}.NA_BRG,
                SUM({$jualdTable}.qty) as qty,
                {$jualdTable}.harga,
                {$jualdTable}.ppn,
                SUM({$jualdTable}.nppn) as nppn,
                SUM({$jualdTable}.dpp) as dpp,
                SUM({$jualdTable}.tkp) as tkp,
                SUM({$jualdTable}.total) as total,
                CASE
                    WHEN {$jualdTable}.ppn = '1' THEN 'PPN Barang Produksi'
                    WHEN {$jualdTable}.ppn = '2' THEN 'PPN Barang Cukai'
                    WHEN {$jualdTable}.ppn = '3' THEN 'PPN Barang Import'
                    ELSE 'Tanpa PPN'
                END AS PPN_KET
            FROM {$jualTable}, {$jualdTable}
            WHERE {$jualTable}.no_bukti = {$jualdTable}.no_bukti
                AND {$jualdTable}.KD_BRG <> ''
                AND {$jualTable}.flag = 'JL'
                AND {$jualTable}.ksr = ?
                AND {$jualTable}.tgl = ?
            GROUP BY
                {$jualTable}.CBG,
                {$jualTable}.ksr,
                {$jualTable}.SHIFT,
                {$jualTable}.tgl,
                {$jualdTable}.KD_BRG,
                {$jualdTable}.ppn
            ORDER BY
                {$jualTable}.CBG,
                {$jualTable}.KSR,
                {$jualTable}.SHIFT,
                {$jualdTable}.PPN,
                {$jualdTable}.KD_BRG";

            $query = DB::select($sql, [
                $formDetails['no_form'],
                $formDetails['na_toko'],
                $formDetails['typ_pers'],
                $formDetails['alamat_pers'],
                $ksr,
                $tgl1
            ]);

            // Prepare data for Jasper Report
            $data = [];
            foreach ($query as $key => $value) {
                $data[] = [
                    'NO_FORM' => $value->no_form ?? '',
                    'NA_TOKO' => $value->na_toko ?? '',
                    'TYP_PERS' => $value->typ_pers ?? '',
                    'ALAMAT_PERS' => $value->alamat_pers ?? '',
                    'CBG' => $value->cbg ?? '',
                    'KSR' => $value->KSR ?? '',
                    'SHIFT' => $value->SHIFT ?? '',
                    'TGL' => $value->tgl ?? '',
                    'SUB' => $value->SUB ?? '',
                    'KD_BRG' => $value->KD_BRG ?? '',
                    'NA_BRG' => $value->NA_BRG ?? '',
                    'QTY' => $value->qty ?? 0,
                    'HARGA' => $value->harga ?? 0,
                    'PPN' => $value->ppn ?? '',
                    'NPPN' => $value->nppn ?? 0,
                    'DPP' => $value->dpp ?? 0,
                    'TKP' => $value->tkp ?? 0,
                    'TOTAL' => $value->total ?? 0,
                    'PPN_KET' => $value->PPN_KET ?? '',
                ];
            }

            // Generate Jasper Report
            $file = 'salesSPM'; // Report template name
            $PHPJasperXML = new PHPJasperXML();
            $PHPJasperXML->load_xml_file(base_path() . '/app/reportc01/phpjasperxml/' . $file . '.jrxml');

            $PHPJasperXML->transferDBtoArray("localhost", "root", "", "tiara");
            $PHPJasperXML->setData($data);

            ob_end_clean();
            $PHPJasperXML->outpage("I"); // I = Inline display

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Terjadi kesalahan saat generate report: ' . $e->getMessage()]);
        }
    }

    /**
     * Get current month from periode - equivalent to LeftStr function in Delphi
     */
    private function getBulanFromPeriode()
    {
        // This should get the current period from session or system
        // Equivalent to: bulan:=LeftStr(frmmenu.FrMenu.periode.Caption,2);
        $periode = session('current_periode', date('m')); // Default to current month
        return substr($periode, 0, 2);
    }

    /**
     * Get report form details - equivalent to Delphi's report form setup
     */
    private function getReportFormDetails()
    {
        // This should implement the logic equivalent to Delphi's:
        // frmmenu.xNA_FRM, frmmenu.xNA_MENU, etc.

        return [
            'no_form' => session('report_no_form', 'RT-SALES-SPM'),
            'na_toko' => session('report_na_toko', config('app.name')),
            'typ_pers' => session('report_typ_pers', 'PERUSAHAAN'),
            'alamat_pers' => session('report_alamat_pers', 'Alamat Perusahaan'),
        ];
    }

    /**
     * Legacy method - keeping for backward compatibility
     */
    public function jasperKode8Report(Request $request)
    {
        // Keep the original method but redirect to new method
        return $this->jasperTSalesSPMReport($request);
    }
}
