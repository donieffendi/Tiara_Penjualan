<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;

class TCetakLBKKLBTATBaruController extends Controller
{
    var $judul = 'Cetak LBKK/LBTAT Baru';
    var $FLAGZ = 'LBKK';

    public function index(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                return view("otransaksi_cetaklbkk_lbtat_baru.index")->with([
                    'judul' => $this->judul,
                    'flagz' => $this->FLAGZ,
                    'error' => 'User tidak memiliki akses cabang (CBG). Hubungi administrator.'
                ]);
            }

            return view("otransaksi_cetaklbkk_lbtat_baru.index")->with([
                'judul' => $this->judul,
                'flagz' => $this->FLAGZ,
                'cbg' => $CBG
            ]);
        } catch (\Exception $e) {
            Log::error('Error in TCetakLBKKLBTATBaru index: ' . $e->getMessage());
            return view("otransaksi_cetaklbkk_lbtat_baru.index")->with([
                'judul' => $this->judul,
                'flagz' => $this->FLAGZ,
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    public function getTCetakLBKKLBTATBaruData(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG;
            $tabType = $request->tab_type ?? 'bz'; // bz, bt, hasil, scan, 3z, 3t
            $jns = $request->jns ?? '2'; // untuk LBTAT: 2=harian, 3=>2 hari

            Log::info('TCetakLBKKLBTATBaru getData() start', [
                'cbg' => $CBG,
                'tab_type' => $tabType,
                'jns' => $jns
            ]);

            // Get toko info
            $toko = DB::select("SELECT NA_TOKO, TYPE FROM toko WHERE KODE = ?", [$CBG]);
            $naToko = $toko[0]->NA_TOKO ?? '';
            
            if($CBG == 'TGZ') {
                $cb = 'Z';
            } else if($CBG == 'TMM') {
                $cb = 'M';
            } else if($CBG == 'SOP') {
                $cb = 'S';
            }

            if ($tabType == 'bz') {
                // Tab 1: Laporan Barang Kosong Komputer (LBKK)
                $query = DB::select("
                    SELECT
                        ? as na_toko,
                        B.rec,
                        A.no_bukti as bukti,
                        CONCAT(LEFT(A.no_bukti,2), RIGHT(A.no_bukti,5)) as no_bukti,
                        B.itemsub,
                        B.kd_brg,
                        B.na_brg,
                        B.ket_uk,
                        B.ket_kem,
                        B.kd,
                        B.hj,
                        B.saldo,
                        B.tgl_trm,
                        B.qty_trm,
                        B.tgl_lbk,
                        B.dtr,
                        B.on_ord
                    FROM {$CBG}.lapbh A
                    INNER JOIN {$CBG}.lapbhd B ON A.no_bukti = B.no_bukti
                    WHERE A.tgl = DATE(NOW())
                        AND A.flag = 'BZ'
                        AND LEFT(A.no_bukti, 2) <> 'SZ'
                        AND RIGHT(A.no_bukti, 1) = ?
                        AND B.saldo < 0
                    ORDER BY A.no_bukti, B.kd_brg
                ", [$naToko, $cb]);
            } elseif ($tabType == 'bt') {
                // Tab 2: Laporan Barang Tidak Ada Transaksi (LBTAT)
                $query = DB::select("
                    SELECT
                        ? as na_toko,
                        B.kd_brg,
                        B.rec,
                        A.no_bukti as bukti,
                        CONCAT(LEFT(A.no_bukti,2), RIGHT(A.no_bukti,5)) as no_bukti,
                        B.itemsub,
                        B.na_brg,
                        B.ket_uk,
                        B.ket_kem,
                        B.kd,
                        B.hj,
                        B.lph,
                        B.saldo,
                        B.tgl_trm,
                        B.qty_trm,
                        B.tgl_lbk,
                        B.tgl_at,
                        DATEDIFF(DATE(NOW()), B.tgl_at) as ini,
                        B.dtr
                    FROM {$CBG}.lapbh A
                    INNER JOIN {$CBG}.lapbhd B ON A.no_bukti = B.no_bukti
                    WHERE A.tgl = DATE(NOW())
                        AND A.flag = 'BT'
                        AND A.typ = ?
                        AND RIGHT(A.no_bukti, 1) = ?
                    ORDER BY A.no_bukti, B.kd_brg
                ", [$naToko, $jns, $cb]);
            } elseif ($tabType == 'hasil') {
                // Tab 3: Hasil Penanganan LBTAT
                $query = DB::select("
                    CALL {$CBG}.penjualan_report_lbtat(?)
                ", [$CBG]);
            } elseif ($tabType == 'scan') {
                // Tab 4: Laporan Barang Kosong (Scan)
                $query = DB::select("
                    SELECT
                        ? as na_toko,
                        B.rec,
                        A.no_bukti as bukti,
                        CONCAT(LEFT(A.no_bukti,2), RIGHT(A.no_bukti,5)) as no_bukti,
                        B.itemsub,
                        B.kd_brg,
                        B.na_brg,
                        B.ket_uk,
                        B.ket_kem,
                        B.kd,
                        B.hj,
                        B.saldo,
                        B.tgl_trm,
                        B.qty_trm,
                        B.tgl_lbk,
                        B.dtr,
                        B.on_ord
                    FROM {$CBG}.lapbh A
                    INNER JOIN {$CBG}.lapbhd B ON A.no_bukti = B.no_bukti
                    WHERE A.tgl = DATE(NOW())
                        AND A.flag = 'BZ'
                        AND LEFT(A.no_bukti, 2) = 'SZ'
                        AND RIGHT(A.no_bukti, 1) = ?
                    ORDER BY A.no_bukti, B.kd_brg
                ", [$naToko, $cb]);
            } elseif ($tabType == '3z') {
                // Tab 1: LBKK Kode 3
                $query = DB::select("
                    SELECT
                        ? as na_toko,
                        B.rec,
                        A.no_bukti as bukti,
                        CONCAT(LEFT(A.no_bukti,2), RIGHT(A.no_bukti,5)) as no_bukti,
                        B.itemsub,
                        B.kd_brg,
                        B.na_brg,
                        B.ket_uk,
                        B.ket_kem,
                        B.kd,
                        B.hj,
                        B.saldo,
                        B.tgl_trm,
                        B.qty_trm,
                        DATE_FORMAT(IFNULL(B.tgl_lbk, '2001-01-01'), '%d/%m/%Y') as tgl_lbk,
                        B.dtr,
                        B.on_ord
                    FROM {$CBG}.lapbh A
                    INNER JOIN {$CBG}.lapbhd B ON A.no_bukti = B.no_bukti
                    WHERE A.tgl = DATE(NOW())
                        AND A.flag = '3Z'
                        AND B.saldo <= 0
                        AND RIGHT(A.no_bukti, 1) = ?
                    ORDER BY A.no_bukti, B.kd_brg
                ", [$naToko, $cb]);
            } elseif ($tabType == '3t') {
                // Tab 2: LBTAT Kode 3
                $query = DB::select("
                    SELECT
                        ? as na_toko,
                        A.sub,
                        B.kd_brg,
                        B.rec,
                        A.no_bukti as bukti,
                        CONCAT(LEFT(A.no_bukti,2), RIGHT(A.no_bukti,5)) as no_bukti,
                        B.itemsub,
                        B.na_brg,
                        B.ket_uk,
                        B.ket_kem,
                        B.kd,
                        B.hj,
                        B.lph,
                        B.saldo,
                        B.tgl_trm,
                        B.qty_trm,
                        B.tgl_lbk,
                        B.tgl_at,
                        DATEDIFF(DATE(NOW()), B.tgl_at) as ini,
                        B.dtr,
                        IF(CONCAT(C.f_panen, C.f_ada) = 'M', '*', '') as sulit
                    FROM {$CBG}.lapbh A
                    INNER JOIN {$CBG}.lapbhd B ON A.no_bukti = B.no_bukti
                    LEFT JOIN {$CBG}.brg C ON B.kd_brg = C.kd_brg
                    WHERE A.tgl = DATE(NOW())
                        AND A.flag = '3T'
                    ORDER BY A.no_bukti, B.kd_brg
                ", [$naToko]);
            } else {
                $query = [];
            }

            return Datatables::of(collect($query))
                ->addIndexColumn()
                ->addColumn('LABEL', function ($row) {
                        return $row->LABEL ?? 0;   //untuk print label
                    })
                ->make(true);
        } catch (\Exception $e) {
            Log::error('Error in getTCetakLBKKLBTATBaruData: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function proses(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG;
            $USERNAME = Auth::user()->username;
            $flagType = $request->flag_type; // BZ, BT, 3Z, 3T
            $jns = $request->jns ?? '2'; // untuk LBTAT
            $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
            $monthstring = substr($periode, 0, 2);

            $toko = DB::select("SELECT TYPE FROM toko WHERE KODE = ?", [$CBG]);
            if (empty($toko)) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Data toko tidak ditemukan untuk cabang ' . $CBG
                ]);
            }
            $kode2 = $toko[0]->TYPE ?? '';

            DB::beginTransaction();

            if($flagType != '3Z' || $flagType != '3T') {

                // Cek apakah sudah diproses
                Log::info('Mengecek apakah laporan sudah diproses sebelumnya...');
                $cek = DB::select("
                    SELECT
                        CONCAT(UPPER(LEFT(usrnm,1)), LOWER(SUBSTR(usrnm,2))) as asma,
                        TIME(tg_smp) as waktu
                    FROM {$CBG}.lapbh
                    WHERE FLAG = ?
                        AND cbg = ?
                        AND tgl = DATE(NOW())
                ", [$flagType, $CBG]);

                if (count($cek) > 0) {
                    $nama = $cek[0]->asma;
                    $jam = $cek[0]->waktu;
                    Log::warning('Laporan sudah pernah diproses', [
                        'oleh' => $nama,
                        'jam' => $jam
                    ]);
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Laporan telah diproses oleh {$nama} pada jam {$jam}"
                    ]);
                }
                DB::statement("
                    DELETE FROM {$CBG}.lapbhd
                    WHERE id IN (
                        SELECT no_id FROM {$CBG}.lapbh
                        WHERE flag = ? AND tgl < DATE(NOW())
                    )
                ", [$flagType]);
                Log::info('Data lapbhd lama berhasil dihapus');

                DB::statement("
                    DELETE FROM {$CBG}.lapbh
                    WHERE flag = ? AND tgl < DATE(NOW())
                ", [$flagType]);
                Log::info('Data lapbh lama berhasil dihapus');

                // Update dan hapus data > 3 hari
                Log::info('Membersihkan data lebih dari 3 hari...');
                DB::statement("
                    UPDATE {$CBG}.lapbh A, {$CBG}.lapbhd B
                    SET B.gol = 9
                    WHERE A.no_bukti = B.no_bukti
                        AND A.tgl < DATE_SUB(DATE(NOW()), INTERVAL 3 DAY)
                ");

                DB::statement("
                    UPDATE {$CBG}.lapbh
                    SET hps = 9
                    WHERE tgl < DATE_SUB(DATE(NOW()), INTERVAL 3 DAY)
                ");

                DB::statement("DELETE FROM {$CBG}.lapbhd WHERE gol = 9");
                DB::statement("DELETE FROM {$CBG}.lapbh WHERE hps = 9");
                Log::info('Cleanup data > 3 hari selesai');


            }
            
            // Proses sesuai flag type
            if ($flagType == 'BZ') {
                // Proses LBKK
                Log::info('=== PROSES LBKK HARIAN (BZ) ===');
                $kode = 'BZ' . substr($periode, 2, 2) . substr($periode, 0, 2);
                Log::info('Kode LBKK: ' . $kode);

                Log::info('Mengambil nomor bukti dari notrans...');
                $noBukti = DB::select("
                    SELECT NOM{$monthstring} as NO_BUKTI
                    FROM notrans
                    WHERE trans = 'LAPBHZ' AND PER = ?
                ", [substr($periode, 2, 4)]);

                $r1 = $noBukti[0]->NO_BUKTI ?? 1;
                Log::info('Nomor bukti: ' . $r1);

                Log::info('Update nomor bukti di notrans...');
                DB::statement("
                    UPDATE notrans
                    SET NOM{$monthstring} = ?
                    WHERE trans = 'LAPBHZ' AND PER = ?
                ", [$r1, substr($periode, 2, 4)]);

                Log::info('Menjalankan stored procedure lapbha...', [
                    'cbg' => $CBG,
                    'periode' => $periode,
                    'flag' => 'BZ',
                    'bukti' => $r1,
                    'username' => $USERNAME
                ]);
                DB::statement("
                    CALL {$CBG}.lapbha(?, ?, ?, ?, ?)
                ", [$CBG, $periode, 'BZ', $r1, $USERNAME]);
                Log::info('Stored procedure lapbha selesai');
            } elseif ($flagType == 'BT') {
                // Proses LBTAT
                Log::info('=== PROSES LBTAT (BT) ===', ['jenis' => $jns == '2' ? 'Harian' : '> 2 Hari']);
                Log::info('Menjalankan stored procedure pjl_lbtt...', [
                    'cbg' => $CBG,
                    'periode' => $periode,
                    'flag' => 'BT',
                    'username' => $USERNAME,
                    'jns' => $jns
                ]);
                DB::statement("
                    CALL {$CBG}.pjl_lbtt(?, ?, ?, ?, ?)
                ", [$CBG, $periode, 'BT', $USERNAME, $jns]);
                Log::info('Stored procedure pjl_lbtt selesai');
            } elseif ($flagType == '3Z') {
                // Proses LBKK Kode 3
                Log::info('=== PROSES LBKK KODE 3 (3Z) ===');
                Log::info('Menjalankan stored procedure tgz.pjl_lbk_ff...', ['cbg' => $CBG]);
                DB::statement("
                    CALL tgz.pjl_lbk_ff(?)
                ", [$CBG]);
                Log::info('Stored procedure pjl_lbk_ff selesai');

                Log::info('Mengecek hasil proses dari temp_result...');
                $result = DB::select("
                    SELECT NOTES FROM {$CBG}.temp_result WHERE id = 1
                ");

                $notes = $result[0]->NOTES ?? '';
                if ($notes != '') {
                    Log::error('Proses gagal dengan pesan: ' . $notes);
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => $notes
                    ]);
                }
                Log::info('Proses LBKK Kode 3 berhasil');
            } elseif ($flagType == '3T') {
                // Proses LBTAT Kode 3
                Log::info('=== PROSES LBTAT KODE 3 (3T) ===');
                Log::info('Menjalankan stored procedure tgz.pjl_lbtt_ff...', ['cbg' => $CBG]);
                DB::statement("
                    CALL tgz.pjl_lbtt_ff(?)
                ", [$CBG]);
                Log::info('Stored procedure pjl_lbtt_ff selesai');

                Log::info('Mengecek hasil proses dari temp_result...');
                $result = DB::select("
                    SELECT NOTES FROM {$CBG}.temp_result WHERE id = 1
                ");

                $notes = $result[0]->NOTES ?? '';
                if ($notes != '') {
                    Log::error('Proses gagal dengan pesan: ' . $notes);
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => $notes
                    ]);
                }
                Log::info('Proses LBTAT Kode 3 berhasil');
            }

            Log::info('Commit transaksi database...');
            DB::commit();
            Log::info('=== PROSES SELESAI SUKSES ===', ['flag_type' => $flagType]);

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diproses'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('=== PROSES GAGAL ===', [
                'flag_type' => $flagType ?? 'unknown',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Gagal memproses data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function jasper(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG;
            $tabType = $request->tab_type;
            $jns = $request->jns ?? '2';
            $reportType = $request->report_type;

            // Get toko info
            $toko = DB::select("SELECT NA_TOKO, TYPE FROM toko WHERE KODE = ?", [$CBG]);
            $naToko = $toko[0]->NA_TOKO ?? '';
            if($CBG == 'TGZ') {
                $cb = 'Z';
            } else if($CBG == 'TMM') {
                $cb = 'M';
            } else if($CBG == 'SOP') {
                $cb = 'S';
            }

            $noForm = '';
            $judul = '';

            if ($tabType == 'bz') {
                $noForm = 'T-PPK1-527';
                $judul = 'LAPORAN BARANG KOSONG KOMPUTER';
                $file = 'rpt_lbkk_lbtat_baru';

                $query = DB::select("
                    SELECT
                        ? as no_form,
                        ? as judul,
                        ? as na_toko,
                        B.rec,
                        CONCAT(LEFT(A.no_bukti,2), RIGHT(A.no_bukti,5)) as no_bukti,
                        B.itemsub,
                        B.kd_brg,
                        B.na_brg,
                        B.ket_uk,
                        B.ket_kem,
                        B.kd,
                        B.hj,
                        B.saldo,
                        B.tgl_trm,
                        B.qty_trm,
                        B.tgl_lbk,
                        B.dtr,
                        B.on_ord
                    FROM {$CBG}.lapbh A
                    INNER JOIN {$CBG}.lapbhd B ON A.no_bukti = B.no_bukti
                    WHERE A.tgl = DATE(NOW())
                        AND A.flag = 'BZ'
                        AND LEFT(A.no_bukti, 2) <> 'SZ'
                        AND RIGHT(A.no_bukti, 1) = ?
                        AND B.saldo < 0
                    ORDER BY A.no_bukti, B.kd_brg
                ", [$noForm, $judul, $naToko, $cb]);
            } elseif ($tabType == 'bt') {
                if ($jns == '2') {
                    $noForm = 'T-PPK1-528.2';
                    $judul = 'LAPORAN PEMANTAUAN BARANG TIDAK ADA TRANSAKSI KASIR ( NON KODE 3 )';
                } else {
                    $noForm = 'T-PPK1-528.3';
                    $judul = 'LAPORAN PEMANTAUAN BARANG TIDAK ADA TRANSAKSI KASIR > 2 HARI ( NON KODE 3 )';
                }

                $query = DB::select("
                    SELECT
                        ? as no_form,
                        ? as judul,
                        ? as na_toko,
                        B.kd_brg,
                        B.rec,
                        CONCAT(LEFT(A.no_bukti,2), RIGHT(A.no_bukti,5)) as no_bukti,
                        B.itemsub,
                        B.na_brg,
                        B.ket_uk,
                        B.ket_kem,
                        B.kd,
                        B.hj,
                        B.lph,
                        B.saldo,
                        B.tgl_trm,
                        B.qty_trm,
                        B.tgl_lbk,
                        B.tgl_at,
                        DATEDIFF(DATE(NOW()), B.tgl_at) as ini,
                        B.dtr
                    FROM {$CBG}.lapbh A
                    INNER JOIN {$CBG}.lapbhd B ON A.no_bukti = B.no_bukti
                    WHERE A.tgl = DATE(NOW())
                        AND A.flag = 'BT'
                        AND A.typ = ?
                        AND RIGHT(A.no_bukti, 1) = ?
                    ORDER BY A.no_bukti, B.kd_brg
                ", [$noForm, $judul, $naToko, $jns, $cb]);
            } elseif ($tabType == 'hasil') {
                $query = DB::select("
                    CALL {$CBG}.penjualan_report_lbtat(?)
                ", [$CBG]);

                if($reportType == 'report') {
                    $file = 'rpt_lbkk_lbtat_baru_2';
                } else if ($reportType == 'order-sela') {
                    $query = DB::select("CALL pjl_rordlebih");
                    $file = 'rpt_lbkk_lbtat_baru_3';
                } else if($reportType == 'label') {
                    $file = 'rpt_lbkk_lbtat_baru_4';

                    // filter yg LABEL = 1
                    $filtered = [];
                    foreach ($query as $row) {
                        if (($row->LABEL ?? 0) == 1) {
                            $filtered[] = $row;
                        }
                    }

                    if (count($filtered) == 0) {
                        $filtered[] = (object)[
                            'KD_BRG' => '',
                            'NA_BRG' => '',
                            'KET_UK' => '',
                            'KET_KEM' => '',
                            'TGL_PSN' => '',
                        ];
                    }

                    $query = $filtered;

                }
                
            } elseif ($tabType == 'scan') {
                $noForm = 'T-PPK1-527S';
                $judul = 'LAPORAN BARANG KOSONG (SCAN)';

                $query = DB::select("
                    SELECT
                        ? as no_form,
                        ? as judul,
                        ? as na_toko,
                        B.rec,
                        CONCAT(LEFT(A.no_bukti,2), RIGHT(A.no_bukti,5)) as no_bukti,
                        B.itemsub,
                        B.kd_brg,
                        B.na_brg,
                        B.ket_uk,
                        B.ket_kem,
                        B.kd,
                        B.hj,
                        B.saldo,
                        B.tgl_trm,
                        B.qty_trm,
                        B.tgl_lbk,
                        B.dtr,
                        B.on_ord
                    FROM {$CBG}.lapbh A
                    INNER JOIN {$CBG}.lapbhd B ON A.no_bukti = B.no_bukti
                    WHERE A.tgl = DATE(NOW())
                        AND A.flag = 'BZ'
                        AND LEFT(A.no_bukti, 2) = 'SZ'
                        AND RIGHT(A.no_bukti, 1) = ?
                    ORDER BY A.no_bukti, B.kd_brg
                ", [$noForm, $judul, $naToko, $cb]);
            } elseif ($tabType == '3z') {
                $noForm = 'T-PPK1-527.1';
                $judul = 'LAPORAN BARANG KOSONG KOMPUTER ( KODE 3 )';

                $query = DB::select("
                    SELECT
                        ? as no_form,
                        ? as judul,
                        ? as na_toko,
                        B.rec,
                        CONCAT(LEFT(A.no_bukti,2), RIGHT(A.no_bukti,5)) as no_bukti,
                        B.itemsub,
                        B.kd_brg,
                        B.na_brg,
                        B.ket_uk,
                        B.ket_kem,
                        B.kd,
                        B.hj,
                        B.saldo,
                        B.tgl_trm,
                        B.qty_trm,
                        DATE_FORMAT(IFNULL(B.tgl_lbk, '2001-01-01'), '%d/%m/%Y') as tgl_lbk,
                        B.dtr,
                        B.on_ord
                    FROM {$CBG}.lapbh A
                    INNER JOIN {$CBG}.lapbhd B ON A.no_bukti = B.no_bukti
                    WHERE A.tgl = DATE(NOW())
                        AND A.flag = '3Z'
                        AND B.saldo <= 0
                        AND RIGHT(A.no_bukti, 1) = ?
                    ORDER BY A.no_bukti, B.kd_brg
                ", [$noForm, $judul, $naToko, $cb]);
            } elseif ($tabType == '3t') {
                $noForm = 'T-PPK1-528.1';
                $judul = 'LAPORAN PEMANTAUAN BARANG TIDAK ADA TRANSAKSI KASIR ( KODE 3 )';

                $query = DB::select("
                    SELECT
                        ? as no_form,
                        ? as judul,
                        ? as na_toko,
                        A.sub,
                        B.kd_brg,
                        B.rec,
                        CONCAT(LEFT(A.no_bukti,2), RIGHT(A.no_bukti,5)) as no_bukti,
                        B.itemsub,
                        B.na_brg,
                        B.ket_uk,
                        B.ket_kem,
                        B.kd,
                        B.hj,
                        B.lph,
                        B.saldo,
                        B.tgl_trm,
                        B.qty_trm,
                        B.tgl_lbk,
                        B.tgl_at,
                        DATEDIFF(DATE(NOW()), B.tgl_at) as ini,
                        B.dtr,
                        IF(CONCAT(C.f_panen, C.f_ada) = 'M', '*', '') as sulit
                    FROM {$CBG}.lapbh A
                    INNER JOIN {$CBG}.lapbhd B ON A.no_bukti = B.no_bukti
                    LEFT JOIN {$CBG}.brg C ON B.kd_brg = C.kd_brg
                    WHERE A.tgl = DATE(NOW())
                        AND A.flag = '3T'
                    ORDER BY A.no_bukti, B.kd_brg
                ", [$noForm, $judul, $naToko]);
            }

            
            $PHPJasperXML = new PHPJasperXML();
            $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

            $data = [];
            foreach ($query as $key => $value) {
                array_push($data, array(
                    'NO_BUKTI' => $query[$key]->no_bukti ?? $query[$key]->NO_BUKTI ?? '',
                    'DATE' => date('d-m-Y'),
                    'TGL_TRM' => $query[$key]->tgl_trm ?? $query[$key]->TGL_TRM ?? '',
                    'KD_BRG' => $query[$key]->kd_brg ?? $query[$key]->KD_BRG ?? '',
                    'NA_BRG' => $query[$key]->na_brg ?? $query[$key]->NA_BRG ?? '',
                    'KET_UK' => $query[$key]->ket_uk ?? $query[$key]->KET_UK ?? '',
                    'KET_KEM' => $query[$key]->ket_kem ?? $query[$key]->KET_KEM ?? '',
                    'NA_TOKO' => $query[$key]->na_toko ?? $query[$key]->nmtoko ?? '',
                    'KD' => $query[$key]->kd ?? '',
                    'HJ' => $query[$key]->hj ?? $query[$key]->HJ ?? 0,
                    'SALDO' => $query[$key]->saldo ?? 0,
                    'ON_ORD' => $query[$key]->on_ord ?? '',
                    'QTY_TRM' => $query[$key]->qty_trm ?? $query[$key]->QTY_TRM ?? 0,
                    'DTR' => $query[$key]->dtr ?? '',
                    'TGL_LBK' => $query[$key]->tgl_lbk ?? '',
                    'TIME' => date('H:i:s'),
                    'SPL' => $query[$key]->SPL ?? '',
                    'TGL_PSN' => $query[$key]->TGL_PSN ?? '',
                    'TGL_AT' => $query[$key]->TGL_AT ?? '',
                    'BKT_AT' => $query[$key]->BKT_AT ?? '',
                    'GAK00' => $query[$key]->GAK00 ?? 0,
                    'AK00' => $query[$key]->AK00 ?? 0,
                    'SRMIN' => $query[$key]->SRMIN ?? '',
                    'ORDERAN' => $query[$key]->orderan ?? '',
                    'STOKTGZ' => $query[$key]->stoktgz ?? 0,
                    'STOKDCK' => $query[$key]->stokdck ?? 0,
                    'KETTGZ' => $query[$key]->kettgz ?? '',
                    'KETDCK' => $query[$key]->ketdck ?? '',
                ));
            }
			
			if (empty($data)) {
				$data[] = [
					'NO_BUKTI' => '',
					'DATE' => date('d-m-Y'),
					'TGL_TRM' => '',
					'KD_BRG' => '',
					'NA_BRG' => '',
					'KET_UK' => '',
					'KET_KEM' => '',
					'NA_TOKO' => '',
					'KD' => '',
					'HJ' => '',
					'SALDO' => '',
					'ON_ORD' => '',
					'QTY_TRM' => '',
					'DTR' => '',
					'TGL_LBK' => '',
					'TIME' => '',
                    'TGL_PSN' => '',
                    'TGL_AT' => '',
                    'BKT_AT' => '',
                    'GAK00' => '',
                    'AK00' => '',
                    'SRMIN' => '',
                    'ORDERAN' => '',
                    'STOKTGZ' => '',
                    'STOKDCK' => '',
                    'KETTGZ' => '',
                    'KETDCK' => '',
				];
			}

            $PHPJasperXML->setData($data);
            ob_end_clean();
            $PHPJasperXML->outpage("I");


            return response()->json([
                'success' => true,
                'data' => $query
            ]);


        } catch (\Exception $e) {
            Log::error('Error in jasper: ' . $e->getMessage());
            return response()->json([
                'error' => 'Gagal generate laporan: ' . $e->getMessage()
            ], 500);
        }
    }
}
