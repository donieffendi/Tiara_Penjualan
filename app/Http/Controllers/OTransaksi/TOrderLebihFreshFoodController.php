<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use PHPJasperXML;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

class TOrderLebihFreshFoodController extends Controller
{
    public function index(Request $request)
    {
        try {
            $judul = 'Transaksi Order Lebih Fresh Food';

            $CBG = Auth::user()->CBG ?? null;
            $username = Auth::user()->username ?? 'system';

            if (!$CBG) {
                return view("otransaksi_TOrderLebihFreshFood.index")->with([
                    'judul' => $judul,
                    'error' => 'User tidak memiliki akses cabang (CBG). Hubungi administrator.'
                ]);
            }

            if (!$request->session()->has('periode')) {
                return view("otransaksi_TOrderLebihFreshFood.index")->with([
                    'judul' => $judul,
                    'warning' => 'Periode belum diset. Silakan set periode terlebih dahulu.'
                ]);
            }

            $periode = $request->session()->get('periode');

            return view("otransaksi_TOrderLebihFreshFood.index")->with([
                'judul' => $judul,
                'cbg' => $CBG,
                'periode' => $periode,
                'username' => $username
            ]);
        } catch (\Exception $e) {
            Log::error('Error in TOrderLebihFreshFood index: ' . $e->getMessage());
            return view("otransaksi_TOrderLebihFreshFood.index")->with([
                'judul' => 'Transaksi Order Lebih Fresh Food',
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    // Ambil data untuk datatables
    public function cari_data(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            $username = Auth::user()->username ?? 'system';

            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            Log::info('=== TOrderLebihFreshFood cari_data START ===', [
                'CBG' => $CBG,
                'username' => $username
            ]);

            $periode = $request->session()->get('periode');
            if (!$periode) {
                return response()->json(['error' => 'Periode belum diset'], 400);
            }

            // Query untuk menampilkan data order lebih (FLAG='OL')
            $query = "
                SELECT
                    o.rec,
                    o.SUB,
                    o.KDBAR,
                    o.KD_BRG,
                    o.NA_BRG,
                    o.ket_kem as KET_KEM,
                    o.qty as QTY,
                    o.KODES as SUPP,
                    DATE_FORMAT(o.TGL, '%d-%m-%Y') as TGL_KIRIM,
                    o.TGL as TGL_RAW,
                    ? as USER
                FROM orderts o
                WHERE o.flag = 'OL'
                AND o.CBG = ?
                ORDER BY o.KD_BRG ASC
            ";

            $data = DB::select($query, [$username, $CBG]);

            Log::info('=== TOrderLebihFreshFood cari_data SUCCESS ===', [
                'data_count' => count($data)
            ]);

            return Datatables::of(collect($data))
                ->addIndexColumn()
                ->editColumn('QTY', function ($row) {
                    return number_format($row->QTY, 2, ',', '.');
                })
                ->addColumn('action', function ($row) {
                    return '<button class="btn btn-sm btn-danger btn-delete-item" data-rec="' . $row->rec . '" title="Hapus Item">
                                <i class="fas fa-trash"></i>
                            </button>';
                })
                ->rawColumns(['action'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('=== TOrderLebihFreshFood cari_data ERROR ===', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Gagal memuat data: ' . $e->getMessage()], 500);
        }
    }

    // Lookup barang - popup daftar barang kode 3 (fresh food)
    public function lookup_barang(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;

            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            Log::info('TOrderLebihFreshFood lookup_barang', [
                'CBG' => $CBG
            ]);

            // Query untuk barang fresh food (kode 3)
            // Menggunakan LEFT(KD_BRG,1)='3' untuk filter fresh food
            $query = "
                SELECT
                    b.KD_BRG as kd_brg,
                    b.NA_BRG as na_brg,
                    b.KET_UK as ket_uk,
                    b.KET_KEM as ket_kem,
                    b.SATUAN as satuan,
                    '3' as klk
                FROM brg b
                WHERE LEFT(b.KD_BRG, 1) = '3'
                ORDER BY b.KD_BRG ASC
                LIMIT 1000
            ";

            $data = DB::select($query);

            Log::info('TOrderLebihFreshFood lookup_barang - raw_query_untuk_navicat', [
                'query' => $query,
                'result_count' => count($data)
            ]);

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Error in lookup_barang: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    // Proses untuk berbagai action
    public function proses(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            $username = Auth::user()->username ?? 'system';

            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            Log::info('TOrderLebihFreshFood proses', [
                'CBG' => $CBG,
                'action' => $request->input('action')
            ]);

            $action = $request->input('action', '');

            DB::beginTransaction();

            switch ($action) {
                case 'save':
                    return $this->saveOrder($request, $CBG, $username);

                case 'refresh':
                    return $this->refreshData($request, $CBG);

                case 'delete_item':
                    return $this->deleteItem($request, $CBG);

                case 'delete_all':
                    return $this->deleteAll($request, $CBG);

                case 'print':
                    return $this->printOrder($request, $CBG, $username);

                case 'export_excel':
                    return $this->exportExcel($request, $CBG, $username);

                case 'jasper':
                    return $this->generateJasperPDF($request, $CBG, $username);

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

    private function saveOrder($request, $CBG, $username)
    {
        $kd_brg = trim($request->input('kd_brg', ''));
        $qty = $request->input('qty', 0);

        if (empty($kd_brg)) {
            DB::rollBack();
            return response()->json(['error' => 'Kode barang tidak boleh kosong'], 400);
        }

        // Cek apakah barang ada di master
        $barang = DB::selectOne("
            SELECT
                SUB as sub,
                KDBAR as kdbar,
                KD_BRG,
                CONCAT(NA_BRG, ' ', ket_uk) as na_brg,
                ket_kem,
                SUPP as supp
            FROM brg
            WHERE KD_BRG = ?
        ", [$kd_brg]);

        if (!$barang) {
            DB::rollBack();
            return response()->json(['error' => 'Kode barang tidak ditemukan'], 404);
        }

        // Cek apakah sudah ada di orderts dengan FLAG='OL'
        $existing = DB::selectOne("
            SELECT rec
            FROM orderts
            WHERE KD_BRG = ?
            AND flag = 'OL'
            AND CBG = ?
        ", [$kd_brg, $CBG]);

        if ($existing) {
            DB::rollBack();
            return response()->json(['error' => 'Barang sudah ada dalam daftar order'], 400);
        }

        // Generate NAMAFILE untuk tracking
        $namaFile = $CBG . '_OL_' . date('Ymd');

        // Insert ke orderts
        DB::statement("
            INSERT INTO orderts (
                SUB, KDBAR, KD_BRG, NA_BRG, ket_kem, qty, KODES, TGL, NAMAFILE, flag, CBG
            ) VALUES (?, ?, ?, ?, ?, ?, ?, CURDATE(), ?, 'OL', ?)
        ", [
            $barang->sub,
            $barang->kdbar,
            $barang->KD_BRG,
            $barang->na_brg,
            $barang->ket_kem,
            $qty,
            $barang->supp,
            $namaFile,
            $CBG
        ]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil ditambahkan!'
        ]);
    }

    private function refreshData($request, $CBG)
    {
        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil direfresh!'
        ]);
    }

    private function deleteItem($request, $CBG)
    {
        $rec = $request->input('rec');

        if (!$rec) {
            DB::rollBack();
            return response()->json(['error' => 'Record tidak ditemukan'], 400);
        }

        DB::statement("
            DELETE FROM orderts
            WHERE rec = ? AND CBG = ? AND flag = 'OL'
        ", [$rec, $CBG]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Item berhasil dihapus!'
        ]);
    }

    private function deleteAll($request, $CBG)
    {
        DB::statement("
            DELETE FROM orderts
            WHERE CBG = ? AND flag = 'OL'
        ", [$CBG]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Semua data berhasil dihapus!'
        ]);
    }

    private function printOrder($request, $CBG, $username)
    {
        // Redirect to Jasper print
        return $this->generateJasperPDF($request, $CBG, $username);
    }

    private function exportExcel($request, $CBG, $username)
    {
        // Get data untuk export excel
        $data = DB::select("
            SELECT
                o.SUB as 'Sub Item',
                o.KDBAR as 'Kode Barang',
                o.KD_BRG as 'Kode BRG',
                o.NA_BRG as 'Nama Barang',
                o.ket_kem as 'Kemasan',
                o.qty as 'Qty',
                o.KODES as 'SUPP',
                DATE_FORMAT(o.TGL, '%d-%m-%Y') as 'Tgl Kirim'
            FROM orderts o
            WHERE o.flag = 'OL'
            AND o.CBG = ?
            ORDER BY o.KD_BRG ASC
        ", [$CBG]);

        if (empty($data)) {
            DB::rollBack();
            return response()->json(['error' => 'Tidak ada data untuk di-export'], 404);
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    // Method publik untuk dipanggil dari route
    public function jasperPrint(Request $request)
    {
        $CBG = Auth::user()->CBG ?? null;
        $username = Auth::user()->username ?? 'system';

        if (!$CBG) {
            return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
        }

        return $this->generateJasperPDF($request, $CBG, $username);
    }

    private function generateJasperPDF($request, $CBG, $username)
    {
        try {
            Log::info('=== TOrderLebihFreshFood generateJasperPDF START ===', [
                'CBG' => $CBG,
                'username' => $username
            ]);

            // Get data untuk print - JOIN dengan tabel brg dan sup untuk data lengkap
            $data = DB::select("
                SELECT
                    o.rec,
                    o.SUB,
                    o.KDBAR,
                    o.KD_BRG,
                    o.NA_BRG,
                    b.KET_UK,
                    b.KET_KEM,
                    COALESCE(bd.LPH, 0) as LPH,
                    COALESCE(bd.AK00, 0) as STOCK,
                    o.qty as QTY,
                    o.KODES as SUPP,
                    COALESCE(s.NAMAS, 'SUPPLIER') as NAMA_SUPP,
                    DATE_FORMAT(o.TGL, '%d-%m-%Y') as TGL_ORDER,
                    DATE_FORMAT(NOW(), '%H:%i:%s') as JAM
                FROM orderts o
                LEFT JOIN brg b ON o.KD_BRG = b.KD_BRG
                LEFT JOIN brgdt bd ON o.KD_BRG = bd.KD_BRG AND bd.CBG = ?
                LEFT JOIN sup s ON o.KODES = s.KODES
                WHERE o.flag = 'OL'
                AND o.CBG = ?
                ORDER BY o.KODES ASC, o.KD_BRG ASC
            ", [$CBG, $CBG]);

            if (empty($data)) {
                return response()->json(['error' => 'Tidak ada data untuk dicetak'], 404);
            }

            Log::info('Data found', ['count' => count($data)]);

            // Generate NAMAFILE berdasarkan CBG dan tanggal
            $namaFile = $CBG . '_OL_' . date('Ymd_His');
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

            Log::info('Report data prepared', [
                'total_rows' => count($reportData)
            ]);

            // Generate Jasper Report with PHPJasperXML
            $file = 'order_lebih_freshfood';
            $PHPJasperXML = new PHPJasperXML();
            $PHPJasperXML->load_xml_file(base_path("/app/reportc01/phpjasperxml/{$file}.jrxml"));

            // Convert to plain array for PHPJasperXML
            $cleanData = json_decode(json_encode($reportData), true);

            // Set parameters
            $PHPJasperXML->arrayParameter = [
                "JUDUL" => "LAPORAN ORDER LEBIH TS KODE 3",
                "CBG" => $CBG,
                "USERNAME" => $username,
                "TGL_CETAK" => date('d-m-Y H:i:s'),
                "NAMAFILE" => $namaFile,
                "TGL_ORDER" => $tglOrder,
                "JAM" => $jam
            ];

            $PHPJasperXML->setData($cleanData);

            // Clear output buffer
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            Log::info('=== Generating PDF with PHPJasperXML ===');

            // Output PDF inline (I = inline, D = download)
            $PHPJasperXML->outpage("I");
            exit;
        } catch (\Exception $e) {
            Log::error('=== TOrderLebihFreshFood generateJasperPDF ERROR ===', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Gagal mencetak: ' . $e->getMessage()
            ], 500);
        }
    }
}
