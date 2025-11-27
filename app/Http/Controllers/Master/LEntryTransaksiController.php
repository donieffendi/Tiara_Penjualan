<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LEntryTransaksiController extends Controller
{
    public function index()
    {
        return view('logistik_entry_transaksi.index');
    }

    public function getEntryTransaksi(Request $request)
    {
        $periode = session('periode', date('Y-m'));
        $cbg = session('cbg', '01');

        // Reset print flags - equivalent to Delphi's print.SQL update
        DB::statement("UPDATE lgstockb SET print=0 WHERE flag='TL'");
        DB::statement("UPDATE lgstockbz SET print=0 WHERE flag='TL'");

        // Main query matching Delphi's stockb.sql query
        $query = DB::select(
            "
        SELECT NO_BUKTI, TGL, TOTAL_QTY, NOTES, TYPE, POSTED, print, 'a' as com
        FROM lgstockb
        WHERE per=? AND flag='TL' AND cbg=?
        UNION ALL
        SELECT NO_BUKTI, TGL, TOTAL_QTY, NOTES, TYPE, POSTED, print, 'b' as com
        FROM lgstockbz
        WHERE per=? AND flag='TL' AND cbg=?
        ORDER BY NO_BUKTI DESC",
            [$periode, $cbg, $periode, $cbg]
        );

        return Datatables::of(collect($query))
            ->addIndexColumn()
            ->editColumn('TGL', function ($row) {
                return $row->TGL ? date('d/m/Y', strtotime($row->TGL)) : '';
            })
            ->editColumn('TOTAL_QTY', function ($row) {
                return number_format($row->TOTAL_QTY, 2);
            })
            ->addColumn('action', function ($row) {
                $btnEdit = '<button onclick="editData(\'' . $row->NO_BUKTI . '\')" class="btn btn-sm btn-primary" title="Edit"><i class="fas fa-edit"></i></button>';
                $btnPrint = '<button onclick="printData(\'' . $row->NO_BUKTI . '\')" class="btn btn-sm btn-info ml-1" title="Print"><i class="fas fa-print"></i></button>';
                return $btnEdit . ' ' . $btnPrint;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function edit(Request $request)
    {
        $no_bukti = $request->get('no_bukti');
        $status = $request->get('status', 'simpan');

        $data = [
            'no_bukti' => $no_bukti,
            'status' => $status,
            'header' => null,
            'detail' => []
        ];

        if ($status == 'edit' && $no_bukti) {
            // Check if posted first (matching Delphi logic)
            $check_posted = DB::select("SELECT posted FROM lgstockb WHERE no_bukti = ?", [$no_bukti]);

            if (!empty($check_posted) && $check_posted[0]->posted == 1) {
                return redirect()->route('lentrytransaksi')->with('error', 'Data sudah terposting !!');
            }

            // Get header data
            $header = DB::select("SELECT * FROM lgstockb WHERE no_bukti = ? ORDER BY no_bukti", [$no_bukti]);

            if (!empty($header)) {
                // Get detail data matching Delphi's query structure
                $detail = DB::select(
                    "
                    SELECT NO_BUKTI, REC, KD_BRG, NA_BRG, ket_kem, QTY, KET, PER, NO_ID
                    FROM lgstockbd
                    WHERE NO_BUKTI = ?
                    ORDER BY REC",
                    [$no_bukti]
                );

                $data['header'] = $header[0];
                $data['detail'] = $detail;
            }
        }

        return view('logistik_entry_transaksi.edit', $data);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'no_bukti' => 'required',
            'tgl' => 'required|date',
            'details' => 'required|array|min:1'
        ]);

        DB::beginTransaction();

        try {
            $no_bukti = trim($request->no_bukti);
            $status = $request->status;
            $periode = session('periode', date('Y-m'));
            $cbg = session('cbg', '01');
            $username = Auth::user()->username ?? 'system';

            if ($status == 'simpan') {
                // Generate no_bukti if needed (matching Delphi logic)
                if ($no_bukti == '+') {
                    $no_bukti = $this->generateNoBukti($periode, $cbg);
                }

                // Check if no_bukti already exists
                $existing = DB::select("SELECT no_bukti FROM lgstockb WHERE no_bukti = ?", [$no_bukti]);
                if (!empty($existing)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No Bukti sudah ada!'
                    ]);
                }

                $total_qty = array_sum(array_column($request->details, 'qty'));

                // Insert header (matching Delphi's INSERT structure)
                DB::statement("
                    INSERT INTO lgstockb (no_bukti, tgl, flag, per, total_qty, notes, usrnm, tg_smp, cbg, type)
                    VALUES (?, ?, 'TL', ?, ?, ?, ?, NOW(), ?, ?)", [
                    $no_bukti,
                    $request->tgl,
                    $periode,
                    $total_qty,
                    $request->notes ?? '',
                    $username,
                    $cbg,
                    $request->type ?? 'IN'
                ]);

                // Get the ID for detail records
                $header_id = DB::select("SELECT no_id FROM lgstockb WHERE no_bukti = ?", [$no_bukti]);
                $id = $header_id[0]->no_id ?? 0;

                // Insert details
                foreach ($request->details as $index => $detail) {
                    DB::statement("
                        INSERT INTO lgstockbd (no_bukti, rec, per, flag, kd_brg, na_brg, ket_kem, qty, sisa, ket, id)
                        VALUES (?, ?, ?, 'TL', ?, ?, ?, ?, ?, ?, ?)", [
                        $no_bukti,
                        $index + 1,
                        $periode,
                        $detail['kd_brg'],
                        $detail['na_brg'],
                        $detail['ket_kem'] ?? '',
                        $detail['qty'],
                        $detail['qty'], // sisa = qty initially
                        $detail['ket'] ?? '',
                        $id
                    ]);
                }
            } else { // Update mode
                $total_qty = array_sum(array_column($request->details, 'qty'));

                // Update header
                DB::statement("
                    UPDATE lgstockb
                    SET tgl=?, notes=?, total_qty=?, usrnm=?, tg_smp=NOW(), type=?
                    WHERE no_bukti=?", [
                    $request->tgl,
                    $request->notes ?? '',
                    $total_qty,
                    $username,
                    $request->type ?? 'IN',
                    $no_bukti
                ]);

                // Handle detail updates (matching Delphi's complex update logic)
                $existing_details = DB::select("SELECT no_id FROM lgstockbd WHERE no_bukti = ?", [$no_bukti]);

                // Process existing records for updates/deletes
                foreach ($existing_details as $existing) {
                    $found = false;
                    foreach ($request->details as $detail) {
                        if (isset($detail['no_id']) && $detail['no_id'] == $existing->no_id) {
                            // Update existing record
                            DB::statement("
                                UPDATE lgstockbd SET
                                rec=?, kd_brg=?, na_brg=?, ket_kem=?, qty=?, ket=?, sisa=?
                                WHERE no_id=?", [
                                $detail['rec'] ?? 1,
                                $detail['kd_brg'],
                                $detail['na_brg'],
                                $detail['ket_kem'] ?? '',
                                $detail['qty'],
                                $detail['ket'] ?? '',
                                $detail['qty'],
                                $existing->no_id
                            ]);
                            $found = true;
                            break;
                        }
                    }

                    if (!$found) {
                        // Delete record not found in new data
                        DB::statement("DELETE FROM lgstockbd WHERE no_id = ?", [$existing->no_id]);
                    }
                }

                // Insert new records (no_id = 0 or not set)
                $header_id = DB::select("SELECT no_id FROM lgstockb WHERE no_bukti = ?", [$no_bukti]);
                $id = $header_id[0]->no_id ?? 0;

                foreach ($request->details as $index => $detail) {
                    if (!isset($detail['no_id']) || $detail['no_id'] == 0) {
                        DB::statement("
                            INSERT INTO lgstockbd (no_bukti, rec, per, flag, kd_brg, na_brg, ket_kem, qty, sisa, ket, id)
                            VALUES (?, ?, ?, 'TL', ?, ?, ?, ?, ?, ?, ?)", [
                            $no_bukti,
                            $index + 1,
                            $periode,
                            $detail['kd_brg'],
                            $detail['na_brg'],
                            $detail['ket_kem'] ?? '',
                            $detail['qty'],
                            $detail['qty'],
                            $detail['ket'] ?? '',
                            $id
                        ]);
                    }
                }
            }

            // Update detail with additional info (matching Delphi's update detail logic)
            DB::statement(
                "
                UPDATE lgstockbd A, brglog B, brglogd C
                SET
                    A.ket_uk = B.ket_uk,
                    A.cbg = ?,
                    A.hj = C.hj,
                    A.hb = C.hb,
                    A.saldo = C.ak00,
                    A.barcode = B.barcode,
                    A.kdlaku = C.kdlaku
                WHERE A.no_bukti = ?
                    AND B.kd_brg = A.kd_brg
                    AND C.kd_brg = A.kd_brg
                    AND C.cbg = ?",
                [$cbg, $no_bukti, $cbg]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil disimpan'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function browse(Request $request)
    {
        $q = $request->get('q', '');

        if (!empty($q)) {
            $barang = DB::select("
                SELECT kd_brg, na_brg, ket_uk, ket_kem, barcode
                FROM brglog
                WHERE (na_brg LIKE ? OR kd_brg LIKE ? OR barcode LIKE ?)
                ORDER BY kd_brg
                LIMIT 50", ["%$q%", "%$q%", "%$q%"]);
        } else {
            $barang = DB::select("
                SELECT kd_brg, na_brg, ket_uk, ket_kem, barcode
                FROM brglog
                ORDER BY kd_brg
                LIMIT 50");
        }

        return response()->json($barang);
    }

    public function getBarangDetail(Request $request)
    {
        $kd_brg = $request->kd_brg;
        $cbg = session('cbg', '01');

        // Matching Delphi's barang lookup logic
        $barang = DB::select(
            "
            SELECT A.kd_brg, A.na_brg, A.ket_uk, A.ket_kem, A.barcode,
                   B.kdlaku, C.hj, C.hb, C.ak00 as saldo
            FROM brglog A
            LEFT JOIN brglogd B ON A.kd_brg = B.kd_brg AND B.cbg = ? AND B.yer = YEAR(NOW())
            LEFT JOIN brglogd C ON A.kd_brg = C.kd_brg AND C.cbg = ?
            WHERE A.kd_brg = ? OR A.barcode = ?",
            [$cbg, $cbg, $kd_brg, $kd_brg]
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

    public function cekOrder(Request $request)
    {
        $no_bukti = $request->no_bukti;
        $result = DB::select("SELECT COUNT(*) as ADA FROM lgstockb WHERE no_bukti = ?", [$no_bukti]);

        return response()->json(['exists' => $result[0]->ADA > 0]);
    }

    public function printOrder(Request $request)
    {
        $no_bukti = $request->no_bukti;
        $cbg = session('cbg', '01');

        // Check posted status to determine which table to use
        $check_posted = DB::select("SELECT posted FROM lgstockb WHERE no_bukti = ?", [$no_bukti]);

        if (!empty($check_posted) && $check_posted[0]->posted == 0) {
            // Use lgstockb/lgstockbd for unposted data
            $data = DB::select(
                "
                SELECT A.no_bukti, A.tgl, A.notes, A.usrnm, A.tg_smp as waktu,
                       D.sub, D.supp, D.kdbar, CONCAT(C.kdlaku, C.klk) AS kd,
                       D.na_brg, D.ket_uk, D.ket_kem, C.hj, SUM(B.qty) as qty,
                       D.retur as type, B.ket
                FROM lgstockb A, lgstockbd B, brglogd C, brglog D
                WHERE B.no_bukti = A.no_bukti
                    AND C.kd_brg = B.kd_brg
                    AND D.kd_brg = B.kd_brg
                    AND C.yer = YEAR(NOW())
                    AND C.cbg = ?
                    AND A.no_bukti = ?
                GROUP BY B.kd_brg
                ORDER BY D.supp, B.kd_brg",
                [$cbg, $no_bukti]
            );
        } else {
            // Use lgstockbz/lgstockbzd for posted data
            $data = DB::select(
                "
                SELECT A.no_bukti, A.tgl, A.notes, A.usrnm, A.tg_smp as waktu,
                       D.sub, D.supp, D.kdbar, CONCAT(C.kdlaku, C.klk) AS kd,
                       D.na_brg, D.ket_uk, D.ket_kem, C.hj, SUM(B.qty) as qty,
                       D.retur as type, B.ket
                FROM lgstockbz A, lgstockbzd B, brglogd C, brglog D
                WHERE B.no_bukti = A.no_bukti
                    AND C.kd_brg = B.kd_brg
                    AND D.kd_brg = B.kd_brg
                    AND C.yer = YEAR(NOW())
                    AND C.cbg = ?
                    AND A.no_bukti = ?
                GROUP BY B.kd_brg
                ORDER BY D.supp, B.kd_brg",
                [$cbg, $no_bukti]
            );
        }

        return response()->json(['data' => $data]);
    }

    public function togglePrint(Request $request)
    {
        $no_bukti = $request->no_bukti;
        $cbg = session('cbg', '01');

        // Check which table to use based on com field logic
        $current_record = DB::select(
            "
            SELECT print, 'a' as com FROM lgstockb WHERE no_bukti = ?
            UNION ALL
            SELECT print, 'b' as com FROM lgstockbz WHERE no_bukti = ?",
            [$no_bukti, $no_bukti]
        );

        if (!empty($current_record)) {
            $record = $current_record[0];
            $new_print_status = $record->print ? 0 : 1;

            if ($record->com == 'a') {
                DB::statement("UPDATE lgstockb SET print = ? WHERE no_bukti = ?", [$new_print_status, $no_bukti]);
            } else {
                DB::statement("UPDATE lgstockbz SET print = ? WHERE no_bukti = ?", [$new_print_status, $no_bukti]);
            }
        }

        return response()->json(['success' => true]);
    }

    public function batchPrint(Request $request)
    {
        $cbg = session('cbg', '01');

        // Get all records marked for printing (matching Delphi's btnPrintClick logic)
        $records_to_print = DB::select(
            "
            SELECT no_bukti FROM lgstockb WHERE print = 1 AND flag = 'LG' AND cbg = ?
            UNION ALL
            SELECT no_bukti FROM lgstockbz WHERE print = 1 AND flag = 'LG' AND cbg = ?",
            [$cbg, $cbg]
        );

        $printed_count = 0;
        foreach ($records_to_print as $record) {
            // Process each record for printing (you would call your print logic here)
            $printed_count++;
        }

        // Reset all print flags after batch print
        DB::statement("UPDATE lgstockb SET print = 0 WHERE flag = 'LG'");
        DB::statement("UPDATE lgstockbz SET print = 0 WHERE flag = 'LG'");

        return response()->json([
            'success' => true,
            'message' => 'Print Selesai! ' . $printed_count . ' dokumen telah diprint.',
            'count' => $printed_count
        ]);
    }

    public function destroy($no_bukti)
    {
        try {
            $check_posted = DB::select("SELECT posted FROM lgstockb WHERE no_bukti = ?", [$no_bukti]);

            if (!empty($check_posted) && $check_posted[0]->posted == 1) {
                return redirect()->route('lentrytransaksi')->with('error', 'Data sudah terposting, tidak dapat dihapus');
            }

            DB::statement("DELETE FROM lgstockbd WHERE no_bukti = ?", [$no_bukti]);
            DB::statement("DELETE FROM lgstockb WHERE no_bukti = ?", [$no_bukti]);

            return redirect()->route('lentrytransaksi')->with('status', 'Data berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('lentrytransaksi')->with('error', 'Gagal menghapus data');
        }
    }

    private function generateNoBukti($periode, $cbg)
    {
        $monthString = substr($periode, 0, 2);
        $year = substr($periode, -4);

        // Get toko type
        $toko = DB::select("SELECT type FROM toko WHERE kode = ?", [$cbg]);
        $kode2 = $toko[0]->type ?? '';

        $kode = 'TL' . substr($year, -2) . $monthString;

        // Get next number
        $notrans = DB::select("SELECT NOM{$monthString} as no_bukti FROM notrans WHERE trans='TRANSLOG' AND per=?", [$year]);
        $r1 = ($notrans[0]->no_bukti ?? 0) + 1;

        // Update counter
        DB::statement("UPDATE notrans SET NOM{$monthString} = ? WHERE trans='TRANSLOG' AND per=?", [$r1, $year]);

        $bkt1 = str_pad($r1, 4, '0', STR_PAD_LEFT);
        return $kode . '-' . $bkt1 . $kode2;
    }
}
