<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PHKasir13Controller extends Controller
{
    public function index()
    {
        try {
            $stocks = DB::select("SELECT kd_brg, ak00 as stok FROM brgdt WHERE LEFT(kd_brg,3)='011' ORDER BY kd_brg");
        } catch (\Illuminate\Database\QueryException $e) {
            $stocks = [];
        }

        return view('promo_hadiah_kasir_13.index', [
            'title' => 'Kasir 13',
            'stocks' => $stocks
        ]);
    }

    public function saveConfig(Request $request)
    {
        DB::beginTransaction();
        try {
            $file = $request->file('csv_file');
            if (!$file) {
                return response()->json(['success' => false, 'message' => 'File tidak ditemukan']);
            }

            $now = DB::selectOne("SELECT DATE_FORMAT(NOW(),'%d%m%Y%h%m%s') as ini")->ini;
            $filePath = storage_path('app/kaser13/' . $now . 'ARIPIN.csv');

            if (file_exists($filePath)) {
                return response()->json(['success' => false, 'message' => 'Data hari ini sudah diambil. Silahkan cetak ulang kitir..']);
            }

            if (!is_dir(storage_path('app/kaser13'))) {
                mkdir(storage_path('app/kaser13'), 0755, true);
            }

            $file->move(storage_path('app/kaser13'), $now . 'ARIPIN.csv');

            $content = file_get_contents($filePath);
            $lines = explode("\n", $content);

            $D = 0;
            $E = 0;
            $MM = substr($request->input('periode'), 0, 2);
            $periode = $request->input('periode');
            $bukti = '';

            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;

                $parts = explode(';', $line);
                if (count($parts) < 4) continue;

                $tgl = substr($parts[0], 0, 4) . '-' . substr($parts[0], 4, 2) . '-' . substr($parts[0], 6, 2);
                $jam = substr($parts[1], 0, 2) . ':' . substr($parts[1], 2, 2);
                $kd_brg = $parts[2];
                $qty = floatval($parts[3]);

                $checkTgl = DB::selectOne("SELECT IF(?<>DATE_SUB(DATE(NOW()),INTERVAL 1 DAY),0,1) as tgl", [$tgl]);
                if ($checkTgl->tgl == 0 && !empty($tgl)) {
                    throw new \Exception('Tanggal salah, ' . $kd_brg . ' ' . $tgl);
                }

                try {
                    $brg = DB::selectOne("SELECT KD_BRG,NA_BRG,AW00,AK00,CONCAT(LPAD(MONTH(NOW()),2,'0'),'/',YEAR(NOW())) AS PER,CONCAT('HDH',RIGHT(YEAR(NOW()),2),MONTH(NOW()),DAY(NOW())) AS BUKTI FROM BRGDT WHERE KD_BRG=CONCAT('011',?) AND CBG='TGZ'", [$kd_brg]);
                } catch (\Exception $ex) {
                    $brg = null;
                }

                if ($brg && !empty($tgl)) {
                    if ($brg->AK00 > $qty) {
                        $D++;
                        $slh = $brg->AK00 - $qty;

                        DB::insert("INSERT INTO juald{$MM} (NO_BUKTI,REC,PER,FLAG,KD_BRG,NA_BRG,QTY,HARGA,TOTAL,barcode,tkp,type,tgl,cbg) SELECT ?,?,CONCAT(LPAD(MONTH(NOW()),2,'0'),'/',YEAR(NOW())),'JL',KD_BRG,NA_BRG,?,HJGZ,?*HJGZ,BARCODE,?*HJGZ,'KS',NOW(),'TGZ' FROM MASKS WHERE KD_BRG=?", ['XX', $D, $slh, $slh, $slh, $brg->KD_BRG]);

                        DB::update("UPDATE brgdt SET ke00=ke00+?,ak00=aw00+ma00-ke00+ln00 WHERE kd_brg=? AND cbg='TGZ'", [$slh, $brg->KD_BRG]);
                    }

                    if ($brg->AK00 < $qty) {
                        $E++;
                        $slh = $qty - $brg->AK00;

                        DB::insert("INSERT INTO stockbzd(no_bukti,KD_BRG,NA_BRG,rec,per,FLAG,CBG,saldo,RIIL,qty) VALUES (?,?,?,?,?,'AO','TGZ',?,?,?)", [$brg->BUKTI, $brg->KD_BRG, $brg->NA_BRG, $E, $brg->PER, $brg->AK00, $qty, $slh]);

                        DB::update("UPDATE brgdt SET ln00=ln00+?,ak00=aw00+ma00-ke00+ln00 WHERE kd_brg=? AND cbg='TGZ'", [$slh, $brg->KD_BRG]);
                    }
                }
            }

            if ($E > 0) {
                $firstBrg = DB::selectOne("SELECT BUKTI FROM BRGDT WHERE LEFT(KD_BRG,3)='011' AND CBG='TGZ' LIMIT 1");
                if ($firstBrg) {
                    DB::insert("INSERT INTO stockbz(no_bukti,tgl,per,posted,tgl_posted,usrnm,flag,tg_smp,CBG) SELECT NO_bukti,DATE(NOW()),per,1,NOW(),?,flag,NOW(),CBG FROM STOCKBZD WHERE no_bukti=? GROUP BY NO_BUKTI", [Auth::user()->name, $firstBrg->BUKTI]);
                }
            }

            if ($D > 0) {
                $mon = substr($periode, 0, 2);
                $ksr = '13';
                $kode = $ksr . substr($periode, -2) . substr($periode, 0, 2);

                $noData = DB::selectOne("SELECT nom{$mon} as no_bukti FROM noks WHERE kasir=? AND per=?", [$ksr, substr($periode, -4)]);
                $r1 = $noData->no_bukti + 1;

                DB::update("UPDATE noks SET NOM{$mon}=? WHERE kasir=? AND PER=?", [$r1, $ksr, substr($periode, -4)]);

                $bkt1 = str_pad($r1, 6, '0', STR_PAD_LEFT);
                $bukti = $kode . $bkt1;

                DB::insert("INSERT INTO jual{$MM}(NO_BUKTI,TGL,PER,FLAG,TOTAL_QTY,TOTAL,dpp,USRNM,TG_SMP,TKP,bulat,totala,bayar,kembali,CBG,ksr,shift,sls,bktklr,tglklr) SELECT CONCAT('R',LPAD(MONTH(NOW()),2,0),LPAD(DAY(NOW()),2,0),'13','P'),DATE(NOW()),?,'RA',0,0,0,?,NOW(),0,0,0,0,0,'TGZ','13','P',1,CONCAT('K',RIGHT(YEAR(NOW()),2),LPAD(MONTH(NOW()),2,0),LPAD(DAY(NOW()),2,0),'13','P'),NOW()", [$periode, Auth::user()->name]);

                DB::insert("INSERT INTO jual{$MM}(NO_BUKTI,TGL,PER,FLAG,TOTAL_QTY,TOTAL,dpp,USRNM,TG_SMP,TKP,bulat,totala,bayar,kembali,CBG,ksr,shift,sls,bktklr,tglklr) SELECT ?,TGL,PER,FLAG,SUM(QTY),SUM(TOTAL),SUM(DPP),?,NOW(),SUM(TKP),SUM(TOTAL)%100,SUM(TOTAL)-(SUM(TOTAL)%100),SUM(TOTAL)-(SUM(TOTAL)%100),0,'TGZ','13','P',1,CONCAT('K',RIGHT(YEAR(NOW()),2),LPAD(MONTH(NOW()),2,0),LPAD(DAY(NOW()),2,0),'13','P'),NOW() FROM juald{$MM} WHERE no_bukti='XX' GROUP BY no_bukti", [$bukti, Auth::user()->name]);

                DB::update("UPDATE juald{$MM} SET NO_BUKTI=? WHERE NO_BUKTI='XX'", [$bukti]);

                DB::update("UPDATE juald{$MM},jual{$MM} SET juald{$MM}.ID=jual{$MM}.NO_ID WHERE juald{$MM}.NO_BUKTI=jual{$MM}.NO_BUKTI AND juald{$MM}.NO_BUKTI=?", [$bukti]);

                DB::insert("INSERT INTO jualby{$MM}(no_bukti,type,jumlah) SELECT ?,'VOUCHER',jual{$MM}.totala FROM jual{$MM} WHERE no_bukti=?", [$bukti, $bukti]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Data berhasil diproses', 'bukti' => $bukti]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function getConfig(Request $request)
    {
        try {
            $stocks = DB::select("SELECT kd_brg, ak00 as stok FROM brgdt WHERE LEFT(kd_brg,3)='011' ORDER BY kd_brg");
            return response()->json(['success' => true, 'data' => $stocks]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function checkCustomer(Request $request)
    {
        return response()->json(['success' => true]);
    }
}
