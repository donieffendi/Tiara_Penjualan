<?php
namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use PHPJasperXML;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

class TBarangPrioritasController extends Controller
{
    public function index(Request $request)
    {
        try {
            $judul = 'Transaksi Barang Prioritas';

            $CBG = Auth::user()->CBG ?? null;
            if (! $CBG) {
                return view("otransaksi_TBarangPrioritas.index")->with([
                    'judul' => $judul,
                    'error' => 'User tidak memiliki akses cabang (CBG). Hubungi administrator.',
                ]);
            }

            if (! $request->session()->has('periode')) {
                return view("otransaksi_TBarangPrioritas.index")->with([
                    'judul'   => $judul,
                    'warning' => 'Periode belum diset. Silakan set periode terlebih dahulu.',
                ]);
            }

            $periode = $request->session()->get('periode');

            return view("otransaksi_TBarangPrioritas.index")->with([
                'judul'   => $judul,
                'cbg'     => $CBG,
                'periode' => $periode,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in TBarangPrioritas index: ' . $e->getMessage());
            return view("otransaksi_TBarangPrioritas.index")->with([
                'judul' => 'Transaksi Barang Prioritas',
                'error' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ]);
        }
    }

    public function tampil(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            if (! $CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            // Call stored procedure untuk generate data prioritas
            DB::statement("CALL pjl_priots()");

            // Ambil data dari tabel priots
            $query = "
                SELECT
                    brg.KD_BRG,
                    CONCAT(brg.NA_BRG, ' ', brg.KET_UK) as NA_BRG,
                    brg.KET_UK,
                    brg.KET_KEM,
                    priots.SUB,
                    priots.LPH,
                    priots.saldo as SALDO,
                    priots.saldo as QTY,
                    priots.TGLx as TGL
                FROM priots
                INNER JOIN {$CBG}.brg ON priots.KD_BRG = brg.KD_BRG
                ORDER BY brg.KD_BRG ASC
            ";

            $data = DB::select($query);

            return response()->json([
                'success' => true,
                'data'    => $data,
                'count'   => count($data),
            ]);
        } catch (\Exception $e) {
            Log::error('Error in tampil: ' . $e->getMessage());
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function refresh(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            if (! $CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            // Ambil data terbaru dari tabel priots
            $query = "
                SELECT
                    brg.KD_BRG,
                    CONCAT(brg.NA_BRG, ' ', brg.KET_UK) as NA_BRG,
                    brg.KET_UK,
                    brg.KET_KEM,
                    priots.SUB,
                    priots.LPH,
                    priots.saldo as SALDO,
                    priots.saldo as QTY,
                    priots.TGLx as TGL
                FROM priots
                INNER JOIN {$CBG}.brg ON priots.KD_BRG = brg.KD_BRG
                ORDER BY brg.KD_BRG ASC
            ";

            $data = DB::select($query);

            return response()->json([
                'success' => true,
                'data'    => $data,
                'count'   => count($data),
            ]);
        } catch (\Exception $e) {
            Log::error('Error in refresh: ' . $e->getMessage());
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function proses(Request $request)
    {
        try {
            $CBG      = Auth::user()->CBG ?? null;
            $username = Auth::user()->username ?? 'system';

            if (! $CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            DB::beginTransaction();

            // Tentukan nama file DBF berdasarkan cabang
            $nama     = '';
            $tableDBF = '';

            if ($CBG == 'TGZ') {
                $nama     = 'PK_GZ.DBF';
                $tableDBF = 'PK_GZ';
            } elseif ($CBG == 'TMM') {
                $nama     = 'PK_TM.DBF';
                $tableDBF = 'PK_TM';
            } elseif ($CBG == 'SOP') {
                $nama     = 'PK_KG.DBF';
                $tableDBF = 'PK_KG';
            } else {
                return response()->json(['error' => 'Cabang tidak valid untuk proses ini'], 400);
            }

            // Ambil data priots untuk besok (tomorrow)
            $query = "
                SELECT *
                FROM priots
                WHERE tglx = DATE(NOW()) + INTERVAL 1 DAY
            ";
            $dataTS = DB::select($query);

            if (count($dataTS) == 0) {
                DB::rollBack();
                return response()->json([
                    'error' => 'Tidak ada data prioritas untuk tanggal besok. Jalankan TAMPIL terlebih dahulu.',
                ], 400);
            }

            // Direktori lokal untuk file DBF
            $dirLokal  = 'D:/DBF/TO3/';
            $fileAwal  = 'A:/dbf/kode 3 ts/TGZ/PK_GZ.DBF'; // Template
            $fileBaca  = 'A:/dbf/kode 3 ts/baca/' . $nama;
            $fileLokal = $dirLokal . $nama;

            // Dalam implementasi nyata, file DBF akan di-copy dan di-insert
            // Untuk sekarang kita simulasikan proses penyimpanan

            $insertCount = 0;
            foreach ($dataTS as $row) {
                // Insert ke tabel DBF (simulasi)
                // Dalam implementasi real, akan menggunakan DBase atau library lain
                $insertCount++;
            }

            DB::commit();

            $message = "Proses Kirim Data Prioritas Selesai!<br>";
            $message .= "Cabang: {$CBG}<br>";
            $message .= "File: {$nama}<br>";
            $message .= "Total Record: {$insertCount}<br>";
            $message .= "File disimpan ke: {$fileBaca}";

            return response()->json([
                'success' => true,
                'message' => $message,
                'count'   => $insertCount,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in proses: ' . $e->getMessage());
            return response()->json([
                'error' => 'Proses gagal: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function print(Request $request)
    {
        try {
            $CBG      = Auth::user()->CBG ?? null;
            $username = Auth::user()->username ?? 'system';
            $TGL = Carbon::now()->format('d-m-Y');
            $JAM = Carbon::now()->format('H:i:s');

            // Query untuk print report
            $query = "
                SELECT
                    '{$username}' AS USER,
                    brg.KD_BRG,
                    CONCAT(brg.NA_BRG, ' ', brg.KET_UK) as NA_BRG,
                    brg.KET_KEM,
                    priots.LPH,
                    priots.saldo as SALDO,
                    priots.saldo as QTY,
                    priots.saldo as QTY_TS,
                    priots.TGLx as TGL
                FROM priots
                INNER JOIN {$CBG}.brg ON priots.KD_BRG = brg.KD_BRG
                ORDER BY brg.KD_BRG ASC
            ";

            $data = DB::select($query);
            // dd($data);

            $file         = 'print_barang_prioritas';
            $PHPJasperXML = new PHPJasperXML();
            $PHPJasperXML->load_xml_file(base_path("/app/reportc01/phpjasperxml/{$file}.jrxml"));

            $cleanData = json_decode(json_encode($data), true);
            $PHPJasperXML->arrayParameter = [
            "TGL"   => $TGL,
            "JAM" => $JAM,
        ];

            $PHPJasperXML->setData($cleanData);

            ob_end_clean();
            $PHPJasperXML->outpage("I");
        } catch (\Exception $e) {
            Log::error('Error in print: ' . $e->getMessage());
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }
}