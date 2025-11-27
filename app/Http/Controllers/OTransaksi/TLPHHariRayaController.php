<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class TLPHHariRayaController extends Controller
{
    /**
     * Halaman Index - List Usulan LPH Hari Raya
     */
    public function index(Request $request)
    {
        try {
            $judul = 'Usulan LPH Hari Raya';

            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                return view("otransaksi_TLPHHariRaya.index")->with([
                    'judul' => $judul,
                    'error' => 'User tidak memiliki akses cabang (CBG). Hubungi administrator.'
                ]);
            }

            if (!$request->session()->has('periode')) {
                return view("otransaksi_TLPHHariRaya.index")->with([
                    'judul' => $judul,
                    'warning' => 'Periode belum diset. Silakan set periode terlebih dahulu.'
                ]);
            }

            $periode = $request->session()->get('periode');

            return view("otransaksi_TLPHHariRaya.index")->with([
                'judul' => $judul,
                'cbg' => $CBG,
                'periode' => $periode
            ]);
        } catch (\Exception $e) {
            Log::error('Error in TLPHHariRaya index: ' . $e->getMessage());
            return view("otransaksi_TLPHHariRaya.index")->with([
                'judul' => 'Usulan LPH Hari Raya',
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Ambil data list usulan LPH Hari Raya untuk datatables
     */
    public function cari_data(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                Log::error('TLPHHariRaya cari_data: User tidak memiliki CBG');
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            $connection = strtolower($CBG);

            Log::info('TLPHHariRaya cari_data: CBG=' . $CBG . ', Connection=' . $connection);

            // Check if table usul_hraya exists
            try {
                $tableCheck = DB::connection($connection)->select("SHOW TABLES LIKE 'usul_hraya'");
                if (empty($tableCheck)) {
                    Log::warning('TLPHHariRaya cari_data: Tabel usul_hraya tidak ditemukan');
                    return Datatables::of(collect([]))->make(true);
                }
            } catch (\Exception $e) {
                Log::error('TLPHHariRaya cari_data table check error: ' . $e->getMessage());
                return Datatables::of(collect([]))->make(true);
            }

            $query = "
                SELECT 
                    NO_BUKTI,
                    NAMA_EVENT,
                    TGL_AWAL,
                    TGL_AKHIR,
                    TGL_RAYA,
                    TGL as TGL_SIMPAN,
                    POSTED,
                    USRNM
                FROM usul_hraya 
                WHERE DATE(TGL) >= CURDATE() - INTERVAL 2 YEAR
                GROUP BY NO_BUKTI 
                ORDER BY TGL DESC
            ";

            $data = DB::connection($connection)->select($query);

            Log::info('TLPHHariRaya cari_data: Found ' . count($data) . ' records');

            return Datatables::of(collect($data))
                ->addIndexColumn()
                ->editColumn('TGL_AWAL', function ($row) {
                    return date('d-m-Y', strtotime($row->TGL_AWAL));
                })
                ->editColumn('TGL_AKHIR', function ($row) {
                    return date('d-m-Y', strtotime($row->TGL_AKHIR));
                })
                ->editColumn('TGL_SIMPAN', function ($row) {
                    return date('d-m-Y H:i', strtotime($row->TGL_SIMPAN));
                })
                ->editColumn('POSTED', function ($row) {
                    if ($row->POSTED == 1) {
                        return '<span class="badge badge-success">Posted</span>';
                    } else {
                        return '<span class="badge badge-warning">Open</span>';
                    }
                })
                ->addColumn('action', function ($row) {
                    $editBtn = '<button class="btn btn-sm btn-primary btn-edit" data-nobukti="' . $row->NO_BUKTI . '"><i class="fas fa-edit"></i> Edit</button> ';
                    $deleteBtn = '<button class="btn btn-sm btn-danger btn-delete" data-nobukti="' . $row->NO_BUKTI . '" ' . ($row->POSTED == 1 ? 'disabled' : '') . '><i class="fas fa-trash"></i> Hapus</button> ';
                    $printBtn = '<button class="btn btn-sm btn-info btn-print" data-nobukti="' . $row->NO_BUKTI . '"><i class="fas fa-print"></i> Print</button> ';

                    if ($row->POSTED == 1) {
                        $postBtn = '<button class="btn btn-sm btn-secondary btn-stop" data-nobukti="' . $row->NO_BUKTI . '"><i class="fas fa-stop"></i> Hentikan</button> ';
                    } else {
                        $postBtn = '<button class="btn btn-sm btn-success btn-start" data-nobukti="' . $row->NO_BUKTI . '" ' . (strtotime($row->TGL_AWAL) > time() ? 'disabled' : '') . '><i class="fas fa-play"></i> Mulai</button> ';
                    }

                    $rekapBtn = '<button class="btn btn-sm btn-warning btn-rekap" data-nobukti="' . $row->NO_BUKTI . '"><i class="fas fa-chart-bar"></i> Rekap</button>';

                    return $editBtn . $deleteBtn . $printBtn . $postBtn . $rekapBtn;
                })
                ->rawColumns(['action', 'POSTED'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('Error in cari_data: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Halaman Edit/New - Form Entry LPH Hari Raya
     */
    public function edit(Request $request, $no_bukti = null)
    {
        try {
            Log::info('TLPHHariRaya edit called with no_bukti: ' . $no_bukti);

            $judul = $no_bukti && $no_bukti !== 'new' ? 'Edit LPH Hari Raya' : 'Tambah LPH Hari Raya';

            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                Log::error('User tidak memiliki CBG');
                return redirect()->route('lphhariraya')->with('error', 'User tidak memiliki akses cabang');
            }

            $periode = $request->session()->get('periode');
            if (!$periode) {
                Log::error('Periode belum diset');
                return redirect()->route('lphhariraya')->with('warning', 'Periode belum diset');
            }

            // Convert periode to string if it's an array
            if (is_array($periode)) {
                $periode = ($periode['bulan'] ?? '01') . ($periode['tahun'] ?? date('Y'));
            }

            $connection = strtolower($CBG);

            Log::info('Periode: ' . $periode . ', CBG: ' . $CBG . ', Connection=' . $connection);

            // Get list Hari Raya untuk combobox - use TGZ connection
            $queryHR = "SELECT kode, nama FROM hraya ORDER BY NO_ID ASC";
            $listHariRaya = DB::connection('tgz')->select($queryHR);

            $data = [];
            $detail = [];
            $no_bukti_display = '+';
            $tgl_raya = date('Y-m-d');
            $tgl_awal = date('Y-m-d', strtotime('-18 days'));
            $tgl_akhir = date('Y-m-d', strtotime('-5 days'));
            $kd_event = '';
            $nama_event = '';
            $posted = 0;

            // Jika edit, ambil data existing
            if ($no_bukti && $no_bukti !== 'new') {
                $query = "
                    SELECT * FROM usul_hraya 
                    WHERE NO_BUKTI = ?
                    LIMIT 1
                ";
                $result = DB::connection($connection)->select($query, [$no_bukti]);

                if (!empty($result)) {
                    $data = $result[0];
                    $no_bukti_display = $data->NO_BUKTI;
                    $tgl_raya = date('Y-m-d', strtotime($data->TGL_RAYA));
                    $tgl_awal = date('Y-m-d', strtotime($data->TGL_AWAL));
                    $tgl_akhir = date('Y-m-d', strtotime($data->TGL_AKHIR));
                    $kd_event = $data->KD_EVENT;
                    $nama_event = $data->NAMA_EVENT;
                    $posted = $data->POSTED;

                    // Get detail
                    $queryDetail = "
                        SELECT 
                            NO_ID,
                            REC,
                            KD_BRG,
                            NA_BRG,
                            KET_UK,
                            KET_KEM,
                            LPH_LAMA,
                            LPH_RAYA
                        FROM usul_hraya 
                        WHERE NO_BUKTI = ?
                        ORDER BY REC
                    ";
                    $detail = DB::connection($connection)->select($queryDetail, [$no_bukti]);
                } else {
                    return redirect()->route('lphhariraya')->with('error', 'Data tidak ditemukan');
                }
            }

            return view("otransaksi_TLPHHariRaya.edit")->with([
                'judul' => $judul,
                'cbg' => $CBG,
                'periode' => $periode,
                'no_bukti' => $no_bukti_display,
                'data' => $data,
                'detail' => $detail,
                'tgl_raya' => $tgl_raya,
                'tgl_awal' => $tgl_awal,
                'tgl_akhir' => $tgl_akhir,
                'kd_event' => $kd_event,
                'nama_event' => $nama_event,
                'posted' => $posted,
                'listHariRaya' => $listHariRaya,
                'status' => $no_bukti && $no_bukti !== 'new' ? 'edit' : 'simpan'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in edit: ' . $e->getMessage());
            return redirect()->route('lphhariraya')->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Search barang
     */
    public function searchBarang(Request $request)
    {
        try {
            $kd_brg = $request->input('kd_brg');
            $cbg = Auth::user()->CBG ?? null;

            if (!$cbg) {
                return response()->json(['success' => false, 'message' => 'User tidak memiliki akses cabang'], 400);
            }

            $connection = strtolower($cbg);

            Log::info('TLPHHariRaya searchBarang: KD_BRG=' . $kd_brg . ', CBG=' . $cbg);

            $query = "
                SELECT 
                    A.KD_BRG,
                    A.KET_UK,
                    A.KET_KEM,
                    A.NA_BRG,
                    B.LPH,
                    CONCAT(A.NA_BRG, ' ', A.KET_UK, '  ') as XX
                FROM brg A
                INNER JOIN brgdt B ON A.KD_BRG = B.KD_BRG
                WHERE B.YER = YEAR(NOW())
                AND B.CBG = ?
                AND LEFT(A.NA_BRG, 1) NOT IN ('3', '5', '8')
                AND B.TD_OD = ''
                AND A.KD_BRG = ?
            ";

            $result = DB::connection($connection)->select($query, [$cbg, $kd_brg]);

            Log::info('TLPHHariRaya searchBarang: Found ' . count($result) . ' results');

            if (!empty($result)) {
                $barang = $result[0];

                // Calculate LPH Hari Raya (LPH x 2)
                $barang->LPH_RAYA = $barang->LPH * 2;

                return response()->json([
                    'success' => true,
                    'data' => $barang
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Barang tidak ditemukan / SubItem bertanda *'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in searchBarang: ' . $e->getMessage());
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
            $periode = $request->session()->get('periode');

            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            $action = $request->input('action', '');

            DB::beginTransaction();

            switch ($action) {
                case 'save':
                    return $this->saveData($request, $CBG, $username, $periode);

                case 'delete':
                    return $this->deleteData($request, $CBG);

                case 'delete_item':
                    return $this->deleteItem($request, $CBG);

                case 'add_item':
                    return $this->addItem($request, $CBG, $username);

                case 'start':
                    return $this->startEvent($request, $CBG, $username);

                case 'stop':
                    return $this->stopEvent($request, $CBG, $username);

                case 'rekap':
                    return $this->rekapJual($request, $CBG, $username);

                case 'buat_ulang':
                    return $this->buatUlang($request, $CBG, $username);

                case 'print':
                    return $this->printLaporan($request, $CBG, $username);

                case 'print_evaluasi':
                    return $this->printEvaluasi($request, $CBG, $username);

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

    /**
     * Save/Update Data Header
     */
    private function saveData($request, $CBG, $username, $periode)
    {
        $status = $request->input('status', 'simpan');
        $no_bukti = $request->input('no_bukti');
        $tgl_raya = $request->input('tgl_raya');
        $tgl_awal = $request->input('tgl_awal');
        $tgl_akhir = $request->input('tgl_akhir');
        $kd_event = $request->input('kd_event');
        $nama_event = $request->input('nama_event');

        // Validasi
        if (empty($nama_event)) {
            DB::rollBack();
            return response()->json(['error' => 'Nama Hari Raya wajib diisi'], 400);
        }

        if (strtotime($tgl_akhir) < strtotime($tgl_awal)) {
            DB::rollBack();
            return response()->json(['error' => 'Tgl. Akhir harus lebih besar dari Tgl. Awal'], 400);
        }

        if (strtotime($tgl_awal) < strtotime(date('Y-m-d'))) {
            DB::rollBack();
            return response()->json(['error' => 'Tgl. Awal tidak bisa kurang dari tanggal sekarang'], 400);
        }

        $connection = strtolower($CBG);

        Log::info('TLPHHariRaya saveData: Status=' . $status . ', CBG=' . $CBG);

        if ($status === 'simpan') {
            // Generate NO_BUKTI baru menggunakan stored procedure
            $queryNoBukti = "CALL NO_TRANSX('USULHRAYA', ?, ?, '', 'HR')";
            $resultNoBukti = DB::connection($connection)->select($queryNoBukti, [
                'TfrLphHariRayaN',
                $CBG
            ]);

            if (empty($resultNoBukti)) {
                DB::rollBack();
                return response()->json(['error' => 'Gagal generate nomor bukti'], 400);
            }

            $no_bukti = $resultNoBukti[0]->BUKTIX ?? null;

            if (!$no_bukti) {
                DB::rollBack();
                return response()->json(['error' => 'Nomor bukti tidak valid'], 400);
            }
        }

        // Process items from request
        $items = $request->input('items', []);

        if (empty($items)) {
            DB::rollBack();
            return response()->json(['error' => 'Minimal harus ada 1 item'], 400);
        }

        // Get existing items
        $queryExisting = "SELECT NO_ID FROM usul_hraya WHERE NO_BUKTI = ?";
        $existingItems = DB::connection($connection)->select($queryExisting, [$no_bukti]);
        $existingIds = array_column($existingItems, 'NO_ID');

        // Process each item
        $rec = 1;
        $processedIds = [];

        foreach ($items as $item) {
            $no_id = $item['no_id'] ?? 0;

            if ($no_id > 0 && in_array($no_id, $existingIds)) {
                // Update existing item
                $updateQuery = "
                    UPDATE usul_hraya SET 
                        REC = ?,
                        KD_BRG = ?,
                        NA_BRG = ?,
                        KET_UK = ?,
                        KET_KEM = ?,
                        TGL_AWAL = ?,
                        TGL_AKHIR = ?,
                        TGL_RAYA = ?,
                        NAMA_EVENT = ?,
                        KD_EVENT = ?,
                        LPH_RAYA = ?,
                        TGL = NOW(),
                        TG_SMP = NOW(),
                        USRNM = ?
                    WHERE NO_ID = ?
                ";

                DB::connection($connection)->statement($updateQuery, [
                    $rec,
                    $item['kd_brg'],
                    $item['na_brg'],
                    $item['ket_uk'],
                    $item['ket_kem'],
                    $tgl_awal,
                    $tgl_akhir,
                    $tgl_raya,
                    $nama_event,
                    $kd_event,
                    $item['lph_raya'],
                    $username,
                    $no_id
                ]);

                $processedIds[] = $no_id;
            } else {
                // Insert new item
                $insertQuery = "
                    INSERT INTO usul_hraya 
                    (REC, KD_BRG, NA_BRG, KET_UK, KET_KEM, TGL, NO_BUKTI, LPH_RAYA, 
                     KD_EVENT, NAMA_EVENT, TGL_AWAL, TGL_AKHIR, TGL_RAYA, TG_SMP, USRNM, LPH_LAMA)
                    VALUES 
                    (?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?)
                ";

                DB::connection($connection)->statement($insertQuery, [
                    $rec,
                    $item['kd_brg'],
                    $item['na_brg'],
                    $item['ket_uk'],
                    $item['ket_kem'],
                    $no_bukti,
                    $item['lph_raya'],
                    $kd_event,
                    $nama_event,
                    $tgl_awal,
                    $tgl_akhir,
                    $tgl_raya,
                    $username,
                    $item['lph_lama'] ?? 0
                ]);
            }

            $rec++;
        }

        // Delete items yang tidak ada di request
        if (!empty($existingIds)) {
            $idsToDelete = array_diff($existingIds, $processedIds);
            if (!empty($idsToDelete)) {
                $placeholders = implode(',', array_fill(0, count($idsToDelete), '?'));
                $deleteQuery = "DELETE FROM usul_hraya WHERE NO_ID IN ($placeholders)";
                DB::connection($connection)->statement($deleteQuery, $idsToDelete);
            }
        }

        // Call stored procedure untuk update data - use TGZ connection
        $callProc = "CALL pjl_usul_hraya('USULINS', ?, '', ?, ?, ?, ?)";
        DB::connection('tgz')->statement($callProc, [
            $no_bukti,
            $tgl_awal,
            $tgl_akhir,
            $CBG,
            $username
        ]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil disimpan!',
            'no_bukti' => $no_bukti
        ]);
    }

    /**
     * Add Item to LPH Hari Raya
     */
    private function addItem($request, $CBG, $username)
    {
        $no_bukti = $request->input('no_bukti');
        $kd_brg = $request->input('kd_brg');
        $lph_raya = $request->input('lph_raya', 0);

        if (empty($kd_brg)) {
            DB::rollBack();
            return response()->json(['error' => 'Kode barang harus diisi'], 400);
        }

        if ($lph_raya <= 0) {
            DB::rollBack();
            return response()->json(['error' => 'LPH Hari Raya harus lebih dari 0'], 400);
        }

        $connection = strtolower($CBG);

        Log::info('TLPHHariRaya addItem: KD_BRG=' . $kd_brg . ', LPH_RAYA=' . $lph_raya);

        // Get data barang
        $queryBrg = "
            SELECT 
                A.KD_BRG,
                A.NA_BRG,
                A.KET_UK,
                A.KET_KEM,
                B.LPH
            FROM brg A
            INNER JOIN brgdt B ON A.KD_BRG = B.KD_BRG
            WHERE A.KD_BRG = ? 
            AND B.CBG = ?
            AND B.YER = YEAR(NOW())
        ";

        $brg = DB::connection($connection)->select($queryBrg, [$kd_brg, $CBG]);

        if (empty($brg)) {
            DB::rollBack();
            return response()->json(['error' => 'Barang tidak ditemukan'], 400);
        }

        $barang = $brg[0];

        // Check if item already exists
        $checkExist = "SELECT NO_ID FROM usul_hraya WHERE NO_BUKTI = ? AND KD_BRG = ?";
        $exist = DB::connection($connection)->select($checkExist, [$no_bukti, $kd_brg]);

        if (!empty($exist)) {
            DB::rollBack();
            return response()->json(['error' => 'Barang sudah ada dalam daftar'], 400);
        }

        // Get max REC
        $maxRec = DB::connection($connection)->select("
            SELECT COALESCE(MAX(REC), 0) as MAX_REC 
            FROM usul_hraya 
            WHERE NO_BUKTI = ?
        ", [$no_bukti]);

        $rec = ($maxRec[0]->MAX_REC ?? 0) + 1;

        // Get header info
        $headerInfo = DB::connection($connection)->select("
            SELECT TGL_AWAL, TGL_AKHIR, TGL_RAYA, NAMA_EVENT, KD_EVENT 
            FROM usul_hraya 
            WHERE NO_BUKTI = ? 
            LIMIT 1
        ", [$no_bukti]);

        if (empty($headerInfo)) {
            DB::rollBack();
            return response()->json(['error' => 'Header tidak ditemukan'], 400);
        }

        $header = $headerInfo[0];

        // Insert new item
        $insertQuery = "
            INSERT INTO usul_hraya 
            (REC, KD_BRG, NA_BRG, KET_UK, KET_KEM, TGL, NO_BUKTI, LPH_RAYA, 
             KD_EVENT, NAMA_EVENT, TGL_AWAL, TGL_AKHIR, TGL_RAYA, TG_SMP, USRNM, LPH_LAMA)
            VALUES (?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?)
        ";

        DB::connection($connection)->statement($insertQuery, [
            $rec,
            $barang->KD_BRG,
            $barang->NA_BRG,
            $barang->KET_UK,
            $barang->KET_KEM,
            $no_bukti,
            $lph_raya,
            $header->KD_EVENT,
            $header->NAMA_EVENT,
            $header->TGL_AWAL,
            $header->TGL_AKHIR,
            $header->TGL_RAYA,
            $username,
            $barang->LPH
        ]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Item berhasil ditambahkan!',
            'no_bukti' => $no_bukti
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

        $connection = strtolower($CBG);

        // Check if posted
        $checkPosted = "
            SELECT POSTED FROM usul_hraya 
            WHERE NO_ID = ?
        ";
        $check = DB::connection($connection)->select($checkPosted, [$no_id]);

        if (!empty($check) && $check[0]->POSTED == 1) {
            DB::rollBack();
            return response()->json(['error' => 'Item sudah diposting, tidak dapat dihapus'], 400);
        }

        $deleteQuery = "DELETE FROM usul_hraya WHERE NO_ID = ?";
        DB::connection($connection)->statement($deleteQuery, [$no_id]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Item berhasil dihapus!'
        ]);
    }

    /**
     * Delete Data (Header and all items)
     */
    private function deleteData($request, $CBG)
    {
        $no_bukti = $request->input('no_bukti');

        if (empty($no_bukti)) {
            DB::rollBack();
            return response()->json(['error' => 'No Bukti tidak valid'], 400);
        }

        $connection = strtolower($CBG);

        // Check if posted
        $queryCheck = "SELECT POSTED FROM usul_hraya WHERE NO_BUKTI = ? LIMIT 1";
        $check = DB::connection($connection)->select($queryCheck, [$no_bukti]);

        if (!empty($check) && $check[0]->POSTED == 1) {
            DB::rollBack();
            return response()->json(['error' => 'Data sudah diposting, tidak dapat dihapus'], 400);
        }

        // Delete all items
        $deleteQuery = "DELETE FROM usul_hraya WHERE NO_BUKTI = ?";
        DB::connection($connection)->statement($deleteQuery, [$no_bukti]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil dihapus!'
        ]);
    }

    /**
     * Start Event - Mulai LPH Hari Raya
     */
    private function startEvent($request, $CBG, $username)
    {
        $no_bukti = $request->input('no_bukti');

        if (empty($no_bukti)) {
            DB::rollBack();
            return response()->json(['error' => 'No Bukti tidak valid'], 400);
        }

        $connection = strtolower($CBG);

        // Check if already posted
        $queryCheck = "SELECT POSTED, TGL_AWAL FROM usul_hraya WHERE NO_BUKTI = ? LIMIT 1";
        $check = DB::connection($connection)->select($queryCheck, [$no_bukti]);

        if (empty($check)) {
            DB::rollBack();
            return response()->json(['error' => 'Data tidak ditemukan'], 400);
        }

        if ($check[0]->POSTED == 1) {
            DB::rollBack();
            return response()->json(['error' => 'Usulan ini sudah dijalankan'], 400);
        }

        if (strtotime($check[0]->TGL_AWAL) > time()) {
            DB::rollBack();
            return response()->json(['error' => 'Usulan belum bisa dimulai'], 400);
        }

        // Call stored procedure - use TGZ connection
        $callProc = "CALL pjl_usul_hraya('START_HRAYA', ?, '', '', '', ?, ?)";
        DB::connection('tgz')->statement($callProc, [$no_bukti, $CBG, $username]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'LPH Hari Raya berhasil dimulai!'
        ]);
    }

    /**
     * Stop Event - Hentikan LPH Hari Raya
     */
    private function stopEvent($request, $CBG, $username)
    {
        $no_bukti = $request->input('no_bukti');

        if (empty($no_bukti)) {
            DB::rollBack();
            return response()->json(['error' => 'No Bukti tidak valid'], 400);
        }

        $connection = strtolower($CBG);

        // Check if posted
        $queryCheck = "SELECT POSTED FROM usul_hraya WHERE NO_BUKTI = ? LIMIT 1";
        $check = DB::connection($connection)->select($queryCheck, [$no_bukti]);

        if (empty($check)) {
            DB::rollBack();
            return response()->json(['error' => 'Data tidak ditemukan'], 400);
        }

        if ($check[0]->POSTED != 1) {
            DB::rollBack();
            return response()->json(['error' => 'Usulan ini sudah dihentikan'], 400);
        }

        // Call stored procedure - use TGZ connection
        $callProc = "CALL pjl_usul_hraya('END_HRAYA', ?, '', '', '', ?, ?)";
        DB::connection('tgz')->statement($callProc, [$no_bukti, $CBG, $username]);
        DB::commit();
        return response()->json([
            'success' => true,
            'message' => 'LPH Hari Raya berhasil dihentikan dan dikembalikan!'
        ]);
    }
    /**
     * Rekap Jual - Generate Rekap Penjualan
     */
    private function rekapJual($request, $CBG, $username)
    {
        $no_bukti = $request->input('no_bukti');
        $ulang = $request->input('ulang', 'N');
        if (empty($no_bukti)) {
            DB::rollBack();
            return response()->json(['error' => 'No Bukti tidak valid'], 400);
        }

        Log::info('TLPHHariRaya rekapJual: NO_BUKTI=' . $no_bukti . ', Ulang=' . $ulang);

        // Check if rekap sudah pernah dibuat - use TGZ connection
        $queryCheck = "CALL pjl_usul_hraya('CEK_REKAP_JUAL', ?, '', '', '', ?, ?)";
        $check = DB::connection('tgz')->select($queryCheck, [$no_bukti, $CBG, $username]);
        if (!empty($check) && $check[0]->PROSES > 0 && $ulang !== 'Y') {
            DB::rollBack();
            return response()->json([
                'error' => 'Rekap sudah pernah dibuat',
                'confirm_ulang' => true
            ], 400);
        }

        // Call stored procedure untuk rekap - use TGZ connection
        $callProc = "CALL pjl_usul_hraya('REKAP_JUAL', ?, ?, '', '', ?, ?)";
        DB::connection('tgz')->statement($callProc, [$no_bukti, $ulang, $CBG, $username]);
        DB::commit();
        return response()->json([
            'success' => true,
            'message' => 'Rekap penjualan berhasil dibuat!',
            'show_report' => true
        ]);
    }

    /**
     * Buat Ulang - Reset dan generate ulang data
     */
    private function buatUlang($request, $CBG, $username)
    {
        $no_bukti = $request->input('no_bukti');

        if (empty($no_bukti)) {
            DB::rollBack();
            return response()->json(['error' => 'No Bukti tidak valid'], 400);
        }

        $connection = strtolower($CBG);

        Log::info('TLPHHariRaya buatUlang: NO_BUKTI=' . $no_bukti);

        // Check if posted
        $queryCheck = "SELECT POSTED FROM usul_hraya WHERE NO_BUKTI = ? LIMIT 1";
        $check = DB::connection($connection)->select($queryCheck, [$no_bukti]);

        if (!empty($check) && $check[0]->POSTED == 1) {
            DB::rollBack();
            return response()->json(['error' => 'Data sudah diposting, tidak bisa dibuat ulang'], 400);
        }

        // Call stored procedure - use TGZ connection
        $callProc = "CALL pjl_usul_hraya('BUAT_ULANG', ?, '', '', '', ?, ?)";
        DB::connection('tgz')->statement($callProc, [$no_bukti, $CBG, $username]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil dibuat ulang!'
        ]);
    }

    /**
     * Print Laporan
     */
    private function printLaporan($request, $CBG, $username)
    {
        $no_bukti = $request->input('no_bukti');

        if (empty($no_bukti)) {
            DB::rollBack();
            return response()->json(['error' => 'No Bukti tidak valid'], 400);
        }

        Log::info('TLPHHariRaya printLaporan: NO_BUKTI=' . $no_bukti);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Laporan siap dicetak',
            'no_bukti' => $no_bukti,
            'print_url' => route('lphhariraya_print', ['no_bukti' => $no_bukti])
        ]);
    }

    /**
     * Print Evaluasi
     */
    private function printEvaluasi($request, $CBG, $username)
    {
        $no_bukti = $request->input('no_bukti');

        if (empty($no_bukti)) {
            DB::rollBack();
            return response()->json(['error' => 'No Bukti tidak valid'], 400);
        }

        $connection = strtolower($CBG);

        Log::info('TLPHHariRaya printEvaluasi: NO_BUKTI=' . $no_bukti);

        // Get data for evaluation
        $query = "
            SELECT 
                a.*,
                b.LPH as LPH_AKTUAL
            FROM usul_hraya a
            LEFT JOIN brgdt b ON a.KD_BRG = b.KD_BRG AND b.CBG = ? AND b.YER = YEAR(NOW())
            WHERE a.NO_BUKTI = ?
            ORDER BY a.REC
        ";

        $data = DB::connection($connection)->select($query, [$CBG, $no_bukti]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Evaluasi siap dicetak',
            'no_bukti' => $no_bukti,
            'data' => $data,
            'print_url' => route('lphhariraya_print_evaluasi', ['no_bukti' => $no_bukti])
        ]);
    }

    /**
     * Get detail items for specific no_bukti (for AJAX)
     */
    /**
     * Get detail items for specific no_bukti (for AJAX)
     */
    public function detail(Request $request, $no_bukti)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            $connection = strtolower($CBG);

            $query = "
            SELECT 
                NO_ID,
                REC,
                KD_BRG,
                NA_BRG,
                KET_UK,
                LPH_LAMA,
                LPH_RAYA
            FROM usul_hraya 
            WHERE NO_BUKTI = ?
            ORDER BY REC
        ";
            $data = DB::connection($connection)->select($query, [$no_bukti]);
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Error in detail: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }
}
