<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class TMasaTarikController extends Controller
{
    public function index(Request $request)
    {
        try {
            $judul = 'Transaksi Masa Tarik';

            $CBG = Auth::user()->CBG ?? null;
            $username = Auth::user()->username ?? 'system';

            if (!$CBG) {
                return view("otransaksi_TMasaTarik.index")->with([
                    'judul' => $judul,
                    'error' => 'User tidak memiliki akses cabang (CBG). Hubungi administrator.'
                ]);
            }

            // Get list outlets (toko) dengan status CB atau MA
            $outlets = DB::select("
                SELECT KODE, CONCAT(KODE, ' - ', NAMA) as NAMA_LENGKAP
                FROM TOKO 
                WHERE STA IN ('CB', 'MA')
                ORDER BY KODE
            ");

            // Get list sub items
            $subItems = DB::select("
                SELECT DISTINCT sub 
                FROM brg 
                WHERE sub IS NOT NULL AND sub != ''
                ORDER BY sub
            ");

            return view("otransaksi_TMasaTarik.index")->with([
                'judul' => $judul,
                'cbg' => $CBG,
                'username' => $username,
                'outlets' => $outlets,
                'subItems' => $subItems
            ]);
        } catch (\Exception $e) {
            Log::error('Error in TMasaTarik index: ' . $e->getMessage());
            return view("otransaksi_TMasaTarik.index")->with([
                'judul' => 'Transaksi Masa Tarik',
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    // Ambil data untuk datatables berdasarkan SUB
    public function cari_data(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            $sub = $request->input('sub', '');

            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            if (empty($sub)) {
                return response()->json(['error' => 'Sub item tidak boleh kosong'], 400);
            }

            // Query sesuai dengan Delphi: select kd_brg, na_brg, ket_uk, ket_kem, tarik, masa_exp, tarik_tipe
            $query = "
                SELECT 
                    kd_brg,
                    na_brg,
                    ket_uk,
                    ket_kem,
                    IFNULL(tarik, 0) as tarik,
                    IFNULL(masa_exp, 0) as masa_exp,
                    IFNULL(tarik_tipe, '') as tarik_tipe,
                    sub
                FROM brg 
                WHERE sub = ?
                ORDER BY kd_brg ASC
            ";

            $data = DB::select($query, [$sub]);

            return Datatables::of(collect($data))
                ->addIndexColumn()
                ->editColumn('tarik', function ($row) {
                    return '<input type="number" class="form-control form-control-sm text-right editable-tarik" 
                            data-kd_brg="' . $row->kd_brg . '" 
                            value="' . $row->tarik . '" 
                            min="0" step="1" style="width: 80px;">';
                })
                ->editColumn('masa_exp', function ($row) {
                    return '<input type="number" class="form-control form-control-sm text-right editable-masa-exp" 
                            data-kd_brg="' . $row->kd_brg . '" 
                            value="' . $row->masa_exp . '" 
                            min="0" step="1" style="width: 80px;">';
                })
                ->editColumn('tarik_tipe', function ($row) {
                    return '<input type="text" class="form-control form-control-sm editable-tarik-tipe" 
                            data-kd_brg="' . $row->kd_brg . '" 
                            value="' . $row->tarik_tipe . '" 
                            maxlength="10" style="width: 100px;">';
                })
                ->rawColumns(['tarik', 'masa_exp', 'tarik_tipe'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('Error in cari_data: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    // Ambil semua data untuk export excel
    public function cari_semua(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            $sub = $request->input('sub', '');

            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            if (empty($sub)) {
                return response()->json(['error' => 'Sub item tidak boleh kosong'], 400);
            }

            $data = DB::select("
                SELECT 
                    sub as 'Sub Item',
                    kd_brg as 'Kode Barang',
                    na_brg as 'Nama Barang',
                    ket_uk as 'Ukuran',
                    ket_kem as 'Kemasan',
                    IFNULL(tarik_tipe, '') as 'Type',
                    IFNULL(tarik, 0) as 'Tarik',
                    IFNULL(masa_exp, 0) as 'Masa Exp'
                FROM brg 
                WHERE sub = ?
                ORDER BY kd_brg ASC
            ", [$sub]);

            if (empty($data)) {
                return response()->json(['error' => 'Tidak ada data untuk di-export'], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Error in cari_semua: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    // Proses update data (tarik, masa_exp, tarik_tipe)
    public function proses(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            $outlet = $request->input('outlet', '');
            $action = $request->input('action', '');

            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            if (empty($outlet)) {
                return response()->json(['error' => 'Outlet tidak boleh kosong'], 400);
            }

            DB::beginTransaction();

            switch ($action) {
                case 'update_tarik':
                    return $this->updateTarik($request, $outlet);

                case 'update_masa_exp':
                    return $this->updateMasaExp($request, $outlet);

                case 'update_tarik_tipe':
                    return $this->updateTarikTipe($request, $outlet);

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

    // Update field TARIK ke semua outlet (kecuali outlet yang dipilih)
    private function updateTarik($request, $outlet)
    {
        $kd_brg = $request->input('kd_brg', '');
        $tarik = $request->input('tarik', 0);

        if (empty($kd_brg)) {
            DB::rollBack();
            return response()->json(['error' => 'Kode barang tidak boleh kosong'], 400);
        }

        // Get list outlet yang akan diupdate (semua outlet CB/MA kecuali outlet yang sedang dibuka)
        $outlets = DB::select("
            SELECT KODE 
            FROM TOKO 
            WHERE STA IN ('CB', 'MA') 
            AND KODE <> ?
        ", [$outlet]);

        // Update ke semua outlet
        foreach ($outlets as $toko) {
            $kode = $toko->KODE;

            // Update menggunakan dynamic database name seperti di Delphi
            DB::statement("
                UPDATE `{$kode}`.brg 
                SET tarik = ? 
                WHERE kd_brg = ?
            ", [$tarik, $kd_brg]);
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Data Tarik berhasil diupdate ke semua outlet!'
        ]);
    }

    // Update field MASA_EXP ke semua outlet (kecuali outlet yang dipilih)
    private function updateMasaExp($request, $outlet)
    {
        $kd_brg = $request->input('kd_brg', '');
        $masa_exp = $request->input('masa_exp', 0);

        if (empty($kd_brg)) {
            DB::rollBack();
            return response()->json(['error' => 'Kode barang tidak boleh kosong'], 400);
        }

        // Get list outlet yang akan diupdate
        $outlets = DB::select("
            SELECT KODE 
            FROM TOKO 
            WHERE STA IN ('CB', 'MA') 
            AND KODE <> ?
        ", [$outlet]);

        // Update ke semua outlet
        foreach ($outlets as $toko) {
            $kode = $toko->KODE;

            DB::statement("
                UPDATE `{$kode}`.brg 
                SET masa_exp = ? 
                WHERE kd_brg = ?
            ", [$masa_exp, $kd_brg]);
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Data Masa Exp berhasil diupdate ke semua outlet!'
        ]);
    }

    // Update field TARIK_TIPE ke semua outlet (kecuali outlet yang dipilih)
    private function updateTarikTipe($request, $outlet)
    {
        $kd_brg = $request->input('kd_brg', '');
        $tarik_tipe = $request->input('tarik_tipe', '');

        if (empty($kd_brg)) {
            DB::rollBack();
            return response()->json(['error' => 'Kode barang tidak boleh kosong'], 400);
        }

        // Get list outlet yang akan diupdate
        $outlets = DB::select("
            SELECT KODE 
            FROM TOKO 
            WHERE STA IN ('CB', 'MA') 
            AND KODE <> ?
        ", [$outlet]);

        // Update ke semua outlet
        foreach ($outlets as $toko) {
            $kode = $toko->KODE;

            DB::statement("
                UPDATE `{$kode}`.brg 
                SET tarik_tipe = ? 
                WHERE kd_brg = ?
            ", [$tarik_tipe, $kd_brg]);
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Data Type berhasil diupdate ke semua outlet!'
        ]);
    }

    // Search barang by kode (untuk autocomplete jika diperlukan)
    public function searchBarang(Request $request)
    {
        try {
            $term = $request->input('term', '');
            $sub = $request->input('sub', '');

            if (empty($term) || strlen($term) < 2) {
                return response()->json([]);
            }

            $query = "
                SELECT 
                    kd_brg,
                    CONCAT(kd_brg, ' - ', na_brg) as label,
                    na_brg
                FROM brg 
                WHERE kd_brg LIKE ? 
            ";

            $params = [$term . '%'];

            if (!empty($sub)) {
                $query .= " AND sub = ?";
                $params[] = $sub;
            }

            $query .= " ORDER BY kd_brg LIMIT 20";

            $results = DB::select($query, $params);

            return response()->json($results);
        } catch (\Exception $e) {
            Log::error('Error in searchBarang: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan'], 500);
        }
    }
}
