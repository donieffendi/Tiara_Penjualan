<?php

namespace App\Http\Controllers\OReport;

use App\Http\Controllers\Controller;
use App\Models\Master\Cbg;
use App\Models\Master\Perid;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;

class RSalesManagerController extends Controller
{
    /**
     * Halaman utama report - Route: /rkasirbantu
     */
    public function report()
    {
        $cbg = DB::SELECT("SELECT KODE FROM toko WHERE STA IN ('MA','CB','DC') ORDER BY NO_ID ASC");

        // Initialize session variables
        session()->put('filter_cbg', '');

        return view('oreport_sales_manager.report')->with([
            'cbg' => $cbg,
            'hasilKasirBantu' => []
        ]);
    }

    public function getKasirBantuReport(Request $request)
    {
        $listCbg = DB::SELECT("SELECT KODE FROM toko WHERE STA IN ('MA','CB','DC') ORDER BY NO_ID ASC");
        $tab = $request->tab ?? 'detail';

        switch ($tab) {

            case 'detail':
                if (empty($request->cbg)) {
                    return view('oreport_sales_manager.report')->with([
                        'cbg' => $listCbg,
                        'hasilKasirBantu' => [],
                        'error' => 'Cabang harus dipilih untuk tab Detail.',
                        'tab' => $tab
                    ]);
                }
                $hasilKasirBantu = $this->getDetailKasirBantu($request->cbg);
                break;

            case 'summary':
                if (empty($request->cbg)) {
                    return view('oreport_sales_manager.report')->with([
                        'cbg' => $listCbg,
                        'hasilKasirBantu' => [],
                        'error' => 'Cabang harus dipilih untuk tab Per Minggu.',
                        'tab' => $tab
                    ]);
                }
                $hasilKasirBantu = $this->getSummaryKasirBantu($request->cbg);
                break;

            // case 'kasir':
            //     if (empty($request->cbg)) {
            //         return view('oreport_sales_manager.report')->with([
            //             'cbg' => $listCbg,
            //             'hasilKasirBantu' => [],
            //             'error' => 'Cabang harus dipilih untuk tab kasir.'
            //         ]);
            //     }
            //     $hasilKasirBantu = $this->getKasirList($request->cbg);
            //     break;
        }

        return view('oreport_sales_manager.report')->with([
            'cbg' => $listCbg,
            'hasilKasirBantu' => $hasilKasirBantu,
            'tab' => $tab
        ]);
    }

    // public function getSalesManagerReportAjax(Request $request)
    // {
    //     try {
    //         $tab = $request->tab ?? 'detail';
    //         $cbg = $request->cbg ?? '';

    //         switch ($tab) {
    //             case 'detail':
    //                 if (!$cbg) {
    //                     return response()->json(['success' => false, 'message' => 'CBG wajib'], 400);
    //                 }
    //                 $data = $this->getDetailKasirBantu($cbg);
    //                 break;

    //             case 'summary':
    //                 if (!$cbg) {
    //                     return response()->json(['success' => false, 'message' => 'CBG wajib'], 400);
    //                 }
    //                 $data = $this->getSummaryKasirBantu($cbg);
    //                 break;

    //             default:
    //                 return response()->json([
    //                     'success' => false,
    //                     'message' => 'Invalid tab'
    //                 ], 400);
    //         }

    //         return response()->json([
    //             'success' => true,
    //             'data' => $data
    //         ]);

    //     } catch (\Exception $e) {
    //         \Log::error('KasirBantu Ajax Error: '.$e->getMessage(), [
    //             'trace' => $e->getTraceAsString()
    //         ]);
    //         return response()->json(['success' => false, 'message' => 'Internal Error'], 500);
    //     }
    // }

    public function getSalesManagerReportAjax(Request $request)
    {
        $tab = $request->tab ?? 'detail';
        $cbg = $request->cbg ?? '';
        // dd($request->all());
        try {
            switch ($tab) {
                case 'detail':
                    if (!$cbg) return response()->json(['success'=>false,'message'=>'CBG wajib'],400);
                    $data = $this->getDetailKasirBantu($cbg);
                    break;

                case 'summary':
                    if (!$cbg) return response()->json(['success'=>false,'message'=>'CBG wajib'],400);
                    $data = $this->getSummaryKasirBantu($cbg);
                    break;

                default:
                    return response()->json(['success'=>false,'message'=>'Tab tidak dikenal'],400);
            }

            return response()->json(['success'=>true,'data'=>$data]);

        } catch (\Exception $e) {
            // debug sementara
            return response()->json([
                'success'=>false,
                'message'=>$e->getMessage(),
                'trace'=>$e->getTraceAsString()
            ],500);
        }
    }


