<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PHTransaksiTransferHadiahController extends Controller
{
    /**
     * Display index page
     * Matching Delphi: frmTFhdhg.pas - List form
     */
    public function index()
    {
        return view('promo_hadiah_transaksi_transfer_hadiah.index');
    }

    /**
     * Get list for datatable
     * Matching Delphi query: SELECT * FROM HDH where per=:per AND FLAG='FH' order by OT
     */
    public function getData(Request $request)
    {
$per = session('periode', date('m.Y'));

        $periode = $per['bulan'] . '/' . $per['tahun'];
        $query = DB::select(
            "SELECT NO_BUKTI, TGL, OT, NAMAS, TOTAL_QTY, TOTAL, posted, CBG
             FROM hdh
             WHERE per = ? AND FLAG = 'FH'
             ORDER BY OT, NO_BUKTI DESC",
            [$periode]
        );

        return Datatables::of(collect($query))
            ->addIndexColumn()
            ->editColumn('TGL', function ($row) {
                return $row->TGL ? date('d/m/Y', strtotime($row->TGL)) : '';
            })
            ->editColumn('TOTAL_QTY', function ($row) {
                return number_format($row->TOTAL_QTY, 2, ',', '.');
            })
            ->editColumn('TOTAL', function ($row) {
                return number_format($row->TOTAL, 2, ',', '.');
            })
            ->editColumn('posted', function ($row) {
                return $row->posted == 1
                    ? '<span class="badge badge-success">Posted</span>'
                    : '<span class="badge badge-warning">Open</span>';
            })
            ->addColumn('action', function ($row) {
                if ($row->posted == 0) {
                    $btnEdit = '<button onclick="editData(\'' . $row->NO_BUKTI . '\')" class="btn btn-sm btn-primary" title="Edit"><i class="fas fa-edit"></i></button>';
                } else {
                    $btnEdit = '<button class="btn btn-sm btn-secondary" disabled title="Sudah Terposting"><i class="fas fa-lock"></i></button>';
                }
                $btnPrint = '<button onclick="printData(\'' . $row->NO_BUKTI . '\')" class="btn btn-sm btn-info ml-1" title="Print"><i class="fas fa-print"></i></button>';
                return $btnEdit . ' ' . $btnPrint;
            })
            ->rawColumns(['action', 'posted'])
            ->make(true);
    }

    /**
     * Show form for create/edit
     * Matching Delphi: frmTFhdhN.pas - Entry form
     */
    public function edit(Request $request)
    {
        $no_bukti = $request->get('no_bukti');
        $status = $request->get('status', 'simpan');

        // Check period closure - matching Delphi NewClick validation
$per = session('periode', date('m.Y'));

        $periode = $per['bulan'] . '/' . $per['tahun'];
        $data = [
            'no_bukti' => '+',
            'status' => $status,
            'header' => null,
            'detail' => [],
            'periode' => $periode,
            'outlets' => $this->getOutletList()
        ];

        // Edit mode - load existing data
        if ($status == 'edit' && $no_bukti) {
            // Matching: SELECT * FROM HDH where FLAG='FH' AND no_bukti = :no_bukti
            $header = DB::select(
                "SELECT * FROM hdh WHERE FLAG = 'FH' AND no_bukti = ?",
                [$no_bukti]
            );

            if (!empty($header)) {
                $headerData = $header[0];

                // Check if posted
                if ($headerData->posted == 1) {
                    return redirect()->route('phtransaksitransferhadiah')
                        ->with('error', 'Data Sudah Terposting, tidak dapat diedit!');
                }

                // Matching: SELECT * FROM HDHD WHERE NO_BUKTI = :no_bukti ORDER BY REC
                $detail = DB::select(
                    "SELECT NO_BUKTI, REC, KD_BRGH, NA_BRGH, QTY, HARGA, TOTAL, PER, NO_ID
                     FROM HDHD
                     WHERE NO_BUKTI = ?
                     ORDER BY REC",
                    [$no_bukti]
                );

                $data['header'] = $headerData;
                $data['detail'] = $detail;
                $data['no_bukti'] = $no_bukti;
            }
        }

        return view('promo_hadiah_transaksi_transfer_hadiah.edit', $data);
    }

    /**
     * Store/Update data
     * Matching Delphi: MSaveClick procedure in frmTFhdhN.pas
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'tgl' => 'required|date',
            'ot' => 'required',
            'details' => 'required|array|min:1'
        ]);

        DB::beginTransaction();

        try {
            $no_bukti = trim($request->no_bukti);
            $status = $request->status;
    $per = session('periode', date('m.Y'));

        $periode = $per['bulan'] . '/' . $per['tahun'];            $username = Auth::user()->username ?? 'system';
            $cbg = session('cbg', '01');

            // Check if period is closed - matching Delphi check
            $check_period = DB::select("SELECT posted FROM perid WHERE kd_peri = ?", [$periode]);
            if (!empty($check_period) && $check_period[0]->posted == 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Closed Period'
                ], 400);
            }

            // Validate date vs periode - matching checkx procedure
            $tgl = Carbon::parse($request->tgl);
            $monthz = str_pad($tgl->month, 2, '0', STR_PAD_LEFT);
            $yearz = $tgl->year;

            $periode_month = substr($periode, 0, 2);
            $periode_year = substr($periode, -4);

            if ($monthz != $periode_month) {
                return response()->json([
                    'success' => false,
                    'message' => 'Month is not the same as Periode.'
                ], 400);
            }

            if ($yearz != $periode_year) {
                return response()->json([
                    'success' => false,
                    'message' => 'Year is not the same as Periode.'
                ], 400);
            }

            // Validate outlet - matching checkx
            if (empty($request->ot)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Outlet tujuan tidak boleh kosong'
                ], 400);
            }

            // Validate total
            if (floatval($request->total ?? 0) <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak boleh kosong.'
                ], 400);
            }

            if ($status == 'simpan') {
                // Generate no_bukti - matching Delphi logic
                if ($no_bukti == '+') {
                    $no_bukti = $this->generateNoBukti($periode, $cbg);
                }

                // INSERT INTO HDH - matching Delphi insert statement
                DB::statement(
                    "INSERT INTO HDH (NO_BUKTI, TGL, CBG, OT, TOTAL_QTY, TOTAL, FLAG, USRNM, PER, TG_SMP, notes)
                     VALUES (?, ?, ?, ?, ?, ?, 'FH', ?, ?, NOW(), ?)",
                    [
                        $no_bukti,
                        $request->tgl,
                        $cbg,
                        trim($request->ot),
                        floatval($request->total_qty ?? 0),
                        floatval($request->total ?? 0),
                        $username,
                        $periode,
                        trim($request->notes ?? '')
                    ]
                );
            } else {
                // Edit mode - call THDHDEL procedure first
                DB::statement("CALL THDHDEL(?)", [$no_bukti]);

                // UPDATE HDH - matching Delphi update statement
                DB::statement(
                    "UPDATE HDH
                     SET TGL = ?, TOTAL_QTY = ?, TOTAL = ?, USRNM = ?, TG_SMP = NOW(), notes = ?
                     WHERE NO_BUKTI = ?",
                    [
                        $request->tgl,
                        floatval($request->total_qty ?? 0),
                        floatval($request->total ?? 0),
                        $username,
                        trim($request->notes ?? ''),
                        $no_bukti
                    ]
                );
            }

            // Get header ID
            $header_id_result = DB::select("SELECT no_id FROM HDH WHERE no_bukti = ?", [$no_bukti]);
            $id = $header_id_result[0]->no_id ?? 0;

            // Insert detail records - matching Delphi insert logic
            $rec = 1;
            foreach ($request->details as $detail) {
                if (!empty($detail['kd_brgh'])) {
                    // INSERT INTO HDHD
                    DB::statement(
                        "INSERT INTO HDHD (NO_BUKTI, REC, PER, FLAG, KD_BRGH, NA_BRGH, QTY, HARGA, TOTAL, ID)
                         VALUES (?, ?, ?, 'FH', ?, ?, ?, ?, ?, ?)",
                        [
                            $no_bukti,
                            $rec,
                            $periode,
                            trim($detail['kd_brgh']),
                            trim($detail['na_brgh'] ?? ''),
                            floatval($detail['qty'] ?? 0),
                            floatval($detail['harga'] ?? 0),
                            floatval($detail['total'] ?? 0),
                            $id
                        ]
                    );

                    // Update stock: gke = gke + :qty, gak = gaw + gma - gke + gln
                    // Matching Delphi: update brghd set gke = gke + :qty, gak = gaw + gma - gke + gln
                    DB::statement(
                        "UPDATE brghd
                         SET gke = gke + ?, gak = gaw + gma - gke + gln
                         WHERE kd_brgh = ?",
                        [
                            floatval($detail['qty'] ?? 0),
                            trim($detail['kd_brgh'])
                        ]
                    );

                    $rec++;
                }
            }

            // Call THDHINS procedure - matching Delphi final step
            DB::statement("CALL THDHINS(?)", [$no_bukti]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Save Data Success',
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
     * Browse product data
     * Matching Delphi: select brgh.KD_BRGH,brgh.NA_BRGH from Brgh
     */
    public function browse(Request $request)
    {
        $q = $request->get('q', '');

        if (!empty($q)) {
            $data = DB::select(
                "SELECT kd_brgh, na_brgh
                 FROM brgh
                 WHERE kd_brgh LIKE ? OR na_brgh LIKE ?
                 ORDER BY kd_brgh
                 LIMIT 50",
                ["%$q%", "%$q%"]
            );
        } else {
            $data = DB::select(
                "SELECT kd_brgh, na_brgh
                 FROM brgh
                 ORDER BY kd_brgh
                 LIMIT 50"
            );
        }

        return response()->json($data);
    }

    /**
     * Get product detail
     * Matching Delphi: select brgh.KD_BRGH,brgh.NA_BRGH from Brgh where brgh.kd_brgh=:brgFC
     */
    public function getDetail(Request $request)
    {
        $kd_brgh = $request->get('kd_brgh');

        $product = DB::select(
            "SELECT kd_brgh, na_brgh FROM brgh WHERE kd_brgh = ? LIMIT 1",
            [$kd_brgh]
        );

        if (!empty($product)) {
            return response()->json([
                'success' => true,
                'exists' => true,
                'data' => $product[0]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Produk tidak ditemukan'
        ]);
    }

    /**
     * Print report
     * Matching Delphi Print1Click:
     * SELECT HDH.NAMAS,HDH.NO_BUKTI,HDHd.KD_BRGH AS SUBITEM, :OT AS cbg,HDH.TGL,
     * HDHd.NA_BRGH, HDHd.qty, HDHd.harga, HDHd.total
     * FROM HDH,HDHd WHERE HDH.NO_BUKTI=HDHd.no_bukti and HDH.no_bukti=:XD
     */
    public function printTransaksiTransferHadiah(Request $request)
    {
        $no_bukti = $request->no_bukti;

        // Get OT value from header
        $header = DB::select("SELECT OT FROM hdh WHERE no_bukti = ?", [$no_bukti]);

        if (empty($header)) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }

        $ot = $header[0]->OT;

        // Determine outlet name based on OT code - matching Delphi logic
        $cbg = '';
        if ($ot == 'SOP') {
            $cbg = 'TIARA SOPUTAN';
        } elseif ($ot == 'TMM') {
            $cbg = 'TIARA MONANG-MANING';
        } elseif ($ot == 'TGZ') {
            $cbg = 'TIARA GATZU';
        }

        // Get print data - matching Delphi query
        $data = DB::select(
            "SELECT HDH.NAMAS, HDH.NO_BUKTI, HDHd.KD_BRGH AS SUBITEM,
                    ? AS cbg, HDH.TGL, HDHd.NA_BRGH, HDHd.qty, HDHd.harga, HDHd.total
             FROM HDH, HDHd
             WHERE HDH.NO_BUKTI = HDHd.no_bukti
               AND HDH.no_bukti = ?",
            [$cbg, $no_bukti]
        );

        return response()->json([
            'data' => $data
        ]);
    }

    /**
     * Generate no_bukti
     * Matching Delphi: FH + year(2) + month(2) + '-' + sequence + kode2
     */
    private function generateNoBukti($periode, $cbg)
    {
        $monthString = substr($periode, 0, 2);
        $year = substr($periode, -4);

        // Get toko type
        try {
            $toko = DB::select("SELECT type FROM toko WHERE kode = ?", [$cbg]);
        } catch (\Exception $e) {
            $toko = [];
        }
        $kode2 = $toko[0]->type ?? '';

        $kode = 'FH' . substr($year, -2) . $monthString;

        // Get next number from notrans
        $notrans = DB::select(
            "SELECT NOM{$monthString} as NO_BUKTI FROM notrans WHERE trans = 'TFHDH' AND PER = ?",
            [$year]
        );
        $r1 = ($notrans[0]->NO_BUKTI ?? 0) + 1;

        // Update counter
        DB::statement(
            "UPDATE notrans SET NOM{$monthString} = ? WHERE trans = 'TFHDH' AND PER = ?",
            [$r1, $year]
        );

        $bkt1 = str_pad($r1, 4, '0', STR_PAD_LEFT);
        return $kode . '-' . $bkt1 . $kode2;
    }

    /**
     * Get outlet list for dropdown
     */
    private function getOutletList()
    {
        try {
            return DB::select("SELECT kode, na_toko FROM toko ORDER BY kode");
        } catch (\Exception $e) {
            return [];
        }
    }
}