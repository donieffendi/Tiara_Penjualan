<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class TPenambahanBarangBaruController extends Controller
{
    /**
     * Halaman Index - List Penambahan Barang Baru
     */
    public function index(Request $request)
    {
        try {
            $judul = 'Penambahan Data Barang Antar Outlet';

            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                return view("otransaksi_TPenambahanBarangBaru.index")->with([
                    'judul' => $judul,
                    'error' => 'User tidak memiliki akses cabang (CBG). Hubungi administrator.'
                ]);
            }

            if (!$request->session()->has('periode')) {
                return view("otransaksi_TPenambahanBarangBaru.index")->with([
                    'judul' => $judul,
                    'warning' => 'Periode belum diset. Silakan set periode terlebih dahulu.'
                ]);
            }

            $periode = $request->session()->get('periode');

            // Convert periode to string if it's an array
            if (is_array($periode)) {
                $periodeDisplay = ($periode['bulan'] ?? '01') . '/' . ($periode['tahun'] ?? date('Y'));
                $periodeValue = ($periode['bulan'] ?? '01') . ($periode['tahun'] ?? date('Y'));
            } else {
                $periodeDisplay = $periode;
                $periodeValue = $periode;
            }

            return view("otransaksi_TPenambahanBarangBaru.index")->with([
                'judul' => $judul,
                'cbg' => $CBG,
                'periode' => $periodeDisplay
            ]);
        } catch (\Exception $e) {
            Log::error('Error in TPenambahanBarangBaru index: ' . $e->getMessage());
            return view("otransaksi_TPenambahanBarangBaru.index")->with([
                'judul' => 'Penambahan Data Barang Antar Outlet',
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Ambil data list untuk datatables
     */
    public function cari_data(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                Log::error('TPenambahanBarangBaru cari_data: User tidak memiliki CBG');
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            $periode = $request->session()->get('periode');

            // Convert periode to string if it's an array
            if (is_array($periode)) {
                $periode = ($periode['bulan'] ?? '01') . ($periode['tahun'] ?? date('Y'));
            }

            Log::info('TPenambahanBarangBaru cari_data: CBG=' . $CBG . ', Periode=' . $periode);

            // Check if table brgch exists
            try {
                $tableCheck = DB::select("SHOW TABLES LIKE 'brgch'");
                if (empty($tableCheck)) {
                    Log::warning('TPenambahanBarangBaru cari_data: Tabel brgch tidak ditemukan');
                    return Datatables::of(collect([]))->make(true);
                }
            } catch (\Exception $e) {
                Log::error('TPenambahanBarangBaru cari_data table check error: ' . $e->getMessage());
                return Datatables::of(collect([]))->make(true);
            }

            $query = "
                SELECT 
                    NO_BUKTI,
                    DATE_FORMAT(TG_SMP, '%d-%m-%Y') as TGL,
                    USRNM,
                    POSTED,
                    PER
                FROM brgch 
                WHERE FLAG = 'PB' 
                AND PER = ?
                ORDER BY TG_SMP DESC
            ";

            $data = DB::select($query, [$periode]);

            Log::info('TPenambahanBarangBaru cari_data: Found ' . count($data) . ' records');

            return Datatables::of(collect($data))
                ->addIndexColumn()
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
                    $postBtn = '<button class="btn btn-sm btn-success btn-post" data-nobukti="' . $row->NO_BUKTI . '" ' . ($row->POSTED == 1 ? 'disabled' : '') . '><i class="fas fa-check"></i> Posting</button>';

                    return $editBtn . $deleteBtn . $postBtn;
                })
                ->rawColumns(['action', 'POSTED'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('Error in cari_data: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Halaman Edit/New - Form Entry Penambahan Barang Baru
     */
    public function edit(Request $request, $no_bukti = null)
    {
        try {
            Log::info('TPenambahanBarangBaru edit called with no_bukti: ' . $no_bukti);

            $judul = $no_bukti && $no_bukti !== 'new' ? 'Edit Penambahan Barang Baru' : 'Tambah Penambahan Barang Baru';

            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                Log::error('User tidak memiliki CBG');
                return redirect()->route('penambahanbarangbaru')->with('error', 'User tidak memiliki akses cabang');
            }

            $periode = $request->session()->get('periode');
            if (!$periode) {
                Log::error('Periode belum diset');
                return redirect()->route('penambahanbarangbaru')->with('warning', 'Periode belum diset');
            }

            // Convert periode to string if it's an array
            if (is_array($periode)) {
                $periodeDisplay = ($periode['bulan'] ?? '01') . '/' . ($periode['tahun'] ?? date('Y'));
                $periodeValue = ($periode['bulan'] ?? '01') . ($periode['tahun'] ?? date('Y'));
            } else {
                $periodeDisplay = $periode;
                $periodeValue = $periode;
            }

            $data = [];
            $detail = [];
            $no_bukti_display = '+';
            $tgl = date('Y-m-d');
            $posted = 0;

            // Jika edit, ambil data existing
            if ($no_bukti && $no_bukti !== 'new') {
                $query = "
                    SELECT * FROM brgch 
                    WHERE NO_BUKTI = ? AND FLAG = 'PB'
                    LIMIT 1
                ";
                $result = DB::select($query, [$no_bukti]);

                if (!empty($result)) {
                    $data = $result[0];
                    $no_bukti_display = $data->NO_BUKTI;
                    $tgl = date('Y-m-d', strtotime($data->TG_SMP));
                    $posted = $data->POSTED;

                    // Get detail
                    $queryDetail = "
                        SELECT 
                            NO_ID,
                            BARCODE,
                            KD_BRG,
                            NA_BRG,
                            KET_UK,
                            KET_KEM,
                            LPH,
                            DTR,
                            NO_TAMU as NO_SP
                        FROM brgchd 
                        WHERE NO_BUKTI = ?
                        ORDER BY NO_ID
                    ";
                    $detail = DB::select($queryDetail, [$no_bukti]);
                } else {
                    return redirect()->route('penambahanbarangbaru')->with('error', 'Data tidak ditemukan');
                }
            }

            return view("otransaksi_TPenambahanBarangBaru.edit")->with([
                'judul' => $judul,
                'cbg' => $CBG,
                'periode' => $periodeDisplay,
                'no_bukti' => $no_bukti_display,
                'data' => $data,
                'detail' => $detail,
                'tgl' => $tgl,
                'posted' => $posted,
                'status' => $no_bukti && $no_bukti !== 'new' ? 'edit' : 'simpan'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in edit: ' . $e->getMessage());
            return redirect()->route('penambahanbarangbaru')->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Search barang dari DC
     */
    public function searchBarang(Request $request)
    {
        try {
            $barcode = $request->input('barcode', '');
            $kd_brg = $request->input('kd_brg', '');
            $cbg = Auth::user()->CBG ?? null;

            if (!$cbg) {
                return response()->json(['success' => false, 'message' => 'User tidak memiliki akses cabang'], 400);
            }

            $searchField = $barcode ? 'BARCODE' : 'KD_BRG';
            $searchValue = $barcode ? $barcode : $kd_brg;

            if (empty($searchValue)) {
                return response()->json(['success' => false, 'message' => 'Barcode atau Kode Barang harus diisi'], 400);
            }

            // Search dari tabel brg_dc (sinkron dari DC)
            $query = "
                SELECT 
                    KD_BRG,
                    BARCODE,
                    NA_BRG,
                    KET_UK,
                    KET_KEM,
                    SUPP,
                    NAMAS,
                    HB,
                    PPN,
                    D1,
                    D2,
                    D3
                FROM brg_dc 
                WHERE {$searchField} = ?
                AND TGL_SINKRON = CURDATE()
            ";

            $result = DB::select($query, [$searchValue]);

            if (!empty($result)) {
                $barang = $result[0];

                return response()->json([
                    'success' => true,
                    'data' => $barang
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Barang tidak ditemukan di Data Center. Pastikan data sudah disinkronkan hari ini.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in searchBarang: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get barang dari file KSP
     */
    public function getKspFile(Request $request)
    {
        try {
            $namaFile = $request->input('nama_file', '');

            if (empty($namaFile)) {
                return response()->json(['success' => false, 'message' => 'Nama file harus diisi'], 400);
            }

            // Get data dari tabel ksp_dc_ts
            $query = "
                SELECT 
                    KD_BRG,
                    BARCODE,
                    NA_BRG,
                    KET_UK,
                    KET_KEM,
                    NO_BUKTI
                FROM ksp_dc_ts 
                WHERE NAMAFILE = ?
                AND (KD_BRG NOT IN (SELECT KD_BRG FROM brgdt WHERE KD_BRG <> '') 
                     OR KD_BRG = '' 
                     OR MASALAH IN (2, 3))
            ";

            $result = DB::select($query, [$namaFile]);

            if (!empty($result)) {
                return response()->json([
                    'success' => true,
                    'data' => $result,
                    'message' => 'Ditemukan ' . count($result) . ' barang baru dari file KSP'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Tidak ada barang baru di file KSP ini'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getKspFile: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Proses Save/Update/Delete/Posting
     */
    public function proses(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            $username = Auth::user()->username ?? 'system';
            $periode = $request->session()->get('periode');

            // Convert periode to string if it's an array
            if (is_array($periode)) {
                $periode = ($periode['bulan'] ?? '01') . ($periode['tahun'] ?? date('Y'));
            }

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

                case 'posting':
                    return $this->postingTerbit($request, $CBG, $username);

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
        $items = $request->input('items', []);

        if (empty($items)) {
            DB::rollBack();
            return response()->json(['error' => 'Minimal harus ada 1 item'], 400);
        }

        if ($status === 'simpan') {
            // Generate NO_BUKTI baru
            $monthString = substr($periode, 0, 2);

            // Get outlet type
            $queryType = "SELECT type FROM toko WHERE kode = ?";
            $resultType = DB::select($queryType, [$CBG]);
            $kode2 = !empty($resultType) ? $resultType[0]->type : '';

            $kode = 'PB' . substr($periode, 2, 2) . $monthString;

            // Get atau create notrans
            $queryNotrans = "
                SELECT NOM{$monthString} as NO_BUKTI 
                FROM notrans 
                WHERE trans = 'PB_TERBIT_BRG' 
                AND PER = ?
            ";
            $resultNotrans = DB::select($queryNotrans, [substr($periode, 2, 4)]);

            if (!empty($resultNotrans)) {
                $r1 = $resultNotrans[0]->NO_BUKTI + 1;
            } else {
                // Insert new notrans
                $insertNotrans = "
                    INSERT INTO notrans (trans, per, form, ket, flag) 
                    VALUES ('PB_TERBIT_BRG', ?, 'TPenambahanBarangBaru', 'Terbit Barang', 'PB')
                ";
                DB::statement($insertNotrans, [substr($periode, 2, 4)]);
                $r1 = 1;
            }

            // Update notrans
            $updateNotrans = "
                UPDATE notrans 
                SET NOM{$monthString} = ? 
                WHERE trans = 'PB_TERBIT_BRG' 
                AND PER = ?
            ";
            DB::statement($updateNotrans, [$r1, substr($periode, 2, 4)]);

            $bkt1 = str_pad($r1, 4, '0', STR_PAD_LEFT);
            $no_bukti = $kode . '-' . $bkt1 . $kode2;

            // Insert header
            $insertHeader = "
                INSERT INTO brgch (NO_BUKTI, FLAG, PER, USRNM, TG_SMP) 
                VALUES (?, 'PB', ?, ?, NOW())
            ";
            DB::statement($insertHeader, [$no_bukti, $periode, $username]);
        } else {
            // Update header
            $updateHeader = "
                UPDATE brgch 
                SET USRNM = ?, TG_SMP = NOW() 
                WHERE NO_BUKTI = ?
            ";
            DB::statement($updateHeader, [$username, $no_bukti]);
        }

        // Get existing items
        $queryExisting = "SELECT NO_ID FROM brgchd WHERE NO_BUKTI = ?";
        $existingItems = DB::select($queryExisting, [$no_bukti]);
        $existingIds = array_column($existingItems, 'NO_ID');

        $processedIds = [];

        foreach ($items as $item) {
            $no_id = $item['no_id'] ?? 0;

            // Get lengkap data dari brg_dc
            $queryBrgDc = "
                SELECT * FROM brg_dc 
                WHERE KD_BRG = ? 
                AND TGL_SINKRON = CURDATE()
            ";
            $brgDcResult = DB::select($queryBrgDc, [$item['kd_brg']]);

            if (empty($brgDcResult)) {
                continue;
            }

            $brgDc = $brgDcResult[0];

            if ($no_id > 0 && in_array($no_id, $existingIds)) {
                // Update existing item
                $updateQuery = "
                    UPDATE brgchd SET 
                        BARCODE = ?,
                        KD_BRG = ?,
                        NA_BRG = ?,
                        KET_KEM = ?,
                        KET_UK = ?,
                        LPH = ?,
                        DTR = ?,
                        NO_TAMU = ?,
                        SUB = ?,
                        KDBAR = ?,
                        SUPP = ?,
                        NAMAS = ?,
                        HB = ?,
                        PPN = ?,
                        D1 = ?,
                        D2 = ?,
                        D3 = ?
                    WHERE NO_ID = ?
                ";

                DB::statement($updateQuery, [
                    $item['barcode'],
                    $item['kd_brg'],
                    $item['na_brg'],
                    $item['ket_kem'],
                    $item['ket_uk'],
                    $item['lph'] ?? 0,
                    $item['dtr'] ?? 0,
                    $item['no_sp'] ?? '',
                    $brgDc->SUB ?? '',
                    $brgDc->KDBAR ?? '',
                    $brgDc->SUPP ?? '',
                    $brgDc->NAMAS ?? '',
                    $brgDc->HB ?? 0,
                    $brgDc->PPN ?? 0,
                    $brgDc->D1 ?? 0,
                    $brgDc->D2 ?? 0,
                    $brgDc->D3 ?? 0,
                    $no_id
                ]);

                $processedIds[] = $no_id;
            } else {
                // Insert new item with complete data
                $insertQuery = "
                    INSERT INTO brgchd 
                    (NO_BUKTI, BARCODE, KD_BRG, NA_BRG, KET_KEM, KET_UK, 
                     LPH, DTR, NO_TAMU, SUB, KDBAR, SUPP, NAMAS, HB, PPN, D1, D2, D3,
                     PANJANG, LEBAR, TINGGI, MASA_TARIK, DTB, TYPE, KLK, 
                     LOC, KEM_P, RETUR, FUNGSI, `IMPORT`, ML, MERK, JNS_KEM, 
                     RASA, GRADE, DISPRO, PGANTI, KD_BRG_PGANTI, ITEM_UNI, 
                     KELOMPOK, KET, KMP, KMP1, KMP2, MO)
                    VALUES 
                    (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
                     ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
                     ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ";

                DB::statement($insertQuery, [
                    $no_bukti,
                    $item['barcode'],
                    $item['kd_brg'],
                    $item['na_brg'],
                    $item['ket_kem'],
                    $item['ket_uk'],
                    $item['lph'] ?? 0,
                    $item['dtr'] ?? 0,
                    $item['no_sp'] ?? '',
                    $brgDc->SUB ?? '',
                    $brgDc->KDBAR ?? '',
                    $brgDc->SUPP ?? '',
                    $brgDc->NAMAS ?? '',
                    $brgDc->HB ?? 0,
                    $brgDc->PPN ?? 0,
                    $brgDc->D1 ?? 0,
                    $brgDc->D2 ?? 0,
                    $brgDc->D3 ?? 0,
                    $brgDc->PANJANG ?? 0,
                    $brgDc->LEBAR ?? 0,
                    $brgDc->TINGGI ?? 0,
                    $brgDc->MASA_TARIK ?? 0,
                    $brgDc->DTB ?? 0,
                    $brgDc->TYPE ?? '',
                    $brgDc->KLK ?? '',
                    $brgDc->LOC ?? '',
                    $brgDc->KEM_P ?? '',
                    $brgDc->RETUR ?? '',
                    $brgDc->FUNGSI ?? '',
                    $brgDc->IMPORT ?? '',
                    $brgDc->ML ?? 0,
                    $brgDc->MERK ?? '',
                    $brgDc->JNS_KEM ?? '',
                    $brgDc->RASA ?? '',
                    $brgDc->GRADE ?? '',
                    $brgDc->DISPRO ?? 0,
                    $brgDc->PGANTI ?? 0,
                    $brgDc->KD_BRG_PGANTI ?? '',
                    $brgDc->ITEM_UNI ?? '',
                    $brgDc->KELOMPOK ?? '',
                    $brgDc->KET ?? '',
                    $brgDc->KMP ?? '',
                    $brgDc->KMP1 ?? 0,
                    $brgDc->KMP2 ?? 0,
                    $brgDc->MO ?? 0
                ]);
            }
        }

        // Delete items yang tidak ada di request
        if (!empty($existingIds)) {
            $idsToDelete = array_diff($existingIds, $processedIds);
            if (!empty($idsToDelete)) {
                $placeholders = implode(',', array_fill(0, count($idsToDelete), '?'));
                $deleteQuery = "DELETE FROM brgchd WHERE NO_ID IN ($placeholders)";
                DB::statement($deleteQuery, $idsToDelete);
            }
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil disimpan!',
            'no_bukti' => $no_bukti
        ]);
    }

    /**
     * Add Item
     */
    private function addItem($request, $CBG, $username)
    {
        $no_bukti = $request->input('no_bukti');
        $barcode = $request->input('barcode', '');
        $kd_brg = $request->input('kd_brg', '');
        $lph = $request->input('lph', 0);
        $dtr = $request->input('dtr', 0);
        $no_sp = $request->input('no_sp', '');

        $searchField = $barcode ? 'BARCODE' : 'KD_BRG';
        $searchValue = $barcode ? $barcode : $kd_brg;

        if (empty($searchValue)) {
            DB::rollBack();
            return response()->json(['error' => 'Barcode atau Kode Barang harus diisi'], 400);
        }

        // Get data barang dari brg_dc
        $queryBrg = "
            SELECT * FROM brg_dc 
            WHERE {$searchField} = ? 
            AND TGL_SINKRON = CURDATE()
        ";

        $brg = DB::select($queryBrg, [$searchValue]);

        if (empty($brg)) {
            DB::rollBack();
            return response()->json(['error' => 'Barang tidak ditemukan di Data Center'], 400);
        }

        $barang = $brg[0];

        // Check if item already exists
        $checkExist = "SELECT NO_ID FROM brgchd WHERE NO_BUKTI = ? AND KD_BRG = ?";
        $exist = DB::select($checkExist, [$no_bukti, $barang->KD_BRG]);

        if (!empty($exist)) {
            DB::rollBack();
            return response()->json(['error' => 'Barang sudah ada dalam daftar'], 400);
        }

        // Insert new item with complete data from DC
        $insertQuery = "
            INSERT INTO brgchd 
            (NO_BUKTI, BARCODE, KD_BRG, NA_BRG, KET_KEM, KET_UK, 
             LPH, DTR, NO_TAMU, SUB, KDBAR, SUPP, NAMAS, HB, PPN, D1, D2, D3,
             PANJANG, LEBAR, TINGGI, MASA_TARIK, DTB, TYPE, KLK, 
             LOC, KEM_P, RETUR, FUNGSI, `IMPORT`, ML, MERK, JNS_KEM, 
             RASA, GRADE, DISPRO, PGANTI, KD_BRG_PGANTI, ITEM_UNI, 
             KELOMPOK, KET, KMP, KMP1, KMP2, MO)
            VALUES 
            (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
             ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
             ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";

        DB::statement($insertQuery, [
            $no_bukti,
            $barang->BARCODE,
            $barang->KD_BRG,
            $barang->NA_BRG,
            $barang->KET_KEM,
            $barang->KET_UK,
            $lph,
            $dtr,
            $no_sp,
            $barang->SUB ?? '',
            $barang->KDBAR ?? '',
            $barang->SUPP ?? '',
            $barang->NAMAS ?? '',
            $barang->HB ?? 0,
            $barang->PPN ?? 0,
            $barang->D1 ?? 0,
            $barang->D2 ?? 0,
            $barang->D3 ?? 0,
            $barang->PANJANG ?? 0,
            $barang->LEBAR ?? 0,
            $barang->TINGGI ?? 0,
            $barang->MASA_TARIK ?? 0,
            $barang->DTB ?? 0,
            $barang->TYPE ?? '',
            $barang->KLK ?? '',
            $barang->LOC ?? '',
            $barang->KEM_P ?? '',
            $barang->RETUR ?? '',
            $barang->FUNGSI ?? '',
            $barang->IMPORT ?? '',
            $barang->ML ?? 0,
            $barang->MERK ?? '',
            $barang->JNS_KEM ?? '',
            $barang->RASA ?? '',
            $barang->GRADE ?? '',
            $barang->DISPRO ?? 0,
            $barang->PGANTI ?? 0,
            $barang->KD_BRG_PGANTI ?? '',
            $barang->ITEM_UNI ?? '',
            $barang->KELOMPOK ?? '',
            $barang->KET ?? '',
            $barang->KMP ?? '',
            $barang->KMP1 ?? 0,
            $barang->KMP2 ?? 0,
            $barang->MO ?? 0
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

        // Check if posted
        $checkPosted = "
            SELECT b.POSTED 
            FROM brgchd a
            INNER JOIN brgch b ON a.NO_BUKTI = b.NO_BUKTI
            WHERE a.NO_ID = ?
        ";
        $check = DB::select($checkPosted, [$no_id]);

        if (!empty($check) && $check[0]->POSTED == 1) {
            DB::rollBack();
            return response()->json(['error' => 'Item sudah diposting, tidak dapat dihapus'], 400);
        }
        $deleteQuery = "DELETE FROM brgchd WHERE NO_ID = ?";
        DB::statement($deleteQuery, [$no_id]);

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

        // Check if posted
        $queryCheck = "SELECT POSTED FROM brgch WHERE NO_BUKTI = ? AND FLAG = 'PB' LIMIT 1";
        $check = DB::select($queryCheck, [$no_bukti]);

        if (!empty($check) && $check[0]->POSTED == 1) {
            DB::rollBack();
            return response()->json(['error' => 'Data sudah diposting, tidak dapat dihapus'], 400);
        }

        // Delete all items first
        $deleteDetail = "DELETE FROM brgchd WHERE NO_BUKTI = ?";
        DB::statement($deleteDetail, [$no_bukti]);

        // Delete header
        $deleteHeader = "DELETE FROM brgch WHERE NO_BUKTI = ?";
        DB::statement($deleteHeader, [$no_bukti]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil dihapus!'
        ]);
    }

    /**
     * Posting Terbit - Insert ke tabel master
     */
    private function postingTerbit($request, $CBG, $username)
    {
        $no_bukti = $request->input('no_bukti');

        if (empty($no_bukti)) {
            DB::rollBack();
            return response()->json(['error' => 'No Bukti tidak valid'], 400);
        }

        // Check if already posted
        $queryCheck = "SELECT POSTED FROM brgch WHERE NO_BUKTI = ? AND FLAG = 'PB' LIMIT 1";
        $check = DB::select($queryCheck, [$no_bukti]);

        if (empty($check)) {
            DB::rollBack();
            return response()->json(['error' => 'Data tidak ditemukan'], 400);
        }

        if ($check[0]->POSTED == 1) {
            DB::rollBack();
            return response()->json(['error' => 'Data sudah diposting'], 400);
        }

        // Cek suplier terlebih dahulu
        $queryDetail = "
        SELECT DISTINCT SUPP, NAMAS, KD_BRG, NA_BRG 
        FROM brgchd 
        WHERE NO_BUKTI = ?
    ";
        $details = DB::select($queryDetail, [$no_bukti]);

        foreach ($details as $detail) {
            $querySup = "SELECT KODES FROM sup WHERE KODES = ?";
            $sup = DB::select($querySup, [$detail->SUPP]);

            if (empty($sup)) {
                DB::rollBack();
                return response()->json([
                    'error' => "Belum ada data suplier {$detail->SUPP} - {$detail->NAMAS}!\n(Barang {$detail->KD_BRG} {$detail->NA_BRG})"
                ], 400);
            }
        }

        // Call stored procedure untuk insert ke brg, masks, supd2
        try {
            $callProc = "CALL dcts_terbit_brgchd(?, ?)";
            DB::statement($callProc, [$no_bukti, $username]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error calling dcts_terbit_brgchd: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal terbitkan barang: ' . $e->getMessage()], 400);
        }

        // Process harga untuk setiap item
        $queryItems = "
        SELECT 
            KD_BRG, LPH, DTR, PPN, HB, D1, D2, D3
        FROM brgchd 
        WHERE NO_BUKTI = ?
    ";
        $items = DB::select($queryItems, [$no_bukti]);

        foreach ($items as $item) {
            $sub = substr($item->KD_BRG, 0, 3);
            $ppn = $item->PPN;
            $ht = $item->HB;
            $d1 = $item->D1;
            $d2 = $item->D2;
            $d3 = $item->D3;

            // Get margin
            $queryMargin = "SELECT persen FROM aotprice WHERE sub = ?";
            $marginResult = DB::select($queryMargin, [$sub]);
            $mg = !empty($marginResult) ? $marginResult[0]->persen : 0;

            // Hitung PPN
            $pn = ($ppn == '1') ? 10 : 0;

            $x = round($ht);
            $y = round($ht);

            // Hitung harga jual
            if ($sub >= '086' && $sub <= '096') {
                $x = floor($x / 10) * 10;
            } elseif ($sub != '***') {
                $x = $x * (100 - $d1) / 100;
                $x = $x * (100 - $d2) / 100;
                $x = $x * (100 + $pn) / 100;
                $x = $x * (100 + $mg) / 100;

                $queryRound = "SELECT ROUND(?, -1) as xx";
                $roundResult = DB::select($queryRound, [$x]);
                $x = $roundResult[0]->xx;
            }

            // Hitung harga beli
            if ($sub != '***') {
                $y = $y * (100 - $d1) / 100;
                $y = $y * (100 - $d2) / 100;
                $y = $y * (100 + $pn) / 100;

                $queryRound = "SELECT ROUND(?, -1) as yy";
                $roundResult = DB::select($queryRound, [$y]);
                $y = $roundResult[0]->yy;
            }

            // Pembulatan harga jual
            $queryBulat = "
            SELECT 
            IF (? >= 1000 AND ? < 10000, 
                IF(SUBSTR(?, 2, 2) = '00', ROUND(? - RIGHT(?, 1) - 10), ?),
            IF (? >= 10000 AND ? < 100000, 
                IF(SUBSTR(?, 3, 1) = '0', ROUND(? - RIGHT(?, 2) - 20), ?),
            IF (? >= 100000 AND ? < 1000000, 
                IF(SUBSTR(?, 3, 1) = '0', ROUND(? - RIGHT(?, 3) - 150), ?),
            IF (? >= 1000000 AND ? < 10000000, 
                IF(SUBSTR(?, 3, 1) = '0', ROUND(? - RIGHT(?, 4) - 1500), ?), 
            ?)))) as hrg
        ";
            $bulatResult = DB::select($queryBulat, array_fill(0, 25, $x));
            $x = $bulatResult[0]->hrg;

            if (!($sub >= '086' && $sub <= '096') && $sub != '***') {
                $queryRound = "SELECT ROUND(?, -1) as xx";
                $roundResult = DB::select($queryRound, [$x]);
                $x = $roundResult[0]->xx;
            }

            $hj = $x;
            $hb = $y;

            // Call stored procedure untuk insert brgd dan brgdt
            try {
                $callProcLph = "CALL dcts_terbit_lphch(?, ?, '', ?, ?, ?, ?)";
                DB::statement($callProcLph, [
                    $no_bukti,
                    $item->KD_BRG,
                    $item->LPH,
                    $item->DTR,
                    $hb,
                    $hj
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error calling dcts_terbit_lphch: ' . $e->getMessage());
                return response()->json(['error' => 'Gagal proses LPH: ' . $e->getMessage()], 400);
            }
        }

        // Update status posted
        $updatePosted = "
        UPDATE brgch 
        SET POSTED = 1, 
            USRNM_POST = ?, 
            TG_POST = NOW() 
        WHERE NO_BUKTI = ?
    ";
        DB::statement($updatePosted, [$username, $no_bukti]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => "Data barang {$no_bukti} berhasil diterbitkan!"
        ]);
    }

    /**
     * Get detail items (for AJAX)
     */
    public function detail(Request $request, $no_bukti)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            $query = "
            SELECT 
                NO_ID,
                BARCODE,
                KD_BRG,
                NA_BRG,
                KET_UK,
                KET_KEM,
                LPH,
                DTR,
                NO_TAMU as NO_SP
            FROM brgchd 
            WHERE NO_BUKTI = ?
            ORDER BY NO_ID
        ";

            $data = DB::select($query, [$no_bukti]);

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
