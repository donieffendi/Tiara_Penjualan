<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PHTurunHargaController extends Controller
{
    /**
     * Display index page for turun harga
     */
    public function index()
    {
        return view('promo_hadiah_turun_harga.index');
    }

    /**
     * Get list of turun harga for datatable
     */
    public function getData(Request $request)
    {
        $per = session('periode', date('m.Y'));

        $periode = $per['bulan'] . '/' . $per['tahun'];
        $cbg = session('cbg', '01');

        // Query matching Delphi: Tampil procedure
        $query = DB::select(
            "SELECT NO_BUKTI, TGL_MULAI, TGL_SLS, KODES, NAMAS, notes, posted,
                    NO_BELI, TR_GZ, TR_MM, TR_SP,
                    CONCAT(RIGHT(TGL_MULAI,2),'/',LEFT(RIGHT(TGL_MULAI,5),2),'-',DATE_FORMAT(tgl_sls,'%d/%m/%Y')) as tx,
                    LPAD(MONTH(tgl_mulai),2,0) as balon, CARA_BAYAR, NA_KWI, cek
             FROM DIS
             WHERE flag='PD'
               AND (per=? OR DATEDIFF(DATE(NOW()),tgl_sls) < 60)
             GROUP BY no_bukti
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

            // Get header data
            $header = DB::select(
                "SELECT NO_BUKTI, TGL, TGL_MULAI, TGL_SLS, JAM_MULAI, JAM_SLS,
                        KODES, NAMAS, notes, CARA_BAYAR, NA_KWI, posted
                 FROM DIS
                 WHERE no_bukti = ?
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

        $periode = $per['bulan'] . '/' . $per['tahun'];        $cbg = session('cbg', '01');

            $cbg = session('cbg', '01');
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

            // Distribute data to other outlets
            $outlets = DB::select(
                "SELECT TRIM(KODE) as cbg FROM toko WHERE kode <> ? AND STA IN ('MA','CB') ORDER BY NO_ID ASC",
                [$cbg]
            );

            foreach ($outlets as $outlet) {
                $cab = $outlet->cbg;

                // Delete existing data
                DB::statement("DELETE FROM dis WHERE no_bukti=?", [$no_bukti]);
                DB::statement("DELETE FROM disd WHERE no_bukti=?", [$no_bukti]);

                // Insert header
                DB::statement(
                    "INSERT INTO dis (TGL, CBG, NO_BUKTI, TGL_MULAI, TGL_SLS, JAM_MULAI, JAM_SLS,
                                             KODES, NAMAS, FLAG, USRNM, PER, TG_SMP, notes, CARA_BAYAR, NA_KWI)
                     SELECT TGL, CBG, NO_BUKTI, TGL_MULAI, TGL_SLS, JAM_MULAI, JAM_SLS,
                            KODES, NAMAS, FLAG, USRNM, PER, TG_SMP, notes, CARA_BAYAR, NA_KWI
                     FROM dis WHERE no_bukti=?",
                    [$no_bukti]
                );

                // Insert detail
                DB::statement(
                    "INSERT INTO disd (NO_BUKTI, REC, PER, FLAG, KD_BRG, NA_BRG,
                                              KET_UK, KET_KEM, HJ, HB, TH, PARTSP, KET, ID)
                     SELECT NO_BUKTI, REC, PER, FLAG, KD_BRG, NA_BRG,
                            KET_UK, KET_KEM, HJ, HB, TH, PARTSP, KET, ID
                     FROM disd WHERE no_bukti=?",
                    [$no_bukti]
                );

                // Update HJ from masks
                DB::statement(
                    "UPDATE disd a, masks b
                     SET a.HJ = b.HJ
                     WHERE a.kd_brg = b.kd_brg AND a.no_bukti = ?",
                    [$no_bukti]
                );
            }

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
     */
    public function printTurunHarga(Request $request)
    {
        $no_bukti = $request->no_bukti;

        $data = DB::select(
            "SELECT dis.NO_BUKTI, dis.TGL_MULAI, dis.TGL_SLS, dis.KODES, dis.NAMAS,
                    disd.KD_BRG, disd.NA_BRG, disd.ket_uk, disd.ket_kem, disd.HJ,
                    disd.hb, disd.KODES, disd.partsp, disd.ket, disd.TH
             FROM dis, disd
             WHERE dis.flag='PD' AND dis.no_bukti = disd.no_bukti
               AND dis.no_bukti = ?
             ORDER BY dis.no_bukti",
            [$no_bukti]
        );

        return response()->json([
            'data' => $data
        ]);
    }

    /**
     * Delete turun harga
     */
    public function destroy($no_bukti)
    {
        try {
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
                // Reset masks data for all outlets
                $outlets = DB::select("SELECT TRIM(KODE) as cbg FROM toko WHERE STA IN ('MA','CB') ORDER BY NO_ID ASC");

                foreach ($outlets as $outlet) {
                    DB::statement(
                        "UPDATE masks
                         SET THGZ=0, THMM=0, THSP=0, JAM='00:00:00', JAMSLS='00:00:00',
                             TGDIS_M='2001-01-01', TGDIS_A='2001-01-01'
                         WHERE KD_BRG IN (SELECT KD_BRG FROM disd WHERE NO_BUKTI=?)",
                        [$no_bukti]
                    );

                    DB::statement("DELETE FROM disd WHERE NO_BUKTI=?", [$no_bukti]);
                    DB::statement("DELETE FROM dis WHERE NO_BUKTI=?", [$no_bukti]);
                }
            }

            DB::commit();

            return redirect()->route('phturanharga')->with('success', 'Turun Harga ' . $no_bukti . ' telah terhapus.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('phturanharga')->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    /**
     * Generate no_bukti for new transaction
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
        $notrans = DB::select("SELECT NOM{$monthString} as no_bukti FROM notrans WHERE trans='TURUNHRG' AND per=?", [$year]);
        $r1 = ($notrans[0]->no_bukti ?? 0) + 1;

        // Update counter
        DB::statement("UPDATE notrans SET NOM{$monthString} = ? WHERE trans='TURUNHRG' AND per=?", [$r1, $year]);

        $bkt1 = str_pad($r1, 4, '0', STR_PAD_LEFT);
        return $kode . '-' . $bkt1 . $kode2;
    }
}
