<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TSPKOKeTGZController extends Controller
{
    public function index()
    {
        $periode = session('periode', date('m.Y'));
        $ma = session('ma', 'TGZ');
        $cbg = session('cbg', '01');

        try {
            $cabang = DB::select("SELECT * FROM toko WHERE STA IN ('MA','CB','DC') ORDER BY NO_ID ASC");
            if (empty($cabang)) {
                $cabang = [];
            }
        } catch (\Exception $e) {
            $cabang = [];
        }

        return view('otranskasi_SPKO_Ke_TGZ.index', compact('cabang', 'periode', 'ma', 'cbg'));
    }

    public function getSPKOKeTGZ(Request $request)
    {
        try {
            Log::info('TSPKOKeTGZ getSPKOKeTGZ() started');

            $periode = session('periode', date('m.Y'));
            $ma = session('ma', 'TGZ');
            $flag = session('flag', session('cbg', '01'));
            $OO = $request->get('cbg', $flag);

            Log::info('TSPKOKeTGZ parameters', [
                'periode' => $periode,
                'ma' => $ma,
                'flag' => $flag,
                'OO' => $OO
            ]);

            // Use Query Builder instead of raw SQL for better DataTables support
            if ($flag == $ma) {
                $query = DB::table($OO . '.po')
                    ->select('*')
                    ->where('per', $periode)
                    ->where('FLAG', 'PZ')
                    ->where('type', 'SK')
                    ->orderBy('NO_BUKTI', 'DESC');
            } else {
                $query = DB::table($OO . '.po')
                    ->select('*')
                    ->where('per', $periode)
                    ->where('FLAG', 'PZ')
                    ->where('type', 'SK')
                    ->where('cbg', $flag)
                    ->orderBy('NO_BUKTI', 'DESC');
            }

            $count = DB::table(DB::raw("({$query->toSql()}) as sub"))
                ->mergeBindings($query)
                ->count();

            Log::info('TSPKOKeTGZ query executed', ['count' => $count]);

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
                ->addColumn('action', function ($row) {
                    $btnEdit = '<button onclick="editData(\'' . $row->NO_BUKTI . '\', \'' . ($row->CBG ?? '') . '\')" class="btn btn-sm btn-primary" title="Edit"><i class="fas fa-edit"></i></button>';
                    $btnPrint = '<button onclick="printData(\'' . $row->NO_BUKTI . '\', \'' . ($row->CBG ?? '') . '\')" class="btn btn-sm btn-info ml-1" data-action="print" data-id="' . $row->NO_BUKTI . '" title="Print"><i class="fas fa-print"></i></button>';
                    return $btnEdit . ' ' . $btnPrint;
                })
                ->rawColumns(['action'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('Error in TSPKOKeTGZ getSPKOKeTGZ: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'draw' => $request->input('draw', 0),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $e->getMessage()
            ], 200);
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

            try {
                $cabang = DB::select("SELECT * FROM toko WHERE STA IN ('MA','CB','DC') ORDER BY NO_ID ASC");
                if (empty($cabang)) {
                    $cabang = [];
                }
            } catch (\Exception $e) {
                $cabang = [];
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
                    'posted' => 0
                ],
                'detail' => [],
                'periode' => $periode,
                'ma' => $ma,
                'cbg' => $OO,
                'cabang' => $cabang,
                'posted' => 0,
                'error' => null
            ];

            if ($status == 'simpan') {
                $time_check = DB::select("SELECT IF(TIME_FORMAT(TIME(NOW()),'%H')<11,'NO','YES') as OK");
                $time_ok = $time_check[0]->OK ?? 'YES';

                if ($time_ok == 'NO') {
                    $data['error'] = 'Tunggu jam 11 keatas, TGZ masih proses SO!';
                    return view('otranskasi_SPKO_Ke_TGZ.edit', $data);
                }

                $posted_check = DB::select("SELECT posted FROM {$ma}.perid WHERE kd_peri=?", [$periode]);
                $posted = $posted_check[0]->posted ?? 0;

                if ($posted == 1) {
                    $data['error'] = 'Closed Period';
                    return view('otranskasi_SPKO_Ke_TGZ.edit', $data);
                }
            }

            if ($status == 'edit' && $no_bukti && $no_bukti != '+') {
                $header = DB::select(
                    "SELECT * FROM {$OO}.po
                 WHERE no_bukti=? AND FLAG='PZ'
                 ORDER BY NO_BUKTI",
                    [$no_bukti]
                );

                if (!empty($header)) {
                    $detail = DB::select(
                        "SELECT * FROM {$OO}.POD
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

            return view('otranskasi_SPKO_Ke_TGZ.edit', $data);
        } catch (\Exception $e) {
            try {
                $cabang = DB::select("SELECT * FROM toko WHERE STA IN ('MA','CB','DC') ORDER BY NO_ID ASC");
                if (empty($cabang)) {
                    $cabang = [];
                }
            } catch (\Exception $e) {
                $cabang = [];
            }

            return view('otranskasi_SPKO_Ke_TGZ.edit', [
                'no_bukti' => '+',
                'status' => 'simpan',
                'header' => (object)[
                    'NO_BUKTI' => '+',
                    'TGL' => date('Y-m-d'),
                    'CBG' => '',
                    'NOTES' => '',
                    'TOTAL_QTY' => 0,
                    'TOTAL' => 0,
                    'posted' => 0
                ],
                'detail' => [],
                'periode' => session('periode', date('m.Y')),
                'ma' => session('ma', 'TGZ'),
                'cbg' => session('cbg', '01'),
                'cabang' => $cabang,
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
                            "CALL {$OO}.NO_TRANSX('PZGUDANG', ?, ?, 'SPKO_TGZ', 'PZ')",
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
                    "INSERT INTO {$OO}.PO (krm_eml, KODES, NAMAS, tgo, tgl_mulai, type, NO_BUKTI, TGL, PER,
                                       JTEMPO, TKK1, TKKS, FLAG, TOTAL_QTY, TOTAL, NETT, UTUH, NOTES,
                                       USRNM, TG_SMP, cbg)
                 VALUES (1, 'AAA', 'TGZ', ?, ?, 'SK', ?, ?, ?,
                         DATE(DATE_ADD(NOW(), INTERVAL 5 DAY)),
                         DATE(DATE_ADD(NOW(), INTERVAL 5 DAY)),
                         DATE(DATE_ADD(NOW(), INTERVAL 4 DAY)),
                         'PZ', ?, ?, ?, 'U', ?, ?, NOW(), ?)",
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
                        $OO
                    ]
                );
            } else {
                DB::statement(
                    "UPDATE {$OO}.PO SET krm_eml=1, TGL=?, NOTES=?, TOTAL_QTY=?, TOTAL=?, NETT=?,
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

            $header_id_result = DB::select("SELECT no_id FROM {$OO}.po WHERE no_bukti=?", [$no_bukti]);
            $id = $header_id_result[0]->no_id ?? 0;

            if ($status == 'edit') {
                $existing_details = DB::select("SELECT no_id FROM {$OO}.pod WHERE no_bukti=?", [$no_bukti]);

                foreach ($existing_details as $existing) {
                    $found = false;
                    foreach ($request->details as $detail) {
                        if (isset($detail['no_id']) && $detail['no_id'] == $existing->no_id) {
                            $qty = floatval($detail['qty'] ?? 0);
                            $harga = floatval($detail['harga'] ?? 0);
                            $total = $qty * $harga;

                            DB::statement(
                                "UPDATE {$OO}.POD SET REC=?, KD_BRG=?, NA_BRG=?, KET_UK=?, KDLAKU=?,
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
                        DB::statement("DELETE FROM {$OO}.POD WHERE NO_ID=?", [$existing->no_id]);
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
                            "INSERT INTO {$OO}.POD (TGO, TGL_MULAI, cbg, NO_BUKTI, REC, PER, FLAG, KD_BRG,
                                                 NA_BRG, KET_UK, KDLAKU, ket_kem, QTY, SISA, TYPE, HARGA,
                                                 TOTAL, KET, ID)
                         VALUES (?, ?, ?, ?, ?, ?, 'PZ', ?, ?, ?, ?, ?, ?, ?, 'SK', ?, ?, ?, ?)",
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

                        DB::statement(
                            "UPDATE {$OO}.BRGDT SET PSN='*', TGL_PSN=NOW()
                         WHERE KD_BRG=? AND CBG=? AND yer=YEAR(NOW())",
                            [trim($detail['kd_brg'] ?? ''), $OO]
                        );
                    }
                    $rec++;
                }
            }

            DB::statement(
                "UPDATE {$OO}.pod
             INNER JOIN (
                 SELECT A.KD_BRG, A.NA_BRG, B.KDLAKU, A.qty QTYX,
                        B.AK00 TK, B.GAK00 GD,
                        IF(B.KDLAKU REGEXP '0|1','GD','TK') KDX,
                        (SELECT IF(KDX='GD' AND QTYX<=GD,'GD','TK')) AMBIL
                 FROM {$OO}.pod A
                 LEFT JOIN {$ma}.brgdt B ON A.KD_BRG=B.KD_BRG
                 WHERE A.flag='PZ' AND A.TYPE='SK' AND A.TGO=CURDATE()
             ) B ON pod.KD_BRG=B.KD_BRG
             SET pod.pmin=B.TK, pod.pmax=B.GD, pod.ABL=B.AMBIL
             WHERE pod.flag='PZ'"
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
            DB::statement("DELETE FROM {$OO}.po WHERE no_bukti=?", [$no_bukti]);
            DB::statement("DELETE FROM {$OO}.pod WHERE no_bukti=?", [$no_bukti]);

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

        if ($type == 'barang') {
            if (!empty($q)) {
                $data = DB::select(
                    "SELECT A.kd_brg, A.ket_uk, A.ket_kem, B.hb, A.NA_BRG, B.HB as harga,
                            B.kdlaku, B.ak00 as toko, B.GAK00 as GUDANG,
                            IF(B.KDLAKU='4', IF(B.AK00>0,'YES','NO'),
                               IF(B.KDLAKU REGEXP '0|1|6|8|3', IF(B.AK00+B.GAK00>0,'YES','NO'),'NO')) AS ADA,
                            CONCAT(A.na_brg,' ',A.ket_uk,'  |  TK : ',toko,'  |  GD : ',GUDANG) XX
                     FROM {$ma}.brg A, {$ma}.brgdt B
                     WHERE A.KD_BRG=B.KD_BRG
                       AND B.yer=YEAR(NOW())
                       AND (A.KD_BRG LIKE ? OR A.NA_BRG LIKE ?)
                     LIMIT 50",
                    ["%$q%", "%$q%"]
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

        if ($type == 'barang') {
            $kd_brg = $request->get('kd_brg');

            $barang = DB::select(
                "SELECT A.kd_brg, A.ket_uk, A.ket_kem, B.hb, A.NA_BRG, B.HB as harga,
                        B.kdlaku, B.ak00 as toko, B.GAK00 as GUDANG,
                        IF(B.KDLAKU='4', IF(B.AK00>0,'YES','NO'),
                           IF(B.KDLAKU REGEXP '0|1|6|8|3', IF(B.AK00+B.GAK00>0,'YES','NO'),'NO')) AS ADA,
                        CONCAT(A.na_brg,' ',A.ket_uk,'  |  TK : ',toko,'  |  GD : ',GUDANG) XX
                 FROM {$ma}.brg A, {$ma}.brgdt B
                 WHERE A.KD_BRG=B.KD_BRG
                   AND B.yer=YEAR(NOW())
                   AND A.KD_BRG=?",
                [$kd_brg]
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

    public function printSPKOKeTGZ(Request $request)
    {
        $no_bukti = $request->no_bukti;
        $OO = $request->get('cbg', session('cbg', '01'));

        $data = DB::select(
            "SELECT PO.CBG, po.no_bukti, po.KODES, po.NAMAS, po.TGL, po.JTEMPO,
                    po.TELP, LEFT(pod.KD_BRG,3) AS NO_SUB, RIGHT(pod.KD_BRG,4) AS NO_ITEM,
                    CONCAT(pod.NA_BRG,' ',pod.ket_uk) as na_brg, pod.pmin,
                    pod.pmax, pod.qty, pod.ket_kem
             FROM {$OO}.PO, {$OO}.pod
             WHERE pod.FLAG='PZ' AND PO.NO_BUKTI=POD.NO_BUKTI AND po.no_bukti=?",
            [$no_bukti]
        );

        return response()->json(['data' => $data]);
    }
}
