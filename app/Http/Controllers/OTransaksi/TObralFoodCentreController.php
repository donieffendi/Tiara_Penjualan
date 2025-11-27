<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TObralFoodCentreController extends Controller
{
    public function index()
    {
        $periode = session('periode', date('m.Y'));
        $cbg = session('cbg', '01');

        return view('otransaksi_obral_food_centre.index', compact('periode', 'cbg'));
    }

    public function getObralFoodCentre(Request $request)
    {
        try {
            $periode = session('periode', date('m.Y'));
            $cbg = session('cbg', '01');

            Log::info('TObralFoodCentre getObralFoodCentre', [
                'periode' => $periode,
                'cbg' => $cbg
            ]);

            $query = DB::select(
                "SELECT NO_BUKTI, TGL, KODES, NAMAS, NOTES,
                        COALESCE(POSTED, 0) as POSTED
                 FROM DIS
                 WHERE per=? AND flag='DF' AND cbg=?
                 ORDER BY NO_BUKTI DESC",
                [$periode, $cbg]
            );

            Log::info('TObralFoodCentre data found', ['count' => count($query)]);

            return Datatables::of(collect($query))
                ->addIndexColumn()
                ->editColumn('TGL', function ($row) {
                    return $row->TGL ? date('d/m/Y', strtotime($row->TGL)) : '-';
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
                    return $row->POSTED == 1
                        ? '<span class="badge badge-success">Posted</span>'
                        : '<span class="badge badge-secondary">Draft</span>';
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
            Log::error('TObralFoodCentre getObralFoodCentre error', [
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
            $no_bukti = $request->get('no_bukti', '+');
            $status = $request->get('status', 'simpan');
            $periode = session('periode', date('m.Y'));
            $cbg = session('cbg', '01');
            $username = Auth::user()->username ?? 'system';

            $data = [
                'no_bukti' => '+',
                'status' => $status,
                'header' => (object)[
                    'no_bukti' => '+',
                    'tgl' => date('Y-m-d'),
                    'kodes' => '',
                    'namas' => '',
                    'notes' => ''
                ],
                'detail' => [],
                'periode' => $periode,
                'cbg' => $cbg,
                'username' => $username,
                'error' => null
            ];

            if ($status == 'edit' && $no_bukti && $no_bukti != '+') {
                // Ambil header
                $header = DB::select(
                    "SELECT NO_BUKTI, TGL, KODES, NAMAS, NOTES
                     FROM DIS
                     WHERE no_bukti=? AND flag='DF'",
                    [$no_bukti]
                );

                if (!empty($header)) {
                    $headerData = $header[0];

                    // Ambil detail
                    $detail = DB::select(
                        "SELECT disd.no_id, disd.rec, disd.kd_brg, disd.na_brg,
                                disd.dis, disd.partsp, disd.ket
                         FROM DISD disd
                         WHERE disd.no_bukti=? AND disd.flag='DF'
                         ORDER BY disd.rec",
                        [$no_bukti]
                    );

                    $data['header'] = $headerData;
                    $data['detail'] = $detail;
                    $data['no_bukti'] = $no_bukti;
                } else {
                    $data['error'] = 'Data tidak ditemukan';
                }
            }

            return view('otransaksi_obral_food_centre.edit', $data);
        } catch (\Exception $e) {
            return view('otransaksi_obral_food_centre.edit', [
                'no_bukti' => '+',
                'status' => 'simpan',
                'header' => (object)[
                    'no_bukti' => '+',
                    'tgl' => date('Y-m-d'),
                    'kodes' => '',
                    'namas' => '',
                    'notes' => ''
                ],
                'detail' => [],
                'periode' => session('periode', date('m.Y')),
                'cbg' => session('cbg', '01'),
                'username' => Auth::user()->username ?? 'system',
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    public function browse(Request $request)
    {
        try {
            $kd_brg = $request->get('kd_brg', '');
            $ma = session('ma', 'TGZ');

            if (empty($kd_brg)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kode Barang harus diisi'
                ], 400);
            }

            Log::info('TObralFoodCentre browse', [
                'kd_brg' => $kd_brg,
                'ma' => $ma
            ]);

            // Cari di master barang food centre
            $barang = DB::select(
                "SELECT kd_brg, na_brg
                 FROM $ma.brgfc
                 WHERE kd_brg=?
                 LIMIT 1",
                [$kd_brg]
            );

            if (!empty($barang)) {
                Log::info('TObralFoodCentre browse found', ['kd_brg' => $kd_brg]);

                return response()->json([
                    'success' => true,
                    'exists' => true,
                    'data' => $barang[0]
                ]);
            }

            Log::warning('TObralFoodCentre browse not found', ['kd_brg' => $kd_brg]);

            return response()->json([
                'success' => false,
                'exists' => false,
                'message' => 'Barang tidak ditemukan'
            ]);
        } catch (\Exception $e) {
            Log::error('TObralFoodCentre browse error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getDetail(Request $request)
    {
        return $this->browse($request);
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'tgl' => 'required|date',
                'details' => 'required|array|min:1'
            ]);

            DB::beginTransaction();

            $no_bukti = trim($request->no_bukti);
            $status = $request->status;
            $periode = session('periode', date('m.Y'));
            $cbg = session('cbg', '01');
            $ma = session('ma', 'TGZ');
            $username = Auth::user()->username ?? 'system';

            Log::info('TObralFoodCentre store', [
                'no_bukti' => $no_bukti,
                'status' => $status,
                'periode' => $periode,
                'cbg' => $cbg,
                'ma' => $ma
            ]);

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

                $kode = 'DF' . substr($periode, -2) . substr($periode, 0, 2);

                // Ambil nomor terakhir
                $lastNo = DB::select(
                    "SELECT NOM" . $periode_month . " as no_bukti
                     FROM notrans
                     WHERE trans='DISFC' AND PER=?",
                    [$periode_year]
                );

                $r1 = !empty($lastNo) ? intval($lastNo[0]->no_bukti) : 0;
                $r1 = $r1 + 1;

                // Update notrans
                DB::statement(
                    "UPDATE notrans
                     SET NOM" . $periode_month . "=?
                     WHERE trans='DISFC' AND PER=?",
                    [$r1, $periode_year]
                );

                $bkt1 = str_pad($r1, 4, '0', STR_PAD_LEFT);
                $no_bukti = $kode . '-' . $bkt1 . $kode2;
            }

            if ($status == 'simpan') {
                // Insert header ke DIS
                DB::statement(
                    "INSERT INTO DIS (NO_BUKTI, TGL, KODES, NAMAS, NOTES, FLAG, CBG, PER, USRNM, TG_SMP, FC)
                     VALUES (?, ?, ?, ?, ?, 'DF', ?, ?, ?, NOW(), 1)",
                    [
                        $no_bukti,
                        $request->tgl,
                        trim($request->kodes ?? ''),
                        trim($request->namas ?? ''),
                        trim($request->notes ?? ''),
                        $cbg,
                        $periode,
                        $username
                    ]
                );
            } else {
                // Update header
                DB::statement(
                    "UPDATE DIS
                     SET TGL=?, KODES=?, NAMAS=?, NOTES=?, USRNM=?, TG_SMP=NOW()
                     WHERE NO_BUKTI=?",
                    [
                        $request->tgl,
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
                                 SET REC=?, KD_BRG=?, NA_BRG=?, DIS=?, PARTSP=?, KET=?
                                 WHERE NO_ID=?",
                                [
                                    $idx + 1,
                                    trim($detail['kd_brg']),
                                    trim($detail['na_brg']),
                                    floatval($detail['dis'] ?? 0),
                                    floatval($detail['partsp'] ?? 0),
                                    trim($detail['ket'] ?? ''),
                                    $no_id
                                ]
                            );
                        } else {
                            // Insert new
                            DB::statement(
                                "INSERT INTO DISD (NO_BUKTI, REC, PER, FLAG, KD_BRG, NA_BRG, DIS, PARTSP, KET, ID)
                                 VALUES (?, ?, ?, 'DF', ?, ?, ?, ?, ?, ?)",
                                [
                                    $no_bukti,
                                    $idx + 1,
                                    $periode,
                                    trim($detail['kd_brg']),
                                    trim($detail['na_brg']),
                                    floatval($detail['dis'] ?? 0),
                                    floatval($detail['partsp'] ?? 0),
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
                            "INSERT INTO DISD (NO_BUKTI, REC, PER, FLAG, KD_BRG, NA_BRG, DIS, PARTSP, KET, ID)
                             VALUES (?, ?, ?, 'DF', ?, ?, ?, ?, ?, ?)",
                            [
                                $no_bukti,
                                $idx + 1,
                                $periode,
                                trim($detail['kd_brg']),
                                trim($detail['na_brg']),
                                floatval($detail['dis'] ?? 0),
                                floatval($detail['partsp'] ?? 0),
                                trim($detail['ket'] ?? ''),
                                $id
                            ]
                        );
                    }
                }
            }

            // Update maskfc dengan diskon terbaru
            DB::statement(
                "UPDATE $ma.maskfc maskfc, (
                    SELECT disd.kd_brg, disd.na_brg, disd.dis
                    FROM DIS dis
                    INNER JOIN DISD disd ON dis.no_bukti = disd.no_bukti
                    WHERE dis.no_bukti = ?
                ) as ageng
                SET maskfc.dis = ageng.dis
                WHERE maskfc.kd_brg = ageng.kd_brg",
                [$no_bukti]
            );

            // Update flag FC pada DIS
            DB::statement(
                "UPDATE DIS SET FC=1 WHERE no_bukti=?",
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

            // Cek apakah periode sudah posted
            $periode = session('periode', date('m.Y'));
            $posted = DB::select(
                "SELECT posted FROM perid WHERE kd_peri=?",
                [$periode]
            );

            if (!empty($posted) && $posted[0]->posted == 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Periode sudah di-posting, tidak dapat menghapus data'
                ], 400);
            }

            // Cek data
            $check = DB::select(
                "SELECT no_bukti FROM DIS WHERE no_bukti=? AND flag='DF'",
                [$no_bukti]
            );

            if (empty($check)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            // Hapus detail
            DB::statement(
                "DELETE FROM DISD WHERE no_bukti=? AND flag='DF'",
                [$no_bukti]
            );

            // Hapus header
            DB::statement(
                "DELETE FROM DIS WHERE no_bukti=? AND flag='DF'",
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

    public function printObralFoodCentre(Request $request)
    {
        $no_bukti = $request->no_bukti;
        $cbg = session('cbg', '01');
        $periode = session('periode', date('m.Y'));

        $data = DB::select(
            "SELECT dis.no_bukti, dis.kodes, dis.namas, disd.kd_brg,
                    disd.na_brg, disd.dis, disd.partsp, disd.ket
             FROM DIS dis
             INNER JOIN DISD disd ON dis.no_bukti = disd.no_bukti
             WHERE dis.flag='DF' AND dis.no_bukti=? AND dis.per=? AND dis.cbg=?
             ORDER BY disd.rec",
            [$no_bukti, $periode, $cbg]
        );

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function getDiskonInfo(Request $request)
    {
        try {
            $kd_brg = $request->get('kd_brg', '');
            $ma = session('ma', 'TGZ');

            if (empty($kd_brg)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kode barang harus diisi'
                ], 400);
            }

            Log::info('TObralFoodCentre getDiskonInfo', [
                'kd_brg' => $kd_brg,
                'ma' => $ma
            ]);

            $disInfo = DB::select(
                "SELECT kd_brg, na_brg, COALESCE(dis, 0) as dis
                 FROM $ma.maskfc
                 WHERE kd_brg = ?",
                [$kd_brg]
            );

            if (!empty($disInfo)) {
                Log::info('TObralFoodCentre getDiskonInfo found', ['kd_brg' => $kd_brg]);

                return response()->json([
                    'success' => true,
                    'data' => $disInfo[0]
                ]);
            }

            Log::warning('TObralFoodCentre getDiskonInfo not found', ['kd_brg' => $kd_brg]);

            return response()->json([
                'success' => false,
                'message' => 'Kode Barang tidak ditemukan'
            ], 404);
        } catch (\Exception $e) {
            Log::error('TObralFoodCentre getDiskonInfo error', [
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
