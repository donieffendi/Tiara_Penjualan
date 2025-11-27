<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class TPembelianBedaHargaController extends Controller
{
    public function index(Request $request)
    {
        try {
            $judul = 'Transaksi Pembelian Beda Harga';

            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                return view("otransaksi_TPembelianBedaHarga.index")->with([
                    'judul' => $judul,
                    'error' => 'User tidak memiliki akses cabang (CBG). Hubungi administrator.'
                ]);
            }

            if (!$request->session()->has('periode')) {
                return view("otransaksi_TPembelianBedaHarga.index")->with([
                    'judul' => $judul,
                    'warning' => 'Periode belum diset. Silakan set periode terlebih dahulu.'
                ]);
            }

            $periode = $request->session()->get('periode');

            return view("otransaksi_TPembelianBedaHarga.index")->with([
                'judul' => $judul,
                'cbg' => $CBG,
                'periode' => $periode
            ]);
        } catch (\Exception $e) {
            Log::error('Error in TPembelianBedaHarga index: ' . $e->getMessage());
            return view("otransaksi_TPembelianBedaHarga.index")->with([
                'judul' => 'Transaksi Pembelian Beda Harga',
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    public function cari_data(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            $connection = strtolower($CBG);
            Log::info('=== TPembelianBedaHarga cari_data ===', [
                'CBG' => $CBG,
                'connection' => $connection
            ]);

            $periode = $request->session()->get('periode');
            if (!$periode) {
                return response()->json(['error' => 'Periode belum diset'], 400);
            }

            // Filter parameters
            $supDari = $request->input('sup_dari', '');
            $supSampai = $request->input('sup_sampai', 'ZZZ');
            $brgDari = $request->input('brg_dari', '');
            $brgSampai = $request->input('brg_sampai', 'ZZZ');
            $tanggal = $request->input('tanggal', date('Y-m-d'));
            $sortBy = $request->input('sort_by', 'supplier');

            // Query untuk mendapatkan data pembelian dengan perbedaan harga
            $query = "
                SELECT 
                    belid.no_id,
                    belid.no_bukti,
                    beli.tgl as tgl_beli,
                    beli.kodes as kd_supplier,
                    beli.namas as nama_supplier,
                    beli.notes,
                    belid.kd_brg,
                    belid.na_brg as nama_barang,
                    brg.ket_uk as ukuran,
                    belid.qty,
                    belid.harga as harga_beli,
                    supd2.harga as harga_supplier,
                    ROUND((
                        ((((belid.harga * (100 - belid.diskon1) / 100) * (100 - belid.diskon2) / 100) * (100 - belid.diskon3) / 100) * (100 - belid.ppn) / 100) - 
                        ((((supd2.harga * (100 - supd2.d1) / 100) * (100 - supd2.d2) / 100) * (100 - supd2.d3) / 100) * (100 - supd2.ppn) / 100)
                    ) * belid.qty, 2) as selisih_total,
                    belid.gol,
                    belid.diskon1,
                    belid.diskon2,
                    belid.diskon3,
                    belid.ppn as ppn_beli,
                    supd2.d1,
                    supd2.d2,
                    supd2.d3,
                    supd2.ppn as ppn_supplier
                FROM beli
                INNER JOIN belid ON beli.no_bukti = belid.no_bukti
                INNER JOIN supd2 ON supd2.supp = beli.kodes AND supd2.kd_brg = belid.kd_brg
                INNER JOIN brg ON belid.kd_brg = brg.kd_brg
                INNER JOIN sup ON beli.kodes = sup.kodes
                WHERE beli.flag = 'BL'
                AND belid.gol = '0'
                AND (
                    (
                        ROUND((((belid.harga * (100 - belid.diskon1) / 100) * (100 - belid.diskon2) / 100) * (100 - belid.diskon3) / 100) * (100 - belid.ppn) / 100, 2) - 
                        ROUND((((supd2.harga * (100 - supd2.d1) / 100) * (100 - supd2.d2) / 100) * (100 - supd2.d3) / 100) * (100 - supd2.ppn) / 100, 2)
                    ) > 1
                    OR
                    (
                        (
                            ROUND((((belid.harga * (100 - belid.diskon1) / 100) * (100 - belid.diskon2) / 100) * (100 - belid.diskon3) / 100) * (100 - belid.ppn) / 100, 2) - 
                            ROUND((((supd2.harga * (100 - supd2.d1) / 100) * (100 - supd2.d2) / 100) * (100 - supd2.d3) / 100) * (100 - supd2.ppn) / 100, 2)
                        ) > 20
                        AND belid.harga > 1000
                    )
                )
            ";

            // Add filters
            if ($supDari) {
                $query .= " AND beli.kodes >= :sup_dari";
            }
            if ($supSampai) {
                $query .= " AND beli.kodes <= :sup_sampai";
            }
            if ($brgDari) {
                $query .= " AND belid.kd_brg >= :brg_dari";
            }
            if ($brgSampai) {
                $query .= " AND belid.kd_brg <= :brg_sampai";
            }
            if ($tanggal) {
                $query .= " AND beli.tgl <= :tanggal";
            }

            // Add sorting
            switch ($sortBy) {
                case 'barang':
                    $query .= " ORDER BY belid.kd_brg ASC, beli.kodes ASC";
                    break;
                case 'selisih':
                    $query .= " ORDER BY selisih_total DESC";
                    break;
                default: // supplier
                    $query .= " ORDER BY beli.kodes ASC, belid.kd_brg ASC";
                    break;
            }

            // Bind parameters
            $bindings = [];
            if ($supDari) $bindings['sup_dari'] = $supDari;
            if ($supSampai) $bindings['sup_sampai'] = $supSampai;
            if ($brgDari) $bindings['brg_dari'] = $brgDari;
            if ($brgSampai) $bindings['brg_sampai'] = $brgSampai;
            if ($tanggal) $bindings['tanggal'] = $tanggal;

            $data = DB::connection($connection)->select($query, $bindings);

            Log::info('Query result count: ' . count($data));

            return Datatables::of(collect($data))
                ->addIndexColumn()
                ->editColumn('tgl_beli', function ($row) {
                    return date('d-m-Y', strtotime($row->tgl_beli));
                })
                ->editColumn('qty', function ($row) {
                    return number_format($row->qty, 0);
                })
                ->editColumn('harga_beli', function ($row) {
                    return number_format($row->harga_beli, 2);
                })
                ->editColumn('harga_supplier', function ($row) {
                    return number_format($row->harga_supplier, 2);
                })
                ->editColumn('selisih_total', function ($row) {
                    return number_format($row->selisih_total, 2);
                })
                ->addColumn('proses', function ($row) {
                    $checked = $row->gol == '1' ? 'checked' : '';
                    return '<input type="checkbox" class="chk-proses" data-id="' . $row->no_id . '" data-nobukti="' . $row->no_bukti . '" data-kdbrg="' . $row->kd_brg . '" ' . $checked . '>';
                })
                ->rawColumns(['proses'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('Error in cari_data: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function update_gol(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            $connection = strtolower($CBG);
            $noBukti = $request->input('no_bukti');
            $kdBrg = $request->input('kd_brg');
            $gol = $request->input('gol', '0');

            Log::info('=== TPembelianBedaHarga update_gol ===', [
                'CBG' => $CBG,
                'connection' => $connection,
                'no_bukti' => $noBukti,
                'kd_brg' => $kdBrg,
                'gol' => $gol
            ]);

            DB::connection($connection)->statement("
                UPDATE belid 
                SET gol = ? 
                WHERE no_bukti = ? 
                AND kd_brg = ?
            ", [$gol, $noBukti, $kdBrg]);

            return response()->json([
                'success' => true,
                'message' => 'Status berhasil diupdate'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in update_gol: ' . $e->getMessage());
            return response()->json([
                'error' => 'Update gagal: ' . $e->getMessage()
            ], 500);
        }
    }

    public function proses(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            $username = Auth::user()->username ?? 'system';

            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            $connection = strtolower($CBG);
            Log::info('=== TPembelianBedaHarga proses ===', [
                'user' => $username,
                'CBG' => $CBG,
                'connection' => $connection
            ]);

            $periode = $request->session()->get('periode');
            if (!$periode) {
                return response()->json(['error' => 'Periode belum diset'], 400);
            }

            DB::connection($connection)->beginTransaction();

            // Get distinct suppliers dengan data yang akan diproses (gol = '1')
            $suppliers = DB::connection($connection)->select("
                SELECT DISTINCT 
                    beli.kodes,
                    beli.namas,
                    beli.golongan
                FROM beli
                INNER JOIN belid ON beli.no_bukti = belid.no_bukti
                INNER JOIN supd2 ON supd2.supp = beli.kodes AND supd2.kd_brg = belid.kd_brg
                INNER JOIN brg ON belid.kd_brg = brg.kd_brg
                INNER JOIN sup ON beli.kodes = sup.kodes
                WHERE beli.flag = 'BL'
                AND belid.gol = '1'
                AND (
                    (
                        ROUND((((belid.harga * (100 - belid.diskon1) / 100) * (100 - belid.diskon2) / 100) * (100 - belid.diskon3) / 100) * (100 - belid.ppn) / 100, 2) - 
                        ROUND((((supd2.harga * (100 - supd2.d1) / 100) * (100 - supd2.d2) / 100) * (100 - supd2.d3) / 100) * (100 - supd2.ppn) / 100, 2)
                    ) > 1
                    OR
                    (
                        (
                            ROUND((((belid.harga * (100 - belid.diskon1) / 100) * (100 - belid.diskon2) / 100) * (100 - belid.diskon3) / 100) * (100 - belid.ppn) / 100, 2) - 
                            ROUND((((supd2.harga * (100 - supd2.d1) / 100) * (100 - supd2.d2) / 100) * (100 - supd2.d3) / 100) * (100 - supd2.ppn) / 100, 2)
                        ) > 20
                        AND belid.harga > 1000
                    )
                )
            ");

            $totalBukti = 0;
            $noBuktiList = [];

            foreach ($suppliers as $supplier) {
                // Generate nomor bukti baru
                $monthString = substr($periode, 0, 2);
                $yearString = substr($periode, -2);
                $kode = 'TH' . $yearString . $monthString;

                // Get nomor terakhir
                $noTrans = DB::connection($connection)->select("
                    SELECT NOM{$monthString} as no_bukti 
                    FROM notrans 
                    WHERE trans = 'THUT' 
                    AND per = ?
                ", [substr($periode, -4)]);

                $nomorBaru = ($noTrans[0]->no_bukti ?? 0) + 1;

                // Update nomor di notrans
                DB::connection($connection)->statement("
                    UPDATE notrans 
                    SET NOM{$monthString} = ? 
                    WHERE trans = 'THUT' 
                    AND per = ?
                ", [$nomorBaru, substr($periode, -4)]);

                $noBuktiBaru = $kode . '-' . str_pad($nomorBaru, 4, '0', STR_PAD_LEFT);

                // Get items untuk supplier ini
                $items = DB::connection($connection)->select("
                    SELECT 
                        belid.no_bukti,
                        belid.kd_brg,
                        belid.na_brg,
                        belid.qty,
                        belid.harga as harga_beli,
                        supd2.harga as harga_supplier,
                        ROUND((
                            ((((belid.harga * (100 - belid.diskon1) / 100) * (100 - belid.diskon2) / 100) * (100 - belid.diskon3) / 100) * (100 - belid.ppn) / 100) - 
                            ((((supd2.harga * (100 - supd2.d1) / 100) * (100 - supd2.d2) / 100) * (100 - supd2.d3) / 100) * (100 - supd2.ppn) / 100)
                        ) * belid.qty, 2) as selisih_total
                    FROM beli
                    INNER JOIN belid ON beli.no_bukti = belid.no_bukti
                    INNER JOIN supd2 ON supd2.supp = beli.kodes AND supd2.kd_brg = belid.kd_brg
                    INNER JOIN brg ON belid.kd_brg = brg.kd_brg
                    WHERE beli.kodes = ?
                    AND beli.flag = 'BL'
                    AND belid.gol = '1'
                    AND (
                        (
                            ROUND((((belid.harga * (100 - belid.diskon1) / 100) * (100 - belid.diskon2) / 100) * (100 - belid.diskon3) / 100) * (100 - belid.ppn) / 100, 2) - 
                            ROUND((((supd2.harga * (100 - supd2.d1) / 100) * (100 - supd2.d2) / 100) * (100 - supd2.d3) / 100) * (100 - supd2.ppn) / 100, 2)
                        ) > 1
                        OR
                        (
                            (
                                ROUND((((belid.harga * (100 - belid.diskon1) / 100) * (100 - belid.diskon2) / 100) * (100 - belid.diskon3) / 100) * (100 - belid.ppn) / 100, 2) - 
                                ROUND((((supd2.harga * (100 - supd2.d1) / 100) * (100 - supd2.d2) / 100) * (100 - supd2.d3) / 100) * (100 - supd2.ppn) / 100, 2)
                            ) > 20
                            AND belid.harga > 1000
                        )
                    )
                ", [$supplier->kodes]);

                if (count($items) == 0) continue;

                // Split items jika lebih dari 15 per bukti
                $chunks = array_chunk($items, 15);

                foreach ($chunks as $chunkIndex => $chunk) {
                    if ($chunkIndex > 0) {
                        // Generate nomor bukti baru untuk chunk berikutnya
                        $nomorBaru++;
                        DB::connection($connection)->statement("
                            UPDATE notrans 
                            SET NOM{$monthString} = ? 
                            WHERE trans = 'THUT' 
                            AND per = ?
                        ", [$nomorBaru, substr($periode, -4)]);
                        $noBuktiBaru = $kode . '-' . str_pad($nomorBaru, 4, '0', STR_PAD_LEFT);
                    }

                    // Insert header beli
                    DB::connection($connection)->statement("
                        INSERT INTO beli (
                            no_bukti, tgl, per, flag, flag2, 
                            kodes, namas, golongan, usrnm, tg_smp
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                    ", [
                        $noBuktiBaru,
                        date('Y-m-d'),
                        $periode,
                        'TH',
                        $CBG,
                        $supplier->kodes,
                        $supplier->namas,
                        $supplier->golongan,
                        $username
                    ]);

                    // Get ID beli yang baru dibuat
                    $beliId = DB::connection($connection)->select("
                        SELECT no_id 
                        FROM beli 
                        WHERE no_bukti = ?
                    ", [$noBuktiBaru]);

                    $idBeli = $beliId[0]->no_id;

                    // Insert detail
                    $rec = 1;
                    foreach ($chunk as $item) {
                        DB::connection($connection)->statement("
                            INSERT INTO belid (
                                no_bukti, rec, per, flag, 
                                kd_brg, na_brg, qty, id,
                                harga, harga_bl, total, bukti_bl
                            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ", [
                            $noBuktiBaru,
                            $rec,
                            $periode,
                            'TH',
                            $item->kd_brg,
                            $item->na_brg,
                            $item->qty,
                            $idBeli,
                            $item->harga_supplier,
                            $item->harga_beli,
                            $item->selisih_total * -1, // Negative karena ini pengurangan
                            $item->no_bukti
                        ]);
                        $rec++;
                    }

                    // Update total di header beli
                    DB::connection($connection)->statement("
                        UPDATE beli 
                        INNER JOIN (
                            SELECT no_bukti, SUM(total) as total, SUM(qty) as qty
                            FROM belid
                            WHERE no_bukti = ?
                            GROUP BY no_bukti
                        ) as detail ON beli.no_bukti = detail.no_bukti
                        SET beli.total = detail.total,
                            beli.nett = detail.total,
                            beli.qty = detail.qty,
                            beli.sisa = detail.total
                        WHERE beli.no_bukti = ?
                    ", [$noBuktiBaru, $noBuktiBaru]);

                    $noBuktiList[] = $noBuktiBaru;
                    $totalBukti++;
                }
            }

            DB::connection($connection)->commit();

            Log::info('Proses berhasil', [
                'total_bukti' => $totalBukti,
                'no_bukti_list' => $noBuktiList
            ]);

            return response()->json([
                'success' => true,
                'message' => "Proses berhasil! {$totalBukti} dokumen telah dibuat.",
                'data' => [
                    'total_bukti' => $totalBukti,
                    'no_bukti_list' => $noBuktiList
                ]
            ]);
        } catch (\Exception $e) {
            DB::connection(strtolower(Auth::user()->CBG ?? ''))->rollBack();
            Log::error('Error in proses: ' . $e->getMessage());
            return response()->json([
                'error' => 'Proses gagal: ' . $e->getMessage()
            ], 500);
        }
    }

    public function cetak(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            $connection = strtolower($CBG);
            Log::info('=== TPembelianBedaHarga cetak ===', [
                'CBG' => $CBG,
                'connection' => $connection
            ]);

            // Get nama toko
            $toko = DB::connection($connection)->select("
                SELECT na_toko 
                FROM toko 
                WHERE kode = ?
            ", [$CBG]);

            $namaToko = $toko[0]->na_toko ?? $CBG;

            // Filter parameters
            $supDari = $request->input('sup_dari', '');
            $supSampai = $request->input('sup_sampai', 'ZZZ');
            $brgDari = $request->input('brg_dari', '');
            $brgSampai = $request->input('brg_sampai', 'ZZZ');
            $tanggal = $request->input('tanggal', date('Y-m-d'));

            $query = "
                SELECT 
                    '{$namaToko}' as nama_toko,
                    belid.no_bukti,
                    beli.tgl as tgl_beli,
                    beli.kodes as kd_supplier,
                    beli.namas as nama_supplier,
                    sup.tlp_k as telepon,
                    beli.notes,
                    belid.kd_brg,
                    belid.na_brg as nama_barang,
                    brg.ket_uk as ukuran,
                    belid.qty,
                    belid.harga as harga_beli,
                    supd2.harga as harga_supplier,
                    ROUND((
                        ((((belid.harga * (100 - belid.diskon1) / 100) * (100 - belid.diskon2) / 100) * (100 - belid.diskon3) / 100) * (100 - belid.ppn) / 100) - 
                        ((((supd2.harga * (100 - supd2.d1) / 100) * (100 - supd2.d2) / 100) * (100 - supd2.d3) / 100) * (100 - supd2.ppn) / 100)
                    ) * belid.qty, 2) as selisih_total,
                    belid.diskon1,
                    belid.diskon2,
                    belid.diskon3,
                    belid.ppn as ppn_beli,
                    supd2.d1,
                    supd2.d2,
                    supd2.d3,
                    supd2.ppn as ppn_supplier
                FROM beli
                INNER JOIN belid ON beli.no_bukti = belid.no_bukti
                INNER JOIN supd2 ON supd2.supp = beli.kodes AND supd2.kd_brg = belid.kd_brg
                INNER JOIN brg ON belid.kd_brg = brg.kd_brg
                INNER JOIN sup ON beli.kodes = sup.kodes
                WHERE beli.flag = 'BL'
                AND belid.gol = '0'
                AND (
                    (
                        ROUND((((belid.harga * (100 - belid.diskon1) / 100) * (100 - belid.diskon2) / 100) * (100 - belid.diskon3) / 100) * (100 - belid.ppn) / 100, 2) - 
                        ROUND((((supd2.harga * (100 - supd2.d1) / 100) * (100 - supd2.d2) / 100) * (100 - supd2.d3) / 100) * (100 - supd2.ppn) / 100, 2)
                    ) > 1
                    OR
                    (
                        (
                            ROUND((((belid.harga * (100 - belid.diskon1) / 100) * (100 - belid.diskon2) / 100) * (100 - belid.diskon3) / 100) * (100 - belid.ppn) / 100, 2) - 
                            ROUND((((supd2.harga * (100 - supd2.d1) / 100) * (100 - supd2.d2) / 100) * (100 - supd2.d3) / 100) * (100 - supd2.ppn) / 100, 2)
                        ) > 20
                        AND belid.harga > 1000
                    )
                )
            ";

            // Add filters
            $bindings = [];
            if ($supDari) {
                $query .= " AND beli.kodes >= ?";
                $bindings[] = $supDari;
            }
            if ($supSampai) {
                $query .= " AND beli.kodes <= ?";
                $bindings[] = $supSampai;
            }
            if ($brgDari) {
                $query .= " AND belid.kd_brg >= ?";
                $bindings[] = $brgDari;
            }
            if ($brgSampai) {
                $query .= " AND belid.kd_brg <= ?";
                $bindings[] = $brgSampai;
            }
            if ($tanggal) {
                $query .= " AND beli.tgl <= ?";
                $bindings[] = $tanggal;
            }

            $query .= " ORDER BY beli.kodes ASC, belid.kd_brg ASC";

            $data = DB::connection($connection)->select($query, $bindings);

            Log::info('Cetak data count: ' . count($data));

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Error in cetak: ' . $e->getMessage());
            return response()->json([
                'error' => 'Cetak gagal: ' . $e->getMessage()
            ], 500);
        }
    }

    public function lookup_supplier(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            $connection = strtolower($CBG);
            Log::info('=== TPembelianBedaHarga lookup_supplier ===', [
                'CBG' => $CBG,
                'connection' => $connection
            ]);

            // Get daftar supplier dengan dynamic connection
            $suppliers = DB::connection($connection)->select("
                SELECT DISTINCT 
                    sup.kodes,
                    sup.namas,
                    sup.tlp_k,
                    sup.alamat
                FROM sup
                WHERE sup.kodes IS NOT NULL
                AND sup.kodes != ''
                ORDER BY sup.kodes ASC
                LIMIT 500
            ");

            Log::info('Supplier count: ' . count($suppliers));

            return response()->json([
                'success' => true,
                'data' => $suppliers
            ]);
        } catch (\Exception $e) {
            Log::error('Error in lookup_supplier: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return response()->json([
                'error' => 'Gagal memuat supplier: ' . $e->getMessage()
            ], 500);
        }
    }

    public function lookup_barang(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            $connection = strtolower($CBG);
            Log::info('=== TPembelianBedaHarga lookup_barang ===', [
                'CBG' => $CBG,
                'connection' => $connection
            ]);

            // Get daftar barang dengan dynamic connection
            $barang = DB::connection($connection)->select("
                SELECT 
                    brg.kd_brg,
                    brg.na_brg,
                    brg.ket_uk,
                    brg.satuan
                FROM brg
                WHERE brg.kd_brg IS NOT NULL
                AND brg.kd_brg != ''
                ORDER BY brg.kd_brg ASC
                LIMIT 1000
            ");

            Log::info('Barang count: ' . count($barang));

            return response()->json([
                'success' => true,
                'data' => $barang
            ]);
        } catch (\Exception $e) {
            Log::error('Error in lookup_barang: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return response()->json([
                'error' => 'Gagal memuat barang: ' . $e->getMessage()
            ], 500);
        }
    }
}
