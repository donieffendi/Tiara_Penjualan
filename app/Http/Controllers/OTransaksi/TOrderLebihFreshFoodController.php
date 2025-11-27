<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

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

            Log::info('TOrderLebihFreshFood cari_data', [
                'CBG' => $CBG,
                'username' => $username
            ]);

            $periode = $request->session()->get('periode');
            if (!$periode) {
                return response()->json(['error' => 'Periode belum diset'], 400);
            }

            // Query untuk menampilkan data order lebih (FLAG='OL') dari database TGZ
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
                FROM tgz.orderts o
                WHERE o.flag = 'OL' 
                AND o.CBG = ?
                ORDER BY o.KD_BRG ASC
            ";

            $data = DB::select($query, [$username, $CBG]);

            Log::info('TOrderLebihFreshFood cari_data - raw_query_untuk_navicat', [
                'query' => str_replace(['?', '?'], ["'$username'", "'$CBG'"], $query)
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
            Log::error('Error in cari_data: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
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

            // Query untuk barang fresh food (kode 3) dari database TGZ
            // Menggunakan LEFT(KD_BRG,1)='3' untuk filter fresh food
            $query = "
                SELECT 
                    b.KD_BRG as kd_brg,
                    b.NA_BRG as na_brg,
                    b.KET_UK as ket_uk,
                    b.KET_KEM as ket_kem,
                    b.SATUAN as satuan,
                    '3' as klk
                FROM tgz.brg b
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

        // Cek apakah barang ada di master TGZ
        $barang = DB::selectOne("
            SELECT 
                SUB as sub,
                KDBAR as kdbar,
                KD_BRG,
                CONCAT(NA_BRG, ' ', ket_uk) as na_brg,
                ket_kem,
                SUPP as supp
            FROM tgz.brg 
            WHERE KD_BRG = ?
        ", [$kd_brg]);

        if (!$barang) {
            DB::rollBack();
            return response()->json(['error' => 'Kode barang tidak ditemukan'], 404);
        }

        // Cek apakah sudah ada di orderts dengan FLAG='OL'
        $existing = DB::selectOne("
            SELECT rec 
            FROM tgz.orderts 
            WHERE KD_BRG = ? 
            AND flag = 'OL' 
            AND CBG = ?
        ", [$kd_brg, $CBG]);

        if ($existing) {
            DB::rollBack();
            return response()->json(['error' => 'Barang sudah ada dalam daftar order'], 400);
        }

        // Insert ke orderts TGZ
        DB::statement("
            INSERT INTO tgz.orderts (
                SUB, KDBAR, KD_BRG, NA_BRG, ket_kem, qty, KODES, TGL, flag, CBG
            ) VALUES (?, ?, ?, ?, ?, ?, ?, CURDATE(), 'OL', ?)
        ", [
            $barang->sub,
            $barang->kdbar,
            $barang->KD_BRG,
            $barang->na_brg,
            $barang->ket_kem,
            $qty,
            $barang->supp,
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
            DELETE FROM tgz.orderts 
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
            DELETE FROM tgz.orderts 
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
        // Get data untuk print dari TGZ
        $data = DB::select("
            SELECT 
                ? AS USER,
                o.rec,
                o.SUB,
                o.KDBAR,
                o.KD_BRG,
                o.NA_BRG,
                o.ket_kem as KET_KEM,
                o.qty as QTY,
                o.KODES as SUPP,
                DATE_FORMAT(o.TGL, '%d-%m-%Y') as TGL_KIRIM
            FROM tgz.orderts o
            WHERE o.flag = 'OL' 
            AND o.CBG = ?
            ORDER BY o.KD_BRG ASC
        ", [$username, $CBG]);

        if (empty($data)) {
            DB::rollBack();
            return response()->json(['error' => 'Tidak ada data untuk dicetak'], 404);
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    private function exportExcel($request, $CBG, $username)
    {
        // Get data untuk export excel dari TGZ
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
            FROM tgz.orderts o
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
}
