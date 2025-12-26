<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PHTurunHargaController extends Controller
{
    /**
     * Constructor - ensure thx table exists
     */
    public function __construct()
    {
        $this->ensureThxTableExists();
    }

    /**
     * Ensure thx table exists in tgz database
     * Auto-create if not exists
     */
    private function ensureThxTableExists()
    {
        try {
            // Check if table exists
            $tableExists = DB::select(
                "SELECT COUNT(*) as count
                 FROM information_schema.tables
                 WHERE table_schema = DATABASE()
                   AND table_name = 'thx'"
            );

            if ($tableExists[0]->count == 0) {
                // Create table thx
                DB::statement("
                    CREATE TABLE thx (
                        NO_ID INT AUTO_INCREMENT PRIMARY KEY,
                        NA VARCHAR(50),
                        NOTES VARCHAR(255),
                        TGL_MULAI DATE,
                        TGL_SLS DATE,
                        KODES VARCHAR(10),
                        NAMAS VARCHAR(100),
                        NO_BUKTI VARCHAR(30),
                        REC INT,
                        KD_BRG VARCHAR(20),
                        NA_BRG VARCHAR(150),
                        KET_UK VARCHAR(50),
                        KET_KEM VARCHAR(50),
                        PARTSP DECIMAL(15,2),
                        TH DECIMAL(15,2),
                        QTY DECIMAL(15,2),
                        TOTAL DECIMAL(15,2),
                        CBG VARCHAR(10),
                        KET TEXT,
                        HJ DECIMAL(15,2) DEFAULT 0,
                        PER VARCHAR(20),
                        INDEX idx_bukti_cbg (NO_BUKTI, CBG)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
                ");
            }
        } catch (\Exception $e) {
            // Log error but continue
            Log::error('Error ensuring thx table exists: ' . $e->getMessage());
        }
    }

    /**
     * Display index page for turun harga
     */
    public function index()
    {
        return view('promo_hadiah_turun_harga.index');
    }

    /**
     * Get list of turun harga for datatable
     * Matching Delphi: Tampil procedure
     */
    public function getData(Request $request)
    {
        $per = session('periode', date('m.Y'));
        $periode = is_array($per) ? ($per['bulan'] . '/' . $per['tahun']) : $per;
        $cbg = session('cbg', 'TGZ');

        // Query matching Delphi: Tampil procedure
        // Use subquery to avoid GROUP BY issues
        $query = DB::select(
            "SELECT NO_BUKTI, TGL_MULAI, TGL_SLS, KODES, NAMAS, notes, posted,
                    NO_BELI, TR_GZ, TR_MM, TR_SP,
                    CONCAT(RIGHT(TGL_MULAI,2),'/',LEFT(RIGHT(TGL_MULAI,5),2),'-',DATE_FORMAT(tgl_sls,'%d/%m/%Y')) as tx,
                    LPAD(MONTH(tgl_mulai),2,0) as balon, CARA_BAYAR, NA_KWI, cek,
                    TOTAL_TGZ, TOTAL_TMM, TOTAL_SOP
             FROM DIS
             WHERE flag='PD'
               AND (per=? OR DATEDIFF(DATE(NOW()),tgl_sls) < 60)
             ORDER BY NO_BUKTI DESC",
            [$periode]
        );

        return Datatables::of(collect($query))
            ->addIndexColumn()
            ->editColumn('TGL_MULAI', function ($row) {
                return $row->TGL_MULAI ? date('d/m/Y', strtotime($row->TGL_MULAI)) : '';
            })
            ->editColumn('TGL_SLS', function ($row) {
                return $row->TGL_SLS ? date('d/m/Y', strtotime($row->TGL_SLS)) : '';
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
                $btnDelete = '<button onclick="deleteData(\'' . $row->NO_BUKTI . '\')" class="btn btn-sm btn-danger ml-1" title="Delete"><i class="fas fa-trash"></i></button>';
                $btnPrint = '<button onclick="printData(\'' . $row->NO_BUKTI . '\')" class="btn btn-sm btn-info ml-1" title="Print"><i class="fas fa-print"></i></button>';
                return $btnEdit . ' ' . $btnDelete . ' ' . $btnPrint;
            })
            ->rawColumns(['action', 'posted'])
            ->make(true);
    }

    /**
     * Show form for create/edit turun harga
     * Matching Delphi: cxGrid1DBTableView1DblClick (edit mode)
     */
    public function edit(Request $request)
    {
        $no_bukti = $request->get('no_bukti');
        $status = $request->get('status', 'simpan');

        $per = session('periode', date('m.Y'));
        $periode = is_array($per) ? ($per['bulan'] . '/' . $per['tahun']) : $per;

        $data = [
            'no_bukti' => '+',
            'status' => $status,
            'header' => null,
            'detail' => [],
            'periode' => $periode,
            'cbg' => session('cbg', 'TGZ')
        ];

        if ($status == 'edit' && $no_bukti) {
            // Check if posted
            $check_posted = DB::select("SELECT posted FROM DIS WHERE no_bukti = ?", [$no_bukti]);

            if (!empty($check_posted) && $check_posted[0]->posted == 1) {
                return redirect()->route('phturanharga')->with('error', 'Data Sudah Terposting !!');
            }

            // Check if promo has ended
            $check_expired = DB::select(
                "SELECT COUNT(*) as jumx FROM DIS WHERE no_bukti=? AND DATE(TGL_SLS) < CURDATE()",
                [$no_bukti]
            );

            if (!empty($check_expired) && $check_expired[0]->jumx > 0) {
                return redirect()->route('phturanharga')->with('error', 'Promo Sudah Berakhir, tidak bisa ubah usulan.');
            }

            // Get header data - matching Delphi edit query
            $header = DB::select(
                "SELECT NO_BUKTI, TGL, TGL_MULAI, TGL_SLS, JAM_MULAI, JAM_SLS,
                        KODES, NAMAS, notes, CARA_BAYAR, NA_KWI, posted
                 FROM DIS
                 WHERE no_bukti = ? AND flag='PD'
                 LIMIT 1",
                [$no_bukti]
            );

            if (!empty($header)) {
                // Get detail data
                $detail = DB::select(
                    "SELECT NO_BUKTI, REC, KD_BRG, NA_BRG, KET_UK, KET_KEM,
                            HJ, HB, PARTSP, KODES, KET, TH, PER, NO_ID, andra
                     FROM DISD
                     WHERE no_bukti = ?
                     ORDER BY REC",
                    [$no_bukti]
                );

                $data['header'] = $header[0];
                $data['detail'] = $detail;
                $data['no_bukti'] = $no_bukti;
            }
        }

        return view('promo_hadiah_turun_harga.edit', $data);
    }

    /**
     * Store/Update turun harga
     * Matching Delphi: Store procedure logic
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'tgl' => 'required|date',
            'tgl_mulai' => 'required|date',
            'tgl_sls' => 'required|date',
            'jam_mulai' => 'required',
            'jam_sls' => 'required',
            'kodes' => 'required',
            'details' => 'required|array|min:1'
        ]);

        DB::beginTransaction();

        try {
            $no_bukti = trim($request->no_bukti);
            $status = $request->status;
            $per = session('periode', date('m.Y'));
            $periode = is_array($per) ? ($per['bulan'] . '/' . $per['tahun']) : $per;
            $cbg = session('cbg', 'TGZ');
            $username = Auth::user()->username ?? 'system';

            // Check if period is closed
            $check_period = DB::select("SELECT posted FROM perid WHERE kd_peri=?", [$periode]);
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

            // Validate date logic
            $tgl_mulai = Carbon::parse($request->tgl_mulai);
            $tgl_sls = Carbon::parse($request->tgl_sls);

            if ($tgl_mulai->equalTo($tgl_sls) || $tgl_mulai->greaterThan($tgl_sls)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Filter tanggal tidak sesuai!'
                ], 400);
            }

            if ($status == 'simpan') {
                // Generate no_bukti
                if ($no_bukti == '+') {
                    $no_bukti = $this->generateNoBukti($periode, $cbg);
                }

                // Insert header
                DB::statement(
                    "INSERT INTO DIS (TGL, CBG, NO_BUKTI, TGL_MULAI, TGL_SLS, JAM_MULAI, JAM_SLS,
                                      KODES, NAMAS, FLAG, USRNM, PER, TG_SMP, notes, CARA_BAYAR, NA_KWI)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'PD', ?, ?, NOW(), ?, ?, ?)",
                    [
                        $request->tgl,
                        $cbg,
                        $no_bukti,
                        $request->tgl_mulai,
                        $request->tgl_sls,
                        $request->jam_mulai,
                        $request->jam_sls,
                        $request->kodes,
                        $request->namas,
                        $username,
                        $periode,
                        $request->notes ?? '',
                        $request->cara_bayar ?? '',
                        $request->na_kwi ?? ''
                    ]
                );
            } else {
                // Update mode - reset turun harga data first
                DB::statement(
                    "UPDATE masks, (SELECT DIS.TGL_MULAI, DIS.TGL_SLS, DISD.NO_BUKTI, DISD.KD_BRG
                                    FROM DIS, DISD
                                    WHERE DIS.no_bukti = DISD.no_bukti
                                      AND DIS.NO_BUKTI = ?) as ini
                     SET masks.th = 0, masks.thgz = 0, masks.thmm = 0, masks.thsp = 0
                     WHERE masks.kd_brg = ini.KD_BRG",
                    [$no_bukti]
                );

                // Update header
                DB::statement(
                    "UPDATE DIS
                     SET NO_BUKTI=?, TGL_MULAI=?, TGL_SLS=?, JAM_MULAI=?, JAM_SLS=?,
                         KODES=?, NAMAS=?, TGL=?, USRNM=?, TG_SMP=NOW(), notes=?,
                         tgz=0, tmm=0, sop=0, CARA_BAYAR=?, NA_KWI=?
                     WHERE NO_BUKTI=?",
                    [
                        $no_bukti,
                        $request->tgl_mulai,
                        $request->tgl_sls,
                        $request->jam_mulai,
                        $request->jam_sls,
                        $request->kodes,
                        $request->namas,
                        $request->tgl,
                        $username,
                        $request->notes ?? '',
                        $request->cara_bayar ?? '',
                        $request->na_kwi ?? '',
                        $no_bukti
                    ]
                );
            }

            // Get header ID
            $header_id_result = DB::select("SELECT no_id FROM DIS WHERE no_bukti=?", [$no_bukti]);
            $id = $header_id_result[0]->no_id ?? 0;

            // Handle detail updates
            if ($status == 'edit') {
                $existing_details = DB::select("SELECT no_id FROM DISD WHERE no_bukti = ?", [$no_bukti]);

                foreach ($existing_details as $existing) {
                    $found = false;
                    foreach ($request->details as $detail) {
                        if (isset($detail['no_id']) && $detail['no_id'] == $existing->no_id) {
                            // Update existing record
                            DB::statement(
                                "UPDATE DISD
                                 SET REC=?, KD_BRG=?, NA_BRG=?, KET_UK=?, KET_KEM=?,
                                     HJ=?, HB=?, th=?, PARTSP=?, KET=?
                                 WHERE NO_ID=?",
                                [
                                    intval($detail['rec'] ?? 1),
                                    trim($detail['kd_brg'] ?? ''),
                                    trim($detail['na_brg'] ?? ''),
                                    trim($detail['ket_uk'] ?? ''),
                                    trim($detail['ket_kem'] ?? ''),
                                    floatval($detail['hj'] ?? 0),
                                    floatval($detail['hb'] ?? 0),
                                    floatval($detail['th'] ?? 0),
                                    floatval($detail['partsp'] ?? 0),
                                    trim($detail['ket'] ?? ''),
                                    $existing->no_id
                                ]
                            );
                            $found = true;
                            break;
                        }
                    }

                    if (!$found) {
                        // Delete record and reset masks data
                        $kd_brg_to_delete = DB::select("SELECT KD_BRG FROM DISD WHERE no_id = ?", [$existing->no_id]);
                        if (!empty($kd_brg_to_delete)) {
                            $kd_brg = $kd_brg_to_delete[0]->KD_BRG;

                            // Reset masks data for all outlets
                            $outlets = DB::select("SELECT TRIM(KODE) as cbg FROM toko WHERE STA IN ('MA','CB') ORDER BY NO_ID ASC");
                            foreach ($outlets as $outlet) {
                                DB::statement(
                                    "UPDATE masks
                                     SET THGZ=0, THMM=0, THSP=0, JAM='00:00:00', JAMSLS='00:00:00',
                                         TGDIS_M='2001-01-01', TGDIS_A='2001-01-01'
                                     WHERE KD_BRG=?",
                                    [$kd_brg]
                                );
                            }
                        }

                        DB::statement("DELETE FROM DISD WHERE NO_ID = ?", [$existing->no_id]);
                    }
                }
            }

            // Insert new detail records
            $rec = 1;
            foreach ($request->details as $detail) {
                if (!empty($detail['kd_brg'])) {
                    if (!isset($detail['no_id']) || $detail['no_id'] == 0) {
                        DB::statement(
                            "INSERT INTO DISD (NO_BUKTI, REC, PER, FLAG, KD_BRG, NA_BRG,
                                               KET_UK, KET_KEM, HJ, HB, TH, PARTSP, KET, ID)
                             VALUES (?, ?, ?, 'PD', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                            [
                                $no_bukti,
                                $rec,
                                $periode,
                                trim($detail['kd_brg']),
                                trim($detail['na_brg'] ?? ''),
                                trim($detail['ket_uk'] ?? ''),
                                trim($detail['ket_kem'] ?? ''),
                                floatval($detail['hj'] ?? 0),
                                floatval($detail['hb'] ?? 0),
                                floatval($detail['th'] ?? 0),
                                floatval($detail['partsp'] ?? 0),
                                trim($detail['ket'] ?? ''),
                                $id
                            ]
                        );
                    }
                    $rec++;
                }
            }

            // Note: Delphi distributes to other outlets, but since we use tgz database only
            // we skip the distribution to TMM/SOP outlets

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
     * Generate sales report for specific outlet (TGZ/TMM/SOP)
     * Matching Delphi: penjualan procedure
     */
    public function generateSalesReport(Request $request)
    {
        $no_bukti = $request->get('no_bukti');
        $cbg = $request->get('cbg', 'TGZ'); // TGZ, TMM, or SOP

        try {
            // Ensure thx table exists
            $this->ensureThxTableExists();

            // Get date range
            $dateRange = DB::select(
                "SELECT MONTH(TGL_SLS) as ak, MONTH(tgl_mulai) as aw, TGL_SLS, per
                 FROM dis WHERE NO_BUKTI = ?",
                [$no_bukti]
            );

            if (empty($dateRange)) {
                return response()->json(['success' => false, 'message' => 'Bukti not found'], 404);
            }

            $aw = $dateRange[0]->aw;
            $ak = $dateRange[0]->ak;
            $per = $dateRange[0]->per;

            if ($ak < $aw) {
                $ak = $ak + 12;
            }

            // Check if report already exists
            $existing = DB::select(
                "SELECT NO_ID FROM thx WHERE no_bukti=? AND cbg=?",
                [$no_bukti, $cbg]
            );

            if (count($existing) > 0) {
                // Ask for update (in real scenario, return to frontend for confirmation)
                DB::statement(
                    "DELETE FROM thx WHERE no_bukti=? AND cbg=?",
                    [$no_bukti, $cbg]
                );
            }

            // Build dynamic query for all months in range
            $unionParts = [];
            $currentMonth = $aw;

            // Filter for ZP and RF types (exclude from TR)
            $filterMinus = " AND juald%02d.TYPE NOT IN ('ZP','RF') ";

            for ($i = $aw; $i <= $ak; $i++) {
                if ($currentMonth > 12) {
                    $currentMonth = 1;
                }

                $bulan = sprintf('%02d', $currentMonth);
                $filter = sprintf($filterMinus, $currentMonth);

                $unionParts[] = "
                    SELECT
                        IF(juald{$bulan}.SUB2 BETWEEN '001' AND '065', CONCAT('49.001.', juald{$bulan}.SUB2),
                        IF(juald{$bulan}.SUB2 BETWEEN '066' AND '085' OR juald{$bulan}.SUB2='088' OR juald{$bulan}.SUB2='099', CONCAT('49.002.', juald{$bulan}.SUB2),
                        IF(juald{$bulan}.SUB2 BETWEEN '086' AND '100', CONCAT('49.003.', juald{$bulan}.SUB2),
                        IF(juald{$bulan}.SUB2 BETWEEN '101' AND '150', CONCAT('49.004.', juald{$bulan}.SUB2),
                        IF(juald{$bulan}.SUB2 BETWEEN '151' AND '180' OR juald{$bulan}.SUB2='199', CONCAT('49.005.', juald{$bulan}.SUB2),
                        IF(juald{$bulan}.SUB2 BETWEEN '201' AND '203', CONCAT('49.010.', juald{$bulan}.SUB2),
                        IF(juald{$bulan}.SUB2 BETWEEN '181' AND '200', CONCAT('49.006.', juald{$bulan}.SUB2),
                        IF(juald{$bulan}.SUB2 BETWEEN '223' AND '225', CONCAT('49.020.', juald{$bulan}.SUB2),
                        IF(juald{$bulan}.SUB2 BETWEEN '300' AND '699', CONCAT('49.007.', juald{$bulan}.SUB2),
                        IF(juald{$bulan}.SUB2 >= '700', CONCAT('49.008.', juald{$bulan}.SUB2), '')))))))))) as na,
                        CONCAT(LEFT(DIS.NOTES, 26), ' SUB-', juald{$bulan}.SUB2) as notes,
                        DIS.TGL_MULAI, DIS.TGL_SLS, DIS.KODES, DIS.NAMAS,
                        DISD.NO_BUKTI, DISD.REC, DISD.KD_BRG, DISD.NA_BRG,
                        DISD.KET_UK, DISD.KET_KEM, DISD.PARTSP, DISD.TH,
                        ROUND(SUM(juald{$bulan}.qty)) as qty,
                        ROUND(SUM(disd.Partsp * juald{$bulan}.qty)) as total,
                        juald{$bulan}.CBG
                    FROM {$cbg}.juald{$bulan}, dis, disd
                    WHERE juald{$bulan}.cbg = '{$cbg}'
                        AND juald{$bulan}.KD_BRG = disd.KD_BRG
                        AND DIS.no_bukti = disd.no_bukti
                        AND dis.no_bukti = '{$no_bukti}'
                        {$filter}
                        AND DATE(juald{$bulan}.TGL) BETWEEN dis.TGL_MULAI AND dis.TGL_SLS
                    GROUP BY juald{$bulan}.KD_BRG
                ";

                $currentMonth++;
            }

            $fullQuery = "
                INSERT INTO thx
                (NA, NOTES, TGL_MULAI, TGL_SLS, KODES, NAMAS, NO_BUKTI, REC, KD_BRG,
                 NA_BRG, KET_UK, KET_KEM, PARTSP, TH, QTY, TOTAL, CBG, KET)
                SELECT NA, NOTES, TGL_MULAI, TGL_SLS, KODES, NAMAS, NO_BUKTI, REC, KD_BRG,
                       NA_BRG, KET_UK, KET_KEM, PARTSP, TH, SUM(QTY) AS QTY,
                       SUM(TOTAL) AS TOTAL, CBG, '' as KET
                FROM (
                    " . implode(" UNION ALL ", $unionParts) . "
                ) as CC
                GROUP BY NO_BUKTI, KD_BRG
                ORDER BY REC
            ";

            DB::statement($fullQuery);

            // Update HJ prices from masks
            DB::statement("CALL pjl_update_hj_tr(?, ?)", [$no_bukti, $cbg]);

            // Calculate total and update DIS
            $totalResult = DB::select(
                "SELECT SUM(total) as total FROM thx WHERE no_bukti=? AND cbg=?",
                [$no_bukti, $cbg]
            );

            $total = $totalResult[0]->total ?? 0;

            DB::statement(
                "UPDATE dis SET TOTAL_{$cbg} = ? WHERE no_bukti = ?",
                [$total, $no_bukti]
            );

            // Get report data
            $reportData = DB::select(
                "SELECT *,
                        IF(HJ=0, 0, HJ-TH) as hjbr,
                        CONCAT(DATE_FORMAT(TGL_MULAI,'%d/%m/%Y'),'-',DATE_FORMAT(TGL_SLS,'%d/%m/%Y')) as berlaku,
                        CONCAT(DATE_FORMAT(TGL_MULAI,'%d'),'-',DATE_FORMAT(TGL_SLS,'%d/%m/%Y')) as masa_jurnal
                 FROM thx
                 WHERE no_bukti=? AND CBG=?",
                [$no_bukti, $cbg]
            );

            return response()->json([
                'success' => true,
                'message' => 'Report generated successfully',
                'data' => $reportData,
                'total' => $total
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Browse data (supplier, product, etc.)
     */
    public function browse(Request $request)
    {
        $type = $request->get('type', 'supplier');
        $q = $request->get('q', '');

        if ($type == 'supplier') {
            if (!empty($q)) {
                $data = DB::select(
                    "SELECT kodes, namas FROM sup WHERE kodes LIKE ? OR namas LIKE ? ORDER BY kodes LIMIT 50",
                    ["%$q%", "%$q%"]
                );
            } else {
                $data = DB::select("SELECT kodes, namas FROM sup ORDER BY kodes LIMIT 50");
            }
        } elseif ($type == 'product') {
            $kodes = $request->get('kodes', '');
            $tgl_mulai = $request->get('tgl_mulai', date('Y-m-d'));

            if (!empty($q)) {
                $data = DB::select(
                    "SELECT B.KD_BRG, B.NA_BRG, B.KET_UK, B.KET_KEM, A.HJ, A.HJGZ, B.SUPP,
                            IF((CURDATE() BETWEEN A.TGDIS_M AND A.TGDIS_A)
                               AND (A.THGZ+A.THMM+A.THSP>0)
                               AND (A.TGDIS_A>=?), 'XX', 'OK') as X,
                            CONCAT(A.NA_BRG,' : ',TGDIS_M,' s/d ',TGDIS_A) as XX
                     FROM masks A JOIN brg B ON A.KD_BRG = B.KD_BRG
                     WHERE (B.KD_BRG LIKE ? OR B.NA_BRG LIKE ?)
                       AND B.SUPP = ?
                     ORDER BY B.KD_BRG
                     LIMIT 50",
                    [$tgl_mulai, "%$q%", "%$q%", $kodes]
                );
            } else {
                $data = DB::select(
                    "SELECT B.KD_BRG, B.NA_BRG, B.KET_UK, B.KET_KEM, A.HJ, A.HJGZ, B.SUPP
                     FROM masks A, brg B
                     WHERE A.KD_BRG = B.KD_BRG AND B.SUPP = ?
                     ORDER BY B.KD_BRG
                     LIMIT 50",
                    [$kodes]
                );
            }
        } else {
            $data = [];
        }

        return response()->json($data);
    }

    /**
     * Get detail (validate product, supplier, etc.)
     */
    public function getDetail(Request $request)
    {
        $type = $request->get('type', 'supplier');

        if ($type == 'supplier') {
            $kodes = $request->get('kodes');
            $supplier = DB::select("SELECT kodes, namas FROM sup WHERE kodes = ?", [$kodes]);

            if (!empty($supplier)) {
                return response()->json([
                    'success' => true,
                    'exists' => true,
                    'data' => $supplier[0]
                ]);
            }
        } elseif ($type == 'product') {
            $kd_brg = $request->get('kd_brg');
            $kodes = $request->get('kodes', '');
            $tgl_mulai = $request->get('tgl_mulai', date('Y-m-d'));

            $product = DB::select(
                "SELECT B.KD_BRG, B.NA_BRG, B.KET_UK, B.KET_KEM, A.HJ, A.HJGZ, B.SUPP,
                        IF((CURDATE() BETWEEN A.TGDIS_M AND A.TGDIS_A)
                           AND (A.THGZ+A.THMM+A.THSP>0)
                           AND (A.TGDIS_A>=?), 'XX', 'OK') as X,
                        CONCAT(A.NA_BRG,' : ',TGDIS_M,' s/d ',TGDIS_A) as XX
                 FROM masks A, brg B
                 WHERE A.KD_BRG = B.KD_BRG AND A.KD_BRG = ?
                 LIMIT 1",
                [$tgl_mulai, $kd_brg]
            );

            if (!empty($product)) {
                if ($product[0]->X == 'XX') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Sudah ada pengajuan atas item ini!'
                    ]);
                }

                if (!empty($kodes) && $product[0]->SUPP != $kodes) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Barang Tidak Sesuai Dengan Supplier!'
                    ]);
                }

                // Check for ongoing promotions (OB/FS)
                $check_promo = DB::select(
                    "SELECT a.NO_BUKTI, a.TGL_MULAI, a.TGL_SLS
                     FROM dis a, disd b
                     WHERE a.NO_BUKTI = b.NO_BUKTI
                       AND DATE(?) BETWEEN a.TGL_MULAI AND a.TGL_SLS
                       AND b.KD_BRG = ?
                       AND a.flag IN ('OB','FS')
                       AND b.dis > 0",
                    [$tgl_mulai, $kd_brg]
                );

                if (!empty($check_promo)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Sedang ada pengajuan atas item ini! ' . $check_promo[0]->NO_BUKTI
                    ]);
                }

                // Check for ongoing turun harga
                $check_th = DB::select(
                    "SELECT a.NO_BUKTI, a.TGL_MULAI, a.TGL_SLS
                     FROM dis a, disd b
                     WHERE a.NO_BUKTI = b.NO_BUKTI
                       AND DATE(?) BETWEEN a.TGL_MULAI AND a.TGL_SLS
                       AND b.KD_BRG = ?
                       AND a.flag = 'PD'
                       AND b.th > 0",
                    [$tgl_mulai, $kd_brg]
                );

                if (!empty($check_th)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Sedang ada pengajuan atas item ini! ' . $check_th[0]->NO_BUKTI
                    ]);
                }

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

        return response()->json([
            'success' => true,
            'exists' => false,
            'data' => null
        ]);
    }

    /**
     * Print turun harga
     * Matching Delphi: bPrintClick procedure
     */
    public function printTurunHarga(Request $request)
    {
        $no_bukti = $request->no_bukti;
        $per = session('periode', date('m.Y'));
        $periode = is_array($per) ? ($per['bulan'] . '/' . $per['tahun']) : $per;
        $cbg = session('cbg', 'TGZ');

        // Matching Delphi print query
        $data = DB::select(
            "SELECT dis.NO_BUKTI, dis.TGL_MULAI, dis.TGL_SLS, dis.KODES, dis.NAMAS,
                    disd.KD_BRG, disd.NA_BRG, disd.ket_uk, disd.ket_kem, disd.HJ,
                    disd.hb, disd.KODES, disd.partsp, disd.ket, disd.TH
             FROM dis, disd
             WHERE dis.flag='PD' AND dis.no_bukti = disd.no_bukti
               AND dis.no_bukti = ?
               AND dis.per = ?
               AND dis.cbg = ?
             ORDER BY dis.no_bukti",
            [$no_bukti, $periode, $cbg]
        );

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Print kwitansi (receipt)
     * Matching Delphi: bKwinClick procedure
     */
    public function printKwitansi(Request $request)
    {
        $no_bukti = $request->no_bukti;

        try {
            // Get penerima name
            $namaResult = DB::select(
                "SELECT IF(TRIM(NA_KWI)='', CONCAT(NAMAS,' ',KODES), NA_KWI) as NAMAX
                 FROM DIS
                 WHERE no_bukti = ?
                 LIMIT 1",
                [$no_bukti]
            );

            if (empty($namaResult)) {
                return response()->json(['success' => false, 'message' => 'Bukti not found'], 404);
            }

            $namas = $namaResult[0]->NAMAX;

            // Get totals
            $totals = DB::select(
                "SELECT CARA_BAYAR, MONTH(TGL_SLS) as ak, MONTH(tgl_mulai) as aw,
                        TOTAL_SOP, TOTAL_TGZ, TOTAL_TMM,
                        (TOTAL_SOP + TOTAL_TGZ + TOTAL_TMM) as TOTALX
                 FROM dis
                 WHERE NO_BUKTI = ?",
                [$no_bukti]
            );

            if (empty($totals)) {
                return response()->json(['success' => false, 'message' => 'No totals found'], 404);
            }

            $totalx = $totals[0]->TOTALX;
            $terbilang = $this->terbilang(strval($totalx));

            // Get header info
            $header = DB::select(
                "SELECT NO_BUKTI, TGL_MULAI, notes,
                        CONCAT(RIGHT(TGL_MULAI,2),'/',LEFT(RIGHT(TGL_MULAI,5),2),'-',DATE_FORMAT(tgl_sls,'%d/%m/%Y')) as tx
                 FROM DIS
                 WHERE no_bukti = ?",
                [$no_bukti]
            );

            $result = [
                'no_bukti' => $no_bukti,
                'namas' => $namas,
                'notes' => $header[0]->notes ?? '',
                'tx' => $header[0]->tx ?? '',
                'tgl_mulai' => $header[0]->TGL_MULAI ?? '',
                'terbilang' => $terbilang,
                'TGZ' => $totals[0]->TOTAL_TGZ ?? 0,
                'TMM' => $totals[0]->TOTAL_TMM ?? 0,
                'SOP' => $totals[0]->TOTAL_SOP ?? 0,
                'CARA_BAYAR' => $totals[0]->CARA_BAYAR ?? ''
            ];

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Terbilang function - converts number to Indonesian words
     * Matching Delphi: terbilang function
     */
    private function terbilang($sValue)
    {
        $angka = [
            '',
            'Satu',
            'Dua',
            'Tiga',
            'Empat',
            'Lima',
            'Enam',
            'Tujuh',
            'Delapan',
            'Sembilan',
            'Sepuluh',
            'Sebelas',
            'Duabelas',
            'Tigabelas',
            'Empatbelas',
            'Limabelas',
            'Enambelas',
            'Tujuhbelas',
            'Delapanbelas',
            'Sembilanbelas'
        ];

        $sPattern = '000000000000000';
        $rupiah = '';

        $s = substr($sPattern, 0, strlen($sPattern) - strlen(trim($sValue))) . $sValue;

        $one = 3;
        $two = 4;
        $three = 5;
        $hitung = 1;

        while ($hitung < 5) {
            $satu = substr($s, $one, 1);
            $dua = substr($s, $two, 1);
            $tiga = substr($s, $three, 1);
            $gabung = $satu . $dua . $tiga;

            if (intval($satu) == 1) {
                $rupiah .= 'Seratus ';
            } elseif (intval($satu) > 1) {
                $rupiah .= $angka[intval($satu)] . ' Ratus ';
            }

            if (intval($dua) == 1) {
                $belas = $dua . $tiga;
                $rupiah .= $angka[intval($belas)];
            } elseif (intval($dua) > 1) {
                $rupiah .= $angka[intval($dua)] . ' Puluh ' . $angka[intval($tiga)];
            } elseif (intval($dua) == 0 && intval($tiga) > 0) {
                if (($hitung == 3 && $gabung == '001') || ($hitung == 3 && $gabung == '  1')) {
                    $rupiah .= 'Seribu ';
                } else {
                    $rupiah .= $angka[intval($tiga)];
                }
            }

            if ($hitung == 1 && intval($gabung) > 0) {
                $rupiah .= ' Milyar ';
            } elseif ($hitung == 2 && intval($gabung) > 0) {
                $rupiah .= ' Juta ';
            } elseif ($hitung == 3 && intval($gabung) > 0) {
                if ($gabung != '001' && $gabung != '  1') {
                    $rupiah .= ' Ribu ';
                }
            }

            $hitung++;
            $one += 3;
            $two += 3;
            $three += 3;
        }

        if (strlen($rupiah) > 1) {
            $rupiah .= ' Rupiah';
        }

        return $rupiah;
    }

    /**
     * Delete turun harga
     * Matching Delphi: cxGrid1DBTableView1KeyUp (delete logic)
     */
    public function destroy($no_bukti)
    {
        try {
            // Check if posted
            $check_posted = DB::select("SELECT posted FROM DIS WHERE no_bukti = ?", [$no_bukti]);

            if (!empty($check_posted) && $check_posted[0]->posted == 1) {
                return redirect()->route('phturanharga')->with('error', 'Data sudah terposting, tidak dapat dihapus');
            }

            DB::beginTransaction();

            // Check if need to reset masks
            $check_masks = DB::select(
                "SELECT KD_BRG FROM masks WHERE KD_BRG IN (SELECT KD_BRG FROM disd WHERE NO_BUKTI=?)",
                [$no_bukti]
            );

            if (!empty($check_masks)) {
                // Reset masks data - matching Delphi reset logic
                DB::statement(
                    "UPDATE masks
                     SET THGZ=0, THMM=0, THSP=0,
                         JAM='00:00:00', JAMSLS='00:00:00',
                         TGDIS_M='2001-01-01', TGDIS_A='2001-01-01'
                     WHERE KD_BRG IN (SELECT KD_BRG FROM disd WHERE NO_BUKTI=?)",
                    [$no_bukti]
                );
            }

            // Delete detail and header
            DB::statement("DELETE FROM disd WHERE NO_BUKTI=?", [$no_bukti]);
            DB::statement("DELETE FROM dis WHERE NO_BUKTI=?", [$no_bukti]);

            DB::commit();

            return redirect()->route('phturanharga')->with('success', 'Turun Harga ' . $no_bukti . ' telah terhapus.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('phturanharga')->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    /**
     * Generate no_bukti for new transaction
     * Matching Delphi: generateNoBukti logic in trins
     */
    private function generateNoBukti($periode, $cbg)
    {
        $monthString = substr($periode, 0, 2);
        $year = substr($periode, -4);

        // Get toko type
        $toko = DB::select("SELECT type FROM toko WHERE kode = ?", [$cbg]);
        $kode2 = $toko[0]->type ?? '';

        $kode = 'PD' . substr($year, -2) . $monthString;

        // Get next number from notrans
        $notrans = DB::select(
            "SELECT NOM{$monthString} as no_bukti FROM notrans WHERE trans='TURUNHRG' AND per=?",
            [$year]
        );
        $r1 = ($notrans[0]->no_bukti ?? 0) + 1;

        // Update counter
        DB::statement(
            "UPDATE notrans SET NOM{$monthString} = ? WHERE trans='TURUNHRG' AND per=?",
            [$r1, $year]
        );

        $bkt1 = str_pad($r1, 4, '0', STR_PAD_LEFT);
        return $kode . '-' . $bkt1 . $kode2;
    }

    /**
     * Update nama penerima for kwitansi
     * Matching Delphi: edit penerima in bKwinClick
     */
    public function updatePenerima(Request $request)
    {
        $no_bukti = $request->no_bukti;
        $penerima = $request->penerima;

        try {
            DB::statement(
                "UPDATE DIS SET NA_KWI = ? WHERE no_bukti = ?",
                [$penerima, $no_bukti]
            );

            return response()->json([
                'success' => true,
                'message' => 'Nama penerima updated'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle check status
     * Matching Delphi: cxcek click logic
     */
    public function toggleCheck(Request $request)
    {
        $no_bukti = $request->no_bukti;

        try {
            $current = DB::select("SELECT cek FROM DIS WHERE no_bukti = ?", [$no_bukti]);

            if (empty($current)) {
                return response()->json(['success' => false, 'message' => 'Not found'], 404);
            }

            $newVal = $current[0]->cek == 0 ? 1 : 0;

            DB::statement("UPDATE DIS SET cek = ? WHERE no_bukti = ?", [$newVal, $no_bukti]);

            return response()->json([
                'success' => true,
                'cek' => $newVal
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
