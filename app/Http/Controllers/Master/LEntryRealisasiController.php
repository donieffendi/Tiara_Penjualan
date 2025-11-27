<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LEntryRealisasiController extends Controller
{
    public function index()
    {
        return view('logistik_entry_realisasi.index');
    }

    public function getEntryRealisasi(Request $request)
    {
        $periode = session('periode', date('Y-m'));
        $cbg = session('cbg', '01');

        // Query untuk mendapatkan data detail barang dari lgstockbd yang terkait dengan lgstockb
        $query = DB::select("
            SELECT
                a.no_bukti,
                b.kd_brg,
                b.na_brg,
                c.ket_uk,
                b.qty,
                c.ket_kem
            FROM lgstockb a
            INNER JOIN lgstockbd b ON a.no_bukti = b.no_bukti
            LEFT JOIN brglog c ON b.kd_brg = c.kd_brg
            WHERE a.per=? AND a.flag='LG' AND a.cbg=?

            UNION ALL

            SELECT
                a.no_bukti,
                b.kd_brg,
                b.na_brg,
                c.ket_uk,
                b.qty,
                c.ket_kem
            FROM lgstockbz a
            INNER JOIN lgstockbzd b ON a.no_bukti = b.no_bukti
            LEFT JOIN brglog c ON b.kd_brg = c.kd_brg
            WHERE a.per=? AND a.flag='LG' AND a.cbg=?
            ORDER BY kd_brg ASC
        ", [$periode, $cbg, $periode, $cbg]);

        return Datatables::of(collect($query))
            ->addIndexColumn()
            ->editColumn('qty', function ($row) {
                return number_format($row->qty, 2, '.', ',');
            })
            ->editColumn('ket_uk', function ($row) {
                return $row->ket_uk ?? '-';
            })
            ->editColumn('ket_kem', function ($row) {
                return $row->ket_kem ?? '-';
            })
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
            $header = DB::select("SELECT * FROM lgstockb WHERE no_bukti = ?", [$no_bukti]);

            if (!empty($header)) {
                $detail = DB::select("
                    SELECT a.*, b.ak00 as stok_gudang
                    FROM lgstockbd a
                    LEFT JOIN brglogd b ON a.kd_brg = b.kd_brg AND b.cbg = ?
                    WHERE a.no_bukti = ?
                    ORDER BY a.rec", [session('cbg', '01'), $no_bukti]);

                $data['header'] = $header[0];
                $data['detail'] = $detail;
            }
        }

        return view('logistik_entry_realisasi.edit', $data);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'no_bukti' => 'required',
            'no_po' => 'required',
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
                // Generate nomor bukti jika input '+'
                if ($no_bukti == '+') {
                    $no_bukti = $this->generateNoBukti($periode, $cbg);
                } else {
                    // Cek apakah no_bukti sudah ada
                    $existing = DB::select("SELECT no_bukti FROM lgstockb WHERE no_bukti = ?", [$no_bukti]);
                    if (!empty($existing)) {
                        return response()->json([
                            'success' => false,
                            'message' => 'No Bukti sudah ada!'
                        ]);
                    }
                }

                $total_qty = array_sum(array_column($request->details, 'qty'));

                DB::statement("
                    INSERT INTO lgstockb (no_bukti, no_po, tgl, per, notes, divisi, total_qty, posted, flag, cbg, usrnm, tg_smp)
                    VALUES (?, ?, ?, ?, ?, ?, ?, 0, 'LG', ?, ?, NOW())", [
                    $no_bukti,
                    $request->no_po,
                    $request->tgl,
                    $periode,
                    $request->notes ?? '',
                    $request->divisi ?? '',
                    $total_qty,
                    $cbg,
                    $username
                ]);

                // Get no_id dari header yang baru di-insert
                $header = DB::select("SELECT no_id FROM lgstockb WHERE no_bukti = ?", [$no_bukti]);
                $no_id = $header[0]->no_id;

                foreach ($request->details as $index => $detail) {
                    DB::statement("
                        INSERT INTO lgstockbd (no_bukti, rec, kd_brg, na_brg, ket_kem, qto, qty, ket, per, flag, id, kdlaku, barcode)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'LG', ?, ?, ?)", [
                        $no_bukti,
                        $index + 1,
                        $detail['kd_brg'],
                        $detail['na_brg'],
                        $detail['ket_kem'] ?? '',
                        $detail['qto'] ?? 0,
                        $detail['qty'],
                        $detail['ket'] ?? '',
                        $periode,
                        $no_id,
                        $detail['kdlaku'] ?? '',
                        $detail['barcode'] ?? ''
                    ]);
                }

                // Update TPOLOG jika ada
                DB::statement("UPDATE tpolog SET bktk = ? WHERE no_bukti = ?", [$no_bukti, $request->no_po]);
            } else {
                // Mode edit
                $total_qty = array_sum(array_column($request->details, 'qty'));

                DB::statement("
                    UPDATE lgstockb
                    SET tgl=?, notes=?, divisi=?, total_qty=?, usrnm=?, tg_smp=NOW()
                    WHERE no_bukti=?", [
                    $request->tgl,
                    $request->notes ?? '',
                    $request->divisi ?? '',
                    $total_qty,
                    $username,
                    $no_bukti
                ]);

                // Hapus detail lama
                DB::statement("DELETE FROM lgstockbd WHERE no_bukti = ?", [$no_bukti]);

                // Get no_id dari header
                $header = DB::select("SELECT no_id FROM lgstockb WHERE no_bukti = ?", [$no_bukti]);
                $no_id = $header[0]->no_id;

                // Insert detail baru
                foreach ($request->details as $index => $detail) {
                    DB::statement("
                        INSERT INTO lgstockbd (no_bukti, rec, kd_brg, na_brg, ket_kem, qto, qty, ket, per, flag, id, kdlaku, barcode)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'LG', ?, ?, ?)", [
                        $no_bukti,
                        $index + 1,
                        $detail['kd_brg'],
                        $detail['na_brg'],
                        $detail['ket_kem'] ?? '',
                        $detail['qto'] ?? 0,
                        $detail['qty'],
                        $detail['ket'] ?? '',
                        $periode,
                        $no_id,
                        $detail['kdlaku'] ?? '',
                        $detail['barcode'] ?? ''
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil disimpan',
                'no_bukti' => $no_bukti
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    private function generateNoBukti($periode, $cbg)
    {
        $month = substr($periode, 0, 2);
        $year = substr($periode, -2);

        // Get type toko
        $toko = DB::select("SELECT type FROM toko WHERE kode = ?", [$cbg]);
        $type = !empty($toko) ? $toko[0]->type : '';

        // Get nomor terakhir
        $nomor = DB::select("SELECT nom{$month} as nomor FROM notrans WHERE trans='LGSTOCKB' AND per=?", ['20' . $year]);
        $no = !empty($nomor) ? $nomor[0]->nomor : 0;
        $no = $no + 1;

        // Update nomor
        DB::statement("UPDATE notrans SET nom{$month} = ? WHERE trans='LGSTOCKB' AND per=?", [$no, '20' . $year]);

        // Format: LGYYMMnnnnTYPE
        return 'LG' . $year . $month . '-' . str_pad($no, 4, '0', STR_PAD_LEFT) . $type;
    }

    public function browse(Request $request)
    {
        $q = $request->get('q', '');

        if (!empty($q)) {
            $barang = DB::select("
                SELECT kd_brg, na_brg, sub, kdbar, ket_uk, ket_kem, hb, barcode
                FROM brglog
                WHERE (na_brg LIKE ? OR kd_brg LIKE ? OR barcode LIKE ?)
                ORDER BY kd_brg
                LIMIT 50", ["%$q%", "%$q%", "%$q%"]);
        } else {
            $barang = DB::select("
                SELECT kd_brg, na_brg, sub, kdbar, ket_uk, ket_kem, hb, barcode
                FROM brglog
                ORDER BY kd_brg
                LIMIT 50");
        }

        return response()->json($barang);
    }

    public function getBarangDetail(Request $request)
    {
        $kd_brg = $request->kd_brg;
        $barcode = $request->barcode;
        $cbg = session('cbg', '01');

        if ($barcode) {
            $barang = DB::select("
                SELECT a.kd_brg, a.na_brg, a.sub, a.kdbar, a.ket_uk, a.ket_kem, a.hb, a.barcode,
                       b.ak00 as stok_gudang, b.kdlaku
                FROM brglog a
                LEFT JOIN brglogd b ON a.kd_brg = b.kd_brg AND b.cbg = ?
                WHERE a.barcode = ?", [$cbg, $barcode]);
        } else {
            $barang = DB::select("
                SELECT a.kd_brg, a.na_brg, a.sub, a.kdbar, a.ket_uk, a.ket_kem, a.hb, a.barcode,
                       b.ak00 as stok_gudang, b.kdlaku
                FROM brglog a
                LEFT JOIN brglogd b ON a.kd_brg = b.kd_brg AND b.cbg = ?
                WHERE a.kd_brg = ?", [$cbg, $kd_brg]);
        }

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

    public function loadFromOrder(Request $request)
    {
        $no_po = $request->no_po;

        $items = DB::select("
            SELECT kd_brg, na_brg, ket_kem, qty
            FROM tpolog
            WHERE no_bukti = ? AND bktk = ''
            ORDER BY rec ASC", [$no_po]);

        if (!empty($items)) {
            $cbg = session('cbg', '01');

            foreach ($items as &$item) {
                $stock = DB::select("
                    SELECT ak00 as stok_gudang, kdlaku
                    FROM brglogd
                    WHERE cbg = ? AND kd_brg = ?", [$cbg, $item->kd_brg]);

                if (!empty($stock)) {
                    $item->stok_gudang = $stock[0]->stok_gudang;
                    $item->kdlaku = $stock[0]->kdlaku;
                } else {
                    $item->stok_gudang = 0;
                    $item->kdlaku = '';
                }
            }

            return response()->json([
                'success' => true,
                'data' => $items
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Data order tidak ditemukan'
        ]);
    }

    public function destroy(Request $request)
    {
        $no_bukti = $request->route('lentryrealisasi');

        try {
            $check_posted = DB::select("SELECT posted FROM lgstockb WHERE no_bukti = ?", [$no_bukti]);

            if (!empty($check_posted) && $check_posted[0]->posted == 1) {
                return redirect()->route('lentryrealisasi')->with('error', 'Data sudah terposting, tidak dapat dihapus');
            }

            DB::statement("DELETE FROM lgstockbd WHERE no_bukti = ?", [$no_bukti]);
            DB::statement("DELETE FROM lgstockb WHERE no_bukti = ?", [$no_bukti]);

            return redirect()->route('lentryrealisasi')->with('status', 'Data berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('lentryrealisasi')->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
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

        $check_posted = DB::select("SELECT posted FROM lgstockb WHERE no_bukti = ?", [$no_bukti]);

        // Get toko info
        $toko = DB::select("
            SELECT A.KODE, A.TYP, A.NA_TOKO, A.NA_PERS, A.TYP_PERS, A.TYP_NPWP, A.ALAMAT, B.NO_BUKTI
            FROM toko A, tokoform B
            WHERE A.TYP=B.TYP AND A.KODE=? AND B.KD_PRNT='RETOKOG_LG'", [$cbg]);

        if (!empty($check_posted) && $check_posted[0]->posted == 0) {
            $data = DB::select("
                SELECT :toko as nmtoko, :no_form no_form, :typ_pers typ_pers,
                       a.no_bukti, a.no_id, a.no_po, a.usrnm, a.tg_smp as waktu, a.notes,
                       c.sub, c.kdbar, b.na_brg, c.ket_uk, d.ak00 as stockr, c.ket_kem,
                       concat(d.kdlaku, d.klk) as kd, c.hb, b.qto, b.qty
                FROM lgstockb a, lgstockbd b, brglog c, brglogd d
                WHERE a.no_bukti = b.no_bukti AND b.kd_brg = c.kd_brg AND
                      b.kd_brg = d.kd_brg AND d.cbg = ? AND b.qty > 0 AND
                      a.no_bukti = ?", [
                !empty($toko) ? $toko[0]->na_toko : '',
                !empty($toko) ? $toko[0]->no_bukti : '',
                !empty($toko) ? $toko[0]->typ_pers : '',
                $cbg,
                $no_bukti
            ]);
        } else {
            $data = DB::select("
                SELECT :toko as nmtoko, :no_form no_form, :typ_pers typ_pers,
                       a.no_bukti, a.no_id, a.no_po, a.usrnm, a.tg_smp as waktu, a.notes,
                       c.sub, c.kdbar, b.na_brg, c.ket_uk, d.ak00 as stockr, c.ket_kem,
                       concat(d.kdlaku, d.klk) as kd, c.hb, b.qto, b.qty
                FROM lgstockbz a, lgstockbzd b, brglog c, brglogd d
                WHERE a.no_bukti = b.no_bukti AND b.kd_brg = c.kd_brg AND
                      b.kd_brg = d.kd_brg AND d.cbg = ? AND b.qty > 0 AND
                      a.no_bukti = ?", [
                !empty($toko) ? $toko[0]->na_toko : '',
                !empty($toko) ? $toko[0]->no_bukti : '',
                !empty($toko) ? $toko[0]->typ_pers : '',
                $cbg,
                $no_bukti
            ]);
        }

        return response()->json(['data' => $data]);
    }
}
