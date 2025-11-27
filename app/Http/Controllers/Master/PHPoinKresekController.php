<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PHPoinKresekController extends Controller
{
    public function index()
    {
        return $this->showIndex('POIN');
    }

    public function indexFC()
    {
        return $this->showIndex('FC');
    }

    private function showIndex($flag)
    {
        try {
            $cbg = DB::select("SELECT kode, na_toko FROM toko WHERE STA IN ('MA','CB','DC') ORDER BY kode ASC");
            if (empty($cbg)) {
                $cbg = [];
            }
        } catch (\Exception $e) {
            $cbg = [];
        }

        $sessionKey = $flag == 'POIN' ? 'phk_cabang' : 'phfc_cabang';
        $selectedCbg = session($sessionKey, '');
        $config = [];

      

        $title = $flag == 'POIN' ? 'Promo Hadiah Poin Kresek' : 'Promo Hadiah Poin EDC';

        return view('promo_hadiah_poin_kresek.index', [
            'cbg' => $cbg,
            'config' => $config,
            'selectedCbg' => $selectedCbg,
            'flag' => $flag,
            'title' => $title
        ]);
    }

    public function saveConfig(Request $request)
    {
        try {
            $cbg = $request->get('cabang');
            $flag = $request->get('flag', 'POIN');
            $flag = 'POIN' ? 'FC' : 'AA';

            $status_poin = $request->get('status_poin', 0);
            $tgl_mulai = $request->get('tgl_mulai');
            $tgl_selesai = $request->get('tgl_selesai');
            $jam_diskon = $request->get('jam_diskon', '00:00:00');

            $sessionKey = $flag == 'POIN' ? 'phk_cabang' : 'phfc_cabang';
            session([$sessionKey => $cbg]);

            DB::beginTransaction();

            $check = DB::select("SELECT cbg FROM poin WHERE cbg = ? AND flag = ?", [$cbg, $flag]);
            $now = date('Y-m-d');

            $year  = date('Y', strtotime($now));
            $month = date('m', strtotime($now));
            $day   = str_pad($tgl_mulai, 2, '0', STR_PAD_LEFT);
            $tgl_mulai = "$year-$month-$day";
            $day   = str_pad($tgl_selesai, 2, '0', STR_PAD_LEFT);
            $tgl_selesai = "$year-$month-$day";
            if (empty($check)) {
                DB::statement(
                    "INSERT INTO poin (cbg, flag, kresek, tgl1, tgl2, jam1, jam2)
                               VALUES (?, ?, ?, ?, ?, ?, ?)",
                    [$cbg, $flag, $status_poin, $tgl_mulai, $tgl_selesai, $jam_diskon, '00:00:00']
                );
            } else {
               
                DB::statement(
                    "UPDATE poin
                               SET kresek = ?, tgl1 = ?, tgl2 = ?, jam1 = ?, jam2= '00:00:00'
                               WHERE cbg = ? AND flag = ?",
                    [$status_poin, $tgl_mulai, $tgl_selesai, $jam_diskon, $cbg, $flag]
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Konfigurasi berhasil disimpan'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function getConfig(Request $request)
    {
        $cbg = $request->get('cabang');
        $flag = $request->get('flag', 'POIN');

        $config = DB::select("SELECT * FROM phpoinkresek_config WHERE cbg = ? AND flag = ?", [$cbg, $flag]);

        if (!empty($config)) {
            return response()->json([
                'success' => true,
                'data' => $config[0]
            ]);
        }

        return response()->json([
            'success' => false,
            'data' => null
        ]);
    }

   
}
