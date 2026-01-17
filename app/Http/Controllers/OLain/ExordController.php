<?php

namespace App\Http\Controllers\OLain;

use App\Http\Controllers\Controller;

use App\Models\OLain\Jackh;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Master\Brg;
use App\Models\Master\Sup;
use DataTables;
use Auth;
use DB;

class ExordController extends Controller
{

    public function index() {
        $cbg = DB::SELECT("SELECT KODE FROM toko WHERE STA IN ('MA','CB','DC') ORDER BY NO_ID ASC");

        session()->put('filter_cbg', '');
        session()->put('filter_tgl', date("d-m-Y"));
        return view('olain_exord.index')->with(['cbg' => $cbg]);
    }

    public function prosesOrder(Request $r)
    {
        $cbg = $r->cbg;
        $tgl = Carbon::parse($r->tgl);

        // 1. validasi tanggal
        if ($tgl->lt(Carbon::create(2023,2,13))) {
            return response()->json(['error'=>'Tidak bisa buat orderan dibawah 13 Februari 2023'],400);
        }

        // 2. call SP TABEL
        DB::statement("CALL dck.gd_orderan_dc_ts('TABEL', ?, ?, ?)", [
            $cbg, $tgl->format('Y-m-d'), auth()->user()->name
        ]);

        // 3. cek kolom
        $col = DB::select("
            SELECT COUNT(*) ADA
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA=? AND TABLE_NAME='orderan_dc_ts'
            AND COLUMN_NAME='TGL_EXPORT_TS'
        ", [$cbg]);

        if ($col[0]->ADA == 0) {
            DB::statement("ALTER TABLE {$cbg}.orderan_dc_ts ADD COLUMN TGL_EXPORT_TS DATETIME NULL DEFAULT '2001-01-01'");
        }

        // 4. call SP BACKUP_ORDER
        DB::statement("CALL dck.gd_orderan_dc_ts('BACKUP_ORDER', ?, '', '')", [$cbg]);

        // 5. hitung shift
        $shift = now()->hour > 13 ? 'S' : 'P';

        // 6. cek sudah pernah diproses?
        $cek = DB::select("
            SELECT NO_BUKTI FROM {$cbg}.orderan_dc_ts WHERE CBG=? AND TGL=? AND SHIFT=?
            UNION ALL
            SELECT NO_BUKTI FROM {$cbg}.orderan_dc_ts_backup WHERE CBG=? AND TGL=? AND SHIFT=?
        ", [$cbg, $tgl->format('Y-m-d'), $shift, $cbg, $tgl->format('Y-m-d'), $shift]);

        if (count($cek) > 0) {
            return response()->json([
                'reprint'=>true,
                'cbg'=>$cbg,
                'tgl'=>$tgl->format('Y-m-d'),
                'shift'=>$shift
            ]);
        }

        // 7. langsung cetak
        return response()->json([
            'ok'=>true,
            'cbg'=>$cbg,
            'tgl'=>$tgl->format('Y-m-d'),
            'shift'=>$shift
        ]);
    }

    public function cetakOrder(Request $r)
    {
        $cbg = $r->cbg;
        $tgl = $r->tgl;
        $shift = $r->shift;

        // dd($request->all()); // untuk debug dulu

        // 1. Pangil stored procedure proses
        DB::statement("CALL dck.gd_orderan_dc_ts('PROSES', ?, ?, ?)", [
            $cbg,
            $tgl,
            auth()->user()->name
        ]);

        // 2. Jalankan EXE delphi dari Laravel
        $cmd = "C:\\TIARA\\EXPORT_ORDER_TS.exe {$cbg} {$tgl} {$shift}";
        shell_exec($cmd);

        return back()->with('success', 'Export orderan selesai');
    }

}