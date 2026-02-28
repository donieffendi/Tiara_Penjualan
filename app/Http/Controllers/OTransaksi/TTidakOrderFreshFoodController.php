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

            Log::info('=== TTidakOrderFreshFood cari_data ===', [
                'CBG' => $CBG
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
                    orderts.ket_uk as KET_UK,
                    orderts.ket_kem as KET_KEM,
                    orderts.KLK,
                    orderts.LPH,
                    orderts.SALDO,
                    orderts.qty as QTY,
                    DATE_ADD(orderts.TGL, INTERVAL 1 DAY) as TGL
                FROM tgz.orderts
                WHERE orderts.flag = 'TO'
                AND orderts.cbg = ?
                ORDER BY orderts.KD_BRG ASC
            ";

            $data = DB::select($query, [$CBG]);

            Log::info('Query result count: ' . count($data));

            return Datatables::of(collect($data))
                ->addIndexColumn()
                ->editColumn('LPH', function ($row) {
                    return number_format($row->LPH, 2);
                })
                ->editColumn('SALDO', function ($row) {
                    return number_format($row->SALDO, 2);
                })
                // ->editColumn('QTY', function ($row) {
                //     return '<input type="number" class="form-control form-control-sm text-right edit-qty" data-rec="' . $row->rec . '" value="' . $row->QTY . '" min="0" step="0.01">';
                // })
                ->editColumn('QTY', function ($row) {
                    return $row->QTY; // kirim angka murni saja
                })
                ->editColumn('TGL', function ($row) {
                    return date('Y-m-d', strtotime($row->TGL));
                })
                ->addColumn('action', function ($row) {
                    return '<button class="btn btn-sm btn-danger btn-delete" data-rec="' . $row->rec . '"><i class="fas fa-trash"></i></button>';
                })
                
                ->rawColumns(['action']) // HAPUS QTY dari sini
                // ->rawColumns(['QTY', 'action'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('Error in cari_data: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
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

            Log::info('=== TTidakOrderFreshFood proses ===', [
                'user' => $username,
                'CBG' => $CBG
            ]);

            $periode = $request->session()->get('periode');
            if (!$periode) {
                return response()->json(['error' => 'Periode belum diset'], 400);
            }

            $action = $request->input('action', '');

            DB::beginTransaction();

            if ($action === 'save') {
                // Save data ke orderts
                $items = $request->input('items', []);

                if (empty($items)) {
                    DB::rollBack();
                    return response()->json(['error' => 'Tidak ada data untuk disimpan'], 400);
                }

                Log::info('Saving items count: ' . count($items));

                // Delete existing data
                DB::statement("DELETE FROM tgz.orderts WHERE flag = 'TO' AND cbg = ?", [$CBG]);

                // Insert new data
                foreach ($items as $item) {
                    DB::statement("
                        INSERT INTO tgz.orderts (
                            rec, SUB, KDBAR, KD_BRG, NA_BRG, ket_uk, ket_kem, KLK,
                            LPH, SALDO, TGL, qty, flag, cbg
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

                DB::commit();

                Log::info('Data saved successfully', ['count' => count($items)]);

                return response()->json([
                    'success' => true,
                    'message' => 'Data berhasil disimpan!'
                ]);
            } elseif ($action === 'refresh') {
                // Refresh = Delete all data
                DB::statement("DELETE FROM tgz.orderts WHERE flag = 'TO' AND cbg = ?", [$CBG]);

                DB::commit();

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
                    DB::rollBack();
                    return response()->json(['error' => 'Cabang tidak valid untuk proses DBF'], 400);
                }

                // Get data dari orderts
                $data = DB::select("
                    SELECT SUB, KDBAR, qty as QTY, TGL, SALDO
                    FROM tgz.orderts
                    WHERE flag = 'TO' AND cbg = ?
                    ORDER BY rec
                ", [$CBG]);

                if (empty($data)) {
                    DB::rollBack();
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

                    DB::commit();

                    Log::info('DBF process completed', ['file' => $namaFile]);

                    return response()->json([
                        'success' => true,
                        'message' => 'Data berhasil diproses ke DBF!'
                    ]);
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Error DBF process: ' . $e->getMessage());
                    return response()->json(['error' => 'Gagal memproses DBF: ' . $e->getMessage()], 500);
                }
            }

            DB::rollBack();
            return response()->json(['error' => 'Action tidak valid'], 400);
        } catch (\Exception $e) {
            DB::rollBack();
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

            Log::info('=== TTidakOrderFreshFood detail ===', [
                'user' => $username,
                'CBG' => $CBG
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
                    orderts.qty as QTY,
                    DATE_ADD(orderts.TGL, INTERVAL 1 DAY) as TGL
                FROM tgz.orderts
                INNER JOIN tgz.brg ON orderts.KD_BRG = brg.KD_BRG
                WHERE orderts.flag = 'TO'
                AND orderts.cbg = ?
                ORDER BY brg.KD_BRG ASC
            ";

            $data = DB::select($query, [$username, $CBG]);

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

            Log::info('=== TTidakOrderFreshFood lookup_barang ===', [
                'CBG' => $CBG
            ]);

            // Get semua barang fresh food untuk ditampilkan di DataTable
            // DataTable akan handle searching dan pagination di client side
            $query = "
                SELECT
                    brg.KD_BRG as kd_brg,
                    brg.NA_BRG as na_brg,
                    brg.KET_UK as ket_uk,
                    brg.KET_KEM as ket_kem,
                    brg.SATUAN as satuan,
                    brg.KLK_DEL as klk
                FROM tgz.brg
                WHERE brg.KD_BRG IS NOT NULL
                AND brg.KD_BRG != ''
                AND brg.KLK_DEL IN ('1', '2', '3')
                ORDER BY brg.KD_BRG ASC
            ";

            $barang = DB::select($query);

            Log::info('Barang fresh food count: ' . count($barang));

            return response()->json([
                'success' => true,
                'data' => $barang,
                'message' => count($barang) . ' barang tersedia'
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

    public function jasper(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            $username = Auth::user()->username ?? 'system';

            if (!$CBG) {
                Log::error('Jasper error: User tidak memiliki CBG');
                return redirect()->back()->with('error', 'User tidak memiliki akses cabang');
            }

            Log::info('=== TTidakOrderFreshFood jasper ===', [
                'user' => $username,
                'CBG' => $CBG
            ]);

            // Get data untuk print dengan JOIN ke tabel brg
            $query = "
                SELECT
                    brg.KD_BRG,
                    CONCAT(brg.NA_BRG, ' ', brg.KET_UK) as NA_BRG,
                    brg.KET_KEM,
                    orderts.LPH,
                    orderts.SALDO,
                    orderts.qty as QTY,
                    DATE_ADD(orderts.TGL, INTERVAL 1 DAY) as TGL
                FROM tgz.orderts
                INNER JOIN tgz.brg ON orderts.KD_BRG = brg.KD_BRG
                WHERE orderts.flag = 'TO'
                AND orderts.cbg = ?
                ORDER BY brg.KD_BRG ASC
            ";

            $data = DB::select($query, [$CBG]);

            if (empty($data)) {
                Log::warning('No data for Jasper report');
                return redirect()->back()->with('error', 'Tidak ada data untuk dicetak');
            }

            Log::info('Data count for Jasper: ' . count($data));

            // Convert stdClass to array for PHPJasperXML
            $data = json_decode(json_encode($data), true);

            // Prepare Jasper parameters
            $tglCetak = date('d-m-Y');
            $jam = date('H:i:s');
            $tglOrder = !empty($data) ? date('d-m-Y', strtotime($data[0]['TGL'])) : date('d-m-Y');

            $PHPJasperXML = new PHPJasperXML();
            $PHPJasperXML->load_xml_file(base_path() . '/app/reportc01/phpjasperxml/tidak_order_freshfood.jrxml');

            $PHPJasperXML->arrayParameter = [
                "JUDUL" => "Laporan Tidak Order Fresh Food",
                "CBG" => $CBG,
                "USERNAME" => $username,
                "TGL_CETAK" => $tglCetak,
                "TGL_ORDER" => $tglOrder,
                "JAM" => $jam
            ];

            $PHPJasperXML->setData($data);

            Log::info('Jasper report generated successfully');

            $PHPJasperXML->outpage("I");
        } catch (\Exception $e) {
            Log::error('Error in jasper: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Gagal mencetak data: ' . $e->getMessage());
        }
    }
}
