<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LOrderLogistikController extends Controller
{
    /**
     * Display listing of order logistik (equivalent to frmlgtogdg)
     */
    public function index()
    {
        return view('logistik_order_logistik.index');
    }

    /**
     * Get data for index page (equivalent to Tampil procedure in Delphi)
     */
    public function getOrderLogistik(Request $request)
    {
        $periode = session('periode', date('m') . '/' . date('Y'));

        // Main query - equivalent to com query in Delphi Tampil procedure
        $query = DB::table('TPOLOG')
            ->select('no_bukti', 'tgl', 'ket', DB::raw('sum(qty) as total_qty'))
            ->where('per', $periode)
            ->where('flag', 'LG')
            ->groupBy('no_bukti', 'tgl', 'ket')
            ->orderBy('no_bukti');

        return Datatables::of($query)
            ->addIndexColumn()
            ->editColumn('tgl', function ($row) {
                return date('d/m/Y', strtotime($row->tgl));
            })
            ->editColumn('total_qty', function ($row) {
                return number_format($row->total_qty, 0);
            })
            ->addColumn('action', function ($row) {
                $btnEdit = '<a href="' . route('lorderlogistik.edit', ['no_bukti' => $row->no_bukti]) . '" class="btn btn-sm btn-primary">Edit</a>';
                $btnPrint = '<button onclick="printOrder(\'' . $row->no_bukti . '\')" class="btn btn-sm btn-info ml-1">Print</button>';
                return $btnEdit . $btnPrint;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Show form for creating new order or editing existing (equivalent to frmlgtogdn)
     */
    public function edit(Request $request)
    {
        $no_bukti = $request->get('no_bukti');
        $tipx = $request->get('tipx', 'edit');
        $idx = $request->get('idx', 0);

        // Navigation logic (equivalent to Delphi edit navigation)
        if ($tipx == 'search' && $request->has('kodex')) {
            $kodex = $request->kodex;
            $result = DB::select("SELECT no_bukti FROM TPOLOG WHERE no_bukti = ? AND flag = 'LG' LIMIT 1", [$kodex]);
            $no_bukti = !empty($result) ? $result[0]->no_bukti : '';
        }

        if ($tipx == 'top') {
            $result = DB::select("SELECT no_bukti FROM TPOLOG WHERE flag = 'LG' ORDER BY no_bukti ASC LIMIT 1");
            $no_bukti = !empty($result) ? $result[0]->no_bukti : '';
        }

        if ($tipx == 'bottom') {
            $result = DB::select("SELECT no_bukti FROM TPOLOG WHERE flag = 'LG' ORDER BY no_bukti DESC LIMIT 1");
            $no_bukti = !empty($result) ? $result[0]->no_bukti : '';
        }

        if ($tipx == 'prev' && $request->has('kodex')) {
            $kodex = $request->kodex;
            $result = DB::select("SELECT no_bukti FROM TPOLOG WHERE no_bukti < ? AND flag = 'LG' ORDER BY no_bukti DESC LIMIT 1", [$kodex]);
            $no_bukti = !empty($result) ? $result[0]->no_bukti : $kodex;
        }

        if ($tipx == 'next' && $request->has('kodex')) {
            $kodex = $request->kodex;
            $result = DB::select("SELECT no_bukti FROM TPOLOG WHERE no_bukti > ? AND flag = 'LG' ORDER BY no_bukti ASC LIMIT 1", [$kodex]);
            $no_bukti = !empty($result) ? $result[0]->no_bukti : $kodex;
        }

        $header = null;
        $details = [];

        if ($no_bukti) {
            // Get header data - equivalent to loading existing data in Delphi
            $headerResult = DB::select("
                SELECT no_bukti, tgl, ket, sum(qty) as total_qty, divisi
                FROM TPOLOG
                WHERE no_bukti = ? AND flag = 'LG'
                GROUP BY no_bukti, tgl, ket, divisi", [$no_bukti]);

            $header = !empty($headerResult) ? $headerResult[0] : null;

            // Get detail data - equivalent to loading detail grid in Delphi
            $details = DB::select("
                SELECT NO_ID, REC, qty, KD_BRG, NA_BRG, KET, ket_kem, kdlaku, sub, kdbar
                FROM TPOLOG
                WHERE NO_BUKTI = ? AND flag = 'LG'
                ORDER BY REC", [$no_bukti]);
        }

        $data = [
            'header' => $header,
            'details' => $details,
            'no_bukti' => $no_bukti,
            'tipx' => $tipx,
            'idx' => $idx
        ];

        return view('logistik_order_logistik.edit', $data);
    }

    /**
     * Store new order or update existing (equivalent to MSaveClick in Delphi)
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'tgl' => 'required|date',
            'ket' => 'required',
            'details' => 'required|array|min:1'
        ]);

        DB::beginTransaction();

        try {
            $periode = session('periode', date('m') . '/' . date('Y'));
            $cbg = session('cbg', Auth::user()->cbg ?? '');
            $username = Auth::user()->name ?? '';

            $no_bukti = $request->no_bukti;
            $isNew = ($no_bukti == '+' || empty($no_bukti));

            // Generate new document number if needed (equivalent to Delphi auto-numbering)
            if ($isNew) {
                $no_bukti = $this->generateNoBukti($periode, $cbg);
            }

            // Validation equivalent to checkx procedure in Delphi
            $this->validatePeriode($request->tgl, $periode);

            // Get existing details for comparison
            $existingDetails = [];
            if (!$isNew) {
                $existing = DB::select("SELECT no_id FROM TPOLOG WHERE flag = 'LG' AND no_bukti = ?", [$no_bukti]);
                foreach ($existing as $row) {
                    $existingDetails[] = $row->no_id;
                }
            }

            $processedIds = [];

            // Process each detail line (equivalent to Delphi detail processing loop)
            foreach ($request->details as $index => $detail) {
                if (empty($detail['kd_brg'])) continue;

                $rec = $index + 1;
                $no_id = $detail['no_id'] ?? 0;

                if ($no_id > 0 && in_array($no_id, $existingDetails)) {
                    // Update existing record
                    DB::statement("
                        UPDATE TPOLOG SET
                            REC = ?, TGL = ?, KD_BRG = ?, NA_BRG = ?, QTY = ?, KET = ?,
                            kdlaku = ?, sub = ?, kdbar = ?, divisi = ?, tg_smp = NOW(),
                            ket_kem = ?, usrnm = ?
                        WHERE NO_ID = ?", [
                        $rec,
                        $request->tgl,
                        trim($detail['kd_brg']),
                        trim($detail['na_brg']),
                        $detail['qty'],
                        $request->ket,
                        $detail['klaku'] ?? '',
                        $detail['sub'] ?? '',
                        $detail['kdbar'] ?? '',
                        $request->divisi ?? '',
                        $detail['kemasan'] ?? '',
                        $username,
                        $no_id
                    ]);
                    $processedIds[] = $no_id;
                } else {
                    // Insert new record
                    DB::statement("
                        INSERT INTO TPOLOG (
                            NO_BUKTI, REC, PER, FLAG, KD_BRG, NA_BRG, QTY, TGL, KET, tg_smp,
                            kdlaku, sub, kdbar, ket_kem, usrnm, cbg, divisi
                        ) VALUES (?, ?, ?, 'LG', ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?)", [
                        $no_bukti,
                        $rec,
                        $periode,
                        trim($detail['kd_brg']),
                        trim($detail['na_brg']),
                        $detail['qty'],
                        $request->tgl,
                        $request->ket,
                        $detail['klaku'] ?? '',
                        $detail['sub'] ?? '',
                        $detail['kdbar'] ?? '',
                        $detail['kemasan'] ?? '',
                        $username,
                        $cbg,
                        $request->divisi ?? ''
                    ]);
                }
            }

            // Delete records that are no longer in the detail list (equivalent to Delphi delete logic)
            if (!$isNew) {
                $toDelete = array_diff($existingDetails, $processedIds);
                foreach ($toDelete as $deleteId) {
                    DB::statement("DELETE FROM TPOLOG WHERE NO_ID = ?", [$deleteId]);
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

    /**
     * Print order logistik (equivalent to Print1Click in Delphi)
     */
    public function printOrder(Request $request)
    {
        $no_bukti = $request->no_bukti;
        $cbg = session('cbg', Auth::user()->cbg ?? '');

        // Query equivalent to halo query in Delphi Print1Click
        $data = DB::select("
            SELECT TIME(TPOLOG.tg_smp) as timo, TPOLOG.tgl, TPOLOG.no_bukti, TPOLOG.sub,
                   brgLOGD.AK00 as STOCKR_TK, TPOLOG.Kdbar, brgLOG.na_brg, TPOLOG.KET_KEM,
                   brglog.KET_UK, tpolog.usrnm, CONCAT(brgLOGD.kdlaku, brgLOGD.KLK) as KD,
                   brgLOGD.srmax as SMAX_TK, brgLOGD.DTR, TPOLOG.qty, TPOLOG.ket, TPolog.divisi
            FROM TPOLOG, BRGLOG, brgLOGD
            WHERE TPOLOG.KD_BRG = brgLOG.KD_BRG
              AND TPOLOG.KD_BRG = brgLOGD.KD_BRG
              AND brgLOGD.cbg = ?
              AND TPOLOG.no_bukti = ?
            ORDER BY TPOLOG.no_bukti", [$cbg, $no_bukti]);

        // Update print flag - equivalent to com01 update in Delphi
        DB::statement("UPDATE TPOLOG SET prnt = 1 WHERE no_bukti = ?", [$no_bukti]);

        return response()->json(['data' => $data]);
    }

    /**
     * Browse barang logistik (equivalent to barang lookup in Delphi)
     */
    public function browse(Request $request)
    {
        $q = $request->get('q', '');
        $cbg = session('cbg', Auth::user()->cbg ?? '');

        if (!empty($q)) {
            $barang = DB::select("
                SELECT brglog.kd_brg, CONCAT(brglog.na_brg, ' ', brglog.ket_uk) as na_brg,
                       brglog.ket_kem, brglog.sub, brglog.kdbar, brglogd.kdlaku,
                       brglogd.tk, brglogd.ak00
                FROM BRGlog, brglogd
                WHERE brglogd.kd_brg = brglog.kd_brg
                  AND brglogd.CBG = ?
                  AND (brglog.na_brg LIKE ? OR brglog.kd_brg LIKE ?)
                ORDER BY brglog.kd_brg
                LIMIT 50", [$cbg, "%$q%", "%$q%"]);
        } else {
            $barang = DB::select("
                SELECT brglog.kd_brg, CONCAT(brglog.na_brg, ' ', brglog.ket_uk) as na_brg,
                       brglog.ket_kem, brglog.sub, brglog.kdbar, brglogd.kdlaku,
                       brglogd.tk, brglogd.ak00
                FROM BRGlog, brglogd
                WHERE brglogd.kd_brg = brglog.kd_brg
                  AND brglogd.CBG = ?
                ORDER BY brglog.kd_brg
                LIMIT 50", [$cbg]);
        }

        return response()->json($barang);
    }

    /**
     * Get barang detail by code (equivalent to Delphi barang validation)
     */
    public function getBarangDetail(Request $request)
    {
        $kd_brg = $request->kd_brg;
        $cbg = session('cbg', Auth::user()->cbg ?? '');

        $barang = DB::select("
            SELECT brglog.kd_brg, CONCAT(brglog.na_brg, ' ', brglog.ket_uk) as na_brg,
                   brglog.ket_kem, brglog.sub, brglog.kdbar, brglogd.kdlaku,
                   brglogd.tk, brglogd.ak00
            FROM BRGlog, brglogd
            WHERE brglogd.kd_brg = brglog.kd_brg
              AND brglogd.CBG = ?
              AND brglog.KD_BRG = ?", [$cbg, $kd_brg]);

        if (!empty($barang)) {
            $item = $barang[0];

            // Validation equivalent to Delphi validation
            if ($item->kdlaku == '4') {
                return response()->json([
                    'success' => false,
                    'message' => 'Barang Kode 4 tidak bisa dipesan ke gudang!'
                ]);
            }

            if ($item->ak00 <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stok Gudang Masih Belum Tersedia!'
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => $item
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Barang tidak ditemukan'
        ]);
    }

    /**
     * Delete order logistik
     */
    public function destroy(Request $request)
    {
        $no_bukti = $request->route('lorderlogistik');

        try {
            DB::statement("DELETE FROM TPOLOG WHERE no_bukti = ? AND flag = 'LG'", [$no_bukti]);
            return redirect()->route('lorderlogistik')->with('status', 'Data berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('lorderlogistik')->with('error', 'Gagal menghapus data');
        }
    }

    /**
     * Generate new document number (equivalent to Delphi auto-numbering)
     */
    private function generateNoBukti($periode, $cbg)
    {
        $month = substr($periode, 0, 2);
        $year = substr($periode, -4);

        // Get toko type
        $toko = DB::select("SELECT type FROM toko WHERE kode = ?", [$cbg]);
        $kode2 = !empty($toko) ? $toko[0]->type : '';

        $kode = 'LG' . substr($year, -2) . $month;

        // Get and update counter
        $counter = DB::select("SELECT NOM{$month} as NO_BUKTI FROM notrans WHERE trans = 'ORDERLOG' AND PER = ?", [$year]);
        $nextNo = 1;

        if (!empty($counter)) {
            $nextNo = $counter[0]->NO_BUKTI + 1;
        }

        DB::statement("UPDATE notrans SET NOM{$month} = ? WHERE trans = 'ORDERLOG' AND PER = ?", [$nextNo, $year]);

        $nomorUrut = str_pad($nextNo, 4, '0', STR_PAD_LEFT);

        return $kode . '-' . $nomorUrut . $kode2;
    }

    /**
     * Validate periode (equivalent to checkx procedure in Delphi)
     */
    private function validatePeriode($tgl, $periode)
    {
        $tglCarbon = Carbon::parse($tgl);
        $monthTgl = str_pad($tglCarbon->month, 2, '0', STR_PAD_LEFT);
        $yearTgl = $tglCarbon->year;

        $monthPeriode = substr($periode, 0, 2);
        $yearPeriode = substr($periode, -4);

        if ($monthTgl != $monthPeriode) {
            throw new \Exception('Month is not the same as Periode.');
        }

        if ($yearTgl != $yearPeriode) {
            throw new \Exception('Year is not the same as Periode.');
        }
    }

    /**
     * Check if document exists
     */
    public function cekOrder(Request $request)
    {
        $no_bukti = $request->no_bukti;
        $result = DB::select("SELECT COUNT(*) as ADA FROM TPOLOG WHERE no_bukti = ? AND flag = 'LG'", [$no_bukti]);

        return response()->json(['exists' => $result[0]->ADA > 0]);
    }
}
