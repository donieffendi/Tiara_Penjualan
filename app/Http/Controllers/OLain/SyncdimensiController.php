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

class SyncdimensiController extends Controller
{

    public function index() {
        return view('olain_syncdimensi.index');
    }

    public function cek()
    {
        $cek = DB::select("CALL sim_sinkron_dimensi('CEK','','')");

        if (count($cek) > 0) {
            return response()->json([
                'exists' => true,
                'jam'    => $cek[0]->JAM ?? '',
                'user'   => $cek[0]->USRNM ?? ''
            ]);
        }

        return response()->json([
            'exists' => false
        ]);
    }

    public function proses(Request $request)
    {
        $request->validate([
            'file' => 'required|file'
        ]);

        $file = $request->file('file');
        $namaFile = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $ext = $file->getClientOriginalExtension();

        // simpan file upload
        $path = $file->storeAs('import_dimensi', $namaFile.'.'.$ext);

        // HAPUS data lama
        DB::statement("CALL sim_sinkron_dimensi('HAPUS', ?, '')", [
            $namaFile.'.'.$ext
        ]);

        /**
         * DI SINI:
         * - baca DBF (pakai library php-dbf / odbc)
         * - loop insert ke sinkron_dimensi_brg
         */

        DB::statement("CALL sim_sinkron_dimensi('UPDATE', ?, ?)", [
            $namaFile.'.'.$ext,
            Auth::user()->username
        ]);

        return response()->json([
            'message' => 'Sinkron Dimensi Barang selesai'
        ]);
    }
}