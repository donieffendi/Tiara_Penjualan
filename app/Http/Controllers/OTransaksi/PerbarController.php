<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
// ganti 1

use App\Models\Master\BrgchDetail;
use App\Models\Master\Brgch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use DataTables;
use Auth;
use DB;
use Carbon\Carbon;

// ganti 2
class PerbarController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getBarang(Request $request)
    {
        $kd_brg = $request->KD_BRG;

        $barang = DB::table('brg')
            ->select('NA_BRG', 'BARCODE')
            ->where('KD_BRG', $kd_brg)
            ->first();

        if ($barang) {
            return response()->json([
                'success' => true,
                'data' => $barang
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Kode barang tidak ditemukan.'
            ]);
        }
    }

    public function index()
    {

        // ganti 3
        return view('otransaksi_perbar.index');
    }


    // ganti 4
    public function proses(Request $request)
    {
        $KD_BRG = $request->KD_BRG;
        $BARCODE2 = $request->BARCODE2;

        // Validasi input
        if (!$KD_BRG || !$BARCODE2) {
            return back()->with('error', 'Kode Barang dan Barcode Baru wajib diisi!');
        }

        // Cek apakah barang ada di tabel brg
        $barang = DB::table('brg')->where('KD_BRG', $KD_BRG)->first();

        if (!$barang) {
            return back()->with('error', 'Barang tidak ditemukan!');
        }

        // Update barcode
        DB::table('brg')
            ->where('KD_BRG', $KD_BRG)
            ->update([
                'BARCODE' => $BARCODE2,
                // 'UPDATED_AT' => now(), // jika kolom ini ada
            ]);

        return redirect()->back()->with('success', 'Barcode berhasil diperbarui!');
    }

}