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

    /**
     * Fungsi untuk mencari data barang berdasarkan kd_brg atau barcode
     * Sesuai dengan Button1Click di Delphi
     */
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

            $kd_brg = trim($request->input('kd_brg', ''));
            $barcode = trim($request->input('barcode', ''));

            // Validasi: minimal salah satu harus diisi
            if (empty($kd_brg) && empty($barcode)) {
                Log::warning('TDataBarang6C: Kode barang dan barcode kosong');
                return response()->json(['error' => 'Kode barang atau barcode harus diisi'], 400);
            }

            // Jika barcode diisi tapi kd_brg kosong, cari kd_brg dari barcode
            // Sesuai dengan txtbarcodExit di Delphi
            if (!empty($barcode) && empty($kd_brg)) {
                Log::info('TDataBarang6C: Mencari kd_brg dari barcode', ['barcode' => $barcode]);

                $result = DB::select("SELECT kd_brg FROM tgz.brg WHERE barcode = ?", [$barcode]);

                if (empty($result)) {
                    Log::warning('TDataBarang6C: Barcode tidak ditemukan', ['barcode' => $barcode]);
                    return response()->json(['error' => 'Barcode tidak ditemukan'], 404);
                }

                $kd_brg = $result[0]->kd_brg;
                Log::info('TDataBarang6C: Barcode ditemukan', ['barcode' => $barcode, 'kd_brg' => $kd_brg]);
            }

            // Jika kd_brg sudah terisi, validasi kd_brg tidak boleh kosong
            // Sesuai dengan validasi di Button1Click Delphi
            if (empty($kd_brg)) {
                Log::warning('TDataBarang6C: Kode barangnya tidak terisi');
                return response()->json(['error' => 'Kode barangnya tidak terisi...'], 400);
            }

            // Query untuk cek apakah data barang ada di tabel brg DAN brgdt
            // Query ini HARUS sama dengan di Delphi:
            // 'select a.kd_brg from brg a,brgdt b where a.kd_brg=b.kd_brg and a.kd_brg=:kd_brg'
            $barang = DB::select("
                SELECT a.no_id, a.kd_brg 
                FROM tgz.brg a, tgz.brgdt b 
                WHERE a.kd_brg = b.kd_brg 
                AND a.kd_brg = ?
            ", [$kd_brg]);

            // Jika data tidak ditemukan (RecordCount = 0)
            if (empty($barang)) {
                Log::warning('TDataBarang6C: Data barang tidak ada', ['kd_brg' => $kd_brg]);
                return response()->json(['error' => 'Data barang tidak ada!'], 404);
            }

            Log::info('TDataBarang6C: Data barang ditemukan', ['kd_brg' => $kd_brg]);

            // Set status = 'EDIT' seperti di Delphi
            // Ambil detail lengkap barang untuk ditampilkan di form detail
            $detail = $this->getDetailBarang($kd_brg, $CBG);

            Log::info('TDataBarang6C cari_barang() completed', ['kd_brg' => $kd_brg]);

            return response()->json([
                'success' => true,
                'message' => 'Data barang ditemukan',
                'status' => 'EDIT',
                'kd_brg' => $kd_brg,
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

    /**
     * Fungsi untuk mendapatkan barcode dari kd_brg
     * Sesuai dengan txtkd_brgExit di Delphi
     */
    public function get_barcode(Request $request)
    {
        try {
            $kd_brg = trim($request->input('kd_brg', ''));

            if (empty($kd_brg)) {
                return response()->json(['barcode' => '']);
            }

            Log::info('TDataBarang6C get_barcode()', ['kd_brg' => $kd_brg]);

            // Query: select barcode from brg where kd_brg=:kd_brg
            $result = DB::select("SELECT barcode FROM tgz.brg WHERE kd_brg = ?", [$kd_brg]);

            $barcode = !empty($result) ? ($result[0]->barcode ?? '') : '';

            return response()->json([
                'success' => true,
                'barcode' => $barcode
            ]);
        } catch (\Exception $e) {
            Log::error('Error in get_barcode: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Fungsi untuk mendapatkan kd_brg dari barcode
     * Sesuai dengan txtbarcodExit di Delphi
     */
    public function get_kd_brg(Request $request)
    {
        try {
            $barcode = trim($request->input('barcode', ''));

            if (empty($barcode)) {
                return response()->json(['kd_brg' => '']);
            }

            Log::info('TDataBarang6C get_kd_brg()', ['barcode' => $barcode]);

            // Query: select kd_brg from brg where barcode=:barcode
            $result = DB::select("SELECT kd_brg FROM tgz.brg WHERE barcode = ?", [$barcode]);

            $kd_brg = !empty($result) ? ($result[0]->kd_brg ?? '') : '';

            return response()->json([
                'success' => true,
                'kd_brg' => $kd_brg
            ]);
        } catch (\Exception $e) {
            Log::error('Error in get_kd_brg: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
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

            $detail = $this->getDetailBarang($kd_brg, $CBG);

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

    /**
     * Fungsi untuk mendapatkan detail lengkap barang
     * Menggabungkan data dari tabel brg, brgdt, dan brgfcd
     */
    private function getDetailBarang($kd_brg, $CBG)
    {
        try {
            Log::info('TDataBarang6C getDetailBarang() started', [
                'kd_brg' => $kd_brg,
                'CBG' => $CBG
            ]);

            // Query data master barang dari tabel brg
            $master = DB::select("
                SELECT
                    no_id,
                    kd_brg,
                    na_brg,
                    barcode,
                    satuan,
                    kelompok,
                    type
                FROM tgz.brg
                WHERE kd_brg = ?
            ", [$kd_brg]);

            if (empty($master)) {
                Log::warning('TDataBarang6C: Master barang tidak ditemukan', ['kd_brg' => $kd_brg]);
                return null;
            }

            $barang = $master[0];
            Log::info('TDataBarang6C: Master barang ditemukan', ['kd_brg' => $kd_brg]);

            // Query data detail harga per cabang dari tabel brgdt
            $detail_transaksi = DB::select("
                SELECT
                    kd_brg,
                    cbg,
                    harga01,
                    harga02,
                    harga03
                FROM tgz.brgdt
                WHERE kd_brg = ?
            ", [$kd_brg]);

            Log::info('TDataBarang6C: Detail harga found', [
                'kd_brg' => $kd_brg,
                'count' => count($detail_transaksi)
            ]);

            // Query data stok per cabang dari tabel brgfcd
            // Hanya ambil data untuk cabang user yang login
            $stok_cabang = DB::select("
                SELECT
                    no_id,
                    kd_brg,
                    cbg,
                    aw00,
                    ma00,
                    ke00,
                    ln00,
                    ak00
                FROM tgz.brgfcd
                WHERE kd_brg = ? AND cbg = ?
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
                'stok_cabang' => !empty($stok_cabang) ? $stok_cabang[0] : null
            ];
        } catch (\Exception $e) {
            Log::error('Error in getDetailBarang: ' . $e->getMessage(), [
                'kd_brg' => $kd_brg,
                'CBG' => $CBG,
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    public function redirectToShow(Request $request)
    {
        $kd_brg = trim($request->kd_brg);
        $barcode = trim($request->barcode);

        if (empty($kd_brg) && empty($barcode)) {
            return back()->with('error', 'Kode barang atau barcode harus diisi');
        }

        // Jika barcode diisi tapi kd_brg kosong
        if (!empty($barcode) && empty($kd_brg)) {
            $barang = DB::table('brg')
                ->where('barcode', $barcode)
                ->first();

            if (!$barang) {
                return back()->with('error', 'Barcode tidak ditemukan');
            }

            $kd_brg = $barang->kd_brg;
        }

        // Ambil NO_ID berdasarkan kd_brg
        $barang = DB::table('brg')
            ->where('kd_brg', $kd_brg)
            ->first();

        if (!$barang) {
            return back()->with('error', 'Data barang tidak ditemukan');
        }

        // Redirect ke BrgController@show
        return redirect()->route('brg.show', ['idx' => $barang->NO_ID]);
    }
}
