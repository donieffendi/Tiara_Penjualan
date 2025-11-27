<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TAmbilOrderKode3Controller extends Controller
{
    public function index(Request $request)
    {
        try {
            $judul = 'Transaksi Ambil Order Kode 3';

            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                return view("otransaksi_TAmbilOrderKode3.index")->with([
                    'judul' => $judul,
                    'error' => 'User tidak memiliki akses cabang (CBG). Hubungi administrator.'
                ]);
            }

            if (!$request->session()->has('periode')) {
                return view("otransaksi_TAmbilOrderKode3.index")->with([
                    'judul' => $judul,
                    'warning' => 'Periode belum diset. Silakan set periode terlebih dahulu.'
                ]);
            }

            $periode = $request->session()->get('periode');

            // Ambil list cabang untuk dropdown
            $cabangList = DB::select("SELECT DISTINCT cbg FROM synchron.brg ORDER BY cbg");

            return view("otransaksi_TAmbilOrderKode3.index")->with([
                'judul' => $judul,
                'cbg' => $CBG,
                'periode' => $periode,
                'cabangList' => $cabangList
            ]);
        } catch (\Exception $e) {
            Log::error('Error in TAmbilOrderKode3 index: ' . $e->getMessage());
            return view("otransaksi_TAmbilOrderKode3.index")->with([
                'judul' => 'Transaksi Ambil Order Kode 3',
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    public function cari_data(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            $selectedCbg = $request->input('cbg');
            $sub = $request->input('sub', '');

            if (empty($selectedCbg)) {
                return response()->json(['error' => 'Pilih cabang terlebih dahulu'], 400);
            }

            if (empty($sub)) {
                return response()->json(['error' => 'Sub barang harus diisi'], 400);
            }

            // Query untuk mengambil data order berdasarkan sub (adaptasi dari Delphi)
            $query = "
                SELECT 
                    b.tgl_order as tgl_order,
                    b.kd_brg,
                    br.na_brg,
                    br.ket_uk,
                    br.ket_kem,
                    b.qty,
                    br.supp as kodes,
                    b.cbg
                FROM {$selectedCbg}.brgdt b
                INNER JOIN {$selectedCbg}.brg br ON b.kd_brg = br.kd_brg
                WHERE LEFT(b.kd_brg, 3) = ?
                AND b.TD_OD = ''
                AND b.CAT_OD <> ''
                AND b.qty <> 0
                ORDER BY b.tgl_order, b.kd_brg
            ";

            $data = DB::select($query, [$sub]);

            return response()->json([
                'success' => true,
                'data' => $data,
                'count' => count($data)
            ]);
        } catch (\Exception $e) {
            Log::error('Error in cari_data: ' . $e->getMessage());
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function proses(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            $username = Auth::user()->username ?? 'system';

            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            $selectedCbg = $request->input('cbg');
            $sub = $request->input('sub', '');

            if (empty($selectedCbg)) {
                return response()->json(['error' => 'Pilih cabang terlebih dahulu'], 400);
            }

            if (empty($sub)) {
                return response()->json(['error' => 'Sub barang harus diisi'], 400);
            }

            DB::beginTransaction();

            // Panggil stored procedure spkode3 (adaptasi dari Delphi)
            // com.SQL.Text:='call spkode3(:cbg, :sub)';
            DB::statement("CALL spkode3(?, ?)", [$selectedCbg, $sub]);

            // Query untuk mendapatkan hasil dari spkode3
            $result = DB::select("
                SELECT * FROM spkode3 
                WHERE sub = ? 
                AND qty <> 0
            ", [$sub]);

            DB::commit();

            $message = "Proses Selesai!<br>";
            $message .= "Data berhasil diproses: " . count($result) . " item<br>";
            $message .= "Silakan dicek kembali.";

            return response()->json([
                'success' => true,
                'message' => $message,
                'count' => count($result),
                'data' => $result
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in proses: ' . $e->getMessage());
            return response()->json([
                'error' => 'Proses gagal: ' . $e->getMessage()
            ], 500);
        }
    }

    public function searchBarang(Request $request)
    {
        try {
            $search = $request->input('search', '');
            $cbg = $request->input('cbg', '');

            if (empty($search) || empty($cbg)) {
                return response()->json(['data' => []]);
            }

            $query = "
                SELECT 
                    LEFT(kd_brg, 3) as sub,
                    kd_brg,
                    na_brg
                FROM {$cbg}.brg
                WHERE LEFT(kd_brg, 1) = '3'
                AND (
                    kd_brg LIKE ? OR
                    na_brg LIKE ? OR
                    LEFT(kd_brg, 3) LIKE ?
                )
                GROUP BY sub
                LIMIT 20
            ";

            $searchParam = '%' . $search . '%';
            $data = DB::select($query, [$searchParam, $searchParam, $searchParam]);

            return response()->json(['data' => $data]);
        } catch (\Exception $e) {
            Log::error('Error in searchBarang: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
