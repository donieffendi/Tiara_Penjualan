<?php

namespace App\Http\Controllers\OTools;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use DB;

class SistemController extends Controller
{
    public function index(Request $request)
    {
        $userLogin = Auth::user()->username;

        // === CEK HAK AKSES (sama seperti Delphi) ===
        $user = DB::select("
            SELECT divisi, spv, CBG, username 
            FROM tgz.users 
            WHERE divisi IN ('programmer','penjualan')
            AND spv='Y'
            AND CBG='TGZ'
            AND username='$userLogin'
        ");

        if (!$user) {
            return view('otools_sistem.index', [
                'message' => 'Anda tidak berhak!',
                'statusProgram' => 0,
                'statusAktif' => 0,
                'toggleState' => false,
                'toggleColor' => 'red',
                'label1' => '',
                'label2' => '',
            ]);
        }

        // === CEK STATUS PROGRAM SERVER (TYPE = 'JL') ===
        $statusProgramRows = DB::select("
            SELECT AKTIF FROM tampung.sim_sistem 
            WHERE FLAG='SO' AND TYPE='JL'
            AND USRNM='SISTEM'
            AND DATE(TG_SMP)=CURDATE()
            LIMIT 1
        ");

        $statusProgram = count($statusProgramRows) > 0 ? 1 : 0;

        // === CEK STATUS AKTIF SO ===
        $sistem = DB::select("
            SELECT AKTIF FROM tgz.sistem 
            WHERE FLAG='SO' AND TYPE='SO'
            LIMIT 1
        ");

        $statusAktif = ($sistem && isset($sistem[0]->AKTIF))
                        ? $sistem[0]->AKTIF
                        : 0;

        $toggleState = $statusAktif ? true : false;
        $toggleColor = $statusAktif ? 'blue' : 'red';

        // === Label seperti Delphi ===
        $label1 = ($statusProgram && $statusAktif)
                    ? 'SO Aktif dan sudah berjalan!!'
                    : 'Masih bisa perpanjang SO';

        $label2 = $toggleState ? 'Stock Aktif' : 'Stock Non Aktif';

        return view('otools_sistem.index', [
            'statusProgram' => $statusProgram,
            'statusAktif'   => $statusAktif,
            'toggleState'   => $toggleState,
            'toggleColor'   => $toggleColor,
            'label1'        => $label1,
            'label2'        => $label2,
        ]);
    }

    public function toggle(Request $request)
    {
        $userCBG   = Auth::user()->CBG;
        $userLogin = Auth::user()->username;

        // Checkbox → ON = 1, OFF = 0
        $x = $request->has('toggle') ? 1 : 0;

        $statusProgram = $request->statusProgram; 
        $statusAktif   = $request->statusAktif;

        // === SAMA seperti Delphi ===
        if ($statusProgram == 1 && $statusAktif == 1 && $x == 0) {
            return back()->withErrors('SO sudah berjalan, tidak bisa dinon aktifkan!');
        }

        // === UPDATE & INSERT (TYPE = 'SO') ===
        DB::transaction(function () use ($x, $userLogin, $userCBG) {

            DB::statement("
                UPDATE tgz.sistem 
                SET AKTIF = $x
                WHERE FLAG='SO' AND TYPE='SO'
            ");

            // Delphi: TYPE = 'SO'
            DB::statement("
                INSERT INTO tampung.sim_sistem 
                (CBG, FLAG, TYPE, TG_SMP, USRNM, AKTIF)
                VALUES ('$userCBG', 'SO', 'SO', NOW(), '$userLogin', '$x')
            ");
        });

        return back()->with('success', 'Selesai!');
    }
}
