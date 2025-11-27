<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TObralSuperMarketController extends Controller
{
    // Determine menu type based on current route
    private function getMenuType()
    {
        $routeName = request()->route()->getName();
        if (strpos($routeName, 'tentryflashsale') !== false) {
            return 'FS';
        }
        return 'OB';
    }

    public function index()
    {
        $menuType = $this->getMenuType();
        $periode = session('periode', date('m.Y'));
        $cbg = session('cbg', '01');

        $pageTitle = $menuType == 'FS' ? 'Entry Flash Sale' : 'Obral Super Market';

        return view('otransaksi_obral_super.index', compact('periode', 'cbg', 'menuType', 'pageTitle'));
    }

    public function getObralSuperMarket(Request $request)
    {
        try {
            $periode = session('periode', date('m.Y'));
            $cbg = session('cbg', '01');

            Log::info('TObralSuperMarket getObralSuperMarket', [
                'periode' => $periode,
                'cbg' => $cbg
            ]);

            $query = DB::select(
                "SELECT NO_BUKTI, TGL_SLS, KODES, NAMAS, NOTES,
                        COALESCE(POSTED, 0) as POSTED
                 FROM DIS
                 WHERE per=? AND flag='OB' AND cbg=?
                 AND (flag2 IS NULL OR flag2 = '')
                 ORDER BY NO_BUKTI DESC",
                [$periode, $cbg]
            );

            Log::info('TObralSuperMarket data found', ['count' => count($query)]);

            return Datatables::of(collect($query))
                ->addIndexColumn()
                ->editColumn('TGL_SLS', function ($row) {
                    return $row->TGL_SLS ? date('d/m/Y', strtotime($row->TGL_SLS)) : '-';
                })
                ->editColumn('KODES', function ($row) {
                    return $row->KODES ?? '-';
                })
                ->editColumn('NAMAS', function ($row) {
                    return $row->NAMAS ?? '-';
                })
                ->editColumn('NOTES', function ($row) {
                    return $row->NOTES ?? '-';
                })
                ->editColumn('POSTED', function ($row) {
                    return $row->POSTED == 1 ? '<span class="badge badge-success">Posted</span>' : '<span class="badge badge-secondary">Draft</span>';
                })
                ->addColumn('action', function ($row) {
                    $btnEdit = '<button onclick="editData(\'' . $row->NO_BUKTI . '\')" class="btn btn-sm btn-primary" title="Edit"><i class="fas fa-edit"></i></button>';
                    $btnPrint = '<button onclick="printData(\'' . $row->NO_BUKTI . '\')" class="btn btn-sm btn-info ml-1" title="Print"><i class="fas fa-print"></i></button>';
                    $btnDelete = '<button onclick="deleteData(\'' . $row->NO_BUKTI . '\')" class="btn btn-sm btn-danger ml-1" title="Delete"><i class="fas fa-trash"></i></button>';
                    return $btnEdit . ' ' . $btnPrint . ' ' . $btnDelete;
                })
                ->rawColumns(['action', 'POSTED'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('TObralSuperMarket getObralSuperMarket error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getEntryFlashSale(Request $request)
    {
        try {
            $periode = session('periode', date('m.Y'));
            $cbg = session('cbg', '01');

            Log::info('TObralSuperMarket getEntryFlashSale', [
                'periode' => $periode,
                'cbg' => $cbg
            ]);

            $query = DB::select(
                "SELECT NO_BUKTI, TGL_SLS, KODES, NAMAS, NOTES,
                        COALESCE(POSTED, 0) as POSTED
                 FROM DIS
                 WHERE per=? AND flag='OB' AND cbg=? AND flag2='FS'
                 ORDER BY NO_BUKTI DESC",
                [$periode, $cbg]
            );

            Log::info('TObralSuperMarket FlashSale data found', ['count' => count($query)]);

            return Datatables::of(collect($query))
                ->addIndexColumn()
                ->editColumn('TGL_SLS', function ($row) {
                    return $row->TGL_SLS ? date('d/m/Y', strtotime($row->TGL_SLS)) : '-';
                })
                ->editColumn('KODES', function ($row) {
                    return $row->KODES ?? '-';
                })
                ->editColumn('NAMAS', function ($row) {
                    return $row->NAMAS ?? '-';
                })
                ->editColumn('NOTES', function ($row) {
                    return $row->NOTES ?? '-';
                })
                ->editColumn('POSTED', function ($row) {
                    return $row->POSTED == 1 ? '<span class="badge badge-success">Posted</span>' : '<span class="badge badge-secondary">Draft</span>';
                })
                ->addColumn('action', function ($row) {
                    $btnEdit = '<button onclick="editData(\'' . $row->NO_BUKTI . '\')" class="btn btn-sm btn-primary" title="Edit"><i class="fas fa-edit"></i></button>';
                    $btnPrint = '<button onclick="printData(\'' . $row->NO_BUKTI . '\')" class="btn btn-sm btn-info ml-1" title="Print"><i class="fas fa-print"></i></button>';
                    $btnDelete = '<button onclick="deleteData(\'' . $row->NO_BUKTI . '\')" class="btn btn-sm btn-danger ml-1" title="Delete"><i class="fas fa-trash"></i></button>';
                    return $btnEdit . ' ' . $btnPrint . ' ' . $btnDelete;
                })
                ->rawColumns(['action', 'POSTED'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('TObralSuperMarket getEntryFlashSale error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit(Request $request)
    {
        try {
            $menuType = $this->getMenuType();
            $no_bukti = $request->get('no_bukti', '+');
            $status = $request->get('status', 'simpan');
            $periode = session('periode', date('m.Y'));
            $cbg = session('cbg', '01');
            $ma = session('ma', 'TGZ');
            $username = Auth::user()->username ?? 'system';

            Log::info('TObralSuperMarket edit', [
                'menuType' => $menuType,
                'no_bukti' => $no_bukti,
                'status' => $status,
                'cbg' => $cbg,
                'ma' => $ma
            ]);

            $pageTitle = $menuType == 'FS' ? 'Entry Flash Sale' : 'Obral Super Market';

            $data = [
                'no_bukti' => '+',
                'status' => $status,
                'menuType' => $menuType,
                'pageTitle' => $pageTitle,
                'header' => (object)[
                    'no_bukti' => '+',
                    'tgl' => date('Y-m-d'),
                    'jam_mulai' => '00:00',
                    'jam_sls' => '23:59',
                    'tgl_mulai' => date('Y-m-d'),
                    'tgl_sls' => date('Y-m-d'),
                    'kodes' => '',
                    'namas' => '',
                    'notes' => '',
                    'no_file' => ''
                ],
                'detail' => [],
                'periode' => $periode,
                'cbg' => $cbg,
                'username' => $username,
                'error' => null,
                'disInfo' => null
            ];

            if ($status == 'edit' && $no_bukti && $no_bukti != '+') {
                // Ambil header dari DIS
                $header = DB::select(
                    "SELECT no_bukti, tgl, jam_mulai, jam_sls, tgl_mulai, tgl_sls,
                            kodes, namas, notes
                     FROM DIS
                     WHERE no_bukti=? AND flag='OB'",
                    [$no_bukti]
                );

                if (!empty($header)) {
                    $headerData = $header[0];

                    // Ambil detail dari DISD
                    $detail = DB::select(
                        "SELECT disd.no_id, disd.rec, disd.kd_brg, disd.na_brg,
                                disd.ket_uk, disd.dis, disd.ket, disd.th,
                                brg.sub
                         FROM DISD disd
                         LEFT JOIN $ma.brg brg ON disd.kd_brg = brg.kd_brg
                         WHERE disd.no_bukti=?
                         ORDER BY disd.rec",
                        [$no_bukti]
                    );

                    Log::info('TObralSuperMarket edit data found', [
                        'no_bukti' => $no_bukti,
                        'detail_count' => count($detail)
                    ]);

                    $data['header'] = $headerData;
                    $data['detail'] = $detail;
                    $data['no_bukti'] = $no_bukti;
                } else {
                    Log::warning('TObralSuperMarket edit not found', ['no_bukti' => $no_bukti]);
                    $data['error'] = 'Data tidak ditemukan';
                }
            }

            return view('otransaksi_obral_super.edit', $data);
        } catch (\Exception $e) {
            Log::error('TObralSuperMarket edit error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return view('otransaksi_obral_super.edit', [
                'no_bukti' => '+',
                'status' => 'simpan',
                'menuType' => $this->getMenuType(),
                'pageTitle' => $this->getMenuType() == 'FS' ? 'Entry Flash Sale' : 'Obral Super Market',
                'header' => (object)[
                    'no_bukti' => '+',
                    'tgl' => date('Y-m-d'),
                    'jam_mulai' => '00:00',
                    'jam_sls' => '23:59',
                    'tgl_mulai' => date('Y-m-d'),
                    'tgl_sls' => date('Y-m-d'),
                    'kodes' => '',
                    'namas' => '',
                    'notes' => '',
                    'no_file' => ''
                ],
                'detail' => [],
                'periode' => session('periode', date('m.Y')),
                'cbg' => session('cbg', '01'),
                'username' => Auth::user()->username ?? 'system',
                'error' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'disInfo' => null
            ]);
        }
    }

    public function browse(Request $request)
    {
        $no_file = $request->get('no_file', '');
        $cbg = session('cbg', '01');

        if (empty($no_file)) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor File harus diisi'
            ], 400);
        }

        // Ambil detail barang dari laporan_brg_macet
        $detail = DB::select(
            "SELECT kd_brg, na_brg, ket_uk, diskon, hj,
                    FLOOR(hj*diskon/100) as th,
                    DATE(tg_smp) + INTERVAL 30 DAY AS tgl_akhir
             FROM laporan_brg_macet
             WHERE no_bukti=? AND diskon > 0 AND stok > 0
             ORDER BY kd_brg",
            [$no_file]
        );

        if (empty($detail)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada data atau semua sudah diproses'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $detail,
            'tgl_akhir' => !empty($detail) ? $detail[0]->tgl_akhir : null
        ]);
    }

    public function getDetail(Request $request)
    {
        try {
            $identifier = $request->get('kd_brg');
            $cbg = session('cbg', '01');
            $ma = session('ma', 'TGZ');

            if (empty($identifier)) {
                return response()->json([
                    'success' => false,
                    'exists' => false,
                    'message' => 'Parameter tidak lengkap'
                ], 400);
            }

            Log::info('TObralSuperMarket getDetail', [
                'kd_brg' => $identifier,
                'cbg' => $cbg,
                'ma' => $ma
            ]);

            // Cari di master brg
            $barang = DB::select(
                "SELECT brg.kd_brg, CONCAT(brg.na_brg, ' ', brg.ket_uk) as na_brg,
                        brg.ket_uk, brg.sub
                 FROM $ma.brg brg
                 WHERE brg.kd_brg=?
                 LIMIT 1",
                [$identifier]
            );

            if (!empty($barang)) {
                Log::info('TObralSuperMarket getDetail found', ['kd_brg' => $identifier]);

                return response()->json([
                    'success' => true,
                    'exists' => true,
                    'data' => $barang[0]
                ]);
            }

            Log::warning('TObralSuperMarket getDetail not found', ['kd_brg' => $identifier]);

            return response()->json([
                'success' => false,
                'exists' => false,
                'message' => 'Barang tidak ditemukan'
            ]);
        } catch (\Exception $e) {
            Log::error('TObralSuperMarket getDetail error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'tgl' => 'required|date',
                'tgl_mulai' => 'required|date',
                'tgl_sls' => 'required|date',
                'jam_mulai' => 'required',
                'jam_sls' => 'required',
                'details' => 'required|array|min:1'
            ]);

            DB::beginTransaction();

            $menuType = $request->input('menu_type', 'OB');
            $no_bukti = trim($request->no_bukti);
            $status = $request->status;
            $periode = session('periode', date('m.Y'));
            $cbg = session('cbg', '01');
            $username = Auth::user()->username ?? 'system';

            $tgl = Carbon::parse($request->tgl);
            $monthz = str_pad($tgl->month, 2, '0', STR_PAD_LEFT);
            $yearz = $tgl->year;

            $periode_month = substr($periode, 0, 2);
            $periode_year = substr($periode, -4);

            // Validasi periode
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

            // Generate nomor bukti untuk simpan baru
            if ($status == 'simpan' && $no_bukti == '+') {
                // Ambil tipe toko
                $tokoInfo = DB::select(
                    "SELECT type FROM toko WHERE kode=?",
                    [$cbg]
                );
                $kode2 = !empty($tokoInfo) ? $tokoInfo[0]->type : '';

                $kode = 'OB' . substr($periode, -2) . substr($periode, 0, 2);

                // Ambil nomor terakhir dari notrans
                $lastNo = DB::select(
                    "SELECT NOM" . $periode_month . " as no_bukti
                     FROM notrans
                     WHERE trans='OBRAL' AND PER=?",
                    [$periode_year]
                );

                $r1 = !empty($lastNo) ? intval($lastNo[0]->no_bukti) : 0;
                $r1 = $r1 + 1;

                // Update notrans
                DB::statement(
                    "UPDATE notrans
                     SET NOM" . $periode_month . "=?
                     WHERE trans='OBRAL' AND PER=?",
                    [$r1, $periode_year]
                );

                $bkt1 = str_pad($r1, 4, '0', STR_PAD_LEFT);
                $no_bukti = $kode . '-' . $bkt1 . $kode2;
            }

            if ($status == 'simpan') {
                // Insert header ke DIS
                DB::statement(
                    "INSERT INTO DIS (NO_BUKTI, TGL, TGL_MULAI, TGL_SLS, JAM_MULAI, JAM_SLS,
                                     KODES, NAMAS, NOTES, FLAG, FLAG2, CBG, PER, USRNM, TG_SMP)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'OB', ?, ?, ?, ?, NOW())",
                    [
                        $no_bukti,
                        $request->tgl,
                        $request->tgl_mulai,
                        $request->tgl_sls,
                        $request->jam_mulai,
                        $request->jam_sls,
                        trim($request->kodes ?? ''),
                        trim($request->namas ?? ''),
                        trim($request->notes ?? ''),
                        $menuType == 'FS' ? 'FS' : '',
                        $cbg,
                        $periode,
                        $username
                    ]
                );
            } else {
                // Clear masks untuk barang yang akan diupdate
                DB::statement(
                    "UPDATE masks, (
                        SELECT disd.kd_brg
                        FROM DIS dis
                        INNER JOIN DISD disd ON dis.no_bukti = disd.no_bukti
                        WHERE dis.no_bukti = ? AND dis.tgz = 1
                    ) as ini
                    SET masks.dis = 0, masks.disgz = 0, masks.dismm = 0, masks.dissp = 0
                    WHERE masks.kd_brg = ini.kd_brg",
                    [$no_bukti]
                );

                // Update header
                DB::statement(
                    "UPDATE DIS
                     SET TGL=?, TGL_MULAI=?, TGL_SLS=?, JAM_MULAI=?, JAM_SLS=?,
                         KODES=?, NAMAS=?, NOTES=?, USRNM=?, TG_SMP=NOW(),
                         tgz=0, tmm=0, sop=0
                     WHERE NO_BUKTI=?",
                    [
                        $request->tgl,
                        $request->tgl_mulai,
                        $request->tgl_sls,
                        $request->jam_mulai,
                        $request->jam_sls,
                        trim($request->kodes ?? ''),
                        trim($request->namas ?? ''),
                        trim($request->notes ?? ''),
                        $username,
                        $no_bukti
                    ]
                );
            }

            // Get ID dari DIS
            $disInfo = DB::select(
                "SELECT no_id FROM DIS WHERE no_bukti=?",
                [$no_bukti]
            );
            $id = !empty($disInfo) ? $disInfo[0]->no_id : 0;

            if ($status == 'edit') {
                // Ambil detail existing
                $existingDetails = DB::select(
                    "SELECT no_id FROM DISD WHERE no_bukti=?",
                    [$no_bukti]
                );

                $existingIds = array_column($existingDetails, 'no_id');
                $newIds = array_filter(array_column($request->details, 'no_id'));

                // Update/Insert details
                foreach ($request->details as $idx => $detail) {
                    if (!empty($detail['kd_brg'])) {
                        $no_id = intval($detail['no_id'] ?? 0);

                        if ($no_id > 0 && in_array($no_id, $existingIds)) {
                            // Update existing
                            DB::statement(
                                "UPDATE DISD
                                 SET REC=?, KD_BRG=?, NA_BRG=?, KET_UK=?, DIS=?, TH=?, KET=?
                                 WHERE NO_ID=?",
                                [
                                    $idx + 1,
                                    trim($detail['kd_brg']),
                                    trim($detail['na_brg']),
                                    trim($detail['ket_uk'] ?? ''),
                                    floatval($detail['dis'] ?? 0),
                                    floatval($detail['th'] ?? 0),
                                    trim($detail['ket'] ?? ''),
                                    $no_id
                                ]
                            );
                        } else {
                            // Insert new
                            DB::statement(
                                "INSERT INTO DISD (NO_BUKTI, REC, PER, FLAG, KD_BRG, NA_BRG,
                                                  KET_UK, DIS, TH, KET, ID)
                                 VALUES (?, ?, ?, 'OB', ?, ?, ?, ?, ?, ?, ?)",
                                [
                                    $no_bukti,
                                    $idx + 1,
                                    $periode,
                                    trim($detail['kd_brg']),
                                    trim($detail['na_brg']),
                                    trim($detail['ket_uk'] ?? ''),
                                    floatval($detail['dis'] ?? 0),
                                    floatval($detail['th'] ?? 0),
                                    trim($detail['ket'] ?? ''),
                                    $id
                                ]
                            );
                        }
                    }
                }

                // Delete removed items
                $deletedIds = array_diff($existingIds, $newIds);
                foreach ($deletedIds as $del_id) {
                    DB::statement(
                        "DELETE FROM DISD WHERE NO_ID=?",
                        [$del_id]
                    );
                }
            } else {
                // Insert new details
                foreach ($request->details as $idx => $detail) {
                    if (!empty($detail['kd_brg'])) {
                        DB::statement(
                            "INSERT INTO DISD (NO_BUKTI, REC, PER, FLAG, KD_BRG, NA_BRG,
                                              KET_UK, DIS, TH, KET, ID)
                             VALUES (?, ?, ?, 'OB', ?, ?, ?, ?, ?, ?, ?)",
                            [
                                $no_bukti,
                                $idx + 1,
                                $periode,
                                trim($detail['kd_brg']),
                                trim($detail['na_brg']),
                                trim($detail['ket_uk'] ?? ''),
                                floatval($detail['dis'] ?? 0),
                                floatval($detail['th'] ?? 0),
                                trim($detail['ket'] ?? ''),
                                $id
                            ]
                        );
                    }
                }
            }

            // Update masks dengan data diskon terbaru
            DB::statement(
                "UPDATE masks, (
                    SELECT dis.tgl_mulai, dis.tgl_sls, disd.kd_brg
                    FROM DIS dis
                    INNER JOIN DISD disd ON dis.no_bukti = disd.no_bukti
                    WHERE dis.no_bukti = ? AND dis.tgz = 1
                ) as ini
                SET masks.tgdis_m = ini.tgl_mulai, masks.tgdis_a = ini.tgl_sls
                WHERE masks.kd_brg = ini.kd_brg",
                [$no_bukti]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Save Data Success',
                'no_bukti' => $no_bukti
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

    public function destroy($no_bukti)
    {
        try {
            DB::beginTransaction();

            // Cek data
            $check = DB::select(
                "SELECT no_bukti FROM DIS WHERE no_bukti=? AND flag='OB'",
                [$no_bukti]
            );

            if (empty($check)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            // Clear masks
            DB::statement(
                "UPDATE masks, (
                    SELECT disd.kd_brg
                    FROM DIS dis
                    INNER JOIN DISD disd ON dis.no_bukti = disd.no_bukti
                    WHERE dis.no_bukti = ? AND dis.tgz = 1
                ) as ini
                SET masks.dis = 0, masks.disgz = 0, masks.dismm = 0, masks.dissp = 0
                WHERE masks.kd_brg = ini.kd_brg",
                [$no_bukti]
            );

            // Hapus detail
            DB::statement(
                "DELETE FROM DISD WHERE no_bukti=?",
                [$no_bukti]
            );

            // Hapus header
            DB::statement(
                "DELETE FROM DIS WHERE no_bukti=? AND flag='OB'",
                [$no_bukti]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function printObralSuperMarket(Request $request)
    {
        $no_bukti = $request->no_bukti;
        $cbg = session('cbg', '01');
        $periode = session('periode', date('m.Y'));

        $data = DB::select(
            "SELECT dis.no_bukti, dis.kodes, dis.namas, disd.kd_brg,
                    disd.na_brg, disd.ket_uk, disd.dis, disd.th, disd.ket
             FROM DIS dis
             INNER JOIN DISD disd ON dis.no_bukti = disd.no_bukti
             WHERE dis.flag='OB' AND dis.no_bukti=? AND dis.per=? AND dis.cbg=?
             ORDER BY disd.rec",
            [$no_bukti, $periode, $cbg]
        );

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function printEntryFlashSale(Request $request)
    {
        return $this->printObralSuperMarket($request);
    }

    // Get diskon info untuk barang tertentu
    public function getDiskonInfo(Request $request)
    {
        try {
            $kd_brg = $request->get('kd_brg', '');
            $ma = session('ma', 'TGZ');
            $mm = session('mm', 'TMM');
            $op = session('op', 'SOP');

            if (empty($kd_brg)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kode barang harus diisi'
                ], 400);
            }

            Log::info('TObralSuperMarket getDiskonInfo', [
                'kd_brg' => $kd_brg,
                'ma' => $ma,
                'mm' => $mm,
                'op' => $op
            ]);

            $disInfo = DB::select(
                "SELECT A.sub, A.kd_brg, A.na_brg, A.ket_uk,
                        COALESCE(A.disgz, 0) as disgz, 
                        COALESCE(B.dismm, 0) as dismm, 
                        COALESCE(C.dissp, 0) as dissp,
                        A.jam, A.jamsls, A.tgdis_m, A.tgdis_a
                 FROM $ma.masks A
                 LEFT JOIN $mm.masks B ON A.kd_brg = B.kd_brg
                 LEFT JOIN $op.masks C ON A.kd_brg = C.kd_brg
                 WHERE A.kd_brg = ?",
                [$kd_brg]
            );

            if (!empty($disInfo)) {
                Log::info('TObralSuperMarket getDiskonInfo found', ['kd_brg' => $kd_brg]);

                return response()->json([
                    'success' => true,
                    'data' => $disInfo[0]
                ]);
            }

            Log::warning('TObralSuperMarket getDiskonInfo not found', ['kd_brg' => $kd_brg]);

            return response()->json([
                'success' => false,
                'message' => 'Kode Barang tidak ditemukan'
            ], 404);
        } catch (\Exception $e) {
            Log::error('TObralSuperMarket getDiskonInfo error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
