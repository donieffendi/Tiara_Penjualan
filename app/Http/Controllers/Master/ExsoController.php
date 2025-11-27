<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Master\Cust;
use App\Models\Master\Cbg;
use App\Models\Master\Perid;
use DataTables;
use Auth;
use DB;


use \koolreport\laravel\Friendship;
use \koolreport\bootstrap4\Theme;

class ExsoController extends Controller
{
	public function exso( Request $request )
    {	
        session()->put('filter_tgl', date("d-m-Y"));
        session()->put('filter_sub', '');

        return view('master_exso.index');
    }

    public function export(Request $request)
    {
        $db = Auth::user()->CBG;    // sama seperti Delphi: cbg := FrMenu.Flag.Caption
        $sub = $request->sub;
        $tgl = $request->tgl;       // sudah yyyy-mm-dd dari JS

        $url = "http://10.10.30.132:8080/export-dbf-app/public/api/export-so-overstok";

        try {
            $client = new \GuzzleHttp\Client();

            $response = $client->post($url, [
                'form_params' => [
                    'db' => $db,
                    'sub' => $sub,
                    'tgl' => $tgl
                ]
            ]);

            $body = $response->getBody()->getContents();

            return response()->json([
                'message' => 'Data Berhasil Dikirim',
                'response' => $body
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengirim data: ' . $e->getMessage()
            ], 500);
        }
    }
}
