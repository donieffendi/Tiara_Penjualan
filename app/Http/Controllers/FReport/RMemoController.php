<?php

namespace App\Http\Controllers\FReport;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\FMaster\Account;

use Illuminate\Http\Request;
use DataTables;
use Auth;
use DB;

include_once base_path()."/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";
use PHPJasperXML;

class RMemoController extends Controller
{

	public function report()
	{
		$acno = Account::query()->get();
		session()->put('filter_tglDari', date("d-m-Y"));
		session()->put('filter_tglSampai', date("d-m-Y"));

		return view('freport_memo.report')->with(['acno' => $acno])->with(['hasil' => []]);
	}

	public function getMemoReport(Request $request)
	{
		$query = DB::table('memo')
			->join('memod', 'memo.NO_BUKTI', '=', 'memod.NO_BUKTI')
			->select('memo.NO_BUKTI', 'memo.TGL', 'memod.ACNO', 'memod.NACNO', 'memod.ACNOB', 'memod.NACNOB', 'memod.URAIAN', 'memod.DEBET', 'memod.KREDIT')->get();

		if ($request->ajax()) {
			// Ganti format tanggal input agar sama dengan database
			$tglDrD = date("Y-m-d", strtotime($request['tglDr']));
			$tglSmpD = date("Y-m-d", strtotime($request['tglSmp']));

			// Convert tanggal agar ambil start of day/end of day
			//$tglDr = Carbon::parse($request->tglDr)->startOfDay();
			$tglSmp = Carbon::parse($request->tglSmp)->endOfDay();

			// Check Filter

			if (!empty($request->tglDr) && !empty($request->tglSmp)) {
				$query = $query->whereBetween('TGL', [$tglDrD, $tglSmp]);
			}
		}

		return Datatables::of($query)->addIndexColumn()->make(true);
	}

	public function cetak(Request $request)
	{

		// if ($report == 'testreport') {
		// $param1 = Input::get('param1');

		// $PHPJasperXML = new PHPJasperXML();

		//Enabling this will let you see the generated SQL (with parameters in place and all)
		//$PHPJasperXML->debugsql=true; 

		//Array of parameters eg. array("param1"  => $param1, "param2"=>$param2);
		//Here we assume that the report has a parameter called "param1"
		// $PHPJasperXML->arrayParameter = array("param1"  => $param1);
		// }

		// $PHPJasperXML->load_xml_file(app_path() . "/reportc01/phpjasperxml/memon.jrxml");

		// $PHPJasperXML->transferDBtoArray();
		//Clean the end of the buffer before outputting the PDF
		// ob_end_clean();

		//page output method I:standard output  D:Download file
		// return Response::make($PHPJasperXML->outpage("I"));

		//$postjson = json_decode(file_get_contents('php://input'), true);
		//$today = date('Y-m-d');

		// if ($postjson['kodea'] != '') $kodea = $postjson['kodea'];
		// else $kodea = $_GET['kodea'];

		// if ($postjson['kodeb'] != '') $kodeb = $postjson['kodeb'];
		// else $kodeb = $_GET['kodeb'];

		// $KD_BAG = $postjson['KD_BAG'];
		// $PER = $_POST['PER'];
		// $NO_PO = $_POST['NO_PO'];

		// $servername = DB::connection()->getConfig("hostname");
		// $username = DB::connection()->getConfig("username");
		// $password = DB::connection()->getConfig("password");
		// $database = DB::connection()->getConfig("database");
		// //$mysqli = mysqli_connect($servername, $username, $password, $database);

		// //$tgl1 = $_GET['tgl1'];
		// //$tgl2 = $_GET['tgl2'];
		// $tgl1 = $request->input('tglDr');
		// $tgl2 = $request->input('tglSmp');

		// //include('phpjasperxml/class/tcpdf/tcpdf.php');
		// //include('phpjasperxml/class/PHPJasperXML.inc.php');
		// //include('phpjasperxml/setting.php');
		// include(app_path() . '/reportc01/phpjasperxml/class/tcpdf/tcpdf.php');
		// include(app_path() . '/reportc01/phpjasperxml/class/PHPJasperXML.inc.php');
		// include(app_path() . '/reportc01/phpjasperxml/setting.php');

		// $conn = mysqli_connect($servername, $username, $password, $database);
		// error_reporting(E_ALL);
		// ob_start();

		// $PHPJasperXML = new \PHPJasperXML();
		// $PHPJasperXML->load_xml_file(app_path() . "/reportc01/phpjasperxml/memon.jrxml");

		// $query = "SELECT MEMO.NO_BUKTI, MEMO.TGL, MEMOD.ACNO, MEMOD.NACNO, MEMOD.ACNOB, MEMOD.NACNOB, MEMOD.DEBET, MEMOD.KREDIT 
		// 				FROM MEMO, MEMOD WHERE MEMO.NO_BUKTI = MEMOD.NO_BUKTI 
		// 				AND MEMO.TGL >='$tgl1' AND MEMO.TGL <='$tgl2'  ORDER BY MEMO.NO_BUKTI ";


		// //$result1 = mysqli_query($mysqli, $query);
		// //$result1 = \DB::select($query);

		// // $result1 = DB::table('memo')
		// // 	->join('memod', 'memo.NO_BUKTI', '=', 'memod.NO_BUKTI')
		// // 	->select('memo.NO_BUKTI', 'memo.TGL', 'memod.ACNO', 'memod.NACNO', 'memod.ACNOB', 'memod.NACNOB', 'memod.URAIAN', 'memod.DEBET', 'memod.KREDIT')->get();

		// $PHPJasperXML->transferDBtoArray($servername, $username, $password, $database);
		// $PHPJasperXML->arraysqltable = array();
		// $result1 = mysqli_query($conn, $query);
		// while ($row1 = mysqli_fetch_assoc($result1)) {
		// 	array_push($PHPJasperXML->arraysqltable, array(
		// 		"NO_BUKTI" => $row1["NO_BUKTI"],
		// 		"TGL" => $row1["TGL"],
		// 		"ACNO" => $row1["ACNO"],
		// 		"NACNO" => $row1["NACNO"],
		// 		"ACNOB" => $row1["ACNOB"],
		// 		"NACNOB" => $row1["NACNOB"],
		// 		"DEBET" => $row1["DEBET"],
		// 		"KREDIT" => $row1["KREDIT"],
		// 		"URAIAN" => $row1["URAIAN"]

		// 	));
		// }
		// ob_end_clean();
		// $PHPJasperXML->outpage("I");
	}



	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	 
	 
