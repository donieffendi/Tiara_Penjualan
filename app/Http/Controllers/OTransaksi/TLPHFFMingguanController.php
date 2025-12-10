<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;
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
                SELECT KD_BRG, 
                    NA_BRG,
                    KET_UK, 
                    KET_KEM, 
                    MO, 
                    LPH_TMM, 
                    LPH_GZ, 
                    LPH_KG, 
                    LPH_TGZ_LL, 
                    LPH_TMM_LL, 
                    LPH_SOP_LL,
                    LPHGZ,
                    LPHTMM,
                    LPHKG,
                    JLGZ,
                    JLMM,
                    JLKG,
                    TS_GZ,
                    TS_KG,
                    TS_MM,
                    KSG_GZ,
                    KSG_TMM,
                    KSG_KG,
                    KETERANGAN
                FROM TGZ.lphkode3_ff
                ORDER BY KD_BRG
            ";

            $data = DB::select($query);

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
                ->editColumn('MO', function ($row) {
                    return number_format($row->MO ?? 0, 2);
                }) 
                ->editColumn('LPH_TMM', function ($row) {
                    return number_format($row->LPH_TMM ?? 0, 2);
                }) 
                ->editColumn('LPH_GZ', function ($row) {
                    return number_format($row->LPH_GZ ?? 0, 2);
                }) 
                ->editColumn('LPH_KG', function ($row) {
                    return number_format($row->LPH_KG ?? 0, 2);
                }) 
                ->editColumn('LPH_TGZ_LL', function ($row) {
                    return number_format($row->LPH_TGZ_LL ?? 0, 2);
                }) 
                ->editColumn('LPH_TMM_LL', function ($row) {
                    return number_format($row->LPH_TMM_LL ?? 0, 2);
                }) 
                ->editColumn('LPH_SOP_LL', function ($row) {
                    return number_format($row->LPH_SOP_LL ?? 0, 2);
                })
                ->editColumn('LPHKG', function ($row) {
                    return number_format($row->LPHKG ?? 0, 2);
                })
                ->editColumn('LPHGZ', function ($row) {
                    return number_format($row->LPHGZ ?? 0, 2);
                })
                ->editColumn('LPHTMM', function ($row) {
                    return number_format($row->LPHTMM ?? 0, 2);
                })
                ->editColumn('JLGZ', function ($row) {
                    return number_format($row->JLGZ ?? 0, 2);
                })
                ->editColumn('JLMM', function ($row) {
                    return number_format($row->JLMM ?? 0, 2);
                })
                ->editColumn('JLKG', function ($row) {
                    return number_format($row->JLKG ?? 0, 2);
                })
                ->editColumn('TS_GZ', function ($row) {
                    return number_format($row->TS_GZ ?? 0, 2);
                })
                ->editColumn('TS_KG', function ($row) {
                    return number_format($row->TS_KG ?? 0, 2);
                })
                ->editColumn('TS_MM', function ($row) {
                    return number_format($row->TS_MM ?? 0, 2);
                })
                ->editColumn('KSG_GZ', function ($row) {
                    return number_format($row->KSG_GZ ?? 0, 2);
                })
                ->editColumn('KSG_TMM', function ($row) {
                    return number_format($row->KSG_TMM ?? 0, 2);
                })
                ->editColumn('KSG_KG', function ($row) {
                    return number_format($row->KSG_KG ?? 0, 2);
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
            $cekData = DB::select("
                SELECT kd_brg 
                FROM lphkode3_ff 
                WHERE tgl = CURDATE() 
                GROUP BY kd_brg
            ");

            if (!empty($cekData)) {
                return response()->json(['error' => 'Data sudah posting hari ini!!!'], 400);
            }

            // Jalankan stored procedure untuk generate data LPH
            DB::statement("CALL lph_mingguan_ff(CURDATE())");

            Log::info('TLPHFFMingguan ambil_data - Stored procedure executed successfully');

            // Verifikasi data berhasil dibuat
            $verifyData = DB::select("
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
            DB::beginTransaction();
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
            DB::statement("
                UPDATE brg a, lphkode3_ff b 
                SET a.{$cebong} = b.LPH_{$cibing} 
                WHERE a.KD_BRG = b.KD_BRG
            ");

            DB::statement("
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
            DB::commit();
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
                DB::rollBack();
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
                SELECT KD_BRG, 
                    NA_BRG,
                    KET_UK, 
                    KET_KEM, 
                    MO, 
                    LPH_TMM, 
                    LPH_GZ, 
                    LPH_KG, 
                    LPH_TGZ_LL, 
                    LPH_TMM_LL, 
                    LPH_SOP_LL,
                    LPHGZ,
                    LPHTMM,
                    LPHKG,
                    JLGZ,
                    JLMM,
                    JLKG,
                    TS_GZ,
                    TS_KG,
                    TS_MM,
                    KSG_GZ,
                    KSG_TMM,
                    KSG_KG,
                    KETERANGAN
                FROM TGZ.lphkode3_ff
                WHERE DATE(tgl) = CURDATE()
                ORDER BY kd_brg
            ";

            $data = DB::select($query);

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
    public function print()
    {
        $file= 'TLPHFFMingguan';
        $query = "
                SELECT 
                    UR, 
                    KD_BRG, 
                    NA_BRG,
                    KET_UK, 
                    KET_KEM, 
                    MO, 
                    LPH_TMM, 
                    LPH_GZ, 
                    LPH_KG, 
                    LPH_TGZ_LL, 
                    LPH_TMM_LL, 
                    LPH_SOP_LL,
                    LPHGZ,
                    LPHTMM,
                    LPHKG,
                    JLGZ,
                    JLMM,
                    JLKG,
                    TS_GZ,
                    TS_KG,
                    TS_MM,
                    KSG_GZ,
                    KSG_TMM,
                    KSG_KG,
                    KETERANGAN
                FROM TGZ.lphkode3_ff
                ORDER BY UR, KD_BRG
            ";

        $data = DB::select($query);
        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));
        foreach ($data as $key => $value) {
            $data[$key]->JUDUL = 'USULAN PERUBAHAN L.P.H';
            $data[$key]->TGL_NOW = now()->format('d/m/Y');
            $data[$key]->TIME_NOW = now()->format('H:i');
        }
        $PHPJasperXML->setData(array_map(function ($item) {
            return (array) $item;
        }, $data));
        ob_end_clean();
        $PHPJasperXML->outpage("I");
    }
}
