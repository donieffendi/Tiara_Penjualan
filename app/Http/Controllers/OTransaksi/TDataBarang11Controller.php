<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TDataBarang11Controller extends Controller
{
    var $judul = 'Data Barang 1-1';
    var $FLAGZ = 'BRG';

    public function index(Request $request)
    {
        try {
            Log::info('=== TDataBarang11 INDEX ===', [
                'user' => Auth::user()->username ?? 'unknown',
                'cbg' => Auth::user()->CBG ?? null
            ]);

            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                Log::error('User tidak memiliki CBG');
                return view("otransaksi_TDataBarang1-1.index")->with([
                    'judul' => $this->judul,
                    'flagz' => $this->FLAGZ,
                    'error' => 'User tidak memiliki akses cabang (CBG). Hubungi administrator.'
                ]);
            }

            Log::info('Halaman index dimuat sukses', ['cbg' => $CBG]);

            return view("otransaksi_TDataBarang1-1.index")->with([
                'judul' => $this->judul,
                'flagz' => $this->FLAGZ,
                'cbg' => $CBG
            ]);
        } catch (\Exception $e) {
            Log::error('Error in TDataBarang11 index: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return view("otransaksi_TDataBarang1-1.index")->with([
                'judul' => $this->judul,
                'flagz' => $this->FLAGZ,
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    public function cari_barang(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            $USERNAME = Auth::user()->username ?? 'unknown';

            Log::info('=== CARI BARANG ===', [
                'user' => $USERNAME,
                'cbg' => $CBG,
                'kd_brg' => $request->input('kd_brg')
            ]);

            if (!$CBG) {
                Log::error('User tidak memiliki CBG');
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            $kd_brg = trim($request->input('kd_brg', ''));

            // Validasi input
            if (empty($kd_brg)) {
                Log::warning('Kode barang kosong');
                return response()->json(['error' => 'Kode barang harus diisi'], 400);
            }

            // Gunakan database connection sesuai CBG
            $connection = strtolower($CBG);

            Log::info('Query data barang', [
                'connection' => $connection,
                'kd_brg' => $kd_brg
            ]);

            // Query sesuai dengan logika Delphi - gabungkan data dari brg dengan supplier
            // Menggunakan LEFT JOIN dengan UNION dari sup dan sup7
            $sqlBrg = "
                SELECT
                    brg.*,
                    IFNULL(ole.namas, '') as nsup,
                    IFNULL(ole.kota, '') as kota,
                    IFNULL(ole.almt_k, '') as alamat,
                    brg.sub as subnd
                FROM brg
                LEFT JOIN (
                    SELECT kodes, namas, kota, almt_k FROM sup
                    UNION
                    SELECT kodes, namas, kota, almt_k FROM sup7
                ) as ole ON brg.supp = ole.kodes
                WHERE brg.kd_brg = '{$kd_brg}'
                ORDER BY brg.kd_brg
            ";

            Log::info('QUERY - Data Barang', [
                'connection' => $connection,
                'raw_query_untuk_navicat' => trim(preg_replace('/\s+/', ' ', $sqlBrg))
            ]);

            $result = DB::connection($connection)->select("
                SELECT
                    brg.*,
                    IFNULL(ole.namas, '') as nsup,
                    IFNULL(ole.kota, '') as kota,
                    IFNULL(ole.almt_k, '') as alamat,
                    brg.sub as subnd
                FROM brg
                LEFT JOIN (
                    SELECT kodes, namas, kota, almt_k FROM sup
                    UNION
                    SELECT kodes, namas, kota, almt_k FROM sup7
                ) as ole ON brg.supp = ole.kodes
                WHERE brg.kd_brg = ?
                ORDER BY brg.kd_brg
            ", [$kd_brg]);

            if (empty($result)) {
                Log::warning('Kode barang tidak ditemukan', ['kd_brg' => $kd_brg]);
                return response()->json(['error' => 'Kode barang tidak ditemukan'], 404);
            }

            $barang = $result[0];
            Log::info('Data barang ditemukan', [
                'kd_brg' => $barang->kd_brg,
                'na_brg' => $barang->na_brg
            ]);

            // Ambil data detail tambahan dari brgdt
            $sqlDetail = "SELECT kd_brg, uk, hrg_beli, hrg_jual, ppn, diskon FROM brgdt WHERE kd_brg = '{$kd_brg}'";
            Log::info('QUERY - Detail Transaksi', [
                'connection' => $connection,
                'raw_query_untuk_navicat' => $sqlDetail
            ]);

            $detail_transaksi = DB::connection($connection)->select("
                SELECT
                    kd_brg,
                    uk,
                    hrg_beli,
                    hrg_jual,
                    ppn,
                    diskon
                FROM brgdt
                WHERE kd_brg = ?
            ", [$kd_brg]);

            Log::info('Detail transaksi ditemukan: ' . count($detail_transaksi) . ' record');

            // Ambil data stok cabang dari brgfcd - TIDAK pakai filter cbg
            $sqlStok = "SELECT kd_brg, aw00, ma00, ke00, ln00, ak00 FROM brgfcd WHERE kd_brg = '{$kd_brg}' LIMIT 1";
            Log::info('QUERY - Stok Cabang', [
                'connection' => $connection,
                'raw_query_untuk_navicat' => $sqlStok
            ]);

            $stok_cabang = DB::connection($connection)->select("
                SELECT
                    kd_brg,
                    aw00,
                    ma00,
                    ke00,
                    ln00,
                    ak00
                FROM brgfcd
                WHERE kd_brg = ?
                LIMIT 1
            ", [$kd_brg]);

            Log::info('Stok cabang ditemukan: ' . count($stok_cabang) . ' record');

            return response()->json([
                'success' => true,
                'message' => 'Data barang ditemukan',
                'data' => [
                    'master' => $barang,
                    'detail_transaksi' => $detail_transaksi,
                    'stok_cabang' => $stok_cabang,
                    'supplier' => [
                        'nama' => $barang->nsup,
                        'kota' => $barang->kota,
                        'alamat' => $barang->alamat
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error in cari_barang: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function detail(Request $request, $kd_brg)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            $USERNAME = Auth::user()->username ?? 'unknown';

            Log::info('=== DETAIL BARANG ===', [
                'user' => $USERNAME,
                'cbg' => $CBG,
                'kd_brg' => $kd_brg
            ]);

            if (!$CBG) {
                Log::error('User tidak memiliki CBG');
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            // Gunakan database connection sesuai CBG
            $connection = strtolower($CBG);

            // Ambil detail barang dengan query yang sama seperti cari_barang
            $sqlBrg = "
                SELECT
                    brg.*,
                    IFNULL(ole.namas, '') as nsup,
                    IFNULL(ole.kota, '') as kota,
                    IFNULL(ole.almt_k, '') as alamat,
                    brg.sub as subnd
                FROM brg
                LEFT JOIN (
                    SELECT kodes, namas, kota, almt_k FROM sup
                    UNION
                    SELECT kodes, namas, kota, almt_k FROM sup7
                ) as ole ON brg.supp = ole.kodes
                WHERE brg.kd_brg = '{$kd_brg}'
                ORDER BY brg.kd_brg
            ";

            Log::info('QUERY - Data Barang Detail', [
                'connection' => $connection,
                'raw_query_untuk_navicat' => trim(preg_replace('/\s+/', ' ', $sqlBrg))
            ]);

            $result = DB::connection($connection)->select("
                SELECT
                    brg.*,
                    IFNULL(ole.namas, '') as nsup,
                    IFNULL(ole.kota, '') as kota,
                    IFNULL(ole.almt_k, '') as alamat,
                    brg.sub as subnd
                FROM brg
                LEFT JOIN (
                    SELECT kodes, namas, kota, almt_k FROM sup
                    UNION
                    SELECT kodes, namas, kota, almt_k FROM sup7
                ) as ole ON brg.supp = ole.kodes
                WHERE brg.kd_brg = ?
                ORDER BY brg.kd_brg
            ", [$kd_brg]);

            if (empty($result)) {
                Log::warning('Data barang tidak ditemukan', ['kd_brg' => $kd_brg]);
                return response()->json(['error' => 'Data barang tidak ditemukan'], 404);
            }

            $barang = $result[0];

            // Ambil data detail tambahan
            $detail_transaksi = DB::connection($connection)->select("
                SELECT
                    kd_brg,
                    uk,
                    hrg_beli,
                    hrg_jual,
                    ppn,
                    diskon
                FROM brgdt
                WHERE kd_brg = ?
            ", [$kd_brg]);

            // Ambil data stok cabang - TIDAK pakai filter cbg
            $stok_cabang = DB::connection($connection)->select("
                SELECT
                    kd_brg,
                    aw00,
                    ma00,
                    ke00,
                    ln00,
                    ak00
                FROM brgfcd
                WHERE kd_brg = ?
                LIMIT 1
            ", [$kd_brg]);

            Log::info('Detail barang berhasil dimuat', [
                'detail_count' => count($detail_transaksi),
                'stok_count' => count($stok_cabang)
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'master' => $barang,
                    'detail_transaksi' => $detail_transaksi,
                    'stok_cabang' => $stok_cabang,
                    'supplier' => [
                        'nama' => $barang->nsup,
                        'kota' => $barang->kota,
                        'alamat' => $barang->alamat
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error in detail: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
