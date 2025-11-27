<?php
namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class TKoreksiStokOpnameController extends Controller
{
    public function index()
    {
        $periode = session('periode', date('m.Y'));
        $cbg     = session('cbg', '01');
        return view('otranskasi_koreksi_stok_opname.index', compact('periode', 'cbg'));
    }

    public function getKoreksiStokOpname(Request $request)
    {
        $periode = session('periode');
        $periode = $periode['bulan'] . '/' . $periode['tahun'];

        $cbg = Auth::user()->CBG;

        $query = DB::select(
            "SELECT NO_BUKTI, TGL, TOTAL_QTY, NOTES, TYPE, BKTK, POSTED
             FROM stockb
             WHERE per='$periode' AND flag='FS' AND cbg='$cbg'
             UNION ALL
             SELECT NO_BUKTI, TGL, TOTAL_QTY, NOTES, TYPE, BKTK, POSTED
             FROM stockbz
             WHERE per='$periode' AND flag='FS' AND cbg='$cbg'
             ORDER BY NO_BUKTI DESC");
        // dd($query);

        return Datatables::of(collect($query))
            ->addIndexColumn()
            ->editColumn('TGL', function ($row) {
                return $row->TGL ? date('d/m/Y', strtotime($row->TGL)) : '';
            })
            ->editColumn('TOTAL_QTY', function ($row) {
                return number_format($row->TOTAL_QTY, 2, ',', '.');
            })
            ->editColumn('POSTED', function ($row) {
                return $row->POSTED == 1
                    ? '<span class="badge badge-success">Posted</span>'
                    : '<span class="badge badge-warning">Open</span>';
            })
            ->addColumn('action', function ($row) {
                $btnEdit = $row->POSTED == 0
                    ? '<button onclick="editData(\'' . $row->NO_BUKTI . '\')" class="btn btn-sm btn-primary" title="Edit"><i class="fas fa-edit"></i></button>'
                    : '<button class="btn btn-sm btn-secondary" disabled title="Sudah Posted"><i class="fas fa-lock"></i></button>';
                $btnPrint  = '<button onclick="printData(\'' . $row->NO_BUKTI . '\')" class="btn btn-sm btn-info ml-1" title="Print"><i class="fas fa-print"></i></button>';
                $btnDelete = $row->POSTED == 0
                    ? '<button onclick="deleteData(\'' . $row->NO_BUKTI . '\')" class="btn btn-sm btn-danger ml-1" title="Delete"><i class="fas fa-trash"></i></button>'
                    : '';
                return $btnEdit . ' ' . $btnPrint . ' ' . $btnDelete;
            })
            ->rawColumns(['action', 'POSTED'])
            ->make(true);
    }

    public function edit(Request $request)
    {
        try {
            $no_bukti = $request->get('no_bukti', '+');
            $status   = $request->get('status', 'simpan');
            $periode  = session('periode', date('m.Y'));
            $cbg      = session('cbg', '01');
            $username = Auth::user()->username ?? 'system';

            // Cek periode posted
            $periodeCheck = DB::select(
                "SELECT posted FROM perid WHERE kd_peri=?",
                [$periode]
            );

            if (! empty($periodeCheck) && $periodeCheck[0]->posted == 1) {
                return view('otranskasi_koreksi_stok_opname.edit', [
                    'error'    => 'Closed Period',
                    'periode'  => $periode,
                    'cbg'      => $cbg,
                    'status'   => $status,
                    'no_bukti' => '+',
                    'header'   => (object) [
                        'no_bukti'  => '+',
                        'tgl'       => date('Y-m-d'),
                        'notes'     => '',
                        'total_qty' => 0,
                        'bktk'      => '',
                    ],
                    'detail'   => [],
                ]);
            }

            $data = [
                'no_bukti' => '+',
                'status'   => $status,
                'header'   => (object) [
                    'no_bukti'  => '+',
                    'tgl'       => date('Y-m-d'),
                    'notes'     => '',
                    'total_qty' => 0,
                    'bktk'      => '',
                ],
                'detail'   => [],
                'periode'  => $periode,
                'cbg'      => $cbg,
                'username' => $username,
                'error'    => null,
            ];

            if ($status == 'edit' && $no_bukti && $no_bukti != '+') {
                // Ambil header dari stockb atau stockbz
                $header = DB::select(
                    "SELECT no_bukti, tgl, notes, total_qty, posted, bktk
                     FROM stockb
                     WHERE no_bukti=? AND flag='FS'",
                    [$no_bukti]
                );

                if (empty($header)) {
                    $header = DB::select(
                        "SELECT no_bukti, tgl, notes, total_qty, posted, bktk
                         FROM stockbz
                         WHERE no_bukti=? AND flag='FS'",
                        [$no_bukti]
                    );
                }

                if (! empty($header)) {
                    $headerData = $header[0];

                    // Cek apakah sudah posted
                    if ($headerData->posted == 1) {
                        return view('otranskasi_koreksi_stok_opname.edit', [
                            'error'    => 'Transaksi sudah di Posting !!',
                            'periode'  => $periode,
                            'cbg'      => $cbg,
                            'status'   => $status,
                            'no_bukti' => $no_bukti,
                            'header'   => $headerData,
                            'detail'   => [],
                        ]);
                    }

                    // Ambil detail dari stockbd
                    $detail = DB::select(
                        "SELECT stockbd.no_id, stockbd.rec, stockbd.kd_brg, stockbd.na_brg,
                                stockbd.qty, stockbd.ket, stockbd.hb, stockbd.total,
                                stockbd.saldo, stockbd.riil, brgfc.BARCODE
                         FROM stockbd
                         LEFT JOIN brgfc ON stockbd.kd_brg = brgfc.KD_BRG
                         WHERE stockbd.no_bukti=?
                         ORDER BY stockbd.rec",
                        [$no_bukti]
                    );

                    $data['header']   = $headerData;
                    $data['detail']   = $detail;
                    $data['no_bukti'] = $no_bukti;
                } else {
                    $data['error'] = 'Data tidak ditemukan';
                }
            }

            return view('otranskasi_koreksi_stok_opname.edit', $data);
        } catch (\Exception $e) {
            return view('otranskasi_koreksi_stok_opname.edit', [
                'no_bukti' => '+',
                'status'   => 'simpan',
                'header'   => (object) [
                    'no_bukti'  => '+',
                    'tgl'       => date('Y-m-d'),
                    'notes'     => '',
                    'total_qty' => 0,
                    'bktk'      => '',
                ],
                'detail'   => [],
                'periode'  => session('periode', date('m.Y')),
                'cbg'      => session('cbg', '01'),
                'username' => Auth::user()->username ?? 'system',
                'error'    => 'Terjadi kesalahan: ' . $e->getMessage(),
            ]);
        }
    }

    public function browse(Request $request)
    {
        $no_bukti = $request->get('no_bukti', '');
        $cbg      = session('cbg', '01');

        if (empty($no_bukti)) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor bukti SO harus diisi',
            ], 400);
        }

        // Cek apakah bukti SO ada dan belum diproses
        $soCheck = DB::select(
            "SELECT no_bukti, sub, flag, posted
             FROM lapbh
             WHERE no_bukti=? AND cbg=? AND flag='SF'",
            [$no_bukti, $cbg]
        );

        if (empty($soCheck)) {
            return response()->json([
                'success' => false,
                'message' => 'Bukti tidak ditemukan...',
            ], 404);
        }

        if ($soCheck[0]->posted == 1) {
            return response()->json([
                'success' => false,
                'message' => 'Udah ada koreksi dengan nomor SO ini...!',
            ], 400);
        }

        // Ambil detail barang dari lapbhd
        $detail = DB::select(
            "SELECT lapbhd.kd_brg, lapbhd.na_brg,
                    brgfc.HB, brgfcd.AK00 as stok, brgfc.BARCODE
             FROM lapbhd
             INNER JOIN brgfc ON lapbhd.kd_brg = brgfc.KD_BRG
             INNER JOIN brgfcd ON brgfc.KD_BRG = brgfcd.KD_BRG
             WHERE lapbhd.no_bukti=? AND brgfcd.cbg=? AND brgfcd.yer=YEAR(NOW())
             ORDER BY lapbhd.rec",
            [$no_bukti, $cbg]
        );

        return response()->json([
            'success' => true,
            'data'    => $detail,
        ]);
    }

    public function getDetail(Request $request)
    {
        $kd_brg = $request->get('kd_brg');
        $cbg    = session('cbg', '01');

        $barang = DB::select(
            "SELECT brgfc.KD_BRG, TRIM(CONCAT(brgfc.NA_BRG, ' ', brgfc.KET_UK)) as NA_BRG,
                    brgfc.HB, brgfcd.AK00 as stok, brgfc.BARCODE
             FROM brgfc
             INNER JOIN brgfcd ON brgfc.KD_BRG = brgfcd.KD_BRG
             WHERE brgfcd.cbg=? AND brgfcd.yer=YEAR(NOW()) AND brgfc.KD_BRG=?",
            [$cbg, $kd_brg]
        );

        if (! empty($barang)) {
            return response()->json([
                'success' => true,
                'exists'  => true,
                'data'    => $barang[0],
            ]);
        }

        return response()->json([
            'success' => false,
            'exists'  => false,
            'message' => 'Barang tidak ditemukan',
        ]);
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'tgl'     => 'required|date',
                'details' => 'required|array|min:1',
            ]);

            DB::beginTransaction();

            $no_bukti = trim($request->no_bukti);
            $status   = $request->status;
            $bktk     = trim($request->bktk ?? '');
            $periode  = session('periode', date('m.Y'));
            $cbg      = session('cbg', '01');
            $username = Auth::user()->username ?? 'system';

            $tgl    = Carbon::parse($request->tgl);
            $monthz = str_pad($tgl->month, 2, '0', STR_PAD_LEFT);
            $yearz  = $tgl->year;

            $periode_month = substr($periode, 0, 2);
            $periode_year  = substr($periode, -4);

            // Validasi periode
            if ($monthz != $periode_month) {
                return response()->json([
                    'success' => false,
                    'message' => 'Month is not the same as Periode.',
                ], 400);
            }

            if ($yearz != $periode_year) {
                return response()->json([
                    'success' => false,
                    'message' => 'Year is not the same as Periode.',
                ], 400);
            }

            // Hitung total qty
            $total_qty    = 0;
            $total_amount = 0;
            foreach ($request->details as $detail) {
                if (! empty($detail['kd_brg'])) {
                    $total_qty += floatval($detail['qty'] ?? 0);
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
                $kode2 = ! empty($tokoInfo) ? $tokoInfo[0]->type : '';

                $kode = 'FS' . substr($periode, -2) . substr($periode, 0, 2);

                // Ambil nomor terakhir dari notrans
                $lastNo = DB::select(
                    "SELECT NOM" . $periode_month . " as no_bukti
                     FROM notrans
                     WHERE trans='KFC' AND PER=?",
                    [$periode_year]
                );

                $r1 = ! empty($lastNo) ? intval($lastNo[0]->no_bukti) : 0;
                $r1 = $r1 + 1;

                // Update notrans
                DB::statement(
                    "UPDATE notrans
                     SET NOM" . $periode_month . "=?
                     WHERE trans='KFC' AND PER=?",
                    [$r1, $periode_year]
                );

                $bkt1     = str_pad($r1, 4, '0', STR_PAD_LEFT);
                $no_bukti = $kode . '-' . $bkt1 . $kode2;
            }

            if ($status == 'simpan') {
                // Insert header
                DB::statement(
                    "INSERT INTO stockb (NO_BUKTI, TGL, FLAG, PER, TOTAL_QTY, NOTES, USRNM, TG_SMP, TYPE, CBG, TOTAL, BKTK)
                     VALUES (?, ?, 'FS', ?, ?, ?, ?, NOW(), 'KOREKSI', ?, ?, ?)",
                    [
                        $no_bukti,
                        $request->tgl,
                        $periode,
                        $total_qty,
                        trim($request->notes ?? ''),
                        $username,
                        $cbg,
                        $total_amount,
                        $bktk,
                    ]
                );
            } else {
                // Update header
                DB::statement(
                    "UPDATE stockb
                     SET TGL=?, NOTES=?, TOTAL_QTY=?, USRNM=?, TG_SMP=NOW(), TOTAL=?
                     WHERE NO_BUKTI=?",
                    [
                        $request->tgl,
                        trim($request->notes ?? ''),
                        $total_qty,
                        $username,
                        $total_amount,
                        $no_bukti,
                    ]
                );
            }

            // Ambil ID header
            $headerId = DB::select(
                "SELECT no_id FROM stockb WHERE no_bukti=?",
                [$no_bukti]
            );
            $id = ! empty($headerId) ? $headerId[0]->no_id : 0;

            // Ambil detail existing untuk update/delete
            if ($status == 'edit') {
                $existingDetails = DB::select(
                    "SELECT no_id FROM stockbd WHERE no_bukti=?",
                    [$no_bukti]
                );

                $existingIds = array_column($existingDetails, 'no_id');
                $newIds      = array_filter(array_column($request->details, 'no_id'));

                // Update existing atau insert new
                foreach ($request->details as $idx => $detail) {
                    if (! empty($detail['kd_brg'])) {
                        $no_id = intval($detail['no_id'] ?? 0);

                        if ($no_id > 0 && in_array($no_id, $existingIds)) {
                            // Update existing
                            DB::statement(
                                "UPDATE stockbd
                                 SET REC=?, KD_BRG=?, NA_BRG=?, QTY=?, KET=?,
                                     SALDO=?, RIIL=?, HB=?, TOTAL=?, FLAG='FS'
                                 WHERE NO_ID=?",
                                [
                                    $idx + 1,
                                    trim($detail['kd_brg']),
                                    trim($detail['na_brg']),
                                    floatval($detail['qty'] ?? 0),
                                    trim($detail['ket'] ?? ''),
                                    floatval($detail['stok'] ?? 0),
                                    floatval($detail['riil'] ?? 0),
                                    floatval($detail['hb'] ?? 0),
                                    floatval($detail['total'] ?? 0),
                                    $no_id,
                                ]
                            );
                        } else {
                            // Insert new
                            DB::statement(
                                "INSERT INTO stockbd
                                 (NO_BUKTI, REC, PER, FLAG, KD_BRG, NA_BRG, QTY, KET, ID,
                                  HJ, HB, TOTAL, SALDO, RIIL)
                                 VALUES (?, ?, ?, 'FS', ?, ?, ?, ?, ?, 0, ?, ?, ?, ?)",
                                [
                                    $no_bukti,
                                    $idx + 1,
                                    $periode,
                                    trim($detail['kd_brg']),
                                    trim($detail['na_brg']),
                                    floatval($detail['qty'] ?? 0),
                                    trim($detail['ket'] ?? ''),
                                    $id,
                                    floatval($detail['hb'] ?? 0),
                                    floatval($detail['total'] ?? 0),
                                    floatval($detail['stok'] ?? 0),
                                    floatval($detail['riil'] ?? 0),
                                ]
                            );
                        }
                    }
                }

                // Delete removed items
                $deletedIds = array_diff($existingIds, $newIds);
                foreach ($deletedIds as $del_id) {
                    DB::statement("DELETE FROM stockbd WHERE NO_ID=?", [$del_id]);
                }
            } else {
                // Insert all details for new record
                foreach ($request->details as $idx => $detail) {
                    if (! empty($detail['kd_brg'])) {
                        DB::statement(
                            "INSERT INTO stockbd
                             (NO_BUKTI, REC, PER, FLAG, KD_BRG, NA_BRG, QTY, KET, ID,
                              HJ, HB, TOTAL, SALDO, RIIL)
                             VALUES (?, ?, ?, 'FS', ?, ?, ?, ?, ?, 0, ?, ?, ?, ?)",
                            [
                                $no_bukti,
                                $idx + 1,
                                $periode,
                                trim($detail['kd_brg']),
                                trim($detail['na_brg']),
                                floatval($detail['qty'] ?? 0),
                                trim($detail['ket'] ?? ''),
                                $id,
                                floatval($detail['hb'] ?? 0),
                                floatval($detail['total'] ?? 0),
                                floatval($detail['stok'] ?? 0),
                                floatval($detail['riil'] ?? 0),
                            ]
                        );
                    }
                }
            }

            // Update lapbh posted jika ada nomor SO
            if (! empty($bktk)) {
                DB::statement(
                    "UPDATE lapbh SET POSTED=1 WHERE NO_BUKTI=?",
                    [$bktk]
                );
            }

            DB::commit();

            return response()->json([
                'success'  => true,
                'message'  => 'Save Data Success',
                'no_bukti' => $no_bukti,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . implode(', ', $e->validator->errors()->all()),
            ], 422);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($no_bukti)
    {
        try {
            DB::beginTransaction();

            // Cek apakah sudah posted
            $check = DB::select(
                "SELECT posted FROM stockb WHERE no_bukti=? AND flag='FS'",
                [$no_bukti]
            );

            if (empty($check)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan',
                ], 404);
            }

            if ($check[0]->posted == 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data sudah di posting, tidak dapat dihapus',
                ], 400);
            }

            // Hapus detail
            DB::statement("DELETE FROM stockbd WHERE no_bukti=?", [$no_bukti]);

            // Hapus header
            DB::statement("DELETE FROM stockb WHERE no_bukti=? AND flag='FS'", [$no_bukti]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function printKoreksiStokOpname(Request $request)
    {
        $no_bukti = $request->no_bukti;
        $cbg      = session('cbg', '01');

        // Ambil nama toko
        $tokoInfo = DB::select(
            "SELECT na_toko FROM toko WHERE kode=?",
            [$cbg]
        );
        $toko = ! empty($tokoInfo) ? $tokoInfo[0]->na_toko : '';

        // Cek posted status
        $headerCheck = DB::select(
            "SELECT posted FROM stockb WHERE no_bukti=? AND flag='FS'",
            [$no_bukti]
        );

        if (empty($headerCheck)) {
            $headerCheck = DB::select(
                "SELECT posted FROM stockbz WHERE no_bukti=? AND flag='FS'",
                [$no_bukti]
            );
        }

        $isPosted = ! empty($headerCheck) && $headerCheck[0]->posted == 1;

        // Query sesuai logika Delphi
        if (! $isPosted) {
            // Dari stockb/stockbd
            $data = DB::select(
                "SELECT ? as nmtoko, stockb.no_bukti,
                        CONCAT(brg.KDBAR, '-', brg.SUB) AS ITEMSUB,
                        CONCAT(brgdt.KDLAKU, brgdt.KLK) AS KD,
                        CONCAT(brg.NA_BRG, ' ', brg.KET_UK) AS NA_BRG,
                        brg.KET_KEM, stockbd.qty, brg.HJ, brg.HB,
                        (stockbd.qty * brg.HB) as total_hb, brg.ALASAN
                 FROM stockb
                 INNER JOIN stockbd ON stockbd.no_bukti = stockb.no_bukti
                 INNER JOIN brg ON stockbd.KD_BRG = brg.KD_BRG
                 INNER JOIN brgdt ON brgdt.kd_brg = brg.kd_brg AND brgdt.cbg=?
                 WHERE TRIM(stockb.NO_BUKTI)=TRIM(?)",
                [$toko, $cbg, $no_bukti]
            );
        } else {
            // Dari stockbz/stockbzd
            $data = DB::select(
                "SELECT ? as nmtoko, stockbz.no_bukti,
                        CONCAT(brg.KDBAR, '-', brg.SUB) AS ITEMSUB,
                        CONCAT(brgdt.KDLAKU, brgdt.KLK) AS KD,
                        CONCAT(brg.NA_BRG, ' ', brg.KET_UK) AS NA_BRG,
                        brg.KET_KEM, stockbzd.qty, brg.HJ, brg.HB,
                        (stockbzd.qty * brg.HB) as total_hb, brg.ALASAN
                 FROM stockbz
                 INNER JOIN stockbzd ON stockbzd.no_bukti = stockbz.no_bukti
                 INNER JOIN brg ON stockbzd.KD_BRG = brg.KD_BRG
                 INNER JOIN brgdt ON brgdt.kd_brg = brg.kd_brg AND brgdt.cbg=?
                 WHERE TRIM(stockbz.NO_BUKTI)=TRIM(?)",
                [$toko, $cbg, $no_bukti]
            );
        }

        return response()->json(['data' => $data]);
    }
}