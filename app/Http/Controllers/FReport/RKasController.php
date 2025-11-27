<?php

namespace App\Http\Controllers\FReport;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\FMaster\Account;

use Illuminate\Http\Request;
use DataTables;
use Auth;
use DB;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;

use \koolreport\laravel\Friendship;
use \koolreport\bootstrap4\Theme;

class RKasController extends Controller
{
	public function report()
	{
		$acno = Account::where('BNK', '=', '1')->get();
		session()->put('filter_tglDari', date("d-m-Y"));
		session()->put('filter_tglSampai', date("d-m-Y"));
		session()->put('filter_acno1', '');
		session()->put('filter_peg1', '');
		session()->put('filter_napeg1', '');
		return view('freport_kas.report')->with(['acno' => $acno])->with(['hasil' => []]);
	}

	public function jasperKasReport(Request $request)
	{
		$file 	= 'kasn';
		$PHPJasperXML = new PHPJasperXML();
		$PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

		$tglDrD = date("Y-m-d", strtotime($request->tglDr));
		$tglSmpD = date("Y-m-d", strtotime($request->tglSmp));
		$periode = date("m/Y", strtotime($request['tglDr']));
		$bulan = date("m", strtotime($request['tglDr']));
		$tahun = date("Y", strtotime($request['tglDr']));
		$acno = $request->acno;

		$query = DB::SELECT("
			SELECT account.ACNO, account.NAMA, accountd.AW$bulan as AWAL
			FROM account, accountd 
			WHERE account.ACNO = accountd.ACNO and account.ACNO='$acno' and accountd.YER='$tahun';
		");

		$xbacno1     = $query[0]->ACNO;
		$xbnama1     = $query[0]->NAMA;
		$xawal1      = $query[0]->AWAL;
		$xakhir1      = $query[0]->AWAL;

		$query = DB::SELECT("
			SELECT SUM(KASD.DEBET) AS DEBET, SUM(KASD.KREDIT) AS KREDIT 
			FROM kas, kasd 
			WHERE kas.NO_BUKTI = kasd.NO_BUKTI AND kas.tgl <'$tglDrD'  and kas.BACNO='$acno' and kas.PER='$periode' $filterpeg ;
		");

		$xawal1      = $xawal1 + $query[0]->DEBET - $query[0]->KREDIT;


		$query = DB::SELECT("
			SELECT SUM(KASD.DEBET) AS DEBET, SUM(KASD.KREDIT) AS KREDIT 
			FROM kas, kasd 
			WHERE kas.NO_BUKTI = kasd.NO_BUKTI AND kas.tgl >='$tglDrD' and kas.tgl <= '$tglSmpD' and kas.BACNO='$acno' and kas.PER='$periode' $filterpeg ;
		");

		$xakhir1      = $xawal1 + $query[0]->DEBET - $query[0]->KREDIT;


		$PHPJasperXML->arrayParameter = array("BACNO" => (string) $xbacno1, "BNAMA" => (string) $xbnama1, "SAWAL" => (float) $xawal1, "SAKHIR" => (float) $xakhir1);
		$PHPJasperXML->arraysqltable = array();

			session()->put('filter_tglDari', $request->tglDr);
			session()->put('filter_tglSampai', $request->tglSmp);
			session()->put('filter_acno1', $request->acno);
			session()->put('filter_peg1', $request->peg1);
			session()->put('filter_napeg1', $request->napeg1);

		$queryakum = DB::SELECT("SET @akum:=0;");
		$query = DB::SELECT("
        	SELECT *, if(@bacno<>BACNO,@akum:=AWAL+DEBET-KREDIT,@akum:=@akum+AWAL+DEBET-KREDIT) as SALDO,@bacno:=BACNO as ganti, URUTAN from
		(
		
		        SELECT '' AS NO_BUKTI, '$tglDrD' AS TGL, '$xbacno1' as BACNO, '' AS BNAMA, '' AS ACNO, '' AS NACNO, 'Saldo Awal' AS URAIAN, 
		        '$xawal1' AS AWAL, 0 AS DEBET, 0 AS KREDIT, 1 AS URUTAN  
		        UNION ALL  
			SELECT kas.NO_BUKTI, kas.TGL, kas.BACNO, kas.BNAMA, kasd.ACNO, kasd.NACNO, kasd.URAIAN, 0 AWAL, kasd.DEBET, kasd.KREDIT, 2 AS URUTAN
			from kas, kasd where kas.NO_BUKTI=kasd.NO_BUKTI and kas.TGL BETWEEN '$tglDrD' and '$tglSmpD' and kas.BACNO='$acno' and kas.PER='$periode'  
			
		) as kas00  ORDER BY TGL, NO_BUKTI ;"
		);
		
		

		if ($request->has('filter')) {
			$acno = Account::where('BNK', '=', '1')->get();
			return view('freport_kas.report')->with(['acno' => $acno])->with(['hasil' => $query]);
		}





		$data = [];
		foreach ($query as $key => $value) {
			array_push($data, array(
				'NO_BUKTI' => $query[$key]->NO_BUKTI,
				'TGL' => $query[$key]->TGL,
				'DEBET' => $query[$key]->DEBET,
				'KREDIT' => $query[$key]->KREDIT,
				'ACNO' => $query[$key]->ACNO,
				'NACNO' => $query[$key]->NACNO,
				'URAIAN' => $query[$key]->URAIAN,
				'SALDO' => $query[$key]->SALDO,
				'KET' => $query[$key]->KET,
			));
		}

		array_push($data, array(
			'NO_BUKTI' => '',
			'TGL' => $tglSmpD,
			'AWAL' => $xawal1,
		));




		$PHPJasperXML->setData($data);
		ob_end_clean();
		$PHPJasperXML->outpage("I");
	}
}
