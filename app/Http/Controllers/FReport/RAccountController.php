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

use \koolreport\laravel\Friendship;
use \koolreport\bootstrap4\Theme;

class RAccountController extends Controller
{

   public function report()
    {
		$acno = Account::query()->get();
		$per = Perid::query()->get();

		
		session()->put('filter_pilih', '');
		session()->put('filter_tahun', '');
		
		//return $acno;
        return view('freport_account.report')->with(['acno' => $acno])->with(['per' => $per])->with(['hasil' => []]);
		
    }
	
	public function getAccountReport(Request $request)
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
		
        		$query = DB::table('account')
			->join('accountd', 'account.ACNO', '=', 'accountd.ACNO')
			->select('account.ACNO','account.NAMA','account.POS2','account.KEL', 'account.NAMA_KEL','accountd.AW'.$bulan. ' as AW', 'accountd.KD'.$bulan. ' as KD', 
			'accountd.KK'.$bulan. ' as KK', 'accountd.BD'.$bulan. ' as BD', 'accountd.BK'.$bulan. ' as BK', 
			'accountd.MD'.$bulan. ' as MD', 'accountd.MK'.$bulan. ' as MK', 'accountd.AK'.$bulan. ' as AK')->where('accountd.YER',$tahun)->orderBy('accountd.ACNO', 'ASC')->get();
			
		//if ($request->ajax())
		//{
				//$query = $query->whereBetween('TGL', [$tglDrD, $tglSmp]);
			
			//return Datatables::of($query)->addIndexColumn()->make(true);
		//}
		return Datatables::of($query)->addIndexColumn()->make(true);
    }	  

	public function jasperAccountReport(Request $request) 
	{
		$file 	= 'nerapr';
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
		$filter_tahun = $request->tahun;

		session()->put('filter_periode', $periode);
		
		if ($request->PILIH == '1'){

			$queryakum = DB::SELECT("SET @akum:=0;");
			$query = DB::SELECT(
						"SELECT account.ACNO,account.POS2, account.KEL, account.NAMA_KEL, account.NAMA,accountd.AW$bulan as AW, accountd.KD$bulan as KD, 
							accountd.KK$bulan as KK, accountd.BD$bulan as BD, accountd.BK$bulan as BK, 
							accountd.MD$bulan as MD, accountd.MK$bulan as MK, accountd.AK$bulan as AK 
						from account, accountd
						where account.ACNO=accountd.ACNO and accountd.YER='$tahun' order by account.ACNO;
						");
		} else if($request->PILIH == '2'){
			$queryakum = DB::SELECT("SET @akum:=0;");
			$query = DB::SELECT(

				" SELECT accountd.ACNO AS ACNO,accountd.NAMA AS NAMA,accountd.AW01 AS AW ,accountd.KD01 + accountd.KD02 + accountd.KD03 + accountd.KD04 + accountd.KD05 + accountd.KD06 + accountd.KD07 + accountd.KD08 + accountd.KD09 + accountd.KD10 + accountd.KD11 + accountd.KD12 AS KD,
						accountd.KK01 + accountd.KK02 + accountd.KK03 + accountd.KK04 + accountd.KK05 + accountd.KK06 + accountd.KK07 + accountd.KK08 + accountd.KK09 + accountd.KK10 + accountd.KK11 + accountd.KK12 AS KK,
						accountd.BD01 + accountd.BD02 + accountd.BD03 + accountd.BD04 + accountd.BD05 + accountd.BD06 + accountd.BD07 + accountd.BD08 + accountd.BD09 + accountd.BD10 + accountd.BD11 + accountd.BD12 AS BD,
						accountd.BK01 + accountd.BK02 + accountd.BK03 + accountd.BK04 + accountd.BK05 + accountd.BK06 + accountd.BK07 + accountd.BK08 + accountd.BK09 + accountd.BK10 + accountd.BK11 + accountd.BK12 AS BK,
						accountd.MD01 + accountd.MD02 + accountd.MD03 + accountd.MD04 + accountd.MD05 + accountd.MD06 + accountd.MD07 + accountd.MD08 + accountd.MD09 + accountd.MD10 + accountd.MD11 + accountd.MD12 AS MD,
						accountd.MK01 + accountd.MK02 + accountd.MK03 + accountd.MK04 + accountd.MK05 + accountd.MK06 + accountd.MK07 + accountd.MK08 + accountd.MK09 + accountd.MK10 + accountd.MK11 + accountd.MK12 AS MK,
						accountd.AK12 AS AK, accountd.POS2, accountd.KEL
					FROM accountd
					WHERE accountd.YER = '$filter_tahun'
					ORDER BY accountd.ACNO"
				);
		}


		
		session()->put('filter_pilih', $request->PILIH);
		session()->put('filter_tahun', $request->tahun);



		if($request->has('filter'))
		{
			$acno = Account::query()->get();
			$per = Perid::query()->get();
			return view('freport_account.report')->with(['acno' => $acno])->with(['per' => $per])->with(['hasil' => $query]);
		}

		$data=[];
		foreach ($query as $key => $value)
		{
			array_push($data, array(
				'ACNO' => $query[$key]->ACNO,
				'NAMA' => $query[$key]->NAMA,
				'AW' => $query[$key]->AW,
				'DEBETK' => $query[$key]->KD,
				'KREDITK' => $query[$key]->KK,
				'DEBETB' => $query[$key]->BD,
				'KREDITB' => $query[$key]->BK,
				'DEBETM' => $query[$key]->MD,
				'KREDITM' => $query[$key]->MK,
				'AKHIR' => $query[$key]->AK,
				'POS2' => $query[$key]->POS2,
				'KEL' => $query[$key]->KEL,
				'NAMA_KEL' => $query[$key]->NAMA_KEL,				
				'PER' => $periode,
				'TGL_CETAK' => date("d/m/Y"),
			));
		}
		$PHPJasperXML->setData($data);
		ob_end_clean();
		$PHPJasperXML->outpage("I");
	}
	
}