    /**
     * Generate laporan Jasper - Route: /jasper-kasirbantu-report
     * Implementasi dari logika Delphi untuk generate report
     */
    public function jasperSalesManagerReport(Request $request)
    {
        $tab = $request->tab ?? 'detail';
        $cbg = $request->cbg;

        // Tentukan file report berdasarkan tipe (sesuai dengan struktur Delphi)
        $file = ($tab == 'detail') ? 'rsales_manager_detail' : 'rsales_manager_summary';

        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

        // Store filter values in session
        session()->put('filter_cbg', $cbg);
        session()->put('report_type', $tab);

        $data = [];

        if (!empty($cbg)) {
            if ($tab == 'detail') {
                // Detail Report - repjuald data
                $results = $this->getDetailKasirBantu($cbg);

                foreach ($results as $row) {
                    $data[] = [
                        'CBG' => $row->CBG ?? '',
                        'SUB' => $row->SUB ?? '',
                        'SUB2' => $row->SUB2 ?? '',
                        'KD_BRG' => $row->KD_BRG ?? '',
                        'NA_BRG' => $row->NA_BRG ?? '',
                        'BARCODE' => $row->BARCODE ?? '',
                        'QTY' => $row->qty ?? 0,
                        'HARGA' => $row->harga ?? 0,
                        'DISKON' => $row->diskon ?? 0,
                        'DISC' => $row->disc ?? 0,
                        'PPN' => $row->ppn ?? '',
                        'NPPN' => $row->nppn ?? 0,
                        'DPP' => $row->dpp ?? 0,
                        'TKP' => $row->tkp ?? 0,
                        'TOTAL' => $row->total ?? 0,
                        'FLAG' => $row->flag ?? '',
                        'TYPE' => $row->type ?? '',
                        'PER' => $row->per ?? '',
                        'KODES' => $row->kodes ?? '',
                        'TGL' => $row->TGL ?? '',
                        'NAMA_TOKO' => $this->getNamaToko($cbg),
                        'REPORT_TYPE' => 'Laporan Detail Sales Manager',
                    ];
                }
            } else {
                // Summary Report - repjual data
                $results = $this->getSummaryKasirBantu($cbg);

                foreach ($results as $row) {
                    $data[] = array_merge((array) $row, [
                        'NAMA_TOKO' => $this->getNamaToko($cbg),
                        'REPORT_TYPE' => 'Laporan Summary Sales Manager',
                    ]);
                }
            }
        }

        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I");
    }


    private function getDetailKasirBantu($cbg)
    {
        $sql = "SELECT
                SUB,
                SUB2,
                KD_BRG,
                NA_BRG,
                BARCODE,
                qty,
                satuan,
                ket,
                harga,
                diskon,
                disc,
                ppn,
                nppn,
                dpp,
                tkp,
                total,
                margin,
                flag,
                `type`,
                per,
                kodes,
                namas,
                TGL,
                CBG,
                TYP,
                CEK,
                HAPUS
            FROM repjuald
            WHERE CBG = ?
            ORDER BY TGL
            LIMIT 100";

        return DB::select($sql, [$cbg]);
    }



    private function getSummaryKasirBantu($cbg)
    {
        $sql = "SELECT
                    MINGGU,
                    YER,
                    SUB,
                    SUB2,
                    KD_BRG,
                    NA_BRG,
                    BARCODE,
                    qty,
                    satuan,
                    ket,
                    harga,
                    diskon,
                    disc,
                    ppn,
                    nppn,
                    dpp,
                    tkp,
                    total,
                    margin,
                    flag,
                    `type`,
                    per,
                    kodes,
                    namas,
                    CBG,
                    TYP,
                    CEK,
                    HAPUS
                FROM repjual
                WHERE CBG = ?
                ORDER BY MINGGU
                LIMIT 100";

        return DB::select($sql, [$cbg]);
    }


