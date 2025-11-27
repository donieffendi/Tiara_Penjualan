<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PHPenukaranHadiahController extends Controller
{
    /**
     * Display index page for Penukaran Hadiah
     */
    public function index()
    {
        return view('promo_hadiah_penukaran_hadiah.index');
    }

    /**
     * Get list of Penukaran Hadiah for datatable
     * Query matching Delphi: SELECT * FROM hdh where per=:per AND FLAG='HK' AND CBG=:cbg order by NO_BUKTI
     */
    public function getData(Request $request)
    {
$per = session('periode', date('m.Y'));

        $periode = $per['bulan'] . '/' . $per['tahun'];
        $cbg = session('cbg', '01');
        $query = DB::select(
            "SELECT NO_BUKTI, TGL, NO_PO, kodes, namas, TOTAL_QTY, posted
             FROM hdh
             WHERE per = ? AND FLAG = 'HK' AND CBG = ?
             ORDER BY NO_BUKTI DESC",
            [$periode, $cbg]
        );

        return Datatables::of(collect($query))
            ->addIndexColumn()
            ->editColumn('TGL', function ($row) {
                return $row->TGL ? date('d/m/Y', strtotime($row->TGL)) : '';
            })
            ->editColumn('posted', function ($row) {
                return $row->posted == 1 ? '<span class="badge badge-success">Posted</span>' : '<span class="badge badge-warning">Open</span>';
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
     * Show form for create/edit Penukaran Hadiah
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
            'periode' => session('periode', date('m.Y')),
            'cbg' => session('cbg', '01')
        ];

        if ($status == 'edit' && $no_bukti) {
            // Get header data - matching Delphi query
            $header = DB::select(
                "SELECT * FROM hdh WHERE FLAG = 'HK' AND no_bukti = ? ORDER BY no_bukti",
                [$no_bukti]
            );

            if (!empty($header)) {
                $headerData = $header[0];

                // Check if posted
                if ($headerData->posted == 1) {
                    return redirect()->route('phpenukaranhadiah')->with('error', 'Data Sudah Terposting !!');
                }

                // Get detail data - matching Delphi query
                $detail = DB::select(
                    "SELECT NO_BUKTI, REC, KD_BRGH, NA_BRGH, QTY, PER, NO_ID
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

        return view('promo_hadiah_penukaran_hadiah.edit', $data);
    }

    /**
     * Store/Update Penukaran Hadiah
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'tgl' => 'required|date',
            'no_resi' => 'required',
            'details' => 'required|array|min:1'
        ]);

        DB::beginTransaction();

        try {
            $no_bukti = trim($request->no_bukti);
            $status = $request->status;
            $per = session('periode', date('m.Y'));

        $periode = $per['bulan'] . '/' . $per['tahun'];       
            $cbg = session('cbg', '01');
            $username = Auth::user()->username ?? 'system';

            // Check if period is closed - matching Delphi check
            $check_period = DB::select("SELECT posted FROM perid WHERE kd_peri = ?", [$periode]);
            if (!empty($check_period) && $check_period[0]->posted == 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Closed Period'
                ], 400);
            }

            // Validate dates match periode
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

            // Check if NO_PO already used (matching Delphi validation)
            if ($status == 'simpan') {
                $check_resi = DB::select("SELECT NO_PO FROM hdh WHERE NO_PO = ?", [$request->no_resi]);
                if (!empty($check_resi)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Nota ini sudah di entri!'
                    ], 400);
                }
            }

            if ($status == 'simpan') {
                // Generate no_bukti
                if ($no_bukti == '+') {
                    $no_bukti = $this->generateNoBukti($periode, $cbg);
                }

                // Insert header - matching Delphi structure
                DB::statement(
                    "INSERT INTO HDH (NO_PO, CBG, NO_BUKTI, TGL, kodes, namas, TOTAL_QTY, FLAG, USRNM, PER, TG_SMP)
                     VALUES (?, ?, ?, ?, ?, ?, ?, 'HK', ?, ?, NOW())",
                    [
                        trim($request->no_resi),
                        $cbg,
                        $no_bukti,
                        $request->tgl,
                        trim($request->kodec ?? ''),
                        trim($request->namac ?? ''),
                        floatval($request->total_qty ?? 0),
                        $username,
                        $periode
                    ]
                );
            } else {
                // Call HDHDEL procedure before update
                DB::statement("CALL HDHDEL(?)", [$no_bukti]);

                // Update header - matching Delphi structure
                DB::statement(
                    "UPDATE HDH
                     SET TGL = ?, kodes = ?, namas = ?, TOTAL_QTY = ?, USRNM = ?, TG_SMP = NOW()
                     WHERE NO_BUKTI = ?",
                    [
                        $request->tgl,
                        trim($request->kodec ?? ''),
                        trim($request->namac ?? ''),
                        floatval($request->total_qty ?? 0),
                        $username,
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
                                 SET REC = ?, KD_BRGH = ?, NA_BRGH = ?, QTY = ?
                                 WHERE NO_ID = ?",
                                [
                                    intval($detail['rec'] ?? 1),
                                    trim($detail['kd_brgh'] ?? ''),
                                    trim($detail['na_brgh'] ?? ''),
                                    floatval($detail['qty'] ?? 0),
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
                            "INSERT INTO HDHD (NO_BUKTI, REC, PER, FLAG, CBG, KD_BRGH, NA_BRGH, QTY, ID)
                             VALUES (?, ?, ?, 'HK', ?, ?, ?, ?, ?)",
                            [
                                $no_bukti,
                                $rec,
                                $periode,
                                $cbg,
                                trim($detail['kd_brgh']),
                                trim($detail['na_brgh'] ?? ''),
                                floatval($detail['qty'] ?? 0),
                                $id
                            ]
                        );

                        // Update brghd stock - matching Delphi logic
                        DB::statement(
                            "UPDATE brghd
                             SET ke00 = ke00 + ?, ak00 = aw00 + ma00 - ke00 + ln00
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

            // Update JUALD2 to mark as processed - matching Delphi
            DB::statement(
                "UPDATE JUALD2 SET SLS = 1 WHERE NO_BUKTI = ?",
                [$request->no_resi]
            );

            // Call HDHINS procedure after insert
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
     * Get sales data from NO_RESI (matching Delphi txtno_resiExit logic)
     */
    public function getSalesData(Request $request)
    {
        $no_resi = $request->get('no_resi');

        if (empty($no_resi)) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor Resi tidak boleh kosong'
            ]);
        }

        // Check if already processed
        $check = DB::select("SELECT NO_PO FROM hdh WHERE NO_PO = ?", [$no_resi]);
        if (!empty($check)) {
            return response()->json([
                'success' => false,
                'message' => 'Nota ini sudah di entri!'
            ]);
        }

        // Get sales header data
        $sales = DB::select(
            "SELECT jual.no_bukti, jual.kodeC, jual.namaC
             FROM jual
             WHERE jual.no_bukti = ?",
            [$no_resi]
        );

        if (empty($sales)) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor Resi Salah!'
            ]);
        }

        // Get sales detail data
        $detail = DB::select(
            "SELECT jual.no_bukti, juald2.KD_BRGH, juald2.NA_BRGH, juald2.qty
             FROM jual, juald2
             WHERE jual.no_bukti = juald2.no_bukti
               AND jual.no_bukti = ?",
            [$no_resi]
        );

        return response()->json([
            'success' => true,
            'header' => $sales[0],
            'detail' => $detail
        ]);
    }

    /**
     * Browse product data (matching Delphi product lookup)
     */
    public function browse(Request $request)
    {
        $q = $request->get('q', '');

        if (!empty($q)) {
            $data = DB::select(
                "SELECT kd_brgh, na_brgh
                 FROM BrgH
                 WHERE kd_brgh LIKE ? OR na_brgh LIKE ?
                 ORDER BY kd_brgh
                 LIMIT 50",
                ["%$q%", "%$q%"]
            );
        } else {
            $data = DB::select(
                "SELECT kd_brgh, na_brgh
                 FROM BrgH
                 ORDER BY kd_brgh
                 LIMIT 50"
            );
        }

        return response()->json($data);
    }

    /**
     * Get product detail (matching Delphi product validation)
     */
    public function getDetail(Request $request)
    {
        $kd_brgh = $request->get('kd_brgh');

        $product = DB::select(
            "SELECT kd_brgh, na_brgh
             FROM BrgH
             WHERE kd_brgh = ?
             LIMIT 1",
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
     * Print Penukaran Hadiah
     */
    public function printPenukaranHadiah(Request $request)
    {
        $no_bukti = $request->no_bukti;
        $per = session('periode', date('m.Y'));

        $periode = $per['bulan'] . '/' . $per['tahun'];        $cbg = session('cbg', '01');

        $cbg = session('cbg', '01');

        $data = DB::select(
            "SELECT hdh.NO_BUKTI, hdh.TGL, hdh.NO_PO, hdh.kodes, hdh.namas,
                    hdhd.KD_BRGH, hdhd.NA_BRGH, hdhd.QTY
             FROM hdh, hdhd
             WHERE hdh.no_bukti = hdhd.no_bukti
               AND hdh.FLAG = 'HK'
               AND hdh.no_bukti = ?
               AND hdh.per = ?
               AND hdh.cbg = ?
             ORDER BY hdhd.REC",
            [$no_bukti, $periode, $cbg]
        );

        return response()->json([
            'data' => $data
        ]);
    }

    /**
     * Generate no_bukti for new transaction - matching Delphi logic
     */
    private function generateNoBukti($periode, $cbg)
    {
        $monthString = substr($periode, 0, 2);
        $year = substr($periode, -4);

        // Get toko type
        $toko = DB::select("SELECT type FROM toko WHERE kode = ?", [$cbg]);
        $kode2 = $toko[0]->type ?? '';

        $kode = 'HK' . substr($year, -2) . $monthString;

        // Get next number from notrans
        $notrans = DB::select("SELECT NOM{$monthString} as NO_BUKTI FROM notrans WHERE trans = 'HDHK' AND PER = ?", [$year]);
        $r1 = ($notrans[0]->NO_BUKTI ?? 0) + 1;

        // Update counter
        DB::statement("UPDATE notrans SET NOM{$monthString} = ? WHERE trans = 'HDHK' AND PER = ?", [$r1, $year]);

        $bkt1 = str_pad($r1, 4, '0', STR_PAD_LEFT);
        return $kode . '-' . $bkt1 . $kode2;
    }
}