<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class TOrderLebihHariRayaOnlineController extends Controller
{
    /**
     * Halaman Index - List Order Lebih Hari Raya
     */
    public function index(Request $request)
    {
        try {
            $judul = 'Order Lebih Hari Raya Fresh Food Online';

            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                return view("otransaksi_TOrderLebihHariRayaOnline.index")->with([
                    'judul' => $judul,
                    'error' => 'User tidak memiliki akses cabang (CBG). Hubungi administrator.'
                ]);
            }

            if (!$request->session()->has('periode')) {
                return view("otransaksi_TOrderLebihHariRayaOnline.index")->with([
                    'judul' => $judul,
                    'warning' => 'Periode belum diset. Silakan set periode terlebih dahulu.'
                ]);
            }

            $periode = $request->session()->get('periode');

            return view("otransaksi_TOrderLebihHariRayaOnline.index")->with([
                'judul' => $judul,
                'cbg' => $CBG,
                'periode' => $periode
            ]);
        } catch (\Exception $e) {
            Log::error('Error in TOrderLebihHariRayaOnline index: ' . $e->getMessage());
            return view("otransaksi_TOrderLebihHariRayaOnline.index")->with([
                'judul' => 'Order Lebih Hari Raya Fresh Food Online',
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Ambil data list order untuk datatables di index
     */
    public function cari_data(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            $connection = strtolower($CBG);

            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            Log::info('TOrderLebihHariRayaOnline cari_data', [
                'CBG' => $CBG,
                'connection' => $connection
            ]);

            // Use Query Builder with dynamic connection for DataTables
            $query = DB::connection($connection)->table('ord_lebih_hari_raya_ff')
                ->select(
                    'NAMAFILE',
                    'KODE_HR',
                    DB::raw('MIN(TGL_AWAL) as TGL_AWAL'),
                    DB::raw('MAX(TGL_AKHIR) as TGL_AKHIR'),
                    'OUTLET',
                    DB::raw('MAX(TGL) as TGL')
                )
                ->where('OUTLET', $CBG)
                ->where(DB::raw('DATE(TGL)'), '>=', DB::raw('CURDATE() - INTERVAL 1 YEAR'))
                ->groupBy('NAMAFILE', 'KODE_HR', 'OUTLET')
                ->orderBy(DB::raw('MAX(TGL)'), 'DESC');

            Log::info('TOrderLebihHariRayaOnline cari_data - Query executed');

            return Datatables::of($query)
                ->addIndexColumn()
                ->editColumn('TGL', function ($row) {
                    return $row->TGL ? date('d-m-Y H:i:s', strtotime($row->TGL)) : '-';
                })
                ->editColumn('TGL_AWAL', function ($row) {
                    return $row->TGL_AWAL ? date('d-m-Y', strtotime($row->TGL_AWAL)) : '-';
                })
                ->editColumn('TGL_AKHIR', function ($row) {
                    return $row->TGL_AKHIR ? date('d-m-Y', strtotime($row->TGL_AKHIR)) : '-';
                })
                ->editColumn('NAMAFILE', function ($row) {
                    return $row->NAMAFILE ?? '-';
                })
                ->editColumn('KODE_HR', function ($row) {
                    return $row->KODE_HR ?? '-';
                })
                ->editColumn('OUTLET', function ($row) {
                    return $row->OUTLET ?? '-';
                })
                ->addColumn('action', function ($row) {
                    $editBtn = '<button class="btn btn-sm btn-primary btn-edit" data-namafile="' . $row->NAMAFILE . '"><i class="fas fa-edit"></i> Edit</button> ';
                    $deleteBtn = '<button class="btn btn-sm btn-danger btn-delete" data-namafile="' . $row->NAMAFILE . '"><i class="fas fa-trash"></i> Hapus</button> ';
                    $detailBtn = '<button class="btn btn-sm btn-info btn-detail" data-namafile="' . $row->NAMAFILE . '"><i class="fas fa-eye"></i> Detail</button>';
                    return $editBtn . $deleteBtn . $detailBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('Error in TOrderLebihHariRayaOnline cari_data: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Halaman Edit/New - Form Entry Order Lebih Hari Raya
     */
    public function edit(Request $request, $namafile = null)
    {
        try {
            $judul = $namafile && $namafile !== 'new' ? 'Edit Order Lebih Hari Raya' : 'Tambah Order Lebih Hari Raya';

            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                return redirect()->route('orderlebihharirayaonline')->with('error', 'User tidak memiliki akses cabang');
            }

            $periode = $request->session()->get('periode');
            if (!$periode) {
                return redirect()->route('orderlebihharirayaonline')->with('warning', 'Periode belum diset');
            }

            $data = [];
            $detail = [];
            $no_bukti = '+';
            $kode_hr = '';
            $tgl_awal = date('Y-m-d');
            $tgl_akhir = date('Y-m-d');

            // Jika edit, ambil data existing
            if ($namafile && $namafile !== 'new') {
                $query = "
                    SELECT * FROM ord_lebih_hari_raya_ff 
                    WHERE NAMAFILE = ? AND OUTLET = ?
                    ORDER BY REC
                ";
                $result = DB::select($query, [$namafile, $CBG]);

                if (!empty($result)) {
                    $data = $result[0];
                    $detail = $result;
                    $no_bukti = $data->NAMAFILE;
                    $kode_hr = $data->KODE_HR;
                    $tgl_awal = date('Y-m-d', strtotime($data->TGL_AWAL));
                    $tgl_akhir = date('Y-m-d', strtotime($data->TGL_AKHIR));
                } else {
                    return redirect()->route('orderlebihharirayaonline')->with('error', 'Data tidak ditemukan');
                }
            }

            return view("otransaksi_TOrderLebihHariRayaOnline.edit")->with([
                'judul' => $judul,
                'cbg' => $CBG,
                'periode' => $periode,
                'namafile' => $namafile,
                'data' => $data,
                'detail' => $detail,
                'no_bukti' => $no_bukti,
                'kode_hr' => $kode_hr,
                'tgl_awal' => $tgl_awal,
                'tgl_akhir' => $tgl_akhir,
                'status' => $namafile && $namafile !== 'new' ? 'edit' : 'simpan'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in edit: ' . $e->getMessage());
            return redirect()->route('orderlebihharirayaonline')->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Get detail items for specific namafile (for AJAX)
     */
    public function detail(Request $request, $namafile)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            $connection = strtolower($CBG);

            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            Log::info('TOrderLebihHariRayaOnline detail', [
                'CBG' => $CBG,
                'connection' => $connection,
                'namafile' => $namafile
            ]);

            $query = "
                SELECT 
                    NO_ID,
                    REC,
                    KD_BRG,
                    NMBAR as NA_BRG,
                    KET_UK,
                    LPH,
                    PER_ORD,
                    KET_KEM
                FROM ord_lebih_hari_raya_ff 
                WHERE NAMAFILE = ? AND OUTLET = ?
                ORDER BY REC
            ";

            $data = DB::connection($connection)->select($query, [$namafile, $CBG]);

            return Datatables::of(collect($data))
                ->addIndexColumn()
                ->editColumn('PER_ORD', function ($row) {
                    return number_format($row->PER_ORD, 2) . ' %';
                })
                ->editColumn('LPH', function ($row) {
                    return number_format($row->LPH, 2);
                })
                ->addColumn('action', function ($row) {
                    $deleteBtn = '<button class="btn btn-xs btn-danger btn-delete-item" data-id="' . $row->NO_ID . '"><i class="fas fa-trash"></i></button>';
                    return $deleteBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('Error in detail: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Lookup barang - popup daftar barang kode 3 (fresh food)
     */
    public function lookup_barang(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            $connection = strtolower($CBG);

            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            Log::info('TOrderLebihHariRayaOnline lookup_barang', [
                'CBG' => $CBG,
                'connection' => $connection
            ]);

            // Query untuk barang dengan klk = '3' (fresh food kode 3)
            $query = "
                SELECT 
                    A.kd_brg,
                    A.na_brg,
                    A.ket_uk,
                    A.ket_kem,
                    A.satuan,
                    A.klk,
                    B.LPH
                FROM brg A
                LEFT JOIN brgdt B ON A.KD_BRG = B.KD_BRG AND B.YER = YEAR(NOW())
                WHERE A.klk = '3'
                ORDER BY A.kd_brg ASC
                LIMIT 1000
            ";

            $data = DB::connection($connection)->select($query);

            Log::info('TOrderLebihHariRayaOnline lookup_barang - raw_query_untuk_navicat', [
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

    /**
     * Search barang (used by /api/search-barang)
     * Moved here from route closure / temporary ApiSearchController
     */
    public function searchBarang(Request $request)
    {
        try {
            $kd_brg = $request->input('kd_brg');
            $CBG = Auth::user()->CBG ?? null;
            $connection = strtolower($CBG);

            if (!$CBG) {
                return response()->json(['success' => false, 'message' => 'User tidak memiliki akses cabang'], 400);
            }

            $query = "
                SELECT A.KD_BRG, A.KET_UK, A.KET_KEM, A.NA_BRG, B.LPH,
                       CONCAT(A.NA_BRG, ' ', A.KET_UK, '  ') as XX
                FROM brg A
                INNER JOIN brgdt B ON A.KD_BRG = B.KD_BRG
                WHERE A.KD_BRG = ? 
                AND B.YER = YEAR(NOW())
                AND A.klk = '3'
            ";

            $result = DB::connection($connection)->select($query, [$kd_brg]);

            if (!empty($result)) {
                return response()->json([
                    'success' => true,
                    'data' => $result[0]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'SubItem tidak ditemukan'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in TOrderLebihHariRayaOnlineController@searchBarang: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Proses Save/Update/Delete
     */
    public function proses(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            $username = Auth::user()->username ?? 'system';
            $connection = strtolower($CBG);

            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            $action = $request->input('action', '');

            Log::info('TOrderLebihHariRayaOnline proses', [
                'CBG' => $CBG,
                'connection' => $connection,
                'action' => $action
            ]);

            DB::connection($connection)->beginTransaction();

            switch ($action) {
                case 'save':
                    return $this->saveData($request, $CBG, $username);

                case 'delete':
                    return $this->deleteData($request, $CBG);

                case 'delete_item':
                    return $this->deleteItem($request, $CBG);

                case 'add_item':
                    return $this->addItem($request, $CBG, $username);

                default:
                    DB::connection(strtolower($CBG))->rollBack();
                    return response()->json(['error' => 'Action tidak valid'], 400);
            }
        } catch (\Exception $e) {
            if ($CBG) {
                DB::connection(strtolower($CBG))->rollBack();
            }
            Log::error('Error in proses: ' . $e->getMessage());
            return response()->json([
                'error' => 'Proses gagal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save/Update Data Header
     */
    private function saveData($request, $CBG, $username)
    {
        $connection = strtolower($CBG);
        $status = $request->input('status', 'simpan');
        $namafile = $request->input('namafile');
        $kode_hr = $request->input('kode_hr');
        $tgl_awal = $request->input('tgl_awal');
        $tgl_akhir = $request->input('tgl_akhir');

        // Validasi
        if (empty($kode_hr)) {
            DB::connection($connection)->rollBack();
            return response()->json(['error' => 'Kode Hari Raya wajib diisi'], 400);
        }

        if (empty($tgl_awal) || empty($tgl_akhir)) {
            DB::connection($connection)->rollBack();
            return response()->json(['error' => 'Tanggal mulai dan sampai harus diisi'], 400);
        }

        if (strtotime($tgl_akhir) < strtotime($tgl_awal)) {
            DB::connection($connection)->rollBack();
            return response()->json(['error' => 'Tanggal sampai tidak boleh lebih kecil dari tanggal mulai'], 400);
        }

        if ($status === 'simpan') {
            // Generate NAMAFILE baru
            // Format: {KODE_HR}{YYMMDD}.{TH}HR
            $queryToko = "SELECT CONCAT(TH,'HR') as EXT FROM toko WHERE KODE = ?";
            $toko = DB::connection($connection)->select($queryToko, [$CBG]);

            if (empty($toko)) {
                DB::connection($connection)->rollBack();
                return response()->json(['error' => 'Data toko tidak ditemukan'], 400);
            }

            $ext = $toko[0]->EXT;
            $namafile = $kode_hr . date('ymd') . '.' . $ext;

            // Cek apakah NAMAFILE sudah ada
            $checkQuery = "SELECT NAMAFILE FROM ord_lebih_hari_raya_ff WHERE NAMAFILE = ?";
            $existing = DB::connection($connection)->select($checkQuery, [$namafile]);

            if (!empty($existing)) {
                DB::connection($connection)->rollBack();
                return response()->json(['error' => 'NO.BUKTI Sudah Ada. Tolong Rubah Kode Hari Raya'], 400);
            }
        } else {
            // Update: Update semua record dengan NAMAFILE ini
            $updateQuery = "
                UPDATE ord_lebih_hari_raya_ff 
                SET TGL_AWAL = ?,
                    TGL_AKHIR = ?,
                    KODE_HR = ?,
                    TGL = NOW()
                WHERE NAMAFILE = ? AND OUTLET = ?
            ";

            DB::connection($connection)->statement($updateQuery, [
                $tgl_awal,
                $tgl_akhir,
                $kode_hr,
                $namafile,
                $CBG
            ]);
        }

        DB::connection($connection)->commit();

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil disimpan!',
            'namafile' => $namafile
        ]);
    }

    /**
     * Add Item to Order
     */
    private function addItem($request, $CBG, $username)
    {
        $connection = strtolower($CBG);
        $namafile = $request->input('namafile');
        $kd_brg = $request->input('kd_brg');
        $per_ord = $request->input('per_ord', 0);
        $tgl_awal = $request->input('tgl_awal');
        $tgl_akhir = $request->input('tgl_akhir');
        $kode_hr = $request->input('kode_hr');

        // Validasi
        if (empty($kd_brg)) {
            DB::connection($connection)->rollBack();
            return response()->json(['error' => 'Kode barang harus diisi'], 400);
        }

        // Get barang info
        $queryBrg = "
            SELECT A.KD_BRG, A.KET_UK, A.KET_KEM, A.NA_BRG, B.LPH,
                   CONCAT(A.NA_BRG, ' ', A.KET_UK, '  ') as XX
            FROM brg A
            INNER JOIN brgdt B ON A.KD_BRG = B.KD_BRG
            WHERE A.KD_BRG = ? 
            AND B.YER = YEAR(NOW())
            AND A.klk = '3'
        ";

        $brg = DB::connection($connection)->select($queryBrg, [$kd_brg]);

        if (empty($brg)) {
            DB::connection($connection)->rollBack();
            return response()->json(['error' => 'SubItem Tidak Ditemukan'], 400);
        }

        $barang = $brg[0];

        // Jika namafile kosong atau '+', generate baru
        if (empty($namafile) || $namafile === '+') {
            $queryToko = "SELECT CONCAT(TH,'HR') as EXT FROM toko WHERE KODE = ?";
            $toko = DB::connection($connection)->select($queryToko, [$CBG]);

            if (empty($toko)) {
                DB::connection($connection)->rollBack();
                return response()->json(['error' => 'Data toko tidak ditemukan'], 400);
            }

            $ext = $toko[0]->EXT;
            $namafile = $kode_hr . date('ymd') . '.' . $ext;

            // Cek apakah NAMAFILE sudah ada
            $checkQuery = "SELECT NAMAFILE FROM ord_lebih_hari_raya_ff WHERE NAMAFILE = ?";
            $existing = DB::connection($connection)->select($checkQuery, [$namafile]);

            if (!empty($existing)) {
                DB::connection($connection)->rollBack();
                return response()->json(['error' => 'NO.BUKTI Sudah Ada. Tolong Rubah Kode Hari Raya'], 400);
            }
        }

        // Cek apakah item sudah ada
        $checkItem = "
            SELECT NO_ID FROM ord_lebih_hari_raya_ff 
            WHERE NAMAFILE = ? AND KD_BRG = ? AND OUTLET = ?
        ";
        $existingItem = DB::connection($connection)->select($checkItem, [$namafile, $kd_brg, $CBG]);

        if (!empty($existingItem)) {
            // Update existing item
            $updateQuery = "
                UPDATE ord_lebih_hari_raya_ff 
                SET PER_ORD = ?,
                    TGL = NOW()
                WHERE NO_ID = ?
            ";
            DB::connection($connection)->statement($updateQuery, [$per_ord, $existingItem[0]->NO_ID]);
        } else {
            // Get max REC
            $maxRec = DB::connection($connection)->select("
                SELECT COALESCE(MAX(REC), 0) as MAX_REC 
                FROM ord_lebih_hari_raya_ff 
                WHERE NAMAFILE = ?
            ", [$namafile]);

            $rec = ($maxRec[0]->MAX_REC ?? 0) + 1;

            // Insert new item
            $insertQuery = "
                INSERT INTO ord_lebih_hari_raya_ff 
                (REC, KD_BRG, NMBAR, KET_UK, KET_KEM, TGL, OUTLET, NAMAFILE, LPH, PER_ORD, KODE_HR, TGL_AWAL, TGL_AKHIR)
                VALUES (?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?)
            ";

            DB::connection($connection)->statement($insertQuery, [
                $rec,
                $barang->KD_BRG,
                $barang->NA_BRG,
                $barang->KET_UK,
                $barang->KET_KEM,
                $CBG,
                $namafile,
                $barang->LPH,
                $per_ord,
                $kode_hr,
                $tgl_awal,
                $tgl_akhir
            ]);

            // Update SUB dan KDBAR
            $updateSub = "
                UPDATE ord_lebih_hari_raya_ff A
                INNER JOIN brg C ON A.KD_BRG = C.KD_BRG
                SET A.SUB = C.SUB, A.KDBAR = C.KDBAR
                WHERE A.NAMAFILE = ? AND A.KD_BRG = ?
            ";
            DB::connection($connection)->statement($updateSub, [$namafile, $kd_brg]);
        }

        DB::connection($connection)->commit();

        return response()->json([
            'success' => true,
            'message' => 'Item berhasil ditambahkan!',
            'namafile' => $namafile
        ]);
    }

    /**
     * Delete Item
     */
    private function deleteItem($request, $CBG)
    {
        $connection = strtolower($CBG);
        $no_id = $request->input('no_id');

        if (empty($no_id)) {
            DB::connection($connection)->rollBack();
            return response()->json(['error' => 'ID tidak valid'], 400);
        }

        $deleteQuery = "DELETE FROM ord_lebih_hari_raya_ff WHERE NO_ID = ? AND OUTLET = ?";
        DB::connection($connection)->statement($deleteQuery, [$no_id, $CBG]);

        DB::connection($connection)->commit();

        return response()->json([
            'success' => true,
            'message' => 'Item berhasil dihapus!'
        ]);
    }

    /**
     * Delete Data (All items with same NAMAFILE)
     */
    private function deleteData($request, $CBG)
    {
        $connection = strtolower($CBG);
        $namafile = $request->input('namafile');

        if (empty($namafile)) {
            DB::connection($connection)->rollBack();
            return response()->json(['error' => 'Nama file tidak valid'], 400);
        }

        $deleteQuery = "DELETE FROM ord_lebih_hari_raya_ff WHERE NAMAFILE = ? AND OUTLET = ?";
        DB::connection($connection)->statement($deleteQuery, [$namafile, $CBG]);

        DB::connection($connection)->commit();

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil dihapus!'
        ]);
    }
}
