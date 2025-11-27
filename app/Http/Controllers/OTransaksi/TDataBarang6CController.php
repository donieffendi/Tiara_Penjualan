<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TDataBarang6CController extends Controller
{
    var $judul = 'Data Barang 6C';
    var $FLAGZ = 'BRG';

    public function index(Request $request)
    {
        try {
            Log::info('TDataBarang6C index() started');

            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                Log::warning('TDataBarang6C: User tidak memiliki CBG');
                return view("otransaksi_TDataBarang6c.index")->with([
                    'judul' => $this->judul,
                    'flagz' => $this->FLAGZ,
                    'error' => 'User tidak memiliki akses cabang (CBG). Hubungi administrator.'
                ]);
            }

            Log::info('TDataBarang6C index() completed', ['CBG' => $CBG]);

            return view("otransaksi_TDataBarang6c.index")->with([
                'judul' => $this->judul,
                'flagz' => $this->FLAGZ,
                'cbg' => $CBG
            ]);
        } catch (\Exception $e) {
            Log::error('Error in TDataBarang6C index: ' . $e->getMessage());
            return view("otransaksi_TDataBarang6c.index")->with([
                'judul' => $this->judul,
                'flagz' => $this->FLAGZ,
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    public function cari_barang(Request $request)
    {
        try {
            Log::info('TDataBarang6C cari_barang() started', [
                'kd_brg' => $request->input('kd_brg'),
                'barcode' => $request->input('barcode')
            ]);

            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                Log::warning('TDataBarang6C cari_barang: User tidak memiliki CBG');
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            // Get master database name from session or default to 'TGZ'
            $ma = session('ma', 'TGZ');

            $kd_brg = trim($request->input('kd_brg', ''));
            $barcode = trim($request->input('barcode', ''));

            // Validasi input
            if (empty($kd_brg) && empty($barcode)) {
                Log::warning('TDataBarang6C: Kode barang dan barcode kosong');
                return response()->json(['error' => 'Kode barang atau barcode harus diisi'], 400);
            }

            // Query pencarian barang
            if (!empty($barcode) && empty($kd_brg)) {
                Log::info('TDataBarang6C: Mencari kd_brg dari barcode', ['barcode' => $barcode]);
                // Cari kd_brg dari barcode
                $result = DB::select("
                    SELECT kd_brg
                    FROM $ma.brg
                    WHERE barcode = ?
                    LIMIT 1
                ", [$barcode]);

                if (empty($result)) {
                    Log::warning('TDataBarang6C: Barcode tidak ditemukan', ['barcode' => $barcode]);
                    return response()->json(['error' => 'Barcode tidak ditemukan'], 404);
                }

                $kd_brg = $result[0]->kd_brg;
                Log::info('TDataBarang6C: Barcode ditemukan', ['barcode' => $barcode, 'kd_brg' => $kd_brg]);
            } elseif (!empty($kd_brg) && empty($barcode)) {
                Log::info('TDataBarang6C: Mencari barcode dari kd_brg', ['kd_brg' => $kd_brg]);
                // Cari barcode dari kd_brg
                $result = DB::select("
                    SELECT barcode
                    FROM $ma.brg
                    WHERE kd_brg = ?
                    LIMIT 1
                ", [$kd_brg]);

                if (!empty($result)) {
                    $barcode = $result[0]->barcode ?? '';
                    Log::info('TDataBarang6C: Barcode ditemukan untuk kd_brg', ['kd_brg' => $kd_brg, 'barcode' => $barcode]);
                }
            }

            // Cek apakah data barang ada di tabel brg dan brgdt
            $barang = DB::select("
                SELECT
                    a.kd_brg,
                    a.na_brg,
                    a.barcode,
                    a.kd_satuan,
                    a.kd_group,
                    a.kd_jenis,
                    a.aktif
                FROM $ma.brg a
                INNER JOIN $ma.brgdt b ON a.kd_brg = b.kd_brg
                WHERE a.kd_brg = ?
                LIMIT 1
            ", [$kd_brg]);

            if (empty($barang)) {
                Log::warning('TDataBarang6C: Data barang tidak ditemukan', ['kd_brg' => $kd_brg]);
                return response()->json(['error' => 'Data barang tidak ada!'], 404);
            }

            Log::info('TDataBarang6C: Data barang ditemukan', ['kd_brg' => $kd_brg]);

            // Ambil detail lengkap barang
            $detail = $this->getDetailBarang($kd_brg, $CBG, $ma);

            Log::info('TDataBarang6C cari_barang() completed', ['kd_brg' => $kd_brg]);

            return response()->json([
                'success' => true,
                'message' => 'Data barang ditemukan',
                'status' => 'EDIT',
                'data' => $detail
            ]);
        } catch (\Exception $e) {
            Log::error('Error in cari_barang: ' . $e->getMessage(), [
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
            Log::info('TDataBarang6C detail() started', ['kd_brg' => $kd_brg]);

            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                Log::warning('TDataBarang6C detail: User tidak memiliki CBG');
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            // Get master database name from session or default to 'TGZ'
            $ma = session('ma', 'TGZ');

            $detail = $this->getDetailBarang($kd_brg, $CBG, $ma);

            if (empty($detail)) {
                Log::warning('TDataBarang6C: Data barang tidak ditemukan', ['kd_brg' => $kd_brg]);
                return response()->json(['error' => 'Data barang tidak ditemukan'], 404);
            }

            Log::info('TDataBarang6C detail() completed', ['kd_brg' => $kd_brg]);

            return response()->json([
                'success' => true,
                'data' => $detail
            ]);
        } catch (\Exception $e) {
            Log::error('Error in detail: ' . $e->getMessage(), [
                'kd_brg' => $kd_brg,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getDetailBarang($kd_brg, $CBG, $ma = 'TGZ')
    {
        try {
            Log::info('TDataBarang6C getDetailBarang() started', [
                'kd_brg' => $kd_brg,
                'CBG' => $CBG,
                'ma' => $ma
            ]);

            // Query data master barang
            $master = DB::select("
                SELECT
                    a.kd_brg,
                    a.na_brg,
                    a.barcode,
                    a.kd_satuan,
                    a.kd_group,
                    a.kd_jenis,
                    a.aktif,
                    s.na_satuan,
                    g.na_group,
                    j.na_jenis
                FROM $ma.brg a
                LEFT JOIN $ma.satuan s ON a.kd_satuan = s.kd_satuan
                LEFT JOIN $ma.mgroup g ON a.kd_group = g.kd_group
                LEFT JOIN $ma.jenis j ON a.kd_jenis = j.kd_jenis
                WHERE a.kd_brg = ?
                LIMIT 1
            ", [$kd_brg]);

            if (empty($master)) {
                Log::warning('TDataBarang6C: Master barang tidak ditemukan', ['kd_brg' => $kd_brg]);
                return null;
            }

            $barang = $master[0];
            Log::info('TDataBarang6C: Master barang ditemukan', ['kd_brg' => $kd_brg]);

            // Query data detail transaksi (brgdt)
            $detail_transaksi = DB::select("
                SELECT
                    kd_brg,
                    uk,
                    hrg_beli,
                    hrg_jual,
                    ppn,
                    diskon
                FROM $ma.brgdt
                WHERE kd_brg = ?
            ", [$kd_brg]);

            Log::info('TDataBarang6C: Detail transaksi found', [
                'kd_brg' => $kd_brg,
                'count' => count($detail_transaksi)
            ]);

            // Query data stok per cabang (brgfcd)
            $stok_cabang = DB::select("
                SELECT
                    kd_brg,
                    cbg,
                    aw00,
                    ma00,
                    ke00,
                    ln00,
                    ak00
                FROM $ma.brgfcd
                WHERE kd_brg = ? AND cbg = ?
                LIMIT 1
            ", [$kd_brg, $CBG]);

            Log::info('TDataBarang6C: Stok cabang found', [
                'kd_brg' => $kd_brg,
                'CBG' => $CBG,
                'found' => !empty($stok_cabang)
            ]);

            Log::info('TDataBarang6C getDetailBarang() completed', ['kd_brg' => $kd_brg]);

            return [
                'master' => $barang,
                'detail_transaksi' => $detail_transaksi,
                'stok_cabang' => $stok_cabang
            ];
        } catch (\Exception $e) {
            Log::error('Error in getDetailBarang: ' . $e->getMessage(), [
                'kd_brg' => $kd_brg,
                'CBG' => $CBG,
                'ma' => $ma,
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }
}