    // public function getKasirList($cbg)
    // {
    //     $cbgTable = trim($cbg) . ".";  // untuk nama tabel
    //     $cbgParam = trim($cbg);        // untuk parameter WHERE

    //     $sql = "
    //         SELECT jual.no_bukti AS NO_BUKTI, jual.tgl AS TGL, jual.cbg AS CBG
    //         FROM {$cbgTable}jual jual
    //         WHERE jual.flag = 'OB'
    //         AND jual.cbg = ?
    //         GROUP BY no_bukti, tgl, cbg
    //         ORDER BY no_bukti
    //     ";

    //     return DB::select($sql, [$cbgParam]);
    // }

    /**
     * Get daftar cabang yang valid
     */
    public function getCabangList()
    {
        try {
            $query = "
                SELECT KODE, STA
                FROM toko
                WHERE STA IN ('MA', 'CB', 'DC')
                ORDER BY NO_ID ASC
            ";

            return DB::select($query);
        } catch (\Exception $e) {
            Log::error('Error in getCabangList: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Export ke Excel
     */
    public function exportToExcel(Request $request)
    {
        try {
            $tab = $request->tab ?? 'detail';
            $cbg = $request->cbg ?? '';

            switch ($tab) {

                case 'detail':
                    if (empty($cbg)) {
                        return response()->json(['error' => 'Cabang harus diisi!'], 400);
                    }
                    $data = $this->getDetailKasirBantu($cbg);
                    break;

                case 'summary':
                    if (empty($cbg)) {
                        return response()->json(['error' => 'Cabang harus diisi!'], 400);
                    }
                    $data = $this->getSummaryKasirBantu($cbg);
                    break;

                // case 'kasir':
                //     if (empty($cbg)) {
                //         return response()->json(['error' => 'Cabang harus diisi!'], 400);
                //     }
                //     $data = $this->getKasirList($cbg);
                //     break;
            }

            if (empty($data)) {
                return response()->json(['message' => 'Tidak ada data'], 200);
            }

            $filename = "kasirbantu_{$tab}_" . date('YmdHis') . ".xlsx";

            return response()->json([
                'success' => true,
                'filename' => $filename,
                'total' => count($data)
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    /**
     * Search kasir bantu dengan AJAX
     */
    public function searchSalesManager(Request $request)
    {
        try {
            $tab = $request->tab ?? 'detail';
            $cbg = $request->cbg ?? '';

            switch ($tab) {

                case 'detail':
                    if (empty($cbg)) {
                        return response()->json([
                            'success' => false,
                            'error' => 'Cabang harus diisi!'
                        ], 400);
                    }
                    $data = $this->getDetailKasirBantu($cbg);
                    break;

                case 'summary':
                    if (empty($cbg)) {
                        return response()->json([
                            'success' => false,
                            'error' => 'Cabang harus diisi!'
                        ], 400);
                    }
                    $data = $this->getSummaryKasirBantu($cbg);
                    break;

                // case 'kasir':
                //     if (empty($cbg)) {
                //         return response()->json([
                //             'success' => false,
                //             'error' => 'Cabang harus diisi!'
                //         ], 400);
                //     }
                //     $data = $this->getKasirList($cbg);
                //     break;
            }

            return response()->json([
                'success' => true,
                'data' => $data,
                'total_records' => count($data)
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get nama toko berdasarkan kode cabang
     */
    public function getNamaToko($cbg)
    {
        try {
            $result = DB::table('tgz.toko')
                ->select('NA_TOKO')
                ->where('KODE', $cbg)
                ->first();

            return $result ? $result->NA_TOKO : '';
        } catch (\Exception $e) {
            Log::error('Error in getNamaToko: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Method untuk preview data sebelum print
     */
    public function previewKasirBantu(Request $request)
    {
        try {
            $cbg = $request->cbg;

            if (empty($cbg)) {
                return redirect()->back()->with('error', 'Cabang harus diisi!');
            }

            $data = $this->getKasirList($cbg);
            $namaToko = $this->getNamaToko($cbg);

            return view('oreport_kasirbantu.preview')->with([
                'data' => $data,
                'cbg' => $cbg,
                'namaToko' => $namaToko,
                'totalRecords' => count($data)
            ]);
        } catch (\Exception $e) {
            Log::error('Error in previewKasir: ' . $e->getMessage());
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}