<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Yajra\DataTables\Facades\DataTables;
use PHPJasperXML;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

class TOrderLebihFreshFoodOnlineController extends Controller
{
    public function index(Request $request)
    {
        try {
            $judul = 'Transaksi Order Lebih Fresh Food Online';

            $CBG = Auth::user()->CBG ?? null;
            $username = Auth::user()->username ?? 'system';

            if (!$CBG) {
                return view("otransaksi_TOrderLebihFreshFoodOnline.index")->with([
                    'judul' => $judul,
                    'error' => 'User tidak memiliki akses cabang (CBG). Hubungi administrator.'
                ]);
            }

            if (!$request->session()->has('periode')) {
                return view("otransaksi_TOrderLebihFreshFoodOnline.index")->with([
                    'judul' => $judul,
                    'warning' => 'Periode belum diset. Silakan set periode terlebih dahulu.'
                ]);
            }

            $periode = $request->session()->get('periode');

            return view("otransaksi_TOrderLebihFreshFoodOnline.index")->with([
                'judul' => $judul,
                'cbg' => $CBG,
                'periode' => $periode,
                'username' => $username
            ]);
        } catch (\Exception $e) {
            Log::error('Error in TOrderLebihFreshFoodOnline index: ' . $e->getMessage());
            return view("otransaksi_TOrderLebihFreshFoodOnline.index")->with([
                'judul' => 'Transaksi Order Lebih Fresh Food Online',
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    // Tampil data GROUP BY NAMAFILE - sesuai Delphi Tampil procedure
    public function cari_data(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;

            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            Log::info('=== TOrderLebihFreshFoodOnline cari_data ===', [
                'CBG' => $CBG
            ]);

            // Query sederhana - hanya ambil data yang sudah punya NAMAFILE
            $query = "
                SELECT
                    NAMAFILE,
                    MIN(TGL) as TGL,
                    COUNT(*) as JUMLAH_ITEM,
                    SUM(qty) as TOTAL_QTY,
                    GROUP_CONCAT(DISTINCT KODES ORDER BY KODES SEPARATOR ', ') as SUPPLIERS
                FROM orderts
                WHERE flag = 'OL'
                AND CBG = ?
                AND NAMAFILE IS NOT NULL
                AND NAMAFILE != ''
                AND TGL >= CURDATE() - INTERVAL 1 DAY
                GROUP BY NAMAFILE
                ORDER BY TGL DESC
            ";

            $data = DB::select($query, [$CBG]);

            Log::info('Query executed successfully', ['count' => count($data)]);

            return Datatables::of(collect($data))
                ->addIndexColumn()
                ->editColumn('TGL', function ($row) {
                    return date('d-m-Y', strtotime($row->TGL));
                })
                ->editColumn('TOTAL_QTY', function ($row) {
                    return number_format($row->TOTAL_QTY, 2, ',', '.');
                })
                ->addColumn('action', function ($row) {
                    return '
                        <button class="btn btn-sm btn-info btn-view" data-namafile="' . $row->NAMAFILE . '" title="Lihat Detail">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-primary btn-print" data-namafile="' . $row->NAMAFILE . '" title="Print">
                            <i class="fas fa-print"></i>
                        </button>
                        <button class="btn btn-sm btn-success btn-export" data-namafile="' . $row->NAMAFILE . '" title="Export DBF">
                            <i class="fas fa-file-export"></i>
                        </button>
                        <button class="btn btn-sm btn-danger btn-delete" data-namafile="' . $row->NAMAFILE . '" title="Hapus">
                            <i class="fas fa-trash"></i>
                        </button>
                    ';
                })
                ->rawColumns(['action'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('Error in cari_data: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    // Get detail by NAMAFILE - untuk view detail saat double click
    public function detail(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            $namafile = $request->input('namafile');

            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            if (!$namafile) {
                return response()->json(['error' => 'NAMAFILE tidak ditemukan'], 400);
            }

            Log::info('=== TOrderLebihFreshFoodOnline detail ===', [
                'CBG' => $CBG,
                'NAMAFILE' => $namafile
            ]);

            // Query untuk detail items
            $query = "
                SELECT
                    o.rec,
                    o.SUB,
                    o.KDBAR,
                    o.KD_BRG,
                    o.NA_BRG,
                    b.KET_UK,
                    b.KET_KEM,
                    bd.LPH,
                    bd.AK00 as STOCK,
                    o.qty as QTY,
                    o.KODES as SUPP,
                    COALESCE(s.NA_SUPP, 'SUPPLIER') as NAMA_SUPP,
                    DATE_FORMAT(o.TGL, '%d-%m-%Y') as TGL
                FROM orderts o
                LEFT JOIN brg b ON o.KD_BRG = b.KD_BRG
                LEFT JOIN brgdt bd ON o.KD_BRG = bd.KD_BRG AND bd.CBG = ?
                LEFT JOIN supp s ON o.KODES = s.KD_SUPP
                WHERE o.NAMAFILE = ?
                AND o.flag = 'OL'
                AND o.CBG = ?
                ORDER BY o.KODES ASC, o.KD_BRG ASC
            ";

            $data = DB::select($query, [$CBG, $namafile, $CBG]);

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Error in detail: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal mengambil detail: ' . $e->getMessage()], 500);
        }
    }

    // Proses untuk berbagai action
    public function proses(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;

            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            $action = $request->input('action', '');

            Log::info('TOrderLebihFreshFoodOnline proses', [
                'CBG' => $CBG,
                'action' => $action
            ]);

            DB::beginTransaction();

            switch ($action) {
                case 'delete':
                    return $this->deleteByNamafile($request, $CBG);

                case 'export_dbf':
                    return $this->exportDbf($request, $CBG);

                default:
                    DB::rollBack();
                    return response()->json(['error' => 'Action tidak valid'], 400);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in proses: ' . $e->getMessage());
            return response()->json([
                'error' => 'Proses gagal: ' . $e->getMessage()
            ], 500);
        }
    }

    // Delete by NAMAFILE - sesuai KeyUp Delete di Delphi
    private function deleteByNamafile($request, $CBG)
    {
        $namafile = $request->input('namafile');

        if (!$namafile) {
            DB::rollBack();
            return response()->json(['error' => 'NAMAFILE tidak ditemukan'], 400);
        }

        Log::info('Deleting order by NAMAFILE', [
            'NAMAFILE' => $namafile,
            'CBG' => $CBG
        ]);

        // DELETE FROM ord_lebih_ts_kd3 where NAMAFILE = :BKT
        DB::statement("
            DELETE FROM orderts
            WHERE NAMAFILE = ?
            AND flag = 'OL'
            AND CBG = ?
        ", [$namafile, $CBG]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil dihapus!'
        ]);
    }

    // Export DBF - sesuai dxBarButton2Click dan exportDbf di Delphi
    private function exportDbf($request, $CBG)
    {
        $namafile = $request->input('namafile');

        if (!$namafile) {
            DB::rollBack();
            return response()->json(['error' => 'NAMAFILE tidak ditemukan'], 400);
        }

        Log::info('Export DBF started', [
            'NAMAFILE' => $namafile,
            'CBG' => $CBG
        ]);

        // Get data untuk export
        $data = DB::select("
            SELECT *
            FROM orderts
            WHERE NAMAFILE = ?
            AND flag = 'OL'
            AND CBG = ?
            ORDER BY KODES, KD_BRG
        ", [$namafile, $CBG]);

        if (empty($data)) {
            DB::rollBack();
            return response()->json(['error' => 'Tidak ada data untuk di-export'], 404);
        }

        // Kirim ke API eksternal - sesuai Delphi
        // urlx := 'http://10.10.30.132:8080/export-dbf-app/public/api/export-ord-lebih';
        try {
            $url = 'http://10.10.30.132:8080/export-dbf-app/public/api/export-ord-lebih';

            $response = Http::timeout(30)->post($url, [
                'bkt' => $namafile,
                'cbg' => $CBG,
                'data' => $data
            ]);

            Log::info('API Response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if ($response->status() == 200) {
                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Data berhasil dikirim!'
                ]);
            } else {
                DB::rollBack();

                return response()->json([
                    'error' => 'Gagal mengirim data. Status: ' . $response->status()
                ], 500);
            }
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error sending to API', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Gagal mengirim data: ' . $e->getMessage()
            ], 500);
        }
    }

    // Print by NAMAFILE - sesuai ButtonPrintClick di Delphi
    public function jasperPrint(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            $username = Auth::user()->username ?? 'system';
            $namafile = $request->input('namafile');

            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            if (!$namafile) {
                return response()->json(['error' => 'NAMAFILE tidak ditemukan'], 400);
            }

            Log::info('=== TOrderLebihFreshFoodOnline jasperPrint ===', [
                'CBG' => $CBG,
                'NAMAFILE' => $namafile
            ]);

            // Query dengan filter NAMAFILE - sesuai Delphi ButtonPrintClick
            // SELECT * FROM ord_lebih_ts_kd3 WHERE NAMAFILE=:XD ORDER BY SUPP, KD_BRG
            $data = DB::select("
                SELECT
                    o.rec,
                    o.SUB,
                    o.KDBAR,
                    o.KD_BRG,
                    o.NA_BRG,
                    b.KET_UK,
                    b.KET_KEM,
                    bd.LPH,
                    bd.AK00 as STOCK,
                    o.qty as QTY,
                    o.KODES as SUPP,
                    COALESCE(s.NA_SUPP, 'SUPPLIER') as NAMA_SUPP,
                    DATE_FORMAT(o.TGL, '%d-%m-%Y') as TGL_ORDER,
                    DATE_FORMAT(NOW(), '%H:%i:%s') as JAM
                FROM orderts o
                LEFT JOIN brg b ON o.KD_BRG = b.KD_BRG
                LEFT JOIN brgdt bd ON o.KD_BRG = bd.KD_BRG AND bd.CBG = ?
                LEFT JOIN supp s ON o.KODES = s.KD_SUPP
                WHERE o.NAMAFILE = ?
                AND o.flag = 'OL'
                AND o.CBG = ?
                ORDER BY o.KODES ASC, o.KD_BRG ASC
            ", [$CBG, $namafile, $CBG]);

            if (empty($data)) {
                return response()->json(['error' => 'Tidak ada data untuk dicetak'], 404);
            }

            Log::info('Data found for print', ['count' => count($data)]);

            $tglOrder = !empty($data) ? $data[0]->TGL_ORDER : date('d-m-Y');
            $jam = !empty($data) ? $data[0]->JAM : date('H:i:s');

            // Prepare data untuk Jasper
            $reportData = [];
            $no = 1;

            foreach ($data as $row) {
                $reportData[] = [
                    'NO' => $no++,
                    'SUB' => $row->SUB ?? '',
                    'KDBAR' => $row->KDBAR ?? '',
                    'KD_BRG' => $row->KD_BRG,
                    'NA_BRG' => $row->NA_BRG,
                    'KET_UK' => $row->KET_UK ?? '',
                    'KET_KEM' => $row->KET_KEM ?? '',
                    'LPH' => (float)($row->LPH ?? 0),
                    'STOCK' => (float)($row->STOCK ?? 0),
                    'QTY' => (float)($row->QTY ?? 0),
                    'SUPP' => $row->SUPP ?? '',
                    'NAMA_SUPP' => $row->NAMA_SUPP ?? 'SUPPLIER'
                ];
            }

            // Generate Jasper Report
            $file = 'order_lebih_freshfood';
            $PHPJasperXML = new PHPJasperXML();
            $PHPJasperXML->load_xml_file(base_path("/app/reportc01/phpjasperxml/{$file}.jrxml"));

            // Convert to plain array
            $cleanData = json_decode(json_encode($reportData), true);

            // Set parameters
            $PHPJasperXML->arrayParameter = [
                "JUDUL" => "LAPORAN ORDER LEBIH TS KODE 3 - ONLINE",
                "CBG" => $CBG,
                "USERNAME" => $username,
                "TGL_CETAK" => date('d-m-Y H:i:s'),
                "NAMAFILE" => $namafile,
                "TGL_ORDER" => $tglOrder,
                "JAM" => $jam
            ];

            $PHPJasperXML->setData($cleanData);

            // Clear output buffer
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            Log::info('=== Generating PDF with PHPJasperXML ===');

            // Output PDF inline
            $PHPJasperXML->outpage("I");
            exit;
        } catch (\Exception $e) {
            Log::error('=== Error in jasperPrint ===', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return response()->json([
                'error' => 'Gagal mencetak: ' . $e->getMessage()
            ], 500);
        }
    }
}
