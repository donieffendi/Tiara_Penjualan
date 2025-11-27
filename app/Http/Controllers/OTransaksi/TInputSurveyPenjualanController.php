<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TInputSurveyPenjualanController extends Controller
{
    public function index()
    {
        $periode = session('periode', date('m.Y'));
        $cbg = session('cbg', '01');
        return view('otransaksi_input_survey_penjualan.index', compact('periode', 'cbg'));
    }

    public function getInputSurveyPenjualan(Request $request)
    {
        $periode = session('periode', date('m.Y'));
        $cbg = session('cbg', '01');

        $query = DB::select(
            "SELECT NO_BUKTI, TGL, TOTAL_QTY, NOTES, NO_AGENDA
             FROM survey
             WHERE per=? AND flag='PS' AND cbg=?
             ORDER BY NO_BUKTI DESC",
            [$periode, $cbg]
        );

        return Datatables::of(collect($query))
            ->addIndexColumn()
            ->editColumn('TGL', function ($row) {
                return $row->TGL ? date('d/m/Y', strtotime($row->TGL)) : '';
            })
            ->editColumn('TOTAL_QTY', function ($row) {
                return number_format($row->TOTAL_QTY, 2, ',', '.');
            })
            ->addColumn('action', function ($row) {
                $btnEdit = '<button onclick="editData(\'' . $row->NO_BUKTI . '\')" class="btn btn-sm btn-primary" title="Edit"><i class="fas fa-edit"></i></button>';
                $btnPrint = '<button onclick="printData(\'' . $row->NO_BUKTI . '\')" class="btn btn-sm btn-info ml-1" title="Print"><i class="fas fa-print"></i></button>';
                $btnDelete = '<button onclick="deleteData(\'' . $row->NO_BUKTI . '\')" class="btn btn-sm btn-danger ml-1" title="Delete"><i class="fas fa-trash"></i></button>';
                return $btnEdit . ' ' . $btnPrint . ' ' . $btnDelete;
            })
            ->rawColumns(['action'])
            ->make(true);
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
                    'notes' => '',
                    'total_qty' => 0,
                    'total' => 0,
                    'no_agenda' => ''
                ],
                'detail' => [],
                'periode' => $periode,
                'cbg' => $cbg,
                'username' => $username,
                'error' => null
            ];

            if ($status == 'edit' && $no_bukti && $no_bukti != '+') {
                // Ambil header dari survey
                $header = DB::select(
                    "SELECT no_bukti, tgl, notes, total_qty, total, no_agenda
                     FROM survey
                     WHERE no_bukti=? AND flag='PS'",
                    [$no_bukti]
                );

                if (!empty($header)) {
                    $headerData = $header[0];

                    // Ambil detail dari surveyd dengan AG_PJL
                    $detail = DB::select(
                        "SELECT surveyd.no_id, surveyd.rec, surveyd.kd_brg, surveyd.na_brg,
                                surveyd.ket_kem, surveyd.r_pjl as jml_ord, surveyd.hj,
                                surveyd.hb_pjl as hb, surveyd.barcode, surveyd.ket,
                                surveyd.pjk, surveyd.hbpjk,
                                (surveyd.r_pjl * surveyd.hb_pjl) as total,
                                brg.ket_uk, brg.sub
                         FROM surveyd
                         LEFT JOIN brg ON surveyd.kd_brg = brg.kd_brg
                         WHERE surveyd.ag_pjl=?
                         ORDER BY surveyd.rec",
                        [$no_bukti]
                    );

                    $data['header'] = $headerData;
                    $data['detail'] = $detail;
                    $data['no_bukti'] = $no_bukti;
                } else {
                    $data['error'] = 'Data tidak ditemukan';
                }
            }

            return view('otransaksi_input_survey_penjualan.edit', $data);
        } catch (\Exception $e) {
            return view('otransaksi_input_survey_penjualan.edit', [
                'no_bukti' => '+',
                'status' => 'simpan',
                'header' => (object)[
                    'no_bukti' => '+',
                    'tgl' => date('Y-m-d'),
                    'notes' => '',
                    'total_qty' => 0,
                    'total' => 0,
                    'no_agenda' => ''
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
        $no_agenda = $request->get('no_agenda', '');
        $cbg = session('cbg', '01');

        if (empty($no_agenda)) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor Survey harus diisi'
            ], 400);
        }

        // Ambil detail barang dari surveyd yang belum ada AG_PJL atau AG_PJL kosong
        $detail = DB::select(
            "SELECT surveyd.no_id, surveyd.kd_brg, surveyd.na_brg, surveyd.barcode,
                    surveyd.ket_kem, surveyd.jo as jml_ord, surveyd.hj, surveyd.hb,
                    brg.ket_uk, brg.sub, brg.pjk,
                    CASE WHEN brg.pjk='Y' THEN ROUND(surveyd.hb/1.1, 2) ELSE surveyd.hb END as hbpjk
             FROM surveyd
             LEFT JOIN brg ON surveyd.kd_brg = brg.kd_brg
             WHERE surveyd.no_bukti=? AND (surveyd.ag_pjl IS NULL OR surveyd.ag_pjl = '' OR LENGTH(surveyd.ag_pjl) < 9)
             ORDER BY surveyd.rec",
            [$no_agenda]
        );

        if (empty($detail)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada data survey atau semua sudah diproses'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $detail
        ]);
    }

    public function getDetail(Request $request)
    {
        $identifier = $request->get('kd_brg'); // bisa barcode atau kd_brg
        $no_agenda = $request->get('no_agenda', '');
        $cbg = session('cbg', '01');

        if (empty($identifier) || empty($no_agenda)) {
            return response()->json([
                'success' => false,
                'exists' => false,
                'message' => 'Parameter tidak lengkap'
            ], 400);
        }

        // Cari di surveyd berdasarkan barcode atau kd_brg
        $barang = DB::select(
            "SELECT surveyd.no_id, surveyd.kd_brg, surveyd.na_brg, surveyd.barcode,
                    surveyd.ket_kem, surveyd.jo as jml_ord, surveyd.hj, surveyd.hb,
                    brg.ket_uk, brg.sub, brg.pjk,
                    CASE WHEN brg.pjk='Y' THEN ROUND(surveyd.hb/1.1, 2) ELSE surveyd.hb END as hbpjk
             FROM surveyd
             LEFT JOIN brg ON surveyd.kd_brg = brg.kd_brg
             WHERE surveyd.no_bukti=? AND (surveyd.barcode=? OR surveyd.kd_brg=?)
             AND (surveyd.ag_pjl IS NULL OR surveyd.ag_pjl = '' OR LENGTH(surveyd.ag_pjl) < 9)
             LIMIT 1",
            [$no_agenda, $identifier, $identifier]
        );

        if (!empty($barang)) {
            return response()->json([
                'success' => true,
                'exists' => true,
                'data' => $barang[0],
                'from_survey' => true
            ]);
        }

        // Jika tidak ada di survey, cari di master brg (untuk input manual)
        $barangMaster = DB::select(
            "SELECT brg.kd_brg, CONCAT(brg.na_brg, ' ', brg.ket_uk) as na_brg,
                    brg.barcode, brg.ket_kem, brg.hj, brg.hb, brg.ket_uk,
                    brg.sub, brg.pjk,
                    CASE WHEN brg.pjk='Y' THEN ROUND(brg.hb/1.1, 2) ELSE brg.hb END as hbpjk
             FROM brg
             WHERE brg.barcode=? OR brg.kd_brg=?
             LIMIT 1",
            [$identifier, $identifier]
        );

        if (!empty($barangMaster)) {
            return response()->json([
                'success' => true,
                'exists' => true,
                'data' => $barangMaster[0],
                'from_survey' => false
            ]);
        }

        return response()->json([
            'success' => false,
            'exists' => false,
            'message' => 'Barang tidak ditemukan'
        ]);
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'tgl' => 'required|date',
                'no_agenda' => 'required',
                'details' => 'required|array|min:1'
            ]);

            DB::beginTransaction();

            $no_bukti = trim($request->no_bukti);
            $status = $request->status;
            $no_agenda = trim($request->no_agenda);
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

            // Hitung total qty dan total
            $total_qty = 0;
            $total_amount = 0;
            foreach ($request->details as $detail) {
                if (!empty($detail['kd_brg'])) {
                    $total_qty += floatval($detail['jml_ord'] ?? 0);
                    $total_amount += floatval($detail['total'] ?? 0);
                }
            }

            // Generate nomor bukti untuk simpan baru
            if ($status == 'simpan' && $no_bukti == '+') {
                // Ambil tipe toko
                $tokoInfo = DB::select(
                    "SELECT type FROM toko WHERE kode=?",
                    [$cbg]
                );
                $kode2 = !empty($tokoInfo) ? $tokoInfo[0]->type : '';

                $kode = 'PS' . substr($periode, -2) . substr($periode, 0, 2);

                // Ambil nomor terakhir dari notrans
                $lastNo = DB::select(
                    "SELECT NOM" . $periode_month . " as no_bukti
                     FROM notrans
                     WHERE trans='HSJUAL' AND PER=?",
                    [$periode_year]
                );

                $r1 = !empty($lastNo) ? intval($lastNo[0]->no_bukti) : 0;
                $r1 = $r1 + 1;

                // Update notrans
                DB::statement(
                    "UPDATE notrans
                     SET NOM" . $periode_month . "=?
                     WHERE trans='HSJUAL' AND PER=?",
                    [$r1, $periode_year]
                );

                $bkt1 = str_pad($r1, 4, '0', STR_PAD_LEFT);
                $no_bukti = $kode . '-' . $bkt1 . $kode2;
            }

            if ($status == 'simpan') {
                // Insert header ke survey
                DB::statement(
                    "INSERT INTO survey (NO_BUKTI, NO_AGENDA, TGL, NOTES, TOTAL_QTY, TOTAL, FLAG, CBG, PER, USRNM, TG_SMP)
                     VALUES (?, ?, ?, ?, ?, ?, 'PS', ?, ?, ?, NOW())",
                    [
                        $no_bukti,
                        $no_agenda,
                        $request->tgl,
                        trim($request->notes ?? ''),
                        $total_qty,
                        $total_amount,
                        $cbg,
                        $periode,
                        $username
                    ]
                );
            } else {
                // Update header
                DB::statement(
                    "UPDATE survey
                     SET NO_AGENDA=?, TGL=?, NOTES=?, TOTAL_QTY=?, TOTAL=?, USRNM=?, TG_SMP=NOW()
                     WHERE NO_BUKTI=?",
                    [
                        $no_agenda,
                        $request->tgl,
                        trim($request->notes ?? ''),
                        $total_qty,
                        $total_amount,
                        $username,
                        $no_bukti
                    ]
                );
            }

            // Proses detail
            if ($status == 'simpan') {
                // Insert/Update surveyd
                foreach ($request->details as $idx => $detail) {
                    if (!empty($detail['kd_brg'])) {
                        $no_id = intval($detail['no_id'] ?? 0);

                        if ($no_id > 0) {
                            // Update existing surveyd record
                            DB::statement(
                                "UPDATE surveyd
                                 SET AG_PJL=?, R_PJL=?, HB_PJL=?, REC=?
                                 WHERE NO_ID=?",
                                [
                                    $no_bukti,
                                    floatval($detail['jml_ord'] ?? 0),
                                    floatval($detail['hbpjk'] ?? 0),
                                    $idx + 1,
                                    $no_id
                                ]
                            );
                        } else {
                            // Insert new surveyd record (untuk barang yang ditambah manual)
                            $maxNoId = DB::select(
                                "SELECT MAX(no_id) as max_id FROM surveyd
                                 WHERE no_bukti=? AND kd_brg=?",
                                [$no_agenda, trim($detail['kd_brg'])]
                            );

                            if (!empty($maxNoId) && $maxNoId[0]->max_id) {
                                // Insert based on existing record
                                DB::statement(
                                    "INSERT INTO surveyd (AG_PJL, R_PJL, HB_PJL, REC,
                                     ID, hb, SUB, NOITEM, no_bukti, kd_brg, na_brg, kdlaku,
                                     ket_kem, hj, jo, supp, sptkk1, tgl, profit, lambat, `import`, barcode, TG_SMP, ket)
                                     SELECT ?, ?, ?, ?,
                                     ID, HB, SUB, NOITEM, no_bukti, kd_brg, na_brg, kdlaku, ket_kem, hj, jo,
                                     supp, sptkk1, tgl, profit, lambat, `import`, barcode, NOW(), ''
                                     FROM surveyd
                                     WHERE no_id=?",
                                    [
                                        $no_bukti,
                                        floatval($detail['jml_ord'] ?? 0),
                                        floatval($detail['hbpjk'] ?? 0),
                                        $idx + 1,
                                        $maxNoId[0]->max_id
                                    ]
                                );
                            } else {
                                // Insert completely new record from brg master
                                DB::statement(
                                    "INSERT INTO surveyd (AG_PJL, R_PJL, HB_PJL, REC, no_bukti, kd_brg, na_brg, barcode, hj, hb, ket_kem, TG_SMP)
                                     SELECT ?, ?, ?, ?, ?, kd_brg, CONCAT(na_brg, ' ', ket_uk), barcode, hj, hb, ket_kem, NOW()
                                     FROM brg
                                     WHERE kd_brg=?",
                                    [
                                        $no_bukti,
                                        floatval($detail['jml_ord'] ?? 0),
                                        floatval($detail['hbpjk'] ?? 0),
                                        $idx + 1,
                                        $no_agenda,
                                        trim($detail['kd_brg'])
                                    ]
                                );
                            }
                        }
                    }
                }
            } else {
                // Edit mode
                // Ambil detail existing
                $existingDetails = DB::select(
                    "SELECT no_id FROM surveyd WHERE ag_pjl=?",
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
                                "UPDATE surveyd
                                 SET AG_PJL=?, R_PJL=?, HB_PJL=?, REC=?
                                 WHERE NO_ID=?",
                                [
                                    $no_bukti,
                                    floatval($detail['jml_ord'] ?? 0),
                                    floatval($detail['hbpjk'] ?? 0),
                                    $idx + 1,
                                    $no_id
                                ]
                            );
                        } else {
                            // Insert new (sama seperti simpan mode)
                            $maxNoId = DB::select(
                                "SELECT MAX(no_id) as max_id FROM surveyd
                                 WHERE no_bukti=? AND kd_brg=?",
                                [$no_agenda, trim($detail['kd_brg'])]
                            );

                            if (!empty($maxNoId) && $maxNoId[0]->max_id) {
                                DB::statement(
                                    "INSERT INTO surveyd (AG_PJL, R_PJL, HB_PJL, REC,
                                     ID, hb, SUB, NOITEM, no_bukti, kd_brg, na_brg, kdlaku,
                                     ket_kem, hj, jo, supp, sptkk1, tgl, profit, lambat, `import`, barcode, TG_SMP, ket)
                                     SELECT ?, ?, ?, ?,
                                     ID, HB, SUB, NOITEM, no_bukti, kd_brg, na_brg, kdlaku, ket_kem, hj, jo,
                                     supp, sptkk1, tgl, profit, lambat, `import`, barcode, NOW(), ''
                                     FROM surveyd
                                     WHERE no_id=?",
                                    [
                                        $no_bukti,
                                        floatval($detail['jml_ord'] ?? 0),
                                        floatval($detail['hbpjk'] ?? 0),
                                        $idx + 1,
                                        $maxNoId[0]->max_id
                                    ]
                                );
                            }
                        }
                    }
                }

                // Clear AG_PJL untuk item yang dihapus
                $deletedIds = array_diff($existingIds, $newIds);
                foreach ($deletedIds as $del_id) {
                    DB::statement(
                        "UPDATE surveyd SET AG_PJL='' WHERE NO_ID=?",
                        [$del_id]
                    );
                }
            }

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
                "SELECT no_bukti FROM survey WHERE no_bukti=? AND flag='PS'",
                [$no_bukti]
            );

            if (empty($check)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            // Clear AG_PJL di surveyd
            DB::statement(
                "UPDATE surveyd SET AG_PJL='' WHERE AG_PJL=?",
                [$no_bukti]
            );

            // Hapus header
            DB::statement(
                "DELETE FROM survey WHERE no_bukti=? AND flag='PS'",
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

    public function printInputSurveyPenjualan(Request $request)
    {
        $no_bukti = $request->no_bukti;
        $cbg = session('cbg', '01');

        $data = DB::select(
            "SELECT survey.notes, survey.KRITERIA, survey.NO_AGENDA, survey.TGL,
                    surveyd.KD_BRG, CONCAT(brgdt.KDLAKU, ' ', surveyd.NA_BRG) as NA_BRG,
                    surveyd.KET_KEM, surveyd.R_PJL as qty, surveyd.HJ, surveyd.HB_PJL as hb,
                    surveyd.R_PJL * surveyd.HB_PJL as total, surveyd.ket
             FROM survey
             INNER JOIN surveyd ON survey.no_bukti = surveyd.AG_PJL
             LEFT JOIN brgdt ON surveyd.KD_BRG = brgdt.KD_BRG AND brgdt.CBG = survey.CBG
             WHERE survey.flag='PS' AND survey.no_bukti=?
             ORDER BY surveyd.rec",
            [$no_bukti]
        );

        return response()->json(['success' => true, 'data' => $data]);
    }
}