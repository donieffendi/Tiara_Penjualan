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

include_once base_path()."/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";
use PHPJasperXML;

use \koolreport\laravel\Friendship;
use \koolreport\bootstrap4\Theme;

class ExpimController extends Controller
{
	public function expim( Request $request )
    {	
		session()->put('filter_tglDari', date("d-m-Y"));

        return view('master_expim.index');
    }

    public function import(Request $request)
    {
        // Validasi bahwa file ada dan ekstensi .sql
        $request->validate([
            'import_file' => 'required|file|mimes:sql,txt'
        ]);

        // Simpan file sementara ke storage/app/tmp/
        $path = $request->file('import_file')->storeAs(
            'tmp',
            'import_' . time() . '.sql'
        );

        // Baca isi file
        $sql = file_get_contents(storage_path('app/' . $path));

        // Jalankan query SQL (hati-hati — pastikan file terpercaya)
        try {
            DB::unprepared($sql);
            return redirect()->back()->with('status', 'Import SQL berhasil!');
        } catch (\Exception $e) {
            return redirect()->back()->with('status', 'Import gagal: ' . $e->getMessage());
        }
    }
}
