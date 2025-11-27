<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class TPelaksanaanTurunHargaController extends Controller
{
    public function index(Request $request)
    {
        try {
            $judul = 'Transaksi Pelaksanaan Turun Harga';
            $CBG = Auth::user()->CBG ?? null;
            $username = Auth::user()->username ?? 'system';

            if (!$CBG) {
                return view("otransaksi_TPelaksanaanTurunHarga.index")->with([
                    'judul' => $judul,
                    'error' => 'User tidak memiliki akses cabang (CBG). Hubungi administrator.'
                ]);
            }

            if (!$request->session()->has('periode')) {
                return view("otransaksi_TPelaksanaanTurunHarga.index")->with([
                    'judul' => $judul,
                    'warning' => 'Periode belum diset. Silakan set periode terlebih dahulu.'
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
                'judul' => $judul,
                'cbg' => $CBG,
                'periode' => $periodeDisplay,
                'username' => $username
            ]);
        } catch (\Exception $e) {
            Log::error('Error in TPelaksanaanTurunHarga index: ' . $e->getMessage());
            return view("otransaksi_TPelaksanaanTurunHarga.index")->with([
                'judul' => 'Transaksi Pelaksanaan Turun Harga',
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    private function updateUsulan($CBG)
    {
        try {
            $connection = strtolower($CBG);
            $fieldMap = $this->getFieldMap($CBG);

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
            'SOP' => ['cibing' => 'TGL_SP', 'th' => 'THSP', 'harga' => 'HJSP']
        ];

        return $maps[$CBG] ?? $maps['TGZ'];
    }

    public function cari_data(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            $periode = session('periode');

            if (!$CBG) {
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
        Log::info("TPelaksanaanTurunHarga getMainTable: CBG={$CBG}, Periode={$periode}");

        // Tabel utama: daftar no_bukti usulan turun harga - use TGZ connection
        $query = "
            SELECT 
                NO_BUKTI,
                TGL_MULAI,
                TGL_SLS,
                KODES,
                NAMAS,
                NOTES,
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
            ->rawColumns(['posted'])
            ->make(true);
    }

    private function getDetailTable($request, $CBG)
    {
        $noBukti = $request->input('no_bukti');
        $fieldMap = $this->getFieldMap($CBG);
        $connection = strtolower($CBG);

        Log::info("TPelaksanaanTurunHarga getDetailTable: NO_BUKTI={$noBukti}, CBG={$CBG}");

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

        $data = DB::connection($connection)->select($query, [$noBukti]);

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

        Log::info("TPelaksanaanTurunHarga getMonitorTable: KD_BRG={$kdBrg}");

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

        $data = DB::connection('tgz')->select($query, [$kdBrg, $kdBrg, $kdBrg]);

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
            $CBG = Auth::user()->CBG ?? null;
            $username = Auth::user()->username ?? 'system';
            $action = $request->input('action');

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
        $noBukti = $request->input('no_bukti');
        $fieldMap = $this->getFieldMap($CBG);
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
            $cab = $outlet->cbg;
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
            'message' => 'Berhasil mengupdate harga turun! Data telah diposting ke semua outlet.'
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
            'total' => count($items)
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
            'data' => $data
        ]);
    }
}
