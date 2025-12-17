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

            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            Log::info('TOrderLebihHariRayaOnline cari_data', [
                'CBG' => $CBG
            ]);

            // Use Query Builder with default connection for DataTables
            $query = DB::table('ord_lebih_hari_raya_ff')
                ->select(
                    'NAMAFILE',
                    'KODE_HR',
                    DB::raw('MIN(TGL_AWAL) as TGL_AWAL'),
                    DB::raw('MAX(TGL_AKHIR) as TGL_AKHIR'),
                    'OUTLET',
                    DB::raw('MAX(TGL) as TGL')
                )
                ->where('OUTLET', '=', $CBG)
                ->whereRaw('DATE(TGL) >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)')
                ->groupBy('NAMAFILE', 'KODE_HR', 'OUTLET')
                ->orderByDesc(DB::raw('MAX(TGL)'));

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
                    $printBtn = '<button class="btn btn-sm btn-success btn-print" data-namafile="' . $row->NAMAFILE . '"><i class="fas fa-print"></i> Print</button> ';
                    $detailBtn = '<button class="btn btn-sm btn-info btn-detail" data-namafile="' . $row->NAMAFILE . '"><i class="fas fa-eye"></i> Detail</button> ';
                    $deleteBtn = '<button class="btn btn-sm btn-danger btn-delete" data-namafile="' . $row->NAMAFILE . '"><i class="fas fa-trash"></i> Hapus</button>';
                    return $editBtn . $printBtn . $detailBtn . $deleteBtn;
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

            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            Log::info('TOrderLebihHariRayaOnline detail', [
                'CBG' => $CBG,
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

            $data = DB::select($query, [$namafile, $CBG]);

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

            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            Log::info('TOrderLebihHariRayaOnline lookup_barang', [
                'CBG' => $CBG
            ]);

            // Query untuk barang fresh food (kode 3) - semua barang dengan prefix tgz.
            $query = "
                SELECT 
                    A.KD_BRG as kd_brg,
                    A.NA_BRG as na_brg,
                    A.KET_UK as ket_uk,
                    A.KET_KEM as ket_kem,
                    A.SATUAN as satuan,
                    COALESCE(B.HARGA01, 0) as LPH
                FROM tgz.brg A
                LEFT JOIN tgz.brgdt B ON A.KD_BRG = B.KD_BRG AND B.CBG = ?
                WHERE A.KD_BRG IS NOT NULL
                AND A.KD_BRG != ''
                AND A.KD_BRG LIKE '3%'
                ORDER BY A.KD_BRG ASC
            ";

            $data = DB::select($query, [$CBG]);

            Log::info('TOrderLebihHariRayaOnline lookup_barang - result', [
                'result_count' => count($data)
            ]);

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => count($data) . ' barang tersedia'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in lookup_barang: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Gagal memuat data barang: ' . $e->getMessage()], 500);
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

            if (!$CBG) {
                return response()->json(['success' => false, 'message' => 'User tidak memiliki akses cabang'], 400);
            }

            $query = "
                SELECT 
                    A.KD_BRG, 
                    A.KET_UK, 
                    A.KET_KEM, 
                    A.NA_BRG, 
                    COALESCE(B.HARGA01, 0) as LPH,
                    CONCAT(A.NA_BRG, ' ', A.KET_UK, '  ') as XX
                FROM tgz.brg A
                LEFT JOIN tgz.brgdt B ON A.KD_BRG = B.KD_BRG AND B.CBG = ?
                WHERE A.KD_BRG = ? 
                AND A.KD_BRG LIKE '3%'
            ";

            $result = DB::select($query, [$CBG, $kd_brg]);

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
            Log::error('Error in TOrderLebihHariRayaOnlineController@searchBarang: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
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

            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            $action = $request->input('action', '');

            Log::info('TOrderLebihHariRayaOnline proses', [
                'CBG' => $CBG,
                'action' => $action
            ]);

            DB::beginTransaction();

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
                    DB::rollBack();
                    return response()->json(['error' => 'Action tidak valid'], 400);
            }
        } catch (\Exception $e) {
            if ($CBG) {
                DB::rollBack();
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
        $status = $request->input('status', 'simpan');
        $namafile = $request->input('namafile');
        $kode_hr = $request->input('kode_hr');
        $tgl_awal = $request->input('tgl_awal');
        $tgl_akhir = $request->input('tgl_akhir');

        // Validasi
        if (empty($kode_hr)) {
            DB::rollBack();
            return response()->json(['error' => 'Kode Hari Raya wajib diisi'], 400);
        }

        if (empty($tgl_awal) || empty($tgl_akhir)) {
            DB::rollBack();
            return response()->json(['error' => 'Tanggal mulai dan sampai harus diisi'], 400);
        }

        if (strtotime($tgl_akhir) < strtotime($tgl_awal)) {
            DB::rollBack();
            return response()->json(['error' => 'Tanggal sampai tidak boleh lebih kecil dari tanggal mulai'], 400);
        }

        if ($status === 'simpan') {
            // Generate NAMAFILE baru
            // Format: {KODE_HR}{YYMMDD}.{TH}HR
            $queryToko = "SELECT CONCAT(TH,'HR') as EXT FROM toko WHERE KODE = ?";
            $toko = DB::select($queryToko, [$CBG]);

            if (empty($toko)) {
                DB::rollBack();
                return response()->json(['error' => 'Data toko tidak ditemukan'], 400);
            }

            $ext = $toko[0]->EXT;
            $namafile = $kode_hr . date('ymd') . '.' . $ext;

            // Cek apakah NAMAFILE sudah ada
            $checkQuery = "SELECT NAMAFILE FROM ord_lebih_hari_raya_ff WHERE NAMAFILE = ?";
            $existing = DB::select($checkQuery, [$namafile]);

            if (!empty($existing)) {
                DB::rollBack();
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

            DB::statement($updateQuery, [
                $tgl_awal,
                $tgl_akhir,
                $kode_hr,
                $namafile,
                $CBG
            ]);
        }

        DB::commit();

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
        $namafile = $request->input('namafile');
        $kd_brg = $request->input('kd_brg');
        $per_ord = $request->input('per_ord', 0);
        $tgl_awal = $request->input('tgl_awal');
        $tgl_akhir = $request->input('tgl_akhir');
        $kode_hr = $request->input('kode_hr');

        Log::info('addItem called', [
            'namafile_input' => $namafile,
            'kd_brg' => $kd_brg,
            'kode_hr' => $kode_hr
        ]);

        // Validasi
        if (empty($kd_brg)) {
            DB::rollBack();
            return response()->json(['error' => 'Kode barang harus diisi'], 400);
        }

        // Get barang info
        $queryBrg = "
            SELECT 
                A.KD_BRG, 
                A.KET_UK, 
                A.KET_KEM, 
                A.NA_BRG, 
                COALESCE(B.HARGA01, 0) as LPH,
                CONCAT(A.NA_BRG, ' ', A.KET_UK, '  ') as XX
            FROM tgz.brg A
            LEFT JOIN tgz.brgdt B ON A.KD_BRG = B.KD_BRG AND B.CBG = ?
            WHERE A.KD_BRG = ? 
            AND A.KD_BRG LIKE '3%'
        ";

        $brg = DB::select($queryBrg, [$CBG, $kd_brg]);

        if (empty($brg)) {
            DB::rollBack();
            return response()->json(['error' => 'SubItem Tidak Ditemukan'], 400);
        }

        $barang = $brg[0];

        // Jika namafile kosong, 'new', atau '+', generate baru
        if (empty($namafile) || $namafile === '+' || $namafile === 'new') {
            $queryToko = "SELECT CONCAT(TH,'HR') as EXT FROM toko WHERE KODE = ?";
            $toko = DB::select($queryToko, [$CBG]);

            if (empty($toko)) {
                DB::rollBack();
                return response()->json(['error' => 'Data toko tidak ditemukan'], 400);
            }

            $ext = $toko[0]->EXT;
            $namafile = $kode_hr . date('ymd') . '.' . $ext;

            // Cek apakah NAMAFILE sudah ada
            $checkQuery = "SELECT NAMAFILE FROM ord_lebih_hari_raya_ff WHERE NAMAFILE = ?";
            $existing = DB::select($checkQuery, [$namafile]);

            if (!empty($existing)) {
                DB::rollBack();
                return response()->json(['error' => 'NO.BUKTI Sudah Ada. Tolong Rubah Kode Hari Raya'], 400);
            }

            Log::info('Generated new namafile', ['namafile' => $namafile, 'kode_hr' => $kode_hr]);
        }

        // Cek apakah item sudah ada
        $checkItem = "
            SELECT NO_ID FROM ord_lebih_hari_raya_ff 
            WHERE NAMAFILE = ? AND KD_BRG = ? AND OUTLET = ?
        ";
        $existingItem = DB::select($checkItem, [$namafile, $kd_brg, $CBG]);

        if (!empty($existingItem)) {
            // Update existing item
            $updateQuery = "
                UPDATE ord_lebih_hari_raya_ff 
                SET PER_ORD = ?,
                    TGL = NOW()
                WHERE NO_ID = ?
            ";
            DB::statement($updateQuery, [$per_ord, $existingItem[0]->NO_ID]);
        } else {
            // Get max REC
            $maxRec = DB::select("
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

            DB::statement($insertQuery, [
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
            DB::statement($updateSub, [$namafile, $kd_brg]);
        }

        DB::commit();

        Log::info('addItem completed', ['final_namafile' => $namafile]);

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
        $no_id = $request->input('no_id');

        if (empty($no_id)) {
            DB::rollBack();
            return response()->json(['error' => 'ID tidak valid'], 400);
        }

        $deleteQuery = "DELETE FROM ord_lebih_hari_raya_ff WHERE NO_ID = ? AND OUTLET = ?";
        DB::statement($deleteQuery, [$no_id, $CBG]);

        DB::commit();

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
        $namafile = $request->input('namafile');

        if (empty($namafile)) {
            DB::rollBack();
            return response()->json(['error' => 'Nama file tidak valid'], 400);
        }

        $deleteQuery = "DELETE FROM ord_lebih_hari_raya_ff WHERE NAMAFILE = ? AND OUTLET = ?";
        DB::statement($deleteQuery, [$namafile, $CBG]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil dihapus!'
        ]);
    }

    /**
     * Print Report - Order Lebih Hari Raya
     */
    public function print(Request $request, $namafile)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            // Get header data
            $queryHeader = "
                SELECT NAMAFILE, KODE_HR, TGL_AWAL, TGL_AKHIR, OUTLET
                FROM ord_lebih_hari_raya_ff
                WHERE NAMAFILE = ? AND OUTLET = ?
                LIMIT 1
            ";
            $header = DB::select($queryHeader, [$namafile, $CBG]);

            if (empty($header)) {
                return response()->json(['error' => 'Data tidak ditemukan'], 404);
            }

            $headerData = $header[0];

            // Get detail data
            $queryDetail = "
                SELECT 
                    ROW_NUMBER() OVER (ORDER BY REC) as NO,
                    KD_BRG,
                    NMBAR as NA_BRG,
                    KET_UK,
                    KET_KEM,
                    CAST(LPH AS DECIMAL(10,2)) as LPH,
                    CAST(PER_ORD AS DECIMAL(10,2)) as PER_ORD
                FROM ord_lebih_hari_raya_ff
                WHERE NAMAFILE = ? AND OUTLET = ?
                ORDER BY REC
            ";
            $detail = DB::select($queryDetail, [$namafile, $CBG]);

            // Prepare data for Jasper
            $reportData = [];
            foreach ($detail as $row) {
                $reportData[] = [
                    'NO' => $row->NO,
                    'KD_BRG' => $row->KD_BRG,
                    'NA_BRG' => $row->NA_BRG,
                    'KET_UK' => $row->KET_UK ?? '',
                    'KET_KEM' => $row->KET_KEM ?? '',
                    'LPH' => number_format($row->LPH, 2, '.', ''),
                    'PER_ORD' => number_format($row->PER_ORD, 2, '.', '')
                ];
            }

            // Generate Jasper Report with PHPJasperXML
            $file = 'order_lebih_hari_raya';
            $PHPJasperXML = new PHPJasperXML();
            $PHPJasperXML->load_xml_file(base_path("/app/reportc01/phpjasperxml/{$file}.jrxml"));

            $cleanData = json_decode(json_encode($reportData), true);
            $PHPJasperXML->arrayParameter = [
                "NAMAFILE" => $headerData->NAMAFILE,
                "KODE_HR" => $headerData->KODE_HR,
                "TGL_AWAL" => date('d-m-Y', strtotime($headerData->TGL_AWAL)),
                "TGL_AKHIR" => date('d-m-Y', strtotime($headerData->TGL_AKHIR)),
                "OUTLET" => $headerData->OUTLET,
                "TGL_CETAK" => date('d-m-Y H:i:s')
            ];

            $PHPJasperXML->setData($cleanData);

            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            $PHPJasperXML->outpage("I");
            exit;
        } catch (\Exception $e) {
            Log::error('Error in print: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Print Report Evaluasi - Perbandingan Order vs Realisasi
     */
    public function printEvaluasi(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            $namafile = $request->input('namafile');
            $tgl_eval_awal = $request->input('tgl_eval_awal');
            $tgl_eval_akhir = $request->input('tgl_eval_akhir');

            if (empty($namafile)) {
                return response()->json(['error' => 'Nama file harus dipilih'], 400);
            }

            if (empty($tgl_eval_awal) || empty($tgl_eval_akhir)) {
                return response()->json(['error' => 'Periode evaluasi harus diisi'], 400);
            }

            // Get header data
            $queryHeader = "
                SELECT NAMAFILE, KODE_HR, TGL_AWAL, TGL_AKHIR, OUTLET
                FROM ord_lebih_hari_raya_ff
                WHERE NAMAFILE = ? AND OUTLET = ?
                LIMIT 1
            ";
            $header = DB::select($queryHeader, [$namafile, $CBG]);

            if (empty($header)) {
                return response()->json(['error' => 'Data tidak ditemukan'], 404);
            }

            $headerData = $header[0];

            // Calculate hari raya period days
            $tgl_awal = new \DateTime($headerData->TGL_AWAL);
            $tgl_akhir = new \DateTime($headerData->TGL_AKHIR);
            $interval = $tgl_awal->diff($tgl_akhir);
            $hari_periode = $interval->days + 1;

            // Get detail with realisasi
            // First, get all order items
            $queryOrder = "
                SELECT 
                    ROW_NUMBER() OVER (ORDER BY REC) as NO,
                    KD_BRG,
                    NMBAR as NA_BRG,
                    KET_UK,
                    CAST(LPH AS DECIMAL(10,2)) as LPH,
                    CAST(PER_ORD AS DECIMAL(10,2)) as PER_ORD,
                    CAST((LPH * ?) * (1 + (PER_ORD / 100)) AS DECIMAL(10,2)) as TOTAL_ORDER
                FROM ord_lebih_hari_raya_ff
                WHERE NAMAFILE = ? AND OUTLET = ?
                ORDER BY REC
            ";
            $orderItems = DB::select($queryOrder, [$hari_periode, $namafile, $CBG]);

            // Get actual sales (realisasi) data from juald tables
            // Build query to search across all juald tables in date range
            $startDate = new \DateTime($tgl_eval_awal);
            $endDate = new \DateTime($tgl_eval_akhir);

            $realisasiMap = [];

            // Loop through each month in the date range
            $currentDate = clone $startDate;
            while ($currentDate <= $endDate) {
                $monthSuffix = $currentDate->format('m'); // 01, 02, etc.
                $tableName = "juald{$monthSuffix}";

                // Check if table exists first
                $tableExists = DB::select("SHOW TABLES LIKE '{$tableName}'");

                if (!empty($tableExists)) {
                    $queryRealisasi = "
                        SELECT KD_BRG, SUM(QTY) as QTY_JUAL
                        FROM {$tableName}
                        WHERE DATE(TGL) BETWEEN ? AND ?
                        AND CBG = ?
                        AND FLAG = 'JL'
                        GROUP BY KD_BRG
                    ";

                    $monthData = DB::select($queryRealisasi, [$tgl_eval_awal, $tgl_eval_akhir, $CBG]);

                    // Accumulate data
                    foreach ($monthData as $item) {
                        if (isset($realisasiMap[$item->KD_BRG])) {
                            $realisasiMap[$item->KD_BRG] += $item->QTY_JUAL;
                        } else {
                            $realisasiMap[$item->KD_BRG] = $item->QTY_JUAL;
                        }
                    }
                }

                // Move to next month
                $currentDate->modify('+1 month');
            }

            // Merge order and realisasi data
            $detail = [];
            foreach ($orderItems as $order) {
                $order->REAL_ORDER = $realisasiMap[$order->KD_BRG] ?? 0;
                $detail[] = $order;
            }

            // Prepare data for Jasper
            $reportData = [];
            foreach ($detail as $row) {
                $selisih = $row->REAL_ORDER - $row->TOTAL_ORDER;
                $persentase_real = $row->TOTAL_ORDER > 0 ? ($row->REAL_ORDER / $row->TOTAL_ORDER) * 100 : 0;

                $keterangan = '';
                if ($persentase_real >= 95 && $persentase_real <= 105) {
                    $keterangan = 'Sesuai Target';
                } elseif ($persentase_real > 105) {
                    $keterangan = 'Melebihi Target';
                } else {
                    $keterangan = 'Di Bawah Target';
                }

                $reportData[] = [
                    'NO' => $row->NO,
                    'KD_BRG' => $row->KD_BRG,
                    'NA_BRG' => $row->NA_BRG,
                    'KET_UK' => $row->KET_UK ?? '',
                    'LPH' => number_format($row->LPH, 2, '.', ''),
                    'PER_ORD' => number_format($row->PER_ORD, 2, '.', ''),
                    'TOTAL_ORDER' => number_format($row->TOTAL_ORDER, 2, '.', ''),
                    'REAL_ORDER' => number_format($row->REAL_ORDER, 2, '.', ''),
                    'SELISIH' => number_format($selisih, 2, '.', ''),
                    'PERSENTASE_REAL' => number_format($persentase_real, 2, '.', ''),
                    'KETERANGAN' => $keterangan
                ];
            }

            // Generate Jasper Report with PHPJasperXML
            $file = 'order_lebih_hari_raya_evaluasi';
            $PHPJasperXML = new PHPJasperXML();
            $PHPJasperXML->load_xml_file(base_path("/app/reportc01/phpjasperxml/{$file}.jrxml"));

            $cleanData = json_decode(json_encode($reportData), true);
            $PHPJasperXML->arrayParameter = [
                "KODE_HR" => $headerData->KODE_HR,
                "TGL_AWAL" => date('d-m-Y', strtotime($headerData->TGL_AWAL)),
                "TGL_AKHIR" => date('d-m-Y', strtotime($headerData->TGL_AKHIR)),
                "OUTLET" => $headerData->OUTLET,
                "TGL_CETAK" => date('d-m-Y H:i:s'),
                "PERIODE" => date('d-m-Y', strtotime($tgl_eval_awal)) . ' s/d ' . date('d-m-Y', strtotime($tgl_eval_akhir))
            ];

            $PHPJasperXML->setData($cleanData);

            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            $PHPJasperXML->outpage("I");
            exit;
        } catch (\Exception $e) {
            Log::error('Error in printEvaluasi: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }
}
