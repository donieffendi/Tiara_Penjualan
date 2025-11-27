<?php
namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PHPJasperXML;
include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use Yajra\DataTables\Facades\DataTables;

class TOrderKoreksiPembelianController extends Controller
{
    public function index()
    {
        // Set flag dari CBG user jika belum ada di session
        if (! session('flag')) {
            $flag = Auth::user()->CBG ?? null;
            if ($flag) {
                session(['flag' => $flag]);
            }
        }

        return view('otranskasi_order_koreksi_pembelian.index');
    }

    public function getOrderKoreksiPembelian(Request $request)
    {
        try {
            $periodeSession = session('periode');
            $flag           = session('flag');

            // Jika flag tidak ada di session, gunakan CBG dari user yang login
            if (! $flag) {
                $flag = Auth::user()->CBG ?? null;
                if ($flag) {
                    session(['flag' => $flag]);
                }
            }

            if (! $periodeSession) {
                Log::warning('TOrderKoreksiPembelian: Periode belum diset');
                return response()->json([
                    'error'           => 'Periode belum diset',
                    'draw'            => intval($request->input('draw', 0)),
                    'recordsTotal'    => 0,
                    'recordsFiltered' => 0,
                    'data'            => [],
                ], 200);
            }

            if (! $flag) {
                Log::warning('TOrderKoreksiPembelian: Cabang (flag) belum diset');
                return response()->json([
                    'error'           => 'Cabang (flag) belum diset',
                    'draw'            => intval($request->input('draw', 0)),
                    'recordsTotal'    => 0,
                    'recordsFiltered' => 0,
                    'data'            => [],
                ], 200);
            }

            // Convert periode array to string format MM/YYYY
            $periode = str_pad($periodeSession['bulan'], 2, '0', STR_PAD_LEFT) . '/' . $periodeSession['tahun'];

            $queryStr = "SELECT NO_BUKTI, TGL, PER, KODES, NAMAS, TOTAL_QTY, FLAG, TG_SMP, CBG,
                    EXP, OPERATOR, USRNM, NOTES, total, LPH1, LPH2, HARI, SUB1, SUB2
             FROM khusus
             WHERE per=? AND flag='BL' AND cbg=?
             GROUP BY no_bukti
             ORDER BY NO_BUKTI DESC";

            Log::info('TOrderKoreksiPembelian Query:', [
                'query'  => $queryStr,
                'params' => [$periode, $flag],
            ]);

            $query = DB::select($queryStr, [$periode, $flag]);
            // dd($query);

            Log::info('TOrderKoreksiPembelian: Data retrieved', ['count' => count($query)]);

            return Datatables::of(collect($query))
                ->addIndexColumn()
                ->editColumn('TGL', function ($row) {
                    return $row->TGL ? date('d-m-Y', strtotime($row->TGL)) : '';
                })
                ->editColumn('TG_SMP', function ($row) {
                    return $row->TG_SMP ? date('d-m-Y H:i', strtotime($row->TG_SMP)) : '';
                })
                ->editColumn('TOTAL_QTY', function ($row) {
                    return number_format($row->TOTAL_QTY, 0, ',', '.');
                })
                ->editColumn('total', function ($row) {
                    return number_format($row->total, 2, ',', '.');
                })
                ->addColumn('action', function ($row) {
                    if (
                        Auth::user()->divisi == "programmer" || Auth::user()->divisi == "owner" ||
                        Auth::user()->divisi == "assistant" || Auth::user()->divisi == "accounting"
                    ) {
                        $btnEdit = '<a class="dropdown-item" href="' . route('torderkoreksipembelian.edit', ['no_bukti' => $row->NO_BUKTI]) . '">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>';
                        $btnDelete = '<a class="dropdown-item btn-delete" href="' . route('torderkoreksipembelian.delete', ['no_bukti' => $row->NO_BUKTI]) . '" data-id="' . $row->NO_BUKTI . '">
                                        <i class="fa fa-trash"></i> Delete
                                      </a>';
                        $btnPrint = '<a class="dropdown-item btn-print" data-id="' . $row->NO_BUKTI . '" href="' . route('torderkoreksipembelian.print', ['no_bukti' => $row->NO_BUKTI]) . '">
                                        <i class="fa fa-print"></i> Print
                                      </a>';

                        $actionBtn = '
                        <div class="dropdown show" style="text-align: center">
                            <a class="btn btn-secondary dropdown-toggle btn-sm" href="#" role="button"
                               id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-bars"></i>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                                ' . $btnEdit . '
                                ' . $btnPrint . '
                                <hr>
                                ' . $btnDelete . '
                            </div>
                        </div>';

                        return $actionBtn;
                    }
                    return '';
                })
                ->rawColumns(['action'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('TOrderKoreksiPembelian Error in getData: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'error'           => 'Terjadi kesalahan: ' . $e->getMessage(),
                'draw'            => intval($request->input('draw', 0)),
                'recordsTotal'    => 0,
                'recordsFiltered' => 0,
                'data'            => [],
            ], 200);
        }
    }

    public function edit(Request $request)
    {
        try {
            $no_bukti       = $request->get('no_bukti');
            $status         = $request->get('status', 'simpan');
            $periodeSession = session('periode');
            $flag           = session('flag') ?? Auth::user()->CBG;

            if (! $periodeSession) {
                Log::warning('TOrderKoreksiPembelian Edit: Periode belum diset');
                return redirect()->back()->with('error', 'Periode belum diset');
            }

            // Convert periode array to string format MM/YYYY
            $periode = str_pad($periodeSession['bulan'], 2, '0', STR_PAD_LEFT) . '/' . $periodeSession['tahun'];

            $data = [
                'no_bukti' => $no_bukti,
                'status'   => $status,
                'header'   => null,
                'detail'   => [],
                'periode'  => $periode,
                'cbg'      => $flag,
            ];

            $queryStr = "SELECT NO_BUKTI, TGL, PER, KODES, NAMAS, TOTAL_QTY, FLAG, TG_SMP, CBG,
                        EXP, OPERATOR, USRNM, NOTES, total, LPH1, LPH2, HARI, SUB1, SUB2, SP
                 FROM khusus
                 WHERE no_bukti='$no_bukti' AND flag='BL'
                 GROUP BY no_bukti
                 ORDER BY NO_BUKTI";

            $header = DB::select($queryStr, [$no_bukti]);

            if (! empty($header)) {
                $detailQueryStr = "SELECT * FROM KHUSUSD
                     WHERE NO_BUKTI = '$no_bukti'
                     ORDER BY REC";

                $detail = DB::select($detailQueryStr, [$no_bukti]);

                $data['header']   = $header[0];
                $data['detail']   = $detail;
                $data['no_bukti'] = $no_bukti;
                // dd($data);
            }
            // dd($data);

            return view('otranskasi_order_koreksi_pembelian.edit', $data);
        } catch (\Exception $e) {
            Log::error('TOrderKoreksiPembelian Error in edit: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // public function store(Request $request)
    // {

    //     dd($request->all());

    //     DB::beginTransaction();

    //     try {
    //         $no_bukti       = trim($request->no_bukti);
    //         $status         = $request->status;
    //         $periodeSession = session('periode');
    //         $flag           = session('flag') ?? Auth::user()->CBG;
    //         $username       = Auth::user()->username ?? Auth::user()->name ?? 'system';

    //         // Convert periode array to string format MM/YYYY
    //         $periode = str_pad($periodeSession['bulan'], 2, '0', STR_PAD_LEFT) . '/' . $periodeSession['tahun'];

    //         $tgl    = Carbon::parse($request->tgl);
    //         $monthz = str_pad($tgl->month, 2, '0', STR_PAD_LEFT);
    //         $yearz  = $tgl->year;

    //         $periode_month = $periodeSession['bulan'];
    //         $periode_year  = $periodeSession['tahun'];

    //         // if ($monthz != str_pad($periode_month, 2, '0', STR_PAD_LEFT)) {
    //         //     return response()->json([
    //         //         'success' => false,
    //         //         'message' => 'Bulan tidak sesuai dengan periode',
    //         //     ], 400);
    //         // }

    //         // if ($yearz != $periode_year) {
    //         //     return response()->json([
    //         //         'success' => false,
    //         //         'message' => 'Tahun tidak sesuai dengan periode',
    //         //     ], 400);
    //         // }

    //         $total_qty    = 0;
    //         $total_amount = 0;

    //         foreach ($request->details as $detail) {
    //             if (! empty($detail['kd_brg'])) {
    //                 $total_qty += floatval($detail['qty'] ?? 0);
    //                 $total_amount += floatval($detail['total'] ?? 0);
    //             }
    //         }

    //         if ($status == 'simpan') {
    //             if ($no_bukti == '+') {
    //                 $no_bukti = $this->generateNoBukti($periodeSession, $flag);
    //             }

    //             $insertQuery = "INSERT INTO KHUSUS (NO_BUKTI, TGL, PER, KODES, NAMAS, TOTAL_QTY, FLAG, TG_SMP, CBG,
    //                                      EXP, OPERATOR, USRNM, NOTES, total, LPH1, LPH2, HARI, SUB1, SUB2)
    //                  VALUES (?, ?, ?, ?, ?, ?, 'BL', NOW(), ?, ?, 'C', ?, ?, ?, ?, ?, ?, ?, ?)";

    //             $insertParams = [
    //                 $no_bukti,
    //                 $request->tgl,
    //                 $periode,
    //                 $request->kodes,
    //                 $request->namas ?? '',
    //                 $total_qty,
    //                 $flag,
    //                 '',
    //                 $username,
    //                 $request->notes ?? '',
    //                 $total_amount,
    //                 floatval($request->lph1 ?? 0),
    //                 floatval($request->lph2 ?? 0),
    //                 floatval($request->hari ?? 0),
    //                 $request->sub1 ?? '',
    //                 $request->sub2 ?? '',
    //             ];

    //             DB::statement($insertQuery, $insertParams);
    //         } else {
    //             $updateQuery = "UPDATE KHUSUS SET TGL=?, KODES=?, NAMAS=?, NOTES=?, TOTAL_QTY=?, TOTAL=?, FLAG='BL',
    //                         CBG=?, EXP=?, OPERATOR='C', LPH1=?, LPH2=?, HARI=?, SUB1=?, SUB2=?,
    //                         USRNM=?, TG_SMP=NOW(), posted=0
    //                  WHERE NO_BUKTI=?";

    //             $updateParams = [
    //                 $request->tgl,
    //                 $request->kodes,
    //                 $request->namas ?? '',
    //                 $request->notes ?? '',
    //                 $total_qty,
    //                 $total_amount,
    //                 $flag,
    //                 '',
    //                 floatval($request->lph1 ?? 0),
    //                 floatval($request->lph2 ?? 0),
    //                 floatval($request->hari ?? 0),
    //                 $request->sub1 ?? '',
    //                 $request->sub2 ?? '',
    //                 $username,
    //                 $no_bukti,
    //             ];

    //             Log::info('TOrderKoreksiPembelian Update Header:', [
    //                 'query'  => $updateQuery,
    //                 'params' => $updateParams,
    //             ]);

    //             DB::statement($updateQuery, $updateParams);
    //         }

    //         $header_id_result = DB::select("SELECT no_id FROM khusus WHERE no_bukti=?", [$no_bukti]);
    //         $id               = $header_id_result[0]->no_id ?? 0;

    //         Log::info('TOrderKoreksiPembelian: Header ID', ['id' => $id]);

    //         if ($status == 'edit') {
    //             $existing_details = DB::select("SELECT no_id FROM khususd WHERE id=?", [$id]);

    //             foreach ($existing_details as $existing) {
    //                 $found = false;
    //                 foreach ($request->details as $detail) {
    //                     if (isset($detail['no_id']) && $detail['no_id'] == $existing->no_id) {
    //                         $updateDetailQuery = "UPDATE KHUSUSD SET REC=?, KODES=?, KD_BRG=?, NA_BRG=?, QTYPO=?, QTYBRG=?, LPH=?,
    //                                     ket_kem=?, QTY=?, HARGA=?, TOTAL=?, NOTES=?, kemasan=?, ket_uk=?
    //                              WHERE NO_ID=?";

    //                         $updateDetailParams = [
    //                             intval($detail['rec'] ?? 1),
    //                             trim($detail['kodes'] ?? ''),
    //                             trim($detail['kd_brg'] ?? ''),
    //                             trim($detail['na_brg'] ?? ''),
    //                             floatval($detail['qtypo'] ?? 0),
    //                             floatval($detail['qtybrg'] ?? 0),
    //                             floatval($detail['lph'] ?? 0),
    //                             $detail['ket_kem'] ?? '',
    //                             floatval($detail['qty'] ?? 0),
    //                             floatval($detail['harga'] ?? 0),
    //                             floatval($detail['total'] ?? 0),
    //                             trim($detail['notes'] ?? ''),
    //                             floatval($detail['kemasan'] ?? 0),
    //                             $detail['ket_uk'] ?? '',
    //                             $existing->no_id,
    //                         ];

    //                         DB::statement($updateDetailQuery, $updateDetailParams);
    //                         $found = true;
    //                         break;
    //                     }
    //                 }

    //                 if (! $found) {
    //                     DB::statement("DELETE FROM KHUSUSD WHERE NO_ID=?", [$existing->no_id]);
    //                 }
    //             }
    //         }

    //         $rec = 1;
    //         foreach ($request->details as $detail) {
    //             if (! empty($detail['kd_brg'])) {
    //                 if (! isset($detail['no_id']) || $detail['no_id'] == 0) {
    //                     $insertDetailQuery = "INSERT INTO KHUSUSD (NO_BUKTI, QTYBRG, LPH, KODES, REC, PER, FLAG, KD_BRG, NA_BRG,
    //                                                KEMASAN, ket_kem, QTYPO, QTY, HARGA, TOTAL, NOTES, ID, cbg, ket_uk)
    //                          VALUES (?, ?, ?, ?, ?, ?, 'BL', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    //                     $insertDetailParams = [
    //                         $no_bukti,
    //                         floatval($detail['qtybrg'] ?? 0),
    //                         floatval($detail['lph'] ?? 0),
    //                         trim($detail['kodes'] ?? ''),
    //                         $rec,
    //                         $periode,
    //                         trim($detail['kd_brg'] ?? ''),
    //                         trim($detail['na_brg'] ?? ''),
    //                         floatval($detail['kemasan'] ?? 0),
    //                         $detail['ket_kem'] ?? '',
    //                         floatval($detail['qtypo'] ?? 0),
    //                         floatval($detail['qty'] ?? 0),
    //                         floatval($detail['harga'] ?? 0),
    //                         floatval($detail['total'] ?? 0),
    //                         trim($detail['notes'] ?? ''),
    //                         $id,
    //                         $flag,
    //                         $detail['ket_uk'] ?? '',
    //                     ];

    //                     DB::statement($insertDetailQuery, $insertDetailParams);
    //                 }
    //                 $rec++;
    //             }
    //         }

    //         DB::statement("DELETE FROM SPO WHERE no_bukti=?", [$no_bukti]);

    //         DB::commit();

    //         return response()->json([
    //             'success'  => true,
    //             'message'  => 'Data berhasil disimpan',
    //             'no_bukti' => $no_bukti,
    //         ]);
    //     } catch (\Exception $e) {
    //         DB::rollback();

    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Gagal menyimpan data: ' . $e->getMessage(),
    //         ], 500);
    //     }
    // }
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {

            $no_bukti = trim($request->no_bukti);
            // dd($no_bukti);
            $periodeSession = session('periode');
            $flag           = session('flag') ?? Auth::user()->CBG;
            $username       = Auth::user()->username ?? Auth::user()->name ?? 'system';

            $periode = str_pad($periodeSession['bulan'], 2, '0', STR_PAD_LEFT) . '/' . $periodeSession['tahun'];

            // Hitung total
            $total_qty    = 0;
            $total_amount = 0;

            foreach ($request->details as $detail) {
                if (! empty($detail['kd_brg'])) {
                    $total_qty += floatval($detail['qty'] ?? 0);
                    $total_amount += floatval($detail['total'] ?? 0);
                }
            }

            // === CEK APAKAH NO_BUKTI SUDAH ADA ===
            $cek = DB::select("SELECT no_bukti FROM KHUSUS WHERE no_bukti=?", [$no_bukti]);
            // dd($cek);

            // === INSERT BARU ===
            if (empty($cek)) {

                // Jika no_bukti = "+" → generate
                if ($no_bukti == '+') {
                    $no_bukti = $this->generateNoBukti($periodeSession, $flag);
                }

                $insertQuery = "
                INSERT INTO KHUSUS
                (NO_BUKTI, TGL, PER, KODES, NAMAS, TOTAL_QTY, FLAG, TG_SMP, CBG,
                 EXP, OPERATOR, USRNM, NOTES, TOTAL, LPH1, LPH2, HARI, SUB1, SUB2)
                VALUES (?, ?, ?, ?, ?, ?, 'BL', NOW(), ?, ?, 'C', ?, ?, ?, ?, ?, ?, ?, ?)
            ";

                $insertParams = [
                    $no_bukti,
                    $request->tgl,
                    $periode,
                    $request->kodes,
                    $request->namas ?? '',
                    $total_qty,
                    $flag,
                    '',
                    $username,
                    $request->notes ?? '',
                    $total_amount,
                    floatval($request->lph1 ?? 0),
                    floatval($request->lph2 ?? 0),
                    floatval($request->hari ?? 0),
                    $request->sub1 ?? '',
                    $request->sub2 ?? '',
                ];

                DB::statement($insertQuery, $insertParams);

            }
            // === UPDATE DATA YANG ADA ===
            else {

                $updateQuery = "
                UPDATE KHUSUS SET
                    TGL=?, KODES=?, NAMAS=?, NOTES=?,
                    TOTAL_QTY=?, TOTAL=?, FLAG='BL',
                    CBG=?, EXP=?, OPERATOR='C',
                    LPH1=?, LPH2=?, HARI=?, SUB1=?, SUB2=?,
                    USRNM=?, TG_SMP=NOW(), POSTED=0
                WHERE NO_BUKTI=?
            ";

                $updateParams = [
                    $request->tgl,
                    $request->kodes,
                    $request->namas ?? '',
                    $request->notes ?? '',
                    $total_qty,
                    $total_amount,
                    $flag,
                    '',
                    floatval($request->lph1 ?? 0),
                    floatval($request->lph2 ?? 0),
                    floatval($request->hari ?? 0),
                    $request->sub1 ?? '',
                    $request->sub2 ?? '',
                    $username,
                    $no_bukti,
                ];

                DB::statement($updateQuery, $updateParams);
            }

            // === Ambil ID ===
            $header = DB::select("SELECT no_bukti FROM khusus WHERE no_bukti=?", [$no_bukti]);
            $id     = $header[0]->no_bukti ?? 0;

            // === UPDATE DETAIL ===
            // Hapus semua detail lama → Insert ulang
            DB::statement("DELETE FROM KHUSUSD WHERE no_bukti=?", [$id]);
            $rec = 1;
            // dd($request->details);
            foreach ($request->details as $detail) {
                if (! empty($detail['kd_brg'])) {

                    $insertDetailQuery = "
                    INSERT INTO KHUSUSD
                    (NO_BUKTI, QTYBRG, LPH, KODES, REC, PER, FLAG, KD_BRG, NA_BRG,
                     KEMASAN, ket_kem, QTYPO, QTY, HARGA, TOTAL, NOTES, ID, CBG, ket_uk)
                    VALUES (?, ?, ?, ?, ?, ?, 'BL', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ";

                    $insertDetailParams = [
                        $no_bukti,
                        floatval($detail['qtybrg'] ?? 0),
                        floatval($detail['lph'] ?? 0),
                        trim($detail['kodes'] ?? ''),
                        $rec,
                        $periode,
                        trim($detail['kd_brg'] ?? ''),
                        trim($detail['na_brg'] ?? ''),
                        floatval($detail['kemasan'] ?? 0),
                        $detail['ket_kem'] ?? '',
                        floatval($detail['qtypo'] ?? 0),
                        floatval($detail['qty'] ?? 0),
                        floatval($detail['harga'] ?? 0),
                        floatval($detail['total'] ?? 0),
                        trim($detail['notes'] ?? ''),
                        $id,
                        $flag,
                        $detail['ket_uk'] ?? '',
                    ];

                    DB::statement($insertDetailQuery, $insertDetailParams);

                    $rec++;
                } else {

                }
            }

            // Hapus SPO
            DB::statement("DELETE FROM SPO WHERE NO_BUKTI=?", [$no_bukti]);

            DB::commit();

            return response()->json([
                'success'  => true,
                'message'  => 'Data berhasil disimpan',
                'no_bukti' => $no_bukti,
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => "Gagal: " . $e->getMessage(),
            ]);
        }
    }

    public function destroy($no_bukti)
    {
        try {
            DB::beginTransaction();

            // Cek apakah header ada
            $header = DB::select("SELECT no_bukti FROM khusus WHERE no_bukti=?", [$no_bukti]);

            if (empty($header)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan',
                ], 404);
            }

            // Delete detail
            DB::statement("DELETE FROM khususd WHERE no_bukti=?", [$no_bukti]);

            // Delete header
            DB::statement("DELETE FROM khusus WHERE no_bukti=?", [$no_bukti]);

            DB::commit();

            // return response()->json([
            //     'success' => true,
            //     'message' => 'Data berhasil dihapus',
            // ]);
            return view('otranskasi_order_koreksi_pembelian.index');

        } catch (\Exception $e) {

            DB::rollback();

            Log::error('TOrderKoreksiPembelian Error in destroy: ' . $e->getMessage(), [
                'no_bukti' => $no_bukti,
                'trace'    => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function browse(Request $request)
    {
        try {
            $type = $request->get('type', 'supplier');
            $q    = $request->get('q', '');
            $flag = session('flag') ?? Auth::user()->CBG;

            Log::info('TOrderKoreksiPembelian Browse:', [
                'type'   => $type,
                'search' => $q,
                'flag'   => $flag,
            ]);

            if ($type == 'supplier') {
                if (! empty($q)) {
                    $query  = "SELECT kodes, namas FROM sup WHERE kodes LIKE ? OR namas LIKE ? ORDER BY kodes LIMIT 50";
                    $params = ["%$q%", "%$q%"];

                    Log::info('TOrderKoreksiPembelian Browse Supplier with search:', [
                        'query'  => $query,
                        'params' => $params,
                    ]);

                    $data = DB::select($query, $params);
                } else {
                    $query = "SELECT kodes, namas FROM sup ORDER BY kodes LIMIT 50";

                    Log::info('TOrderKoreksiPembelian Browse All Suppliers:', ['query' => $query]);

                    $data = DB::select($query);
                }
            } elseif ($type == 'barang') {
                if (! empty($q)) {
                    $query = "SELECT brg.kd_brg, brg.na_brg, brg.type, brg.ket_uk, brg.ket_kem, brgdt.lph, brg.supp,
                            brgdt.hj, brgdt.hb, brg.namas, brgdt.klk, brg.mo, brg.loc, brgdt.srmin as sr_min,
                            brgdt.srmax as smax_tk, brgdt.dtr, brgdt.smin, brgdt.smax, brgdt.hb, brgdt.kdlaku,
                            brg.sub, brg.kdbar,
                            substr(trim(brg.KET_KEM), ((LOCATE('/', trim(brg.ket_kem))+1))) AS kemasan,
                            IFNULL((SELECT sum(pod.qty) FROM pod WHERE pod.cbg=? AND pod.KD_BRG=brg.KD_BRG GROUP BY pod.KD_BRG), 0) as totalpo,
                            brgdt.AK00+brgdt.GAK00 as saldo
                     FROM brg, brgdt
                     WHERE brg.KD_BRG=brgdt.KD_BRG
                       AND brgdt.yer=year(now())
                       AND brgdt.cbg=?
                       AND (brg.KD_BRG LIKE ? OR brg.NA_BRG LIKE ?)
                     LIMIT 50";

                    $params = [$flag, $flag, "%$q%", "%$q%"];

                    Log::info('TOrderKoreksiPembelian Browse Barang with search:', [
                        'query'  => $query,
                        'params' => $params,
                    ]);

                    $data = DB::select($query, $params);
                } else {
                    $data = [];
                }
            } else {
                $data = [];
            }

            Log::info('TOrderKoreksiPembelian Browse Result:', ['count' => count($data)]);

            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('TOrderKoreksiPembelian Error in browse: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getDetail(Request $request)
    {
        try {
            $type = $request->get('type', 'supplier');
            $flag = session('flag') ?? Auth::user()->CBG;

            Log::info('TOrderKoreksiPembelian GetDetail:', ['type' => $type, 'flag' => $flag]);

            if ($type == 'supplier') {
                $kodes = $request->get('kodes');

                $query  = "SELECT kodes, namas FROM sup WHERE kodes=?";
                $params = [$kodes];

                Log::info('TOrderKoreksiPembelian GetDetail Supplier:', [
                    'query'  => $query,
                    'params' => $params,
                ]);

                $supplier = DB::select($query, $params);

                if (! empty($supplier)) {
                    Log::info('TOrderKoreksiPembelian GetDetail Supplier Found:', ['kodes' => $kodes]);
                    return response()->json([
                        'success' => true,
                        'exists'  => true,
                        'data'    => $supplier[0],
                    ]);
                }
            } elseif ($type == 'barang') {
                $kd_brg = $request->get('kd_brg');

                $query = "SELECT brg.kd_brg, brg.na_brg, brg.type, brg.ket_uk, brg.ket_kem, brgdt.lph, brg.supp,
                        brgdt.hj, brgdt.hb, brg.namas, brgdt.klk, brg.mo, brg.loc, brgdt.srmin as sr_min,
                        brgdt.srmax as smax_tk, brgdt.dtr, brgdt.smin, brgdt.smax, brgdt.hb, brgdt.kdlaku,
                        brg.sub, brg.kdbar,
                        substr(trim(brg.KET_KEM), ((LOCATE('/', trim(brg.ket_kem))+1))) AS kemasan,
                        IFNULL((SELECT sum(pod.qty) FROM pod WHERE pod.cbg=? AND pod.KD_BRG=brg.KD_BRG GROUP BY pod.KD_BRG), 0) as totalpo,
                        brgdt.AK00+brgdt.GAK00 as saldo
                 FROM brg, brgdt
                 WHERE brg.KD_BRG=brgdt.KD_BRG
                   AND brgdt.yer=year(now())
                   AND brgdt.cbg=?
                   AND BRG.KD_BRG=?";

                $params = [$flag, $flag, $kd_brg];

                Log::info('TOrderKoreksiPembelian GetDetail Barang:', [
                    'query'  => $query,
                    'params' => $params,
                ]);

                $barang = DB::select($query, $params);

                if (! empty($barang)) {
                    Log::info('TOrderKoreksiPembelian GetDetail Barang Found:', ['kd_brg' => $kd_brg]);
                    return response()->json([
                        'success' => true,
                        'exists'  => true,
                        'data'    => $barang[0],
                    ]);
                }
            }

            Log::warning('TOrderKoreksiPembelian GetDetail: Data not found', ['type' => $type]);

            return response()->json([
                'success' => true,
                'exists'  => false,
                'data'    => null,
            ]);
        } catch (\Exception $e) {
            Log::error('TOrderKoreksiPembelian Error in getDetail: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function printOrderKoreksiPembelian(Request $request)
    {
        try {
            $no_bukti = $request->no_bukti;
            $flag     = session('flag') ?? Auth::user()->CBG;

            // Check if already posted
            $postedQuery  = "SELECT posted FROM khusus WHERE no_bukti=?";
            $postedParams = [$no_bukti];

            $posted = DB::select($postedQuery, $postedParams);

            if (! empty($posted) && $posted[0]->posted == 0) {

                $insertSPOQuery = "INSERT INTO SPO(no_bukti, tgl, per, kodes, namas, notes, flag, total, nett, type, tg_smp, CBG,
                                 KET_KEM, kemasan, NA_BRG, KD_BRG, QTY, HARGA, SUB, KDBAR, KET, TGO, TGL_MULAI)
                 SELECT khusus.NO_BUKTI, date(now()), khusus.per, khususd.kodes, khusus.namas, KHUSUS.notes, KHUSUS.FLAG,
                        KHUSUSd.total, KHUSUSd.total, KHUSUS.type, now(), KHUSUS.cbg, KHUSUSd.ket_kem, khususd.kemasan,
                        khususd.NA_BRG, khususd.KD_BRG, khususd.qty, khususd.harga,
                        left(trim(khususd.kd_brg), 3) as sub, right(trim(khususd.kd_brg), 4) as kdbar,
                        khusus.ket, date(now()), date(now())
                 FROM khusus, khususd
                 WHERE khusus.no_bukti=khususd.no_bukti AND khusus.no_bukti=?";

                $insertSPOParams = [$no_bukti];

                Log::info('TOrderKoreksiPembelian Insert SPO:', [
                    'query'  => $insertSPOQuery,
                    'params' => $insertSPOParams,
                ]);

                DB::statement($insertSPOQuery, $insertSPOParams);

                $updatePostedQuery  = "UPDATE khusus SET posted=1 WHERE no_bukti=?";
                $updatePostedParams = [$no_bukti];

                Log::info('TOrderKoreksiPembelian Update Posted Status:', [
                    'query'  => $updatePostedQuery,
                    'params' => $updatePostedParams,
                ]);

                DB::statement($updatePostedQuery, $updatePostedParams);
            }

            // Get print data
            $printDataQuery = "SELECT khusus.NO_BUKTI, khusus.per, khususd.kodes, khusus.namas, KHUSUS.notes, KHUSUS.FLAG,
                    KHUSUSd.total, KHUSUS.type, khusus.hari, KHUSUS.cbg, brg.ket_kem, khususd.NA_BRG,
                    khususd.KD_BRG, khususd.qty, khususd.harga, brg.mo, khususd.riil, khususd.qtybrg,
                    left(trim(khususd.kd_brg), 3) as sub, right(trim(khususd.kd_brg), 4) as kdbar,
                    khusus.ket, date(now()) as tgl, brg.ket_uk, khususd.kd_laku
             FROM khusus, khususd, brg
             WHERE khusus.no_bukti=khususd.no_bukti
               AND khususd.KD_BRG=brg.KD_BRG
               AND khusus.no_bukti=?";

            $printDataParams = [$no_bukti];

            Log::info('TOrderKoreksiPembelian Get Print Data:', [
                'query'  => $printDataQuery,
                'params' => $printDataParams,
            ]);

            $data = DB::select($printDataQuery, $printDataParams);

            // Log::info('TOrderKoreksiPembelian Print Data Retrieved:', ['count' => count($data)]);

            // return response()->json(['data' => $data]);

            $file         = 'order_koreksi_pembelian';
            $PHPJasperXML = new PHPJasperXML();
            $PHPJasperXML->load_xml_file(base_path("/app/reportc01/phpjasperxml/{$file}.jrxml"));

            // $PHPJasperXML->setData($data);
            $cleanData = json_decode(json_encode($data), true);
            $PHPJasperXML->setData($cleanData);

            // dd($data);

            ob_end_clean();
            $PHPJasperXML->outpage("I");
        } catch (\Exception $e) {
            Log::error('TOrderKoreksiPembelian Error in printOrderKoreksiPembelian: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal mencetak: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function generateNoBukti($periodeSession, $flag)
    {
        // Convert periode array to string format MM/YYYY if it's an array
        if (is_array($periodeSession)) {
            $monthString = str_pad($periodeSession['bulan'], 2, '0', STR_PAD_LEFT);
            $year        = $periodeSession['tahun'];
        } else {
            // Fallback for old format MM.YYYY or MM/YYYY
            $monthString = substr($periodeSession, 0, 2);
            $year        = substr($periodeSession, -4);
        }

        Log::info('TOrderKoreksiPembelian Generate NO_BUKTI:', [
            'month' => $monthString,
            'year'  => $year,
            'flag'  => $flag,
        ]);

        $tokoQuery  = "SELECT type FROM toko WHERE kode=?";
        $tokoParams = [$flag];

        Log::info('TOrderKoreksiPembelian Get Toko Type:', [
            'query'  => $tokoQuery,
            'params' => $tokoParams,
        ]);

        $toko  = DB::select($tokoQuery, $tokoParams);
        $kode2 = $toko[0]->type ?? '';

        $kode = 'BL' . substr($year, -2) . $monthString;

        $notransQuery  = "SELECT NOM{$monthString} as no_bukti FROM notrans WHERE trans='KHU' AND per=?";
        $notransParams = [$year];

        Log::info('TOrderKoreksiPembelian Get Notrans:', [
            'query'  => $notransQuery,
            'params' => $notransParams,
        ]);

        $notrans = DB::select($notransQuery, $notransParams);
        $r1      = ($notrans[0]->no_bukti ?? 0) + 1;

        $updateNotransQuery  = "UPDATE notrans SET NOM{$monthString}=? WHERE trans='KHU' AND per=?";
        $updateNotransParams = [$r1, $year];

        Log::info('TOrderKoreksiPembelian Update Notrans:', [
            'query'  => $updateNotransQuery,
            'params' => $updateNotransParams,
        ]);

        DB::statement($updateNotransQuery, $updateNotransParams);

        $bkt1             = str_pad($r1, 4, '0', STR_PAD_LEFT);
        $generatedNoBukti = $kode . '-' . $bkt1 . $kode2;

        Log::info('TOrderKoreksiPembelian Generated NO_BUKTI:', ['no_bukti' => $generatedNoBukti]);

        return $generatedNoBukti;
    }

    public function prosesSub(Request $request)
    {
        $pros  = 'D';
        $cbg   = Auth::user()->CBG;
        $DEPT  = $request->SUB;
        $LPH1  = $request->LPH1;
        $SUPP1 = $request->SUPP1;
        $SUPP2 = $request->SUPP2;
        $LPH2  = $request->LPH2;
        $SUB1  = $request->SUB1;
        $SUB2  = $request->SUB2;
        $HARI  = $request->HARI;

        if ($pros === 'S') {
            $query = DB::select("
                SELECT ini.*, (totaltk + totalgd) AS saldo
                FROM (
                    SELECT
                        brg.sub, brg.KD_BRG, brg.NA_BRG, brg.KET_UK, brg.KET_KEM,
                        brg.supp AS kodes, brg.TYPE,
                        CASE
                            WHEN '$cbg' = 'TMM' THEN brg.sp_l
                            WHEN '$cbg' = 'SOP' THEN brg.sp_lf
                            ELSE ''
                        END AS spl,
                        brgdt.hb, brgdt.KDLAKU, brgdt.klk, brgdt.lph, brg.mo,
                        RIGHT(TRIM(brg.KET_KEM), LENGTH(TRIM(brg.KET_KEM)) - LOCATE('/', TRIM(brg.KET_KEM))) AS KEM,
                        IF(kk.totalpo IS NULL, 0, kk.totalpo) AS totalpo,
                        brgdt.AK00 AS totaltk, brgd.AK00 AS totalgd
                    FROM brg
                    JOIN brgd ON brg.KD_BRG = brgd.KD_BRG
                    JOIN brgdt ON brgd.KD_BRG = brgdt.KD_BRG
                    LEFT JOIN (
                        SELECT pod.KD_BRG, SUM(pod.qty) AS totalpo
                        FROM pod
                        JOIN po ON po.NO_BUKTI = pod.no_bukti AND po.cbg = '$cbg'
                        WHERE IF(
                                po.TKK3 <> '2001-01-01', po.TKK3,
                                IF(po.TKK2 <> '2001-01-01', po.TKK2, po.TKK1)
                            ) >= DATE(NOW())
                        GROUP BY KD_BRG
                    ) AS kk ON kk.kd_brg = brgdt.KD_BRG
                    WHERE
                        brgd.cbg = '$cbg' AND
                        brgdt.cbg = '$cbg' AND
                        brgdt.yer = YEAR(NOW()) AND
                        brgdt.kdlaku IN('0','1','4') AND
                        TRIM(brgdt.TD_OD) = '' AND
                        brg.supp BETWEEN '$SUPP1' AND '$SUPP2'
                ) AS ini
                WHERE
                    lph BETWEEN '$LPH1' AND '$LPH2' AND
                    sub BETWEEN '$SUB1' AND '$SUB2'
                ORDER BY kodes ASC, kd_brg ASC",
            );
        }

        // pros = D (Departement)
        elseif ($pros === 'D') {
            $query = DB::select("SELECT ini.*, totaltk + totalgd AS saldo
            FROM (
                SELECT
                    brg.sub, brg.KD_BRG, brg.NA_BRG, brg.KET_UK, brg.KET_KEM,
                    brg.supp AS kodes, brg.TYPE,
                    CASE
                        WHEN '$cbg' = 'TMM' THEN brg.sp_l
                        WHEN '$cbg' = 'SOP' THEN brg.sp_lf
                        ELSE ''
                    END AS spl,
                    brgdt.hb, brgdt.KDLAKU, brgdt.klk, brgdt.lph, brg.mo,
                    RIGHT(TRIM(brg.KET_KEM), LENGTH(TRIM(brg.KET_KEM)) - LOCATE('/', TRIM(brg.KET_KEM))) AS KEM,
                    IF(kk.totalpo IS NULL, 0, kk.totalpo) AS totalpo,
                    brgdt.AK00 AS totaltk, brgd.AK00 AS totalgd
                FROM brg
                JOIN brgd ON brg.KD_BRG = brgd.KD_BRG
                JOIN brgdt ON brgd.KD_BRG = brgdt.KD_BRG
                LEFT JOIN (
                    SELECT pod.KD_BRG, SUM(pod.qty) AS totalpo
                    FROM pod
                    JOIN po ON po.NO_BUKTI = pod.no_bukti AND po.cbg = '$cbg'
                    WHERE IF(
                            po.TKK3 <> '2001-01-01', po.TKK3,
                            IF(po.TKK2 <> '2001-01-01', po.TKK2, po.TKK1)
                        ) >= DATE(NOW())
                    GROUP BY KD_BRG
                ) AS kk ON kk.kd_brg = brgdt.KD_BRG
                WHERE
                    brgd.cbg = '$cbg' AND
                    brgdt.cbg = '$cbg' AND
                    brgdt.yer = YEAR(NOW()) AND
                    brgdt.kdlaku IN('0','1','4') AND
                    TRIM(brgdt.TD_OD) = ''
                    -- brg.type = 'D'
            ) AS ini
            WHERE
                lph BETWEEN '$LPH1' AND '$LPH2' AND
                    sub BETWEEN '$SUB1' AND '$SUB2'
            ORDER BY kodes ASC, kd_brg ASC"
            );
        }
        // dd($query);

        $hasil = [];

        foreach ($query as $row) {

            // hitung z1
            $z1 = ($row->lph * $HARI)
             - $row->totalpo
             - $row->saldo;

            // total SPO
            $spo = DB::selectOne("
            SELECT SUM(qty) AS totot
            FROM spo
            WHERE kd_brg = '$row->KD_BRG' AND cbg = '$cbg'"
            );

            $z1 -= ($spo->totot ?? 0);

            $z4 = intval($z1);
            $z2 = $z4 / $row->KEM;

            // ceiling
            $z3 = ceil($z2);

            // final qty
            $z1 = $z3 * $row->KEM;

            if ($z1 > 0) {

                // spl cocok atau cbg = TGZ
                if ($row->spl == $request->SPL || $cbg == 'TGZ') {

                    $hasil[] = [
                        'kd_brg'   => $row->KD_BRG,
                        'na_brg'   => $row->NA_BRG,
                        'kodes'    => $row->kodes,
                        'kemasan'  => $row->KET_KEM,
                        'ket_uk'   => $row->KET_UK,
                        'nkemasan' => $row->KEM,
                        'qty'      => $z1,
                        'notes'    => $z1,
                        'harga'    => $row->hb,
                        'total'    => $z1 * $row->hb,
                        'MO'       => $row->mo,
                        'lph'      => $row->lph,
                        'qtybrg'   => $row->saldo,
                        'klaku'    => $row->KDLAKU,
                        'qtypo'    => $row->totalpo,
                        'type'     => $row->TYPE,
                        'psn'      => $row->spl,
                    ];
                }
            }
        }

        // dd($hasil);
        return response()->json([
            'status' => 'OK',
            'data'   => $hasil,
        ]);
    }

}