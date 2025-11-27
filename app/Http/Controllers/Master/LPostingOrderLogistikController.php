<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LPostingOrderLogistikController extends Controller
{
    public function index()
    {
        try {
            $cbg = DB::select("SELECT * FROM toko WHERE STA IN ('MA','CB','DC') ORDER BY NO_ID ASC");
            if (empty($cbg)) {
                $cbg = [];
            }
        } catch (\Illuminate\Database\QueryException $e) {
            $cbg = [];
        }

        $periode = session('periode', date('m-Y'));
        $flagg = session('flagg', 'LG');

        $data = [
            'cbg' => $cbg,
            'periode' => $periode,
            'flagg' => $flagg
        ];

        return view('logistik_posting_order_logistik.index', $data);
    }

    public function getPostingOrderLogistik(Request $request)
    {
        $flagg = $request->get('flagg', 'LG');
        $periode = $request->get('periode');
        $cbg = $request->get('cbg');

        session(['flagg' => $flagg, 'periode' => $periode, 'cbg' => $cbg]);

        $query = "
            SELECT no_bukti, tgl, notes, divisi, total_qty as total, 0 as cek,
                   cbg as outlet, no_bukti as bukti_cbg, no_po
            FROM lgstockb
            WHERE flag = ? AND posted = 0 AND per = ?
            AND cbg = ?
            ORDER BY no_bukti";

        $data = DB::select($query, [$flagg, $periode, $cbg]);

        return DataTables::of(collect($data))
            ->addIndexColumn()
            ->editColumn('tgl', function ($row) {
                return Carbon::parse($row->tgl)->format('d/m/Y');
            })
            ->editColumn('total', function ($row) {
                return number_format($row->total, 0);
            })
            ->addColumn('cek_box', function ($row) {
                return '<input type="checkbox" class="cek-item" value="' . $row->no_bukti . '" data-bukti="' . $row->no_bukti . '">';
            })
            ->rawColumns(['cek_box'])
            ->make(true);
    }

    public function store(Request $request)
    {
        try {
            $selected_bukti = $request->get('selected_bukti', []);
            $cbg = session('cbg');
            $flagg = session('flagg', 'LG');
            $periode = session('periode');

            if (empty($selected_bukti)) {
                return response()->json(['success' => false, 'message' => 'Tidak ada data yang dipilih']);
            }

            $posted_count = 0;
            $max_process = 6;

            foreach ($selected_bukti as $bukti) {
                if ($posted_count >= $max_process) break;

                DB::beginTransaction();

                try {
                    // Update KDLAKU
                    DB::statement("
                        UPDATE lgstockbd a, lgstockb b
                        SET a.cbg = b.cbg, a.per = b.per
                        WHERE a.no_bukti = b.no_bukti AND a.no_bukti = ?", [$bukti]);

                    DB::statement("
                        UPDATE lgstockd a, brglogd b
                        SET a.kdlaku = b.kdlaku
                        WHERE a.kd_brg = b.kd_brg AND a.no_bukti = ?", [$bukti]);

                    // Check if can be posted (current month)
                    $check = DB::select("
                        SELECT no_bukti, cbg
                        FROM lgstockb
                        WHERE no_bukti = ?
                        AND MONTH(tgl) = MONTH(NOW())
                        AND YEAR(tgl) = YEAR(NOW())", [$bukti]);

                    if (count($check) > 0) {
                        // Get detail data
                        $details = DB::select("
                            SELECT a.qty, a.kd_brg, a.no_id, a.no_bukti, a.flag, a.abl, b.kdlaku
                            FROM lgstockbd a, brglogd b
                            WHERE a.no_bukti = ?
                            AND a.kd_brg = b.kd_brg AND b.cbg = ?", [$bukti, $cbg]);

                        foreach ($details as $detail) {
                            if ($detail->flag == 'LG') {
                                // Update stock
                                DB::statement(
                                    "
                                    UPDATE brglogd
                                    SET ke00 = ke00 + ?, ak00 = aw00 + ma00 - ke00 + ln00
                                    WHERE kd_brg = ? AND cbg = ?",
                                    [$detail->qty, $detail->kd_brg, $cbg]
                                );

                                if ($detail->qty > 0) {
                                    // Get PO number
                                    $po_data = DB::select("
                                        SELECT no_po
                                        FROM lgstockb
                                        WHERE no_bukti = ?", [$bukti]);

                                    if (!empty($po_data)) {
                                        $no_po = $po_data[0]->no_po;

                                        // Delete from tpolog
                                        DB::statement(
                                            "
                                            DELETE FROM tpolog
                                            WHERE no_bukti = ? AND kd_brg = ?",
                                            [$no_po, $detail->kd_brg]
                                        );

                                        // Update tpolog
                                        DB::statement("
                                            UPDATE tpolog
                                            SET bktk = ''
                                            WHERE no_bukti = ?", [$no_po]);
                                    }
                                }
                            }
                        }

                        // Call posting procedure
                        DB::statement("CALL lgpoststkb(?)", [$bukti]);

                        $posted_count++;
                        DB::commit();
                    } else {
                        DB::rollback();
                        return response()->json([
                            'success' => false,
                            'message' => $bukti . ' Tidak bisa diposting / terlambat posting!'
                        ]);
                    }
                } catch (\Exception $e) {
                    DB::rollback();
                    return response()->json([
                        'success' => false,
                        'message' => 'Error posting ' . $bukti . ': ' . $e->getMessage()
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => $posted_count . ' dokumen berhasil diposting'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function cekOrder(Request $request)
    {
        $action = $request->get('action');

        if ($action == 'check_all') {
            return response()->json(['success' => true, 'action' => 'check_all']);
        } elseif ($action == 'uncheck_all') {
            return response()->json(['success' => true, 'action' => 'uncheck_all']);
        }

        return response()->json(['success' => false]);
    }

    public function browse(Request $request)
    {
        $q = $request->get('q', '');
        $cbg = $request->get('cbg');

        if (!empty($q)) {
            $barang = DB::select("
                SELECT kd_brg, na_brg, ket_uk, ket_kem, barcode
                FROM {$cbg}.brglog
                WHERE (na_brg LIKE ? OR kd_brg LIKE ? OR barcode LIKE ?)
                ORDER BY kd_brg
                LIMIT 50", ["%$q%", "%$q%", "%$q%"]);
        } else {
            $barang = DB::select("
                SELECT kd_brg, na_brg, ket_uk, ket_kem, barcode
                FROM {$cbg}.brglog
                ORDER BY kd_brg
                LIMIT 50");
        }

        return response()->json($barang);
    }

    public function getBarangDetail(Request $request)
    {
        $kd_brg = $request->kd_brg;
        $cbg = $request->cbg;

        $barang = DB::select(
            "
            SELECT A.kd_brg, A.na_brg, A.ket_uk, A.ket_kem, A.barcode,
                   B.kdlaku, B.hj, B.hb, B.ak00 as saldo
            FROM {$cbg}.brglog A
            LEFT JOIN {$cbg}.brglogd B ON A.kd_brg = B.kd_brg AND B.cbg = ? AND B.yer = YEAR(NOW())
            WHERE A.kd_brg = ? OR A.barcode = ?",
            [$cbg, $kd_brg, $kd_brg]
        );

        if (!empty($barang)) {
            return response()->json([
                'success' => true,
                'exists' => true,
                'data' => $barang[0]
            ]);
        }

        return response()->json([
            'success' => true,
            'exists' => false,
            'data' => null
        ]);
    }

    public function printOrder(Request $request)
    {
        $selected_bukti = $request->get('selected_bukti', []);

        if (empty($selected_bukti)) {
            return response()->json(['success' => false, 'message' => 'Tidak ada data yang dipilih untuk dicetak']);
        }

        // Logic for printing can be implemented here
        return response()->json([
            'success' => true,
            'message' => 'Print berhasil untuk ' . count($selected_bukti) . ' dokumen'
        ]);
    }

    public function destroy($no_bukti)
    {
        try {
            // Check if can be deleted (not posted)
            $check = DB::select("
                SELECT no_bukti
                FROM lgstockb
                WHERE no_bukti = ? AND posted = 0", [$no_bukti]);

            if (empty($check)) {
                return response()->json(['success' => false, 'message' => 'Data tidak dapat dihapus atau tidak ditemukan']);
            }

            DB::beginTransaction();

            // Delete detail first
            DB::statement("DELETE FROM lgstockbd WHERE no_bukti = ?", [$no_bukti]);

            // Delete header
            DB::statement("DELETE FROM lgstockb WHERE no_bukti = ?", [$no_bukti]);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Data berhasil dihapus']);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
