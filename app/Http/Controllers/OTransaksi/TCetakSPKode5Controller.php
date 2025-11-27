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
                $query = DB::connection($CBG)->table('spo')
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
                $query = DB::connection($CBG)->table('sup')
                    ->select('kodes', 'namas')
                    ->whereRaw("LEFT(kodes, 3) = '542'")
                    ->orderBy('kodes')
                    ->get();

                return response()->json($query);
            }

            // Browse untuk Barang
            if ($request->type == 'barang') {
                $query = DB::connection($CBG)
                    ->table('brg')
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

            $query = DB::connection($CBG)
                ->table('spo')
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
                    // Insert ke history
                    DB::connection($CBG)->table('hist')->insert([
                        'tgltrans' => now(),
                        'operator' => $USERNAME,
                        'kd_brg'   => $row['kd_brg'],
                        'sub'      => $row['sub'],
                        'kdbar'    => $row['kdbar'],
                        'qty'      => $row['qty'],
                        'kodtrans' => 'SPO',
                        'ket'      => 'Hps dari KS',
                        'waktu'    => now(),
                        'gd'       => $row['klaku'],
                        'nobukti'  => $row['no_bukti'],
                    ]);

                    // Hapus dari spo
                    DB::connection($CBG)->table('spo')
                        ->where('no_id', $row['no_id'])
                        ->delete();
                } else {
                    if ($row['no_id'] == 0) {
                        // Insert baru
                        if (! empty($row['kd_brg'])) {
                            DB::connection($CBG)->table('spo')->insert([
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
                                'tg_smp'   => now(),
                                'kemasan'  => $row['kemasan'],
                                'type'     => $row['type'],
                                'cbg'      => $CBG,
                            ]);
                        }
                    } else {
                        // Update existing
                        DB::connection($CBG)->table('spo')
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
                                'tg_smp'  => now(),
                                'flag'    => 'KS',
                            ]);
                    }
                }
            }

            // Update sub, kdbar, kdlaku
            DB::connection($CBG)->statement("
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

    public function loadFromBukti(Request $request)
    {
        try {
            $CBG     = Auth::user()->CBG;
            $noBukti = $request->no_bukti;

            // Update flag dari JL/BL ke KS
            DB::connection($CBG)->table('spo')
                ->where('no_bukti', $noBukti)
                ->whereIn('flag', ['JL', 'BL'])
                ->whereRaw("LEFT(kodes, 3) = '542'")
                ->update([
                    'flag' => 'KS',
                    'ket'  => 'KHUSUS',
                ]);

            // Update kdlaku dan klk
            DB::connection($CBG)->statement("
                UPDATE spo, brgdt
                SET spo.kdlaku = brgdt.kdlaku,
                    spo.klk = brgdt.klk
                WHERE spo.kd_brg = brgdt.kd_brg
                    AND spo.no_bukti = ?
                    AND spo.flag IN ('JL', 'BL')
                    AND LEFT(spo.kodes, 3) = '542'
            ", [$noBukti]);

            return response()->json(['success' => true, 'message' => 'Data berhasil dimuat']);
        } catch (\Exception $e) {
            Log::error('Error in loadFromBukti: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal memuat data: ' . $e->getMessage()], 500);
        }
    }

    public function proses(Request $request)
    {
        try {
            DB::beginTransaction();

            $CBG      = Auth::user()->CBG;
            $USERNAME = Auth::user()->username;
            $PERIODE  = session('periode.bulan') . '/' . session('periode.tahun');

            $toko = DB::connection($CBG)->table('toko')
                ->where('kode', $CBG)
                ->first();

            if (! $toko) {
                return response()->json(['error' => 'Data toko tidak ditemukan'], 400);
            }

            $tokoNama   = $toko->na_toko;
            $tokoAlamat = $toko->alamat;
            $tokoType   = $toko->type;

            // Get data supplier yang akan diproses
            $suppliers = DB::connection($CBG)
                ->table('spo')
                ->join('sup', 'spo.kodes', '=', 'sup.kodes')
                ->join('brg', 'spo.kd_brg', '=', 'brg.kd_brg')
                ->leftJoin('brgdt', function ($join) use ($CBG) {
                    $join->on('brg.kd_brg', '=', 'brgdt.kd_brg')
                        ->where('brgdt.yer', DB::raw('YEAR(NOW())'))
                        ->where('brgdt.cbg', $CBG);
                })
                ->select(
                    'spo.no_bukti',
                    'spo.type',
                    'spo.tgl',
                    'spo.kodes',
                    'sup.namas',
                    'brg.type as tipe',
                    DB::raw("DATE(NOW()) as tanggal"),
                    DB::raw("IF(brgdt.klk < 'U', ASCII(brgdt.klk) - 64, (((ASCII(brgdt.klk) - 64 - 20) * 5) + 20)) as KLX"),
                    DB::raw("DAYOFWEEK(DATE(DATE_ADD(NOW(), INTERVAL (SELECT KLX) DAY))) as HARI"),
                    DB::raw("DATE(DATE_ADD(NOW(), INTERVAL (SELECT KLX) DAY)) as TGLX"),
                    DB::raw("DATE(DATE_ADD(NOW(), INTERVAL (SELECT KLX) - sup.ORDR - (IF(brgdt.klk < 'T', 1, 4)) DAY)) as TGLZ"),
                    DB::raw("DAYOFWEEK(DATE(DATE_ADD(NOW(), INTERVAL (SELECT KLX) - sup.ORDR - (IF(brgdt.klk < 'T', 1, 4)) DAY))) as HARIZ"),
                    DB::raw("IF(HARI = 1, TGLX + INTERVAL 1 DAY, TGLX) as JTEMPO"),
                    DB::raw("IF(HARIZ = 7, TGLZ + INTERVAL 1 DAY, TGLZ) as TKKS")
                )
                ->where('spo.kodes', 'sup.kodes')
                ->where('spo.cbg', $CBG)
                ->where('spo.kd_brg', 'brg.kd_brg')
                ->whereRaw("LEFT(spo.kodes, 3) = '542'")
                ->where('spo.flag', 'KS')
                ->where('spo.gol', '0')
                ->where('spo.ket', 'KHUSUS')
                ->groupBy('brg.type', 'kodes')
                ->orderBy('brg.type')
                ->orderBy('kodes')
                ->get();

            if ($suppliers->count() == 0) {
                return response()->json(['error' => 'Tidak ada data supplier yang akan diproses'], 400);
            }

            $listPO = [];

            foreach ($suppliers as $supplier) {
                // Generate nomor bukti PO
                $noBukti = $this->generateNoBukti($CBG, $PERIODE, $tokoType);

                // Insert ke tabel PO
                $idPO = DB::connection($CBG)->table('po')->insertGetId([
                    'notes'     => 'KHUSUS',
                    'no_bukti'  => $noBukti,
                    'tgl'       => now(),
                    'tgo'       => $supplier->tgl,
                    'tgl_mulai' => $supplier->tgl,
                    'per'       => $PERIODE,
                    'flag'      => 'PO',
                    'kodes'     => $supplier->kodes,
                    'namas'     => $supplier->namas,
                    'jtempo'    => $supplier->JTEMPO,
                    'tkk1'      => $supplier->JTEMPO,
                    'tkks'      => $supplier->TKKS,
                    'usrnm'     => $USERNAME,
                    'tg_smp'    => now(),
                    'type'      => 'KS',
                    'golongan'  => $supplier->tipe,
                    'cbg'       => $CBG,
                    'buktik'    => $supplier->no_bukti,
                ]);

                // Get detail barang
                $details = DB::connection($CBG)
                    ->table('spo')
                    ->join('brg', 'spo.kd_brg', '=', 'brg.kd_brg')
                    ->select(
                        'spo.kd_brg',
                        'spo.na_brg',
                        'spo.qty',
                        'brg.type as tipe',
                        'brg.hb as harga',
                        DB::raw('spo.qty * brg.hb as total'),
                        'spo.kdlaku'
                    )
                    ->where('spo.kd_brg', 'brg.kd_brg')
                    ->where('brg.type', $supplier->tipe)
                    ->where('spo.kodes', $supplier->kodes)
                    ->where('spo.flag', 'KS')
                    ->where('spo.cbg', $CBG)
                    ->where('spo.ket', 'KHUSUS')
                    ->orderBy('spo.kd_brg')
                    ->get();

                $rec = 1;
                foreach ($details as $detail) {
                    // Jika lebih dari 15 item, buat PO baru
                    if ($rec > 15) {
                        $noBukti = $this->generateNoBukti($CBG, $PERIODE, $tokoType);

                        $idPO = DB::connection($CBG)->table('po')->insertGetId([
                            'notes'     => 'KHUSUS',
                            'no_bukti'  => $noBukti,
                            'tgl'       => now(),
                            'tgo'       => $supplier->tgl,
                            'tgl_mulai' => $supplier->tgl,
                            'per'       => $PERIODE,
                            'flag'      => 'PO',
                            'kodes'     => $supplier->kodes,
                            'namas'     => $supplier->namas,
                            'jtempo'    => $supplier->JTEMPO,
                            'tkk1'      => $supplier->JTEMPO,
                            'tkks'      => $supplier->TKKS,
                            'usrnm'     => $USERNAME,
                            'tg_smp'    => now(),
                            'type'      => 'KS',
                            'golongan'  => $supplier->tipe,
                            'cbg'       => $CBG,
                            'buktik'    => $supplier->no_bukti,
                        ]);

                        $rec = 1;
                    }

                    // Insert detail PO
                    DB::connection($CBG)->table('pod')->insert([
                        'no_bukti' => $noBukti,
                        'rec'      => $rec,
                        'per'      => $PERIODE,
                        'flag'     => 'PO',
                        'kd_brg'   => $detail->kd_brg,
                        'na_brg'   => $detail->na_brg,
                        'qty'      => $detail->qty,
                        'sisa'     => $detail->qty,
                        'id'       => $idPO,
                        'harga'    => $detail->harga,
                        'total'    => $detail->total,
                        'type'     => 'KS',
                        'cbg'      => $CBG,
                        'kdlaku'   => $detail->kdlaku,
                    ]);

                    $rec++;
                }

                // Update total PO
                DB::connection($CBG)->statement("
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
            DB::connection($CBG)->table('spo')
                ->where('flag', 'KS')
                ->where('ket', 'KHUSUS')
                ->whereRaw("LEFT(kodes, 3) = '542'")
                ->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Proses berhasil',
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
                    strtolower($popo) => $r1 == 99999 ? 0 : $r1,
                ]);

            $noBukti = 'K' . $tokoType . str_pad($r1, 5, '0', STR_PAD_LEFT);

            // Cek apakah no bukti sudah ada
            $exists = DB::connection($CBG)->table('po')
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

            $toko = DB::connection($CBG)->table('toko')
                ->where('kode', $CBG)
                ->first();

            $query = DB::connection($CBG)
                ->table('po')
                ->join('pod', 'po.no_bukti', '=', 'pod.no_bukti')
                ->join('sup', 'po.kodes', '=', 'sup.kodes')
                ->join('brg', 'pod.kd_brg', '=', 'brg.kd_brg')
                ->select(
                    DB::raw("'" . $toko->na_toko . "' as nmtoko"),
                    DB::raw("'" . $toko->alamat . "' as alamatini"),
                    'po.tgl',
                    'po.no_bukti',
                    'po.namas',
                    'po.kodes',
                    'sup.cat_sp',
                    'sup.golongan',
                    'sup.by_kr',
                    'po.tkk1',
                    'brg.item_uni',
                    'sup.email',
                    'brg.sub',
                    'brg.kdbar',
                    'brg.barcode as brcd',
                    'pod.kdlaku',
                    'pod.na_brg',
                    'brg.ket_kem',
                    'brg.ket_uk',
                    DB::raw("pod.qty / (SUBSTRING(TRIM(brg.ket_kem), LOCATE('/', TRIM(brg.ket_kem)) + 1)) as kem"),
                    'pod.qty'
                )
                ->where('po.no_bukti', $noBukti)
                ->orderBy('po.no_bukti')
                ->get();

            $data = [];
            foreach ($query as $row) {
                $data[] = [
                    'NMTOKO'    => $row->nmtoko,
                    'ALAMATINI' => $row->alamatini,
                    'TGL'       => Carbon::parse($row->tgl)->format('d-m-Y'),
                    'NO_BUKTI'  => $row->no_bukti,
                    'NAMAS'     => $row->namas,
                    'KODES'     => $row->kodes,
                    'CAT_SP'    => $row->cat_sp,
                    'GOLONGAN'  => $row->golongan,
                    'BY_KR'     => $row->by_kr,
                    'TKK1'      => Carbon::parse($row->tkk1)->format('d-m-Y'),
                    'ITEM_UNI'  => $row->item_uni,
                    'EMAIL'     => $row->email,
                    'SUB'       => $row->sub,
                    'KDBAR'     => $row->kdbar,
                    'BRCD'      => $row->brcd,
                    'KDLAKU'    => $row->kdlaku,
                    'NA_BRG'    => $row->na_brg,
                    'KET_KEM'   => $row->ket_kem,
                    'KET_UK'    => $row->ket_uk,
                    'KEM'       => $row->kem,
                    'QTY'       => $row->qty,
                ];
            }

            $PHPJasperXML = new PHPJasperXML();
            $PHPJasperXML->load_xml_file(base_path() . '/app/reportc01/phpjasperxml/cetak_sp_kode5.jrxml');
            $PHPJasperXML->setData($data);
            ob_end_clean();
            $PHPJasperXML->outpage("I");
        } catch (\Exception $e) {
            Log::error('Error in jasper: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal generate laporan: ' . $e->getMessage());
        }
    }

    public function cetak_ulang(Request $request)
    {
        try {
            $bukti = $request->bukti;
            $cbg   = Auth::user()->CBG;

            $toko = DB::table('toko')
                ->select('NA_TOKO', 'alamat as alamatini', 'type')
                ->where('KODE', $cbg)
                ->first();
            $na_toko   = $toko->NA_TOKO;
            $alamatini = $toko->alamatini;
            $tipe      = $toko->type;

            $query = DB::SELECT("SELECT po.tgl,po.no_bukti,
                                    po.NAMAS,po.KODES,sup.CAT_SP,sup.GOLONGAN,sup.BY_KR,po.TKK1,brg.ITEM_UNI,sup.email,
                                    brg.SUB,brg.KDBAR,brg.barcode as brcd,pod.KDLAKU,pod.NA_BRG,
                                    brg.KET_kem,brg.KET_UK,sup.kota as kotanya,
                                    pod.qty/(substr(trim(brg.KET_KEM),((LOCATE('/',trim(brg.ket_kem))+1)))) AS kem,pod.qty,
                                    IF(left(brg.BARCODE,3)<>left(brg.KD_BRG,3),'///',
                                    IF(sup.S_BAR='Y','V',if(sup.S_BAR='T','&','X')) ) as cod
                                    FROM po, pod, SUP, BRG
                                    where po.no_bukti = pod.no_bukti AND po.kodes=SUP.KODES
                                    AND pod.KD_BRG=brg.KD_BRG AND po.no_bukti='$bukti'  order by po.no_bukti ASC, pod.KD_BRG ASC ");

            $file         = 'ambil_data_cetak_sp_kode5';
            $PHPJasperXML = new PHPJasperXML();
            $PHPJasperXML->load_xml_file(base_path("/app/reportc01/phpjasperxml/{$file}.jrxml"));

            // $PHPJasperXML->setData($data);
            $cleanData                    = json_decode(json_encode($query), true);
            $PHPJasperXML->arrayParameter = [
                "na_toko"   => $na_toko,
                "alamatini" => $alamatini,
                "tipe"      => $tipe,
            ];

            $PHPJasperXML->setData($cleanData);

            // dd($cleanData);

            ob_end_clean();
            $PHPJasperXML->outpage("I");

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mencetak: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function prosesBukti(Request $request)
    {
        $bukti = trim($request->bukti);
        $cbg   = Auth::user()->CBG;

        // if (!$bukti) {
        //     return response()->json(['error' => 'Bukti tidak boleh kosong'], 400);
        // }

        // 1. Ambil data spo
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

        DB::table('spo')
            ->where('no_bukti', $bukti)
            ->whereIn('flag', ['JL', 'BL'])
            ->whereRaw("LEFT(kodes, 3) = '542'")
            ->update([
                'flag' => 'KS',
                'ket'  => 'KHUSUS',
            ]);

        DB::table('spo')
            ->join('brgdt', 'spo.kd_brg', '=', 'brgdt.kd_brg')
            ->where('spo.no_bukti', $bukti)
            ->whereIn('spo.flag', ['JL', 'BL', 'KS'])
            ->whereRaw("LEFT(spo.kodes, 3) = '542'")
            ->update([
                'spo.kdlaku' => DB::raw('brgdt.kdlaku'),
                'spo.klk'    => DB::raw('brgdt.klk'),
            ]);

        $updated = DB::table('spo')
            ->where('no_bukti', $bukti)
            ->whereRaw("LEFT(kodes, 3) = '542'")
            ->orderBy('kd_brg')
            ->get();

        return response()->json([
            'status'  => 'success',
            'message' => 'Data berhasil diproses',
            'data'    => $updated,
        ]);
    }

}