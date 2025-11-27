<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class TPenangananLBTATController extends Controller
{
    // =============================================
    // TRANSAKSI PENANGANAN LBTAT
    // =============================================

    public function index(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            $route = $request->route()->getName();

            if (!$CBG) {
                return view("otransaksi_penanganan_lbtat.index")->with([
                    'judul' => $this->getJudul($route),
                    'route_name' => $route,
                    'error' => 'User tidak memiliki akses cabang (CBG). Hubungi administrator.'
                ]);
            }

            // Check if this is edit mode
            $noBukti = $request->get('no_bukti');
            $status = $request->get('status', 'list');

            if ($status == 'edit' && $noBukti) {
                return $this->edit($noBukti, $route);
            }

            return view("otransaksi_penanganan_lbtat.index")->with([
                'judul' => $this->getJudul($route),
                'route_name' => $route,
                'cbg' => $CBG
            ]);
        } catch (\Exception $e) {
            Log::error('Error in TPenangananLBTAT index: ' . $e->getMessage());
            return view("otransaksi_penanganan_lbtat.index")->with([
                'judul' => 'Error',
                'route_name' => $request->route()->getName(),
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    private function getJudul($route)
    {
        if (strpos($route, 'tpenangananlbtat') !== false) {
            return 'Transaksi Penanganan LB/LBTAT';
        } elseif (strpos($route, 'tprosesstockopname') !== false) {
            return 'Transaksi Proses Stock Opname';
        }
        return 'Transaksi';
    }

    private function getFlag($route)
    {
        if (strpos($route, 'tpenangananlbtat') !== false) {
            return 'AK'; // LBTAT
        } elseif (strpos($route, 'tprosesstockopname') !== false) {
            return 'AO'; // Stock Opname
        }
        return 'AK';
    }

    public function getTPenangananLBTATData(Request $request)
    {
        return $this->getData($request, 'tpenangananlbtat');
    }

    public function getTProsesStockOpnameData(Request $request)
    {
        return $this->getData($request, 'tprosesstockopname');
    }

    private function getData(Request $request, $route)
    {
        try {
            $CBG = Auth::user()->CBG;
            $periode = date('mY');
            $flag = $this->getFlag($route);

            // Cek apakah tabel stockb ada
            try {
                $query = DB::select("
                    SELECT
                        no_bukti as NO_BUKTI,
                        DATE_FORMAT(tgl, '%d/%m/%Y') as tgl,
                        total_qty,
                        notes as NOTES,
                        type as TYPE,
                        CONCAT(LEFT(nolap,2), RIGHT(nolap,5)) as bukti,
                        tgl as tgl_raw
                    FROM {$CBG}.stockb
                    WHERE per = ?
                        AND flag = ?
                    ORDER BY no_bukti DESC
                ", [$periode, $flag]);
            } catch (\Illuminate\Database\QueryException $e) {
                Log::error('Error querying stockb table: ' . $e->getMessage());
                // Jika tabel tidak ada, return data kosong
                $query = [];
            }

            return Datatables::of(collect($query))
                ->addIndexColumn()
                ->addColumn('action', function ($row) use ($route) {
                    $btn = '<button type="button" class="btn btn-xs btn-info btn-edit" data-bukti="' . $row->NO_BUKTI . '" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('Error in getData: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    private function edit($noBukti, $route)
    {
        try {
            $CBG = Auth::user()->CBG;

            // Get header data
            try {
                $header = DB::select("
                    SELECT * FROM {$CBG}.stockb
                    WHERE no_bukti = ?
                ", [$noBukti]);
            } catch (\Illuminate\Database\QueryException $e) {
                Log::error('Error querying stockb: ' . $e->getMessage());
                return redirect()->route($route)->with('error', 'Tabel stockb tidak ditemukan atau belum ada data');
            }

            if (empty($header)) {
                return redirect()->route($route)->with('error', 'Data tidak ditemukan');
            }

            $header = $header[0];

            // Get detail data
            try {
                $detail = DB::select("
                    SELECT
                        a.*,
                        b.saldo as stok_system,
                        b.hj,
                        b.kdlaku,
                        CASE
                            WHEN a.qty IS NULL OR a.qty = 0 THEN 0
                            ELSE 1
                        END as cek_status
                    FROM {$CBG}.stockbd a
                    LEFT JOIN {$CBG}.brgdt b ON a.kd_brg = b.kd_brg
                        AND b.cbg = ?
                        AND b.yer = YEAR(NOW())
                    WHERE a.no_bukti = ?
                    ORDER BY a.rec
                ", [$CBG, $noBukti]);
            } catch (\Illuminate\Database\QueryException $e) {
                Log::error('Error querying stockbd: ' . $e->getMessage());
                $detail = [];
            }

            return view("otransaksi_penanganan_lbtat.edit")->with([
                'judul' => $this->getJudul($route),
                'route_name' => $route,
                'header' => $header,
                'detail' => $detail,
                'cbg' => $CBG
            ]);
        } catch (\Exception $e) {
            Log::error('Error in edit: ' . $e->getMessage());
            return redirect()->route($route)->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function proses(Request $request)
    {
        try {
            DB::beginTransaction();

            $CBG = Auth::user()->CBG;
            $USERNAME = Auth::user()->username;
            $noBukti = $request->no_bukti;
            $tgl = $request->tgl;
            $notes = $request->notes;
            $details = $request->details ?? [];
            $action = $request->action; // proses, hapus_positif, hapus_nol, hapus_negatif

            if (!$noBukti) {
                return response()->json([
                    'success' => false,
                    'message' => 'No Bukti harus diisi'
                ], 400);
            }

            // Check if document exists
            $exists = DB::select("
                SELECT no_bukti FROM {$CBG}.stockb WHERE no_bukti = ?
            ", [$noBukti]);

            if (empty($exists)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dokumen tidak ditemukan'
                ], 404);
            }

            if ($action == 'proses') {
                // Update header
                DB::statement("
                    UPDATE {$CBG}.stockb
                    SET tgl = ?,
                        notes = ?,
                        usrnm = ?,
                        tg_smp = NOW()
                    WHERE no_bukti = ?
                ", [$tgl, $notes, $USERNAME, $noBukti]);

                // Process details
                if (!empty($details)) {
                    foreach ($details as $detail) {
                        if (isset($detail['cek']) && $detail['cek'] == '1') {
                            // Update detail
                            DB::statement("
                                UPDATE {$CBG}.stockbd
                                SET qty = ?,
                                    ket = ?,
                                    sisa = qty
                                WHERE no_bukti = ?
                                    AND kd_brg = ?
                            ", [
                                $detail['qty'] ?? 0,
                                $detail['ket'] ?? '',
                                $noBukti,
                                $detail['kd_brg']
                            ]);
                        }
                    }
                }

                // Calculate total qty
                $totalQty = DB::select("
                    SELECT SUM(qty) as total
                    FROM {$CBG}.stockbd
                    WHERE no_bukti = ?
                ", [$noBukti]);

                $total = $totalQty[0]->total ?? 0;

                DB::statement("
                    UPDATE {$CBG}.stockb
                    SET total_qty = ?
                    WHERE no_bukti = ?
                ", [$total, $noBukti]);
            } elseif ($action == 'hapus_positif') {
                // Delete positive stock differences
                DB::statement("
                    DELETE FROM {$CBG}.stockbd
                    WHERE no_bukti = ?
                        AND qty > 0
                        AND qty > sisa
                ", [$noBukti]);
            } elseif ($action == 'hapus_nol') {
                // Delete zero stock
                DB::statement("
                    DELETE FROM {$CBG}.stockbd
                    WHERE no_bukti = ?
                        AND qty = 0
                ", [$noBukti]);
            } elseif ($action == 'hapus_negatif') {
                // Delete negative stock differences
                DB::statement("
                    DELETE FROM {$CBG}.stockbd
                    WHERE no_bukti = ?
                        AND qty < sisa
                ", [$noBukti]);
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diproses'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in proses: ' . $e->getMessage());
            return response()->json([
                'error' => 'Gagal memproses data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function jasper(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG;
            $noBukti = $request->no_bukti;
            $route = $request->route_name;

            if (!$noBukti) {
                return response()->json([
                    'success' => false,
                    'message' => 'No Bukti harus diisi'
                ], 400);
            }

            // Get toko info
            $toko = DB::select("SELECT NA_TOKO FROM toko WHERE KODE = ?", [$CBG]);
            $naToko = $toko[0]->NA_TOKO ?? '';

            // Get header
            $header = DB::select("
                SELECT * FROM {$CBG}.stockb WHERE no_bukti = ?
            ", [$noBukti]);

            if (empty($header)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            // Get detail with additional info
            $detail = DB::select("
                SELECT
                    ? as nmtoko,
                    a.kd_brg as KD_BRG,
                    CONCAT(LEFT(a.no_bukti,2), RIGHT(a.no_bukti,4)) as bukti,
                    a.na_brg as NA_BRG,
                    a.ket_uk as KET_UK,
                    a.kd,
                    b.hj as HJ,
                    b.srmin as SRMIN,
                    b.ak12 as stockt,
                    c.ak12 as stockg,
                    a.qty,
                    d.type as TYPE,
                    a.ket,
                    b.tgl_psn as TGL_PSN,
                    b.qty_trm as QTY_TRM,
                    b.tgl_at as TGL_AT,
                    b.bkt_at,
                    '' as simpul,
                    IF(LEFT(a.na_brg,1)='3',
                        CASE
                            WHEN b.dtr <= 3 THEN
                                IF(LEFT(a.kd_brg,3) IN ('153','154','171'), b.dtr, '3')
                            ELSE b.dtr
                        END,
                        COALESCE(e.dtr, 0)
                    ) as DTR
                FROM {$CBG}.stockbd a
                INNER JOIN {$CBG}.stockb d ON a.no_bukti = d.no_bukti
                LEFT JOIN {$CBG}.brgdt b ON a.kd_brg = b.kd_brg
                    AND b.cbg = ?
                    AND b.yer = YEAR(NOW())
                LEFT JOIN {$CBG}.brgd c ON b.kd_brg = c.kd_brg
                    AND b.cbg = c.cbg
                    AND b.yer = c.yer
                LEFT JOIN tgz.brg_dc_ts e ON b.kd_brg = e.kd_brg
                WHERE a.no_bukti = ?
                    AND d.tgl = DATE(NOW())
                ORDER BY a.rec
            ", [$naToko, $CBG, $noBukti]);

            return response()->json([
                'success' => true,
                'data' => $detail,
                'header' => $header[0]
            ]);
        } catch (\Exception $e) {
            Log::error('Error in jasper: ' . $e->getMessage());
            return response()->json([
                'error' => 'Gagal generate laporan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function checkAll(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG;
            $noBukti = $request->no_bukti;
            $checked = $request->checked ?? true;

            DB::statement("
                UPDATE {$CBG}.stockbd
                SET qty = IF(?, sisa, NULL)
                WHERE no_bukti = ?
            ", [$checked, $noBukti]);

            return response()->json([
                'success' => true,
                'message' => $checked ? 'Semua item berhasil di-check' : 'Semua item berhasil di-uncheck'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in checkAll: ' . $e->getMessage());
            return response()->json([
                'error' => 'Gagal update data: ' . $e->getMessage()
            ], 500);
        }
    }

    // =============================================
    // TRANSAKSI PROSES STOCK OPNAME (NEW)
    // =============================================

    public function createStockOpname(Request $request)
    {
        try {
            DB::beginTransaction();

            $CBG = Auth::user()->CBG;
            $USERNAME = Auth::user()->username;
            $periode = date('mY');
            $monthstring = substr($periode, 0, 2);

            // Get toko type
            try {
                $toko = DB::select("SELECT type FROM toko WHERE kode = ?", [$CBG]) ?? [];
            } catch (\Exception $e) {
                Log::error('Error getting toko type: ' . $e->getMessage());
                $toko = [];
            }
            $kode2 = $toko[0]->type ?? '';

            // Generate no bukti
            $kode = 'SO' . substr($periode, 2, 2) . substr($periode, 0, 2);

            try {
                $noBukti = DB::select("
                    SELECT NOM{$monthstring} as NO_BUKTI
                    FROM notrans
                    WHERE trans = 'SOPJL' AND PER = ?
                ", [substr($periode, 2, 4)]);
            } catch (\Exception $e) {
                Log::error('Error getting noBukti: ' . $e->getMessage());
                $noBukti = [];
            }

            $r1 = ($noBukti[0]->NO_BUKTI ?? 0) + 1;

            DB::statement("
                UPDATE notrans
                SET NOM{$monthstring} = ?
                WHERE trans = 'SOPJL' AND PER = ?
            ", [$r1, substr($periode, 2, 4)]);

            $bkt1 = str_pad($r1, 4, '0', STR_PAD_LEFT);
            $noBuktiFull = $kode . '-' . $bkt1 . $kode2;

            // Insert header
            DB::statement("
                INSERT INTO {$CBG}.stockb
                (no_bukti, nolap, tgl, flag, per, type, usrnm, tg_smp, cbg, total_qty, notes)
                VALUES (?, ?, DATE(NOW()), 'AO', ?, 'SO', ?, NOW(), ?, 0, '')
            ", [$noBuktiFull, $kode . $bkt1, $periode, $USERNAME, $CBG]);

            // Insert detail from brgdt
            DB::statement("
                INSERT INTO {$CBG}.stockbd
                (no_bukti, rec, per, flag, kd_brg, na_brg, ket_uk, ket_kem, kd, sisa, id)
                SELECT
                    ?,
                    ROW_NUMBER() OVER (ORDER BY a.kd_brg) as rec,
                    ?,
                    'AO',
                    a.kd_brg,
                    b.na_brg,
                    b.ket_uk,
                    b.ket_kem,
                    CONCAT(LEFT(b.kd_brg,3), '-', SUBSTRING(b.kd_brg,4,4)),
                    a.ak12 as sisa,
                    (SELECT no_id FROM {$CBG}.stockb WHERE no_bukti = ?)
                FROM {$CBG}.brgdt a
                INNER JOIN {$CBG}.brg b ON a.kd_brg = b.kd_brg
                WHERE a.cbg = ?
                    AND a.yer = YEAR(NOW())
                    AND a.ak12 > 0
                ORDER BY a.kd_brg
            ", [$noBuktiFull, $periode, $noBuktiFull, $CBG]);

            DB::commit();

            // Return with redirect URL instead of just success
            return response()->json([
                'success' => true,
                'message' => 'Stock Opname berhasil dibuat',
                'no_bukti' => $noBuktiFull,
                'redirect_url' => route('tprosesstockopname') . '?status=edit&no_bukti=' . urlencode($noBuktiFull)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in createStockOpname: ' . $e->getMessage());
            return response()->json([
                'error' => 'Gagal membuat Stock Opname: ' . $e->getMessage()
            ], 500);
        }
    }

    // =============================================
    // EXPORT/IMPORT STOCK OPNAME
    // =============================================

    public function exportSO(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG;
            $noBukti = $request->no_bukti;

            if (!$noBukti) {
                return response()->json([
                    'success' => false,
                    'message' => 'No Bukti harus diisi'
                ], 400);
            }

            // Get master cbg
            $master = DB::select("SELECT kode FROM toko WHERE sta = 'MA'");
            $cbgMaster = $master[0]->kode ?? 'tgz';

            // Export data
            $data = DB::select("
                CALL {$cbgMaster}.pjl_expimp_so('EXPORT_DAT_COLL', ?, ?, '')
            ", [$CBG, $noBukti]);

            $output = [];
            foreach ($data as $row) {
                $kdbrg = substr($row->SUB ?? '', 0, 3) . substr($row->KDBAR ?? '', 0, 4);
                $nabrg = str_pad(substr($row->NA_BRG ?? '', 0, 30), 30, ' ');
                $barco = str_pad(substr($row->BARCODE ?? '', 0, 13), 13, ' ');
                $ketuk = str_pad(substr($row->KET_UK ?? '', 0, 7), 7, ' ');
                $ketkem = str_pad(substr($row->KET_KEM ?? '', 0, 18), 18, ' ');
                $stoktk = str_pad($row->SALDO ?? '0', 10, ' ', STR_PAD_LEFT);
                $hj = str_pad($row->HJ ?? '0', 12, ' ', STR_PAD_LEFT);
                $lph = str_pad($row->LPH ?? '0', 10, ' ', STR_PAD_LEFT);
                $dtr = str_pad($row->DTR ?? '0', 10, ' ', STR_PAD_LEFT);

                $output[] = $kdbrg . $barco . $nabrg . $ketuk . $ketkem . $stoktk . $hj . $lph . $dtr;
            }

            return response()->json([
                'success' => true,
                'data' => $output,
                'filename' => $noBukti . '.txt'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in exportSO: ' . $e->getMessage());
            return response()->json([
                'error' => 'Gagal export: ' . $e->getMessage()
            ], 500);
        }
    }

    public function importSO(Request $request)
    {
        try {
            DB::beginTransaction();

            $CBG = Auth::user()->CBG;
            $USERNAME = Auth::user()->username;
            $noBukti = $request->no_bukti;
            $fileContent = $request->file_content;
            $overwrite = $request->overwrite ?? false;

            if (!$noBukti || !$fileContent) {
                return response()->json([
                    'success' => false,
                    'message' => 'No Bukti dan file harus diisi'
                ], 400);
            }

            // Get master cbg
            $master = DB::select("SELECT kode FROM toko WHERE sta = 'MA'");
            $cbgMaster = $master[0]->kode ?? 'tgz';

            // Check if already imported
            if (!$overwrite) {
                $check = DB::select("
                    CALL {$cbgMaster}.pjl_expimp_so('CEK_IMPORT', ?, ?, '')
                ", [$CBG, $noBukti]);

                if (!empty($check)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Import SO ' . $noBukti . ' sudah diproses pada ' .
                            ($check[0]->HARI ?? '') . ' ' . ($check[0]->JAM ?? '') .
                            ' oleh ' . ($check[0]->USRNM ?? ''),
                        'ask_overwrite' => true
                    ], 400);
                }
            } else {
                // Delete old import
                DB::statement("
                    CALL {$cbgMaster}.pjl_expimp_so('HAPUS_IMPORT', ?, ?, '')
                ", [$CBG, $noBukti]);
            }

            // Delete old data from temp table
            DB::statement("
                DELETE FROM {$CBG}.sopjl_outlet_txt
                WHERE cbg = ? AND no_bukti = ?
            ", [$CBG, $noBukti]);

            // Process import
            $lines = explode("\n", $fileContent);
            $values = [];

            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;

                $parts = explode(',', $line);
                if (count($parts) >= 2) {
                    $kdBrg = trim($parts[0]);
                    $qty = trim($parts[1]);
                    $values[] = "('{$CBG}', '{$noBukti}', '{$kdBrg}', {$qty})";
                }
            }

            if (!empty($values)) {
                DB::statement("
                    INSERT INTO {$CBG}.sopjl_outlet_txt (cbg, no_bukti, kd_brg, riil)
                    VALUES " . implode(',', $values));

                // Update SO
                DB::statement("
                    CALL {$cbgMaster}.pjl_expimp_so('UPDATE_SO_IMPORT', ?, ?, ?)
                ", [$CBG, $noBukti, $USERNAME]);

                // Backup to permanent table
                DB::statement("
                    DROP TABLE IF EXISTS {$CBG}.sopjl_outlet_txt{$CBG}
                ");

                DB::statement("
                    CREATE TABLE {$CBG}.sopjl_outlet_txt{$CBG}
                    SELECT * FROM {$CBG}.sopjl_outlet_txt
                ");

                DB::statement("
                    ALTER TABLE {$CBG}.sopjl_outlet_txt{$CBG}
                    MODIFY COLUMN NO_ID int(11) NOT NULL AUTO_INCREMENT FIRST,
                    ADD PRIMARY KEY (NO_ID),
                    ADD INDEX `cari` (`NO_BUKTI`, `KD_BRG`)
                ");
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => $noBukti . ' berhasil diimport'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in importSO: ' . $e->getMessage());
            return response()->json([
                'error' => 'Gagal import: ' . $e->getMessage()
            ], 500);
        }
    }
}