<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class TLaporanBarangFlashSaleController extends Controller
{
    public function index(Request $request)
    {
        try {
            $judul = 'Laporan Barang Flash Sale';

            Log::info('=== TLaporanBarangFlashSale INDEX ===', [
                'user' => Auth::user()->username ?? 'unknown',
                'cbg' => Auth::user()->CBG ?? null
            ]);

            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                Log::error('User tidak memiliki CBG');
                return view("otransaksi_TLaporanBarangFlashSale.index")->with([
                    'judul' => $judul,
                    'error' => 'User tidak memiliki akses cabang (CBG). Hubungi administrator.'
                ]);
            }

            if (!$request->session()->has('periode')) {
                Log::warning('Periode belum diset');
                return view("otransaksi_TLaporanBarangFlashSale.index")->with([
                    'judul' => $judul,
                    'warning' => 'Periode belum diset. Silakan set periode terlebih dahulu.'
                ]);
            }

            Log::info('Halaman index dimuat sukses', ['cbg' => $CBG]);

            return view("otransaksi_TLaporanBarangFlashSale.index")->with([
                'judul' => $judul,
                'cbg' => $CBG
            ]);
        } catch (\Exception $e) {
            Log::error('Error in TLaporanBarangFlashSale index: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return view("otransaksi_TLaporanBarangFlashSale.index")->with([
                'judul' => 'Laporan Barang Flash Sale',
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    public function cari_data(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            $USERNAME = Auth::user()->username ?? 'unknown';

            Log::info('=== CARI DATA FLASH SALE ===', [
                'user' => $USERNAME,
                'cbg' => $CBG
            ]);

            if (!$CBG) {
                Log::error('User tidak memiliki CBG');
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            $periode = $request->session()->get('periode', date('m.Y'));

            Log::info('Query data flash sale', [
                'cbg' => $CBG,
                'periode' => $periode
            ]);

            // Gunakan database connection sesuai CBG
            $connection = strtolower($CBG);

            $sql = "
                SELECT
                    KD_BRG as sub_item,
                    NA_BRG as nama_barang,
                    SATUAN as ukuran,
                    KD_PGR as kd_program,
                    COALESCE(DIS_LAMA, 0) as dis_sebelumnya,
                    COALESCE(DISKON, 0) as dis_baru,
                    COALESCE(STOK, 0) as stok,
                    TGL_JL as tgl_jual,
                    KEMASAN as kemasan
                FROM laporan_brg_macet
                ORDER BY KD_BRG ASC
            ";

            Log::info('QUERY - Flash Sale Data', [
                'connection' => $connection,
                'raw_query_untuk_navicat' => trim(preg_replace('/\s+/', ' ', $sql))
            ]);

            // Ambil data dari tabel laporan_brg_macet menggunakan dynamic connection
            $query = DB::connection($connection)->table('laporan_brg_macet')
                ->select(
                    'KD_BRG as sub_item',
                    'NA_BRG as nama_barang',
                    'SATUAN as ukuran',
                    'KD_PGR as kd_program',
                    DB::raw('COALESCE(DIS_LAMA, 0) as dis_sebelumnya'),
                    DB::raw('COALESCE(DISKON, 0) as dis_baru'),
                    DB::raw('COALESCE(STOK, 0) as stok'),
                    'TGL_JL as tgl_jual',
                    'KEMASAN as kemasan'
                )
                ->orderBy('KD_BRG', 'ASC');

            $count = $query->count();
            Log::info('Data flash sale ditemukan: ' . $count . ' record');

            return Datatables::of($query)
                ->addIndexColumn()
                ->editColumn('dis_sebelumnya', function ($row) {
                    return number_format($row->dis_sebelumnya, 2);
                })
                ->editColumn('dis_baru', function ($row) {
                    return number_format($row->dis_baru, 2);
                })
                ->editColumn('stok', function ($row) {
                    return number_format($row->stok, 0);
                })
                ->editColumn('tgl_jual', function ($row) {
                    return $row->tgl_jual ? date('d-m-Y', strtotime($row->tgl_jual)) : '-';
                })
                ->editColumn('ukuran', function ($row) {
                    return $row->ukuran ?? '-';
                })
                ->editColumn('kemasan', function ($row) {
                    return $row->kemasan ?? '-';
                })
                ->editColumn('kd_program', function ($row) {
                    return $row->kd_program ?? '-';
                })
                ->make(true);
        } catch (\Exception $e) {
            Log::error('Error in cari_data flash sale', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function detail(Request $request, $id)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            $USERNAME = Auth::user()->username ?? 'unknown';

            Log::info('=== PROSES DATA FLASH SALE ===', [
                'user' => $USERNAME,
                'cbg' => $CBG,
                'id' => $id
            ]);

            if (!$CBG) {
                Log::error('User tidak memiliki CBG');
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            // Gunakan database connection sesuai CBG
            $connection = strtolower($CBG);

            DB::connection($connection)->beginTransaction();

            // Panggil stored procedure untuk proses data
            $procedureName = "pjl_lap_flashsale";

            Log::info('Calling stored procedure', [
                'connection' => $connection,
                'procedure' => $procedureName,
                'param' => $CBG
            ]);

            try {
                $sqlCall = "CALL {$procedureName}('{$CBG}')";
                Log::info('QUERY - Call Stored Procedure', [
                    'connection' => $connection,
                    'raw_query_untuk_navicat' => $sqlCall
                ]);

                DB::connection($connection)->statement("CALL {$procedureName}(?)", [$CBG]);
                Log::info('Stored procedure executed successfully');
            } catch (\Exception $procError) {
                Log::error('Stored procedure error', [
                    'procedure' => $procedureName,
                    'connection' => $connection,
                    'message' => $procError->getMessage(),
                    'file' => $procError->getFile(),
                    'line' => $procError->getLine()
                ]);

                // Jika stored procedure gagal, tetap lanjutkan untuk menampilkan data yang ada
                DB::connection($connection)->rollBack();

                return response()->json([
                    'success' => false,
                    'error' => 'Stored procedure gagal: ' . $procError->getMessage(),
                    'message' => 'Proses data gagal, namun Anda masih dapat melihat data yang tersedia.'
                ], 500);
            }

            // Refresh data dari laporan_brg_macet
            $sqlData = "SELECT * FROM laporan_brg_macet LIMIT 100";
            Log::info('QUERY - Refresh Data', [
                'connection' => $connection,
                'raw_query_untuk_navicat' => $sqlData
            ]);

            $data = DB::connection($connection)->select("SELECT * FROM laporan_brg_macet LIMIT 100");

            DB::connection($connection)->commit();

            Log::info('Proses data flash sale berhasil', [
                'count' => count($data)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Proses data berhasil! Total ' . count($data) . ' data telah diproses.',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            $connection = strtolower($CBG ?? 'mysql');
            DB::connection($connection)->rollBack();

            Log::error('Error in detail flash sale', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Proses gagal: ' . $e->getMessage()
            ], 500);
        }
    }
}
