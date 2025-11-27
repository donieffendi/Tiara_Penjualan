<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class TUsulanHapusBarangController extends Controller
{
    public function index(Request $request)
    {
        try {
            $judul = 'Transaksi Usulan Hapus Barang';
            $CBG = Auth::user()->CBG ?? null;
            $username = Auth::user()->username ?? 'system';

            if (!$CBG) {
                return view("otransaksi_TUsulanHapusBarang.index")->with([
                    'judul' => $judul,
                    'error' => 'User tidak memiliki akses cabang (CBG). Hubungi administrator.'
                ]);
            }

            // Hanya TGZ yang berhak akses
            if ($CBG !== 'TGZ') {
                return view("otransaksi_TUsulanHapusBarang.index")->with([
                    'judul' => $judul,
                    'error' => 'Hanya TGZ yang berhak mengakses halaman ini!'
                ]);
            }

            if (!$request->session()->has('periode')) {
                return view("otransaksi_TUsulanHapusBarang.index")->with([
                    'judul' => $judul,
                    'warning' => 'Periode belum diset. Silakan set periode terlebih dahulu.'
                ]);
            }

            $periode = $request->session()->get('periode');

            // Convert periode to string if it's an array
            if (is_array($periode)) {
                $periodeDisplay = ($periode['bulan'] ?? '01') . '/' . ($periode['tahun'] ?? date('Y'));
            } else {
                $periodeDisplay = $periode;
            }

            return view("otransaksi_TUsulanHapusBarang.index")->with([
                'judul' => $judul,
                'cbg' => $CBG,
                'periode' => $periodeDisplay,
                'username' => $username
            ]);
        } catch (\Exception $e) {
            Log::error('Error in TUsulanHapusBarang index: ' . $e->getMessage());
            return view("otransaksi_TUsulanHapusBarang.index")->with([
                'judul' => 'Transaksi Usulan Hapus Barang',
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    // Tab 1: Usulan Hapus - Ambil data untuk datatables
    public function cari_data(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;

            if (!$CBG || $CBG !== 'TGZ') {
                Log::error('TUsulanHapusBarang cari_data: Akses ditolak untuk CBG=' . $CBG);
                return response()->json(['error' => 'Akses ditolak'], 403);
            }

            $tab = $request->input('tab', 'usulan');

            Log::info('TUsulanHapusBarang cari_data: CBG=' . $CBG . ', Tab=' . $tab);

            // Check if required tables exist
            try {
                if ($tab === 'usulan') {
                    $tableCheck = DB::select("SHOW TABLES LIKE 'brg'");
                    if (empty($tableCheck)) {
                        Log::warning('TUsulanHapusBarang cari_data: Tabel brg tidak ditemukan');
                        return Datatables::of(collect([]))->make(true);
                    }

                    $tableCheck2 = DB::select("SHOW TABLES LIKE 'brgdt'");
                    if (empty($tableCheck2)) {
                        Log::warning('TUsulanHapusBarang cari_data: Tabel brgdt tidak ditemukan');
                        return Datatables::of(collect([]))->make(true);
                    }
                } else {
                    $tableCheck = DB::select("SHOW TABLES LIKE 'brg_del'");
                    if (empty($tableCheck)) {
                        Log::warning('TUsulanHapusBarang cari_data: Tabel brg_del tidak ditemukan');
                        return Datatables::of(collect([]))->make(true);
                    }
                }
            } catch (\Exception $e) {
                Log::error('TUsulanHapusBarang cari_data table check error: ' . $e->getMessage());
                return Datatables::of(collect([]))->make(true);
            }

            if ($tab === 'usulan') {
                return $this->getDataUsulan();
            } else {
                return $this->getDataPosting();
            }
        } catch (\Exception $e) {
            Log::error('Error in cari_data: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    private function getDataUsulan()
    {
        // Query untuk menampilkan data usulan hapus
        // Tanda brg.TANDA_HPS: 0=Belum Tercentang, 1=Tercentang/Check, 2=Sudah Terproses
        $query = "
            SELECT	
                B.NO_ID, 
                A.KD_BRG, 
                CONCAT(B.NA_BRG, ' ', B.KET_UK) AS NA_BRG,  
                A.LPH, 
                A.KDLAKU AS KD, 
                A.TD_OD, 
                A.CAT_OD, 
                A.AK00 + A.GAK00 AS STOK,
                COALESCE(DATE(A.TGL_OD), '') AS TG_ODx,
                COALESCE(DATE(A.TGL_TRM), '') AS TG_TRMx,
                COALESCE(DATE(A.TGL_BK), '') AS TG_BKx,
                COALESCE(DATE(A.TGL_AT), '') AS TG_KSx,
                COALESCE(DATE(A.TGL_PRO), '') AS TG_PROx,
                GREATEST(
                    COALESCE(DATE(A.TGL_OD), '1900-01-01'),
                    COALESCE(DATE(A.TGL_TRM), '1900-01-01'),
                    COALESCE(DATE(A.TGL_BK), '1900-01-01'),
                    COALESCE(DATE(A.TGL_AT), '1900-01-01'),
                    COALESCE(DATE(A.TGL_PRO), '1900-01-01')
                ) AS GREATESTX,
                DATEDIFF(
                    CURDATE(), 
                    COALESCE(DATE(A.TGL_OD), CURDATE())
                ) AS HARI, 
                B.TANDA_HPS
            FROM brgdt A
            INNER JOIN brg B ON A.KD_BRG = B.KD_BRG
            WHERE A.TD_OD <> '' 
                AND A.CAT_OD <> ''
                AND YEAR(A.TGL_OD) > 2002
                AND A.AK00 + A.GAK00 < 1
                AND LEFT(A.KD_BRG, 3) <> '011'
                AND B.TANDA_HPS IN (0, 1)
                AND B.HAPUS = 2
            ORDER BY A.KD_BRG ASC
        ";

        $data = DB::select($query);

        return Datatables::of(collect($data))
            ->addIndexColumn()
            ->editColumn('TG_ODx', function ($row) {
                return $row->TG_ODx ?: '-';
            })
            ->editColumn('TG_TRMx', function ($row) {
                return $row->TG_TRMx ?: '-';
            })
            ->editColumn('TG_BKx', function ($row) {
                return $row->TG_BKx ?: '-';
            })
            ->editColumn('TG_KSx', function ($row) {
                return $row->TG_KSx ?: '-';
            })
            ->editColumn('HARI', function ($row) {
                return number_format($row->HARI, 0, ',', '.');
            })
            ->editColumn('TANDA_HPS', function ($row) {
                if ($row->TANDA_HPS == 1) {
                    return '<span class="badge badge-success">Checked</span>';
                }
                return '<span class="badge badge-secondary">-</span>';
            })
            ->rawColumns(['TANDA_HPS'])
            ->make(true);
    }

    private function getDataPosting()
    {
        // Query untuk menampilkan data posting (brg_del)
        $query = "
            SELECT 
                NO_ID, 
                KD_BRG, 
                NA_BRG, 
                CONCAT(TD_OD, CAT_OD) AS OD, 
                TGL_OD, 
                TGL_TRM, 
                TGL_BK, 
                TGL_AT, 
                USERX, 
                DATE(TGL_IN) AS TGL
            FROM brg_del 
            WHERE POSTED = 0
            ORDER BY KD_BRG ASC
        ";

        $data = DB::select($query);

        return Datatables::of(collect($data))
            ->addIndexColumn()
            ->editColumn('TGL_OD', function ($row) {
                return $row->TGL_OD ?: '-';
            })
            ->editColumn('TGL_TRM', function ($row) {
                return $row->TGL_TRM ?: '-';
            })
            ->editColumn('TGL_BK', function ($row) {
                return $row->TGL_BK ?: '-';
            })
            ->editColumn('TGL_AT', function ($row) {
                return $row->TGL_AT ?: '-';
            })
            ->editColumn('TGL', function ($row) {
                return $row->TGL ?: '-';
            })
            ->make(true);
    }

    // Proses untuk berbagai action
    public function proses(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            $username = Auth::user()->username ?? 'system';

            if (!$CBG || $CBG !== 'TGZ') {
                return response()->json(['error' => 'Akses ditolak'], 403);
            }

            $action = $request->input('action', '');
            $tab = $request->input('tab', 'usulan');

            DB::beginTransaction();

            switch ($action) {
                case 'tampilkan':
                    return $this->tampilkanData($request);

                case 'update_usulan':
                    return $this->updateUsulanData($request);

                case 'proses_usulan':
                    return $this->prosesUsulan($request, $username);

                case 'export_excel_usulan':
                    return $this->exportExcelUsulan($request);

                case 'cek_data_posting':
                    return $this->cekDataPosting($request);

                case 'proses_posting':
                    return $this->prosesPosting($request);

                case 'export_excel_posting':
                    return $this->exportExcelPosting($request);

                case 'toggle_check':
                    return $this->toggleCheck($request);

                case 'batal_hapus':
                    return $this->batalHapus($request, $username);

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

    private function tampilkanData($request)
    {
        // Update data usulan hapus dari semua outlet
        $this->updateUsulanData($request);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil ditampilkan dan diupdate!'
        ]);
    }

    private function updateUsulanData($request)
    {
        $currentMonth = (int)date('m');

        if ($currentMonth === 1) {
            // Proses Januari - cek bulan 11 dan 12 tahun lalu
            $this->prosesJanuari();
        } else {
            // Proses selain Januari - cek semua bulan
            $this->prosesNonJanuari();
        }

        // Update flag HAPUS=2 di TGZ untuk item yang layak hapus di semua outlet
        DB::statement("
            UPDATE brg A,
            (
                SELECT 
                    A.KD_BRG, 
                    A.NA_BRG, 
                    A.HAPUS,
                    COALESCE(B.HAPUS, 1) AS TMM_HAPUS, 
                    COALESCE(C.HAPUS, 1) AS SOP_HAPUS, 
                    COALESCE(D.HAPUS, 1) AS DCK_HAPUS,
                    (COALESCE(B.HAPUS, 1) + COALESCE(C.HAPUS, 1) + COALESCE(D.HAPUS, 1)) AS TOTAL_HAPUS
                FROM tgz.brg A
                LEFT JOIN tmm.brg B ON B.KD_BRG = A.KD_BRG
                LEFT JOIN sop.brg C ON C.KD_BRG = A.KD_BRG
                LEFT JOIN dck.brg D ON D.KD_BRG = A.KD_BRG
                WHERE A.HAPUS = 1
                HAVING TOTAL_HAPUS > 2
            ) B
            SET A.HAPUS = 2
            WHERE A.KD_BRG = B.KD_BRG
        ");

        return true;
    }

    private function prosesJanuari()
    {
        // Ambil tahun kemarin
        $lastYear = date('Y') - 1;

        // Ambil semua outlet
        $outlets = DB::select("
            SELECT kode 
            FROM tgz.toko 
            WHERE STA IN ('MA', 'CB', 'DC') 
            ORDER BY NO_ID ASC
        ");

        foreach ($outlets as $outlet) {
            $cbg = $outlet->kode;

            // Reset HAPUS = 0
            DB::statement("UPDATE {$cbg}.brg SET HAPUS = 0");

            // Update HAPUS = 1 untuk item yang memenuhi syarat
            DB::statement("
                UPDATE {$cbg}.brg A,
                (
                    SELECT A.KD_BRG
                    FROM {$cbg}.brgdt A
                    INNER JOIN {$cbg}.brgdt{$lastYear} B ON A.KD_BRG = B.KD_BRG
                    WHERE A.TD_OD = '*' 
                        AND A.CAT_OD <> ''
                        AND YEAR(A.TGL_OD) > 2002
                        AND A.AK00 + A.GAK00 < 1
                        AND (A.CAT_OD LIKE '%TP%' OR A.CAT_OD LIKE '%DD%')
                        AND LEFT(A.KD_BRG, 3) <> '011'
                        AND A.KDLAKU <> '3'
                        AND A.AW00 = 0 AND A.MA00 = 0 AND A.KE00 = 0 AND A.LN00 = 0
                        AND B.AW11 = 0 AND B.MA11 = 0 AND B.KE11 = 0 AND B.LN11 = 0
                        AND B.AW12 = 0 AND B.MA12 = 0 AND B.KE12 = 0 AND B.LN12 = 0
                ) B
                SET A.HAPUS = 1
                WHERE A.KD_BRG = B.KD_BRG
            ");
        }
    }

    private function prosesNonJanuari()
    {
        // Ambil semua outlet
        $outlets = DB::select("
            SELECT kode 
            FROM tgz.toko 
            WHERE STA IN ('MA', 'CB', 'DC') 
            ORDER BY NO_ID ASC
        ");

        foreach ($outlets as $outlet) {
            $cbg = $outlet->kode;

            // Reset HAPUS = 0
            DB::statement("UPDATE {$cbg}.brg SET HAPUS = 0");

            // Update HAPUS = 1 untuk item yang memenuhi syarat
            DB::statement("
                UPDATE {$cbg}.brg A,
                (
                    SELECT A.KD_BRG
                    FROM {$cbg}.brgdt A
                    WHERE A.TD_OD = '*' 
                        AND A.CAT_OD <> ''
                        AND YEAR(A.TGL_OD) > 2002
                        AND A.AK00 + A.GAK00 < 1
                        AND (A.CAT_OD LIKE '%TP%' OR A.CAT_OD LIKE '%DD%')
                        AND LEFT(A.KD_BRG, 3) <> '011'
                        AND A.KDLAKU <> '3'
                        AND A.AW00 = 0 AND A.MA00 = 0 AND A.KE00 = 0 AND A.LN00 = 0
                        AND A.AW01 = 0 AND A.MA01 = 0 AND A.KE01 = 0 AND A.LN01 = 0
                        AND A.AW02 = 0 AND A.MA02 = 0 AND A.KE02 = 0 AND A.LN02 = 0
                        AND A.AW03 = 0 AND A.MA03 = 0 AND A.KE03 = 0 AND A.LN03 = 0
                        AND A.AW04 = 0 AND A.MA04 = 0 AND A.KE04 = 0 AND A.LN04 = 0
                        AND A.AW05 = 0 AND A.MA05 = 0 AND A.KE05 = 0 AND A.LN05 = 0
                        AND A.AW06 = 0 AND A.MA06 = 0 AND A.KE06 = 0 AND A.LN06 = 0
                        AND A.AW07 = 0 AND A.MA07 = 0 AND A.KE07 = 0 AND A.LN07 = 0
                        AND A.AW08 = 0 AND A.MA08 = 0 AND A.KE08 = 0 AND A.LN08 = 0
                        AND A.AW09 = 0 AND A.MA09 = 0 AND A.KE09 = 0 AND A.LN09 = 0
                        AND A.AW10 = 0 AND A.MA10 = 0 AND A.KE10 = 0 AND A.LN10 = 0
                        AND A.AW11 = 0 AND A.MA11 = 0 AND A.KE11 = 0 AND A.LN11 = 0
                        AND A.AW12 = 0 AND A.MA12 = 0 AND A.KE12 = 0 AND A.LN12 = 0
                ) B
                SET A.HAPUS = 1
                WHERE A.KD_BRG = B.KD_BRG
            ");
        }
    }

    private function prosesUsulan($request, $username)
    {
        // Cek jumlah data yang akan diproses
        $count = DB::selectOne("
            SELECT COUNT(*) AS total 
            FROM brg 
            WHERE TANDA_HPS = 1
        ");

        if ($count->total == 0) {
            DB::rollBack();
            return response()->json(['error' => 'Tidak ada data yang ditandai untuk dihapus'], 400);
        }

        // Insert ke brg_del dan update TANDA_HPS
        DB::statement("
            INSERT INTO brg_del 
            (KD_BRG, NA_BRG, USERX, TGL_IN, CBG, TD_OD, CAT_OD, TGL_OD, TGL_TRM, TGL_BK, TGL_AT)
            SELECT 	
                A.KD_BRG, 
                CONCAT(A.NA_BRG, ' ', A.KET_UK) AS NA_BRG, 
                ? AS USERX, 
                NOW() AS TGL_IN, 
                ? AS CBG,  
                B.TD_OD, 
                B.CAT_OD, 
                B.TGL_OD, 
                B.TGL_TRM, 
                B.TGL_BK, 
                B.TGL_AT
            FROM brg A
            INNER JOIN brgdt B ON A.KD_BRG = B.KD_BRG
            WHERE A.TANDA_HPS = 1
        ", [$username, 'TGZ']);

        DB::statement("
            UPDATE brg
            SET TANDA_HPS = 2
            WHERE TANDA_HPS = 1
        ");

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => "Proses selesai! Total {$count->total} item berhasil diproses.",
            'total' => $count->total
        ]);
    }

    private function cekDataPosting($request)
    {
        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Data posting berhasil dimuat!'
        ]);
    }

    private function prosesPosting($request)
    {
        // Cek data yang akan diposting
        $count = DB::selectOne("
            SELECT COUNT(*) AS total 
            FROM brg_del 
            WHERE POSTED = 0
        ");

        if ($count->total == 0) {
            DB::rollBack();
            return response()->json(['error' => 'Tidak ada data untuk diposting'], 400);
        }

        // Buat folder untuk export
        $dir = 'BRG_DEL/' . date('m_Y') . '/' . date('d') . '/';
        $filename = date('dmY_His') . '_Hapus_BRG.xlsx';

        // Hapus data di semua outlet
        $outlets = DB::select("
            SELECT kode 
            FROM tgz.toko 
            WHERE STA IN ('MA', 'CB', 'DC') 
            ORDER BY NO_ID ASC
        ");

        foreach ($outlets as $outlet) {
            $cbg = $outlet->kode;

            DB::statement("
                DELETE FROM {$cbg}.brg
                WHERE kd_brg IN (SELECT KD_BRG FROM tgz.brg_del WHERE POSTED = 0)
            ");

            DB::statement("
                DELETE FROM {$cbg}.brgd
                WHERE kd_brg IN (SELECT KD_BRG FROM tgz.brg_del WHERE POSTED = 0)
            ");

            DB::statement("
                DELETE FROM {$cbg}.brgdt
                WHERE kd_brg IN (SELECT KD_BRG FROM tgz.brg_del WHERE POSTED = 0)
            ");

            DB::statement("
                DELETE FROM {$cbg}.masks
                WHERE kd_brg IN (SELECT KD_BRG FROM tgz.brg_del WHERE POSTED = 0)
            ");

            DB::statement("
                DELETE FROM {$cbg}.supd2
                WHERE kd_brg IN (SELECT KD_BRG FROM tgz.brg_del WHERE POSTED = 0)
            ");
        }

        // Update POSTED = 1
        DB::statement("
            UPDATE tgz.brg_del
            SET POSTED = 1, TGL_POSTED = NOW()
            WHERE POSTED = 0
        ");

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => "Posting selesai! Total {$count->total} item berhasil dihapus dari semua outlet.",
            'total' => $count->total,
            'folder' => $dir,
            'filename' => $filename
        ]);
    }

    private function toggleCheck($request)
    {
        $kdBrg = $request->input('kd_brg');
        $currentFlag = $request->input('current_flag', 0);

        // Toggle flag: 0 -> 1, 1 -> 0
        $newFlag = $currentFlag == 0 ? 1 : 0;

        DB::statement("
            UPDATE brg
            SET TANDA_HPS = ?
            WHERE KD_BRG = ?
        ", [$newFlag, $kdBrg]);

        DB::commit();

        return response()->json([
            'success' => true,
            'new_flag' => $newFlag
        ]);
    }

    private function batalHapus($request, $username)
    {
        $noId = $request->input('no_id');
        $kdBrg = $request->input('kd_brg');

        // Update brg.TANDA_HPS = 0
        DB::statement("
            UPDATE brg
            SET TANDA_HPS = 0
            WHERE KD_BRG = ?
        ", [$kdBrg]);

        // Hapus dari brg_del
        DB::statement("
            DELETE FROM brg_del
            WHERE NO_ID = ?
        ", [$noId]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Item berhasil dibatalkan dari daftar hapus'
        ]);
    }

    private function exportExcelUsulan($request)
    {
        $query = "
            SELECT	
                A.KD_BRG AS 'Kode Barang', 
                CONCAT(B.NA_BRG, ' ', B.KET_UK) AS 'Nama Barang',  
                A.LPH AS 'LPH', 
                A.KDLAKU AS 'Kode Laku', 
                CONCAT(A.TD_OD, A.CAT_OD) AS 'Kategori OD',
                COALESCE(DATE(A.TGL_OD), '') AS 'Tgl OD',
                COALESCE(DATE(A.TGL_TRM), '') AS 'Tgl Terima',
                COALESCE(DATE(A.TGL_BK), '') AS 'Tgl BK',
                COALESCE(DATE(A.TGL_AT), '') AS 'Tgl Kasir',
                DATEDIFF(CURDATE(), COALESCE(DATE(A.TGL_OD), CURDATE())) AS 'Hari'
            FROM brgdt A
            INNER JOIN brg B ON A.KD_BRG = B.KD_BRG
            WHERE A.TD_OD <> '' 
                AND A.CAT_OD <> ''
                AND YEAR(A.TGL_OD) > 2002
                AND A.AK00 + A.GAK00 < 1
                AND LEFT(A.KD_BRG, 3) <> '011'
                AND B.TANDA_HPS IN (0, 1)
                AND B.HAPUS = 2
            ORDER BY A.KD_BRG ASC
        ";

        $data = DB::select($query);

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

    private function exportExcelPosting($request)
    {
        $query = "
            SELECT 
                KD_BRG AS 'Kode Barang', 
                NA_BRG AS 'Nama Barang', 
                CONCAT(TD_OD, CAT_OD) AS 'Kategori OD', 
                TGL_OD AS 'Tgl OD', 
                TGL_TRM AS 'Tgl Terima', 
                TGL_BK AS 'Tgl BK', 
                TGL_AT AS 'Tgl Kasir', 
                USERX AS 'User', 
                DATE(TGL_IN) AS 'Tgl Usulan'
            FROM brg_del 
            WHERE POSTED = 0
            ORDER BY KD_BRG ASC
        ";

        $data = DB::select($query);

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
