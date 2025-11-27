<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PHTerimaHadiahSupplierController extends Controller
{
    /**
     * Display index page
     */
    public function index()
    {
        return view('promo_hadiah_terima_hadiah_supplier.index');
    }

    /**
     * Get list for datatable
     * Matching: SELECT * FROM HDH where per=:per AND FLAG='HM' order by NO_BUKTI
     */
    public function getData(Request $request)
    {
$per = session('periode', date('m.Y'));

        $periode = $per['bulan'] . '/' . $per['tahun'];
        $query = DB::select(
            "SELECT NO_BUKTI, TGL, kodes, namas, TOTAL_QTY, TOTAL, posted
             FROM hdh
             WHERE per = ? AND FLAG = 'HM'
             ORDER BY NO_BUKTI DESC",
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
     */
    public function edit(Request $request)
    {
        $no_bukti = $request->get('no_bukti');
        $status = $request->get('status', 'simpan');

        $data = [
            'no_bukti' => '+',
            'status' => $status,
            'header' => null,
            'detail' => [],
            'periode' => session('periode', date('m.Y'))
        ];

        // Edit mode - load existing data
        if ($status == 'edit' && $no_bukti) {
            // Get header: SELECT * FROM HDH where FLAG='HM' AND no_bukti = :no_bukti
            $header = DB::select(
                "SELECT * FROM hdh WHERE FLAG = 'HM' AND no_bukti = ?",
                [$no_bukti]
            );

            if (!empty($header)) {
                $headerData = $header[0];

                // Check if posted
                if ($headerData->posted == 1) {
                    return redirect()->route('phterimahadiahsupplier')
                        ->with('error', 'Data Sudah Terposting, tidak dapat diedit!');
                }

                // Get detail: SELECT * FROM HDHD WHERE NO_BUKTI = :no_bukti ORDER BY REC
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

        return view('promo_hadiah_terima_hadiah_supplier.edit', $data);
    }

    /**
     * Store/Update data
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'tgl' => 'required|date',
            'kodes' => 'required',
            'details' => 'required|array|min:1'
        ]);

        DB::beginTransaction();

        try {
            $no_bukti = trim($request->no_bukti);
            $status = $request->status;
    $per = session('periode', date('m.Y'));

        $periode = $per['bulan'] . '/' . $per['tahun'];            $username = Auth::user()->username ?? 'system';

            // Check if period is closed
            $check_period = DB::select("SELECT posted FROM perid WHERE kd_peri = ?", [$periode]);
            if (!empty($check_period) && $check_period[0]->posted == 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Closed Period'
                ], 400);
            }

            // Validate date vs periode
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

            // Validate supplier
            if (empty($request->kodes)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Supplier is Empty.'
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
                // Generate no_bukti
                if ($no_bukti == '+') {
                    $no_bukti = $this->generateNoBukti($periode);
                }

                // Insert header
                DB::statement(
                    "INSERT INTO HDH (NO_BUKTI, TGL, KODES, NAMAS, TOTAL_QTY, TOTAL, FLAG, USRNM, PER, TG_SMP, notes, CBG)
                     VALUES (?, ?, ?, ?, ?, ?, 'HM', ?, ?, NOW(), ?, ?)",
                    [
                        $no_bukti,
                        $request->tgl,
                        trim($request->kodes),
                        trim($request->namas ?? ''),
                        floatval($request->total_qty ?? 0),
                        floatval($request->total ?? 0),
                        $username,
                        $periode,
                        trim($request->notes ?? ''),
                        session('cbg', '01')
                    ]
                );
            } else {
                // Call HDHDEL procedure
                DB::statement("CALL HDHDEL(?)", [$no_bukti]);

                // Update header
                DB::statement(
                    "UPDATE HDH
                     SET TGL = ?, KODES = ?, NAMAS = ?, TOTAL_QTY = ?, TOTAL = ?, USRNM = ?, TG_SMP = NOW(), notes = ?
                     WHERE NO_BUKTI = ?",
                    [
                        $request->tgl,
                        trim($request->kodes),
                        trim($request->namas ?? ''),
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

            // Handle detail updates (matching Delphi logic)
            if ($status == 'edit') {
                $existing_details = DB::select("SELECT no_id FROM HDHD WHERE no_bukti = ?", [$no_bukti]);

                foreach ($existing_details as $existing) {
                    $found = false;
                    foreach ($request->details as $detail) {
                        if (isset($detail['no_id']) && $detail['no_id'] == $existing->no_id) {
                            // Update existing record
                            DB::statement(
                                "UPDATE HDHD
                                 SET REC = ?, KD_BRGH = ?, NA_BRGH = ?, QTY = ?, HARGA = ?, TOTAL = ?
                                 WHERE NO_ID = ?",
                                [
                                    intval($detail['rec'] ?? 1),
                                    trim($detail['kd_brgh'] ?? ''),
                                    trim($detail['na_brgh'] ?? ''),
                                    floatval($detail['qty'] ?? 0),
                                    floatval($detail['harga'] ?? 0),
                                    floatval($detail['total'] ?? 0),
                                    $existing->no_id
                                ]
                            );
                            $found = true;
                            break;
                        }
                    }

                    if (!$found) {
                        // Delete record not found in new details
                        DB::statement("DELETE FROM HDHD WHERE NO_ID = ?", [$existing->no_id]);
                    }
                }
            }

            // Insert new detail records
            $rec = 1;
            foreach ($request->details as $detail) {
                if (!empty($detail['kd_brgh'])) {
                    if (!isset($detail['no_id']) || $detail['no_id'] == 0) {
                        DB::statement(
                            "INSERT INTO HDHD (NO_BUKTI, REC, PER, FLAG, KD_BRGH, NA_BRGH, QTY, HARGA, TOTAL, ID)
                             VALUES (?, ?, ?, 'HM', ?, ?, ?, ?, ?, ?)",
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

                        // Update stock: gma = gma + :qty, gak = gaw + gma - gke + gln
                        DB::statement(
                            "UPDATE brghd
                             SET gma = gma + ?, gak = gaw + gma - gke + gln
                             WHERE kd_brgh = ?",
                            [
                                floatval($detail['qty'] ?? 0),
                                trim($detail['kd_brgh'])
                            ]
                        );
                    }
                    $rec++;
                }
            }

            // Call HDHINS procedure
            DB::statement("CALL HDHINS(?)", [$no_bukti]);

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
     * Browse supplier data
     */
    public function browse(Request $request)
    {
        $q = $request->get('q', '');
        $type = $request->get('type', 'supplier'); // supplier or product

        if ($type == 'supplier') {
            if (!empty($q)) {
                $data = DB::select(
                    "SELECT kodes, namas
                     FROM sup
                     WHERE kodes LIKE ? OR namas LIKE ?
                     ORDER BY kodes
                     LIMIT 50",
                    ["%$q%", "%$q%"]
                );
            } else {
                $data = DB::select(
                    "SELECT kodes, namas
                     FROM sup
                     ORDER BY kodes
                     LIMIT 50"
                );
            }
        } else {
            // Product browse
            if (!empty($q)) {
                $data = DB::select(
                    "SELECT kd_brgH as kd_brgh, na_brgH as na_brgh
                     FROM BrgH
                     WHERE kd_brgH LIKE ? OR na_brgH LIKE ?
                     ORDER BY kd_brgH
                     LIMIT 50",
                    ["%$q%", "%$q%"]
                );
            } else {
                $data = DB::select(
                    "SELECT kd_brgH as kd_brgh, na_brgH as na_brgh
                     FROM BrgH
                     ORDER BY kd_brgH
                     LIMIT 50"
                );
            }
        }

        return response()->json($data);
    }

    /**
     * Get detail (supplier or product)
     */
    public function getDetail(Request $request)
    {
        $type = $request->get('type', 'supplier');

        if ($type == 'supplier') {
            $kodes = $request->get('kodes');
            $supplier = DB::select(
                "SELECT kodes, namas FROM sup WHERE kodes = ? LIMIT 1",
                [$kodes]
            );

            if (!empty($supplier)) {
                return response()->json([
                    'success' => true,
                    'exists' => true,
                    'data' => $supplier[0]
                ]);
            }
        } else {
            $kd_brgh = $request->get('kd_brgh');
            $product = DB::select(
                "SELECT kd_brgH as kd_brgh, na_brgH as na_brgh FROM BrgH WHERE kd_brgH = ? LIMIT 1",
                [$kd_brgh]
            );

            if (!empty($product)) {
                return response()->json([
                    'success' => true,
                    'exists' => true,
                    'data' => $product[0]
                ]);
            }
        }

        return response()->json([
            'success' => false,
            'message' => ($type == 'supplier' ? 'Supplier' : 'Produk') . ' tidak ditemukan'
        ]);
    }

    /**
     * Print report
     * Matching: SELECT hdh.KODES, hdh.NAMAS, hdhd.KD_BRGH, hdhd.NA_BRGH, hdhd.qty, hdhd.harga, hdhd.total
     */
    public function printTerimaHadiahSupplier(Request $request)
    {
        $no_bukti = $request->no_bukti;

        $data = DB::select(
            "SELECT hdh.KODES, hdh.NAMAS, hdhd.KD_BRGH, hdhd.NA_BRGH, hdhd.qty, hdhd.harga, hdhd.total
             FROM HDH, hdhd
             WHERE hdhd.no_bukti = hdh.NO_BUKTI
               AND hdh.NO_BUKTI = ?
             ORDER BY hdhd.rec ASC",
            [$no_bukti]
        );

        return response()->json([
            'data' => $data
        ]);
    }

    /**
     * Generate no_bukti
     * Matching Delphi logic: HM + year(2) + month(2) + '-' + sequence + type
     */
    private function generateNoBukti($periode)
    {
        $monthString = substr($periode, 0, 2);
        $year = substr($periode, -4);

        $kode = 'HM' . substr($year, -2) . $monthString;

        // Get next number from notrans
        $notrans = DB::select(
            "SELECT NOM{$monthString} as NO_BUKTI FROM notrans WHERE trans = 'HDHM' AND PER = ?",
            [$year]
        );
        $r1 = ($notrans[0]->NO_BUKTI ?? 0) + 1;

        // Update counter
        DB::statement(
            "UPDATE notrans SET NOM{$monthString} = ? WHERE trans = 'HDHM' AND PER = ?",
            [$r1, $year]
        );

        $bkt1 = str_pad($r1, 4, '0', STR_PAD_LEFT);
        return $kode . '-' . $bkt1;
    }
}
