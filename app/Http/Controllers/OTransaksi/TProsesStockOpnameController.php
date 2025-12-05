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

    public function getProsesStockOpname(Request $request)
    {
        try {
            $periode = session('periode', date('m.Y'));
            $cbg     = $this->getValidCbg();

            Log::info('TProsesStockOpname getProsesStockOpname', [
                'periode' => $periode,
                'cbg'     => $cbg,
            ]);

            $query = DB::select(
                "SELECT NO_BUKTI, TGL, SUB, USRNM, POSTED
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
                    $btnPrint  = '<button onclick="printData(\'' . $row->NO_BUKTI . '\')" class="btn btn-sm btn-info ml-1" title="Print"><i class="fas fa-print"></i></button>';
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

                    // Ambil detail dari lapbhd
                    $detail = DB::select(
                        "SELECT lapbhd.no_id, lapbhd.rec, lapbhd.kd_brg, lapbhd.na_brg,
                                lapbhd.hj, lapbhd.saldo, brg.supp as SUPP,
                                IFNULL(lapbhd.cek, 0) as cek, brg.sub as SUB, '' as STAND
                         FROM lapbhd
                         LEFT JOIN brg ON lapbhd.kd_brg = brg.kd_brg
                         WHERE lapbhd.no_bukti=?
                         ORDER BY lapbhd.rec",
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
                        "SELECT lapbhd.no_id, lapbhd.rec, lapbhd.kd_brg, lapbhd.na_brg,
                                lapbhd.hj, lapbhd.saldo, brg.supp as SUPP,
                                IFNULL(lapbhd.cek, 0) as cek, brg.sub as SUB, '' as STAND
                         FROM lapbhd
                         LEFT JOIN brg ON lapbhd.kd_brg = brg.kd_brg
                         WHERE lapbhd.no_bukti=?
                         ORDER BY lapbhd.rec",
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
            // Log request data for debugging (simplified to avoid array to string conversion)
            Log::info('TProsesStockOpname store request', [
                'no_bukti'     => $request->no_bukti,
                'tgl'          => $request->tgl,
                'sub'          => $request->sub,
                'status'       => $request->status,
                'detail_count' => count($request->input('detail', []))
            ]);

            $this->validate($request, [
                'tgl' => 'required|date',
                'sub' => 'required',
            ]);

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

    public function storeKoreksiSo(Request $request)
    {
        try {

            $this->validate($request, [
                'tgl'      => 'required|date',
                'no_bukti' => 'required',
                'type'     => 'required',
            ]);

            DB::beginTransaction();

            $no_bukti = trim($request->no_bukti);
            $status   = $request->status;
            $periode  = session('periode', date('m.Y'));
            $cbg      = Auth::user()->CBG ?? 'TGZ';
            $username = Auth::user()->username ?? 'system';

            if (is_array($periode)) {
                $periode = $periode['bulan'] . '.' . $periode['tahun'];
            }

            $bulanPeriode = substr($periode, 0, 2);
            $tahunPeriode = substr($periode, -4);

            $tgl = Carbon::parse($request->tgl);
            if ($tgl->format('m') != $bulanPeriode || $tgl->format('Y') != $tahunPeriode) {
                return response()->json([
                    'success' => false,
                    'message' => "Tanggal tidak sesuai periode",
                ], 400);
            }

            // Tentukan FLAG (AO / AK)
            $flg = ($request->type == 'BSO') ? 'AO' : 'AK';

            if ($status == 'simpan' && $no_bukti == '+') {

                $toko  = DB::select("SELECT type FROM toko WHERE kode=?", [$cbg]);
                $kode2 = ! empty($toko) ? $toko[0]->type : '';

                $kode = "AS" . substr($periode, -2) . substr($periode, 0, 2);

                // Ambil nomor terakhir
                $cekNo = DB::select(
                    "SELECT NOM{$bulanPeriode} AS no_bukti
                 FROM notrans
                 WHERE trans='KASISTEN' AND PER=?",
                    [$tahunPeriode]
                );

                $r1 = ! empty($cekNo) ? intval($cekNo[0]->no_bukti) : 0;
                $r1++;

                DB::statement(
                    "UPDATE notrans SET NOM{$bulanPeriode}=? WHERE trans='KASISTEN' AND PER=?",
                    [$r1, $tahunPeriode]
                );

                $formatNo = str_pad($r1, 4, '0', STR_PAD_LEFT);
                $no_bukti = $kode . "-" . $formatNo . $kode2;
            }

            if ($status == 'simpan') {

                DB::statement(
                    "INSERT INTO STOCKB (NO_BUKTI, TGL, FLAG, PER, TOTAL_QTY, NOTES, USRNM, TG_SMP, TYPE, CBG, SUB, NOLAP, TOTAL)
                 VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?)",
                    [
                        $no_bukti,
                        $request->tgl,
                        $flg,
                        $periode,
                        $request->total_qty,
                        trim($request->notes),
                        $username,
                        $request->type,
                        $cbg,
                        $request->sub,
                        $request->nolap,
                        $request->total,
                    ]
                );

            } else {

                DB::statement("CALL STOCKBDEL(?)", [$no_bukti]);

                DB::statement(
                    "UPDATE STOCKB
                 SET TGL=?, NOTES=?, TOTAL_QTY=?, USRNM=?, TG_SMP=NOW(), TOTAL=?
                 WHERE NO_BUKTI=?",
                    [
                        $request->tgl,
                        trim($request->notes),
                        $request->total_qty,
                        $username,
                        $request->total,
                        $no_bukti,
                    ]
                );
            }

            $h        = DB::select("SELECT no_id FROM STOCKB WHERE no_bukti=?", [$no_bukti]);
            $idHeader = ! empty($h) ? $h[0]->no_id : 0;

            // =============================
            // 3. SINKRONISASI DETAIL STOCKBD
            // =============================

            $detailDB    = DB::select("SELECT no_id FROM STOCKBD WHERE no_bukti=?", [$no_bukti]);
            $detailInput = $request->detail;
            $existing    = collect($detailDB)->pluck('no_id')->toArray();

            foreach ($existing as $rowDb) {

                $found = collect($detailInput)->firstWhere('no_id', $rowDb);

                if ($found) {
                    DB::statement(
                        "UPDATE STOCKBD SET REC=?, KD_BRG=?, NA_BRG=?, ket_kem=?, QTY=?, KET=?, riil=?, total=?
                     WHERE NO_ID=?",
                        [
                            $found['rec'],
                            $found['kd_brg'],
                            $found['na_brg'],
                            $found['ket_kem'],
                            $found['qty'],
                            $found['ket'],
                            $found['riil'],
                            $found['total'],
                            $rowDb,
                        ]
                    );
                } else {
                    // DELETE
                    DB::statement("DELETE FROM STOCKBD WHERE NO_ID=?", [$rowDb]);
                }
            }

            // Input baru
            foreach ($detailInput as $row) {

                if (intval($row['no_id']) == 0) {

                    DB::statement(
                        "INSERT INTO STOCKBD
                     (NO_BUKTI, REC, PER, FLAG, KD_BRG, itemsub, NA_BRG, ket_uk, ket_kem, kd, hj, saldo, lph, cat, QTY, riil, total, KET, ID)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                        [
                            $no_bukti,
                            $row['rec'],
                            $periode,
                            $flg,
                            $row['kd_brg'],
                            $row['itemsub'],
                            $row['na_brg'],
                            $row['ket_uk'],
                            $row['ket_kem'],
                            $row['kd'],
                            $row['hj'],
                            $row['saldo'],
                            $row['lph'],
                            $row['cat'],
                            $row['qty'],
                            $row['riil'],
                            $row['total'],
                            $row['ket'],
                            $idHeader,
                        ]
                    );
                }
            }

            // =============================
            // 4. CALL PROCEDURE STOCKBINS
            // =============================

            DB::statement("CALL STOCKBINS(?)", [$no_bukti]);

            // =============================
            // 5. UPDATE LAPBH POSTED
            // =============================

            DB::statement(
                "UPDATE lapbh SET posted=1 WHERE no_bukti=?",
                [$request->nolap]
            );

            DB::commit();

            return response()->json([
                'success'  => true,
                'message'  => 'Save Data Success',
                'no_bukti' => $no_bukti,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
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

//         dd([
//     'sub' => $sub,
//     'item1' => $item1,
//     'item2' => $item2,
//     'supp' => $supp,
//     'cbg' => $cbg,
//     'tat' => $tat,
//     'lph1' => $lph1,
//     'lph2' => $lph2,
//     'cbkdlaku' => $cbkdlaku
// ]);

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

}