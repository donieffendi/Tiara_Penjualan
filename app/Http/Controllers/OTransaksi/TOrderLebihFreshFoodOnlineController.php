<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Yajra\DataTables\Facades\DataTables;
use PHPJasperXML;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

class TOrderLebihFreshFoodOnlineController extends Controller
{
    public function index(Request $request)
    {
        try {
            $judul = 'Transaksi Order Lebih Fresh Food Online';

            $CBG = Auth::user()->CBG ?? null;
            $username = Auth::user()->username ?? 'system';

            if (!$CBG) {
                return view("otransaksi_TOrderLebihFreshFoodOnline.index")->with([
                    'judul' => $judul,
                    'hasil' => [],
                    'error' => 'User tidak memiliki akses cabang (CBG). Hubungi administrator.'
                ]);
            }

            if (!$request->session()->has('periode')) {
                return view("otransaksi_TOrderLebihFreshFoodOnline.index")->with([
                    'judul' => $judul,
                    'hasil' => [],
                    'warning' => 'Periode belum diset. Silakan set periode terlebih dahulu.'
                ]);
            }

            $periode = $request->session()->get('periode');

            // Query GROUP BY NAMAFILE sesuai Delphi - untuk tampilan awal
            // Tampilkan data 30 hari terakhir agar ada data yang muncul
            $query = "
                SELECT
                    @rownum := @rownum + 1 as NO,
                    MIN(DATE_FORMAT(TGL, '%d-%m-%Y')) as TGL,
                    NAMAFILE,
                    GROUP_CONCAT(DISTINCT SUPP ORDER BY SUPP SEPARATOR ', ') as SUPLIER,
                    MIN(OUTLET) as OUTLET
                FROM ord_lebih_ts_kd3,
                (SELECT @rownum := 0) r
                /* ini nanti dihapus ya komennya di where, krn buat ngecek aja */
                /* WHERE TGL >= CURDATE() - INTERVAL 30 DAY*/
                GROUP BY NAMAFILE
                ORDER BY MIN(TGL) DESC, NAMAFILE DESC
            ";

            $hasil = DB::select($query);

            // Add AKSI column with HTML buttons
            foreach ($hasil as $row) {
                $row->AKSI = '
                    <button class="btn btn-sm btn-info btn-edit" data-file="' . htmlspecialchars($row->NAMAFILE) . '" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-primary btn-print" data-file="' . htmlspecialchars($row->NAMAFILE) . '" title="Print">
                        <i class="fas fa-print"></i>
                    </button>
                    <button class="btn btn-sm btn-success btn-send" data-file="' . htmlspecialchars($row->NAMAFILE) . '" title="Kirim">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                    <button class="btn btn-sm btn-danger btn-delete" data-file="' . htmlspecialchars($row->NAMAFILE) . '" title="Hapus">
                        <i class="fas fa-trash"></i>
                    </button>
                ';
            }

            return view("otransaksi_TOrderLebihFreshFoodOnline.index")->with([
                'judul' => $judul,
                'cbg' => $CBG,
                'periode' => $periode,
                'username' => $username,
                'hasil' => $hasil
            ]);
        } catch (\Exception $e) {
            Log::error('Error in TOrderLebihFreshFoodOnline index: ' . $e->getMessage());
            return view("otransaksi_TOrderLebihFreshFoodOnline.index")->with([
                'judul' => 'Transaksi Order Lebih Fresh Food Online',
                'hasil' => [],
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    // Tampil data GROUP BY NAMAFILE - sesuai Delphi Tampil procedure
    public function cari_data(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;

            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            Log::info('=== TOrderLebihFreshFoodOnline cari_data ===', [
                'CBG' => $CBG
            ]);

            // Query sesuai Delphi - tampilkan data 30 hari terakhir
            $query = "
                SELECT
                    NAMAFILE,
                    MIN(TGL) as TGL,
                    MIN(URUT) as KODE,
                    GROUP_CONCAT(DISTINCT SUPP ORDER BY SUPP SEPARATOR ', ') as SUPLIER,
                    MIN(OUTLET) as OUTLET
                FROM ord_lebih_ts_kd3
                WHERE TGL >= CURDATE() - INTERVAL 30 DAY
                GROUP BY NAMAFILE
                ORDER BY MIN(TGL) DESC, NAMAFILE DESC
            ";

            $data = DB::select($query);

            Log::info('Query executed successfully', ['count' => count($data)]);

            return Datatables::of(collect($data))
                ->addIndexColumn()
                ->editColumn('TGL', function ($row) {
                    return date('d-m-Y', strtotime($row->TGL));
                })
                ->addColumn('action', function ($row) {
                    return '
                        <button class="btn btn-sm btn-info btn-edit" data-file="' . $row->NAMAFILE . '" title="Edit">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-sm btn-primary btn-print" data-file="' . $row->NAMAFILE . '" title="Print">
                            <i class="fas fa-print"></i> Print
                        </button>
                        <button class="btn btn-sm btn-success btn-send" data-file="' . $row->NAMAFILE . '" title="Kirim">
                            <i class="fas fa-paper-plane"></i> Kirim
                        </button>
                        <button class="btn btn-sm btn-danger btn-delete" data-file="' . $row->NAMAFILE . '" title="Hapus">
                            <i class="fas fa-trash"></i> Hapus
                        </button>
                    ';
                })
                ->rawColumns(['action'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('Error in cari_data: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    // Get detail by NAMAFILE - untuk view detail saat double click
    public function detail(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            $namafile = $request->input('namafile');

            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            if (!$namafile) {
                return response()->json(['error' => 'NAMAFILE tidak ditemukan'], 400);
            }

            Log::info('=== TOrderLebihFreshFoodOnline detail ===', [
                'CBG' => $CBG,
                'NAMAFILE' => $namafile
            ]);

            // Query untuk detail items - SUPP adalah field di tabel ord_lebih_ts_kd3
            $query = "
                SELECT
                    o.rec,
                    o.SUB,
                    o.KDBAR,
                    o.KD_BRG,
                    o.NMBAR as NA_BRG,
                    o.ket_uk as KET_UK,
                    o.ket_kem as KET_KEM,
                    o.LPH,
                    o.SALDO as STOCK,
                    o.qty as QTY,
                    o.SUPP,
                    o.SUPP as NAMA_SUPP,
                    DATE_FORMAT(o.TGL, '%d-%m-%Y') as TGL
                FROM ord_lebih_ts_kd3 o
                WHERE o.NAMAFILE = ?
                ORDER BY o.SUPP ASC, o.KD_BRG ASC
            ";

            $data = DB::select($query, [$namafile]);

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Error in detail: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal mengambil detail: ' . $e->getMessage()], 500);
        }
    }

    // Halaman new - untuk input data baru
    // public function newForm(Request $request)
    // {
    //     try {
    //         $judul = 'Input Order Lebih Fresh Food Online';
    //         $CBG = Auth::user()->CBG ?? null;
    //         $username = Auth::user()->username ?? 'system';

    //         if (!$CBG) {
    //             return view("otransaksi_TOrderLebihFreshFoodOnline.edit")->with([
    //                 'judul' => $judul,
    //                 'error' => 'User tidak memiliki akses cabang (CBG). Hubungi administrator.'
    //             ]);
    //         }

    //         return view("otransaksi_TOrderLebihFreshFoodOnline.edit")->with([
    //             'judul' => $judul,
    //             'cbg' => $CBG,
    //             'username' => $username,
    //             'status' => 'simpan',
    //             'namafile' => ''
    //         ]);
    //     } catch (\Exception $e) {
    //         Log::error('Error in newForm: ' . $e->getMessage());
    //         return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
    //     }
    // }

    public function newForm(Request $request)
    {
        try {

            $judul = 'Input Order Lebih Fresh Food Online';
            $CBG = Auth::user()->CBG ?? null;
            $username = Auth::user()->username ?? 'system';

            if (!$CBG) {
                return view("otransaksi_TOrderLebihFreshFoodOnline.create", [
                    'judul' => $judul,
                    'error' => 'User tidak memiliki akses cabang (CBG). Hubungi administrator.'
                ]);
            }

            return view("otransaksi_TOrderLebihFreshFoodOnline.create", [
                'judul' => $judul,
                'cbg' => $CBG,
                'username' => $username,
                'status' => 'simpan',
                'namafile' => '',
                'header' => null,
                'hasil' => []
            ]);

        } catch (\Exception $e) {

            Log::error('Error newForm: '.$e->getMessage());

            return redirect()->back()->with('error','Terjadi kesalahan');
        }
    }

    // Halaman edit - untuk edit data existing
    public function editForm(Request $request)
    {
        try {
            $judul = 'Edit Order Lebih Fresh Food Online';
            $CBG = Auth::user()->CBG ?? null;
            $username = Auth::user()->username ?? 'system';

            // Ambil identifier dari parameter (format: KODES_KODE)
            $identifier = $request->input('namafile');

            if (!$CBG) {
                return view("otransaksi_TOrderLebihFreshFoodOnline.edit")->with([
                    'judul' => $judul,
                    'hasil' => [],
                    'error' => 'User tidak memiliki akses cabang (CBG). Hubungi administrator.'
                ]);
            }

            if (!$identifier) {
                return redirect()->back()->with('error', 'Identifier tidak ditemukan');
            }

            // identifier adalah NAMAFILE langsung
            // Get data detail untuk KoolReport
            $query = "
                SELECT
                    @rownum := @rownum + 1 as NO,
                    o.rec,
                    o.SUB as SUB_ITEM,
                    o.KD_BRG,
                    o.NMBAR as NAMA_BARANG,
                    CONCAT(COALESCE(o.ket_uk, ''), ' ', COALESCE(o.ket_kem, '')) as KEMASAN,
                    o.qty as QTY,
                    o.SUPP,
                    o.SUPP as NAMA_SUPP,
                    DATE_FORMAT(o.TGL, '%d-%m-%Y') as TGL_KIRIM,
                    o.LPH,
                    o.SALDO
                FROM ord_lebih_ts_kd3 o,
                (SELECT @rownum := 0) r
                WHERE o.NAMAFILE = ?
                ORDER BY o.SUPP, o.KD_BRG
            ";

            $hasil = DB::select($query, [$identifier]);

            if (empty($hasil)) {
                return redirect()->back()->with('error', 'Data tidak ditemukan');
            }

            // Get header info dari record pertama
            $firstRecord = $hasil[0] ?? null;
            $header = (object)[
                'TGL' => $firstRecord->TGL_KIRIM ?? date('d-m-Y'),
                'NAMAFILE' => $identifier,
                'SUPP' => $firstRecord->SUPP ?? '-',
                'NAMA_SUPP' => $firstRecord->NAMA_SUPP ?? '-',
                'JML_ITEM' => count($hasil)
            ];

            return view("otransaksi_TOrderLebihFreshFoodOnline.edit")->with([
                'judul' => $judul,
                'cbg' => $CBG,
                'username' => $username,
                'status' => 'edit',
                'identifier' => $identifier,
                'header' => $header,
                'hasil' => $hasil
            ]);
        } catch (\Exception $e) {
            Log::error('Error in editForm: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // Generate NO.BUKTI otomatis dari ord_online_ts
    private function generateNoBukti($CBG)
    {
        // Get ORD_FF extension
        $toko = DB::selectOne("SELECT ORD_FF FROM toko WHERE KODE = ?", [$CBG]);

        if (!$toko) {
            throw new \Exception('Data toko tidak ditemukan');
        }

        $ord_ff = $toko->ORD_FF ?? 'OL';

        // Get nomor urut dari ord_online_ts
        $noBukti = DB::selectOne("
            SELECT
                CONCAT(DATE_FORMAT(CURDATE(),'%d'), URUT, '.', ?) as NF,
                URUT
            FROM ord_online_ts
            WHERE CEK = 0
            AND NOTES = 'LEBIH MANUAL'
            ORDER BY URUT
            LIMIT 1
        ", [$ord_ff]);

        if (!$noBukti) {
            throw new \Exception('Create NO.BUKTI bermasalah! Tidak ada nomor urut tersedia');
        }

        // Update CEK dan NAMA_FILE
        DB::statement("
            UPDATE ord_online_ts
            SET CEK = 2, NAMA_FILE = ?
            WHERE CEK = 0
            AND NOTES = 'LEBIH MANUAL'
            AND URUT = ?
        ", [$noBukti->NF, $noBukti->URUT]);

        return [
            'namafile' => $noBukti->NF,
            'urut' => $noBukti->URUT
        ];
    }

    // Save form - untuk simpan data dari halaman new/edit
    public function saveForm(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            $username = Auth::user()->username ?? 'system';

            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            $status = $request->input('status');
            $namafile = $request->input('namafile') ?? '';
            // $tgl = $request->input('tgl');
            $items = $request->input('items', []);

        //    dd([$status, $namafile, $items]);

            if (empty($items)) {
                return response()->json(['error' => 'Tidak ada item untuk disimpan'], 400);
            }

            DB::beginTransaction();

            $urut = '';

            // Generate NO.BUKTI jika status simpan (baru)
            if ($status === 'simpan') {
                $noBuktiData = $this->generateNoBukti($CBG);
                $namafile = $noBuktiData['namafile'];
                $urut = $noBuktiData['urut'];
            } else {
                // Get URUT dari data existing
                $existing = DB::selectOne("
                    SELECT URUT
                    FROM ord_lebih_ts_kd3
                    WHERE NAMAFILE = ?
                    LIMIT 1
                ", [$namafile]);

                $urut = $existing->URUT ?? '';
            }

            // Get existing data untuk update/delete
            $existingData = DB::select("
                SELECT NO_ID
                FROM ord_lebih_ts_kd3
                WHERE NAMAFILE = ?
            ", [$namafile]);

            $existingIds = array_column($existingData, 'NO_ID');
            $itemIds = array_column(array_filter($items, function ($item) {
                return isset($item['NO_ID']) && $item['NO_ID'] > 0;
            }), 'NO_ID');

            // Delete items yang tidak ada di request
            $idsToDelete = array_diff($existingIds, $itemIds);
            if (!empty($idsToDelete)) {
                DB::statement("
                    DELETE FROM ord_lebih_ts_kd3
                    WHERE NO_ID IN (" . implode(',', $idsToDelete) . ")
                ");
            }

            // Update atau Insert items
            foreach ($items as $index => $item) {
                if (empty($item['KD_BRG'])) continue;

                $rec = $index + 1;

                if (isset($item['NO_ID']) && $item['NO_ID'] > 0) {
                    // UPDATE
                    DB::statement("
                        UPDATE ord_lebih_ts_kd3 SET
                            REC = ?,
                            SUB = ?,
                            KD_BRG = ?,
                            NMBAR = ?,
                            KET_UK = ?,
                            KET_KEM = ?,
                            SUPP = ?,
                            PPN = ?,
                            QTY = ?,
                            URUT = ?,
                            TGL = ?,
                            OUTLET = ?,
                            NAMAFILE = ?,
                            LPH = ?,
                            STOKR = ?,
                            JAM = TIME(NOW())
                        WHERE NO_ID = ?
                    ", [
                        $rec,
                        $item['SUB'] ?? '',
                        $item['KD_BRG'],
                        $item['NA_BRG'] ?? $item['NMBAR'] ?? '',
                        $item['KET_UK'] ?? '',
                        $item['KET_KEM'] ?? '',
                        $item['SUPP'] ?? '',
                        $item['PPN'] ?? 0,
                        $item['QTY'] ?? 0,
                        $urut,
                        date('d/m/Y'),
                        $CBG,
                        $namafile,
                        $item['LPH'] ?? 0,
                        $item['STOK'] ?? 0,
                        $item['NO_ID']
                    ]);
                } else {
                    // INSERT
                    DB::statement("
                        INSERT INTO ord_lebih_ts_kd3 (
                            REC, SUB, KD_BRG, NMBAR, KET_UK, KET_KEM,
                            SUPP, PPN, QTY, URUT, TGL, JAM, OUTLET, NAMAFILE, LPH, STOKR
                        ) VALUES (
                            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, TIME(NOW()), ?, ?, ?, ?
                        )
                    ", [
                        $rec,
                        $item['SUB'] ?? '',
                        $item['KD_BRG'],
                        $item['NA_BRG'] ?? $item['NMBAR'] ?? '',
                        $item['KET_UK'] ?? '',
                        $item['KET_KEM'] ?? '',
                        $item['SUPP'] ?? '',
                        $item['PPN'] ?? 0,
                        $item['QTY'] ?? 0,
                        $urut,
                        date('d/m/Y'),
                        $CBG,
                        $namafile,
                        $item['LPH'] ?? 0,
                        $item['STOK'] ?? 0
                    ]);
                }
            }

            $result = DB::SELECT("UPDATE $CBG.ord_lebih_ts_kd3 A, $CBG.sup B, $CBG.brg C 
                                SET 
                                    A.SUB=C.SUB, A.KDBAR=C.KDBAR, A.NAMA=B.NAMAS, A.ALMT_K=B.AL_NPWP, A.KOTA=B.KOTA, A.GOLONGAN=B.GOLONGAN 
                                WHERE A.KD_BRG=C.KD_BRG 
                                    AND A.SUPP=B.KODES 
                                    AND A.NAMAFILE='$namafile'");

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil disimpan!',
                'namafile' => $namafile
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in saveForm: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return response()->json([
                'error' => 'Gagal menyimpan data: ' . $e->getMessage()
            ], 500);
        }
    }

    // Get barang by KD_BRG - untuk lookup barang saat input
    // public function getBarang(Request $request)
    // {
    //     try {
    //         $CBG = Auth::user()->CBG ?? null;
    //         $kd_brg = $request->input('kd_brg');

    //         if (!$CBG) {
    //             return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
    //         }

    //         if (!$kd_brg) {
    //             return response()->json(['error' => 'Kode barang tidak boleh kosong'], 400);
    //         }

    //         // Query untuk get data barang
    //         $barang = DB::selectOne("
    //             SELECT
    //                 b.KD_BRG,
    //                 b.NA_BRG,
    //                 b.KET_UK,
    //                 b.KET_KEM,
    //                 b.SUB,
    //                 b.KDBAR as KDBAR,
    //                 b.SUPP,
    //                 bd.LPH,
    //                 bd.AK00 as STOK,
    //                 CONCAT(b.NA_BRG, ' ', b.KET_UK) as NAMA_LENGKAP
    //             FROM brg b
    //             LEFT JOIN brgdt bd ON b.KD_BRG = bd.KD_BRG AND bd.CBG = ?
    //             WHERE b.KD_BRG = ?
    //             AND LEFT(b.NA_BRG, 1) = '3'
    //         ", [$CBG, $kd_brg]);

    //         if (!$barang) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'SubItem Tidak Ditemukan.'
    //             ], 404);
    //         }

    //         return response()->json([
    //             'success' => true,
    //             'data' => $barang
    //         ]);
    //     } catch (\Exception $e) {
    //         Log::error('Error in getBarang: ' . $e->getMessage());
    //         return response()->json([
    //             'error' => 'Gagal mengambil data barang: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function browseBarang(Request $request)
    {
        try {

            $cbg = Auth::user()->CBG;

            $data = DB::select("
                SELECT 
                    kd_brg,
                    na_brg,
                    ket_kem,
                    barcode,
                    retur
                FROM {$cbg}.brg
                WHERE TD_OD = ''
                AND LPH > 0.10
                AND LEFT(NA_BRG,1) = '3'
                ORDER BY KD_BRG ASC
            ");

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ],500);

        }
    }

    public function getBarang(Request $request)
    {
        try {

            $cbg = Auth::user()->CBG ?? null;
            $kd_brg = trim($request->kd_brg);

            if (!$cbg) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak memiliki akses cabang'
                ]);
            }

            if (!$kd_brg) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kode barang kosong'
                ]);
            }

            $sql = "
                SELECT 
                    A.KD_BRG,
                    A.KET_UK,
                    A.KET_KEM,
                    A.NA_BRG,
                    B.LPH,
                    B.AK00 AS TOKO,
                    B.GAK00 AS GUDANG,
                    A.SUPP,
                    A.PPN,
                    CONCAT(A.NA_BRG,' ',A.KET_UK,' ') AS XX
                FROM {$cbg}.brg A
                JOIN {$cbg}.brgdt B 
                    ON A.KD_BRG = B.KD_BRG
                WHERE 
                    B.YER = YEAR(NOW())
                    AND LEFT(A.NA_BRG,1) = '3'
                    AND A.KD_BRG = ?
                LIMIT 1
            ";

            $barang = DB::selectOne($sql, [$kd_brg]);

            if (!$barang) {
                return response()->json([
                    'success' => false,
                    'message' => 'SubItem Tidak Ditemukan.'
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => $barang
            ]);

        } catch (\Exception $e) {

            Log::error('Error getBarang : '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server'
            ],500);
        }
    }

    // Proses untuk berbagai action
    public function proses(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;

            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            $action = $request->input('action', '');

            Log::info('TOrderLebihFreshFoodOnline proses', [
                'CBG' => $CBG,
                'action' => $action
            ]);

            DB::beginTransaction();

            switch ($action) {
                case 'delete':
                    return $this->deleteByNamafile($request, $CBG);

                case 'export_dbf':
                    return $this->exportDbf($request, $CBG);

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

    // Delete by NAMAFILE - sesuai KeyUp Delete di Delphi
    private function deleteByNamafile($request, $CBG)
    {
        $namafile = $request->input('namafile');

        if (!$namafile) {
            DB::rollBack();
            return response()->json(['error' => 'NAMAFILE tidak ditemukan'], 400);
        }

        Log::info('Deleting order by NAMAFILE', [
            'NAMAFILE' => $namafile,
            'CBG' => $CBG
        ]);

        // DELETE FROM ord_lebih_ts_kd3 where NAMAFILE = :BKT (sesuai Delphi)
        DB::statement("
            DELETE FROM ord_lebih_ts_kd3
            WHERE NAMAFILE = ?
        ", [$namafile]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil dihapus!'
        ]);
    }

    // Export DBF - sesuai dxBarButton2Click dan exportDbf di Delphi
    private function exportDbf($request, $CBG)
    {
        $namafile = $request->input('namafile');

        if (!$namafile) {
            DB::rollBack();
            return response()->json(['error' => 'NAMAFILE tidak ditemukan'], 400);
        }

        Log::info('Export DBF started', [
            'NAMAFILE' => $namafile,
            'CBG' => $CBG
        ]);

        // Get data untuk export
        $data = DB::select("
            SELECT *
            FROM ord_lebih_ts_kd3
            WHERE NAMAFILE = ?
            ORDER BY SUPP, KD_BRG
        ", [$namafile]);

        if (empty($data)) {
            DB::rollBack();
            return response()->json(['error' => 'Tidak ada data untuk di-export'], 404);
        }

        // Kirim ke API eksternal 
        // urlx := 'http://10.10.30.132:8080/export-dbf-app/public/api/export-ord-lebih';
        try {
            $url = 'http://10.10.30.132:8080/export-dbf-app/public/api/export-ord-lebih';

            $response = Http::timeout(30)->post($url, [
                'bkt' => $namafile,
                'cbg' => $CBG,
                'data' => $data
            ]);

            Log::info('API Response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if ($response->status() == 200) {
                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Data berhasil dikirim!'
                ]);
            } else {
                DB::rollBack();

                return response()->json([
                    'error' => 'Gagal mengirim data. Status: ' . $response->status()
                ], 500);
            }
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error sending to API', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Gagal mengirim data: ' . $e->getMessage()
            ], 500);
        }
    }

    // Print by NAMAFILE - sesuai ButtonPrintClick di Delphi
    public function jasperPrint(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            $username = Auth::user()->username ?? 'system';
            $namafile = $request->input('namafile');

            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            if (!$namafile) {
                return response()->json(['error' => 'NAMAFILE tidak ditemukan'], 400);
            }

            Log::info('=== TOrderLebihFreshFoodOnline jasperPrint ===', [
                'CBG' => $CBG,
                'NAMAFILE' => $namafile
            ]);

            // Query PERSIS seperti Delphi ButtonPrintClick:
            // SELECT * FROM ord_lebih_ts_kd3 WHERE NAMAFILE=:XD ORDER BY SUPP, KD_BRG
            $data = DB::select("
                SELECT
                    a.rec,
                    a.SUB,
                    a.KDBAR,
                    a.KD_BRG,
                    a.NMBAR as NA_BRG,
                    a.ket_uk as KET_UK,
                    a.ket_kem as KET_KEM,
                    a.LPH,
                    a.SALDO as STOCK,
                    a.STOKR,
                    a.qty as QTY,
                    a.SUPP,
                    s.NAMAS AS NAMA_SUPP,
                    a.NAMAFILE,
                    a.KET,
                    DATE_FORMAT(a.TGL, '%d-%m-%Y') as TGL_ORDER,
                    DATE_FORMAT(NOW(), '%H:%i:%s') as JAM
                FROM ord_lebih_ts_kd3 a
                LEFT JOIN sup s ON a.SUPP = s.KODES
                WHERE a.NAMAFILE = ?
                ORDER BY a.SUPP, a.KD_BRG
            ", [$namafile]);

            if (empty($data)) {
                return response()->json(['error' => 'Tidak ada data untuk dicetak'], 404);
            }

            Log::info('Data found for print', ['count' => count($data)]);

            $tglOrder = !empty($data) ? $data[0]->TGL_ORDER : date('d-m-Y');
            $jam = !empty($data) ? $data[0]->JAM : date('H:i:s');

            // Prepare data untuk Jasper
            $reportData = [];
            $no = 1;

            foreach ($data as $row) {
                $reportData[] = [
                    'NO' => $no++,
                    'SUB' => $row->SUB ?? '',
                    'KDBAR' => $row->KDBAR ?? '',
                    'KD_BRG' => $row->KD_BRG,
                    'NA_BRG' => $row->NA_BRG,
                    'KET_UK' => $row->KET_UK ?? '',
                    'KET_KEM' => $row->KET_KEM ?? '',
                    'LPH' => (float)($row->LPH ?? 0),
                    'STOCK' => (float)($row->STOCK ?? 0),
                    'STOKR' => (float)($row->STOKR ?? 0),
                    'QTY' => (float)($row->QTY ?? 0),
                    'SUPP' => $row->SUPP ?? '',
                    'KET' => $row->KET ?? '',
                    'NAMA_SUPP' => $row->NAMA_SUPP ?? 'SUPPLIER',
                    'NAMAFILE' => $row->NAMAFILE ?? ''
                ];
            }

            // Generate Jasper Report
            $file = 'order_lebih_freshfood(1)';
            $PHPJasperXML = new PHPJasperXML();
            $PHPJasperXML->load_xml_file(base_path("/app/reportc01/phpjasperxml/{$file}.jrxml"));

            // Convert to plain array
            $cleanData = json_decode(json_encode($reportData), true);

            // Set parameters
            $PHPJasperXML->arrayParameter = [
                "JUDUL" => "LAPORAN ORDER LEBIH TS KODE 3 - ONLINE",
                "CBG" => $CBG,
                "USERNAME" => $username,
                "TGL_CETAK" => date('d-m-Y'),
                "NAMAFILE" => $namafile,
                "TGL_ORDER" => $tglOrder,
                "JAM" => $jam
            ];

            $PHPJasperXML->setData($cleanData);

            // Clear output buffer
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            Log::info('=== Generating PDF with PHPJasperXML ===');

            // Output PDF inline
            $PHPJasperXML->outpage("I");
            exit;
        } catch (\Exception $e) {
            Log::error('=== Error in jasperPrint ===', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return response()->json([
                'error' => 'Gagal mencetak: ' . $e->getMessage()
            ], 500);
        }
    }
}
