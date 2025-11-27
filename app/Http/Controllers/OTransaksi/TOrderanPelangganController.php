<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TOrderanPelangganController extends Controller
{
    public function index()
    {
        $periodeSession = session('periode');
        $flag = session('flag') ?? Auth::user()->CBG ?? '01';

        // Set flag in session if not set
        if (!session('flag')) {
            session(['flag' => $flag]);
        }

        // Convert periode array to string format for display
        if (is_array($periodeSession)) {
            $periode = str_pad($periodeSession['bulan'], 2, '0', STR_PAD_LEFT) . '/' . $periodeSession['tahun'];
        } else {
            $periode = $periodeSession ?? date('m/Y');
        }

        Log::info('TOrderanPelanggan Index:', [
            'periode' => $periode,
            'flag' => $flag
        ]);

        return view('otranskasi_Orderan_Pelanggan.index', compact('periode', 'flag'));
    }

    public function getOrderanPelanggan(Request $request)
    {
        try {
            $periodeSession = session('periode');
            $flag = session('flag') ?? Auth::user()->CBG ?? '01';

            if (!$periodeSession) {
                Log::warning('TOrderanPelanggan getData: Periode belum diset');
                return response()->json([
                    'draw' => intval($request->input('draw', 1)),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                    'error' => 'Periode belum diset. Silakan set periode terlebih dahulu.'
                ]);
            }

            // Convert periode array to string format MM/YYYY
            if (is_array($periodeSession)) {
                $periode = str_pad($periodeSession['bulan'], 2, '0', STR_PAD_LEFT) . '/' . $periodeSession['tahun'];
            } else {
                $periode = $periodeSession;
            }

            $queryStr = "SELECT no_bukti, tgl, ket, SUM(qty) as total_qty
                 FROM tpo
                 WHERE per=? AND flag='TC'
                 GROUP BY no_bukti
                 ORDER BY no_bukti";

            $params = [$periode];

            Log::info('TOrderanPelanggan getData Query:', [
                'query' => $queryStr,
                'params' => $params
            ]);

            $query = DB::select($queryStr, $params);

            Log::info('TOrderanPelanggan getData Result:', [
                'count' => count($query)
            ]);

            return Datatables::of(collect($query))
                ->addIndexColumn()
                ->editColumn('tgl', function ($row) {
                    return $row->tgl ? date('d/m/Y', strtotime($row->tgl)) : '';
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
            Log::error('TOrderanPelanggan Error in getData: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'draw' => intval($request->input('draw', 1)),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Terjadi kesalahan saat mengambil data: ' . $e->getMessage()
            ]);
        }
    }

    public function edit(Request $request)
    {
        try {
            $no_bukti = $request->get('no_bukti', '+');
            $status = $request->get('status', 'simpan');
            $periodeSession = session('periode');
            $flag = session('flag') ?? Auth::user()->CBG ?? '01';
            $username = Auth::user()->username ?? Auth::user()->name ?? 'system';

            // Convert periode array to string format
            if (is_array($periodeSession)) {
                $periode = str_pad($periodeSession['bulan'], 2, '0', STR_PAD_LEFT) . '/' . $periodeSession['tahun'];
            } else {
                $periode = $periodeSession ?? date('m/Y');
            }

            Log::info('TOrderanPelanggan Edit:', [
                'no_bukti' => $no_bukti,
                'status' => $status,
                'periode' => $periode,
                'flag' => $flag
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
                'flag' => $flag,
                'error' => null
            ];

            if ($status == 'edit' && $no_bukti && $no_bukti != '+') {
                $headerQuery = "SELECT no_bukti, tgl, ket, SUM(qty) as total_qty
                     FROM tpo
                     WHERE no_bukti=? AND flag='TC'
                     GROUP BY no_bukti
                     ORDER BY no_bukti";

                $headerParams = [$no_bukti];

                Log::info('TOrderanPelanggan Get Header:', [
                    'query' => $headerQuery,
                    'params' => $headerParams
                ]);

                $header = DB::select($headerQuery, $headerParams);

                if (!empty($header)) {
                    $detailQuery = "SELECT no_id, rec, kd_brg, na_brg, qty, ket, ket_kem, kdlaku, sub, kdbar
                         FROM tpo
                         WHERE no_bukti=? AND flag='TC'
                         ORDER BY rec";

                    $detailParams = [$no_bukti];

                    Log::info('TOrderanPelanggan Get Detail:', [
                        'query' => $detailQuery,
                        'params' => $detailParams
                    ]);

                    $detail = DB::select($detailQuery, $detailParams);

                    $data['header'] = $header[0];
                    $data['detail'] = $detail;
                    $data['no_bukti'] = $no_bukti;

                    Log::info('TOrderanPelanggan Edit Data Found:', [
                        'header_count' => count($header),
                        'detail_count' => count($detail)
                    ]);
                } else {
                    Log::warning('TOrderanPelanggan Edit: Data not found', ['no_bukti' => $no_bukti]);
                    $data['error'] = 'Data tidak ditemukan';
                }
            }

            return view('otranskasi_Orderan_Pelanggan.edit', $data);
        } catch (\Exception $e) {
            Log::error('TOrderanPelanggan Error in edit: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            $periodeSession = session('periode');
            if (is_array($periodeSession)) {
                $periode = str_pad($periodeSession['bulan'], 2, '0', STR_PAD_LEFT) . '/' . $periodeSession['tahun'];
            } else {
                $periode = $periodeSession ?? date('m/Y');
            }

            return view('otranskasi_Orderan_Pelanggan.edit', [
                'no_bukti' => '+',
                'status' => 'simpan',
                'header' => (object)[
                    'no_bukti' => '+',
                    'tgl' => date('Y-m-d'),
                    'ket' => '',
                    'total_qty' => 0
                ],
                'detail' => [],
                'periode' => $periode,
                'flag' => session('flag') ?? Auth::user()->CBG ?? '01',
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

            DB::beginTransaction();

            $no_bukti = trim($request->no_bukti);
            $status = $request->status;
            $periodeSession = session('periode');
            $flag = session('flag') ?? Auth::user()->CBG ?? '01';
            $username = Auth::user()->username ?? Auth::user()->name ?? 'system';

            if (!$periodeSession) {
                Log::warning('TOrderanPelanggan Store: Periode belum diset');
                return response()->json([
                    'success' => false,
                    'message' => 'Periode belum diset'
                ], 400);
            }

            // Convert periode array to string format MM/YYYY
            if (is_array($periodeSession)) {
                $periode = str_pad($periodeSession['bulan'], 2, '0', STR_PAD_LEFT) . '/' . $periodeSession['tahun'];
            } else {
                $periode = $periodeSession;
            }

            $tgl = Carbon::parse($request->tgl);
            $monthz = str_pad($tgl->month, 2, '0', STR_PAD_LEFT);
            $yearz = $tgl->year;

            $periode_month = is_array($periodeSession) ? $periodeSession['bulan'] : substr($periode, 0, 2);
            $periode_year = is_array($periodeSession) ? $periodeSession['tahun'] : substr($periode, -4);

            Log::info('TOrderanPelanggan Store: Validating periode', [
                'tgl_month' => $monthz,
                'tgl_year' => $yearz,
                'periode_month' => $periode_month,
                'periode_year' => $periode_year
            ]);

            if ($monthz != str_pad($periode_month, 2, '0', STR_PAD_LEFT)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bulan tidak sesuai dengan periode'
                ], 400);
            }

            if ($yearz != $periode_year) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tahun tidak sesuai dengan periode'
                ], 400);
            }

            if ($status == 'simpan') {
                if ($no_bukti == '+') {
                    $tokoQuery = "SELECT type FROM toko WHERE kode=?";
                    $tokoParams = [$flag];

                    Log::info('TOrderanPelanggan Get Toko Type:', [
                        'query' => $tokoQuery,
                        'params' => $tokoParams
                    ]);

                    $toko = DB::select($tokoQuery, $tokoParams);
                    $kode2 = $toko[0]->type ?? '';

                    $monthString = str_pad($periode_month, 2, '0', STR_PAD_LEFT);
                    $kode = 'TC' . substr($periode_year, -2) . $monthString;

                    $notransQuery = "SELECT NOM" . $monthString . " as no_bukti
                         FROM notrans
                         WHERE trans='ORDERTKC' AND per=?";
                    $notransParams = [$periode_year];

                    Log::info('TOrderanPelanggan Get Notrans:', [
                        'query' => $notransQuery,
                        'params' => $notransParams
                    ]);

                    $notrans = DB::select($notransQuery, $notransParams);

                    $r1 = ($notrans[0]->no_bukti ?? 0) + 1;

                    $updateNotransQuery = "UPDATE notrans SET NOM" . $monthString . "=? WHERE trans='ORDERTKC' AND per=?";
                    $updateNotransParams = [$r1, $periode_year];

                    Log::info('TOrderanPelanggan Update Notrans:', [
                        'query' => $updateNotransQuery,
                        'params' => $updateNotransParams
                    ]);

                    DB::statement($updateNotransQuery, $updateNotransParams);

                    $bkt1 = str_pad($r1, 4, '0', STR_PAD_LEFT);
                    $no_bukti = $kode . '-' . $bkt1 . $kode2;

                    Log::info('TOrderanPelanggan Generated NO_BUKTI:', ['no_bukti' => $no_bukti]);
                }
            }

            if ($status == 'edit') {
                $existingQuery = "SELECT no_id FROM tpo WHERE flag='TC' AND no_bukti=?";
                $existingParams = [$no_bukti];

                Log::info('TOrderanPelanggan Get Existing Details:', [
                    'query' => $existingQuery,
                    'params' => $existingParams
                ]);

                $existing = DB::select($existingQuery, $existingParams);

                Log::info('TOrderanPelanggan Processing existing details:', ['count' => count($existing)]);

                foreach ($existing as $row) {
                    $found = false;
                    foreach ($request->details as $detail) {
                        if (isset($detail['no_id']) && $detail['no_id'] == $row->no_id) {
                            $updateDetailQuery = "UPDATE tpo
                                 SET rec=?, tgl=?, kd_brg=?, na_brg=?, qty=?, ket=?,
                                     kdlaku=?, sub=?, kdbar=?, tg_smp=NOW(), ket_kem=?, usrnm=?
                                 WHERE no_id=?";

                            $updateDetailParams = [
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
                            ];

                            Log::info('TOrderanPelanggan Update Detail:', [
                                'query' => $updateDetailQuery,
                                'params' => $updateDetailParams
                            ]);

                            DB::statement($updateDetailQuery, $updateDetailParams);
                            $found = true;
                            break;
                        }
                    }

                    if (!$found) {
                        Log::info('TOrderanPelanggan Delete Detail:', ['no_id' => $row->no_id]);
                        DB::statement("DELETE FROM tpo WHERE no_id=?", [$row->no_id]);
                    }
                }
            }

            $rec = 1;
            foreach ($request->details as $detail) {
                if (!empty($detail['kd_brg'])) {
                    if (!isset($detail['no_id']) || $detail['no_id'] == 0) {
                        $insertDetailQuery = "INSERT INTO tpo (no_bukti, rec, per, flag, kd_brg, na_brg, qty, tgl, ket,
                                              tg_smp, kdlaku, sub, kdbar, ket_kem, usrnm, cbg)
                             VALUES (?, ?, ?, 'TC', ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?)";

                        $insertDetailParams = [
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
                            $flag
                        ];

                        Log::info('TOrderanPelanggan Insert Detail:', [
                            'query' => $insertDetailQuery,
                            'params' => $insertDetailParams
                        ]);

                        DB::statement($insertDetailQuery, $insertDetailParams);
                    }
                    $rec++;
                }
            }

            DB::commit();

            Log::info('TOrderanPelanggan: Data saved successfully', ['no_bukti' => $no_bukti]);

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil disimpan',
                'no_bukti' => $no_bukti
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('TOrderanPelanggan Validation Error:', [
                'errors' => $e->validator->errors()->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . implode(', ', $e->validator->errors()->all())
            ], 422);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('TOrderanPelanggan Error in store: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($no_bukti)
    {
        DB::beginTransaction();

        try {
            Log::info('TOrderanPelanggan Delete:', ['no_bukti' => $no_bukti]);

            $deleteQuery = "DELETE FROM tpo WHERE no_bukti=? AND flag='TC'";
            $deleteParams = [$no_bukti];

            Log::info('TOrderanPelanggan Delete Query:', [
                'query' => $deleteQuery,
                'params' => $deleteParams
            ]);

            DB::statement($deleteQuery, $deleteParams);

            DB::commit();

            Log::info('TOrderanPelanggan: Deletion successful', ['no_bukti' => $no_bukti]);

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('TOrderanPelanggan Error in destroy: ' . $e->getMessage(), [
                'no_bukti' => $no_bukti,
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
            $flag = session('flag') ?? Auth::user()->CBG ?? '01';

            Log::info('TOrderanPelanggan Browse:', [
                'search' => $q,
                'flag' => $flag
            ]);

            if (!empty($q)) {
                $query = "SELECT brg.kd_brg, CONCAT(brg.na_brg, ' ', brg.ket_uk) as na_brg,
                        brg.ket_kem, brg.sub, brg.kdbar, brgdt.kdlaku, brgdt.gak00 as stok
                 FROM brg, brgdt
                 WHERE brgdt.kd_brg=brg.kd_brg AND brgdt.cbg=?
                   AND (brg.kd_brg LIKE ? OR brg.na_brg LIKE ?)
                 LIMIT 50";

                $params = [$flag, "%$q%", "%$q%"];

                Log::info('TOrderanPelanggan Browse Query:', [
                    'query' => $query,
                    'params' => $params
                ]);

                $data = DB::select($query, $params);
            } else {
                $data = [];
            }

            Log::info('TOrderanPelanggan Browse Result:', ['count' => count($data)]);

            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('TOrderanPelanggan Error in browse: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getDetail(Request $request)
    {
        try {
            $kd_brg = $request->get('kd_brg');
            $flag = session('flag') ?? Auth::user()->CBG ?? '01';

            Log::info('TOrderanPelanggan GetDetail:', [
                'kd_brg' => $kd_brg,
                'flag' => $flag
            ]);

            $query = "SELECT brg.kd_brg, CONCAT(brg.na_brg, ' ', brg.ket_uk) as na_brg,
                    brg.ket_kem, brg.sub, brg.kdbar, brgdt.kdlaku, brgdt.gak00 as stok
             FROM brg, brgdt
             WHERE brgdt.kd_brg=brg.kd_brg AND brgdt.cbg=? AND brg.kd_brg=?";

            $params = [$flag, $kd_brg];

            Log::info('TOrderanPelanggan GetDetail Query:', [
                'query' => $query,
                'params' => $params
            ]);

            $barang = DB::select($query, $params);

            if (!empty($barang)) {
                $row = $barang[0];

                if ($row->kdlaku == '4') {
                    Log::warning('TOrderanPelanggan GetDetail: Barang kode 4', ['kd_brg' => $kd_brg]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Barang Kode 4 tidak bisa dipesan ke gudang!'
                    ]);
                }

                if ($row->stok <= 0) {
                    Log::warning('TOrderanPelanggan GetDetail: Stok habis', [
                        'kd_brg' => $kd_brg,
                        'stok' => $row->stok
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Stok Gudang Masih Belum Tersedia!'
                    ]);
                }

                Log::info('TOrderanPelanggan GetDetail: Barang found', ['kd_brg' => $kd_brg]);

                return response()->json([
                    'success' => true,
                    'exists' => true,
                    'data' => $row
                ]);
            }

            Log::warning('TOrderanPelanggan GetDetail: Barang not found', ['kd_brg' => $kd_brg]);

            return response()->json([
                'success' => false,
                'exists' => false,
                'message' => 'Barang tidak ditemukan'
            ]);
        } catch (\Exception $e) {
            Log::error('TOrderanPelanggan Error in getDetail: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail: ' . $e->getMessage()
            ], 500);
        }
    }

    public function printOrderanPelanggan(Request $request)
    {
        try {
            $no_bukti = $request->no_bukti;
            $flag = session('flag') ?? Auth::user()->CBG ?? '01';

            Log::info('TOrderanPelanggan Print:', [
                'no_bukti' => $no_bukti,
                'flag' => $flag
            ]);

            $dataQuery = "SELECT TIME(tpo.tg_smp) as timo, tpo.tgl, tpo.no_bukti, tpo.sub, tpo.kdbar,
                    brg.na_brg, tpo.ket_kem, brg.ket_uk, brgdt.gak00 as stockr, brgdt.ak00 as stockr_tk,
                    CONCAT(brgdt.kdlaku, brgdt.klk) as kd, brgdt.srmax as smax_tk, brgdt.dtr, tpo.qty, tpo.ket
             FROM tpo, brg, brgdt
             WHERE tpo.kd_brg=brg.kd_brg AND tpo.kd_brg=brgdt.kd_brg
               AND brgdt.cbg=? AND tpo.no_bukti=? AND tpo.flag='TC'
             ORDER BY tpo.no_bukti";

            $dataParams = [$flag, $no_bukti];

            Log::info('TOrderanPelanggan Print Get Data:', [
                'query' => $dataQuery,
                'params' => $dataParams
            ]);

            $data = DB::select($dataQuery, $dataParams);

            $updateQuery = "UPDATE tpo SET prnt=1 WHERE no_bukti=? AND flag='TC'";
            $updateParams = [$no_bukti];

            Log::info('TOrderanPelanggan Print Update:', [
                'query' => $updateQuery,
                'params' => $updateParams
            ]);

            DB::statement($updateQuery, $updateParams);

            Log::info('TOrderanPelanggan Print Data Retrieved:', ['count' => count($data)]);

            return response()->json(['data' => $data]);
        } catch (\Exception $e) {
            Log::error('TOrderanPelanggan Error in printOrderanPelanggan: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal mencetak: ' . $e->getMessage()
            ], 500);
        }
    }
}
