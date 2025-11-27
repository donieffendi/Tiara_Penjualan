<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TSPKOKeDCTunjungsariController extends Controller
{
    public function index()
    {
        $periode = session('periode', date('m.Y'));
        $ma = session('ma', 'TGZ');
        $cbg = session('cbg', '01');
        $OO = $cbg;

        try {
            $cabang = DB::select("SELECT * FROM toko WHERE STA IN ('MA','CB','DC') ORDER BY NO_ID ASC");
            if (empty($cabang)) {
                $cabang = [];
            }
        } catch (\Exception $e) {
            $cabang = [];
        }

        return view('otranskasi_SPKO_Ke_DC_Tunjungsari.index', compact('cabang', 'periode', 'ma', 'cbg'));
    }

    public function getData(Request $request)
    {
        $periode = session('periode', date('m.Y'));
        $ma = session('ma', 'TGZ');
        $flag = session('flag', session('cbg', '01'));
        $OO = $request->get('cbg', $flag);
        $golongan = $request->get('golongan', '');

        Log::info('TSPKOKeDCTunjungsariController getData() start', [
            'periode' => $periode,
            'ma' => $ma,
            'flag' => $flag,
            'OO' => $OO,
            'golongan' => $golongan
        ]);

        try {
            $query = DB::table($OO . '.po_dc_ts')
                ->select('*')
                ->where('per', '=', $periode)
                ->where('FLAG', '=', 'PT')
                ->where('type', '=', 'DC')
                ->where('golongan', '=', substr($golongan, 0, 2));

            if ($flag != $ma) {
                $query->where('cbg', '=', $flag);
            }

            $query->orderBy('NO_BUKTI', 'DESC');

            $count = $query->count();
            Log::info('TSPKOKeDCTunjungsari getData() query executed', ['count' => $count]);

            return Datatables::of($query)
                ->addIndexColumn()
                ->editColumn('TGL', function ($row) {
                    return $row->TGL ? date('d/m/Y', strtotime($row->TGL)) : '';
                })
                ->editColumn('KODES', function ($row) {
                    return $row->KODES ?? '';
                })
                ->editColumn('NAMAS', function ($row) {
                    return $row->NAMAS ?? '';
                })
                ->editColumn('NOTES', function ($row) {
                    return $row->NOTES ?? '';
                })
                ->editColumn('CBG', function ($row) {
                    return $row->CBG ?? '';
                })
                ->editColumn('na_file', function ($row) {
                    return $row->na_file ?? '-';
                })
                ->addColumn('action', function ($row) {
                    $btnEdit = '<button onclick="editData(\'' . $row->NO_BUKTI . '\', \'' . $row->CBG . '\')" class="btn btn-sm btn-primary" title="Edit"><i class="fas fa-edit"></i></button>';
                    $btnExport = '<button onclick="exportData(\'' . $row->NO_BUKTI . '\', \'' . $row->CBG . '\')" class="btn btn-sm btn-success ml-1" title="Export"><i class="fas fa-file-export"></i></button>';
                    $btnPrint = '<button onclick="printData(\'' . $row->NO_BUKTI . '\', \'' . $row->CBG . '\')" class="btn btn-sm btn-info ml-1" title="Print"><i class="fas fa-print"></i></button>';
                    return $btnEdit . ' ' . $btnExport . ' ' . $btnPrint;
                })
                ->rawColumns(['action'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('TSPKOKeDCTunjungsari getData() error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function edit(Request $request)
    {
        try {
            $no_bukti = $request->get('no_bukti', '+');
            $status = $request->get('status', 'simpan');
            $OO = $request->get('cbg', session('cbg', '01'));
            $periode = session('periode', date('m.Y'));
            $ma = session('ma', 'TGZ');
            $flag = session('flag', session('cbg', '01'));

            DB::statement("CREATE TABLE IF NOT EXISTS {$OO}.po_dc_ts LIKE {$OO}.po");
            DB::statement("CREATE TABLE IF NOT EXISTS {$OO}.pod_dc_ts LIKE {$OO}.pod");

            $check_col = DB::select(
                "SELECT COUNT(*) as ADA FROM INFORMATION_SCHEMA.COLUMNS
                 WHERE TABLE_SCHEMA=? AND TABLE_NAME='po_dc_ts' AND COLUMN_NAME='USRNM_POSTED'",
                [$OO]
            );

            if ($check_col[0]->ADA == 0) {
                DB::statement("ALTER TABLE {$OO}.po_dc_ts ADD COLUMN `USRNM_POSTED` varchar(20) NOT NULL DEFAULT ''");
                DB::statement("ALTER TABLE {$OO}.pod_dc_ts ADD COLUMN `LAYAN` decimal(13,3) NOT NULL DEFAULT 0");
            }

            try {
                $cabang = DB::select("SELECT * FROM toko WHERE STA IN ('MA','CB','DC') ORDER BY NO_ID ASC");
                if (empty($cabang)) {
                    $cabang = [];
                }
            } catch (\Exception $e) {
                $cabang = [];
            }

            try {
                $dc_list = DB::select("SELECT KODES, NAMAS FROM {$OO}.sup WHERE KODE_DC<>'' ORDER BY KODES");
                if (empty($dc_list)) {
                    $dc_list = [];
                }
            } catch (\Exception $e) {
                $dc_list = [];
            }

            $data = [
                'no_bukti' => '+',
                'status' => $status,
                'header' => (object)[
                    'NO_BUKTI' => '+',
                    'TGL' => date('Y-m-d'),
                    'CBG' => $OO != $ma ? $OO : '',
                    'NOTES' => '',
                    'TOTAL_QTY' => 0,
                    'TOTAL' => 0,
                    'NETT' => 0,
                    'posted' => 0,
                    'golongan' => ''
                ],
                'detail' => [],
                'periode' => $periode,
                'ma' => $ma,
                'cbg' => $OO,
                'cabang' => $cabang,
                'dc_list' => $dc_list,
                'posted' => 0,
                'error' => null
            ];

            if ($status == 'simpan') {
                $posted_check = DB::select("SELECT posted FROM {$OO}.perid WHERE kd_peri=?", [$periode]);
                $posted = $posted_check[0]->posted ?? 0;

                if ($posted == 1) {
                    $data['error'] = 'Closed Period';
                    return view('otranskasi_SPKO_Ke_DC_Tunjungsari.edit', $data);
                }
            }

            if ($status == 'edit' && $no_bukti && $no_bukti != '+') {
                $header = DB::select(
                    "SELECT * FROM {$OO}.po_dc_ts
                     WHERE no_bukti=? AND FLAG='PT'
                     ORDER BY NO_BUKTI",
                    [$no_bukti]
                );

                if (!empty($header)) {
                    $detail = DB::select(
                        "SELECT * FROM {$OO}.pod_dc_ts
                         WHERE NO_BUKTI=?
                         ORDER BY REC",
                        [$no_bukti]
                    );

                    $data['header'] = $header[0];
                    $data['detail'] = $detail;
                    $data['no_bukti'] = $no_bukti;
                    $data['posted'] = $header[0]->posted ?? 0;
                    $data['cbg'] = $OO;
                } else {
                    $data['error'] = 'Data tidak ditemukan';
                }
            }

            return view('otranskasi_SPKO_Ke_DC_Tunjungsari.edit', $data);
        } catch (\Exception $e) {
            try {
                $cabang = DB::select("SELECT * FROM toko WHERE STA IN ('MA','CB','DC') ORDER BY NO_ID ASC");
                $dc_list = DB::select("SELECT KODES, NAMAS FROM " . session('cbg', '01') . ".sup WHERE KODE_DC<>'' ORDER BY KODES");
            } catch (\Exception $ex) {
                $cabang = [];
                $dc_list = [];
            }

            return view('otranskasi_SPKO_Ke_DC_Tunjungsari.edit', [
                'no_bukti' => '+',
                'status' => 'simpan',
                'header' => (object)[
                    'NO_BUKTI' => '+',
                    'TGL' => date('Y-m-d'),
                    'CBG' => '',
                    'NOTES' => '',
                    'TOTAL_QTY' => 0,
                    'TOTAL' => 0,
                    'NETT' => 0,
                    'posted' => 0,
                    'golongan' => ''
                ],
                'detail' => [],
                'periode' => session('periode', date('m.Y')),
                'ma' => session('ma', 'TGZ'),
                'cbg' => session('cbg', '01'),
                'cabang' => $cabang,
                'dc_list' => $dc_list,
                'posted' => 0,
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'tgl' => 'required|date',
                'cbg' => 'required',
                'golongan' => 'required',
                'details' => 'required|array|min:1'
            ]);

            DB::beginTransaction();

            $no_bukti = trim($request->no_bukti);
            $status = $request->status;
            $periode = session('periode', date('m.Y'));
            $OO = trim($request->cbg);
            $username = Auth::user()->username ?? 'system';
            $ma = session('ma', 'TGZ');

            if (empty($OO) || $OO == $ma) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cabang harus diisi dengan benar!'
                ], 400);
            }

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

            $posted_check = DB::select("SELECT posted FROM {$OO}.perid WHERE kd_peri=?", [$periode]);
            $posted = $posted_check[0]->posted ?? 0;

            if ($posted == 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Closed Period'
                ], 400);
            }

            $total_qty = 0;
            $total_amount = 0;

            foreach ($request->details as $detail) {
                if (!empty($detail['kd_brg'])) {
                    $qty = floatval($detail['qty'] ?? 0);
                    $harga = floatval($detail['harga'] ?? 0);
                    $total = $qty * $harga;

                    $total_qty += $qty;
                    $total_amount += $total;
                }
            }

            if ($status == 'simpan') {
                if ($no_bukti == '+') {
                    try {
                        $no_bukti_result = DB::select(
                            "CALL {$OO}.NO_TRANSX('PT_Khusus', ?, ?, 'SPKO_DCTS', 'PT')",
                            [self::class, $OO]
                        );

                        if (empty($no_bukti_result)) {
                            throw new \Exception('Create NO.BUKTI bermasalah! x539');
                        }

                        $no_bukti = $no_bukti_result[0]->BUKTIX ?? null;

                        if (!$no_bukti) {
                            throw new \Exception('NO.BUKTI tidak berhasil digenerate');
                        }
                    } catch (\Exception $e) {
                        DB::rollback();
                        return response()->json([
                            'success' => false,
                            'message' => 'Gagal generate nomor bukti: ' . $e->getMessage()
                        ], 500);
                    }
                }

                DB::statement(
                    "INSERT INTO {$OO}.po_dc_ts (krm_eml, KODES, NAMAS, tgo, tgl_mulai, type, NO_BUKTI, TGL, PER,
                                       JTEMPO, TKK1, TKKS, FLAG, TOTAL_QTY, TOTAL, NETT, UTUH, NOTES,
                                       USRNM, TG_SMP, cbg, KS, GOLONGAN)
                     VALUES (1, '510C', 'ADIKARYA PANGAN FRESHINDO', ?, ?, 'DC', ?, ?, ?,
                             DATE(DATE_ADD(NOW(), INTERVAL 5 DAY)),
                             DATE(DATE_ADD(NOW(), INTERVAL 5 DAY)),
                             DATE(DATE_ADD(NOW(), INTERVAL 4 DAY)),
                             'PT', ?, ?, ?, 'U', ?, ?, NOW(), ?, 'Y', ?)",
                    [
                        date('Y-m-d'),
                        date('Y-m-d'),
                        $no_bukti,
                        $request->tgl,
                        $periode,
                        $total_qty,
                        $total_amount,
                        $total_amount,
                        $request->notes ?? '',
                        $username,
                        $OO,
                        $request->golongan
                    ]
                );
            } else {
                DB::statement(
                    "UPDATE {$OO}.po_dc_ts SET krm_eml=1, TGL=?, NOTES=?, TOTAL_QTY=?, TOTAL=?, NETT=?,
                                     USRNM=?, TG_SMP=NOW()
                     WHERE NO_BUKTI=?",
                    [
                        $request->tgl,
                        $request->notes ?? '',
                        $total_qty,
                        $total_amount,
                        $total_amount,
                        $username,
                        $no_bukti
                    ]
                );
            }

            $header_id_result = DB::select("SELECT no_id FROM {$OO}.po_dc_ts WHERE no_bukti=?", [$no_bukti]);
            $id = $header_id_result[0]->no_id ?? 0;

            if ($status == 'edit') {
                $existing_details = DB::select("SELECT no_id FROM {$OO}.pod_dc_ts WHERE no_bukti=?", [$no_bukti]);

                foreach ($existing_details as $existing) {
                    $found = false;
                    foreach ($request->details as $detail) {
                        if (isset($detail['no_id']) && $detail['no_id'] == $existing->no_id) {
                            $qty = floatval($detail['qty'] ?? 0);
                            $harga = floatval($detail['harga'] ?? 0);
                            $total = $qty * $harga;

                            DB::statement(
                                "UPDATE {$OO}.pod_dc_ts SET REC=?, KD_BRG=?, NA_BRG=?, KET_UK=?, KDLAKU=?,
                                                  ket_kem=?, QTY=?, HARGA=?, TOTAL=?, KET=?, SISA=QTY-KIRIM
                                 WHERE NO_ID=?",
                                [
                                    intval($detail['rec'] ?? 1),
                                    trim($detail['kd_brg'] ?? ''),
                                    trim($detail['na_brg'] ?? ''),
                                    trim($detail['ket_uk'] ?? ''),
                                    trim($detail['kdlaku'] ?? ''),
                                    trim($detail['ket_kem'] ?? ''),
                                    $qty,
                                    $harga,
                                    $total,
                                    trim($detail['notes'] ?? ''),
                                    $existing->no_id
                                ]
                            );
                            $found = true;
                            break;
                        }
                    }

                    if (!$found) {
                        DB::statement("DELETE FROM {$OO}.pod_dc_ts WHERE NO_ID=?", [$existing->no_id]);
                    }
                }
            }

            $rec = 1;
            foreach ($request->details as $detail) {
                if (!empty($detail['kd_brg'])) {
                    if (!isset($detail['no_id']) || $detail['no_id'] == 0) {
                        $qty = floatval($detail['qty'] ?? 0);
                        $harga = floatval($detail['harga'] ?? 0);
                        $total = $qty * $harga;

                        DB::statement(
                            "INSERT INTO {$OO}.pod_dc_ts (TGO, TGL_MULAI, cbg, NO_BUKTI, REC, PER, FLAG, KD_BRG,
                                                 NA_BRG, KET_UK, KDLAKU, ket_kem, QTY, SISA, TYPE, HARGA,
                                                 TOTAL, KET, ID)
                             VALUES (?, ?, ?, ?, ?, ?, 'PT', ?, ?, ?, ?, ?, ?, ?, 'DC', ?, ?, ?, ?)",
                            [
                                date('Y-m-d'),
                                date('Y-m-d'),
                                $OO,
                                $no_bukti,
                                $rec,
                                $periode,
                                trim($detail['kd_brg'] ?? ''),
                                trim($detail['na_brg'] ?? ''),
                                trim($detail['ket_uk'] ?? ''),
                                trim($detail['kdlaku'] ?? ''),
                                trim($detail['ket_kem'] ?? ''),
                                $qty,
                                $qty,
                                $harga,
                                $total,
                                trim($detail['notes'] ?? ''),
                                $id
                            ]
                        );
                    }
                    $rec++;
                }
            }

            DB::statement(
                "UPDATE {$OO}.pod_dc_ts, {$OO}.brg_dc_ts B
                 SET pod_dc_ts.pmin=0, pod_dc_ts.pmax=B.STOK_DC,
                     pod_dc_ts.ABL=IF(KDLAKU REGEXP '0|1' AND pod_dc_ts.QTY<=B.STOK_DC, 'GD', 'TK')
                 WHERE pod_dc_ts.KD_BRG=B.KD_BRG"
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Save Data Success'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . implode(', ', $e->validator->errors()->all())
            ], 422);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($no_bukti, Request $request)
    {
        $OO = $request->get('cbg', session('cbg', '01'));

        DB::beginTransaction();

        try {
            DB::statement("DELETE FROM {$OO}.po_dc_ts WHERE no_bukti=?", [$no_bukti]);
            DB::statement("DELETE FROM {$OO}.pod_dc_ts WHERE no_bukti=?", [$no_bukti]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function browse(Request $request)
    {
        $type = $request->get('type', 'barang');
        $q = $request->get('q', '');
        $OO = $request->get('cbg', session('cbg', '01'));
        $ma = session('ma', 'TGZ');
        $tipe = $request->get('tipe', '');

        if ($type == 'barang') {
            if (!empty($q)) {
                $data = DB::select(
                    "SELECT A.kd_brg, A.ket_uk, A.ket_kem, B.hb, A.NA_BRG, B.HB as harga, A.on_dc,
                            B.kdlaku, B.ak00 as toko, B.GAK00 as GUDANG, B.ak00 + B.GAK00 AS STOKX,
                            C.STOK_DC, A.TYPE as GOLONGAN,
                            IF(COALESCE(C.STOK_DC,0)>0,'YES','NO') AS ADA,
                            CONCAT(A.na_brg,' ',A.ket_uk,'  |  DC : ',COALESCE(C.STOK_DC,0)) XX
                     FROM {$ma}.brg A
                     INNER JOIN {$ma}.brgdt B ON A.KD_BRG=B.KD_BRG
                     LEFT JOIN {$OO}.brg_dc_ts C ON B.KD_BRG=C.KD_BRG
                     WHERE B.yer=YEAR(NOW()) AND A.TYPE=?
                       AND (A.KD_BRG LIKE ? OR A.NA_BRG LIKE ?)
                     LIMIT 50",
                    [$tipe, "%$q%", "%$q%"]
                );
            } else {
                $data = [];
            }
        } else {
            $data = [];
        }

        return response()->json($data);
    }

    public function getDetail(Request $request)
    {
        $type = $request->get('type', 'barang');
        $ma = session('ma', 'TGZ');
        $OO = $request->get('cbg', session('cbg', '01'));
        $tipe = $request->get('tipe', '');

        if ($type == 'barang') {
            $kd_brg = $request->get('kd_brg');

            $barang = DB::select(
                "SELECT A.kd_brg, A.ket_uk, A.ket_kem, B.hb, A.NA_BRG, B.HB as harga, A.on_dc,
                        B.kdlaku, B.ak00 as toko, B.GAK00 as GUDANG,
                        C.STOK_DC, A.TYPE as GOLONGAN,
                        IF(COALESCE(C.STOK_DC,0)>0,'YES','NO') AS ADA,
                        CONCAT(A.na_brg,' ',A.ket_uk,'  ') XX
                 FROM {$ma}.brg A
                 INNER JOIN {$ma}.brgdt B ON A.KD_BRG=B.KD_BRG
                 LEFT JOIN {$OO}.brg_dc_ts C ON B.KD_BRG=C.KD_BRG
                 WHERE B.yer=YEAR(NOW()) AND A.TYPE=? AND A.KD_BRG=?",
                [$tipe, $kd_brg]
            );

            if (!empty($barang)) {
                return response()->json([
                    'success' => true,
                    'exists' => true,
                    'data' => $barang[0]
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'exists' => false,
            'data' => null
        ]);
    }

    public function proses(Request $request)
    {
        try {
            $OO = trim($request->cbg);
            $ma = session('ma', 'TGZ');
            $kodes = trim($request->kodes);
            $lph1 = floatval($request->lph1);
            $lph2 = floatval($request->lph2);
            $sub1 = trim($request->sub1);
            $sub2 = trim($request->sub2);
            $tipe = trim($request->tipe);
            $butuh = floatval($request->butuh);

            $data = DB::select(
                "SELECT A.kd_brg, A.ket_uk, A.ket_kem, B.hb, A.NA_BRG, B.HB as harga, A.on_dc,
                        B.kdlaku, B.ak00 as toko, B.GAK00 as GUDANG, B.ak00 + B.GAK00 AS STOKX,
                        C.STOK_DC, A.TYPE as GOLONGAN, B.LPH,
                        CONCAT(A.na_brg,' ',A.ket_uk,'  ') XX,
                        RIGHT(TRIM(A.KET_KEM), LENGTH(TRIM(A.KET_KEM)) - LOCATE('/', TRIM(A.KET_KEM))) as KEM
                 FROM {$OO}.brg A
                 INNER JOIN {$OO}.brgdt B ON A.KD_BRG=B.KD_BRG
                 LEFT JOIN {$OO}.brg_dc_ts C ON B.KD_BRG=C.KD_BRG
                 WHERE B.yer=YEAR(NOW()) AND A.sub BETWEEN ? AND ?
                   AND A.LPH BETWEEN ? AND ? AND TRIM(B.TD_OD)='' AND A.ON_DC=1
                   AND (SELECT KODE_DC FROM {$OO}.SUP WHERE KODES=A.SUPP LIMIT 1) = ? AND A.TYPE=?",
                [$sub1, $sub2, $lph1, $lph2, $kodes, $tipe]
            );

            $result = [];

            foreach ($data as $row) {
                $jo = ($row->LPH * $butuh) - $row->STOKX;
                $jo = intval($jo);
                $jo = $jo / floatval($row->KEM);

                $ceiling = DB::select("SELECT CEILING(?) as ZZ", [$jo]);
                $jo = $ceiling[0]->ZZ;
                $jo = $jo * floatval($row->KEM);

                if ($jo > 0) {
                    $result[] = [
                        'kd_brg' => $row->kd_brg,
                        'na_brg' => $row->NA_BRG,
                        'ket_uk' => $row->ket_uk,
                        'ket_kem' => $row->ket_kem,
                        'kdlaku' => $row->kdlaku,
                        'qty' => $jo,
                        'harga' => $row->harga,
                        'total' => $jo * $row->harga,
                        'stok' => $row->STOK_DC ?? 0
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function print(Request $request)
    {
        $no_bukti = $request->no_bukti;
        $OO = $request->get('cbg', session('cbg', '01'));

        $data = DB::select(
            "SELECT PO.CBG, po.no_bukti, po.KODES, po.NAMAS, po.TGL, po.JTEMPO,
                    po.TELP, LEFT(pod.KD_BRG,3) AS NO_SUB, RIGHT(pod.KD_BRG,4) AS NO_ITEM,
                    CONCAT(pod.NA_BRG,' ',pod.ket_uk) as na_brg, pod.pmin,
                    pod.pmax, pod.qty, pod.ket_kem, po.na_file
             FROM {$OO}.po_dc_ts po, {$OO}.pod_dc_ts pod
             WHERE pod.FLAG='PT' AND PO.NO_BUKTI=POD.NO_BUKTI AND po.no_bukti=?",
            [$no_bukti]
        );

        return response()->json(['data' => $data]);
    }

    public function export(Request $request)
    {
        try {
            $no_bukti = $request->no_bukti;
            $OO = $request->get('cbg', session('cbg', '01'));

            DB::statement("CALL dck.gd_orderan_dc_ts('PROSES_SP_KHUSUS', ?, '', ?)", [$OO, $no_bukti]);

            return response()->json([
                'success' => true,
                'message' => 'Export berhasil'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Export gagal: ' . $e->getMessage()
            ], 500);
        }
    }
}
