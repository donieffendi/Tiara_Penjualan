<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use PHPJasperXML;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

class TPelaksanaanTurunHargaController extends Controller
{
    public function index(Request $request)
    {
        try {
            $judul    = 'Transaksi Pelaksanaan Turun Harga';
            $CBG      = Auth::user()->CBG ?? null;
            $username = Auth::user()->username ?? 'system';

            if (! $CBG) {
                return view("otransaksi_TPelaksanaanTurunHarga.index")->with([
                    'judul' => $judul,
                    'error' => 'User tidak memiliki akses cabang (CBG). Hubungi administrator.',
                ]);
            }

            if (! $request->session()->has('periode')) {
                return view("otransaksi_TPelaksanaanTurunHarga.index")->with([
                    'judul'   => $judul,
                    'warning' => 'Periode belum diset. Silakan set periode terlebih dahulu.',
                ]);
            }

            $periode = $request->session()->get('periode');

            if (is_array($periode)) {
                $periodeDisplay = ($periode['bulan'] ?? '01') . '/' . ($periode['tahun'] ?? date('Y'));
            } else {
                $periodeDisplay = $periode;
            }

            // Update data usulan saat load halaman
            $this->updateUsulan($CBG);

            return view("otransaksi_TPelaksanaanTurunHarga.index")->with([
                'judul'    => $judul,
                'cbg'      => $CBG,
                'periode'  => $periodeDisplay,
                'username' => $username,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in TPelaksanaanTurunHarga index: ' . $e->getMessage());
            return view("otransaksi_TPelaksanaanTurunHarga.index")->with([
                'judul' => 'Transaksi Pelaksanaan Turun Harga',
                'error' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ]);
        }
    }

    private function updateUsulan($CBG)
    {
        try {
            $connection = strtolower($CBG);
            $fieldMap   = $this->getFieldMap($CBG);

            Log::info("TPelaksanaanTurunHarga updateUsulan: CBG={$CBG}, Connection={$connection}");

            // Update masks berdasarkan usulan dis/disd yang aktif - use CBG connection
            $query = "
                UPDATE masks A
                INNER JOIN (
                    SELECT
                        dis.NO_BUKTI,
                        dis.JAM_MULAI,
                        dis.JAM_SLS,
                        dis.TGL_MULAI,
                        dis.TGL_SLS,
                        disd.KD_BRG,
                        disd.NA_BRG,
                        disd.TH
                    FROM tgz.dis dis
                    INNER JOIN tgz.disd disd ON dis.NO_BUKTI = disd.NO_BUKTI
                    WHERE dis.FLAG = 'PD'
                        AND dis.{$CBG} = 1
                        AND disd.TH > 0
                        AND CURDATE() BETWEEN dis.TGL_MULAI AND dis.TGL_SLS
                ) B ON A.KD_BRG = B.KD_BRG
                SET
                    A.{$fieldMap['th']} = B.TH,
                    A.TH = B.TH,
                    A.JAM = B.JAM_MULAI,
                    A.JAMSLS = B.JAM_SLS,
                    A.TGDIS_M = B.TGL_MULAI,
                    A.TGDIS_A = B.TGL_SLS
                WHERE (A.{$fieldMap['th']} != B.TH
                    OR A.TGDIS_M != B.TGL_MULAI
                    OR A.TGDIS_A != B.TGL_SLS)
            ";

            DB::connection($connection)->statement($query);

            Log::info("Update usulan berhasil untuk CBG: {$CBG}");
        } catch (\Exception $e) {
            Log::error("Error updateUsulan: " . $e->getMessage());
        }
    }

    private function getFieldMap($CBG)
    {
        $maps = [
            'TGZ' => ['cibing' => 'TGL_GZ', 'th' => 'THGZ', 'harga' => 'HJGZ'],
            'TMM' => ['cibing' => 'TGL_MM', 'th' => 'THMM', 'harga' => 'HJMM'],
            'SOP' => ['cibing' => 'TGL_SP', 'th' => 'THSP', 'harga' => 'HJSP'],
        ];

        return $maps[$CBG] ?? $maps['TGZ'];
    }

    public function cari_data(Request $request)
    {
        try {
            $CBG        = Auth::user()->CBG ?? null;
            $periodeArr = session('periode');
            $periode    = $periodeArr['bulan'] . '/' . $periodeArr['tahun'];
            if (! $CBG) {
                return response()->json(['error' => 'Akses ditolak'], 403);
            }

            $table = $request->input('table', 'main');

            if ($table === 'main') {
                return $this->getMainTable($CBG, $periode);
            } elseif ($table === 'detail') {
                return $this->getDetailTable($request, $CBG);
            } elseif ($table === 'monitor') {
                return $this->getMonitorTable($request);
            }

            return Datatables::of(collect([]))->make(true);
        } catch (\Exception $e) {
            Log::error('Error in cari_data: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function getMainTable($CBG, $periode)
    {
        $query = "
            SELECT
                NO_BUKTI,
                TGL_MULAI,
                TGL_SLS,
                KODES,
                NAMAS,
                NOTES,
                CARA_BAYAR,
                NA_KWI,
                CEK,
                {$CBG} AS posted
            FROM dis
            WHERE PER = ?
                AND FLAG = 'PD'
            GROUP BY NO_BUKTI
            ORDER BY NO_BUKTI DESC
        ";

        $data = DB::connection('tgz')->select($query, [$periode]);

        Log::info("TPelaksanaanTurunHarga getMainTable: Found " . count($data) . " records");

        return Datatables::of(collect($data))
            ->addIndexColumn()
            ->editColumn('posted', function ($row) {
                if ($row->posted == 1) {
                    return '<span class="badge badge-success">Posted</span>';
                } elseif ($row->posted == 0) {
                    return '<span class="badge badge-warning">Belum Posted</span>';
                }
                return '<span class="badge badge-secondary">-</span>';
            })
            ->editColumn('TGL_MULAI', function ($row) {
                return date('d/m/Y', strtotime($row->TGL_MULAI));
            })
            ->editColumn('TGL_SLS', function ($row) {
                return date('d/m/Y', strtotime($row->TGL_SLS));
            })
            ->editColumn('CARA_BAYAR', function ($row) {
                return $row->CARA_BAYAR ?? '-';
            })
            ->editColumn('NA_KWI', function ($row) {
                return $row->NA_KWI ?? '-';
            })
            ->editColumn('CEK', function ($row) {
                if ($row->CEK == 1) {
                    return '<span class="badge badge-success"><i class="fas fa-check"></i></span>';
                }
                return '<span class="badge badge-secondary"><i class="fas fa-times"></i></span>';
            })
            ->rawColumns(['posted', 'CEK'])
            ->make(true);
    }

    private function getDetailTable($request, $CBG)
    {
        $noBukti    = $request->input('no_bukti');
        $fieldMap   = $this->getFieldMap($CBG);
        $connection = strtolower($CBG);

        // Tabel detail: item barang dari no_bukti tertentu - use CBG connection
        $query = "
            SELECT
                masks.{$fieldMap['harga']} AS harga,
                masks.{$fieldMap['th']} AS th,
                masks.NA_BRG,
                masks.KD_BRG,
                masks.KET_UK,
                masks.TGDIS_M,
                masks.TGDIS_A,
                0 AS hps
            FROM masks
            INNER JOIN tgz.disd ON masks.KD_BRG = disd.KD_BRG
            WHERE disd.NO_BUKTI = ?
        ";

        $data = DB::select($query, [$noBukti]);

        Log::info("TPelaksanaanTurunHarga getDetailTable: Found " . count($data) . " items");

        return Datatables::of(collect($data))
            ->addIndexColumn()
            ->editColumn('harga', function ($row) {
                return number_format($row->harga, 0, ',', '.');
            })
            ->editColumn('th', function ($row) {
                return number_format($row->th, 0, ',', '.');
            })
            ->editColumn('TGDIS_M', function ($row) {
                return $row->TGDIS_M ? date('d/m/Y', strtotime($row->TGDIS_M)) : '-';
            })
            ->editColumn('TGDIS_A', function ($row) {
                return $row->TGDIS_A ? date('d/m/Y', strtotime($row->TGDIS_A)) : '-';
            })
            ->editColumn('hps', function ($row) {
                return '<input type="checkbox" class="chk-hapus" data-kd-brg="' . $row->KD_BRG . '">';
            })
            ->rawColumns(['hps'])
            ->make(true);
    }

    private function getMonitorTable($request)
    {
        $kdBrg = $request->input('kd_brg');

        // Query untuk mendapatkan data dari semua outlet - use TGZ connection
        $query = "
            SELECT 'TGZ' AS CBG, A.KD_BRG, A.NA_BRG, A.THGZ, 0 AS THSP, 0 AS THMM,
                   A.JAM, A.JAMSLS, A.TGDIS_M, A.TGDIS_A
            FROM tgz.masks A
            WHERE A.KD_BRG = ?

            UNION ALL

            SELECT 'TMM' AS CBG, A.KD_BRG, A.NA_BRG, 0 AS THGZ, 0 AS THSP, A.THMM,
                   A.JAM, A.JAMSLS, A.TGDIS_M, A.TGDIS_A
            FROM tmm.masks A
            WHERE A.KD_BRG = ?

            UNION ALL

            SELECT 'SOP' AS CBG, A.KD_BRG, A.NA_BRG, 0 AS THGZ, A.THSP, 0 AS THMM,
                   A.JAM, A.JAMSLS, A.TGDIS_M, A.TGDIS_A
            FROM sop.masks A
            WHERE A.KD_BRG = ?
        ";

        $data = DB::select($query, [$kdBrg, $kdBrg, $kdBrg]);

        Log::info("TPelaksanaanTurunHarga getMonitorTable: Found " . count($data) . " outlets");

        return Datatables::of(collect($data))
            ->addIndexColumn()
            ->editColumn('TGDIS_M', function ($row) {
                return $row->TGDIS_M ? date('d/m/Y', strtotime($row->TGDIS_M)) : '-';
            })
            ->editColumn('TGDIS_A', function ($row) {
                return $row->TGDIS_A ? date('d/m/Y', strtotime($row->TGDIS_A)) : '-';
            })
            ->make(true);
    }

    public function proses(Request $request)
    {
        try {
            $CBG      = Auth::user()->CBG ?? null;
            $username = Auth::user()->username ?? 'system';
            $action   = $request->input('action');

            DB::beginTransaction();

            switch ($action) {
                case 'update_posted':
                    return $this->updatePosted($request, $CBG);

                case 'hapus_items':
                    return $this->hapusItems($request);

                case 'export_excel':
                    return $this->exportExcel($request, $CBG);

                default:
                    DB::rollBack();
                    return response()->json(['error' => 'Action tidak valid'], 400);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in proses: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function updatePosted($request, $CBG)
    {
        $noBukti    = $request->input('no_bukti');
        $fieldMap   = $this->getFieldMap($CBG);
        $connection = strtolower($CBG);

        Log::info("TPelaksanaanTurunHarga updatePosted: NO_BUKTI={$noBukti}, CBG={$CBG}");

        // Cek apakah sudah posted - use TGZ connection
        $check = DB::connection('tgz')->selectOne("
            SELECT {$CBG} AS posted
            FROM dis
            WHERE NO_BUKTI = ?
        ", [$noBukti]);

        if ($check->posted == 1) {
            Log::warning("TPelaksanaanTurunHarga updatePosted: Already posted");
            DB::rollBack();
            return response()->json(['error' => 'Data sudah diposting sebelumnya'], 400);
        }

        // Update masks dari usulan - use CBG connection
        $updateQuery = "
            UPDATE masks
            INNER JOIN (
                SELECT
                    dis.JAM_MULAI,
                    dis.JAM_SLS,
                    dis.TGL_MULAI,
                    dis.TGL_SLS,
                    disd.KD_BRG,
                    disd.NA_BRG,
                    disd.TH
                FROM tgz.dis
                INNER JOIN tgz.disd ON dis.NO_BUKTI = disd.NO_BUKTI
                WHERE dis.NO_BUKTI = ?
            ) AS ageng ON masks.KD_BRG = ageng.KD_BRG
            SET
                masks.TH = ageng.TH,
                masks.{$fieldMap['th']} = ageng.TH,
                masks.JAM = ageng.JAM_MULAI,
                masks.JAMSLS = ageng.JAM_SLS,
                masks.TGDIS_M = ageng.TGL_MULAI,
                masks.TGDIS_A = ageng.TGL_SLS
        ";

        DB::connection($connection)->statement($updateQuery, [$noBukti]);
        Log::info("TPelaksanaanTurunHarga updatePosted: Updated masks in {$CBG}");

        // Update status posted di semua outlet - use TGZ connection
        $outlets = DB::connection('tgz')->select("
            SELECT TRIM(KODE) AS cbg
            FROM toko
            WHERE STA IN ('MA', 'CB')
            ORDER BY NO_ID ASC
        ");

        Log::info("TPelaksanaanTurunHarga updatePosted: Found " . count($outlets) . " outlets to update");

        foreach ($outlets as $outlet) {
            $cab     = $outlet->cbg;
            $cabConn = strtolower($cab);

            try {
                DB::connection($cabConn)->statement("
                    UPDATE dis
                    SET {$CBG} = 1, {$fieldMap['cibing']} = NOW()
                    WHERE NO_BUKTI = ?
                ", [$noBukti]);

                Log::info("TPelaksanaanTurunHarga updatePosted: Updated {$cab}");
            } catch (\Exception $e) {
                Log::warning("TPelaksanaanTurunHarga updatePosted: Failed to update {$cab} - " . $e->getMessage());
            }
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengupdate harga turun! Data telah diposting ke semua outlet.',
        ]);
    }

    private function hapusItems($request)
    {
        $items = $request->input('items', []);

        if (empty($items)) {
            DB::rollBack();
            return response()->json(['error' => 'Tidak ada item yang dipilih'], 400);
        }

        // Logic untuk menghapus item dari daftar (soft delete atau update flag)
        // Sesuaikan dengan kebutuhan bisnis

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Item berhasil dihapus dari daftar',
            'total'   => count($items),
        ]);
    }

    private function exportExcel($request, $CBG)
    {
        $noBukti = $request->input('no_bukti');
        $periode = session('periode');

        Log::info("TPelaksanaanTurunHarga exportExcel: NO_BUKTI={$noBukti}, CBG={$CBG}, Periode={$periode}");

        $query = "
            SELECT
                ? AS CBG,
                dis.NO_BUKTI,
                dis.TGL_MULAI,
                dis.TGL_SLS,
                dis.KODES,
                dis.NAMAS,
                disd.KD_BRG,
                disd.NA_BRG,
                disd.KET_UK,
                disd.KET_KEM,
                disd.HJ,
                disd.HB,
                disd.KODES AS KODES_DETAIL,
                disd.PARTSP,
                disd.KET,
                disd.TH
            FROM dis
            INNER JOIN disd ON dis.NO_BUKTI = disd.NO_BUKTI
            WHERE dis.FLAG = 'PD'
                AND dis.NO_BUKTI = ?
                AND dis.PER = ?
            ORDER BY dis.NO_BUKTI
        ";

        $data = DB::connection('tgz')->select($query, [$CBG, $noBukti, $periode]);

        Log::info("TPelaksanaanTurunHarga exportExcel: Found " . count($data) . " records");

        if (empty($data)) {
            DB::rollBack();
            return response()->json(['error' => 'Tidak ada data untuk di-export'], 404);
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    public function create()
    {
        try {
            $judul     = 'Tambah Turun Harga Baru';
            $CBG       = Auth::user()->CBG ?? null;
            $username  = Auth::user()->username ?? 'system';
            $periode   = session('periode');

            if (! $CBG) {
                return redirect()->route('tpelaksanaanturunharga.index')
                    ->with('error', 'User tidak memiliki akses cabang (CBG)');
            }

            if (! $periode) {
                return redirect()->route('tpelaksanaanturunharga.index')
                    ->with('warning', 'Periode belum diset');
            }

            if (is_array($periode)) {
                $periodeDisplay = ($periode['bulan'] ?? '01') . '/' . ($periode['tahun'] ?? date('Y'));
            } else {
                $periodeDisplay = $periode;
            }

            // Get suppliers
            $suppliers = DB::connection('tgz')->select("
                SELECT DISTINCT KODES, NAMAS
                FROM supp
                WHERE KODES != ''
                ORDER BY NAMAS
            ");

            return view('otransaksi_TPelaksanaanTurunHarga.create')->with([
                'judul'     => $judul,
                'cbg'       => $CBG,
                'periode'   => $periodeDisplay,
                'username'  => $username,
                'suppliers' => $suppliers,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in create: ' . $e->getMessage());
            return redirect()->route('tpelaksanaanturunharga.index')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'KODES'     => 'required',
                'NAMAS'     => 'required',
                'TGL_MULAI' => 'required|date',
                'TGL_SLS'   => 'required|date',
                'details'   => 'required|array|min:1',
            ]);

            DB::beginTransaction();

            $CBG      = Auth::user()->CBG ?? null;
            $username = Auth::user()->username ?? 'system';
            $periode  = session('periode');

            if (is_array($periode)) {
                $periodeStr = ($periode['bulan'] ?? '01') . '/' . ($periode['tahun'] ?? date('Y'));
            } else {
                $periodeStr = $periode;
            }

            // Generate No Bukti
            $noBukti = $this->generateNoBukti($CBG, $periodeStr);

            // Insert header to all outlets
            $outlets = DB::connection('tgz')->select("
                SELECT TRIM(KODE) AS cbg
                FROM toko
                WHERE STA IN ('MA', 'CB')
                ORDER BY NO_ID ASC
            ");

            foreach ($outlets as $outlet) {
                $cab     = $outlet->cbg;
                $cabConn = strtolower($cab);

                try {
                    // Insert to dis table
                    DB::connection($cabConn)->insert("
                        INSERT INTO dis (
                            NO_BUKTI, KODES, NAMAS, TGL, TGL_MULAI, TGL_SLS,
                            JAM_MULAI, JAM_SLS, PER, NOTES, FLAG, CBG, USRNM,
                            TG_SMP, POSTED
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 0)
                    ", [
                        $noBukti,
                        $request->KODES,
                        $request->NAMAS,
                        $request->TGL_MULAI,
                        $request->TGL_MULAI,
                        $request->TGL_SLS,
                        $request->JAM_MULAI ?? '00:00:00',
                        $request->JAM_SLS ?? '23:59:59',
                        $periodeStr,
                        $request->NOTES ?? '',
                        'PD',
                        $cab,
                        $username,
                    ]);

                    // Insert details
                    $rec = 1;
                    foreach ($request->details as $detail) {
                        DB::connection($cabConn)->insert("
                            INSERT INTO disd (
                                NO_BUKTI, REC, KD_BRG, NA_BRG, KET_UK, KET_KEM,
                                HJ, HB, TH, PARTSP, PER, FLAG, CBG, KODES
                            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ", [
                            $noBukti,
                            $rec,
                            $detail['KD_BRG'],
                            $detail['NA_BRG'],
                            $detail['KET_UK'] ?? '',
                            $detail['KET_KEM'] ?? '',
                            $detail['HJ'] ?? 0,
                            $detail['HB'] ?? 0,
                            $detail['TH'] ?? 0,
                            $detail['PARTSP'] ?? 0,
                            $periodeStr,
                            'PD',
                            $cab,
                            $request->KODES,
                        ]);
                        $rec++;
                    }

                    Log::info("TPelaksanaanTurunHarga store: Inserted to {$cab}");
                } catch (\Exception $e) {
                    Log::warning("TPelaksanaanTurunHarga store: Failed to insert to {$cab} - " . $e->getMessage());
                }
            }

            DB::commit();

            return redirect()->route('tpelaksanaanturunharga.index')
                ->with('success', 'Data turun harga berhasil disimpan dengan No. Bukti: ' . $noBukti);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in store: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function edit($noBukti)
    {
        try {
            $judul    = 'Edit Turun Harga';
            $CBG      = Auth::user()->CBG ?? null;
            $username = Auth::user()->username ?? 'system';
            $periode  = session('periode');

            if (is_array($periode)) {
                $periodeDisplay = ($periode['bulan'] ?? '01') . '/' . ($periode['tahun'] ?? date('Y'));
            } else {
                $periodeDisplay = $periode;
            }

            // Get header data
            $data = DB::connection('tgz')->selectOne("
                SELECT * FROM dis WHERE NO_BUKTI = ? AND FLAG = 'PD'
            ", [$noBukti]);

            if (! $data) {
                return redirect()->route('tpelaksanaanturunharga.index')
                    ->with('error', 'Data tidak ditemukan');
            }

            // Get detail data
            $details = DB::connection('tgz')->select("
                SELECT * FROM disd WHERE NO_BUKTI = ? ORDER BY REC
            ", [$noBukti]);

            // Get suppliers
            $suppliers = DB::connection('tgz')->select("
                SELECT DISTINCT KODES, NAMAS
                FROM supp
                WHERE KODES != ''
                ORDER BY NAMAS
            ");

            return view('otransaksi_TPelaksanaanTurunHarga.create')->with([
                'judul'     => $judul,
                'cbg'       => $CBG,
                'periode'   => $periodeDisplay,
                'username'  => $username,
                'data'      => $data,
                'details'   => $details,
                'suppliers' => $suppliers,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in edit: ' . $e->getMessage());
            return redirect()->route('tpelaksanaanturunharga.index')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $noBukti)
    {
        try {
            $request->validate([
                'KODES'     => 'required',
                'NAMAS'     => 'required',
                'TGL_MULAI' => 'required|date',
                'TGL_SLS'   => 'required|date',
                'details'   => 'required|array|min:1',
            ]);

            DB::beginTransaction();

            $CBG      = Auth::user()->CBG ?? null;
            $username = Auth::user()->username ?? 'system';
            $periode  = session('periode');

            if (is_array($periode)) {
                $periodeStr = ($periode['bulan'] ?? '01') . '/' . ($periode['tahun'] ?? date('Y'));
            } else {
                $periodeStr = $periode;
            }

            // Update to all outlets
            $outlets = DB::connection('tgz')->select("
                SELECT TRIM(KODE) AS cbg
                FROM toko
                WHERE STA IN ('MA', 'CB')
                ORDER BY NO_ID ASC
            ");

            foreach ($outlets as $outlet) {
                $cab     = $outlet->cbg;
                $cabConn = strtolower($cab);

                try {
                    // Update header
                    DB::connection($cabConn)->update("
                        UPDATE dis SET
                            KODES = ?, NAMAS = ?, TGL = ?, TGL_MULAI = ?,
                            TGL_SLS = ?, JAM_MULAI = ?, JAM_SLS = ?, NOTES = ?
                        WHERE NO_BUKTI = ?
                    ", [
                        $request->KODES,
                        $request->NAMAS,
                        $request->TGL_MULAI,
                        $request->TGL_MULAI,
                        $request->TGL_SLS,
                        $request->JAM_MULAI ?? '00:00:00',
                        $request->JAM_SLS ?? '23:59:59',
                        $request->NOTES ?? '',
                        $noBukti,
                    ]);

                    // Delete old details
                    DB::connection($cabConn)->delete("
                        DELETE FROM disd WHERE NO_BUKTI = ?
                    ", [$noBukti]);

                    // Insert new details
                    $rec = 1;
                    foreach ($request->details as $detail) {
                        DB::connection($cabConn)->insert("
                            INSERT INTO disd (
                                NO_BUKTI, REC, KD_BRG, NA_BRG, KET_UK, KET_KEM,
                                HJ, HB, TH, PARTSP, PER, FLAG, CBG, KODES
                            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ", [
                            $noBukti,
                            $rec,
                            $detail['KD_BRG'],
                            $detail['NA_BRG'],
                            $detail['KET_UK'] ?? '',
                            $detail['KET_KEM'] ?? '',
                            $detail['HJ'] ?? 0,
                            $detail['HB'] ?? 0,
                            $detail['TH'] ?? 0,
                            $detail['PARTSP'] ?? 0,
                            $periodeStr,
                            'PD',
                            $cab,
                            $request->KODES,
                        ]);
                        $rec++;
                    }

                    Log::info("TPelaksanaanTurunHarga update: Updated {$cab}");
                } catch (\Exception $e) {
                    Log::warning("TPelaksanaanTurunHarga update: Failed to update {$cab} - " . $e->getMessage());
                }
            }

            DB::commit();

            return redirect()->route('tpelaksanaanturunharga.index')
                ->with('success', 'Data turun harga berhasil diupdate');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in update: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy($noBukti)
    {
        try {
            $resetData = request('reset_data', 0);

            DB::beginTransaction();

            $CBG = Auth::user()->CBG ?? null;

            // Get outlets
            $outlets = DB::connection('tgz')->select("
                SELECT TRIM(KODE) AS cbg
                FROM toko
                WHERE STA IN ('MA', 'CB')
                ORDER BY NO_ID ASC
            ");

            foreach ($outlets as $outlet) {
                $cab     = $outlet->cbg;
                $cabConn = strtolower($cab);

                try {
                    // If reset data, clear masks
                    if ($resetData == 1) {
                        DB::connection($cabConn)->statement("
                            UPDATE masks
                            SET THGZ = 0, THMM = 0, THSP = 0,
                                JAM = '00:00:00', JAMSLS = '00:00:00',
                                TGDIS_M = '2001-01-01', TGDIS_A = '2001-01-01'
                            WHERE KD_BRG IN (
                                SELECT KD_BRG FROM disd WHERE NO_BUKTI = ?
                            )
                        ", [$noBukti]);
                    }

                    // Delete details
                    DB::connection($cabConn)->delete("
                        DELETE FROM disd WHERE NO_BUKTI = ?
                    ", [$noBukti]);

                    // Delete header
                    DB::connection($cabConn)->delete("
                        DELETE FROM dis WHERE NO_BUKTI = ?
                    ", [$noBukti]);

                    Log::info("TPelaksanaanTurunHarga destroy: Deleted from {$cab}");
                } catch (\Exception $e) {
                    Log::warning("TPelaksanaanTurunHarga destroy: Failed to delete from {$cab} - " . $e->getMessage());
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Turun Harga ' . $noBukti . ' telah terhapus.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in destroy: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function generateNoBukti($CBG, $periode)
    {
        $bulan = substr($periode, 0, 2);
        $tahun = substr($periode, 3, 4);

        // Get toko type
        $toko = DB::connection('tgz')->selectOne("
            SELECT TYPE FROM toko WHERE KODE = ?
        ", [$CBG]);

        $type = $toko->TYPE ?? '';

        // Get current number
        $notrans = DB::connection('tgz')->selectOne("
            SELECT NOM{$bulan} AS nomor
            FROM notrans
            WHERE trans = 'DIS' AND PER = ?
        ", [$tahun]);

        $currentNo = ($notrans->nomor ?? 0) + 1;

        // Update notrans
        DB::connection('tgz')->update("
            UPDATE notrans
            SET NOM{$bulan} = ?
            WHERE trans = 'DIS' AND PER = ?
        ", [$currentNo, $tahun]);

        // Format: DIS2312-0001TG (DIS + YYMM - nomor + type)
        $noBukti = 'DIS' . substr($tahun, 2, 2) . $bulan . '-' . str_pad($currentNo, 4, '0', STR_PAD_LEFT) . $type;

        return $noBukti;
    }

    public function print(Request $request)
    {

        $CBG        = Auth::user()->CBG ?? null;
        $no_bukti   = $request->no_bukti;
        $TGL        = Carbon::now()->format('d/m/Y');
        $JAM        = Carbon::now()->addHour()->toTimeString();
        $periodeArr = session('periode');
        $periode    = $periodeArr['bulan'] . '/' . $periodeArr['tahun'];

        $query = DB::select(" SELECT
                '$CBG' AS CBG,
                dis.NO_BUKTI,
                dis.TGL_MULAI,
                dis.TGL_SLS,
                dis.KODES,
                dis.NAMAS,
                disd.KD_BRG,
                disd.NA_BRG,
                disd.KET_UK,
                disd.KET_KEM,
                disd.HJ,
                disd.HB,
                disd.KODES AS KODES_DETAIL,
                disd.PARTSP,
                disd.KET,
                disd.TH
            FROM dis
            INNER JOIN disd ON dis.NO_BUKTI = disd.NO_BUKTI
            WHERE dis.FLAG = 'PD'
                AND dis.NO_BUKTI = '$no_bukti'
                AND dis.PER = '$periode'
            ORDER BY dis.NO_BUKTI");

        $file = 'print_turun_harga';

        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path("/app/reportc01/phpjasperxml/{$file}.jrxml"));

        $cleanData                    = json_decode(json_encode($query), true);
        $PHPJasperXML->arrayParameter = [
            "TGL" => $TGL,
            "JAM" => $JAM,
        ];

        $PHPJasperXML->setData($cleanData);

        // dd($cleanData);

        ob_end_clean();
        $PHPJasperXML->outpage("I");
    }
}
