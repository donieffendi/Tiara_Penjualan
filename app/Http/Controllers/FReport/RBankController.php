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

use \koolreport\laravel\Friendship;
use \koolreport\bootstrap4\Theme;

class RbankController extends Controller
{

    public function report()
    {
		$acno = Account::where('BNK', '=','2')->get();
		session()->put('filter_tglDari', date("d-m-Y"));
		session()->put('filter_tglSampai', date("d-m-Y"));
		session()->put('filter_acno1', '');

        return view('freport_bank.report')->with(['acno' => $acno])->with(['hasil' => []]);
    }
	
	public function getBankReport(Request $request)
    {
    /*
        $query = DB::table('bank')
			->join('bankd', 'bank.NO_BUKTI', '=', 'bankd.NO_BUKTI')
			->select('bank.NO_BUKTI', 'bank.TGL', 'bank.BACNO', 'bank.BNAMA', 'bankd.ACNO', 'bankd.NACNO', 'bankd.URAIAN', 'bankd.DEBET', 'bankd.KREDIT')->get();
		*/
			
		$periode = $request->session()->get('periode')['bulan']. '/' . $request->session()->get('periode')['tahun'];
		$bulan = substr($periode,0,2);
		$tahun = substr($periode,3,4);
		$acno = '';
		$tgawal = $tahun.'-'.$bulan.'-01';
		
		if ($request->ajax())
		{
			// Ganti format tanggal input agar sama dengan database
			$tglDrD = date("Y-m-d", strtotime($request['tglDr']));
            $tglSmpD = date("Y-m-d", strtotime($request['tglSmp']));
			
			// Convert tanggal agar ambil start of day/end of day
			$tglDr = Carbon::parse($request->tglDr)->startOfDay();
            $tglSmp = Carbon::parse($request->tglSmp)->endOfDay();
			
			// Check Filter
			/*
			if (!empty($request->acno))
			{
				$query = $query->where('BACNO', $request->acno);
			}
			
			if (!empty($request->tglDr) && !empty($request->tglSmp))
			{
				$query = $query->whereBetween('TGL', [$tglDrD, $tglSmp]);
			}
			*/
			
			$periode = date("m/Y", strtotime($request['tglDr']));
			$bulan = date("m", strtotime($request['tglDr']));
			$tahun = date("Y", strtotime($request['tglDr']));
			$acno = $request->acno;
			$tgawal = $tahun.'-'.$bulan.'-01';
		}
		
		$queryakum = DB::SELECT("SET @akum:=0;");
		$query = DB::SELECT("
		SELECT *,@akum:=@akum+AWAL+DEBET-KREDIT SALDO from
		(
			SELECT '' AS NO_BUKTI, '$tglDrD'  AS TGL, BACNO AS BACNO, BNAMA AS BNAMA, '' AS ACNO, '' AS NACNO, 
			'SALDO AWAL' URAIAN, 
			SUM(AWAL) AS AWAL, 0 DEBET, 0 KREDIT
			from
			(
				SELECT ACNO AS BACNO, NAMA AS BNAMA, AW$bulan AS AWAL 
				from accountd WHERE ACNO='$acno' and YER='$tahun'
				UNION ALL
				SELECT BACNO AS BACNO, BNAMA AS BNAMA, SUM(BANKD.DEBET - BANKD.KREDIT ) AS AWAL 
				from bank, bankd where bank.NO_BUKTI=bankd.NO_BUKTI and bank.TGL<'$tglDrD' 
				and bank.BACNO='$acno' and bank.PER='$periode'
			) as AWAL00
			UNION ALL
			SELECT bank.NO_BUKTI, bank.TGL, bank.BACNO, bank.BNAMA, bankd.ACNO, bankd.NACNO, bankd.URAIAN, 0 AWAL, bankd.DEBET, bankd.KREDIT 
			from bank, bankd where bank.NO_BUKTI=bankd.NO_BUKTI and bank.TGL BETWEEN '$tglDrD' and '$tglSmpD' and bank.BACNO='$acno' and bank.PER='$periode'
		) as Rbank;"
		);
		
		return Datatables::of($query)->addIndexColumn()->make(true);
		
    }	 
	 
