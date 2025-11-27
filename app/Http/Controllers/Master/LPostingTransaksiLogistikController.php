<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LPostingTransaksiLogistikController extends Controller
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

        $data = [
            'cbg' => $cbg,
            'periode' => session('periode', date('m-Y')),
            'flagg' => session('flagg', 'LG')
        ];

        return view('logistik_posting_transaksi_logistik.index', $data);
    }

    public function getPostingTransaksiLogistik(Request $request)
    {
        $cbg = $request->get('cbg', session('user_cabang', 'CB'));
        $periode = $request->get('periode', date('m-Y'));
        $flagg = $request->get('flagg', 'LG');

        // Store in session
        session(['user_cabang' => $cbg]);
        session(['periode' => $periode]);
        session(['flagg' => $flagg]);

        // Parse periode (MM-YYYY)
        $periodeParts = explode('-', $periode);
        $month = isset($periodeParts[0]) ? $periodeParts[0] : date('m');
        $year = isset($periodeParts[1]) ? $periodeParts[1] : date('Y');

        $query = "
            SELECT no_bukti, tgl, notes, total_qty as total, 0 as cek,
                   '' as outlet, '' as bukti_cbg" .
            ($flagg == 'LG' ? ", '' as no_po" : "") . "
            FROM lgstockb
            WHERE flag = 'TL' AND posted = 0 AND cbg = ?
            AND MONTH(tgl) = ? AND YEAR(tgl) = ?
            ORDER BY no_bukti";

        $data = DB::select($query, [$cbg, $month, $year]);

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
            $cbg = session('user_cabang', 'CB');

            if (empty($selected_bukti)) {
                return response()->json(['success' => false, 'message' => 'Tidak ada data yang dipilih']);
            }

            $posted_count = 0;
            $max_process = 15;

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
                        UPDATE lgstockbd a, brglogd b
                        SET a.kdlaku = b.kdlaku
                        WHERE a.kd_brg = b.kd_brg AND a.no_bukti = ?", [$bukti]);

                    // Check if can be posted (current month)
                    $check = DB::select("
                        SELECT no_bukti
                        FROM lgstockb
                        WHERE no_bukti = ? AND cbg = ?
                        AND MONTH(tgl) = MONTH(NOW())
                        AND YEAR(tgl) = YEAR(NOW())", [$bukti, $cbg]);

                    if (count($check) > 0) {
                        // Get detail data
                        $details = DB::select("
                            SELECT a.qty, a.kd_brg, a.no_id, a.flag, b.kdlaku
                            FROM lgstockbd a, brglogd b
                            WHERE a.no_bukti = ?
                            AND a.kd_brg = b.kd_brg AND b.cbg = ?", [$bukti, $cbg]);

                        foreach ($details as $detail) {
                            if ($detail->flag == 'TL') {
                                // Update stock - retur dari toko ke gudang
                                DB::statement(
                                    "
                                    UPDATE brglogd
                                    SET ke00 = ke00 + ?, ak00 = aw00 + ma00 - ke00 + ln00,
                                        rln00 = rln00 + ?, rak00 = raw00 + rma00 - rke00 + rln00
                                    WHERE kd_brg = ? AND cbg = ?",
                                    [$detail->qty, $detail->qty, $detail->kd_brg, $cbg]
                                );
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

        return response()->json([
            'success' => true,
            'message' => 'Print berhasil untuk ' . count($selected_bukti) . ' dokumen'
        ]);
    }

    public function destroy($no_bukti)
    {
        try {
            $check = DB::select("
                SELECT no_bukti
                FROM lgstockb
                WHERE no_bukti = ? AND posted = 0", [$no_bukti]);

            if (empty($check)) {
                return response()->json(['success' => false, 'message' => 'Data tidak dapat dihapus atau tidak ditemukan']);
            }

            DB::beginTransaction();

            DB::statement("DELETE FROM lgstockbd WHERE no_bukti = ?", [$no_bukti]);
            DB::statement("DELETE FROM lgstockb WHERE no_bukti = ?", [$no_bukti]);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Data berhasil dihapus']);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}