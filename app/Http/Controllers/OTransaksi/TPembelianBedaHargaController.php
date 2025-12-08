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

            Log::info('=== TPembelianBedaHarga cari_data ===', [
                'CBG' => $CBG
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
                    belid.NO_ID as no_id,
                    belid.NO_BUKTI as no_bukti,
                    beli.TGL as tgl_beli,
                    beli.KODES as kd_supplier,
                    beli.NAMAS as nama_supplier,
                    beli.NOTES as notes,
                    belid.KD_BRG as kd_brg,
                    belid.NA_BRG as nama_barang,
                    brg.ket_uk as ukuran,
                    belid.QTY as qty,
                    belid.HARGA as harga_beli,
                    supd2.HARGA as harga_supplier,
                    ROUND((
                        ((((belid.HARGA * (100 - belid.DISKON1) / 100) * (100 - belid.DISKON2) / 100) * (100 - belid.DISKON3) / 100) * (100 - belid.PPN) / 100) - 
                        ((((supd2.HARGA * (100 - supd2.D1) / 100) * (100 - supd2.D2) / 100) * (100 - supd2.D3) / 100) * (100 - supd2.PPN) / 100)
                    ) * belid.QTY, 2) as selisih_total,
                    belid.GOL as gol,
                    belid.DISKON1 as diskon1,
                    belid.DISKON2 as diskon2,
                    belid.DISKON3 as diskon3,
                    belid.PPN as ppn_beli,
                    supd2.D1 as d1,
                    supd2.D2 as d2,
                    supd2.D3 as d3,
                    supd2.PPN as ppn_supplier
                FROM beli
                INNER JOIN belid ON beli.NO_BUKTI = belid.NO_BUKTI
                INNER JOIN supd2 ON supd2.SUPP = beli.KODES AND supd2.KD_BRG = belid.KD_BRG
                INNER JOIN brg ON belid.KD_BRG = brg.kd_brg
                INNER JOIN sup ON beli.KODES = sup.KODES
                WHERE beli.FLAG = 'BL'
                AND belid.GOL = '0'
                AND (
                    ABS(
                        ROUND((((belid.HARGA * (100 - belid.DISKON1) / 100) * (100 - belid.DISKON2) / 100) * (100 - belid.DISKON3) / 100) * (100 - belid.PPN) / 100, 2) - 
                        ROUND((((supd2.HARGA * (100 - supd2.D1) / 100) * (100 - supd2.D2) / 100) * (100 - supd2.D3) / 100) * (100 - supd2.PPN) / 100, 2)
                    ) > 1
                    OR
                    (
                        ABS(
                            ROUND((((belid.HARGA * (100 - belid.DISKON1) / 100) * (100 - belid.DISKON2) / 100) * (100 - belid.DISKON3) / 100) * (100 - belid.PPN) / 100, 2) - 
                            ROUND((((supd2.HARGA * (100 - supd2.D1) / 100) * (100 - supd2.D2) / 100) * (100 - supd2.D3) / 100) * (100 - supd2.PPN) / 100, 2)
                        ) > 20
                        AND belid.HARGA > 1000
                    )
                )
            ";

            // Add filters
            if ($supDari) {
                $query .= " AND beli.KODES >= :sup_dari";
            }
            if ($supSampai) {
                $query .= " AND beli.KODES <= :sup_sampai";
            }
            if ($brgDari) {
                $query .= " AND belid.KD_BRG >= :brg_dari";
            }
            if ($brgSampai) {
                $query .= " AND belid.KD_BRG <= :brg_sampai";
            }
            if ($tanggal) {
                $query .= " AND beli.TGL <= :tanggal";
            }

            // Add sorting
            switch ($sortBy) {
                case 'barang':
                    $query .= " ORDER BY belid.KD_BRG ASC, beli.KODES ASC";
                    break;
                case 'selisih':
                    $query .= " ORDER BY selisih_total DESC";
                    break;
                default: // supplier
                    $query .= " ORDER BY beli.KODES ASC, belid.KD_BRG ASC";
                    break;
            }

            // Bind parameters
            $bindings = [];
            if ($supDari) $bindings['sup_dari'] = $supDari;
            if ($supSampai) $bindings['sup_sampai'] = $supSampai;
            if ($brgDari) $bindings['brg_dari'] = $brgDari;
            if ($brgSampai) $bindings['brg_sampai'] = $brgSampai;
            if ($tanggal) $bindings['tanggal'] = $tanggal;

            $data = DB::select($query, $bindings);

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

            $noBukti = $request->input('no_bukti');
            $kdBrg = $request->input('kd_brg');
            $gol = $request->input('gol', '0');

            Log::info('=== TPembelianBedaHarga update_gol ===', [
                'CBG' => $CBG,
                'no_bukti' => $noBukti,
                'kd_brg' => $kdBrg,
                'gol' => $gol
            ]);

            DB::statement("
                UPDATE belid 
                SET GOL = ? 
                WHERE NO_BUKTI = ? 
                AND KD_BRG = ?
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

            Log::info('=== TPembelianBedaHarga proses ===', [
                'user' => $username,
                'CBG' => $CBG
            ]);

            $periode = $request->session()->get('periode');
            if (!$periode) {
                return response()->json(['error' => 'Periode belum diset'], 400);
            }

            DB::beginTransaction();

            // Get distinct suppliers dengan data yang akan diproses (gol = '1')
            $suppliers = DB::select("
                SELECT DISTINCT 
                    beli.KODES as kodes,
                    beli.NAMAS as namas,
                    beli.GOLONGAN as golongan
                FROM beli
                INNER JOIN belid ON beli.NO_BUKTI = belid.NO_BUKTI
                INNER JOIN supd2 ON supd2.SUPP = beli.KODES AND supd2.KD_BRG = belid.KD_BRG
                INNER JOIN brg ON belid.KD_BRG = brg.kd_brg
                INNER JOIN sup ON beli.KODES = sup.KODES
                WHERE beli.FLAG = 'BL'
                AND belid.GOL = '1'
                AND (
                    ABS(
                        ROUND((((belid.HARGA * (100 - belid.DISKON1) / 100) * (100 - belid.DISKON2) / 100) * (100 - belid.DISKON3) / 100) * (100 - belid.PPN) / 100, 2) - 
                        ROUND((((supd2.HARGA * (100 - supd2.D1) / 100) * (100 - supd2.D2) / 100) * (100 - supd2.D3) / 100) * (100 - supd2.PPN) / 100, 2)
                    ) > 1
                    OR
                    (
                        ABS(
                            ROUND((((belid.HARGA * (100 - belid.DISKON1) / 100) * (100 - belid.DISKON2) / 100) * (100 - belid.DISKON3) / 100) * (100 - belid.PPN) / 100, 2) - 
                            ROUND((((supd2.HARGA * (100 - supd2.D1) / 100) * (100 - supd2.D2) / 100) * (100 - supd2.D3) / 100) * (100 - supd2.PPN) / 100, 2)
                        ) > 20
                        AND belid.HARGA > 1000
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
                $noTrans = DB::select("
                    SELECT NOM{$monthString} as no_bukti 
                    FROM notrans 
                    WHERE trans = 'THUT' 
                    AND per = ?
                ", [substr($periode, -4)]);

                $nomorBaru = ($noTrans[0]->no_bukti ?? 0) + 1;

                // Update nomor di notrans
                DB::statement("
                    UPDATE notrans 
                    SET NOM{$monthString} = ? 
                    WHERE trans = 'THUT' 
                    AND per = ?
                ", [$nomorBaru, substr($periode, -4)]);

                $noBuktiBaru = $kode . '-' . str_pad($nomorBaru, 4, '0', STR_PAD_LEFT);

                // Get items untuk supplier ini
                $items = DB::select("
                    SELECT 
                        belid.NO_BUKTI as no_bukti,
                        belid.KD_BRG as kd_brg,
                        belid.NA_BRG as na_brg,
                        belid.QTY as qty,
                        belid.HARGA as harga_beli,
                        supd2.HARGA as harga_supplier,
                        ROUND((
                            ((((belid.HARGA * (100 - belid.DISKON1) / 100) * (100 - belid.DISKON2) / 100) * (100 - belid.DISKON3) / 100) * (100 - belid.PPN) / 100) - 
                            ((((supd2.HARGA * (100 - supd2.D1) / 100) * (100 - supd2.D2) / 100) * (100 - supd2.D3) / 100) * (100 - supd2.PPN) / 100)
                        ) * belid.QTY, 2) as selisih_total
                    FROM beli
                    INNER JOIN belid ON beli.NO_BUKTI = belid.NO_BUKTI
                    INNER JOIN supd2 ON supd2.SUPP = beli.KODES AND supd2.KD_BRG = belid.KD_BRG
                    INNER JOIN brg ON belid.KD_BRG = brg.kd_brg
                    WHERE beli.KODES = ?
                    AND beli.FLAG = 'BL'
                    AND belid.GOL = '1'
                    AND (
                        (
                            ROUND((((belid.HARGA * (100 - belid.DISKON1) / 100) * (100 - belid.DISKON2) / 100) * (100 - belid.DISKON3) / 100) * (100 - belid.PPN) / 100, 2) - 
                            ROUND((((supd2.HARGA * (100 - supd2.D1) / 100) * (100 - supd2.D2) / 100) * (100 - supd2.D3) / 100) * (100 - supd2.PPN) / 100, 2)
                        ) > 1
                        OR
                        (
                            (
                                ROUND((((belid.HARGA * (100 - belid.DISKON1) / 100) * (100 - belid.DISKON2) / 100) * (100 - belid.DISKON3) / 100) * (100 - belid.PPN) / 100, 2) - 
                                ROUND((((supd2.HARGA * (100 - supd2.D1) / 100) * (100 - supd2.D2) / 100) * (100 - supd2.D3) / 100) * (100 - supd2.PPN) / 100, 2)
                            ) > 20
                            AND belid.HARGA > 1000
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
                        DB::statement("
                            UPDATE notrans 
                            SET NOM{$monthString} = ? 
                            WHERE trans = 'THUT' 
                            AND per = ?
                        ", [$nomorBaru, substr($periode, -4)]);
                        $noBuktiBaru = $kode . '-' . str_pad($nomorBaru, 4, '0', STR_PAD_LEFT);
                    }

                    // Insert header beli
                    DB::statement("
                        INSERT INTO beli (
                            NO_BUKTI, TGL, PER, FLAG, FLAG2, 
                            KODES, NAMAS, GOLONGAN, USRNM, TG_SMP
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
                    $beliId = DB::select("
                        SELECT NO_ID as no_id 
                        FROM beli 
                        WHERE NO_BUKTI = ?
                    ", [$noBuktiBaru]);

                    $idBeli = $beliId[0]->no_id;

                    // Insert detail
                    $rec = 1;
                    foreach ($chunk as $item) {
                        DB::statement("
                            INSERT INTO belid (
                                NO_BUKTI, REC, PER, FLAG, 
                                KD_BRG, NA_BRG, QTY, ID,
                                HARGA, HARGA_BL, TOTAL, BUKTI_BL
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
                    DB::statement("
                        UPDATE beli 
                        INNER JOIN (
                            SELECT NO_BUKTI, SUM(TOTAL) as total, SUM(QTY) as qty
                            FROM belid
                            WHERE NO_BUKTI = ?
                            GROUP BY NO_BUKTI
                        ) as detail ON beli.NO_BUKTI = detail.NO_BUKTI
                        SET beli.TOTAL = detail.total,
                            beli.NETT = detail.total,
                            beli.QTY = detail.qty,
                            beli.SISA = detail.total
                        WHERE beli.NO_BUKTI = ?
                    ", [$noBuktiBaru, $noBuktiBaru]);

                    $noBuktiList[] = $noBuktiBaru;
                    $totalBukti++;
                }
            }

            DB::commit();

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
            DB::rollBack();
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

            Log::info('=== TPembelianBedaHarga cetak ===', [
                'CBG' => $CBG
            ]);

            // Get nama toko
            $toko = DB::select("
                SELECT NA_TOKO as na_toko 
                FROM toko 
                WHERE KODE = ?
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
                    belid.NO_BUKTI as no_bukti,
                    beli.TGL as tgl_beli,
                    beli.KODES as kd_supplier,
                    beli.NAMAS as nama_supplier,
                    sup.TLP_K as telepon,
                    beli.NOTES as notes,
                    belid.KD_BRG as kd_brg,
                    belid.NA_BRG as nama_barang,
                    brg.ket_uk as ukuran,
                    belid.QTY as qty,
                    belid.HARGA as harga_beli,
                    supd2.HARGA as harga_supplier,
                    ROUND((
                        ((((belid.HARGA * (100 - belid.DISKON1) / 100) * (100 - belid.DISKON2) / 100) * (100 - belid.DISKON3) / 100) * (100 - belid.PPN) / 100) - 
                        ((((supd2.HARGA * (100 - supd2.D1) / 100) * (100 - supd2.D2) / 100) * (100 - supd2.D3) / 100) * (100 - supd2.PPN) / 100)
                    ) * belid.QTY, 2) as selisih_total,
                    belid.DISKON1 as diskon1,
                    belid.DISKON2 as diskon2,
                    belid.DISKON3 as diskon3,
                    belid.PPN as ppn_beli,
                    supd2.D1 as d1,
                    supd2.D2 as d2,
                    supd2.D3 as d3,
                    supd2.PPN as ppn_supplier
                FROM beli
                INNER JOIN belid ON beli.NO_BUKTI = belid.NO_BUKTI
                INNER JOIN supd2 ON supd2.SUPP = beli.KODES AND supd2.KD_BRG = belid.KD_BRG
                INNER JOIN brg ON belid.KD_BRG = brg.kd_brg
                INNER JOIN sup ON beli.KODES = sup.KODES
                WHERE beli.FLAG = 'BL'
                AND belid.GOL = '0'
                AND (
                    ABS(
                        ROUND((((belid.HARGA * (100 - belid.DISKON1) / 100) * (100 - belid.DISKON2) / 100) * (100 - belid.DISKON3) / 100) * (100 - belid.PPN) / 100, 2) - 
                        ROUND((((supd2.HARGA * (100 - supd2.D1) / 100) * (100 - supd2.D2) / 100) * (100 - supd2.D3) / 100) * (100 - supd2.PPN) / 100, 2)
                    ) > 1
                    OR
                    (
                        ABS(
                            ROUND((((belid.HARGA * (100 - belid.DISKON1) / 100) * (100 - belid.DISKON2) / 100) * (100 - belid.DISKON3) / 100) * (100 - belid.PPN) / 100, 2) - 
                            ROUND((((supd2.HARGA * (100 - supd2.D1) / 100) * (100 - supd2.D2) / 100) * (100 - supd2.D3) / 100) * (100 - supd2.PPN) / 100, 2)
                        ) > 20
                        AND belid.HARGA > 1000
                    )
                )
            ";

            // Add filters
            $bindings = [];
            if ($supDari) {
                $query .= " AND beli.KODES >= ?";
                $bindings[] = $supDari;
            }
            if ($supSampai) {
                $query .= " AND beli.KODES <= ?";
                $bindings[] = $supSampai;
            }
            if ($brgDari) {
                $query .= " AND belid.KD_BRG >= ?";
                $bindings[] = $brgDari;
            }
            if ($brgSampai) {
                $query .= " AND belid.KD_BRG <= ?";
                $bindings[] = $brgSampai;
            }
            if ($tanggal) {
                $query .= " AND beli.TGL <= ?";
                $bindings[] = $tanggal;
            }

            $query .= " ORDER BY beli.KODES ASC, belid.KD_BRG ASC";

            $data = DB::select($query, $bindings);

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

            Log::info('=== TPembelianBedaHarga lookup_supplier ===', [
                'CBG' => $CBG
            ]);

            // Get daftar supplier - gunakan database default dengan filter cbg jika ada
            $suppliers = DB::select("
                SELECT DISTINCT 
                    sup.KODES as kodes,
                    sup.NAMAS as namas,
                    sup.TLP_K as tlp_k,
                    sup.ALMT_K as alamat
                FROM sup
                WHERE sup.KODES IS NOT NULL
                AND sup.KODES != ''
                ORDER BY sup.KODES ASC
                LIMIT 500
            ");

            Log::info('Supplier count: ' . count($suppliers), [
                'sample' => count($suppliers) > 0 ? $suppliers[0] : null
            ]);

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

            Log::info('=== TPembelianBedaHarga lookup_barang ===', [
                'CBG' => $CBG
            ]);

            // Get daftar barang - gunakan database default
            $barang = DB::select("
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