	public function jasperBankReport(Request $request) 
	{
		$file 	= 'bankn';
		$PHPJasperXML = new PHPJasperXML();
		$PHPJasperXML->load_xml_file(base_path().('/app/reportc01/phpjasperxml/'.$file.'.jrxml'));
		
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
			SELECT SUM(bankd.DEBET) AS DEBET, SUM(bankd.KREDIT) AS KREDIT 
			FROM bank, bankd 
			WHERE bank.NO_BUKTI = bankd.NO_BUKTI AND bank.tgl <'$tglDrD'  and bank.BACNO='$acno' and bank.PER='$periode';
		");		
		
        $xawal1      = $xawal1 + $query[0]->DEBET - $query[0]->KREDIT;		
	
        $query = DB::SELECT("
			SELECT SUM(BANKD.DEBET) AS DEBET, SUM(BANKD.KREDIT) AS KREDIT 
			FROM bank, bankd 
			WHERE bank.NO_BUKTI = bankd.NO_BUKTI AND bank.tgl >='$tglDrD' and bank.tgl <= '$tglSmpD' and bank.BACNO='$acno' and bank.PER='$periode' ;
		");	

	    $xakhir1      = $xawal1 + $query[0]->DEBET - $query[0]->KREDIT;		
	
	
        $PHPJasperXML->arrayParameter = array("BACNO" => (string) $xbacno1, "BNAMA" => (string) $xbnama1, "SAWAL" => (float) $xawal1, "SAKHIR" => (float) $xakhir1 );
        $PHPJasperXML->arraysqltable = array();
	
		
			session()->put('filter_tglDari', $request->tglDr);
			session()->put('filter_tglSampai', $request->tglSmp);
			session()->put('filter_acno1', $request->acno);

		$queryakum = DB::SELECT("SET @akum:=0;");
		$query = DB::SELECT("
        	SELECT *, if(@bacno<>BACNO,@akum:=AWAL+DEBET-KREDIT,@akum:=@akum+AWAL+DEBET-KREDIT) as SALDO,@bacno:=BACNO as ganti, URUTAN from
		(
		
		        SELECT '' AS NO_BUKTI, '$tglDrD' AS TGL, '$xbacno1' as BACNO, '' AS BNAMA, '' AS ACNO, '' AS NACNO, 'Saldo Awal' AS URAIAN, 
		        '$xawal1' AS AWAL, 0 AS DEBET, 0 AS KREDIT, 1 AS URUTAN  
		        UNION ALL  
			SELECT bank.NO_BUKTI, bank.TGL, bank.BACNO, bank.BNAMA, bankd.ACNO, bankd.NACNO, bankd.URAIAN, 0 AWAL, bankd.DEBET, bankd.KREDIT, 2 AS URUTAN
			from bank, bankd where bank.NO_BUKTI=bankd.NO_BUKTI and bank.TGL BETWEEN '$tglDrD' and '$tglSmpD' and bank.BACNO='$acno' and bank.PER='$periode'  
			
		) as bank00  ORDER BY TGL, NO_BUKTI ;"
		);
		$acno = Account::where('BNK', '=','2')->get();
		if($request->has('filter'))
		{
			return view('freport_bank.report')->with(['acno' => $acno])->with(['hasil' => $query]);
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
				'URAIAN' => $query[$key]->URAIAN,
				'SALDO' => $query[$key]->SALDO,
				'KET' => $query[$key]->KET,
			));
		}
		$PHPJasperXML->setData($data);
		ob_end_clean();
		$PHPJasperXML->outpage("I");
	}
	
	

	
}
