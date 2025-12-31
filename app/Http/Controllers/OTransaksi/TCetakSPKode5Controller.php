<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PHPJasperXML;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use Yajra\DataTables\Facades\DataTables;

class TCetakSPKode5Controller extends Controller
{
    public $judul = 'Cetak SP Kode 5';
    public $FLAGZ = 'SPK5';

    public function index(Request $request)
    {
        try {
            if (! $request->session()->has('periode')) {
                return view("otransaksi_cetakspkode5.index")->with([
                    'judul'   => $this->judul,
                    'flagz'   => $this->FLAGZ,
                    'warning' => 'Periode belum diset. Silakan set periode terlebih dahulu.',
                ]);
            }

            $CBG = Auth::user()->CBG ?? null;
            if (! $CBG) {
                return view("otransaksi_cetakspkode5.index")->with([
                    'judul' => $this->judul,
                    'flagz' => $this->FLAGZ,
                    'error' => 'User tidak memiliki akses cabang (CBG). Hubungi administrator.',
                ]);
            }

            return view("otransaksi_cetakspkode5.index")->with([
                'judul' => $this->judul,
                'flagz' => $this->FLAGZ,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in TCetakSPKode5 index: ' . $e->getMessage());
            return view("otransaksi_cetakspkode5.index")->with([
                'judul' => $this->judul,
                'flagz' => $this->FLAGZ,
                'error' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ]);
        }
    }

    public function browse(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG;

            // Browse untuk No Bukti JL/BL
            if ($request->type == 'bukti') {
                $query = DB::table('spo')
                    ->select('no_bukti', 'tgl')
                    ->whereIn('flag', ['JL', 'BL'])
                    ->where('cbg', $CBG)
                    ->whereRaw("LEFT(kodes, 3) = '542'")
                    ->groupBy('no_bukti', 'tgl')
                    ->orderBy('no_bukti', 'desc')
                    ->get();

                return response()->json($query);
            }

            // Browse untuk Supplier
            if ($request->type == 'supplier') {
                $query = DB::table('sup')
                    ->select('kodes', 'namas')
                    ->whereRaw("LEFT(kodes, 3) = '542'")
                    ->orderBy('kodes')
                    ->get();

                return response()->json($query);
            }

            // Browse untuk Barang
            if ($request->type == 'barang') {
                $query = DB::table('brg')
                    ->join('brgdt', function ($join) use ($CBG) {
                        $join->on('brg.kd_brg', '=', 'brgdt.kd_brg')
                            ->where('brgdt.cbg', '=', $CBG)
                            ->whereRaw('brgdt.yer = YEAR(NOW())');
                    })
                    ->select(
                        'brg.kd_brg',
                        'brg.na_brg',
                        'brg.ket_kem',
                        'brg.sub',
                        'brg.kdbar',
                        'brg.supp as kodes',
                        'brg.type',
                        DB::raw("SUBSTRING(TRIM(brg.ket_kem), LOCATE('/', TRIM(brg.ket_kem)) + 1) as kemasan"),
                        'brgdt.kdlaku'
                    )
                    ->orderBy('brg.kd_brg')
                    ->get();

                return response()->json($query);
            }

            return response()->json([]);
        } catch (\Exception $e) {
            Log::error('Error in browse: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function getTCetakSPKode5Data(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG;

            $query = DB::table('spo')
                ->select(
                    'spo.no_id',
                    'spo.no_bukti',
                    'spo.tgl',
                    'spo.kd_brg',
                    'spo.na_brg',
                    'spo.ket_kem',
                    'spo.qty',
                    'spo.harga',
                    'spo.total',
                    'spo.kodes',
                    'spo.namas',
                    'spo.sub',
                    'spo.kdbar',
                    'spo.kdlaku',
                    'spo.kemasan',
                    'spo.type',
                    DB::raw("IF((spo.kdlaku='0' OR spo.kdlaku='1'), 'G', IF((spo.kdlaku='4' OR spo.kdlaku='5' OR spo.kdlaku='6'), 'T', '')) as klaku"),
                    DB::raw("0 as hps")
                )
                ->where('spo.flag', 'KS')
                ->where('spo.ket', 'KHUSUS')
                ->where('spo.cbg', $CBG)
                ->whereRaw("LEFT(spo.kodes, 3) = '542'")
                ->orderBy('spo.kd_brg');

            return Datatables::of($query)
                ->addIndexColumn()
                ->addColumn('tgl_format', function ($row) {
                    return Carbon::parse($row->tgl)->format('d-m-Y');
                })
                ->addColumn('action', function ($row) {
                    return '<input type="checkbox" class="chk-hapus" data-id="' . $row->no_id . '">';
                })
                ->rawColumns(['action'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('Error in getTCetakSPKode5Data: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $CBG      = Auth::user()->CBG;
            $USERNAME = Auth::user()->username;
            $data     = $request->data;

            foreach ($data as $row) {
                if (isset($row['hps']) && $row['hps'] == 1) {
                    // Insert to hist table before delete
                    DB::table('hist')->insert([
                        'tgltrans' => DB::raw('NOW()'),
                        'operator' => $USERNAME,
                        'kd_brg'   => $row['kd_brg'],
                        'sub'      => $row['sub'],
                        'kdbar'    => $row['kdbar'],
                        'qty'      => $row['qty'],
                        'kodtrans' => 'SPO',
                        'ket'      => 'Hps dari KS',
                        'waktu'    => DB::raw('NOW()'),
                        'gd'       => $row['klaku'],
                        'nobukti'  => $row['no_bukti'],
                    ]);

                    // Delete from spo
                    DB::table('spo')
                        ->where('no_id', $row['no_id'])
                        ->delete();
                } else {
                    // Hitung qty based on kemasan
                    $x1       = $row['qty'];
                    $kemasan  = $row['kemasan'] ?? 1;
                    $x2       = floor($x1 / $kemasan);
                    $x3       = $x2 * $kemasan;

                    if ($row['no_id'] == 0) {
                        // Insert new record (manual entry)
                        if (!empty($row['kd_brg'])) {
                            DB::table('spo')->insert([
                                'ket'      => 'KHUSUS',
                                'no_bukti' => $row['no_bukti'],
                                'tgl'      => $row['tgl'],
                                'kd_brg'   => $row['kd_brg'],
                                'na_brg'   => $row['na_brg'],
                                'kodes'    => $row['kodes'],
                                'namas'    => $row['namas'],
                                'qty'      => $row['qty'],
                                'harga'    => $row['harga'],
                                'total'    => $row['total'],
                                'flag'     => 'KS',
                                'sub'      => $row['sub'],
                                'kdbar'    => $row['kdbar'],
                                'ket_kem'  => $row['ket_kem'],
                                'kdlaku'   => $row['klaku'],
                                'tg_smp'   => DB::raw('NOW()'),
                                'kemasan'  => $kemasan,
                                'type'     => $row['type'],
                                'cbg'      => $CBG,
                            ]);
                        }
                    } else {
                        // Update existing record
                        DB::table('spo')
                            ->where('no_bukti', $row['no_bukti'])
                            ->where('kd_brg', $row['kd_brg'])
                            ->where('ket', 'KHUSUS')
                            ->update([
                                'tgl'     => $row['tgl'],
                                'ket_kem' => $row['ket_kem'],
                                'kdbar'   => $row['kdbar'],
                                'sub'     => $row['sub'],
                                'kdlaku'  => $row['klaku'],
                                'kodes'   => $row['kodes'],
                                'namas'   => $row['namas'],
                                'qty'     => $row['qty'],
                                'harga'   => $row['harga'],
                                'total'   => $row['total'],
                                'tg_smp'  => DB::raw('NOW()'),
                                'flag'    => 'KS',
                            ]);
                    }
                }
            }

            // Update sub, kdbar, kdlaku from brgdt
            DB::statement("
                UPDATE spo, brgdt
                SET spo.sub = LEFT(spo.kd_brg, 3),
                    spo.kdbar = RIGHT(spo.kd_brg, 4),
                    spo.kdlaku = brgdt.kdlaku
                WHERE spo.kd_brg = brgdt.kd_brg
                    AND spo.cbg = brgdt.cbg
                    AND spo.flag = 'KS'
                    AND spo.ket = 'KHUSUS'
            ");

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Data berhasil disimpan']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in store: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal menyimpan data: ' . $e->getMessage()], 500);
        }
    }

    public function proses(Request $request)
    {
        try {
            DB::beginTransaction();

            $CBG      = Auth::user()->CBG;
            $USERNAME = Auth::user()->username;
            $PERIODE  = session('periode.bulan') . '/' . session('periode.tahun');

            $toko = DB::table('toko')
                ->where('kode', $CBG)
                ->first();

            if (! $toko) {
                return response()->json(['error' => 'Data toko tidak ditemukan'], 400);
            }

            $tokoType = $toko->type;

            // Get data suppliers yang akan diproses (GROUP BY type, kodes)
            try {
                $suppliers = DB::select("
                SELECT *,
                    IF(HARI=1, TGLX + INTERVAL 1 DAY, TGLX) AS JTEMPO,
                    IF(HARIZ=7, TGLZ + INTERVAL 1 DAY, TGLZ) AS TKKS
                FROM (
                    SELECT spo.no_bukti, spo.TYPE as type, spo.tgl, spo.kodes, sup.namas,
                        DATE(NOW()) as tanggal,
                        IF(brgdt.KLK<'U', ASCII(brgdt.KLK)-64, (((ASCII(brgdt.KLK)-64-20)*5)+20)) AS KLX,
                        DAYOFWEEK(DATE(DATE_ADD(NOW(), INTERVAL (
                            IF(brgdt.KLK<'U', ASCII(brgdt.KLK)-64, (((ASCII(brgdt.KLK)-64-20)*5)+20))
                        ) DAY))) AS HARI,
                        DATE(DATE_ADD(NOW(), INTERVAL (
                            IF(brgdt.KLK<'U', ASCII(brgdt.KLK)-64, (((ASCII(brgdt.KLK)-64-20)*5)+20))
                        ) DAY)) AS TGLX,
                        DATE(DATE_ADD(NOW(), INTERVAL (
                            IF(brgdt.KLK<'U', ASCII(brgdt.KLK)-64, (((ASCII(brgdt.KLK)-64-20)*5)+20))
                            - sup.ORDR - (IF(brgdt.KLK<'T', 1, 4))
                        ) DAY)) AS TGLZ,
                        DAYOFWEEK(DATE(DATE_ADD(NOW(), INTERVAL (
                            IF(brgdt.KLK<'U', ASCII(brgdt.KLK)-64, (((ASCII(brgdt.KLK)-64-20)*5)+20))
                            - sup.ORDR - (IF(brgdt.KLK<'T', 1, 4))
                        ) DAY))) AS HARIZ,
                        brg.TYPE as tipe
                    FROM spo, sup, brg
                        LEFT JOIN brgdt ON brg.kd_brg = brgdt.kd_brg
                            AND brgdt.yer = YEAR(NOW())
                            AND brgdt.cbg = ?
                    WHERE spo.kodes = sup.kodes
                        AND spo.cbg = ?
                        AND spo.kd_brg = brg.kd_brg
                        AND LEFT(spo.kodes, 3) = '542'
                        AND spo.flag = 'KS'
                        AND spo.gol = '0'
                        AND spo.ket = 'KHUSUS'
                    GROUP BY spo.no_bukti, spo.TYPE, spo.tgl, spo.kodes, sup.namas, brg.TYPE, brgdt.KLK, sup.ORDR
                    ORDER BY brg.TYPE, spo.kodes
                ) AS UU
            ", [$CBG, $CBG]);
            } catch (\Exception $queryEx) {
                Log::error('Error executing supplier query: ' . $queryEx->getMessage());
                throw $queryEx;
            }

            if (count($suppliers) == 0) {
                return response()->json(['error' => 'Tidak ada data supplier yang akan diproses'], 400);
            }

            // Debug: Log query result
            Log::info('Suppliers found: ' . count($suppliers));
            foreach ($suppliers as $idx => $sup) {
                Log::info("Supplier $idx properties: " . implode(', ', array_keys(get_object_vars($sup))));
                Log::info("Supplier $idx values: no_bukti=" . ($sup->no_bukti ?? 'NULL') . ", kodes=" . ($sup->kodes ?? 'NULL'));
            }

            $listPO = [];

            foreach ($suppliers as $supplier) {
                // Safe property access with fallback
                $supplierType = $supplier->type ?? $supplier->TYPE ?? null;
                $supplierTipe = $supplier->tipe ?? $supplier->TIPE ?? null;
                
                if (!$supplierType || !$supplierTipe) {
                    Log::error('Missing type/tipe in supplier. Available properties: ' . implode(', ', array_keys(get_object_vars($supplier))));
                    continue; // Skip this supplier
                }
                
                // Generate nomor bukti PO
                $noBukti = $this->generateNoBukti($CBG, $PERIODE, $tokoType);

                // Insert ke tabel PO
                $idPO = DB::table('po')->insertGetId([
                    'notes'      => 'KHUSUS',
                    'no_bukti'   => $noBukti,
                    'tgl'        => date('Y-m-d'),
                    'tgo'        => date('Y-m-d', strtotime($supplier->tgl)),
                    'tgl_mulai'  => date('Y-m-d', strtotime($supplier->tgl)),
                    'per'        => $PERIODE,
                    'flag'       => 'PO',
                    'kodes'      => $supplier->kodes,
                    'namas'      => $supplier->namas,
                    'jtempo'     => date('Y-m-d', strtotime($supplier->JTEMPO)),
                    'tkk1'       => date('Y-m-d', strtotime($supplier->JTEMPO)),
                    'tkks'       => date('Y-m-d', strtotime($supplier->TKKS)),
                    'usrnm'      => $USERNAME,
                    'tg_smp'     => DB::raw('NOW()'),
                    'type'       => 'KS',
                    'golongan'   => $supplierTipe,
                    'cbg'        => $CBG,
                    'buktik'     => $supplier->no_bukti,
                ]);

                // Get detail barang untuk supplier ini
                // Cek apakah DCK untuk pakai harga diskon atau tidak
                if ($CBG == 'DCK') {
                    $details = DB::select("
                        SELECT spo.KD_BRG as kd_brg, spo.NA_BRG as na_brg, spo.QTY as qty, brg.TYPE as tipe,
                            ROUND((((brgdt.HB * (100 - brgdt.D1) / 100) * (100 - brgdt.D2) / 100))) AS HARGA,
                            spo.QTY * brg.hb as total, spo.KDLAKU as kdlaku
                        FROM brg, spo
                            LEFT JOIN brgdt ON brgdt.kd_brg = spo.KD_BRG
                        WHERE spo.KD_BRG = brg.kd_brg
                            AND brg.TYPE = ?
                            AND spo.kodes = ?
                            AND spo.flag = 'KS'
                            AND spo.cbg = ?
                            AND spo.ket = 'KHUSUS'
                        ORDER BY spo.kd_brg
                    ", [$supplierTipe, $supplier->kodes, $CBG]);
                } else {
                    $details = DB::select("
                        SELECT spo.KD_BRG as kd_brg, spo.NA_BRG as na_brg, spo.QTY as qty, brg.TYPE as tipe,
                            brg.hb as harga, spo.QTY * brg.hb as total, spo.KDLAKU as kdlaku
                        FROM spo, brg
                        WHERE spo.KD_BRG = brg.kd_brg
                            AND brg.TYPE = ?
                            AND spo.kodes = ?
                            AND spo.flag = 'KS'
                            AND spo.cbg = ?
                            AND spo.ket = 'KHUSUS'
                        ORDER BY spo.kd_brg
                    ", [$supplierTipe, $supplier->kodes, $CBG]);
                }

                $rec = 1;
                foreach ($details as $detail) {
                    // Jika rec > 15, buat PO baru
                    if ($rec > 15) {
                        // Generate nomor bukti PO baru
                        $noBukti = $this->generateNoBukti($CBG, $PERIODE, $tokoType);

                        // Insert PO baru
                        $idPO = DB::table('po')->insertGetId([
                            'notes'      => 'KHUSUS',
                            'no_bukti'   => $noBukti,
                            'tgl'        => date('Y-m-d'),
                            'tgo'        => date('Y-m-d', strtotime($supplier->tgl)),
                            'tgl_mulai'  => date('Y-m-d', strtotime($supplier->tgl)),
                            'per'        => $PERIODE,
                            'flag'       => 'PO',
                            'kodes'      => $supplier->kodes,
                            'namas'      => $supplier->namas,
                            'jtempo'     => date('Y-m-d', strtotime($supplier->JTEMPO)),
                            'tkk1'       => date('Y-m-d', strtotime($supplier->JTEMPO)),
                            'tkks'       => date('Y-m-d', strtotime($supplier->TKKS)),
                            'usrnm'      => $USERNAME,
                            'tg_smp'     => DB::raw('NOW()'),
                            'type'       => 'KS',
                            'golongan'   => $supplier->tipe,
                            'cbg'        => $CBG,
                            'buktik'     => $supplier->no_bukti,
                        ]);

                        $rec = 1;
                    }

                    // Insert detail ke POD
                    DB::table('pod')->insert([
                        'no_bukti' => $noBukti,
                        'rec'      => $rec,
                        'per'      => $PERIODE,
                        'flag'     => 'PO',
                        'kd_brg'   => trim($detail->kd_brg),
                        'na_brg'   => trim($detail->na_brg),
                        'qty'      => $detail->qty,
                        'sisa'     => $detail->qty,
                        'id'       => $idPO,
                        'harga'    => $detail->harga,
                        'total'    => $detail->total,
                        'type'     => 'KS',
                        'cbg'      => $CBG,
                        'kdlaku'   => trim($detail->kdlaku),
                    ]);

                    $rec++;
                }

                // Update total PO
                DB::statement("
                    UPDATE po, (
                        SELECT no_bukti, SUM(qty) as qty, SUM(total) as total
                        FROM pod
                        WHERE no_bukti = ?
                        GROUP BY no_bukti
                    ) as hero
                    SET po.total = hero.total, po.total_qty = hero.qty
                    WHERE po.no_bukti = hero.no_bukti
                ", [$noBukti]);

                $listPO[] = $noBukti;
            }

            // Hapus data dari SPO
            DB::table('spo')
                ->where('flag', 'KS')
                ->where('ket', 'KHUSUS')
                ->whereRaw("LEFT(kodes, 3) = '542'")
                ->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Proses berhasil. ' . count($listPO) . ' PO telah dibuat.',
                'po_list' => $listPO,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in proses: ' . $e->getMessage());
            return response()->json(['error' => 'Proses gagal: ' . $e->getMessage()], 500);
        }
    }

    private function generateNoBukti($CBG, $PERIODE, $tokoType)
    {
        $tahun = explode('/', $PERIODE)[1];

        $popo = 'NOM01';
        if ($tokoType == 'M') {
            $popo = 'NOM02';
        } elseif ($tokoType != 'Z') {
            $popo = 'NOM03';
        }

        do {
            $noTrans = DB::table('notrans')
                ->where('trans', 'PO')
                ->where('per', $tahun)
                ->first();

            $r1 = $noTrans->{strtolower($popo)} ?? 0;
            $r1++;

            DB::table('notrans')
                ->where('trans', 'PO')
                ->where('per', $tahun)
                ->update([
                    strtolower($popo) => ($r1 == 99999 ? 0 : $r1)
                ]);

            $noBukti = 'K' . $tokoType . str_pad($r1, 5, '0', STR_PAD_LEFT);

            // Cek apakah no bukti sudah ada
            $exists = DB::table('po')
                ->where('no_bukti', $noBukti)
                ->exists();
        } while ($exists);

        return $noBukti;
    }

    public function jasper(Request $request)
    {
        try {
            $CBG     = Auth::user()->CBG;
            $noBukti = $request->no_bukti;

            if (! $noBukti) {
                return redirect()->back()->with('error', 'No Bukti tidak boleh kosong');
            }

            $toko = DB::table('toko')
                ->where('kode', $CBG)
                ->first();

            $query = DB::select("
                SELECT
                    :nmtoko as nmtoko,
                    :alamatini as alamatini,
                    po.tgl,
                    po.no_bukti,
                    po.namas,
                    po.kodes,
                    sup.cat_sp,
                    sup.golongan,
                    sup.by_kr,
                    po.tkk1,
                    brg.item_uni,
                    sup.email,
                    brg.sub,
                    brg.kdbar,
                    brg.barcode as brcd,
                    pod.kdlaku,
                    pod.na_brg,
                    brg.ket_kem,
                    brg.ket_uk,
                    sup.kota as kotanya,
                    pod.qty / (SUBSTRING(TRIM(brg.ket_kem), LOCATE('/', TRIM(brg.ket_kem)) + 1)) AS kem,
                    pod.qty,
                    IF(LEFT(brg.barcode, 3) <> LEFT(brg.kd_brg, 3), '///',
                        IF(sup.s_bar = 'Y', 'V', IF(sup.s_bar = 'T', '&', 'X'))
                    ) as cod
                FROM po, pod, sup, brg
                WHERE po.no_bukti = pod.no_bukti
                    AND po.kodes = sup.kodes
                    AND pod.kd_brg = brg.kd_brg
                    AND po.no_bukti = :bkt1
                ORDER BY po.no_bukti ASC, pod.kd_brg ASC
            ", [
                'nmtoko'    => $toko->na_toko,
                'alamatini' => $toko->alamat,
                'bkt1'      => $noBukti,
            ]);

            $data = [];
            foreach ($query as $row) {
                $data[] = [
                    'nmtoko'    => $row->nmtoko,
                    'alamatini' => $row->alamatini,
                    'tgl'       => Carbon::parse($row->tgl)->format('d-m-Y'),
                    'no_bukti'  => $row->no_bukti,
                    'namas'     => $row->namas,
                    'kodes'     => $row->kodes,
                    'cat_sp'    => $row->cat_sp,
                    'golongan'  => $row->golongan,
                    'by_kr'     => $row->by_kr,
                    'tkk1'      => Carbon::parse($row->tkk1)->format('d-m-Y'),
                    'item_uni'  => $row->item_uni,
                    'sub'       => $row->sub,
                    'kdbar'     => $row->kdbar,
                    'brcd'      => $row->brcd,
                    'kdlaku'    => $row->kdlaku,
                    'na_brg'    => $row->na_brg,
                    'ket_kem'   => $row->ket_kem,
                    'ket_uk'    => $row->ket_uk,
                    'kotanya'   => $row->kotanya,
                    'kem'       => number_format($row->kem, 0),
                    'qty'       => number_format($row->qty, 0),
                    'cod'       => $row->cod,
                ];
            }

            $PHPJasperXML = new PHPJasperXML();
            $PHPJasperXML->load_xml_file(base_path() . '/app/reportc01/phpjasperxml/cetak_sp_kode5.jrxml');
            $PHPJasperXML->setData($data);
            ob_end_clean();
            $PHPJasperXML->outpage("I");
            exit;
        } catch (\Exception $e) {
            Log::error('Error in jasper: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal generate laporan: ' . $e->getMessage());
        }
    }

    public function prosesBukti(Request $request)
    {
        try {
            $bukti = trim($request->bukti);
            $cbg   = Auth::user()->CBG;

            if (empty($bukti)) {
                return response()->json(['error' => 'Bukti tidak boleh kosong'], 400);
            }

            // 1. Cek apakah data exists
            $data = DB::table('spo')
                ->select('no_bukti', 'kd_brg', 'na_brg', 'qty')
                ->where('no_bukti', $bukti)
                ->whereIn('flag', ['JL', 'BL'])
                ->whereRaw("LEFT(kodes, 3) = '542'")
                ->where('cbg', $cbg)
                ->orderBy('kd_brg')
                ->orderBy('no_bukti')
                ->get();

            if ($data->count() === 0) {
                return response()->json([
                    'status'    => 'not_found',
                    'message'   => 'Data tidak ditemukan. Lihat list datanya?',
                    'open_list' => true,
                ]);
            }

            // 2. Update flag dari JL/BL ke KS
            DB::table('spo')
                ->where('no_bukti', $bukti)
                ->whereIn('flag', ['JL', 'BL'])
                ->whereRaw("LEFT(kodes, 3) = '542'")
                ->update([
                    'flag' => 'KS',
                    'ket'  => 'KHUSUS',
                ]);

            // 3. Update kdlaku dan klk dari brgdt
            DB::statement("
                UPDATE spo, brgdt
                SET spo.kdlaku = brgdt.kdlaku,
                    spo.klk = brgdt.klk
                WHERE spo.kd_brg = brgdt.kd_brg
                    AND spo.no_bukti = ?
                    AND (spo.flag = 'JL' OR spo.flag = 'BL' OR spo.flag = 'KS')
                    AND LEFT(spo.kodes, 3) = '542'
            ", [$bukti]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Data berhasil diproses. ' . $data->count() . ' item telah dimuat.',
                'count'   => $data->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error in prosesBukti: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal memproses: ' . $e->getMessage()], 500);
        }
    }
}
