<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PHProgramPromosiHadiahController extends Controller
{
    /**
     * Display index page
     */
    public function index()
    {
        return view('promo_program_hadiah.index');
    }

    /**
     * Get data for datatable
     */
    public function getProgramPromosiHadiah(Request $request)
    {
        $per = session('periode', date('m.Y'));

        $periode = $per['bulan'] . '/' . $per['tahun'];   
        // Clean up temporary data
        DB::statement("CALL hdh_delhshv");

        $query = DB::select(
            "SELECT lbhijau.no_id, lbhijau.no_bukti, lbhijau.kd_prm, lbhijau.tgl,
                lbhijau.ket, lbhijau.kodes, lbhijau.namas, lbhijau.tg_mulai,
                lbhijau.tg_akhir, lbhijau.type, lbhijau.qty_beli, lbhijau.rp_beli,
                lbhijau.kelipatan, lbhijau.kondisi, lbhijau.jns, lbhijau.maxh,
                (SELECT GROUP_CONCAT(na_brgh SEPARATOR ', ')
                 FROM lbhijaud
                 WHERE lbhijaud.no_bukti = lbhijau.no_bukti
                 LIMIT 3) as nama_hadiah,
                0 as stok
         FROM lbhijau
         WHERE lbhijau.per = ? OR lbhijau.tg_akhir >= DATE(NOW())
         ORDER BY lbhijau.no_id DESC",
            [$periode]
        );

        return Datatables::of(collect($query))
            ->addIndexColumn()
            ->editColumn('tgl', function ($row) {
                return $row->tgl ? date('d/m/Y', strtotime($row->tgl)) : '';
            })
            ->editColumn('tg_mulai', function ($row) {
                return $row->tg_mulai ? date('d/m/Y', strtotime($row->tg_mulai)) : '';
            })
            ->editColumn('tg_akhir', function ($row) {
                return $row->tg_akhir ? date('d/m/Y', strtotime($row->tg_akhir)) : '';
            })
            ->editColumn('type', function ($row) {
                $types = ['H' => 'HIJAU', 'U' => 'CASH', 'V' => 'VARIAN', 'B' => 'BANK'];
                $typeText = $types[$row->type] ?? $row->type;
                $badges = [
                    'H' => 'success',
                    'U' => 'info',
                    'V' => 'warning',
                    'B' => 'primary'
                ];
                $badge = $badges[$row->type] ?? 'secondary';
                return '<span class="badge badge-' . $badge . '">' . $typeText . '</span>';
            })
            ->editColumn('qty_beli', function ($row) {
                return number_format($row->qty_beli, 0);
            })
            ->editColumn('rp_beli', function ($row) {
                return number_format($row->rp_beli, 2);
            })
            ->editColumn('nama_hadiah', function ($row) {
                return $row->nama_hadiah ?: '-';
            })
            ->editColumn('stok', function ($row) {
                return number_format($row->stok, 0);
            })
            ->addColumn('action', function ($row) {
                $btnEdit = '<button onclick="editData(\'' . $row->kd_prm . '\')" class="btn btn-sm btn-primary" title="Edit"><i class="fas fa-edit"></i></button>';
                return $btnEdit;
            })
            ->rawColumns(['action', 'type'])
            ->make(true);
    }

    /**
     * Show form for create/edit
     */
    public function edit(Request $request)
    {
        $kd_prm = $request->get('kd_prm');
        $status = $request->get('status', 'simpan');

        $data = [
            'no_bukti' => '+',
            'status' => $status,
            'header' => null,
            'detail' => [],
            'periode' => session('periode', date('m.Y')),
            'banks' => $this->getBankList()
        ];

        if ($status == 'edit' && $kd_prm) {
            $header = DB::select(
                "SELECT no_id, no_bukti, kd_prm, tgl, ket, kodes, namas,
                        tg_mulai, tg_akhir, jm_mulai, jm_akhir, type,
                        qty_beli, rp_beli, kelipatan, kondisi, jns, maxh,
                        nkartu, tgz, tmm, sop
                 FROM lbhijau
                 WHERE kd_prm = ?
                 ORDER BY kd_prm",
                [$kd_prm]
            );

            if (!empty($header)) {
                $detail = DB::select(
                    "SELECT no_id, no_bukti, rec, kd_brgh, na_brgh, qty, kd_prm, id
                     FROM lbhijaud
                     WHERE no_bukti = ?
                     ORDER BY rec",
                    [$header[0]->no_bukti]
                );

                $data['header'] = $header[0];
                $data['detail'] = $detail;
                $data['no_bukti'] = $header[0]->no_bukti;
                $data['kd_prm'] = $kd_prm;
            }
        }

        return view('promo_program_hadiah.edit', $data);
    }

    /**
     * Store/Update
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'tgl' => 'required|date',
            'tg_mulai' => 'required|date',
            'tg_akhir' => 'required|date',
            'jm_mulai' => 'required',
            'jm_akhir' => 'required',
            'cbtype' => 'required',
            'kodes' => 'required',
            'namas' => 'required',
            'kd_prm' => 'required'
        ]);

        DB::beginTransaction();

        try {
            $no_bukti = trim($request->no_bukti);
            $status = $request->status;
            $per = session('periode', date('m.Y'));

        $periode = $per['bulan'] . '/' . $per['tahun'];        
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

            // Validate
            $validation = $this->validatePromosiData($request, $periode, $status);
            if (!$validation['success']) {
                return response()->json($validation, 400);
            }

            // Type mapping
            $type_map = ['HIJAU' => 'H', 'CASH' => 'U', 'VARIAN' => 'V', 'BANK' => 'B'];
            $txttype = $type_map[$request->cbtype] ?? 'H';

            // Kondisi
            $kondisi = '';
            if ($request->cbkondisi == 'TOTAL SEMUA BELANJA') {
                $kondisi = 'H';
            } elseif ($request->cbkondisi == 'TOTAL BELANJA BRG PROMO') {
                $kondisi = 'D';
            }

            // Jns
            $jns = '';
            if ($request->cbjns == 'DAN') {
                $jns = 'AND';
            } elseif ($request->cbjns == 'ATAU') {
                $jns = 'OR';
            }

            // Kelipatan
            $kelipatan = $request->cbkeli ? 1 : 0;
            $maxh = $request->cbkeli ? floatval($request->txtmaxh ?? 0) : 0;

            // Flags
            $tgz = $request->ctgz ? 1 : 0;
            $tmm = $request->ctmm ? 1 : 0;
            $sop = $request->csop ? 1 : 0;

            if ($status == 'simpan') {
                if ($no_bukti == '+') {
                    $no_bukti = $this->generateNoBukti($periode, $cbg);
                }

                // Check kd_prm exists
                $check_kd = DB::select("SELECT kd_prm FROM lbhijau WHERE kd_prm = ?", [$request->kd_prm]);
                if (!empty($check_kd)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Kode Promosi telah Di pakai'
                    ], 400);
                }

                // Insert header
                DB::statement(
                    "INSERT INTO lbhijau
                    (maxh, no_bukti, kd_prm, tgl, qty_beli, rp_beli, kelipatan, kondisi, jns,
                     type, kodes, namas, tg_mulai, tg_akhir, jm_mulai, jm_akhir, ket,
                     usrnm, per, nkartu, tgz, tmm, sop, tg_smp)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
                    [
                        $maxh,
                        $no_bukti,
                        $request->kd_prm,
                        $request->tgl,
                        floatval($request->txtqtybl ?? 0),
                        floatval($request->txttotalbl ?? 0),
                        $kelipatan,
                        $kondisi,
                        $jns,
                        $txttype,
                        $request->kodes,
                        $request->namas,
                        $request->tg_mulai,
                        $request->tg_akhir,
                        $request->jm_mulai,
                        $request->jm_akhir,
                        $request->ket ?? '',
                        $username,
                        $periode,
                        $request->cbbank ?? '',
                        $tgz,
                        $tmm,
                        $sop
                    ]
                );

                DB::statement("CALL hdh_kode(?)", [$request->kd_prm]);
            } else {
                // Update
                DB::statement(
                    "UPDATE lbhijau SET
                     maxh=?, qty_beli=?, rp_beli=?, kelipatan=?, kondisi=?, jns=?, type=?, ket=?,
                     kodes=?, namas=?, kd_prm=?, tg_mulai=?, tg_akhir=?, jm_mulai=?, jm_akhir=?,
                     usrnm=?, per=?, nkartu=?, tgz=?, tmm=?, sop=?
                     WHERE no_bukti=?",
                    [
                        $maxh,
                        floatval($request->txtqtybl ?? 0),
                        floatval($request->txttotalbl ?? 0),
                        $kelipatan,
                        $kondisi,
                        $jns,
                        $txttype,
                        $request->ket ?? '',
                        $request->kodes,
                        $request->namas,
                        $request->kd_prm,
                        $request->tg_mulai,
                        $request->tg_akhir,
                        $request->jm_mulai,
                        $request->jm_akhir,
                        $username,
                        $periode,
                        $request->cbbank ?? '',
                        $tgz,
                        $tmm,
                        $sop,
                        $no_bukti
                    ]
                );

                DB::statement("CALL hdh_kode(?)", [$request->kd_prm]);
            }

            // Get header ID
            $header_id_result = DB::select("SELECT no_id FROM lbhijau WHERE no_bukti=?", [$no_bukti]);
            $id = $header_id_result[0]->no_id ?? 0;

            // Handle detail updates
            if ($status == 'edit') {
                $existing_details = DB::select("SELECT no_id FROM lbhijaud WHERE no_bukti = ?", [$no_bukti]);

                foreach ($existing_details as $existing) {
                    $found = false;
                    foreach ($request->details ?? [] as $detail) {
                        if (isset($detail['no_id']) && $detail['no_id'] == $existing->no_id) {
                            DB::statement(
                                "UPDATE lbhijaud
                                 SET rec=?, kd_brgh=?, na_brgh=?, qty=?
                                 WHERE no_id=?",
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
                        DB::statement("DELETE FROM lbhijaud WHERE no_id = ?", [$existing->no_id]);
                    }
                }
            }

            // Insert new details
            $rec = 1;
            foreach ($request->details ?? [] as $detail) {
                if (!empty($detail['kd_brgh'])) {
                    if (!isset($detail['no_id']) || $detail['no_id'] == 0) {
                        DB::statement(
                            "INSERT INTO lbhijaud (no_bukti, rec, kd_brgh, na_brgh, qty, kd_prm, id)
                             VALUES (?, ?, ?, ?, ?, ?, ?)",
                            [
                                $no_bukti,
                                $rec,
                                trim($detail['kd_brgh']),
                                trim($detail['na_brgh'] ?? ''),
                                floatval($detail['qty'] ?? 0),
                                $request->kd_prm,
                                $id
                            ]
                        );
                    }
                    $rec++;
                }
            }

            // Replicate to branches
            $branches = DB::select(
                "SELECT TRIM(kode) as cbg FROM toko WHERE kode<>? AND type2<>''",
                [$cbg]
            );

            foreach ($branches as $branch) {
                $cab = $branch->cbg;

                DB::statement("DELETE FROM {$cab}.lbhijaud WHERE no_bukti=?", [$no_bukti]);
                DB::statement("DELETE FROM {$cab}.lbhijau WHERE no_bukti=?", [$no_bukti]);

                DB::statement(
                    "INSERT INTO {$cab}.lbhijau
                     (maxh, no_bukti, kd_prm, tgl, qty_beli, rp_beli, kelipatan, kondisi, jns,
                      type, kodes, namas, tg_mulai, tg_akhir, jm_mulai, jm_akhir, ket,
                      usrnm, per, nkartu, tgz, tmm, sop, tg_smp)
                     SELECT maxh, no_bukti, kd_prm, tgl, qty_beli, rp_beli, kelipatan, kondisi, jns,
                            type, kodes, namas, tg_mulai, tg_akhir, jm_mulai, jm_akhir, ket,
                            usrnm, per, nkartu, tgz, tmm, sop, tg_smp
                     FROM lbhijau WHERE no_bukti=?",
                    [$no_bukti]
                );

                DB::statement(
                    "INSERT INTO {$cab}.lbhijaud (no_bukti, rec, kd_brgh, na_brgh, qty, kd_prm, id)
                     SELECT no_bukti, rec, kd_brgh, na_brgh, qty, kd_prm, id
                     FROM lbhijaud WHERE no_bukti=?",
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
     * Browse data
     */
    public function browse(Request $request)
    {
        $type = $request->get('type', 'supplier');
        $q = $request->get('q', '');

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
                $data = DB::select("SELECT kodes, namas FROM sup ORDER BY kodes LIMIT 50");
            }
        } elseif ($type == 'barang') {
            $kd_prm = $request->get('kd_prm', '');
            $txttype = $request->get('txttype', 'H');
            $hx_field = $txttype == 'V' ? 'hv' : 'hs';

            if (!empty($kd_prm)) {
                $data = DB::select(
                    "SELECT b.kd_brg, b.na_brg, b.ket_uk, b.ket_kem
                     FROM masks a, brg b
                     WHERE a.kd_brg = b.kd_brg AND a.{$hx_field} = ?
                     ORDER BY b.kd_brg
                     LIMIT 200",
                    [$kd_prm]
                );
            } else {
                $data = [];
            }
        } elseif ($type == 'account') {
            $data = DB::select(
                "SELECT acno, nama
                 FROM account
                 WHERE acno LIKE '1%'
                 ORDER BY acno
                 LIMIT 50"
            );
        } else {
            $data = [];
        }

        return response()->json($data);
    }

    /**
     * Get detail
     */
    public function getDetail(Request $request)
    {
        $type = $request->get('type', 'supplier');

        if ($type == 'supplier') {
            $kodes = $request->get('kodes');
            $supplier = DB::select("SELECT kodes, namas FROM sup WHERE kodes = ? ORDER BY kodes", [$kodes]);

            if (!empty($supplier)) {
                return response()->json([
                    'success' => true,
                    'exists' => true,
                    'data' => $supplier[0]
                ]);
            }
        } elseif ($type == 'barang') {
            $kd_brgh = $request->get('kd_brgh');
            $barang = DB::select("SELECT kd_brgh, na_brgh FROM brgh WHERE kd_brgh = TRIM(?)", [$kd_brgh]);

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

    /**
     * Update masks
     */
    public function updateMasks(Request $request)
    {
        $this->validate($request, [
            'txtsub1' => 'required',
            'txtsub2' => 'required',
            'txtsup1' => 'required',
            'txtsup2' => 'required',
            'txtkode' => 'required',
            'txttype' => 'required'
        ]);

        DB::beginTransaction();

        try {
            $hx_field = $request->txttype == 'V' ? 'hv' : 'hs';

            $branches = DB::select(
                "SELECT TRIM(kode) as cbg FROM toko WHERE sta IN ('MA', 'CB') ORDER BY no_id ASC"
            );

            foreach ($branches as $branch) {
                $cab = $branch->cbg;

                DB::statement(
                    "UPDATE {$cab}.masks SET masks.{$hx_field} = ?
                     WHERE masks.sub >= ? AND masks.sub <= ?
                       AND masks.supp >= ? AND masks.supp <= ?
                       AND IF(? = '', 1=1, masks.kdbar = ?)",
                    [
                        $request->txtkode,
                        $request->txtsub1,
                        $request->txtsub2,
                        $request->txtsup1,
                        $request->txtsup2,
                        $request->txtnoitem ?? '',
                        $request->txtnoitem ?? ''
                    ]
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Update masks berhasil'
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
     * Clear masks
     */
    public function clearMasks(Request $request)
    {
        $this->validate($request, [
            'txtkode' => 'required',
            'txttype' => 'required',
            'no_bukti' => 'required'
        ]);

        DB::beginTransaction();

        try {
            $username = Auth::user()->username ?? 'system';

            DB::statement(
                "INSERT INTO errorprg (errmsg, no_bukti, dterr, usrnm)
                 SELECT brg, no_bukti, NOW(), ?
                 FROM lbhijau WHERE no_bukti = ?",
                [$username, $request->no_bukti]
            );

            $hx_field = $request->txttype == 'V' ? 'hv' : 'hs';

            $branches = DB::select("SELECT TRIM(kode) as cbg FROM toko WHERE type2 <> ''");

            foreach ($branches as $branch) {
                $cab = $branch->cbg;
                DB::statement("UPDATE {$cab}.masks SET {$hx_field} = '' WHERE {$hx_field} = ?", [$request->txtkode]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Clear promo code berhasil'
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
     * Delete
     */
    public function destroy($no_bukti)
    {
        $flag = session('flag', 'TGZ');

        if ($flag != 'TGZ') {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses untuk menghapus'
            ], 403);
        }

        DB::beginTransaction();

        try {
            $branches = DB::select("SELECT TRIM(kode) as cbg FROM toko WHERE sta IN ('MA', 'CB')");

            foreach ($branches as $branch) {
                $cab = $branch->cbg;
                DB::statement("DELETE FROM {$cab}.lbhijaud WHERE no_bukti = ?", [$no_bukti]);
                DB::statement("DELETE FROM {$cab}.lbhijau WHERE no_bukti = ?", [$no_bukti]);
            }

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

    /**
     * Print
     */
    public function printPromosi(Request $request)
    {
        $sub1 = $request->get('sub1', '');
        $sub2 = $request->get('sub2', '');

        try {
            $data = DB::select("CALL hdh_prnt(?, ?)", [$sub1, $sub2]);

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search
     */
    public function searchPromosi(Request $request)
    {
        $type = $request->get('type', 'kode');
        $search = trim($request->get('search', ''));

        try {
            if ($type == 'kode') {
                $data = DB::select(
                    "SELECT lbhijau.no_bukti, lbhijau.kd_prm, lbhijau.ket, lbhijau.kodes, lbhijau.namas,
                            lbhijau.tg_mulai, lbhijau.tg_akhir, lbhijau.type,
                            lbhijaud.kd_brgh, lbhijaud.na_brgh
                     FROM lbhijau, lbhijaud
                     WHERE lbhijaud.no_bukti = lbhijau.no_bukti
                       AND lbhijaud.kd_brgh = ?
                     ORDER BY lbhijau.no_bukti ASC, lbhijaud.kd_brgh ASC",
                    [$search]
                );
            } else {
                $data = DB::select(
                    "SELECT A.no_bukti, A.kd_prm, A.ket, A.kodes, A.namas,
                            A.tg_mulai, A.tg_akhir, A.type, B.kd_brgh, B.na_brgh
                     FROM lbhijau A, lbhijaud B
                     WHERE A.no_bukti = B.no_bukti
                       AND B.na_brgh LIKE ?
                     ORDER BY A.no_id ASC, B.kd_brgh ASC",
                    ['%' . $search . '%']
                );
            }

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mencari data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate
     */
    private function validatePromosiData($request, $periode, $status)
    {
        if ($status == 'simpan') {
            $tgl = Carbon::parse($request->tgl);
            $monthz = str_pad($tgl->month, 2, '0', STR_PAD_LEFT);
            $periode_month = substr($periode, 0, 2);

            if ($monthz != $periode_month) {
                return [
                    'success' => false,
                    'message' => 'Periode tidak sama dengan tanggal!'
                ];
            }
        }

        if ($request->jm_mulai == '00:00:00') {
            return ['success' => false, 'message' => 'Filter Jam Mulai, tidak boleh kosong!!'];
        }

        if ($request->jm_akhir == '00:00:00') {
            return ['success' => false, 'message' => 'Filter Jam Selesai, tidak boleh kosong!!'];
        }

        $tg_mulai = Carbon::parse($request->tg_mulai);
        $tg_akhir = Carbon::parse($request->tg_akhir);

        if ($tg_akhir->lt($tg_mulai)) {
            return ['success' => false, 'message' => 'Tanggal Selesai Harus Lebih Tinggi Tanggal Mulai!!'];
        }

        if ($request->cbtype == 'BANK') {
            if (empty($request->cbbank)) {
                return ['success' => false, 'message' => 'Bank Tidak Boleh Kosong !!'];
            }
            if (floatval($request->txttotalbl ?? 0) <= 0) {
                return ['success' => false, 'message' => 'Jumlah beli tidak boleh 0 !!'];
            }
        } elseif ($request->cbtype == 'HIJAU') {
            if (floatval($request->txtqtybl ?? 0) <= 0) {
                return ['success' => false, 'message' => 'Jumlah beli tidak boleh 0 !!'];
            }
        } elseif ($request->cbtype == 'VARIAN') {
            if (floatval($request->txtqtybl ?? 0) <= 0) {
                return ['success' => false, 'message' => 'Jumlah beli tidak boleh 0 !!'];
            }
            if (floatval($request->txttotalbl ?? 0) <= 0) {
                return ['success' => false, 'message' => 'Total beli tidak boleh 0 !!'];
            }
            if (empty($request->cbjns)) {
                return ['success' => false, 'message' => 'Isi jenis hubungannya !!'];
            }
            if (empty($request->cbkondisi)) {
                return ['success' => false, 'message' => 'Isi kondisi pembeliannya !!'];
            }
        } elseif ($request->cbtype == 'CASH') {
            if (floatval($request->txttotalbl ?? 0) <= 0) {
                return ['success' => false, 'message' => 'Total beli tidak boleh 0 !!'];
            }
        } else {
            return ['success' => false, 'message' => 'Cek lagi Type Promosi anda!! *pastikan menggunakan huruf besar.'];
        }

        return ['success' => true];
    }

    /**
     * Generate no_bukti
     */
    private function generateNoBukti($periode, $cbg)
    {
        $monthString = substr($periode, 0, 2);
        $year = substr($periode, -4);

        $toko = DB::select("SELECT type FROM toko WHERE kode = ?", [$cbg]);
        $kode2 = $toko[0]->type ?? '';

        $kode = 'LH' . substr($year, -2) . $monthString;

        $notrans = DB::select(
            "SELECT NOM{$monthString} as no_bukti FROM notrans WHERE trans='HIJAU' AND per=?",
            [$year]
        );
        $r1 = ($notrans[0]->no_bukti ?? 0) + 1;

        DB::statement(
            "UPDATE notrans SET NOM{$monthString} = ? WHERE trans='HIJAU' AND per=?",
            [$r1, $year]
        );

        $bkt1 = str_pad($r1, 4, '0', STR_PAD_LEFT);
        return $kode . '-' . $bkt1 . $kode2;
    }

    /**
     * Get bank list
     */
    private function getBankList()
    {
        try {
            $banks = DB::select("SELECT acno, nama FROM account WHERE acno LIKE '1%' ORDER BY acno");
            return !empty($banks) ? $banks : [];
        } catch (\Exception $e) {
            return [];
        }
    }
}
