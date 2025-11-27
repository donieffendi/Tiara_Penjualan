<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TKoreksiTokoManualController extends Controller
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

            Log::info('TKoreksiTokoManual index accessed', [
                'periode' => $periode,
                'flag' => $flag,
                'user' => Auth::user()->username ?? 'unknown'
            ]);

            return view('otranskasi_koreksi_toko_manual.index', compact('periode', 'flag'));
        } catch (\Exception $e) {
            Log::error('TKoreksiTokoManual index error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function getKoreksiTokoManual(Request $request)
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

            Log::info('TKoreksiTokoManual getData started', [
                'periode' => $periode,
                'cbg' => $cbg,
                'user' => Auth::user()->username ?? 'unknown'
            ]);

            // Query union dari stockb dan stockbz (data yang sudah diposting)
            $query = DB::table('stockb')
                ->select('NO_BUKTI', 'TGL', 'TOTAL_QTY', 'NOTES', 'TYPE', 'BKTK', 'POSTED')
                ->where('per', $periode)
                ->where('flag', 'MT')
                ->where('cbg', $cbg)
                ->unionAll(
                    DB::table('stockbz')
                        ->select('NO_BUKTI', 'TGL', 'TOTAL_QTY', 'NOTES', 'TYPE', 'BKTK', 'POSTED')
                        ->where('per', $periode)
                        ->where('flag', 'MT')
                        ->where('cbg', $cbg)
                )
                ->orderBy('NO_BUKTI', 'DESC')
                ->get();

            Log::info('TKoreksiTokoManual getData query executed', [
                'periode' => $periode,
                'cbg' => $cbg,
                'row_count' => count($query)
            ]);

            return Datatables::of($query)
                ->addIndexColumn()
                ->editColumn('tgl', function ($row) {
                    return $row->TGL ? date('d/m/Y', strtotime($row->TGL)) : '';
                })
                ->editColumn('total_qty', function ($row) {
                    return number_format($row->TOTAL_QTY, 2, ',', '.');
                })
                ->editColumn('posted', function ($row) {
                    return $row->POSTED == 1
                        ? '<span class="badge badge-success">Posted</span>'
                        : '<span class="badge badge-warning">Open</span>';
                })
                ->addColumn('action', function ($row) {
                    $btnEdit = $row->POSTED == 0
                        ? '<button onclick="editData(\'' . $row->NO_BUKTI . '\')" class="btn btn-sm btn-primary" title="Edit"><i class="fas fa-edit"></i></button>'
                        : '<button class="btn btn-sm btn-secondary" disabled title="Sudah Posted"><i class="fas fa-lock"></i></button>';
                    $btnPrint = '<button onclick="printData(\'' . $row->NO_BUKTI . '\')" class="btn btn-sm btn-info ml-1" title="Print"><i class="fas fa-print"></i></button>';
                    return $btnEdit . ' ' . $btnPrint;
                })
                ->rawColumns(['action', 'posted'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('TKoreksiTokoManual getData error:', [
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

            Log::info('TKoreksiTokoManual edit accessed', [
                'no_bukti' => $no_bukti,
                'status' => $status,
                'periode' => $periode,
                'cbg' => $cbg
            ]);

            // Cek periode posted
            $periodeCheck = DB::table('perid')
                ->select('posted')
                ->where('kd_peri', $periode)
                ->first();

            if ($periodeCheck && $periodeCheck->posted == 1) {
                return view('otranskasi_koreksi_toko_manual.edit', [
                    'error' => 'Closed Period',
                    'periode' => $periode,
                    'cbg' => $cbg,
                    'status' => $status,
                    'no_bukti' => '+',
                    'header' => (object)[
                        'no_bukti' => '+',
                        'tgl' => date('Y-m-d'),
                        'total_qty' => 0,
                        'notes' => '',
                        'total' => 0
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
                    'total_qty' => 0,
                    'notes' => '',
                    'total' => 0
                ],
                'detail' => [],
                'periode' => $periode,
                'cbg' => $cbg,
                'ma' => $ma,
                'error' => null
            ];

            if ($status == 'edit' && $no_bukti && $no_bukti != '+') {
                // Cek di stockb terlebih dahulu
                $header = DB::table('stockb')
                    ->select('no_bukti', 'tgl', 'total_qty', 'notes', 'posted', 'total')
                    ->where('no_bukti', $no_bukti)
                    ->where('flag', 'MT')
                    ->first();

                // Jika sudah diposting, cek di stockbz
                if (!$header) {
                    $header = DB::table('stockbz')
                        ->select('no_bukti', 'tgl', 'total_qty', 'notes', 'posted', 'total')
                        ->where('no_bukti', $no_bukti)
                        ->where('flag', 'MT')
                        ->first();
                }

                if ($header) {
                    $headerData = $header;

                    // Cek apakah sudah posted
                    if ($headerData->posted == 1) {
                        return view('otranskasi_koreksi_toko_manual.edit', [
                            'error' => 'Transaksi sudah di Posting !!',
                            'periode' => $periode,
                            'cbg' => $cbg,
                            'status' => $status,
                            'no_bukti' => $no_bukti,
                            'header' => $headerData,
                            'detail' => []
                        ]);
                    }

                    // Ambil detail dari stockbd
                    $detail = DB::table('stockbd')
                        ->select('no_id', 'rec', 'kd_brg', 'na_brg', 'qty', 'ket', 'hb', 'total', 'ket_kem', 'ket_uk')
                        ->where('no_bukti', $no_bukti)
                        ->orderBy('rec')
                        ->get();

                    // Ambil barcode untuk setiap barang
                    foreach ($detail as $item) {
                        $brgInfo = DB::table($ma . '.brg')
                            ->select('barcode')
                            ->where('kd_brg', $item->kd_brg)
                            ->first();
                        $item->barcode = $brgInfo ? $brgInfo->barcode : '';
                    }

                    $data['header'] = $headerData;
                    $data['detail'] = $detail;
                    $data['no_bukti'] = $no_bukti;
                } else {
                    $data['error'] = 'Data tidak ditemukan';
                }
            }

            return view('otranskasi_koreksi_toko_manual.edit', $data);
        } catch (\Exception $e) {
            return view('otranskasi_koreksi_toko_manual.edit', [
                'no_bukti' => '+',
                'status' => 'simpan',
                'header' => (object)[
                    'no_bukti' => '+',
                    'tgl' => date('Y-m-d'),
                    'total_qty' => 0,
                    'notes' => '',
                    'total' => 0
                ],
                'detail' => [],
                'periode' => $periode ?? date('m/Y'),
                'cbg' => $cbg ?? '01',
                'ma' => $ma ?? 'TGZ',
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

            Log::info('TKoreksiTokoManual store started', [
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
            if ($status == 'simpan' && $no_bukti == '+') {
                // Ambil tipe toko
                $tokoInfo = DB::table('toko')
                    ->select('type')
                    ->where('kode', $cbg)
                    ->first();
                $kode2 = $tokoInfo ? $tokoInfo->type : '';

                $kode = 'MT' . substr($periode, -2) . substr($periode, 0, 2);

                // Ambil nomor terakhir dari notrans
                $lastNo = DB::table('notrans')
                    ->selectRaw("NOM" . $periode_month . " as no_bukti")
                    ->where('trans', 'MANUALTK')
                    ->where('PER', $periode_year)
                    ->first();

                $r1 = $lastNo ? intval($lastNo->no_bukti) : 0;
                $r1 = $r1 + 1;

                // Update notrans
                DB::table('notrans')
                    ->where('trans', 'MANUALTK')
                    ->where('PER', $periode_year)
                    ->update(["NOM" . $periode_month => $r1]);

                $bkt1 = str_pad($r1, 4, '0', STR_PAD_LEFT);
                $no_bukti = $kode . '-' . $bkt1 . $kode2;
            }

            // Proses update BKTK jika ada
            foreach ($request->details as $detail) {
                if (!empty($detail['bktk'])) {
                    $bktkPrefix = substr(trim($detail['bktk']), 0, 2);
                    if ($bktkPrefix == 'TS' || $bktkPrefix == 'GG') {
                        DB::table('stockbd')
                            ->where('NO_BUKTI', trim($detail['bktk']))
                            ->where('KD_BRG', trim($detail['kd_brg']))
                            ->update([
                                'BKTK' => trim($detail['bktk']),
                                'qty' => DB::raw('qty - (' . floatval($detail['qty'] ?? 0) . ' * -1)')
                            ]);
                    }
                }
            }

            // Hitung total
            $totalQty = 0;
            $totalHarga = 0;
            foreach ($request->details as $detail) {
                if (!empty($detail['kd_brg'])) {
                    $qty = floatval($detail['qty'] ?? 0);
                    $hb = floatval($detail['hb'] ?? 0);
                    $totalQty += $qty;
                    $totalHarga += ($qty * $hb);
                }
            }

            if ($status == 'simpan') {
                // Insert header
                DB::table('stockb')->insert([
                    'NO_BUKTI' => $no_bukti,
                    'TGL' => $request->tgl,
                    'FLAG' => 'MT',
                    'PER' => $periode,
                    'TOTAL_QTY' => $totalQty,
                    'NOTES' => trim($request->notes ?? ''),
                    'USRNM' => $username,
                    'TG_SMP' => now(),
                    'type' => 'MUSNAH',
                    'cbg' => $cbg,
                    'total' => $totalHarga
                ]);
            } else {
                // Update header
                DB::table('stockb')
                    ->where('NO_BUKTI', $no_bukti)
                    ->update([
                        'TGL' => $request->tgl,
                        'NOTES' => trim($request->notes ?? ''),
                        'TOTAL_QTY' => $totalQty,
                        'total' => $totalHarga,
                        'USRNM' => $username,
                        'TG_SMP' => now()
                    ]);
            }

            // Ambil ID header
            $headerId = DB::table('stockb')
                ->select('no_id')
                ->where('no_bukti', $no_bukti)
                ->first();
            $id = $headerId ? $headerId->no_id : 0;

            // Hapus detail lama jika edit
            if ($status == 'edit') {
                $existing = DB::table('stockbd')
                    ->select('no_id')
                    ->where('no_bukti', $no_bukti)
                    ->get();

                $existingIds = $existing->pluck('no_id')->toArray();
                $keepIds = array_filter(array_column($request->details, 'no_id'));

                foreach ($existingIds as $oldId) {
                    if (!in_array($oldId, $keepIds)) {
                        DB::table('stockbd')->where('no_id', $oldId)->delete();
                    }
                }
            }

            // Insert/Update detail
            $rec = 1;
            foreach ($request->details as $detail) {
                if (!empty($detail['kd_brg'])) {
                    $qty = floatval($detail['qty'] ?? 0);
                    $hb = floatval($detail['hb'] ?? 0);
                    $total = $qty * $hb;

                    if (isset($detail['no_id']) && $detail['no_id'] > 0) {
                        // Update existing
                        DB::table('stockbd')
                            ->where('NO_ID', $detail['no_id'])
                            ->update([
                                'REC' => $rec,
                                'KD_BRG' => trim($detail['kd_brg']),
                                'NA_BRG' => trim($detail['na_brg']),
                                'flag' => 'MT',
                                'ket_kem' => trim($detail['ket_kem'] ?? ''),
                                'QTY' => $qty,
                                'KET' => trim($detail['ket'] ?? ''),
                                'HB' => $hb,
                                'TOTAL' => $total
                            ]);
                    } else {
                        // Insert new
                        DB::table('stockbd')->insert([
                            'NO_BUKTI' => $no_bukti,
                            'REC' => $rec,
                            'PER' => $periode,
                            'FLAG' => 'MT',
                            'KD_BRG' => trim($detail['kd_brg']),
                            'NA_BRG' => trim($detail['na_brg']),
                            'ket_kem' => trim($detail['ket_kem'] ?? ''),
                            'QTY' => $qty,
                            'KET' => trim($detail['ket'] ?? ''),
                            'ID' => $id,
                            'hj' => 0,
                            'hb' => $hb,
                            'total' => $total
                        ]);
                    }
                    $rec++;
                }
            }

            DB::commit();

            Log::info('TKoreksiTokoManual store completed successfully', [
                'no_bukti' => $no_bukti,
                'status' => $status
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Save Data Success'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('TKoreksiTokoManual store validation failed:', [
                'errors' => $e->validator->errors()->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . implode(', ', $e->validator->errors()->all())
            ], 422);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('TKoreksiTokoManual store error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
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

            // Get session variables
            $cbg = session('flag') ?? Auth::user()->CBG ?? '01';
            $ma = session('ma', 'TGZ');

            Log::info('TKoreksiTokoManual browse started', [
                'query' => $q,
                'cbg' => $cbg,
                'ma' => $ma
            ]);

            if (!empty($q)) {
                // Use database name directly in query (not as connection)
                $data = DB::table($ma . '.brg as A')
                    ->join($ma . '.brgdt as B', 'A.kd_brg', '=', 'B.kd_brg')
                    ->select(
                        'A.kd_brg',
                        DB::raw("CONCAT(A.na_brg, ' ', A.ket_uk) as na_brg"),
                        'A.ket_kem',
                        'A.ket_uk',
                        'B.hb',
                        'A.barcode'
                    )
                    ->where('B.cbg', $cbg)
                    ->where(function ($query) use ($q) {
                        $query->where('A.kd_brg', 'LIKE', "%$q%")
                            ->orWhere('A.na_brg', 'LIKE', "%$q%")
                            ->orWhere('A.barcode', 'LIKE', "%$q%");
                    })
                    ->limit(50)
                    ->get();

                Log::info('TKoreksiTokoManual browse query executed', [
                    'query' => $q,
                    'result_count' => count($data)
                ]);
            } else {
                $data = [];
            }

            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('TKoreksiTokoManual browse error:', [
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

            Log::info('TKoreksiTokoManual getDetail started', [
                'kd_brg' => $kd_brg,
                'cbg' => $cbg,
                'ma' => $ma
            ]);

            // Use database name directly in query (not as connection)
            // Search by kd_brg OR barcode
            $barang = DB::table($ma . '.brg as A')
                ->join($ma . '.brgdt as B', 'A.kd_brg', '=', 'B.kd_brg')
                ->select(
                    'A.kd_brg',
                    DB::raw("CONCAT(A.na_brg, ' ', A.ket_uk) as na_brg"),
                    'A.ket_kem',
                    'A.ket_uk',
                    'B.hb',
                    'A.barcode'
                )
                ->where('B.cbg', $cbg)
                ->where(function ($query) use ($kd_brg) {
                    $query->where('A.kd_brg', $kd_brg)
                        ->orWhere('A.barcode', $kd_brg);
                })
                ->first();

            if ($barang) {
                Log::info('TKoreksiTokoManual getDetail found item', [
                    'kd_brg' => $kd_brg,
                    'na_brg' => $barang->na_brg
                ]);

                return response()->json([
                    'success' => true,
                    'exists' => true,
                    'data' => $barang
                ]);
            }

            Log::warning('TKoreksiTokoManual getDetail item not found', [
                'kd_brg' => $kd_brg
            ]);

            return response()->json([
                'success' => false,
                'exists' => false,
                'message' => 'Barang tidak ditemukan'
            ]);
        } catch (\Exception $e) {
            Log::error('TKoreksiTokoManual getDetail error:', [
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

    public function printKoreksiTokoManual(Request $request)
    {
        try {
            $no_bukti = $request->no_bukti;

            // Get session variables
            $cbg = session('flag') ?? Auth::user()->CBG ?? '01';
            $ma = session('ma', 'TGZ');

            Log::info('TKoreksiTokoManual print started', [
                'no_bukti' => $no_bukti,
                'cbg' => $cbg,
                'ma' => $ma,
                'user' => Auth::user()->username ?? 'unknown'
            ]);

            // Ambil nama toko
            $tokoInfo = DB::table('toko')
                ->select('na_toko')
                ->where('kode', $cbg)
                ->first();
            $toko = $tokoInfo ? $tokoInfo->na_toko : '';

            // Cek apakah sudah posted
            $headerCheck = DB::table('stockb')
                ->select('posted')
                ->where('no_bukti', $no_bukti)
                ->first();

            $isPosted = $headerCheck ? $headerCheck->posted : 1;

            if ($isPosted == 0) {
                // Ambil dari stockb dan stockbd
                $data = DB::table('stockb')
                    ->join('stockbd', 'stockbd.no_bukti', '=', 'stockb.no_bukti')
                    ->join($ma . '.brg as brg', 'stockbd.KD_BRG', '=', 'brg.KD_BRG')
                    ->leftJoin($ma . '.brgdt as brgdt', function ($join) use ($cbg) {
                        $join->on('brg.kd_brg', '=', 'brgdt.kd_brg')
                            ->where('brgdt.cbg', '=', $cbg);
                    })
                    ->select(
                        DB::raw("'$toko' as nmtoko"),
                        'stockb.no_bukti',
                        DB::raw("CONCAT(brg.KDBAR, '-', brg.SUB) AS ITEMSUB"),
                        DB::raw("CONCAT(brgdt.KDLAKU, brgdt.KLK) AS KD"),
                        DB::raw("CONCAT(brg.NA_BRG, ' ', brg.KET_UK) AS NA_BRG"),
                        'brg.KET_KEM',
                        'stockbd.qty',
                        'brgdt.HJ',
                        'brgdt.HB',
                        DB::raw("(stockbd.qty) * (brgdt.HB) as total_hb"),
                        'brg.ALASAN'
                    )
                    ->where(DB::raw('TRIM(stockb.NO_BUKTI)'), '=', DB::raw('TRIM(\'' . $no_bukti . '\')'))
                    ->get();
            } else {
                // Ambil dari stockbz dan stockbzd
                $data = DB::table('stockbz')
                    ->join('stockbzd', 'stockbzd.no_bukti', '=', 'stockbz.no_bukti')
                    ->join($ma . '.brg as brg', 'stockbzd.KD_BRG', '=', 'brg.KD_BRG')
                    ->leftJoin($ma . '.brgdt as brgdt', function ($join) use ($cbg) {
                        $join->on('brg.kd_brg', '=', 'brgdt.kd_brg')
                            ->where('brgdt.cbg', '=', $cbg);
                    })
                    ->select(
                        DB::raw("'$toko' as nmtoko"),
                        'stockbz.no_bukti',
                        DB::raw("CONCAT(brg.KDBAR, '-', brg.SUB) AS ITEMSUB"),
                        DB::raw("CONCAT(brgdt.KDLAKU, brgdt.KLK) AS KD"),
                        DB::raw("CONCAT(brg.NA_BRG, ' ', brg.KET_UK) AS NA_BRG"),
                        'brg.KET_KEM',
                        'stockbzd.qty',
                        'brgdt.HJ',
                        'brgdt.HB',
                        DB::raw("(stockbzd.qty) * (brgdt.HB) as total_hb"),
                        'brg.ALASAN'
                    )
                    ->where(DB::raw('TRIM(stockbz.NO_BUKTI)'), '=', DB::raw('TRIM(\'' . $no_bukti . '\')'))
                    ->get();
            }

            Log::info('TKoreksiTokoManual print completed', [
                'no_bukti' => $no_bukti,
                'row_count' => count($data),
                'is_posted' => $isPosted
            ]);

            return response()->json(['data' => $data]);
        } catch (\Exception $e) {
            Log::error('TKoreksiTokoManual print error:', [
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
