<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\FMaster\Account;

use Illuminate\Http\Request;
use DataTables;
use Auth;
use DB;
use Carbon\Carbon;


class PostingController extends Controller
{
    
    public function browsebukti(Request $request)
    {
		$bukti = DB::table($request->TABEL)->select('NO_BUKTI','POSTED')->where('NO_BUKTI', $request->NO_BUKTI)->get();
		return response()->json($bukti);
	}

    public function index(Request $request)
    {
        if(strtolower($request->JNS)=='f')
        {
            $jns='Finance';
            $ket=[
                ['ket' => 'Kas','tabel' => 'kas'],
                ['ket' => 'Bank','tabel' => 'bank'],
                ['ket' => 'Memo','tabel' => 'memo'],
            ];
        }
        else if(strtolower($request->JNS)=='s')
        {
            $jns='Stok';
            $ket=[
                ['ket' => 'Purchase Order','tabel' => 'po'],
                ['ket' => 'Pembelian','tabel' => 'beli'],
                ['ket' => 'Sales Order','tabel' => 'so'],
                ['ket' => 'Surat Jalan','tabel' => 'surats'],
                ['ket' => 'Penjualan','tabel' => 'jual'],
                ['ket' => 'Koreksi Stok','tabel' => 'stockb'],
            ];
        }
        else if(strtolower($request->JNS)=='u')
        {
            $jns='Uang';
            $ket=[
                ['ket' => 'Pembayaran Hutang','tabel' => 'hut'],
                ['ket' => 'Transaksi Hutang','tabel' => 'beli'],
                ['ket' => 'Pembayaran Piutang','tabel' => 'piu'],
                ['ket' => 'Transaksi Piutang','tabel' => 'jual'],
                ['ket' => 'U.M Beli','tabel' => 'beli'],
                ['ket' => 'U.M Jual','tabel' => 'jual'],
            ];
        }
        
        return view('posting')->with(['ket' => $ket, 'judul' => $jns, 'jenis' => strtolower($request->JNS)]);
    }

    public function posting(Request $request)
    {
		$tabel = $request->input('tabel');
        $dari = date("Y-m-d", strtotime($request['tglDr']));
        $sampai = date("Y-m-d", strtotime($request['tglSmp']));

        if ($tabel) {
                $sql = "UPDATE $tabel 
                        SET POSTED=1, 
                            -- USRNM='".Auth::user()->username."', 
                            TG_SMP=NOW() 
                        WHERE TGL between '$dari' and '$sampai';";
                $postsql = DB::SELECT($sql);
		}
		
        return redirect('posting/index?JNS='.$request->jenis)->with('status', 'Posting '.$tabel.' selesai..');
    }
    
    public function bukaposting(Request $request)
    {
		$tabel = $request->input('tabelbuka');
		$nobukti = $request->input('NO_BUKTI');

        if ($tabel) {
                $sql = "UPDATE $tabel 
                        SET POSTED=0, 
                            -- USRNM='".Auth::user()->username."', 
                            TG_SMP=NOW() 
                        WHERE NO_BUKTI='$nobukti';";
                $postsql = DB::SELECT($sql);
		}
		
        return redirect('posting/index?JNS='.$request->jenis)->with('status', 'Transaksi '.$nobukti.' selesai dibuka..');
    }
	 
}
