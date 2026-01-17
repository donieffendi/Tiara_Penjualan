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

class CollectController extends Controller
{

    public function index() {
        return view('olain_collect.index');
    }

    public function getCollect(Request $request)
    {

        $collect = DB::SELECT("CALL sim_ambil_brg_rak('EXPORT_DAT_COLL','','')");
        
        return Datatables::of($collect)
                ->addIndexColumn()
                ->make(true);
    }

    private function padR($val, $len)
    {
        return str_pad(substr((string)$val, 0, $len), $len, ' ', STR_PAD_RIGHT);
    }

    private function padL($val, $len)
    {
        return str_pad(substr((string)$val, 0, $len), $len, ' ', STR_PAD_LEFT);
    }

    public function exportTxt()
    {
        $data = DB::select('CALL sim_ambil_brg_rak("EXPORT_DAT_COLL","","")');
        $lines = [];

        foreach ($data as $r) {

            $line =
                $this->padR(substr($r->SUB,0,3).substr($r->KDBAR,0,4), 7) .
                $this->padR($r->BARCODE, 13) .
                $this->padR($r->NA_BRG, 30) .
                $this->padR($r->KET_UK, 10) .
                $this->padR($r->KET_KEM, 18) .
                $this->padL(number_format($r->HJ, 2, '.', ''), 12) .
                $this->padL(number_format($r->LPH, 2, '.', ''), 10) .
                $this->padL(number_format($r->AK00, 0, '.', ''), 10);

            // Guard wajib
            if (strlen($line) !== 110) {
                throw new \Exception("Invalid length: ".strlen($line));
            }

            $lines[] = $line;
        }

        // dd(
        //     substr($line, 0, 7),   // KD_BRG
        //     substr($line, 7, 13)   // BARCODE
        // );

        return response(implode("\r\n", $lines))
            ->header('Content-Type', 'text/plain')
            ->header(
                'Content-Disposition',
                'attachment; filename="MASTER_DATA_COLLECTOR.txt"'
            );
    }

}