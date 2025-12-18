<?php
namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PHPJasperXML;
use Yajra\DataTables\Facades\DataTables;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

class TProsesStockOpnameController extends Controller
{
    /**
     * Get valid CBG from session or user, with fallback to TGZ
     */
    private function getValidCbg()
    {
        $cbg = session('flag');
        if (empty($cbg)) {
            $cbg = Auth::user()->CBG ?? 'TGZ';
        }
        // Validasi cbg, hanya terima TGZ, TMM, SOP
        if (! in_array($cbg, ['TGZ', 'TMM', 'SOP'])) {
            $cbg = 'TGZ';
        }
        return $cbg;
    }

    public function index()
    {
        $periode = session('periode', date('m.Y'));

        // Handle if periode is an array
        if (is_array($periode)) {
            $periode = $periode['bulan'] . '.' . $periode['tahun'];
        }

        $cbg = $this->getValidCbg();
        return view('otranskasi_proses_stok_opname.index', compact('periode', 'cbg'));
    }

    public function getProsesStockOpname(Request $request, $tab)
    {
        try {
            $periode = session('periode', date('m.Y'));
            if (is_array($periode)) {
                $periode = $periode['bulan'] . '/' . $periode['tahun'];
            }
            $cbg = $this->getValidCbg();

            if ($tab === 'SO1') {
                $query = DB::select(
                    "SELECT NO_BUKTI, TGL, SUB, USRNM, POSTED
                 FROM lapbh
                 WHERE flag='SF' AND cbg=?
                 ORDER BY NO_BUKTI DESC",
                    [$cbg]
                );
            } elseif ($tab === 'SO2') {
                $query = DB::select("SELECT *,concat(left(nolap,2), right(nolap,5)) as bukti
                                FROM stockb
                                WHERE per = '$periode'
                                AND flag = 'AO'
                                ORDER BY no_bukti");
            }

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
                    // $btnPrint  = '<button onclick="printData(\'' . $row->NO_BUKTI . '\')" class="btn btn-sm btn-info ml-1" title="Print"><i class="fas fa-print"></i></button>';
                    $btnDelete = $row->POSTED == 0
                        ? '<button onclick="deleteData(\'' . $row->NO_BUKTI . '\')" class="btn btn-sm btn-danger ml-1" title="Delete"><i class="fas fa-trash"></i></button>'
                        : '';
                    return $btnEdit . '' . $btnDelete;
                })
                ->rawColumns(['action', 'POSTED'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('TProsesStockOpname getProsesStockOpname error', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function edit(Request $request)
    {
        try {
            $no_bukti = $request->get('no_bukti', '+');
            $status   = $request->get('status', 'simpan');
            $periode  = session('periode', date('m.Y'));

            // Handle if periode is an array
            if (is_array($periode)) {
                $periode = $periode['bulan'] . '.' . $periode['tahun'];
            }

            $cbg = $this->getValidCbg();

            // Cek periode posted
            $periodeCheck = DB::select(
                "SELECT posted FROM perid WHERE kd_peri=?",
                [$periode]
            );

            if (! empty($periodeCheck) && $periodeCheck[0]->posted == 1) {
                return view('otranskasi_proses_stok_opname.edit', [
                    'error'    => 'Closed Period',
                    'periode'  => $periode,
                    'cbg'      => $cbg,
                    'status'   => $status,
                    'no_bukti' => '+',
                    'header'   => (object) [
                        'no_bukti' => '+',
                        'tgl'      => date('Y-m-d'),
                        'sub'      => '',
                        'notes'    => '',
                    ],
                    'detail'   => [],
                ]);
            }

            $data = [
                'no_bukti' => '+',
                'status'   => $status,
                'header'   => (object) [
                    'no_bukti' => '+',
                    'tgl'      => date('Y-m-d'),
                    'sub'      => '',
                    'notes'    => '',
                ],
                'detail'   => [],
                'periode'  => $periode,
                'cbg'      => $cbg,
                'error'    => null,
            ];

            if ($status == 'edit' && $no_bukti && $no_bukti != '+') {
                // Ambil header
                $header = DB::select(
                    "SELECT no_bukti, tgl, sub, posted
                     FROM lapbh
                     WHERE no_bukti=? AND flag='SF'",
                    [$no_bukti]
                );

                if (! empty($header)) {
                    $headerData = $header[0];

                    // Cek apakah sudah posted
                    if ($headerData->posted == 1) {
                        return view('otranskasi_proses_stok_opname.edit', [
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
                        "SELECT
                         FROM stockbd
                         LEFT JOIN brg ON stockbd.kd_brg = brg.kd_brg
                         WHERE stockbd.no_bukti=?
                         ORDER BY stockbd.rec",
                        [$no_bukti]
                    );

                    // Ambil barcode untuk setiap barang
                    foreach ($detail as $item) {
                        $brgInfo = DB::select(
                            "SELECT barcode FROM brg WHERE kd_brg=?",
                            [$item->kd_brg]
                        );
                        $item->barcode = ! empty($brgInfo) ? $brgInfo[0]->barcode : '';
                    }

                    $data['header']   = $headerData;
                    $data['detail']   = $detail;
                    $data['no_bukti'] = $no_bukti;
                } else {
                    $data['error'] = 'Data tidak ditemukan';
                }
            }

            return view('otranskasi_proses_stok_opname.edit', $data);
        } catch (\Exception $e) {
            return view('otranskasi_proses_stok_opname.edit', [
                'no_bukti' => '+',
                'status'   => 'simpan',
                'header'   => (object) [
                    'no_bukti' => '+',
                    'tgl'      => date('Y-m-d'),
                    'sub'      => '',
                    'notes'    => '',
                ],
                'detail'   => [],
                'periode'  => session('periode', date('m.Y')),
                'cbg'      => $this->getValidCbg(),
                'error'    => 'Terjadi kesalahan: ' . $e->getMessage(),
            ]);
        }
    }

    public function koreksi(Request $request)
    {
        try {
            $no_bukti = $request->get('no_bukti', '+');
            $status   = $request->get('status', 'simpan');
            $periode  = session('periode', date('m.Y'));

            // Handle if periode is an array
            if (is_array($periode)) {
                $periode = $periode['bulan'] . '.' . $periode['tahun'];
            }

            $cbg = $this->getValidCbg();

            // Cek periode posted
            $periodeCheck = DB::select(
                "SELECT posted FROM perid WHERE kd_peri=?",
                [$periode]
            );

            if (! empty($periodeCheck) && $periodeCheck[0]->posted == 1) {
                return view('otranskasi_proses_stok_opname.koreksi', [
                    'error'    => 'Closed Period',
                    'periode'  => $periode,
                    'cbg'      => $cbg,
                    'status'   => $status,
                    'no_bukti' => '+',
                    'header'   => (object) [
                        'no_bukti' => '+',
                        'tgl'      => date('Y-m-d'),
                        'sub'      => '',
                        'notes'    => '',
                    ],
                    'detail'   => [],
                ]);
            }

            if ($status == 'edit' && $no_bukti && $no_bukti != '+') {
                // Ambil header
                $header = DB::select(
                    "SELECT no_bukti, tgl, sub, posted
                     FROM stockb
                     WHERE no_bukti=? AND flag='AO'",
                    [$no_bukti]
                );

                if (! empty($header)) {
                    $headerData = $header[0];

                    // Cek apakah sudah posted
                    if ($headerData->posted == 1) {
                        return view('otranskasi_proses_stok_opname.koreksi', [
                            'error'    => 'Transaksi sudah di Posting !!',
                            'periode'  => $periode,
                            'cbg'      => $cbg,
                            'status'   => $status,
                            'no_bukti' => $no_bukti,
                            'header'   => $headerData,
                            'detail'   => [],
                        ]);
                    }

                    // Ambil detail dari lapbhd
                    $detail = DB::select(
                        "SELECT stockbd.no_id, stockbd.rec, stockbd.kd_brg, stockbd.na_brg,
                                stockbd.hj, stockbd.saldo, brg.supp as SUPP,
                                IFNULL(stockbd.cek, 0) as cek, brg.sub as SUB, '' as STAND
                         FROM stockbd
                         LEFT JOIN brg ON stockbd.kd_brg = brg.kd_brg
                         WHERE stockbd.no_bukti=?
                         ORDER BY stockbd.rec",
                        [$no_bukti]
                    );

                    // Ambil barcode untuk setiap barang
                    foreach ($detail as $item) {
                        $brgInfo = DB::select(
                            "SELECT barcode FROM brg WHERE kd_brg=?",
                            [$item->kd_brg]
                        );
                        $item->barcode = ! empty($brgInfo) ? $brgInfo[0]->barcode : '';
                    }

                    $data['header']   = $headerData;
                    $data['detail']   = $detail;
                    $data['no_bukti'] = $no_bukti;
                    $data['status']   = $status;
                } else {
                    $data['error'] = 'Data tidak ditemukan';
                }
            } else {
                $data = [
                    'no_bukti' => '+',
                    'status'   => $status,
                    'header'   => (object) [
                        'no_bukti' => '+',
                        'tgl'      => date('Y-m-d'),
                        'sub'      => '',
                        'notes'    => '',
                    ],
                    'detail'   => [],
                    'periode'  => $periode,
                    'cbg'      => $cbg,
                    'error'    => null,
                ];
            }

            return view('otranskasi_proses_stok_opname.koreksi', $data);
        } catch (\Exception $e) {
            return view('otranskasi_proses_stok_opname.koreksi', [
                'no_bukti' => '+',
                'status'   => 'simpan',
                'header'   => (object) [
                    'no_bukti' => '+',
                    'tgl'      => date('Y-m-d'),
                    'sub'      => '',
                    'notes'    => '',
                ],
                'detail'   => [],
                'periode'  => session('periode', date('m.Y')),
                'cbg'      => $this->getValidCbg(),
                'error'    => 'Terjadi kesalahan: ' . $e->getMessage(),
            ]);
        }
    }

    public function store(Request $request)
    {
        try {

            DB::beginTransaction();

            $no_bukti = trim($request->no_bukti);
            $status   = $request->status;
            $periode  = session('periode', date('m.Y'));

            // Handle if periode is an array
            if (is_array($periode)) {
                $periode = $periode['bulan'] . '.' . $periode['tahun'];
            }

            $cbg      = $this->getValidCbg();
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

            // Get details - handle both 'detail' and 'details'
            $details = $request->input('details', $request->input('detail', []));

            if (empty($details) || ! is_array($details)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Detail barang harus diisi',
                ], 400);
            }

            // Generate nomor bukti untuk simpan baru
            if ($status == 'simpan' && $no_bukti == '+') {
                // Ambil tipe toko
                $tokoInfo = DB::select(
                    "SELECT type FROM toko WHERE kode=?",
                    [$cbg]
                );
                $kode2 = ! empty($tokoInfo) ? $tokoInfo[0]->type : '';

                $kode = 'SF' . substr($periode, -2) . substr($periode, 0, 2);

                // Ambil nomor terakhir dari notrans
                $lastNo = DB::select(
                    "SELECT NOM" . $periode_month . " as no_bukti
                     FROM notrans
                     WHERE trans='SOFC' AND PER=?",
                    [$periode_year]
                );

                $r1 = ! empty($lastNo) ? intval($lastNo[0]->no_bukti) : 0;
                $r1 = $r1 + 1;

                // Update notrans
                DB::statement(
                    "UPDATE notrans
                     SET NOM" . $periode_month . "=?
                     WHERE trans='SOFC' AND PER=?",
                    [$r1, $periode_year]
                );

                $bkt1     = str_pad($r1, 4, '0', STR_PAD_LEFT);
                $no_bukti = $kode . '-' . $bkt1 . $kode2;
            }

            if ($status == 'simpan') {
                // Insert header
                DB::statement(
                    "INSERT INTO lapbh (NO_BUKTI, TGL, SUB, USRNM, TG_SMP, CBG, FLAG)
                     VALUES (?, ?, ?, ?, NOW(), ?, 'SF')",
                    [
                        $no_bukti,
                        $request->tgl,
                        trim($request->sub),
                        $username,
                        $cbg,
                    ]
                );
            } else {
                // Update header
                DB::statement(
                    "UPDATE lapbh
                     SET TGL=?, SUB=?, USRNM=?, TG_SMP=NOW()
                     WHERE NO_BUKTI=?",
                    [
                        $request->tgl,
                        trim($request->sub),
                        $username,
                        $no_bukti,
                    ]
                );
            }

            // Ambil ID header
            $headerId = DB::select(
                "SELECT no_id FROM lapbh WHERE no_bukti=?",
                [$no_bukti]
            );
            $id = ! empty($headerId) ? $headerId[0]->no_id : 0;

            // Hapus detail lama jika edit
            if ($status == 'edit') {
                DB::statement("DELETE FROM lapbhd WHERE no_bukti=?", [$no_bukti]);
            }

            // Insert detail
            $rec = 1;

            Log::info('TProsesStockOpname store details', [
                'details' => $details,
                'count'   => count($details ?? [])
            ]);

            foreach ($details as $detail) {
                // Handle both array and object notation
                $kd_brg = is_array($detail) ? ($detail['kd_brg'] ?? '') : ($detail->kd_brg ?? '');
                $cek    = is_array($detail) ? ($detail['cek'] ?? 0) : ($detail->cek ?? 0);

                if (! empty($kd_brg) && $cek == 1) {
                    $na_brg = is_array($detail) ? ($detail['na_brg'] ?? '') : ($detail->na_brg ?? '');
                    $hj     = is_array($detail) ? ($detail['hj'] ?? 0) : ($detail->hj ?? 0);
                    $saldo  = is_array($detail) ? ($detail['saldo'] ?? 0) : ($detail->saldo ?? 0);

                    DB::statement(
                        "INSERT INTO lapbhd (NO_BUKTI, REC, KD_BRG, NA_BRG, HJ, SALDO, FLAG, ID)
                         VALUES (?, ?, ?, ?, ?, ?, 'SF', ?)",
                        [
                            $no_bukti,
                            $rec,
                            trim($kd_brg),
                            trim($na_brg),
                            floatval($hj),
                            floatval($saldo),
                            $id,
                            1,
                        ]
                    );
                    $rec++;
                }
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

    // public function storeKoreksiSo(Request $request)
    // {
    //     try {

    //         DB::beginTransaction();

    //         $no_bukti = trim($request->no_bukti);
    //         $status   = $request->status;
    //         $periode  = session('periode', date('m.Y'));
    //         $cbg      = Auth::user()->CBG ?? 'TGZ';
    //         $username = Auth::user()->username ?? 'system';

    //         if (is_array($periode)) {
    //             $periode = $periode['bulan'] . '.' . $periode['tahun'];
    //         }

    //         $bulanPeriode = substr($periode, 0, 2);
    //         $tahunPeriode = substr($periode, -4);

    //         $tgl = Carbon::parse($request->tgl);
    //         if ($tgl->format('m') != $bulanPeriode || $tgl->format('Y') != $tahunPeriode) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => "Tanggal tidak sesuai periode",
    //             ], 400);
    //         }

    //         $flg = ($request->type == 'BSO') ? 'AO' : 'AK';

    //         if ($status == 'simpan' && $no_bukti == '+') {

    //             $toko  = DB::select("SELECT type FROM toko WHERE kode=?", [$cbg]);
    //             $kode2 = ! empty($toko) ? $toko[0]->type : '';

    //             $kode = "AS" . substr($periode, -2) . substr($periode, 0, 2);

    //             // Ambil nomor terakhir
    //             $cekNo = DB::select(
    //                 "SELECT NOM{$bulanPeriode} AS no_bukti
    //              FROM notrans
    //              WHERE trans='KASISTEN' AND PER=?",
    //                 [$tahunPeriode]
    //             );

    //             $r1 = ! empty($cekNo) ? intval($cekNo[0]->no_bukti) : 0;
    //             $r1++;

    //             DB::statement(
    //                 "UPDATE notrans SET NOM{$bulanPeriode}=? WHERE trans='KASISTEN' AND PER=?",
    //                 [$r1, $tahunPeriode]
    //             );

    //             $formatNo = str_pad($r1, 4, '0', STR_PAD_LEFT);
    //             $no_bukti = $kode . "-" . $formatNo . $kode2;
    //         }

    //         if ($status == 'simpan') {

    //             DB::statement(
    //                 "INSERT INTO STOCKB (NO_BUKTI, TGL, FLAG, PER, TOTAL_QTY, NOTES, USRNM, TG_SMP, TYPE, CBG, SUB, NOLAP, TOTAL)
    //              VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?)",
    //                 [
    //                     $no_bukti,
    //                     $request->tgl,
    //                     $flg,
    //                     $periode,
    //                     $request->total_qty,
    //                     trim($request->notes),
    //                     $username,
    //                     $request->type,
    //                     $cbg,
    //                     $request->sub,
    //                     $request->nolap,
    //                     $request->total,
    //                 ]
    //             );

    //         } else {

    //             DB::statement("CALL STOCKBDEL(?)", [$no_bukti]);

    //             DB::statement(
    //                 "UPDATE STOCKB
    //              SET TGL=?, NOTES=?, TOTAL_QTY=?, USRNM=?, TG_SMP=NOW(), TOTAL=?
    //              WHERE NO_BUKTI=?",
    //                 [
    //                     $request->tgl,
    //                     trim($request->notes),
    //                     $request->total_qty,
    //                     $username,
    //                     $request->total,
    //                     $no_bukti,
    //                 ]
    //             );
    //         }

    //         $h        = DB::select("SELECT no_id FROM STOCKB WHERE no_bukti=?", [$no_bukti]);
    //         $idHeader = ! empty($h) ? $h[0]->no_id : 0;

    //         // =============================
    //         // 3. SINKRONISASI DETAIL STOCKBD
    //         // =============================

    //         $detailDB    = DB::select("SELECT no_id FROM STOCKBD WHERE no_bukti=?", [$no_bukti]);
    //         $detailInput = $request->detail;
    //         $existing    = collect($detailDB)->pluck('no_id')->toArray();

    //         foreach ($existing as $rowDb) {

    //             $found = collect($detailInput)->firstWhere('no_id', $rowDb);

    //             if ($found) {
    //                 DB::statement(
    //                     "UPDATE STOCKBD SET REC=?, KD_BRG=?, NA_BRG=?, ket_kem=?, QTY=?, KET=?, riil=?, total=?
    //                  WHERE NO_ID=?",
    //                     [
    //                         $found['rec'],
    //                         $found['kd_brg'],
    //                         $found['na_brg'],
    //                         $found['ket_kem'],
    //                         $found['qty'],
    //                         $found['ket'],
    //                         $found['riil'],
    //                         $found['total'],
    //                         $rowDb,
    //                     ]
    //                 );
    //             } else {
    //                 // DELETE
    //                 DB::statement("DELETE FROM STOCKBD WHERE NO_ID=?", [$rowDb]);
    //             }
    //         }

    //         // Input baru
    //         foreach ($detailInput as $row) {

    //             if (intval($row['no_id']) == 0) {

    //                 DB::statement(
    //                     "INSERT INTO STOCKBD
    //                  (NO_BUKTI, REC, PER, FLAG, KD_BRG, itemsub, NA_BRG, ket_uk, ket_kem, kd, hj, saldo, lph, cat, QTY, riil, total, KET, ID)
    //                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
    //                     [
    //                         $no_bukti,
    //                         $row['rec'],
    //                         $periode,
    //                         $flg,
    //                         $row['kd_brg'],
    //                         $row['itemsub'],
    //                         $row['na_brg'],
    //                         $row['ket_uk'],
    //                         $row['ket_kem'],
    //                         $row['kd'],
    //                         $row['hj'],
    //                         $row['saldo'],
    //                         $row['lph'],
    //                         $row['cat'],
    //                         $row['qty'],
    //                         $row['riil'],
    //                         $row['total'],
    //                         $row['ket'],
    //                         $idHeader,
    //                     ]
    //                 );
    //             }
    //         }

    //         // =============================
    //         // 4. CALL PROCEDURE STOCKBINS
    //         // =============================

    //         DB::statement("CALL STOCKBINS(?)", [$no_bukti]);

    //         // =============================
    //         // 5. UPDATE LAPBH POSTED
    //         // =============================

    //         DB::statement(
    //             "UPDATE lapbh SET posted=1 WHERE no_bukti=?",
    //             [$request->nolap]
    //         );

    //         DB::commit();

    //         return response()->json([
    //             'success'  => true,
    //             'message'  => 'Save Data Success',
    //             'no_bukti' => $no_bukti,
    //         ]);

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Error: ' . $e->getMessage(),
    //         ], 500);
    //     }
    // }
    public function storeKoreksiSo(Request $request)
    {
        DB::transaction(function () use ($request) {

            /** FLAG */
            $flag    = $request->type === 'BSO' ? 'AO' : 'AK';
            $periode = session('periode');

            if (is_array($periode)) {
                $bulan   = str_pad($periode['bulan'], 2, '0', STR_PAD_LEFT);
                $tahun   = $periode['tahun'];
                $periode = $bulan . '/' . $tahun;
            } else {
                [$bulan, $tahun] = explode('/', $periode);
            }

            $cbg      = Auth::user()->CBG;
            $username = Auth::user()->username;

            if ($request->no_bukti === '+') {

                $kode2 = DB::table('toko')
                    ->where('KODE', $cbg)
                    ->value('TYPE');

                $nomor = DB::table('notrans')
                    ->where('TRANS', 'KASISTEN')
                    ->where('PER', $tahun)
                    ->value("NOM{$bulan}");

                $nomor++;

                DB::table('notrans')
                    ->where('TRANS', 'KASISTEN')
                    ->where('PER', $tahun)
                    ->update(["NOM{$bulan}" => $nomor]);

                $noBukti = 'AS' . substr($periode, -2) . $bulan . '-' .
                str_pad($nomor, 4, '0', STR_PAD_LEFT) . $kode2;
            } else {
                $noBukti = $request->no_bukti;
            }

            /** =============================
             * INSERT / UPDATE STOCKB
             * ============================= */
            if ($request->status === 'simpan') {

                DB::table('stockb')->insert([
                    'no_bukti'  => $noBukti,
                    'tgl'       => $request->tgl,
                    'flag'      => $flag,
                    'per'       => $periode,
                    'notes'     => '',
                    'usrnm'     => $username,
                    'tg_smp'    => now(),
                    'type'      => $request->type,
                    'cbg'       => $cbg,
                    'sub'       => $request->sub,
                    'nolap'     => $request->no_so,
                    'total_qty' => 0,
                    'total'     => 0,
                ]);

            } else {

                DB::statement('CALL STOCKBDEL(?)', [$noBukti]);

                DB::table('stockb')
                    ->where('no_bukti', $noBukti)
                    ->update([
                        'tgl'       => $request->tgl,
                        'notes'     => '',
                        'total_qty' => 0,
                        'total'     => 0,
                        'usrnm'     => auth()->user()->name,
                        'tg_smp'    => now(),
                    ]);
            }

            /** =============================
             * DETAIL STOCKBD
             * ============================= */
            $headerId = DB::table('stockb')
                ->where('no_bukti', $noBukti)
                ->value('no_id');

            foreach ($request->detail as $i => $row) {

                if ($row['no_id'] == 0) {
                    // INSERT
                    DB::table('stockbd')->insert([
                        'no_bukti' => $noBukti,
                        'rec'      => $i + 1,
                        'per'      => $periode,
                        'flag'     => $flag,
                        'kd_brg'   => $row['kd_brg'],
                        'na_brg'   => $row['na_brg'],
                        'qty'      => $row['qty'],
                        'riil'     => $row['riil'],
                        'total'    => $row['total'],
                        'ket'      => $row['ket'] ?? '',
                        'id'       => $headerId,
                        'saldo2'   => $row['qty_trans'] ?? 0,
                    ]);
                } else {
                    // UPDATE
                    DB::table('stockbd')
                        ->where('no_id', $row['no_id'])
                        ->update([
                            'rec'   => $i + 1,
                            'qty'   => $row['qty'],
                            'riil'  => $row['riil'],
                            'total' => $row['total'],
                            'ket'   => $row['ket'] ?? '',
                        ]);
                }
            }

            /** =============================
             * PROCEDURE & UPDATE LANJUTAN
             * ============================= */
            DB::statement('CALL STOCKBINS(?)', [$noBukti]);

            DB::table('lapbh')
                ->where('no_bukti', $request->nolap)
                ->update(['posted' => 1]);
        });

        return response()->json([
            'status'  => true,
            'message' => 'Save Data Success',
        ]);
    }

    public function browse(Request $request)
    {
        try {
            $cbg      = Auth::user()->CBG;
            $q        = $request->get('q', '');
            $sub      = $request->get('sub', '');
            $item1    = $request->get('item1', '');
            $item2    = $request->get('item2', '');
            $supp     = $request->get('supp', '');
            $tat      = $request->get('tat', null);
            $lph1     = $request->get('lph1', null);
            $lph2     = $request->get('lph2', null);
            $cbkdlaku = trim($request->get('cbkdlaku', 'ALL'));
            // dd($tat);

            $query = DB::table('brg')
                ->join('brgdt', 'brg.KD_BRG', '=', 'brgdt.KD_BRG')
                ->select(
                    'brg.KD_BRG',
                    'brg.NA_BRG',
                    'brg.KET_KEM',
                    'brg.KET_UK',
                    DB::raw("CONCAT(brg.kdbar,'-',brg.SUB) AS itemsub"),
                    DB::raw("CONCAT(brgdt.KDLAKU,brgdt.KLK) AS kd"),
                    'brgdt.HJ',
                    'brgdt.AK00 AS saldo',
                    'brgdt.lph'
                )
                ->where('brgdt.cbg', $cbg)
                ->where(DB::raw('brgdt.yer'), DB::raw('YEAR(NOW())'));

            // ========== KDLaku Optional ==========
            if ($cbkdlaku !== 'ALL') {
                if ($cbkdlaku === '3') {
                    $query->whereRaw("LEFT(brgdt.na_brg,1)='3'");
                } else {
                    $query->where('brgdt.kdlaku', intval($cbkdlaku));
                }
            }

            // // ========== TAT Optional ==========
            if (! empty($tat)) {
                $query->whereRaw("DATEDIFF(DATE(NOW()), DATE(brgdt.tgl_at)) >= ?", [$tat]);
            }

            // ========== LPH Optional ==========
            if ($lph1 !== null && $lph2 !== null) {
                $query->whereBetween('brgdt.lph', [$lph1, $lph2]);
            }

            // ========== SUB Optional ==========
            if (! empty($sub)) {
                $query->where('brg.sub', $sub);
            }

            if (! empty($supp)) {
                $query->where('brg.SUPP', $supp);
            }

            // // ========== ITEM RANGE Optional ==========
            if (! empty($item1)) {
                $query->where('brg.kdbar', '>=', $item1);
            }

            if (! empty($item2)) {
                $query->where('brg.kdbar', '<=', $item2);
            }

            $data = $query->orderBy('brg.KD_BRG')->get();

            return response()->json($data);

        } catch (\Exception $e) {

            Log::error('TProsesStockOpname browse error', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getDetail(Request $request)
    {
        try {
            $kd_brg = $request->get('kd_brg');
            $cbg    = $this->getValidCbg();

            Log::info('TProsesStockOpname getDetail', [
                'kd_brg' => $kd_brg,
                'cbg'    => $cbg,
            ]);

            $barang = DB::select(
                "SELECT A.kd_brg as KD_BRG, TRIM(CONCAT(A.na_brg, ' ', A.ket_uk)) as NA_BRG,
                        A.sub as SUB, '' as KDBAR, A.ket_uk as KET_UK, '' as STAND,
                        B.hj as HJ, B.hb as HB, 0 as saldo, A.supp as SUPP, A.barcode as BARCODE
                 FROM brg A
                 INNER JOIN brgdt B ON A.kd_brg=B.kd_brg
                 WHERE B.cbg=? AND B.yer=YEAR(NOW()) AND A.kd_brg=?",
                [$cbg, $kd_brg]
            );

            if (! empty($barang)) {
                Log::info('TProsesStockOpname getDetail found', ['kd_brg' => $kd_brg]);

                return response()->json([
                    'success' => true,
                    'exists'  => true,
                    'data'    => $barang[0],
                ]);
            }

            Log::warning('TProsesStockOpname getDetail not found', ['kd_brg' => $kd_brg]);

            return response()->json([
                'success' => false,
                'exists'  => false,
                'message' => 'Barang tidak ditemukan',
            ]);
        } catch (\Exception $e) {
            Log::error('TProsesStockOpname getDetail error', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

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
                "SELECT posted FROM lapbh WHERE no_bukti=? AND flag='SF'",
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

            DB::statement("DELETE FROM lapbhd WHERE no_bukti=?", [$no_bukti]);

            DB::statement("DELETE FROM lapbh WHERE no_bukti=? AND flag='SF'", [$no_bukti]);

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

    public function printProsesStockOpname(Request $request)
    {
        try {
            $no_bukti = $request->get('nobukti');
            $cbg      = $this->getValidCbg();

            $TGL = Carbon::now()->format('d/m/Y');
            $JAM = Carbon::now()->addHour()->toTimeString();

            $tokoInfo = DB::select(
                "SELECT na_toko FROM toko WHERE kode=?",
                [$cbg]
            );
            $toko = ! empty($tokoInfo) ? $tokoInfo[0]->na_toko : '';

            $data = DB::select("SELECT
                                ? AS NA_TOKO,
                                lapbh.*,
                                lapbhd.*,
                                CONCAT(LEFT(lapbh.no_bukti, 2), RIGHT(lapbh.no_bukti, 5)) AS bukt,
                                IF(LEFT(lapbh.no_bukti, 2) = 'XO', qty_apps, '') AS RIL
                            FROM lapbh
                            JOIN lapbhd ON lapbh.no_bukti = lapbhd.no_bukti
                            WHERE TRIM(lapbh.no_bukti) = TRIM(?)
                            ORDER BY lapbhd.kd_brg
                        ", [$toko, $no_bukti]);

            $file         = 'print_proses_stock_opname';
            $PHPJasperXML = new PHPJasperXML();
            $PHPJasperXML->load_xml_file(base_path("/app/reportc01/phpjasperxml/{$file}.jrxml"));

            $cleanData                    = json_decode(json_encode($data), true);
            $PHPJasperXML->arrayParameter = [
                "TGL" => $TGL,
                "JAM" => $JAM,
            ];

            $PHPJasperXML->setData($cleanData);

            ob_end_clean();
            $PHPJasperXML->outpage("I");

        } catch (\Exception $e) {

            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function buatSO2(Request $request)
    {
        try {
            $no_bukti = $request->no_bukti;
            $cbg      = $this->getValidCbg();
            $user     = auth()->user()->username ?? 'SYSTEM';

            // Validasi prefix XO / XG
            $prefix = substr($no_bukti, 0, 2);
            if ($prefix !== 'XO' && $prefix !== 'XG') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya bukti XO atau XG yang dapat diproses.',
                ]);
            }

            $result = DB::select("
            CALL pjl_buatso_scan(:prosx, :cbgx, :buktix, :userx)
        ", [
                'prosx'  => 'PROSES_BUKTI',
                'cbgx'   => $cbg,
                'buktix' => $no_bukti,
                'userx'  => $user,
            ]);

            $buktiBaru = $result[0]->BUKTI ?? '';

            if ($buktiBaru !== '') {
                return response()->json([
                    'success'    => true,
                    'message'    => 'SO2 berhasil dibuat',
                    'bukti_baru' => $buktiBaru,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'SO baru tidak dapat dibuat.',
                ]);
            }

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function browseKoreksiSo(Request $request)
    {

        DB::beginTransaction();
        try {
            $nolap   = strtoupper(trim($request->no_so));
            $cbg     = Auth::user()->CBG;
            $periode = session('periode', date('m.Y'));

            if (is_array($periode)) {
                $periode = $periode['bulan'] . '/' . $periode['tahun'];
            }

            $flag = substr($nolap, 0, 2);

            if ($flag === 'SZ') {
                $flagDb = 'BZ';
            } elseif ($flag === 'XO') {
                $flagDb = 'SO';
            } else {
                $flagDb = $flag;
            }

            $bukti = DB::table('lapbh')
                ->whereRaw("CONCAT(LEFT(no_bukti,2),RIGHT(no_bukti,5)) = ?", [$nolap])
                ->where('flag', $flagDb)
                ->max('no_bukti');

            if (! $bukti) {
                throw new \Exception('Bukti tidak ditemukan...!');
            }

            $header = DB::table('lapbh')
                ->where('no_bukti', $bukti)
                ->where('cbg', $cbg)
                ->first();

            if (! $header) {
                throw new \Exception('Bukti tidak ditemukan...!');
            }

            if ($header->posted == 1) {
                throw new \Exception('Sudah ada Koreksi dengan nomor SO ini...!');
            }

            $type        = '';
            $showQtyApps = false;

            if (in_array($header->flag, ['BZ', '3Z'])) {
                $type = 'LBK';
            } elseif ($header->flag === 'BT') {
                $type = 'TAT';
            } elseif ($header->flag === 'SO') {
                $type        = 'BSO';
                $showQtyApps = true;
            }

            $details = DB::table('lapbhd')
                ->where('no_bukti', $bukti)
                ->orderBy('kd_brg')
                ->get();

            $rows = [];

            foreach ($details as $brg) {

                /* ambil stok & harga */

                $brgdt = DB::table('brgdt')
                    ->where('KD_BRG', $brg->kd_brg)
                    ->where('CBG', $cbg)
                    ->where('YER', now()->year)
                    ->first();

                $saldo = $brgdt->AK00 ?? 0;
                $harga = $brgdt->HB;

                /* ===============================
             * riil & qty apps
             * =============================== */
                $riil      = $saldo;
                $qty_apps  = $brg->QTY_APPS ?? 0;
                $qty_trans = $brg->QTY_TRANS ?? 0;

                if ($type === 'BSO') {
                    $riil = $qty_apps == 0 ? $saldo : $qty_apps;

                    if (substr($nolap, 0, 2) === 'XO') {
                        $riil      = $brg->QTY_APPS;
                        $saldo     = $brg->SALDO;
                        $qty_trans = $brg->QTY_TRANS;
                    }
                }

                /* ===============================
             * QTY INDIKATOR
             * =============================== */
                $rekap = DB::table('synchron.rekap_stok_' . $cbg)
                    ->where('per', $periode)
                    ->where('kd_brg', $brg->kd_brg)
                    ->first();

                $qty_indi = $rekap->akhir_tk ?? 0;

                $rows[] = [
                    'kd_brg'    => $brg->kd_brg,
                    'itemsub'   => $brg->itemsub,
                    'na_brg'    => $brg->na_brg,
                    'ket_uk'    => $brg->ket_uk,
                    'ket_kem'   => $brg->ket_kem,
                    'saldo'     => $saldo,
                    'harga'     => $harga,
                    'kd'        => $brg->kd,
                    'hj'        => $brg->hj,
                    'qty'       => $qty_indi,
                    'qty_trans' => $qty_trans,
                    'riil'      => $riil,
                    'qty_apps'  => $qty_apps,
                    'qty_indi'  => $qty_indi,
                ];
            }

            DB::commit();
            // dd($rows);

            return response()->json([
                'status'        => true,
                'no_bukti'      => $header->no_bukti,
                'sub'           => $header->sub,
                'type'          => $type,
                'show_qty_apps' => $showQtyApps,
                'data'          => $rows,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'msg'    => $e->getMessage(),
            ], 400);
        }
    }

}
