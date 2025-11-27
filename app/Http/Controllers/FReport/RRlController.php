<?php

namespace App\Http\Controllers\FReport;

use App\Http\Controllers\Controller;
use App\Models\FMaster\Account;
use App\Models\Master\Perid;

use Carbon\Carbon;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use DB;

include_once base_path()."/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";
use PHPJasperXML;


class RRlController extends Controller
{

   public function report()
    {
		$acno = Account::query()->get();
		$per = Perid::query()->get();
		//return $acno;
        return view('freport_rl.report')->with(['acno' => $acno])->with(['per' => $per])->with(['hasil' => []]);
		
    }
	
		public function getRlReport(Request $request)
    {
		
		 if ($request->session()->has('periode')) 
		{
			$periode = $request->session()->get('periode')['bulan']. '/' . $request->session()->get('periode')['tahun'];
		} else
		{
			$periode = '';
		}
		
		if($request['perio'])
		{
			$periode = $request['perio'];
		}
		
		$bulan = substr($periode,0,2);
		$tahun = substr($periode,3,4);
		
        $query = DB::table('rl')
			->select('KODE','NAMA','JUM'.$bulan. ' as JUM', 'AK'.$bulan. ' as AK')->where('YER',$tahun)->get();
			
		//if ($request->ajax())
		//{
				//$query = $query->whereBetween('TGL', [$tglDrD, $tglSmp]);
			
			//return Datatables::of($query)->addIndexColumn()->make(true);
		//}
		return Datatables::of($query)->addIndexColumn()->make(true);
    }	  

    public function jasperRlReport(Request $request) 
	{
		$file 	= 'rl';
		$PHPJasperXML = new PHPJasperXML();
		$PHPJasperXML->load_xml_file(base_path().('/app/reportc01/phpjasperxml/'.$file.'.jrxml'));
		
		
        if ($request->session()->has('periode')) 
		{
			$periode = $request->session()->get('periode')['bulan']. '/' . $request->session()->get('periode')['tahun'];
		} else
		{
			$periode = '';
		}
		
		if($request['perio'])
		{
			$periode = $request['perio'];
		}
		
		$bulan = substr($periode,0,2);
		$tahun = substr($periode,3,4);

		session()->put('filter_periode', $periode);
		
		$queryakum = DB::SELECT("SET @akum:=0;");
		$query = DB::SELECT("
		SELECT KODE, NAMA, JUM$bulan as JUM, AK$bulan as AK
		from rl
		where YER='$tahun' ORDER BY GOL;
		");

		if($request->has('filter'))
		{
			$acno = Account::query()->get();
			$per = Perid::query()->get();
			return view('freport_rl.report')->with(['acno' => $acno])->with(['per' => $per])->with(['hasil' => $query]);
		}

		$data=[];
		foreach ($query as $key => $value)
		{
			array_push($data, array(
				'KODE' => $query[$key]->KODE,
				'NAMA' => $query[$key]->NAMA,
				'AWAL' => $query[$key]->JUM,
				'MASUK' => $query[$key]->AK,
				'PER' => $periode,
				'TGL_CETAK' => date("d/m/Y"),
			));
		}
		$PHPJasperXML->setData($data);
		ob_end_clean();
		$PHPJasperXML->outpage("I");
	}
	
}
