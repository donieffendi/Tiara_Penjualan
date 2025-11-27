<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;
use Auth;

use \koolreport\laravel\Friendship;
use \koolreport\bootstrap4\Theme;

class CheatsheetController extends Controller
{
    public function index()
    {
        $tahun = session()->get('periode')['tahun'];
        $data = [
            'bulan'    => array(),
            'belikg'    => array(),
        ];

		$query = DB::SELECT("SELECT monthname(TGL) as BULAN, sum(KG) as KG from beli WHERE right(PER,4)='$tahun' GROUP BY month(TGL)");
        $nilaimax = 0;
        $bulanmax = '';
		foreach ($query as $key => $value)
		{
            if ($query[$key]->KG>$nilaimax) 
            {
                $nilaimax = $query[$key]->KG;
                $bulanmax = $query[$key]->BULAN;
            }
        }

        return view('cheat')->with(['hasil' => $query])->with(['nilaimax' => $nilaimax])->with(['bulanmax' => $bulanmax]);
    }

}