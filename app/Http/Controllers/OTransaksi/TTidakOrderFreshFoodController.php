<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class TTidakOrderFreshFoodController extends Controller
{
    public function index(Request $request)
    {
        try {
            $judul = 'Transaksi Tidak Order Fresh Food';

            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                return view("otransaksi_TTidakOrderFreshFood.index")->with([
                    'judul' => $judul,
                    'error' => 'User tidak memiliki akses cabang (CBG). Hubungi administrator.'
                ]);
            }

            if (!$request->session()->has('periode')) {
                return view("otransaksi_TTidakOrderFreshFood.index")->with([
                    'judul' => $judul,
                    'warning' => 'Periode belum diset. Silakan set periode terlebih dahulu.'
                ]);
            }

            $periode = $request->session()->get('periode');

            return view("otransaksi_TTidakOrderFreshFood.index")->with([
                'judul' => $judul,
                'cbg' => $CBG,
                'periode' => $periode
            ]);
        } catch (\Exception $e) {
            Log::error('Error in TTidakOrderFreshFood index: ' . $e->getMessage());
            return view("otransaksi_TTidakOrderFreshFood.index")->with([
                'judul' => 'Transaksi Tidak Order Fresh Food',
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    public function cari_data(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            $connection = strtolower($CBG);
            Log::info('=== TTidakOrderFreshFood cari_data ===', [
                'CBG' => $CBG,
                'connection' => $connection
            ]);

            $periode = $request->session()->get('periode');
            if (!$periode) {
                return response()->json(['error' => 'Periode belum diset'], 400);
            }

            // Query data dari orderts (data yang sudah tersimpan sebelumnya)
            $query = "
                SELECT 
                    orderts.rec,
                    orderts.SUB,
                    orderts.KDBAR,
                    orderts.KD_BRG,
                    orderts.NA_BRG,
                    orderts.KET_UK,
                    orderts.KET_KEM,
                    orderts.KLK,
                    orderts.LPH,
                    orderts.SALDO,
                    orderts.QTY,
                    DATE_ADD(orderts.TGL, INTERVAL 1 DAY) as TGL
                FROM orderts 
                WHERE orderts.flag = 'TO' 
                AND orderts.cbg = ?
                ORDER BY orderts.kd_brg ASC
            ";

            $data = DB::connection($connection)->select($query, [$CBG]);

            Log::info('Query result count: ' . count($data));

            return Datatables::of(collect($data))
                ->addIndexColumn()
                ->editColumn('LPH', function ($row) {
                    return number_format($row->LPH, 2);
                })
                ->editColumn('SALDO', function ($row) {
                    return number_format($row->SALDO, 2);
                })
                ->editColumn('QTY', function ($row) {
                    return '<input type="number" class="form-control form-control-sm text-right edit-qty" data-rec="' . $row->rec . '" value="' . $row->QTY . '" min="0" step="0.01">';
                })
                ->editColumn('TGL', function ($row) {
                    return date('Y-m-d', strtotime($row->TGL));
                })
                ->addColumn('action', function ($row) {
                    return '<button class="btn btn-sm btn-danger btn-delete" data-rec="' . $row->rec . '"><i class="fas fa-trash"></i></button>';
                })
                ->rawColumns(['QTY', 'action'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('Error in cari_data: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function proses(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            $username = Auth::user()->username ?? 'system';

            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            $connection = strtolower($CBG);
            Log::info('=== TTidakOrderFreshFood proses ===', [
                'user' => $username,
                'CBG' => $CBG,
                'connection' => $connection
            ]);

            $periode = $request->session()->get('periode');
            if (!$periode) {
                return response()->json(['error' => 'Periode belum diset'], 400);
            }

            $action = $request->input('action', '');

            DB::connection($connection)->beginTransaction();

            if ($action === 'save') {
                // Save data ke orderts
                $items = $request->input('items', []);

                if (empty($items)) {
                    DB::connection($connection)->rollBack();
                    return response()->json(['error' => 'Tidak ada data untuk disimpan'], 400);
                }

                Log::info('Saving items count: ' . count($items));

                // Delete existing data
                DB::connection($connection)->statement("DELETE FROM orderts WHERE flag = 'TO' AND cbg = ?", [$CBG]);

                // Insert new data
                foreach ($items as $item) {
                    DB::connection($connection)->statement("
                        INSERT INTO orderts (
                            rec, SUB, KDBAR, KD_BRG, NA_BRG, KET_UK, KET_KEM, KLK, 
                            LPH, SALDO, TGL, QTY, FLAG, CBG
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'TO', ?)
                    ", [
                        $item['rec'],
                        trim($item['SUB']),
                        trim($item['KDBAR']),
                        trim($item['KD_BRG']),
                        trim($item['NA_BRG']),
                        trim($item['KET_UK'] ?? ''),
                        trim($item['KET_KEM'] ?? ''),
                        trim($item['KLK'] ?? ''),
                        $item['LPH'] ?? 0,
                        $item['SALDO'] ?? 0,
                        $item['TGL'],
                        $item['QTY'] ?? 0,
                        $CBG
                    ]);
                }

                DB::connection($connection)->commit();

                Log::info('Data saved successfully', ['count' => count($items)]);

                return response()->json([
                    'success' => true,
                    'message' => 'Data berhasil disimpan!'
                ]);
            } elseif ($action === 'refresh') {
                // Refresh = Delete all data
                DB::connection($connection)->statement("DELETE FROM orderts WHERE flag = 'TO' AND cbg = ?", [$CBG]);

                DB::connection($connection)->commit();

                Log::info('Data refreshed (deleted)');

                return response()->json([
                    'success' => true,
                    'message' => 'Data berhasil dihapus!'
                ]);
            } elseif ($action === 'proses_dbf') {
                // Proses untuk export ke DBF (sesuai btnProsesClick di Delphi)
                $namaFile = '';

                // Tentukan nama file berdasarkan CBG
                if ($CBG == 'TGZ') {
                    $namaFile = 'TO_GZ.DBF';
                } elseif ($CBG == 'TMM') {
                    $namaFile = 'TO_TM.DBF';
                } elseif ($CBG == 'SOP') {
                    $namaFile = 'TO_KG.DBF';
                } else {
                    DB::connection($connection)->rollBack();
                    return response()->json(['error' => 'Cabang tidak valid untuk proses DBF'], 400);
                }

                // Get data dari orderts
                $data = DB::connection($connection)->select("
                    SELECT SUB, KDBAR, QTY, TGL, SALDO 
                    FROM orderts 
                    WHERE flag = 'TO' AND cbg = ?
                    ORDER BY rec
                ", [$CBG]);

                if (empty($data)) {
                    DB::connection($connection)->rollBack();
                    return response()->json(['error' => 'Tidak ada data untuk diproses'], 400);
                }

                Log::info('Processing DBF data count: ' . count($data));

                // Lokasi folder DBF
                $dirLokal = 'D:\\DBF\\TO3\\';
                $fileBaca = 'A:\\dbf\\kode 3 ts\\baca\\' . $namaFile;
                $fileAwal = 'A:\\dbf\\kode 3 ts\\TGZ\\TO_GZ.DBF';

                // Pastikan folder ada
                if (!is_dir($dirLokal)) {
                    mkdir($dirLokal, 0755, true);
                }

                // Hapus file lama jika ada
                if (file_exists($fileBaca)) {
                    unlink($fileBaca);
                }
                if (file_exists($dirLokal . $namaFile)) {
                    unlink($dirLokal . $namaFile);
                }

                // Copy template DBF
                if (!file_exists($fileAwal)) {
                    DB::rollBack();
                    return response()->json(['error' => 'File template DBF tidak ditemukan'], 404);
                }

                copy($fileAwal, $dirLokal . $namaFile);

                // Insert data ke DBF menggunakan PDO
                try {
                    $tableName = str_replace('.DBF', '', $namaFile);
                    $connectionString = "odbc:Driver={Microsoft Visual FoxPro Driver};SourceType=DBF;SourceDB=" . $dirLokal . ";Exclusive=No;";
                    $pdo = new \PDO($connectionString);

                    foreach ($data as $row) {
                        $stmt = $pdo->prepare("
                            INSERT INTO {$tableName} (SUB, KDBAR, QTY, TGL, SALDO) 
                            VALUES (?, ?, ?, ?, ?)
                        ");
                        $stmt->execute([
                            $row->SUB,
                            $row->KDBAR,
                            $row->QTY,
                            date('Y-m-d', strtotime($row->TGL)),
                            $row->SALDO
                        ]);
                    }

                    // Copy ke server
                    copy($dirLokal . $namaFile, $fileBaca);

                    DB::connection($connection)->commit();

                    Log::info('DBF process completed', ['file' => $namaFile]);

                    return response()->json([
                        'success' => true,
                        'message' => 'Data berhasil diproses ke DBF!'
                    ]);
                } catch (\Exception $e) {
                    DB::connection($connection)->rollBack();
                    Log::error('Error DBF process: ' . $e->getMessage());
                    return response()->json(['error' => 'Gagal memproses DBF: ' . $e->getMessage()], 500);
                }
            }

            DB::connection($connection)->rollBack();
            return response()->json(['error' => 'Action tidak valid'], 400);
        } catch (\Exception $e) {
            if (isset($connection)) {
                DB::connection($connection)->rollBack();
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
            $username = Auth::user()->username ?? 'system';

            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            $connection = strtolower($CBG);
            Log::info('=== TTidakOrderFreshFood detail ===', [
                'user' => $username,
                'CBG' => $CBG,
                'connection' => $connection
            ]);

            // Get data untuk print (sesuai Button3Click di Delphi)
            $query = "
                SELECT 
                    ? AS USER,
                    brg.KD_BRG,
                    CONCAT(brg.NA_BRG, ' ', brg.KET_UK) as NA_BRG,
                    brg.KET_KEM,
                    orderts.LPH,
                    orderts.SALDO,
                    orderts.QTY,
                    DATE_ADD(orderts.TGL, INTERVAL 1 DAY) as TGL
                FROM orderts
                INNER JOIN brg ON orderts.KD_BRG = brg.KD_BRG
                WHERE orderts.flag = 'TO' 
                AND orderts.cbg = ?
                ORDER BY brg.kd_brg ASC
            ";

            $data = DB::connection($connection)->select($query, [$username, $CBG]);

            if (empty($data)) {
                return response()->json(['error' => 'Tidak ada data untuk dicetak'], 404);
            }

            Log::info('Detail data count: ' . count($data));

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Error in detail: ' . $e->getMessage());
            return response()->json([
                'error' => 'Gagal mengambil detail: ' . $e->getMessage()
            ], 500);
        }
    }

    public function lookup_barang(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            $connection = strtolower($CBG);
            Log::info('=== TTidakOrderFreshFood lookup_barang ===', [
                'CBG' => $CBG,
                'connection' => $connection
            ]);

            // Get daftar barang fresh food dengan dynamic connection
            // Fresh food biasanya kategori tertentu, sesuaikan dengan kebutuhan
            $barang = DB::connection($connection)->select("
                SELECT 
                    brg.kd_brg,
                    brg.na_brg,
                    brg.ket_uk,
                    brg.ket_kem,
                    brg.satuan,
                    brg.klk
                FROM brg
                WHERE brg.kd_brg IS NOT NULL
                AND brg.kd_brg != ''
                AND brg.klk IN ('1', '2', '3')
                ORDER BY brg.kd_brg ASC
                LIMIT 1000
            ");

            Log::info('Barang fresh food count: ' . count($barang));

            return response()->json([
                'success' => true,
                'data' => $barang
            ]);
        } catch (\Exception $e) {
            Log::error('Error in lookup_barang: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return response()->json([
                'error' => 'Gagal memuat barang: ' . $e->getMessage()
            ], 500);
        }
    }
}