	 public function jasperMemoReport(Request $request) 
	{
		$file 	= 'memon';
		$PHPJasperXML = new PHPJasperXML();
		$PHPJasperXML->load_xml_file(base_path().('/app/reportc01/phpjasperxml/'.$file.'.jrxml'));
		
		$tglDrD = date("Y-m-d", strtotime($request->tglDr));
		$tglSmpD = date("Y-m-d", strtotime($request->tglSmp));
		//$acno = $request->acno;
		session()->put('filter_tglDari', $request->tglDr);
		session()->put('filter_tglSampai', $request->tglSmp);

		$query = DB::SELECT("
			SELECT memo.NO_BUKTI,memo.TGL,memod.DEBET,memod.KREDIT,memod.ACNO,memod.NACNO, memod.ACNOB,memod.NACNOB, memod.URAIAN
			FROM memo, memod 
			WHERE memo.NO_BUKTI=memod.NO_BUKTI  and memo.TGL between '$tglDrD' and '$tglSmpD' 
			ORDER BY memo.NO_BUKTI;
		");

		if($request->has('filter'))
		{
			$acno = Account::query()->get();
	
			return view('freport_memo.report')->with(['acno' => $acno])->with(['hasil' => $query]);
		}

		$data=[];
		foreach ($query as $key => $value)
		{
			array_push($data, array(
				'NO_BUKTI' => $query[$key]->NO_BUKTI,
				'TGL' => $query[$key]->TGL,
				'DEBET' => $query[$key]->DEBET,
				'KREDIT' => $query[$key]->KREDIT,
				'ACNO' => $query[$key]->ACNO,
				'NACNO' => $query[$key]->NACNO,
				'ACNOB' => $query[$key]->ACNOB,
				'NACNOB' => $query[$key]->NACNOB,
				'URAIAN' => $query[$key]->URAIAN,
			));
		}
		$PHPJasperXML->setData($data);
		ob_end_clean();
		$PHPJasperXML->outpage("I");
	}
	
}
