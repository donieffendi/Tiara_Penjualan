<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
// ganti 1

use App\Models\Master\Sup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use DataTables;
use Auth;
use DB;
use Carbon\Carbon;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";
use PHPJasperXML;

// ganti 2
class KtdController extends Controller
{
    public function runFox()
    {   
        $kode = Auth::user()->CBG;
        // ambil dari DB
        $lok_cb = DB::table('toko')->where('KODE', $kode)->value('FOLDER_DCTS');

        // path seperti Delphi
        $lok_kirim = "\\\\192.168.0.100\\spkirim\\{$lok_cb}\\KIRIM";

        // net use
        shell_exec('cmd /c net use \\192.168.0.100 kasir /user:192.168.0.100\kasir');
        shell_exec('cmd /c net use \\192.168.0.100');

        // explore folder seperti Delphi
        shell_exec('cmd /c start "" explorer "' . $lok_kirim . '"');

        return back()->with('success', "Berhasil membuka folder KIRIM!");
    }
}