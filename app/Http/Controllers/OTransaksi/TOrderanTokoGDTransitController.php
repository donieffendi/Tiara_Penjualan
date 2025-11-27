<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TOrderanTokoGDTransitController extends Controller
{
    public function index()
    {
        try {
            // Handle periode (could be array or string)
            $periodeSession = session('periode');
            if (is_array($periodeSession)) {
                $periode = str_pad($periodeSession['bulan'], 2, '0', STR_PAD_LEFT) . '/' . $periodeSession['tahun'];
            } else {
                $periode = $periodeSession ?? date('m/Y');
            }

            // Get flag/cbg from session or user
            $flag = session('flag') ?? Auth::user()->CBG ?? '01';

            Log::info('TOrderanTokoGDTransit index accessed', [
                'periode' => $periode,
                'flag' => $flag,
                'user' => Auth::user()->username ?? 'unknown'
            ]);

            return view('otranskasi_Orderan_Toko_GD_Transit.index', compact('periode', 'flag'));
        } catch (\Exception $e) {
            Log::error('TOrderanTokoGDTransit index error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function getOrderanTokoGDTransit(Request $request)
    {
        try {
            // Handle periode (could be array or string)
            $periodeSession = session('periode');
            if (is_array($periodeSession)) {
                $periode = str_pad($periodeSession['bulan'], 2, '0', STR_PAD_LEFT) . '/' . $periodeSession['tahun'];
            } else {
                $periode = $periodeSession ?? date('m/Y');
            }

            // Get flag/cbg from session or user
            $cbg = session('flag') ?? Auth::user()->CBG ?? '01';

            Log::info('TOrderanTokoGDTransit getData started', [
                'periode' => $periode,
                'cbg' => $cbg,
                'user' => Auth::user()->username ?? 'unknown'
            ]);

            $query = DB::select(
                "SELECT no_bukti, tgl, cbg, per, SUM(qty) as total_qty
                 FROM tpo
                 WHERE per=? AND flag='OT' AND cbg=?
                 GROUP BY no_bukti, tgl, cbg, per
                 ORDER BY no_bukti DESC",
                [$periode, $cbg]
            );

            Log::info('TOrderanTokoGDTransit getData query executed', [
                'periode' => $periode,
                'cbg' => $cbg,
                'row_count' => count($query)
            ]);

            return Datatables::of(collect($query))
                ->addIndexColumn()
                ->editColumn('tgl', function ($row) {
                    return $row->tgl ? date('d/m/Y', strtotime($row->tgl)) : '';
                })
                ->editColumn('total_qty', function ($row) {
                    return number_format($row->total_qty, 0, ',', '.');
                })
                ->addColumn('action', function ($row) {
                    $btnEdit = '<button onclick="editData(\'' . $row->no_bukti . '\')" class="btn btn-sm btn-primary" title="Edit"><i class="fas fa-edit"></i></button>';
                    $btnPrint = '<button onclick="printData(\'' . $row->no_bukti . '\')" class="btn btn-sm btn-info ml-1" title="Print"><i class="fas fa-print"></i></button>';
                    $btnDelete = '<button onclick="deleteData(\'' . $row->no_bukti . '\')" class="btn btn-sm btn-danger ml-1" title="Delete"><i class="fas fa-trash"></i></button>';
                    return $btnEdit . ' ' . $btnPrint . ' ' . $btnDelete;
                })
                ->rawColumns(['action'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('TOrderanTokoGDTransit getData error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit(Request $request)
    {
        try {
            $no_bukti = $request->get('no_bukti', '+');
            $status = $request->get('status', 'simpan');

            // Handle periode (could be array or string)
            $periodeSession = session('periode');
            if (is_array($periodeSession)) {
                $periode = str_pad($periodeSession['bulan'], 2, '0', STR_PAD_LEFT) . '/' . $periodeSession['tahun'];
            } else {
                $periode = $periodeSession ?? date('m/Y');
            }

            // Get flag/cbg from session or user
            $cbg = session('flag') ?? Auth::user()->CBG ?? '01';
            $ma = session('ma', 'TGZ');
            $oo = session('ma', 'TGZ');
            // $oo = session('oo', 'DCSBYO1');

            Log::info('TOrderanTokoGDTransit edit accessed', [
                'no_bukti' => $no_bukti,
                'status' => $status,
                'periode' => $periode,
                'cbg' => $cbg
            ]);

            // Cek periode posted
            $periodeCheck = DB::select(
                "SELECT posted FROM perid WHERE kd_peri=?",
                [$periode]
            );

            if (!empty($periodeCheck) && $periodeCheck[0]->posted == 1) {
                return view('otranskasi_Orderan_Toko_GD_Transit.edit', [
                    'error' => 'Closed Period',
                    'periode' => $periode,
                    'cbg' => $cbg
                ]);
            }

            $data = [
                'no_bukti' => '+',
                'status' => $status,
                'header' => (object)[
                    'no_bukti' => '+',
                    'tgl' => date('Y-m-d'),
                    'total_qty' => 0,
                    'total_harga' => 0,
                    'total_nett' => 0
                ],
                'detail' => [],
                'periode' => $periode,
                'cbg' => $cbg,
                'ma' => $ma,
                'oo' => $oo,
                'error' => null
            ];

            if ($status == 'edit' && $no_bukti && $no_bukti != '+') {
                $header = DB::select(
                    "SELECT no_bukti, tgl, SUM(qty) as total_qty, SUM(qty*harga) as total_harga
                     FROM tpo
                     WHERE no_bukti=? AND flag='OT'
                     GROUP BY no_bukti, tgl",
                    [$no_bukti]
                );

                if (!empty($header)) {
                    $detail = DB::select(
                        "SELECT no_id, rec, kd_brg, na_brg, qty, harga, (qty*harga) as total,
                                ket_kem, kdlaku, sub, kdbar, ket_uk, ket as notes
                         FROM tpo
                         WHERE no_bukti=? AND flag='OT'
                         ORDER BY rec",
                        [$no_bukti]
                    );

                    foreach ($detail as $item) {
                        // Get stok dari DC
                        $stok = DB::select(
                            "SELECT COALESCE(stok_dc, 0) as stok
                             FROM brg_dc_ts
                             WHERE kd_brg=?",
                            [$item->kd_brg]
                        );
                        $item->stok = !empty($stok) ? $stok[0]->stok : 0;
                    }

                    $data['header'] = $header[0];
                    $data['header']->total_nett = $header[0]->total_harga;
                    $data['detail'] = $detail;
                    $data['no_bukti'] = $no_bukti;
                } else {
                    $data['error'] = 'Data tidak ditemukan';
                }
            }

            return view('otranskasi_Orderan_Toko_GD_Transit.edit', $data);
        } catch (\Exception $e) {
            return view('otranskasi_Orderan_Toko_GD_Transit.edit', [
                'no_bukti' => '+',
                'status' => 'simpan',
                'header' => (object)[
                    'no_bukti' => '+',
                    'tgl' => date('Y-m-d'),
                    'total_qty' => 0,
                    'total_harga' => 0,
                    'total_nett' => 0
                ],
                'detail' => [],
                'periode' => $periode ?? date('m/Y'),
                'cbg' => $cbg ?? '01',
                'ma' => $ma ?? 'TGZ',
                'oo' => $oo ?? 'DCSBYO1',
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'tgl' => 'required|date',
                'details' => 'required|array|min:1'
            ]);

            Log::info('TOrderanTokoGDTransit store started', [
                'no_bukti' => $request->no_bukti,
                'status' => $request->status,
                'user' => Auth::user()->username ?? 'unknown'
            ]);

            DB::beginTransaction();

            $no_bukti = trim($request->no_bukti);
            $status = $request->status;

            // Handle periode (could be array or string)
            $periodeSession = session('periode');
            if (is_array($periodeSession)) {
                $periode = str_pad($periodeSession['bulan'], 2, '0', STR_PAD_LEFT) . '/' . $periodeSession['tahun'];
            } else {
                $periode = $periodeSession ?? date('m/Y');
            }

            // Get flag/cbg from session or user
            $cbg = session('flag') ?? Auth::user()->CBG ?? '01';
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
            if ($status == 'simpan') {
                if ($no_bukti == '+') {
                    // Call stored procedure untuk generate nomor bukti
                    $result = DB::select(
                        "CALL NO_TRANSX(?, ?, ?, ?, ?)",
                        ['ORDERTKC', 'TfrOrdGdTransitn', $cbg, 'v1.0', date('d')]
                    );

                    if (empty($result)) {
                        throw new \Exception('Create NO.BUKTI bermasalah! x539');
                    }

                    $no_bukti = $result[0]->BUKTIX;
                }
            }

            // Hapus detail lama jika edit
            if ($status == 'edit') {
                $existing = DB::select(
                    "SELECT no_id FROM tpo WHERE flag='OT' AND no_bukti=?",
                    [$no_bukti]
                );

                $existingIds = array_column($existing, 'no_id');
                $keepIds = array_filter(array_column($request->details, 'no_id'));

                foreach ($existingIds as $oldId) {
                    if (!in_array($oldId, $keepIds)) {
                        DB::statement("DELETE FROM tpo WHERE no_id=?", [$oldId]);
                    }
                }
            }

            // Insert/Update detail
            $rec = 1;
            foreach ($request->details as $detail) {
                if (!empty($detail['kd_brg'])) {
                    if (isset($detail['no_id']) && $detail['no_id'] > 0) {
                        // Update existing
                        DB::statement(
                            "UPDATE tpo
                             SET rec=?, tgl=?, kd_brg=?, na_brg=?, qty=?, harga=?,
                                 kdlaku=?, sub=?, kdbar=?, tg_smp=NOW(), ket_kem=?,
                                 ket_uk=?, ket=?, usrnm=?
                             WHERE no_id=?",
                            [
                                $rec,
                                $request->tgl,
                                trim($detail['kd_brg']),
                                trim($detail['na_brg']),
                                floatval($detail['qty'] ?? 0),
                                floatval($detail['harga'] ?? 0),
                                trim($detail['kdlaku'] ?? ''),
                                trim($detail['sub'] ?? ''),
                                trim($detail['kdbar'] ?? ''),
                                trim($detail['ket_kem'] ?? ''),
                                trim($detail['ket_uk'] ?? ''),
                                trim($detail['notes'] ?? ''),
                                $username,
                                $detail['no_id']
                            ]
                        );
                    } else {
                        // Insert new
                        DB::statement(
                            "INSERT INTO tpo (no_bukti, rec, per, flag, kd_brg, na_brg, qty, harga,
                                              tgl, tg_smp, kdlaku, sub, kdbar, ket_kem, ket_uk, ket, usrnm, cbg)
                             VALUES (?, ?, ?, 'OT', ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?)",
                            [
                                $no_bukti,
                                $rec,
                                $periode,
                                trim($detail['kd_brg']),
                                trim($detail['na_brg']),
                                floatval($detail['qty'] ?? 0),
                                floatval($detail['harga'] ?? 0),
                                $request->tgl,
                                trim($detail['kdlaku'] ?? ''),
                                trim($detail['sub'] ?? ''),
                                trim($detail['kdbar'] ?? ''),
                                trim($detail['ket_kem'] ?? ''),
                                trim($detail['ket_uk'] ?? ''),
                                trim($detail['notes'] ?? ''),
                                $username,
                                $cbg
                            ]
                        );

                        // Update brgdt set TK flag
                        DB::statement(
                            "UPDATE brgdt SET TK='*', UP=1, TGL_TK=NOW()
                             WHERE kd_brg=? AND cbg=?",
                            [trim($detail['kd_brg']), $cbg]
                        );
                    }
                    $rec++;
                }
            }

            DB::commit();

            Log::info('TOrderanTokoGDTransit store completed successfully', [
                'no_bukti' => $no_bukti,
                'status' => $status
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Save Data Success'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('TOrderanTokoGDTransit store validation failed:', [
                'errors' => $e->validator->errors()->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . implode(', ', $e->validator->errors()->all())
            ], 422);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('TOrderanTokoGDTransit store error:', [
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
        DB::beginTransaction();

        try {
            Log::info('TOrderanTokoGDTransit destroy started', [
                'no_bukti' => $no_bukti,
                'user' => Auth::user()->username ?? 'unknown'
            ]);

            DB::statement("DELETE FROM tpo WHERE no_bukti=? AND flag='OT'", [$no_bukti]);

            DB::commit();

            Log::info('TOrderanTokoGDTransit destroy completed successfully', [
                'no_bukti' => $no_bukti
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('TOrderanTokoGDTransit destroy error:', [
                'no_bukti' => $no_bukti,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function browse(Request $request)
    {
        try {
            $q = $request->get('q', '');

            // Get session variables
            $cbg = session('flag') ?? Auth::user()->CBG ?? '01';
            $ma = session('ma', 'TGZ');
            $oo = session('oo', 'DCSBYO1');
            $sub1 = $request->get('sub1', '');
            $sub2 = $request->get('sub2', '');

            Log::info('TOrderanTokoGDTransit browse started', [
                'query' => $q,
                'cbg' => $cbg,
                'ma' => $ma,
                'oo' => $oo
            ]);

            if (!empty($q)) {
                $data = DB::select(
                    "SELECT A.kd_brg, CONCAT(A.na_brg, ' ', A.ket_uk) as na_brg,
                            A.ket_kem, A.sub, A.kdbar, A.ket_uk, B.kdlaku, B.hb as harga,
                            COALESCE(C.stok_dc, 0) as stok
                     FROM " . $ma . ".brg A
                     INNER JOIN " . $ma . ".brgdt B ON A.kd_brg=B.kd_brg
                     LEFT JOIN brg_dc_ts C ON B.kd_brg=C.kd_brg
                     WHERE B.yer=YEAR(NOW()) AND B.cbg=?
                       AND (A.kd_brg LIKE ? OR A.na_brg LIKE ?)
                       AND COALESCE(C.stok_dc, 0) > 0
                     LIMIT 50",
                    [$ma, "%$q%", "%$q%"]
                );

                Log::info('TOrderanTokoGDTransit browse query executed', [
                    'query' => $q,
                    'result_count' => count($data)
                ]);
            } else {
                $data = [];
            }

            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('TOrderanTokoGDTransit browse error:', [
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
        try {
            $kd_brg = $request->get('kd_brg');

            // Get session variables
            $cbg = session('flag') ?? Auth::user()->CBG ?? '01';
            $ma = session('ma', 'TGZ');
            $oo = session('oo', 'DCSBYO1');

            Log::info('TOrderanTokoGDTransit getDetail started', [
                'kd_brg' => $kd_brg,
                'cbg' => $cbg,
                'ma' => $ma,
                'oo' => $oo
            ]);

            $barang = DB::select(
                "SELECT A.kd_brg, CONCAT(A.na_brg, ' ', A.ket_uk) as na_brg,
                        A.ket_kem, A.sub, A.kdbar, A.ket_uk, A.on_dc, A.type as golongan,
                        B.kdlaku, B.hb as harga, B.ak00 as toko, B.gak00 as gudang,
                        COALESCE(C.stok_dc, 0) as stok,
                        IF(COALESCE(C.stok_dc, 0) > 0, 'YES', 'NO') as ada
                 FROM " . $ma . ".brg A
                 INNER JOIN " . $ma . ".brgdt B ON A.kd_brg=B.kd_brg
                 LEFT JOIN brg_dc_ts C ON B.kd_brg=C.kd_brg
                 WHERE B.yer=YEAR(NOW()) AND A.kd_brg=?",
                [$kd_brg]
            );

            if (!empty($barang)) {
                $row = $barang[0];

                Log::info('TOrderanTokoGDTransit getDetail found item', [
                    'kd_brg' => $kd_brg,
                    'stok' => $row->stok,
                    'ada' => $row->ada
                ]);

                if ($row->ada != 'YES') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Stok di DC Tunjungsari Kosong!'
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'exists' => true,
                    'data' => $row
                ]);
            }

            Log::warning('TOrderanTokoGDTransit getDetail item not found', [
                'kd_brg' => $kd_brg
            ]);

            return response()->json([
                'success' => false,
                'exists' => false,
                'message' => 'Tidak ada barang'
            ]);
        } catch (\Exception $e) {
            Log::error('TOrderanTokoGDTransit getDetail error:', [
                'kd_brg' => $request->get('kd_brg'),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function ambilData(Request $request)
    {
        try {
            $sub1 = $request->get('sub1', '');
            $sub2 = $request->get('sub2', '');
            $cbg = session('cbg', '01');
            $ma = session('ma', 'TGZ');
            $oo = session('oo', 'DCSBYO1');

            if (empty($sub1) || empty($sub2)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sub harus diisi!'
                ], 400);
            }

            $data = DB::select(
                "SELECT A.kd_brg, A.ket_uk, A.ket_kem, B.hb, A.na_brg, A.sub, A.kdbar,
                        B.hb as harga,
                        IF(GREATEST(CEILING(1.5*B.lph), C.dtr) < 5, 5, GREATEST(CEILING(1.5*B.lph), C.dtr)) as srmax,
                        B.kdlaku, B.ak00 as toko, B.gak00 as gudang, B.ak00 as stokx,
                        COALESCE(C.stok_dc, 0) as stok_dc, A.type as golongan, B.lph,
                        RIGHT(TRIM(A.ket_kem), LENGTH(TRIM(A.ket_kem)) - LOCATE('/', TRIM(A.ket_kem))) as kem
                 FROM " . $ma . ".brg A
                 INNER JOIN " . $ma . ".brgdt B ON A.kd_brg=B.kd_brg
                 LEFT JOIN brg_dc_ts C ON B.kd_brg=C.kd_brg
                 WHERE B.yer=YEAR(NOW()) AND A.sub <> '011'
                   AND (B.tk='' OR B.up=0)
                   AND B.ak00 <= IF(GREATEST(CEILING(0.5*B.lph), CEILING(0.5*C.dtr)) < 4, 4,
                                    GREATEST(CEILING(0.5*B.lph), CEILING(0.5*C.dtr)))
                   AND (B.kdlaku='0' OR B.kdlaku='1')
                   AND B.gak00 >= B.ak00
                   AND A.sub BETWEEN ? AND ?
                   AND B.gak00 > 0
                 ORDER BY A.type, A.kd_brg",
                [$sub1, $sub2]
            );

            if (empty($data)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak Ada Data'
                ], 404);
            }

            $result = [];
            foreach ($data as $item) {
                $jo = $item->srmax - $item->stokx;

                if ($jo > 0) {
                    $result[] = [
                        'kd_brg' => $item->kd_brg,
                        'na_brg' => $item->na_brg,
                        'ket_uk' => $item->ket_uk,
                        'ket_kem' => $item->ket_kem,
                        'kdlaku' => $item->kdlaku,
                        'sub' => $item->sub,
                        'kdbar' => $item->kdbar,
                        'qty' => $jo,
                        'harga' => $item->harga,
                        'total' => $jo * $item->harga,
                        'stok' => $item->stok_dc
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function printOrderanTokoGDTransit(Request $request)
    {
        try {
            $no_bukti = $request->no_bukti;

            // Get session variables
            $cbg = session('flag') ?? Auth::user()->CBG ?? '01';
            $ma = session('ma', 'TGZ');

            Log::info('TOrderanTokoGDTransit print started', [
                'no_bukti' => $no_bukti,
                'cbg' => $cbg,
                'ma' => $ma,
                'user' => Auth::user()->username ?? 'unknown'
            ]);

            $data = DB::select(
                "SELECT IF(brg.sp_l='D', 'D', 'L') as spl, brg.sub,
                        tpo.no_bukti, tpo.tgl, brg.barcode, brg.item_uni, brg.ket_uk,
                        tpo.kd_brg, tpo.na_brg, brg.sub, brg.kdbar, brgdt.kdlaku,
                        brgdt.srmax, brgdt.srmin, brg.ket_kem,
                        brgdt.ak00 as stockr_tk, brgdt.gak00 as stockr,
                        tpo.qty, brgdt.dtr, brg.mo, brg.type,
                        brgdt.srmax as smax_tk, CONCAT(brgdt.kdlaku, brgdt.klk) as kd
                 FROM tpo
                 INNER JOIN " . $ma . ".brg ON brg.kd_brg=tpo.kd_brg
                 INNER JOIN " . $ma . ".brgdt ON brgdt.kd_brg=tpo.kd_brg
                 WHERE tpo.no_bukti=? AND tpo.flag='OT'
                 ORDER BY brg.sub, brg.type ASC, tpo.rec ASC",
                [$no_bukti]
            );

            Log::info('TOrderanTokoGDTransit print completed', [
                'no_bukti' => $no_bukti,
                'row_count' => count($data)
            ]);

            return response()->json(['data' => $data]);
        } catch (\Exception $e) {
            Log::error('TOrderanTokoGDTransit print error:', [
                'no_bukti' => $request->no_bukti,
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
