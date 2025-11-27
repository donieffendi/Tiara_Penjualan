<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TPengembalianKeGudangController extends Controller
{
    public function index($tipe = 'gudangumum')
    {
        try {
            Log::info('TPengembalianKeGudang index() started', ['tipe' => $tipe]);

            $periodeSession = session('periode', date('m.Y'));

            // Handle periode as array or string
            if (is_array($periodeSession)) {
                $periode = str_pad($periodeSession['bulan'], 2, '0', STR_PAD_LEFT) . '.' . $periodeSession['tahun'];
            } else {
                // Convert MM/YYYY or MM.YYYY to MM.YYYY
                $periode = str_replace('/', '.', $periodeSession);
            }

            $cbg = session('flag', Auth::user()->CBG ?? '01');

            // Tentukan label berdasarkan tipe
            $pageTitle = $tipe === 'dctanjungsari'
                ? 'Transaksi Pengembalian Barang ke Gudang - DC Tanjungsari'
                : 'Transaksi Pengembalian Barang ke Gudang - Umum';

            Log::info('TPengembalianKeGudang index() completed', [
                'tipe' => $tipe,
                'periode' => $periode,
                'cbg' => $cbg
            ]);

            return view('otranskasi_pengembalian_ke_gudang.index', compact('periode', 'cbg', 'tipe', 'pageTitle'));
        } catch (\Exception $e) {
            Log::error('Error in TPengembalianKeGudang index: ' . $e->getMessage(), [
                'tipe' => $tipe,
                'trace' => $e->getTraceAsString()
            ]);

            return view('otranskasi_pengembalian_ke_gudang.index', [
                'periode' => date('m.Y'),
                'cbg' => '01',
                'tipe' => $tipe,
                'pageTitle' => 'Error Loading Page',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function getPengembalianKeGudang(Request $request, $tipe = 'gudangumum')
    {
        try {
            Log::info('TPengembalianKeGudang getPengembalianKeGudang() started', [
                'tipe' => $tipe,
                'filters' => [
                    'no_bukti' => $request->no_bukti,
                    'notes' => $request->notes,
                    'per' => $request->per
                ]
            ]);

            $periodeSession = session('periode', date('m.Y'));

            // Handle periode as array or string
            if (is_array($periodeSession)) {
                $periode = str_pad($periodeSession['bulan'], 2, '0', STR_PAD_LEFT) . '.' . $periodeSession['tahun'];
            } else {
                $periode = str_replace('/', '.', $periodeSession);
            }

            $cbg = session('flag', Auth::user()->CBG ?? '01');

            // Filter berdasarkan tipe (DC atau Umum)
            $filterSup = $tipe === 'dctanjungsari'
                ? ' AND LEFT(KODES,3)="510" '
                : ' AND LEFT(KODES,3)<>"510" ';

            // Filter tambahan dari request
            $filterNoBukti = '';
            $filterNotes = '';
            $filterPer = '';

            if ($request->has('no_bukti') && !empty($request->no_bukti)) {
                $noBuktiEscaped = addslashes($request->no_bukti);
                $filterNoBukti = ' AND NO_BUKTI LIKE "%' . $noBuktiEscaped . '%" ';
                Log::info('Filter NO_BUKTI applied', ['value' => $request->no_bukti]);
            }

            if ($request->has('notes') && !empty($request->notes)) {
                $notesEscaped = addslashes($request->notes);
                $filterNotes = ' AND NOTES LIKE "%' . $notesEscaped . '%" ';
                Log::info('Filter NOTES applied', ['value' => $request->notes]);
            }

            if ($request->has('per') && !empty($request->per)) {
                $perEscaped = addslashes($request->per);
                $filterPer = ' AND per="' . $perEscaped . '" ';
                Log::info('Filter PER applied', ['value' => $request->per]);
            } else {
                $filterPer = ' AND per="' . $periode . '" ';
                Log::info('Filter PER default', ['value' => $periode]);
            }

            // Reset print flags
            DB::statement(
                "UPDATE stockb, stockbz SET stockb.print=0, stockbz.print=0
                 WHERE stockb.flag='TS' AND stockbz.flag='TS'
                 AND stockb.per=? AND stockbz.per=?
                 AND stockb.cbg=? AND stockbz.cbg=?",
                [$periode, $periode, $cbg, $cbg]
            );

            // Query union dari stockb dan stockbz
            $query = "SELECT NO_BUKTI, TGL, TOTAL_QTY, NOTES, TYPE, POSTED, print, 'a' as com
                      FROM stockb
                      WHERE flag='TS' AND cbg=? $filterSup $filterNoBukti $filterNotes $filterPer
                      UNION ALL
                      SELECT NO_BUKTI, TGL, TOTAL_QTY, NOTES, TYPE, POSTED, print, 'b' as com
                      FROM stockbz
                      WHERE date(tgl_posted)=date(now()) AND flag='TS' AND cbg=? $filterSup $filterNoBukti $filterNotes $filterPer
                      ORDER BY NO_BUKTI DESC";

            Log::info('Executing query', [
                'cbg' => $cbg,
                'filters' => [
                    'sup' => $filterSup,
                    'no_bukti' => $filterNoBukti,
                    'notes' => $filterNotes,
                    'per' => $filterPer
                ]
            ]);

            $data = DB::select($query, [$cbg, $cbg]);

            Log::info('Query executed', ['result_count' => count($data)]);

            return Datatables::of(collect($data))
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
                ->editColumn('print', function ($row) {
                    return $row->print == 1
                        ? '<input type="checkbox" class="chk-print" data-bukti="' . $row->NO_BUKTI . '" data-com="' . $row->com . '" checked disabled>'
                        : '<input type="checkbox" class="chk-print" data-bukti="' . $row->NO_BUKTI . '" data-com="' . $row->com . '">';
                })
                ->addColumn('action', function ($row) use ($tipe) {
                    $btnEdit = $row->POSTED == 0
                        ? '<button onclick="editData(\'' . $row->NO_BUKTI . '\')" class="btn btn-sm btn-primary" title="Edit"><i class="fas fa-edit"></i></button>'
                        : '<button class="btn btn-sm btn-secondary" disabled title="Sudah Posted"><i class="fas fa-lock"></i></button>';
                    $btnPrint = '<button onclick="printData(\'' . $row->NO_BUKTI . '\', \'' . $row->POSTED . '\')" class="btn btn-sm btn-info ml-1" title="Print"><i class="fas fa-print"></i></button>';
                    return $btnEdit . ' ' . $btnPrint;
                })
                ->rawColumns(['action', 'POSTED', 'print'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('Error in getPengembalianKeGudang: ' . $e->getMessage(), [
                'tipe' => $tipe,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'draw' => $request->draw ?? 0,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $e->getMessage()
            ]);
        }
    }

    public function edit(Request $request, $tipe = 'gudangumum')
    {
        try {
            $no_bukti = $request->get('no_bukti', '+');
            $status = $request->get('status', 'simpan');
            $period = session('periode', date('m.Y'));
            $periode = $period['bulan'] . '.' . $period['tahun'];
            $cbg = session('cbg', '01');
            $ma = session('ma', 'TGZ');

            // Tentukan label berdasarkan tipe
            $pageTitle = $tipe === 'dctanjungsari'
                ? 'Transaksi Pengembalian Barang ke Gudang - DC Tanjungsari'
                : 'Transaksi Pengembalian Barang ke Gudang - Umum';

            $data = [
                'no_bukti' => '+',
                'status' => $status,
                'header' => (object)[
                    'no_bukti' => '+',
                    'tgl' => date('Y-m-d'),
                    'total_qty' => 0,
                    'notes' => '',
                    'kodes' => $tipe === 'dctanjungsari' ? '510' : ''
                ],
                'detail' => [],
                'periode' => $periode,
                'cbg' => $cbg,
                'ma' => $ma,
                'tipe' => $tipe,
                'pageTitle' => $pageTitle,
                'error' => null
            ];

            if ($status == 'edit' && $no_bukti && $no_bukti != '+') {
                // Cek di stockb terlebih dahulu
                $header = DB::select(
                    "SELECT no_bukti, kodes, tgl, total_qty, notes, posted
                     FROM stockb
                     WHERE no_bukti=? AND flag='TS'",
                    [$no_bukti]
                );

                // Jika sudah diposting, cek di stockbz
                if (empty($header)) {
                    $header = DB::select(
                        "SELECT no_bukti, kodes, tgl, total_qty, notes, posted
                         FROM stockbz
                         WHERE no_bukti=? AND flag='TS'",
                        [$no_bukti]
                    );
                }

                if (!empty($header)) {
                    $headerData = $header[0];

                    // Cek apakah sudah posted
                    if ($headerData->posted == 1) {
                        return view('otranskasi_pengembalian_ke_gudang.edit', array_merge($data, [
                            'error' => 'Data sudah terposting !!',
                            'header' => $headerData
                        ]));
                    }

                    // Ambil detail dari stockbd
                    $detail = DB::select(
                        "SELECT no_id, rec, kd_brg, na_brg, ket_kem, qty, ket, jns, kdlaku, barcode
                         FROM stockbd
                         WHERE no_bukti=?
                         ORDER BY rec",
                        [$no_bukti]
                    );

                    // Ambil SPL untuk setiap barang
                    foreach ($detail as $item) {
                        $brgInfo = DB::select(
                            "SELECT SP_L, SP_LF FROM brg WHERE kd_brg=?",
                            [$item->kd_brg]
                        );

                        if (!empty($brgInfo)) {
                            if ($cbg == 'TMM') {
                                $item->spl = $brgInfo[0]->SP_L;
                            } elseif ($cbg == 'SOP') {
                                $item->spl = $brgInfo[0]->SP_LF;
                            } else {
                                $item->spl = '';
                            }
                        } else {
                            $item->spl = '';
                        }
                    }

                    $data['header'] = $headerData;
                    $data['detail'] = $detail;
                    $data['no_bukti'] = $no_bukti;
                } else {
                    $data['error'] = 'Data tidak ditemukan';
                }
            }

            return view('otranskasi_pengembalian_ke_gudang.edit', $data);
        } catch (\Exception $e) {
            return view('otranskasi_pengembalian_ke_gudang.edit', [
                'no_bukti' => '+',
                'status' => 'simpan',
                'header' => (object)[
                    'no_bukti' => '+',
                    'tgl' => date('Y-m-d'),
                    'total_qty' => 0,
                    'notes' => '',
                    'kodes' => $tipe === 'dctanjungsari' ? '510' : ''
                ],
                'detail' => [],
                'periode' => session('periode', date('m.Y')),
                'cbg' => session('cbg', '01'),
                'ma' => session('ma', 'TGZ'),
                'tipe' => $tipe,
                'pageTitle' => $tipe === 'dctanjungsari'
                    ? 'Transaksi Pengembalian Barang ke Gudang - DC Tanjungsari'
                    : 'Transaksi Pengembalian Barang ke Gudang - Umum',
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    public function store(Request $request, $tipe = 'gudangumum')
    {
        try {
            $this->validate($request, [
                'tgl' => 'required|date',
                'details' => 'required|array|min:1'
            ]);

            DB::beginTransaction();

            $no_bukti = trim($request->no_bukti);
            $status = $request->status;
            $period = session('periode', date('m.Y'));
            $periode = $period['bulan'] . '.' . $period['tahun'];
            $cbg = session('cbg', '01');
            $username = Auth::user()->username ?? 'system';
            $kodes = $tipe === 'dctanjungsari' ? '510' : '';

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

                $kode = 'TS' . substr($periode, -2) . substr($periode, 0, 2);

                // Ambil nomor terakhir dari notrans
                $lastNo = DB::select(
                    "SELECT NOM" . $periode_month . " as no_bukti
                     FROM notrans
                     WHERE trans='KTOKO' AND PER=?",
                    [$periode_year]
                );

                $r1 = !empty($lastNo) ? intval($lastNo[0]->no_bukti) : 0;
                $r1 = $r1 + 1;

                // Update notrans
                DB::statement(
                    "UPDATE notrans
                     SET NOM" . $periode_month . "=?
                     WHERE trans='KTOKO' AND PER=?",
                    [$r1, $periode_year]
                );

                $bkt1 = str_pad($r1, 4, '0', STR_PAD_LEFT);
                $no_bukti = $kode . '-' . $bkt1 . $kode2;
            }

            // Hitung total
            $totalQty = 0;
            foreach ($request->details as $detail) {
                if (!empty($detail['kd_brg'])) {
                    $qty = floatval($detail['qty'] ?? 0);
                    $totalQty += $qty;
                }
            }

            if ($status == 'simpan') {
                // Insert header
                DB::statement(
                    "INSERT INTO stockb (NO_BUKTI, KODES, TGL, FLAG, PER, TOTAL_QTY, NOTES, USRNM, TG_SMP, cbg)
                     VALUES (?, ?, ?, 'TS', ?, ?, ?, ?, NOW(), ?)",
                    [
                        $no_bukti,
                        $kodes,
                        $request->tgl,
                        $periode,
                        $totalQty,
                        trim($request->notes ?? ''),
                        $username,
                        $cbg
                    ]
                );
            } else {
                // Update header
                DB::statement(
                    "UPDATE stockb
                     SET TGL=?, NOTES=?, TOTAL_QTY=?, USRNM=?, TG_SMP=NOW()
                     WHERE NO_BUKTI=?",
                    [
                        $request->tgl,
                        trim($request->notes ?? ''),
                        $totalQty,
                        $username,
                        $no_bukti
                    ]
                );
            }

            // Ambil ID header
            $headerId = DB::select(
                "SELECT no_id FROM stockb WHERE no_bukti=?",
                [$no_bukti]
            );
            $id = !empty($headerId) ? $headerId[0]->no_id : 0;

            // Hapus detail lama jika edit
            if ($status == 'edit') {
                $existing = DB::select(
                    "SELECT no_id FROM stockbd WHERE no_bukti=?",
                    [$no_bukti]
                );

                $existingIds = array_column($existing, 'no_id');
                $keepIds = array_filter(array_column($request->details, 'no_id'));

                foreach ($existingIds as $oldId) {
                    if (!in_array($oldId, $keepIds)) {
                        DB::statement("DELETE FROM stockbd WHERE no_id=?", [$oldId]);
                    }
                }
            }

            // Insert/Update detail
            $rec = 1;
            foreach ($request->details as $detail) {
                if (!empty($detail['kd_brg'])) {
                    $qty = floatval($detail['qty'] ?? 0);

                    if (isset($detail['no_id']) && $detail['no_id'] > 0) {
                        // Update existing
                        DB::statement(
                            "UPDATE stockbd
                             SET KDLAKU=?, BARCODE=?, REC=?, KD_BRG=?, NA_BRG=?, ket_kem=?, QTY=?, KET=?, JNS=?, SISA=?
                             WHERE NO_ID=?",
                            [
                                trim($detail['kdlaku'] ?? ''),
                                trim($detail['barcode'] ?? ''),
                                $rec,
                                trim($detail['kd_brg']),
                                trim($detail['na_brg']),
                                trim($detail['ket_kem'] ?? ''),
                                $qty,
                                trim($detail['ket'] ?? ''),
                                trim($detail['jns'] ?? ''),
                                $qty,
                                $detail['no_id']
                            ]
                        );
                    } else {
                        // Insert new
                        DB::statement(
                            "INSERT INTO stockbd (KDLAKU, NO_BUKTI, REC, PER, FLAG, KD_BRG, NA_BRG, ket_kem, QTY, SISA, JNS, HJ, HB, KET, ID, BARCODE)
                             VALUES (?, ?, ?, ?, 'TS', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                            [
                                trim($detail['kdlaku'] ?? ''),
                                $no_bukti,
                                $rec,
                                $periode,
                                trim($detail['kd_brg']),
                                trim($detail['na_brg']),
                                trim($detail['ket_kem'] ?? ''),
                                $qty,
                                $qty,
                                trim($detail['jns'] ?? ''),
                                floatval($detail['hj'] ?? 0),
                                floatval($detail['hb'] ?? 0),
                                trim($detail['ket'] ?? ''),
                                $id,
                                trim($detail['barcode'] ?? '')
                            ]
                        );
                    }
                    $rec++;
                }
            }

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

    public function browse(Request $request, $tipe = 'gudangumum')
    {
        $q = $request->get('q', '');
        $cbg = session('cbg', '01');
        $ma = session('ma', 'TGZ');

        // Filter berdasarkan tipe
        $filterSpL = $tipe === 'dctanjungsari' ? ' AND ON_DC = "1" ' : '';

        if (!empty($q)) {
            $data = DB::select(
                "SELECT A.kd_brg, A.na_brg, A.ket_kem, A.ket_uk, A.retur, A.on_dc,
                        B.hb, B.hj, B.kdlaku, A.barcode
                 FROM " . $ma . ".brg A
                 INNER JOIN " . $ma . ".brgdt B ON A.kd_brg=B.kd_brg
                 WHERE B.cbg=? AND B.yer=year(now()) $filterSpL
                 AND (A.kd_brg LIKE ? OR A.na_brg LIKE ? OR A.barcode LIKE ?)
                 LIMIT 50",
                [$cbg, "%$q%", "%$q%", "%$q%"]
            );
        } else {
            $data = [];
        }

        return response()->json($data);
    }

    public function getDetail(Request $request, $tipe = 'gudangumum')
    {
        $kd_brg = $request->get('kd_brg');
        $barcode = $request->get('barcode');
        $chkBarcode = $request->get('chkBarcode', false);
        $cbg = session('cbg', '01');
        $ma = session('ma', 'TGZ');

        // Filter berdasarkan tipe
        $filterSpL = $tipe === 'dctanjungsari' ? ' AND A.ON_DC = "1" ' : '';

        if ($chkBarcode) {
            // Scan dengan barcode timbangan
            $barang = DB::select(
                "SELECT A.kd_brg, A.na_brg, A.ket_kem, A.ket_uk, A.retur, A.on_dc,
                        B.hb, B.hj, B.kdlaku, C.barcode,
                        left(trim(right(trim(?),Length(trim(?))-7)),5)/1000 as qtyscan
                 FROM " . $ma . ".brg A
                 INNER JOIN " . $ma . ".brgdt B ON A.kd_brg=B.kd_brg
                 INNER JOIN " . $ma . ".masks C ON A.kd_brg=C.kd_brg
                 WHERE B.cbg=? AND B.yer=year(now()) $filterSpL
                 AND A.kd_brg=concat(right(left(?,7),3),left(left(?,7),4))",
                [$barcode, $barcode, $cbg, $barcode, $barcode]
            );
        } else {
            // Scan biasa atau input manual
            $searchField = !empty($barcode) ? 'A.barcode' : 'A.kd_brg';
            $searchValue = !empty($barcode) ? $barcode : $kd_brg;

            $barang = DB::select(
                "SELECT A.kd_brg, A.na_brg, A.ket_kem, A.ket_uk, A.retur, A.on_dc,
                        B.hb, B.hj, B.kdlaku, A.barcode
                 FROM " . $ma . ".brg A
                 INNER JOIN " . $ma . ".brgdt B ON A.kd_brg=B.kd_brg
                 WHERE B.cbg=? AND B.yer=year(now()) $filterSpL
                 AND $searchField=?",
                [$cbg, $searchValue]
            );
        }

        if (!empty($barang)) {
            $item = $barang[0];

            // Tentukan tipe barang
            if ($item->retur == 'Y') {
                $item->jns = 'RETUR';
            } elseif ($item->retur == 'G') {
                $item->jns = 'TKRGLG';
            } else {
                if ($item->kdlaku == '5') {
                    $item->jns = 'RENOVASI';
                } else {
                    $item->jns = 'TDK RETUR';
                }
            }

            // Ambil SPL
            $splInfo = DB::select(
                "SELECT SP_L, SP_LF FROM " . $ma . ".brg WHERE kd_brg=?",
                [$item->kd_brg]
            );

            if (!empty($splInfo)) {
                if ($cbg == 'TMM') {
                    $item->spl = $splInfo[0]->SP_L;
                } elseif ($cbg == 'SOP') {
                    $item->spl = $splInfo[0]->SP_LF;
                } else {
                    $item->spl = '';
                }
            } else {
                $item->spl = '';
            }

            return response()->json([
                'success' => true,
                'exists' => true,
                'data' => $item
            ]);
        }

        return response()->json([
            'success' => false,
            'exists' => false,
            'message' => 'Barang tidak ditemukan'
        ]);
    }

    public function printPengembalianKeGudang(Request $request, $tipe = 'gudangumum')
    {
        $no_bukti = $request->no_bukti;
        $posted = $request->posted ?? 0;
        $cbg = session('cbg', '01');

        // Ambil nama toko
        $tokoInfo = DB::select(
            "SELECT na_toko FROM toko WHERE kode=?",
            [$cbg]
        );
        $toko = !empty($tokoInfo) ? $tokoInfo[0]->na_toko : '';

        // Pilih tabel berdasarkan status posted
        $AA = $posted == 0 ? 'stockb' : 'stockbz';
        $BB = $posted == 0 ? 'stockbd' : 'stockbzd';

        if ($tipe === 'dctanjungsari') {
            // Query untuk DC Tanjungsari
            $query = "SELECT ? as nmtoko, A.no_bukti, D.SUB,
                        if(D.ON_DC=1, coalesce((SELECT KODE_DC from sup WHERE KODES=D.supp limit 1), ''), 'L') as SPL,
                        A.KODES as SUPP, B.KD_BRG, CONCAT(C.KDLAKU,C.KLK) AS KD,
                        B.NA_BRG, D.KET_UK, B.KET_KEM, C.HJ, sum(B.qty) as qty, B.KET,
                        'G' as RTX,
                        (SELECT IF(RTX='Y','RETUR', IF(RTX='T','TIDAK BISA RETUR','TUKAR GULING'))) KETX
                      FROM $AA A, $BB B, brgdt C, brg D
                      WHERE B.no_bukti=A.no_bukti
                      AND C.KD_BRG=B.KD_BRG
                      AND D.KD_BRG=B.KD_BRG
                      AND C.yer=year(now())
                      AND A.NO_BUKTI=?
                      GROUP BY B.KD_BRG
                      ORDER BY RTX, D.SUPP, B.KD_BRG";
        } else {
            // Query untuk Gudang Umum
            $query = "SELECT ? as nmtoko, A.no_bukti, D.SUB,
                        if(D.ON_DC=1, coalesce((SELECT KODE_DC from sup WHERE KODES=D.supp limit 1), ''), 'L') as SPL,
                        D.SUPP, D.KD_BRG, CONCAT(C.KDLAKU,C.KLK) AS KD,
                        D.NA_BRG, D.KET_UK, D.KET_KEM, C.HJ, sum(B.qty) as qty, B.KET,
                        IF(D.RETUR NOT IN ('Y','T','G'),'Y',D.RETUR) RTX,
                        (SELECT IF(RTX='Y','RETUR', IF(RTX='T','TIDAK BISA RETUR','TUKAR GULING'))) KETX
                      FROM $AA A, $BB B, brgdt C, brg D
                      WHERE B.no_bukti=A.no_bukti
                      AND C.KD_BRG=B.KD_BRG
                      AND D.KD_BRG=B.KD_BRG
                      AND C.yer=year(now())
                      AND A.NO_BUKTI=?
                      GROUP BY B.KD_BRG
                      ORDER BY RTX, D.SUPP, B.KD_BRG";
        }

        $data = DB::select($query, [$toko, $no_bukti]);

        return response()->json(['data' => $data]);
    }

    public function destroy($tipe, $no_bukti)
    {
        try {
            DB::beginTransaction();

            // Hapus detail
            DB::statement("DELETE FROM stockbd WHERE no_bukti=?", [$no_bukti]);

            // Hapus header
            DB::statement("DELETE FROM stockb WHERE no_bukti=?", [$no_bukti]);

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

    public function updatePrint(Request $request, $tipe = 'gudangumum')
    {
        try {
            $no_bukti = $request->no_bukti;
            $table = $request->table; // 'stockb' atau 'stockbz'
            $printValue = $request->print; // 0 atau 1

            DB::statement(
                "UPDATE $table SET print=? WHERE no_bukti=?",
                [$printValue, $no_bukti]
            );

            return response()->json([
                'success' => true,
                'message' => 'Print flag updated'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
