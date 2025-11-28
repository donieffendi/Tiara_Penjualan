<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TProsesStockOpnameController extends Controller
{
    public function index()
    {
        $periode = session('periode', date('m.Y'));
        $cbg = session('cbg', '01');
        return view('otranskasi_proses_stok_opname.index', compact('periode', 'cbg'));
    }

    public function getProsesStockOpname(Request $request)
    {
        try {
            $periode = session('periode', date('m.Y'));
            $cbg = session('cbg', '01');

            Log::info('TProsesStockOpname getProsesStockOpname', [
                'periode' => $periode,
                'cbg' => $cbg
            ]);

            $query = DB::select(
                "SELECT NO_BUKTI, TGL, SUB, USRNM, POSTED, NOTES
                 FROM lapbh
                 WHERE flag='SF' AND cbg=?
                 ORDER BY NO_BUKTI DESC",
                [$cbg]
            );

            Log::info('TProsesStockOpname data found', ['count' => count($query)]);

            return Datatables::of(collect($query))
                ->addIndexColumn()
                ->editColumn('TGL', function ($row) {
                    return $row->TGL ? date('d/m/Y', strtotime($row->TGL)) : '';
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
                    $btnPrint = '<button onclick="printData(\'' . $row->NO_BUKTI . '\')" class="btn btn-sm btn-info ml-1" title="Print"><i class="fas fa-print"></i></button>';
                    $btnDelete = $row->POSTED == 0
                        ? '<button onclick="deleteData(\'' . $row->NO_BUKTI . '\')" class="btn btn-sm btn-danger ml-1" title="Delete"><i class="fas fa-trash"></i></button>'
                        : '';
                    return $btnEdit . ' ' . $btnPrint . ' ' . $btnDelete;
                })
                ->rawColumns(['action', 'POSTED'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('TProsesStockOpname getProsesStockOpname error', [
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

            // Cek periode posted
            $periodeCheck = DB::select(
                "SELECT posted FROM perid WHERE kd_peri=?",
                [$periode]
            );

            if (!empty($periodeCheck) && $periodeCheck[0]->posted == 1) {
                return view('otranskasi_proses_stok_opname.edit', [
                    'error' => 'Closed Period',
                    'periode' => $periode,
                    'cbg' => $cbg,
                    'status' => $status,
                    'no_bukti' => '+',
                    'header' => (object)[
                        'no_bukti' => '+',
                        'tgl' => date('Y-m-d'),
                        'sub' => '',
                        'notes' => ''
                    ],
                    'detail' => []
                ]);
            }

            $data = [
                'no_bukti' => '+',
                'status' => $status,
                'header' => (object)[
                    'no_bukti' => '+',
                    'tgl' => date('Y-m-d'),
                    'sub' => '',
                    'notes' => ''
                ],
                'detail' => [],
                'periode' => $periode,
                'cbg' => $cbg,
                'error' => null
            ];

            if ($status == 'edit' && $no_bukti && $no_bukti != '+') {
                // Ambil header
                $header = DB::select(
                    "SELECT no_bukti, tgl, sub, notes, posted
                     FROM lapbh
                     WHERE no_bukti=? AND flag='SF'",
                    [$no_bukti]
                );

                if (!empty($header)) {
                    $headerData = $header[0];

                    // Cek apakah sudah posted
                    if ($headerData->posted == 1) {
                        return view('otranskasi_proses_stok_opname.edit', [
                            'error' => 'Transaksi sudah di Posting !!',
                            'periode' => $periode,
                            'cbg' => $cbg,
                            'status' => $status,
                            'no_bukti' => $no_bukti,
                            'header' => $headerData,
                            'detail' => []
                        ]);
                    }

                    // Ambil detail dari lapbhd
                    $detail = DB::select(
                        "SELECT lapbhd.no_id, lapbhd.rec, lapbhd.kd_brg, lapbhd.na_brg,
                                lapbhd.hj, lapbhd.saldo, brgfc.SUPP,
                                IFNULL(lapbhd.cek, 0) as cek, brgfc.SUB, brgfc.STAND
                         FROM lapbhd
                         LEFT JOIN brgfc ON lapbhd.kd_brg = brgfc.KD_BRG
                         WHERE lapbhd.no_bukti=?
                         ORDER BY lapbhd.rec",
                        [$no_bukti]
                    );

                    // Ambil barcode untuk setiap barang
                    foreach ($detail as $item) {
                        $brgInfo = DB::select(
                            "SELECT BARCODE FROM brgfc WHERE kd_brg=?",
                            [$item->kd_brg]
                        );
                        $item->barcode = !empty($brgInfo) ? $brgInfo[0]->BARCODE : '';
                    }

                    $data['header'] = $headerData;
                    $data['detail'] = $detail;
                    $data['no_bukti'] = $no_bukti;
                } else {
                    $data['error'] = 'Data tidak ditemukan';
                }
            }

            return view('otranskasi_proses_stok_opname.edit', $data);
        } catch (\Exception $e) {
            return view('otranskasi_proses_stok_opname.edit', [
                'no_bukti' => '+',
                'status' => 'simpan',
                'header' => (object)[
                    'no_bukti' => '+',
                    'tgl' => date('Y-m-d'),
                    'sub' => '',
                    'notes' => ''
                ],
                'detail' => [],
                'periode' => session('periode', date('m.Y')),
                'cbg' => session('cbg', '01'),
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'tgl' => 'required|date',
                'sub' => 'required',
                'details' => 'required|array|min:1'
            ]);

            DB::beginTransaction();

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

                $kode = 'SF' . substr($periode, -2) . substr($periode, 0, 2);

                // Ambil nomor terakhir dari notrans
                $lastNo = DB::select(
                    "SELECT NOM" . $periode_month . " as no_bukti
                     FROM notrans
                     WHERE trans='SOFC' AND PER=?",
                    [$periode_year]
                );

                $r1 = !empty($lastNo) ? intval($lastNo[0]->no_bukti) : 0;
                $r1 = $r1 + 1;

                // Update notrans
                DB::statement(
                    "UPDATE notrans
                     SET NOM" . $periode_month . "=?
                     WHERE trans='SOFC' AND PER=?",
                    [$r1, $periode_year]
                );

                $bkt1 = str_pad($r1, 4, '0', STR_PAD_LEFT);
                $no_bukti = $kode . '-' . $bkt1 . $kode2;
            }

            if ($status == 'simpan') {
                // Insert header
                DB::statement(
                    "INSERT INTO lapbh (NO_BUKTI, TGL, SUB, NOTES, USRNM, TG_SMP, CBG, FLAG)
                     VALUES (?, ?, ?, ?, ?, NOW(), ?, 'SF')",
                    [
                        $no_bukti,
                        $request->tgl,
                        trim($request->sub),
                        trim($request->notes ?? ''),
                        $username,
                        $cbg
                    ]
                );
            } else {
                // Update header
                DB::statement(
                    "UPDATE lapbh
                     SET TGL=?, SUB=?, NOTES=?, USRNM=?, TG_SMP=NOW()
                     WHERE NO_BUKTI=?",
                    [
                        $request->tgl,
                        trim($request->sub),
                        trim($request->notes ?? ''),
                        $username,
                        $no_bukti
                    ]
                );
            }

            // Ambil ID header
            $headerId = DB::select(
                "SELECT no_id FROM lapbh WHERE no_bukti=?",
                [$no_bukti]
            );
            $id = !empty($headerId) ? $headerId[0]->no_id : 0;

            // Hapus detail lama jika edit
            if ($status == 'edit') {
                DB::statement("DELETE FROM lapbhd WHERE no_bukti=?", [$no_bukti]);
            }

            // Insert detail
            $rec = 1;
            foreach ($request->details as $detail) {
                if (!empty($detail['kd_brg']) && isset($detail['cek']) && $detail['cek'] == 1) {
                    DB::statement(
                        "INSERT INTO lapbhd (NO_BUKTI, REC, KD_BRG, NA_BRG, HJ, SALDO, FLAG, ID, CEK)
                         VALUES (?, ?, ?, ?, ?, ?, 'SF', ?, ?)",
                        [
                            $no_bukti,
                            $rec,
                            trim($detail['kd_brg']),
                            trim($detail['na_brg']),
                            floatval($detail['hj'] ?? 0),
                            floatval($detail['saldo'] ?? 0),
                            $id,
                            1
                        ]
                    );
                    $rec++;
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

    public function browse(Request $request)
    {
        try {
            $q = $request->get('q', '');
            $sub = $request->get('sub', '');
            $item1 = $request->get('item1', '');
            $item2 = $request->get('item2', 'ZZZZ');
            $supp = $request->get('supp', '');
            $cbg = session('cbg', '01');

            Log::info('TProsesStockOpname browse', [
                'q' => $q,
                'sub' => $sub,
                'item1' => $item1,
                'item2' => $item2,
                'supp' => $supp,
                'cbg' => $cbg
            ]);

            if (!empty($supp)) {
                // Browse by supplier
                $data = DB::select(
                    "SELECT brgfc.KD_BRG, TRIM(CONCAT(brgfc.NA_BRG, ' ', brgfc.KET_UK)) as NA_BRG,
                            brgfc.SUB, brgfc.KDBAR, brgfc.KET_UK, brgfc.STAND,
                            brgfc.HJ, brgfc.HB, brgfcd.AK00 as saldo, brgfc.SUPP, brgfc.BARCODE
                     FROM brgfc brgfc
                     INNER JOIN brgfcd brgfcd ON brgfc.KD_BRG=brgfcd.KD_BRG
                     WHERE brgfcd.cbg=? AND brgfcd.yer=YEAR(NOW())
                       AND brgfc.SUPP=?
                     ORDER BY brgfc.KD_BRG ASC
                     LIMIT 500",
                    [$cbg, $supp]
                );
            } elseif (!empty($sub)) {
                // Browse by sub and item range
                $data = DB::select(
                    "SELECT brgfc.KD_BRG, TRIM(CONCAT(brgfc.NA_BRG, ' ', brgfc.KET_UK)) as NA_BRG,
                            brgfc.SUB, brgfc.KDBAR, brgfc.KET_UK, brgfc.STAND,
                            brgfc.HJ, brgfc.HB, brgfcd.AK00 as saldo, brgfc.SUPP, brgfc.BARCODE
                     FROM brgfc brgfc
                     INNER JOIN brgfcd brgfcd ON brgfc.KD_BRG=brgfcd.KD_BRG
                     WHERE brgfcd.cbg=? AND brgfcd.yer=YEAR(NOW())
                       AND brgfc.SUB=?
                       AND brgfc.KDBAR>=?
                       AND brgfc.KDBAR<=?
                     ORDER BY brgfc.KD_BRG ASC
                     LIMIT 500",
                    [$cbg, $sub, $item1, $item2]
                );
            } elseif (!empty($q)) {
                // Search by keyword
                $data = DB::select(
                    "SELECT brgfc.KD_BRG, TRIM(CONCAT(brgfc.NA_BRG, ' ', brgfc.KET_UK)) as NA_BRG,
                            brgfc.SUB, brgfc.KDBAR, brgfc.KET_UK, brgfc.STAND,
                            brgfc.HJ, brgfc.HB, brgfcd.AK00 as saldo, brgfc.SUPP, brgfc.BARCODE
                     FROM brgfc brgfc
                     INNER JOIN brgfcd brgfcd ON brgfc.KD_BRG=brgfcd.KD_BRG
                     WHERE brgfcd.cbg=? AND brgfcd.yer=YEAR(NOW())
                       AND (brgfc.KD_BRG LIKE ? OR brgfc.NA_BRG LIKE ? OR brgfc.BARCODE LIKE ?)
                     ORDER BY brgfc.KD_BRG ASC
                     LIMIT 50",
                    [$cbg, "%$q%", "%$q%", "%$q%"]
                );
            } else {
                $data = [];
            }

            Log::info('TProsesStockOpname browse found', ['count' => count($data)]);

            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('TProsesStockOpname browse error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getDetail(Request $request)
    {
        try {
            $kd_brg = $request->get('kd_brg');
            $cbg = session('cbg', '01');

            Log::info('TProsesStockOpname getDetail', [
                'kd_brg' => $kd_brg,
                'cbg' => $cbg
            ]);

            $barang = DB::select(
                "SELECT brgfc.KD_BRG, TRIM(CONCAT(brgfc.NA_BRG, ' ', brgfc.KET_UK)) as NA_BRG,
                        brgfc.SUB, brgfc.KDBAR, brgfc.KET_UK, brgfc.STAND,
                        brgfc.HJ, brgfc.HB, brgfcd.AK00 as saldo, brgfc.SUPP, brgfc.BARCODE
                 FROM brgfc brgfc
                 INNER JOIN brgfcd brgfcd ON brgfc.KD_BRG=brgfcd.KD_BRG
                 WHERE brgfcd.cbg=? AND brgfcd.yer=YEAR(NOW()) AND brgfc.KD_BRG=?",
                [$cbg, $kd_brg]
            );

            if (!empty($barang)) {
                Log::info('TProsesStockOpname getDetail found', ['kd_brg' => $kd_brg]);

                return response()->json([
                    'success' => true,
                    'exists' => true,
                    'data' => $barang[0]
                ]);
            }

            Log::warning('TProsesStockOpname getDetail not found', ['kd_brg' => $kd_brg]);

            return response()->json([
                'success' => false,
                'exists' => false,
                'message' => 'Barang tidak ditemukan'
            ]);
        } catch (\Exception $e) {
            Log::error('TProsesStockOpname getDetail error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

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

            // Cek apakah sudah posted
            $check = DB::select(
                "SELECT posted FROM lapbh WHERE no_bukti=? AND flag='SF'",
                [$no_bukti]
            );

            if (empty($check)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            if ($check[0]->posted == 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data sudah di posting, tidak dapat dihapus'
                ], 400);
            }

            // Hapus detail
            DB::statement("DELETE FROM lapbhd WHERE no_bukti=?", [$no_bukti]);

            // Hapus header
            DB::statement("DELETE FROM lapbh WHERE no_bukti=? AND flag='SF'", [$no_bukti]);

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

    public function printProsesStockOpname(Request $request)
    {
        try {
            $no_bukti = $request->no_bukti;
            $cbg = session('cbg', '01');

            Log::info('TProsesStockOpname print', [
                'no_bukti' => $no_bukti,
                'cbg' => $cbg
            ]);

            // Ambil nama toko
            $tokoInfo = DB::select(
                "SELECT na_toko FROM toko WHERE kode=?",
                [$cbg]
            );
            $toko = !empty($tokoInfo) ? $tokoInfo[0]->na_toko : '';

            // Ambil data
            $data = DB::select(
                "SELECT ? as nmtoko, lapbh.no_bukti, lapbh.tgl, lapbh.sub,
                        lapbhd.kd_brg, lapbhd.na_brg, brgfc.STAND,
                        lapbhd.hj, lapbhd.saldo, brgfc.BARCODE, brgfc.SUPP
                 FROM lapbh
                 INNER JOIN lapbhd ON lapbh.no_bukti=lapbhd.no_bukti
                 LEFT JOIN brgfc brgfc ON lapbhd.kd_brg=brgfc.KD_BRG
                 WHERE TRIM(lapbh.NO_BUKTI)=TRIM(?) AND lapbh.flag='SF'
                 ORDER BY lapbhd.rec",
                [$toko, $no_bukti]
            );

            Log::info('TProsesStockOpname print found', ['count' => count($data)]);

            return response()->json(['data' => $data]);
        } catch (\Exception $e) {
            Log::error('TProsesStockOpname print error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
