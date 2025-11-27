<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class TLHKode3TrialController extends Controller
{
    public function index(Request $request)
    {
        $judul = 'Transaksi LHKode3 Trial';
        $CBG = Auth::user()->CBG ?? null;

        if (!$CBG) {
            return view("otransaksi_TLHKode3Trial.index", compact('judul'))->with('error', 'User tidak memiliki akses cabang');
        }

        if (!$request->session()->has('periode')) {
            return view("otransaksi_TLHKode3Trial.index", compact('judul', 'CBG'))->with('warning', 'Periode belum diset');
        }

        $periode = $request->session()->get('periode');
        return view("otransaksi_TLHKode3Trial.index", compact('judul', 'CBG', 'periode'));
    }

    public function cari_data(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            $query = "SELECT * FROM tgz.lphkode3 ORDER BY SUB, KDBAR";
            $data = DB::select($query);

            if (empty($data)) {
                return response()->json(['error' => 'Tidak ada data. Silakan klik AMBIL DATA terlebih dahulu'], 404);
            }

            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            Log::error('Error cari_data: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal memuat data: ' . $e->getMessage()], 500);
        }
    }

    public function ambil_data(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            $cekData = DB::select("SELECT kd_brg FROM tgz.lphkode3 WHERE tgl = CURDATE() GROUP BY kd_brg");

            if (!empty($cekData)) {
                return response()->json(['error' => 'Data sudah posting hari ini!'], 400);
            }

            $minggu = DB::select("SELECT MONTH(NOW()) as bln, WEEK(NOW())-WEEK(DATE_ADD(LAST_DAY(DATE_SUB(NOW(),INTERVAL 1 MONTH)),INTERVAL 1 DAY)) as ming");
            $bulan = str_pad($minggu[0]->bln, 2, '0', STR_PAD_LEFT);
            $bulanLalu = str_pad($minggu[0]->bln - 1 ?: 12, 2, '0', STR_PAD_LEFT);
            $ming = $minggu[0]->ming;

            $cabangList = DB::select("SELECT kode FROM tgz.toko WHERE STA IN ('MA','CB') ORDER BY NO_ID ASC");

            DB::statement("TRUNCATE tgz.lphkode3");

            $unionQuery = [];

            foreach ($cabangList as $cbg) {
                $cbgCode = $cbg->kode;

                if ($ming == 3) {
                    $unionQuery[] = "
                        SELECT aa.cbg, aa.kd_brg, ROUND(SUM(IF(jns='1',qty,0)),2) as jumlah_kirim, 
                               ROUND(SUM(IF(jns='2',qty,0)),2) as jumlah_musnah, ROUND(SUM(IF(jns='3',qty,0)),2) as jumlah_jual,
                               SUM(krm) as krm, LEFT(brgdt.na_brg,1) as kdlaku, LEFT(brgdt.kd_brg,3) as sub, 
                               RIGHT(brgdt.kd_brg,4) as kdbar, brgdt.lph 
                        FROM (
                            SELECT AQ.*, IF(qty<0.5*brgdt.lph,1,0) as krm 
                            FROM (
                                SELECT beliz.cbg, belizd.kd_brg, SUM(belizd.qty) as qty, beliz.tgl, DAYOFWEEK(beliz.tgl) as hari, '1' AS JNS
                                FROM {$cbgCode}.beliz, {$cbgCode}.belizd 
                                WHERE beliz.NO_BUKTI=belizd.no_bukti AND LEFT(TRIM(belizd.NA_BRG),1)='3' 
                                      AND beliz.cbg='{$cbgCode}' AND MONTH(beliz.tgl)=MONTH(NOW()) AND YEAR(beliz.tgl)=YEAR(NOW()) 
                                      AND beliz.FLAG<>'RB' 
                                GROUP BY beliz.cbg, beliz.tgl, belizd.kd_brg
                                
                                UNION ALL
                                
                                SELECT musnah.cbg, musnahd.kd_brg, SUM(musnahd.qty) as qty, musnah.tgl, DAYOFWEEK(musnah.tgl) as hari, '2' AS JNS, 0 as krm
                                FROM {$cbgCode}.musnah, {$cbgCode}.musnahd 
                                WHERE musnah.no_bukti=musnahd.no_bukti AND LEFT(TRIM(musnahd.NA_BRG),1)='3' 
                                      AND musnah.cbg='{$cbgCode}' AND MONTH(musnah.tgl)=MONTH(NOW()) AND YEAR(musnah.tgl)=YEAR(NOW())
                                GROUP BY musnah.cbg, musnah.tgl, musnahd.kd_brg
                                
                                UNION ALL
                                
                                SELECT jual{$bulan}.cbg, juald{$bulan}.kd_brg, SUM(juald{$bulan}.qty) as qty, jual{$bulan}.tgl, DAYOFWEEK(jual{$bulan}.tgl) as hari, '3' AS JNS, 0 as krm
                                FROM {$cbgCode}.jual{$bulan}, {$cbgCode}.juald{$bulan} 
                                WHERE jual{$bulan}.NO_BUKTI=juald{$bulan}.no_bukti AND LEFT(TRIM(juald{$bulan}.NA_BRG),1)='3' 
                                      AND jual{$bulan}.cbg='{$cbgCode}' AND MONTH(jual{$bulan}.tgl)=MONTH(NOW()) AND YEAR(jual{$bulan}.tgl)=YEAR(NOW()) 
                                      AND jual{$bulan}.flag='JL'
                                GROUP BY jual{$bulan}.cbg, jual{$bulan}.tgl, juald{$bulan}.kd_brg
                            ) AS AQ, {$cbgCode}.brgdt 
                            WHERE AQ.kd_BRG=brgdt.KD_BRG AND AQ.CBG=brgdt.CBG
                        ) AS aa, {$cbgCode}.BRGDT 
                        WHERE aa.kd_brg=brgdt.KD_BRG AND aa.cbg=brgdt.cbg 
                        GROUP BY aa.kd_brg, aa.cbg
                    ";
                } else {
                    $unionQuery[] = "
                        SELECT aa.cbg, aa.kd_brg, ROUND(SUM(IF(jns='1',qty,0)),2) as jumlah_kirim, 
                               ROUND(SUM(IF(jns='2',qty,0)),2) as jumlah_musnah, ROUND(SUM(IF(jns='3',qty,0)),2) as jumlah_jual,
                               SUM(krm) as krm, LEFT(brgdt.na_brg,1) as kdlaku, LEFT(brgdt.kd_brg,3) as sub, 
                               RIGHT(brgdt.kd_brg,4) as kdbar, brgdt.lph 
                        FROM (
                            SELECT AQ.*, IF(qty<0.5*brgdt.lph,1,0) as krm 
                            FROM (
                                SELECT beliz.cbg, belizd.kd_brg, SUM(belizd.qty) as qty, beliz.tgl, DAYOFWEEK(beliz.tgl) as hari, '1' AS JNS
                                FROM {$cbgCode}.beliz, {$cbgCode}.belizd 
                                WHERE beliz.NO_BUKTI=belizd.no_bukti AND LEFT(TRIM(belizd.NA_BRG),1)='3' 
                                      AND beliz.cbg='{$cbgCode}' AND beliz.tgl>DATE_SUB(DATE(NOW()),INTERVAL 7 DAY) AND beliz.tgl<=DATE(NOW()) 
                                      AND beliz.FLAG<>'RB' 
                                GROUP BY beliz.cbg, beliz.tgl, belizd.kd_brg
                                
                                UNION ALL
                                
                                SELECT musnah.cbg, musnahd.kd_brg, SUM(musnahd.qty) as qty, musnah.tgl, DAYOFWEEK(musnah.tgl) as hari, '2' AS JNS, 0 as krm
                                FROM {$cbgCode}.musnah, {$cbgCode}.musnahd 
                                WHERE musnah.no_bukti=musnahd.no_bukti AND LEFT(TRIM(musnahd.NA_BRG),1)='3' 
                                      AND musnah.cbg='{$cbgCode}' AND musnah.tgl>DATE_SUB(DATE(NOW()),INTERVAL 7 DAY) AND musnah.tgl<=DATE(NOW())
                                GROUP BY musnah.cbg, musnah.tgl, musnahd.kd_brg
                                
                                UNION ALL
                                
                                SELECT jual{$bulan}.cbg, juald{$bulan}.kd_brg, SUM(juald{$bulan}.qty) as qty, jual{$bulan}.tgl, DAYOFWEEK(jual{$bulan}.tgl) as hari, '3' AS JNS, 0 as krm
                                FROM {$cbgCode}.jual{$bulan}, {$cbgCode}.juald{$bulan} 
                                WHERE jual{$bulan}.NO_BUKTI=juald{$bulan}.no_bukti AND LEFT(TRIM(juald{$bulan}.NA_BRG),1)='3' 
                                      AND jual{$bulan}.cbg='{$cbgCode}' AND jual{$bulan}.tgl>DATE_SUB(DATE(NOW()),INTERVAL 7 DAY) AND jual{$bulan}.tgl<=DATE(NOW()) 
                                      AND jual{$bulan}.flag='JL'
                                GROUP BY jual{$bulan}.cbg, jual{$bulan}.tgl, juald{$bulan}.kd_brg
                                
                                UNION ALL
                                
                                SELECT jual{$bulanLalu}.cbg, juald{$bulanLalu}.kd_brg, SUM(juald{$bulanLalu}.qty) as qty, jual{$bulanLalu}.tgl, DAYOFWEEK(jual{$bulanLalu}.tgl) as hari, '3' AS JNS, 0 as krm
                                FROM {$cbgCode}.jual{$bulanLalu}, {$cbgCode}.juald{$bulanLalu} 
                                WHERE jual{$bulanLalu}.NO_BUKTI=juald{$bulanLalu}.no_bukti AND LEFT(TRIM(juald{$bulanLalu}.NA_BRG),1)='3' 
                                      AND jual{$bulanLalu}.cbg='{$cbgCode}' AND jual{$bulanLalu}.tgl>DATE_SUB(DATE(NOW()),INTERVAL 7 DAY) AND jual{$bulanLalu}.tgl<=DATE(NOW()) 
                                      AND jual{$bulanLalu}.flag='JL'
                                GROUP BY jual{$bulanLalu}.cbg, jual{$bulanLalu}.tgl, juald{$bulanLalu}.kd_brg
                            ) AS AQ, {$cbgCode}.brgdt 
                            WHERE AQ.kd_BRG=brgdt.KD_BRG AND AQ.CBG=brgdt.CBG
                        ) AS aa, {$cbgCode}.BRGDT 
                        WHERE aa.kd_brg=brgdt.KD_BRG AND aa.cbg=brgdt.cbg 
                        GROUP BY aa.kd_brg, aa.cbg
                    ";
                }
            }

            $pembagi = $ming == 3 ? "(DAY(NOW()) - 1)" : "7";
            $mainQuery = "
                INSERT INTO tgz.lphkode3(UR, TGL, KD_BRG, NA_BRG, KET_UK, KET_KEM, SUB, KDBAR, LPH_TMM, LPH_GZ, LPH_KG, 
                                         LPHTMM, LPHGZ, LPHKG, LPH_TMM_LL, LPH_GZ_LL, LPH_KG_LL, JLMM, JLGZ, JLKG, MO, KETERANGAN)
                SELECT LPAD(IF(PP.SUB=@SUB,@NOM,@NOM:=@NOM+1),5,'0') AS UR, DATE(NOW()) AS TGL, PP.KD_BRG, brg.NA_BRG, brg.KET_UK, brg.KET_KEM,
                       @SUB:=PP.SUB AS SUB, PP.KDBAR,
                       IF(LPH_TMM<0,0,LPH_TMM) AS LPH_TMM, IF(LPH_GZ<0,0,LPH_GZ) AS LPH_GZ, IF(LPH_KG<0,0,LPH_KG) AS LPH_KG,
                       IF(LPH_TMM<0,0,LPH_TMM) AS LPHTMM, IF(LPH_GZ<0,0,LPH_GZ) AS LPHGZ, IF(LPH_KG<0,0,LPH_KG) AS LPHKG,
                       LPH_TMM_LL, LPH_GZ_LL, LPH_KG_LL, JLMM, JLGZ, JLKG, brg.MO,
                       CONCAT(DAY(DATE_SUB(NOW(),INTERVAL 1 DAY)),'/',MONTH(DATE_SUB(NOW(),INTERVAL 1 DAY)),'/',YEAR(DATE_SUB(NOW(),INTERVAL 1 DAY)),' - M ',PP.ming) as ket
                FROM (
                    SELECT KD_BRG, SUB, KDBAR, 
                           SUM(IF(CBG='TMM',IF(HSL IS NULL,0,HSL),0)) AS LPH_TMM,
                           SUM(IF(CBG='TGZ',IF(HSL IS NULL,0,HSL),0)) AS LPH_GZ,
                           SUM(IF(CBG='SOP',IF(HSL IS NULL,0,HSL),0)) AS LPH_KG,
                           WEEK(NOW())-WEEK(DATE_ADD(LAST_DAY(DATE_SUB(NOW(),INTERVAL 1 MONTH)),INTERVAL 1 DAY)) as ming,
                           SUM(IF(CBG='TMM',IF(LPH_TMM_LL IS NULL,0,LPH_TMM_LL),0)) AS LPH_TMM_LL,
                           SUM(IF(CBG='TGZ',IF(LPH_GZ_LL IS NULL,0,LPH_GZ_LL),0)) AS LPH_GZ_LL,
                           SUM(IF(CBG='SOP',IF(LPH_KG_LL IS NULL,0,LPH_KG_LL),0)) AS LPH_KG_LL,
                           SUM(IF(CBG='TGZ',IF(JLGZ IS NULL,0,JLGZ/{$pembagi}),0)) AS JLGZ,
                           SUM(IF(CBG='SOP',IF(JLKG IS NULL,0,JLKG/{$pembagi}),0)) AS JLKG,
                           SUM(IF(CBG='TMM',IF(JLMM IS NULL,0,JLMM/{$pembagi}),0)) AS JLMM
                    FROM (
                        SELECT TT.*, 
                               IF(jumlah_kirim<>0, ROUND((jumlah_kirim-jumlah_musnah)/" . ($ming == 3 ? "(DAY(NOW())-krm)" : "(7-krm)") . ",2), lph) as HSL,
                               IF(TT.cbg='TGZ',lph,0) as LPH_GZ_LL, IF(TT.cbg='TMM',lph,0) as LPH_TMM_LL, IF(TT.cbg='SOP',lph,0) as LPH_KG_LL,
                               IF(TT.cbg='TGZ',jumlah_jual,0) as JLGZ, IF(TT.cbg='TMM',jumlah_jual,0) as JLMM, IF(TT.cbg='SOP',jumlah_jual,0) as JLKG
                        FROM (
                            " . implode(" UNION ALL ", $unionQuery) . "
                        ) as tt 
                        WHERE jumlah_kirim > 0 OR jumlah_jual > 0
                    ) as yy 
                    GROUP BY KD_BRG, SUB, KDBAR
                ) AS PP 
                JOIN (SELECT @NOM:=0, @SUB:='') AS WW ON 1=1 
                LEFT JOIN tgz.BRG ON PP.KD_BRG=brg.KD_BRG 
                ORDER BY SUB, KD_BRG
            ";

            DB::statement($mainQuery);

            DB::statement("UPDATE tgz.lphkode3, tgz.BRGDT SET lphkode3.LPH_GZ_LL=BRGDT.LPH WHERE lphkode3.KD_BRG=BRGDT.KD_BRG AND brgdt.CBG='TGZ'");
            DB::statement("UPDATE tgz.lphkode3, sop.BRGDT SET lphkode3.LPH_KG_LL=BRGDT.LPH WHERE lphkode3.KD_BRG=BRGDT.KD_BRG AND brgdt.CBG='SOP'");
            DB::statement("UPDATE tgz.lphkode3, tmm.BRGDT SET lphkode3.LPH_TMM_LL=BRGDT.LPH WHERE lphkode3.KD_BRG=BRGDT.KD_BRG AND brgdt.CBG='TMM'");

            $this->updateDataTS();

            $jumlahData = DB::select("SELECT COUNT(*) as total FROM tgz.lphkode3")[0]->total;

            return response()->json([
                'success' => true,
                'message' => "Data berhasil diambil! Total {$jumlahData} item"
            ]);
        } catch (\Exception $e) {
            Log::error('Error ambil_data: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal mengambil data: ' . $e->getMessage()], 500);
        }
    }

    private function updateDataTS()
    {
        $pathDBF = 'D:\dbf\kode 3 ts\ki_uslh.DBF';

        if (!file_exists($pathDBF)) {
            return;
        }

        try {
            $connectionString = "odbc:Driver={Microsoft Visual FoxPro Driver};SourceType=DBF;SourceDB=" . dirname($pathDBF) . ";Exclusive=No;";
            $pdo = new \PDO($connectionString);

            $stmt = $pdo->query("SELECT TGL, SUB, KDBAR, lph_GZ, lph_KG, lph_TMM FROM ki_uslh WHERE tgl = DATE()");
            $dataTS = $stmt->fetchAll(\PDO::FETCH_OBJ);

            foreach ($dataTS as $row) {
                DB::statement("
                    UPDATE tgz.lphkode3 
                    SET TS_GZ = ?, TS_KG = ?, TS_MM = ? 
                    WHERE KD_BRG = CONCAT(?, ?)
                ", [$row->lph_GZ, $row->lph_KG, $row->lph_TMM, trim($row->SUB), trim($row->KDBAR)]);
            }
        } catch (\Exception $e) {
            Log::error('Error update TS: ' . $e->getMessage());
        }
    }

    public function proses(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            $items = $request->input('items', []);
            if (empty($items)) {
                return response()->json(['error' => 'Tidak ada data untuk diproses'], 400);
            }

            DB::beginTransaction();

            $cibings = ['GZ' => 'LPH', 'TMM' => 'lph_tm', 'KG' => 'lph_tf'];
            $updated = 0;

            foreach ($cibings as $cibing => $cebong) {
                $field = $cibing == 'GZ' ? 'LPHGZ' : ($cibing == 'TMM' ? 'LPHTMM' : 'LPHKG');

                DB::statement("
                    UPDATE brgdt, tgz.lphkode3 
                    SET brgdt.LPH = lphkode3.{$field}, 
                        brgdt.TGL_LPH = NOW(),
                        brgdt.SRMIN = lphkode3.{$field}, 
                        brgdt.SRMAX = ROUND(lphkode3.{$field} * 1.5, 2)
                    WHERE brgdt.cbg = ? AND brgdt.KD_BRG = lphkode3.KD_BRG
                ", [$CBG == 'TGZ' ? 'TGZ' : ($CBG == 'TMM' ? 'TMM' : 'SOP')]);

                DB::statement("UPDATE tgz.brg, tgz.lphkode3 SET brg.{$cebong} = lphkode3.LPH_{$cibing} WHERE brg.KD_BRG = lphkode3.KD_BRG");
                DB::statement("
                    UPDATE tgz.brg A, tgz.lphkode3 B 
                    SET A.DTR2 = IF(ROUND(A.DTB * A.{$cebong}) < 3, 
                                    3 * SUBSTR(TRIM(A.KET_KEM), LOCATE('/', TRIM(A.ket_kem)) + 1), 
                                    CEILING(A.DTB * A.{$cebong}))
                    WHERE A.KD_BRG = B.KD_BRG
                ");

                DB::statement("UPDATE sop.brg, tgz.lphkode3 SET brg.{$cebong} = lphkode3.LPH_{$cibing} WHERE brg.KD_BRG = lphkode3.KD_BRG");
                DB::statement("
                    UPDATE sop.brg A, tgz.lphkode3 B 
                    SET A.DTR2 = IF(ROUND(A.DTB * A.{$cebong}) < 3, 
                                    3 * SUBSTR(TRIM(A.KET_KEM), LOCATE('/', TRIM(A.ket_kem)) + 1), 
                                    CEILING(A.DTB * A.{$cebong}))
                    WHERE A.KD_BRG = B.KD_BRG
                ");

                DB::statement("UPDATE tmm.brg, tgz.lphkode3 SET brg.{$cebong} = lphkode3.LPH_{$cibing} WHERE brg.KD_BRG = lphkode3.KD_BRG");
                DB::statement("
                    UPDATE tmm.brg A, tgz.lphkode3 B 
                    SET A.DTR2 = IF(ROUND(A.DTB * A.{$cebong}) < 3, 
                                    3 * SUBSTR(TRIM(A.KET_KEM), LOCATE('/', TRIM(A.ket_kem)) + 1), 
                                    CEILING(A.DTB * A.{$cebong}))
                    WHERE A.KD_BRG = B.KD_BRG
                ");

                $updated++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Data berhasil diproses dan diupdate ke semua cabang!<br>Total cabang diupdate: {$updated}"
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error proses: ' . $e->getMessage());
            return response()->json(['error' => 'Proses gagal: ' . $e->getMessage()], 500);
        }
    }

    public function detail(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            $query = "SELECT * FROM tgz.lphkode3 ORDER BY SUB, KDBAR";
            $data = DB::select($query);

            if (empty($data)) {
                return response()->json(['error' => 'Tidak ada data untuk dicetak'], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $data,
                'cbg' => $CBG
            ]);
        } catch (\Exception $e) {
            Log::error('Error detail: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal mengambil detail: ' . $e->getMessage()], 500);
        }
    }
}
