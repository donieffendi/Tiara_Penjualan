<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class TLPHFFMingguanController extends Controller
{
    public function index(Request $request)
    {
        try {
            $judul = 'Transaksi LPH FF Mingguan';

            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                return view("otransaksi_TLPHFFMingguan.index")->with([
                    'judul' => $judul,
                    'error' => 'User tidak memiliki akses cabang (CBG). Hubungi administrator.'
                ]);
            }

            if (!$request->session()->has('periode')) {
                return view("otransaksi_TLPHFFMingguan.index")->with([
                    'judul' => $judul,
                    'warning' => 'Periode belum diset. Silakan set periode terlebih dahulu.'
                ]);
            }

            $periode = $request->session()->get('periode');

            return view("otransaksi_TLPHFFMingguan.index")->with([
                'judul' => $judul,
                'cbg' => $CBG,
                'periode' => $periode
            ]);
        } catch (\Exception $e) {
            Log::error('Error in TLPHFFMingguan index: ' . $e->getMessage());
            return view("otransaksi_TLPHFFMingguan.index")->with([
                'judul' => 'Transaksi LPH FF Mingguan',
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    public function cari_data(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            $connection = strtolower($CBG);

            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            $periode = $request->session()->get('periode');
            if (!$periode) {
                return response()->json(['error' => 'Periode belum diset'], 400);
            }

            Log::info('TLPHFFMingguan cari_data', [
                'CBG' => $CBG,
                'connection' => $connection
            ]);

            // Ambil data LPH FF Mingguan dari database TGZ
            $query = "
                SELECT 
                    ur,
                    LEFT(kd_brg, 3) as sub,
                    RIGHT(kd_brg, 4) as kdbar,
                    kd_brg,
                    na_brg,
                    ket_uk,
                    ket_kem,
                    mo,
                    jl_tmm,
                    jl_gz,
                    jl_kg,
                    ll_tmm,
                    ll_gz,
                    ll_kg,
                    lph_tmm,
                    lph_gz,
                    lph_kg,
                    laku_kasir,
                    ts_gz,
                    ts_kg,
                    ts_mm,
                    jam_kosong,
                    keterangan,
                    tgl
                FROM lphkode3_ff
                ORDER BY ur, kd_brg
            ";

            $data = DB::connection('tgz')->select($query);

            Log::info('TLPHFFMingguan cari_data - raw_query_untuk_navicat', [
                'query' => 'USE tgz; ' . $query,
                'result_count' => count($data)
            ]);

            if (empty($data)) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'Data kosong, silakan AMBIL DATA terlebih dahulu'
                ]);
            }

            return Datatables::of(collect($data))
                ->addIndexColumn()
                ->editColumn('mo', function ($row) {
                    return number_format($row->mo ?? 0, 2);
                })
                ->editColumn('jl_tmm', function ($row) {
                    return number_format($row->jl_tmm ?? 0, 2);
                })
                ->editColumn('jl_gz', function ($row) {
                    return number_format($row->jl_gz ?? 0, 2);
                })
                ->editColumn('jl_kg', function ($row) {
                    return number_format($row->jl_kg ?? 0, 2);
                })
                ->editColumn('ll_tmm', function ($row) {
                    return number_format($row->ll_tmm ?? 0, 2);
                })
                ->editColumn('ll_gz', function ($row) {
                    return number_format($row->ll_gz ?? 0, 2);
                })
                ->editColumn('ll_kg', function ($row) {
                    return number_format($row->ll_kg ?? 0, 2);
                })
                ->editColumn('lph_tmm', function ($row) {
                    return number_format($row->lph_tmm ?? 0, 2, '.', '');
                })
                ->editColumn('lph_gz', function ($row) {
                    return number_format($row->lph_gz ?? 0, 2, '.', '');
                })
                ->editColumn('lph_kg', function ($row) {
                    return number_format($row->lph_kg ?? 0, 2, '.', '');
                })
                ->editColumn('laku_kasir', function ($row) {
                    return number_format($row->laku_kasir ?? 0, 2);
                })
                ->editColumn('ts_gz', function ($row) {
                    return number_format($row->ts_gz ?? 0, 2);
                })
                ->editColumn('ts_kg', function ($row) {
                    return number_format($row->ts_kg ?? 0, 2);
                })
                ->editColumn('ts_mm', function ($row) {
                    return number_format($row->ts_mm ?? 0, 2);
                })
                ->make(true);
        } catch (\Exception $e) {
            Log::error('Error in cari_data: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function ambil_data(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            $connection = strtolower($CBG);

            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            Log::info('TLPHFFMingguan ambil_data', [
                'CBG' => $CBG,
                'connection' => $connection
            ]);

            // Cek apakah data sudah ada untuk hari ini
            $cekData = DB::connection('tgz')->select("
                SELECT kd_brg 
                FROM lphkode3_ff 
                WHERE tgl = CURDATE() 
                GROUP BY kd_brg
            ");

            if (!empty($cekData)) {
                return response()->json(['error' => 'Data sudah posting hari ini!!!'], 400);
            }

            // Jalankan stored procedure untuk generate data LPH
            DB::connection('tgz')->statement("CALL lph_mingguan_ff(CURDATE())");

            Log::info('TLPHFFMingguan ambil_data - Stored procedure executed successfully');

            // Verifikasi data berhasil dibuat
            $verifyData = DB::connection('tgz')->select("
                SELECT COUNT(*) as total 
                FROM lphkode3_ff 
                WHERE DATE(tgl) = CURDATE()
            ");

            $totalRecords = $verifyData[0]->total ?? 0;

            Log::info('TLPHFFMingguan ambil_data - Data verified', [
                'total_records' => $totalRecords
            ]);

            if ($totalRecords == 0) {
                return response()->json([
                    'error' => 'Stored procedure dijalankan tetapi tidak menghasilkan data. Periksa stored procedure lph_mingguan_ff.'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diambil! Total ' . $totalRecords . ' records dibuat.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in ambil_data: ' . $e->getMessage());
            return response()->json([
                'error' => 'Gagal mengambil data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function proses(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            $connection = strtolower($CBG);

            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            $periode = $request->session()->get('periode');
            if (!$periode) {
                return response()->json(['error' => 'Periode belum diset'], 400);
            }

            $dataItems = $request->input('items', []);

            if (empty($dataItems)) {
                return response()->json(['error' => 'Tidak ada data untuk diproses'], 400);
            }

            Log::info('TLPHFFMingguan proses', [
                'CBG' => $CBG,
                'connection' => $connection,
                'items_count' => count($dataItems)
            ]);

            DB::connection($connection)->beginTransaction();
            DB::connection('tgz')->beginTransaction();
            DB::connection('sop')->beginTransaction();
            DB::connection('tmm')->beginTransaction();

            // Tentukan cibing berdasarkan CBG
            $cibing = 'GZ'; // Default
            if ($CBG == 'TMM') {
                $cibing = 'MM';
            } elseif ($CBG == 'SOP') {
                $cibing = 'KG';
            }

            $cebong = 'LPH';
            if ($cibing == 'MM') {
                $cebong = 'lph_tm';
            } elseif ($cibing == 'KG') {
                $cebong = 'lph_tf';
            }

            Log::info('TLPHFFMingguan proses - parameters', [
                'cibing' => $cibing,
                'cebong' => $cebong
            ]);

            // Update brgdt di outlet sesuai CBG
            DB::connection($connection)->statement("
                UPDATE brgdt a, tgz.lphkode3_ff b 
                SET 
                    a.LPH = b.LPH_{$cibing},
                    a.TGL_LPH = NOW(),
                    a.SRMIN = b.LPH_{$cibing},
                    a.SRMAX = ROUND(b.LPH_{$cibing} * 1.5)
                WHERE a.cbg = ? 
                AND a.KD_BRG = b.KD_BRG
            ", [$CBG]);

            // Update brg di TGZ
            DB::connection('tgz')->statement("
                UPDATE brg a, lphkode3_ff b 
                SET a.{$cebong} = b.LPH_{$cibing} 
                WHERE a.KD_BRG = b.KD_BRG
            ");

            DB::connection('tgz')->statement("
                UPDATE brg A, lphkode3_ff B 
                SET A.DTR2 = IF(
                    ROUND(A.DTB * A.{$cebong}) < 3,
                    3 * SUBSTR(TRIM(A.KET_KEM), ((LOCATE('/', TRIM(A.ket_kem)) + 1))),
                    CEILING(A.DTB * A.{$cebong})
                )
                WHERE A.KD_BRG = B.KD_BRG
            ");

            // Update brg di SOP
            DB::connection('sop')->statement("
                UPDATE brg a, tgz.lphkode3_ff b 
                SET a.{$cebong} = b.LPH_{$cibing} 
                WHERE a.KD_BRG = b.KD_BRG
            ");

            DB::connection('sop')->statement("
                UPDATE brg A, tgz.lphkode3_ff B 
                SET A.DTR2 = IF(
                    ROUND(A.DTB * A.{$cebong}) < 3,
                    3 * SUBSTR(TRIM(A.KET_KEM), ((LOCATE('/', TRIM(A.ket_kem)) + 1))),
                    CEILING(A.DTB * A.{$cebong})
                )
                WHERE A.KD_BRG = B.KD_BRG
            ");

            // Update brg di TMM
            DB::connection('tmm')->statement("
                UPDATE brg a, tgz.lphkode3_ff b 
                SET a.{$cebong} = b.LPH_{$cibing} 
                WHERE a.KD_BRG = b.KD_BRG
            ");

            DB::connection('tmm')->statement("
                UPDATE brg A, tgz.lphkode3_ff B 
                SET A.DTR2 = IF(
                    ROUND(A.DTB * A.{$cebong}) < 3,
                    3 * SUBSTR(TRIM(A.KET_KEM), ((LOCATE('/', TRIM(A.ket_kem)) + 1))),
                    CEILING(A.DTB * A.{$cebong})
                )
                WHERE A.KD_BRG = B.KD_BRG
            ");

            DB::connection($connection)->commit();
            DB::connection('tgz')->commit();
            DB::connection('sop')->commit();
            DB::connection('tmm')->commit();

            Log::info('TLPHFFMingguan proses - completed successfully');

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diproses dan diupdate ke semua database!<br>Database: ' . $CBG . ', TGZ, SOP, TMM'
            ]);
        } catch (\Exception $e) {
            try {
                DB::connection(strtolower($CBG))->rollBack();
                DB::connection('tgz')->rollBack();
                DB::connection('sop')->rollBack();
                DB::connection('tmm')->rollBack();
            } catch (\Exception $rollbackError) {
                Log::error('TLPHFFMingguan proses - rollback error: ' . $rollbackError->getMessage());
            }

            Log::error('Error in proses: ' . $e->getMessage());
            return response()->json([
                'error' => 'Proses gagal: ' . $e->getMessage()
            ], 500);
        }
    }

    public function detail(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;

            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            Log::info('TLPHFFMingguan detail', [
                'CBG' => $CBG
            ]);

            // Get detail untuk cetak
            $query = "
                SELECT 
                    kd_brg,
                    na_brg,
                    ket_uk,
                    ket_kem,
                    mo,
                    jl_tmm,
                    jl_gz,
                    jl_kg,
                    ll_tmm,
                    ll_gz,
                    ll_kg,
                    lph_tmm,
                    lph_gz,
                    lph_kg,
                    laku_kasir,
                    ts_gz,
                    ts_kg,
                    ts_mm,
                    jam_kosong,
                    keterangan,
                    tgl
                FROM lphkode3_ff
                WHERE DATE(tgl) = CURDATE()
                ORDER BY ur, kd_brg
            ";

            $data = DB::connection('tgz')->select($query);

            if (empty($data)) {
                return response()->json(['error' => 'Data tidak ditemukan'], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $data,
                'cbg' => $CBG,
                'tanggal' => date('d-m-Y')
            ]);
        } catch (\Exception $e) {
            Log::error('Error in detail: ' . $e->getMessage());
            return response()->json([
                'error' => 'Gagal mengambil detail: ' . $e->getMessage()
            ], 500);
        }
    }
}
