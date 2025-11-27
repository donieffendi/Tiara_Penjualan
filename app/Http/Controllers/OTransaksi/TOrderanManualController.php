<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TOrderanManualController extends Controller
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

            Log::info('TOrderanManual index accessed', [
                'periode' => $periode,
                'flag' => $flag,
                'user' => Auth::user()->username ?? 'unknown'
            ]);

            return view('otranskasi_Orderan_Manual.index', compact('periode', 'flag'));
        } catch (\Exception $e) {
            Log::error('TOrderanManual index error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function getOrderanManual(Request $request)
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

            Log::info('TOrderanManual getData started', [
                'periode' => $periode,
                'flag' => $flag,
                'user' => Auth::user()->username ?? 'unknown'
            ]);

            $query = DB::select(
                "SELECT no_bukti, tgl, ket, SUM(qty) as total_qty
                 FROM tpo
                 WHERE per=? AND flag='TM'
                 GROUP BY no_bukti, tgl, ket
                 ORDER BY no_bukti",
                [$periode]
            );

            Log::info('TOrderanManual getData query executed', [
                'periode' => $periode,
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
                    return $btnEdit . ' ' . $btnPrint;
                })
                ->rawColumns(['action'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('TOrderanManual getData error:', [
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

            Log::info('TOrderanManual edit accessed', [
                'no_bukti' => $no_bukti,
                'status' => $status,
                'periode' => $periode,
                'cbg' => $cbg
            ]);

            $data = [
                'no_bukti' => '+',
                'status' => $status,
                'header' => (object)[
                    'no_bukti' => '+',
                    'tgl' => date('Y-m-d'),
                    'ket' => '',
                    'total_qty' => 0
                ],
                'detail' => [],
                'periode' => $periode,
                'cbg' => $cbg,
                'error' => null
            ];

            if ($status == 'edit' && $no_bukti && $no_bukti != '+') {
                $header = DB::select(
                    "SELECT no_bukti, tgl, ket, SUM(qty) as total_qty
                     FROM tpo
                     WHERE no_bukti=? AND flag='TM'
                     GROUP BY no_bukti, tgl, ket
                     ORDER BY no_bukti",
                    [$no_bukti]
                );

                if (!empty($header)) {
                    $detail = DB::select(
                        "SELECT no_id, rec, kd_brg, na_brg, qty, ket_kem, kdlaku, sub, kdbar
                         FROM tpo
                         WHERE no_bukti=? AND flag='TM'
                         ORDER BY rec",
                        [$no_bukti]
                    );

                    foreach ($detail as $item) {
                        $stok = DB::select(
                            "SELECT gak00 as stok FROM brgdt WHERE kd_brg=? AND cbg=?",
                            [$item->kd_brg, $cbg]
                        );
                        $item->stok = !empty($stok) ? $stok[0]->stok : 0;
                    }

                    $data['header'] = $header[0];
                    $data['detail'] = $detail;
                    $data['no_bukti'] = $no_bukti;
                } else {
                    $data['error'] = 'Data tidak ditemukan';
                }
            }

            return view('otranskasi_Orderan_Manual.edit', $data);
        } catch (\Exception $e) {
            return view('otranskasi_Orderan_Manual.edit', [
                'no_bukti' => '+',
                'status' => 'simpan',
                'header' => (object)[
                    'no_bukti' => '+',
                    'tgl' => date('Y-m-d'),
                    'ket' => '',
                    'total_qty' => 0
                ],
                'detail' => [],
                'periode' => $periode ?? date('m/Y'),
                'cbg' => $cbg ?? '01',
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

            Log::info('TOrderanManual store started', [
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

            if ($status == 'simpan') {
                if ($no_bukti == '+') {
                    $toko = DB::select("SELECT type FROM toko WHERE kode=?", [$cbg]);
                    $kode2 = $toko[0]->type ?? '';
                    $kode = 'TM' . substr($periode, -2) . substr($periode, 0, 2);

                    $notrans = DB::select(
                        "SELECT NOM" . $periode_month . " as no_bukti
                         FROM notrans
                         WHERE trans='ORDERTKC' AND per=?",
                        [$periode_year]
                    );

                    $r1 = ($notrans[0]->no_bukti ?? 0) + 1;

                    DB::statement(
                        "UPDATE notrans
                         SET NOM" . $periode_month . "=?
                         WHERE trans='ORDERTKC' AND per=?",
                        [$r1, $periode_year]
                    );

                    $bkt1 = str_pad($r1, 4, '0', STR_PAD_LEFT);
                    $no_bukti = $kode . '-' . $bkt1 . $kode2;
                }
            }

            if ($status == 'edit') {
                $existing = DB::select(
                    "SELECT no_id FROM tpo WHERE flag='TM' AND no_bukti=?",
                    [$no_bukti]
                );

                foreach ($existing as $row) {
                    $found = false;
                    foreach ($request->details as $detail) {
                        if (isset($detail['no_id']) && $detail['no_id'] == $row->no_id) {
                            DB::statement(
                                "UPDATE tpo
                                 SET rec=?, tgl=?, kd_brg=?, na_brg=?, qty=?, ket=?,
                                     kdlaku=?, sub=?, kdbar=?, tg_smp=NOW(), ket_kem=?, usrnm=?
                                 WHERE no_id=?",
                                [
                                    intval($detail['rec'] ?? 1),
                                    $request->tgl,
                                    trim($detail['kd_brg'] ?? ''),
                                    trim($detail['na_brg'] ?? ''),
                                    floatval($detail['qty'] ?? 0),
                                    trim($request->notes ?? ''),
                                    trim($detail['kdlaku'] ?? ''),
                                    trim($detail['sub'] ?? ''),
                                    trim($detail['kdbar'] ?? ''),
                                    trim($detail['ket_kem'] ?? ''),
                                    $username,
                                    $row->no_id
                                ]
                            );
                            $found = true;
                            break;
                        }
                    }

                    if (!$found) {
                        DB::statement("DELETE FROM tpo WHERE no_id=?", [$row->no_id]);
                    }
                }
            }

            $rec = 1;
            foreach ($request->details as $detail) {
                if (!empty($detail['kd_brg'])) {
                    if (!isset($detail['no_id']) || $detail['no_id'] == 0) {
                        DB::statement(
                            "INSERT INTO tpo (no_bukti, rec, per, flag, kd_brg, na_brg, qty, tgl, ket,
                                              tg_smp, kdlaku, sub, kdbar, ket_kem, usrnm, cbg)
                             VALUES (?, ?, ?, 'TM', ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?)",
                            [
                                $no_bukti,
                                $rec,
                                $periode,
                                trim($detail['kd_brg'] ?? ''),
                                trim($detail['na_brg'] ?? ''),
                                floatval($detail['qty'] ?? 0),
                                $request->tgl,
                                trim($request->notes ?? ''),
                                trim($detail['kdlaku'] ?? ''),
                                trim($detail['sub'] ?? ''),
                                trim($detail['kdbar'] ?? ''),
                                trim($detail['ket_kem'] ?? ''),
                                $username,
                                $cbg
                            ]
                        );
                    }
                    $rec++;
                }
            }

            DB::commit();

            Log::info('TOrderanManual store completed successfully', [
                'no_bukti' => $no_bukti,
                'status' => $status
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Save Data Success'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('TOrderanManual store validation failed:', [
                'errors' => $e->validator->errors()->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . implode(', ', $e->validator->errors()->all())
            ], 422);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('TOrderanManual store error:', [
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
            Log::info('TOrderanManual destroy started', [
                'no_bukti' => $no_bukti,
                'user' => Auth::user()->username ?? 'unknown'
            ]);

            DB::statement("DELETE FROM tpo WHERE no_bukti=? AND flag='TM'", [$no_bukti]);

            DB::commit();

            Log::info('TOrderanManual destroy completed successfully', [
                'no_bukti' => $no_bukti
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('TOrderanManual destroy error:', [
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

            // Get flag/cbg from session or user
            $cbg = session('flag') ?? Auth::user()->CBG ?? '01';

            Log::info('TOrderanManual browse started', [
                'query' => $q,
                'cbg' => $cbg
            ]);

            if (!empty($q)) {
                $data = DB::select(
                    "SELECT brg.kd_brg, CONCAT(brg.na_brg, ' ', brg.ket_uk) as na_brg,
                            brg.ket_kem, brg.sub, brg.kdbar, brgdt.kdlaku, brgdt.tk, brgdt.gak00 as stok
                     FROM brg, brgdt
                     WHERE brgdt.kd_brg=brg.kd_brg AND brgdt.cbg=?
                       AND (brg.kd_brg LIKE ? OR brg.na_brg LIKE ?)
                     LIMIT 50",
                    [$cbg, "%$q%", "%$q%"]
                );

                Log::info('TOrderanManual browse query executed', [
                    'query' => $q,
                    'result_count' => count($data)
                ]);
            } else {
                $data = [];
            }

            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('TOrderanManual browse error:', [
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

            // Get flag/cbg from session or user
            $cbg = session('flag') ?? Auth::user()->CBG ?? '01';

            Log::info('TOrderanManual getDetail started', [
                'kd_brg' => $kd_brg,
                'cbg' => $cbg
            ]);

            $barang = DB::select(
                "SELECT brg.kd_brg, CONCAT(brg.na_brg, ' ', brg.ket_uk) as na_brg,
                        brg.ket_kem, brg.sub, brg.kdbar, brgdt.kdlaku, brgdt.tk, brgdt.gak00 as stok
                 FROM brg, brgdt
                 WHERE brgdt.kd_brg=brg.kd_brg AND brgdt.cbg=? AND brg.kd_brg=?",
                [$cbg, $kd_brg]
            );

            if (!empty($barang)) {
                $row = $barang[0];

                Log::info('TOrderanManual getDetail found item', [
                    'kd_brg' => $kd_brg,
                    'kdlaku' => $row->kdlaku,
                    'stok' => $row->stok
                ]);

                if ($row->kdlaku == '4') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Barang Kode 4 tidak bisa dipesan ke gudang!'
                    ]);
                }

                if ($row->stok <= 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Stok Gudang Masih Belum Tersedia!'
                    ]);
                }

                if (!empty($row->tk)) {
                    return response()->json([
                        'success' => true,
                        'exists' => true,
                        'data' => $row,
                        'warning' => 'Barang ini sudah dalam antrian Order Otomatis!'
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'exists' => true,
                    'data' => $row
                ]);
            }

            Log::warning('TOrderanManual getDetail item not found', [
                'kd_brg' => $kd_brg
            ]);

            return response()->json([
                'success' => false,
                'exists' => false,
                'message' => 'Barang tidak ditemukan'
            ]);
        } catch (\Exception $e) {
            Log::error('TOrderanManual getDetail error:', [
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

    public function printOrderanManual(Request $request)
    {
        try {
            $no_bukti = $request->no_bukti;

            // Get flag/cbg from session or user
            $cbg = session('flag') ?? Auth::user()->CBG ?? '01';

            Log::info('TOrderanManual print started', [
                'no_bukti' => $no_bukti,
                'cbg' => $cbg,
                'user' => Auth::user()->username ?? 'unknown'
            ]);

            $data = DB::select(
                "SELECT TIME(tpo.tg_smp) as timo, tpo.tgl, tpo.no_bukti, tpo.sub, tpo.kdbar,
                        brg.na_brg, tpo.ket_kem, brg.ket_uk, brgdt.gak00 as stockr, brgdt.ak00 as stockr_tk,
                        CONCAT(brgdt.kdlaku, brgdt.klk) as kd, brgdt.srmax as smax_tk, brgdt.dtr, tpo.qty, tpo.ket
                 FROM tpo, brg, brgdt
                 WHERE tpo.kd_brg=brg.kd_brg AND tpo.kd_brg=brgdt.kd_brg
                   AND brgdt.cbg=? AND tpo.no_bukti=? AND tpo.flag='TM'
                 ORDER BY tpo.no_bukti",
                [$cbg, $no_bukti]
            );

            DB::statement(
                "UPDATE tpo SET prnt=1 WHERE no_bukti=? AND flag='TM'",
                [$no_bukti]
            );

            Log::info('TOrderanManual print completed', [
                'no_bukti' => $no_bukti,
                'row_count' => count($data)
            ]);

            return response()->json(['data' => $data]);
        } catch (\Exception $e) {
            Log::error('TOrderanManual print error:', [
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
